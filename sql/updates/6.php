<?php

ChangeColumnType('area', 'custbranch', 'CHAR(3)', 'NOT NULL', '');
AddColumn('specialinstructions', 'custbranch', 'TEXT', 'NOT NULL', '', 'brpostaddr6');
AddColumn('parentgroupname', 'accountgroups', 'VARCHAR(30)', 'NOT NULL', '', 'sequenceintb');

DropTable('worksorders', 'accumvalueissued');

CreateTable('workorders',
"CREATE TABLE `workorders` (
  wo int(11) NOT NULL,
  loccode char(5) NOT NULL default '',
  requiredby date NOT NULL default '0000-00-00',
  startdate date NOT NULL default '0000-00-00',
  costissued double NOT NULL default '0',
  closed tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`wo`),
  KEY LocCode (`loccode`),
  KEY StartDate (`startdate`),
  KEY RequiredBy (`requiredby`)
)");

AddConstraint('workorders', 'workorders_ibfk_1', 'loccode', 'locations', 'loccode');

CreateTable('woitems',
"CREATE TABLE `woitems` (
  wo int(11) NOT NULL,
  stockid char(20) NOT NULL default '',
  qtyreqd double NOT NULL DEFAULT 1,
  qtyrecd double NOT NULL DEFAULT 0,
  stdcost double NOT NULL,
  nextlotsnref varchar(20) DEFAULT '',
  PRIMARY KEY  (`wo`, `stockid`),
  KEY `stockid` (`stockid`)
)");

AddConstraint('woitems', 'woitems_ibfk_1', 'stockid', 'stockmaster', 'stockid');
AddConstraint('woitems', 'woitems_ibfk_2', 'wo', 'workorders', 'wo');

CreateTable('worequirements',
"CREATE TABLE `worequirements` (
  wo int(11) NOT NULL,
  parentstockid varchar(20) NOT NULL,
  stockid varchar(20) NOT NULL,
  qtypu double NOT NULL DEFAULT 1,
  stdcost double NOT NULL DEFAULT 0,
  autoissue tinyint NOT NULL DEFAULT 0,
   PRIMARY KEY  (`wo`, `parentstockid`,`stockid`)
)");

AddConstraint('worequirements', 'worequirements_ibfk_1', 'wo', 'workorders', 'wo');
AddConstraint('worequirements', 'worequirements_ibfk_2', 'stockid', 'stockmaster', 'stockid');
AddConstraint('worequirements', 'worequirements_ibfk_3', 'parentstockid', 'woitems', 'stockid');

AddColumn('autoissue', 'bom', 'TINYINT', 'NOT NULL', '0', 'quantity');

NewConfigValue('AutoIssue', '1');

DropIndex('stockmoves', 'StockID');
AddIndex(array('reference'), 'stockmoves', 'stockmoves');
DropPrimaryKey('recurrsalesorderdetails', array('recurrorderno','stkcode'));


UpdateDBNo(basename(__FILE__, '.php'));

?>