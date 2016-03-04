<?php
/* $Id: PDFProdSpecHeader.inc  1 2014-09-15 06:31:08Z agaluski $ */

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
$LeftOvers = $PDF->addTextWrap($XPos + 330, $YPos, 140, $FontSize, _('Technical Data Sheet'));
$YPos -= $LineHeight;
$YPos -= $LineHeight;
$PDF->setFont('', 'B');
$Offset= array_sum($PDF->GetStringWidth(($Spec), '', '', 0, true)) + 2;
$LeftOvers = $PDF->addTextWrap($XPos + 1, $YPos, $Offset, $FontSize, $Spec);
$PDF->setFont('', '');
$LeftOvers = $PDF->addTextWrap($XPos + $Offset, $YPos, 500 - $Offset, $FontSize, '- ' . $SpecDesc);
while (mb_strlen($LeftOvers) > 1) {
	$YPos -= $LineHeight;
	$LeftOvers = $PDF->addTextWrap($XPos + 60, $YPos, 445, $FontSize, $LeftOvers, 'left');
}
$FontSize = 12;
$LineHeight = $FontSize * 1.25;
$YPos -= $LineHeight;
$YPos -= $LineHeight;
?>