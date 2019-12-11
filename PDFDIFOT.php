<?php
include ('includes/session.php');
include ('includes/SQL_CommonFunctions.php');

$InputError = 0;

if (isset($_POST['FromDate']) and !is_date($_POST['FromDate'])) {
	$Msg = _('The date from must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	$InputError = 1;
}
if (isset($_POST['ToDate']) and !is_date($_POST['ToDate'])) {
	$Msg = _('The date to must be specified in the format') . ' ' . $_SESSION['DefaultDateFormat'];
	$InputError = 1;
}

if (!isset($_POST['FromDate']) or !isset($_POST['ToDate']) or $InputError == 1) {

	$Title = _('Delivery In Full On Time (DIFOT) Report');
	include ('includes/header.php');

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/transactions.png" title="', $Title, '" alt="" />', ' ', _('DIFOT Report'), '
		</p>';

	echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<fieldset>
			<legend>', _('Report Criteria'), '</legend>';

	echo '<field>
			<label for="FromDate">', _('From Date'), ':</label>
			<input type="text" class="date" name="FromDate" autofocus="autofocus" required="required" maxlength="10" size="10" value="', Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, Date('m') - 1, 0, Date('y'))), '" />
			<fieldhelp>', _('Enter the date from which variances between orders and deliveries are to be listed'), '</fieldhelp>
		</field>';

	echo '<field>
			<label for="ToDate">', _('To Date'), ':</label>
			<input type="text" class="date" name="ToDate" required="required" maxlength="10" size="10" value="', Date($_SESSION['DefaultDateFormat']), '" />
			<fieldhelp>', _('Enter the date to which variances between orders and deliveries are to be listed'), '</fieldhelp>
		</field>';

	if (!isset($_POST['DaysAcceptable'])) {
		$_POST['DaysAcceptable'] = 1;
	}

	echo '<field>
			<label for="DaysAcceptable">', _('Accpetable wait in days'), ':</label>
			<input type="text" class="number" name="DaysAcceptable" required="required" maxlength="2" size="2" value="', $_POST['DaysAcceptable'], '" />
			<fieldhelp>', _('Enter the number of days considered acceptable between delivery requested date and invoice date(ie the date dispatched)'), '</fieldhelp>
		</field>';

	echo '<field>
			<label for="CategoryID">', _('Inventory Category'), '</label>
			<select required="required" name="CategoryID">
				<option selected="selected" value="All">', _('Over All Categories'), '</option>';

	$SQL = "SELECT categorydescription, categoryid FROM stockcategory WHERE stocktype<>'D' AND stocktype<>'L'";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="', $MyRow['categoryid'], '">', $MyRow['categorydescription'], '</option>';
	}
	echo '</select>
		</field>';

	$SQL = "SELECT locations.loccode,
					locationname
				FROM locations
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" . $_SESSION['UserID'] . "'
					AND locationusers.canview=1";
	$Result = DB_query($SQL);

	echo '<field>
			<label for="Location">', _('Inventory Location'), ':</label>
			<select required="required" name="Location">';
	echo '<option selected="selected" value="All">', _('All Locations'), '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="Email">', _('Email the report'), ':</label>
			<select required="required" name="Email">
				<option selected="selected" value="No">', _('No'), '</option>
				<option value="Yes">', _('Yes'), '</option>
			</select>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="Go" value="', _('Create PDF'), '" />
		</div>';

	echo '</form>';

	if ($InputError == 1) {
		prnMsg($Msg, 'error');
	}
	include ('includes/footer.php');
	exit;
} else {
	include ('includes/ConnectDB.php');
}

if ($_POST['CategoryID'] == 'All' and $_POST['Location'] == 'All') {
	$SQL = "SELECT salesorders.orderno,
				salesorders.deliverydate,
				salesorderdetails.actualdispatchdate,
				TO_DAYS(salesorderdetails.actualdispatchdate) - TO_DAYS(salesorders.deliverydate) AS daydiff,
				salesorderdetails.quantity,
				salesorderdetails.stkcode,
				stockmaster.description,
				stockmaster.decimalplaces,
				salesorders.debtorno,
				salesorders.branchcode
			FROM salesorderdetails
			INNER JOIN stockmaster
				ON salesorderdetails.stkcode=stockmaster.stockid
			INNER JOIN salesorders
				ON salesorderdetails.orderno=salesorders.orderno
			INNER JOIN locationusers
				ON locationusers.loccode=salesorders.fromstkloc
				AND locationusers.userid='" . $_SESSION['UserID'] . "'
				AND locationusers.canview=1
			WHERE salesorders.deliverydate >='" . FormatDateForSQL($_POST['FromDate']) . "'
				AND salesorders.deliverydate <='" . FormatDateForSQL($_POST['ToDate']) . "'
				AND (TO_DAYS(salesorderdetails.actualdispatchdate) - TO_DAYS(salesorders.deliverydate)) >='" . filter_number_format($_POST['DaysAcceptable']) . "'";

} elseif ($_POST['CategoryID'] != 'All' and $_POST['Location'] == 'All') {
	$SQL = "SELECT salesorders.orderno,
				salesorders.deliverydate,
				salesorderdetails.actualdispatchdate,
				TO_DAYS(salesorderdetails.actualdispatchdate) - TO_DAYS(salesorders.deliverydate) AS daydiff,
				salesorderdetails.quantity,
				salesorderdetails.stkcode,
				stockmaster.description,
				stockmaster.decimalplaces,
				salesorders.debtorno,
				salesorders.branchcode
			FROM salesorderdetails
			INNER JOIN stockmaster
				ON salesorderdetails.stkcode=stockmaster.stockid
			INNER JOIN salesorders
				ON salesorderdetails.orderno=salesorders.orderno
			INNER JOIN locationusers
				ON locationusers.loccode=salesorders.fromstkloc
				AND locationusers.userid='" . $_SESSION['UserID'] . "'
				AND locationusers.canview=1
			WHERE salesorders.deliverydate >='" . FormatDateForSQL($_POST['FromDate']) . "'
				AND salesorders.deliverydate <='" . FormatDateForSQL($_POST['ToDate']) . "'
				AND stockmaster.categoryid='" . $_POST['CategoryID'] . "'
				AND (TO_DAYS(salesorderdetails.actualdispatchdate)
					- TO_DAYS(salesorders.deliverydate)) >='" . filter_number_format($_POST['DaysAcceptable']) . "'";

} elseif ($_POST['CategoryID'] == 'All' and $_POST['Location'] != 'All') {

	$SQL = "SELECT salesorders.orderno,
					salesorders.deliverydate,
					salesorderdetails.actualdispatchdate,
					TO_DAYS(salesorderdetails.actualdispatchdate) - TO_DAYS(salesorders.deliverydate) AS daydiff,
					salesorderdetails.quantity,
					salesorderdetails.stkcode,
					stockmaster.description,
					stockmaster.decimalplaces,
					salesorders.debtorno,
					salesorders.branchcode
				FROM salesorderdetails
				INNER JOIN stockmaster
					ON salesorderdetails.stkcode=stockmaster.stockid
				INNER JOIN salesorders
					ON salesorderdetails.orderno=salesorders.orderno
				INNER JOIN locationusers
					ON locationusers.loccode=salesorders.fromstkloc
					AND locationusers.userid='" . $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				WHERE salesorders.deliverydate >='" . FormatDateForSQL($_POST['FromDate']) . "'
					AND salesorders.deliverydate <='" . FormatDateForSQL($_POST['ToDate']) . "'
					AND salesorders.fromstkloc='" . $_POST['Location'] . "'
					AND (TO_DAYS(salesorderdetails.actualdispatchdate)
								- TO_DAYS(salesorders.deliverydate)) >='" . filter_number_format($_POST['DaysAcceptable']) . "'";

} elseif ($_POST['CategoryID'] != 'All' and $_POST['Location'] != 'All') {

	$SQL = "SELECT salesorders.orderno,
					salesorders.deliverydate,
					salesorderdetails.actualdispatchdate,
					TO_DAYS(salesorderdetails.actualdispatchdate) - TO_DAYS(salesorders.deliverydate) AS daydiff,
					salesorderdetails.quantity,
					salesorderdetails.stkcode,
					stockmaster.description,
					stockmaster.decimalplaces,
					salesorders.debtorno,
					salesorders.branchcode
				FROM salesorderdetails
				INNER JOIN stockmaster
					ON salesorderdetails.stkcode=stockmaster.stockid
				INNER JOIN salesorders
					ON salesorderdetails.orderno=salesorders.orderno
				INNER JOIN locationusers
					ON locationusers.loccode=salesorders.fromstkloc
					AND locationusers.userid='" . $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				WHERE salesorders.deliverydate >='" . FormatDateForSQL($_POST['FromDate']) . "'
					AND salesorders.deliverydate <='" . FormatDateForSQL($_POST['ToDate']) . "'
					AND stockmaster.categoryid='" . $_POST['CategoryID'] . "'
					AND salesorders.fromstkloc='" . $_POST['Location'] . "'
					AND (TO_DAYS(salesorderdetails.actualdispatchdate)
						- TO_DAYS(salesorders.deliverydate)) >='" . filter_number_format($_POST['DaysAcceptable']) . "'";

}

$Result = DB_query($SQL, '', '', false, false); //dont error check - see below
if (DB_error_no() != 0) {
	$Title = _('DIFOT Report Error');
	include ('includes/header.php');
	prnMsg(_('An error occurred getting the days between delivery requested and actual invoice'), 'error');
	if ($Debug == 1) {
		prnMsg(_('The SQL used to get the days between requested delivery and actual invoice dates was') . "<br />$SQL", 'error');
	}
	include ('includes/footer.php');
	exit;
} elseif (DB_num_rows($Result) == 0) {
	$Title = _('DIFOT Report Error');
	include ('includes/header.php');
	prnMsg(_('There were no variances between deliveries and orders found in the database within the period from') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate'] . '. ' . _('Please try again selecting a different date range'), 'info');
	if ($Debug == 1) {
		prnMsg(_('The SQL that returned no rows was') . '<br />' . $SQL, 'error');
	}
	include ('includes/footer.php');
	exit;
}

include ('includes/PDFStarter.php');

/*PDFStarter.php has all the variables for page size and width set up depending on the users default preferences for paper size */

$PDF->addInfo('Title', _('Dispatches After') . $_POST['DaysAcceptable'] . ' ' . _('Day(s) from Requested Delivery Date'));
$PDF->addInfo('Subject', _('Delivery Dates from') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate']);
$line_height = 12;
$PageNumber = 1;
$TotalDiffs = 0;

include ('includes/PDFDIFOTPageHeader.php');

while ($MyRow = DB_fetch_array($Result)) {

	if (DayOfWeekFromSQLDate($MyRow['actualdispatchdate']) == 1) {
		$DaysDiff = $MyRow['daydiff'] - 2;
	} else {
		$DaysDiff = $MyRow['daydiff'];
	}
	if ($DaysDiff > $_POST['DaysAcceptable']) {
		$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 40, $FontSize, $MyRow['orderno'], 'left');
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 40, $YPos, 200, $FontSize, $MyRow['stkcode'] . ' - ' . $MyRow['description'], 'left');
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 240, $YPos, 50, $FontSize, locale_number_format($MyRow['quantity'], $MyRow['decimalplaces']), 'right');
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 295, $YPos, 50, $FontSize, $MyRow['debtorno'], 'left');
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 345, $YPos, 50, $FontSize, $MyRow['branchcode'], 'left');
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 395, $YPos, 50, $FontSize, ConvertSQLDate($MyRow['actualdispatchdate']), 'left');
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 445, $YPos, 20, $FontSize, $DaysDiff, 'left');

		$YPos-= ($line_height);
		$TotalDiffs++;

		if ($YPos - (2 * $line_height) < $Bottom_Margin) {
			/*Then set up a new page */
			$PageNumber++;
			include ('includes/PDFDIFOTPageHeader.php');
		}
		/*end of new page header  */
	}
}
/* end of while there are delivery differences to print */

$YPos-= $line_height;
$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 200, $FontSize, _('Total number of differences') . ' ' . locale_number_format($TotalDiffs), 'left');

if ($_POST['CategoryID'] == 'All' and $_POST['Location'] == 'All') {
	$SQL = "SELECT COUNT(salesorderdetails.orderno)
			FROM salesorderdetails
			INNER JOIN debtortrans
				ON salesorderdetails.orderno=debtortrans.order_
			INNER JOIN salesorders
				ON salesorderdetails.orderno = salesorders.orderno
			INNER JOIN locationusers
				ON locationusers.loccode=salesorders.fromstkloc
				AND locationusers.userid='" . $_SESSION['UserID'] . "'
				AND locationusers.canview=1
			WHERE debtortrans.trandate>='" . FormatDateForSQL($_POST['FromDate']) . "'
				AND debtortrans.trandate <='" . FormatDateForSQL($_POST['ToDate']) . "'";

} elseif ($_POST['CategoryID'] != 'All' and $_POST['Location'] == 'All') {
	$SQL = "SELECT COUNT(salesorderdetails.orderno)
		FROM salesorderdetails
		INNER JOIN debtortrans
			ON salesorderdetails.orderno=debtortrans.order_
		INNER JOIN stockmaster
			ON salesorderdetails.stkcode=stockmaster.stockid
		INNER JOIN salesorders
			ON salesorderdetails.orderno = salesorders.orderno
		INNER JOIN locationusers
			ON locationusers.loccode=salesorders.fromstkloc
			AND locationusers.userid='" . $_SESSION['UserID'] . "'
			AND locationusers.canview=1
		WHERE debtortrans.trandate>='" . FormatDateForSQL($_POST['FromDate']) . "'
			AND debtortrans.trandate <='" . FormatDateForSQL($_POST['ToDate']) . "'
			AND stockmaster.categoryid='" . $_POST['CategoryID'] . "'";

} elseif ($_POST['CategoryID'] == 'All' and $_POST['Location'] != 'All') {

	$SQL = "SELECT COUNT(salesorderdetails.orderno)
		FROM salesorderdetails
		INNER JOIN debtortrans
			ON salesorderdetails.orderno=debtortrans.order_
		INNER JOIN salesorders
			ON salesorderdetails.orderno = salesorders.orderno
		INNER JOIN locationusers
			ON locationusers.loccode=salesorders.fromstkloc
			AND locationusers.userid='" . $_SESSION['UserID'] . "'
			AND locationusers.canview=1
		WHERE debtortrans.trandate>='" . FormatDateForSQL($_POST['FromDate']) . "'
			AND debtortrans.trandate <='" . FormatDateForSQL($_POST['ToDate']) . "'
			AND salesorders.fromstkloc='" . $_POST['Location'] . "'";

} elseif ($_POST['CategoryID'] != 'All' and $_POST['Location'] != 'All') {

	$SQL = "SELECT COUNT(salesorderdetails.orderno)
		FROM salesorderdetails
		INNER JOIN debtortrans
			ON salesorderdetails.orderno=debtortrans.order_
		INNER JOIN salesorders
			ON salesorderdetails.orderno = salesorders.orderno
		INNER JOIN locationusers
			ON locationusers.loccode=salesorders.fromstkloc
				AND locationusers.userid='" . $_SESSION['UserID'] . "'
				AND locationusers.canview=1
		INNER JOIN stockmaster
			ON salesorderdetails.stkcode = stockmaster.stockid
		WHERE salesorders.fromstkloc ='" . $_POST['Location'] . "'
			AND categoryid='" . $_POST['CategoryID'] . "'
			AND trandate >='" . FormatDateForSQL($_POST['FromDate']) . "'
			AND trandate <= '" . FormatDateForSQL($_POST['ToDate']) . "'";

}
$ErrMsg = _('Could not retrieve the count of sales order lines in the period under review');
$Result = DB_query($SQL, $ErrMsg);

$MyRow = DB_fetch_row($Result);
$YPos-= $line_height;
$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 200, $FontSize, _('Total number of order lines') . ' ' . locale_number_format($MyRow[0]), 'left');

$YPos-= $line_height;
$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 200, $FontSize, _('DIFOT') . ' ' . locale_number_format((1 - ($TotalDiffs / $MyRow[0])) * 100, 2) . '%', 'left');

$ReportFileName = $_SESSION['DatabaseName'] . '_DIFOT_' . date('Y-m-d') . '.pdf';
$PDF->OutputD($ReportFileName);
if ($_POST['Email'] == 'Yes') {
	$PDF->Output($_SESSION['reports_dir'] . '/' . $ReportFileName, 'F');
	include ('includes/htmlMimeMail.php');
	$Mail = new htmlMimeMail();
	$attachment = $Mail->getFile($_SESSION['reports_dir'] . '/' . $ReportFileName);
	$Mail->setText(_('Please find herewith DIFOT report from') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate']);
	$Mail->addAttachment($attachment, 'DIFOT.pdf', 'application/pdf');
	$Mail->setFrom($_SESSION['CompanyRecord']['coyname'] . '<' . $_SESSION['CompanyRecord']['email'] . '>');

	if ($_SESSION['SmtpSetting'] == 0) {
		$Mail->setFrom($_SESSION['CompanyRecord']['coyname'] . ' <' . $_SESSION['CompanyRecord']['email'] . '>');
		$Result = $Mail->send(array($_SESSION['FactoryManagerEmail']));
	} else {
		$Result = SendmailBySmtp($Mail, array($_SESSION['FactoryManagerEmail']));
	}
}

?>