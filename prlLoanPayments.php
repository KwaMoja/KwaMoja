<?php
/* $Revision: 1.0 $ */

include('includes/session.inc');

$Status = array();
$Status[0] = _('Pending Authorisation');
$Status[1] = _('Authorised');
$Status[2] = _('Posted');
$Status[3] = _('Cancelled');
$Status[4] = _('Rejected');
$Status[5] = _('Written Off');

$Title = _('Issue Employee Loans');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_POST['update'])) {
	foreach ($_POST as $key=>$value) {
		if (mb_substr($key, 0, 4) == 'Post') {
			$Loan = mb_substr($key, 4);
			$LoanSQL = "SELECT employeeid,
								loanfileid,
								loanamount,
								accountcode,
								bankaccount,
								tagref
							FROM prlloanfile
							WHERE counterindex='" . $Loan . "'";
			$LoanResult = DB_query($LoanSQL);
			$LoanRow = DB_fetch_array($LoanResult);

			/* Get the next transaction number */
			$TransNo = GetNextTransNo(60);
			$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']));

			$result = DB_Txn_Begin();

			/* Then do the bank transaction */
			$BankTransactionSQL = "INSERT INTO banktrans (transno,
														type,
														bankact,
														ref,
														chequeno,
														exrate,
														functionalexrate,
														transdate,
														banktranstype,
														amount,
														currcode
													) VALUES (
														'" . $TransNo . "',
														60,
														'" . $LoanRow['bankaccount'] . "',
														'" . _('Payment of loan to employee number') . ' ' . $LoanRow['employeeid'] . "',
														'" . $_POST['Cheque' . $Loan] . "',
														'1',
														'1',
														CURRENT_DATE,
														'" . $_POST['PaymentType' . $Loan] . "',
														'" . $LoanRow['loanamount'] . "',
														'" . $_SESSION['CompanyRecord']['currencydefault'] . "'
													)";
			$ErrMsg = _('Cannot insert a bank transaction because');
			$DbgMsg = _('Cannot insert a bank transaction with the SQL');
			$result = DB_query($BankTransactionSQL, $ErrMsg, $DbgMsg, true);
			if (DB_error_no($result) == 0) {
				prnMsg( _('The bank transaction has been successfully entered'), 'success');
			}

			/* Then credit the bank account */
			$BankGLSQL = "INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										narrative,
										amount
									) VALUES (
										60,
										'" . $TransNo . "',
										CURRENT_DATE,
										'" . $PeriodNo . "',
										'" . $LoanRow['bankaccount'] . "',
										'" . _('Payment of loan to employee number') . ' ' . $LoanRow['employeeid'] . "',
										" . -($LoanRow['loanamount']) . "
									)";
			$ErrMsg = _('Cannot insert a GL transaction for the bank account credit because');
			$DbgMsg = _('Cannot insert a GL transaction for the bank account credit using the SQL');
			$result = DB_query($BankGLSQL, $ErrMsg, $DbgMsg, true);
			if (DB_error_no($result) == 0) {
				prnMsg( _('The GL credit has been successfully entered'), 'success');
			}

			/* then debit the loan account */
			$LoanGLSQL = "INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										narrative,
										amount
									) VALUES (
										60,
										'" . $TransNo . "',
										CURRENT_DATE,
										'" . $PeriodNo . "',
										'" . $LoanRow['accountcode'] . "',
										'" . _('Payment of loan to employee number') . ' ' . $LoanRow['employeeid'] . "',
										'" . $LoanRow['loanamount'] . "'
									)";
			$ErrMsg = _('Cannot insert a GL transaction for the bank account credit because');
			$DbgMsg = _('Cannot insert a GL transaction for the bank account credit using the SQL');
			$result = DB_query($LoanGLSQL, $ErrMsg, $DbgMsg, true);
			if (DB_error_no($result) == 0) {
				prnMsg( _('The GL debit has been successfully entered'), 'success');
			}

			/* and finally update the loan status */
			$UpdateLoanSQL = "UPDATE prlloanfile SET status=2 WHERE counterindex='" . $Loan . "'";
			$ErrMsg = _('Cannot update the loan status because');
			$DbgMsg = _('Cannot update the loan status using the SQL');
			$result = DB_query($UpdateLoanSQL, $ErrMsg, $DbgMsg, true);
			if (DB_error_no($result) == 0) {
				prnMsg( _('The loan has been updated'), 'success');
			}

			$result = DB_Txn_Commit();
		}
	}
}

echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/loan.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';
echo '<form method="post" class="noPrint" id="LoanDeductionForm" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

$SQL = "SELECT prlloanfile.counterindex,
				prlloanfile.loanfileid,
				prlloanfile.loanfiledesc,
				prlloanfile.employeeid,
				prlemployeemaster.firstname,
				prlemployeemaster.middlename,
				prlemployeemaster.lastname,
				prlloanfile.loandate,
				prlloantable.loantabledesc,
				departments.description,
				prlloanfile.loanamount,
				prlloanfile.amortization,
				prlloanfile.nextdeduction,
				prlloanfile.loanbalance,
				prlloanfile.status,
				www_users.realname
			FROM prlloanfile
			INNER JOIN prlemployeemaster
				ON prlloanfile.employeeid=prlemployeemaster.employeeid
			INNER JOIN departments
				ON prlemployeemaster.departmentid=departments.departmentid
			INNER JOIN prlloantable
				ON prlloantable.loantableid=prlloanfile.loantableid
			INNER JOIN www_users
				ON www_users.userid=prlloanfile.authoriser
			INNER JOIN bankaccountusers
				ON prlloanfile.bankaccount=bankaccountusers.accountcode
			WHERE prlloanfile.loanbalance>0
				AND status=1
				AND prlloanfile.authoriser='" . $_SESSION['UserID'] . "'
				AND bankaccountusers.userid = '" . $_SESSION['UserID'] . "'";
$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0) {
	echo '<table class="selection">
			<tr>
				<th>' . _('Description') . '</th>
				<th>' . _('Employee ID') . '</th>
				<th>' . _('Employee Name') . '</th>
				<th>' . _('Department') . '</th>
				<th>' . _('Loan Type') . '</th>
				<th>' . _('Loan Date') . '</th>
				<th>' . _('Loan Amount') . '</th>
				<th>' . _('Status') . '</th>
				<th>' . _('Payment Type') . '</th>
				<th>' . _('Cheque/Voucher') . '</th>
				<th>' . _('Make Payment') . '</th>
			</tr>';

	while ($LoanRow = DB_fetch_array($Result)) {
		echo '<tr>
				<td>' . $LoanRow['loanfiledesc'] . '</td>
				<td>' . $LoanRow['employeeid'] . '</td>
				<td>' . $LoanRow['firstname'] . ' ' . $LoanRow['middlename'] . ' ' . $LoanRow['lastname'] . '</td>
				<td>' . $LoanRow['description'] . '</td>
				<td>' . $LoanRow['loantabledesc'] . '</td>
				<td>' . ConvertSQLDate($LoanRow['loandate']) . '</td>
				<td class="number">' . locale_number_format($LoanRow['loanamount'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td>' . $Status[$LoanRow['status']] . '</td>
				<td><select minlength="0" name="PaymentType' . $LoanRow['counterindex'] . '">';
		include('includes/GetPaymentMethods.php');
		/* The array Payttypes is set up in includes/GetPaymentMethods.php
		payment methods can be modified from the setup tab of the main menu under payment methods*/

		foreach ($PaytTypes as $PaytID => $PaytType) {
			echo '<option value="' . $PaytID . '">' . $PaytType . '</option>';
		} //end foreach
		echo '</select>
				</td>
				<td><input type="text" size="6" name ="Cheque' . $LoanRow['counterindex'] . '" value="" /></td>
				<td><input type="checkbox" name ="Post' . $LoanRow['counterindex'] . '" value="" /></td>
			</tr>';
	}
	echo '</table>';
	echo '<div class="centre">
			<input type="submit" name="update" value="' . _('Make Loan Payments') . '" />
		</div>';
} else {
	prnMsg( _('There are no loans waiting to be issued.'), 'info');
}

include('includes/footer.inc');

?>