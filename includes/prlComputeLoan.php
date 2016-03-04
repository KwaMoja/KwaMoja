<?php
if (isset($_GET['PayrollID'])) {
	$PayrollID = $_GET['PayrollID'];
} elseif (isset($_POST['PayrollID'])) {
	$PayrollID = $_POST['PayrollID'];
} else {
	unset($PayrollID);
}

$Status = GetOpenCloseStr(GetPayrollRow($PayrollID, 11));
if ($Status == 'Closed') {
	prnMsg(_('Payroll is Closed. Re-open first...'), 'warn');
	include('includes/footer.inc');
	exit;
}
if (isset($_POST['submit'])) {
	prnMsg(_('Contact Administrator...'), 'error');
	include('includes/footer.inc');
	exit;
} else {
	$FromPeriod = GetPayrollRow($PayrollID, 3);
	$ToPeriod = GetPayrollRow($PayrollID, 4);

	//update/delete any previous posting for the same payrollid
	$SQL = "SELECT counterindex,payrollid,refid,employeeid,amount
	        FROM prlloandeduction
			WHERE prlloandeduction.payrollid='" . $PayrollID . "'";
	$OthDetails = DB_query($SQL);
	if (DB_num_rows($OthDetails) > 0) {
		while ($MyRow = DB_fetch_array($OthDetails)) {
			$SQL = "SELECT counterindex,loanfileid,employeeid,amortization,loanbalance
					FROM prlloanfile
			        WHERE prlloanfile.counterindex='" . $MyRow['refid'] . "'
					ORDER BY counterindex";
			$IncDetails = DB_query($SQL);
			if (DB_num_rows($IncDetails) > 0) {
				while ($incrow = DB_fetch_array($IncDetails)) {
					$LoanAmount = $MyRow['amount'];
					if ($LoanAmount > 0 or $LoanAmount <> null) {
						$SQL = 'UPDATE prlloanfile SET ytddeduction=ytddeduction-' . $LoanAmount . ', loanbalance=loanbalance+' . $LoanAmount . '
								WHERE counterindex = ' . $incrow['counterindex'];
						$PostLoanAmount = DB_query($SQL);
					}
				}
			}
		}

		$SQL = "DELETE FROM prlloandeduction WHERE payrollid ='" . $PayrollID . "'";
		$Postdelloan = DB_query($SQL);
	}




	$SQL = "SELECT counterindex,payrollid,employeeid,loandeduction
			FROM prlpayrolltrans
			WHERE prlpayrolltrans.payrollid='" . $PayrollID . "'";
	$PayDetails = DB_query($SQL);
	if (DB_num_rows($PayDetails) > 0) {
		while ($MyRow = DB_fetch_array($PayDetails)) {
			$SQL = "SELECT counterindex,loanfileid,loantableid,employeeid,amortization,nextdeduction,loanbalance
					FROM prlloanfile
			        WHERE prlloanfile.employeeid='" . $MyRow['employeeid'] . "'
					AND nextdeduction<='$ToPeriod'
					ORDER BY counterindex";
			$LoanDetails = DB_query($SQL);
			if (DB_num_rows($LoanDetails) > 0) {
				while ($loanrow = DB_fetch_array($LoanDetails)) {
					if ($loanrow['loanbalance'] > 0) {
						if ($loanrow['loanbalance'] <= $loanrow['amortization']) {
							$SQL = "INSERT INTO prlloandeduction(employeeid,refid,loantableid,amount)
										SELECT prlloanfile.employeeid,prlloanfile.counterindex,prlloanfile.loantableid,prlloanfile.loanbalance						FROM prlloanfile
						WHERE prlloanfile.counterindex='" . $loanrow['counterindex'] . "'";
							$ErrMsg = _('Inserting Loan File failed.');
							$InsLoanRecords = DB_query($SQL, $ErrMsg);

							//adjust balance
							$LoanAmount = $loanrow['loanbalance'];
							$SQL = 'UPDATE prlloanfile SET ytddeduction=ytddeduction+' . $LoanAmount . ', loanbalance=loanbalance-' . $LoanAmount . '
										WHERE counterindex = ' . $loanrow['counterindex'];
							$UpdateLoanAmount = DB_query($SQL);

						} else {

							$SQL = "INSERT INTO prlloandeduction(employeeid,refid,loantableid,amount)
										SELECT employeeid,counterindex,loantableid,amortization
										FROM prlloanfile
										WHERE prlloanfile.counterindex='" . $loanrow['counterindex'] . "'";
							$ErrMsg = _('Inserting Loan File failed.');
							$InsLoanRecords = DB_query($SQL, $ErrMsg);

							//adjust balance
							$LoanAmount = $loanrow['amortization'];
							$SQL = 'UPDATE prlloanfile SET ytddeduction=ytddeduction+' . $LoanAmount . ', loanbalance=loanbalance-' . $LoanAmount . '
										WHERE counterindex = ' . $loanrow['counterindex'];
							$UpdateLoanAmount = DB_query($SQL);

						}
					}
					$SQL = "UPDATE prlloandeduction SET payrollid='" . $PayrollID . "'
											WHERE prlloandeduction.payrollid = ''
											AND prlloandeduction.loantableid='" . $loanrow['loantableid'] . "'
											AND prlloandeduction.employeeid='" . $MyRow['employeeid'] . "'";
					$PostLOAN = DB_query($SQL);
				}
			}

		}
	}


	$SQL = "SELECT counterindex,payrollid,employeeid,loandeduction
			FROM prlpayrolltrans
			WHERE prlpayrolltrans.payrollid='" . $PayrollID . "'";
	$PayDetails = DB_query($SQL);
	if (DB_num_rows($PayDetails) > 0) {
		while ($MyRow = DB_fetch_array($PayDetails)) {
			$SQL = "SELECT sum(amount) AS loanded
				FROM prlloandeduction
				WHERE prlloandeduction.employeeid='" . $MyRow['employeeid'] . "'
				AND payrollid='" . $MyRow['payrollid'] . "'";
			$LoanDetails = DB_query($SQL);
			if (DB_num_rows($LoanDetails) > 0) {
				//$otrow=DB_fetch_array($OTDetails);
				//$OTPayment=$otrow['otpay'];
				//$SQL = 'UPDATE prlpayrolltrans SET otpay='.$OTPayment.'
				//			WHERE counterindex = ' . $MyRow['counterindex'];
				//$PostOTPay = DB_query($SQL);

				$loanrow = DB_fetch_array($LoanDetails);
				$LoanPayment = $loanrow['loanded'];
				if ($LoanPayment > 0 or $LoanPayment <> null) {
					$SQL = 'UPDATE prlpayrolltrans SET loandeduction=' . $LoanPayment . '
					     WHERE counterindex = ' . $MyRow['counterindex'];
					$PostLoanPay = DB_query($SQL);
				}
			}
		}
	} else {
		//echo "No Loan Deduction..";
	}

}
?>