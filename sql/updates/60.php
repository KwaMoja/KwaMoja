<?php

AddColumn('defaulttaxcatid', 'stockcategory', 'INT (1)', 'NOT NULL', 1, 'categorydescription', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>