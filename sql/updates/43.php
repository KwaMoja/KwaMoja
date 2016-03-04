<?php

NewMenuItem('Utilities', 'Transactions', _('Import GL Transactions from a csv file'), '/Z_ImportGLTransactions.php', 11);
NewScript('/Z_ImportGLTransactions.php', 15);

UpdateDBNo(basename(__FILE__, '.php'));

?>