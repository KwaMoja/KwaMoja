<?php

AddColumn('itemdue', 'salesorderdetails', 'DATE', 'NOT NULL', "0000-00-00", 'narrative');
AddColumn('poline', 'salesorderdetails', 'VARCHAR(10)', 'NOT NULL', '', 'itemdue');

AddColumn('customerpoline', 'debtorsmaster', 'TinyInt(1)', 'NOT NULL', "0", 'taxref');


UpdateDBNo(basename(__FILE__, '.php'));

?>