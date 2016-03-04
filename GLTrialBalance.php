<?php

/*Through deviousness and cunning, this system allows trial balances for
 * any date range that recalcuates the p & l balances and shows the balance
 * sheets as at the end of the period selected - so first off need to show
 * the input of criteria screen while the user is selecting the criteria
 * the system is posting any unposted transactions
 */

include('includes/session.inc');
$Title = _('Trial Balance');
include('includes/SQL_CommonFunctions.inc');
include('includes/AccountSectionsDef.inc'); //this reads in the Accounts Sections array


if (isset($_POST['FromPeriod']) and isset($_POST['ToPeriod']) and $_POST['FromPeriod'] > $_POST['ToPeriod']) {

	prnMsg(_('The selected period from is actually after the period to! Please re-select the reporting period'), 'error');
	$_POST['SelectADifferentPeriod'] = _('Select A Different Period');
}

if ((!isset($_POST['FromPeriod']) and !isset($_POST['ToPeriod'])) or isset($_POST['SelectADifferentPeriod'])) {

	$ViewTopic = 'GeneralLedger';
	$BookMark = 'TrialBalance';
	include('includes/header.inc');
	echo '<p class="page_title_text" >
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . _('Trial Balance') . '" alt="' . _('Trial Balance') . '" />' . ' ' . $Title . '
		</p>';
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (Date('m') > $_SESSION['YearEnd']) {
		/*Dates in SQL format */
		$DefaultFromDate = Date('Y-m-d', Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y')));
		$FromDate = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y')));
	} else {
		$DefaultFromDate = Date('Y-m-d', Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y') - 1));
		$FromDate = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y') - 1));
	}
	/*GetPeriod function creates periods if need be the return value is not used */
	$NotUsedPeriodNo = GetPeriod($FromDate);

	/*Show a form to allow input of criteria for TB to show */
	echo '<table class="selection" summary="' . _('Input criteria for inquiry') . '">
			<tr>
				<td>' . _('Select Period From') . ':</td>
				<td><select name="FromPeriod">';
	$NextYear = date('Y-m-d', strtotime('+1 Year'));
	$SQL = "SELECT periodno,
					lastdate_in_period
				FROM periods
				WHERE lastdate_in_period < '" . $NextYear . "'
				ORDER BY periodno DESC";
	$Periods = DB_query($SQL);


	while ($MyRow = DB_fetch_array($Periods)) {
		if (isset($_POST['FromPeriod']) and $_POST['FromPeriod'] != '') {
			if ($_POST['FromPeriod'] == $MyRow['periodno']) {
				echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			} else {
				echo '<option value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			}
		} else {
			if ($MyRow['lastdate_in_period'] == $DefaultFromDate) {
				echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			} else {
				echo '<option value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			}
		}
	}

	echo '</select></td>
		</tr>';
	if (!isset($_POST['ToPeriod']) or $_POST['ToPeriod'] == '') {
		$DefaultToPeriod = GetPeriod(date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, Date('m') + 1, 0, Date('Y'))));
	} else {
		$DefaultToPeriod = $_POST['ToPeriod'];
	}

	echo '<tr>
			<td>' . _('Select Period To') . ':</td>
			<td><select name="ToPeriod">';

	$RetResult = DB_data_seek($Periods, 0);

	while ($MyRow = DB_fetch_array($Periods)) {

		if ($MyRow['periodno'] == $DefaultToPeriod) {
			echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
		} else {
			echo '<option value ="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
		}
	}
	echo '</select></td>
		</tr>
		</table>
		<br />';

	echo '<div class="centre">
			<input type="submit" name="ShowTB" value="' . _('Show Trial Balance') . '" />
			<input type="submit" name="PrintPDF" value="' . _('Print PDF') . '" />
			<input type="submit" name="ExportCSV" value="' . _('Export to Spreadsheet') . '" />
		</div>';

	/*Now do the posting while the user is thinking about the period to select */

	include('includes/GLPostings.inc');

} else if (isset($_POST['PrintPDF'])) {

	include('includes/PDFStarter.php');

	$PDF->addInfo('Title', _('Trial Balance'));
	$PDF->addInfo('Subject', _('Trial Balance'));
	$PageNumber = 0;
	$FontSize = 10;
	$line_height = 12;

	$NumberOfMonths = $_POST['ToPeriod'] - $_POST['FromPeriod'] + 1;

	$SQL = "SELECT lastdate_in_period
			FROM periods
			WHERE periodno='" . $_POST['ToPeriod'] . "'";
	$PrdResult = DB_query($SQL);
	$MyRow = DB_fetch_row($PrdResult);
	$PeriodToDate = MonthAndYearFromSQLDate($MyRow[0]);

	$RetainedEarningsAct = $_SESSION['CompanyRecord']['retainedearnings'];

	$SQL = "SELECT accountgroups.groupname,
			accountgroups.parentgroupname,
			accountgroups.pandl,
			chartdetails.accountcode ,
			chartmaster.accountname,
			Sum(CASE WHEN chartdetails.period='" . $_POST['FromPeriod'] . "' THEN chartdetails.bfwd ELSE 0 END) AS firstprdbfwd,
			Sum(CASE WHEN chartdetails.period='" . $_POST['FromPeriod'] . "' THEN chartdetails.bfwdbudget ELSE 0 END) AS firstprdbudgetbfwd,
			Sum(CASE WHEN chartdetails.period='" . $_POST['ToPeriod'] . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS lastprdcfwd,
			Sum(CASE WHEN chartdetails.period='" . $_POST['ToPeriod'] . "' THEN chartdetails.actual ELSE 0 END) AS monthactual,
			Sum(CASE WHEN chartdetails.period='" . $_POST['ToPeriod'] . "' THEN chartdetails.budget ELSE 0 END) AS monthbudget,
			Sum(CASE WHEN chartdetails.period='" . $_POST['ToPeriod'] . "' THEN chartdetails.bfwdbudget + chartdetails.budget ELSE 0 END) AS lastprdbudgetcfwd
		FROM chartmaster
			INNER JOIN accountgroups ON chartmaster.groupcode = accountgroups.groupcode AND chartmaster.language=accountgroups.language
			INNER JOIN chartdetails ON chartmaster.accountcode= chartdetails.accountcode
			INNER JOIN glaccountusers ON glaccountusers.accountcode=chartmaster.accountcode AND glaccountusers.userid='" . $_SESSION['UserID'] . "' AND glaccountusers.canview=1
		WHERE chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
		GROUP BY accountgroups.groupcode,
				accountgroups.parentgroupname,
				accountgroups.pandl,
				accountgroups.sequenceintb,
				chartdetails.accountcode,
				chartmaster.accountname
		ORDER BY accountgroups.pandl desc,
			accountgroups.sequenceintb,
			accountgroups.groupcode,
			chartdetails.accountcode";

	$AccountsResult = DB_query($SQL);
	if (DB_error_no() != 0) {
		$Title = _('Trial Balance') . ' - ' . _('Problem Report') . '....';
		$ViewTopic = 'GeneralLedger';
		$BookMark = 'TrialBalance';
		include('includes/header.inc');
		prnMsg(_('No general ledger accounts were returned by the SQL because') . ' - ' . DB_error_msg());
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($Debug == 1) {
			echo '<br />' . $SQL;
		}
		include('includes/footer.inc');
		exit;
	}
	if (DB_num_rows($AccountsResult) == 0) {
		$Title = _('Print Trial Balance Error');
		$ViewTopic = 'GeneralLedger';
		$BookMark = 'TrialBalance';
		include('includes/header.inc');
		echo '<p>';
		prnMsg(_('There were no entries to print out for the selections specified'));
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		include('includes/footer.inc');
		exit;
	}

	include('includes/PDFTrialBalancePageHeader.inc');

	$j = 1;
	$Level = 1;
	$ActGrp = '';
	$ParentGroups = array();
	$ParentGroups[$Level] = '';
	$GrpActual = array(
		0
	);
	$GrpBudget = array(
		0
	);
	$GrpPrdActual = array(
		0
	);
	$GrpPrdBudget = array(
		0
	);
	$PeriodProfitLoss = 0;
	$PeriodBudgetProfitLoss = 0;
	$MonthProfitLoss = 0;
	$MonthBudgetProfitLoss = 0;
	$BFwdProfitLoss = 0;
	$CheckMonth = 0;
	$CheckBudgetMonth = 0;
	$CheckPeriodActual = 0;
	$CheckPeriodBudget = 0;

	while ($MyRow = DB_fetch_array($AccountsResult)) {

		if ($MyRow['groupname'] != $ActGrp) {

			if ($ActGrp != '') {

				// Print heading if at end of page
				if ($YPos < ($Bottom_Margin + (2 * $line_height))) {
					include('includes/PDFTrialBalancePageHeader.inc');
				}
				if ($MyRow['parentgroupname'] == $ActGrp) {
					$Level++;
					$ParentGroups[$Level] = $MyRow['groupname'];
				} elseif ($MyRow['parentgroupname'] == $ParentGroups[$Level]) {
					$YPos -= (.5 * $line_height);
					$PDF->line($Left_Margin + 250, $YPos + $line_height, $Left_Margin + 500, $YPos + $line_height);
					$PDF->setFont('', 'B');
					$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 60, $FontSize, _('Total'));
					$LeftOvers = $PDF->addTextWrap($Left_Margin + 60, $YPos, 190, $FontSize, $ParentGroups[$Level]);
					$LeftOvers = $PDF->addTextWrap($Left_Margin + 250, $YPos, 70, $FontSize, locale_number_format($GrpActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
					$LeftOvers = $PDF->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, locale_number_format($GrpBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
					$LeftOvers = $PDF->addTextWrap($Left_Margin + 370, $YPos, 70, $FontSize, locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
					$LeftOvers = $PDF->addTextWrap($Left_Margin + 430, $YPos, 70, $FontSize, locale_number_format($GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
					$PDF->line($Left_Margin + 250, $YPos, $Left_Margin + 500, $YPos);
					/*Draw the bottom line */
					$YPos -= (2 * $line_height);
					$PDF->setFont('', '');
					$ParentGroups[$Level] = $MyRow['groupname'];
					$GrpActual[$Level] = 0;
					$GrpBudget[$Level] = 0;
					$GrpPrdActual[$Level] = 0;
					$GrpPrdBduget[$Level] = 0;

				} else {
					do {
						$YPos -= $line_height;
						$PDF->line($Left_Margin + 250, $YPos + $line_height, $Left_Margin + 500, $YPos + $line_height);
						$PDF->setFont('', 'B');
						$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 60, $FontSize, _('Total'));
						$LeftOvers = $PDF->addTextWrap($Left_Margin + 60, $YPos, 190, $FontSize, $ParentGroups[$Level]);
						$LeftOvers = $PDF->addTextWrap($Left_Margin + 250, $YPos, 70, $FontSize, locale_number_format($GrpActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
						$LeftOvers = $PDF->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, locale_number_format($GrpBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
						$LeftOvers = $PDF->addTextWrap($Left_Margin + 370, $YPos, 70, $FontSize, locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
						$LeftOvers = $PDF->addTextWrap($Left_Margin + 430, $YPos, 70, $FontSize, locale_number_format($GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
						$PDF->line($Left_Margin + 250, $YPos, $Left_Margin + 500, $YPos);
						/*Draw the bottom line */
						$YPos -= (2 * $line_height);
						$PDF->setFont('', '');
						$ParentGroups[$Level] = '';
						$GrpActual[$Level] = 0;
						$GrpBudget[$Level] = 0;
						$GrpPrdActual[$Level] = 0;
						$GrpPrdBduget[$Level] = 0;
						$Level--;
					} while ($Level > 0 and $MyRow['parentgroupname'] != $ParentGroups[$Level]);

					if ($Level > 0) {
						$YPos -= $line_height;
						$PDF->line($Left_Margin + 250, $YPos + $line_height, $Left_Margin + 500, $YPos + $line_height);
						$PDF->setFont('', 'B');
						$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 60, $FontSize, _('Total'));
						$LeftOvers = $PDF->addTextWrap($Left_Margin + 60, $YPos, 190, $FontSize, $ParentGroups[$Level]);
						$LeftOvers = $PDF->addTextWrap($Left_Margin + 250, $YPos, 70, $FontSize, locale_number_format($GrpActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
						$LeftOvers = $PDF->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, locale_number_format($GrpBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
						$LeftOvers = $PDF->addTextWrap($Left_Margin + 370, $YPos, 70, $FontSize, locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
						$LeftOvers = $PDF->addTextWrap($Left_Margin + 430, $YPos, 70, $FontSize, locale_number_format($GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
						$PDF->line($Left_Margin + 250, $YPos, $Left_Margin + 500, $YPos);
						/*Draw the bottom line */
						$YPos -= (2 * $line_height);
						$PDF->setFont('', '');
						$GrpActual[$Level] = 0;
						$GrpBudget[$Level] = 0;
						$GrpPrdActual[$Level] = 0;
						$GrpPrdBduget[$Level] = 0;
					} else {
						$Level = 1;
					}
				}
			}
			$YPos -= (2 * $line_height);
			// Print account group name
			$PDF->setFont('', 'B');
			$ActGrp = $MyRow['groupname'];
			$ParentGroups[$Level] = $MyRow['groupname'];
			$FontSize = 10;
			$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 200, $FontSize, $MyRow['groupname']);
			$FontSize = 8;
			$PDF->setFont('', '');
			$YPos -= (2 * $line_height);
		}

		if ($MyRow['pandl'] == 1) {

			$AccountPeriodActual = $MyRow['lastprdcfwd'] - $MyRow['firstprdbfwd'];
			$AccountPeriodBudget = $MyRow['lastprdbudgetcfwd'] - $MyRow['firstprdbudgetbfwd'];

			$PeriodProfitLoss += $AccountPeriodActual;
			$PeriodBudgetProfitLoss += $AccountPeriodBudget;
			$MonthProfitLoss += $MyRow['monthactual'];
			$MonthBudgetProfitLoss += $MyRow['monthbudget'];
			$BFwdProfitLoss += $MyRow['firstprdbfwd'];
		} else {
			/*PandL ==0 its a balance sheet account */
			if ($MyRow['accountcode'] == $RetainedEarningsAct) {
				$AccountPeriodActual = $BFwdProfitLoss + $MyRow['lastprdcfwd'];
				$AccountPeriodBudget = $BFwdProfitLoss + $MyRow['lastprdbudgetcfwd'] - $MyRow['firstprdbudgetbfwd'];
			} else {
				$AccountPeriodActual = $MyRow['lastprdcfwd'];
				$AccountPeriodBudget = $MyRow['firstprdbfwd'] + $MyRow['lastprdbudgetcfwd'] - $MyRow['firstprdbudgetbfwd'];
			}

		}
		for ($i = 0; $i <= $Level; $i++) {
			if (!isset($GrpActual[$i])) {
				$GrpActual[$i] = 0;
			}
			$GrpActual[$i] += $MyRow['monthactual'];
			if (!isset($GrpBudget[$i])) {
				$GrpBudget[$i] = 0;
			}
			$GrpBudget[$i] += $MyRow['monthbudget'];
			if (!isset($GrpPrdActual[$i])) {
				$GrpPrdActual[$i] = 0;
			}
			$GrpPrdActual[$i] += $AccountPeriodActual;
			if (!isset($GrpPrdBudget[$i])) {
				$GrpPrdBudget[$i] = 0;
			}
			$GrpPrdBudget[$i] += $AccountPeriodBudget;
		}

		$CheckMonth += $MyRow['monthactual'];
		$CheckBudgetMonth += $MyRow['monthbudget'];
		$CheckPeriodActual += $AccountPeriodActual;
		$CheckPeriodBudget += $AccountPeriodBudget;

		// Print heading if at end of page
		if ($YPos < ($Bottom_Margin)) {
			include('includes/PDFTrialBalancePageHeader.inc');
		}

		// Print total for each account
		$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 60, $FontSize, $MyRow['accountcode']);
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 60, $YPos, 190, $FontSize, $MyRow['accountname']);
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 250, $YPos, 70, $FontSize, locale_number_format($MyRow['monthactual'], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, locale_number_format($MyRow['monthbudget'], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 370, $YPos, 70, $FontSize, locale_number_format($AccountPeriodActual, $_SESSION['CompanyRecord']['decimalplaces']), 'right');
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 430, $YPos, 70, $FontSize, locale_number_format($AccountPeriodBudget, $_SESSION['CompanyRecord']['decimalplaces']), 'right');
		$YPos -= $line_height;

	} //end of while loop


	while ($Level > 0 and $MyRow['parentgroupname'] != $ParentGroups[$Level]) {

		$YPos -= (.5 * $line_height);
		$PDF->line($Left_Margin + 250, $YPos + $line_height, $Left_Margin + 500, $YPos + $line_height);
		$PDF->setFont('', 'B');
		$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 60, $FontSize, _('Total'));
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 60, $YPos, 190, $FontSize, $ParentGroups[$Level]);
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 250, $YPos, 70, $FontSize, locale_number_format($GrpActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, locale_number_format($GrpBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 370, $YPos, 70, $FontSize, locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 430, $YPos, 70, $FontSize, locale_number_format($GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
		$PDF->line($Left_Margin + 250, $YPos, $Left_Margin + 500, $YPos);
		/*Draw the bottom line */
		$YPos -= (2 * $line_height);
		$ParentGroups[$Level] = '';
		$GrpActual[$Level] = 0;
		$GrpBudget[$Level] = 0;
		$GrpPrdActual[$Level] = 0;
		$GrpPrdBduget[$Level] = 0;
		$Level--;
	}


	$YPos -= (2 * $line_height);
	$PDF->line($Left_Margin + 250, $YPos + $line_height, $Left_Margin + 500, $YPos + $line_height);
	$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 60, $FontSize, _('Check Totals'));
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 250, $YPos, 70, $FontSize, locale_number_format($CheckMonth, $_SESSION['CompanyRecord']['decimalplaces']), 'right');
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, locale_number_format($CheckBudgetMonth, $_SESSION['CompanyRecord']['decimalplaces']), 'right');
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 370, $YPos, 70, $FontSize, locale_number_format($CheckPeriodActual, $_SESSION['CompanyRecord']['decimalplaces']), 'right');
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 430, $YPos, 70, $FontSize, locale_number_format($CheckPeriodBudget, $_SESSION['CompanyRecord']['decimalplaces']), 'right');
	$PDF->line($Left_Margin + 250, $YPos, $Left_Margin + 500, $YPos);

	$PDF->OutputD($_SESSION['DatabaseName'] . '_GL_Trial_Balance_' . Date('Y-m-d') . '.pdf');
	$PDF->__destruct();
	exit;
} elseif (isset($_POST['ExportCSV'])) {
	include('includes/header.inc');
	echo '<div class="centre"><a href="GLTrialBalance.php">' . _('Select A Different Period') . '</a></div>';
	echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/GLTrialBalance_csv.php?FromPeriod=' . $_POST['FromPeriod'] . '&ToPeriod=' . $_POST['ToPeriod'] . '">';
} else {

	$ViewTopic = 'GeneralLedger';
	$BookMark = 'TrialBalance';
	include('includes/header.inc');
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="FromPeriod" value="' . $_POST['FromPeriod'] . '" />
			<input type="hidden" name="ToPeriod" value="' . $_POST['ToPeriod'] . '" />';

	$NumberOfMonths = $_POST['ToPeriod'] - $_POST['FromPeriod'] + 1;

	$SQL = "SELECT lastdate_in_period
			FROM periods
			WHERE periodno='" . $_POST['ToPeriod'] . "'";
	$PrdResult = DB_query($SQL);
	$MyRow = DB_fetch_row($PrdResult);
	$PeriodToDate = MonthAndYearFromSQLDate($MyRow[0]);

	$RetainedEarningsAct = $_SESSION['CompanyRecord']['retainedearnings'];

	$SQL = "SELECT accountgroups.groupname,
			accountgroups.parentgroupname,
			accountgroups.pandl,
			chartdetails.accountcode ,
			chartmaster.accountname,
			Sum(CASE WHEN chartdetails.period='" . $_POST['FromPeriod'] . "' THEN chartdetails.bfwd ELSE 0 END) AS firstprdbfwd,
			Sum(CASE WHEN chartdetails.period='" . $_POST['FromPeriod'] . "' THEN chartdetails.bfwdbudget ELSE 0 END) AS firstprdbudgetbfwd,
			Sum(CASE WHEN chartdetails.period='" . $_POST['ToPeriod'] . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS lastprdcfwd,
			Sum(CASE WHEN chartdetails.period='" . $_POST['ToPeriod'] . "' THEN chartdetails.actual ELSE 0 END) AS monthactual,
			Sum(CASE WHEN chartdetails.period='" . $_POST['ToPeriod'] . "' THEN chartdetails.budget ELSE 0 END) AS monthbudget,
			Sum(CASE WHEN chartdetails.period='" . $_POST['ToPeriod'] . "' THEN chartdetails.bfwdbudget + chartdetails.budget ELSE 0 END) AS lastprdbudgetcfwd
		FROM chartmaster
			INNER JOIN accountgroups ON chartmaster.groupcode = accountgroups.groupcode AND chartmaster.language=accountgroups.language
			INNER JOIN chartdetails ON chartmaster.accountcode= chartdetails.accountcode
			INNER JOIN glaccountusers ON glaccountusers.accountcode=chartmaster.accountcode AND glaccountusers.userid='" . $_SESSION['UserID'] . "' AND glaccountusers.canview=1
		WHERE chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
		GROUP BY accountgroups.groupcode,
				accountgroups.pandl,
				accountgroups.sequenceintb,
				accountgroups.parentgroupname,
				chartdetails.accountcode,
				chartmaster.accountname
		ORDER BY accountgroups.pandl desc,
			accountgroups.sequenceintb,
			accountgroups.groupcode,
			chartdetails.accountcode";


	$AccountsResult = DB_query($SQL, _('No general ledger accounts were returned by the SQL because'), _('The SQL that failed was') . ': ');

	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . _('Trial Balance') . '" alt="' . _('Print') . '" />' . ' ' . _('Trial Balance Report') . '</p>';

	/*show a table of the accounts info returned by the SQL
	Account Code ,   Account Name , Month Actual, Month Budget, Period Actual, Period Budget */

	echo '<table cellpadding="2" class="selection" summary="' . _('Trial Balance Report') . '">';
	echo '<thead>
			<tr>
				<th colspan="6">
					<b>' . _('Trial Balance for the month of ') . $PeriodToDate . _(' and for the ') . $NumberOfMonths . _(' months to ') . $PeriodToDate . '</b>
					<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/printer.png" class="PrintIcon" title="' . _('Print') . '" alt="' . _('Print') . '" onclick="window.print();" />
				</th>
			</tr>
			<tr>
				<th>' . _('Account') . '</th>
				<th>' . _('Account Name') . '</th>
				<th>' . _('Month Actual') . '</th>
				<th>' . _('Month Budget') . '</th>
				<th>' . _('Period Actual') . '</th>
				<th>' . _('Period Budget') . '</th>
			</tr>
		</thead>';

	$k = 0; //row colour counter
	$ActGrp = '';
	$ParentGroups = array();
	$Level = 1; //level of nested sub-groups
	$ParentGroups[$Level] = '';
	$GrpActual = array(
		0
	);
	$GrpBudget = array(
		0
	);
	$GrpPrdActual = array(
		0
	);
	$GrpPrdBudget = array(
		0
	);

	$PeriodProfitLoss = 0;
	$PeriodBudgetProfitLoss = 0;
	$MonthProfitLoss = 0;
	$MonthBudgetProfitLoss = 0;
	$BFwdProfitLoss = 0;
	$CheckMonth = 0;
	$CheckBudgetMonth = 0;
	$CheckPeriodActual = 0;
	$CheckPeriodBudget = 0;

	$j = 0;
	echo '<tbody>';

	while ($MyRow = DB_fetch_array($AccountsResult)) {

		if ($MyRow['groupname'] != $ActGrp) {
			if ($ActGrp != '') { //so its not the first account group of the first account displayed
				if ($MyRow['parentgroupname'] == $ActGrp) {
					$Level++;
					$ParentGroups[$Level] = $MyRow['groupname'];
					$GrpActual[$Level] = 0;
					$GrpBudget[$Level] = 0;
					$GrpPrdActual[$Level] = 0;
					$GrpPrdBudget[$Level] = 0;
					$ParentGroups[$Level] = '';
				} elseif ($ParentGroups[$Level] == $MyRow['parentgroupname']) {
					printf('<tr>
						<td colspan="2"><i>%s ' . _('Total') . ' </i></td>
						<td class="number"><i>%s</i></td>
						<td class="number"><i>%s</i></td>
						<td class="number"><i>%s</i></td>
						<td class="number"><i>%s</i></td>
						</tr>', $ParentGroups[$Level], locale_number_format($GrpActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($GrpBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']));

					$GrpActual[$Level] = 0;
					$GrpBudget[$Level] = 0;
					$GrpPrdActual[$Level] = 0;
					$GrpPrdBudget[$Level] = 0;
					$ParentGroups[$Level] = $MyRow['groupname'];
				} else {
					do {
						printf('<tr>
							<td colspan="2"><i>%s ' . _('Total') . ' </i></td>
							<td class="number"><i>%s</i></td>
							<td class="number"><i>%s</i></td>
							<td class="number"><i>%s</i></td>
							<td class="number"><i>%s</i></td>
							</tr>', $ParentGroups[$Level], locale_number_format($GrpActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($GrpBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']));

						$GrpActual[$Level] = 0;
						$GrpBudget[$Level] = 0;
						$GrpPrdActual[$Level] = 0;
						$GrpPrdBudget[$Level] = 0;
						$ParentGroups[$Level] = '';
						$Level--;

						++$j;
					} while ($Level > 0 and $MyRow['groupname'] != $ParentGroups[$Level]);

					if ($Level > 0) {
						printf('<tr>
						<td colspan="2"><i>%s ' . _('Total') . ' </i></td>
						<td class="number"><i>%s</i></td>
						<td class="number"><i>%s</i></td>
						<td class="number"><i>%s</i></td>
						<td class="number"><i>%s</i></td>
						</tr>', $ParentGroups[$Level], locale_number_format($GrpActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($GrpBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']));

						$GrpActual[$Level] = 0;
						$GrpBudget[$Level] = 0;
						$GrpPrdActual[$Level] = 0;
						$GrpPrdBudget[$Level] = 0;
						$ParentGroups[$Level] = '';
					} else {
						$Level = 1;
					}
				}
			}
			$ParentGroups[$Level] = $MyRow['groupname'];
			$ActGrp = $MyRow['groupname'];
			printf('<tr>
						<td colspan="6"><h2>%s</h2></td>
					</tr>', $MyRow['groupname']);
		}

		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			++$k;
		}
		/*MonthActual, MonthBudget, FirstPrdBFwd, FirstPrdBudgetBFwd, LastPrdBudgetCFwd, LastPrdCFwd */


		if ($MyRow['pandl'] == 1) {

			$AccountPeriodActual = $MyRow['lastprdcfwd'] - $MyRow['firstprdbfwd'];
			$AccountPeriodBudget = $MyRow['lastprdbudgetcfwd'] - $MyRow['firstprdbudgetbfwd'];

			$PeriodProfitLoss += $AccountPeriodActual;
			$PeriodBudgetProfitLoss += $AccountPeriodBudget;
			$MonthProfitLoss += $MyRow['monthactual'];
			$MonthBudgetProfitLoss += $MyRow['monthbudget'];
			$BFwdProfitLoss += $MyRow['firstprdbfwd'];
		} else {
			/*PandL ==0 its a balance sheet account */
			if ($MyRow['accountcode'] == $RetainedEarningsAct) {
				$AccountPeriodActual = $BFwdProfitLoss + $MyRow['lastprdcfwd'];
				$AccountPeriodBudget = $BFwdProfitLoss + $MyRow['lastprdbudgetcfwd'] - $MyRow['firstprdbudgetbfwd'];
			} else {
				$AccountPeriodActual = $MyRow['lastprdcfwd'];
				$AccountPeriodBudget = $MyRow['firstprdbfwd'] + $MyRow['lastprdbudgetcfwd'] - $MyRow['firstprdbudgetbfwd'];
			}

		}

		if (!isset($GrpActual[$Level])) {
			$GrpActual[$Level] = 0;
		}
		if (!isset($GrpBudget[$Level])) {
			$GrpBudget[$Level] = 0;
		}
		if (!isset($GrpPrdActual[$Level])) {
			$GrpPrdActual[$Level] = 0;
		}
		if (!isset($GrpPrdBudget[$Level])) {
			$GrpPrdBudget[$Level] = 0;
		}
		$GrpActual[$Level] += $MyRow['monthactual'];
		$GrpBudget[$Level] += $MyRow['monthbudget'];
		$GrpPrdActual[$Level] += $AccountPeriodActual;
		$GrpPrdBudget[$Level] += $AccountPeriodBudget;

		$CheckMonth += $MyRow['monthactual'];
		$CheckBudgetMonth += $MyRow['monthbudget'];
		$CheckPeriodActual += $AccountPeriodActual;
		$CheckPeriodBudget += $AccountPeriodBudget;

		$ActEnquiryURL = '<a href="' . $RootPath . '/GLAccountInquiry.php?FromPeriod=' . $_POST['FromPeriod'] . '&amp;ToPeriod=' . $_POST['ToPeriod'] . '&amp;Account=' . $MyRow['accountcode'] . '&amp;Show=Yes">' . $MyRow['accountcode'] . '</a>';

		printf('<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>', $ActEnquiryURL, htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false), locale_number_format($MyRow['monthactual'], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($MyRow['monthbudget'], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($AccountPeriodActual, $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($AccountPeriodBudget, $_SESSION['CompanyRecord']['decimalplaces']));

		++$j;
	}
	//end of while loop


	if ($ActGrp != '') { //so its not the first account group of the first account displayed
		if ($MyRow['parentgroupname'] == $ActGrp) {
			$Level++;
			$ParentGroups[$Level] = $MyRow['groupname'];
		} elseif ($ParentGroups[$Level] == $MyRow['parentgroupname']) {
			printf('<tr>
					<td colspan="2"><i>%s ' . _('Total') . ' </i></td>
					<td class="number"><i>%s</i></td>
					<td class="number"><i>%s</i></td>
					<td class="number"><i>%s</i></td>
					<td class="number"><i>%s</i></td>
					</tr>', $ParentGroups[$Level], locale_number_format($GrpActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($GrpBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']));

			$GrpActual[$Level] = 0;
			$GrpBudget[$Level] = 0;
			$GrpPrdActual[$Level] = 0;
			$GrpPrdBudget[$Level] = 0;
			$ParentGroups[$Level] = $MyRow['groupname'];
		} else {
			do {
				printf('<tr>
						<td colspan="2"><i>%s ' . _('Total') . ' </i></td>
						<td class="number"><i>%s</i></td>
						<td class="number"><i>%s</i></td>
						<td class="number"><i>%s</i></td>
						<td class="number"><i>%s</i></td>
						</tr>', $ParentGroups[$Level], locale_number_format($GrpActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($GrpBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']));

				$GrpActual[$Level] = 0;
				$GrpBudget[$Level] = 0;
				$GrpPrdActual[$Level] = 0;
				$GrpPrdBudget[$Level] = 0;
				$ParentGroups[$Level] = '';
				$Level--;

				++$j;
			} while (isset($ParentGroups[$Level]) and ($MyRow['groupname'] != $ParentGroups[$Level] and $Level > 0));

			if ($Level > 0) {
				printf('<tr>
						<td colspan="2"><i>%s ' . _('Total') . ' </i></td>
						<td class="number"><i>%s</i></td>
						<td class="number"><i>%s</i></td>
						<td class="number"><i>%s</i></td>
						<td class="number"><i>%s</i></td>
						</tr>', $ParentGroups[$Level], locale_number_format($GrpActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($GrpBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($GrpPrdBudget[$Level], $_SESSION['CompanyRecord']['decimalplaces']));

				$GrpActual[$Level] = 0;
				$GrpBudget[$Level] = 0;
				$GrpPrdActual[$Level] = 0;
				$GrpPrdBudget[$Level] = 0;
				$ParentGroups[$Level] = '';
			} else {
				$Level = 1;
			}
		}
	}



	printf('<tr>
				<td colspan="2"><b>' . _('Check Totals') . '</b></td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
			</tr>', locale_number_format($CheckMonth, $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($CheckBudgetMonth, $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($CheckPeriodActual, $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($CheckPeriodBudget, $_SESSION['CompanyRecord']['decimalplaces']));
	echo '</tbody>';
	echo '</table>';
	echo '<div class="centre"><input type="submit" name="SelectADifferentPeriod" value="' . _('Select A Different Period') . '" /></div>';
}
echo '</form>';
include('includes/footer.inc');

?>