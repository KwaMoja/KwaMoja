<?php

NewScript('ModuleEditor.php', 15, $db);
NewMenuItem('system', 'Transactions', _('Update Module Order'), '/ModuleEditor.php', '22', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>