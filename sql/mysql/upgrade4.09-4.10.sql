INSERT INTO config VALUES('ExchangeRateFeed','ECB');
ALTER TABLE `salesorders` ADD `salesperson` VARCHAR( 4 ) NOT NULL , ADD INDEX ( `salesperson` );
ALTER TABLE `salesman` CHANGE `salesmancode` `salesmancode` VARCHAR( 4 ) NOT NULL DEFAULT '';
ALTER TABLE `salesorderdetails` DROP `commissionrate`;
ALTER TABLE `salesorderdetails` DROP `commissionearned`;
INSERT INTO scripts VALUES ('CounterReturns.php','5','Allows credits and refunds from the default Counter Sale account for an inventory location');
UPDATE config SET confvalue='4.09.1' WHERE confname='VersionNumber';

ALTER TABLE purchorders MODIFY `initiator` VARCHAR(20);

CREATE TABLE `jobcards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `debtorno` varchar(255) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `task1` varchar(500) DEFAULT NULL,
  `task2` varchar(500) DEFAULT NULL,
  `task3` varchar(500) DEFAULT NULL,
  `task4` varchar(500) DEFAULT NULL,
  `task5` varchar(500) DEFAULT NULL,
  `task6` varchar(500) DEFAULT NULL,
  `CreateDate` date DEFAULT NULL,
  `CompleteDate` date DEFAULT NULL,
  `Invoice` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

ALTER TABLE `jobcards` CHANGE `CreateDate` `createdate` date DEFAULT NULL;
ALTER TABLE `jobcards` CHANGE `CompleteDate` `completedate` date DEFAULT NULL;
ALTER TABLE `jobcards` CHANGE `Invoice` `invoice` varchar(255) DEFAULT NULL;

INSERT INTO `securitytokens` VALUES(1000, 'User can view and alter sales prices');
INSERT INTO `securitytokens` VALUES(1001, 'User can bypass purchasing security and go straight from order to invoice');
UPDATE `securitytokens` SET tokenname='Unknown' WHERE tokenid=12;
ALTER TABLE `www_users` ADD `fontsize` TINYINT( 2 ) NOT NULL DEFAULT 0;
INSERT INTO scripts VALUES ('CustomerPurchases.php','5','Shows the purchases a customer has made.');
INSERT INTO scripts VALUES ('GoodsReceivedButNotInvoiced.php','15','Shows the list of Goods Received Not Yet Invoiced, both in supplier currency and home currency. Total in home curency should match the GL Account for Goods received not invoiced. Any discrepancy is due to multicurrency errors.');
INSERT INTO scripts VALUES ('ItemsWithoutPicture.php','15','Shows the list of curent items without picture in webERP');

UPDATE scripts SET pagesecurity='2' WHERE script='GoodsReceivedButNotInvoiced.php';
UPDATE scripts SET script='Z_ItemsWithoutPicture.php' WHERE script='ItemsWithoutPicture.php';
