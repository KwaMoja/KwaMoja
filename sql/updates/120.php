<?php

AddColumn('userid', 'stockmoves', 'VARCHAR(20)', 'NOT NULL', '', 'trandate');

UpdateDBNo(basename(__FILE__, '.php'));

?>