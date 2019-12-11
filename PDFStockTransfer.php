<?php
/* This script is superseded by the PDFStockLocTransfer.php which produces a multiple item stock transfer listing - this was for the old individual stock transfers where there is just single items being transferred */

include ('includes/session.php');

if (!isset($_GET['TransferNo'])) {
	if (isset($_POST['TransferNo'])) {
		if (is_numeric($_POST['TransferNo'])) {
			$_GET['TransferNo'] = $_POST['TransferNo'];
		} else {
			prnMsg(_('The entered transfer reference is expected to be numeric'), 'error');
			unset($_POST['TransferNo']);
		}
	}
	if (!isset($_GET['TransferNo'])) { //still not set from a post then
		//open a form for entering a transfer number
		$Title = _('Print Stock Transfer');
		include ('includes/header.php');
		echo '<p class="page_title_text">
				<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/printer.png" title="', _('Print Transfer Note'), '" alt="" />', $Title, '
			</p>';

		echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post" id="form">';
		echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

		echo '<fieldset>
				<legend>', _('Report Criteria'), '</legend>
				<field>
					<label for="TransferNo">', _('Print Stock Transfer Note'), ' :</label>
					<input type="text" class="number"  name="TransferNo" required="required" maxlength="10" size="11" />
				</field>
			</fieldset>';

		echo '<div class="centre">
				<input type="submit" name="Process" value="', _('Print Transfer Note'), '" />
			</div>
		</form>';

		include ('includes/footer.php');
		exit();
	}
}

include ('includes/PDFStarter.php');
$PDF->addInfo('Title', _('Stock Transfer Form'));
$PageNumber = 1;
$line_height = 12;

include ('includes/PDFStockTransferHeader.php');

/*Print out the category totals */
$SQL = "SELECT stockmoves.stockid,
				description,
				transno,
				stockmoves.loccode,
				locationname,
				trandate,
				qty,
				reference
			FROM stockmoves
			INNER JOIN stockmaster
				ON stockmoves.stockid=stockmaster.stockid
			INNER JOIN locations
				ON stockmoves.loccode=locations.loccode
			INNER JOIN locationusers
				ON locationusers.loccode=locations.loccode
				AND locationusers.userid='" . $_SESSION['UserID'] . "'
				AND locationusers.canview=1
			WHERE transno='" . $_GET['TransferNo'] . "'
				AND qty < 0
				AND type=16";

$Result = DB_query($SQL);
if (DB_num_rows($Result) == 0) {
	$Title = _('Print Stock Transfer - Error');
	include ('includes/header.php');
	prnMsg(_('There was no transfer found at your location with number') . ': ' . $_GET['TransferNo'], 'error');
	echo '<div class="centre">
			<a href="PDFStockTransfer.php">', _('Try Again'), '</a>
		</div>';
	include ('includes/footer.php');
	exit;
}
//get the first stock movement which will be the quantity taken from the initiating location
while ($MyRow = DB_fetch_array($Result)) {
	$StockId = $MyRow['stockid'];
	$From = $MyRow['locationname'];
	$Date = $MyRow['trandate'];
	$To = $MyRow['reference'];
	$Quantity = - $MyRow['qty'];
	$Description = $MyRow['description'];

	$LeftOvers = $PDF->addTextWrap($Left_Margin + 1, $YPos - 10, 300 - $Left_Margin, $FontSize, $StockId);
	/*resmoart mods*/
	/*$LeftOvers = $PDF->addTextWrap($Left_Margin+75,$YPos-10,300-$Left_Margin,$FontSize-2, $Description);*/
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 75, $YPos - 10, 300 - $Left_Margin, $FontSize, $Description);
	/*resmart ends*/
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 250, $YPos - 10, 300 - $Left_Margin, $FontSize, $From);
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 350, $YPos - 10, 300 - $Left_Margin, $FontSize, $To);
	$LeftOvers = $PDF->addTextWrap($Left_Margin + 475, $YPos - 10, 300 - $Left_Margin, $FontSize, $Quantity);

	$YPos = $YPos - $line_height;

	if ($YPos < $Bottom_Margin + $line_height) {
		include ('includes/PDFStockTransferHeader.php');
	}
	/*resmart mods*/
	$SQL = "SELECT stockmaster.controlled
			FROM stockmaster WHERE stockid ='" . $StockId . "'";
	$CheckControlledResult = DB_query($SQL, '<br />' . _('Could not determine if the item was controlled or not because') . ' ');
	$ControlledRow = DB_fetch_row($CheckControlledResult);

	if ($ControlledRow[0] == 1) {
		/*Then its a controlled item */
		$SQL = "SELECT stockserialmoves.serialno,
						stockserialmoves.moveqty
					FROM stockmoves
					INNER JOIN stockserialmoves
						ON stockmoves.stkmoveno= stockserialmoves.stockmoveno
					WHERE stockmoves.stockid='" . $StockId . "'
						AND stockmoves.type =16
						AND qty > 0
						AND stockmoves.transno='" . $_GET['TransferNo'] . "'";
		$GetStockMoveResult = DB_query($SQL, _('Could not retrieve the stock movement reference number which is required in order to retrieve details of the serial items that came in with this GRN'));
		while ($SerialStockMoves = DB_fetch_array($GetStockMoveResult)) {
			$LeftOvers = $PDF->addTextWrap($Left_Margin + 40, $YPos - 10, 300 - $Left_Margin, $FontSize, _('Lot/Serial') . ': ');
			$LeftOvers = $PDF->addTextWrap($Left_Margin + 75, $YPos - 10, 300 - $Left_Margin, $FontSize, $SerialStockMoves['serialno']);
			$LeftOvers = $PDF->addTextWrap($Left_Margin + 250, $YPos - 10, 300 - $Left_Margin, $FontSize, $SerialStockMoves['moveqty']);
			$YPos = $YPos - $line_height;

			if ($YPos < $Bottom_Margin + $line_height) {
				include ('includes/PDFStockTransferHeader.php');
			} //while SerialStockMoves
			
		}
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 40, $YPos - 10, 300 - $Left_Margin, $FontSize, ' ');
		$YPos = $YPos - $line_height;
		if ($YPos < $Bottom_Margin + $line_height) {
			include ('includes/PDFStockTransferHeader.php');
		} //controlled item*/
		
	}
	/*resmart ends*/
}
$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos - 70, 300 - $Left_Margin, $FontSize, _('Date of transfer') . ':' . $Date);

$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos - 120, 300 - $Left_Margin, $FontSize, _('Signed for') . $From . '______________________');
$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos - 160, 300 - $Left_Margin, $FontSize, _('Signed for') . $To . '______________________');

$PDF->OutputD($_SESSION['DatabaseName'] . '_StockTransfer_' . date('Y-m-d') . '.pdf');
$PDF->__destruct();
?>