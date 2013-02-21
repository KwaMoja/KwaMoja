<?php

NewConfigValue('ExchangeRateFeed','ECB', $db);

AddColumn('salesperson', 'salesorders', 'VARCHAR(4)', 'NOT NULL', '', 'poplaced', $db);
ChangeColumnType('salesmancode', 'salesman', 'VARCHAR(4)', 'NOT NULL', '', $db);
DropColumn('commissionrate', 'salesorderdetails', $db);
DropColumn('commissionearned', 'salesorderdetails', $db);

NewScript('CounterReturns.php','5',$db);

ChangeColumnType('initiator', 'purchorders', 'VARCHAR(20)', 'NOT NULL', '', $db);

CreateTable('jobcards',
"CREATE TABLE `jobcards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `debtorno` varchar(255) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `task1` varchar(500) DEFAULT NULL,
  `task2` varchar(500) DEFAULT NULL,
  `task3` varchar(500) DEFAULT NULL,
  `task4` varchar(500) DEFAULT NULL,
  `task5` varchar(500) DEFAULT NULL,
  `task6` varchar(500) DEFAULT NULL,
  `createdate` date DEFAULT NULL,
  `completedate` date DEFAULT NULL,
  `invoice` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
)",
$db);

InsertRecord('securitytokens', array('tokenid', 'tokenname'), array('1000', 'User can view and alter sales prices'), array('tokenid', 'tokenname'), array('1000', 'User can view and alter sales prices'), $db);
InsertRecord('securitytokens', array('tokenid', 'tokenname'), array('1001', 'User can bypass purchasing security and go straight from order to invoice'), array('tokenid', 'tokenname'), array('1001', 'User can bypass purchasing security and go straight from order to invoice'), $db);
UpdateField('securitytokens', 'tokenname', 'Unknown', 'tokenid=12', $db);

AddColumn('fontsize', 'www_users', 'TINYINT(2)', 'NOT NULL', '0', 'department', $db);

NewScript('CustomerPurchases.php','5', $db);
NewScript('GoodsReceivedButNotInvoiced.php','15', $db);
NewScript('ItemsWithoutPicture.php','15', $db);

UpdateField('scripts', 'pagesecurity', '2', "script='GoodsReceivedButNotInvoiced.php'", $db);

DeleteRecords('scripts', "script='Z_ItemsWithoutPicture.php'", $db);
UpdateField('scripts', 'script', 'Z_ItemsWithoutPicture.php', "script='ItemsWithoutPicture.php'", $db);

ChangeColumnType('description', 'taxauthorities', 'VARCHAR(40)', 'NOT NULL', '', $db);

NewScript('MaterialsNotUsed.php', '4', $db);

ChangeConfigValue('VersionNumber', '4.10', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>