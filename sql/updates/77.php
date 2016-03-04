<?php

CreateTable('bankaccountusers',
"CREATE TABLE IF NOT EXISTS `bankaccountusers` (
  `accountcode` varchar(20) NOT NULL DEFAULT '',
  `userid` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (accountcode,userid)
)");

$SQL = "INSERT IGNORE INTO bankaccountusers (SELECT bankaccounts.accountcode, www_users.userid FROM bankaccounts, www_users)";
$Result = DB_query($SQL);

NewScript('BankAccountUsers.php', 15);

NewMenuItem('system', 'Maintenance', _('Bank Account Authorised Users'), '/BankAccountUsers.php', 13);

UpdateDBNo(basename(__FILE__, '.php'));

?>