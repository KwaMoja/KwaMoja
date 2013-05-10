<?php

ChangeColumnType('salescatname', 'salescat', 'VARCHAR(50)', 'NOT NULL', '', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>