<?php
NewScript('Z_DeleteOldPrices.php','15', $db);
NewScript('Z_ChangeLocationCode.php','15', $db);

CreateTable('internalstockcatrole',
"CREATE TABLE IF NOT EXISTS `internalstockcatrole` (
  `categoryid` varchar(6) NOT NULL,
  `secroleid` int(11) NOT NULL,
  KEY `internalstockcatrole_ibfk_1` (`categoryid`),
  KEY `internalstockcatrole_ibfk_2` (`secroleid`)
)",
$db);

NewScript('InternalStockCategoriesByRole.php','15',$db);
AddColumn('internalrequest', 'locations', 'TINYINT(1)', 'NOT NULL', '1', 'cashsalebranch', $db);

ChangeColumnType('shipdate', 'loctransfers', 'DATETIME', 'NOT NULL', "0000-00-00 00:00:00", $db);
ChangeColumnType('recdate', 'loctransfers', 'DATETIME', 'NOT NULL', "0000-00-00 00:00:00", $db);

NewScript('GLJournalInquiry.php','15',$db);
NewScript('PDFGLJournal.php','15',$db);

AddColumn('department', 'www_users', 'INT(11)', 'NOT NULL', '0', 'pdflanguage', $db);

NewConfigValue('WorkingDaysWeek','5', $db);

ChangeColumnType('address6', 'suppliers', 'VARCHAR(40)', 'NOT NULL', '', $db);
ChangeColumnType('braddress6', 'custbranch', 'VARCHAR(40)', 'NOT NULL', '', $db);
ChangeColumnType('address6', 'debtorsmaster', 'VARCHAR(40)', 'NOT NULL', '', $db);

AddConstraint('stockcatproperties', 'categoryid', 'categoryid', 'stockcategory', 'categoryid', $db);
AddConstraint('stockitemproperties', 'stockid', 'stockid', 'stockmaster', 'stockid', $db);
AddConstraint('stockitemproperties', 'stkcatpropid', 'stkcatpropid', 'stockcatproperties', 'stkcatpropid', $db);
AddConstraint('stockmovestaxes', 'stkmoveno', 'stkmoveno', 'stockmoves', 'stkmoveno', $db);
AddIndex(array('loccode'), 'stockrequest', 'loccode', $db);
AddConstraint('stockrequest', 'loccode', 'loccode', 'locations', 'loccode', $db);
AddIndex(array('departmentid'), 'stockrequest', 'departmentid', $db);
AddConstraint('stockrequest', 'departmentid', 'departmentid', 'departments', 'departmentid', $db);

AddPrimaryKey('stockrequestitems', array('dispatchitemsid', 'dispatchid'), $db);
AddIndex(array('dispatchid'), 'stockrequestitems', 'dispatchid', $db);
AddIndex(array('stockid'), 'stockrequestitems', 'stockid', $db);
AddConstraint('stockrequestitems', 'dispatchid', 'dispatchid', 'stockrequest', 'dispatchid', $db);
AddConstraint('stockrequestitems', 'stockrequestitems_ibfk_2', 'stockid', 'stockmaster', 'stockid', $db);

AddPrimaryKey('internalstockcatrole', array('categoryid', 'secroleid'), $db);
AddConstraint('internalstockcatrole', 'internalstockcatrole_ibfk_1', 'categoryid', 'stockcategory', 'categoryid', $db);
AddConstraint('internalstockcatrole', 'secroleid', 'secroleid', 'securityroles', 'secroleid', $db);

NewScript('PDFQuotationPortrait.php','2',$db);

ChangeConfigValue('VersionNumber', '4.09', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>