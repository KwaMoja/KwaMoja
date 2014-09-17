<?php

/* This function returns a list of the stock shipper id's
 * currently setup on KwaMoja
 */

function GetShipperList($User, $Password) {
	$Errors = array();
	$db = db($User, $Password);
	if (gettype($db) == 'integer') {
		$Errors[0] = NoAuthorisation;
		return $Errors;
	}
	$SQL = 'SELECT shipper_id FROM shippers';
	$result = api_DB_query($SQL);
	$i = 0;
	while ($MyRow = DB_fetch_array($result)) {
		$ShipperList[$i] = $MyRow[0];
		++$i;
	}
	return $ShipperList;
}

/* This function takes as a parameter a shipper id
 * and returns an array containing the details of the selected
 * shipper.
 */

function GetShipperDetails($Shipper, $User, $Password) {
	$Errors = array();
	$db = db($User, $Password);
	if (gettype($db) == 'integer') {
		$Errors[0] = NoAuthorisation;
		return $Errors;
	}
	$SQL = "SELECT * FROM shippers WHERE shipper_id='" . $Shipper . "'";
	$result = api_DB_query($SQL);
	return DB_fetch_array($result);
}
?>