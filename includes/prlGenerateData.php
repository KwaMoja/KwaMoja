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
	prnMsg( _('Payroll is Closed. Re-open first...'), 'info');
	include('includes/footer.inc');
	exit;
}
if (isset($_POST['submit'])) {
	prnMsg( _('Contact Administrator...'), 'error');
	include('includes/footer.inc');
	exit;
} else {
	$SQL = "DELETE FROM prlpayrolltrans WHERE payrollid ='" . $PayrollID . "'";
	$Postdelptrans = DB_query($SQL);
	$PayPeriodID = GetPayrollRow($PayrollID, 2);
	$FSMonthRow = GetPayrollRow($PayrollID, 5);
	$FSYearRow = GetPayrollRow($PayrollID, 6);
	$SQL = "SELECT employeeid,
					periodrate,
					hourlyrate
				FROM prlemployeemaster
				WHERE payperiodid = '" . $PayPeriodID . "'
					AND active=0";

	$ChartDetailsNotSetUpResult = DB_query($SQL, _('Could not test to see that all detail records properly initiated'));
	if (DB_num_rows($ChartDetailsNotSetUpResult) > 0) {
		$SQL = "INSERT INTO prlpayrolltrans (employeeid,
											periodrate,
											hourlyrate)
										SELECT employeeid,
												periodrate,
												hourlyrate
											FROM prlemployeemaster
											WHERE prlemployeemaster.payperiodid = '" . $PayPeriodID . "'
												AND prlemployeemaster.active=0";
		$ErrMsg = _('Inserting new chart details records required failed because');
		$InsChartDetailsRecords = DB_query($SQL, $ErrMsg);
		$SQL = "UPDATE prlpayrolltrans SET payrollid='" . $PayrollID . "'
							WHERE payrollid = ''";
		$PostPrd = DB_query($SQL);

		$SQL = "UPDATE prlpayrolltrans SET fsmonth=$FSMonthRow,
											fsyear=$FSYearRow
										WHERE prlpayrolltrans.payrollid='" . $PayrollID . "'";
		$PostFSPeriod = DB_query($SQL);
	} else {
		prnMsg( _('No Employees Records Match....'), 'info');
		include('includes/footer.inc');
		exit;
	}

}

?>