<?php

CreateTable('menuitems',
"CREATE TABLE `menuitems` (
  `secroleid` int(11) NOT NULL DEFAULT '15',
  `modulelink` varchar(10) NOT NULL DEFAULT '',
  `menusection` varchar(15) NOT NULL DEFAULT '',
  `caption` varchar(60) NOT NULL DEFAULT '',
  `url` varchar(60) NOT NULL DEFAULT '',
  `sequence` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`secroleid`,`modulelink`,`menusection`,`caption`),
  CONSTRAINT `menuitems_ibfk_1` FOREIGN KEY (`secroleid`) REFERENCES `securityroles` (`secroleid`),
  CONSTRAINT `menuitems_ibfk_2` FOREIGN KEY (`secroleid`, `modulelink`) REFERENCES `modules` (`secroleid`, `modulelink`)
)");

?>