<?php

ChangeColumnSize('reference', 'stockmoves', 'VARCHAR(100)', 'NOT NULL', '', 100);

UpdateDBNo(basename(__FILE__, '.php'));

?>