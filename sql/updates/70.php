<?php

NewScript('ImportSalesPriceList.php', 15, $db);
NewMenuItem('orders', 'Maintenance', _('Import Sales Prices From CSV File'), '/ImportSalesPriceList.php', 4, $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>