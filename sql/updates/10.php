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
)");

AddColumn('taxref', 'suppliers', 'VARCHAR(20)', 'NOT NULL', '', 'factorcompanyid');

CreateTable('tags',
"CREATE TABLE `tags` (
`tagref` tinyint(4) NOT NULL auto_increment,
`tagdescription` varchar(50) NOT NULL,
PRIMARY KEY (`tagref`)
)");

AddColumn('tag', 'gltrans', 'TINYINT(4)', 'NOT NULL', "0", 'jobref');

DropColumn('vtiger_accountid', 'custbranch');
DropColumn('vtiger_accountid', 'salesorders');
DropColumn('vtiger_productid', 'stockmaster');
DeleteConfigValue('vtiger_integration');

AddColumn('lat', 'custbranch', 'FLOAT( 10, 6 )', 'NOT NULL', "0.0", 'braddress6');
AddColumn('lng', 'custbranch', 'FLOAT( 10, 6 )', 'NOT NULL', "0.0", 'lat');
AddColumn('lat', 'suppliers', 'FLOAT( 10, 6 )', 'NOT NULL', "0.0", 'address6');
AddColumn('lng', 'suppliers', 'FLOAT( 10, 6 )', 'NOT NULL', "0.0", 'lat');

CreateTable('geocode_param',
"CREATE TABLE `geocode_param` (
 `geocodeid` varchar(4) NOT NULL default '',
 `geocode_key` varchar(200) NOT NULL default '',
 `center_long` varchar(20) NOT NULL default '',
 `center_lat` varchar(20) NOT NULL default '',
 `map_height` varchar(10) NOT NULL default '',
 `map_width` varchar(10) NOT NULL default '',
 `map_host` varchar(50) NOT NULL default ''
)");

NewConfigValue('geocode_integration', '0');

NewConfigValue('DefaultCustomerType', '1');

CreateTable('debtortype',
"CREATE TABLE `debtortype` (
`typeid` tinyint(4) NOT NULL auto_increment,
`typename` varchar(100) NOT NULL,
PRIMARY KEY (`typeid`)
)");

InsertRecord('debtortype', array( 'typeid' , 'typename' ), array(NULL, 'Default'), array( 'typeid' , 'typename' ), array(NULL, 'Default'));

AddColumn('typeid', 'debtorsmaster', 'TINYINT(4)', 'NOT NULL', "1", 'customerpoline');
AddConstraint('debtorsmaster', 'debtorsmaster_ibfk_5', 'typeid', 'debtortype', 'typeid');

AddColumn('effectivefrom', 'purchdata', 'DATE', 'NOT NULL', "0000-00-00", 'preferred');

CreateTable('debtortypenotes',
"CREATE TABLE `debtortypenotes` (
`noteid` tinyint(4) NOT NULL auto_increment,
`typeid` tinyint(4) NOT NULL default '0',
`href` varchar(100) NOT NULL,
`note` varchar(200) NOT NULL,
`date` date NOT NULL default '0000-00-00',
`priority` varchar(20) NOT NULL,
PRIMARY KEY (`noteid`)
)");

CreateTable('custnotes',
"CREATE TABLE `custnotes` (
`noteid` tinyint(4) NOT NULL auto_increment,
`debtorno` varchar(10) NOT NULL default '0',
`href` varchar(100) NOT NULL,
`note` varchar(200) NOT NULL,
`date` date NOT NULL default '0000-00-00',
`priority` varchar(20) NOT NULL,
PRIMARY KEY (`noteid`)
)");

NewConfigValue('Extended_CustomerInfo', '0');
NewConfigValue('Extended_SupplierInfo', '0');

ChangeColumnType('area', 'salesglpostings', 'VARCHAR(3)', 'NOT NULL', '');
ChangeColumnType('area', 'salesanalysis', 'VARCHAR(3)', 'NOT NULL', '');
ChangeColumnType('trandate', 'debtortrans', 'DATE', 'NOT NULL', '0000-00-00');

UpdateDBNo(basename(__FILE__, '.php'));

?>