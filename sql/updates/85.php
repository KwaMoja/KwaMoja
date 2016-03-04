<?php

NewScript('SalesTopCustomersInquiry.php', 2);

NewMenuItem('orders', 'Reports', _('Top Customers Inquiry'), '/SalesTopCustomersInquiry.php', 16);

UpdateDBNo(basename(__FILE__, '.php'));

?>