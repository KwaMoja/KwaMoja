<?php

unset($_SESSION['ModuleLink']);
unset($_SESSION['ReportList']);
unset($_SESSION['ModuleList']);
unset($_SESSION['MenuItems']);

$SQL = "SELECT SQL_CACHE `modulelink`,
				`reportlink` ,
				`modulename`
			FROM modules
			WHERE secroleid = '" . $_SESSION['AccessLevel'] . "'
			ORDER BY `sequence`";
$Result = DB_query($SQL);

while ($MyRow = DB_fetch_array($Result)) {
	$_SESSION['ModuleLink'][] = $MyRow['modulelink'];
	$_SESSION['ReportList'][$MyRow['modulelink']] = $MyRow['reportlink'];
	$_SESSION['ModuleList'][] = _($MyRow['modulename']);
}
$SQL = "SELECT SQL_CACHE `modulelink`,
				`menusection` ,
				`caption` ,
				`url`
			FROM menuitems
			WHERE secroleid = '" . $_SESSION['AccessLevel'] . "'
			ORDER BY `sequence`, `menusection`";
$Result = DB_query($SQL);

while ($MyRow = DB_fetch_array($Result)) {
	$_SESSION['MenuItems'][$MyRow['modulelink']][$MyRow['menusection']]['Caption'][] = _($MyRow['caption']);
	$_SESSION['MenuItems'][$MyRow['modulelink']][$MyRow['menusection']]['URL'][] = $MyRow['url'];
}

include('includes/PluginMenuLinksArray.php');

?>