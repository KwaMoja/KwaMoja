<?php

AddColumn('address5', 'debtorsmaster', 'VARCHAR(20)', 'NOT NULL', '', 'address4', $db);
AddColumn('address6', 'debtorsmaster', 'VARCHAR(15)', 'NOT NULL', '', 'address5', $db);

AddColumn('braddress5', 'custbranch', 'VARCHAR(20)', 'NOT NULL', '', 'braddress4', $db);
AddColumn('braddress6', 'custbranch', 'VARCHAR(15)', 'NOT NULL', '', 'braddress5', $db);

AddColumn('brpostaddr5', 'custbranch', 'VARCHAR(20)', 'NOT NULL', '', 'brpostaddr4', $db);
AddColumn('brpostaddr6', 'custbranch', 'VARCHAR(15)', 'NOT NULL', '', 'brpostaddr5', $db);

AddColumn('deladd4', 'locations', 'VARCHAR(40)', 'NOT NULL', '', 'deladd3', $db);
AddColumn('deladd5', 'locations', 'VARCHAR(20)', 'NOT NULL', '', 'deladd4', $db);
AddColumn('deladd6', 'locations', 'VARCHAR(15)', 'NOT NULL', '', 'deladd5', $db);

AddColumn('deladd5', 'purchorders', 'VARCHAR(20)', 'NOT NULL', '', 'deladd4', $db);
AddColumn('deladd6', 'purchorders', 'VARCHAR(15)', 'NOT NULL', '', 'deladd5', $db);
AddColumn('contact', 'purchorders', 'VARCHAR(30)', 'NOT NULL', '', 'deladd6', $db);

AddColumn('deladd5', 'recurringsalesorders', 'VARCHAR(20)', 'NOT NULL', '', 'deladd4', $db);
AddColumn('deladd6', 'recurringsalesorders', 'VARCHAR(15)', 'NOT NULL', '', 'deladd5', $db);
ChangeColumnType('deladd2', 'recurringsalesorders', 'VARCHAR(40)', 'NOT NULL', '', $db);
ChangeColumnType('deladd3', 'recurringsalesorders', 'VARCHAR(40)', 'NOT NULL', '', $db);
ChangeColumnType('deladd4', 'recurringsalesorders', 'VARCHAR(40)', 'NOT NULL', '', $db);

AddColumn('deladd5', 'salesorders', 'VARCHAR(20)', 'NOT NULL', '', 'deladd4', $db);
AddColumn('deladd6', 'salesorders', 'VARCHAR(15)', 'NOT NULL', '', 'deladd5', $db);
ChangeColumnType('deladd2', 'salesorders', 'VARCHAR(40)', 'NOT NULL', '', $db);
ChangeColumnType('deladd3', 'salesorders', 'VARCHAR(40)', 'NOT NULL', '', $db);
ChangeColumnType('deladd4', 'salesorders', 'VARCHAR(40)', 'NOT NULL', '', $db);

AddColumn('address5', 'suppliers', 'VARCHAR(20)', 'NOT NULL', '', 'address4', $db);
AddColumn('address6', 'suppliers', 'VARCHAR(15)', 'NOT NULL', '', 'address5', $db);

ChangeColumnName('regoffice3', 'companies', 'VARCHAR(40)', 'NOT NULL', '', 'regoffice4', $db);
ChangeColumnName('regoffice2', 'companies', 'VARCHAR(40)', 'NOT NULL', '', 'regoffice3', $db);
ChangeColumnName('regoffice1', 'companies', 'VARCHAR(40)', 'NOT NULL', '', 'regoffice2', $db);
ChangeColumnName('postaladdress', 'companies', 'VARCHAR(40)', 'NOT NULL', '', 'regoffice1', $db);
AddColumn('regoffice5', 'companies', 'VARCHAR(20)', 'NOT NULL', '', 'regoffice4', $db);
AddColumn('regoffice6', 'companies', 'VARCHAR(15)', 'NOT NULL', '', 'regoffice5', $db);


UpdateDBNo(basename(__FILE__, '.php'), $db);

?>