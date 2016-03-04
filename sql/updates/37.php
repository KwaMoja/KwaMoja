<?php

DropPrimaryKey('stockrequestitems', array('dispatchitemsid'));

AddPrimaryKey('stockrequestitems', array('dispatchitemsid', 'dispatchid'));

UpdateDBNo(basename(__FILE__, '.php'));

?>