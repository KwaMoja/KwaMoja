<?php

AddColumn('restrictlocations', 'www_users', 'TINYINT(1)', 'NOT NULL', 1, 'defaultlocation', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>