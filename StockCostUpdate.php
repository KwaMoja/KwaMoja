<?php
include ('includes/session.php');

$UpdateSecurity = $_SESSION['PageSecurityArray']['PurchData.php'];
$Title = _('Stock Cost Update');
include ('includes/header.php');
include ('includes/SQL_CommonFunctions.php');
echo '<script src="javascripts/Chart.js"></script>';
if (isset($_GET['StockID'])) {
	$StockId = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])) {
	$StockId = trim(mb_strtoupper($_POST['StockID']));
}

echo '<div class="toplink"><a href="', $RootPath, '/SelectProduct.php">', _('Back to Items'), '</a></div>';

echo '<p class="page_title_text" >
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/supplier.png" title="', _('Inventory Adjustment'), '" alt="" />', $Title, '
	</p>';

if (isset($_POST['UpdateData'])) {

	$SQL = "SELECT stockcosts.materialcost,
					stockcosts.labourcost,
					stockcosts.overheadcost,
					mbflag,
					sum(quantity) as totalqoh
			FROM stockmaster
			INNER JOIN locstock
				ON stockmaster.stockid=locstock.stockid
			LEFT JOIN stockcosts
				ON stockmaster.stockid=stockcosts.stockid
				AND stockcosts.succeeded=0
			WHERE stockmaster.stockid='" . $StockId . "'
			GROUP BY description,
					units,
					lastcost,
					actualcost,
					stockcosts.materialcost,
					stockcosts.labourcost,
					stockcosts.overheadcost,
					mbflag";
	$ErrMsg = _('The entered item code does not exist');
	$OldResult = DB_query($SQL, $ErrMsg);
	$OldRow = DB_fetch_array($OldResult);
	$_POST['QOH'] = $OldRow['totalqoh'];
	$_POST['OldMaterialCost'] = $OldRow['materialcost'];
	if ($OldRow['mbflag'] == 'M') {
		$_POST['OldLabourCost'] = $OldRow['labourcost'];
		$_POST['OldOverheadCost'] = $OldRow['overheadcost'];
	} else {
		$_POST['OldLabourCost'] = 0;
		$_POST['OldOverheadCost'] = 0;
		$_POST['LabourCost'] = 0;
		$_POST['OverheadCost'] = 0;
	}
	DB_free_result($OldResult);

	$OldCost = $_POST['OldMaterialCost'] + $_POST['OldLabourCost'] + $_POST['OldOverheadCost'];
	$NewCost = filter_number_format($_POST['MaterialCost']) + filter_number_format($_POST['LabourCost']) + filter_number_format($_POST['OverheadCost']);

	$Result = DB_query("SELECT * FROM stockmaster WHERE stockid='" . $StockId . "'");
	$MyRow = DB_fetch_row($Result);
	if (DB_num_rows($Result) == 0) {
		prnMsg(_('The entered item code does not exist'), 'error', _('Non-existent Item'));
	} elseif (abs($NewCost - $OldCost) > pow(10, -($_SESSION['StandardCostDecimalPlaces'] + 1))) {

		$Result = DB_Txn_Begin();
		ItemCostUpdateGL($StockId, $NewCost);

		$ErrMsg = _('The old cost details for the stock item could not be updated because');
		$DbgMsg = _('The SQL that failed was');
		$SQL = "UPDATE stockcosts SET succeeded=1 WHERE stockid='" . $StockId . "'";
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

		$SQL = "INSERT INTO stockcosts VALUES('" . $StockId . "',
										'" . filter_number_format($_POST['MaterialCost']) . "',
										'" . filter_number_format($_POST['LabourCost']) . "',
										'" . filter_number_format($_POST['OverheadCost']) . "',
										CURRENT_TIMESTAMP,
										0)";
		$ErrMsg = _('The new cost details for the stock item could not be inserted because');
		$DbgMsg = _('The SQL that failed was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

		$SQL = "UPDATE stockmaster SET lastcostupdate=CURRENT_DATE WHERE stockid='" . $StockId . "'";
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

		$Result = DB_Txn_Commit();

		UpdateCost($StockId); //Update any affected BOMs
		
	}
}

$ErrMsg = _('The cost details for the stock item could not be retrieved because');
$DbgMsg = _('The SQL that failed was');

$SQL = "SELECT description,
				units,
				lastcost,
				actualcost,
				stockcosts.materialcost,
				stockcosts.labourcost,
				stockcosts.overheadcost,
				mbflag,
				stocktype,
				lastcostupdate,
				sum(quantity) as totalqoh
			FROM stockmaster
			INNER JOIN locstock
				ON stockmaster.stockid=locstock.stockid
			INNER JOIN stockcategory
				ON stockmaster.categoryid = stockcategory.categoryid
			LEFT JOIN stockcosts
				ON stockmaster.stockid = stockcosts.stockid
				AND stockcosts.succeeded=0
			WHERE stockmaster.stockid='" . $StockId . "'
			GROUP BY description,
					units,
					lastcost,
					actualcost,
					stockcosts.materialcost,
					stockcosts.labourcost,
					stockcosts.overheadcost,
					mbflag,
					stocktype";
$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

$MyRow = DB_fetch_array($Result);
$ItemDescription = $MyRow['description'];

echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

echo '<table cellpadding="2">
		<tr>
			<th colspan="2">', _('Item Code'), ':
				<input type="text" name="StockID" value="', $StockId, '"  required="required" maxlength="20" />
				<input type="submit" name="Show" value="', _('Show Cost Details'), '" />
			</th>
		</tr>
		<tr>
			<th colspan="2">', $StockId, ' - ', $MyRow['description'], '</th>
		</tr>
		<tr>
			<th colspan="2">', _('Total Quantity On Hand'), ': ', $MyRow['totalqoh'], ' ', $MyRow['units'], '</th>
		</tr>
		<tr>
			<th colspan="2">', _('Last Cost update on'), ': ', ConvertSQLDate($MyRow['lastcostupdate']), '</th>
		</tr>
	</table>';

if (($MyRow['mbflag'] == 'D' and $MyRow['stocktype'] != 'L') or $MyRow['mbflag'] == 'A' or $MyRow['mbflag'] == 'K') {
	echo '</form>'; // Close the form
	if ($MyRow['mbflag'] == 'D') {
		echo '<br />', $StockId, ' ', _('is a service item');
	} else if ($MyRow['mbflag'] == 'A') {
		echo '<br />', $StockId, ' ', _('is an assembly part');
	} else if ($MyRow['mbflag'] == 'K') {
		echo '<br />', $StockId, ' ', _('is a kit set part');
	}
	prnMsg(_('Cost information cannot be modified for kits assemblies or service items') . '. ' . _('Please select a different part'), 'warn');
	include ('includes/footer.php');
	exit;
}

$HistorySQL = "SELECT stockcosts.materialcost,
					stockcosts.labourcost,
					stockcosts.overheadcost,
					stockcosts.costfrom,
					stockcosts.succeeded
				FROM stockcosts
				WHERE stockid='" . $StockId . "'
				ORDER BY costfrom DESC
				LIMIT 10";
$HistoryResult = DB_query($HistorySQL);
echo '<table cellpadding="2">
		<tr>
			<th>', _('Cost From'), '</th>
			<th>', _('Material Cost'), '</th>
			<th>', _('Labour Cost'), '</th>
			<th>', _('Overhead Cost'), '</th>
		</tr>';
while ($HistoryRow = DB_fetch_array($HistoryResult)) {
	echo '<tr class="striped_row">
			<td>', ConvertSQLDate($HistoryRow['costfrom']), '</td>
			<td class="number">', locale_number_format($HistoryRow['materialcost'], $_SESSION['StandardCostDecimalPlaces']), '</td>
			<td class="number">', locale_number_format($HistoryRow['labourcost'], $_SESSION['StandardCostDecimalPlaces']), '</td>
			<td class="number">', locale_number_format($HistoryRow['overheadcost'], $_SESSION['StandardCostDecimalPlaces']), '</td>
		</tr>';
	$Dates[] = '"' . ConvertSQLDate($HistoryRow['costfrom']) . '"';
	$MaterialCosts[] = $HistoryRow['materialcost'];
	$LabourCosts[] = $HistoryRow['labourcost'];
	$OverheadCosts[] = $HistoryRow['overheadcost'];
	$AllCosts[] = $HistoryRow['materialcost'] + $HistoryRow['labourcost'] + $HistoryRow['overheadcost'];
}
echo '</table>';

echo '<fieldset>
		<legend>', _('Cost Update'), '</legend>';

if (!in_array($UpdateSecurity, $_SESSION['AllowedPageSecurityTokens'])) {
	echo '<field>
			<label for="MaterialCost">', _('Cost'), ':</label>
			<div class="fieldtext">', locale_number_format($MyRow['materialcost'] + $MyRow['labourcost'] + $MyRow['overheadcost'], $_SESSION['StandardCostDecimalPlaces']), '</div>
		</field>';
} else {

	if ($MyRow['mbflag'] == 'M') {
		echo '<input type="hidden" name="MaterialCost" value="', $MyRow['materialcost'], '" />';
		echo '<field>
				<label for="MaterialCost">', _('Standard Material Cost Per Unit'), ':</label>
				<div class="fieldtext">', locale_number_format($MyRow['materialcost'], $_SESSION['StandardCostDecimalPlaces']), '</div>
			</field>';
		echo '<field>
				<label for="LabourCost">', _('Standard Labour Cost Per Unit'), ':</label>
				<input type="text" class="number" name="LabourCost" value="', locale_number_format($MyRow['labourcost'], $_SESSION['StandardCostDecimalPlaces']), '" />
			</field>';
		echo '<field>
				<label for="OverheadCost">', _('Standard Overhead Cost Per Unit'), ':</label>
				<input type="text" class="number" name="OverheadCost" value="', locale_number_format($MyRow['overheadcost'], $_SESSION['StandardCostDecimalPlaces']), '" />
			</field>';
	} elseif ($MyRow['mbflag'] == 'B' or $MyRow['mbflag'] == 'D') {
		echo '<field>
				<label for="MaterialCost">', _('Standard Cost'), ':</label>
				<input type="text" class="number" name="MaterialCost" value="', locale_number_format($MyRow['materialcost'], $_SESSION['StandardCostDecimalPlaces']), '" />
			</field>';
	} else {
		echo '<input type="hidden" name="LabourCost" value="0" />
			<input type="hidden" name="OverheadCost" value="0" />';
	}
	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="UpdateData" value="', _('Update'), '" />
		</div>';
}
if ($MyRow['mbflag'] != 'D') {
	echo '<div class="centre">
			<a href="', $RootPath, '/StockStatus.php?StockID=', urlencode($StockId), '">', _('Show Stock Status'), '</a><br />
			<a href="', $RootPath, '/StockMovements.php?StockID=', urlencode($StockId), '">', _('Show Stock Movements'), '</a><br />
			<a href="', $RootPath, '/StockUsage.php?StockID=', urlencode($StockId), '">', _('Show Stock Usage'), '</a><br />
			<a href="', $RootPath, '/SelectSalesOrder.php?SelectedStockItem=', urlencode($StockId), '">', _('Search Outstanding Sales Orders'), '</a><br />
			<a href="', $RootPath, '/SelectCompletedOrder.php?SelectedStockItem=', urlencode($StockId), '">', _('Search Completed Sales Orders'), '</a>
		</div>';
}
echo '</form>';
include ('includes/footer.php');
?>