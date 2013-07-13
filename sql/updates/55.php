<?php

ChangeColumnType('brpostaddr3', 'custbranch', 'VARCHAR(40)','NOT NULL', '', $db);
ChangeColumnType('brpostaddr4', 'custbranch', 'VARCHAR(50)','NOT NULL', '', $db);
ChangeColumnType('brpostaddr6', 'custbranch', 'VARCHAR(40)','NOT NULL', '', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>