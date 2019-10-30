<?php
include ('includes/session.php');

if (isset($_POST['PrintPDF']) and isset($_POST['FromCriteria']) and mb_strlen($_POST['FromCriteria']) >= 1 and isset($_POST['ToCriteria']) and mb_strlen($_POST['ToCriteria']) >= 1) {

	include ('includes/PDFStarter.php');

	$PDF->addInfo('Title', _('Supplier Balance Listing'));
	$PDF->addInfo('Subject', _('Supplier Balances'));

	$FontSize = 12;
	$PageNumber = 0;
	$line_height = 12;

	$SQL = "SELECT min(supplierid) AS fromcriteria,
					max(supplierid) AS tocriteria
				FROM suppliers";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	if ($_POST['FromCriteria'] == '') {
		$_POST['FromCriteria'] = $MyRow['fromcriteria'];
	}
	if ($_POST['ToCriteria'] == '') {
		$_POST['ToCriteria'] = $MyRow['tocriteria'];
	}

	/*Now figure out the aged analysis for the Supplier range under review */

	$SQL = "SELECT suppliers.supplierid,
					suppliers.suppname,
					currencies.currency,
					currencies.decimalplaces AS currdecimalplaces,
					SUM((supptrans.ovamount + supptrans.ovgst - supptrans.alloc)/supptrans.rate) AS balance,
					SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS fxbalance,
					SUM(CASE WHEN supptrans.trandate > '" . $_POST['PeriodEnd'] . "' THEN
			(supptrans.ovamount + supptrans.ovgst)/supptrans.rate ELSE 0 END) AS afterdatetrans,
					SUM(CASE WHEN supptrans.trandate > '" . $_POST['PeriodEnd'] . "'
						AND (supptrans.type=22 OR supptrans.type=21) THEN
						supptrans.diffonexch ELSE 0 END) AS afterdatediffonexch,
					SUM(CASE WHEN supptrans.trandate > '" . $_POST['PeriodEnd'] . "' THEN
						supptrans.ovamount + supptrans.ovgst ELSE 0 END) AS fxafterdatetrans
			FROM suppliers INNER JOIN currencies
			ON suppliers.currcode = currencies.currabrev
			INNER JOIN supptrans
			ON suppliers.supplierid = supptrans.supplierno
			WHERE suppliers.supplierid >= '" . $_POST['FromCriteria'] . "'
			AND suppliers.supplierid <= '" . $_POST['ToCriteria'] . "'
			GROUP BY suppliers.supplierid,
				suppliers.suppname,
				currencies.currency,
				currencies.decimalplaces";

	$SupplierResult = DB_query($SQL);

	if (DB_error_no() != 0) {
		$Title = _('Supplier Balances - Problem Report');
		include ('includes/header.php');
		prnMsg(_('The Supplier details could not be retrieved by the SQL because') . ' ' . DB_error_msg(), 'error');
		echo '<a href="', $RootPath, '/index.php">', _('Back to the menu'), '</a>';
		if ($Debug == 1) {
			echo '<br />' . $SQL;
		}
		include ('includes/footer.php');
		exit;
	}
	if (DB_num_rows($SupplierResult) == 0) {
		$Title = _('Supplier Balances - Problem Report');
		include ('includes/header.php');
		prnMsg(_('There are no supplier balances to list'), 'error');
		echo '<a href="', $RootPath, '/index.php">', _('Back to the menu'), '</a>';
		include ('includes/footer.php');
		exit;
	}

	include ('includes/PDFSupplierBalsPageHeader.php');

	$TotBal = 0;

	while ($SupplierBalances = DB_fetch_array($SupplierResult)) {

		$Balance = $SupplierBalances['balance'] - $SupplierBalances['afterdatetrans'] + $SupplierBalances['afterdatediffonexch'];
		$FXBalance = $SupplierBalances['fxbalance'] - $SupplierBalances['fxafterdatetrans'];

		if (ABS($Balance) > 0.009 or ABS($FXBalance) > 0.009) {

			$DisplayBalance = locale_number_format($SupplierBalances['balance'] - $SupplierBalances['afterdatetrans'] + $SupplierBalances['afterdatediffonexch'], $_SESSION['CompanyRecord']['decimalplaces']);
			$DisplayFXBalance = locale_number_format($SupplierBalances['fxbalance'] - $SupplierBalances['fxafterdatetrans'], $SupplierBalances['currdecimalplaces']);

			$TotBal+= $Balance;

			$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 220 - $Left_Margin, $FontSize, $SupplierBalances['supplierid'] . ' - ' . $SupplierBalances['suppname'], 'left');
			$LeftOvers = $PDF->addTextWrap(220, $YPos, 60, $FontSize, $DisplayBalance, 'right');
			$LeftOvers = $PDF->addTextWrap(280, $YPos, 60, $FontSize, $DisplayFXBalance, 'right');
			$LeftOvers = $PDF->addTextWrap(350, $YPos, 100, $FontSize, $SupplierBalances['currency'], 'left');

			$YPos-= $line_height;
			if ($YPos < $Bottom_Margin + $line_height) {
				include ('includes/PDFSupplierBalsPageHeader.php');
			}
		}
	}
	/*end Supplier aged analysis while loop */

	$YPos-= $line_height;
	if ($YPos < $Bottom_Margin + (2 * $line_height)) {
		$PageNumber++;
		include ('includes/PDFSupplierBalsPageHeader.php');
	}

	$DisplayTotBalance = locale_number_format($TotBal, $_SESSION['CompanyRecord']['decimalplaces']);

	$LeftOvers = $PDF->addTextWrap(220, $YPos, 60, $FontSize, $DisplayTotBalance, 'right');

	$PDF->OutputD($_SESSION['DatabaseName'] . '_Supplier_Balances_at_Period_End_' . Date('Y-m-d') . '.pdf');
	$PDF->__destruct();

} else {
	/*The option to print PDF was not hit */

	$Title = _('Supplier Balances At A Period End');
	include ('includes/header.php');

	$SQL = "SELECT min(supplierid) AS fromcriteria,
					max(supplierid) AS tocriteria
				FROM suppliers";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/transactions.png" title="', _('Supplier Allocations'), '" alt="" />', ' ', $Title, '
		</p>';

	if (!isset($_POST['FromCriteria'])) {
		$_POST['FromCriteria'] = $MyRow['fromcriteria'];
	}
	if (!isset($_POST['ToCriteria'])) {
		$_POST['ToCriteria'] = $MyRow['tocriteria'];
	}
	/*if $FromCriteria is not set then show a form to allow input	*/

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<fieldset>
			<legend>', _('Report Criteria'), '</legend>';

	echo '<field>
			<label for="FromCriteria">', _('From Supplier Code'), ':</label>
			<input type="text" required="required" maxlength="6" size="7" name="FromCriteria" value="', $_POST['FromCriteria'], '" />
		</field>';

	echo '<field>
			<label for="ToCriteria">', _('To Supplier Code'), ':</label>
			<input type="text" required="required" maxlength="6" size="7" name="ToCriteria" value="', $_POST['ToCriteria'], '" />
		</field>';

	$SQL = "SELECT periodno,
					lastdate_in_period
			FROM periods
			ORDER BY periodno DESC";

	$ErrMsg = _('Could not retrieve period data because');
	$Periods = DB_query($SQL, $ErrMsg);
	echo '<field>
			<label for="PeriodEnd">', _('Balances as at'), ':</label>
			<select required="required" name="PeriodEnd">';
	while ($MyRow = DB_fetch_array($Periods)) {
		echo '<option value="', $MyRow['lastdate_in_period'], '" selected="selected" >', MonthAndYearFromSQLDate($MyRow['lastdate_in_period'], 'M', -1), '</option>';
	}
	echo '</select>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="PrintPDF" value="', _('Print PDF'), '" />
		</div>';

	echo '</form>';

	include ('includes/footer.php');
}
/*end of else not PrintPDF */

?>