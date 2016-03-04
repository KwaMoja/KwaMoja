<?php

CreateTable('securitytokens',
"CREATE TABLE `securitytokens` (
  `tokenid` int(11) NOT NULL DEFAULT '0',
  `tokenname` text NOT NULL,
  PRIMARY KEY (`tokenid`)
)");

InsertRecord('securitytokens', array('tokenid'), array(0), array('tokenid', 'tokenname'), array(0, 'Main Index Page'));
InsertRecord('securitytokens', array('tokenid'), array(1), array('tokenid', 'tokenname'), array(1, 'Order Entry/Inquiries customer access only'));
InsertRecord('securitytokens', array('tokenid'), array(2), array('tokenid', 'tokenname'), array(2, 'Basic Reports and Inquiries with selection options'));
InsertRecord('securitytokens', array('tokenid'), array(3), array('tokenid', 'tokenname'), array(3, 'Credit notes and AR management'));
InsertRecord('securitytokens', array('tokenid'), array(4), array('tokenid', 'tokenname'), array(4, 'Purchasing data/PO Entry/Reorder Levels '));
InsertRecord('securitytokens', array('tokenid'), array(5), array('tokenid', 'tokenname'), array(5, 'Accounts Payable'));
InsertRecord('securitytokens', array('tokenid'), array(6), array('tokenid', 'tokenname'), array(6, 'Petty Cash'));
InsertRecord('securitytokens', array('tokenid'), array(7), array('tokenid', 'tokenname'), array(7, 'Bank Reconciliations'));
InsertRecord('securitytokens', array('tokenid'), array(8), array('tokenid', 'tokenname'), array(8, 'General ledger reports/inquiries'));
InsertRecord('securitytokens', array('tokenid'), array(9), array('tokenid', 'tokenname'), array(9, 'Supplier centre - Supplier access only'));
InsertRecord('securitytokens', array('tokenid'), array(10), array('tokenid', 'tokenname'), array(10, 'General Ledger Maintenance, stock valuation & Configuration'));
InsertRecord('securitytokens', array('tokenid'), array(11), array('tokenid', 'tokenname'), array(11, 'Inventory Management and Pricing'));
InsertRecord('securitytokens', array('tokenid'), array(12), array('tokenid', 'tokenname'), array(12, 'Unknown'));
InsertRecord('securitytokens', array('tokenid'), array(13), array('tokenid', 'tokenname'), array(13, 'Unknown'));
InsertRecord('securitytokens', array('tokenid'), array(14), array('tokenid', 'tokenname'), array(14, 'Unknown'));
InsertRecord('securitytokens', array('tokenid'), array(15), array('tokenid', 'tokenname'), array(15, 'User Management and System Administration'));
InsertRecord('securitytokens', array('tokenid'), array(1000), array('tokenid', 'tokenname'), array(1000, 'User can view and alter sales prices'));
InsertRecord('securitytokens', array('tokenid'), array(1001), array('tokenid', 'tokenname'), array(1001, 'User can bypass purchasing security and go straight from order to invoice'));

$SQL ="INSERT INTO securitygroups SELECT secroleid, tokenid FROM securityroles,securitytokens)";
executeSQL($SQL);

?>