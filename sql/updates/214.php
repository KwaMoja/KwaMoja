<?php
NewScript('SupplierGroups.php', 15);
NewMenuItem('AP', 'Maintenance', _('Maintain Supplier Groups'), '/SupplierGroups.php', 3);

UpdateDBNo(basename(__FILE__, '.php'));

?>