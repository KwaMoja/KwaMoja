<?php

ChangeColumnType('debtorsact', 'companies', 'VARCHAR(20)', 'NOT NULL', '70000', $db);
ChangeColumnType('pytdiscountact', 'companies', 'VARCHAR(20)', 'NOT NULL', '55000', $db);
ChangeColumnType('creditorsact', 'companies', 'VARCHAR(20)', 'NOT NULL', '80000', $db);
ChangeColumnType('payrollact', 'companies', 'VARCHAR(20)', 'NOT NULL', '84000', $db);
ChangeColumnType('grnact', 'companies', 'VARCHAR(20)', 'NOT NULL', '72000', $db);
ChangeColumnType('exchangediffact', 'companies', 'VARCHAR(20)', 'NOT NULL', '65000', $db);
ChangeColumnType('purchasesexchangediffact', 'companies', 'VARCHAR(20)', 'NOT NULL', '0', $db);
ChangeColumnType('retainedearnings', 'companies', 'VARCHAR(20)', 'NOT NULL', '90000', $db);
ChangeColumnType('freightact', 'companies', 'VARCHAR(20)', 'NOT NULL', '0', $db);

ChangeColumnType('stockact', 'lastcostrollup', 'VARCHAR(20)', 'NOT NULL', '0', $db);
ChangeColumnType('adjglact', 'lastcostrollup', 'VARCHAR(20)', 'NOT NULL', '0', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>