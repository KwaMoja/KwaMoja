<?php

NewScript('QATests.php', '16');
NewScript('ProductSpecs.php', '16');
NewScript('TestPlanResults.php', '16');
NewScript('PDFProdSpec.php', '0');
NewScript('PDFCOA.php', '0');
NewScript('PDFTestPlan.php', '16');
NewScript('SelectQASamples.php', '16');
NewScript('HistoricalTestResults.php', '16');

NewSecurityToken(16, _('Quality Assurance Manager'));

NewConfigValue('QualityProdSpecText', 'Disclaimer: No information supplied by our company constitutes a warranty regarding product performance or use. Any information regarding
performance or use is only offered as suggestion for investigation for use, based upon our company or other customer experience. our company
makes no warranties, expressed or implied, concerning the suitability or fitness of any of its products for any particular purpose. It is the
responsibility of the customer to determine that the product is safe, lawful and technically suitable for the intended use. The disclosure of
information herein is not a license to operate under, or a recommendation to infringe any patents.');
NewConfigValue('QualityCOAText', 'Disclaimer: No information supplied by our company constitutes a warranty regarding product performance or use. Any information regarding
performance or use is only offered as suggestion for investigation for use, based upon our company or other customer experience. our company
makes no warranties, expressed or implied, concerning the suitability or fitness of any of its products for any particular purpose. It is the
responsibility of the customer to determine that the product is safe, lawful and technically suitable for the intended use. The disclosure of
information herein is not a license to operate under, or a recommendation to infringe any patents.');
NewConfigValue('QualityLogSamples', '1');

CreateTable('qatests',
"CREATE TABLE IF NOT EXISTS `qatests` (
  `testid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `method` varchar(20),
  `groupby` varchar(20),
  `units` varchar(20) NOT NULL,
  `type` varchar(15) NOT NULL,
  `defaultvalue` varchar(150) NOT NULL DEFAULT '''''',
  `numericvalue` tinyint(4) NOT NULL DEFAULT '0',
  `showoncert` int(11) NOT NULL DEFAULT '1',
  `showonspec` int(11) NOT NULL DEFAULT '1',
  `showontestplan` tinyint(4) NOT NULL DEFAULT '1',
  `active` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`testid`),
  KEY `name` (`name`),
  KEY `groupname` (`groupby`,`name`)
)");

CreateTable('prodspecs',
"CREATE TABLE IF NOT EXISTS `prodspecs` (
	`keyval` varchar(25) NOT NULL,
	`testid` int(11) NOT NULL,
	`defaultvalue` varchar(150) NOT NULL DEFAULT '',
	`targetvalue` varchar(30) NOT NULL DEFAULT '',
    `rangemin` float NOT NULL DEFAULT '0',
    `rangemax` float NOT NULL DEFAULT '0',
	`showoncert` tinyint(11) NOT NULL DEFAULT '1',
	`showonspec` tinyint(4) NOT NULL DEFAULT '1',
	`showontestplan` tinyint(4) NOT NULL DEFAULT '1',
	`active` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`keyval`,`testid`),
	KEY `testid` (`testid`),
	CONSTRAINT `prodspecs_ibfk_1` FOREIGN KEY (`testid`) REFERENCES `qatests` (`testid`)
)");

CreateTable('qasamples',
"CREATE TABLE IF NOT EXISTS `qasamples` (
  `sampleid` int(11) NOT NULL AUTO_INCREMENT,
  `prodspeckey` varchar(25) NOT NULL DEFAULT '',
  `lotkey` varchar(25) NOT NULL DEFAULT '',
  `identifier` varchar(10) NOT NULL DEFAULT '',
  `createdby` varchar(15) NOT NULL DEFAULT '',
  `sampledate` date NOT NULL DEFAULT '0000-00-00',
 `comments` varchar(255) NOT NULL DEFAULT '',
  `cert` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sampleid`),
  KEY `prodspeckey` (`prodspeckey`,`lotkey`),
  CONSTRAINT `qasamples_ibfk_1` FOREIGN KEY (`prodspeckey`) REFERENCES `prodspecs` (`keyval`)
)");

CreateTable('sampleresults',
"CREATE TABLE IF NOT EXISTS `sampleresults` (
	`resultid` bigint(20) NOT NULL AUTO_INCREMENT,
	`sampleid` int(11) NOT NULL,
	`testid` int(11) NOT NULL,
	`defaultvalue` varchar(150) NOT NULL,
	`targetvalue` varchar(30) NOT NULL,
	`rangemin` float NOT NULL DEFAULT '0',
	`rangemax` float NOT NULL DEFAULT '0',
	`testvalue` varchar(30) NOT NULL DEFAULT '',
	`testdate` date NOT NULL DEFAULT '0000-00-00',
	`testedby` varchar(15) NOT NULL DEFAULT '',
	`comments` varchar(255) NOT NULL DEFAULT '',
	`isinspec` tinyint(4) NOT NULL DEFAULT '0',
	`showoncert` tinyint(4) NOT NULL DEFAULT '1',
	`showontestplan` tinyint(4) NOT NULL DEFAULT '1',
	`manuallyadded` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`resultid`),
  KEY `sampleid` (`sampleid`),
  KEY `testid` (`testid`),
  CONSTRAINT `sampleresults_ibfk_1` FOREIGN KEY (`testid`) REFERENCES `qatests` (`testid`)
)");

DropConstraint('pickreqdetails', 'pickreqdetails_ibfk_2');
AddConstraint('pickreqdetails', 'pickreqdetails_ibfk_2', 'prid', 'pickreq', 'prid');

DropConstraint('pickserialdetails', 'pickserialdetails_ibfk_1');
AddConstraint('pickserialdetails', 'pickserialdetails_ibfk_1', 'detailno', 'pickreqdetails', 'detailno');

NewModule('qa', 'qa', _('Quality Assurance'), 8);

NewMenuItem('qa', 'Transactions', _('QA Samples and Test Results'), '/SelectQASamples.php', 1);
NewMenuItem('qa', 'Reports', _('Print Product Specification'), '/PDFProdSpec.php', 1);
NewMenuItem('qa', 'Reports', _('Print Certificate of Analysis'), '/PDFCOA.php', 2);
NewMenuItem('qa', 'Reports', _('Historical QA Test Results'), '/HistoricalTestResults.php', 3);
NewMenuItem('qa', 'Maintenance', _('Quality Tests Maintenance'), '/QATests.php', 1);
NewMenuItem('qa', 'Maintenance', _('Product Specifications'), '/ProductSpecs.php', 2);

UpdateDBNo(basename(__FILE__, '.php'));

?>