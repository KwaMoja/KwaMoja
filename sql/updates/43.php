<?php

NewMenuItem('Utilities', 'Transactions', _('Import GL Transactions from a csv file'), '/Z_ImportGLTransactions.php', 11, $db);
NewScript('/Z_ImportGLTransactions.php', 15, $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>