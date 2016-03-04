<?php

AddColumn('email', 'custcontacts', 'VARCHAR(78)', 'NOT NULL', '', 'notes');

NewConfigValue('WorkingDaysWeek', '5');

NewScript('PDFQuotationPortrait.php', '2');

ChangeConfigValue('VersionNumber', '4.04.5');

UpdateDBNo(basename(__FILE__, '.php'));

?>