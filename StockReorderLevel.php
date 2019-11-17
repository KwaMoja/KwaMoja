<?php
include ('includes/session.php');
$Title = _('Stock Re-Order Level Maintenance');
include ('includes/header.php');

if (isset($_GET['StockID'])) {
	$StockId = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])) {
	$StockId = trim(mb_strtoupper($_POST['StockID']));
} else {
	$StockId = '';
}

echo '<div class="toplink">
		<a href="', $RootPath, '/SelectProduct.php">', _('Back to Items'), '</a>
	</div>';

echo '<p class="page_title_text" >
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/inventory.png" title="', _('Inventory'), '" alt="" /><b>', $Title, '</b>
	</p>';

$Result = DB_query("SELECT description, units FROM stockmaster WHERE stockid='" . $StockId . "'");
$MyRow = DB_fetch_row($Result);

echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

$SQL = "SELECT locstock.loccode,
				locations.locationname,
				locstock.quantity,
				locstock.reorderlevel,
				stockmaster.decimalplaces,
				locationusers.canupd
			FROM locstock
			INNER JOIN locations
				ON locstock.loccode=locations.loccode
			INNER JOIN locationusers
				ON locationusers.loccode=locstock.loccode
				AND locationusers.userid='" . $_SESSION['UserID'] . "'
				AND locationusers.canview=1
			INNER JOIN stockmaster
				ON locstock.stockid=stockmaster.stockid
			WHERE locstock.stockid = '" . $StockId . "'
			ORDER BY locations.locationname";

$ErrMsg = _('The stock held at each location cannot be retrieved because');
$DbgMsg = _('The SQL that failed was');

$LocStockResult = DB_query($SQL, $ErrMsg, $DbgMsg);

echo '<table>
		<thead>
			<tr>
				<th colspan="3">', _('Stock Code'), ':<input type="text" name="StockID" size="21" value="', $StockId, '" required="required" maxlength="20" /><input type="submit" name="Show" value="', _('Show Re-Order Levels'), '" /></th>
			</tr>
			<tr>
				<th colspan="3"><h3><b>', $StockId, ' - ', $MyRow[0], '</b>  (', _('In Units of'), ' ', $MyRow[1], ')</h3></th>
			</tr>
			<tr>
				<th class="SortedColumn">', _('Location'), '</th>
				<th>', _('Quantity On Hand'), '</th>
				<th>', _('Re-Order Level'), '</th>
			</tr>
		</thead>';

echo '<tbody>';
while ($MyRow = DB_fetch_array($LocStockResult)) {

	if (isset($_POST['UpdateData']) and $_POST['Old_' . $MyRow['loccode']] != filter_number_format($_POST[$MyRow['loccode']]) and is_numeric(filter_number_format($_POST[$MyRow['loccode']])) and filter_number_format($_POST[$MyRow['loccode']]) >= 0) {

		$MyRow['reorderlevel'] = filter_number_format($_POST[$MyRow['loccode']]);
		$SQL = "UPDATE locstock SET reorderlevel = '" . filter_number_format($_POST[$MyRow['loccode']]) . "'
	   		WHERE stockid = '" . $StockId . "'
			AND loccode = '" . $MyRow['loccode'] . "'";
		$UpdateReorderLevel = DB_query($SQL);

	}

	if ($MyRow['canupd'] == 1) {
		$UpdateCode = '<input title="' . _('Input safety stock quantity') . '" type="text" class="number" name="' . $MyRow['loccode'] . '" maxlength="10" size="10" value="' . $MyRow['reorderlevel'] . '" /><input type="hidden" name="Old_' . $MyRow['loccode'] . '" value="' . $MyRow['reorderlevel'] . '" />';
	} else {
		$UpdateCode = '<input type="hidden" name="' . $MyRow['loccode'] . '">' . $MyRow['reorderlevel'] . '<input type="hidden" name="Old_' . $MyRow['loccode'] . '" value="' . $MyRow['reorderlevel'] . '" />';
	}

	echo '<tr class="striped_row">
			<td>', $MyRow['locationname'], '</td>
			<td class="number">', locale_number_format($MyRow['quantity'], $MyRow['decimalplaces']), '</td>
			<td class="number">', $UpdateCode, '</td>
		</tr>';
	//end of page full new headings if
	
}
//end of while loop
echo '</tbody>
	</table>
	<div class="centre">
		<input type="submit" name="UpdateData" value="' . _('Update') . '" /><br />';

echo '<a href="' . $RootPath . '/StockMovements.php?StockID=' . urlencode($StockId) . '">' . _('Show Stock Movements') . '</a><br />';
echo '<a href="' . $RootPath . '/StockUsage.php?StockID=' . urlencode($StockId) . '">' . _('Show Stock Usage') . '</a><br />';
echo '<a href="' . $RootPath . '/SelectSalesOrder.php?SelectedStockItem=' . urlencode($StockId) . '">' . _('Search Outstanding Sales Orders') . '</a><br />';
echo '<a href="' . $RootPath . '/SelectCompletedOrder.php?SelectedStockItem=' . urlencode($StockId) . '">' . _('Search Completed Sales Orders') . '</a>';

echo '</div>
	</form>';
include ('includes/footer.php');
?>