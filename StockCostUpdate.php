<?php

include('includes/session.inc');

$UpdateSecurity = $_SESSION['PageSecurityArray']['PurchData.php'];
$Title = _('Stock Cost Update');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
echo '<script src="javascripts/Chart.js"></script>';
if (isset($_GET['StockID'])) {
	$StockId = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])) {
	$StockId = trim(mb_strtoupper($_POST['StockID']));
}

echo '<div class="toplink"><a href="' . $RootPath . '/SelectProduct.php">' . _('Back to Items') . '</a></div>';

echo '<p class="page_title_text" >
		<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/supplier.png" title="' . _('Inventory Adjustment') . '" alt="" />' . $Title . '
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
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table widthe="98%">
		<tr>
			<td style="vertical-align:top; width:50%">';

echo '<table cellpadding="2" class="selection">
		<tr>
			<th colspan="2">' . _('Item Code') . ':
				<input type="text" name="StockID" value="' . $StockId . '"  required="required" maxlength="20" />
				<input type="submit" name="Show" value="' . _('Show Cost Details') . '" />
			</th>
		</tr>
		<tr>
			<th colspan="2">' . $StockId . ' - ' . $MyRow['description'] . '</th>
		</tr>
		<tr>
			<th colspan="2">' . _('Total Quantity On Hand') . ': ' . $MyRow['totalqoh'] . ' ' . $MyRow['units'] . '</th>
		</tr>
		<tr>
			<th colspan="2">' . _('Last Cost update on') . ': ' . ConvertSQLDate($MyRow['lastcostupdate']) . '</th>
		</tr>
	</table>';

if (($MyRow['mbflag'] == 'D' and $MyRow['stocktype'] != 'L') or $MyRow['mbflag'] == 'A' or $MyRow['mbflag'] == 'K') {
	echo '</form>'; // Close the form
	if ($MyRow['mbflag'] == 'D') {
		echo '<br />' . $StockId . ' ' . _('is a service item');
	} else if ($MyRow['mbflag'] == 'A') {
		echo '<br />' . $StockId . ' ' . _('is an assembly part');
	} else if ($MyRow['mbflag'] == 'K') {
		echo '<br />' . $StockId . ' ' . _('is a kit set part');
	}
	prnMsg(_('Cost information cannot be modified for kits assemblies or service items') . '. ' . _('Please select a different part'), 'warn');
	include('includes/footer.inc');
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
echo '<table cellpadding="2" class="selection">
		<tr>
			<th>' . _('Cost From') . '</th>
			<th>' . _('Material Cost') . '</th>
			<th>' . _('Labour Cost') . '</th>
			<th>' . _('Overhead Cost') . '</th>
		</tr>';
while ($HistoryRow = DB_fetch_array($HistoryResult)) {
	echo '<tr>
			<td>' . ConvertSQLDate($HistoryRow['costfrom']) . '</td>
			<td class="number">' . locale_number_format($HistoryRow['materialcost'], $_SESSION['StandardCostDecimalPlaces']) . '</td>
			<td class="number">' . locale_number_format($HistoryRow['labourcost'], $_SESSION['StandardCostDecimalPlaces']) . '</td>
			<td class="number">' . locale_number_format($HistoryRow['overheadcost'], $_SESSION['StandardCostDecimalPlaces']) . '</td>
		</tr>';
	$Dates[] = '"' . ConvertSQLDate($HistoryRow['costfrom']) . '"';
	$MaterialCosts[] = $HistoryRow['materialcost'];
	$LabourCosts[] = $HistoryRow['labourcost'];
	$OverheadCosts[] = $HistoryRow['overheadcost'];
	$AllCosts[] = $HistoryRow['materialcost'] + $HistoryRow['labourcost'] + $HistoryRow['overheadcost'];
}
echo '</table>';

if (!in_array($UpdateSecurity, $_SESSION['AllowedPageSecurityTokens'])) {
	echo '<table cellpadding="2" class="selection">
			<tr>
				<td>' . _('Cost') . ':</td>
				<td class="number">' . locale_number_format($MyRow['materialcost'] + $MyRow['labourcost'] + $MyRow['overheadcost'], $_SESSION['StandardCostDecimalPlaces']) . '</td>
			</tr>
		</table>';
} else {

	if ($MyRow['mbflag'] == 'M') {
		echo '<input type="hidden" name="MaterialCost" value="' . $MyRow['materialcost'] . '" />';
		echo '<table cellpadding="2" class="selection">';
		echo '<tr>
				<td>' . _('Standard Material Cost Per Unit') . ':</td>
				<td class="number">' . locale_number_format($MyRow['materialcost'], $_SESSION['StandardCostDecimalPlaces']) . '</td>
			</tr>';
		echo '<tr>
				<td>' . _('Standard Labour Cost Per Unit') . ':</td>
				<td class="number"><input type="text" class="number" name="LabourCost" value="' . locale_number_format($MyRow['labourcost'], $_SESSION['StandardCostDecimalPlaces']) . '" /></td>
			</tr>';
		echo '<tr>
				<td>' . _('Standard Overhead Cost Per Unit') . ':</td>
				<td class="number"><input type="text" class="number" name="OverheadCost" value="' . locale_number_format($MyRow['overheadcost'], $_SESSION['StandardCostDecimalPlaces']) . '" /></td>
			</tr>';
	} elseif ($MyRow['mbflag'] == 'B' or $MyRow['mbflag'] == 'D') {
		echo '<tr>
				<th>' . _('Standard Cost') . ':
					<input type="text" class="number" name="MaterialCost" value="' . locale_number_format($MyRow['materialcost'], $_SESSION['StandardCostDecimalPlaces']) . '" />
				</th>
			</tr>';
	} else {
		echo '<input type="hidden" name="LabourCost" value="0" />
			<input type="hidden" name="OverheadCost" value="0" />';
	}
	echo '</table>
			 <div class="centre">
				  <input type="submit" name="UpdateData" value="' . _('Update') . '" />
			 </div>';
}
echo '</td>'; //box1
echo '<td>';
echo '<canvas id="canvas" height="450" width="600"></canvas>';
echo '<script>

var lineChartData = {
labels : [' . implode(',', array_reverse($Dates)) . '],
datasets : [
{
fillColor : "rgba(128,0,128,0.5)",
strokeColor : "rgba(128,0,128,1)",
pointColor : "rgba(128,0,128,1)",
pointStrokeColor : "#fff",
data : [' . implode(',', array_reverse($AllCosts)) . ']
},
{
fillColor : "rgba(30,144,255,0.5)",
strokeColor : "rgba(30,144,255,1)",
pointColor : "rgba(30,144,255,1)",
pointStrokeColor : "#fff",
data : [' . implode(',', array_reverse($MaterialCosts)) . ']
},
{
fillColor : "rgba(255,255,0,0.5)",
strokeColor : "rgba(255,255,0,1)",
pointColor : "rgba(255,255,0,1)",
pointStrokeColor : "#fff",
data : [' . implode(',', array_reverse($LabourCosts)) . ']
},
{
fillColor : "rgba(139,105,20,0.5)",
strokeColor : "rgba(139,105,20,1)",
pointColor : "rgba(139,105,20,1)",
pointStrokeColor : "#fff",
data : [' . implode(',', array_reverse($OverheadCosts)) . ']
}
]

}

var myLine = new Chart(document.getElementById("canvas").getContext("2d")).Line(lineChartData);

</script>';
echo '</td>
		<td>
			<ul style="border-radius:4px;border:#7F7F7F solid 1px; padding:15px">
				<li style="color:#800080">Total</li>
				<li style="color:#1E90FF">Materials</li>
				<li style="color:#FFFF00">Labour</li>
				<li style="color:#8B6914">Overheads</li>
			</ul>
		</td>
	</tr>'; //Box

echo '</table>'; //Container
if ($MyRow['mbflag'] != 'D') {
	echo '<div class="centre">
			<a href="' . $RootPath . '/StockStatus.php?StockID=' . urlencode($StockId) . '">' . _('Show Stock Status') . '</a>
			<a href="' . $RootPath . '/StockMovements.php?StockID=' . urlencode($StockId) . '">' . _('Show Stock Movements') . '</a>
			<a href="' . $RootPath . '/StockUsage.php?StockID=' . urlencode($StockId) . '">' . _('Show Stock Usage') . '</a>
			<a href="' . $RootPath . '/SelectSalesOrder.php?SelectedStockItem=' . urlencode($StockId) . '">' . _('Search Outstanding Sales Orders') . '</a>
			<a href="' . $RootPath . '/SelectCompletedOrder.php?SelectedStockItem=' . urlencode($StockId) . '">' . _('Search Completed Sales Orders') . '</a>
		</div>';
}
echo '</form>';
include('includes/footer.inc');
?>