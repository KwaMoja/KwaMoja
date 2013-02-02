CREATE TABLE IF NOT EXISTS `supplierdiscounts` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `sellthroughsupport` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO scripts VALUES ('SellThroughSupport.php',  '9',  'Defines the items, period and quantum of support for which supplier has agreed to provide.');
INSERT INTO scripts VALUES ('PDFSellThroughSupportClaim.php',  '9',  'Reports the sell through support claims to be made against all suppliers for a given date range.');
INSERT INTO scripts VALUES ('ReportBug.php',  '15',  'Report an issue directly to the KwaMoja bugs page');

INSERT INTO `labels` VALUES (NULL,'DPS01 *',210,297,210,297,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'DPS01 *',210,297,210,297,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'DPS01 *',210,297,210,297,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'DPS02 *',210,297,210,149,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'DPS08 *',210,297,105,71,7,7,0,0);
INSERT INTO `labels` VALUES (NULL,'DPS10 *',210,297,105,59.6,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'DPS16 *',210,297,105,37,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'DPS24 *',210,297,70,36,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'DPS30 *',210,297,70,30,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'DPS04 *',210,297,105,149,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'J5101 *',210,297,38,69,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'J5102 *',210,297,63.5,38,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'J5103 *',210,297,38,135,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L4730 *',210,297,17.8,10,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L4743 *',210,297,99.1,42.3,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L6008 *',210,297,25.4,10,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L6009 *',210,297,45.7,21.2,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L6011 *',210,297,63.5,29.6,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L6012 *',210,297,96,50.8,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7102 *',210,297,192,39,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7159 *',210,297,63.5,33.9,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7160 *',210,297,63.5,38.1,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7161 *',210,297,63.5,46.6,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7162 *',210,297,99.1,34,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7163 *',210,297,99.1,38.1,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7164',210,297,63.5,72,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7165 *',210,297,99.1,67.7,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7166',210,297,99.1,93.1,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7167 *',210,297,199.6,289.1,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7168 *',210,297,199.6,143.5,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7169 *',210,297,99.1,139,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7170 *',210,297,134,11,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7171 *',210,297,200,60,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7172 *',210,297,100,30,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7173 *',210,297,99.1,57,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7409 *',210,297,57,15,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7644 *',210,297,133,29.6,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7651 *',210,297,38.1,21.2,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7654 *',210,297,45.7,25.4,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7664 *',210,297,71,70,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7665 *',210,297,72,21.15,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7666 *',210,297,70,52,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7668 *',210,297,59,51,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7670 *',210,297,65,65,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7671 *',210,297,76.2,46.4,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7674 *',210,297,145,17,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'L7701 *',210,297,192,62,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'EL1S',210,297,210,287,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'VSL3B',210,297,191,99.48,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'EL3 LL03NSE',210,297,210,99.48,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'SLSQ95',210,297,95,95,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'EL6 LL06NSE',210,297,105,99.48,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'VSL6',210,297,70,149,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'EL8 LL08NSE',210,297,105,74.2,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'EL8SB',210,297,72,99,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'EL10S',210,297,105,57,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'EL12S',210,297,105,48,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'EL14 LL14NSE',210,297,105,42.5,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'EL15S',210,297,70,50,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'SLSQ51',210,297,51,51,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'EL15',210,297,70,59.6,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'EL16S LL16SE',210,297,105,35,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'EL21S LL21SE',210,297,70,38,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'EL21',210,297,70,42.5,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'EL24LS',210,297,70,34,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'EL24S LL24SE',210,297,70,35,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'EL24 LL24NSE',210,297,70,37,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'EL27S',210,297,70,32,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'VSL33D',210,297,53,21,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'EL33S',210,297,70,25.4,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'SLSQ37',210,297,37,37,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'VSL36SB',210,297,90,12,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'LL36',210,297,48.9,29.6,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'VSL56SB',210,297,89,10,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'EL56',210,297,52.5,21.3,0,0,0,0);
INSERT INTO `labels` VALUES (NULL,'SLSQ25',210,297,25,25,0,0,0,0);
UPDATE labels SET rowheight=height;
UPDATE labels SET columnwidth=width;

UPDATE config SET confvalue='4.10.1' WHERE confname='VersionNumber';
