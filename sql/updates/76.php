<?php

NewScript('Z_ImportDebtors.php', 15);
NewScript('Z_ImportSuppliers.php', 15);

NewMenuItem('Utilities', 'Transactions', _('Import Debtors'), '/Z_ImportDebtors.php', 13);
NewMenuItem('Utilities', 'Transactions', _('Import Suppliers'), '/Z_ImportSuppliers.php', 14);

UpdateDBNo(basename(__FILE__, '.php'));

?>