<?php

AddColumn('language', 'accountsection', 'VARCHAR(10)', 'NOT NULL', "en_GB.utf8", 'sectionid');
DropPrimaryKey('accountsection', Array('sectionid'));
AddPrimaryKey('accountsection', Array('sectionid', 'language'));

AddColumn('language', 'accountgroups', 'VARCHAR(10)', 'NOT NULL', "en_GB.utf8", 'groupcode');
DropPrimaryKey('accountgroups', Array('groupcode'));
AddPrimaryKey('accountgroups', Array('groupcode', 'language'));

AddColumn('language', 'chartmaster', 'VARCHAR(10)', 'NOT NULL', "en_GB.utf8", 'accountcode');
DropPrimaryKey('chartmaster', Array('accountcode'));
AddPrimaryKey('chartmaster', Array('accountcode', 'language'));

$SQL = "SELECT stockid, description, longdescription FROM stockmaster";
$Result = DB_query($SQL);
while ($MyRow = DB_fetch_array($Result)) {
	InsertRecord('stockdescriptiontranslations', array('stockid', 'language_id'), array($MyRow['stockid'], $_SESSION['DefaultLanguage']), array('stockid', 'language_id', 'descriptiontranslation', 'needsrevision'), array($MyRow['stockid'], $_SESSION['DefaultLanguage'], $MyRow['description'], 0));
	InsertRecord('stocklongdescriptiontranslations', array('stockid', 'language_id'), array($MyRow['stockid'], $_SESSION['DefaultLanguage']), array('stockid', 'language_id', 'longdescriptiontranslation', 'needsrevision'), array($MyRow['stockid'], $_SESSION['DefaultLanguage'], $MyRow['longdescription'], 0));
}

UpdateDBNo(basename(__FILE__, '.php'));

?>