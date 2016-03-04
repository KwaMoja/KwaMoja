<?php

ChangeColumnType('discountglcode', 'salesglpostings', 'VARCHAR(20)', 'NOT NULL', '0');
ChangeColumnType('salesglcode', 'salesglpostings', 'VARCHAR(20)', 'NOT NULL', '0');

ChangeColumnType('glcode', 'cogsglpostings', 'VARCHAR(20)', 'NOT NULL', '0');

ChangeColumnType('costact', 'fixedassetcategories', 'VARCHAR(20)', 'NOT NULL', '0');
ChangeColumnType('depnact', 'fixedassetcategories', 'VARCHAR(20)', 'NOT NULL', '0');
ChangeColumnType('disposalact', 'fixedassetcategories', 'VARCHAR(20)', 'NOT NULL', '80000');
ChangeColumnType('accumdepnact', 'fixedassetcategories', 'VARCHAR(20)', 'NOT NULL', '0');

ChangeColumnType('glcode', 'purchorderdetails', 'VARCHAR(20)', 'NOT NULL', '0');

ChangeColumnType('stockact', 'stockcategory', 'VARCHAR(20)', 'NOT NULL', '0');
ChangeColumnType('adjglact', 'stockcategory', 'VARCHAR(20)', 'NOT NULL', '0');
ChangeColumnType('issueglact', 'stockcategory', 'VARCHAR(20)', 'NOT NULL', '0');
ChangeColumnType('purchpricevaract', 'stockcategory', 'VARCHAR(20)', 'NOT NULL', '80000');
ChangeColumnType('materialuseagevarac', 'stockcategory', 'VARCHAR(20)', 'NOT NULL', '80000');
ChangeColumnType('wipact', 'stockcategory', 'VARCHAR(20)', 'NOT NULL', '0');

ChangeColumnType('overheadrecoveryact', 'workcentres', 'VARCHAR(20)', 'NOT NULL', '0');


UpdateDBNo(basename(__FILE__, '.php'));

?>