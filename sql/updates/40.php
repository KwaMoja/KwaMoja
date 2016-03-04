<?php

CreateTable('abcmethods',
"CREATE TABLE `abcmethods` (
	`methodid` TINYINT NOT NULL DEFAULT 0,
	`methodname` VARCHAR(40) NOT NULL DEFAULT '',
	PRIMARY KEY (`methodid`)
)");

InsertRecord('abcmethods', array('methodid', 'methodname'), array(0, 'Consumption Value Ranking'), array('methodid', 'methodname'), array(0, 'Consumption Value Ranking'));

CreateTable('abcgroups',
"CREATE TABLE `abcgroups`(
	`groupid` INT(11) NOT NULL DEFAULT 0,
	`groupname` VARCHAR(40) NOT NULL DEFAULT '',
	`methodid` TINYINT NOT NULL DEFAULT 0,
	`apercentage` TINYINT NOT NULL DEFAULT 0,
	`bpercentage` TINYINT NOT NULL DEFAULT 0,
	`cpercentage` TINYINT NOT NULL DEFAULT 0,
	`zerousage` CHAR(1) NOT NULL DEFAULT 'D',
	`months` TINYINT NOT NULL DEFAULT 12,
	PRIMARY KEY (`groupid`),
	CONSTRAINT `abctgroups_ibfk_1` FOREIGN KEY (`methodid`) REFERENCES `abcmethods` (`methodid`)
)");

CreateTable('abcstock',
"CREATE TABLE `abcstock` (
	`groupid` INT(11) NOT NULL DEFAULT 0,
	`stockid` VARCHAR(20) NOT NULL DEFAULT '',
	`abccategory` CHAR(1) NOT NULL DEFAULT 'C',
	PRIMARY KEY (`groupid`, `stockid`),
	CONSTRAINT `abcstock_ibfk_1` FOREIGN KEY (`groupid`) REFERENCES `abcgroups` (`groupid`),
	CONSTRAINT `abcstock_ibfk_2` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
)");

NewScript('ABCRankingMethods.php', 15);
NewScript('ABCRankingGroups.php', 15);
NewScript('ABCRunAnalysis.php', 15);

UpdateDBNo(basename(__FILE__, '.php'));

?>