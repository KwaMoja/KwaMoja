<?php

If (isset($_POST['PrintPDF']) AND isset($_POST['PayrollID'])) {

	include('config.php');
	include('includes/PDFStarter.php');
	include('includes/ConnectDB.inc');
	include('includes/DateFunctions.inc');
	include('includes/prlFunctions.php');

	/* A4_Landscape */

	$Page_Width = 842;
	$Page_Height = 595;
	$Top_Margin = 20;
	$Bottom_Margin = 20;
	$Left_Margin = 25;
	$Right_Margin = 22;

	$PageSize = array(
		0,
		0,
		$Page_Width,
		$Page_Height
	);
	$pdf = new Cpdf($PageSize);

	$PageNumber = 0;

	$pdf->selectFont('./fonts/Helvetica.afm');

	/* Standard PDF file creation header stuff */
	$pdf->addinfo('Title', _('Payroll Register'));
	$pdf->addinfo('Subject', _('Payroll Register'));


	$PageNumber = 1;
	$line_height = 12;


	$PayDesc = GetPayrollRow($_POST['PayrollID'], 1);
	$FromPeriod = GetPayrollRow($_POST['PayrollID'], 3);
	$ToPeriod = GetPayrollRow($_POST['PayrollID'], 4);
	$PageNumber = 0;
	$FontSize = 10;
	$pdf->addinfo('Title', _('Payroll Register'));
	$pdf->addinfo('Subject', _('Payroll Register'));
	$line_height = 12;
	$EmpID = '';
	$Basic = 0;
	$OthInc = 0;
	$Lates = 0;
	$Absent = 0;
	$OT = 0;
	$Gross = 0;
	$SSS = 0;
	$HDMF = '';
	$PhilHealt = 0;
	$Loan = 0;
	$Tax = 0;
	$Net = 0;
	include('includes/PDFPayRegisterPageHeader.inc');
	$k = 0; //row colour counter
	$SQL = "SELECT employeeid,basicpay,othincome,absent,late,otpay,grosspay,loandeduction,sss,hdmf,grosspay,tax,netpay
			FROM prlpayrolltrans
			WHERE prlpayrolltrans.payrollid='" . $_POST['PayrollID'] . "'";
	$PayResult = DB_query($SQL);
	if (DB_num_rows($PayResult) > 0) {
		while ($MyRow = DB_fetch_array($PayResult)) {
			$EmpID = $MyRow['employeeid'];
			$FullName = GetName($EmpID);
			$Basic = $MyRow['basicpay'];
			$OthInc = $MyRow['othincome'];
			$OT = $MyRow['otpay'];
			$Gross = $MyRow['grosspay'];
			$SSS = $MyRow['sss'];
			$HDMF = $MyRow['hdmf'];
			$grosspay = $MyRow['grosspay'];
			$Loan = $MyRow['loandeduction'];
			$Tax = $MyRow['tax'];
			$Net = $MyRow['netpay'];

			$GTBasic += $MyRow['basicpay'];
			$GTOthInc += $MyRow['othincome'];
			$GTOT += $MyRow['otpay'];
			$GTGross += $MyRow['grosspay'];
			$GTSSS += $MyRow['sss'];
			$GTHDMF += $MyRow['hdmf'];
			$GTgrosspay += $MyRow['grosspay'];
			$GTLoan += $MyRow['loandeduction'];
			$GTTax += $MyRow['tax'];
			$GTNet += $MyRow['netpay'];

			//$YPos -= (2 * $line_height);  //double spacing
			$FontSize = 8;
			$pdf->selectFont('./fonts/Helvetica.afm');
			$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 50, $FontSize, $EmpID);
			$LeftOvers = $pdf->addTextWrap(100, $YPos, 120, $FontSize, $FullName, 'left');
			$LeftOvers = $pdf->addTextWrap(221, $YPos, 50, $FontSize, number_format($Basic, 2), 'right');
			$LeftOvers = $pdf->addTextWrap(272, $YPos, 50, $FontSize, number_format($OthInc, 2), 'right');
			$LeftOvers = $pdf->addTextWrap(313, $YPos, 50, $FontSize, number_format($Lates, 2), 'right');
			$LeftOvers = $pdf->addTextWrap(354, $YPos, 50, $FontSize, number_format($Absent, 2), 'right');
			$LeftOvers = $pdf->addTextWrap(395, $YPos, 50, $FontSize, number_format($OT, 2), 'right');
			$LeftOvers = $pdf->addTextWrap(446, $YPos, 50, $FontSize, number_format($Gross, 2), 'right');
			$LeftOvers = $pdf->addTextWrap(487, $YPos, 50, $FontSize, number_format($SSS, 2), 'right');
			$LeftOvers = $pdf->addTextWrap(528, $YPos, 50, $FontSize, number_format($HDMF, 2), 'right');
			$LeftOvers = $pdf->addTextWrap(569, $YPos, 50, $FontSize, number_format($grosspay, 2), 'right');
			$LeftOvers = $pdf->addTextWrap(610, $YPos, 50, $FontSize, number_format($Loan, 2), 'right');
			$LeftOvers = $pdf->addTextWrap(671, $YPos, 50, $FontSize, number_format($Tax, 2), 'right');
			$LeftOvers = $pdf->addTextWrap(722, $YPos, 50, $FontSize, number_format($Net, 2), 'right');
			$YPos -= $line_height;
			if ($YPos < ($Bottom_Margin)) {
				include('includes/PDFPayRegisterPageHeader.inc');
			}
		}

	} //end of loop

	$LeftOvers = $pdf->line($Page_Width - $Right_Margin, $YPos, $Left_Margin, $YPos);
	$YPos -= (2 * $line_height);
	$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 150, $FontSize, 'Grand Total');
	$LeftOvers = $pdf->addTextWrap(221, $YPos, 50, $FontSize, number_format($GTBasic, 2), 'right');
	$LeftOvers = $pdf->addTextWrap(221, $YPos, 50, $FontSize, number_format($GTBasic, 2), 'right');
	$LeftOvers = $pdf->addTextWrap(272, $YPos, 50, $FontSize, number_format($GTOthInc, 2), 'right');
	$LeftOvers = $pdf->addTextWrap(313, $YPos, 50, $FontSize, number_format($GTLates, 2), 'right');
	$LeftOvers = $pdf->addTextWrap(354, $YPos, 50, $FontSize, number_format($GTAbsent, 2), 'right');
	$LeftOvers = $pdf->addTextWrap(395, $YPos, 50, $FontSize, number_format($GTOT, 2), 'right');
	$LeftOvers = $pdf->addTextWrap(446, $YPos, 50, $FontSize, number_format($GTGross, 2), 'right');
	$LeftOvers = $pdf->addTextWrap(487, $YPos, 50, $FontSize, number_format($GTSSS, 2), 'right');
	$LeftOvers = $pdf->addTextWrap(528, $YPos, 50, $FontSize, number_format($GTHDMF, 2), 'right');
	$LeftOvers = $pdf->addTextWrap(569, $YPos, 50, $FontSize, number_format($GTgrosspay, 2), 'right');
	$LeftOvers = $pdf->addTextWrap(610, $YPos, 50, $FontSize, number_format($GTLoan, 2), 'right');
	$LeftOvers = $pdf->addTextWrap(671, $YPos, 50, $FontSize, number_format($GTTax, 2), 'right');
	$LeftOvers = $pdf->addTextWrap(722, $YPos, 50, $FontSize, number_format($GTNet, 2), 'right');

	$LeftOvers = $pdf->line($Page_Width - $Right_Margin, $YPos, $Left_Margin, $YPos);


	$pdfcode = $pdf->output();
	$len = strlen($pdfcode);
	if ($len <= 20) {
		$Title = _('Payroll Register Error');
		include('includes/header.inc');
		echo '<p>';
		prnMsg(_('There were no entries to print out for the selections specified'));
		echo '<br /><a href="' . $RootPath . '/index.php?">' . _('Back to the menu') . '</a>';
		include('includes/footer.inc');
		exit;
	} else {
		header('Content-type: application/pdf');
		header('Content-Length: ' . $len);
		header('Content-Disposition: inline; filename=PayrollRegister.pdf');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		$pdf->Stream();

	}
	exit;

} elseif (isset($_POST['ShowPR'])) {
	include('includes/session.inc');
	$Title = _('grosspay Monthly Premium Listing');
	include('includes/header.inc');
	echo 'Use PrintPDF instead';
	echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
	include('includes/footer.inc');
	exit;
} else {
	/*The option to print PDF was not hit */

	include('includes/session.inc');
	$Title = _('Payroll Register');
	include('includes/header.inc');

	echo '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '?">';
	echo '<table><tr><td>' . _('Select Payroll:') . '</td><td><select Name="PayrollID">';
	DB_data_seek($Result, 0);
	$SQL = 'SELECT payrollid, payrolldesc FROM prlpayrollperiod';
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['payrollid'] == isset($_POST['PayrollID'])) {
			echo '<option selected="selected" value=';
		} else {
			echo '<option value=';
		}
		echo $MyRow['payrollid'] . '>' . $MyRow['payrolldesc'];
	} //end while loop
	echo '</select></td></tr>';
	echo "</table><p><input type='Submit' name='ShowPR' value='" . _('Show Payroll Register') . "'>";
	echo "<p><input type='Submit' name='PrintPDF' value='" . _('PrintPDF') . "'>";

	include('includes/footer.inc');
}
/*end of else not PrintPDF */

?>