<?php

AddColumn('current', 'salesman', 'TINYINT(2)', 'NOT NULL', '1', 'commissionrate2', $db);

ChangeConfigValue('VersionNumber', '4.04.4', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>