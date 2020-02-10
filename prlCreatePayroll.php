<?php
/* $Revision: 1.0 $ */

$PageSecurity = 5;

include ('includes/session.php');

$Title = _('Payroll Records Maintenance');

include ('includes/header.php');
include ('includes/prlFunctions.php');

if (isset($_GET['PayrollID'])) {
	$PayrollID = $_GET['PayrollID'];
} elseif (isset($_POST['PayrollID'])) {
	$PayrollID = $_POST['PayrollID'];
} else {
	unset($PayrollID);
}

if ($_POST['Generate'] == _('Generate Payroll Data')) {
	include ('includes/prlGenerateData.php');
	include ('includes/prlComputeBasic.php');
	include ('includes/prlComputeOthIncome.php');
	include ('includes/prlComputeTD.php');
	include ('includes/prlComputeOT.php');
	include ('includes/prlComputeGross.php');
	include ('includes/prlComputeLoan.php');
	include ('includes/prlComputeSSS.php');
	include ('includes/prlComputeHDMF.php');
	include ('includes/prlComputePH.php');
	//include('includes/prlComputeTAX.php'); //annualized method
	include ('includes/prlComputeTAX2.php'); //common method
	include ('includes/prlComputeNet.php');
}

if ($_POST['Close'] == _('Close Payroll Period')) {
	$Status = GetOpenCloseStr(GetPayrollRow($PayrollID, 11));
	if ($Status == 'Closed') {
		exit("Payroll is already closed. Re-open first...");
	} else {
		/*
			$SQL = "SELECT loantableid,amount
				FROM prlloandeduction
				WHERE payrollid='$PayrollID'";
				$LoanDetails = DB_query($SQL);
				if(DB_num_rows($LoanDetails)>0)
				{
					while ($loanrow = DB_fetch_array($LoanDetails))
					{
						$LoanPayment=$loanrow['amount'];
						if ($LoanPayment>0 or $LoanPayment<>null) {
							$SQL = 'UPDATE prlloanfile SET ytddeduction=ytddeduction+'.$LoanPayment.', loanbalance=loanbalance-'.$LoanPayment.'
							WHERE loantableid = ' . $loanrow['loantableid'];
							$PostLoanPay = DB_query($SQL);
						}
					}
				}
		*/
		$SQL = "UPDATE prlpayrollperiod SET
					payclosed=1
					 WHERE payrollid = '$PayrollID'";
		$ErrMsg = _('The payroll record could not be updated because');
		$DbgMsg = _('The SQL that was used to update the payroll failed was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
		prnMsg(_('The payroll master record for') . ' ' . $PayrollID . ' ' . _('has been closed'), 'success');
		exit("Payroll is succesfully closed...");
	}
}

if ($_POST['Purge'] == _('Purge Payroll Period')) {
	exit("Not implemented at this moment...");
}

if ($_POST['Reopen'] == _('Re-open Payroll Period')) {
	$Status = GetOpenCloseStr(GetPayrollRow($PayrollID, 11));
	if ($Status == 'Open') {
		exit("Payroll is already open...");
	} else {
		/*
			$SQL = "SELECT loantableid,amount
				FROM prlloandeduction
				WHERE payrollid='$PayrollID'";
				$LoanDetails = DB_query($SQL);
				if(DB_num_rows($LoanDetails)>0)
				{
					while ($loanrow = DB_fetch_array($LoanDetails))
					{
						$LoanPayment=$loanrow['amount'];
						if ($LoanPayment>0 or $LoanPayment<>null) {
							$SQL = 'UPDATE prlloanfile SET ytddeduction=ytddeduction-'.$LoanPayment.', loanbalance=loanbalance+'.$LoanPayment.'
							WHERE loantableid = ' . $loanrow['loantableid'];
							$PostLoanPay = DB_query($SQL);
						}
					}
				}
		*/

		$SQL = "UPDATE prlpayrollperiod SET
					payclosed=0
					 WHERE payrollid = '$PayrollID'";
		$ErrMsg = _('The payroll record could not be updated because');
		$DbgMsg = _('The SQL that was used to update the payroll failed was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
		prnMsg(_('The payroll master record for') . ' ' . $PayrollID . ' ' . _('has been opened'), 'success');
		exit("Payroll is succesfully re-opened...");
	}
}

if (!isset($PayrollID)) {
} else {
	//PayrollID exists - either passed when calling the form or from the form itself
	echo "<form method='post' action='" . basename(__FILE__) . '?' . SID . "'>";
	echo '<table>';
	if (!isset($_POST['New'])) {
		$SQL = "SELECT payrollid,
					payrolldesc,
					payperiodid,
					startdate,
					enddate,
					fsmonth,
					fsyear,
					deductsss,
					deducthdmf,
					deductphilhealth,
					payclosed
			FROM prlpayrollperiod
			WHERE payrollid = '$PayrollID'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		$Description = $MyRow['payrolldesc'];
		$PayPeriodID = GetPayPeriodDesc($MyRow['payperiodid']);
		$StartDate = ConvertSQLDate($MyRow['startdate']);
		$EndDate = ConvertSQLDate($MyRow['enddate']);
		$FSMonth = GetMonthStr($MyRow['fsmonth']);
		$FSYear = $MyRow['fsyear'];
		$SSS = GetYesNoStr($MyRow['deductsss']);
		$HDMF = GetYesNoStr($MyRow['deducthdmf']);
		$PhilHealth = GetYesNoStr($MyRow['deductphilhealth']);
		$Status = GetOpenCloseStr($MyRow['payclosed']);
		echo "<input type=HIDDEN name='PayrollID' value='$PayrollID'>";
	} else {
		// its a new employee  being added
		echo "<input type=HIDDEN name='New' value='Yes'>";
		echo '<tr><td>' . _('Payroll ID') . ':</td><td><input type="text" name="PayrollID" value="', $PayrollID, '" size=12 maxlength=10></td></tr>';
	}
	echo "<table WIDTH=30% BORDER=2><tr></tr>";
	echo '<tr><td WIDTH=100%>';
	echo '<a href="' . $RootPath . '/prlEditPayroll.php?&PayrollID=' . $PayrollID . '">' . _('Edit Payroll Period') . '</a>';
	echo '</td><td WIDTH=100%>';
	echo '</td></tr></table><BR>';
	echo '<FONT size=1>' . '' . "</FONT><input type=SUBMIT name='Close' value='" . _('Close Payroll Period') . "'><input type=SUBMIT name='Purge' value='" . _('Purge Payroll Period') . "'><HR>";
	echo '<FONT size=1>' . '' . "</FONT><input type=SUBMIT name='Generate' value='" . _('Generate Payroll Data') . "'><input type=SUBMIT name='Reopen' value='" . _('Re-open Payroll Period') . "'><HR>";

?>

<table width="640" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td height="180" valign="top">

      <table width="90%" border="1" cellspacing="0" cellpadding="0" align="center" bordercolordark="#CCCCCC" bordercolorlight="#CCCCCC" bgcolor="#F2F2F2">
        <tr bgcolor="#F4F4F4">
          <td height="30" width="26%">
            <div align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1">Payroll ID
              :</font></div>
          </td>
          <td height="30" width="74%"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1" color="#000066"><b><? echo $PayrollID; ?></b></font></td>
        </tr>
        <tr bgcolor="#F4F4F4">
          <td height="30" width="26%">
            <div align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1">Description
              :</font></div>
          </td>
          <td height="30" width="74%"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1" color="#000066"><b><? echo $Description; ?></b></font></td>
        </tr>
        <tr bgcolor="#F4F4F4">
          <td height="30" width="26%">
            <div align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1">Pay Period
              :</font></div>
          </td>
          <td height="30" width="74%">
            <p><font face="Verdana, Arial, Helvetica, sans-serif" size="-1" color="#000066"><b><? echo $PayPeriodID; ?></b></font></p>
          </td>
        </tr>
        <tr bgcolor="#F4F4F4">
          <td height="30" width="26%">
            <div align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1">Start Date
              :</font></div>
          </td>
          <td height="30" width="74%"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1" color="#000066"><b><? echo $StartDate; ?></a></b></font></td>
        </tr>
        <tr bgcolor="#F4F4F4">
          <td height="30" width="26%">
            <div align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1">End Date
              :</font></div>
          </td>
          <td height="30" width="74%"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1" color="#000066"><b><? echo $EndDate; ?></a></b></font></td>
        </tr>
        <tr bgcolor="#F4F4F4">
          <td height="30" width="26%">
            <div align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1">FS Month
              :</font></div>
          </td>
          <td height="30" width="74%"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1" color="#000066"><b><? echo "$FSMonth $FSYear"; ?></a></b></font></td>
        </tr>
        <tr bgcolor="#F4F4F4">
          <td height="30" width="26%">
            <div align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1">Deduct SSS
              :</font></div>
          </td>
          <td height="30" width="74%"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1" color="#000066"><b><? echo $SSS; ?></a></b></font></td>
        </tr>
        <tr bgcolor="#F4F4F4">
          <td height="30" width="26%">
            <div align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1">Deduct HDMF
              :</font></div>
          </td>
          <td height="30" width="74%"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1" color="#000066"><b><? echo $HDMF; ?></a></b></font></td>
        </tr>
        <tr bgcolor="#F4F4F4">
          <td height="30" width="26%">
            <div align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1">Deduct PhilHealth
              :</font></div>
          </td>
          <td height="30" width="74%"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1" color="#000066"><b><? echo $PhilHealth; ?></a></b></font></td>
        </tr>
        <tr bgcolor="#F4F4F4">
          <td height="30" width="26%">
            <div align="right"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1">Payroll Status
              :</font></div>
          </td>
          <td height="30" width="74%" bgcolor="#F4F4F4"><font face="Verdana, Arial, Helvetica, sans-serif" size="-1"><b><font color="#000066"><? echo $Status; ?></font></b></font></td>
        </tr>
      </table>

    </td>
  </tr>

</table>
<?php
} // end of main ifs
include ('includes/footer.php');
?>
