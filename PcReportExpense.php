<?php
include ('includes/session.php');
$Title = _('Petty Cash Expense Management Report');
$ViewTopic = 'PettyCash';
$BookMark = 'PcReportExpense';

if (isset($_GET['download'])) {
	$SQL = "SELECT type,
					size,
					content
				FROM pcreceipts
				WHERE pccashdetail='" . $_GET['receipt'] . "'
					AND name='" . $_GET['name'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	header('Content-type: ' . $MyRow['type'] . "\n");
	header('Content-Disposition: attachment; filename=' . $_GET['name'] . "\n");
	header('Content-Length: ' . $MyRow['size'] . "\n");
	echo $MyRow['content'];
	exit;
}

include ('includes/SQL_CommonFunctions.php');
include ('includes/header.php');

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/money_add.png" title="', _('PC Expense Report'), '" alt="" />', ' ', $Title, '
	</p>';

echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

if (isset($_POST['SelectedExpense'])) {
	$SelectedExpense = mb_strtoupper($_POST['SelectedExpense']);
} elseif (isset($_GET['SelectedExpense'])) {
	$SelectedExpense = mb_strtoupper($_GET['SelectedExpense']);
}

if ((!isset($_POST['FromDate']) and !isset($_POST['ToDate'])) or isset($_POST['SelectDifferentDate'])) {

	if (!isset($_POST['FromDate'])) {
		$_POST['FromDate'] = Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, Date('m'), 1, Date('Y')));
	}

	if (!isset($_POST['ToDate'])) {
		$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
	}

	/*Show a form to allow input of criteria for Expenses to show */
	echo '<fieldset>
			<legend>', _('Report Criteria'), '</legend>';

	$SQL = "SELECT DISTINCT(pctabexpenses.codeexpense)
					FROM pctabs
					INNER JOIN pctabexpenses
						ON pctabexpenses.typetabcode = pctabs.typetabcode
					WHERE ( pctabs.authorizer='" . $_SESSION['UserID'] . "' OR pctabs.usercode ='" . $_SESSION['UserID'] . "' OR pctabs.assigner ='" . $_SESSION['UserID'] . "' )
					ORDER BY pctabexpenses.codeexpense";
	$Result = DB_query($SQL);
	echo '<field>
			<label for="SelectedExpense">', _('Code Of Petty Cash Expense'), ':</label>
			<select name="SelectedExpense">';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['SelectedExpense']) and $MyRow['codeexpense'] == $_POST['SelectedExpense']) {
			echo '<option selected="selected" value="', $MyRow['codeexpense'], '">', $MyRow['codeexpense'], '</option>';
		} else {
			echo '<option value="', $MyRow['codeexpense'], '">', $MyRow['codeexpense'], '</option>';
		}

	} //end while loop get type of tab
	echo '</select>
		</field>';

	echo '<field>
			<label for="FromDate">', _('From Date'), ':</label>
			<input class="date" type="text" name="FromDate" maxlength="10" size="11" value="', $_POST['FromDate'], '" />
		</field>';

	echo '<field>
			<label for="ToDate">', _('To Date'), ':</label>
			<input class="date" type="text" name="ToDate" maxlength="10" size="11" value="', $_POST['ToDate'], '" />
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="ShowTB" value="', _('Show HTML'), '" />
		</div>
	</form>';

} else {

	$SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
	$SQL_ToDate = FormatDateForSQL($_POST['ToDate']);

	echo '<input type="hidden" name="FromDate" value="', $_POST['FromDate'], '" />';
	echo '<input type="hidden" name="ToDate" value="', $_POST['ToDate'], '" />';

	echo '<table>
			<tr>
				<td>', _('Expense Code'), ':</td>
				<td>', $SelectedExpense, '</td>
			</tr>
			<tr>
				<td>', _('From'), ':</td>
				<td>', $_POST['FromDate'], '</td>
			</tr>
			<tr>
				<td>', _('To'), ':</td>
				<td>', $_POST['ToDate'], '</td>
			</tr>
		</table>';

	$SQL = "SELECT pcashdetails.counterindex,
					pcashdetails.date,
					pcashdetails.tabcode,
					pcashdetails.amount,
					pcashdetails.notes,
					pcashdetails.receipt,
					pcashdetails.authorized,
					pctabs.currency,
					currencies.decimalplaces
			FROM pcashdetails
			INNER JOIN pctabs
				ON pcashdetails.tabcode = pctabs.tabcode
			INNER JOIN currencies
				ON pctabs.currency = currencies.currabrev
			WHERE codeexpense='" . $SelectedExpense . "'
				AND date >='" . $SQL_FromDate . "'
				AND date <= '" . $SQL_ToDate . "'
				AND (pctabs.authorizer='" . $_SESSION['UserID'] . "' OR pctabs.usercode ='" . $_SESSION['UserID'] . "' OR pctabs.assigner ='" . $_SESSION['UserID'] . "')
			ORDER BY date,
					counterindex ASC";

	$TabDetail = DB_query($SQL, _('No Petty Cash movements for this expense code were returned by the SQL because'), _('The SQL that failed was:'));

	echo '<table>
			<tr>
				<th>', _('Date'), '</th>
				<th>', _('Tab'), '</th>
				<th>', _('Amount'), '</th>
				<th>', _('Currency'), '</th>
				<th>', _('Notes'), '</th>
				<th>', _('Receipt'), '</th>
				<th>', _('Authorised'), '</th>
			</tr>';

	while ($MyRow = DB_fetch_array($TabDetail)) {
		$ReceiptSQL = "SELECT name
							FROM pcreceipts
							WHERE pccashdetail='" . $MyRow['counterindex'] . "'";
		$ReceiptResult = DB_query($ReceiptSQL);
		if (DB_num_rows($ReceiptResult) > 0) {
			$ReceiptRow = DB_fetch_array($ReceiptResult);
			$ReceiptText = '<a href="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '?download=yes&receipt=' . urlencode($MyRow['counterindex']) . '&name=' . urlencode($ReceiptRow['name']) . '">' . _('View receipt') . '</a>';
		} else {
			$ReceiptText = _('No receipt');
		}

		echo '<tr class="striped_row">
				<td>', ConvertSQLDate($MyRow['date']), '</td>
				<td>', $MyRow['tabcode'], '</td>
				<td class="number">', locale_number_format($MyRow['amount'], $MyRow['decimalplaces']), '</td>
				<td>', $MyRow['currency'], '</td>
				<td>', $MyRow['notes'], '</td>
				<td>', $ReceiptText, '</td>
				<td>', ConvertSQLDate($MyRow['authorized']), '</td>
			</tr>';
	}

	echo '</table>';

	echo '<div class="centre">
			<input type="submit" name="SelectDifferentDate" value="' . _('Select A Different Date') . '" />
		</div>';

	echo '</form>';
}
include ('includes/footer.php');

?>