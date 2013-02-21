<?php

executeSQL("INSERT INTO securitytokens VALUES(0, 'Main Index Page')", $db);
executeSQL("INSERT INTO securitygroups (SELECT secroleid,0 FROM securityroles)", $db);

NewScript('reportwriter/admin/ReportCreator.php', '15', $db);
NewScript('RecurringSalesOrdersProcess.php', '1', $db);

RemoveScript('Z_CopyBOM.php', $db);
NewScript('CopyBOM.php', 15, $db);

CreateTable('departments',
"CREATE TABLE departments (
`departmentid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`description` VARCHAR (100) NOT NULL DEFAULT '',
`authoriser` varchar (20) NOT NULL DEFAULT ''
)",
$db);

CreateTable('stockrequest',
"CREATE TABLE stockrequest (
`dispatchid` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`loccode` VARCHAR (5) NOT NULL DEFAULT '',
`departmentid` INT NOT NULL DEFAULT 0,
`despatchdate` DATE NOT NULL DEFAULT '0000-00-00',
`authorised` TINYINT NOT NULL DEFAULT 0,
`closed` TINYINT NOT NULL DEFAULT 0,
`narrative` TEXT NOT NULL
)",
$db);

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
)",
$db);

NewScript('Departments.php', '1', $db);
NewScript('InternalStockRequest.php', '1', $db);
NewScript('InternalStockRequestFulfill.php', '1', $db);
NewScript('InternalStockRequestAuthorisation.php', '1', $db);

AddColumn('issueglact', 'stockcategory', 'INT', 'NOT NULL', '0', 'adjglact', $db);
executeSQL("UPDATE `stockcategory` SET `issueglact`=`adjglact`", $db);
InsertRecord('systypes', array('typeid', 'typename'), array('38', 'Stock Requests'), array('typeid', 'typename'), array('38', 'Stock Requests'), $db);

NewConfigValue('ShowStockidOnImages','0', $db);

NewScript('SupplierPriceList.php', '4', $db);

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
)",
$db);

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
)",
$db);

AddIndex(array('locationname'), 'locations', 'locationname', $db);

ChangeColumnName('papersize', 'labels', 'DOUBLE', 'NOT NULL', '0', 'pagewidth', $db);
AddColumn('pageheight', 'labels', 'DOUBLE', 'NOT NULL', '0', 'pagewidth', $db);
ChangeColumnType('height', 'labels', 'DOUBLE', 'NOT NULL', '0', $db);
ChangeColumnType('width', 'labels', 'DOUBLE', 'NOT NULL', '0', $db);
ChangeColumnType('topmargin', 'labels', 'DOUBLE', 'NOT NULL', '0', $db);
ChangeColumnType('leftmargin', 'labels', 'DOUBLE', 'NOT NULL', '0', $db);
ChangeColumnType('rowheight', 'labels', 'DOUBLE', 'NOT NULL', '0', $db);
ChangeColumnType('columnwidth', 'labels', 'DOUBLE', 'NOT NULL', '0', $db);
ChangeColumnType('vpos', 'labelfields', 'DOUBLE', 'NOT NULL', '0', $db);
ChangeColumnType('hpos', 'labelfields', 'DOUBLE', 'NOT NULL', '0', $db);

AddColumn('opencashdrawer', 'paymentmethods', 'TINYINT', 'NOT NULL', '0', 'usepreprintedstationery', $db);

DropConstraint('bankaccounts', 'bankaccounts_ibfk_1', $db);
DropConstraint('banktrans', 'banktrans_ibfk_2', $db);

ChangeColumnType('accountcode', 'bankaccounts', 'varchar(20)', 'NOT NULL', '0', $db);
ChangeColumnType('bankact', 'banktrans', 'varchar(20)', 'NOT NULL', '0', $db);

AddConstraint('banktrans', 'banktrans_ibfk_2', 'bankact', 'bankaccounts', 'accountcode', $db);

DropConstraint('chartdetails', 'chartdetails_ibfk_1', $db);
DropPrimaryKey('chartdetails', array('accountcode', 'period'), $db);
ChangeColumnType('accountcode', 'chartdetails', 'varchar(20)', 'NOT NULL', '0', $db);
AddPrimaryKey('chartdetails', array('accountcode','period'), $db);
DropConstraint('gltrans', 'gltrans_ibfk_1', $db);
ChangeColumnType('account', 'gltrans', 'varchar(20)', 'NOT NULL', '0', $db);
AddConstraint('gltrans', 'gltrans_ibfk_1', 'account', 'chartmaster', 'accountcode', $db);
DropConstraint('pcexpenses', 'pcexpenses_ibfk_1', $db);
ChangeColumnType('glaccount', 'pcexpenses', 'varchar(20)', 'NOT NULL', '0', $db);
AddConstraint('pcexpenses', 'pcexpenses_ibfk_1', 'glaccount', 'chartmaster', 'accountcode', $db);
DropConstraint('pctabs', 'pctabs_ibfk_5', $db);
ChangeColumnType('glaccountassignment', 'pctabs', 'varchar(20)', 'NOT NULL', '0', $db);
ChangeColumnType('glaccountpcash', 'pctabs', 'varchar(20)', 'NOT NULL', '0', $db);
AddConstraint('pctabs', 'pctabs_ibfk_5', 'glaccountassignment', 'chartmaster', 'accountcode', $db);
DropConstraint('taxauthorities', 'taxauthorities_ibfk_1', $db);
ChangeColumnType('taxglcode', 'taxauthorities', 'varchar(20)', 'NOT NULL', '0', $db);
AddConstraint('taxauthorities', 'taxauthorities_ibfk_1', 'taxglcode', 'chartmaster', 'accountcode', $db);
DropConstraint('taxauthorities', 'taxauthorities_ibfk_2', $db);
ChangeColumnType('purchtaxglaccount', 'taxauthorities', 'varchar(20)', 'NOT NULL', '0', $db);
AddConstraint('taxauthorities', 'taxauthorities_ibfk_2', 'purchtaxglaccount', 'chartmaster', 'accountcode', $db);
ChangeColumnType('accountcode', 'chartmaster', 'varchar(20)', 'NOT NULL', '0', $db);
AddConstraint('bankaccounts', 'bankaccounts_ibfk_1', 'accountcode', 'chartmaster', 'accountcode', $db);
AddConstraint('chartdetails', 'chartdetails_ibfk_1', 'accountcode', 'chartmaster', 'accountcode', $db);

NewScript('NoSalesItems.php',  '2',  $db);

ChangeConfigValue('VersionNumber', '4.08', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>