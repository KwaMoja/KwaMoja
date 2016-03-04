<?php

DropColumn('lastcurcostdate', 'stockmaster');
AddColumn('lastcostupdate', 'stockmaster', 'DATE', 'NOT NULL', "0000-00-00", 'netweight');

NewConfigValue('InventoryManagerEmail',  'inventory@example.com');

AddIndex(array('ref'), 'banktrans', 'ref');

AddColumn('tag', 'pcexpenses', 'TINYINT( 4 )', 'NOT NULL', "0", 'glaccount');

DropConstraint('debtotrans', 'debtortrans_ibfk_1');

ChangeConfigValue('VersionNumber', '4.06.6');

UpdateDBNo(basename(__FILE__, '.php'));

?>