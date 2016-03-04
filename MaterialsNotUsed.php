<?php

/* Session started in session.inc for password checking and authorisation level check
 * config.php is in turn included in session.inc
 */

include('includes/session.inc');
$Title = _('Raw Materials Not Used Anywhere');
include('includes/header.inc');

$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				(stockcosts.materialcost + stockcosts.labourcost + stockcosts.overheadcost) AS stdcost,
				(SELECT SUM(quantity)
				FROM locstock
				WHERE locstock.stockid = stockmaster.stockid) AS qoh
		FROM stockmaster
		LEFT JOIN stockcosts
			ON stockcosts.stockid=stockmater.stockid
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
echo '<p class="page_title_text" align="center"><strong>' . _('Raw Materials Not Used in any BOM') . '</strong></p>';
if (DB_num_rows($Result) != 0) {
	$TotalValue = 0;
	echo '<table class="selection">
			<tr>
				<th>' . _('#') . '</th>
				<th>' . _('Code') . '</th>
				<th>' . _('Description') . '</th>
				<th>' . _('QOH') . '</th>
				<th>' . _('Std Cost') . '</th>
				<th>' . _('Value') . '</th>
			</tr>';
	$k = 0; //row colour counter
	while ($MyRow = DB_fetch_array($Result)) {
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}
		$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . urlencode($MyRow['stockid']) . '">' . $MyRow['stockid'] . '</a>';
		$LineValue = $MyRow['qoh'] * $MyRow['stdcost'];
		$TotalValue = $TotalValue + $LineValue;

		printf('<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>', $i, $CodeLink, $MyRow['description'], locale_number_format($MyRow['qoh'], $MyRow['decimalplaces']), locale_number_format($MyRow['stdcost'], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($LineValue, $_SESSION['CompanyRecord']['decimalplaces']));
		++$i;
	}

	printf('<td colspan="4">%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			</tr>', '', _('Total') . ':', locale_number_format($TotalValue, $_SESSION['CompanyRecord']['decimalplaces']));

	echo '</table>';
} else {
	prnMsg(_('There are no raw materials to show in this inquiry'), 'info');
}

include('includes/footer.inc');
?>