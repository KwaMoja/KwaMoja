<?php

AddColumn('chequeno', 'banktrans', 'INT(11)', 'NOT NULL', 0, 'ref');
ChangeColumnType('chequeno', 'banktrans', 'VARCHAR(20)', 'NOT NULL', '');

UpdateDBNo(basename(__FILE__, '.php'));

?>