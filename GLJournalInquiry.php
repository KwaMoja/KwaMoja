<?php
include ('includes/session.php');
$Title = _('General Ledger Journal Inquiry');
$ViewTopic = 'GeneralLedger';
$BookMark = 'GLJournalInquiry';
include ('includes/header.php');

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/money_add.png" title="', $Title, '" alt="', $Title, '" />', ' ', $Title, '
	</p>';

if (!isset($_POST['Show'])) {
	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<fieldset>
			<legend>', _('Selection Criteria'), '</legend>';

	$SQL = "SELECT typeno FROM systypes WHERE typeid=0";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$MaxJournalNumberUsed = $MyRow['typeno'];

	echo '<field>
			<label>', _('Journal Number Range'), ' (', _('Between'), ' 1 ', _('and'), ' ', $MaxJournalNumberUsed, ')</label>
			', _('From'), ':', '&nbsp;&nbsp;&nbsp;<input type="text" class="number" name="NumberFrom" size="10" required="required" maxlength="11" value="1" />', '
			', _('To'), ':', '&nbsp;&nbsp;&nbsp;<input type="text" class="number" name="NumberTo" size="10" required="required" maxlength="11" value="', $MaxJournalNumberUsed, '" />
		</field>';

	$SQL = "SELECT MIN(trandate) AS fromdate,
					MAX(trandate) AS todate FROM gltrans WHERE type=0";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	if (isset($MyRow['fromdate']) and $MyRow['fromdate'] != '') {
		$FromDate = $MyRow['fromdate'];
		$ToDate = $MyRow['todate'];
	} else {
		$FromDate = date('Y-m-d');
		$ToDate = date('Y-m-d');
	}

	echo '<field>
			<label>', _('Journals Dated Between'), ':</label>
			', _('From'), ':', '&nbsp;&nbsp;&nbsp;<input type="text" name="FromTransDate" class="date" maxlength="10" size="11" value="', ConvertSQLDate($FromDate), '" />
			', _('To'), ':', '&nbsp;&nbsp;&nbsp;<input type="text" name="ToTransDate" class="date" maxlength="10" size="11" value="', ConvertSQLDate($ToDate), '" />
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="Show" value"', _('Show transactions'), '" />
		</div>';

	echo '</form>';
} else {

	$SQL = "SELECT gltrans.counterindex,
					gltrans.typeno,
					gltrans.trandate,
					gltrans.account,
					chartmaster.accountname,
					gltrans.narrative,
					gltrans.amount,
					gltrans.jobref
				FROM gltrans
				INNER JOIN chartmaster
					ON gltrans.account=chartmaster.accountcode
			WHERE gltrans.type='0'
				AND gltrans.trandate>='" . FormatDateForSQL($_POST['FromTransDate']) . "'
				AND gltrans.trandate<='" . FormatDateForSQL($_POST['ToTransDate']) . "'
				AND gltrans.typeno>='" . $_POST['NumberFrom'] . "'
				AND gltrans.typeno<='" . $_POST['NumberTo'] . "'
				AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
			ORDER BY gltrans.typeno,
					gltrans.account";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 0) {
		prnMsg(_('There are no transactions for this account in the date range selected'), 'info');
	} else {
		echo '<table summary="', _('General ledger journal listing'), '">
			<tr>
				<th colspan="9">
					<b>', _('General Ledger Jornals'), '</b>
					<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/printer.png" class="PrintIcon" title="', _('Print'), '" alt="', _('Print'), '" onclick="window.print();" />
				</th>
			</tr>
			<tr>
				<th>', _('Date'), '</th>
				<th>', _('Journal Number'), '</th>
				<th>', _('Account Code'), '</th>
				<th>', _('Account Description'), '</th>
				<th>', _('Narrative'), '</th>
				<th>', _('Amount'), ' ', $_SESSION['CompanyRecord']['currencydefault'], '</th>
				<th>', _('Tag'), '</th>
				<th></th>
			</tr>';

		$LastJournal = 0;

		while ($MyRow = DB_fetch_array($Result)) {

			$TagsSQL = "SELECT gltags.tagref,
								tags.tagdescription
							FROM gltags
							INNER JOIN tags
								ON gltags.tagref=tags.tagref
							WHERE gltags.counterindex='" . $MyRow['counterindex'] . "'";
			$TagsResult = DB_query($TagsSQL);

			$TagDescriptions = '';
			while ($TagRows = DB_fetch_array($TagsResult)) {
				$TagDescriptions.= $TagRows['tagref'] . ' - ' . $TagRows['tagdescription'] . '<br />';
			}

			if ($MyRow['typeno'] != $LastJournal) {
				echo '<tr>
						<td colspan="8"></td>
					</tr>
					<tr class="striped_row">
						<td valign="top">', ConvertSQLDate($MyRow['trandate']), '</td>
						<td valign="top" class="number">', $MyRow['typeno'], '</td>';

			} else {
				echo '<tr class="striped_row">
						<td valign="top" colspan="2"></td>';
			}

			// if user is allowed to see the account we show it, other wise we show "OTHERS ACCOUNTS"
			$CheckSql = "SELECT count(*)
						 FROM glaccountusers
						 WHERE accountcode= '" . $MyRow['account'] . "'
							 AND userid = '" . $_SESSION['UserID'] . "'
							 AND canview = '1'";
			$CheckResult = DB_query($CheckSql);
			$CheckRow = DB_fetch_row($CheckResult);

			if ($CheckRow[0] > 0) {
				echo '<td valign="top">', $MyRow['account'], '</td>
						<td valign="top">', $MyRow['accountname'], '</td>';
			} else {
				echo '<td valign="top">', _('Others'), '</td>
						<td valign="top">', _('Other GL Accounts'), '</td>';
			}

			echo '<td valign="top">', $MyRow['narrative'], '</td>
					<td valign="top" class="number">', locale_number_format($MyRow['amount'], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
					<td valign="top" class="number">', $TagDescriptions, '</td>';

			if ($MyRow['typeno'] != $LastJournal) {
				echo '<td valign="top" class="number"><a href="PDFGLJournal.php?JournalNo=', urlencode($MyRow['typeno']), '">', _('Print'), '</a></td></tr>';

				$LastJournal = $MyRow['typeno'];
			} else {
				echo '<td colspan="1"></td></tr>';
			}

		}
		echo '</table>';
	} //end if no bank trans in the range to show
	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<div class="centre"><input type="submit" name="Return" value="', _('Select Another Date'), '" /></div>';
	echo '</form>';
}
include ('includes/footer.php');

?>