<?php

AddColumn('usedforwo', 'locations', 'TINYINT(1)', 'NOT NULL', '1', 'internalrequest');

UpdateDBNo(basename(__FILE__, '.php'));

?>