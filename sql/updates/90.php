<?php

NewScript('PDFWOPrint.php', 11);
NewScript('PDFFGLabel.php', 11);
NewScript('PDFQALabel.php', 2);

AddColumn('comments', 'woitems', 'text', 'NULL', 'NULL', 'nextlotsnref');

$DirHandle = dir($PathPrefix . 'companies/');
while (false !== ($CompanyEntry = $DirHandle->read())) {
	if (is_dir($PathPrefix . 'companies/' . $CompanyEntry) and $CompanyEntry != '..' and $CompanyEntry != '' and $CompanyEntry != '.') {
		copy($PathPrefix . 'companies/' . $DefaultDatabase . '/FormDesigns/FGLabel.xml', $PathPrefix . 'companies/' . $CompanyEntry . '/FormDesigns/FGLabel.xml');
		copy($PathPrefix . 'companies/' . $DefaultDatabase . '/FormDesigns/WOPaperwork.xml', $PathPrefix . 'companies/' . $CompanyEntry . '/FormDesigns/WOPaperwork.xml');
		copy($PathPrefix . 'companies/' . $DefaultDatabase . '/FormDesigns/QALabel.xml', $PathPrefix . 'companies/' . $CompanyEntry . '/FormDesigns/QALabel.xml');
	}
}
$DirHandle->close();

UpdateDBNo(basename(__FILE__, '.php'));

?>