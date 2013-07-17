<?php

NewConfigValue('ShopShowTopCategoryMenu', '1', $db);
NewConfigValue('ShopShowLogoAndShopName', '1', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>