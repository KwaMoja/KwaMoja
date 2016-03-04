<?php

include('includes/session.inc');
$Title = _('Customer How Paid Inquiry');
/* Manual links before header.inc */
$ViewTopic = 'ARInquiries';
$BookMark = 'WhereAllocated';
include('includes/header.inc');

if (isset($_GET['TransNo']) and isset($_GET['TransType'])) {
	$_POST['TransNo'] = (int)$_GET['TransNo'];
	$_POST['TransType'] = (int)$_GET['TransType'];
	$_POST['ShowResults'] = true;
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text noPrint" >
		<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/money_add.png" title="' . _('Customer Where Allocated') . '" alt="' . _('Customer Where Allocated') . '" />' . $Title . '
	</p>
	<table class="selection noPrint" summary="' . _('Select criteria for the where used inquiry') . '">
	<tr>
		<td>' . _('Type') . ':</td>
		<td><select tabindex="1" name="TransType"> ';

if (!isset($_POST['TransType'])) {
	$_POST['TransType'] = '10';
}

if ($_POST['TransType'] == 10) {
	 echo '<option selected="selected" value="10">' . _('Invoice') . '</option>
			<option value="12">' . _('Receipt') . '</option>
			<option value="11">' . _('Credit Note') . '</option>';
} elseif ($_POST['TransType'] == 12) {
	echo '<option selected="selected" value="12">' . _('Receipt') . '</option>
			<option value="10">' . _('Invoice') . '</option>
			<option value="11">' . _('Credit Note') . '</option>';
} elseif ($_POST['TransType'] == 11) {
	echo '<option selected="selected" value="11">' . _('Credit Note') . '</option>
		<option value="10">' . _('Invoice') . '</option>
		<option value="12">' . _('Receipt') . '</option>';
}

echo '</select>
		</td>';

if (!isset($_POST['TransNo'])) {
	$_POST['TransNo'] = '';
}
echo '<td>' . _('Transaction Number') . ':</td>
		<td><input class="number" tabindex="2" type="text" name="TransNo" required="required" maxlength="10" size="10" value="' . $_POST['TransNo'] . '" /></td>
	</tr>
	</table>
	<div class="centre noPrint">
		<input tabindex="3" type="submit" name="ShowResults" value="' . _('Show How Allocated') . '" />
	</div>
	</form>';

if (isset($_POST['ShowResults']) and $_POST['TransNo'] == '') {
	echo '<br />';
	prnMsg(_('The transaction number to be queried must be entered first'), 'warn');
}

if (isset($_POST['ShowResults']) and $_POST['TransNo'] != '') {


	/*First off get the DebtorTransID of the transaction (invoice normally) selected */
	$SQL = "SELECT debtortrans.id,
				ovamount+ovgst AS totamt,
				currencies.decimalplaces AS currdecimalplaces,
				debtorsmaster.currcode
			FROM debtortrans
			INNER JOIN debtorsmaster
				ON debtortrans.debtorno=debtorsmaster.debtorno
			INNER JOIN currencies
				ON debtorsmaster.currcode=currencies.currabrev
			WHERE type='" . $_POST['TransType'] . "'
				AND transno = '" . $_POST['TransNo'] . "'";

	if ($_SESSION['SalesmanLogin'] != '') {
			$SQL .= " AND debtortrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
	}

	$Result = DB_query($SQL);

	if (DB_num_rows($Result) == 1) {
		$MyRow = DB_fetch_array($Result);
		$AllocToID = $MyRow['id'];
		$CurrCode = $MyRow['currcode'];
		$CurrDecimalPlaces = $MyRow['currdecimalplaces'];

		$SQL = "SELECT type,
					transno,
					trandate,
					debtortrans.debtorno,
					reference,
					debtortrans.rate,
					ovamount+ovgst+ovfreight+ovdiscount as totalamt,
					custallocns.amt
				FROM debtortrans
				INNER JOIN custallocns ";
		if ($_POST['TransType'] == 12 or $_POST['TransType'] == 11) {

			$TitleInfo = ($_POST['TransType'] == 12)?_('Receipt'):_('Credit Note');
			$SQL .= "ON debtortrans.id = custallocns.transid_allocto
				WHERE custallocns.transid_allocfrom = '" . $AllocToID . "'";
		} else {
			$TitleInfo = _('invoice');
			$SQL .= "ON debtortrans.id = custallocns.transid_allocfrom
				WHERE custallocns.transid_allocto = '" . $AllocToID . "'";
		}
		$SQL .= " ORDER BY transno ";

		$ErrMsg = _('The customer transactions for the selected criteria could not be retrieved because');
		$TransResult = DB_query($SQL, $ErrMsg);

		if (DB_num_rows($TransResult) == 0) {
			prnMsg(_('There are no allocations made against this transaction'), 'info');

			if ($MyRow['totamt'] < 0 and ($_POST['TransType'] == 12 or $_POST['TransType'] == 11)) {
				prnMsg(_('This transaction was a receipt of funds and there can be no allocations of receipts or credits to a receipt. This inquiry is meant to be used to see how a payment which is entered as a negative receipt is settled against credit notes or receipts'),'info');
			} else {
				prnMsg(_('There are no allocations made against this transaction'),'info');
			}
		} else {
			$Printer = true;
			echo '<div id="Report">';
			echo '<table class="selection" summary="' . _('Allocations made against invoice number') . ' ' . $_POST['TransNo'] . '">';

			echo '<tr>
					<th colspan="6">
					<div class="centre">
						<b>' . _('Allocations made against') . ' ' . $TitleInfo . ' ' . _('number') . ' ' . $_POST['TransNo'] . '<br />' . _('Transaction Total') . ': ' . locale_number_format($MyRow['totamt'], $CurrDecimalPlaces) . ' ' . $CurrCode . '</b>
						<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/printer.png" class="PrintIcon" title="' . _('Print') . '" alt="' . _('Print') . '" onclick="window.print();" />
					</div>
					</th>
				</tr>
				<tr>
					<th>' . _('Type') . '</th>
					<th>' . _('Number') . '</th>
					<th>' . _('Reference') . '</th>
					<th>' . _('Ex Rate') . '</th>
					<th>' . _('Amount') . '</th>
					<th>' . _('Alloc') . '</th>
				</tr>';

			$k = 0; //row colour counter
			$AllocsTotal = 0;

			while ($MyRow = DB_fetch_array($TransResult)) {
				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} else {
					echo '<tr class="OddTableRows">';
					++$k;
				}

				if ($MyRow['type'] == 11) {
					$TransType = _('Credit Note');
				} elseif ($MyRow['type'] == 10){
					$TransType = _('Invoice');
				} else {
					$TransType = _('Receipt');
				}
				echo '<td>' . $TransType . '</td>
					<td>' . $MyRow['transno'] . '</td>
					<td>' . $MyRow['reference'] . '</td>
					<td>' . $MyRow['rate'] . '</td>
					<td class="number">' . locale_number_format($MyRow['totalamt'], $CurrDecimalPlaces) . '</td>
					<td class="number">' . locale_number_format($MyRow['amt'], $CurrDecimalPlaces) . '</td>
					</tr>';

				$AllocsTotal += $MyRow['amt'];
			}
			//end of while loop
			echo '<tr>
					<td colspan="5" class="number">' . _('Total allocated') . '</td>
					<td class="number">' . locale_number_format($AllocsTotal, $CurrDecimalPlaces) . '</td>
				</tr>
			</table>
		</div>';
		} // end if there are allocations against the transaction
	} else {
		prnMsg( _('This transaction does not exist as yet'), 'info');
	}
}
include('includes/footer.inc');

?>