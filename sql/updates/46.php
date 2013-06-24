<?php

NewMenuItem('stock', 'Maintenance', _('Brands Maintenance'), '/Manufacturers.php', 11, $db);

NewMenuItem('GL', 'Transactions', _('Import Bank Transactions'), '/ImportBankTrans.php', 5, $db);

NewMenuItem('FA', 'Reports', _('My Maintenance Schedule'), '/MaintenanceUserSchedule.php', 2, $db);
NewMenuItem('FA', 'Reports', _('Maintenance Reminder Emails'), '/MaintenanceReminders.php', 3, $db);

NewMenuItem('FA', 'Maintenance', _('Maintenance Tasks'), '/MaintenanceTasks.php', 3, $db);

NewMenuItem('system', 'Transactions', _('Web-Store Configuration'), '/ShopParameters.php', 21, $db);

NewScript('MaintenanceTasks.php', 1, $db);
NewScript('MaintenanceUserSchedule.php', 1, $db);
NewScript('MaintenanceReminders.php', 1, $db);

NewScript('ImportBankTransAnalysis.php', 11, $db);
NewScript('ImportBankTrans.php', 11, $db);

NewScript('Manufacturers.php', 15, $db);
NewScript('SalesCategoryDescriptions.php', 15, $db);
NewScript('ShopParameters.php', 15, $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>