<?php

CreateTable('unitsofmeasure',
"CREATE TABLE `unitsofmeasure` (
  `unitid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `unitname` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`unitid`)
)");

InsertRecord('unitsofmeasure', array('unitname'), array('each'), array('unitid', 'unitname'), array(null, 'each'));

?>