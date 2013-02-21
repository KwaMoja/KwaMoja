<?php

ChangeColumnType('area', 'custbranch', 'CHAR(3)', 'NOT NULL', '', $db);
AddColumn('specialinstructions', 'custbranch', 'TEXT', 'NOT NULL', '', 'brpostaddr6', $db);
AddColumn('parentgroupname', 'accountgroups', 'VARCHAR(30)', 'NOT NULL', '', 'sequenceintb', $db);

DropTable('worksorders', 'accumvalueissued', $db);

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
)",
$db);

AddConstraint('workorders', 'workorders_ibfk_1', 'loccode', 'locations', 'loccode', $db);

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
)",
$db);

AddConstraint('woitems', 'woitems_ibfk_1', 'stockid', 'stockmaster', 'stockid', $db);
AddConstraint('woitems', 'woitems_ibfk_2', 'wo', 'workorders', 'wo', $db);

CreateTable('worequirements',
"CREATE TABLE `worequirements` (
  wo int(11) NOT NULL,
  parentstockid varchar(20) NOT NULL,
  stockid varchar(20) NOT NULL,
  qtypu double NOT NULL DEFAULT 1,
  stdcost double NOT NULL DEFAULT 0,
  autoissue tinyint NOT NULL DEFAULT 0,
   PRIMARY KEY  (`wo`, `parentstockid`,`stockid`)
)",
$db);

AddConstraint('worequirements', 'worequirements_ibfk_1', 'wo', 'workorders', 'wo', $db);
AddConstraint('worequirements', 'worequirements_ibfk_2', 'stockid', 'stockmaster', 'stockid', $db);
AddConstraint('worequirements', 'worequirements_ibfk_3', 'parentstockid', 'woitems', 'stockid', $db);

AddColumn('autoissue', 'bom', 'TINYINT', 'NOT NULL', '0', 'quantity', $db);

NewConfigValue('AutoIssue', '1', $db);

DropIndex('stockmoves', 'StockID', $db);
AddIndex(array('reference'), 'stockmoves', 'stockmoves', $db);
DropPrimaryKey('recurrsalesorderdetails', array('recurrorderno','stkcode'), $db);


UpdateDBNo(basename(__FILE__, '.php'), $db);

?>