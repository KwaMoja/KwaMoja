<?php

$host = $_SESSION['Installer']['HostName'];
$DBUser = $_SESSION['Installer']['UserName'];
$DBPassword = $_SESSION['Installer']['Password'];
$DBType = $_SESSION['Installer']['DBMS'];
$_SESSION['DatabaseName'] = $_SESSION['Installer']['Database'];

include($PathPrefix . 'includes/ConnectDB_' . $_SESSION['Installer']['DBMS'] . '.inc');
include($PathPrefix . 'includes/UpgradeDB_' . $_SESSION['Installer']['DBMS'] . '.inc');
$Path_To_Root = '..';
$Config_File = $Path_To_Root . '/config.php';
if (!file_exists($Path_To_Root . '/companies/' . $_SESSION['Installer']['Database'])) {
	$CompanyDir = $Path_To_Root . '/companies/' . $_SESSION['Installer']['Database'];
	$Result = mkdir($CompanyDir);
	$Result = mkdir($CompanyDir . '/part_pics');
	$Result = mkdir($CompanyDir . '/EDI_Incoming_Orders');
	$Result = mkdir($CompanyDir . '/reports');
	$Result = mkdir($CompanyDir . '/EDI_Sent');
	$Result = mkdir($CompanyDir . '/EDI_Pending');
	$Result = mkdir($CompanyDir . '/reportwriter');
	$Result = mkdir($CompanyDir . '/pdf_append');
	$Result = mkdir($CompanyDir . '/FormDesigns');
	copy($Path_To_Root . '/companies/kwamoja/FormDesigns/GoodsReceived.xml', $CompanyDir . '/FormDesigns/GoodsReceived.xml');
	copy($Path_To_Root . '/companies/kwamoja/FormDesigns/PickingList.xml', $CompanyDir . '/FormDesigns/PickingList.xml');
	copy($Path_To_Root . '/companies/kwamoja/FormDesigns/PurchaseOrder.xml', $CompanyDir . '/FormDesigns/PurchaseOrder.xml');
	copy($Path_To_Root . '/companies/kwamoja/FormDesigns/Journal.xml', $CompanyDir . '/FormDesigns/Journal.xml');
	echo '<div class="success">' . _('The companies directory has been successfully created') . '</div>';
	ob_flush();
	if (isset($File_Temp_Name)) {
		$Result = move_uploaded_file($File_Temp_Name, $CompanyDir . '/logo.jpg');

	} else {
		$Result = copy($Path_To_Root . '/logo_server.jpg', $CompanyDir . '/logo.jpg');
	}
	if ($Result) {
		echo '<div class="success">' . _('Your logo has been successfully uploaded') . '</div>';
	} else {
		echo '<div class="warn">' . _('Your logo could not be uploaded. You must copy this to your companies directory later.') . '</div>';
	}
	ob_flush();
} else {
	echo '<div class="error">' . _('This company name already exists') . '</div>';
	exit;
}
//$msg holds the text of the new config.php file
$msg = "<?php\n\n";
$msg .= "// User configurable variables\n";
$msg .= "//---------------------------------------------------\n\n";
$msg .= "//DefaultLanguage to use for the login screen and the setup of new users.\n";
$msg .= "\$DefaultLanguage = '" . $_SESSION['Installer']['Language'] . "';\n\n";
$msg .= "// Whether to display the demo login and password or not on the login screen\n";
$msg .= "\$AllowDemoMode = FALSE;\n\n";
$msg .= "// Connection information for the database\n";
$msg .= "// \$host is the computer ip address or name where the database is located\n";
$msg .= "// assuming that the webserver is also the sql server\n";
$msg .= "\$host = '" . $host . "';\n\n";
$msg .= "// assuming that the web server is also the sql server\n";
$msg .= "\$DBType = '" . $_SESSION['Installer']['DBMS'] . "';\n";
$msg .= "//assuming that the web server is also the sql server\n";
$msg .= "\$DBUser = '" . $DBUser . "';\n";
$msg .= "\$DBPassword = '" . $DBPassword . "';\n";
$msg .= "// The timezone of the business - this allows the possibility of having;\n";
$msg .= "date_default_timezone_set('" . $_SESSION['Installer']['TimeZone'] . "');\n";
$msg .= "putenv('TZ=" . $_SESSION['Installer']['TimeZone'] . "');\n";
$msg .= "\$AllowCompanySelectionBox = 'ShowSelectionBox';\n";
$msg .= "//The system administrator name use the user input mail;\n";
if (strtolower($_SESSION['Installer']['Email']) != 'admin@kwamoja.com') {
	$msg .= "\$SysAdminEmail = '" . $_SESSION['Installer']['Email'] . "';\n";
}
if (isset($NewCompany)) {
	$msg .= "\$DefaultCompany = '" . $_SESSION['Installer']['Database'] . "';\n";
} else {
	$msg .= "\$DefaultCompany = '" . $_SESSION['Installer']['Database'] . "';\n";
}
$msg .= "\$SessionLifeTime = 3600;\n";
$msg .= "\$MaximumExecutionTime = 120;\n";
$msg .= "\$CryptFunction = 'sha1';\n";
$msg .= "\$DefaultClock = 12;\n";
$msg .= "\$RootPath = dirname(htmlspecialchars(\$_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'));\n";
$msg .= "if (isset(\$DirectoryLevelsDeep)){\n";
$msg .= "   for (\$i=0;\$i<\$DirectoryLevelsDeep;\$i++){\n";
$msg .= "		\$RootPath = mb_substr(\$RootPath,0, strrpos(\$RootPath,'/'));\n";
$msg .= "	}\n";
$msg .= "}\n";

$msg .= "if (\$RootPath == '/' OR \$RootPath == '\\\') {\n";
$msg .= "	\$RootPath = '';\n";
$msg .= "}\n";
$msg .= "error_reporting(E_ALL && ~E_NOTICE);\n";
$msg .= "/* Make sure there is nothing - not even spaces after this last ?> */\n";
$msg .= "?>";

//write the config.php file since we have test the writability of the root path and companies,
//there is little possibility that it will fail here. So just an warn if it is failed.
if (!$zp = fopen($Path_To_Root . '/config.php', 'w')) {
	echo '<div class="error">' . _("Cannot open the configuration file") . $Config_File . '</div>';
} else {
	if (!fwrite($zp, $msg)) {
		fclose($zp);
		echo '<div class="error">' . _("Cannot write to the configuration file") . $Config_File . '</div>';
	}
	//close file
	fclose($zp);
}
echo '<div class="success">' . _('The config.php file has been created based on your settings.') . '</div>';
ob_flush();

echo '<legend>' . _('Building your database.') . ' ' . _('This may take some time, please be patient') . '</legend>';
echo '<div id="progress" class="centre" style="border-radius: 5px;width:100%;border:1px solid #157213;"></div>';
echo '<div id="information" style="width"></div>';
$StartingUpdate = 0;
$EndingUpdate = HighestFileName('../');
unset($_SESSION['Updates']);
$_SESSION['Updates']['Errors'] = 0;
$_SESSION['Updates']['Successes'] = 0;
$_SESSION['Updates']['Warnings'] = 0;
for ($UpdateNumber = $StartingUpdate; $UpdateNumber <= $EndingUpdate; $UpdateNumber++) {
	if (file_exists($PathPrefix . 'sql/updates/' . $UpdateNumber . '.php')) {
		$percent = intval($UpdateNumber / $EndingUpdate * 100) . "%";
		echo '<script language="javascript">
						document.getElementById("progress").innerHTML="<div style=\"margin: 1px;border-radius: 5px; width:' . $percent . ';background-color:#157213;\">&nbsp;</div>";
						document.getElementById("information").innerHTML="' . $UpdateNumber . ' file(s) processed.";
					</script>';
		echo str_repeat(' ', 1024 * 4);
		$sql = "SET foreign_key_checks=0";
		$result = executeSQL($sql, $db, False);
		flush();
		if ($result == 0) {
			include($PathPrefix . 'sql/updates/' . $UpdateNumber . '.php');
		}
		flush();
	}
}
if ($_SESSION['Updates']['Errors'] > 0) {
	echo 'Number of Errors ' . $_SESSION['Updates']['Errors'];
	foreach ($_SESSION['Updates']['Messages'] as $Message) {
		echo '<div class="error">' . $Message . '</div>';
	}
	exit;
}
/* Now we uploade the chosen chart of accounts */
$sql = "SET foreign_key_checks=0";
$result = executeSQL($sql, $db, False);
include($PathPrefix . 'install/coa/' . $_SESSION['Installer']['CoA']);
echo '<div class="success">' . _('Your chosen chart of accounts has been uploaded') . '</div>';
ob_flush();
/* Create the admin user */
InsertRecord('www_users', array(
	'userid'
), array(
	'admin'
), array(
	'userid',
	'password',
	'realname',
	'email',
	'displayrecordsmax',
	'fullaccess',
	'cancreatetender',
	'modulesallowed',
	'blocked',
	'theme',
	'language',
	'pdflanguage',
	'fontsize'
), array(
	$_SESSION['Installer']['AdminAccount'],
	sha1($_SESSION['Installer']['KwaMojaPassword']),
	$_SESSION['Installer']['AdminAccount'],
	$_SESSION['Installer']['Email'],
	50,
	8,
	1,
	'1,1,1,1,1,1,1,1,1,1,1,1,',
	0,
	'aguapop',
	$_SESSION['Installer']['Language'],
	0,
	0
), $db);
if (isset($_POST['Demo'])) {
	echo '<legend>' . _('Populating the database with demo currencies data.') . '</legend>';
	PopulateSQLDataBySQL($PathPrefix . 'sql/demodata/currencies.sql', $db, $DBType, false, $_SESSION['Installer']['Database']);

	echo '<legend>' . _('Populating the database with demo salestypes data.') . '</legend>';
	PopulateSQLDataBySQL($PathPrefix . 'sql/demodata/salestypes.sql', $db, $DBType, false, $_SESSION['Installer']['Database']);

	echo '<legend>' . _('Populating the database with demo areas data.') . '</legend>';
	PopulateSQLDataBySQL($PathPrefix . 'sql/demodata/areas.sql', $db, $DBType, false, $_SESSION['Installer']['Database']);

	echo '<legend>' . _('Populating the database with demo holdreasons data.') . '</legend>';
	PopulateSQLDataBySQL($PathPrefix . 'sql/demodata/holdreasons.sql', $db, $DBType, false, $_SESSION['Installer']['Database']);

	echo '<legend>' . _('Populating the database with demo paymentterms data.') . '</legend>';
	PopulateSQLDataBySQL($PathPrefix . 'sql/demodata/paymentterms.sql', $db, $DBType, false, $_SESSION['Installer']['Database']);

	echo '<legend>' . _('Populating the database with demo suppliers data.') . '</legend>';
	PopulateSQLDataBySQL($PathPrefix . 'sql/demodata/suppliers.sql', $db, $DBType, false, $_SESSION['Installer']['Database']);

	echo '<legend>' . _('Populating the database with demo debtors data.') . '</legend>';
	PopulateSQLDataBySQL($PathPrefix . 'sql/demodata/debtorsmaster.sql', $db, $DBType, false, $_SESSION['Installer']['Database']);

	echo '<legend>' . _('Populating the database with demo salesman data.') . '</legend>';
	PopulateSQLDataBySQL($PathPrefix . 'sql/demodata/salesman.sql', $db, $DBType, false, $_SESSION['Installer']['Database']);

	echo '<legend>' . _('Populating the database with demo taxprovinces data.') . '</legend>';
	PopulateSQLDataBySQL($PathPrefix . 'sql/demodata/taxprovinces.sql', $db, $DBType, false, $_SESSION['Installer']['Database']);

	echo '<legend>' . _('Populating the database with demo locations data.') . '</legend>';
	PopulateSQLDataBySQL($PathPrefix . 'sql/demodata/locations.sql', $db, $DBType, false, $_SESSION['Installer']['Database']);

	echo '<legend>' . _('Populating the database with demo branch data.') . '</legend>';
	PopulateSQLDataBySQL($PathPrefix . 'sql/demodata/custbranch.sql', $db, $DBType, false, $_SESSION['Installer']['Database']);

	echo '<legend>' . _('Populating the database with demo stockcategory data.') . '</legend>';
	PopulateSQLDataBySQL($PathPrefix . 'sql/demodata/stockcategory.sql', $db, $DBType, false, $_SESSION['Installer']['Database']);

	echo '<legend>' . _('Populating the database with demo unitsofmeasure data.') . '</legend>';
	PopulateSQLDataBySQL($PathPrefix . 'sql/demodata/unitsofmeasure.sql', $db, $DBType, false, $_SESSION['Installer']['Database']);

	echo '<legend>' . _('Populating the database with demo stockmaster data.') . '</legend>';
	PopulateSQLDataBySQL($PathPrefix . 'sql/demodata/stockmaster.sql', $db, $DBType, false, $_SESSION['Installer']['Database']);
}


function HighestFileName($PathPrefix) {
	$files = glob($PathPrefix . 'sql/updates/*.php');
	natsort($files);
	return basename(array_pop($files), ".php");
}


function executeSQL($sql, $db, $TrapErrors = False) {
	global $SQLFile;
	/* Run an sql statement and return an error code */
	if (!isset($SQLFile)) {
		DB_IgnoreForeignKeys($db);
		$result = DB_query($sql, $db, '', '', false, $TrapErrors);
		$ErrorNumber = DB_error_no($db);
		DB_ReinstateForeignKeys($db);
		return $ErrorNumber;
	} else {
		fwrite($SQLFile, $sql . ";\n");
	}
}

function updateDBNo($NewNumber, $db) {
	global $SQLFile;
	if (!isset($SQLFile)) {
		$sql = "UPDATE config SET confvalue='" . $NewNumber . "' WHERE confname='DBUpdateNumber'";
		executeSQL($sql, $db);
		$_SESSION['DBUpdateNumber'] = $NewNumber;
	}
}
//@para $File is the sql file name
//@para $db is the DB connect reference
//@para $DBType refer to mysqli or mysql connection
//@para $NewDB is the new database name
//@para $DemoDB is the demo database name
//The purpose of this function is populate the database with mysql extention
function PopulateSQLDataBySQL($File, $db, $DBType, $NewDB = false, $DemoDB = 'kwamojademo') {
	$dbName = ($NewDB) ? $NewDB : $DemoDB;
	($DBType == 'mysql') ? mysql_select_db($dbName, $db) : mysqli_select_db($db, $dbName);
	$SQLScriptFile = file($File);
	$ScriptFileEntries = sizeof($SQLScriptFile);
	$SQL = '';
	$InAFunction = false;
	echo '<div id="progress' . $File . '" class="centre" style="border-radius: 5px;width:100%;border:1px solid #157213;"></div>';
	echo '<div id="information' . $File . '" style="width"></div>';
	for ($i = 1; $i <= $ScriptFileEntries; $i++) {

		$SQLScriptFile[$i-1] = trim($SQLScriptFile[$i-1]);
		//ignore lines that start with -- or USE or /*

		$SQL .= ' ' . $SQLScriptFile[$i-1];

		//check if this line kicks off a function definition - pg chokes otherwise
		if (mb_substr($SQLScriptFile[$i-1], 0, 15) == 'CREATE FUNCTION') {
			$InAFunction = true;
		}
		//check if this line completes a function definition - pg chokes otherwise
		if (mb_substr($SQLScriptFile[$i-1], 0, 8) == 'LANGUAGE') {
			$InAFunction = false;
		}
		if (mb_strpos($SQLScriptFile[$i-1], ';') > 0 and !$InAFunction) {
			// Database created above with correct name.
			$result = ($DBType == 'mysql') ? mysql_query($SQL, $db) : mysqli_query($db, $SQL);
			$SQL = '';
		}
		$percent = intval($i / $ScriptFileEntries * 100) . "%";
		echo '<script language="javascript">
						document.getElementById("progress' . $File . '").innerHTML="<div style=\"margin: 1px;border-radius: 5px; width:' . $percent . ';background-color:#157213;\">&nbsp;</div>";
						document.getElementById("information' . $File . '").innerHTML="' . ($i - 1) . ' row(s) processed.";
					</script>';
		echo str_repeat(' ', 1024 * 4);

	} //end of for loop around the lines of the sql script
}

echo '<form id="DatabaseConfig" action="../index.php" method="post">';
echo '<fieldset>
			<legend>' . _('KwaMoja Installation Is Completed') . '</legend>
			<div class="page_help_text">
				<ul>
					<li>' . _('The KwaMoja installation has been successfully completed..') . '</li>
					<li>' . _('Click on the button below to start using KwaMoja.') . '</li>
					<li>' . _('When you first sign in you will be taken to a series of screens to help you set KwaMoja up.') . '</li>
					<li>' . _('Please enjoy using the software and hopefully contribute back to the project.') . '</li>
				</ul>
			</div>
		</fieldset>';

echo '<fieldset style="text-align:center">
		<button type="submit" name="end">' . _('Start KwaMoja') . '<img src="restart.png" style="float:right" /></button>
	</fieldset>
</form>';

echo '<script src="installer.js"></script>';

?>