<?php
/* $Revision: 1.0 $ */

include('includes/session.inc');

$Title = _('Employee Loan Repayments');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/money_add.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['Process'])) {
	$PayrollControlAccount = $_SESSION['CompanyRecord']['payrollact'];
	$PeriodNumber = GetPeriod($_POST['PayrollDate']);

	$SQL = "SELECT numberofpayday, dayofpay FROM prlpayperiod WHERE payperiodid='" . $_POST['PayPeriodID'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	switch($MyRow['numberofpayday']) {
		case 12:
			$NextDeductionDate= DateAdd($_POST['PayrollDate'], 'm', 1);
			break;
		case 52:
			$NextDeductionDate= DateAdd($_POST['PayrollDate'], 'w', 1);
			break;
		default:
			$NextDeductionDate= DateAdd($_POST['PayrollDate'], 'm', 1);
			break;
	}

	foreach ($_POST as $Key=>$Value) {
		if (substr($Key, 0, 7) == 'Payment') {
			$Index = substr($Key, 7);
			$Amount = $Value;
			$SQL = "SELECT loanfileid,
							loanfiledesc
							employeeid,
							loanbalance,
							accountcode,
							tagref
						FROM prlloanfile
						WHERE counterindex='" . $Index . "'";
			$Result = DB_query($SQL);
			$LoanRow = DB_fetch_array($Result);

			$TransactionNumber = GetNextTransNo(61);

			DB_Txn_Begin();
			$DebitSql = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount,
											tag
										) VALUES (
											61,
											'" . $TransactionNumber . "',
											'" . FormatDateForSQL($_POST['PayrollDate']) . "',
											'" . $PeriodNumber . "',
											'" . $PayrollControlAccount . "',
											'" . _('Repayment of loan') . ' ' . $LoanRow['loanfileid'] . ' ' . _('Employee') . ' ' . $LoanRow['employeeid'] . "',
											'" . $Amount . "',
											'" . $LoanRow['tagref'] . "'
										)";
			$CreditSql = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount,
												tag
											) VALUES (
												61,
												'" . $TransactionNumber . "',
												'" . FormatDateForSQL($_POST['PayrollDate']) . "',
												'" . $PeriodNumber . "',
												'" . $LoanRow['accountcode'] . "',
												'" . _('Repayment of loan') . ' ' . $LoanRow['loanfileid'] . ' ' . _('Employee') . ' ' . $LoanRow['employeeid'] . "',
												'" . (-$Amount) . "',
												'" . $LoanRow['tagref'] . "'
											)";
			$ErrMsg = _('Cannot insert a GL entry for the loan repayment because');
			$DbgMsg = _('The SQL that failed to insert the GL Trans record was');
			$Result = DB_query($DebitSql, $ErrMsg, $DbgMsg, true);
			$Result = DB_query($CreditSql, $ErrMsg, $DbgMsg, true);

			$UpdateLoanFileSql = "UPDATE prlloanfile SET nextdeduction='" . $NextDeductionDate . "',
														loanbalance=" . ($LoanRow['loanbalance'] - $Amount) . "
													WHERE counterindex='" . $Index . "'";
			$ErrMsg = _('Cannot update the loan repayment file because');
			$DbgMsg = _('The SQL that failed to update the loan repayment file record was');
			$Result = DB_query($UpdateLoanFileSql, $ErrMsg, $DbgMsg, true);

			DB_Txn_Commit();

			if (DB_error_no() == 0) {
				prnMsg(_('The loan repayments have been correctly posted'), 'success');
			} else {
				prnMsg(_('The loan repayments could not be posted'), 'error');
			}
		}
	}
	include('includes/footer.inc');
	exit;
}

if (!isset($_POST['PayPeriodID'])) {
	echo '<form method="post" class="noPrint" id="LoanDeductionForm" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>';
	echo '<tr>
			<td>' . _('Payroll Date') . '</td>
			<td><input type="text" size="10" maxlength="10" required="required" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="PayrollDate" value="' . date($_SESSION['DefaultDateFormat']) . '" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('Pay Period') . ':</td>
			<td><select name="PayPeriodID" required="required">';
	$SQL = 'SELECT payperiodid, payperioddesc FROM prlpayperiod';
	$Result = DB_query($SQL);
	echo '<option value=""></option>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="' . $MyRow['payperiodid'] . '">' . $MyRow['payperioddesc'] . '</option>';
	} //end while loop
	echo '</select>
			</td>
		</tr>';
	echo '</table>';
	echo '<div class="centre">
			<input type="submit" name="SelectPayPeriod" value="Select Pay Period" />
		</div>';
	echo '</form>';
} else {
	$SQL = "SELECT prlloanfile.counterindex,
					prlloanfile.loanfileid,
					prlloanfile.loanfiledesc,
					prlemployeemaster.firstname,
					prlemployeemaster.middlename,
					prlemployeemaster.lastname,
					prlloanfile.amortization,
					prlloanfile.loanbalance
				FROM prlloanfile
				INNER JOIN prlemployeemaster
					ON prlloanfile.employeeid=prlemployeemaster.employeeid
				WHERE prlemployeemaster.payperiodid='" . $_POST['PayPeriodID'] . "'
					AND prlloanfile.nextdeduction<='" . FormatDateForSQL($_POST['PayrollDate']) . "'
					AND prlloanfile.loanbalance>0";
	$Result = DB_query($SQL);

	echo '<form method="post" class="noPrint" id="LoanDeductionForm" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="PayPeriodID" value="' . $_POST['PayPeriodID'] . '" />';
	echo '<input type="hidden" name="PayrollDate" value="' . $_POST['PayrollDate'] . '" />';
	if (DB_num_rows($Result) > 0) {
		echo '<table class="selection">
				<tr>
					<th>' . _('Loan ID') . '</th>
					<th>' . _('Employee') . '</th>
					<th>' . _('Loan Description') . '</th>
					<th>' . _('Loan Balance') . '</th>
					<th>' . _('Repayment') . '</th>
				</tr>';
		while ($MyRow = DB_fetch_array($Result)) {
			if ($MyRow['amortization'] > $MyRow['loanbalance']) {
				$MyRow['amortization'] = $MyRow['loanbalance'];
			}
			echo '<tr>
					<td>' . $MyRow['loanfileid'] . '</td>
					<td>' . $MyRow['firstname'] . ' ' . $MyRow['middlename'] . ' ' . $MyRow['lastname'] . '</td>
					<td>' . $MyRow['loanfiledesc'] . '</td>
					<td class="number">' . locale_number_format($MyRow['loanbalance'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td class="number"><input type="text" size="10" class="number" name="Payment' . $MyRow['counterindex'] . '" value="' . locale_number_format($MyRow['amortization'], $_SESSION['CompanyRecord']['decimalplaces']) . '" /></td>
				</tr>';
		}
		echo '</table>';
		echo '<div class="centre">
				<input type="submit" name="Process" value="' . _('Process Payments') . '" />
			</div>';
		echo '</form>';
	}
}

include('includes/footer.inc');

?>