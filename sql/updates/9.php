<?php

CreateTable('audittrail',
"CREATE TABLE `audittrail` (
	`transactiondate` datetime NOT NULL default '0000-00-00',
	`userid` varchar(20) NOT NULL default '',
	`querystring` text,
	KEY `UserID` (`userid`),
  CONSTRAINT `audittrail_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `www_users` (`userid`)
)",
$db);

ChangeColumnType('contactemail', 'salesorders', 'VARCHAR(40)', 'NOT NULL', '', $db);

NewConfigValue('MonthsAuditTrail', '1', $db);

CreateTable('factorcompanies',
"CREATE TABLE `factorcompanies` (
  `id` int(11) NOT NULL auto_increment,
  `coyname` varchar(50) NOT NULL default '',
  `address1` varchar(40) NOT NULL default '',
  `address2` varchar(40) NOT NULL default '',
  `address3` varchar(40) NOT NULL default '',
  `address4` varchar(40) NOT NULL default '',
  `address5` varchar(20) NOT NULL default '',
  `address6` varchar(15) NOT NULL default '',
  `contact` varchar(25) NOT NULL default '',
  `telephone` varchar(25) NOT NULL default '',
  `fax` varchar(25) NOT NULL default '',
  `email` varchar(55) NOT NULL default '',
  PRIMARY KEY  (`id`)
)",
$db);

InsertRecord('factorcompanies', array( 'id' , 'coyname' ), array(NULL, 'None'), array( 'id' , 'coyname' ), array(NULL, 'None'), $db);
AddColumn('factorcompanyid', 'suppliers', 'INT(11)', 'NOT NULL', '1', 'taxgroupid', $db);
AddConstraint('suppliers', 'suppliers_ibfk_4', 'factorcompanyid', 'factorcompanies', 'id', $db);

AddColumn('perishable', 'stockmaster', 'TINYINT(1)', 'NOT NULL', '0', 'serialised', $db);
AddColumn('appendfile', 'stockmaster', 'VARCHAR(40)', 'NOT NULL', "'none'", 'serialised', $db);

AddColumn('expirationdate', 'stockserialitems', 'DATETIME', 'NOT NULL', "'0000-00-00'", 'serialno', $db);
AddColumn('currcode', 'bankaccounts', 'CHAR( 3 )', 'NOT NULL', '', 'accountcode', $db);
AddIndex(array('currcode'), 'bankaccounts', 'currcode', $db);
ChangeColumnType('exrate', 'banktrans', 'DOUBLE', 'NOT NULL', '1.0', $db);
AddColumn('functionalexrate', 'banktrans', 'DOUBLE', 'NOT NULL', "'1'", 'exrate', $db);

DropConstraint('worequirements', 'worequirements_ibfk_3', $db);

AddConstraint('worequirements', 'worequirements_ibfk_3', array('wo', 'parentstockid'), 'woitems', array('wo', 'stockid'), $db);

NewConfigValue('ProhibitNegativeStock','1', $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('36', 'Exchange Difference'), array('typeid' ,'typename'), array('36', 'Exchange Difference'), $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('40', 'Work Order'), array('typeid' ,'typename'), array('40', 'Work Order'), $db);
NewConfigValue('UpdateCurrencyRatesDaily', '0', $db);

CreateTable('assetmanager',
"CREATE TABLE `assetmanager` (
  `id` int(11) NOT NULL auto_increment,
  `serialno` varchar(30) NOT NULL default '',
  `assetglcode` int(11) NOT NULL default '0',
  `depnglcode` int(11) NOT NULL default '0',
  `description` varchar(30) NOT NULL default '',
  `lifetime` int(11) NOT NULL default 0,
  `location` varchar(15) NOT NULL default '',
  `cost` double NOT NULL default 0.0,
  `depn` double NOT NULL default 0.0,
  PRIMARY KEY  (`id`)
)",
$db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>