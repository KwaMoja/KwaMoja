<?php

include('includes/session.inc');
$Title = _('Shipments Open Inquiry');
include('includes/header.inc');

echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/supplier.png" title="' . _('Supplier') . '" alt="" />' . ' ' . _('Open Shipments for') . ' ' . $_GET['SupplierName'] . '.</p>';

if (!isset($_GET['SupplierID']) or !isset($_GET['SupplierName'])) {
	echo '<br />';
	prnMsg(_('This page must be given the supplier code to look for shipments for'), 'error');
	include('includes/footer.inc');
	exit;
}

$SQL = "SELECT shiptref,
				vessel,
				eta,
				shipmentdate
			FROM shipments
			WHERE supplierid='" . $_GET['SupplierID'] . "'";
$ErrMsg = _('No shipments were returned from the database because') . ' - ' . DB_error_msg();
$ShiptsResult = DB_query($SQL, $ErrMsg);

if (DB_num_rows($ShiptsResult) == 0) {
	prnMsg(_('There are no open shipments currently set up for') . ' ' . $_GET['SupplierName'], 'warn');
	include('includes/footer.inc');
	exit;
}
/*show a table of the shipments returned by the SQL */

echo '<table cellpadding="2" class="selection">
		<tr>
			<th>' . _('Reference') . '</th>
			<th>' . _('Vessel') . '</th>
			<th>' . _('Shipment Date') . '</th>
			<th>' . _('ETA') . '</th>
		</tr>';

$k = 0; //row colour counter

while ($MyRow = DB_fetch_array($ShiptsResult)) {
	if ($k == 1) {
		echo '<tr class="OddTableRows">';
		$k = 0;
	} else {
		echo '<tr class="EvenTableRows">';
		$k = 1;
	}

	echo '<td><a href="' . $RootPath . '/Shipments.php?SelectedShipment=' . urlencode($MyRow['shiptref']) . '">' . $MyRow['shiptref'] . '</a></td>
			<td>' . $MyRow['vessel'] . '</td>
			<td>' . ConvertSQLDate($MyRow['shipmentdate']) . '</td>
			<td>' . ConvertSQLDate($MyRow['eta']) . '</td>
		</tr>';

}
//end of while loop

echo '</table>';

include('includes/footer.inc');

?>