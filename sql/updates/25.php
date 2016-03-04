<?php

ChangeColumnType('debtorsact', 'companies', 'VARCHAR(20)', 'NOT NULL', '70000');
ChangeColumnType('pytdiscountact', 'companies', 'VARCHAR(20)', 'NOT NULL', '55000');
ChangeColumnType('creditorsact', 'companies', 'VARCHAR(20)', 'NOT NULL', '80000');
ChangeColumnType('payrollact', 'companies', 'VARCHAR(20)', 'NOT NULL', '84000');
ChangeColumnType('grnact', 'companies', 'VARCHAR(20)', 'NOT NULL', '72000');
ChangeColumnType('exchangediffact', 'companies', 'VARCHAR(20)', 'NOT NULL', '65000');
ChangeColumnType('purchasesexchangediffact', 'companies', 'VARCHAR(20)', 'NOT NULL', '0');
ChangeColumnType('retainedearnings', 'companies', 'VARCHAR(20)', 'NOT NULL', '90000');
ChangeColumnType('freightact', 'companies', 'VARCHAR(20)', 'NOT NULL', '0');

ChangeColumnType('stockact', 'lastcostrollup', 'VARCHAR(20)', 'NOT NULL', '0');
ChangeColumnType('adjglact', 'lastcostrollup', 'VARCHAR(20)', 'NOT NULL', '0');

UpdateDBNo(basename(__FILE__, '.php'));

?>