<?php
$ModuleLink = array();
$ReportList = array();

/*The headings showing on the tabs accross the main index used also in WWW_Users for defining what should be visible to the user */
$ModuleList = array();

$sql = "SELECT `modulelink`,
				`reportlink` ,
				`modulename`
			FROM modules
			WHERE secroleid = '" . $_SESSION['AccessLevel'] . "'
			ORDER BY `sequence`";
$result = DB_query($sql, $db);

while ($myrow=DB_fetch_array($result)) {
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

while ($myrow=DB_fetch_array($result)) {
	$MenuItems[$myrow['modulelink']][$myrow['menusection']]['Caption'][] = _($myrow['caption']);
	$MenuItems[$myrow['modulelink']][$myrow['menusection']]['URL'][] = $myrow['url'];
}

include('includes/PluginMenuLinksArray.php');

?>