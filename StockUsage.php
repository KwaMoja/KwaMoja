<?php
include ('includes/session.php');

$Title = _('Stock Usage');

if (isset($_GET['StockID'])) {
	$StockId = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])) {
	$StockId = trim(mb_strtoupper($_POST['StockID']));
} else {
	$StockId = '';
}

if (isset($_POST['ShowGraphUsage'])) {
	echo '<meta http-equiv="Refresh" content="0; url=', $RootPath, '/StockUsageGraph.php?StockLocation=', $_POST['StockLocation'], '&amp;StockID=', $StockId . '">';
	prnMsg(_('You should automatically be forwarded to the usage graph') . '. ' . _('If this does not happen') . ' (' . _('if the browser does not support META Refresh') . ') ' . '<a href="' . $RootPath . '/StockUsageGraph.php?StockLocation=' . urlencode($_POST['StockLocation']) . '&amp;StockID=' . urlencode($StockId) . '">' . _('click here') . '</a> ' . _('to continue'), 'info');
	exit;
}

include ('includes/header.php');

$Result = DB_query("SELECT description,
						units,
						mbflag,
						decimalplaces
					FROM stockmaster
					WHERE stockid='" . $StockId . "'");
$MyRow = DB_fetch_array($Result);

if (isset($StockId) and $StockId != '') {
	echo '<p class="page_title_text" >
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', _('Dispatch'), '" alt="" />', _('Item'), ' : ', $StockId, ' - ', $MyRow['description'], '   (', _('in units of'), ' : ', $MyRow['units'], ')
		</p>';
} else {
	echo '<p class="page_title_text" >
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', _('Dispatch'), '" alt="" />', ' ', _('Stock Usage Inquiry'), '
		</p>';
}

$DecimalPlaces = $MyRow['decimalplaces'];

echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

$ItsKitSetAssemblyOrDummy = False;
if ($MyRow['mbflag'] == 'K' or $MyRow['mbflag'] == 'A' or $MyRow['mbflag'] == 'D') {

	prnMsg(_('The selected item is a dummy or assembly or kit-set item and cannot have a stock holding') . '. ' . _('Please select a different item'), 'warn');

	$StockId = '';
}

echo '<fieldset>
		<legend>', _('Inquiry Criteria'), '</legend>';

echo '<field>
		<label for="StockID">', _('Stock Code'), ':</label>
		<input type="text" name="StockID" size="21" required="required" maxlength="20" value="', $StockId, '" />
	</field>';

echo '<field>
		<label for="StockLocation">', _('From Stock Location'), ':</label>
		<select required="required" name="StockLocation">';

$SQL = "SELECT locationname,
				locations.loccode
			FROM locations
			INNER JOIN locationusers
				ON locationusers.loccode=locations.loccode
				AND locationusers.userid='" . $_SESSION['UserID'] . "'
				AND locationusers.canview=1";
echo '<option selected="selected" value="All">', _('All Locations'), '</option>';
$ResultStkLocs = DB_query($SQL);
while ($MyRow = DB_fetch_array($ResultStkLocs)) {
	if (isset($_POST['StockLocation'])) {
		if ($MyRow['loccode'] == $_POST['StockLocation']) {
			echo '<option selected="selected" value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
		} else {
			echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
		}
	} else {
		echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
		$_POST['StockLocation'] = 'All';
	}
}
echo '</select>
	<field>
</fieldset>';

echo '<div class="centre">
		<input type="submit" name="ShowUsage" value="', _('Show Stock Usage'), '" />
		<input type="submit" name="ShowGraphUsage" value="', _('Show Graph Of Stock Usage'), '" />
	</div>';

/*HideMovt ==1 if the movement was only created for the purpose of a transaction but is not a physical movement eg. A price credit will create a movement record for the purposes of display on a credit note
 but there is no physical stock movement - it makes sense honest ??? */

$CurrentPeriod = GetPeriod(Date($_SESSION['DefaultDateFormat']));

if (isset($_POST['ShowUsage'])) {
	if ($_POST['StockLocation'] == 'All') {
		$SQL = "SELECT periods.periodno,
				periods.lastdate_in_period,
				canview,
				SUM(CASE WHEN (stockmoves.type=10 OR stockmoves.type=11 OR stockmoves.type=17 OR stockmoves.type=28 OR stockmoves.type=38)
							AND stockmoves.hidemovt=0
							AND stockmoves.stockid = '" . $StockId . "'
						THEN -stockmoves.qty ELSE 0 END) AS qtyused
				FROM periods
				LEFT JOIN stockmoves
					ON periods.periodno=stockmoves.prd
				INNER JOIN locationusers
					ON locationusers.loccode=stockmoves.loccode
					AND locationusers.userid='" . $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				WHERE periods.periodno <='" . $CurrentPeriod . "'
				GROUP BY periods.periodno,
					periods.lastdate_in_period
				ORDER BY periodno DESC LIMIT " . $_SESSION['NumberOfPeriodsOfStockUsage'];
	} else {
		$SQL = "SELECT periods.periodno,
				periods.lastdate_in_period,
				SUM(CASE WHEN (stockmoves.type=10 OR stockmoves.type=11 OR stockmoves.type=17 OR stockmoves.type=28 OR stockmoves.type=38)
								AND stockmoves.hidemovt=0
								AND stockmoves.stockid = '" . $StockId . "'
								AND stockmoves.loccode='" . $_POST['StockLocation'] . "'
							THEN -stockmoves.qty ELSE 0 END) AS qtyused
				FROM periods LEFT JOIN stockmoves
					ON periods.periodno=stockmoves.prd
				WHERE periods.periodno <='" . $CurrentPeriod . "'
				GROUP BY periods.periodno,
					periods.lastdate_in_period
				ORDER BY periodno DESC LIMIT " . $_SESSION['NumberOfPeriodsOfStockUsage'];

	}
	$MovtsResult = DB_query($SQL);
	if (DB_error_no() != 0) {
		echo _('The stock usage for the selected criteria could not be retrieved because'), ' - ', DB_error_msg();
		if ($Debug == 1) {
			echo '<br />', _('The SQL that failed was'), $SQL;
		}
		exit;
	}

	echo '<table>
			<thead>
				<tr>
					<th class="SortedColumn">', _('Month'), '</th>
					<th>', _('Usage'), '</th>
				</tr>
			</thead>';

	$TotalUsage = 0;
	$PeriodsCounter = 0;
	echo '<tbody>';
	while ($MyRow = DB_fetch_array($MovtsResult)) {

		$DisplayDate = $MyRow['periodno'] . ' - ' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']);

		$TotalUsage+= $MyRow['qtyused'];
		$PeriodsCounter++;
		echo '<tr class="striped_row">
				<td>', $DisplayDate, '</td>
				<td class="number">', locale_number_format($MyRow['qtyused'], $DecimalPlaces), '</td>
			</tr>';

		//end of page full new headings if
		
	}
	//end of while loop
	if ($TotalUsage > 0 and $PeriodsCounter > 0) {
		echo '</tbody>';
		echo '<tr class="total_row">
				<td colspan="2">', _('Average Usage per month is'), ' ', locale_number_format($TotalUsage / $PeriodsCounter), '</td>
			</tr>';
	}
	echo '</table>';
}
/* end if Show Usage is clicked */

echo '<div class="centre">
		<a href="', $RootPath, '/StockStatus.php?StockID=', urlencode($StockId), '">', _('Show Stock Status'), '</a><br />
		<a href="', $RootPath, '/StockMovements.php?StockID=', urlencode($StockId), '&amp;StockLocation=', urlencode($_POST['StockLocation']), '">', _('Show Stock Movements'), '</a><br />
		<a href="', $RootPath, '/SelectSalesOrder.php?SelectedStockItem=', urlencode($StockId), '&amp;StockLocation=', urlencode($_POST['StockLocation']), '">', _('Search Outstanding Sales Orders'), '</a><br />
		<a href="', $RootPath, '/SelectCompletedOrder.php?SelectedStockItem=', urlencode($StockId), '">', _('Search Completed Sales Orders'), '</a><br />
		<a href="', $RootPath, '/PO_SelectOSPurchOrder.php?SelectedStockItem=', urlencode($StockId), '">', _('Search Outstanding Purchase Orders'), '</a>
	</div>';

echo '</form>';
include ('includes/footer.php');

?>