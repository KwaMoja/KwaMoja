<?php
if (isset($_GET['PayrollID'])) {
	$PayrollID = $_GET['PayrollID'];
} elseif (isset($_POST['PayrollID'])) {
	$PayrollID = $_POST['PayrollID'];
} else {
	unset($PayrollID);
}
$HowFrequent = 1; // 1 -> every payday 2 -> once a month
$FSMonthRow = GetPayrollRow($PayrollID, 5);
$FSYearRow = GetPayrollRow($PayrollID, 6);
$DeductHDMF = GetYesNoStr(GetPayrollRow($PayrollID, 8));
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
	$SQL = "DELETE FROM prlempgrosspayfile WHERE payrollid ='" . $PayrollID . "'";
	$Postdelhdmf = DB_query($SQL);

	$SQL = "UPDATE prlpayrolltrans SET	hdmf=0
				WHERE payrollid ='" . $PayrollID . "'";
	$RePostHDMF = DB_query($SQL);

	if ($DeductHDMF == 'Yes') {
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
					$HDMFDetails = DB_query($SQL);
					if (DB_num_rows($HDMFDetails) > 0) {
						$hdmfrow = DB_fetch_array($HDMFDetails);
						$HDMFGP = $hdmfrow['Gross'];
						if ($HDMFGP > 0 or $HDMFGP <> null) {
							$HFMFER = GetHDMFER($HDMFGP);
							$HFMFEE = GetHDMFEE($HDMFGP);
							$HDMFTOT = $HFMFEE + $HFMFER;
							$SQL = "INSERT INTO prlempgrosspayfile (
												payrollid,
												employeeid,
												grosspay,
												employergrosspay,
												employeegrosspay,
												total,
												fsmonth,
												fsyear)
												VALUES ('$PayrollID',
													'" . $MyRow['employeeid'] . "',
													'$HDMFGP',
													'$HFMFER',
													'$HFMFEE',
													'$HDMFTOT',
													'" . $MyRow['fsmonth'] . "',
													'" . $MyRow['fsyear'] . "'
													)";
							$ErrMsg = _('Inserting Grosspay File failed.');
							$InsSSSRecords = DB_query($SQL, $ErrMsg);
						} //if sssgp>0
					} //dbnumross sssdetials>0
				} //end of while
			} else {
				while ($MyRow = DB_fetch_array($PayDetails)) {
					$HDMFGP = $MyRow['basicpay'];
					if ($HDMFGP > 0 or $HDMFGP <> null) {
						$HFMFER = GetHDMFER($HDMFGP);
						$HFMFEE = GetHDMFEE($HDMFGP);
						$HDMFTOT = $HFMFEE + $HFMFER;
						$SQL = "INSERT INTO prlempgrosspayfile (
												payrollid,
												employeeid,
												grosspay,
												employergrosspay,
												employeegrosspay,
												total,
												fsmonth,
												fsyear)
												VALUES ('$PayrollID',
													'" . $MyRow['employeeid'] . "',
													'$HDMFGP',
													'$HFMFER',
													'$HFMFEE',
													'$HDMFTOT',
													'" . $MyRow['fsmonth'] . "',
													'" . $MyRow['fsyear'] . "'
													)";
						$ErrMsg = _('Inserting HDMF File failed.');
						$InsSSSRecords = DB_query($SQL, $ErrMsg);
					} //if sssgp>0
				} //end of while
			}
		} //dbnumrows paydetails > 0
	} //deduct sss=yes

	//posting to payroll trans for hdmf
	if ($DeductHDMF == 'Yes') {
		$SQL = "SELECT counterindex,payrollid,employeeid,basicpay,absent,late,otpay,fsmonth,fsyear
				FROM prlpayrolltrans
				WHERE prlpayrolltrans.payrollid='" . $PayrollID . "'";
		$PayDetails = DB_query($SQL);
		if (DB_num_rows($PayDetails) > 0) {
			while ($MyRow = DB_fetch_array($PayDetails)) {
				$SQL = "SELECT employeegrosspay
					FROM prlempgrosspayfile
			        WHERE prlempgrosspayfile.employeeid='" . $MyRow['employeeid'] . "'
					AND prlempgrosspayfile.payrollid='" . $PayrollID . "'";
				$HDMFDetails = DB_query($SQL);
				if (DB_num_rows($HDMFDetails) > 0) {
					$hdmfrow = DB_fetch_array($HDMFDetails);
					$HDMFPayment = $hdmfrow['employeegrosspay'];
					$SQL = 'UPDATE prlpayrolltrans SET hdmf=' . $HDMFPayment . '
					     WHERE counterindex = ' . $MyRow['counterindex'];
					$PostHDMFPay = DB_query($SQL);
				}
			}
		}
	}
} //isset post submit
?>