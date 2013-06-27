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
echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('Customer') . '" alt="" />' . ' ' . _('Customers') . '</p>';
if (!isset($_SESSION['CustomerType'])) { //initialise if not already done
	$_SESSION['CustomerType'] = '';
} //!isset($_SESSION['CustomerType'])
// only run geocode if integration is turned on and customer has been selected
if ($_SESSION['geocode_integration'] == 1 and $_SESSION['CustomerID'] != "") {
	$sql = "SELECT * FROM geocode_param WHERE 1";
	$ErrMsg = _('An error occurred in retrieving the information');
	$result = DB_query($sql, $db, $ErrMsg);
	$myrow = DB_fetch_array($result);
	$sql = "SELECT debtorsmaster.debtorno,
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
	$result2 = DB_query($sql, $db, $ErrMsg);
	$myrow2 = DB_fetch_array($result2);
	$Lattitude = $myrow2['lat'];
	$Longitude = $myrow2['lng'];
	$API_Key = $myrow['geocode_key'];
	$center_long = $myrow['center_long'];
	$center_lat = $myrow['center_lat'];
	$map_height = $myrow['map_height'];
	$map_width = $myrow['map_width'];
	$map_host = $myrow['map_host'];
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
unset($result);
$msg = '';
if (isset($_POST['Go1']) or isset($_POST['Go2'])) {
	$_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
	$_POST['Go'] = '';
} //isset($_POST['Go1']) or isset($_POST['Go2'])
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} //!isset($_POST['PageOffset'])
else {
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
	} //($_POST['Keywords'] == '') and ($_POST['CustCode'] == '') and ($_POST['CustPhone'] == '') and ($_POST['CustType'] == 'ALL') and ($_POST['Area'] == 'ALL') and ($_POST['CustAdd'] == '')
	else {
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
					AND custbranch.phoneno " . LIKE . " '%" . $_POST['CustPhone'] . "%'
					AND (debtorsmaster.address1 " . LIKE . " '%" . $_POST['CustAdd'] . "%'
						OR debtorsmaster.address2 " . LIKE . " '%" . $_POST['CustAdd'] . "%'
						OR debtorsmaster.address3 " . LIKE . " '%" . $_POST['CustAdd'] . "%'
						OR debtorsmaster.address4 " . LIKE . " '%" . $_POST['CustAdd'] . "%')";

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

	$result = DB_query($SQL, $db, $ErrMsg);
	if (DB_num_rows($result) == 1) {
		$myrow = DB_fetch_array($result);
		$_SESSION['CustomerID'] = $myrow['debtorno'];
		$_SESSION['BranchCode'] = $myrow['branchcode'];
		unset($result);
		unset($_POST['Search']);
	} //DB_num_rows($result) == 1
	elseif (DB_num_rows($result) == 0) {
		prnMsg(_('No customer records contain the selected text') . ' - ' . _('please alter your search criteria and try again'), 'info');
		echo '<br />';
	} //DB_num_rows($result) == 0
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
	$result = DB_query($SQL, $db, $ErrMsg);
	if ($myrow = DB_fetch_array($result)) {
		$CustomerName = htmlspecialchars($myrow['name'], ENT_QUOTES, 'UTF-8', false);
		$PhoneNo = $myrow['phoneno'];
	} //$myrow = DB_fetch_array($result)
	unset($result);

	echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . _('Customer') . '" alt="" />' . ' ' . _('Customer') . ' : ' . $_SESSION['CustomerID'] . ' - ' . $CustomerName . ' - ' . $PhoneNo . _(' has been selected') . '</p>';
	echo '<div class="page_help_text noPrint">' . _('Select a menu option to operate using this customer') . '.</div><br />';

	echo '<table cellpadding="4" width="90%" class="selection">
			<tr>
				<th style="width:33%">' . _('Customer Inquiries') . '</th>
				<th style="width:33%">' . _('Customer Transactions') . '</th>
				<th style="width:33%">' . _('Customer Maintenance') . '</th>
			</tr>';
	echo '<tr><td valign="top" class="select">';
	/* Customer Inquiry Options */
	echo '<a href="' . $RootPath . '/CustomerInquiry.php?CustomerID=' . $_SESSION['CustomerID'] . '">' . _('Customer Transaction Inquiries') . '</a><br />';
	echo '<a href="' . $RootPath . '/Customers.php?DebtorNo=' . $_SESSION['CustomerID'] . '&amp;Modify=No">' . _('View Customer Details') . '</a><br />';
	echo '<a href="' . $RootPath . '/PrintCustStatements.php?FromCust=' . $_SESSION['CustomerID'] . '&amp;ToCust=' . $_SESSION['CustomerID'] . '&amp;PrintPDF=Yes">' . _('Print Customer Statement') . '</a><br />';
	echo '<a href="' . $RootPath . '/SelectCompletedOrder.php?SelectedCustomer=' . $_SESSION['CustomerID'] . '">' . _('Order Inquiries') . '</a><br />';
	echo '<a href="' . $RootPath . '/CustomerPurchases.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . _('Show purchases from this customer') . '</a><br />';
	wikiLink('Customer', $_SESSION['CustomerID']);
	echo '</td><td valign="top" class="select">';
	echo '<a href="' . $RootPath . '/SelectSalesOrder.php?SelectedCustomer=' . $_SESSION['CustomerID'] . '">' . _('Modify Outstanding Sales Orders') . '</a><br />';
	echo '<a href="' . $RootPath . '/CustomerAllocations.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . _('Allocate Receipts or Credit Notes') . '</a><br />';
	echo '<a href="' . $RootPath . '/JobCards.php?DebtorNo=' . $_SESSION['CustomerID'] . '&amp;BranchNo=' . $_SESSION['BranchCode'] . '">' . _('Job Cards') . '</a><br />';
	if (isset($_SESSION['CustomerID']) and isset($_SESSION['BranchCode'])) {
		echo '<a href="' . $RootPath . '/CounterSales.php?DebtorNo=' . $_SESSION['CustomerID'] . '&amp;BranchNo=' . $_SESSION['BranchCode'] . '">' . _('Create a Counter Sale for this Customer') . '</a><br />';
	} //isset($_SESSION['CustomerID']) and isset($_SESSION['BranchCode'])
	echo '</td><td valign="top" class="select">';
	echo '<a href="' . $RootPath . '/Customers.php?">' . _('Add a New Customer') . '</a><br />';
	echo '<a href="' . $RootPath . '/Customers.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . _('Modify Customer Details') . '</a><br />';
	echo '<a href="' . $RootPath . '/CustomerBranches.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . _('Add/Modify/Delete Customer Branches') . '</a><br />';
	echo '<a href="' . $RootPath . '/SelectProduct.php">' . _('Special Customer Prices') . '</a><br />';
	echo '<a href="' . $RootPath . '/CustEDISetup.php">' . _('Customer EDI Configuration') . '</a><br />';
	echo '<a href="' . $RootPath . '/CustLoginSetup.php">' . _('Customer Login Configuration') . '</a>';
	echo '</td>';
	echo '</tr></table><br />';
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
		echo '<a href="' . $RootPath . '/Customers.php?">' . _('Add a New Customer') . '</a><br />';
	} //!isset($_SESSION['SalesmanLogin']) or $_SESSION['SalesmanLogin'] == ''
	echo '</td></tr></table>';
}
echo '<form onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (mb_strlen($msg) > 1) {
	prnMsg($msg, 'info');
} //mb_strlen($msg) > 1
echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Search for Customers') . '</p>';
echo '<table cellpadding="3" class="selection">';
echo '<tr><td colspan="2">' . _('Enter a partial Name') . ':</td><td>';
if (isset($_POST['Keywords'])) {
	echo '<input type="text" name="Keywords" value="' . $_POST['Keywords'] . '" size="20" minlength="0" maxlength="25" />';
} //isset($_POST['Keywords'])
else {
	echo '<input type="text" name="Keywords" size="20" minlength="0" maxlength="25" />';
}
echo '</td>
	<td><b>' . _('OR') . '</b></td><td>' . _('Enter a partial Code') . ':</td>
	<td>';
if (isset($_POST['CustCode'])) {
	echo '<input type="text" name="CustCode" value="' . $_POST['CustCode'] . '" size="15" minlength="0" maxlength="18" />';
} //isset($_POST['CustCode'])
else {
	echo '<input type="text" name="CustCode" size="15" minlength="0" maxlength="18" />';
}
echo '</td>
	</tr>
	<tr>
		<td><b>' . _('OR') . '</b></td>
		<td>' . _('Enter a partial Phone Number') . ':</td>
		<td>';
if (isset($_POST['CustPhone'])) {
	echo '<input type="text" name="CustPhone" value="' . $_POST['CustPhone'] . '" size="15" minlength="0" maxlength="18" />';
} //isset($_POST['CustPhone'])
else {
	echo '<input type="text" name="CustPhone" size="15" minlength="0" maxlength="18" />';
}
echo '</td>';
echo '<td><b>' . _('OR') . '</b></td>
		<td>' . _('Enter part of the Address') . ':</td>
		<td>';
if (isset($_POST['CustAdd'])) {
	echo '<input type="text" name="CustAdd" value="' . $_POST['CustAdd'] . '" size="20" minlength="0" maxlength="25" />';
} //isset($_POST['CustAdd'])
else {
	echo '<input type="text" name="CustAdd" size="20" minlength="0" maxlength="25" />';
}
echo '</td></tr>';
echo '<tr>
		<td><b>' . _('OR') . '</b></td>
		<td>' . _('Choose a Type') . ':</td>
		<td>';
if (isset($_POST['CustType'])) {
	// Show Customer Type drop down list
	$result2 = DB_query("SELECT typeid, typename FROM debtortype", $db);
	// Error if no customer types setup
	if (DB_num_rows($result2) == 0) {
		$DataError = 1;
		echo '<a href="CustomerTypes.php" target="_parent">' . _('Setup Types') . '</a>';
		echo '<tr><td colspan="2">' . prnMsg(_('No Customer types defined'), 'error') . '</td></tr>';
	} //DB_num_rows($result2) == 0
	else {
		// If OK show select box with option selected
		echo '<select name="CustType">
				<option value="ALL">' . _('Any') . '</option>';
		while ($myrow = DB_fetch_array($result2)) {
			if ($_POST['CustType'] == $myrow['typename']) {
				echo '<option selected="selected" value="' . $myrow['typename'] . '">' . $myrow['typename'] . '</option>';
			} //$_POST['CustType'] == $myrow['typename']
			else {
				echo '<option value="' . $myrow['typename'] . '">' . $myrow['typename'] . '</option>';
			}
		} //end while loop
		DB_data_seek($result2, 0);
		echo '</select></td>';
	}
} //isset($_POST['CustType'])
else {
	// No option selected="selected" yet, so show Customer Type drop down list
	$result2 = DB_query("SELECT typeid, typename FROM debtortype", $db);
	// Error if no customer types setup
	if (DB_num_rows($result2) == 0) {
		$DataError = 1;
		echo '<a href="CustomerTypes.php" target="_parent">' . _('Setup Types') . '</a>';
		echo '<tr><td colspan="2">' . prnMsg(_('No Customer types defined'), 'error') . '</td></tr>';
	} //DB_num_rows($result2) == 0
	else {
		// if OK show select box with available options to choose
		echo '<select name="CustType">
				<option value="ALL">' . _('Any') . '</option>';
		while ($myrow = DB_fetch_array($result2)) {
			echo '<option value="' . $myrow['typename'] . '">' . $myrow['typename'] . '</option>';
		} //end while loop
		DB_data_seek($result2, 0);
		echo '</select></td>';
	}
}

/* Option to select a sales area */
echo '<td><b>' . _('OR') . '</b></td>
		<td>' . _('Choose an Area') . ':</td><td>';
$result2 = DB_query("SELECT areacode, areadescription FROM areas", $db);
// Error if no sales areas setup
if (DB_num_rows($result2) == 0) {
	$DataError = 1;
	echo '<a href="Areas.php" target="_parent">' . _('Setup Areas') . '</a>';
	echo '<tr><td colspan="2">' . prnMsg(_('No Sales Areas defined'), 'error') . '</td></tr>';
} //DB_num_rows($result2) == 0
else {
	// if OK show select box with available options to choose
	echo '<select name="Area">';
	echo '<option value="ALL">' . _('Any') . '</option>';
	while ($myrow = DB_fetch_array($result2)) {
		if (isset($_POST['Area']) and $_POST['Area'] == $myrow['areacode']) {
			echo '<option selected="selected" value="' . $myrow['areacode'] . '">' . $myrow['areadescription'] . '</option>';
		} //isset($_POST['Area']) and $_POST['Area'] == $myrow['areacode']
		else {
			echo '<option value="' . $myrow['areacode'] . '">' . $myrow['areadescription'] . '</option>';
		}
	} //end while loop
	DB_data_seek($result2, 0);
	echo '</select></td></tr>';
}

echo '</table><br />';
echo '<div class="centre">
		<input type="submit" name="Search" value="' . _('Search Now') . '" />
		<input type="submit" name="CSV" value="' . _('CSV Format') . '" />
	</div>';
if (isset($_SESSION['SalesmanLogin']) and $_SESSION['SalesmanLogin'] != '') {
	prnMsg(_('Your account enables you to see only customers allocated to you'), 'warn', _('Note: Sales-person Login'));
} //isset($_SESSION['SalesmanLogin']) and $_SESSION['SalesmanLogin'] != ''
if (isset($result)) {
	unset($_SESSION['CustomerID']);
	$ListCount = DB_num_rows($result);
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
		echo '<br />
				<table cellpadding="2" class="selection">';
		$TableHeader = '<tr>
							<th>' . _('Code') . '</th>
							<th>' . _('Customer Name') . '</th>
							<th>' . _('Branch') . '</th>
							<th>' . _('Contact') . '</th>
							<th>' . _('Type') . '</th>
							<th>' . _('Phone') . '</th>
							<th>' . _('Fax') . '</th>
							<th>' . _('Email') . '</th>
						</tr>';
		echo $TableHeader;
		$j = 1;
		$k = 0; //row counter to determine background colour
		$RowIndex = 0;
	} //!isset($_POST['CSV'])
	if (DB_num_rows($result) <> 0) {
		if (isset($_POST['CSV'])) {
			$FileName = $_SESSION['reports_dir'] . '/Customer_Listing_' . Date('Y-m-d') . '.csv';
			echo '<br /><p class="page_title_text noPrint" ><a href="' . $FileName . '">' . _('Click to view the csv Search Result') . '</p>';
			$fp = fopen($FileName, 'w');
			while ($myrow2 = DB_fetch_array($result)) {
				fwrite($fp, $myrow2['debtorno'] . ',' . str_replace(',', '', $myrow2['name']) . ',' . str_replace(',', '', $myrow2['address1']) . ',' . str_replace(',', '', $myrow2['address2']) . ',' . str_replace(',', '', $myrow2['address3']) . ',' . str_replace(',', '', $myrow2['address4']) . ',' . str_replace(',', '', $myrow2['contactname']) . ',' . str_replace(',', '', $myrow2['typename']) . ',' . $myrow2['phoneno'] . ',' . $myrow2['faxno'] . ',' . $myrow2['email'] . "\n");
			} //$myrow2 = DB_fetch_array($result)
		} //isset($_POST['CSV'])
		if (!isset($_POST['CSV'])) {
			DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		} //!isset($_POST['CSV'])
		$i = 0; //counter for input controls
		while (($myrow = DB_fetch_array($result)) and ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} //$k == 1
			else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			echo '<td><input type="submit" name="SubmitCustomerSelection' . $i . '" value="' . htmlspecialchars($myrow['debtorno'] . ' ' . $myrow['branchcode'], ENT_QUOTES, 'UTF-8', false) . '" />
				<input type="hidden" name="SelectedCustomer' . $i . '" value="' . $myrow['debtorno'] . '" />
				<input type="hidden" name="SelectedBranch' . $i . '" value="' . $myrow['branchcode'] . '" /></td>
				<td>' . htmlspecialchars($myrow['name'], ENT_QUOTES, 'UTF-8', false) . '</td>
				<td>' . htmlspecialchars($myrow['brname'], ENT_QUOTES, 'UTF-8', false) . '</td>
				<td>' . $myrow['contactname'] . '</td>
				<td>' . $myrow['typename'] . '</td>
				<td>' . $myrow['phoneno'] . '</td>
				<td>' . $myrow['faxno'] . '</td>
				<td>' . $myrow['email'] . '</td>
			</tr>';
			$i++;
			$j++; //row counter
			if ($j == 11 and ($RowIndex + 1 != $_SESSION['DisplayRecordsMax'])) {
				$j = 1;
				echo $TableHeader;
			} //$j == 11 and ($RowIndex + 1 != $_SESSION['DisplayRecordsMax'])
			$RowIndex++;
			//end of page full new headings if
		} //($myrow = DB_fetch_array($result)) and ($RowIndex <> $_SESSION['DisplayRecordsMax'])
		//end of while loop
		echo '</table>';
		echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
	} //DB_num_rows($result) <> 0
} //isset($result)
//end if results to show
if (!isset($_POST['CSV'])) {
	if (isset($ListPageMax) and $ListPageMax > 1) {
		echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
echo '</div>
	  </form>';
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
			$sql = "SELECT debtortype.typeid,
							debtortype.typename
						FROM debtorsmaster INNER JOIN debtortype
					ON debtorsmaster.typeid = debtortype.typeid
					WHERE debtorsmaster.debtorno = '" . $_SESSION['CustomerID'] . "'";
			$ErrMsg = _('An error occurred in retrieving the information');
			$result = DB_query($sql, $db, $ErrMsg);
			$myrow = DB_fetch_array($result);
			$CustomerType = $myrow['typeid'];
			$CustomerTypeName = $myrow['typename'];
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
			$DataResult = DB_query($SQL, $db);
			$myrow = DB_fetch_array($DataResult);
			// Select some more data about the customer
			$SQL = "SELECT sum(ovamount+ovgst) as total
					FROM debtortrans
					WHERE debtorno = '" . $_SESSION['CustomerID'] . "'
					AND type !=12";
			$Total1Result = DB_query($SQL, $db);
			$row = DB_fetch_array($Total1Result);
			echo '<table width="45%" cellpadding="4">';
			echo '<tr><th style="width:33%" colspan="3">' . _('Customer Data') . '</th></tr>';
			echo '<tr><td valign="top" class="select">';
			/* Customer Data */
			if ($myrow['lastpaiddate'] == 0) {
				echo _('No receipts from this customer.') . '</td>
					<td class="select"></td>
					<td class="select"></td>
					</tr>';
			} //$myrow['lastpaiddate'] == 0
			else {
				echo _('Last Paid Date:') . '</td>
					<td class="select"> <b>' . ConvertSQLDate($myrow['lastpaiddate']) . '</b> </td>
					<td class="select">' . $myrow['lastpaiddays'] . ' ' . _('days') . '</td>
					</tr>';
			}
			echo '<tr><td class="select">' . _('Last Paid Amount (inc tax):') . '</td>
					<td class="select"> <b>' . locale_number_format($myrow['lastpaid'], $myrow['currdecimalplaces']) . '</b></td>
					<td class="select"></td>
					</tr>';
			echo '<tr><td class="select">' . _('Customer since:') . '</td>
					<td class="select"> <b>' . ConvertSQLDate($myrow['clientsince']) . '</b> </td>
					<td class="select">' . $myrow['customersincedays'] . ' ' . _('days') . '</td>
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
						<td class="select">' . _('Total Spend from this Customer (inc tax):') . ' </td>
						<td class="select"><b>' . locale_number_format($row['total'], $myrow['currdecimalplaces']) . '</b></td>
						<td class="select"></td>
						</tr>';
			}
			echo '<tr>
					<td class="select">' . _('Customer Type:') . ' </td>
					<td class="select"><b>' . $CustomerTypeName . '</b></td>
					<td class="select"></td>
					</tr>';
			echo '</table>';
		} //$_SESSION['CustomerID'] != ''
		// Customer Contacts
		$sql = "SELECT * FROM custcontacts
				WHERE debtorno='" . $_SESSION['CustomerID'] . "'
				ORDER BY contid";
		$result = DB_query($sql, $db);
		if (DB_num_rows($result) <> 0) {
			echo '<br /><div class="centre"><img src="' . $RootPath . '/css/' . $Theme . '/images/group_add.png" title="' . _('Customer Contacts') . '" alt="" />' . ' ' . _('Customer Contacts') . '</div>';
			echo '<br /><table width="45%">';
			echo '<tr>
					<th>' . _('Name') . '</th>
					<th>' . _('Role') . '</th>
					<th>' . _('Phone Number') . '</th>
					<th>' . _('Email') . '</th>
					<th>' . _('Notes') . '</th>
					<th>' . _('Edit') . '</th>
					<th>' . _('Delete') . '</th>
					<th> <a href="AddCustomerContacts.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . _('Add New Contact') . '</a> </th>
				</tr>';
			$k = 0; //row colour counter
			while ($myrow = DB_fetch_array($result)) {
				if ($k == 1) {
					echo '<tr class="OddTableRows">';
					$k = 0;
				} //$k == 1
				else {
					echo '<tr class="EvenTableRows">';
					$k = 1;
				}
				echo '<td>' . $myrow[2] . '</td>
					<td>' . $myrow[3] . '</td>
					<td>' . $myrow[4] . '</td>
					<td><a href="mailto:' . $myrow[6] . '">' . $myrow[6] . '</a></td>
					<td>' . $myrow[5] . '</td>
					<td><a href="AddCustomerContacts.php?Id=' . $myrow[0] . '&amp;DebtorNo=' . $myrow[1] . '">' . _('Edit') . '</a></td>
					<td><a href="AddCustomerContacts.php?Id=' . $myrow[0] . '&amp;DebtorNo=' . $myrow[1] . '&amp;delete=1">' . _('Delete') . '</a></td>
					</tr>';
			} //END WHILE LIST LOOP
			echo '</table>';
		} //DB_num_rows($result) <> 0
		else {
			if ($_SESSION['CustomerID'] != "") {
				echo '<br /><div class="centre"><img src="' . $RootPath . '/css/' . $Theme . '/images/group_add.png" title="' . _('Customer Contacts') . '" alt="" /><a href="AddCustomerContacts.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . ' ' . _('Add New Contact') . '</a></div>';
			} //$_SESSION['CustomerID'] != ""
		}
		// Customer Notes
		$sql = "SELECT noteid,
						debtorno,
						href,
						note,
						date,
						priority
				FROM custnotes
				WHERE debtorno='" . $_SESSION['CustomerID'] . "'
				ORDER BY date DESC";
		$result = DB_query($sql, $db);
		if (DB_num_rows($result) <> 0) {
			echo '<br /><div class="centre"><img src="' . $RootPath . '/css/' . $Theme . '/images/note_add.png" title="' . _('Customer Notes') . '" alt="" />' . ' ' . _('Customer Notes') . '</div><br />';
			echo '<table width="45%">';
			echo '<tr>
					<th>' . _('date') . '</th>
					<th>' . _('note') . '</th>
					<th>' . _('hyperlink') . '</th>
					<th>' . _('priority') . '</th>
					<th>' . _('Edit') . '</th>
					<th>' . _('Delete') . '</th>
					<th> <a href="AddCustomerNotes.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . ' ' . _('Add New Note') . '</a> </th>
				</tr>';
			$k = 0; //row colour counter
			while ($myrow = DB_fetch_array($result)) {
				if ($k == 1) {
					echo '<tr class="OddTableRows">';
					$k = 0;
				} //$k == 1
				else {
					echo '<tr class="EvenTableRows">';
					$k = 1;
				}
				echo '<td>' . ConvertSQLDate($myrow['date']) . '</td>
					<td>' . $myrow['note'] . '</td>
					<td><a href="' . $myrow['href'] . '">' . $myrow['href'] . '</a></td>
					<td>' . $myrow['priority'] . '</td>
					<td><a href="AddCustomerNotes.php?Id=' . $myrow['noteid'] . '&amp;DebtorNo=' . $myrow['debtorno'] . '">' . _('Edit') . '</a></td>
					<td><a href="AddCustomerNotes.php?Id=' . $myrow['noteid'] . '&amp;DebtorNo=' . $myrow['debtorno'] . '&amp;delete=1">' . _('Delete') . '</a></td>
					</tr>';
			} //END WHILE LIST LOOP
			echo '</table>';
		} //DB_num_rows($result) <> 0
		else {
			if ($_SESSION['CustomerID'] != '') {
				echo '<br /><div class="centre"><img src="' . $RootPath . '/css/' . $Theme . '/images/note_add.png" title="' . _('Customer Notes') . '" alt="" /><a href="AddCustomerNotes.php?DebtorNo=' . $_SESSION['CustomerID'] . '">' . ' ' . _('Add New Note for this Customer') . '</a></div>';
			} //$_SESSION['CustomerID'] != ''
		}
		// Custome Type Notes
		$sql = "SELECT * FROM debtortypenotes
				WHERE typeid='" . $CustomerType . "'
				ORDER BY date DESC";
		$result = DB_query($sql, $db);
		if (DB_num_rows($result) <> 0) {
			echo '<br /><div class="centre"><img src="' . $RootPath . '/css/' . $Theme . '/images/folder_add.png" title="' . _('Customer Type (Group) Notes') . '" alt="" />' . ' ' . _('Customer Type (Group) Notes for:' . '<b> ' . $CustomerTypeName . '</b>') . '</div><br />';
			echo '<table width="45%">';
			echo '<tr>
				 	<th>' . _('date') . '</th>
				  	<th>' . _('note') . '</th>
				   	<th>' . _('file link / reference / URL') . '</th>
				   	<th>' . _('priority') . '</th>
				   	<th>' . _('Edit') . '</th>
				   	<th>' . _('Delete') . '</th>
				   	<th><a href="AddCustomerTypeNotes.php?DebtorType=' . $CustomerType . '">' . _('Add New Group Note') . '</a></th>
				  </tr>';
			$k = 0; //row colour counter
			while ($myrow = DB_fetch_array($result)) {
				if ($k == 1) {
					echo '<tr class="OddTableRows">';
					$k = 0;
				} //$k == 1
				else {
					echo '<tr class="EvenTableRows">';
					$k = 1;
				}
				echo '<td>' . $myrow[4] . '</td>
					<td>' . $myrow[3] . '</td>
					<td>' . $myrow[2] . '</td>
					<td>' . $myrow[5] . '</td>
					<td><a href="AddCustomerTypeNotes.php?Id=' . $myrow[0] . '&amp;DebtorType=' . $myrow[1] . '">' . _('Edit') . '</a></td>
					<td><a href="AddCustomerTypeNotes.php?Id=' . $myrow[0] . '&amp;DebtorType=' . $myrow[1] . '&amp;delete=1">' . _('Delete') . '</a></td>
					</tr>';
			} //END WHILE LIST LOOP
			echo '</table>';
		} //DB_num_rows($result) <> 0
		else {
			if ($_SESSION['CustomerID'] != '') {
				echo '<br /><div class="centre"><img src="' . $RootPath . '/css/' . $Theme . '/images/folder_add.png" title="' . _('Customer Group Notes') . '" alt="" /><a href="AddCustomerTypeNotes.php?DebtorType=' . $CustomerType . '">' . ' ' . _('Add New Group Note') . '</a></div><br />';
			} //$_SESSION['CustomerID'] != ''
		}
	} //$_SESSION['Extended_CustomerInfo'] == 1
} //isset($_SESSION['CustomerID']) and $_SESSION['CustomerID'] != ''
echo '<script  type="text/javascript">defaultControl(document.forms[0].CustCode);</script>';
include('includes/footer.inc');
?>