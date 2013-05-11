<?php

InsertRecord('mailgroups', array('id', 'groupname'), array(3,'OffersReceivedResultRecipients'), array('id', 'groupname'), array(3,'OffersReceivedResultRecipients'), $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>