CREATE DATABASE IF NOT EXISTS kwamoja;
USE kwamoja;
SET FOREIGN_KEY_CHECKS = 0;
-- MySQL dump 10.14  Distrib 10.0.0-MariaDB, for debian-linux-gnu (i686)
--
-- Host: localhost    Database: kwamoja
-- ------------------------------------------------------
-- Server version	10.0.0-MariaDB-mariadb1~precise-log
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `accountgroups`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accountgroups` (
  `groupname` char(30) NOT NULL DEFAULT '',
  `sectioninaccounts` int(11) NOT NULL DEFAULT '0',
  `pandl` tinyint(4) NOT NULL DEFAULT '1',
  `sequenceintb` smallint(6) NOT NULL DEFAULT '0',
  `parentgroupname` varchar(30) NOT NULL,
  PRIMARY KEY (`groupname`),
  KEY `SequenceInTB` (`sequenceintb`),
  KEY `sectioninaccounts` (`sectioninaccounts`),
  KEY `parentgroupname` (`parentgroupname`),
  CONSTRAINT `accountgroups_ibfk_1` FOREIGN KEY (`sectioninaccounts`) REFERENCES `accountsection` (`sectionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `accountsection`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accountsection` (
  `sectionid` int(11) NOT NULL DEFAULT '0',
  `sectionname` text NOT NULL,
  PRIMARY KEY (`sectionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `areas`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `areas` (
  `areacode` char(3) NOT NULL,
  `areadescription` varchar(25) NOT NULL DEFAULT '',
  PRIMARY KEY (`areacode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `assetmanager`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assetmanager` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `location` varchar(15) NOT NULL DEFAULT '',
  `cost` double NOT NULL DEFAULT '0',
  `depn` double NOT NULL DEFAULT '0',
  `datepurchased` date NOT NULL DEFAULT '0000-00-00',
  `disposalvalue` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `audittrail`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audittrail` (
  `transactiondate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `userid` varchar(20) NOT NULL DEFAULT '',
  `querystring` text,
  KEY `UserID` (`userid`),
  CONSTRAINT `audittrail_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `www_users` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bankaccounts`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bankaccounts` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `currcode` char(3) NOT NULL,
  `invoice` smallint(2) NOT NULL DEFAULT '0',
  `bankaccountcode` varchar(50) NOT NULL DEFAULT '',
  `bankaccountname` char(50) NOT NULL DEFAULT '',
  `bankaccountnumber` char(50) NOT NULL DEFAULT '',
  `bankaddress` char(50) DEFAULT NULL,
  PRIMARY KEY (`accountcode`),
  KEY `currcode` (`currcode`),
  KEY `BankAccountName` (`bankaccountname`),
  KEY `BankAccountNumber` (`bankaccountnumber`),
  CONSTRAINT `bankaccounts_ibfk_1` FOREIGN KEY (`accountcode`) REFERENCES `chartmaster` (`accountcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `banktrans`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banktrans` (
  `banktransid` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` smallint(6) NOT NULL DEFAULT '0',
  `transno` bigint(20) NOT NULL DEFAULT '0',
  `bankact` varchar(20) NOT NULL DEFAULT '0',
  `ref` varchar(50) NOT NULL DEFAULT '',
  `amountcleared` double NOT NULL DEFAULT '0',
  `exrate` double NOT NULL DEFAULT '1' COMMENT 'From bank account currency to payment currency',
  `functionalexrate` double NOT NULL DEFAULT '1' COMMENT 'Account currency to functional currency',
  `transdate` date NOT NULL DEFAULT '0000-00-00',
  `banktranstype` varchar(30) NOT NULL DEFAULT '',
  `amount` double NOT NULL DEFAULT '0',
  `currcode` char(3) NOT NULL DEFAULT '',
  PRIMARY KEY (`banktransid`),
  KEY `BankAct` (`bankact`,`ref`),
  KEY `TransDate` (`transdate`),
  KEY `TransType` (`banktranstype`),
  KEY `Type` (`type`,`transno`),
  KEY `CurrCode` (`currcode`),
  KEY `ref` (`ref`),
  KEY `ref_2` (`ref`),
  KEY `ref_3` (`ref`),
  KEY `ref_4` (`ref`),
  KEY `ref_5` (`ref`),
  KEY `ref_6` (`ref`),
  KEY `ref_7` (`ref`),
  KEY `ref_8` (`ref`),
  KEY `ref_9` (`ref`),
  KEY `ref_10` (`ref`),
  CONSTRAINT `banktrans_ibfk_1` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`),
  CONSTRAINT `banktrans_ibfk_2` FOREIGN KEY (`bankact`) REFERENCES `bankaccounts` (`accountcode`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bom`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bom` (
  `parent` char(20) NOT NULL DEFAULT '',
  `component` char(20) NOT NULL DEFAULT '',
  `workcentreadded` char(5) NOT NULL DEFAULT '',
  `loccode` char(5) NOT NULL DEFAULT '',
  `effectiveafter` date NOT NULL DEFAULT '0000-00-00',
  `effectiveto` date NOT NULL DEFAULT '9999-12-31',
  `quantity` double NOT NULL DEFAULT '1',
  `autoissue` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`parent`,`component`,`workcentreadded`,`loccode`),
  KEY `Component` (`component`),
  KEY `EffectiveAfter` (`effectiveafter`),
  KEY `EffectiveTo` (`effectiveto`),
  KEY `LocCode` (`loccode`),
  KEY `Parent` (`parent`,`effectiveafter`,`effectiveto`,`loccode`),
  KEY `Parent_2` (`parent`),
  KEY `WorkCentreAdded` (`workcentreadded`),
  CONSTRAINT `bom_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `bom_ibfk_2` FOREIGN KEY (`component`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `bom_ibfk_3` FOREIGN KEY (`workcentreadded`) REFERENCES `workcentres` (`code`),
  CONSTRAINT `bom_ibfk_4` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chartdetails`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chartdetails` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `period` smallint(6) NOT NULL DEFAULT '0',
  `budget` double NOT NULL DEFAULT '0',
  `actual` double NOT NULL DEFAULT '0',
  `bfwd` double NOT NULL DEFAULT '0',
  `bfwdbudget` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`accountcode`,`period`),
  KEY `Period` (`period`),
  CONSTRAINT `chartdetails_ibfk_1` FOREIGN KEY (`accountcode`) REFERENCES `chartmaster` (`accountcode`),
  CONSTRAINT `chartdetails_ibfk_2` FOREIGN KEY (`period`) REFERENCES `periods` (`periodno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chartmaster`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chartmaster` (
  `accountcode` varchar(20) NOT NULL DEFAULT '0',
  `accountname` char(50) NOT NULL DEFAULT '',
  `group_` char(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`accountcode`),
  KEY `AccountName` (`accountname`),
  KEY `Group_` (`group_`),
  CONSTRAINT `chartmaster_ibfk_1` FOREIGN KEY (`group_`) REFERENCES `accountgroups` (`groupname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cogsglpostings`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cogsglpostings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `area` char(3) NOT NULL DEFAULT '',
  `stkcat` varchar(6) NOT NULL DEFAULT '',
  `glcode` int(11) NOT NULL DEFAULT '0',
  `salestype` char(2) NOT NULL DEFAULT 'AN',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Area_StkCat` (`area`,`stkcat`,`salestype`),
  KEY `Area` (`area`),
  KEY `StkCat` (`stkcat`),
  KEY `GLCode` (`glcode`),
  KEY `SalesType` (`salestype`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `companies`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `companies` (
  `coycode` int(11) NOT NULL DEFAULT '1',
  `coyname` varchar(50) NOT NULL DEFAULT '',
  `gstno` varchar(20) NOT NULL DEFAULT '',
  `companynumber` varchar(20) NOT NULL DEFAULT '0',
  `regoffice1` varchar(40) NOT NULL DEFAULT '',
  `regoffice2` varchar(40) NOT NULL DEFAULT '',
  `regoffice3` varchar(40) NOT NULL DEFAULT '',
  `regoffice4` varchar(40) NOT NULL DEFAULT '',
  `regoffice5` varchar(20) NOT NULL DEFAULT '',
  `regoffice6` varchar(15) NOT NULL DEFAULT '',
  `telephone` varchar(25) NOT NULL DEFAULT '',
  `fax` varchar(25) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  `currencydefault` varchar(4) NOT NULL DEFAULT '',
  `debtorsact` int(11) NOT NULL DEFAULT '70000',
  `pytdiscountact` int(11) NOT NULL DEFAULT '55000',
  `creditorsact` int(11) NOT NULL DEFAULT '80000',
  `payrollact` int(11) NOT NULL DEFAULT '84000',
  `grnact` int(11) NOT NULL DEFAULT '72000',
  `exchangediffact` int(11) NOT NULL DEFAULT '65000',
  `purchasesexchangediffact` int(11) NOT NULL DEFAULT '0',
  `retainedearnings` int(11) NOT NULL DEFAULT '90000',
  `gllink_debtors` tinyint(1) DEFAULT '1',
  `gllink_creditors` tinyint(1) DEFAULT '1',
  `gllink_stock` tinyint(1) DEFAULT '1',
  `freightact` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`coycode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `config`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config` (
  `confname` varchar(35) NOT NULL DEFAULT '',
  `confvalue` text NOT NULL,
  PRIMARY KEY (`confname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contractbom`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contractbom` (
  `contractref` varchar(20) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `workcentreadded` char(5) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '1',
  PRIMARY KEY (`contractref`,`stockid`,`workcentreadded`),
  KEY `Stockid` (`stockid`),
  KEY `ContractRef` (`contractref`),
  KEY `WorkCentreAdded` (`workcentreadded`),
  CONSTRAINT `contractbom_ibfk_1` FOREIGN KEY (`workcentreadded`) REFERENCES `workcentres` (`code`),
  CONSTRAINT `contractbom_ibfk_3` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contractcharges`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contractcharges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contractref` varchar(20) NOT NULL,
  `transtype` smallint(6) NOT NULL DEFAULT '20',
  `transno` int(11) NOT NULL DEFAULT '0',
  `amount` double NOT NULL DEFAULT '0',
  `narrative` text NOT NULL,
  `anticipated` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `contractref` (`contractref`,`transtype`,`transno`),
  KEY `contractcharges_ibfk_2` (`transtype`),
  CONSTRAINT `contractcharges_ibfk_1` FOREIGN KEY (`contractref`) REFERENCES `contracts` (`contractref`),
  CONSTRAINT `contractcharges_ibfk_2` FOREIGN KEY (`transtype`) REFERENCES `systypes` (`typeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contractreqts`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contractreqts` (
  `contractreqid` int(11) NOT NULL AUTO_INCREMENT,
  `contractref` varchar(20) NOT NULL DEFAULT '0',
  `requirement` varchar(40) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '1',
  `costperunit` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`contractreqid`),
  KEY `ContractRef` (`contractref`),
  CONSTRAINT `contractreqts_ibfk_1` FOREIGN KEY (`contractref`) REFERENCES `contracts` (`contractref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contracts`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contracts` (
  `contractref` varchar(20) NOT NULL DEFAULT '',
  `contractdescription` text NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `categoryid` varchar(6) NOT NULL DEFAULT '',
  `orderno` int(11) NOT NULL DEFAULT '0',
  `customerref` varchar(20) NOT NULL DEFAULT '',
  `margin` double NOT NULL DEFAULT '1',
  `wo` int(11) NOT NULL DEFAULT '0',
  `requireddate` date NOT NULL DEFAULT '0000-00-00',
  `drawing` varchar(50) NOT NULL DEFAULT '',
  `exrate` double NOT NULL DEFAULT '1',
  PRIMARY KEY (`contractref`),
  KEY `OrderNo` (`orderno`),
  KEY `CategoryID` (`categoryid`),
  KEY `Status` (`status`),
  KEY `WO` (`wo`),
  KEY `loccode` (`loccode`),
  KEY `DebtorNo` (`debtorno`,`branchcode`),
  CONSTRAINT `contracts_ibfk_1` FOREIGN KEY (`debtorno`, `branchcode`) REFERENCES `custbranch` (`debtorno`, `branchcode`),
  CONSTRAINT `contracts_ibfk_2` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`categoryid`),
  CONSTRAINT `contracts_ibfk_3` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `currencies`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `currencies` (
  `currency` char(20) NOT NULL DEFAULT '',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `country` char(50) NOT NULL DEFAULT '',
  `hundredsname` char(15) NOT NULL DEFAULT 'Cents',
  `decimalplaces` tinyint(3) NOT NULL DEFAULT '2',
  `rate` double NOT NULL DEFAULT '1',
  PRIMARY KEY (`currabrev`),
  KEY `Country` (`country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `custallocns`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custallocns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `amt` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `datealloc` date NOT NULL DEFAULT '0000-00-00',
  `transid_allocfrom` int(11) NOT NULL DEFAULT '0',
  `transid_allocto` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `DateAlloc` (`datealloc`),
  KEY `TransID_AllocFrom` (`transid_allocfrom`),
  KEY `TransID_AllocTo` (`transid_allocto`),
  CONSTRAINT `custallocns_ibfk_1` FOREIGN KEY (`transid_allocfrom`) REFERENCES `debtortrans` (`id`),
  CONSTRAINT `custallocns_ibfk_2` FOREIGN KEY (`transid_allocto`) REFERENCES `debtortrans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `custbranch`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custbranch` (
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `brname` varchar(40) NOT NULL DEFAULT '',
  `braddress1` varchar(40) NOT NULL DEFAULT '',
  `braddress2` varchar(40) NOT NULL DEFAULT '',
  `braddress3` varchar(40) NOT NULL DEFAULT '',
  `braddress4` varchar(50) NOT NULL DEFAULT '',
  `braddress5` varchar(20) NOT NULL DEFAULT '',
  `braddress6` varchar(40) NOT NULL DEFAULT '',
  `lat` float(10,6) NOT NULL DEFAULT '0.000000',
  `lng` float(10,6) NOT NULL DEFAULT '0.000000',
  `estdeliverydays` smallint(6) NOT NULL DEFAULT '1',
  `area` char(3) NOT NULL,
  `salesman` varchar(4) NOT NULL DEFAULT '',
  `fwddate` smallint(6) NOT NULL DEFAULT '0',
  `phoneno` varchar(20) NOT NULL DEFAULT '',
  `faxno` varchar(20) NOT NULL DEFAULT '',
  `contactname` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  `defaultlocation` varchar(5) NOT NULL DEFAULT '',
  `taxgroupid` tinyint(4) NOT NULL DEFAULT '1',
  `defaultshipvia` int(11) NOT NULL DEFAULT '1',
  `deliverblind` tinyint(1) DEFAULT '1',
  `disabletrans` tinyint(4) NOT NULL DEFAULT '0',
  `brpostaddr1` varchar(40) NOT NULL DEFAULT '',
  `brpostaddr2` varchar(40) NOT NULL DEFAULT '',
  `brpostaddr3` varchar(30) NOT NULL DEFAULT '',
  `brpostaddr4` varchar(20) NOT NULL DEFAULT '',
  `brpostaddr5` varchar(20) NOT NULL DEFAULT '',
  `brpostaddr6` varchar(15) NOT NULL DEFAULT '',
  `specialinstructions` text NOT NULL,
  `custbranchcode` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`branchcode`,`debtorno`),
  KEY `BrName` (`brname`),
  KEY `DebtorNo` (`debtorno`),
  KEY `Salesman` (`salesman`),
  KEY `Area` (`area`),
  KEY `DefaultLocation` (`defaultlocation`),
  KEY `DefaultShipVia` (`defaultshipvia`),
  KEY `taxgroupid` (`taxgroupid`),
  CONSTRAINT `custbranch_ibfk_1` FOREIGN KEY (`debtorno`) REFERENCES `debtorsmaster` (`debtorno`),
  CONSTRAINT `custbranch_ibfk_2` FOREIGN KEY (`area`) REFERENCES `areas` (`areacode`),
  CONSTRAINT `custbranch_ibfk_3` FOREIGN KEY (`salesman`) REFERENCES `salesman` (`salesmancode`),
  CONSTRAINT `custbranch_ibfk_4` FOREIGN KEY (`defaultlocation`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `custbranch_ibfk_6` FOREIGN KEY (`defaultshipvia`) REFERENCES `shippers` (`shipper_id`),
  CONSTRAINT `custbranch_ibfk_7` FOREIGN KEY (`taxgroupid`) REFERENCES `taxgroups` (`taxgroupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `custcontacts`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custcontacts` (
  `contid` int(11) NOT NULL AUTO_INCREMENT,
  `debtorno` varchar(10) NOT NULL,
  `contactname` varchar(40) NOT NULL,
  `role` varchar(40) NOT NULL,
  `phoneno` varchar(20) NOT NULL,
  `notes` varchar(255) NOT NULL,
  `email` varchar(55) NOT NULL,
  PRIMARY KEY (`contid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `custnotes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custnotes` (
  `noteid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `debtorno` varchar(10) NOT NULL DEFAULT '0',
  `href` varchar(100) NOT NULL,
  `note` text NOT NULL,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `priority` varchar(20) NOT NULL,
  PRIMARY KEY (`noteid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `debtorsmaster`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `debtorsmaster` (
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `name` varchar(40) NOT NULL DEFAULT '',
  `address1` varchar(40) NOT NULL DEFAULT '',
  `address2` varchar(40) NOT NULL DEFAULT '',
  `address3` varchar(40) NOT NULL DEFAULT '',
  `address4` varchar(50) NOT NULL DEFAULT '',
  `address5` varchar(20) NOT NULL DEFAULT '',
  `address6` varchar(40) NOT NULL DEFAULT '',
  `currcode` char(3) NOT NULL DEFAULT '',
  `salestype` char(2) NOT NULL DEFAULT '',
  `clientsince` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `holdreason` smallint(6) NOT NULL DEFAULT '0',
  `paymentterms` char(2) NOT NULL DEFAULT 'f',
  `discount` double NOT NULL DEFAULT '0',
  `pymtdiscount` double NOT NULL DEFAULT '0',
  `lastpaid` double NOT NULL DEFAULT '0',
  `lastpaiddate` datetime DEFAULT NULL,
  `creditlimit` double NOT NULL DEFAULT '1000',
  `invaddrbranch` tinyint(4) NOT NULL DEFAULT '0',
  `discountcode` char(2) NOT NULL DEFAULT '',
  `ediinvoices` tinyint(4) NOT NULL DEFAULT '0',
  `ediorders` tinyint(4) NOT NULL DEFAULT '0',
  `edireference` varchar(20) NOT NULL DEFAULT '',
  `editransport` varchar(5) NOT NULL DEFAULT 'email',
  `ediaddress` varchar(50) NOT NULL DEFAULT '',
  `ediserveruser` varchar(20) NOT NULL DEFAULT '',
  `ediserverpwd` varchar(20) NOT NULL DEFAULT '',
  `taxref` varchar(20) NOT NULL DEFAULT '',
  `customerpoline` tinyint(1) NOT NULL DEFAULT '0',
  `typeid` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`debtorno`),
  KEY `Currency` (`currcode`),
  KEY `HoldReason` (`holdreason`),
  KEY `Name` (`name`),
  KEY `PaymentTerms` (`paymentterms`),
  KEY `SalesType` (`salestype`),
  KEY `EDIInvoices` (`ediinvoices`),
  KEY `EDIOrders` (`ediorders`),
  KEY `debtorsmaster_ibfk_5` (`typeid`),
  CONSTRAINT `debtorsmaster_ibfk_1` FOREIGN KEY (`holdreason`) REFERENCES `holdreasons` (`reasoncode`),
  CONSTRAINT `debtorsmaster_ibfk_2` FOREIGN KEY (`currcode`) REFERENCES `currencies` (`currabrev`),
  CONSTRAINT `debtorsmaster_ibfk_3` FOREIGN KEY (`paymentterms`) REFERENCES `paymentterms` (`termsindicator`),
  CONSTRAINT `debtorsmaster_ibfk_4` FOREIGN KEY (`salestype`) REFERENCES `salestypes` (`typeabbrev`),
  CONSTRAINT `debtorsmaster_ibfk_5` FOREIGN KEY (`typeid`) REFERENCES `debtortype` (`typeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `debtortrans`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `debtortrans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transno` int(11) NOT NULL DEFAULT '0',
  `type` smallint(6) NOT NULL DEFAULT '0',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `trandate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `inputdate` datetime NOT NULL,
  `prd` smallint(6) NOT NULL DEFAULT '0',
  `settled` tinyint(4) NOT NULL DEFAULT '0',
  `reference` varchar(20) NOT NULL DEFAULT '',
  `tpe` char(2) NOT NULL DEFAULT '',
  `order_` int(11) NOT NULL DEFAULT '0',
  `rate` double NOT NULL DEFAULT '0',
  `ovamount` double NOT NULL DEFAULT '0',
  `ovgst` double NOT NULL DEFAULT '0',
  `ovfreight` double NOT NULL DEFAULT '0',
  `ovdiscount` double NOT NULL DEFAULT '0',
  `diffonexch` double NOT NULL DEFAULT '0',
  `alloc` double NOT NULL DEFAULT '0',
  `invtext` text,
  `shipvia` int(11) NOT NULL DEFAULT '0',
  `edisent` tinyint(4) NOT NULL DEFAULT '0',
  `consignment` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `DebtorNo` (`debtorno`,`branchcode`),
  KEY `Order_` (`order_`),
  KEY `Prd` (`prd`),
  KEY `Tpe` (`tpe`),
  KEY `Type` (`type`),
  KEY `Settled` (`settled`),
  KEY `TranDate` (`trandate`),
  KEY `TransNo` (`transno`),
  KEY `Type_2` (`type`,`transno`),
  KEY `EDISent` (`edisent`),
  CONSTRAINT `debtortrans_ibfk_2` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`),
  CONSTRAINT `debtortrans_ibfk_3` FOREIGN KEY (`prd`) REFERENCES `periods` (`periodno`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `debtortranstaxes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `debtortranstaxes` (
  `debtortransid` int(11) NOT NULL DEFAULT '0',
  `taxauthid` tinyint(4) NOT NULL DEFAULT '0',
  `taxamount` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`debtortransid`,`taxauthid`),
  KEY `taxauthid` (`taxauthid`),
  CONSTRAINT `debtortranstaxes_ibfk_1` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`),
  CONSTRAINT `debtortranstaxes_ibfk_2` FOREIGN KEY (`debtortransid`) REFERENCES `debtortrans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `debtortype`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `debtortype` (
  `typeid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `typename` varchar(100) NOT NULL,
  PRIMARY KEY (`typeid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `debtortypenotes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `debtortypenotes` (
  `noteid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `typeid` tinyint(4) NOT NULL DEFAULT '0',
  `href` varchar(100) NOT NULL,
  `note` varchar(200) NOT NULL,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `priority` varchar(20) NOT NULL,
  PRIMARY KEY (`noteid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `deliverynotes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deliverynotes` (
  `deliverynotenumber` int(11) NOT NULL,
  `deliverynotelineno` tinyint(4) NOT NULL,
  `salesorderno` int(11) NOT NULL,
  `salesorderlineno` int(11) NOT NULL,
  `qtydelivered` double NOT NULL DEFAULT '0',
  `printed` tinyint(4) NOT NULL DEFAULT '0',
  `invoiced` tinyint(4) NOT NULL DEFAULT '0',
  `deliverydate` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`deliverynotenumber`,`deliverynotelineno`),
  KEY `deliverynotes_ibfk_2` (`salesorderno`,`salesorderlineno`),
  CONSTRAINT `deliverynotes_ibfk_1` FOREIGN KEY (`salesorderno`) REFERENCES `salesorders` (`orderno`),
  CONSTRAINT `deliverynotes_ibfk_2` FOREIGN KEY (`salesorderno`, `salesorderlineno`) REFERENCES `salesorderdetails` (`orderno`, `orderlineno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `departments`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departments` (
  `departmentid` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL DEFAULT '',
  `authoriser` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`departmentid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `discountmatrix`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `discountmatrix` (
  `salestype` char(2) NOT NULL DEFAULT '',
  `discountcategory` char(2) NOT NULL DEFAULT '',
  `quantitybreak` int(11) NOT NULL DEFAULT '1',
  `discountrate` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`salestype`,`discountcategory`,`quantitybreak`),
  KEY `QuantityBreak` (`quantitybreak`),
  KEY `DiscountCategory` (`discountcategory`),
  KEY `SalesType` (`salestype`),
  CONSTRAINT `discountmatrix_ibfk_1` FOREIGN KEY (`salestype`) REFERENCES `salestypes` (`typeabbrev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `edi_orders_seg_groups`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `edi_orders_seg_groups` (
  `seggroupno` tinyint(4) NOT NULL DEFAULT '0',
  `maxoccur` int(4) NOT NULL DEFAULT '0',
  `parentseggroup` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`seggroupno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `edi_orders_segs`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `edi_orders_segs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `segtag` char(3) NOT NULL DEFAULT '',
  `seggroup` tinyint(4) NOT NULL DEFAULT '0',
  `maxoccur` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `SegTag` (`segtag`),
  KEY `SegNo` (`seggroup`)
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ediitemmapping`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ediitemmapping` (
  `supporcust` varchar(4) NOT NULL DEFAULT '',
  `partnercode` varchar(10) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `partnerstockid` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`supporcust`,`partnercode`,`stockid`),
  KEY `PartnerCode` (`partnercode`),
  KEY `StockID` (`stockid`),
  KEY `PartnerStockID` (`partnerstockid`),
  KEY `SuppOrCust` (`supporcust`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `edimessageformat`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `edimessageformat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `partnercode` varchar(10) NOT NULL DEFAULT '',
  `messagetype` varchar(6) NOT NULL DEFAULT '',
  `section` varchar(7) NOT NULL DEFAULT '',
  `sequenceno` int(11) NOT NULL DEFAULT '0',
  `linetext` varchar(70) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `PartnerCode` (`partnercode`,`messagetype`,`sequenceno`),
  KEY `Section` (`section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `emailsettings`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emailsettings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `host` varchar(30) NOT NULL,
  `port` char(5) NOT NULL,
  `heloaddress` varchar(20) NOT NULL,
  `username` varchar(30) DEFAULT NULL,
  `password` varchar(30) DEFAULT NULL,
  `timeout` int(11) DEFAULT '5',
  `companyname` varchar(50) DEFAULT NULL,
  `auth` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `factorcompanies`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `factorcompanies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coyname` varchar(50) NOT NULL DEFAULT '',
  `address1` varchar(40) NOT NULL DEFAULT '',
  `address2` varchar(40) NOT NULL DEFAULT '',
  `address3` varchar(40) NOT NULL DEFAULT '',
  `address4` varchar(40) NOT NULL DEFAULT '',
  `address5` varchar(20) NOT NULL DEFAULT '',
  `address6` varchar(15) NOT NULL DEFAULT '',
  `contact` varchar(25) NOT NULL DEFAULT '',
  `telephone` varchar(25) NOT NULL DEFAULT '',
  `fax` varchar(25) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `factor_name` (`coyname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fixedassetcategories`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fixedassetcategories` (
  `categoryid` char(6) NOT NULL DEFAULT '',
  `categorydescription` char(20) NOT NULL DEFAULT '',
  `costact` int(11) NOT NULL DEFAULT '0',
  `depnact` int(11) NOT NULL DEFAULT '0',
  `disposalact` int(11) NOT NULL DEFAULT '80000',
  `accumdepnact` int(11) NOT NULL DEFAULT '0',
  `defaultdepnrate` double NOT NULL DEFAULT '0.2',
  `defaultdepntype` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`categoryid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fixedassetlocations`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fixedassetlocations` (
  `locationid` char(6) NOT NULL DEFAULT '',
  `locationdescription` char(20) NOT NULL DEFAULT '',
  `parentlocationid` char(6) DEFAULT '',
  PRIMARY KEY (`locationid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fixedassets`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fixedassets` (
  `assetid` int(11) NOT NULL AUTO_INCREMENT,
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `barcode` varchar(20) NOT NULL,
  `assetlocation` varchar(6) NOT NULL DEFAULT '',
  `cost` double NOT NULL DEFAULT '0',
  `accumdepn` double NOT NULL DEFAULT '0',
  `datepurchased` date NOT NULL DEFAULT '0000-00-00',
  `disposalproceeds` double NOT NULL DEFAULT '0',
  `assetcategoryid` varchar(6) NOT NULL DEFAULT '',
  `description` varchar(50) NOT NULL DEFAULT '',
  `longdescription` text NOT NULL,
  `depntype` int(11) NOT NULL DEFAULT '1',
  `depnrate` double NOT NULL,
  `disposaldate` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`assetid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fixedassettrans`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fixedassettrans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assetid` int(11) NOT NULL,
  `transtype` tinyint(4) NOT NULL,
  `transdate` date NOT NULL,
  `transno` int(11) NOT NULL,
  `periodno` smallint(6) NOT NULL,
  `inputdate` date NOT NULL,
  `fixedassettranstype` varchar(8) NOT NULL,
  `amount` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `assetid` (`assetid`,`transtype`,`transno`),
  KEY `inputdate` (`inputdate`),
  KEY `transdate` (`transdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `freightcosts`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `freightcosts` (
  `shipcostfromid` int(11) NOT NULL AUTO_INCREMENT,
  `locationfrom` varchar(5) NOT NULL DEFAULT '',
  `destination` varchar(40) NOT NULL DEFAULT '',
  `shipperid` int(11) NOT NULL DEFAULT '0',
  `cubrate` double NOT NULL DEFAULT '0',
  `kgrate` double NOT NULL DEFAULT '0',
  `maxkgs` double NOT NULL DEFAULT '999999',
  `maxcub` double NOT NULL DEFAULT '999999',
  `fixedprice` double NOT NULL DEFAULT '0',
  `minimumchg` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`shipcostfromid`),
  KEY `Destination` (`destination`),
  KEY `LocationFrom` (`locationfrom`),
  KEY `ShipperID` (`shipperid`),
  KEY `Destination_2` (`destination`,`locationfrom`,`shipperid`),
  CONSTRAINT `freightcosts_ibfk_1` FOREIGN KEY (`locationfrom`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `freightcosts_ibfk_2` FOREIGN KEY (`shipperid`) REFERENCES `shippers` (`shipper_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `geocode_param`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `geocode_param` (
  `geocodeid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `geocode_key` varchar(200) NOT NULL DEFAULT '',
  `center_long` varchar(20) NOT NULL DEFAULT '',
  `center_lat` varchar(20) NOT NULL DEFAULT '',
  `map_height` varchar(10) NOT NULL DEFAULT '',
  `map_width` varchar(10) NOT NULL DEFAULT '',
  `map_host` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`geocodeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gltrans`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gltrans` (
  `counterindex` int(11) NOT NULL AUTO_INCREMENT,
  `type` smallint(6) NOT NULL DEFAULT '0',
  `typeno` bigint(16) NOT NULL DEFAULT '1',
  `chequeno` int(11) NOT NULL DEFAULT '0',
  `trandate` date NOT NULL DEFAULT '0000-00-00',
  `periodno` smallint(6) NOT NULL DEFAULT '0',
  `account` varchar(20) NOT NULL DEFAULT '0',
  `narrative` varchar(200) NOT NULL DEFAULT '',
  `amount` double NOT NULL DEFAULT '0',
  `posted` tinyint(4) NOT NULL DEFAULT '0',
  `jobref` varchar(20) NOT NULL DEFAULT '',
  `tag` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`counterindex`),
  KEY `Account` (`account`),
  KEY `ChequeNo` (`chequeno`),
  KEY `PeriodNo` (`periodno`),
  KEY `Posted` (`posted`),
  KEY `TranDate` (`trandate`),
  KEY `TypeNo` (`typeno`),
  KEY `Type_and_Number` (`type`,`typeno`),
  KEY `JobRef` (`jobref`),
  CONSTRAINT `gltrans_ibfk_1` FOREIGN KEY (`account`) REFERENCES `chartmaster` (`accountcode`),
  CONSTRAINT `gltrans_ibfk_2` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`),
  CONSTRAINT `gltrans_ibfk_3` FOREIGN KEY (`periodno`) REFERENCES `periods` (`periodno`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `grns`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grns` (
  `grnbatch` smallint(6) NOT NULL DEFAULT '0',
  `grnno` int(11) NOT NULL AUTO_INCREMENT,
  `podetailitem` int(11) NOT NULL DEFAULT '0',
  `itemcode` varchar(20) NOT NULL DEFAULT '',
  `deliverydate` date NOT NULL DEFAULT '0000-00-00',
  `itemdescription` varchar(100) NOT NULL DEFAULT '',
  `qtyrecd` double NOT NULL DEFAULT '0',
  `quantityinv` double NOT NULL DEFAULT '0',
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `stdcostunit` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`grnno`),
  KEY `DeliveryDate` (`deliverydate`),
  KEY `ItemCode` (`itemcode`),
  KEY `PODetailItem` (`podetailitem`),
  KEY `SupplierID` (`supplierid`),
  CONSTRAINT `grns_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`supplierid`),
  CONSTRAINT `grns_ibfk_2` FOREIGN KEY (`podetailitem`) REFERENCES `purchorderdetails` (`podetailitem`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `holdreasons`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `holdreasons` (
  `reasoncode` smallint(6) NOT NULL DEFAULT '1',
  `reasondescription` char(30) NOT NULL DEFAULT '',
  `dissallowinvoices` tinyint(4) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`reasoncode`),
  KEY `ReasonDescription` (`reasondescription`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `internalstockcatrole`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `internalstockcatrole` (
  `categoryid` varchar(6) NOT NULL,
  `secroleid` int(11) NOT NULL,
  PRIMARY KEY (`categoryid`,`secroleid`),
  KEY `internalstockcatrole_ibfk_1` (`categoryid`),
  KEY `internalstockcatrole_ibfk_2` (`secroleid`),
  CONSTRAINT `internalstockcatrole_ibfk_1` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`categoryid`),
  CONSTRAINT `internalstockcatrole_ibfk_2` FOREIGN KEY (`secroleid`) REFERENCES `securityroles` (`secroleid`),
  CONSTRAINT `internalstockcatrole_ibfk_3` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`categoryid`),
  CONSTRAINT `internalstockcatrole_ibfk_4` FOREIGN KEY (`secroleid`) REFERENCES `securityroles` (`secroleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jobcards`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `createdate` date DEFAULT NULL,
  `completedate` date DEFAULT NULL,
  `invoice` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `labelfields`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `labelfields` (
  `labelfieldid` int(11) NOT NULL AUTO_INCREMENT,
  `labelid` tinyint(4) NOT NULL,
  `fieldvalue` varchar(20) NOT NULL,
  `vpos` double NOT NULL DEFAULT '0',
  `hpos` double NOT NULL DEFAULT '0',
  `fontsize` tinyint(4) NOT NULL,
  `barcode` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`labelfieldid`),
  KEY `labelid` (`labelid`),
  KEY `vpos` (`vpos`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `labels`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `labels` (
  `labelid` tinyint(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(50) NOT NULL,
  `pagewidth` double NOT NULL DEFAULT '0',
  `pageheight` double NOT NULL DEFAULT '0',
  `height` double NOT NULL DEFAULT '0',
  `width` double NOT NULL DEFAULT '0',
  `topmargin` double NOT NULL DEFAULT '0',
  `leftmargin` double NOT NULL DEFAULT '0',
  `rowheight` double NOT NULL DEFAULT '0',
  `columnwidth` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`labelid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lastcostrollup`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lastcostrollup` (
  `stockid` char(20) NOT NULL DEFAULT '',
  `totalonhand` double NOT NULL DEFAULT '0',
  `matcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `labcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `oheadcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `categoryid` char(6) NOT NULL DEFAULT '',
  `stockact` int(11) NOT NULL DEFAULT '0',
  `adjglact` int(11) NOT NULL DEFAULT '0',
  `newmatcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `newlabcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `newoheadcost` decimal(20,4) NOT NULL DEFAULT '0.0000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `locations`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locations` (
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `locationname` varchar(50) NOT NULL DEFAULT '',
  `deladd1` varchar(40) NOT NULL DEFAULT '',
  `deladd2` varchar(40) NOT NULL DEFAULT '',
  `deladd3` varchar(40) NOT NULL DEFAULT '',
  `deladd4` varchar(40) NOT NULL DEFAULT '',
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `tel` varchar(30) NOT NULL DEFAULT '',
  `fax` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  `contact` varchar(30) NOT NULL DEFAULT '',
  `taxprovinceid` tinyint(4) NOT NULL DEFAULT '1',
  `cashsalecustomer` varchar(10) DEFAULT '',
  `managed` int(11) DEFAULT '0',
  `cashsalebranch` varchar(10) DEFAULT '',
  `internalrequest` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'Allow (1) or not (0) internal request from this location',
  PRIMARY KEY (`loccode`),
  UNIQUE KEY `locationname` (`locationname`),
  KEY `taxprovinceid` (`taxprovinceid`),
  CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`taxprovinceid`) REFERENCES `taxprovinces` (`taxprovinceid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `locstock`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `locstock` (
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '0',
  `reorderlevel` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`loccode`,`stockid`),
  KEY `StockID` (`stockid`),
  CONSTRAINT `locstock_ibfk_1` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `locstock_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `loctransfers`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loctransfers` (
  `reference` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `shipqty` double NOT NULL DEFAULT '0',
  `recqty` double NOT NULL DEFAULT '0',
  `shipdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `recdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `shiploc` varchar(7) NOT NULL DEFAULT '',
  `recloc` varchar(7) NOT NULL DEFAULT '',
  KEY `Reference` (`reference`,`stockid`),
  KEY `ShipLoc` (`shiploc`),
  KEY `RecLoc` (`recloc`),
  KEY `StockID` (`stockid`),
  CONSTRAINT `loctransfers_ibfk_1` FOREIGN KEY (`shiploc`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `loctransfers_ibfk_2` FOREIGN KEY (`recloc`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `loctransfers_ibfk_3` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stores Shipments To And From Locations';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mrpcalendar`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mrpcalendar` (
  `calendardate` date NOT NULL,
  `daynumber` int(6) NOT NULL,
  `manufacturingflag` smallint(6) NOT NULL DEFAULT '1',
  PRIMARY KEY (`calendardate`),
  KEY `daynumber` (`daynumber`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mrpdemands`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mrpdemands` (
  `demandid` int(11) NOT NULL AUTO_INCREMENT,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `mrpdemandtype` varchar(6) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '0',
  `duedate` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`demandid`),
  KEY `StockID` (`stockid`),
  KEY `mrpdemands_ibfk_1` (`mrpdemandtype`),
  CONSTRAINT `mrpdemands_ibfk_1` FOREIGN KEY (`mrpdemandtype`) REFERENCES `mrpdemandtypes` (`mrpdemandtype`),
  CONSTRAINT `mrpdemands_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mrpdemandtypes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mrpdemandtypes` (
  `mrpdemandtype` varchar(6) NOT NULL DEFAULT '',
  `description` char(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`mrpdemandtype`),
  KEY `mrpdemandtype` (`mrpdemandtype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mrpplannedorders`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mrpplannedorders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `part` char(20) DEFAULT NULL,
  `duedate` date DEFAULT NULL,
  `supplyquantity` double DEFAULT NULL,
  `ordertype` varchar(6) DEFAULT NULL,
  `orderno` int(11) DEFAULT NULL,
  `mrpdate` date DEFAULT NULL,
  `updateflag` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `offers`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offers` (
  `offerid` int(11) NOT NULL AUTO_INCREMENT,
  `tenderid` int(11) NOT NULL DEFAULT '0',
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '0',
  `uom` varchar(15) NOT NULL DEFAULT '',
  `price` double NOT NULL DEFAULT '0',
  `expirydate` date NOT NULL DEFAULT '0000-00-00',
  `currcode` char(3) NOT NULL DEFAULT '',
  PRIMARY KEY (`offerid`),
  KEY `offers_ibfk_1` (`supplierid`),
  KEY `offers_ibfk_2` (`stockid`),
  CONSTRAINT `offers_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`supplierid`),
  CONSTRAINT `offers_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orderdeliverydifferenceslog`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orderdeliverydifferenceslog` (
  `orderno` int(11) NOT NULL DEFAULT '0',
  `invoiceno` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantitydiff` double NOT NULL DEFAULT '0',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branch` varchar(10) NOT NULL DEFAULT '',
  `can_or_bo` char(3) NOT NULL DEFAULT 'CAN',
  KEY `StockID` (`stockid`),
  KEY `DebtorNo` (`debtorno`,`branch`),
  KEY `Can_or_BO` (`can_or_bo`),
  KEY `OrderNo` (`orderno`),
  CONSTRAINT `orderdeliverydifferenceslog_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `orderdeliverydifferenceslog_ibfk_2` FOREIGN KEY (`debtorno`, `branch`) REFERENCES `custbranch` (`debtorno`, `branchcode`),
  CONSTRAINT `orderdeliverydifferenceslog_ibfk_3` FOREIGN KEY (`orderno`) REFERENCES `salesorders` (`orderno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `paymentmethods`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paymentmethods` (
  `paymentid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `paymentname` varchar(15) NOT NULL DEFAULT '',
  `paymenttype` int(11) NOT NULL DEFAULT '1',
  `receipttype` int(11) NOT NULL DEFAULT '1',
  `usepreprintedstationery` tinyint(4) NOT NULL DEFAULT '0',
  `opencashdrawer` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`paymentid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `paymentterms`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paymentterms` (
  `termsindicator` char(2) NOT NULL DEFAULT '',
  `terms` char(40) NOT NULL DEFAULT '',
  `daysbeforedue` smallint(6) NOT NULL DEFAULT '0',
  `dayinfollowingmonth` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`termsindicator`),
  KEY `DaysBeforeDue` (`daysbeforedue`),
  KEY `DayInFollowingMonth` (`dayinfollowingmonth`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pcashdetails`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pcashdetails` (
  `counterindex` int(20) NOT NULL AUTO_INCREMENT,
  `tabcode` varchar(20) NOT NULL,
  `date` date NOT NULL,
  `codeexpense` varchar(20) NOT NULL,
  `amount` double NOT NULL,
  `authorized` date NOT NULL COMMENT 'date cash assigment was revised and authorized by authorizer from tabs table',
  `posted` tinyint(4) NOT NULL COMMENT 'has (or has not) been posted into gltrans',
  `notes` text NOT NULL,
  `receipt` text COMMENT 'filename or path to scanned receipt or code of receipt to find physical receipt if tax guys or auditors show up',
  PRIMARY KEY (`counterindex`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pcexpenses`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pcexpenses` (
  `codeexpense` varchar(20) NOT NULL COMMENT 'code for the group',
  `description` varchar(50) NOT NULL COMMENT 'text description, e.g. meals, train tickets, fuel, etc',
  `glaccount` varchar(20) NOT NULL DEFAULT '0',
  `tag` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`codeexpense`),
  KEY `glaccount` (`glaccount`),
  CONSTRAINT `pcexpenses_ibfk_1` FOREIGN KEY (`glaccount`) REFERENCES `chartmaster` (`accountcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pctabexpenses`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pctabexpenses` (
  `typetabcode` varchar(20) NOT NULL,
  `codeexpense` varchar(20) NOT NULL,
  KEY `typetabcode` (`typetabcode`),
  KEY `codeexpense` (`codeexpense`),
  CONSTRAINT `pctabexpenses_ibfk_1` FOREIGN KEY (`typetabcode`) REFERENCES `pctypetabs` (`typetabcode`),
  CONSTRAINT `pctabexpenses_ibfk_2` FOREIGN KEY (`codeexpense`) REFERENCES `pcexpenses` (`codeexpense`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pctabs`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pctabs` (
  `tabcode` varchar(20) NOT NULL,
  `usercode` varchar(20) NOT NULL COMMENT 'code of user employee from www_users',
  `typetabcode` varchar(20) NOT NULL,
  `currency` char(3) NOT NULL,
  `tablimit` double NOT NULL,
  `assigner` varchar(20) NOT NULL COMMENT 'Cash assigner for the tab',
  `authorizer` varchar(20) NOT NULL COMMENT 'code of user from www_users',
  `glaccountassignment` varchar(20) NOT NULL DEFAULT '0',
  `glaccountpcash` varchar(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tabcode`),
  KEY `usercode` (`usercode`),
  KEY `typetabcode` (`typetabcode`),
  KEY `currency` (`currency`),
  KEY `authorizer` (`authorizer`),
  KEY `glaccountassignment` (`glaccountassignment`),
  CONSTRAINT `pctabs_ibfk_1` FOREIGN KEY (`usercode`) REFERENCES `www_users` (`userid`),
  CONSTRAINT `pctabs_ibfk_2` FOREIGN KEY (`typetabcode`) REFERENCES `pctypetabs` (`typetabcode`),
  CONSTRAINT `pctabs_ibfk_3` FOREIGN KEY (`currency`) REFERENCES `currencies` (`currabrev`),
  CONSTRAINT `pctabs_ibfk_4` FOREIGN KEY (`authorizer`) REFERENCES `www_users` (`userid`),
  CONSTRAINT `pctabs_ibfk_5` FOREIGN KEY (`glaccountassignment`) REFERENCES `chartmaster` (`accountcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pctypetabs`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pctypetabs` (
  `typetabcode` varchar(20) NOT NULL COMMENT 'code for the type of petty cash tab',
  `typetabdescription` varchar(50) NOT NULL COMMENT 'text description, e.g. tab for CEO',
  PRIMARY KEY (`typetabcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `periods`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `periods` (
  `periodno` smallint(6) NOT NULL DEFAULT '0',
  `lastdate_in_period` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`periodno`),
  KEY `LastDate_in_Period` (`lastdate_in_period`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pickinglistdetails`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pickinglistdetails` (
  `pickinglistno` int(11) NOT NULL DEFAULT '0',
  `pickinglistlineno` int(11) NOT NULL DEFAULT '0',
  `orderlineno` int(11) NOT NULL DEFAULT '0',
  `qtyexpected` double NOT NULL DEFAULT '0',
  `qtypicked` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`pickinglistno`,`pickinglistlineno`),
  CONSTRAINT `pickinglistdetails_ibfk_1` FOREIGN KEY (`pickinglistno`) REFERENCES `pickinglists` (`pickinglistno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pickinglists`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pickinglists` (
  `pickinglistno` int(11) NOT NULL DEFAULT '0',
  `orderno` int(11) NOT NULL DEFAULT '0',
  `pickinglistdate` date NOT NULL DEFAULT '0000-00-00',
  `dateprinted` date NOT NULL DEFAULT '0000-00-00',
  `deliverynotedate` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`pickinglistno`),
  KEY `pickinglists_ibfk_1` (`orderno`),
  CONSTRAINT `pickinglists_ibfk_1` FOREIGN KEY (`orderno`) REFERENCES `salesorders` (`orderno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prices`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prices` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `typeabbrev` char(2) NOT NULL DEFAULT '',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `price` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `startdate` date NOT NULL DEFAULT '0000-00-00',
  `enddate` date NOT NULL DEFAULT '9999-12-31',
  PRIMARY KEY (`stockid`,`typeabbrev`,`currabrev`,`debtorno`,`branchcode`,`startdate`,`enddate`),
  KEY `CurrAbrev` (`currabrev`),
  KEY `DebtorNo` (`debtorno`),
  KEY `StockID` (`stockid`),
  KEY `TypeAbbrev` (`typeabbrev`),
  CONSTRAINT `prices_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `prices_ibfk_2` FOREIGN KEY (`currabrev`) REFERENCES `currencies` (`currabrev`),
  CONSTRAINT `prices_ibfk_3` FOREIGN KEY (`typeabbrev`) REFERENCES `salestypes` (`typeabbrev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `purchdata`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchdata` (
  `supplierno` char(10) NOT NULL DEFAULT '',
  `stockid` char(20) NOT NULL DEFAULT '',
  `price` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `suppliersuom` char(50) NOT NULL DEFAULT '',
  `conversionfactor` double NOT NULL DEFAULT '1',
  `supplierdescription` char(50) NOT NULL DEFAULT '',
  `leadtime` smallint(6) NOT NULL DEFAULT '1',
  `preferred` tinyint(4) NOT NULL DEFAULT '0',
  `effectivefrom` date NOT NULL,
  `suppliers_partno` varchar(50) NOT NULL DEFAULT '',
  `minorderqty` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`supplierno`,`stockid`,`effectivefrom`),
  KEY `StockID` (`stockid`),
  KEY `SupplierNo` (`supplierno`),
  KEY `Preferred` (`preferred`),
  CONSTRAINT `purchdata_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `purchdata_ibfk_2` FOREIGN KEY (`supplierno`) REFERENCES `suppliers` (`supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `purchorderauth`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchorderauth` (
  `userid` varchar(20) NOT NULL DEFAULT '',
  `currabrev` char(3) NOT NULL DEFAULT '',
  `cancreate` smallint(2) NOT NULL DEFAULT '0',
  `authlevel` int(11) NOT NULL DEFAULT '0',
  `offhold` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`userid`,`currabrev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `purchorderdetails`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchorderdetails` (
  `podetailitem` int(11) NOT NULL AUTO_INCREMENT,
  `orderno` int(11) NOT NULL DEFAULT '0',
  `itemcode` varchar(20) NOT NULL DEFAULT '',
  `deliverydate` date NOT NULL DEFAULT '0000-00-00',
  `itemdescription` varchar(100) NOT NULL,
  `glcode` int(11) NOT NULL DEFAULT '0',
  `qtyinvoiced` double NOT NULL DEFAULT '0',
  `unitprice` double NOT NULL DEFAULT '0',
  `actprice` double NOT NULL DEFAULT '0',
  `stdcostunit` double NOT NULL DEFAULT '0',
  `quantityord` double NOT NULL DEFAULT '0',
  `quantityrecd` double NOT NULL DEFAULT '0',
  `shiptref` int(11) NOT NULL DEFAULT '0',
  `jobref` varchar(20) NOT NULL DEFAULT '',
  `completed` tinyint(4) NOT NULL DEFAULT '0',
  `suppliersunit` varchar(50) DEFAULT NULL,
  `suppliers_partno` varchar(50) NOT NULL DEFAULT '',
  `assetid` int(11) NOT NULL DEFAULT '0',
  `conversionfactor` double NOT NULL DEFAULT '1',
  PRIMARY KEY (`podetailitem`),
  KEY `DeliveryDate` (`deliverydate`),
  KEY `GLCode` (`glcode`),
  KEY `ItemCode` (`itemcode`),
  KEY `JobRef` (`jobref`),
  KEY `OrderNo` (`orderno`),
  KEY `ShiptRef` (`shiptref`),
  KEY `Completed` (`completed`),
  CONSTRAINT `purchorderdetails_ibfk_1` FOREIGN KEY (`orderno`) REFERENCES `purchorders` (`orderno`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `purchorders`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchorders` (
  `orderno` int(11) NOT NULL AUTO_INCREMENT,
  `supplierno` varchar(10) NOT NULL DEFAULT '',
  `comments` longblob,
  `orddate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `rate` double NOT NULL DEFAULT '1',
  `dateprinted` datetime DEFAULT NULL,
  `allowprint` tinyint(4) NOT NULL DEFAULT '1',
  `initiator` varchar(20) DEFAULT NULL,
  `requisitionno` varchar(15) DEFAULT NULL,
  `intostocklocation` varchar(5) NOT NULL DEFAULT '',
  `deladd1` varchar(40) NOT NULL DEFAULT '',
  `deladd2` varchar(40) NOT NULL DEFAULT '',
  `deladd3` varchar(40) NOT NULL DEFAULT '',
  `deladd4` varchar(40) NOT NULL DEFAULT '',
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `tel` varchar(15) NOT NULL DEFAULT '',
  `suppdeladdress1` varchar(40) NOT NULL DEFAULT '',
  `suppdeladdress2` varchar(40) NOT NULL DEFAULT '',
  `suppdeladdress3` varchar(40) NOT NULL DEFAULT '',
  `suppdeladdress4` varchar(40) NOT NULL DEFAULT '',
  `suppdeladdress5` varchar(20) NOT NULL DEFAULT '',
  `suppdeladdress6` varchar(15) NOT NULL DEFAULT '',
  `suppliercontact` varchar(30) NOT NULL DEFAULT '',
  `supptel` varchar(30) NOT NULL DEFAULT '',
  `contact` varchar(30) NOT NULL DEFAULT '',
  `version` decimal(3,2) NOT NULL DEFAULT '1.00',
  `revised` date NOT NULL DEFAULT '0000-00-00',
  `realorderno` varchar(16) NOT NULL DEFAULT '',
  `deliveryby` varchar(100) NOT NULL DEFAULT '',
  `deliverydate` date NOT NULL DEFAULT '0000-00-00',
  `status` varchar(12) NOT NULL DEFAULT '',
  `stat_comment` text NOT NULL,
  `paymentterms` char(2) NOT NULL DEFAULT '',
  `port` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`orderno`),
  KEY `OrdDate` (`orddate`),
  KEY `SupplierNo` (`supplierno`),
  KEY `IntoStockLocation` (`intostocklocation`),
  KEY `AllowPrintPO` (`allowprint`),
  CONSTRAINT `purchorders_ibfk_1` FOREIGN KEY (`supplierno`) REFERENCES `suppliers` (`supplierid`),
  CONSTRAINT `purchorders_ibfk_2` FOREIGN KEY (`intostocklocation`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `recurringsalesorders`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recurringsalesorders` (
  `recurrorderno` int(11) NOT NULL AUTO_INCREMENT,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `customerref` varchar(50) NOT NULL DEFAULT '',
  `buyername` varchar(50) DEFAULT NULL,
  `comments` longblob,
  `orddate` date NOT NULL DEFAULT '0000-00-00',
  `ordertype` char(2) NOT NULL DEFAULT '',
  `shipvia` int(11) NOT NULL DEFAULT '0',
  `deladd1` varchar(40) NOT NULL DEFAULT '',
  `deladd2` varchar(40) NOT NULL DEFAULT '',
  `deladd3` varchar(40) NOT NULL DEFAULT '',
  `deladd4` varchar(40) DEFAULT NULL,
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `contactphone` varchar(25) DEFAULT NULL,
  `contactemail` varchar(25) DEFAULT NULL,
  `deliverto` varchar(40) NOT NULL DEFAULT '',
  `freightcost` double NOT NULL DEFAULT '0',
  `fromstkloc` varchar(5) NOT NULL DEFAULT '',
  `lastrecurrence` date NOT NULL DEFAULT '0000-00-00',
  `stopdate` date NOT NULL DEFAULT '0000-00-00',
  `frequency` tinyint(4) NOT NULL DEFAULT '1',
  `autoinvoice` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`recurrorderno`),
  KEY `debtorno` (`debtorno`),
  KEY `orddate` (`orddate`),
  KEY `ordertype` (`ordertype`),
  KEY `locationindex` (`fromstkloc`),
  KEY `branchcode` (`branchcode`,`debtorno`),
  CONSTRAINT `recurringsalesorders_ibfk_1` FOREIGN KEY (`branchcode`, `debtorno`) REFERENCES `custbranch` (`branchcode`, `debtorno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `recurrsalesorderdetails`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recurrsalesorderdetails` (
  `recurrorderno` int(11) NOT NULL DEFAULT '0',
  `stkcode` varchar(20) NOT NULL DEFAULT '',
  `unitprice` double NOT NULL DEFAULT '0',
  `quantity` double NOT NULL DEFAULT '0',
  `discountpercent` double NOT NULL DEFAULT '0',
  `narrative` text NOT NULL,
  KEY `orderno` (`recurrorderno`),
  KEY `stkcode` (`stkcode`),
  CONSTRAINT `recurrsalesorderdetails_ibfk_1` FOREIGN KEY (`recurrorderno`) REFERENCES `recurringsalesorders` (`recurrorderno`),
  CONSTRAINT `recurrsalesorderdetails_ibfk_2` FOREIGN KEY (`stkcode`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reportcolumns`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reportcolumns` (
  `reportid` smallint(6) NOT NULL DEFAULT '0',
  `colno` smallint(6) NOT NULL DEFAULT '0',
  `heading1` varchar(15) NOT NULL DEFAULT '',
  `heading2` varchar(15) DEFAULT NULL,
  `calculation` tinyint(1) NOT NULL DEFAULT '0',
  `periodfrom` smallint(6) DEFAULT NULL,
  `periodto` smallint(6) DEFAULT NULL,
  `datatype` varchar(15) DEFAULT NULL,
  `colnumerator` tinyint(4) DEFAULT NULL,
  `coldenominator` tinyint(4) DEFAULT NULL,
  `calcoperator` char(1) DEFAULT NULL,
  `budgetoractual` tinyint(1) NOT NULL DEFAULT '0',
  `valformat` char(1) NOT NULL DEFAULT 'N',
  `constant` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`reportid`,`colno`),
  CONSTRAINT `reportcolumns_ibfk_1` FOREIGN KEY (`reportid`) REFERENCES `reportheaders` (`reportid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reportfields`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reportfields` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `reportid` int(5) NOT NULL DEFAULT '0',
  `entrytype` varchar(15) NOT NULL DEFAULT '',
  `seqnum` int(3) NOT NULL DEFAULT '0',
  `fieldname` varchar(80) NOT NULL DEFAULT '',
  `displaydesc` varchar(25) NOT NULL DEFAULT '',
  `visible` enum('1','0') NOT NULL DEFAULT '1',
  `columnbreak` enum('1','0') NOT NULL DEFAULT '1',
  `params` text,
  PRIMARY KEY (`id`),
  KEY `reportid` (`reportid`)
) ENGINE=MyISAM AUTO_INCREMENT=1805 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reportheaders`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reportheaders` (
  `reportid` smallint(6) NOT NULL AUTO_INCREMENT,
  `reportheading` varchar(80) NOT NULL DEFAULT '',
  `groupbydata1` varchar(15) NOT NULL DEFAULT '',
  `newpageafter1` tinyint(1) NOT NULL DEFAULT '0',
  `lower1` varchar(10) NOT NULL DEFAULT '',
  `upper1` varchar(10) NOT NULL DEFAULT '',
  `groupbydata2` varchar(15) DEFAULT NULL,
  `newpageafter2` tinyint(1) NOT NULL DEFAULT '0',
  `lower2` varchar(10) DEFAULT NULL,
  `upper2` varchar(10) DEFAULT NULL,
  `groupbydata3` varchar(15) DEFAULT NULL,
  `newpageafter3` tinyint(1) NOT NULL DEFAULT '0',
  `lower3` varchar(10) DEFAULT NULL,
  `upper3` varchar(10) DEFAULT NULL,
  `groupbydata4` varchar(15) NOT NULL DEFAULT '',
  `newpageafter4` tinyint(1) NOT NULL DEFAULT '0',
  `upper4` varchar(10) NOT NULL DEFAULT '',
  `lower4` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`reportid`),
  KEY `ReportHeading` (`reportheading`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reportlinks`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reportlinks` (
  `table1` varchar(25) NOT NULL DEFAULT '',
  `table2` varchar(25) NOT NULL DEFAULT '',
  `equation` varchar(75) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reports`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reports` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `reportname` varchar(30) NOT NULL DEFAULT '',
  `reporttype` char(3) NOT NULL DEFAULT 'rpt',
  `groupname` varchar(9) NOT NULL DEFAULT 'misc',
  `defaultreport` enum('1','0') NOT NULL DEFAULT '0',
  `papersize` varchar(15) NOT NULL DEFAULT 'A4,210,297',
  `paperorientation` enum('P','L') NOT NULL DEFAULT 'P',
  `margintop` int(3) NOT NULL DEFAULT '10',
  `marginbottom` int(3) NOT NULL DEFAULT '10',
  `marginleft` int(3) NOT NULL DEFAULT '10',
  `marginright` int(3) NOT NULL DEFAULT '10',
  `coynamefont` varchar(20) NOT NULL DEFAULT 'Helvetica',
  `coynamefontsize` int(3) NOT NULL DEFAULT '12',
  `coynamefontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
  `coynamealign` enum('L','C','R') NOT NULL DEFAULT 'C',
  `coynameshow` enum('1','0') NOT NULL DEFAULT '1',
  `title1desc` varchar(50) NOT NULL DEFAULT '%reportname%',
  `title1font` varchar(20) NOT NULL DEFAULT 'Helvetica',
  `title1fontsize` int(3) NOT NULL DEFAULT '10',
  `title1fontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
  `title1fontalign` enum('L','C','R') NOT NULL DEFAULT 'C',
  `title1show` enum('1','0') NOT NULL DEFAULT '1',
  `title2desc` varchar(50) NOT NULL DEFAULT 'Report Generated %date%',
  `title2font` varchar(20) NOT NULL DEFAULT 'Helvetica',
  `title2fontsize` int(3) NOT NULL DEFAULT '10',
  `title2fontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
  `title2fontalign` enum('L','C','R') NOT NULL DEFAULT 'C',
  `title2show` enum('1','0') NOT NULL DEFAULT '1',
  `filterfont` varchar(10) NOT NULL DEFAULT 'Helvetica',
  `filterfontsize` int(3) NOT NULL DEFAULT '8',
  `filterfontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
  `filterfontalign` enum('L','C','R') NOT NULL DEFAULT 'L',
  `datafont` varchar(10) NOT NULL DEFAULT 'Helvetica',
  `datafontsize` int(3) NOT NULL DEFAULT '10',
  `datafontcolor` varchar(10) NOT NULL DEFAULT 'black',
  `datafontalign` enum('L','C','R') NOT NULL DEFAULT 'L',
  `totalsfont` varchar(10) NOT NULL DEFAULT 'Helvetica',
  `totalsfontsize` int(3) NOT NULL DEFAULT '10',
  `totalsfontcolor` varchar(11) NOT NULL DEFAULT '0,0,0',
  `totalsfontalign` enum('L','C','R') NOT NULL DEFAULT 'L',
  `col1width` int(3) NOT NULL DEFAULT '25',
  `col2width` int(3) NOT NULL DEFAULT '25',
  `col3width` int(3) NOT NULL DEFAULT '25',
  `col4width` int(3) NOT NULL DEFAULT '25',
  `col5width` int(3) NOT NULL DEFAULT '25',
  `col6width` int(3) NOT NULL DEFAULT '25',
  `col7width` int(3) NOT NULL DEFAULT '25',
  `col8width` int(3) NOT NULL DEFAULT '25',
  `col9width` int(3) NOT NULL DEFAULT '25',
  `col10width` int(3) NOT NULL DEFAULT '25',
  `col11width` int(3) NOT NULL DEFAULT '25',
  `col12width` int(3) NOT NULL DEFAULT '25',
  `col13width` int(3) NOT NULL DEFAULT '25',
  `col14width` int(3) NOT NULL DEFAULT '25',
  `col15width` int(3) NOT NULL DEFAULT '25',
  `col16width` int(3) NOT NULL DEFAULT '25',
  `col17width` int(3) NOT NULL DEFAULT '25',
  `col18width` int(3) NOT NULL DEFAULT '25',
  `col19width` int(3) NOT NULL DEFAULT '25',
  `col20width` int(3) NOT NULL DEFAULT '25',
  `table1` varchar(25) NOT NULL DEFAULT '',
  `table2` varchar(25) DEFAULT NULL,
  `table2criteria` varchar(75) DEFAULT NULL,
  `table3` varchar(25) DEFAULT NULL,
  `table3criteria` varchar(75) DEFAULT NULL,
  `table4` varchar(25) DEFAULT NULL,
  `table4criteria` varchar(75) DEFAULT NULL,
  `table5` varchar(25) DEFAULT NULL,
  `table5criteria` varchar(75) DEFAULT NULL,
  `table6` varchar(25) DEFAULT NULL,
  `table6criteria` varchar(75) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`reportname`,`groupname`)
) ENGINE=MyISAM AUTO_INCREMENT=136 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salesanalysis`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salesanalysis` (
  `typeabbrev` char(2) NOT NULL DEFAULT '',
  `periodno` smallint(6) NOT NULL DEFAULT '0',
  `amt` double NOT NULL DEFAULT '0',
  `cost` double NOT NULL DEFAULT '0',
  `cust` varchar(10) NOT NULL DEFAULT '',
  `custbranch` varchar(10) NOT NULL DEFAULT '',
  `qty` double NOT NULL DEFAULT '0',
  `disc` double NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `area` varchar(3) NOT NULL,
  `budgetoractual` tinyint(1) NOT NULL DEFAULT '0',
  `salesperson` char(3) NOT NULL DEFAULT '',
  `stkcategory` varchar(6) NOT NULL DEFAULT '',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  KEY `CustBranch` (`custbranch`),
  KEY `Cust` (`cust`),
  KEY `PeriodNo` (`periodno`),
  KEY `StkCategory` (`stkcategory`),
  KEY `StockID` (`stockid`),
  KEY `TypeAbbrev` (`typeabbrev`),
  KEY `Area` (`area`),
  KEY `BudgetOrActual` (`budgetoractual`),
  KEY `Salesperson` (`salesperson`),
  CONSTRAINT `salesanalysis_ibfk_1` FOREIGN KEY (`periodno`) REFERENCES `periods` (`periodno`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescat`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescat` (
  `salescatid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `parentcatid` tinyint(4) DEFAULT NULL,
  `salescatname` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`salescatid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salescatprod`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salescatprod` (
  `salescatid` tinyint(4) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`salescatid`,`stockid`),
  KEY `salescatid` (`salescatid`),
  KEY `stockid` (`stockid`),
  CONSTRAINT `salescatprod_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `salescatprod_ibfk_2` FOREIGN KEY (`salescatid`) REFERENCES `salescat` (`salescatid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salesglpostings`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salesglpostings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `area` varchar(3) NOT NULL,
  `stkcat` varchar(6) NOT NULL DEFAULT '',
  `discountglcode` int(11) NOT NULL DEFAULT '0',
  `salesglcode` int(11) NOT NULL DEFAULT '0',
  `salestype` char(2) NOT NULL DEFAULT 'AN',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Area_StkCat` (`area`,`stkcat`,`salestype`),
  KEY `Area` (`area`),
  KEY `StkCat` (`stkcat`),
  KEY `SalesType` (`salestype`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salesman`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salesman` (
  `salesmancode` varchar(4) NOT NULL DEFAULT '',
  `salesmanname` char(30) NOT NULL DEFAULT '',
  `smantel` char(20) NOT NULL DEFAULT '',
  `smanfax` char(20) NOT NULL DEFAULT '',
  `commissionrate1` double NOT NULL DEFAULT '0',
  `breakpoint` decimal(10,0) NOT NULL DEFAULT '0',
  `commissionrate2` double NOT NULL DEFAULT '0',
  `current` tinyint(4) NOT NULL COMMENT 'Salesman current (1) or not (0)',
  PRIMARY KEY (`salesmancode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salesorderdetails`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salesorderdetails` (
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
  `itemdue` date DEFAULT NULL COMMENT 'Due date for line item.  Some customers require \r\nacknowledgements with due dates by line item',
  `poline` varchar(10) DEFAULT NULL COMMENT 'Some Customers require acknowledgements with a PO line number for each sales line',
  PRIMARY KEY (`orderlineno`,`orderno`),
  KEY `OrderNo` (`orderno`),
  KEY `StkCode` (`stkcode`),
  KEY `Completed` (`completed`),
  CONSTRAINT `salesorderdetails_ibfk_1` FOREIGN KEY (`orderno`) REFERENCES `salesorders` (`orderno`),
  CONSTRAINT `salesorderdetails_ibfk_2` FOREIGN KEY (`stkcode`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salesorders`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salesorders` (
  `orderno` int(11) NOT NULL,
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `customerref` varchar(50) NOT NULL DEFAULT '',
  `buyername` varchar(50) DEFAULT NULL,
  `comments` longblob,
  `orddate` date NOT NULL DEFAULT '0000-00-00',
  `ordertype` char(2) NOT NULL DEFAULT '',
  `shipvia` int(11) NOT NULL DEFAULT '0',
  `deladd1` varchar(40) NOT NULL DEFAULT '',
  `deladd2` varchar(40) NOT NULL DEFAULT '',
  `deladd3` varchar(40) NOT NULL DEFAULT '',
  `deladd4` varchar(40) DEFAULT NULL,
  `deladd5` varchar(20) NOT NULL DEFAULT '',
  `deladd6` varchar(15) NOT NULL DEFAULT '',
  `contactphone` varchar(25) DEFAULT NULL,
  `contactemail` varchar(40) DEFAULT NULL,
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
  `salesperson` varchar(4) NOT NULL,
  PRIMARY KEY (`orderno`),
  KEY `DebtorNo` (`debtorno`),
  KEY `OrdDate` (`orddate`),
  KEY `OrderType` (`ordertype`),
  KEY `LocationIndex` (`fromstkloc`),
  KEY `BranchCode` (`branchcode`,`debtorno`),
  KEY `ShipVia` (`shipvia`),
  KEY `quotation` (`quotation`),
  KEY `poplaced` (`poplaced`),
  KEY `salesperson` (`salesperson`),
  CONSTRAINT `salesorders_ibfk_1` FOREIGN KEY (`branchcode`, `debtorno`) REFERENCES `custbranch` (`branchcode`, `debtorno`),
  CONSTRAINT `salesorders_ibfk_2` FOREIGN KEY (`shipvia`) REFERENCES `shippers` (`shipper_id`),
  CONSTRAINT `salesorders_ibfk_3` FOREIGN KEY (`fromstkloc`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `salestypes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salestypes` (
  `typeabbrev` char(2) NOT NULL DEFAULT '',
  `sales_type` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`typeabbrev`),
  KEY `Sales_Type` (`sales_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scripts`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scripts` (
  `script` varchar(78) NOT NULL DEFAULT '',
  `pagesecurity` int(11) NOT NULL DEFAULT '1',
  `description` text NOT NULL,
  PRIMARY KEY (`script`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `securitygroups`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `securitygroups` (
  `secroleid` int(11) NOT NULL DEFAULT '0',
  `tokenid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`secroleid`,`tokenid`),
  KEY `secroleid` (`secroleid`),
  KEY `tokenid` (`tokenid`),
  CONSTRAINT `securitygroups_secroleid_fk` FOREIGN KEY (`secroleid`) REFERENCES `securityroles` (`secroleid`),
  CONSTRAINT `securitygroups_tokenid_fk` FOREIGN KEY (`tokenid`) REFERENCES `securitytokens` (`tokenid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `securityroles`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `securityroles` (
  `secroleid` int(11) NOT NULL AUTO_INCREMENT,
  `secrolename` text NOT NULL,
  PRIMARY KEY (`secroleid`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `securitytokens`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `securitytokens` (
  `tokenid` int(11) NOT NULL DEFAULT '0',
  `tokenname` text NOT NULL,
  PRIMARY KEY (`tokenid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shipmentcharges`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipmentcharges` (
  `shiptchgid` int(11) NOT NULL AUTO_INCREMENT,
  `shiptref` int(11) NOT NULL DEFAULT '0',
  `transtype` smallint(6) NOT NULL DEFAULT '0',
  `transno` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `value` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`shiptchgid`),
  KEY `TransType` (`transtype`,`transno`),
  KEY `ShiptRef` (`shiptref`),
  KEY `StockID` (`stockid`),
  KEY `TransType_2` (`transtype`),
  CONSTRAINT `shipmentcharges_ibfk_1` FOREIGN KEY (`shiptref`) REFERENCES `shipments` (`shiptref`),
  CONSTRAINT `shipmentcharges_ibfk_2` FOREIGN KEY (`transtype`) REFERENCES `systypes` (`typeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shipments`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipments` (
  `shiptref` int(11) NOT NULL DEFAULT '0',
  `voyageref` varchar(20) NOT NULL DEFAULT '0',
  `vessel` varchar(50) NOT NULL DEFAULT '',
  `eta` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `accumvalue` double NOT NULL DEFAULT '0',
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `closed` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`shiptref`),
  KEY `ETA` (`eta`),
  KEY `SupplierID` (`supplierid`),
  KEY `ShipperRef` (`voyageref`),
  KEY `Vessel` (`vessel`),
  CONSTRAINT `shipments_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shippers`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shippers` (
  `shipper_id` int(11) NOT NULL AUTO_INCREMENT,
  `shippername` char(40) NOT NULL DEFAULT '',
  `mincharge` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`shipper_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockcategory`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockcategory` (
  `categoryid` char(6) NOT NULL DEFAULT '',
  `categorydescription` char(20) NOT NULL DEFAULT '',
  `stocktype` char(1) NOT NULL DEFAULT 'F',
  `stockact` int(11) NOT NULL DEFAULT '0',
  `adjglact` int(11) NOT NULL DEFAULT '0',
  `issueglact` int(11) NOT NULL DEFAULT '0',
  `purchpricevaract` int(11) NOT NULL DEFAULT '80000',
  `materialuseagevarac` int(11) NOT NULL DEFAULT '80000',
  `wipact` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`categoryid`),
  KEY `CategoryDescription` (`categorydescription`),
  KEY `StockType` (`stocktype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockcatproperties`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockcatproperties` (
  `stkcatpropid` int(11) NOT NULL AUTO_INCREMENT,
  `categoryid` char(6) NOT NULL,
  `label` text NOT NULL,
  `controltype` tinyint(4) NOT NULL DEFAULT '0',
  `defaultvalue` varchar(100) NOT NULL DEFAULT '''''',
  `maximumvalue` double NOT NULL DEFAULT '999999999',
  `reqatsalesorder` tinyint(4) NOT NULL DEFAULT '0',
  `minimumvalue` double NOT NULL DEFAULT '-999999999',
  `numericvalue` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`stkcatpropid`),
  KEY `categoryid` (`categoryid`),
  CONSTRAINT `stockcatproperties_ibfk_1` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`categoryid`),
  CONSTRAINT `stockcatproperties_ibfk_2` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`categoryid`),
  CONSTRAINT `stockcatproperties_ibfk_3` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`categoryid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockcheckfreeze`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockcheckfreeze` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `qoh` double NOT NULL DEFAULT '0',
  `stockcheckdate` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`stockid`,`loccode`),
  KEY `LocCode` (`loccode`),
  CONSTRAINT `stockcheckfreeze_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockcheckfreeze_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockcounts`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockcounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `qtycounted` double NOT NULL DEFAULT '0',
  `reference` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `StockID` (`stockid`),
  KEY `LocCode` (`loccode`),
  CONSTRAINT `stockcounts_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockcounts_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockitemproperties`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockitemproperties` (
  `stockid` varchar(20) NOT NULL,
  `stkcatpropid` int(11) NOT NULL,
  `value` varchar(50) NOT NULL,
  PRIMARY KEY (`stockid`,`stkcatpropid`),
  KEY `stockid` (`stockid`),
  KEY `value` (`value`),
  KEY `stkcatpropid` (`stkcatpropid`),
  CONSTRAINT `stockitemproperties_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockitemproperties_ibfk_2` FOREIGN KEY (`stkcatpropid`) REFERENCES `stockcatproperties` (`stkcatpropid`),
  CONSTRAINT `stockitemproperties_ibfk_3` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockitemproperties_ibfk_4` FOREIGN KEY (`stkcatpropid`) REFERENCES `stockcatproperties` (`stkcatpropid`),
  CONSTRAINT `stockitemproperties_ibfk_5` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockitemproperties_ibfk_6` FOREIGN KEY (`stkcatpropid`) REFERENCES `stockcatproperties` (`stkcatpropid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockmaster`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockmaster` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `categoryid` varchar(6) NOT NULL DEFAULT '',
  `description` varchar(50) NOT NULL DEFAULT '',
  `longdescription` text NOT NULL,
  `units` varchar(20) NOT NULL DEFAULT 'each',
  `mbflag` char(1) NOT NULL DEFAULT 'B',
  `actualcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `lastcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `materialcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `labourcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `overheadcost` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `lowestlevel` smallint(6) NOT NULL DEFAULT '0',
  `discontinued` tinyint(4) NOT NULL DEFAULT '0',
  `controlled` tinyint(4) NOT NULL DEFAULT '0',
  `eoq` double NOT NULL DEFAULT '0',
  `volume` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `kgs` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `barcode` varchar(50) NOT NULL DEFAULT '',
  `discountcategory` char(2) NOT NULL DEFAULT '',
  `taxcatid` tinyint(4) NOT NULL DEFAULT '1',
  `serialised` tinyint(4) NOT NULL DEFAULT '0',
  `appendfile` varchar(40) NOT NULL DEFAULT 'none',
  `perishable` tinyint(1) NOT NULL DEFAULT '0',
  `decimalplaces` tinyint(4) NOT NULL DEFAULT '0',
  `pansize` double NOT NULL DEFAULT '0',
  `shrinkfactor` double NOT NULL DEFAULT '0',
  `nextserialno` bigint(20) NOT NULL DEFAULT '0',
  `netweight` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `lastcostupdate` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`stockid`),
  KEY `CategoryID` (`categoryid`),
  KEY `Description` (`description`),
  KEY `MBflag` (`mbflag`),
  KEY `StockID` (`stockid`,`categoryid`),
  KEY `Controlled` (`controlled`),
  KEY `DiscountCategory` (`discountcategory`),
  KEY `taxcatid` (`taxcatid`),
  CONSTRAINT `stockmaster_ibfk_1` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`categoryid`),
  CONSTRAINT `stockmaster_ibfk_2` FOREIGN KEY (`taxcatid`) REFERENCES `taxcategories` (`taxcatid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockmoves`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockmoves` (
  `stkmoveno` int(11) NOT NULL AUTO_INCREMENT,
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `type` smallint(6) NOT NULL DEFAULT '0',
  `transno` int(11) NOT NULL DEFAULT '0',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `trandate` date NOT NULL DEFAULT '0000-00-00',
  `debtorno` varchar(10) NOT NULL DEFAULT '',
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `price` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `prd` smallint(6) NOT NULL DEFAULT '0',
  `reference` varchar(40) NOT NULL DEFAULT '',
  `qty` double NOT NULL DEFAULT '1',
  `discountpercent` double NOT NULL DEFAULT '0',
  `standardcost` double NOT NULL DEFAULT '0',
  `show_on_inv_crds` tinyint(4) NOT NULL DEFAULT '1',
  `newqoh` double NOT NULL DEFAULT '0',
  `hidemovt` tinyint(4) NOT NULL DEFAULT '0',
  `narrative` text,
  PRIMARY KEY (`stkmoveno`),
  KEY `DebtorNo` (`debtorno`),
  KEY `LocCode` (`loccode`),
  KEY `Prd` (`prd`),
  KEY `StockID_2` (`stockid`),
  KEY `TranDate` (`trandate`),
  KEY `TransNo` (`transno`),
  KEY `Type` (`type`),
  KEY `Show_On_Inv_Crds` (`show_on_inv_crds`),
  KEY `Hide` (`hidemovt`),
  KEY `reference` (`reference`),
  CONSTRAINT `stockmoves_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockmoves_ibfk_2` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`),
  CONSTRAINT `stockmoves_ibfk_3` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `stockmoves_ibfk_4` FOREIGN KEY (`prd`) REFERENCES `periods` (`periodno`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockmovestaxes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockmovestaxes` (
  `stkmoveno` int(11) NOT NULL DEFAULT '0',
  `taxauthid` tinyint(4) NOT NULL DEFAULT '0',
  `taxrate` double NOT NULL DEFAULT '0',
  `taxontax` tinyint(4) NOT NULL DEFAULT '0',
  `taxcalculationorder` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`stkmoveno`,`taxauthid`),
  KEY `taxauthid` (`taxauthid`),
  KEY `calculationorder` (`taxcalculationorder`),
  CONSTRAINT `stockmovestaxes_ibfk_1` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`),
  CONSTRAINT `stockmovestaxes_ibfk_2` FOREIGN KEY (`stkmoveno`) REFERENCES `stockmoves` (`stkmoveno`),
  CONSTRAINT `stockmovestaxes_ibfk_3` FOREIGN KEY (`stkmoveno`) REFERENCES `stockmoves` (`stkmoveno`),
  CONSTRAINT `stockmovestaxes_ibfk_4` FOREIGN KEY (`stkmoveno`) REFERENCES `stockmoves` (`stkmoveno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockrequest`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockrequest` (
  `dispatchid` int(11) NOT NULL AUTO_INCREMENT,
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `departmentid` int(11) NOT NULL DEFAULT '0',
  `despatchdate` date NOT NULL DEFAULT '0000-00-00',
  `authorised` tinyint(4) NOT NULL DEFAULT '0',
  `closed` tinyint(4) NOT NULL DEFAULT '0',
  `narrative` text NOT NULL,
  PRIMARY KEY (`dispatchid`),
  KEY `loccode` (`loccode`),
  KEY `departmentid` (`departmentid`),
  KEY `loccode_2` (`loccode`),
  KEY `departmentid_2` (`departmentid`),
  CONSTRAINT `stockrequest_ibfk_1` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `stockrequest_ibfk_2` FOREIGN KEY (`departmentid`) REFERENCES `departments` (`departmentid`),
  CONSTRAINT `stockrequest_ibfk_3` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `stockrequest_ibfk_4` FOREIGN KEY (`departmentid`) REFERENCES `departments` (`departmentid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockrequestitems`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockrequestitems` (
  `dispatchitemsid` int(11) NOT NULL DEFAULT '0',
  `dispatchid` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantity` double NOT NULL DEFAULT '0',
  `qtydelivered` double NOT NULL DEFAULT '0',
  `decimalplaces` int(11) NOT NULL DEFAULT '0',
  `uom` varchar(20) NOT NULL DEFAULT '',
  `completed` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`dispatchitemsid`),
  KEY `dispatchid` (`dispatchid`),
  KEY `stockid` (`stockid`),
  KEY `dispatchid_2` (`dispatchid`),
  KEY `stockid_2` (`stockid`),
  CONSTRAINT `stockrequestitems_ibfk_1` FOREIGN KEY (`dispatchid`) REFERENCES `stockrequest` (`dispatchid`),
  CONSTRAINT `stockrequestitems_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockrequestitems_ibfk_3` FOREIGN KEY (`dispatchid`) REFERENCES `stockrequest` (`dispatchid`),
  CONSTRAINT `stockrequestitems_ibfk_4` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockserialitems`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockserialitems` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `loccode` varchar(5) NOT NULL DEFAULT '',
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `expirationdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `quantity` double NOT NULL DEFAULT '0',
  `qualitytext` text NOT NULL,
  PRIMARY KEY (`stockid`,`serialno`,`loccode`),
  KEY `StockID` (`stockid`),
  KEY `LocCode` (`loccode`),
  KEY `serialno` (`serialno`),
  CONSTRAINT `stockserialitems_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockserialitems_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stockserialmoves`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stockserialmoves` (
  `stkitmmoveno` int(11) NOT NULL AUTO_INCREMENT,
  `stockmoveno` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `serialno` varchar(30) NOT NULL DEFAULT '',
  `moveqty` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`stkitmmoveno`),
  KEY `StockMoveNo` (`stockmoveno`),
  KEY `StockID_SN` (`stockid`,`serialno`),
  KEY `serialno` (`serialno`),
  CONSTRAINT `stockserialmoves_ibfk_1` FOREIGN KEY (`stockmoveno`) REFERENCES `stockmoves` (`stkmoveno`),
  CONSTRAINT `stockserialmoves_ibfk_2` FOREIGN KEY (`stockid`, `serialno`) REFERENCES `stockserialitems` (`stockid`, `serialno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `suppallocs`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppallocs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `amt` double NOT NULL DEFAULT '0',
  `datealloc` date NOT NULL DEFAULT '0000-00-00',
  `transid_allocfrom` int(11) NOT NULL DEFAULT '0',
  `transid_allocto` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `TransID_AllocFrom` (`transid_allocfrom`),
  KEY `TransID_AllocTo` (`transid_allocto`),
  KEY `DateAlloc` (`datealloc`),
  CONSTRAINT `suppallocs_ibfk_1` FOREIGN KEY (`transid_allocfrom`) REFERENCES `supptrans` (`id`),
  CONSTRAINT `suppallocs_ibfk_2` FOREIGN KEY (`transid_allocto`) REFERENCES `supptrans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `suppliercontacts`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliercontacts` (
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `contact` varchar(30) NOT NULL DEFAULT '',
  `position` varchar(30) NOT NULL DEFAULT '',
  `tel` varchar(30) NOT NULL DEFAULT '',
  `fax` varchar(30) NOT NULL DEFAULT '',
  `mobile` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(55) NOT NULL DEFAULT '',
  `ordercontact` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`supplierid`,`contact`),
  KEY `Contact` (`contact`),
  KEY `SupplierID` (`supplierid`),
  CONSTRAINT `suppliercontacts_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `suppliers`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `suppname` varchar(40) NOT NULL DEFAULT '',
  `address1` varchar(40) NOT NULL DEFAULT '',
  `address2` varchar(40) NOT NULL DEFAULT '',
  `address3` varchar(40) NOT NULL DEFAULT '',
  `address4` varchar(50) NOT NULL DEFAULT '',
  `address5` varchar(20) NOT NULL DEFAULT '',
  `address6` varchar(40) NOT NULL DEFAULT '',
  `supptype` tinyint(4) NOT NULL DEFAULT '1',
  `lat` float(10,6) NOT NULL DEFAULT '0.000000',
  `lng` float(10,6) NOT NULL DEFAULT '0.000000',
  `currcode` char(3) NOT NULL DEFAULT '',
  `suppliersince` date NOT NULL DEFAULT '0000-00-00',
  `paymentterms` char(2) NOT NULL DEFAULT '',
  `lastpaid` double NOT NULL DEFAULT '0',
  `lastpaiddate` datetime DEFAULT NULL,
  `bankact` varchar(30) NOT NULL DEFAULT '',
  `bankref` varchar(12) NOT NULL DEFAULT '',
  `bankpartics` varchar(12) NOT NULL DEFAULT '',
  `remittance` tinyint(4) NOT NULL DEFAULT '1',
  `taxgroupid` tinyint(4) NOT NULL DEFAULT '1',
  `factorcompanyid` int(11) NOT NULL DEFAULT '1',
  `taxref` varchar(20) NOT NULL DEFAULT '',
  `phn` varchar(50) NOT NULL DEFAULT '',
  `port` varchar(200) NOT NULL DEFAULT '',
  `email` varchar(55) DEFAULT NULL,
  `fax` varchar(25) DEFAULT NULL,
  `telephone` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`supplierid`),
  KEY `CurrCode` (`currcode`),
  KEY `PaymentTerms` (`paymentterms`),
  KEY `SuppName` (`suppname`),
  KEY `taxgroupid` (`taxgroupid`),
  CONSTRAINT `suppliers_ibfk_1` FOREIGN KEY (`currcode`) REFERENCES `currencies` (`currabrev`),
  CONSTRAINT `suppliers_ibfk_2` FOREIGN KEY (`paymentterms`) REFERENCES `paymentterms` (`termsindicator`),
  CONSTRAINT `suppliers_ibfk_3` FOREIGN KEY (`taxgroupid`) REFERENCES `taxgroups` (`taxgroupid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `suppliertype`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliertype` (
  `typeid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `typename` varchar(100) NOT NULL,
  PRIMARY KEY (`typeid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `supptrans`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supptrans` (
  `transno` int(11) NOT NULL DEFAULT '0',
  `type` smallint(6) NOT NULL DEFAULT '0',
  `supplierno` varchar(10) NOT NULL DEFAULT '',
  `suppreference` varchar(20) NOT NULL DEFAULT '',
  `trandate` date NOT NULL DEFAULT '0000-00-00',
  `duedate` date NOT NULL DEFAULT '0000-00-00',
  `inputdate` datetime NOT NULL,
  `settled` tinyint(4) NOT NULL DEFAULT '0',
  `rate` double NOT NULL DEFAULT '1',
  `ovamount` double NOT NULL DEFAULT '0',
  `ovgst` double NOT NULL DEFAULT '0',
  `diffonexch` double NOT NULL DEFAULT '0',
  `alloc` double NOT NULL DEFAULT '0',
  `transtext` text,
  `hold` tinyint(4) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `TypeTransNo` (`transno`,`type`),
  KEY `DueDate` (`duedate`),
  KEY `Hold` (`hold`),
  KEY `SupplierNo` (`supplierno`),
  KEY `Settled` (`settled`),
  KEY `SupplierNo_2` (`supplierno`,`suppreference`),
  KEY `SuppReference` (`suppreference`),
  KEY `TranDate` (`trandate`),
  KEY `TransNo` (`transno`),
  KEY `Type` (`type`),
  CONSTRAINT `supptrans_ibfk_1` FOREIGN KEY (`type`) REFERENCES `systypes` (`typeid`),
  CONSTRAINT `supptrans_ibfk_2` FOREIGN KEY (`supplierno`) REFERENCES `suppliers` (`supplierid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `supptranstaxes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supptranstaxes` (
  `supptransid` int(11) NOT NULL DEFAULT '0',
  `taxauthid` tinyint(4) NOT NULL DEFAULT '0',
  `taxamount` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`supptransid`,`taxauthid`),
  KEY `taxauthid` (`taxauthid`),
  CONSTRAINT `supptranstaxes_ibfk_1` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`),
  CONSTRAINT `supptranstaxes_ibfk_2` FOREIGN KEY (`supptransid`) REFERENCES `supptrans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `systypes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `systypes` (
  `typeid` smallint(6) NOT NULL DEFAULT '0',
  `typename` char(50) NOT NULL DEFAULT '',
  `typeno` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`typeid`),
  KEY `TypeNo` (`typeno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tags`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `tagref` tinyint(4) NOT NULL AUTO_INCREMENT,
  `tagdescription` varchar(50) NOT NULL,
  PRIMARY KEY (`tagref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `taxauthorities`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxauthorities` (
  `taxid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `description` varchar(40) NOT NULL DEFAULT '',
  `taxglcode` varchar(20) NOT NULL DEFAULT '0',
  `purchtaxglaccount` varchar(20) NOT NULL DEFAULT '0',
  `bank` varchar(50) NOT NULL DEFAULT '',
  `bankacctype` varchar(20) NOT NULL DEFAULT '',
  `bankacc` varchar(50) NOT NULL DEFAULT '',
  `bankswift` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`taxid`),
  KEY `TaxGLCode` (`taxglcode`),
  KEY `PurchTaxGLAccount` (`purchtaxglaccount`),
  CONSTRAINT `taxauthorities_ibfk_1` FOREIGN KEY (`taxglcode`) REFERENCES `chartmaster` (`accountcode`),
  CONSTRAINT `taxauthorities_ibfk_2` FOREIGN KEY (`purchtaxglaccount`) REFERENCES `chartmaster` (`accountcode`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `taxauthrates`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxauthrates` (
  `taxauthority` tinyint(4) NOT NULL DEFAULT '1',
  `dispatchtaxprovince` tinyint(4) NOT NULL DEFAULT '1',
  `taxcatid` tinyint(4) NOT NULL DEFAULT '0',
  `taxrate` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`taxauthority`,`dispatchtaxprovince`,`taxcatid`),
  KEY `TaxAuthority` (`taxauthority`),
  KEY `dispatchtaxprovince` (`dispatchtaxprovince`),
  KEY `taxcatid` (`taxcatid`),
  CONSTRAINT `taxauthrates_ibfk_1` FOREIGN KEY (`taxauthority`) REFERENCES `taxauthorities` (`taxid`),
  CONSTRAINT `taxauthrates_ibfk_2` FOREIGN KEY (`taxcatid`) REFERENCES `taxcategories` (`taxcatid`),
  CONSTRAINT `taxauthrates_ibfk_3` FOREIGN KEY (`dispatchtaxprovince`) REFERENCES `taxprovinces` (`taxprovinceid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `taxcategories`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxcategories` (
  `taxcatid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `taxcatname` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`taxcatid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `taxgroups`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxgroups` (
  `taxgroupid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `taxgroupdescription` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`taxgroupid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `taxgrouptaxes`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxgrouptaxes` (
  `taxgroupid` tinyint(4) NOT NULL DEFAULT '0',
  `taxauthid` tinyint(4) NOT NULL DEFAULT '0',
  `calculationorder` tinyint(4) NOT NULL DEFAULT '0',
  `taxontax` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`taxgroupid`,`taxauthid`),
  KEY `taxgroupid` (`taxgroupid`),
  KEY `taxauthid` (`taxauthid`),
  CONSTRAINT `taxgrouptaxes_ibfk_1` FOREIGN KEY (`taxgroupid`) REFERENCES `taxgroups` (`taxgroupid`),
  CONSTRAINT `taxgrouptaxes_ibfk_2` FOREIGN KEY (`taxauthid`) REFERENCES `taxauthorities` (`taxid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `taxprovinces`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxprovinces` (
  `taxprovinceid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `taxprovincename` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`taxprovinceid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tenderitems`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tenderitems` (
  `tenderid` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `quantity` varchar(40) NOT NULL DEFAULT '',
  `units` varchar(20) NOT NULL DEFAULT 'each',
  PRIMARY KEY (`tenderid`,`stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tenders`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tenders` (
  `tenderid` int(11) NOT NULL DEFAULT '0',
  `location` varchar(5) NOT NULL DEFAULT '',
  `address1` varchar(40) NOT NULL DEFAULT '',
  `address2` varchar(40) NOT NULL DEFAULT '',
  `address3` varchar(40) NOT NULL DEFAULT '',
  `address4` varchar(40) NOT NULL DEFAULT '',
  `address5` varchar(20) NOT NULL DEFAULT '',
  `address6` varchar(15) NOT NULL DEFAULT '',
  `telephone` varchar(25) NOT NULL DEFAULT '',
  `closed` int(2) NOT NULL DEFAULT '0',
  `requiredbydate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`tenderid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tendersuppliers`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tendersuppliers` (
  `tenderid` int(11) NOT NULL DEFAULT '0',
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `email` varchar(40) NOT NULL DEFAULT '',
  `responded` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tenderid`,`supplierid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `unitsofmeasure`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unitsofmeasure` (
  `unitid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `unitname` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`unitid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `woitems`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `woitems` (
  `wo` int(11) NOT NULL,
  `stockid` char(20) NOT NULL DEFAULT '',
  `qtyreqd` double NOT NULL DEFAULT '1',
  `qtyrecd` double NOT NULL DEFAULT '0',
  `stdcost` double NOT NULL,
  `nextlotsnref` varchar(20) DEFAULT '',
  PRIMARY KEY (`wo`,`stockid`),
  KEY `stockid` (`stockid`),
  CONSTRAINT `woitems_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `woitems_ibfk_2` FOREIGN KEY (`wo`) REFERENCES `workorders` (`wo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `worequirements`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `worequirements` (
  `wo` int(11) NOT NULL,
  `parentstockid` varchar(20) NOT NULL,
  `stockid` varchar(20) NOT NULL,
  `qtypu` double NOT NULL DEFAULT '1',
  `stdcost` double NOT NULL DEFAULT '0',
  `autoissue` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`wo`,`parentstockid`,`stockid`),
  KEY `stockid` (`stockid`),
  KEY `worequirements_ibfk_3` (`parentstockid`),
  CONSTRAINT `worequirements_ibfk_1` FOREIGN KEY (`wo`) REFERENCES `workorders` (`wo`),
  CONSTRAINT `worequirements_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `worequirements_ibfk_3` FOREIGN KEY (`wo`, `parentstockid`) REFERENCES `woitems` (`wo`, `stockid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `workcentres`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workcentres` (
  `code` char(5) NOT NULL DEFAULT '',
  `location` char(5) NOT NULL DEFAULT '',
  `description` char(20) NOT NULL DEFAULT '',
  `capacity` double NOT NULL DEFAULT '1',
  `overheadperhour` decimal(10,0) NOT NULL DEFAULT '0',
  `overheadrecoveryact` int(11) NOT NULL DEFAULT '0',
  `setuphrs` decimal(10,0) NOT NULL DEFAULT '0',
  PRIMARY KEY (`code`),
  KEY `Description` (`description`),
  KEY `Location` (`location`),
  CONSTRAINT `workcentres_ibfk_1` FOREIGN KEY (`location`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `workorders`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workorders` (
  `wo` int(11) NOT NULL,
  `loccode` char(5) NOT NULL DEFAULT '',
  `requiredby` date NOT NULL DEFAULT '0000-00-00',
  `startdate` date NOT NULL DEFAULT '0000-00-00',
  `costissued` double NOT NULL DEFAULT '0',
  `closed` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`wo`),
  KEY `LocCode` (`loccode`),
  KEY `StartDate` (`startdate`),
  KEY `RequiredBy` (`requiredby`),
  CONSTRAINT `worksorders_ibfk_1` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `woserialnos`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `woserialnos` (
  `wo` int(11) NOT NULL,
  `stockid` varchar(20) NOT NULL,
  `serialno` varchar(30) NOT NULL,
  `quantity` double NOT NULL DEFAULT '1',
  `qualitytext` text NOT NULL,
  PRIMARY KEY (`wo`,`stockid`,`serialno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `www_users`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `www_users` (
  `userid` varchar(20) NOT NULL DEFAULT '',
  `password` text NOT NULL,
  `realname` varchar(35) NOT NULL DEFAULT '',
  `customerid` varchar(10) NOT NULL DEFAULT '',
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `salesman` char(3) NOT NULL,
  `phone` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(55) DEFAULT NULL,
  `defaultlocation` varchar(5) NOT NULL DEFAULT '',
  `fullaccess` int(11) NOT NULL DEFAULT '1',
  `cancreatetender` tinyint(1) NOT NULL DEFAULT '0',
  `lastvisitdate` datetime DEFAULT NULL,
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `pagesize` varchar(20) NOT NULL DEFAULT 'A4',
  `modulesallowed` varchar(40) NOT NULL DEFAULT '',
  `blocked` tinyint(4) NOT NULL DEFAULT '0',
  `displayrecordsmax` int(11) NOT NULL DEFAULT '0',
  `theme` varchar(30) NOT NULL DEFAULT 'fresh',
  `language` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `pdflanguage` tinyint(1) NOT NULL DEFAULT '0',
  `department` int(11) NOT NULL DEFAULT '0',
  `fontsize` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`userid`),
  KEY `CustomerID` (`customerid`),
  KEY `DefaultLocation` (`defaultlocation`),
  CONSTRAINT `www_users_ibfk_1` FOREIGN KEY (`defaultlocation`) REFERENCES `locations` (`loccode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-01-21 22:26:32
-- MySQL dump 10.14  Distrib 10.0.0-MariaDB, for debian-linux-gnu (i686)
--
-- Host: localhost    Database: kwamoja
-- ------------------------------------------------------
-- Server version	10.0.0-MariaDB-mariadb1~precise-log
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `accountgroups`
--

INSERT INTO `accountgroups` VALUES ('BBQs',5,1,6000,'Promotion Overheads');
INSERT INTO `accountgroups` VALUES ('Cost of Goods Sold',2,1,5000,'');
INSERT INTO `accountgroups` VALUES ('Current Assets',20,0,1000,'');
INSERT INTO `accountgroups` VALUES ('Equity',50,0,3000,'');
INSERT INTO `accountgroups` VALUES ('Fixed Assets',10,0,500,'');
INSERT INTO `accountgroups` VALUES ('Giveaways',5,1,6000,'Promotion Overheads');
INSERT INTO `accountgroups` VALUES ('Income Tax',5,1,9000,'');
INSERT INTO `accountgroups` VALUES ('Liabilities',30,0,2000,'');
INSERT INTO `accountgroups` VALUES ('Marketing Expenses',5,1,6000,'');
INSERT INTO `accountgroups` VALUES ('Operating Overheads',5,1,7000,'');
INSERT INTO `accountgroups` VALUES ('Other Revenue and Expenses',5,1,8000,'');
INSERT INTO `accountgroups` VALUES ('Outward Freight',2,1,5000,'Cost of Goods Sold');
INSERT INTO `accountgroups` VALUES ('Promotion Overheads',5,1,6000,'Marketing Expenses');
INSERT INTO `accountgroups` VALUES ('Revenue',1,1,4000,'');
INSERT INTO `accountgroups` VALUES ('Sales',1,1,10,'');

--
-- Dumping data for table `accountsection`
--

INSERT INTO `accountsection` VALUES (1,'Income');
INSERT INTO `accountsection` VALUES (2,'Cost Of Sales');
INSERT INTO `accountsection` VALUES (5,'Overheads');
INSERT INTO `accountsection` VALUES (10,'Fixed Assets');
INSERT INTO `accountsection` VALUES (20,'Amounts Receivable');
INSERT INTO `accountsection` VALUES (30,'Amounts Payable');
INSERT INTO `accountsection` VALUES (50,'Financed By');

--
-- Dumping data for table `areas`
--

INSERT INTO `areas` VALUES ('CE','Central Region');
INSERT INTO `areas` VALUES ('CO','Coastal Region');
INSERT INTO `areas` VALUES ('EA','Eastern');
INSERT INTO `areas` VALUES ('NB','Nairobi');
INSERT INTO `areas` VALUES ('NE','North Eastern');
INSERT INTO `areas` VALUES ('NY','Nyanza');
INSERT INTO `areas` VALUES ('RV','Rift Valley');
INSERT INTO `areas` VALUES ('WE','Western');

--
-- Dumping data for table `assetmanager`
--


--
-- Dumping data for table `audittrail`
--

INSERT INTO `audittrail` VALUES ('2012-12-08 20:45:12','admin','UPDATE www_users SET lastvisitdate=\'2012-12-08 20:45:12\'\n							WHERE www_users.userid=\'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-08 20:46:12','admin','UPDATE config\n				SET confvalue=\'2012-12-08\'\n				WHERE confname=\'DB_Maintenance_LastRun\'');
INSERT INTO `audittrail` VALUES ('2012-12-08 20:49:31','admin','UPDATE www_users SET lastvisitdate=\'2012-12-08 20:49:31\'\n							WHERE www_users.userid=\'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-08 20:50:38','admin','UPDATE www_users SET lastvisitdate=\'2012-12-08 20:50:38\'\n							WHERE www_users.userid=\'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-08 20:51:53','admin','UPDATE companies SET coyname=\'kwamojademo\',\n									companynumber = \'\',\n									gstno=\'not entered yet\',\n									regoffice1=\'123 Web Way\',\n									regoffice2=\'PO Box 123\',\n									regoffice3=\'Queen Street\',\n									regoffice4=\'Melbourne\',\n									regoffice5=\'Victoria 3043\',\n									regoffice6=\'Australia\',\n									telephone=\'+61 3 4567 8901\',\n									fax=\'+61 3 4567 8902\',\n									email=\'kwamoja@kwamojademo.com\',\n									currencydefault=\'GBP\',\n									debtorsact=\'1100\',\n									pytdiscountact=\'4900\',\n									creditorsact=\'2100\',\n									payrollact=\'2400\',\n									grnact=\'2150\',\n									exchangediffact=\'4200\',\n									purchasesexchangediffact=\'5200\',\n									retainedearnings=\'3500\',\n									gllink_debtors=\'1\',\n									gllink_creditors=\'1\',\n									gllink_stock=\'1\',\n									freightact=\'5600\'\n								WHERE coycode=1');
INSERT INTO `audittrail` VALUES ('2012-12-08 20:51:54','admin','UPDATE currencies SET rate=rate/0.007692308');
INSERT INTO `audittrail` VALUES ('2012-12-08 20:52:05','admin','UPDATE config SET confvalue = \'\' WHERE confname = \'DefaultPriceList\'');
INSERT INTO `audittrail` VALUES ('2012-12-08 20:52:05','admin','UPDATE config SET confvalue = \'GBP\' WHERE confname = \'CountryOfOperation\'');
INSERT INTO `audittrail` VALUES ('2012-12-08 20:52:05','admin','UPDATE config SET confvalue = \'companies/kwamoja/EDI_Sent\' WHERE confname = \'part_pics_dir\'');
INSERT INTO `audittrail` VALUES ('2012-12-08 20:52:05','admin','UPDATE config SET confvalue = \'companies/kwamoja/EDI_Sent\' WHERE confname = \'reports_dir\'');
INSERT INTO `audittrail` VALUES ('2012-12-08 20:52:05','admin','UPDATE config SET confvalue = \'\' WHERE confname = \'ProhibitPostingsBefore\'');
INSERT INTO `audittrail` VALUES ('2012-12-09 00:42:05','admin','INSERT INTO periods VALUES (0,\'2012-12-31\')');
INSERT INTO `audittrail` VALUES ('2012-12-09 00:42:05','admin','INSERT INTO periods VALUES (1,\'2013-01-31\')');
INSERT INTO `audittrail` VALUES ('2012-12-09 04:45:22','admin','INSERT INTO chartdetails (accountcode, period)\n					SELECT chartmaster.accountcode, periods.periodno\n					FROM (chartmaster CROSS JOIN periods)\n					LEFT JOIN chartdetails ON chartmaster.accountcode = chartdetails.accountcode\n					AND periods.periodno = chartdetails.period\n					WHERE (periods.periodno BETWEEN \'0\' AND \'1\')\n					AND chartdetails.accountcode IS NULL');
INSERT INTO `audittrail` VALUES ('2012-12-10 13:49:26','admin','INSERT INTO stockcategory (categoryid,\n											stocktype,\n											categorydescription,\n											stockact,\n											adjglact,\n											issueglact,\n											purchpricevaract,\n											materialuseagevarac,\n											wipact)\n										VALUES (\n											\'FOOD\',\n											\'F\',\n											\'Food items for sale\',\n											\'1460\',\n											\'5700\',\n											\'5700\',\n											\'5000\',\n											\'5000\',\n											\'1440\')');
INSERT INTO `audittrail` VALUES ('2012-12-10 13:50:05','admin','INSERT INTO stockcategory (categoryid,\n											stocktype,\n											categorydescription,\n											stockact,\n											adjglact,\n											issueglact,\n											purchpricevaract,\n											materialuseagevarac,\n											wipact)\n										VALUES (\n											\'INGR\',\n											\'M\',\n											\'Food ingrediants\',\n											\'1420\',\n											\'5700\',\n											\'5700\',\n											\'5000\',\n											\'5000\',\n											\'1440\')');
INSERT INTO `audittrail` VALUES ('2012-12-10 13:50:20','admin','DELETE FROM locations WHERE loccode=\'TOR\'');
INSERT INTO `audittrail` VALUES ('2012-12-10 13:51:09','admin','INSERT INTO locations (loccode,\n										locationname,\n										deladd1,\n										deladd2,\n										deladd3,\n										deladd4,\n										deladd5,\n										deladd6,\n										tel,\n										fax,\n										email,\n										contact,\n										taxprovinceid,\n										cashsalecustomer,\n										cashsalebranch,\n										managed,\n										internalrequest)\n						VALUES (\'MSA\',\n								\'Mombasa Main Warehouse\',\n								\' \',\n								\'\',\n								\'\',\n								\'\',\n								\'\',\n								\'\',\n								\'\',\n								\'\',\n								\'\',\n								\'\',\n								\'1\',\n								\'\',\n								\'\',\n								\'0\',\n								\'0\')');
INSERT INTO `audittrail` VALUES ('2012-12-10 13:51:47','admin','UPDATE www_users SET realname=\'Demonstration user\',\n						customerid=\'\',\n						phone=\'\',\n						email=\'info@kwamoja.com\',\n						password=\'f0f77a7f88e7c1e93ab4e316b4574c7843b00ea4\',\n						branchcode=\'\',\n						supplierid=\'\',\n						salesman=\'\',\n						pagesize=\'A4\',\n						fullaccess=\'8\',\n						cancreatetender=\'0\',\n						theme=\'aguapop\',\n						language =\'en_GB.utf8\',\n						defaultlocation=\'MSA\',\n						modulesallowed=\'1,1,1,1,1,1,1,1,1,1,1,\',\n						blocked=\'0\',\n						pdflanguage=\'0\',\n						department=\'0\'\n					WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-10 13:51:58','admin','DELETE FROM locations WHERE loccode=\'MEL\'');
INSERT INTO `audittrail` VALUES ('2012-12-10 14:12:28','admin','UPDATE companies SET coyname=\'KwaMoja Demonstration Company Ltd\',\n									companynumber = \'KE1234567890\',\n									gstno=\'not entered yet\',\n									regoffice1=\'123 Web Way\',\n									regoffice2=\'PO Box 123\',\n									regoffice3=\'Queen Street\',\n									regoffice4=\'Melbourne\',\n									regoffice5=\'Victoria 3043\',\n									regoffice6=\'Australia\',\n									telephone=\'+61 3 4567 8901\',\n									fax=\'+61 3 4567 8902\',\n									email=\'kwamoja@kwamojademo.com\',\n									currencydefault=\'KES\',\n									debtorsact=\'1100\',\n									pytdiscountact=\'4900\',\n									creditorsact=\'2100\',\n									payrollact=\'2400\',\n									grnact=\'2150\',\n									exchangediffact=\'4200\',\n									purchasesexchangediffact=\'5200\',\n									retainedearnings=\'3500\',\n									gllink_debtors=\'1\',\n									gllink_creditors=\'1\',\n									gllink_stock=\'1\',\n									freightact=\'5600\'\n								WHERE coycode=1');
INSERT INTO `audittrail` VALUES ('2012-12-10 14:12:28','admin','UPDATE currencies SET rate=rate/129.9999948000002');
INSERT INTO `audittrail` VALUES ('2012-12-10 14:13:36','admin','UPDATE companies SET coyname=\'KwaMoja Demonstration Company Ltd\',\n									companynumber = \'KE1234567890\',\n									gstno=\'not entered yet\',\n									regoffice1=\'Plot 8172\',\n									regoffice2=\'Kisanjani Road\',\n									regoffice3=\'Ganjoni\',\n									regoffice4=\'Mombasa\',\n									regoffice5=\'Kenya\',\n									regoffice6=\'East Africa\',\n									telephone=\'+61 3 4567 8901\',\n									fax=\'+61 3 4567 8902\',\n									email=\'kwamoja@kwamojademo.com\',\n									currencydefault=\'KES\',\n									debtorsact=\'1100\',\n									pytdiscountact=\'4900\',\n									creditorsact=\'2100\',\n									payrollact=\'2400\',\n									grnact=\'2150\',\n									exchangediffact=\'4200\',\n									purchasesexchangediffact=\'5200\',\n									retainedearnings=\'3500\',\n									gllink_debtors=\'1\',\n									gllink_creditors=\'1\',\n									gllink_stock=\'1\',\n									freightact=\'5600\'\n								WHERE coycode=1');
INSERT INTO `audittrail` VALUES ('2012-12-10 14:14:12','admin','DELETE FROM currencies WHERE currabrev=\'GBP\'');
INSERT INTO `audittrail` VALUES ('2012-12-10 14:14:43','admin','INSERT INTO suppliertype\n						(typename)\n					VALUES (\'FOOD\')');
INSERT INTO `audittrail` VALUES ('2012-12-10 14:17:17','admin','INSERT INTO suppliers (supplierid,\n										suppname,\n										address1,\n										address2,\n										address3,\n										address4,\n										address5,\n										address6,\n										telephone,\n										fax,\n										email,\n										supptype,\n										currcode,\n										suppliersince,\n										paymentterms,\n										bankpartics,\n										bankref,\n										bankact,\n										remittance,\n										taxgroupid,\n										factorcompanyid,\n										lat,\n										lng,\n										taxref)\n								 VALUES (\'VOI001\',\n								 	\'Voi Fruit and Vegetable\',\n									\'PO Box 9999\',\n									\'\',\n									\'Voi\',\n									\'\',\n									\'\',\n									\'Kenya\',\n									\'\',\n									\'\',\n									\'\',\n									\'1\',\n									\'KES\',\n									\'2012-12-10\',\n									\'20\',\n									\'\',\n									\'0\',\n									\'\',\n									\'0\',\n									\'1\',\n									\'0\',\n									\'0\',\n									\'0\',\n									\'\')');
INSERT INTO `audittrail` VALUES ('2012-12-10 14:17:47','admin','INSERT INTO purchorderauth ( userid,\n						currabrev,\n						cancreate,\n						offhold,\n						authlevel)\n					VALUES( \'admin\',\n						\'KES\',\n						\'0\',\n						\'0\',\n						\'1000000000\')');
INSERT INTO `audittrail` VALUES ('2012-12-10 14:19:33','admin','INSERT INTO stockmaster (stockid,\n												description,\n												longdescription,\n												categoryid,\n												units,\n												mbflag,\n												eoq,\n												discontinued,\n												controlled,\n												serialised,\n												perishable,\n												volume,\n												kgs,\n												barcode,\n												discountcategory,\n												taxcatid,\n												decimalplaces,\n												shrinkfactor,\n												pansize)\n							VALUES (\'CHAPATI\',\n								\'Chapati\',\n								\'Chapati\',\n								\'FOOD\',\n								\'each\',\n								\'M\',\n								\'0\',\n								\'0\',\n								\'0\',\n								\'0\',\n								\'1\',\n								\'0\',\n								\'0\',\n								\'\',\n								\'\',\n								\'1\',\n								\'0\',\n								\'0\',\n								\'0\')');
INSERT INTO `audittrail` VALUES ('2012-12-10 14:19:33','admin','INSERT INTO locstock (loccode,\n													stockid)\n										SELECT locations.loccode,\n										\'CHAPATI\'\n										FROM locations');
INSERT INTO `audittrail` VALUES ('2012-12-10 14:19:45','admin','UPDATE stockmaster SET	materialcost=\'0.0000\',\n										labourcost=\'10.00\',\n										overheadcost=\'0.00\',\n										lastcost=\'0\',\n										lastcostupdate =\'2012-12-10\'\n								WHERE stockid=\'CHAPATI\'');
INSERT INTO `audittrail` VALUES ('2012-12-10 14:21:11','admin','INSERT INTO purchorders ( orderno,\n											supplierno,\n											comments,\n											orddate,\n											rate,\n											initiator,\n											requisitionno,\n											intostocklocation,\n											deladd1,\n											deladd2,\n											deladd3,\n											deladd4,\n											deladd5,\n											deladd6,\n											tel,\n											suppdeladdress1,\n											suppdeladdress2,\n											suppdeladdress3,\n											suppdeladdress4,\n											suppdeladdress5,\n											suppdeladdress6,\n											suppliercontact,\n											supptel,\n											contact,\n											version,\n											revised,\n											deliveryby,\n											status,\n											stat_comment,\n											deliverydate,\n											paymentterms,\n											allowprint)\n							VALUES(	\'1\',\n									\'VOI001\',\n									\'\',\n									\'2012-12-10\',\n									\'1\',\n									\'admin\',\n									\'\',\n									\'MSA\',\n									\'Kisanjani road \',\n									\'\',\n									\'\',\n									\'\',\n									\'\',\n									\'\',\n									\'\',\n									\'PO Box 9999\',\n									\'\',\n									\'Voi\',\n									\'\',\n									\'\',\n									\'Kenya\',\n									\'\',\n									\'\',\n									\'\',\n									\'1\',\n									\'2012-12-10\',\n									\'1\',\n									\'Authorised\',\n									\'10/12/2012 - Order Created and Authorised by &lt;a href=&quot;mailto:info@kwamoja.com&quot;&gt;Demonstration user&lt;/a&gt;&lt;br /&gt;&lt;br /&gt;\',\n									\'2012-12-10\',\n									\'20\',\n									\'1\' )');
INSERT INTO `audittrail` VALUES ('2012-12-10 14:21:11','admin','INSERT INTO purchorderdetails (orderno,\n														itemcode,\n														deliverydate,\n														itemdescription,\n														glcode,\n														unitprice,\n														quantityord,\n														shiptref,\n														jobref,\n														suppliersunit,\n														suppliers_partno,\n														assetid,\n														conversionfactor )\n									VALUES (\'1\',\n											\'CHAPATI\',\n											\'2012-12-10\',\n											\'Chapati\',\n											\'1460\',\n											\'10\',\n											\'10\',\n											\'0\',\n											\'0\',\n											\'each\',\n											\'\',\n											\'0\',\n											\'1\')');
INSERT INTO `audittrail` VALUES ('2012-12-10 14:24:25','admin','UPDATE purchorders	SET	allowprint =  0,\n										dateprinted  = \'2012-12-10\',\n										status = \'Printed\',\n										stat_comment = \'10/12/2012 - Printed by &lt;a href=&quot;mailto:info@kwamoja.com&quot;&gt;Demonstration user&lt;/a&gt;&lt;br /&gt;10/12/2012 - Order Created and Authorised by &lt;a href=&quot;mailto:info@kwamoja.com&quot;&gt;Demonstration user&lt;/a&gt;&lt;br /&gt;&lt;br /&gt;\'\n				WHERE purchorders.orderno = \'1\'');
INSERT INTO `audittrail` VALUES ('2012-12-11 10:27:27','admin','INSERT INTO debtortype\n						(typename)\n					VALUES (\'Private Individual\')');
INSERT INTO `audittrail` VALUES ('2012-12-11 10:27:33','admin','INSERT INTO debtortype\n						(typename)\n					VALUES (\'NGO\')');
INSERT INTO `audittrail` VALUES ('2012-12-11 10:30:37','admin','INSERT INTO areas (areacode,\n									areadescription\n								) VALUES (\n									\'CO\',\n									\'Coastal Region\'\n								)');
INSERT INTO `audittrail` VALUES ('2012-12-11 10:30:50','admin','INSERT INTO areas (areacode,\n									areadescription\n								) VALUES (\n									\'CE\',\n									\'Central Region\'\n								)');
INSERT INTO `audittrail` VALUES ('2012-12-11 10:31:03','admin','INSERT INTO areas (areacode,\n									areadescription\n								) VALUES (\n									\'EA\',\n									\'Eastern\'\n								)');
INSERT INTO `audittrail` VALUES ('2012-12-11 10:31:18','admin','INSERT INTO areas (areacode,\n									areadescription\n								) VALUES (\n									\'NB\',\n									\'Nairobi\'\n								)');
INSERT INTO `audittrail` VALUES ('2012-12-11 10:31:35','admin','INSERT INTO areas (areacode,\n									areadescription\n								) VALUES (\n									\'NE\',\n									\'North Eastern\'\n								)');
INSERT INTO `audittrail` VALUES ('2012-12-11 10:31:49','admin','INSERT INTO areas (areacode,\n									areadescription\n								) VALUES (\n									\'NY\',\n									\'Nyanza\'\n								)');
INSERT INTO `audittrail` VALUES ('2012-12-11 10:32:02','admin','INSERT INTO areas (areacode,\n									areadescription\n								) VALUES (\n									\'RV\',\n									\'Rift Valley\'\n								)');
INSERT INTO `audittrail` VALUES ('2012-12-11 10:32:13','admin','INSERT INTO areas (areacode,\n									areadescription\n								) VALUES (\n									\'WE\',\n									\'Western\'\n								)');
INSERT INTO `audittrail` VALUES ('2012-12-11 10:33:13','admin','INSERT INTO salestypes (typeabbrev,\n											sales_type)\n							VALUES (\'EA\',\n									\'East African Community\')');
INSERT INTO `audittrail` VALUES ('2012-12-11 10:33:13','admin','UPDATE config\n					SET confvalue=\'EA\'\n					WHERE confname=\'DefaultPriceList\'');
INSERT INTO `audittrail` VALUES ('2012-12-11 11:44:40','admin','INSERT INTO debtorsmaster (\n							debtorno,\n							name,\n							address1,\n							address2,\n							address3,\n							address4,\n							address5,\n							address6,\n							currcode,\n							clientsince,\n							holdreason,\n							paymentterms,\n							discount,\n							discountcode,\n							pymtdiscount,\n							creditlimit,\n							salestype,\n							invaddrbranch,\n							taxref,\n							customerpoline,\n							typeid)\n				VALUES (\'COA001\',\n					\'Coastal Hotels Ltd\',\n					\'Mt Kenya Road\',\n					\'\',\n					\'\',\n					\'Mombasa\',\n					\'\',\n					\'Kenya\',\n					\'KES\',\n					\'2012-12-11\',\n					\'1\',\n					\'20\',\n					\'0\',\n					\'\',\n					\'0\',\n					\'1000\',\n					\'EA\',\n					\'0\',\n					\'\',\n					\'0\',\n					\'1\'\n					)');
INSERT INTO `audittrail` VALUES ('2012-12-11 11:47:19','admin','INSERT INTO salesman (salesmancode,\n						salesmanname,\n						commissionrate1,\n						commissionrate2,\n						breakpoint,\n						smantel,\n						smanfax,\n						current)\n				VALUES (\'IN\',\n					\'Internet bookings\',\n					\'0\',\n					\'0\',\n					\'0\',\n					\'\',\n					\'\',\n					\'1\'\n					)');
INSERT INTO `audittrail` VALUES ('2012-12-11 11:47:37','admin','INSERT INTO debtortype\n						(typename)\n					VALUES (\'Corporate\')');
INSERT INTO `audittrail` VALUES ('2012-12-11 11:54:48','admin','UPDATE debtorsmaster SET\n					name=\'Coastal Hotels Ltd\',\n					address1=\'Mt Kenya Road\',\n					address2=\'\',\n					address3=\'\',\n					address4=\'Mombasa\',\n					address5=\'\',\n					address6=\'Kenya\',\n					currcode=\'KES\',\n					clientsince=\'2012-12-11\',\n					holdreason=\'1\',\n					paymentterms=\'20\',\n					discount=\'0\',\n					discountcode=\'\',\n					pymtdiscount=\'0\',\n					creditlimit=\'1000\',\n					salestype = \'EA\',\n					invaddrbranch=\'0\',\n					taxref=\'\',\n					customerpoline=\'0\',\n					typeid=\'3\'\n				  WHERE debtorno = \'COA001\'');
INSERT INTO `audittrail` VALUES ('2012-12-11 11:56:45','admin','INSERT INTO custbranch (branchcode,\n						debtorno,\n						brname,\n						braddress1,\n						braddress2,\n						braddress3,\n						braddress4,\n						braddress5,\n						braddress6,\n						lat,\n						lng,\n 						specialinstructions,\n						estdeliverydays,\n						fwddate,\n						salesman,\n						phoneno,\n						faxno,\n						contactname,\n						area,\n						email,\n						taxgroupid,\n						defaultlocation,\n						brpostaddr1,\n						brpostaddr2,\n						brpostaddr3,\n						brpostaddr4,\n						disabletrans,\n						defaultshipvia,\n						custbranchcode,\n						deliverblind)\n				VALUES (\'COA001\',\n					\'COA001\',\n					\'Coastal Hotels Ltd\',\n					\'Mt Kenya Road\',\n					\'\',\n					\'\',\n					\'Mombasa\',\n					\'\',\n					\'Kenya\',\n					\'0\',\n					\'0\',\n					\'\',\n					\'0\',\n					\'0\',\n					\'IN\',\n					\'\',\n					\'\',\n					\'\',\n					\'CE\',\n					\'\',\n					\'1\',\n					\'MSA\',\n					\'\',\n					\'\',\n					\'\',\n					\'\',\n					\'0\',\n					\'1\',\n					\'\',\n					\'1\'\n					)');
INSERT INTO `audittrail` VALUES ('2012-12-11 12:01:26','admin','INSERT INTO www_users (userid,\n										realname,\n										customerid,\n										branchcode,\n										password,\n										phone,\n										email,\n										pagesize,\n										fullaccess,\n										defaultlocation,\n										modulesallowed,\n										displayrecordsmax,\n										theme,\n										language)\n									VALUES (\'coastal\',\n											\'Coastal Hotelsd Ltd\',\n											\'COA001\',\n											\'COA001\',\n											\'f0f77a7f88e7c1e93ab4e316b4574c7843b00ea4\',\n											\'\',\n											\'\',\n											\'A4\',\n											\'7\',\n											\'MSA\',\n											\'1,1,0,0,0,0,0,0\',\n											\'50\',\n											\'aguapop\',\n											\'en_GB.utf8\')');
INSERT INTO `audittrail` VALUES ('2012-12-12 02:30:33','admin','INSERT INTO debtorsmaster (\n							debtorno,\n							name,\n							address1,\n							address2,\n							address3,\n							address4,\n							address5,\n							address6,\n							currcode,\n							clientsince,\n							holdreason,\n							paymentterms,\n							discount,\n							discountcode,\n							pymtdiscount,\n							creditlimit,\n							salestype,\n							invaddrbranch,\n							taxref,\n							customerpoline,\n							typeid)\n				VALUES (\'FAT001\',\n					\'Fatuma Mumbi\',\n					\'PO Box 12345\',\n					\'\',\n					\'\',\n					\'\',\n					\'Nairobi\',\n					\'Kenya\',\n					\'KES\',\n					\'2012-12-12\',\n					\'1\',\n					\'20\',\n					\'0\',\n					\'\',\n					\'0\',\n					\'1000\',\n					\'EA\',\n					\'0\',\n					\'\',\n					\'0\',\n					\'1\'\n					)');
INSERT INTO `audittrail` VALUES ('2012-12-12 02:30:38','admin','INSERT INTO custbranch (branchcode,\n						debtorno,\n						brname,\n						braddress1,\n						braddress2,\n						braddress3,\n						braddress4,\n						braddress5,\n						braddress6,\n						lat,\n						lng,\n 						specialinstructions,\n						estdeliverydays,\n						fwddate,\n						salesman,\n						phoneno,\n						faxno,\n						contactname,\n						area,\n						email,\n						taxgroupid,\n						defaultlocation,\n						brpostaddr1,\n						brpostaddr2,\n						brpostaddr3,\n						brpostaddr4,\n						disabletrans,\n						defaultshipvia,\n						custbranchcode,\n						deliverblind)\n				VALUES (\'FAT001\',\n					\'FAT001\',\n					\'Fatuma Mumbi\',\n					\'PO Box 12345\',\n					\'\',\n					\'\',\n					\'\',\n					\'Nairobi\',\n					\'Kenya\',\n					\'0\',\n					\'0\',\n					\'\',\n					\'0\',\n					\'0\',\n					\'IN\',\n					\'\',\n					\'\',\n					\'\',\n					\'CE\',\n					\'\',\n					\'1\',\n					\'MSA\',\n					\'\',\n					\'\',\n					\'\',\n					\'\',\n					\'0\',\n					\'1\',\n					\'\',\n					\'1\'\n					)');
INSERT INTO `audittrail` VALUES ('2012-12-12 17:14:59','coastal','UPDATE www_users SET lastvisitdate=\'2012-12-12 17:14:59\'\n							WHERE www_users.userid=\'coastal\'');
INSERT INTO `audittrail` VALUES ('2012-12-13 16:26:18','admin','UPDATE www_users SET lastvisitdate=\'2012-12-13 16:26:18\'\n							WHERE www_users.userid=\'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-13 16:26:51','admin','UPDATE www_users SET lastvisitdate=\'2012-12-13 16:26:51\'\n							WHERE www_users.userid=\'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-13 16:27:07','admin','UPDATE www_users SET lastvisitdate=\'2012-12-13 16:27:07\'\n							WHERE www_users.userid=\'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-13 16:27:20','admin','UPDATE www_users SET lastvisitdate=\'2012-12-13 16:27:19\'\n							WHERE www_users.userid=\'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-13 16:29:36','admin','UPDATE www_users SET lastvisitdate=\'2012-12-13 16:29:36\'\n							WHERE www_users.userid=\'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-13 16:29:50','admin','UPDATE www_users SET lastvisitdate=\'2012-12-13 16:29:49\'\n							WHERE www_users.userid=\'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-13 17:00:22','admin','UPDATE www_users SET realname=\'Demonstration user\',\n						customerid=\'\',\n						phone=\'\',\n						email=\'info@kwamoja.com\',\n						\n						branchcode=\'\',\n						supplierid=\'\',\n						salesman=\'\',\n						pagesize=\'A4\',\n						fullaccess=\'8\',\n						cancreatetender=\'0\',\n						theme=\'aguapop\',\n						language =\'en_GB.utf8\',\n						defaultlocation=\'MSA\',\n						modulesallowed=\'1,1,1,1,1,1,1,1,1,1,1,\',\n						blocked=\'0\',\n						pdflanguage=\'0\',\n						department=\'0\',\n						fontsize=\'1\'\n					WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-13 17:02:49','admin','UPDATE www_users SET realname=\'Demonstration user\',\n						customerid=\'\',\n						phone=\'\',\n						email=\'info@kwamoja.com\',\n						\n						branchcode=\'\',\n						supplierid=\'\',\n						salesman=\'\',\n						pagesize=\'A4\',\n						fullaccess=\'8\',\n						cancreatetender=\'0\',\n						theme=\'aguapop\',\n						language =\'en_GB.utf8\',\n						defaultlocation=\'MSA\',\n						modulesallowed=\'1,1,1,1,1,1,1,1,1,1,1,\',\n						blocked=\'0\',\n						pdflanguage=\'0\',\n						department=\'0\',\n						fontsize=\'0\'\n					WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-13 17:05:09','admin','UPDATE www_users SET realname=\'Demonstration user\',\n						customerid=\'\',\n						phone=\'\',\n						email=\'info@kwamoja.com\',\n						\n						branchcode=\'\',\n						supplierid=\'\',\n						salesman=\'\',\n						pagesize=\'A4\',\n						fullaccess=\'8\',\n						cancreatetender=\'0\',\n						theme=\'aguapop\',\n						language =\'en_GB.utf8\',\n						defaultlocation=\'MSA\',\n						modulesallowed=\'1,1,1,1,1,1,1,1,1,1,1,\',\n						blocked=\'0\',\n						pdflanguage=\'0\',\n						department=\'0\',\n						fontsize=\'1\'\n					WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-13 17:05:33','admin','UPDATE www_users SET realname=\'Demonstration user\',\n						customerid=\'\',\n						phone=\'\',\n						email=\'info@kwamoja.com\',\n						\n						branchcode=\'\',\n						supplierid=\'\',\n						salesman=\'\',\n						pagesize=\'A4\',\n						fullaccess=\'8\',\n						cancreatetender=\'0\',\n						theme=\'aguapop\',\n						language =\'en_GB.utf8\',\n						defaultlocation=\'MSA\',\n						modulesallowed=\'1,1,1,1,1,1,1,1,1,1,1,\',\n						blocked=\'0\',\n						pdflanguage=\'0\',\n						department=\'0\',\n						fontsize=\'2\'\n					WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-13 17:05:48','admin','UPDATE www_users SET realname=\'Demonstration user\',\n						customerid=\'\',\n						phone=\'\',\n						email=\'info@kwamoja.com\',\n						\n						branchcode=\'\',\n						supplierid=\'\',\n						salesman=\'\',\n						pagesize=\'A4\',\n						fullaccess=\'8\',\n						cancreatetender=\'0\',\n						theme=\'aguapop\',\n						language =\'en_GB.utf8\',\n						defaultlocation=\'MSA\',\n						modulesallowed=\'1,1,1,1,1,1,1,1,1,1,1,\',\n						blocked=\'0\',\n						pdflanguage=\'0\',\n						department=\'0\',\n						fontsize=\'1\'\n					WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-13 17:07:26','admin','UPDATE www_users SET realname=\'Demonstration user\',\n						customerid=\'\',\n						phone=\'\',\n						email=\'info@kwamoja.com\',\n						\n						branchcode=\'\',\n						supplierid=\'\',\n						salesman=\'\',\n						pagesize=\'A4\',\n						fullaccess=\'8\',\n						cancreatetender=\'0\',\n						theme=\'aguapop\',\n						language =\'en_GB.utf8\',\n						defaultlocation=\'MSA\',\n						modulesallowed=\'1,1,1,1,1,1,1,1,1,1,1,\',\n						blocked=\'0\',\n						pdflanguage=\'0\',\n						department=\'0\',\n						fontsize=\'2\'\n					WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-13 17:07:38','admin','UPDATE www_users SET realname=\'Demonstration user\',\n						customerid=\'\',\n						phone=\'\',\n						email=\'info@kwamoja.com\',\n						\n						branchcode=\'\',\n						supplierid=\'\',\n						salesman=\'\',\n						pagesize=\'A4\',\n						fullaccess=\'8\',\n						cancreatetender=\'0\',\n						theme=\'aguapop\',\n						language =\'en_GB.utf8\',\n						defaultlocation=\'MSA\',\n						modulesallowed=\'1,1,1,1,1,1,1,1,1,1,1,\',\n						blocked=\'0\',\n						pdflanguage=\'0\',\n						department=\'0\',\n						fontsize=\'1\'\n					WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-13 17:10:02','admin','UPDATE www_users SET realname=\'Demonstration user\',\n						customerid=\'\',\n						phone=\'\',\n						email=\'info@kwamoja.com\',\n						\n						branchcode=\'\',\n						supplierid=\'\',\n						salesman=\'\',\n						pagesize=\'A4\',\n						fullaccess=\'8\',\n						cancreatetender=\'0\',\n						theme=\'aguapop\',\n						language =\'en_GB.utf8\',\n						defaultlocation=\'MSA\',\n						modulesallowed=\'1,1,1,1,1,1,1,1,1,1,1,\',\n						blocked=\'0\',\n						pdflanguage=\'0\',\n						department=\'0\',\n						fontsize=\'0\'\n					WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-13 17:11:57','admin','UPDATE www_users SET realname=\'Demonstration user\',\n						customerid=\'\',\n						phone=\'\',\n						email=\'info@kwamoja.com\',\n						\n						branchcode=\'\',\n						supplierid=\'\',\n						salesman=\'\',\n						pagesize=\'A4\',\n						fullaccess=\'8\',\n						cancreatetender=\'0\',\n						theme=\'aguapop\',\n						language =\'en_GB.utf8\',\n						defaultlocation=\'MSA\',\n						modulesallowed=\'1,1,1,1,1,1,1,1,1,1,1,\',\n						blocked=\'0\',\n						pdflanguage=\'0\',\n						department=\'0\',\n						fontsize=\'2\'\n					WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-13 17:12:29','admin','UPDATE www_users SET realname=\'Demonstration user\',\n						customerid=\'\',\n						phone=\'\',\n						email=\'info@kwamoja.com\',\n						\n						branchcode=\'\',\n						supplierid=\'\',\n						salesman=\'\',\n						pagesize=\'A4\',\n						fullaccess=\'8\',\n						cancreatetender=\'0\',\n						theme=\'aguapop\',\n						language =\'en_GB.utf8\',\n						defaultlocation=\'MSA\',\n						modulesallowed=\'1,1,1,1,1,1,1,1,1,1,1,\',\n						blocked=\'0\',\n						pdflanguage=\'0\',\n						department=\'0\',\n						fontsize=\'1\'\n					WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-13 17:12:46','admin','UPDATE www_users SET realname=\'Demonstration user\',\n						customerid=\'\',\n						phone=\'\',\n						email=\'info@kwamoja.com\',\n						\n						branchcode=\'\',\n						supplierid=\'\',\n						salesman=\'\',\n						pagesize=\'A4\',\n						fullaccess=\'8\',\n						cancreatetender=\'0\',\n						theme=\'aguapop\',\n						language =\'en_GB.utf8\',\n						defaultlocation=\'MSA\',\n						modulesallowed=\'1,1,1,1,1,1,1,1,1,1,1,\',\n						blocked=\'0\',\n						pdflanguage=\'0\',\n						department=\'0\',\n						fontsize=\'0\'\n					WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-13 17:14:31','admin','UPDATE www_users SET realname=\'Demonstration user\',\n						customerid=\'\',\n						phone=\'\',\n						email=\'info@kwamoja.com\',\n						\n						branchcode=\'\',\n						supplierid=\'\',\n						salesman=\'\',\n						pagesize=\'A4\',\n						fullaccess=\'8\',\n						cancreatetender=\'0\',\n						theme=\'aguapop\',\n						language =\'en_GB.utf8\',\n						defaultlocation=\'MSA\',\n						modulesallowed=\'1,1,1,1,1,1,1,1,1,1,1,\',\n						blocked=\'0\',\n						pdflanguage=\'0\',\n						department=\'0\',\n						fontsize=\'2\'\n					WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-13 17:14:51','admin','UPDATE www_users SET realname=\'Demonstration user\',\n						customerid=\'\',\n						phone=\'\',\n						email=\'info@kwamoja.com\',\n						\n						branchcode=\'\',\n						supplierid=\'\',\n						salesman=\'\',\n						pagesize=\'A4\',\n						fullaccess=\'8\',\n						cancreatetender=\'0\',\n						theme=\'aguapop\',\n						language =\'en_GB.utf8\',\n						defaultlocation=\'MSA\',\n						modulesallowed=\'1,1,1,1,1,1,1,1,1,1,1,\',\n						blocked=\'0\',\n						pdflanguage=\'0\',\n						department=\'0\',\n						fontsize=\'1\'\n					WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-13 17:15:14','admin','UPDATE www_users SET realname=\'Demonstration user\',\n						customerid=\'\',\n						phone=\'\',\n						email=\'info@kwamoja.com\',\n						\n						branchcode=\'\',\n						supplierid=\'\',\n						salesman=\'\',\n						pagesize=\'A4\',\n						fullaccess=\'8\',\n						cancreatetender=\'0\',\n						theme=\'aguapop\',\n						language =\'en_GB.utf8\',\n						defaultlocation=\'MSA\',\n						modulesallowed=\'1,1,1,1,1,1,1,1,1,1,1,\',\n						blocked=\'0\',\n						pdflanguage=\'0\',\n						department=\'0\',\n						fontsize=\'0\'\n					WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-13 18:32:58','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'aguapop\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-13 18:33:24','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'aguapop\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'2\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-13 18:33:34','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'aguapop\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'0\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-13 22:14:02','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'aguapop\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-13 23:20:57','admin','INSERT INTO securitygroups (secroleid,\n											tokenid)\n									VALUES (\'8\',\n											\'1000\' )');
INSERT INTO `audittrail` VALUES ('2012-12-15 04:16:34','admin','INSERT INTO stockmaster (stockid,\n												description,\n												longdescription,\n												categoryid,\n												units,\n												mbflag,\n												eoq,\n												discontinued,\n												controlled,\n												serialised,\n												perishable,\n												volume,\n												kgs,\n												barcode,\n												discountcategory,\n												taxcatid,\n												decimalplaces,\n												shrinkfactor,\n												pansize)\n							VALUES (\'FLOUR\',\n								\'Maize Flour\',\n								\'Maize Flour\',\n								\'INGR\',\n								\'kgs\',\n								\'B\',\n								\'0\',\n								\'0\',\n								\'0\',\n								\'0\',\n								\'1\',\n								\'0\',\n								\'0\',\n								\'\',\n								\'\',\n								\'1\',\n								\'3\',\n								\'0\',\n								\'0\')');
INSERT INTO `audittrail` VALUES ('2012-12-15 04:16:34','admin','INSERT INTO locstock (loccode,\n													stockid)\n										SELECT locations.loccode,\n										\'FLOUR\'\n										FROM locations');
INSERT INTO `audittrail` VALUES ('2012-12-15 04:17:04','admin','INSERT INTO stockmaster (stockid,\n												description,\n												longdescription,\n												categoryid,\n												units,\n												mbflag,\n												eoq,\n												discontinued,\n												controlled,\n												serialised,\n												perishable,\n												volume,\n												kgs,\n												barcode,\n												discountcategory,\n												taxcatid,\n												decimalplaces,\n												shrinkfactor,\n												pansize)\n							VALUES (\'WATER\',\n								\'Water\',\n								\'Water\',\n								\'INGR\',\n								\'litres\',\n								\'B\',\n								\'0\',\n								\'0\',\n								\'0\',\n								\'0\',\n								\'0\',\n								\'0\',\n								\'0\',\n								\'\',\n								\'\',\n								\'1\',\n								\'3\',\n								\'0\',\n								\'0\')');
INSERT INTO `audittrail` VALUES ('2012-12-15 04:17:04','admin','INSERT INTO locstock (loccode,\n													stockid)\n										SELECT locations.loccode,\n										\'WATER\'\n										FROM locations');
INSERT INTO `audittrail` VALUES ('2012-12-15 04:18:19','admin','INSERT INTO workcentres (code,\n										location,\n										description,\n										overheadrecoveryact,\n										overheadperhour)\n					VALUES (\'KIT\',\n						\'MSA\',\n						\'Kitchen\',\n						\'5100\',\n						\'120\'\n						)');
INSERT INTO `audittrail` VALUES ('2012-12-15 04:20:44','admin','INSERT INTO bom (parent,\n											component,\n											workcentreadded,\n											loccode,\n											quantity,\n											effectiveafter,\n											effectiveto,\n											autoissue)\n							VALUES (\'CHAPATI\',\n								\'FLOUR\',\n								\'KIT\',\n								\'MSA\',\n								0.45,\n								\'2012-12-14\',\n								\'2032-12-15\',\n								0)');
INSERT INTO `audittrail` VALUES ('2012-12-15 04:21:15','admin','INSERT INTO bom (parent,\n											component,\n											workcentreadded,\n											loccode,\n											quantity,\n											effectiveafter,\n											effectiveto,\n											autoissue)\n							VALUES (\'CHAPATI\',\n								\'WATER\',\n								\'KIT\',\n								\'MSA\',\n								0.25,\n								\'2012-12-14\',\n								\'2032-12-15\',\n								0)');
INSERT INTO `audittrail` VALUES ('2012-12-15 12:37:54','admin','INSERT INTO salesorders (\n								orderno,\n								debtorno,\n								branchcode,\n								customerref,\n								comments,\n								orddate,\n								ordertype,\n								shipvia,\n								deliverto,\n								deladd1,\n								deladd2,\n								deladd3,\n								deladd4,\n								deladd5,\n								deladd6,\n								contactphone,\n								contactemail,\n								salesperson,\n								freightcost,\n								fromstkloc,\n								deliverydate,\n								quotedate,\n								confirmeddate,\n								quotation,\n								deliverblind)\n							VALUES (\n								\'1\',\n								\'COA001\',\n								\'COA001\',\n								\'\',\n								\'\',\n								\'2012-12-15 12:37\',\n								\'EA\',\n								\'1\',\n								\'Coastal Hotels Ltd\',\n								\'Mt Kenya Road\',\n								\'\',\n								\'\',\n								\'Mombasa\',\n								\'\',\n								\'Kenya\',\n								\'\',\n								\'\',\n								\'IN\',\n								\'0\',\n								\'MSA\',\n								\'2012-12-17\',\n								\'2012-12-17\',\n								\'2012-12-17\',\n								\'0\',\n								\'1\'\n								)');
INSERT INTO `audittrail` VALUES ('2012-12-15 12:37:54','admin','INSERT INTO salesorderdetails (\n											orderlineno,\n											orderno,\n											stkcode,\n											unitprice,\n											quantity,\n											discountpercent,\n											narrative,\n											poline,\n											itemdue)\n										VALUES (\n					\'0\',\n					\'1\',\n					\'CHAPATI\',\n					\'10\',\n					\'100\',\n					\'0\',\n					\'\',\n					\'\',\n					\'2012-12-15\'\n				)');
INSERT INTO `audittrail` VALUES ('2012-12-15 12:39:07','admin','UPDATE config SET confvalue = \'KES\' WHERE confname = \'CountryOfOperation\'');
INSERT INTO `audittrail` VALUES ('2012-12-15 12:39:07','admin','UPDATE config SET confvalue = \'1900-01-01\' WHERE confname = \'ProhibitPostingsBefore\'');
INSERT INTO `audittrail` VALUES ('2012-12-15 12:39:07','admin','UPDATE config SET confvalue=\'MSA\' WHERE confname=\'DefaultFactoryLocation\'');
INSERT INTO `audittrail` VALUES ('2012-12-15 12:39:19','admin','INSERT INTO salesorders (\n								orderno,\n								debtorno,\n								branchcode,\n								customerref,\n								comments,\n								orddate,\n								ordertype,\n								shipvia,\n								deliverto,\n								deladd1,\n								deladd2,\n								deladd3,\n								deladd4,\n								deladd5,\n								deladd6,\n								contactphone,\n								contactemail,\n								salesperson,\n								freightcost,\n								fromstkloc,\n								deliverydate,\n								quotedate,\n								confirmeddate,\n								quotation,\n								deliverblind)\n							VALUES (\n								\'2\',\n								\'COA001\',\n								\'COA001\',\n								\'\',\n								\'\',\n								\'2012-12-15 12:39\',\n								\'EA\',\n								\'1\',\n								\'Coastal Hotels Ltd\',\n								\'Mt Kenya Road\',\n								\'\',\n								\'\',\n								\'Mombasa\',\n								\'\',\n								\'Kenya\',\n								\'\',\n								\'\',\n								\'IN\',\n								\'0\',\n								\'MSA\',\n								\'2012-12-17\',\n								\'2012-12-17\',\n								\'2012-12-17\',\n								\'0\',\n								\'1\'\n								)');
INSERT INTO `audittrail` VALUES ('2012-12-15 12:39:19','admin','INSERT INTO salesorderdetails (\n											orderlineno,\n											orderno,\n											stkcode,\n											unitprice,\n											quantity,\n											discountpercent,\n											narrative,\n											poline,\n											itemdue)\n										VALUES (\n					\'0\',\n					\'2\',\n					\'CHAPATI\',\n					\'10\',\n					\'100\',\n					\'0\',\n					\'\',\n					\'\',\n					\'2012-12-15\'\n				)');
INSERT INTO `audittrail` VALUES ('2012-12-15 12:39:19','admin','INSERT INTO workorders (wo,\n												 loccode,\n												 requiredby,\n												 startdate)\n								 VALUES (\'2\',\n										\'MSA\',\n										\'2012-12-15\',\n										\'2012-12-15\')');
INSERT INTO `audittrail` VALUES ('2012-12-15 12:39:19','admin','INSERT INTO woitems (wo,\n											 stockid,\n											 qtyreqd,\n											 stdcost)\n								 VALUES ( \'2\',\n										 \'CHAPATI\',\n										 \'190\',\n										 \'0\')');
INSERT INTO `audittrail` VALUES ('2012-12-15 12:39:19','admin','INSERT INTO worequirements (wo,\n				parentstockid,\n				stockid,\n				qtypu,\n				stdcost,\n				autoissue)\n			SELECT \'2\',\n				\'CHAPATI\',\n				bom.component,\n				bom.quantity*1,\n				materialcost+labourcost+overheadcost,\n				bom.autoissue\n			FROM bom INNER JOIN stockmaster\n			ON bom.component=stockmaster.stockid\n			WHERE bom.parent=\'CHAPATI\'\n			AND bom.loccode =\'MSA\'\n			AND stockmaster.mbflag&lt;&gt;\'G\'\n			AND bom.component NOT IN (\n				SELECT stockid\n				FROM worequirements\n				WHERE wo = \'2\'\n				AND parentstockid = \'CHAPATI\'\n			)');
INSERT INTO `audittrail` VALUES ('2012-12-18 01:14:45','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'aguapop\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'0\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-18 01:14:51','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'aguapop\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-18 01:20:12','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'silverwolf\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-18 01:29:35','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'aguapop\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-18 01:30:24','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'wood\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-18 01:30:46','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'silverwolf\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-18 01:31:01','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'fresh\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-18 01:31:17','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'professional\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-18 01:31:35','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'default\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-18 01:31:53','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'gel\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-18 01:32:13','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'aguapop\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-18 01:32:29','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'fluid\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-18 12:38:03','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'silverwolf\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-22 09:04:41','admin','UPDATE www_users SET lastvisitdate=\'2012-12-22 09:04:41\'\n							WHERE www_users.userid=\'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-22 09:05:43','admin','UPDATE www_users SET lastvisitdate=\'2012-12-22 09:05:43\'\n							WHERE www_users.userid=\'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-26 22:23:21','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'silverwolf\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'0\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-27 10:58:06','admin','UPDATE www_users SET lastvisitdate=\'2012-12-27 10:58:06\'\n							WHERE www_users.userid=\'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-27 10:58:34','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'silverwolf\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-28 09:48:22','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'aguapop\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'0\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-30 07:17:39','admin','UPDATE config SET confvalue = \'Y-m-d\' WHERE confname = \'DefaultDateFormat\'');
INSERT INTO `audittrail` VALUES ('2012-12-30 07:20:34','admin','UPDATE config\n				SET confvalue=\'2012-12-30\'\n				WHERE confname=\'DB_Maintenance_LastRun\'');
INSERT INTO `audittrail` VALUES ('2012-12-29 23:28:06','admin','UPDATE config\n				SET confvalue=\'2012-12-29\'\n				WHERE confname=\'DB_Maintenance_LastRun\'');
INSERT INTO `audittrail` VALUES ('2012-12-29 23:33:13','admin','UPDATE www_users SET lastvisitdate=\'2012-12-29 23:33:13\'\n							WHERE www_users.userid=\'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-29 23:33:31','admin','UPDATE www_users SET lastvisitdate=\'2012-12-29 23:33:31\'\n							WHERE www_users.userid=\'admin\'');
INSERT INTO `audittrail` VALUES ('2012-12-30 11:31:42','admin','UPDATE config SET confvalue = \'d/m/Y\' WHERE confname = \'DefaultDateFormat\'');
INSERT INTO `audittrail` VALUES ('2013-01-01 14:57:08','admin','UPDATE config SET confvalue = \'Y/m/d\' WHERE confname = \'DefaultDateFormat\'');
INSERT INTO `audittrail` VALUES ('2013-01-01 14:58:19','admin','UPDATE config SET confvalue = \'Y-m-d\' WHERE confname = \'DefaultDateFormat\'');
INSERT INTO `audittrail` VALUES ('2013-01-01 15:00:25','admin','UPDATE config\n				SET confvalue=\'2013-01-01\'\n				WHERE confname=\'DB_Maintenance_LastRun\'');
INSERT INTO `audittrail` VALUES ('2013-01-01 15:23:42','admin','UPDATE config SET confvalue = \'d/m/Y\' WHERE confname = \'DefaultDateFormat\'');
INSERT INTO `audittrail` VALUES ('2013-01-01 15:25:37','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'aguapop\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-02 10:12:38','admin','UPDATE www_users SET realname=\'Demonstration user\',\n						customerid=\'\',\n						phone=\'\',\n						email=\'info@kwamoja.com\',\n						\n						branchcode=\'\',\n						supplierid=\'\',\n						salesman=\'\',\n						pagesize=\'A4\',\n						fullaccess=\'8\',\n						cancreatetender=\'1\',\n						theme=\'aguapop\',\n						language =\'en_GB.utf8\',\n						defaultlocation=\'MSA\',\n						modulesallowed=\'1,1,1,1,1,1,1,1,1,1,1,\',\n						blocked=\'0\',\n						pdflanguage=\'0\',\n						department=\'0\',\n						fontsize=\'1\'\n					WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-02 10:16:12','admin','UPDATE suppliers SET suppname=\'Voi Fruit and Vegetable\',\n							address1=\'PO Box 9999\',\n							address2=\'\',\n							address3=\'Voi\',\n							address4=\'\',\n							address5=\'\',\n							address6=\'Kenya\',\n							telephone=\'\',\n							fax = \'\',\n							email = \'sales@example.com\',\n							supptype = \'1\',\n							currcode=\'KES\',\n							suppliersince=\'2012-12-10\',\n							paymentterms=\'20\',\n							bankpartics=\'\',\n							bankref=\'0\',\n					 		bankact=\'\',\n							remittance=\'0\',\n							taxgroupid=\'1\',\n							factorcompanyid=\'0\',\n							lat=\'0\',\n							lng=\'0\',\n							taxref=\'\'\n						WHERE supplierid = \'VOI001\'');
INSERT INTO `audittrail` VALUES ('2013-01-02 11:34:59','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'silverwolf\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-02 15:25:08','admin','INSERT INTO tenders (tenderid,\n											location,\n											address1,\n											address2,\n											address3,\n											address4,\n											address5,\n											address6,\n											telephone,\n											requiredbydate)\n								VALUES (\'1\',\n										\'MSA\',\n										\' \',\n										\'\',\n										\'\',\n										\'\',\n										\'\',\n										\'\',\n										\'\',\n										\'2013-01-02\')');
INSERT INTO `audittrail` VALUES ('2013-01-02 15:25:08','admin','INSERT INTO tendersuppliers (tenderid,\n															supplierid,\n															email)\n								VALUES (\'1\',\n										\'VOI001\',\n										\'sales@example.com\')');
INSERT INTO `audittrail` VALUES ('2013-01-02 15:25:09','admin','INSERT INTO tenderitems (tenderid,\n														stockid,\n														quantity,\n														units)\n											VALUES (\'1\',\n													\'CHAPATI\',\n													\'10\',\n													\'each\')');
INSERT INTO `audittrail` VALUES ('2013-01-02 15:25:09','admin','INSERT INTO tenderitems (tenderid,\n														stockid,\n														quantity,\n														units)\n											VALUES (\'1\',\n													\'FLOUR\',\n													\'20\',\n													\'kgs\')');
INSERT INTO `audittrail` VALUES ('2013-01-02 15:25:09','admin','INSERT INTO tenderitems (tenderid,\n														stockid,\n														quantity,\n														units)\n											VALUES (\'1\',\n													\'WATER\',\n													\'30\',\n													\'litres\')');
INSERT INTO `audittrail` VALUES ('2013-01-02 15:52:51','admin','INSERT INTO www_users (userid,\n										realname,\n										supplierid,\n										password,\n										phone,\n										email,\n										pagesize,\n										fullaccess,\n										defaultlocation,\n										lastvisitdate,\n										modulesallowed,\n										displayrecordsmax,\n										theme,\n										language)\n						VALUES (\'voifv\',\n							\'Voi Fruit and Vegetable supplies Lt\',\n							\'VOI001\',\n							\'8467dd232d0410dd7fc0e25a5e9ce72f9bdc0d1e\',\n							\'\',\n							\'\',\n							\'A4\',\n							\'9\',\n							\'MSA\',\n							\'02/01/2013\',\n							\'0,0,0,0,0,0,0,0,0,0,0,\',\n							\'50\',\n							\'silverwolf\',\n							\'en_GB.utf8\')');
INSERT INTO `audittrail` VALUES ('2013-01-02 16:01:19','admin','UPDATE scripts SET pagesecurity=\'0\' WHERE script=\'UserSettings.php\'');
INSERT INTO `audittrail` VALUES ('2013-01-02 16:01:47','voifv','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'silverwolf\',\n					language=\'en_GB.utf8\',\n					email=\'\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'voifv\'');
INSERT INTO `audittrail` VALUES ('2013-01-02 18:40:45','voifv','INSERT INTO offers (	supplierid,\n												tenderid,\n												stockid,\n												quantity,\n												uom,\n												price,\n												expirydate,\n												currcode)\n						VALUES (\'VOI001\',\n								\'1\',\n								\'CHAPATI\',\n								\'10\',\n								\'each\',\n								\'30.00\',\n								\'2013-01-02\',\n								\'KES\')');
INSERT INTO `audittrail` VALUES ('2013-01-02 18:40:45','voifv','INSERT INTO offers (	supplierid,\n												tenderid,\n												stockid,\n												quantity,\n												uom,\n												price,\n												expirydate,\n												currcode)\n						VALUES (\'VOI001\',\n								\'1\',\n								\'FLOUR\',\n								\'20.000\',\n								\'kgs\',\n								\'20.00\',\n								\'2013-01-02\',\n								\'KES\')');
INSERT INTO `audittrail` VALUES ('2013-01-02 18:40:45','voifv','INSERT INTO offers (	supplierid,\n												tenderid,\n												stockid,\n												quantity,\n												uom,\n												price,\n												expirydate,\n												currcode)\n						VALUES (\'VOI001\',\n								\'1\',\n								\'WATER\',\n								\'30.000\',\n								\'litres\',\n								\'10.00\',\n								\'2013-01-02\',\n								\'KES\')');
INSERT INTO `audittrail` VALUES ('2013-01-02 18:40:46','voifv','UPDATE tendersuppliers\n			SET responded=1\n			WHERE supplierid=\'VOI001\'\n			AND tenderid=\'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-03 19:10:43','admin','UPDATE www_users SET lastvisitdate=\'2013-01-03 19:10:43\'\n							WHERE www_users.userid=\'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-03 19:10:58','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'aguapop\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-03 19:11:59','admin','UPDATE www_users SET lastvisitdate=\'2013-01-03 19:11:59\'\n							WHERE www_users.userid=\'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-03 19:12:11','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'aguapop\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'0\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-03 19:12:15','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'aguapop\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-03 19:12:21','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'silverwolf\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-03 19:22:27','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'aguapop\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-03 19:46:49','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'silverwolf\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-04 15:02:27','admin','DELETE FROM bankaccounts WHERE accountcode=\'1030\'');
INSERT INTO `audittrail` VALUES ('2013-01-04 15:02:49','admin','INSERT INTO bankaccounts (accountcode,\n										bankaccountname,\n										bankaccountcode,\n										bankaccountnumber,\n										bankaddress,\n										currcode,\n										invoice\n									) VALUES (\'1030\',\n										\'Cheque Account\',\n										\'\',\n										\'1\',\n										\'\',\n										\'KES\',\n										\'1\' )');
INSERT INTO `audittrail` VALUES ('2013-01-04 15:03:03','admin','UPDATE bankaccounts SET bankaccountname=\'Savings Account\',\n											bankaccountcode=\'\',\n											bankaccountnumber=\'2\',\n											bankaddress=\'\',\n											currcode =\'KES\',\n											invoice =\'0\'\n										WHERE accountcode = \'1040\'');
INSERT INTO `audittrail` VALUES ('2013-01-04 15:03:41','admin','INSERT INTO periods (periodno,\n													lastdate_in_period)\n												VALUES (\n													\'2\',\n													\'2013-02-28\')');
INSERT INTO `audittrail` VALUES ('2013-01-04 15:03:41','admin','INSERT INTO debtortrans (transno,\n											type,\n											debtorno,\n											branchcode,\n											trandate,\n											inputdate,\n											prd,\n											reference,\n											tpe,\n											rate,\n											ovamount,\n											ovdiscount,\n											invtext)\n					VALUES (\n						\'1\',\n						12,\n						\'FAT001\',\n						\'\',\n						\'2013-01-04\',\n						\'2013-01-04 15-03-41\',\n						\'1\',\n						\'Cash \',\n						\'\',\n						\'1\',\n						\'-100\',\n						\'0\',\n						\'\'\n					)');
INSERT INTO `audittrail` VALUES ('2013-01-04 15:03:41','admin','UPDATE debtorsmaster\n						SET lastpaiddate = \'2013-01-04\',\n						lastpaid=\'100\'\n					WHERE debtorsmaster.debtorno=\'FAT001\'');
INSERT INTO `audittrail` VALUES ('2013-01-04 15:03:42','admin','INSERT INTO banktrans (type,\n								transno,\n								bankact,\n								ref,\n								exrate,\n								functionalexrate,\n								transdate,\n								banktranstype,\n								amount,\n								currcode)\n		VALUES (\n			12,\n			\'1\',\n			\'1030\',\n			\'\',\n			\'1\',\n			\'1\',\n			\'2013-01-04\',\n			\'Cash\',\n			\'100\',\n			\'KES\'\n		)');
INSERT INTO `audittrail` VALUES ('2013-01-04 15:03:42','admin','INSERT INTO gltrans (type,\n										typeno,\n										trandate,\n										periodno,\n										account,\n										narrative,\n										amount)\n				VALUES (\n					12,\n					\'1\',\n					\'2013-01-04\',\n					\'1\',\n					\'1030\',\n					\'\',\n					\'100\'\n				)');
INSERT INTO `audittrail` VALUES ('2013-01-04 15:03:42','admin','INSERT INTO gltrans ( type,\n										typeno,\n										trandate,\n										periodno,\n										account,\n										narrative,\n										amount)\n						VALUES (\n							12,\n							\'1\',\n							\'2013-01-04\',\n							\'1\',\n							\'1100\',\n							\'\',\n							\'-100\'\n							)');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:02:35','admin','INSERT INTO workorders (wo,\n									loccode,\n									requiredby,\n									startdate)\n								VALUES (\n									\'3\',\n									\'MSA\',\n									\'2013-01-05\',\n									\'2013-01-05\')');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:12:38','admin','UPDATE taxauthorities\n					SET taxglcode =\'2300\',\n					purchtaxglaccount =\'2310\',\n					description = \'Kenya Revenue Author\',\n					bank = \'\',\n					bankacctype = \'\',\n					bankacc = \'\',\n					bankswift = \'\'\n				WHERE taxid = \'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:14:52','admin','UPDATE taxauthorities\n					SET taxglcode =\'2300\',\n					purchtaxglaccount =\'2310\',\n					description = \'URA\',\n					bank = \'\',\n					bankacctype = \'\',\n					bankacc = \'\',\n					bankswift = \'\'\n				WHERE taxid = \'5\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:15:04','admin','UPDATE taxauthorities\n					SET taxglcode =\'2300\',\n					purchtaxglaccount =\'2310\',\n					description = \'KRA\',\n					bank = \'\',\n					bankacctype = \'\',\n					bankacc = \'\',\n					bankswift = \'\'\n				WHERE taxid = \'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:15:30','admin','UPDATE taxauthorities\n					SET taxglcode =\'2300\',\n					purchtaxglaccount =\'2310\',\n					description = \'TRA\',\n					bank = \'\',\n					bankacctype = \'\',\n					bankacc = \'\',\n					bankswift = \'\'\n				WHERE taxid = \'11\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:19:11','admin','UPDATE taxauthorities\n					SET taxglcode =\'2300\',\n					purchtaxglaccount =\'2310\',\n					description = \'Kenya Revenue Authority\',\n					bank = \'\',\n					bankacctype = \'\',\n					bankacc = \'\',\n					bankswift = \'\'\n				WHERE taxid = \'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:19:33','admin','UPDATE taxauthorities\n					SET taxglcode =\'2300\',\n					purchtaxglaccount =\'2310\',\n					description = \'Uganda Revenue Authority\',\n					bank = \'\',\n					bankacctype = \'\',\n					bankacc = \'\',\n					bankswift = \'\'\n				WHERE taxid = \'5\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:19:53','admin','UPDATE taxauthorities\n					SET taxglcode =\'2300\',\n					purchtaxglaccount =\'2310\',\n					description = \'Tanzania Revenue Authority\',\n					bank = \'\',\n					bankacctype = \'\',\n					bankacc = \'\',\n					bankswift = \'\'\n				WHERE taxid = \'11\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:20:13','admin','UPDATE taxauthorities\n					SET taxglcode =\'2300\',\n					purchtaxglaccount =\'2310\',\n					description = \'Rwanda Revenue Authority\',\n					bank = \'\',\n					bankacctype = \'\',\n					bankacc = \'\',\n					bankswift = \'\'\n				WHERE taxid = \'12\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:20:45','admin','UPDATE taxauthorities\n					SET taxglcode =\'2300\',\n					purchtaxglaccount =\'2310\',\n					description = \'Burundi Revenue Authority\',\n					bank = \'\',\n					bankacctype = \'\',\n					bankacc = \'\',\n					bankswift = \'\'\n				WHERE taxid = \'13\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:22:25','admin','UPDATE taxauthrates SET taxrate=0.16\n						WHERE taxcatid = \'1\'\n						AND dispatchtaxprovince = \'1\'\n						AND taxauthority = \'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:22:25','admin','UPDATE taxauthrates SET taxrate=0.16\n						WHERE taxcatid = \'2\'\n						AND dispatchtaxprovince = \'1\'\n						AND taxauthority = \'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:22:25','admin','UPDATE taxauthrates SET taxrate=0.16\n						WHERE taxcatid = \'5\'\n						AND dispatchtaxprovince = \'1\'\n						AND taxauthority = \'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:23:25','admin','UPDATE taxauthrates SET taxrate=0.18\n						WHERE taxcatid = \'1\'\n						AND dispatchtaxprovince = \'1\'\n						AND taxauthority = \'5\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:23:25','admin','UPDATE taxauthrates SET taxrate=0.18\n						WHERE taxcatid = \'2\'\n						AND dispatchtaxprovince = \'1\'\n						AND taxauthority = \'5\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:23:25','admin','UPDATE taxauthrates SET taxrate=0.18\n						WHERE taxcatid = \'5\'\n						AND dispatchtaxprovince = \'1\'\n						AND taxauthority = \'5\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:24:16','admin','UPDATE taxauthrates SET taxrate=0.18\n						WHERE taxcatid = \'1\'\n						AND dispatchtaxprovince = \'1\'\n						AND taxauthority = \'11\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:24:16','admin','UPDATE taxauthrates SET taxrate=0.18\n						WHERE taxcatid = \'2\'\n						AND dispatchtaxprovince = \'1\'\n						AND taxauthority = \'11\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:24:16','admin','UPDATE taxauthrates SET taxrate=0.18\n						WHERE taxcatid = \'5\'\n						AND dispatchtaxprovince = \'1\'\n						AND taxauthority = \'11\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:24:52','admin','UPDATE taxauthrates SET taxrate=0.18\n						WHERE taxcatid = \'1\'\n						AND dispatchtaxprovince = \'1\'\n						AND taxauthority = \'12\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:24:52','admin','UPDATE taxauthrates SET taxrate=0.18\n						WHERE taxcatid = \'2\'\n						AND dispatchtaxprovince = \'1\'\n						AND taxauthority = \'12\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:24:52','admin','UPDATE taxauthrates SET taxrate=0.18\n						WHERE taxcatid = \'5\'\n						AND dispatchtaxprovince = \'1\'\n						AND taxauthority = \'12\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:25:20','admin','UPDATE taxauthrates SET taxrate=0.18\n						WHERE taxcatid = \'1\'\n						AND dispatchtaxprovince = \'1\'\n						AND taxauthority = \'13\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:25:20','admin','UPDATE taxauthrates SET taxrate=0.18\n						WHERE taxcatid = \'2\'\n						AND dispatchtaxprovince = \'1\'\n						AND taxauthority = \'13\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:25:20','admin','UPDATE taxauthrates SET taxrate=0.18\n						WHERE taxcatid = \'5\'\n						AND dispatchtaxprovince = \'1\'\n						AND taxauthority = \'13\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:26:36','admin','INSERT INTO taxgrouptaxes ( taxgroupid,\n												taxauthid,\n												calculationorder)\n					VALUES (\'1\',\n							\'1\',\n							0)');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:26:45','admin','UPDATE taxgroups SET taxgroupdescription = \'Kenya\'\n					WHERE taxgroupid = \'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:27:05','admin','UPDATE taxgroups SET taxgroupdescription = \'Uganda\'\n					WHERE taxgroupid = \'2\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:27:09','admin','INSERT INTO taxgrouptaxes ( taxgroupid,\n												taxauthid,\n												calculationorder)\n					VALUES (\'2\',\n							\'5\',\n							0)');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:27:27','admin','UPDATE taxgroups SET taxgroupdescription = \'Tanzania\'\n					WHERE taxgroupid = \'3\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:27:33','admin','INSERT INTO taxgrouptaxes ( taxgroupid,\n												taxauthid,\n												calculationorder)\n					VALUES (\'3\',\n							\'11\',\n							0)');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:27:44','admin','INSERT INTO taxgroups (taxgroupdescription)\n						VALUES (\'Rwanda\')');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:27:49','admin','INSERT INTO taxgrouptaxes ( taxgroupid,\n												taxauthid,\n												calculationorder)\n					VALUES (\'4\',\n							\'12\',\n							0)');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:27:59','admin','INSERT INTO taxgroups (taxgroupdescription)\n						VALUES (\'Burundi\')');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:28:03','admin','INSERT INTO taxgrouptaxes ( taxgroupid,\n												taxauthid,\n												calculationorder)\n					VALUES (\'5\',\n							\'13\',\n							0)');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:28:28','admin','UPDATE taxprovinces\n					SET taxprovincename=\'East African Community\'\n					WHERE taxprovincename LIKE \'Default Tax province\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:31:13','admin','DELETE FROM worequirements\n											WHERE wo=\'2\'\n											AND parentstockid=\'CHAPATI\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:31:13','admin','INSERT INTO worequirements (wo,\n				parentstockid,\n				stockid,\n				qtypu,\n				stdcost,\n				autoissue)\n			SELECT \'2\',\n				\'CHAPATI\',\n				bom.component,\n				bom.quantity*1,\n				materialcost+labourcost+overheadcost,\n				bom.autoissue\n			FROM bom INNER JOIN stockmaster\n			ON bom.component=stockmaster.stockid\n			WHERE bom.parent=\'CHAPATI\'\n			AND bom.loccode =\'MSA\'\n			AND stockmaster.mbflag&lt;&gt;\'G\'\n			AND bom.component NOT IN (\n				SELECT stockid\n				FROM worequirements\n				WHERE wo = \'2\'\n				AND parentstockid = \'CHAPATI\'\n			)');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:31:14','admin','UPDATE locstock\n				SET quantity = locstock.quantity + 50\n				WHERE locstock.stockid = \'CHAPATI\'\n				AND loccode = \'MSA\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:31:14','admin','INSERT INTO stockmoves (stockid,\n										type,\n										transno,\n										loccode,\n										trandate,\n										price,\n										prd,\n										reference,\n										qty,\n										standardcost,\n										newqoh)\n					VALUES (\'CHAPATI\',\n							26,\n							\'2\',\n							\'MSA\',\n							\'2013-01-05\',\n							\'0\',\n							\'1\',\n							\'2\',\n							\'50\',\n							\'0\',\n							\'50\')');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:31:14','admin','UPDATE woitems\n									SET qtyrecd=qtyrecd+50,\n										nextlotsnref=\'\'\n									WHERE wo=\'2\'\n									AND stockid=\'CHAPATI\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:31:37','admin','UPDATE salesorders\n			SET comments = CONCAT(comments,\' Inv \',\'1\')\n			WHERE orderno= \'2\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:31:37','admin','INSERT INTO debtortrans (transno,\n									type,\n									debtorno,\n									branchcode,\n									trandate,\n									inputdate,\n									prd,\n									reference,\n									tpe,\n									order_,\n									ovamount,\n									ovgst,\n									ovfreight,\n									rate,\n									invtext,\n									shipvia,\n									consignment )\n								VALUES (\n									\'1\',\n									10,\n									\'COA001\',\n									\'COA001\',\n									\'2013-01-07\',\n									\'2013-01-05 12-31-37\',\n									\'1\',\n									\'\',\n									\'EA\',\n									\'2\',\n									\'500\',\n									\'80\',\n									\'0\',\n									\'1\',\n									\'\',\n									\'1\',\n									\'\'	)');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:31:37','admin','INSERT INTO debtortranstaxes (debtortransid,\n											taxauthid,\n											taxamount)\n								VALUES (\'2\',\n									\'1\',\n									\'80\')');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:31:37','admin','INSERT INTO orderdeliverydifferenceslog (orderno,\n															invoiceno,\n															stockid,\n															quantitydiff,\n															debtorno,\n															branch,\n															can_or_bo\n														)\n												VALUES (\n													\'2\',\n													\'1\',\n													\'CHAPATI\',\n													\'50\',\n													\'COA001\',\n													\'COA001\',\n													\'BO\'\n												)');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:31:37','admin','UPDATE salesorderdetails\n							SET qtyinvoiced = qtyinvoiced + 50,\n								actualdispatchdate = \'2013-01-07\'\n							WHERE orderno = \'2\'\n							AND orderlineno = \'0\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:31:37','admin','UPDATE locstock\n						SET quantity = locstock.quantity - 50\n						WHERE locstock.stockid = \'CHAPATI\'\n						AND loccode = \'MSA\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:31:37','admin','INSERT INTO stockmoves (stockid,\n														type,\n														transno,\n														loccode,\n														trandate,\n														debtorno,\n														branchcode,\n														price,\n														prd,\n														reference,\n														qty,\n														discountpercent,\n														standardcost,\n														newqoh,\n														narrative )\n													VALUES (\'CHAPATI\',\n														10,\n														\'1\',\n														\'MSA\',\n														\'2013-01-07\',\n														\'COA001\',\n														\'COA001\',\n														\'10\',\n														\'1\',\n														\'2\',\n														\'-50\',\n														\'0\',\n														\'10.0000\',\n														\'0\',\n														\'\' )');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:31:37','admin','INSERT INTO stockmovestaxes (stkmoveno,\n													taxauthid,\n													taxrate,\n													taxcalculationorder,\n													taxontax)\n										VALUES (\'2\',\n											\'1\',\n											\'0.16\',\n											\'0\',\n											\'0\')');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:31:38','admin','INSERT INTO salesanalysis (typeabbrev,\n												periodno,\n												amt,\n												cost,\n												cust,\n												custbranch,\n												qty,\n												disc,\n												stockid,\n												area,\n												budgetoractual,\n												salesperson,\n												stkcategory )\n								SELECT \'EA\',\n										\'1\',\n										\'500\',\n										\'500\',\n										\'COA001\',\n										\'COA001\',\n										\'50\',\n										\'0\',\n										\'CHAPATI\',\n										custbranch.area,\n										1,\n										\'IN\',\n										stockmaster.categoryid\n								FROM stockmaster, custbranch\n								WHERE stockmaster.stockid = \'CHAPATI\'\n								AND custbranch.debtorno = \'COA001\'\n								AND custbranch.branchcode=\'COA001\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:31:38','admin','INSERT INTO gltrans (type,\n											typeno,\n											trandate,\n											periodno,\n											account,\n											narrative,\n											amount)\n									VALUES (\n										10,\n										\'1\',\n										\'2013-01-07\',\n										\'1\',\n										\'5000\',\n										\'COA001 - CHAPATI x 50 @ 10.0000\',\n										\'500\')');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:31:38','admin','INSERT INTO gltrans (type,\n											typeno,\n											trandate,\n											periodno,\n											account,\n											narrative,\n											amount)\n									VALUES (\n										10,\n										\'1\',\n										\'2013-01-07\',\n										\'1\',\n										\'1460\',\n										\'COA001 - CHAPATI x 50 @ 10.0000\',\n										\'-500\')');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:31:38','admin','INSERT INTO gltrans (type,\n												typeno,\n												trandate,\n												periodno,\n												account,\n												narrative,\n												amount )\n										VALUES (\n											10,\n											\'1\',\n											\'2013-01-07\',\n											\'1\',\n											\'4100\',\n											\'COA001 - CHAPATI x 50 @ 10\',\n											\'-500\')');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:31:38','admin','INSERT INTO gltrans (type,\n										typeno,\n										trandate,\n										periodno,\n										account,\n										narrative,\n										amount)\n									VALUES (\n										10,\n										\'1\',\n										\'2013-01-07\',\n										\'1\',\n										\'1100\',\n										\'COA001\',\n										\'580\')');
INSERT INTO `audittrail` VALUES ('2013-01-05 12:31:38','admin','INSERT INTO gltrans (type,\n											typeno,\n											trandate,\n											periodno,\n											account,\n											narrative,\n											amount)\n										VALUES (\n											10,\n											\'1\',\n											\'2013-01-07\',\n											\'1\',\n											\'2300\',\n											\'COA001\',\n											\'-80\')');
INSERT INTO `audittrail` VALUES ('2013-01-05 14:11:16','admin','UPDATE banktrans SET amountcleared= 100\n									WHERE banktransid=\'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 14:11:43','admin','INSERT INTO debtortrans (transno,\n											type,\n											debtorno,\n											branchcode,\n											trandate,\n											inputdate,\n											prd,\n											reference,\n											tpe,\n											rate,\n											ovamount,\n											ovdiscount,\n											invtext)\n					VALUES (\n						\'2\',\n						12,\n						\'COA001\',\n						\'\',\n						\'2013-01-05\',\n						\'2013-01-05 14-11-43\',\n						\'1\',\n						\'Cash \',\n						\'\',\n						\'1\',\n						\'-280\',\n						\'0\',\n						\'\'\n					)');
INSERT INTO `audittrail` VALUES ('2013-01-05 14:11:43','admin','UPDATE debtorsmaster\n						SET lastpaiddate = \'2013-01-05\',\n						lastpaid=\'280\'\n					WHERE debtorsmaster.debtorno=\'COA001\'');
INSERT INTO `audittrail` VALUES ('2013-01-05 14:11:44','admin','INSERT INTO banktrans (type,\n								transno,\n								bankact,\n								ref,\n								exrate,\n								functionalexrate,\n								transdate,\n								banktranstype,\n								amount,\n								currcode)\n		VALUES (\n			12,\n			\'2\',\n			\'1030\',\n			\'\',\n			\'1\',\n			\'1\',\n			\'2013-01-05\',\n			\'Cash\',\n			\'280\',\n			\'KES\'\n		)');
INSERT INTO `audittrail` VALUES ('2013-01-05 14:11:44','admin','INSERT INTO gltrans (type,\n										typeno,\n										trandate,\n										periodno,\n										account,\n										narrative,\n										amount)\n				VALUES (\n					12,\n					\'2\',\n					\'2013-01-05\',\n					\'1\',\n					\'1030\',\n					\'\',\n					\'280\'\n				)');
INSERT INTO `audittrail` VALUES ('2013-01-05 14:11:44','admin','INSERT INTO gltrans ( type,\n										typeno,\n										trandate,\n										periodno,\n										account,\n										narrative,\n										amount)\n						VALUES (\n							12,\n							\'2\',\n							\'2013-01-05\',\n							\'1\',\n							\'1100\',\n							\'\',\n							\'-280\'\n							)');
INSERT INTO `audittrail` VALUES ('2013-01-06 11:30:11','admin','UPDATE www_users SET lastvisitdate=\'2013-01-06 11:30:11\'\n							WHERE www_users.userid=\'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-06 22:45:36','admin','UPDATE locations SET loccode=\'MSA\',\n									locationname=\'Mombasa Main Warehouse\',\n									deladd1=\' \',\n									deladd2=\'\',\n									deladd3=\'\',\n									deladd4=\'\',\n									deladd5=\'\',\n									deladd6=\'\',\n									tel=\'\',\n									fax=\'\',\n									email=\'\',\n									contact=\'\',\n									taxprovinceid = \'1\',\n									cashsalecustomer =\'\',\n									cashsalebranch =\'\',\n									managed = \'0\',\n									internalrequest = \'1\'\n						WHERE loccode = \'MSA\'');
INSERT INTO `audittrail` VALUES ('2013-01-06 22:48:55','admin','INSERT INTO departments (description,\r\n											 authoriser )\r\n					VALUES (\'Finance Department\',\r\n							\'admin\')');
INSERT INTO `audittrail` VALUES ('2013-01-06 22:52:37','admin','INSERT INTO internalstockcatrole (secroleid,\r\n												categoryid)\r\n										VALUES (\'8\',\r\n												\'INGR\')');
INSERT INTO `audittrail` VALUES ('2013-01-06 23:01:44','admin','INSERT INTO stockrequest (dispatchid,\r\n											loccode,\r\n											departmentid,\r\n											despatchdate,\r\n											narrative)\r\n										VALUES(\r\n											\'1\',\r\n											\'MSA\',\r\n											\'1\',\r\n											\'2013-01-06\',\r\n											\'\')');
INSERT INTO `audittrail` VALUES ('2013-01-06 23:01:44','admin','INSERT INTO stockrequestitems (dispatchitemsid,\r\n													dispatchid,\r\n													stockid,\r\n													quantity,\r\n													decimalplaces,\r\n													uom)\r\n												VALUES(\r\n													\'0\',\r\n													\'1\',\r\n													\'FLOUR\',\r\n													\'10\',\r\n													\'3\',\r\n													\'kgs\')');
INSERT INTO `audittrail` VALUES ('2013-01-06 23:01:44','admin','INSERT INTO stockrequestitems (dispatchitemsid,\r\n													dispatchid,\r\n													stockid,\r\n													quantity,\r\n													decimalplaces,\r\n													uom)\r\n												VALUES(\r\n													\'1\',\r\n													\'1\',\r\n													\'WATER\',\r\n													\'20\',\r\n													\'3\',\r\n													\'litres\')');
INSERT INTO `audittrail` VALUES ('2013-01-07 10:55:57','admin','UPDATE stockrequest\r\n					SET authorised=\'1\'\r\n					WHERE dispatchid=\'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-07 11:15:46','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'silverwolf\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'0\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-07 11:16:41','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'silverwolf\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'2\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-07 11:26:32','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'silverwolf\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-07 12:57:48','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'silverwolf\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'0\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-07 12:58:19','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'silverwolf\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-08 17:13:22','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'aguapop\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-08 17:32:35','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'wood\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:01:18','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'fluid\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:02:37','admin','UPDATE www_users SET realname=\'Demonstration user\',\n						customerid=\'\',\n						phone=\'\',\n						email=\'info@kwamoja.com\',\n						password=\'8467dd232d0410dd7fc0e25a5e9ce72f9bdc0d1e\',\n						branchcode=\'\',\n						supplierid=\'\',\n						salesman=\'\',\n						pagesize=\'A4\',\n						fullaccess=\'8\',\n						cancreatetender=\'1\',\n						theme=\'aguapop\',\n						language =\'en_GB.utf8\',\n						defaultlocation=\'MSA\',\n						modulesallowed=\'1,1,1,1,1,1,1,1,1,1,1,\',\n						blocked=\'0\',\n						pdflanguage=\'0\',\n						department=\'0\',\n						fontsize=\'1\'\n					WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:31:57','admin','INSERT INTO periods (periodno,\n													lastdate_in_period)\n												VALUES (\n													\'-1\',\n													\'2012-11-30\')');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:31:57','admin','INSERT INTO periods (periodno,\n													lastdate_in_period)\n												VALUES (\n													\'-2\',\n													\'2012-10-31\')');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:31:58','admin','INSERT INTO periods (periodno,\n													lastdate_in_period)\n												VALUES (\n													\'-3\',\n													\'2012-09-30\')');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:31:58','admin','INSERT INTO periods (periodno,\n													lastdate_in_period)\n												VALUES (\n													\'-4\',\n													\'2012-08-31\')');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:31:58','admin','INSERT INTO periods (periodno,\n													lastdate_in_period)\n												VALUES (\n													\'-5\',\n													\'2012-07-31\')');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:31:58','admin','INSERT INTO periods (periodno,\n													lastdate_in_period)\n												VALUES (\n													\'-6\',\n													\'2012-06-30\')');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:31:58','admin','INSERT INTO periods (periodno,\n													lastdate_in_period)\n												VALUES (\n													\'-7\',\n													\'2012-05-31\')');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:31:59','admin','INSERT INTO periods (periodno,\n													lastdate_in_period)\n												VALUES (\n													\'-8\',\n													\'2012-04-30\')');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:31:59','admin','INSERT INTO periods (periodno,\n													lastdate_in_period)\n												VALUES (\n													\'3\',\n													\'2013-03-31\')');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:32:00','admin','INSERT INTO chartdetails (accountcode, period)\n					SELECT chartmaster.accountcode, periods.periodno\n					FROM (chartmaster CROSS JOIN periods)\n					LEFT JOIN chartdetails ON chartmaster.accountcode = chartdetails.accountcode\n					AND periods.periodno = chartdetails.period\n					WHERE (periods.periodno BETWEEN \'-8\' AND \'3\')\n					AND chartdetails.accountcode IS NULL');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:32:01','admin','UPDATE chartdetails SET actual = actual + 380\n						WHERE accountcode = \'1030\'\n						AND period= \'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:32:01','admin','UPDATE chartdetails SET bfwd = bfwd + 380\n						WHERE accountcode = \'1030\'\n						AND period &gt; \'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:32:01','admin','UPDATE chartdetails SET actual = actual + 200\n						WHERE accountcode = \'1100\'\n						AND period= \'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:32:01','admin','UPDATE chartdetails SET bfwd = bfwd + 200\n						WHERE accountcode = \'1100\'\n						AND period &gt; \'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:32:01','admin','UPDATE chartdetails SET actual = actual + -500\n						WHERE accountcode = \'1460\'\n						AND period= \'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:32:01','admin','UPDATE chartdetails SET bfwd = bfwd + -500\n						WHERE accountcode = \'1460\'\n						AND period &gt; \'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:32:01','admin','UPDATE chartdetails SET actual = actual + -80\n						WHERE accountcode = \'2300\'\n						AND period= \'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:32:01','admin','UPDATE chartdetails SET bfwd = bfwd + -80\n						WHERE accountcode = \'2300\'\n						AND period &gt; \'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:32:01','admin','UPDATE chartdetails SET actual = actual + -500\n						WHERE accountcode = \'4100\'\n						AND period= \'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:32:01','admin','UPDATE chartdetails SET bfwd = bfwd + -500\n						WHERE accountcode = \'4100\'\n						AND period &gt; \'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:32:01','admin','UPDATE gltrans SET posted = 1 WHERE periodno = \'1\' AND posted=0');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:32:01','admin','UPDATE chartdetails SET actual = actual + 500\n				WHERE accountcode = \'5000\'\n				AND period= \'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:32:01','admin','UPDATE chartdetails SET bfwd = bfwd + 500\n				WHERE accountcode = \'5000\'\n				AND period &gt; \'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:36:11','admin','INSERT INTO chartmaster (accountcode,\n						accountname,\n						group_)\n					VALUES (\'6005\',\n							\'Free Gifts\',\n							\'Giveaways\')');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:36:17','admin','INSERT INTO chartdetails (accountcode, period)\n					SELECT chartmaster.accountcode, periods.periodno\n					FROM (chartmaster CROSS JOIN periods)\n					LEFT JOIN chartdetails ON chartmaster.accountcode = chartdetails.accountcode\n					AND periods.periodno = chartdetails.period\n					WHERE (periods.periodno BETWEEN \'-8\' AND \'3\')\n					AND chartdetails.accountcode IS NULL');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:37:49','admin','INSERT INTO gltrans (type,\n									typeno,\n									trandate,\n									periodno,\n									account,\n									narrative,\n									amount,\n									tag)\n				VALUES (\'0\',\n					\'1\',\n					\'2012-12-31\',\n					\'0\',\n					\'6005\',\n					\'\',\n					\'200\',\n					\'0\'\n					)');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:37:49','admin','INSERT INTO gltrans (type,\n									typeno,\n									trandate,\n									periodno,\n									account,\n									narrative,\n									amount,\n									tag)\n				VALUES (\'0\',\n					\'1\',\n					\'2012-12-31\',\n					\'0\',\n					\'6100\',\n					\'\',\n					\'300\',\n					\'0\'\n					)');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:37:49','admin','INSERT INTO gltrans (type,\n									typeno,\n									trandate,\n									periodno,\n									account,\n									narrative,\n									amount,\n									tag)\n				VALUES (\'0\',\n					\'1\',\n					\'2012-12-31\',\n					\'0\',\n					\'6150\',\n					\'\',\n					\'-500\',\n					\'0\'\n					)');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:37:49','admin','UPDATE chartdetails SET actual = actual + 200\n						WHERE accountcode = \'6005\'\n						AND period= \'0\'');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:37:49','admin','UPDATE chartdetails SET bfwd = bfwd + 200\n						WHERE accountcode = \'6005\'\n						AND period &gt; \'0\'');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:37:49','admin','UPDATE chartdetails SET actual = actual + 300\n						WHERE accountcode = \'6100\'\n						AND period= \'0\'');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:37:49','admin','UPDATE chartdetails SET bfwd = bfwd + 300\n						WHERE accountcode = \'6100\'\n						AND period &gt; \'0\'');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:37:49','admin','UPDATE gltrans SET posted = 1 WHERE periodno = \'0\' AND posted=0');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:37:49','admin','UPDATE chartdetails SET actual = actual + -500\n				WHERE accountcode = \'6150\'\n				AND period= \'0\'');
INSERT INTO `audittrail` VALUES ('2013-01-08 22:37:49','admin','UPDATE chartdetails SET bfwd = bfwd + -500\n				WHERE accountcode = \'6150\'\n				AND period &gt; \'0\'');
INSERT INTO `audittrail` VALUES ('2013-01-11 12:02:26','admin','INSERT INTO stockmaster (stockid,\n												description,\n												longdescription,\n												categoryid,\n												units,\n												mbflag,\n												eoq,\n												discontinued,\n												controlled,\n												serialised,\n												perishable,\n												volume,\n												kgs,\n												barcode,\n												discountcategory,\n												taxcatid,\n												decimalplaces,\n												shrinkfactor,\n												pansize)\n							VALUES (\'CONSULT\',\n								\'Web Consultancy\',\n								\'Web Consultancy\',\n								\'FOOD\',\n								\'each\',\n								\'D\',\n								\'0\',\n								\'0\',\n								\'0\',\n								\'0\',\n								\'0\',\n								\'0\',\n								\'0\',\n								\'\',\n								\'\',\n								\'1\',\n								\'0\',\n								\'0\',\n								\'0\')');
INSERT INTO `audittrail` VALUES ('2013-01-11 12:02:27','admin','INSERT INTO locstock (loccode,\n													stockid)\n										SELECT locations.loccode,\n										\'CONSULT\'\n										FROM locations');
INSERT INTO `audittrail` VALUES ('2013-01-11 12:03:37','admin','INSERT INTO stockcategory (categoryid,\n											stocktype,\n											categorydescription,\n											stockact,\n											adjglact,\n											issueglact,\n											purchpricevaract,\n											materialuseagevarac,\n											wipact)\n										VALUES (\n											\'CONSUL\',\n											\'L\',\n											\'Consultancy\',\n											\'1010\',\n											\'1\',\n											\'1\',\n											\'1\',\n											\'1\',\n											\'1010\')');
INSERT INTO `audittrail` VALUES ('2013-01-11 12:04:04','admin','UPDATE stockmaster\n						SET longdescription=\'Web Consultancy\',\n							description=\'Web Consultancy\',\n							discontinued=\'0\',\n							controlled=\'0\',\n							serialised=\'0\',\n							perishable=\'0\',\n							categoryid=\'CONSUL\',\n							units=\'each\',\n							mbflag=\'D\',\n							eoq=\'0\',\n							volume=\'0.0000\',\n							kgs=\'0.0000\',\n							barcode=\'\',\n							discountcategory=\'\',\n							taxcatid=\'1\',\n							decimalplaces=\'0\',\n							shrinkfactor=\'0\',\n							pansize=\'0\',\n							nextserialno=\'0\'\n					WHERE stockid=\'CONSULT\'');
INSERT INTO `audittrail` VALUES ('2013-01-11 12:04:05','admin','INSERT INTO gltrans (type,\n												typeno,\n												trandate,\n												periodno,\n												account,\n												narrative,\n												amount)\n										VALUES ( 0,\n												\'2\',\n												\'2013-01-11\',\n												\'1\',\n												\'1010\',\n												\'CONSULT Change stock category\',\n												\'0\')');
INSERT INTO `audittrail` VALUES ('2013-01-11 12:04:05','admin','INSERT INTO gltrans (type,\n												typeno,\n												trandate,\n												periodno,\n												account,\n												narrative,\n												amount)\n										VALUES ( 0,\n												\'2\',\n												\'2013-01-11\',\n												\'1\',\n												\'1460\',\n												\'CONSULT Change stock category\',\n												\'0\')');
INSERT INTO `audittrail` VALUES ('2013-01-11 12:04:28','admin','INSERT INTO unitsofmeasure (unitname )\n					VALUES (\'hours\')');
INSERT INTO `audittrail` VALUES ('2013-01-11 12:04:49','admin','UPDATE stockmaster\n						SET longdescription=\'Web Consultancy\',\n							description=\'Web Consultancy\',\n							discontinued=\'0\',\n							controlled=\'0\',\n							serialised=\'0\',\n							perishable=\'0\',\n							categoryid=\'CONSUL\',\n							units=\'hours\',\n							mbflag=\'D\',\n							eoq=\'0\',\n							volume=\'0.0000\',\n							kgs=\'0.0000\',\n							barcode=\'\',\n							discountcategory=\'\',\n							taxcatid=\'1\',\n							decimalplaces=\'2\',\n							shrinkfactor=\'0\',\n							pansize=\'0\',\n							nextserialno=\'0\'\n					WHERE stockid=\'CONSULT\'');
INSERT INTO `audittrail` VALUES ('2013-01-11 17:24:08','admin','INSERT INTO debtorsmaster (\n							debtorno,\n							name,\n							address1,\n							address2,\n							address3,\n							address4,\n							address5,\n							address6,\n							currcode,\n							clientsince,\n							holdreason,\n							paymentterms,\n							discount,\n							discountcode,\n							pymtdiscount,\n							creditlimit,\n							salestype,\n							invaddrbranch,\n							taxref,\n							customerpoline,\n							typeid)\n				VALUES (\'KAM001\',\n					\'Kampala Newspapers Incorporated\',\n					\'Nile Avenue\',\n					\'Kampala\',\n					\'\',\n					\'\',\n					\'\',\n					\'Uganda\',\n					\'KES\',\n					\'2013-01-11\',\n					\'1\',\n					\'20\',\n					\'0\',\n					\'\',\n					\'0\',\n					\'1000\',\n					\'EA\',\n					\'0\',\n					\'\',\n					\'0\',\n					\'3\'\n					)');
INSERT INTO `audittrail` VALUES ('2013-01-11 17:24:25','admin','INSERT INTO custbranch (branchcode,\n						debtorno,\n						brname,\n						braddress1,\n						braddress2,\n						braddress3,\n						braddress4,\n						braddress5,\n						braddress6,\n						lat,\n						lng,\n 						specialinstructions,\n						estdeliverydays,\n						fwddate,\n						salesman,\n						phoneno,\n						faxno,\n						contactname,\n						area,\n						email,\n						taxgroupid,\n						defaultlocation,\n						brpostaddr1,\n						brpostaddr2,\n						brpostaddr3,\n						brpostaddr4,\n						disabletrans,\n						defaultshipvia,\n						custbranchcode,\n						deliverblind)\n				VALUES (\'KAM001\',\n					\'KAM001\',\n					\'Kampala Newspapers Incorporated\',\n					\'Nile Avenue\',\n					\'Kampala\',\n					\'\',\n					\'\',\n					\'\',\n					\'Uganda\',\n					\'0\',\n					\'0\',\n					\'\',\n					\'0\',\n					\'0\',\n					\'IN\',\n					\'\',\n					\'\',\n					\'\',\n					\'CE\',\n					\'\',\n					\'1\',\n					\'MSA\',\n					\'\',\n					\'\',\n					\'\',\n					\'\',\n					\'\',\n					\'1\',\n					\'\',\n					\'1\'\n					)');
INSERT INTO `audittrail` VALUES ('2013-01-11 17:25:47','admin','DELETE FROM salesorderdetails\n									WHERE orderno=\'0\'\n									AND orderlineno=\'0\'');
INSERT INTO `audittrail` VALUES ('2013-01-11 17:26:28','admin','UPDATE debtorsmaster SET\n					name=\'Kampala Newspapers Incorporated\',\n					address1=\'Nile Avenue\',\n					address2=\'Kampala\',\n					address3=\'\',\n					address4=\'\',\n					address5=\'\',\n					address6=\'Uganda\',\n					currcode=\'UGX\',\n					clientsince=\'2013-01-11\',\n					holdreason=\'1\',\n					paymentterms=\'20\',\n					discount=\'0\',\n					discountcode=\'\',\n					pymtdiscount=\'0\',\n					creditlimit=\'1000\',\n					salestype = \'EA\',\n					invaddrbranch=\'0\',\n					taxref=\'\',\n					customerpoline=\'0\',\n					typeid=\'3\'\n				  WHERE debtorno = \'KAM001\'');
INSERT INTO `audittrail` VALUES ('2013-01-11 17:26:41','admin','UPDATE salesorderdetails SET quantity=75,\n															unitprice=70000,\n															discountpercent=0,\n															narrative =\'\',\n															itemdue = \'2013-01-11\',\n															poline = \'\'\n								WHERE orderno=0\n								AND orderlineno=1');
INSERT INTO `audittrail` VALUES ('2013-01-11 17:27:46','admin','UPDATE salesorderdetails SET quantity=75,\n															unitprice=70000,\n															discountpercent=0,\n															narrative =\'75 hours of consultancy on design and preparation of web site\',\n															itemdue = \'2013-01-11\',\n															poline = \'\'\n								WHERE orderno=0\n								AND orderlineno=1');
INSERT INTO `audittrail` VALUES ('2013-01-11 17:27:56','admin','INSERT INTO salesorders (\n								orderno,\n								debtorno,\n								branchcode,\n								customerref,\n								comments,\n								orddate,\n								ordertype,\n								shipvia,\n								deliverto,\n								deladd1,\n								deladd2,\n								deladd3,\n								deladd4,\n								deladd5,\n								deladd6,\n								contactphone,\n								contactemail,\n								salesperson,\n								freightcost,\n								fromstkloc,\n								deliverydate,\n								quotedate,\n								confirmeddate,\n								quotation,\n								deliverblind)\n							VALUES (\n								\'3\',\n								\'KAM001\',\n								\'KAM001\',\n								\'\',\n								\'\',\n								\'2013-01-11 17:27\',\n								\'EA\',\n								\'1\',\n								\'Kampala Newspapers Incorporated\',\n								\'Nile Avenue\',\n								\'Kampala\',\n								\'\',\n								\'\',\n								\'\',\n								\'Uganda\',\n								\'\',\n								\'\',\n								\'IN\',\n								\'0\',\n								\'MSA\',\n								\'2013-01-12\',\n								\'2013-01-12\',\n								\'2013-01-12\',\n								\'0\',\n								\'1\'\n								)');
INSERT INTO `audittrail` VALUES ('2013-01-11 17:27:57','admin','INSERT INTO salesorderdetails (\n											orderlineno,\n											orderno,\n											stkcode,\n											unitprice,\n											quantity,\n											discountpercent,\n											narrative,\n											poline,\n											itemdue)\n										VALUES (\n					\'1\',\n					\'3\',\n					\'CONSULT\',\n					\'70000\',\n					\'75\',\n					\'0\',\n					\'75 hours of consultancy on design and preparation of web site\',\n					\'\',\n					\'2013-01-11\'\n				)');
INSERT INTO `audittrail` VALUES ('2013-01-11 17:28:58','admin','UPDATE salesorderdetails SET quantity=75,\n															unitprice=70000,\n															discountpercent=0,\n															narrative =\'75 hours of consultancy on design and preparation of web site @ UGX70,000 per hour\',\n															itemdue = \'2013-01-11\',\n															poline = \'\'\n								WHERE orderno=3\n								AND orderlineno=1');
INSERT INTO `audittrail` VALUES ('2013-01-11 17:29:07','admin','UPDATE salesorders SET debtorno = \'KAM001\',\n										branchcode = \'KAM001\',\n										customerref = \'\',\n										comments = \'\',\n										ordertype = \'EA\',\n										shipvia = \'1\',\n										deliverydate = \'2013-01-12\',\n										quotedate = \'2013-01-12\',\n										confirmeddate = \'2013-01-12\',\n										deliverto = \'Kampala Newspapers Incorporated\',\n										deladd1 = \'Nile Avenue\',\n										deladd2 = \'Kampala\',\n										deladd3 = \'\',\n										deladd4 = \'\',\n										deladd5 = \'\',\n										deladd6 = \'Uganda\',\n										contactphone = \'\',\n										contactemail = \'\',\n										salesperson = \'IN\',\n										freightcost = \'0\',\n										fromstkloc = \'MSA\',\n										printedpackingslip = \'0\',\n										quotation = \'0\',\n										deliverblind = \'1\'\n						WHERE salesorders.orderno=\'3\'');
INSERT INTO `audittrail` VALUES ('2013-01-11 17:29:07','admin','UPDATE salesorderdetails SET unitprice=\'70000\',\n													quantity=\'75\',\n													discountpercent=\'0\',\n													completed=\'0\',\n													poline=\'\',\n													itemdue=\'2013-01-11\'\n						WHERE salesorderdetails.orderno=\'3\'\n						AND salesorderdetails.orderlineno=\'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-11 17:29:57','admin','UPDATE salesorders\n			SET comments = CONCAT(comments,\' Inv \',\'2\')\n			WHERE orderno= \'3\'');
INSERT INTO `audittrail` VALUES ('2013-01-11 17:29:57','admin','INSERT INTO debtortrans (transno,\n									type,\n									debtorno,\n									branchcode,\n									trandate,\n									inputdate,\n									prd,\n									reference,\n									tpe,\n									order_,\n									ovamount,\n									ovgst,\n									ovfreight,\n									rate,\n									invtext,\n									shipvia,\n									consignment )\n								VALUES (\n									\'2\',\n									10,\n									\'KAM001\',\n									\'KAM001\',\n									\'2013-01-12\',\n									\'2013-01-11 17-29-57\',\n									\'1\',\n									\'\',\n									\'EA\',\n									\'3\',\n									\'1400000\',\n									\'224000\',\n									\'0\',\n									\'26.923077030000005\',\n									\'Invoice for first 20 hours of web site consultancy\',\n									\'1\',\n									\'\'	)');
INSERT INTO `audittrail` VALUES ('2013-01-11 17:29:57','admin','INSERT INTO debtortranstaxes (debtortransid,\n											taxauthid,\n											taxamount)\n								VALUES (\'4\',\n									\'1\',\n									\'8319.9999669577\')');
INSERT INTO `audittrail` VALUES ('2013-01-11 17:29:57','admin','UPDATE salesorderdetails\n							SET qtyinvoiced = qtyinvoiced + 20,\n								actualdispatchdate = \'2013-01-12\'\n							WHERE orderno = \'3\'\n							AND orderlineno = \'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-11 17:29:57','admin','INSERT INTO stockmoves (stockid,\n												type,\n												transno,\n												loccode,\n												trandate,\n												debtorno,\n												branchcode,\n												price,\n												prd,\n												reference,\n												qty,\n												discountpercent,\n												standardcost,\n												narrative )\n											VALUES (\'CONSULT\',\n												10,\n												\'2\',\n												\'MSA\',\n												\'2013-01-12\',\n												\'KAM001\',\n												\'KAM001\',\n												\'2600\',\n												\'1\',\n												\'3\',\n												\'-20\',\n												\'0\',\n												\'0.0000\',\n												\'75 hours of consultancy on design and preparation of web site @ UGX70,000 per hour\')');
INSERT INTO `audittrail` VALUES ('2013-01-11 17:29:57','admin','INSERT INTO stockmovestaxes (stkmoveno,\n													taxauthid,\n													taxrate,\n													taxcalculationorder,\n													taxontax)\n										VALUES (\'3\',\n											\'1\',\n											\'0.16\',\n											\'0\',\n											\'0\')');
INSERT INTO `audittrail` VALUES ('2013-01-11 17:29:58','admin','INSERT INTO salesanalysis (typeabbrev,\n												periodno,\n												amt,\n												cost,\n												cust,\n												custbranch,\n												qty,\n												disc,\n												stockid,\n												area,\n												budgetoractual,\n												salesperson,\n												stkcategory )\n								SELECT \'EA\',\n										\'1\',\n										\'52000\',\n										\'0\',\n										\'KAM001\',\n										\'KAM001\',\n										\'20\',\n										\'0\',\n										\'CONSULT\',\n										custbranch.area,\n										1,\n										\'IN\',\n										stockmaster.categoryid\n								FROM stockmaster, custbranch\n								WHERE stockmaster.stockid = \'CONSULT\'\n								AND custbranch.debtorno = \'KAM001\'\n								AND custbranch.branchcode=\'KAM001\'');
INSERT INTO `audittrail` VALUES ('2013-01-11 17:29:58','admin','INSERT INTO gltrans (type,\n												typeno,\n												trandate,\n												periodno,\n												account,\n												narrative,\n												amount )\n										VALUES (\n											10,\n											\'2\',\n											\'2013-01-12\',\n											\'1\',\n											\'4100\',\n											\'KAM001 - CONSULT x 20 @ 70000\',\n											\'-52000\')');
INSERT INTO `audittrail` VALUES ('2013-01-11 17:29:58','admin','INSERT INTO gltrans (type,\n										typeno,\n										trandate,\n										periodno,\n										account,\n										narrative,\n										amount)\n									VALUES (\n										10,\n										\'2\',\n										\'2013-01-12\',\n										\'1\',\n										\'1100\',\n										\'KAM001\',\n										\'60320\')');
INSERT INTO `audittrail` VALUES ('2013-01-11 17:29:58','admin','INSERT INTO gltrans (type,\n											typeno,\n											trandate,\n											periodno,\n											account,\n											narrative,\n											amount)\n										VALUES (\n											10,\n											\'2\',\n											\'2013-01-12\',\n											\'1\',\n											\'2300\',\n											\'KAM001\',\n											\'-8320\')');
INSERT INTO `audittrail` VALUES ('2013-01-11 22:41:58','admin','UPDATE securitytokens\n				SET tokenname=\'User Management and System Administration\'\n			WHERE tokenid=\'15\'');
INSERT INTO `audittrail` VALUES ('2013-01-14 13:03:49','admin','INSERT INTO supptrans (transno,\n											type,\n											supplierno,\n											trandate,\n											inputdate,\n											suppreference,\n											rate,\n											ovamount,\n											transtext) valueS (\'1\',\n					22,\n					\'VOI001\',\n					\'2013-01-14\',\n					\'2013-01-14 13-03-49\',\n					\'Cash\',\n					\'1\',\n					\'-200\',\n					\'\'\n				)');
INSERT INTO `audittrail` VALUES ('2013-01-14 13:03:49','admin','UPDATE suppliers\n					SET	lastpaiddate = \'2013-01-14\',\n						lastpaid=\'200\'\n					WHERE suppliers.supplierid=\'VOI001\'');
INSERT INTO `audittrail` VALUES ('2013-01-14 13:03:49','admin','INSERT INTO gltrans ( type,\n											typeno,\n											trandate,\n											periodno,\n											account,\n											narrative,\n											amount) VALUES (22,\n								\'1\',\n								\'2013-01-14\',\n								\'1\',\n								\'2100\',\n								\'VOI001-\',\n								\'200\')');
INSERT INTO `audittrail` VALUES ('2013-01-14 13:03:49','admin','INSERT INTO gltrans ( type,\n											typeno,\n											trandate,\n											periodno,\n											account,\n											narrative,\n											amount)\n						VALUES (\'22\',\n								\'1\',\n								\'2013-01-14\',\n								\'1\',\n								\'1030\',\n								\'VOI001-\',\n								\'-200\')');
INSERT INTO `audittrail` VALUES ('2013-01-14 13:03:49','admin','INSERT INTO banktrans (transno,\n										type,\n										bankact,\n										ref,\n										exrate,\n										functionalexrate,\n										transdate,\n										banktranstype,\n										amount,\n										currcode)\n							VALUES (\'1\',\n									\'22\',\n									\'1030\',\n									\'VOI001-\',\n									\'1\',\n									\'1\',\n									\'2013-01-14\',\n									\'Cash\',\n									\'-200\',\n									\'KES\'\n								)');
INSERT INTO `audittrail` VALUES ('2013-01-14 17:08:09','admin','UPDATE www_users SET lastvisitdate=\'2013-01-14 17:08:09\'\n							WHERE www_users.userid=\'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-14 22:07:22','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'default\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-14 22:07:32','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'silverwolf\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-14 22:14:12','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'silverwolf\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-14 22:14:37','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'silverwolf\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-14 22:14:39','admin','UPDATE www_users\n			SET fontsize=\'2\'\n			WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-14 22:16:27','admin','UPDATE www_users\n			SET fontsize=\'2\'\n			WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-14 22:16:35','admin','UPDATE www_users\n			SET fontsize=\'2\'\n			WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-14 22:16:45','admin','UPDATE www_users\n			SET fontsize=\'2\'\n			WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-14 22:19:02','admin','UPDATE www_users\n			SET fontsize=\'2\'\n			WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-14 22:19:05','admin','UPDATE www_users\n			SET fontsize=\'1\'\n			WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-14 22:19:08','admin','UPDATE www_users\n			SET fontsize=\'0\'\n			WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-14 22:19:11','admin','UPDATE www_users\n			SET fontsize=\'1\'\n			WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-14 22:19:14','admin','UPDATE www_users\n			SET fontsize=\'2\'\n			WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-14 22:19:16','admin','UPDATE www_users\n			SET fontsize=\'1\'\n			WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-14 22:23:04','admin','UPDATE www_users\n			SET fontsize=\'0\'\n			WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-14 22:26:02','admin','UPDATE www_users\n						SET fontsize=\'2\'\n						WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-14 22:26:05','admin','UPDATE www_users\n						SET fontsize=\'1\'\n						WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-14 22:26:07','admin','UPDATE www_users\n						SET fontsize=\'0\'\n						WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-14 22:27:43','admin','UPDATE www_users\n						SET fontsize=\'1\'\n						WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-14 22:28:28','admin','UPDATE www_users\n						SET fontsize=\'0\'\n						WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 00:38:49','admin','UPDATE www_users\n						SET fontsize=\'1\'\n						WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 00:46:55','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'wood\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 00:47:20','admin','UPDATE www_users\n						SET fontsize=\'2\'\n						WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 00:47:23','admin','UPDATE www_users\n						SET fontsize=\'1\'\n						WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 00:47:31','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'fresh\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 00:47:37','admin','UPDATE www_users\n						SET fontsize=\'0\'\n						WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 00:47:43','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'aguapop\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'0\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 00:47:46','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'fluid\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'0\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 00:47:53','admin','UPDATE www_users\n						SET fontsize=\'1\'\n						WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 00:48:02','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'aguapop\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'1\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 00:48:09','admin','UPDATE www_users\n						SET fontsize=\'1\'\n						WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 01:14:54','admin','UPDATE www_users\n						SET fontsize=\'0\'\n						WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 01:15:02','admin','UPDATE www_users\n						SET fontsize=\'1\'\n						WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 01:18:49','admin','UPDATE www_users\n						SET fontsize=\'1\'\n						WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 01:23:55','admin','UPDATE www_users\n						SET fontsize=\'1\'\n						WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 01:24:10','admin','UPDATE www_users\n						SET fontsize=\'0\'\n						WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 01:33:59','admin','INSERT INTO purchdata (supplierno,\n								stockid,\n								price,\n								effectivefrom,\n								suppliersuom,\n								conversionfactor,\n								supplierdescription,\n								suppliers_partno,\n								leadtime,\n								minorderqty,\n								preferred)\n						VALUES (\'VOI001\',\n							\'FLOUR\',\n							\'10\',\n							\'2013-01-15\',\n							\'kgs\',\n							\'1\',\n							\'\',\n							\'\',\n							\'1\',							\'1\',\n							\'0\')');
INSERT INTO `audittrail` VALUES ('2013-01-15 10:17:13','admin','UPDATE www_users\n						SET fontsize=\'1\'\n						WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 10:19:00','admin','UPDATE purchdata SET price=\'10.0000\',\n								effectivefrom=\'2013-01-15\',\n								suppliersuom=\'kgs\',\n								conversionfactor=\'1\',\n								supplierdescription=\'\',\n								suppliers_partno=\'\',\n								leadtime=\'5\',\n								minorderqty=\'100\',\n								preferred=\'0\'\n							WHERE purchdata.stockid=\'FLOUR\'\n							AND purchdata.supplierno=\'VOI001\'\n							AND purchdata.effectivefrom=\'2013-01-15\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 14:09:26','admin','UPDATE www_users\n						SET fontsize=\'1\'\n						WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 14:09:30','admin','UPDATE www_users\n						SET fontsize=\'0\'\n						WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 14:49:46','admin','UPDATE www_users\n				SET displayrecordsmax=\'50\',\n					theme=\'aguapop\',\n					language=\'en_GB.utf8\',\n					email=\'info@kwamoja.com\',\n					pdflanguage=\'0\',\n					fontsize=\'0\'\n				WHERE userid = \'admin\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 16:57:06','admin','UPDATE stockmaster\n						SET longdescription=\'Chapati\',\n							description=\'Chapati\',\n							discontinued=\'0\',\n							controlled=\'0\',\n							serialised=\'0\',\n							perishable=\'1\',\n							categoryid=\'FOOD\',\n							units=\'each\',\n							mbflag=\'M\',\n							eoq=\'0\',\n							volume=\'0.0000\',\n							kgs=\'0.0000\',\n							barcode=\'\',\n							discountcategory=\'\',\n							taxcatid=\'4\',\n							decimalplaces=\'0\',\n							shrinkfactor=\'0\',\n							pansize=\'0\',\n							nextserialno=\'0\'\n					WHERE stockid=\'CHAPATI\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 16:57:06','admin','DELETE FROM stockitemproperties\n									WHERE stockid =\'CHAPATI\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 16:58:26','admin','INSERT INTO purchorders ( orderno,\n											supplierno,\n											comments,\n											orddate,\n											rate,\n											initiator,\n											requisitionno,\n											intostocklocation,\n											deladd1,\n											deladd2,\n											deladd3,\n											deladd4,\n											deladd5,\n											deladd6,\n											tel,\n											suppdeladdress1,\n											suppdeladdress2,\n											suppdeladdress3,\n											suppdeladdress4,\n											suppdeladdress5,\n											suppdeladdress6,\n											suppliercontact,\n											supptel,\n											contact,\n											version,\n											revised,\n											deliveryby,\n											status,\n											stat_comment,\n											deliverydate,\n											paymentterms,\n											allowprint)\n							VALUES(	\'2\',\n									\'VOI001\',\n									\'\',\n									\'2013-01-15\',\n									\'1\',\n									\'admin\',\n									\'\',\n									\'MSA\',\n									\' Ganjoni Road\',\n									\'\',\n									\'\',\n									\'\',\n									\'\',\n									\'\',\n									\'\',\n									\'PO Box 9999\',\n									\'\',\n									\'Voi\',\n									\'\',\n									\'\',\n									\'Kenya\',\n									\'\',\n									\'\',\n									\'\',\n									\'1\',\n									\'2013-01-15\',\n									\'1\',\n									\'Authorised\',\n									\'15/01/2013 - Order Created and Authorised by &lt;a href=&quot;mailto:info@kwamoja.com&quot;&gt;Demonstration user&lt;/a&gt;&lt;br /&gt;&lt;br /&gt;\',\n									\'2013-01-15\',\n									\'20\',\n									\'1\' )');
INSERT INTO `audittrail` VALUES ('2013-01-15 16:58:27','admin','INSERT INTO purchorderdetails (orderno,\n														itemcode,\n														deliverydate,\n														itemdescription,\n														glcode,\n														unitprice,\n														quantityord,\n														shiptref,\n														jobref,\n														suppliersunit,\n														suppliers_partno,\n														assetid,\n														conversionfactor )\n									VALUES (\'2\',\n											\'CHAPATI\',\n											\'2013-01-15\',\n											\'Chapati\',\n											\'1460\',\n											\'100\',\n											\'10\',\n											\'0\',\n											\'0\',\n											\'each\',\n											\'\',\n											\'0\',\n											\'1\')');
INSERT INTO `audittrail` VALUES ('2013-01-15 16:58:27','admin','INSERT INTO purchorderdetails (orderno,\n														itemcode,\n														deliverydate,\n														itemdescription,\n														glcode,\n														unitprice,\n														quantityord,\n														shiptref,\n														jobref,\n														suppliersunit,\n														suppliers_partno,\n														assetid,\n														conversionfactor )\n									VALUES (\'2\',\n											\'FLOUR\',\n											\'2013-01-20\',\n											\' - Maize Flour\',\n											\'1420\',\n											\'10\',\n											\'10\',\n											\'0\',\n											\'0\',\n											\'kgs\',\n											\'\',\n											\'0\',\n											\'1\')');
INSERT INTO `audittrail` VALUES ('2013-01-15 16:59:01','admin','UPDATE purchorders	SET	allowprint =  0,\n										dateprinted  = \'2013-01-15\',\n										status = \'Printed\',\n										stat_comment = \'15/01/2013 - Printed by &lt;a href=&quot;mailto:info@kwamoja.com&quot;&gt;Demonstration user&lt;/a&gt;&lt;br /&gt;15/01/2013 - Order Created and Authorised by &lt;a href=&quot;mailto:info@kwamoja.com&quot;&gt;Demonstration user&lt;/a&gt;&lt;br /&gt;&lt;br /&gt;\'\n				WHERE purchorders.orderno = \'2\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 16:59:34','admin','UPDATE purchorderdetails SET\n												quantityrecd = quantityrecd + \'1\',\n												stdcostunit=\'10\',\n												completed=\'0\'\n										WHERE podetailitem = \'2\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 16:59:34','admin','INSERT INTO grns (grnbatch,\n									podetailitem,\n									itemcode,\n									itemdescription,\n									deliverydate,\n									qtyrecd,\n									supplierid,\n									stdcostunit)\n							VALUES (\'1\',\n								\'2\',\n								\'CHAPATI\',\n								\'Chapati\',\n								\'2013-01-15\',\n								\'1\',\n								\'VOI001\',\n								\'10.0000\')');
INSERT INTO `audittrail` VALUES ('2013-01-15 16:59:34','admin','UPDATE locstock\n							SET quantity = locstock.quantity + \'1\'\n						WHERE locstock.stockid = \'CHAPATI\'\n						AND loccode = \'MSA\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 16:59:34','admin','INSERT INTO stockmoves (stockid,\n												type,\n												transno,\n												loccode,\n												trandate,\n												price,\n												prd,\n												reference,\n												qty,\n												standardcost,\n												newqoh)\n									VALUES (\n										\'CHAPATI\',\n										25,\n										\'1\',\n										\'MSA\',\n										\'2013-01-15\',\n										\'100\',\n										\'1\',\n										\'VOI001 (Voi Fruit and Vegetable) - 2\',\n										\'1\',\n										\'10\',\n										\'1\'\n										)');
INSERT INTO `audittrail` VALUES ('2013-01-15 16:59:35','admin','INSERT INTO gltrans (type,\n											typeno,\n											trandate,\n											periodno,\n											account,\n											narrative,\n											amount)\n									VALUES (\n										25,\n										\'1\',\n										\'2013-01-15\',\n										\'1\',\n										\'1460\',\n										\'PO: 2 VOI001 - CHAPATI - Chapati x 1 @ 10\',\n										\'10\'\n										)');
INSERT INTO `audittrail` VALUES ('2013-01-15 16:59:35','admin','INSERT INTO gltrans (type,\n											typeno,\n											trandate,\n											periodno,\n											account,\n											narrative,\n											amount)\n									VALUES (25,\n										\'1\',\n										\'2013-01-15\',\n										\'1\',\n										\'2150\',\n										\'PO1358269154: 2 VOI001 - CHAPATI - Chapati x 1 @ 10\',\n										\'-10\'\n										)');
INSERT INTO `audittrail` VALUES ('2013-01-15 16:59:35','admin','UPDATE purchorderdetails SET quantityrecd = quantityrecd + \'10\',\n													stdcostunit=\'0\',\n													completed=1\n						WHERE podetailitem = \'3\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 16:59:35','admin','INSERT INTO grns (grnbatch,\n									podetailitem,\n									itemcode,\n									itemdescription,\n									deliverydate,\n									qtyrecd,\n									supplierid,\n									stdcostunit)\n							VALUES (\'1\',\n								\'3\',\n								\'FLOUR\',\n								\' - Maize Flour\',\n								\'2013-01-15\',\n								\'10\',\n								\'VOI001\',\n								\'0.0000\')');
INSERT INTO `audittrail` VALUES ('2013-01-15 16:59:35','admin','UPDATE locstock\n							SET quantity = locstock.quantity + \'10\'\n						WHERE locstock.stockid = \'FLOUR\'\n						AND loccode = \'MSA\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 16:59:35','admin','INSERT INTO stockmoves (stockid,\n												type,\n												transno,\n												loccode,\n												trandate,\n												price,\n												prd,\n												reference,\n												qty,\n												standardcost,\n												newqoh)\n									VALUES (\n										\'FLOUR\',\n										25,\n										\'1\',\n										\'MSA\',\n										\'2013-01-15\',\n										\'10\',\n										\'1\',\n										\'VOI001 (Voi Fruit and Vegetable) - 2\',\n										\'10\',\n										\'0\',\n										\'10\'\n										)');
INSERT INTO `audittrail` VALUES ('2013-01-15 16:59:35','admin','INSERT INTO gltrans (type,\n											typeno,\n											trandate,\n											periodno,\n											account,\n											narrative,\n											amount)\n									VALUES (\n										25,\n										\'1\',\n										\'2013-01-15\',\n										\'1\',\n										\'1420\',\n										\'PO: 2 VOI001 - FLOUR -  - Maize Flour x 10 @ 0\',\n										\'0\'\n										)');
INSERT INTO `audittrail` VALUES ('2013-01-15 16:59:35','admin','INSERT INTO gltrans (type,\n											typeno,\n											trandate,\n											periodno,\n											account,\n											narrative,\n											amount)\n									VALUES (25,\n										\'1\',\n										\'2013-01-15\',\n										\'1\',\n										\'2150\',\n										\'PO1358269154: 2 VOI001 - FLOUR -  - Maize Flour x 10 @ 0\',\n										\'0\'\n										)');
INSERT INTO `audittrail` VALUES ('2013-01-15 17:00:19','admin','INSERT INTO gltrans (type,\n													typeno,\n													trandate,\n													periodno,\n													account,\n													narrative,\n													amount)\n								VALUES (\'20\',\n									\'1\',\n									\'2013-01-14\',\n									\'1\',\n									\'2150\',\n									\'VOI001 - GRN 1 - CHAPATI x 1 @  std cost of 10\',\n								 	\'10\')');
INSERT INTO `audittrail` VALUES ('2013-01-15 17:00:19','admin','INSERT INTO gltrans (type,\n															typeno,\n															trandate,\n															periodno,\n															account,\n															narrative,\n															amount)\n													VALUES (20,\n													\'1\',\n													\'2013-01-14\',\n													\'1\',\n													\'1460\',\n													\'VOI001 - Average Cost Adj - CHAPATI x 1 x 90\',\n													\'90\')');
INSERT INTO `audittrail` VALUES ('2013-01-15 17:00:19','admin','INSERT INTO gltrans (type,\n															typeno,\n															trandate,\n															periodno,\n															account,\n															narrative,\n															amount)\n													VALUES (20,\n													\'1\',\n													\'2013-01-14\',\n													\'1\',\n													\'1420\',\n													\'VOI001 - Average Cost Adj - FLOUR x 10 x 10\',\n													\'100\')');
INSERT INTO `audittrail` VALUES ('2013-01-15 17:00:19','admin','INSERT INTO gltrans (type,\n												typeno,\n												trandate,\n												periodno,\n												account,\n												narrative,\n												amount)\n										VALUES (20,\n												\'1\',\n												\'2013-01-14\',\n												\'1\',\n												\'2310\',\n												\'VOI001 - Inv 12345 Kenya Revenue Authority 16.00% KES32 @ exch rate 1\',\n												\'32\')');
INSERT INTO `audittrail` VALUES ('2013-01-15 17:00:19','admin','INSERT INTO gltrans (type,\n										typeno,\n										trandate,\n										periodno,\n										account,\n										narrative,\n										amount)\n								VALUES (20,\n									\'1\',\n									\'2013-01-14\',\n									\'1\',\n									\'2100\',\n									\'VOI001 - Inv 12345 KES232 @ a rate of 1\',\n									\'-232\')');
INSERT INTO `audittrail` VALUES ('2013-01-15 17:00:19','admin','INSERT INTO supptrans (transno,\n										type,\n										supplierno,\n										suppreference,\n										trandate,\n										duedate,\n										ovamount,\n										ovgst,\n										rate,\n										transtext,\n										inputdate)\n							VALUES (\n								\'1\',\n								20 ,\n								\'VOI001\',\n								\'12345\',\n								\'2013-01-14\',\n								\'2013-02-22\',\n								\'200\',\n								\'32\',\n								\'1\',\n								\'\',\n								\'2013-01-15\')');
INSERT INTO `audittrail` VALUES ('2013-01-15 17:00:19','admin','INSERT INTO supptranstaxes (supptransid,\n												taxauthid,\n												taxamount)\n									VALUES (\n										\'2\',\n										\'1\',\n										\'32\')');
INSERT INTO `audittrail` VALUES ('2013-01-15 17:00:19','admin','UPDATE purchorderdetails\n					SET qtyinvoiced = qtyinvoiced + 1,\n						actprice = \'100\'\n					WHERE podetailitem = \'2\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 17:00:19','admin','UPDATE grns\n					SET quantityinv = quantityinv + 1\n					WHERE grnno = \'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 17:00:19','admin','UPDATE stockmoves SET price = \'100\'\n											WHERE stockid=\'CHAPATI\'\n											AND type=25\n											AND loccode=\'MSA\'\n											AND transno=\'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 17:00:19','admin','UPDATE stockmaster\n										SET lastcost=materialcost+overheadcost+labourcost,\n										materialcost=materialcost+90\n										WHERE stockid=\'CHAPATI\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 17:00:19','admin','UPDATE purchorderdetails\n					SET qtyinvoiced = qtyinvoiced + 10,\n						actprice = \'10\'\n					WHERE podetailitem = \'3\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 17:00:19','admin','UPDATE grns\n					SET quantityinv = quantityinv + 10\n					WHERE grnno = \'2\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 17:00:19','admin','UPDATE stockmoves SET price = \'10\'\n											WHERE stockid=\'FLOUR\'\n											AND type=25\n											AND loccode=\'MSA\'\n											AND transno=\'1\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 17:00:19','admin','UPDATE stockmaster\n										SET lastcost=materialcost+overheadcost+labourcost,\n										materialcost=materialcost+10\n										WHERE stockid=\'FLOUR\'');
INSERT INTO `audittrail` VALUES ('2013-01-15 22:22:36','admin','INSERT INTO gltrans (type,\n											typeno,\n											trandate,\n											periodno,\n											account,\n											narrative,\n											tag,\n											amount)\n									VALUES (20,\n										\'2\',\n										\'2013-01-14\',\n										\'1\',\n										\'6100\',\n										\'VOI001 \',\n										\'0\',\n										\'12340\')');
INSERT INTO `audittrail` VALUES ('2013-01-15 22:22:36','admin','INSERT INTO gltrans (type,\n												typeno,\n												trandate,\n												periodno,\n												account,\n												narrative,\n												amount)\n										VALUES (20,\n												\'2\',\n												\'2013-01-14\',\n												\'1\',\n												\'2310\',\n												\'VOI001 - Inv 666 Kenya Revenue Authority 16.00% KES1974.4 @ exch rate 1\',\n												\'1974.4\')');
INSERT INTO `audittrail` VALUES ('2013-01-15 22:22:36','admin','INSERT INTO gltrans (type,\n										typeno,\n										trandate,\n										periodno,\n										account,\n										narrative,\n										amount)\n								VALUES (20,\n									\'2\',\n									\'2013-01-14\',\n									\'1\',\n									\'2100\',\n									\'VOI001 - Inv 666 KES14,314 @ a rate of 1\',\n									\'-14314.4\')');
INSERT INTO `audittrail` VALUES ('2013-01-15 22:22:37','admin','INSERT INTO supptrans (transno,\n										type,\n										supplierno,\n										suppreference,\n										trandate,\n										duedate,\n										ovamount,\n										ovgst,\n										rate,\n										transtext,\n										inputdate)\n							VALUES (\n								\'2\',\n								20 ,\n								\'VOI001\',\n								\'666\',\n								\'2013-01-14\',\n								\'2013-02-22\',\n								\'12340\',\n								\'1974.4\',\n								\'1\',\n								\'\',\n								\'2013-01-15\')');
INSERT INTO `audittrail` VALUES ('2013-01-15 22:22:37','admin','INSERT INTO supptranstaxes (supptransid,\n												taxauthid,\n												taxamount)\n									VALUES (\n										\'3\',\n										\'1\',\n										\'1974.4\')');
INSERT INTO `audittrail` VALUES ('2013-01-21 10:50:40','admin','UPDATE chartmaster SET group_=\'\' WHERE group_=\'\'');
INSERT INTO `audittrail` VALUES ('2013-01-21 10:50:40','admin','UPDATE chartmaster SET group_=\'\' WHERE group_=\'\'');
INSERT INTO `audittrail` VALUES ('2013-01-21 11:38:13','admin','UPDATE accountgroups SET groupname=\'Fixed Assets\',\n										sectioninaccounts=\'10\',\n										pandl=\'0\',\n										sequenceintb=\'501\',\n										parentgroupname=\'\'\n									WHERE groupname = \'Fixed Assets\'');
INSERT INTO `audittrail` VALUES ('2013-01-21 11:38:22','admin','UPDATE accountgroups SET groupname=\'Fixed Assets\',\n										sectioninaccounts=\'10\',\n										pandl=\'0\',\n										sequenceintb=\'500\',\n										parentgroupname=\'\'\n									WHERE groupname = \'Fixed Assets\'');
INSERT INTO `audittrail` VALUES ('2013-01-21 11:43:06','admin','UPDATE chartmaster SET group_=\'Operating Overheads\' WHERE group_=\'Operating Expenses\'');
INSERT INTO `audittrail` VALUES ('2013-01-21 11:43:06','admin','UPDATE accountgroups SET groupname=\'Operating Overheads\',\n										sectioninaccounts=\'5\',\n										pandl=\'1\',\n										sequenceintb=\'7000\',\n										parentgroupname=\'\'\n									WHERE groupname = \'Operating Expenses\'');
INSERT INTO `audittrail` VALUES ('2013-01-21 11:49:09','admin','UPDATE chartmaster SET group_=\'Promotion Overheads\' WHERE group_=\'Promotions\'');
INSERT INTO `audittrail` VALUES ('2013-01-21 11:49:09','admin','UPDATE accountgroups SET parentgroupname=\'Promotion Overheads\' WHERE parentgroupname=\'Promotions\'');
INSERT INTO `audittrail` VALUES ('2013-01-21 11:49:10','admin','UPDATE accountgroups SET groupname=\'Promotion Overheads\',\n										sectioninaccounts=\'5\',\n										pandl=\'1\',\n										sequenceintb=\'6000\',\n										parentgroupname=\'Marketing Expenses\'\n									WHERE groupname = \'Promotions\'');

--
-- Dumping data for table `bankaccounts`
--

INSERT INTO `bankaccounts` VALUES ('1030','KES',1,'','Cheque Account','1','');
INSERT INTO `bankaccounts` VALUES ('1040','KES',0,'','Savings Account','2','');

--
-- Dumping data for table `banktrans`
--

INSERT INTO `banktrans` VALUES (1,12,1,'1030','',100,1,1,'2013-01-04','Cash',100,'KES');
INSERT INTO `banktrans` VALUES (2,12,2,'1030','',0,1,1,'2013-01-05','Cash',280,'KES');
INSERT INTO `banktrans` VALUES (3,22,1,'1030','VOI001-',0,1,1,'2013-01-14','Cash',-200,'KES');

--
-- Dumping data for table `bom`
--

INSERT INTO `bom` VALUES ('CHAPATI','FLOUR','KIT','MSA','2012-12-14','2032-12-15',0.45,0);
INSERT INTO `bom` VALUES ('CHAPATI','WATER','KIT','MSA','2012-12-14','2032-12-15',0.25,0);

--
-- Dumping data for table `chartdetails`
--

INSERT INTO `chartdetails` VALUES ('1',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1010',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1010',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1010',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1010',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1010',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1010',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1010',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1010',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1010',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1010',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1010',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1010',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1020',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1020',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1020',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1020',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1020',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1020',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1020',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1020',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1020',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1020',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1020',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1020',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1030',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1030',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1030',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1030',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1030',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1030',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1030',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1030',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1030',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1030',1,0,380,0,0);
INSERT INTO `chartdetails` VALUES ('1030',2,0,0,380,0);
INSERT INTO `chartdetails` VALUES ('1030',3,0,0,380,0);
INSERT INTO `chartdetails` VALUES ('1040',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1040',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1040',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1040',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1040',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1040',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1040',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1040',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1040',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1040',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1040',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1040',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1050',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1050',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1050',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1050',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1050',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1050',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1050',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1050',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1050',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1050',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1050',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1050',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1060',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1060',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1060',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1060',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1060',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1060',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1060',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1060',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1060',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1060',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1060',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1060',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1070',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1070',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1070',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1070',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1070',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1070',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1070',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1070',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1070',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1070',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1070',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1070',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1080',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1080',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1080',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1080',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1080',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1080',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1080',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1080',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1080',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1080',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1080',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1080',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1090',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1090',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1090',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1090',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1090',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1090',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1090',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1090',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1090',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1090',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1090',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1090',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1100',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1100',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1100',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1100',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1100',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1100',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1100',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1100',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1100',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1100',1,0,200,0,0);
INSERT INTO `chartdetails` VALUES ('1100',2,0,0,200,0);
INSERT INTO `chartdetails` VALUES ('1100',3,0,0,200,0);
INSERT INTO `chartdetails` VALUES ('1150',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1150',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1150',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1150',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1150',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1150',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1150',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1150',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1150',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1150',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1150',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1150',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1200',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1200',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1200',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1200',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1200',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1200',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1200',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1200',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1200',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1200',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1200',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1200',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1250',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1250',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1250',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1250',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1250',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1250',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1250',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1250',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1250',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1250',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1250',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1250',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1300',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1300',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1300',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1300',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1300',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1300',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1300',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1300',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1300',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1300',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1300',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1300',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1350',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1350',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1350',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1350',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1350',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1350',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1350',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1350',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1350',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1350',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1350',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1350',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1400',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1400',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1400',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1400',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1400',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1400',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1400',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1400',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1400',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1400',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1400',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1400',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1420',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1420',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1420',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1420',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1420',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1420',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1420',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1420',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1420',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1420',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1420',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1420',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1440',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1440',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1440',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1440',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1440',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1440',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1440',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1440',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1440',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1440',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1440',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1440',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1460',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1460',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1460',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1460',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1460',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1460',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1460',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1460',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1460',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1460',1,0,-500,0,0);
INSERT INTO `chartdetails` VALUES ('1460',2,0,0,-500,0);
INSERT INTO `chartdetails` VALUES ('1460',3,0,0,-500,0);
INSERT INTO `chartdetails` VALUES ('1500',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1500',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1500',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1500',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1500',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1500',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1500',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1500',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1500',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1500',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1500',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1500',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1550',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1550',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1550',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1550',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1550',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1550',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1550',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1550',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1550',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1550',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1550',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1550',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1600',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1600',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1600',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1600',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1600',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1600',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1600',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1600',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1600',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1600',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1600',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1600',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1620',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1620',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1620',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1620',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1620',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1620',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1620',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1620',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1620',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1620',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1620',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1620',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1650',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1650',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1650',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1650',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1650',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1650',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1650',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1650',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1650',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1650',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1650',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1650',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1670',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1670',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1670',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1670',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1670',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1670',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1670',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1670',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1670',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1670',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1670',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1670',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1700',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1700',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1700',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1700',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1700',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1700',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1700',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1700',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1700',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1700',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1700',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1700',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1710',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1710',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1710',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1710',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1710',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1710',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1710',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1710',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1710',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1710',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1710',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1710',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1720',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1720',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1720',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1720',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1720',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1720',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1720',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1720',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1720',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1720',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1720',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1720',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1730',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1730',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1730',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1730',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1730',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1730',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1730',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1730',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1730',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1730',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1730',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1730',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1740',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1740',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1740',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1740',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1740',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1740',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1740',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1740',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1740',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1740',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1740',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1740',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1750',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1750',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1750',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1750',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1750',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1750',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1750',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1750',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1750',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1750',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1750',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1750',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1760',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1760',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1760',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1760',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1760',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1760',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1760',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1760',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1760',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1760',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1760',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1760',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1770',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1770',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1770',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1770',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1770',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1770',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1770',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1770',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1770',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1770',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1770',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1770',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1780',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1780',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1780',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1780',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1780',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1780',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1780',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1780',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1780',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1780',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1780',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1780',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1790',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1790',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1790',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1790',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1790',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1790',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1790',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1790',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1790',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1790',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1790',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1790',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1800',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1800',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1800',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1800',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1800',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1800',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1800',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1800',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1800',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1800',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1800',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1800',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1850',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1850',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1850',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1850',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1850',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1850',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1850',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1850',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1850',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1850',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1850',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1850',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1900',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1900',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1900',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1900',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1900',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1900',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1900',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1900',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1900',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1900',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1900',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('1900',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2010',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2010',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2010',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2010',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2010',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2010',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2010',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2010',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2010',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2010',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2010',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2010',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2020',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2020',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2020',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2020',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2020',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2020',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2020',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2020',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2020',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2020',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2020',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2020',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2050',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2050',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2050',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2050',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2050',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2050',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2050',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2050',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2050',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2050',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2050',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2050',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2100',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2100',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2100',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2100',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2100',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2100',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2100',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2100',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2100',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2100',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2100',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2100',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2150',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2150',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2150',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2150',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2150',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2150',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2150',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2150',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2150',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2150',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2150',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2150',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2200',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2200',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2200',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2200',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2200',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2200',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2200',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2200',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2200',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2200',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2200',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2200',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2230',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2230',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2230',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2230',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2230',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2230',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2230',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2230',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2230',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2230',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2230',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2230',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2250',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2250',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2250',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2250',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2250',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2250',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2250',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2250',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2250',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2250',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2250',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2250',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2300',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2300',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2300',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2300',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2300',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2300',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2300',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2300',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2300',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2300',1,0,-80,0,0);
INSERT INTO `chartdetails` VALUES ('2300',2,0,0,-80,0);
INSERT INTO `chartdetails` VALUES ('2300',3,0,0,-80,0);
INSERT INTO `chartdetails` VALUES ('2310',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2310',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2310',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2310',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2310',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2310',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2310',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2310',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2310',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2310',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2310',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2310',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2320',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2320',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2320',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2320',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2320',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2320',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2320',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2320',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2320',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2320',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2320',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2320',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2330',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2330',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2330',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2330',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2330',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2330',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2330',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2330',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2330',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2330',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2330',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2330',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2340',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2340',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2340',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2340',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2340',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2340',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2340',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2340',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2340',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2340',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2340',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2340',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2350',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2350',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2350',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2350',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2350',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2350',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2350',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2350',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2350',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2350',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2350',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2350',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2360',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2360',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2360',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2360',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2360',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2360',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2360',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2360',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2360',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2360',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2360',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2360',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2400',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2400',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2400',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2400',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2400',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2400',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2400',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2400',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2400',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2400',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2400',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2400',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2410',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2410',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2410',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2410',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2410',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2410',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2410',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2410',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2410',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2410',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2410',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2410',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2420',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2420',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2420',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2420',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2420',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2420',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2420',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2420',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2420',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2420',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2420',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2420',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2450',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2450',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2450',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2450',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2450',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2450',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2450',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2450',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2450',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2450',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2450',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2450',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2460',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2460',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2460',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2460',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2460',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2460',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2460',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2460',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2460',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2460',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2460',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2460',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2470',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2470',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2470',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2470',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2470',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2470',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2470',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2470',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2470',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2470',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2470',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2470',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2480',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2480',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2480',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2480',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2480',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2480',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2480',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2480',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2480',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2480',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2480',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2480',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2500',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2500',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2500',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2500',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2500',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2500',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2500',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2500',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2500',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2500',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2500',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2500',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2550',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2550',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2550',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2550',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2550',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2550',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2550',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2550',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2550',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2550',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2550',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2550',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2560',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2560',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2560',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2560',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2560',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2560',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2560',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2560',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2560',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2560',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2560',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2560',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2600',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2600',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2600',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2600',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2600',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2600',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2600',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2600',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2600',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2600',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2600',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2600',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2700',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2700',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2700',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2700',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2700',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2700',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2700',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2700',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2700',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2700',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2700',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2700',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2720',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2720',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2720',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2720',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2720',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2720',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2720',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2720',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2720',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2720',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2720',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2720',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2740',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2740',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2740',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2740',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2740',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2740',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2740',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2740',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2740',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2740',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2740',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2740',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2760',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2760',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2760',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2760',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2760',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2760',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2760',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2760',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2760',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2760',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2760',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2760',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2800',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2800',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2800',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2800',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2800',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2800',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2800',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2800',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2800',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2800',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2800',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2800',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2900',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2900',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2900',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2900',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2900',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2900',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2900',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2900',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2900',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2900',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2900',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('2900',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3100',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3100',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3100',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3100',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3100',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3100',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3100',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3100',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3100',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3100',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3100',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3100',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3200',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3200',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3200',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3200',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3200',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3200',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3200',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3200',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3200',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3200',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3200',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3200',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3300',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3300',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3300',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3300',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3300',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3300',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3300',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3300',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3300',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3300',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3300',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3300',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3400',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3400',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3400',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3400',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3400',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3400',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3400',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3400',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3400',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3400',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3400',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3400',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3500',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3500',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3500',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3500',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3500',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3500',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3500',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3500',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3500',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3500',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3500',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('3500',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4100',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4100',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4100',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4100',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4100',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4100',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4100',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4100',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4100',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4100',1,0,-500,0,0);
INSERT INTO `chartdetails` VALUES ('4100',2,0,0,-500,0);
INSERT INTO `chartdetails` VALUES ('4100',3,0,0,-500,0);
INSERT INTO `chartdetails` VALUES ('4200',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4200',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4200',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4200',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4200',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4200',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4200',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4200',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4200',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4200',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4200',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4200',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4500',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4500',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4500',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4500',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4500',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4500',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4500',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4500',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4500',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4500',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4500',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4500',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4600',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4600',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4600',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4600',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4600',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4600',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4600',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4600',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4600',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4600',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4600',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4600',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4700',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4700',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4700',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4700',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4700',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4700',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4700',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4700',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4700',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4700',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4700',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4700',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4800',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4800',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4800',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4800',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4800',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4800',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4800',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4800',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4800',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4800',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4800',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4800',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4900',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4900',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4900',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4900',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4900',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4900',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4900',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4900',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4900',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4900',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4900',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('4900',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5000',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5000',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5000',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5000',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5000',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5000',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5000',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5000',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5000',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5000',1,0,500,0,0);
INSERT INTO `chartdetails` VALUES ('5000',2,0,0,500,0);
INSERT INTO `chartdetails` VALUES ('5000',3,0,0,500,0);
INSERT INTO `chartdetails` VALUES ('5100',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5100',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5100',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5100',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5100',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5100',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5100',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5100',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5100',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5100',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5100',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5100',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5200',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5200',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5200',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5200',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5200',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5200',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5200',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5200',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5200',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5200',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5200',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5200',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5500',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5500',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5500',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5500',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5500',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5500',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5500',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5500',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5500',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5500',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5500',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5500',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5600',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5600',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5600',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5600',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5600',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5600',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5600',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5600',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5600',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5600',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5600',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5600',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5700',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5700',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5700',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5700',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5700',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5700',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5700',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5700',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5700',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5700',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5700',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5700',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5800',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5800',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5800',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5800',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5800',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5800',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5800',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5800',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5800',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5800',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5800',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5800',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5900',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5900',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5900',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5900',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5900',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5900',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5900',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5900',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5900',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5900',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5900',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('5900',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6005',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6005',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6005',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6005',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6005',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6005',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6005',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6005',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6005',0,0,200,0,0);
INSERT INTO `chartdetails` VALUES ('6005',1,0,0,200,0);
INSERT INTO `chartdetails` VALUES ('6005',2,0,0,200,0);
INSERT INTO `chartdetails` VALUES ('6005',3,0,0,200,0);
INSERT INTO `chartdetails` VALUES ('6100',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6100',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6100',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6100',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6100',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6100',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6100',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6100',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6100',0,0,300,0,0);
INSERT INTO `chartdetails` VALUES ('6100',1,0,0,300,0);
INSERT INTO `chartdetails` VALUES ('6100',2,0,0,300,0);
INSERT INTO `chartdetails` VALUES ('6100',3,0,0,300,0);
INSERT INTO `chartdetails` VALUES ('6150',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6150',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6150',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6150',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6150',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6150',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6150',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6150',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6150',0,0,-500,0,0);
INSERT INTO `chartdetails` VALUES ('6150',1,0,0,-500,0);
INSERT INTO `chartdetails` VALUES ('6150',2,0,0,-500,0);
INSERT INTO `chartdetails` VALUES ('6150',3,0,0,-500,0);
INSERT INTO `chartdetails` VALUES ('6200',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6200',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6200',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6200',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6200',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6200',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6200',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6200',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6200',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6200',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6200',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6200',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6250',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6250',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6250',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6250',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6250',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6250',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6250',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6250',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6250',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6250',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6250',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6250',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6300',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6300',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6300',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6300',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6300',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6300',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6300',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6300',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6300',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6300',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6300',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6300',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6400',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6400',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6400',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6400',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6400',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6400',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6400',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6400',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6400',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6400',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6400',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6400',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6500',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6500',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6500',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6500',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6500',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6500',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6500',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6500',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6500',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6500',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6500',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6500',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6550',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6550',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6550',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6550',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6550',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6550',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6550',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6550',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6550',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6550',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6550',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6550',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6590',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6590',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6590',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6590',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6590',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6590',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6590',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6590',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6590',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6590',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6590',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6590',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6600',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6600',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6600',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6600',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6600',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6600',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6600',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6600',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6600',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6600',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6600',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6600',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6700',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6700',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6700',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6700',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6700',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6700',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6700',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6700',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6700',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6700',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6700',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6700',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6800',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6800',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6800',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6800',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6800',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6800',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6800',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6800',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6800',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6800',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6800',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6800',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6900',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6900',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6900',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6900',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6900',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6900',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6900',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6900',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6900',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6900',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6900',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('6900',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7020',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7020',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7020',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7020',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7020',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7020',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7020',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7020',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7020',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7020',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7020',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7020',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7030',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7030',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7030',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7030',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7030',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7030',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7030',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7030',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7030',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7030',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7030',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7030',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7040',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7040',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7040',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7040',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7040',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7040',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7040',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7040',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7040',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7040',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7040',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7040',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7050',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7050',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7050',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7050',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7050',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7050',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7050',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7050',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7050',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7050',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7050',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7050',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7060',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7060',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7060',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7060',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7060',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7060',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7060',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7060',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7060',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7060',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7060',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7060',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7070',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7070',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7070',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7070',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7070',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7070',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7070',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7070',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7070',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7070',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7070',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7070',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7080',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7080',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7080',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7080',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7080',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7080',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7080',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7080',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7080',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7080',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7080',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7080',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7090',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7090',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7090',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7090',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7090',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7090',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7090',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7090',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7090',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7090',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7090',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7090',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7100',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7100',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7100',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7100',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7100',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7100',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7100',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7100',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7100',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7100',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7100',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7100',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7150',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7150',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7150',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7150',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7150',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7150',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7150',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7150',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7150',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7150',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7150',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7150',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7200',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7200',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7200',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7200',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7200',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7200',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7200',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7200',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7200',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7200',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7200',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7200',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7210',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7210',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7210',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7210',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7210',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7210',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7210',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7210',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7210',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7210',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7210',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7210',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7220',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7220',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7220',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7220',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7220',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7220',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7220',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7220',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7220',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7220',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7220',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7220',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7230',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7230',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7230',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7230',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7230',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7230',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7230',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7230',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7230',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7230',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7230',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7230',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7240',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7240',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7240',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7240',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7240',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7240',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7240',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7240',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7240',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7240',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7240',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7240',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7260',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7260',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7260',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7260',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7260',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7260',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7260',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7260',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7260',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7260',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7260',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7260',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7280',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7280',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7280',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7280',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7280',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7280',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7280',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7280',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7280',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7280',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7280',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7280',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7300',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7300',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7300',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7300',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7300',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7300',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7300',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7300',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7300',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7300',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7300',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7300',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7350',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7350',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7350',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7350',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7350',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7350',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7350',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7350',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7350',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7350',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7350',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7350',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7390',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7390',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7390',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7390',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7390',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7390',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7390',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7390',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7390',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7390',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7390',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7390',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7400',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7400',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7400',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7400',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7400',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7400',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7400',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7400',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7400',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7400',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7400',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7400',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7450',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7450',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7450',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7450',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7450',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7450',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7450',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7450',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7450',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7450',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7450',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7450',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7500',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7500',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7500',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7500',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7500',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7500',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7500',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7500',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7500',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7500',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7500',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7500',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7550',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7550',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7550',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7550',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7550',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7550',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7550',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7550',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7550',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7550',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7550',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7550',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7600',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7600',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7600',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7600',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7600',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7600',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7600',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7600',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7600',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7600',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7600',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7600',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7610',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7610',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7610',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7610',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7610',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7610',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7610',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7610',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7610',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7610',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7610',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7610',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7620',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7620',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7620',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7620',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7620',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7620',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7620',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7620',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7620',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7620',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7620',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7620',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7630',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7630',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7630',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7630',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7630',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7630',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7630',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7630',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7630',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7630',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7630',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7630',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7640',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7640',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7640',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7640',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7640',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7640',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7640',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7640',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7640',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7640',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7640',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7640',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7650',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7650',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7650',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7650',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7650',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7650',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7650',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7650',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7650',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7650',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7650',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7650',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7660',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7660',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7660',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7660',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7660',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7660',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7660',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7660',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7660',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7660',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7660',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7660',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7700',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7700',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7700',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7700',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7700',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7700',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7700',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7700',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7700',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7700',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7700',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7700',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7750',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7750',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7750',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7750',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7750',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7750',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7750',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7750',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7750',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7750',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7750',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7750',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7800',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7800',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7800',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7800',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7800',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7800',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7800',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7800',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7800',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7800',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7800',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7800',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7900',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7900',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7900',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7900',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7900',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7900',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7900',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7900',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7900',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7900',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7900',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('7900',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8100',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8100',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8100',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8100',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8100',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8100',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8100',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8100',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8100',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8100',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8100',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8100',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8200',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8200',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8200',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8200',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8200',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8200',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8200',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8200',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8200',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8200',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8200',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8200',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8300',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8300',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8300',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8300',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8300',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8300',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8300',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8300',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8300',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8300',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8300',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8300',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8400',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8400',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8400',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8400',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8400',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8400',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8400',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8400',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8400',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8400',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8400',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8400',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8500',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8500',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8500',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8500',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8500',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8500',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8500',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8500',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8500',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8500',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8500',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8500',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8600',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8600',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8600',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8600',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8600',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8600',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8600',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8600',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8600',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8600',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8600',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8600',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8900',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8900',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8900',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8900',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8900',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8900',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8900',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8900',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8900',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8900',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8900',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('8900',3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('9100',-8,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('9100',-7,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('9100',-6,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('9100',-5,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('9100',-4,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('9100',-3,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('9100',-2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('9100',-1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('9100',0,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('9100',1,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('9100',2,0,0,0,0);
INSERT INTO `chartdetails` VALUES ('9100',3,0,0,0,0);

--
-- Dumping data for table `chartmaster`
--

INSERT INTO `chartmaster` VALUES ('1','Default Sales/Discounts','Sales');
INSERT INTO `chartmaster` VALUES ('1010','Petty Cash','Current Assets');
INSERT INTO `chartmaster` VALUES ('1020','Cash on Hand','Current Assets');
INSERT INTO `chartmaster` VALUES ('1030','Cheque Accounts','Current Assets');
INSERT INTO `chartmaster` VALUES ('1040','Savings Accounts','Current Assets');
INSERT INTO `chartmaster` VALUES ('1050','Payroll Accounts','Current Assets');
INSERT INTO `chartmaster` VALUES ('1060','Special Accounts','Current Assets');
INSERT INTO `chartmaster` VALUES ('1070','Money Market Investments','Current Assets');
INSERT INTO `chartmaster` VALUES ('1080','Short-Term Investments (< 90 days)','Current Assets');
INSERT INTO `chartmaster` VALUES ('1090','Interest Receivable','Current Assets');
INSERT INTO `chartmaster` VALUES ('1100','Accounts Receivable','Current Assets');
INSERT INTO `chartmaster` VALUES ('1150','Allowance for Doubtful Accounts','Current Assets');
INSERT INTO `chartmaster` VALUES ('1200','Notes Receivable','Current Assets');
INSERT INTO `chartmaster` VALUES ('1250','Income Tax Receivable','Current Assets');
INSERT INTO `chartmaster` VALUES ('1300','Prepaid Expenses','Current Assets');
INSERT INTO `chartmaster` VALUES ('1350','Advances','Current Assets');
INSERT INTO `chartmaster` VALUES ('1400','Supplies Inventory','Current Assets');
INSERT INTO `chartmaster` VALUES ('1420','Raw Material Inventory','Current Assets');
INSERT INTO `chartmaster` VALUES ('1440','Work in Progress Inventory','Current Assets');
INSERT INTO `chartmaster` VALUES ('1460','Finished Goods Inventory','Current Assets');
INSERT INTO `chartmaster` VALUES ('1500','Land','Fixed Assets');
INSERT INTO `chartmaster` VALUES ('1550','Bonds','Fixed Assets');
INSERT INTO `chartmaster` VALUES ('1600','Buildings','Fixed Assets');
INSERT INTO `chartmaster` VALUES ('1620','Accumulated Depreciation of Buildings','Fixed Assets');
INSERT INTO `chartmaster` VALUES ('1650','Equipment','Fixed Assets');
INSERT INTO `chartmaster` VALUES ('1670','Accumulated Depreciation of Equipment','Fixed Assets');
INSERT INTO `chartmaster` VALUES ('1700','Furniture & Fixtures','Fixed Assets');
INSERT INTO `chartmaster` VALUES ('1710','Accumulated Depreciation of Furniture & Fixtures','Fixed Assets');
INSERT INTO `chartmaster` VALUES ('1720','Office Equipment','Fixed Assets');
INSERT INTO `chartmaster` VALUES ('1730','Accumulated Depreciation of Office Equipment','Fixed Assets');
INSERT INTO `chartmaster` VALUES ('1740','Software','Fixed Assets');
INSERT INTO `chartmaster` VALUES ('1750','Accumulated Depreciation of Software','Fixed Assets');
INSERT INTO `chartmaster` VALUES ('1760','Vehicles','Fixed Assets');
INSERT INTO `chartmaster` VALUES ('1770','Accumulated Depreciation Vehicles','Fixed Assets');
INSERT INTO `chartmaster` VALUES ('1780','Other Depreciable Property','Fixed Assets');
INSERT INTO `chartmaster` VALUES ('1790','Accumulated Depreciation of Other Depreciable Prop','Fixed Assets');
INSERT INTO `chartmaster` VALUES ('1800','Patents','Fixed Assets');
INSERT INTO `chartmaster` VALUES ('1850','Goodwill','Fixed Assets');
INSERT INTO `chartmaster` VALUES ('1900','Future Income Tax Receivable','Current Assets');
INSERT INTO `chartmaster` VALUES ('2010','Bank Indedebtedness (overdraft)','Liabilities');
INSERT INTO `chartmaster` VALUES ('2020','Retainers or Advances on Work','Liabilities');
INSERT INTO `chartmaster` VALUES ('2050','Interest Payable','Liabilities');
INSERT INTO `chartmaster` VALUES ('2100','Accounts Payable','Liabilities');
INSERT INTO `chartmaster` VALUES ('2150','Goods Received Suspense','Liabilities');
INSERT INTO `chartmaster` VALUES ('2200','Short-Term Loan Payable','Liabilities');
INSERT INTO `chartmaster` VALUES ('2230','Current Portion of Long-Term Debt Payable','Liabilities');
INSERT INTO `chartmaster` VALUES ('2250','Income Tax Payable','Liabilities');
INSERT INTO `chartmaster` VALUES ('2300','GST Payable','Liabilities');
INSERT INTO `chartmaster` VALUES ('2310','GST Recoverable','Liabilities');
INSERT INTO `chartmaster` VALUES ('2320','PST Payable','Liabilities');
INSERT INTO `chartmaster` VALUES ('2330','PST Recoverable (commission)','Liabilities');
INSERT INTO `chartmaster` VALUES ('2340','Payroll Tax Payable','Liabilities');
INSERT INTO `chartmaster` VALUES ('2350','Withholding Income Tax Payable','Liabilities');
INSERT INTO `chartmaster` VALUES ('2360','Other Taxes Payable','Liabilities');
INSERT INTO `chartmaster` VALUES ('2400','Employee Salaries Payable','Liabilities');
INSERT INTO `chartmaster` VALUES ('2410','Management Salaries Payable','Liabilities');
INSERT INTO `chartmaster` VALUES ('2420','Director / Partner Fees Payable','Liabilities');
INSERT INTO `chartmaster` VALUES ('2450','Health Benefits Payable','Liabilities');
INSERT INTO `chartmaster` VALUES ('2460','Pension Benefits Payable','Liabilities');
INSERT INTO `chartmaster` VALUES ('2470','Canada Pension Plan Payable','Liabilities');
INSERT INTO `chartmaster` VALUES ('2480','Employment Insurance Premiums Payable','Liabilities');
INSERT INTO `chartmaster` VALUES ('2500','Land Payable','Liabilities');
INSERT INTO `chartmaster` VALUES ('2550','Long-Term Bank Loan','Liabilities');
INSERT INTO `chartmaster` VALUES ('2560','Notes Payable','Liabilities');
INSERT INTO `chartmaster` VALUES ('2600','Building & Equipment Payable','Liabilities');
INSERT INTO `chartmaster` VALUES ('2700','Furnishing & Fixture Payable','Liabilities');
INSERT INTO `chartmaster` VALUES ('2720','Office Equipment Payable','Liabilities');
INSERT INTO `chartmaster` VALUES ('2740','Vehicle Payable','Liabilities');
INSERT INTO `chartmaster` VALUES ('2760','Other Property Payable','Liabilities');
INSERT INTO `chartmaster` VALUES ('2800','Shareholder Loans','Liabilities');
INSERT INTO `chartmaster` VALUES ('2900','Suspense','Liabilities');
INSERT INTO `chartmaster` VALUES ('3100','Capital Stock','Equity');
INSERT INTO `chartmaster` VALUES ('3200','Capital Surplus / Dividends','Equity');
INSERT INTO `chartmaster` VALUES ('3300','Dividend Taxes Payable','Equity');
INSERT INTO `chartmaster` VALUES ('3400','Dividend Taxes Refundable','Equity');
INSERT INTO `chartmaster` VALUES ('3500','Retained Earnings','Equity');
INSERT INTO `chartmaster` VALUES ('4100','Product / Service Sales','Revenue');
INSERT INTO `chartmaster` VALUES ('4200','Sales Exchange Gains/Losses','Revenue');
INSERT INTO `chartmaster` VALUES ('4500','Consulting Services','Revenue');
INSERT INTO `chartmaster` VALUES ('4600','Rentals','Revenue');
INSERT INTO `chartmaster` VALUES ('4700','Finance Charge Income','Revenue');
INSERT INTO `chartmaster` VALUES ('4800','Sales Returns & Allowances','Revenue');
INSERT INTO `chartmaster` VALUES ('4900','Sales Discounts','Revenue');
INSERT INTO `chartmaster` VALUES ('5000','Cost of Sales','Cost of Goods Sold');
INSERT INTO `chartmaster` VALUES ('5100','Production Expenses','Cost of Goods Sold');
INSERT INTO `chartmaster` VALUES ('5200','Purchases Exchange Gains/Losses','Cost of Goods Sold');
INSERT INTO `chartmaster` VALUES ('5500','Direct Labour Costs','Cost of Goods Sold');
INSERT INTO `chartmaster` VALUES ('5600','Freight Charges','Outward Freight');
INSERT INTO `chartmaster` VALUES ('5700','Inventory Adjustment','Cost of Goods Sold');
INSERT INTO `chartmaster` VALUES ('5800','Purchase Returns & Allowances','Cost of Goods Sold');
INSERT INTO `chartmaster` VALUES ('5900','Purchase Discounts','Cost of Goods Sold');
INSERT INTO `chartmaster` VALUES ('6005','Free Gifts','Giveaways');
INSERT INTO `chartmaster` VALUES ('6100','Advertising','Marketing Expenses');
INSERT INTO `chartmaster` VALUES ('6150','Promotion','Promotion Overheads');
INSERT INTO `chartmaster` VALUES ('6200','Communications','Marketing Expenses');
INSERT INTO `chartmaster` VALUES ('6250','Meeting Expenses','Marketing Expenses');
INSERT INTO `chartmaster` VALUES ('6300','Travelling Expenses','Marketing Expenses');
INSERT INTO `chartmaster` VALUES ('6400','Delivery Expenses','Marketing Expenses');
INSERT INTO `chartmaster` VALUES ('6500','Sales Salaries & Commission','Marketing Expenses');
INSERT INTO `chartmaster` VALUES ('6550','Sales Salaries & Commission Deductions','Marketing Expenses');
INSERT INTO `chartmaster` VALUES ('6590','Benefits','Marketing Expenses');
INSERT INTO `chartmaster` VALUES ('6600','Other Selling Expenses','Marketing Expenses');
INSERT INTO `chartmaster` VALUES ('6700','Permits, Licenses & License Fees','Marketing Expenses');
INSERT INTO `chartmaster` VALUES ('6800','Research & Development','Marketing Expenses');
INSERT INTO `chartmaster` VALUES ('6900','Professional Services','Marketing Expenses');
INSERT INTO `chartmaster` VALUES ('7020','Support Salaries & Wages','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7030','Support Salary & Wage Deductions','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7040','Management Salaries','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7050','Management Salary deductions','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7060','Director / Partner Fees','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7070','Director / Partner Deductions','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7080','Payroll Tax','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7090','Benefits','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7100','Training & Education Expenses','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7150','Dues & Subscriptions','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7200','Accounting Fees','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7210','Audit Fees','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7220','Banking Fees','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7230','Credit Card Fees','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7240','Consulting Fees','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7260','Legal Fees','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7280','Other Professional Fees','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7300','Business Tax','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7350','Property Tax','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7390','Corporation Capital Tax','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7400','Office Rent','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7450','Equipment Rental','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7500','Office Supplies','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7550','Office Repair & Maintenance','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7600','Automotive Expenses','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7610','Communication Expenses','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7620','Insurance Expenses','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7630','Postage & Courier Expenses','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7640','Miscellaneous Expenses','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7650','Travel Expenses','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7660','Utilities','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7700','Ammortization Expenses','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7750','Depreciation Expenses','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7800','Interest Expense','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('7900','Bad Debt Expense','Operating Overheads');
INSERT INTO `chartmaster` VALUES ('8100','Gain on Sale of Assets','Other Revenue and Expenses');
INSERT INTO `chartmaster` VALUES ('8200','Interest Income','Other Revenue and Expenses');
INSERT INTO `chartmaster` VALUES ('8300','Recovery on Bad Debt','Other Revenue and Expenses');
INSERT INTO `chartmaster` VALUES ('8400','Other Revenue','Other Revenue and Expenses');
INSERT INTO `chartmaster` VALUES ('8500','Loss on Sale of Assets','Other Revenue and Expenses');
INSERT INTO `chartmaster` VALUES ('8600','Charitable Contributions','Other Revenue and Expenses');
INSERT INTO `chartmaster` VALUES ('8900','Other Expenses','Other Revenue and Expenses');
INSERT INTO `chartmaster` VALUES ('9100','Income Tax Provision','Income Tax');

--
-- Dumping data for table `cogsglpostings`
--

INSERT INTO `cogsglpostings` VALUES (5,'AN','ANY',5000,'AN');

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` VALUES (1,'KwaMoja Demonstration Company Ltd','not entered yet','KE1234567890','Plot 8172','Kisanjani Road','Ganjoni','Mombasa','Kenya','East Africa','+61 3 4567 8901','+61 3 4567 8902','kwamoja@kwamojademo.com','KES',1100,4900,2100,2400,2150,4200,5200,3500,1,1,1,5600);

--
-- Dumping data for table `config`
--

INSERT INTO `config` VALUES ('AllowOrderLineItemNarrative','1');
INSERT INTO `config` VALUES ('AllowSalesOfZeroCostItems','0');
INSERT INTO `config` VALUES ('AutoAuthorisePO','1');
INSERT INTO `config` VALUES ('AutoCreateWOs','1');
INSERT INTO `config` VALUES ('AutoDebtorNo','0');
INSERT INTO `config` VALUES ('AutoIssue','1');
INSERT INTO `config` VALUES ('CheckCreditLimits','1');
INSERT INTO `config` VALUES ('Check_Price_Charged_vs_Order_Price','1');
INSERT INTO `config` VALUES ('Check_Qty_Charged_vs_Del_Qty','1');
INSERT INTO `config` VALUES ('CountryOfOperation','KES');
INSERT INTO `config` VALUES ('CreditingControlledItems_MustExist','0');
INSERT INTO `config` VALUES ('DB_Maintenance','30');
INSERT INTO `config` VALUES ('DB_Maintenance_LastRun','2013-01-01');
INSERT INTO `config` VALUES ('DefaultBlindPackNote','1');
INSERT INTO `config` VALUES ('DefaultCreditLimit','1000');
INSERT INTO `config` VALUES ('DefaultCustomerType','1');
INSERT INTO `config` VALUES ('DefaultDateFormat','d/m/Y');
INSERT INTO `config` VALUES ('DefaultDisplayRecordsMax','50');
INSERT INTO `config` VALUES ('DefaultFactoryLocation','MSA');
INSERT INTO `config` VALUES ('DefaultPriceList','EA');
INSERT INTO `config` VALUES ('DefaultSupplierType','1');
INSERT INTO `config` VALUES ('DefaultTaxCategory','1');
INSERT INTO `config` VALUES ('DefaultTheme','silverwolf');
INSERT INTO `config` VALUES ('Default_Shipper','1');
INSERT INTO `config` VALUES ('DefineControlledOnWOEntry','1');
INSERT INTO `config` VALUES ('DispatchCutOffTime','14');
INSERT INTO `config` VALUES ('DoFreightCalc','0');
INSERT INTO `config` VALUES ('EDIHeaderMsgId','D:01B:UN:EAN010');
INSERT INTO `config` VALUES ('EDIReference','KWAMOJA');
INSERT INTO `config` VALUES ('EDI_Incoming_Orders','companies/kwamojademo/EDI_Incoming_Orders');
INSERT INTO `config` VALUES ('EDI_MsgPending','companies/kwamojademo/EDI_MsgPending');
INSERT INTO `config` VALUES ('EDI_MsgSent','companies/kwamojademo/EDI_Sent');
INSERT INTO `config` VALUES ('ExchangeRateFeed','ECB');
INSERT INTO `config` VALUES ('Extended_CustomerInfo','0');
INSERT INTO `config` VALUES ('Extended_SupplierInfo','0');
INSERT INTO `config` VALUES ('FactoryManagerEmail','manager@company.com');
INSERT INTO `config` VALUES ('FreightChargeAppliesIfLessThan','1000');
INSERT INTO `config` VALUES ('FreightTaxCategory','1');
INSERT INTO `config` VALUES ('FrequentlyOrderedItems','0');
INSERT INTO `config` VALUES ('geocode_integration','0');
INSERT INTO `config` VALUES ('HTTPS_Only','0');
INSERT INTO `config` VALUES ('InventoryManagerEmail','');
INSERT INTO `config` VALUES ('InvoicePortraitFormat','1');
INSERT INTO `config` VALUES ('LogPath','');
INSERT INTO `config` VALUES ('LogSeverity','0');
INSERT INTO `config` VALUES ('MaxImageSize','300');
INSERT INTO `config` VALUES ('MonthsAuditTrail','1');
INSERT INTO `config` VALUES ('NumberOfMonthMustBeShown','6');
INSERT INTO `config` VALUES ('NumberOfPeriodsOfStockUsage','12');
INSERT INTO `config` VALUES ('OverChargeProportion','30');
INSERT INTO `config` VALUES ('OverReceiveProportion','20');
INSERT INTO `config` VALUES ('PackNoteFormat','1');
INSERT INTO `config` VALUES ('PageLength','48');
INSERT INTO `config` VALUES ('part_pics_dir','companies/kwamoja/EDI_Sent');
INSERT INTO `config` VALUES ('PastDueDays1','30');
INSERT INTO `config` VALUES ('PastDueDays2','60');
INSERT INTO `config` VALUES ('PO_AllowSameItemMultipleTimes','1');
INSERT INTO `config` VALUES ('ProhibitJournalsToControlAccounts','1');
INSERT INTO `config` VALUES ('ProhibitNegativeStock','1');
INSERT INTO `config` VALUES ('ProhibitPostingsBefore','1900-01-01');
INSERT INTO `config` VALUES ('PurchasingManagerEmail','test@company.com');
INSERT INTO `config` VALUES ('QuickEntries','10');
INSERT INTO `config` VALUES ('RadioBeaconFileCounter','/home/RadioBeacon/FileCounter');
INSERT INTO `config` VALUES ('RadioBeaconFTP_user_name','RadioBeacon ftp server user name');
INSERT INTO `config` VALUES ('RadioBeaconHomeDir','/home/RadioBeacon');
INSERT INTO `config` VALUES ('RadioBeaconStockLocation','BL');
INSERT INTO `config` VALUES ('RadioBraconFTP_server','192.168.2.2');
INSERT INTO `config` VALUES ('RadioBreaconFilePrefix','ORDXX');
INSERT INTO `config` VALUES ('RadionBeaconFTP_user_pass','Radio Beacon remote ftp server password');
INSERT INTO `config` VALUES ('reports_dir','companies/kwamoja/EDI_Sent');
INSERT INTO `config` VALUES ('RequirePickingNote','0');
INSERT INTO `config` VALUES ('RomalpaClause','Ownership will not pass to the buyer until the goods have been paid for in full.');
INSERT INTO `config` VALUES ('ShowStockidOnImages','0');
INSERT INTO `config` VALUES ('ShowValueOnGRN','1');
INSERT INTO `config` VALUES ('Show_Settled_LastMonth','1');
INSERT INTO `config` VALUES ('SO_AllowSameItemMultipleTimes','1');
INSERT INTO `config` VALUES ('StandardCostDecimalPlaces','2');
INSERT INTO `config` VALUES ('TaxAuthorityReferenceName','');
INSERT INTO `config` VALUES ('UpdateCurrencyRatesDaily','0');
INSERT INTO `config` VALUES ('VersionNumber','4.10');
INSERT INTO `config` VALUES ('WeightedAverageCosting','1');
INSERT INTO `config` VALUES ('WikiApp','Disabled');
INSERT INTO `config` VALUES ('WikiPath','wiki');
INSERT INTO `config` VALUES ('WorkingDaysWeek','5');
INSERT INTO `config` VALUES ('YearEnd','3');

--
-- Dumping data for table `contractbom`
--


--
-- Dumping data for table `contractcharges`
--


--
-- Dumping data for table `contractreqts`
--


--
-- Dumping data for table `contracts`
--


--
-- Dumping data for table `currencies`
--

INSERT INTO `currencies` VALUES ('Kenya Shillings','KES','Kenya','Cent',0,1);
INSERT INTO `currencies` VALUES ('Rwanda Franc','RWF','Rwanda','Centime',2,7.328490000000001);
INSERT INTO `currencies` VALUES ('Tanzania Shillings','TZS','Tanzania','Cent',0,21.538461740000002);
INSERT INTO `currencies` VALUES ('Uganda Shillings','UGX','Uganda','Centavo',0,26.923077030000005);

--
-- Dumping data for table `custallocns`
--


--
-- Dumping data for table `custbranch`
--

INSERT INTO `custbranch` VALUES ('COA001','COA001','Coastal Hotels Ltd','Mt Kenya Road','','','Mombasa','','Kenya',0.000000,0.000000,0,'CE','IN',0,'','','','','MSA',1,1,1,0,'','','','','','','','');
INSERT INTO `custbranch` VALUES ('FAT001','FAT001','Fatuma Mumbi','PO Box 12345','','','','Nairobi','Kenya',0.000000,0.000000,0,'CE','IN',0,'','','','','MSA',1,1,1,0,'','','','','','','','');
INSERT INTO `custbranch` VALUES ('KAM001','KAM001','Kampala Newspapers Incorporated','Nile Avenue','Kampala','','','','Uganda',0.000000,0.000000,0,'CE','IN',0,'','','','','MSA',1,1,1,0,'','','','','','','','');

--
-- Dumping data for table `custcontacts`
--


--
-- Dumping data for table `custnotes`
--


--
-- Dumping data for table `debtorsmaster`
--

INSERT INTO `debtorsmaster` VALUES ('COA001','Coastal Hotels Ltd','Mt Kenya Road','','','Mombasa','','Kenya','KES','EA','2012-12-11 00:00:00',1,'20',0,0,280,'2013-01-05 00:00:00',1000,0,'',0,0,'','email','','','','',0,3);
INSERT INTO `debtorsmaster` VALUES ('FAT001','Fatuma Mumbi','PO Box 12345','','','','Nairobi','Kenya','KES','EA','2012-12-12 00:00:00',1,'20',0,0,100,'2013-01-04 00:00:00',1000,0,'',0,0,'','email','','','','',0,1);
INSERT INTO `debtorsmaster` VALUES ('KAM001','Kampala Newspapers Incorporated','Nile Avenue','Kampala','','','','Uganda','UGX','EA','2013-01-11 00:00:00',1,'20',0,0,0,NULL,1000,0,'',0,0,'','email','','','','',0,3);

--
-- Dumping data for table `debtortrans`
--

INSERT INTO `debtortrans` VALUES (1,1,12,'FAT001','','2013-01-04 00:00:00','2013-01-04 15:03:41',1,0,'Cash ','',0,1,-100,0,0,0,0,0,'',0,0,'');
INSERT INTO `debtortrans` VALUES (2,1,10,'COA001','COA001','2013-01-07 00:00:00','2013-01-05 12:31:37',1,0,'','EA',2,1,500,80,0,0,0,0,'',1,0,'');
INSERT INTO `debtortrans` VALUES (3,2,12,'COA001','','2013-01-05 00:00:00','2013-01-05 14:11:43',1,0,'Cash ','',0,1,-280,0,0,0,0,0,'',0,0,'');
INSERT INTO `debtortrans` VALUES (4,2,10,'KAM001','KAM001','2013-01-12 00:00:00','2013-01-11 17:29:57',1,0,'','EA',3,26.923077030000005,1400000,224000,0,0,0,0,'Invoice for first 20 hours of web site consultancy',1,0,'');

--
-- Dumping data for table `debtortranstaxes`
--

INSERT INTO `debtortranstaxes` VALUES (2,1,80);
INSERT INTO `debtortranstaxes` VALUES (4,1,8319.9999669577);

--
-- Dumping data for table `debtortype`
--

INSERT INTO `debtortype` VALUES (1,'Private Individual');
INSERT INTO `debtortype` VALUES (2,'NGO');
INSERT INTO `debtortype` VALUES (3,'Corporate');

--
-- Dumping data for table `debtortypenotes`
--


--
-- Dumping data for table `deliverynotes`
--


--
-- Dumping data for table `departments`
--

INSERT INTO `departments` VALUES (1,'Finance Department','admin');

--
-- Dumping data for table `discountmatrix`
--


--
-- Dumping data for table `edi_orders_seg_groups`
--

INSERT INTO `edi_orders_seg_groups` VALUES (0,1,0);
INSERT INTO `edi_orders_seg_groups` VALUES (1,9999,0);
INSERT INTO `edi_orders_seg_groups` VALUES (2,99,0);
INSERT INTO `edi_orders_seg_groups` VALUES (3,99,2);
INSERT INTO `edi_orders_seg_groups` VALUES (5,5,2);
INSERT INTO `edi_orders_seg_groups` VALUES (6,5,0);
INSERT INTO `edi_orders_seg_groups` VALUES (7,5,0);
INSERT INTO `edi_orders_seg_groups` VALUES (8,10,0);
INSERT INTO `edi_orders_seg_groups` VALUES (9,9999,8);
INSERT INTO `edi_orders_seg_groups` VALUES (10,10,0);
INSERT INTO `edi_orders_seg_groups` VALUES (11,10,10);
INSERT INTO `edi_orders_seg_groups` VALUES (12,5,0);
INSERT INTO `edi_orders_seg_groups` VALUES (13,99,0);
INSERT INTO `edi_orders_seg_groups` VALUES (14,5,13);
INSERT INTO `edi_orders_seg_groups` VALUES (15,10,0);
INSERT INTO `edi_orders_seg_groups` VALUES (19,99,0);
INSERT INTO `edi_orders_seg_groups` VALUES (20,1,19);
INSERT INTO `edi_orders_seg_groups` VALUES (21,1,19);
INSERT INTO `edi_orders_seg_groups` VALUES (22,2,19);
INSERT INTO `edi_orders_seg_groups` VALUES (23,1,19);
INSERT INTO `edi_orders_seg_groups` VALUES (24,5,19);
INSERT INTO `edi_orders_seg_groups` VALUES (28,200000,0);
INSERT INTO `edi_orders_seg_groups` VALUES (32,25,28);
INSERT INTO `edi_orders_seg_groups` VALUES (33,9999,28);
INSERT INTO `edi_orders_seg_groups` VALUES (34,99,28);
INSERT INTO `edi_orders_seg_groups` VALUES (36,5,34);
INSERT INTO `edi_orders_seg_groups` VALUES (37,9999,28);
INSERT INTO `edi_orders_seg_groups` VALUES (38,10,28);
INSERT INTO `edi_orders_seg_groups` VALUES (39,999,28);
INSERT INTO `edi_orders_seg_groups` VALUES (42,5,39);
INSERT INTO `edi_orders_seg_groups` VALUES (43,99,28);
INSERT INTO `edi_orders_seg_groups` VALUES (44,1,43);
INSERT INTO `edi_orders_seg_groups` VALUES (45,1,43);
INSERT INTO `edi_orders_seg_groups` VALUES (46,2,43);
INSERT INTO `edi_orders_seg_groups` VALUES (47,1,43);
INSERT INTO `edi_orders_seg_groups` VALUES (48,5,43);
INSERT INTO `edi_orders_seg_groups` VALUES (49,10,28);
INSERT INTO `edi_orders_seg_groups` VALUES (50,1,0);

--
-- Dumping data for table `edi_orders_segs`
--

INSERT INTO `edi_orders_segs` VALUES (1,'UNB',0,1);
INSERT INTO `edi_orders_segs` VALUES (2,'UNH',0,1);
INSERT INTO `edi_orders_segs` VALUES (3,'BGM',0,1);
INSERT INTO `edi_orders_segs` VALUES (4,'DTM',0,35);
INSERT INTO `edi_orders_segs` VALUES (5,'PAI',0,1);
INSERT INTO `edi_orders_segs` VALUES (6,'ALI',0,5);
INSERT INTO `edi_orders_segs` VALUES (7,'FTX',0,99);
INSERT INTO `edi_orders_segs` VALUES (8,'RFF',1,1);
INSERT INTO `edi_orders_segs` VALUES (9,'DTM',1,5);
INSERT INTO `edi_orders_segs` VALUES (10,'NAD',2,1);
INSERT INTO `edi_orders_segs` VALUES (11,'LOC',2,99);
INSERT INTO `edi_orders_segs` VALUES (12,'FII',2,5);
INSERT INTO `edi_orders_segs` VALUES (13,'RFF',3,1);
INSERT INTO `edi_orders_segs` VALUES (14,'CTA',5,1);
INSERT INTO `edi_orders_segs` VALUES (15,'COM',5,5);
INSERT INTO `edi_orders_segs` VALUES (16,'TAX',6,1);
INSERT INTO `edi_orders_segs` VALUES (17,'MOA',6,1);
INSERT INTO `edi_orders_segs` VALUES (18,'CUX',7,1);
INSERT INTO `edi_orders_segs` VALUES (19,'DTM',7,5);
INSERT INTO `edi_orders_segs` VALUES (20,'PAT',8,1);
INSERT INTO `edi_orders_segs` VALUES (21,'DTM',8,5);
INSERT INTO `edi_orders_segs` VALUES (22,'PCD',8,1);
INSERT INTO `edi_orders_segs` VALUES (23,'MOA',9,1);
INSERT INTO `edi_orders_segs` VALUES (24,'TDT',10,1);
INSERT INTO `edi_orders_segs` VALUES (25,'LOC',11,1);
INSERT INTO `edi_orders_segs` VALUES (26,'DTM',11,5);
INSERT INTO `edi_orders_segs` VALUES (27,'TOD',12,1);
INSERT INTO `edi_orders_segs` VALUES (28,'LOC',12,2);
INSERT INTO `edi_orders_segs` VALUES (29,'PAC',13,1);
INSERT INTO `edi_orders_segs` VALUES (30,'PCI',14,1);
INSERT INTO `edi_orders_segs` VALUES (31,'RFF',14,1);
INSERT INTO `edi_orders_segs` VALUES (32,'DTM',14,5);
INSERT INTO `edi_orders_segs` VALUES (33,'GIN',14,10);
INSERT INTO `edi_orders_segs` VALUES (34,'EQD',15,1);
INSERT INTO `edi_orders_segs` VALUES (35,'ALC',19,1);
INSERT INTO `edi_orders_segs` VALUES (36,'ALI',19,5);
INSERT INTO `edi_orders_segs` VALUES (37,'DTM',19,5);
INSERT INTO `edi_orders_segs` VALUES (38,'QTY',20,1);
INSERT INTO `edi_orders_segs` VALUES (39,'RNG',20,1);
INSERT INTO `edi_orders_segs` VALUES (40,'PCD',21,1);
INSERT INTO `edi_orders_segs` VALUES (41,'RNG',21,1);
INSERT INTO `edi_orders_segs` VALUES (42,'MOA',22,1);
INSERT INTO `edi_orders_segs` VALUES (43,'RNG',22,1);
INSERT INTO `edi_orders_segs` VALUES (44,'RTE',23,1);
INSERT INTO `edi_orders_segs` VALUES (45,'RNG',23,1);
INSERT INTO `edi_orders_segs` VALUES (46,'TAX',24,1);
INSERT INTO `edi_orders_segs` VALUES (47,'MOA',24,1);
INSERT INTO `edi_orders_segs` VALUES (48,'LIN',28,1);
INSERT INTO `edi_orders_segs` VALUES (49,'PIA',28,25);
INSERT INTO `edi_orders_segs` VALUES (50,'IMD',28,99);
INSERT INTO `edi_orders_segs` VALUES (51,'MEA',28,99);
INSERT INTO `edi_orders_segs` VALUES (52,'QTY',28,99);
INSERT INTO `edi_orders_segs` VALUES (53,'ALI',28,5);
INSERT INTO `edi_orders_segs` VALUES (54,'DTM',28,35);
INSERT INTO `edi_orders_segs` VALUES (55,'MOA',28,10);
INSERT INTO `edi_orders_segs` VALUES (56,'GIN',28,127);
INSERT INTO `edi_orders_segs` VALUES (57,'QVR',28,1);
INSERT INTO `edi_orders_segs` VALUES (58,'FTX',28,99);
INSERT INTO `edi_orders_segs` VALUES (59,'PRI',32,1);
INSERT INTO `edi_orders_segs` VALUES (60,'CUX',32,1);
INSERT INTO `edi_orders_segs` VALUES (61,'DTM',32,5);
INSERT INTO `edi_orders_segs` VALUES (62,'RFF',33,1);
INSERT INTO `edi_orders_segs` VALUES (63,'DTM',33,5);
INSERT INTO `edi_orders_segs` VALUES (64,'PAC',34,1);
INSERT INTO `edi_orders_segs` VALUES (65,'QTY',34,5);
INSERT INTO `edi_orders_segs` VALUES (66,'PCI',36,1);
INSERT INTO `edi_orders_segs` VALUES (67,'RFF',36,1);
INSERT INTO `edi_orders_segs` VALUES (68,'DTM',36,5);
INSERT INTO `edi_orders_segs` VALUES (69,'GIN',36,10);
INSERT INTO `edi_orders_segs` VALUES (70,'LOC',37,1);
INSERT INTO `edi_orders_segs` VALUES (71,'QTY',37,1);
INSERT INTO `edi_orders_segs` VALUES (72,'DTM',37,5);
INSERT INTO `edi_orders_segs` VALUES (73,'TAX',38,1);
INSERT INTO `edi_orders_segs` VALUES (74,'MOA',38,1);
INSERT INTO `edi_orders_segs` VALUES (75,'NAD',39,1);
INSERT INTO `edi_orders_segs` VALUES (76,'CTA',42,1);
INSERT INTO `edi_orders_segs` VALUES (77,'COM',42,5);
INSERT INTO `edi_orders_segs` VALUES (78,'ALC',43,1);
INSERT INTO `edi_orders_segs` VALUES (79,'ALI',43,5);
INSERT INTO `edi_orders_segs` VALUES (80,'DTM',43,5);
INSERT INTO `edi_orders_segs` VALUES (81,'QTY',44,1);
INSERT INTO `edi_orders_segs` VALUES (82,'RNG',44,1);
INSERT INTO `edi_orders_segs` VALUES (83,'PCD',45,1);
INSERT INTO `edi_orders_segs` VALUES (84,'RNG',45,1);
INSERT INTO `edi_orders_segs` VALUES (85,'MOA',46,1);
INSERT INTO `edi_orders_segs` VALUES (86,'RNG',46,1);
INSERT INTO `edi_orders_segs` VALUES (87,'RTE',47,1);
INSERT INTO `edi_orders_segs` VALUES (88,'RNG',47,1);
INSERT INTO `edi_orders_segs` VALUES (89,'TAX',48,1);
INSERT INTO `edi_orders_segs` VALUES (90,'MOA',48,1);
INSERT INTO `edi_orders_segs` VALUES (91,'TDT',49,1);
INSERT INTO `edi_orders_segs` VALUES (92,'UNS',50,1);
INSERT INTO `edi_orders_segs` VALUES (93,'MOA',50,1);
INSERT INTO `edi_orders_segs` VALUES (94,'CNT',50,1);
INSERT INTO `edi_orders_segs` VALUES (95,'UNT',50,1);

--
-- Dumping data for table `ediitemmapping`
--


--
-- Dumping data for table `edimessageformat`
--


--
-- Dumping data for table `emailsettings`
--


--
-- Dumping data for table `factorcompanies`
--


--
-- Dumping data for table `fixedassetcategories`
--


--
-- Dumping data for table `fixedassetlocations`
--


--
-- Dumping data for table `fixedassets`
--


--
-- Dumping data for table `fixedassettrans`
--


--
-- Dumping data for table `freightcosts`
--


--
-- Dumping data for table `geocode_param`
--


--
-- Dumping data for table `gltrans`
--

INSERT INTO `gltrans` VALUES (1,12,1,0,'2013-01-04',1,'1030','',100,1,'',0);
INSERT INTO `gltrans` VALUES (2,12,1,0,'2013-01-04',1,'1100','',-100,1,'',0);
INSERT INTO `gltrans` VALUES (3,10,1,0,'2013-01-07',1,'5000','COA001 - CHAPATI x 50 @ 10.0000',500,1,'',0);
INSERT INTO `gltrans` VALUES (4,10,1,0,'2013-01-07',1,'1460','COA001 - CHAPATI x 50 @ 10.0000',-500,1,'',0);
INSERT INTO `gltrans` VALUES (5,10,1,0,'2013-01-07',1,'4100','COA001 - CHAPATI x 50 @ 10',-500,1,'',0);
INSERT INTO `gltrans` VALUES (6,10,1,0,'2013-01-07',1,'1100','COA001',580,1,'',0);
INSERT INTO `gltrans` VALUES (7,10,1,0,'2013-01-07',1,'2300','COA001',-80,1,'',0);
INSERT INTO `gltrans` VALUES (8,12,2,0,'2013-01-05',1,'1030','',280,1,'',0);
INSERT INTO `gltrans` VALUES (9,12,2,0,'2013-01-05',1,'1100','',-280,1,'',0);
INSERT INTO `gltrans` VALUES (10,0,1,0,'2012-12-31',0,'6005','',200,1,'',0);
INSERT INTO `gltrans` VALUES (11,0,1,0,'2012-12-31',0,'6100','',300,1,'',0);
INSERT INTO `gltrans` VALUES (12,0,1,0,'2012-12-31',0,'6150','',-500,1,'',0);
INSERT INTO `gltrans` VALUES (13,0,2,0,'2013-01-11',1,'1010','CONSULT Change stock category',0,0,'',0);
INSERT INTO `gltrans` VALUES (14,0,2,0,'2013-01-11',1,'1460','CONSULT Change stock category',0,0,'',0);
INSERT INTO `gltrans` VALUES (15,10,2,0,'2013-01-12',1,'4100','KAM001 - CONSULT x 20 @ 70000',-52000,0,'',0);
INSERT INTO `gltrans` VALUES (16,10,2,0,'2013-01-12',1,'1100','KAM001',60320,0,'',0);
INSERT INTO `gltrans` VALUES (17,10,2,0,'2013-01-12',1,'2300','KAM001',-8320,0,'',0);
INSERT INTO `gltrans` VALUES (18,22,1,0,'2013-01-14',1,'2100','VOI001-',200,0,'',0);
INSERT INTO `gltrans` VALUES (19,22,1,0,'2013-01-14',1,'1030','VOI001-',-200,0,'',0);
INSERT INTO `gltrans` VALUES (20,25,1,0,'2013-01-15',1,'1460','PO: 2 VOI001 - CHAPATI - Chapati x 1 @ 10',10,0,'',0);
INSERT INTO `gltrans` VALUES (21,25,1,0,'2013-01-15',1,'2150','PO1358269154: 2 VOI001 - CHAPATI - Chapati x 1 @ 10',-10,0,'',0);
INSERT INTO `gltrans` VALUES (22,25,1,0,'2013-01-15',1,'1420','PO: 2 VOI001 - FLOUR -  - Maize Flour x 10 @ 0',0,0,'',0);
INSERT INTO `gltrans` VALUES (23,25,1,0,'2013-01-15',1,'2150','PO1358269154: 2 VOI001 - FLOUR -  - Maize Flour x 10 @ 0',0,0,'',0);
INSERT INTO `gltrans` VALUES (24,20,1,0,'2013-01-14',1,'2150','VOI001 - GRN 1 - CHAPATI x 1 @  std cost of 10',10,0,'',0);
INSERT INTO `gltrans` VALUES (25,20,1,0,'2013-01-14',1,'1460','VOI001 - Average Cost Adj - CHAPATI x 1 x 90',90,0,'',0);
INSERT INTO `gltrans` VALUES (26,20,1,0,'2013-01-14',1,'1420','VOI001 - Average Cost Adj - FLOUR x 10 x 10',100,0,'',0);
INSERT INTO `gltrans` VALUES (27,20,1,0,'2013-01-14',1,'2310','VOI001 - Inv 12345 Kenya Revenue Authority 16.00% KES32 @ exch rate 1',32,0,'',0);
INSERT INTO `gltrans` VALUES (28,20,1,0,'2013-01-14',1,'2100','VOI001 - Inv 12345 KES232 @ a rate of 1',-232,0,'',0);
INSERT INTO `gltrans` VALUES (29,20,2,0,'2013-01-14',1,'6100','VOI001 ',12340,0,'',0);
INSERT INTO `gltrans` VALUES (30,20,2,0,'2013-01-14',1,'2310','VOI001 - Inv 666 Kenya Revenue Authority 16.00% KES1974.4 @ exch rate 1',1974.4,0,'',0);
INSERT INTO `gltrans` VALUES (31,20,2,0,'2013-01-14',1,'2100','VOI001 - Inv 666 KES14,314 @ a rate of 1',-14314.4,0,'',0);

--
-- Dumping data for table `grns`
--

INSERT INTO `grns` VALUES (1,1,2,'CHAPATI','2013-01-15','Chapati',1,1,'VOI001',10);
INSERT INTO `grns` VALUES (1,2,3,'FLOUR','2013-01-15',' - Maize Flour',10,10,'VOI001',0);

--
-- Dumping data for table `holdreasons`
--

INSERT INTO `holdreasons` VALUES (1,'Good History',0);
INSERT INTO `holdreasons` VALUES (20,'Watch',0);
INSERT INTO `holdreasons` VALUES (51,'In liquidation',1);

--
-- Dumping data for table `internalstockcatrole`
--

INSERT INTO `internalstockcatrole` VALUES ('INGR',8);

--
-- Dumping data for table `jobcards`
--


--
-- Dumping data for table `labelfields`
--


--
-- Dumping data for table `labels`
--


--
-- Dumping data for table `lastcostrollup`
--


--
-- Dumping data for table `locations`
--

INSERT INTO `locations` VALUES ('MSA','Mombasa Main Warehouse',' ','','','','','','','','','',1,'',0,'',1);

--
-- Dumping data for table `locstock`
--

INSERT INTO `locstock` VALUES ('MSA','CHAPATI',1,0);
INSERT INTO `locstock` VALUES ('MSA','CONSULT',0,0);
INSERT INTO `locstock` VALUES ('MSA','FLOUR',10,0);
INSERT INTO `locstock` VALUES ('MSA','WATER',0,0);

--
-- Dumping data for table `loctransfers`
--


--
-- Dumping data for table `mrpcalendar`
--


--
-- Dumping data for table `mrpdemands`
--


--
-- Dumping data for table `mrpdemandtypes`
--


--
-- Dumping data for table `mrpplannedorders`
--


--
-- Dumping data for table `offers`
--

INSERT INTO `offers` VALUES (1,1,'VOI001','CHAPATI',10,'each',30,'2013-01-02','KES');
INSERT INTO `offers` VALUES (2,1,'VOI001','FLOUR',20,'kgs',20,'2013-01-02','KES');
INSERT INTO `offers` VALUES (3,1,'VOI001','WATER',30,'litres',10,'2013-01-02','KES');

--
-- Dumping data for table `orderdeliverydifferenceslog`
--

INSERT INTO `orderdeliverydifferenceslog` VALUES (2,1,'CHAPATI',50,'COA001','COA001','BO');

--
-- Dumping data for table `paymentmethods`
--

INSERT INTO `paymentmethods` VALUES (1,'Cheque',1,1,1,0);
INSERT INTO `paymentmethods` VALUES (2,'Cash',1,1,0,0);
INSERT INTO `paymentmethods` VALUES (3,'Direct Credit',1,1,0,0);

--
-- Dumping data for table `paymentterms`
--

INSERT INTO `paymentterms` VALUES ('20','Due 20th Of the Following Month',0,22);
INSERT INTO `paymentterms` VALUES ('30','Due By End Of The Following Month',0,30);
INSERT INTO `paymentterms` VALUES ('7','Payment due within 7 days',7,0);
INSERT INTO `paymentterms` VALUES ('CA','Cash Only',2,0);

--
-- Dumping data for table `pcashdetails`
--


--
-- Dumping data for table `pcexpenses`
--


--
-- Dumping data for table `pctabexpenses`
--


--
-- Dumping data for table `pctabs`
--


--
-- Dumping data for table `pctypetabs`
--


--
-- Dumping data for table `periods`
--

INSERT INTO `periods` VALUES (-8,'2012-04-30');
INSERT INTO `periods` VALUES (-7,'2012-05-31');
INSERT INTO `periods` VALUES (-6,'2012-06-30');
INSERT INTO `periods` VALUES (-5,'2012-07-31');
INSERT INTO `periods` VALUES (-4,'2012-08-31');
INSERT INTO `periods` VALUES (-3,'2012-09-30');
INSERT INTO `periods` VALUES (-2,'2012-10-31');
INSERT INTO `periods` VALUES (-1,'2012-11-30');
INSERT INTO `periods` VALUES (0,'2012-12-31');
INSERT INTO `periods` VALUES (1,'2013-01-31');
INSERT INTO `periods` VALUES (2,'2013-02-28');
INSERT INTO `periods` VALUES (3,'2013-03-31');

--
-- Dumping data for table `pickinglistdetails`
--


--
-- Dumping data for table `pickinglists`
--


--
-- Dumping data for table `prices`
--


--
-- Dumping data for table `purchdata`
--

INSERT INTO `purchdata` VALUES ('VOI001','FLOUR',10.0000,'kgs',1,'',5,0,'2013-01-15','',100);

--
-- Dumping data for table `purchorderauth`
--

INSERT INTO `purchorderauth` VALUES ('admin','KES',0,1000000000,0);

--
-- Dumping data for table `purchorderdetails`
--

INSERT INTO `purchorderdetails` VALUES (1,1,'CHAPATI','2012-12-10','Chapati',1460,0,10,0,0,10,0,0,'0',0,'each','',0,1);
INSERT INTO `purchorderdetails` VALUES (2,2,'CHAPATI','2013-01-15','Chapati',1460,1,100,100,10,10,1,0,'0',0,'each','',0,1);
INSERT INTO `purchorderdetails` VALUES (3,2,'FLOUR','2013-01-20',' - Maize Flour',1420,10,10,10,0,10,10,0,'0',1,'kgs','',0,1);

--
-- Dumping data for table `purchorders`
--

INSERT INTO `purchorders` VALUES (1,'VOI001','','2012-12-10 00:00:00',1,'2012-12-10 00:00:00',0,'admin','','MSA','Kisanjani road ','','','','','','','PO Box 9999','','Voi','','','Kenya','','','',1.00,'2012-12-10','','1','2012-12-10','Printed','10/12/2012 - Printed by &lt;a href=&quot;mailto:info@kwamoja.com&quot;&gt;Demonstration user&lt;/a&gt;&lt;br /&gt;10/12/2012 - Order Created and Authorised by &lt;a href=&quot;mailto:info@kwamoja.com&quot;&gt;Demonstration user&lt;/a&gt;&lt;br /&gt;&lt;br /&gt;','20','');
INSERT INTO `purchorders` VALUES (2,'VOI001','','2013-01-15 00:00:00',1,'2013-01-15 00:00:00',0,'admin','','MSA',' Ganjoni Road','','','','','','','PO Box 9999','','Voi','','','Kenya','','','',1.00,'2013-01-15','','1','2013-01-15','Printed','15/01/2013 - Printed by &lt;a href=&quot;mailto:info@kwamoja.com&quot;&gt;Demonstration user&lt;/a&gt;&lt;br /&gt;15/01/2013 - Order Created and Authorised by &lt;a href=&quot;mailto:info@kwamoja.com&quot;&gt;Demonstration user&lt;/a&gt;&lt;br /&gt;&lt;br /&gt;','20','');

--
-- Dumping data for table `recurringsalesorders`
--


--
-- Dumping data for table `recurrsalesorderdetails`
--


--
-- Dumping data for table `reportcolumns`
--


--
-- Dumping data for table `reportfields`
--


--
-- Dumping data for table `reportheaders`
--


--
-- Dumping data for table `reportlinks`
--

INSERT INTO `reportlinks` VALUES ('accountgroups','accountsection','accountgroups.sectioninaccounts=accountsection.sectionid');
INSERT INTO `reportlinks` VALUES ('accountsection','accountgroups','accountsection.sectionid=accountgroups.sectioninaccounts');
INSERT INTO `reportlinks` VALUES ('bankaccounts','chartmaster','bankaccounts.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','bankaccounts','chartmaster.accountcode=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('banktrans','systypes','banktrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','banktrans','systypes.typeid=banktrans.type');
INSERT INTO `reportlinks` VALUES ('banktrans','bankaccounts','banktrans.bankact=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('bankaccounts','banktrans','bankaccounts.accountcode=banktrans.bankact');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.parent=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.parent');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.component');
INSERT INTO `reportlinks` VALUES ('bom','workcentres','bom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','bom','workcentres.code=bom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('bom','locations','bom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','bom','locations.loccode=bom.loccode');
INSERT INTO `reportlinks` VALUES ('buckets','workcentres','buckets.workcentre=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','buckets','workcentres.code=buckets.workcentre');
INSERT INTO `reportlinks` VALUES ('chartdetails','chartmaster','chartdetails.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','chartdetails','chartmaster.accountcode=chartdetails.accountcode');
INSERT INTO `reportlinks` VALUES ('chartdetails','periods','chartdetails.period=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','chartdetails','periods.periodno=chartdetails.period');
INSERT INTO `reportlinks` VALUES ('chartmaster','accountgroups','chartmaster.group_=accountgroups.groupname');
INSERT INTO `reportlinks` VALUES ('accountgroups','chartmaster','accountgroups.groupname=chartmaster.group_');
INSERT INTO `reportlinks` VALUES ('contractbom','workcentres','contractbom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','contractbom','workcentres.code=contractbom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('contractbom','locations','contractbom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','contractbom','locations.loccode=contractbom.loccode');
INSERT INTO `reportlinks` VALUES ('contractbom','stockmaster','contractbom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','contractbom','stockmaster.stockid=contractbom.component');
INSERT INTO `reportlinks` VALUES ('contractreqts','contracts','contractreqts.contract=contracts.contractref');
INSERT INTO `reportlinks` VALUES ('contracts','contractreqts','contracts.contractref=contractreqts.contract');
INSERT INTO `reportlinks` VALUES ('contracts','custbranch','contracts.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','contracts','custbranch.debtorno=contracts.debtorno');
INSERT INTO `reportlinks` VALUES ('contracts','stockcategory','contracts.branchcode=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','contracts','stockcategory.categoryid=contracts.branchcode');
INSERT INTO `reportlinks` VALUES ('contracts','salestypes','contracts.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','contracts','salestypes.typeabbrev=contracts.typeabbrev');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocfrom=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocto=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocto');
INSERT INTO `reportlinks` VALUES ('custbranch','debtorsmaster','custbranch.debtorno=debtorsmaster.debtorno');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','custbranch','debtorsmaster.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','areas','custbranch.area=areas.areacode');
INSERT INTO `reportlinks` VALUES ('areas','custbranch','areas.areacode=custbranch.area');
INSERT INTO `reportlinks` VALUES ('custbranch','salesman','custbranch.salesman=salesman.salesmancode');
INSERT INTO `reportlinks` VALUES ('salesman','custbranch','salesman.salesmancode=custbranch.salesman');
INSERT INTO `reportlinks` VALUES ('custbranch','locations','custbranch.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','custbranch','locations.loccode=custbranch.defaultlocation');
INSERT INTO `reportlinks` VALUES ('custbranch','shippers','custbranch.defaultshipvia=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','custbranch','shippers.shipper_id=custbranch.defaultshipvia');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','holdreasons','debtorsmaster.holdreason=holdreasons.reasoncode');
INSERT INTO `reportlinks` VALUES ('holdreasons','debtorsmaster','holdreasons.reasoncode=debtorsmaster.holdreason');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','currencies','debtorsmaster.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','debtorsmaster','currencies.currabrev=debtorsmaster.currcode');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','paymentterms','debtorsmaster.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','debtorsmaster','paymentterms.termsindicator=debtorsmaster.paymentterms');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','salestypes','debtorsmaster.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','debtorsmaster','salestypes.typeabbrev=debtorsmaster.salestype');
INSERT INTO `reportlinks` VALUES ('debtortrans','custbranch','debtortrans.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','debtortrans','custbranch.debtorno=debtortrans.debtorno');
INSERT INTO `reportlinks` VALUES ('debtortrans','systypes','debtortrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','debtortrans','systypes.typeid=debtortrans.type');
INSERT INTO `reportlinks` VALUES ('debtortrans','periods','debtortrans.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','debtortrans','periods.periodno=debtortrans.prd');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','taxauthorities','debtortranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','debtortranstaxes','taxauthorities.taxid=debtortranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','debtortrans','debtortranstaxes.debtortransid=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','debtortranstaxes','debtortrans.id=debtortranstaxes.debtortransid');
INSERT INTO `reportlinks` VALUES ('discountmatrix','salestypes','discountmatrix.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','discountmatrix','salestypes.typeabbrev=discountmatrix.salestype');
INSERT INTO `reportlinks` VALUES ('freightcosts','locations','freightcosts.locationfrom=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','freightcosts','locations.loccode=freightcosts.locationfrom');
INSERT INTO `reportlinks` VALUES ('freightcosts','shippers','freightcosts.shipperid=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','freightcosts','shippers.shipper_id=freightcosts.shipperid');
INSERT INTO `reportlinks` VALUES ('gltrans','chartmaster','gltrans.account=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','gltrans','chartmaster.accountcode=gltrans.account');
INSERT INTO `reportlinks` VALUES ('gltrans','systypes','gltrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','gltrans','systypes.typeid=gltrans.type');
INSERT INTO `reportlinks` VALUES ('gltrans','periods','gltrans.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','gltrans','periods.periodno=gltrans.periodno');
INSERT INTO `reportlinks` VALUES ('grns','suppliers','grns.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','grns','suppliers.supplierid=grns.supplierid');
INSERT INTO `reportlinks` VALUES ('grns','purchorderdetails','grns.podetailitem=purchorderdetails.podetailitem');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','grns','purchorderdetails.podetailitem=grns.podetailitem');
INSERT INTO `reportlinks` VALUES ('locations','taxprovinces','locations.taxprovinceid=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','locations','taxprovinces.taxprovinceid=locations.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('locstock','locations','locstock.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','locstock','locations.loccode=locstock.loccode');
INSERT INTO `reportlinks` VALUES ('locstock','stockmaster','locstock.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','locstock','stockmaster.stockid=locstock.stockid');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.shiploc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.shiploc');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.recloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.recloc');
INSERT INTO `reportlinks` VALUES ('loctransfers','stockmaster','loctransfers.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','loctransfers','stockmaster.stockid=loctransfers.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','stockmaster','orderdeliverydifferenceslog.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','orderdeliverydifferencesl','stockmaster.stockid=orderdeliverydifferenceslog.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','custbranch','orderdeliverydifferenceslog.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','orderdeliverydifferencesl','custbranch.debtorno=orderdeliverydifferenceslog.debtorno');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','salesorders','orderdeliverydifferenceslog.branchcode=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','orderdeliverydifferencesl','salesorders.orderno=orderdeliverydifferenceslog.branchcode');
INSERT INTO `reportlinks` VALUES ('prices','stockmaster','prices.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','prices','stockmaster.stockid=prices.stockid');
INSERT INTO `reportlinks` VALUES ('prices','currencies','prices.currabrev=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','prices','currencies.currabrev=prices.currabrev');
INSERT INTO `reportlinks` VALUES ('prices','salestypes','prices.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','prices','salestypes.typeabbrev=prices.typeabbrev');
INSERT INTO `reportlinks` VALUES ('purchdata','stockmaster','purchdata.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','purchdata','stockmaster.stockid=purchdata.stockid');
INSERT INTO `reportlinks` VALUES ('purchdata','suppliers','purchdata.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchdata','suppliers.supplierid=purchdata.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','purchorders','purchorderdetails.orderno=purchorders.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','purchorderdetails','purchorders.orderno=purchorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','suppliers','purchorders.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchorders','suppliers.supplierid=purchorders.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorders','locations','purchorders.intostocklocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','purchorders','locations.loccode=purchorders.intostocklocation');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','custbranch','recurringsalesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','recurringsalesorders','custbranch.branchcode=recurringsalesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','recurringsalesorders','recurrsalesorderdetails.recurrorderno=recurringsalesorders.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','recurrsalesorderdetails','recurringsalesorders.recurrorderno=recurrsalesorderdetails.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','stockmaster','recurrsalesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','recurrsalesorderdetails','stockmaster.stockid=recurrsalesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('reportcolumns','reportheaders','reportcolumns.reportid=reportheaders.reportid');
INSERT INTO `reportlinks` VALUES ('reportheaders','reportcolumns','reportheaders.reportid=reportcolumns.reportid');
INSERT INTO `reportlinks` VALUES ('salesanalysis','periods','salesanalysis.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','salesanalysis','periods.periodno=salesanalysis.periodno');
INSERT INTO `reportlinks` VALUES ('salescatprod','stockmaster','salescatprod.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salescatprod','stockmaster.stockid=salescatprod.stockid');
INSERT INTO `reportlinks` VALUES ('salescatprod','salescat','salescatprod.salescatid=salescat.salescatid');
INSERT INTO `reportlinks` VALUES ('salescat','salescatprod','salescat.salescatid=salescatprod.salescatid');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','salesorders','salesorderdetails.orderno=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','salesorderdetails','salesorders.orderno=salesorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','stockmaster','salesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salesorderdetails','stockmaster.stockid=salesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('salesorders','custbranch','salesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','salesorders','custbranch.branchcode=salesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('salesorders','shippers','salesorders.debtorno=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','salesorders','shippers.shipper_id=salesorders.debtorno');
INSERT INTO `reportlinks` VALUES ('salesorders','locations','salesorders.fromstkloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','salesorders','locations.loccode=salesorders.fromstkloc');
INSERT INTO `reportlinks` VALUES ('securitygroups','securityroles','securitygroups.secroleid=securityroles.secroleid');
INSERT INTO `reportlinks` VALUES ('securityroles','securitygroups','securityroles.secroleid=securitygroups.secroleid');
INSERT INTO `reportlinks` VALUES ('securitygroups','securitytokens','securitygroups.tokenid=securitytokens.tokenid');
INSERT INTO `reportlinks` VALUES ('securitytokens','securitygroups','securitytokens.tokenid=securitygroups.tokenid');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','shipments','shipmentcharges.shiptref=shipments.shiptref');
INSERT INTO `reportlinks` VALUES ('shipments','shipmentcharges','shipments.shiptref=shipmentcharges.shiptref');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','systypes','shipmentcharges.transtype=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','shipmentcharges','systypes.typeid=shipmentcharges.transtype');
INSERT INTO `reportlinks` VALUES ('shipments','suppliers','shipments.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','shipments','suppliers.supplierid=shipments.supplierid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','stockmaster','stockcheckfreeze.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcheckfreeze','stockmaster.stockid=stockcheckfreeze.stockid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','locations','stockcheckfreeze.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcheckfreeze','locations.loccode=stockcheckfreeze.loccode');
INSERT INTO `reportlinks` VALUES ('stockcounts','stockmaster','stockcounts.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcounts','stockmaster.stockid=stockcounts.stockid');
INSERT INTO `reportlinks` VALUES ('stockcounts','locations','stockcounts.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcounts','locations.loccode=stockcounts.loccode');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcategory','stockmaster.categoryid=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','stockmaster','stockcategory.categoryid=stockmaster.categoryid');
INSERT INTO `reportlinks` VALUES ('stockmaster','taxcategories','stockmaster.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','stockmaster','taxcategories.taxcatid=stockmaster.taxcatid');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockmaster','stockmoves.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockmoves','stockmaster.stockid=stockmoves.stockid');
INSERT INTO `reportlinks` VALUES ('stockmoves','systypes','stockmoves.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','stockmoves','systypes.typeid=stockmoves.type');
INSERT INTO `reportlinks` VALUES ('stockmoves','locations','stockmoves.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockmoves','locations.loccode=stockmoves.loccode');
INSERT INTO `reportlinks` VALUES ('stockmoves','periods','stockmoves.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','stockmoves','periods.periodno=stockmoves.prd');
INSERT INTO `reportlinks` VALUES ('stockmovestaxes','taxauthorities','stockmovestaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','stockmovestaxes','taxauthorities.taxid=stockmovestaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockmaster','stockserialitems.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockserialitems','stockmaster.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','locations','stockserialitems.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockserialitems','locations.loccode=stockserialitems.loccode');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockmoves','stockserialmoves.stockmoveno=stockmoves.stkmoveno');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockserialmoves','stockmoves.stkmoveno=stockserialmoves.stockmoveno');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockserialitems','stockserialmoves.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockserialmoves','stockserialitems.stockid=stockserialmoves.stockid');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocfrom=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocto=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocto');
INSERT INTO `reportlinks` VALUES ('suppliercontacts','suppliers','suppliercontacts.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','suppliercontacts','suppliers.supplierid=suppliercontacts.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','currencies','suppliers.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','suppliers','currencies.currabrev=suppliers.currcode');
INSERT INTO `reportlinks` VALUES ('suppliers','paymentterms','suppliers.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','suppliers','paymentterms.termsindicator=suppliers.paymentterms');
INSERT INTO `reportlinks` VALUES ('suppliers','taxgroups','suppliers.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','suppliers','taxgroups.taxgroupid=suppliers.taxgroupid');
INSERT INTO `reportlinks` VALUES ('supptrans','systypes','supptrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','supptrans','systypes.typeid=supptrans.type');
INSERT INTO `reportlinks` VALUES ('supptrans','suppliers','supptrans.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','supptrans','suppliers.supplierid=supptrans.supplierno');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','taxauthorities','supptranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','supptranstaxes','taxauthorities.taxid=supptranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','supptrans','supptranstaxes.supptransid=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','supptranstaxes','supptrans.id=supptranstaxes.supptransid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.taxglcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.taxglcode');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.purchtaxglaccount=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.purchtaxglaccount');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxauthorities','taxauthrates.taxauthority=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxauthrates','taxauthorities.taxid=taxauthrates.taxauthority');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxcategories','taxauthrates.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','taxauthrates','taxcategories.taxcatid=taxauthrates.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxprovinces','taxauthrates.dispatchtaxprovince=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','taxauthrates','taxprovinces.taxprovinceid=taxauthrates.dispatchtaxprovince');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxgroups','taxgrouptaxes.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','taxgrouptaxes','taxgroups.taxgroupid=taxgrouptaxes.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxauthorities','taxgrouptaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxgrouptaxes','taxauthorities.taxid=taxgrouptaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('workcentres','locations','workcentres.location=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','workcentres','locations.loccode=workcentres.location');
INSERT INTO `reportlinks` VALUES ('worksorders','locations','worksorders.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','worksorders','locations.loccode=worksorders.loccode');
INSERT INTO `reportlinks` VALUES ('worksorders','stockmaster','worksorders.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','worksorders','stockmaster.stockid=worksorders.stockid');
INSERT INTO `reportlinks` VALUES ('www_users','locations','www_users.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','www_users','locations.loccode=www_users.defaultlocation');
INSERT INTO `reportlinks` VALUES ('accountgroups','accountsection','accountgroups.sectioninaccounts=accountsection.sectionid');
INSERT INTO `reportlinks` VALUES ('accountsection','accountgroups','accountsection.sectionid=accountgroups.sectioninaccounts');
INSERT INTO `reportlinks` VALUES ('bankaccounts','chartmaster','bankaccounts.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','bankaccounts','chartmaster.accountcode=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('banktrans','systypes','banktrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','banktrans','systypes.typeid=banktrans.type');
INSERT INTO `reportlinks` VALUES ('banktrans','bankaccounts','banktrans.bankact=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('bankaccounts','banktrans','bankaccounts.accountcode=banktrans.bankact');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.parent=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.parent');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.component');
INSERT INTO `reportlinks` VALUES ('bom','workcentres','bom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','bom','workcentres.code=bom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('bom','locations','bom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','bom','locations.loccode=bom.loccode');
INSERT INTO `reportlinks` VALUES ('buckets','workcentres','buckets.workcentre=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','buckets','workcentres.code=buckets.workcentre');
INSERT INTO `reportlinks` VALUES ('chartdetails','chartmaster','chartdetails.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','chartdetails','chartmaster.accountcode=chartdetails.accountcode');
INSERT INTO `reportlinks` VALUES ('chartdetails','periods','chartdetails.period=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','chartdetails','periods.periodno=chartdetails.period');
INSERT INTO `reportlinks` VALUES ('chartmaster','accountgroups','chartmaster.group_=accountgroups.groupname');
INSERT INTO `reportlinks` VALUES ('accountgroups','chartmaster','accountgroups.groupname=chartmaster.group_');
INSERT INTO `reportlinks` VALUES ('contractbom','workcentres','contractbom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','contractbom','workcentres.code=contractbom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('contractbom','locations','contractbom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','contractbom','locations.loccode=contractbom.loccode');
INSERT INTO `reportlinks` VALUES ('contractbom','stockmaster','contractbom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','contractbom','stockmaster.stockid=contractbom.component');
INSERT INTO `reportlinks` VALUES ('contractreqts','contracts','contractreqts.contract=contracts.contractref');
INSERT INTO `reportlinks` VALUES ('contracts','contractreqts','contracts.contractref=contractreqts.contract');
INSERT INTO `reportlinks` VALUES ('contracts','custbranch','contracts.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','contracts','custbranch.debtorno=contracts.debtorno');
INSERT INTO `reportlinks` VALUES ('contracts','stockcategory','contracts.branchcode=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','contracts','stockcategory.categoryid=contracts.branchcode');
INSERT INTO `reportlinks` VALUES ('contracts','salestypes','contracts.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','contracts','salestypes.typeabbrev=contracts.typeabbrev');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocfrom=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocto=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocto');
INSERT INTO `reportlinks` VALUES ('custbranch','debtorsmaster','custbranch.debtorno=debtorsmaster.debtorno');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','custbranch','debtorsmaster.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','areas','custbranch.area=areas.areacode');
INSERT INTO `reportlinks` VALUES ('areas','custbranch','areas.areacode=custbranch.area');
INSERT INTO `reportlinks` VALUES ('custbranch','salesman','custbranch.salesman=salesman.salesmancode');
INSERT INTO `reportlinks` VALUES ('salesman','custbranch','salesman.salesmancode=custbranch.salesman');
INSERT INTO `reportlinks` VALUES ('custbranch','locations','custbranch.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','custbranch','locations.loccode=custbranch.defaultlocation');
INSERT INTO `reportlinks` VALUES ('custbranch','shippers','custbranch.defaultshipvia=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','custbranch','shippers.shipper_id=custbranch.defaultshipvia');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','holdreasons','debtorsmaster.holdreason=holdreasons.reasoncode');
INSERT INTO `reportlinks` VALUES ('holdreasons','debtorsmaster','holdreasons.reasoncode=debtorsmaster.holdreason');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','currencies','debtorsmaster.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','debtorsmaster','currencies.currabrev=debtorsmaster.currcode');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','paymentterms','debtorsmaster.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','debtorsmaster','paymentterms.termsindicator=debtorsmaster.paymentterms');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','salestypes','debtorsmaster.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','debtorsmaster','salestypes.typeabbrev=debtorsmaster.salestype');
INSERT INTO `reportlinks` VALUES ('debtortrans','custbranch','debtortrans.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','debtortrans','custbranch.debtorno=debtortrans.debtorno');
INSERT INTO `reportlinks` VALUES ('debtortrans','systypes','debtortrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','debtortrans','systypes.typeid=debtortrans.type');
INSERT INTO `reportlinks` VALUES ('debtortrans','periods','debtortrans.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','debtortrans','periods.periodno=debtortrans.prd');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','taxauthorities','debtortranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','debtortranstaxes','taxauthorities.taxid=debtortranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','debtortrans','debtortranstaxes.debtortransid=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','debtortranstaxes','debtortrans.id=debtortranstaxes.debtortransid');
INSERT INTO `reportlinks` VALUES ('discountmatrix','salestypes','discountmatrix.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','discountmatrix','salestypes.typeabbrev=discountmatrix.salestype');
INSERT INTO `reportlinks` VALUES ('freightcosts','locations','freightcosts.locationfrom=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','freightcosts','locations.loccode=freightcosts.locationfrom');
INSERT INTO `reportlinks` VALUES ('freightcosts','shippers','freightcosts.shipperid=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','freightcosts','shippers.shipper_id=freightcosts.shipperid');
INSERT INTO `reportlinks` VALUES ('gltrans','chartmaster','gltrans.account=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','gltrans','chartmaster.accountcode=gltrans.account');
INSERT INTO `reportlinks` VALUES ('gltrans','systypes','gltrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','gltrans','systypes.typeid=gltrans.type');
INSERT INTO `reportlinks` VALUES ('gltrans','periods','gltrans.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','gltrans','periods.periodno=gltrans.periodno');
INSERT INTO `reportlinks` VALUES ('grns','suppliers','grns.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','grns','suppliers.supplierid=grns.supplierid');
INSERT INTO `reportlinks` VALUES ('grns','purchorderdetails','grns.podetailitem=purchorderdetails.podetailitem');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','grns','purchorderdetails.podetailitem=grns.podetailitem');
INSERT INTO `reportlinks` VALUES ('locations','taxprovinces','locations.taxprovinceid=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','locations','taxprovinces.taxprovinceid=locations.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('locstock','locations','locstock.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','locstock','locations.loccode=locstock.loccode');
INSERT INTO `reportlinks` VALUES ('locstock','stockmaster','locstock.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','locstock','stockmaster.stockid=locstock.stockid');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.shiploc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.shiploc');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.recloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.recloc');
INSERT INTO `reportlinks` VALUES ('loctransfers','stockmaster','loctransfers.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','loctransfers','stockmaster.stockid=loctransfers.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','stockmaster','orderdeliverydifferenceslog.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','orderdeliverydifferencesl','stockmaster.stockid=orderdeliverydifferenceslog.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','custbranch','orderdeliverydifferenceslog.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','orderdeliverydifferencesl','custbranch.debtorno=orderdeliverydifferenceslog.debtorno');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','salesorders','orderdeliverydifferenceslog.branchcode=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','orderdeliverydifferencesl','salesorders.orderno=orderdeliverydifferenceslog.branchcode');
INSERT INTO `reportlinks` VALUES ('prices','stockmaster','prices.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','prices','stockmaster.stockid=prices.stockid');
INSERT INTO `reportlinks` VALUES ('prices','currencies','prices.currabrev=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','prices','currencies.currabrev=prices.currabrev');
INSERT INTO `reportlinks` VALUES ('prices','salestypes','prices.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','prices','salestypes.typeabbrev=prices.typeabbrev');
INSERT INTO `reportlinks` VALUES ('purchdata','stockmaster','purchdata.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','purchdata','stockmaster.stockid=purchdata.stockid');
INSERT INTO `reportlinks` VALUES ('purchdata','suppliers','purchdata.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchdata','suppliers.supplierid=purchdata.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','purchorders','purchorderdetails.orderno=purchorders.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','purchorderdetails','purchorders.orderno=purchorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','suppliers','purchorders.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchorders','suppliers.supplierid=purchorders.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorders','locations','purchorders.intostocklocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','purchorders','locations.loccode=purchorders.intostocklocation');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','custbranch','recurringsalesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','recurringsalesorders','custbranch.branchcode=recurringsalesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','recurringsalesorders','recurrsalesorderdetails.recurrorderno=recurringsalesorders.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','recurrsalesorderdetails','recurringsalesorders.recurrorderno=recurrsalesorderdetails.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','stockmaster','recurrsalesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','recurrsalesorderdetails','stockmaster.stockid=recurrsalesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('reportcolumns','reportheaders','reportcolumns.reportid=reportheaders.reportid');
INSERT INTO `reportlinks` VALUES ('reportheaders','reportcolumns','reportheaders.reportid=reportcolumns.reportid');
INSERT INTO `reportlinks` VALUES ('salesanalysis','periods','salesanalysis.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','salesanalysis','periods.periodno=salesanalysis.periodno');
INSERT INTO `reportlinks` VALUES ('salescatprod','stockmaster','salescatprod.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salescatprod','stockmaster.stockid=salescatprod.stockid');
INSERT INTO `reportlinks` VALUES ('salescatprod','salescat','salescatprod.salescatid=salescat.salescatid');
INSERT INTO `reportlinks` VALUES ('salescat','salescatprod','salescat.salescatid=salescatprod.salescatid');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','salesorders','salesorderdetails.orderno=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','salesorderdetails','salesorders.orderno=salesorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','stockmaster','salesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salesorderdetails','stockmaster.stockid=salesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('salesorders','custbranch','salesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','salesorders','custbranch.branchcode=salesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('salesorders','shippers','salesorders.debtorno=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','salesorders','shippers.shipper_id=salesorders.debtorno');
INSERT INTO `reportlinks` VALUES ('salesorders','locations','salesorders.fromstkloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','salesorders','locations.loccode=salesorders.fromstkloc');
INSERT INTO `reportlinks` VALUES ('securitygroups','securityroles','securitygroups.secroleid=securityroles.secroleid');
INSERT INTO `reportlinks` VALUES ('securityroles','securitygroups','securityroles.secroleid=securitygroups.secroleid');
INSERT INTO `reportlinks` VALUES ('securitygroups','securitytokens','securitygroups.tokenid=securitytokens.tokenid');
INSERT INTO `reportlinks` VALUES ('securitytokens','securitygroups','securitytokens.tokenid=securitygroups.tokenid');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','shipments','shipmentcharges.shiptref=shipments.shiptref');
INSERT INTO `reportlinks` VALUES ('shipments','shipmentcharges','shipments.shiptref=shipmentcharges.shiptref');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','systypes','shipmentcharges.transtype=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','shipmentcharges','systypes.typeid=shipmentcharges.transtype');
INSERT INTO `reportlinks` VALUES ('shipments','suppliers','shipments.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','shipments','suppliers.supplierid=shipments.supplierid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','stockmaster','stockcheckfreeze.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcheckfreeze','stockmaster.stockid=stockcheckfreeze.stockid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','locations','stockcheckfreeze.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcheckfreeze','locations.loccode=stockcheckfreeze.loccode');
INSERT INTO `reportlinks` VALUES ('stockcounts','stockmaster','stockcounts.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcounts','stockmaster.stockid=stockcounts.stockid');
INSERT INTO `reportlinks` VALUES ('stockcounts','locations','stockcounts.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcounts','locations.loccode=stockcounts.loccode');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcategory','stockmaster.categoryid=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','stockmaster','stockcategory.categoryid=stockmaster.categoryid');
INSERT INTO `reportlinks` VALUES ('stockmaster','taxcategories','stockmaster.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','stockmaster','taxcategories.taxcatid=stockmaster.taxcatid');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockmaster','stockmoves.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockmoves','stockmaster.stockid=stockmoves.stockid');
INSERT INTO `reportlinks` VALUES ('stockmoves','systypes','stockmoves.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','stockmoves','systypes.typeid=stockmoves.type');
INSERT INTO `reportlinks` VALUES ('stockmoves','locations','stockmoves.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockmoves','locations.loccode=stockmoves.loccode');
INSERT INTO `reportlinks` VALUES ('stockmoves','periods','stockmoves.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','stockmoves','periods.periodno=stockmoves.prd');
INSERT INTO `reportlinks` VALUES ('stockmovestaxes','taxauthorities','stockmovestaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','stockmovestaxes','taxauthorities.taxid=stockmovestaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockmaster','stockserialitems.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockserialitems','stockmaster.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','locations','stockserialitems.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockserialitems','locations.loccode=stockserialitems.loccode');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockmoves','stockserialmoves.stockmoveno=stockmoves.stkmoveno');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockserialmoves','stockmoves.stkmoveno=stockserialmoves.stockmoveno');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockserialitems','stockserialmoves.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockserialmoves','stockserialitems.stockid=stockserialmoves.stockid');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocfrom=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocto=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocto');
INSERT INTO `reportlinks` VALUES ('suppliercontacts','suppliers','suppliercontacts.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','suppliercontacts','suppliers.supplierid=suppliercontacts.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','currencies','suppliers.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','suppliers','currencies.currabrev=suppliers.currcode');
INSERT INTO `reportlinks` VALUES ('suppliers','paymentterms','suppliers.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','suppliers','paymentterms.termsindicator=suppliers.paymentterms');
INSERT INTO `reportlinks` VALUES ('suppliers','taxgroups','suppliers.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','suppliers','taxgroups.taxgroupid=suppliers.taxgroupid');
INSERT INTO `reportlinks` VALUES ('supptrans','systypes','supptrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','supptrans','systypes.typeid=supptrans.type');
INSERT INTO `reportlinks` VALUES ('supptrans','suppliers','supptrans.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','supptrans','suppliers.supplierid=supptrans.supplierno');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','taxauthorities','supptranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','supptranstaxes','taxauthorities.taxid=supptranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','supptrans','supptranstaxes.supptransid=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','supptranstaxes','supptrans.id=supptranstaxes.supptransid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.taxglcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.taxglcode');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.purchtaxglaccount=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.purchtaxglaccount');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxauthorities','taxauthrates.taxauthority=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxauthrates','taxauthorities.taxid=taxauthrates.taxauthority');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxcategories','taxauthrates.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','taxauthrates','taxcategories.taxcatid=taxauthrates.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxprovinces','taxauthrates.dispatchtaxprovince=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','taxauthrates','taxprovinces.taxprovinceid=taxauthrates.dispatchtaxprovince');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxgroups','taxgrouptaxes.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','taxgrouptaxes','taxgroups.taxgroupid=taxgrouptaxes.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxauthorities','taxgrouptaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxgrouptaxes','taxauthorities.taxid=taxgrouptaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('workcentres','locations','workcentres.location=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','workcentres','locations.loccode=workcentres.location');
INSERT INTO `reportlinks` VALUES ('worksorders','locations','worksorders.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','worksorders','locations.loccode=worksorders.loccode');
INSERT INTO `reportlinks` VALUES ('worksorders','stockmaster','worksorders.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','worksorders','stockmaster.stockid=worksorders.stockid');
INSERT INTO `reportlinks` VALUES ('www_users','locations','www_users.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','www_users','locations.loccode=www_users.defaultlocation');
INSERT INTO `reportlinks` VALUES ('accountgroups','accountsection','accountgroups.sectioninaccounts=accountsection.sectionid');
INSERT INTO `reportlinks` VALUES ('accountsection','accountgroups','accountsection.sectionid=accountgroups.sectioninaccounts');
INSERT INTO `reportlinks` VALUES ('bankaccounts','chartmaster','bankaccounts.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','bankaccounts','chartmaster.accountcode=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('banktrans','systypes','banktrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','banktrans','systypes.typeid=banktrans.type');
INSERT INTO `reportlinks` VALUES ('banktrans','bankaccounts','banktrans.bankact=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('bankaccounts','banktrans','bankaccounts.accountcode=banktrans.bankact');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.parent=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.parent');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.component');
INSERT INTO `reportlinks` VALUES ('bom','workcentres','bom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','bom','workcentres.code=bom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('bom','locations','bom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','bom','locations.loccode=bom.loccode');
INSERT INTO `reportlinks` VALUES ('buckets','workcentres','buckets.workcentre=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','buckets','workcentres.code=buckets.workcentre');
INSERT INTO `reportlinks` VALUES ('chartdetails','chartmaster','chartdetails.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','chartdetails','chartmaster.accountcode=chartdetails.accountcode');
INSERT INTO `reportlinks` VALUES ('chartdetails','periods','chartdetails.period=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','chartdetails','periods.periodno=chartdetails.period');
INSERT INTO `reportlinks` VALUES ('chartmaster','accountgroups','chartmaster.group_=accountgroups.groupname');
INSERT INTO `reportlinks` VALUES ('accountgroups','chartmaster','accountgroups.groupname=chartmaster.group_');
INSERT INTO `reportlinks` VALUES ('contractbom','workcentres','contractbom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','contractbom','workcentres.code=contractbom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('contractbom','locations','contractbom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','contractbom','locations.loccode=contractbom.loccode');
INSERT INTO `reportlinks` VALUES ('contractbom','stockmaster','contractbom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','contractbom','stockmaster.stockid=contractbom.component');
INSERT INTO `reportlinks` VALUES ('contractreqts','contracts','contractreqts.contract=contracts.contractref');
INSERT INTO `reportlinks` VALUES ('contracts','contractreqts','contracts.contractref=contractreqts.contract');
INSERT INTO `reportlinks` VALUES ('contracts','custbranch','contracts.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','contracts','custbranch.debtorno=contracts.debtorno');
INSERT INTO `reportlinks` VALUES ('contracts','stockcategory','contracts.branchcode=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','contracts','stockcategory.categoryid=contracts.branchcode');
INSERT INTO `reportlinks` VALUES ('contracts','salestypes','contracts.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','contracts','salestypes.typeabbrev=contracts.typeabbrev');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocfrom=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocto=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocto');
INSERT INTO `reportlinks` VALUES ('custbranch','debtorsmaster','custbranch.debtorno=debtorsmaster.debtorno');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','custbranch','debtorsmaster.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','areas','custbranch.area=areas.areacode');
INSERT INTO `reportlinks` VALUES ('areas','custbranch','areas.areacode=custbranch.area');
INSERT INTO `reportlinks` VALUES ('custbranch','salesman','custbranch.salesman=salesman.salesmancode');
INSERT INTO `reportlinks` VALUES ('salesman','custbranch','salesman.salesmancode=custbranch.salesman');
INSERT INTO `reportlinks` VALUES ('custbranch','locations','custbranch.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','custbranch','locations.loccode=custbranch.defaultlocation');
INSERT INTO `reportlinks` VALUES ('custbranch','shippers','custbranch.defaultshipvia=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','custbranch','shippers.shipper_id=custbranch.defaultshipvia');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','holdreasons','debtorsmaster.holdreason=holdreasons.reasoncode');
INSERT INTO `reportlinks` VALUES ('holdreasons','debtorsmaster','holdreasons.reasoncode=debtorsmaster.holdreason');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','currencies','debtorsmaster.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','debtorsmaster','currencies.currabrev=debtorsmaster.currcode');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','paymentterms','debtorsmaster.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','debtorsmaster','paymentterms.termsindicator=debtorsmaster.paymentterms');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','salestypes','debtorsmaster.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','debtorsmaster','salestypes.typeabbrev=debtorsmaster.salestype');
INSERT INTO `reportlinks` VALUES ('debtortrans','custbranch','debtortrans.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','debtortrans','custbranch.debtorno=debtortrans.debtorno');
INSERT INTO `reportlinks` VALUES ('debtortrans','systypes','debtortrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','debtortrans','systypes.typeid=debtortrans.type');
INSERT INTO `reportlinks` VALUES ('debtortrans','periods','debtortrans.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','debtortrans','periods.periodno=debtortrans.prd');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','taxauthorities','debtortranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','debtortranstaxes','taxauthorities.taxid=debtortranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','debtortrans','debtortranstaxes.debtortransid=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','debtortranstaxes','debtortrans.id=debtortranstaxes.debtortransid');
INSERT INTO `reportlinks` VALUES ('discountmatrix','salestypes','discountmatrix.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','discountmatrix','salestypes.typeabbrev=discountmatrix.salestype');
INSERT INTO `reportlinks` VALUES ('freightcosts','locations','freightcosts.locationfrom=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','freightcosts','locations.loccode=freightcosts.locationfrom');
INSERT INTO `reportlinks` VALUES ('freightcosts','shippers','freightcosts.shipperid=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','freightcosts','shippers.shipper_id=freightcosts.shipperid');
INSERT INTO `reportlinks` VALUES ('gltrans','chartmaster','gltrans.account=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','gltrans','chartmaster.accountcode=gltrans.account');
INSERT INTO `reportlinks` VALUES ('gltrans','systypes','gltrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','gltrans','systypes.typeid=gltrans.type');
INSERT INTO `reportlinks` VALUES ('gltrans','periods','gltrans.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','gltrans','periods.periodno=gltrans.periodno');
INSERT INTO `reportlinks` VALUES ('grns','suppliers','grns.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','grns','suppliers.supplierid=grns.supplierid');
INSERT INTO `reportlinks` VALUES ('grns','purchorderdetails','grns.podetailitem=purchorderdetails.podetailitem');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','grns','purchorderdetails.podetailitem=grns.podetailitem');
INSERT INTO `reportlinks` VALUES ('locations','taxprovinces','locations.taxprovinceid=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','locations','taxprovinces.taxprovinceid=locations.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('locstock','locations','locstock.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','locstock','locations.loccode=locstock.loccode');
INSERT INTO `reportlinks` VALUES ('locstock','stockmaster','locstock.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','locstock','stockmaster.stockid=locstock.stockid');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.shiploc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.shiploc');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.recloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.recloc');
INSERT INTO `reportlinks` VALUES ('loctransfers','stockmaster','loctransfers.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','loctransfers','stockmaster.stockid=loctransfers.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','stockmaster','orderdeliverydifferenceslog.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','orderdeliverydifferencesl','stockmaster.stockid=orderdeliverydifferenceslog.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','custbranch','orderdeliverydifferenceslog.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','orderdeliverydifferencesl','custbranch.debtorno=orderdeliverydifferenceslog.debtorno');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','salesorders','orderdeliverydifferenceslog.branchcode=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','orderdeliverydifferencesl','salesorders.orderno=orderdeliverydifferenceslog.branchcode');
INSERT INTO `reportlinks` VALUES ('prices','stockmaster','prices.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','prices','stockmaster.stockid=prices.stockid');
INSERT INTO `reportlinks` VALUES ('prices','currencies','prices.currabrev=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','prices','currencies.currabrev=prices.currabrev');
INSERT INTO `reportlinks` VALUES ('prices','salestypes','prices.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','prices','salestypes.typeabbrev=prices.typeabbrev');
INSERT INTO `reportlinks` VALUES ('purchdata','stockmaster','purchdata.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','purchdata','stockmaster.stockid=purchdata.stockid');
INSERT INTO `reportlinks` VALUES ('purchdata','suppliers','purchdata.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchdata','suppliers.supplierid=purchdata.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','purchorders','purchorderdetails.orderno=purchorders.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','purchorderdetails','purchorders.orderno=purchorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','suppliers','purchorders.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchorders','suppliers.supplierid=purchorders.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorders','locations','purchorders.intostocklocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','purchorders','locations.loccode=purchorders.intostocklocation');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','custbranch','recurringsalesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','recurringsalesorders','custbranch.branchcode=recurringsalesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','recurringsalesorders','recurrsalesorderdetails.recurrorderno=recurringsalesorders.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','recurrsalesorderdetails','recurringsalesorders.recurrorderno=recurrsalesorderdetails.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','stockmaster','recurrsalesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','recurrsalesorderdetails','stockmaster.stockid=recurrsalesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('reportcolumns','reportheaders','reportcolumns.reportid=reportheaders.reportid');
INSERT INTO `reportlinks` VALUES ('reportheaders','reportcolumns','reportheaders.reportid=reportcolumns.reportid');
INSERT INTO `reportlinks` VALUES ('salesanalysis','periods','salesanalysis.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','salesanalysis','periods.periodno=salesanalysis.periodno');
INSERT INTO `reportlinks` VALUES ('salescatprod','stockmaster','salescatprod.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salescatprod','stockmaster.stockid=salescatprod.stockid');
INSERT INTO `reportlinks` VALUES ('salescatprod','salescat','salescatprod.salescatid=salescat.salescatid');
INSERT INTO `reportlinks` VALUES ('salescat','salescatprod','salescat.salescatid=salescatprod.salescatid');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','salesorders','salesorderdetails.orderno=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','salesorderdetails','salesorders.orderno=salesorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','stockmaster','salesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salesorderdetails','stockmaster.stockid=salesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('salesorders','custbranch','salesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','salesorders','custbranch.branchcode=salesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('salesorders','shippers','salesorders.debtorno=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','salesorders','shippers.shipper_id=salesorders.debtorno');
INSERT INTO `reportlinks` VALUES ('salesorders','locations','salesorders.fromstkloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','salesorders','locations.loccode=salesorders.fromstkloc');
INSERT INTO `reportlinks` VALUES ('securitygroups','securityroles','securitygroups.secroleid=securityroles.secroleid');
INSERT INTO `reportlinks` VALUES ('securityroles','securitygroups','securityroles.secroleid=securitygroups.secroleid');
INSERT INTO `reportlinks` VALUES ('securitygroups','securitytokens','securitygroups.tokenid=securitytokens.tokenid');
INSERT INTO `reportlinks` VALUES ('securitytokens','securitygroups','securitytokens.tokenid=securitygroups.tokenid');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','shipments','shipmentcharges.shiptref=shipments.shiptref');
INSERT INTO `reportlinks` VALUES ('shipments','shipmentcharges','shipments.shiptref=shipmentcharges.shiptref');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','systypes','shipmentcharges.transtype=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','shipmentcharges','systypes.typeid=shipmentcharges.transtype');
INSERT INTO `reportlinks` VALUES ('shipments','suppliers','shipments.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','shipments','suppliers.supplierid=shipments.supplierid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','stockmaster','stockcheckfreeze.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcheckfreeze','stockmaster.stockid=stockcheckfreeze.stockid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','locations','stockcheckfreeze.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcheckfreeze','locations.loccode=stockcheckfreeze.loccode');
INSERT INTO `reportlinks` VALUES ('stockcounts','stockmaster','stockcounts.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcounts','stockmaster.stockid=stockcounts.stockid');
INSERT INTO `reportlinks` VALUES ('stockcounts','locations','stockcounts.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcounts','locations.loccode=stockcounts.loccode');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcategory','stockmaster.categoryid=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','stockmaster','stockcategory.categoryid=stockmaster.categoryid');
INSERT INTO `reportlinks` VALUES ('stockmaster','taxcategories','stockmaster.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','stockmaster','taxcategories.taxcatid=stockmaster.taxcatid');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockmaster','stockmoves.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockmoves','stockmaster.stockid=stockmoves.stockid');
INSERT INTO `reportlinks` VALUES ('stockmoves','systypes','stockmoves.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','stockmoves','systypes.typeid=stockmoves.type');
INSERT INTO `reportlinks` VALUES ('stockmoves','locations','stockmoves.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockmoves','locations.loccode=stockmoves.loccode');
INSERT INTO `reportlinks` VALUES ('stockmoves','periods','stockmoves.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','stockmoves','periods.periodno=stockmoves.prd');
INSERT INTO `reportlinks` VALUES ('stockmovestaxes','taxauthorities','stockmovestaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','stockmovestaxes','taxauthorities.taxid=stockmovestaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockmaster','stockserialitems.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockserialitems','stockmaster.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','locations','stockserialitems.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockserialitems','locations.loccode=stockserialitems.loccode');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockmoves','stockserialmoves.stockmoveno=stockmoves.stkmoveno');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockserialmoves','stockmoves.stkmoveno=stockserialmoves.stockmoveno');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockserialitems','stockserialmoves.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockserialmoves','stockserialitems.stockid=stockserialmoves.stockid');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocfrom=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocto=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocto');
INSERT INTO `reportlinks` VALUES ('suppliercontacts','suppliers','suppliercontacts.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','suppliercontacts','suppliers.supplierid=suppliercontacts.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','currencies','suppliers.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','suppliers','currencies.currabrev=suppliers.currcode');
INSERT INTO `reportlinks` VALUES ('suppliers','paymentterms','suppliers.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','suppliers','paymentterms.termsindicator=suppliers.paymentterms');
INSERT INTO `reportlinks` VALUES ('suppliers','taxgroups','suppliers.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','suppliers','taxgroups.taxgroupid=suppliers.taxgroupid');
INSERT INTO `reportlinks` VALUES ('supptrans','systypes','supptrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','supptrans','systypes.typeid=supptrans.type');
INSERT INTO `reportlinks` VALUES ('supptrans','suppliers','supptrans.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','supptrans','suppliers.supplierid=supptrans.supplierno');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','taxauthorities','supptranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','supptranstaxes','taxauthorities.taxid=supptranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','supptrans','supptranstaxes.supptransid=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','supptranstaxes','supptrans.id=supptranstaxes.supptransid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.taxglcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.taxglcode');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.purchtaxglaccount=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.purchtaxglaccount');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxauthorities','taxauthrates.taxauthority=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxauthrates','taxauthorities.taxid=taxauthrates.taxauthority');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxcategories','taxauthrates.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','taxauthrates','taxcategories.taxcatid=taxauthrates.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxprovinces','taxauthrates.dispatchtaxprovince=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','taxauthrates','taxprovinces.taxprovinceid=taxauthrates.dispatchtaxprovince');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxgroups','taxgrouptaxes.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','taxgrouptaxes','taxgroups.taxgroupid=taxgrouptaxes.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxauthorities','taxgrouptaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxgrouptaxes','taxauthorities.taxid=taxgrouptaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('workcentres','locations','workcentres.location=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','workcentres','locations.loccode=workcentres.location');
INSERT INTO `reportlinks` VALUES ('worksorders','locations','worksorders.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','worksorders','locations.loccode=worksorders.loccode');
INSERT INTO `reportlinks` VALUES ('worksorders','stockmaster','worksorders.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','worksorders','stockmaster.stockid=worksorders.stockid');
INSERT INTO `reportlinks` VALUES ('www_users','locations','www_users.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','www_users','locations.loccode=www_users.defaultlocation');
INSERT INTO `reportlinks` VALUES ('accountgroups','accountsection','accountgroups.sectioninaccounts=accountsection.sectionid');
INSERT INTO `reportlinks` VALUES ('accountsection','accountgroups','accountsection.sectionid=accountgroups.sectioninaccounts');
INSERT INTO `reportlinks` VALUES ('bankaccounts','chartmaster','bankaccounts.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','bankaccounts','chartmaster.accountcode=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('banktrans','systypes','banktrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','banktrans','systypes.typeid=banktrans.type');
INSERT INTO `reportlinks` VALUES ('banktrans','bankaccounts','banktrans.bankact=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('bankaccounts','banktrans','bankaccounts.accountcode=banktrans.bankact');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.parent=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.parent');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.component');
INSERT INTO `reportlinks` VALUES ('bom','workcentres','bom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','bom','workcentres.code=bom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('bom','locations','bom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','bom','locations.loccode=bom.loccode');
INSERT INTO `reportlinks` VALUES ('buckets','workcentres','buckets.workcentre=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','buckets','workcentres.code=buckets.workcentre');
INSERT INTO `reportlinks` VALUES ('chartdetails','chartmaster','chartdetails.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','chartdetails','chartmaster.accountcode=chartdetails.accountcode');
INSERT INTO `reportlinks` VALUES ('chartdetails','periods','chartdetails.period=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','chartdetails','periods.periodno=chartdetails.period');
INSERT INTO `reportlinks` VALUES ('chartmaster','accountgroups','chartmaster.group_=accountgroups.groupname');
INSERT INTO `reportlinks` VALUES ('accountgroups','chartmaster','accountgroups.groupname=chartmaster.group_');
INSERT INTO `reportlinks` VALUES ('contractbom','workcentres','contractbom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','contractbom','workcentres.code=contractbom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('contractbom','locations','contractbom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','contractbom','locations.loccode=contractbom.loccode');
INSERT INTO `reportlinks` VALUES ('contractbom','stockmaster','contractbom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','contractbom','stockmaster.stockid=contractbom.component');
INSERT INTO `reportlinks` VALUES ('contractreqts','contracts','contractreqts.contract=contracts.contractref');
INSERT INTO `reportlinks` VALUES ('contracts','contractreqts','contracts.contractref=contractreqts.contract');
INSERT INTO `reportlinks` VALUES ('contracts','custbranch','contracts.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','contracts','custbranch.debtorno=contracts.debtorno');
INSERT INTO `reportlinks` VALUES ('contracts','stockcategory','contracts.branchcode=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','contracts','stockcategory.categoryid=contracts.branchcode');
INSERT INTO `reportlinks` VALUES ('contracts','salestypes','contracts.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','contracts','salestypes.typeabbrev=contracts.typeabbrev');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocfrom=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocto=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocto');
INSERT INTO `reportlinks` VALUES ('custbranch','debtorsmaster','custbranch.debtorno=debtorsmaster.debtorno');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','custbranch','debtorsmaster.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','areas','custbranch.area=areas.areacode');
INSERT INTO `reportlinks` VALUES ('areas','custbranch','areas.areacode=custbranch.area');
INSERT INTO `reportlinks` VALUES ('custbranch','salesman','custbranch.salesman=salesman.salesmancode');
INSERT INTO `reportlinks` VALUES ('salesman','custbranch','salesman.salesmancode=custbranch.salesman');
INSERT INTO `reportlinks` VALUES ('custbranch','locations','custbranch.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','custbranch','locations.loccode=custbranch.defaultlocation');
INSERT INTO `reportlinks` VALUES ('custbranch','shippers','custbranch.defaultshipvia=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','custbranch','shippers.shipper_id=custbranch.defaultshipvia');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','holdreasons','debtorsmaster.holdreason=holdreasons.reasoncode');
INSERT INTO `reportlinks` VALUES ('holdreasons','debtorsmaster','holdreasons.reasoncode=debtorsmaster.holdreason');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','currencies','debtorsmaster.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','debtorsmaster','currencies.currabrev=debtorsmaster.currcode');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','paymentterms','debtorsmaster.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','debtorsmaster','paymentterms.termsindicator=debtorsmaster.paymentterms');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','salestypes','debtorsmaster.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','debtorsmaster','salestypes.typeabbrev=debtorsmaster.salestype');
INSERT INTO `reportlinks` VALUES ('debtortrans','custbranch','debtortrans.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','debtortrans','custbranch.debtorno=debtortrans.debtorno');
INSERT INTO `reportlinks` VALUES ('debtortrans','systypes','debtortrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','debtortrans','systypes.typeid=debtortrans.type');
INSERT INTO `reportlinks` VALUES ('debtortrans','periods','debtortrans.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','debtortrans','periods.periodno=debtortrans.prd');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','taxauthorities','debtortranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','debtortranstaxes','taxauthorities.taxid=debtortranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','debtortrans','debtortranstaxes.debtortransid=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','debtortranstaxes','debtortrans.id=debtortranstaxes.debtortransid');
INSERT INTO `reportlinks` VALUES ('discountmatrix','salestypes','discountmatrix.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','discountmatrix','salestypes.typeabbrev=discountmatrix.salestype');
INSERT INTO `reportlinks` VALUES ('freightcosts','locations','freightcosts.locationfrom=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','freightcosts','locations.loccode=freightcosts.locationfrom');
INSERT INTO `reportlinks` VALUES ('freightcosts','shippers','freightcosts.shipperid=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','freightcosts','shippers.shipper_id=freightcosts.shipperid');
INSERT INTO `reportlinks` VALUES ('gltrans','chartmaster','gltrans.account=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','gltrans','chartmaster.accountcode=gltrans.account');
INSERT INTO `reportlinks` VALUES ('gltrans','systypes','gltrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','gltrans','systypes.typeid=gltrans.type');
INSERT INTO `reportlinks` VALUES ('gltrans','periods','gltrans.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','gltrans','periods.periodno=gltrans.periodno');
INSERT INTO `reportlinks` VALUES ('grns','suppliers','grns.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','grns','suppliers.supplierid=grns.supplierid');
INSERT INTO `reportlinks` VALUES ('grns','purchorderdetails','grns.podetailitem=purchorderdetails.podetailitem');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','grns','purchorderdetails.podetailitem=grns.podetailitem');
INSERT INTO `reportlinks` VALUES ('locations','taxprovinces','locations.taxprovinceid=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','locations','taxprovinces.taxprovinceid=locations.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('locstock','locations','locstock.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','locstock','locations.loccode=locstock.loccode');
INSERT INTO `reportlinks` VALUES ('locstock','stockmaster','locstock.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','locstock','stockmaster.stockid=locstock.stockid');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.shiploc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.shiploc');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.recloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.recloc');
INSERT INTO `reportlinks` VALUES ('loctransfers','stockmaster','loctransfers.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','loctransfers','stockmaster.stockid=loctransfers.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','stockmaster','orderdeliverydifferenceslog.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','orderdeliverydifferencesl','stockmaster.stockid=orderdeliverydifferenceslog.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','custbranch','orderdeliverydifferenceslog.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','orderdeliverydifferencesl','custbranch.debtorno=orderdeliverydifferenceslog.debtorno');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','salesorders','orderdeliverydifferenceslog.branchcode=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','orderdeliverydifferencesl','salesorders.orderno=orderdeliverydifferenceslog.branchcode');
INSERT INTO `reportlinks` VALUES ('prices','stockmaster','prices.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','prices','stockmaster.stockid=prices.stockid');
INSERT INTO `reportlinks` VALUES ('prices','currencies','prices.currabrev=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','prices','currencies.currabrev=prices.currabrev');
INSERT INTO `reportlinks` VALUES ('prices','salestypes','prices.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','prices','salestypes.typeabbrev=prices.typeabbrev');
INSERT INTO `reportlinks` VALUES ('purchdata','stockmaster','purchdata.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','purchdata','stockmaster.stockid=purchdata.stockid');
INSERT INTO `reportlinks` VALUES ('purchdata','suppliers','purchdata.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchdata','suppliers.supplierid=purchdata.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','purchorders','purchorderdetails.orderno=purchorders.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','purchorderdetails','purchorders.orderno=purchorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','suppliers','purchorders.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchorders','suppliers.supplierid=purchorders.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorders','locations','purchorders.intostocklocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','purchorders','locations.loccode=purchorders.intostocklocation');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','custbranch','recurringsalesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','recurringsalesorders','custbranch.branchcode=recurringsalesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','recurringsalesorders','recurrsalesorderdetails.recurrorderno=recurringsalesorders.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','recurrsalesorderdetails','recurringsalesorders.recurrorderno=recurrsalesorderdetails.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','stockmaster','recurrsalesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','recurrsalesorderdetails','stockmaster.stockid=recurrsalesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('reportcolumns','reportheaders','reportcolumns.reportid=reportheaders.reportid');
INSERT INTO `reportlinks` VALUES ('reportheaders','reportcolumns','reportheaders.reportid=reportcolumns.reportid');
INSERT INTO `reportlinks` VALUES ('salesanalysis','periods','salesanalysis.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','salesanalysis','periods.periodno=salesanalysis.periodno');
INSERT INTO `reportlinks` VALUES ('salescatprod','stockmaster','salescatprod.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salescatprod','stockmaster.stockid=salescatprod.stockid');
INSERT INTO `reportlinks` VALUES ('salescatprod','salescat','salescatprod.salescatid=salescat.salescatid');
INSERT INTO `reportlinks` VALUES ('salescat','salescatprod','salescat.salescatid=salescatprod.salescatid');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','salesorders','salesorderdetails.orderno=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','salesorderdetails','salesorders.orderno=salesorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','stockmaster','salesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salesorderdetails','stockmaster.stockid=salesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('salesorders','custbranch','salesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','salesorders','custbranch.branchcode=salesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('salesorders','shippers','salesorders.debtorno=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','salesorders','shippers.shipper_id=salesorders.debtorno');
INSERT INTO `reportlinks` VALUES ('salesorders','locations','salesorders.fromstkloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','salesorders','locations.loccode=salesorders.fromstkloc');
INSERT INTO `reportlinks` VALUES ('securitygroups','securityroles','securitygroups.secroleid=securityroles.secroleid');
INSERT INTO `reportlinks` VALUES ('securityroles','securitygroups','securityroles.secroleid=securitygroups.secroleid');
INSERT INTO `reportlinks` VALUES ('securitygroups','securitytokens','securitygroups.tokenid=securitytokens.tokenid');
INSERT INTO `reportlinks` VALUES ('securitytokens','securitygroups','securitytokens.tokenid=securitygroups.tokenid');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','shipments','shipmentcharges.shiptref=shipments.shiptref');
INSERT INTO `reportlinks` VALUES ('shipments','shipmentcharges','shipments.shiptref=shipmentcharges.shiptref');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','systypes','shipmentcharges.transtype=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','shipmentcharges','systypes.typeid=shipmentcharges.transtype');
INSERT INTO `reportlinks` VALUES ('shipments','suppliers','shipments.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','shipments','suppliers.supplierid=shipments.supplierid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','stockmaster','stockcheckfreeze.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcheckfreeze','stockmaster.stockid=stockcheckfreeze.stockid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','locations','stockcheckfreeze.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcheckfreeze','locations.loccode=stockcheckfreeze.loccode');
INSERT INTO `reportlinks` VALUES ('stockcounts','stockmaster','stockcounts.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcounts','stockmaster.stockid=stockcounts.stockid');
INSERT INTO `reportlinks` VALUES ('stockcounts','locations','stockcounts.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcounts','locations.loccode=stockcounts.loccode');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcategory','stockmaster.categoryid=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','stockmaster','stockcategory.categoryid=stockmaster.categoryid');
INSERT INTO `reportlinks` VALUES ('stockmaster','taxcategories','stockmaster.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','stockmaster','taxcategories.taxcatid=stockmaster.taxcatid');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockmaster','stockmoves.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockmoves','stockmaster.stockid=stockmoves.stockid');
INSERT INTO `reportlinks` VALUES ('stockmoves','systypes','stockmoves.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','stockmoves','systypes.typeid=stockmoves.type');
INSERT INTO `reportlinks` VALUES ('stockmoves','locations','stockmoves.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockmoves','locations.loccode=stockmoves.loccode');
INSERT INTO `reportlinks` VALUES ('stockmoves','periods','stockmoves.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','stockmoves','periods.periodno=stockmoves.prd');
INSERT INTO `reportlinks` VALUES ('stockmovestaxes','taxauthorities','stockmovestaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','stockmovestaxes','taxauthorities.taxid=stockmovestaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockmaster','stockserialitems.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockserialitems','stockmaster.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','locations','stockserialitems.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockserialitems','locations.loccode=stockserialitems.loccode');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockmoves','stockserialmoves.stockmoveno=stockmoves.stkmoveno');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockserialmoves','stockmoves.stkmoveno=stockserialmoves.stockmoveno');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockserialitems','stockserialmoves.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockserialmoves','stockserialitems.stockid=stockserialmoves.stockid');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocfrom=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocto=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocto');
INSERT INTO `reportlinks` VALUES ('suppliercontacts','suppliers','suppliercontacts.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','suppliercontacts','suppliers.supplierid=suppliercontacts.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','currencies','suppliers.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','suppliers','currencies.currabrev=suppliers.currcode');
INSERT INTO `reportlinks` VALUES ('suppliers','paymentterms','suppliers.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','suppliers','paymentterms.termsindicator=suppliers.paymentterms');
INSERT INTO `reportlinks` VALUES ('suppliers','taxgroups','suppliers.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','suppliers','taxgroups.taxgroupid=suppliers.taxgroupid');
INSERT INTO `reportlinks` VALUES ('supptrans','systypes','supptrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','supptrans','systypes.typeid=supptrans.type');
INSERT INTO `reportlinks` VALUES ('supptrans','suppliers','supptrans.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','supptrans','suppliers.supplierid=supptrans.supplierno');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','taxauthorities','supptranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','supptranstaxes','taxauthorities.taxid=supptranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','supptrans','supptranstaxes.supptransid=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','supptranstaxes','supptrans.id=supptranstaxes.supptransid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.taxglcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.taxglcode');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.purchtaxglaccount=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.purchtaxglaccount');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxauthorities','taxauthrates.taxauthority=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxauthrates','taxauthorities.taxid=taxauthrates.taxauthority');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxcategories','taxauthrates.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','taxauthrates','taxcategories.taxcatid=taxauthrates.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxprovinces','taxauthrates.dispatchtaxprovince=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','taxauthrates','taxprovinces.taxprovinceid=taxauthrates.dispatchtaxprovince');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxgroups','taxgrouptaxes.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','taxgrouptaxes','taxgroups.taxgroupid=taxgrouptaxes.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxauthorities','taxgrouptaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxgrouptaxes','taxauthorities.taxid=taxgrouptaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('workcentres','locations','workcentres.location=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','workcentres','locations.loccode=workcentres.location');
INSERT INTO `reportlinks` VALUES ('worksorders','locations','worksorders.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','worksorders','locations.loccode=worksorders.loccode');
INSERT INTO `reportlinks` VALUES ('worksorders','stockmaster','worksorders.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','worksorders','stockmaster.stockid=worksorders.stockid');
INSERT INTO `reportlinks` VALUES ('www_users','locations','www_users.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','www_users','locations.loccode=www_users.defaultlocation');
INSERT INTO `reportlinks` VALUES ('accountgroups','accountsection','accountgroups.sectioninaccounts=accountsection.sectionid');
INSERT INTO `reportlinks` VALUES ('accountsection','accountgroups','accountsection.sectionid=accountgroups.sectioninaccounts');
INSERT INTO `reportlinks` VALUES ('bankaccounts','chartmaster','bankaccounts.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','bankaccounts','chartmaster.accountcode=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('banktrans','systypes','banktrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','banktrans','systypes.typeid=banktrans.type');
INSERT INTO `reportlinks` VALUES ('banktrans','bankaccounts','banktrans.bankact=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('bankaccounts','banktrans','bankaccounts.accountcode=banktrans.bankact');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.parent=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.parent');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.component');
INSERT INTO `reportlinks` VALUES ('bom','workcentres','bom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','bom','workcentres.code=bom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('bom','locations','bom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','bom','locations.loccode=bom.loccode');
INSERT INTO `reportlinks` VALUES ('buckets','workcentres','buckets.workcentre=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','buckets','workcentres.code=buckets.workcentre');
INSERT INTO `reportlinks` VALUES ('chartdetails','chartmaster','chartdetails.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','chartdetails','chartmaster.accountcode=chartdetails.accountcode');
INSERT INTO `reportlinks` VALUES ('chartdetails','periods','chartdetails.period=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','chartdetails','periods.periodno=chartdetails.period');
INSERT INTO `reportlinks` VALUES ('chartmaster','accountgroups','chartmaster.group_=accountgroups.groupname');
INSERT INTO `reportlinks` VALUES ('accountgroups','chartmaster','accountgroups.groupname=chartmaster.group_');
INSERT INTO `reportlinks` VALUES ('contractbom','workcentres','contractbom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','contractbom','workcentres.code=contractbom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('contractbom','locations','contractbom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','contractbom','locations.loccode=contractbom.loccode');
INSERT INTO `reportlinks` VALUES ('contractbom','stockmaster','contractbom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','contractbom','stockmaster.stockid=contractbom.component');
INSERT INTO `reportlinks` VALUES ('contractreqts','contracts','contractreqts.contract=contracts.contractref');
INSERT INTO `reportlinks` VALUES ('contracts','contractreqts','contracts.contractref=contractreqts.contract');
INSERT INTO `reportlinks` VALUES ('contracts','custbranch','contracts.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','contracts','custbranch.debtorno=contracts.debtorno');
INSERT INTO `reportlinks` VALUES ('contracts','stockcategory','contracts.branchcode=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','contracts','stockcategory.categoryid=contracts.branchcode');
INSERT INTO `reportlinks` VALUES ('contracts','salestypes','contracts.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','contracts','salestypes.typeabbrev=contracts.typeabbrev');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocfrom=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocto=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocto');
INSERT INTO `reportlinks` VALUES ('custbranch','debtorsmaster','custbranch.debtorno=debtorsmaster.debtorno');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','custbranch','debtorsmaster.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','areas','custbranch.area=areas.areacode');
INSERT INTO `reportlinks` VALUES ('areas','custbranch','areas.areacode=custbranch.area');
INSERT INTO `reportlinks` VALUES ('custbranch','salesman','custbranch.salesman=salesman.salesmancode');
INSERT INTO `reportlinks` VALUES ('salesman','custbranch','salesman.salesmancode=custbranch.salesman');
INSERT INTO `reportlinks` VALUES ('custbranch','locations','custbranch.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','custbranch','locations.loccode=custbranch.defaultlocation');
INSERT INTO `reportlinks` VALUES ('custbranch','shippers','custbranch.defaultshipvia=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','custbranch','shippers.shipper_id=custbranch.defaultshipvia');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','holdreasons','debtorsmaster.holdreason=holdreasons.reasoncode');
INSERT INTO `reportlinks` VALUES ('holdreasons','debtorsmaster','holdreasons.reasoncode=debtorsmaster.holdreason');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','currencies','debtorsmaster.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','debtorsmaster','currencies.currabrev=debtorsmaster.currcode');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','paymentterms','debtorsmaster.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','debtorsmaster','paymentterms.termsindicator=debtorsmaster.paymentterms');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','salestypes','debtorsmaster.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','debtorsmaster','salestypes.typeabbrev=debtorsmaster.salestype');
INSERT INTO `reportlinks` VALUES ('debtortrans','custbranch','debtortrans.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','debtortrans','custbranch.debtorno=debtortrans.debtorno');
INSERT INTO `reportlinks` VALUES ('debtortrans','systypes','debtortrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','debtortrans','systypes.typeid=debtortrans.type');
INSERT INTO `reportlinks` VALUES ('debtortrans','periods','debtortrans.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','debtortrans','periods.periodno=debtortrans.prd');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','taxauthorities','debtortranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','debtortranstaxes','taxauthorities.taxid=debtortranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','debtortrans','debtortranstaxes.debtortransid=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','debtortranstaxes','debtortrans.id=debtortranstaxes.debtortransid');
INSERT INTO `reportlinks` VALUES ('discountmatrix','salestypes','discountmatrix.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','discountmatrix','salestypes.typeabbrev=discountmatrix.salestype');
INSERT INTO `reportlinks` VALUES ('freightcosts','locations','freightcosts.locationfrom=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','freightcosts','locations.loccode=freightcosts.locationfrom');
INSERT INTO `reportlinks` VALUES ('freightcosts','shippers','freightcosts.shipperid=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','freightcosts','shippers.shipper_id=freightcosts.shipperid');
INSERT INTO `reportlinks` VALUES ('gltrans','chartmaster','gltrans.account=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','gltrans','chartmaster.accountcode=gltrans.account');
INSERT INTO `reportlinks` VALUES ('gltrans','systypes','gltrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','gltrans','systypes.typeid=gltrans.type');
INSERT INTO `reportlinks` VALUES ('gltrans','periods','gltrans.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','gltrans','periods.periodno=gltrans.periodno');
INSERT INTO `reportlinks` VALUES ('grns','suppliers','grns.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','grns','suppliers.supplierid=grns.supplierid');
INSERT INTO `reportlinks` VALUES ('grns','purchorderdetails','grns.podetailitem=purchorderdetails.podetailitem');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','grns','purchorderdetails.podetailitem=grns.podetailitem');
INSERT INTO `reportlinks` VALUES ('locations','taxprovinces','locations.taxprovinceid=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','locations','taxprovinces.taxprovinceid=locations.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('locstock','locations','locstock.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','locstock','locations.loccode=locstock.loccode');
INSERT INTO `reportlinks` VALUES ('locstock','stockmaster','locstock.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','locstock','stockmaster.stockid=locstock.stockid');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.shiploc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.shiploc');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.recloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.recloc');
INSERT INTO `reportlinks` VALUES ('loctransfers','stockmaster','loctransfers.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','loctransfers','stockmaster.stockid=loctransfers.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','stockmaster','orderdeliverydifferenceslog.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','orderdeliverydifferencesl','stockmaster.stockid=orderdeliverydifferenceslog.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','custbranch','orderdeliverydifferenceslog.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','orderdeliverydifferencesl','custbranch.debtorno=orderdeliverydifferenceslog.debtorno');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','salesorders','orderdeliverydifferenceslog.branchcode=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','orderdeliverydifferencesl','salesorders.orderno=orderdeliverydifferenceslog.branchcode');
INSERT INTO `reportlinks` VALUES ('prices','stockmaster','prices.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','prices','stockmaster.stockid=prices.stockid');
INSERT INTO `reportlinks` VALUES ('prices','currencies','prices.currabrev=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','prices','currencies.currabrev=prices.currabrev');
INSERT INTO `reportlinks` VALUES ('prices','salestypes','prices.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','prices','salestypes.typeabbrev=prices.typeabbrev');
INSERT INTO `reportlinks` VALUES ('purchdata','stockmaster','purchdata.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','purchdata','stockmaster.stockid=purchdata.stockid');
INSERT INTO `reportlinks` VALUES ('purchdata','suppliers','purchdata.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchdata','suppliers.supplierid=purchdata.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','purchorders','purchorderdetails.orderno=purchorders.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','purchorderdetails','purchorders.orderno=purchorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','suppliers','purchorders.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchorders','suppliers.supplierid=purchorders.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorders','locations','purchorders.intostocklocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','purchorders','locations.loccode=purchorders.intostocklocation');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','custbranch','recurringsalesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','recurringsalesorders','custbranch.branchcode=recurringsalesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','recurringsalesorders','recurrsalesorderdetails.recurrorderno=recurringsalesorders.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','recurrsalesorderdetails','recurringsalesorders.recurrorderno=recurrsalesorderdetails.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','stockmaster','recurrsalesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','recurrsalesorderdetails','stockmaster.stockid=recurrsalesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('reportcolumns','reportheaders','reportcolumns.reportid=reportheaders.reportid');
INSERT INTO `reportlinks` VALUES ('reportheaders','reportcolumns','reportheaders.reportid=reportcolumns.reportid');
INSERT INTO `reportlinks` VALUES ('salesanalysis','periods','salesanalysis.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','salesanalysis','periods.periodno=salesanalysis.periodno');
INSERT INTO `reportlinks` VALUES ('salescatprod','stockmaster','salescatprod.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salescatprod','stockmaster.stockid=salescatprod.stockid');
INSERT INTO `reportlinks` VALUES ('salescatprod','salescat','salescatprod.salescatid=salescat.salescatid');
INSERT INTO `reportlinks` VALUES ('salescat','salescatprod','salescat.salescatid=salescatprod.salescatid');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','salesorders','salesorderdetails.orderno=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','salesorderdetails','salesorders.orderno=salesorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','stockmaster','salesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salesorderdetails','stockmaster.stockid=salesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('salesorders','custbranch','salesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','salesorders','custbranch.branchcode=salesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('salesorders','shippers','salesorders.debtorno=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','salesorders','shippers.shipper_id=salesorders.debtorno');
INSERT INTO `reportlinks` VALUES ('salesorders','locations','salesorders.fromstkloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','salesorders','locations.loccode=salesorders.fromstkloc');
INSERT INTO `reportlinks` VALUES ('securitygroups','securityroles','securitygroups.secroleid=securityroles.secroleid');
INSERT INTO `reportlinks` VALUES ('securityroles','securitygroups','securityroles.secroleid=securitygroups.secroleid');
INSERT INTO `reportlinks` VALUES ('securitygroups','securitytokens','securitygroups.tokenid=securitytokens.tokenid');
INSERT INTO `reportlinks` VALUES ('securitytokens','securitygroups','securitytokens.tokenid=securitygroups.tokenid');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','shipments','shipmentcharges.shiptref=shipments.shiptref');
INSERT INTO `reportlinks` VALUES ('shipments','shipmentcharges','shipments.shiptref=shipmentcharges.shiptref');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','systypes','shipmentcharges.transtype=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','shipmentcharges','systypes.typeid=shipmentcharges.transtype');
INSERT INTO `reportlinks` VALUES ('shipments','suppliers','shipments.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','shipments','suppliers.supplierid=shipments.supplierid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','stockmaster','stockcheckfreeze.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcheckfreeze','stockmaster.stockid=stockcheckfreeze.stockid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','locations','stockcheckfreeze.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcheckfreeze','locations.loccode=stockcheckfreeze.loccode');
INSERT INTO `reportlinks` VALUES ('stockcounts','stockmaster','stockcounts.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcounts','stockmaster.stockid=stockcounts.stockid');
INSERT INTO `reportlinks` VALUES ('stockcounts','locations','stockcounts.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcounts','locations.loccode=stockcounts.loccode');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcategory','stockmaster.categoryid=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','stockmaster','stockcategory.categoryid=stockmaster.categoryid');
INSERT INTO `reportlinks` VALUES ('stockmaster','taxcategories','stockmaster.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','stockmaster','taxcategories.taxcatid=stockmaster.taxcatid');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockmaster','stockmoves.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockmoves','stockmaster.stockid=stockmoves.stockid');
INSERT INTO `reportlinks` VALUES ('stockmoves','systypes','stockmoves.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','stockmoves','systypes.typeid=stockmoves.type');
INSERT INTO `reportlinks` VALUES ('stockmoves','locations','stockmoves.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockmoves','locations.loccode=stockmoves.loccode');
INSERT INTO `reportlinks` VALUES ('stockmoves','periods','stockmoves.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','stockmoves','periods.periodno=stockmoves.prd');
INSERT INTO `reportlinks` VALUES ('stockmovestaxes','taxauthorities','stockmovestaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','stockmovestaxes','taxauthorities.taxid=stockmovestaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockmaster','stockserialitems.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockserialitems','stockmaster.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','locations','stockserialitems.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockserialitems','locations.loccode=stockserialitems.loccode');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockmoves','stockserialmoves.stockmoveno=stockmoves.stkmoveno');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockserialmoves','stockmoves.stkmoveno=stockserialmoves.stockmoveno');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockserialitems','stockserialmoves.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockserialmoves','stockserialitems.stockid=stockserialmoves.stockid');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocfrom=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocto=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocto');
INSERT INTO `reportlinks` VALUES ('suppliercontacts','suppliers','suppliercontacts.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','suppliercontacts','suppliers.supplierid=suppliercontacts.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','currencies','suppliers.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','suppliers','currencies.currabrev=suppliers.currcode');
INSERT INTO `reportlinks` VALUES ('suppliers','paymentterms','suppliers.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','suppliers','paymentterms.termsindicator=suppliers.paymentterms');
INSERT INTO `reportlinks` VALUES ('suppliers','taxgroups','suppliers.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','suppliers','taxgroups.taxgroupid=suppliers.taxgroupid');
INSERT INTO `reportlinks` VALUES ('supptrans','systypes','supptrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','supptrans','systypes.typeid=supptrans.type');
INSERT INTO `reportlinks` VALUES ('supptrans','suppliers','supptrans.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','supptrans','suppliers.supplierid=supptrans.supplierno');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','taxauthorities','supptranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','supptranstaxes','taxauthorities.taxid=supptranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','supptrans','supptranstaxes.supptransid=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','supptranstaxes','supptrans.id=supptranstaxes.supptransid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.taxglcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.taxglcode');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.purchtaxglaccount=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.purchtaxglaccount');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxauthorities','taxauthrates.taxauthority=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxauthrates','taxauthorities.taxid=taxauthrates.taxauthority');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxcategories','taxauthrates.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','taxauthrates','taxcategories.taxcatid=taxauthrates.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxprovinces','taxauthrates.dispatchtaxprovince=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','taxauthrates','taxprovinces.taxprovinceid=taxauthrates.dispatchtaxprovince');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxgroups','taxgrouptaxes.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','taxgrouptaxes','taxgroups.taxgroupid=taxgrouptaxes.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxauthorities','taxgrouptaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxgrouptaxes','taxauthorities.taxid=taxgrouptaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('workcentres','locations','workcentres.location=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','workcentres','locations.loccode=workcentres.location');
INSERT INTO `reportlinks` VALUES ('worksorders','locations','worksorders.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','worksorders','locations.loccode=worksorders.loccode');
INSERT INTO `reportlinks` VALUES ('worksorders','stockmaster','worksorders.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','worksorders','stockmaster.stockid=worksorders.stockid');
INSERT INTO `reportlinks` VALUES ('www_users','locations','www_users.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','www_users','locations.loccode=www_users.defaultlocation');
INSERT INTO `reportlinks` VALUES ('accountgroups','accountsection','accountgroups.sectioninaccounts=accountsection.sectionid');
INSERT INTO `reportlinks` VALUES ('accountsection','accountgroups','accountsection.sectionid=accountgroups.sectioninaccounts');
INSERT INTO `reportlinks` VALUES ('bankaccounts','chartmaster','bankaccounts.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','bankaccounts','chartmaster.accountcode=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('banktrans','systypes','banktrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','banktrans','systypes.typeid=banktrans.type');
INSERT INTO `reportlinks` VALUES ('banktrans','bankaccounts','banktrans.bankact=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('bankaccounts','banktrans','bankaccounts.accountcode=banktrans.bankact');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.parent=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.parent');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.component');
INSERT INTO `reportlinks` VALUES ('bom','workcentres','bom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','bom','workcentres.code=bom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('bom','locations','bom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','bom','locations.loccode=bom.loccode');
INSERT INTO `reportlinks` VALUES ('buckets','workcentres','buckets.workcentre=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','buckets','workcentres.code=buckets.workcentre');
INSERT INTO `reportlinks` VALUES ('chartdetails','chartmaster','chartdetails.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','chartdetails','chartmaster.accountcode=chartdetails.accountcode');
INSERT INTO `reportlinks` VALUES ('chartdetails','periods','chartdetails.period=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','chartdetails','periods.periodno=chartdetails.period');
INSERT INTO `reportlinks` VALUES ('chartmaster','accountgroups','chartmaster.group_=accountgroups.groupname');
INSERT INTO `reportlinks` VALUES ('accountgroups','chartmaster','accountgroups.groupname=chartmaster.group_');
INSERT INTO `reportlinks` VALUES ('contractbom','workcentres','contractbom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','contractbom','workcentres.code=contractbom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('contractbom','locations','contractbom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','contractbom','locations.loccode=contractbom.loccode');
INSERT INTO `reportlinks` VALUES ('contractbom','stockmaster','contractbom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','contractbom','stockmaster.stockid=contractbom.component');
INSERT INTO `reportlinks` VALUES ('contractreqts','contracts','contractreqts.contract=contracts.contractref');
INSERT INTO `reportlinks` VALUES ('contracts','contractreqts','contracts.contractref=contractreqts.contract');
INSERT INTO `reportlinks` VALUES ('contracts','custbranch','contracts.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','contracts','custbranch.debtorno=contracts.debtorno');
INSERT INTO `reportlinks` VALUES ('contracts','stockcategory','contracts.branchcode=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','contracts','stockcategory.categoryid=contracts.branchcode');
INSERT INTO `reportlinks` VALUES ('contracts','salestypes','contracts.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','contracts','salestypes.typeabbrev=contracts.typeabbrev');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocfrom=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocto=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocto');
INSERT INTO `reportlinks` VALUES ('custbranch','debtorsmaster','custbranch.debtorno=debtorsmaster.debtorno');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','custbranch','debtorsmaster.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','areas','custbranch.area=areas.areacode');
INSERT INTO `reportlinks` VALUES ('areas','custbranch','areas.areacode=custbranch.area');
INSERT INTO `reportlinks` VALUES ('custbranch','salesman','custbranch.salesman=salesman.salesmancode');
INSERT INTO `reportlinks` VALUES ('salesman','custbranch','salesman.salesmancode=custbranch.salesman');
INSERT INTO `reportlinks` VALUES ('custbranch','locations','custbranch.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','custbranch','locations.loccode=custbranch.defaultlocation');
INSERT INTO `reportlinks` VALUES ('custbranch','shippers','custbranch.defaultshipvia=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','custbranch','shippers.shipper_id=custbranch.defaultshipvia');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','holdreasons','debtorsmaster.holdreason=holdreasons.reasoncode');
INSERT INTO `reportlinks` VALUES ('holdreasons','debtorsmaster','holdreasons.reasoncode=debtorsmaster.holdreason');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','currencies','debtorsmaster.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','debtorsmaster','currencies.currabrev=debtorsmaster.currcode');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','paymentterms','debtorsmaster.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','debtorsmaster','paymentterms.termsindicator=debtorsmaster.paymentterms');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','salestypes','debtorsmaster.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','debtorsmaster','salestypes.typeabbrev=debtorsmaster.salestype');
INSERT INTO `reportlinks` VALUES ('debtortrans','custbranch','debtortrans.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','debtortrans','custbranch.debtorno=debtortrans.debtorno');
INSERT INTO `reportlinks` VALUES ('debtortrans','systypes','debtortrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','debtortrans','systypes.typeid=debtortrans.type');
INSERT INTO `reportlinks` VALUES ('debtortrans','periods','debtortrans.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','debtortrans','periods.periodno=debtortrans.prd');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','taxauthorities','debtortranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','debtortranstaxes','taxauthorities.taxid=debtortranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','debtortrans','debtortranstaxes.debtortransid=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','debtortranstaxes','debtortrans.id=debtortranstaxes.debtortransid');
INSERT INTO `reportlinks` VALUES ('discountmatrix','salestypes','discountmatrix.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','discountmatrix','salestypes.typeabbrev=discountmatrix.salestype');
INSERT INTO `reportlinks` VALUES ('freightcosts','locations','freightcosts.locationfrom=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','freightcosts','locations.loccode=freightcosts.locationfrom');
INSERT INTO `reportlinks` VALUES ('freightcosts','shippers','freightcosts.shipperid=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','freightcosts','shippers.shipper_id=freightcosts.shipperid');
INSERT INTO `reportlinks` VALUES ('gltrans','chartmaster','gltrans.account=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','gltrans','chartmaster.accountcode=gltrans.account');
INSERT INTO `reportlinks` VALUES ('gltrans','systypes','gltrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','gltrans','systypes.typeid=gltrans.type');
INSERT INTO `reportlinks` VALUES ('gltrans','periods','gltrans.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','gltrans','periods.periodno=gltrans.periodno');
INSERT INTO `reportlinks` VALUES ('grns','suppliers','grns.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','grns','suppliers.supplierid=grns.supplierid');
INSERT INTO `reportlinks` VALUES ('grns','purchorderdetails','grns.podetailitem=purchorderdetails.podetailitem');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','grns','purchorderdetails.podetailitem=grns.podetailitem');
INSERT INTO `reportlinks` VALUES ('locations','taxprovinces','locations.taxprovinceid=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','locations','taxprovinces.taxprovinceid=locations.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('locstock','locations','locstock.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','locstock','locations.loccode=locstock.loccode');
INSERT INTO `reportlinks` VALUES ('locstock','stockmaster','locstock.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','locstock','stockmaster.stockid=locstock.stockid');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.shiploc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.shiploc');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.recloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.recloc');
INSERT INTO `reportlinks` VALUES ('loctransfers','stockmaster','loctransfers.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','loctransfers','stockmaster.stockid=loctransfers.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','stockmaster','orderdeliverydifferenceslog.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','orderdeliverydifferencesl','stockmaster.stockid=orderdeliverydifferenceslog.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','custbranch','orderdeliverydifferenceslog.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','orderdeliverydifferencesl','custbranch.debtorno=orderdeliverydifferenceslog.debtorno');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','salesorders','orderdeliverydifferenceslog.branchcode=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','orderdeliverydifferencesl','salesorders.orderno=orderdeliverydifferenceslog.branchcode');
INSERT INTO `reportlinks` VALUES ('prices','stockmaster','prices.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','prices','stockmaster.stockid=prices.stockid');
INSERT INTO `reportlinks` VALUES ('prices','currencies','prices.currabrev=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','prices','currencies.currabrev=prices.currabrev');
INSERT INTO `reportlinks` VALUES ('prices','salestypes','prices.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','prices','salestypes.typeabbrev=prices.typeabbrev');
INSERT INTO `reportlinks` VALUES ('purchdata','stockmaster','purchdata.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','purchdata','stockmaster.stockid=purchdata.stockid');
INSERT INTO `reportlinks` VALUES ('purchdata','suppliers','purchdata.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchdata','suppliers.supplierid=purchdata.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','purchorders','purchorderdetails.orderno=purchorders.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','purchorderdetails','purchorders.orderno=purchorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','suppliers','purchorders.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchorders','suppliers.supplierid=purchorders.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorders','locations','purchorders.intostocklocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','purchorders','locations.loccode=purchorders.intostocklocation');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','custbranch','recurringsalesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','recurringsalesorders','custbranch.branchcode=recurringsalesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','recurringsalesorders','recurrsalesorderdetails.recurrorderno=recurringsalesorders.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','recurrsalesorderdetails','recurringsalesorders.recurrorderno=recurrsalesorderdetails.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','stockmaster','recurrsalesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','recurrsalesorderdetails','stockmaster.stockid=recurrsalesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('reportcolumns','reportheaders','reportcolumns.reportid=reportheaders.reportid');
INSERT INTO `reportlinks` VALUES ('reportheaders','reportcolumns','reportheaders.reportid=reportcolumns.reportid');
INSERT INTO `reportlinks` VALUES ('salesanalysis','periods','salesanalysis.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','salesanalysis','periods.periodno=salesanalysis.periodno');
INSERT INTO `reportlinks` VALUES ('salescatprod','stockmaster','salescatprod.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salescatprod','stockmaster.stockid=salescatprod.stockid');
INSERT INTO `reportlinks` VALUES ('salescatprod','salescat','salescatprod.salescatid=salescat.salescatid');
INSERT INTO `reportlinks` VALUES ('salescat','salescatprod','salescat.salescatid=salescatprod.salescatid');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','salesorders','salesorderdetails.orderno=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','salesorderdetails','salesorders.orderno=salesorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','stockmaster','salesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salesorderdetails','stockmaster.stockid=salesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('salesorders','custbranch','salesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','salesorders','custbranch.branchcode=salesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('salesorders','shippers','salesorders.debtorno=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','salesorders','shippers.shipper_id=salesorders.debtorno');
INSERT INTO `reportlinks` VALUES ('salesorders','locations','salesorders.fromstkloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','salesorders','locations.loccode=salesorders.fromstkloc');
INSERT INTO `reportlinks` VALUES ('securitygroups','securityroles','securitygroups.secroleid=securityroles.secroleid');
INSERT INTO `reportlinks` VALUES ('securityroles','securitygroups','securityroles.secroleid=securitygroups.secroleid');
INSERT INTO `reportlinks` VALUES ('securitygroups','securitytokens','securitygroups.tokenid=securitytokens.tokenid');
INSERT INTO `reportlinks` VALUES ('securitytokens','securitygroups','securitytokens.tokenid=securitygroups.tokenid');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','shipments','shipmentcharges.shiptref=shipments.shiptref');
INSERT INTO `reportlinks` VALUES ('shipments','shipmentcharges','shipments.shiptref=shipmentcharges.shiptref');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','systypes','shipmentcharges.transtype=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','shipmentcharges','systypes.typeid=shipmentcharges.transtype');
INSERT INTO `reportlinks` VALUES ('shipments','suppliers','shipments.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','shipments','suppliers.supplierid=shipments.supplierid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','stockmaster','stockcheckfreeze.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcheckfreeze','stockmaster.stockid=stockcheckfreeze.stockid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','locations','stockcheckfreeze.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcheckfreeze','locations.loccode=stockcheckfreeze.loccode');
INSERT INTO `reportlinks` VALUES ('stockcounts','stockmaster','stockcounts.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcounts','stockmaster.stockid=stockcounts.stockid');
INSERT INTO `reportlinks` VALUES ('stockcounts','locations','stockcounts.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcounts','locations.loccode=stockcounts.loccode');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcategory','stockmaster.categoryid=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','stockmaster','stockcategory.categoryid=stockmaster.categoryid');
INSERT INTO `reportlinks` VALUES ('stockmaster','taxcategories','stockmaster.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','stockmaster','taxcategories.taxcatid=stockmaster.taxcatid');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockmaster','stockmoves.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockmoves','stockmaster.stockid=stockmoves.stockid');
INSERT INTO `reportlinks` VALUES ('stockmoves','systypes','stockmoves.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','stockmoves','systypes.typeid=stockmoves.type');
INSERT INTO `reportlinks` VALUES ('stockmoves','locations','stockmoves.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockmoves','locations.loccode=stockmoves.loccode');
INSERT INTO `reportlinks` VALUES ('stockmoves','periods','stockmoves.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','stockmoves','periods.periodno=stockmoves.prd');
INSERT INTO `reportlinks` VALUES ('stockmovestaxes','taxauthorities','stockmovestaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','stockmovestaxes','taxauthorities.taxid=stockmovestaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockmaster','stockserialitems.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockserialitems','stockmaster.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','locations','stockserialitems.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockserialitems','locations.loccode=stockserialitems.loccode');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockmoves','stockserialmoves.stockmoveno=stockmoves.stkmoveno');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockserialmoves','stockmoves.stkmoveno=stockserialmoves.stockmoveno');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockserialitems','stockserialmoves.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockserialmoves','stockserialitems.stockid=stockserialmoves.stockid');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocfrom=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocto=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocto');
INSERT INTO `reportlinks` VALUES ('suppliercontacts','suppliers','suppliercontacts.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','suppliercontacts','suppliers.supplierid=suppliercontacts.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','currencies','suppliers.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','suppliers','currencies.currabrev=suppliers.currcode');
INSERT INTO `reportlinks` VALUES ('suppliers','paymentterms','suppliers.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','suppliers','paymentterms.termsindicator=suppliers.paymentterms');
INSERT INTO `reportlinks` VALUES ('suppliers','taxgroups','suppliers.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','suppliers','taxgroups.taxgroupid=suppliers.taxgroupid');
INSERT INTO `reportlinks` VALUES ('supptrans','systypes','supptrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','supptrans','systypes.typeid=supptrans.type');
INSERT INTO `reportlinks` VALUES ('supptrans','suppliers','supptrans.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','supptrans','suppliers.supplierid=supptrans.supplierno');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','taxauthorities','supptranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','supptranstaxes','taxauthorities.taxid=supptranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','supptrans','supptranstaxes.supptransid=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','supptranstaxes','supptrans.id=supptranstaxes.supptransid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.taxglcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.taxglcode');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.purchtaxglaccount=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.purchtaxglaccount');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxauthorities','taxauthrates.taxauthority=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxauthrates','taxauthorities.taxid=taxauthrates.taxauthority');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxcategories','taxauthrates.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','taxauthrates','taxcategories.taxcatid=taxauthrates.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxprovinces','taxauthrates.dispatchtaxprovince=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','taxauthrates','taxprovinces.taxprovinceid=taxauthrates.dispatchtaxprovince');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxgroups','taxgrouptaxes.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','taxgrouptaxes','taxgroups.taxgroupid=taxgrouptaxes.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxauthorities','taxgrouptaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxgrouptaxes','taxauthorities.taxid=taxgrouptaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('workcentres','locations','workcentres.location=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','workcentres','locations.loccode=workcentres.location');
INSERT INTO `reportlinks` VALUES ('worksorders','locations','worksorders.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','worksorders','locations.loccode=worksorders.loccode');
INSERT INTO `reportlinks` VALUES ('worksorders','stockmaster','worksorders.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','worksorders','stockmaster.stockid=worksorders.stockid');
INSERT INTO `reportlinks` VALUES ('www_users','locations','www_users.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','www_users','locations.loccode=www_users.defaultlocation');
INSERT INTO `reportlinks` VALUES ('accountgroups','accountsection','accountgroups.sectioninaccounts=accountsection.sectionid');
INSERT INTO `reportlinks` VALUES ('accountsection','accountgroups','accountsection.sectionid=accountgroups.sectioninaccounts');
INSERT INTO `reportlinks` VALUES ('bankaccounts','chartmaster','bankaccounts.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','bankaccounts','chartmaster.accountcode=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('banktrans','systypes','banktrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','banktrans','systypes.typeid=banktrans.type');
INSERT INTO `reportlinks` VALUES ('banktrans','bankaccounts','banktrans.bankact=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('bankaccounts','banktrans','bankaccounts.accountcode=banktrans.bankact');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.parent=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.parent');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.component');
INSERT INTO `reportlinks` VALUES ('bom','workcentres','bom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','bom','workcentres.code=bom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('bom','locations','bom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','bom','locations.loccode=bom.loccode');
INSERT INTO `reportlinks` VALUES ('buckets','workcentres','buckets.workcentre=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','buckets','workcentres.code=buckets.workcentre');
INSERT INTO `reportlinks` VALUES ('chartdetails','chartmaster','chartdetails.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','chartdetails','chartmaster.accountcode=chartdetails.accountcode');
INSERT INTO `reportlinks` VALUES ('chartdetails','periods','chartdetails.period=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','chartdetails','periods.periodno=chartdetails.period');
INSERT INTO `reportlinks` VALUES ('chartmaster','accountgroups','chartmaster.group_=accountgroups.groupname');
INSERT INTO `reportlinks` VALUES ('accountgroups','chartmaster','accountgroups.groupname=chartmaster.group_');
INSERT INTO `reportlinks` VALUES ('contractbom','workcentres','contractbom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','contractbom','workcentres.code=contractbom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('contractbom','locations','contractbom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','contractbom','locations.loccode=contractbom.loccode');
INSERT INTO `reportlinks` VALUES ('contractbom','stockmaster','contractbom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','contractbom','stockmaster.stockid=contractbom.component');
INSERT INTO `reportlinks` VALUES ('contractreqts','contracts','contractreqts.contract=contracts.contractref');
INSERT INTO `reportlinks` VALUES ('contracts','contractreqts','contracts.contractref=contractreqts.contract');
INSERT INTO `reportlinks` VALUES ('contracts','custbranch','contracts.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','contracts','custbranch.debtorno=contracts.debtorno');
INSERT INTO `reportlinks` VALUES ('contracts','stockcategory','contracts.branchcode=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','contracts','stockcategory.categoryid=contracts.branchcode');
INSERT INTO `reportlinks` VALUES ('contracts','salestypes','contracts.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','contracts','salestypes.typeabbrev=contracts.typeabbrev');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocfrom=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocto=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocto');
INSERT INTO `reportlinks` VALUES ('custbranch','debtorsmaster','custbranch.debtorno=debtorsmaster.debtorno');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','custbranch','debtorsmaster.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','areas','custbranch.area=areas.areacode');
INSERT INTO `reportlinks` VALUES ('areas','custbranch','areas.areacode=custbranch.area');
INSERT INTO `reportlinks` VALUES ('custbranch','salesman','custbranch.salesman=salesman.salesmancode');
INSERT INTO `reportlinks` VALUES ('salesman','custbranch','salesman.salesmancode=custbranch.salesman');
INSERT INTO `reportlinks` VALUES ('custbranch','locations','custbranch.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','custbranch','locations.loccode=custbranch.defaultlocation');
INSERT INTO `reportlinks` VALUES ('custbranch','shippers','custbranch.defaultshipvia=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','custbranch','shippers.shipper_id=custbranch.defaultshipvia');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','holdreasons','debtorsmaster.holdreason=holdreasons.reasoncode');
INSERT INTO `reportlinks` VALUES ('holdreasons','debtorsmaster','holdreasons.reasoncode=debtorsmaster.holdreason');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','currencies','debtorsmaster.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','debtorsmaster','currencies.currabrev=debtorsmaster.currcode');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','paymentterms','debtorsmaster.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','debtorsmaster','paymentterms.termsindicator=debtorsmaster.paymentterms');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','salestypes','debtorsmaster.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','debtorsmaster','salestypes.typeabbrev=debtorsmaster.salestype');
INSERT INTO `reportlinks` VALUES ('debtortrans','custbranch','debtortrans.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','debtortrans','custbranch.debtorno=debtortrans.debtorno');
INSERT INTO `reportlinks` VALUES ('debtortrans','systypes','debtortrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','debtortrans','systypes.typeid=debtortrans.type');
INSERT INTO `reportlinks` VALUES ('debtortrans','periods','debtortrans.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','debtortrans','periods.periodno=debtortrans.prd');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','taxauthorities','debtortranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','debtortranstaxes','taxauthorities.taxid=debtortranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','debtortrans','debtortranstaxes.debtortransid=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','debtortranstaxes','debtortrans.id=debtortranstaxes.debtortransid');
INSERT INTO `reportlinks` VALUES ('discountmatrix','salestypes','discountmatrix.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','discountmatrix','salestypes.typeabbrev=discountmatrix.salestype');
INSERT INTO `reportlinks` VALUES ('freightcosts','locations','freightcosts.locationfrom=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','freightcosts','locations.loccode=freightcosts.locationfrom');
INSERT INTO `reportlinks` VALUES ('freightcosts','shippers','freightcosts.shipperid=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','freightcosts','shippers.shipper_id=freightcosts.shipperid');
INSERT INTO `reportlinks` VALUES ('gltrans','chartmaster','gltrans.account=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','gltrans','chartmaster.accountcode=gltrans.account');
INSERT INTO `reportlinks` VALUES ('gltrans','systypes','gltrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','gltrans','systypes.typeid=gltrans.type');
INSERT INTO `reportlinks` VALUES ('gltrans','periods','gltrans.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','gltrans','periods.periodno=gltrans.periodno');
INSERT INTO `reportlinks` VALUES ('grns','suppliers','grns.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','grns','suppliers.supplierid=grns.supplierid');
INSERT INTO `reportlinks` VALUES ('grns','purchorderdetails','grns.podetailitem=purchorderdetails.podetailitem');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','grns','purchorderdetails.podetailitem=grns.podetailitem');
INSERT INTO `reportlinks` VALUES ('locations','taxprovinces','locations.taxprovinceid=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','locations','taxprovinces.taxprovinceid=locations.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('locstock','locations','locstock.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','locstock','locations.loccode=locstock.loccode');
INSERT INTO `reportlinks` VALUES ('locstock','stockmaster','locstock.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','locstock','stockmaster.stockid=locstock.stockid');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.shiploc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.shiploc');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.recloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.recloc');
INSERT INTO `reportlinks` VALUES ('loctransfers','stockmaster','loctransfers.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','loctransfers','stockmaster.stockid=loctransfers.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','stockmaster','orderdeliverydifferenceslog.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','orderdeliverydifferencesl','stockmaster.stockid=orderdeliverydifferenceslog.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','custbranch','orderdeliverydifferenceslog.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','orderdeliverydifferencesl','custbranch.debtorno=orderdeliverydifferenceslog.debtorno');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','salesorders','orderdeliverydifferenceslog.branchcode=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','orderdeliverydifferencesl','salesorders.orderno=orderdeliverydifferenceslog.branchcode');
INSERT INTO `reportlinks` VALUES ('prices','stockmaster','prices.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','prices','stockmaster.stockid=prices.stockid');
INSERT INTO `reportlinks` VALUES ('prices','currencies','prices.currabrev=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','prices','currencies.currabrev=prices.currabrev');
INSERT INTO `reportlinks` VALUES ('prices','salestypes','prices.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','prices','salestypes.typeabbrev=prices.typeabbrev');
INSERT INTO `reportlinks` VALUES ('purchdata','stockmaster','purchdata.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','purchdata','stockmaster.stockid=purchdata.stockid');
INSERT INTO `reportlinks` VALUES ('purchdata','suppliers','purchdata.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchdata','suppliers.supplierid=purchdata.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','purchorders','purchorderdetails.orderno=purchorders.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','purchorderdetails','purchorders.orderno=purchorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','suppliers','purchorders.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchorders','suppliers.supplierid=purchorders.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorders','locations','purchorders.intostocklocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','purchorders','locations.loccode=purchorders.intostocklocation');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','custbranch','recurringsalesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','recurringsalesorders','custbranch.branchcode=recurringsalesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','recurringsalesorders','recurrsalesorderdetails.recurrorderno=recurringsalesorders.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','recurrsalesorderdetails','recurringsalesorders.recurrorderno=recurrsalesorderdetails.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','stockmaster','recurrsalesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','recurrsalesorderdetails','stockmaster.stockid=recurrsalesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('reportcolumns','reportheaders','reportcolumns.reportid=reportheaders.reportid');
INSERT INTO `reportlinks` VALUES ('reportheaders','reportcolumns','reportheaders.reportid=reportcolumns.reportid');
INSERT INTO `reportlinks` VALUES ('salesanalysis','periods','salesanalysis.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','salesanalysis','periods.periodno=salesanalysis.periodno');
INSERT INTO `reportlinks` VALUES ('salescatprod','stockmaster','salescatprod.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salescatprod','stockmaster.stockid=salescatprod.stockid');
INSERT INTO `reportlinks` VALUES ('salescatprod','salescat','salescatprod.salescatid=salescat.salescatid');
INSERT INTO `reportlinks` VALUES ('salescat','salescatprod','salescat.salescatid=salescatprod.salescatid');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','salesorders','salesorderdetails.orderno=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','salesorderdetails','salesorders.orderno=salesorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','stockmaster','salesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salesorderdetails','stockmaster.stockid=salesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('salesorders','custbranch','salesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','salesorders','custbranch.branchcode=salesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('salesorders','shippers','salesorders.debtorno=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','salesorders','shippers.shipper_id=salesorders.debtorno');
INSERT INTO `reportlinks` VALUES ('salesorders','locations','salesorders.fromstkloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','salesorders','locations.loccode=salesorders.fromstkloc');
INSERT INTO `reportlinks` VALUES ('securitygroups','securityroles','securitygroups.secroleid=securityroles.secroleid');
INSERT INTO `reportlinks` VALUES ('securityroles','securitygroups','securityroles.secroleid=securitygroups.secroleid');
INSERT INTO `reportlinks` VALUES ('securitygroups','securitytokens','securitygroups.tokenid=securitytokens.tokenid');
INSERT INTO `reportlinks` VALUES ('securitytokens','securitygroups','securitytokens.tokenid=securitygroups.tokenid');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','shipments','shipmentcharges.shiptref=shipments.shiptref');
INSERT INTO `reportlinks` VALUES ('shipments','shipmentcharges','shipments.shiptref=shipmentcharges.shiptref');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','systypes','shipmentcharges.transtype=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','shipmentcharges','systypes.typeid=shipmentcharges.transtype');
INSERT INTO `reportlinks` VALUES ('shipments','suppliers','shipments.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','shipments','suppliers.supplierid=shipments.supplierid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','stockmaster','stockcheckfreeze.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcheckfreeze','stockmaster.stockid=stockcheckfreeze.stockid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','locations','stockcheckfreeze.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcheckfreeze','locations.loccode=stockcheckfreeze.loccode');
INSERT INTO `reportlinks` VALUES ('stockcounts','stockmaster','stockcounts.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcounts','stockmaster.stockid=stockcounts.stockid');
INSERT INTO `reportlinks` VALUES ('stockcounts','locations','stockcounts.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcounts','locations.loccode=stockcounts.loccode');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcategory','stockmaster.categoryid=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','stockmaster','stockcategory.categoryid=stockmaster.categoryid');
INSERT INTO `reportlinks` VALUES ('stockmaster','taxcategories','stockmaster.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','stockmaster','taxcategories.taxcatid=stockmaster.taxcatid');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockmaster','stockmoves.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockmoves','stockmaster.stockid=stockmoves.stockid');
INSERT INTO `reportlinks` VALUES ('stockmoves','systypes','stockmoves.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','stockmoves','systypes.typeid=stockmoves.type');
INSERT INTO `reportlinks` VALUES ('stockmoves','locations','stockmoves.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockmoves','locations.loccode=stockmoves.loccode');
INSERT INTO `reportlinks` VALUES ('stockmoves','periods','stockmoves.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','stockmoves','periods.periodno=stockmoves.prd');
INSERT INTO `reportlinks` VALUES ('stockmovestaxes','taxauthorities','stockmovestaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','stockmovestaxes','taxauthorities.taxid=stockmovestaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockmaster','stockserialitems.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockserialitems','stockmaster.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','locations','stockserialitems.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockserialitems','locations.loccode=stockserialitems.loccode');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockmoves','stockserialmoves.stockmoveno=stockmoves.stkmoveno');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockserialmoves','stockmoves.stkmoveno=stockserialmoves.stockmoveno');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockserialitems','stockserialmoves.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockserialmoves','stockserialitems.stockid=stockserialmoves.stockid');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocfrom=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocto=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocto');
INSERT INTO `reportlinks` VALUES ('suppliercontacts','suppliers','suppliercontacts.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','suppliercontacts','suppliers.supplierid=suppliercontacts.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','currencies','suppliers.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','suppliers','currencies.currabrev=suppliers.currcode');
INSERT INTO `reportlinks` VALUES ('suppliers','paymentterms','suppliers.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','suppliers','paymentterms.termsindicator=suppliers.paymentterms');
INSERT INTO `reportlinks` VALUES ('suppliers','taxgroups','suppliers.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','suppliers','taxgroups.taxgroupid=suppliers.taxgroupid');
INSERT INTO `reportlinks` VALUES ('supptrans','systypes','supptrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','supptrans','systypes.typeid=supptrans.type');
INSERT INTO `reportlinks` VALUES ('supptrans','suppliers','supptrans.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','supptrans','suppliers.supplierid=supptrans.supplierno');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','taxauthorities','supptranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','supptranstaxes','taxauthorities.taxid=supptranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','supptrans','supptranstaxes.supptransid=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','supptranstaxes','supptrans.id=supptranstaxes.supptransid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.taxglcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.taxglcode');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.purchtaxglaccount=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.purchtaxglaccount');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxauthorities','taxauthrates.taxauthority=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxauthrates','taxauthorities.taxid=taxauthrates.taxauthority');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxcategories','taxauthrates.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','taxauthrates','taxcategories.taxcatid=taxauthrates.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxprovinces','taxauthrates.dispatchtaxprovince=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','taxauthrates','taxprovinces.taxprovinceid=taxauthrates.dispatchtaxprovince');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxgroups','taxgrouptaxes.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','taxgrouptaxes','taxgroups.taxgroupid=taxgrouptaxes.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxauthorities','taxgrouptaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxgrouptaxes','taxauthorities.taxid=taxgrouptaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('workcentres','locations','workcentres.location=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','workcentres','locations.loccode=workcentres.location');
INSERT INTO `reportlinks` VALUES ('worksorders','locations','worksorders.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','worksorders','locations.loccode=worksorders.loccode');
INSERT INTO `reportlinks` VALUES ('worksorders','stockmaster','worksorders.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','worksorders','stockmaster.stockid=worksorders.stockid');
INSERT INTO `reportlinks` VALUES ('www_users','locations','www_users.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','www_users','locations.loccode=www_users.defaultlocation');
INSERT INTO `reportlinks` VALUES ('accountgroups','accountsection','accountgroups.sectioninaccounts=accountsection.sectionid');
INSERT INTO `reportlinks` VALUES ('accountsection','accountgroups','accountsection.sectionid=accountgroups.sectioninaccounts');
INSERT INTO `reportlinks` VALUES ('bankaccounts','chartmaster','bankaccounts.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','bankaccounts','chartmaster.accountcode=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('banktrans','systypes','banktrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','banktrans','systypes.typeid=banktrans.type');
INSERT INTO `reportlinks` VALUES ('banktrans','bankaccounts','banktrans.bankact=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('bankaccounts','banktrans','bankaccounts.accountcode=banktrans.bankact');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.parent=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.parent');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.component');
INSERT INTO `reportlinks` VALUES ('bom','workcentres','bom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','bom','workcentres.code=bom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('bom','locations','bom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','bom','locations.loccode=bom.loccode');
INSERT INTO `reportlinks` VALUES ('buckets','workcentres','buckets.workcentre=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','buckets','workcentres.code=buckets.workcentre');
INSERT INTO `reportlinks` VALUES ('chartdetails','chartmaster','chartdetails.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','chartdetails','chartmaster.accountcode=chartdetails.accountcode');
INSERT INTO `reportlinks` VALUES ('chartdetails','periods','chartdetails.period=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','chartdetails','periods.periodno=chartdetails.period');
INSERT INTO `reportlinks` VALUES ('chartmaster','accountgroups','chartmaster.group_=accountgroups.groupname');
INSERT INTO `reportlinks` VALUES ('accountgroups','chartmaster','accountgroups.groupname=chartmaster.group_');
INSERT INTO `reportlinks` VALUES ('contractbom','workcentres','contractbom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','contractbom','workcentres.code=contractbom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('contractbom','locations','contractbom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','contractbom','locations.loccode=contractbom.loccode');
INSERT INTO `reportlinks` VALUES ('contractbom','stockmaster','contractbom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','contractbom','stockmaster.stockid=contractbom.component');
INSERT INTO `reportlinks` VALUES ('contractreqts','contracts','contractreqts.contract=contracts.contractref');
INSERT INTO `reportlinks` VALUES ('contracts','contractreqts','contracts.contractref=contractreqts.contract');
INSERT INTO `reportlinks` VALUES ('contracts','custbranch','contracts.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','contracts','custbranch.debtorno=contracts.debtorno');
INSERT INTO `reportlinks` VALUES ('contracts','stockcategory','contracts.branchcode=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','contracts','stockcategory.categoryid=contracts.branchcode');
INSERT INTO `reportlinks` VALUES ('contracts','salestypes','contracts.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','contracts','salestypes.typeabbrev=contracts.typeabbrev');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocfrom=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocto=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocto');
INSERT INTO `reportlinks` VALUES ('custbranch','debtorsmaster','custbranch.debtorno=debtorsmaster.debtorno');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','custbranch','debtorsmaster.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','areas','custbranch.area=areas.areacode');
INSERT INTO `reportlinks` VALUES ('areas','custbranch','areas.areacode=custbranch.area');
INSERT INTO `reportlinks` VALUES ('custbranch','salesman','custbranch.salesman=salesman.salesmancode');
INSERT INTO `reportlinks` VALUES ('salesman','custbranch','salesman.salesmancode=custbranch.salesman');
INSERT INTO `reportlinks` VALUES ('custbranch','locations','custbranch.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','custbranch','locations.loccode=custbranch.defaultlocation');
INSERT INTO `reportlinks` VALUES ('custbranch','shippers','custbranch.defaultshipvia=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','custbranch','shippers.shipper_id=custbranch.defaultshipvia');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','holdreasons','debtorsmaster.holdreason=holdreasons.reasoncode');
INSERT INTO `reportlinks` VALUES ('holdreasons','debtorsmaster','holdreasons.reasoncode=debtorsmaster.holdreason');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','currencies','debtorsmaster.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','debtorsmaster','currencies.currabrev=debtorsmaster.currcode');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','paymentterms','debtorsmaster.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','debtorsmaster','paymentterms.termsindicator=debtorsmaster.paymentterms');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','salestypes','debtorsmaster.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','debtorsmaster','salestypes.typeabbrev=debtorsmaster.salestype');
INSERT INTO `reportlinks` VALUES ('debtortrans','custbranch','debtortrans.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','debtortrans','custbranch.debtorno=debtortrans.debtorno');
INSERT INTO `reportlinks` VALUES ('debtortrans','systypes','debtortrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','debtortrans','systypes.typeid=debtortrans.type');
INSERT INTO `reportlinks` VALUES ('debtortrans','periods','debtortrans.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','debtortrans','periods.periodno=debtortrans.prd');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','taxauthorities','debtortranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','debtortranstaxes','taxauthorities.taxid=debtortranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','debtortrans','debtortranstaxes.debtortransid=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','debtortranstaxes','debtortrans.id=debtortranstaxes.debtortransid');
INSERT INTO `reportlinks` VALUES ('discountmatrix','salestypes','discountmatrix.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','discountmatrix','salestypes.typeabbrev=discountmatrix.salestype');
INSERT INTO `reportlinks` VALUES ('freightcosts','locations','freightcosts.locationfrom=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','freightcosts','locations.loccode=freightcosts.locationfrom');
INSERT INTO `reportlinks` VALUES ('freightcosts','shippers','freightcosts.shipperid=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','freightcosts','shippers.shipper_id=freightcosts.shipperid');
INSERT INTO `reportlinks` VALUES ('gltrans','chartmaster','gltrans.account=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','gltrans','chartmaster.accountcode=gltrans.account');
INSERT INTO `reportlinks` VALUES ('gltrans','systypes','gltrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','gltrans','systypes.typeid=gltrans.type');
INSERT INTO `reportlinks` VALUES ('gltrans','periods','gltrans.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','gltrans','periods.periodno=gltrans.periodno');
INSERT INTO `reportlinks` VALUES ('grns','suppliers','grns.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','grns','suppliers.supplierid=grns.supplierid');
INSERT INTO `reportlinks` VALUES ('grns','purchorderdetails','grns.podetailitem=purchorderdetails.podetailitem');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','grns','purchorderdetails.podetailitem=grns.podetailitem');
INSERT INTO `reportlinks` VALUES ('locations','taxprovinces','locations.taxprovinceid=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','locations','taxprovinces.taxprovinceid=locations.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('locstock','locations','locstock.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','locstock','locations.loccode=locstock.loccode');
INSERT INTO `reportlinks` VALUES ('locstock','stockmaster','locstock.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','locstock','stockmaster.stockid=locstock.stockid');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.shiploc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.shiploc');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.recloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.recloc');
INSERT INTO `reportlinks` VALUES ('loctransfers','stockmaster','loctransfers.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','loctransfers','stockmaster.stockid=loctransfers.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','stockmaster','orderdeliverydifferenceslog.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','orderdeliverydifferencesl','stockmaster.stockid=orderdeliverydifferenceslog.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','custbranch','orderdeliverydifferenceslog.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','orderdeliverydifferencesl','custbranch.debtorno=orderdeliverydifferenceslog.debtorno');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','salesorders','orderdeliverydifferenceslog.branchcode=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','orderdeliverydifferencesl','salesorders.orderno=orderdeliverydifferenceslog.branchcode');
INSERT INTO `reportlinks` VALUES ('prices','stockmaster','prices.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','prices','stockmaster.stockid=prices.stockid');
INSERT INTO `reportlinks` VALUES ('prices','currencies','prices.currabrev=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','prices','currencies.currabrev=prices.currabrev');
INSERT INTO `reportlinks` VALUES ('prices','salestypes','prices.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','prices','salestypes.typeabbrev=prices.typeabbrev');
INSERT INTO `reportlinks` VALUES ('purchdata','stockmaster','purchdata.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','purchdata','stockmaster.stockid=purchdata.stockid');
INSERT INTO `reportlinks` VALUES ('purchdata','suppliers','purchdata.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchdata','suppliers.supplierid=purchdata.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','purchorders','purchorderdetails.orderno=purchorders.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','purchorderdetails','purchorders.orderno=purchorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','suppliers','purchorders.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchorders','suppliers.supplierid=purchorders.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorders','locations','purchorders.intostocklocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','purchorders','locations.loccode=purchorders.intostocklocation');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','custbranch','recurringsalesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','recurringsalesorders','custbranch.branchcode=recurringsalesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','recurringsalesorders','recurrsalesorderdetails.recurrorderno=recurringsalesorders.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','recurrsalesorderdetails','recurringsalesorders.recurrorderno=recurrsalesorderdetails.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','stockmaster','recurrsalesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','recurrsalesorderdetails','stockmaster.stockid=recurrsalesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('reportcolumns','reportheaders','reportcolumns.reportid=reportheaders.reportid');
INSERT INTO `reportlinks` VALUES ('reportheaders','reportcolumns','reportheaders.reportid=reportcolumns.reportid');
INSERT INTO `reportlinks` VALUES ('salesanalysis','periods','salesanalysis.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','salesanalysis','periods.periodno=salesanalysis.periodno');
INSERT INTO `reportlinks` VALUES ('salescatprod','stockmaster','salescatprod.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salescatprod','stockmaster.stockid=salescatprod.stockid');
INSERT INTO `reportlinks` VALUES ('salescatprod','salescat','salescatprod.salescatid=salescat.salescatid');
INSERT INTO `reportlinks` VALUES ('salescat','salescatprod','salescat.salescatid=salescatprod.salescatid');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','salesorders','salesorderdetails.orderno=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','salesorderdetails','salesorders.orderno=salesorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','stockmaster','salesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salesorderdetails','stockmaster.stockid=salesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('salesorders','custbranch','salesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','salesorders','custbranch.branchcode=salesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('salesorders','shippers','salesorders.debtorno=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','salesorders','shippers.shipper_id=salesorders.debtorno');
INSERT INTO `reportlinks` VALUES ('salesorders','locations','salesorders.fromstkloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','salesorders','locations.loccode=salesorders.fromstkloc');
INSERT INTO `reportlinks` VALUES ('securitygroups','securityroles','securitygroups.secroleid=securityroles.secroleid');
INSERT INTO `reportlinks` VALUES ('securityroles','securitygroups','securityroles.secroleid=securitygroups.secroleid');
INSERT INTO `reportlinks` VALUES ('securitygroups','securitytokens','securitygroups.tokenid=securitytokens.tokenid');
INSERT INTO `reportlinks` VALUES ('securitytokens','securitygroups','securitytokens.tokenid=securitygroups.tokenid');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','shipments','shipmentcharges.shiptref=shipments.shiptref');
INSERT INTO `reportlinks` VALUES ('shipments','shipmentcharges','shipments.shiptref=shipmentcharges.shiptref');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','systypes','shipmentcharges.transtype=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','shipmentcharges','systypes.typeid=shipmentcharges.transtype');
INSERT INTO `reportlinks` VALUES ('shipments','suppliers','shipments.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','shipments','suppliers.supplierid=shipments.supplierid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','stockmaster','stockcheckfreeze.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcheckfreeze','stockmaster.stockid=stockcheckfreeze.stockid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','locations','stockcheckfreeze.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcheckfreeze','locations.loccode=stockcheckfreeze.loccode');
INSERT INTO `reportlinks` VALUES ('stockcounts','stockmaster','stockcounts.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcounts','stockmaster.stockid=stockcounts.stockid');
INSERT INTO `reportlinks` VALUES ('stockcounts','locations','stockcounts.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcounts','locations.loccode=stockcounts.loccode');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcategory','stockmaster.categoryid=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','stockmaster','stockcategory.categoryid=stockmaster.categoryid');
INSERT INTO `reportlinks` VALUES ('stockmaster','taxcategories','stockmaster.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','stockmaster','taxcategories.taxcatid=stockmaster.taxcatid');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockmaster','stockmoves.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockmoves','stockmaster.stockid=stockmoves.stockid');
INSERT INTO `reportlinks` VALUES ('stockmoves','systypes','stockmoves.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','stockmoves','systypes.typeid=stockmoves.type');
INSERT INTO `reportlinks` VALUES ('stockmoves','locations','stockmoves.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockmoves','locations.loccode=stockmoves.loccode');
INSERT INTO `reportlinks` VALUES ('stockmoves','periods','stockmoves.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','stockmoves','periods.periodno=stockmoves.prd');
INSERT INTO `reportlinks` VALUES ('stockmovestaxes','taxauthorities','stockmovestaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','stockmovestaxes','taxauthorities.taxid=stockmovestaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockmaster','stockserialitems.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockserialitems','stockmaster.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','locations','stockserialitems.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockserialitems','locations.loccode=stockserialitems.loccode');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockmoves','stockserialmoves.stockmoveno=stockmoves.stkmoveno');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockserialmoves','stockmoves.stkmoveno=stockserialmoves.stockmoveno');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockserialitems','stockserialmoves.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockserialmoves','stockserialitems.stockid=stockserialmoves.stockid');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocfrom=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocto=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocto');
INSERT INTO `reportlinks` VALUES ('suppliercontacts','suppliers','suppliercontacts.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','suppliercontacts','suppliers.supplierid=suppliercontacts.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','currencies','suppliers.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','suppliers','currencies.currabrev=suppliers.currcode');
INSERT INTO `reportlinks` VALUES ('suppliers','paymentterms','suppliers.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','suppliers','paymentterms.termsindicator=suppliers.paymentterms');
INSERT INTO `reportlinks` VALUES ('suppliers','taxgroups','suppliers.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','suppliers','taxgroups.taxgroupid=suppliers.taxgroupid');
INSERT INTO `reportlinks` VALUES ('supptrans','systypes','supptrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','supptrans','systypes.typeid=supptrans.type');
INSERT INTO `reportlinks` VALUES ('supptrans','suppliers','supptrans.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','supptrans','suppliers.supplierid=supptrans.supplierno');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','taxauthorities','supptranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','supptranstaxes','taxauthorities.taxid=supptranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','supptrans','supptranstaxes.supptransid=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','supptranstaxes','supptrans.id=supptranstaxes.supptransid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.taxglcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.taxglcode');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.purchtaxglaccount=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.purchtaxglaccount');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxauthorities','taxauthrates.taxauthority=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxauthrates','taxauthorities.taxid=taxauthrates.taxauthority');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxcategories','taxauthrates.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','taxauthrates','taxcategories.taxcatid=taxauthrates.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxprovinces','taxauthrates.dispatchtaxprovince=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','taxauthrates','taxprovinces.taxprovinceid=taxauthrates.dispatchtaxprovince');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxgroups','taxgrouptaxes.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','taxgrouptaxes','taxgroups.taxgroupid=taxgrouptaxes.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxauthorities','taxgrouptaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxgrouptaxes','taxauthorities.taxid=taxgrouptaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('workcentres','locations','workcentres.location=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','workcentres','locations.loccode=workcentres.location');
INSERT INTO `reportlinks` VALUES ('worksorders','locations','worksorders.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','worksorders','locations.loccode=worksorders.loccode');
INSERT INTO `reportlinks` VALUES ('worksorders','stockmaster','worksorders.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','worksorders','stockmaster.stockid=worksorders.stockid');
INSERT INTO `reportlinks` VALUES ('www_users','locations','www_users.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','www_users','locations.loccode=www_users.defaultlocation');
INSERT INTO `reportlinks` VALUES ('accountgroups','accountsection','accountgroups.sectioninaccounts=accountsection.sectionid');
INSERT INTO `reportlinks` VALUES ('accountsection','accountgroups','accountsection.sectionid=accountgroups.sectioninaccounts');
INSERT INTO `reportlinks` VALUES ('bankaccounts','chartmaster','bankaccounts.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','bankaccounts','chartmaster.accountcode=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('banktrans','systypes','banktrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','banktrans','systypes.typeid=banktrans.type');
INSERT INTO `reportlinks` VALUES ('banktrans','bankaccounts','banktrans.bankact=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('bankaccounts','banktrans','bankaccounts.accountcode=banktrans.bankact');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.parent=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.parent');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.component');
INSERT INTO `reportlinks` VALUES ('bom','workcentres','bom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','bom','workcentres.code=bom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('bom','locations','bom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','bom','locations.loccode=bom.loccode');
INSERT INTO `reportlinks` VALUES ('buckets','workcentres','buckets.workcentre=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','buckets','workcentres.code=buckets.workcentre');
INSERT INTO `reportlinks` VALUES ('chartdetails','chartmaster','chartdetails.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','chartdetails','chartmaster.accountcode=chartdetails.accountcode');
INSERT INTO `reportlinks` VALUES ('chartdetails','periods','chartdetails.period=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','chartdetails','periods.periodno=chartdetails.period');
INSERT INTO `reportlinks` VALUES ('chartmaster','accountgroups','chartmaster.group_=accountgroups.groupname');
INSERT INTO `reportlinks` VALUES ('accountgroups','chartmaster','accountgroups.groupname=chartmaster.group_');
INSERT INTO `reportlinks` VALUES ('contractbom','workcentres','contractbom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','contractbom','workcentres.code=contractbom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('contractbom','locations','contractbom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','contractbom','locations.loccode=contractbom.loccode');
INSERT INTO `reportlinks` VALUES ('contractbom','stockmaster','contractbom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','contractbom','stockmaster.stockid=contractbom.component');
INSERT INTO `reportlinks` VALUES ('contractreqts','contracts','contractreqts.contract=contracts.contractref');
INSERT INTO `reportlinks` VALUES ('contracts','contractreqts','contracts.contractref=contractreqts.contract');
INSERT INTO `reportlinks` VALUES ('contracts','custbranch','contracts.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','contracts','custbranch.debtorno=contracts.debtorno');
INSERT INTO `reportlinks` VALUES ('contracts','stockcategory','contracts.branchcode=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','contracts','stockcategory.categoryid=contracts.branchcode');
INSERT INTO `reportlinks` VALUES ('contracts','salestypes','contracts.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','contracts','salestypes.typeabbrev=contracts.typeabbrev');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocfrom=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocto=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocto');
INSERT INTO `reportlinks` VALUES ('custbranch','debtorsmaster','custbranch.debtorno=debtorsmaster.debtorno');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','custbranch','debtorsmaster.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','areas','custbranch.area=areas.areacode');
INSERT INTO `reportlinks` VALUES ('areas','custbranch','areas.areacode=custbranch.area');
INSERT INTO `reportlinks` VALUES ('custbranch','salesman','custbranch.salesman=salesman.salesmancode');
INSERT INTO `reportlinks` VALUES ('salesman','custbranch','salesman.salesmancode=custbranch.salesman');
INSERT INTO `reportlinks` VALUES ('custbranch','locations','custbranch.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','custbranch','locations.loccode=custbranch.defaultlocation');
INSERT INTO `reportlinks` VALUES ('custbranch','shippers','custbranch.defaultshipvia=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','custbranch','shippers.shipper_id=custbranch.defaultshipvia');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','holdreasons','debtorsmaster.holdreason=holdreasons.reasoncode');
INSERT INTO `reportlinks` VALUES ('holdreasons','debtorsmaster','holdreasons.reasoncode=debtorsmaster.holdreason');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','currencies','debtorsmaster.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','debtorsmaster','currencies.currabrev=debtorsmaster.currcode');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','paymentterms','debtorsmaster.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','debtorsmaster','paymentterms.termsindicator=debtorsmaster.paymentterms');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','salestypes','debtorsmaster.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','debtorsmaster','salestypes.typeabbrev=debtorsmaster.salestype');
INSERT INTO `reportlinks` VALUES ('debtortrans','custbranch','debtortrans.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','debtortrans','custbranch.debtorno=debtortrans.debtorno');
INSERT INTO `reportlinks` VALUES ('debtortrans','systypes','debtortrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','debtortrans','systypes.typeid=debtortrans.type');
INSERT INTO `reportlinks` VALUES ('debtortrans','periods','debtortrans.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','debtortrans','periods.periodno=debtortrans.prd');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','taxauthorities','debtortranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','debtortranstaxes','taxauthorities.taxid=debtortranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','debtortrans','debtortranstaxes.debtortransid=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','debtortranstaxes','debtortrans.id=debtortranstaxes.debtortransid');
INSERT INTO `reportlinks` VALUES ('discountmatrix','salestypes','discountmatrix.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','discountmatrix','salestypes.typeabbrev=discountmatrix.salestype');
INSERT INTO `reportlinks` VALUES ('freightcosts','locations','freightcosts.locationfrom=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','freightcosts','locations.loccode=freightcosts.locationfrom');
INSERT INTO `reportlinks` VALUES ('freightcosts','shippers','freightcosts.shipperid=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','freightcosts','shippers.shipper_id=freightcosts.shipperid');
INSERT INTO `reportlinks` VALUES ('gltrans','chartmaster','gltrans.account=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','gltrans','chartmaster.accountcode=gltrans.account');
INSERT INTO `reportlinks` VALUES ('gltrans','systypes','gltrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','gltrans','systypes.typeid=gltrans.type');
INSERT INTO `reportlinks` VALUES ('gltrans','periods','gltrans.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','gltrans','periods.periodno=gltrans.periodno');
INSERT INTO `reportlinks` VALUES ('grns','suppliers','grns.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','grns','suppliers.supplierid=grns.supplierid');
INSERT INTO `reportlinks` VALUES ('grns','purchorderdetails','grns.podetailitem=purchorderdetails.podetailitem');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','grns','purchorderdetails.podetailitem=grns.podetailitem');
INSERT INTO `reportlinks` VALUES ('locations','taxprovinces','locations.taxprovinceid=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','locations','taxprovinces.taxprovinceid=locations.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('locstock','locations','locstock.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','locstock','locations.loccode=locstock.loccode');
INSERT INTO `reportlinks` VALUES ('locstock','stockmaster','locstock.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','locstock','stockmaster.stockid=locstock.stockid');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.shiploc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.shiploc');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.recloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.recloc');
INSERT INTO `reportlinks` VALUES ('loctransfers','stockmaster','loctransfers.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','loctransfers','stockmaster.stockid=loctransfers.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','stockmaster','orderdeliverydifferenceslog.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','orderdeliverydifferencesl','stockmaster.stockid=orderdeliverydifferenceslog.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','custbranch','orderdeliverydifferenceslog.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','orderdeliverydifferencesl','custbranch.debtorno=orderdeliverydifferenceslog.debtorno');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','salesorders','orderdeliverydifferenceslog.branchcode=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','orderdeliverydifferencesl','salesorders.orderno=orderdeliverydifferenceslog.branchcode');
INSERT INTO `reportlinks` VALUES ('prices','stockmaster','prices.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','prices','stockmaster.stockid=prices.stockid');
INSERT INTO `reportlinks` VALUES ('prices','currencies','prices.currabrev=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','prices','currencies.currabrev=prices.currabrev');
INSERT INTO `reportlinks` VALUES ('prices','salestypes','prices.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','prices','salestypes.typeabbrev=prices.typeabbrev');
INSERT INTO `reportlinks` VALUES ('purchdata','stockmaster','purchdata.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','purchdata','stockmaster.stockid=purchdata.stockid');
INSERT INTO `reportlinks` VALUES ('purchdata','suppliers','purchdata.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchdata','suppliers.supplierid=purchdata.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','purchorders','purchorderdetails.orderno=purchorders.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','purchorderdetails','purchorders.orderno=purchorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','suppliers','purchorders.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchorders','suppliers.supplierid=purchorders.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorders','locations','purchorders.intostocklocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','purchorders','locations.loccode=purchorders.intostocklocation');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','custbranch','recurringsalesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','recurringsalesorders','custbranch.branchcode=recurringsalesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','recurringsalesorders','recurrsalesorderdetails.recurrorderno=recurringsalesorders.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','recurrsalesorderdetails','recurringsalesorders.recurrorderno=recurrsalesorderdetails.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','stockmaster','recurrsalesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','recurrsalesorderdetails','stockmaster.stockid=recurrsalesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('reportcolumns','reportheaders','reportcolumns.reportid=reportheaders.reportid');
INSERT INTO `reportlinks` VALUES ('reportheaders','reportcolumns','reportheaders.reportid=reportcolumns.reportid');
INSERT INTO `reportlinks` VALUES ('salesanalysis','periods','salesanalysis.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','salesanalysis','periods.periodno=salesanalysis.periodno');
INSERT INTO `reportlinks` VALUES ('salescatprod','stockmaster','salescatprod.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salescatprod','stockmaster.stockid=salescatprod.stockid');
INSERT INTO `reportlinks` VALUES ('salescatprod','salescat','salescatprod.salescatid=salescat.salescatid');
INSERT INTO `reportlinks` VALUES ('salescat','salescatprod','salescat.salescatid=salescatprod.salescatid');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','salesorders','salesorderdetails.orderno=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','salesorderdetails','salesorders.orderno=salesorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','stockmaster','salesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salesorderdetails','stockmaster.stockid=salesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('salesorders','custbranch','salesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','salesorders','custbranch.branchcode=salesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('salesorders','shippers','salesorders.debtorno=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','salesorders','shippers.shipper_id=salesorders.debtorno');
INSERT INTO `reportlinks` VALUES ('salesorders','locations','salesorders.fromstkloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','salesorders','locations.loccode=salesorders.fromstkloc');
INSERT INTO `reportlinks` VALUES ('securitygroups','securityroles','securitygroups.secroleid=securityroles.secroleid');
INSERT INTO `reportlinks` VALUES ('securityroles','securitygroups','securityroles.secroleid=securitygroups.secroleid');
INSERT INTO `reportlinks` VALUES ('securitygroups','securitytokens','securitygroups.tokenid=securitytokens.tokenid');
INSERT INTO `reportlinks` VALUES ('securitytokens','securitygroups','securitytokens.tokenid=securitygroups.tokenid');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','shipments','shipmentcharges.shiptref=shipments.shiptref');
INSERT INTO `reportlinks` VALUES ('shipments','shipmentcharges','shipments.shiptref=shipmentcharges.shiptref');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','systypes','shipmentcharges.transtype=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','shipmentcharges','systypes.typeid=shipmentcharges.transtype');
INSERT INTO `reportlinks` VALUES ('shipments','suppliers','shipments.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','shipments','suppliers.supplierid=shipments.supplierid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','stockmaster','stockcheckfreeze.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcheckfreeze','stockmaster.stockid=stockcheckfreeze.stockid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','locations','stockcheckfreeze.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcheckfreeze','locations.loccode=stockcheckfreeze.loccode');
INSERT INTO `reportlinks` VALUES ('stockcounts','stockmaster','stockcounts.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcounts','stockmaster.stockid=stockcounts.stockid');
INSERT INTO `reportlinks` VALUES ('stockcounts','locations','stockcounts.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcounts','locations.loccode=stockcounts.loccode');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcategory','stockmaster.categoryid=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','stockmaster','stockcategory.categoryid=stockmaster.categoryid');
INSERT INTO `reportlinks` VALUES ('stockmaster','taxcategories','stockmaster.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','stockmaster','taxcategories.taxcatid=stockmaster.taxcatid');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockmaster','stockmoves.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockmoves','stockmaster.stockid=stockmoves.stockid');
INSERT INTO `reportlinks` VALUES ('stockmoves','systypes','stockmoves.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','stockmoves','systypes.typeid=stockmoves.type');
INSERT INTO `reportlinks` VALUES ('stockmoves','locations','stockmoves.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockmoves','locations.loccode=stockmoves.loccode');
INSERT INTO `reportlinks` VALUES ('stockmoves','periods','stockmoves.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','stockmoves','periods.periodno=stockmoves.prd');
INSERT INTO `reportlinks` VALUES ('stockmovestaxes','taxauthorities','stockmovestaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','stockmovestaxes','taxauthorities.taxid=stockmovestaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockmaster','stockserialitems.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockserialitems','stockmaster.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','locations','stockserialitems.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockserialitems','locations.loccode=stockserialitems.loccode');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockmoves','stockserialmoves.stockmoveno=stockmoves.stkmoveno');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockserialmoves','stockmoves.stkmoveno=stockserialmoves.stockmoveno');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockserialitems','stockserialmoves.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockserialmoves','stockserialitems.stockid=stockserialmoves.stockid');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocfrom=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocto=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocto');
INSERT INTO `reportlinks` VALUES ('suppliercontacts','suppliers','suppliercontacts.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','suppliercontacts','suppliers.supplierid=suppliercontacts.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','currencies','suppliers.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','suppliers','currencies.currabrev=suppliers.currcode');
INSERT INTO `reportlinks` VALUES ('suppliers','paymentterms','suppliers.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','suppliers','paymentterms.termsindicator=suppliers.paymentterms');
INSERT INTO `reportlinks` VALUES ('suppliers','taxgroups','suppliers.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','suppliers','taxgroups.taxgroupid=suppliers.taxgroupid');
INSERT INTO `reportlinks` VALUES ('supptrans','systypes','supptrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','supptrans','systypes.typeid=supptrans.type');
INSERT INTO `reportlinks` VALUES ('supptrans','suppliers','supptrans.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','supptrans','suppliers.supplierid=supptrans.supplierno');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','taxauthorities','supptranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','supptranstaxes','taxauthorities.taxid=supptranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','supptrans','supptranstaxes.supptransid=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','supptranstaxes','supptrans.id=supptranstaxes.supptransid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.taxglcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.taxglcode');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.purchtaxglaccount=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.purchtaxglaccount');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxauthorities','taxauthrates.taxauthority=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxauthrates','taxauthorities.taxid=taxauthrates.taxauthority');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxcategories','taxauthrates.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','taxauthrates','taxcategories.taxcatid=taxauthrates.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxprovinces','taxauthrates.dispatchtaxprovince=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','taxauthrates','taxprovinces.taxprovinceid=taxauthrates.dispatchtaxprovince');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxgroups','taxgrouptaxes.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','taxgrouptaxes','taxgroups.taxgroupid=taxgrouptaxes.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxauthorities','taxgrouptaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxgrouptaxes','taxauthorities.taxid=taxgrouptaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('workcentres','locations','workcentres.location=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','workcentres','locations.loccode=workcentres.location');
INSERT INTO `reportlinks` VALUES ('worksorders','locations','worksorders.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','worksorders','locations.loccode=worksorders.loccode');
INSERT INTO `reportlinks` VALUES ('worksorders','stockmaster','worksorders.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','worksorders','stockmaster.stockid=worksorders.stockid');
INSERT INTO `reportlinks` VALUES ('www_users','locations','www_users.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','www_users','locations.loccode=www_users.defaultlocation');
INSERT INTO `reportlinks` VALUES ('accountgroups','accountsection','accountgroups.sectioninaccounts=accountsection.sectionid');
INSERT INTO `reportlinks` VALUES ('accountsection','accountgroups','accountsection.sectionid=accountgroups.sectioninaccounts');
INSERT INTO `reportlinks` VALUES ('bankaccounts','chartmaster','bankaccounts.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','bankaccounts','chartmaster.accountcode=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('banktrans','systypes','banktrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','banktrans','systypes.typeid=banktrans.type');
INSERT INTO `reportlinks` VALUES ('banktrans','bankaccounts','banktrans.bankact=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('bankaccounts','banktrans','bankaccounts.accountcode=banktrans.bankact');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.parent=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.parent');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.component');
INSERT INTO `reportlinks` VALUES ('bom','workcentres','bom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','bom','workcentres.code=bom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('bom','locations','bom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','bom','locations.loccode=bom.loccode');
INSERT INTO `reportlinks` VALUES ('buckets','workcentres','buckets.workcentre=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','buckets','workcentres.code=buckets.workcentre');
INSERT INTO `reportlinks` VALUES ('chartdetails','chartmaster','chartdetails.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','chartdetails','chartmaster.accountcode=chartdetails.accountcode');
INSERT INTO `reportlinks` VALUES ('chartdetails','periods','chartdetails.period=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','chartdetails','periods.periodno=chartdetails.period');
INSERT INTO `reportlinks` VALUES ('chartmaster','accountgroups','chartmaster.group_=accountgroups.groupname');
INSERT INTO `reportlinks` VALUES ('accountgroups','chartmaster','accountgroups.groupname=chartmaster.group_');
INSERT INTO `reportlinks` VALUES ('contractbom','workcentres','contractbom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','contractbom','workcentres.code=contractbom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('contractbom','locations','contractbom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','contractbom','locations.loccode=contractbom.loccode');
INSERT INTO `reportlinks` VALUES ('contractbom','stockmaster','contractbom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','contractbom','stockmaster.stockid=contractbom.component');
INSERT INTO `reportlinks` VALUES ('contractreqts','contracts','contractreqts.contract=contracts.contractref');
INSERT INTO `reportlinks` VALUES ('contracts','contractreqts','contracts.contractref=contractreqts.contract');
INSERT INTO `reportlinks` VALUES ('contracts','custbranch','contracts.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','contracts','custbranch.debtorno=contracts.debtorno');
INSERT INTO `reportlinks` VALUES ('contracts','stockcategory','contracts.branchcode=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','contracts','stockcategory.categoryid=contracts.branchcode');
INSERT INTO `reportlinks` VALUES ('contracts','salestypes','contracts.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','contracts','salestypes.typeabbrev=contracts.typeabbrev');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocfrom=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocto=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocto');
INSERT INTO `reportlinks` VALUES ('custbranch','debtorsmaster','custbranch.debtorno=debtorsmaster.debtorno');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','custbranch','debtorsmaster.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','areas','custbranch.area=areas.areacode');
INSERT INTO `reportlinks` VALUES ('areas','custbranch','areas.areacode=custbranch.area');
INSERT INTO `reportlinks` VALUES ('custbranch','salesman','custbranch.salesman=salesman.salesmancode');
INSERT INTO `reportlinks` VALUES ('salesman','custbranch','salesman.salesmancode=custbranch.salesman');
INSERT INTO `reportlinks` VALUES ('custbranch','locations','custbranch.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','custbranch','locations.loccode=custbranch.defaultlocation');
INSERT INTO `reportlinks` VALUES ('custbranch','shippers','custbranch.defaultshipvia=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','custbranch','shippers.shipper_id=custbranch.defaultshipvia');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','holdreasons','debtorsmaster.holdreason=holdreasons.reasoncode');
INSERT INTO `reportlinks` VALUES ('holdreasons','debtorsmaster','holdreasons.reasoncode=debtorsmaster.holdreason');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','currencies','debtorsmaster.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','debtorsmaster','currencies.currabrev=debtorsmaster.currcode');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','paymentterms','debtorsmaster.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','debtorsmaster','paymentterms.termsindicator=debtorsmaster.paymentterms');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','salestypes','debtorsmaster.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','debtorsmaster','salestypes.typeabbrev=debtorsmaster.salestype');
INSERT INTO `reportlinks` VALUES ('debtortrans','custbranch','debtortrans.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','debtortrans','custbranch.debtorno=debtortrans.debtorno');
INSERT INTO `reportlinks` VALUES ('debtortrans','systypes','debtortrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','debtortrans','systypes.typeid=debtortrans.type');
INSERT INTO `reportlinks` VALUES ('debtortrans','periods','debtortrans.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','debtortrans','periods.periodno=debtortrans.prd');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','taxauthorities','debtortranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','debtortranstaxes','taxauthorities.taxid=debtortranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','debtortrans','debtortranstaxes.debtortransid=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','debtortranstaxes','debtortrans.id=debtortranstaxes.debtortransid');
INSERT INTO `reportlinks` VALUES ('discountmatrix','salestypes','discountmatrix.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','discountmatrix','salestypes.typeabbrev=discountmatrix.salestype');
INSERT INTO `reportlinks` VALUES ('freightcosts','locations','freightcosts.locationfrom=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','freightcosts','locations.loccode=freightcosts.locationfrom');
INSERT INTO `reportlinks` VALUES ('freightcosts','shippers','freightcosts.shipperid=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','freightcosts','shippers.shipper_id=freightcosts.shipperid');
INSERT INTO `reportlinks` VALUES ('gltrans','chartmaster','gltrans.account=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','gltrans','chartmaster.accountcode=gltrans.account');
INSERT INTO `reportlinks` VALUES ('gltrans','systypes','gltrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','gltrans','systypes.typeid=gltrans.type');
INSERT INTO `reportlinks` VALUES ('gltrans','periods','gltrans.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','gltrans','periods.periodno=gltrans.periodno');
INSERT INTO `reportlinks` VALUES ('grns','suppliers','grns.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','grns','suppliers.supplierid=grns.supplierid');
INSERT INTO `reportlinks` VALUES ('grns','purchorderdetails','grns.podetailitem=purchorderdetails.podetailitem');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','grns','purchorderdetails.podetailitem=grns.podetailitem');
INSERT INTO `reportlinks` VALUES ('locations','taxprovinces','locations.taxprovinceid=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','locations','taxprovinces.taxprovinceid=locations.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('locstock','locations','locstock.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','locstock','locations.loccode=locstock.loccode');
INSERT INTO `reportlinks` VALUES ('locstock','stockmaster','locstock.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','locstock','stockmaster.stockid=locstock.stockid');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.shiploc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.shiploc');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.recloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.recloc');
INSERT INTO `reportlinks` VALUES ('loctransfers','stockmaster','loctransfers.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','loctransfers','stockmaster.stockid=loctransfers.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','stockmaster','orderdeliverydifferenceslog.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','orderdeliverydifferencesl','stockmaster.stockid=orderdeliverydifferenceslog.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','custbranch','orderdeliverydifferenceslog.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','orderdeliverydifferencesl','custbranch.debtorno=orderdeliverydifferenceslog.debtorno');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','salesorders','orderdeliverydifferenceslog.branchcode=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','orderdeliverydifferencesl','salesorders.orderno=orderdeliverydifferenceslog.branchcode');
INSERT INTO `reportlinks` VALUES ('prices','stockmaster','prices.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','prices','stockmaster.stockid=prices.stockid');
INSERT INTO `reportlinks` VALUES ('prices','currencies','prices.currabrev=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','prices','currencies.currabrev=prices.currabrev');
INSERT INTO `reportlinks` VALUES ('prices','salestypes','prices.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','prices','salestypes.typeabbrev=prices.typeabbrev');
INSERT INTO `reportlinks` VALUES ('purchdata','stockmaster','purchdata.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','purchdata','stockmaster.stockid=purchdata.stockid');
INSERT INTO `reportlinks` VALUES ('purchdata','suppliers','purchdata.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchdata','suppliers.supplierid=purchdata.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','purchorders','purchorderdetails.orderno=purchorders.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','purchorderdetails','purchorders.orderno=purchorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','suppliers','purchorders.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchorders','suppliers.supplierid=purchorders.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorders','locations','purchorders.intostocklocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','purchorders','locations.loccode=purchorders.intostocklocation');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','custbranch','recurringsalesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','recurringsalesorders','custbranch.branchcode=recurringsalesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','recurringsalesorders','recurrsalesorderdetails.recurrorderno=recurringsalesorders.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','recurrsalesorderdetails','recurringsalesorders.recurrorderno=recurrsalesorderdetails.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','stockmaster','recurrsalesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','recurrsalesorderdetails','stockmaster.stockid=recurrsalesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('reportcolumns','reportheaders','reportcolumns.reportid=reportheaders.reportid');
INSERT INTO `reportlinks` VALUES ('reportheaders','reportcolumns','reportheaders.reportid=reportcolumns.reportid');
INSERT INTO `reportlinks` VALUES ('salesanalysis','periods','salesanalysis.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','salesanalysis','periods.periodno=salesanalysis.periodno');
INSERT INTO `reportlinks` VALUES ('salescatprod','stockmaster','salescatprod.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salescatprod','stockmaster.stockid=salescatprod.stockid');
INSERT INTO `reportlinks` VALUES ('salescatprod','salescat','salescatprod.salescatid=salescat.salescatid');
INSERT INTO `reportlinks` VALUES ('salescat','salescatprod','salescat.salescatid=salescatprod.salescatid');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','salesorders','salesorderdetails.orderno=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','salesorderdetails','salesorders.orderno=salesorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','stockmaster','salesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salesorderdetails','stockmaster.stockid=salesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('salesorders','custbranch','salesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','salesorders','custbranch.branchcode=salesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('salesorders','shippers','salesorders.debtorno=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','salesorders','shippers.shipper_id=salesorders.debtorno');
INSERT INTO `reportlinks` VALUES ('salesorders','locations','salesorders.fromstkloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','salesorders','locations.loccode=salesorders.fromstkloc');
INSERT INTO `reportlinks` VALUES ('securitygroups','securityroles','securitygroups.secroleid=securityroles.secroleid');
INSERT INTO `reportlinks` VALUES ('securityroles','securitygroups','securityroles.secroleid=securitygroups.secroleid');
INSERT INTO `reportlinks` VALUES ('securitygroups','securitytokens','securitygroups.tokenid=securitytokens.tokenid');
INSERT INTO `reportlinks` VALUES ('securitytokens','securitygroups','securitytokens.tokenid=securitygroups.tokenid');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','shipments','shipmentcharges.shiptref=shipments.shiptref');
INSERT INTO `reportlinks` VALUES ('shipments','shipmentcharges','shipments.shiptref=shipmentcharges.shiptref');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','systypes','shipmentcharges.transtype=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','shipmentcharges','systypes.typeid=shipmentcharges.transtype');
INSERT INTO `reportlinks` VALUES ('shipments','suppliers','shipments.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','shipments','suppliers.supplierid=shipments.supplierid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','stockmaster','stockcheckfreeze.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcheckfreeze','stockmaster.stockid=stockcheckfreeze.stockid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','locations','stockcheckfreeze.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcheckfreeze','locations.loccode=stockcheckfreeze.loccode');
INSERT INTO `reportlinks` VALUES ('stockcounts','stockmaster','stockcounts.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcounts','stockmaster.stockid=stockcounts.stockid');
INSERT INTO `reportlinks` VALUES ('stockcounts','locations','stockcounts.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcounts','locations.loccode=stockcounts.loccode');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcategory','stockmaster.categoryid=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','stockmaster','stockcategory.categoryid=stockmaster.categoryid');
INSERT INTO `reportlinks` VALUES ('stockmaster','taxcategories','stockmaster.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','stockmaster','taxcategories.taxcatid=stockmaster.taxcatid');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockmaster','stockmoves.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockmoves','stockmaster.stockid=stockmoves.stockid');
INSERT INTO `reportlinks` VALUES ('stockmoves','systypes','stockmoves.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','stockmoves','systypes.typeid=stockmoves.type');
INSERT INTO `reportlinks` VALUES ('stockmoves','locations','stockmoves.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockmoves','locations.loccode=stockmoves.loccode');
INSERT INTO `reportlinks` VALUES ('stockmoves','periods','stockmoves.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','stockmoves','periods.periodno=stockmoves.prd');
INSERT INTO `reportlinks` VALUES ('stockmovestaxes','taxauthorities','stockmovestaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','stockmovestaxes','taxauthorities.taxid=stockmovestaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockmaster','stockserialitems.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockserialitems','stockmaster.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','locations','stockserialitems.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockserialitems','locations.loccode=stockserialitems.loccode');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockmoves','stockserialmoves.stockmoveno=stockmoves.stkmoveno');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockserialmoves','stockmoves.stkmoveno=stockserialmoves.stockmoveno');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockserialitems','stockserialmoves.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockserialmoves','stockserialitems.stockid=stockserialmoves.stockid');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocfrom=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocto=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocto');
INSERT INTO `reportlinks` VALUES ('suppliercontacts','suppliers','suppliercontacts.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','suppliercontacts','suppliers.supplierid=suppliercontacts.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','currencies','suppliers.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','suppliers','currencies.currabrev=suppliers.currcode');
INSERT INTO `reportlinks` VALUES ('suppliers','paymentterms','suppliers.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','suppliers','paymentterms.termsindicator=suppliers.paymentterms');
INSERT INTO `reportlinks` VALUES ('suppliers','taxgroups','suppliers.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','suppliers','taxgroups.taxgroupid=suppliers.taxgroupid');
INSERT INTO `reportlinks` VALUES ('supptrans','systypes','supptrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','supptrans','systypes.typeid=supptrans.type');
INSERT INTO `reportlinks` VALUES ('supptrans','suppliers','supptrans.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','supptrans','suppliers.supplierid=supptrans.supplierno');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','taxauthorities','supptranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','supptranstaxes','taxauthorities.taxid=supptranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','supptrans','supptranstaxes.supptransid=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','supptranstaxes','supptrans.id=supptranstaxes.supptransid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.taxglcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.taxglcode');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.purchtaxglaccount=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.purchtaxglaccount');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxauthorities','taxauthrates.taxauthority=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxauthrates','taxauthorities.taxid=taxauthrates.taxauthority');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxcategories','taxauthrates.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','taxauthrates','taxcategories.taxcatid=taxauthrates.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxprovinces','taxauthrates.dispatchtaxprovince=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','taxauthrates','taxprovinces.taxprovinceid=taxauthrates.dispatchtaxprovince');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxgroups','taxgrouptaxes.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','taxgrouptaxes','taxgroups.taxgroupid=taxgrouptaxes.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxauthorities','taxgrouptaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxgrouptaxes','taxauthorities.taxid=taxgrouptaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('workcentres','locations','workcentres.location=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','workcentres','locations.loccode=workcentres.location');
INSERT INTO `reportlinks` VALUES ('worksorders','locations','worksorders.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','worksorders','locations.loccode=worksorders.loccode');
INSERT INTO `reportlinks` VALUES ('worksorders','stockmaster','worksorders.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','worksorders','stockmaster.stockid=worksorders.stockid');
INSERT INTO `reportlinks` VALUES ('www_users','locations','www_users.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','www_users','locations.loccode=www_users.defaultlocation');
INSERT INTO `reportlinks` VALUES ('accountgroups','accountsection','accountgroups.sectioninaccounts=accountsection.sectionid');
INSERT INTO `reportlinks` VALUES ('accountsection','accountgroups','accountsection.sectionid=accountgroups.sectioninaccounts');
INSERT INTO `reportlinks` VALUES ('bankaccounts','chartmaster','bankaccounts.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','bankaccounts','chartmaster.accountcode=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('banktrans','systypes','banktrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','banktrans','systypes.typeid=banktrans.type');
INSERT INTO `reportlinks` VALUES ('banktrans','bankaccounts','banktrans.bankact=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('bankaccounts','banktrans','bankaccounts.accountcode=banktrans.bankact');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.parent=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.parent');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.component');
INSERT INTO `reportlinks` VALUES ('bom','workcentres','bom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','bom','workcentres.code=bom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('bom','locations','bom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','bom','locations.loccode=bom.loccode');
INSERT INTO `reportlinks` VALUES ('buckets','workcentres','buckets.workcentre=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','buckets','workcentres.code=buckets.workcentre');
INSERT INTO `reportlinks` VALUES ('chartdetails','chartmaster','chartdetails.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','chartdetails','chartmaster.accountcode=chartdetails.accountcode');
INSERT INTO `reportlinks` VALUES ('chartdetails','periods','chartdetails.period=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','chartdetails','periods.periodno=chartdetails.period');
INSERT INTO `reportlinks` VALUES ('chartmaster','accountgroups','chartmaster.group_=accountgroups.groupname');
INSERT INTO `reportlinks` VALUES ('accountgroups','chartmaster','accountgroups.groupname=chartmaster.group_');
INSERT INTO `reportlinks` VALUES ('contractbom','workcentres','contractbom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','contractbom','workcentres.code=contractbom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('contractbom','locations','contractbom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','contractbom','locations.loccode=contractbom.loccode');
INSERT INTO `reportlinks` VALUES ('contractbom','stockmaster','contractbom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','contractbom','stockmaster.stockid=contractbom.component');
INSERT INTO `reportlinks` VALUES ('contractreqts','contracts','contractreqts.contract=contracts.contractref');
INSERT INTO `reportlinks` VALUES ('contracts','contractreqts','contracts.contractref=contractreqts.contract');
INSERT INTO `reportlinks` VALUES ('contracts','custbranch','contracts.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','contracts','custbranch.debtorno=contracts.debtorno');
INSERT INTO `reportlinks` VALUES ('contracts','stockcategory','contracts.branchcode=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','contracts','stockcategory.categoryid=contracts.branchcode');
INSERT INTO `reportlinks` VALUES ('contracts','salestypes','contracts.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','contracts','salestypes.typeabbrev=contracts.typeabbrev');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocfrom=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocto=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocto');
INSERT INTO `reportlinks` VALUES ('custbranch','debtorsmaster','custbranch.debtorno=debtorsmaster.debtorno');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','custbranch','debtorsmaster.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','areas','custbranch.area=areas.areacode');
INSERT INTO `reportlinks` VALUES ('areas','custbranch','areas.areacode=custbranch.area');
INSERT INTO `reportlinks` VALUES ('custbranch','salesman','custbranch.salesman=salesman.salesmancode');
INSERT INTO `reportlinks` VALUES ('salesman','custbranch','salesman.salesmancode=custbranch.salesman');
INSERT INTO `reportlinks` VALUES ('custbranch','locations','custbranch.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','custbranch','locations.loccode=custbranch.defaultlocation');
INSERT INTO `reportlinks` VALUES ('custbranch','shippers','custbranch.defaultshipvia=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','custbranch','shippers.shipper_id=custbranch.defaultshipvia');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','holdreasons','debtorsmaster.holdreason=holdreasons.reasoncode');
INSERT INTO `reportlinks` VALUES ('holdreasons','debtorsmaster','holdreasons.reasoncode=debtorsmaster.holdreason');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','currencies','debtorsmaster.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','debtorsmaster','currencies.currabrev=debtorsmaster.currcode');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','paymentterms','debtorsmaster.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','debtorsmaster','paymentterms.termsindicator=debtorsmaster.paymentterms');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','salestypes','debtorsmaster.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','debtorsmaster','salestypes.typeabbrev=debtorsmaster.salestype');
INSERT INTO `reportlinks` VALUES ('debtortrans','custbranch','debtortrans.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','debtortrans','custbranch.debtorno=debtortrans.debtorno');
INSERT INTO `reportlinks` VALUES ('debtortrans','systypes','debtortrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','debtortrans','systypes.typeid=debtortrans.type');
INSERT INTO `reportlinks` VALUES ('debtortrans','periods','debtortrans.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','debtortrans','periods.periodno=debtortrans.prd');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','taxauthorities','debtortranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','debtortranstaxes','taxauthorities.taxid=debtortranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','debtortrans','debtortranstaxes.debtortransid=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','debtortranstaxes','debtortrans.id=debtortranstaxes.debtortransid');
INSERT INTO `reportlinks` VALUES ('discountmatrix','salestypes','discountmatrix.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','discountmatrix','salestypes.typeabbrev=discountmatrix.salestype');
INSERT INTO `reportlinks` VALUES ('freightcosts','locations','freightcosts.locationfrom=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','freightcosts','locations.loccode=freightcosts.locationfrom');
INSERT INTO `reportlinks` VALUES ('freightcosts','shippers','freightcosts.shipperid=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','freightcosts','shippers.shipper_id=freightcosts.shipperid');
INSERT INTO `reportlinks` VALUES ('gltrans','chartmaster','gltrans.account=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','gltrans','chartmaster.accountcode=gltrans.account');
INSERT INTO `reportlinks` VALUES ('gltrans','systypes','gltrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','gltrans','systypes.typeid=gltrans.type');
INSERT INTO `reportlinks` VALUES ('gltrans','periods','gltrans.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','gltrans','periods.periodno=gltrans.periodno');
INSERT INTO `reportlinks` VALUES ('grns','suppliers','grns.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','grns','suppliers.supplierid=grns.supplierid');
INSERT INTO `reportlinks` VALUES ('grns','purchorderdetails','grns.podetailitem=purchorderdetails.podetailitem');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','grns','purchorderdetails.podetailitem=grns.podetailitem');
INSERT INTO `reportlinks` VALUES ('locations','taxprovinces','locations.taxprovinceid=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','locations','taxprovinces.taxprovinceid=locations.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('locstock','locations','locstock.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','locstock','locations.loccode=locstock.loccode');
INSERT INTO `reportlinks` VALUES ('locstock','stockmaster','locstock.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','locstock','stockmaster.stockid=locstock.stockid');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.shiploc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.shiploc');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.recloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.recloc');
INSERT INTO `reportlinks` VALUES ('loctransfers','stockmaster','loctransfers.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','loctransfers','stockmaster.stockid=loctransfers.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','stockmaster','orderdeliverydifferenceslog.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','orderdeliverydifferencesl','stockmaster.stockid=orderdeliverydifferenceslog.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','custbranch','orderdeliverydifferenceslog.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','orderdeliverydifferencesl','custbranch.debtorno=orderdeliverydifferenceslog.debtorno');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','salesorders','orderdeliverydifferenceslog.branchcode=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','orderdeliverydifferencesl','salesorders.orderno=orderdeliverydifferenceslog.branchcode');
INSERT INTO `reportlinks` VALUES ('prices','stockmaster','prices.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','prices','stockmaster.stockid=prices.stockid');
INSERT INTO `reportlinks` VALUES ('prices','currencies','prices.currabrev=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','prices','currencies.currabrev=prices.currabrev');
INSERT INTO `reportlinks` VALUES ('prices','salestypes','prices.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','prices','salestypes.typeabbrev=prices.typeabbrev');
INSERT INTO `reportlinks` VALUES ('purchdata','stockmaster','purchdata.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','purchdata','stockmaster.stockid=purchdata.stockid');
INSERT INTO `reportlinks` VALUES ('purchdata','suppliers','purchdata.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchdata','suppliers.supplierid=purchdata.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','purchorders','purchorderdetails.orderno=purchorders.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','purchorderdetails','purchorders.orderno=purchorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','suppliers','purchorders.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchorders','suppliers.supplierid=purchorders.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorders','locations','purchorders.intostocklocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','purchorders','locations.loccode=purchorders.intostocklocation');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','custbranch','recurringsalesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','recurringsalesorders','custbranch.branchcode=recurringsalesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','recurringsalesorders','recurrsalesorderdetails.recurrorderno=recurringsalesorders.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','recurrsalesorderdetails','recurringsalesorders.recurrorderno=recurrsalesorderdetails.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','stockmaster','recurrsalesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','recurrsalesorderdetails','stockmaster.stockid=recurrsalesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('reportcolumns','reportheaders','reportcolumns.reportid=reportheaders.reportid');
INSERT INTO `reportlinks` VALUES ('reportheaders','reportcolumns','reportheaders.reportid=reportcolumns.reportid');
INSERT INTO `reportlinks` VALUES ('salesanalysis','periods','salesanalysis.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','salesanalysis','periods.periodno=salesanalysis.periodno');
INSERT INTO `reportlinks` VALUES ('salescatprod','stockmaster','salescatprod.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salescatprod','stockmaster.stockid=salescatprod.stockid');
INSERT INTO `reportlinks` VALUES ('salescatprod','salescat','salescatprod.salescatid=salescat.salescatid');
INSERT INTO `reportlinks` VALUES ('salescat','salescatprod','salescat.salescatid=salescatprod.salescatid');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','salesorders','salesorderdetails.orderno=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','salesorderdetails','salesorders.orderno=salesorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','stockmaster','salesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salesorderdetails','stockmaster.stockid=salesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('salesorders','custbranch','salesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','salesorders','custbranch.branchcode=salesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('salesorders','shippers','salesorders.debtorno=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','salesorders','shippers.shipper_id=salesorders.debtorno');
INSERT INTO `reportlinks` VALUES ('salesorders','locations','salesorders.fromstkloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','salesorders','locations.loccode=salesorders.fromstkloc');
INSERT INTO `reportlinks` VALUES ('securitygroups','securityroles','securitygroups.secroleid=securityroles.secroleid');
INSERT INTO `reportlinks` VALUES ('securityroles','securitygroups','securityroles.secroleid=securitygroups.secroleid');
INSERT INTO `reportlinks` VALUES ('securitygroups','securitytokens','securitygroups.tokenid=securitytokens.tokenid');
INSERT INTO `reportlinks` VALUES ('securitytokens','securitygroups','securitytokens.tokenid=securitygroups.tokenid');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','shipments','shipmentcharges.shiptref=shipments.shiptref');
INSERT INTO `reportlinks` VALUES ('shipments','shipmentcharges','shipments.shiptref=shipmentcharges.shiptref');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','systypes','shipmentcharges.transtype=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','shipmentcharges','systypes.typeid=shipmentcharges.transtype');
INSERT INTO `reportlinks` VALUES ('shipments','suppliers','shipments.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','shipments','suppliers.supplierid=shipments.supplierid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','stockmaster','stockcheckfreeze.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcheckfreeze','stockmaster.stockid=stockcheckfreeze.stockid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','locations','stockcheckfreeze.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcheckfreeze','locations.loccode=stockcheckfreeze.loccode');
INSERT INTO `reportlinks` VALUES ('stockcounts','stockmaster','stockcounts.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcounts','stockmaster.stockid=stockcounts.stockid');
INSERT INTO `reportlinks` VALUES ('stockcounts','locations','stockcounts.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcounts','locations.loccode=stockcounts.loccode');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcategory','stockmaster.categoryid=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','stockmaster','stockcategory.categoryid=stockmaster.categoryid');
INSERT INTO `reportlinks` VALUES ('stockmaster','taxcategories','stockmaster.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','stockmaster','taxcategories.taxcatid=stockmaster.taxcatid');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockmaster','stockmoves.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockmoves','stockmaster.stockid=stockmoves.stockid');
INSERT INTO `reportlinks` VALUES ('stockmoves','systypes','stockmoves.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','stockmoves','systypes.typeid=stockmoves.type');
INSERT INTO `reportlinks` VALUES ('stockmoves','locations','stockmoves.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockmoves','locations.loccode=stockmoves.loccode');
INSERT INTO `reportlinks` VALUES ('stockmoves','periods','stockmoves.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','stockmoves','periods.periodno=stockmoves.prd');
INSERT INTO `reportlinks` VALUES ('stockmovestaxes','taxauthorities','stockmovestaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','stockmovestaxes','taxauthorities.taxid=stockmovestaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockmaster','stockserialitems.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockserialitems','stockmaster.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','locations','stockserialitems.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockserialitems','locations.loccode=stockserialitems.loccode');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockmoves','stockserialmoves.stockmoveno=stockmoves.stkmoveno');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockserialmoves','stockmoves.stkmoveno=stockserialmoves.stockmoveno');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockserialitems','stockserialmoves.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockserialmoves','stockserialitems.stockid=stockserialmoves.stockid');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocfrom=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocto=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocto');
INSERT INTO `reportlinks` VALUES ('suppliercontacts','suppliers','suppliercontacts.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','suppliercontacts','suppliers.supplierid=suppliercontacts.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','currencies','suppliers.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','suppliers','currencies.currabrev=suppliers.currcode');
INSERT INTO `reportlinks` VALUES ('suppliers','paymentterms','suppliers.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','suppliers','paymentterms.termsindicator=suppliers.paymentterms');
INSERT INTO `reportlinks` VALUES ('suppliers','taxgroups','suppliers.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','suppliers','taxgroups.taxgroupid=suppliers.taxgroupid');
INSERT INTO `reportlinks` VALUES ('supptrans','systypes','supptrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','supptrans','systypes.typeid=supptrans.type');
INSERT INTO `reportlinks` VALUES ('supptrans','suppliers','supptrans.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','supptrans','suppliers.supplierid=supptrans.supplierno');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','taxauthorities','supptranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','supptranstaxes','taxauthorities.taxid=supptranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','supptrans','supptranstaxes.supptransid=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','supptranstaxes','supptrans.id=supptranstaxes.supptransid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.taxglcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.taxglcode');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.purchtaxglaccount=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.purchtaxglaccount');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxauthorities','taxauthrates.taxauthority=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxauthrates','taxauthorities.taxid=taxauthrates.taxauthority');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxcategories','taxauthrates.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','taxauthrates','taxcategories.taxcatid=taxauthrates.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxprovinces','taxauthrates.dispatchtaxprovince=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','taxauthrates','taxprovinces.taxprovinceid=taxauthrates.dispatchtaxprovince');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxgroups','taxgrouptaxes.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','taxgrouptaxes','taxgroups.taxgroupid=taxgrouptaxes.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxauthorities','taxgrouptaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxgrouptaxes','taxauthorities.taxid=taxgrouptaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('workcentres','locations','workcentres.location=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','workcentres','locations.loccode=workcentres.location');
INSERT INTO `reportlinks` VALUES ('worksorders','locations','worksorders.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','worksorders','locations.loccode=worksorders.loccode');
INSERT INTO `reportlinks` VALUES ('worksorders','stockmaster','worksorders.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','worksorders','stockmaster.stockid=worksorders.stockid');
INSERT INTO `reportlinks` VALUES ('www_users','locations','www_users.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','www_users','locations.loccode=www_users.defaultlocation');
INSERT INTO `reportlinks` VALUES ('accountgroups','accountsection','accountgroups.sectioninaccounts=accountsection.sectionid');
INSERT INTO `reportlinks` VALUES ('accountsection','accountgroups','accountsection.sectionid=accountgroups.sectioninaccounts');
INSERT INTO `reportlinks` VALUES ('bankaccounts','chartmaster','bankaccounts.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','bankaccounts','chartmaster.accountcode=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('banktrans','systypes','banktrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','banktrans','systypes.typeid=banktrans.type');
INSERT INTO `reportlinks` VALUES ('banktrans','bankaccounts','banktrans.bankact=bankaccounts.accountcode');
INSERT INTO `reportlinks` VALUES ('bankaccounts','banktrans','bankaccounts.accountcode=banktrans.bankact');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.parent=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.parent');
INSERT INTO `reportlinks` VALUES ('bom','stockmaster','bom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','bom','stockmaster.stockid=bom.component');
INSERT INTO `reportlinks` VALUES ('bom','workcentres','bom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','bom','workcentres.code=bom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('bom','locations','bom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','bom','locations.loccode=bom.loccode');
INSERT INTO `reportlinks` VALUES ('buckets','workcentres','buckets.workcentre=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','buckets','workcentres.code=buckets.workcentre');
INSERT INTO `reportlinks` VALUES ('chartdetails','chartmaster','chartdetails.accountcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','chartdetails','chartmaster.accountcode=chartdetails.accountcode');
INSERT INTO `reportlinks` VALUES ('chartdetails','periods','chartdetails.period=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','chartdetails','periods.periodno=chartdetails.period');
INSERT INTO `reportlinks` VALUES ('chartmaster','accountgroups','chartmaster.group_=accountgroups.groupname');
INSERT INTO `reportlinks` VALUES ('accountgroups','chartmaster','accountgroups.groupname=chartmaster.group_');
INSERT INTO `reportlinks` VALUES ('contractbom','workcentres','contractbom.workcentreadded=workcentres.code');
INSERT INTO `reportlinks` VALUES ('workcentres','contractbom','workcentres.code=contractbom.workcentreadded');
INSERT INTO `reportlinks` VALUES ('contractbom','locations','contractbom.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','contractbom','locations.loccode=contractbom.loccode');
INSERT INTO `reportlinks` VALUES ('contractbom','stockmaster','contractbom.component=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','contractbom','stockmaster.stockid=contractbom.component');
INSERT INTO `reportlinks` VALUES ('contractreqts','contracts','contractreqts.contract=contracts.contractref');
INSERT INTO `reportlinks` VALUES ('contracts','contractreqts','contracts.contractref=contractreqts.contract');
INSERT INTO `reportlinks` VALUES ('contracts','custbranch','contracts.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','contracts','custbranch.debtorno=contracts.debtorno');
INSERT INTO `reportlinks` VALUES ('contracts','stockcategory','contracts.branchcode=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','contracts','stockcategory.categoryid=contracts.branchcode');
INSERT INTO `reportlinks` VALUES ('contracts','salestypes','contracts.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','contracts','salestypes.typeabbrev=contracts.typeabbrev');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocfrom=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('custallocns','debtortrans','custallocns.transid_allocto=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','custallocns','debtortrans.id=custallocns.transid_allocto');
INSERT INTO `reportlinks` VALUES ('custbranch','debtorsmaster','custbranch.debtorno=debtorsmaster.debtorno');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','custbranch','debtorsmaster.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','areas','custbranch.area=areas.areacode');
INSERT INTO `reportlinks` VALUES ('areas','custbranch','areas.areacode=custbranch.area');
INSERT INTO `reportlinks` VALUES ('custbranch','salesman','custbranch.salesman=salesman.salesmancode');
INSERT INTO `reportlinks` VALUES ('salesman','custbranch','salesman.salesmancode=custbranch.salesman');
INSERT INTO `reportlinks` VALUES ('custbranch','locations','custbranch.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','custbranch','locations.loccode=custbranch.defaultlocation');
INSERT INTO `reportlinks` VALUES ('custbranch','shippers','custbranch.defaultshipvia=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','custbranch','shippers.shipper_id=custbranch.defaultshipvia');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','holdreasons','debtorsmaster.holdreason=holdreasons.reasoncode');
INSERT INTO `reportlinks` VALUES ('holdreasons','debtorsmaster','holdreasons.reasoncode=debtorsmaster.holdreason');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','currencies','debtorsmaster.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','debtorsmaster','currencies.currabrev=debtorsmaster.currcode');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','paymentterms','debtorsmaster.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','debtorsmaster','paymentterms.termsindicator=debtorsmaster.paymentterms');
INSERT INTO `reportlinks` VALUES ('debtorsmaster','salestypes','debtorsmaster.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','debtorsmaster','salestypes.typeabbrev=debtorsmaster.salestype');
INSERT INTO `reportlinks` VALUES ('debtortrans','custbranch','debtortrans.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','debtortrans','custbranch.debtorno=debtortrans.debtorno');
INSERT INTO `reportlinks` VALUES ('debtortrans','systypes','debtortrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','debtortrans','systypes.typeid=debtortrans.type');
INSERT INTO `reportlinks` VALUES ('debtortrans','periods','debtortrans.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','debtortrans','periods.periodno=debtortrans.prd');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','taxauthorities','debtortranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','debtortranstaxes','taxauthorities.taxid=debtortranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('debtortranstaxes','debtortrans','debtortranstaxes.debtortransid=debtortrans.id');
INSERT INTO `reportlinks` VALUES ('debtortrans','debtortranstaxes','debtortrans.id=debtortranstaxes.debtortransid');
INSERT INTO `reportlinks` VALUES ('discountmatrix','salestypes','discountmatrix.salestype=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','discountmatrix','salestypes.typeabbrev=discountmatrix.salestype');
INSERT INTO `reportlinks` VALUES ('freightcosts','locations','freightcosts.locationfrom=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','freightcosts','locations.loccode=freightcosts.locationfrom');
INSERT INTO `reportlinks` VALUES ('freightcosts','shippers','freightcosts.shipperid=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','freightcosts','shippers.shipper_id=freightcosts.shipperid');
INSERT INTO `reportlinks` VALUES ('gltrans','chartmaster','gltrans.account=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','gltrans','chartmaster.accountcode=gltrans.account');
INSERT INTO `reportlinks` VALUES ('gltrans','systypes','gltrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','gltrans','systypes.typeid=gltrans.type');
INSERT INTO `reportlinks` VALUES ('gltrans','periods','gltrans.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','gltrans','periods.periodno=gltrans.periodno');
INSERT INTO `reportlinks` VALUES ('grns','suppliers','grns.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','grns','suppliers.supplierid=grns.supplierid');
INSERT INTO `reportlinks` VALUES ('grns','purchorderdetails','grns.podetailitem=purchorderdetails.podetailitem');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','grns','purchorderdetails.podetailitem=grns.podetailitem');
INSERT INTO `reportlinks` VALUES ('locations','taxprovinces','locations.taxprovinceid=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','locations','taxprovinces.taxprovinceid=locations.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('locstock','locations','locstock.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','locstock','locations.loccode=locstock.loccode');
INSERT INTO `reportlinks` VALUES ('locstock','stockmaster','locstock.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','locstock','stockmaster.stockid=locstock.stockid');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.shiploc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.shiploc');
INSERT INTO `reportlinks` VALUES ('loctransfers','locations','loctransfers.recloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','loctransfers','locations.loccode=loctransfers.recloc');
INSERT INTO `reportlinks` VALUES ('loctransfers','stockmaster','loctransfers.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','loctransfers','stockmaster.stockid=loctransfers.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','stockmaster','orderdeliverydifferenceslog.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','orderdeliverydifferencesl','stockmaster.stockid=orderdeliverydifferenceslog.stockid');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','custbranch','orderdeliverydifferenceslog.debtorno=custbranch.debtorno');
INSERT INTO `reportlinks` VALUES ('custbranch','orderdeliverydifferencesl','custbranch.debtorno=orderdeliverydifferenceslog.debtorno');
INSERT INTO `reportlinks` VALUES ('orderdeliverydifferencesl','salesorders','orderdeliverydifferenceslog.branchcode=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','orderdeliverydifferencesl','salesorders.orderno=orderdeliverydifferenceslog.branchcode');
INSERT INTO `reportlinks` VALUES ('prices','stockmaster','prices.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','prices','stockmaster.stockid=prices.stockid');
INSERT INTO `reportlinks` VALUES ('prices','currencies','prices.currabrev=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','prices','currencies.currabrev=prices.currabrev');
INSERT INTO `reportlinks` VALUES ('prices','salestypes','prices.typeabbrev=salestypes.typeabbrev');
INSERT INTO `reportlinks` VALUES ('salestypes','prices','salestypes.typeabbrev=prices.typeabbrev');
INSERT INTO `reportlinks` VALUES ('purchdata','stockmaster','purchdata.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','purchdata','stockmaster.stockid=purchdata.stockid');
INSERT INTO `reportlinks` VALUES ('purchdata','suppliers','purchdata.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchdata','suppliers.supplierid=purchdata.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorderdetails','purchorders','purchorderdetails.orderno=purchorders.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','purchorderdetails','purchorders.orderno=purchorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('purchorders','suppliers','purchorders.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','purchorders','suppliers.supplierid=purchorders.supplierno');
INSERT INTO `reportlinks` VALUES ('purchorders','locations','purchorders.intostocklocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','purchorders','locations.loccode=purchorders.intostocklocation');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','custbranch','recurringsalesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','recurringsalesorders','custbranch.branchcode=recurringsalesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','recurringsalesorders','recurrsalesorderdetails.recurrorderno=recurringsalesorders.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurringsalesorders','recurrsalesorderdetails','recurringsalesorders.recurrorderno=recurrsalesorderdetails.recurrorderno');
INSERT INTO `reportlinks` VALUES ('recurrsalesorderdetails','stockmaster','recurrsalesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','recurrsalesorderdetails','stockmaster.stockid=recurrsalesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('reportcolumns','reportheaders','reportcolumns.reportid=reportheaders.reportid');
INSERT INTO `reportlinks` VALUES ('reportheaders','reportcolumns','reportheaders.reportid=reportcolumns.reportid');
INSERT INTO `reportlinks` VALUES ('salesanalysis','periods','salesanalysis.periodno=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','salesanalysis','periods.periodno=salesanalysis.periodno');
INSERT INTO `reportlinks` VALUES ('salescatprod','stockmaster','salescatprod.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salescatprod','stockmaster.stockid=salescatprod.stockid');
INSERT INTO `reportlinks` VALUES ('salescatprod','salescat','salescatprod.salescatid=salescat.salescatid');
INSERT INTO `reportlinks` VALUES ('salescat','salescatprod','salescat.salescatid=salescatprod.salescatid');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','salesorders','salesorderdetails.orderno=salesorders.orderno');
INSERT INTO `reportlinks` VALUES ('salesorders','salesorderdetails','salesorders.orderno=salesorderdetails.orderno');
INSERT INTO `reportlinks` VALUES ('salesorderdetails','stockmaster','salesorderdetails.stkcode=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','salesorderdetails','stockmaster.stockid=salesorderdetails.stkcode');
INSERT INTO `reportlinks` VALUES ('salesorders','custbranch','salesorders.branchcode=custbranch.branchcode');
INSERT INTO `reportlinks` VALUES ('custbranch','salesorders','custbranch.branchcode=salesorders.branchcode');
INSERT INTO `reportlinks` VALUES ('salesorders','shippers','salesorders.debtorno=shippers.shipper_id');
INSERT INTO `reportlinks` VALUES ('shippers','salesorders','shippers.shipper_id=salesorders.debtorno');
INSERT INTO `reportlinks` VALUES ('salesorders','locations','salesorders.fromstkloc=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','salesorders','locations.loccode=salesorders.fromstkloc');
INSERT INTO `reportlinks` VALUES ('securitygroups','securityroles','securitygroups.secroleid=securityroles.secroleid');
INSERT INTO `reportlinks` VALUES ('securityroles','securitygroups','securityroles.secroleid=securitygroups.secroleid');
INSERT INTO `reportlinks` VALUES ('securitygroups','securitytokens','securitygroups.tokenid=securitytokens.tokenid');
INSERT INTO `reportlinks` VALUES ('securitytokens','securitygroups','securitytokens.tokenid=securitygroups.tokenid');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','shipments','shipmentcharges.shiptref=shipments.shiptref');
INSERT INTO `reportlinks` VALUES ('shipments','shipmentcharges','shipments.shiptref=shipmentcharges.shiptref');
INSERT INTO `reportlinks` VALUES ('shipmentcharges','systypes','shipmentcharges.transtype=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','shipmentcharges','systypes.typeid=shipmentcharges.transtype');
INSERT INTO `reportlinks` VALUES ('shipments','suppliers','shipments.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','shipments','suppliers.supplierid=shipments.supplierid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','stockmaster','stockcheckfreeze.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcheckfreeze','stockmaster.stockid=stockcheckfreeze.stockid');
INSERT INTO `reportlinks` VALUES ('stockcheckfreeze','locations','stockcheckfreeze.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcheckfreeze','locations.loccode=stockcheckfreeze.loccode');
INSERT INTO `reportlinks` VALUES ('stockcounts','stockmaster','stockcounts.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcounts','stockmaster.stockid=stockcounts.stockid');
INSERT INTO `reportlinks` VALUES ('stockcounts','locations','stockcounts.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockcounts','locations.loccode=stockcounts.loccode');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockcategory','stockmaster.categoryid=stockcategory.categoryid');
INSERT INTO `reportlinks` VALUES ('stockcategory','stockmaster','stockcategory.categoryid=stockmaster.categoryid');
INSERT INTO `reportlinks` VALUES ('stockmaster','taxcategories','stockmaster.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','stockmaster','taxcategories.taxcatid=stockmaster.taxcatid');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockmaster','stockmoves.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockmoves','stockmaster.stockid=stockmoves.stockid');
INSERT INTO `reportlinks` VALUES ('stockmoves','systypes','stockmoves.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','stockmoves','systypes.typeid=stockmoves.type');
INSERT INTO `reportlinks` VALUES ('stockmoves','locations','stockmoves.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockmoves','locations.loccode=stockmoves.loccode');
INSERT INTO `reportlinks` VALUES ('stockmoves','periods','stockmoves.prd=periods.periodno');
INSERT INTO `reportlinks` VALUES ('periods','stockmoves','periods.periodno=stockmoves.prd');
INSERT INTO `reportlinks` VALUES ('stockmovestaxes','taxauthorities','stockmovestaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','stockmovestaxes','taxauthorities.taxid=stockmovestaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockmaster','stockserialitems.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','stockserialitems','stockmaster.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','locations','stockserialitems.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','stockserialitems','locations.loccode=stockserialitems.loccode');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockmoves','stockserialmoves.stockmoveno=stockmoves.stkmoveno');
INSERT INTO `reportlinks` VALUES ('stockmoves','stockserialmoves','stockmoves.stkmoveno=stockserialmoves.stockmoveno');
INSERT INTO `reportlinks` VALUES ('stockserialmoves','stockserialitems','stockserialmoves.stockid=stockserialitems.stockid');
INSERT INTO `reportlinks` VALUES ('stockserialitems','stockserialmoves','stockserialitems.stockid=stockserialmoves.stockid');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocfrom=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocfrom');
INSERT INTO `reportlinks` VALUES ('suppallocs','supptrans','suppallocs.transid_allocto=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','suppallocs','supptrans.id=suppallocs.transid_allocto');
INSERT INTO `reportlinks` VALUES ('suppliercontacts','suppliers','suppliercontacts.supplierid=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','suppliercontacts','suppliers.supplierid=suppliercontacts.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','currencies','suppliers.currcode=currencies.currabrev');
INSERT INTO `reportlinks` VALUES ('currencies','suppliers','currencies.currabrev=suppliers.currcode');
INSERT INTO `reportlinks` VALUES ('suppliers','paymentterms','suppliers.paymentterms=paymentterms.termsindicator');
INSERT INTO `reportlinks` VALUES ('paymentterms','suppliers','paymentterms.termsindicator=suppliers.paymentterms');
INSERT INTO `reportlinks` VALUES ('suppliers','taxgroups','suppliers.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','suppliers','taxgroups.taxgroupid=suppliers.taxgroupid');
INSERT INTO `reportlinks` VALUES ('supptrans','systypes','supptrans.type=systypes.typeid');
INSERT INTO `reportlinks` VALUES ('systypes','supptrans','systypes.typeid=supptrans.type');
INSERT INTO `reportlinks` VALUES ('supptrans','suppliers','supptrans.supplierno=suppliers.supplierid');
INSERT INTO `reportlinks` VALUES ('suppliers','supptrans','suppliers.supplierid=supptrans.supplierno');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','taxauthorities','supptranstaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','supptranstaxes','taxauthorities.taxid=supptranstaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('supptranstaxes','supptrans','supptranstaxes.supptransid=supptrans.id');
INSERT INTO `reportlinks` VALUES ('supptrans','supptranstaxes','supptrans.id=supptranstaxes.supptransid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.taxglcode=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.taxglcode');
INSERT INTO `reportlinks` VALUES ('taxauthorities','chartmaster','taxauthorities.purchtaxglaccount=chartmaster.accountcode');
INSERT INTO `reportlinks` VALUES ('chartmaster','taxauthorities','chartmaster.accountcode=taxauthorities.purchtaxglaccount');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxauthorities','taxauthrates.taxauthority=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxauthrates','taxauthorities.taxid=taxauthrates.taxauthority');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxcategories','taxauthrates.taxcatid=taxcategories.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxcategories','taxauthrates','taxcategories.taxcatid=taxauthrates.taxcatid');
INSERT INTO `reportlinks` VALUES ('taxauthrates','taxprovinces','taxauthrates.dispatchtaxprovince=taxprovinces.taxprovinceid');
INSERT INTO `reportlinks` VALUES ('taxprovinces','taxauthrates','taxprovinces.taxprovinceid=taxauthrates.dispatchtaxprovince');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxgroups','taxgrouptaxes.taxgroupid=taxgroups.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgroups','taxgrouptaxes','taxgroups.taxgroupid=taxgrouptaxes.taxgroupid');
INSERT INTO `reportlinks` VALUES ('taxgrouptaxes','taxauthorities','taxgrouptaxes.taxauthid=taxauthorities.taxid');
INSERT INTO `reportlinks` VALUES ('taxauthorities','taxgrouptaxes','taxauthorities.taxid=taxgrouptaxes.taxauthid');
INSERT INTO `reportlinks` VALUES ('workcentres','locations','workcentres.location=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','workcentres','locations.loccode=workcentres.location');
INSERT INTO `reportlinks` VALUES ('worksorders','locations','worksorders.loccode=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','worksorders','locations.loccode=worksorders.loccode');
INSERT INTO `reportlinks` VALUES ('worksorders','stockmaster','worksorders.stockid=stockmaster.stockid');
INSERT INTO `reportlinks` VALUES ('stockmaster','worksorders','stockmaster.stockid=worksorders.stockid');
INSERT INTO `reportlinks` VALUES ('www_users','locations','www_users.defaultlocation=locations.loccode');
INSERT INTO `reportlinks` VALUES ('locations','www_users','locations.loccode=www_users.defaultlocation');

--
-- Dumping data for table `reports`
--


--
-- Dumping data for table `salesanalysis`
--

INSERT INTO `salesanalysis` VALUES ('EA',1,500,500,'COA001','COA001',50,0,'CHAPATI','CE',1,'IN','FOOD',1);
INSERT INTO `salesanalysis` VALUES ('EA',1,52000,0,'KAM001','KAM001',20,0,'CONSULT','CE',1,'IN','CONSUL',2);

--
-- Dumping data for table `salescat`
--


--
-- Dumping data for table `salescatprod`
--


--
-- Dumping data for table `salesglpostings`
--

INSERT INTO `salesglpostings` VALUES (1,'AN','ANY',4900,4100,'AN');
INSERT INTO `salesglpostings` VALUES (2,'AN','AIRCON',5000,4800,'DE');

--
-- Dumping data for table `salesman`
--

INSERT INTO `salesman` VALUES ('IN','Internet bookings','','',0,0,0,1);

--
-- Dumping data for table `salesorderdetails`
--

INSERT INTO `salesorderdetails` VALUES (0,1,'CHAPATI',0,10,100,0,0,'0000-00-00 00:00:00',0,'','2012-12-15','');
INSERT INTO `salesorderdetails` VALUES (0,2,'CHAPATI',50,10,100,0,0,'2013-01-07 00:00:00',0,'','2012-12-15','');
INSERT INTO `salesorderdetails` VALUES (1,3,'CONSULT',20,70000,75,0,0,'2013-01-12 00:00:00',0,'75 hours of consultancy on design and preparation of web site @ UGX70,000 per hour','2013-01-11','');

--
-- Dumping data for table `salesorders`
--

INSERT INTO `salesorders` VALUES (1,'COA001','COA001','',NULL,'','2012-12-15','EA',1,'Mt Kenya Road','','','Mombasa','','Kenya','','','Coastal Hotels Ltd',1,0,'MSA','2012-12-17','2012-12-17',0,'0000-00-00',0,'2012-12-17',0,'IN');
INSERT INTO `salesorders` VALUES (2,'COA001','COA001','',NULL,' Inv 1','2012-12-15','EA',1,'Mt Kenya Road','','','Mombasa','','Kenya','','','Coastal Hotels Ltd',1,0,'MSA','2012-12-17','2012-12-17',0,'0000-00-00',0,'2012-12-17',0,'IN');
INSERT INTO `salesorders` VALUES (3,'KAM001','KAM001','',NULL,' Inv 2','2013-01-11','EA',1,'Nile Avenue','Kampala','','','','Uganda','','','Kampala Newspapers Incorporated',1,0,'MSA','2013-01-12','2013-01-12',0,'0000-00-00',0,'2013-01-12',0,'IN');

--
-- Dumping data for table `salestypes`
--

INSERT INTO `salestypes` VALUES ('EA','East African Community');

--
-- Dumping data for table `scripts`
--

INSERT INTO `scripts` VALUES ('AccountGroups.php',10,'Defines the groupings of general ledger accounts');
INSERT INTO `scripts` VALUES ('AccountSections.php',10,'Defines the sections in the general ledger reports');
INSERT INTO `scripts` VALUES ('AddCustomerContacts.php',3,'Adds customer contacts');
INSERT INTO `scripts` VALUES ('AddCustomerNotes.php',3,'Adds notes about customers');
INSERT INTO `scripts` VALUES ('AddCustomerTypeNotes.php',3,'');
INSERT INTO `scripts` VALUES ('AgedDebtors.php',2,'Lists customer account balances in detail or summary in selected currency');
INSERT INTO `scripts` VALUES ('AgedSuppliers.php',2,'Lists supplier account balances in detail or summary in selected currency');
INSERT INTO `scripts` VALUES ('Areas.php',3,'Defines the sales areas - all customers must belong to a sales area for the purposes of sales analysis');
INSERT INTO `scripts` VALUES ('AuditTrail.php',15,'Shows the activity with SQL statements and who performed the changes');
INSERT INTO `scripts` VALUES ('BankAccounts.php',10,'Defines the general ledger code for bank accounts and specifies that bank transactions be created for these accounts for the purposes of reconciliation');
INSERT INTO `scripts` VALUES ('BankMatching.php',7,'Allows payments and receipts to be matched off against bank statements');
INSERT INTO `scripts` VALUES ('BankReconciliation.php',7,'Displays the bank reconciliation for a selected bank account');
INSERT INTO `scripts` VALUES ('BOMExtendedQty.php',2,'Shows the component requirements to make an item');
INSERT INTO `scripts` VALUES ('BOMIndented.php',2,'Shows the bill of material indented for each level');
INSERT INTO `scripts` VALUES ('BOMIndentedReverse.php',2,'');
INSERT INTO `scripts` VALUES ('BOMInquiry.php',2,'Displays the bill of material with cost information');
INSERT INTO `scripts` VALUES ('BOMListing.php',2,'Lists the bills of material for a selected range of items');
INSERT INTO `scripts` VALUES ('BOMs.php',9,'Administers the bills of material for a selected item');
INSERT INTO `scripts` VALUES ('COGSGLPostings.php',10,'Defines the general ledger account to be used for cost of sales entries');
INSERT INTO `scripts` VALUES ('CompanyPreferences.php',10,'Defines the settings applicable for the company, including name, address, tax authority reference, whether GL integration used etc.');
INSERT INTO `scripts` VALUES ('ConfirmDispatchControlled_Invoice.php',11,'Specifies the batch references/serial numbers of items dispatched that are being invoiced');
INSERT INTO `scripts` VALUES ('ConfirmDispatch_Invoice.php',2,'Creates sales invoices from entered sales orders based on the quantities dispatched that can be modified');
INSERT INTO `scripts` VALUES ('ContractBOM.php',6,'Creates the item requirements from stock for a contract as part of the contract cost build up');
INSERT INTO `scripts` VALUES ('ContractCosting.php',6,'Shows a contract cost - the components and other non-stock costs issued to the contract');
INSERT INTO `scripts` VALUES ('ContractOtherReqts.php',4,'Creates the other requirements for a contract cost build up');
INSERT INTO `scripts` VALUES ('Contracts.php',6,'Creates or modifies a customer contract costing');
INSERT INTO `scripts` VALUES ('CopyBOM.php',9,'Allows a bill of material to be copied between items');
INSERT INTO `scripts` VALUES ('CounterReturns.php',5,'Allows credits and refunds from the default Counter Sale account for an inventory location');
INSERT INTO `scripts` VALUES ('CounterSales.php',1,'Allows sales to be entered against a cash sale customer account defined in the users location record');
INSERT INTO `scripts` VALUES ('CreditItemsControlled.php',3,'Specifies the batch references/serial numbers of items being credited back into stock');
INSERT INTO `scripts` VALUES ('CreditStatus.php',3,'Defines the credit status records. Each customer account is given a credit status from this table. Some credit status records can prohibit invoicing and new orders being entered.');
INSERT INTO `scripts` VALUES ('Credit_Invoice.php',3,'Creates a credit note based on the details of an existing invoice');
INSERT INTO `scripts` VALUES ('Currencies.php',9,'Defines the currencies available. Each customer and supplier must be defined as transacting in one of the currencies defined here.');
INSERT INTO `scripts` VALUES ('CustEDISetup.php',11,'Allows the set up the customer specified EDI parameters for server, email or ftp.');
INSERT INTO `scripts` VALUES ('CustLoginSetup.php',15,'');
INSERT INTO `scripts` VALUES ('CustomerAllocations.php',3,'Allows customer receipts and credit notes to be allocated to sales invoices');
INSERT INTO `scripts` VALUES ('CustomerBranches.php',3,'Defines the details of customer branches such as delivery address and contact details - also sales area, representative etc');
INSERT INTO `scripts` VALUES ('CustomerInquiry.php',1,'Shows the customers account transactions with balances outstanding, links available to drill down to invoice/credit note or email invoices/credit notes');
INSERT INTO `scripts` VALUES ('CustomerPurchases.php',5,'Shows the purchases a customer has made.');
INSERT INTO `scripts` VALUES ('CustomerReceipt.php',3,'Entry of both customer receipts against accounts receivable and also general ledger or nominal receipts');
INSERT INTO `scripts` VALUES ('Customers.php',3,'Defines the setup of a customer account, including payment terms, billing address, credit status, currency etc');
INSERT INTO `scripts` VALUES ('CustomerTransInquiry.php',2,'Lists in html the sequence of customer transactions, invoices, credit notes or receipts by a user entered date range');
INSERT INTO `scripts` VALUES ('CustomerTypes.php',15,'');
INSERT INTO `scripts` VALUES ('CustWhereAlloc.php',2,'Shows to which invoices a receipt was allocated to');
INSERT INTO `scripts` VALUES ('DailyBankTransactions.php',8,'');
INSERT INTO `scripts` VALUES ('DailySalesInquiry.php',2,'Shows the daily sales with GP in a calendar format');
INSERT INTO `scripts` VALUES ('DebtorsAtPeriodEnd.php',2,'Shows the debtors control account as at a previous period end - based on system calendar monthly periods');
INSERT INTO `scripts` VALUES ('DeliveryDetails.php',1,'Used during order entry to allow the entry of delivery addresses other than the defaulted branch delivery address and information about carrier/shipping method etc');
INSERT INTO `scripts` VALUES ('Departments.php',1,'Create business departments');
INSERT INTO `scripts` VALUES ('DiscountCategories.php',11,'Defines the items belonging to a discount category. Discount Categories are used to allow discounts based on quantities across a range of producs');
INSERT INTO `scripts` VALUES ('DiscountMatrix.php',11,'Defines the rates of discount applicable to discount categories and the customer groupings to which the rates are to apply');
INSERT INTO `scripts` VALUES ('EDIMessageFormat.php',10,'Specifies the EDI message format used by a customer - administrator use only.');
INSERT INTO `scripts` VALUES ('EDIProcessOrders.php',11,'Processes incoming EDI orders into sales orders');
INSERT INTO `scripts` VALUES ('EDISendInvoices.php',15,'Processes invoiced EDI customer invoices into EDI messages and sends using the customers preferred method either ftp or email attachments.');
INSERT INTO `scripts` VALUES ('EmailConfirmation.php',2,'');
INSERT INTO `scripts` VALUES ('EmailCustTrans.php',2,'Emails selected invoice or credit to the customer');
INSERT INTO `scripts` VALUES ('ExchangeRateTrend.php',2,'Shows the trend in exchange rates as retrieved from ECB');
INSERT INTO `scripts` VALUES ('Factors.php',5,'Defines supplier factor companies');
INSERT INTO `scripts` VALUES ('FixedAssetCategories.php',11,'Defines the various categories of fixed assets');
INSERT INTO `scripts` VALUES ('FixedAssetDepreciation.php',10,'Calculates and creates GL transactions to post depreciation for a period');
INSERT INTO `scripts` VALUES ('FixedAssetItems.php',11,'Allows fixed assets to be defined');
INSERT INTO `scripts` VALUES ('FixedAssetList.php',11,'');
INSERT INTO `scripts` VALUES ('FixedAssetLocations.php',11,'Allows the locations of fixed assets to be defined');
INSERT INTO `scripts` VALUES ('FixedAssetRegister.php',11,'Produces a csv, html or pdf report of the fixed assets over a period showing period depreciation, additions and disposals');
INSERT INTO `scripts` VALUES ('FixedAssetTransfer.php',11,'Allows the fixed asset locations to be changed in bulk');
INSERT INTO `scripts` VALUES ('FormDesigner.php',14,'');
INSERT INTO `scripts` VALUES ('FormMaker.php',1,'Allows running user defined Forms');
INSERT INTO `scripts` VALUES ('FreightCosts.php',11,'Defines the setup of the freight cost using different shipping methods to different destinations. The system can use this information to calculate applicable freight if the items are defined with the correct kgs and cubic volume');
INSERT INTO `scripts` VALUES ('FTP_RadioBeacon.php',2,'FTPs sales orders for dispatch to a radio beacon software enabled warehouse dispatching facility');
INSERT INTO `scripts` VALUES ('geocode.php',3,'');
INSERT INTO `scripts` VALUES ('GeocodeSetup.php',3,'');
INSERT INTO `scripts` VALUES ('geocode_genxml_customers.php',3,'');
INSERT INTO `scripts` VALUES ('geocode_genxml_suppliers.php',3,'');
INSERT INTO `scripts` VALUES ('geo_displaymap_customers.php',3,'');
INSERT INTO `scripts` VALUES ('geo_displaymap_suppliers.php',3,'');
INSERT INTO `scripts` VALUES ('GetStockImage.php',1,'');
INSERT INTO `scripts` VALUES ('GLAccountCSV.php',8,'Produces a CSV of the GL transactions for a particular range of periods and GL account');
INSERT INTO `scripts` VALUES ('GLAccountInquiry.php',8,'Shows the general ledger transactions for a specified account over a specified range of periods');
INSERT INTO `scripts` VALUES ('GLAccountReport.php',8,'Produces a report of the GL transactions for a particular account');
INSERT INTO `scripts` VALUES ('GLAccounts.php',10,'Defines the general ledger accounts');
INSERT INTO `scripts` VALUES ('GLBalanceSheet.php',8,'Shows the balance sheet for the company as at a specified date');
INSERT INTO `scripts` VALUES ('GLBudgets.php',10,'Defines GL Budgets');
INSERT INTO `scripts` VALUES ('GLCodesInquiry.php',8,'Shows the list of general ledger codes defined with account names and groupings');
INSERT INTO `scripts` VALUES ('GLJournal.php',10,'Entry of general ledger journals, periods are calculated based on the date entered here');
INSERT INTO `scripts` VALUES ('GLJournalInquiry.php',15,'General Ledger Journal Inquiry');
INSERT INTO `scripts` VALUES ('GLProfit_Loss.php',8,'Shows the profit and loss of the company for the range of periods entered');
INSERT INTO `scripts` VALUES ('GLTagProfit_Loss.php',8,'');
INSERT INTO `scripts` VALUES ('GLTags.php',10,'Allows GL tags to be defined');
INSERT INTO `scripts` VALUES ('GLTransInquiry.php',8,'Shows the general ledger journal created for the sub ledger transaction specified');
INSERT INTO `scripts` VALUES ('GLTrialBalance.php',8,'Shows the trial balance for the month and the for the period selected together with the budgeted trial balances');
INSERT INTO `scripts` VALUES ('GLTrialBalance_csv.php',8,'Produces a CSV of the Trial Balance for a particular period');
INSERT INTO `scripts` VALUES ('GoodsReceived.php',11,'Entry of items received against purchase orders');
INSERT INTO `scripts` VALUES ('GoodsReceivedButNotInvoiced.php',2,'Shows the list of Goods Received Not Yet Invoiced, both in supplier currency and home currency. Total in home curency should match the GL Account for Goods received not invoiced. Any discrepancy is due to multicurrency errors.');
INSERT INTO `scripts` VALUES ('GoodsReceivedControlled.php',11,'Entry of the serial numbers or batch references for controlled items received against purchase orders');
INSERT INTO `scripts` VALUES ('index.php',1,'The main menu from where all functions available to the user are accessed by clicking on the links');
INSERT INTO `scripts` VALUES ('InternalStockCategoriesByRole.php',15,'Maintains the stock categories to be used as internal for any user security role');
INSERT INTO `scripts` VALUES ('InternalStockRequest.php',1,'Create an internal stock request');
INSERT INTO `scripts` VALUES ('InternalStockRequestAuthorisation.php',1,'Authorise internal stock requests');
INSERT INTO `scripts` VALUES ('InternalStockRequestFulfill.php',1,'Fulfill an internal stock request');
INSERT INTO `scripts` VALUES ('InventoryPlanning.php',2,'Creates a pdf report showing the last 4 months use of items including as a component of assemblies together with stock quantity on hand, current demand for the item and current quantity on sales order.');
INSERT INTO `scripts` VALUES ('InventoryPlanningPrefSupplier.php',2,'Produces a report showing the inventory to be ordered by supplier');
INSERT INTO `scripts` VALUES ('InventoryQuantities.php',2,'');
INSERT INTO `scripts` VALUES ('InventoryValuation.php',2,'Creates a pdf report showing the value of stock at standard cost for a range of product categories selected');
INSERT INTO `scripts` VALUES ('ItemsWithoutPicture.php',15,'Shows the list of curent items without picture in KwaMoja');
INSERT INTO `scripts` VALUES ('Labels.php',15,'Produces item pricing labels in a pdf from a range of selected criteria');
INSERT INTO `scripts` VALUES ('Locations.php',11,'Defines the inventory stocking locations or warehouses');
INSERT INTO `scripts` VALUES ('Logout.php',1,'Shows when the user logs out of KwaMoja');
INSERT INTO `scripts` VALUES ('MailInventoryValuation.php',1,'Meant to be run as a scheduled process to email the stock valuation off to a specified person. Creates the same stock valuation report as InventoryValuation.php');
INSERT INTO `scripts` VALUES ('ManualContents.php',1,'');
INSERT INTO `scripts` VALUES ('MenuAccess.php',15,'');
INSERT INTO `scripts` VALUES ('MRP.php',9,'');
INSERT INTO `scripts` VALUES ('MRPCalendar.php',9,'');
INSERT INTO `scripts` VALUES ('MRPCreateDemands.php',9,'');
INSERT INTO `scripts` VALUES ('MRPDemands.php',9,'');
INSERT INTO `scripts` VALUES ('MRPDemandTypes.php',9,'');
INSERT INTO `scripts` VALUES ('MRPPlannedPurchaseOrders.php',2,'');
INSERT INTO `scripts` VALUES ('MRPPlannedWorkOrders.php',2,'');
INSERT INTO `scripts` VALUES ('MRPReport.php',2,'');
INSERT INTO `scripts` VALUES ('MRPReschedules.php',2,'');
INSERT INTO `scripts` VALUES ('MRPShortages.php',2,'');
INSERT INTO `scripts` VALUES ('NoSalesItems.php',2,'Shows the No Selling (worst) items');
INSERT INTO `scripts` VALUES ('OffersReceived.php',4,'');
INSERT INTO `scripts` VALUES ('OrderDetails.php',2,'Shows the detail of a sales order');
INSERT INTO `scripts` VALUES ('OutstandingGRNs.php',2,'Creates a pdf showing all GRNs for which there has been no purchase invoice matched off against.');
INSERT INTO `scripts` VALUES ('PageSecurity.php',15,'');
INSERT INTO `scripts` VALUES ('PaymentAllocations.php',5,'');
INSERT INTO `scripts` VALUES ('PaymentMethods.php',15,'');
INSERT INTO `scripts` VALUES ('Payments.php',5,'Entry of bank account payments either against an AP account or a general ledger payment - if the AP-GL link in company preferences is set');
INSERT INTO `scripts` VALUES ('PaymentTerms.php',10,'Defines the payment terms records, these can be expressed as either a number of days credit or a day in the following month. All customers and suppliers must have a corresponding payment term recorded against their account');
INSERT INTO `scripts` VALUES ('PcAssignCashToTab.php',6,'');
INSERT INTO `scripts` VALUES ('PcAuthorizeExpenses.php',6,'');
INSERT INTO `scripts` VALUES ('PcClaimExpensesFromTab.php',6,'');
INSERT INTO `scripts` VALUES ('PcExpenses.php',15,'');
INSERT INTO `scripts` VALUES ('PcExpensesTypeTab.php',15,'');
INSERT INTO `scripts` VALUES ('PcReportTab.php',6,'');
INSERT INTO `scripts` VALUES ('PcTabs.php',15,'');
INSERT INTO `scripts` VALUES ('PcTypeTabs.php',15,'');
INSERT INTO `scripts` VALUES ('PDFBankingSummary.php',3,'Creates a pdf showing the amounts entered as receipts on a specified date together with references for the purposes of banking');
INSERT INTO `scripts` VALUES ('PDFChequeListing.php',3,'Creates a pdf showing all payments that have been made from a specified bank account over a specified period. This can be emailed to an email account defined in config.php - ie a financial controller');
INSERT INTO `scripts` VALUES ('PDFCustomerList.php',2,'Creates a report of the customer and branch information held. This report has options to print only customer branches in a specified sales area and sales person. Additional option allows to list only those customers with activity either under or over a specified amount, since a specified date.');
INSERT INTO `scripts` VALUES ('PDFCustTransListing.php',3,'');
INSERT INTO `scripts` VALUES ('PDFDeliveryDifferences.php',3,'Creates a pdf report listing the delivery differences from what the customer requested as recorded in the order entry. The report calculates a percentage of order fill based on the number of orders filled in full on time');
INSERT INTO `scripts` VALUES ('PDFDIFOT.php',3,'Produces a pdf showing the delivery in full on time performance');
INSERT INTO `scripts` VALUES ('PDFGLJournal.php',15,'General Ledger Journal Print');
INSERT INTO `scripts` VALUES ('PDFGrn.php',2,'Produces a GRN report on the receipt of stock');
INSERT INTO `scripts` VALUES ('PDFLowGP.php',2,'Creates a pdf report showing the low gross profit sales made in the selected date range. The percentage of gp deemed acceptable can also be entered');
INSERT INTO `scripts` VALUES ('PDFOrdersInvoiced.php',3,'Produces a pdf of orders invoiced based on selected criteria');
INSERT INTO `scripts` VALUES ('PDFOrderStatus.php',3,'Reports on sales order status by date range, by stock location and stock category - producing a pdf showing each line items and any quantites delivered');
INSERT INTO `scripts` VALUES ('PDFPeriodStockTransListing.php',3,'Allows stock transactions of a specific transaction type to be listed over a single day or period range');
INSERT INTO `scripts` VALUES ('PDFPickingList.php',2,'');
INSERT INTO `scripts` VALUES ('PDFPriceList.php',2,'Creates a pdf of the price list applicable to a given sales type and customer. Also allows the listing of prices specific to a customer');
INSERT INTO `scripts` VALUES ('PDFPrintLabel.php',10,'');
INSERT INTO `scripts` VALUES ('PDFQuotation.php',2,'');
INSERT INTO `scripts` VALUES ('PDFQuotationPortrait.php',2,'Portrait quotation');
INSERT INTO `scripts` VALUES ('PDFReceipt.php',2,'');
INSERT INTO `scripts` VALUES ('PDFRemittanceAdvice.php',2,'');
INSERT INTO `scripts` VALUES ('PDFStockCheckComparison.php',2,'Creates a pdf comparing the quantites entered as counted at a given range of locations against the quantity stored as on hand as at the time a stock check was initiated.');
INSERT INTO `scripts` VALUES ('PDFStockLocTransfer.php',1,'Creates a stock location transfer docket for the selected location transfer reference number');
INSERT INTO `scripts` VALUES ('PDFStockNegatives.php',1,'Produces a pdf of the negative stocks by location');
INSERT INTO `scripts` VALUES ('PDFStockTransfer.php',2,'Produces a report for stock transfers');
INSERT INTO `scripts` VALUES ('PDFSuppTransListing.php',3,'');
INSERT INTO `scripts` VALUES ('PDFTopItems.php',2,'Produces a pdf report of the top items sold');
INSERT INTO `scripts` VALUES ('PeriodsInquiry.php',2,'Shows a list of all the system defined periods');
INSERT INTO `scripts` VALUES ('POReport.php',2,'');
INSERT INTO `scripts` VALUES ('PO_AuthorisationLevels.php',15,'');
INSERT INTO `scripts` VALUES ('PO_AuthoriseMyOrders.php',4,'');
INSERT INTO `scripts` VALUES ('PO_Header.php',4,'Entry of a purchase order header record - date, references buyer etc');
INSERT INTO `scripts` VALUES ('PO_Items.php',4,'Entry of a purchase order items - allows entry of items with lookup of currency cost from Purchasing Data previously entered also allows entry of nominal items against a general ledger code if the AP is integrated to the GL');
INSERT INTO `scripts` VALUES ('PO_OrderDetails.php',2,'Purchase order inquiry shows the quantity received and invoiced of purchase order items as well as the header information');
INSERT INTO `scripts` VALUES ('PO_PDFPurchOrder.php',2,'Creates a pdf of the selected purchase order for printing or email to one of the supplier contacts entered');
INSERT INTO `scripts` VALUES ('PO_SelectOSPurchOrder.php',2,'Shows the outstanding purchase orders for selecting with links to receive or modify the purchase order header and items');
INSERT INTO `scripts` VALUES ('PO_SelectPurchOrder.php',2,'Allows selection of any purchase order with links to the inquiry');
INSERT INTO `scripts` VALUES ('Prices.php',9,'Entry of prices for a selected item also allows selection of sales type and currency for the price');
INSERT INTO `scripts` VALUES ('PricesBasedOnMarkUp.php',11,'');
INSERT INTO `scripts` VALUES ('PricesByCost.php',11,'Allows prices to be updated based on cost');
INSERT INTO `scripts` VALUES ('Prices_Customer.php',11,'Entry of prices for a selected item and selected customer/branch. The currency and sales type is defaulted from the customer\'s record');
INSERT INTO `scripts` VALUES ('PrintCheque.php',5,'');
INSERT INTO `scripts` VALUES ('PrintCustOrder.php',2,'Creates a pdf of the dispatch note - by default this is expected to be on two part pre-printed stationery to allow pickers to note discrepancies for the confirmer to update the dispatch at the time of invoicing');
INSERT INTO `scripts` VALUES ('PrintCustOrder_generic.php',2,'Creates two copies of a laser printed dispatch note - both copies need to be written on by the pickers with any discrepancies to advise customer of any shortfall and on the office copy to ensure the correct quantites are invoiced');
INSERT INTO `scripts` VALUES ('PrintCustStatements.php',2,'Creates a pdf for the customer statements in the selected range');
INSERT INTO `scripts` VALUES ('PrintCustTrans.php',1,'Creates either a html invoice or credit note or a pdf. A range of invoices or credit notes can be selected also.');
INSERT INTO `scripts` VALUES ('PrintCustTransPortrait.php',1,'');
INSERT INTO `scripts` VALUES ('PrintSalesOrder_generic.php',2,'');
INSERT INTO `scripts` VALUES ('PurchData.php',4,'Entry of supplier purchasing data, the suppliers part reference and the suppliers currency cost of the item');
INSERT INTO `scripts` VALUES ('RecurringSalesOrders.php',1,'');
INSERT INTO `scripts` VALUES ('RecurringSalesOrdersProcess.php',1,'Process Recurring Sales Orders');
INSERT INTO `scripts` VALUES ('ReorderLevel.php',2,'Allows reorder levels of inventory to be updated');
INSERT INTO `scripts` VALUES ('ReorderLevelLocation.php',2,'');
INSERT INTO `scripts` VALUES ('ReportBug.php',15,'');
INSERT INTO `scripts` VALUES ('ReportCreator.php',13,'Report Writer and Form Creator script that creates templates for user defined reports and forms');
INSERT INTO `scripts` VALUES ('ReportletContainer.php',1,'');
INSERT INTO `scripts` VALUES ('ReportMaker.php',1,'Produces reports from the report writer templates created');
INSERT INTO `scripts` VALUES ('reportwriter/admin/ReportCreator.php',15,'Report Writer');
INSERT INTO `scripts` VALUES ('ReprintGRN.php',11,'Allows selection of a goods received batch for reprinting the goods received note given a purchase order number');
INSERT INTO `scripts` VALUES ('ReverseGRN.php',11,'Reverses the entry of goods received - creating stock movements back out and necessary general ledger journals to effect the reversal');
INSERT INTO `scripts` VALUES ('SalesAnalReptCols.php',2,'Entry of the definition of a sales analysis report\'s columns.');
INSERT INTO `scripts` VALUES ('SalesAnalRepts.php',2,'Entry of the definition of a sales analysis report headers');
INSERT INTO `scripts` VALUES ('SalesAnalysis_UserDefined.php',2,'Creates a pdf of a selected user defined sales analysis report');
INSERT INTO `scripts` VALUES ('SalesByTypePeriodInquiry.php',2,'Shows sales for a selected date range by sales type/price list');
INSERT INTO `scripts` VALUES ('SalesCategories.php',11,'');
INSERT INTO `scripts` VALUES ('SalesCategoryPeriodInquiry.php',2,'Shows sales for a selected date range by stock category');
INSERT INTO `scripts` VALUES ('SalesGLPostings.php',10,'Defines the general ledger accounts used to post sales to based on product categories and sales areas');
INSERT INTO `scripts` VALUES ('SalesGraph.php',6,'');
INSERT INTO `scripts` VALUES ('SalesInquiry.php',2,'');
INSERT INTO `scripts` VALUES ('SalesPeople.php',3,'Defines the sales people of the business');
INSERT INTO `scripts` VALUES ('SalesTopItemsInquiry.php',2,'Shows the top item sales for a selected date range');
INSERT INTO `scripts` VALUES ('SalesTypes.php',15,'Defines the sales types - prices are held against sales types they can be considered price lists. Sales analysis records are held by sales type too.');
INSERT INTO `scripts` VALUES ('SecurityTokens.php',15,'Administration of security tokens');
INSERT INTO `scripts` VALUES ('SelectAsset.php',2,'Allows a fixed asset to be selected for modification or viewing');
INSERT INTO `scripts` VALUES ('SelectCompletedOrder.php',1,'Allows the selection of completed sales orders for inquiries - choices to select by item code or customer');
INSERT INTO `scripts` VALUES ('SelectContract.php',6,'Allows a contract costing to be selected for modification or viewing');
INSERT INTO `scripts` VALUES ('SelectCreditItems.php',3,'Entry of credit notes from scratch, selecting the items in either quick entry mode or searching for them manually');
INSERT INTO `scripts` VALUES ('SelectCustomer.php',2,'Selection of customer - from where all customer related maintenance, transactions and inquiries start');
INSERT INTO `scripts` VALUES ('SelectGLAccount.php',8,'Selection of general ledger account from where all general ledger account maintenance, or inquiries are initiated');
INSERT INTO `scripts` VALUES ('SelectOrderItems.php',1,'Entry of sales order items with both quick entry and part search functions');
INSERT INTO `scripts` VALUES ('SelectProduct.php',2,'Selection of items. All item maintenance, transactions and inquiries start with this script');
INSERT INTO `scripts` VALUES ('SelectRecurringSalesOrder.php',2,'');
INSERT INTO `scripts` VALUES ('SelectSalesOrder.php',2,'Selects a sales order irrespective of completed or not for inquiries');
INSERT INTO `scripts` VALUES ('SelectSupplier.php',2,'Selects a supplier. A supplier is required to be selected before any AP transactions and before any maintenance or inquiry of the supplier');
INSERT INTO `scripts` VALUES ('SelectWorkOrder.php',2,'');
INSERT INTO `scripts` VALUES ('ShipmentCosting.php',11,'Shows the costing of a shipment with all the items invoice values and any shipment costs apportioned. Updating the shipment has an option to update standard costs of all items on the shipment and create any general ledger variance journals');
INSERT INTO `scripts` VALUES ('Shipments.php',11,'Entry of shipments from outstanding purchase orders for a selected supplier - changes in the delivery date will cascade into the different purchase orders on the shipment');
INSERT INTO `scripts` VALUES ('Shippers.php',15,'Defines the shipping methods available. Each customer branch has a default shipping method associated with it which must match a record from this table');
INSERT INTO `scripts` VALUES ('ShiptsList.php',2,'Shows a list of all the open shipments for a selected supplier. Linked from POItems.php');
INSERT INTO `scripts` VALUES ('Shipt_Select.php',11,'Selection of a shipment for displaying and modification or updating');
INSERT INTO `scripts` VALUES ('SMTPServer.php',15,'');
INSERT INTO `scripts` VALUES ('SpecialOrder.php',4,'Allows for a sales order to be created and an indent order to be created on a supplier for a one off item that may never be purchased again. A dummy part is created based on the description and cost details given.');
INSERT INTO `scripts` VALUES ('StockAdjustments.php',11,'Entry of quantity corrections to stocks in a selected location.');
INSERT INTO `scripts` VALUES ('StockAdjustmentsControlled.php',11,'Entry of batch references or serial numbers on controlled stock items being adjusted');
INSERT INTO `scripts` VALUES ('StockCategories.php',11,'Defines the stock categories. All items must refer to one of these categories. The category record also allows the specification of the general ledger codes where stock items are to be posted - the balance sheet account and the profit and loss effect of any adjustments and the profit and loss effect of any price variances');
INSERT INTO `scripts` VALUES ('StockCheck.php',2,'Allows creation of a stock check file - copying the current quantites in stock for later comparison to the entered counts. Also produces a pdf for the count sheets.');
INSERT INTO `scripts` VALUES ('StockCostUpdate.php',9,'Allows update of the standard cost of items producing general ledger journals if the company preferences stock GL interface is active');
INSERT INTO `scripts` VALUES ('StockCounts.php',2,'Allows entry of stock counts');
INSERT INTO `scripts` VALUES ('StockDispatch.php',2,'');
INSERT INTO `scripts` VALUES ('StockLocMovements.php',2,'Inquiry shows the Movements of all stock items for a specified location');
INSERT INTO `scripts` VALUES ('StockLocStatus.php',2,'Shows the stock on hand together with outstanding sales orders and outstanding purchase orders by stock location for all items in the selected stock category');
INSERT INTO `scripts` VALUES ('StockLocTransfer.php',11,'Entry of a bulk stock location transfer for many parts from one location to another.');
INSERT INTO `scripts` VALUES ('StockLocTransferReceive.php',11,'Effects the transfer and creates the stock movements for a bulk stock location transfer initiated from StockLocTransfer.php');
INSERT INTO `scripts` VALUES ('StockMovements.php',2,'Shows a list of all the stock movements for a selected item and stock location including the price at which they were sold in local currency and the price at which they were purchased for in local currency');
INSERT INTO `scripts` VALUES ('StockQties_csv.php',5,'Makes a comma separated values (CSV)file of the stock item codes and quantities');
INSERT INTO `scripts` VALUES ('StockQuantityByDate.php',2,'Shows the stock on hand for each item at a selected location and stock category as at a specified date');
INSERT INTO `scripts` VALUES ('StockReorderLevel.php',4,'Entry and review of the re-order level of items by stocking location');
INSERT INTO `scripts` VALUES ('Stocks.php',11,'Defines an item - maintenance and addition of new parts');
INSERT INTO `scripts` VALUES ('StockSerialItemResearch.php',3,'');
INSERT INTO `scripts` VALUES ('StockSerialItems.php',2,'Shows a list of the serial numbers or the batch references and quantities of controlled items. This inquiry is linked from the stock status inquiry');
INSERT INTO `scripts` VALUES ('StockStatus.php',2,'Shows the stock on hand together with outstanding sales orders and outstanding purchase orders by stock location for a selected part. Has a link to show the serial numbers in stock at the location selected if the item is controlled');
INSERT INTO `scripts` VALUES ('StockTransferControlled.php',11,'Entry of serial numbers/batch references for controlled items being received on a stock transfer. The script is used by both bulk transfers and point to point transfers');
INSERT INTO `scripts` VALUES ('StockTransfers.php',11,'Entry of point to point stock location transfers of a single part');
INSERT INTO `scripts` VALUES ('StockUsage.php',2,'Inquiry showing the quantity of stock used by period calculated from the sum of the stock movements over that period - by item and stock location. Also available over all locations');
INSERT INTO `scripts` VALUES ('StockUsageGraph.php',2,'');
INSERT INTO `scripts` VALUES ('SuppContractChgs.php',5,'');
INSERT INTO `scripts` VALUES ('SuppCreditGRNs.php',5,'Entry of a supplier credit notes (debit notes) against existing GRN which have already been matched in full or in part');
INSERT INTO `scripts` VALUES ('SuppFixedAssetChgs.php',5,'');
INSERT INTO `scripts` VALUES ('SuppInvGRNs.php',5,'Entry of supplier invoices against goods received');
INSERT INTO `scripts` VALUES ('SupplierAllocations.php',5,'Entry of allocations of supplier payments and credit notes to invoices');
INSERT INTO `scripts` VALUES ('SupplierBalsAtPeriodEnd.php',2,'');
INSERT INTO `scripts` VALUES ('SupplierContacts.php',5,'Entry of supplier contacts and contact details including email addresses');
INSERT INTO `scripts` VALUES ('SupplierCredit.php',5,'Entry of supplier credit notes (debit notes)');
INSERT INTO `scripts` VALUES ('SupplierInquiry.php',2,'Inquiry showing invoices, credit notes and payments made to suppliers together with the amounts outstanding');
INSERT INTO `scripts` VALUES ('SupplierInvoice.php',5,'Entry of supplier invoices');
INSERT INTO `scripts` VALUES ('SupplierPriceList.php',4,'Maintain Supplier Price Lists');
INSERT INTO `scripts` VALUES ('Suppliers.php',5,'Entry of new suppliers and maintenance of existing suppliers');
INSERT INTO `scripts` VALUES ('SupplierTenderCreate.php',4,'Create or Edit tenders');
INSERT INTO `scripts` VALUES ('SupplierTenders.php',9,'');
INSERT INTO `scripts` VALUES ('SupplierTransInquiry.php',2,'');
INSERT INTO `scripts` VALUES ('SupplierTypes.php',4,'');
INSERT INTO `scripts` VALUES ('SuppLoginSetup.php',15,'');
INSERT INTO `scripts` VALUES ('SuppPaymentRun.php',5,'Automatic creation of payment records based on calculated amounts due from AP invoices entered');
INSERT INTO `scripts` VALUES ('SuppPriceList.php',2,'');
INSERT INTO `scripts` VALUES ('SuppShiptChgs.php',5,'Entry of supplier invoices against shipments as charges against a shipment');
INSERT INTO `scripts` VALUES ('SuppTransGLAnalysis.php',5,'Entry of supplier invoices against general ledger codes');
INSERT INTO `scripts` VALUES ('SystemCheck.php',10,'');
INSERT INTO `scripts` VALUES ('SystemParameters.php',15,'');
INSERT INTO `scripts` VALUES ('Tax.php',2,'Creates a report of the ad-valoerm tax - GST/VAT - for the period selected from accounts payable and accounts receivable data');
INSERT INTO `scripts` VALUES ('TaxAuthorities.php',15,'Entry of tax authorities - the state intitutions that charge tax');
INSERT INTO `scripts` VALUES ('TaxAuthorityRates.php',11,'Entry of the rates of tax applicable to the tax authority depending on the item tax level');
INSERT INTO `scripts` VALUES ('TaxCategories.php',15,'Allows for categories of items to be defined that might have different tax rates applied to them');
INSERT INTO `scripts` VALUES ('TaxGroups.php',15,'Allows for taxes to be grouped together where multiple taxes might apply on sale or purchase of items');
INSERT INTO `scripts` VALUES ('TaxProvinces.php',15,'Allows for inventory locations to be defined so that tax applicable from sales in different provinces can be dealt with');
INSERT INTO `scripts` VALUES ('TopItems.php',2,'Shows the top selling items');
INSERT INTO `scripts` VALUES ('UnitsOfMeasure.php',15,'Allows for units of measure to be defined');
INSERT INTO `scripts` VALUES ('UpgradeDatabase.php',15,'Allows for the database to be automatically upgraded based on currently recorded DBUpgradeNumber config option');
INSERT INTO `scripts` VALUES ('UserSettings.php',0,'Allows the user to change system wide defaults for the theme - appearance, the number of records to show in searches and the language to display messages in');
INSERT INTO `scripts` VALUES ('WhereUsedInquiry.php',2,'Inquiry showing where an item is used ie all the parents where the item is a component of');
INSERT INTO `scripts` VALUES ('WorkCentres.php',9,'Defines the various centres of work within a manufacturing company. Also the overhead and labour rates applicable to the work centre and its standard capacity');
INSERT INTO `scripts` VALUES ('WorkOrderCosting.php',11,'');
INSERT INTO `scripts` VALUES ('WorkOrderEntry.php',10,'Entry of new work orders');
INSERT INTO `scripts` VALUES ('WorkOrderIssue.php',11,'Issue of materials to a work order');
INSERT INTO `scripts` VALUES ('WorkOrderReceive.php',11,'Allows for receiving of works orders');
INSERT INTO `scripts` VALUES ('WorkOrderStatus.php',11,'Shows the status of works orders');
INSERT INTO `scripts` VALUES ('WOSerialNos.php',10,'');
INSERT INTO `scripts` VALUES ('WWW_Access.php',15,'');
INSERT INTO `scripts` VALUES ('WWW_Users.php',15,'Entry of users and security settings of users');
INSERT INTO `scripts` VALUES ('Z_BottomUpCosts.php',15,'');
INSERT INTO `scripts` VALUES ('Z_ChangeBranchCode.php',15,'Utility to change the branch code of a customer that cascades the change through all the necessary tables');
INSERT INTO `scripts` VALUES ('Z_ChangeCustomerCode.php',15,'Utility to change a customer code that cascades the change through all the necessary tables');
INSERT INTO `scripts` VALUES ('Z_ChangeLocationCode.php',15,'Change a locations code and in all tables where the old code was used to the new code');
INSERT INTO `scripts` VALUES ('Z_ChangeStockCategory.php',15,'');
INSERT INTO `scripts` VALUES ('Z_ChangeStockCode.php',15,'Utility to change an item code that cascades the change through all the necessary tables');
INSERT INTO `scripts` VALUES ('Z_ChangeSupplierCode.php',15,'Script to change a supplier code accross all tables necessary');
INSERT INTO `scripts` VALUES ('Z_CheckAllocationsFrom.php',15,'');
INSERT INTO `scripts` VALUES ('Z_CheckAllocs.php',2,'');
INSERT INTO `scripts` VALUES ('Z_CheckDebtorsControl.php',15,'Inquiry that shows the total local currency (functional currency) balance of all customer accounts to reconcile with the general ledger debtors account');
INSERT INTO `scripts` VALUES ('Z_CheckGLTransBalance.php',15,'Checks all GL transactions balance and reports problem ones');
INSERT INTO `scripts` VALUES ('Z_CreateChartDetails.php',9,'Utility page to create chart detail records for all general ledger accounts and periods created - needs expert assistance in use');
INSERT INTO `scripts` VALUES ('Z_CreateCompany.php',15,'Utility to insert company number 1 if not already there - actually only company 1 is used - the system is not multi-company');
INSERT INTO `scripts` VALUES ('Z_CreateCompanyTemplateFile.php',15,'');
INSERT INTO `scripts` VALUES ('Z_CurrencyDebtorsBalances.php',15,'Inquiry that shows the total foreign currency together with the total local currency (functional currency) balances of all customer accounts to reconcile with the general ledger debtors account');
INSERT INTO `scripts` VALUES ('Z_CurrencySuppliersBalances.php',15,'Inquiry that shows the total foreign currency amounts and also the local currency (functional currency) balances of all supplier accounts to reconcile with the general ledger creditors account');
INSERT INTO `scripts` VALUES ('Z_DataExport.php',15,'');
INSERT INTO `scripts` VALUES ('Z_DeleteCreditNote.php',15,'Utility to reverse a customer credit note - a desperate measure that should not be used except in extreme circumstances');
INSERT INTO `scripts` VALUES ('Z_DeleteInvoice.php',15,'Utility to reverse a customer invoice - a desperate measure that should not be used except in extreme circumstances');
INSERT INTO `scripts` VALUES ('Z_DeleteOldPrices.php',15,'Deletes all old prices');
INSERT INTO `scripts` VALUES ('Z_DeleteSalesTransActions.php',15,'Utility to delete all sales transactions, sales analysis the lot! Extreme care required!!!');
INSERT INTO `scripts` VALUES ('Z_DescribeTable.php',11,'');
INSERT INTO `scripts` VALUES ('Z_ImportChartOfAccounts.php',11,'');
INSERT INTO `scripts` VALUES ('Z_ImportFixedAssets.php',15,'Allow fixed assets to be imported from a csv');
INSERT INTO `scripts` VALUES ('Z_ImportGLAccountGroups.php',11,'');
INSERT INTO `scripts` VALUES ('Z_ImportGLAccountSections.php',11,'');
INSERT INTO `scripts` VALUES ('Z_ImportPartCodes.php',11,'Allows inventory items to be imported from a csv');
INSERT INTO `scripts` VALUES ('Z_ImportStocks.php',15,'');
INSERT INTO `scripts` VALUES ('Z_index.php',15,'Utility menu page');
INSERT INTO `scripts` VALUES ('Z_ItemsWithoutPicture.php',15,'Shows the list of curent items without picture in webERP');
INSERT INTO `scripts` VALUES ('Z_MakeNewCompany.php',15,'');
INSERT INTO `scripts` VALUES ('Z_MakeStockLocns.php',15,'Utility to make LocStock records for all items and locations if not already set up.');
INSERT INTO `scripts` VALUES ('Z_poAddLanguage.php',15,'Allows a new language po file to be created');
INSERT INTO `scripts` VALUES ('Z_poAdmin.php',15,'Allows for a gettext language po file to be administered');
INSERT INTO `scripts` VALUES ('Z_poEditLangHeader.php',15,'');
INSERT INTO `scripts` VALUES ('Z_poEditLangModule.php',15,'');
INSERT INTO `scripts` VALUES ('Z_poEditLangRemaining.php',15,'');
INSERT INTO `scripts` VALUES ('Z_poRebuildDefault.php',15,'');
INSERT INTO `scripts` VALUES ('Z_PriceChanges.php',15,'Utility to make bulk pricing alterations to selected sales type price lists or selected customer prices only');
INSERT INTO `scripts` VALUES ('Z_ReApplyCostToSA.php',15,'Utility to allow the sales analysis table to be updated with the latest cost information - the sales analysis takes the cost at the time the sale was made to reconcile with the enteries made in the gl.');
INSERT INTO `scripts` VALUES ('Z_RePostGLFromPeriod.php',15,'Utility to repost all general ledger transaction commencing from a specified period. This can take some time in busy environments. Normally GL transactions are posted automatically each time a trial balance or profit and loss account is run');
INSERT INTO `scripts` VALUES ('Z_ReverseSuppPaymentRun.php',15,'Utility to reverse an entire Supplier payment run');
INSERT INTO `scripts` VALUES ('Z_SalesIntegrityCheck.php',15,'');
INSERT INTO `scripts` VALUES ('Z_UpdateChartDetailsBFwd.php',15,'Utility to recalculate the ChartDetails table B/Fwd balances - extreme care!!');
INSERT INTO `scripts` VALUES ('Z_Upgrade3.10.php',15,'');
INSERT INTO `scripts` VALUES ('Z_Upgrade_3.01-3.02.php',15,'');
INSERT INTO `scripts` VALUES ('Z_Upgrade_3.04-3.05.php',15,'');
INSERT INTO `scripts` VALUES ('Z_Upgrade_3.05-3.06.php',15,'');
INSERT INTO `scripts` VALUES ('Z_Upgrade_3.07-3.08.php',15,'');
INSERT INTO `scripts` VALUES ('Z_Upgrade_3.08-3.09.php',15,'');
INSERT INTO `scripts` VALUES ('Z_Upgrade_3.09-3.10.php',15,'');
INSERT INTO `scripts` VALUES ('Z_Upgrade_3.10-3.11.php',15,'');
INSERT INTO `scripts` VALUES ('Z_Upgrade_3.11-4.00.php',15,'');
INSERT INTO `scripts` VALUES ('Z_UploadForm.php',15,'Utility to upload a file to a remote server');
INSERT INTO `scripts` VALUES ('Z_UploadResult.php',15,'Utility to upload a file to a remote server');

--
-- Dumping data for table `securitygroups`
--

INSERT INTO `securitygroups` VALUES (1,0);
INSERT INTO `securitygroups` VALUES (1,1);
INSERT INTO `securitygroups` VALUES (1,2);
INSERT INTO `securitygroups` VALUES (2,0);
INSERT INTO `securitygroups` VALUES (2,1);
INSERT INTO `securitygroups` VALUES (2,2);
INSERT INTO `securitygroups` VALUES (2,11);
INSERT INTO `securitygroups` VALUES (3,0);
INSERT INTO `securitygroups` VALUES (3,1);
INSERT INTO `securitygroups` VALUES (3,2);
INSERT INTO `securitygroups` VALUES (3,3);
INSERT INTO `securitygroups` VALUES (3,4);
INSERT INTO `securitygroups` VALUES (3,5);
INSERT INTO `securitygroups` VALUES (3,11);
INSERT INTO `securitygroups` VALUES (4,0);
INSERT INTO `securitygroups` VALUES (4,1);
INSERT INTO `securitygroups` VALUES (4,2);
INSERT INTO `securitygroups` VALUES (4,5);
INSERT INTO `securitygroups` VALUES (5,0);
INSERT INTO `securitygroups` VALUES (5,1);
INSERT INTO `securitygroups` VALUES (5,2);
INSERT INTO `securitygroups` VALUES (5,3);
INSERT INTO `securitygroups` VALUES (5,11);
INSERT INTO `securitygroups` VALUES (6,0);
INSERT INTO `securitygroups` VALUES (6,1);
INSERT INTO `securitygroups` VALUES (6,2);
INSERT INTO `securitygroups` VALUES (6,3);
INSERT INTO `securitygroups` VALUES (6,4);
INSERT INTO `securitygroups` VALUES (6,5);
INSERT INTO `securitygroups` VALUES (6,6);
INSERT INTO `securitygroups` VALUES (6,7);
INSERT INTO `securitygroups` VALUES (6,8);
INSERT INTO `securitygroups` VALUES (6,9);
INSERT INTO `securitygroups` VALUES (6,10);
INSERT INTO `securitygroups` VALUES (6,11);
INSERT INTO `securitygroups` VALUES (7,0);
INSERT INTO `securitygroups` VALUES (7,1);
INSERT INTO `securitygroups` VALUES (8,0);
INSERT INTO `securitygroups` VALUES (8,1);
INSERT INTO `securitygroups` VALUES (8,2);
INSERT INTO `securitygroups` VALUES (8,3);
INSERT INTO `securitygroups` VALUES (8,4);
INSERT INTO `securitygroups` VALUES (8,5);
INSERT INTO `securitygroups` VALUES (8,6);
INSERT INTO `securitygroups` VALUES (8,7);
INSERT INTO `securitygroups` VALUES (8,8);
INSERT INTO `securitygroups` VALUES (8,9);
INSERT INTO `securitygroups` VALUES (8,10);
INSERT INTO `securitygroups` VALUES (8,11);
INSERT INTO `securitygroups` VALUES (8,12);
INSERT INTO `securitygroups` VALUES (8,13);
INSERT INTO `securitygroups` VALUES (8,14);
INSERT INTO `securitygroups` VALUES (8,15);
INSERT INTO `securitygroups` VALUES (8,1000);
INSERT INTO `securitygroups` VALUES (9,0);
INSERT INTO `securitygroups` VALUES (9,9);

--
-- Dumping data for table `securityroles`
--

INSERT INTO `securityroles` VALUES (1,'Inquiries/Order Entry');
INSERT INTO `securityroles` VALUES (2,'Manufac/Stock Admin');
INSERT INTO `securityroles` VALUES (3,'Purchasing Officer');
INSERT INTO `securityroles` VALUES (4,'AP Clerk');
INSERT INTO `securityroles` VALUES (5,'AR Clerk');
INSERT INTO `securityroles` VALUES (6,'Accountant');
INSERT INTO `securityroles` VALUES (7,'Customer Log On Only');
INSERT INTO `securityroles` VALUES (8,'System Administrator');
INSERT INTO `securityroles` VALUES (9,'Supplier Log On Only');

--
-- Dumping data for table `securitytokens`
--

INSERT INTO `securitytokens` VALUES (0,'Main Index Page');
INSERT INTO `securitytokens` VALUES (1,'Order Entry/Inquiries customer access only');
INSERT INTO `securitytokens` VALUES (2,'Basic Reports and Inquiries with selection options');
INSERT INTO `securitytokens` VALUES (3,'Credit notes and AR management');
INSERT INTO `securitytokens` VALUES (4,'Purchasing data/PO Entry/Reorder Levels');
INSERT INTO `securitytokens` VALUES (5,'Accounts Payable');
INSERT INTO `securitytokens` VALUES (6,'Petty Cash');
INSERT INTO `securitytokens` VALUES (7,'Bank Reconciliations');
INSERT INTO `securitytokens` VALUES (8,'General ledger reports/inquiries');
INSERT INTO `securitytokens` VALUES (9,'Supplier centre - Supplier access only');
INSERT INTO `securitytokens` VALUES (10,'General Ledger Maintenance, stock valuation & Configuration');
INSERT INTO `securitytokens` VALUES (11,'Inventory Management and Pricing');
INSERT INTO `securitytokens` VALUES (12,'Unknown');
INSERT INTO `securitytokens` VALUES (13,'Unknown');
INSERT INTO `securitytokens` VALUES (14,'Unknown');
INSERT INTO `securitytokens` VALUES (15,'User Management and System Administration');
INSERT INTO `securitytokens` VALUES (1000,'User can view and alter sales prices');
INSERT INTO `securitytokens` VALUES (1001,'User can bypass purchasing security and go straight from order to invoice');

--
-- Dumping data for table `shipmentcharges`
--


--
-- Dumping data for table `shipments`
--


--
-- Dumping data for table `shippers`
--

INSERT INTO `shippers` VALUES (1,'Default Shipper',0);

--
-- Dumping data for table `stockcategory`
--

INSERT INTO `stockcategory` VALUES ('CONSUL','Consultancy','L',1010,1,1,1,1,1010);
INSERT INTO `stockcategory` VALUES ('FOOD','Food items for sale','F',1460,5700,5700,5000,5000,1440);
INSERT INTO `stockcategory` VALUES ('INGR','Food ingrediants','M',1420,5700,5700,5000,5000,1440);

--
-- Dumping data for table `stockcatproperties`
--


--
-- Dumping data for table `stockcheckfreeze`
--


--
-- Dumping data for table `stockcounts`
--


--
-- Dumping data for table `stockitemproperties`
--


--
-- Dumping data for table `stockmaster`
--

INSERT INTO `stockmaster` VALUES ('CHAPATI','FOOD','Chapati','Chapati','each','M',0.0000,10.0000,90.0000,10.0000,0.0000,0,0,0,0,0.0000,0.0000,'','',4,0,'none',1,0,0,0,0,0.0000,'2012-12-10');
INSERT INTO `stockmaster` VALUES ('CONSULT','CONSUL','Web Consultancy','Web Consultancy','hours','D',0.0000,0.0000,0.0000,0.0000,0.0000,0,0,0,0,0.0000,0.0000,'','',1,0,'none',0,2,0,0,0,0.0000,'0000-00-00');
INSERT INTO `stockmaster` VALUES ('FLOUR','INGR','Maize Flour','Maize Flour','kgs','B',0.0000,0.0000,10.0000,0.0000,0.0000,0,0,0,0,0.0000,0.0000,'','',1,0,'none',1,3,0,0,0,0.0000,'0000-00-00');
INSERT INTO `stockmaster` VALUES ('WATER','INGR','Water','Water','litres','B',0.0000,0.0000,0.0000,0.0000,0.0000,0,0,0,0,0.0000,0.0000,'','',1,0,'none',0,3,0,0,0,0.0000,'0000-00-00');

--
-- Dumping data for table `stockmoves`
--

INSERT INTO `stockmoves` VALUES (1,'CHAPATI',26,2,'MSA','2013-01-05','','',0.0000,1,'2',50,0,0,1,50,0,NULL);
INSERT INTO `stockmoves` VALUES (2,'CHAPATI',10,1,'MSA','2013-01-07','COA001','COA001',10.0000,1,'2',-50,0,10,1,0,0,'');
INSERT INTO `stockmoves` VALUES (3,'CONSULT',10,2,'MSA','2013-01-12','KAM001','KAM001',2600.0000,1,'3',-20,0,0,1,0,0,'75 hours of consultancy on design and preparation of web site @ UGX70,000 per hour');
INSERT INTO `stockmoves` VALUES (4,'CHAPATI',25,1,'MSA','2013-01-15','','',100.0000,1,'VOI001 (Voi Fruit and Vegetable) - 2',1,0,10,1,1,0,NULL);
INSERT INTO `stockmoves` VALUES (5,'FLOUR',25,1,'MSA','2013-01-15','','',10.0000,1,'VOI001 (Voi Fruit and Vegetable) - 2',10,0,0,1,10,0,NULL);

--
-- Dumping data for table `stockmovestaxes`
--

INSERT INTO `stockmovestaxes` VALUES (2,1,0.16,0,0);
INSERT INTO `stockmovestaxes` VALUES (3,1,0.16,0,0);

--
-- Dumping data for table `stockrequest`
--

INSERT INTO `stockrequest` VALUES (1,'MSA',1,'2013-01-06',1,0,'');

--
-- Dumping data for table `stockrequestitems`
--

INSERT INTO `stockrequestitems` VALUES (0,1,'FLOUR',10,0,3,'kgs',0);
INSERT INTO `stockrequestitems` VALUES (1,1,'WATER',20,0,3,'litres',0);

--
-- Dumping data for table `stockserialitems`
--


--
-- Dumping data for table `stockserialmoves`
--


--
-- Dumping data for table `suppallocs`
--


--
-- Dumping data for table `suppliercontacts`
--


--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` VALUES ('VOI001','Voi Fruit and Vegetable','PO Box 9999','','Voi','','','Kenya',1,0.000000,0.000000,'KES','2012-12-10','20',200,'2013-01-14 00:00:00','','0','',0,1,0,'','','','sales@example.com','','');

--
-- Dumping data for table `suppliertype`
--

INSERT INTO `suppliertype` VALUES (1,'FOOD');

--
-- Dumping data for table `supptrans`
--

INSERT INTO `supptrans` VALUES (1,22,'VOI001','Cash','2013-01-14','0000-00-00','2013-01-14 13:03:49',0,1,-200,0,0,0,'',0,1);
INSERT INTO `supptrans` VALUES (1,20,'VOI001','12345','2013-01-14','2013-02-22','2013-01-15 00:00:00',0,1,200,32,0,0,'',0,2);
INSERT INTO `supptrans` VALUES (2,20,'VOI001','666','2013-01-14','2013-02-22','2013-01-15 00:00:00',0,1,12340,1974.4,0,0,'',0,3);

--
-- Dumping data for table `supptranstaxes`
--

INSERT INTO `supptranstaxes` VALUES (2,1,32);
INSERT INTO `supptranstaxes` VALUES (3,1,1974.4);

--
-- Dumping data for table `systypes`
--

INSERT INTO `systypes` VALUES (0,'Journal - GL',2);
INSERT INTO `systypes` VALUES (1,'Payment - GL',0);
INSERT INTO `systypes` VALUES (2,'Receipt - GL',0);
INSERT INTO `systypes` VALUES (3,'Standing Journal',0);
INSERT INTO `systypes` VALUES (10,'Sales Invoice',2);
INSERT INTO `systypes` VALUES (11,'Credit Note',0);
INSERT INTO `systypes` VALUES (12,'Receipt',2);
INSERT INTO `systypes` VALUES (15,'Journal - Debtors',0);
INSERT INTO `systypes` VALUES (16,'Location Transfer',0);
INSERT INTO `systypes` VALUES (17,'Stock Adjustment',0);
INSERT INTO `systypes` VALUES (18,'Purchase Order',2);
INSERT INTO `systypes` VALUES (19,'Picking List',0);
INSERT INTO `systypes` VALUES (20,'Purchase Invoice',2);
INSERT INTO `systypes` VALUES (21,'Debit Note',0);
INSERT INTO `systypes` VALUES (22,'Creditors Payment',1);
INSERT INTO `systypes` VALUES (23,'Creditors Journal',0);
INSERT INTO `systypes` VALUES (25,'Purchase Order Delivery',1);
INSERT INTO `systypes` VALUES (26,'Work Order Receipt',2);
INSERT INTO `systypes` VALUES (28,'Work Order Issue',1);
INSERT INTO `systypes` VALUES (29,'Work Order Variance',0);
INSERT INTO `systypes` VALUES (30,'Sales Order',3);
INSERT INTO `systypes` VALUES (31,'Shipment Close',0);
INSERT INTO `systypes` VALUES (32,'Contract Close',0);
INSERT INTO `systypes` VALUES (35,'Cost Update',0);
INSERT INTO `systypes` VALUES (36,'Exchange Difference',0);
INSERT INTO `systypes` VALUES (37,'Tenders',1);
INSERT INTO `systypes` VALUES (38,'Stock Requests',1);
INSERT INTO `systypes` VALUES (40,'Work Order',3);
INSERT INTO `systypes` VALUES (41,'Asset Addition',0);
INSERT INTO `systypes` VALUES (42,'Asset Category Change',0);
INSERT INTO `systypes` VALUES (43,'Delete w/down asset',0);
INSERT INTO `systypes` VALUES (44,'Depreciation',0);
INSERT INTO `systypes` VALUES (49,'Import Fixed Assets',0);
INSERT INTO `systypes` VALUES (50,'Opening Balance',0);
INSERT INTO `systypes` VALUES (500,'Auto Debtor Number',0);

--
-- Dumping data for table `tags`
--


--
-- Dumping data for table `taxauthorities`
--

INSERT INTO `taxauthorities` VALUES (1,'Kenya Revenue Authority','2300','2310','','','','');
INSERT INTO `taxauthorities` VALUES (5,'Uganda Revenue Authority','2300','2310','','','','');
INSERT INTO `taxauthorities` VALUES (11,'Tanzania Revenue Authority','2300','2310','','','','');
INSERT INTO `taxauthorities` VALUES (12,'Rwanda Revenue Authority','2300','2310','','','','');
INSERT INTO `taxauthorities` VALUES (13,'Burundi Revenue Authority','2300','2310','','','','');

--
-- Dumping data for table `taxauthrates`
--

INSERT INTO `taxauthrates` VALUES (1,1,1,0.16);
INSERT INTO `taxauthrates` VALUES (1,1,2,0.16);
INSERT INTO `taxauthrates` VALUES (1,1,5,0.16);
INSERT INTO `taxauthrates` VALUES (5,1,1,0.18);
INSERT INTO `taxauthrates` VALUES (5,1,2,0.18);
INSERT INTO `taxauthrates` VALUES (5,1,5,0.18);
INSERT INTO `taxauthrates` VALUES (11,1,1,0.18);
INSERT INTO `taxauthrates` VALUES (11,1,2,0.18);
INSERT INTO `taxauthrates` VALUES (11,1,5,0.18);
INSERT INTO `taxauthrates` VALUES (12,1,1,0.18);
INSERT INTO `taxauthrates` VALUES (12,1,2,0.18);
INSERT INTO `taxauthrates` VALUES (12,1,5,0.18);
INSERT INTO `taxauthrates` VALUES (13,1,1,0.18);
INSERT INTO `taxauthrates` VALUES (13,1,2,0.18);
INSERT INTO `taxauthrates` VALUES (13,1,5,0.18);

--
-- Dumping data for table `taxcategories`
--

INSERT INTO `taxcategories` VALUES (1,'Taxable supply');
INSERT INTO `taxcategories` VALUES (2,'Luxury Items');
INSERT INTO `taxcategories` VALUES (4,'Exempt');
INSERT INTO `taxcategories` VALUES (5,'Freight');

--
-- Dumping data for table `taxgroups`
--

INSERT INTO `taxgroups` VALUES (1,'Kenya');
INSERT INTO `taxgroups` VALUES (2,'Uganda');
INSERT INTO `taxgroups` VALUES (3,'Tanzania');
INSERT INTO `taxgroups` VALUES (4,'Rwanda');
INSERT INTO `taxgroups` VALUES (5,'Burundi');

--
-- Dumping data for table `taxgrouptaxes`
--

INSERT INTO `taxgrouptaxes` VALUES (1,1,0,0);
INSERT INTO `taxgrouptaxes` VALUES (2,5,0,0);
INSERT INTO `taxgrouptaxes` VALUES (3,11,0,0);
INSERT INTO `taxgrouptaxes` VALUES (4,12,0,0);
INSERT INTO `taxgrouptaxes` VALUES (5,13,0,0);

--
-- Dumping data for table `taxprovinces`
--

INSERT INTO `taxprovinces` VALUES (1,'East African Community');

--
-- Dumping data for table `tenderitems`
--

INSERT INTO `tenderitems` VALUES (1,'CHAPATI','10','each');
INSERT INTO `tenderitems` VALUES (1,'FLOUR','20','kgs');
INSERT INTO `tenderitems` VALUES (1,'WATER','30','litres');

--
-- Dumping data for table `tenders`
--

INSERT INTO `tenders` VALUES (1,'MSA',' ','','','','','','',0,'2013-01-02 00:00:00');

--
-- Dumping data for table `tendersuppliers`
--

INSERT INTO `tendersuppliers` VALUES (1,'VOI001','sales@example.com',1);

--
-- Dumping data for table `unitsofmeasure`
--

INSERT INTO `unitsofmeasure` VALUES (1,'each');
INSERT INTO `unitsofmeasure` VALUES (2,'meters');
INSERT INTO `unitsofmeasure` VALUES (3,'kgs');
INSERT INTO `unitsofmeasure` VALUES (4,'litres');
INSERT INTO `unitsofmeasure` VALUES (5,'length');
INSERT INTO `unitsofmeasure` VALUES (6,'hours');

--
-- Dumping data for table `woitems`
--

INSERT INTO `woitems` VALUES (2,'CHAPATI',190,50,0,'');

--
-- Dumping data for table `worequirements`
--

INSERT INTO `worequirements` VALUES (2,'CHAPATI','FLOUR',0.45,0,0);
INSERT INTO `worequirements` VALUES (2,'CHAPATI','WATER',0.25,0,0);

--
-- Dumping data for table `workcentres`
--

INSERT INTO `workcentres` VALUES ('KIT','MSA','Kitchen',1,120,5100,0);

--
-- Dumping data for table `workorders`
--

INSERT INTO `workorders` VALUES (2,'MSA','2012-12-15','2012-12-15',0,0);
INSERT INTO `workorders` VALUES (3,'MSA','2013-01-05','2013-01-05',0,0);

--
-- Dumping data for table `woserialnos`
--


--
-- Dumping data for table `www_users`
--

INSERT INTO `www_users` VALUES ('admin','8467dd232d0410dd7fc0e25a5e9ce72f9bdc0d1e','Demonstration user','','','','','info@kwamoja.com','MSA',8,1,'2013-01-21 14:27:36','','A4','1,1,1,1,1,1,1,1,1,1,1,',0,50,'aguapop','en_GB.utf8',0,0,0);
INSERT INTO `www_users` VALUES ('coastal','8467dd232d0410dd7fc0e25a5e9ce72f9bdc0d1e','Coastal Hotelsd Ltd','COA001','','','','','MSA',7,0,'2012-12-17 22:37:30','COA001','A4','1,1,0,0,0,0,0,0',0,50,'aguapop','en_GB.utf8',0,0,0);
INSERT INTO `www_users` VALUES ('voifv','8467dd232d0410dd7fc0e25a5e9ce72f9bdc0d1e','Voi Fruit and Vegetable supplies Lt','','VOI001','','','','MSA',9,0,'2013-01-03 11:59:54','','A4','0,0,0,0,0,0,0,0,0,0,0,',0,50,'silverwolf','en_GB.utf8',0,0,1);
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-01-21 22:26:35
SET FOREIGN_KEY_CHECKS = 1;
