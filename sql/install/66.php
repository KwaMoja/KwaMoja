<?php

CreateTable('modules',
"CREATE TABLE `modules` (
  `secroleid` int(11) NOT NULL DEFAULT '15',
  `modulelink` varchar(10) NOT NULL DEFAULT '',
  `reportlink` varchar(4) NOT NULL DEFAULT '',
  `modulename` varchar(25) NOT NULL DEFAULT '',
  `sequence` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`secroleid`,`modulelink`),
  CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`secroleid`) REFERENCES `securityroles` (`secroleid`)
)");

?>