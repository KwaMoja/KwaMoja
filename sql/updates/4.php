<?php

ChangeConfigName('DefaultTaxLevel', 'DefaultTaxCategory', $db);
ChangeConfigValue('part_pics_dir', 'companies/kwamoja/part_pics', $db);
ChangeConfigValue('EDI_Incoming_Orders', 'companies/kwamoja/EDI_Incoming_Orders', $db);
ChangeConfigValue('EDI_MsgPending', 'companies/kwamoja/EDI_MsgPending', $db);
ChangeConfigValue('EDI_Sent', 'companies/kwamoja/EDI_Sent', $db);
ChangeConfigValue('companies/kwamoja/reports', 'reports_dir', $db);
DropColumn('pinno', 'www_users', $db);
DropColumn('swipecard', 'www_users', $db);
ChangeColumnType('bankact', 'suppliers', 'VARCHAR( 30 )', 'NOT NULL', '', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>