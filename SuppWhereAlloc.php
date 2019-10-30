<?php
include ('includes/session.php');
$Title = _('Supplier How Paid Inquiry');

$ViewTopic = 'APInquiries';
$BookMark = 'WhereAllocated';

include ('includes/header.php');
if (isset($_GET['TransNo']) and isset($_GET['TransType'])) {
	$_POST['TransNo'] = (int)$_GET['TransNo'];
	$_POST['TransType'] = (int)$_GET['TransType'];
	$_POST['ShowResults'] = true;
}

echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

echo '<p class="page_title_text noPrint">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/money_add.png" title="', _('Supplier Where Allocated'), '" alt="" />', $Title, '
	</p>';

if (!isset($_POST['TransType'])) {
	$_POST['TransType'] = '20';
}
echo '<fieldset>
		<legend>', _('Report Criteria'), '</legend>';

echo '<field>
		<label for="TransType">', _('Type'), ':</label>
		<select name="TransType"> ';
if ($_POST['TransType'] == 20) {
	echo '<option selected="selected" value="20">', _('Purchase Invoice'), '</option>
			<option value="22">', _('Payment'), '</option>
			<option value="21">', _('Debit Note'), '</option>';
} elseif ($_POST['TransType'] == 22) {
	echo '<option selected="selected" value="22">', _('Payment'), '</option>
			<option value="20">', _('Purchase Invoice'), '</option>
			<option value="21">', _('Debit Note'), '</option>';
} elseif ($_POST['TransType'] == 21) {
	echo '<option selected="selected" value="21">', _('Debit Note'), '</option>
			<option value="20">', _('Purchase Invoice'), '</option>
			<option value="22">', _('Payment'), '</option>';
}
echo '</select>
	</field>';

if (!isset($_POST['TransNo'])) {
	$_POST['TransNo'] = '';
}
echo '<field>
		<label for="TransNo">', _('Transaction Number'), ':</label>
		<input type="text" class="number" name="TransNo"  required="required" maxlength="20" size="20" value="', $_POST['TransNo'], '" />
	</field>';

echo '</fieldset>';

echo '<div class="centre noPrint">
		<input type="submit" name="ShowResults" value="', _('Show How Allocated'), '" />
	</div>';

if (isset($_POST['ShowResults']) and $_POST['TransNo'] == '') {
	prnMsg(_('The transaction number to be queried must be entered first'), 'warn');
}

if (isset($_POST['ShowResults']) and $_POST['TransNo'] != '') {

	/*First off get the DebtorTransID of the transaction (invoice normally) selected */
	$SQL = "SELECT supptrans.id,
				ovamount+ovgst AS totamt,
				currencies.decimalplaces AS currdecimalplaces,
				suppliers.currcode
			FROM supptrans INNER JOIN suppliers
			ON supptrans.supplierno=suppliers.supplierid
			INNER JOIN currencies
			ON suppliers.currcode=currencies.currabrev
			WHERE type='" . $_POST['TransType'] . "'
			AND transno = '" . $_POST['TransNo'] . "'";

	if ($_SESSION['SalesmanLogin'] != '') {
		$SQL.= " AND supptrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
	}
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {
		$MyRow = DB_fetch_array($Result);
		$AllocToID = $MyRow['id'];
		$CurrCode = $MyRow['currcode'];
		$CurrDecimalPlaces = $MyRow['currdecimalplaces'];
		$SQL = "SELECT type,
					transno,
					trandate,
					supptrans.supplierno,
					suppreference,
					supptrans.rate,
					ovamount+ovgst as totalamt,
					suppallocs.amt
				FROM supptrans
				INNER JOIN suppallocs ";
		if ($_POST['TransType'] == 22 or $_POST['TransType'] == 21) {

			$TitleInfo = ($_POST['TransType'] == 22) ? _('Payment') : _('Debit Note');
			$SQL.= "ON supptrans.id = suppallocs.transid_allocto
				WHERE suppallocs.transid_allocfrom = '" . $AllocToID . "'";
		} else {
			$TitleInfo = _('invoice');
			$SQL.= "ON supptrans.id = suppallocs.transid_allocfrom
				WHERE suppallocs.transid_allocto = '" . $AllocToID . "'";
		}
		$SQL.= " ORDER BY transno ";

		$ErrMsg = _('The customer transactions for the selected criteria could not be retrieved because');
		$TransResult = DB_query($SQL, $ErrMsg);

		if (DB_num_rows($TransResult) == 0) {

			if ($MyRow['totamt'] > 0 and ($_POST['TransType'] == 22 or $_POST['TransType'] == 21)) {
				prnMsg(_('This transaction was a receipt of funds and there can be no allocations of receipts or credits to a receipt. This inquiry is meant to be used to see how a payment which is entered as a negative receipt is settled against credit notes or receipts'), 'info');
			} else {
				prnMsg(_('There are no allocations made against this transaction'), 'info');
			}
		} else {
			echo '<table>';

			echo '<tr>
					<th colspan="7">
					<div class="centre">
						<b>', _('Allocations made against'), ' ', $TitleInfo, ' ', _('number'), ' ', $_POST['TransNo'], '<br />', _('Transaction Total'), ': ', locale_number_format($MyRow['totamt'], $CurrDecimalPlaces), ' ', $CurrCode, '</b>
						<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/printer.png" class="PrintIcon" data-title="', _('Print'), '" alt="', _('Print'), '" onclick="window.print();" />
					</div>
					</th>
				</tr>';

			echo '<tr>
					<th>', _('Date'), '</th>
					<th>', _('Type'), '</th>
					<th>', _('Number') . '</th>
					<th>', _('Reference'), '</th>
					<th>', _('Ex Rate'), '</th>
					<th>', _('Amount'), '</th>
					<th>', _('Alloc'), '</th>
				</tr>';

			$AllocsTotal = 0;

			while ($MyRow = DB_fetch_array($TransResult)) {

				if ($MyRow['type'] == 21) {
					$TransType = _('Debit Note');
				} elseif ($MyRow['type'] == 20) {
					$TransType = _('Purchase Invoice');
				} else {
					$TransType = _('Payment');
				}
				echo '<tr class="striped_row">
						<td>', ConvertSQLDate($MyRow['trandate']), '</td>
						<td>', $TransType, '</td>
						<td>', $MyRow['transno'], '</td>
						<td>', $MyRow['suppreference'], '</td>
						<td>', $MyRow['rate'], '</td>
						<td class="number">', locale_number_format($MyRow['totalamt'], $CurrDecimalPlaces), '</td>
						<td class="number">', locale_number_format($MyRow['amt'], $CurrDecimalPlaces), '</td>
					</tr>';

				//end of page full new headings if
				$AllocsTotal+= $MyRow['amt'];
			}
			//end of while loop
			echo '<tr class="total_row">
					<td colspan="6" class="number">', _('Total allocated'), '</td>
					<td class="number">', locale_number_format($AllocsTotal, $CurrDecimalPlaces), '</td>
				</tr>
			</table>';
		} // end if there are allocations against the transaction
		
	} else {
		prnMsg(_('This transaction does not exist as yet'), 'info');
	}
}
echo '</form>';

include ('includes/footer.php');

?>