<?php

include('includes/session.inc');

$Title = _('All Stock Movements By Location');

include('includes/header.inc');

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

echo '<table class="selection">
	 <tr>
		 <td>  ' . _('From Stock Location') . ':<select required="required" name="StockLocation"> ';

$SQL = "SELECT locationname,
				locations.loccode
			FROM locations
			INNER JOIN locationusers
				ON locationusers.loccode=locations.loccode
				AND locationusers.userid='" .  $_SESSION['UserID'] . "'
				AND locationusers.canview=1";
echo '<option selected="selected" value="All">' . _('All Locations') . '</option>';
if (!isset($_POST['StockLocation'])) {
	$_POST['StockLocation'] = 'All';
}

$ResultStkLocs = DB_query($SQL);
while ($MyRow = DB_fetch_array($ResultStkLocs)) {
	if (isset($_POST['StockLocation']) and $_POST['StockLocation'] != 'All') {
		if ($MyRow['loccode'] == $_POST['StockLocation']) {
			echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	} else {
		echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	}
}

echo '</select>';

if (!isset($_POST['BeforeDate']) or !is_date($_POST['BeforeDate'])) {
	$_POST['BeforeDate'] = Date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['AfterDate']) or !is_date($_POST['AfterDate'])) {
	$_POST['AfterDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, Date('m') - 1, Date('d'), Date('y')));
}
echo ' ' . _('Show Movements before') . ': <input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="BeforeDate" size="12" required="required" maxlength="12" value="' . $_POST['BeforeDate'] . '" />';
echo ' ' . _('But after') . ': <input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="AfterDate" size="12" required="required" maxlength="12" value="' . $_POST['AfterDate'] . '" />';
echo '</td>
	 </tr>
	 </table>';
echo '<div class="centre">
		   <input type="submit" name="ShowMoves" value="' . _('Show Stock Movements') . '" />
	 </div>';

if ($_POST['StockLocation'] == 'All') {
	$_POST['StockLocation'] = '%%';
}

$SQLBeforeDate = FormatDateForSQL($_POST['BeforeDate']);
$SQLAfterDate = FormatDateForSQL($_POST['AfterDate']);

$SQL = "SELECT stockmoves.stockid,
				systypes.typename,
				stockmoves.type,
				stockmoves.transno,
				stockmoves.trandate,
				stockmoves.debtorno,
				stockmoves.branchcode,
				stockmoves.qty,
				stockmoves.reference,
				stockmoves.price,
				stockmoves.discountpercent,
				stockmoves.newqoh,
				stockmaster.decimalplaces
			FROM stockmoves
			INNER JOIN systypes ON stockmoves.type=systypes.typeid
			INNER JOIN stockmaster ON stockmoves.stockid=stockmaster.stockid
			WHERE  stockmoves.loccode " . LIKE . " '" . $_POST['StockLocation'] . "'
			AND stockmoves.trandate >= '" . $SQLAfterDate . "'
			AND stockmoves.trandate <= '" . $SQLBeforeDate . "'
			AND hidemovt=0
			ORDER BY stkmoveno DESC";
$ErrMsg = _('The stock movements for the selected criteria could not be retrieved because');
$MovtsResult = DB_query($SQL, $ErrMsg);

echo '<table cellpadding="5" cellspacing="4 "class="selection">
		<tr>
			<th>' . _('Item Code') . '</th>
			<th>' . _('Type') . '</th>
			<th>' . _('Trans No') . '</th>
			<th>' . _('Date') . '</th>
			<th>' . _('Customer') . '</th>
			<th>' . _('Quantity') . '</th>
			<th>' . _('Reference') . '</th>
			<th>' . _('Price') . '</th>
			<th>' . _('Discount') . '</th>
			<th>' . _('Quantity on Hand') . '</th>
		</tr>';

$k = 0; //row colour counter

while ($MyRow = DB_fetch_array($MovtsResult)) {

	if ($k == 1) {
		echo '<tr class="OddTableRows">';
		$k = 0;
	} else {
		echo '<tr class="EvenTableRows">';
		$k = 1;
	}

	$DisplayTranDate = ConvertSQLDate($MyRow['trandate']);


	printf('<td><a target="_blank" href="' . $RootPath . '/StockStatus.php?StockID=%s">%s</a></td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>', mb_strtoupper($MyRow['stockid']), mb_strtoupper($MyRow['stockid']), $MyRow['typename'], $MyRow['transno'], $DisplayTranDate, $MyRow['debtorno'], locale_number_format($MyRow['qty'], $MyRow['decimalplaces']), $MyRow['reference'], locale_number_format($MyRow['price'], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($MyRow['discountpercent'] * 100, 2), locale_number_format($MyRow['newqoh'], $MyRow['decimalplaces']));
}
//end of while loop

echo '</table>';
echo '</form>';

include('includes/footer.inc');

?>