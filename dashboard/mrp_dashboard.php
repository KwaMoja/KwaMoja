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

echo '<table border="0" cellspacing="0" cellpadding="2" style="max-width:100%;width:99%;">
		<tr>
			<th colspan="4" style="margin:0px;padding:0px;background: transparent;">
				<div class="CanvasTitle">' . _('MRP') . '
					<a href="' . $RootPath . 'Dashboard.php?Remove=' . urlencode($MyRow['id']) . '" target="_parent" id="CloseButton">X</a>
				</div>
			</th>
		</tr>';
$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				stockmaster.mbflag,
				SUM(locstock.quantity) AS qoh,
				stockmaster.units,
				stockmaster.decimalplaces
			FROM stockmaster,
				locstock
			WHERE stockmaster.stockid=locstock.stockid
			GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag,
					stockmaster.decimalplaces
			ORDER BY stockmaster.stockid LIMIT 5";

$searchresult = DB_query($SQL);
echo '<tbody>
		<tr>
			<th>' . _('Code') . '</th>
			<th>' . _('Description') . '</th>
			<th>' . _('Total QTY on Hand') . '</th>
			<th>' . _('Units') . '</th>
		</tr>';
$k = 0;
while ($row = DB_fetch_array($searchresult)) {
	$StockId = $row['stockid'];
	if ($k == 1) {
		echo '<tr class="EvenTableRows">';
		$k = 0;
	} else {
		echo '<tr class="OddTableRows">';
		$k = 1;
	}
	$qoh = locale_number_format($row['qoh'], $row['decimalplaces']);

	echo '<td><a href="' . $RootPath . '/StockStatus.php?StockID=' . urlencode($StockId) . '" target="_blank">' . $row['stockid'] . '</td>
		<td>' . $row['description'] . '</td>
		<td class="number">' . $qoh . '</td>
		<td>' . $row['units'] . '</td>
	</tr>';

}

echo '</tbody>
	</table>';

?>