<?php

CreateTable('areas',
"CREATE TABLE `areas` (
  `areacode` char(3) NOT NULL,
  `parentarea` char(3) NOT NULL DEFAULT '',
  `areadescription` varchar(25) NOT NULL DEFAULT '',
  PRIMARY KEY (`areacode`)
)");


?>