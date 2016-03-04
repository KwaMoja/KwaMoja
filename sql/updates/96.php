<?php

AddColumn('authorizerexpenses', 'pctabs', 'VARCHAR(20)', 'NOT NULL', '""', 'authorizer');

$SQL = "UPDATE pctabs SET authorizerexpenses=authorizer";

executeSQL($SQL);

NewMenuItem('PC', 'Transactions', 'Cash Authorisation', '/PcAuthorizeCheque.php', 4);
NewScript('PcAuthorizeCheque.php', 6);

UpdateDBNo(basename(__FILE__, '.php'));

?>