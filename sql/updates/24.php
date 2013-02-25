<?php

AddColumn('packages', 'debtortrans', 'INT', 'NOT NULL', 1, 'consignment', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>