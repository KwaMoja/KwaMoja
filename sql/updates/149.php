<?php

CreateTable('salesorderattachments',
"CREATE TABLE IF NOT EXISTS `salesorderattachments` (
  `orderno` int(11) NOT NULL,
  `name` VARCHAR(30) NOT NULL,
  `type` VARCHAR(30) NOT NULL,
  `size` INT NOT NULL,
  `content` MEDIUMBLOB NOT NULL,
  PRIMARY KEY (`orderno`, `name`)
)");

$Attachments = glob('companies/' . $_SESSION['DatabaseName'] . '/Attachments/*');
foreach ($Attachments as $Attachment) {
	if (mime_content_type($Attachment) != 'directory') {
		$OrderNumber = pathinfo($Attachment, PATHINFO_BASENAME);
		$Name = $OrderNumber;
		$Type = mime_content_type($Attachment);
		$Size = filesize($Attachment);
		$fp = fopen($Attachment, 'r');
		$Content = fread($fp, $Size);
		$Content = addslashes($Content);
		fclose($fp);
		$SQL = "INSERT INTO salesorderattachments VALUES('" . $OrderNumber . "',
														 '" . $Name . "',
														 '" . $Type . "',
														 " . $Size . ",
														 '" . $Content . "'
														)";
		$Result = DB_query($SQL);
	}
}

UpdateDBNo(basename(__FILE__, '.php'));

?>