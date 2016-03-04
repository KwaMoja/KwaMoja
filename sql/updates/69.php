<?php

NewScript('PurchaseByPrefSupplier.php', 4);
NewMenuItem('PO', 'Transactions', _('Create a PO based on the preferred supplier'), '/PurchaseByPrefSupplier.php', 9);

UpdateDBNo(basename(__FILE__, '.php'));

?>