<?php

$sql = "SELECT `modulelink`,
				`reportlink` ,
				`modulename`
			FROM modules
			WHERE secroleid = '" . $_SESSION['AccessLevel'] . "'
			ORDER BY `sequence`";
$result = DB_query($sql, $db);
$Modules = DB_fetch_all($result);

foreach ($Modules as $myrow) {
	$ModuleLink[] = $myrow['modulelink'];
	$ReportList[$myrow['modulelink']] = $myrow['reportlink'];
	$ModuleList[] = _($myrow['modulename']);
}

$sql = "SELECT `modulelink`,
				`menusection` ,
				`caption` ,
				`url`
			FROM menuitems
			WHERE secroleid = '" . $_SESSION['AccessLevel'] . "'
			ORDER BY `sequence`, `menusection`";
$result = DB_query($sql, $db);

$Items = DB_fetch_all($result);

foreach ($Items as $myrow) {
	$MenuItems[$myrow['modulelink']][$myrow['menusection']]['Caption'][] = _($myrow['caption']);
	$MenuItems[$myrow['modulelink']][$myrow['menusection']]['URL'][] = $myrow['url'];
}

include('includes/PluginMenuLinksArray.php');

?>