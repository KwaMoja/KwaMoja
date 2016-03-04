<?php

AddColumn('userid', 'stockrequest', 'VARCHAR( 20 )', 'NOT NULL', '', 'dispatchid');

NewMenuItem('stock', 'Transactions', _('Amend an internal stock request'), '/InternalStockRequest.php?Edit=Yes', 11);

UpdateDBNo(basename(__FILE__, '.php'));

?>