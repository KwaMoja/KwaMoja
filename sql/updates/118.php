<?php

NewScript('Z_RebuildSalesAnalysis.php', '15');
NewScript('Z_AutoCustomerAllocations.php', '15');
NewScript('CustomerBalancesMovement.php', '3');
NewScript('Z_UpdateItemCosts.php', '3');

NewMenuItem('system', 'Maintenance', _('Rebuild sales analysis Records'), '/Z_RebuildSalesAnalysis.php', 15);
NewMenuItem('system', 'Maintenance', _('Automaticall allocate customer receipts and credit notes'), '/Z_AutoCustomerAllocations.php', 16);
NewMenuItem('AR', 'Reports', _('Customer Activity and Balances'), '/CustomerBalancesMovement.php', 12);
NewMenuItem('system', 'Maintenance', _('Update Item Costs from a CSV file'), '/Z_UpdateItemCosts.php', 12);

UpdateDBNo(basename(__FILE__, '.php'));

?>