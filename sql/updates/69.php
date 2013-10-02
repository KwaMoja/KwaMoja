<?php

NewScript('PurchaseByPrefSupplier.php', 4, $db);
NewMenuItem('PO', 'Transactions', _('Create a PO based on the preferred supplier'), '/PurchaseByPrefSupplier.php', 9, $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>