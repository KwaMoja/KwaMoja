<?php

InsertRecord('mailgroups', array('id', 'groupname'), array(2,'SalesAnalysisReportRecipients'), array('id', 'groupname'), array(2,'SalesAnalysisReportRecipients'), $db);

NewScript('MailSalesReport_csv.php',  '15', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>