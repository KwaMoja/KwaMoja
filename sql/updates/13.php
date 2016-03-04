<?php

NewScript('SecurityTokens.php', 15);
NewScript('SalesByTypePeriodInquiry.php', 2);
NewScript('SalesCategoryPeriodInquiry.php', 2);
NewScript('SalesTopItemsInquiry.php', 2);

ChangeConfigValue('VersionNumber', '4.04');

UpdateDBNo(basename(__FILE__, '.php'));

?>