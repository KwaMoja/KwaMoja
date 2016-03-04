<?php

CreateTable('abcstock',
"CREATE TABLE `abcstock` (
  `groupid` int(11) NOT NULL DEFAULT '0',
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `abccategory` char(1) NOT NULL DEFAULT 'C',
  PRIMARY KEY (`groupid`,`stockid`),
  KEY `abcstock_ibfk_2` (`stockid`),
  CONSTRAINT `abcstock_ibfk_1` FOREIGN KEY (`groupid`) REFERENCES `abcgroups` (`groupid`),
  CONSTRAINT `abcstock_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
)");


?>