<?php


// Titles and screen header
// Needs the file config.php loaded where the variables are defined for
//  $RootPath
//  $Title - should be defined in the page this file is included with
if (!isset($RootPath)) {
	$RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF']));
	if ($RootPath == '/' or $RootPath == "\\") {
		$RootPath = '';
	}
}
$debug = 0;
$ViewTopic = isset($ViewTopic) ? '?ViewTopic=' . $ViewTopic : '';
$BookMark = isset($BookMark) ? '#' . $BookMark : '';
$StrictXHTML = False;
if (!headers_sent()) {
	if ($StrictXHTML) {
		header('Content-type: application/xhtml+xml; charset=utf-8');
	} else {
		header('Content-type: text/html; charset=utf-8');
	}
}
if (isset($Title) and $Title == _('Copy a BOM to New Item Code')) { //solve the cannot modify heaer information in CopyBOM.php scritps
	ob_start();
}
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $Title . '</title>';
echo '<link rel="shortcut icon" href="' . $RootPath . '/favicon.ico" />';
echo '<link rel="icon" href="' . $RootPath . '/favicon.ico" />';
if ($StrictXHTML) {
	echo '<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />';
} else {
	echo '<meta http-equiv="Content-Type" content="application/html; charset=utf-8" />';
}
echo '<link href="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/default.css" rel="stylesheet" type="text/css" />';
echo '<link href="' . $RootPath . '/css/tooltip.css" rel="stylesheet" type="text/css" />';
if ($debug == 1) {
	echo '<link href="' . $RootPath . '/css/holmes.css" rel="stylesheet" type="text/css" />';
}

echo '<script type="text/javascript" src = "' . $RootPath . '/javascripts/MiscFunctions.js"></script>';
echo '<style media="screen">
			.noPrint{ display: block; }
			.yesPrint{ display: block !important; }
		</style>
		<style media="print">
			.noPrint{ display: none; }
			.yesPrint{ display: block !important; }
		</style>';
echo '</head>';
if ($debug == 1) {
	echo '<body class="holmes-debug">';
} else {
	echo '<body>';
}

if (isset($_GET['FontSize'])) {
	$sql = "UPDATE www_users
						SET fontsize='" . $_GET['FontSize'] . "'
						WHERE userid = '" . $_SESSION['UserID'] . "'";
	$result = DB_query($sql, $db);
}

if (isset($_POST['FontSize'])) {
	$_SESSION['ScreenFontSize'] = $_POST['FontSize'];
} else if (isset($_GET['FontSize'])) {
	$_SESSION['ScreenFontSize'] = $_GET['FontSize'];
}

switch ($_SESSION['ScreenFontSize']) {
	case 0:
		$FontSize = '8pt';
		break;
	case 1:
		$FontSize = '10pt';
		break;
	case 2:
		$FontSize = '12pt';
		break;
	default:
		$FontSize = '10pt';
}
echo '<style>
			body {
					font-size: ' . $FontSize . ';
				}
			</style>';

echo '<div id="CanvasDiv">';
echo '<div id="HeaderDiv" class="noPrint">';
echo '<div id="HeaderWrapDiv">';


if (isset($Title)) {


	if (!isset($_SESSION['CompanyRecord'])) {
		include('companies/' . $_SESSION['DatabaseName'] . '/Companies.php');
		$_SESSION['CompanyRecord']['coyname'] = $CompanyName[$_SESSION['DatabaseName']];
	}
	echo '<div id="AppInfoDiv">'; //===HJ===
	echo '<div id="AppInfoCompanyDiv">';
	echo '<img src="' . $RootPath . '/css/' . $Theme . '/images/company.png" title="' . _('Company') . '" alt="' . _('Company') . '"/>' . stripslashes($_SESSION['CompanyRecord']['coyname']);
	echo '</div>';
	echo '<div id="AppInfoUserDiv">';
	echo '<a data-tooltip="' . _('Change settings for') . ' ' . stripslashes($_SESSION['UsersRealName']) . '" href="' . $RootPath . '/UserSettings.php"><img src="' . $RootPath . '/css/' . $Theme . '/images/user.png" alt="' . stripslashes($_SESSION['UsersRealName']) . '" /></a>';
	echo '</div>';
	echo '<div id="AppInfoModuleDiv">';
	// Make the title text a class, can be set to display:none is some themes
	echo $Title;
	if (basename($_SERVER['PHP_SELF']) == 'index.php') {
		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		echo '<span style="font-size:8pt;"><a href="' . $RootPath . '/index.php?FontSize=0" data-tooltip="Small text size">A</a></span>&nbsp;';
		echo '<span style="font-size:10pt;"><a href="' . $RootPath . '/index.php?FontSize=1" data-tooltip="Medium text size">A</a></span>&nbsp;';
		echo '<span style="font-size:12pt;"><a href="' . $RootPath . '/index.php?FontSize=2" data-tooltip="Large text size">A</a></span>&nbsp;';
	}
	echo '</div>';
	echo '</div>'; // AppInfoDiv
	echo '<div id="QuickMenuDiv"><ul>';

	if (basename($_SERVER['PHP_SELF']) != 'Dashboard.php') {
		echo '<li><a data-tooltip="' . _('Show Dashboard') . '" href="' . $RootPath . '/Dashboard.php"><img width="32px" src="' . $RootPath . '/css/' . $Theme . '/images/dashboard-icon.png" alt="' . _('Dashboard') . '" /></a></li>'; //take off inline formatting, use CSS instead ===HJ===
	}
	if (basename($_SERVER['PHP_SELF']) != 'index.php') {
		echo '<li><a data-tooltip="' . _('Return to the main menu') . '" href="' . $RootPath . '/index.php"><img src="' . $RootPath . '/css/' . $Theme . '/images/home.png" alt="' . _('Main Menu') . '" /></a></li>'; //take off inline formatting, use CSS instead ===HJ===
	}

	if (count($_SESSION['AllowedPageSecurityTokens']) > 1) {

		if (basename($_SERVER['PHP_SELF']) != 'index.php') {
			if ($_SESSION['DBUpdateNumber'] >= 56) {
				$sql = "SELECT caption, href FROM favourites WHERE href='" . basename($_SERVER['PHP_SELF']) . "' AND userid='" . $_SESSION['UserID'] . "'";
				$result = DB_query($sql, $db);

				if (DB_num_rows($result) == 0) {
					echo '<li><img src="' . $RootPath . '/css/' . $Theme . '/images/add.png" id="PlusMinus" onclick="AddScript(\'' . basename($_SERVER['PHP_SELF']) . '\',\'' . $Title . '\')" data-tooltip="' . _('Add this script to your list of commonly used') . '" alt="' . _('Add to commonly used') . '" /></li>';
				} else {
					echo '<li><img src="' . $RootPath . '/css/' . $Theme . '/images/subtract.png" id="PlusMinus" onclick="RemoveScript(\'' . basename($_SERVER['PHP_SELF']) . '\')" data-tooltip="' . _('Remove this script from your list of commonly used') . '" alt="' . _('Remove from commonly used') . '" /></li>';
				}
			}
		}

		if ($_SESSION['DBUpdateNumber'] >= 56) {
			$sql = "SELECT caption, href FROM favourites WHERE userid='" . $_SESSION['UserID'] . "'";
			$result = DB_query($sql, $db);

			echo '<li><select name="Favourites" id="favourites" onchange="window.open (this.value,\'_self\',false)">';
			echo '<option value=""><i><----' . _('Commonly used') . '----></i></option>';
			while ($myrow = DB_fetch_array($result)) {
				echo '<option value="' . $myrow['href'] . '">' . $myrow['caption'] . '</option>';
			}
			echo '</select></li>';
		}
		$DefaultManualLink = '<li><a rel="external" data-tooltip="' . _('Read the manual page for this functionality') . '" target="_blank" href="' . $RootPath . '/doc/Manual/ManualContents.php' . $ViewTopic . $BookMark . '"><img src="' . $RootPath . '/css/' . $Theme . '/images/manual.png" alt="' . _('Help') . '" /></a></li>';

		if (mb_substr($_SESSION['Language'], 0, 2) != 'en') {
			if (file_exists('locale/' . $_SESSION['Language'] . '/Manual/ManualContents.php')) {
				echo '<li><a target="_blank" data-tooltip="' . _('Read the manual page for this functionality') . '" href="' . $RootPath . '/locale/' . $_SESSION['Language'] . '/Manual/ManualContents.php' . $ViewTopic . $BookMark . '"><img src="' . $RootPath . '/css/' . $Theme . '/images/manual.png" title="' . _('Help') . '" alt="' . _('Help') . '" /></a></li>';
			} else {
				echo $DefaultManualLink;
			}
		} else {
			echo $DefaultManualLink;
		}
	}

	echo '<li><a data-tooltip="' . _('Logout') . '" href="' . $RootPath . '/Logout.php" onclick="return MakeConfirm(\'' . _('Are you sure you wish to logout?') . '\', \'' . _('Confirm Logout') . '\', this);"><img src="' . $RootPath . '/css/' . $Theme . '/images/quit.png" alt="' . _('Logout') . '" /></a></li>';

	echo '</ul></div>'; // QuickMenuDiv
}
echo '</div>'; // HeaderWrapDiv
echo '</div>'; // Headerdiv

echo '<div id="BodyDiv">';
echo '<input type="hidden" name="Theme" id="Theme" value="' . $Theme . '" />';
echo '<div id="BodyWrapDiv">';

?>