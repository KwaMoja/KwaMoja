<?php

ChangeConfigValue('VersionNumber', '4.05', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>