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
	$SQL = "UPDATE prlottrans SET	otamount=0
				WHERE payrollid ='" . $PayrollID . "'";
	$RePostOT = DB_query($SQL);
	$SQL = "UPDATE prlpayrolltrans SET	otpay=0
				WHERE payrollid ='" . $PayrollID . "'";
	$RePostPT = DB_query($SQL);

	$SQL = "SELECT counterindex,payrollid,employeeid,hourlyrate,otpay
			FROM prlpayrolltrans
			WHERE prlpayrolltrans.payrollid='" . $PayrollID . "'";
	$PayDetails = DB_query($SQL);
	if (DB_num_rows($PayDetails) > 0) {
		while ($MyRow = DB_fetch_array($PayDetails)) {
			$SQL = "SELECT counterindex,overtimeid,employeeid,payrollid,othours,otamount
					FROM prlottrans
			        WHERE prlottrans.employeeid='" . $MyRow['employeeid'] . "'
					AND otdate>='$FromPeriod'
					AND  otdate<='$ToPeriod'
					ORDER BY OTDate";
			$OTDetails = DB_query($SQL);
			if (DB_num_rows($OTDetails) > 0) {
				while ($otrow = DB_fetch_array($OTDetails)) {
					if (($otrow['payrollid'] == $PayrollID) or ($otrow['payrollid'] == '')) {

						$SQL = "SELECT overtimerate
								FROM prlovertimetable
								WHERE overtimeid='" . $otrow['overtimeid'] . "'";
						$OTRateResult = DB_query($SQL);
						if (DB_num_rows($OTRateResult) > 0) {
							$otraterow = DB_fetch_array($OTRateResult);
							$OTRate = $otraterow['overtimerate'] * $MyRow['hourlyrate'];
							$SQL = 'UPDATE prlottrans SET payrollid=' . $PayrollID . ', otamount=othours*' . $OTRate . '
									WHERE counterindex = ' . $otrow['counterindex'];
							$PostOT = DB_query($SQL);
						}
					}
				}
			}
		}
	}


	$SQL = "SELECT counterindex,payrollid,employeeid,otpay
			FROM prlpayrolltrans
			WHERE prlpayrolltrans.payrollid='" . $PayrollID . "'";
	$PayDetails = DB_query($SQL);
	if (DB_num_rows($PayDetails) > 0) {

		while ($MyRow = DB_fetch_array($PayDetails)) {
			$SQL = "SELECT sum(otamount) AS otpay
				FROM prlottrans
				WHERE prlottrans.employeeid='" . $MyRow['employeeid'] . "'
				AND payrollid='" . $MyRow['payrollid'] . "'
				ORDER BY OTDate";
			$OTDetails = DB_query($SQL);

			if (DB_num_rows($OTDetails) > 0) {
				$otrow = DB_fetch_array($OTDetails);
				$OTPayment = $otrow['otpay'];
				if ($OTPayment > 0 or $OTPayment <> null) {
					$SQL = 'UPDATE prlpayrolltrans SET otpay=' . $OTPayment . '
								WHERE counterindex = ' . $MyRow['counterindex'];
					$PostOTPay = DB_query($SQL);
				}
			}
		}
	}

}
?>