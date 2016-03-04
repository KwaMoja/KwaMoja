<?php

ChangeColumnSize('consignment', 'debtortrans', 'VARCHAR(20)', 'NOT NULL', '', 20);

AddColumn('currabrev', 'pricematrix', 'CHAR(3)', 'NOT NULL', '""', 'price');
AddColumn('startdate', 'pricematrix', 'date', 'NOT NULL', '0000-00-00', 'currabrev');
AddColumn('enddate', 'pricematrix', 'date', 'NOT NULL', '9999-12-31', 'startdate');

DropPrimaryKey('pricematrix', array('salestype', 'stockid', 'quantitybreak'));
AddPrimaryKey('pricematrix', array('salestype', 'stockid', 'currabrev', 'quantitybreak', 'startdate', 'enddate'));

DropIndex('pricematrix', 'DiscountCategory');
AddIndex(array('currabrev'), 'pricematrix', 'currabrev');
AddIndex(array('stockid'), 'pricematrix', 'stockid');

UpdateDBNo(basename(__FILE__, '.php'));

?>