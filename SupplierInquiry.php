<?php

include('includes/session.inc');
$Title = _('Supplier Inquiry');
$ViewTopic = 'AccountsPayable';// Filename in ManualContents.php's TOC./* RChacon: Is there any content for Supplier Inquiry? */
$BookMark = 'AccountsPayable';// Anchor's id in the manual's html document.
include('includes/header.inc');

include('includes/SQL_CommonFunctions.inc');

// always figure out the SQL required from the inputs available

if (!isset($_GET['SupplierID']) and !isset($_SESSION['SupplierID'])) {
	echo '<br />' . _('To display the enquiry a Supplier must first be selected from the Supplier selection screen') . '<br />
			<div class="centre">
				<a href="' . $RootPath . '/SelectSupplier.php">' . _('Select a Supplier to Inquire On') . '</a>
			</div>';
	include('includes/footer.inc');
	exit;
} else {
	if (isset($_GET['SupplierID'])) {
		$_SESSION['SupplierID'] = $_GET['SupplierID'];
	}
	$SupplierID = $_SESSION['SupplierID'];
}

if (isset($_GET['FromDate'])) {
	$_POST['TransAfterDate'] = $_GET['FromDate'];
}
if (!isset($_POST['TransAfterDate']) or !is_date($_POST['TransAfterDate'])) {
	$_POST['TransAfterDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, Date('m') - 12, Date('d'), Date('Y')));
}


$SQL = "SELECT suppliers.suppname,
		suppliers.currcode,
		currencies.currency,
		currencies.decimalplaces AS currdecimalplaces,
		paymentterms.terms,
		SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS balance,
		SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) >= paymentterms.daysbeforedue
			THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		ELSE
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= 0 THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		END) AS due,
		SUM(CASE WHEN paymentterms.daysbeforedue > 0  THEN
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) > paymentterms.daysbeforedue
					AND (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays1'] . ")
			THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		ELSE
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= '" . $_SESSION['PastDueDays1'] . "')
			THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		END) AS overdue1,
		Sum(CASE WHEN paymentterms.daysbeforedue > 0 THEN
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + " . $_SESSION['PastDueDays2'] . ")
			THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		ELSE
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, " . INTERVAL('1', 'MONTH') . "), " . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ")) >= '" . $_SESSION['PastDueDays2'] . "')
			THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		END ) AS overdue2
		FROM suppliers INNER JOIN paymentterms
		ON suppliers.paymentterms = paymentterms.termsindicator
	 	INNER JOIN currencies
	 	ON suppliers.currcode = currencies.currabrev
	 	INNER JOIN supptrans
	 	ON suppliers.supplierid = supptrans.supplierno
		WHERE suppliers.supplierid = '" . $SupplierID . "'
		GROUP BY suppliers.suppname,
	  			currencies.currency,
	  			currencies.decimalplaces,
	  			paymentterms.terms,
	  			paymentterms.daysbeforedue,
	  			paymentterms.dayinfollowingmonth";

$ErrMsg = _('The supplier details could not be retrieved by the SQL because');
$DbgMsg = _('The SQL that failed was');

$SupplierResult = DB_query($SQL, $ErrMsg, $DbgMsg);

if (DB_num_rows($SupplierResult) == 0) {

	/*Because there is no balance - so just retrieve the header information about the Supplier - the choice is do one query to get the balance and transactions for those Suppliers who have a balance and two queries for those who don't have a balance OR always do two queries - I opted for the former */

	$NIL_BALANCE = True;

	$SQL = "SELECT suppliers.suppname,
					suppliers.currcode,
					currencies.currency,
					currencies.decimalplaces AS currdecimalplaces,
					paymentterms.terms
			FROM suppliers INNER JOIN paymentterms
			ON suppliers.paymentterms = paymentterms.termsindicator
			INNER JOIN currencies
			ON suppliers.currcode = currencies.currabrev
			WHERE suppliers.supplierid = '" . $SupplierID . "'";

	$ErrMsg = _('The supplier details could not be retrieved by the SQL because');
	$DbgMsg = _('The SQL that failed was');

	$SupplierResult = DB_query($SQL, $ErrMsg, $DbgMsg);

} else {
	$NIL_BALANCE = False;
}

$SupplierRecord = DB_fetch_array($SupplierResult);

if ($NIL_BALANCE == True) {
	$SupplierRecord['balance'] = 0;
	$SupplierRecord['due'] = 0;
	$SupplierRecord['overdue1'] = 0;
	$SupplierRecord['overdue2'] = 0;
}

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/supplier.png" title="' . _('Supplier') . '" alt="" /> ' .
		_('Supplier') . ': ' .
			stripslashes($SupplierID) . ' - ' . $SupplierRecord['suppname'] . '<br />' .
		_('All amounts stated in') . ': ' .
			$SupplierRecord['currcode'] . ' - ' . _($SupplierRecord['currency']) . '<br />' .
		_('Terms') . ': ' .
			$SupplierRecord['terms'] . '</p>';

if (isset($_GET['HoldType']) and isset($_GET['HoldTrans'])) {

	if ($_GET['HoldStatus'] == _('Hold')) {
		$SQL = "UPDATE supptrans SET hold=1
				WHERE type='" . $_GET['HoldType'] . "'
				AND transno='" . $_GET['HoldTrans'] . "'";
	} elseif ($_GET['HoldStatus'] == _('Release')) {
		$SQL = "UPDATE supptrans SET hold=0
				WHERE type='" . $_GET['HoldType'] . "'
				AND transno='" . $_GET['HoldTrans'] . "'";
	}

	$ErrMsg = _('The Supplier Transactions could not be updated because');
	$DbgMsg = _('The SQL that failed was');
	$UpdateResult = DB_query($SQL, $ErrMsg, $DbgMsg);

}

echo '<table width="90%" class="selection">
		<tr>
			<th>' . _('Total Balance') . '</th>
			<th>' . _('Current') . '</th>
			<th>' . _('Now Due') . '</th>
			<th>' . $_SESSION['PastDueDays1'] . '-' . $_SESSION['PastDueDays2'] . ' ' . _('Days Overdue') . '</th>
			<th>' . _('Over') . ' ' . $_SESSION['PastDueDays2'] . ' ' . _('Days Overdue') . '</th>
		</tr>';

echo '<tr>
		  <td class="number">' . locale_number_format($SupplierRecord['balance'], $SupplierRecord['currdecimalplaces']) . '</td>
		  <td class="number">' . locale_number_format(($SupplierRecord['balance'] - $SupplierRecord['due']), $SupplierRecord['currdecimalplaces']) . '</td>
		  <td class="number">' . locale_number_format(($SupplierRecord['due'] - $SupplierRecord['overdue1']), $SupplierRecord['currdecimalplaces']) . '</td>
		  <td class="number">' . locale_number_format(($SupplierRecord['overdue1'] - $SupplierRecord['overdue2']), $SupplierRecord['currdecimalplaces']) . '</td>
		  <td class="number">' . locale_number_format($SupplierRecord['overdue2'], $SupplierRecord['currdecimalplaces']) . '</td>
	  </tr>
	</table>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo _('Show all transactions after') . ': ' . '<input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="TransAfterDate" value="' . $_POST['TransAfterDate'] . '" required="required" maxlength="10" size="10" />
		<input type="submit" name="Refresh Inquiry" value="' . _('Refresh Inquiry') . '" />
	</form>';

$DateAfterCriteria = FormatDateForSQL($_POST['TransAfterDate']);

$SQL = "SELECT supptrans.id,
			systypes.typename,
			supptrans.type,
			supptrans.transno,
			supptrans.trandate,
			supptrans.suppreference,
			supptrans.rate,
			(supptrans.ovamount + supptrans.ovgst) AS totalamount,
			supptrans.alloc AS allocated,
			supptrans.hold,
			supptrans.settled,
			supptrans.transtext,
			supptrans.supplierno
		FROM supptrans,
			systypes
		WHERE supptrans.type = systypes.typeid
		AND supptrans.supplierno = '" . $SupplierID . "'
		AND supptrans.trandate >= '" . $DateAfterCriteria . "'
		ORDER BY supptrans.trandate";

$ErrMsg = _('No transactions were returned by the SQL because');
$DbgMsg = _('The SQL that failed was');

$TransResult = DB_query($SQL, $ErrMsg, $DbgMsg);

if (DB_num_rows($TransResult) == 0) {
	echo '<br /><div class="centre">' . _('There are no transactions to display since') . ' ' . $_POST['TransAfterDate'];
	echo '</div>';
	include('includes/footer.inc');
	exit;
}

/*show a table of the transactions returned by the SQL */

echo '<table width="90%" class="selection">
		<thead>
			<tr>
				<th class="SortedColumn">' . _('Date') . '</th>
				<th class="SortedColumn">' . _('Type') . '</th>
				<th class="SortedColumn">' . _('Number') . '</th>
				<th class="SortedColumn">' . _('Reference') . '</th>
				<th class="SortedColumn">' . _('Comments') . '</th>
				<th class="SortedColumn">' . _('Total') . '</th>
				<th class="SortedColumn">' . _('Allocated') . '</th>
				<th class="SortedColumn">' . _('Balance') . '</th>
				<th class="noPrint">' . _('More Info') . '</th>
				<th class="noPrint">' . _('More Info') . '</th>
			</tr>
		</thead>';

$j = 1;
$k = 0; //row colour counter

$AuthSQL = "SELECT offhold
				FROM purchorderauth
				WHERE userid='" . $_SESSION['UserID'] . "'
					AND currabrev='" . $SupplierRecord['currcode'] . "'";

$AuthResult = DB_query($AuthSQL);
$AuthRow = DB_fetch_array($AuthResult);
echo '<tbody>';
while ($MyRow = DB_fetch_array($TransResult)) {

	if ($MyRow['hold'] == 0 and $MyRow['settled'] == 0) {
		$HoldValue = _('Hold');
	} elseif ($MyRow['settled'] == 1) {
		$HoldValue = '';
	} else {
		$HoldValue = _('Release');
	}
	if ($MyRow['hold'] == 1) {
		echo '<tr bgcolor="#DD99BB">';
	} elseif ($k == 1) {
		echo '<tr class="EvenTableRows">';
		$k = 0;
	} else {
		echo '<tr class="OddTableRows">';
		$k = 1;
	}

	$FormatedTranDate = ConvertSQLDate($MyRow['trandate']);

	// All table-row (tag tr) must have 10 table-datacells (tag td).

	$BaseTD8 = '<td>' . ConvertSQLDate($MyRow['trandate']) . '</td>
				<td>' . _($MyRow['typename']) . '</td>
				<td class="number"><a href="' . $RootPath . '/SuppWhereAlloc.php?TransType=' . urlencode($MyRow['type']) . '&TransNo=' . urlencode($MyRow['transno']) . '">' . $MyRow['transno'] . '</a></td>
				<td>' . $MyRow['suppreference'] . '</td>
				<td>' . $MyRow['transtext'] . '</td>
				<td class="number">' . locale_number_format($MyRow['totalamount'],$SupplierRecord['currdecimalplaces']) . '</td>
				<td class="number">' . locale_number_format($MyRow['allocated'],$SupplierRecord['currdecimalplaces']) . '</td>
				<td class="number">' . locale_number_format($MyRow['totalamount']-$MyRow['allocated'],$SupplierRecord['currdecimalplaces']) . '</td>';

	$PaymentTD1 = '<td class="noPrint"><a href="' . $RootPath . '/PaymentAllocations.php?SuppID=%s&amp;InvID=%s" title="' . _('Click to view payments') . '"><img alt="" src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/money_delete.png" width="16"/> ' . _('Payments') . '</a></td>';

/* To do: $HoldValueTD1*/

	$AllocationTD1 = '<td class="noPrint"><a href="' . $RootPath . '/SupplierAllocations.php?AllocTrans=%s" title="' . _('Click to allocate funds') . '"><img alt="" src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/allocation.png" /> ' . _('Allocation') . '</a></td>';

	$GLEntriesTD1 = '<td class="noPrint"><a href="' . $RootPath . '/GLTransInquiry.php?TypeID=%s&amp;TransNo=%s" target="_blank" title="' . _('Click to view the GL entries') . '"><img alt="" src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/gl.png" width="16" /> ' . _('GL Entries') . '</a></td>';

	if ($MyRow['type'] == 20) { /*Show a link to allow GL postings to be viewed but no link to allocate */

		if ($_SESSION['CompanyRecord']['gllink_creditors'] == True) {
			if ($MyRow['totalamount'] - $MyRow['allocated'] == 0) {
				/*The trans is settled so don't show option to hold */
				printf($BaseTD8 . $PaymentTD1 . $GLEntriesTD1 . '</tr>',
					// $PaymentTD1 parameters:
					$MyRow['supplierno'],
					$MyRow['suppreference'],
					// $GLEntriesTD1 parameters:
					$MyRow['type'],
					$MyRow['transno']);

			} else {

				printf($BaseTD8);
 				if ($AuthRow[0] == 0) {
					echo '<td class="noPrint"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?HoldType=' . $MyRow['type'] . '&amp;HoldTrans=' . $MyRow['transno'] . '&amp;HoldStatus=' . $HoldValue . '&amp;FromDate=' . $_POST['TransAfterDate'] . '">' . $HoldValue . '</a></td>';
				} else {
					if ($HoldValue == _('Release')) {
						echo '<td>' . $HoldValue . '</a></td>';
					} else {
						echo '<td class="noPrint"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?' . 'HoldType=' . $MyRow['type'] . '&amp;HoldTrans=' . $MyRow['transno'] . '&amp;HoldStatus=' . $HoldValue . '&amp;FromDate=' . $_POST['TransAfterDate'] . '">' . $HoldValue . '</a></td>';
					}
				}
				printf($GLEntriesTD1 . '</tr>',
					// $GLEntriesTD1 parameters:
					$MyRow['type'],
					$MyRow['transno']);
			}
		} else {

			if ($MyRow['totalamount'] - $MyRow['allocated'] == 0) {
				/*The trans is settled so don't show option to hold */
				echo $BaseTD8, '<td class="noPrint">&nbsp;</td><td class="noPrint">&nbsp;</td></tr>';

			} else {

				echo $BaseTD8 . '
					<td class="noPrint"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,'UTF-8') . '/PaymentAllocations.php?SuppID=' .
						$MyRow['type'] . '&amp;InvID=' . $MyRow['transno'] . '">' . _('View Payments') . '</a></td>
					<td class="noPrint"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES,'UTF-8') . '?HoldType=' . urlencode($_POST['TransAfterDate']) . '&amp;HoldTrans=' . urlencode($HoldValue) . '&amp;HoldStatus=' .
						$RootPath . '&amp;FromDate='. urlencode($MyRow['supplierno']) . '">' . $MyRow['suppreference'] . '</a></td></tr>';
			}
		}

	} else {
		/*its a credit note or a payment */

		if ($_SESSION['CompanyRecord']['gllink_creditors'] == True) {
			printf($BaseTD8 . $AllocationTD1 . $GLEntriesTD1 . '</tr>',
				// $AllocationTD1 parameters:
				$MyRow['id'],
				// $GLEntriesTD1 parameters:
				$MyRow['type'],
				$MyRow['transno']);

		} else {
			/*Not linked to GL */
			printf($BaseTD8 . $AllocationTD1 . '<td class="noPrint">&nbsp;</td></tr>',
				// $AllocationTD1 parameters:
				$MyRow['id']);

		}
	} //end of page full new headings if
} //end of while loop

echo '</tbody>';
echo '</table>';
include('includes/footer.inc');
?>