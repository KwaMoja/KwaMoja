<?php

NewConfigValue('ShopShowInfoLinks', '1', $db);
NewConfigValue('ShopTitle', 'Shop Home', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>