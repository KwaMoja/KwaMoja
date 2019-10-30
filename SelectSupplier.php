<?php
/* Selects a supplier. A supplier is required to be selected before any AP transactions and before any maintenance or inquiry of the supplier */

include ('includes/session.php');
$Title = _('Search Suppliers');

/* Manual links before header.php */
$ViewTopic = 'AccountsPayable';
$BookMark = 'SelectSupplier';

include ('includes/header.php');
include ('includes/SQL_CommonFunctions.php');
if (!isset($_SESSION['SupplierID'])) {
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/supplier.png" title="', _('Search'), '" alt="" />', ' ', _('Suppliers'), '
		</p>';
}
if (isset($_GET['SupplierID'])) {
	$_SESSION['SupplierID'] = $_GET['SupplierID'];
}
// only get geocode information if integration is on, and supplier has been selected
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
	echo '<script src="https://maps.google.com/maps?file=api&amp;v=2&amp;key=' . $ApiKey . '"';
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
			</script>';
}

if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
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
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/supplier.png" title="', _('Supplier'), '" alt="" />', ' ', _('Supplier'), ' : <b>', stripslashes($_SESSION['SupplierID']), ' - ', $SupplierName, '</b> ', _('has been selected'), '.
		</p>';
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
			echo '<table>';
			$ContactSQL = "SELECT contact,
									position,
									tel,
									email
								FROM suppliercontacts
								WHERE supplierid='" . $_SESSION['SupplierID'] . "'";
			$ContactResult = DB_query($ContactSQL);

			if (DB_num_rows($ContactResult) > 0) {
				echo '<tr>
						<th>', _('Contact Name'), '</th>
						<th>', _('Position'), '</th>
						<th>', _('Telephone Number'), '</th>
						<th">', _('Email Address'), '</th>
					</tr>';
				while ($ContactRow = DB_fetch_array($ContactResult)) {
					echo '<tr class="striped_row">
							<td>', $ContactRow['contact'], '</td>
							<td>', $ContactRow['position'], '</td>
							<td>', $ContactRow['tel'], '</td>
							<td>', $ContactRow['email'], '</td>
						</tr>';
				}
			}

			echo '<tr>
					<th colspan="4">', _('Supplier Data'), '</th>
				</tr>
				<tr class="striped_row">';
			/* Supplier Data */
			//echo "Distance to this Supplier: <b>TBA</b><br />";
			if ($MyRow['lastpaiddate'] == 0) {
				echo '<td>', _('No payments yet to this supplier.'), '</td>
					<td valign="top" class="select"></td>';
			} else {
				echo '<td>', _('Last Paid'), ':</td>
					<td><b>', ConvertSQLDate($MyRow['lastpaiddate']), '</b></td>';
			}
			echo '<td>', _('Last Paid Amount'), ':</td>
					<td class="number">  <b>', locale_number_format($MyRow['lastpaid'], $MyRow['currdecimalplaces']), '</b></td></tr>';
			echo '<tr class="striped_row">
					<td>', _('Supplier since'), ':</td>
					<td> <b>', ConvertSQLDate($MyRow['suppliersince']), '</b></td>
					<td>', _('Total Spend with this Supplier'), ':</td>
					<td class="number"><b>', locale_number_format($Row['total'], $MyRow['currdecimalplaces']), '</b></td>
				</tr>';
			echo '<tr class="striped_row">
					<td>', _('Email Address'), ':</td>
					<td> <b>', $MyRow['email'], '</b></td>
					<td>', _('Telephone Number'), ':</td>
					<td class="number"> <b>', $MyRow['telephone'], '</b></td>
				</tr>';
			echo '</table>';
		}
	}
	echo '<div class="page_help_text">', _('Select a menu option to operate using this supplier.'), '</div>';
	echo '<fieldset style="text-align:center">';
	// Customer inquiries options:
	echo '<fieldset class="MenuList">
			<legend><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/reports.png" data-title="', _('Inquiries and Reports'), '" />', _('Supplier Inquiries'), '</legend>
			<ul>
				<li class="MenuItem">
					<a href="' . $RootPath . '/SupplierInquiry.php?SupplierID=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('Account Inquiry') . '</a>
				</li>
				<li class="MenuItem">
					<a href="' . $RootPath . '/SupplierGRNAndInvoiceInquiry.php?SelectedSupplier=' . urlencode($_SESSION['SupplierID']) . '&amp;SupplierName=' . urlencode($SupplierName) . '">' . _('Supplier Delivery Note AND GRN inquiry') . '</a>
				</li>
				<li class="MenuItem">
					<a href="', $RootPath . '/PO_SelectOSPurchOrder.php?SelectedSupplier=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('Outstanding Purchase Orders') . '</a>
				</li>
				<li class="MenuItem">
					<a href="' . $RootPath . '/PO_SelectPurchOrder.php?SelectedSupplier=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('View All Orders') . '</a>
				</li>
				<li class="MenuItem">
					', wikiLink('Supplier', urlencode(stripslashes($_SESSION['SupplierID']))), '
				</li>
				<li class="MenuItem">
					<a href="' . $RootPath . '/ShiptsList.php?SupplierID=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '&amp;SupplierName=' . urlencode($SupplierName) . '">' . _('Open shipments') . '</a>
				</li>
				<li class="MenuItem">
					<a href="' . $RootPath . '/Shipt_Select.php?SelectedSupplier=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('Modify/Close Shipments') . '</a>
				</li>
				<li class="MenuItem">
					<a href="' . $RootPath . '/SuppPriceList.php?SelectedSupplier=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('Price List') . '</a>
				</li>
			</ul>
		</fieldset>';

	echo '<fieldset class="MenuList">
			<legend><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/transactions.png" data-title="', _('Supplier Transactions'), '" />', _('Supplier Transactions'), '</legend>
			<ul>
				<li class="MenuItem">
					<a href="' . $RootPath . '/PO_Header.php?NewOrder=Yes&amp;SupplierID=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('Enter a Purchase Order') . '</a>
				</li>
				<li class="MenuItem">
					<a href="' . $RootPath . '/SupplierInvoice.php?New=True&SupplierID=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('Enter an Invoice') . '</a>
				</li>
				<li class="MenuItem">
					<a href="' . $RootPath . '/SupplierCredit.php?New=true&amp;SupplierID=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('Enter a Credit Note') . '</a>
				</li>
				<li class="MenuItem">
					<a href="' . $RootPath . '/Payments.php?SupplierID=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('Enter a Payment/Receipt') . '</a>
				</li>
				<li class="MenuItem">
					<a href="' . $RootPath . '/ReverseGRN.php?SupplierID=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('Reverse an Outstanding Goods Received Note (GRN)') . '</a>
				</li>
			</ul>
		</fieldset>';

	echo '<fieldset class="MenuList">
			<legend><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" data-title="', _('Supplier Maintenance'), '" />', _('Supplier Maintenance'), '</legend>
			<ul>
				<li class="MenuItem">
					<a href="' . $RootPath . '/Suppliers.php">' . _('Add Supplier') . '</a>
				</li>
				<li class="MenuItem">
					<a href="' . $RootPath . '/Suppliers.php?Copy=Yes&SupplierID=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('Copy Supplier') . '</a>
				</li>
				<li class="MenuItem">
					<a href="' . $RootPath . '/Suppliers.php?SupplierID=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('Modify Supplier') . '</a>
				</li>
				<li class="MenuItem">
					<a href="' . $RootPath . '/SupplierContacts.php?SupplierID=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('Contacts') . '</a>
				</li>
				<li class="MenuItem">
					<a href="' . $RootPath . '/SellThroughSupport.php?SupplierID=' . urlencode(stripslashes($_SESSION['SupplierID'])) . '">' . _('Sell Through Support') . '</a>
				</li>
				<li class="MenuItem">
					<a href="' . $RootPath . '/Shipments.php?NewShipment=Yes">' . _('Shipments') . '</a>
				</li>
				<li class="MenuItem">
					<a href="' . $RootPath . '/SuppLoginSetup.php">' . _('Supplier Login') . '</a>
				</li>
			</ul>
		</fieldset>';

	echo '</fieldset>';

}
echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

echo '<p class="page_title_text">
		<img src="', $RootPath . '/css/' . $_SESSION['Theme'], '/images/magnifier.png" title="', _('Search'), '" alt="" />', ' ', _('Search for Suppliers'), '
	</p>';

echo '<fieldset>
		<field>
			<label for="Keywords">', _('Enter a partial Name'), ':</label>';
if (isset($_POST['Keywords'])) {
	echo '<input type="search" name="Keywords" autofocus="autofocus" value="', $_POST['Keywords'], '" size="20" maxlength="25" />';
} else {
	echo '<input type="search" name="Keywords" autofocus="autofocus" size="20" maxlength="25" />';
}
echo '</field>';

echo '<h1>' . _('OR') . '</h1>';

echo '<field>
		<label for="SupplierCode">', _('Enter a partial Code'), ':</label>';
if (isset($_POST['SupplierCode'])) {
	echo '<input type="search" name="SupplierCode" value="' . $_POST['SupplierCode'] . '" size="15" maxlength="18" />';
} else {
	echo '<input type="search" name="SupplierCode" size="15" maxlength="18" />';
}
echo '</field>
	</fieldset>';

echo '<div class="centre">
		<input type="submit" name="Search" value="', _('Search Now'), '" />
	</div>';

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
		echo '<p>&nbsp;&nbsp;', $_POST['PageOffset'], ' ', _('of'), ' ', $ListPageMax, ' ', _('pages'), '. ', _('Go to Page'), ': </p>';
		echo '<select name="PageOffset">';
		$ListPage = 1;
		while ($ListPage <= $ListPageMax) {
			if ($ListPage == $_POST['PageOffset']) {
				echo '<option value="', $ListPage, '" selected="selected">', $ListPage, '</option>';
			} else {
				echo '<option value="', $ListPage, '">', $ListPage, '</option>';
			}
			$ListPage++;
		}
		echo '</select>
			<input type="submit" name="Go" value="', _('Go'), '" />
			<input type="submit" name="Previous" value="', _('Previous'), '" />
			<input type="submit" name="Next" value="', _('Next'), '" />';
	}
	echo '<input type="hidden" name="Search" value="', _('Search Now'), '" />';
	$k = 0; //row counter to determine background colour
	$RowIndex = 0;
	if (DB_num_rows($Result) <> 0) {
		DB_data_seek($Result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
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
						<th>', _('Telephone'), '</th>
						<th>', _('Email'), '</th>
						<th>', _('URL'), '</th>
					</tr>
				</thead>';

		echo '<tbody>';
		while (($MyRow = DB_fetch_array($Result)) and ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			echo '<tr class="striped_row">
					<td><input type="submit" name="Select" value="', $MyRow['supplierid'], '" /></td>
					<td>', $MyRow['suppname'], '</td>
					<td>', $MyRow['currcode'], '</td>
					<td>', $MyRow['address1'], '</td>
					<td>', $MyRow['address2'], '</td>
					<td>', $MyRow['address3'], '</td>
					<td>', $MyRow['address4'], '</td>
					<td>', $MyRow['telephone'], '</td>
					<td><a href="mailto://', $MyRow['email'], '">', $MyRow['email'], '</a></td>
					<td><a href="', $MyRow['url'], '"target="_blank">', $MyRow['url'], '</a></td>
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
	echo '<p>&nbsp;&nbsp;', $_POST['PageOffset'], ' ', _('of'), ' ', $ListPageMax, ' ', _('pages'), '. ', _('Go to Page'), ': </p>';
	echo '<select name="PageOffset">';
	$ListPage = 1;
	while ($ListPage <= $ListPageMax) {
		if ($ListPage == $_POST['PageOffset']) {
			echo '<option value="', $ListPage, '" selected="selected">', $ListPage, '</option>';
		} else {
			echo '<option value="', $ListPage, '">', $ListPage, '</option>';
		}
		$ListPage++;
	}
	echo '</select>
		<input type="submit" name="Go" value="', _('Go'), '" />
		<input type="submit" name="Previous" value="', _('Previous'), '" />
		<input type="submit" name="Next" value="', _('Next'), '" />';
}
echo '</form>';
// Only display the geocode map if the integration is turned on, and there is a latitude/longitude to display
if (isset($_SESSION['SupplierID']) and $_SESSION['SupplierID'] != '') {
	if ($_SESSION['geocode_integration'] == 1) {
		if ($Latitude == 0) {
			echo '<div class="centre">', _('Mapping is enabled, but no Mapping data to display for this Supplier.'), '</div>';
		} else {
			echo '<table>
					<thead>
						<tr>
							<th>', _('Supplier Mapping'), '</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="centre">', _('Mapping is enabled, Map will display below.'), '</td>
						</tr>
						<tr>
							<td class="centre">', // Mapping:
			'<div class="centre" id="map" style="width: ', $MapWidth, 'px; height: ', $MapHeight, 'px"></div>
							</td>
						</tr>
					<tbody>
				</table>';
		}
	}
}

include ('includes/footer.php');
?>