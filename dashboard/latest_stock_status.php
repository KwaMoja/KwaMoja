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

echo '<div align="center" style="width:100%;">
<table style="max-width:100%;width:99%;" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <th colspan="4" style="margin:0px;padding:0px;background: transparent;">
			<div class="CanvasTitle">' . _('Latest stock status') .
				'<a href="' . $RootPath . 'Dashboard.php?Remove=' . $myrow['id'] . '" target="_parent" id="CloseButton">X</a>
			</div>
		</th>
      </tr>';
		$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.mbflag,
						stockmaster.discontinued,
						SUM(locstock.quantity) AS qoh,
						stockmaster.units,
						stockmaster.decimalplaces
					FROM stockmaster
					LEFT JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid,
						locstock
					WHERE stockmaster.stockid=locstock.stockid
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.units,
						stockmaster.mbflag,
						stockmaster.discontinued,
						stockmaster.decimalplaces
					ORDER BY stockmaster.discontinued, stockmaster.stockid LIMIT 5";
					$searchresult = DB_query($SQL, $db);

						echo "<tbody><tr><th width='25%'> Code </th><th width='25%'>Description</th><th width='25%'>Total QTY on Hand</th><th width='25%'>Units</th></tr> ";
						$k=0;
					while ($row = DB_fetch_array($searchresult))
					{
						$stockid = $row['stockid'];
						$desc = $row['description'];
						$units = $row['units'];
						$mbflag = $row['mbflag'];

						if ($k == 1){
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}
					$qoh = locale_number_format($row['qoh'], $myrow['decimalplaces']);

		echo " <td> " . $row['stockid'] . " </td>";
						echo " <td> " . $row['description'] . " </td><td>$qoh</td><td width='80px'> " . $row['units'] . " </td></tbody> ";

					}

		echo '</table></td>';
      echo '</tr>
    </table>



</div>';

?>