<?php

AddColumn('destinationcountry', 'freightcosts', 'VARCHAR( 40 )', 'NOT NULL', '', 'locationfrom', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>