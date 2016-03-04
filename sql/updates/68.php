<?php

AddColumn('url', 'suppliers', 'VARCHAR( 70 )', 'NOT NULL', '', 'email');
ChangeConfigValue('VersionNumber', '13.10.0');

UpdateDBNo(basename(__FILE__, '.php'));

?>