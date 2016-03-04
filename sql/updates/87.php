<?php


InsertRecord('systypes', array('typeid'), array('600'), array('typeid' ,'typename' ,'typeno'), array('600',  _('Auto Supplier Number'),  '0'));

NewConfigValue('AutoSupplierNo', '0');

UpdateDBNo(basename(__FILE__, '.php'));

?>