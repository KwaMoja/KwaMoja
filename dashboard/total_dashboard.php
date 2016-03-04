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
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
header('Pragma: no-cache'); // HTTP 1.0.
header('Expires: 0'); // Proxies.
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
		body { font-size: ' . $FontSize . ';
			}
	</style>';

echo '</head><body style="background:transparent;">';

$SQL = "SELECT id FROM dashboard_scripts WHERE scripts='" . basename($_SERVER['PHP_SELF']) . "'";
$Result = DB_query($SQL);
$MyRow = DB_fetch_array($Result);

echo '<div class="centre">
		<table border="0" cellspacing="0" style="max-width:100%;width:99%;" cellpadding="2" class="selection">
			<tr>
				<th colspan="2" style="margin:0px;padding:0px;background: transparent;">
					<div class="CanvasTitle">' . _('Sales/Purchase Order Report') . '
						<a href="' . $RootPath . 'Dashboard.php?Remove=' . urlencode($MyRow['id']) . '" target="_parent" id="CloseButton">X</a>
					</div>
				</th>
			</tr>
			<tr bgcolor="#F2F2F2">
				<td style="border-bottom:1px solid #3550aa;"> Total amount of sales orders</td>';
$SQL = "SELECT salesorders.orderno,
				debtorsmaster.name,
				custbranch.brname,
				salesorders.customerref,
				salesorders.orddate,
				salesorders.deliverydate,
				salesorders.deliverto,
				currencies.decimalplaces AS currdecimalplaces,
				SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
			FROM salesorders
			INNER JOIN salesorderdetails
				ON salesorders.orderno = salesorderdetails.orderno
			INNER JOIN debtorsmaster
				ON salesorders.debtorno = debtorsmaster.debtorno
			INNER JOIN custbranch
				ON salesorders.branchcode = custbranch.branchcode
				AND salesorders.debtorno = custbranch.debtorno
			INNER JOIN currencies
				ON debtorsmaster.currcode = currencies.currabrev
			GROUP BY salesorders.orderno,
					debtorsmaster.name,
					currencies.decimalplaces,
					custbranch.brname,
					salesorders.customerref,
					salesorders.orddate ORDER BY salesorders.orderno";



$SalesOrdersResult = DB_query($SQL);

$TotalSalesOrders = 0;
while ($row = DB_fetch_array($SalesOrdersResult)) {
	$TotalSalesOrders += $row['ordervalue'];
}


echo '<td style="border-bottom:1px solid #3550aa;" class="number"><strong>' . locale_number_format($TotalSalesOrders, $row['currdecimalplaces']) . '</strong></td></tr>
<tr bgcolor="#FFFFFF"><td style="border-bottom:1px solid #3550aa";>Total amount of Purchase orders</td>';

$SQL = "SELECT purchorders.orderno,
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
						currencies.decimalplaces LIMIT 5";
$SalesOrdersResult2 = DB_query($SQL);
$TotalPurchaseOrders = 0;
while ($row = DB_fetch_array($SalesOrdersResult2)) {

	$TotalPurchaseOrders += $row['ordervalue'];
}

echo '<td style="border-bottom:1px solid #3550aa;" class="number"><strong>' . locale_number_format($TotalPurchaseOrders, $row['currdecimalplaces']) . '</strong></td></tr>
<tr bgcolor="#F2F2F2"><td>Total amount of Outstanding to receive</td>';
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
				FROM salesorders INNER JOIN salesorderdetails
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
$TotalOutstanding = 0;
while ($row = DB_fetch_array($SalesOrdersResult1)) {
	$TotalOutstanding += $row['ordervalue'];
}

echo '<td style="padding-left:60px;" class="number"><strong>' . locale_number_format($TotalOutstanding, $row['currdecimalplaces']) . '</strong></td>
</table>
</div></body>';

?>