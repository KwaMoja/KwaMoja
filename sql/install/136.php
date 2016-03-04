<?php

CreateTable('systypes',
"CREATE TABLE `systypes` (
  `typeid` smallint(6) NOT NULL DEFAULT '0',
  `typename` char(50) NOT NULL DEFAULT '',
  `typeno` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`typeid`),
  KEY `TypeNo` (`typeno`)
)");

InsertRecord('systypes', array('typeid'), array(0), array('typeid', 'typename', 'typeno'), array(0, 'Journal - GL',0));
InsertRecord('systypes', array('typeid'), array(1), array('typeid', 'typename', 'typeno'), array(1, 'Payment - GL',0));
InsertRecord('systypes', array('typeid'), array(2), array('typeid', 'typename', 'typeno'), array(2, 'Receipt - GL',0));
InsertRecord('systypes', array('typeid'), array(3), array('typeid', 'typename', 'typeno'), array(3, 'Standing Journal',0));
InsertRecord('systypes', array('typeid'), array(10), array('typeid', 'typename', 'typeno'), array(10, 'Sales Invoice',0));
InsertRecord('systypes', array('typeid'), array(11), array('typeid', 'typename', 'typeno'), array(11, 'Credit Note',0));
InsertRecord('systypes', array('typeid'), array(12), array('typeid', 'typename', 'typeno'), array(12, 'Receipt',0));
InsertRecord('systypes', array('typeid'), array(15), array('typeid', 'typename', 'typeno'), array(15, 'Journal - Debtors',0));
InsertRecord('systypes', array('typeid'), array(16), array('typeid', 'typename', 'typeno'), array(16, 'Location Transfer',0));
InsertRecord('systypes', array('typeid'), array(17), array('typeid', 'typename', 'typeno'), array(17, 'Stock Adjustment',0));
InsertRecord('systypes', array('typeid'), array(18), array('typeid', 'typename', 'typeno'), array(18, 'Purchase Order',0));
InsertRecord('systypes', array('typeid'), array(19), array('typeid', 'typename', 'typeno'), array(19, 'Picking List',0));
InsertRecord('systypes', array('typeid'), array(20), array('typeid', 'typename', 'typeno'), array(20, 'Purchase Invoice',0));
InsertRecord('systypes', array('typeid'), array(21), array('typeid', 'typename', 'typeno'), array(21, 'Debit Note',0));
InsertRecord('systypes', array('typeid'), array(22), array('typeid', 'typename', 'typeno'), array(22, 'Creditors Payment',0));
InsertRecord('systypes', array('typeid'), array(23), array('typeid', 'typename', 'typeno'), array(23, 'Creditors Journal',0));
InsertRecord('systypes', array('typeid'), array(25), array('typeid', 'typename', 'typeno'), array(25, 'Purchase Order Delivery',0));
InsertRecord('systypes', array('typeid'), array(26), array('typeid', 'typename', 'typeno'), array(26, 'Work Order Receipt',0));
InsertRecord('systypes', array('typeid'), array(28), array('typeid', 'typename', 'typeno'), array(28, 'Work Order Issue',0));
InsertRecord('systypes', array('typeid'), array(29), array('typeid', 'typename', 'typeno'), array(29, 'Work Order Variance',0));
InsertRecord('systypes', array('typeid'), array(30), array('typeid', 'typename', 'typeno'), array(30, 'Sales Order',0));
InsertRecord('systypes', array('typeid'), array(31), array('typeid', 'typename', 'typeno'), array(31, 'Shipment Close',0));
InsertRecord('systypes', array('typeid'), array(32), array('typeid', 'typename', 'typeno'), array(32, 'Contract Close',0));
InsertRecord('systypes', array('typeid'), array(35), array('typeid', 'typename', 'typeno'), array(35, 'Cost Update',0));
InsertRecord('systypes', array('typeid'), array(36), array('typeid', 'typename', 'typeno'), array(36, 'Exchange Difference',0));
InsertRecord('systypes', array('typeid'), array(37), array('typeid', 'typename', 'typeno'), array(37, 'Tenders ',0));
InsertRecord('systypes', array('typeid'), array(38), array('typeid', 'typename', 'typeno'), array(38, 'Stock Requests',0));
InsertRecord('systypes', array('typeid'), array(40), array('typeid', 'typename', 'typeno'), array(40, 'Work Order',0));
InsertRecord('systypes', array('typeid'), array(41), array('typeid', 'typename', 'typeno'), array(41, 'Asset Addition',0));
InsertRecord('systypes', array('typeid'), array(42), array('typeid', 'typename', 'typeno'), array(42, 'Asset Category Change',0));
InsertRecord('systypes', array('typeid'), array(43), array('typeid', 'typename', 'typeno'), array(43, 'Delete w/down asset',0));
InsertRecord('systypes', array('typeid'), array(44), array('typeid', 'typename', 'typeno'), array(44, 'Depreciation',0));
InsertRecord('systypes', array('typeid'), array(49), array('typeid', 'typename', 'typeno'), array(49, 'Import Fixed Assets',0));
InsertRecord('systypes', array('typeid'), array(50), array('typeid', 'typename', 'typeno'), array(50, 'Opening Balance',0));
InsertRecord('systypes', array('typeid'), array(500), array('typeid', 'typename', 'typeno'), array(500, 'Auto Debtor Number',0));

?>