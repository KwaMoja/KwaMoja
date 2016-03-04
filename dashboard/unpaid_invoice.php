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

echo '<div align="center" style="width:100%;">
	<table  style="max-width:100%;width:99%;" border="0" cellspacing="0" cellpadding="2">
      <tr>
        <th colspan="5" style="margin:0px;padding:0px;background: transparent;">
			<div class="CanvasTitle">' . _('Latest unpaid customer invoices') . '
				<a href="' . $RootPath . 'Dashboard.php?Remove=' . urlencode($MyRow['id']) . '" target="_parent" id="CloseButton">X</a>
			</div>
        </th>
      </tr>';

$SQL = "SELECT salesorders.orderno,
				debtorsmaster.name,
				custbranch.brname,
				salesorders.customerref,
				salesorders.orddate,
				salesorders.deliverydate,
				salesorders.deliverto,
				salesorders.printedpackingslip,
				salesorders.poplaced,
				currencies.decimalplaces AS currdecimalplaces,
				SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)/currencies.rate) AS ordervalue
			FROM salesorders
			INNER JOIN salesorderdetails
				ON salesorders.orderno = salesorderdetails.orderno
			INNER JOIN debtorsmaster
				ON salesorders.debtorno = debtorsmaster.debtorno
			INNER JOIN custbranch
				ON debtorsmaster.debtorno = custbranch.debtorno
				AND salesorders.branchcode = custbranch.branchcode
			INNER JOIN currencies
				ON debtorsmaster.currcode = currencies.currabrev
			WHERE salesorderdetails.completed=0
			GROUP BY salesorders.orderno,
					debtorsmaster.name,
					custbranch.brname,
					salesorders.customerref,
					salesorders.orddate,
					salesorders.deliverydate,
					salesorders.deliverto,
					salesorders.printedpackingslip,
					salesorders.poplaced
			ORDER BY salesorders.orderno";
$SalesOrdersResult1 = DB_query($SQL);


echo '<tr>
		<th>' . _('Customer') . '</th>
		<th>' . _('Order Date') . '</th>
		<th>' . _('Delivery Date') . '</th>
		<th>' . _('Delivery To') . '</th>
		<th>' . _('Order Total') . '</th>
	</tr> ';

$k = 0;


$TotalOrderValue = 0;
while ($row = DB_fetch_array($SalesOrdersResult1)) {
	if ($k == 1) {
		echo '<tr class="EvenTableRows">';
		$k = 0;
	} else {
		echo '<tr class="OddTableRows">';
		$k = 1;
	}
	$fo = locale_number_format($row['ordervalue'], $row['currdecimalplaces']);
	$TotalOrderValue += $row['ordervalue'];

	$FormatedOrderDate = ConvertSQLDate($row['orddate']);
	$FormatedDelDate = ConvertSQLDate($row['deliverydate']);

	echo '<td>' . $row['name'] . '</td>
			<td>' . $FormatedOrderDate . '</td>
			<td>' . $FormatedDelDate . '</td>
			<td> ' . $row['deliverto'] . ' </td>
			<td class="number">' . $fo . '</td>
		</tr>';

}
echo '<tr>
		<td colspan="4">' . _('Total') . '</td>
		<td colspan="2" class="number">' . locale_number_format($TotalOrderValue, $row['currdecimalplaces']) . '</td>
	</tr>
</tbody>';

echo '</table>';

?>