<?php

AddColumn('glaccountcode', 'locations', 'VARCHAR(20)', 'NOT NULL', "", 'usedforwo');
AddColumn('allowinvoicing', 'locations', 'TINYINT(1)', 'NOT NULL', "1", 'glaccountcode');

UpdateDBNo(basename(__FILE__, '.php'));

?>