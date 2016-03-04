<?php
/* Session started in session.inc for password checking and authorisation level check
config.php is in turn included in session.inc*/
include('includes/session.inc');
$Title = _('List of Items without picture');
include('includes/header.inc');
$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				stockcategory.categorydescription
			FROM stockmaster
			INNER JOIN stockcategory
				ON stockmaster.categoryid = stockcategory.categoryid
			WHERE stockmaster.discontinued = 0
				AND stockcategory.stocktype != 'D'
			ORDER BY stockcategory.categorydescription,
					stockmaster.stockid";
$Result = DB_query($SQL);
$PrintHeader = TRUE;
if (DB_num_rows($Result) != 0) {
	echo '<p class="page_title_text"  align="center"><strong>' . _('Current Items without picture') . '</strong></p>';
	echo '<table class="selection">';
	$k = 0; //row colour counter
	$i = 1;
	while ($MyRow = DB_fetch_array($Result)) {
		if (!file_exists($_SESSION['part_pics_dir'] . '/' . $MyRow['stockid'] . '.jpg')) {
			if ($PrintHeader) {
				echo '<tr>
									<th>' . '#' . '</th>
									<th>' . _('Category') . '</th>
									<th>' . _('Item Code') . '</th>
									<th>' . _('Description') . '</th>
								</tr>';
				$PrintHeader = FALSE;
			}
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . urlencode($MyRow['stockid']) . '">' . $MyRow['stockid'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
				</tr>', $i, $MyRow['categorydescription'], $CodeLink, $MyRow['description']);
			++$i;
		}
	}
	echo '</table>';
}
include('includes/footer.inc');
?>