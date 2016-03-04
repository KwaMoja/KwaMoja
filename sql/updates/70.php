<?php

NewScript('ImportSalesPriceList.php', 15);
NewMenuItem('orders', 'Maintenance', _('Import Sales Prices From CSV File'), '/ImportSalesPriceList.php', 4);

UpdateDBNo(basename(__FILE__, '.php'));

?>