<?php

CreateTable('glaccountusers',
"CREATE TABLE IF NOT EXISTS `glaccountusers` (
  `accountcode` varchar(20) NOT NULL,
  `userid` varchar(20) NOT NULL,
  `canview` tinyint(4) NOT NULL DEFAULT '0',
  `canupd` tinyint(4) NOT NULL DEFAULT '0'
)");

AddIndex(array('userid', 'accountcode'), 'glaccountusers', 'useraccount');
AddIndex(array('accountcode', 'userid'), 'glaccountusers', 'accountuser');

$SQL ="INSERT INTO glaccountusers (userid,
									accountcode,
									canview,
									canupd)
							SELECT www_users.userid,
									chartmaster.accountcode,
									1,
									1
							FROM www_users
							CROSS JOIN chartmaster
							LEFT JOIN glaccountusers
								ON www_users.userid = glaccountusers.userid
								AND chartmaster.accountcode = glaccountusers.accountcode
							WHERE glaccountusers.userid IS NULL";
$Result = DB_query($SQL);
NewScript('GLAccountUsers.php', '15');
NewScript('UserGLAccounts.php', '15');

NewMenuItem('GL', 'Maintenance', _('GL Accounts Authorised Users Maintenance'), '/GLAccountUsers.php', 6);
NewMenuItem('GL', 'Maintenance', _('User Authorised GL Accounts Maintenance'), '/UserGLAccounts.php', 7);

UpdateDBNo(basename(__FILE__, '.php'));

?>