<?php

ChangeConfigValue('ShopFreightModule', 'ShopFreightMethod', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>