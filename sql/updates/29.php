<?php

AddColumn('chequeno', 'banktrans', 'INT(11)', 'NOT NULL', 0, 'ref', $db);
ChangeColumnType('chequeno', 'banktrans', 'VARCHAR(20)', 'NOT NULL', '', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>