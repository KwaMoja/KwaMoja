<?php

ChangeColumnType('brpostaddr3', 'custbranch', 'VARCHAR(40)','NOT NULL', '');
ChangeColumnType('brpostaddr4', 'custbranch', 'VARCHAR(50)','NOT NULL', '');
ChangeColumnType('brpostaddr6', 'custbranch', 'VARCHAR(40)','NOT NULL', '');

UpdateDBNo(basename(__FILE__, '.php'));

?>