<?php

AddColumn('importformat', 'bankaccounts', 'VARCHAR(10)', 'NOT NULL', '', 'pettycash');

UpdateDBNo(basename(__FILE__, '.php'));

?>