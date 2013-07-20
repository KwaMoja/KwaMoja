<?php

AddColumn('active', 'salescat', 'INT (1)', 'NOT NULL', 1, 'salescatname', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>