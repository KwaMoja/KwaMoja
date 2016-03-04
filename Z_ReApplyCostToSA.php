<?php

include('includes/session.inc');
$Title = _('Apply Current Cost to Sales Analysis');
include('includes/header.inc');

echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '<br /></p>';

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

$SQL = "SELECT MonthName(lastdate_in_period) AS mnth,
				YEAR(lastdate_in_period) AS yr,
				periodno
			FROM periods";
echo '<div class="centre">' . _('Select the Period to update the costs for') . ':<select name="PeriodNo">';
$Result = DB_query($SQL);

echo '<option selected="selected" value="0">' . _('No Period Selected') . '</option>';

while ($PeriodInfo = DB_fetch_array($Result)) {

	echo '<option value="' . $PeriodInfo['periodno'] . '">' . $PeriodInfo['mnth'] . ' ' . $PeriodInfo['yr'] . '</option>';

}

echo '</select>';

echo '<input type="submit" name="UpdateSalesAnalysis" value="' . _('Update Sales Analysis Costs') . '" /></div>';
echo '</form>';

if (isset($_POST['UpdateSalesAnalysis']) and $_POST['PeriodNo'] != 0) {
	$SQL = "SELECT stockmaster.stockid,
					stockcosts.materialcost+stockcosts.overheadcost+stockcosts.labourcost AS standardcost,
					stockmaster.mbflag
				FROM salesanalysis
				INNER JOIN stockmaster
					ON salesanalysis.stockid=stockmaster.stockid
				LEFT JOIN stockcosts
					ON salesanalysis.stockid=stockcosts.stockid
				WHERE periodno='" . $_POST['PeriodNo'] . "'
					AND stockmaster.mbflag<>'D'
					AND stockcosts.succeeded=0
				GROUP BY stockmaster.stockid,
						stockcosts.materialcost,
						stockcosts.overheadcost,
						stockcosts.labourcost,
						stockmaster.mbflag";


	$ErrMsg = _('Could not retrieve the sales analysis records to be updated because');
	$Result = DB_query($SQL, $ErrMsg);

	while ($ItemsToUpdate = DB_fetch_array($Result)) {

		if ($ItemsToUpdate['mbflag'] == 'A') {
			$SQL = "SELECT SUM(stockcosts.materialcost + stockcosts.labourcost + stockcosts.overheadcost) AS standardcost
					FROM stockcosts
					INNER JOIN BOM
						ON stockcosts.stockid = bom.component
					WHERE bom.parent = '" . $ItemsToUpdate['stockid'] . "'
						AND bom.effectiveto > CURRENT_DATE
						AND bom.effectiveafter < CURRENT_DATE
						AND stockcosts.succeeded=0";

			$ErrMsg = _('Could not recalculate the current cost of the assembly item') . $ItemsToUpdate['stockid'] . ' ' . _('because');
			$AssemblyCostResult = DB_query($SQL, $ErrMsg);
			$AssemblyCost = DB_fetch_row($AssemblyCostResult);
			$Cost = $AssemblyCost[0];
		} else {
			$Cost = $ItemsToUpdate['standardcost'];
		}

		$SQL = "UPDATE salesanalysis SET cost = (qty * " . $Cost . ")
				WHERE stockid='" . $ItemsToUpdate['stockid'] . "'
				AND periodno ='" . $_POST['PeriodNo'] . "'";

		$ErrMsg = _('Could not update the sales analysis records for') . ' ' . $ItemsToUpdate['stockid'] . ' ' . _('because');
		$UpdResult = DB_query($SQL, $ErrMsg);


		prnMsg(_('Updated sales analysis for period') . ' ' . $_POST['PeriodNo'] . ' ' . _('and stock item') . ' ' . $ItemsToUpdate['stockid'] . ' ' . _('using a cost of') . ' ' . $Cost, 'success');
	}


	prnMsg(_('Updated the sales analysis cost data for period') . ' ' . $_POST['PeriodNo'], 'success');
}
include('includes/footer.inc');
?>