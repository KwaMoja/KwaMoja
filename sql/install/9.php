<?php

CreateTable('bankaccountusers',
"CREATE TABLE `bankaccountusers` (
  `accountcode` varchar(20) NOT NULL DEFAULT '',
  `userid` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`accountcode`,`userid`)
)");


?>