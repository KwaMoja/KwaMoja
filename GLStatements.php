<?php
// GLStatements.php
// Shows a set of financial statements.
// This program is under the GNU General Public License, last version. 2016-10-08.
// This creative work is under the CC BY-NC-SA, last version. 2016-10-08.
/*
Info about financial statements: IAS 1 - Presentation of Financial Statements.
Parameters:
	PeriodFrom: Select the beginning of the reporting period.
	PeriodTo: Select the end of the reporting period.
	Period: Select a period instead of using the beginning and end of the reporting period.
	ShowBudget: Check this box to show the budget.
	ShowDetail: Check this box to show all accounts instead a summary.
	ShowZeroBalance: Check this box to include accounts with zero balance.
	ShowFinancialPosition: Check this box to show the statement of financial position as at the end and at the beginning of the period;
	ShowComprehensiveIncome: Check this box to show the statement of comprehensive income;
	ShowChangesInEquity: Check this box to show the statement of changes in equity;
	ShowCashFlows: Check this box to show the statement of cash flows;
	ShowNotes: Check this box to show the notes that summarize the significant accounting policies and other explanatory information.
	NewReport: Click this button to start a new report.
	IsIncluded: Parameter to indicate that a script is included within another.
*/
// BEGIN: Functions division ===================================================
// END: Functions division =====================================================
// BEGIN: Data division ========================================================
$Title = _('Financial Statements');
$ViewTopic = 'GeneralLedger';
$BookMark = 'GLStatements';
// END: Data division ==========================================================
// BEGIN: Procedure division ===================================================
include ('includes/session.php');
include ('includes/header.php');

// Merges gets into posts:
if (isset($_GET['PeriodFrom'])) {
	$_POST['PeriodFrom'] = $_GET['PeriodFrom'];
}
if (isset($_GET['PeriodTo'])) {
	$_POST['PeriodTo'] = $_GET['PeriodTo'];
}
if (isset($_GET['Period'])) {
	$_POST['Period'] = $_GET['Period'];
}
if (isset($_GET['ShowBudget'])) {
	$_POST['ShowBudget'] = $_GET['ShowBudget'];
}
if (isset($_GET['ShowZeroBalance'])) {
	$_POST['ShowZeroBalance'] = $_GET['ShowZeroBalance'];
}
if (isset($_GET['ShowFinancialPosition'])) {
	$_POST['ShowFinancialPosition'] = $_GET['ShowFinancialPosition'];
}
if (isset($_GET['ShowComprehensiveIncome'])) {
	$_POST['ShowComprehensiveIncome'] = $_GET['ShowComprehensiveIncome'];
}
if (isset($_GET['ShowChangesInEquity'])) {
	$_POST['ShowChangesInEquity'] = $_GET['ShowChangesInEquity'];
}
if (isset($_GET['ShowCashFlows'])) {
	$_POST['ShowCashFlows'] = $_GET['ShowCashFlows'];
}
if (isset($_GET['ShowNotes'])) {
	$_POST['ShowNotes'] = $_GET['ShowNotes'];
}
if (isset($_GET['NewReport'])) {
	$_POST['NewReport'] = $_GET['NewReport'];
}
// Sets PeriodFrom and PeriodTo from Period:
if (isset($_POST['Period']) and $_POST['Period'] != '') {
	$_POST['PeriodFrom'] = ReportPeriod($_POST['Period'], 'From');
	$_POST['PeriodTo'] = ReportPeriod($_POST['Period'], 'To');
}
// Validates the data submitted in the form:
if (!isset($_POST['PeriodFrom'])) {
	$BeginMonth = ($_SESSION['YearEnd'] == 12 ? 1 : $_SESSION['YearEnd'] + 1); // Sets January as the month that follows December.
	if ($BeginMonth <= date('n')) { // It is a month in the current year.
		$BeginDate = mktime(0, 0, 0, $BeginMonth, 1, date('Y'));
	} else { // It is a month in the previous year.
		$BeginDate = mktime(0, 0, 0, $BeginMonth, 1, date('Y') - 1);
	}
	$_POST['PeriodFrom'] = GetPeriod(date($_SESSION['DefaultDateFormat'], $BeginDate));
}
if (!isset($_POST['PeriodTo'])) {
	$_POST['PeriodTo'] = GetPeriod(date($_SESSION['DefaultDateFormat']));
}
if ($_POST['PeriodTo'] - $_POST['PeriodFrom'] + 1 > 12) {
	// The reporting period is greater than 12 months.
	$_POST['NewReport'] = 'on';
	prnMsg(_('The period should be 12 months or less in duration. Please select an alternative period range.'), 'error');
}
if ($_POST['PeriodFrom'] > $_POST['PeriodTo']) {
	// The beginning is after the end.
	$_POST['NewReport'] = 'on';
	prnMsg(_('The beginning of the period should be before or equal to the end of the period. Please reselect the reporting period.'), 'error');
}
if (isset($_POST['Submit']) and !isset($_POST['ShowFinancialPosition']) and !isset($_POST['ShowComprehensiveIncome']) and !isset($_POST['ShowChangesInEquity']) and !isset($_POST['ShowCashFlows']) and !isset($_POST['ShowNotes'])) {
	// No financial statement was selected.
	$_POST['NewReport'] = 'on';
	prnMsg(_('You must select at least one financial statement. Please select financial statements.'), 'error');
}
// Main code:
if (isset($_POST['Submit']) and !isset($_POST['NewReport'])) {
	// If PeriodFrom and PeriodTo are set and it is not a NewReport, generates the report:
	echo '<p class="page_title_text">
			<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/gl.png" title="', $Title, '" /> ', $Title, '
		</p>', // Page title, reporting statement.
	stripslashes($_SESSION['CompanyRecord']['coyname']); // Page title, reporting entity.
	$Result = DB_query('SELECT lastdate_in_period FROM `periods` WHERE `periodno`=' . $_POST['PeriodFrom']);
	$PeriodFromName = DB_fetch_array($Result);
	$Result = DB_query('SELECT lastdate_in_period FROM `periods` WHERE `periodno`=' . $_POST['PeriodTo']);
	$PeriodToName = DB_fetch_array($Result);
	echo _('From'), ' ', MonthAndYearFromSQLDate($PeriodFromName['lastdate_in_period']), ' ', _('to'), ' ', MonthAndYearFromSQLDate($PeriodToName['lastdate_in_period']), '<br />'; // Page title, reporting period.
	include_once ('includes/CurrenciesArray.php'); // Array to retrieve currency name.
	echo _('All amounts stated in'), ': ', _($CurrencyName[$_SESSION['CompanyRecord']['currencydefault']]), '</p>'; // Page title, reporting presentation currency and level of rounding used.
	echo '<p>', _('In this set of financial statements:');
	if (isset($_POST['ShowFinancialPosition'])) {
		echo _('Statement of financial position'), '<br />';
	} else {
		echo '<br />* ';
	}
	if (isset($_POST['ShowComprehensiveIncome'])) {
		echo _('Statement of comprehensive income'), '<br />';
	} else {
		echo '<br />* ';
	}
	if (isset($_POST['ShowChangesInEquity'])) {
		echo _('Statement of changes in equity'), '<br />';
	} else {
		echo '<br />* ';
	}
	if (isset($_POST['ShowCashFlows'])) {
		echo _('Statement of cash flows'), '<br />';
	} else {
		echo '<br />* ';
	}
	if (isset($_POST['ShowNotes'])) {
		echo _('Notes'), '<br />';
	} else {
		echo '<br />* ';
	}
	echo '</p>';
	$IsIncluded = true;
	$PageBreak = '<hr class="PageBreak"/>' . chr(12); // Marker to indicate that the content that follows is part of a new page.
	// Displays the statements using the corresponding scripts:
	if (isset($_POST['ShowFinancialPosition']) and file_exists($RootPath . '/GLBalanceSheet.php')) {
		$_POST['ShowDetail'] = 'Detailed';
		echo $PageBreak;
		include ('GLBalanceSheet.php');
	}
	if (isset($_POST['ShowComprehensiveIncome']) and file_exists($RootPath . '/GLProfit_Loss.php')) {
		$_POST['ShowDetail'] = 'Detailed';
		echo $PageBreak;
		include ('GLProfit_Loss.php');
	}
	if (isset($_POST['ShowChangesInEquity']) and file_exists($RootPath . '/GLChangesInEquity.php')) {
		echo $PageBreak;
		include ('GLChangesInEquity.php');
	}
	if (isset($_POST['ShowCashFlows']) and file_exists($RootPath . '/GLCashFlowsIndirect.php')) {
		echo $PageBreak;
		include ('GLCashFlowsIndirect.php');
	}
	if (isset($_POST['ShowNotes']) and file_exists($RootPath . '/GLNotes.php')) {
		echo $PageBreak;
		include ('GLNotes.php');
	}
	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />';
	// Resend report parameters:
	echo '<input name="PeriodFrom" type="hidden" value="', $_POST['PeriodFrom'], '" />';
	echo '<input name="PeriodTo" type="hidden" value="', $_POST['PeriodTo'], '" />';
	echo '<input name="ShowBudget" type="hidden" value="', $_POST['ShowBudget'], '" />';
	echo '<input name="ShowZeroBalance" type="hidden" value="', $_POST['ShowZeroBalance'], '" />';
	echo '<input name="ShowFinancialPosition" type="hidden" value="', $_POST['ShowFinancialPosition'], '" />';
	echo '<input name="ShowComprehensiveIncome" type="hidden" value="', $_POST['ShowComprehensiveIncome'], '" />';
	echo '<input name="ShowChangesInEquity" type="hidden" value="', $_POST['ShowChangesInEquity'], '" />';
	echo '<input name="ShowCashFlows" type="hidden" value="', $_POST['ShowCashFlows'], '" />';
	echo '<input name="ShowNotes" type="hidden" value="', $_POST['ShowNotes'], '" />';

	echo '<div class="centre noprint">
			<input type="submit" name="NewForm" value="', _('New Report'), '" />
		</div>
	</form>';
} else {
	// If PeriodFrom or PeriodTo are NOT set or it is a NewReport, shows a parameters input form:
	echo '<p class="page_title_text">
			<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/printer.png" title="', $Title, '" /> ', $Title, '
		</p>'; // Page title.
	echo '<div class="page_help_text">', _('Shows a set of financial statements.') . '<br />' . _('A complete set of financial statements comprises:(a) a statement of financial position as at the end and at the beginning of the period;(b) a statement of comprehensive income for the period;(c) a statement of changes in equity for the period;(d) a statement of cash flows for the period; and(e) notes that summarize the significant accounting policies and other explanatory information.') . '<br />' . _('KwaMoja is an accrual based system (not a cash based system). Accrual systems include items when they are invoiced to the customer, and when expenses are owed based on the supplier invoice date.'), '</div>';
	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />';

	echo '<fieldset>
			<legend>', _('Report Parameters'), '</legend>';

	echo '<field>
			<label for="PeriodFrom">', _('Select period from'), '</label>
			<select id="PeriodFrom" name="PeriodFrom" required="required" autofocus="autofocus">';
	$Periods = DB_query('SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno ASC');
	while ($MyRow = DB_fetch_array($Periods)) {
		if ($MyRow['periodno'] == $_POST['PeriodFrom']) {
			echo '<option selected="selected" value="', $MyRow['periodno'], '">', MonthAndYearFromSQLDate($MyRow['lastdate_in_period']), '</option>';
		} else {
			echo '<option value="', $MyRow['periodno'], '">', MonthAndYearFromSQLDate($MyRow['lastdate_in_period']), '</option>';
		}
	}
	echo '</select>
		<fieldhelp>', _('Select the beginning of the reporting period'), '</fieldhelp>
	</field>';

	// Select period to:
	echo '<field>
			<label for="PeriodTo">', _('Select period to'), '</label>
			<select id="PeriodTo" name="PeriodTo" required="required">';
	DB_data_seek($Periods, 0);
	while ($MyRow = DB_fetch_array($Periods)) {
		if ($MyRow['periodno'] == $_POST['PeriodTo']) {
			echo '<option selected="selected" value="', $MyRow['periodno'], '">', MonthAndYearFromSQLDate($MyRow['lastdate_in_period']), '</option>';
		} else {
			echo '<option value="', $MyRow['periodno'], '">', MonthAndYearFromSQLDate($MyRow['lastdate_in_period']), '</option>';
		}
	}
	echo '</select>
		<fieldhelp>', _('Select the end of the reporting period'), '</fieldhelp>
	</field>';
	// OR Select period:
	if (!isset($_POST['Period'])) {
		$_POST['Period'] = '';
	}
	echo '<h3>', _('OR'), '</h3>';

	echo '<field>
			<label for="period">', _('Select Period'), ':</label>
			', ReportPeriodList($_POST['Period'], array('l', 't')), '<fieldhelp>', _('Select a period instead of using the beginning and end of the reporting period.'), '</fieldhelp>
		</field>';

	// Show the budget:
	if (isset($_POST['ShowBudget']) and $_POST['ShowBudget'] != '') {
		$Checked = ' checked="checked" ';
	} else {
		$Checked = ' ';
	}
	echo '<field>
			<label for="ShowBudget">', _('Show the budget'), ':</label>
			<input', $Checked, ' id="ShowBudget" name="ShowBudget" type="checkbox" />
			<fieldhelp>', _('Check this box to show the budget.'), '</fieldhelp>
		</field>';

	// Show accounts with zero balance:
	if (isset($_POST['ShowZeroBalance']) and $_POST['ShowZeroBalance'] != '') {
		$Checked = ' checked="checked" ';
	} else {
		$Checked = ' ';
	}
	echo '<field>
			<label for="ShowZeroBalance">', _('Show accounts with zero balance'), '</label>
			<td><input', $Checked, ' id="ShowZeroBalance" name="ShowZeroBalance" type="checkbox" />
			<fieldhelp>', _('Check this box to include accounts with zero balance'), '</fieldhelp>
		</field>';

	// Show the statement of financial position:
	if (isset($_POST['ShowFinancialPosition']) and $_POST['ShowFinancialPosition'] != '') {
		$Checked = ' checked="checked" ';
	} else {
		$Checked = ' ';
	}
	echo '<field>
			<label for="ShowFinancialPosition">', _('Show the statement of financial position'), '</label>
			<input', $Checked, ' id="ShowFinancialPosition" name="ShowFinancialPosition" type="checkbox" />
			<fieldhelp>', _('Check this box to show the statement of financial position.'), '</fieldhelp>
		</field>';

	// Show the statement of comprehensive income:
	if (isset($_POST['ShowComprehensiveIncome']) and $_POST['ShowComprehensiveIncome'] != '') {
		$Checked = ' checked="checked" ';
	} else {
		$Checked = ' ';
	}
	echo '<field>
			<label for="ShowComprehensiveIncome">', _('Show the statement of comprehensive income'), '</label>
			<input', $Checked, ' id="ShowComprehensiveIncome" name="ShowComprehensiveIncome" type="checkbox" />
			<fieldhelp>', _('Check this box to show the statement of comprehensive income.'), '</fieldhelp>
		</field>';

	// Show the statement of changes in equity:
	if (isset($_POST['ShowChangesInEquity']) and $_POST['ShowChangesInEquity'] != '') {
		$Checked = ' checked="checked" ';
	} else {
		$Checked = ' ';
	}
	echo '<field>
			<label for="ShowChangesInEquity">', _('Show the statement of changes in equity'), '</label>
			<input', $Checked, ' id="ShowChangesInEquity" name="ShowChangesInEquity" type="checkbox" />
			<fieldhelp>', _('Check this box to show the statement of changes in equity.'), '</fieldhelp>
		</field>';

	// Show the statement of cash flows:
	if (isset($_POST['ShowCashFlows']) and $_POST['ShowCashFlows'] != '') {
		$Checked = ' checked="checked" ';
	} else {
		$Checked = ' ';
	}
	echo '<field>
			<label for="ShowCashFlows">', _('Show the statement of cash flows'), '</label>
			<input', $Checked, ' id="ShowCashFlows" name="ShowCashFlows" type="checkbox" />
			<fieldhelp>', _('Check this box to show the statement of cash flows.'), '</fieldhelp>
		</field>';

	// Show the notes:
	if (isset($_POST['ShowNotes']) and $_POST['ShowNotes'] != '') {
		$Checked = ' checked="checked" ';
	} else {
		$Checked = ' ';
	}
	echo '<field>
			<label for="ShowNotes">', _('Show the notes'), '</label>
			<input', $Checked, ' id="ShowNotes" name="ShowNotes" type="checkbox" />
			<fieldhelp>', _('Check this box to show the notes that summarize the significant accounting policies and other explanatory information'), '</fieldhel>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="Submit" value="', _('Submit'), '" />
		</div';

	echo '</form>';
}
include ('includes/footer.php');
?>