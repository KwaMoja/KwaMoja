<?php

/* This function returns a list of the tax group id's
 * currently setup on KwaMoja
 */

function GetTaxGroupList($user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db) == 'integer') {
		$Errors[0] = NoAuthorisation;
		return $Errors;
	}
	$SQL = 'SELECT taxgroupid FROM taxgroups';
	$Result = api_DB_query($SQL);
	$i = 0;
	while ($MyRow = DB_fetch_array($Result)) {
		$TaxgroupList[$i] = $MyRow[0];
		++$i;
	}
	return $TaxgroupList;
}

/* This function takes as a parameter a tax group id
 * and returns an array containing the details of the selected
 * tax group.
 */

function GetTaxGroupDetails($taxgroup, $user, $password) {
	$Errors = array();
	$db = db($user, $password);
	if (gettype($db) == 'integer') {
		$Errors[0] = NoAuthorisation;
		return $Errors;
	}
	$SQL = "SELECT * FROM taxgroups WHERE taxgroupid='" . $taxgroup . "'";
	$Result = api_DB_query($SQL);
	return DB_fetch_array($Result);
}

/* This function takes as a parameter a tax group id
 * and returns an array containing the taxes in the selected
 * tax group.
 */

function GetTaxGroupTaxes($TaxGroup, $User, $Password) {
	$Errors = array();
	$db = db($User, $Password);
	if (gettype($db) == 'integer') {
		$Errors[0] = NoAuthorisation;
		return $Errors;
	}
	$SQL = "SELECT taxgroupid, taxauthid, calculationorder, taxontax FROM taxgrouptaxes WHERE taxgroupid='" . $TaxGroup . "'";
	$Result = api_DB_query($SQL);
	$i = 0;
	$Answer = array();
	while ($MyRow = DB_fetch_array($Result)) {
		$Answer[$i]['taxgroupid'] = $MyRow['taxgroupid'];
		$Answer[$i]['taxauthid'] = $MyRow['taxauthid'];
		$Answer[$i]['calculationorder'] = $MyRow['calculationorder'];
		$Answer[$i]['taxontax'] = $MyRow['taxontax'];
		++$i;
	}
	$Errors[0] = 0;
	$Errors[1] = $Answer;
	return $Errors;
}

/* This function returns a list of the tax authority ids
 * currently setup on KwaMoja
 */
function GetTaxAuthorityList($User, $Password) {
	$Errors = array();
	$db = db($User, $Password);
	if (gettype($db) == 'integer') {
		$Errors[0] = NoAuthorisation;
		return $Errors;
	}
	$SQL = 'SELECT taxid FROM taxauthorities';
	$Result = api_DB_query($SQL);
	$i = 0;
	while ($MyRow = DB_fetch_array($Result)) {
		$TaxAuthList[$i] = $MyRow[0];
		++$i;
	}
	return $TaxAuthList;
}

/* This function takes as a parameter a tax authority id
 * and returns an array containing the details of the selected
 * tax authority.
 */

function GetTaxAuthorityDetails($TaxAuthority, $User, $Password) {
	$Errors = array();
	$db = db($User, $Password);
	if (gettype($db) == 'integer') {
		$Errors[0] = NoAuthorisation;
		return $Errors;
	}
	$SQL = "SELECT * FROM taxauthorities WHERE taxid='" . $TaxAuthority . "'";
	$Result = api_DB_query($SQL);
	return DB_fetch_array($Result);
}

/* This function takes as a parameter a tax authority id and a tax category id
 * and returns an array containing the rate of tax for the selected
 * tax authority and tax category
 */

function GetTaxAuthorityRates($TaxAuthority, $User, $Password) {
	$Errors = array();
	$db = db($User, $Password);
	if (gettype($db) == 'integer') {
		$Errors[0] = NoAuthorisation;
		return $Errors;
	}
	$SQL = "SELECT taxcatid, dispatchtaxprovince, taxrate FROM taxauthrates WHERE taxauthority='" . $TaxAuthority . "'";
	$Result = api_DB_query($SQL);
	$i = 0;
	$Answer = array();
	while ($MyRow = DB_fetch_array($Result)) {
		$Answer[$i]['taxcatid'] = $MyRow['taxcatid'];
		$Answer[$i]['dispatchtaxprovince'] = $MyRow['dispatchtaxprovince'];
		$Answer[$i]['taxrate'] = $MyRow['taxrate'];
		++$i;
	}
	$Errors[0] = 0;
	$Errors[1] = $Answer;
	return $Errors;
}

?>