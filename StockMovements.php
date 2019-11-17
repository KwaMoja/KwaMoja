<?php
include ('includes/session.php');
$Title = _('Stock Movements');
/* Manual links before header.php */
$ViewTopic = 'Inventory';
$BookMark = 'InventoryMovement';
include ('includes/header.php');

if (isset($_GET['StockID'])) {
	$StockId = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])) {
	$StockId = trim(mb_strtoupper($_POST['StockID']));
} else {
	$StockId = '';
}

if (isset($StockId) and $StockId != '') {
	$Result = DB_query("SELECT description, units FROM stockmaster WHERE stockid='" . $StockId . "'");
	$MyRow = DB_fetch_row($Result);
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/inventory.png" title="', _('Inventory'), '" alt="" /><b>', ' ', $StockId, ' - ', $MyRow['0'], ' : ', _('in units of'), ' : ', $MyRow[1], '</b>
		</p>';
} else {
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/inventory.png" title="', _('Inventory'), '" alt="" />', _('Stock Movements Report'), '
		</p>';
}

echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

if (!isset($_POST['BeforeDate']) or !is_date($_POST['BeforeDate'])) {
	$_POST['BeforeDate'] = Date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['AfterDate']) or !is_date($_POST['AfterDate'])) {
	$_POST['AfterDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, Date('m') - 3, Date('d'), Date('y')));
}
echo '<fieldset>
		<legend>', _('Report Criteria'), '</legend>
		<field>
			<label for="StockID">', _('Stock Code'), ':</label>
			<input type="text" name="StockID" size="21" value="', $StockId, '" required="required" maxlength="20" />
		</field>';

$SQL = "SELECT locations.loccode,
				locationname
			FROM locations
			INNER JOIN locationusers
				ON locationusers.loccode=locations.loccode
				AND locationusers.userid='" . $_SESSION['UserID'] . "'
				AND locationusers.canview=1
			ORDER BY locationname";
$ResultStkLocs = DB_query($SQL);
echo '<field>
		<label for="StockLocation">', _('From Stock Location'), ':</label>
		<select required="required" name="StockLocation"> ';

while ($MyRow = DB_fetch_array($ResultStkLocs)) {
	if (isset($_POST['StockLocation']) and $_POST['StockLocation'] != 'All') {
		if ($MyRow['loccode'] == $_POST['StockLocation']) {
			echo '<option selected="selected" value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
		} else {
			echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
		}
	} elseif ($MyRow['loccode'] == $_SESSION['UserStockLocation']) {
		echo '<option selected="selected" value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
		$_POST['StockLocation'] = $MyRow['loccode'];
	} else {
		echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
	}
}

echo '</select>
	</field>';

echo '<field>
		<label>', _('Show Movements between'), ':</label>
		<input type="text" name="AfterDate" class="date" size="12" required="required" maxlength="12" value="', $_POST['AfterDate'], '" /> ', _('and'), ':
		<input type="text" name="BeforeDate" class="date" size="12" required="required" maxlength="12" value="', $_POST['BeforeDate'], '" />
	</field>
</fieldset>';

echo '<div class="centre">
		<input type="submit" name="ShowMoves" value="', _('Show Stock Movements'), '" />
	</div>';

if (isset($StockId) and $StockId != '') {
	$SQLBeforeDate = FormatDateForSQL($_POST['BeforeDate']);
	$SQLAfterDate = FormatDateForSQL($_POST['AfterDate']);

	$SQL = "SELECT stockmoves.stockid,
				systypes.typename,
				stockmoves.stkmoveno,
				stockmoves.type,
				stockmoves.transno,
				stockmoves.trandate,
				stockmoves.userid,
				stockmoves.debtorno,
				stockmoves.branchcode,
				custbranch.brname,
				stockmoves.qty,
				stockmoves.reference,
				stockmoves.price,
				stockmoves.discountpercent,
				stockmoves.newqoh,
				stockmoves.narrative,
				stockmaster.decimalplaces,
				stockmaster.controlled,
				stockmaster.serialised
		FROM stockmoves
		INNER JOIN systypes
			ON stockmoves.type=systypes.typeid
		INNER JOIN stockmaster
			ON stockmoves.stockid=stockmaster.stockid
		LEFT JOIN custbranch
			ON stockmoves.debtorno=custbranch.debtorno
			AND stockmoves.branchcode = custbranch.branchcode
		WHERE  stockmoves.loccode='" . $_POST['StockLocation'] . "'
			AND stockmoves.trandate >= '" . $SQLAfterDate . "'
			AND stockmoves.stockid = '" . $StockId . "'
			AND stockmoves.trandate <= '" . $SQLBeforeDate . "'
			AND hidemovt=0
		ORDER BY stkmoveno DESC";
	$ErrMsg = _('The stock movements for the selected criteria could not be retrieved because') . ' - ';
	$DbgMsg = _('The SQL that failed was') . ' ';

	$MovtsResult = DB_query($SQL, $ErrMsg, $DbgMsg);
	$MyRow = DB_fetch_array($MovtsResult);

	echo '<table>
		<tr>
		<th>', _('Type'), '</th>
		<th>', _('Number'), '</th>
		<th>', _('Date'), '</th>
		<th>', _('User ID'), '</th>
		<th>', _('Customer'), '</th>
		<th>', _('Branch'), '</th>
		<th>', _('Quantity'), '</th>
		<th>', _('Reference'), '</th>
		<th>', _('Cost'), '</th>
		<th>', _('Discount'), '</th>
		<th>', _('New Qty'), '</th>
		<th>', _('Narrative'), '</th>';
	if ($MyRow['controlled'] == 1) {
		echo '<th>', _('Serial No.'), '</th>';
	}
	echo '</tr>';

	DB_data_seek($MovtsResult, 0);

	while ($MyRow = DB_fetch_array($MovtsResult)) {

		$DisplayTranDate = ConvertSQLDate($MyRow['trandate']);

		$SerialSQL = "SELECT serialno, moveqty FROM stockserialmoves WHERE stockmoveno='" . $MyRow['stkmoveno'] . "'";
		$SerialResult = DB_query($SerialSQL);

		$SerialText = '';
		while ($SerialRow = DB_fetch_array($SerialResult)) {
			if ($MyRow['serialised'] == 1) {
				$SerialText.= $SerialRow['serialno'] . '<br />';
			} else {
				$SerialText.= $SerialRow['serialno'] . ' Qty- ' . $SerialRow['moveqty'] . '<br />';
			}
		}

		if ($MyRow['type'] == 10) {
			/*its a sales invoice allow link to show invoice it was sold on*/

			echo '<tr class="striped_row">
				<td><a target="_blank" href="', $RootPath, '/PrintCustTrans.php?FromTransNo=', urlencode($MyRow['transno']), '&amp;InvOrCredit=Invoice">', $MyRow['typename'], '</a></td>
				<td>', $MyRow['transno'], '</td>
				<td>', $DisplayTranDate, '</td>
				<td>', $MyRow['userid'], '</td>
				<td>', $MyRow['debtorno'], '</td>
				<td>', $MyRow['branchcode'], ' - ', $MyRow['brname'], '</td>
				<td class="number">', locale_number_format($MyRow['qty'], $MyRow['decimalplaces']), '</td>
				<td>', $MyRow['reference'], '</td>
				<td class="number">', locale_number_format($MyRow['price'], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td class="number">', locale_number_format($MyRow['discountpercent'] * 100, 2), '%%</td>
				<td class="number">', locale_number_format($MyRow['newqoh'], $MyRow['decimalplaces']), '</td>
				<td>', $MyRow['narrative'], '</td>';
			if ($MyRow['controlled'] == 1) {
				echo '<td>', $SerialText, '</td>';
			}
			echo '</tr>';

		} elseif ($MyRow['type'] == 11) {

			echo '<tr class="striped_row">
				<td><a target="_blank" href="', $RootPath, '/PrintCustTrans.php?FromTransNo=', urlencode($MyRow['transno']), '&amp;InvOrCredit=Credit">', $MyRow['typename'], '</a></td>
				<td>', $MyRow['transno'], '</td>
				<td>', $DisplayTranDate, '</td>
				<td>', $MyRow['userid'], '</td>
				<td>', $MyRow['debtorno'], '</td>
				<td>', $MyRow['branchcode'], '</td>
				<td class="number">', locale_number_format($MyRow['qty'], $MyRow['decimalplaces']), '</td>
				<td>', $MyRow['reference'], '</td>
				<td class="number">', locale_number_format($MyRow['price'], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td class="number">', locale_number_format($MyRow['discountpercent'] * 100, 2), '%%</td>
				<td class="number">', locale_number_format($MyRow['newqoh'], $MyRow['decimalplaces']), '</td>
				<td>', $MyRow['narrative'], '</td>';
			if ($MyRow['controlled'] == 1) {
				echo '<td>', $SerialText, '</td>';
			}
			echo '</tr>';

		} else {

			echo '<tr class="striped_row">
				<td>', $MyRow['typename'], '</td>
				<td>', $MyRow['transno'], '</td>
				<td>', $DisplayTranDate, '</td>
				<td>', $MyRow['userid'], '</td>
				<td>', $MyRow['debtorno'], '</td>
				<td>', $MyRow['branchcode'], '</td>
				<td class="number">', locale_number_format($MyRow['qty'], $MyRow['decimalplaces']), '</td>
				<td>', $MyRow['reference'], '</td>
				<td class="number">', locale_number_format($MyRow['price'], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td class="number">', locale_number_format($MyRow['discountpercent'] * 100, 2), '%</td>
				<td class="number">', locale_number_format($MyRow['newqoh'], $MyRow['decimalplaces']), '</td>
				<td>', $MyRow['narrative'], '</td>';
			if ($MyRow['controlled'] == 1) {
				echo '<td>', $SerialText, '</td>';
			}
			echo '</tr>';

		}
		//end of page full new headings if
		
	}
	//end of while loop
	echo '</table>';
	echo '<div class="centre">
		<a href="', $RootPath, '/StockStatus.php?StockID=', urlencode($StockId), '">', _('Show Stock Status'), '</a><br />
		<a href="', $RootPath, '/StockUsage.php?StockID=', urlencode($StockId), '&amp;StockLocation=', urlencode($_POST['StockLocation']), '">', _('Show Stock Usage'), '</a><br />
		<a href="', $RootPath, '/SelectSalesOrder.php?SelectedStockItem=', urlencode($StockId), '&amp;StockLocation=', urlencode($_POST['StockLocation']), '">', _('Search Outstanding Sales Orders'), '</a><br />
		<a href="', $RootPath, '/SelectCompletedOrder.php?SelectedStockItem=', urlencode($StockId), '">', _('Search Completed Sales Orders'), '</a>
	</div>
</form>';
}

include ('includes/footer.php');

?>