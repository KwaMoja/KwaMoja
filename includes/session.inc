<?php

if (!isset($PathPrefix)) {
	$PathPrefix = '';
} //!isset($PathPrefix)
include($PathPrefix . 'Branding.php');

if (!file_exists($PathPrefix . 'config.php')) {
	$RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'));
	if ($RootPath == '/' or $RootPath == "\\") {
		$RootPath = '';
	} //$RootPath == '/' or $RootPath == "\\"
	header('Location:' . $RootPath . '/install/index.php?New=True');
	exit;
} //!file_exists($PathPrefix . 'config.php')
include($PathPrefix . 'config.php');

if (isset($host)) {
	/* It seems to be a webERP installation being upgraded
	 * so make sure variables are correctly named */
	$Host = $host;
	$DBPort = $mysqlport;
	$DefaultCompany = $DefaultDatabase;
}

if (isset($dbuser)) {
	$DBUser = $dbuser;
	$DBPassword = $dbpassword;
	$DBType = $dbType;
}

if (isset($SessionSavePath)) {
	session_save_path($SessionSavePath);
} //isset($SessionSavePath)

if (!isset($SysAdminEmail)) {
	$SysAdminEmail = '';
}

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

if (!isset($_SESSION['AttemptsCounter']) or $AllowDemoMode==true){
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
		} //gettype($Value) != 'array'
		else {
			foreach ($PostVariableValue as $PostArrayKey => $PostArrayValue) {
				if (get_magic_quotes_gpc()) {
					$PostVariableValue[$PostArrayKey] = stripslashes($Value[$PostArrayKey]);
				}
				$PostVariableValue[$PostArrayKey] = DB_escape_string($PostArrayValue);
			} //$Value as $Key1 => $Value1
		}
	} //$_POST as $Key => $Value

	/* iterate through all elements of the $_GET array and DB_escape_string them
	to limit possibility for SQL injection attacks and cross scripting attacks
	*/
	foreach ($_GET as $GetKey => $GetValue) {
		if (gettype($GetValue) != 'array' and basename($_SERVER['SCRIPT_NAME']) != 'index.php') {
			$_GET[$GetKey] = DB_escape_string(urldecode($GetValue));
		} //gettype($Value) != 'array'
	} //$_GET as $Key => $Value
} //isset($_SESSION['DatabaseName'])
else { //set SESSION['FormID'] before the a user has even logged in
	$_SESSION['FormID'] = sha1(uniqid(mt_rand(), true));
}

/* only do security checks if AllowAnyone is not true */
if (!isset($AllowAnyone) or !isset($_SESSION['CompanyDefaultsLoaded'])) {
	/* only do security checks if AllowAnyone is not true */

	include $PathPrefix . 'includes/UserLogin.php';
	/* Login checking and setup */

	if (isset($_POST['UserNameEntryField']) and isset($_POST['Password'])) {
		$rc = userLogin($_POST['UserNameEntryField'], $_POST['Password'], $SysAdminEmail);
	} //isset($_POST['UserNameEntryField']) and isset($_POST['Password'])
	elseif (empty($_SESSION['DatabaseName'])) {
		$rc = UL_SHOWLOGIN;
	} //empty($_SESSION['DatabaseName'])
	else {
		$rc = UL_OK;
	}

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
} else if (basename($_SERVER['SCRIPT_NAME']) == 'Logout.php') {
	header('Location: index.php');
}

if (isset($_SESSION['LastActivity']) and (time() - $_SESSION['LastActivity']) > $SessionLifeTime) {
	if (basename($_SERVER['SCRIPT_NAME']) != 'Logout.php') {
		header('Location: Logout.php');
	}
} else {
	$_SESSION['LastActivity'] = time();
}

if (!isset($_SESSION['CompanyDefaultsLoaded'])) {
	include($PathPrefix . 'includes/GetConfig.php');
	if (!isset($_SESSION['DBUpdateNumber'])) {
		$_SESSION['DBUpdateNumber'] = 0;
	}
	/*If the highest of the DB update files is greater than the DBUpdateNumber held in config table then do upgrades */
	$_SESSION['DBVersion'] = HighestFileName($PathPrefix);
	if (($_SESSION['DBVersion'] > $_SESSION['DBUpdateNumber']) and (basename($_SERVER['SCRIPT_NAME']) != 'Z_UpgradeDatabase.php')) {
		header('Location: Z_UpgradeDatabase.php');
	} else {
		unset($_SESSION['DBVersion']);
	}

	/*Check to see if currency rates need to be updated */
	if (isset($_SESSION['UpdateCurrencyRatesDaily'])) {
		if ($_SESSION['UpdateCurrencyRatesDaily'] != 0) {
			if (DateDiff(Date($_SESSION['DefaultDateFormat']), ConvertSQLDate($_SESSION['UpdateCurrencyRatesDaily']), 'd') > 0) {
				if ($_SESSION['ExchangeRateFeed'] == 'ECB') {
					$CurrencyRates = GetECBCurrencyRates(); // gets rates from ECB see includes/MiscFunctions.php

					/*Loop around the defined currencies and get the rate from ECB */
					if ($CurrencyRates != false) {
						$CurrenciesResult = DB_query("SELECT currabrev FROM currencies");
						while ($CurrencyRow = DB_fetch_row($CurrenciesResult)) {
							if ($CurrencyRow[0] != $_SESSION['CompanyRecord']['currencydefault']) {
								$UpdateCurrRateResult = DB_query("UPDATE currencies SET rate='" . GetCurrencyRate($CurrencyRow[0], $CurrencyRates) . "'
																WHERE currabrev='" . $CurrencyRow[0] . "'");
							} //$CurrencyRow[0] != $_SESSION['CompanyRecord']['currencydefault']
						} //$CurrencyRow = DB_fetch_row($CurrenciesResult)
					} //$CurrencyRates != false
				} else {
					$CurrenciesResult = DB_query("SELECT currabrev FROM currencies");
					while ($CurrencyRow = DB_fetch_row($CurrenciesResult)) {
						if ($CurrencyRow[0] != $_SESSION['CompanyRecord']['currencydefault']) {
							$UpdateCurrRateResult = DB_query("UPDATE currencies SET rate='" . google_currency_rate($CurrencyRow[0]) . "'
															WHERE currabrev='" . $CurrencyRow[0] . "'");
						} //$CurrencyRow[0] != $_SESSION['CompanyRecord']['currencydefault']
					} //$CurrencyRow = DB_fetch_row($CurrenciesResult)
				}
				$_SESSION['UpdateCurrencyRatesDaily'] = Date('Y-m-d');
				$UpdateConfigResult = DB_query("UPDATE config SET confvalue = CURRENT_DATE WHERE confname='UpdateCurrencyRatesDaily'");
			} //DateDiff(Date($_SESSION['DefaultDateFormat']), ConvertSQLDate($_SESSION['UpdateCurrencyRatesDaily']), 'd') > 0
		} //$_SESSION['UpdateCurrencyRatesDaily'] != 0
	} //isset($_SESSION['UpdateCurrencyRatesDaily'])
}

if (isset($_POST['Theme']) and ($_SESSION['UsersRealName'] == $_POST['RealName'])) {
	$_SESSION['Theme'] = $_POST['Theme'];
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
		$LogoFile = null;
		$dir = $PathPrefix . 'companies/' . $CompanyDir;
		if (file_exists($dir . '/logo.png')) {
			$LogoFile = 'companies/' . $CompanyDir . '/logo.png';
		} elseif (file_exists($dir . '/logo.jpg')) {
			$LogoFile = 'companies/' . $CompanyDir . '/logo.jpg';
		}
		return $LogoFile;
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
	if (PHP_VERSION_ID < 50500) {
		$Salt = base64_encode(mcrypt_create_iv(22, MCRYPT_DEV_URANDOM));
		$Salt = str_replace('+', '.', $Salt);
		$Hash = crypt($Password, '$2y$10$' . $Salt . '$');
	} else {
		$Hash = password_hash($Password, PASSWORD_DEFAULT);
	}
	return $Hash;
}

function VerifyPass($Password, $Hash) {
	if(PHP_VERSION_ID < 50500) {
		return (crypt($Password, $Hash) == $Hash);
	} else {
		return password_verify($Password, $Hash);
	}
}

function HighestFileName($PathPrefix) {
	$files = glob('sql/updates/*.php');
	natsort($files);
	return basename(array_pop($files), ".php");
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