<?php

AddColumn('address5', 'debtorsmaster', 'VARCHAR(20)', 'NOT NULL', '', 'address4');
AddColumn('address6', 'debtorsmaster', 'VARCHAR(15)', 'NOT NULL', '', 'address5');

AddColumn('braddress5', 'custbranch', 'VARCHAR(20)', 'NOT NULL', '', 'braddress4');
AddColumn('braddress6', 'custbranch', 'VARCHAR(15)', 'NOT NULL', '', 'braddress5');

AddColumn('brpostaddr5', 'custbranch', 'VARCHAR(20)', 'NOT NULL', '', 'brpostaddr4');
AddColumn('brpostaddr6', 'custbranch', 'VARCHAR(15)', 'NOT NULL', '', 'brpostaddr5');

AddColumn('deladd4', 'locations', 'VARCHAR(40)', 'NOT NULL', '', 'deladd3');
AddColumn('deladd5', 'locations', 'VARCHAR(20)', 'NOT NULL', '', 'deladd4');
AddColumn('deladd6', 'locations', 'VARCHAR(15)', 'NOT NULL', '', 'deladd5');

AddColumn('deladd5', 'purchorders', 'VARCHAR(20)', 'NOT NULL', '', 'deladd4');
AddColumn('deladd6', 'purchorders', 'VARCHAR(15)', 'NOT NULL', '', 'deladd5');
AddColumn('contact', 'purchorders', 'VARCHAR(30)', 'NOT NULL', '', 'deladd6');

AddColumn('deladd5', 'recurringsalesorders', 'VARCHAR(20)', 'NOT NULL', '', 'deladd4');
AddColumn('deladd6', 'recurringsalesorders', 'VARCHAR(15)', 'NOT NULL', '', 'deladd5');
ChangeColumnType('deladd2', 'recurringsalesorders', 'VARCHAR(40)', 'NOT NULL', '');
ChangeColumnType('deladd3', 'recurringsalesorders', 'VARCHAR(40)', 'NOT NULL', '');
ChangeColumnType('deladd4', 'recurringsalesorders', 'VARCHAR(40)', 'NOT NULL', '');

AddColumn('deladd5', 'salesorders', 'VARCHAR(20)', 'NOT NULL', '', 'deladd4');
AddColumn('deladd6', 'salesorders', 'VARCHAR(15)', 'NOT NULL', '', 'deladd5');
ChangeColumnType('deladd2', 'salesorders', 'VARCHAR(40)', 'NOT NULL', '');
ChangeColumnType('deladd3', 'salesorders', 'VARCHAR(40)', 'NOT NULL', '');
ChangeColumnType('deladd4', 'salesorders', 'VARCHAR(40)', 'NOT NULL', '');

AddColumn('address5', 'suppliers', 'VARCHAR(20)', 'NOT NULL', '', 'address4');
AddColumn('address6', 'suppliers', 'VARCHAR(15)', 'NOT NULL', '', 'address5');

ChangeColumnName('regoffice3', 'companies', 'VARCHAR(40)', 'NOT NULL', '', 'regoffice4');
ChangeColumnName('regoffice2', 'companies', 'VARCHAR(40)', 'NOT NULL', '', 'regoffice3');
ChangeColumnName('regoffice1', 'companies', 'VARCHAR(40)', 'NOT NULL', '', 'regoffice2');
ChangeColumnName('postaladdress', 'companies', 'VARCHAR(40)', 'NOT NULL', '', 'regoffice1');
AddColumn('regoffice5', 'companies', 'VARCHAR(20)', 'NOT NULL', '', 'regoffice4');
AddColumn('regoffice6', 'companies', 'VARCHAR(15)', 'NOT NULL', '', 'regoffice5');


UpdateDBNo(basename(__FILE__, '.php'));

?>