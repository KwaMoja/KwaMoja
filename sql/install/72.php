<?php

CreateTable('mrprequirements',
"CREATE TABLE `mrprequirements` (
  `part` char(20) DEFAULT NULL,
  `daterequired` date DEFAULT NULL,
  `quantity` double DEFAULT NULL,
  `mrpdemandtype` varchar(6) DEFAULT NULL,
  `orderno` int(11) DEFAULT NULL,
  `directdemand` smallint(6) DEFAULT NULL,
  `whererequired` char(20) DEFAULT NULL,
  KEY `part` (`part`)
)");


?>