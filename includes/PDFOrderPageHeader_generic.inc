<?php
/* $Id: PDFOrderPageHeader_generic.inc 5816 2013-02-07 07:54:41Z daintree $*/
/* pdf-php by R&OS code to set up a new sales order page */
if ($PageNumber > 1) {
	$PDF->newPage();
}

$XPos = $Page_Width / 2 - 60;
/* if the deliver blind flag is set on the order, we do not want to output
the company logo */
if ($DeliverBlind < 2) {
	$PDF->addJpegFromFile($_SESSION['LogoFile'], $XPos + 20, 520, 0, 60);
}
$FontSize = 18;

if ($Copy == 'Customer') {
	$PDF->addText($XPos - 20, 500, $FontSize, _('Packing Slip') . ' - ' . _('Customer Copy'));
} else {
	$PDF->addText($XPos - 20, 500, $FontSize, _('Packing Slip') . ' - ' . _('Office Copy'));
}

/* if the deliver blind flag is set on the order, we do not want to output
the company contact info */
if ($DeliverBlind < 2) {
	$FontSize = 14;
	$YPos = 480;
	$PDF->addText($XPos, $YPos, $FontSize, $_SESSION['CompanyRecord']['coyname']);
	$FontSize = 8;
	$PDF->addText($XPos, $YPos - 12, $FontSize, $_SESSION['CompanyRecord']['regoffice1']);
	$PDF->addText($XPos, $YPos - 21, $FontSize, $_SESSION['CompanyRecord']['regoffice2']);
	$PDF->addText($XPos, $YPos - 30, $FontSize, $_SESSION['CompanyRecord']['regoffice3'] . ' ' . $_SESSION['CompanyRecord']['regoffice4'] . ' ' . $_SESSION['CompanyRecord']['regoffice5']);
	$PDF->addText($XPos, $YPos - 39, $FontSize, _('Ph') . ': ' . $_SESSION['CompanyRecord']['telephone'] . ' ' . _('Fax') . ': ' . $_SESSION['CompanyRecord']['fax']);
	$PDF->addText($XPos, $YPos - 48, $FontSize, $_SESSION['CompanyRecord']['email']);
}

$XPos = 46;
$YPos = 566;

$FontSize = 14;
$PDF->addText($XPos, $YPos, $FontSize, _('Delivered To') . ':');
$PDF->addText($XPos, $YPos - 15, $FontSize, $MyRow['deliverto']);
$PDF->addText($XPos, $YPos - 30, $FontSize, $MyRow['deladd1']);
$PDF->addText($XPos, $YPos - 45, $FontSize, $MyRow['deladd2']);
$PDF->addText($XPos, $YPos - 60, $FontSize, $MyRow['deladd3'] . ' ' . $MyRow['deladd4'] . ' ' . $MyRow['deladd5'] . ' ' . $MyRow['deladd6']);

$YPos -= 80;

$PDF->addText($XPos, $YPos, $FontSize, _('Customer') . ':');
$PDF->addText($XPos, $YPos - 15, $FontSize, $MyRow['name']);
$PDF->addText($XPos, $YPos - 30, $FontSize, $MyRow['address1']);
$PDF->addText($XPos, $YPos - 45, $FontSize, $MyRow['address2']);
$PDF->addText($XPos, $YPos - 60, $FontSize, $MyRow['address3'] . ' ' . $MyRow['address4'] . ' ' . $MyRow['address5'] . ' ' . $MyRow['address6']);


$PDF->addText($XPos, $YPos - 82, $FontSize, _('Customer No.') . ' : ' . $MyRow['debtorno']);
$PDF->addText($XPos, $YPos - 100, $FontSize, _('Shipped by') . ' : ' . $MyRow['shippername']);

$LeftOvers = $PDF->addTextWrap($XPos, $YPos - 130, 170, $FontSize, _('Comments') . ':' . stripcslashes($MyRow['comments']));

if (mb_strlen($LeftOvers) > 1) {
	$LeftOvers = $PDF->addTextWrap($XPos, $YPos - 145, 170, $FontSize, $LeftOvers);
	if (mb_strlen($LeftOvers) > 1) {
		$LeftOvers = $PDF->addTextWrap($XPos, $YPos - 160, 170, $FontSize, $LeftOvers);
		if (mb_strlen($LeftOvers) > 1) {
			$LeftOvers = $PDF->addTextWrap($XPos, $YPos - 175, 170, $FontSize, $LeftOvers);
			if (mb_strlen($LeftOvers) > 1) {
				$LeftOvers = $PDF->addTextWrap($XPos, $YPos - 180, 170, $FontSize, $LeftOvers);
			}
		}
	}
}

$PDF->addText(620, 560, $FontSize, _('Order No') . ':');
$PDF->addText(700, 560, $FontSize, $_GET['TransNo']);
$PDF->addText(620, 560 - 15, $FontSize, _('Your Ref') . ':');
$PDF->addText(700, 560 - 15, $FontSize, $MyRow['customerref']);
$PDF->addText(620, 560 - 45, $FontSize, _('Order Date') . ':');
$PDF->addText(700, 560 - 45, $FontSize, ConvertSQLDate($MyRow['orddate']));
$PDF->addText(620, 560 - 60, $FontSize, _('Printed') . ': ');
$PDF->addText(700, 560 - 60, $FontSize, Date($_SESSION['DefaultDateFormat']));
$PDF->addText(620, 560 - 75, $FontSize, _('From') . ': ');
$PDF->addText(700, 560 - 75, $FontSize, $MyRow['locationname']);
$PDF->addText(620, 560 - 90, $FontSize, _('Page') . ':');
$PDF->addText(700, 560 - 90, $FontSize, $PageNumber);

$YPos -= 170;
$XPos = 15;

$header_line_height = $line_height + 25;

$LeftOvers = $PDF->addTextWrap($XPos, $YPos, 127, $FontSize, _('Item Code'), 'left');
$LeftOvers = $PDF->addTextWrap(147, $YPos, 255, $FontSize, _('Item Description'), 'left');
$LeftOvers = $PDF->addTextWrap(400, $YPos, 85, $FontSize, _('Ord Quantity'), 'right');
$LeftOvers = $PDF->addTextWrap(487, $YPos, 70, $FontSize, _('Bin Locn'), 'center');
$LeftOvers = $PDF->addTextWrap(573, $YPos, 85, $FontSize, _('This Delivery'), 'right');
$LeftOvers = $PDF->addTextWrap(672, $YPos, 85, $FontSize, _('Prev Delivery'), 'right');

$YPos -= $line_height;

$FontSize = 12;

?>