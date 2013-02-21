<?php

AddColumn('email', 'custcontacts', 'VARCHAR', 'NOT NULL', '', 'notes', $db);

NewConfigValue('WorkingDaysWeek', '5', $db);

NewScript('PDFQuotationPortrait.php', '2', $db);

ChangeConfigValue('VersionNumber', '4.04.5', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>