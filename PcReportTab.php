<?php
include ('includes/session.php');
include ('includes/SQL_CommonFunctions.php');

$Title = _('Petty Cash Management Report');
$ViewTopic = 'PettyCash';
$BookMark = 'PcReportTab';

if (isset($_POST['SelectedTabs'])) {
	$SelectedTabs = mb_strtoupper($_POST['SelectedTabs']);
} elseif (isset($_GET['SelectedTabs'])) {
	$SelectedTabs = mb_strtoupper($_GET['SelectedTabs']);
}

if ((!isset($_POST['FromDate']) and !isset($_POST['ToDate'])) or isset($_POST['SelectDifferentDate'])) {

	include ('includes/header.php');

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/money_add.png" title="', _('Payment Entry'), '" alt="" />', ' ', $Title, '
		</p>';

	echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	if (!isset($_POST['FromDate'])) {
		$_POST['FromDate'] = Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, Date('m'), 1, Date('Y')));
	}

	if (!isset($_POST['ToDate'])) {
		$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
	}

	/*Show a form to allow input of criteria for Tabs to show */
	echo '<fieldset>
			<legend>', _('Report Criteria'), '</legend>';

	$SQL = "SELECT tabcode
		FROM pctabs
		WHERE ( authorizer='" . $_SESSION['UserID'] . "' OR usercode ='" . $_SESSION['UserID'] . "' OR assigner ='" . $_SESSION['UserID'] . "' )
		ORDER BY tabcode";
	$Result = DB_query($SQL);
	echo '<field>
			<label for="SelectedTabs">', _('Code Of Petty Cash Tab'), ':</label>
			<select autofocus="autofocus" required="required" name="SelectedTabs">';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['SelectedTabs']) and $MyRow['tabcode'] == $_POST['SelectedTabs']) {
			echo '<option selected="selected" value="', $MyRow['tabcode'], '">', $MyRow['tabcode'], '</option>';
		} else {
			echo '<option value="', $MyRow['tabcode'], '">', $MyRow['tabcode'], '</option>';
		}
	} //end while loop get type of tab
	echo '</select>
		</field>';

	echo '<field>
			<label for="FromDate">', _('From Date'), ':</label>
			<input class="date" type="text" name="FromDate" required="required" maxlength="10" size="11" value="', $_POST['FromDate'], '" />
		</field>';

	echo '<field>
			<label for="ToDate">', _('To Date'), ':</label>
			<input class="date" type="text" name="ToDate" required="required" maxlength="10" size="11" value="', $_POST['ToDate'], '" />
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="ShowTB" value="', _('Show HTML'), '" />
			<input type="submit" name="PrintPDF" value="', _('PrintPDF'), '" />
		</div>';

	echo '</form>';

} else if (isset($_POST['PrintPDF'])) {

	include ('includes/PDFStarter.php');
	$PageNumber = 0;
	$FontSize = 10;
	$PDF->addInfo('Title', _('Petty Cash Report Of Tab'));
	$PDF->addInfo('Subject', _('Petty Cash Report Of Tab'));
	$line_height = 12;

	$SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
	$SQL_ToDate = FormatDateForSQL($_POST['ToDate']);

	$SQL = "SELECT * FROM pcashdetails
			WHERE tabcode='" . $SelectedTabs . "'
			AND date >='" . $SQL_FromDate . "' AND date <= '" . $SQL_ToDate . "'
			ORDER BY date, counterindex ASC";

	$TabDetail = DB_query($SQL);

	if (DB_error_no() != 0) {
		include ('includes/header.php');
		prnMsg(_('An error occurred getting the orders details'), '', _('Database Error'));
		if ($Debug == 1) {
			prnMsg(_('The SQL used to get the orders that failed was') . '<br />' . $SQL, '', _('Database Error'));
		}
		include ('includes/footer.php');
		exit;
	} elseif (DB_num_rows($TabDetail) == 0) {
		include ('includes/header.php');
		prnMsg(_('There were no expenses found in the database within the period from') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate'] . '. ' . _('Please try again selecting a different date range'), 'warn');
		if ($Debug == 1) {
			prnMsg(_('The SQL that returned no rows was') . '<br />' . $SQL, '', _('Database Error'));
		}
		include ('includes/footer.php');
		exit;
	}

	include ('includes/PDFTabReportHeader.php');

	$SqlTabs = "SELECT * FROM pctabs
			WHERE tabcode='" . $SelectedTabs . "'";

	$TabResult = DB_query($SqlTabs, _('No Petty Cash tabs were returned by the SQL because'), _('The SQL that failed was') . ': ');

	$Tabs = DB_fetch_array($TabResult);

	$SqlBalance = "SELECT SUM(amount) FROM pcashdetails
					WHERE tabcode='" . $SelectedTabs . "'
					AND date<'" . $SQL_FromDate . "'";

	$TabBalance = DB_query($SqlBalance);

	$Balance = DB_fetch_array($TabBalance);

	if (!isset($Balance['0'])) {
		$Balance['0'] = 0;
	}

	$YPos-= (2 * $line_height);
	$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 60, $FontSize, _('Tab Code') . ': ');
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 100, $YPos, 20, $FontSize, ': ');
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 110, $YPos, 70, $FontSize, $SelectedTabs);
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 290, $YPos, 70, $FontSize, _('From') . ' ');
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 320, $YPos, 20, $FontSize, ': ');
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 340, $YPos, 70, $FontSize, $_POST['FromDate']);

	$YPos-= $line_height;
	$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 60, $FontSize, _('User'));
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 100, $YPos, 20, $FontSize, ': ');
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 110, $YPos, 70, $FontSize, $Tabs['usercode']);
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 290, $YPos, 70, $FontSize, _('To') . ' ');
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 320, $YPos, 20, $FontSize, ': ');
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 340, $YPos, 70, $FontSize, $_POST['ToDate']);

	$YPos-= $line_height;
	$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 60, $FontSize, _('Authoriser'));
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 100, $YPos, 20, $FontSize, ': ');
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 110, $YPos, 70, $FontSize, $Tabs['authorizer']);

	$YPos-= $line_height;
	$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 60, $FontSize, _('Currency'));
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 100, $YPos, 20, $FontSize, ': ');
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 110, $YPos, 70, $FontSize, $Tabs['currency']);

	$YPos-= $line_height;
	$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 40, $FontSize, _('Balance before'));
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 55, $YPos, 70, $FontSize, $_POST['FromDate']);
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 100, $YPos, 20, $FontSize, ': ');
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 110, $YPos, 70, $FontSize, locale_number_format($Balance['0'], $_SESSION['CompanyRecord']['decimalplaces']));
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 150, $YPos, 70, $FontSize, $Tabs['currency']);

	$YPos-= (2 * $line_height);
	$PDF->line($Page_Width - $Right_Margin, $YPos + $line_height, $Left_Margin, $YPos + $line_height);

	$YPos-= (2 * $line_height);
	$FontSize = 8;
	$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 70, $FontSize, _('Date Of Expense'));
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 70, $YPos, 100, $FontSize, _('Description'));
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 200, $YPos, 100, $FontSize, _('Amount'));
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 250, $YPos, 100, $FontSize, _('Note'));
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 350, $YPos, 100, $FontSize, _('Receipt'));
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 420, $YPos, 100, $FontSize, _('Date Authorised'));
	$YPos-= (2 * $line_height);

	while ($MyRow = DB_fetch_array($TabDetail)) {

		$SQLdes = "SELECT description
					FROM pcexpenses
					WHERE codeexpense='" . $MyRow[3] . "'";

		$ResultDes = DB_query($SQLdes);
		$Description = DB_fetch_array($ResultDes);

		if (!isset($Description[0])) {
			$Description[0] = 'ASSIGNCASH';
		}

		// Print total for each account
		$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 70, $FontSize, ConvertSQLDate($MyRow['date']));
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 70, $YPos, 130, $FontSize, $Description[0]);
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 180, $YPos, 50, $FontSize, locale_number_format($MyRow['amount'], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 250, $YPos, 100, $FontSize, $MyRow['notes']);
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 350, $YPos, 70, $FontSize, $MyRow['receipt']);
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 430, $YPos, 70, $FontSize, ConvertSQLDate($MyRow['authorized']));
		$YPos-= $line_height;

	} //end of while loop
	$SQLamount = "SELECT sum(amount)
				FROM pcashdetails
				WHERE tabcode='" . $SelectedTabs . "'
				AND date<='" . $SQL_ToDate . "'";

	$ResultAmount = DB_query($SQLamount);
	$Amount = DB_fetch_array($ResultAmount);

	if (!isset($Amount[0])) {
		$Amount[0] = 0;
	}

	$YPos-= (2 * $line_height);
	$PDF->line($Left_Margin + 250, $YPos + $line_height, $Left_Margin + 500, $YPos + $line_height);
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 70, $YPos, 100, $FontSize, _('Balance at'));
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 110, $YPos, 70, $FontSize, $_POST['ToDate']);
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 160, $YPos, 20, $FontSize, _(': '));
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 160, $YPos, 70, $FontSize, locale_number_format($Amount[0], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 240, $YPos, 70, $FontSize, $Tabs['currency']);
	$PDF->line($Page_Width - $Right_Margin, $YPos + $line_height, $Left_Margin, $YPos + $line_height);

	$PDF->OutputD($_SESSION['DatabaseName'] . '_PettyCash_Tab_Report_' . date('Y-m-d') . '.pdf');
	$PDF->__destruct();
	exit;
} else {

	include ('includes/header.php');

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/money_add.png" title="', _('Payment Entry'), '" alt="" />', ' ', $Title, '
		</p>';

	$SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
	$SQL_ToDate = FormatDateForSQL($_POST['ToDate']);

	echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<input type="hidden" name="FromDate" value="', $_POST['FromDate'], '" />
			<input type="hidden" name="ToDate" value="', $_POST['ToDate'], '" />';

	$SqlTabs = "SELECT tabcode,
						usercode,
						typetabcode,
						currency,
						tablimit,
						assigner,
						authorizer,
						authorizerexpenses,
						glaccountassignment,
						glaccountpcash,
						defaulttag,
						taxgroupid
					FROM pctabs
					WHERE tabcode='" . $SelectedTabs . "'";

	$TabResult = DB_query($SqlTabs, _('No Petty Cash Tabs were returned by the SQL because'), _('The SQL that failed was') . ': ');

	$Tabs = DB_fetch_array($TabResult);

	echo '<table>
			<tr>
				<td>', _('Tab Code'), '</td>
				<td>:</td>
				<td style="width:200px">', $SelectedTabs, '</td>
				<td>', _('From'), '</td>
				<td>:</td>
				<td>', $_POST['FromDate'], '</td>
			</tr>
			<tr>
				<td>', _('User'), '</td>
				<td>:</td>
				<td>', $Tabs['usercode'], '</td>
				<td>', _('To'), '</td>
				<td>:</td>
				<td>', $_POST['ToDate'], '</td>
			</tr>
			<tr>
				<td>', _('Authoriser'), '</td>
				<td>:</td>
				<td>', $Tabs['authorizer'], '</td>
			</tr>
			<tr>
				<td>', _('Currency'), '</td>
				<td>:</td>
				<td>', $Tabs['currency'], '</td>
			</tr>';

	$SqlBalance = "SELECT SUM(amount)
			FROM pcashdetails
			WHERE tabcode='" . $SelectedTabs . "'
			AND date<'" . $SQL_FromDate . "'";

	$TabBalance = DB_query($SqlBalance);

	$Balance = DB_fetch_array($TabBalance);

	if (!isset($Balance['0'])) {
		$Balance['0'] = 0;
	}

	echo '<tr>
			<td>', _('Balance before '), $_POST['FromDate'], '</td>
			<td>:</td>
			<td>', locale_number_format($Balance['0'], $_SESSION['CompanyRecord']['decimalplaces']), ' ', $Tabs['currency'], '</td>
		</tr>';

	$SqlBalanceNotAut = "SELECT SUM(amount)
			FROM pcashdetails
			WHERE tabcode= '" . $SelectedTabs . "'
			AND authorized = '0000-00-00'
			AND date<'" . $SQL_FromDate . "'";

	$TabBalanceNotAut = DB_query($SqlBalanceNotAut);

	$BalanceNotAut = DB_fetch_array($TabBalanceNotAut);

	if (!isset($BalanceNotAut['0'])) {
		$BalanceNotAut['0'] = 0;
	}

	echo '<tr>
			<td>', _('Total not authorised before '), '', $_POST['FromDate'], '</td>
			<td>:</td>
			<td>', locale_number_format($BalanceNotAut['0'], $_SESSION['CompanyRecord']['decimalplaces']), ' ', $Tabs['currency'], '</td>
		</tr>';

	echo '</table>';

	/*show a table of the accounts info returned by the SQL
	 Account Code ,   Account Name , Month Actual, Month Budget, Period Actual, Period Budget */

	$SQL = "SELECT counterindex,
					tabcode,
					date,
					codeexpense,
					amount,
					authorized,
					posted,
					notes,
					receipt
				FROM pcashdetails
				WHERE tabcode='" . $SelectedTabs . "'
					AND date >='" . $SQL_FromDate . "'
					AND date <= '" . $SQL_ToDate . "'
				ORDER BY date, counterindex Asc";

	$TabDetail = DB_query($SQL, _('No Petty Cash movements for this tab were returned by the SQL because'), _('The SQL that failed was') . ': ');

	echo '<table>';
	echo '<tr>
			<th>', _('Date Of Expense'), '</th>
			<th>', _('Expense Description'), '</th>
			<th>', _('Amount'), '</th>
			<th>', _('Notes'), '</th>
			<th>', _('Receipt'), '</th>
			<th>', _('Date Authorised'), '</th>
		</tr>';

	$j = 1;

	while ($MyRow = DB_fetch_array($TabDetail)) {
		$SQLdes = "SELECT description
				FROM pcexpenses
				WHERE codeexpense='" . $MyRow['codeexpense'] . "'";

		$ResultDes = DB_query($SQLdes);
		$Description = DB_fetch_array($ResultDes);

		if (!isset($Description['0'])) {
			$Description['0'] = 'ASSIGNCASH';
		}
		if ($MyRow['5'] != '0000-00-00') {
			echo '<tr class="striped_row">
					<td>', ConvertSQLDate($MyRow['date']), '</td>
					<td>', $Description['0'], '</td>
					<td class="number">', locale_number_format($MyRow['amount'], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
					<td>', $MyRow['notes'], '</td>
					<td>', $MyRow['receipt'], '</td>
					<td>', ConvertSQLDate($MyRow['authorized']), '</td>
				</tr>';
		} else {
			echo '<tr class="striped_row">
					<td>', ConvertSQLDate($MyRow['date']), '</td>
					<td>', $Description['0'], '</td>
					<td class="number">', locale_number_format($MyRow['amount'], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
					<td>', $MyRow['notes'], '</td>
					<td>', $MyRow['receipt'], '</td>
					<td>', '		  ', '</td>
				</tr>';
		}

	}

	$SQLamount = "SELECT sum(amount)
				FROM pcashdetails
				WHERE tabcode='" . $SelectedTabs . "'
				AND date<='" . $SQL_ToDate . "'";

	$ResultAmount = DB_query($SQLamount);
	$Amount = DB_fetch_array($ResultAmount);

	if (!isset($Amount[0])) {
		$Amount[0] = 0;
	}

	echo '<tr class="total_row">
			<td colspan="2" style="text-align:right">', _('Balance At'), ' ', $_POST['ToDate'], ':</td>
			<td>', locale_number_format($Amount[0], $_SESSION['CompanyRecord']['decimalplaces']), ' </td>
			<td>', $Tabs['currency'], '</td>
			<td></td>
			<td></td>
		</tr>';

	echo '</table>';

	echo '<div class="centre">
			<input type="submit" name="SelectDifferentDate" value="', _('Select A Different Date'), '" />
		</div>';
	echo '</form>';
}
include ('includes/footer.php');

?>