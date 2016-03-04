<?php

CreateTable('salesman',
"CREATE TABLE `salesman` (
  `salesmancode` varchar(4) NOT NULL,
  `salesmanname` char(30) NOT NULL DEFAULT '',
  `salesarea` char(3) NOT NULL DEFAULT '',
  `manager` int(1) NOT NULL DEFAULT '0',
  `smantel` char(20) NOT NULL DEFAULT '',
  `smanfax` char(20) NOT NULL DEFAULT '',
  `commissionrate1` double NOT NULL DEFAULT '0',
  `breakpoint` decimal(10,0) NOT NULL DEFAULT '0',
  `commissionrate2` double NOT NULL DEFAULT '0',
  `current` tinyint(4) NOT NULL COMMENT 'Salesman current (1) or not (0)',
  PRIMARY KEY (`salesmancode`),
  KEY `fk_salesman_1` (`salesarea`),
  CONSTRAINT `fk_salesman_1` FOREIGN KEY (`salesarea`) REFERENCES `areas` (`areacode`)
)");


?>