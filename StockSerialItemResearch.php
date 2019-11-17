<?php
/* $Id$*/

include ('includes/session.php');
$Title = _('Serial Item Research');
include ('includes/header.php');

echo '<p class="page_title_text" >
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/inventory.png" title="', _('Inventory'), '" alt="" /><b>', $Title, '</b>
	</p>';

//validate the submission
if (isset($_POST['serialno'])) {
	$SerialNo = trim($_POST['serialno']);
} elseif (isset($_GET['serialno'])) {
	$SerialNo = trim($_GET['serialno']);
} else {
	$SerialNo = '';
}

echo '<form id="SerialNoResearch" method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

echo '<fieldset>
		<legend>', _('Inquiry Criteria'), '</legend>
		<field>
			<label for="serialno">', _('Serial Number'), ':</label>
			<input id="serialno" type="text" name="serialno" size="21" required="required" maxlength="20" value="', $SerialNo, '" />
		</field>
	</fieldset>';

echo '<div class="centre">
		<input type="submit" name="submit" value="', _('Search'), '" />
	</div>';

echo '</form>';

if ($SerialNo != '') {
	//the point here is to allow a semi fuzzy search, but still keep someone from killing the db server
	if (mb_strstr($SerialNo, '%')) {
		while (mb_strstr($SerialNo, '%%')) {
			$SerialNo = str_replace('%%', '%', $SerialNo);
		}
		if (mb_strlen($SerialNo) < 11) {
			$SerialNo = str_replace('%', '', $SerialNo);
			prnMsg('You can not use LIKE with short numbers. It has been removed.', 'warn');
		}
	}
	$SQL = "SELECT stockserialitems.serialno,
					stockserialitems.stockid,
					stockserialitems.quantity AS CurInvQty,
					stockserialmoves.moveqty,
					stockmoves.type,
					systypes.typename,
					stockmoves.transno,
					stockmoves.loccode,
					locations.locationname,
					stockmoves.trandate,
					stockmoves.debtorno,
					stockmoves.branchcode,
					stockmoves.reference,
					stockmoves.qty AS TotalMoveQty
				FROM stockserialitems
				INNER JOIN stockserialmoves
					ON stockserialitems.serialno = stockserialmoves.serialno
					AND stockserialitems.stockid=stockserialmoves.stockid
				INNER JOIN stockmoves
					ON stockserialmoves.stockmoveno = stockmoves.stkmoveno
					AND stockserialitems.loccode=stockmoves.loccode
				INNER JOIN systypes
					ON stockmoves.type=systypes.typeid
				INNER JOIN locations
					on stockmoves.loccode = locations.loccode
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" . $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				WHERE stockserialitems.serialno " . LIKE . " '" . $SerialNo . "'
				ORDER BY stkmoveno";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) == 0) {
		prnMsg(_('No History found for Serial Number') . ': <b>' . $SerialNo . '</b>', 'warn');
	} else {
		echo '<table>';
		echo '<tr>
				<th colspan="11"><h4>', _('Details for Serial Item'), ': <b>', $SerialNo, '</b><br />', _('Length'), '=', mb_strlen($SerialNo), '</h4></th>
			</tr>';
		echo '<tr>
				<th>', _('StockID'), '</th>
				<th>', _('CurInvQty'), '</th>
				<th>', _('Move Qty'), '</th>
				<th>', _('Move Type'), '</th>
				<th>', _('Trans #'), '</th>
				<th>', _('Location'), '</th>
				<th>', _('Date'), '</th>
				<th>', _('DebtorNo'), '</th>
				<th>', _('Branch'), '</th>
				<th>', _('Move Ref'), '</th>
				<th>', _('Total Move Qty'), '</th>
			</tr>';
		while ($MyRow = DB_fetch_row($Result)) {
			echo '<tr class="striped_row">
					<td>', $MyRow[1], '<br />', $MyRow[0], '</td>
					<td class="number">', $MyRow[2], '</td>
					<td class="number">', $MyRow[3], '</td>
					<td>', $MyRow[5], ' (', $MyRow[4], ')</td>
					<td class="number">', $MyRow[6], '</td>
					<td>', $MyRow[7], ' - ', $MyRow[8], '</td>
					<td>', $MyRow[9], ' &nbsp;</td>
					<td>', $MyRow[10], ' &nbsp;</td>
					<td>', $MyRow[11], ' &nbsp;</td>
					<td>', $MyRow[12], ' &nbsp;</td>
					<td class="number">', $MyRow[13], '</td>
				</tr>';
		} //END WHILE LIST LOOP
		echo '</table>';
	} // ELSE THERE WHERE ROWS
	
} //END OF POST IS SET
include ('includes/footer.php');
?>