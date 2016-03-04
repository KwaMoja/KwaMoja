<?php

/*  Performs login checks and $_SESSION initialisation */

define('UL_OK', 0);
/* User verified, session initialised */
define('UL_NOTVALID', 1);
/* User/password do not agree */
define('UL_BLOCKED', 2);
/* Account locked, too many failed logins */
define('UL_CONFIGERR', 3);
/* Configuration error in KwaMoja or server */
define('UL_SHOWLOGIN', 4);
define('UL_MAINTENANCE', 5);

/*	UserLogin
 *  Function to validate user name,  perform validity checks and initialise
 *  $_SESSION data.
 *  Returns:
 *	See define() statements above.
 */

function userLogin($Name, $Password, $SysAdminEmail = '') {

	global $Debug;
	setcookie('Login', $_SESSION['DatabaseName'], time() + 3600 * 24 * 30);
	if (isset($_COOKIE['Module'])) {
		$_GET['Application'] = $_COOKIE['Module'];
	}
	$_SESSION['LastActivity'] = time();
	if (!isset($_SESSION['AccessLevel']) or $_SESSION['AccessLevel'] == '' or (isset($Name) and $Name != '')) {
		/* if not logged in */
		$_SESSION['AccessLevel'] = '';
		$_SESSION['CustomerID'] = '';
		$_SESSION['UserBranch'] = '';
		$_SESSION['SalesmanLogin'] = '';
		$_SESSION['Module'] = '';
		$_SESSION['PageSize'] = '';
		$_SESSION['UserStockLocation'] = '';
		$_SESSION['AttemptsCounter']++;
		// Show login screen
		if (!isset($Name) or $Name == '') {
			return UL_SHOWLOGIN;
		}
		$SQL = "SELECT *
				FROM www_users
				WHERE www_users.userid='" . $Name . "'";
		$ErrMsg = _('Could not retrieve user details on login because');
		$PasswordVerified = false;
		$AuthResult = DB_query($SQL, $ErrMsg);

		if (DB_num_rows($AuthResult) > 0) {
			$MyRow = DB_fetch_array($AuthResult);
			if (VerifyPass($Password, $MyRow['password'])) {
				$PasswordVerified = true;
		    } elseif (isset($GLOBALS['CryptFunction'])) {
				/*if the password stored in the DB was compiled the old way,
				 * the previous comparison will fail,
				 * try again with the old hashing algorithm,
				 * then re-hash the password using the new algorithm.
				 * The next version should not have $CryptFunction anymore for new installs.
				 */
				switch ($GLOBALS['CryptFunction']) {
					case 'sha1':
						if ($MyRow['password'] == sha1($Password)) {
							$PasswordVerified = true;
						}
						break;
					case 'md5':
						if ($MyRow['password'] == md5($Password)) {
							$PasswordVerified = true;
						}
						break;
					default:
						if ($MyRow['password'] == $Password) {
							$PasswordVerified = true;
						}
				}
				if ($PasswordVerified) {
					$SQL = "UPDATE www_users SET password = '" . CryptPass($Password) . "'"
							. " WHERE userid = '" . $Name . "'";
					DB_query($SQL);
				}

		    }
		}

		// Populate session variables with data base results
		if ($PasswordVerified) {
			if ($MyRow['blocked'] == 1) {
				//the account is blocked
				return UL_BLOCKED;
			}
			/*reset the attempts counter on successful login */
			$_SESSION['UserID'] = $MyRow['userid'];
			$_SESSION['AttemptsCounter'] = 0;
			$_SESSION['AccessLevel'] = $MyRow['fullaccess'];
			$_SESSION['CustomerID'] = $MyRow['customerid'];
			$_SESSION['UserBranch'] = $MyRow['branchcode'];
			$_SESSION['DefaultPageSize'] = $MyRow['pagesize'];
			$_SESSION['UserStockLocation'] = $MyRow['defaultlocation'];
			if (isset($MyRow['restrictlocations'])) {
				$_SESSION['RestrictLocations'] = $MyRow['restrictlocations'];
			} else {
				$_SESSION['RestrictLocations'] = 1;
			}
			$_SESSION['UserEmail'] = $MyRow['email'];
			$_SESSION['ModulesEnabled'] = explode(",", $MyRow['modulesallowed']);
			$_SESSION['UsersRealName'] = $MyRow['realname'];
			$_SESSION['Theme'] = $MyRow['theme'];
			require_once 'MobileDetect.php';
			$MobileDetect = new Mobile_Detect;
			if ($MobileDetect->isMobile()) {
				$_SESSION['Theme'] = 'mobile';
			}
			$_SESSION['Language'] = $MyRow['language'];
			$_SESSION['SalesmanLogin'] = $MyRow['salesman'];
			$_SESSION['CanCreateTender'] = $MyRow['cancreatetender'];
			$_SESSION['AllowedDepartment'] = $MyRow['department'];
			if (isset($MyRow['fontsize'])) {
				switch ($MyRow['fontsize']) {
					case 0:
						$_SESSION['ScreenFontSize'] = '8pt';
						break;
					case 1:
						$_SESSION['ScreenFontSize'] = '10pt';
						break;
					case 2:
						$_SESSION['ScreenFontSize'] = '12pt';
						break;
					default:
						$_SESSION['ScreenFontSize'] = '10pt';
				}
			} else {
				$_SESSION['ScreenFontSize'] = 0;
			}
			if (isset($MyRow['defaulttag'])) {
				$_SESSION['DefaultTag'] = $MyRow['defaulttag'];
			} else {
				$_SESSION['DefaultTag'] = '8pt';
			}

			if (isset($MyRow['pdflanguage'])) {
				$_SESSION['PDFLanguage'] = $MyRow['pdflanguage'];
			} else {
				$_SESSION['PDFLanguage'] = '0'; //default to latin western languages
			}

			if ($MyRow['displayrecordsmax'] > 0) {
				$_SESSION['DisplayRecordsMax'] = $MyRow['displayrecordsmax'];
			} else {
				$_SESSION['DisplayRecordsMax'] = $_SESSION['DefaultDisplayRecordsMax']; // default comes from config.php
			}

			$SQL = "UPDATE www_users SET lastvisitdate='" . date('Y-m-d H:i:s') . "'
							WHERE www_users.userid='" . $Name . "'";
			$AuthResult = DB_query($SQL);
			/*get the security tokens that the user has access to */
			$SQL = "SELECT tokenid
						FROM securitygroups
						WHERE secroleid =  '" . $_SESSION['AccessLevel'] . "'";
			$SecResult = DB_query($SQL);
			$_SESSION['AllowedPageSecurityTokens'] = array();
			if (DB_num_rows($SecResult) == 0) {
				return UL_CONFIGERR;
			} else {
				$i = 0;
				$UserIsSysAdmin = FALSE;
				while ($MyRow = DB_fetch_row($SecResult)) {
					if ($MyRow[0] == 15) {
						$UserIsSysAdmin = TRUE;
					}
					$_SESSION['AllowedPageSecurityTokens'][$i] = $MyRow[0];
					++$i;
				}
			}
			// check if only maintenance users can access KwaMoja
			$SQL = "SELECT confvalue FROM config WHERE confname = 'DB_Maintenance'";
			$MaintenanceResult = DB_query($SQL);
			if (DB_num_rows($MaintenanceResult) == 0) {
				return UL_CONFIGERR;
			} else {
				$MyMaintenanceRow = DB_fetch_row($MaintenanceResult);
				if (($MyMaintenanceRow[0] == -1) and ($UserIsSysAdmin == FALSE)) {
					// the configuration setting has been set to -1 ==> Allow SysAdmin Access Only
					// the user is NOT a SysAdmin
					return UL_MAINTENANCE;
				}
			}
		} else { // Incorrect password
			// 5 login attempts, show failed login screen
			if (!isset($_SESSION['AttemptsCounter'])) {
				$_SESSION['AttemptsCounter'] = 0;
			} elseif ($_SESSION['AttemptsCounter'] >= 5 and isset($Name)) {
				/*User blocked from future accesses until sysadmin releases */
				$SQL = "UPDATE www_users
							SET blocked=1
							WHERE www_users.userid='" . $Name . "'";
				$AuthResult = DB_query($SQL);
				if ($SysAdminEmail != '') {
					$EmailSubject = _('User access blocked') . ' ' . $Name;
					$EmailText = _('User ID') . ' ' . $Name . ' - ' . $Password . ' - ' . _('has been blocked access at') . ' ' . Date('Y-m-d H:i:s') . ' ' . _('from IP') . ' ' . $_SERVER["REMOTE_ADDR"] . ' ' . _('due to too many failed attempts.');
					if ($_SESSION['SmtpSetting'] == 0) {
						mail($SysAdminEmail, $EmailSubject, $EmailText);

					} else {
						include('includes/htmlMimeMail.php');
						$Mail = new htmlMimeMail();
						$Mail->setSubject($EmailSubject);
						$Mail->setText($EmailText);
						$Result = SendmailBySmtp($Mail, array(
							$SysAdminEmail
						));
					}

				}
				return UL_BLOCKED;
			}

			return UL_NOTVALID;
		}
	} // End of userid/password check
	// Run with debugging messages for the system administrator(s) but not anyone else

	return UL_OK;
	/* All is well */
}

?>