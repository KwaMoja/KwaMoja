<?php
/* $Id: PDFCOAHeader.inc  1 2014-09-15 06:31:08Z agaluski $ */

if ($PageNumber > 1) {
	$PDF->newPage();
}
$SectionHeading = 0;
$PDF->setFont('Helvetica', '');

$XPos = 65;
$YPos = 50;
$FontSize = 8;
$LineHeight = $FontSize * 1.25;
$PDF->SetLineWidth(1);
$PDF->line($XPos + 1, $YPos + $RectHeight, $XPos + 506, $YPos + $RectHeight);
$LeftOvers = $PDF->addTextWrap($XPos, $YPos, 500, $FontSize, $_SESSION['CompanyRecord']['coyname'] . ' | ' . $_SESSION['CompanyRecord']['regoffice4'] . ' | ' . $_SESSION['CompanyRecord']['telephone'], 'center');
$YPos -= $LineHeight;
$LeftOvers = $PDF->addTextWrap($XPos, $YPos, 500, $FontSize, $_SESSION['CompanyRecord']['regoffice1'] . $_SESSION['CompanyRecord']['regoffice2'], 'center');
$PDF->SetLineWidth(.2);

$YPos = 720;
$YPos -= $LineHeight;
$YPos -= $LineHeight;
$PDF->addJpegFromFile($_SESSION['LogoFile'], $XPos, $YPos, 0, 70);
$FontSize = 14;
$LineHeight = $FontSize * 1.50;
$YPos += $LineHeight;
$LeftOvers = $PDF->addTextWrap($XPos + 330, $YPos, 140, $FontSize, _('Certificate of Analysis'));
$YPos -= $LineHeight;
$YPos -= $LineHeight;
$PDF->setFont('', 'B');
$LeftOvers = $PDF->addTextWrap($XPos + 1, $YPos, 210, $FontSize, $Spec);
$PDF->setFont('', '');
$YPos -= $LineHeight;
$YPos -= $LineHeight;
$LeftOvers = $PDF->addTextWrap($XPos + 1, $YPos, 500, $FontSize, 'Certificate of Analysis for Lot' . ': ' . $SelectedCOA . '        ' . 'Date' . ': ' . $SampleDate, 'center');
$FontSize = 12;
$LineHeight = $FontSize * 1.25;
$YPos -= $LineHeight;
$YPos -= $LineHeight;
?>