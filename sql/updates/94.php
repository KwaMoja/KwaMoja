<?php

AddColumn('closecomments', 'workorders', 'text', 'NOT NULL', '""', 'closed');

UpdateDBNo(basename(__FILE__, '.php'));

?>