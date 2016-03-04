<?php

CreateTable('asteriskdata',
"CREATE TABLE `asteriskdata` (
 `accountcode` varchar(10) NOT NULL DEFAULT '',
 `sourcenumber` varchar(20) NOT NULL DEFAULT '0',
 `destinationnumber` varchar(20) NOT NULL DEFAULT '0',
 `dcontext` varchar(20) NOT NULL DEFAULT '',
 `clid` varchar(100) NOT NULL DEFAULT '',
 `channel` varchar(100) NOT NULL DEFAULT '',
 `dstchannel` varchar(100) NOT NULL DEFAULT '',
 `lastapp` varchar(20) NOT NULL DEFAULT '',
 `lastdata` varchar(100) NOT NULL DEFAULT '',
 `callstartdate` datetime NOT NULL,
 `callanswerdate` datetime NOT NULL,
 `callenddate` datetime NOT NULL,
 `callduration` int(11) NOT NULL DEFAULT 0,
 `billseconds` int(11) NOT NULL DEFAULT 0,
 `disposition` varchar(25) NOT NULL DEFAULT '',
 `amaflags` varchar(25) NOT NULL DEFAULT '',
 `costpersecond` double NOT NULL DEFAULT 0.0,
 `uniqueid` varchar(20) NOT NULL DEFAULT '' ,
 `userfield` varchar(20) NOT NULL DEFAULT '' ,
 `quality` varchar(10) NOT NULL DEFAULT '' ,
 PRIMARY KEY (`accountcode`, `callstartdate`),
 KEY `Debtorno` (`accountcode`),
 CONSTRAINT `asteriskdata _ibfk_1` FOREIGN KEY (`accountcode`) REFERENCES `debtorsmaster` (`debtorno`)
)");

CreateTable('telecomrates',
"CREATE TABLE `telecomrates` (
 `region` varchar(100) NOT NULL DEFAULT '',
 `prefix` varchar(20) NOT NULL DEFAULT '1',
 `ratepersecond` double NOT NULL DEFAULT 0.0,
 PRIMARY KEY (`prefix`)
)");

NewScript('AsteriskImport.php', 15);

NewMenuItem('orders', 'Transactions', 'Import Asterisk Files', '/AsteriskImport.php', 15);

UpdateDBNo(basename(__FILE__, '.php'));

?>