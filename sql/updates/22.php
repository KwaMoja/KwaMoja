<?php

NewConfigValue('ExchangeRateFeed','ECB');

AddColumn('salesperson', 'salesorders', 'VARCHAR(4)', 'NOT NULL', '', 'poplaced');
ChangeColumnType('salesmancode', 'salesman', 'VARCHAR(4)', 'NOT NULL', '');
DropColumn('commissionrate', 'salesorderdetails');
DropColumn('commissionearned', 'salesorderdetails');

NewScript('CounterReturns.php','5');

ChangeColumnType('initiator', 'purchorders', 'VARCHAR(20)', 'NOT NULL', '');

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
)");

InsertRecord('securitytokens', array('tokenid', 'tokenname'), array('1000', 'User can view and alter sales prices'), array('tokenid', 'tokenname'), array('1000', 'User can view and alter sales prices'));
InsertRecord('securitytokens', array('tokenid', 'tokenname'), array('1001', 'User can bypass purchasing security and go straight from order to invoice'), array('tokenid', 'tokenname'), array('1001', 'User can bypass purchasing security and go straight from order to invoice'));
UpdateField('securitytokens', 'tokenname', 'Unknown', 'tokenid=12');

AddColumn('fontsize', 'www_users', 'TINYINT(2)', 'NOT NULL', '0', 'department');

NewScript('CustomerPurchases.php','5');
NewScript('GoodsReceivedButNotInvoiced.php','15');
NewScript('ItemsWithoutPicture.php','15');

UpdateField('scripts', 'pagesecurity', '2', "script='GoodsReceivedButNotInvoiced.php'");

DeleteRecords('scripts', "script='Z_ItemsWithoutPicture.php'");
UpdateField('scripts', 'script', 'Z_ItemsWithoutPicture.php', "script='ItemsWithoutPicture.php'");

ChangeColumnType('description', 'taxauthorities', 'VARCHAR(40)', 'NOT NULL', '');

NewScript('MaterialsNotUsed.php', '4');

ChangeConfigValue('VersionNumber', '4.10');

UpdateDBNo(basename(__FILE__, '.php'));

?>