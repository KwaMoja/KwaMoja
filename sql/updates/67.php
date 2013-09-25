<?php

ChangeColumnSize('areacode', 'areas', 'CHAR(3)', 'NOT NULL', '', 3, $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>