<?php

AddColumn('url', 'suppliers', 'VARCHAR( 70 )', 'NOT NULL', '', 'email', $db);
ChangeConfigValue('VersionNumber', '13.10.0', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>