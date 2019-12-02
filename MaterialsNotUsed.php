<?php
/* Session started in session.php for password checking and authorisation level check
 * config.php is in turn included in session.php
*/

include ('includes/session.php');
$Title = _('Raw Materials Not Used Anywhere');
include ('includes/header.php');

$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				(stockcosts.materialcost + stockcosts.labourcost + stockcosts.overheadcost) AS stdcost,
				(SELECT SUM(quantity)
				FROM locstock
				WHERE locstock.stockid = stockmaster.stockid) AS qoh
		FROM stockmaster
		LEFT JOIN stockcosts
			ON stockcosts.stockid=stockmaster.stockid
			AND stockcosts.succeeded=0
		INNER JOIN stockcategory
			ON stockmaster.categoryid = stockcategory.categoryid
		WHERE stockcategory.stocktype = 'M'
			AND stockmaster.discontinued = 0
			AND NOT EXISTS(
				SELECT *
				FROM bom
				WHERE bom.component = stockmaster.stockid )
		ORDER BY stockmaster.stockid";
$Result = DB_query($SQL);

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/inventory.png" title="', _('Search'), '" alt="" />', ' ', _('Raw Materials Not Used in any BOM'), '
	</p>';

if (DB_num_rows($Result) != 0) {
	$TotalValue = 0;
	echo '<table>
			<tr>
				<th>', _('#'), '</th>
				<th>', _('Code'), '</th>
				<th>', _('Description'), '</th>
				<th>', _('QOH'), '</th>
				<th>', _('Std Cost'), '</th>
				<th>', _('Value'), '</th>
			</tr>';
	$i = 0;
	while ($MyRow = DB_fetch_array($Result)) {
		$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . urlencode($MyRow['stockid']) . '">' . $MyRow['stockid'] . '</a>';
		$LineValue = $MyRow['qoh'] * $MyRow['stdcost'];
		$TotalValue = $TotalValue + $LineValue;

		echo '<tr class="striped_row">
				<td class="number">', $i, '</td>
				<td>', $CodeLink, '</td>
				<td>', $MyRow['description'], '</td>
				<td class="number">', locale_number_format($MyRow['qoh'], $MyRow['decimalplaces']), '</td>
				<td class="number">', locale_number_format($MyRow['stdcost'], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td class="number">', locale_number_format($LineValue, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
			</tr>';
		++$i;
	}

	echo '<tr class="total_row">
			<td colspan="4"></td>
			<td>', _('Total'), ':</td>
			<td class="number">', locale_number_format($TotalValue, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
		</tr>';

	echo '</table>';
} else {
	prnMsg(_('There are no raw materials to show in this inquiry'), 'info');
}

include ('includes/footer.php');
?>