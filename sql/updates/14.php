<?php

ChangeColumnType('pagesecurity', 'scripts', 'INT(11)', 'NOT NULL', '1', $db);

AddColumn('assigner', 'pctabs', 'VARCHAR(20)', 'NOT NULL', '', 'tablimit', $db);

ChangeConfigValue('VersionNumber', '4.04.1', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>