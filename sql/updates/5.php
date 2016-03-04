<?php

CreateTable('reports',
"CREATE TABLE `reports` (
  `id` int(5) NOT NULL auto_increment,
  `reportname` varchar(30) NOT NULL default '',
  `reporttype` char(3) NOT NULL default 'rpt',
  `groupname` varchar(9) NOT NULL default 'misc',
  `defaultreport` enum('1','0') NOT NULL default '0',
  `papersize` varchar(15) NOT NULL default 'A4,210,297',
  `paperorientation` enum('P','L') NOT NULL default 'P',
  `margintop` int(3) NOT NULL default '10',
  `marginbottom` int(3) NOT NULL default '10',
  `marginleft` int(3) NOT NULL default '10',
  `marginright` int(3) NOT NULL default '10',
  `coynamefont` varchar(20) NOT NULL default 'Helvetica',
  `coynamefontsize` int(3) NOT NULL default '12',
  `coynamefontcolor` varchar(11) NOT NULL default '0,0,0',
  `coynamealign` enum('L','C','R') NOT NULL default 'C',
  `coynameshow` enum('1','0') NOT NULL default '1',
  `title1desc` varchar(50) NOT NULL default '%reportname%',
  `title1font` varchar(20) NOT NULL default 'Helvetica',
  `title1fontsize` int(3) NOT NULL default '10',
  `title1fontcolor` varchar(11) NOT NULL default '0,0,0',
  `title1fontalign` enum('L','C','R') NOT NULL default 'C',
  `title1show` enum('1','0') NOT NULL default '1',
  `title2desc` varchar(50) NOT NULL default 'Report Generated %date%',
  `title2font` varchar(20) NOT NULL default 'Helvetica',
  `title2fontsize` int(3) NOT NULL default '10',
  `title2fontcolor` varchar(11) NOT NULL default '0,0,0',
  `title2fontalign` enum('L','C','R') NOT NULL default 'C',
  `title2show` enum('1','0') NOT NULL default '1',
  `filterfont` varchar(10) NOT NULL default 'Helvetica',
  `filterfontsize` int(3) NOT NULL default '8',
  `filterfontcolor` varchar(11) NOT NULL default '0,0,0',
  `filterfontalign` enum('L','C','R') NOT NULL default 'L',
  `datafont` varchar(10) NOT NULL default 'Helvetica',
  `datafontsize` int(3) NOT NULL default '10',
  `datafontcolor` varchar(10) NOT NULL default 'black',
  `datafontalign` enum('L','C','R') NOT NULL default 'L',
  `totalsfont` varchar(10) NOT NULL default 'Helvetica',
  `totalsfontsize` int(3) NOT NULL default '10',
  `totalsfontcolor` varchar(11) NOT NULL default '0,0,0',
  `totalsfontalign` enum('L','C','R') NOT NULL default 'L',
  `col1width` int(3) NOT NULL default '25',
  `col2width` int(3) NOT NULL default '25',
  `col3width` int(3) NOT NULL default '25',
  `col4width` int(3) NOT NULL default '25',
  `col5width` int(3) NOT NULL default '25',
  `col6width` int(3) NOT NULL default '25',
  `col7width` int(3) NOT NULL default '25',
  `col8width` int(3) NOT NULL default '25',
  `table1` varchar(25) NOT NULL default '',
  `table2` varchar(25) default NULL,
  `table2criteria` varchar(75) default NULL,
  `table3` varchar(25) default NULL,
  `table3criteria` varchar(75) default NULL,
  `table4` varchar(25) default NULL,
  `table4criteria` varchar(75) default NULL,
  `table5` VARCHAR(25) ,
  `table5criteria` VARCHAR(75) ,
  `table6` VARCHAR(25),
  `table6criteria` VARCHAR(75),
  PRIMARY KEY  (`id`),
  KEY `name` (`reportname`,`groupname`)
)");

CreateTable('reportfields',
"CREATE TABLE `reportfields` (
  `id` int(8) NOT NULL auto_increment,
  `reportid` int(5) NOT NULL default '0',
  `entrytype` varchar(15) NOT NULL default '',
  `seqnum` int(3) NOT NULL default '0',
  `fieldname` varchar(35) NOT NULL default '',
  `displaydesc` varchar(25) NOT NULL default '',
  `visible` enum('1','0') NOT NULL default '1',
  `columnbreak` enum('1','0') NOT NULL default '1',
  `params` text,
  PRIMARY KEY  (`id`),
  KEY `reportid` (`reportid`)
)");

CreateTable('reportlinks',
"CREATE TABLE IF NOT EXISTS `reportlinks` (
  `table1` varchar(25) NOT NULL default '',
  `table2` varchar(25) NOT NULL default '',
  `equation` varchar(75) NOT NULL default ''
)");

/* Need to find a way of making this rdbms agnostic
 * but for the time being do it as mysql only
 */

if ($DBType=='mysql' or $DBType=='mysqli') {
	$SQL = "INSERT INTO reportlinks SELECT table_name, referenced_table_name, concat(table_name, '.', column_name, '=' , referenced_table_name, '.', referenced_column_name) FROM information_schema.key_column_usage WHERE referenced_table_name is not null and table_schema = '" . $_SESSION['DatabaseName'] . "'";
	executeSQL($SQL);
}

NewConfigValue('WikiApp','Disabled');
NewConfigValue('WikiPath','wiki');
NewConfigValue('ProhibitJournalsToControlAccounts','0');
NewConfigValue('InvoicePortraitFormat', '0');
NewConfigValue('ProhibitPostingsBefore', '2006-01-01');
NewConfigValue('WeightedAverageCosting', '1');
NewConfigValue('AllowOrderLineItemNarrative', '1');
NewConfigValue('vtiger_integration', '0');
NewConfigValue('DB_Maintenance', '-1');
NewConfigValue('HTTPS_Only', '0');

AddIndex(array('serialno'), 'stockserialitems', 'serialno');
AddIndex(array('serialno'), 'stockserialmoves', 'serialno');

InsertRecord('taxcategories', array('taxcatname'), array('Freight'), array('taxcatname'), array('Freight'));

DropIndex('custbranch', 'BranchCode');

AddColumn('stdcostunit', 'grns', 'double', 'NOT NULL', '0', 'supplierid');
DropConstraint('stockcheckfreeze', 'stockcheckfreeze_ibfk_1');
DropPrimaryKey('stockcheckfreeze', array('stockid'));
AddPrimaryKey('stockcheckfreeze', array('stockid', 'loccode'));
AddConstraint('stockcheckfreeze', 'stockcheckfreeze_ibfk_1', 'stockid', 'stockmaster', 'stockid');

UpdateDBNo(basename(__FILE__, '.php'));

?>