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

echo '<div align="center">
<table border="0" cellspacing="0" cellpadding="0"  style="max-width:100%;width:99%;">
      <tr>
        <th colspan="3" style="margin:0px;padding:0px;background: transparent;">
			<div class="CanvasTitle">' . _('Work orders') . '
				<a href="' . $RootPath . 'Dashboard.php?Remove=' . urlencode($MyRow['id']) . '" target="_parent" id="CloseButton">X</a>
			</div>
		</th>
      </tr>';
$SQL = "SELECT workorders.wo,
				woitems.stockid,
				stockmaster.
				description,
				stockmaster.decimalplaces,
				woitems.qtyreqd,
				woitems.qtyrecd,
				workorders.requiredby,
				workorders.startdate
			FROM workorders
			INNER JOIN woitems
				ON workorders.wo = woitems.wo
			INNER JOIN stockmaster
				ON woitems.stockid = stockmaster.stockid
			ORDER BY workorders.wo LIMIT 5";
$WorkOrdersResult = DB_query($SQL);

echo '<tbody>
		<tr>
			<th>' . _('Item') . '</th>
			<th>' . _('Quantity Required') . '</th>
			<th>' . _('Quantity Outstanding') . '</th>
		</tr>';
$k = 0;
while ($row = DB_fetch_array($WorkOrdersResult)) {
	$StockId = $row['stockid'];
	if ($k == 1) {
		echo '<tr class="EvenTableRows">';
		$k = 0;
	} else {
		echo '<tr class="OddTableRows">';
		$k = 1;
	}
	$FormatedRequiredByDate = ConvertSQLDate($row['requiredby']);
	$FormatedStartDate = ConvertSQLDate($row['startdate']);
	$qreq = locale_number_format($row['qtyreqd'], $row['decimalplaces']);
	$qout = locale_number_format($row['qtyreqd'] - $row['qtyrecd'], $row['decimalplaces']);

	echo '<td><a href="' . $RootPath . '/StockStatus.php?StockID=' . urlencode($StockId) . '" target="_blank">' . $row['stockid'] . ' -' . $row['description'] . '</td>
			<td class="number">' . $qreq . '</td>
			<td class="number">' . $qout . '</td>
		</tbody>';

}

echo '</table>';

?>