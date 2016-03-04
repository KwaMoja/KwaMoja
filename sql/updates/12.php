<?php

/* Ensure that all tablse use the utf8_general_cli
 * character set
 */

$Result = DB_show_tables();
while ($table = DB_fetch_array($Result)) {
	if (CharacterSet($table[0]) != 'utf8_general_ci') {
		$response = executeSQL('ALTER TABLE ' . $table[0] . ' CONVERT TO CHARACTER SET utf8');
		if ($response == 0) {
			OutputResult(_('The character set of') . ' ' . $table[0] . ' ' . _('has been changed to utf8_general_ci'), 'success');
		} else {
			OutputResult(_('The character set of') . ' ' . $table[0] . ' ' . _('could not be changed to utf8_general_ci'), 'error');
		}
	} else {
		OutputResult(_('The character set of') . ' ' . $table[0] . ' ' . _('is already utf8_general_ci'), 'info');
	}
}

/* Increase the size of the language field in www_users to enable
 * utf languages to be accepted
 */

ChangeColumnSize('language', 'www_users', 'varchar(10)', 'NOT NULL', 'en_GB.utf8', 10);

/* New config values for logging any prnMsg messages
 * Defines the patth and the messages to be logged
 */

NewConfigValue('LogPath', '');
NewConfigValue('LogSeverity', '0');

/* New config values for whether to show frequently ordered items
 * on order entry, and if so then how many months to show
 */

NewConfigValue('FrequentlyOrderedItems', '0');
NewConfigValue('NumberOfMonthMustBeShown', '6');

/* Add the field in the currencies that shows the number of decimal places to be shown for each currency */

AddColumn('decimalplaces', 'currencies', 'tinyint(3)', 'NOT NULL', '2', 'hundredsname');

AddColumn('suppdeladdress1', 'purchorders', 'varchar(40)', 'NOT NULL', '', 'deladd6');
AddColumn('suppdeladdress2', 'purchorders', 'varchar(40)', 'NOT NULL', '', 'suppdeladdress1');
AddColumn('suppdeladdress3', 'purchorders', 'varchar(40)', 'NOT NULL', '', 'suppdeladdress2');
AddColumn('suppdeladdress4', 'purchorders', 'varchar(20)', 'NOT NULL', '', 'suppdeladdress3');
AddColumn('suppdeladdress5', 'purchorders', 'varchar(15)', 'NOT NULL', '', 'suppdeladdress4');
AddColumn('suppdeladdress6', 'purchorders', 'varchar(30)', 'NOT NULL', '', 'suppdeladdress5');
AddColumn('supptel', 'purchorders', 'varchar(30)', 'NOT NULL', '""', 'suppdeladdress6');
AddColumn('tel', 'purchorders', 'varchar(15)', 'NOT NULL', '""', 'deladd6');
AddColumn('paymentterms', 'purchorders', 'char(2)', 'NOT NULL', '""', 'stat_comment');
AddColumn('port', 'purchorders', 'varchar(40)', 'NOT NULL', '""', 'paymentterms');

/* Add column to www_users for the pdf language to be used */

AddColumn('pdflanguage', 'www_users', 'tinyint(1)', 'NOT NULL', '0', 'language');

/* add a column to the purchase order authentication table for whether
 * the user is allowed to remove an invoice from hold
 */

AddColumn('offhold', 'purchorderauth', 'tinyint(1)', 'NOT NULL', '0', 'authlevel');

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
)");

CreateTable("pcexpenses", "CREATE TABLE `pcexpenses` (
  `codeexpense` varchar(20) NOT NULL COMMENT 'code for the group',
  `description` varchar(50) NOT NULL COMMENT 'text description, e.g. meals, train tickets, fuel, etc',
  `glaccount` int(11) NOT NULL COMMENT 'GL related account',
  PRIMARY KEY (`codeexpense`),
  KEY (`glaccount`)
)");

CreateTable("pctabexpenses", "CREATE TABLE `pctabexpenses` (
  `typetabcode` varchar(20) NOT NULL,
  `codeexpense` varchar(20) NOT NULL,
  KEY (`typetabcode`),
  KEY (`codeexpense`)
)");

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
)");

CreateTable("pctypetabs", "CREATE TABLE `pctypetabs` (
  `typetabcode` varchar(20) NOT NULL COMMENT 'code for the type of petty cash tab',
  `typetabdescription` varchar(50) NOT NULL COMMENT 'text description, e.g. tab for CEO',
  PRIMARY KEY (`typetabcode`)
)");

AddConstraint('pcexpenses', 'pcexpenses_ibfk_1', 'glaccount', 'chartmaster', 'accountcode');
AddConstraint('pctabexpenses', 'pctabexpenses_ibfk_1', 'typetabcode', 'pctypetabs', 'typetabcode');
AddConstraint('pctabexpenses', 'pctabexpenses_ibfk_2', 'codeexpense', 'pcexpenses', 'codeexpense');

AddConstraint('pctabs', 'pctabs_ibfk_1', 'usercode', 'www_users', 'userid');
AddConstraint('pctabs', 'pctabs_ibfk_2', 'typetabcode', 'pctypetabs', 'typetabcode');
AddConstraint('pctabs', 'pctabs_ibfk_3', 'currency', 'currencies', 'currabrev');
AddConstraint('pctabs', 'pctabs_ibfk_4', 'authorizer', 'www_users', 'userid');
AddConstraint('pctabs', 'pctabs_ibfk_5', 'glaccountassignment', 'chartmaster', 'accountcode');

DropConstraint('suppliers', 'suppliers_ibfk_4');

UpdateField('suppliers', 'factorcompanyid', 0, '`factorcompanyid`=1');
DeleteRecords('factorcompanies', "coyname='None'");

/* New security token for petty cash usage */

UpdateField('securitytokens', 'tokenname', 'Petty Cash', 'tokenid=6');

/* Add input date to transaction tables so that transactions can be
 * reported on by their input date
 */

AddColumn('inputdate', 'supptrans', 'datetime', 'NOT NULL', '0000-00-00', 'duedate');
AddColumn('inputdate', 'debtortrans', 'datetime', 'NOT NULL', '0000-00-00', 'trandate');

/* Change the size of the fieldname field in the report writer as
 * the previous size was not big enough to hold all field names
 */

ChangeColumnSize('fieldname', 'reportfields', 'varchar(60)', 'NOT NULL', '', 60);

/* Database changes needed for the picking list functionality
 */

NewConfigValue('RequirePickingNote', 0);

CreateTable("pickinglists", "CREATE TABLE `pickinglists` (
  `pickinglistno` int(11) NOT NULL DEFAULT 0,
  `orderno` int(11) NOT NULL DEFAULT 0,
  `pickinglistdate` date NOT NULL default '0000-00-00',
  `dateprinted` date NOT NULL default '0000-00-00',
  `deliverynotedate` date NOT NULL default '0000-00-00',
  CONSTRAINT `pickinglists_ibfk_1` FOREIGN KEY (`orderno`) REFERENCES `salesorders` (`orderno`),
  PRIMARY KEY (`pickinglistno`)
)");

CreateTable("pickinglistdetails", "CREATE TABLE `pickinglistdetails` (
  `pickinglistno` int(11) NOT NULL DEFAULT 0,
  `pickinglistlineno` int(11) NOT NULL DEFAULT 0,
  `orderlineno` int(11) NOT NULL DEFAULT 0,
  `qtyexpected` double NOT NULL default 0.00,
  `qtypicked` double NOT NULL default 0.00,
  CONSTRAINT `pickinglistdetails_ibfk_1` FOREIGN KEY (`pickinglistno`) REFERENCES `pickinglists` (`pickinglistno`),
  PRIMARY KEY (`pickinglistno`, `pickinglistlineno`)
)");

/* Database changes required to add start and end dates for sales prices
 */

AddColumn('startdate', 'prices', 'Date', 'NOT NULL', '0000-00-00', 'branchcode');
AddColumn('enddate', 'prices', 'Date', 'NOT NULL', '9999-12-31', 'startdate');

DropPrimaryKey('prices', array(
	'stockid',
	'typeabbrev',
	'currabrev',
	'debtorno'
));
AddPrimaryKey('prices', array(
	'stockid',
	'typeabbrev',
	'currabrev',
	'debtorno',
	'branchcode',
	'startdate',
	'enddate'
));
UpdateField('prices', 'startdate', '1999-01-01', "stockid='%'");
UpdateField('prices', 'enddate', '', "stockid='%'");

/* Add in minimum order quantity field to the supplier purchasing data
 */

AddColumn('minorderqty', 'purchdata', 'int(11)', 'NOT NULL', '1', 'suppliers_partno');

/* Add in field to record at what date the stock check freeze was made
 */

AddColumn('stockcheckdate', 'stockcheckfreeze', 'date', 'NOT NULL', '0000-00-00', 'qoh');

/* Add extra columns for supplier head office details
 */

AddColumn('email', 'suppliers', 'varchar(55)', 'NOT NULL', '', 'port');
AddColumn('fax', 'suppliers', 'varchar(25)', 'NOT NULL', '', 'email');
AddColumn('telephone', 'suppliers', 'varchar(25)', 'NOT NULL', '', 'fax');

/* Add extra database items needed for supplier only login
 */

AddColumn('supplierid', 'www_users', 'varchar(10)', 'NOT NULL', '', 'customerid');
InsertRecord('securityroles', array(
	'secroleid',
	'secrolename'
), array(
	7,
	'Customer Log On Only'
), array(
	'secroleid',
	'secrolename'
), array(
	7,
	'Customer Log On Only'
));
InsertRecord('securityroles', array(
	'secroleid',
	'secrolename'
), array(
	8,
	'System Administrator'
), array(
	'secroleid',
	'secrolename'
), array(
	8,
	'System Administrator'
));
InsertRecord('securityroles', array(
	'secroleid',
	'secrolename'
), array(
	9,
	'Supplier Log On Only'
), array(
	'secroleid',
	'secrolename'
), array(
	9,
	'Supplier Log On Only'
));

InsertRecord('securitytokens', array(
	'tokenid'
), array(
	1
), array(
	'tokenid',
	'tokenname'
), array(
	1,
	_('Order Entry/Inquiries customer access only')
));
InsertRecord('securitytokens', array(
	'tokenid'
), array(
	2
), array(
	'tokenid',
	'tokenname'
), array(
	2,
	_('Basic Reports and Inquiries with selection options')
));
InsertRecord('securitytokens', array(
	'tokenid'
), array(
	3
), array(
	'tokenid',
	'tokenname'
), array(
	3,
	_('Credit notes and AR management')
));
InsertRecord('securitytokens', array(
	'tokenid'
), array(
	4
), array(
	'tokenid',
	'tokenname'
), array(
	4,
	_('Purchasing data/PO Entry/Reorder Levels ')
));
InsertRecord('securitytokens', array(
	'tokenid'
), array(
	5
), array(
	'tokenid',
	'tokenname'
), array(
	5,
	_('Accounts Payable')
));
InsertRecord('securitytokens', array(
	'tokenid'
), array(
	6
), array(
	'tokenid',
	'tokenname'
), array(
	6,
	_('Petty Cash')
));
InsertRecord('securitytokens', array(
	'tokenid'
), array(
	7
), array(
	'tokenid',
	'tokenname'
), array(
	7,
	_('Bank Reconciliations')
));
InsertRecord('securitytokens', array(
	'tokenid'
), array(
	8
), array(
	'tokenid',
	'tokenname'
), array(
	8,
	_('General ledger reports/inquiries')
));
InsertRecord('securitytokens', array(
	'tokenid'
), array(
	9
), array(
	'tokenid',
	'tokenname'
), array(
	9,
	_('Supplier centre - Supplier access only')
));
InsertRecord('securitytokens', array(
	'tokenid'
), array(
	10
), array(
	'tokenid',
	'tokenname'
), array(
	10,
	_('General Ledger Maintenance, stock valuation and Configuration')
));
InsertRecord('securitytokens', array(
	'tokenid'
), array(
	11
), array(
	'tokenid',
	'tokenname'
), array(
	11,
	_('Inventory Management and Pricing')
));
InsertRecord('securitytokens', array(
	'tokenid'
), array(
	12
), array(
	'tokenid',
	'tokenname'
), array(
	12,
	_('Unknown')
));
InsertRecord('securitytokens', array(
	'tokenid'
), array(
	13
), array(
	'tokenid',
	'tokenname'
), array(
	13,
	_('Unknown')
));
InsertRecord('securitytokens', array(
	'tokenid'
), array(
	14
), array(
	'tokenid',
	'tokenname'
), array(
	14,
	_('Unknown')
));
InsertRecord('securitytokens', array(
	'tokenid'
), array(
	15
), array(
	'tokenid',
	'tokenname'
), array(
	15,
	_('User Management and System Administration')
));

UpdateField('securitytokens', 'tokenname', _('Supplier centre - Supplier access only'), 'tokenid=9');
InsertRecord('securitygroups', array(
	'secroleid',
	'tokenid'
), array(
	9,
	9
), array(
	'secroleid',
	'tokenid'
), array(
	9,
	9
));
InsertRecord('securitygroups', array(
	'secroleid',
	'tokenid'
), array(
	7,
	1
), array(
	'secroleid',
	'tokenid'
), array(
	7,
	1
));
for ($i = 1; $i <= 15; $i++) {
	InsertRecord('securitygroups', array(
		'secroleid',
		'tokenid'
	), array(
		8,
		$i
	), array(
		'secroleid',
		'tokenid'
	), array(
		8,
		$i
	));
}

/* add a field to each location giving a customer/branch combination
 * that can be used for cash sales at that location
 */

AddColumn('cashsalecustomer', 'locations', 'varchar(21)', 'NOT NULL', '', 'taxprovinceid');

/* New database components required for contracts module
 */

DropTable('contracts', 'rate');
DropTable('contractreqts', 'component');
DropTable('contractbom', 'component');

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
)");

CreateTable('contractreqts', "CREATE TABLE IF NOT EXISTS `contractreqts` (
  `contractreqid` int(11) NOT NULL AUTO_INCREMENT,
  `contractref` varchar(20) NOT NULL DEFAULT '0',
  `requirement` varchar(40) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '1',
  `costperunit` double NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`contractreqid`),
  KEY `ContractRef` (`contractref`),
  CONSTRAINT `contractreqts_ibfk_1` FOREIGN KEY (`contractref`) REFERENCES `contracts` (`contractref`)
)");


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
)");

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
)");

/* Increase the size of the salesType field to 40 characters
 */

ChangeColumnSize('sales_type', 'salestypes', 'varchar(40)', 'NOT NULL', '', 40);

/* New config value to determine whether values are shown on the grn
 * screen and the printed grn
 */

NewConfigValue('ShowValueOnGRN', 1);

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
)");

/* New config value for the Purchasing managers email address
 */

NewConfigValue('PurchasingManagerEmail', '');

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
)");

InsertRecord('emailsettings', array(
	'id',
	'host',
	'port',
	'heloaddress',
	'username',
	'password',
	'timeout',
	'companyname',
	'auth'
), array(
	Null,
	'localhost',
	25,
	'helo',
	'',
	'',
	5,
	'',
	0
), array(
	'id',
	'host',
	'port',
	'heloaddress',
	'username',
	'password',
	'timeout',
	'companyname',
	'auth'
), array(
	Null,
	'localhost',
	25,
	'helo',
	'',
	'',
	5,
	'',
	0
));

/* New fields for sales commission work
 */

AddColumn('commissionrate', 'salesorderdetails', 'double', 'NOT NULL', 0.0, 'poline');
AddColumn('commissionearned', 'salesorderdetails', 'double', 'NOT NULL', 0.0, 'commissionrate');

/* New supplier type field and table
 */

CreateTable('suppliertype', "CREATE TABLE `suppliertype` (
  `typeid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `typename` varchar(100) NOT NULL,
  PRIMARY KEY (`typeid`)
)");

NewConfigValue('DefaultSupplierType', 1);
InsertRecord('suppliertype', array(
	'typeid',
	'typename'
), array(
	NULL,
	'Default'
), array(
	'typeid',
	'typename'
), array(
	NULL,
	'Default'
));
AddColumn('supptype', 'suppliers', 'tinyint(4)', 'NOT NULL', 1, 'address6');

/* Change the shipment quantity to a double from integer,
 * as stock quantities can be of type double
 */

ChangeColumnType('shipqty', 'loctransfers', 'double', 'NOT NULL', 0.0);

/* Create a new security token foer prices security, so that only
 * certain roles can view pricing information
 */

UpdateField('securitytokens', 'tokenname', 'Prices Security', 'tokenid=12');

/* Remove the primary key on table orderdeliverydifferenceslog
 */

DropPrimaryKey('orderdeliverydifferenceslog', array(
	'orderno',
	'invoiceno',
	'stockid'
));

/* Chenge received quantity to a type of double as stock
 * quantities are not necessarily integers
 */

ChangeColumnType('recqty', 'loctransfers', 'double', 'NOT NULL', 0.0);

/* New system type needed for contract work
 */

/* Add extra columns to reports
 */

for ($i = 9; $i <= 20; $i++) {
	AddColumn('col' . $i . 'width', 'reports', 'int(3)', 'NOT NULL', '25', 'col' . ($i - 1) . 'width');
}

/* Increase the size of the fieldname field to accomodate all field names
 */

ChangeColumnSize('fieldname', 'reportfields', 'varchar(80)', 'NOT NULL', '', 80);
/* Addin extra fields to stockcatproperties table
 */

AddColumn('maximumvalue', 'stockcatproperties', 'Double', 'NOT NULL', 999999999, 'defaultvalue');
AddColumn('minimumvalue', 'stockcatproperties', 'Double', 'NOT NULL', -999999999, 'maximumvalue');
AddColumn('numericvalue', 'stockcatproperties', 'tinyint', 'NOT NULL', 0, 'minimumvalue');


/* Lots of database changes required for the move from fixed
 * asset manager v2 to v3
 */

DropTable('assetmanager', 'lifetime');

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
)");

CreateTable("fixedassetlocations", "CREATE TABLE `fixedassetlocations` (
	`locationid` char(6) NOT NULL default '',
	`locationdescription` char(20) NOT NULL default '',
	`parentlocationid` char(6) DEFAULT '',
	PRIMARY KEY  (`locationid`)
)");

RenameTable('assetmanager', 'fixedassets');
AddColumn('assetcategoryid', 'fixedassets', 'varchar(6)', 'NOT NULL', '', 'disposalvalue');
AddColumn('description', 'fixedassets', 'varchar(50)', 'NOT NULL', '', 'assetcategoryid');
AddColumn('longdescription', 'fixedassets', 'text', 'NOT NULL', '', 'description');
AddColumn('depntype', 'fixedassets', 'int(11)', 'NOT NULL', 1, 'longdescription');
AddColumn('depnrate', 'fixedassets', 'double', 'NOT NULL', 0.0, 'depntype');
AddColumn('barcode', 'fixedassets', 'varchar(30)', 'NOT NULL', '', 'depnrate');
ChangeColumnName('depn', 'fixedassets', 'double', 'NOT NULL', 0.0, 'accumdepn');
ChangeColumnName('location', 'fixedassets', 'varchar(6)', 'NOT NULL', '', 'assetlocation');

if (DB_table_exists('fixedassets')) {
	$SQL = "desc fixedassets stockid";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) > 0) {
		$response = executeSQL("UPDATE fixedassets INNER JOIN stockmaster ON fixedassets.stockid=stockmaster.stockid SET assetcategoryid=stockmaster.categoryid,
fixedassets.description=stockmaster.description, fixedassets.longdescription=stockmaster.longdescription", False);
		if ($response == 0) {
			OutputResult(_('The fixedassets table has been updated from stockmaster'), 'success');
		} else {
			OutputResult(_('The fixedassets table could not be updated from stockmaster'), 'error');
		}
	} else {
		OutputResult(_('The fixedassets table is already correct'), 'info');
	}
} else {
	$response = executeSQL("UPDATE fixedassets INNER JOIN stockmaster ON fixedassets.stockid=stockmaster.stockid SET assetcategoryid=stockmaster.categoryid,
fixedassets.description=stockmaster.description, fixedassets.longdescription=stockmaster.longdescription", False);
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
)");

if (DB_table_exists('fixedassets')) {
	$SQL = "SELECT categoryid FROM fixedassetcategories";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 0) {
		$response = executeSQL("INSERT INTO fixedassetcategories (categoryid, categorydescription, costact, depnact, disposalact, accumdepnact)
SELECT categoryid, categorydescription, stockact, adjglact, materialuseagevarac, wipact FROM stockcategory WHERE stocktype='A'", False);
		if ($response == 0) {
			OutputResult(_('The fixedassetcategories table has been updated from stockcategory'), 'success');
		} else {
			OutputResult(_('The fixedassetcategories table could not be updated from stockcategory'), 'error');
		}
	} else {
		OutputResult(_('The fixedassetcategories table is already correct'), 'info');
	}
} else {
	$response = executeSQL("INSERT INTO fixedassetcategories (categoryid, categorydescription, costact, depnact, disposalact, accumdepnact)
SELECT categoryid, categorydescription, stockact, adjglact, materialuseagevarac, wipact FROM stockcategory WHERE stocktype='A'", False);
}

$SQL = "SELECT categoryid FROM stockcategory WHERE stockcategory.stocktype='A'";
$Result = DB_query($SQL);
if (DB_num_rows($Result) != 0) {
	$response = executeSQL("DELETE locstock.* FROM locstock INNER JOIN stockmaster ON locstock.stockid=stockmaster.stockid INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid WHERE stockcategory.stocktype='A'", False);
	if ($response == 0) {
		OutputResult(_('The fixedassetcategories have been removed from stockcategory'), 'success');
	} else {
		OutputResult(_('The fixedassetcategories table could not be removed from stockcategory'), 'error');
	}
} else {
	OutputResult(_('The stockcategory table is already correct'), 'info');
}

$SQL = "SELECT stockitemproperties.stockid
	FROM stockitemproperties
	INNER JOIN stockmaster
		ON stockitemproperties.stockid=stockmaster.stockid
	INNER JOIN stockcategory
		ON stockmaster.categoryid=stockcategory.categoryid
	WHERE stockcategory.stocktype='A'";
$Result = DB_query($SQL);
if (DB_num_rows($Result) != 0) {
	$response = executeSQL("DELETE stockitemproperties.* FROM stockitemproperties INNER JOIN stockmaster ON stockitemproperties.stockid=stockmaster.stockid INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid WHERE stockcategory.stocktype='A'", False);
	if ($response == 0) {
		OutputResult(_('The fixed assets have been removed from stockitemproperties'), 'success');
	} else {
		OutputResult(_('The fixed assets could not be removed from stockitemproperties'), 'error');
	}
} else {
	OutputResult(_('The fixed assets have already been removed from stockitemproperties'), 'info');
}

$SQL = "SELECT stockserialmoves.* FROM stockserialmoves, stockmoves,
stockmaster,stockcategory WHERE stockserialmoves.stockmoveno=stockmoves.stkmoveno AND
stockmoves.stockid = stockmaster.stockid AND stockmaster.categoryid = stockcategory.categoryid AND stockcategory.stocktype = 'A'";
$Result = DB_query($SQL);
if (DB_num_rows($Result) != 0) {
	$response = executeSQL("DELETE stockserialmoves.* FROM stockserialmoves, stockmoves,
stockmaster,stockcategory WHERE stockserialmoves.stockmoveno=stockmoves.stkmoveno AND
stockmoves.stockid = stockmaster.stockid AND stockmaster.categoryid = stockcategory.categoryid AND stockcategory.stocktype = 'A'", False);
	if ($response == 0) {
		OutputResult(_('The fixed assets have been removed from stockitemproperties'), 'success');
	} else {
		OutputResult(_('The fixed assets could not be removed from stockitemproperties'), 'error');
	}
} else {
	OutputResult(_('The fixed assets have already been removed from stockitemproperties'), 'info');
}

$SQL = "SELECT stockserialitems.* FROM stockserialitems, stockmaster, stockcategory
WHERE stockserialitems.stockid = stockmaster.stockid AND stockmaster.categoryid=stockcategory.categoryid AND stocktype='A'";
$Result = DB_query($SQL);
if (DB_num_rows($Result) != 0) {
	$response = executeSQL("DELETE stockserialitems.* FROM stockserialitems, stockmaster, stockcategory
WHERE stockserialitems.stockid = stockmaster.stockid AND stockmaster.categoryid=stockcategory.categoryid AND stocktype='A'", False);
	if ($response == 0) {
		OutputResult(_('The fixed assets have been removed from stockserialitems, stockmaster, and stockcategory tables'), 'success');
	} else {
		OutputResult(_('The fixed assets could not be removed from stockserialitems, stockmaster, and stockcategory tables'), 'error');
	}
} else {
	OutputResult(_('The fixed assets have already been removed from stockserialitems, stockmaster, and stockcategory tables'), 'info');
}

$SQL = "SELECT stockmoves.*
		FROM stockmoves,
			stockmaster,
			stockcategory
		WHERE stockmoves.stockid = stockmaster.stockid
			AND stockmaster.categoryid = stockcategory.categoryid
			AND stockcategory.stocktype = 'A'";
$Result = DB_query($SQL);
if (DB_num_rows($Result) != 0) {
	$response = executeSQL("DELETE stockmoves.* FROM stockmoves, stockmaster, stockcategory WHERE stockmoves.stockid = stockmaster.stockid AND stockmaster.categoryid = stockcategory.categoryid AND stockcategory.stocktype = 'A'", False);
	if ($response == 0) {
		OutputResult(_('The fixed assets have been removed from stockmoves, stockmaster, and stockcategory tables'), 'success');
	} else {
		OutputResult(_('The fixed assets could not be removed from stockmoves, stockmaster, and stockcategory tables'), 'error');
	}
} else {
	OutputResult(_('The fixed assets have already been removed from stockmoves, stockmaster, and stockcategory tables'), 'info');
}

$SQL = "SELECT stockmaster.* FROM stockmaster INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid WHERE stockcategory.stocktype='A'";
$Result = DB_query($SQL);
if (DB_num_rows($Result) != 0) {
	$response = executeSQL("DELETE stockmaster.* FROM stockmaster INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid WHERE stockcategory.stocktype='A'", False);
	if ($response == 0) {
		OutputResult(_('The fixed assets have been removed from stockmaster table'), 'success');
	} else {
		OutputResult(_('The fixed assets could not be removed from stockmaster table'), 'error');
	}
} else {
	OutputResult(_('The fixed assets have already been removed from stockmaster table'), 'info');
}

ChangeColumnName('id', 'fixedassets', 'int(11)', 'NOT NULL', 0, 'assetid', 'AUTO_INCREMENT');

$SQL = "SELECT categoryid FROM  stockcategory WHERE stocktype='A'";
$Result = DB_query($SQL);
if (DB_num_rows($Result) != 0) {
	$response = executeSQL("DELETE FROM stockcategory WHERE stocktype='A'", False);
	if ($response == 0) {
		OutputResult(_('The fixed assets have been removed from stockmaster table'), 'success');
	} else {
		OutputResult(_('The fixed assets could not be removed from stockmaster table'), 'error');
	}
} else {
	OutputResult(_('The fixed assets have already been removed from stockmaster table'), 'info');
}

DropColumn('stockid', 'fixedassets');

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
)");

AddColumn('assetid', 'purchorderdetails', 'int(11)', 'NOT NULL', 0, 'total_amount');

/* New database stuff to move the page security levels to a
 * database table.
 */

DropTable('scripts', 'pagedescription');

CreateTable('scripts', "CREATE TABLE `scripts` (
	`script` varchar(78) NOT NULL DEFAULT '',
	`pagesecurity` int(11) NOT NULL DEFAULT 1,
	`description` varchar(78) NOT NULL DEFAULT '',
	PRIMARY KEY  (`script`)
)");

NewScript('AccountGroups.php', 10);
NewScript('AccountSections.php', 10);
NewScript('AddCustomerContacts.php', 3);
NewScript('AddCustomerNotes.php', 3);
NewScript('AddCustomerTypeNotes.php', 3);
NewScript('AgedDebtors.php', 2);
NewScript('AgedSuppliers.php', 2);
NewScript('Areas.php', 3);
NewScript('AuditTrail.php', 15);
NewScript('BankAccounts.php', 10);
NewScript('BankMatching.php', 7);
NewScript('BankReconciliation.php', 7);
NewScript('BOMExtendedQty.php', 2);
NewScript('BOMIndented.php', 2);
NewScript('BOMIndentedReverse.php', 2);
NewScript('BOMInquiry.php', 2);
NewScript('BOMListing.php', 2);
NewScript('BOMs.php', 9);
NewScript('COGSGLPostings.php', 10);
NewScript('CompanyPreferences.php', 10);
NewScript('ConfirmDispatchControlled_Invoice.php', 11);
NewScript('ConfirmDispatch_Invoice.php', 2);
NewScript('ContractBOM.php', 6);
NewScript('ContractCosting.php', 6);
NewScript('ContractOtherReqts.php', 4);
NewScript('Contracts.php', 6);
NewScript('CounterSales.php', 1);
NewScript('Credit_Invoice.php', 3);
NewScript('CreditItemsControlled.php', 3);
NewScript('CreditStatus.php', 3);
NewScript('Currencies.php', 9);
NewScript('CustEDISetup.php', 11);
NewScript('CustLoginSetup.php', 15);
NewScript('CustomerAllocations.php', 3);
NewScript('CustomerBranches.php', 3);
NewScript('CustomerInquiry.php', 1);
NewScript('CustomerReceipt.php', 3);
NewScript('Customers.php', 3);
NewScript('CustomerTransInquiry.php', 2);
NewScript('CustomerTypes.php', 15);
NewScript('CustWhereAlloc.php', 2);
NewScript('DailyBankTransactions.php', 8);
NewScript('DailySalesInquiry.php', 2);
NewScript('DebtorsAtPeriodEnd.php', 2);
NewScript('DeliveryDetails.php', 1);
NewScript('DiscountCategories.php', 11);
NewScript('DiscountMatrix.php', 11);
NewScript('EDIMessageFormat.php', 10);
NewScript('EDIProcessOrders.php', 11);
NewScript('EDISendInvoices.php', 15);
NewScript('EmailConfirmation.php', 2);
NewScript('EmailCustTrans.php', 2);
NewScript('ExchangeRateTrend.php', 2);
NewScript('Factors.php', 5);
NewScript('FixedAssetCategories.php', 11);
NewScript('FixedAssetDepreciation.php', 10);
NewScript('FixedAssetItems.php', 11);
NewScript('FixedAssetList.php', 11);
NewScript('FixedAssetLocations.php', 11);
NewScript('FixedAssetRegister.php', 11);
NewScript('FixedAssetTransfer.php', 11);
NewScript('FormDesigner.php', 14);
NewScript('FreightCosts.php', 11);
NewScript('FTP_RadioBeacon.php', 2);
NewScript('geocode_genxml_customers.php', 3);
NewScript('geocode_genxml_suppliers.php', 3);
NewScript('geocode.php', 3);
NewScript('GeocodeSetup.php', 3);
NewScript('geo_displaymap_customers.php', 3);
NewScript('geo_displaymap_suppliers.php', 3);
NewScript('GetStockImage.php', 1);
NewScript('GLAccountCSV.php', 8);
NewScript('GLAccountInquiry.php', 8);
NewScript('GLAccountReport.php', 8);
NewScript('GLAccounts.php', 10);
NewScript('GLBalanceSheet.php', 8);
NewScript('GLBudgets.php', 10);
NewScript('GLCodesInquiry.php', 8);
NewScript('GLJournal.php', 10);
NewScript('GLProfit_Loss.php', 8);
NewScript('GLTagProfit_Loss.php', 8);
NewScript('GLTags.php', 10);
NewScript('GLTransInquiry.php', 8);
NewScript('GLTrialBalance_csv.php', 8);
NewScript('GLTrialBalance.php', 8);
NewScript('GoodsReceivedControlled.php', 11);
NewScript('GoodsReceived.php', 11);
NewScript('index.php', 1);
NewScript('InventoryPlanning.php', 2);
NewScript('InventoryPlanningPrefSupplier.php', 2);
NewScript('InventoryQuantities.php', 2);
NewScript('InventoryValuation.php', 2);
NewScript('Labels.php', 15);
NewScript('Locations.php', 11);
NewScript('Logout.php', 1);
NewScript('MailInventoryValuation.php', 1);
NewScript('scriptsAccess.php', 15);
NewScript('MRPCalendar.php', 9);
NewScript('MRPCreateDemands.php', 9);
NewScript('MRPDemands.php', 9);
NewScript('MRPDemandTypes.php', 9);
NewScript('MRP.php', 9);
NewScript('MRPPlannedPurchaseOrders.php', 2);
NewScript('MRPPlannedWorkOrders.php', 2);
NewScript('MRPReport.php', 2);
NewScript('MRPReschedules.php', 2);
NewScript('MRPShortages.php', 2);
NewScript('OffersReceived.php', 4);
NewScript('OrderDetails.php', 2);
NewScript('OutstandingGRNs.php', 2);
NewScript('PaymentAllocations.php', 5);
NewScript('PaymentMethods.php', 15);
NewScript('Payments.php', 5);
NewScript('PaymentTerms.php', 10);
NewScript('PcAssignCashToTab.php', 6);
NewScript('PcAuthorizeExpenses.php', 6);
NewScript('PcClaimExpensesFromTab.php', 6);
NewScript('PcExpenses.php', 15);
NewScript('PcExpensesTypeTab.php', 15);
NewScript('PcReportTab.php', 6);
NewScript('PcTabs.php', 15);
NewScript('PcTypeTabs.php', 15);
NewScript('PDFBankingSummary.php', 3);
NewScript('PDFChequeListing.php', 3);
NewScript('PDFCustomerList.php', 2);
NewScript('PDFCustTransListing.php', 3);
NewScript('PDFDeliveryDifferences.php', 3);
NewScript('PDFDIFOT.php', 3);
NewScript('PDFGrn.php', 2);
NewScript('PDFLowGP.php', 2);
NewScript('PDFOrdersInvoiced.php', 3);
NewScript('PDFOrderStatus.php', 3);
NewScript('PDFPickingList.php', 2);
NewScript('PDFPriceList.php', 2);
NewScript('PDFPrintLabel.php', 10);
NewScript('PDFQuotation.php', 2);
NewScript('PDFReceipt.php', 2);
NewScript('PDFRemittanceAdvice.php', 2);
NewScript('PDFStockCheckComparison.php', 2);
NewScript('PDFStockLocTransfer.php', 1);
NewScript('PDFStockNegatives.php', 1);
NewScript('PDFStockTransfer.php', 2);
NewScript('PDFStockTransListing.php', 3);
NewScript('PDFSuppTransListing.php', 3);
NewScript('PDFTopItems.php', 2);
NewScript('PeriodsInquiry.php', 2);
NewScript('PO_AuthorisationLevels.php', 15);
NewScript('PO_AuthoriseMyOrders.php', 4);
NewScript('PO_Header.php', 4);
NewScript('PO_Items.php', 4);
NewScript('PO_OrderDetails.php', 2);
NewScript('PO_PDFPurchOrder.php', 2);
NewScript('POReport.php', 2);
NewScript('PO_SelectOSPurchOrder.php', 2);
NewScript('PO_SelectPurchOrder.php', 2);
NewScript('PricesBasedOnMarkUp.php', 11);
NewScript('PricesByCost.php', 11);
NewScript('Prices_Customer.php', 11);
NewScript('Prices.php', 9);
NewScript('PrintCheque.php', 5);
NewScript('PrintCustOrder_generic.php', 2);
NewScript('PrintCustOrder.php', 2);
NewScript('PrintCustStatements.php', 2);
NewScript('PrintCustTrans.php', 1);
NewScript('PrintCustTransPortrait.php', 1);
NewScript('PrintSalesOrder_generic.php', 2);
NewScript('PurchData.php', 4);
NewScript('RecurringSalesOrders.php', 1);
NewScript('ReorderLevelLocation.php', 2);
NewScript('ReorderLevel.php', 2);
NewScript('ReportBug.php', 15);
NewScript('ReverseGRN.php', 11);
NewScript('SalesAnalReptCols.php', 2);
NewScript('SalesAnalRepts.php', 2);
NewScript('SalesAnalysis_UserDefined.php', 2);
NewScript('SalesCategories.php', 11);
NewScript('SalesGLPostings.php', 10);
NewScript('SalesGraph.php', 6);
NewScript('SalesInquiry.php', 2);
NewScript('SalesPeople.php', 3);
NewScript('SalesTypes.php', 15);
NewScript('SelectAsset.php', 2);
NewScript('SelectCompletedOrder.php', 1);
NewScript('SelectContract.php', 6);
NewScript('SelectCreditItems.php', 3);
NewScript('SelectCustomer.php', 2);
NewScript('SelectGLAccount.php', 8);
NewScript('SelectOrderItems.php', 1);
NewScript('SelectProduct.php', 2);
NewScript('SelectRecurringSalesOrder.php', 2);
NewScript('SelectSalesOrder.php', 2);
NewScript('SelectSupplier.php', 2);
NewScript('SelectWorkOrder.php', 2);
NewScript('ShipmentCosting.php', 11);
NewScript('Shipments.php', 11);
NewScript('Shippers.php', 15);
NewScript('Shipt_Select.php', 11);
NewScript('ShiptsList.php', 2);
NewScript('SMTPServer.php', 15);
NewScript('SpecialOrder.php', 4);
NewScript('StockAdjustmentsControlled.php', 11);
NewScript('StockAdjustments.php', 11);
NewScript('StockCategories.php', 11);
NewScript('StockCheck.php', 2);
NewScript('StockCostUpdate.php', 9);
NewScript('StockCounts.php', 2);
NewScript('StockDispatch.php', 2);
NewScript('StockLocMovements.php', 2);
NewScript('StockLocStatus.php', 2);
NewScript('StockLocTransfer.php', 11);
NewScript('StockLocTransferReceive.php', 11);
NewScript('StockMovements.php', 2);
NewScript('StockQties_csv.php', 5);
NewScript('StockQuantityByDate.php', 2);
NewScript('StockReorderLevel.php', 4);
NewScript('StockSerialItemResearch.php', 3);
NewScript('StockSerialItems.php', 2);
NewScript('Stocks.php', 11);
NewScript('StockStatus.php', 2);
NewScript('StockTransferControlled.php', 11);
NewScript('StockTransfers.php', 11);
NewScript('StockUsageGraph.php', 2);
NewScript('StockUsage.php', 2);
NewScript('SuppContractChgs.php', 5);
NewScript('SuppCreditGRNs.php', 5);
NewScript('SuppFixedAssetChgs.php', 5);
NewScript('SuppInvGRNs.php', 5);
NewScript('SupplierAllocations.php', 5);
NewScript('SupplierBalsAtPeriodEnd.php', 2);
NewScript('SupplierContacts.php', 5);
NewScript('SupplierCredit.php', 5);
NewScript('SupplierInquiry.php', 2);
NewScript('SupplierInvoice.php', 5);
NewScript('Suppliers.php', 5);
NewScript('SupplierTenders.php', 9);
NewScript('SupplierTransInquiry.php', 2);
NewScript('SupplierTypes.php', 4);
NewScript('SuppLoginSetup.php', 15);
NewScript('SuppPaymentRun.php', 5);
NewScript('SuppPriceList.php', 2);
NewScript('SuppShiptChgs.php', 5);
NewScript('SuppTransGLAnalysis.php', 5);
NewScript('SystemCheck.php', 10);
NewScript('SystemParameters.php', 15);
NewScript('TaxAuthorities.php', 15);
NewScript('TaxAuthorityRates.php', 11);
NewScript('TaxCategories.php', 15);
NewScript('TaxGroups.php', 15);
NewScript('Tax.php', 2);
NewScript('TaxProvinces.php', 15);
NewScript('TopItems.php', 2);
NewScript('UnitsOfMeasure.php', 15);
NewScript('UserSettings.php', 1);
NewScript('WhereUsedInquiry.php', 2);
NewScript('WorkCentres.php', 9);
NewScript('WorkOrderCosting.php', 11);
NewScript('WorkOrderEntry.php', 10);
NewScript('WorkOrderIssue.php', 11);
NewScript('WorkOrderReceive.php', 11);
NewScript('WorkOrderStatus.php', 11);
NewScript('WOSerialNos.php', 10);
NewScript('WWW_Access.php', 15);
NewScript('WWW_Users.php', 15);
NewScript('Z_BottomUpCosts.php', 15);
NewScript('Z_ChangeBranchCode.php', 15);
NewScript('Z_ChangeCustomerCode.php', 15);
NewScript('Z_ChangeStockCategory.php', 15);
NewScript('Z_ChangeStockCode.php', 15);
NewScript('Z_CheckAllocationsFrom.php', 15);
NewScript('Z_CheckAllocs.php', 2);
NewScript('Z_CheckDebtorsControl.php', 15);
NewScript('Z_CheckGLTransBalance.php', 15);
NewScript('Z_CopyBOM.php', 9);
NewScript('Z_CreateChartDetails.php', 9);
NewScript('Z_CreateCompany.php', 15);
NewScript('Z_CreateCompanyTemplateFile.php', 15);
NewScript('Z_CurrencyDebtorsBalances.php', 15);
NewScript('Z_CurrencySuppliersBalances.php', 15);
NewScript('Z_DataExport.php', 15);
NewScript('Z_DeleteCreditNote.php', 15);
NewScript('Z_DeleteInvoice.php', 15);
NewScript('Z_DeleteSalesTransActions.php', 15);
NewScript('Z_DescribeTable.php', 11);
NewScript('Z_ImportChartOfAccounts.php', 11);
NewScript('Z_ImportFixedAssets.php', 15);
NewScript('Z_ImportGLAccountGroups.php', 11);
NewScript('Z_ImportGLAccountSections.php', 11);
NewScript('Z_ImportPartCodes.php', 11);
NewScript('Z_ImportStocks.php', 15);
NewScript('Z_index.php', 15);
NewScript('Z_MakeNewCompany.php', 15);
NewScript('Z_MakeStockLocns.php', 15);
NewScript('Z_poAddLanguage.php', 15);
NewScript('Z_poAdmin.php', 15);
NewScript('Z_poEditLangHeader.php', 15);
NewScript('Z_poEditLangModule.php', 15);
NewScript('Z_poEditLangRemaining.php', 15);
NewScript('Z_poRebuildDefault.php', 15);
NewScript('Z_PriceChanges.php', 15);
NewScript('Z_ReApplyCostToSA.php', 15);
NewScript('Z_RePostGLFromPeriod.php', 15);
NewScript('Z_ReverseSuppPaymentRun.php', 15);
NewScript('Z_SalesIntegrityCheck.php', 15);
NewScript('Z_UpdateChartDetailsBFwd.php', 15);
NewScript('Z_Upgrade_3.01-3.02.php', 15);
NewScript('Z_Upgrade_3.04-3.05.php', 15);
NewScript('Z_Upgrade_3.05-3.06.php', 15);
NewScript('Z_Upgrade_3.07-3.08.php', 15);
NewScript('Z_Upgrade_3.08-3.09.php', 15);
NewScript('Z_Upgrade_3.09-3.10.php', 15);
NewScript('Z_Upgrade_3.10-3.11.php', 15);
NewScript('Z_Upgrade3.10.php', 15);
NewScript('Z_Upgrade_3.11-4.00.php', 15);
NewScript('Z_UploadForm.php', 15);
NewScript('Z_UploadResult.php', 15);
NewScript('ReportletContainer.php', 1);
NewScript('PageSecurity.php', 15);
NewScript('UpgradeDatabase.php', 15);
NewScript('ManualContents.php', 10);
NewScript('FormMaker.php', 1);
NewScript('ReportMaker.php', 1);
NewScript('ReportCreator.php', 13);

NewConfigValue('VersionNumber', '3.12.0');
ChangeConfigValue('VersionNumber', '3.12.1');
ChangeConfigValue('VersionNumber', '3.12.2');

ChangeColumnName('nw', 'purchorderdetails', 'VARCHAR(50)', 'NOT NULL', '', 'netweight');
ChangeColumnName('gw', 'purchorderdetails', 'VARCHAR(50)', 'NOT NULL', '', 'kgs');
AddColumn('conversionfactor', 'purchorderdetails', 'DOUBLE', 'NOT NULL', '1', 'assetid');
ChangeConfigValue('VersionNumber', '3.12.3');
ChangeColumnName('uom', 'purchorderdetails', 'VARCHAR(50)', 'NOT NULL', '', 'suppliersunit');
ChangeConfigValue('VersionNumber', '3.12.31');
NewConfigValue('AutoAuthorisePO', '1');
ChangeConfigValue('VersionNumber', '4.03');

AddColumn('poplaced', 'salesorders', 'TINYINT', 'NOT NULL', '0', 'quotedate');
AddIndex(array(
	'poplaced'
), 'salesorders', 'poplaced');

ChangeConfigValue('VersionNumber', '4.03.1');
ChangeConfigValue('VersionNumber', '4.03.2');

AddColumn('cashsalebranch', 'locations', 'VARCHAR(10)', 'NOT NULL', "Default ''", 'managed');
ChangeColumnType('cashsalecustomer', 'locations', 'VARCHAR(10)', 'NOT NULL', '');

ChangeConfigValue('VersionNumber', '4.03.3');

NewScript('Z_ChangeSupplierCode.php', '15');

ChangeConfigValue('VersionNumber', '4.03.5');

NewScript('ReprintGRN.php', '11');
ChangeConfigValue('VersionNumber', '4.03.6');

AddColumn('usepreprintedstationery', 'paymentmethods', 'TINYINT', 'NOT NULL', '0', 'receipttype');

RemoveScript('PDFStockTransListing.php');
NewScript('PDFPeriodStockTransListing.php', '3');
ChangeConfigValue('VersionNumber', '4.03.7');

DropColumn('itemno', 'purchorderdetails');
DropColumn('subtotal_amount', 'purchorderdetails');
DropColumn('package', 'purchorderdetails');
DropColumn('pcunit', 'purchorderdetails');
DropColumn('kgs', 'purchorderdetails');
DropColumn('cuft', 'purchorderdetails');
DropColumn('total_quantity', 'purchorderdetails');
DropColumn('netweight', 'purchorderdetails');
DropColumn('total_amount', 'purchorderdetails');
DropColumn('uom', 'purchorderdetails');

ChangeConfigValue('VersionNumber', '4.03.8');

ChangeColumnType('stockcheckdate', 'stockcheckfreeze', 'DATE', 'NOT NULL', '0000-00-00');

UpdateDBNo(basename(__FILE__, '.php'));

?>