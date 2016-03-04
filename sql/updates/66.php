<?php

NewScript('Z_UpdateSalesAnalysisWithLatestCustomerData.php', 15);
NewScript('Dashboard.php', 0);
NewMenuItem('Utilities', 'Transactions', _('Update sales analysis with latest customer data'), '/Z_UpdateSalesAnalysisWithLatestCustomerData.php', 12);

UpdateDBNo(basename(__FILE__, '.php'));

?>