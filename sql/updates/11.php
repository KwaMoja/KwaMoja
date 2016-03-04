<?php

CreateTable('mrpdemandtypes',
"CREATE TABLE `mrpdemandtypes` (
  `mrpdemandtype` varchar(6) NOT NULL default '',
  `description` char(30) NOT NULL default '',
  PRIMARY KEY  (`mrpdemandtype`),
  KEY `mrpdemandtype` (`mrpdemandtype`)
)");

CreateTable('mrpdemands',
"CREATE TABLE `mrpdemands` (
  `demandid` int(11) NOT NULL AUTO_INCREMENT,
  `stockid` varchar(20) NOT NULL default '',
  `mrpdemandtype` varchar(6) NOT NULL default '',
  `quantity` double NOT NULL default '0',
  `duedate` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`demandid`),
  KEY `StockID` (`stockid`)
)");

AddConstraint('mrpdemands', 'mrpdemands_ibfk_1', 'mrpdemandtype', 'mrpdemandtypes', 'mrpdemandtype');
AddConstraint('mrpdemands', 'mrpdemands_ibfk_2', 'stockid', 'stockmaster', 'stockid');

AddColumn('pansize', 'stockmaster', 'DOUBLE', 'NOT NULL', '0.0', 'decimalplaces');
AddColumn('shrinkfactor', 'stockmaster', 'DOUBLE', 'NOT NULL', '0.0', 'pansize');

CreateTable('mrpcalendar',
"CREATE TABLE `mrpcalendar` (
	calendardate date NOT NULL,
	daynumber int(6) NOT NULL,
	manufacturingflag smallint(6) NOT NULL default '1',
	INDEX (daynumber),
	PRIMARY KEY (calendardate)
)");

InsertRecord('mrpdemandtypes', array('mrpdemandtype', 'description'), array('FOR','Forecast'), array('mrpdemandtype', 'description'), array('FOR','Forecast'));

AddPrimaryKey('geocode_param', array('geocodeid'));
ChangeColumnName('geocodeid', 'geocode_param', 'TINYINT', 'NOT NULL', '0', 'geocodeid', 'autoincrement');
AddIndex(array('coyname'), 'factorcompanies', 'factor_name');

AddColumn('currcode', 'bankaccounts', 'CHAR(3)', 'NOT NULL', '', 'accountcode');

ChangeColumnType('role', 'custcontacts', 'VARCHAR(40)', 'NOT NULL', '');
ChangeColumnType('phoneno', 'custcontacts', 'VARCHAR(20)', 'NOT NULL', '');
ChangeColumnType('notes', 'custcontacts', 'VARCHAR(255)', 'NOT NULL', '');

DropPrimaryKey('purchdata', array('supplierno','stockid'));
AddPrimaryKey('purchdata', array('supplierno','stockid', 'effectivefrom'));

AddColumn('quotedate', 'salesorders', 'DATE', 'NOT NULL', "0000-00-00", 'quotation');
AddColumn('confirmeddate', 'salesorders', 'DATE', 'NOT NULL', "0000-00-00", 'deliverydate');

CreateTable('woserialnos',
"CREATE TABLE `woserialnos` (
	`wo` INT NOT NULL ,
	`stockid` VARCHAR( 20 ) NOT NULL ,
	`serialno` VARCHAR( 30 ) NOT NULL ,
	`quantity` DOUBLE NOT NULL DEFAULT '1',
	`qualitytext` TEXT NOT NULL,
	 PRIMARY KEY (`wo`,`stockid`,`serialno`)
)");

NewConfigValue('AutoCreateWOs', 1);
NewConfigValue('DefaultFactoryLocation','MEL');
NewConfigValue('FactoryManagerEmail','manager@company.com');
NewConfigValue('DefineControlledOnWOEntry', '1');

AddColumn('nextserialno', 'stockmaster', 'BIGINT', 'NOT NULL', "0", 'shrinkfactor');
AddColumn('qualitytext', 'stockserialitems', 'TEXT', 'NOT NULL', '', 'quantity');
ChangeColumnType('orderno', 'salesorders', 'INT( 11 )', 'NOT NULL', '');

CreateTable('purchorderauth',
"CREATE TABLE `purchorderauth` (
	`userid` varchar(20) NOT NULL DEFAULT '',
	`currabrev` char(3) NOT NULL DEFAULT '',
	`cancreate` smallint(2) NOT NULL DEFAULT 0,
	`authlevel` int(11) NOT NULL DEFAULT 0,
	PRIMARY KEY (`userid`,`currabrev`)
)");

AddColumn('version', 'purchorders', 'DECIMAL(3,2)', 'NOT NULL', "1.0", 'contact');
AddColumn('revised', 'purchorders', 'DATE', 'NOT NULL', "0000-00-00", 'version');
AddColumn('realorderno', 'purchorders', 'VARCHAR(16)', 'NOT NULL', '', 'revised');
AddColumn('deliveryby', 'purchorders', 'VARCHAR(100)', 'NOT NULL', '', 'realorderno');
AddColumn('deliverydate', 'purchorders', 'DATE', 'NOT NULL', "0000-00-00", 'deliveryby');
AddColumn('status', 'purchorders', 'VARCHAR(12)', 'NOT NULL', '', 'deliverydate');
AddColumn('stat_comment', 'purchorders', 'TEXT', 'NOT NULL', '', 'status');

AddColumn('itemno', 'purchorderdetails', 'VARCHAR(50)', 'NOT NULL', '', 'itemcode');
AddColumn('uom', 'purchorderdetails', 'VARCHAR(50)', 'NOT NULL', '', 'itemno');
AddColumn('subtotal_amount', 'purchorderdetails', 'VARCHAR(50)', 'NOT NULL', '', 'uom');
AddColumn('package', 'purchorderdetails', 'VARCHAR(50)', 'NOT NULL', '', 'subtotal_amount');
AddColumn('pcunit', 'purchorderdetails', 'VARCHAR(50)', 'NOT NULL', '', 'package');
AddColumn('nw', 'purchorderdetails', 'VARCHAR(50)', 'NOT NULL', '', 'pcunit');
AddColumn('suppliers_partno', 'purchorderdetails', 'VARCHAR(50)', 'NOT NULL', '', 'nw');
AddColumn('gw', 'purchorderdetails', 'VARCHAR(50)', 'NOT NULL', '', 'suppliers_partno');
AddColumn('cuft', 'purchorderdetails', 'VARCHAR(50)', 'NOT NULL', '', 'gw');
AddColumn('total_quantity', 'purchorderdetails', 'VARCHAR(50)', 'NOT NULL', '', 'cuft');
AddColumn('total_amount', 'purchorderdetails', 'VARCHAR(50)', 'NOT NULL', '', 'total_quantity');

AddColumn('phn', 'suppliers', 'VARCHAR(50)', 'NOT NULL', '', 'taxref');
AddColumn('port', 'suppliers', 'VARCHAR(50)', 'NOT NULL', '', 'phn');

AddColumn('netweight', 'stockmaster', 'DECIMAL(20,4)', 'NOT NULL', "0.0", 'nextserialno');
AddColumn('suppliers_partno', 'purchdata', 'VARCHAR(50)', 'NOT NULL', '', 'effectivefrom');

ChangeColumnType('note', 'custnotes', 'TEXT', 'NOT NULL', '');

AddColumn('bankaccountcode', 'bankaccounts', 'VARCHAR(50)', 'NOT NULL', '', 'currcode');
AddColumn('invoice', 'bankaccounts', 'SMALLINT(2)', 'NOT NULL', "0", 'bankaccountcode');

AddColumn('salesman', 'www_users', 'CHAR( 3 )', 'NOT NULL', '', 'customerid');

ChangeColumnType('shipvia', 'debtortrans', 'INT(11)', 'NOT NULL', '0');

CreateTable('audittrail',
"CREATE TABLE IF NOT EXISTS `audittrail` (
  `transactiondate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `userid` varchar(20) NOT NULL DEFAULT '',
  `querystring` text,
  KEY `UserID` (`userid`)
)");

AddConstraint('audittrail', 'audittrail_ibfk_1', 'userid', 'www_users', 'userid');

CreateTable('deliverynotes',
"CREATE TABLE IF NOT EXISTS `deliverynotes` (
  `deliverynotenumber` int(11) NOT NULL,
  `deliverynotelineno` tinyint(4) NOT NULL,
  `salesorderno` int(11) NOT NULL,
  `salesorderlineno` int(11) NOT NULL,
  `qtydelivered` double NOT NULL DEFAULT '0',
  `printed` tinyint(4) NOT NULL DEFAULT '0',
  `invoiced` tinyint(4) NOT NULL DEFAULT '0',
  `deliverydate` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`deliverynotenumber`,`deliverynotelineno`)
)");

AddConstraint('deliverynotes', 'deliverynotes_ibfk_1', array('salesorderno', 'salesorderlineno'), 'salesorderdetails', array('orderno', 'orderlineno'));

UpdateDBNo(basename(__FILE__, '.php'));

?>