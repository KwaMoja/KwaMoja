<?php

NewScript('CollectiveWorkOrderCost.php', '2');

NewMenuItem('manuf', 'Maintenance', _('Multiple Work Orders Total Cost Inquiry'), '/CollectiveWorkOrderCost.php', 7);

UpdateDBNo(basename(__FILE__, '.php'));

?>