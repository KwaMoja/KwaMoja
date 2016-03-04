<?php

DropConstraint('stockcatproperties', 'stockcatproperties_ibfk_2');
DropConstraint('stockcatproperties', 'stockcatproperties_ibfk_3');

RemoveScript('FixedAssetList.php');
RemoveScript('MenuAccess.php');
RemoveScript('OrderEntryDiscountPricing.php');
RemoveScript('PrintSalesOrder.php');
RemoveScript('ReportBug.php');
RemoveScript('ReportletContainer.php');
RemoveScript('SystemCheck.php');

UpdateDBNo(basename(__FILE__, '.php'));

?>