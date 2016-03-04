<?php

NewScript('Donors.php', '15');
NewScript('Projects.php', '15');
NewScript('ProjectBOM.php', '15');
NewScript('ProjectOtherReqts.php', '15');
NewScript('SelectProject.php', '15');

CreateTable('donors',
"CREATE TABLE `donors` (
  `donorno` varchar(10) NOT NULL DEFAULT '',
  `name` varchar(40) NOT NULL DEFAULT '',
  `address1` varchar(40) NOT NULL DEFAULT '',
  `address2` varchar(40) NOT NULL DEFAULT '',
  `address3` varchar(40) NOT NULL DEFAULT '',
  `address4` varchar(50) NOT NULL DEFAULT '',
  `address5` varchar(20) NOT NULL DEFAULT '',
  `address6` varchar(40) NOT NULL,
  `currcode` char(3) NOT NULL DEFAULT '',
  `language_id` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  PRIMARY KEY (`donorno`),
  KEY `Currency` (`currcode`),
  CONSTRAINT `donors_ibfk_1` FOREIGN KEY (`currcode`) REFERENCES `currencies` (`currabrev`)
)");

CreateTable('projects',
"CREATE TABLE `projects` (
  `projectref` varchar(20) NOT NULL DEFAULT '',
  `projectdescription` text NOT NULL,
  `donorno` varchar(10) NOT NULL DEFAULT '',
  `budgetno` int(11) NOT NULL,
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `categoryid` varchar(6) NOT NULL DEFAULT '',
  `customerref` varchar(20) NOT NULL DEFAULT '',
  `margin` double NOT NULL DEFAULT '1',
  `wo` int(11) NOT NULL DEFAULT '0',
  `requireddate` date NOT NULL DEFAULT '0000-00-00',
  `completiondate` date NOT NULL DEFAULT '0000-00-00',
  `drawing` varchar(50) NOT NULL DEFAULT '',
  `exrate` double NOT NULL DEFAULT '1',
  PRIMARY KEY (`projectref`),
  KEY `CategoryID` (`categoryid`),
  KEY `Status` (`status`),
  KEY `WO` (`wo`),
  KEY `loccode` (`loccode`),
  KEY `DonorNo` (`donorno`),
  CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`donorno`) REFERENCES `donors` (`donorno`),
  CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`categoryid`),
  CONSTRAINT `projects_ibfk_3` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
)");

CreateTable('projectbom',
"CREATE TABLE `projectbom` (
  `projectref` varchar(20) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `workcentreadded` char(5) NOT NULL DEFAULT '',
  `requiredby` date NOT NULL DEFAULT '0000-00-00',
  `quantity` double NOT NULL DEFAULT '1',
  `issued` double NOT NULL DEFAULT '0',
  `authorised` smallint(1)  NOT NULL DEFAULT 0,
  PRIMARY KEY (`projectref`,`stockid`,`workcentreadded`),
  KEY `Stockid` (`stockid`),
  KEY `ProjectRef` (`projectref`),
  KEY `WorkCentreAdded` (`workcentreadded`),
  CONSTRAINT `projectbom_ibfk_1` FOREIGN KEY (`workcentreadded`) REFERENCES `workcentres` (`code`),
  CONSTRAINT `projectbom_ibfk_3` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
)");

CreateTable('projectcharges',
"CREATE TABLE `projectcharges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectref` varchar(20) NOT NULL,
  `transtype` smallint(6) NOT NULL DEFAULT '20',
  `transno` int(11) NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0',
  `narrative` text NOT NULL,
  `anticipated` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `contractref` (`projectref`,`transtype`,`transno`),
  KEY `projectcharges_ibk_1` (`transtype`),
  CONSTRAINT `projectcharges_ibfk_1` FOREIGN KEY (`projectref`) REFERENCES `projects` (`projectref`),
  CONSTRAINT `projectcharges_ibfk_2` FOREIGN KEY (`transtype`) REFERENCES `systypes` (`typeid`)
)");

CreateTable('projectreqts',
"CREATE TABLE `projectreqts` (
  `projectreqid` int(11) NOT NULL AUTO_INCREMENT,
  `projectref` varchar(20) NOT NULL DEFAULT '0',
  `requirement` varchar(40) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '1',
  `costperunit` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`projectreqid`),
  KEY `ProjectRef` (`projectref`),
  CONSTRAINT `projectreqts_ibfk_1` FOREIGN KEY (`projectref`) REFERENCES `projects` (`projectref`)
)");

CreateTable('projectbudgets',
"CREATE TABLE `projectbudgets` (
  `orderno` int(11) NOT NULL,
  `donorno` varchar(10) NOT NULL DEFAULT '',
  `customerref` varchar(50) NOT NULL DEFAULT '',
  `buyername` varchar(50) DEFAULT NULL,
  `comments` longblob,
  `orddate` date NOT NULL DEFAULT '0000-00-00',
  `ordertype` char(2) NOT NULL DEFAULT '',
  `deladd1` varchar(40) NOT NULL DEFAULT '',
  `deladd2` varchar(40) NOT NULL,
  `deladd3` varchar(40) NOT NULL,
  `deladd4` varchar(40) NOT NULL,
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `contactphone` varchar(25) DEFAULT NULL,
  `contactemail` varchar(40) NOT NULL,
  `deliverto` varchar(40) NOT NULL DEFAULT '',
  `deliverblind` tinyint(1) DEFAULT '1',
  `freightcost` double NOT NULL DEFAULT '0',
  `fromstkloc` varchar(5) NOT NULL DEFAULT '',
  `deliverydate` date NOT NULL DEFAULT '0000-00-00',
  `confirmeddate` date NOT NULL DEFAULT '0000-00-00',
  `printedpackingslip` tinyint(4) NOT NULL DEFAULT '0',
  `datepackingslipprinted` date NOT NULL DEFAULT '0000-00-00',
  `quotation` tinyint(4) NOT NULL DEFAULT '0',
  `quotedate` date NOT NULL DEFAULT '0000-00-00',
  `poplaced` tinyint(4) NOT NULL DEFAULT '0',
  `internalcomment` blob,
  PRIMARY KEY (`orderno`),
  KEY `DonorNo` (`donorno`),
  KEY `OrdDate` (`orddate`),
  KEY `OrderType` (`ordertype`),
  KEY `LocationIndex` (`fromstkloc`),
  KEY `quotation` (`quotation`),
  KEY `poplaced` (`poplaced`)
)");

CreateTable('projectbudgetdetails',
"CREATE TABLE `projectbudgetdetails` (
  `orderlineno` int(11) NOT NULL DEFAULT '0',
  `orderno` int(11) NOT NULL DEFAULT '0',
  `stkcode` varchar(20) NOT NULL DEFAULT '',
  `qtyinvoiced` double NOT NULL DEFAULT '0',
  `unitprice` double NOT NULL DEFAULT '0',
  `quantity` double NOT NULL DEFAULT '0',
  `estimate` tinyint(4) NOT NULL DEFAULT '0',
  `discountpercent` double NOT NULL DEFAULT '0',
  `actualdispatchdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `narrative` text,
  `itemdue` date DEFAULT NULL,
  `poline` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`orderlineno`,`orderno`),
  KEY `OrderNo` (`orderno`),
  KEY `StkCode` (`stkcode`),
  KEY `Completed` (`completed`)
)");

NewModule('pjct', 'pjct', _('Project Accounting'), 9);

NewMenuItem('pjct', 'Maintenance', _('Donor Maintenance'), '/Donors.php', 1);
NewMenuItem('pjct', 'Transactions', _('Create New Project'), '/Projects.php', 1);
NewMenuItem('pjct', 'Transactions', _('Select a Project'), '/SelectProject.php', 2);

InsertRecord('systypes', array('typeid'), array('510'), array('typeid' ,'typename' ,'typeno'), array('510',  _('Auto Donor Number'),  '0'));

UpdateDBNo(basename(__FILE__, '.php'));

?>