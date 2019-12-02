<?php
/* Entry of point to point stock location transfers of a single part. */
include ('includes/DefineSerialItems.php');
include ('includes/DefineStockTransfers.php');

include ('includes/session.php');
$Title = _('Stock Transfers'); // Screen identification.
$ViewTopic = 'Inventory'; // Filename's id in ManualContents.php's TOC.
$BookMark = 'LocationTransfers'; // Anchor's id in the manual's html document.
include ('includes/header.php');
include ('includes/SQL_CommonFunctions.php');

if (empty($_GET['identifier'])) {
	/*unique session identifier to ensure that there is no conflict with other order entry sessions on the same machine  */
	$Identifier = date('U');
} else {
	$Identifier = $_GET['identifier'];
}

if (isset($_POST['NewTransfer'])) {
	$_GET['NewTransfer'] = $_POST['NewTransfer'];
}

if (isset($_POST['StockID'])) {
	$_GET['StockID'] = $_POST['StockID'];
}

if (isset($_GET['New']) or isset($_GET['NewTransfer'])) {
	unset($_SESSION['Transfer' . $Identifier]);
}

if (isset($_GET['From'])) {
	$_POST['StockLocationFrom'] = $_GET['From'];
	$_POST['StockLocationTo'] = $_GET['To'];
	$_POST['Quantity'] = $_GET['Quantity'];
}

if (isset($_POST['CheckCode'])) {

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', _('Dispatch'), '" alt="" />
			', ' ', _('Select Item to Transfer'), '
		</p>';

	if (mb_strlen($_POST['StockText']) > 0) {
		$SQL = "SELECT stockid,
					description
			 FROM stockmaster
			 WHERE description " . LIKE . " '%" . $_POST['StockText'] . "%'";
	} else {
		$SQL = "SELECT stockid,
					description
			  FROM stockmaster
			  WHERE stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'";
	}
	$ErrMsg = _('The stock information cannot be retrieved because');
	$DbgMsg = _('The SQL to get the stock description was');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<table>
			<thead>
				<tr>
					<th class="SortedColumn">', _('Stock Code'), '</th>
					<th class="SortedColumn">', _('Stock Description'), '</th>
					<th></th>
				</tr>
			</thead>';

	echo '<tbody>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<input type="hidden" name="Identifier" value="', $Identifier, '" />';
		echo '<input type="hidden" name="NewTransfer" value="Yes" />';
		echo '<input type="hidden" name="StockLocationFrom" value="', $_POST['StockLocationFrom'], '" />';
		echo '<input type="hidden" name="StockLocationTo" value="', $_POST['StockLocationTo'], '" />';
		echo '<tr class="striped_row">
				<td>', $MyRow['stockid'], '</td>
				<td>', $MyRow['description'], '</td>
				<td><a href="', $RootPath, '/StockTransfers.php?identifier=', urlencode($Identifier), '&StockID=', urlencode($MyRow['stockid']), '&amp;Description=', urlencode($MyRow['description']), '&amp;NewTransfer=Yes&amp;Quantity=', urlencode(filter_number_format($_POST['Quantity'])), '&amp;From=', urlencode($_POST['StockLocationFrom']), '&amp;To=', urlencode($_POST['StockLocationTo']), '">', _('Transfer'), '</a></td>
				<td><input type="submit" name="StockID" value="', $MyRow['stockid'], '" /></td>
			</tr>';

	}
	echo '</tbody>';
	echo '</table>';
	echo '</form>';
	include ('includes/footer.php');
	exit;
}

$NewTransfer = false;
/*initialise this first then determine from form inputs */

if (isset($_GET['NewTransfer'])) {
	unset($_SESSION['Transfer' . $Identifier]);
	unset($_SESSION['TransferItem']);
	/*this is defined in bulk transfers but needs to be unset for individual transfers */
	$NewTransfer = $_GET['NewTransfer'];
}

if (isset($_POST['StockID'])) {
	/* initiate a new transfer only if the StockID is different to the previous entry */
	if (isset($_SESSION['Transfer' . $Identifier]->TransferItem[0])) {
		if ($_POST['StockID'] != $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID) {
			unset($_SESSION['Transfer' . $Identifier]);
			$NewTransfer = true;
		}
	} else {
		/* _SESSION['Transfer']->TransferItem[0] is not set so */
		$NewTransfer = true;
	}
}

if ($NewTransfer and isset($_GET['StockID'])) {
	$_POST['StockID'] = $_GET['StockID'];
	if (!isset($_POST['StockLocationFrom'])) {
		$_POST['StockLocationFrom'] = '';
	}
	if (!isset($_POST['StockLocationTo'])) {
		$_POST['StockLocationTo'] = '';
	}
	if (!isset($_POST['Quantity'])) {
		$_POST['Quantity'] = 0;
	}

	$SQL = "SELECT locationname, glaccountcode FROM locations WHERE loccode='" . $_POST['StockLocationFrom'] . "'";
	$Result = DB_query($SQL);
	$MyFromLocationRow = DB_fetch_array($Result);

	$SQL = "SELECT locationname, glaccountcode FROM locations WHERE loccode='" . $_POST['StockLocationTo'] . "'";
	$Result = DB_query($SQL);
	$MyToLocationRow = DB_fetch_array($Result);

	$_SESSION['Transfer' . $Identifier] = new StockTransfer(0, $_POST['StockLocationFrom'], $MyFromLocationRow['locationname'], $MyFromLocationRow['glaccountcode'], $_POST['StockLocationTo'], $MyToLocationRow['locationname'], $MyToLocationRow['glaccountcode'], Date($_SESSION['DefaultDateFormat']));
	$_SESSION['Transfer' . $Identifier]->TrfID = $Identifier;

	$SQL = "SELECT description,
					units,
					mbflag,
					stockcosts.materialcost+stockcosts.labourcost+stockcosts.overheadcost as standardcost,
					controlled,
					serialised,
					perishable,
					decimalplaces
				FROM stockmaster
				LEFT JOIN stockcosts
					ON stockmaster.stockid=stockcosts.stockid
					AND stockcosts.succeeded=0
				WHERE stockcosts.stockid='" . trim(mb_strtoupper($_POST['StockID'])) . "'";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) == 0) {
		prnMsg(_('Unable to locate Stock Code') . ' ' . mb_strtoupper($_POST['StockID']), 'error');
	} elseif (DB_num_rows($Result) > 0) {
		$MyRow = DB_fetch_array($Result);
		$_SESSION['Transfer' . $Identifier]->TransferItem[0] = new LineItem(trim(mb_strtoupper($_POST['StockID'])), $MyRow['description'], filter_number_format($_POST['Quantity']), $MyRow['units'], $MyRow['controlled'], $MyRow['serialised'], $MyRow['perishable'], $MyRow['decimalplaces']);

		$_SESSION['Transfer' . $Identifier]->TransferItem[0]->StandardCost = $MyRow['standardcost'];

		if ($MyRow['mbflag'] == 'D' or $MyRow['mbflag'] == 'A' or $MyRow['mbflag'] == 'K') {
			prnMsg(_('The part entered is either or a dummy part or an assembly or a kit-set part') . '. ' . _('These parts are not physical parts and no stock holding is maintained for them') . '. ' . _('Stock Transfers are therefore not possible'), 'warn');
			echo '<hr />';
			echo '<a href="', $RootPath, '/StockTransfers.php?NewTransfer=Yes">', _('Enter another Transfer'), '</a>';
			unset($_SESSION['Transfer' . $Identifier]);
			include ('includes/footer.php');
			exit;
		}
	}
}

if (isset($_SESSION['Transfer' . $Identifier]->StockLocationFrom)) {
	$SQL = "SELECT id FROM container WHERE parentid='" . $_SESSION['Transfer' . $Identifier]->StockLocationFrom . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) > 0) {
		$FromWarehouseDefined = true;
	} else {
		$FromWarehouseDefined = false;
	}
}

if (isset($_SESSION['Transfer' . $Identifier]->StockLocationTo)) {
	$SQL = "SELECT id FROM container WHERE parentid='" . $_SESSION['Transfer' . $Identifier]->StockLocationTo . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) > 0) {
		$ToWarehouseDefined = true;
	} else {
		$ToWarehouseDefined = false;
	}
}

if (isset($_POST['Quantity']) and isset($_SESSION['Transfer' . $Identifier]->TransferItem[0]->Controlled) and $_SESSION['Transfer' . $Identifier]->TransferItem[0]->Controlled == 0) {

	$_SESSION['Transfer' . $Identifier]->TransferItem[0]->Quantity = filter_number_format($_POST['Quantity']);

}

if (isset($_POST['StockLocationFrom']) and $_POST['StockLocationFrom'] != $_SESSION['Transfer' . $Identifier]->StockLocationFrom) {

	$_SESSION['Transfer' . $Identifier]->StockLocationFrom = $_POST['StockLocationFrom'];
	$_SESSION['Transfer' . $Identifier]->StockLocationTo = $_POST['StockLocationTo'];
	$_SESSION['Transfer' . $Identifier]->TransferItem[0]->Quantity = filter_number_format($_POST['Quantity']);
	$_SESSION['Transfer' . $Identifier]->TransferItem[0]->SerialItems = array();
}
if (isset($_POST['StockLocationTo'])) {
	$_SESSION['Transfer' . $Identifier]->StockLocationTo = $_POST['StockLocationTo'];
}

if (isset($_POST['EnterTransfer'])) {

	$Result = DB_query("SELECT * FROM stockmaster WHERE stockid='" . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID . "'");
	$MyRow = DB_fetch_row($Result);
	$InputError = false;
	if (DB_num_rows($Result) == 0) {
		prnMsg(_('The entered item code does not exist'), 'error');
		$InputError = true;
	} elseif (!is_numeric($_SESSION['Transfer' . $Identifier]->TransferItem[0]->Quantity)) {
		prnMsg(_('The quantity entered must be numeric'), 'error');
		$InputError = true;
	} elseif ($_SESSION['Transfer' . $Identifier]->TransferItem[0]->Quantity <= 0) {
		prnMsg(_('The quantity entered must be a positive number greater than zero'), 'error');
		$InputError = true;
	}
	if ($_SESSION['Transfer' . $Identifier]->StockLocationFrom == $_SESSION['Transfer' . $Identifier]->StockLocationTo and $_POST['ToContainer'] == $_POST['FromContainer']) {
		prnMsg(_('The locations to transfer from and to must be different'), 'error');
		$InputError = true;
	}

	if ($InputError == False) {
		/*All inputs must be sensible so make the stock movement records and update the locations stocks */

		$TransferNumber = GetNextTransNo(16);
		$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']));
		$SQLTransferDate = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));

		$Result = DB_Txn_Begin();

		// Need to get the current location quantity will need it later for the stock movement
		$SQL = "SELECT locstock.quantity
				FROM locstock
				WHERE locstock.stockid='" . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID . "'
				AND loccode= '" . $_SESSION['Transfer' . $Identifier]->StockLocationFrom . "'";

		$ErrMsg = _('Could not retrieve the QOH at the sending location because');
		$DbgMsg = _('The SQL that failed was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

		if (DB_num_rows($Result) == 1) {
			$LocQtyRow = DB_fetch_row($Result);
			$QtyOnHandPrior = $LocQtyRow[0];
		} else {
			// There must actually be some error this should never happen
			$QtyOnHandPrior = 0;
		}
		if ($_SESSION['ProhibitNegativeStock'] == 1 and $QtyOnHandPrior < $_SESSION['Transfer' . $Identifier]->TransferItem[0]->Quantity) {
			prnMsg(_('There is insufficient stock to make this transfer and') . ' ' . $ProjectName . ' ' . _('is setup to prevent negative stock'), 'warn');
			include ('includes/footer.php');
			exit;
		}
		// BEGIN: **********************************************************************
		// Insert outgoing inventory GL transaction if any of the locations has a GL account code:
		if (($_SESSION['Transfer' . $Identifier]->StockLocationFromAccount != '' or $_SESSION['Transfer' . $Identifier]->StockLocationToAccount != '') and ($_SESSION['Transfer' . $Identifier]->StockLocationFromAccount != $_SESSION['Transfer' . $Identifier]->StockLocationToAccount)) {
			// Get the account code:
			if ($_SESSION['Transfer' . $Identifier]->StockLocationToAccount != '') {
				$AccountCode = $_SESSION['Transfer' . $Identifier]->StockLocationToAccount;
			} else {
				$StockGLCode = GetStockGLCode($_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID); // Get Category's account codes.
				$AccountCode = $StockGLCode['stockact']; // Select account code for stock.
				
			}
			// Get the item cost:
			$SQLStandardCost = "SELECT stockcosts.materialcost + stockcosts.labourcost + stockcosts.overheadcost AS standardcost
								FROM stockcosts
								WHERE stockcosts.stockid ='" . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID . "'";
			$ErrMsg = _('The standard cost of the item cannot be retrieved because');
			$DbgMsg = _('The SQL that failed was');
			$Result = DB_query($SQLStandardCost, $ErrMsg, $DbgMsg);
			$MyRow = DB_fetch_array($Result);
			$StandardCost = $MyRow['standardcost']; // QUESTION: Standard cost for: Assembly (value="A") and Manufactured (value="M") items ?
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
					'" . $TransferNumber . "',
					'" . $AccountCode . "',
					'" . $_SESSION['Transfer' . $Identifier]->StockLocationFrom . ' - ' . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID . ' x ' . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->Quantity . ' @ ' . $StandardCost . "',
					'" . -$_SESSION['Transfer' . $Identifier]->TransferItem[0]->Quantity * $StandardCost . "')";
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The outgoing inventory GL transacction record could not be inserted because');
			$DbgMsg = _('The following SQL to insert records was used');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
			foreach ($_POST['tag'] as $Tag) {
				$SQL = "INSERT INTO gltags VALUES ( LAST_INSERT_ID(),
													'" . $Tag . "')";
				$ErrMsg = _('Cannot insert a GL tag for the adjustment line because');
				$DbgMsg = _('The SQL that failed to insert the GL tag record was');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
			}
		}
		// END: ************************************************************************
		// Insert the stock movement for the stock going out of the from location
		$SQL = "INSERT INTO stockmoves (stockid,
										type,
										transno,
										loccode,
										container,
										trandate,
										userid,
										prd,
										reference,
										qty,
										newqoh)
				VALUES (
						'" . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID . "',
						16,
						'" . $TransferNumber . "',
						'" . $_SESSION['Transfer' . $Identifier]->StockLocationFrom . "',
						'" . $_POST['FromContainer'] . "',
						'" . $SQLTransferDate . "',
						'" . $_SESSION['UserID'] . "',
						'" . $PeriodNo . "',
						'To " . $_SESSION['Transfer' . $Identifier]->StockLocationTo . "',
						'" . round(-$_SESSION['Transfer' . $Identifier]->TransferItem[0]->Quantity, $_SESSION['Transfer' . $Identifier]->TransferItem[0]->DecimalPlaces) . "',
						'" . ($QtyOnHandPrior - round($_SESSION['Transfer' . $Identifier]->TransferItem[0]->Quantity, $_SESSION['Transfer' . $Identifier]->TransferItem[0]->DecimalPlaces)) . "'
						)";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record cannot be inserted because');
		$DbgMsg = _('The following SQL to insert the stock movement record was used');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

		/*Get the ID of the StockMove... */
		$StkMoveNo = DB_Last_Insert_ID('stockmoves', 'stkmoveno');

		/*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/

		if ($_SESSION['Transfer' . $Identifier]->TransferItem[0]->Controlled == 1) {
			foreach ($_SESSION['Transfer' . $Identifier]->TransferItem[0]->SerialItems as $Item) {
				/*We need to add or update the StockSerialItem record and
				 The StockSerialMoves as well */

				/*First need to check if the serial items already exists or not in the location from */
				$SQL = "SELECT COUNT(*)
						FROM stockserialitems
						WHERE
						stockid='" . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID . "'
						AND loccode='" . $_SESSION['Transfer' . $Identifier]->StockLocationFrom . "'
						AND serialno='" . $Item->BundleRef . "'";

				$ErrMsg = _('The entered item code does not exist');
				$Result = DB_query($SQL, $ErrMsg);
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
										quantity)
						VALUES ('" . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID . "',
						'" . $_SESSION['Transfer' . $Identifier]->StockLocationFrom . "',
						'" . $Item->BundleRef . "',
						'" . FormatDateForSQL($Item->ExpiryDate) . "',
						'" . -$Item->BundleQty . "')";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be added because');
					$DbgMsg = _('The following SQL to insert the serial stock item record was used');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				}

				/* now insert the serial stock movement */

				$SQL = "INSERT INTO stockserialmoves (
								stockmoveno,
								stockid,
								serialno,
								moveqty)
						VALUES (
							'" . $StkMoveNo . "',
							'" . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID . "',
							'" . $Item->BundleRef . "',
							'" . $Item->BundleQty . "'
							)";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
				$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			}
			/* foreach controlled item in the serialitems array */
		}
		/*end if the transferred item is a controlled item */

		// Need to get the current location quantity will need it later for the stock movement
		$SQL = "SELECT locstock.quantity
				FROM locstock
				WHERE locstock.stockid='" . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID . "'
				AND loccode= '" . $_SESSION['Transfer' . $Identifier]->StockLocationTo . "'";
		$ErrMsg = _('Could not retrieve QOH at the destination because');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
		if (DB_num_rows($Result) == 1) {
			$LocQtyRow = DB_fetch_row($Result);
			$QtyOnHandPrior = $LocQtyRow[0];
		} else {
			// There must actually be some error this should never happen
			$QtyOnHandPrior = 0;
		}
		// BEGIN: **********************************************************************
		// Insert incoming inventory GL transaction if any of the locations has a GL account code:
		if (($_SESSION['Transfer' . $Identifier]->StockLocationFromAccount != '' or $_SESSION['Transfer' . $Identifier]->StockLocationToAccount != '') and ($_SESSION['Transfer' . $Identifier]->StockLocationFromAccount != $_SESSION['Transfer' . $Identifier]->StockLocationToAccount)) {
			// Get the account code:
			if ($_SESSION['Transfer' . $Identifier]->StockLocationToAccount != '') {
				$AccountCode = $_SESSION['Transfer' . $Identifier]->StockLocationToAccount;
			} else {
				$StockGLCode = GetStockGLCode($_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID); // Get Category's account codes.
				$AccountCode = $StockGLCode['stockact']; // Select account code for stock.
				
			}
			// Get the item cost:
			$SQLStandardCost = "SELECT stockcosts.materialcost + stockcosts.labourcost + stockcosts.overheadcost AS standardcost
								FROM stockcosts
								WHERE stockcosts.stockid ='" . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID . "'";
			$ErrMsg = _('The standard cost of the item cannot be retrieved because');
			$DbgMsg = _('The SQL that failed was');
			$Result = DB_query($SQLStandardCost, $ErrMsg, $DbgMsg);
			$MyRow = DB_fetch_array($Result);
			$StandardCost = $MyRow['standardcost']; // QUESTION: Standard cost for: Assembly (value="A") and Manufactured (value="M") items ?
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
					'" . $TransferNumber . "',
					'" . $AccountCode . "',
					'" . $_SESSION['Transfer' . $Identifier]->StockLocationTo . ' - ' . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID . ' x ' . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->Quantity . ' @ ' . $StandardCost . "',
					'" . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->Quantity * $StandardCost . "')";
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The incoming inventory GL transacction record could not be inserted because');
			$DbgMsg = _('The following SQL to insert records was used');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
			foreach ($_POST['tag'] as $Tag) {
				$SQL = "INSERT INTO gltags VALUES ( LAST_INSERT_ID(),
													'" . $Tag . "')";
				$ErrMsg = _('Cannot insert a GL tag for the adjustment line because');
				$DbgMsg = _('The SQL that failed to insert the GL tag record was');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
			}
		}
		// END: ************************************************************************
		// Insert the stock movement for the stock coming into the to location
		$SQL = "INSERT INTO stockmoves (stockid,
						type,
						transno,
						loccode,
						container,
						trandate,
						userid,
						prd,
						reference,
						qty,
						newqoh)
			VALUES ('" . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID . "',
					16,
					'" . $TransferNumber . "',
					'" . $_SESSION['Transfer' . $Identifier]->StockLocationTo . "',
					'" . $_POST['ToContainer'] . "',
					'" . $SQLTransferDate . "',
					'" . $_SESSION['UserID'] . "',
					'" . $PeriodNo . "',
					'" . _('From') . " " . $_SESSION['Transfer' . $Identifier]->StockLocationFrom . "',
					'" . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->Quantity . "',
					'" . round($QtyOnHandPrior + $_SESSION['Transfer' . $Identifier]->TransferItem[0]->Quantity, $_SESSION['Transfer' . $Identifier]->TransferItem[0]->DecimalPlaces) . "')";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record cannot be inserted because');
		$DbgMsg = _('The following SQL to insert the stock movement record was used');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

		/*Get the ID of the StockMove... */
		$StkMoveNo = DB_Last_Insert_ID('stockmoves', 'stkmoveno');

		/*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/

		if ($_SESSION['Transfer' . $Identifier]->TransferItem[0]->Controlled == 1) {
			foreach ($_SESSION['Transfer' . $Identifier]->TransferItem[0]->SerialItems as $Item) {
				/*We need to add or update the StockSerialItem record and
				 The StockSerialMoves as well */

				/*First need to check if the serial items already exists or not in the location from */
				$SQL = "SELECT COUNT(*)
						FROM stockserialitems
						WHERE stockid='" . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID . "'
						AND loccode='" . $_SESSION['Transfer' . $Identifier]->StockLocationTo . "'
						AND serialno='" . $Item->BundleRef . "'";

				$ErrMsg = _('Could not determine if the serial item exists in the transfer to location');
				$Result = DB_query($SQL, $ErrMsg);
				$SerialItemExistsRow = DB_fetch_row($Result);

				if ($SerialItemExistsRow[0] == 1) {

					$SQL = "UPDATE stockserialitems
							SET quantity= quantity + '" . $Item->BundleQty . "',
								expirationdate='" . FormatDateForSQL($Item->ExpiryDate) . "'
							WHERE stockid='" . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID . "'
							AND loccode='" . $_SESSION['Transfer' . $Identifier]->StockLocationTo . "'
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
														quantity,
														qualitytext
													) VALUES (
														'" . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID . "',
														'" . $_SESSION['Transfer' . $Identifier]->StockLocationTo . "',
														'" . $Item->BundleRef . "',
														'" . FormatDateForSQL($Item->ExpiryDate) . "',
														'" . $Item->BundleQty . "',
														''
													)";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be added because');
					$DbgMsg = _('The following SQL to insert the serial stock item record was used') . ':';
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				}

				/* now insert the serial stock movement */

				$SQL = "INSERT INTO stockserialmoves (stockmoveno,
									stockid,
									serialno,
									moveqty)
							VALUES ('" . $StkMoveNo . "',
								'" . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID . "',
								'" . $Item->BundleRef . "',
								'" . $Item->BundleQty . "')";
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
				$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			}
			/* foreach controlled item in the serialitems array */
		}
		/*end if the transfer item is a controlled item */

		$SQL = "UPDATE locstock SET quantity = quantity - '" . round($_SESSION['Transfer' . $Identifier]->TransferItem[0]->Quantity, $_SESSION['Transfer' . $Identifier]->TransferItem[0]->DecimalPlaces) . "'
				WHERE stockid='" . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID . "'
				AND loccode='" . $_SESSION['Transfer' . $Identifier]->StockLocationFrom . "'";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
		$DbgMsg = _('The following SQL to update the location stock record was used');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

		$SQL = "UPDATE locstock
				SET quantity = quantity + '" . round($_SESSION['Transfer' . $Identifier]->TransferItem[0]->Quantity, $_SESSION['Transfer' . $Identifier]->TransferItem[0]->DecimalPlaces) . "'
				WHERE stockid='" . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID . "'
				AND loccode='" . $_SESSION['Transfer' . $Identifier]->StockLocationTo . "'";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
		$DbgMsg = _('The following SQL to update the location stock record was used');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

		$Result = DB_Txn_Commit();

		prnMsg(_('An inventory transfer of') . ' ' . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID . ' - ' . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->ItemDescription . ' ' . _('has been created from') . ' ' . $_SESSION['Transfer' . $Identifier]->StockLocationFrom . ' ' . _('to') . ' ' . $_SESSION['Transfer' . $Identifier]->StockLocationTo . ' ' . _('for a quantity of') . ' ' . $_SESSION['Transfer' . $Identifier]->TransferItem[0]->Quantity, 'success');
		echo '<a href="PDFStockTransfer.php?identifier=', urlencode($Identifier), '&TransferNo=', urlencode($TransferNumber), '">', _('Print Transfer Note'), '</a>';
		unset($_SESSION['Transfer' . $Identifier]);
		include ('includes/footer.php');
		exit;
	}

}

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/supplier.png" title="', _('Dispatch'), '" alt="" />', ' ', $Title, '
	</p>';

if (isset($_GET['NewTransfer']) or isset($_GET['New'])) {

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', urlencode($Identifier), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	if (!isset($_GET['Description'])) {
		$_GET['Description'] = '';
	}

	echo '<fieldset>
			<legend>', _('Transfer Details'), '</legend>';

	if (isset($_GET['NewTransfer'])) {
		echo '<field>
				<label for="StockCode">', _('Stock Code'), ':</label>
				<div class="fieldtext">', $_GET['StockID'], '</div>
			</field>';
	} else {
		echo '<field>
				<label for="StockText">', _('Partial Description'), ':</label>
				<input type="text" name="StockText" size="21" autofocus="autofocus" value="', stripslashes($_GET['Description']), '" />
				<fieldhelp>', _('Enter all or part of a description for a stock item.'), '</fieldhelp>
			</field>';

		echo '<field>
				<label for="StockCode">', _('Partial Stock Code'), ':</label>
				<input type="text" name="StockCode" size="21" value="" maxlength="20" />
				<fieldhelp>', _('Enter all or part of a code for a stock item.'), '</fieldhelp>
			</field>';
	}

	echo '<h1>', _('AND'), '</h1>';

	echo '<field>
			<label for="StockLocationFrom">', _('From Stock Location'), ':</label>
			<select required="required" name="StockLocationFrom">';

	$SQL = "SELECT locationname,
					locations.loccode
				FROM locations
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" . $_SESSION['UserID'] . "'
					AND locationusers.canupd=1";

	$ResultStkLocs = DB_query($SQL);
	while ($MyRow = DB_fetch_array($ResultStkLocs)) {
		if (isset($_SESSION['Transfer' . $Identifier]->StockLocationFrom)) {
			if ($MyRow['loccode'] == $_SESSION['Transfer' . $Identifier]->StockLocationFrom) {
				echo '<option selected="selected" value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
			} else {
				echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
			}
		} elseif (isset($_SESSION['Transfer' . $Identifier]) and $MyRow['loccode'] == $_SESSION['UserStockLocation']) {
			echo '<option selected="selected" value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
			$_SESSION['Transfer' . $Identifier]->StockLocationFrom = $MyRow['loccode'];
		} else {
			echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
		}
	}

	echo '</select>
		<fieldhelp>', _('Select the location from where this stock adjustment will take place'), '</fieldhelp>
	</field>';

	echo '<field>
			<label for="StockLocationTo">', _('To Stock Location'), ': </label>
			<select required="required" name="StockLocationTo"> ';

	$SQL = "SELECT locationname,
					loccode
				FROM locations";
	$ResultStkLocs = DB_query($SQL);

	while ($MyRow = DB_fetch_array($ResultStkLocs)) {
		if (isset($_SESSION['Transfer' . $Identifier]) and isset($_SESSION['Transfer' . $Identifier]->StockLocationTo)) {
			if ($MyRow['loccode'] == $_SESSION['Transfer' . $Identifier]->StockLocationTo) {
				echo '<option selected="selected" value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
			} else {
				echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
			}
		} else if ($MyRow['loccode'] == $_SESSION['UserStockLocation'] and isset($_SESSION['Transfer' . $Identifier])) {
			echo '<option selected="selected" value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
			$_SESSION['Transfer' . $Identifier]->StockLocationTo = $MyRow['loccode'];
		} else {
			echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
		}
	}

	echo '</select>
		<fieldhelp>', _('Select the location to where this stock adjustment will take place'), '</fieldhelp>
	</field>';

	echo '</fieldset>';

	if (isset($_GET['NewTransfer'])) {
		echo '<input type="hidden" name="StockID", value="', $_GET['StockID'], '" />';
		echo '<div class="centre">
				<input type="submit" name="TransferDetails" value="', _('Enter Transfer Details'), '" />
			</div>';
	} else {
		echo '<div class="centre">
				<input type="submit" name="CheckCode" value="', _('Find Part'), '" />
			</div>';
	}

	echo '</form>';
	include ('includes/footer.php');
	exit;
}

if (isset($_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID) and $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID != '') {
	$SQL = "SELECT locationname, glaccountcode FROM locations WHERE loccode='" . $_SESSION['Transfer' . $Identifier]->StockLocationFrom . "'";
	$Result = DB_query($SQL);
	$LocationRow = DB_fetch_array($Result);
	$_SESSION['Transfer' . $Identifier]->StockLocationFromName = $LocationRow['locationname'];

	$SQL = "SELECT locationname, glaccountcode FROM locations WHERE loccode='" . $_SESSION['Transfer' . $Identifier]->StockLocationTo . "'";
	$Result = DB_query($SQL);
	$LocationRow = DB_fetch_array($Result);
	$_SESSION['Transfer' . $Identifier]->StockLocationToName = $LocationRow['locationname'];

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', urlencode($Identifier), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<fieldset>
			<legend>', _('Transfer Details'), '</legend>';

	echo '<field>
			<label for="StockCode">', _('Stock Code'), ':</label>
			<div class="fieldtext">', $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID, '</div>
		</field>';

	echo '<field>
			<label for="StockText">', _('Description'), ':</label>
			<div class="fieldtext">', $_SESSION['Transfer' . $Identifier]->TransferItem[0]->ItemDescription, '</div>
		</field>';

	if (isset($_SESSION['Transfer' . $Identifier]->StockLocationFrom) and $_SESSION['Transfer' . $Identifier]->StockLocationFrom != '') {
		echo '<field>
				<label for="StockLocationFrom">', _('From Location'), '</label>
				<div class="fieldtext">', $_SESSION['Transfer' . $Identifier]->StockLocationFrom, ' - ', $_SESSION['Transfer' . $Identifier]->StockLocationFromName, '</div>
			</field>';
	} else {
		echo '<field>
				<label for="StockLocationFrom">', _('From Stock Location'), ':</label>
				<select required="required" name="StockLocationFrom">';

		$SQL = "SELECT locationname,
						locations.loccode
					FROM locations
					INNER JOIN locationusers
						ON locationusers.loccode=locations.loccode
						AND locationusers.userid='" . $_SESSION['UserID'] . "'
						AND locationusers.canupd=1";

		$ResultStkLocs = DB_query($SQL);
		while ($MyRow = DB_fetch_array($ResultStkLocs)) {
			if (isset($_SESSION['Transfer' . $Identifier]->StockLocationFrom)) {
				if ($MyRow['loccode'] == $_SESSION['Transfer' . $Identifier]->StockLocationFrom) {
					echo '<option selected="selected" value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
				} else {
					echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
				}
			} elseif (isset($_SESSION['Transfer' . $Identifier]) and $MyRow['loccode'] == $_SESSION['UserStockLocation']) {
				echo '<option selected="selected" value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
				$_SESSION['Transfer' . $Identifier]->StockLocationFrom = $MyRow['loccode'];
			} else {
				echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
			}
		}

		echo '</select>
				<fieldhelp>', _('Select the location from where this stock adjustment will take place'), '</fieldhelp>
			</field>';
	}

	if ($FromWarehouseDefined) {
		$ContainerSQL = "SELECT id, name FROM container WHERE location='" . $_SESSION['Transfer' . $Identifier]->StockLocationFrom . "' AND putaway=1";
		$ContainerResult = DB_query($ContainerSQL);
		echo '<field>
				<label for="FromContainer">', _('Select container to take stock from'), '</label>
				<select name="FromContainer" autofocus="autofocus">';
		while ($MyContainerRow = DB_fetch_array($ContainerResult)) {
			if (isset($_POST['FromContainer']) and $_POST['FromContainer'] == $MyContainerRow['id']) {
				echo '<option selected="selected" value="', $MyContainerRow['id'], '">', $MyContainerRow['name'], '</option>';
			} else {
				echo '<option value="', $MyContainerRow['id'], '">', $MyContainerRow['name'], '</option>';
			}
		}
		echo '</select>
			<fieldhelp>', _('Select the container within the warehouse from where the stock to be transferred is situated.'), '</fieldhelp>
		</field>';
	} else {
		echo '<input type="hidden" name="FromContainer" value="', $_SESSION['Transfer' . $Identifier]->StockLocationFrom, '" />';
	}

	if (isset($_SESSION['Transfer' . $Identifier]->StockLocationTo) and $_SESSION['Transfer' . $Identifier]->StockLocationTo != '') {
		echo '<field>
				<label for="StockLocationTo">', _('To Location'), '</label>
				<div class="fieldtext">', $_SESSION['Transfer' . $Identifier]->StockLocationTo, ' - ', $_SESSION['Transfer' . $Identifier]->StockLocationToName, '</div>
			</field>';
	} else {
		echo '<field>
				<label for="StockLocationTo">', _('To Stock Location'), ':</label>
				<select required="required" name="StockLocationTo">';

		$SQL = "SELECT locationname,
						locations.loccode
					FROM locations
					INNER JOIN locationusers
						ON locationusers.loccode=locations.loccode
						AND locationusers.userid='" . $_SESSION['UserID'] . "'
						AND locationusers.canupd=1";

		$ResultStkLocs = DB_query($SQL);
		while ($MyRow = DB_fetch_array($ResultStkLocs)) {
			if (isset($_SESSION['Transfer' . $Identifier]->StockLocationTo)) {
				if ($MyRow['loccode'] == $_SESSION['Transfer' . $Identifier]->StockLocationTo) {
					echo '<option selected="selected" value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
				} else {
					echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
				}
			} elseif (isset($_SESSION['Transfer' . $Identifier]) and $MyRow['loccode'] == $_SESSION['UserStockLocation']) {
				echo '<option selected="selected" value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
				$_SESSION['Transfer' . $Identifier]->StockLocationTo = $MyRow['loccode'];
			} else {
				echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
			}
		}

		echo '</select>
				<fieldhelp>', _('Select the location where this stock adjustment will go to'), '</fieldhelp>
			</field>';
	}

	if ($ToWarehouseDefined) {
		$ContainerSQL = "SELECT id, name FROM container WHERE location='" . $_SESSION['Transfer' . $Identifier]->StockLocationTo . "' AND putaway=1";
		$ContainerResult = DB_query($ContainerSQL);
		echo '<field>
				<label for="ToContainer">', _('Select container to move stock to'), '</label>
				<select name="ToContainer">';
		while ($MyContainerRow = DB_fetch_array($ContainerResult)) {
			if (isset($_POST['ToContainer']) and $_POST['ToContainer'] == $MyContainerRow['id']) {
				echo '<option selected="selected" value="', $MyContainerRow['id'], '">', $MyContainerRow['name'], '</option>';
			} else {
				echo '<option value="', $MyContainerRow['id'], '">', $MyContainerRow['name'], '</option>';
			}
		}
		echo '</select>
			<fieldhelp>', _('Select the container within the warehouse where the stock to be transferred is to be placed.'), '</fieldhelp>
		</field>';
	} else {
		echo '<input type="hidden" name="ToContainer" value="', $_SESSION['Transfer' . $Identifier]->StockLocationTo, '" />';
	}

	echo '<field>
			<label for="Quantity">', _('Transfer Quantity'), ':</label>
			<input type="text" class="number" name="Quantity" size="12" required="required" maxlength="12" value="0" />
			<fieldhelp>', _('The quantity to be transferred'), '</fieldhelp>
		</field>';
	//Select the tag
	echo '<field>
			<label for="tag[]">', _('Select Tag'), '</label>
			<select name="tag[]" multiple="multiple">';

	$SQL = "SELECT tagref,
				tagdescription
			FROM tags
			ORDER BY tagref";

	$Result = DB_query($SQL);
	echo '<option value="0">0 - ', _('None'), '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_SESSION['Transfer' . $Identifier]->tag) and $_SESSION['Transfer' . $Identifier]->tag == $MyRow['tagref']) {
			echo '<option selected="selected" value="', $MyRow['tagref'], '">', $MyRow['tagref'], ' - ', $MyRow['tagdescription'], '</option>';
		} else {
			echo '<option value="', $MyRow['tagref'], '">', $MyRow['tagref'], ' - ', $MyRow['tagdescription'], '</option>';
		}
	}
	echo '</select>
		<fieldhelp>', _('Select one or more tags from the list. Use the CTL button to select multiple tags'), '</fieldhelp>
	</field>';
	// End select tag
	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="EnterTransfer" value="', _('Enter Stock Transfer'), '" /><br />';

	if (empty($_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID) and isset($_POST['StockID'])) {
		$StockId = $_POST['StockID'];
	} else if (isset($_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID)) {
		$StockId = $_SESSION['Transfer' . $Identifier]->TransferItem[0]->StockID;
	} else {
		$StockId = '';
	}
	if (isset($_SESSION['Transfer' . $Identifier])) {
		echo '<a href="', $RootPath, '/StockStatus.php?StockID=', urlencode($StockId), '">', _('Show Stock Status'), '</a><br />';
		echo '<a href="', $RootPath, '/StockMovements.php?StockID=', urlencode($StockId), '">', _('Show Movements'), '</a><br />';
		echo '<a href="', $RootPath, '/StockUsage.php?StockID=', urlencode($StockId), '&amp;StockLocation=', urlencode($_SESSION['Transfer' . $Identifier]->StockLocationFrom), '">', _('Show Stock Usage'), '</a><br />';
		echo '<a href="', $RootPath, '/SelectSalesOrder.php?SelectedStockItem=', urlencode($StockId), '&amp;StockLocation=', urlencode($_SESSION['Transfer' . $Identifier]->StockLocationFrom), '">', _('Search Outstanding Sales Orders'), '</a><br />';
		echo '<a href="', $RootPath, '/SelectCompletedOrder.php?SelectedStockItem=', urlencode($StockId), '">', _('Search Completed Sales Orders'), '</a>';
	}
	echo '</div>
		</form>';
}

include ('includes/footer.php');
?>