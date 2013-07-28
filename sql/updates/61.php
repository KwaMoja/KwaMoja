<?php

AddColumn('parentarea', 'areas', 'CHAR( 3 )', 'NOT NULL', '', 'areacode', $db);
AddColumn('salesarea', 'salesman', 'CHAR( 3 )', 'NOT NULL', '', 'salesmanname', $db);
AddColumn('manager', 'salesman', 'INT( 1 )', 'NOT NULL', '0', 'salesarea', $db);

AddConstraint('salesman', 'fk_salesman_1', 'salesarea', 'areas', 'areacode', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>