<?php

DeleteConfigValue('ShopShowLeftCategoryMenu', $db);
DeleteConfigValue('ShopShowInfoLinks', $db);
DeleteConfigValue('ShopShowTopCategoryMenu', $db);
DeleteConfigValue('ShopShowLogoAndShopName', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>