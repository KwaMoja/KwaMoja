<?php
include ('includes/session.php');
$Title = _('Stock Location Transfer Docket Error');

include ('includes/PDFStarter.php');

if (isset($_POST['TransferNo'])) {
	$_GET['TransferNo'] = $_POST['TransferNo'];
}

if (!isset($_GET['TransferNo'])) {

	include ('includes/header.php');
	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Reprint transfer docket') . '</p><br />';
	echo '<form method="post" action="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>
			<tr>
				<td>' . _('Transfer docket to reprint') . '</td>
				<td><input type="text" class="number" required="required" maxlength="10" size="10" name="TransferNo" /></td>
			</tr>
		</table>';
	echo '<div class="centre">
			<input type="submit" name="Print" value="' . _('Print') . '" />
		  </div>';
	echo '</form>';
	include ('includes/footer.php');
	exit;
}

$PDF->addInfo('Title', _('Inventory Location Transfer BOL'));
$PDF->addInfo('Subject', _('Inventory Location Transfer BOL') . ' # ' . $_GET['TransferNo']);
$FontSize = 10;
$PageNumber = 1;
$line_height = 30;

$ErrMsg = _('An error occurred retrieving the items on the transfer') . '.' . '<p>' . _('This page must be called with a location transfer reference number') . '.';
$DbgMsg = _('The SQL that failed while retrieving the items on the transfer was');
$SQL = "SELECT loctransfers.reference,
			   loctransfers.stockid,
			   stockmaster.description,
			   loctransfers.shipqty,
			   loctransfers.recqty,
			   loctransfers.shipdate,
			   loctransfers.shiploc,
			   locations.locationname as shiplocname,
			   loctransfers.recloc,
			   locationsrec.locationname as reclocname,
			   stockmaster.decimalplaces
		FROM loctransfers
		INNER JOIN stockmaster
			ON loctransfers.stockid=stockmaster.stockid
		INNER JOIN locations
			ON loctransfers.shiploc=locations.loccode
		INNER JOIN locations AS locationsrec
			ON loctransfers.recloc = locationsrec.loccode
		INNER JOIN locationusers
			ON locationusers.loccode=locations.loccode
			AND locationusers.userid='" . $_SESSION['UserID'] . "'
			AND locationusers.canview=1
		INNER JOIN locationusers AS locationusersrec
			ON locationusersrec.loccode=locationsrec.loccode
			AND locationusersrec.userid='" . $_SESSION['UserID'] . "'
			AND locationusersrec.canview=1
		WHERE loctransfers.reference='" . $_GET['TransferNo'] . "'";

$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

if (DB_num_rows($Result) == 0) {

	include ('includes/header.php');
	prnMsg(_('The transfer reference selected does not appear to be set up') . ' - ' . _('enter the items to be transferred first'), 'error');
	include ('includes/footer.php');
	exit;
}

$TransferRow = DB_fetch_array($Result);

include ('includes/PDFStockLocTransferHeader.php');
$line_height = 30;
$FontSize = 10;

do {

	$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 100, $FontSize, $TransferRow['stockid'], 'left');
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 100, $YPos, 250, $FontSize, $TransferRow['description'], 'left');
	$LeftOvers = $PDF->addTextWrap($Page_Width - $Right_Margin - 100 - 100, $YPos, 100, $FontSize, locale_number_format($TransferRow['shipqty'], $TransferRow['decimalplaces']), 'right');
	$LeftOvers = $PDF->addTextWrap($Page_Width - $Right_Margin - 100, $YPos, 100, $FontSize, locale_number_format($TransferRow['recqty'], $TransferRow['decimalplaces']), 'right');

	$PDF->line($Left_Margin, $YPos - 2, $Page_Width - $Right_Margin, $YPos - 2);

	$YPos-= $line_height;

	if ($YPos < $Bottom_Margin + $line_height) {
		$PageNumber++;
		include ('includes/PDFStockLocTransferHeader.php');
	}

} while ($TransferRow = DB_fetch_array($Result));
$PDF->OutputD($_SESSION['DatabaseName'] . '_StockLocTrfShipment_' . date('Y-m-d') . '.pdf');
$PDF->__destruct();
?>