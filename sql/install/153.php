<?php

CreateTable('www_users',
"CREATE TABLE `www_users` (
  `userid` varchar(20) NOT NULL DEFAULT '',
  `password` text NOT NULL,
  `realname` varchar(35) NOT NULL DEFAULT '',
  `customerid` varchar(10) NOT NULL DEFAULT '',
  `supplierid` varchar(10) NOT NULL DEFAULT '',
  `salesman` char(3) NOT NULL,
  `phone` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(55) DEFAULT NULL,
  `defaultlocation` varchar(5) NOT NULL DEFAULT '',
  `restrictlocations` tinyint(1) NOT NULL DEFAULT '1',
  `fullaccess` int(11) NOT NULL DEFAULT '1',
  `cancreatetender` tinyint(1) NOT NULL DEFAULT '0',
  `lastvisitdate` datetime DEFAULT NULL,
  `branchcode` varchar(10) NOT NULL DEFAULT '',
  `pagesize` varchar(20) NOT NULL DEFAULT 'A4',
  `modulesallowed` varchar(40) NOT NULL DEFAULT '',
  `blocked` tinyint(4) NOT NULL DEFAULT '0',
  `displayrecordsmax` int(11) NOT NULL DEFAULT '0',
  `theme` varchar(30) NOT NULL DEFAULT 'fresh',
  `language` varchar(10) NOT NULL DEFAULT 'en_GB.utf8',
  `pdflanguage` tinyint(1) NOT NULL DEFAULT '0',
  `department` int(11) NOT NULL DEFAULT '0',
  `fontsize` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`userid`),
  KEY `CustomerID` (`customerid`),
  KEY `DefaultLocation` (`defaultlocation`),
  CONSTRAINT `www_users_ibfk_1` FOREIGN KEY (`defaultlocation`) REFERENCES `locations` (`loccode`)
)");

$SQL = "INSERT INTO securitygroups SELECT secroleid, tokenid FROM securityroles, securitytokens";
executeSQL($SQL);

if (is_writable($PathPrefix . 'companies/' . $_SESSION['DatabaseName'])) {
	$FileHandle = fopen($PathPrefix . 'companies/' . $_SESSION['DatabaseName'] . '/Companies.php', 'w');

	fwrite($FileHandle, '<?php' . "\n");

	fwrite($FileHandle, '$CompanyName[\'' . $_SESSION['DatabaseName'] . '\'] = \'' . stripslashes($_SESSION['CompanyRecord']['coyname']) . '\';' . "\n");

	fwrite($FileHandle, '?>');

	fclose($FileHandle);
	$_SESSION['Updates']['Successes']++;
} else {
	prnMsg( _('The directory') . ' ' . $PathPrefix . 'companies/' . $_SESSION['DatabaseName'] . ' ' . _('must be writable by the web server'), 'error');
	include($PathPrefix . 'includes/footer.inc');
	$_SESSION['Updates']['Errors']++;
	exit;
}

?>