<?php

/*	Please note that addTextWrap() prints a font-size-height further down than
addText() and other functions. Use addText() instead of addTextWrap() to
print left aligned elements.*/

if (!$FirstPage) {
	/* only initiate a new page if its not the first */
	$PDF->newPage();
}

$YPos = $Page_Height - $Top_Margin;

$PDF->addJpegFromFile($_SESSION['LogoFile'], $Page_Width / 2 - 120, $YPos - 40, 0, 60);
$FontSize = 15;
if ($InvOrCredit == 'Invoice') {

	$PDF->addText($Page_Width - 200, $YPos, $FontSize, _('TAX INVOICE') . ' ');
} else {
	$PDF->addText($Page_Width - 200, $YPos, $FontSize, _('CREDIT NOTE') . ' ');
}

$XPos = $Page_Width - 265;
$YPos -= 111;
/*draw a nice curved corner box around the billing details */
/*from the top right */
$PDF->partEllipse($XPos + 225, $YPos + 100, 0, 90, 10, 10);
/*line to the top left */
$PDF->line($XPos + 225, $YPos + 110, $XPos, $YPos + 110);
/*Dow top left corner */
$PDF->partEllipse($XPos, $YPos + 100, 90, 180, 10, 10);
/*Do a line to the bottom left corner */
$PDF->line($XPos - 10, $YPos + 100, $XPos - 10, $YPos + 5);
/*Now do the bottom left corner 180 - 270 coming back west*/
$PDF->partEllipse($XPos, $YPos + 5, 180, 270, 10, 10);
/*Now a line to the bottom right */
$PDF->line($XPos, $YPos - 5, $XPos + 225, $YPos - 5);
/*Now do the bottom right corner */
$PDF->partEllipse($XPos + 225, $YPos + 5, 270, 360, 10, 10);
/*Finally join up to the top right corner where started */
$PDF->line($XPos + 235, $YPos + 5, $XPos + 235, $YPos + 100);

$YPos = $Page_Height - $Top_Margin - 10;

$FontSize = 10;

$PDF->addText($Page_Width - 268, $YPos - 13, $FontSize, _('Number'));
$PDF->addText($Page_Width - 180, $YPos - 13, $FontSize, $FromTransNo);
$PDF->addText($Page_Width - 268, $YPos - 26, $FontSize, _('Customer Code'));
$PDF->addText($Page_Width - 180, $YPos - 26, $FontSize, $MyRow['debtorno'] . ' ' . _('Branch') . ' ' . $MyRow['branchcode']);
$PDF->addText($Page_Width - 268, $YPos - 39, $FontSize, _('Date'));
$PDF->addText($Page_Width - 180, $YPos - 39, $FontSize, ConvertSQLDate($MyRow['trandate']));


if ($InvOrCredit == 'Invoice') {

	$PDF->addText($Page_Width - 268, $YPos - 52, $FontSize, _('Order No'));
	$PDF->addText($Page_Width - 180, $YPos - 52, $FontSize, $MyRow['orderno']);
	$PDF->addText($Page_Width - 268, $YPos - 65, $FontSize, _('Order Date'));
	$PDF->addText($Page_Width - 180, $YPos - 65, $FontSize, ConvertSQLDate($MyRow['orddate']));
	$PDF->addText($Page_Width - 268, $YPos - 78, $FontSize, _('Dispatch Detail'));
	$PDF->addText($Page_Width - 180, $YPos - 78, $FontSize, $MyRow['shippername'] . '-' . $MyRow['consignment']);
	$PDF->addText($Page_Width - 268, $YPos - 91, $FontSize, _('Dispatched From'));
	$PDF->addText($Page_Width - 180, $YPos - 91, $FontSize, $MyRow['locationname']);
}


$PDF->addText($Page_Width - 268, $YPos - 108, $FontSize, _('Page'));
$PDF->addText($Page_Width - 180, $YPos - 108, $FontSize, $PageNumber);

/*End of the text in the right side box */

/*Now print out the company name and address in the middle under the logo */
$XPos = $Page_Width / 2 - 90;
$YPos = $Page_Height - $Top_Margin - 60;
$PDF->addText($XPos, $YPos, $FontSize, $_SESSION['CompanyRecord']['coyname']);

$FontSize = 8;
$PDF->addText($XPos, $YPos - 10, $FontSize, $_SESSION['TaxAuthorityReferenceName'] . ': ' . $_SESSION['CompanyRecord']['gstno']);
$PDF->addText($XPos, $YPos - 19, $FontSize, $_SESSION['CompanyRecord']['regoffice1']);
$PDF->addText($XPos, $YPos - 28, $FontSize, $_SESSION['CompanyRecord']['regoffice2']);
$PDF->addText($XPos, $YPos - 37, $FontSize, $_SESSION['CompanyRecord']['regoffice3'] . ' ' . $_SESSION['CompanyRecord']['regoffice4'] . ' ' . $_SESSION['CompanyRecord']['regoffice5']);
$PDF->addText($XPos, $YPos - 46, $FontSize, $_SESSION['CompanyRecord']['regoffice6']);
$PDF->addText($XPos, $YPos - 54, $FontSize, _('Phone') . ':' . $_SESSION['CompanyRecord']['telephone'] . ' ' . _('Fax') . ': ' . $_SESSION['CompanyRecord']['fax']);
$PDF->addText($XPos, $YPos - 63, $FontSize, _('Email') . ': ' . $_SESSION['CompanyRecord']['email']);

/*Now the customer charged to details top left */

$XPos = $Left_Margin;
$YPos = $Page_Height - $Top_Margin;

$FontSize = 10;

$PDF->addText($XPos, $YPos, $FontSize, _('Sold To') . ':');
$XPos += 80;

if ($MyRow['invaddrbranch'] == 0) {
	$PDF->addText($XPos, $YPos, $FontSize, html_entity_decode($MyRow['name']));
	$PDF->addText($XPos, $YPos - 14, $FontSize, html_entity_decode($MyRow['address1']));
	$PDF->addText($XPos, $YPos - 28, $FontSize, html_entity_decode($MyRow['address2']));
	$PDF->addText($XPos, $YPos - 42, $FontSize, html_entity_decode($MyRow['address3']) . ' ' . html_entity_decode($MyRow['address4']));
} else {
	$PDF->addText($XPos, $YPos, $FontSize, html_entity_decode($MyRow['name']));
	$PDF->addText($XPos, $YPos - 14, $FontSize, html_entity_decode($MyRow['brpostaddr1']));
	$PDF->addText($XPos, $YPos - 28, $FontSize, html_entity_decode($MyRow['brpostaddr2']));
	$PDF->addText($XPos, $YPos - 42, $FontSize, html_entity_decode($MyRow['brpostaddr3']) . ' ' . html_entity_decode($MyRow['brpostaddr4']) . ' ' . html_entity_decode($MyRow['brpostaddr5']) . ' ' . html_entity_decode($MyRow['brpostaddr6']));
}


$XPos -= 80;
$YPos -= ($line_height * 4);

if ($InvOrCredit == 'Invoice') {

	$PDF->addText($XPos, $YPos, $FontSize, _('Delivered To') . ':');
	$XPos += 80;
	// Before trying to call htmlspecialchars_decode, check that its supported, if not substitute a compatible version
	if (!function_exists('htmlspecialchars_decode')) {
		function htmlspecialchars_decode($str) {
			$trans = get_html_translation_table(HTML_SPECIALCHARS);

			$decode = ARRAY();
			foreach ($trans AS $char => $entity) {
				$decode[$entity] = $char;
			}

			$str = strtr($str, $decode);

			return $str;
		}
	}
	$PDF->addText($XPos, $YPos, $FontSize, html_entity_decode($MyRow['deliverto']));
	$PDF->addText($XPos, $YPos - 14, $FontSize, html_entity_decode($MyRow['deladd1']));
	$PDF->addText($XPos, $YPos - 28, $FontSize, html_entity_decode($MyRow['deladd2']));
	$PDF->addText($XPos, $YPos - 42, $FontSize, html_entity_decode($MyRow['deladd3']) . ' ' . html_entity_decode($MyRow['deladd4']) . ' ' . html_entity_decode($MyRow['deladd5']));
	$XPos -= 80;
}
if ($InvOrCredit == 'Credit') {
	/* then its a credit note */

	$PDF->addText($XPos, $YPos, $FontSize, _('Charge Branch') . ':');
	$XPos += 80;
	$PDF->addText($XPos, $YPos, $FontSize, html_entity_decode($MyRow['brname']));
	$PDF->addText($XPos, $YPos - 14, $FontSize, html_entity_decode($MyRow['braddress1']));
	$PDF->addText($XPos, $YPos - 28, $FontSize, html_entity_decode($MyRow['braddress2']));
	$PDF->addText($XPos, $YPos - 42, $FontSize, html_entity_decode($MyRow['braddress3']) . ' ' . html_entity_decode($MyRow['braddress4']) . ' ' . html_entity_decode($MyRow['braddress5']) . ' ' . html_entity_decode($MyRow['braddress6']));
	$XPos -= 80;
}

$XPos = $Left_Margin;

$YPos = $Page_Height - $Top_Margin - 80;
/*draw a line under the company address and charge to address
$PDF->line($XPos, $YPos,$Right_Margin, $YPos); */

$XPos = $Page_Width / 2;

$XPos = $Left_Margin;
$YPos -= ($line_height * 2);

$PDF->addText($Left_Margin, $YPos - 8, $FontSize, _('All amounts stated in') . ': ' . $MyRow['currcode']);

/*draw a box with nice round corner for entering line items */
/*90 degree arc at top right of box 0 degrees starts a bottom */
$PDF->partEllipse($Page_Width - $Right_Margin - 10, $Bottom_Margin + 390, 0, 90, 10, 10);
/*line to the top left */
$PDF->line($Page_Width - $Right_Margin - 10, $Bottom_Margin + 400, $Left_Margin + 10, $Bottom_Margin + 400);
/*Dow top left corner */
$PDF->partEllipse($Left_Margin + 10, $Bottom_Margin + 390, 90, 180, 10, 10);
/*Do a line to the bottom left corner */
$PDF->line($Left_Margin, $Bottom_Margin + 390, $Left_Margin, $Bottom_Margin + 10);
/*Now do the bottom left corner 180 - 270 coming back west*/
$PDF->partEllipse($Left_Margin + 10, $Bottom_Margin + 10, 180, 270, 10, 10);
/*Now a line to the bottom right */
$PDF->line($Left_Margin + 10, $Bottom_Margin, $Page_Width - $Right_Margin - 10, $Bottom_Margin);
/*Now do the bottom right corner */
$PDF->partEllipse($Page_Width - $Right_Margin - 10, $Bottom_Margin + 10, 270, 360, 10, 10);
/*Finally join up to the top right corner where started */
$PDF->line($Page_Width - $Right_Margin, $Bottom_Margin + 10, $Page_Width - $Right_Margin, $Bottom_Margin + 390);


$YPos -= ($line_height * 2);
/*Set up headings */
$FontSize = 10;
$PDF->addText($Left_Margin + 2, $YPos, $FontSize, _('Customer Tax Ref') . ':');
$PDF->addText($Left_Margin + 100, $YPos, $FontSize, $MyRow['taxref']);


/*Print a vertical line */
$PDF->line($Left_Margin + 248, $YPos - 10 + $line_height + 3, $Left_Margin + 248, $YPos - 18);
if ($InvOrCredit == 'Invoice') {
	$PDF->addText($Left_Margin + 252, $YPos, $FontSize, _('Customer Order Ref.') . ':');
	$PDF->addText($Left_Margin + 370, $YPos, $FontSize, $MyRow['customerref']);
}
/*Print a vertical line */
$PDF->line($Left_Margin + 450, $YPos + $line_height - 7, $Left_Margin + 450, $YPos - 18);

$PDF->addText($Left_Margin + 453, $YPos, $FontSize, _('Sales Person') . ':');
$PDF->addText($Left_Margin + 510, $YPos, $FontSize, $MyRow['salesmanname']);

$YPos -= 8;
/*draw a line */
$PDF->line($XPos, $YPos - 10, $Page_Width - $Right_Margin, $YPos - 10);

$YPos -= 12;

$TopOfColHeadings = $YPos - 10;

$PDF->addText($Left_Margin + 5, $YPos, $FontSize, _('Item Code'));
$PDF->addText($Left_Margin + 100, $YPos, $FontSize, _('Description'));
$PDF->addText($Left_Margin + 382, $YPos, $FontSize, _('Unit Price'));
$PDF->addText($Left_Margin + 485, $YPos, $FontSize, _('Quantity'));
$PDF->addText($Left_Margin + 555, $YPos, $FontSize, _('UOM'));
$PDF->addText($Left_Margin + 595, $YPos, $FontSize, _('Discount'));
$PDF->addText($Left_Margin + 690, $YPos, $FontSize, _('Extended Price'));

$YPos -= 8;

/*draw a line */
$PDF->line($XPos, $YPos - 5, $Page_Width - $Right_Margin, $YPos - 5);

$YPos -= ($line_height);

?>