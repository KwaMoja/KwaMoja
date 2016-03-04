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
	$pdf->addinfo('Title', _('Alphalist'));
	$pdf->addinfo('Subject', _('Alphalist'));


	$PageNumber = 1;
	$line_height = 12;

	$PageNumber = 0;
	$FontSize = 10;
	$pdf->addinfo('Title', _('Alphalist'));
	$pdf->addinfo('Subject', _('Alphalist'));
	$line_height = 12;
	include('includes/PDFTaxYTDPageHeader.inc');
	//list of all employees
	$SQL = "SELECT employeeid
			FROM prlemployeemaster
			WHERE prlemployeemaster.employeeid<>''";
	$EmpListResult = DB_query($SQL, _('Could not test to see that all detail records properly initiated'));
	if (DB_num_rows($EmpListResult) > 0) {
		while ($emprow = DB_fetch_array($EmpListResult)) {
			$k = 0; //row colour counter
			$SQL = "SELECT sum(taxableincome) AS Gross,sum(tax) AS Tax
					FROM prlemptaxfile
					WHERE prlemptaxfile.employeeid='" . $emprow['employeeid'] . "'
					AND prlemptaxfile.fsyear='" . $FSYear . "'";
			$PayResult = DB_query($SQL);
			if (DB_num_rows($PayResult) > 0) {
				$MyRow = DB_fetch_array($PayResult);
				$EmpID = $emprow['employeeid'];
				$TaxNumber = GetEmpRow($EmpID, 23);
				$TaxID = GetEmpRow($EmpID, 35);
				$FullName = GetName($EmpID);
				$MyExemption = GetTaxStatusRow(GetEmpRow($EmpID, 35), 4);
				$Gross = $MyRow['Gross'];
				$NetTaxable = $MyRow['Gross'] - $MyExemption;
				$TaxWithheld = $MyRow['Tax'];
				$MyTax = GetMyTax($NetTaxable);
				$Refund = $MyTax - $TaxWithheld;
				$GTNetTaxable += $NetTaxable;
				$GTMyTax += $MyTax;
				$GTTaxWithheld += $TaxWithheld;
				$GTRefund += $Refund;

				//$YPos -= (2 * $line_height);  //double spacing
				$FontSize = 8;
				$pdf->selectFont('./fonts/Helvetica.afm');
				$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 120, $FontSize, $FullName, 'left');
				$LeftOvers = $pdf->addTextWrap(150, $YPos, 60, $FontSize, $TaxNumber, 'right');
				$LeftOvers = $pdf->addTextWrap(220, $YPos, 60, $FontSize, $TaxID, 'right');
				$LeftOvers = $pdf->addTextWrap(290, $YPos, 60, $FontSize, number_format($Gross, 2), 'right');
				$LeftOvers = $pdf->addTextWrap(360, $YPos, 60, $FontSize, number_format($MyExemption, 2), 'right');
				$LeftOvers = $pdf->addTextWrap(430, $YPos, 60, $FontSize, number_format($NetTaxable, 2), 'right');
				$LeftOvers = $pdf->addTextWrap(500, $YPos, 60, $FontSize, number_format($MyTax, 2), 'right');
				$LeftOvers = $pdf->addTextWrap(570, $YPos, 60, $FontSize, number_format($TaxWithheld, 2), 'right');
				$LeftOvers = $pdf->addTextWrap(660, $YPos, 60, $FontSize, number_format($Refund, 2), 'right');
				$YPos -= $line_height;
				if ($YPos < ($Bottom_Margin)) {
					include('includes/PDFTaxYTDPageHeader.inc');
				}
			}
		}
	}

	$LeftOvers = $pdf->line($Page_Width - $Right_Margin, $YPos, $Left_Margin, $YPos);
	$YPos -= (2 * $line_height);
	$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 150, $FontSize, 'Grand Total');
	$LeftOvers = $pdf->addTextWrap(500, $YPos, 60, $FontSize, number_format($GTMyTax, 2), 'right');
	$LeftOvers = $pdf->addTextWrap(570, $YPos, 60, $FontSize, number_format($GTTaxWithheld, 2), 'right');
	$LeftOvers = $pdf->addTextWrap(660, $YPos, 60, $FontSize, number_format($GTRefund, 2), 'right');
	$LeftOvers = $pdf->line($Page_Width - $Right_Margin, $YPos, $Left_Margin, $YPos);


	$pdfcode = $pdf->output();
	$len = strlen($pdfcode);
	if ($len <= 20) {
		$Title = _('Alphalist error');
		include('includes/header.inc');
		echo '<p>';
		prnMsg(_('There were no entries to print out for the selections specified'));
		echo '<br /><a href="' . $RootPath . '/index.php?">' . _('Back to the menu') . '</a>';
		include('includes/footer.inc');
		exit;
	} else {
		header('Content-type: application/pdf');
		header('Content-Length: ' . $len);
		header('Content-Disposition: inline; filename=Alphalist.pdf');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		$pdf->Stream();

	}
	exit;

} elseif (isset($_POST['ShowPR'])) {
	include('includes/session.inc');
	$Title = _('Alphalist');
	include('includes/header.inc');
	echo 'Use PrintPDF instead';
	echo "<br /><a href='" . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
	include('includes/footer.inc');
	exit;
} else {
	/*The option to print PDF was not hit */
	include('includes/session.inc');
	$Title = _('Alphalist');
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
	echo "</table><p><input type='Submit' name='ShowPR' value='" . _('Show Alpalist') . "'>";
	echo "<p><input type='Submit' name='PrintPDF' value='" . _('PrintPDF') . "'>";
	include('includes/footer.inc');

}
/*end of else not PrintPDF */

?>