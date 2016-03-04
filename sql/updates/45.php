<?php

AddColumn('restrictlocations', 'www_users', 'TINYINT(1)', 'NOT NULL', 1, 'defaultlocation');

UpdateDBNo(basename(__FILE__, '.php'));

?>