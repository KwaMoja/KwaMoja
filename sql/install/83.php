<?php

CreateTable('periods',
"CREATE TABLE `periods` (
  `periodno` smallint(6) NOT NULL DEFAULT '0',
  `lastdate_in_period` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`periodno`),
  KEY `LastDate_in_Period` (`lastdate_in_period`)
)");

$_SESSION['DefaultDateFormat'] = 'd/m/Y';
InsertRecord('periods', array('periodno'), array(0), array('periodno', 'lastdate_in_period'), array(0, date('Y-m-t')));
InsertRecord('periods', array('periodno'), array(1), array('periodno', 'lastdate_in_period'), array(1, LastDayOfMonth(DateAdd(date('d/m/Y'), 'm', 1))));
unset($_SESSION['DefaultDateFormat']);
?>