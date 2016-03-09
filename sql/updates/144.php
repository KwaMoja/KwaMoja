<?php

AddColumn('language', 'accountsection', 'VARCHAR(10)', 'NOT NULL', "en_GB.utf8", 'sectionid');
DropConstraint('accountgroups', 'accountgroups_ibfk_1');
DropPrimaryKey('accountsection', Array('sectionid'));
AddPrimaryKey('accountsection', Array('sectionid', 'language'));

AddColumn('language', 'accountgroups', 'VARCHAR(10)', 'NOT NULL', "en_GB.utf8", 'groupcode');
DropPrimaryKey('accountgroups', Array('groupcode'));
AddPrimaryKey('accountgroups', Array('groupcode', 'language'));

AddColumn('language', 'chartmaster', 'VARCHAR(10)', 'NOT NULL', "en_GB.utf8", 'accountcode');
DropConstraint('bankaccounts', 'bankaccounts_ibfk_1');
DropConstraint('chartdetails', 'chartdetails_ibfk_1');
DropConstraint('gltrans', 'gltrans_ibfk_1');
DropConstraint('pcexpenses', 'pcexpenses_ibfk_1');
DropConstraint('pctabs', 'pctabs_ibfk_1');
DropConstraint('pctabs', 'pctabs_ibfk_5');
DropConstraint('taxauthorities', 'taxauthorities_ibfk_1');
DropConstraint('taxauthorities', 'taxauthorities_ibfk_2');
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