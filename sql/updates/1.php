<?php

ChangeColumnType('language', 'www_users', 'VARCHAR(10)', 'NOT NULL', 'en_GB.utf8');
DropTable('buckets', 'availdate');


UpdateDBNo(basename(__FILE__, '.php'));

?>