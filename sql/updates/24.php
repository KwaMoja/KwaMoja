<?php

AddColumn('packages', 'debtortrans', 'INT', 'NOT NULL', 1, 'consignment');

UpdateDBNo(basename(__FILE__, '.php'));

?>