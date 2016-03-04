<?php

AddColumn('webcart', 'currencies', 'TINYINT(1)', 'NOT NULL', 1, 'rate');

UpdateDBNo(basename(__FILE__, '.php'));

?>