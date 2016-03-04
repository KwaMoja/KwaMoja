<?php

ChangeColumnType('pagesecurity', 'scripts', 'INT(11)', 'NOT NULL', '1');

AddColumn('assigner', 'pctabs', 'VARCHAR(20)', 'NOT NULL', '', 'tablimit');

ChangeConfigValue('VersionNumber', '4.04.1');

UpdateDBNo(basename(__FILE__, '.php'));

?>