

CREATE TABLE IF NOT EXISTS `dashboard_scripts` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `scripts` varchar(78) CHARACTER SET utf8 NOT NULL,
  `pagesecurity` int(11) NOT NULL DEFAULT '1',
  `description` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;


INSERT INTO `dashboard_scripts` (`id`, `scripts`, `pagesecurity`, `description`) VALUES
(1, 'total_dashboard.php', 1, 'Shows total for sales, purchase and outstanding orders'),
(2, 'customer_orders.php', 2, 'Shows latest customer orders have been placed.'),
(3, 'unpaid_invoice.php', 2, 'Shows Outstanding invoices'),
(4, 'latest_po.php', 3, 'Shows latest Purchase orders'),
(5, 'latest_po_auth.php', 3, 'Shows Purchase orders to authorize'),
(6, 'latest_stock_status.php', 3, 'Shows latest stock status'),
(7, 'work_orders.php', 3, 'Shows latest work orders'),
(8, 'mrp_dashboard.php', 3, 'Shows latest MRP'),
(9, 'bank_trans.php', 2, 'Shows latest bank transactions');



CREATE TABLE IF NOT EXISTS `dashboard_users` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `userid` varchar(20) NOT NULL,
  `scripts` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;


INSERT INTO `dashboard_users` (`id`, `userid`, `scripts`) VALUES
(3, 'admin', '1,2,5,7');

