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
)");

NewConfigValue('ShopName', '');
NewConfigValue('ShopContactUs', '');
NewConfigValue('ShopPrivacyStatement', '');
NewConfigValue('ShopFreightPolicy', '');
NewConfigValue('ShopTermsConditions', '');
NewConfigValue('ShopDebtorNo', '');
NewConfigValue('ShopBranchCode', '');
NewConfigValue('ShopAboutUs', '');
NewConfigValue('ShopMode', '');
NewConfigValue('ShopPayPalUser', '');
NewConfigValue('ShopPayPalPassword', '');
NewConfigValue('ShopPayPalSignature', '');
NewConfigValue('ShopPayPalProUser', '');
NewConfigValue('ShopPayPalProPassword', '');
NewConfigValue('ShopPayPalProSignature', '');
NewConfigValue('ShopCreditCardGateway', 'PayFlowPro');
NewConfigValue('ShopPayFlowUser', '');
NewConfigValue('ShopPayFlowPassword', '');
NewConfigValue('ShopPayFlowVendor', '');
NewConfigValue('ShopPayFlowMerchant', '');
NewConfigValue('ShopAllowPayPal', '1');
NewConfigValue('ShopAllowCreditCards', '1');
NewConfigValue('ShopAllowBankTransfer', '1');
NewConfigValue('ShopAllowSurcharges', '1');
NewConfigValue('ShopPayPalSurcharge', '0.034');
NewConfigValue('ShopBankTransferSurcharge', '0.0');
NewConfigValue('ShopCreditCardSurcharge', '0.029');
NewConfigValue('ShopPayPalBankAccount', '1030');
NewConfigValue('ShopCreditCardBankAccount', '1030');
NewConfigValue('ShopSwipeHQMerchantID', '');
NewConfigValue('ShopSwipeHQAPIKey', '');
NewConfigValue('ShopSurchargeStockID', '');
NewConfigValue('ItemDescriptionLanguages', '');
NewConfigValue('SmtpSetting', '');

CreateTable('stockdescriptiontranslations',
"CREATE TABLE IF NOT EXISTS `stockdescriptiontranslations` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `language_id` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `descriptiontranslation` varchar(50) NOT NULL,
  PRIMARY KEY (`stockid`,`language_id`)
)");

AddColumn('language_id', 'debtorsmaster', 'VARCHAR( 10 )', 'NOT NULL', 'en_GB.utf8', 'typeid');
AddColumn('salesperson', 'debtortrans', 'VARCHAR( 4 )', 'NOT NULL', 'DE', 'packages');

AddIndex(array('salesperson'), 'debtortrans', 'salesperson');

CreateTable('manufacturers',
"CREATE TABLE IF NOT EXISTS `manufacturers` (
  `manufacturers_id` int(11) NOT NULL AUTO_INCREMENT,
  `manufacturers_name` varchar(32) NOT NULL,
  `manufacturers_url` varchar(50) NOT NULL DEFAULT '',
  `manufacturers_image` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`manufacturers_id`),
  KEY (`manufacturers_name`)
)");

CreateTable('salescattranslations',
"CREATE TABLE IF NOT EXISTS `salescattranslations` (
  `salescatid` tinyint(4) NOT NULL DEFAULT '0',
  `language_id` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `salescattranslation` varchar(40) NOT NULL,
  PRIMARY KEY (`salescatid`,`language_id`)
)");

AddColumn('manufacturers_id', 'salescatprod', 'INT( 11 )', 'NOT NULL', '0', 'stockid');
AddColumn('featured', 'salescatprod', 'INT( 11 )', 'NOT NULL', '0', 'manufacturers_id');

AddIndex(array('manufacturers_id'), 'salescatprod', 'manufacturers_id');

UpdateDBNo(basename(__FILE__, '.php'));

?>