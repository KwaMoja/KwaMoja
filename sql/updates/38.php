<?php

ChangeColumnType('discountglcode', 'salesglpostings', 'VARCHAR(20)', 'NOT NULL', '0', $db);
ChangeColumnType('salesglcode', 'salesglpostings', 'VARCHAR(20)', 'NOT NULL', '0', $db);

ChangeColumnType('glcode', 'cogsglpostings', 'VARCHAR(20)', 'NOT NULL', '0', $db);

ChangeColumnType('costact', 'fixedassetcategories', 'VARCHAR(20)', 'NOT NULL', '0', $db);
ChangeColumnType('depnact', 'fixedassetcategories', 'VARCHAR(20)', 'NOT NULL', '0', $db);
ChangeColumnType('disposalact', 'fixedassetcategories', 'VARCHAR(20)', 'NOT NULL', '80000', $db);
ChangeColumnType('accumdepnact', 'fixedassetcategories', 'VARCHAR(20)', 'NOT NULL', '0', $db);

ChangeColumnType('glcode', 'purchorderdetails', 'VARCHAR(20)', 'NOT NULL', '0', $db);

ChangeColumnType('stockact', 'stockcategory', 'VARCHAR(20)', 'NOT NULL', '0', $db);
ChangeColumnType('adjglact', 'stockcategory', 'VARCHAR(20)', 'NOT NULL', '0', $db);
ChangeColumnType('issueglact', 'stockcategory', 'VARCHAR(20)', 'NOT NULL', '0', $db);
ChangeColumnType('purchpricevaract', 'stockcategory', 'VARCHAR(20)', 'NOT NULL', '80000', $db);
ChangeColumnType('materialuseagevarac', 'stockcategory', 'VARCHAR(20)', 'NOT NULL', '80000', $db);
ChangeColumnType('wipact', 'stockcategory', 'VARCHAR(20)', 'NOT NULL', '0', $db);

ChangeColumnType('overheadrecoveryact', 'workcentres', 'VARCHAR(20)', 'NOT NULL', '0', $db);


UpdateDBNo(basename(__FILE__, '.php'), $db);

?>