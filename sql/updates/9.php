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
AddColumn('appendfile', 'stockmaster', 'VARCHAR(40)', 'NOT NULL', "none", 'serialised', $db);

AddColumn('expirationdate', 'stockserialitems', 'DATETIME', 'NOT NULL', "0000-00-00", 'serialno', $db);
AddColumn('currcode', 'bankaccounts', 'CHAR( 3 )', 'NOT NULL', '', 'accountcode', $db);
AddIndex(array('currcode'), 'bankaccounts', 'currcode', $db);
ChangeColumnType('exrate', 'banktrans', 'DOUBLE', 'NOT NULL', '1.0', $db);
AddColumn('functionalexrate', 'banktrans', 'DOUBLE', 'NOT NULL', " 1", 'exrate', $db);

DropConstraint('worequirements', 'worequirements_ibfk_3', $db);

AddConstraint('worequirements', 'worequirements_ibfk_3', array('wo', 'parentstockid'), 'woitems', array('wo', 'stockid'), $db);

NewConfigValue('ProhibitNegativeStock','1', $db);

InsertRecord('systypes', array('typeid' ,'typename'), array('0', _('Journal - GL')), array('typeid' ,'typename'), array('0', _('Journal - GL')), $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('1', _('Payment - GL')), array('typeid' ,'typename'), array('1', _('Payment - GL')), $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('2', _('Receipt - GL')), array('typeid' ,'typename'), array('2', _('Receipt - GL')), $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('3', _('Standing Journal')), array('typeid' ,'typename'), array('3', _('Standing Journal')), $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('10', _('Sales Invoice')), array('typeid' ,'typename'), array('10', _('Sales Invoice')), $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('11', _('Credit Note')), array('typeid' ,'typename'), array('11', _('Credit Note')), $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('12', _('Receipt')), array('typeid' ,'typename'), array('12', _('Receipt')), $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('15', _('Journal - Debtors')), array('typeid' ,'typename'), array('15', _('Journal - Debtors')), $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('16', _('Location Transfer')), array('typeid' ,'typename'), array('16', _('Location Transfer')), $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('17', _('Stock Adjustment')), array('typeid' ,'typename'), array('17', _('Stock Adjustment')), $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('18', _('Purchase Order')), array('typeid' ,'typename'), array('18', _('Purchase Order')), $db);
InsertRecord('systypes', array('typeid', 'typename'), array('19', _('Picking List')), array('typeid', 'typename'), array('19', _('Picking List')), $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('20', _('Purchase Invoice')), array('typeid' ,'typename'), array('20', _('Purchase Invoice')), $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('21', _('Debit Note')), array('typeid' ,'typename'), array('21', _('Debit Note')), $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('22', _('Creditors Payment')), array('typeid' ,'typename'), array('22', _('Creditors Payment')), $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('23', _('Creditors Journal')), array('typeid' ,'typename'), array('23', _('Creditors Journal')), $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('25', _('Purchase Order Delivery')), array('typeid' ,'typename'), array('25', _('Purchase Order Delivery')), $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('26', _('Work Order Receipt')), array('typeid' ,'typename'), array('26', _('Work Order Receipt')), $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('28', _('Work Order Issue')), array('typeid' ,'typename'), array('28', _('Work Order Issue')), $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('29', _('Work Order Variance')), array('typeid' ,'typename'), array('29', _('Work Order Variance')), $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('30', _('Sales Order')), array('typeid' ,'typename'), array('30', _('Sales Order')), $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('31', _('Shipment Close')), array('typeid' ,'typename'), array('31', _('Shipment Close')), $db);
InsertRecord('systypes', array('typeid', 'typename'), array('32', _('Contract Close')), array('typeid', 'typename'), array('32', _('Contract Close')), $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('35', _('Cost Update')), array('typeid' ,'typename'), array('35', _('Cost Update')), $db);
InsertRecord('systypes', array('typeid', 'typename'), array('37', _('Tenders')), array('typeid', 'typename'), array('37', _('Tenders')), $db);
InsertRecord('systypes', array('typeid', 'typename'), array('38', _('Stock Requests')), array('typeid', 'typename'), array('38', _('Stock Requests')), $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('36', _('Exchange Difference')), array('typeid' ,'typename'), array('36', _('Exchange Difference')), $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('40', _('Work Order')), array('typeid' ,'typename'), array('40', _('Work Order')), $db);
InsertRecord('systypes', array('typeid', 'typename'), array('41', _('Asset Addition')), array('typeid', 'typename'), array('41', _('Asset Addition')), $db);
InsertRecord('systypes', array('typeid', 'typename'), array('42', _('Asset Category Change')), array('typeid', 'typename'), array('42', _('Asset Category Change')), $db);
InsertRecord('systypes', array('typeid', 'typename'), array('43', _('Delete w/down asset')), array('typeid', 'typename'), array('43', _('Delete w/down asset')), $db);
InsertRecord('systypes', array('typeid', 'typename'), array('44', _('Depreciation')), array('typeid', 'typename'), array('44', _('Depreciation')), $db);
InsertRecord('systypes', array('typeid', 'typename'), array('49', _('Import Fixed Assets')), array('typeid', 'typename'), array('49', _('Import Fixed Assets')), $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('50', _('Opening Balance')), array('typeid' ,'typename'), array('50', _('Opening Balance')), $db);
InsertRecord('systypes', array('typeid' ,'typename'), array('500', _('Auto Debtor Number')), array('typeid' ,'typename'), array('500', _('Auto Debtor Number')), $db);

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