<?php
// Systems can temporarily force a reload by setting the variable
// $ForceConfigReload to true
if (isset($ForceConfigReload) and $ForceConfigReload == true or !isset($_SESSION['CompanyDefaultsLoaded']) or isset($_SESSION['FirstStart'])) {

	$SQL = "SELECT SQL_CACHE confname, confvalue FROM config";
	$ErrMsg = _('Could not get the configuration parameters from the database because');
	$ConfigResult = DB_query($SQL, $ErrMsg);
	while ($MyRow = DB_fetch_array($ConfigResult)) {
		if (is_numeric($MyRow['confvalue']) and $MyRow['confname'] != 'DefaultPriceList' and $MyRow['confname'] != 'VersionNumber') {
			//the variable name is given by $MyRow[0]
			$_SESSION[$MyRow['confname']] = (double)$MyRow['confvalue'];
		} else {
			$_SESSION[$MyRow['confname']] = $MyRow['confvalue'];
		}
	} //end loop through all config variables
	$_SESSION['CompanyDefaultsLoaded'] = true;
	//purge the audit trail if necessary
	if (isset($_SESSION['MonthsAuditTrail'])) {
		$SQL = "DELETE FROM audittrail
				WHERE  transactiondate <= '" . Date('Y-m-d', mktime(0, 0, 0, Date('m') - $_SESSION['MonthsAuditTrail'])) . "'";
		$ErrMsg = _('There was a problem deleting expired audit-trail history');
		$Result = DB_query($SQL);
	} //isset($_SESSION['MonthsAuditTrail'])
	DB_free_result($ConfigResult); // no longer needed
	/*Maybe we should check config directories exist and try to create if not */

	/*Load the pagesecurity settings from the database */
	$SQL = "SELECT SQL_CACHE script, pagesecurity FROM scripts";
	$Result = DB_query($SQL, '', '', false, false);
	if (DB_error_no() != 0) {
		/* the table may not exist with the pagesecurity field in it if it is an older KwaMoja database
		 * divert to the db upgrade if the VersionNumber is not in the config table
		 * */
		header('Location: Z_UpgradeDatabase.php');
	}
	//Populate the PageSecurityArray array for each script's  PageSecurity value
	while ($MyRow = DB_fetch_array($Result)) {
		$_SESSION['PageSecurityArray'][$MyRow['script']] = $MyRow['pagesecurity'];
	}

	if (!isset($_SESSION['DBUpdateNumber'])) { // the config record for VersionNumber is not yet added
		$_SESSION['DBUpdateNumber'] = - 1;
		header('Location: Z_UpgradeDatabase.php'); //divert to the db upgrade if the VersionNumber is not in the config table
		
	}

	if ($_SESSION['DBUpdateNumber'] > 143) {
		$_SESSION['ChartLanguage'] = GetChartLanguage();
		$_SESSION['InventoryLanguage'] = GetInventoryLanguage();
	}

	/* Also reads all the company data set up in the company record and returns an array */
	$SQL = "SELECT SQL_CACHE *
				FROM companies
				INNER JOIN currencies ON companies.currencydefault=currencies.currabrev
				WHERE coycode=1";

	$ErrMsg = _('An error occurred accessing the database to retrieve the company information');
	$ReadCoyResult = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($ReadCoyResult) == 0) {
		$PeriodsSQL = "SELECT SQL_CACHE periodno FROM periods";
		$PeriodResult = DB_query($PeriodsSQL);
		if (DB_num_rows($PeriodResult) == 0) {
			$_SESSION['DefaultDateFormat'] = 'd/m/Y';
			GetPeriod(DateAdd(date($_SESSION['DefaultDateFormat']), 'm', -12));
		}
	} else {
		$_SESSION['CompanyRecord'] = DB_fetch_array($ReadCoyResult);
	}

	$SQL = "SELECT SQL_CACHE id,
				host,
				port,
				heloaddress,
				username,
				password,
				timeout,
				auth,
				security
			FROM emailsettings";
	$Result = DB_query($SQL, '', '', false, false);
	if (DB_error_no() == 0) {
		/*test to ensure that the emailsettings table exists!!
		 * if it doesn't exist then we are into an UpgradeDatabase scenario anyway
		*/
		$MyRow = DB_fetch_array($Result);

		$_SESSION['SMTPSettings']['host'] = $MyRow['host'];
		$_SESSION['SMTPSettings']['port'] = $MyRow['port'];
		$_SESSION['SMTPSettings']['heloaddress'] = $MyRow['heloaddress'];
		$_SESSION['SMTPSettings']['username'] = $MyRow['username'];
		$_SESSION['SMTPSettings']['password'] = $MyRow['password'];
		$_SESSION['SMTPSettings']['timeout'] = $MyRow['timeout'];
		$_SESSION['SMTPSettings']['auth'] = $MyRow['auth'];
		$_SESSION['SMTPSettings']['security'] = $MyRow['security'];
	}
} //end if force reload or not set already


/*
These variable if required are in config.php

$_SESSION['DefaultLanguage'] = en_GB
$AllowDemoMode = 1

$EDIHeaderMsgId = D:01B:UN:EAN010
$EDIReference = KWAMOJA
$EDI_MsgPending = EDI_Pending
$EDI_MsgSent = EDI_Sent
$EDI_Incoming_Orders = EDI_Incoming_Orders

$RadioBeaconStockLocation = BL
$RadioBeaconHomeDir = /home/RadioBeacon
$RadioBeaconFileCounter = /home/RadioBeacon/FileCounter
$RadioBeaconFilePrefix = ORDXX
$RadioBeaconFTP_server = 192.168.2.2
$RadioBeaconFTP_user_name = RadioBeacon ftp server user name
$RadionBeaconFTP_user_pass = Radio Beacon remote ftp server password
*/
?>