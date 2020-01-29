<?php
$Host = $_SESSION['Installer']['HostName'];
$DBUser = $_SESSION['Installer']['UserName'];
$DBPassword = $_SESSION['Installer']['Password'];
$DBType = $_SESSION['Installer']['DBMS'];
$_SESSION['DatabaseName'] = $_SESSION['Installer']['Database'];
$DefaultDatabase = 'default';

/* Try to connect to the DBMS */
switch ($_SESSION['Installer']['DBMS']) {
	case 'mariadb':
		$DB = @mysqli_connect($_SESSION['Installer']['HostName'], $_SESSION['Installer']['UserName'], $_SESSION['Installer']['Password'], $_SESSION['DatabaseName']);
	break;
	case 'mysql':
		$DB = @mysql_connect($_SESSION['Installer']['HostName'] . ':' . $_SESSION['Installer']['DBPort'], $_SESSION['Installer']['UserName'], $_SESSION['Installer']['Password']);
	break;
	case 'mysqli':
		$DB = @mysqli_connect($_SESSION['Installer']['HostName'], $_SESSION['Installer']['UserName'], $_SESSION['Installer']['Password'], $_SESSION['DatabaseName']);
	break;
	case 'posgres':
		$DB = pg_connect('host=' . $_SESSION['Installer']['HostName'] . ' dbname=kwamoja port=5432 user=postgres');;
	break;
	default:
		$DB = @mysqli_connect($_SESSION['Installer']['HostName'], $_SESSION['Installer']['UserName'], $_SESSION['Installer']['Password']);
	break;
}
if (!$DB) {
	$Errors[] = _('Failed to connect the database management system');
} else {
	$Result = @mysqli_query($DB, 'SET SQL_MODE="NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"');
}


include ($PathPrefix . 'includes/ConnectDB_' . $_SESSION['Installer']['DBMS'] . '.php');
include ($PathPrefix . 'includes/UpgradeDB_' . $_SESSION['Installer']['DBMS'] . '.php');
$Result = DB_query('SET SQL_MODE="NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"');
include ($PathPrefix . 'includes/DateFunctions.php');
date_default_timezone_set($_SESSION['Installer']['TimeZone']);
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
	copy($Path_To_Root . '/companies/default/FormDesigns/GoodsReceived.xml', $CompanyDir . '/FormDesigns/GoodsReceived.xml');
	copy($Path_To_Root . '/companies/default/FormDesigns/PickingList.xml', $CompanyDir . '/FormDesigns/PickingList.xml');
	copy($Path_To_Root . '/companies/default/FormDesigns/PurchaseOrder.xml', $CompanyDir . '/FormDesigns/PurchaseOrder.xml');
	copy($Path_To_Root . '/companies/default/FormDesigns/Journal.xml', $CompanyDir . '/FormDesigns/Journal.xml');
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
//$Msg holds the text of the new config.php file
$Msg = "<?php\n\n";
$Msg.= "// User configurable variables\n";
$Msg.= "//---------------------------------------------------\n\n";
$Msg.= "//DefaultLanguage to use for the login screen and the setup of new users.\n";
$Msg.= "\$DefaultLanguage = '" . $_SESSION['Installer']['Language'] . "';\n\n";
$Msg.= "// Whether to display the demo login and password or not on the login screen\n";
$Msg.= "\$AllowDemoMode = FALSE;\n\n";
$Msg.= "// Connection information for the database\n";
$Msg.= "// \$Host is the computer ip address or name where the database is located\n";
$Msg.= "// assuming that the webserver is also the sql server\n";
$Msg.= "\$Host = '" . $Host . "';\n\n";
$Msg.= "// assuming that the web server is also the sql server\n";
$Msg.= "\$DBType = '" . $_SESSION['Installer']['DBMS'] . "';\n";
$Msg.= "//assuming that the web server is also the sql server\n";
$Msg.= "\$DBUser = '" . $DBUser . "';\n";
$Msg.= "\$DBPassword = '" . $DBPassword . "';\n";
$Msg.= "// The timezone of the business - this allows the possibility of having;\n";
$Msg.= "define('TIMEZONE', '" . $_SESSION['Installer']['TimeZone'] . "');\n";
$Msg.= "date_default_timezone_set(TIMEZONE);\n";
$Msg.= "\$AllowCompanySelectionBox = 'ShowSelectionBox';\n";
$Msg.= "//The system administrator name use the user input mail;\n";
if (strtolower($_SESSION['Installer']['Email']) != 'admin@kwamoja.com') {
	$Msg.= "\$SysAdminEmail = '" . $_SESSION['Installer']['Email'] . "';\n";
}
if (isset($NewCompany)) {
	$Msg.= "\$DefaultCompany = '" . $_SESSION['Installer']['Database'] . "';\n";
} else {
	$Msg.= "\$DefaultCompany = '" . $_SESSION['Installer']['Database'] . "';\n";
}
$Msg.= "\$SessionLifeTime = 3600;\n";
$Msg.= "\$MaximumExecutionTime = 120;\n";
$Msg.= "\$DefaultClock = 12;\n";
$Msg.= "\$RootPath = dirname(htmlspecialchars(\basename(__FILE__),ENT_QUOTES,'UTF-8'));\n";
$Msg.= "if (isset(\$DirectoryLevelsDeep)){\n";
$Msg.= "   for (\$i=0;\$i<\$DirectoryLevelsDeep;\$i++){\n";
$Msg.= "		\$RootPath = mb_substr(\$RootPath,0, strrpos(\$RootPath,'/'));\n";
$Msg.= "	}\n";
$Msg.= "}\n";

$Msg.= "if (\$RootPath == '/' OR \$RootPath == '\\\') {\n";
$Msg.= "	\$RootPath = '';\n";
$Msg.= "}\n";
$Msg.= "error_reporting(E_ALL && ~E_NOTICE);\n";
$Msg.= "\$Debug = 0;\n";
$Msg.= "/* Make sure there is nothing - not even spaces after this last ?> */\n";
$Msg.= "?>";

//write the config.php file since we have test the writability of the root path and companies,
//there is little possibility that it will fail here. So just an warn if it is failed.
if (!$zp = fopen($Path_To_Root . '/config.php', 'w')) {
	echo '<div class="error">' . _("Cannot open the configuration file") . $Config_File . '</div>';
} else {
	if (!fwrite($zp, $Msg)) {
		fclose($zp);
		echo '<div class="error">' . _("Cannot write to the configuration file") . $Config_File . '</div>';
	}
	//close file
	fclose($zp);
}
echo '<div class="success">' . _('The config.php file has been created based on your settings.') . '</div>';
ob_flush();

$CompanyFileHandler = fopen($Path_To_Root . '/companies/' . $_SESSION['DatabaseName'] . '/Companies.php', 'w');
$Contents = "<?php\n\n";
$Contents.= "\$CompanyName['" . $_SESSION['DatabaseName'] . "'] = '" . $_SESSION['CompanyRecord']['coyname'] . "';\n";
$Contents.= "?>";

if (!fwrite($CompanyFileHandler, $Contents)) {
	fclose($CompanyFileHandler);
	echo '<div class="error">' . _("Cannot write to the Companies.php file") . '</div>';
}
//close file
fclose($CompanyFileHandler);

// echo '<legend>' . _('Building your database.') . ' ' . _('This may take some time, please be patient') . '</legend>';
echo '<div id="information" style="width"></div>';

/* Setup the structure of the database */
$DBName = $_SESSION['Installer']['Database'];

$SQLScriptFile = file('db/structure.sql');
$ScriptFileEntries = sizeof($SQLScriptFile);
$SQL = '';
$InAFunction = false;
DB_IgnoreForeignKeys();
for ($i = 0;$i < $ScriptFileEntries;$i++) {

	$SQLScriptFile[$i] = trim($SQLScriptFile[$i]);
	//ignore lines that start with -- or USE or /*
	if (mb_substr($SQLScriptFile[$i], 0, 2) != '--' and mb_strstr($SQLScriptFile[$i], '/*') == false and mb_strlen($SQLScriptFile[$i]) > 1) {

		$SQL.= ' ' . $SQLScriptFile[$i];

		//check if this line kicks off a function definition - pg chokes otherwise
		if (mb_substr($SQLScriptFile[$i], 0, 15) == 'CREATE FUNCTION') {
			$InAFunction = true;
		}
		//check if this line completes a function definition - pg chokes otherwise
		if (mb_substr($SQLScriptFile[$i], 0, 8) == 'LANGUAGE') {
			$InAFunction = false;
		}
		if (mb_strpos($SQLScriptFile[$i], ';') > 0 and !$InAFunction) {
			// Database created above with correct name.
			if (strncasecmp($SQL, ' CREATE DATABASE ', 17) and strncasecmp($SQL, ' USE ', 5)) {
				$SQL = mb_substr($SQL, 0, mb_strlen($SQL) - 1);
				$Result = DB_query($SQL);
			}
			$SQL = '';
		}

	} //end if its a valid sql line not a comment

} //end of for loop around the lines of the sql script
/* End database structure */

if (DB_error_no() == 0) {
	echo '<div class="success">' . _('The database has been created.') . '</div>';
} else {
	echo '<div class="error">' . _('There was an error creating the database.') . ' - ' . DB_error_msg() . '</div>';
}

$SQL = "INSERT INTO config VALUES('VersionNumber', '16.03')";
$Result = DB_query($SQL);

if (DB_error_no() == 0) {
	echo '<div class="success">' . _('The version number has been inserted.') . '</div>';
} else {
	echo '<div class="error">' . _('There was an error inserting the version number') . ' - ' . DB_error_msg() . '</div>';
}

$SQL = "INSERT INTO config VALUES('DBUpdateNumber', " . HighestFileName('../') . ")";
$Result = DB_query($SQL);

if (DB_error_no() == 0) {
	echo '<div class="success">' . _('The database update revision has been inserted.') . '</div>';
} else {
	echo '<div class="error">' . _('There was an error inserting the DB revision number') . ' - ' . DB_error_msg() . '</div>';
}

$SQL = "INSERT INTO www_users  (userid,
								password,
								realname,
								email,
								displayrecordsmax,
								fullaccess,
								cancreatetender,
								modulesallowed,
								blocked,
								theme,
								language,
								pdflanguage,
								changepassword,
								fontsize
							) VALUES (
								'" . $_SESSION['Installer']['AdminAccount'] . "',
								'" . CryptPass($_SESSION['Installer']['KwaMojaPassword']) . "',
								'" . _('Administrator') . "',
								'" . $_SESSION['Installer']['Email'] . "',
								50,
								8,
								1,
								'1,1,1,1,1,1,1,1,0,0,0,1,1,1,1,',
								0,
								'aguapop',
								'" . $_SESSION['Installer']['Language'] . "',
								0,
								0,
								0
							)";
$Result = DB_query($SQL);

if (DB_error_no() == 0) {
	echo '<div class="success">' . _('The admin user has been inserted.') . '</div>';
} else {
	echo '<div class="error">' . _('There was an error inserting the admin user') . ' - ' . DB_error_msg() . '</div>';
}

/* Now we uploade the chosen chart of accounts */
if (!isset($_POST['Demo'])) {
	$COAScriptFile = file($_SESSION['Installer']['CoA']);
	$ScriptFileEntries = sizeof($COAScriptFile);
	$SQL = '';
	$InAFunction = false;
	DB_IgnoreForeignKeys();
	for ($i = 0;$i < $ScriptFileEntries;$i++) {

		$COAScriptFile[$i] = trim($COAScriptFile[$i]);
		//ignore lines that start with -- or USE or /*
		if (mb_substr($COAScriptFile[$i], 0, 2) != '--' and mb_strstr($COAScriptFile[$i], '/*') == false and mb_strlen($COAScriptFile[$i]) > 1) {

			$SQL.= ' ' . $COAScriptFile[$i];

			//check if this line kicks off a function definition - pg chokes otherwise
			if (mb_substr($COAScriptFile[$i], 0, 15) == 'CREATE FUNCTION') {
				$InAFunction = true;
			}
			//check if this line completes a function definition - pg chokes otherwise
			if (mb_substr($COAScriptFile[$i], 0, 8) == 'LANGUAGE') {
				$InAFunction = false;
			}
			if (mb_strpos($COAScriptFile[$i], ';') > 0 and !$InAFunction) {
				// Database created above with correct name.
				if (strncasecmp($SQL, ' CREATE DATABASE ', 17) and strncasecmp($SQL, ' USE ', 5)) {
					$SQL = mb_substr($SQL, 0, mb_strlen($SQL) - 1);
					$Result = DB_query($SQL);
				}
				$SQL = '';
			}

		} //end if its a valid sql line not a comment

	} //end of for loop around the lines of the sql script
	echo '<div class="success">' . _('Your chosen chart of accounts has been uploaded') . '</div>';
	ob_flush();
	/* Create the admin user */
} else {
	echo '<legend>' . _('Populating the database with demo data.') . '</legend>';
	PopulateSQLDataBySQL($PathPrefix . 'sql/demodata/data.sql', $DB, $DBType, false, $_SESSION['Installer']['Database']);
}

$SQL = "INSERT INTO glaccountusers SELECT accountcode, 'admin', 1, 1 FROM chartmaster";
$Result = DB_query($SQL);
if (DB_error_no() == 0) {
	echo '<div class="success">' . _('The admin user has been given permissions on all GL accounts.') . '</div>';
} else {
	echo '<div class="error">' . _('There was an error with creating permission for the admin user') . ' - ' . DB_error_msg() . '</div>';
}

$CountryOfOperation = substr(basename($_SESSION['Installer']['CoA'], '.sql'), 3, 2);

$SQL = array();
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('AllowOrderLineItemNarrative','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('AllowSalesOfZeroCostItems','0')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('AutoAuthorisePO','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('AutoCreateWOs','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('AutoDebtorNo','0')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('AutoIssue','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('AutoSupplierNo','0')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('CheckCreditLimits','0')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('Check_Price_Charged_vs_Order_Price','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('Check_Qty_Charged_vs_Del_Qty','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('CountryOfOperation','" . $CountryOfOperation . "')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('CreditingControlledItems_MustExist','0')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('DB_Maintenance','30')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('DB_Maintenance_LastRun','" . date("Y-m-d") . "')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('DefaultBlindPackNote','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('DefaultCreditLimit','1000')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('DefaultCustomerType','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('DefaultDateFormat','d/m/Y')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('DefaultDisplayRecordsMax','50')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('DefaultFactoryLocation','MEL')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('DefaultPriceList','GE')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('DefaultSupplierType','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('DefaultTaxCategory','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('DefaultTheme','aguapop')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('Default_Shipper','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('DefineControlledOnWOEntry','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('DispatchCutOffTime','14')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('DoFreightCalc','0')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('EDIHeaderMsgId','D:01B:UN:EAN010')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('EDIReference','KWAMOJA')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('EDI_Incoming_Orders','companies/" . $_SESSION['DatabaseName'] . "/EDI_Incoming_Orders')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('EDI_MsgPending','companies/" . $_SESSION['DatabaseName'] . "/EDI_MsgPending')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('EDI_MsgSent','companies/" . $_SESSION['DatabaseName'] . "/EDI_Sent')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ExchangeRateFeed','ECB')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('Extended_CustomerInfo','0')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('Extended_SupplierInfo','0')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('FactoryManagerEmail','" . $_SESSION['Installer']['Email'] . "')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('FirstLogIn','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('FreightChargeAppliesIfLessThan','1000')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('FreightTaxCategory','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('FrequentlyOrderedItems','0')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('geocode_integration','0')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('GoogleTranslatorAPIKey','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('HTTPS_Only','0')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('InventoryManagerEmail','" . $_SESSION['Installer']['Email'] . "')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('InvoiceQuantityDefault','0')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ItemDescriptionLanguages','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('KwaMojaImagesFromOpenCart','data/part_pics/')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('KwaMojaToOpenCartDaily_LastRun','0000-00-00 00:00:00')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('KwaMojaToOpenCartHourly_LastRun','0000-00-00 00:00:00')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('LogPath','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('LogSeverity','0')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('MaxImageSize','300')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('MonthsAuditTrail','12')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('NewBranchesMustBeAuthorised','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('NumberOfMonthMustBeShown','6')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('NumberOfPeriodsOfStockUsage','12')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('OpenCartToKwaMoja_LastRun','0000-00-00 00:00:00')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('OverChargeProportion','30')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('OverReceiveProportion','20')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('PackNoteFormat','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('PageLength','48')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('part_pics_dir','companies/" . $_SESSION['DatabaseName'] . "/EDI_Sent')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('PastDueDays1','30')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('PastDueDays2','60')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('PO_AllowSameItemMultipleTimes','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ProhibitJournalsToControlAccounts','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ProhibitNegativeStock','0')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ProhibitPostingsBefore','2013-02-28')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('PurchasingManagerEmail','" . $_SESSION['Installer']['Email'] . "')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('QualityCOAText','Disclaimer: No information supplied by our company constitutes a warranty regarding product performance or use. Any information regarding\r\nperformance or use is only offered as suggestion for investigation for use, based upon our company or other customer experience. our company\r\nmakes no warranties, expressed or implied, concerning the suitability or fitness of any of its products for any particular purpose. It is the\r\nresponsibility of the customer to determine that the product is safe, lawful and technically suitable for the intended use. The disclosure of\r\ninformation herein is not a license to operate under, or a recommendation to infringe any patents.')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('QualityLogSamples','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('QualityProdSpecText','Disclaimer: No information supplied by our company constitutes a warranty regarding product performance or use. Any information regarding\r\nperformance or use is only offered as suggestion for investigation for use, based upon our company or other customer experience. our company\r\nmakes no warranties, expressed or implied, concerning the suitability or fitness of any of its products for any particular purpose. It is the\r\nresponsibility of the customer to determine that the product is safe, lawful and technically suitable for the intended use. The disclosure of\r\ninformation herein is not a license to operate under, or a recommendation to infringe any patents.')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('QuickEntries','10')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('RadioBeaconFileCounter','/home/RadioBeacon/FileCounter')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('RadioBeaconFTP_user_name','RadioBeacon ftp server user name')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('RadioBeaconHomeDir','/home/RadioBeacon')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('RadioBeaconStockLocation','BL')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('RadioBraconFTP_server','192.168.2.2')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('RadioBreaconFilePrefix','ORDXX')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('RadionBeaconFTP_user_pass','Radio Beacon remote ftp server password')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('reports_dir','companies/" . $_SESSION['DatabaseName'] . "/EDI_Sent')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('RequirePickingNote','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('RomalpaClause','Ownership will not pass to the buyer until the goods have been paid for in full.')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopAboutUs','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopAdditionalStockLocations','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopAllowBankTransfer','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopAllowCreditCards','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopAllowPayPal','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopAllowSurcharges','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopBankTransferSurcharge','0.0')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopBranchCode','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopContactUs','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopCreditCardBankAccount','1030')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopCreditCardGateway','PayFlowPro')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopCreditCardSurcharge','0.029')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopDebtorNo','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopFreightModule','ShopFreightMethod')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopFreightPolicy','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopManagerEmail','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopMode','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopName','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopPayFlowMerchant','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopPayFlowPassword','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopPayFlowUser','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopPayFlowVendor','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopPayPalBankAccount','1030')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopPaypalCommissionAccount','7220')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopPayPalPassword','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopPayPalProPassword','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopPayPalProSignature','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopPayPalProUser','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopPayPalSignature','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopPayPalSurcharge','0.034')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopPayPalUser','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopPrivacyStatement','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopShowInfoLinks','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopShowLeftCategoryMenu','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopShowLogoAndShopName','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopShowOnlyAvailableItems','0')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopShowQOHColumn','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopShowTopCategoryMenu','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopStockLocations','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopSurchargeStockID','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopSwipeHQAPIKey','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopSwipeHQMerchantID','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopTermsConditions','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShopTitle','Shop Home')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShowStockidOnImages','0')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('ShowValueOnGRN','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('Show_Settled_LastMonth','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('SmtpSetting','0')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('SO_AllowSameItemMultipleTimes','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('StandardCostDecimalPlaces','2')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('TaxAuthorityReferenceName','URA')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('TermsAndConditions','')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('UpdateCurrencyRatesDaily','2016-02-27')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('vtiger_integration','0')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('WeightedAverageCosting','1')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('WikiApp','WackoWiki')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('WikiPath','wiki')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('WorkingDaysWeek','5')";
$SQL[] = "INSERT INTO `config` (`confname`, `confvalue`) VALUES ('YearEnd','12')";
foreach ($SQL as $Query) {
	$Result = DB_query($Query);
}

$SQL = array();
$SQL[] = "INSERT INTO `securitytokens` (`tokenid`, `tokenname`) VALUES (0,'Main Index Page')";
$SQL[] = "INSERT INTO `securitytokens` (`tokenid`, `tokenname`) VALUES (1,'Order Entry/Inquiries customer access only')";
$SQL[] = "INSERT INTO `securitytokens` (`tokenid`, `tokenname`) VALUES (2,'Basic Reports and Inquiries with selection options')";
$SQL[] = "INSERT INTO `securitytokens` (`tokenid`, `tokenname`) VALUES (3,'Credit notes and AR management')";
$SQL[] = "INSERT INTO `securitytokens` (`tokenid`, `tokenname`) VALUES (4,'Purchasing data/PO Entry/Reorder Levels')";
$SQL[] = "INSERT INTO `securitytokens` (`tokenid`, `tokenname`) VALUES (5,'Accounts Payable')";
$SQL[] = "INSERT INTO `securitytokens` (`tokenid`, `tokenname`) VALUES (6,'Petty Cash')";
$SQL[] = "INSERT INTO `securitytokens` (`tokenid`, `tokenname`) VALUES (7,'Bank Reconciliations')";
$SQL[] = "INSERT INTO `securitytokens` (`tokenid`, `tokenname`) VALUES (8,'General ledger reports/inquiries')";
$SQL[] = "INSERT INTO `securitytokens` (`tokenid`, `tokenname`) VALUES (9,'Supplier centre - Supplier access only')";
$SQL[] = "INSERT INTO `securitytokens` (`tokenid`, `tokenname`) VALUES (10,'General Ledger Maintenance, stock valuation & Configuration')";
$SQL[] = "INSERT INTO `securitytokens` (`tokenid`, `tokenname`) VALUES (11,'Inventory Management and Pricing')";
$SQL[] = "INSERT INTO `securitytokens` (`tokenid`, `tokenname`) VALUES (13,'Report Writer')";
$SQL[] = "INSERT INTO `securitytokens` (`tokenid`, `tokenname`) VALUES (14,'Form Designer')";
$SQL[] = "INSERT INTO `securitytokens` (`tokenid`, `tokenname`) VALUES (15,'User Management and System Administration')";
$SQL[] = "INSERT INTO `securitytokens` (`tokenid`, `tokenname`) VALUES (16,'Quality Assurance')";
$SQL[] = "INSERT INTO `securitytokens` (`tokenid`, `tokenname`) VALUES (1000,'User can view and alter sales prices')";
$SQL[] = "INSERT INTO `securitytokens` (`tokenid`, `tokenname`) VALUES (1001,'User can bypass purchasing security and go straight from order to invoice')";
$SQL[] = "INSERT INTO `securitytokens` (`tokenid`, `tokenname`) VALUES (1002,'User can view and alter supplier prices')";
foreach ($SQL as $Query) {
	$Result = DB_query($Query);
}

$SQL = "INSERT INTO `securityroles` (`secroleid`, `secrolename`) VALUES (8,'System Administrator')";
$Result = DB_query($SQL);

$SQL = "INSERT INTO securitygroups (SELECT securityroles.secroleid, securitytokens.tokenid FROM securityroles, securitytokens)";
$Result = DB_query($SQL);

$SQL = array();
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ABCRankingGroups.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ABCRankingMethods.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ABCRunAnalysis.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('AccountGroups.php',10,'Defines the groupings of general ledger accounts')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('AccountSections.php',10,'Defines the sections in the general ledger reports')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('AddCustomerContacts.php',3,'Adds customer contacts')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('AddCustomerNotes.php',3,'Adds notes about customers')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('AddCustomerTypeNotes.php',3,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('AgedControlledInventory.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('AgedDebtors.php',2,'Lists customer account balances in detail or summary in selected currency')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('AgedSuppliers.php',2,'Lists supplier account balances in detail or summary in selected currency')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('AnalysisHorizontalIncome.php',8,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('AnalysisHorizontalPosition.php',8,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Areas.php',3,'Defines the sales areas - all customers must belong to a sales area for the purposes of sales analysis')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('AsteriskImport.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('AuditTrail.php',15,'Shows the activity with SQL statements and who performed the changes')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('AutomaticTranslationDescriptions.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('BackupDatabase.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('BankAccounts.php',10,'Defines the general ledger code for bank accounts and specifies that bank transactions be created for these accounts for the purposes of reconciliation')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('BankAccountUsers.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('BankMatching.php',7,'Allows payments and receipts to be matched off against bank statements')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('BankReconciliation.php',7,'Displays the bank reconciliation for a selected bank account')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('BOMExtendedQty.php',2,'Shows the component requirements to make an item')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('BOMIndented.php',2,'Shows the bill of material indented for each level')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('BOMIndentedReverse.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('BOMInquiry.php',2,'Displays the bill of material with cost information')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('BOMListing.php',2,'Lists the bills of material for a selected range of items')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('BOMs.php',9,'Administers the bills of material for a selected item')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Branding.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('COGSGLPostings.php',10,'Defines the general ledger account to be used for cost of sales entries')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('CollectiveWorkOrderCost.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('CompanyPreferences.php',10,'Defines the settings applicable for the company, including name, address, tax authority reference, whether GL integration used etc.')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('config.distrib.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('config.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ConfirmDispatchControlled_Invoice.php',11,'Specifies the batch references/serial numbers of items dispatched that are being invoiced')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ConfirmDispatch_Invoice.php',2,'Creates sales invoices from entered sales orders based on the quantities dispatched that can be modified')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ContractBOM.php',6,'Creates the item requirements from stock for a contract as part of the contract cost build up')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ContractCosting.php',6,'Shows a contract cost - the components and other non-stock costs issued to the contract')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ContractOtherReqts.php',4,'Creates the other requirements for a contract cost build up')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Contracts.php',6,'Creates or modifies a customer contract costing')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('CopyBOM.php',9,'Allows a bill of material to be copied between items')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('CounterReturns.php',5,'Allows credits and refunds from the default Counter Sale account for an inventory location')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('CounterSales.php',1,'Allows sales to be entered against a cash sale customer account defined in the users location record')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('CreditItemsControlled.php',3,'Specifies the batch references/serial numbers of items being credited back into stock')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('CreditStatus.php',3,'Defines the credit status records. Each customer account is given a credit status from this table. Some credit status records can prohibit invoicing and new orders being entered.')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Credit_Invoice.php',3,'Creates a credit note based on the details of an existing invoice')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Currencies.php',9,'Defines the currencies available. Each customer and supplier must be defined as transacting in one of the currencies defined here.')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('CustEDISetup.php',11,'Allows the set up the customer specified EDI parameters for server, email or ftp.')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('CustItem.php',11,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('CustLoginSetup.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('CustomerAccount.php',1,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('CustomerAllocations.php',3,'Allows customer receipts and credit notes to be allocated to sales invoices')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('CustomerBalancesMovement.php',3,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('CustomerBranches.php',3,'Defines the details of customer branches such as delivery address and contact details - also sales area, representative etc')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('CustomerInquiry.php',1,'Shows the customers account transactions with balances outstanding, links available to drill down to invoice/credit note or email invoices/credit notes')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('CustomerPurchases.php',5,'Shows the purchases a customer has made.')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('CustomerReceipt.php',3,'Entry of both customer receipts against accounts receivable and also general ledger or nominal receipts')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Customers.php',3,'Defines the setup of a customer account, including payment terms, billing address, credit status, currency etc')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('CustomerTransInquiry.php',2,'Lists in html the sequence of customer transactions, invoices, credit notes or receipts by a user entered date range')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('CustomerTypes.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('CustWhereAlloc.php',2,'Shows to which invoices a receipt was allocated to')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('DailyBankTransactions.php',8,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('DailySalesInquiry.php',2,'Shows the daily sales with GP in a calendar format')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Dashboard.php',0,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('DebtorsAtPeriodEnd.php',2,'Shows the debtors control account as at a previous period end - based on system calendar monthly periods')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('DefineWarehouse.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('DeliveryDetails.php',1,'Used during order entry to allow the entry of delivery addresses other than the defaulted branch delivery address and information about carrier/shipping method etc')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Departments.php',1,'Create business departments')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('DiscountCategories.php',11,'Defines the items belonging to a discount category. Discount Categories are used to allow discounts based on quantities across a range of producs')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('DiscountMatrix.php',11,'Defines the rates of discount applicable to discount categories and the customer groupings to which the rates are to apply')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Donors.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('EDIMessageFormat.php',10,'Specifies the EDI message format used by a customer - administrator use only.')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('EDIProcessOrders.php',11,'Processes incoming EDI orders into sales orders')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('EDISendInvoices.php',15,'Processes invoiced EDI customer invoices into EDI messages and sends using the customers preferred method either ftp or email attachments.')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('EDISendInvoices_Reece.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('EmailConfirmation.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('EmailCustStatements.php',3,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('EmailCustTrans.php',2,'Emails selected invoice or credit to the customer')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('EnableBranches.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ExchangeRateTrend.php',2,'Shows the trend in exchange rates as retrieved from ECB')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Factors.php',5,'Defines supplier factor companies')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('favicon.ico',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('FixedAssetCategories.php',11,'Defines the various categories of fixed assets')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('FixedAssetDepreciation.php',10,'Calculates and creates GL transactions to post depreciation for a period')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('FixedAssetItems.php',11,'Allows fixed assets to be defined')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('FixedAssetLocations.php',11,'Allows the locations of fixed assets to be defined')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('FixedAssetRegister.php',11,'Produces a csv, html or pdf report of the fixed assets over a period showing period depreciation, additions and disposals')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('FixedAssetTransfer.php',11,'Allows the fixed asset locations to be changed in bulk')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('FormDesigner.php',14,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('FormMaker.php',1,'Allows running user defined Forms')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('FreightCosts.php',11,'Defines the setup of the freight cost using different shipping methods to different destinations. The system can use this information to calculate applicable freight if the items are defined with the correct kgs and cubic volume')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('FTP_RadioBeacon.php',2,'FTPs sales orders for dispatch to a radio beacon software enabled warehouse dispatching facility')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GeneratePickingList.php',11,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('geocode.php',3,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GeocodeSetup.php',3,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('geocode_genxml_customers.php',3,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('geocode_genxml_suppliers.php',3,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('geo_displaymap_customers.php',3,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('geo_displaymap_suppliers.php',3,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GetStockImage.php',1,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GLAccountCSV.php',8,'Produces a CSV of the GL transactions for a particular range of periods and GL account')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GLAccountInquiry.php',8,'Shows the general ledger transactions for a specified account over a specified range of periods')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GLAccountReport.php',8,'Produces a report of the GL transactions for a particular account')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GLAccounts.php',10,'Defines the general ledger accounts')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GLAccountUsers.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GLAccountUsersCopyAuthority.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GLBalanceSheet.php',8,'Shows the balance sheet for the company as at a specified date')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GLBalanceSheet_new.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GLBudgets.php',10,'Defines GL Budgets')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GLCodesInquiry.php',8,'Shows the list of general ledger codes defined with account names and groupings')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GLJournal.php',10,'Entry of general ledger journals, periods are calculated based on the date entered here')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GLJournalInquiry.php',15,'General Ledger Journal Inquiry')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GLProfit_Loss.php',8,'Shows the profit and loss of the company for the range of periods entered')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GLTagProfit_Loss.php',8,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GLTags.php',10,'Allows GL tags to be defined')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GLTransInquiry.php',8,'Shows the general ledger journal created for the sub ledger transaction specified')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GLTrialBalance.php',8,'Shows the trial balance for the month and the for the period selected together with the budgeted trial balances')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GLTrialBalance_csv.php',8,'Produces a CSV of the Trial Balance for a particular period')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GoodsReceived.php',11,'Entry of items received against purchase orders')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GoodsReceivedButNotInvoiced.php',2,'Shows the list of Goods Received Not Yet Invoiced, both in supplier currency and home currency. Total in home curency should match the GL Account for Goods received not invoiced. Any discrepancy is due to multicurrency errors.')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('GoodsReceivedControlled.php',11,'Entry of the serial numbers or batch references for controlled items received against purchase orders')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('HistoricalTestResults.php',16,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ImportBankTrans.php',11,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ImportBankTransAnalysis.php',11,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ImportSalesPriceList.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('index.php',1,'The main menu from where all functions available to the user are accessed by clicking on the links')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('InitialScripts.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('InternalStockCategoriesByRole.php',15,'Maintains the stock categories to be used as internal for any user security role')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('InternalStockRequest.php',1,'Create an internal stock request')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('InternalStockRequestAuthorisation.php',1,'Authorise internal stock requests')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('InternalStockRequestFulfill.php',1,'Fulfill an internal stock request')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('InventoryPlanning.php',2,'Creates a pdf report showing the last 4 months use of items including as a component of assemblies together with stock quantity on hand, current demand for the item and current quantity on sales order.')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('InventoryPlanningPrefSupplier.php',2,'Produces a report showing the inventory to be ordered by supplier')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('InventoryQuantities.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('InventoryValuation.php',2,'Creates a pdf report showing the value of stock at standard cost for a range of product categories selected')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('JobCards.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('JobScheduler.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Labels.php',15,'Produces item pricing labels in a pdf from a range of selected criteria')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Locations.php',11,'Defines the inventory stocking locations or warehouses')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('LocationUsers.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Logout.php',1,'Shows when the user logs out of KwaMoja')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('logo_server.jpg',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('MailingGroupMaintenance.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('MailInventoryValuation.php',1,'Meant to be run as a scheduled process to email the stock valuation off to a specified person. Creates the same stock valuation report as InventoryValuation.php')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('MailSalesReport.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('MailSalesReport_csv.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('MaintenanceReminders.php',1,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('MaintenanceTasks.php',1,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('MaintenanceUserSchedule.php',1,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ManualContents.php',1,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Manufacturers.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('MaterialsNotUsed.php',4,'Lists the items from Raw Materials Categories not used in any bom (thus not used at all)')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('MenuManager.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ModuleEditor.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('MonthlyBankTransactions.php',8,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('MRP.php',9,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('MRPCalendar.php',9,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('MRPCreateDemands.php',9,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('MRPDemands.php',9,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('MRPDemandTypes.php',9,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('MRPPlannedPurchaseOrders.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('MRPPlannedWorkOrders.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('MRPReport.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('MRPReschedules.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('MRPShortages.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('NoSalesItems.php',2,'Shows the No Selling (worst) items')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('OcKwaMojaToOpenCartDaily.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('OcKwaMojaToOpenCartHourly.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('OcOpenCartToKwaMoja.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('OffersReceived.php',4,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('OrderDetails.php',2,'Shows the detail of a sales order')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('OutstandingGRNs.php',2,'Creates a pdf showing all GRNs for which there has been no purchase invoice matched off against.')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PageSecurity.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PaymentAllocations.php',5,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PaymentMethods.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Payments.php',5,'Entry of bank account payments either against an AP account or a general ledger payment - if the AP-GL link in company preferences is set')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PaymentTerms.php',10,'Defines the payment terms records, these can be expressed as either a number of days credit or a day in the following month. All customers and suppliers must have a corresponding payment term recorded against their account')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PcAssignCashToTab.php',6,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PcAuthorizeCheque.php',6,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PcAuthorizeExpenses.php',6,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PcClaimExpensesFromTab.php',6,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PcExpenses.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PcExpensesTypeTab.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PcReportTab.php',6,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PcTabs.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PcTypeTabs.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFAck.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFBankingSummary.php',3,'Creates a pdf showing the amounts entered as receipts on a specified date together with references for the purposes of banking')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFChequeListing.php',3,'Creates a pdf showing all payments that have been made from a specified bank account over a specified period. This can be emailed to an email account defined in config.php - ie a financial controller')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFCOA.php',0,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFCustomerList.php',2,'Creates a report of the customer and branch information held. This report has options to print only customer branches in a specified sales area and sales person. Additional option allows to list only those customers with activity either under or over a specified amount, since a specified date.')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFCustTransListing.php',3,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFDeliveryDifferences.php',3,'Creates a pdf report listing the delivery differences from what the customer requested as recorded in the order entry. The report calculates a percentage of order fill based on the number of orders filled in full on time')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFDIFOT.php',3,'Produces a pdf showing the delivery in full on time performance')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFFGLabel.php',11,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFGLJournal.php',15,'General Ledger Journal Print')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFGrn.php',2,'Produces a GRN report on the receipt of stock')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFLowGP.php',2,'Creates a pdf report showing the low gross profit sales made in the selected date range. The percentage of gp deemed acceptable can also be entered')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFOrdersInvoiced.php',3,'Produces a pdf of orders invoiced based on selected criteria')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFOrderStatus.php',3,'Reports on sales order status by date range, by stock location and stock category - producing a pdf showing each line items and any quantites delivered')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFPeriodStockTransListing.php',3,'Allows stock transactions of a specific transaction type to be listed over a single day or period range')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFPickingList.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFPriceList.php',2,'Creates a pdf of the price list applicable to a given sales type and customer. Also allows the listing of prices specific to a customer')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFPrintLabel.php',10,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFProdSpec.php',0,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFQALabel.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFQuotation.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFQuotationPortrait.php',2,'Portrait quotation')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFReceipt.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFRemittanceAdvice.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFSellThroughSupportClaim.php',9,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFShipLabel.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFStockCheckComparison.php',2,'Creates a pdf comparing the quantites entered as counted at a given range of locations against the quantity stored as on hand as at the time a stock check was initiated.')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFStockLocTransfer.php',1,'Creates a stock location transfer docket for the selected location transfer reference number')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFStockNegatives.php',1,'Produces a pdf of the negative stocks by location')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFStockTransfer.php',2,'Produces a report for stock transfers')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFSuppTransListing.php',3,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFTestPlan.php',16,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFTopItems.php',2,'Produces a pdf report of the top items sold')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PDFWOPrint.php',11,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PeriodsInquiry.php',2,'Shows a list of all the system defined periods')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PickingLists.php',11,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PickingListsControlled.php',11,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PluginInstall.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PluginUnInstall.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PluginUpload.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('POClearBackOrders.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('POReport.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PO_AuthorisationLevels.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PO_AuthoriseMyOrders.php',4,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PO_Chk_ShiptRef_JobRef.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PO_Header.php',4,'Entry of a purchase order header record - date, references buyer etc')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PO_Items.php',4,'Entry of a purchase order items - allows entry of items with lookup of currency cost from Purchasing Data previously entered also allows entry of nominal items against a general ledger code if the AP is integrated to the GL')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PO_OrderDetails.php',2,'Purchase order inquiry shows the quantity received and invoiced of purchase order items as well as the header information')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PO_PDFPurchOrder.php',2,'Creates a pdf of the selected purchase order for printing or email to one of the supplier contacts entered')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PO_SelectOSPurchOrder.php',2,'Shows the outstanding purchase orders for selecting with links to receive or modify the purchase order header and items')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PO_SelectPurchOrder.php',2,'Allows selection of any purchase order with links to the inquiry')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PriceMatrix.php',11,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Prices.php',9,'Entry of prices for a selected item also allows selection of sales type and currency for the price')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PricesBasedOnMarkUp.php',11,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PricesByCost.php',11,'Allows prices to be updated based on cost')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Prices_Customer.php',11,'Entry of prices for a selected item and selected customer/branch. The currency and sales type is defaulted from the customer\'s record')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PrintCheque.php',5,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PrintCustOrder.php',2,'Creates a pdf of the dispatch note - by default this is expected to be on two part pre-printed stationery to allow pickers to note discrepancies for the confirmer to update the dispatch at the time of invoicing')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PrintCustOrder_generic.php',2,'Creates two copies of a laser printed dispatch note - both copies need to be written on by the pickers with any discrepancies to advise customer of any shortfall and on the office copy to ensure the correct quantites are invoiced')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PrintCustStatements.php',2,'Creates a pdf for the customer statements in the selected range')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PrintCustTrans.php',1,'Creates either a html invoice or credit note or a pdf. A range of invoices or credit notes can be selected also.')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PrintCustTransPortrait.php',1,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PrintSalesOrder_generic.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PrintWOItemSlip.php',4,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlabout.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlALD.php',10,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlAuthoriseLoans.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlBankDetails.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlBP.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlCreatePayroll.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlCurrencies.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlDepartments.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlEditPayroll.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlEmployeeMaster.php',5,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlEmployers.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlEmploymentStatus.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlGP.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlLoanFile.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlLoanPayments.php',10,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlLoanRepayments.php',10,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlLoanTable.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlMsgBox.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlnssf.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlOTFile.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlOthIncome.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlOthIncTable.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlOvertime.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlPaye.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlPayPeriod.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlPositions.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlRegTimeEntry.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlReNSSFPremium.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlRepBankTrans.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlRepBPPremium.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlRepCashTrans.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlRepGROSSPAYPremium.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlRepPayrollRegister.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlRepPayrollRegYTD.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlRepPaySlip.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlRepTax.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlRepTaxYTD.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlSC.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlSelectDeduction.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlSelectEmployee.php',10,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlSelectLoan.php',10,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlSelectOT.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlSelectOthIncome.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlSelectPayroll.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlSelectPayTrans.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlSelectRT.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlSelectTaxStatus.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlSelectTD.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlSSC.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlTardiness.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlTax.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlTaxAuthority.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlTaxAuthorityRates.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('prlTaxStatus.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ProductSpecs.php',16,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ProjectBOM.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ProjectCosting.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ProjectOtherReqts.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Projects.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PurchaseByPrefSupplier.php',4,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('PurchData.php',4,'Entry of supplier purchasing data, the suppliers part reference and the suppliers currency cost of the item')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('QATests.php',16,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('QuickInvoice.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('RecurringSalesOrders.php',1,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('RecurringSalesOrdersProcess.php',1,'Process Recurring Sales Orders')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('RegularPaymentsProcess.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('RegularPaymentsSetup.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('RelatedItemsUpdate.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ReorderLevel.php',2,'Allows reorder levels of inventory to be updated')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ReorderLevelLocation.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ReportBug.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ReportCreator.php',13,'Report Writer and Form Creator script that creates templates for user defined reports and forms')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ReportMaker.php',1,'Produces reports from the report writer templates created')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('reportwriter/admin/ReportCreator.php',15,'Report Writer')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('report_runner.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ReprintGRN.php',11,'Allows selection of a goods received batch for reprinting the goods received note given a purchase order number')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('RetainedEarningsReconciliation.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ReverseGRN.php',11,'Reverses the entry of goods received - creating stock movements back out and necessary general ledger journals to effect the reversal')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('RevisionTranslations.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('RunScheduledJobs.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SalesAnalReptCols.php',2,'Entry of the definition of a sales analysis report\'s columns.')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SalesAnalRepts.php',2,'Entry of the definition of a sales analysis report headers')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SalesAnalysis_UserDefined.php',2,'Creates a pdf of a selected user defined sales analysis report')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SalesByTypePeriodInquiry.php',2,'Shows sales for a selected date range by sales type/price list')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SalesCategories.php',11,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SalesCategoryDescriptions.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SalesCategoryPeriodInquiry.php',2,'Shows sales for a selected date range by stock category')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SalesGLPostings.php',10,'Defines the general ledger accounts used to post sales to based on product categories and sales areas')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SalesGraph.php',6,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SalesInquiry.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SalesPeople.php',3,'Defines the sales people of the business')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SalesTopCustomersInquiry.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SalesTopItemsInquiry.php',2,'Shows the top item sales for a selected date range')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SalesTypes.php',15,'Defines the sales types - prices are held against sales types they can be considered price lists. Sales analysis records are held by sales type too.')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('scriptsAccess.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SearchCustomers.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SecurityTokens.php',15,'Administration of security tokens')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SelectAsset.php',2,'Allows a fixed asset to be selected for modification or viewing')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SelectCompletedOrder.php',1,'Allows the selection of completed sales orders for inquiries - choices to select by item code or customer')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SelectContract.php',6,'Allows a contract costing to be selected for modification or viewing')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SelectCreditItems.php',3,'Entry of credit notes from scratch, selecting the items in either quick entry mode or searching for them manually')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SelectCustomer.php',2,'Selection of customer - from where all customer related maintenance, transactions and inquiries start')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SelectGLAccount.php',8,'Selection of general ledger account from where all general ledger account maintenance, or inquiries are initiated')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SelectOrderItems.php',1,'Entry of sales order items with both quick entry and part search functions')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SelectPickingLists.php',11,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SelectProduct.php',2,'Selection of items. All item maintenance, transactions and inquiries start with this script')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SelectProject.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SelectQASamples.php',16,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SelectRecurringSalesOrder.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SelectSalesOrder.php',2,'Selects a sales order irrespective of completed or not for inquiries')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SelectSupplier.php',2,'Selects a supplier. A supplier is required to be selected before any AP transactions and before any maintenance or inquiry of the supplier')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SelectWorkOrder.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SellThroughSupport.php',9,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ShipmentCosting.php',11,'Shows the costing of a shipment with all the items invoice values and any shipment costs apportioned. Updating the shipment has an option to update standard costs of all items on the shipment and create any general ledger variance journals')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Shipments.php',11,'Entry of shipments from outstanding purchase orders for a selected supplier - changes in the delivery date will cascade into the different purchase orders on the shipment')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Shippers.php',15,'Defines the shipping methods available. Each customer branch has a default shipping method associated with it which must match a record from this table')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ShiptsList.php',2,'Shows a list of all the open shipments for a selected supplier. Linked from POItems.php')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Shipt_Select.php',11,'Selection of a shipment for displaying and modification or updating')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('ShopParameters.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SMTPServer.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SpecialOrder.php',4,'Allows for a sales order to be created and an indent order to be created on a supplier for a one off item that may never be purchased again. A dummy part is created based on the description and cost details given.')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockAdjustments.php',11,'Entry of quantity corrections to stocks in a selected location.')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockAdjustmentsControlled.php',11,'Entry of batch references or serial numbers on controlled stock items being adjusted')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockCategories.php',11,'Defines the stock categories. All items must refer to one of these categories. The category record also allows the specification of the general ledger codes where stock items are to be posted - the balance sheet account and the profit and loss effect of any adjustments and the profit and loss effect of any price variances')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockCategorySalesInquiry.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockCheck.php',2,'Allows creation of a stock check file - copying the current quantites in stock for later comparison to the entered counts. Also produces a pdf for the count sheets.')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockClone.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockCostUpdate.php',9,'Allows update of the standard cost of items producing general ledger journals if the company preferences stock GL interface is active')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockCounts.php',2,'Allows entry of stock counts')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockDispatch.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockLocMovements.php',2,'Inquiry shows the Movements of all stock items for a specified location')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockLocStatus.php',2,'Shows the stock on hand together with outstanding sales orders and outstanding purchase orders by stock location for all items in the selected stock category')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockLocTransfer.php',11,'Entry of a bulk stock location transfer for many parts from one location to another.')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockLocTransferReceive.php',11,'Effects the transfer and creates the stock movements for a bulk stock location transfer initiated from StockLocTransfer.php')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockMovements.php',2,'Shows a list of all the stock movements for a selected item and stock location including the price at which they were sold in local currency and the price at which they were purchased for in local currency')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockQties_csv.php',5,'Makes a comma separated values (CSV)file of the stock item codes and quantities')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockQuantityByDate.php',2,'Shows the stock on hand for each item at a selected location and stock category as at a specified date')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockReorderLevel.php',4,'Entry and review of the re-order level of items by stocking location')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Stocks.php',11,'Defines an item - maintenance and addition of new parts')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockSerialItemResearch.php',3,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockSerialItems.php',2,'Shows a list of the serial numbers or the batch references and quantities of controlled items. This inquiry is linked from the stock status inquiry')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockStatus.php',2,'Shows the stock on hand together with outstanding sales orders and outstanding purchase orders by stock location for a selected part. Has a link to show the serial numbers in stock at the location selected if the item is controlled')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockTransferControlled.php',11,'Entry of serial numbers/batch references for controlled items being received on a stock transfer. The script is used by both bulk transfers and point to point transfers')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockTransfers.php',11,'Entry of point to point stock location transfers of a single part')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockTypes.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockUsage.php',2,'Inquiry showing the quantity of stock used by period calculated from the sum of the stock movements over that period - by item and stock location. Also available over all locations')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('StockUsageGraph.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SuppContractChgs.php',5,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SuppCreditGRNs.php',5,'Entry of a supplier credit notes (debit notes) against existing GRN which have already been matched in full or in part')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SuppFixedAssetChgs.php',5,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SuppInvGRNs.php',5,'Entry of supplier invoices against goods received')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SupplierAllocations.php',5,'Entry of allocations of supplier payments and credit notes to invoices')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SupplierBalsAtPeriodEnd.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SupplierContacts.php',5,'Entry of supplier contacts and contact details including email addresses')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SupplierCredit.php',5,'Entry of supplier credit notes (debit notes)')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SupplierGRNAndInvoiceInquiry.php',5,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SupplierInquiry.php',2,'Inquiry showing invoices, credit notes and payments made to suppliers together with the amounts outstanding')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SupplierInvoice.php',5,'Entry of supplier invoices')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SupplierPriceList.php',4,'Maintain Supplier Price Lists')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Suppliers.php',5,'Entry of new suppliers and maintenance of existing suppliers')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SupplierTenderCreate.php',4,'Create or Edit tenders')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SupplierTenders.php',9,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SupplierTransInquiry.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SupplierTypes.php',4,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SuppLoginSetup.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SuppPaymentRun.php',5,'Automatic creation of payment records based on calculated amounts due from AP invoices entered')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SuppPriceList.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SuppShiptChgs.php',5,'Entry of supplier invoices against shipments as charges against a shipment')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SuppTransGLAnalysis.php',5,'Entry of supplier invoices against general ledger codes')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SuppWhereAlloc.php',3,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('SystemParameters.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Tax.php',2,'Creates a report of the ad-valoerm tax - GST/VAT - for the period selected from accounts payable and accounts receivable data')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('TaxAuthorities.php',15,'Entry of tax authorities - the state intitutions that charge tax')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('TaxAuthorityRates.php',11,'Entry of the rates of tax applicable to the tax authority depending on the item tax level')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('TaxCategories.php',15,'Allows for categories of items to be defined that might have different tax rates applied to them')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('TaxGroups.php',15,'Allows for taxes to be grouped together where multiple taxes might apply on sale or purchase of items')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('TaxProvinces.php',15,'Allows for inventory locations to be defined so that tax applicable from sales in different provinces can be dealt with')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('TestPlanResults.php',16,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('TopItems.php',2,'Shows the top selling items')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('UnitsOfMeasure.php',15,'Allows for units of measure to be defined')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('UpdateFavourites.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('UpgradeDatabase.php',15,'Allows for the database to be automatically upgraded based on currently recorded DBUpgradeNumber config option')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('UploadPriceList.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('UserBankAccounts.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('UserGLAccounts.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('UserLocations.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('UserSettings.php',1,'Allows the user to change system wide defaults for the theme - appearance, the number of records to show in searches and the language to display messages in')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('WhereUsedInquiry.php',2,'Inquiry showing where an item is used ie all the parents where the item is a component of')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('WOCanBeProducedNow.php',4,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('WorkCentres.php',9,'Defines the various centres of work within a manufacturing company. Also the overhead and labour rates applicable to the work centre and its standard capacity')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('WorkOrderCosting.php',11,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('WorkOrderEntry.php',10,'Entry of new work orders')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('WorkOrderIssue.php',11,'Issue of materials to a work order')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('WorkOrderReceive.php',11,'Allows for receiving of works orders')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('WorkOrderStatus.php',11,'Shows the status of works orders')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('WOSerialNos.php',10,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('WWW_Access.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('WWW_Users.php',15,'Entry of users and security settings of users')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_AutoCustomerAllocations.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_BottomUpCosts.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_ChangeBranchCode.php',15,'Utility to change the branch code of a customer that cascades the change through all the necessary tables')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_ChangeCustomerCode.php',15,'Utility to change a customer code that cascades the change through all the necessary tables')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_ChangeGLAccountCode.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_ChangeLocationCode.php',15,'Change a locations code and in all tables where the old code was used to the new code')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_ChangeStockCategory.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_ChangeStockCode.php',15,'Utility to change an item code that cascades the change through all the necessary tables')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_ChangeSupplierCode.php',15,'Script to change a supplier code accross all tables necessary')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_CheckAllocationsFrom.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_CheckAllocs.php',2,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_CheckDebtorsControl.php',15,'Inquiry that shows the total local currency (functional currency) balance of all customer accounts to reconcile with the general ledger debtors account')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_CheckGLTransBalance.php',15,'Checks all GL transactions balance and reports problem ones')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_CreateChartDetails.php',9,'Utility page to create chart detail records for all general ledger accounts and periods created - needs expert assistance in use')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_CreateCompany.php',15,'Utility to insert company number 1 if not already there - actually only company 1 is used - the system is not multi-company')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_CreateCompanyTemplateFile.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_CurrencyDebtorsBalances.php',15,'Inquiry that shows the total foreign currency together with the total local currency (functional currency) balances of all customer accounts to reconcile with the general ledger debtors account')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_CurrencySuppliersBalances.php',15,'Inquiry that shows the total foreign currency amounts and also the local currency (functional currency) balances of all supplier accounts to reconcile with the general ledger creditors account')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_DataExport.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_DeleteCreditNote.php',15,'Utility to reverse a customer credit note - a desperate measure that should not be used except in extreme circumstances')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_DeleteInvoice.php',15,'Utility to reverse a customer invoice - a desperate measure that should not be used except in extreme circumstances')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_DeleteOldPrices.php',15,'Deletes all old prices')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_DeleteSalesTransActions.php',15,'Utility to delete all sales transactions, sales analysis the lot! Extreme care required!!!')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_DescribeTable.php',11,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_ImportChartOfAccounts.php',11,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_ImportCustBranch.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_ImportDebtors.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_ImportFixedAssets.php',15,'Allow fixed assets to be imported from a csv')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_ImportGLAccountGroups.php',11,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_ImportGLAccountSections.php',11,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_ImportGLTransactions.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_ImportPartCodes.php',11,'Allows inventory items to be imported from a csv')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_ImportStocks.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_ImportSuppliers.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_index.php',15,'Utility menu page')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_ItemsWithoutPicture.php',15,'Shows the list of curent items without picture in KwaMoja')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_MakeLocUsers.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_MakeNewCompany.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_MakeStockLocns.php',15,'Utility to make LocStock records for all items and locations if not already set up.')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_poAddLanguage.php',15,'Allows a new language po file to be created')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_poAdmin.php',15,'Allows for a gettext language po file to be administered')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_poEditLangHeader.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_poEditLangModule.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_poEditLangRemaining.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_poRebuildDefault.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_PriceChanges.php',15,'Utility to make bulk pricing alterations to selected sales type price lists or selected customer prices only')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_ReApplyCostToSA.php',15,'Utility to allow the sales analysis table to be updated with the latest cost information - the sales analysis takes the cost at the time the sale was made to reconcile with the enteries made in the gl.')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_RebuildSalesAnalysis.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_RePostGLFromPeriod.php',15,'Utility to repost all general ledger transaction commencing from a specified period. This can take some time in busy environments. Normally GL transactions are posted automatically each time a trial balance or profit and loss account is run')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_ReverseSuppPaymentRun.php',15,'Utility to reverse an entire Supplier payment run')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_SalesIntegrityCheck.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_UpdateChartDetailsBFwd.php',15,'Utility to recalculate the ChartDetails table B/Fwd balances - extreme care!!')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_UpdateItemCosts.php',3,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_UpdateSalesAnalysisWithLatestCustomerData.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_Upgrade3.10.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_UpgradeDatabase.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_Upgrade_3.01-3.02.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_Upgrade_3.04-3.05.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_Upgrade_3.05-3.06.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_Upgrade_3.07-3.08.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_Upgrade_3.08-3.09.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_Upgrade_3.09-3.10.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_Upgrade_3.10-3.11.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_Upgrade_3.11-4.00.php',15,'')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_UploadForm.php',15,'Utility to upload a file to a remote server')";
$SQL[] = "INSERT INTO `scripts` (`script`, `pagesecurity`, `description`) VALUES ('Z_UploadResult.php',15,'Utility to upload a file to a remote server')";
foreach ($SQL as $Query) {
	$Result = DB_query($Query);
}

$SQL = array();
$SQL[] = "INSERT INTO `modules` (`secroleid`, `modulelink`, `reportlink`, `modulename`, `sequence`) VALUES (8,'AP','ap','Payables',3)";
$SQL[] = "INSERT INTO `modules` (`secroleid`, `modulelink`, `reportlink`, `modulename`, `sequence`) VALUES (8,'AR','ar','Receivables',2)";
$SQL[] = "INSERT INTO `modules` (`secroleid`, `modulelink`, `reportlink`, `modulename`, `sequence`) VALUES (8,'FA','fa','Asset Manager',11)";
$SQL[] = "INSERT INTO `modules` (`secroleid`, `modulelink`, `reportlink`, `modulename`, `sequence`) VALUES (8,'GL','gl','General Ledger',7)";
$SQL[] = "INSERT INTO `modules` (`secroleid`, `modulelink`, `reportlink`, `modulename`, `sequence`) VALUES (8,'HR','hr','Human Resources',9)";
$SQL[] = "INSERT INTO `modules` (`secroleid`, `modulelink`, `reportlink`, `modulename`, `sequence`) VALUES (8,'manuf','man','Manufacturing',6)";
$SQL[] = "INSERT INTO `modules` (`secroleid`, `modulelink`, `reportlink`, `modulename`, `sequence`) VALUES (8,'orders','ord','Sales',1)";
$SQL[] = "INSERT INTO `modules` (`secroleid`, `modulelink`, `reportlink`, `modulename`, `sequence`) VALUES (8,'PC','pc','Petty Cash',12)";
$SQL[] = "INSERT INTO `modules` (`secroleid`, `modulelink`, `reportlink`, `modulename`, `sequence`) VALUES (8,'pjct','pjct','Project Accounting',10)";
$SQL[] = "INSERT INTO `modules` (`secroleid`, `modulelink`, `reportlink`, `modulename`, `sequence`) VALUES (8,'PO','prch','Purchases',4)";
$SQL[] = "INSERT INTO `modules` (`secroleid`, `modulelink`, `reportlink`, `modulename`, `sequence`) VALUES (8,'qa','qa','Quality Assurance',8)";
$SQL[] = "INSERT INTO `modules` (`secroleid`, `modulelink`, `reportlink`, `modulename`, `sequence`) VALUES (8,'stock','inv','Inventory',5)";
$SQL[] = "INSERT INTO `modules` (`secroleid`, `modulelink`, `reportlink`, `modulename`, `sequence`) VALUES (8,'system','sys','Setup',13)";
$SQL[] = "INSERT INTO `modules` (`secroleid`, `modulelink`, `reportlink`, `modulename`, `sequence`) VALUES (8,'Utilities','util','Utilities',14)";
foreach ($SQL as $Query) {
	$Result = DB_query($Query);
}

$SQL = array();
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AP','Maintenance','Add Supplier','/Suppliers.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AP','Maintenance','Maintain Factor Companies','/Factors.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AP','Maintenance','Select Supplier','/SelectSupplier.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AP','Reports','Aged Supplier Report','/AgedSuppliers.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AP','Reports','List Daily Transactions','/PDFSuppTransListing.php',6)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AP','Reports','Outstanding GRNs Report','/OutstandingGRNs.php',4)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AP','Reports','Payment Run Report','/SuppPaymentRun.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AP','Reports','Remittance Advices','/PDFRemittanceAdvice.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AP','Reports','Supplier Balances At A Prior Month End','/SupplierBalsAtPeriodEnd.php',5)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AP','Reports','Supplier Transaction Inquiries','/SupplierTransInquiry.php',7)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AP','Reports','Where Allocated Inquiry','/SuppWhereAlloc.php',8)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AP','Transactions','Select Supplier','/SelectSupplier.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AP','Transactions','Supplier Allocations','/SupplierAllocations.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AR','Maintenance','Add Customer','/Customers.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AR','Maintenance','Enable CustomerBranches','/EnableBranches.php',8)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AR','Maintenance','Select Customer','/SelectCustomer.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AR','Reports','Aged Customer Balances/Overdues Report','/AgedDebtors.php',5)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AR','Reports','Customer Activity and Balances','/CustomerBalancesMovement.php',12)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AR','Reports','Customer Listing By Area/Salesperson','/PDFCustomerList.php',8)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AR','Reports','Customer Transaction Inquiries','/CustomerTransInquiry.php',11)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AR','Reports','Debtor Balances At A Prior Month End','/DebtorsAtPeriodEnd.php',7)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AR','Reports','List Daily Transactions','/PDFCustTransListing.php',10)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AR','Reports','Print Invoices or Credit Notes','/PrintCustTrans.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AR','Reports','Print Statements','/PrintCustStatements.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AR','Reports','Re-Print A Deposit Listing','/PDFBankingSummary.php',6)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AR','Reports','Sales Analysis Reports','/SalesAnalRepts.php',4)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AR','Reports','Sales Graphs','/SalesGraph.php',9)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AR','Reports','Where Allocated Inquiry','/CustWhereAlloc.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AR','Transactions','Allocate Receipts or Credit Notes','/CustomerAllocations.php',4)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AR','Transactions','Create A Credit Note','/SelectCreditItems.php?NewCredit=Yes',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AR','Transactions','Enter Receipts','/CustomerReceipt.php?NewReceipt=Yes&amp;Type=Customer',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'AR','Transactions','Select Order to Invoice','/SelectSalesOrder.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'FA','Maintenance','Add or Maintain Asset Locations','/FixedAssetLocations.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'FA','Maintenance','Asset Categories Maintenance','/FixedAssetCategories.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'FA','Maintenance','Maintenance Tasks','/MaintenanceTasks.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'FA','Reports','Asset Register','/FixedAssetRegister.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'FA','Reports','Maintenance Reminder Emails','/MaintenanceReminders.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'FA','Reports','My Maintenance Schedule','/MaintenanceUserSchedule.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'FA','Transactions','Add a new Asset','/FixedAssetItems.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'FA','Transactions','Change Asset Location','/FixedAssetTransfer.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'FA','Transactions','Depreciation Journal','/FixedAssetDepreciation.php',4)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'FA','Transactions','Select an Asset','/SelectAsset.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Maintenance','Account Groups','/AccountGroups.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Maintenance','Account Sections','/AccountSections.php',4)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Maintenance','Copy Authority GL Accounts from user A to B','/GLAccountUsersCopyAuthority.php',7)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Maintenance','GL Account','/GLAccounts.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Maintenance','GL Accounts Authorised Users Maintenance','/GLAccountUsers.php',6)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Maintenance','GL Budgets','/GLBudgets.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Maintenance','GL Tags','/GLTags.php',5)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Maintenance','Set up a RegularPayment','/RegularPaymentsSetup.php',10)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Maintenance','User Authorised GL Accounts Maintenance','/UserGLAccounts.php',7)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Maintenance','User Authorized Bank Accounts','/UserBankAccounts.php',9)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Reports','Account Inquiry','/SelectGLAccount.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Reports','Account Listing','/GLAccountReport.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Reports','Account Listing to CSV File','/GLAccountCSV.php',4)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Reports','Balance Sheet','/GLBalanceSheet.php',11)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Reports','Bank Account Reconciliation Statement','/BankReconciliation.php',6)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Reports','Bank Transactions Inquiry','/DailyBankTransactions.php',8)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Reports','Cheque Payments Listing','/PDFChequeListing.php',7)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Reports','General Ledger Journal Inquiry','/GLJournalInquiry.php',5)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Reports','Horizontal Analysis of Statement of Comprehensive Income','/AnalysisHorizontalIncome.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Reports','Horizontal analysis of statement of financial position','/AnalysisHorizontalPosition.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Reports','Monthly Bank Inquiry','/MonthlyBankTransactions.php',9)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Reports','Profit and Loss Statement','/GLProfit_Loss.php',10)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Reports','Tag Reports','/GLTagProfit_Loss.php',12)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Reports','Tax Reports','/Tax.php',13)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Reports','Trial Balance','/GLTrialBalance.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Transactions','Bank Account Payments Entry','/Payments.php?NewPayment=Yes',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Transactions','Bank Account Payments Matching','/BankMatching.php?Type=Payments',4)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Transactions','Bank Account Receipts Entry','/CustomerReceipt.php?NewReceipt=Yes&amp;Type=GL',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Transactions','Bank Account Receipts Matching','/BankMatching.php?Type=Receipts',5)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Transactions','Import Bank Transactions','/ImportBankTrans.php',5)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'GL','Transactions','Journal Entry','/GLJournal.php?NewJournal=Yes',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'HR','Maintenance','Add/Update Employees Record','/prlSelectEmployee.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'HR','Maintenance','Add/Update Loan Types','/prlLoanTable.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'HR','Maintenance','Maintain Employment Statuses','/prlEmploymentStatus.php',5)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'HR','Maintenance','Maintain Pay Periods','/prlPayPeriod.php',5)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'HR','Maintenance','Maintain Tax Status','/prlTaxStatus.php',4)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'HR','Maintenance','Review Employee Loans','/prlSelectLoan.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'HR','Transactions','Add/Update an Employee Loan','/prlALD.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'HR','Transactions','Authorise Employee Loans','/prlAuthoriseLoans.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'HR','Transactions','Employee Loan Repayments','/prlLoanRepayments.php',4)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'HR','Transactions','Issue Employee Loans','/prlLoanPayments.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'manuf','Maintenance','Auto Create Master Schedule','/MRPCreateDemands.php',5)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'manuf','Maintenance','Bills Of Material','/BOMs.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'manuf','Maintenance','Copy a Bill Of Materials Between Items','/CopyBOM.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'manuf','Maintenance','Master Schedule','/MRPDemands.php',4)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'manuf','Maintenance','MRP Calculation','/MRP.php',6)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'manuf','Maintenance','Multiple Work Orders Total Cost Inquiry','/CollectiveWorkOrderCost.php',7)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'manuf','Maintenance','Work Centre','/WorkCentres.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'manuf','Reports','Bill Of Material Listing','/BOMListing.php',4)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'manuf','Reports','Costed Bill Of Material Inquiry','/BOMInquiry.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'manuf','Reports','Indented Bill Of Material Listing','/BOMIndented.php',5)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'manuf','Reports','Indented Where Used Listing','/BOMIndentedReverse.php',8)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'manuf','Reports','List Components Required','/BOMExtendedQty.php',6)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'manuf','Reports','List Materials Not Used anywhere','/MaterialsNotUsed.php',7)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'manuf','Reports','MRP','/MRPReport.php',9)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'manuf','Reports','MRP Reschedules Required','/MRPReschedules.php',13)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'manuf','Reports','MRP Shortages','/MRPShortages.php',10)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'manuf','Reports','MRP Suggested Purchase Orders','/MRPPlannedPurchaseOrders.php',11)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'manuf','Reports','MRP Suggested Work Orders','/MRPPlannedWorkOrders.php',12)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'manuf','Reports','Select A Work Order','/SelectWorkOrder.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'manuf','Reports','Where Used Inquiry','/WhereUsedInquiry.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'manuf','Reports','WO Items ready to produce','/WOCanBeProducedNow.php',15)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'manuf','Transactions','Select A Work Order','/SelectWorkOrder.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'manuf','Transactions','Work Order Entry','/WorkOrderEntry.php?New=True',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Maintenance','Create Contract','/Contracts.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Maintenance','Import Sales Prices From CSV File','/ImportSalesPriceList.php',4)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Maintenance','Select Contract','/SelectContract.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Maintenance','Sell Through Support Deals','/SellThroughSupport.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Reports','Daily Sales Inquiry','/DailySalesInquiry.php',5)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Reports','Delivery In Full On Time (DIFOT) Report','/PDFDIFOT.php',10)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Reports','Order Delivery Differences Report','/PDFDeliveryDifferences.php',9)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Reports','Order Inquiry','/SelectCompletedOrder.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Reports','Order Status Report','/PDFOrderStatus.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Reports','Orders Invoiced Reports','/PDFOrdersInvoiced.php',4)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Reports','Print Price Lists','/PDFPriceList.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Reports','Sales By Category By Item Inquiry','/StockCategorySalesInquiry.php',6)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Reports','Sales By Category Inquiry','/SalesCategoryPeriodInquiry.php',7)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Reports','Sales By Sales Type Inquiry','/SalesByTypePeriodInquiry.php',6)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Reports','Sales Order Detail Or Summary Inquiries','/SalesInquiry.php',11)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Reports','Sales With Low Gross Profit Report','/PDFLowGP.php',14)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Reports','Sell Through Support Claims Report','/PDFSellThroughSupportClaim.php',15)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Reports','Top Customers Inquiry','/SalesTopCustomersInquiry.php',16)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Reports','Top Sales Items Report','/TopItems.php',12)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Reports','Top Sellers Inquiry','/SalesTopItemsInquiry.php',8)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Reports','Worst Sales Items Report','/NoSalesItems.php',13)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Transactions','Enter An Order or Quotation','/SelectOrderItems.php?NewOrder=Yes',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Transactions','Enter Counter Returns','/CounterReturns.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Transactions','Enter Counter Sales','/CounterSales.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Transactions','Generate/Print Picking Lists','/GeneratePickingList.php',6)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Transactions','Import Asterisk Files','/AsteriskImport.php',15)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Transactions','Maintain Picking Lists','/SelectPickingLists.php',7)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Transactions','Outstanding Sales Orders/Quotations','/SelectSalesOrder.php',5)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Transactions','Process Recurring Orders','/RecurringSalesOrdersProcess.php',8)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Transactions','Recurring Order Template','/SelectRecurringSalesOrder.php',7)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Transactions','Special Order','/SpecialOrder.php',6)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Transactions','Synchronise KwaMoja to OpenCart Daily','/OcKwaMojaToOpenCartDaily.php',13)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Transactions','Synchronise KwaMoja to OpenCart Hourly','/OcKwaMojaToOpenCartHourly.php',12)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'orders','Transactions','Synchronise OpenCart to KwaMoja','/OcOpenCartToKwaMoja.php',11)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'PC','Maintenance','Expenses for Type of PC Tab','/PcExpensesTypeTab.php',4)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'PC','Maintenance','PC Expenses','/PcExpenses.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'PC','Maintenance','PC Tabs','/PcTabs.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'PC','Maintenance','Types of PC Tabs','/PcTypeTabs.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'PC','Reports','PC Tab General Report','/PcReportTab.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'PC','Transactions','Assign Cash to PC Tab','/PcAssignCashToTab.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'PC','Transactions','Cash Authorisation','/PcAuthorizeCheque.php',4)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'PC','Transactions','Claim Expenses From PC Tab','/PcClaimExpensesFromTab.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'PC','Transactions','Expenses Authorisation','/PcAuthorizeExpenses.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'pjct','Maintenance','Donor Maintenance','/Donors.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'pjct','Transactions','Create New Project','/Projects.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'pjct','Transactions','Select a Project','/SelectProject.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'PO','Maintenance','Clear Orders with Quantity on Back Orders','/POClearBackOrders.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'PO','Maintenance','Maintain Supplier Price Lists','/SupplierPriceList.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'PO','Reports','Purchase Order Detail Or Summary Inquiries','/POReport.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'PO','Reports','Purchase Order Inquiry','/PO_SelectPurchOrder.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'PO','Reports','Supplier Price List','/SuppPriceList.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'PO','Transactions','Add Purchase Order','/PO_Header.php?NewOrder=Yes',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'PO','Transactions','Create a New Tender','/SupplierTenderCreate.php?New=Yes',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'PO','Transactions','Create a PO based on the preferred supplier','/PurchaseByPrefSupplier.php',9)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'PO','Transactions','Edit Existing Tenders','/SupplierTenderCreate.php?Edit=Yes',4)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'PO','Transactions','Orders to Authorise','/PO_AuthoriseMyOrders.php',6)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'PO','Transactions','Process Tenders and Offers','/OffersReceived.php',5)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'PO','Transactions','Purchase Orders','/PO_SelectOSPurchOrder.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'PO','Transactions','Select A Shipment','/Shipt_Select.php',8)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'PO','Transactions','Shipment Entry','/SelectSupplier.php',7)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'qa','Maintenance','Product Specifications','/ProductSpecs.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'qa','Maintenance','Quality Tests Maintenance','/QATests.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'qa','Reports','Historical QA Test Results','/HistoricalTestResults.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'qa','Reports','Print Certificate of Analysis','/PDFCOA.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'qa','Reports','Print Product Specification','/PDFProdSpec.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'qa','Transactions','QA Samples and Test Results','/SelectQASamples.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Maintenance','ABC Ranking Groups','/ABCRankingGroups.php',9)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Maintenance','ABC Ranking Methods','/ABCRankingMethods.php',8)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Maintenance','Add A New Item','/Stocks.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Maintenance','Add or Update Prices Based On Costs','/PricesBasedOnMarkUp.php',4)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Maintenance','Brands Maintenance','/Manufacturers.php',11)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Maintenance','Reorder Level By Category/Location','/ReorderLevelLocation.php',7)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Maintenance','Run ABC Ranking Analysis','/ABCRunAnalysis.php',10)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Maintenance','Sales Category Maintenance','/SalesCategories.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Maintenance','Select An Item','/SelectProduct.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Maintenance','Translated Descriptions Revision','/RevisionTranslations.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Maintenance','Upload new prices from csv file','/UploadPriceList.php',6)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Maintenance','View or Update Prices Based On Costs','/PricesByCost.php',5)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Reports','Aged Controlled Inventory Report','/AgedControlledInventory.php',15)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Reports','All Inventory Movements By Location/Date','/StockLocMovements.php',17)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Reports','Compare Counts Vs Stock Check Data','/PDFStockCheckComparison.php',16)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Reports','Historical Stock Quantity By Location/Category','/StockQuantityByDate.php',19)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Reports','Inventory Item Movements','/StockMovements.php',4)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Reports','Inventory Item Status','/StockStatus.php',5)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Reports','Inventory Item Usage','/StockUsage.php',6)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Reports','Inventory Planning Based On Preferred Supplier Data','/InventoryPlanningPrefSupplier.php',13)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Reports','Inventory Planning Report','/InventoryPlanning.php',12)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Reports','Inventory Quantities','/InventoryQuantities.php',7)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Reports','Inventory Stock Check Sheets','/StockCheck.php',14)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Reports','Inventory Valuation Report','/InventoryValuation.php',10)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Reports','List Inventory Status By Location/Category','/StockLocStatus.php',18)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Reports','List Negative Stocks','/PDFStockNegatives.php',20)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Reports','Mail Inventory Valuation Report','/MailInventoryValuation.php',11)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Reports','Make Inventory Quantities CSV','/StockQties_csv.php',15)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Reports','Period Stock Transaction Listing','/PDFPeriodStockTransListing.php',21)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Reports','Print Price Labels','/PDFPrintLabel.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Reports','Reorder Level','/ReorderLevel.php',8)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Reports','Reprint GRN','/ReprintGRN.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Reports','Serial Item Research Tool','/StockSerialItemResearch.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Reports','Stock Dispatch','/StockDispatch.php',9)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Reports','Stock Transfer Note','/PDFStockTransfer.php',22)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Transactions','Amend an internal stock request','/InternalStockRequest.php?Edit=Yes',11)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Transactions','Authorise Internal Stock Requests','/InternalStockRequestAuthorisation.php',9)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Transactions','Bulk Inventory Transfer - Dispatch','/StockLocTransfer.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Transactions','Bulk Inventory Transfer - Receive','/StockLocTransferReceive.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Transactions','Create a New Internal Stock Request','/InternalStockRequest.php?New=Yes',8)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Transactions','Enter Stock Counts','/StockCounts.php',7)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Transactions','Fulfil Internal Stock Requests','/InternalStockRequestFulfill.php',10)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Transactions','Inventory Adjustments','/StockAdjustments.php?NewAdjustment=Yes',5)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Transactions','Inventory Location Transfers','/StockTransfers.php?New=Yes',4)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Transactions','Receive Purchase Orders','/PO_SelectOSPurchOrder.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'stock','Transactions','Reverse Goods Received','/ReverseGRN.php',6)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Maintenance','Automaticall allocate customer receipts and credit notes','/Z_AutoCustomerAllocations.php',16)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Maintenance','Bank Account Authorised Users','/BankAccountUsers.php',13)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Maintenance','Discount Category Maintenance','/DiscountCategories.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Maintenance','Install a KwaMoja plugin','/PluginInstall.php',11)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Maintenance','Inventory Categories Maintenance','/StockCategories.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Maintenance','Inventory Locations Maintenance','/Locations.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Maintenance','Maintain Internal Departments','/Departments.php',7)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Maintenance','Maintain Internal Stock Categories to User Roles','/InternalStockCategoriesByRole.php',8)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Maintenance','MRP Available Production Days','/MRPCalendar.php',5)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Maintenance','MRP Demand Types','/MRPDemandTypes.php',6)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Maintenance','Rebuild sales analysis Records','/Z_RebuildSalesAnalysis.php',15)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Maintenance','Remove a KwaMoja plugin','/PluginUnInstall.php',12)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Maintenance','Report a problem with KwaMoja','/ReportBug.php',9)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Maintenance','Units of Measure','/UnitsOfMeasure.php',4)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Maintenance','Update Item Costs from a CSV file','/Z_UpdateItemCosts.php',12)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Maintenance','Upload a KwaMoja plugin file','/PluginUpload.php',10)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Maintenance','User Authorised Inventory Locations Maintenance','/UserLocations.php',14)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Maintenance','User Location Maintenance','/LocationUsers.php',15)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Reports','COGS GL Interface Postings','/COGSGLPostings.php',12)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Reports','Credit Status','/CreditStatus.php',4)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Reports','Customer Types','/CustomerTypes.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Reports','Discount Matrix','/DiscountMatrix.php',14)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Reports','Freight Costs Maintenance','/FreightCosts.php',13)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Reports','Mantain prices by quantity break and sales types','/PriceMatrix.php',15)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Reports','Mantain stock types','/StockTypes.php',16)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Reports','Payment Methods','/PaymentMethods.php',7)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Reports','Payment Terms','/PaymentTerms.php',5)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Reports','Sales Areas','/Areas.php',9)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Reports','Sales GL Interface Postings','/SalesGLPostings.php',11)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Reports','Sales People','/SalesPeople.php',8)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Reports','Sales Types','/SalesTypes.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Reports','Set Purchase Order Authorisation levels','/PO_AuthorisationLevels.php',6)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Reports','Shippers','/Shippers.php',10)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Reports','Supplier Types','/SupplierTypes.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Transactions','Access Permissions Maintenance','/WWW_Access.php',5)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Transactions','Bank Accounts','/BankAccounts.php',7)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Transactions','Company Preferences','/CompanyPreferences.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Transactions','Configuration Settings','/SystemParameters.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Transactions','Currency Maintenance','/Currencies.php',8)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Transactions','Dispatch Tax Province Maintenance','/TaxProvinces.php',11)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Transactions','Form Layout Editor','/FormDesigner.php',17)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Transactions','Geocode Setup','/GeocodeSetup.php',16)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Transactions','Label Templates Maintenance','/Labels.php',18)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Transactions','List Periods Defined','/PeriodsInquiry.php',13)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Transactions','Mailing Group Maintenance','/MailingGroupMaintenance.php',20)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Transactions','Maintain Security Tokens','/SecurityTokens.php',4)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Transactions','Page Security Settings','/PageSecurity.php',6)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Transactions','Report Builder Tool','/reportwriter/admin/ReportCreator.php',14)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Transactions','Schedule tasks to be automatically run','/JobScheduler.php',15)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Transactions','SMTP Server Details','/SMTPServer.php',19)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Transactions','Tax Authorities and Rates Maintenance','/TaxAuthorities.php',9)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Transactions','Tax Category Maintenance','/TaxCategories.php',12)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Transactions','Tax Group Maintenance','/TaxGroups.php',10)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Transactions','Update Module Order','/ModuleEditor.php',22)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Transactions','User Maintenance','/WWW_Users.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Transactions','View Audit Trail','/AuditTrail.php',15)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'system','Transactions','Web-Store Configuration','/ShopParameters.php',21)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Maintenance','Create new company template SQL file and submit to KwaMoja','/Z_CreateCompanyTemplateFile.php',6)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Maintenance','Create User Location records','/Z_MakeLocUsers.php',15)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Maintenance','Data Export Options','/Z_DataExport.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Maintenance','Import Fixed Assets from .csv file','/Z_ImportFixedAssets.php',5)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Maintenance','Import Stock Items from .csv','/Z_ImportStocks.php',4)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Maintenance','Maintain Language Files','/Z_poAdmin.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Maintenance','Make New Company','/Z_MakeNewCompany.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Maintenance','Purge all old prices','/Z_DeleteOldPrices.php',9)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Maintenance','Re-calculate brought forward amounts in GL','/Z_UpdateChartDetailsBFwd.php',7)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Maintenance','Re-Post all GL transactions from a specified period','/Z_RePostGLFromPeriod.php',8)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Reports','List of items without picture','/Z_ItemsWithoutPicture.php',4)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Reports','Show General Transactions That Do Not Balance','/Z_CheckGLTransBalance.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Reports','Show Local Currency Total Debtor Balances','/Z_CurrencyDebtorsBalances.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Reports','Show Local Currency Total Suppliers Balances','/Z_CurrencySuppliersBalances.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Transactions','Automatic Translation - Item descriptions','/AutomaticTranslationDescriptions.php',6)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Transactions','Cash Authorisation','/Z_ChangeStockCategory.php',15)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Transactions','Change A Customer Branch Code','/Z_ChangeBranchCode.php',2)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Transactions','Change A Customer Code','/Z_ChangeCustomerCode.php',1)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Transactions','Change A General Ledger Code','/Z_ChangeGLAccountCode.php',6)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Transactions','Change A Location Code','/Z_ChangeLocationCode.php',4)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Transactions','Change A Supplier Code','/Z_ChangeSupplierCode.php',3)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Transactions','Change An Inventory Item Code','/Z_ChangeStockCode.php',5)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Transactions','Delete sales transactions','/Z_DeleteSalesTransActions.php',9)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Transactions','Import Debtors','/Z_ImportDebtors.php',13)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Transactions','Import GL Transactions from a csv file','/Z_ImportGLTransactions.php',11)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Transactions','Import Suppliers','/Z_ImportSuppliers.php',14)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Transactions','Re-apply costs to Sales Analysis','/Z_ReApplyCostToSA.php',8)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Transactions','Reverse all supplier payments on a specified date','/Z_ReverseSuppPaymentRun.php',10)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Transactions','Update costs for all BOM items, from the bottom up','/Z_BottomUpCosts.php',7)";
$SQL[] = "INSERT INTO `menuitems` (`secroleid`, `modulelink`, `menusection`, `caption`, `url`, `sequence`) VALUES (8,'Utilities','Transactions','Update sales analysis with latest customer data','/Z_UpdateSalesAnalysisWithLatestCustomerData.php',12)";
foreach ($SQL as $Query) {
	$Result = DB_query($Query);
}

$SQL = array();
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (0,'Journal - GL',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (1,'Payment - GL',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (2,'Receipt - GL',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (3,'Standing Journal',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (10,'Sales Invoice',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (11,'Credit Note',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (12,'Receipt',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (15,'Journal - Debtors',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (16,'Location Transfer',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (17,'Stock Adjustment',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (18,'Purchase Order',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (19,'Picking List',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (20,'Purchase Invoice',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (21,'Debit Note',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (22,'Creditors Payment',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (23,'Creditors Journal',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (25,'Purchase Order Delivery',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (26,'Work Order Receipt',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (28,'Work Order Issue',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (29,'Work Order Variance',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (30,'Sales Order',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (31,'Shipment Close',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (32,'Contract Close',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (35,'Cost Update',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (36,'Exchange Difference',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (37,'Tenders',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (38,'Stock Requests',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (40,'Work Order',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (41,'Asset Addition',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (42,'Asset Category Change',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (43,'Delete w/down asset',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (44,'Depreciation',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (49,'Import Fixed Assets',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (50,'Opening Balance',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (60,'Staff Loans',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (61,'Staff Loan Repayments',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (500,'Auto Debtor Number',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (510,'Auto Donor Number',0)";
$SQL[] = "INSERT INTO `systypes` (`typeid`, `typename`, `typeno`) VALUES (600,'Auto Supplier Number',0)";
foreach ($SQL as $Query) {
	$Result = DB_query($Query);
}

function HighestFileName($PathPrefix) {
	$files = glob($PathPrefix . 'sql/updates/*.php');
	natsort($files);
	return basename(array_pop($files), ".php");
}

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

function executeSQL($SQL, $TrapErrors = False) {
	global $SQLFile;
	/* Run an sql statement and return an error code */
	if (!isset($SQLFile)) {
		DB_IgnoreForeignKeys();
		$Result = DB_query($SQL, '', '', false, $TrapErrors);
		$ErrorNumber = DB_error_no();
		DB_ReinstateForeignKeys();
		return $ErrorNumber;
	} else {
		fwrite($SQLFile, $SQL . ";\n");
	}
}

function updateDBNo($NewNumber) {
	global $SQLFile;
	if (!isset($SQLFile)) {
		$SQL = "UPDATE config SET confvalue='" . $NewNumber . "' WHERE confname='DBUpdateNumber'";
		executeSQL($SQL);
		$_SESSION['DBUpdateNumber'] = $NewNumber;
	}
}
//@para $File is the sql file name
//@para $DB is the DB connect reference
//@para $DBType refer to mysqli or mysql connection
//@para $NewDB is the new database name
//@para $DemoDB is the demo database name
//The purpose of this function is populate the database with mysql extention
function PopulateSQLDataBySQL($File, $DB, $DBType, $NewDB = false, $DemoDB = 'kwamojademo') {
	$DBName = ($NewDB) ? $NewDB : $DemoDB;
	($DBType == 'mysql') ? mysql_select_db($DBName, $DB) : mysqli_select_db($DB, $DBName);
	$SQLScriptFile = file($File);
	$ScriptFileEntries = sizeof($SQLScriptFile);
	$SQL = '';
	$InAFunction = false;
	echo '<div id="progress' . $File . '" class="centre" style="border-radius: 5px;width:100%;border:1px solid #157213;"></div>';
	echo '<div id="information' . $File . '" style="width"></div>';
	for ($i = 1;$i <= $ScriptFileEntries;$i++) {

		$SQLScriptFile[$i - 1] = trim($SQLScriptFile[$i - 1]);
		//ignore lines that start with -- or USE or /*
		$SQL.= ' ' . $SQLScriptFile[$i - 1];

		//check if this line kicks off a function definition - pg chokes otherwise
		if (mb_substr($SQLScriptFile[$i - 1], 0, 15) == 'CREATE FUNCTION') {
			$InAFunction = true;
		}
		//check if this line completes a function definition - pg chokes otherwise
		if (mb_substr($SQLScriptFile[$i - 1], 0, 8) == 'LANGUAGE') {
			$InAFunction = false;
		}
		if (mb_strpos($SQLScriptFile[$i - 1], ';') > 0 and !$InAFunction) {
			// Database created above with correct name.
			$Result = ($DBType == 'mysql') ? mysql_query($SQL, $DB) : mysqli_query($DB, $SQL);
			$SQL = '';
		}
		$percent = intval($i / $ScriptFileEntries * 100) . "%";
		echo '<script language="javascript">
						document.getElementById("progress' . $File . '").innerHTML="<div style=\"margin: 1px;border-radius: 5px; width:' . $percent . ';background-color:#157213;\">&nbsp;</div>";
						document.getElementById("information' . $File . '").innerHTML="' . ($i - 1) . ' row(s) processed.";
					</script>';
		echo str_repeat(' ', 1024 * 4);
		flush();

	} //end of for loop around the lines of the sql script

}

function chmod_R($path, $filemode, $dirmode) {
	if (is_dir($path)) {
		if (!chmod($path, $dirmode)) {
			$dirmode_str = decoct($dirmode);
			print "Failed applying filemode '$dirmode_str' on directory '$path'\n";
			print "  `-> the directory '$path' will be skipped from recursive chmod\n";
			return;
		}
		$dh = opendir($path);
		while (($file = readdir($dh)) !== false) {
			if ($file != '.' && $file != '..') { // skip self and parent pointing directories
				$fullpath = $path . '/' . $file;
				chmod_R($fullpath, $filemode, $dirmode);
			}
		}
		closedir($dh);
	} else {
		if (is_link($path)) {
			print "link '$path' is skipped\n";
			return;
		}
		if (!chmod($path, $filemode)) {
			$filemode_str = decoct($filemode);
			print "Failed applying filemode '$filemode_str' on file '$path'\n";
			return;
		}
	}
}

echo '<form id="DatabaseConfig" action="../index.php" method="post">';
echo '<fieldset>
			<legend>' . _('KwaMoja Installation Is Completed') . '</legend>
			<div class="page_help_text">
				<ul>
					<li>' . _('The KwaMoja installation has been successfully completed.') . '</li>
					<li>' . _('Before using your system please ensure that write permissions have been removed from all files except the companies folder.') . '</li>
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

?>