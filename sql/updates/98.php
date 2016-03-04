<?php

CreateTable('regularpayments',
"CREATE TABLE IF NOT EXISTS `regularpayments` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `frequency` char(1) NOT NULL default 'M',
  `days` tinyint(3) NOT NULL DEFAULT 0,
  `glcode` varchar(20) NOT NULL DEFAULT '1',
  `bankaccountcode` varchar(20) NOT NULL DEFAULT '0',
  `tag` tinyint(4) NOT NULL DEFAULT '0',
  `amount` double NOT NULL default 0,
  `currabrev` char(3) NOT NULL DEFAULT '',
  `narrative` varchar(255) default '',
  `firstpayment` date NOT NULL default '0000-00-00',
  `finalpayment` date NOT NULL default '0000-00-00',
  `nextpayment` date NOT NULL default '0000-00-00',
  `completed` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY  (`id`)
)",
$db);

NewScript('RegularPaymentsSetup.php', 5, $db);
NewScript('RegularPaymentsProcess.php', 5, $db);

NewMenuItem('GL', 'Transactions', _('Process Regular Payments'), '/RegularPaymentsProcess.php', 7, $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>