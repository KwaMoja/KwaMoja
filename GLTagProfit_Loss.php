<?php

include('includes/session.inc');
$Title = _('Income and Expenditure by Tag');
include('includes/SQL_CommonFunctions.inc');
include('includes/AccountSectionsDef.inc'); // This loads the $Sections variable


if (isset($_POST['FromPeriod']) and ($_POST['FromPeriod'] > $_POST['ToPeriod'])) {
	prnMsg(_('The selected period from is actually after the period to') . '! ' . _('Please reselect the reporting period'), 'error');
	$_POST['SelectADifferentPeriod'] = 'Select A Different Period';
}

if ((!isset($_POST['FromPeriod']) and !isset($_POST['ToPeriod'])) or isset($_POST['SelectADifferentPeriod'])) {

	$ViewTopic = 'GeneralLedger';
	$BookMark = 'TagReports';
	include('includes/header.inc');
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<p class="page_title_text" >
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/printer.png" title="' . $Title . '" alt="' . $Title . '" />' . ' ' . $Title . '
		</p>';

	if (Date('m') > $_SESSION['YearEnd']) {
		/*Dates in SQL format */
		$DefaultFromDate = Date('Y-m-d', Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y')));
		$FromDate = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y')));
	} else {
		$DefaultFromDate = Date('Y-m-d', Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y') - 1));
		$FromDate = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y') - 1));
	}
	$Period = GetPeriod($FromDate);

	/*Show a form to allow input of criteria for profit and loss to show */
	echo '<table class="selection" summary="' . _('Input Criteria for Report') . '">
			<tr>
				<td>' . _('Select Period From') . ':</td>
				<td><select name="FromPeriod">';

	$SQL = "SELECT periodno,
					lastdate_in_period
			FROM periods
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
		$LastDate = date('Y-m-d', mktime(0, 0, 0, Date('m') + 1, 0, Date('Y')));
		$SQL = "SELECT periodno FROM periods where lastdate_in_period = '" . $LastDate . "'";
		$MaxPrd = DB_query($SQL);
		$MaxPrdrow = DB_fetch_row($MaxPrd);
		$DefaultToPeriod = (int) ($MaxPrdrow[0]);

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
			echo '<option value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
		}
	}
	echo '</select></td></tr>';
	//Select the tag
	echo '<tr>
			<td>' . _('Select tag') . '</td>
			<td><select name="tag">';

	$SQL = "SELECT tagref,
				tagdescription
				FROM tags
				ORDER BY tagref";

	$Result = DB_query($SQL);
	echo '<option value="0">0 - ' . _('None') . '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['tag']) and $_POST['tag'] == $MyRow['tagref']) {
			echo '<option selected="selected" value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
		}
	}
	echo '</select></td></tr>';
	// End select tag

	echo '<tr>
			<td>' . _('Detail Or Summary') . ':</td>
			<td><select name="Detail">
				<option selected="selected" value="Summary">' . _('Summary') . '</option>
				<option selected="selected" value="Detailed">' . _('All Accounts') . '</option>
				</select>
			</td>
		</tr>
		</table>
		<br />
		<div class="centre">
			<input type="submit" name="ShowPL" value="' . _('Show Statement of Income and Expenditure') . '" />
			<br />
			<br />
			<input type="submit" name="PrintPDF" value="' . _('PrintPDF') . '" />
		</div>';

	/*Now do the posting while the user is thinking about the period to select */

	include('includes/GLPostings.inc');

} else if (isset($_POST['PrintPDF'])) {

	$tagsql = "SELECT tagdescription FROM tags WHERE tagref='" . $_POST['tag'] . "'";
	$tagresult = DB_query($tagsql);
	$tagrow = DB_fetch_array($tagresult);
	$Tag = $tagrow['tagdescription'];
	include('includes/PDFStarter.php');
	$PDF->addInfo('Title', _('Income and Expenditure'));
	$PDF->addInfo('Subject', _('Income and Expenditure'));
	$PageNumber = 0;
	$FontSize = 10;
	$line_height = 12;

	$NumberOfMonths = $_POST['ToPeriod'] - $_POST['FromPeriod'] + 1;

	if ($NumberOfMonths > 12) {
		include('includes/header.inc');
		prnMsg(_('A period up to 12 months in duration can be specified') . ' - ' . _('the system automatically shows a comparative for the same period from the previous year') . ' - ' . _('it cannot do this if a period of more than 12 months is specified') . '. ' . _('Please select an alternative period range'), 'error');
		include('includes/footer.inc');
		exit;
	}

	$SQL = "SELECT lastdate_in_period
			FROM periods
			WHERE periodno='" . $_POST['ToPeriod'] . "'";
	$PrdResult = DB_query($SQL);
	$MyRow = DB_fetch_row($PrdResult);
	$PeriodToDate = MonthAndYearFromSQLDate($MyRow[0]);


	$SQL = "SELECT accountgroups.sectioninaccounts,
					accountgroups.groupname,
					accountgroups.parentgroupname,
					gltrans.account ,
					chartmaster.accountname,
					Sum(CASE WHEN (gltrans.periodno>='" . $_POST['FromPeriod'] . "' and gltrans.periodno<='" . $_POST['ToPeriod'] . "') THEN gltrans.amount ELSE 0 END) AS TotalAllPeriods,
					Sum(CASE WHEN (gltrans.periodno='" . $_POST['ToPeriod'] . "') THEN gltrans.amount ELSE 0 END) AS TotalThisPeriod
			FROM chartmaster
			INNER JOIN accountgroups
				ON chartmaster.groupcode = accountgroups.groupcode
				AND chartmaster.language = accountgroups.language
			INNER JOIN gltrans ON chartmaster.accountcode= gltrans.account
			INNER JOIN glaccountusers ON glaccountusers.accountcode=chartmaster.accountcode AND glaccountusers.userid='" .  $_SESSION['UserID'] . "' AND glaccountusers.canview=1
			WHERE accountgroups.pandl=1
				AND gltrans.tag='" . $_POST['tag'] . "'
				AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
			GROUP BY accountgroups.sectioninaccounts,
					accountgroups.groupname,
					accountgroups.parentgroupname,
					gltrans.account,
					chartmaster.accountname,
					accountgroups.sequenceintb
			ORDER BY accountgroups.sectioninaccounts,
					accountgroups.sequenceintb,
					accountgroups.groupname,
					gltrans.account";

	$AccountsResult = DB_query($SQL);

	if (DB_error_no() != 0) {
		$Title = _('Income and Expenditure') . ' - ' . _('Problem Report') . '....';
		include('includes/header.inc');
		prnMsg(_('No general ledger accounts were returned by the SQL because') . ' - ' . DB_error_msg());
		echo '<br />
				<a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($Debug == 1) {
			echo '<br />' . $SQL;
		}
		include('includes/footer.inc');
		exit;
	}
	if (DB_num_rows($AccountsResult) == 0) {
		$Title = _('Print Income and Expenditure Error');
		include('includes/header.inc');
		echo '<br />';
		prnMsg(_('There were no entries to print out for the selections specified'), 'info');
		echo '<br />
				<a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		include('includes/footer.inc');
		exit;
	}

	include('includes/PDFTagProfitAndLossPageHeader.inc');

	$Section = '';
	$SectionPrdActual = 0;

	$ActGrp = '';
	$ParentGroups = array();
	$Level = 0;
	$ParentGroups[$Level] = '';
	$GrpPrdActual = array(
		0
	);
	$PeriodProfitLoss = 0;
	while ($MyRow = DB_fetch_array($AccountsResult)) {

		// Print heading if at end of page
		if ($YPos < ($Bottom_Margin)) {
			include('includes/PDFTagProfitAndLossPageHeader.inc');
		}

		if ($MyRow['groupname'] != $ActGrp) {
			if ($ActGrp != '') {
				if ($MyRow['parentgroupname'] != $ActGrp) {
					while ($MyRow['groupname'] != $ParentGroups[$Level] and $Level > 0) {
						if ($_POST['Detail'] == 'Detailed') {
							$ActGrpLabel = $ParentGroups[$Level] . ' ' . _('total');
						} else {
							$ActGrpLabel = $ParentGroups[$Level];
						}
						if ($Section == 1) {
							/*Income */
							$LeftOvers = $PDF->addTextWrap($Left_Margin + ($Level * 10), $YPos, 200 - ($Level * 10), $FontSize, $ActGrpLabel);
							$LeftOvers = $PDF->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
							$YPos -= (2 * $line_height);
						} else {
							/*Costs */
							$LeftOvers = $PDF->addTextWrap($Left_Margin + ($Level * 10), $YPos, 200 - ($Level * 10), $FontSize, $ActGrpLabel);
							$LeftOvers = $PDF->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, locale_number_format(-$GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
							$YPos -= (2 * $line_height);
						}
						$GrpPrdLY[$Level] = 0;
						$GrpPrdActual[$Level] = 0;
						$GrpPrdBudget[$Level] = 0;
						$ParentGroups[$Level] = '';
						$Level--;
						// Print heading if at end of page
						if ($YPos < ($Bottom_Margin + (2 * $line_height))) {
							include('includes/PDFTagProfitAndLossPageHeader.inc');
						}
					} //end of loop
					//still need to print out the group total for the same level
					if ($_POST['Detail'] == 'Detailed') {
						$ActGrpLabel = $ParentGroups[$Level] . ' ' . _('total');
					} else {
						$ActGrpLabel = $ParentGroups[$Level];
					}
					if ($Section == 1) {
						/*Income */
						$LeftOvers = $PDF->addTextWrap($Left_Margin + ($Level * 10), $YPos, 200 - ($Level * 10), $FontSize, $ActGrpLabel);
						$PDF->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
						$YPos -= (2 * $line_height);
					} else {
						/*Costs */
						$LeftOvers = $PDF->addTextWrap($Left_Margin + ($Level * 10), $YPos, 200 - ($Level * 10), $FontSize, $ActGrpLabel);
						$LeftOvers = $PDF->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, locale_number_format(-$GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
						$YPos -= (2 * $line_height);
					}
					$GrpPrdActual[$Level] = 0;
					$ParentGroups[$Level] = '';
				}
			}
		}

		// Print heading if at end of page
		if ($YPos < ($Bottom_Margin + (2 * $line_height))) {
			include('includes/PDFTagProfitAndLossPageHeader.inc');
		}

		if ($MyRow['sectioninaccounts'] != $Section) {

			$PDF->setFont('', 'B');
			$FontSize = 10;
			if ($Section != '') {
				$PDF->line($Left_Margin + 310, $YPos + $line_height, $Left_Margin + 500, $YPos + $line_height);
				$PDF->line($Left_Margin + 310, $YPos, $Left_Margin + 500, $YPos);
				if ($Section == 1) {
					/*Income*/

					$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 200, $FontSize, $Sections[$Section]);
					$LeftOvers = $PDF->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, locale_number_format($SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']), 'right');
					$YPos -= (2 * $line_height);

					$TotalIncome = -$SectionPrdActual;
					$TotalBudgetIncome = -$SectionPrdBudget;
					$TotalLYIncome = -$SectionPrdLY;
				} else {
					$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 200, $FontSize, $Sections[$Section]);
					$LeftOvers = $PDF->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, locale_number_format(-$SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']), 'right');
					$YPos -= (2 * $line_height);
				}
				if ($Section == 2) {
					/*Cost of Sales - need sub total for Gross Profit*/
					$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 200, $FontSize, _('Gross Profit'));
					$LeftOvers = $PDF->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, locale_number_format($TotalIncome - $SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']), 'right');
					$PDF->line($Left_Margin + 310, $YPos + $line_height, $Left_Margin + 500, $YPos + $line_height);
					$PDF->line($Left_Margin + 310, $YPos, $Left_Margin + 500, $YPos);
					$YPos -= (2 * $line_height);

					if ($TotalIncome != 0) {
						$PrdGPPercent = 100 * ($TotalIncome - $SectionPrdActual) / $TotalIncome;
					} else {
						$PrdGPPercent = 0;
					}
					$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 200, $FontSize, _('Gross Profit Percent'));
					$LeftOvers = $PDF->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, locale_number_format($PrdGPPercent, 1) . '%', 'right');
					$YPos -= (2 * $line_height);
				}
			}
			$SectionPrdActual = 0;
			$SectionPrdBudget = 0;
			$SectionPrdLY = 0;

			$Section = $MyRow['sectioninaccounts'];

			if ($_POST['Detail'] == 'Detailed') {
				$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 200, $FontSize, $Sections[$MyRow['sectioninaccounts']]);
				$YPos -= (2 * $line_height);
			}
			$FontSize = 8;
			$PDF->setFont('', '');
		}

		if ($MyRow['groupname'] != $ActGrp) {
			if ($MyRow['parentgroupname'] == $ActGrp and $ActGrp != '') { //adding another level of nesting
				$Level++;
			}
			$ActGrp = $MyRow['groupname'];
			$ParentGroups[$Level] = $ActGrp;
			if ($_POST['Detail'] == 'Detailed') {
				$FontSize = 10;
				$PDF->setFont('', 'B');
				$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 200, $FontSize, $MyRow['groupname']);
				$YPos -= (2 * $line_height);
				$FontSize = 8;
				$PDF->setFont('', '');
			}
		}

		$AccountPeriodActual = $MyRow['TotalAllPeriods'];
		$PeriodProfitLoss += $AccountPeriodActual;

		for ($i = 0; $i <= $Level; $i++) {
			//			$GrpPrdLY[$i] +=$AccountPeriodLY;
		}


		$SectionPrdActual += $AccountPeriodActual;

		if ($_POST['Detail'] == _('Detailed')) {
			$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 60, $FontSize, $MyRow['account']);
			$LeftOvers = $PDF->addTextWrap($Left_Margin + 60, $YPos, 190, $FontSize, $MyRow['accountname']);
			if ($Section == 1) {
				/*Income*/
				$LeftOvers = $PDF->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, locale_number_format($AccountPeriodActual, $_SESSION['CompanyRecord']['decimalplaces']), 'right');
			} else {
				$LeftOvers = $PDF->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, locale_number_format(-$AccountPeriodActual, $_SESSION['CompanyRecord']['decimalplaces']), 'right');
			}
			$YPos -= $line_height;
		}
	}
	//end of loop

	if ($ActGrp != '') {

		if ($MyRow['parentgroupname'] != $ActGrp) {

			while ($MyRow['groupname'] != $ParentGroups[$Level] and $Level > 0) {
				if ($_POST['Detail'] == 'Detailed') {
					$ActGrpLabel = $ParentGroups[$Level] . ' ' . _('total');
				} else {
					$ActGrpLabel = $ParentGroups[$Level];
				}
				if ($Section == 1) {
					/*Income */
					$LeftOvers = $PDF->addTextWrap($Left_Margin + ($Level * 10), $YPos, 200 - ($Level * 10), $FontSize, $ActGrpLabel);
					$LeftOvers = $PDF->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
					$YPos -= (2 * $line_height);
				} else {
					/*Costs */
					$LeftOvers = $PDF->addTextWrap($Left_Margin + ($Level * 10), $YPos, 200 - ($Level * 10), $FontSize, $ActGrpLabel);
					$LeftOvers = $PDF->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, locale_number_format(-$GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
					$YPos -= (2 * $line_height);
				}
				$GrpPrdActual[$Level] = 0;
				$ParentGroups[$Level] = '';
				$Level--;
				// Print heading if at end of page
				if ($YPos < ($Bottom_Margin + (2 * $line_height))) {
					include('includes/PDFTagProfitAndLossPageHeader.inc');
				}
			}
			//still need to print out the group total for the same level
			if ($_POST['Detail'] == 'Detailed') {
				$ActGrpLabel = $ParentGroups[$Level] . ' ' . _('total');
			} else {
				$ActGrpLabel = $ParentGroups[$Level];
			}
			if ($Section == 1) {
				/*Income */
				$LeftOvers = $PDF->addTextWrap($Left_Margin + ($Level * 10), $YPos, 200 - ($Level * 10), $FontSize, $ActGrpLabel);
				$PDF->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
				$YPos -= (2 * $line_height);
			} else {
				/*Costs */
				$LeftOvers = $PDF->addTextWrap($Left_Margin + ($Level * 10), $YPos, 200 - ($Level * 10), $FontSize, $ActGrpLabel);
				$LeftOvers = $PDF->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, locale_number_format(-$GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
				$YPos -= (2 * $line_height);
			}
			$GrpPrdActual[$Level] = 0;
			$ParentGroups[$Level] = '';
		}
	}
	// Print heading if at end of page
	if ($YPos < ($Bottom_Margin + (2 * $line_height))) {
		include('includes/PDFTagProfitAndLossPageHeader.inc');
	}
	if ($Section != '') {

		$PDF->setFont('', 'B');
		$PDF->line($Left_Margin + 310, $YPos + 10, $Left_Margin + 500, $YPos + 10);
		$PDF->line($Left_Margin + 310, $YPos, $Left_Margin + 500, $YPos);

		if ($Section == 1) {
			/*Income*/
			$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 200, $FontSize, $Sections[$Section]);
			$LeftOvers = $PDF->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, locale_number_format(-$SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']), 'right');
			$YPos -= (2 * $line_height);

			$TotalIncome = -$SectionPrdActual;
			$TotalBudgetIncome = -$SectionPrdBudget;
			$TotalLYIncome = -$SectionPrdLY;
		} else {
			$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 60, $FontSize, $Sections[$Section]);
			$LeftOvers = $PDF->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, locale_number_format($SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']), 'right');
			$YPos -= (2 * $line_height);
		}
		if ($Section == 2) {
			/*Cost of Sales - need sub total for Gross Profit*/
			$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 60, $FontSize, _('Gross Profit'));
			$LeftOvers = $PDF->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, locale_number_format($TotalIncome - $SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']), 'right');
			$YPos -= (2 * $line_height);

			$LeftOvers = $PDF->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, locale_number_format(100 * ($TotalIncome - $SectionPrdActual) / $TotalIncome, 1) . '%', 'right');
			$YPos -= (2 * $line_height);
		}
	}

	$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 60, $FontSize, _('Profit') . ' - ' . _('Loss'));
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 310, $YPos, 70, $FontSize, locale_number_format(-$PeriodProfitLoss, $_SESSION['CompanyRecord']['decimalplaces']), 'right');

	$PDF->line($Left_Margin + 310, $YPos + $line_height, $Left_Margin + 500, $YPos + $line_height);
	$PDF->line($Left_Margin + 310, $YPos, $Left_Margin + 500, $YPos);

	$PDF->OutputD($_SESSION['DatabaseName'] . '_' . 'Tag_Income_Statement_' . date('Y-m-d') . '.pdf');
	$PDF->__destruct();
	exit;

} else {

	$ViewTopic = 'GeneralLedger';
	$BookMark = 'TagReports';
	include('includes/header.inc');
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<input type="hidden" name="FromPeriod" value="' . $_POST['FromPeriod'] . '" />
		<input type="hidden" name="ToPeriod" value="' . $_POST['ToPeriod'] . '" />';

	$NumberOfMonths = $_POST['ToPeriod'] - $_POST['FromPeriod'] + 1;

	if ($NumberOfMonths > 12) {
		echo '<br />';
		prnMsg(_('A period up to 12 months in duration can be specified') . ' - ' . _('the system automatically shows a comparative for the same period from the previous year') . ' - ' . _('it cannot do this if a period of more than 12 months is specified') . '. ' . _('Please select an alternative period range'), 'error');
		include('includes/footer.inc');
		exit;
	}

	$SQL = "SELECT lastdate_in_period
			FROM periods
			WHERE periodno='" . $_POST['ToPeriod'] . "'";
	$PrdResult = DB_query($SQL);
	$MyRow = DB_fetch_row($PrdResult);
	$PeriodToDate = MonthAndYearFromSQLDate($MyRow[0]);


	$SQL = "SELECT accountgroups.sectioninaccounts,
					accountgroups.groupname,
					accountgroups.parentgroupname,
					gltrans.account,
					chartmaster.accountname,
					Sum(CASE WHEN (gltrans.periodno>='" . $_POST['FromPeriod'] . "' AND gltrans.periodno<='" . $_POST['ToPeriod'] . "') THEN gltrans.amount ELSE 0 END) AS TotalAllPeriods,
					Sum(CASE WHEN (gltrans.periodno='" . $_POST['ToPeriod'] . "') THEN gltrans.amount ELSE 0 END) AS TotalThisPeriod
			FROM chartmaster
			INNER JOIN accountgroups
				ON chartmaster.groupcode = accountgroups.groupcode
				AND chartmaster.language = accountgroups.language
			INNER JOIN gltrans
				ON chartmaster.accountcode= gltrans.account
			WHERE accountgroups.pandl=1
				AND gltrans.tag='" . $_POST['tag'] . "'
				AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
			GROUP BY accountgroups.sectioninaccounts,
					accountgroups.groupname,
					accountgroups.parentgroupname,
					gltrans.account,
					chartmaster.accountname
			ORDER BY accountgroups.sectioninaccounts,
					accountgroups.sequenceintb,
					accountgroups.groupname,
					gltrans.account";


	$AccountsResult = DB_query($SQL, _('No general ledger accounts were returned by the SQL because'), _('The SQL that failed was'));
	$SQL = "SELECT tagdescription FROM tags WHERE tagref='" . $_POST['tag'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);

	/*show a table of the accounts info returned by the SQL
	Account Code ,   Account Name , Month Actual, Month Budget, Period Actual, Period Budget */
	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/printer.png" title="' . _('Print') . '" alt="' . _('Print') . '" />' . ' ' . $Title . '</p>';

	echo '<table cellpadding="2" class="selection" summary="' . _('Income and Expenditure by Tag') . '">';
	echo '<tr>
			<th colspan="9">
				<div class="centre">
					<h2>
					<b>' . _('Statement of Income and Expenditure for Tag') . ' ' . $MyRow[0] . ' ' . _('during the') . ' ' . $NumberOfMonths . ' ' . _('months to') . ' ' . $PeriodToDate . '</b>
					<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/printer.png" class="PrintIcon" title="' . _('Print') . '" alt="' . _('Print') . '" onclick="window.print();" />
					</h2>
				</div>
			</th>
		</tr>';

	if ($_POST['Detail'] == 'Detailed') {
		echo '<tr>
							<th>' . _('Account') . '</th>
							<th>' . _('Account Name') . '</th>
							<th colspan="2">' . _('Period Actual') . '</th>
						</tr>';
	} else {
		/*summary */
		echo '<tr>
							<th colspan="2"></th>
							<th colspan="2">' . _('Period Actual') . '</th>
						</tr>';
	}


	$k = 0; //row colour counter
	$Section = '';
	$SectionPrdActual = 0;
	$SectionPrdLY = 0;
	$SectionPrdBudget = 0;

	$PeriodProfitLoss = 0;
	$PeriodLYProfitLoss = 0;
	$PeriodBudgetProfitLoss = 0;

	$ActGrp = '';
	$ParentGroups = array();
	$Level = 0;
	$ParentGroups[$Level] = '';
	$GrpPrdActual = array(
		0
	);
	$GrpPrdLY = array(
		0
	);
	$GrpPrdBudget = array(
		0
	);
	$TotalIncome = 0;

	while ($MyRow = DB_fetch_array($AccountsResult)) {


		if ($MyRow['groupname'] != $ActGrp) {
			if ($MyRow['parentgroupname'] != $ActGrp and $ActGrp != '') {
				while ($MyRow['groupname'] != $ParentGroups[$Level] and $Level > 0) {
					if ($_POST['Detail'] == 'Detailed') {
						echo '<tr>
							<td colspan="2"></td>
							<td colspan="6"><hr /></td>
						</tr>';
						$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level] . ' ' . _('total');
					} else {
						$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level];
					}

					if ($Section == 3) {
						/*Income */
						printf('<tr>
									<td colspan="2"><h4><i>%s </i></h4></td>
									<td></td>
									<td class="number">%s</td>
								</tr>', $ActGrpLabel, locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']));
					} else {
						/*Costs */
						printf('<tr>
									<td colspan="2"><h4><i>%s </i></h4></td>
									<td class="number">%s</td>
									<td></td>
								</tr>', $ActGrpLabel, locale_number_format(-$GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']));
					}
					$GrpPrdLY[$Level] = 0;
					$GrpPrdActual[$Level] = 0;
					$GrpPrdBudget[$Level] = 0;
					$ParentGroups[$Level] = '';
					$Level--;
				} //end while
				//still need to print out the old group totals
				if ($_POST['Detail'] == 'Detailed') {
					echo '<tr>
								<td colspan="2"></td>
								<td colspan="6"><hr /></td>
							</tr>';
					$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level] . ' ' . _('total');
				} else {
					$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level];
				}

				if ($Section == 4) {
					/*Income */
					printf('<tr>
								<td colspan="2"><h4><i>%s </i></h4></td>
								<td></td>
								<td class="number">%s</td>
							</tr>', $ActGrpLabel, locale_number_format(-$GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']));
				} else {
					/*Costs */
					printf('<tr>
								<td colspan="2"><h4><i>%s</i></h4></td>
								<td class="number">%s</td>
								<td></td>
							</tr>', $ActGrpLabel, locale_number_format(-$GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']));
				}
				$GrpPrdActual[$Level] = 0;
				$ParentGroups[$Level] = '';
			}
			++$j;
		}

		if ($MyRow['sectioninaccounts'] != $Section) {

			if ($SectionPrdLY + $SectionPrdActual + $SectionPrdBudget != 0) {
				if ($Section == 4) {
					/*Income*/

					echo '<tr>
							<td colspan="2"></td>
		  					<td><hr /></td>
							<td></td>
							<td><hr /></td>
						</tr>';

					printf('<tr>
								<td colspan="2"><h2>%s</h2></td>
								<td></td>
								<td class="number">%s</td>
							</tr>', $Sections[$Section], locale_number_format($SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']));

				} else {
					echo '<tr>
							<td colspan="2"></td>
							<td colspan="2"><hr /></td>
						</tr>';
					printf('<tr>
								<td colspan="2"><h2>%s</h2></td>
								<td></td>
								<td class="number">%s</td>
							</tr>', $Sections[$Section], locale_number_format($SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']));
				}
				if ($Section == 1) {
					$TotalIncome += $SectionPrdActual;
				}

				if ($Section == 2) {
					/*Cost of Sales - need sub total for Gross Profit*/
					echo '<tr>
							<td colspan="2"></td>
							<td colspan="5"><hr /></td>
						</tr>';
					printf('<tr>
								<td colspan="2"><h2>' . _('Gross Profit') . '</h2></td>
								<td></td>
								<td class="number">%s</td>
							</tr>', locale_number_format($TotalIncome + $SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']));

					if ($TotalIncome != 0) {
						$PrdGPPercent = 100 * ($TotalIncome + $SectionPrdActual) / $TotalIncome;
					} else {
						$PrdGPPercent = 0;
					}
					echo '<tr>
							<td colspan="2"></td>
							<td colspan="6"><hr /></td>
						</tr>';
					printf('<tr>
							<td colspan="2"><h4><i>' . _('Gross Profit Percent') . '</i></h4></td>
							<td></td>
							<td class="number"><i>%s</i></td>
							</tr><tr><td colspan="6"> </td></tr>', locale_number_format($PrdGPPercent, 1) . '%');
					++$j;
				}
			}
			$SectionPrdActual = 0;

			$Section = $MyRow['sectioninaccounts'];

			if ($_POST['Detail'] == 'Detailed') {
				printf('<tr>
							<td colspan="6"><h2><b>%s</b></h2></td>
						</tr>', $Sections[$MyRow['sectioninaccounts']]);
			}
			++$j;

		}

		if ($MyRow['groupname'] != $ActGrp) {

			if ($MyRow['parentgroupname'] == $ActGrp and $ActGrp != '') { //adding another level of nesting
				$Level++;
			}

			$ParentGroups[$Level] = $MyRow['groupname'];
			$ActGrp = $MyRow['groupname'];
			if ($_POST['Detail'] == 'Detailed') {
				printf('<tr>
							<td colspan="6"><h4><b>%s</b></h4></td>
						</tr>', $MyRow['groupname']);

			}
		}

		$AccountPeriodActual = $MyRow['TotalAllPeriods'];
		if ($Section == 4) {
			$PeriodProfitLoss -= $AccountPeriodActual;
		} else {
			$PeriodProfitLoss -= $AccountPeriodActual;
		}

		for ($i = 0; $i <= $Level; $i++) {
			if (!isset($GrpPrdActual[$i])) {
				$GrpPrdActual[$i] = 0;
			}
			$GrpPrdActual[$i] += $AccountPeriodActual;
		}
		$SectionPrdActual -= $AccountPeriodActual;

		if ($_POST['Detail'] == _('Detailed')) {

			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				++$k;
			}

			$ActEnquiryURL = '<a href="' . $RootPath . '/GLAccountInquiry.php?Period=' . $_POST['ToPeriod'] . '&amp;Account=' . $MyRow['account'] . '&amp;Show=Yes">' . $MyRow['account'] . '</a>';

			if ($Section == 4) {
				printf('<td>%s</td>
						<td>%s</td>
						<td></td>
						<td class="number">%s</td>
						</tr>', $ActEnquiryURL, htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false), locale_number_format(-$AccountPeriodActual, $_SESSION['CompanyRecord']['decimalplaces']));
			} else {
				printf('<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						</tr>', $ActEnquiryURL, htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false), locale_number_format(-$AccountPeriodActual, $_SESSION['CompanyRecord']['decimalplaces']));
			}

			++$j;
		}
	}
	//end of loop


	if ($MyRow['groupname'] != $ActGrp) {
		if ($MyRow['parentgroupname'] != $ActGrp and $ActGrp != '') {
			while ($MyRow['groupname'] != $ParentGroups[$Level] and $Level > 0) {
				if ($_POST['Detail'] == 'Detailed') {
					echo '<tr>
							<td colspan="2"></td>
							<td colspan="4"><hr /></td>
						</tr>';
					$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level] . ' ' . _('total');
				} else {
					$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level];
				}
				if ($Section == 4) {
					/*Income */
					echo '<tr>
							<td colspan="2"><h4><i>' . $ActGrpLabel . '</i></h4></td>
							<td></td>
							<td class="number">' . locale_number_format(-$GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						</tr>';
				} else {
					/*Costs */
					echo '<tr>
							<td colspan="2"><h4><i>' . $ActGrpLabel . '</i></h4></td>
							<td class="number">' . locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
						</tr>';
				}
				$GrpPrdActual[$Level] = 0;
				$ParentGroups[$Level] = '';
				$Level--;
			} //end while
			//still need to print out the old group totals
			if ($_POST['Detail'] == 'Detailed') {
				echo '<tr>
							<td colspan="2"></td>
							<td colspan="4"><hr /></td>
						</tr>';
				$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level] . ' ' . _('total');
			} else {
				$ActGrpLabel = str_repeat('___', $Level) . $ParentGroups[$Level];
			}

			if ($Section == 4) {
				/*Income */
				printf('<tr>
						<td colspan="2"><h4><i>%s</i></h4></td>
						<td></td>
						<td class="number">%s</td>
						</tr>', $ActGrpLabel, locale_number_format(-$GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']));
			} else {
				/*Costs */
				printf('<tr>
						<td colspan="2"><h4><i>%s </i></h4></td>
						<td class="number">%s</td>
						<td></td>
						</tr>', $ActGrpLabel, locale_number_format($GrpPrdActual[$Level], $_SESSION['CompanyRecord']['decimalplaces']));
			}
			$GrpPrdActual[$Level] = 0;
			$ParentGroups[$Level] = '';
		}
		++$j;
	}

	if ($MyRow['sectioninaccounts'] != $Section) {

		if ($Section == 4) {
			/*Income*/

			echo '<tr>
					<td colspan="2"></td>
					<td colspan="2"><hr /></td>
				</tr>
				<tr>
					<td colspan="2"><h2>' . $Sections[$Section] . '</h2></td>
					<td></td>
					<td class="number">' . locale_number_format($SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				</tr>';
			$TotalIncome = $SectionPrdActual;
		} else {
			echo '<tr>
					<td colspan="2"></td>
					<td colspan="2"><hr /></td>
				</tr>
				<tr>
					<td colspan="2"><h2>' . $Sections[$Section] . '</h2></td>
					<td></td>
					<td class="number">' . locale_number_format(-$SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				</tr>';
		}
		if ($Section == 2) {
			/*Cost of Sales - need sub total for Gross Profit*/
			echo '<tr>
					<td colspan="2"></td>
					<td colspan="2"><hr /></td>
				</tr>
				<tr>
					<td colspan="2"><h2>' . _('Gross Profit') . '</h2></td>
					<td></td>
					<td class="number">' . locale_number_format($TotalIncome - $SectionPrdActual, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				</tr>';

			if ($TotalIncome != 0) {
				$PrdGPPercent = 100 * ($TotalIncome - $SectionPrdActual) / $TotalIncome;
			} else {
				$PrdGPPercent = 0;
			}
			echo '<tr>
					<td colspan="2"></td>
					<td colspan="2"><hr /></td>
				</tr>
				<tr>
					<td colspan="2"><h4><i>' . _('Gross Profit Percent') . '</i></h4></td>
					<td></td>
					<td class="number"><i>' . locale_number_format($PrdGPPercent, 1) . '%</i></td>
					<td></td>
				</tr>';

			++$j;
		}

		$SectionPrdActual = 0;

		$Section = $MyRow['sectioninaccounts'];

		if ($_POST['Detail'] == 'Detailed' and isset($Sections[$MyRow['sectioninaccounts']])) {
			echo '<tr>
					<td colspan="6"><h2><b>' . $Sections[$MyRow['sectioninaccounts']] . '</b></h2></td>
				</tr>';
		}
		++$j;

	}

	echo '<tr>
			<td colspan="2"></td>
			<td colspan="2"><hr /></td>
		</tr>';

	printf('<tr style="background-color:#ffffff">
			<td colspan="2"><h2><b>' . _('Surplus') . ' - ' . _('Deficit') . '</b></h2></td>
			<td></td>
			<td class="number">%s</td>
			</tr>', locale_number_format($PeriodProfitLoss, $_SESSION['CompanyRecord']['decimalplaces']));

	echo '<tr>
			<td colspan="2"></td>
			<td colspan="4"><hr /></td>
		</tr>
		</table>
		<div class="centre">
			<input type="submit" name="SelectADifferentPeriod" value="' . _('Select A Different Period') . '" />
		</div>';
}
echo '</form>';
include('includes/footer.inc');

?>