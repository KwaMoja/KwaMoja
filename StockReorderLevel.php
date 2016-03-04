<?php

include('includes/session.inc');
$Title = _('Stock Re-Order Level Maintenance');
include('includes/header.inc');

if (isset($_GET['StockID'])) {
	$StockId = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])) {
	$StockId = trim(mb_strtoupper($_POST['StockID']));
} else {
	$StockId = '';
}

echo '<div class="toplink">
		<a href="' . $RootPath . '/SelectProduct.php">' . _('Back to Items') . '</a>
	</div>';

echo '<p class="page_title_text" >
		<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/inventory.png" title="' . _('Inventory') . '" alt="" /><b>' . $Title . '</b>
	</p>';

$Result = DB_query("SELECT description, units FROM stockmaster WHERE stockid='" . $StockId . "'");
$MyRow = DB_fetch_row($Result);

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

$SQL = "SELECT locstock.loccode,
				locations.locationname,
				locstock.quantity,
				locstock.reorderlevel,
				stockmaster.decimalplaces
				stockmaster.decimalplaces,
				canupd
			FROM locstock
			INNER JOIN locations
				ON locstock.loccode=locations.loccode
			INNER JOIN locationusers
				ON locationusers.loccode=locstock.loccode
				AND locationusers.userid='" .  $_SESSION['UserID'] . "'
				AND locationusers.canview=1
			INNER JOIN stockmaster
				ON locstock.stockid=stockmaster.stockid
			WHERE locstock.stockid = '" . $StockId . "'
			ORDER BY locations.locationname";

$ErrMsg = _('The stock held at each location cannot be retrieved because');
$DbgMsg = _('The SQL that failed was');

$LocStockResult = DB_query($SQL, $ErrMsg, $DbgMsg);

echo '<table class="selection">
		<thead>
			<tr>
				<th colspan="3">' . _('Stock Code') . ':<input type="text" name="StockID" size="21" value="' . $StockId . '" required="required" maxlength="20" /><input type="submit" name="Show" value="' . _('Show Re-Order Levels') . '" /></th>
			</tr>
			<tr>
				<th colspan="3"><h3><b>' . $StockId . ' - ' . $MyRow[0] . '</b>  (' . _('In Units of') . ' ' . $MyRow[1] . ')</h3></th>
			</tr>
			<tr>
				<th class="SortedColumn">' . _('Location') . '</th>
				<th>' . _('Quantity On Hand') . '</th>
				<th>' . _('Re-Order Level') . '</th>
			</tr>
		</thead>';

$k = 0; //row colour counter
echo '<tbody>';
while ($MyRow = DB_fetch_array($LocStockResult)) {

	if ($k == 1) {
		echo '<tr class="EvenTableRows">';
		$k = 0;
	} else {
		echo '<tr class="OddTableRows">';
		$k = 1;
	}

	if (isset($_POST['UpdateData']) AND $_POST['Old_' . $MyRow['loccode']] != filter_number_format($_POST[$MyRow['loccode']]) AND is_numeric(filter_number_format($_POST[$MyRow['loccode']])) AND filter_number_format($_POST[$MyRow['loccode']]) >= 0) {

		$MyRow['reorderlevel'] = filter_number_format($_POST[$MyRow['loccode']]);
		$SQL = "UPDATE locstock SET reorderlevel = '" . filter_number_format($_POST[$MyRow['loccode']]) . "'
	   		WHERE stockid = '" . $StockId . "'
			AND loccode = '" . $MyRow['loccode'] . "'";
		$UpdateReorderLevel = DB_query($SQL);

	}

	if ($MyRow['canupd'] == 1) {
		$UpdateCode = '<input title="' . _('Input safety stock quantity') . '" type="text" class="number" name="%s" maxlength="10" size="10" value="%s" /><input type="hidden" name="Old_%s" value="%s" />';
	} else {
		$UpdateCode = '<input type="hidden" name="%s">%s<input type="hidden" name="Old_%s" value="%s" />';
	}

	printf('<td>%s</td>
			<td class="number">%s</td>
			<td class="number">' . $UpdateCode . '</td></tr>',
			$MyRow['locationname'],
			locale_number_format($MyRow['quantity'], $MyRow['decimalplaces']),
			$MyRow['loccode'],
			$MyRow['reorderlevel'],
			$MyRow['loccode'],
			$MyRow['reorderlevel']);
	//end of page full new headings if
}
//end of while loop

echo '</tbody>
	</table>
	<div class="centre">
		<input type="submit" name="UpdateData" value="' . _('Update') . '" />';

echo '<a href="' . $RootPath . '/StockMovements.php?StockID=' . urlencode($StockId) . '">' . _('Show Stock Movements') . '</a>';
echo '<a href="' . $RootPath . '/StockUsage.php?StockID=' . urlencode($StockId) . '">' . _('Show Stock Usage') . '</a>';
echo '<a href="' . $RootPath . '/SelectSalesOrder.php?SelectedStockItem=' . urlencode($StockId) . '">' . _('Search Outstanding Sales Orders') . '</a>';
echo '<a href="' . $RootPath . '/SelectCompletedOrder.php?SelectedStockItem=' . urlencode($StockId) . '">' . _('Search Completed Sales Orders') . '</a>';

echo '</div>
	</form>';
include('includes/footer.inc');
?>