<?php

NewConfigValue('ShopShowOnlyAvailableItems', '0', $db);
NewConfigValue('ShopShowQOHColumn', '1', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>