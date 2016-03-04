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

echo '<table border="0" cellspacing="0" cellpadding="1" style="max-width:100%;width:99%;">
		<tr>
			<th colspan="5" style="margin:0px;padding:0px;background: transparent;">
				<div class="CanvasTitle">' . _('Latest bank transactions') . '
					<a href="' . $RootPath . 'Dashboard.php?Remove=' . urlencode($MyRow['id']) . '" target="_parent" id="CloseButton">X</a>
				</div>
			</th>
		</tr>';

$SQL = "SELECT banktrans.currcode,
				banktrans.amount,
				banktrans.functionalexrate,
				banktrans.exrate,
				banktrans.banktranstype,
				banktrans.transdate,
				bankaccounts.bankaccountname,
				systypes.typename,
				currencies.decimalplaces
			FROM banktrans
			INNER JOIN bankaccounts
				ON banktrans.bankact=bankaccounts.accountcode
			INNER JOIN systypes
				ON banktrans.type=systypes.typeid
			INNER JOIN currencies
				ON banktrans.currcode=currencies.currabrev
			ORDER BY banktrans.transdate DESC LIMIT 5";

$Result = DB_query($SQL);
$AccountCurrTotal = 0;
$LocalCurrTotal = 0;

echo '<tbody>
		<tr>
			<th>' . _('Currency') . '</th>
			<th>' . _('Amount') . '</th>
			<th>' . _('Transaction Type') . '</th>
			<th>' . _('Transaction Date') . '</th>
			<th>' . _('Account Name') . '</th>
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

	$AccountCurrTotal += $row['amount'];
	$LocalCurrTotal += $row['amount'] / $row['functionalexrate'] / $row['exrate'];
	echo '<td>' . $row['currcode'] . '</td>
			<td class="number">' . locale_number_format($row['amount'], $row['decimalplaces']) . '</td>
			<td>' . $row['banktranstype'] . '</td>
			<td>' . $row['transdate'] . '</td>
			<td class="number">' . $row['bankaccountname'] . '</td>
		</tr>';
}
echo '</tbody>
		</table>';

?>