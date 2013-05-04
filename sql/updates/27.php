<?php

NewScript('PluginUpload.php', '15', $db);
NewScript('PluginInstall.php', '15', $db);
NewScript('PluginUnInstall.php', '15', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>