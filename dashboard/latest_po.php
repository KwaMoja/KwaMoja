<?php
$PageSecurity = 0;
$PathPrefix = '../';
include('../includes/session.inc');

$RootPath = '../';

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>Dashboard</title>';
echo '<link rel="shortcut icon" href="' . $RootPath . '/favicon.ico" />';
echo '<link rel="icon" href="' . $RootPath . '/favicon.ico" />';

echo '<meta http-equiv="Content-Type" content="application/html; charset=utf-8" />';
echo '<meta http-equiv="refresh" content="600">';

echo '<link href="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/default.css" rel="stylesheet" type="text/css" />';
echo '<script type="text/javascript" src = "' . $RootPath . '/javascripts/MiscFunctions.js"></script>';
echo '<style media="screen">
			.noPrint{ display: block; }
			.yesPrint{ display: block !important; }
		</style>
		<style media="print">
			.noPrint{ display: none; }
			.yesPrint{ display: block !important; }
		</style>';

echo '</head><body style="background:transparent;">';

switch ($_SESSION['ScreenFontSize']) {
	case 0:
		$FontSize = '8pt';
		break;
	case 1:
		$FontSize = '10pt';
		break;
	case 2:
		$FontSize = '12pt';
		break;
	default:
		$FontSize = '10pt';
}
echo '<style>
			body {
					font-size: ' . $FontSize . ';
				}
			</style>';

$SQL = "SELECT id FROM dashboard_scripts WHERE scripts='" . basename($_SERVER['PHP_SELF']) . "'";
$Result = DB_query($SQL);
$MyRow = DB_fetch_array($Result);

echo '<table style="max-width:100%;width:99%;" cellspacing="0" cellpadding="1" border="0">
		<tr>
			<th colspan="5" style="margin:0px;padding:0px;background: transparent;">
				<div class="CanvasTitle">' . _('Latest purchase orders') . '
					<a href="' . $RootPath . 'Dashboard.php?Remove=' . urlencode($MyRow['id']) . '" target="_parent" id="CloseButton">X</a>
				</div>
			</th>
		</tr>';
$SQL = 'SELECT purchorders.orderno,
				suppliers.suppname,
				purchorders.orddate,
				purchorders.deliverydate,
				purchorders.initiator,
				purchorders.requisitionno,
				purchorders.allowprint,
				purchorders.status,
				suppliers.currcode,
				currencies.decimalplaces AS currdecimalplaces,
				SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
			FROM purchorders
			INNER JOIN purchorderdetails
				ON purchorders.orderno = purchorderdetails.orderno
			INNER JOIN suppliers
				ON purchorders.supplierno = suppliers.supplierid
			INNER JOIN currencies
				ON suppliers.currcode=currencies.currabrev
			WHERE purchorders.orderno=purchorderdetails.orderno
			GROUP BY purchorders.orderno,
					suppliers.suppname,
					purchorders.orddate,
					purchorders.initiator,
					purchorders.requisitionno,
					purchorders.allowprint,
					purchorders.status,
					suppliers.currcode,
					currencies.decimalplaces
			ORDER BY orddate DESC LIMIT 5';
$SalesOrdersResult2 = DB_query($SQL);
$Total = 0;

echo '<tbody>
		<tr>
			<th>' . _('Supplier') . '</th>
			<th>' . _('Order Date') . '</th>
			<th>' . _('Delivery Date') . '</th>
			<th>' . _('Order Total') . '</th>
			<th>' . _('Status') . '</th>
		</tr>';
$k = 0;
while ($row = DB_fetch_array($SalesOrdersResult2)) {
	if ($k == 1) {
		echo '<tr class="EvenTableRows">';
		$k = 0;
	} else {
		echo '<tr class="OddTableRows">';
		$k = 1;
	}
	$FormatedOrderValue2 = locale_number_format($row['ordervalue'], $row['currdecimalplaces']);
	$Total += $row['ordervalue'];

	$FormatedOrderDate1 = ConvertSQLDate($row['orddate']);
	$FormatedDelDate1 = ConvertSQLDate($row['deliverydate']);

	echo '<td> ' . $row['suppname'] . ' </td>
			<td>' . $FormatedOrderDate1 . '</td>
			<td>' . $FormatedDelDate1 . '</td>
			<td class="number">' . $FormatedOrderValue2 . '</td>
			<td> ' . $row['status'] . ' </td> ';

}
echo '</tbody>
	</table>';

?>