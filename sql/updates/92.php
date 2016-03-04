<?php

ChangeColumnSize('consignment', 'debtortrans', 'VARCHAR(20)', 'NOT NULL', '', 20);

UpdateDBNo(basename(__FILE__, '.php'));

?>