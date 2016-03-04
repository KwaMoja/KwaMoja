<?php

include('includes/session.inc');
$Title = _('Where Used Inquiry');
include('includes/header.inc');

if (isset($_GET['StockID'])) {
	$StockId = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])) {
	$StockId = trim(mb_strtoupper($_POST['StockID']));
}

echo '<div class="toplink"><a href="' . $RootPath . '/SelectProduct.php">' . _('Back to Items') . '</a></div>
	<p class="page_title_text" >
		<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '
	</p>';
if (isset($StockId)) {
	$Result = DB_query("SELECT description,
								units,
								mbflag
						FROM stockmaster
						WHERE stockid='" . $StockId . "'");
	$MyRow = DB_fetch_row($Result);
	if (DB_num_rows($Result) == 0) {
		prnMsg(_('The item code entered') . ' - ' . $StockId . ' ' . _('is not set up as an item in the system') . '. ' . _('Re-enter a valid item code or select from the Select Item link above'), 'error');
		include('includes/footer.inc');
		exit;
	}
	echo '<br />
		<div class="centre"><h3>' . $StockId . ' - ' . $MyRow[0] . '  (' . _('in units of') . ' ' . $MyRow[1] . ')</h3></div>';
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">
	<div class="centre">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($StockId)) {
	echo _('Enter an Item Code') . ': <input type="text" name="StockID" size="21" autofocus="autofocus" required="required" maxlength="20" value="' . $StockId . '" />';
} else {
	echo _('Enter an Item Code') . ': <input type="text" name="StockID" size="21" autofocus="autofocus" required="required" maxlength="20" />';
}

echo '<input type="submit" name="ShowWhereUsed" value="' . _('Show Where Used') . '" />';

echo '<br />
	  </div>';

if (isset($StockId)) {

	$SQL = "SELECT bom.*,
				stockmaster.description,
				stockmaster.discontinued
			FROM bom
			INNER JOIN stockmaster
				ON bom.parent = stockmaster.stockid
			INNER JOIN locationusers
				ON locationusers.loccode=bom.loccode
				AND locationusers.userid='" .  $_SESSION['UserID'] . "'
				AND locationusers.canview=1
			WHERE component='" . $StockId . "'
				AND bom.effectiveafter <= CURRENT_DATE
				AND bom.effectiveto > CURRENT_DATE";

	$ErrMsg = _('The parents for the selected part could not be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($Result) == 0) {
		prnMsg(_('The selected item') . ' ' . $StockId . ' ' . _('is not used as a component of any other parts'), 'error');
	} else {

		echo '<table width="97%" class="selection">
				<tr>
					<th>' . _('Used By') . '</th>
					<th>' . _('Status') . '</th>
					<th>' . _('Work Centre') . '</th>
					<th>' . _('Location') . '</th>
					<th>' . _('Quantity Required') . '</th>
					<th>' . _('Effective After') . '</th>
					<th>' . _('Effective To') . '</th>
				</tr>';
		$k = 0;
		while ($MyRow = DB_fetch_array($Result)) {

			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}

			if ($MyRow['discontinued'] == 1){
				$Status = _('Obsolete');
			}else{
				$Status = _('Current');
			}

			echo '<td><a target="_blank" href="' . $RootPath . '/BOMInquiry.php?StockID=' . $MyRow['parent'] . '" alt="' . _('Show Bill Of Material') . '">' . $MyRow['parent'] . ' - ' . $MyRow['description'] . '</a></td>
				<td>' . $MyRow['workcentreadded'] . '</td>
				<td>' . $MyRow['loccode'] . '</td>
				<td class="number">' . locale_number_format($MyRow['quantity'], 'Variable') . '</td>
				<td>' . ConvertSQLDate($MyRow['effectiveafter']) . '</td>
				<td>' . ConvertSQLDate($MyRow['effectiveto']) . '</td>
			</tr>';

			//end of page full new headings if
		}

		echo '</table>';
	}
} // StockID is set

echo '</form>';
include('includes/footer.inc');
?>