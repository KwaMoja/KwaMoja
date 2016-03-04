<?php

CreateTable('prltaxstatus',
"CREATE TABLE `prltaxstatus` (
  `taxstatusid` varchar(10)  NOT NULL default '',
  `taxstatusdescription` varchar(40)  NOT NULL default '',
  `personalexemption` decimal(12,2) NOT NULL default '0.00',
  `additionalexemption` decimal(12,2) NOT NULL default '0.00',
  `totalexemption` decimal(12,2) NOT NULL default '0.00',
  PRIMARY KEY  (`taxstatusid`)
)");

CreateTable('prlpayperiod',
"CREATE TABLE `prlpayperiod` (
  `payperiodid` tinyint(4) NOT NULL DEFAULT '0',
  `payperioddesc` varchar(15) NOT NULL DEFAULT '',
  `numberofpayday` int(11) NOT NULL DEFAULT '0',
  `dayofpay` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`payperiodid`)
)");

CreateTable('prlemploymentstatus',
"CREATE TABLE prlemploymentstatus (
  employmentid tinyint(4) NOT NULL AUTO_INCREMENT,
  employmentdesc varchar(15) NOT NULL default '',
  PRIMARY KEY  (employmentid)
)");

CreateTable('prlemployeemaster',
"CREATE TABLE prlemployeemaster (
  employeeid varchar(10) NOT NULL default '',
  lastname varchar(40) NOT NULL default '',
  firstname varchar(40) NOT NULL default '',
  middlename varchar(40) NOT NULL default '',
  address1 varchar(100) NOT NULL default '',
  address2 varchar(100) NOT NULL default '',
  address3 varchar(50) NOT NULL default '',
  zip varchar(15) NOT NULL default '',
  country varchar(40) NOT NULL default '',
  phone1 varchar(20) NOT NULL default '',
  email1 varchar(50) NOT NULL default '',
  id varchar(40) NOT NULL DEFAULT '',
  ni varchar(40) NOT NULL DEFAULT '',
  costcenterid char(5) NOT NULL default '',
  departmentid int(11) NOT NULL DEFAULT 0,
  position int(11) NOT NULL default 0,
  birthdate date NOT NULL default '0000-00-00',
  marital varchar(20) NOT NULL default '',
  gender varchar(15) NOT NULL default '',
  taxstatusid varchar(10) NULL default '',
  payperiodid tinyint(4) NOT NULL default '0',
  paytype tinyint(4) NOT NULL default '0',
  employmentid tinyint(4) NOT NULL default '0',
  active int(11) NOT NULL default '0',
  PRIMARY KEY (employeeid),
  KEY `prlemployeemaster_ibk1` (`position`),
  KEY `prlemployeemaster_ibk2` (`costcenterid`),
  KEY `prlemployeemaster_ibk3` (`taxstatusid`),
  KEY `prlemployeemaster_ibk4` (`payperiodid`),
  KEY `prlemployeemaster_ibk5` (`employmentid`),
  KEY `prlemployeemaster_ibk6` (`departmentid`),
  CONSTRAINT `prlemployeemaster_ibfk1` FOREIGN KEY (`position`) REFERENCES `securityroles` (`secroleid`),
  CONSTRAINT `prlemployeemaster_ibfk2` FOREIGN KEY (`costcenterid`) REFERENCES `workcentres` (`code`),
  CONSTRAINT `prlemployeemaster_ibfk3` FOREIGN KEY (`taxstatusid`) REFERENCES `prltaxstatus` (`taxstatusid`),
  CONSTRAINT `prlemployeemaster_ibfk4` FOREIGN KEY (`payperiodid`) REFERENCES `prlpayperiod` (`payperiodid`),
  CONSTRAINT `prlemployeemaster_ibfk5` FOREIGN KEY (`employmentid`) REFERENCES `prlemploymentstatus` (`employmentid`),
  CONSTRAINT `prlemployeemaster_ibfk6` FOREIGN KEY (`departmentid`) REFERENCES `departments` (`departmentid`)
)");

CreateTable('prlloantable',
"CREATE TABLE `prlloantable` (
  `loantableid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `loantabledesc` varchar(25) NOT NULL default '',
  `interest` varchar(15) default NULL,
  PRIMARY KEY  (`loantableid`)
)");

CreateTable('prlloanfile',
"CREATE TABLE `prlloanfile` (
  `counterindex` int(11) NOT NULL AUTO_INCREMENT,
  `loanfileid` varchar(10) NOT NULL DEFAULT '',
  `loanfiledesc` varchar(40) NOT NULL DEFAULT '',
  `employeeid` varchar(10) NOT NULL DEFAULT '',
  `loandate` date NOT NULL DEFAULT '0000-00-00',
  `loantableid` tinyint(4) NOT NULL DEFAULT '0',
  `loanamount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `amortization` decimal(12,2) NOT NULL DEFAULT '0.00',
  `nextdeduction` date NOT NULL DEFAULT '0000-00-00',
  `ytddeduction` decimal(12,2) NOT NULL DEFAULT '0.00',
  `loanbalance` decimal(12,2) NOT NULL DEFAULT '0.00',
  `accountcode` varchar(20) NOT NULL DEFAULT '1',
  `bankaccount` varchar(20) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `authoriser` varchar(20) NOT NULL DEFAULT '',
  `tagref` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`counterindex`),
  KEY `prlloanfile_ibfk1` (`accountcode`),
  KEY `prlloanfile_ibfk2` (`bankaccount`),
  KEY `prlloanfile_ibfk3` (`employeeid`),
  KEY `prlloanfile_ibfk4` (`authoriser`),
  KEY `prlloanfile_ibfk5` (`loandate`),
  KEY `prlloanfile_ibfk6` (`tagref`),
  CONSTRAINT `prlloanfile_ibfk1` FOREIGN KEY (`accountcode`) REFERENCES `chartmaster` (`accountcode`),
  CONSTRAINT `prlloanfile_ibfk2` FOREIGN KEY (`bankaccount`) REFERENCES `chartmaster` (`accountcode`),
  CONSTRAINT `prlloanfile_ibfk3` FOREIGN KEY (`employeeid`) REFERENCES `prlemployeemaster` (`employeeid`),
  CONSTRAINT `prlloanfile_ibfk4` FOREIGN KEY (`authoriser`) REFERENCES `www_users` (`userid`)
)");

CreateTable('prlloandeduction',
"CREATE TABLE prlloandeduction (
  counterindex int(11) NOT NULL auto_increment,
  payrollid varchar(10) NULL default '',
  employeeid varchar(10) NOT NULL default '',
  loantableid tinyint(4) NOT NULL default '0',
  amount decimal(12,2) NOT NULL default '0.00',
  accountcode int(11) NOT NULL default '0',
  PRIMARY KEY  (counterindex)
)");

NewModule('HR', 'hr', _('Human Resources'), 9);
NewScript('prlEmployeeMaster.php', 5);
NewScript('prlALD.php', 10);
NewScript('prlSelectEmployee.php', 10);
NewScript('prlSelectLoan.php', 10);
NewScript('prlLoanTable.php', 15);
NewScript('prlTaxStatus.php', 15);
NewScript('prlPayPeriod.php', 15);
NewScript('prlEmploymentStatus.php', 15);
NewScript('prlAuthoriseLoans.php', 15);
NewScript('prlLoanPayments.php', 10);
NewScript('prlLoanRepayments.php', 10);

NewMenuItem('HR', 'Transactions', _('Add/Update an Employee Loan'), '/prlALD.php', 1);
NewMenuItem('HR', 'Transactions', _('Authorise Employee Loans'), '/prlAuthoriseLoans.php', 2);
NewMenuItem('HR', 'Transactions', _('Issue Employee Loans'), '/prlLoanPayments.php', 3);
NewMenuItem('HR', 'Transactions', _('Employee Loan Repayments'), '/prlLoanRepayments.php', 4);
NewMenuItem('HR', 'Maintenance', _('Add/Update Employees Record'), '/prlSelectEmployee.php', 1);
NewMenuItem('HR', 'Maintenance', _('Add/Update Loan Types'), '/prlLoanTable.php', 2);
NewMenuItem('HR', 'Maintenance', _('Review Employee Loans'), '/prlSelectLoan.php', 3);
NewMenuItem('HR', 'Maintenance', _('Maintain Tax Status'), '/prlTaxStatus.php', 4);
NewMenuItem('HR', 'Maintenance', _('Maintain Pay Periods'), '/prlPayPeriod.php', 5);
NewMenuItem('HR', 'Maintenance', _('Maintain Employment Statuses'), '/prlEmploymentStatus.php', 5);

NewSysType(60, _('Staff Loans'));
NewSysType(61, _('Staff Loan Repayments'));

UpdateDBNo(basename(__FILE__, '.php'));
?>