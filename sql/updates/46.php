<?php

NewMenuItem('stock', 'Maintenance', _('Brands Maintenance'), '/Manufacturers.php', 11);

NewMenuItem('GL', 'Transactions', _('Import Bank Transactions'), '/ImportBankTrans.php', 5);

NewMenuItem('FA', 'Reports', _('My Maintenance Schedule'), '/MaintenanceUserSchedule.php', 2);
NewMenuItem('FA', 'Reports', _('Maintenance Reminder Emails'), '/MaintenanceReminders.php', 3);

NewMenuItem('FA', 'Maintenance', _('Maintenance Tasks'), '/MaintenanceTasks.php', 3);

NewMenuItem('system', 'Transactions', _('Web-Store Configuration'), '/ShopParameters.php', 21);

NewScript('MaintenanceTasks.php', 1);
NewScript('MaintenanceUserSchedule.php', 1);
NewScript('MaintenanceReminders.php', 1);

NewScript('ImportBankTransAnalysis.php', 11);
NewScript('ImportBankTrans.php', 11);

NewScript('Manufacturers.php', 15);
NewScript('SalesCategoryDescriptions.php', 15);
NewScript('ShopParameters.php', 15);

UpdateDBNo(basename(__FILE__, '.php'));

?>