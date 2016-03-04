<?php

/* This function returns a list of the currency abbreviations
 * currently setup on KwaMoja
 */

function GetCurrencyList($user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db) == 'integer') {
		$Errors[0] = NoAuthorisation;
		return $Errors;
	}
	$SQL = 'SELECT currabrev FROM currencies';
	$Result = api_DB_query($SQL);
	$i = 0;
	while ($MyRow = DB_fetch_array($Result)) {
		$CurrencyList[$i] = $MyRow[0];
		++$i;
	}
	return $CurrencyList;
}

/* This function takes as a parameter a currency abbreviation
 * and returns an array containing the details of the selected
 * currency.
 */

function GetCurrencyDetails($currency, $user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db) == 'integer') {
		$Errors[0] = NoAuthorisation;
		return $Errors;
	}
	$SQL = "SELECT * FROM currencies WHERE currabrev='" . $currency . "'";
	$Result = api_DB_query($SQL);
	return DB_fetch_array($Result);
}

?>