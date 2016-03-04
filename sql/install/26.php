<?php

CreateTable('dashboard_scripts',
"CREATE TABLE `dashboard_scripts` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `scripts` varchar(78) NOT NULL,
  `pagesecurity` int(11) NOT NULL DEFAULT '1',
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
)");


?>