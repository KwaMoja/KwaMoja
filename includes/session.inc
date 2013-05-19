<?php
/* $Id$*/

if (!isset($PathPrefix)) {
	$PathPrefix = '';
} //!isset($PathPrefix)

if (!file_exists($PathPrefix . 'config.php')) {
	$RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'));
	if ($RootPath == '/' or $RootPath == "\\") {
		$RootPath = '';
	} //$RootPath == '/' or $RootPath == "\\"
	header('Location:' . $RootPath . '/install/index.php');
	exit;
} //!file_exists($PathPrefix . 'config.php')
include($PathPrefix . 'config.php');
$DBVersion=HighestFileName($PathPrefix);
if (isset($dbuser)) {
	$DBUser=$dbuser;
	$DBPassword=$dbpassword;
	$DBType=$dbType;
}

if (isset($SessionSavePath)) {
	session_save_path($SessionSavePath);
} //isset($SessionSavePath)

if (!isset($SysAdminEmail)) {
	$SysAdminEmail='';
}

ini_set('session.gc_maxlifetime', $SessionLifeTime);

if (!ini_get('safe_mode')) {
	set_time_limit($MaximumExecutionTime);
	ini_set('max_execution_time', $MaximumExecutionTime);
} //!ini_get('safe_mode')
session_write_close(); //in case a previous session is not closed
session_start();

include($PathPrefix . 'includes/ConnectDB.inc');
include($PathPrefix . 'includes/DateFunctions.inc');

// Uncomment to turn off attempts counter
//$_SESSION['AttemptsCounter'] = 0;

if (!isset($_SESSION['AttemptsCounter'])) {
	$_SESSION['AttemptsCounter'] = 0;
} //!isset($_SESSION['AttemptsCounter'])

/* iterate through all elements of the $_POST array and DB_escape_string them
to limit possibility for SQL injection attacks and cross scripting attacks
*/

if (isset($_SESSION['DatabaseName'])) {
	foreach ($_POST as $PostVariableName => $PostVariableValue) {
		if (gettype($PostVariableValue) != 'array') {
			if (get_magic_quotes_gpc()) {
				$_POST['name'] = stripslashes($_POST['name']);
			} //get_magic_quotes_gpc()
			$_POST[$PostVariableName] = DB_escape_string($PostVariableValue);
		} //gettype($value) != 'array'
		else {
			foreach ($PostVariableValue as $PostArrayKey => $PostArrayValue) {
				if (get_magic_quotes_gpc()) {
					$PostVariableValue[$PostArrayKey] = stripslashes($value[$PostArrayKey]);
				}
				$PostVariableValue[$PostArrayKey] = DB_escape_string($PostArrayValue);
			} //$value as $key1 => $value1
		}
	} //$_POST as $key => $value

	/* iterate through all elements of the $_GET array and DB_escape_string them
	to limit possibility for SQL injection attacks and cross scripting attacks
	*/
	foreach ($_GET as $GetKey => $GetValue) {
		if (gettype($GetValue) != 'array') {
			$_GET[$GetKey] = DB_escape_string($GetValue);
		} //gettype($value) != 'array'
	} //$_GET as $key => $value
} //isset($_SESSION['DatabaseName'])
else { //set SESSION['FormID'] before the a user has even logged in
	$_SESSION['FormID'] = sha1(uniqid(mt_rand(), true));
}

include($PathPrefix . 'includes/LanguageSetup.php');

/* only do security checks if AllowAnyone is not true */
if (!isset($AllowAnyone)) {
	/* only do security checks if AllowAnyone is not true */

	include $PathPrefix . 'includes/UserLogin.php';
	/* Login checking and setup */

	if (isset($_POST['UserNameEntryField']) and isset($_POST['Password'])) {
		$rc = userLogin($_POST['UserNameEntryField'], $_POST['Password'], $SysAdminEmail, $db);
	} //isset($_POST['UserNameEntryField']) and isset($_POST['Password'])
	elseif (empty($_SESSION['DatabaseName'])) {
		$rc = UL_SHOWLOGIN;
	} //empty($_SESSION['DatabaseName'])
	else {
		$rc = UL_OK;
	}

	/*  Need to set the theme to make login screen nice */
	$Theme = (isset($_SESSION['Theme'])) ? $_SESSION['Theme'] : 'gel';
	switch ($rc) {
		case UL_OK;
			include($PathPrefix . 'includes/LanguageSetup.php');
			break;

		case UL_SHOWLOGIN:
			include($PathPrefix . 'includes/Login.php');
			exit;

		case UL_BLOCKED:
			die(include($PathPrefix . 'includes/FailedLogin.php'));

		case UL_CONFIGERR:
			$Title = _('Account Error Report');
			include($PathPrefix . 'includes/header.inc');
			echo '<br /><br /><br />';
			prnMsg(_('Your user role does not have any access defined for KwaMoja. There is an error in the security setup for this user account'), 'error');
			include($PathPrefix . 'includes/footer.inc');
			exit;

		case UL_NOTVALID:
			$demo_text = '<font size="3" color="red"><b>' . _('incorrect password') . '</b></font><br /><b>' . _('The user/password combination') . '<br />' . _('is not a valid user of the system') . '</b>';
			die(include($PathPrefix . 'includes/Login.php'));

		case UL_MAINTENANCE:
			$demo_text = '<font size="3" color="red"><b>' . _('system maintenance') . '</b></font><br /><b>' . _('KwaMoja is not available right now') . '<br />' . _('during maintenance of the system') . '</b>';
			die(include($PathPrefix . 'includes/Login.php'));

	} //$rc
} //!isset($AllowAnyone)
else if (basename($_SERVER['SCRIPT_NAME']) == 'Logout.php'){
	header('Location: index.php');
}

/*User is logged in so get configuration parameters  - save in session*/
include($PathPrefix . 'includes/GetConfig.php');

/*If the highest of the DB update files is greater than the DBUpdateNumber held in config table then do upgrades */
if (($DBVersion>$_SESSION['DBUpdateNumber']) and (basename($_SERVER['SCRIPT_NAME'])!='Z_UpgradeDatabase.php')) {
	header('Location: Z_UpgradeDatabase.php');
}

if (isset($_SESSION['DB_Maintenance'])) {
	if ($_SESSION['DB_Maintenance'] != 0) {
		if (DateDiff(Date($_SESSION['DefaultDateFormat']), ConvertSQLDate($_SESSION['DB_Maintenance_LastRun']), 'd') >= $_SESSION['DB_Maintenance']) {
			/*Do the DB maintenance routing for the DB_type selected */
			DB_Maintenance($db);
			$_SESSION['DB_Maintenance_LastRun'] = Date('Y-m-d');
		} //DateDiff(Date($_SESSION['DefaultDateFormat']), ConvertSQLDate($_SESSION['DB_Maintenance_LastRun']), 'd') >= $_SESSION['DB_Maintenance']
	} //$_SESSION['DB_Maintenance'] != 0
} //isset($_SESSION['DB_Maintenance'])

//purge the audit trail if necessary
if (isset($_SESSION['MonthsAuditTrail'])) {
	$sql = "DELETE FROM audittrail
			WHERE  transactiondate <= '" . Date('Y-m-d', mktime(0, 0, 0, Date('m') - $_SESSION['MonthsAuditTrail'])) . "'";
	$ErrMsg = _('There was a problem deleting expired audit-trail history');
	$result = DB_query($sql, $db);
} //isset($_SESSION['MonthsAuditTrail'])

/*Check to see if currency rates need to be updated */
if (isset($_SESSION['UpdateCurrencyRatesDaily'])) {
	if ($_SESSION['UpdateCurrencyRatesDaily'] != 0) {
		if (DateDiff(Date($_SESSION['DefaultDateFormat']), ConvertSQLDate($_SESSION['UpdateCurrencyRatesDaily']), 'd') > 0) {
			if ($_SESSION['ExchangeRateFeed'] == 'ECB') {
				$CurrencyRates = GetECBCurrencyRates(); // gets rates from ECB see includes/MiscFunctions.php

				/*Loop around the defined currencies and get the rate from ECB */
				if ($CurrencyRates != false) {
					$CurrenciesResult = DB_query("SELECT currabrev FROM currencies", $db);
					while ($CurrencyRow = DB_fetch_row($CurrenciesResult)) {
						if ($CurrencyRow[0] != $_SESSION['CompanyRecord']['currencydefault']) {
							$UpdateCurrRateResult = DB_query("UPDATE currencies SET rate='" . GetCurrencyRate($CurrencyRow[0], $CurrencyRates) . "'
																WHERE currabrev='" . $CurrencyRow[0] . "'", $db);
						} //$CurrencyRow[0] != $_SESSION['CompanyRecord']['currencydefault']
					} //$CurrencyRow = DB_fetch_row($CurrenciesResult)
				} //$CurrencyRates != false
			} //$_SESSION['ExchangeRateFeed'] == 'ECB'
			else {
				$CurrenciesResult = DB_query("SELECT currabrev FROM currencies", $db);
				while ($CurrencyRow = DB_fetch_row($CurrenciesResult)) {
					if ($CurrencyRow[0] != $_SESSION['CompanyRecord']['currencydefault']) {
						$UpdateCurrRateResult = DB_query("UPDATE currencies SET rate='" . google_currency_rate($CurrencyRow[0]) . "'
															WHERE currabrev='" . $CurrencyRow[0] . "'", $db);
					} //$CurrencyRow[0] != $_SESSION['CompanyRecord']['currencydefault']
				} //$CurrencyRow = DB_fetch_row($CurrenciesResult)
			}
			$_SESSION['UpdateCurrencyRatesDaily'] = Date('Y-m-d');
			$UpdateConfigResult = DB_query("UPDATE config SET confvalue = '" . Date('Y-m-d') . "' WHERE confname='UpdateCurrencyRatesDaily'", $db);
		} //DateDiff(Date($_SESSION['DefaultDateFormat']), ConvertSQLDate($_SESSION['UpdateCurrencyRatesDaily']), 'd') > 0
	} //$_SESSION['UpdateCurrencyRatesDaily'] != 0
} //isset($_SESSION['UpdateCurrencyRatesDaily'])


if (isset($_POST['Theme']) and ($_SESSION['UsersRealName'] == $_POST['RealName'])) {
	$_SESSION['Theme'] = $_POST['Theme'];
	$Theme = $_POST['Theme'];
} //isset($_POST['Theme']) and ($_SESSION['UsersRealName'] == $_POST['RealName'])
elseif (!isset($_SESSION['Theme'])) {
	$Theme = $_SESSION['DefaultTheme'];
	$_SESSION['Theme'] = $_SESSION['DefaultTheme'];

} //!isset($_SESSION['Theme'])
else {
	$Theme = $_SESSION['Theme'];
}

/* Set the logo if not yet set.
 * will be done only once per session and each time
 * we are not in session (i.e. before login)
 */
if (empty($_SESSION['LogoFile'])) {
	/* find a logo in companies/$CompanyDir
	 * (nice side effect of function:
	 * variables are local, so we will never
	 * cause name clashes)
	 */

	function findLogoFile($CompanyDir, $PathPrefix) {
		$dir = $PathPrefix . 'companies/' . $CompanyDir . '/';
		$DirHandle = dir($dir);
		while ($DirEntry = $DirHandle->read()) {
			if ($DirEntry != '.' and $DirEntry != '..') {
				$InCompanyDir[] = $DirEntry; //make an array of all files under company directory
			} //$DirEntry != '.' and $DirEntry != '..'
		} //loop through list of files in the company directory
		if ($InCompanyDir !== FALSE) {
			foreach ($InCompanyDir as $logofilename) {
				if (strncasecmp($logofilename, 'logo.png', 8) === 0 and is_readable($dir . $logofilename) and is_file($dir . $logofilename)) {
					$logo = $logofilename;
					break;
				} //strncasecmp($logofilename, 'logo.png', 8) === 0 and is_readable($dir . $logofilename) and is_file($dir . $logofilename)
			} //$InCompanyDir as $logofilename
			if (!isset($logo)) {
				foreach ($InCompanyDir as $logofilename) {
					if (strncasecmp($logofilename, 'logo.jpg', 8) === 0 and is_readable($dir . $logofilename) and is_file($dir . $logofilename)) {
						$logo = $logofilename;
						break;
					} //strncasecmp($logofilename, 'logo.jpg', 8) === 0 and is_readable($dir . $logofilename) and is_file($dir . $logofilename)
				} //$InCompanyDir as $logofilename
			} //!isset($logo)
			if (empty($logo)) {
				return null;
			} //empty($logo)
			else {
				return 'companies/' . $CompanyDir . '/' . $logo;
			}
		} //end listing of files under company directory is not empty
	}

	/* Find a logo in companies/<company of this session> */
	if (!empty($_SESSION['DatabaseName'])) {
		$_SESSION['LogoFile'] = findLogoFile($_SESSION['DatabaseName'], $PathPrefix);
	} //!empty($_SESSION['DatabaseName'])
} //empty($_SESSION['LogoFile'])

if ($_SESSION['HTTPS_Only'] == 1) {
	if ($_SERVER['HTTPS'] != 'on') {
		prnMsg(_('KwaMoja is configured to allow only secure socket connections. Pages must be called with https://') . ' .....', 'error');
		exit;
	} //$_SERVER['HTTPS'] != 'on'
} //$_SESSION['HTTPS_Only'] == 1

// Now check that the user as logged in has access to the page being called. $SecurityGroups is an array of
// arrays defining access for each group of users. These definitions can be modified by a system admin under setup


if (!is_array($_SESSION['AllowedPageSecurityTokens']) and !isset($AllowAnyone)) {
	$Title = _('Account Error Report');
	include($PathPrefix . 'includes/header.inc');
	echo '<br /><br /><br />';
	prnMsg(_('Security settings have not been defined for your user account. Please advise your system administrator. It could also be that there is a session problem with your PHP web server'), 'error');
	include($PathPrefix . 'includes/footer.inc');
	exit;
} //!is_array($_SESSION['AllowedPageSecurityTokens']) and !isset($AllowAnyone)

/*The page security variable is now retrieved from the database in GetConfig.php and stored in the $SESSION['PageSecurityArray'] array
 * the key for the array is the script name - the script name is retrieved from the basename ($_SERVER['SCRIPT_NAME'])
 */
if (!isset($PageSecurity)) {
	//only hardcoded in the UpgradeDatabase script - so old versions that don't have the scripts.pagesecurity field do not choke
	$PageSecurity = $_SESSION['PageSecurityArray'][basename($_SERVER['SCRIPT_NAME'])];
} //!isset($PageSecurity)


if (!isset($AllowAnyone)) {
	if ((!in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens']) or !isset($PageSecurity))) {
		$Title = _('Security Permissions Problem');
		include($PathPrefix . 'includes/header.inc');
		echo '<tr>
			<td class="menu_group_items">
				<table width="100%" class="table_index">
					<tr><td class="menu_group_item">';
		echo '<b><font style="size:+1; text-align:center;">' . _('The security settings on your account do not permit you to access this function') . '</font></b>';

		echo '</td>
			</tr>
			</table>
			</td>
			</tr>';

		include($PathPrefix . 'includes/footer.inc');
		exit;
	} //(!in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens']) or !isset($PageSecurity))
} //!isset($AllowAnyone)

if (in_array(9, $_SESSION['AllowedPageSecurityTokens']) and count($_SESSION['AllowedPageSecurityTokens']) == 2) {
	$SupplierLogin = 1;
} //in_array(9, $_SESSION['AllowedPageSecurityTokens']) and $PageSecurity == 0 and count($_SESSION['AllowedPageSecurityTokens']) == 2
else if (in_array(1, $_SESSION['AllowedPageSecurityTokens']) and count($_SESSION['AllowedPageSecurityTokens']) == 2) {
	$SupplierLogin = 0;
} //in_array(1, $_SESSION['AllowedPageSecurityTokens']) and $PageSecurity == 0 and count($_SESSION['AllowedPageSecurityTokens']) == 2

function CryptPass($Password) {
	global $CryptFunction;
	if ($CryptFunction == 'sha1') {
		return sha1($Password);
	} //$CryptFunction == 'sha1'
	elseif ($CryptFunction == 'md5') {
		return md5($Password);
	} //$CryptFunction == 'md5'
	else {
		return $Password;
	}
}

function HighestFileName($PathPrefix) {
	if($handle=opendir($PathPrefix . 'sql/updates')){

		$i=0;

		// Go through directory:
		while(false!==($file=readdir($handle))){
			// filter unnecessary file/dir paths:
			if(substr($file,-3)=='php'){

				$FileNameLength=strlen($file)-4;
				$UpdateNames[$i]=substr($file,0,$FileNameLength);

				$i++;
			}
		}
		closedir($handle);
	}
	return max($UpdateNames);
}

if (sizeof($_POST) > 0 and !isset($AllowAnyone)) {
	/*Security check to ensure that the form submitted is originally sourced from KwaMoja with the FormID = $_SESSION['FormID'] - which is set before the first login*/
	if (!isset($_POST['FormID']) or ($_POST['FormID'] != $_SESSION['FormID'])) {
		$Title = _('Error in form verification');
		include('includes/header.inc');
		prnMsg(_('This form was not submitted with a correct ID'), 'error');
		include('includes/footer.inc');
		exit;
	} //!isset($_POST['FormID']) or ($_POST['FormID'] != $_SESSION['FormID'])
} //sizeof($_POST) > 0 and !isset($AllowAnyone)

?>
