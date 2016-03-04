<?php

CreateTable('schedule',
"CREATE TABLE `schedule` (
 `jobnumber` int(11) auto_increment,
 `script` varchar(60) NOT NULL DEFAULT '',
 `nextrun` datetime NOT NULL DEFAULT '0000-00-00',
 `frequency` char NOT NULL DEFAULT 'd',
 PRIMARY KEY (`jobnumber`),
 KEY `Script` (`script`)
)");

NewScript('JobScheduler.php', 15);

NewMenuItem('system', 'Transactions', 'Schedule tasks to be automatically run', '/JobScheduler.php', 15);

UpdateDBNo(basename(__FILE__, '.php'));

?>