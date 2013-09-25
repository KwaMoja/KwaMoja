<?php

CreateTable('taxcategories',
"CREATE TABLE taxcategories(
  taxcatid tinyint( 4 ) AUTO_INCREMENT NOT NULL ,
  taxcatname varchar( 30 ) NOT NULL ,
  PRIMARY KEY ( taxcatid )
)",
$db);

DropConstraint('taxauthlevels', 'taxauthlevels_ibfk_2', $db);

ChangeColumnName('dispatchtaxauthority', 'taxauthlevels', 'TINYINT( 4 )', 'NOT NULL', '1', 'dispatchtaxprovince', $db);
ChangeColumnName('level', 'taxauthlevels', 'TINYINT( 4 )', 'NOT NULL', '0', 'taxcatid', $db);

DropIndex('taxauthlevels', 'dispatchtaxauthority', $db);
AddIndex(array('dispatchtaxprovince'), 'taxauthlevels', 'dispatchtaxprovince', $db);
AddIndex(array('taxcatid'), 'taxauthlevels', 'taxcatid', $db);

InsertRecord('taxcategories', array('taxcatid', 'taxcatname'), array('1', 'Taxable supply'), array('taxcatid', 'taxcatname'), array('1', 'Taxable supply'), $db);
InsertRecord('taxcategories', array('taxcatid', 'taxcatname'), array('2', 'Luxury Items'), array('taxcatid', 'taxcatname'), array('2', 'Luxury Items'), $db);
InsertRecord('taxcategories', array('taxcatid', 'taxcatname'), array('0', 'Exempt'), array('taxcatid', 'taxcatname'), array('0', 'Exempt'), $db);

AddConstraint('taxauthlevels', 'taxcatid_ibfk_1', 'taxcatid', 'taxcategories', 'taxcatid', $db);

CreateTable('taxprovinces',
"CREATE TABLE taxprovinces(
  taxprovinceid tinyint( 4 ) AUTO_INCREMENT NOT NULL ,
  taxprovincename varchar( 30 ) NOT NULL ,
  PRIMARY KEY ( taxprovinceid )
)",
$db);

ChangeColumnName('taxauthority', 'locations', 'TINYINT(4)', 'NOT NULL', '1', 'taxprovinceid', $db);

AddIndex(array('taxprovinceid'), 'locations', 'taxprovinceid', $db);
//InsertRecord('taxprovinces', array('taxprovinceid', 'taxprovincename'), array(NULL, 'Default Tax province'), array('taxprovinceid', 'taxprovincename'), array(NULL, 'Default Tax province'), $db);
AddConstraint('locations', 'locations_ibfk_1', 'taxprovinceid', 'taxprovinces', 'taxprovinceid', $db);

CreateTable('taxgroups',
"CREATE TABLE taxgroups (
  taxgroupid tinyint(4) auto_increment NOT NULL,
  taxgroupdescription varchar(30) NOT NULL,
  PRIMARY KEY(taxgroupid)
)",
$db);

CreateTable('taxgrouptaxes',
"CREATE TABLE taxgrouptaxes (
  taxgroupid tinyint(4) NOT NULL,
  taxauthid tinyint(4) NOT NULL,
  calculationorder tinyint(4) NOT NULL,
  taxontax tinyint(4) DEFAULT 0 NOT NULL,
  PRIMARY KEY(taxgroupid, taxauthid )
)",
$db);

AddIndex(array('taxgroupid'), 'taxgrouptaxes', 'taxgroupid', $db);
AddIndex(array('taxauthid'), 'taxgrouptaxes', 'taxauthid', $db);
AddConstraint('taxgrouptaxes', 'taxgrouptaxes_ibfk_1', 'taxgroupid', 'taxgroups', 'taxgroupid', $db);
AddConstraint('taxgrouptaxes', 'taxgrouptaxes_ibfk_2', 'taxauthid', 'taxauthorities', 'taxid', $db);

CreateTable('stockmovestaxes',
"CREATE TABLE stockmovestaxes (
	stkmoveno int NOT NULL,
	taxauthid tinyint NOT NULL,
	taxontax TINYINT DEFAULT 0 NOT NULL,
	taxcalculationorder TINYINT NOT NULL,
	taxrate double DEFAULT 0 NOT NULL,
	PRIMARY KEY (stkmoveno,taxauthid),
	KEY (taxauthid),
	KEY (taxcalculationorder)
)",
$db);

AddConstraint('stockmovestaxes', 'stockmovestaxes_ibfk_1', 'taxauthid', 'taxauthorities', 'taxid', $db);
DropColumn('taxrate', 'stockmoves', $db);

CreateTable('debtortranstaxes',
"CREATE TABLE debtortranstaxes (
	`debtortransid` INT NOT NULL ,
	`taxauthid` TINYINT NOT NULL ,
	`taxamount` DOUBLE NOT NULL,
	PRIMARY KEY(debtortransid, taxauthid),
	KEY (taxauthid)
)",
$db);
AddConstraint('debtortranstaxes', 'debtortranstaxes_ibfk_1', 'taxauthid', 'taxauthorities', 'taxid', $db);
AddConstraint('debtortranstaxes', 'debtortranstaxes_ibfk_2', 'debtortransid', 'debtortrans', 'id', $db);

DropConstraint('custbranch', 'custbranch_ibfk_5', $db);
ChangeColumnName('taxauthority', 'custbranch', 'TINYINT(4)', 'NOT NULL', '1', 'taxgroupid', $db);
DropIndex('custbranch', 'area_2', $db);
DropIndex('custbranch', 'taxauthority', $db);
AddIndex(array('taxgroupid'), 'custbranch', 'taxgroupid', $db);
//InsertRecord('taxgroups', array('taxgroupid', 'taxgroupdescription'), array(NULL,'Default tax group'), array('taxgroupid', 'taxgroupdescription'), array(NULL,'Default tax group'), $db);
AddConstraint('custbranch', 'custbranch_ibfk_7', 'taxgroupid', 'taxgroups', 'taxgroupid', $db);

RenameTable('taxauthlevels', 'taxauthrates', $db);
AddConstraint('taxauthrates', 'taxauthrates_ibfk_1', 'dispatchtaxprovince', 'taxprovinces', 'taxprovinceid', $db);

ChangeColumnName('taxlevel', 'stockmaster', 'TINYINT(4)', 'NOT NULL', '1', 'taxcatid', $db);
AddIndex( array('taxcatid'), 'stockmaster', 'stockmaster_ibix_1', $db);
AddConstraint('stockmaster', 'stockmaster_ibfk_2', 'taxcatid', 'taxcategories', 'taxcatid', $db);

DropPrimaryKey('salesorderdetails', array('orderno','stkcode'), $db);
AddColumn('orderlineno', 'salesorderdetails', 'INT(11)', 'NOT NULL', '0', 'orderno', $db);
AddPrimaryKey('salesorderdetails', array('orderno','orderlineno'), $db);

NewConfigValue('FreightTaxCategory','1', $db);
NewConfigValue('SO_AllowSameItemMultipleTimes','1', $db);

CreateTable('supptranstaxes',
"CREATE TABLE `supptranstaxes` (
  `supptransid` int(11) NOT NULL default '0',
  `taxauthid` tinyint(4) NOT NULL default '0',
  `taxamount` double NOT NULL default '0',
  PRIMARY KEY  (`supptransid`,`taxauthid`),
  KEY `taxauthid` (`taxauthid`),
  CONSTRAINT `supptranstaxes_ibfk_1` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`),
  CONSTRAINT `supptranstaxes_ibfk_2` FOREIGN KEY (`supptransid`) REFERENCES `supptrans` (`id`)
)",
$db);

DropConstraint('suppliers', 'suppliers_ibfk_3', $db);
ChangeColumnName('taxauthority', 'suppliers', 'TINYINT', 'NOT NULL', '1', 'taxgroupid', $db);
DropIndex('suppliers', 'taxauthority', $db);
AddIndex(array('taxgroupid'),'suppliers', 'taxgroupid', $db);
AddConstraint('suppliers', 'suppliers_ibfk_3', 'taxgroupid', 'taxgroups', 'taxgroupid', $db);

AddColumn('managed', 'locations', 'TINYINT', 'NOT NULL', '0', 'taxprovinceid', $db);


UpdateDBNo(basename(__FILE__, '.php'), $db);

?>