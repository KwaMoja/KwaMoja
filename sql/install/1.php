<?php

CreateTable('abcgroups',
"CREATE TABLE `abcgroups` (
  `groupid` int(11) NOT NULL DEFAULT '0',
  `groupname` varchar(40) NOT NULL DEFAULT '',
  `methodid` tinyint(4) NOT NULL DEFAULT '0',
  `apercentage` tinyint(4) NOT NULL DEFAULT '0',
  `bpercentage` tinyint(4) NOT NULL DEFAULT '0',
  `cpercentage` tinyint(4) NOT NULL DEFAULT '0',
  `zerousage` char(1) NOT NULL DEFAULT 'D',
  `months` tinyint(4) NOT NULL DEFAULT '12',
  PRIMARY KEY (`groupid`),
  KEY `abctgroups_ibfk_1` (`methodid`),
  CONSTRAINT `abctgroups_ibfk_1` FOREIGN KEY (`methodid`) REFERENCES `abcmethods` (`methodid`)
)");


?>