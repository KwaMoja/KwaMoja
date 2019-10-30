<?php
include ('includes/session.php');
include ('includes/SQL_CommonFunctions.php');

if (isset($_GET['BatchNo'])) {
	$_POST['BatchNo'] = $_GET['BatchNo'];
}

if (!isset($_POST['BatchNo'])) {
	$Title = _('Create PDF Print Out For A Batch Of Receipts');
	/* Manual links before header.php */
	$ViewTopic = 'ARReports';
	$BookMark = 'BankingSummary';
	include ('includes/header.php');

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/bank.png" title="', $Title, '" alt="', $Title, '" />', ' ', $Title, '
		</p>';

	$SQL = "SELECT DISTINCT
			transno,
			transdate
		FROM banktrans
		WHERE type=12
		ORDER BY transno DESC";
	$Result = DB_query($SQL);

	echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<fieldset>
			<legend>', _('Details of the batch to be re-printed'), '</legend>
			<field>
				<label for="BatchNo">', _('Select the batch number of receipts to be printed'), ':</label>
				<select autofocus="autofocus" required="required" name="BatchNo">';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="', $MyRow['transno'], '">', _('Batch'), ' ', $MyRow['transno'], ' - ', ConvertSqlDate($MyRow['transdate']), '</option>';
	}
	echo '</select>
		</field>
	</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="EnterBatchNo" value="', _('Create PDF'), '" />
		</div>
	</form>';

	include ('includes/footer.php');
	exit;
}

if (isset($_POST['BatchNo']) and $_POST['BatchNo'] != '') {
	$SQL = "SELECT bankaccountname,
				bankaccountnumber,
				ref,
				transdate,
				banktranstype,
				bankact,
				banktrans.exrate,
				banktrans.functionalexrate,
				banktrans.currcode,
				currencies.decimalplaces AS currdecimalplaces
			FROM bankaccounts INNER JOIN banktrans
			ON bankaccounts.accountcode=banktrans.bankact
			INNER JOIN currencies
			ON bankaccounts.currcode=currencies.currabrev
			WHERE banktrans.transno='" . $_POST['BatchNo'] . "'
			AND banktrans.type=12";

	$ErrMsg = _('An error occurred getting the header information about the receipt batch number') . ' ' . $_POST['BatchNo'];
	$DbgMsg = _('The SQL used to get the receipt header information that failed was');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

	if (DB_num_rows($Result) == 0) {
		$Title = _('Create PDF Print-out For A Batch Of Receipts');
		include ('includes/header.php');
		prnMsg(_('The receipt batch number') . ' ' . $_POST['BatchNo'] . ' ' . _('was not found in the database') . '. ' . _('Please try again selecting a different batch number'), 'warn');
		include ('includes/footer.php');
		exit;
	}
	/* OK get the row of receipt batch header info from the BankTrans table */
	$MyRow = DB_fetch_array($Result);
	$ExRate = $MyRow['exrate'];
	$FunctionalExRate = $MyRow['functionalexrate'];
	$Currency = $MyRow['currcode'];
	$BankTransType = $MyRow['banktranstype'];
	$BankedDate = $MyRow['transdate'];
	$BankActName = $MyRow['bankaccountname'];
	$BankActNumber = $MyRow['bankaccountnumber'];
	$BankingReference = $MyRow['ref'];
	$BankCurrDecimalPlaces = $MyRow['currdecimalplaces'];

	$SQL = "SELECT debtorsmaster.name,
			ovamount,
			invtext,
			reference
		FROM debtorsmaster INNER JOIN debtortrans
		ON debtorsmaster.debtorno=debtortrans.debtorno
		WHERE debtortrans.transno='" . $_POST['BatchNo'] . "'
		AND debtortrans.type=12";

	$CustRecs = DB_query($SQL, '', '', false, false);
	if (DB_error_no() != 0) {
		$Title = _('Create PDF Print-out For A Batch Of Receipts');
		include ('includes/header.php');
		prnMsg(_('An error occurred getting the customer receipts for batch number') . ' ' . $_POST['BatchNo'], 'error');
		if ($Debug == 1) {
			prnMsg(_('The SQL used to get the customer receipt information that failed was') . '<br />' . $SQL, 'error');
		}
		include ('includes/footer.php');
		exit;
	}
	$SQL = "SELECT narrative,
			amount
		FROM gltrans
		WHERE gltrans.typeno='" . $_POST['BatchNo'] . "'
		AND gltrans.type=12
		AND gltrans.account !='" . $MyRow['bankact'] . "'
		AND gltrans.account !='" . $_SESSION['CompanyRecord']['debtorsact'] . "'";

	$GLRecs = DB_query($SQL, '', '', false, false);
	if (DB_error_no() != 0) {
		$Title = _('Create PDF Print-out For A Batch Of Receipts');
		include ('includes/header.php');
		prnMsg(_('An error occurred getting the GL receipts for batch number') . ' ' . $_POST['BatchNo'], 'error');
		if ($Debug == 1) {
			prnMsg(_('The SQL used to get the GL receipt information that failed was') . ':<br />' . $SQL, 'error');
		}
		include ('includes/footer.php');
		exit;
	}

	include ('includes/PDFStarter.php');

	/*PDFStarter.php has all the variables for page size and width set up depending on the users default preferences for paper size */

	$PDF->addInfo('Title', _('Banking Summary'));
	$PDF->addInfo('Subject', _('Banking Summary Number') . ' ' . $_POST['BatchNo']);
	$line_height = 12;
	$PageNumber = 0;
	$TotalBanked = 0;

	include ('includes/PDFBankingSummaryPageHeader.php');

	while ($MyRow = DB_fetch_array($CustRecs)) {

		$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 60, $FontSize, locale_number_format(-$MyRow['ovamount'], $BankCurrDecimalPlaces), 'right');
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 65, $YPos, 150, $FontSize, $MyRow['name'], 'left');
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 215, $YPos, 100, $FontSize, $MyRow['invtext'], 'left');
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 315, $YPos, 100, $FontSize, $MyRow['reference'], 'left');

		$YPos-= ($line_height);
		$TotalBanked-= $MyRow['ovamount'];

		if ($YPos - (2 * $line_height) < $Bottom_Margin) {
			/*Then set up a new page */
			include ('includes/PDFBankingSummaryPageHeader.php');
		}
		/*end of new page header  */
	}
	/* end of while there are customer receipts in the batch to print */

	/* Right now print out the GL receipt entries in the batch */
	while ($MyRow = DB_fetch_array($GLRecs)) {

		$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 60, $FontSize, locale_number_format((-$MyRow['amount'] * $ExRate * $FunctionalExRate), $BankCurrDecimalPlaces), 'right');
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 65, $YPos, 300, $FontSize, $MyRow['narrative'], 'left');
		$YPos-= ($line_height);
		$TotalBanked+= (-$MyRow['amount'] * $ExRate);

		if ($YPos - (2 * $line_height) < $Bottom_Margin) {
			/*Then set up a new page */
			include ('includes/PDFBankingSummaryPageHeader.php');
		}
		/*end of new page header  */
	}
	/* end of while there are GL receipts in the batch to print */

	$YPos-= $line_height;
	$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 60, $FontSize, locale_number_format($TotalBanked, 2), 'right');
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 65, $YPos, 300, $FontSize, _('TOTAL') . ' ' . $Currency . ' ' . _('BANKED'), 'left');

	$PDF->OutputD($_SESSION['DatabaseName'] . '_BankingSummary_' . date('Y-m-d') . '.pdf');
	$PDF->__destruct();
}

?>