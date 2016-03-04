<?php

include('includes/DefineSerialItems.php');
include('includes/DefineStockTransfers.php');

include('includes/session.inc');
$Title = _('Inventory Transfer') . ' - ' . _('Receiving');// Screen identification.
$ViewTopic = 'Inventory';// Filename's id in ManualContents.php's TOC.
$BookMark = 'LocationTransfers';// Anchor's id in the manual's html document.
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (empty($_GET['identifier'])) {
	/*unique session identifier to ensure that there is no conflict with other order entry sessions on the same machine  */
	$Identifier = date('U');
} else {
	$Identifier = $_GET['identifier'];
}

if (isset($_GET['NewTransfer'])) {
	unset($_SESSION['Transfer' . $Identifier]);
}
if (isset($_SESSION['Transfer' . $Identifier]) and $_SESSION['Transfer' . $Identifier]->TrfID == '') {
	unset($_SESSION['Transfer' . $Identifier]);
}


if (isset($_POST['ProcessTransfer'])) {
	/*Ok Time To Post transactions to Inventory Transfers, and Update Posted variable & received Qty's  to LocTransfers */

	$PeriodNo = GetPeriod($_SESSION['Transfer' . $Identifier]->TranDate);
	$SQLTransferDate = FormatDateForSQL($_SESSION['Transfer' . $Identifier]->TranDate);

	$InputError = False;
	/*Start off hoping for the best */
	$i = 0;
	$TotalQuantity = 0;
	foreach ($_SESSION['Transfer' . $Identifier]->TransferItem as $TrfLine) {
		if (is_numeric(filter_number_format($_POST['Qty' . $i]))) {
			/*Update the quantity received from the inputs */
			$_SESSION['Transfer' . $Identifier]->TransferItem[$i]->Quantity = round(filter_number_format($_POST['Qty' . $i]), $_SESSION['Transfer' . $Identifier]->TransferItem[$i]->DecimalPlaces);
  		} elseif ($_POST['Qty' . $i] == '') {
			$_SESSION['Transfer' . $Identifier]->TransferItem[$i]->Quantity = 0;
		} else {
			prnMsg(_('The quantity entered for') . ' ' . $TrfLine->StockID . ' ' . _('is not numeric') . '. ' . _('All quantities must be numeric'), 'error');
			$InputError = True;
		}
		if (filter_number_format($_POST['Qty' . $i]) < 0) {
			prnMsg(_('The quantity entered for') . ' ' . $TrfLine->StockID . ' ' . _('is negative') . '. ' . _('All quantities must be for positive numbers greater than zero'), 'error');
			$InputError = True;
		}
		if ($TrfLine->PrevRecvQty + $TrfLine->Quantity > $TrfLine->ShipQty) {
			prnMsg(_('The Quantity entered plus the Quantity Previously Received can not be greater than the Total Quantity shipped for') . ' ' . $TrfLine->StockID, 'error');
			$InputError = True;
		}
		if (isset($_POST['CancelBalance' . $i]) and $_POST['CancelBalance' . $i] == 1) {
			$_SESSION['Transfer' . $Identifier]->TransferItem[$i]->CancelBalance = 1;
		} else {
			$_SESSION['Transfer' . $Identifier]->TransferItem[$i]->CancelBalance = 0;
		}
		$TotalQuantity += $TrfLine->Quantity;
		++$i;
	}
	/*end loop to validate and update the SESSION['Transfer'] data */
	if ($TotalQuantity < 0) {
		prnMsg(_('All quantities entered are less than zero') . '. ' . _('Please correct that and try again'), 'error');
		$InputError = True;
	}
	//exit;
	if (!$InputError) {
		/*All inputs must be sensible so make the stock movement records and update the locations stocks */

		$Result = DB_Txn_Begin(); // The Txn should affect the full transfer

		foreach ($_SESSION['Transfer' . $Identifier]->TransferItem as $TrfLine) {
			if ($TrfLine->Quantity >= 0) {

				/* Need to get the current location quantity will need it later for the stock movement */
				$SQL = "SELECT locstock.quantity
						FROM locstock
						WHERE locstock.stockid='" . $TrfLine->StockID . "'
						AND loccode= '" . $_SESSION['Transfer' . $Identifier]->StockLocationFrom . "'";

				$Result = DB_query($SQL, _('Could not retrieve the stock quantity at the dispatch stock location prior to this transfer being processed'));
				if (DB_num_rows($Result) == 1) {
					$LocQtyRow = DB_fetch_row($Result);
					$QtyOnHandPrior = $LocQtyRow[0];
				} else {
					/* There must actually be some error this should never happen */
					$QtyOnHandPrior = 0;
				}

				/* Insert the stock movement for the stock going out of the from location */
				$SQL = "INSERT INTO stockmoves (stockid,
												type,
												transno,
												loccode,
												trandate,
												userid,
												prd,
												reference,
												qty,
												newqoh)
					VALUES (
						'" . $TrfLine->StockID . "',
						16,
						'" . $_SESSION['Transfer' . $Identifier]->TrfID . "',
						'" . $_SESSION['Transfer' . $Identifier]->StockLocationFrom . "',
						'" . $SQLTransferDate . "',
						'" . $_SESSION['UserID'] . "',
						'" . $PeriodNo . "',
						'" . _('To') . ' ' . DB_escape_string($_SESSION['Transfer' . $Identifier]->StockLocationToName) . "',
						'" . round(-$TrfLine->Quantity, $TrfLine->DecimalPlaces) . "',
						'" . round($QtyOnHandPrior - $TrfLine->Quantity, $TrfLine->DecimalPlaces) . "'
					)";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record cannot be inserted because');
				$DbgMsg = _('The following SQL to insert the stock movement record was used');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

				/*Get the ID of the StockMove... */
				$StkMoveNo = DB_Last_Insert_ID('stockmoves', 'stkmoveno');

				/*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/

				if ($TrfLine->Controlled == 1) {
					foreach ($TrfLine->SerialItems as $Item) {
						/*We need to add or update the StockSerialItem record and
						The StockSerialMoves as well */

						/*First need to check if the serial items already exists or not in the location from */
						$SQL = "SELECT COUNT(*)
							FROM stockserialitems
							WHERE
							stockid='" . $TrfLine->StockID . "'
							AND loccode='" . $_SESSION['Transfer' . $Identifier]->StockLocationFrom . "'
							AND serialno='" . $Item->BundleRef . "'";

						$Result = DB_query($SQL, '<br />' . _('Could not determine if the serial item exists'));
						$SerialItemExistsRow = DB_fetch_row($Result);

						if ($SerialItemExistsRow[0] == 1) {

							$SQL = "UPDATE stockserialitems
										SET quantity= quantity - '" . $Item->BundleQty . "',
											expirationdate='" . FormatDateForSQL($Item->ExpiryDate) . "'
										WHERE stockid='" . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID . "'
											AND loccode='" . $_SESSION['Transfer' . $Identifier]->StockLocationFrom . "'
											AND serialno='" . $Item->BundleRef . "'";

							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated because');
							$DbgMsg = _('The following SQL to update the serial stock item record was used');
							$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
						} else {
							/*Need to insert a new serial item record */
							$SQL = "INSERT INTO stockserialitems (stockid,
																loccode,
																serialno,
																expirationdate,
																quantity
															) VALUES (
																'" . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID . "',
																'" . $_SESSION['Transfer' . $Identifier]->StockLocationFrom . "',
																'" . $Item->BundleRef . "',
																'" . FormatDateForSQL($Item->ExpiryDate) . "',
																'" . -$Item->BundleQty . "'
															)";

							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item for the stock being transferred out of the existing location could not be inserted because');
							$DbgMsg = _('The following SQL to update the serial stock item record was used');
							$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
						}


						/* now insert the serial stock movement */

						$SQL = "INSERT INTO stockserialmoves (
								stockmoveno,
								stockid,
								serialno,
								moveqty
							) VALUES (
								'" . $StkMoveNo . "',
								'" . $TrfLine->StockID . "',
								'" . $Item->BundleRef . "',
								'" . -$Item->BundleQty . "'
							)";
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
						$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
						$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

					}
					/* foreach controlled item in the serialitems array */
				}
				/*end if the transferred item is a controlled item */


				/* Need to get the current location quantity will need it later for the stock movement */
				$SQL = "SELECT locstock.quantity
						FROM locstock
						WHERE locstock.stockid='" . $TrfLine->StockID . "'
						AND loccode= '" . $_SESSION['Transfer' . $Identifier]->StockLocationTo . "'";

				$Result = DB_query($SQL, _('Could not retrieve the quantity on hand at the location being transferred to'));
				if (DB_num_rows($Result) == 1) {
					$LocQtyRow = DB_fetch_row($Result);
					$QtyOnHandPrior = $LocQtyRow[0];
				} else {
					// There must actually be some error this should never happen
					$QtyOnHandPrior = 0;
				}

// BEGIN: **********************************************************************
				// Insert outgoing inventory GL transaction if any of the locations has a GL account code:
				if(($_SESSION['Transfer']->StockLocationFromAccount != '' or $_SESSION['Transfer']->StockLocationToAccount != '')) {
					// Get the account code:
					if($_SESSION['Transfer']->StockLocationFromAccount != '') {
						$AccountCode = $_SESSION['Transfer']->StockLocationFromAccount;
					} else {
						$StockGLCode = GetStockGLCode($TrfLine->StockID);// Get Category's account codes.
						$AccountCode = $StockGLCode['stockact'];// Select account code for stock.
					}
					// Get the item cost:
					$SQLStandardCost = "SELECT stockcosts.materialcost + stockcosts.labourcost + stockcosts.overheadcost AS standardcost
										FROM stockcosts
										WHERE stockcosts.stockid ='" . $TrfLine->StockID . "'";
					$ErrMsg = _('The standard cost of the item cannot be retrieved because');
					$DbgMsg = _('The SQL that failed was');
					$Result = DB_query($SQLStandardCost, $ErrMsg, $DbgMsg);
					$MyRow = DB_fetch_array($Result);
					$StandardCost = $MyRow['standardcost'];// QUESTION: Standard cost for: Assembly (value="A") and Manufactured (value="M") items ?
					// Insert record:
					$SQL = "INSERT INTO gltrans (
							periodno,
							trandate,
							type,
							typeno,
							account,
							narrative,
							amount)
						VALUES (
							'" . $PeriodNo . "',
							'" . $SQLTransferDate . "',
							16,
							'" . $_SESSION['Transfer']->TrfID . "',
							'" . $AccountCode . "',
							'" . $_SESSION['Transfer']->StockLocationFrom . ' - ' . $TrfLine->StockID . ' x ' . $TrfLine->Quantity . ' @ ' . $StandardCost . "',
							'" . -$TrfLine->Quantity * $StandardCost . "'
						)";
					$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The outgoing inventory GL transacction record could not be inserted because');
					$DbgMsg =  _('The following SQL to insert records was used');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				}
// END: ************************************************************************

				// Insert the stock movement for the stock coming into the to location
				$SQL = "INSERT INTO stockmoves (stockid,
												type,
												transno,
												loccode,
												trandate,
												userid,
												prd,
												reference,
												qty,
												newqoh)
					VALUES (
						'" . $TrfLine->StockID . "',
						16,
						'" . $_SESSION['Transfer' . $Identifier]->TrfID . "',
						'" . $_SESSION['Transfer' . $Identifier]->StockLocationTo . "',
						'" . $SQLTransferDate . "',
						'" . $_SESSION['UserID'] . "',
						'" . $PeriodNo . "',
						'" . _('From') . ' ' . DB_escape_string($_SESSION['Transfer' . $Identifier]->StockLocationFromName) . "',
						'" . round($TrfLine->Quantity, $TrfLine->DecimalPlaces) . "',
						'" . round($QtyOnHandPrior + $TrfLine->Quantity, $TrfLine->DecimalPlaces) . "'
						)";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record for the incoming stock cannot be added because');
				$DbgMsg = _('The following SQL to insert the stock movement record was used');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);


				/*Get the ID of the StockMove... */
				$StkMoveNo = DB_Last_Insert_ID('stockmoves', 'stkmoveno');

				/*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/

				if ($TrfLine->Controlled == 1) {
					foreach ($TrfLine->SerialItems as $Item) {
						/*We need to add or update the StockSerialItem record and
						The StockSerialMoves as well */

						/*First need to check if the serial items already exists or not in the location to */
						$SQL = "SELECT COUNT(*)
							FROM stockserialitems
							WHERE
							stockid='" . $TrfLine->StockID . "'
							AND loccode='" . $_SESSION['Transfer' . $Identifier]->StockLocationTo . "'
							AND serialno='" . $Item->BundleRef . "'";

						$Result = DB_query($SQL, '<br />' . _('Could not determine if the serial item exists'));
						$SerialItemExistsRow = DB_fetch_row($Result);


						if ($SerialItemExistsRow[0] == 1) {

							$SQL = "UPDATE stockserialitems
										SET quantity= quantity + '" . $Item->BundleQty . "',
											expirationdate='" . FormatDateForSQL($Item->ExpiryDate) . "'
										WHERE stockid='" . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID . "'
											AND loccode='" . $_SESSION['Transfer' . $Identifier]->StockLocationTo . "'
											AND serialno='" . $Item->BundleRef . "'";

							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated for the quantity coming in because');
							$DbgMsg = _('The following SQL to update the serial stock item record was used');
							$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
						} else {
							/*Need to insert a new serial item record */
							$SQL = "INSERT INTO stockserialitems (stockid,
																loccode,
																serialno,
																expirationdate,
																quantity
															) VALUES (
																'" . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID . "',
																'" . $_SESSION['Transfer' . $Identifier]->StockLocationFrom . "',
																'" . $Item->BundleRef . "',
																'" . FormatDateForSQL($Item->ExpiryDate) . "',
																'" . -$Item->BundleQty . "'
															)";

							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record for the stock coming in could not be added because');
							$DbgMsg = _('The following SQL to update the serial stock item record was used');
							$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
						}


						/* now insert the serial stock movement */

						$SQL = "INSERT INTO stockserialmoves (
											stockmoveno,
											stockid,
											serialno,
											moveqty)
								VALUES (" . $StkMoveNo . ",
									'" . $TrfLine->StockID . "',
									'" . $Item->BundleRef . "',
									'" . $Item->BundleQty . "')";
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
						$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
						$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

					}
					/* foreach controlled item in the serialitems array */
				}
				/*end if the transfer item is a controlled item */

				$SQL = "UPDATE locstock
					SET quantity = quantity - '" . round($TrfLine->Quantity, $TrfLine->DecimalPlaces) . "'
					WHERE stockid='" . $TrfLine->StockID . "'
					AND loccode='" . $_SESSION['Transfer' . $Identifier]->StockLocationFrom . "'";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg = _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
// BEGIN: **********************************************************************
				// Insert incoming inventory GL transaction if any of the locations has a GL account code:
				if (($_SESSION['Transfer']->StockLocationFromAccount != '' or $_SESSION['Transfer']->StockLocationToAccount != '')) {
					// Get the account code:
					if($_SESSION['Transfer']->StockLocationToAccount !='') {
						$AccountCode = $_SESSION['Transfer']->StockLocationToAccount;
					} else {
						$StockGLCode = GetStockGLCode($TrfLine->StockID);// Get Category's account codes.
						$AccountCode = $StockGLCode['stockact'];// Select account code for stock.
					}
					// Get the item cost:
					$SQLStandardCost = "SELECT stockcosts.materialcost + stockcosts.labourcost + stockcosts.overheadcost AS standardcost
										FROM stockcosts
										WHERE stockcosts.stockid ='" . $TrfLine->StockID . "'";
					$ErrMsg = _('The standard cost of the item cannot be retrieved because');
					$DbgMsg = _('The SQL that failed was');
					$Result = DB_query($SQLStandardCost, $ErrMsg, $DbgMsg);
					$MyRow = DB_fetch_array($Result);
					$StandardCost = $MyRow['standardcost'];// QUESTION: Standard cost for: Assembly (value="A") and Manufactured (value="M") items ?
					// Insert record:
					$SQL = "INSERT INTO gltrans (
							periodno,
							trandate,
							type,
							typeno,
							account,
							narrative,
							amount)
						VALUES (
							'" . $PeriodNo . "',
							'" . $SQLTransferDate . "',
							16,
							'" . $_SESSION['Transfer']->TrfID . "',
							'" . $AccountCode . "',
							'" . $_SESSION['Transfer']->StockLocationTo . ' - ' . $TrfLine->StockID . ' x ' . $TrfLine->Quantity . ' @ ' . $StandardCost . "',
							'" . $TrfLine->Quantity * $StandardCost . "')";
					$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The incoming inventory GL transacction record could not be inserted because');
					$DbgMsg =  _('The following SQL to insert records was used');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				}
// END: ************************************************************************

				$SQL = "UPDATE locstock
					SET quantity = quantity + '" . round($TrfLine->Quantity, $TrfLine->DecimalPlaces) . "'
					WHERE stockid='" . $TrfLine->StockID . "'
					AND loccode='" . $_SESSION['Transfer' . $Identifier]->StockLocationTo . "'";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg = _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

				prnMsg(_('A stock transfer for item code') . ' - ' . $TrfLine->StockID . ' ' . $TrfLine->ItemDescription . ' ' . _('has been created from') . ' ' . $_SESSION['Transfer' . $Identifier]->StockLocationFromName . ' ' . _('to') . ' ' . $_SESSION['Transfer' . $Identifier]->StockLocationToName . ' ' . _('for a quantity of') . ' ' . $TrfLine->Quantity, 'success');

				if ($TrfLine->CancelBalance == 1) {
					RecordItemCancelledInTransfer($_SESSION['Transfer']->TrfID, $TrfLine->StockID, $TrfLine->Quantity);
					$SQL = "UPDATE loctransfers SET recqty = recqty + '" . round($TrfLine->Quantity, $TrfLine->DecimalPlaces) . "',
						shipqty = recqty + '" . round($TrfLine->Quantity, $TrfLine->DecimalPlaces) . "',
								recdate = '" . Date('Y-m-d H:i:s') . "'
						WHERE reference = '" . $_SESSION['Transfer' . $Identifier]->TrfID . "'
						AND stockid = '" . $TrfLine->StockID . "'";
				} else {
					$SQL = "UPDATE loctransfers SET recqty = recqty + '" . round($TrfLine->Quantity, $TrfLine->DecimalPlaces) . "',
								recdate = '" . Date('Y-m-d H:i:s') . "'
							WHERE reference = '" . $_SESSION['Transfer' . $Identifier]->TrfID . "'
								AND stockid = '" . $TrfLine->StockID . "'";
				}
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('Unable to update the Location Transfer Record');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				unset($_SESSION['Transfer' . $Identifier]->LineItem[$i]);
				unset($_POST['Qty' . $i]);
			} /*end if Quantity >= 0 */
			if ($TrfLine->CancelBalance == 1) {
				$SQL = "UPDATE loctransfers SET shipqty = recqty
						WHERE reference = '" . $_SESSION['Transfer']->TrfID . "'
						AND stockid = '" .  $TrfLine->StockID . "'";
				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('Unable to set the quantity received to the quantity shipped to cancel the balance on this transfer line');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				// send an email to the inventory manager about this cancellation (as can lead to employee fraud)
				if ($_SESSION['InventoryManagerEmail'] != '') {
					$ConfirmationText = _('Cancelled balance at transfer') . ': ' . $_SESSION['Transfer' . $Identifier]->TrfID . "\r\n" . _('From Location') . ': ' . $_SESSION['Transfer' . $Identifier]->StockLocationFrom . "\r\n" . _('To Location') . ': ' . $_SESSION['Transfer' . $Identifier]->StockLocationTo . "\r\n" . _('Stock code') . ': ' . $TrfLine->StockID . "\r\n" . _('Qty received') . ': ' . round($TrfLine->Quantity, $TrfLine->DecimalPlaces) . "\r\n" . _('By user') . ': ' . $_SESSION['UserID'] . "\r\n" . _('At') . ': ' . Date('Y-m-d H:i:s');
					$EmailSubject = _('Cancelled balance at transfer') . ' ' . $_SESSION['Transfer' . $Identifier]->TrfID;
					if ($_SESSION['SmtpSetting'] == 0) {
						mail($_SESSION['InventoryManagerEmail'], $EmailSubject, $ConfirmationText);
					} else {
						include('includes/htmlMimeMail.php');
						$Mail = new htmlMimeMail();
						$Mail->setSubject($EmailSubject);
						$Mail->setText($ConfirmationText);
						$Result = SendmailBySmtp($Mail, array(
							$_SESSION['InventoryManagerEmail']
						));
					}
				}
			}
			++$i;
		}
		/*end of foreach TransferItem */

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Unable to COMMIT the Stock Transfer transaction');
		DB_Txn_Commit();

		unset($_SESSION['Transfer' . $Identifier]->LineItem);
		unset($_SESSION['Transfer' . $Identifier]);
	}
	/* end of if no input errors */

}
/*end of PRocess Transfer */

if (isset($_GET['Trf_ID'])) {

	unset($_SESSION['Transfer' . $Identifier]);

	$SQL = "SELECT loctransfers.stockid,
				stockmaster.description,
				stockmaster.units,
				stockmaster.controlled,
				stockmaster.serialised,
				stockmaster.perishable,
				stockmaster.decimalplaces,
				loctransfers.shipqty,
				loctransfers.recqty,
				locations.locationname as shiplocationname,
				locations.glaccountcode as shipaccountcode,
				reclocations.locationname as reclocationname,
				locations.glaccountcode as shipaccountcode,
				loctransfers.shiploc,
				loctransfers.recloc
			FROM loctransfers
			INNER JOIN locations
				ON loctransfers.shiploc=locations.loccode
			INNER JOIN locations as reclocations
				ON loctransfers.recloc = reclocations.loccode
			INNER JOIN stockmaster
				ON loctransfers.stockid=stockmaster.stockid
			INNER JOIN locationusers
				ON locationusers.loccode=reclocations.loccode
				AND locationusers.userid='" .  $_SESSION['UserID'] . "'
				AND locationusers.canupd=1
			WHERE reference ='" . $_GET['Trf_ID'] . "' ORDER BY loctransfers.stockid";


	$ErrMsg = _('The details of transfer number') . ' ' . $_GET['Trf_ID'] . ' ' . _('could not be retrieved because') . ' ';
	$DbgMsg = _('The SQL to retrieve the transfer was');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

	if (DB_num_rows($Result) == 0) {
		echo '<h3>' . _('Transfer') . ' #' . $_GET['Trf_ID'] . ' ' . _('Does Not Exist') . '</h3><br />';
		include('includes/footer.inc');
		exit;
	}

	$MyRow = DB_fetch_array($Result);

	$_SESSION['Transfer' . $Identifier] = new StockTransfer($_GET['Trf_ID'], $MyRow['shiploc'], $MyRow['shiplocationname'], $MyRow['shipaccountcode'], $MyRow['recloc'], $MyRow['reclocationname'], $MyRow['recaccountcode'], Date($_SESSION['DefaultDateFormat']));
	/*Populate the StockTransfer TransferItem s array with the lines to be transferred */
	$i = 0;
	do {
		$_SESSION['Transfer' . $Identifier]->TransferItem[$i] = new LineItem($MyRow['stockid'], $MyRow['description'], $MyRow['shipqty'], $MyRow['units'], $MyRow['controlled'], $MyRow['serialised'], $MyRow['perishable'], $MyRow['decimalplaces']);
		$_SESSION['Transfer' . $Identifier]->TransferItem[$i]->PrevRecvQty = $MyRow['recqty'];
		$_SESSION['Transfer' . $Identifier]->TransferItem[$i]->Quantity = $MyRow['shipqty'] - $MyRow['recqty'];

		++$i;
		/*numerical index for the TransferItem[] array of LineItem s */

	} while ($MyRow = DB_fetch_array($Result));

}
/* $_GET['Trf_ID'] is set */

if (isset($_SESSION['Transfer' . $Identifier])) {
	//Begin Form for receiving shipment

	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/supplier.png" title="' . _('Dispatch') . '" alt="" />' . ' ' . $Title . '</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . urlencode($Identifier) . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	prnMsg(_('Please Verify Shipment Quantities Received'), 'info');

	$i = 0; //Line Item Array pointer

	echo '<table class="selection">
			<thead>
				<tr>
					<th colspan="7"><h3>' . _('Location Transfer Reference') . ' #' . $_SESSION['Transfer' . $Identifier]->TrfID . ' ' . _('from') . ' ' . $_SESSION['Transfer' . $Identifier]->StockLocationFromName . ' ' . _('to') . ' ' . $_SESSION['Transfer' . $Identifier]->StockLocationToName . '</h3></th>
				</tr>
				<tr>
					<th class="SortedColumn">' . _('Item Code') . '</th>
					<th class="SortedColumn">' . _('Item Description') . '</th>
					<th>' . _('Quantity Dispatched') . '</th>
					<th>' . _('Quantity Received') . '</th>
					<th>' . _('Quantity To Receive') . '</th>
					<th>' . _('Units') . '</th>
					<th>' . _('Cancel Balance') . '</th>
				</tr>
			</thead>';

	echo '<tbody>';
	$k = 0;
	foreach ($_SESSION['Transfer' . $Identifier]->TransferItem as $TrfLine) {
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			++$k;
		}

		echo '<td>' . $TrfLine->StockID . '</td>
			<td>' . $TrfLine->ItemDescription . '</td>';

		echo '<td class="number">' . locale_number_format($TrfLine->ShipQty, $TrfLine->DecimalPlaces) . '</td>';
		if (isset($_POST['Qty' . $i]) and is_numeric(filter_number_format($_POST['Qty' . $i]))) {

			$_SESSION['Transfer' . $Identifier]->TransferItem[$i]->Quantity = round(filter_number_format($_POST['Qty' . $i]), $TrfLine->DecimalPlaces);

			$Qty = round(filter_number_format($_POST['Qty' . $i]), $TrfLine->DecimalPlaces);

		} else if ($TrfLine->Controlled == 1) {
			if (sizeOf($TrfLine->SerialItems) == 0) {
				$Qty = 0;
			} else {
				$Qty = $TrfLine->Quantity;
			}
		} else {
			$Qty = $TrfLine->Quantity;
		}
		echo '<td class="number">' . locale_number_format($TrfLine->PrevRecvQty, $TrfLine->DecimalPlaces) . '</td>';

		if ($TrfLine->Controlled == 1) {
			echo '<td class="number">
					<input type="hidden" name="Qty' . $i . '" value="' . locale_number_format($Qty, $TrfLine->DecimalPlaces) . '" />
					<a href="' . $RootPath . '/StockTransferControlled.php?identifier=' . urlencode($Identifier) . '&TransferItem=' . urlencode($i) . '" />' . $Qty . '</a>
				</td>';
		} else {
			echo '<td><input type="text" class="number" name="Qty' . $i . '" required="required" maxlength="10" size="auto" value="' . locale_number_format($Qty, $TrfLine->DecimalPlaces) . '" /></td>';
		}

		echo '<td>' . $TrfLine->PartUnit . '</td>';

		echo '<td><input type="checkbox" name="CancelBalance' . $i . '" value="1" /></td>';


		if ($TrfLine->Controlled == 1) {
			if ($TrfLine->Serialised == 1) {
				echo '<td><a href="' . $RootPath . '/StockTransferControlled.php?identifier=' . urlencode($Identifier) . '&TransferItem=' . urlencode($i) . '">' . _('Enter Serial Numbers') . '</a></td>';
			} else {
				echo '<td><a href="' . $RootPath . '/StockTransferControlled.php?identifier=' . urlencode($Identifier) . '&TransferItem=' . urlencode($i) . '">' . _('Enter Batch Refs') . '</a></td>';
			}
		}

		echo '</tr>';

		++$i;
		/* the array of TransferItem s is indexed numerically and i matches the index no */
	}
	/*end of foreach TransferItem */

	echo '</tbody>
		</table>
		<div class="centre">
			<input type="submit" name="ProcessTransfer" value="' . _('Process Inventory Transfer') . '" />
			<br />
		</div>
	</form>';
	echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?NewTransfer=true">' . _('Select A Different Transfer') . '</a>';

} else {
	/*Not $_SESSION['Transfer' . $Identifier] set */

	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/supplier.png" title="' . _('Dispatch') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" id="form1">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	$SQL = "SELECT locationname,
					locations.loccode
				FROM locations
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				ORDER BY locationname";
	$LocResult = DB_query($SQL);

	echo '<table class="selection">';
	echo '<tr>
			<td>' . _('Select Location Receiving Into') . ':</td>
			<td>';
	echo '<select required="required" name="RecLocation" onchange="ReloadForm(form1.RefreshTransferList)">';
	if (!isset($_POST['RecLocation'])) {
		$_POST['RecLocation'] = $_SESSION['UserStockLocation'];
	}
	while ($MyRow = DB_fetch_array($LocResult)) {
		if ($MyRow['loccode'] == $_POST['RecLocation']) {
			echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	}
	echo '</select>
		<input type="submit" name="RefreshTransferList" value="' . _('Refresh Transfer List') . '" /></td>
		</tr>
		</table>';

	$SQL = "SELECT DISTINCT reference,
				locations.locationname as trffromloc,
				shipdate
			FROM loctransfers INNER JOIN locations
				ON loctransfers.shiploc=locations.loccode
			WHERE recloc='" . $_POST['RecLocation'] . "'
			AND recqty < shipqty";

	$TrfResult = DB_query($SQL);
	if (DB_num_rows($TrfResult) > 0) {
		$LocSql = "SELECT locationname FROM locations WHERE loccode='" . $_POST['RecLocation'] . "'";
		$LocResult = DB_query($LocSql);
		$LocRow = DB_fetch_array($LocResult);
		echo '<table class="selection">';
		echo '<tr>
				<th colspan="4"><h3>' . _('Pending Transfers Into') . ' ' . $LocRow['locationname'] . '</h3></th>
			</tr>';
		echo '<tr>
				<th>' . _('Transfer Ref') . '</th>
				<th>' . _('Transfer From') . '</th>
				<th>' . _('Dispatch Date') . '</th>
			</tr>';
		$k = 0;
		while ($MyRow = DB_fetch_array($TrfResult)) {

			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				++$k;
			}
			echo '<td class="number">' . $MyRow['reference'] . '</td>
					<td>' . $MyRow['trffromloc'] . '</td>
					<td>' . ConvertSQLDateTime($MyRow['shipdate']) . '</td>
					<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Trf_ID=' . $MyRow['reference'] . '">' . _('Receive') . '</a></td>
					</tr>';
		}
		echo '</table>';
	} else if (!isset($_POST['ProcessTransfer'])) {
		prnMsg(_('There are no incoming transfers to this location'), 'info');
	}
	echo '</form>';
}

function RecordItemCancelledInTransfer($TransferReference, $StockID, $CancelQty) {
	$SQL = "INSERT INTO loctransfercancellations (reference,
												stockid,
												cancelqty,
												canceldate,
												canceluserid
											) VALUES (
												'" . $TransferReference . "',
												'" . $StockID . "',
												(SELECT (l2.shipqty-l2.recqty)
													FROM loctransfers AS l2
													WHERE l2.reference = '" . $TransferReference . "'
														AND l2.stockid ='" . $StockID . "') - " . $CancelQty . ",
												'" . Date('Y-m-d H:i:s') . "',
												'" . $_SESSION['UserID'] . "'
											)";
	$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The transfer cancellation record could not be inserted because');
	$DbgMsg =  _('The following SQL to insert records was used');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
}

include('includes/footer.inc');
?>