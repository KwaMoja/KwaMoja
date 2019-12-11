<?php
NewScript('DashboardConfig.php', 15);
NewMenuItem('system', 'Transactions', _('Configure the Dashboard'), '/DashboardConfig.php', 8);

NewScript('total_dashboard.php', 2);
NewScript('customer_orders.php', 2);
NewScript('unpaid_invoice.php', 2);
NewScript('latest_stock_status.php', 2);
NewScript('work_orders.php', 2);
NewScript('mrp_dashboard.php', 2);
NewScript('bank_trans.php', 2);
NewScript('latest_po.php', 2);
NewScript('latest_po_auth.php', 2);

UpdateDBNo(basename(__FILE__, '.php'));

?>