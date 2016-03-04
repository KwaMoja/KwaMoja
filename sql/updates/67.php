<?php

ChangeColumnSize('areacode', 'areas', 'CHAR(3)', 'NOT NULL', '', 3);

UpdateDBNo(basename(__FILE__, '.php'));

?>