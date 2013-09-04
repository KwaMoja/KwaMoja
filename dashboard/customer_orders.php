<?php
$PageSecurity=0;
$PathPrefix='../';
include('../includes/session.inc');

$RootPath = '../';

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

	echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>Dashboard</title>';
	echo '<link rel="shortcut icon" href="'. $RootPath.'/favicon.ico" />';
	echo '<link rel="icon" href="' . $RootPath.'/favicon.ico" />';

		echo '<meta http-equiv="Content-Type" content="application/html; charset=utf-8" />';

	echo '<link href="' . $RootPath . '/css/'. $_SESSION['Theme'] .'/default.css" rel="stylesheet" type="text/css" />';
	echo '<script type="text/javascript" src = "'.$RootPath.'/javascripts/MiscFunctions.js"></script>';
	echo '<style media="screen">
			.noPrint{ display: block; }
			.yesPrint{ display: block !important; }
		</style>
		<style media="print">
			.noPrint{ display: none; }
			.yesPrint{ display: block !important; }
		</style>';


	echo '</head>
			<body  style="background:transparent;">';

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

$sql = "SELECT id FROM dashboard_scripts WHERE scripts='" . basename($_SERVER['PHP_SELF']) . "'";
$result = DB_query($sql, $db);
$myrow = DB_fetch_array($result);

echo '<div class="centre">
<table  style="max-width:100%;width:99%;" border="0" cellspacing="0" cellpadding="2">
      <tr>
        <th colspan="5" style="margin:0px;padding:0px;background: transparent;">
			<div class="CanvasTitle">' . _('Latest customer orders') .
				'<a href="' . $RootPath . 'Dashboard.php?Remove=' . $myrow['id'] . '" target="_parent" id="CloseButton">X</a>
			</div>
        </th>
      </tr>';
 		$SQL = 'SELECT salesorders.orderno, debtorsmaster.name, custbranch.brname, salesorders.customerref, salesorders.orddate, salesorders.deliverydate, salesorders.deliverto, currencies.decimalplaces AS currdecimalplaces, SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
FROM salesorders
INNER JOIN salesorderdetails ON salesorders.orderno = salesorderdetails.orderno
INNER JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
INNER JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
AND salesorders.debtorno = custbranch.debtorno
INNER JOIN currencies ON debtorsmaster.currcode = currencies.currabrev  WHERE salesorders.orderno= 1 AND salesorderdetails.completed = 1 GROUP BY salesorders.orderno,
						debtorsmaster.name,
						currencies.decimalplaces,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate ORDER BY salesorders.orderno LIMIT 5';



					$SalesOrdersResult = DB_query($SQL,$db);

	$TotalSalesOrders = 0;
						echo '<tr><th>Order #</th><th> Customer </th><th>Order Date</th><th>Delivery Date</th><th>Order Amount</th></tr> ';
						$k=0;
	while ($row = DB_fetch_array($SalesOrdersResult))

					{

						if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}
						$TotalSalesOrders += $row['ordervalue'];
						$FormatedOrderValue = locale_number_format($row['ordervalue'],$row['currdecimalplaces']);
						$OrderDate = ConvertSQLDate($row['orddate']);
						$DelDate = ConvertSQLDate($row['deliverydate']);
						$TotalSalesOrders += $row['ordervalue'];
						echo ' <td> ' . $row['orderno'] . ' </td><td> ' . $row['name'] . ' </td>';
						echo ' <td>'.$OrderDate.'</td><td>'.$DelDate.'</td><td>'.$FormatedOrderValue.'</td> ';
						}
					    echo '<tr><td colspan=3>Total</td><td colspan=2>'.$TotalSalesOrders.'</td></tr></tbody>';

					echo '</table></td>
      </tr>
    </table>




</div>
';



include('includes/footer.inc');
?>
