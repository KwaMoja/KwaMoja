<?php

CreateTable('locationusers',
"CREATE TABLE IF NOT EXISTS `locationusers` (
  `loccode` varchar(5) NOT NULL,
  `userid` varchar(20) NOT NULL,
  `canview` tinyint(4) NOT NULL DEFAULT '0',
  `canupd` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`loccode`,`userid`),
  KEY `UserId` (`userid`)
)");

NewScript('Z_MakeLocUsers.php', 15);
NewScript('LocationUsers.php', 15);
NewScript('AgedControlledInventory.php', 15);

NewMenuItem('Utilities', 'Maintenance', 'Create User Location records', '/Z_MakeLocUsers.php', 15);
NewMenuItem('system', 'Maintenance', 'User Location Maintenance', '/LocationUsers.php', 15);
NewMenuItem('stock', 'Reports', 'Aged Controlled Inventory Report', '/AgedControlledInventory.php', 15);

UpdateDBNo(basename(__FILE__, '.php'));

?>