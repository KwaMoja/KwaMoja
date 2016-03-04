<?php

If (isset($_POST['PrintPDF']) AND isset($_POST['FSYear'])) {

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
	$pdf->addinfo('Title', _('YTD Payroll Register'));
	$pdf->addinfo('Subject', _('YTD Payroll Register'));


	$PageNumber = 1;
	$line_height = 12;

	$PageNumber = 0;
	$FontSize = 10;
	$pdf->addinfo('Title', _('YTD Payroll Register'));
	$pdf->addinfo('Subject', _('YTD Payroll Register'));
	$line_height = 12;
	include('includes/PDFPayRegYTDPageHeader.inc');
	//list of all employees
	$SQL = "SELECT employeeid
			FROM prlemployeemaster
			WHERE prlemployeemaster.employeeid<>''";
	$EmpListResult = DB_query($SQL, _('Could not test to see that all detail records properly initiated'));
	if (DB_num_rows($EmpListResult) > 0) {
		while ($emprow = DB_fetch_array($EmpListResult)) {
			$k = 0; //row colour counter
			$SQL = "SELECT sum(basicpay) AS Basic,sum(othincome) AS OthInc,sum(absent) as Absent,
					          sum(late) AS Late,sum(otpay) AS OT,sum(grosspay) AS GrossPay,
							  sum(loandeduction) AS LoanDed,sum(sss) AS SSS,sum(hdmf) AS HDMF,sum(philhealth) AS PH,
							  sum(tax) AS Tax,sum(netpay) AS NetPay
					FROM prlpayrolltrans
					WHERE prlpayrolltrans.employeeid='" . $emprow['employeeid'] . "'
					AND prlpayrolltrans.fsyear='" . $FSYear . "'";
			$PayResult = DB_query($SQL);
			if (DB_num_rows($PayResult) > 0) {
				$MyRow = DB_fetch_array($PayResult);
				$EmpID = $emprow['employeeid'];
				$FullName = GetName($EmpID);
				$Basic = $MyRow['Basic'];
				$OthInc = $MyRow['OthInc'];
				$Late = $MyRow['Late'];
				$Absent = $MyRow['Absent'];
				$OT = $MyRow['OT'];
				$Gross = $MyRow['GrossPay'];
				$SSS = $MyRow['SSS'];
				$HDMF = $MyRow['HDMF'];
				$PH = $MyRow['PH'];
				$LoanDed = $MyRow['LoanDed'];
				$Tax = $MyRow['Tax'];
				$NetPay = $MyRow['NetPay'];

				$GTBasic += $MyRow['Basic'];
				$GTOthInc += $MyRow['OthInc'];
				$GTLate += $MyRow['Late'];
				$GTAbsent += $MyRow['Absent'];
				$GTOT += $MyRow['OT'];
				$GTGross += $MyRow['GrossPay'];
				$GTSSS += $MyRow['SSS'];
				$GTHDMF += $MyRow['HDMF'];
				$GTPhilHealth += $MyRow['PH'];
				$GTLoan += $MyRow['LoanDed'];
				$GTTax += $MyRow['Tax'];
				$GTNet += $MyRow['NetPay'];

				//$YPos -= (2 * $line_height);  //double spacing
				$FontSize = 8;
				$pdf->selectFont('./fonts/Helvetica.afm');
				$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 50, $FontSize, $EmpID);
				$LeftOvers = $pdf->addTextWrap(100, $YPos, 120, $FontSize, $FullName, 'left');
				$LeftOvers = $pdf->addTextWrap(221, $YPos, 50, $FontSize, number_format($Basic, 2), 'right');
				$LeftOvers = $pdf->addTextWrap(272, $YPos, 50, $FontSize, number_format($OthInc, 2), 'right');
				$LeftOvers = $pdf->addTextWrap(313, $YPos, 50, $FontSize, number_format($Late, 2), 'right');
				$LeftOvers = $pdf->addTextWrap(354, $YPos, 50, $FontSize, number_format($Absent, 2), 'right');
				$LeftOvers = $pdf->addTextWrap(395, $YPos, 50, $FontSize, number_format($OT, 2), 'right');
				$LeftOvers = $pdf->addTextWrap(446, $YPos, 50, $FontSize, number_format($GrossPay, 2), 'right');
				$LeftOvers = $pdf->addTextWrap(487, $YPos, 50, $FontSize, number_format($SSS, 2), 'right');
				$LeftOvers = $pdf->addTextWrap(528, $YPos, 50, $FontSize, number_format($HDMF, 2), 'right');
				$LeftOvers = $pdf->addTextWrap(569, $YPos, 50, $FontSize, number_format($PH, 2), 'right');
				$LeftOvers = $pdf->addTextWrap(610, $YPos, 50, $FontSize, number_format($LoanDed, 2), 'right');
				$LeftOvers = $pdf->addTextWrap(671, $YPos, 50, $FontSize, number_format($Tax, 2), 'right');
				$LeftOvers = $pdf->addTextWrap(722, $YPos, 50, $FontSize, number_format($NetPay, 2), 'right');
				$YPos -= $line_height;
				if ($YPos < ($Bottom_Margin)) {
					include('includes/PDFPayRegYTDPageHeader.inc');
				}
			}
		}
	}

	$LeftOvers = $pdf->line($Page_Width - $Right_Margin, $YPos, $Left_Margin, $YPos);
	$YPos -= (2 * $line_height);
	$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 150, $FontSize, 'Grand Total');
	$LeftOvers = $pdf->addTextWrap(221, $YPos, 50, $FontSize, number_format($GTBasic, 2), 'right');
	$LeftOvers = $pdf->addTextWrap(272, $YPos, 50, $FontSize, number_format($GTOthInc, 2), 'right');
	$LeftOvers = $pdf->addTextWrap(313, $YPos, 50, $FontSize, number_format($GTLates, 2), 'right');
	$LeftOvers = $pdf->addTextWrap(354, $YPos, 50, $FontSize, number_format($GTAbsent, 2), 'right');
	$LeftOvers = $pdf->addTextWrap(395, $YPos, 50, $FontSize, number_format($GTOT, 2), 'right');
	$LeftOvers = $pdf->addTextWrap(446, $YPos, 50, $FontSize, number_format($GTGross, 2), 'right');
	$LeftOvers = $pdf->addTextWrap(487, $YPos, 50, $FontSize, number_format($GTSSS, 2), 'right');
	$LeftOvers = $pdf->addTextWrap(528, $YPos, 50, $FontSize, number_format($GTHDMF, 2), 'right');
	$LeftOvers = $pdf->addTextWrap(569, $YPos, 50, $FontSize, number_format($GTPhilHealth, 2), 'right');
	$LeftOvers = $pdf->addTextWrap(610, $YPos, 50, $FontSize, number_format($GTLoan, 2), 'right');
	$LeftOvers = $pdf->addTextWrap(671, $YPos, 50, $FontSize, number_format($GTTax, 2), 'right');
	$LeftOvers = $pdf->addTextWrap(722, $YPos, 50, $FontSize, number_format($GTNet, 2), 'right');

	$LeftOvers = $pdf->line($Page_Width - $Right_Margin, $YPos, $Left_Margin, $YPos);


	$pdfcode = $pdf->output();
	$len = strlen($pdfcode);
	if ($len <= 20) {
		$Title = _('YTD Payroll Register Error');
		include('includes/header.inc');
		echo '<p>';
		prnMsg(_('There were no entries to print out for the selections specified'));
		echo '<br /><a href="' . $RootPath . '/index.php?">' . _('Back to the menu') . '</a>';
		include('includes/footer.inc');
		exit;
	} else {
		header('Content-type: application/pdf');
		header('Content-Length: ' . $len);
		header('Content-Disposition: inline; filename=YTDPayrollReg.pdf');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		$pdf->Stream();

	}
	exit;

} elseif (isset($_POST['ShowPR'])) {
	include('includes/session.inc');
	$Title = _('PhilHealth Monthly Premium Listing');
	include('includes/header.inc');
	echo 'Use PrintPDF instead';
	echo "<br /><a href='" . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
	include('includes/footer.inc');
	exit;
} else {
	/*The option to print PDF was not hit */
	include('includes/session.inc');
	$Title = _('YTD Payroll Register');
	include('includes/header.inc');

	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
	echo '<table>';
	echo '</select></td></tr>';
	echo '<tr><td><align="centert"><b>' . _('FS Year') . ":<select name='FSYear'>";
	echo '<option selected="selected" value=0>' . _('Select One');
	for ($yy = 2006; $yy <= 2015; $yy++) {
		echo "<option value=$yy>$yy</option>\n";
	}
	echo '</select></td></tr>';

	echo "</table><p><input type='Submit' name='ShowPR' value='" . _('Show YTD Payroll Register') . "'>";
	echo "<p><input type='Submit' name='PrintPDF' value='" . _('PrintPDF') . "'>";

	include('includes/footer.inc');

}
/*end of else not PrintPDF */

?>