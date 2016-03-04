<?php

ChangeColumnSize('tel', 'purchorders', 'VARCHAR(30)', 'NOT NULL', '', 30);
UpdateDBNo(basename(__FILE__, '.php'));

?>