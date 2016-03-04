<?php

$PageSecurity=1;
/* Through deviousness and cunning, this system allows shows the balance sheets
 * as at the end of any period selected - so first off need to show the input
 * of criteria screen while the user is selecting the period end of the balance
 * date meanwhile the system is posting any unposted transactions
 */

include('includes/session.inc');
$Title = _('Balance Sheet');// Screen identification.
$ViewTopic = 'GeneralLedger';// Filename's id in ManualContents.php's TOC.
$BookMark = 'BalanceSheet';// Anchor's id in the manual's html document.
include('includes/SQL_CommonFunctions.inc');
include('includes/AccountSectionsDef.inc'); // This loads the $Sections variable

if (!isset($_POST['BalancePeriodEnd']) or isset($_POST['SelectADifferentPeriod'])) {

	/*Show a form to allow input of criteria for Balance Sheet to show */
	include('includes/header.inc');

	echo '<p class="page_title_text">
			<img alt="" src="' . $RootPath.'/css/' . $_SESSION['Theme'] . '/images/printer.png" title="' . _('Print Statement of Financial Position') . '" />
			' . _('Balance Sheet') . '
		</p>';// Page title.

	echo '<div class="page_help_text">' . _('Balance Sheet (or statement of financial position) is a summary  of balances. Assets, liabilities and ownership equity are listed as of a specific date, such as the end of its financial year. Of the four basic financial statements, the balance sheet is the only statement which applies to a single point in time.') . '<br />' . _('The balance sheet has three parts: assets, liabilities and ownership equity. The main categories of assets are listed first and are followed by the liabilities. The difference between the assets and the liabilities is known as equity or the net assets or the net worth or capital of the company and according to the accounting equation, net worth must equal assets minus liabilities.') . '<br />' . $ProjectName . _(' is an accrual based system (not a cash based system).  Accrual systems include items when they are invoiced to the customer, and when expenses are owed based on the supplier invoice date.') . '</div>';

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection" summary="' . _('Criteria for report') . '">
			<tr>
				<td>' . _('Select the balance date') . ':</td>
				<td><select name="BalancePeriodEnd">';

	$periodno = GetPeriod(Date($_SESSION['DefaultDateFormat']));
	$SQL = "SELECT lastdate_in_period FROM periods WHERE periodno='" . $periodno . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$lastdate_in_period = $MyRow[0];

	$SQL = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC";
	$Periods = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Periods)) {
		if ($MyRow['periodno'] == $periodno) {
			echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' . ConvertSQLDate($lastdate_in_period) . '</option>';
		} else {
			echo '<option value="' . $MyRow['periodno'] . '">' . ConvertSQLDate($MyRow['lastdate_in_period']) . '</option>';
		}
	}

	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('Detail Or Summary') . ':</td>
			<td><select name="Detail">
				<option value="Summary">' . _('Summary') . '</option>
				<option selected="selected" value="Detailed">' . _('All Accounts') . '</option>
			</select></td>
		</tr>

		<tr>
			 <td>' . _('Show all Accounts including zero balances') . '</td>
			 <td><input type="checkbox" checked="checked" title="' . _('Check this box to display all accounts including those accounts with no balance') . '" name="ShowZeroBalances"></td>
		</tr>
	</table>';

	echo '<div class="centre">
			<input type="submit" name="ShowBalanceSheet" value="' . _('Show Balance Sheet') . '" />
		</div>';
	echo '</form>';

	/*Now do the posting while the user is thinking about the period to select */
	include('includes/GLPostings.inc');
	include('includes/footer.inc');

} else {

	include('includes/header.inc');
	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/preview.png" title="' . _('HTML View') . '" alt="' . _('HTML View') . '" /> ' . _('HTML View') . '</p>';

	/* Get last years totals into arrays */
	$SQL = "SELECT accountgroups.sectioninaccounts,
					accountgroups.parentgroupcode,
					chartmaster.groupcode,
					SUM(gltrans.amount) as grouptotal
				FROM chartmaster
				INNER JOIN gltrans
					ON chartmaster.accountcode=gltrans.account
				INNER JOIN accountgroups
					ON chartmaster.groupcode=accountgroups.groupcode
					AND chartmaster.language=accountgroups.language
				WHERE periodno<='" . ($_POST['BalancePeriodEnd']-12) . "'
					AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
				GROUP BY chartmaster.groupcode
						ORDER BY sequenceintb,
								accountgroups.groupcode,
								chartmaster.accountcode";

	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		/* Get the group balances from the lowest level groups */
		$LYLowestLevelGroupBalances[$MyRow['groupcode']] = $MyRow['grouptotal'];
		/* Find the parent group codes */
		if (isset($LYParentGroupCodeTotal[$MyRow['parentgroupcode']])) {
			$LYParentGroupCodeTotal[$MyRow['parentgroupcode']] += $MyRow['grouptotal'];
		} else {
			$LYParentGroupCodeTotal[$MyRow['parentgroupcode']] = $MyRow['grouptotal'];
		}
		if (isset($LYSectionTotal[$MyRow['sectioninaccounts']])) {
			$LYSectionTotal[$MyRow['sectioninaccounts']] += $MyRow['grouptotal'];
		} else {
			$LYSectionTotal[$MyRow['sectioninaccounts']] = $MyRow['grouptotal'];
		}
	}

	/* Get this years totals into arrays */
	$SQL = "SELECT accountgroups.sectioninaccounts,
					accountgroups.parentgroupcode,
					chartmaster.groupcode,
					SUM(gltrans.amount) as grouptotal
				FROM chartmaster
				INNER JOIN gltrans
					ON chartmaster.accountcode=gltrans.account
				INNER JOIN accountgroups
					ON chartmaster.groupcode=accountgroups.groupcode
					AND chartmaster.language=accountgroups.language
				WHERE periodno<='" . $_POST['BalancePeriodEnd'] . "'
					AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
				GROUP BY chartmaster.groupcode
						ORDER BY sequenceintb,
								accountgroups.groupcode,
								chartmaster.accountcode";

	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		/* Get the group balances from the lowest level groups */
		$LowestLevelGroupBalances[$MyRow['groupcode']] = $MyRow['grouptotal'];
		/* Find the parent group codes */
		if (isset($ParentGroupCodeTotal[$MyRow['parentgroupcode']])) {
			$ParentGroupCodeTotal[$MyRow['parentgroupcode']] += $MyRow['grouptotal'];
		} else {
			$ParentGroupCodeTotal[$MyRow['parentgroupcode']] = $MyRow['grouptotal'];
		}
		if (isset($SectionTotal[$MyRow['sectioninaccounts']])) {
			$SectionTotal[$MyRow['sectioninaccounts']] += $MyRow['grouptotal'];
		} else {
			$SectionTotal[$MyRow['sectioninaccounts']] = $MyRow['grouptotal'];
		}
	}

	$SQL = "SELECT lastdate_in_period FROM periods WHERE periodno='" . $_POST['BalancePeriodEnd'] . "'";
	$PrdResult = DB_query($SQL);
	$MyRow = DB_fetch_row($PrdResult);
	$BalanceDate = ConvertSQLDate($MyRow[0]);

	echo '<table class="selection" summary="' . _('HTML View') . '">
			<thead>
				<tr>
					<th colspan="8">
						<h2>' . _('Balance Sheet as at') . ' ' . $BalanceDate . '
						<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/printer.png" class="PrintIcon" title="' . _('Print') . '" alt="' . _('Print') . '" onclick="window.print();" />
						</h2>
					</th>
				</tr>
				<tr>
					<th colspan="2">' . _('Account Details') . '</th>
					<th colspan="3">' . _('This Year') . '</th>
					<th colspan="3">' . _('Last Year') . '</th>
				</tr>
			</thead>';
	$LastSection = '';
	$LastGroup = '';
	$LastParentGroup = '';
	$MainListSQL = "SELECT chartmaster.accountcode,
							chartmaster.accountname,
							accountgroups.groupcode,
							accountgroups.groupname,
							accountgroups.sectioninaccounts,
							accountgroups.parentgroupcode,
							SUM(gltrans.amount) AS balance
						FROM chartmaster
						INNER JOIN accountgroups
							ON chartmaster.groupcode=accountgroups.groupcode
							AND chartmaster.language=accountgroups.language
						LEFT JOIN gltrans
							ON gltrans.account=chartmaster.accountcode
							AND gltrans.periodno<='" . $_POST['BalancePeriodEnd'] . "'
						WHERE pandl=0
							AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
						GROUP BY sectioninaccounts,
								accountcode
						ORDER BY sequenceintb,
								sectioninaccounts,
								accountgroups.groupcode,
								chartmaster.accountcode";
	$MainListResult = DB_query($MainListSQL);

	$LYMainListSQL = "SELECT chartmaster.accountcode,
							chartmaster.accountname,
							accountgroups.groupcode,
							accountgroups.groupname,
							accountgroups.sectioninaccounts,
							accountgroups.parentgroupcode,
							SUM(gltrans.amount) AS balance
						FROM chartmaster
						INNER JOIN accountgroups
							ON chartmaster.groupcode=accountgroups.groupcode
							AND chartmaster.language=accountgroups.language
						LEFT JOIN gltrans
							ON gltrans.account=chartmaster.accountcode
							AND gltrans.periodno<='" . $_POST['BalancePeriodEnd'] . "'
						WHERE pandl=0
							AND language='" . $_SESSION['ChartLanguage'] . "'
						GROUP BY sectioninaccounts,
								accountcode
						ORDER BY sequenceintb,
								sectioninaccounts,
								accountgroups.groupcode,
								chartmaster.accountcode";
	$LYMainListResult = DB_query($LYMainListSQL);

	$LastYearTotal = 0;
	$ThisYearTotal = 0;
	$i = 0; //Table row counter

	while ($MainAccountListRow = DB_fetch_array($MainListResult)) {
		$LYMainAccountListRow = DB_fetch_array($LYMainListResult);
		if ($_SESSION['CompanyRecord']['retainedearnings'] == $MainAccountListRow['accountcode']) {
			/*Get last years retainedearnings figure */
			if (!isset($LYLowestLevelGroupBalances[$MainAccountListRow['groupcode']])) {
				$LYLowestLevelGroupBalances[$MainAccountListRow['groupcode']] = 0;
				$LYParentGroupCodeTotal[$MainAccountListRow['parentgroupcode']] = 0;
				$LYSectionTotal[$LastSection] = 0;
				$LYMainAccountListRow['balance'] = 0;
			}
			$RetainedEarningsSQL = "SELECT SUM(amount) AS retainedearnings,
											accountgroups.groupcode,
											accountgroups.parentgroupcode,
											accountgroups.sectioninaccounts
										FROM chartmaster
										INNER JOIN accountgroups
											ON chartmaster.groupcode=accountgroups.groupcode
											AND chartmaster.language=accountgroups.language
										INNER JOIN gltrans
											ON gltrans.account=chartmaster.accountcode
										WHERE periodno<='" . ($_POST['BalancePeriodEnd'] - 12) . "'
											AND language='" . $_SESSION['ChartLanguage'] . "'
											AND pandl=1";
			$RetainedEarningsResult = DB_query($RetainedEarningsSQL);
			$RetainedEarningsRow = DB_fetch_array($RetainedEarningsResult);
			$LYMainAccountListRow['balance'] += $RetainedEarningsRow['retainedearnings'];
			$LYLowestLevelGroupBalances[$MainAccountListRow['groupcode']] = $LYLowestLevelGroupBalances[$MainAccountListRow['groupcode']] + $RetainedEarningsRow['retainedearnings'];
			$LYParentGroupCodeTotal[$MainAccountListRow['parentgroupcode']] = $LYParentGroupCodeTotal[$MainAccountListRow['parentgroupcode']] + $RetainedEarningsRow['retainedearnings'];
			$LYSectionTotal[$LastSection] = $LYSectionTotal[$LastSection] + $RetainedEarningsRow['retainedearnings'];

			if (!isset($LowestLevelGroupBalances[$MainAccountListRow['groupcode']])) {
				$LowestLevelGroupBalances[$MainAccountListRow['groupcode']] = 0;
			}
			if (!isset($SectionTotal[$LastSection])) {
				$SectionTotal[$LastSection] = 0;
			}
			/*Get this years retainedearnings figure */
			$RetainedEarningsSQL = "SELECT SUM(amount) AS retainedearnings,
											accountgroups.groupcode,
											accountgroups.parentgroupcode,
											accountgroups.sectioninaccounts
										FROM chartmaster
										INNER JOIN accountgroups
											ON chartmaster.groupcode=accountgroups.groupcode
											AND chartmaster.language=accountgroups.language
										INNER JOIN gltrans
											ON gltrans.account=chartmaster.accountcode
										WHERE periodno<='" . $_POST['BalancePeriodEnd'] . "'
											AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
											AND pandl=1";
			$RetainedEarningsResult = DB_query($RetainedEarningsSQL);
			$RetainedEarningsRow = DB_fetch_array($RetainedEarningsResult);
			$MainAccountListRow['balance'] += $RetainedEarningsRow['retainedearnings'];
			$LowestLevelGroupBalances[$MainAccountListRow['groupcode']] = $LowestLevelGroupBalances[$MainAccountListRow['groupcode']] + $RetainedEarningsRow['retainedearnings'];
			$ParentGroupCodeTotal[$MainAccountListRow['parentgroupcode']] = $ParentGroupCodeTotal[$MainAccountListRow['parentgroupcode']] + $RetainedEarningsRow['retainedearnings'];
			$SectionTotal[$LastSection] = $SectionTotal[$LastSection] + $RetainedEarningsRow['retainedearnings'];
		}
		$LastYearTotal += $LYMainAccountListRow['balance'];
		$ThisYearTotal += $MainAccountListRow['balance'];
		if ($MainAccountListRow['groupcode'] != $LastGroup and $LastGroup != '') {
			if (!isset($LYLowestLevelGroupBalances[$LastGroup])) {
				$LYLowestLevelGroupBalances[$LastGroup] = 0;
			}
			if (!isset($LowestLevelGroupBalances[$LastGroup])) {
				$LowestLevelGroupBalances[$LastGroup] = 0;
			}
			echo '<tr>
					<td></td>
					<td></td>
					<td>---------------------</td>
					<td></td>
					<td></td>
					<td>---------------------</td>
				</tr>';
			echo '<tr>
					<td class="number"><b>' . $LastGroup . '</b></td>
					<td><b>' . $MainAccountListRow['groupname'] . '</b></td>
					<td class="number"><b>' . locale_number_format($LowestLevelGroupBalances[$LastGroup], $_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
					<td></td>
					<td></td>
					<td class="number"><b>' . locale_number_format($LYLowestLevelGroupBalances[$LastGroup], $_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
				</tr>';
			echo '<tr>
					<td></td>
					<td></td>
					<td>---------------------</td>
					<td></td>
					<td></td>
					<td>---------------------</td>
				</tr>';
		}
		if ($MainAccountListRow['parentgroupcode'] != $LastParentGroup and $LastParentGroup != '') {
			if (!isset($LYParentGroupCodeTotal[$LastParentGroup])) {
				$LYParentGroupCodeTotal[$LastParentGroup] = 0;
			}
			$ParentNameSQL = "SELECT groupname FROM accountgroups WHERE groupcode='" . $LastParentGroup . "'";
			$ParentNameResult = DB_query($ParentNameSQL);
			$ParentNameRow = DB_fetch_array($ParentNameResult);
			echo '<tr>
					<td></td>
					<td></td>
					<td></td>
					<td>---------------------</td>
					<td></td>
					<td></td>
					<td>---------------------</td>
				</tr>';
			echo '<tr>
					<td></td>
					<td class="number"><b>' . $LastParentGroup . ' - ' . $ParentNameRow['groupname'] . '</b></td>
					<td></td>
					<td class="number"><b>' . locale_number_format($ParentGroupCodeTotal[$LastParentGroup], $_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
					<td></td>
					<td></td>
					<td class="number"><b>' . locale_number_format($LYParentGroupCodeTotal[$LastParentGroup], $_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
				</tr>';
			echo '<tr>
					<td></td>
					<td></td>
					<td></td>
					<td>---------------------</td>
					<td></td>
					<td></td>
					<td>---------------------</td>
				</tr>';
		}
		if ($MainAccountListRow['sectioninaccounts'] != $LastSection and $LastSection != '') {
			if (!isset($LYSectionTotal[$LastSection])) {
				$LYSectionTotal[$LastSection] = 0;
			}
			if (!isset($SectionTotal[$LastSection])) {
				$SectionTotal[$LastSection] = 0;
			}
			$SectionNameSQL = "SELECT sectionname FROM accountsection WHERE sectionid='" . $LastSection . "'";
			$SectionNameResult = DB_query($SectionNameSQL);
			$SectionNameRow = DB_fetch_array($SectionNameResult);
			echo '<tr>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td>---------------------</td>
					<td></td>
					<td></td>
					<td>---------------------</td>
				</tr>';
			echo '<tr>
					<td></td>
					<td class="number"><b><u>' . $LastSection . ' - ' . $SectionNameRow['sectionname'] . '</u></b></td>
					<td></td>
					<td></td>
					<td class="number"><b>' . locale_number_format($SectionTotal[$LastSection], $_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
					<td></td>
					<td></td>
					<td class="number"><b>' . locale_number_format($LYSectionTotal[$LastSection], $_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
				</tr>';
			echo '<tr>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td>---------------------</td>
					<td></td>
					<td></td>
					<td>---------------------</td>
				</tr>';
		}
		if ($i == 0) {
			$RowClass = 'EvenTableRows';
			$i = 1;
		} else {
			$RowClass = 'OddTableRows';
			$i = 0;
		}
		echo '<tr class="' . $RowClass . '">
				<td>' . $MainAccountListRow['accountcode'] . '</td>
				<td>' . $MainAccountListRow['accountname'] . '</td>
				<td class="number">' . locale_number_format($MainAccountListRow['balance'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td></td>
				<td></td>
				<td class="number">' . locale_number_format($LYMainAccountListRow['balance'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td></td>
				<td></td>
			</tr>';
		$LastGroup = $MainAccountListRow['groupcode'];
		$LastParentGroup = $MainAccountListRow['parentgroupcode'];
		$LastSection = $MainAccountListRow['sectioninaccounts'];
	}
	if (!isset($LYLowestLevelGroupBalances[$LastGroup])) {
		$LYLowestLevelGroupBalances[$LastGroup] = 0;
		$LYParentGroupCodeTotal[$LastParentGroup] = 0;
		$LYSectionTotal[$LastSection] = 0;
	}
	echo '<tr>
			<td></td>
			<td></td>
			<td>---------------------</td>
			<td></td>
			<td></td>
			<td>---------------------</td>
		</tr>';
	echo '<tr>
			<td class="number"><b>' . $LastGroup . '</b></td>
			<td><b>' . $MainAccountListRow['groupname'] . '</b></td>
			<td class="number"><b>' . locale_number_format($LowestLevelGroupBalances[$LastGroup], $_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
			<td></td>
			<td></td>
			<td class="number"><b>' . locale_number_format($LYLowestLevelGroupBalances[$LastGroup], $_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
		</tr>';
	echo '<tr>
			<td></td>
			<td></td>
			<td>---------------------</td>
			<td></td>
			<td></td>
			<td>---------------------</td>
		</tr>';
	$ParentNameSQL = "SELECT groupname FROM accountgroups WHERE groupcode='" . $LastParentGroup . "'";
	$ParentNameResult = DB_query($ParentNameSQL);
	$ParentNameRow = DB_fetch_array($ParentNameResult);
	echo '<tr>
			<td></td>
			<td></td>
			<td></td>
			<td>---------------------</td>
			<td></td>
			<td></td>
			<td>---------------------</td>
		</tr>';
	echo '<tr>
			<td></td>
			<td class="number"><b>' . $LastParentGroup . ' - ' . $ParentNameRow['groupname'] . '</b></td>
			<td></td>
			<td class="number"><b>' . locale_number_format($ParentGroupCodeTotal[$LastParentGroup], $_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
			<td></td>
			<td></td>
			<td class="number"><b>' . locale_number_format($LYParentGroupCodeTotal[$LastParentGroup], $_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
		</tr>';
	echo '<tr>
			<td></td>
			<td></td>
			<td></td>
			<td>---------------------</td>
			<td></td>
			<td></td>
			<td>---------------------</td>
		</tr>';
	$SectionNameSQL = "SELECT sectionname FROM accountsection WHERE sectionid='" . $LastSection . "'";
	$SectionNameResult = DB_query($SectionNameSQL);
	$SectionNameRow = DB_fetch_array($SectionNameResult);
	echo '<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td>---------------------</td>
			<td></td>
			<td></td>
			<td>---------------------</td>
		</tr>';
	echo '<tr>
			<td></td>
			<td class="number"><b><u>' . $LastSection . ' - ' . $SectionNameRow['sectionname'] . '</u></b></td>
			<td></td>
			<td></td>
			<td class="number"><b>' . locale_number_format($SectionTotal[$LastSection], $_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
			<td></td>
			<td></td>
			<td class="number"><b>' . locale_number_format($LYSectionTotal[$LastSection], $_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
		</tr>';
	echo '<tr>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td>---------------------</td>
			<td></td>
			<td></td>
			<td>---------------------</td>
		</tr>';
	echo '<tr>
			<td></td>
			<td class="number"><b><u>' . _('Check Totals') . '</u></b></td>
			<td></td>
			<td></td>
			<td class="number"><b><u>' . locale_number_format($ThisYearTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</u></b></td>
			<td></td>
			<td></td>
			<td class="number"><b><u>' . locale_number_format($LastYearTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</u></b></td>
		</tr>';
	echo '</table>';
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Select A Different Balance Date') . ' </a>
		</div>';
	include('includes/footer.inc');
}
?>