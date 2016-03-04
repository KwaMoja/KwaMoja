<?php

if ($PageNumber > 1) {
	$PDF->newPage();
}

$XPos = 55;
$YPos = 575;

$PDF->addText($XPos, $YPos, $FontSize, $MyRow['deliverto']);
$PDF->addText($XPos, $YPos - 13, $FontSize, $MyRow['deladd1']);
$PDF->addText($XPos, $YPos - 26, $FontSize, $MyRow['deladd2']);
$PDF->addText($XPos, $YPos - 39, $FontSize, $MyRow['deladd3'] . ' ' . $MyRow['deladd4'] . ' ' . $MyRow['deladd5'] . ' ' . $MyRow['deladd6']);

$YPos = 510;

$PDF->addText($XPos, $YPos, $FontSize, $MyRow['name']);
$PDF->addText($XPos, $YPos - 13, $FontSize, $MyRow['address1']);
$PDF->addText($XPos, $YPos - 26, $FontSize, $MyRow['address2']);
$PDF->addText($XPos, $YPos - 39, $FontSize, $MyRow['address3'] . ' ' . $MyRow['address4'] . ' ' . $MyRow['address5'] . ' ' . $MyRow['deladd6']);


/*Print Dispatch Date - as current date
$XPos=50;
$YPos=98;
$PDF->addText($XPos, $YPos,$FontSize, Date($_SESSION['DefaultDateFormat']));

*/


/*Print the freight company to be used */
$XPos = 65;
$YPos = 48;
$PDF->addText($XPos, $YPos, $FontSize, $MyRow['shippername']);

$XPos = 630;
$YPos = 567;
$PDF->addText($XPos, $YPos, $FontSize, _('Order No') . ': ' . $_GET['TransNo']);
$PDF->addText($XPos, $YPos - 14, $FontSize, _('Your Ref') . ': ' . $MyRow['customerref']);


$XPos = 687;
$YPos = 539;
$PDF->addText($XPos, $YPos, $FontSize, ConvertSQLDate($MyRow['orddate']));

$XPos = 630;
$YPos = 525;
$PDF->addText($XPos, $YPos, $FontSize, _('Cust') . ': ' . $MyRow['debtorno']);
$PDF->addText($XPos, $YPos - 14, $FontSize, _('Branch') . ': ' . $MyRow['branchcode']);

$PDF->addText($XPos, $YPos - 32, $FontSize, _('Page') . ': ' . $PageNumber);

$PDF->addText($XPos, $YPos - 46, $FontSize, _('From') . ': ' . $MyRow['locationname']);


/*Print the order number */
$XPos = 510;
$YPos = 96;
$PDF->addText($XPos, $YPos, $FontSize, $_GET['TransNo']);


$XPos = 609;
$YPos = 96;
$LeftOvers = $PDF->addTextWrap($XPos, $YPos, 170, $FontSize, stripcslashes($MyRow['comments']));

if (mb_strlen($LeftOvers) > 1) {
	$LeftOvers = $PDF->addTextWrap($XPos, $YPos - 14, 170, $FontSize, $LeftOvers);
	if (mb_strlen($LeftOvers) > 1) {
		$LeftOvers = $PDF->addTextWrap($XPos, $YPos - 28, 170, $FontSize, $LeftOvers);
		if (mb_strlen($LeftOvers) > 1) {
			$LeftOvers = $PDF->addTextWrap($XPos, $YPos - 42, 170, $FontSize, $LeftOvers);
			if (mb_strlen($LeftOvers) > 1) {
				$LeftOvers = $PDF->addTextWrap($XPos, $YPos - 56, 170, $FontSize, $LeftOvers);
			}
		}
	}
}

$YPos = 414;

?>