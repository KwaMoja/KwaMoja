<?php

ChangeColumnType('groupname', 'accountgroups', 'VARCHAR(150)', 'NOT NULL', '');
ChangeColumnType('group_', 'chartmaster', 'VARCHAR(150)', 'NOT NULL', '');
ChangeColumnType('accountname', 'chartmaster', 'VARCHAR(150)', 'NOT NULL', '');
ChangeColumnType('parentgroupname', 'accountgroups', 'VARCHAR(150)', 'NOT NULL', '');

ChangeColumnType('groupcode', 'accountgroups', 'CHAR(10)', 'NOT NULL', '');
ChangeColumnType('groupcode', 'chartmaster', 'CHAR(10)', 'NOT NULL', '');
ChangeColumnType('parentgroupcode', 'accountgroups', 'CHAR(10)', 'NOT NULL', '');

DropPrimaryKey('accountgroups', array('groupname'));
AddPrimaryKey('accountgroups', array('groupcode'));

DropIndex('chartmaster', 'Group_');
AddIndex(array('groupcode'),'chartmaster', 'Group');

UpdateDBNo(basename(__FILE__, '.php'));

?>