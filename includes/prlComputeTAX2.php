<?php
if (isset($_GET['PayrollID'])) {
	$PayrollID = $_GET['PayrollID'];
} elseif (isset($_POST['PayrollID'])) {
	$PayrollID = $_POST['PayrollID'];
} else {
	unset($PayrollID);
}
$SQL = "DELETE FROM prlemptaxfile WHERE payrollid ='" . $PayrollID . "'";
$Postdeltax = DB_query($SQL);

$SQL = "UPDATE prlpayrolltrans SET	tax=0
		WHERE payrollid ='" . $PayrollID . "'";
$RePostTAX = DB_query($SQL);

$FSMonthRow = GetPayrollRow($PayrollID, 5);
$FSYearRow = GetPayrollRow($PayrollID, 6);
$FSPPID = GetPayrollRow($PayrollID, 2);
$NumberofPayday = GetPayPeriodRow(GetPayrollRow($PayrollID, 2), 2);
if (isset($_POST['submit'])) {
	prnMsg(_('Contact Administrator...'), 'error');
	include('includes/footer.inc');
	exit;
} else {
	//to determent number of payday this month
	if ($NumberofPayday >= 12) { //payroll for monthly to daily based on frequency of payday
		$SQL = "SELECT payrollid
			FROM prlpayrollperiod
			WHERE prlpayrollperiod.payperiodid='" . $FSPPID . "'
			AND prlpayrollperiod.payclosed='1'
			AND prlpayrollperiod.fsmonth='" . $FSMonthRow . "'
			AND prlpayrollperiod.fsyear='" . $FSYearRow . "'";
		$PayPeriodRows = DB_query($SQL);
		$NumPaydaythisMos = DB_num_rows($PayPeriodRows) + 1; //closed payroll + current payroll
		$NumPaydayPerMos = $NumberofPayday / 12;
		$UnPaidPDthisMos = $NumPaydayPerMos - $NumPaydaythisMos;
		//$UnPaidPDthisYR=$UnPaidPDthisMos+((12-$FSMonthRow)*$NumPaydayPerMos);

		//list of employesse
		$SQL = "SELECT counterindex,payrollid,employeeid,othincome,grosspay,sss,hdmf,philhealth,fsmonth,fsyear
				FROM prlpayrolltrans
				WHERE prlpayrolltrans.payrollid='" . $PayrollID . "'";
		$PayDetails = DB_query($SQL);
		if (DB_num_rows($PayDetails) > 0) {
			while ($MyRow = DB_fetch_array($PayDetails)) {
				$Ctaxable = $MyRow['grosspay'] - $MyRow['sss'] - $MyRow['hdmf'] - $MyRow['philhealth'];
				if ($MyRow['othincome'] > 0) {
					$SQL = "SELECT othincid,amount
								FROM prlothincaddition
								WHERE prlothincaddition.employeeid='" . $MyRow['employeeid'] . "'
								AND prlothincaddition.payrollid='" . $PayrollID . "'";
					$OIDetails = DB_query($SQL);
					if (DB_num_rows($OIDetails) > 0) {
						while ($othrow = DB_fetch_array($OIDetails)) {
							$OIIDDesc = GetOthIncRow($othrow['othincid'], 1);
							if ($OIIDDesc == 'Non-Tax') {
								$Ctaxable -= $othrow['amount'];
							}
						}
					}
				}
				//$EstGrosstoEarn=$Ctaxable*$UnPaidPDthisYR;
				//grosspay and tax withheld for every employee
				//computer tax
				$MyEstGrossIncome = $Ctaxable;
				//$MyExemption=GetTaxStatusRow(GetEmpRow($MyRow['employeeid'],35),4);
				//$MyEstTaxableIncome=$MyEstGrossIncome-$MyExemption;
				$MyEstTaxableIncome = $MyEstGrossIncome;
				$MyTaxStatusID = GetEmpRow($MyRow['employeeid'], 35);
				//PRINTERR($PayrollID);
				//PRINTERR($MyTaxStatusID);
				//PRINTERR($FSPPID);

				//PRINTERR($MyEstTaxableIncome+' '+$PayrollID+' '+$MyTaxStatusID);
				$MyEstTax = GetMyTax2($MyEstTaxableIncome, $FSPPID, $MyTaxStatusID);
				//PRINTERR($MyEstTax);
				//if ($UnPaidPDthisYR==0) {
				//	$MyTaxWithheld=$MyEstTax-$TaxUpToDate;
				//} else {
				//	$MyTaxWithheld=($MyEstTax-$TaxUpToDate)/($UnPaidPDthisYR+1);
				//}
				$MyTaxWithheld = $MyEstTax;
				$SQL = 'UPDATE prlpayrolltrans SET tax=' . $MyTaxWithheld . '
									WHERE counterindex = ' . $MyRow['counterindex'];
				$PostTaxPay = DB_query($SQL);
				if ($Ctaxable > 0) {
					$SQL = "INSERT INTO prlemptaxfile (
								payrollid,
								employeeid,
								taxableincome,
								tax,
								fsmonth,
								fsyear)
								VALUES ('$PayrollID',
										'" . $MyRow['employeeid'] . "',
										'$Ctaxable',
										'$MyTaxWithheld',
										'" . $MyRow['fsmonth'] . "',
										'" . $MyRow['fsyear'] . "'
										)";
					$ErrMsg = _('Inserting Tax File failed.');
					$InsTaxRecords = DB_query($SQL, $ErrMsg);
				} //end of if ($Ctaxable>0)
			} //end ofwhile ($MyRow = DB_fetch_array($PayDetails)) list of employess
		}
	} elseif ($NumberofPayday < 12) {
		PRINTERR('No tax computation - tax table not ready...');
	}
} //isset post submit
?>