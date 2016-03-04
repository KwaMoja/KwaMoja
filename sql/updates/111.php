<?php

AddColumn('createdate', 'stockserialitems', 'DATETIME', 'NULL', Date('Y-m-d H:i:s'), 'qualitytext');
AddIndex(array('createdate'), 'stockserialitems', 'CreateDate');

UpdateField('stockserialitems', 'createdate', 'NULL', "stockid LIKE '%'");

executeSQL('UPDATE stockserialitems as stockserialitems SET createdate=
(SELECT  trandate FROM (select trandate, stockserialitems.serialno, stockserialitems.stockid from stockserialitems
LEFT JOIN stockserialmoves ON stockserialitems.serialno=stockserialmoves.serialno
LEFT JOIN stockmoves ON stockserialmoves.stockmoveno=stockmoves.stkmoveno
GROUP BY stockserialitems.stockid, stockserialitems.serialno
ORDER BY trandate) as ssi
WHERE ssi.serialno=stockserialitems.serialno
AND ssi.stockid=stockserialitems.stockid)');

NewScript('GeneratePickingList.php', '11');
NewScript('PickingLists.php', '11');
NewScript('PickingListsControlled.php', '11');
NewScript('SelectPickingLists.php', '11');

AddColumn('internalcomment', 'salesorders', 'BLOB', 'NULL', NULL, 'salesperson');

CreateTable('pickreq',
"CREATE TABLE pickreq (
  prid INT(11) NOT NULL AUTO_INCREMENT,
  initiator VARCHAR(20) NOT NULL DEFAULT '',
  shippedby VARCHAR(20) NOT NULL DEFAULT '',
  initdate DATE NOT NULL DEFAULT '0000-00-00',
  requestdate DATE NOT NULL DEFAULT '0000-00-00',
  shipdate DATE NOT NULL DEFAULT '0000-00-00',
  status VARCHAR(12) NOT NULL DEFAULT '',
  comments TEXT DEFAULT NULL,
  closed TINYINT NOT NULL DEFAULT '0',
  loccode VARCHAR(5) NOT NULL DEFAULT '',
  orderno INT(11) NOT NULL DEFAULT '1',
  consignment VARCHAR(15) NOT NULL DEFAULT '',
  packages INT(11) NOT NULL DEFAULT '1' COMMENT 'number of cartons',
  PRIMARY KEY (`prid`),
  KEY (`orderno`),
  KEY (`requestdate`),
  KEY (`shipdate`),
  KEY (`status`),
  KEY (`closed`),
  CONSTRAINT FOREIGN KEY(`loccode`) REFERENCES `locations`(`loccode`),
  CONSTRAINT FOREIGN KEY(`orderno`) REFERENCES `salesorders`(`orderno`)
)");

CreateTable('pickreqdetails',
"CREATE TABLE pickreqdetails (
  detailno INT(11) NOT NULL AUTO_INCREMENT,
  prid INT(11) NOT NULL  DEFAULT '1',
  orderlineno INT(11) NOT NULL  DEFAULT '0',
  stockid VARCHAR(20) NOT NULL DEFAULT '',
  qtyexpected DOUBLE NOT NULL DEFAULT '0',
  qtypicked DOUBLE NOT NULL DEFAULT '0',
  invoicedqty DOUBLE NOT NULL DEFAULT '0',
  shipqty DOUBLE NOT NULL DEFAULT '0',
  PRIMARY KEY (`detailno`),
  KEY (`prid`),
  CONSTRAINT FOREIGN KEY(`stockid`) REFERENCES stockmaster(`stockid`),
  CONSTRAINT FOREIGN KEY(`prid`) REFERENCES pickreq(`prid`)
)");

CreateTable('pickserialdetails',
"CREATE TABLE pickserialdetails (
  serialmoveid INT(11) NOT NULL AUTO_INCREMENT,
  detailno INT(11) NOT NULL DEFAULT '1',
  stockid VARCHAR(20) NOT NULL DEFAULT '',
  serialno VARCHAR(30) NOT NULL DEFAULT '',
  moveqty DOUBLE NOT NULL DEFAULT '0',
  PRIMARY KEY (`serialmoveid`),
  KEY (`detailno`),
  KEY (`stockid`,`serialno`),
  KEY (`serialno`),
  CONSTRAINT FOREIGN KEY (`detailno`) REFERENCES pickreqdetails (`detailno`),
  CONSTRAINT FOREIGN KEY (`stockid`,`serialno`) REFERENCES `stockserialitems`(`stockid`,`serialno`)
)");

NewConfigValue('TermsAndConditions', '');

NewMenuItem('orders', 'Transactions', _('Generate/Print Picking Lists'), '/GeneratePickingList.php', 6);
NewMenuItem('orders', 'Transactions', _('Maintain Picking Lists'), '/SelectPickingLists.php', 7);
RemoveMenuItem('orders', 'Transactions', _('Print Picking Lists'), '/PDFPickingList.php');


UpdateDBNo(basename(__FILE__, '.php'));

?>