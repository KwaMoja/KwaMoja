<?php
if (isset($_GET['PayrollID'])) {
	$PayrollID = $_GET['PayrollID'];
} elseif (isset($_POST['PayrollID'])) {
	$PayrollID = $_POST['PayrollID'];
} else {
	unset($PayrollID);
}

if (isset($_POST['submit'])) {
	prnMsg(_('Contact Administrator...'), 'error');
	include('includes/footer.inc');
	exit;
} else {
	$FromPeriod = GetPayrollRow($PayrollID, 3);
	$ToPeriod = GetPayrollRow($PayrollID, 4);
	$SQL = "UPDATE prlpayrolltrans SET	basicpay=0
				WHERE prlpayrolltrans.payrollid ='" . $PayrollID . "'";
	$RePostBPay = DB_query($SQL);
	$SQL = "UPDATE prldailytrans SET	regamt=0
				WHERE prldailytrans.payrollid ='" . $PayrollID . "'";
	$RePostRA = DB_query($SQL);

	$SQL = "SELECT counterindex,payrollid,employeeid,periodrate,hourlyrate
			FROM prlpayrolltrans
			WHERE prlpayrolltrans.payrollid='" . $PayrollID . "'";
	$PayDetails = DB_query($SQL);
	if (DB_num_rows($PayDetails) > 0) {
		while ($MyRow = DB_fetch_array($PayDetails)) {
			$SQL = "SELECT counterindex,payrollid,employeeid,reghrs,regamt
					FROM prldailytrans
			        WHERE prldailytrans.employeeid='" . $MyRow['employeeid'] . "'
					AND rtdate>='$FromPeriod'
					AND  rtdate<='$ToPeriod'
					ORDER BY RTDate";
			$RTDetails = DB_query($SQL);
			if (DB_num_rows($RTDetails) > 0) {
				while ($rtrow = DB_fetch_array($RTDetails)) {
					if (($rtrow['payrollid'] == $PayrollID) or ($rtrow['payrollid'] == '')) {
						$PayType = GetPayTypeDesc(GetEmpRow($rtrow['employeeid'], 29));
						if ($PayType == 'Hourly') {
							$HRRate = $MyRow['hourlyrate'];
							$SQL = 'UPDATE prldailytrans SET payrollid=' . $PayrollID . ', regamt=reghrs*' . $HRRate . '
											WHERE counterindex = ' . $rtrow['counterindex'];
							$PostBPay = DB_query($SQL);
						}
					}
				}
			}
		}
	}

	$SQL = "SELECT counterindex,payrollid,employeeid,periodrate
			FROM prlpayrolltrans
			WHERE prlpayrolltrans.payrollid='" . $PayrollID . "'";
	$PayDetails = DB_query($SQL);
	if (DB_num_rows($PayDetails) > 0) {
		while ($MyRow = DB_fetch_array($PayDetails)) {
			$PayType = GetPayTypeDesc(GetEmpRow($MyRow['employeeid'], 29));
			if ($PayType == 'Hourly') {
				$SQL = "SELECT sum(regamt) AS BasicPay
					FROM prldailytrans
					WHERE prldailytrans.employeeid='" . $MyRow['employeeid'] . "'
					AND payrollid='" . $MyRow['payrollid'] . "'
					ORDER BY RTDate";
				$RTDetails = DB_query($SQL);
				if (DB_num_rows($RTDetails) > 0) {
					$bprow = DB_fetch_array($RTDetails);
					$RTPayment = $bprow['BasicPay'];
					if ($RTPayment > 0) {
						$SQL = 'UPDATE prlpayrolltrans SET basicpay=' . $RTPayment . '
								WHERE counterindex = ' . $MyRow['counterindex'];
						$PostRTPay = DB_query($SQL);
					}
				}
			} elseif ($PayType == 'Salary') {
				$RTPayment = $MyRow['periodrate'];
				$SQL = 'UPDATE prlpayrolltrans SET basicpay=' . $RTPayment . '
								WHERE counterindex = ' . $MyRow['counterindex'];
				$PostRTPay = DB_query($SQL);
			}
		}
	}
}
?>