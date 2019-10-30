<?php
include ('includes/DefineOfferClass.php');
include ('includes/session.php');
$Title = _('Supplier Tendering');
include ('includes/header.php');

$Maximum_Number_Of_Parts_To_Show = 50;

if (isset($_GET['TenderType'])) {
	$_POST['TenderType'] = $_GET['TenderType'];
}

if (empty($_GET['identifier'])) {
	/*unique session identifier to ensure that there is no conflict with other supplier tender sessions on the same machine  */
	$Identifier = date('U');
} else {
	$Identifier = $_GET['identifier'];
}

if (!isset($_POST['SupplierID'])) {
	$SQL = "SELECT supplierid FROM www_users WHERE userid='" . $_SESSION['UserID'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	if ($MyRow['supplierid'] == '') {
		prnMsg(_('This functionality can only be accessed via a supplier login.'), 'warning');
		include ('includes/footer.php');
		exit;
	} else {
		$_POST['SupplierID'] = $MyRow['supplierid'];
	}
}

if (isset($_GET['Delete'])) {
	$_POST['SupplierID'] = $_SESSION['offer' . $Identifier]->SupplierID;
	$_POST['TenderType'] = $_GET['Type'];
	$_SESSION['offer' . $Identifier]->remove_from_offer($_GET['Delete']);
}

$SQL = "SELECT suppname,
			currcode
		FROM suppliers
		WHERE supplierid='" . $_POST['SupplierID'] . "'";
$Result = DB_query($SQL);
$MyRow = DB_fetch_array($Result);
$Supplier = $MyRow['suppname'];
$Currency = $MyRow['currcode'];

if (isset($_POST['Confirm'])) {
	$_SESSION['offer' . $Identifier]->Save();
	$_SESSION['offer' . $Identifier]->EmailOffer();
	$SQL = "UPDATE tendersuppliers
			SET responded=1
			WHERE supplierid='" . $_SESSION['offer' . $Identifier]->SupplierID . "'
			AND tenderid='" . $_SESSION['offer' . $Identifier]->TenderID . "'";
	$Result = DB_query($SQL);
}

if (isset($_POST['Process'])) {
	if (isset($_SESSION['offer' . $Identifier])) {
		unset($_SESSION['offer' . $Identifier]);
	}
	$_SESSION['offer' . $Identifier] = new Offer($_POST['SupplierID']);
	$_SESSION['offer' . $Identifier]->TenderID = $_POST['Tender'];
	$_SESSION['offer' . $Identifier]->CurrCode = $Currency;
	$LineNo = 0;
	foreach ($_POST as $Key => $Value) {
		if (mb_substr($Key, 0, 7) == 'StockID') {
			$Index = mb_substr($Key, 7, mb_strlen($Key) - 7);
			$ItemCode = $Value;
			$Quantity = $_POST['Qty' . $Index];
			$Price = $_POST['Price' . $Index];
			$_SESSION['offer' . $Identifier]->add_to_offer($LineNo, $ItemCode, $Quantity, $_POST['ItemDescription' . $Index], $Price, $_POST['UOM' . $Index], $_POST['DecimalPlaces' . $Index], $_POST['RequiredByDate' . $Index]);
			$LineNo++;
		}
	}
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/supplier.png" title="', _('Tenders'), '" alt="" />', ' ', _('Confirm the Response For Tender'), ' ', $_SESSION['offer' . $Identifier]->TenderID, '
		</p>';

	echo '<form action="', htmlspecialchars(basename(__FILE__)), '?identifier=', urlencode($Identifier), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<table>';
	echo '<input type="hidden" name="TenderType" value="3" />';
	$LocationSQL = "SELECT tenderid,
						locations.locationname,
						address1,
						address2,
						address3,
						address4,
						address5,
						address6,
						telephone
					FROM tenders
					INNER JOIN locations
					ON tenders.location=locations.loccode
					WHERE closed=0
					AND tenderid='" . $_SESSION['offer' . $Identifier]->TenderID . "'";
	$LocationResult = DB_query($LocationSQL);
	$MyLocationRow = DB_fetch_row($LocationResult);
	$CurrencySQL = "SELECT decimalplaces from currencies WHERE currabrev='" . $_SESSION['offer' . $Identifier]->CurrCode . "'";
	$CurrencyResult = DB_query($CurrencySQL);
	$CurrencyRow = DB_fetch_array($CurrencyResult);
	echo '<tr>
			<td valign="top" style="background-color:#cccce5">' . _('Deliver To') . ':</td>
			<td valign="top" style="background-color:#cccce5">';
	for ($i = 1;$i < 8;$i++) {
		if ($MyLocationRow[$i] != '') {
			echo $MyLocationRow[$i] . '<br />';
		}
	}
	echo '</td>';
	echo '<th colspan="8" style="vertical-align:top"><font size="2" color="#616161">' . _('Tender Number') . ': ' . $_SESSION['offer' . $Identifier]->TenderID . '</font></th>';
	echo '<input type="hidden" value="' . $_SESSION['offer' . $Identifier]->TenderID . '" name="Tender" />';
	echo '<tr>
			<th>' . stripslashes($_SESSION['CompanyRecord']['coyname']) . '<br />' . _('Item Code') . '</th>
			<th>' . _('Item Description') . '</th>
			<th>' . _('Quantity') . '<br />' . _('Offered') . '</th>
			<th>' . $Supplier . '<br />' . _('Units of Measure') . '</th>
			<th>' . _('Currency') . '</th>
			<th>' . $Supplier . '<br />' . _('Price') . '</th>
			<th>' . _('Line Value') . '</th>
			<th>' . _('Delivery By') . '</th>
		</tr>';

	foreach ($_SESSION['offer' . $Identifier]->LineItems as $LineItem) {
		echo '<tr><td>' . $LineItem->StockID . '</td>';
		echo '<td>' . $LineItem->ItemDescription . '</td>';
		echo '<td class="number"> ' . locale_number_format($LineItem->Quantity, $LineItem->DecimalPlaces) . '</td>';
		echo '<td>' . $LineItem->Units . '</td>';
		echo '<td>' . $_SESSION['offer' . $Identifier]->CurrCode . '</td>';
		echo '<td class="number">' . locale_number_format($LineItem->Price, $CurrencyRow['decimalplaces']) . '</td>';
		echo '<td class="number">' . locale_number_format($LineItem->Price * $LineItem->Quantity, $CurrencyRow['decimalplaces']) . '</td>';
		echo '<td>' . $LineItem->ExpiryDate . '</td>';
	}
	echo '</table>
		<br />
		<div class="centre">
			<input type="submit" name="Confirm" value="' . _('Confirm and Send Email') . '" />
			<br />
			<br />
			<input type="submit" name="Cancel" value="' . _('Cancel Offer') . '" />
		</div>
		</form>';
	include ('includes/footer.php');
	exit;
}

/* If the supplierID is set then it must be a login from the supplier but if nothing else is
 * set then the supplier must have just logged in so show them the choices.
*/
if (isset($_POST['SupplierID']) and empty($_POST['TenderType']) and empty($_POST['Search']) and empty($_POST['NewItem']) and empty($_GET['Delete'])) {
	if (isset($_SESSION['offer' . $Identifier])) {
		unset($_SESSION['offer' . $Identifier]);
	}
	echo '<form method="post" action="' . htmlspecialchars(basename(__FILE__)), '?identifier=', urlencode($Identifier), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/supplier.png" title="', _('Tenders'), '" alt="" />', ' ', _('Create or View Offers from'), ' ', $Supplier, '
		</p>';

	echo '<fieldset>
			<legend>', _('Supplier Tender Options'), '</legend>';

	echo '<field>
			<label for="TenderType">', _('Select option for tendering'), '</label>
			<select required="required" name="TenderType">
				<option value="1">', _('View or Amend outstanding offers from'), ' ', $Supplier, '</option>
				<option value="2">', _('Create a new offer from'), ' ', $Supplier, '</option>
				<option value="3">', _('View any open tenders without an offer from'), ' ', $Supplier, '</option>
			</select>
		</field>';

	echo '<input type="hidden" name="SupplierID" value="', $_POST['SupplierID'], '" />';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="submit" value="', _('Select'), '" />
		</div>
	</form>';
}

if (isset($_POST['NewItem']) and !isset($_POST['Refresh'])) {
	foreach ($_POST as $Key => $Value) {
		if (mb_substr($Key, 0, 7) == 'StockID') {
			$Index = mb_substr($Key, 7, mb_strlen($Key) - 7);
			$StockId = $Value;
			$Quantity = filter_number_format($_POST['Qty' . $Index]);
			$Price = filter_number_format($_POST['Price' . $Index]);
			$UOM = $_POST['uom' . $Index];
			if (isset($UOM) and $Quantity > 0) {
				$SQL = "SELECT description, decimalplaces FROM stockmaster WHERE stockid='" . $StockId . "'";
				$Result = DB_query($SQL);
				$MyRow = DB_fetch_array($Result);
				$_SESSION['offer' . $Identifier]->add_to_offer($_SESSION['offer' . $Identifier]->LinesOnOffer, $StockId, $Quantity, $MyRow['description'], $Price, $UOM, $MyRow['decimalplaces'], DateAdd(date($_SESSION['DefaultDateFormat']), 'm', 3));
				unset($UOM);
			}
		}
	}
}

if (isset($_POST['Refresh']) and !isset($_POST['NewItem'])) {
	foreach ($_POST as $Key => $Value) {
		if (mb_substr($Key, 0, 7) == 'StockID') {
			$Index = mb_substr($Key, 7, mb_strlen($Key) - 7);
			$StockId = $Value;
			$Quantity = filter_number_format($_POST['Qty' . $Index]);
			$Price = filter_number_format($_POST['Price' . $Index]);
			$ExpiryDate = $_POST['expirydate' . $Index];
		}
		if (isset($ExpiryDate)) {
			$_SESSION['offer' . $Identifier]->update_offer_item($Index, $Quantity, $Price, $ExpiryDate);
			unset($ExpiryDate);
		}
	}
}

if (isset($_POST['Update'])) {
	foreach ($_POST as $Key => $Value) {
		if (mb_substr($Key, 0, 3) == 'Qty') {
			$LineNo = mb_substr($Key, 3);
			$Quantity = $Value;
		}
		if (mb_substr($Key, 0, 5) == 'Price') {
			$Price = $Value;
		}
		if (mb_substr($Key, 0, 10) == 'expirydate') {
			$ExpiryDate = $Value;
		}
		if (isset($ExpiryDate)) {
			$_SESSION['offer' . $Identifier]->update_offer_item($LineNo, $Quantity, $Price, $ExpiryDate);
			unset($ExpiryDate);
		}
	}
	$_SESSION['offer' . $Identifier]->Save('Yes');
	$_SESSION['offer' . $Identifier]->EmailOffer();
	unset($_SESSION['offer' . $Identifier]);
	include ('includes/footer.php');
	exit;
}

if (isset($_POST['Save'])) {
	foreach ($_POST as $Key => $Value) {
		if (mb_substr($Key, 0, 3) == 'Qty') {
			$LineNo = mb_substr($Key, 3);
			$Quantity = $Value;
		}
		if (mb_substr($Key, 0, 5) == 'Price') {
			$Price = $Value;
		}
		if (mb_substr($Key, 0, 10) == 'expirydate') {
			$ExpiryDate = $Value;
		}
		if (isset($ExpiryDate)) {
			$_SESSION['offer' . $Identifier]->update_offer_item($LineNo, $Quantity, $Price, $ExpiryDate);
			unset($ExpiryDate);
		}
	}
	$_SESSION['offer' . $Identifier]->Save();
	$_SESSION['offer' . $Identifier]->EmailOffer();
	unset($_SESSION['offer' . $Identifier]);
	include ('includes/footer.php');
	exit;
}

/*The supplier has chosen option 1
*/
if (isset($_POST['TenderType']) and $_POST['TenderType'] == 1 and !isset($_POST['Refresh']) and !isset($_GET['Delete'])) {
	$SQL = "SELECT offers.offerid,
				offers.stockid,
				stockmaster.description,
				offers.quantity,
				offers.uom,
				offers.price,
				offers.expirydate,
				stockmaster.decimalplaces
			FROM offers
			INNER JOIN stockmaster
				ON offers.stockid=stockmaster.stockid
			WHERE offers.supplierid='" . $_POST['SupplierID'] . "'
				AND offers.expirydate>=CURRENT_DATE";

	$Result = DB_query($SQL);
	$_SESSION['offer' . $Identifier] = new Offer($_POST['SupplierID']);
	$_SESSION['offer' . $Identifier]->CurrCode = $Currency;
	while ($MyRow = DB_fetch_array($Result)) {
		$_SESSION['offer' . $Identifier]->add_to_offer($MyRow['offerid'], $MyRow['stockid'], $MyRow['quantity'], $MyRow['description'], $MyRow['price'], $MyRow['uom'], $MyRow['decimalplaces'], ConvertSQLDate($MyRow['expirydate']));
	}
}

if (isset($_POST['TenderType']) and $_POST['TenderType'] != 3 and isset($_SESSION['offer' . $Identifier]) and $_SESSION['offer' . $Identifier]->LinesOnOffer > 0 or isset($_POST['Update'])) {
	echo '<form method="post" action="', htmlspecialchars(basename(__FILE__)), '?identifier=', urlencode($Identifier), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/supplier.png" title="', _('Search'), '" alt="" />', ' ', _('Items to offer from'), ' ', $Supplier, '
		</p>';

	echo '<table>
			<thead>
				<tr>
					<th class="SortedColumn">', _('Stock ID'), '</th>
					<th class="SortedColumn">', _('Description'), '</th>
					<th>', _('Quantity'), '</th>
					<th>', _('UOM'), '</th>
					<th>', _('Price'), ' (', $Currency, ')</th>
					<th>', _('Line Total'), ' (', $Currency, ')</th>
					<th class="SortedColumn">', _('Expiry Date'), '</th>
					<th></th>
				</tr>
			</thead>';

	echo '<tbody>';
	foreach ($_SESSION['offer' . $Identifier]->LineItems as $LineItems) {
		if ($LineItems->Deleted == False) {

			echo '<input type="hidden" name="StockID' . $LineItems->LineNo . '" value="' . $LineItems->StockID . '" />';
			echo '<tr class="striped_row">
					<td>', $LineItems->StockID, '</td>
					<td>', $LineItems->ItemDescription, '</td>
					<td><input type="text" class="number" required="required" maxlebgth="11" name="Qty', $LineItems->LineNo, '" value="', locale_number_format($LineItems->Quantity, $LineItems->DecimalPlaces), '" /></td>
					<td>', $LineItems->Units, '</td>
					<td><input type="text" class="number" required="required" maxlebgth="11" name="Price', $LineItems->LineNo, '" value="', locale_number_format($LineItems->Price, 2, '.', ''), '" /></td>
					<td class="number">', locale_number_format($LineItems->Price * $LineItems->Quantity, 2), '</td>
					<td><input type="text" size="11" required="required" maxlebgth="10" class="date" name="expirydate', $LineItems->LineNo, '" value="', $LineItems->ExpiryDate, '" /></td>
					<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', urlencode($Identifier), '&Delete=', urlencode($LineItems->LineNo), '&Type=', urlencode($_POST['TenderType']), '">', _('Remove'), '</a></td>
				</tr>';
		}
	}
	echo '</tbody>';
	echo '</table>';
	echo '<input type="hidden" name="TenderType" value="' . $_POST['TenderType'] . '" />';
	if ($_POST['TenderType'] == 1) {
		echo '<br />
				<div class="centre">
					<input type="submit" name="Update" value="Update offer" />
					<input type="submit" name="Refresh" value="Refresh screen" />
				</div>';
	} else if ($_POST['TenderType'] == 2) {
		echo '<br />
				<div class="centre">
					<input type="submit" name="Save" value="Save offer" />
					<input type="submit" name="Refresh" value="Refresh screen" />
				</div>';
	}
	echo '</form>';
}

/*The supplier has chosen option 2
*/
if (isset($_POST['TenderType']) and $_POST['TenderType'] == 2 and !isset($_POST['Search']) or isset($_GET['Delete'])) {

	if (!isset($_SESSION['offer' . $Identifier])) {
		$_SESSION['offer' . $Identifier] = new Offer($_POST['SupplierID']);
	}
	echo '<form action="', htmlspecialchars(basename(__FILE__)), '?identifier=', $Identifier, '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', _('Search'), '" alt="" />', ' ', _('Search for Inventory Items'), '
		</p>';

	$SQL = "SELECT categoryid,
				categorydescription
			FROM stockcategory
			ORDER BY categorydescription";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) == 0) {
		echo '<font size="4" color="red">', _('Problem Report'), ':</font><br />', _('There are no stock categories currently defined please use the link below to set them up');
		echo '<br /><a href="', $RootPath, '/StockCategories.php">', _('Define Stock Categories'), '</a>';
		exit;
	}
	echo '<fieldset>
			<legend>', _('Search Criteria'), '</legend>';

	if (!isset($_POST['StockCat'])) {
		$_POST['StockCat'] = '';
	}
	echo '<field>
			<label for="StockCat">', _('In Stock Category'), ':</label>
			<select name="StockCat">';
	if ($_POST['StockCat'] == 'All') {
		echo '<option selected="selected" value="All">', _('All'), '</option>';
	} else {
		echo '<option value="All">', _('All'), '</option>';
	}
	while ($MyRow1 = DB_fetch_array($Result)) {
		if ($MyRow1['categoryid'] == $_POST['StockCat']) {
			echo '<option selected="selected" value="', $MyRow1['categoryid'], '">', $MyRow1['categorydescription'], '</option>';
		} else {
			echo '<option value="', $MyRow1['categoryid'], '">', $MyRow1['categorydescription'], '</option>';
		}
	}
	echo '</select>
		</field>';

	echo '<input type="hidden" name="TenderType" value="', $_POST['TenderType'], '" />';
	echo '<input type="hidden" name="SupplierID" value="', $_POST['SupplierID'], '" />';

	echo '<field>
			<label for="Keywords">', _('Enter partial'), '<b> ', _('Description'), '</b>:</label>';
	if (isset($_POST['Keywords'])) {
		echo '<input type="search" name="Keywords" value="', $_POST['Keywords'], '" size="20" maxlength="25" />';
	} else {
		echo '<input type="search" name="Keywords" size="20" maxlength="25" />';
	}
	echo '</field>';

	echo '<h1>', _('OR'), '</h1>';

	echo '<field>
			<label for="StockCode">', _('Enter partial'), ' <b>', _('Stock Code'), '</b>:</label>';
	if (isset($_POST['StockCode'])) {
		echo '<input type="search" autofocus="autofocus" name="StockCode" value="', $_POST['StockCode'], '" size="15" maxlength="18" />';
	} else {
		echo '<input type="search" autofocus="autofocus" name="StockCode" size="15" maxlength="18" />';
	}
	echo '</field>
		</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="Search" value="', _('Search Now'), '" />
		</div>
	</form>';
}

/*The supplier has chosen option 3
*/
if (isset($_POST['TenderType']) and $_POST['TenderType'] == 3 and !isset($_POST['Search']) or isset($_GET['Delete'])) {

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/supplier.png" title="', _('Tenders'), '" alt="" />', ' ', _('Tenders Waiting For Offers'), '
		</p>';

	echo '<form action="', basename(__FILE__), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<input type="hidden" name="TenderType" value="3" />';

	$SQL = "SELECT DISTINCT tendersuppliers.tenderid,
				suppliers.currcode
			FROM tendersuppliers
			LEFT JOIN suppliers
			ON suppliers.supplierid=tendersuppliers.supplierid
			LEFT JOIN tenders
			ON tenders.tenderid=tendersuppliers.tenderid
			WHERE tendersuppliers.supplierid='" . $_POST['SupplierID'] . "'
			AND tenders.closed=0
			AND tendersuppliers.responded=0
			ORDER BY tendersuppliers.tenderid";
	$Result = DB_query($SQL);
	echo '<table>';
	echo '<tr>
			<th colspan="13">', _('Outstanding Tenders Waiting For Offer'), '</th>
		</tr>';
	while ($MyRow = DB_fetch_row($Result)) {
		$LocationSQL = "SELECT tenderid,
							locations.locationname,
							address1,
							address2,
							address3,
							address4,
							address5,
							address6,
							telephone
						FROM tenders
						INNER JOIN locations
						ON tenders.location=locations.loccode
						WHERE closed=0
						AND tenderid='" . $MyRow[0] . "'";
		$LocationResult = DB_query($LocationSQL);
		$MyLocationRow = DB_fetch_row($LocationResult);
		echo '<tr class="striped_row">
				<td valign="top">', _('Deliver To'), ':</td>
				<td valign="top">';
		for ($i = 1;$i < 8;$i++) {
			if ($MyLocationRow[$i] != '') {
				echo $MyLocationRow[$i] . '<br />';
			}
		}
		echo '</td>
				<th colspan="8">', _('Tender Number'), ': ', $MyRow[0], '</th>
				<input type="hidden" value="', $MyRow[0], '" name="Tender" />
				<th><input type="submit" value="', _('Process Tender'), '" name="Process" /></th>
			</tr>';
		$ItemSQL = "SELECT tenderitems.tenderid,
						tenderitems.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						purchdata.suppliers_partno,
						tenderitems.quantity,
						tenderitems.units,
						tenders.requiredbydate,
						purchdata.suppliersuom
					FROM tenderitems
					LEFT JOIN stockmaster
					ON tenderitems.stockid=stockmaster.stockid
					LEFT JOIN purchdata
					ON tenderitems.stockid=purchdata.stockid
					AND purchdata.supplierno='" . $_POST['SupplierID'] . "'
					LEFT JOIN tenders
					ON tenders.tenderid=tenderitems.tenderid
					WHERE tenderitems.tenderid='" . $MyRow[0] . "'";
		$ItemResult = DB_query($ItemSQL);
		echo '<tr>
				<th>', stripslashes($_SESSION['CompanyRecord']['coyname']), '<br />', _('Item Code'), '</th>
				<th>', _('Item Description'), '</th>
				<th>', $Supplier, '<br />', _('Item Code'), '</th>
				<th>', _('Quantity'), '<br />', _('Required'), '</th>
				<th>', stripslashes($_SESSION['CompanyRecord']['coyname']), '<br />', _('Units of Measure'), '</th>
				<th>', _('Required By'), '</th>
				<th>', _('Quantity'), '<br />', _('Offered'), '</th>
				<th>', $Supplier, '<br />', _('Units of Measure'), '</th>
				<th>', _('Currency'), '</th>
				<th>', $Supplier, '<br />', _('Price'), '</th>
				<th>', _('Delivery By'), '</th>
			</tr>';
		$i = 0;
		while ($MyItemRow = DB_fetch_array($ItemResult)) {
			echo '<tr class="striped_row">
					<td>', $MyItemRow['stockid'], '</td>
					<td>', $MyItemRow['description'], '</td>
					<input type="hidden" name="StockID', $i, '" value="', $MyItemRow['stockid'], '" />
					<input type="hidden" name="ItemDescription', $i, '" value="', $MyItemRow['description'], '" />
					<td>', $MyItemRow['suppliers_partno'], '</td>
					<td class="number">', locale_number_format($MyItemRow['quantity'], $MyItemRow['decimalplaces']), '</td>
					<td>', $MyItemRow['units'], '</td>
					<td>', ConvertSQLDate($MyItemRow['requiredbydate']), '</td>';

			if ($MyItemRow['suppliersuom'] == '') {
				$MyItemRow['suppliersuom'] = $MyItemRow['units'];
			}
			echo '<td><input type="text" class="number" required="required" maxlength="10" size="10" name="Qty', $i, '" value="', locale_number_format($MyItemRow['quantity'], $MyItemRow['decimalplaces']), '" /></td>
				<input type="hidden" name="UOM', $i, '" value="', $MyItemRow['units'], '" />
				<input type="hidden" name="DecimalPlaces', $i, '" value="', $MyItemRow['decimalplaces'], '" />
				<td>', $MyItemRow['suppliersuom'], '</td>
				<td>', $MyRow[1], '</td>
				<td><input type="text" class="number" required="required" maxlength="10" size="10" name="Price', $i, '" value="0.00" /></td>
				<td><input type="text" class="date" required="required" maxlength="10" name="RequiredByDate', $i, '" size="11" value="', ConvertSQLDate($MyItemRow['requiredbydate']), '" /></td>
			</tr>';
			++$i;
		}
		echo '</form>';
	}
	echo '</table>';
}

if (isset($_POST['Search'])) {
	/*ie seach for stock items */
	echo '<form method="post" action="', htmlspecialchars(basename(__FILE__)), '?identifier=', urlencode($Identifier), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/supplier.png" title="', _('Tenders'), '" alt="" />', ' ', _('Select items to offer from'), ' ', $Supplier, '
		</p>';

	if ($_POST['Keywords'] and $_POST['StockCode']) {
		prnMsg(_('Stock description keywords have been used in preference to the Stock code extract entered'), 'info');
	}
	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		if ($_POST['StockCat'] == 'All') {
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockmaster.mbflag!='D'
					AND stockmaster.mbflag!='A'
					AND stockmaster.mbflag!='K'
					AND stockmaster.discontinued!=1
					AND stockmaster.description " . LIKE . " '$SearchString'
					ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockmaster.mbflag!='D'
					AND stockmaster.mbflag!='A'
					AND stockmaster.mbflag!='K'
					AND stockmaster.discontinued!=1
					AND stockmaster.description " . LIKE . " '$SearchString'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid";
		}

	} elseif ($_POST['StockCode']) {

		$_POST['StockCode'] = '%' . $_POST['StockCode'] . '%';

		if ($_POST['StockCat'] == 'All') {
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockmaster.mbflag!='D'
					AND stockmaster.mbflag!='A'
					AND stockmaster.mbflag!='K'
					AND stockmaster.discontinued!=1
					AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'
					ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockmaster.mbflag!='D'
					AND stockmaster.mbflag!='A'
					AND stockmaster.mbflag!='K'
					AND stockmaster.discontinued!=1
					AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid";
		}

	} else {
		if ($_POST['StockCat'] == 'All') {
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockmaster.mbflag!='D'
					AND stockmaster.mbflag!='A'
					AND stockmaster.mbflag!='K'
					AND stockmaster.discontinued!=1
					ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE stockmaster.mbflag!='D'
					AND stockmaster.mbflag!='A'
					AND stockmaster.mbflag!='K'
					AND stockmaster.discontinued!=1
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid";
		}
	}

	$ErrMsg = _('There is a problem selecting the part records to display because');
	$DbgMsg = _('The SQL statement that failed was');
	$SearchResult = DB_query($SQL, $ErrMsg, $DbgMsg);

	if (DB_num_rows($SearchResult) == 0 and $Debug == 1) {
		prnMsg(_('There are no products to display matching the criteria provided'), 'warn');
	}
	if (DB_num_rows($SearchResult) == 1) {

		$MyRow = DB_fetch_array($SearchResult);
		$_GET['NewItem'] = $MyRow['stockid'];
	}

	if (isset($SearchResult)) {

		echo '<table cellpadding="1">
				<thead>
					<tr>
						<th class="SortedColumn">', _('Code'), '</th>
						<th class="SortedColumn">', _('Description'), '</th>
						<th>', _('Units'), '</th>
						<th>', _('Image'), '</th>
						<th>', _('Quantity'), '</th>
						<th>', _('Price'), ' (', $Currency, ')</th>
					</tr>
				</thead>';

		$i = 0;

		$PartsDisplayed = 0;
		echo '<tbody>';
		while ($MyRow = DB_fetch_array($SearchResult)) {

			$SupportedImgExt = array('png', 'jpg', 'jpeg');
			$ImageFileArray = glob($_SESSION['part_pics_dir'] . '/' . $MyRow['stockid'] . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE);
			$ImageFile = reset($ImageFileArray);
			if (extension_loaded('gd') and function_exists('gd_info') and file_exists($ImageFile)) {
				$ImageSource = '<img src="GetStockImage.php?automake=1&textcolor=FFFFFF&bgcolor=CCCCCC&StockID=' . urlencode($MyRow['stockid']) . '&text=&width=64&height=64" alt="" />';
			} else if (file_exists($ImageFile)) {
				$ImageSource = '<img src="' . $ImageFile . '" height="64" width="64" />';
			} else {
				$ImageSource = _('No Image');
			}

			$UOMsql = "SELECT conversionfactor,
						suppliersuom,
						unitsofmeasure.unitname
					FROM purchdata
					LEFT JOIN unitsofmeasure
					ON purchdata.suppliersuom=unitsofmeasure.unitid
					WHERE supplierno='" . $_POST['SupplierID'] . "'
					AND stockid='" . $MyRow['stockid'] . "'";

			$UOMresult = DB_query($UOMsql);
			if (DB_num_rows($UOMresult) > 0) {
				$UOMrow = DB_fetch_array($UOMresult);
				if (mb_strlen($UOMrow['suppliersuom']) > 0) {
					$UOM = $UOMrow['unitname'];
				} else {
					$UOM = $MyRow['units'];
				}
			} else {
				$UOM = $MyRow['units'];
			}
			echo '<tr class="striped_row">
					<td>', $MyRow['stockid'], '</td>
					<td>', $MyRow['description'], '</td>
					<td>', $UOM, '</td>
					<td>', $ImageSource, '</td>
					<td><input class="number" type="text" size="6" value="0" name="Qty', $i, '" /></td>
					<td><input class="number" type="text" size="12" value="0" name="Price', $i, '" /></td>
					<input type="hidden" size="12" value="', $MyRow['stockid'], '" name="StockID', $i, '" />
					<input type="hidden" value="', $UOM, '" name="uom', $i, '" />
				</tr>';
			++$i;
			$PartsDisplayed++;
			if ($PartsDisplayed == $Maximum_Number_Of_Parts_To_Show) {
				break;
			}
			#end of page full new headings if
			
		}
		#end of while loop
		echo '</tbody>';
		echo '</table>';
		if ($PartsDisplayed == $Maximum_Number_Of_Parts_To_Show) {

			/*$Maximum_Number_Of_Parts_To_Show defined in config.php */
			prnMsg(_('Only the first') . ' ' . $Maximum_Number_Of_Parts_To_Show . ' ' . _('can be displayed') . '. ' . _('Please restrict your search to only the parts required'), 'info');
		}
		echo '<a name="end"></a>
				<div class="centre">
					<input type="submit" name="NewItem" value="', _('Add to Offer'), '" />
				</div>';
	} #end if SearchResults to show
	echo '<input type="hidden" name="TenderType" value="', $_POST['TenderType'], '" />';
	echo '<input type="hidden" name="SupplierID" value="', $_POST['SupplierID'], '" />';

	echo '</form>';

} //end of if search
include ('includes/footer.php');

?>