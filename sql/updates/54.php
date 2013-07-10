<?php

ChangeColumnName('kgs', 'stockmaster', 'DECIMAL( 20, 4 )', 'NOT NULL', '0.0000', 'grossweight', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>