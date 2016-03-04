<?php

include('includes/session.inc');

$Title = _('Search GL Accounts');

$ViewTopic = 'GeneralLedger';
$BookMark = 'GLAccountInquiry';
include('includes/header.inc');

unset($Result);

if (isset($_POST['Search'])) {

	//insert wildcard characters in spaces
	$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

	$SQL = "SELECT chartmaster.accountcode,
					chartmaster.accountname,
					chartmaster.group_,
					CASE WHEN accountgroups.pandl!=0
						THEN '" . _('Profit and Loss') . "'
						ELSE '" . _('Balance Sheet') . "' END AS pl
				FROM chartmaster
				INNER JOIN accountgroups
					ON chartmaster.groupcode = accountgroups.groupcode
					AND chartmaster.language = accountgroups.language
				INNER JOIN glaccountusers
					ON glaccountusers.accountcode=chartmaster.accountcode
					AND glaccountusers.userid='" .  $_SESSION['UserID'] . "'
					AND glaccountusers.canupd=1
				WHERE accountname " . LIKE . " '" . $SearchString . "'
					AND chartmaster.accountcode " . LIKE . " '" . $_POST['GLCode'] . "%'
					AND chartmaster.groupcode " . LIKE . " '%" . $_POST['GroupCode'] . "%'
					AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
				ORDER BY accountgroups.sequenceintb,
					chartmaster.accountcode";

	$Result = DB_query($SQL);

}

echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . _('Search') . '" alt="' . _('Search for General Ledger Accounts') . '" />' . ' ' . _('Search for General Ledger Accounts') . '</p>';
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class="selection" summary="' . _('Criteria for inquiry') . '">
		<tr>
			<td>' . _('Enter extract of text in the Account name') . ':</td>
			<td><input type="text" name="Keywords" size="20" maxlength="25" /></td>
			<td><b>' . _('OR') . '</b></td>
			<td>' . _('Enter Account No. to search from') . ':</td>
			<td><input type="text" name="GLCode" size="15" maxlength="18" class="number" /></td>
		</tr>';

$GroupSQL = "SELECT groupcode,
					groupname
				FROM accountgroups
				WHERE language='" . $_SESSION['ChartLanguage'] . "'
				ORDER BY sequenceintb";
$GroupResult = DB_query($GroupSQL);

echo '<tr>
		<td>' . _('Search In Account Group') . ':</td>
		<td><select name="GroupCode">';

echo '<option value="%%">' . _('All Account Groups') . '</option>';
while ($GroupRow = DB_fetch_array($GroupResult)) {
	if (isset($_POST['GroupCode']) and $GroupRow['groupcode'] == $_POST['GroupCode']) {
		echo '<option selected="selected" value="', $GroupRow['groupcode'], '">', $GroupRow['groupcode'], ' - ', $GroupRow['groupname'], '</option>';
	} else {
		echo '<option value="', $GroupRow['groupcode'], '">', $GroupRow['groupcode'], ' - ', $GroupRow['groupname'], '</option>';
	}
}
echo '</select></td>
	</tr>
	</table>';

echo '<div class="centre">
		<input type="submit" name="Search" value="' . _('Search Now') . '" />
		<input type="submit" name="reset" value="' . _('Reset') . '" />
	</div></form>';

if (isset($Result) and DB_num_rows($Result) > 0) {

	echo '<form action="GLAccountInquiry.php" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection" summary="' . _('List of GL Accounts') . '">
			<thead>
				<tr>
					<th class="SortedColumn">' . _('Code') . '</th>
					<th class="SortedColumn">' . _('Account Name') . '</th>
					<th class="SortedColumn">' . _('Group') . '</th>
					<th class="SortedColumn">' . _('Account Type') . '</th>
				</tr>
			</thead>';

	echo '<tbody>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr>
				<td>' . htmlspecialchars($MyRow['accountcode'],ENT_QUOTES,'UTF-8',false) . '</td>
				<td>' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</td>
				<td>' . $MyRow['group_'] . '</td>
				<td>' . $MyRow['pl'] . '</td>
				<td>
					<a href="' . $RootPath . '/GLAccountInquiry.php?Account=' . urlencode($MyRow['accountcode']) . '&amp;Show=Yes">
						<img width="24px" src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . _('Inquiry') . '" alt="' . _('Inquiry') . '" />
					</a>
				</td>
				<td>
					<a href="' . $RootPath . '/GLAccounts.php?SelectedAccount=' . urlencode($MyRow['accountcode']) . '">
						<img width="24px" src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Edit') . '" alt="' . _('Edit') . '" />
					</a>
			</tr>';
	}
	//end of while loop

	echo '</tbody>';
	echo '</table>';

}
//end if results to show

echo '</form>';

include('includes/footer.inc');
?>