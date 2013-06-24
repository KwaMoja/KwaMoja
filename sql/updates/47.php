<?php

CreateTable('fixedassettasks',
"CREATE TABLE IF NOT EXISTS `fixedassettasks` (
  `taskid` int(11) NOT NULL AUTO_INCREMENT,
  `assetid` int(11) NOT NULL,
  `taskdescription` text NOT NULL,
  `frequencydays` int(11) NOT NULL DEFAULT '365',
  `lastcompleted` date NOT NULL,
  `userresponsible` varchar(20) NOT NULL,
  `manager` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`taskid`),
  KEY `assetid` (`assetid`),
  KEY `userresponsible` (`userresponsible`)
)", $db);

NewConfigValue('ShopName', '', $db);
NewConfigValue('ShopContactUs', '', $db);
NewConfigValue('ShopPrivacyStatement', '', $db);
NewConfigValue('ShopFreightPolicy', '', $db);
NewConfigValue('ShopTermsConditions', '', $db);
NewConfigValue('ShopDebtorNo', '', $db);
NewConfigValue('ShopBranchCode', '', $db);
NewConfigValue('ShopAboutUs', '', $db);
NewConfigValue('ShopMode', '', $db);
NewConfigValue('ShopPayPalUser', '', $db);
NewConfigValue('ShopPayPalPassword', '', $db);
NewConfigValue('ShopPayPalSignature', '', $db);
NewConfigValue('ShopPayPalProUser', '', $db);
NewConfigValue('ShopPayPalProPassword', '', $db);
NewConfigValue('ShopPayPalProSignature', '', $db);
NewConfigValue('ShopCreditCardGateway', 'PayFlowPro', $db);
NewConfigValue('ShopPayFlowUser', '', $db);
NewConfigValue('ShopPayFlowPassword', '', $db);
NewConfigValue('ShopPayFlowVendor', '', $db);
NewConfigValue('ShopPayFlowMerchant', '', $db);
NewConfigValue('ShopAllowPayPal', '1', $db);
NewConfigValue('ShopAllowCreditCards', '1', $db);
NewConfigValue('ShopAllowBankTransfer', '1', $db);
NewConfigValue('ShopAllowSurcharges', '1', $db);
NewConfigValue('ShopPayPalSurcharge', '0.034', $db);
NewConfigValue('ShopBankTransferSurcharge', '0.0', $db);
NewConfigValue('ShopCreditCardSurcharge', '0.029', $db);
NewConfigValue('ShopPayPalBankAccount', '1030', $db);
NewConfigValue('ShopCreditCardBankAccount', '1030', $db);
NewConfigValue('ShopSwipeHQMerchantID', '', $db);
NewConfigValue('ShopSwipeHQAPIKey', '', $db);
NewConfigValue('ShopSurchargeStockID', '', $db);
NewConfigValue('ItemDescriptionLanguages', '', $db);
NewConfigValue('SmtpSetting', '', $db);

CreateTable('stockdescriptiontranslations',
"CREATE TABLE IF NOT EXISTS `stockdescriptiontranslations` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `language_id` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `descriptiontranslation` varchar(50) NOT NULL,
  PRIMARY KEY (`stockid`,`language_id`)
)"
,$db);

AddColumn('language_id', 'debtorsmaster', 'VARCHAR( 10 )', 'NOT NULL', 'en_GB.utf8', 'typeid', $db);
AddColumn('salesperson', 'debtortrans', 'VARCHAR( 4 )', 'NOT NULL', 'en_GB.utf8', 'packages', $db);

AddIndex(array('salesperson'), 'debtortrans', 'salesperson', $db);

CreateTable('manufacturers',
"CREATE TABLE IF NOT EXISTS `manufacturers` (
  `manufacturers_id` int(11) NOT NULL AUTO_INCREMENT,
  `manufacturers_name` varchar(32) NOT NULL,
  `manufacturers_url` varchar(50) NOT NULL DEFAULT '',
  `manufacturers_image` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`manufacturers_id`),
  KEY (`manufacturers_name`)
)",
$db);

CreateTable('salescattranslations',
"CREATE TABLE IF NOT EXISTS `salescattranslations` (
  `salescatid` tinyint(4) NOT NULL DEFAULT '0',
  `language_id` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `salescattranslation` varchar(40) NOT NULL,
  PRIMARY KEY (`salescatid`,`language_id`)
)",
$db);

AddColumn('manufacturers_id', 'salescatprod', 'INT( 11 )', 'NOT NULL', '0', 'stockid', $db);
AddColumn('featured', 'salescatprod', 'INT( 11 )', 'NOT NULL', '0', 'manufacturers_id', $db);

AddIndex(array('manufacturers_id'), 'salescatprod', 'manufacturers_id', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>