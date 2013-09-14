<?php


/* Through deviousness and cunning, this system allows shows the balance sheets
 * as at the end of any period selected - so first off need to show the input
 * of criteria screen while the user is selecting the period end of the balance
 * date meanwhile the system is posting any unposted transactions
 */

include('includes/session.inc');
$Title = _('Balance Sheet');
include('includes/SQL_CommonFunctions.inc');
include('includes/AccountSectionsDef.inc'); // This loads the $Sections variable

if (!isset($_POST['BalancePeriodEnd']) or isset($_POST['SelectADifferentPeriod'])) {

	/*Show a form to allow input of criteria for Balance Sheet to show */
	$ViewTopic = 'GeneralLedger';
	$BookMark = 'BalanceSheet';
	include('includes/header.inc');
	echo '<div class="centre"><p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/printer.png" title="' . _('Balance Sheet') . '" alt="' . _('Balance Sheet') . '" />' . ' ' . _('Balance Sheet') . '</p></div>';
	echo '<div class="page_help_text noPrint">' . _('Balance Sheet (or statement of financial position) is a summary  of balances. Assets, liabilities and ownership equity are listed as of a specific date, such as the end of its financial year. Of the four basic financial statements, the balance sheet is the only statement which applies to a single point in time.') . '<br />' . _('The balance sheet has three parts: assets, liabilities and ownership equity. The main categories of assets are listed first and are followed by the liabilities. The difference between the assets and the liabilities is known as equity or the net assets or the net worth or capital of the company and according to the accounting equation, net worth must equal assets minus liabilities.') . '<br />' . _('KwaMoja is an accrual based system (not a cash based system).  Accrual systems include items when they are invoiced to the customer, and when expenses are owed based on the supplier invoice date.') . '</div>';

	echo '<form onSubmit="return VerifyForm(this);" method="post" class="noPrint" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<br /><table class="selection" summary="' . _('Criteria for report') . '">
			<tr>
				<td>' . _('Select the balance date') . ':</td>
				<td><select minlength="0" name="BalancePeriodEnd">';

	$periodno = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
	$sql = "SELECT lastdate_in_period FROM periods WHERE periodno='" . $periodno . "'";
	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result, $db);
	$lastdate_in_period = $myrow[0];

	$sql = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC";
	$Periods = DB_query($sql, $db);

	while ($myrow = DB_fetch_array($Periods, $db)) {
		if ($myrow['periodno'] == $periodno) {
			echo '<option selected="selected" value="' . $myrow['periodno'] . '">' . ConvertSQLDate($lastdate_in_period) . '</option>';
		} else {
			echo '<option value="' . $myrow['periodno'] . '">' . ConvertSQLDate($myrow['lastdate_in_period']) . '</option>';
		}
	}

	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('Detail Or Summary') . ':</td>
			<td><select minlength="0" name="Detail">
				<option value="Summary">' . _('Summary') . '</option>
				<option selected="selected" value="Detailed">' . _('All Accounts') . '</option>
			</select></td>
		</tr>

		<tr>
			 <td>' . _('Show all Accounts including zero balances') . '</td>
			 <td><input type="checkbox" checked="checked" title="' . _('Check this box to display all accounts including those accounts with no balance') . '" name="ShowZeroBalances"></td>
		</tr>
	</table>';

	echo '<br />
			<div class="centre">
				<input type="submit" name="ShowBalanceSheet" value="' . _('Show on Screen (HTML)') . '" />
			</div>';
	echo '<br />
			<div class="centre">
				<input type="submit" name="PrintPDF" value="' . _('Produce PDF Report') . '" />
			</div>';
	echo '</form>';

	/*Now do the posting while the user is thinking about the period to select */
	include('includes/GLPostings.inc');

} elseif (isset($_POST['PrintPDF'])) {

	include('includes/PDFStarter.php');
	$pdf->addInfo('Title', _('Balance Sheet'));
	$pdf->addInfo('Subject', _('Balance Sheet'));
	$line_height = 12;
	$PageNumber = 0;
	$FontSize = 10;

	$RetainedEarningsAct = $_SESSION['CompanyRecord']['retainedearnings'];

	$sql = "SELECT lastdate_in_period FROM periods WHERE periodno='" . $_POST['BalancePeriodEnd'] . "'";
	$PrdResult = DB_query($sql, $db);
	$myrow = DB_fetch_row($PrdResult);
	$BalanceDate = ConvertSQLDate($myrow[0]);

	/*Calculate B/Fwd retained earnings */

	$SQL = "SELECT Sum(CASE WHEN chartdetails.period='" . $_POST['BalancePeriodEnd'] . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS accumprofitbfwd,
			Sum(CASE WHEN chartdetails.period='" . ($_POST['BalancePeriodEnd'] - 12) . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS lyaccumprofitbfwd
		FROM chartmaster INNER JOIN accountgroups
		ON chartmaster.group_ = accountgroups.groupname INNER JOIN chartdetails
		ON chartmaster.accountcode= chartdetails.accountcode
		WHERE accountgroups.pandl=1";

	$AccumProfitResult = DB_query($SQL, $db);
	if (DB_error_no($db) != 0) {
		$Title = _('Balance Sheet') . ' - ' . _('Problem Report') . '....';
		include('includes/header.inc');
		prnMsg(_('The accumulated profits brought forward could not be calculated by the SQL because') . ' - ' . DB_error_msg($db));
		echo '<br />
				<a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($debug == 1) {
			echo '<br />' . $SQL;
		}
		include('includes/footer.inc');
		exit;
	}

	$AccumProfitRow = DB_fetch_array($AccumProfitResult);
	/*should only be one row returned */

	$SQL = "SELECT accountgroups.sectioninaccounts,
			accountgroups.groupname,
			accountgroups.parentgroupname,
			chartdetails.accountcode ,
			chartmaster.accountname,
			Sum(CASE WHEN chartdetails.period='" . $_POST['BalancePeriodEnd'] . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS balancecfwd,
			Sum(CASE WHEN chartdetails.period='" . ($_POST['BalancePeriodEnd'] - 12) . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS lybalancecfwd
		FROM chartmaster INNER JOIN accountgroups
		ON chartmaster.group_ = accountgroups.groupname INNER JOIN chartdetails
		ON chartmaster.accountcode= chartdetails.accountcode
		WHERE accountgroups.pandl=0
		GROUP BY accountgroups.groupname,
			chartdetails.accountcode,
			chartmaster.accountname,
			accountgroups.parentgroupname,
			accountgroups.sequenceintb,
			accountgroups.sectioninaccounts
		ORDER BY accountgroups.sectioninaccounts,
			accountgroups.sequenceintb,
			accountgroups.groupname,
			chartdetails.accountcode";

	$AccountsResult = DB_query($SQL, $db);

	if (DB_error_no($db) != 0) {
		$Title = _('Balance Sheet') . ' - ' . _('Problem Report') . '....';
		include('includes/header.inc');
		prnMsg(_('No general ledger accounts were returned by the SQL because') . ' - ' . DB_error_msg($db));
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($debug == 1) {
			echo '<br />' . $SQL;
		}
		include('includes/footer.inc');
		exit;
	}

	$ListCount = DB_num_rows($AccountsResult); // UldisN

	include('includes/PDFBalanceSheetPageHeader.inc');

	$k = 0; //row colour counter
	$Section = '';
	$SectionBalance = 0;
	$SectionBalanceLY = 0;

	$LYCheckTotal = 0;
	$CheckTotal = 0;

	$ActGrp = '';
	$Level = 0;
	$ParentGroups = array();
	$ParentGroups[$Level] = '';
	$GroupTotal = array(
		0
	);
	$LYGroupTotal = array(
		0
	);

	while ($myrow = DB_fetch_array($AccountsResult)) {
		$AccountBalance = $myrow['balancecfwd'];
		$LYAccountBalance = $myrow['lybalancecfwd'];

		if ($myrow['accountcode'] == $RetainedEarningsAct) {
			$AccountBalance += $AccumProfitRow['accumprofitbfwd'];
			$LYAccountBalance += $AccumProfitRow['lyaccumprofitbfwd'];
		}
		if ($ActGrp != '') {
			if ($myrow['groupname'] != $ActGrp) {
				$FontSize = 8;
				$pdf->setFont('', 'B');
				while ($myrow['groupname'] != $ParentGroups[$Level] and $Level > 0) {
					$YPos -= $line_height;
					$LeftOvers = $pdf->addTextWrap($Left_Margin + (10 * ($Level + 1)), $YPos, 200, $FontSize, _('Total') . ' ' . $ParentGroups[$Level]);
					$LeftOvers = $pdf->addTextWrap($Left_Margin + 250, $YPos, 100, $FontSize, locale_number_format($GroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
					$LeftOvers = $pdf->addTextWrap($Left_Margin + 350, $YPos, 100, $FontSize, locale_number_format($LYGroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
					$ParentGroups[$Level] = '';
					$GroupTotal[$Level] = 0;
					$LYGroupTotal[$Level] = 0;
					$Level--;
					if ($YPos < $Bottom_Margin) {
						include('includes/PDFBalanceSheetPageHeader.inc');
					}
				}
				$YPos -= $line_height;
				$LeftOvers = $pdf->addTextWrap($Left_Margin + (10 * ($Level + 1)), $YPos, 200, $FontSize, _('Total') . ' ' . $ParentGroups[$Level]);
				$LeftOvers = $pdf->addTextWrap($Left_Margin + 250, $YPos, 100, $FontSize, locale_number_format($GroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
				$LeftOvers = $pdf->addTextWrap($Left_Margin + 350, $YPos, 100, $FontSize, locale_number_format($LYGroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
				$ParentGroups[$Level] = '';
				$GroupTotal[$Level] = 0;
				$LYGroupTotal[$Level] = 0;
				$YPos -= $line_height;
				if ($YPos < $Bottom_Margin) {
					include('includes/PDFBalanceSheetPageHeader.inc');
				}
			}
		}

		if ($myrow['sectioninaccounts'] != $Section) {

			if ($Section != '') {
				$FontSize = 8;
				$pdf->setFont('', 'B');
				$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 200, $FontSize, $Sections[$Section]);
				$LeftOvers = $pdf->addTextWrap($Left_Margin + 250, $YPos, 100, $FontSize, locale_number_format($SectionBalance, $_SESSION['CompanyRecord']['decimalplaces']), 'right');
				$LeftOvers = $pdf->addTextWrap($Left_Margin + 350, $YPos, 100, $FontSize, locale_number_format($SectionBalanceLY, $_SESSION['CompanyRecord']['decimalplaces']), 'right');
				$YPos -= (2 * $line_height);
				if ($YPos < $Bottom_Margin) {
					include('includes/PDFBalanceSheetPageHeader.inc');
				}
			}
			$SectionBalanceLY = 0;
			$SectionBalance = 0;

			$Section = $myrow['sectioninaccounts'];
			if ($_POST['Detail'] == 'Detailed') {

				$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 200, $FontSize, $Sections[$myrow['sectioninaccounts']]);
				$YPos -= (2 * $line_height);
				if ($YPos < $Bottom_Margin) {
					include('includes/PDFBalanceSheetPageHeader.inc');
				}
			}
		}

		if ($myrow['groupname'] != $ActGrp) {
			if ($YPos < $Bottom_Margin + $line_height) {
				include('includes/PDFBalanceSheetPageHeader.inc');
			}
			$FontSize = 8;
			$pdf->setFont('', 'B');
			if ($myrow['parentgroupname'] == $ActGrp and $ActGrp != '') {
				$Level++;
			}
			$ActGrp = $myrow['groupname'];
			$ParentGroups[$Level] = $ActGrp;
			if ($_POST['Detail'] == 'Detailed') {
				$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 200, $FontSize, $myrow['groupname']);
				$YPos -= $line_height;
			}
			$GroupTotal[$Level] = 0;
			$LYGroupTotal[$Level] = 0;
		}

		$SectionBalanceLY += $LYAccountBalance;
		$SectionBalance += $AccountBalance;

		for ($i = 0; $i <= $Level; $i++) {
			$LYGroupTotal[$i] += $LYAccountBalance;
			$GroupTotal[$i] += $AccountBalance;
		}
		$LYCheckTotal += $LYAccountBalance;
		$CheckTotal += $AccountBalance;

		if ($_POST['Detail']=='Detailed') {
			if (isset($_POST['ShowZeroBalances']) or (!isset($_POST['ShowZeroBalances']) and ($AccountBalance <> 0 or $LYAccountBalance <> 0))) {
				$FontSize = 8;
				$pdf->setFont('', '');
				$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 50, $FontSize, $myrow['accountcode']);
				$LeftOvers = $pdf->addTextWrap($Left_Margin + 55, $YPos, 200, $FontSize, $myrow['accountname']);
				$LeftOvers = $pdf->addTextWrap($Left_Margin + 250, $YPos, 100, $FontSize, locale_number_format($AccountBalance,$_SESSION['CompanyRecord']['decimalplaces']), 'right');
				$LeftOvers = $pdf->addTextWrap($Left_Margin + 350, $YPos, 100, $FontSize, locale_number_format($LYAccountBalance, $_SESSION['CompanyRecord']['decimalplaces']), 'right');
				$YPos -= $line_height;
			}
		}
	}
	$FontSize = 8;
	$pdf->setFont('', 'B');
	while ($Level > 0) {
		$YPos -= $line_height;
		$LeftOvers = $pdf->addTextWrap($Left_Margin + (10 * ($Level + 1)), $YPos, 200, $FontSize, _('Total') . ' ' . $ParentGroups[$Level]);
		$LeftOvers = $pdf->addTextWrap($Left_Margin + 250, $YPos, 100, $FontSize, locale_number_format($GroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
		$LeftOvers = $pdf->addTextWrap($Left_Margin + 350, $YPos, 100, $FontSize, locale_number_format($LYGroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
		$ParentGroups[$Level] = '';
		$GroupTotal[$Level] = 0;
		$LYGroupTotal[$Level] = 0;
		$Level--;
	}
	$YPos -= $line_height;
	$LeftOvers = $pdf->addTextWrap($Left_Margin + (10 * ($Level + 1)), $YPos, 200, $FontSize, _('Total') . ' ' . $ParentGroups[$Level]);
	$LeftOvers = $pdf->addTextWrap($Left_Margin + 250, $YPos, 100, $FontSize, locale_number_format($GroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin + 350, $YPos, 100, $FontSize, locale_number_format($LYGroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']), 'right');
	$ParentGroups[$Level] = '';
	$GroupTotal[$Level] = 0;
	$LYGroupTotal[$Level] = 0;
	$YPos -= $line_height;

	if ($SectionBalanceLY + $SectionBalance != 0) {
		$FontSize = 8;
		$pdf->setFont('', 'B');
		$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 200, $FontSize, $Sections[$Section]);
		$LeftOvers = $pdf->addTextWrap($Left_Margin + 250, $YPos, 100, $FontSize, locale_number_format($SectionBalance, $_SESSION['CompanyRecord']['decimalplaces']), 'right');
		$LeftOvers = $pdf->addTextWrap($Left_Margin + 350, $YPos, 100, $FontSize, locale_number_format($SectionBalanceLY, $_SESSION['CompanyRecord']['decimalplaces']), 'right');
		$YPos -= $line_height;
	}

	$YPos -= $line_height;

	$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 200, $FontSize, _('Check Total'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin + 250, $YPos, 100, $FontSize, locale_number_format($CheckTotal, $_SESSION['CompanyRecord']['decimalplaces']), 'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin + 350, $YPos, 100, $FontSize, locale_number_format($LYCheckTotal, $_SESSION['CompanyRecord']['decimalplaces']), 'right');

	if ($ListCount == 0) { //UldisN
		$Title = _('Print Balance Sheet Error');
		include('includes/header.inc');
		prnMsg(_('There were no entries to print out for the selections specified'));
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		include('includes/footer.inc');
		exit;
	} else {
		$pdf->OutputD($_SESSION['DatabaseName'] . '_GL_Balance_Sheet_' . date('Y-m-d') . '.pdf');
		$pdf->__destruct();
	}
	exit;
} else {
	$ViewTopic = 'GeneralLedger';
	$BookMark = 'BalanceSheet';
	include('includes/header.inc');
	echo '<form onSubmit="return VerifyForm(this);" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="BalancePeriodEnd" value="' . $_POST['BalancePeriodEnd'] . '" />';

	$RetainedEarningsAct = $_SESSION['CompanyRecord']['retainedearnings'];

	$sql = "SELECT lastdate_in_period FROM periods WHERE periodno='" . $_POST['BalancePeriodEnd'] . "'";
	$PrdResult = DB_query($sql, $db);
	$myrow = DB_fetch_row($PrdResult);
	$BalanceDate = ConvertSQLDate($myrow[0]);

	/*Calculate B/Fwd retained earnings */

	$SQL = "SELECT Sum(CASE WHEN chartdetails.period='" . $_POST['BalancePeriodEnd'] . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS accumprofitbfwd,
			Sum(CASE WHEN chartdetails.period='" . ($_POST['BalancePeriodEnd'] - 12) . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS lyaccumprofitbfwd
		FROM chartmaster INNER JOIN accountgroups
		ON chartmaster.group_ = accountgroups.groupname INNER JOIN chartdetails
		ON chartmaster.accountcode= chartdetails.accountcode
		WHERE accountgroups.pandl=1";

	$AccumProfitResult = DB_query($SQL, $db, _('The accumulated profits brought forward could not be calculated by the SQL because'));

	$AccumProfitRow = DB_fetch_array($AccumProfitResult);
	/*should only be one row returned */

	$SQL = "SELECT accountgroups.sectioninaccounts,
			accountgroups.groupname,
			accountgroups.parentgroupname,
			chartdetails.accountcode,
			chartmaster.accountname,
			Sum(CASE WHEN chartdetails.period='" . $_POST['BalancePeriodEnd'] . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS balancecfwd,
			Sum(CASE WHEN chartdetails.period='" . ($_POST['BalancePeriodEnd'] - 12) . "' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS lybalancecfwd
		FROM chartmaster INNER JOIN accountgroups
		ON chartmaster.group_ = accountgroups.groupname INNER JOIN chartdetails
		ON chartmaster.accountcode= chartdetails.accountcode
		WHERE accountgroups.pandl=0
		GROUP BY accountgroups.groupname,
			chartdetails.accountcode,
			chartmaster.accountname,
			accountgroups.parentgroupname,
			accountgroups.sequenceintb,
			accountgroups.sectioninaccounts
		ORDER BY accountgroups.sectioninaccounts,
			accountgroups.sequenceintb,
			accountgroups.groupname,
			chartdetails.accountcode";

	$AccountsResult = DB_query($SQL, $db, _('No general ledger accounts were returned by the SQL because'));
	echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/preview.gif" title="' . _('HTML View') . '" alt="' . _('HTML View') . '" /> ' . _('HTML View') . '</p>';

	echo '<div class="invoice">
			<table class="selection" summary="' . _('HTML View') . '">
			<tr>
				<th colspan="6">
					<h2>' . _('Balance Sheet as at') . ' ' . $BalanceDate . '
					<img src="' . $RootPath . '/css/' . $Theme . '/images/printer.png" class="PrintIcon noPrint" title="' . _('Print') . '" alt="' . _('Print') . '" onclick="window.print();" />
					</h2>
				</th>
			</tr>';

	if ($_POST['Detail'] == 'Detailed') {
		$TableHeader = '<tr>
							<th>' . _('Account') . '</th>
							<th>' . _('Account Name') . '</th>
							<th colspan="2">' . $BalanceDate . '</th>
							<th colspan="2">' . _('Last Year') . '</th>
						</tr>';
	} else {
		/*summary */
		$TableHeader = '<tr>
							<th colspan="2"></th>
							<th colspan="2">' . $BalanceDate . '</th>
							<th colspan="2">' . _('Last Year') . '</th>
						</tr>';
	}


	$k = 0; //row colour counter
	$Section = '';
	$SectionBalance = 0;
	$SectionBalanceLY = 0;

	$LYCheckTotal = 0;
	$CheckTotal = 0;

	$ActGrp = '';
	$Level = 0;
	$ParentGroups = array();
	$ParentGroups[$Level] = '';
	$GroupTotal = array(
		0
	);
	$LYGroupTotal = array(
		0
	);

	echo $TableHeader;
	$j = 0; //row counter

	while ($myrow = DB_fetch_array($AccountsResult)) {
		$AccountBalance = $myrow['balancecfwd'];
		$LYAccountBalance = $myrow['lybalancecfwd'];

		if ($myrow['accountcode'] == $RetainedEarningsAct) {
			$AccountBalance += $AccumProfitRow['accumprofitbfwd'];
			$LYAccountBalance += $AccumProfitRow['lyaccumprofitbfwd'];
		}

		if ($myrow['groupname'] != $ActGrp and $ActGrp != '') {
			if ($myrow['parentgroupname'] != $ActGrp) {
				while ($myrow['groupname'] != $ParentGroups[$Level] and $Level > 0) {
					if ($_POST['Detail'] == 'Detailed') {
						echo '<tr>
								<td colspan="2"></td>
	  							<td><hr /></td>
								<td></td>
								<td><hr /></td>
								<td></td>
							</tr>';
					}
					printf('<tr>
							  <td colspan="2"><I>%s</I></td>
							  <td class="number">%s</td>
							  <td></td>
							  <td class="number">%s</td>
							</tr>', $ParentGroups[$Level], locale_number_format($GroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($LYGroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']));
					$GroupTotal[$Level] = 0;
					$LYGroupTotal[$Level] = 0;
					$ParentGroups[$Level] = '';
					$Level--;
					$j++;
				}
				if ($_POST['Detail'] == 'Detailed') {
					echo '<tr>
							<td colspan="2"></td>
							<td><hr /></td>
							<td></td>
							<td><hr /></td>
							<td></td>
						</tr>';
				}

				printf('<tr>
						  <td colspan="2">%s</td>
						  <td class="number">%s</td>
						  <td></td>
						  <td class="number">%s</td>
						</tr>', $ParentGroups[$Level], locale_number_format($GroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($LYGroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']));

				$GroupTotal[$Level] = 0;
				$LYGroupTotal[$Level] = 0;
				$ParentGroups[$Level] = '';
				$j++;
			}
		}
		if ($myrow['sectioninaccounts'] != $Section) {

			if ($Section != '') {
				if ($_POST['Detail'] == 'Detailed') {
					echo '<tr>
							<td colspan="2"></td>
							<td><hr /></td>
							<td></td>
							<td><hr /></td>
							<td></td>
						</tr>';
				} else {
					echo '<tr>
							<td colspan="3"></td>
							<td><hr /></td>
							<td></td>
							<td><hr /></td>
						</tr>';
				}

				printf('<tr>
							<td colspan="3"><h2>%s</h2></td>
							<td class="number">%s</td>
							<td></td>
							<td class="number">%s</td>
						</tr>', $Sections[$Section], locale_number_format($SectionBalance, $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($SectionBalanceLY, $_SESSION['CompanyRecord']['decimalplaces']));
				$j++;
			}
			$SectionBalanceLY = 0;
			$SectionBalance = 0;
			$Section = $myrow['sectioninaccounts'];


			if ($_POST['Detail'] == 'Detailed') {
				printf('<tr>
						  <td colspan="6"><h1>%s</h1></td>
						</tr>', $Sections[$myrow['sectioninaccounts']]);
			}
		}

		if ($myrow['groupname'] != $ActGrp) {

			if ($ActGrp != '' and $myrow['parentgroupname'] == $ActGrp) {
				$Level++;
			}

			if ($_POST['Detail'] == 'Detailed') {
				$ActGrp = $myrow['groupname'];
				printf('<tr>
						  <td colspan="6"><h3>%s</h3></td>
						</tr>', $myrow['groupname']);
				echo $TableHeader;
			}
			$GroupTotal[$Level] = 0;
			$LYGroupTotal[$Level] = 0;
			$ActGrp = $myrow['groupname'];
			$ParentGroups[$Level] = $myrow['groupname'];
			$j++;
		}

		$SectionBalanceLY += $LYAccountBalance;
		$SectionBalance += $AccountBalance;
		for ($i = 0; $i <= $Level; $i++) {
			$LYGroupTotal[$i] += $LYAccountBalance;
			$GroupTotal[$i] += $AccountBalance;
		}
		$LYCheckTotal += $LYAccountBalance;
		$CheckTotal += $AccountBalance;


		if ($_POST['Detail'] == 'Detailed') {

			if (isset($_POST['ShowZeroBalances']) or (!isset($_POST['ShowZeroBalances']) and ($AccountBalance <> 0 or $LYAccountBalance <> 0))){
				if ($k==1){
					echo '<tr class="OddTableRows">';
					$k=0;
				} else {
					echo '<tr class="EvenTableRows">';
					$k++;
				}

				$ActEnquiryURL = '<a href="' . $RootPath . '/GLAccountInquiry.php?Period=' . $_POST['BalancePeriodEnd'] . '&amp;Account=' . $myrow['accountcode'] . '">' . $myrow['accountcode'] . '</a>';

				printf('<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td></td>
						<td class="number">%s</td>
						<td></td>
						</tr>',
						$ActEnquiryURL,
						htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false),
						locale_number_format($AccountBalance,$_SESSION['CompanyRecord']['decimalplaces']),
						locale_number_format($LYAccountBalance,$_SESSION['CompanyRecord']['decimalplaces']));
				$j++;
			}
		}
	}
	//end of loop

	while ($myrow['groupname'] != $ParentGroups[$Level] and $Level > 0) {
		if ($_POST['Detail'] == 'Detailed') {
			echo '<tr>
					<td colspan="2"></td>
					<td><hr /></td>
					<td></td>
					<td><hr /></td>
					<td></td>
				</tr>';
		}
		printf('<tr>
				  <td colspan="2"><I>%s</I></td>
				  <td class="number">%s</td>
				  <td></td>
				  <td class="number">%s</td>
				</tr>', $ParentGroups[$Level], locale_number_format($GroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($LYGroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']));
		$Level--;
	}
	if ($_POST['Detail'] == 'Detailed') {
		echo '<tr>
				<td colspan="2"></td>
				<td><hr /></td>
				<td></td>
				<td><hr /></td>
				<td></td>
			</tr>';
	}

	printf('<tr>
			  <td colspan="2">%s</td>
			  <td class="number">%s</td>
			  <td></td>
			  <td class="number">%s</td>
		   </tr>', $ParentGroups[$Level], locale_number_format($GroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($LYGroupTotal[$Level], $_SESSION['CompanyRecord']['decimalplaces']));

	if ($_POST['Detail'] == 'Detailed') {
		echo '<tr>
		<td colspan="2"></td>
		<td><hr /></td>
		<td></td>
		<td><hr /></td>
		<td></td>
		</tr>';
	} else {
		echo '<tr>
		<td colspan="3"></td>
		<td><hr /></td>
		<td></td>
		<td><hr /></td>
		</tr>';
	}

	printf('<tr>
		<td colspan="3"><h2>%s</h2></td>
		<td class="number">%s</td>
		<td></td>
		<td class="number">%s</td>
		</tr>', $Sections[$Section], locale_number_format($SectionBalance, $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($SectionBalanceLY, $_SESSION['CompanyRecord']['decimalplaces']));

	$Section = $myrow['sectioninaccounts'];

	if (isset($myrow['sectioninaccounts']) and $_POST['Detail'] == 'Detailed') {
		printf('<tr>
				<td colspan="6"><h1>%s</h1></td>
				</tr>', $Sections[$myrow['sectioninaccounts']]);
	}

	echo '<tr>
			<td colspan="3"></td>
	  		<td><hr /></td>
			<td></td>
			<td><hr /></td>
		</tr>';

	printf('<tr>
		<td colspan="3"><h2>' . _('Check Total') . '</h2></td>
		<td class="number">%s</td>
		<td></td>
		<td class="number">%s</td>
		</tr>', locale_number_format($CheckTotal, $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($LYCheckTotal, $_SESSION['CompanyRecord']['decimalplaces']));

	echo '<tr>
		<td colspan="3"></td>
	  	<td><hr /></td>
		<td></td>
		<td><hr /></td>
		</tr>';

	echo '</table>';
	echo '</div>';
	echo '<br /><div class="centre noPrint"><input type="submit" name="SelectADifferentPeriod" value="' . _('Select A Different Balance Date') . '" /></div>';
	echo '</form>';
}

include('includes/footer.inc');
?>