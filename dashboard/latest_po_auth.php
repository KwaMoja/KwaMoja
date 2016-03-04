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

echo '<table style="max-width:100%;width:99%;" border="0" cellspacing="0" cellpadding="1" border="1">
		<tr>
			<th colspan="6" style="margin:0px;padding:0px;background: transparent;">
				<div class="CanvasTitle">' . _('Purchase orders to authorise') . '
					<a href="' . $RootPath . 'Dashboard.php?Remove=' . urlencode($MyRow['id']) . '" target="_parent" id="CloseButton">X</a>
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
$Result = DB_query($SQL);
echo '<tbody>
		<tr>
			<th>' . _('Supplier') . '</th>
			<th>' . _('Email') . '</th>
			<th>' . _('Order Date') . '</th>
			<th>' . _('Delivery Date') . '</th>
			<th>' . _('Total Amount') . '</th>
			<th>' . _('Status') . '</th>
		</tr>';
$k = 0;
while ($row = DB_fetch_array($Result)) {
	if ($k == 1) {
		echo '<tr class="EvenTableRows">';
		$k = 0;
	} else {
		echo '<tr class="OddTableRows">';
		$k = 1;
	}
	$AuthSQL = "SELECT authlevel
					FROM purchorderauth
					WHERE currabrev='" . $row['currcode'] . "'
						AND userid='" . $_SESSION['UserID'] . "'";

	$AuthResult = DB_query($AuthSQL);
	$myauthrow = DB_fetch_array($AuthResult);
	$AuthLevel = $myauthrow['authlevel'];

	$OrderValueSQL = "SELECT sum(unitprice*quantityord) as ordervalue,
							sum(unitprice*quantityord) as total
						FROM purchorderdetails
						GROUP BY orderno";


	$OrderValueResult = DB_query($OrderValueSQL);
	$MyOrderValueRow = DB_fetch_array($OrderValueResult);
	$OrderValue = $MyOrderValueRow['ordervalue'];
	$totalOV = $MyOrderValueRow['total'];

	$FormatedOrderDate2 = ConvertSQLDate($row['orddate']);
	$FormatedDelDate2 = ConvertSQLDate($row['deliverydate']);

	echo '<td>' . $row['suppname'] . '</td>
			<td>' . $row['email'] . '</td>
			<td>' . $FormatedOrderDate2 . '</td>
			<td>' . $FormatedDelDate2 . '</td>
			<td class="number">' . locale_number_format($totalOV, $row['currdecimalplaces']) . '</td>
			<td>' . $row['status'] . '</td>
		</tr>';

}
echo '</tbody>
	</table>';

?>