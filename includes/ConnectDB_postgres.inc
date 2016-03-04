<?php

define('LIKE', 'ILIKE');

if (!isset($DBPort)) {
	$DBPort = 5432;
}
/* $PgConnStr = $PgConnStr = "host=".$Host." dbname=".$_SESSION['DatabaseName']; */
$PgConnStr = 'dbname=' . $_SESSION['DatabaseName'];

if (isset($Host) and ($Host != "")) {
	$PgConnStr = 'host=' . $Host . ' ' . $PgConnStr;
}

if (isset($DBPort) and ($DBPort != "")) {
	$PgConnStr = 'port=' . $DBPort . ' ' . $PgConnStr;
}

if (isset($DBUser) and ($DBUser != "")) {
	// if we have a user we need to use password if supplied
	$PgConnStr .= " user=" . $DBUser;
	if (isset($DBPassword) and ($DBPassword != "")) {
		$PgConnStr .= " password=" . $DBPassword;
	}
}

global $db; // Make sure it IS global, regardless of our context
$db = pg_connect($PgConnStr);

if (!$db) {
	if ($Debug == 1) {
		echo '<br />' . $PgConnStr . '<br />';
	}
	echo '<br />' . _('The company name entered together with the configuration in the file config.php for the database user name and password do not provide the information required to connect to the database.') . '<br /><br />' . _(' Try logging in with an alternative company name.');
	echo '<br /><a href="index.php">' . _('Back to login page') . '</a>';
	unset($_SESSION['DatabaseName']);
	exit;
}

require_once($PathPrefix . 'includes/MiscFunctions.php');

//DB wrapper functions to change only once for whole application

function DB_connect($Host, $DBUser, $DBPassword, $DBPort) {
	/* $PgConnStr = $PgConnStr = "host=".$Host." dbname=".$_SESSION['DatabaseName']; */
	$PgConnStr = 'dbname=' . $_SESSION['DatabaseName'];

	if (isset($Host) and ($Host != "")) {
		$PgConnStr = 'host=' . $Host . ' ' . $PgConnStr;
	}

	if (isset($DBPort) and ($DBPort != "")) {
		$PgConnStr = 'port=' . $DBPort . ' ' . $PgConnStr;
	}

	if (isset($DBUser) and ($DBUser != "")) {
		// if we have a user we need to use password if supplied
		$PgConnStr .= " user=" . $DBUser;
		if (isset($DBPassword) and ($DBPassword != "")) {
			$PgConnStr .= " password=" . $DBPassword;
		}
	}
	return pg_connect($PgConnStr);
}

function DB_query($SQL, $ErrorMessage = '', $DebugMessage = '', $Transaction = false, $TrapErrors = true) {

	global $db;
	global $Debug;
	global $PathPrefix;

	$Result = pg_query($db, $SQL);
	if ($DebugMessage == '') {
		$DebugMessage = _('The SQL that failed was:');
	}
	//if (DB_error_no($Conn) != 0){
	if (!$Result and $TrapErrors) {
		if ($TrapErrors) {
			require_once($PathPrefix . 'includes/header.inc');
		}
		prnMsg($ErrorMessage . '<br />' . DB_error_msg($Conn), 'error', _('DB ERROR:'));
		if ($Debug == 1) {
			echo '<br />' . $DebugMessage . "<br />$SQL<br />";
		}
		if ($Transaction) {
			$SQL = 'rollback';
			$Result = DB_query($SQL);
			if (DB_error_no() != 0) {
				prnMsg('<br />' . _('Error Rolling Back Transaction!!'), '', _('DB DEBUG:'));
			}
		}
		if ($TrapErrors) {
			include($PathPrefix . 'includes/footer.inc');
			exit;
		}
	}
	return $Result;

}

function DB_fetch_row(&$ResultIndex) {
	$RowPointer = pg_fetch_row($ResultIndex);
	return $RowPointer;
}

function DB_fetch_assoc(&$ResultIndex) {

	$RowPointer = pg_fetch_assoc($ResultIndex);
	return $RowPointer;
}

function DB_fetch_array(&$ResultIndex) {
	$RowPointer = pg_fetch_array($ResultIndex);
	return $RowPointer;
}

function DB_fetch_all(&$ResultIndex) {

	$ResultArray = mysqli_fetch_all($ResultIndex, MYSQLI_ASSOC);
	return $ResultArray;
}

function DB_data_seek(&$ResultIndex, $Record) {
	pg_result_seek($ResultIndex, $Record);
}

function DB_free_result(&$ResultIndex) {
	pg_free_result($ResultIndex);
}

function DB_num_rows(&$ResultIndex) {
	return pg_num_rows($ResultIndex);
}
// Added by MGT
function DB_affected_rows(&$ResultIndex) {
	return pg_affected_rows($ResultIndex);
}

function DB_error_no() {
	global $db;
	return DB_error_msg() == "" ? 0 : -1;
}

function DB_error_msg() {
	global $db;
	return pg_last_error($db);
}

function DB_Last_Insert_ID($Table, $FieldName) {
	global $db;
	$TemporaryResult = DB_query("SELECT currval('" . $Table . "_" . $FieldName . "_seq') FROM " . $Table);
	$Result = pg_fetch_result($TemporaryResult, 0, 0);
	DB_free_result($TemporaryResult);
	return $Result;
}

function DB_escape_string($String) {
	return pg_escape_string($String);
}

function interval($Value, $Interval) {
	global $DBType;
	return "\n" . 'interval ' . $Value . ' ' . $Interval . "\n";
}

function DB_show_tables($TableName = '%') {
	global $db;
	$Result = DB_query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name " . LIKE . " '" . $TableName . "'");
	return $Result;
}

function DB_show_fields($TableName) {
	global $db;
	$Result = DB_query("SELECT table_name FROM information_schema.tables WHERE table_schema='public' AND table_name='" . $TableName . "'");
	if (DB_num_rows($Result) == 1) {
		$Result = DB_query("SELECT column_name FROM information_schema.columns WHERE table_name ='$TableName'");
		return $Result;
	}
}

function DB_Maintenance() {
	global $db;

	prnMsg(_('The system has just run the regular database administration and optimisation routine'), 'info');

	$Result = DB_query('VACUUM ANALYZE');

	$Result = DB_query("UPDATE config
				SET confvalue=CURRENT_DATE
				WHERE confname='DB_Maintenance_LastRun'");
}

function DB_select_database($DBName) {
	global $db;
	mysqli_select_db($db, $DBName);
}

function DB_set_timezone() {
	$Now = new DateTime();
	$Minutes = $Now->getOffset() / 60;
	$Sign = ($Minutes < 0 ? -1 : 1);
	$Minutes = abs($Minutes);
	$Hours = floor($Minutes / 60);
	$Minutes -= $Hours * 60;
	$Offset = sprintf('%+d:%02d', $Hours * $Sign, $Minutes);
	$Result = DB_query("SET time_zone='" . $Offset . "'");
}

?>