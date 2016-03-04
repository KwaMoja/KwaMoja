<?php

NewConfigValue('ShopShowLeftCategoryMenu', '1');
NewConfigValue('ShopStockLocations', '1');
DeleteConfigValue('ShopAdditionalStockLocations');

UpdateDBNo(basename(__FILE__, '.php'));

?>