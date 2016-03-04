<?php

CreateTable('suppinvstogrn',
"CREATE TABLE `suppinvstogrn` (
  `suppinv` INT(11) NOT NULL ,
  `grnno` INT(11) NOT NULL ,
  PRIMARY KEY (`suppinv`, `grnno`),
  CONSTRAINT `suppinvstogrn_ibfk_1` FOREIGN KEY (`suppinv`) REFERENCES `supptrans` (`id`),
  CONSTRAINT `suppinvstogrn_ibfk_2` FOREIGN KEY (`grnno`) REFERENCES `grns` (`grnno`)
)");

UpdateDBNo(basename(__FILE__, '.php'));

?>