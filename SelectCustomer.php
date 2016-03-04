<?php

include('includes/session.inc');

$Title = _('Search Customers');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['Select'])) {
	$_SESSION['CustomerID'] = $_GET['Select'];
} //isset($_GET['Select'])

if (!isset($_SESSION['CustomerID'])) { //initialise if not already done
	$_SESSION['CustomerID'] = '';
} //!isset($_SESSION['CustomerID'])

if (isset($_GET['Area'])) {
	$_POST['Area'] = $_GET['Area'];
	$_POST['Search'] = 'Search';
	$_POST['Keywords'] = '';
	$_POST['CustCode'] = '';
	$_POST['CustPhone'] = '';
	$_POST['CustAdd'] = '';
	$_POST['CustType'] = '';
} //isset($_GET['Area'])

echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/customer.png" title="' . _('Customer') . '" alt="" />' . ' ' . _('Customers') . '</p>';

if (!isset($_SESSION['CustomerType'])) { //initialise if not already done
	$_SESSION['CustomerType'] = '';
} //!isset($_SESSION['CustomerType'])

// only run geocode if integration is turned on and customer has been selected
if ($_SESSION['geocode_integration'] == 1 and $_SESSION['CustomerID'] != "") {
	$SQL = "SELECT * FROM geocode_param WHERE 1";
	$ErrMsg = _('An error occurred in retrieving the information');
	$Result = DB_query($SQL, $ErrMsg);
	$MyRow = DB_fetch_array($Result);
	$SQL = "SELECT debtorsmaster.debtorno,
					debtorsmaster.name,
					custbranch.branchcode,
					custbranch.brname,
					custbranch.lat,
					custbranch.lng
				FROM debtorsmaster LEFT JOIN custbranch
				ON debtorsmaster.debtorno = custbranch.debtorno
				WHERE debtorsmaster.debtorno = '" . $_SESSION['CustomerID'] . "'
				ORDER BY debtorsmaster.debtorno";
	$ErrMsg = _('An error occurred in retrieving the information');
	$Result2 = DB_query($SQL, $ErrMsg);
	$MyRow2 = DB_fetch_array($Result2);
	$Lattitude = $MyRow2['lat'];
	$Longitude = $MyRow2['lng'];
	$API_Key = $MyRow['geocode_key'];
	$center_long = $MyRow['center_long'];
	$center_lat = $MyRow['center_lat'];
	$map_height = $MyRow['map_height'];
	$map_width = $MyRow['map_width'];
	$map_host = $MyRow['map_host'];
	echo '<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=' . $API_Key . '"';
	echo ' type="text/javascript"></script>';
	echo ' <script type="text/javascript">';
	echo 'function load() {
		if (GBrowserIsCompatible()) {
			var map = new GMap2(document.getElementById("map"));
		map.addControl(new GSmallMapControl());
		map.addControl(new GMapTypeControl());';
	echo 'map.setCenter(new GLatLng(' . $Lattitude . ', ' . $Longitude . '), 11);';
	echo 'var marker = new GMarker(new GLatLng(' . $Lattitude . ', ' . $Longitude . '));';
	echo 'map.addOverlay(marker);
		GEvent.addListener(marker, "click", function() {
		marker.openInfoWindowHtml(WINDOW_HTML);
		});
		marker.openInfoWindowHtml(WINDOW_HTML);
		}
		}
		</script>';
	echo '<body onload="load()" onunload="GUnload()">';
} //$_SESSION['geocode_integration'] == 1 and $_SESSION['CustomerID'] != ""

unset($Result);
$Msg = '';

if (isset($_POST['Go1']) or isset($_POST['Go2'])) {
	$_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
	$_POST['Go'] = '';
} //isset($_POST['Go1']) or isset($_POST['Go2'])

if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	} //$_POST['PageOffset'] == 0
}

if (isset($_POST['Search']) or isset($_POST['CSV']) or isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Previous'])) {
	unset($_POST['JustSelectedACustomer']);
	if (isset($_POST['Search'])) {
		$_POST['PageOffset'] = 1;
	} //isset($_POST['Search'])

	if (($_POST['Keywords'] == '') and ($_POST['CustCode'] == '') and ($_POST['CustPhone'] == '') and ($_POST['CustType'] == 'ALL') and ($_POST['Area'] == 'ALL') and ($_POST['CustAdd'] == '')) {
		//no criteria set then default to all customers
		$SQL = "SELECT debtorsmaster.debtorno,
					debtorsmaster.name,
					debtorsmaster.address1,
					debtorsmaster.address2,
					debtorsmaster.address3,
					debtorsmaster.address4,
					custbranch.branchcode,
					custbranch.brname,
					custbranch.contactname,
					debtortype.typename,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.email
				FROM debtorsmaster LEFT JOIN custbranch
				ON debtorsmaster.debtorno = custbranch.debtorno
				INNER JOIN debtortype
				ON debtorsmaster.typeid = debtortype.typeid";
	} else {
		$SearchKeywords = mb_strtoupper(trim(str_replace(' ', '%', $_POST['Keywords'])));
		$_POST['CustCode'] = mb_strtoupper(trim($_POST['CustCode']));
		$_POST['CustPhone'] = trim($_POST['CustPhone']);
		$_POST['CustAdd'] = trim($_POST['CustAdd']);
		$SQL = "SELECT debtorsmaster.debtorno,
						debtorsmaster.name,
						debtorsmaster.address1,
						debtorsmaster.address2,
						debtorsmaster.address3,
						debtorsmaster.address4,
						custbranch.branchcode,
						custbranch.brname,
						custbranch.contactname,
						debtortype.typename,
						custbranch.phoneno,
						custbranch.faxno,
						custbranch.email
					FROM debtorsmaster INNER JOIN debtortype
						ON debtorsmaster.typeid = debtortype.typeid
					LEFT JOIN custbranch
						ON debtorsmaster.debtorno = custbranch.debtorno
					WHERE debtorsmaster.name " . LIKE . " '%" . $SearchKeywords . "%'
					AND debtorsmaster.debtorno " . LIKE . " '%" . $_POST['CustCode'] . "%'
					AND (custbranch.phoneno " . LIKE . " '%" . $_POST['CustPhone'] . "%' OR custbranch.phoneno IS NULL)
					AND (debtorsmaster.address1 " . LIKE . " '%" . $_POST['CustAdd'] . "%'
						OR debtorsmaster.address2 " . LIKE . " '%" . $_POST['CustAdd'] . "%'
						OR debtorsmaster.address3 " . LIKE . " '%" . $_POST['CustAdd'] . "%'
						OR debtorsmaster.address4 " . LIKE . " '%" . $_POST['CustAdd'] . "%')";//If there is no custbranch set, the phoneno in custbranch will be null, so we add IS NULL condition otherwise those debtors without custbranches setting will be no searchable and it will make a inconsistence with customer receipt interface.

		if (mb_strlen($_POST['CustType']) > 0 and $_POST['CustType'] != 'ALL') {
			$SQL .= " AND debtortype.typename = '" . $_POST['CustType'] . "'";
		} //mb_strlen($_POST['CustType']) > 0 and $_POST['CustType'] != 'ALL'

		if (mb_strlen($_POST['Area']) > 0 and $_POST['Area'] != 'ALL') {
			$SQL .= " AND custbranch.area = '" . $_POST['Area'] . "'";
		} //mb_strlen($_POST['Area']) > 0 and $_POST['Area'] != 'ALL'

	} //one of keywords or custcode or custphone was more than a zero length string

	if ($_SESSION['SalesmanLogin'] != '') {
		$SQL .= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
	} //$_SESSION['SalesmanLogin'] != ''

	$SQL .= " ORDER BY debtorsmaster.name";
	$ErrMsg = _('The searched customer records requested cannot be retrieved because');

	$Result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($Result) == 1) {
		$MyRow = DB_fetch_array($Result);
		$_SESSION['CustomerID'] = $MyRow['debtorno'];
		$_SESSION['BranchCode'] = $MyRow['branchcode'];
		unset($Result);
		unset($_POST['Search']);
	} elseif (DB_num_rows($Result) == 0) {
		prnMsg(_('No customer records contain the selected text') . ' - ' . _('please alter your search criteria and try again'), 'info');
		echo '<br />';
	} //DB_num_rows($Result) == 0
} //end of if search

if (isset($_POST['JustSelectedACustomer'])) {
	/*Need to figure out the number of the form variable that the user clicked on */
	for ($i = 0; $i < count($_POST); $i++) { //loop through the returned customers
		if (isset($_POST['SubmitCustomerSelection' . $i])) {
			break;
		} //isset($_POST['SubmitCustomerSelection' . $i])
	} //$i = 0; $i < count($_POST); $i++
	if ($i == count($_POST)) {
		prnMsg(_('Unable to identify the selected customer'), 'error');
	} //$i == count($_POST)
	else {
		$_SESSION['CustomerID'] = $_POST['SelectedCustomer' . $i];
		$_SESSION['BranchCode'] = $_POST['SelectedBranch' . $i];
	}
} //isset($_POST['JustSelectedACustomer'])

if ($_SESSION['CustomerID'] != '' and !isset($_POST['Search']) and !isset($_POST['CSV'])) {
	if (!isset($_SESSION['BranchCode'])) {
		$SQL = "SELECT debtorsmaster.name,
					custbranch.phoneno
			FROM debtorsmaster INNER JOIN custbranch
			ON debtorsmaster.debtorno=custbranch.debtorno
			WHERE custbranch.debtorno='" . $_SESSION['CustomerID'] . "'";

	} //!isset($_SESSION['BranchCode'])
	else {
		$SQL = "SELECT debtorsmaster.name,
					custbranch.phoneno
			FROM debtorsmaster INNER JOIN custbranch
			ON debtorsmaster.debtorno=custbranch.debtorno
			WHERE custbranch.debtorno='" . $_SESSION['CustomerID'] . "'
			AND custbranch.branchcode='" . $_SESSION['BranchCode'] . "'";
	}
	$ErrMsg = _('The customer name requested cannot be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);
	if ($MyRow = DB_fetch_array($Result)) {
		$CustomerName = htmlspecialchars($MyRow['name'], ENT_QUOTES, 'UTF-8', false);
		$PhoneNo = $MyRow['phoneno'];
	} //$MyRow = DB_fetch_array($Result)
	unset($Result);

	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/customer.png" title="' . _('Customer') . '" alt="" />' . ' ' . _('Customer') . ' : ' . stripslashes($_SESSION['CustomerID']) . ' - ' . $CustomerName . ' - ' . $PhoneNo . _(' has been selected') . '</p>';
	echo '<div class="page_help_text">' . _('Select a menu option to operate using this customer') . '.</div><br />';

	echo '<table cellpadding="4" width="90%" class="selection">
			<tr>
				<th style="width:33%">' . _('Customer Inquiries') . '</th>
				<th style="width:33%">' . _('Customer Transactions') . '</th>
				<th style="width:33%">' . _('Customer Maintenance') . '</th>
			</tr>';
	echo '<tr><td valign="top" class="select">';
	/* Customer Inquiry Options */
	echo '<a href="' . $RootPath . '/CustomerInquiry.php?CustomerID=' . urlencode($_SESSION['CustomerID']) . '">' . _('Customer Transaction Inquiries') . '</a>';
	echo '<a href="' . $RootPath . '/CustomerAccount.php?CustomerID=' . urlencode($_SESSION['CustomerID']) . '">' . _('Customer Account statement on screen') . '</a>';
	echo '<a href="' . $RootPath . '/Customers.php?DebtorNo=' . urlencode($_SESSION['CustomerID']) . '&amp;Modify=No">' . _('View Customer Details') . '</a>';
	echo '<a href="' . $RootPath . '/PrintCustStatements.php?FromCust=' . urlencode($_SESSION['CustomerID']) . '&amp;ToCust=' . urlencode($_SESSION['CustomerID']) . '&amp;PrintPDF=Yes">' . _('Print Customer Statement') . '</a>';
	echo '<a href="' . $RootPath . '/EmailCustStatements.php?FromCust=' . urlencode($_SESSION['CustomerID']) . '&amp;ToCust=' . urlencode($_SESSION['CustomerID']) . '&amp;PrintPDF=Yes">' . _('Email Customer Statement') . '</a>';
	echo '<a href="' . $RootPath . '/SelectCompletedOrder.php?SelectedCustomer=' . urlencode($_SESSION['CustomerID']) . '">' . _('Order Inquiries') . '</a>';
	echo '<a href="' . $RootPath . '/CustomerPurchases.php?DebtorNo=' . urlencode($_SESSION['CustomerID']) . '">' . _('Show purchases from this customer') . '</a>';
	wikiLink('Customer', $_SESSION['CustomerID']);
	echo '</td><td valign="top" class="select">';
	echo '<a href="' . $RootPath . '/SelectSalesOrder.php?SelectedCustomer=' . urlencode($_SESSION['CustomerID']) . '">' . _('Modify Outstanding Sales Orders') . '</a>';
	echo '<a href="' . $RootPath . '/CustomerAllocations.php?DebtorNo=' . urlencode($_SESSION['CustomerID']) . '">' . _('Allocate Receipts or Credit Notes') . '</a>';
	echo '<a href="' . $RootPath . '/JobCards.php?DebtorNo=' . urlencode($_SESSION['CustomerID']) . '&amp;BranchNo=' . $_SESSION['BranchCode'] . '">' . _('Job Cards') . '</a>';
	echo '<a href="' . $RootPath . '/CustomerReceipt.php?CustomerID=' . urlencode($_SESSION['CustomerID']) . '&NewReceipt=Yes&Type=Customer">' . _('Enter a Receipt From This Customer') . '</a>';
	if (isset($_SESSION['CustomerID']) and isset($_SESSION['BranchCode'])) {
		echo '<a href="' . $RootPath . '/CounterSales.php?DebtorNo=' . urlencode($_SESSION['CustomerID']) . '&amp;BranchNo=' . $_SESSION['BranchCode'] . '">' . _('Create a Counter Sale for this Customer') . '</a>';
	} //isset($_SESSION['CustomerID']) and isset($_SESSION['BranchCode'])
	echo '</td><td valign="top" class="select">';
	echo '<a href="' . $RootPath . '/Customers.php">' . _('Add a New Customer') . '</a>';
	echo '<a href="' . $RootPath . '/Customers.php?DebtorNo=' . urlencode($_SESSION['CustomerID']) . '">' . _('Modify Customer Details') . '</a>';
	echo '<a href="' . $RootPath . '/CustomerBranches.php?DebtorNo=' . urlencode(stripslashes($_SESSION['CustomerID'])) . '">' . _('Add/Modify/Delete Customer Branches') . '</a>';
	echo '<a href="' . $RootPath . '/SelectProduct.php">' . _('Special Customer Prices') . '</a>';
	echo '<a href="' . $RootPath . '/CustEDISetup.php">' . _('Customer EDI Configuration') . '</a>';
	echo '<a href="' . $RootPath . '/CustLoginSetup.php">' . _('Customer Login Configuration') . '</a>';
	echo '<a href="' . $RootPath . '/AddCustomerContacts.php?DebtorNo=' . urlencode($_SESSION['CustomerID']) . '">' . _('Add a customer contact') . '</a>';
	echo '<a href="' . $RootPath . '/AddCustomerNotes.php?DebtorNo=' . urlencode($_SESSION['CustomerID']) . '">' . _('Add a note on this customer') . '</a>';
	echo '</td>';
	echo '</tr></table>';
} //$_SESSION['CustomerID'] != '' and !isset($_POST['Search']) and !isset($_POST['CSV'])
else {
	echo '<table width="90%">
			<tr>
				<th style="width:33%">' . _('Customer Inquiries') . '</th>
				<th style="width:33%">' . _('Customer Transactions') . '</th>
				<th style="width:33%">' . _('Customer Maintenance') . '</th>
			</tr>';
	echo '<tr>
			<td class="select"></td>
			<td class="select"></td>
			<td class="select">';
	if (!isset($_SESSION['SalesmanLogin']) or $_SESSION['SalesmanLogin'] == '') {
		echo '<a href="' . $RootPath . '/Customers.php">' . _('Add a New Customer') . '</a><br />';
	} //!isset($_SESSION['SalesmanLogin']) or $_SESSION['SalesmanLogin'] == ''
	echo '</td></tr></table>';
}
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (mb_strlen($Msg) > 1) {
	prnMsg($Msg, 'info');
} //mb_strlen($Msg) > 1
echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Search for Customers') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<tr><td colspan="2">' . _('Enter a partial Name') . ':</td><td>';
if (isset($_POST['Keywords'])) {
	echo '<input type="text" name="Keywords" value="' . $_POST['Keywords'] . '" size="20" maxlength="25" />';
} //isset($_POST['Keywords'])
else {
	echo '<input type="text" name="Keywords" size="20" maxlength="25" />';
}
echo '</td>
	<td><b>' . _('OR') . '</b></td><td>' . _('Enter a partial Code') . ':</td>
	<td>';
if (isset($_POST['CustCode'])) {
	echo '<input type="text" autofocus="autofocus" name="CustCode" value="' . $_POST['CustCode'] . '" size="15" maxlength="18" />';
} //isset($_POST['CustCode'])
else {
	echo '<input type="text" autofocus="autofocus" name="CustCode" size="15" maxlength="18" />';
}
echo '</td>
	</tr>
	<tr>
		<td><b>' . _('OR') . '</b></td>
		<td>' . _('Enter a partial Phone Number') . ':</td>
		<td>';
if (isset($_POST['CustPhone'])) {
	echo '<input type="text" name="CustPhone" value="' . $_POST['CustPhone'] . '" size="15" maxlength="18" />';
} //isset($_POST['CustPhone'])
else {
	echo '<input type="text" name="CustPhone" size="15" maxlength="18" />';
}
echo '</td>';
echo '<td><b>' . _('OR') . '</b></td>
		<td>' . _('Enter part of the Address') . ':</td>
		<td>';
if (isset($_POST['CustAdd'])) {
	echo '<input type="text" name="CustAdd" value="' . $_POST['CustAdd'] . '" size="20" maxlength="25" />';
} //isset($_POST['CustAdd'])
else {
	echo '<input type="text" name="CustAdd" size="20" maxlength="25" />';
}
echo '</td></tr>';
echo '<tr>
		<td><b>' . _('OR') . '</b></td>
		<td>' . _('Choose a Type') . ':</td>
		<td>';
if (isset($_POST['CustType'])) {
	// Show Customer Type drop down list
	$Result2 = DB_query("SELECT typeid, typename FROM debtortype ORDER BY typename");
	// Error if no customer types setup
	if (DB_num_rows($Result2) == 0) {
		$DataError = 1;
		echo '<a href="CustomerTypes.php" target="_parent">' . _('Setup Types') . '</a>';
		echo '<tr><td colspan="2">' . prnMsg(_('No Customer types defined'), 'error') . '</td></tr>';
	} //DB_num_rows($Result2) == 0
	else {
		// If OK show select box with option selected
		echo '<select name="CustType">
				<option value="ALL">' . _('Any') . '</option>';
		while ($MyRow = DB_fetch_array($Result2)) {
			if ($_POST['CustType'] == $MyRow['typename']) {
				echo '<option selected="selected" value="' . $MyRow['typename'] . '">' . $MyRow['typename'] . '</option>';
			} //$_POST['CustType'] == $MyRow['typename']
			else {
				echo '<option value="' . $MyRow['typename'] . '">' . $MyRow['typename'] . '</option>';
			}
		} //end while loop
		DB_data_seek($Result2, 0);
		echo '</select></td>';
	}
} //isset($_POST['CustType'])
else {
	// No option selected="selected" yet, so show Customer Type drop down list
	$Result2 = DB_query("SELECT typeid, typename FROM debtortype");
	// Error if no customer types setup
	if (DB_num_rows($Result2) == 0) {
		$DataError = 1;
		echo '<a href="CustomerTypes.php" target="_parent">' . _('Setup Types') . '</a>';
		echo '<tr><td colspan="2">' . prnMsg(_('No Customer types defined'), 'error') . '</td></tr>';
	} //DB_num_rows($Result2) == 0
	else {
		// if OK show select box with available options to choose
		echo '<select name="CustType">
				<option value="ALL">' . _('Any') . '</option>';
		while ($MyRow = DB_fetch_array($Result2)) {
			echo '<option value="' . $MyRow['typename'] . '">' . $MyRow['typename'] . '</option>';
		} //end while loop
		DB_data_seek($Result2, 0);
		echo '</select></td>';
	}
}

/* Option to select a sales area */
echo '<td><b>' . _('OR') . '</b></td>
		<td>' . _('Choose an Area') . ':</td><td>';
$Result2 = DB_query("SELECT areacode, areadescription FROM areas");
// Error if no sales areas setup
if (DB_num_rows($Result2) == 0) {
	$DataError = 1;
	echo '<a href="Areas.php" target="_parent">' . _('Setup Areas') . '</a>';
	echo '<tr><td colspan="2">' . prnMsg(_('No Sales Areas defined'), 'error') . '</td></tr>';
} //DB_num_rows($Result2) == 0
else {
	// if OK show select box with available options to choose
	echo '<select name="Area">';
	echo '<option value="ALL">' . _('Any') . '</option>';
	while ($MyRow = DB_fetch_array($Result2)) {
		if (isset($_POST['Area']) and $_POST['Area'] == $MyRow['areacode']) {
			echo '<option selected="selected" value="' . $MyRow['areacode'] . '">' . $MyRow['areadescription'] . '</option>';
		} //isset($_POST['Area']) and $_POST['Area'] == $MyRow['areacode']
		else {
			echo '<option value="' . $MyRow['areacode'] . '">' . $MyRow['areadescription'] . '</option>';
		}
	} //end while loop
	DB_data_seek($Result2, 0);
	echo '</select></td></tr>';
}

echo '</table>';
echo '<div class="centre">
		<input type="submit" name="Search" value="' . _('Search Now') . '" />
		<input type="submit" name="CSV" value="' . _('CSV Format') . '" />
	</div>';
if (isset($_SESSION['SalesmanLogin']) and $_SESSION['SalesmanLogin'] != '') {
	prnMsg(_('Your account enables you to see only customers allocated to you'), 'warn', _('Note: Sales-person Login'));
} //isset($_SESSION['SalesmanLogin']) and $_SESSION['SalesmanLogin'] != ''
if (isset($Result)) {
	unset($_SESSION['CustomerID']);
	$ListCount = DB_num_rows($Result);
	$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
	if (!isset($_POST['CSV'])) {
		if (isset($_POST['Next'])) {
			if ($_POST['PageOffset'] < $ListPageMax) {
				$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
			} //$_POST['PageOffset'] < $ListPageMax
		} //isset($_POST['Next'])
		if (isset($_POST['Previous'])) {
			if ($_POST['PageOffset'] > 1) {
				$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
			} //$_POST['PageOffset'] > 1
		} //isset($_POST['Previous'])
		echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
		if ($ListPageMax > 1) {
			echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
			echo '<select name="PageOffset1">';
			$ListPage = 1;
			while ($ListPage <= $ListPageMax) {
				if ($ListPage == $_POST['PageOffset']) {
					echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
				} //$ListPage == $_POST['PageOffset']
				else {
					echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
				}
				$ListPage++;
			} //$ListPage <= $ListPageMax
			echo '</select>
				<input type="submit" name="Go1" value="' . _('Go') . '" />
				<input type="submit" name="Previous" value="' . _('Previous') . '" />
				<input type="submit" name="Next" value="' . _('Next') . '" />';
			echo '</div>';
		} //$ListPageMax > 1
		$k = 0; //row counter to determine background colour
		$RowIndex = 0;
	} //!isset($_POST['CSV'])
	if (DB_num_rows($Result) <> 0) {
		echo '<table cellpadding="2" class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Customer Name') . '</th>
						<th class="SortedColumn">' . _('Branch') . '</th>
						<th>' . _('Contact') . '</th>
						<th>' . _('Type') . '</th>
						<th>' . _('Phone') . '</th>
						<th>' . _('Fax') . '</th>
						<th>' . _('Email') . '</th>
					</tr>
				</thead>';
		if (isset($_POST['CSV'])) {
			$FileName = $_SESSION['reports_dir'] . '/Customer_Listing_' . Date('Y-m-d') . '.csv';
			echo '<br /><p class="page_title_text" ><a href="' . $FileName . '">' . _('Click to view the csv Search Result') . '</p>';
			$fp = fopen($FileName, 'w');
			while ($MyRow2 = DB_fetch_array($Result)) {
				fwrite($fp, $MyRow2['debtorno'] . ',' . str_replace(',', '', $MyRow2['name']) . ',' . str_replace(',', '', $MyRow2['address1']) . ',' . str_replace(',', '', $MyRow2['address2']) . ',' . str_replace(',', '', $MyRow2['address3']) . ',' . str_replace(',', '', $MyRow2['address4']) . ',' . str_replace(',', '', $MyRow2['contactname']) . ',' . str_replace(',', '', $MyRow2['typename']) . ',' . $MyRow2['phoneno'] . ',' . $MyRow2['faxno'] . ',' . $MyRow2['email'] . "\n");
			} //$MyRow2 = DB_fetch_array($Result)
		} //isset($_POST['CSV'])
		if (!isset($_POST['CSV'])) {
			DB_data_seek($Result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		} //!isset($_POST['CSV'])
		$i = 0; //counter for input controls
		echo '<tbody>';
		while (($MyRow = DB_fetch_array($Result)) and ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} //$k == 1
			else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			echo '<td><input type="submit" name="SubmitCustomerSelection' . $i . '" value="' . $MyRow['debtorno'] . ' ' . $MyRow['branchcode'] . '" />
				<input type="hidden" name="SelectedCustomer' . $i . '" value="' . $MyRow['debtorno'] . '" />
				<input type="hidden" name="SelectedBranch' . $i . '" value="' . $MyRow['branchcode'] . '" /></td>
				<td>' . htmlspecialchars($MyRow['name'], ENT_QUOTES, 'UTF-8', false) . '</td>
				<td>' . htmlspecialchars($MyRow['brname'], ENT_QUOTES, 'UTF-8', false) . '</td>
				<td>' . $MyRow['contactname'] . '</td>
				<td>' . $MyRow['typename'] . '</td>
				<td>' . $MyRow['phoneno'] . '</td>
				<td>' . $MyRow['faxno'] . '</td>
				<td>' . $MyRow['email'] . '</td>
			</tr>';
			++$i;
			$RowIndex++;
			//end of page full new headings if
		} //($MyRow = DB_fetch_array($Result)) and ($RowIndex <> $_SESSION['DisplayRecordsMax'])
		//end of while loop
		echo '</tbody>';
		echo '</table>';
		echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
		echo '</table>';
		echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
	} //DB_num_rows($Result) <> 0
} //isset($Result)
//end if results to show
if (!isset($_POST['CSV'])) {
	if (isset($ListPageMax) and $ListPageMax > 1) {
		echo '<div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
		echo '<select name="PageOffset2">';
		$ListPage = 1;
		while ($ListPage <= $ListPageMax) {
			if ($ListPage == $_POST['PageOffset']) {
				echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
			} //$ListPage == $_POST['PageOffset']
			else {
				echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
			}
			$ListPage++;
		} //$ListPage <= $ListPageMax
		echo '</select>
			<input type="submit" name="Go2" value="' . _('Go') . '" />
			<input type="submit" name="Previous" value="' . _('Previous') . '" />
			<input type="submit" name="Next" value="' . _('Next') . '" />';
		echo '</div>';
	} //isset($ListPageMax) and $ListPageMax > 1
	//end if results to show
} //!isset($_POST['CSV'])
echo '</form>';
// Only display the geocode map if the integration is turned on, and there is a latitude/longitude to display
if (isset($_SESSION['CustomerID']) and $_SESSION['CustomerID'] != '') {
	if ($_SESSION['geocode_integration'] == 1) {
		echo '<br />';
		if ($Lattitude == 0) {
			echo '<div class="centre">' . _('Mapping is enabled, but no Mapping data to display for this Customer.') . '</div>';
		} //$Lattitude == 0
		else {
			echo '<tr>
					<td colspan="2">
					<table width="45%" cellpadding="4">
						<tr>
							<th style="width:33%">' . _('Customer Mapping') . '</th>
						</tr>
					</td>
					<th valign="top">
						<div class="centre">' . _('Mapping is enabled, Map will display below.') . '
						</div>
						<div align="center" id="map" style="width: ' . $map_width . 'px; height: ' . $map_height . 'px">
						</div>
						<br />
					</th>
					</tr>
					</table>';
		}
	} //$_SESSION['geocode_integration'] == 1
	// Extended Customer Info only if selected in Configuration
	if ($_SESSION['Extended_CustomerInfo'] == 1) {
		if ($_SESSION['CustomerID'] != '') {
			$SQL = "SELECT debtortype.typeid,
							debtortype.typename
						FROM debtorsmaster INNER JOIN debtortype
					ON debtorsmaster.typeid = debtortype.typeid
					WHERE debtorsmaster.debtorno = '" . $_SESSION['CustomerID'] . "'";
			$ErrMsg = _('An error occurred in retrieving the information');
			$Result = DB_query($SQL, $ErrMsg);
			$MyRow = DB_fetch_array($Result);
			$CustomerType = $MyRow['typeid'];
			$CustomerTypeName = $MyRow['typename'];
			// Customer Data
			echo '<br />';
			// Select some basic data about the Customer
			$SQL = "SELECT debtorsmaster.clientsince,
						(TO_DAYS(date(now())) - TO_DAYS(date(debtorsmaster.clientsince))) as customersincedays,
						(TO_DAYS(date(now())) - TO_DAYS(date(debtorsmaster.lastpaiddate))) as lastpaiddays,
						debtorsmaster.paymentterms,
						debtorsmaster.lastpaid,
						debtorsmaster.lastpaiddate,
						currencies.decimalplaces AS currdecimalplaces
					FROM debtorsmaster INNER JOIN currencies
					ON debtorsmaster.currcode=currencies.currabrev
					WHERE debtorsmaster.debtorno ='" . $_SESSION['CustomerID'] . "'";
			$DataResult = DB_query($SQL);
			$MyRow = DB_fetch_array($DataResult);
			// Select some more data about the customer
			$SQL = "SELECT sum(ovamount+ovgst) as total
					FROM debtortrans
					WHERE debtorno = '" . $_SESSION['CustomerID'] . "'
					AND type !=12";
			$Total1Result = DB_query($SQL);
			$row = DB_fetch_array($Total1Result);
			echo '<table width="45%" cellpadding="4">';
			echo '<tr>
					<th style="width:33%" colspan="3">' . _('Customer Data') . '</th>
				</tr>';
			echo '<tr><td valign="top" class="select">';
			/* Customer Data */
			if ($MyRow['lastpaiddate'] == 0) {
				echo _('No receipts from this customer.') . '</td>
					<td class="select"></td>
					<td class="select"></td>
					</tr>';
			} //$MyRow['lastpaiddate'] == 0
			else {
				echo _('Last Paid Date') . ':</td>
					<td class="select"> <b>' . ConvertSQLDate($MyRow['lastpaiddate']) . '</b> </td>
					<td class="select">' . $MyRow['lastpaiddays'] . ' ' . _('days') . '</td>
					</tr>';
			}
			echo '<tr>
					<td class="select">' . _('Last Paid Amount (inc tax)') . ':</td>
					<td class="select"> <b>' . locale_number_format($MyRow['lastpaid'], $MyRow['currdecimalplaces']) . '</b></td>
					<td class="select"></td>
				</tr>';
			echo '<tr>
					<td class="select">' . _('Customer since') . ':</td>
					<td class="select"> <b>' . ConvertSQLDate($MyRow['clientsince']) . '</b> </td>
					<td class="select">' . $MyRow['customersincedays'] . ' ' . _('days') . '</td>
				</tr>';
			if ($row['total'] == 0) {
				echo '<tr>
						<td class="select">' . _('No Spend from this Customer.') . '</b></td>
						<td class="select"></td>
						<td class="select"></td>
					</tr>';
			} //$row['total'] == 0
			else {
				echo '<tr>
						<td class="select">' . _('Total Spend from this Customer (inc tax)') . ':</td>
						<td class="select"><b>' . locale_number_format($row['total'], $MyRow['currdecimalplaces']) . '</b></td>
						<td class="select"></td>
					</tr>';
			}
			echo '<tr>
					<td class="select">' . _('Customer Type') . ':</td>
					<td class="select"><b>' . $CustomerTypeName . '</b></td>
					<td class="select"></td>
				</tr>';
			echo '</table>';
		} //$_SESSION['CustomerID'] != ''
		// Customer Contacts
		$SQL = "SELECT * FROM custcontacts
				WHERE debtorno='" . $_SESSION['CustomerID'] . "'
				ORDER BY contid";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) <> 0) {
			echo '<div class="centre"><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/group_add.png" title="' . _('Customer Contacts') . '" alt="" />' . ' ' . _('Customer Contacts') . '</div>';
			echo '<table width="45%">
					<thead>
						<tr>
							<th class="SortedColumn">' . _('Name') . '</th>
							<th class="SortedColumn">' . _('Role') . '</th>
							<th class="SortedColumn">' . _('Phone Number') . '</th>
							<th>' . _('Email') . '</th>
							<th>' . _('Notes') . '</th>
							<th>' . _('Edit') . '</th>
							<th>' . _('Delete') . '</th>
							<th> <a href="AddCustomerContacts.php?DebtorNo=' . urlencode($_SESSION['CustomerID']) . '">' . _('Add New Contact') . '</a> </th>
						</tr>
					</thead>';
			$k = 0; //row colour counter
			echo '<tbody>';
			while ($MyRow = DB_fetch_array($Result)) {
				if ($k == 1) {
					echo '<tr class="OddTableRows">';
					$k = 0;
				} //$k == 1
				else {
					echo '<tr class="EvenTableRows">';
					$k = 1;
				}
				echo '<td>' . $MyRow[2] . '</td>
					<td>' . $MyRow[3] . '</td>
					<td>' . $MyRow[4] . '</td>
					<td><a href="mailto:' . $MyRow[6] . '">' . $MyRow[6] . '</a></td>
					<td>' . $MyRow[5] . '</td>
					<td><a href="AddCustomerContacts.php?Id=' . urlencode($MyRow[0]) . '&amp;DebtorNo=' . urlencode($MyRow[1]) . '">' . _('Edit') . '</a></td>
					<td><a href="AddCustomerContacts.php?Id=' . urlencode($MyRow[0]) . '&amp;DebtorNo=' . urlencode($MyRow[1]) . '&amp;delete=1">' . _('Delete') . '</a></td>
					</tr>';
			} //END WHILE LIST LOOP
			echo '</tbody>';
			echo '</table>';
		} //DB_num_rows($Result) <> 0
		else {
			if ($_SESSION['CustomerID'] != "") {
				echo '<div class="centre">
						<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/group_add.png" title="' . _('Customer Contacts') . '" alt="" />
						<a href="AddCustomerContacts.php?DebtorNo=' . urlencode($_SESSION['CustomerID']) . '">' . ' ' . _('Add New Contact') . '</a>
					</div>';
			} //$_SESSION['CustomerID'] != ""
		}
		// Customer Notes
		$SQL = "SELECT noteid,
						debtorno,
						href,
						note,
						date,
						priority
				FROM custnotes
				WHERE debtorno='" . $_SESSION['CustomerID'] . "'
				ORDER BY date DESC";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) <> 0) {
			echo '<div class="centre"><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/note_add.png" title="' . _('Customer Notes') . '" alt="" />' . ' ' . _('Customer Notes') . '</div><br />';
			echo '<table width="45%">
					<thead>
						<tr>
							<th class="SortedColumn">' . _('date') . '</th>
							<th>' . _('note') . '</th>
							<th>' . _('hyperlink') . '</th>
							<th class="SortedColumn">' . _('priority') . '</th>
							<th>' . _('Edit') . '</th>
							<th>' . _('Delete') . '</th>
							<th> <a href="AddCustomerNotes.php?DebtorNo=' . urlencode($_SESSION['CustomerID']) . '">' . ' ' . _('Add New Note') . '</a> </th>
						</tr>
					</thead>';
			$k = 0; //row colour counter
			echo '<tbody>';
			while ($MyRow = DB_fetch_array($Result)) {
				if ($k == 1) {
					echo '<tr class="OddTableRows">';
					$k = 0;
				} //$k == 1
				else {
					echo '<tr class="EvenTableRows">';
					$k = 1;
				}
				echo '<td>' . ConvertSQLDate($MyRow['date']) . '</td>
					<td>' . $MyRow['note'] . '</td>
					<td><a href="' . $MyRow['href'] . '">' . $MyRow['href'] . '</a></td>
					<td>' . $MyRow['priority'] . '</td>
					<td><a href="AddCustomerNotes.php?Id=' . urlencode($MyRow['noteid']) . '&amp;DebtorNo=' . urlencode($MyRow['debtorno']) . '">' . _('Edit') . '</a></td>
					<td><a href="AddCustomerNotes.php?Id=' . urlencode($MyRow['noteid']) . '&amp;DebtorNo=' . urlencode($MyRow['debtorno']) . '&amp;delete=1">' . _('Delete') . '</a></td>
					</tr>';
			} //END WHILE LIST LOOP
			echo '</tbody>';
			echo '</table>';
		} //DB_num_rows($Result) <> 0
		else {
			if ($_SESSION['CustomerID'] != '') {
				echo '<div class="centre">
						<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/note_add.png" title="' . _('Customer Notes') . '" alt="" />
						<a href="AddCustomerNotes.php?DebtorNo=' . urlencode($_SESSION['CustomerID']) . '">' . ' ' . _('Add New Note for this Customer') . '</a>
					</div>';
			} //$_SESSION['CustomerID'] != ''
		}
		// Custome Type Notes
		$SQL = "SELECT * FROM debtortypenotes
				WHERE typeid='" . $CustomerType . "'
				ORDER BY date DESC";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) <> 0) {
			echo '<div class="centre">
					<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/folder_add.png" title="' . _('Customer Type (Group) Notes') . '" alt="" />' . ' ' . _('Customer Type (Group) Notes for') . ':<b> ' . $CustomerTypeName . '</b>' . '
				</div>';
			echo '<table width="45%">
					<thead>
						<tr>
							<th class="SortedColumn">' . _('date') . '</th>
							<th>' . _('note') . '</th>
							<th>' . _('file link / reference / URL') . '</th>
							<th class="SortedColumn">' . _('priority') . '</th>
							<th>' . _('Edit') . '</th>
							<th>' . _('Delete') . '</th>
							<th><a href="AddCustomerTypeNotes.php?DebtorType=' . urlencode($CustomerType) . '">' . _('Add New Group Note') . '</a></th>
						</tr>
					</thead>';
			$k = 0; //row colour counter
			echo '<tbody>';
			while ($MyRow = DB_fetch_array($Result)) {
				if ($k == 1) {
					echo '<tr class="OddTableRows">';
					$k = 0;
				} //$k == 1
				else {
					echo '<tr class="EvenTableRows">';
					$k = 1;
				}
				echo '<td>' . $MyRow[4] . '</td>
					<td>' . $MyRow[3] . '</td>
					<td>' . $MyRow[2] . '</td>
					<td>' . $MyRow[5] . '</td>
					<td><a href="AddCustomerTypeNotes.php?Id=' . urlencode($MyRow[0]) . '&amp;DebtorType=' . urlencode($MyRow[1]) . '">' . _('Edit') . '</a></td>
					<td><a href="AddCustomerTypeNotes.php?Id=' . urlencode($MyRow[0]) . '&amp;DebtorType=' . urlencode($MyRow[1]) . '&amp;delete=1">' . _('Delete') . '</a></td>
				</tr>';
			} //END WHILE LIST LOOP
			echo '</tbody>';
			echo '</table>';
		} //DB_num_rows($Result) <> 0
		else {
			if ($_SESSION['CustomerID'] != '') {
				echo '<div class="centre"><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/folder_add.png" title="' . _('Customer Group Notes') . '" alt="" />
						<a href="AddCustomerTypeNotes.php?DebtorType=' . urlencode($CustomerType) . '">' . ' ' . _('Add New Group Note') . '</a>
					</div>';
			} //$_SESSION['CustomerID'] != ''
		}
	} //$_SESSION['Extended_CustomerInfo'] == 1
} //isset($_SESSION['CustomerID']) and $_SESSION['CustomerID'] != ''

include('includes/footer.inc');
?>