<?php
include ('includes/SQL_CommonFunctions.php');
include ('includes/session.php');

$InputError = 0;
if (isset($_POST['Date']) and !is_date($_POST['Date'])) {
	$Msg = _('The date must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	$InputError = 1;
	unset($_POST['Date']);
}

if (!isset($_POST['Date'])) {

	$Title = _('Supplier Transaction Listing');
	include ('includes/header.php');

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/transactions.png" title="', $Title, '" alt="" />', ' ', _('Supplier Transaction Listing'), '
		</p>';

	if ($InputError == 1) {
		prnMsg($Msg, 'error');
	}

	echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<fieldset>
			<legend>', _('Report Criteria'), '</legend>
			<field>
				<label for="Date">', _('Enter the date for which the transactions are to be listed'), ':</label>
				<input type="text" name="Date" required="required" maxlength="10" size="10" class="date" value="', Date($_SESSION['DefaultDateFormat']), '" />
			</field>';

	echo '<field>
			<label for="TransType">', _('Transaction type'), '</label>
			<select required="required" name="TransType">
				<option value="20">', _('Invoices'), '</option>
				<option value="21">', _('Credit Notes'), '</option>
				<option value="22">', _('Payments'), '</option>
			</select>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="Go" value="', _('Create PDF'), '" />
		</div>';

	echo '</form>';

	include ('includes/footer.php');
	exit;
} else {

	include ('includes/ConnectDB.php');
}

$SQL = "SELECT type,
			supplierno,
			suppreference,
			trandate,
			ovamount,
			ovgst,
			transtext,
			currcode,
			decimalplaces AS currdecimalplaces,
			suppname
		FROM supptrans INNER JOIN suppliers
		ON supptrans.supplierno = suppliers.supplierid
		INNER JOIN currencies
		ON suppliers.currcode=currencies.currabrev
		WHERE type='" . $_POST['TransType'] . "'
		AND trandate='" . FormatDateForSQL($_POST['Date']) . "'";

$Result = DB_query($SQL, '', '', false, false);

if (DB_error_no() != 0) {
	$Title = _('Payment Listing');
	include ('includes/header.php');
	prnMsg(_('An error occurred getting the payments'), 'error');
	if ($Debug == 1) {
		prnMsg(_('The SQL used to get the receipt header information that failed was') . ':<br />' . $SQL, 'error');
	}
	include ('includes/footer.php');
	exit;
} elseif (DB_num_rows($Result) == 0) {
	$Title = _('Payment Listing');
	include ('includes/header.php');
	echo '<br />';
	prnMsg(_('There were no transactions found in the database for the date') . ' ' . $_POST['Date'] . '. ' . _('Please try again selecting a different date'), 'info');
	include ('includes/footer.php');
	exit;
}

include ('includes/PDFStarter.php');

/*PDFStarter.php has all the variables for page size and width set up depending on the users default preferences for paper size */

$PDF->addInfo('Title', _('Supplier Transaction Listing'));
$PDF->addInfo('Subject', _('Supplier transaction listing from') . '  ' . $_POST['Date']);
$line_height = 12;
$PageNumber = 1;
$TotalCheques = 0;

include ('includes/PDFSuppTransListingPageHeader.php');

while ($MyRow = DB_fetch_array($Result)) {
	$CurrDecimalPlaces = $MyRow['currdecimalplaces'];
	$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 160, $FontSize, $MyRow['suppname'], 'left');
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 162, $YPos, 80, $FontSize, $MyRow['suppreference'], 'left');
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 242, $YPos, 70, $FontSize, ConvertSQLDate($MyRow['trandate']), 'left');
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 312, $YPos, 70, $FontSize, locale_number_format($MyRow['ovamount'], $CurrDecimalPlaces), 'right');
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 382, $YPos, 70, $FontSize, locale_number_format($MyRow['ovgst'], $CurrDecimalPlaces), 'right');
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 452, $YPos, 70, $FontSize, locale_number_format($MyRow['ovamount'] + $MyRow['ovgst'], $CurrDecimalPlaces), 'right');

	$YPos-= ($line_height);
	$TotalCheques = $TotalCheques - $MyRow['ovamount'];

	if ($YPos - (2 * $line_height) < $Bottom_Margin) {
		/*Then set up a new page */
		$PageNumber++;
		include ('includes/PDFChequeListingPageHeader.php');
	}
	/*end of new page header  */
}
/* end of while there are customer receipts in the batch to print */

$YPos-= $line_height;
$LeftOvers = $PDF->addTextWrap($Left_Margin + 452, $YPos, 70, $FontSize, locale_number_format(-$TotalCheques, $CurrDecimalPlaces), 'right');
$LeftOvers = $PDF->addTextWrap($Left_Margin + 265, $YPos, 300, $FontSize, _('Total') . '  ' . _('Transactions'), 'left');

$ReportFileName = $_SESSION['DatabaseName'] . '_SuppTransListing_' . date('Y-m-d') . '.pdf';
$PDF->OutputD($ReportFileName);
$PDF->__destruct();
?>