<?php

ini_set('max_execution_time', 0);
ini_set('output_buffering', 4096);

session_name('kwamoja_installation');
session_start();

if (isset($_POST['next']) and isset($_SESSION['Installer']['CurrentPage']) and $_SESSION['Installer']['CurrentPage'] == 1) {
	/* Page 1 has been submitted so deal with the input */
	$_SESSION['Installer']['DBMS'] = $_POST['DBMS'];
	switch($_SESSION['Installer']['DBMS']) {
		case 'mariadb':
			$_SESSION['Installer']['DBPort'] = 3306;
			break;
		case 'mysql':
			$_SESSION['Installer']['DBPort'] = 3306;
			break;
		case 'mysqli':
			$_SESSION['Installer']['DBPort'] = 3306;
			break;
		default:
			$_SESSION['Installer']['DBPort'] = 3306;
			break;
	}
	$_SESSION['Installer']['Language'] = $_POST['Language'];
}

if (isset($_POST['next']) and isset($_SESSION['Installer']['CurrentPage']) and $_SESSION['Installer']['CurrentPage'] == 2) {
	/* Page 2 has been submitted so deal with the input */
}

if (isset($_POST['next']) and isset($_SESSION['Installer']['CurrentPage']) and $_SESSION['Installer']['CurrentPage'] == 3) {
	/* Page 3 has been submitted so deal with the input */
	$_SESSION['Installer']['HostName'] = $_POST['HostName'];
	$_SESSION['Installer']['UserName'] = $_POST['UserName'];
	$_SESSION['Installer']['Password'] = $_POST['Password'];
	$_SESSION['Installer']['Database'] = $_POST['Database'];
	if (PingDomain($_SESSION['Installer']['HostName']) < 0) {
		$Errors[] = _('The database host cannot be reached. Maybe you have typed it incorrectly.');
	}

	/* Try to connect to the DBMS */
	switch($_SESSION['Installer']['DBMS']) {
		case 'mariadb':
			$db = @mysqli_connect($_SESSION['Installer']['HostName'], $_SESSION['Installer']['UserName'], $_SESSION['Installer']['Password']);
			break;
		case 'mysql':
			$db = @mysql_connect($_SESSION['Installer']['HostName'] . ':' . $_SESSION['Installer']['DBPort'], $_SESSION['Installer']['UserName'], $_SESSION['Installer']['Password']);
			break;
		case 'mysqli':
			$db = @mysqli_connect($_SESSION['Installer']['HostName'], $_SESSION['Installer']['UserName'], $_SESSION['Installer']['Password']);
			break;
		default:
			$db = @mysqli_connect($_SESSION['Installer']['HostName'], $_SESSION['Installer']['UserName'], $_SESSION['Installer']['Password']);
			break;
	}
	if (!$db) {
		$Errors[] = _('Failed to connect the database management system');
	}

	/* Does the database of that name exist?
	 * If not does the user have the privileges to create it?*/
	$DBExistsSql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $_SESSION['Installer']['Database'] . "'";
	$PrivilegesSql = "SELECT * FROM INFORMATION_SCHEMA.USER_PRIVILEGES WHERE GRANTEE=" . '"' . "'" . $_SESSION['Installer']['UserName'] . "'@'" . $_SESSION['Installer']['HostName'] . "'" . '"' . " AND PRIVILEGE_TYPE='CREATE'";
	switch($_SESSION['Installer']['DBMS']) {
		case 'mariadb':
			$DBExistsResult = @mysqli_query($db, $DBExistsSql);
			$PrivilegesResult = mysqli_query($db, $PrivilegesSql);
			$rows = @mysqli_num_rows($DBExistsResult);
			$Privileges = @mysqli_num_rows($PrivilegesResult);
			break;
		case 'mysql':
			$DBExistsResult = @mysql_query($DBExistsSql, $db);
			$PrivilegesResult = @mysql_query($PrivilegesSql, $db);
			$rows = @mysql_num_rows($DBExistsResult);
			$Privileges = @mysql_num_rows($PrivilegesResult);
			break;
		case 'mysqli':
			$DBExistsResult = @mysqli_query($db, $DBExistsSql);
			$PrivilegesResult = @mysqli_query($db, $PrivilegesSql);
			$rows = @mysqli_num_rows($DBExistsResult);
			$Privileges = @mysqli_num_rows($PrivilegesResult);
			break;
		default:
			$DBExistsResult = @mysqli_query($db, $DBExistsSql);
			$PrivilegesResult = @mysqli_query($db, $PrivilegesSql);
			$rows = @mysqli_num_rows($DBExistsResult);
			$Privileges = @mysqli_num_rows($PrivilegesResult);
			break;
	}
	if ($rows == 0) { /* Then the database does not exist */
		if ($Privileges == 0) {
			$Errors[] = _('The database does not exist, and this database user does not have privileges to create it');
		} else { /* Then we can create the database */
			$sql = "CREATE DATABASE " . $_SESSION['Installer']['Database'];
			switch($_SESSION['Installer']['DBMS']) {
				case 'mariadb':
					$Result = @mysqli_query($db, $sql);
					break;
				case 'mysql':
					$Result = @mysql_query($sql, $db);
					break;
				case 'mysqli':
					$Result = @mysqli_query($db, $sql);
					break;
				default:
					$Result = @mysqli_query($db, $sql);
					break;
			}
		}
	} else { /* Need to make sure any data is removed from existing DB */
		$sql = "SELECT 'TRUNCATE TABLE ' + table_name + ';' FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . $_SESSION['Installer']['Database'] . "'";
		switch($_SESSION['Installer']['DBMS']) {
			case 'mariadb':
				$Result = @mysqli_query($db, $sql);
				break;
			case 'mysql':
				$Result = @mysql_query($sql, $db);
				break;
			case 'mysqli':
				$Result = @mysqli_query($db, $sql);
				break;
			default:
				$Result = @mysqli_query($db, $sql);
				break;
		}
	}
}

if (isset($_POST['next']) and isset($_SESSION['Installer']['CurrentPage']) and $_SESSION['Installer']['CurrentPage'] == 4) {
	/* Page 4 has been submitted so deal with the input */
	$_SESSION['CompanyRecord']['coyname'] = $_POST['CompanyName'];
	$_SESSION['Installer']['CoA'] = $_POST['COA'];
	$_SESSION['Installer']['TimeZone'] = $_POST['TimeZone'];
	$_SESSION['Installer']['Email'] = 'info@example.com';
	$_SESSION['Installer']['AdminAccount'] = $_POST['adminaccount'];
	$_SESSION['Installer']['KwaMojaPassword'] = $_POST['KwaMojaPassword'];
}

if (isset($_GET['New']) or isset($_POST['cancel'])) { /* If the installer is just starting */
	unset($_SESSION['Installer']);
	$_SESSION['Installer']['CurrentPage'] = 1;
	$_SESSION['Installer']['Language'] = 'en_GB.utf8';
	$_SESSION['Installer']['CoA'] = 'en_GB-utf8.php';
	$_SESSION['Installer']['DBMS'] = 'mariadb';
	$_SESSION['Installer']['DBExt'] = 1;
	$_SESSION['Installer']['HostName'] = 'localhost';
	$_SESSION['Installer']['UserName'] = 'root';
	$_SESSION['Installer']['Password'] = '';
	$_SESSION['Installer']['Database'] = 'kwamoja';
	$_SESSION['Installer']['Email'] = 'info@example.com';
	$_SESSION['Installer']['AdminAccount'] = 'admin';
	$_SESSION['Installer']['KwaMojaPassword'] = 'kwamoja';
	$_SESSION['CompanyRecord']['coyname'] = 'KwaMoja';
	$_SESSION['Installer']['TimeZone'] = 'Africa/Nairobi';
} else { /* Move on a page in the wizard */
	if (isset($_POST['next']) and empty($Errors) and $_SESSION['Installer']['CurrentPage'] < 5) {
		$_SESSION['Installer']['CurrentPage']++;
	}
	if (isset($_POST['previous']) and $_SESSION['Installer']['CurrentPage'] > 1) {
		$_SESSION['Installer']['CurrentPage']--;
	}
}

$PathPrefix = '../'; //To point to the includes files correctly

$DefaultLanguage = $_SESSION['Installer']['Language']; // Need the language in this variable as this is the variable used elsewhere in KwaMoja
include($PathPrefix . 'includes/LanguageSetup.php');

/*
 * KwaMoja Installer
 * Step 1: Licence acknowledgement and Choose Language and DBMS
 * Step 2: Check requirements
 * Step 3: Database connection
 * Step 4: Company details
 * Step 5: Administrator account details
 * Step 6: Finalise
 **/

/* Send the HTTP headers */
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title>' . _('KwaMoja Installer') . '</title>
			<link rel="stylesheet" type="text/css" href="installer.css" />
		</head>';

echo '<body>
		<div id="CanvasDiv">';
echo '<h1>' . _('KwaMoja Installation Wizard') . '</h1>';

if (file_exists($PathPrefix . 'config.php') or file_exists($PathPrefix . 'Config.php')) {
	echo '<div class="error">' . _('It seems that the system has been already installed. If you want to install again, please remove the config.php file first') . '</div>';
} else {
	include('Page' . $_SESSION['Installer']['CurrentPage'] . '.php');
}

echo '</div>
	</body>
	</html>';
ob_end_flush();
function PingDomain($domain){
	$starttime = microtime(true);
	$file = @fsockopen ($domain, 80, $errno, $errstr, 10);
	$stoptime = microtime(true);
	$status = 0;
	if (!$file) {
		$status = -1;  // Site is down
	}
	return $status;
}

?>