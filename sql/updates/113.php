<?php

CreateTable('stocklongdescriptiontranslations',
"CREATE TABLE `stocklongdescriptiontranslations` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `language_id` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `longdescriptiontranslation` mediumtext NOT NULL,
  PRIMARY KEY (`stockid`,`language_id`)
)");

executeSQL("INSERT INTO stocklongdescriptiontranslations (SELECT stockid, language_id,descriptiontranslation as longdescriptiontranslation FROM stockdescriptiontranslations)");

UpdateDBNo(basename(__FILE__, '.php'));

?>