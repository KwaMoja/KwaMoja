<?php

InsertRecord('mailgroups', array('id', 'groupname'), array(1,'ChkListingRecipients'), array('id', 'groupname'), array(1,'ChkListingRecipients'), $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>