<?php
include ('includes/DefineStockAdjustment.php');
include ('includes/DefineSerialItems.php');
include ('includes/session.php');
$Title = _('Stock Adjustments');
/* Manual links before header.php */
$ViewTopic = 'Inventory';
$BookMark = 'InventoryAdjustments';
include ('includes/header.php');
include ('includes/SQL_CommonFunctions.php');

if (empty($_GET['identifier'])) {
	/*unique session identifier to ensure that there is no conflict with other adjustment sessions on the same machine  */
	$Identifier = date('U');
} else {
	$Identifier = $_GET['identifier'];
}

if (isset($_GET['NewAdjustment'])) {
	unset($_SESSION['Adjustment' . $Identifier]);
	$_SESSION['Adjustment' . $Identifier] = new StockAdjustment();
}

if (!isset($_SESSION['Adjustment' . $Identifier])) {
	$_SESSION['Adjustment' . $Identifier] = new StockAdjustment();
}

$NewAdjustment = false;

if (isset($_GET['StockID'])) {
	$NewAdjustment = true;
	$StockId = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])) {
	if ($_POST['StockID'] != $_SESSION['Adjustment' . $Identifier]->StockID) {
		$NewAdjustment = true;
		$StockId = trim(mb_strtoupper($_POST['StockID']));
	}
}

if ($NewAdjustment == true) {

	$_SESSION['Adjustment' . $Identifier]->StockID = trim(mb_strtoupper($StockId));
	$Result = DB_query("SELECT description,
							controlled,
							serialised,
							decimalplaces,
							perishable,
							stockcosts.materialcost+stockcosts.labourcost+stockcosts.overheadcost AS totalcost,
							units
						FROM stockmaster
						LEFT JOIN stockcosts
							ON stockmaster.stockid=stockcosts.stockid
							AND stockcosts.succeeded=0
						WHERE stockcosts.stockid='" . $_SESSION['Adjustment' . $Identifier]->StockID . "'");
	$MyRow = DB_fetch_array($Result);
	$_SESSION['Adjustment' . $Identifier]->ItemDescription = $MyRow['description'];
	$_SESSION['Adjustment' . $Identifier]->Controlled = $MyRow['controlled'];
	$_SESSION['Adjustment' . $Identifier]->Serialised = $MyRow['serialised'];
	$_SESSION['Adjustment' . $Identifier]->DecimalPlaces = $MyRow['decimalplaces'];
	$_SESSION['Adjustment' . $Identifier]->SerialItems = array();
	if (!isset($_SESSION['Adjustment' . $Identifier]->Quantity) or !is_numeric($_SESSION['Adjustment' . $Identifier]->Quantity)) {
		$_SESSION['Adjustment' . $Identifier]->Quantity = 0;
	}
	$_SESSION['Adjustment' . $Identifier]->PartUnit = $MyRow['units'];
	$_SESSION['Adjustment' . $Identifier]->StandardCost = $MyRow['totalcost'];
	$DecimalPlaces = $MyRow['decimalplaces'];
	DB_free_result($Result);

} //end if it's a new adjustment
if (isset($_POST['tag'])) {
	$_SESSION['Adjustment' . $Identifier]->tag = $_POST['tag'];
}
if (isset($_POST['Narrative'])) {
	$_SESSION['Adjustment' . $Identifier]->Narrative = $_POST['Narrative'];
}

$SQL = "SELECT locationname,
				locations.loccode
			FROM locations
			INNER JOIN locationusers
				ON locationusers.loccode=locations.loccode
				AND locationusers.userid='" . $_SESSION['UserID'] . "'
				AND locationusers.canupd=1";

$ResultStkLocs = DB_query($SQL);
$LocationList = array();
while ($MyRow = DB_fetch_array($ResultStkLocs)) {
	$LocationList[$MyRow['loccode']] = $MyRow['locationname'];
}

if (isset($_POST['StockLocation'])) {
	if ($_SESSION['Adjustment' . $Identifier]->StockLocation != $_POST['StockLocation']) { /* User has changed the stock location, so the serial no must be validated again */
		$_SESSION['Adjustment' . $Identifier]->SerialItems = array();
	}
	$_SESSION['Adjustment' . $Identifier]->StockLocation = $_POST['StockLocation'];
} else {
	if (empty($_SESSION['Adjustment' . $Identifier]->StockLocation)) {
		if (empty($_SESSION['UserStockLocation'])) {
			$_SESSION['Adjustment' . $Identifier]->StockLocation = key(reset($LocationList));
		} else {
			$_SESSION['Adjustment' . $Identifier]->StockLocation = $_SESSION['UserStockLocation'];
		}
	}
}
if (isset($_POST['Quantity'])) {
	if ($_POST['Quantity'] == '' or !is_numeric(filter_number_format($_POST['Quantity']))) {
		$_POST['Quantity'] = 0;
	}
} else {
	$_POST['Quantity'] = 0;
}
if ($_POST['Quantity'] != 0) { //To prevent from serilised quantity changing to zero
	$_SESSION['Adjustment' . $Identifier]->Quantity = filter_number_format($_POST['Quantity']);
	if (count($_SESSION['Adjustment' . $Identifier]->SerialItems) == 0 and $_SESSION['Adjustment' . $Identifier]->Controlled == 1) { /* There is no quantity available for controlled items */
		$_SESSION['Adjustment' . $Identifier]->Quantity = 0;
	}
}

if (isset($_GET['OldIdentifier'])) {
	$_SESSION['Adjustment' . $Identifier]->StockLocation = $_SESSION['Adjustment' . $_GET['OldIdentifier']]->StockLocation;
}

if (isset($_SESSION['Adjustment' . $Identifier]->StockLocation)) {
	$SQL = "SELECT id FROM container WHERE parentid='" . $_SESSION['Adjustment' . $Identifier]->StockLocation . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) > 0) {
		$WarehouseDefined = true;
	} else {
		$WarehouseDefined = false;
	}
}

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/inventory.png" title="', _('Inventory Adjustment'), '" alt="" />', _('Inventory Adjustment'), '
	</p>';

if (isset($_POST['CheckCode'])) {

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', _('Dispatch'), '" alt="" />', _('Select Item to Adjust'), '
		</p>';

	if (mb_strlen($_POST['StockText']) > 0) {
		$SQL = "SELECT stockid,
					description
				FROM stockmaster
				INNER JOIN stockcategory
					ON stockcategory.categoryid=stockmaster.categoryid
				INNER JOIN stocktypes
					ON stockcategory.stocktype=stocktypes.type
				WHERE description " . LIKE . " '%" . $_POST['StockText'] . "%'
					AND stocktypes.physicalitem=1";
	} else {
		$SQL = "SELECT stockid,
					description
				FROM stockmaster
				INNER JOIN stockcategory
					ON stockcategory.categoryid=stockmaster.categoryid
				INNER JOIN stocktypes
					ON stockcategory.stocktype=stocktypes.type
				WHERE stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
					AND stocktypes.physicalitem=1";
	}
	$ErrMsg = _('The stock information cannot be retrieved because');
	$DbgMsg = _('The SQL to get the stock description was');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
	echo '<table>
			<thead>
				<tr>
					<th class="SortedColumn">', _('Stock Code'), '</th>
					<th class="SortedColumn">', _('Stock Description'), '</th>
				</tr>
			</thead>';

	echo '<tbody>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
				<td>', $MyRow['stockid'], '</td>
				<td>', $MyRow['description'], '</td>
				<td><a href="StockAdjustments.php?StockID=', urlencode($MyRow[0]), '&amp;Description=', urlencode($MyRow[1]), '&amp;OldIdentifier=', urlencode($Identifier), '">', _('Adjust'), '</a>
			</tr>';
	}
	echo '</tbody>';
	echo '</table>';
	include ('includes/footer.php');
	exit;
}

if (isset($_POST['EnterAdjustment']) and $_POST['EnterAdjustment'] != '') {

	$InputError = false;
	/*Start by hoping for the best */
	$Result = DB_query("SELECT * FROM stockmaster WHERE stockid='" . $_SESSION['Adjustment' . $Identifier]->StockID . "'");
	$MyRow = DB_fetch_row($Result);
	if (DB_num_rows($Result) == 0) {
		prnMsg(_('The entered item code does not exist'), 'error');
		$InputError = true;
	} elseif (!is_numeric($_SESSION['Adjustment' . $Identifier]->Quantity)) {
		prnMsg(_('The quantity entered must be numeric'), 'error');
		$InputError = true;
	} elseif (strlen(substr(strrchr($_SESSION['Adjustment' . $Identifier]->Quantity, "."), 1)) > $_SESSION['Adjustment' . $Identifier]->DecimalPlaces) {
		prnMsg(_('The decimal places input is greater than the number defined for this item. The number of decimal places defined for this item is') . ' ' . $_SESSION['Adjustment' . $Identifier]->DecimalPlaces, 1, 'error');
		$InputError = true;
	} elseif ($_SESSION['Adjustment' . $Identifier]->Quantity == 0) {
		prnMsg(_('The quantity entered cannot be zero') . '. ' . _('There would be no adjustment to make'), 'error');
		$InputError = true;
	} elseif ($_SESSION['Adjustment' . $Identifier]->Controlled == 1 and count($_SESSION['Adjustment' . $Identifier]->SerialItems) == 0) {
		prnMsg(_('The item entered is a controlled item that requires the detail of the serial numbers or batch references to be adjusted to be entered'), 'error');
		$InputError = true;
	}

	if ($_SESSION['ProhibitNegativeStock'] == 1) {
		$SQL = "SELECT quantity FROM locstock
				WHERE stockid='" . $_SESSION['Adjustment' . $Identifier]->StockID . "'
				AND loccode='" . $_SESSION['Adjustment' . $Identifier]->StockLocation . "'";
		$CheckNegResult = DB_query($SQL);
		$CheckNegRow = DB_fetch_array($CheckNegResult);
		if ($CheckNegRow['quantity'] + $_SESSION['Adjustment' . $Identifier]->Quantity < 0) {
			$InputError = true;
			prnMsg(_('The system parameters are set to prohibit negative stocks. Processing this stock adjustment would result in negative stock at this location. This adjustment will not be processed.'), 'error');
		}
	}

	if (!$InputError) {

		/*All inputs must be sensible so make the stock movement records and update the locations stocks */

		$AdjustmentNumber = GetNextTransNo(17);
		$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']));
		$SQLAdjustmentDate = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));

		$Result = DB_Txn_Begin();

		// Need to get the current location quantity will need it later for the stock movement
		$SQL = "SELECT locstock.quantity
			FROM locstock
			WHERE locstock.stockid='" . $_SESSION['Adjustment' . $Identifier]->StockID . "'
			AND loccode= '" . $_SESSION['Adjustment' . $Identifier]->StockLocation . "'";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) == 1) {
			$LocQtyRow = DB_fetch_row($Result);
			$QtyOnHandPrior = $LocQtyRow[0];
		} else {
			// There must actually be some error this should never happen
			$QtyOnHandPrior = 0;
		}

		if (empty($_POST['Container'])) {
			$_POST['Container'] = $_SESSION['Adjustment' . $Identifier]->StockLocation;
		}

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
										newqoh,
										standardcost
									) VALUES (
										'" . $_SESSION['Adjustment' . $Identifier]->StockID . "',
										17,
										'" . $AdjustmentNumber . "',
										'" . $_SESSION['Adjustment' . $Identifier]->StockLocation . "',
										'" . $_POST['Container'] . "',
										'" . $SQLAdjustmentDate . "',
										'" . $_SESSION['UserID'] . "',
										'" . $PeriodNo . "',
										'" . $_SESSION['Adjustment' . $Identifier]->Narrative . "',
										'" . $_SESSION['Adjustment' . $Identifier]->Quantity . "',
										'" . ($QtyOnHandPrior + $_SESSION['Adjustment' . $Identifier]->Quantity) . "',
										'" . $_SESSION['Adjustment' . $Identifier]->StandardCost . "'
									)";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record cannot be inserted because');
		$DbgMsg = _('The following SQL to insert the stock movement record was used');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

		/*Get the ID of the StockMove... */
		$StkMoveNo = DB_Last_Insert_ID('stockmoves', 'stkmoveno');

		/*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/

		if ($_SESSION['Adjustment' . $Identifier]->Controlled == 1) {
			foreach ($_SESSION['Adjustment' . $Identifier]->SerialItems as $Item) {
				/*We need to add or update the StockSerialItem record and
				 The StockSerialMoves as well */

				/*First need to check if the serial items already exists or not */
				$SQL = "SELECT COUNT(*)
						FROM stockserialitems
						WHERE stockid='" . $_SESSION['Adjustment' . $Identifier]->StockID . "'
						AND loccode='" . $_SESSION['Adjustment' . $Identifier]->StockLocation . "'
						AND serialno='" . $Item->BundleRef . "'";
				$ErrMsg = _('Unable to determine if the serial item exists');
				$Result = DB_query($SQL, $ErrMsg);
				$SerialItemExistsRow = DB_fetch_row($Result);

				if ($SerialItemExistsRow[0] == 1) {

					$SQL = "UPDATE stockserialitems SET quantity= quantity + " . $Item->BundleQty . "
							WHERE stockid='" . $_SESSION['Adjustment' . $Identifier]->StockID . "'
							AND loccode='" . $_SESSION['Adjustment' . $Identifier]->StockLocation . "'
							AND serialno='" . $Item->BundleRef . "'";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated because');
					$DbgMsg = _('The following SQL to update the serial stock item record was used');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				} else {
					/*Need to insert a new serial item record */
					$SQL = "INSERT INTO stockserialitems (stockid,
														loccode,
														serialno,
														qualitytext,
														quantity,
														expirationdate,
														createdate)
											VALUES ('" . $_SESSION['Adjustment' . $Identifier]->StockID . "',
											'" . $_SESSION['Adjustment' . $Identifier]->StockLocation . "',
											'" . $Item->BundleRef . "',
											'',
											'" . $Item->BundleQty . "',
											'" . FormatDateForSQL($Item->ExpiryDate) . "',
											CURRENT_TIME)";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated because');
					$DbgMsg = _('The following SQL to update the serial stock item record was used');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				}

				/* now insert the serial stock movement */

				$SQL = "INSERT INTO stockserialmoves (stockmoveno,
													stockid,
													serialno,
													moveqty)
										VALUES ('" . $StkMoveNo . "',
											'" . $_SESSION['Adjustment' . $Identifier]->StockID . "',
											'" . $Item->BundleRef . "',
											'" . $Item->BundleQty . "')";
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
				$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			}
			/* foreach controlled item in the serialitems array */
		}
		/*end if the adjustment item is a controlled item */

		$SQL = "UPDATE locstock SET quantity = quantity + '" . $_SESSION['Adjustment' . $Identifier]->Quantity . "'
				WHERE stockid='" . $_SESSION['Adjustment' . $Identifier]->StockID . "'
				AND loccode='" . $_SESSION['Adjustment' . $Identifier]->StockLocation . "'";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
		$DbgMsg = _('The following SQL to update the stock record was used');

		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

		if ($_SESSION['CompanyRecord']['gllink_stock'] == 1 and $_SESSION['Adjustment' . $Identifier]->StandardCost > 0) {

			$StockGLCodes = GetStockGLCode($_SESSION['Adjustment' . $Identifier]->StockID);

			$SQL = "INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										amount,
										narrative)
								VALUES (17,
									'" . $AdjustmentNumber . "',
									'" . $SQLAdjustmentDate . "',
									'" . $PeriodNo . "',
									'" . $StockGLCodes['adjglact'] . "',
									'" . round($_SESSION['Adjustment' . $Identifier]->StandardCost * -($_SESSION['Adjustment' . $Identifier]->Quantity), $_SESSION['CompanyRecord']['decimalplaces']) . "',
									'" . $_SESSION['Adjustment' . $Identifier]->StockID . " x " . $_SESSION['Adjustment' . $Identifier]->Quantity . " @ " . $_SESSION['Adjustment' . $Identifier]->StandardCost . " " . $_SESSION['Adjustment' . $Identifier]->Narrative . "'
								)";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction entries could not be added because');
			$DbgMsg = _('The following SQL to insert the GL entries was used');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
			foreach ($_SESSION['Adjustment' . $Identifier]->tag as $Tag) {
				$SQL = "INSERT INTO gltags VALUES ( LAST_INSERT_ID(),
													'" . $Tag . "')";
				$ErrMsg = _('Cannot insert a GL tag for the adjustment line because');
				$DbgMsg = _('The SQL that failed to insert the GL tag record was');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
			}

			$SQL = "INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										amount,
										narrative)
								VALUES (17,
									'" . $AdjustmentNumber . "',
									'" . $SQLAdjustmentDate . "',
									'" . $PeriodNo . "',
									'" . $StockGLCodes['stockact'] . "',
									'" . round($_SESSION['Adjustment' . $Identifier]->StandardCost * $_SESSION['Adjustment' . $Identifier]->Quantity, $_SESSION['CompanyRecord']['decimalplaces']) . "',
									'" . $_SESSION['Adjustment' . $Identifier]->StockID . " x " . $_SESSION['Adjustment' . $Identifier]->Quantity . " @ " . $_SESSION['Adjustment' . $Identifier]->StandardCost . " " . $_SESSION['Adjustment' . $Identifier]->Narrative . "'
								)";

			$Errmsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction entries could not be added because');
			$DbgMsg = _('The following SQL to insert the GL entries was used');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
			foreach ($_SESSION['Adjustment' . $Identifier]->tag as $Tag) {
				$SQL = "INSERT INTO gltags VALUES ( LAST_INSERT_ID(),
													'" . $Tag . "')";
				$ErrMsg = _('Cannot insert a GL tag for the adjustment line because');
				$DbgMsg = _('The SQL that failed to insert the GL tag record was');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
			}
		}

		EnsureGLEntriesBalance(17, $AdjustmentNumber);

		$Result = DB_Txn_Commit();

		if (mb_strlen($_SESSION['Adjustment' . $Identifier]->Narrative) > 0) {
			$AdjustReason = _('Narrative') . ' ' . $_SESSION['Adjustment' . $Identifier]->Narrative . ':';
		} else {
			$AdjustReason = '';
		}
		$ConfirmationText = _('A stock adjustment for') . ' ' . $_SESSION['Adjustment' . $Identifier]->StockID . ' -  ' . $_SESSION['Adjustment' . $Identifier]->ItemDescription . ' ' . _('has been created from location') . ' ' . $_SESSION['Adjustment' . $Identifier]->StockLocation . ' ' . _('for a quantity of') . ' ' . locale_number_format($_SESSION['Adjustment' . $Identifier]->Quantity, $_SESSION['Adjustment' . $Identifier]->DecimalPlaces) . ' ' . $AdjustReason;
		prnMsg($ConfirmationText, 'success');

		if ($_SESSION['InventoryManagerEmail'] != '') {
			$ConfirmationText = $ConfirmationText . ' ' . _('by user') . ' ' . $_SESSION['UserID'] . ' ' . _('at') . ' ' . Date('Y-m-d H:i:s');
			$EmailSubject = _('Stock adjustment for') . ' ' . $_SESSION['Adjustment' . $Identifier]->StockID;
			if ($_SESSION['SmtpSetting'] == 0) {
				mail($_SESSION['InventoryManagerEmail'], $EmailSubject, $ConfirmationText);
			} else {
				include ('includes/htmlMimeMail.php');
				$Mail = new htmlMimeMail();
				$Mail->setSubject($EmailSubject);
				$Mail->setText($ConfirmationText);
				$Result = SendmailBySmtp($Mail, array($_SESSION['InventoryManagerEmail']));
			}

		}
		$StockId = $_SESSION['Adjustment' . $Identifier]->StockID;
		unset($_SESSION['Adjustment' . $Identifier]);
		unset($StockId);
	}
	/* end if there was no input error */

}
/* end if the user hit enter the adjustment */

if (!isset($_SESSION['Adjustment' . $Identifier])) {
	$Controlled = 0;
	$Quantity = 0;
	$DecimalPlaces = 2;
} else {
	$StockId = $_SESSION['Adjustment' . $Identifier]->StockID;
	$Controlled = $_SESSION['Adjustment' . $Identifier]->Controlled;
	$Quantity = $_SESSION['Adjustment' . $Identifier]->Quantity;
	$SQL = "SELECT stockcosts.materialcost,
				stockcosts.labourcost,
				stockcosts.overheadcost,
				units,
				decimalplaces
			FROM stockmaster
			LEFT JOIN stockcosts
				ON stockmaster.stockid=stockcosts.stockid
				AND stockcosts.succeeded=0
			WHERE stockcosts.stockid='" . $StockId . "'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$_SESSION['Adjustment' . $Identifier]->PartUnit = $MyRow['units'];
	$_SESSION['Adjustment' . $Identifier]->StandardCost = $MyRow['materialcost'] + $MyRow['labourcost'] + $MyRow['overheadcost'];
	$DecimalPlaces = $MyRow['decimalplaces'];
}

if (isset($_SESSION['Adjustment' . $Identifier]->StockID) and $_SESSION['Adjustment' . $Identifier]->StockID != '') {
	$SQL = "SELECT locations.locationname,
					locstock.stockid,
					locstock.quantity
				FROM locstock
				INNER JOIN locations
					ON locstock.loccode=locations.loccode
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" . $_SESSION['UserID'] . "'
					AND locationusers.canupd=1
				WHERE locstock.stockid='" . $_SESSION['Adjustment' . $Identifier]->StockID . "'";
	$Result = DB_query($SQL);
	echo '<table>
			<tr>
				<th colspan="2">', _('Current Stock Balances'), '</th>
			</tr>
			<tr>
				<th>', _('Location'), '</th>
				<th>', _('Quantity'), '</th>
			</tr>';

	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
				<td>', $MyRow['locationname'], '</td>
				<td class="number">', locale_number_format($MyRow['quantity'], $DecimalPlaces), '</td>
			</tr>';
	}
	echo '</table>';
}

if (!isset($StockId) or $StockId == '') {

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', $Identifier, '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<fieldset>
			<legend>', _('Adjustment Details'), '</legend>';
	if (!isset($_GET['Description'])) {
		$_GET['Description'] = '';
	}

	echo '<field>
			<label for="StockText">', _('Partial Description'), ':</label>
			<input type="text" autofocus="autofocus" name="StockText" size="21" value="', stripslashes($_GET['Description']), '" />
			<fieldhelp>', _('Enter all or part of a description for a stock item.'), '</fieldhelp>
		</field>';

	echo '<field>
			<label for="StockCode">', _('Partial Stock Code'), ':</label>
			<input type="text" name="StockCode" size="21" value="" maxlength="20" />
			<fieldhelp>', _('Enter all or part of a code for a stock item.'), '</fieldhelp>
		</field>';

	echo '<h1>', _('AND'), '</h1>';

	echo '<field>
			<label for="StockLocation">', _('Adjustment to Stock At Location'), ':</label>
			<select name="StockLocation" required="required"> ';
	foreach ($LocationList as $Loccode => $Locationname) {
		if ($Loccode == $_SESSION['Adjustment' . $Identifier]->StockLocation) {
			echo '<option selected="selected" value="', $Loccode, '">', $Locationname, '</option>';
		} else {
			echo '<option value="', $Loccode, '">', $Locationname, '</option>';
		}
	}
	echo '</select>
		<fieldhelp>', _('Select the location where this stock adjustment will take place'), '</fieldhelp>
	</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="CheckCode" value="', _('Find Part'), '" />
		</div>
	</form>';
}

if (isset($StockId) and $StockId != '') {

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', $Identifier, '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<fieldset>
			<legend>', _('Adjustment Details'), '</legend>';
	if (isset($_SESSION['Adjustment' . $Identifier]) and mb_strlen($_SESSION['Adjustment' . $Identifier]->ItemDescription) > 1) {
		echo '<h3>', $_SESSION['Adjustment' . $Identifier]->ItemDescription, ' (', _('In Units of'), ' ', $_SESSION['Adjustment' . $Identifier]->PartUnit, ' ) - ', _('Unit Cost'), ' = ', locale_number_format($_SESSION['Adjustment' . $Identifier]->StandardCost, 4), '</h3>';
	}
	if (isset($_SESSION['Adjustment' . $Identifier]) and !isset($_SESSION['Adjustment' . $Identifier]->Narrative)) {
		$_SESSION['Adjustment' . $Identifier]->Narrative = '';
		$Narrative = '';
	} elseif (isset($_SESSION['Adjustment' . $Identifier]->Narrative)) {
		$Narrative = $_SESSION['Adjustment' . $Identifier]->Narrative;
	} else {
		$Narrative = '';
	}

	echo '<field>
			<label for="Location">', _('Stock Code'), '</label>
			<div class="fieldtext">', $StockId, '</div>
		</field>';

	echo '<field>
			<label for="Description">', _('Description'), '</label>
			<div class="fieldtext">', $_SESSION['Adjustment' . $Identifier]->ItemDescription, '</div>
		</field>';

	echo '<field>
			<label for="Location">', _('In Location'), '</label>
			<div class="fieldtext">', $_SESSION['Adjustment' . $Identifier]->StockLocation, '</div>
		</field>';

	if ($WarehouseDefined) {
		$ContainerSQL = "SELECT id, name FROM container WHERE location='" . $_SESSION['Adjustment' . $Identifier]->StockLocation . "' AND putaway=1";
		$ContainerResult = DB_query($ContainerSQL);
		echo '<field>
				<label for="Container">', _('Select warehouse container'), '</label>
				<select name="Container">';
		while ($MyContainerRow = DB_fetch_array($ContainerResult)) {
			if (isset($_POST['Container']) and $_POST['Container'] == $MyContainerRow['id']) {
				echo '<option selected="selected" value="', $MyContainerRow['id'], '">', $MyContainerRow['name'], '</option>';
			} else {
				echo '<option value="', $MyContainerRow['id'], '">', $MyContainerRow['name'], '</option>';
			}
		}
		echo '</select>
			<fieldhelp>', _('Select the container within the warehouse where the stock to be adjusted is situated.'), '</fieldhelp>
		</field>';
	}

	echo '<field>
			<label for="Narrative">', _('Comments On Why'), ':</label>
			<input type="text" spellcheck="true" name="Narrative" size="32" maxlength="30" value="', $Narrative, '" />
			<fieldhelp>', _('Add a narrative explaining the reasons for the stock adjustment.'), '</fieldhelp>
		</field>';

	echo '<field>
			<label for="Quantity">', _('Adjustment Quantity'), ':</label>';
	if ($Controlled == 1) {
		if ($_SESSION['Adjustment' . $Identifier]->StockLocation == '') {
			$_SESSION['Adjustment' . $Identifier]->StockLocation = $_SESSION['UserStockLocation'];
		}
		echo '<input type="hidden" name="Quantity" value="', $_SESSION['Adjustment' . $Identifier]->Quantity, '" />
					' . locale_number_format($_SESSION['Adjustment' . $Identifier]->Quantity, $DecimalPlaces), ' &nbsp; &nbsp; &nbsp; &nbsp;
					[<a class="FontSize" href="', $RootPath, '/StockAdjustmentsControlled.php?AdjType=REMOVE&identifier=', urlencode($Identifier), '">', _('Remove'), '</a>]
					[<a class="FontSize" href="', $RootPath, '/StockAdjustmentsControlled.php?AdjType=ADD&identifier=', urlencode($Identifier), '">', _('Add'), '</a>]';
	} else {
		echo '<input type="text" class="number" name="Quantity" size="12" required="required" maxlength="12" value="', locale_number_format($Quantity, $DecimalPlaces), '" />';
	}
	echo '<fieldhelp>', _('The quantity to be adjusted. A neative amount reduces stock, and a positive amount increases it.'), '</fieldhelp>
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
		if (isset($_SESSION['Adjustment' . $Identifier]->tag) and $_SESSION['Adjustment' . $Identifier]->tag == $MyRow['tagref']) {
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
			<input type="submit" name="EnterAdjustment" value="', _('Enter Stock Adjustment'), '" /><br />';

	if (!isset($_POST['StockLocation'])) {
		$_POST['StockLocation'] = '';
	}

	echo '<a href="', $RootPath, '/StockStatus.php?StockID=', urlencode($StockId), '">', _('Show Stock Status'), '</a><br />';
	echo '<a href="', $RootPath, '/StockMovements.php?StockID=', urlencode($StockId), '">', _('Show Movements'), '</a><br />';
	echo '<a href="', $RootPath, '/StockUsage.php?StockID=', urlencode($StockId), '&amp;StockLocation=', urlencode($_POST['StockLocation']), '">', _('Show Stock Usage'), '</a><br />';
	echo '<a href="', $RootPath, '/SelectSalesOrder.php?SelectedStockItem=', urlencode($StockId), '&amp;StockLocation=', urlencode($_POST['StockLocation']), '">', _('Search Outstanding Sales Orders'), '</a><br />';
	echo '<a href="', $RootPath, '/SelectCompletedOrder.php?SelectedStockItem=', urlencode($StockId), '">', _('Search Completed Sales Orders'), '</a>';

	echo '</div>
		</form>';
}

include ('includes/footer.php');
?>