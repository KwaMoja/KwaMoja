<?php

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');

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

	$Title = _('Delivery Differences Report');
	include('includes/header.inc');

	echo '<div class="centre"><p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/transactions.png" title="' . $Title . '" alt="" />' . ' ' . _('Delivery Differences Report') . '</p></div>';

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">
			<tr>
				<td>' . _('Enter the date from which variances between orders and deliveries are to be listed') . ':</td>
				<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" autofocus="autofocus" required="required" maxlength="10" size="10" value="' . Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, Date('m') - 1, 0, Date('y'))) . '" /></td>
			</tr>';
	echo '<tr>
			<td>' . _('Enter the date to which variances between orders and deliveries are to be listed') . ':</td>
			<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '"  name="ToDate" required="required" maxlength="10" size="10" value="' . Date($_SESSION['DefaultDateFormat']) . '" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('Inventory Category') . '</td>
			<td>';

	$SQL = "SELECT categorydescription,
					categoryid
			FROM stockcategory
			WHERE stocktype<>'D'
			AND stocktype<>'L'";

	$Result = DB_query($SQL);


	echo '<select required="required" name="CategoryID">
			<option selected="selected" value="All">' . _('Over All Categories') . '</option>';

	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
	}

	echo '</select></td>
		</tr>';

	echo '<tr>
			<td>' . _('Inventory Location') . ':</td>
			<td><select required="required" name="Location">';

	$SQL = "SELECT locations.loccode,
					locationname
				FROM locations
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canview=1";
	echo '<option selected="selected" value="All">' . _('All Locations') . '</option>';

	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('Email the report off') . ':</td>
			<td>
				<select required="required" name="Email">
					<option selected="selected" value="No">' . _('No') . '</option>
					<option value="Yes">' . _('Yes') . '</option>
				</select>
			</td>
		</tr>
		</table>
		<div class="centre">
			<input type="submit" name="Go" value="' . _('Create PDF') . '" />
		</div>';
	echo '</form>';

	if ($InputError == 1) {
		prnMsg($Msg, 'error');
	}
	include('includes/footer.inc');
	exit;
} else {
	include('includes/ConnectDB.inc');
}

if ($_POST['CategoryID'] == 'All' and $_POST['Location'] == 'All') {
	$SQL = "SELECT invoiceno,
					orderdeliverydifferenceslog.orderno,
					orderdeliverydifferenceslog.stockid,
					stockmaster.description,
					stockmaster.decimalplaces,
					quantitydiff,
					trandate,
					orderdeliverydifferenceslog.debtorno,
					orderdeliverydifferenceslog.branch
				FROM orderdeliverydifferenceslog
				INNER JOIN stockmaster
					ON orderdeliverydifferenceslog.stockid=stockmaster.stockid
				INNER JOIN salesorders
					ON orderdeliverydifferenceslog.orderno = salesorders.orderno
				INNER JOIN locationusers
					ON locationusers.loccode=salesorders.fromstkloc
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				INNER JOIN debtortrans
					ON orderdeliverydifferenceslog.invoiceno=debtortrans.transno
				WHERE debtortrans.type=10
					AND trandate >='" . FormatDateForSQL($_POST['FromDate']) . "'
					AND trandate <='" . FormatDateForSQL($_POST['ToDate']) . "'";

} elseif ($_POST['CategoryID'] != 'All' and $_POST['Location'] == 'All') {
	$SQL = "SELECT invoiceno,
					orderdeliverydifferenceslog.orderno,
					orderdeliverydifferenceslog.stockid,
					stockmaster.description,
					stockmaster.decimalplaces,
					quantitydiff,
					trandate,
					orderdeliverydifferenceslog.debtorno,
					orderdeliverydifferenceslog.branch
				FROM orderdeliverydifferenceslog
				INNER JOIN stockmaster
					ON orderdeliverydifferenceslog.stockid=stockmaster.stockid
				INNER JOIN salesorders
					ON orderdeliverydifferenceslog.orderno = salesorders.orderno
				INNER JOIN locationusers
					ON locationusers.loccode=salesorders.fromstkloc
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				INNER JOIN debtortrans
					ON orderdeliverydifferenceslog.invoiceno=debtortrans.transno
				WHERE debtortrans.type=10
					AND trandate >='" . FormatDateForSQL($_POST['FromDate']) . "'
					AND trandate <='" . FormatDateForSQL($_POST['ToDate']) . "'
					AND categoryid='" . $_POST['CategoryID'] . "'";

} elseif ($_POST['CategoryID'] == 'All' and $_POST['Location'] != 'All') {
	$SQL = "SELECT invoiceno,
					orderdeliverydifferenceslog.orderno,
					orderdeliverydifferenceslog.stockid,
					stockmaster.description,
					stockmaster.decimalplaces,
					quantitydiff,
					trandate,
					orderdeliverydifferenceslog.debtorno,
					orderdeliverydifferenceslog.branch
				FROM orderdeliverydifferenceslog
				INNER JOIN stockmaster
					ON orderdeliverydifferenceslog.stockid=stockmaster.stockid
				INNER JOIN debtortrans
					ON orderdeliverydifferenceslog.invoiceno=debtortrans.transno
				INNER JOIN salesorders
					ON orderdeliverydifferenceslog.orderno=salesorders.orderno
				INNER JOIN locationusers
					ON locationusers.loccode=salesorders.fromstkloc
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				WHERE debtortrans.type=10
					AND salesorders.fromstkloc='" . $_POST['Location'] . "'
					AND trandate >='" . FormatDateForSQL($_POST['FromDate']) . "'
					AND trandate <='" . FormatDateForSQL($_POST['ToDate']) . "'";

} elseif ($_POST['CategoryID'] != 'All' and $_POST['location'] != 'All') {

	$SQL = "SELECT invoiceno,
					orderdeliverydifferenceslog.orderno,
					orderdeliverydifferenceslog.stockid,
					stockmaster.description,
					stockmaster.decimalplaces,
					quantitydiff,
					trandate,
					orderdeliverydifferenceslog.debtorno,
					orderdeliverydifferenceslog.branch
				FROM orderdeliverydifferenceslog
				INNER JOIN stockmaster
					ON orderdeliverydifferenceslog.stockid=stockmaster.stockid
				INNER JOIN debtortrans
					ON orderdeliverydifferenceslog.invoiceno=debtortrans.transno
					AND debtortrans.type=10
				INNER JOIN salesorders
					ON orderdeliverydifferenceslog.orderno = salesorders.orderno
				INNER JOIN locationusers
					ON locationusers.loccode=salesorders.fromstkloc
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				WHERE salesorders.fromstkloc='" . $_POST['Location'] . "'
					AND categoryid='" . $_POST['CategoryID'] . "'
					AND trandate >='" . FormatDateForSQL($_POST['FromDate']) . "'
					AND trandate <= '" . FormatDateForSQL($_POST['ToDate']) . "'";
}

if ($_SESSION['SalesmanLogin'] != '') {
	$SQL .= " AND debtortrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
}

$Result = DB_query($SQL, '', '', false, false); //dont error check - see below

if (DB_error_no() != 0) {
	$Title = _('Delivery Differences Log Report Error');
	include('includes/header.inc');
	prnMsg(_('An error occurred getting the variances between deliveries and orders'), 'error');
	if ($Debug == 1) {
		prnMsg(_('The SQL used to get the variances between deliveries and orders that failed was') . '<br />' . $SQL, 'error');
	}
	include('includes/footer.inc');
	exit;
} elseif (DB_num_rows($Result) == 0) {
	$Title = _('Delivery Differences Log Report Error');
	include('includes/header.inc');
	prnMsg(_('There were no variances between deliveries and orders found in the database within the period from') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate'] . '. ' . _('Please try again selecting a different date range'), 'info');
	if ($Debug == 1) {
		prnMsg(_('The SQL that returned no rows was') . '<br />' . $SQL, 'error');
	}
	include('includes/footer.inc');
	exit;
}

include('includes/PDFStarter.php');

/*PDFStarter.php has all the variables for page size and width set up depending on the users default preferences for paper size */

$PDF->addInfo('Title', _('Variances Between Deliveries and Orders'));
$PDF->addInfo('Subject', _('Variances Between Deliveries and Orders from') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate']);
$line_height = 12;
$PageNumber = 1;
$TotalDiffs = 0;

include('includes/PDFDeliveryDifferencesPageHeader.inc');

while ($MyRow = DB_fetch_array($Result)) {

	$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 40, $FontSize, $MyRow['invoiceno'], 'left');
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 40, $YPos, 40, $FontSize, $MyRow['orderno'], 'left');
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 80, $YPos, 200, $FontSize, $MyRow['stockid'] . ' - ' . $MyRow['description'], 'left');

	$LeftOvers = $PDF->addTextWrap($Left_Margin + 280, $YPos, 50, $FontSize, locale_number_format($MyRow['quantitydiff'], $MyRow['decimalplaces']), 'right');
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 335, $YPos, 50, $FontSize, $MyRow['debtorno'], 'left');
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 385, $YPos, 50, $FontSize, $MyRow['branch'], 'left');
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 435, $YPos, 50, $FontSize, ConvertSQLDate($MyRow['trandate']), 'left');

	$YPos -= ($line_height);
	$TotalDiffs++;

	if ($YPos - (2 * $line_height) < $Bottom_Margin) {
		/*Then set up a new page */
		$PageNumber++;
		include('includes/PDFDeliveryDifferencesPageHeader.inc');
	}
	/*end of new page header  */
}
/* end of while there are delivery differences to print */


$YPos -= $line_height;
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
				AND locationusers.userid='" .  $_SESSION['UserID'] . "'
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
				AND locationusers.userid='" .  $_SESSION['UserID'] . "'
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
				AND locationusers.userid='" .  $_SESSION['UserID'] . "'
				AND locationusers.canview=1
			WHERE debtortrans.trandate>='". FormatDateForSQL($_POST['FromDate']) . "'
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
				AND locationusers.userid='" .  $_SESSION['UserID'] . "'
				AND locationusers.canview=1
			INNER JOIN stockmaster
				ON salesorderdetails.stkcode = stockmaster.stockid
			WHERE salesorders.fromstkloc ='" . $_POST['Location'] . "'
				AND categoryid='" . $_POST['CategoryID'] . "'
				AND trandate >='" . FormatDateForSQL($_POST['FromDate']) . "'
				AND trandate <= '" . FormatDateForSQL($_POST['ToDate']) . "'";
}

if ($_SESSION['SalesmanLogin'] != '') {
	$SQL .= " AND debtortrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
}

$ErrMsg = _('Could not retrieve the count of sales order lines in the period under review');
$Result = DB_query($SQL, $ErrMsg);


$MyRow = DB_fetch_row($Result);
$YPos -= $line_height;
$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 200, $FontSize, _('Total number of order lines') . ' ' . locale_number_format($MyRow[0]), 'left');

$YPos -= $line_height;
$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 200, $FontSize, _('DIFOT') . ' ' . locale_number_format((1 - ($TotalDiffs / $MyRow[0])) * 100, 2) . '%', 'left');

$ReportFileName = $_SESSION['DatabaseName'] . '_DeliveryDifferences_' . date('Y-m-d') . '.pdf';
$PDF->OutputD($ReportFileName);

if ($_POST['Email'] == 'Yes') {
	if (file_exists($_SESSION['reports_dir'] . '/' . $ReportFileName)) {
		unlink($_SESSION['reports_dir'] . '/' . $ReportFileName);
	}
	$PDF->Output($_SESSION['reports_dir'] . '/' . $ReportFileName, 'F');

	include('includes/htmlMimeMail.php');

	$Mail = new htmlMimeMail();
	$attachment = $Mail->getFile($_SESSION['reports_dir'] . '/' . $ReportFileName);
	$Mail->setText(_('Please find herewith delivery differences report from') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate']);
	$Mail->addAttachment($attachment, $ReportFileName, 'application/pdf');
	if ($_SESSION['SmtpSetting'] == 0) {
		$Mail->setFrom($_SESSION['CompanyRecord']['coyname'] . ' <' . $_SESSION['CompanyRecord']['email'] . '>');
		$Result = $Mail->send(array(
			$_SESSION['FactoryManagerEmail']
		));
	} else {
		$Result = SendmailBySmtp($Mail, array(
			$_SESSION['FactoryManagerEmail']
		));
	}

}
$PDF->__destruct();
?>