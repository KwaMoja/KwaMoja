<?php

AddColumn('pettycash', 'bankaccounts', 'TINYINT(1)', 'NOT NULL', 0, 'bankaddress');
AddColumn('userid', 'banktrans', 'VARCHAR(20)', 'NOT NULL', '', 'currcode');
AddColumn('defaulttag', 'www_users', 'TINYINT(4)', 'NOT NULL', 1, 'fontsize');

UpdateDBNo(basename(__FILE__, '.php'));

?>