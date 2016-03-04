<?php

include('includes/DefineSerialItems.php');
include('includes/DefineStockTransfers.php');

include('includes/session.inc');
$Title = _('Transfer Controlled Items');

/* Session started in session.inc for password checking and authorisation level check */

include('includes/header.inc');

echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/inventory.png" title="' . _('Inventory') . '" alt="" /><b>' . $Title . '</b></p>';

$Identifier = $_GET['identifier'];

if (isset($_GET['NewTransfer'])) {
	unset($_SESSION['Transfer' . $Identifier]);
}
if (isset($_SESSION['Transfer' . $Identifier]) and $_SESSION['Transfer' . $Identifier]->TrfID == '') {
	unset($_SESSION['Transfer' . $Identifier]);
}

if (!isset($_SESSION['Transfer' . $Identifier])) {
	/* This page can only be called when a stock Transfer is pending */
	echo '<div class="centre"><a href="' . $RootPath . '/StockTransfers.php?NewTransfer=Yes">' . _('Enter A Stock Transfer') . '</a><br />';
	prnMsg(_('This page can only be opened if a Stock Transfer for a Controlled Item has been initiated'), 'error');
	echo '</div>';
	include('includes/footer.inc');
	exit;
}

if (isset($_GET['TransferItem'])) {
	$TransferItem = $_GET['TransferItem'];
	$_SESSION['TransferItem'] = $_GET['TransferItem'];
} elseif (isset($_SESSION['TransferItem'])) {
	$TransferItem = $_SESSION['TransferItem'];
}


/*Save some typing by referring to the line item class object in short form */
if (isset($TransferItem)) {
	/*we are in a bulk transfer */
	$LineItem =& $_SESSION['Transfer' . $Identifier]->TransferItem[$TransferItem];
} else {
	/*we are in an individual transfer */
	$LineItem =& $_SESSION['Transfer' . $Identifier]->TransferItem[0];
}

//Make sure this item is really controlled
if ($LineItem->Controlled != 1) {
	if (isset($TransferItem)) {
		echo '<div class="centre"><a href="' . $RootPath . '/StockLocTransferReceive.php>' . _('Receive A Stock Transfer') . '</a></div>';
	} else {
		echo '<div class="centre"><a href="' . $RootPath . '/StockTransfers.php?NewTransfer=Yes">' . _('Enter A Stock Transfer') . '</a></div>';
	}
	prnMsg(_('Notice') . ' - ' . _('The transferred item must be defined as controlled to require input of the batch numbers or serial numbers being transferred'), 'error');
	include('includes/footer.inc');
	exit;
}

echo '<div class="centre">';

if (isset($TransferItem)) {

	echo _('Transfer Items is set equal to') . ' ' . $TransferItem;

	echo '<a href="' . $RootPath . '/StockLocTransferReceive.php?identifier=' . urlencode($Identifier) . '&StockID=' . urlencode($LineItem->StockID) . '">' . _('Back To Transfer Screen') . '</a>';
} else {
	echo '<a href="' . $RootPath . '/StockTransfers.php?identifier=' . urlencode($Identifier) . '&StockID=' . urlencode($LineItem->StockID) . '">' . _('Back To Transfer Screen') . '</a>';
}

echo '<font size="2"><b>' . _('Transfer of controlled item') . ' ' . $LineItem->StockID . ' - ' . $LineItem->ItemDescription . '</b></font>
	</div>';

/** vars needed by InputSerialItem : **/
$LocationOut = $_SESSION['Transfer' . $Identifier]->StockLocationFrom;
$ItemMustExist = true;
$StockId = $LineItem->StockID;
$InOutModifier = 1;
$ShowExisting = true;
if (isset($TransferItem)) {
	$LineNo = $TransferItem;
} else {
	$LineNo = 0;
}
include('includes/InputSerialItems.php');

/*TotalQuantity set inside this include file from the sum of the bundles
of the item selected for adjusting */
$LineItem->Quantity = $TotalQuantity;

/*Also a multi select box for adding bundles to the Transfer without keying */

include('includes/footer.inc');
exit;
?>