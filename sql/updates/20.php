<?php
$SQL = "SET foreign_key_checks=0";
$Result = executeSQL($SQL, False);

executeSQL("INSERT INTO securitytokens VALUES(0, 'Main Index Page')");
executeSQL("INSERT INTO securitygroups (SELECT secroleid,0 FROM securityroles)");

NewScript('reportwriter/admin/ReportCreator.php', '15');
NewScript('RecurringSalesOrdersProcess.php', '1');

RemoveScript('Z_CopyBOM.php');
NewScript('CopyBOM.php', 15);

CreateTable('departments',
"CREATE TABLE departments (
`departmentid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`description` VARCHAR (100) NOT NULL DEFAULT '',
`authoriser` varchar (20) NOT NULL DEFAULT ''
)");

CreateTable('stockrequest',
"CREATE TABLE stockrequest (
`dispatchid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`loccode` VARCHAR (5) NOT NULL DEFAULT '',
`departmentid` INT NOT NULL DEFAULT 0,
`despatchdate` DATE NOT NULL DEFAULT '0000-00-00',
`authorised` TINYINT NOT NULL DEFAULT 0,
`closed` TINYINT NOT NULL DEFAULT 0,
`narrative` TEXT NOT NULL
)");

CreateTable('stockrequestitems',
"CREATE TABLE stockrequestitems (
`dispatchitemsid` INT NOT NULL DEFAULT 0,
`dispatchid` INT NOT NULL DEFAULT 0,
`stockid` VARCHAR (20) NOT NULL DEFAULT '',
`quantity` DOUBLE NOT NULL DEFAULT 0,
`qtydelivered` DOUBLE NOT NULL DEFAULT 0,
`decimalplaces` INT(11) NOT NULL DEFAULT 0,
`uom` VARCHAR(20) NOT NULL DEFAULT '',
`completed` TINYINT NOT NULL DEFAULT 0
)");

NewScript('Departments.php', '1');
NewScript('InternalStockRequest.php', '1');
NewScript('InternalStockRequestFulfill.php', '1');
NewScript('InternalStockRequestAuthorisation.php', '1');

AddColumn('issueglact', 'stockcategory', 'INT', 'NOT NULL', '0', 'adjglact');
executeSQL("UPDATE `stockcategory` SET `issueglact`=`adjglact`");

NewConfigValue('ShowStockidOnImages','0');

NewScript('SupplierPriceList.php', '4');

CreateTable('labels',
"CREATE TABLE IF NOT EXISTS `labels` (
  `labelid` tinyint(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(50) NOT NULL,
  `papersize` varchar(20) NOT NULL,
  `height` tinyint(11) NOT NULL,
  `width` tinyint(11) NOT NULL,
  `topmargin` tinyint(11) NOT NULL,
  `leftmargin` tinyint(11) NOT NULL,
  `rowheight` tinyint(11) NOT NULL,
  `columnwidth` tinyint(11) NOT NULL,
  PRIMARY KEY (`labelid`)
)");

CreateTable('labelfields',
"CREATE TABLE IF NOT EXISTS `labelfields` (
  `labelfieldid` int(11) NOT NULL AUTO_INCREMENT,
  `labelid` tinyint(4) NOT NULL,
  `fieldvalue` varchar(20) CHARACTER SET utf8 NOT NULL,
  `vpos` tinyint(4) NOT NULL,
  `hpos` tinyint(4) NOT NULL,
  `fontsize` tinyint(4) NOT NULL,
  `barcode` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`labelfieldid`),
  KEY `labelid` (`labelid`),
  KEY `vpos` (`vpos`)
)");

AddIndex(array('locationname'), 'locations', 'locationname');

DropPrimaryKey('chartmaster', 'accountcode');
DropConstraint('bankaccounts', 'bankaccounts_ibfk_1');
DropConstraint('chartdetails', 'chartdetails_ibfk_1');
DropConstraint('gltrans', 'gltrans_ibfk_1');
DropConstraint('pcexpenses', 'pcexpenses_ibfk_1');
DropConstraint('pctabs', 'pctabs_ibfk_5');
DropConstraint('taxauthorities', 'taxauthorities_ibfk_1');
ChangeColumnType('accountcode', 'chartmaster', 'varchar(20)', 'NOT NULL', '0');
AddPrimaryKey('chartmaster', 'accountcode');
AddConstraint('bankaccounts', 'bankaccounts_ibfk_1', 'accountcode', 'chartmaster', 'accountcode');
AddConstraint('chartdetails', 'chartdetails_ibfk_1', 'accountcode', 'chartmaster', 'accountcode');
AddConstraint('gltrans', 'gltrans_ibfk_1', 'account', 'chartmaster', 'accountcode');
AddConstraint('pcexpenses', 'pcexpenses_ibfk_1', 'glaccount', 'chartmaster', 'accountcode');
AddConstraint('pctabs', 'pctabs_ibfk_5', 'glaccountassignment', 'chartmaster', 'accountcode');
AddConstraint('taxauthorities', 'taxauthorities_ibfk_1', 'taxglcode', 'chartmaster', 'accountcode');


ChangeColumnName('papersize', 'labels', 'DOUBLE', 'NOT NULL', '0', 'pagewidth');
AddColumn('pageheight', 'labels', 'DOUBLE', 'NOT NULL', '0', 'pagewidth');
ChangeColumnType('height', 'labels', 'DOUBLE', 'NOT NULL', '0');
ChangeColumnType('width', 'labels', 'DOUBLE', 'NOT NULL', '0');
ChangeColumnType('topmargin', 'labels', 'DOUBLE', 'NOT NULL', '0');
ChangeColumnType('leftmargin', 'labels', 'DOUBLE', 'NOT NULL', '0');
ChangeColumnType('rowheight', 'labels', 'DOUBLE', 'NOT NULL', '0');
ChangeColumnType('columnwidth', 'labels', 'DOUBLE', 'NOT NULL', '0');
ChangeColumnType('vpos', 'labelfields', 'DOUBLE', 'NOT NULL', '0');
ChangeColumnType('hpos', 'labelfields', 'DOUBLE', 'NOT NULL', '0');

AddColumn('opencashdrawer', 'paymentmethods', 'TINYINT', 'NOT NULL', '0', 'usepreprintedstationery');

DropConstraint('bankaccounts', 'bankaccounts_ibfk_1');
DropConstraint('banktrans', 'banktrans_ibfk_2');

ChangeColumnType('accountcode', 'bankaccounts', 'varchar(20)', 'NOT NULL', '0');
ChangeColumnType('bankact', 'banktrans', 'varchar(20)', 'NOT NULL', '0');

AddConstraint('banktrans', 'banktrans_ibfk_2', 'bankact', 'bankaccounts', 'accountcode');

DropConstraint('chartdetails', 'chartdetails_ibfk_1');
DropPrimaryKey('chartdetails', array('accountcode', 'period'));
ChangeColumnType('accountcode', 'chartdetails', 'varchar(20)', 'NOT NULL', '0');
AddPrimaryKey('chartdetails', array('accountcode','period'));
DropConstraint('gltrans', 'gltrans_ibfk_1');
ChangeColumnType('account', 'gltrans', 'varchar(20)', 'NOT NULL', '0');
AddConstraint('gltrans', 'gltrans_ibfk_1', 'account', 'chartmaster', 'accountcode');
DropConstraint('pcexpenses', 'pcexpenses_ibfk_1');
ChangeColumnType('glaccount', 'pcexpenses', 'varchar(20)', 'NOT NULL', '0');
AddConstraint('pcexpenses', 'pcexpenses_ibfk_1', 'glaccount', 'chartmaster', 'accountcode');
DropConstraint('pctabs', 'pctabs_ibfk_5');
ChangeColumnType('glaccountassignment', 'pctabs', 'varchar(20)', 'NOT NULL', '0');
ChangeColumnType('glaccountpcash', 'pctabs', 'varchar(20)', 'NOT NULL', '0');
AddConstraint('pctabs', 'pctabs_ibfk_5', 'glaccountassignment', 'chartmaster', 'accountcode');
DropConstraint('taxauthorities', 'taxauthorities_ibfk_1');
ChangeColumnType('taxglcode', 'taxauthorities', 'varchar(20)', 'NOT NULL', '0');
AddConstraint('taxauthorities', 'taxauthorities_ibfk_1', 'taxglcode', 'chartmaster', 'accountcode');
DropConstraint('taxauthorities', 'taxauthorities_ibfk_2');
ChangeColumnType('purchtaxglaccount', 'taxauthorities', 'varchar(20)', 'NOT NULL', '0');
AddConstraint('taxauthorities', 'taxauthorities_ibfk_2', 'purchtaxglaccount', 'chartmaster', 'accountcode');
AddConstraint('bankaccounts', 'bankaccounts_ibfk_1', 'accountcode', 'chartmaster', 'accountcode');
AddConstraint('chartdetails', 'chartdetails_ibfk_1', 'accountcode', 'chartmaster', 'accountcode');

NewScript('NoSalesItems.php', '2');

ChangeConfigValue('VersionNumber', '4.08');

UpdateDBNo(basename(__FILE__, '.php'));

?>