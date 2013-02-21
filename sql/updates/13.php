<?php

NewScript('SecurityTokens.php', 15, $db);
NewScript('SalesByTypePeriodInquiry.php', 2, $db);
NewScript('SalesCategoryPeriodInquiry.php', 2, $db);
NewScript('SalesTopItemsInquiry.php', 2, $db);

ChangeConfigValue('VersionNumber', '4.04', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>