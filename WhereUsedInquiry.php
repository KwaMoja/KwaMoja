<?php
include ('includes/session.php');
$Title = _('Where Used Inquiry');
include ('includes/header.php');

if (isset($_GET['StockID'])) {
	$StockId = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])) {
	$StockId = trim(mb_strtoupper($_POST['StockID']));
}

echo '<div class="toplink"><a href="', $RootPath, '/SelectProduct.php">', _('Back to Items'), '</a></div>';

echo '<p class="page_title_text" >
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', _('Search'), '" alt="" />', ' ', $Title, '
	</p>';

echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

if (isset($StockId)) {
	$Result = DB_query("SELECT description,
								units,
								mbflag
						FROM stockmaster
						WHERE stockid='" . $StockId . "'");
	$MyRow = DB_fetch_row($Result);
	if (DB_num_rows($Result) == 0) {
		prnMsg(_('The item code entered') . ' - ' . $StockId . ' ' . _('is not set up as an item in the system') . '. ' . _('Re-enter a valid item code or select from the Select Item link above'), 'error');
		include ('includes/footer.php');
		exit;
	}
	echo '<fieldset>
			<legend>', $StockId, ' - ', $MyRow[0], '  (', _('in units of'), ' ', $MyRow[1], ')</legend>';
} else {
	echo '<fieldset>
			<legend>', _('Where Used Inquiry'), '</legend>';
}

if (isset($StockId)) {
	echo '<field>
			<label for="StockID">', _('Enter an Item Code'), ':</label>
			<input type="text" name="StockID" size="21" autofocus="autofocus" required="required" maxlength="20" value="', $StockId, '" />
		</field>';
} else {
	echo '<field>
			<label for="StockID">', _('Enter an Item Code') . ':</label>
			<input type="text" name="StockID" size="21" autofocus="autofocus" required="required" maxlength="20" />
		</field>';
}

echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="ShowWhereUsed" value="', _('Show Where Used'), '" />
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
				AND locationusers.userid='" . $_SESSION['UserID'] . "'
				AND locationusers.canview=1
			WHERE component='" . $StockId . "'
				AND bom.effectiveafter <= CURRENT_DATE
				AND bom.effectiveto > CURRENT_DATE";

	$ErrMsg = _('The parents for the selected part could not be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($Result) == 0) {
		prnMsg(_('The selected item') . ' ' . $StockId . ' ' . _('is not used as a component of any other parts'), 'error');
	} else {

		echo '<table width="97%">
				<thead>
					<tr>
						<th class="SortedColumn">', _('Used By'), '</th>
						<th class="SortedColumn">', _('Status'), '</th>
						<th class="SortedColumn">', _('Work Centre'), '</th>
						<th class="SortedColumn">', _('Location'), '</th>
						<th>', _('Quantity Required'), '</th>
						<th class="SortedColumn">', _('Effective After'), '</th>
						<th class="SortedColumn">', _('Effective To'), '</th>
					</tr>
				</thead>
				<tbody>';

		while ($MyRow = DB_fetch_array($Result)) {

			if ($MyRow['discontinued'] == 1) {
				$Status = _('Obsolete');
			} else {
				$Status = _('Current');
			}

			echo '<tr class="striped_row">
					<td><a target="_blank" href="', $RootPath, '/BOMInquiry.php?StockID=', urlencode($MyRow['parent']), '" alt="', _('Show Bill Of Material'), '">', $MyRow['parent'], ' - ', $MyRow['description'], '</a></td>
					<td>', $Status, '</td>
					<td>', $MyRow['workcentreadded'], '</td>
					<td>', $MyRow['loccode'], '</td>
					<td class="number">', locale_number_format($MyRow['quantity'], 'Variable'), '</td>
					<td class="date">', ConvertSQLDate($MyRow['effectiveafter']), '</td>
					<td class="date">', ConvertSQLDate($MyRow['effectiveto']), '</td>
				</tr>';

			//end of page full new headings if
			
		}

		echo '</tbody>
			</table>';
	}
} // StockID is set
echo '</form>';
include ('includes/footer.php');
?>