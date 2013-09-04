<?php

CreateTable('dashboard_scripts',
"CREATE TABLE IF NOT EXISTS `dashboard_scripts` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `scripts` varchar(78) CHARACTER SET utf8 NOT NULL,
  `pagesecurity` int(11) NOT NULL DEFAULT '1',
  `description` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
)",
$db);

InsertRecord('dashboard_scripts', array( 'id' , 'scripts', 'pagesecurity', 'description' ), array(NULL, 'total_dashboard.php', 1, _('Shows total for sales, purchase and outstanding orders')), array( 'id' , 'scripts', 'pagesecurity', 'description' ), array(NULL, 'total_dashboard.php', 1, _('Shows total for sales, purchase and outstanding orders')), $db);
InsertRecord('dashboard_scripts', array( 'id' , 'scripts', 'pagesecurity', 'description' ), array(NULL, 'customer_orders.php', 2, _('Shows latest customer orders have been placed.')), array( 'id' , 'scripts', 'pagesecurity', 'description' ), array(NULL, 'customer_orders.php', 1, _('Shows latest customer orders have been placed.')), $db);
InsertRecord('dashboard_scripts', array( 'id' , 'scripts', 'pagesecurity', 'description' ), array(NULL, 'unpaid_invoice.php', 2, _('Shows Outstanding invoices')), array( 'id' , 'scripts', 'pagesecurity', 'description' ), array(NULL, 'unpaid_invoice.php', 1, _('Shows Outstanding invoices')), $db);
InsertRecord('dashboard_scripts', array( 'id' , 'scripts', 'pagesecurity', 'description' ), array(NULL, 'latest_po.php', 3, _('Shows latest Purchase orders')), array( 'id' , 'scripts', 'pagesecurity', 'description' ), array(NULL, 'latest_po.php', 1, _('Shows latest Purchase orders')), $db);
InsertRecord('dashboard_scripts', array( 'id' , 'scripts', 'pagesecurity', 'description' ), array(NULL, 'latest_po_auth.php', 3, _('Shows Purchase orders to authorise')), array( 'id' , 'scripts', 'pagesecurity', 'description' ), array(NULL, 'latest_po_auth.php', 1, _('Shows Purchase orders to authorise')), $db);
InsertRecord('dashboard_scripts', array( 'id' , 'scripts', 'pagesecurity', 'description' ), array(NULL, 'latest_stock_status.php', 3, _('Shows latest stock status')), array( 'id' , 'scripts', 'pagesecurity', 'description' ), array(NULL, 'latest_stock_status.php', 1, _('Shows latest stock status')), $db);
InsertRecord('dashboard_scripts', array( 'id' , 'scripts', 'pagesecurity', 'description' ), array(NULL, 'work_orders.php', 3, _('Shows latest work orders')), array( 'id' , 'scripts', 'pagesecurity', 'description' ), array(NULL, 'work_orders.php', 1, _('Shows latest work orders')), $db);
InsertRecord('dashboard_scripts', array( 'id' , 'scripts', 'pagesecurity', 'description' ), array(NULL, 'mrp_dashboard.php', 3, _('Shows latest MRP')), array( 'id' , 'scripts', 'pagesecurity', 'description' ), array(NULL, 'mrp_dashboard.php', 1, _('Shows latest MRP')), $db);
InsertRecord('dashboard_scripts', array( 'id' , 'scripts', 'pagesecurity', 'description' ), array(NULL, 'bank_trans.php', 2, _('Shows latest bank transactions')), array( 'id' , 'scripts', 'pagesecurity', 'description' ), array(NULL, 'bank_trans.php', 1, _('Shows latest bank transactions')), $db);

CreateTable('dashboard_users',
"CREATE TABLE IF NOT EXISTS `dashboard_users` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userid` varchar(20) NOT NULL,
  `scripts` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
)",
$db);

InsertRecord('dashboard_users', array( 'id' , 'userid', 'scripts' ), array(NULL, 'admin', '1,2,5,7'), array( 'id' , 'userid', 'scripts' ), array(NULL, 'admin', '1,2,5,7'), $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>