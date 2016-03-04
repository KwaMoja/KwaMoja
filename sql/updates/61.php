<?php

AddColumn('parentarea', 'areas', 'CHAR( 3 )', 'NOT NULL', '', 'areacode');
AddColumn('salesarea', 'salesman', 'CHAR( 3 )', 'NOT NULL', '', 'salesmanname');
AddColumn('manager', 'salesman', 'INT( 1 )', 'NOT NULL', '0', 'salesarea');

AddConstraint('salesman', 'fk_salesman_1', 'salesarea', 'areas', 'areacode');

UpdateDBNo(basename(__FILE__, '.php'));

?>