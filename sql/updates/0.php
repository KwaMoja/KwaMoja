<?php

CreateTable('accountgroups',
"CREATE TABLE `accountgroups` (
  `groupname` char(30) NOT NULL default '',
  `sectioninaccounts` int(11) NOT NULL default '0',
  `pandl` tinyint(4) NOT NULL default '1',
  `sequenceintb` smallint(6) NOT NULL default '0',
  PRIMARY KEY (`groupname`),
  KEY `SequenceInTB` (`sequenceintb`),
  KEY `sectioninaccounts` (`sectioninaccounts`),
  CONSTRAINT `accountgroups_ibfk_1` FOREIGN KEY (`sectioninaccounts`) REFERENCES `accountsection` (`sectionid`)
)",
$db);

CreateTable('accountsection',
"CREATE TABLE `accountsection` (
  `sectionid` int(11) NOT NULL default '0',
  `sectionname` text NOT NULL,
  PRIMARY KEY (`sectionid`)
)",
$db);

CreateTable('areas',
"CREATE TABLE `areas` (
  `areacode` char(2) NOT NULL default '',
  `areadescription` varchar(25) NOT NULL default '',
  PRIMARY KEY (`areacode`)
)",
$db);

CreateTable('bankaccounts',
"CREATE TABLE `bankaccounts` (
  `accountcode` int(11) NOT NULL default '0',
  `bankaccountname` char(50) NOT NULL default '',
  `bankaccountnumber` char(50) NOT NULL default '',
  `bankaddress` char(50) default NULL,
  PRIMARY KEY (`accountcode`),
  KEY `BankAccountName` (`bankaccountname`),
  KEY `BankAccountNumber` (`bankaccountnumber`),
  CONSTRAINT `bankaccounts_ibfk_1` FOREIGN KEY (`accountcode`) REFERENCES `chartmaster` (`AccountCode`)
)",
$db);

CreateTable('banktrans',
"CREATE TABLE `banktrans` (
  `banktransid` bigint(20) NOT NULL auto_increment,
  `type` smallint(6) NOT NULL default '0',
  `transno` bigint(20) NOT NULL default '0',
  `bankact` int(11) NOT NULL default '0',
  `ref` varchar(50) NOT NULL default '',
  `amountcleared` double NOT NULL default '0',
  `exrate` double NOT NULL default '1',
  `transdate` date NOT NULL default '0000-00-00',
  `banktranstype` varchar(30) NOT NULL default '',
  `amount` double NOT NULL default '0',
  `currcode` char(3) NOT NULL default '',
  PRIMARY KEY (`banktransid`),
  KEY `BankAct` (`bankact`,`ref`),
  KEY `TransDate` (`transdate`),
  KEY `TransType` (`banktranstype`),
  KEY `Type` (`type`,`transno`),
  KEY `CurrCode` (`currcode`),
  CONSTRAINT `banktrans_ibfk_1` FOREIGN KEY (`type`) REFERENCES `systypes` (`TypeID`),
  CONSTRAINT `banktrans_ibfk_2` FOREIGN KEY (`bankact`) REFERENCES `bankaccounts` (`accountcode`)
)",
$db);

CreateTable('bom',
"CREATE TABLE `bom` (
  `parent` char(20) NOT NULL default '',
  `component` char(20) NOT NULL default '',
  `workcentreadded` char(5) NOT NULL default '',
  `loccode` char(5) NOT NULL default '',
  `effectiveafter` date NOT NULL default '0000-00-00',
  `effectiveto` date NOT NULL default '9999-12-31',
  `quantity` double(16,4) NOT NULL default '1.0000',
  PRIMARY KEY (`parent`,`component`,`workcentreadded`,`loccode`),
  KEY `Component` (`component`),
  KEY `EffectiveAfter` (`effectiveafter`),
  KEY `EffectiveTo` (`effectiveto`),
  KEY `LocCode` (`loccode`),
  KEY `Parent` (`parent`,`effectiveafter`,`effectiveto`,`loccode`),
  KEY `Parent_2` (`parent`),
  KEY `WorkCentreAdded` (`workcentreadded`),
  CONSTRAINT `bom_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `stockmaster` (`StockID`),
  CONSTRAINT `bom_ibfk_2` FOREIGN KEY (`component`) REFERENCES `stockmaster` (`StockID`),
  CONSTRAINT `bom_ibfk_3` FOREIGN KEY (`workcentreadded`) REFERENCES `workcentres` (`Code`),
  CONSTRAINT `bom_ibfk_4` FOREIGN KEY (`loccode`) REFERENCES `locations` (`LocCode`)
)",
$db);

CreateTable('buckets',
"CREATE TABLE `buckets` (
  `workcentre` char(5) NOT NULL default '',
  `availdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `capacity` double NOT NULL default '0',
  PRIMARY KEY (`workcentre`,`availdate`),
  KEY `WorkCentre` (`workcentre`),
  KEY `AvailDate` (`availdate`),
  CONSTRAINT `buckets_ibfk_1` FOREIGN KEY (`workcentre`) REFERENCES `workcentres` (`Code`)
)",
$db);

CreateTable('chartdetails',
"CREATE TABLE `chartdetails` (
  `accountcode` int(11) NOT NULL default '0',
  `period` smallint(6) NOT NULL default '0',
  `budget` double NOT NULL default '0',
  `actual` double NOT NULL default '0',
  `bfwd` double NOT NULL default '0',
  `bfwdbudget` double NOT NULL default '0',
  PRIMARY KEY (`accountcode`,`period`),
  KEY `Period` (`period`),
  CONSTRAINT `chartdetails_ibfk_1` FOREIGN KEY (`accountcode`) REFERENCES `chartmaster` (`AccountCode`),
  CONSTRAINT `chartdetails_ibfk_2` FOREIGN KEY (`period`) REFERENCES `periods` (`PeriodNo`)
)",
$db);

CreateTable('chartmaster',
"CREATE TABLE `chartmaster` (
  `accountcode` int(11) NOT NULL default '0',
  `accountname` char(50) NOT NULL default '',
  `group_` char(30) NOT NULL default '',
  PRIMARY KEY (`accountcode`),
  KEY `AccountCode` (`accountcode`),
  KEY `AccountName` (`accountname`),
  KEY `Group_` (`group_`),
  CONSTRAINT `chartmaster_ibfk_1` FOREIGN KEY (`group_`) REFERENCES `accountgroups` (`groupname`)
)",
$db);

CreateTable('cogsglpostings',
"CREATE TABLE `cogsglpostings` (
  `id` int(11) NOT NULL auto_increment,
  `area` char(2) NOT NULL default '',
  `stkcat` varchar(6) NOT NULL default '',
  `glcode` int(11) NOT NULL default '0',
  `salestype` char(2) NOT NULL default 'AN',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Area_StkCat` (`area`,`stkcat`,`salestype`),
  KEY `Area` (`area`),
  KEY `StkCat` (`stkcat`),
  KEY `GLCode` (`glcode`),
  KEY `SalesType` (`salestype`)
)",
$db);

CreateTable('companies',
"CREATE TABLE `companies` (
  `coycode` int(11) NOT NULL default '1',
  `coyname` varchar(50) NOT NULL default '',
  `gstno` varchar(20) NOT NULL default '',
  `companynumber` varchar(20) NOT NULL default '0',
  `postaladdress` varchar(50) NOT NULL default '',
  `regoffice1` varchar(50) NOT NULL default '',
  `regoffice2` varchar(50) NOT NULL default '',
  `regoffice3` varchar(50) NOT NULL default '',
  `telephone` varchar(25) NOT NULL default '',
  `fax` varchar(25) NOT NULL default '',
  `email` varchar(55) NOT NULL default '',
  `currencydefault` varchar(4) NOT NULL default '',
  `debtorsact` int(11) NOT NULL default '70000',
  `pytdiscountact` int(11) NOT NULL default '55000',
  `creditorsact` int(11) NOT NULL default '80000',
  `payrollact` int(11) NOT NULL default '84000',
  `grnact` int(11) NOT NULL default '72000',
  `exchangediffact` int(11) NOT NULL default '65000',
  `purchasesexchangediffact` int(11) NOT NULL default '0',
  `retainedearnings` int(11) NOT NULL default '90000',
  `gllink_debtors` tinyint(1) default '1',
  `gllink_creditors` tinyint(1) default '1',
  `gllink_stock` tinyint(1) default '1',
  `freightact` int(11) NOT NULL default '0',
  PRIMARY KEY (`coycode`)
)",
$db);

CreateTable('config',
"CREATE TABLE `config` (
  `confname` varchar(35) NOT NULL default '',
  `confvalue` text NOT NULL,
  PRIMARY KEY (`confname`)
)",
$db);

CreateTable('contractbom',
"CREATE TABLE `contractbom` (
  `contractref` char(20) NOT NULL default '',
  `component` char(20) NOT NULL default '',
  `workcentreadded` char(5) NOT NULL default '',
  `loccode` char(5) NOT NULL default '',
  `quantity` double(16,4) NOT NULL default '1.0000',
  PRIMARY KEY (`contractref`,`component`,`workcentreadded`,`loccode`),
  KEY `Component` (`component`),
  KEY `LocCode` (`loccode`),
  KEY `ContractRef` (`contractref`),
  KEY `WorkCentreAdded` (`workcentreadded`),
  KEY `WorkCentreAdded_2` (`workcentreadded`),
  CONSTRAINT `contractbom_ibfk_1` FOREIGN KEY (`workcentreadded`) REFERENCES `workcentres` (`Code`),
  CONSTRAINT `contractbom_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`LocCode`),
  CONSTRAINT `contractbom_ibfk_3` FOREIGN KEY (`component`) REFERENCES `stockmaster` (`StockID`)
)",
$db);

CreateTable('contractreqts',
"CREATE TABLE `contractreqts` (
  `contractreqid` int(11) NOT NULL auto_increment,
  `contract` char(20) NOT NULL default '',
  `component` char(40) NOT NULL default '',
  `quantity` double(16,4) NOT NULL default '1.0000',
  `priceperunit` decimal(20,4) NOT NULL default '0.0000',
  PRIMARY KEY (`contractreqid`),
  KEY `Contract` (`contract`),
  CONSTRAINT `contractreqts_ibfk_1` FOREIGN KEY (`contract`) REFERENCES `contracts` (`ContractRef`)
)",
$db);

CreateTable('contracts',
"CREATE TABLE `contracts` (
  `contractref` varchar(20) NOT NULL default '',
  `contractdescription` varchar(50) NOT NULL default '',
  `debtorno` varchar(10) NOT NULL default '',
  `branchcode` varchar(10) NOT NULL default '',
  `status` varchar(10) NOT NULL default 'Quotation',
  `categoryid` varchar(6) NOT NULL default '',
  `typeabbrev` char(2) NOT NULL default '',
  `orderno` int(11) NOT NULL default '0',
  `quotedpricefx` decimal(20,4) NOT NULL default '0.0000',
  `margin` double(16,4) NOT NULL default '1.0000',
  `woref` varchar(20) NOT NULL default '',
  `requireddate` datetime NOT NULL default '0000-00-00 00:00:00',
  `canceldate` datetime NOT NULL default '0000-00-00 00:00:00',
  `quantityreqd` double(16,4) NOT NULL default '1.0000',
  `specifications` longblob NOT NULL,
  `datequoted` datetime NOT NULL default '0000-00-00 00:00:00',
  `units` varchar(15) NOT NULL default 'Each',
  `drawing` longblob NOT NULL,
  `rate` double(16,4) NOT NULL default '1.0000',
  PRIMARY KEY (`contractref`),
  KEY `OrderNo` (`orderno`),
  KEY `CategoryID` (`categoryid`),
  KEY `Status` (`status`),
  KEY `TypeAbbrev` (`typeabbrev`),
  KEY `WORef` (`woref`),
  KEY `DebtorNo` (`debtorno`,`branchcode`),
  CONSTRAINT `contracts_ibfk_1` FOREIGN KEY (`debtorno`, `branchcode`) REFERENCES `custbranch` (`DebtorNo`, `BranchCode`),
  CONSTRAINT `contracts_ibfk_2` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`CategoryID`),
  CONSTRAINT `contracts_ibfk_3` FOREIGN KEY (`typeabbrev`) REFERENCES `salestypes` (`TypeAbbrev`)
)",
$db);

CreateTable('currencies',
"CREATE TABLE `currencies` (
  `currency` char(20) NOT NULL default '',
  `currabrev` char(3) NOT NULL default '',
  `country` char(50) NOT NULL default '',
  `hundredsname` char(15) NOT NULL default 'Cents',
  `rate` double(16,4) NOT NULL default '1.0000',
  PRIMARY KEY (`currabrev`),
  KEY `Country` (`country`)
)",
$db);

CreateTable('custallocns',
"CREATE TABLE `custallocns` (
  `id` int(11) NOT NULL auto_increment,
  `amt` decimal(20,4) NOT NULL default '0.0000',
  `datealloc` date NOT NULL default '0000-00-00',
  `transid_allocfrom` int(11) NOT NULL default '0',
  `transid_allocto` int(11) NOT NULL default '0',
  PRIMARY KEY (`id`),
  KEY `DateAlloc` (`datealloc`),
  KEY `TransID_AllocFrom` (`transid_allocfrom`),
  KEY `TransID_AllocTo` (`transid_allocto`),
  CONSTRAINT `custallocns_ibfk_1` FOREIGN KEY (`transid_allocfrom`) REFERENCES `debtortrans` (`ID`),
  CONSTRAINT `custallocns_ibfk_2` FOREIGN KEY (`transid_allocto`) REFERENCES `debtortrans` (`ID`)
)",
$db);

createTable('custbranch',
"CREATE TABLE `custbranch` (
  `branchcode` varchar(10) NOT NULL default '',
  `debtorno` varchar(10) NOT NULL default '',
  `brname` varchar(40) NOT NULL default '',
  `braddress1` varchar(40) NOT NULL default '',
  `braddress2` varchar(40) NOT NULL default '',
  `braddress3` varchar(40) NOT NULL default '',
  `braddress4` varchar(50) NOT NULL default '',
  `estdeliverydays` smallint(6) NOT NULL default '1',
  `area` char(2) NOT NULL default '',
  `salesman` varchar(4) NOT NULL default '',
  `fwddate` smallint(6) NOT NULL default '0',
  `phoneno` varchar(20) NOT NULL default '',
  `faxno` varchar(20) NOT NULL default '',
  `contactname` varchar(30) NOT NULL default '',
  `email` varchar(55) NOT NULL default '',
  `defaultlocation` varchar(5) NOT NULL default '',
  `taxauthority` tinyint(4) NOT NULL default '1',
  `defaultshipvia` int(11) NOT NULL default '1',
  `deliverblind` tinyint(1) default '1',
  `disabletrans` tinyint(4) NOT NULL default '0',
  `brpostaddr1` varchar(40) NOT NULL default '',
  `brpostaddr2` varchar(40) NOT NULL default '',
  `brpostaddr3` varchar(30) NOT NULL default '',
  `brpostaddr4` varchar(20) NOT NULL default '',
  `custbranchcode` varchar(30) NOT NULL default '',
  PRIMARY KEY (`branchcode`,`debtorno`),
  KEY `BranchCode` (`branchcode`),
  KEY `BrName` (`brname`),
  KEY `DebtorNo` (`debtorno`),
  KEY `Salesman` (`salesman`),
  KEY `Area` (`area`),
  KEY `Area_2` (`area`),
  KEY `DefaultLocation` (`defaultlocation`),
  KEY `TaxAuthority` (`taxauthority`),
  KEY `DefaultShipVia` (`defaultshipvia`),
  CONSTRAINT `custbranch_ibfk_1` FOREIGN KEY (`debtorno`) REFERENCES `debtorsmaster` (`DebtorNo`),
  CONSTRAINT `custbranch_ibfk_2` FOREIGN KEY (`area`) REFERENCES `areas` (`areacode`),
  CONSTRAINT `custbranch_ibfk_3` FOREIGN KEY (`salesman`) REFERENCES `salesman` (`SalesmanCode`),
  CONSTRAINT `custbranch_ibfk_4` FOREIGN KEY (`defaultlocation`) REFERENCES `locations` (`LocCode`),
  CONSTRAINT `custbranch_ibfk_5` FOREIGN KEY (`taxauthority`) REFERENCES `taxauthorities` (`TaxID`),
  CONSTRAINT `custbranch_ibfk_6` FOREIGN KEY (`defaultshipvia`) REFERENCES `shippers` (`Shipper_ID`)
)",
$db);

CreateTable('debtorsmaster',
"CREATE TABLE `debtorsmaster` (
  `debtorno` varchar(10) NOT NULL default '',
  `name` varchar(40) NOT NULL default '',
  `address1` varchar(40) NOT NULL default '',
  `address2` varchar(40) NOT NULL default '',
  `address3` varchar(40) NOT NULL default '',
  `address4` varchar(50) NOT NULL default '',
  `currcode` char(3) NOT NULL default '',
  `salestype` char(2) NOT NULL default '',
  `clientsince` datetime NOT NULL default '0000-00-00 00:00:00',
  `holdreason` smallint(6) NOT NULL default '0',
  `paymentterms` char(2) NOT NULL default 'f',
  `discount` double(16,4) NOT NULL default '0.0000',
  `pymtdiscount` double(16,4) NOT NULL default '0.0000',
  `lastpaid` double(16,4) NOT NULL default '0.0000',
  `lastpaiddate` datetime default NULL,
  `creditlimit` double NOT NULL default '1000',
  `invaddrbranch` tinyint(4) NOT NULL default '0',
  `discountcode` char(2) NOT NULL default '',
  `ediinvoices` tinyint(4) NOT NULL default '0',
  `ediorders` tinyint(4) NOT NULL default '0',
  `edireference` varchar(20) NOT NULL default '',
  `editransport` varchar(5) NOT NULL default 'email',
  `ediaddress` varchar(50) NOT NULL default '',
  `ediserveruser` varchar(20) NOT NULL default '',
  `ediserverpwd` varchar(20) NOT NULL default '',
  `taxref` varchar(20) NOT NULL default '',
  PRIMARY KEY (`debtorno`),
  KEY `Currency` (`currcode`),
  KEY `HoldReason` (`holdreason`),
  KEY `Name` (`name`),
  KEY `PaymentTerms` (`paymentterms`),
  KEY `SalesType` (`salestype`),
  KEY `EDIInvoices` (`ediinvoices`),
  KEY `EDIOrders` (`ediorders`),
  CONSTRAINT `debtorsmaster_ibfk_1` FOREIGN KEY (`holdreason`) REFERENCES `holdreasons` (`ReasonCode`),
  CONSTRAINT `debtorsmaster_ibfk_2` FOREIGN KEY (`currcode`) REFERENCES `currencies` (`currabrev`),
  CONSTRAINT `debtorsmaster_ibfk_3` FOREIGN KEY (`paymentterms`) REFERENCES `paymentterms` (`TermsIndicator`),
  CONSTRAINT `debtorsmaster_ibfk_4` FOREIGN KEY (`salestype`) REFERENCES `salestypes` (`TypeAbbrev`)
)",
$db);

CreateTable('debtortrans',
"CREATE TABLE `debtortrans` (
  `id` int(11) NOT NULL auto_increment,
  `transno` int(11) NOT NULL default '0',
  `type` smallint(6) NOT NULL default '0',
  `debtorno` varchar(10) NOT NULL default '',
  `branchcode` varchar(10) NOT NULL default '',
  `trandate` datetime NOT NULL default '0000-00-00 00:00:00',
  `prd` smallint(6) NOT NULL default '0',
  `settled` tinyint(4) NOT NULL default '0',
  `reference` varchar(20) NOT NULL default '',
  `tpe` char(2) NOT NULL default '',
  `order_` int(11) NOT NULL default '0',
  `rate` double(16,6) NOT NULL default '0.000000',
  `ovamount` double NOT NULL default '0',
  `ovgst` double NOT NULL default '0',
  `ovfreight` double NOT NULL default '0',
  `ovdiscount` double NOT NULL default '0',
  `diffonexch` double NOT NULL default '0',
  `alloc` double NOT NULL default '0',
  `invtext` text,
  `shipvia` varchar(10) NOT NULL default '',
  `edisent` tinyint(4) NOT NULL default '0',
  `consignment` varchar(15) NOT NULL default '',
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
  CONSTRAINT `debtortrans_ibfk_1` FOREIGN KEY (`debtorno`) REFERENCES `custbranch` (`debtorno`),
  CONSTRAINT `debtortrans_ibfk_2` FOREIGN KEY (`type`) REFERENCES `systypes` (`TypeID`),
  CONSTRAINT `debtortrans_ibfk_3` FOREIGN KEY (`prd`) REFERENCES `periods` (`PeriodNo`)
)",
$db);

CreateTable('discountmatrix',
"CREATE TABLE `discountmatrix` (
  `salestype` char(2) NOT NULL default '',
  `discountcategory` char(2) NOT NULL default '',
  `quantitybreak` int(11) NOT NULL default '1',
  `discountrate` double(16,4) NOT NULL default '0.0000',
  PRIMARY KEY (`salestype`,`discountcategory`,`quantitybreak`),
  KEY `QuantityBreak` (`quantitybreak`),
  KEY `DiscountCategory` (`discountcategory`),
  KEY `SalesType` (`salestype`),
  CONSTRAINT `discountmatrix_ibfk_1` FOREIGN KEY (`salestype`) REFERENCES `salestypes` (`TypeAbbrev`)
)",
$db);

CreateTable('edi_orders_seg_groups',
"CREATE TABLE `edi_orders_seg_groups` (
  `seggroupno` tinyint(4) NOT NULL default '0',
  `maxoccur` int(4) NOT NULL default '0',
  `parentseggroup` tinyint(4) NOT NULL default '0',
  PRIMARY KEY (`seggroupno`)
)",
$db);

CreateTable('edi_orders_segs',
"CREATE TABLE `edi_orders_segs` (
  `id` int(11) NOT NULL auto_increment,
  `segtag` char(3) NOT NULL default '',
  `seggroup` tinyint(4) NOT NULL default '0',
  `maxoccur` tinyint(4) NOT NULL default '0',
  PRIMARY KEY (`id`),
  KEY `SegTag` (`segtag`),
  KEY `SegNo` (`seggroup`)
)",
$db);

CreateTable('ediitemmapping',
"CREATE TABLE `ediitemmapping` (
  `supporcust` varchar(4) NOT NULL default '',
  `partnercode` varchar(10) NOT NULL default '',
  `stockid` varchar(20) NOT NULL default '',
  `partnerstockid` varchar(50) NOT NULL default '',
  PRIMARY KEY (`supporcust`,`partnercode`,`stockid`),
  KEY `PartnerCode` (`partnercode`),
  KEY `StockID` (`stockid`),
  KEY `PartnerStockID` (`partnerstockid`),
  KEY `SuppOrCust` (`supporcust`)
)",
$db);

CreateTable('edimessageformat',
"CREATE TABLE `edimessageformat` (
  `id` int(11) NOT NULL auto_increment,
  `partnercode` varchar(10) NOT NULL default '',
  `messagetype` varchar(6) NOT NULL default '',
  `section` varchar(7) NOT NULL default '',
  `sequenceno` int(11) NOT NULL default '0',
  `linetext` varchar(70) NOT NULL default '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `PartnerCode` (`partnercode`,`messagetype`,`sequenceno`),
  KEY `Section` (`section`)
)",
$db);

CreateTable('freightcosts',
"CREATE TABLE `freightcosts` (
  `shipcostfromid` int(11) NOT NULL auto_increment,
  `locationfrom` varchar(5) NOT NULL default '',
  `destination` varchar(40) NOT NULL default '',
  `shipperid` int(11) NOT NULL default '0',
  `cubrate` double(16,2) NOT NULL default '0.00',
  `kgrate` double(16,2) NOT NULL default '0.00',
  `maxkgs` double(16,2) NOT NULL default '999999.00',
  `maxcub` double(16,2) NOT NULL default '999999.00',
  `fixedprice` double(16,2) NOT NULL default '0.00',
  `minimumchg` double(16,2) NOT NULL default '0.00',
  PRIMARY KEY (`shipcostfromid`),
  KEY `Destination` (`destination`),
  KEY `LocationFrom` (`locationfrom`),
  KEY `ShipperID` (`shipperid`),
  KEY `Destination_2` (`destination`,`locationfrom`,`shipperid`),
  CONSTRAINT `freightcosts_ibfk_1` FOREIGN KEY (`locationfrom`) REFERENCES `locations` (`LocCode`),
  CONSTRAINT `freightcosts_ibfk_2` FOREIGN KEY (`shipperid`) REFERENCES `shippers` (`Shipper_ID`)
)",
$db);

CreateTable('gltrans',
"CREATE TABLE `gltrans` (
  `counterindex` int(11) NOT NULL auto_increment,
  `type` smallint(6) NOT NULL default '0',
  `typeno` bigint(16) NOT NULL default '1',
  `chequeno` int(11) NOT NULL default '0',
  `trandate` date NOT NULL default '0000-00-00',
  `periodno` smallint(6) NOT NULL default '0',
  `account` int(11) NOT NULL default '0',
  `narrative` varchar(200) NOT NULL default '',
  `amount` double NOT NULL default '0',
  `posted` tinyint(4) NOT NULL default '0',
  `jobref` varchar(20) NOT NULL default '',
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
  CONSTRAINT `gltrans_ibfk_2` FOREIGN KEY (`type`) REFERENCES `systypes` (`TypeID`),
  CONSTRAINT `gltrans_ibfk_3` FOREIGN KEY (`periodno`) REFERENCES `periods` (`PeriodNo`)
)",
$db);

CreateTable('grns',
"CREATE TABLE `grns` (
  `grnbatch` smallint(6) NOT NULL default '0',
  `grnno` int(11) NOT NULL auto_increment,
  `podetailitem` int(11) NOT NULL default '0',
  `itemcode` varchar(20) NOT NULL default '',
  `deliverydate` date NOT NULL default '0000-00-00',
  `itemdescription` varchar(100) NOT NULL default '',
  `qtyrecd` double(16,4) NOT NULL default '0.0000',
  `quantityinv` double(16,4) NOT NULL default '0.0000',
  `supplierid` varchar(10) NOT NULL default '',
  PRIMARY KEY (`grnno`),
  KEY `DeliveryDate` (`deliverydate`),
  KEY `ItemCode` (`itemcode`),
  KEY `PODetailItem` (`podetailitem`),
  KEY `SupplierID` (`supplierid`),
  CONSTRAINT `grns_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`SupplierID`),
  CONSTRAINT `grns_ibfk_2` FOREIGN KEY (`podetailitem`) REFERENCES `purchorderdetails` (`PODetailItem`)
)",
$db);

CreateTable('holdreasons',
"CREATE TABLE `holdreasons` (
  `reasoncode` smallint(6) NOT NULL default '1',
  `reasondescription` char(30) NOT NULL default '',
  `dissallowinvoices` tinyint(4) NOT NULL default '-1',
  PRIMARY KEY (`reasoncode`),
  KEY `ReasonCode` (`reasoncode`),
  KEY `ReasonDescription` (`reasondescription`)
)",
$db);

CreateTable('lastcostrollup',
"CREATE TABLE `lastcostrollup` (
  `stockid` char(20) NOT NULL default '',
  `totalonhand` double(16,4) NOT NULL default '0.0000',
  `matcost` decimal(20,4) NOT NULL default '0.0000',
  `labcost` decimal(20,4) NOT NULL default '0.0000',
  `oheadcost` decimal(20,4) NOT NULL default '0.0000',
  `categoryid` char(6) NOT NULL default '',
  `stockact` int(11) NOT NULL default '0',
  `adjglact` int(11) NOT NULL default '0',
  `newmatcost` decimal(20,4) NOT NULL default '0.0000',
  `newlabcost` decimal(20,4) NOT NULL default '0.0000',
  `newoheadcost` decimal(20,4) NOT NULL default '0.0000'
)",
$db);

CreateTable('locations',
"CREATE TABLE `locations` (
  `loccode` varchar(5) NOT NULL default '',
  `locationname` varchar(50) NOT NULL default '',
  `deladd1` varchar(40) NOT NULL default '',
  `deladd2` varchar(40) NOT NULL default '',
  `deladd3` varchar(40) NOT NULL default '',
  `tel` varchar(30) NOT NULL default '',
  `fax` varchar(30) NOT NULL default '',
  `email` varchar(55) NOT NULL default '',
  `contact` varchar(30) NOT NULL default '',
  `taxauthority` tinyint(4) NOT NULL default '1',
  PRIMARY KEY (`loccode`)
)",
$db);

CreateTable('locstock',
"CREATE TABLE `locstock` (
  `loccode` varchar(5) NOT NULL default '',
  `stockid` varchar(20) NOT NULL default '',
  `quantity` double(16,1) NOT NULL default '0.0',
  `reorderlevel` bigint(20) NOT NULL default '0',
  PRIMARY KEY (`loccode`,`stockid`),
  KEY `StockID` (`stockid`),
  CONSTRAINT `locstock_ibfk_1` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `locstock_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`StockID`)
)",
$db);

CreateTable('loctransfers',
"CREATE TABLE `loctransfers` (
  `reference` int(11) NOT NULL default '0',
  `stockid` varchar(20) NOT NULL default '',
  `shipqty` int(11) NOT NULL default '0',
  `recqty` int(11) NOT NULL default '0',
  `shipdate` date NOT NULL default '0000-00-00',
  `recdate` date NOT NULL default '0000-00-00',
  `shiploc` varchar(7) NOT NULL default '',
  `recloc` varchar(7) NOT NULL default '',
  KEY `Reference` (`reference`,`stockid`),
  KEY `ShipLoc` (`shiploc`),
  KEY `RecLoc` (`recloc`),
  KEY `StockID` (`stockid`),
  CONSTRAINT `loctransfers_ibfk_1` FOREIGN KEY (`shiploc`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `loctransfers_ibfk_2` FOREIGN KEY (`recloc`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `loctransfers_ibfk_3` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`StockID`)
)",
$db);

CreateTable('orderdeliverydifferenceslog',
"CREATE TABLE `orderdeliverydifferenceslog` (
  `orderno` int(11) NOT NULL default '0',
  `invoiceno` int(11) NOT NULL default '0',
  `stockid` varchar(20) NOT NULL default '',
  `quantitydiff` double(16,4) NOT NULL default '0.0000',
  `debtorno` varchar(10) NOT NULL default '',
  `branch` varchar(10) NOT NULL default '',
  `can_or_bo` char(3) NOT NULL default 'CAN',
  PRIMARY KEY (`orderno`,`invoiceno`,`stockid`),
  KEY `StockID` (`stockid`),
  KEY `DebtorNo` (`debtorno`,`branch`),
  KEY `Can_or_BO` (`can_or_bo`),
  KEY `OrderNo` (`orderno`),
  CONSTRAINT `orderdeliverydifferenceslog_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`StockID`),
  CONSTRAINT `orderdeliverydifferenceslog_ibfk_2` FOREIGN KEY (`debtorno`, `branch`) REFERENCES `custbranch` (`debtorno`, `branchcode`),
  CONSTRAINT `orderdeliverydifferenceslog_ibfk_3` FOREIGN KEY (`orderno`) REFERENCES `salesorders` (`OrderNo`)
)",
$db);

createTable('paymentmethods',
"CREATE TABLE `paymentmethods` (
  `paymentid` tinyint(4) NOT NULL auto_increment,
  `paymentname` varchar(15) NOT NULL default '',
  `paymenttype` int(11) NOT NULL default '1',
  `receipttype` int(11) NOT NULL default '1',
  PRIMARY KEY (`paymentid`)
)",
$db);

CreateTable('paymentterms',
"CREATE TABLE `paymentterms` (
  `termsindicator` char(2) NOT NULL default '',
  `terms` char(40) NOT NULL default '',
  `daysbeforedue` smallint(6) NOT NULL default '0',
  `dayinfollowingmonth` smallint(6) NOT NULL default '0',
  PRIMARY KEY (`termsindicator`),
  KEY `DaysBeforeDue` (`daysbeforedue`),
  KEY `DayInFollowingMonth` (`dayinfollowingmonth`)
)",
$db);

CreateTable('periods',
"CREATE TABLE `periods` (
  `periodno` smallint(6) NOT NULL default '0',
  `lastdate_in_period` date NOT NULL default '0000-00-00',
  PRIMARY KEY (`periodno`),
  KEY `LastDate_in_Period` (`lastdate_in_period`)
)",
$db);

CreateTable('prices',
"CREATE TABLE `prices` (
  `stockid` varchar(20) NOT NULL default '',
  `typeabbrev` char(2) NOT NULL default '',
  `currabrev` char(3) NOT NULL default '',
  `debtorno` varchar(10) NOT NULL default '',
  `price` decimal(20,4) NOT NULL default '0.0000',
  `branchcode` varchar(10) NOT NULL default '',
  PRIMARY KEY (`stockid`,`typeabbrev`,`currabrev`,`debtorno`),
  KEY `CurrAbrev` (`currabrev`),
  KEY `DebtorNo` (`debtorno`),
  KEY `StockID` (`stockid`),
  KEY `TypeAbbrev` (`typeabbrev`),
  CONSTRAINT `prices_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`StockID`),
  CONSTRAINT `prices_ibfk_2` FOREIGN KEY (`currabrev`) REFERENCES `currencies` (`currabrev`),
  CONSTRAINT `prices_ibfk_3` FOREIGN KEY (`typeabbrev`) REFERENCES `salestypes` (`TypeAbbrev`)
)",
$db);

CreateTable('purchdata',
"CREATE TABLE `purchdata` (
  `supplierno` char(10) NOT NULL default '',
  `stockid` char(20) NOT NULL default '',
  `price` decimal(20,4) NOT NULL default '0.0000',
  `suppliersuom` char(50) NOT NULL default '',
  `conversionfactor` double(16,4) NOT NULL default '1.0000',
  `supplierdescription` char(50) NOT NULL default '',
  `leadtime` smallint(6) NOT NULL default '1',
  `preferred` tinyint(4) NOT NULL default '0',
  PRIMARY KEY (`supplierno`,`stockid`),
  KEY `StockID` (`stockid`),
  KEY `SupplierNo` (`supplierno`),
  KEY `Preferred` (`preferred`),
  CONSTRAINT `purchdata_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`StockID`),
  CONSTRAINT `purchdata_ibfk_2` FOREIGN KEY (`supplierno`) REFERENCES `suppliers` (`SupplierID`)
)",
$db);

CreateTable('purchorderdetails',
"CREATE TABLE `purchorderdetails` (
  `podetailitem` int(11) NOT NULL auto_increment,
  `orderno` int(11) NOT NULL default '0',
  `itemcode` varchar(20) NOT NULL default '',
  `deliverydate` date NOT NULL default '0000-00-00',
  `itemdescription` varchar(100) NOT NULL default '',
  `glcode` int(11) NOT NULL default '0',
  `qtyinvoiced` double(16,4) NOT NULL default '0.0000',
  `unitprice` double(16,4) NOT NULL default '0.0000',
  `actprice` double(16,4) NOT NULL default '0.0000',
  `stdcostunit` double(16,4) NOT NULL default '0.0000',
  `quantityord` double(16,4) NOT NULL default '0.0000',
  `quantityrecd` double(16,4) NOT NULL default '0.0000',
  `shiptref` int(1) NOT NULL default '0',
  `jobref` varchar(20) NOT NULL default '',
  `completed` tinyint(4) NOT NULL default '0',
  PRIMARY KEY (`podetailitem`),
  KEY `DeliveryDate` (`deliverydate`),
  KEY `GLCode` (`glcode`),
  KEY `ItemCode` (`itemcode`),
  KEY `JobRef` (`jobref`),
  KEY `OrderNo` (`orderno`),
  KEY `ShiptRef` (`shiptref`),
  KEY `Completed` (`completed`),
  CONSTRAINT `purchorderdetails_ibfk_1` FOREIGN KEY (`orderno`) REFERENCES `purchorders` (`OrderNo`)
)",
$db);

CreateTable('purchorders',
"CREATE TABLE `purchorders` (
  `orderno` int(11) NOT NULL auto_increment,
  `supplierno` varchar(10) NOT NULL default '',
  `comments` longblob,
  `orddate` datetime NOT NULL default '0000-00-00 00:00:00',
  `rate` double(16,4) NOT NULL default '1.0000',
  `dateprinted` datetime default NULL,
  `allowprint` tinyint(4) NOT NULL default '1',
  `initiator` varchar(10) default NULL,
  `requisitionno` varchar(15) default NULL,
  `intostocklocation` varchar(5) NOT NULL default '',
  `deladd1` varchar(40) NOT NULL default '',
  `deladd2` varchar(40) NOT NULL default '',
  `deladd3` varchar(40) NOT NULL default '',
  `deladd4` varchar(40) NOT NULL default '',
  PRIMARY KEY (`orderno`),
  KEY `OrdDate` (`orddate`),
  KEY `SupplierNo` (`supplierno`),
  KEY `IntoStockLocation` (`intostocklocation`),
  KEY `AllowPrintPO` (`allowprint`),
  CONSTRAINT `purchorders_ibfk_1` FOREIGN KEY (`supplierno`) REFERENCES `suppliers` (`SupplierID`),
  CONSTRAINT `purchorders_ibfk_2` FOREIGN KEY (`intostocklocation`) REFERENCES `locations` (`loccode`)
)",
$db);

CreateTable('recurringsalesorders',
"CREATE TABLE `recurringsalesorders` (
  `recurrorderno` int(11) NOT NULL auto_increment,
  `debtorno` varchar(10) NOT NULL default '',
  `branchcode` varchar(10) NOT NULL default '',
  `customerref` varchar(50) NOT NULL default '',
  `buyername` varchar(50) default NULL,
  `comments` longblob,
  `orddate` date NOT NULL default '0000-00-00',
  `ordertype` char(2) NOT NULL default '',
  `shipvia` int(11) NOT NULL default '0',
  `deladd1` varchar(40) NOT NULL default '',
  `deladd2` varchar(20) NOT NULL default '',
  `deladd3` varchar(15) NOT NULL default '',
  `deladd4` varchar(15) default NULL,
  `contactphone` varchar(25) default NULL,
  `contactemail` varchar(25) default NULL,
  `deliverto` varchar(40) NOT NULL default '',
  `freightcost` double NOT NULL default '0',
  `fromstkloc` varchar(5) NOT NULL default '',
  `lastrecurrence` date NOT NULL default '0000-00-00',
  `stopdate` date NOT NULL default '0000-00-00',
  `frequency` tinyint(4) NOT NULL default '1',
  `autoinvoice` tinyint(4) NOT NULL default '0',
  PRIMARY KEY (`recurrorderno`),
  KEY `debtorno` (`debtorno`),
  KEY `orddate` (`orddate`),
  KEY `ordertype` (`ordertype`),
  KEY `locationindex` (`fromstkloc`),
  KEY `branchcode` (`branchcode`,`debtorno`),
  CONSTRAINT `recurringsalesorders_ibfk_1` FOREIGN KEY (`branchcode`, `debtorno`) REFERENCES `custbranch` (`branchcode`, `debtorno`)
)",
$db);

CreateTable('recurrsalesorderdetails',
"CREATE TABLE `recurrsalesorderdetails` (
  `recurrorderno` int(11) NOT NULL default '0',
  `stkcode` varchar(20) NOT NULL default '',
  `unitprice` double(16,4) NOT NULL default '0.0000',
  `quantity` double(16,4) NOT NULL default '0.0000',
  `discountpercent` double(16,4) NOT NULL default '0.0000',
  `narrative` text NOT NULL,
  PRIMARY KEY (`recurrorderno`,`stkcode`),
  KEY `orderno` (`recurrorderno`),
  KEY `stkcode` (`stkcode`),
  CONSTRAINT `recurrsalesorderdetails_ibfk_1` FOREIGN KEY (`recurrorderno`) REFERENCES `recurringsalesorders` (`recurrorderno`),
  CONSTRAINT `recurrsalesorderdetails_ibfk_2` FOREIGN KEY (`stkcode`) REFERENCES `stockmaster` (`stockid`)
)",
$db);

CreateTable('reportcolumns',
"CREATE TABLE `reportcolumns` (
  `reportid` smallint(6) NOT NULL default '0',
  `colno` smallint(6) NOT NULL default '0',
  `heading1` varchar(15) NOT NULL default '',
  `heading2` varchar(15) default NULL,
  `calculation` tinyint(1) NOT NULL default '0',
  `periodfrom` smallint(6) default NULL,
  `periodto` smallint(6) default NULL,
  `datatype` varchar(15) default NULL,
  `colnumerator` tinyint(4) default NULL,
  `coldenominator` tinyint(4) default NULL,
  `calcoperator` char(1) default NULL,
  `budgetoractual` tinyint(1) NOT NULL default '0',
  `valformat` char(1) NOT NULL default 'N',
  `constant` double NOT NULL default '0',
  PRIMARY KEY (`reportid`,`colno`),
  CONSTRAINT `reportcolumns_ibfk_1` FOREIGN KEY (`reportid`) REFERENCES `reportheaders` (`ReportID`)
)",
$db);

CreateTable('reportheaders',
"CREATE TABLE `reportheaders` (
  `reportid` smallint(6) NOT NULL auto_increment,
  `reportheading` varchar(80) NOT NULL default '',
  `groupbydata1` varchar(15) NOT NULL default '',
  `newpageafter1` tinyint(1) NOT NULL default '0',
  `lower1` varchar(10) NOT NULL default '',
  `upper1` varchar(10) NOT NULL default '',
  `groupbydata2` varchar(15) default NULL,
  `newpageafter2` tinyint(1) NOT NULL default '0',
  `lower2` varchar(10) default NULL,
  `upper2` varchar(10) default NULL,
  `groupbydata3` varchar(15) default NULL,
  `newpageafter3` tinyint(1) NOT NULL default '0',
  `lower3` varchar(10) default NULL,
  `upper3` varchar(10) default NULL,
  `groupbydata4` varchar(15) NOT NULL default '',
  `newpageafter4` tinyint(1) NOT NULL default '0',
  `upper4` varchar(10) NOT NULL default '',
  `lower4` varchar(10) NOT NULL default '',
  PRIMARY KEY (`reportid`),
  KEY `ReportHeading` (`reportheading`)
)",
$db);

CreateTable('salesanalysis',
"CREATE TABLE `salesanalysis` (
  `typeabbrev` char(2) NOT NULL default '',
  `periodno` smallint(6) NOT NULL default '0',
  `amt` double(16,4) NOT NULL default '0.0000',
  `cost` double(16,4) NOT NULL default '0.0000',
  `cust` varchar(10) NOT NULL default '',
  `custbranch` varchar(10) NOT NULL default '',
  `qty` double(16,4) NOT NULL default '0.0000',
  `disc` double(16,4) NOT NULL default '0.0000',
  `stockid` varchar(20) NOT NULL default '',
  `area` char(2) NOT NULL default '',
  `budgetoractual` tinyint(1) NOT NULL default '0',
  `salesperson` char(3) NOT NULL default '',
  `stkcategory` varchar(6) NOT NULL default '',
  `id` int(11) NOT NULL auto_increment,
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
)",
$db);

CreateTable('salescat',
"CREATE TABLE `salescat` (
  `salescatid` tinyint(4) NOT NULL auto_increment,
  `parentcatid` tinyint(4) default NULL,
  `salescatname` varchar(30) default NULL,
  PRIMARY KEY (`salescatid`)
)",
$db);

CreateTable('salescatprod',
"CREATE TABLE `salescatprod` (
  `salescatid` tinyint(4) NOT NULL default '0',
  `stockid` varchar(20) NOT NULL default '',
  PRIMARY KEY (`salescatid`,`stockid`),
  KEY `salescatid` (`salescatid`),
  KEY `stockid` (`stockid`),
  CONSTRAINT `salescatprod_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `salescatprod_ibfk_2` FOREIGN KEY (`salescatid`) REFERENCES `salescat` (`salescatid`)
)",
$db);

CreateTable('salesglpostings',
"CREATE TABLE `salesglpostings` (
  `id` int(11) NOT NULL auto_increment,
  `area` char(2) NOT NULL default '',
  `stkcat` varchar(6) NOT NULL default '',
  `discountglcode` int(11) NOT NULL default '0',
  `salesglcode` int(11) NOT NULL default '0',
  `salestype` char(2) NOT NULL default 'AN',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Area_StkCat` (`area`,`stkcat`,`salestype`),
  KEY `Area` (`area`),
  KEY `StkCat` (`stkcat`),
  KEY `SalesType` (`salestype`)
)",
$db);

Createtable('salesman',
"CREATE TABLE `salesman` (
  `salesmancode` char(3) NOT NULL default '',
  `salesmanname` char(30) NOT NULL default '',
  `smantel` char(20) NOT NULL default '',
  `smanfax` char(20) NOT NULL default '',
  `commissionrate1` double(16,4) NOT NULL default '0.0000',
  `breakpoint` decimal(20,4) NOT NULL default '0.0000',
  `commissionrate2` double(16,4) NOT NULL default '0.0000',
  PRIMARY KEY (`salesmancode`)
)",
$db);

CreateTable('salesorderdetails',
"CREATE TABLE `salesorderdetails` (
  `orderno` int(11) NOT NULL default '0',
  `stkcode` varchar(20) NOT NULL default '',
  `qtyinvoiced` double(16,4) NOT NULL default '0.0000',
  `unitprice` double(16,4) NOT NULL default '0.0000',
  `quantity` double(16,4) NOT NULL default '0.0000',
  `estimate` tinyint(4) NOT NULL default '0',
  `discountpercent` double(16,4) NOT NULL default '0.0000',
  `actualdispatchdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `completed` tinyint(1) NOT NULL default '0',
  `narrative` text NOT NULL,
  PRIMARY KEY (`orderno`,`stkcode`),
  KEY `OrderNo` (`orderno`),
  KEY `StkCode` (`stkcode`),
  KEY `Completed` (`completed`),
  CONSTRAINT `salesorderdetails_ibfk_1` FOREIGN KEY (`orderno`) REFERENCES `salesorders` (`OrderNo`),
  CONSTRAINT `salesorderdetails_ibfk_2` FOREIGN KEY (`stkcode`) REFERENCES `stockmaster` (`StockID`)
)",
$db);

CreateTable('salesorders',
"CREATE TABLE `salesorders` (
  `orderno` int(11) NOT NULL auto_increment,
  `debtorno` varchar(10) NOT NULL default '',
  `branchcode` varchar(10) NOT NULL default '',
  `customerref` varchar(50) NOT NULL default '',
  `buyername` varchar(50) default NULL,
  `comments` longblob,
  `orddate` date NOT NULL default '0000-00-00',
  `ordertype` char(2) NOT NULL default '',
  `shipvia` int(11) NOT NULL default '0',
  `deladd1` varchar(40) NOT NULL default '',
  `deladd2` varchar(20) NOT NULL default '',
  `deladd3` varchar(15) NOT NULL default '',
  `deladd4` varchar(15) default NULL,
  `contactphone` varchar(25) default NULL,
  `contactemail` varchar(25) default NULL,
  `deliverto` varchar(40) NOT NULL default '',
  `deliverblind` tinyint(1) default '1',
  `freightcost` double NOT NULL default '0',
  `fromstkloc` varchar(5) NOT NULL default '',
  `deliverydate` date NOT NULL default '0000-00-00',
  `printedpackingslip` tinyint(4) NOT NULL default '0',
  `datepackingslipprinted` date NOT NULL default '0000-00-00',
  `quotation` tinyint(4) NOT NULL default '0',
  PRIMARY KEY (`orderno`),
  KEY `DebtorNo` (`debtorno`),
  KEY `OrdDate` (`orddate`),
  KEY `OrderType` (`ordertype`),
  KEY `LocationIndex` (`fromstkloc`),
  KEY `BranchCode` (`branchcode`,`debtorno`),
  KEY `ShipVia` (`shipvia`),
  KEY `quotation` (`quotation`),
  CONSTRAINT `salesorders_ibfk_1` FOREIGN KEY (`branchcode`, `debtorno`) REFERENCES `custbranch` (`branchcode`, `debtorno`),
  CONSTRAINT `salesorders_ibfk_2` FOREIGN KEY (`shipvia`) REFERENCES `shippers` (`Shipper_ID`),
  CONSTRAINT `salesorders_ibfk_3` FOREIGN KEY (`fromstkloc`) REFERENCES `locations` (`loccode`)
)",
$db);

CreateTable('salestypes',
"CREATE TABLE `salestypes` (
  `typeabbrev` char(2) NOT NULL default '',
  `sales_type` char(20) NOT NULL default '',
  PRIMARY KEY (`typeabbrev`),
  KEY `Sales_Type` (`sales_type`)
)",
$db);

CreateTable('scripts',
"CREATE TABLE `scripts` (
  `pageid` smallint(4) NOT NULL auto_increment,
  `filename` varchar(50) NOT NULL default '',
  `pagedescription` text NOT NULL,
  PRIMARY KEY (`pageid`),
  KEY `FileName` (`filename`)
)",
$db);

CreateTable('securitygroups',
"CREATE TABLE `securitygroups` (
  `secroleid` int(11) NOT NULL default '0',
  `tokenid` int(11) NOT NULL default '0',
  PRIMARY KEY (`secroleid`,`tokenid`),
  KEY `secroleid` (`secroleid`),
  KEY `tokenid` (`tokenid`),
  CONSTRAINT `securitygroups_secroleid_fk` FOREIGN KEY (`secroleid`) REFERENCES `securityroles` (`secroleid`),
  CONSTRAINT `securitygroups_tokenid_fk` FOREIGN KEY (`tokenid`) REFERENCES `securitytokens` (`tokenid`)
)",
$db);

CreateTable('securityroles',
"CREATE TABLE `securityroles` (
  `secroleid` int(11) NOT NULL auto_increment,
  `secrolename` text NOT NULL,
  PRIMARY KEY (`secroleid`)
)",
$db);

CreateTable('securitytokens',
"CREATE TABLE `securitytokens` (
  `tokenid` int(11) NOT NULL default '0',
  `tokenname` text NOT NULL,
  PRIMARY KEY (`tokenid`)
)",
$db);

CreateTable('shipmentcharges',
"CREATE TABLE `shipmentcharges` (
  `shiptchgid` int(11) NOT NULL auto_increment,
  `shiptref` int(11) NOT NULL default '0',
  `transtype` smallint(6) NOT NULL default '0',
  `transno` int(11) NOT NULL default '0',
  `stockid` varchar(20) NOT NULL default '',
  `value` double NOT NULL default '0',
  PRIMARY KEY (`shiptchgid`),
  KEY `TransType` (`transtype`,`transno`),
  KEY `ShiptRef` (`shiptref`),
  KEY `StockID` (`stockid`),
  KEY `TransType_2` (`transtype`),
  CONSTRAINT `shipmentcharges_ibfk_1` FOREIGN KEY (`shiptref`) REFERENCES `shipments` (`ShiptRef`),
  CONSTRAINT `shipmentcharges_ibfk_2` FOREIGN KEY (`transtype`) REFERENCES `systypes` (`TypeID`)
)",
$db);

CreateTable('shipments',
"CREATE TABLE `shipments` (
  `shiptref` int(11) NOT NULL default '0',
  `voyageref` varchar(20) NOT NULL default '0',
  `vessel` varchar(50) NOT NULL default '',
  `eta` datetime NOT NULL default '0000-00-00 00:00:00',
  `accumvalue` double(16,4) NOT NULL default '0.0000',
  `supplierid` varchar(10) NOT NULL default '',
  `closed` tinyint(4) NOT NULL default '0',
  PRIMARY KEY (`shiptref`),
  KEY `ETA` (`eta`),
  KEY `SupplierID` (`supplierid`),
  KEY `ShipperRef` (`voyageref`),
  KEY `Vessel` (`vessel`),
  CONSTRAINT `shipments_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`SupplierID`)
)",
$db);

CreateTable('shippers',
"CREATE TABLE `shippers` (
  `shipper_id` int(11) NOT NULL auto_increment,
  `shippername` char(40) NOT NULL default '',
  `mincharge` double(16,4) NOT NULL default '0.0000',
  PRIMARY KEY (`shipper_id`)
)",
$db);

CreateTable('stockcategory',
"CREATE TABLE `stockcategory` (
  `categoryid` char(6) NOT NULL default '',
  `categorydescription` char(20) NOT NULL default '',
  `stocktype` char(1) NOT NULL default 'F',
  `stockact` int(11) NOT NULL default '0',
  `adjglact` int(11) NOT NULL default '0',
  `purchpricevaract` int(11) NOT NULL default '80000',
  `materialuseagevarac` int(11) NOT NULL default '80000',
  `wipact` int(11) NOT NULL default '0',
  PRIMARY KEY (`categoryid`),
  KEY `CategoryDescription` (`categorydescription`),
  KEY `StockType` (`stocktype`)
)",
$db);

CreateTable('stockcheckfreeze',
"CREATE TABLE `stockcheckfreeze` (
  `stockid` varchar(20) NOT NULL default '',
  `loccode` varchar(5) NOT NULL default '',
  `qoh` double NOT NULL default '0',
  PRIMARY KEY (`stockid`),
  KEY `LocCode` (`loccode`),
  CONSTRAINT `stockcheckfreeze_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`StockID`),
  CONSTRAINT `stockcheckfreeze_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
)",
$db);

CreateTable('stockcounts',
"CREATE TABLE `stockcounts` (
  `id` int(11) NOT NULL auto_increment,
  `stockid` varchar(20) NOT NULL default '',
  `loccode` varchar(5) NOT NULL default '',
  `qtycounted` double NOT NULL default '0',
  `reference` varchar(20) NOT NULL default '',
  PRIMARY KEY (`id`),
  KEY `StockID` (`stockid`),
  KEY `LocCode` (`loccode`),
  CONSTRAINT `stockcounts_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`StockID`),
  CONSTRAINT `stockcounts_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
)",
$db);

CreateTable('stockmaster',
"CREATE TABLE `stockmaster` (
  `stockid` varchar(20) NOT NULL default '',
  `categoryid` varchar(6) NOT NULL default '',
  `description` varchar(50) NOT NULL default '',
  `longdescription` text NOT NULL,
  `units` varchar(20) NOT NULL default 'each',
  `mbflag` char(1) NOT NULL default 'B',
  `lastcurcostdate` date NOT NULL default '1800-01-01',
  `actualcost` decimal(20,4) NOT NULL default '0.0000',
  `lastcost` decimal(20,4) NOT NULL default '0.0000',
  `materialcost` decimal(20,4) NOT NULL default '0.0000',
  `labourcost` decimal(20,4) NOT NULL default '0.0000',
  `overheadcost` decimal(20,4) NOT NULL default '0.0000',
  `lowestlevel` smallint(6) NOT NULL default '0',
  `discontinued` tinyint(4) NOT NULL default '0',
  `controlled` tinyint(4) NOT NULL default '0',
  `eoq` double(10,2) NOT NULL default '0.00',
  `volume` decimal(20,4) NOT NULL default '0.0000',
  `kgs` decimal(20,4) NOT NULL default '0.0000',
  `barcode` varchar(50) NOT NULL default '',
  `discountcategory` char(2) NOT NULL default '',
  `taxlevel` tinyint(4) NOT NULL default '1',
  `serialised` tinyint(4) NOT NULL default '0',
  `decimalplaces` tinyint(4) NOT NULL default '0',
  PRIMARY KEY (`stockid`),
  KEY `CategoryID` (`categoryid`),
  KEY `Description` (`description`),
  KEY `LastCurCostDate` (`lastcurcostdate`),
  KEY `MBflag` (`mbflag`),
  KEY `StockID` (`stockid`,`categoryid`),
  KEY `Controlled` (`controlled`),
  KEY `DiscountCategory` (`discountcategory`),
  CONSTRAINT `stockmaster_ibfk_1` FOREIGN KEY (`categoryid`) REFERENCES `stockcategory` (`categoryid`)
)",
$db);

CreateTable('stockmoves',
"CREATE TABLE `stockmoves` (
  `stkmoveno` int(11) NOT NULL auto_increment,
  `stockid` varchar(20) NOT NULL default '',
  `type` smallint(6) NOT NULL default '0',
  `transno` int(11) NOT NULL default '0',
  `loccode` varchar(5) NOT NULL default '',
  `trandate` date NOT NULL default '0000-00-00',
  `debtorno` varchar(10) NOT NULL default '',
  `branchcode` varchar(10) NOT NULL default '',
  `price` decimal(20,4) NOT NULL default '0.0000',
  `prd` smallint(6) NOT NULL default '0',
  `reference` varchar(40) NOT NULL default '',
  `qty` double(16,4) NOT NULL default '1.0000',
  `discountpercent` double(16,4) NOT NULL default '0.0000',
  `standardcost` double(16,4) NOT NULL default '0.0000',
  `show_on_inv_crds` tinyint(4) NOT NULL default '1',
  `newqoh` double NOT NULL default '0',
  `hidemovt` tinyint(4) NOT NULL default '0',
  `taxrate` double NOT NULL default '0',
  `narrative` text NOT NULL,
  PRIMARY KEY (`stkmoveno`),
  KEY `DebtorNo` (`debtorno`),
  KEY `LocCode` (`loccode`),
  KEY `Prd` (`prd`),
  KEY `StockID` (`stockid`,`loccode`),
  KEY `StockID_2` (`stockid`),
  KEY `TranDate` (`trandate`),
  KEY `TransNo` (`transno`),
  KEY `Type` (`type`),
  KEY `Show_On_Inv_Crds` (`show_on_inv_crds`),
  KEY `Hide` (`hidemovt`),
  CONSTRAINT `stockmoves_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockmoves_ibfk_2` FOREIGN KEY (`type`) REFERENCES `systypes` (`TypeID`),
  CONSTRAINT `stockmoves_ibfk_3` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `stockmoves_ibfk_4` FOREIGN KEY (`prd`) REFERENCES `periods` (`periodno`)
)",
$db);

CreateTable('stockserialitems',
"CREATE TABLE `stockserialitems` (
  `stockid` varchar(20) NOT NULL default '',
  `loccode` varchar(5) NOT NULL default '',
  `serialno` varchar(30) NOT NULL default '',
  `quantity` double NOT NULL default '0',
  PRIMARY KEY (`stockid`,`serialno`,`loccode`),
  KEY `StockID` (`stockid`),
  KEY `LocCode` (`loccode`),
  CONSTRAINT `stockserialitems_ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`),
  CONSTRAINT `stockserialitems_ibfk_2` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
)",
$db);

CreateTable('stockserialmoves',
"CREATE TABLE `stockserialmoves` (
  `stkitmmoveno` int(11) NOT NULL auto_increment,
  `stockmoveno` int(11) NOT NULL default '0',
  `stockid` varchar(20) NOT NULL default '',
  `serialno` varchar(30) NOT NULL default '',
  `moveqty` double NOT NULL default '0',
  PRIMARY KEY (`stkitmmoveno`),
  KEY `StockMoveNo` (`stockmoveno`),
  KEY `StockID_SN` (`stockid`,`serialno`),
  CONSTRAINT `stockserialmoves_ibfk_1` FOREIGN KEY (`stockmoveno`) REFERENCES `stockmoves` (`stkmoveno`),
  CONSTRAINT `stockserialmoves_ibfk_2` FOREIGN KEY (`stockid`, `serialno`) REFERENCES `stockserialitems` (`stockid`, `serialno`)
)",
$db);

CreateTable('suppallocs',
"CREATE TABLE `suppallocs` (
  `id` int(11) NOT NULL auto_increment,
  `amt` double NOT NULL default '0',
  `datealloc` date NOT NULL default '0000-00-00',
  `transid_allocfrom` int(11) NOT NULL default '0',
  `transid_allocto` int(11) NOT NULL default '0',
  PRIMARY KEY (`id`),
  KEY `TransID_AllocFrom` (`transid_allocfrom`),
  KEY `TransID_AllocTo` (`transid_allocto`),
  KEY `DateAlloc` (`datealloc`),
  CONSTRAINT `suppallocs_ibfk_1` FOREIGN KEY (`transid_allocfrom`) REFERENCES `supptrans` (`ID`),
  CONSTRAINT `suppallocs_ibfk_2` FOREIGN KEY (`transid_allocto`) REFERENCES `supptrans` (`ID`)
)",
$db);

CreateTable('suppliercontacts',
"CREATE TABLE `suppliercontacts` (
  `supplierid` varchar(10) NOT NULL default '',
  `contact` varchar(30) NOT NULL default '',
  `position` varchar(30) NOT NULL default '',
  `tel` varchar(30) NOT NULL default '',
  `fax` varchar(30) NOT NULL default '',
  `mobile` varchar(30) NOT NULL default '',
  `email` varchar(55) NOT NULL default '',
  `ordercontact` tinyint(1) NOT NULL default '0',
  PRIMARY KEY (`supplierid`,`contact`),
  KEY `Contact` (`contact`),
  KEY `SupplierID` (`supplierid`),
  CONSTRAINT `suppliercontacts_ibfk_1` FOREIGN KEY (`supplierid`) REFERENCES `suppliers` (`SupplierID`)
)",
$db);

CreateTable('suppliers',
"CREATE TABLE `suppliers` (
  `supplierid` char(10) NOT NULL default '',
  `suppname` char(40) NOT NULL default '',
  `address1` char(40) NOT NULL default '',
  `address2` char(40) NOT NULL default '',
  `address3` char(40) NOT NULL default '',
  `address4` char(50) NOT NULL default '',
  `currcode` char(3) NOT NULL default '',
  `suppliersince` date NOT NULL default '0000-00-00',
  `paymentterms` char(2) NOT NULL default '',
  `lastpaid` double(16,4) NOT NULL default '0.0000',
  `lastpaiddate` datetime default NULL,
  `bankact` char(16) NOT NULL default '',
  `bankref` char(12) NOT NULL default '',
  `bankpartics` char(12) NOT NULL default '',
  `remittance` tinyint(4) NOT NULL default '1',
  `taxauthority` tinyint(4) NOT NULL default '1',
  PRIMARY KEY (`supplierid`),
  KEY `CurrCode` (`currcode`),
  KEY `PaymentTerms` (`paymentterms`),
  KEY `SupplierID` (`supplierid`),
  KEY `SuppName` (`suppname`),
  KEY `TaxAuthority` (`taxauthority`),
  CONSTRAINT `suppliers_ibfk_1` FOREIGN KEY (`currcode`) REFERENCES `currencies` (`currabrev`),
  CONSTRAINT `suppliers_ibfk_2` FOREIGN KEY (`paymentterms`) REFERENCES `paymentterms` (`termsindicator`),
  CONSTRAINT `suppliers_ibfk_3` FOREIGN KEY (`taxauthority`) REFERENCES `taxauthorities` (`TaxID`)
)",
$db);

CreateTable('supptrans',
"CREATE TABLE `supptrans` (
  `transno` int(11) NOT NULL default '0',
  `type` smallint(6) NOT NULL default '0',
  `supplierno` varchar(10) NOT NULL default '',
  `suppreference` varchar(20) NOT NULL default '',
  `trandate` date NOT NULL default '0000-00-00',
  `duedate` date NOT NULL default '0000-00-00',
  `settled` tinyint(4) NOT NULL default '0',
  `rate` double(16,6) NOT NULL default '1.000000',
  `ovamount` double(16,4) NOT NULL default '0.0000',
  `ovgst` double(16,4) NOT NULL default '0.0000',
  `diffonexch` double(16,4) NOT NULL default '0.0000',
  `alloc` double(16,4) NOT NULL default '0.0000',
  `transtext` longblob,
  `hold` tinyint(4) NOT NULL default '0',
  `id` int(11) NOT NULL auto_increment,
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
  CONSTRAINT `supptrans_ibfk_1` FOREIGN KEY (`type`) REFERENCES `systypes` (`TypeID`),
  CONSTRAINT `supptrans_ibfk_2` FOREIGN KEY (`supplierno`) REFERENCES `suppliers` (`supplierid`)
)",
$db);

CreateTable('systypes',
"CREATE TABLE `systypes` (
  `typeid` smallint(6) NOT NULL default '0',
  `typename` char(50) NOT NULL default '',
  `typeno` int(11) NOT NULL default '1',
  PRIMARY KEY (`typeid`),
  KEY `TypeNo` (`typeno`)
)",
$db);

CreateTable('taxauthlevels',
"CREATE TABLE `taxauthlevels` (
  `taxauthority` tinyint(4) NOT NULL default '1',
  `dispatchtaxauthority` tinyint(4) NOT NULL default '1',
  `level` tinyint(4) NOT NULL default '0',
  `taxrate` double NOT NULL default '0',
  PRIMARY KEY (`taxauthority`,`dispatchtaxauthority`,`level`),
  KEY `TaxAuthority` (`taxauthority`),
  KEY `DispatchTaxAuthority` (`dispatchtaxauthority`),
  CONSTRAINT `taxauthlevels_ibfk_1` FOREIGN KEY (`taxauthority`) REFERENCES `taxauthorities` (`TaxID`),
  CONSTRAINT `taxauthlevels_ibfk_2` FOREIGN KEY (`dispatchtaxauthority`) REFERENCES `taxauthorities` (`TaxID`)
)",
$db);

CreateTable('taxauthorities',
"CREATE TABLE `taxauthorities` (
  `taxid` tinyint(4) NOT NULL auto_increment,
  `description` varchar(20) NOT NULL default '',
  `taxglcode` int(11) NOT NULL default '0',
  `purchtaxglaccount` int(11) NOT NULL default '0',
  `bank` varchar(50) NOT NULL default '',
  `bankacctype` varchar(20) NOT NULL default '',
  `bankacc` varchar(50) NOT NULL default '',
  `bankswift` varchar(30) NOT NULL default '',
  PRIMARY KEY (`taxid`),
  KEY `TaxGLCode` (`taxglcode`),
  KEY `PurchTaxGLAccount` (`purchtaxglaccount`),
  CONSTRAINT `taxauthorities_ibfk_2` FOREIGN KEY (`purchtaxglaccount`) REFERENCES `chartmaster` (`accountcode`)
  CONSTRAINT `taxauthorities_ibfk_1` FOREIGN KEY (`taxglcode`) REFERENCES `chartmaster` (`accountcode`),
)",
$db);

CreateTable('unitsofmeasure',
"CREATE TABLE `unitsofmeasure` (
  `unitid` tinyint(4) NOT NULL auto_increment,
  `unitname` varchar(15) NOT NULL default '',
  PRIMARY KEY (`unitid`)
)",
$db);

CreateTable('workcentres',
"CREATE TABLE `workcentres` (
  `code` char(5) NOT NULL default '',
  `location` char(5) NOT NULL default '',
  `description` char(20) NOT NULL default '',
  `capacity` double(16,4) NOT NULL default '1.0000',
  `overheadperhour` decimal(20,4) NOT NULL default '0.0000',
  `overheadrecoveryact` int(11) NOT NULL default '0',
  `setuphrs` decimal(20,4) NOT NULL default '0.0000',
  PRIMARY KEY (`code`),
  KEY `Description` (`description`),
  KEY `Location` (`location`),
  CONSTRAINT `workcentres_ibfk_1` FOREIGN KEY (`location`) REFERENCES `locations` (`loccode`)
)",
$db);

CreateTable('worksorders',
"CREATE TABLE `worksorders` (
  `woref` char(20) NOT NULL default '',
  `loccode` char(5) NOT NULL default '',
  `unitsreqd` smallint(6) NOT NULL default '1',
  `unitsrecd` double NOT NULL default '0',
  `stockid` char(20) NOT NULL default '',
  `stdcost` decimal(20,4) NOT NULL default '0.0000',
  `requiredby` date NOT NULL default '0000-00-00',
  `releaseddate` date NOT NULL default '1800-01-01',
  `accumvalueissued` decimal(20,4) NOT NULL default '0.0000',
  `accumvaluetrfd` decimal(20,4) NOT NULL default '0.0000',
  `closed` tinyint(4) NOT NULL default '0',
  `released` tinyint(4) NOT NULL default '0',
  PRIMARY KEY (`woref`),
  KEY `StockID` (`stockid`),
  KEY `LocCode` (`loccode`),
  KEY `ReleasedDate` (`releaseddate`),
  KEY `RequiredBy` (`requiredby`),
  KEY `WORef` (`woref`,`loccode`),
  CONSTRAINT `workorders_ibfk_1` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`),
  CONSTRAINT `workorders_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
)",
$db);

CreateTable('www_users',
"CREATE TABLE `www_users` (
  `userid` varchar(20) NOT NULL default '',
  `password` text NOT NULL,
  `realname` varchar(35) NOT NULL default '',
  `customerid` varchar(10) NOT NULL default '',
  `phone` varchar(30) NOT NULL default '',
  `email` varchar(55) default NULL,
  `defaultlocation` varchar(5) NOT NULL default '',
  `fullaccess` int(11) NOT NULL default '1',
  `lastvisitdate` datetime default NULL,
  `branchcode` varchar(10) NOT NULL default '',
  `pagesize` varchar(20) NOT NULL default 'A4',
  `modulesallowed` varchar(20) NOT NULL default '',
  `blocked` tinyint(4) NOT NULL default '0',
  `displayrecordsmax` int(11) NOT NULL default '0',
  `theme` varchar(30) NOT NULL default 'fresh',
  `language` char(2) NOT NULL default 'en',
  `pinno` varchar(30) NOT NULL default '',
  `swipecard` varchar(50) NOT NULL default '',
  PRIMARY KEY (`userid`),
  KEY `CustomerID` (`customerid`),
  KEY `DefaultLocation` (`defaultlocation`),
  CONSTRAINT `www_users_ibfk_1` FOREIGN KEY (`defaultlocation`) REFERENCES `locations` (`loccode`)
)",
$db);

NewConfigValue('DBUpdateNumber', 0, $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>