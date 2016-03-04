<?php

ChangeConfigName('DefaultTaxLevel', 'DefaultTaxCategory');
ChangeConfigValue('part_pics_dir', 'companies/' . $_SESSION['DatabaseName'] . '/part_pics');
ChangeConfigValue('EDI_Incoming_Orders', 'companies/' . $_SESSION['DatabaseName'] . '/EDI_Incoming_Orders');
ChangeConfigValue('EDI_MsgPending', 'companies/' . $_SESSION['DatabaseName'] . '/EDI_MsgPending');
ChangeConfigValue('EDI_Sent', 'companies/' . $_SESSION['DatabaseName'] . '/EDI_Sent');
ChangeConfigValue('companies/' . $_SESSION['DatabaseName'] . '/reports', 'reports_dir');
DropColumn('pinno', 'www_users');
DropColumn('swipecard', 'www_users');
ChangeColumnType('bankact', 'suppliers', 'VARCHAR( 30 )', 'NOT NULL', '');

UpdateDBNo(basename(__FILE__, '.php'));

?>