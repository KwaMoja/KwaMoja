<?php

CreateTable('custcontacts',
"CREATE TABLE `custcontacts` (
  `contid` int(11) NOT NULL auto_increment,
  `debtorno` varchar(10) NOT NULL,
  `contactname` varchar(40) NOT NULL,
  `role` varchar(40) NOT NULL,
  `phoneno` varchar(20) NOT NULL,
  `notes` varchar(255) NOT NULL,
  PRIMARY KEY  (`contid`)
)",
$db);

AddColumn('taxref', 'suppliers', 'VARCHAR(20)', 'NOT NULL', '', 'factorcompanyid', $db);

CreateTable('tags',
"CREATE TABLE `tags` (
`tagref` tinyint(4) NOT NULL auto_increment,
`tagdescription` varchar(50) NOT NULL,
PRIMARY KEY (`tagref`)
)",
$db);

AddColumn('tag', 'gltrans', 'TINYINT(4)', 'NOT NULL', "0", 'jobref', $db);

DropColumn('vtiger_accountid', 'custbranch', $db);
DropColumn('vtiger_accountid', 'salesorders', $db);
DropColumn('vtiger_productid', 'stockmaster', $db);
DeleteConfigValue('vtiger_integration', $db);

AddColumn('lat', 'custbranch', 'FLOAT( 10, 6 )', 'NOT NULL', "0.0", 'braddress6', $db);
AddColumn('lng', 'custbranch', 'FLOAT( 10, 6 )', 'NOT NULL', "0.0", 'lat', $db);
AddColumn('lat', 'suppliers', 'FLOAT( 10, 6 )', 'NOT NULL', "0.0", 'address6', $db);
AddColumn('lng', 'suppliers', 'FLOAT( 10, 6 )', 'NOT NULL', "0.0", 'lat', $db);

CreateTable('geocode_param',
"CREATE TABLE `geocode_param` (
 `geocodeid` varchar(4) NOT NULL default '',
 `geocode_key` varchar(200) NOT NULL default '',
 `center_long` varchar(20) NOT NULL default '',
 `center_lat` varchar(20) NOT NULL default '',
 `map_height` varchar(10) NOT NULL default '',
 `map_width` varchar(10) NOT NULL default '',
 `map_host` varchar(50) NOT NULL default ''
)",
$db);

NewConfigValue('geocode_integration', '0', $db);

NewConfigValue('DefaultCustomerType', '1', $db);

CreateTable('debtortype',
"CREATE TABLE `debtortype` (
`typeid` tinyint(4) NOT NULL auto_increment,
`typename` varchar(100) NOT NULL,
PRIMARY KEY (`typeid`)
)",
$db);

InsertRecord('debtortype', array( 'typeid' , 'typename' ), array(NULL, 'Default'), array( 'typeid' , 'typename' ), array(NULL, 'Default'), $db);

AddColumn('typeid', 'debtorsmaster', 'TINYINT(4)', 'NOT NULL', "1", 'customerpoline', $db);
AddConstraint('debtorsmaster', 'debtorsmaster_ibfk_5', 'typeid', 'debtortype', 'typeid', $db);

AddColumn('effectivefrom', 'purchdata', 'DATE', 'NOT NULL', "0000-00-00", 'preferred', $db);

CreateTable('debtortypenotes',
"CREATE TABLE `debtortypenotes` (
`noteid` tinyint(4) NOT NULL auto_increment,
`typeid` tinyint(4) NOT NULL default '0',
`href` varchar(100) NOT NULL,
`note` varchar(200) NOT NULL,
`date` date NOT NULL default '0000-00-00',
`priority` varchar(20) NOT NULL,
PRIMARY KEY (`noteid`)
)",
$db);

CreateTable('custnotes',
"CREATE TABLE `custnotes` (
`noteid` tinyint(4) NOT NULL auto_increment,
`debtorno` varchar(10) NOT NULL default '0',
`href` varchar(100) NOT NULL,
`note` varchar(200) NOT NULL,
`date` date NOT NULL default '0000-00-00',
`priority` varchar(20) NOT NULL,
PRIMARY KEY (`noteid`)
)",
$db);

NewConfigValue('Extended_CustomerInfo', '0', $db);
NewConfigValue('Extended_SupplierInfo', '0', $db);

ChangeColumnType('area', 'salesglpostings', 'VARCHAR(3)', 'NOT NULL', '', $db);
ChangeColumnType('area', 'salesanalysis', 'VARCHAR(3)', 'NOT NULL', '', $db);
ChangeColumnType('trandate', 'debtortrans', 'DATE', 'NOT NULL', '0000-00-00', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>