<?php

CreateTable('levels',
"CREATE TABLE `levels` (
  `part` char(20) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `leadtime` smallint(6) NOT NULL DEFAULT '0',
  `pansize` double NOT NULL DEFAULT '0',
  `shrinkfactor` double NOT NULL DEFAULT '0',
  `eoq` double NOT NULL DEFAULT '0',
  KEY `part` (`part`)
)");


?>