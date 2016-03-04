<?php

InsertRecord('mailgroups', array('id', 'groupname'), array(2,'SalesAnalysisReportRecipients'), array('id', 'groupname'), array(2,'SalesAnalysisReportRecipients'));

NewScript('MailSalesReport_csv.php',  '15');

UpdateDBNo(basename(__FILE__, '.php'));

?>