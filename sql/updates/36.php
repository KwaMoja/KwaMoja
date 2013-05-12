<?php

InsertRecord('mailgroups', array('id', 'groupname'), array(4,'InventoryValuationRecipients'), array('id', 'groupname'), array(4,'InventoryValuationRecipients'), $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>