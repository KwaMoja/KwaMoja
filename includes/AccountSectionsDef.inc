<?php

$Sections = array();
$SQL = 'SELECT sectionid, sectionname FROM accountsection ORDER by sectionid';
$SectionResult = DB_query($SQL);
while ($secrow = DB_fetch_array($SectionResult)) {
	$Sections[$secrow['sectionid']] = $secrow['sectionname'];
}
DB_free_result($SectionResult); // no longer needed
?>