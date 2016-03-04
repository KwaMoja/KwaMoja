<?php

AddColumn('authoriser', 'purchorders', 'VARCHAR(20)', 'NOT NULL', '', 'initiator');

$SQL = "UPDATE purchorders SET authoriser=initiator";
$Result = DB_query($SQL);

$DirHandle = dir($PathPrefix . 'companies/');
while (false !== ($CompanyEntry = $DirHandle->read())) {
	if (is_dir($PathPrefix . 'companies/' . $CompanyEntry) and $CompanyEntry != '..' and $CompanyEntry != '' and $CompanyEntry != '.') {
		copy($PathPrefix . 'companies/' . $DefaultDatabase . '/FormDesigns/PurchaseOrder.xml', $PathPrefix . 'companies/' . $CompanyEntry . '/FormDesigns/PurchaseOrder.xml');
	}
}
$DirHandle->close();

UpdateDBNo(basename(__FILE__, '.php'));

?>