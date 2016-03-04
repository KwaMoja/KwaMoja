<?php

include('includes/session.inc');
$Title = _('Search Suppliers');

/* Manual links before header.inc */
$ViewTopic = 'AccountsPayable';
$BookMark = 'SelectSupplier';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
if (!isset($_SESSION['SupplierID'])) {
	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/supplier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Suppliers') . '</p>';
}
if (isset($_GET['SupplierID'])) {
	$_SESSION['SupplierID'] = $_GET['SupplierID'];
}
// only get geocode information if integration is on, and supplier has been selected
if ($_SESSION['geocode_integration'] == 1 and isset($_SESSION['SupplierID'])) {
	$SQL = "SELECT * FROM geocode_param WHERE 1";
	$ErrMsg = _('An error occurred in retrieving the information');
	$Result = DB_query($SQL, $ErrMsg);
	$MyRow = DB_fetch_array($Result);
	$SQL = "SELECT suppliers.supplierid,
					suppliers.lat,
					suppliers.lng
				FROM suppliers
				WHERE suppliers.supplierid = '" . $_SESSION['SupplierID'] . "'
				ORDER BY suppliers.supplierid";
	$ErrMsg = _('An error occurred in retrieving the information');
	$Result2 = DB_query($SQL, $ErrMsg);
	$MyRow2 = DB_fetch_array($Result2);
	$Latitude = $MyRow2['lat'];
	$Longitude = $MyRow2['lng'];
	$ApiKey = $MyRow['geocode_key'];
	$CenterLong = $MyRow['center_long'];
	$CenterLat = $MyRow['center_lat'];
	$MapHeight = $MyRow['map_height'];
	$MapWidth = $MyRow['map_width'];
	$MapHost = $MyRow['map_host'];
	echo '<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=' . $ApiKey . '"';
	echo ' type="text/javascript"></script>';
	echo ' <script type="text/javascript">';
	echo 'function load() {
		if (GBrowserIsCompatible()) {
			var map = new GMap2(document.getElementById("map"));
			map.addControl(new GSmallMapControl());
			map.addControl(new GMapTypeControl());';
	echo 'map.setCenter(new GLatLng(' . $Latitude . ', ' . $Longitude . '), 11);';
	echo 'var marker = new GMarker(new GLatLng(' . $Latitude . ', ' . $Longitude . '));';
	echo 'map.addOverlay(marker);
			GEvent.addListener(marker, "click", function() {
			marker.openInfoWindowHtml(WINDOW_HTML);
			});
			marker.openInfoWindowHtml(WINDOW_HTML);
			}
			}
			</script>
			<body onload="load()" onunload="GUnload()" >';
}

if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}
if (isset($_POST['Select'])) {
	/*User has hit the button selecting a supplier */
	$_SESSION['SupplierID'] = $_POST['Select'];
	unset($_POST['Select']);
	unset($_POST['Keywords']);
	unset($_POST['SupplierCode']);
	unset($_POST['Search']);
	unset($_POST['Go']);
	unset($_POST['Next']);
	unset($_POST['Previous']);
}
if (isset($_POST['Search']) or isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Previous'])) {

	if (mb_strlen($_POST['Keywords']) > 0 and mb_strlen($_POST['SupplierCode']) > 0) {
		prnMsg(_('Supplier name keywords have been used in preference to the Supplier code extract entered'), 'info');
	}
	$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
	$SQL = "SELECT supplierid,
					suppname,
					currcode,
					address1,
					address2,
					address3,
					address4,
					telephone,
					email,
					url
				FROM suppliers
				WHERE suppname " . LIKE . " '" . $SearchString . "'
					AND supplierid " . LIKE . " '%" . $_POST['SupplierCode'] . "%'
				ORDER BY suppname";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 1) {
		$MyRow = DB_fetch_row($Result);
		$SingleSupplierReturned = $MyRow[0];
	}
	if (isset($SingleSupplierReturned)) {
		/*there was only one supplier returned */
		$_SESSION['SupplierID'] = DB_escape_string($SingleSupplierReturned);
		unset($_POST['Keywords']);
		unset($_POST['SupplierCode']);
		unset($_POST['Search']);
	} else {
		unset($_SESSION['SupplierID']);
	}
}
if (isset($_SESSION['SupplierID'])) {
	$SupplierName = '';
	$SQL = "SELECT suppliers.suppname
			FROM suppliers
			WHERE suppliers.supplierid ='" . $_SESSION['SupplierID'] . "'";
	$SupplierNameResult = DB_query($SQL);
	if (DB_num_rows($SupplierNameResult) == 1) {
		$MyRow = DB_fetch_row($SupplierNameResult);
		$SupplierName = $MyRow[0];
	}
	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/supplier.png" title="' . _('Supplier') . '" alt="" />' . ' ' . _('Supplier') . ' : <b>' . stripslashes($_SESSION['SupplierID']) . ' - ' . $SupplierName . '</b> ' . _('has been selected') . '.</p>';
	// Extended Info only if selected in Configuration
	if ($_SESSION['Extended_SupplierInfo'] == 1) {
		if ($_SESSION['SupplierID'] != '') {
			$SQL = "SELECT suppliers.suppname,
							suppliers.lastpaid,
							suppliers.lastpaiddate,
							suppliersince,
							currencies.decimalplaces AS currdecimalplaces,
							email,
							telephone
					FROM suppliers
					INNER JOIN currencies
						ON suppliers.currcode=currencies.currabrev
					WHERE suppliers.supplierid ='" . $_SESSION['SupplierID'] . "'";
			$ErrMsg = _('An error occurred in retrieving the information');
			$DataResult = DB_query($SQL, $ErrMsg);
			$MyRow = DB_fetch_array($DataResult);
			// Select some more data about the supplier
			$SQL = "SELECT SUM(ovamount) AS total FROM supptrans WHERE supplierno = '" . $_SESSION['SupplierID'] . "' AND (type = '20' OR type='21')";
			$Total1Result = DB_query($SQL);
			$Row = DB_fetch_array($Total1Result);
			echo '<table style="width:75%" cellpadding="4">';
			echo '<tr>
					<th style="width:40%">' . _('Contact Name') . '</th>
					<th style="width:20%">' . _('Position') . '</th>
					<th style="width:20%">' . _('Telephone Number') . '</th>
					<th style="width:20%">' . _('Email Address') . '</th>
				</tr>';
			$ContactSQL = "SELECT contact,
									position,
									tel,
									email
								FROM suppliercontacts
								WHERE supplierid='" . $_SESSION['SupplierID'] . "'";
			$ContactResult = DB_query($ContactSQL);
			while ($ContactRow = DB_fetch_array($ContactResult)) {
				echo '<tr>
						<td style="width:40%" class="select">' . $ContactRow['contact'] . '</td>
						<td style="width:20%" class="select">' . $ContactRow['position'] . '</td>
						<td style="width:20%" class="select">' . $ContactRow['tel'] . '</td>
						<td style="width:20%" class="select">' . $ContactRow['email'] . '</td>
					</tr>';
			}
			echo '<tr>
					<th colspan="4">' . _('Supplier Data') . '</th>
				</tr>
				<tr>
					<td style="width:30%" valign="top" class="select">';
			/* Supplier Data */
			//echo "Distance to this Supplier: <b>TBA</b><br />";
			if ($MyRow['lastpaiddate'] == 0) {
				echo _('No payments yet to this supplier.') . '</td>
					<td valign="top" class="select"></td>';
			} else {
				echo _('Last Paid') . ':</td>
					<td style="width:20%" valign="top" class="select"> <b>' . ConvertSQLDate($MyRow['lastpaiddate']) . '</b></td>';
			}
			echo '<td style="width:30%" valign="top" class="select">' . _('Last Paid Amount') . ':</td>
					<td style="width:20%" valign="top" class="number select">  <b>' . locale_number_format($MyRow['lastpaid'], $MyRow['currdecimalplaces']) . '</b></td></tr>';
			echo '<tr>
					<td style="width:30%" valign="top" class="select">' . _('Supplier since') . ':</td>
					<td style="width:20%" valign="top" class="select"> <b>' . ConvertSQLDate($MyRow['suppliersince']) . '</b></td>
					<td style="width:30%" valign="top" class="select">' . _('Total Spend with this Supplier') . ':</td>
					<td style="width:20%" valign="top" class="number select"> <b>' . locale_number_format($Row['total'], $MyRow['currdecimalplaces']) . '</b></td>
				</tr>';
			echo '<tr>
					<td style="width:30%" valign="top" class="select">' . _('Email Address') . ':</td>
					<td style="width:20%" valign="top" class="select"> <b>' . $MyRow['email'] . '</b></td>
					<td style="width:30%" valign="top" class="select">' . _('Telephone Number') . ':</td>
					<td style="width:20%" valign="top" class="number select"> <b>' . $MyRow['telephone'] . '</b></td>
				</tr>';
			echo '</table>';
		}
	}
	echo '<div class="page_help_text">' . _('Select a menu option to operate using this supplier.') . '</div>';
	echo '<table width="90%" cellpadding="4">
			<tr>
				<th style="width:33%">' . _('Supplier Inquiries') . '</th>
				<th style="width:33%">' . _('Supplier Transactions') . '</th>
				<th style="width:33%">' . _('Supplier Maintenance') . '</th>
			</tr>';
	echo '<tr>
			<td valign="top" class="select">';
	/* Inquiry Options */
	echo '<a href="' . $RootPath . '/SupplierInquiry.php?SupplierID=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('Account Inquiry') . '</a>';
	echo '<a href="' . $RootPath . '/SupplierGRNAndInvoiceInquiry.php?SelectedSupplier=' . urlencode($_SESSION['SupplierID']) . '&amp;SupplierName=' . urlencode($SupplierName) . '">' . _('Supplier Delivery Note AND GRN inquiry') . '</a>';

	echo '<a href="' . $RootPath . '/PO_SelectOSPurchOrder.php?SelectedSupplier=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('Outstanding Purchase Orders') . '</a>';
	echo '<a href="' . $RootPath . '/PO_SelectPurchOrder.php?SelectedSupplier=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('View All Orders') . '</a>';
	wikiLink('Supplier', urlencode(stripslashes($_SESSION['SupplierID'])));
	echo '<a href="' . $RootPath . '/ShiptsList.php?SupplierID=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '&amp;SupplierName=' . urlencode($SupplierName) . '">' . _('Open shipments') . '</a>';
	echo '<a href="' . $RootPath . '/Shipt_Select.php?SelectedSupplier=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('Modify/Close Shipments') . '</a>';
	echo '<a href="' . $RootPath . '/SuppPriceList.php?SelectedSupplier=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('Price List') . '</a>';
	echo '</td><td valign="top" class="select">';
	/* Supplier Transactions */
	echo '<a href="' . $RootPath . '/PO_Header.php?NewOrder=Yes&amp;SupplierID=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('Enter a Purchase Order') . '</a>';
	echo '<a href="' . $RootPath . '/SupplierInvoice.php?SupplierID=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('Enter an Invoice') . '</a>';
	echo '<a href="' . $RootPath . '/SupplierCredit.php?New=true&amp;SupplierID=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('Enter a Credit Note') . '</a>';
	echo '<a href="' . $RootPath . '/Payments.php?SupplierID=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('Enter a Payment/Receipt') . '</a>';
	echo '<a href="' . $RootPath . '/ReverseGRN.php?SupplierID=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('Reverse an Outstanding Goods Received Note (GRN)') . '</a>';
	echo '</td><td valign="top" class="select">';
	/* Supplier Maintenance */
	echo '<a href="' . $RootPath . '/Suppliers.php">' . _('Add Supplier') . '</a>
		<a href="' . $RootPath . '/Suppliers.php?SupplierID=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('Modify Supplier') . '</a>
		<a href="' . $RootPath . '/SupplierContacts.php?SupplierID=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('Contacts') . '</a>
		<a href="' . $RootPath . '/SellThroughSupport.php?SupplierID=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('Sell Through Support') . '</a>
		<a href="' . $RootPath . '/Shipments.php?NewShipment=Yes">' . _('Shipments') . '</a>
		<a href="' . $RootPath . '/SuppLoginSetup.php">' . _('Supplier Login') . '</a>
		</td>
		</tr>
		</table>';

}
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Search for Suppliers') . '</p>
	<table cellpadding="3" class="selection">
	<tr>
		<td>' . _('Enter a partial Name') . ':</td>
		<td>';
if (isset($_POST['Keywords'])) {
	echo '<input type="text" name="Keywords" autofocus="autofocus" value="' . $_POST['Keywords'] . '" size="20" maxlength="25" />';
} else {
	echo '<input type="text" name="Keywords" autofocus="autofocus" size="20" maxlength="25" />';
}
echo '</td>
		<td><b>' . _('OR') . '</b></td>
		<td>' . _('Enter a partial Code') . ':</td>
		<td>';
if (isset($_POST['SupplierCode'])) {
	echo '<input type="text" name="SupplierCode" value="' . $_POST['SupplierCode'] . '" size="15" maxlength="18" />';
} else {
	echo '<input type="text" name="SupplierCode" size="15" maxlength="18" />';
}
echo '</td></tr>
		</table>
		<div class="centre"><input type="submit" name="Search" value="' . _('Search Now') . '" /></div>';
//if (isset($Result) and !isset($SingleSupplierReturned)) {
if (isset($_POST['Search'])) {
	$ListCount = DB_num_rows($Result);
	$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
	if (isset($_POST['Next'])) {
		if ($_POST['PageOffset'] < $ListPageMax) {
			$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
		}
	}
	if (isset($_POST['Previous'])) {
		if ($_POST['PageOffset'] > 1) {
			$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
		}
	}
	if ($ListPageMax > 1) {
		echo '<p>&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': </p>';
		echo '<select name="PageOffset">';
		$ListPage = 1;
		while ($ListPage <= $ListPageMax) {
			if ($ListPage == $_POST['PageOffset']) {
				echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
			} else {
				echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
			}
			$ListPage++;
		}
		echo '</select>
			<input type="submit" name="Go" value="' . _('Go') . '" />
			<input type="submit" name="Previous" value="' . _('Previous') . '" />
			<input type="submit" name="Next" value="' . _('Next') . '" />';
		echo '<br />';
	}
	echo '<input type="hidden" name="Search" value="' . _('Search Now') . '" />';
	$k = 0; //row counter to determine background colour
	$RowIndex = 0;
	if (DB_num_rows($Result) <> 0) {
		DB_data_seek($Result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		echo '<table cellpadding="2">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Supplier Name') . '</th>
						<th>' . _('Currency') . '</th>
						<th>' . _('Address 1') . '</th>
						<th>' . _('Address 2') . '</th>
						<th>' . _('Address 3') . '</th>
						<th>' . _('Address 4') . '</th>
						<th>' . _('Telephone') . '</th>
						<th>' . _('Email') . '</th>
						<th>' . _('URL') . '</th>
					</tr>
				</thead>';

		echo '<tbody>';
		while (($MyRow = DB_fetch_array($Result)) and ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			echo '<td><input type="submit" name="Select" value="' . $MyRow['supplierid'] . '" /></td>
					<td>' . $MyRow['suppname'] . '</td>
					<td>' . $MyRow['currcode'] . '</td>
					<td>' . $MyRow['address1'] . '</td>
					<td>' . $MyRow['address2'] . '</td>
					<td>' . $MyRow['address3'] . '</td>
					<td>' . $MyRow['address4'] . '</td>
					<td>' . $MyRow['telephone'] . '</td>
					<td><a href="mailto://' . $MyRow['email'] . '">' . $MyRow['email'] . '</a></td>
					<td><a href="' . $MyRow['url'] . '"target="_blank">' . $MyRow['url'] . '</a></td>
				</tr>';
			$RowIndex = $RowIndex + 1;
			//end of page full new headings if
		}
		//end of while loop
		echo '</tbody>';
		echo '</table>';
	} else {
		prnMsg(_('There are no suppliers returned for this criteria. Please enter new criteria'), 'info');
	}
}
//end if results to show
if (isset($ListPageMax) and $ListPageMax > 1) {
	echo '<p>&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': </p>';
	echo '<select name="PageOffset">';
	$ListPage = 1;
	while ($ListPage <= $ListPageMax) {
		if ($ListPage == $_POST['PageOffset']) {
			echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
		} else {
			echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
		}
		$ListPage++;
	}
	echo '</select>
		<input type="submit" name="Go" value="' . _('Go') . '" />
		<input type="submit" name="Previous" value="' . _('Previous') . '" />
		<input type="submit" name="Next" value="' . _('Next') . '" />';
	echo '<br />';
}
echo '</form>';
// Only display the geocode map if the integration is turned on, and there is a latitude/longitude to display
if (isset($_SESSION['SupplierID']) and $_SESSION['SupplierID'] != '') {
	if ($_SESSION['geocode_integration'] == 1) {
		if ($Latitude == 0) {
			echo '<br />';
			echo '<div class="centre">' . _('Mapping is enabled, but no Mapping data to display for this Supplier.') . '</div>';
		} else {
			echo '<div class="centre"><br />';
			echo '<tr>
					<td colspan="2">';
			echo '<table width="45%" class="selection">
					<tr>
						<th style="width:33%">' . _('Supplier Mapping') . '</th>
					</tr>';
			echo '</td>
					<td valign="top">';
			/* Mapping */
			echo '<div class="centre">' . _('Mapping is enabled, Map will display below.') . '</div>';
			echo '<div class="centre" id="map" style="width: ' . $MapWidth . 'px; height: ' . $MapHeight . 'px"></div></div><br />';
			echo '</td></tr></table>';
		}
	}
}

include('includes/footer.inc');
?>