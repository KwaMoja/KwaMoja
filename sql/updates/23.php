<?php

CreateTable('supplierdiscounts',
"CREATE TABLE IF NOT EXISTS `supplierdiscounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplierno` varchar(10) NOT NULL,
  `stockid` varchar(20) NOT NULL,
  `discountnarrative` varchar(20) NOT NULL,
  `discountpercent` double NOT NULL,
  `discountamount` double NOT NULL,
  `effectivefrom` date NOT NULL,
  `effectiveto` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `supplierno` (`supplierno`),
  KEY `effectivefrom` (`effectivefrom`),
  KEY `effectiveto` (`effectiveto`),
  KEY `stockid` (`stockid`)
)",
$db);

CreateTable('sellthroughsupport',
"CREATE TABLE IF NOT EXISTS `sellthroughsupport` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplierno` varchar(10) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `categoryid` char(6) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `narrative` varchar(20) NOT NULL DEFAULT '',
  `rebatepercent` double NOT NULL DEFAULT '0',
  `rebateamount` double NOT NULL DEFAULT '0',
  `effectivefrom` date NOT NULL,
  `effectiveto` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `supplierno` (`supplierno`),
  KEY `debtorno` (`debtorno`),
  KEY `effectivefrom` (`effectivefrom`),
  KEY `effectiveto` (`effectiveto`),
  KEY `stockid` (`stockid`),
  KEY `categoryid` (`categoryid`)
)",
$db);

NewScript('SellThroughSupport.php',  '9', $db);
NewScript('PDFSellThroughSupportClaim.php',  '9', $db);
NewScript('ReportBug.php',  '15', $db);
NewScript('UploadPriceList.php',  '15', $db);

AddColumn('bin', 'locstock', 'VARCHAR(10)', 'NOT NULL', '', 'reorderlevel', $db);

ChangeConfigValue('VersionNumber', '4.10.1', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>