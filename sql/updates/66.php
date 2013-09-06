<?php

NewScript('Z_UpdateSalesAnalysisWithLatestCustomerData.php', 15, $db);
NewScript('Dashboard.php', 0, $db);
NewMenuItem('Utilities', 'Transactions', _('Update sales analysis with latest customer data'), '/Z_UpdateSalesAnalysisWithLatestCustomerData.php', 12, $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>