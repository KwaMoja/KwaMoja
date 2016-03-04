<?php

NewScript('UserBankAccounts.php', '15');

NewMenuItem('GL', 'Maintenance', _('User Authorized Bank Accounts'), '/UserBankAccounts.php', 9);

UpdateDBNo(basename(__FILE__, '.php'));

?>