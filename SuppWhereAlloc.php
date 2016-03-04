<?php

include('includes/session.inc');
$Title = _('Supplier How Paid Inquiry');

$ViewTopic = 'APInquiries';
$BookMark = 'WhereAllocated';

include('includes/header.inc');
if (isset($_GET['TransNo']) and isset($_GET['TransType'])) {
	$_POST['TransNo'] = (int) $_GET['TransNo'];
	$_POST['TransType'] = (int) $_GET['TransType'];
	$_POST['ShowResults'] = true;
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<p class="page_title_text noPrint">
		<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/money_add.png" title="' . _('Supplier Where Allocated') . '" alt="" />' . $Title . '
	</p>
	<table class="selection noPrint">
	<tr>
		<td>' . _('Type') . ':</td>
		<td><select tabindex="1" name="TransType"> ';

if (!isset($_POST['TransType'])) {
	$_POST['TransType'] = '20';
}
if ($_POST['TransType'] == 20) {
	echo '<option selected="selected" value="20">' . _('Purchase Invoice') . '</option>
			<option value="22">' . _('Payment') . '</option>
			<option value="21">' . _('Debit Note') . '</option>';
} elseif ($_POST['TransType'] == 22) {
	echo '<option selected="selected" value="22">' . _('Payment') . '</option>
			<option value="20">' . _('Purchase Invoice') . '</option>
			<option value="21">' . _('Debit Note') . '</option>';
} elseif ($_POST['TransType'] == 21) {
	echo '<option selected="selected" value="21">' . _('Debit Note') . '</option>
		<option value="20">' . _('Purchase Invoice') . '</option>
		<option value="22">' . _('Payment') . '</option>';
}

echo '</select></td>';

if (!isset($_POST['TransNo'])) {
	$_POST['TransNo'] = '';
}
echo '<td>' . _('Transaction Number') . ':</td>
		<td><input tabindex="2" type="text" class="number" name="TransNo"  required="required" maxlength="20" size="20" value="' . $_POST['TransNo'] . '" /></td>
	</tr>
	</table>
	<div class="centre noPrint">
		<input tabindex="3" type="submit" name="ShowResults" value="' . _('Show How Allocated') . '" />
	</div>';

if (isset($_POST['ShowResults']) and $_POST['TransNo'] == '') {
	echo '<br />';
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
		$SQL .= " AND supptrans.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
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
					supptrans.supplierno,
					suppreference,
					supptrans.rate,
					ovamount+ovgst as totalamt,
					suppallocs.amt
				FROM supptrans
				INNER JOIN suppallocs ";
		if ($_POST['TransType'] == 22 or $_POST['TransType'] == 21) {

			$TitleInfo = ($_POST['TransType'] == 22) ? _('Payment') : _('Debit Note');
			$SQL .= "ON supptrans.id = suppallocs.transid_allocto
				WHERE suppallocs.transid_allocfrom = '" . $AllocToID . "'";
		} else {
			$TitleInfo = _('invoice');
			$SQL .= "ON supptrans.id = suppallocs.transid_allocfrom
				WHERE suppallocs.transid_allocto = '" . $AllocToID . "'";
		}
		$SQL .= " ORDER BY transno ";

		$ErrMsg = _('The customer transactions for the selected criteria could not be retrieved because');
		$TransResult = DB_query($SQL, $ErrMsg);

		if (DB_num_rows($TransResult) == 0) {

			if ($MyRow['totamt'] > 0 AND ($_POST['TransType'] == 22 OR $_POST['TransType'] == 21)) {
				prnMsg(_('This transaction was a receipt of funds and there can be no allocations of receipts or credits to a receipt. This inquiry is meant to be used to see how a payment which is entered as a negative receipt is settled against credit notes or receipts'), 'info');
			} else {
				prnMsg(_('There are no allocations made against this transaction'), 'info');
			}
		} else {
			echo '<div id="Report">
				<table class="selection">';

			echo '<tr>
					<th colspan="6">
					<div class="centre">
						<b>' . _('Allocations made against') . ' ' . $TitleInfo . ' ' . _('number') . ' ' . $_POST['TransNo'] . '<br />' . _('Transaction Total') . ': ' . locale_number_format($MyRow['totamt'], $CurrDecimalPlaces) . ' ' . $CurrCode . '</b>
						<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/printer.png" class="PrintIcon" title="' . _('Print') . '" alt="' . _('Print') . '" onclick="window.print();" />
					</div>
					</th>
				</tr>';

			echo '<tr>
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
					$k++;
				}

				if ($MyRow['type'] == 21) {
					$TransType = _('Debit Note');
				} elseif ($MyRow['type'] == 20) {
					$TransType = _('Purchase Invoice');
				} else {
					$TransType = _('Payment');
				}
				echo '<td>' . $TransType . '</td>
					<td>' . $MyRow['transno'] . '</td>
					<td>' . $MyRow['suppreference'] . '</td>
					<td>' . $MyRow['rate'] . '</td>
					<td class="number">' . locale_number_format($MyRow['totalamt'], $CurrDecimalPlaces) . '</td>
					<td class="number">' . locale_number_format($MyRow['amt'], $CurrDecimalPlaces) . '</td>
				</tr>';

				//end of page full new headings if
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
echo '</form>';

include('includes/footer.inc');

?>