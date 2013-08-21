<?php

/* Ensure that all tablse use the utf8_general_cli
 * character set
 */

$result=DB_show_tables($db);
while ($table = DB_fetch_array($result)) {
	if (CharacterSet($table[0], $db) != 'utf8_general_ci') {
		$response=executeSQL('ALTER TABLE '.$table[0].' CONVERT TO CHARACTER SET utf8', $db);
		if ($response==0) {
			OutputResult( _('The character set of').' '.$table[0].' '._('has been changed to utf8_general_ci') , 'success');
		} else {
			OutputResult( _('The character set of').' '.$table[0].' '._('could not be changed to utf8_general_ci') , 'error');
		}
	} else {
		OutputResult( _('The character set of').' '.$table[0].' '._('is already utf8_general_ci') , 'info');
	}
}

/* Increase the size of the language field in www_users to enable
 * utf languages to be accepted
 */

ChangeColumnSize('language', 'www_users', 'varchar(10)', 'NOT NULL', 'en_GB.utf8', 10, $db);

/* New config values for logging any prnMsg messages
 * Defines the patth and the messages to be logged
 */

NewConfigValue('LogPath', '', $db);
NewConfigValue('LogSeverity', '0', $db);

/* New config values for whether to show frequently ordered items
 * on order entry, and if so then how many months to show
 */

NewConfigValue('FrequentlyOrderedItems', '0', $db);
NewConfigValue('NumberOfMonthMustBeShown', '6', $db);

/* Add the field in the currencies that shows the number of decimal places to be shown for each currency */

AddColumn('decimalplaces', 'currencies', 'tinyint(3)', 'NOT NULL', '2', 'hundredsname', $db);

AddColumn('suppdeladdress1', 'purchorders', 'varchar(40)', 'NOT NULL', '', 'deladd6', $db);
AddColumn('suppdeladdress2', 'purchorders', 'varchar(40)', 'NOT NULL', '', 'suppdeladdress1', $db);
AddColumn('suppdeladdress3', 'purchorders', 'varchar(40)', 'NOT NULL', '', 'suppdeladdress2', $db);
AddColumn('suppdeladdress4', 'purchorders', 'varchar(20)', 'NOT NULL', '', 'suppdeladdress3', $db);
AddColumn('suppdeladdress5', 'purchorders', 'varchar(15)', 'NOT NULL', '', 'suppdeladdress4', $db);
AddColumn('suppdeladdress6', 'purchorders', 'varchar(30)', 'NOT NULL', '', 'suppdeladdress5', $db);
AddColumn('supptel', 'purchorders', 'varchar(30)', 'NOT NULL', '""', 'suppdeladdress6', $db);
AddColumn('tel', 'purchorders', 'varchar(15)', 'NOT NULL', '""', 'deladd6', $db);
AddColumn('paymentterms', 'purchorders', 'char(2)', 'NOT NULL', '""', 'stat_comment', $db);
AddColumn('port', 'purchorders', 'varchar(40)', 'NOT NULL', '""', 'paymentterms', $db);

/* Add column to www_users for the pdf language to be used */

AddColumn('pdflanguage', 'www_users', 'tinyint(1)', 'NOT NULL', '0', 'language', $db);

/* add a column to the purchase order authentication table for whether
 * the user is allowed to remove an invoice from hold
 */

AddColumn('offhold', 'purchorderauth', 'tinyint(1)', 'NOT NULL', '0', 'authlevel', $db);

/* Create all the tables required for the new petty cash module
 */

CreateTable("pcashdetails", "CREATE TABLE `pcashdetails` (
  `counterindex` int(20) NOT NULL AUTO_INCREMENT,
  `tabcode` varchar(20) NOT NULL,
  `date` date NOT NULL,
  `codeexpense` varchar(20) NOT NULL,
  `amount` double NOT NULL,
  `authorized` date NOT NULL COMMENT 'date cash assigment was revised and authorized by authorizer from tabs table',
  `posted` tinyint(4) NOT NULL COMMENT 'has (or has not) been posted into gltrans',
  `notes` text NOT NULL,
  `receipt` text COMMENT 'filename or path to scanned receipt or code of receipt to find physical receipt if tax guys or auditors show up',
  PRIMARY KEY (`counterindex`)
)", $db);

CreateTable("pcexpenses", "CREATE TABLE `pcexpenses` (
  `codeexpense` varchar(20) NOT NULL COMMENT 'code for the group',
  `description` varchar(50) NOT NULL COMMENT 'text description, e.g. meals, train tickets, fuel, etc',
  `glaccount` int(11) NOT NULL COMMENT 'GL related account',
  PRIMARY KEY (`codeexpense`),
  KEY (`glaccount`)
)", $db);

CreateTable("pctabexpenses", "CREATE TABLE `pctabexpenses` (
  `typetabcode` varchar(20) NOT NULL,
  `codeexpense` varchar(20) NOT NULL,
  KEY (`typetabcode`),
  KEY (`codeexpense`)
)", $db);

CreateTable("pctabs", "CREATE TABLE `pctabs` (
  `tabcode` varchar(20) NOT NULL,
  `usercode` varchar(20) NOT NULL COMMENT 'code of user employee from www_users',
  `typetabcode` varchar(20) NOT NULL,
  `currency` char(3) NOT NULL,
  `tablimit` double NOT NULL,
  `authorizer` varchar(20) NOT NULL COMMENT 'code of user from www_users',
  `glaccountassignment` int(11) NOT NULL COMMENT 'gl account where the money comes from',
  `glaccountpcash` int(11) NOT NULL,
  PRIMARY KEY (`tabcode`),
  KEY (`usercode`),
  KEY (`typetabcode`),
  KEY (`currency`),
  KEY (`authorizer`),
  KEY (`glaccountassignment`)
)", $db);

CreateTable("pctypetabs", "CREATE TABLE `pctypetabs` (
  `typetabcode` varchar(20) NOT NULL COMMENT 'code for the type of petty cash tab',
  `typetabdescription` varchar(50) NOT NULL COMMENT 'text description, e.g. tab for CEO',
  PRIMARY KEY (`typetabcode`)
)", $db);

AddConstraint('pcexpenses', 'pcexpenses_ibfk_1', 'glaccount', 'chartmaster', 'accountcode', $db);
AddConstraint('pctabexpenses', 'pctabexpenses_ibfk_1', 'typetabcode', 'pctypetabs', 'typetabcode', $db);
AddConstraint('pctabexpenses', 'pctabexpenses_ibfk_2', 'codeexpense', 'pcexpenses', 'codeexpense', $db);

AddConstraint('pctabs', 'pctabs_ibfk_1', 'usercode', 'www_users', 'userid', $db);
AddConstraint('pctabs', 'pctabs_ibfk_2', 'typetabcode', 'pctypetabs', 'typetabcode', $db);
AddConstraint('pctabs', 'pctabs_ibfk_3', 'currency', 'currencies', 'currabrev', $db);
AddConstraint('pctabs', 'pctabs_ibfk_4', 'authorizer', 'www_users', 'userid', $db);
AddConstraint('pctabs', 'pctabs_ibfk_5', 'glaccountassignment', 'chartmaster', 'accountcode', $db);

DropConstraint('suppliers', 'suppliers_ibfk_4', $db);

UpdateField('suppliers', 'factorcompanyid', 0, '`factorcompanyid`=1', $db);
DeleteRecords('factorcompanies', "coyname='None'", $db);

/* New security token for petty cash usage */

UpdateField('securitytokens', 'tokenname', 'Petty Cash', 'tokenid=6', $db);

/* Add input date to transaction tables so that transactions can be
 * reported on by their input date
 */

AddColumn('inputdate', 'supptrans', 'datetime', 'NOT NULL', '0000-00-00', 'duedate', $db);
AddColumn('inputdate', 'debtortrans', 'datetime', 'NOT NULL', '0000-00-00', 'trandate', $db);

/* Change the size of the fieldname field in the report writer as
 * the previous size was not big enough to hold all field names
 */

ChangeColumnSize('fieldname', 'reportfields', 'varchar(60)', 'NOT NULL', '', 60, $db);

/* Database changes needed for the picking list functionality
 */

NewConfigValue('RequirePickingNote', 0, $db);

CreateTable("pickinglists", "CREATE TABLE `pickinglists` (
  `pickinglistno` int(11) NOT NULL DEFAULT 0,
  `orderno` int(11) NOT NULL DEFAULT 0,
  `pickinglistdate` date NOT NULL default '0000-00-00',
  `dateprinted` date NOT NULL default '0000-00-00',
  `deliverynotedate` date NOT NULL default '0000-00-00',
  CONSTRAINT `pickinglists_ibfk_1` FOREIGN KEY (`orderno`) REFERENCES `salesorders` (`orderno`),
  PRIMARY KEY (`pickinglistno`)
)", $db);

CreateTable("pickinglistdetails", "CREATE TABLE `pickinglistdetails` (
  `pickinglistno` int(11) NOT NULL DEFAULT 0,
  `pickinglistlineno` int(11) NOT NULL DEFAULT 0,
  `orderlineno` int(11) NOT NULL DEFAULT 0,
  `qtyexpected` double NOT NULL default 0.00,
  `qtypicked` double NOT NULL default 0.00,
  CONSTRAINT `pickinglistdetails_ibfk_1` FOREIGN KEY (`pickinglistno`) REFERENCES `pickinglists` (`pickinglistno`),
  PRIMARY KEY (`pickinglistno`, `pickinglistlineno`)
)", $db);

InsertRecord('systypes', array('typeid', 'typename'), array(19, 'Picking List'), array('typeid', 'typename'), array(19, 'Picking List'), $db);

/* Database changes required to add start and end dates for sales prices
 */

AddColumn('startdate', 'prices', 'Date', 'NOT NULL', '0000-00-00', 'branchcode', $db);
AddColumn('enddate', 'prices', 'Date', 'NOT NULL', '9999-12-31', 'startdate', $db);

DropPrimaryKey('prices', array('stockid' , 'typeabbrev' , 'currabrev' , 'debtorno'), $db);
AddPrimaryKey('prices', array('stockid' , 'typeabbrev' , 'currabrev' , 'debtorno' , 'branchcode', 'startdate' , 'enddate'), $db);
UpdateField('prices', 'startdate', '1999-01-01', "stockid='%'", $db);
UpdateField('prices', 'enddate', '', "stockid='%'", $db);

/* Add in minimum order quantity field to the supplier purchasing data
 */

AddColumn('minorderqty', 'purchdata', 'int(11)', 'NOT NULL', '1', 'suppliers_partno', $db);

/* Add in field to record at what date the stock check freeze was made
 */

AddColumn('stockcheckdate', 'stockcheckfreeze', 'date', 'NOT NULL', '0000-00-00', 'qoh', $db);

/* Add extra columns for supplier head office details
 */

AddColumn('email', 'suppliers', 'varchar(55)', 'NOT NULL', '', 'port', $db);
AddColumn('fax', 'suppliers', 'varchar(25)', 'NOT NULL', '', 'email', $db);
AddColumn('telephone', 'suppliers', 'varchar(25)', 'NOT NULL', '', 'fax', $db);

/* Add extra database items needed for supplier only login
 */

AddColumn('supplierid', 'www_users', 'varchar(10)', 'NOT NULL', '', 'customerid', $db);
InsertRecord('securityroles', array('secroleid', 'secrolename'), array(9,'Supplier Log On Only'), array('secroleid', 'secrolename'), array(9,'Supplier Log On Only'), $db);
UpdateField('securitytokens', 'tokenname', 'Supplier centre - Supplier access only', 'tokenid=9', $db);
InsertRecord('securitygroups', array('secroleid', 'tokenid'), array(9,9), array('secroleid', 'tokenid'), array(9,9), $db);

/* add a field to each location giving a customer/branch combination
 * that can be used for cash sales at that location
 */

AddColumn('cashsalecustomer', 'locations', 'varchar(21)', 'NOT NULL', '', 'taxprovinceid', $db);

/* New database components required for contracts module
 */

DropTable('contracts', 'rate', $db);
DropTable('contractreqts', 'component', $db);
DropTable('contractbom', 'component', $db);

CreateTable('contractbom', "CREATE TABLE IF NOT EXISTS `contractbom` (
   contractref varchar(20) NOT NULL DEFAULT '0',
   `stockid` varchar(20) NOT NULL DEFAULT '',
  `workcentreadded` char(5) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '1',
  PRIMARY KEY (`contractref`,`stockid`,`workcentreadded`),
  KEY `Stockid` (`stockid`),
  KEY `ContractRef` (`contractref`),
  KEY `WorkCentreAdded` (`workcentreadded`),
  CONSTRAINT `contractbom_ibfk_1` FOREIGN KEY (`workcentreadded`) REFERENCES `workcentres` (`code`),
  CONSTRAINT `contractbom_ibfk_3` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
)", $db);

CreateTable('contractreqts', "CREATE TABLE IF NOT EXISTS `contractreqts` (
  `contractreqid` int(11) NOT NULL AUTO_INCREMENT,
  `contractref` varchar(20) NOT NULL DEFAULT '0',
  `requirement` varchar(40) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '1',
  `costperunit` double NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`contractreqid`),
  KEY `ContractRef` (`contractref`),
  CONSTRAINT `contractreqts_ibfk_1` FOREIGN KEY (`contractref`) REFERENCES `contracts` (`contractref`)
)", $db);


CreateTable('contracts', "CREATE TABLE IF NOT EXISTS `contracts` (
  `contractref` varchar(20) NOT NULL DEFAULT '',
  `contractdescription` text NOT NULL DEFAULT '',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
   `loccode` varchar(5) NOT NULL DEFAULT '',
  `status` tinyint NOT NULL DEFAULT 0,
  `categoryid` varchar(6) NOT NULL DEFAULT '',
  `orderno` int(11) NOT NULL DEFAULT '0',
  `customerref` VARCHAR( 20 ) NOT NULL DEFAULT '',
  `margin` double NOT NULL DEFAULT '1',
  `wo` int(11) NOT NULL DEFAULT '0',
  `requireddate` date NOT NULL DEFAULT '0000-00-00',
  `drawing` varchar(50) NOT NULL DEFAULT '',
  `exrate` double NOT NULL DEFAULT '1',
  PRIMARY KEY (`contractref`),
  KEY `OrderNo` (`orderno`),
  KEY `CategoryID` (`categoryid`),
  KEY `Status` (`status`),
  KEY `WO` (`wo`),
  KEY `loccode` (`loccode`),
  KEY `DebtorNo` (`debtorno`,`branchcode`),
  CONSTRAINT `contracts_ibfk_1` FOREIGN KEY (`debtorno`, `branchcode`) REFERENCES `custbranch` (`debtorno`, `branchcode`),
  CONSTRAINT `contracts_ibfk_2` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`categoryid`),
  CONSTRAINT `contracts_ibfk_3` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
)", $db);

CreateTable('contractcharges', "CREATE TABLE IF NOT EXISTS `contractcharges` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `contractref` varchar(20) NOT NULL,
  `transtype` SMALLINT NOT NULL DEFAULT 20,
  `transno` INT NOT NULL DEFAULT 0,
  `amount` double NOT NULL DEFAULT 0,
  `narrative` TEXT NOT NULL DEFAULT '',
  `anticipated` TINYINT NOT NULL DEFAULT 0,
  INDEX ( `contractref` , `transtype` , `transno` ),
  CONSTRAINT `contractcharges_ibfk_1` FOREIGN KEY (`contractref`) REFERENCES `contracts` (`contractref`),
  CONSTRAINT `contractcharges_ibfk_2` FOREIGN KEY (`transtype`) REFERENCES `systypes` (`typeid`)
)", $db);

/* Increase the size of the salesType field to 40 characters
 */

ChangeColumnSize('sales_type', 'salestypes', 'varchar(40)', 'NOT NULL', '', 40, $db);

/* New config value to determine whether values are shown on the grn
 * screen and the printed grn
 */

NewConfigValue('ShowValueOnGRN', 1, $db);

/* A new table to contain offers from suppliers
 */

CreateTable('offers', "CREATE TABLE IF NOT EXISTS `offers` (
  offerid int(11) NOT NULL AUTO_INCREMENT,
  tenderid int(11) NOT NULL DEFAULT 0,
  supplierid varchar(10) NOT NULL DEFAULT '',
  stockid varchar(20) NOT NULL DEFAULT '',
  quantity double NOT NULL DEFAULT 0.0,
  uom varchar(15) NOT NULL DEFAULT '',
  price double NOT NULL DEFAULT 0.0,
  expirydate date NOT NULL DEFAULT '0000-00-00',
  currcode char(3) NOT NULL DEFAULT '',
  PRIMARY KEY (`offerid`),
  CONSTRAINT `offers_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`supplierid`),
  CONSTRAINT `offers_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
)", $db);

/* New config value for the Purchasing managers email address
 */

NewConfigValue('PurchasingManagerEmail', '', $db);

/* Move the smtp server settings into the database
 */

CreateTable('emailsettings', "CREATE TABLE `emailsettings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `host` varchar(30) NOT NULL,
  `port` char(5) NOT NULL,
  `heloaddress` varchar(20) NOT NULL,
  `username` varchar(30) DEFAULT NULL,
  `password` varchar(30) DEFAULT NULL,
  `timeout` int(11) DEFAULT '5',
  `companyname` varchar(50) DEFAULT NULL,
  `auth` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
)", $db);

InsertRecord('emailsettings', array('id', 'host', 'port', 'heloaddress', 'username', 'password', 'timeout', 'companyname', 'auth'), array(Null, 'localhost', 25, 'helo', '', '', 5, '', 0), array('id', 'host', 'port', 'heloaddress', 'username', 'password', 'timeout', 'companyname', 'auth'), array(Null, 'localhost', 25, 'helo', '', '', 5, '', 0), $db);

/* New fields for sales commission work
 */

AddColumn('commissionrate', 'salesorderdetails', 'double', 'NOT NULL', 0.0, 'poline', $db);
AddColumn('commissionearned', 'salesorderdetails', 'double', 'NOT NULL', 0.0, 'commissionrate', $db);

/* New supplier type field and table
 */

CreateTable('suppliertype', "CREATE TABLE `suppliertype` (
  `typeid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `typename` varchar(100) NOT NULL,
  PRIMARY KEY (`typeid`)
)", $db);

NewConfigValue('DefaultSupplierType', 1, $db);
InsertRecord('suppliertype', array('typeid', 'typename'), array(NULL, 'Default'), array('typeid', 'typename'), array(NULL, 'Default'), $db);
AddColumn('supptype', 'suppliers', 'tinyint(4)', 'NOT NULL', 1, 'address6', $db);

/* Change the shipment quantity to a double from integer,
 * as stock quantities can be of type double
 */

ChangeColumnType('shipqty', 'loctransfers', 'double', 'NOT NULL', 0.0, $db);

/* Create a new security token foer prices security, so that only
 * certain roles can view pricing information
 */

UpdateField('securitytokens', 'tokenname', 'Prices Security', 'tokenid=12', $db);

/* Remove the primary key on table orderdeliverydifferenceslog
 */

DropPrimaryKey('orderdeliverydifferenceslog', array('orderno','invoiceno','stockid'), $db);

/* Chenge received quantity to a type of double as stock
 * quantities are not necessarily integers
 */

ChangeColumnType('recqty', 'loctransfers', 'double', 'NOT NULL', 0.0, $db);

/* New system type needed for contract work
 */

InsertRecord('systypes', array('typeid', 'typename'), array('32', 'Contract Close'), array('typeid', 'typename', 'typeno'), array('32', 'Contract Close', '1'), $db);

/* Add extra columns to reports
 */

for ($i=9; $i<=20; $i++) {
	AddColumn('col'.$i.'width', 'reports', 'int(3)', 'NOT NULL', '25', 'col'.($i-1).'width', $db);
}

/* Increase the size of the fieldname field to accomodate all field names
 */

ChangeColumnSize('fieldname', 'reportfields', 'varchar(80)', 'NOT NULL', '', 80, $db);
/* Addin extra fields to stockcatproperties table
 */

AddColumn('maximumvalue', 'stockcatproperties', 'Double', 'NOT NULL', 999999999, 'defaultvalue', $db);
AddColumn('minimumvalue', 'stockcatproperties', 'Double', 'NOT NULL', -999999999, 'maximumvalue', $db);
AddColumn('numericvalue', 'stockcatproperties', 'tinyint', 'NOT NULL', 0, 'minimumvalue', $db);


/* Lots of database changes required for the move from fixed
 * asset manager v2 to v3
 */

DropTable('assetmanager', 'lifetime', $db);

/* Creates the version 2 assetmanager table */

CreateTable("assetmanager", "CREATE TABLE `assetmanager` (
  `id` int(11) NOT NULL auto_increment,
  `stockid` varchar(20) NOT NULL default '',
  `serialno` varchar(30) NOT NULL default '',
  `location` varchar(15) NOT NULL default '',
  `cost` double NOT NULL default '0',
  `depn` double NOT NULL default '0',
  `datepurchased` date NOT NULL default '0000-00-00',
  `disposalvalue` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY  (`id`)
)",
$db);

CreateTable("fixedassetlocations",
"CREATE TABLE `fixedassetlocations` (
	`locationid` char(6) NOT NULL default '',
	`locationdescription` char(20) NOT NULL default '',
	`parentlocationid` char(6) DEFAULT '',
	PRIMARY KEY  (`locationid`)
)",
$db);

RenameTable('assetmanager', 'fixedassets', $db);
AddColumn('assetcategoryid', 'fixedassets', 'varchar(6)', 'NOT NULL', '', 'disposalvalue', $db);
AddColumn('description', 'fixedassets', 'varchar(50)', 'NOT NULL', '', 'assetcategoryid', $db);
AddColumn('longdescription', 'fixedassets', 'text', 'NOT NULL', '', 'description', $db);
AddColumn('depntype', 'fixedassets', 'int(11)', 'NOT NULL', 1, 'longdescription', $db);
AddColumn('depnrate', 'fixedassets', 'double', 'NOT NULL', 0.0, 'depntype', $db);
AddColumn('barcode', 'fixedassets', 'varchar(30)', 'NOT NULL', '', 'depnrate', $db);
ChangeColumnName('depn', 'fixedassets', 'double', 'NOT NULL', 0.0, 'accumdepn', $db);
ChangeColumnName('location', 'fixedassets', 'varchar(6)', 'NOT NULL', '', 'assetlocation', $db);

if (DB_table_exists('fixedassets', $db)) {
	$sql="desc fixedassets stockid";
	$result=DB_query($sql, $db);
	if (DB_num_rows($result)>0) {
		$response=executeSQL("UPDATE fixedassets INNER JOIN stockmaster ON fixedassets.stockid=stockmaster.stockid SET assetcategoryid=stockmaster.categoryid,
fixedassets.description=stockmaster.description, fixedassets.longdescription=stockmaster.longdescription", $db, False);
		if ($response==0) {
			OutputResult( _('The fixedassets table has been updated from stockmaster') , 'success');
		} else {
			OutputResult( _('The fixedassets table could not be updated from stockmaster') , 'error');
		}
	} else {
		OutputResult( _('The fixedassets table is already correct') , 'info');
	}
} else {
		$response=executeSQL("UPDATE fixedassets INNER JOIN stockmaster ON fixedassets.stockid=stockmaster.stockid SET assetcategoryid=stockmaster.categoryid,
fixedassets.description=stockmaster.description, fixedassets.longdescription=stockmaster.longdescription", $db, False);
}

Createtable('fixedassetcategories', "CREATE TABLE IF NOT EXISTS `fixedassetcategories` (
  `categoryid` char(6) NOT NULL DEFAULT '',
  `categorydescription` char(20) NOT NULL DEFAULT '',
  `costact` int(11) NOT NULL DEFAULT '0',
  `depnact` int(11) NOT NULL DEFAULT '0',
  `disposalact` int(11) NOT NULL DEFAULT '80000',
  `accumdepnact` int(11) NOT NULL DEFAULT '0',
  defaultdepnrate double NOT NULL DEFAULT '.2',
  defaultdepntype int NOT NULL DEFAULT '1',
  PRIMARY KEY (`categoryid`)
)", $db);

if (DB_table_exists('fixedassets', $db)) {
	$sql="SELECT categoryid FROM fixedassetcategories";
	$result=DB_query($sql, $db);
	if (DB_num_rows($result)==0) {
		$response=executeSQL("INSERT INTO fixedassetcategories (categoryid, categorydescription, costact, depnact, disposalact, accumdepnact)
SELECT categoryid, categorydescription, stockact, adjglact, materialuseagevarac, wipact FROM stockcategory WHERE stocktype='A'", $db, False);
		if ($response==0) {
			OutputResult( _('The fixedassetcategories table has been updated from stockcategory') , 'success');
		} else {
			OutputResult( _('The fixedassetcategories table could not be updated from stockcategory') , 'error');
		}
	} else {
		OutputResult( _('The fixedassetcategories table is already correct') , 'info');
	}
}  else {
		$response=executeSQL("INSERT INTO fixedassetcategories (categoryid, categorydescription, costact, depnact, disposalact, accumdepnact)
SELECT categoryid, categorydescription, stockact, adjglact, materialuseagevarac, wipact FROM stockcategory WHERE stocktype='A'", $db, False);
}

$sql="SELECT categoryid FROM stockcategory WHERE stockcategory.stocktype='A'";
$result=DB_query($sql, $db);
if (DB_num_rows($result)!=0) {
	$response=executeSQL("DELETE locstock.* FROM locstock INNER JOIN stockmaster ON locstock.stockid=stockmaster.stockid INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid WHERE stockcategory.stocktype='A'", $db, False);
	if ($response==0) {
		OutputResult( _('The fixedassetcategories have been removed from stockcategory') , 'success');
	} else {
		OutputResult( _('The fixedassetcategories table could not be removed from stockcategory') , 'error');
	}
} else {
	OutputResult( _('The stockcategory table is already correct') , 'info');
}

$sql="SELECT stockitemproperties.stockid
	FROM stockitemproperties
	INNER JOIN stockmaster
		ON stockitemproperties.stockid=stockmaster.stockid
	INNER JOIN stockcategory
		ON stockmaster.categoryid=stockcategory.categoryid
	WHERE stockcategory.stocktype='A'";
$result=DB_query($sql, $db);
if (DB_num_rows($result)!=0) {
	$response=executeSQL("DELETE stockitemproperties.* FROM stockitemproperties INNER JOIN stockmaster ON stockitemproperties.stockid=stockmaster.stockid INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid WHERE stockcategory.stocktype='A'", $db, False);
	if ($response==0) {
		OutputResult( _('The fixed assets have been removed from stockitemproperties') , 'success');
	} else {
		OutputResult( _('The fixed assets could not be removed from stockitemproperties') , 'error');
	}
} else {
	OutputResult( _('The fixed assets have already been removed from stockitemproperties') , 'info');
}

$sql="SELECT stockserialmoves.* FROM stockserialmoves, stockmoves,
stockmaster,stockcategory WHERE stockserialmoves.stockmoveno=stockmoves.stkmoveno AND
stockmoves.stockid = stockmaster.stockid AND stockmaster.categoryid = stockcategory.categoryid AND stockcategory.stocktype = 'A'";
$result=DB_query($sql, $db);
if (DB_num_rows($result)!=0) {
	$response=executeSQL("DELETE stockserialmoves.* FROM stockserialmoves, stockmoves,
stockmaster,stockcategory WHERE stockserialmoves.stockmoveno=stockmoves.stkmoveno AND
stockmoves.stockid = stockmaster.stockid AND stockmaster.categoryid = stockcategory.categoryid AND stockcategory.stocktype = 'A'", $db, False);
	if ($response==0) {
		OutputResult( _('The fixed assets have been removed from stockitemproperties') , 'success');
	} else {
		OutputResult( _('The fixed assets could not be removed from stockitemproperties') , 'error');
	}
} else {
	OutputResult( _('The fixed assets have already been removed from stockitemproperties') , 'info');
}

$sql="SELECT stockserialitems.* FROM stockserialitems, stockmaster, stockcategory
WHERE stockserialitems.stockid = stockmaster.stockid AND stockmaster.categoryid=stockcategory.categoryid AND stocktype='A'";
$result=DB_query($sql, $db);
if (DB_num_rows($result)!=0) {
	$response=executeSQL("DELETE stockserialitems.* FROM stockserialitems, stockmaster, stockcategory
WHERE stockserialitems.stockid = stockmaster.stockid AND stockmaster.categoryid=stockcategory.categoryid AND stocktype='A'", $db, False);
	if ($response==0) {
		OutputResult( _('The fixed assets have been removed from stockserialitems, stockmaster, and stockcategory tables') , 'success');
	} else {
		OutputResult( _('The fixed assets could not be removed from stockserialitems, stockmaster, and stockcategory tables') , 'error');
	}
} else {
	OutputResult( _('The fixed assets have already been removed from stockserialitems, stockmaster, and stockcategory tables') , 'info');
}

$sql="SELECT stockmoves.*
		FROM stockmoves,
			stockmaster,
			stockcategory
		WHERE stockmoves.stockid = stockmaster.stockid
			AND stockmaster.categoryid = stockcategory.categoryid
			AND stockcategory.stocktype = 'A'";
$result=DB_query($sql, $db);
if (DB_num_rows($result)!=0) {
	$response=executeSQL("DELETE stockmoves.* FROM stockmoves, stockmaster, stockcategory WHERE stockmoves.stockid = stockmaster.stockid AND stockmaster.categoryid = stockcategory.categoryid AND stockcategory.stocktype = 'A'", $db, False);
	if ($response==0) {
		OutputResult( _('The fixed assets have been removed from stockmoves, stockmaster, and stockcategory tables') , 'success');
	} else {
		OutputResult( _('The fixed assets could not be removed from stockmoves, stockmaster, and stockcategory tables') , 'error');
	}
} else {
	OutputResult( _('The fixed assets have already been removed from stockmoves, stockmaster, and stockcategory tables') , 'info');
}

$sql="SELECT stockmaster.* FROM stockmaster INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid WHERE stockcategory.stocktype='A'";
$result=DB_query($sql, $db);
if (DB_num_rows($result)!=0) {
	$response=executeSQL("DELETE stockmaster.* FROM stockmaster INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid WHERE stockcategory.stocktype='A'", $db, False);
	if ($response==0) {
		OutputResult( _('The fixed assets have been removed from stockmaster table') , 'success');
	} else {
		OutputResult( _('The fixed assets could not be removed from stockmaster table') , 'error');
	}
} else {
	OutputResult( _('The fixed assets have already been removed from stockmaster table') , 'info');
}

ChangeColumnName('id', 'fixedassets', 'int(11)', 'NOT NULL', 0, 'assetid', $db, 'AUTO_INCREMENT');

$sql="SELECT categoryid FROM  stockcategory WHERE stocktype='A'";
$result=DB_query($sql, $db);
if (DB_num_rows($result)!=0) {
	$response=executeSQL("DELETE FROM stockcategory WHERE stocktype='A'", $db, False);
	if ($response==0) {
		OutputResult( _('The fixed assets have been removed from stockmaster table') , 'success');
	} else {
		OutputResult( _('The fixed assets could not be removed from stockmaster table') , 'error');
	}
} else {
	OutputResult( _('The fixed assets have already been removed from stockmaster table') , 'info');
}

DropColumn('stockid', 'fixedassets', $db);

InsertRecord('systypes', array('typeid', 'typename'), array('41', 'Asset Addition'), array('typeid', 'typename', 'typeno'), array('41', 'Asset Addition', '1'), $db);
InsertRecord('systypes', array('typeid', 'typename'), array('42', 'Asset Category Change'), array('typeid', 'typename', 'typeno'), array('42', 'Asset Category Change', '1'), $db);
InsertRecord('systypes', array('typeid', 'typename'), array('43', 'Delete w/down asset'), array('typeid', 'typename', 'typeno'), array('43', 'Delete w/down asset', '1'), $db);
InsertRecord('systypes', array('typeid', 'typename'), array('44', 'Depreciation'), array('typeid', 'typename', 'typeno'), array('44', 'Depreciation', '1'), $db);

CreateTable('fixedassettrans', "CREATE TABLE fixedassettrans(
id INT( 11 ) NOT NULL AUTO_INCREMENT ,
assetid INT( 11 ) NOT NULL ,
transtype TINYINT( 4 ) NOT NULL ,
transdate DATE NOT NULL,
transno INT NOT NULL ,
periodno SMALLINT( 6 ) NOT NULL ,
inputdate DATE NOT NULL ,
fixedassettranstype  varchar(8) NOT NULL ,
amount DOUBLE NOT NULL ,
PRIMARY KEY ( id ) ,
INDEX ( assetid, transtype, transno ) ,
INDEX ( inputdate ),
INDEX (transdate)
)", $db);

AddColumn('assetid', 'purchorderdetails', 'int(11)', 'NOT NULL', 0, 'total_amount', $db);

InsertRecord('systypes', array('typeid', 'typename'), array('49', 'Import Fixed Assets'), array('typeid', 'typename', 'typeno'), array('49', 'Import Fixed Assets', '1'), $db);

/* New database stuff to move the page security levels to a
 * database table.
 */

DropTable('scripts', 'pagedescription', $db);

CreateTable('scripts', "CREATE TABLE `scripts` (
	`script` varchar(78) NOT NULL DEFAULT '',
	`pagesecurity` int(11) NOT NULL DEFAULT 1,
	`description` varchar(78) NOT NULL DEFAULT '',
	PRIMARY KEY  (`script`)
)", $db);

NewScript('AccountGroups.php', 10, $db);
NewScript('AccountSections.php', 10, $db);
NewScript('AddCustomerContacts.php', 3, $db);
NewScript('AddCustomerNotes.php', 3, $db);
NewScript('AddCustomerTypeNotes.php', 3, $db);
NewScript('AgedDebtors.php', 2, $db);
NewScript('AgedSuppliers.php', 2, $db);
NewScript('Areas.php', 3, $db);
NewScript('AuditTrail.php', 15, $db);
NewScript('BankAccounts.php', 10, $db);
NewScript('BankMatching.php', 7, $db);
NewScript('BankReconciliation.php', 7, $db);
NewScript('BOMExtendedQty.php', 2, $db);
NewScript('BOMIndented.php', 2, $db);
NewScript('BOMIndentedReverse.php', 2, $db);
NewScript('BOMInquiry.php', 2, $db);
NewScript('BOMListing.php', 2, $db);
NewScript('BOMs.php', 9, $db);
NewScript('COGSGLPostings.php', 10, $db);
NewScript('CompanyPreferences.php', 10, $db);
NewScript('ConfirmDispatchControlled_Invoice.php', 11, $db);
NewScript('ConfirmDispatch_Invoice.php', 2, $db);
NewScript('ContractBOM.php', 6, $db);
NewScript('ContractCosting.php', 6, $db);
NewScript('ContractOtherReqts.php', 4, $db);
NewScript('Contracts.php', 6, $db);
NewScript('CounterSales.php', 1, $db);
NewScript('Credit_Invoice.php', 3, $db);
NewScript('CreditItemsControlled.php', 3, $db);
NewScript('CreditStatus.php', 3, $db);
NewScript('Currencies.php', 9, $db);
NewScript('CustEDISetup.php', 11, $db);
NewScript('CustLoginSetup.php', 15, $db);
NewScript('CustomerAllocations.php', 3, $db);
NewScript('CustomerBranches.php', 3, $db);
NewScript('CustomerInquiry.php', 1, $db);
NewScript('CustomerReceipt.php', 3, $db);
NewScript('Customers.php', 3, $db);
NewScript('CustomerTransInquiry.php', 2, $db);
NewScript('CustomerTypes.php', 15, $db);
NewScript('CustWhereAlloc.php', 2, $db);
NewScript('DailyBankTransactions.php', 8, $db);
NewScript('DailySalesInquiry.php', 2, $db);
NewScript('DebtorsAtPeriodEnd.php', 2, $db);
NewScript('DeliveryDetails.php', 1, $db);
NewScript('DiscountCategories.php', 11, $db);
NewScript('DiscountMatrix.php', 11, $db);
NewScript('EDIMessageFormat.php', 10, $db);
NewScript('EDIProcessOrders.php', 11, $db);
NewScript('EDISendInvoices.php', 15, $db);
NewScript('EmailConfirmation.php', 2, $db);
NewScript('EmailCustTrans.php', 2, $db);
NewScript('ExchangeRateTrend.php', 2, $db);
NewScript('Factors.php', 5, $db);
NewScript('FixedAssetCategories.php', 11, $db);
NewScript('FixedAssetDepreciation.php', 10, $db);
NewScript('FixedAssetItems.php', 11, $db);
NewScript('FixedAssetList.php', 11, $db);
NewScript('FixedAssetLocations.php', 11, $db);
NewScript('FixedAssetRegister.php', 11, $db);
NewScript('FixedAssetTransfer.php', 11, $db);
NewScript('FormDesigner.php', 14, $db);
NewScript('FreightCosts.php', 11, $db);
NewScript('FTP_RadioBeacon.php', 2, $db);
NewScript('geocode_genxml_customers.php', 3, $db);
NewScript('geocode_genxml_suppliers.php', 3, $db);
NewScript('geocode.php', 3, $db);
NewScript('GeocodeSetup.php', 3, $db);
NewScript('geo_displaymap_customers.php', 3, $db);
NewScript('geo_displaymap_suppliers.php', 3, $db);
NewScript('GetStockImage.php', 1, $db);
NewScript('GLAccountCSV.php', 8, $db);
NewScript('GLAccountInquiry.php', 8, $db);
NewScript('GLAccountReport.php', 8, $db);
NewScript('GLAccounts.php', 10, $db);
NewScript('GLBalanceSheet.php', 8, $db);
NewScript('GLBudgets.php', 10, $db);
NewScript('GLCodesInquiry.php', 8, $db);
NewScript('GLJournal.php', 10, $db);
NewScript('GLProfit_Loss.php', 8, $db);
NewScript('GLTagProfit_Loss.php', 8, $db);
NewScript('GLTags.php', 10, $db);
NewScript('GLTransInquiry.php', 8, $db);
NewScript('GLTrialBalance_csv.php', 8, $db);
NewScript('GLTrialBalance.php', 8, $db);
NewScript('GoodsReceivedControlled.php', 11, $db);
NewScript('GoodsReceived.php', 11, $db);
NewScript('index.php', 1, $db);
NewScript('InventoryPlanning.php', 2, $db);
NewScript('InventoryPlanningPrefSupplier.php', 2, $db);
NewScript('InventoryQuantities.php', 2, $db);
NewScript('InventoryValuation.php', 2, $db);
NewScript('Labels.php', 15, $db);
NewScript('Locations.php', 11, $db);
NewScript('Logout.php', 1, $db);
NewScript('MailInventoryValuation.php', 1, $db);
NewScript('scriptsAccess.php', 15, $db);
NewScript('MRPCalendar.php', 9, $db);
NewScript('MRPCreateDemands.php', 9, $db);
NewScript('MRPDemands.php', 9, $db);
NewScript('MRPDemandTypes.php', 9, $db);
NewScript('MRP.php', 9, $db);
NewScript('MRPPlannedPurchaseOrders.php', 2, $db);
NewScript('MRPPlannedWorkOrders.php', 2, $db);
NewScript('MRPReport.php', 2, $db);
NewScript('MRPReschedules.php', 2, $db);
NewScript('MRPShortages.php', 2, $db);
NewScript('OffersReceived.php', 4, $db);
NewScript('OrderDetails.php', 2, $db);
NewScript('OutstandingGRNs.php', 2, $db);
NewScript('PaymentAllocations.php', 5, $db);
NewScript('PaymentMethods.php', 15, $db);
NewScript('Payments.php', 5, $db);
NewScript('PaymentTerms.php', 10, $db);
NewScript('PcAssignCashToTab.php', 6, $db);
NewScript('PcAuthorizeExpenses.php', 6, $db);
NewScript('PcClaimExpensesFromTab.php', 6, $db);
NewScript('PcExpenses.php', 15, $db);
NewScript('PcExpensesTypeTab.php', 15, $db);
NewScript('PcReportTab.php', 6, $db);
NewScript('PcTabs.php', 15, $db);
NewScript('PcTypeTabs.php', 15, $db);
NewScript('PDFBankingSummary.php', 3, $db);
NewScript('PDFChequeListing.php', 3, $db);
NewScript('PDFCustomerList.php', 2, $db);
NewScript('PDFCustTransListing.php', 3, $db);
NewScript('PDFDeliveryDifferences.php', 3, $db);
NewScript('PDFDIFOT.php', 3, $db);
NewScript('PDFGrn.php', 2, $db);
NewScript('PDFLowGP.php', 2, $db);
NewScript('PDFOrdersInvoiced.php', 3, $db);
NewScript('PDFOrderStatus.php', 3, $db);
NewScript('PDFPickingList.php', 2, $db);
NewScript('PDFPriceList.php', 2, $db);
NewScript('PDFPrintLabel.php', 10, $db);
NewScript('PDFQuotation.php', 2, $db);
NewScript('PDFReceipt.php', 2, $db);
NewScript('PDFRemittanceAdvice.php', 2, $db);
NewScript('PDFStockCheckComparison.php', 2, $db);
NewScript('PDFStockLocTransfer.php', 1, $db);
NewScript('PDFStockNegatives.php', 1, $db);
NewScript('PDFStockTransfer.php', 2, $db);
NewScript('PDFStockTransListing.php', 3, $db);
NewScript('PDFSuppTransListing.php', 3, $db);
NewScript('PDFTopItems.php', 2, $db);
NewScript('PeriodsInquiry.php', 2, $db);
NewScript('PO_AuthorisationLevels.php', 15, $db);
NewScript('PO_AuthoriseMyOrders.php', 4, $db);
NewScript('PO_Header.php', 4, $db);
NewScript('PO_Items.php', 4, $db);
NewScript('PO_OrderDetails.php', 2, $db);
NewScript('PO_PDFPurchOrder.php', 2, $db);
NewScript('POReport.php', 2, $db);
NewScript('PO_SelectOSPurchOrder.php', 2, $db);
NewScript('PO_SelectPurchOrder.php', 2, $db);
NewScript('PricesBasedOnMarkUp.php', 11, $db);
NewScript('PricesByCost.php', 11, $db);
NewScript('Prices_Customer.php', 11, $db);
NewScript('Prices.php', 9, $db);
NewScript('PrintCheque.php', 5, $db);
NewScript('PrintCustOrder_generic.php', 2, $db);
NewScript('PrintCustOrder.php', 2, $db);
NewScript('PrintCustStatements.php', 2, $db);
NewScript('PrintCustTrans.php', 1, $db);
NewScript('PrintCustTransPortrait.php', 1, $db);
NewScript('PrintSalesOrder_generic.php', 2, $db);
NewScript('PurchData.php', 4, $db);
NewScript('RecurringSalesOrders.php', 1, $db);
NewScript('ReorderLevelLocation.php', 2, $db);
NewScript('ReorderLevel.php', 2, $db);
NewScript('ReportBug.php', 15, $db);
NewScript('ReverseGRN.php', 11, $db);
NewScript('SalesAnalReptCols.php', 2, $db);
NewScript('SalesAnalRepts.php', 2, $db);
NewScript('SalesAnalysis_UserDefined.php', 2, $db);
NewScript('SalesCategories.php', 11, $db);
NewScript('SalesGLPostings.php', 10, $db);
NewScript('SalesGraph.php', 6, $db);
NewScript('SalesInquiry.php', 2, $db);
NewScript('SalesPeople.php', 3, $db);
NewScript('SalesTypes.php', 15, $db);
NewScript('SelectAsset.php', 2, $db);
NewScript('SelectCompletedOrder.php', 1, $db);
NewScript('SelectContract.php', 6, $db);
NewScript('SelectCreditItems.php', 3, $db);
NewScript('SelectCustomer.php', 2, $db);
NewScript('SelectGLAccount.php', 8, $db);
NewScript('SelectOrderItems.php', 1, $db);
NewScript('SelectProduct.php', 2, $db);
NewScript('SelectRecurringSalesOrder.php', 2, $db);
NewScript('SelectSalesOrder.php', 2, $db);
NewScript('SelectSupplier.php', 2, $db);
NewScript('SelectWorkOrder.php', 2, $db);
NewScript('ShipmentCosting.php', 11, $db);
NewScript('Shipments.php', 11, $db);
NewScript('Shippers.php', 15, $db);
NewScript('Shipt_Select.php', 11, $db);
NewScript('ShiptsList.php', 2, $db);
NewScript('SMTPServer.php', 15, $db);
NewScript('SpecialOrder.php', 4, $db);
NewScript('StockAdjustmentsControlled.php', 11, $db);
NewScript('StockAdjustments.php', 11, $db);
NewScript('StockCategories.php', 11, $db);
NewScript('StockCheck.php', 2, $db);
NewScript('StockCostUpdate.php', 9, $db);
NewScript('StockCounts.php', 2, $db);
NewScript('StockDispatch.php', 2, $db);
NewScript('StockLocMovements.php', 2, $db);
NewScript('StockLocStatus.php', 2, $db);
NewScript('StockLocTransfer.php', 11, $db);
NewScript('StockLocTransferReceive.php', 11, $db);
NewScript('StockMovements.php', 2, $db);
NewScript('StockQties_csv.php', 5, $db);
NewScript('StockQuantityByDate.php', 2, $db);
NewScript('StockReorderLevel.php', 4, $db);
NewScript('StockSerialItemResearch.php', 3, $db);
NewScript('StockSerialItems.php', 2, $db);
NewScript('Stocks.php', 11, $db);
NewScript('StockStatus.php', 2, $db);
NewScript('StockTransferControlled.php', 11, $db);
NewScript('StockTransfers.php', 11, $db);
NewScript('StockUsageGraph.php', 2, $db);
NewScript('StockUsage.php', 2, $db);
NewScript('SuppContractChgs.php', 5, $db);
NewScript('SuppCreditGRNs.php', 5, $db);
NewScript('SuppFixedAssetChgs.php', 5, $db);
NewScript('SuppInvGRNs.php', 5, $db);
NewScript('SupplierAllocations.php', 5, $db);
NewScript('SupplierBalsAtPeriodEnd.php', 2, $db);
NewScript('SupplierContacts.php', 5, $db);
NewScript('SupplierCredit.php', 5, $db);
NewScript('SupplierInquiry.php', 2, $db);
NewScript('SupplierInvoice.php', 5, $db);
NewScript('Suppliers.php', 5, $db);
NewScript('SupplierTenders.php', 9, $db);
NewScript('SupplierTransInquiry.php', 2, $db);
NewScript('SupplierTypes.php', 4, $db);
NewScript('SuppLoginSetup.php', 15, $db);
NewScript('SuppPaymentRun.php', 5, $db);
NewScript('SuppPriceList.php', 2, $db);
NewScript('SuppShiptChgs.php', 5, $db);
NewScript('SuppTransGLAnalysis.php', 5, $db);
NewScript('SystemCheck.php', 10, $db);
NewScript('SystemParameters.php', 15, $db);
NewScript('TaxAuthorities.php', 15, $db);
NewScript('TaxAuthorityRates.php', 11, $db);
NewScript('TaxCategories.php', 15, $db);
NewScript('TaxGroups.php', 15, $db);
NewScript('Tax.php', 2, $db);
NewScript('TaxProvinces.php', 15, $db);
NewScript('TopItems.php', 2, $db);
NewScript('UnitsOfMeasure.php', 15, $db);
NewScript('UserSettings.php', 1, $db);
NewScript('WhereUsedInquiry.php', 2, $db);
NewScript('WorkCentres.php', 9, $db);
NewScript('WorkOrderCosting.php', 11, $db);
NewScript('WorkOrderEntry.php', 10, $db);
NewScript('WorkOrderIssue.php', 11, $db);
NewScript('WorkOrderReceive.php', 11, $db);
NewScript('WorkOrderStatus.php', 11, $db);
NewScript('WOSerialNos.php', 10, $db);
NewScript('WWW_Access.php', 15, $db);
NewScript('WWW_Users.php', 15, $db);
NewScript('Z_BottomUpCosts.php', 15, $db);
NewScript('Z_ChangeBranchCode.php', 15, $db);
NewScript('Z_ChangeCustomerCode.php', 15, $db);
NewScript('Z_ChangeStockCategory.php', 15, $db);
NewScript('Z_ChangeStockCode.php', 15, $db);
NewScript('Z_CheckAllocationsFrom.php', 15, $db);
NewScript('Z_CheckAllocs.php', 2, $db);
NewScript('Z_CheckDebtorsControl.php', 15, $db);
NewScript('Z_CheckGLTransBalance.php', 15, $db);
NewScript('Z_CopyBOM.php', 9, $db);
NewScript('Z_CreateChartDetails.php', 9, $db);
NewScript('Z_CreateCompany.php', 15, $db);
NewScript('Z_CreateCompanyTemplateFile.php', 15, $db);
NewScript('Z_CurrencyDebtorsBalances.php', 15, $db);
NewScript('Z_CurrencySuppliersBalances.php', 15, $db);
NewScript('Z_DataExport.php', 15, $db);
NewScript('Z_DeleteCreditNote.php', 15, $db);
NewScript('Z_DeleteInvoice.php', 15, $db);
NewScript('Z_DeleteSalesTransActions.php', 15, $db);
NewScript('Z_DescribeTable.php', 11, $db);
NewScript('Z_ImportChartOfAccounts.php', 11, $db);
NewScript('Z_ImportFixedAssets.php', 15, $db);
NewScript('Z_ImportGLAccountGroups.php', 11, $db);
NewScript('Z_ImportGLAccountSections.php', 11, $db);
NewScript('Z_ImportPartCodes.php', 11, $db);
NewScript('Z_ImportStocks.php', 15, $db);
NewScript('Z_index.php', 15, $db);
NewScript('Z_MakeNewCompany.php', 15, $db);
NewScript('Z_MakeStockLocns.php', 15, $db);
NewScript('Z_poAddLanguage.php', 15, $db);
NewScript('Z_poAdmin.php', 15, $db);
NewScript('Z_poEditLangHeader.php', 15, $db);
NewScript('Z_poEditLangModule.php', 15, $db);
NewScript('Z_poEditLangRemaining.php', 15, $db);
NewScript('Z_poRebuildDefault.php', 15, $db);
NewScript('Z_PriceChanges.php', 15, $db);
NewScript('Z_ReApplyCostToSA.php', 15, $db);
NewScript('Z_RePostGLFromPeriod.php', 15, $db);
NewScript('Z_ReverseSuppPaymentRun.php', 15, $db);
NewScript('Z_SalesIntegrityCheck.php', 15, $db);
NewScript('Z_UpdateChartDetailsBFwd.php', 15, $db);
NewScript('Z_Upgrade_3.01-3.02.php', 15, $db);
NewScript('Z_Upgrade_3.04-3.05.php', 15, $db);
NewScript('Z_Upgrade_3.05-3.06.php', 15, $db);
NewScript('Z_Upgrade_3.07-3.08.php', 15, $db);
NewScript('Z_Upgrade_3.08-3.09.php', 15, $db);
NewScript('Z_Upgrade_3.09-3.10.php', 15, $db);
NewScript('Z_Upgrade_3.10-3.11.php', 15, $db);
NewScript('Z_Upgrade3.10.php', 15, $db);
NewScript('Z_Upgrade_3.11-4.00.php', 15, $db);
NewScript('Z_UploadForm.php', 15, $db);
NewScript('Z_UploadResult.php', 15, $db);
NewScript('ReportletContainer.php', 1, $db);
NewScript('PageSecurity.php', 15, $db);
NewScript('UpgradeDatabase.php', 15, $db);
NewScript('ManualContents.php', 10, $db);
NewScript('FormMaker.php', 1, $db);
NewScript('ReportMaker.php', 1, $db);
NewScript('ReportCreator.php', 13, $db);

NewConfigValue('VersionNumber', '3.12.0', $db);
ChangeConfigValue('VersionNumber', '3.12.1', $db);
ChangeConfigValue('VersionNumber', '3.12.2', $db);

ChangeColumnName('nw', 'purchorderdetails', 'VARCHAR(50)', 'NOT NULL', '', 'netweight', $db);
ChangeColumnName('gw', 'purchorderdetails', 'VARCHAR(50)', 'NOT NULL', '', 'kgs', $db);
AddColumn('conversionfactor', 'purchorderdetails', 'DOUBLE', 'NOT NULL', '1', 'assetid', $db);
ChangeConfigValue('VersionNumber', '3.12.3', $db);
ChangeColumnName('uom', 'purchorderdetails', 'VARCHAR(50)', 'NOT NULL', '', 'suppliersunit', $db);
ChangeConfigValue('VersionNumber', '3.12.31', $db);
NewConfigValue('AutoAuthorisePO', '1', $db);
ChangeConfigValue('VersionNumber', '4.03', $db);

AddColumn('poplaced', 'salesorders', 'TINYINT', 'NOT NULL', '0', 'quotedate', $db);
AddIndex(array('poplaced'), 'salesorders', 'poplaced', $db);

ChangeConfigValue('VersionNumber', '4.03.1', $db);
ChangeConfigValue('VersionNumber', '4.03.2', $db);

AddColumn('cashsalebranch', 'locations', 'VARCHAR(10)', 'NOT NULL', "Default ''", 'managed', $db);
ChangeColumnType('cashsalecustomer', 'locations', 'VARCHAR(10)', 'NOT NULL', '', $db);

ChangeConfigValue('VersionNumber', '4.03.3', $db);

NewScript('Z_ChangeSupplierCode.php', '15', $db);

ChangeConfigValue('VersionNumber', '4.03.5', $db);

NewScript('ReprintGRN.php', '11', $db);
ChangeConfigValue('VersionNumber', '4.03.6', $db);

AddColumn('usepreprintedstationery', 'paymentmethods', 'TINYINT', 'NOT NULL', '0', 'receipttype', $db);

RemoveScript('PDFStockTransListing.php', $db);
NewScript('PDFPeriodStockTransListing.php','3', $db);
ChangeConfigValue('VersionNumber', '4.03.7', $db);

DropColumn('itemno', 'purchorderdetails', $db);
DropColumn('subtotal_amount', 'purchorderdetails', $db);
DropColumn('package', 'purchorderdetails', $db);
DropColumn('pcunit', 'purchorderdetails', $db);
DropColumn('kgs', 'purchorderdetails', $db);
DropColumn('cuft', 'purchorderdetails', $db);
DropColumn('total_quantity', 'purchorderdetails', $db);
DropColumn('netweight', 'purchorderdetails', $db);
DropColumn('total_amount', 'purchorderdetails', $db);
DropColumn('uom', 'purchorderdetails', $db);

ChangeConfigValue('VersionNumber', '4.03.8', $db);

ChangeColumnType('stockcheckdate', 'stockcheckfreeze', 'DATE', 'NOT NULL', '0000-00-00', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>