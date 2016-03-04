<?php
DB_IgnoreForeignKeys();
CreateTable('stockcosts',
"CREATE TABLE `stockcosts` (
  `stockid` varchar(20) NOT NULL DEFAULT '',
  `materialcost` double NOT NULL DEFAULT 0.0,
  `labourcost` double NOT NULL DEFAULT 0.0,
  `overheadcost` double NOT NULL DEFAULT 0.0,
  `costfrom` datetime NOT NULL DEFAULT '0000-00-00',
  `succeeded` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`stockid`, `costfrom`),
  CONSTRAINT ` stockcosts _ibfk_1` FOREIGN KEY (`stockid`) REFERENCES `stockmaster` (`stockid`)
)");

$SQL = "INSERT IGNORE INTO stockcosts SELECT stockid, materialcost, labourcost, overheadcost, CURRENT_TIME, 0 FROM stockmaster";
$Result = DB_query($SQL);

DropColumn('materialcost', 'stockmaster');
DropColumn('labourcost', 'stockmaster');
DropColumn('overheadcost', 'stockmaster');
DB_ReinstateForeignKeys();
UpdateDBNo(basename(__FILE__, '.php'));

?>