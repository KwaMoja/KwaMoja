<?php
$PageSecurity = 2;
//include_once('includes/printerrmsg.php');
if (isset($_POST['PrintPDF']) and isset($_POST['PayrollID'])) {
	//printerr($_POST['PayrollID']);
	include ('config.php');
	include ('includes/PDFStarter.php');
	include ('includes/ConnectDB.php');
	include ('includes/DateFunctions.php');
	include ('includes/prlFunctions.php');

	$PDF->selectFont('./fonts/Helvetica.afm');

	/* Standard PDF file creation header stuff */
	$PDF->addinfo('Title', _('Pay Slip'));
	$PDF->addinfo('Subject', _('Pay Slip'));

	//(612,792);
	$PageNumber = 1;
	$line_height = 12;

	$PayDesc = GetPayrollRow($_POST['PayrollID'], 1);
	$FromPeriod = GetPayrollRow($_POST['PayrollID'], 3);
	$ToPeriod = GetPayrollRow($_POST['PayrollID'], 4);
	$FontSize = 10;
	$PDF->addinfo('Title', _('Pay Slip'));
	$PDF->addinfo('Subject', _('Pay Slip'));
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

	$YPos = $Page_Height - $Top_Margin;
	$YPos-= (2 * $line_height);

	$PaySlip = 1;
	$SQL = "SELECT prlpayrolltrans.employeeid,
	               prlpayrolltrans.basicpay,
				   prlpayrolltrans.othincome,
				   prlpayrolltrans.absent,
				   prlpayrolltrans.late,
				   prlpayrolltrans.otpay,
				   prlpayrolltrans.grosspay,
				   prlpayrolltrans.loandeduction,
				   prlpayrolltrans.sss,
				   prlpayrolltrans.hdmf,
				   prlpayrolltrans.philhealth,
				   prlpayrolltrans.tax,
				   prlpayrolltrans.netpay,
				   prlemployeemaster.employeeid,
					prlemployeemaster.lastname,
					prlemployeemaster.firstname
			FROM prlpayrolltrans,prlemployeemaster
			WHERE prlpayrolltrans.payrollid='" . $_POST['PayrollID'] . "'
			AND prlpayrolltrans.employeeid = prlemployeemaster.employeeid
			ORDER BY lastname, firstname";

	$PayResult = DB_query($SQL);
	if (DB_num_rows($PayResult) > 0) {
		while ($MyRow = DB_fetch_array($PayResult)) {

			$EmpID = $MyRow['employeeid'];
			$FullName = GetName($EmpID);

			$Basic = $MyRow['basicpay'];
			$OthInc = $MyRow['othincome'];
			$Lates = $MyRow['late'];
			$Absent = $MyRow['absent'];
			$OT = $MyRow['otpay'];
			$Gross = $MyRow['grosspay'];
			$SSS = $MyRow['sss'];
			$HDMF = $MyRow['hdmf'];
			$PhilHealth = $MyRow['philhealth'];
			$Loan = $MyRow['loandeduction'];
			$Tax = $MyRow['tax'];
			$Net = $MyRow['netpay'];
			$Deduction = $SSS + $HDMF + $PhilHealth + $Loan + $Tax;

			if ($PaySlip == 1) {
				$FontSize = 10;
				$PDF->selectFont('./fonts/Helvetica-Bold.afm');
				$HeadPos1 = $YPos;
				$LeftOvers = $PDF->addText($Left_Margin, $YPos, $FontSize, $_SESSION['CompanyRecord']['coyname']);
				$YPos-= (1 * $line_height);
				$FontSize = 10;
				$PDF->selectFont('./fonts/Helvetica-Bold.afm');
				$FullName = _('Name : ') . $FullName;
				$LeftOvers = $PDF->addText($Left_Margin, $YPos, $FontSize, $FullName);
				$FontSize = 8;
				$YPos-= (1 * $line_height);
				$LeftOvers = $PDF->addText($Left_Margin, $YPos, $FontSize, $PayDesc);
				$YPos-= (1 * $line_height);
				$Heading2 = _('Period from ') . $FromPeriod . ' to ' . $ToPeriod;
				$LeftOvers = $PDF->addText($Left_Margin, $YPos, $FontSize, $Heading2);
				$YPos-= 25;
				/*Draw a rectangle to put the headings in     */
				$BoxHeight = 20;
				//$PDF->line($Left_Margin, $YPos+$BoxHeight,$Page_Width-$Right_Margin, $YPos+$BoxHeight); //top vertical
				$PDF->line($Left_Margin, $YPos + $BoxHeight, 262, $YPos + $BoxHeight); //top vertical
				$PDF->line($Left_Margin, $YPos + $BoxHeight, $Left_Margin, $YPos);
				$PDF->line($Left_Margin, $YPos, 262, $YPos); //bottom vertical
				$PDF->line(262, $YPos + $BoxHeight, 262, $YPos); //right horizontal
				$YPos+= 5;
				/*set up the headings */
				$FontSize = 10;
				$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 65, $FontSize, 'Income', 'right');
				$LeftOvers = $PDF->addTextWrap(155, $YPos, 65, $FontSize, 'Deduction', 'right');
				$YPos-= (2 * $line_height);
				//$YPos -= (2 * $line_height);  //double spacing
				$OldYPos1 = $YPos;
				$FontSize = 8;
				$PDF->selectFont('./fonts/Helvetica.afm');
				$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 55, $FontSize, 'Basic : ', 'right');
				$LeftOvers = $PDF->addTextWrap(110, $YPos, 40, $FontSize, number_format($Basic, 2), 'right');
				$YPos-= $line_height;
				$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 55, $FontSize, 'Other Income : ', 'right');
				$LeftOvers = $PDF->addTextWrap(110, $YPos, 40, $FontSize, number_format($OthInc, 2), 'right');
				$YPos-= $line_height;
				$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 55, $FontSize, 'Lates : ', 'right');
				$LeftOvers = $PDF->addTextWrap(110, $YPos, 40, $FontSize, number_format($Lates, 2), 'right');
				$YPos-= $line_height;
				$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 55, $FontSize, 'Absent : ', 'right');
				$LeftOvers = $PDF->addTextWrap(110, $YPos, 40, $FontSize, number_format($Absent, 2), 'right');
				$YPos-= $line_height;
				$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 55, $FontSize, 'Overtime : ', 'right');
				$LeftOvers = $PDF->addTextWrap(110, $YPos, 40, $FontSize, number_format($OT, 2), 'right');
				$YPos-= $line_height;

				//2nd column
				$OldYPos2 = $OldYPos1;
				$YPos = $OldYPos1;
				$FontSize = 8;
				$PDF->selectFont('./fonts/Helvetica.afm');
				$LeftOvers = $PDF->addTextWrap(155, $YPos, 65, $FontSize, 'SSS : ', 'right');
				$LeftOvers = $PDF->addTextWrap(221, $YPos, 40, $FontSize, number_format($SSS, 2), 'right');
				$YPos-= $line_height;
				$LeftOvers = $PDF->addTextWrap(155, $YPos, 65, $FontSize, 'HDMF : ', 'right');
				$LeftOvers = $PDF->addTextWrap(221, $YPos, 40, $FontSize, number_format($HDMF, 2), 'right');
				$YPos-= $line_height;
				$LeftOvers = $PDF->addTextWrap(155, $YPos, 65, $FontSize, 'PHIC : ', 'right');
				$LeftOvers = $PDF->addTextWrap(221, $YPos, 40, $FontSize, number_format($PhilHealth, 2), 'right');
				$YPos-= $line_height;
				$LeftOvers = $PDF->addTextWrap(155, $YPos, 65, $FontSize, 'Tax : ', 'right');
				$LeftOvers = $PDF->addTextWrap(221, $YPos, 40, $FontSize, number_format($Tax, 2), 'right');
				$YPos-= $line_height;
				$LeftOvers = $PDF->addTextWrap(155, $YPos, 65, $FontSize, 'Loan Deduction : ', 'right');
				$LeftOvers = $PDF->addTextWrap(221, $YPos, 40, $FontSize, number_format($Loan, 2), 'right');
				$YPos-= 25;
				/*Draw a rectangle to put the headings in     */
				$BoxHeight = 20;
				//$PDF->line($Left_Margin, $YPos+$BoxHeight,$Page_Width-$Right_Margin, $YPos+$BoxHeight); //top vertical
				$PDF->line($Left_Margin, $YPos + $BoxHeight, 262, $YPos + $BoxHeight); //top vertical
				$PDF->line($Left_Margin, $YPos + $BoxHeight, $Left_Margin, $YPos);
				$PDF->line($Left_Margin, $YPos, 262, $YPos); //bottom vertical
				$PDF->line(262, $YPos + $BoxHeight, 262, $YPos); //right horizontal
				$YPos+= 5;
				/*set up the headings */
				$Xpos = $Left_Margin + 1;
				$LeftOvers = $PDF->addTextWrap($Xpos, $YPos, 65, $FontSize, 'Gross Income : ', 'right');
				$LeftOvers = $PDF->addTextWrap(110, $YPos, 40, $FontSize, number_format($Gross, 2), 'right');
				$LeftOvers = $PDF->addTextWrap(155, $YPos, 65, $FontSize, 'Total Deduction : ', 'right');
				$LeftOvers = $PDF->addTextWrap(221, $YPos, 40, $FontSize, number_format($Deduction, 2), 'right');

				$YPos-= 50;
				/*Draw a rectangle to put the headings in     */
				$BoxHeight = 45;
				//$PDF->line($Left_Margin, $YPos+$BoxHeight,262, $YPos+$BoxHeight); //top vertical
				$PDF->line($Left_Margin, $YPos + $BoxHeight, $Left_Margin, $YPos);
				$PDF->line($Left_Margin, $YPos, 262, $YPos); //bottom vertical
				$PDF->line(262, $YPos + $BoxHeight, 262, $YPos); //right horizontal
				$YPos+= 5;
				/*set up the headings */
				$Xpos = $Left_Margin + 1;
				$LeftOvers = $PDF->addTextWrap($Xpos, $YPos, 100, $FontSize, 'Employee Signature', 'right');
				$LeftOvers = $PDF->addTextWrap(150, $YPos, 65, $FontSize, 'Net Pay : ', 'right');
				$LeftOvers = $PDF->addTextWrap(216, $YPos, 40, $FontSize, number_format($Net, 2), 'right');
				$YPos-= $line_height;

				$PaySlip = 2;
			} elseif ($PaySlip == 2) {
				//header
				$FontSize = 10;
				$PDF->selectFont('./fonts/Helvetica-Bold.afm');
				$YPos = $HeadPos1;
				$LeftOvers = $PDF->addText(322, $YPos, $FontSize, $_SESSION['CompanyRecord']['coyname']);
				$YPos-= (1 * $line_height);
				$FontSize = 10;
				$PDF->selectFont('./fonts/Helvetica-Bold.afm');
				$FullName = _('Name : ') . $FullName;
				$LeftOvers = $PDF->addText(322, $YPos, $FontSize, $FullName);
				$FontSize = 10;
				$YPos-= (1 * $line_height);
				$LeftOvers = $PDF->addText(322, $YPos, $FontSize, $PayDesc);
				$YPos-= (1 * $line_height);
				$Heading2 = _('Period from ') . $FromPeriod . ' to ' . $ToPeriod;
				$LeftOvers = $PDF->addText(322, $YPos, $FontSize, $Heading2);
				$YPos-= 25;
				/*Draw a rectangle to put the headings in     */
				$BoxHeight = 20;
				$PDF->line(321, $YPos + $BoxHeight, 539, $YPos + $BoxHeight); //top vertical
				$PDF->line(321, $YPos + $BoxHeight, 321, $YPos); //left horizontal
				$PDF->line(321, $YPos, 539, $YPos); //bottom vertical
				$PDF->line(539, $YPos + $BoxHeight, 539, $YPos); //right horizontal
				$YPos+= 5;
				/*set up the headings */
				$FontSize = 10;
				$LeftOvers = $PDF->addTextWrap(322, $YPos, 65, $FontSize, 'Income', 'right');
				$LeftOvers = $PDF->addTextWrap(423, $YPos, 65, $FontSize, 'Deduction', 'right');
				$YPos-= (2 * $line_height);

				//$YPos -= (2 * $line_height);  //double spacing
				$YPos = $OldYPos1;
				$FontSize = 8;
				$PDF->selectFont('./fonts/Helvetica.afm');
				$LeftOvers = $PDF->addTextWrap(322, $YPos, 65, $FontSize, 'Basic : ', 'right');
				$LeftOvers = $PDF->addTextWrap(387, $YPos, 40, $FontSize, number_format($Basic, 2), 'right');
				$YPos-= $line_height;
				$LeftOvers = $PDF->addTextWrap(322, $YPos, 65, $FontSize, 'Other Income : ', 'right');
				$LeftOvers = $PDF->addTextWrap(387, $YPos, 40, $FontSize, number_format($OthInc, 2), 'right');
				$YPos-= $line_height;
				$LeftOvers = $PDF->addTextWrap(322, $YPos, 65, $FontSize, 'Lates : ', 'right');
				$LeftOvers = $PDF->addTextWrap(387, $YPos, 40, $FontSize, number_format($Lates, 2), 'right');
				$YPos-= $line_height;
				$LeftOvers = $PDF->addTextWrap(322, $YPos, 65, $FontSize, 'Absent : ', 'right');
				$LeftOvers = $PDF->addTextWrap(387, $YPos, 40, $FontSize, number_format($Absent, 2), 'right');
				$YPos-= $line_height;
				$LeftOvers = $PDF->addTextWrap(322, $YPos, 65, $FontSize, 'Overtime : ', 'right');
				$LeftOvers = $PDF->addTextWrap(387, $YPos, 40, $FontSize, number_format($OT, 2), 'right');
				$YPos-= $line_height;

				//2nd column
				$YPos = $OldYPos2;
				$FontSize = 8;
				$PDF->selectFont('./fonts/Helvetica.afm');
				$LeftOvers = $PDF->addTextWrap(432, $YPos, 65, $FontSize, 'SSS : ', 'right');
				$LeftOvers = $PDF->addTextWrap(498, $YPos, 40, $FontSize, number_format($SSS, 2), 'right');
				$YPos-= $line_height;
				$LeftOvers = $PDF->addTextWrap(432, $YPos, 65, $FontSize, 'HDMF : ', 'right');
				$LeftOvers = $PDF->addTextWrap(498, $YPos, 40, $FontSize, number_format($HDMF, 2), 'right');
				$YPos-= $line_height;
				$LeftOvers = $PDF->addTextWrap(432, $YPos, 65, $FontSize, 'PHIC : ', 'right');
				$LeftOvers = $PDF->addTextWrap(498, $YPos, 40, $FontSize, number_format($PhilHealth, 2), 'right');
				$YPos-= $line_height;
				$LeftOvers = $PDF->addTextWrap(432, $YPos, 65, $FontSize, 'Tax : ', 'right');
				$LeftOvers = $PDF->addTextWrap(498, $YPos, 40, $FontSize, number_format($Tax, 2), 'right');
				$YPos-= $line_height;
				$LeftOvers = $PDF->addTextWrap(432, $YPos, 65, $FontSize, 'Loan Deduction : ', 'right');
				$LeftOvers = $PDF->addTextWrap(498, $YPos, 40, $FontSize, number_format($Loan, 2), 'right');
				$YPos-= 25;
				/*Draw a rectangle to put the headings in     */
				$BoxHeight = 20;
				$PDF->line(321, $YPos + $BoxHeight, 539, $YPos + $BoxHeight); //top vertical
				$PDF->line(321, $YPos + $BoxHeight, 321, $YPos); //left horizontal
				$PDF->line(321, $YPos, 539, $YPos); //bottom vertical
				$PDF->line(539, $YPos + $BoxHeight, 539, $YPos); //right horizontal
				$YPos+= 5;
				/*set up the headings */
				$LeftOvers = $PDF->addTextWrap(322, $YPos, 65, $FontSize, 'Gross Income : ', 'right');
				$LeftOvers = $PDF->addTextWrap(387, $YPos, 40, $FontSize, number_format($Gross, 2), 'right');
				$LeftOvers = $PDF->addTextWrap(432, $YPos, 65, $FontSize, 'Total Deduction : ', 'right');
				$LeftOvers = $PDF->addTextWrap(498, $YPos, 40, $FontSize, number_format($Deduction, 2), 'right');
				$YPos-= 50;
				/*Draw a rectangle to put the headings in     */
				$BoxHeight = 45;
				$PDF->line(321, $YPos + $BoxHeight, 321, $YPos);
				$PDF->line(321, $YPos, 539, $YPos); //bottom vertical
				$PDF->line(539, $YPos + $BoxHeight, 539, $YPos); //right horizontal
				$YPos+= 5;
				/*set up the headings */
				$LeftOvers = $PDF->addTextWrap(322, $YPos, 100, $FontSize, 'Employee Signature', 'right');
				$LeftOvers = $PDF->addTextWrap(432, $YPos, 65, $FontSize, 'Net Pay : ', 'right');
				$LeftOvers = $PDF->addTextWrap(498, $YPos, 40, $FontSize, number_format($Net, 2), 'right');
				$YPos-= $line_height;
				$YPos-= (5 * $line_height);

				$PaySlip = 1;
			}

			if ($YPos < ($Bottom_Margin)) {
				$PageNumber++;
				if ($PageNumber > 1) {
					$PDF->newPage();
					$YPos = $Page_Height - $Top_Margin;
					$YPos-= (2 * $line_height);
				}
			}
		}

	} //end of loop
	

	$PDFcode = $PDF->output();
	$len = strlen($PDFcode);
	if ($len <= 20) {
		$Title = _('Payroll Register Error');
		include ('includes/header.php');
		echo '<p>';
		prnMsg(_('There were no entries to print out for the selections specified'));
		echo '<BR><A HREF="' . $RootPath . '/index.php?' . SID . '">' . _('Back to the menu') . '</A>';
		include ('includes/footer.php');
		exit;
	} else {
		header('Content-type: application/pdf');
		header('Content-Length: ' . $len);
		header('Content-Disposition: inline; filename=PayrollRegister.pdf');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		$PDF->Stream();

	}
	exit;

} elseif (isset($_POST['ShowPR'])) {
	include ('includes/session.php');
	$Title = _('Pay Slip');
	include ('includes/header.php');
	echo 'Use PrintPDF instead';
	echo "<BR><A HREF='" . $RootPath . "/index.php?" . SID . "'>" . _('Back to the menu') . '</A>';
	include ('includes/footer.php');
	exit;
} else { /*The option to print PDF was not hit */

	include ('includes/session.php');
	$Title = _('Pay Slip');
	include ('includes/header.php');

	echo '<form method="POST" ACTION="prlRepPaySlip.php"';
	echo '<table><tr><td>' . _('Select Payroll:') . '</td><td><select Name="PayrollID">';
	DB_data_seek($Result, 0);
	$SQL = 'SELECT payrollid, payrolldesc FROM prlpayrollperiod';
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['payrollid'] == $_POST['PayrollID']) {
			echo '<option selected="selected" value=';
		} else {
			echo "<option value=";
		}
		//$pn = $MyRow['payrollid'] . $MyRow['payrolldesc'];
		echo $MyRow['payrollid'] . '>' . $MyRow['payrolldesc'];
	} //end while loop
	echo '</select></td></tr>';
	echo '</table><P><input type="submit" name="PrintPDF" value="' . _('PrintPDF') . '">';
	include ('includes/footer.php');;
} /*end of else not PrintPDF */

?>
