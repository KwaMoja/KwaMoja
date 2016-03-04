<?php
$PageSecurity = 1;
include('includes/session.inc');
$JobsSQL = "SELECT jobnumber, script, frequency FROM schedule WHERE nextrun<CURRENT_TIMESTAMP";
$JobsResult = DB_query($JobsSQL);

while ($JobsRow = DB_fetch_array($JobsResult)) {
	if ($JobsRow['frequency'] == 'h') {
		$UpdateSQL = "UPDATE schedule SET nextrun=DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE jobnumber='" . $JobsRow['jobnumber'] . "'";
	}
	if ($JobsRow['frequency'] == 'd') {
		$UpdateSQL = "UPDATE schedule SET nextrun=DATE_ADD(NOW(), INTERVAL 24 HOUR) WHERE jobnumber='" . $JobsRow['jobnumber'] . "'";
	}
	if ($JobsRow['frequency'] == 'w') {
		$UpdateSQL = "UPDATE schedule SET nextrun=DATE_ADD(NOW(), INTERVAL 7 DAY) WHERE jobnumber='" . $JobsRow['jobnumber'] . "'";
	}
	$UpdateResult = DB_query($UpdateSQL);
	ob_start();
	include($JobsRow['script']);
	ob_end_clean();
}
?>