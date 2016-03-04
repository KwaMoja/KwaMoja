<?php

AddColumn('groupcode', 'accountgroups', 'INT(11)', 'NOT NULL', "0", 'groupname');
AddColumn('groupcode', 'chartmaster', 'INT(11)', 'NOT NULL', "0", 'group_');
AddColumn('parentgroupcode', 'accountgroups', 'INT(11)', 'NOT NULL', "0", 'parentgroupname');

$i = 1;
$SQL = "SELECT parentgroupname, groupname FROM accountgroups";
$Result = DB_query($SQL);
while ($MyRow = DB_fetch_array($Result)) {
	$UpdateCodeSQL = "UPDATE accountgroups SET groupcode='" . ($i * 10) . "' WHERE groupname='" . $MyRow['groupname'] . "'";
	$UpdateCodeResult = DB_query($UpdateCodeSQL);
	if ($MyRow['parentgroupname'] == '') {
		$ParentGroupCode = 0;
	} else {
		$ParentCodeSQL = "SELECT groupcode FROM accountgroups WHERE groupname='" . $MyRow['parentgroupname'] . "'";
		$ParentCodeResult = DB_query($ParentCodeSQL);
		$ParentCodeRow = DB_fetch_array($ParentCodeResult);
		$ParentGroupCode = $ParentCodeRow['groupcode'];
	}
	$UpdateParentSQL = "UPDATE accountgroups SET parentgroupcode='" . $ParentGroupCode . "' WHERE groupname='" . $MyRow['groupname'] . "'";
	$UpdateParentResult = DB_query($UpdateParentSQL);
	++$i;
}

$SQL = "SELECT accountcode, group_ FROM chartmaster";
$Result = DB_query($SQL);
while ($MyRow = DB_fetch_array($Result)) {
	$UpdateSQL = "UPDATE chartmaster SET groupcode=(SELECT groupcode FROM accountgroups WHERE groupname=group_) WHERE accountcode='" . $MyRow['accountcode'] . "'";
	$UpdateResult = DB_query($UpdateSQL);
}

UpdateDBNo(basename(__FILE__, '.php'));

?>