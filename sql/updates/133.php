<?php

CreateTable('loctransfercancellations',
"CREATE TABLE `loctransfercancellations` (
  `reference` INT(11) NOT NULL ,
  `stockid` VARCHAR(20) NOT NULL ,
  `cancelqty` DOUBLE NOT NULL ,
  `canceldate` DATETIME NOT NULL ,
  `canceluserid` VARCHAR(20) NOT NULL ,
  KEY `Index1` (`reference`, `stockid`),
  KEY `Index2` (`canceldate`, `reference`, `stockid`)
)");

UpdateDBNo(basename(__FILE__, '.php'));

?>