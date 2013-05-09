<?php

AddColumn('webcart', 'currencies', 'TINYINT(1)', 'NOT NULL', 1, 'rate', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>