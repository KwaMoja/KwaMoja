<?php

NewScript('GLAccountUsersCopyAuthority.php', '15');

NewMenuItem('GL', 'Maintenance', _('Copy Authority GL Accounts from user A to B'), '/GLAccountUsersCopyAuthority.php', 7);

UpdateDBNo(basename(__FILE__, '.php'));

?>