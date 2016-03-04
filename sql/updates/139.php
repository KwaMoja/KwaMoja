<?php

NewScript('SupplierGRNAndInvoiceInquiry.php', '5');

AddColumn('supplierref', 'grns', 'VARCHAR(30)', 'NOT NULL', "", 'reference');

UpdateDBNo(basename(__FILE__, '.php'));

?>