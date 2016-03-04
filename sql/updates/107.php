<?php

NewConfigValue('KwaMojaImagesFromOpenCart', 'data/part_pics/');

AddColumn('length', 'stockmaster', 'DECIMAL(15, 8)', 'NOT NULL', '0', 'netweight');
AddColumn('width', 'stockmaster', 'DECIMAL(15, 8)', 'NOT NULL', '0', 'length');
AddColumn('height', 'stockmaster', 'DECIMAL(15, 8)', 'NOT NULL', '0', 'width');
AddColumn('unitsdimension', 'stockmaster', 'VARCHAR(15)', 'NOT NULL', 'mm', 'height');

CreateTable('unitsofdimension',
"CREATE TABLE IF NOT EXISTS `unitsofdimension` (
  `unitid` tinyint(4) NOT NULL AUTO_INCREMENT,
  `unitname` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`unitid`)
)");

InsertRecord('unitsofdimension', array('unitname'), array('mm'), array('unitid', 'unitname'), array(NULL, 'mm'));
InsertRecord('unitsofdimension', array('unitname'), array('cm'), array('unitid', 'unitname'), array(NULL, 'cm'));

NewScript('OcOpenCartToKwaMoja.php',  '15');
NewScript('OcKwaMojaToOpenCartHourly.php',  '15');
NewScript('OcKwaMojaToOpenCartDaily.php',  '15');

NewMenuItem('orders', 'Transactions', 'Synchronise OpenCart to KwaMoja', '/OcOpenCartToKwaMoja.php', 11);
NewMenuItem('orders', 'Transactions', 'Synchronise KwaMoja to OpenCart Hourly', '/OcKwaMojaToOpenCartHourly.php', 12);
NewMenuItem('orders', 'Transactions', 'Synchronise KwaMoja to OpenCart Daily', '/OcKwaMojaToOpenCartDaily.php', 13);

NewConfigValue('OpenCartToKwaMoja_LastRun', '0000-00-00 00:00:00');
NewConfigValue('KwaMojaToOpenCartHourly_LastRun', '0000-00-00 00:00:00');
NewConfigValue('KwaMojaToOpenCartDaily_LastRun', '0000-00-00 00:00:00');

AddColumn('date_created', 'salescat', 'DATETIME', 'NOT NULL', '0000-00-00 00:00:00', 'active');
AddColumn('date_updated', 'salescat', 'TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', 'NOT NULL', '0000-00-00 00:00:00', 'date_created');
CreateTrigger('salescat', 'salescat_creation_timestamp', 'BEFORE INSERT', 'NEW', 'date_created=NOW()');

AddColumn('date_created', 'stockmaster', 'DATETIME', 'NOT NULL', '0000-00-00 00:00:00', 'lastcostupdate');
AddColumn('date_updated', 'stockmaster', 'TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', 'NOT NULL', '0000-00-00 00:00:00', 'date_created');
CreateTrigger('stockmaster', 'stockmaster_creation_timestamp', 'BEFORE INSERT', 'NEW', 'date_created=NOW()');

AddColumn('date_created', 'salescatprod', 'DATETIME', 'NOT NULL', '0000-00-00 00:00:00', 'featured');
AddColumn('date_updated', 'salescatprod', 'TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', 'NOT NULL', '0000-00-00 00:00:00', 'date_created');
CreateTrigger('salescatprod', 'salescatprod_creation_timestamp', 'BEFORE INSERT', 'NEW', 'date_created=NOW()');

AddColumn('date_created', 'prices', 'DATETIME', 'NOT NULL', '0000-00-00 00:00:00', 'enddate');
AddColumn('date_updated', 'prices', 'TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', 'NOT NULL', '0000-00-00 00:00:00', 'date_created');
CreateTrigger('prices', 'prices_creation_timestamp', 'BEFORE INSERT', 'NEW', 'date_created=NOW()');

AddColumn('date_created', 'locstock', 'DATETIME', 'NOT NULL', '0000-00-00 00:00:00', 'bin');
AddColumn('date_updated', 'locstock', 'TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', 'NOT NULL', '0000-00-00 00:00:00', 'date_created');
CreateTrigger('locstock', 'lockstock_creation_timestamp', 'BEFORE INSERT', 'NEW', 'date_created=NOW()');

AddColumn('date_created', 'relateditems', 'DATETIME', 'NOT NULL', '0000-00-00 00:00:00', 'related');
AddColumn('date_updated', 'relateditems', 'TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', 'NOT NULL', '0000-00-00 00:00:00', 'date_created');
CreateTrigger('relateditems', 'relateditems_creation_timestamp', 'BEFORE INSERT', 'NEW', 'date_created=NOW()');

UpdateDBNo(basename(__FILE__, '.php'));

?>