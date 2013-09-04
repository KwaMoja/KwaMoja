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
		echo '<meta http-equiv="refresh" content="600">';

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

$sql = "SELECT id FROM dashboard_scripts WHERE scripts='" . basename($_SERVER['PHP_SELF']) . "'";
$result = DB_query($sql, $db);
$myrow = DB_fetch_array($result);

echo '<div class="centre">
<table style="max-width:100%;width:99%;" border="0" cellspacing="0" cellpadding="0" border="1">
      <tr>
        <th colspan="5" style="margin:0px;padding:0px;background: transparent;">
			<div class="CanvasTitle">' . _('Purchase orders to authorise') .
				'<a href="' . $RootPath . 'Dashboard.php?Remove=' . $myrow['id'] . '" target="_parent" id="CloseButton">X</a>
			</div>
		</th>
      </tr>';
		$SQL = "SELECT purchorders.*,
			suppliers.suppname,
			suppliers.currcode,
			www_users.realname,
			www_users.email,
			currencies.decimalplaces AS currdecimalplaces
		FROM purchorders INNER JOIN suppliers
			ON suppliers.supplierid=purchorders.supplierno
		INNER JOIN currencies
			ON suppliers.currcode=currencies.currabrev
		INNER JOIN www_users
			ON www_users.userid=purchorders.initiator
	WHERE status='Pending' LIMIT 5";
		$result = DB_query($SQL,$db);
						echo "<tbody><tr><th> Supplier </th><th>E-mail</th><th>Order Date</th><th>Delivery Date</th><th>Total Amount</th></tr> ";
				$k=0;
				while ($row = DB_fetch_array($result))
				{
				if ($k == 1){
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}
				$AuthSQL="SELECT authlevel FROM purchorderauth
				WHERE
				currabrev='".$row['currcode']."'";

	$AuthResult=DB_query($AuthSQL, $db);
	$myauthrow=DB_fetch_array($AuthResult);
	$AuthLevel=$myauthrow['authlevel'];

	$OrderValueSQL="SELECT sum(unitprice*quantityord) as ordervalue, sum(sum(unitprice*quantityord)) as total
		           	FROM purchorderdetails";


	$OrderValueResult=DB_query($OrderValueSQL, $db);
	$MyOrderValueRow=DB_fetch_array($OrderValueResult);
	$OrderValue=$MyOrderValueRow['ordervalue'];
	$totalOV = $MyOrderValueRow['total'];

						$FormatedOrderDate2 = ConvertSQLDate($row['orddate']);
						$FormatedDelDate2 = ConvertSQLDate($row['deliverydate']);

						echo " <td> " . $row['suppname'] . " </td>";
						echo ' <td>$FormatedOrderDate2</td><td>'.$row['email'].'</td><td>$FormatedDelDate2</td><td>$totalOV</td><td> ' . $row['status'] . " </td> ";

					}
			echo '<tr><td colspan="3"></td><td colspan="2"></td></tr></tbody>';
		echo '</table></td>';
     echo' </tr>
    </table>
</div>';

?>