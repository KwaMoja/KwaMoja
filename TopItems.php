<?php
/* Session started in session.php for password checking and authorisation level check
 config.php is in turn included in session.php*/
include ('includes/session.php');
include ('includes/SQL_CommonFunctions.php');
$Title = _('Top Items Searching');
include ('includes/header.php');
//check if input already
if (!(isset($_POST['Search']))) {

	echo '<p class="page_title_text" >
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', _('Top Sales Order Search'), '" alt="" />', ' ', _('Top Sales Order Search'), '
		</p>';

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	//to view store location
	echo '<fieldset>
			<legend>', _('Report Criteria'), '</legend>';

	$SQL = "SELECT locationname,
					locations.loccode
				FROM locations
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" . $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				ORDER BY locations.locationname";
	echo '<field>
			<label for="Location">', _('Select Location'), '</label>
			<select name="Location">
				<option selected="selected" value="All">', _('All Locations'), '</option>';
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
	}
	echo '</select>
		</field>';
	//to view list of customer
	$SQL = "SELECT typename,
					typeid
				FROM debtortype
			ORDER BY typename";
	$Result = DB_query($SQL);
	echo '<field>
			<label for="Customers">', _('Select Customer Type'), '</label>
			<select required="required" name="Customers">
				<option value="All">', _('All'), '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="', $MyRow['typeid'], '">', $MyRow['typename'], '</option>';
	}
	echo '</select>
		</field>';

	// stock category selection
	$SQL = "SELECT categoryid,
					categorydescription
			FROM stockcategory
			ORDER BY categorydescription";
	$Result1 = DB_query($SQL);

	echo '<field>
			<label for="StockCat">', _('In Stock Category'), ':</label>
			<select name="StockCat">';
	if (!isset($_POST['StockCat'])) {
		$_POST['StockCat'] = 'All';
	}
	if ($_POST['StockCat'] == 'All') {
		echo '<option selected="selected" value="All">', _('All'), '</option>';
	} else {
		echo '<option value="All">', _('All'), '</option>';
	}
	while ($MyRow1 = DB_fetch_array($Result1)) {
		if ($MyRow1['categoryid'] == $_POST['StockCat']) {
			echo '<option selected="selected" value="', $MyRow1['categoryid'], '">', $MyRow1['categorydescription'], '</option>';
		} else {
			echo '<option value="', $MyRow1['categoryid'], '">', $MyRow1['categorydescription'], '</option>';
		}
	}
	echo '</select>
		</field>';

	//view order by list to display
	echo '<field>
			<label for="Sequence">', _('Select Order By '), ':</label>
			<select required="required" name="Sequence">
				<option value="totalinvoiced">', _('Total Pieces'), '</option>
				<option value="valuesales">', _('Value of Sales'), '</option>
			</select>
		</field>';
	//View number of days
	echo '<field>
			<label for="NumberOfDays">', _('Number Of Days'), ':</label>
			<input class="number" type="text" name="NumberOfDays" size="8" required="required" maxlength="8" value="30" />
		 </field>';
	//Stock in days less than
	echo '<field>
			<label for="MaxDaysOfStock">', _('With less than'), ' </label>
			<input class="number" type="text" name="MaxDaysOfStock" size="8" required="required" maxlength="8" value="999" />
			', ' ', _('Days of Stock (QOH + QOO) Available'), '
		 </field>';
	//view number of NumberOfTopItems items
	echo '<field>
			<label for="NumberOfTopItems">', _('Number Of Top Items'), ':</label>
			<input class="number" type="text" name="NumberOfTopItems" size="8" required="required" maxlength="8" value="100" />
		 </field>
	</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="Search" value="', _('Search'), '" />
		</div>
	</form>';
} else {
	// everything below here to view NumberOfTopItems items sale on selected location
	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', -filter_number_format($_POST['NumberOfDays'])));

	$SQL = "SELECT salesorderdetails.stkcode,
					SUM(salesorderdetails.qtyinvoiced) AS totalinvoiced,
					SUM(salesorderdetails.qtyinvoiced * salesorderdetails.unitprice/currencies.rate ) AS valuesales,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag,
					currencies.rate,
					debtorsmaster.currcode,
					fromstkloc,
					stockmaster.decimalplaces
				FROM salesorderdetails
				INNER JOIN salesorders
					ON salesorderdetails.orderno = salesorders.orderno
				INNER JOIN debtorsmaster
					ON salesorders.debtorno = debtorsmaster.debtorno
				INNER JOIN stockmaster
					ON salesorderdetails.stkcode = stockmaster.stockid
				INNER JOIN currencies
					ON debtorsmaster.currcode = currencies.currabrev
				INNER JOIN locationusers
					ON locationusers.loccode=salesorders.fromstkloc
					AND locationusers.userid='" . $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				WHERE salesorderdetails.actualdispatchdate >= '" . $FromDate . "'";

	if ($_POST['Location'] != 'All') {
		$SQL = $SQL . "	AND salesorders.fromstkloc = '" . $_POST['Location'] . "'";
	}

	if ($_POST['Customers'] != 'All') {
		$SQL = $SQL . "	AND debtorsmaster.typeid = '" . $_POST['Customers'] . "'";
	}

	if ($_POST['StockCat'] != 'All') {
		$SQL = $SQL . "	AND stockmaster.categoryid = '" . $_POST['StockCat'] . "'";
	}

	$SQL = $SQL . "	GROUP BY salesorderdetails.stkcode
					ORDER BY `" . $_POST['Sequence'] . "` DESC
					LIMIT " . filter_number_format($_POST['NumberOfTopItems']);

	$Result = DB_query($SQL);

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/sales.png" title="', _('Top Sales Order Search'), '" alt="" />', _('Top Sales Items List') . '
		</p>';
	echo '<table>
			<thead>
				<tr>
					<th class="SortedColumn">', _('#'), '</th>
					<th class="SortedColumn">', _('Code'), '</th>
					<th class="SortedColumn">', _('Description'), '</th>
					<th class="SortedColumn">', _('Total Invoiced'), '</th>
					<th>', _('Units'), '</th>
					<th class="SortedColumn">', _('Value Sales'), '</th>
					<th>', _('On Hand'), '</th>
					<th>', _('On Order'), '</th>
					<th>', _('Stock (Days)'), '</th>
				</tr>
			</thead>';
	echo '<input type="hidden" value="', $_POST['Location'], '" name="Location" />
			<input type="hidden" value="', $_POST['Sequence'], '" name="Sequence" />
			<input type="hidden" value="', filter_number_format($_POST['NumberOfDays']), '" name="NumberOfDays" />
			<input type="hidden" value="', $_POST['Customers'], '" name="Customers" />
			<input type="hidden" value="', filter_number_format($_POST['NumberOfTopItems']), '" name="NumberOfTopItems" />';

	$i = 1;
	echo '<tbody>';
	while ($MyRow = DB_fetch_array($Result)) {
		$QOH = 0;
		$QOO = 0;
		switch ($MyRow['mbflag']) {
			case 'A':
			case 'D':
			case 'K':
				$QOH = _('N/A');
				$QOO = _('N/A');
			break;
			case 'M':
			case 'B':
				$QohSql = "SELECT sum(quantity)
								FROM locstock
								INNER JOIN locationusers
									ON locationusers.loccode=locstock.loccode
									AND locationusers.userid='" . $_SESSION['UserID'] . "'
									AND locationusers.canview=1
								WHERE stockid = '" . DB_escape_string($MyRow['stkcode']) . "'";
				$QohResult = DB_query($QohSql);
				$QohRow = DB_fetch_row($QohResult);
				$QOH = $QohRow[0];
				// Get the QOO due to Purchase orders for all locations. Function defined in SQL_CommonFunctions.php
				$QOO = GetQuantityOnOrderDueToPurchaseOrders($MyRow['stkcode']);
				// Get the QOO dues to Work Orders for all locations. Function defined in SQL_CommonFunctions.php
				$QOO+= GetQuantityOnOrderDueToWorkOrders($MyRow['stkcode']);
			break;
		}
		if (is_numeric($QOH) and is_numeric($QOO)) {
			$DaysOfStock = ($QOH + $QOO) / ($MyRow['totalinvoiced'] / $_POST['NumberOfDays']);
		} elseif (is_numeric($QOH)) {
			$DaysOfStock = $QOH / ($MyRow['totalinvoiced'] / $_POST['NumberOfDays']);
		} elseif (is_numeric($QOO)) {
			$DaysOfStock = $QOO / ($MyRow['totalinvoiced'] / $_POST['NumberOfDays']);
		} else {
			$DaysOfStock = 0;
		}
		if ($DaysOfStock < $_POST['MaxDaysOfStock']) {
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . urlencode($MyRow['stkcode']) . '">' . $MyRow['stkcode'] . '</a>';
			if (is_numeric($QOH)) {
				$QOH = locale_number_format($QOH, $MyRow['decimalplaces']);
			}
			if (is_numeric($QOO)) {
				$QOO = locale_number_format($QOO, $MyRow['decimalplaces']);
			}

			echo '<tr class="striped_row">
					<td class="number">', $i, '</td>
					<td>', $CodeLink, '</td>
					<td>', $MyRow['description'], '</td>
					<td class="number">', locale_number_format($MyRow['totalinvoiced'], $MyRow['decimalplaces']), '</td>
					<td>', $MyRow['units'], '</td>
					<td class="number">', locale_number_format($MyRow['valuesales'], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
					<td class="number">', $QOH, '</td>
					<td class="number">', $QOO, '</td>
					<td class="number">', locale_number_format($DaysOfStock, 0), '</td>
				</tr>';
		}
		++$i;
	}
	echo '</tbody>
		</table>';

	echo '<div class="centre">
			<a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">', _('Select different criteria'), '</a>
		</div>';
}
include ('includes/footer.php');
?>