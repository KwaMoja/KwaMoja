<?php
$PageSecurity=1;
include('includes/session.inc');
$Title = _('General Ledger Retained Earnings Reconciliation');
$ViewTopic = 'GeneralLedger';
$BookMark = 'GLAccountInquiry';
include('includes/header.inc');
include('includes/GLPostings.inc');

echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/transactions.png" title="' . $Title . '" alt="' . $Title . '" />' . ' ' . $Title . '</p>';

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($_POST['Submit'])) {

	$OpeningBalanceSQL = "SELECT SUM(amount)
							FROM gltrans
							WHERE account='" . $_SESSION['CompanyRecord']['retainedearnings'] . "'";
	$OpeningBalanceResult = DB_query($OpeningBalanceSQL);
	$OpeningBalanceRow = DB_fetch_row($OpeningBalanceResult);
	$OpeningBalance = $OpeningBalanceRow[0];

	$SQL = "SELECT periodno,
					lastdate_in_period
				FROM periods
				WHERE MONTH(lastdate_in_period)='" . $_SESSION['YearEnd'] . "'
					AND periodno<='" . $_POST['YearEndDate'] . "'
				ORDER BY periodno ASC";
	$Result = DB_query($SQL);
	echo '<table class="selection">';
	echo '<tr>
			<th>', _('Year End'), '</th>
			<th>', _('Profit/Loss for Year'), '</th>
		</tr>';

	echo '<tr>
			<th class="number">', _('Opening Balances'), '</th>
			<th class="number">', locale_number_format($OpeningBalance,  $_SESSION['CompanyRecord']['decimalplaces']), '</th>
		<tr>';

	$TotalBalance = $OpeningBalance;
	while ($MyRow = DB_fetch_array($Result)) {
		$YearEnd = $MyRow['periodno'];
		$BalanceSQL = "SELECT SUM(amount)
							FROM gltrans
							INNER JOIN chartmaster
								ON gltrans.account=chartmaster.accountcode
							INNER JOIN accountgroups
								ON chartmaster.groupcode=accountgroups.groupcode
								AND chartmaster.language=accountgroups.language
							WHERE pandl=1
								AND periodno<='" . $YearEnd . "'
								AND periodno>'" . ($YearEnd-12) . "'
								AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'";
		$BalanceResult = DB_query($BalanceSQL);
		$BalanceRow = DB_fetch_Row($BalanceResult);
		echo '<tr>
				<th>', ConvertSQLDate($MyRow['lastdate_in_period']), '</th>
				<td class="number">', locale_number_format($BalanceRow[0],  $_SESSION['CompanyRecord']['decimalplaces']), '</td>
			</tr>';
		$TotalBalance += $BalanceRow[0];
	}
	echo '<tr>
			<th class="number">', _('Retained Earnings per Balance Sheet'), '</th>
			<th class="number">', locale_number_format($TotalBalance,  $_SESSION['CompanyRecord']['decimalplaces']), '</th>
		</tr>';
	echo '</table>';
}

$SQL = "SELECT periodno,
				lastdate_in_period
			FROM periods
			WHERE MONTH(lastdate_in_period)='" . $_SESSION['YearEnd'] . "'";
$Result = DB_query($SQL);

echo '<table>
		<tr>
			<td>', _('Select Year End for the Reconcilation'), '</td>
			<td><select name="YearEndDate">';
while ($MyRow = DB_fetch_array($Result)) {
	echo '<option value="', $MyRow['periodno'], '">', $MyRow['lastdate_in_period'], '</option>';
}
echo '</select>
			</td>
		</tr>
	</table>';
echo '<div class="centre">
		<input type="submit" name="Submit" value="', _('Show Reconciliation'), '" />
	</div>';

echo '</form>';

include('includes/footer.inc');
?>