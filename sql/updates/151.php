<?php

NewConfigValue('NewBranchesMustBeAuthorised', 0);

CreateTable('custbranchattachments',
"CREATE TABLE IF NOT EXISTS `custbranchattachments` (
  `debtorno` varchar(10) NOT NULL,
  `branchcode` varchar(10) NOT NULL,
  `name` VARCHAR(30) NOT NULL,
  `type` VARCHAR(30) NOT NULL,
  `size` INT NOT NULL,
  `content` MEDIUMBLOB NOT NULL,
  PRIMARY KEY (`debtorno`, `branchcode`, `name`)
)");

NewScript('EnableBranches.php', '15');

NewMenuItem('AR', 'Maintenance', _('Enable CustomerBranches'), '/EnableBranches.php', 8);

UpdateDBNo(basename(__FILE__, '.php'));

?>