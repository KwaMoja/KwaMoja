<?php

NewScript('ModuleEditor.php', 15);
NewMenuItem('system', 'Transactions', _('Update Module Order'), '/ModuleEditor.php', '22');

UpdateDBNo(basename(__FILE__, '.php'));

?>