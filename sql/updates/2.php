<?php

CreateTable('taxcategories',
"CREATE TABLE taxcategories(
  taxcatid tinyint( 4 ) AUTO_INCREMENT NOT NULL ,
  taxcatname varchar( 30 ) NOT NULL ,
  PRIMARY KEY ( taxcatid )
)");

DropConstraint('taxauthlevels', 'taxauthlevels_ibfk_2');

ChangeColumnName('dispatchtaxauthority', 'taxauthlevels', 'TINYINT( 4 )', 'NOT NULL', '1', 'dispatchtaxprovince');
ChangeColumnName('level', 'taxauthlevels', 'TINYINT( 4 )', 'NOT NULL', '0', 'taxcatid');

DropIndex('taxauthlevels', 'dispatchtaxauthority');
AddIndex(array('dispatchtaxprovince'), 'taxauthlevels', 'dispatchtaxprovince');
AddIndex(array('taxcatid'), 'taxauthlevels', 'taxcatid');

InsertRecord('taxcategories', array('taxcatid', 'taxcatname'), array('1', 'Taxable supply'), array('taxcatid', 'taxcatname'), array('1', 'Taxable supply'));
InsertRecord('taxcategories', array('taxcatid', 'taxcatname'), array('2', 'Luxury Items'), array('taxcatid', 'taxcatname'), array('2', 'Luxury Items'));
InsertRecord('taxcategories', array('taxcatid', 'taxcatname'), array('0', 'Exempt'), array('taxcatid', 'taxcatname'), array('0', 'Exempt'));

AddConstraint('taxauthlevels', 'taxcatid_ibfk_1', 'taxcatid', 'taxcategories', 'taxcatid');

CreateTable('taxprovinces',
"CREATE TABLE taxprovinces(
  taxprovinceid tinyint( 4 ) AUTO_INCREMENT NOT NULL ,
  taxprovincename varchar( 30 ) NOT NULL ,
  PRIMARY KEY ( taxprovinceid )
)");

ChangeColumnName('taxauthority', 'locations', 'TINYINT(4)', 'NOT NULL', '1', 'taxprovinceid');

AddIndex(array('taxprovinceid'), 'locations', 'taxprovinceid');
//InsertRecord('taxprovinces', array('taxprovinceid', 'taxprovincename'), array(NULL, 'Default Tax province'), array('taxprovinceid', 'taxprovincename'), array(NULL, 'Default Tax province'));
AddConstraint('locations', 'locations_ibfk_1', 'taxprovinceid', 'taxprovinces', 'taxprovinceid');

CreateTable('taxgroups',
"CREATE TABLE taxgroups (
  taxgroupid tinyint(4) auto_increment NOT NULL,
  taxgroupdescription varchar(30) NOT NULL,
  PRIMARY KEY(taxgroupid)
)");

CreateTable('taxgrouptaxes',
"CREATE TABLE taxgrouptaxes (
  taxgroupid tinyint(4) NOT NULL,
  taxauthid tinyint(4) NOT NULL,
  calculationorder tinyint(4) NOT NULL,
  taxontax tinyint(4) DEFAULT 0 NOT NULL,
  PRIMARY KEY(taxgroupid, taxauthid )
)");

AddIndex(array('taxgroupid'), 'taxgrouptaxes', 'taxgroupid');
AddIndex(array('taxauthid'), 'taxgrouptaxes', 'taxauthid');
AddConstraint('taxgrouptaxes', 'taxgrouptaxes_ibfk_1', 'taxgroupid', 'taxgroups', 'taxgroupid');
AddConstraint('taxgrouptaxes', 'taxgrouptaxes_ibfk_2', 'taxauthid', 'taxauthorities', 'taxid');

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
)");

AddConstraint('stockmovestaxes', 'stockmovestaxes_ibfk_1', 'taxauthid', 'taxauthorities', 'taxid');
DropColumn('taxrate', 'stockmoves');

CreateTable('debtortranstaxes',
"CREATE TABLE debtortranstaxes (
	`debtortransid` INT NOT NULL ,
	`taxauthid` TINYINT NOT NULL ,
	`taxamount` DOUBLE NOT NULL,
	PRIMARY KEY(debtortransid, taxauthid),
	KEY (taxauthid)
)");
AddConstraint('debtortranstaxes', 'debtortranstaxes_ibfk_1', 'taxauthid', 'taxauthorities', 'taxid');
AddConstraint('debtortranstaxes', 'debtortranstaxes_ibfk_2', 'debtortransid', 'debtortrans', 'id');

DropConstraint('custbranch', 'custbranch_ibfk_5');
ChangeColumnName('taxauthority', 'custbranch', 'TINYINT(4)', 'NOT NULL', '1', 'taxgroupid');
DropIndex('custbranch', 'area_2');
DropIndex('custbranch', 'taxauthority');
AddIndex(array('taxgroupid'), 'custbranch', 'taxgroupid');
//InsertRecord('taxgroups', array('taxgroupid', 'taxgroupdescription'), array(NULL,'Default tax group'), array('taxgroupid', 'taxgroupdescription'), array(NULL,'Default tax group'));
AddConstraint('custbranch', 'custbranch_ibfk_7', 'taxgroupid', 'taxgroups', 'taxgroupid');

RenameTable('taxauthlevels', 'taxauthrates');
AddConstraint('taxauthrates', 'taxauthrates_ibfk_1', 'dispatchtaxprovince', 'taxprovinces', 'taxprovinceid');

ChangeColumnName('taxlevel', 'stockmaster', 'TINYINT(4)', 'NOT NULL', '1', 'taxcatid');
AddIndex( array('taxcatid'), 'stockmaster', 'stockmaster_ibix_1');
AddConstraint('stockmaster', 'stockmaster_ibfk_2', 'taxcatid', 'taxcategories', 'taxcatid');

DropPrimaryKey('salesorderdetails', array('orderno','stkcode'));
AddColumn('orderlineno', 'salesorderdetails', 'INT(11)', 'NOT NULL', '0', 'orderno');
AddPrimaryKey('salesorderdetails', array('orderno','orderlineno'));

NewConfigValue('FreightTaxCategory','1');
NewConfigValue('SO_AllowSameItemMultipleTimes','1');

CreateTable('supptranstaxes',
"CREATE TABLE `supptranstaxes` (
  `supptransid` int(11) NOT NULL default '0',
  `taxauthid` tinyint(4) NOT NULL default '0',
  `taxamount` double NOT NULL default '0',
  PRIMARY KEY  (`supptransid`,`taxauthid`),
  KEY `taxauthid` (`taxauthid`),
  CONSTRAINT `supptranstaxes_ibfk_1` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`),
  CONSTRAINT `supptranstaxes_ibfk_2` FOREIGN KEY (`supptransid`) REFERENCES `supptrans` (`id`)
)");

DropConstraint('suppliers', 'suppliers_ibfk_3');
ChangeColumnName('taxauthority', 'suppliers', 'TINYINT', 'NOT NULL', '1', 'taxgroupid');
DropIndex('suppliers', 'taxauthority');
AddIndex(array('taxgroupid'),'suppliers', 'taxgroupid');
AddConstraint('suppliers', 'suppliers_ibfk_3', 'taxgroupid', 'taxgroups', 'taxgroupid');

AddColumn('managed', 'locations', 'TINYINT', 'NOT NULL', '0', 'taxprovinceid');


UpdateDBNo(basename(__FILE__, '.php'));

?>