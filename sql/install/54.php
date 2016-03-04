<?php

CreateTable('jobcards',
"CREATE TABLE `jobcards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `debtorno` varchar(255) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `task1` varchar(500) DEFAULT NULL,
  `task2` varchar(500) DEFAULT NULL,
  `task3` varchar(500) DEFAULT NULL,
  `task4` varchar(500) DEFAULT NULL,
  `task5` varchar(500) DEFAULT NULL,
  `task6` varchar(500) DEFAULT NULL,
  `createdate` date DEFAULT NULL,
  `completedate` date DEFAULT NULL,
  `invoice` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
)");


?>