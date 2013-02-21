<?php

ChangeColumnType('language', 'www_users', 'VARCHAR(10)', 'NOT NULL', 'en_GB.utf8', $db);
DropTable('buckets', 'availdate', $db);


UpdateDBNo(basename(__FILE__, '.php'), $db);

?>