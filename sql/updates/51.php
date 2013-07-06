<?php

NewConfigValue('ShopShowLeftCategoryMenu', '1', $db);
NewConfigValue('ShopStockLocations', '1', $db);
DeleteConfigValue('ShopAdditionalStockLocations', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>