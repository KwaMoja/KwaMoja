<?php

ChangeColumnSize('host', 'emailsettings', 'VARCHAR(50)', 'NOT NULL', '', 50);
ChangeColumnSize('username', 'emailsettings', 'VARCHAR(50)', 'NOT NULL', '', 50);
ChangeColumnSize('password', 'emailsettings', 'VARCHAR(50)', 'NOT NULL', '', 50);

UpdateDBNo(basename(__FILE__, '.php'));

?>