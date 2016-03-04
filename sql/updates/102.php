<?php

CreateTable('relateditems',
"CREATE TABLE IF NOT EXISTS `relateditems` (
  `stockid` varchar(20) CHARACTER SET utf8 NOT NULL,
  `related` varchar(20) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`stockid`,`related`),
  UNIQUE KEY `Related` (`related`,`stockid`)
)");

NewScript('RelatedItemsUpdate.php', 2);

UpdateDBNo(basename(__FILE__, '.php'));

?>