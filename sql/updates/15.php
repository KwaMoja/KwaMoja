<?php

AddColumn('current', 'salesman', 'TINYINT(2)', 'NOT NULL', '1', 'commissionrate2');

ChangeConfigValue('VersionNumber', '4.04.4');

UpdateDBNo(basename(__FILE__, '.php'));

?>