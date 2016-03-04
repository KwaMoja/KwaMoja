<?php
NewScript('Z_DeleteOldPrices.php', '15');
NewScript('Z_ChangeLocationCode.php', '15');

CreateTable('internalstockcatrole', "CREATE TABLE IF NOT EXISTS `internalstockcatrole` (
  `categoryid` varchar(6) NOT NULL,
  `secroleid` int(11) NOT NULL,
  KEY `internalstockcatrole_ibfk_1` (`categoryid`),
  KEY `internalstockcatrole_ibfk_2` (`secroleid`)
)");

NewScript('InternalStockCategoriesByRole.php', '15');
AddColumn('internalrequest', 'locations', 'TINYINT(1)', 'NOT NULL', '1', 'cashsalebranch');

ChangeColumnType('shipdate', 'loctransfers', 'DATETIME', 'NOT NULL', "0000-00-00 00:00:00");
ChangeColumnType('recdate', 'loctransfers', 'DATETIME', 'NOT NULL', "0000-00-00 00:00:00");

NewScript('GLJournalInquiry.php', '15');
NewScript('PDFGLJournal.php', '15');

AddColumn('department', 'www_users', 'INT(11)', 'NOT NULL', '0', 'pdflanguage');

NewConfigValue('WorkingDaysWeek', '5');

ChangeColumnType('address6', 'suppliers', 'VARCHAR(40)', 'NOT NULL', '');
ChangeColumnType('braddress6', 'custbranch', 'VARCHAR(40)', 'NOT NULL', '');
ChangeColumnType('address6', 'debtorsmaster', 'VARCHAR(40)', 'NOT NULL', '');

AddConstraint('stockcatproperties', 'categoryid', 'categoryid', 'stockcategory', 'categoryid');
AddConstraint('stockitemproperties', 'stockid', 'stockid', 'stockmaster', 'stockid');
AddConstraint('stockitemproperties', 'stkcatpropid', 'stkcatpropid', 'stockcatproperties', 'stkcatpropid');
AddConstraint('stockmovestaxes', 'stkmoveno', 'stkmoveno', 'stockmoves', 'stkmoveno');
AddIndex(array(
	'loccode'
), 'stockrequest', 'loccode');
AddConstraint('stockrequest', 'loccode', 'loccode', 'locations', 'loccode');
AddIndex(array(
	'departmentid'
), 'stockrequest', 'departmentid');
AddConstraint('stockrequest', 'departmentid', 'departmentid', 'departments', 'departmentid');

AddPrimaryKey('stockrequestitems', array(
	'dispatchitemsid',
	'dispatchid'
));
AddIndex(array(
	'dispatchid'
), 'stockrequestitems', 'dispatchid');
AddIndex(array(
	'stockid'
), 'stockrequestitems', 'stockid');
AddConstraint('stockrequestitems', 'dispatchid', 'dispatchid', 'stockrequest', 'dispatchid');
AddConstraint('stockrequestitems', 'stockrequestitems_ibfk_2', 'stockid', 'stockmaster', 'stockid');

AddPrimaryKey('internalstockcatrole', array(
	'categoryid',
	'secroleid'
));
AddConstraint('internalstockcatrole', 'internalstockcatrole_ibfk_1', 'categoryid', 'stockcategory', 'categoryid');
AddConstraint('internalstockcatrole', 'secroleid', 'secroleid', 'securityroles', 'secroleid');

NewScript('PDFQuotationPortrait.php', '2');

ChangeConfigValue('VersionNumber', '4.09');

UpdateDBNo(basename(__FILE__, '.php'));

?>