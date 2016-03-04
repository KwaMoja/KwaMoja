<?php

NewScript('StockCategorySalesInquiry.php', '2');
NewMenuItem('orders', 'Reports', _('Sales By Category By Item Inquiry'), '/StockCategorySalesInquiry.php', 6);

UpdateDBNo(basename(__FILE__, '.php'));

?>