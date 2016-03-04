<?php

ChangeColumnType('salescatname', 'salescat', 'VARCHAR(50)', 'NOT NULL', '');

UpdateDBNo(basename(__FILE__, '.php'));

?>