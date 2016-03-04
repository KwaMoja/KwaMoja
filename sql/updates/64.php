<?php

DeleteConfigValue('ShopShowLeftCategoryMenu');
DeleteConfigValue('ShopShowInfoLinks');
DeleteConfigValue('ShopShowTopCategoryMenu');
DeleteConfigValue('ShopShowLogoAndShopName');

UpdateDBNo(basename(__FILE__, '.php'));

?>