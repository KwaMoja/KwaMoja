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
	$SQL = "UPDATE prlpayrolltrans SET	othincome=0
				WHERE payrollid ='" . $PayrollID . "'";
	$RePostPT = DB_query($SQL);

	$SQL = "SELECT counterindex,payrollid,employeeid,othincome
			FROM prlpayrolltrans
			WHERE prlpayrolltrans.payrollid='" . $PayrollID . "'";
	$PayDetails = DB_query($SQL);
	if (DB_num_rows($PayDetails) > 0) {
		while ($MyRow = DB_fetch_array($PayDetails)) {
			$SQL = "SELECT sum(othincamount) AS OTHPay
					FROM prlothincfile
			        WHERE prlothincfile.employeeid='" . $MyRow['employeeid'] . "'
					AND prlothincfile.othdate>='$FromPeriod'
					AND  prlothincfile.othdate<='$ToPeriod'
					ORDER BY OthDate";
			$OIDetails = DB_query($SQL);
			if (DB_num_rows($OIDetails) > 0) {
				$oirow = DB_fetch_array($OIDetails);
				$OTHPayment = $oirow['OTHPay'];
				//if ($OTHPayment>0 or $OTPayment<>null) {
				//	$SQL = 'UPDATE prlpayrolltrans SET othincome='.$OTHPayment.'
				//	WHERE counterindex = ' . $MyRow['counterindex'];
				//$PostOTPay = DB_query($SQL);
				//}
			}
		}
	}
}
?>