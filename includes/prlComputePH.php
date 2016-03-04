<?php
if (isset($_GET['PayrollID'])) {
	$PayrollID = $_GET['PayrollID'];
} elseif (isset($_POST['PayrollID'])) {
	$PayrollID = $_POST['PayrollID'];
} else {
	unset($PayrollID);
}
$HowFrequent = 1; //1 -> every payday 2 -> once a month
$FSMonthRow = GetPayrollRow($PayrollID, 5);
$FSYearRow = GetPayrollRow($PayrollID, 6);
$DeductPH = GetYesNoStr(GetPayrollRow($PayrollID, 9));
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
	$SQL = "DELETE FROM prlempbasicpayfile WHERE payrollid ='" . $PayrollID . "'";
	$Postdelph = DB_query($SQL);

	$SQL = "UPDATE prlpayrolltrans SET	philhealth=0
				WHERE payrollid ='" . $PayrollID . "'";
	$RePostPH = DB_query($SQL);

	if ($DeductPH == 'Yes') {
		$SQL = "SELECT counterindex,payrollid,employeeid,basicpay,othincome,absent,late,otpay,grosspay,fsmonth,fsyear
				FROM prlpayrolltrans
				WHERE prlpayrolltrans.payrollid='" . $PayrollID . "'";
		$PayDetails = DB_query($SQL);
		if (DB_num_rows($PayDetails) > 0) {
			if ($HowFrequent == 2) {
				while ($MyRow = DB_fetch_array($PayDetails)) {
					$SQL = "SELECT sum(basicpay) AS Gross
					FROM prlpayrolltrans
					WHERE prlpayrolltrans.employeeid='" . $MyRow['employeeid'] . "'
					AND prlpayrolltrans.fsmonth='" . $FSMonthRow . "'
					AND prlpayrolltrans.fsyear='" . $FSYearRow . "'";
					$PHDetails = DB_query($SQL);
					if (DB_num_rows($PHDetails) > 0) {
						$phrow = DB_fetch_array($PHDetails);
						$PHGP = $phrow['Gross'];
						if ($PHGP > 30000) {
							$PHGP = 30000;
						}
						if ($PHGP > 0 or $PHGP <> null) {
							$MyPhRow = GetPHRow($PHGP);
							$SQL = "INSERT INTO prlempbasicpayfile (
												payrollid,
												employeeid,
												grosspay,
												rangefrom,
												salarycredit,
												rangeto,
												employerbasicpay,
												employerec,
												employeebasicpay,
												total,
												fsmonth,
												fsyear)
												VALUES ('$PayrollID',
													'" . $MyRow['employeeid'] . "',
													'$PHGP',
													'" . $MyPhRow['rangefrom'] . "',
													'" . $MyPhRow['rangeto'] . "',
													'" . $MyPhRow['salarycredit'] . "',
													'" . $MyPhRow['employerbasicpay'] . "',
													'" . $MyPhRow['employerec'] . "',
													'" . $MyPhRow['employeebasicpay'] . "',
													'" . $MyPhRow['total'] . "',
													'" . $MyRow['fsmonth'] . "',
													'" . $MyRow['fsyear'] . "'
													)";
							$ErrMsg = _('Inserting Basic Pay File failed.');
							$InsPHRecords = DB_query($SQL, $ErrMsg);
						} //if sssgp>0
					} //dbnumross sssdetials>0
				} //end of while
			} else {
				while ($MyRow = DB_fetch_array($PayDetails)) {
					$PHGP = $MyRow['basicpay'];
					if ($PHGP > 15000) {
						$PHGP = 15000;
					}
					if ($PHGP > 0 or $PHGP <> null) {
						$MyPhRow = GetPHRow($PHGP);
						$SQL = "INSERT INTO prlempbasicpayfile (
												payrollid,
												employeeid,
												grosspay,
												rangefrom,
												rangeto,
												salarycredit,
												employerbasicpay,
												employerec,
												employeebasicpay,
												total,
												fsmonth,
												fsyear)
												VALUES ('$PayrollID',
													'" . $MyRow['employeeid'] . "',
													'$PHGP',
													'" . $MyPhRow['rangefrom'] . "',
													'" . $MyPhRow['rangeto'] . "',
													'" . isset($MyPhRow['salarycredit']) . "',
													'" . $MyPhRow['employerbasicpay'] . "',
													'" . $MyPhRow['employerec'] . "',
													'" . $MyPhRow['employeebasicpay'] . "',
													'" . $MyPhRow['total'] . "',
													'" . $MyRow['fsmonth'] . "',
													'" . $MyRow['fsyear'] . "'
													)";
						$ErrMsg = _('Inserting Basic Pay File failed.');
						$InsPHRecords = DB_query($SQL, $ErrMsg);
					} //if sssgp>0
				} //end of while
			} //end of if ($HowFrequent==2) {
		} //dbnumrows paydetails > 0
	} //deduct sss=yes

	//posting to payroll trans for sss
	if ($DeductPH == 'Yes') {
		$SQL = "SELECT counterindex,payrollid,employeeid,otpay,fsmonth,fsyear
				FROM prlpayrolltrans
				WHERE prlpayrolltrans.payrollid='" . $PayrollID . "'";
		$PayDetails = DB_query($SQL);
		if (DB_num_rows($PayDetails) > 0) {
			while ($MyRow = DB_fetch_array($PayDetails)) {
				$SQL = "SELECT employeebasicpay
					FROM prlempbasicpayfile
			        WHERE prlempbasicpayfile.employeeid='" . $MyRow['employeeid'] . "'
					AND prlempbasicpayfile.payrollid='" . $PayrollID . "'";
				$PHDetails = DB_query($SQL);
				if (DB_num_rows($PHDetails) > 0) {
					$phrow = DB_fetch_array($PHDetails);
					$PHPayment = $phrow['employeebasicpay'];
					$SQL = 'UPDATE prlpayrolltrans SET philhealth=' . $PHPayment . '
					     WHERE counterindex = ' . $MyRow['counterindex'];
					$PostPHPay = DB_query($SQL);
				}
			}
		}
	}
} //isset post submit
?>