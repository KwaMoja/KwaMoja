<?php

CreateTable('dashboard_users',
"CREATE TABLE `dashboard_users` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userid` varchar(20) NOT NULL,
  `scripts` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
)");


?>