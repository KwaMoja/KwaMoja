<?php
include ('includes/DefineTenderClass.php');
include ('includes/SQL_CommonFunctions.php');
include ('includes/session.php');

if (empty($_GET['identifier'])) {
	/*unique session identifier to ensure that there is no conflict with other supplier tender sessions on the same machine  */
	$Identifier = date('U');
} else {
	$Identifier = $_GET['identifier'];
}

if (isset($_GET['New']) and isset($_SESSION['tender' . $Identifier])) {
	unset($_SESSION['tender' . $Identifier]);
}

if (isset($_GET['New']) and $_SESSION['CanCreateTender'] == 0) {
	$Title = _('Authorisation Problem');
	include ('includes/header.php');
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/supplier.png" title="', $Title, '" alt="" />  ', $Title, '
		</p>';
	prnMsg(_('You do not have authority to create supplier tenders for this company.') . '<br />' . _('Please see your system administrator'), 'warn');
	include ('includes/footer.php');
	exit;
}

if (isset($_GET['Edit']) and $_SESSION['CanCreateTender'] == 0) {
	$Title = _('Authorisation Problem');
	include ('includes/header.php');
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/supplier.png" title="', $Title, '" alt="" />  ', $Title, '
		</p>';
	prnMsg(_('You do not have authority to amend supplier tenders for this company.') . '<br />' . _('Please see your system administrator'), 'warn');
	include ('includes/footer.php');
	exit;
}

if (isset($_POST['Close'])) {
	$SQL = "UPDATE tenders SET closed=1 WHERE tenderid='" . $_SESSION['tender' . $Identifier]->TenderId . "'";
	$Result = DB_query($SQL);
	$_GET['Edit'] = 'Yes';
	unset($_SESSION['tender' . $Identifier]);
}

$ShowTender = 0;

if (isset($_GET['ID'])) {
	$SQL = "SELECT tenderid,
					location,
					address1,
					address2,
					address3,
					address4,
					address5,
					address6,
					telephone,
					requiredbydate
				FROM tenders
				INNER JOIN locationusers
					ON locationusers.loccode=tenders.location
					AND locationusers.userid='" . $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				WHERE tenderid='" . $_GET['ID'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	if (isset($_SESSION['tender' . $Identifier])) {
		unset($_SESSION['tender' . $Identifier]);
	}
	$_SESSION['tender' . $Identifier] = new Tender();
	$_SESSION['tender' . $Identifier]->TenderId = $MyRow['tenderid'];
	$_SESSION['tender' . $Identifier]->Location = $MyRow['location'];
	$_SESSION['tender' . $Identifier]->DelAdd1 = $MyRow['address1'];
	$_SESSION['tender' . $Identifier]->DelAdd2 = $MyRow['address2'];
	$_SESSION['tender' . $Identifier]->DelAdd3 = $MyRow['address3'];
	$_SESSION['tender' . $Identifier]->DelAdd4 = $MyRow['address4'];
	$_SESSION['tender' . $Identifier]->DelAdd5 = $MyRow['address5'];
	$_SESSION['tender' . $Identifier]->DelAdd6 = $MyRow['address6'];
	$_SESSION['tender' . $Identifier]->RequiredByDate = $MyRow['requiredbydate'];

	$SQL = "SELECT tenderid,
					tendersuppliers.supplierid,
					suppliers.suppname,
					tendersuppliers.email
				FROM tendersuppliers
				LEFT JOIN suppliers
					ON tendersuppliers.supplierid=suppliers.supplierid
				WHERE tenderid='" . $_GET['ID'] . "'";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		$_SESSION['tender' . $Identifier]->add_supplier_to_tender($MyRow['supplierid'], $MyRow['suppname'], $MyRow['email']);
	}

	$SQL = "SELECT tenderid,
					tenderitems.stockid,
					tenderitems.quantity,
					stockmaster.description,
					tenderitems.units,
					stockmaster.decimalplaces
				FROM tenderitems
				LEFT JOIN stockmaster
					ON tenderitems.stockid=stockmaster.stockid
				WHERE tenderid='" . $_GET['ID'] . "'";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		$_SESSION['tender' . $Identifier]->add_item_to_tender($_SESSION['tender' . $Identifier]->LinesOnTender, $MyRow['stockid'], $MyRow['quantity'], $MyRow['description'], $MyRow['units'], $MyRow['decimalplaces'], DateAdd(date($_SESSION['DefaultDateFormat']), 'm', 3));
	}
	$ShowTender = 1;
}

if (isset($_GET['Edit'])) {
	$Title = _('Edit an Existing Supplier Tender Request');
	include ('includes/header.php');
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/supplier.png" title="', _('Purchase Order Tendering'), '" alt="" />  ', $Title, '
		</p>';
	$SQL = "SELECT tenderid,
					location,
					address1,
					address2,
					address3,
					address4,
					address5,
					address6,
					telephone
				FROM tenders
				INNER JOIN locationusers
					ON locationusers.loccode=tenders.location
					AND locationusers.userid='" . $_SESSION['UserID'] . "'
					AND locationusers.canupd=1
				WHERE closed=0
					AND requiredbydate >= CURRENT_DATE";
	$Result = DB_query($SQL);
	echo '<table>
			<thead>
				<tr>
					<th class="SortedColumn">', _('Tender ID'), '</th>
					<th class="SortedColumn">', _('Location'), '</th>
					<th>', _('Address 1'), '</th>
					<th>', _('Address 2'), '</th>
					<th>', _('Address 3'), '</th>
					<th>', _('Address 4'), '</th>
					<th>', _('Address 5'), '</th>
					<th>', _('Address 6'), '</th>
					<th>', _('Telephone'), '</th>
					<th></th>
				</tr>
			</thead>';

	echo '<tbody>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
				<td>', $MyRow['tenderid'], '</td>
				<td>', $MyRow['location'], '</td>
				<td>', $MyRow['address1'], '</td>
				<td>', $MyRow['address2'], '</td>
				<td>', $MyRow['address3'], '</td>
				<td>', $MyRow['address4'], '</td>
				<td>', $MyRow['address5'], '</td>
				<td>', $MyRow['address6'], '</td>
				<td>', $MyRow['telephone'], '</td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', urlencode($Identifier), '&amp;ID=', urlencode($MyRow['tenderid']), '">', _('Edit'), '</a></td>
			</tr>';
	}
	echo '</tbody>';
	echo '</table>';
	include ('includes/footer.php');
	exit;
} else if (isset($_GET['ID']) or (isset($_SESSION['tender' . $Identifier]->TenderId))) {
	$Title = _('Edit an Existing Supplier Tender Request');
	include ('includes/header.php');
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/supplier.png" title="', _('Purchase Order Tendering'), '" alt="" />', $Title, '
		</p>';
} else {
	$Title = _('Create a New Supplier Tender Request');
	include ('includes/header.php');
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/supplier.png" title="', _('Purchase Order Tendering'), '" alt="" />', $Title, '
		</p>';
}

if (isset($_POST['Save'])) {
	$_SESSION['tender' . $Identifier]->RequiredByDate = $_POST['RequiredByDate'];
	$_SESSION['tender' . $Identifier]->save();
	$_SESSION['tender' . $Identifier]->EmailSuppliers();
	prnMsg(_('The tender has been successfully saved'), 'success');
	include ('includes/footer.php');
	exit;
}

if (isset($_GET['DeleteSupplier'])) {
	$_SESSION['tender' . $Identifier]->remove_supplier_from_tender($_GET['DeleteSupplier']);
	$ShowTender = 1;
}

if (isset($_GET['DeleteItem'])) {
	$_SESSION['tender' . $Identifier]->remove_item_from_tender($_GET['DeleteItem']);
	$ShowTender = 1;
}

if (isset($_POST['SelectedSupplier'])) {
	$SQL = "SELECT suppname,
					email
				FROM suppliers
				WHERE supplierid='" . $_POST['SelectedSupplier'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	if (mb_strlen($MyRow['email']) > 0) {
		$_SESSION['tender' . $Identifier]->add_supplier_to_tender($_POST['SelectedSupplier'], $MyRow['suppname'], $MyRow['email']);
	} else {
		prnMsg(_('The supplier must have an email set up or they cannot be part of a tender'), 'warn');
	}
	$ShowTender = 1;
}

if (isset($_POST['NewItem']) and !isset($_POST['Refresh'])) {
	foreach ($_POST as $Key => $Value) {
		if (mb_substr($Key, 0, 7) == 'StockID') {
			$Index = mb_substr($Key, 7, mb_strlen($Key) - 7);
			$StockId = $Value;
			$Quantity = filter_number_format($_POST['Qty' . $Index]);
			$UOM = $_POST['UOM' . $Index];
			$SQL = "SELECT description,
							decimalplaces
						FROM stockmaster
						WHERE stockid='" . $StockId . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_array($Result);
			$_SESSION['tender' . $Identifier]->add_item_to_tender($_SESSION['tender' . $Identifier]->LinesOnTender, $StockId, $Quantity, $MyRow['description'], $UOM, $MyRow['decimalplaces'], DateAdd(date($_SESSION['DefaultDateFormat']), 'm', 3));
			unset($UOM);
		}
	}
	$ShowTender = 1;
}

if (!isset($_SESSION['tender' . $Identifier]) or isset($_POST['LookupDeliveryAddress']) or $ShowTender == 1) {

	/* Show Tender header screen */
	if (!isset($_SESSION['tender' . $Identifier])) {
		$_SESSION['tender' . $Identifier] = new Tender();
	}
	if (!isset($_SESSION['tender' . $Identifier]->RequiredByDate)) {
		$_SESSION['tender' . $Identifier]->RequiredByDate = FormatDateForSQL(date($_SESSION['DefaultDateFormat']));
	}
	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', urlencode($Identifier), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<fieldset>
			<legend>', _('Tender header details'), '</legend>';

	echo '<field>
			<label for="RequiredByDate">', _('Delivery Must Be Made Before'), '</label>
			<input type="text" class="date" required="required" maxlength="10" name="RequiredByDate" size="11" value="', ConvertSQLDate($_SESSION['tender' . $Identifier]->RequiredByDate), '" />
		</field>';

	if (!isset($_POST['StkLocation']) or $_POST['StkLocation'] == '') {
		/* If this is the first time
		 * the form loaded set up defaults */

		$_POST['StkLocation'] = $_SESSION['UserStockLocation'];

		$SQL = "SELECT deladd1,
						deladd2,
						deladd3,
						deladd4,
						deladd5,
						deladd6,
						tel,
						contact
					FROM locations
					INNER JOIN locationusers
						ON locationusers.loccode=.locations.loccode
						AND locationusers.userid='" . $_SESSION['UserID'] . "'
						AND locationusers.canupd=1
					WHERE locations.loccode='" . $_POST['StkLocation'] . "'";

		$LocnAddrResult = DB_query($SQL);
		if (DB_num_rows($LocnAddrResult) == 1) {
			$LocnRow = DB_fetch_array($LocnAddrResult);
			$_POST['DelAdd1'] = $LocnRow['deladd1'];
			$_POST['DelAdd2'] = $LocnRow['deladd2'];
			$_POST['DelAdd3'] = $LocnRow['deladd3'];
			$_POST['DelAdd4'] = $LocnRow['deladd4'];
			$_POST['DelAdd5'] = $LocnRow['deladd5'];
			$_POST['DelAdd6'] = $LocnRow['deladd6'];
			$_POST['Tel'] = $LocnRow['tel'];
			$_POST['Contact'] = $LocnRow['contact'];

			$_SESSION['tender' . $Identifier]->Location = $_POST['StkLocation'];
			$_SESSION['tender' . $Identifier]->DelAdd1 = $_POST['DelAdd1'];
			$_SESSION['tender' . $Identifier]->DelAdd2 = $_POST['DelAdd2'];
			$_SESSION['tender' . $Identifier]->DelAdd3 = $_POST['DelAdd3'];
			$_SESSION['tender' . $Identifier]->DelAdd4 = $_POST['DelAdd4'];
			$_SESSION['tender' . $Identifier]->DelAdd5 = $_POST['DelAdd5'];
			$_SESSION['tender' . $Identifier]->DelAdd6 = $_POST['DelAdd6'];
			$_SESSION['tender' . $Identifier]->Telephone = $_POST['Tel'];
			$_SESSION['tender' . $Identifier]->Contact = $_POST['Contact'];

		} else {
			/*The default location of the user is crook */
			prnMsg(_('The default stock location set up for this user is not a currently defined stock location') . '. ' . _('Your system administrator needs to amend your user record'), 'error');
		}

	} elseif (isset($_POST['LookupDeliveryAddress'])) {

		$SQL = "SELECT deladd1,
						deladd2,
						deladd3,
						deladd4,
						deladd5,
						deladd6,
						tel,
						contact
					FROM locations
					INNER JOIN locationusers
						ON locationusers.loccode=.locations.loccode
						AND locationusers.userid='" . $_SESSION['UserID'] . "'
						AND locationusers.canupd=1
					WHERE locations.loccode='" . $_POST['StkLocation'] . "'";

		$LocnAddrResult = DB_query($SQL);
		if (DB_num_rows($LocnAddrResult) == 1) {
			$LocnRow = DB_fetch_array($LocnAddrResult);
			$_POST['DelAdd1'] = $LocnRow['deladd1'];
			$_POST['DelAdd2'] = $LocnRow['deladd2'];
			$_POST['DelAdd3'] = $LocnRow['deladd3'];
			$_POST['DelAdd4'] = $LocnRow['deladd4'];
			$_POST['DelAdd5'] = $LocnRow['deladd5'];
			$_POST['DelAdd6'] = $LocnRow['deladd6'];
			$_POST['Tel'] = $LocnRow['tel'];
			$_POST['Contact'] = $LocnRow['contact'];

			$_SESSION['tender' . $Identifier]->Location = stripslashes($_POST['StkLocation']);
			$_SESSION['tender' . $Identifier]->DelAdd1 = $_POST['DelAdd1'];
			$_SESSION['tender' . $Identifier]->DelAdd2 = $_POST['DelAdd2'];
			$_SESSION['tender' . $Identifier]->DelAdd3 = $_POST['DelAdd3'];
			$_SESSION['tender' . $Identifier]->DelAdd4 = $_POST['DelAdd4'];
			$_SESSION['tender' . $Identifier]->DelAdd5 = $_POST['DelAdd5'];
			$_SESSION['tender' . $Identifier]->DelAdd6 = $_POST['DelAdd6'];
			$_SESSION['tender' . $Identifier]->Telephone = $_POST['Tel'];
			$_SESSION['tender' . $Identifier]->Contact = $_POST['Contact'];
		}
	}
	echo '<field>
			<label for="StkLocation">', _('Warehouse'), ':</label>
			<select required="required" name="StkLocation" onchange="ReloadForm(form1.LookupDeliveryAddress)">';

	$SQL = "SELECT locationname,
					locations.loccode
					FROM locations
					INNER JOIN locationusers
						ON locationusers.loccode=.locations.loccode
						AND locationusers.userid='" . $_SESSION['UserID'] . "'
						AND locationusers.canupd=1";

	$LocnResult = DB_query($SQL);

	while ($LocnRow = DB_fetch_array($LocnResult)) {
		if ((isset($_SESSION['tender' . $Identifier]->Location) and $_SESSION['tender' . $Identifier]->Location == $LocnRow['loccode'])) {
			echo '<option selected="selected" value="', $LocnRow['loccode'], '">', $LocnRow['locationname'], '</option>';
		} else {
			echo '<option value="', $LocnRow['loccode'], '">', $LocnRow['locationname'], '</option>';
		}
	}

	echo '</select>
		<input type="submit" name="LookupDeliveryAddress" value="', _('Select'), '" />
	</field>';

	/* Display the details of the delivery location
	*/
	echo '<field>
			<label for="Contact">', _('Delivery Contact'), ':</label>
			<td><input type="text" name="Contact" size="41"  value="', $_SESSION['tender' . $Identifier]->Contact, '" /></td>
		</field>';

	echo '<field>
			<label for="DelAdd1">', _('Address'), ' 1 :</label>
			<input type="text" name="DelAdd1" size="41" maxlength="40" value="', $_POST['DelAdd1'], '" />
		</field>';
	echo '<field>
			<label for="DelAdd2">', _('Address'), ' 2 :</label>
			<input type="text" name="DelAdd2" size="41" maxlength="40" value="', $_POST['DelAdd2'], '" />
		</field>';
	echo '<field>
			<label for="DelAdd3">', _('Address'), ' 3 :</label>
			<input type="text" name="DelAdd3" size="41" maxlength="40" value="', $_POST['DelAdd3'], '" />
		</field>';
	echo '<field>
			<label for="DelAdd4">', _('Address'), ' 4 :</label>
			<td><input type="text" name="DelAdd4" size="21" maxlength="20" value="', $_POST['DelAdd4'], '" /></td>
		</field>';
	echo '<field>
			<label for="DelAdd5">', _('Address'), ' 5 :</label>
			<td><input type="text" name="DelAdd5" size="16" maxlength="15" value="', $_POST['DelAdd5'], '" /></td>
		</field>';
	echo '<field>
			<label for="DelAdd6">', _('Address'), ' 6 :</label>
			<td><input type="text" name="DelAdd6" size="16" maxlength="15" value="', $_POST['DelAdd6'], '" /></td>
		</field>';
	echo '<field>
			<label for="Tel">', _('Phone'), ':</label>
			<td><input type="tel" name="Tel" size="31" maxlength="30" value="' . $_SESSION['tender' . $Identifier]->Telephone . '" /></td>
		</field>';
	echo '</fieldset>';

	/* Display the supplier/item details
	*/
	echo '<table>';

	/* Supplier Details
	*/
	echo '<tr>
			<td valign="top">';

	if (sizeof($_SESSION['tender' . $Identifier]->Suppliers) > 0) {
		echo '<table>
				<tr>
					<th colspan="4">', _('Suppliers To Send Tender'), '</th>
				</tr>
				<tr>
					<th>', _('Supplier Code'), '</th>
					<th>', _('Supplier Name'), '</th>
					<th>', _('Email Address'), '</th>
					<th></th>
				</tr>';
		foreach ($_SESSION['tender' . $Identifier]->Suppliers as $Supplier) {
			echo '<tr>
					<td>', stripslashes($Supplier->SupplierCode), '</td>
					<td>', $Supplier->SupplierName, '</td>
					<td>', $Supplier->EmailAddress, '</td>
					<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', urlencode($Identifier), '&amp;DeleteSupplier=', urlencode($Supplier->SupplierCode), '">', _('Delete'), '</a></td>
				</tr>';
		}
		echo '</table>';
	}

	echo '</td>
		<td valign="top">';
	/* Item Details
	*/
	if (sizeof($_SESSION['tender' . $Identifier]->LineItems) > 0) {
		echo '<table>
				<tr>
					<th colspan="6"><h3>', _('Items in Tender'), '</h3></th>
				</tr>
				<tr>
					<th>', _('Stock ID'), '</th>
					<th>', _('Description'), '</th>
					<th>', _('Quantity'), '</th>
					<th>', _('UOM'), '</th>
					<th></th>
				</tr>';

		foreach ($_SESSION['tender' . $Identifier]->LineItems as $LineItems) {
			if ($LineItems->Deleted == False) {
				echo '<tr class="striped_row">
						<td>', $LineItems->StockID, '</td>
						<td>', $LineItems->ItemDescription, '</td>
						<td class="number">', locale_number_format($LineItems->Quantity, $LineItems->DecimalPlaces), '</td>
						<td>', $LineItems->Units, '</td>
						<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', urlencode($Identifier), '&amp;DeleteItem=', urlencode($LineItems->LineNo), '">', _('Delete'), '</a></td>
					</tr>';
			}
		}
		echo '</table>';
	}
	echo '</td>
		</tr>
	</table>';

	echo '<div class="centre">
			<input type="submit" name="Suppliers" value="', _('Select Suppliers'), '" />
			<input type="submit" name="Items" value="', _('Select Items For Tender'), '" />
		</div>';

	echo '<div class="centre">';
	if ($_SESSION['tender' . $Identifier]->LinesOnTender > 0 and $_SESSION['tender' . $Identifier]->SuppliersOnTender > 0) {
		echo '<input type="submit" name="Close" value="', _('Close This Tender'), '" />';
	}
	if ($_SESSION['tender' . $Identifier]->LinesOnTender > 0 and $_SESSION['tender' . $Identifier]->SuppliersOnTender > 0) {

		echo '<input type="submit" name="Save" value="', _('Save Tender'), '" />
			</div>';
	}
	echo '</form>';
	include ('includes/footer.php');
	exit;
}

if (isset($_POST['Suppliers'])) {
	echo '<form action="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', urlencode($Identifier), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', _('Search'), '" alt="" />', ' ', _('Search for Suppliers'), '
		</p>';

	echo '<fieldset>
			<legend>', _('Supplier Search Criteria'), '</legend>';

	echo '<field>
			<label for="Keywords">', _('Enter a partial Name'), ':</label>';
	if (isset($_POST['Keywords'])) {
		echo '<input type="search" name="Keywords" value="', $_POST['Keywords'], '" size="20" maxlength="25" />';
	} else {
		echo '<input type="search" name="Keywords" size="20" maxlength="25" />';
	}
	echo '</field>';

	echo '<h1>', _('OR'), '</h1>';

	echo '<field>
			<label for="SupplierCode">', _('Enter a partial Code'), ':</label>';
	if (isset($_POST['SupplierCode'])) {
		echo '<input type="search" name="SupplierCode" value="', $_POST['SupplierCode'], '" size="15" maxlength="18" />';
	} else {
		echo '<input type="search" name="SupplierCode" size="15" maxlength="18" />';
	}
	echo '</field>
		</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="SearchSupplier" value="', _('Search Now'), '" />
		</div>';

	echo '</form>';
}

if (isset($_POST['SearchSupplier']) or isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Previous'])) {

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', urlencode($Identifier), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	if (!isset($_POST['PageOffset'])) {
		$_POST['PageOffset'] = 0;
	}
	if (isset($_POST['Next'])) {
		$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
	}
	if (isset($_POST['Previous'])) {
		$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
	}
	echo '<input type="hidden" name="Keywords" value="', $_POST['Keywords'], '" />';
	echo '<input type="hidden" name="SupplierCode" value="', $_POST['SupplierCode'], '" />';
	echo '<input type="hidden" name="PageOffset" value="', $_POST['PageOffset'], '" />';
	$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
	//insert wildcard characters in spaces
	$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
	$_POST['SupplierCode'] = mb_strtoupper($_POST['SupplierCode']);
	$SQL = "SELECT supplierid,
					suppname,
					currcode,
					address1,
					address2,
					address3,
					address4
				FROM suppliers
				WHERE suppname " . LIKE . " '" . $SearchString . "'
					AND supplierid " . LIKE . " '%" . $_POST['SupplierCode'] . "%'
					AND email<>''
				ORDER BY suppname
				LIMIT " . ($_SESSION['DisplayRecordsMax'] * $_POST['PageOffset']) . ", " . ($_SESSION['DisplayRecordsMax']);

	$Result = DB_query($SQL);
} //end of if search
if (isset($_POST['SearchSupplier'])) {
	$ListCount = DB_num_rows($Result);
	if ($ListCount == $_SESSION['DisplayRecordsMax']) {
		$Next = 'Active';
	} else {
		$Next = 'InActive';
	}
	if ($_POST['PageOffset'] > 0) {
		$Previous = 'Active';
	} else {
		$Previous = 'InActive';
	}

	if ($Previous == 'Active') {
		echo '<input type="submit" name="Previous" value="', _('Previous'), '" />';
	} else {
		echo '<input type="submit" name="Previous" value="', _('Previous'), '" disabled />';
	}
	if ($Next == 'Active') {
		echo '<input type="submit" name="Next" value="', _('Next'), '" />';
	} else {
		echo '<input type="submit" name="Next" value="', _('Next'), '" disabled />';
	}

	echo '<input type="hidden" name="SearchSupplier" value="', _('Search Now'), '" />';
	echo '<table cellpadding="2">
			<thead>
				<tr>
					<th class="SortedColumn">', _('Code'), '</th>
					<th class="SortedColumn">', _('Supplier Name'), '</th>
					<th>', _('Currency'), '</th>
					<th>', _('Address 1'), '</th>
					<th>', _('Address 2'), '</th>
					<th>', _('Address 3'), '</th>
					<th>', _('Address 4'), '</th>
				</tr>
			</thead>';
	$j = 1;

	$RowIndex = 0;
	echo '<tbody>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
				<td><input type="submit" name="SelectedSupplier" value="', $MyRow['supplierid'], '" /></td>
				<td>', $MyRow['suppname'], '</td>
				<td>', $MyRow['currcode'], '</td>
				<td>', $MyRow['address1'], '</td>
				<td>', $MyRow['address2'], '</td>
				<td>', $MyRow['address3'], '</td>
				<td>', $MyRow['address4'], '</td>
			</tr>';
		$RowIndex = $RowIndex + 1;
		//end of page full new headings if
		
	}
	//end of while loop
	echo '</tbody>';
	echo '</table>';
	echo '</form>';
}

/*The supplier has chosen option 2
*/
if (isset($_POST['Items'])) {
	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', urlencode($Identifier), '" method="post">';
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
		echo '<p class="bad">', _('Problem Report'), ':</p><br />', _('There are no stock categories currently defined please use the link below to set them up');
		echo '<a href="', $RootPath, '/StockCategories.php">', _('Define Stock Categories'), '</a>';
		exit;
	}
	echo '<fieldset>
			<legend>', _('Search Criteria'), '</legend>';

	echo '<field>
			<label for="StockCat">', _('In Stock Category'), ':</label>
			<select required="required" name="StockCat">';
	if (!isset($_POST['StockCat'])) {
		$_POST['StockCat'] = '';
	}
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

if (isset($_POST['Search'])) {
	/*ie seach for stock items */
	echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', urlencode($Identifier), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/supplier.png" title="', _('Tenders'), '" alt="" />', ' ', _('Select items required on this tender'), '
		</p>';

	if ($_POST['Keywords'] and $_POST['StockCode']) {
		prnMsg(_('Stock description keywords have been used in preference to the Stock code extract entered'), 'info');
	}
	//insert wildcard characters in spaces
	$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

	if ($_POST['StockCat'] == 'All') {
		$CategoryString = '%';
	} else {
		$CategoryString = $_POST['StockCat'];
	}
	$_POST['StockCode'] = '%' . $_POST['StockCode'] . '%';
	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
				FROM stockmaster
				INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
				WHERE stockmaster.mbflag!='D'
					AND stockmaster.mbflag!='A'
					AND stockmaster.mbflag!='K'
					AND stockmaster.mbflag!='G'
					AND stockmaster.discontinued!=1
					AND stockmaster.description " . LIKE . " '" . $SearchString . "'
					AND stockmaster.categoryid " . LIKE . " '" . $CategoryString . "'
					AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'
				ORDER BY stockmaster.stockid
				LIMIT " . $_SESSION['DisplayRecordsMax'];

	$ErrMsg = _('There is a problem selecting the part records to display because');
	$DbgMsg = _('The SQL statement that failed was');
	$SearchResult = DB_query($SQL, $ErrMsg, $DbgMsg);

	if (isset($SearchResult)) {

		echo '<table cellpadding="1">
				<thead>
					<tr>
						<th class="SortedColumn">', _('Code'), '</th>
						<th class="SortedColumn">', _('Description'), '</th>
						<th>', _('Units'), '</th>
						<th>', _('Image'), '</th>
						<th>', _('Quantity'), '</th>
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

			echo '<tr class="striped_row">
					<td>', $MyRow['stockid'], '</td>
					<td>', $MyRow['description'], '</td>
					<td>', $MyRow['units'], '</td>
					<td>', $ImageSource, '</td>
					<td><input class="number" type="text" required="required" maxlength="10" size="6" value="0" name="Qty', $i, '" /></td>
					<input type="hidden" value="', $MyRow['units'], '" name="UOM', $i, '" />
					<input type="hidden" value="', $MyRow['stockid'], '" name="StockID', $i, '" />
				</tr>';

			++$i;
			#end of page full new headings if
			
		}
		#end of while loop
		echo '</tbody>';
		echo '</table>';

		echo '<a name="end"></a>
			<div class="centre">
				<input type="submit" name="NewItem" value="', _('Add to Tender'), '" />
			</div>';
	} #end if SearchResults to show
	echo '</form>';

} //end of if search
include ('includes/footer.php');

?>