<?php

DropPrimaryKey('stockrequestitems', array('dispatchitemsid'), $db);

AddPrimaryKey('stockrequestitems', array('dispatchitemsid', 'dispatchid'), $db);

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>