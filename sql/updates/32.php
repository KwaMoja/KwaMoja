<?php

CreateTable('mailgroups',
"CREATE TABLE `mailgroups` (
	id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	groupname varchar(100) NOT NULL,
	unique (groupname))",
$db);

CreateTable('mailgroupdetails',
"CREATE TABLE `mailgroupdetails` (
	groupname varchar(100) NOT NULL,
	userid varchar(20) NOT NULL,
	CONSTRAINT FOREIGN KEY (`groupname`) REFERENCES `mailgroups` (`groupname`),
	CONSTRAINT FOREIGN KEY (`userid`) REFERENCES `www_users`(`userid`),
	INDEX(`groupname`))",
$db);

NewScript('MailingGroupMaintenance.php',  '15', $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>