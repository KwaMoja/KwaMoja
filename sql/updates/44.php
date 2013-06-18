<?php

CreateTable('warehouse',
"CREATE TABLE `warehouse` (
	`warehouseid` VARCHAR(3) NOT NULL DEFAULT '',
	`loccode` VARCHAR(5) NOT NULL DEFAULT '',
	`name` VARCHAR(50) NOT NULL DEFAULT '',
	PRIMARY KEY (`warehouseid`, `loccode`),
	CONSTRAINT `warehouse_ibfk_1` FOREIGN KEY (`loccode`) REFERENCES `locations` (`loccode`)
)",
$db);

CreateTable('whlocations',
"CREATE TABLE `whlocations` (
	`whlocationid` INT(11) NOT NULL AUTO_INCREMENT,
	`whlocationcode` VARCHAR(50) NOT NULL DEFAULT '',
	`warehouseid` VARCHAR(3) NOT NULL DEFAULT '',
	`parentwhlocationid` int(11) NOT NULL DEFAULT 0,
	`name` VARCHAR(50) NOT NULL DEFAULT '',
	`width` INT(11)) NOT NULL DEFAULT 1,
	`length` INT(11)) NOT NULL DEFAULT 1,
	`height` INT(11)) NOT NULL DEFAULT 1,
	`notes` TEXT DEFAULT NULL COMMENT 'notes about this bin',
	`img` VARCHAR(50) DEFAULT NULL COMMENT 'img about this bin',
	`active` TINYINT(4) NOT NULL DEFAULT '1' COMMENT 'true=1',
	`putaway` TINYINT(4) NOT NULL DEFAULT '1' COMMENT 'true=1',
	`replenish` TINYINT(4) NOT NULL DEFAULT '1' COMMENT 'true=1',
	`issue` TINYINT(4) NOT NULL DEFAULT '1' COMMENT 'true=1',
	PRIMARY KEY (`whlocationid`),
	CONSTRAINT `whlocations_ibfk_1` FOREIGN KEY (`warehouseid`) REFERENCES `warehouse` (`warehouseid`)
)",
$db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>