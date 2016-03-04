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

$ViewTopic = isset($ViewTopic) ? '?ViewTopic=' . $ViewTopic : '';
$BookMark = isset($BookMark) ? '#' . $BookMark : '';

if (isset($Title) and $Title == _('Copy a BOM to New Item Code')) { //solve the cannot modify heaer information in CopyBOM.php scritps
	ob_start();
}

echo '<!DOCTYPE html>';
echo '';

echo '<html moznomarginboxes mozdisallowselectionprint>
		<head>
			<meta http-equiv="Content-Type" content="application/html; charset=utf-8" />
			<title>', $Title, '</title>
			<link rel="icon" href="', $RootPath, '/favicon.ico" />
			<link href="', $RootPath, '/css/', $_SESSION['Theme'], '/default.css" rel="stylesheet" type="text/css" media="screen" />
			<link href="', $RootPath, '/css/print.css" rel="stylesheet" type="text/css" media="print" />
			<link href="', $RootPath, '/css/hint.css" rel="stylesheet" type="text/css" media="screen" />
			<script type="text/javascript" src = "', $RootPath, '/javascripts/MiscFunctions.js"></script>';

if ($Debug === 0) {
	echo '</head>';
	echo '<body onload="initial()">';
} else {
	echo '<link href="', $RootPath, '/css/holmes.css" rel="stylesheet" type="text/css" />';
	echo '</head>';
	echo '<body class="holmes-debug" onload="initial()">';
}

if (isset($_GET['FontSize'])) {
	$SQL = "UPDATE www_users
				SET fontsize='" . $_GET['FontSize'] . "'
				WHERE userid = '" . $_SESSION['UserID'] . "'";
	$Result = DB_query($SQL);
	switch ($_GET['FontSize']) {
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
}
echo '<style>
			body {
					font-size: ', $_SESSION['ScreenFontSize'], ';
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
	echo '<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/company.png" title="', _('Company'), '" alt="', _('Company'), '"/>', stripslashes($_SESSION['CompanyRecord']['coyname']);
	echo '</div>';
	echo '<div id="AppInfoUserDiv">';
	echo '<a  class="hint--bottom" data-hint="' . _('Change the settings for') . ' ' . $_SESSION['UsersRealName'] . '" href="', $RootPath, '/UserSettings.php"><img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/user.png" alt="', stripslashes($_SESSION['UsersRealName']), '" /></a>';
	echo '</div>';
	echo '<div id="AppInfoModuleDiv">';
	// Make the title text a class, can be set to display:none is some themes
	echo $Title;
	$ScriptName = basename($_SERVER['PHP_SELF']);
	if ($ScriptName == 'index.php') {
		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		echo '<span style="font-size:8pt;" class="hint--bottom" data-hint="Small text size"><a class="FontSize" href="', $RootPath, '/index.php?FontSize=0">A</a></span>&nbsp;';
		echo '<span style="font-size:10pt;" class="hint--bottom" data-hint="Medium text size"><a class="FontSize" href="', $RootPath, '/index.php?FontSize=1">A</a></span>&nbsp;';
		echo '<span style="font-size:12pt;" class="hint--bottom" data-hint="Large text size"><a class="FontSize" href="', $RootPath, '/index.php?FontSize=2">A</a></span>&nbsp;';
	}
	echo '</div>';
	echo '</div>'; // AppInfoDiv
	echo '<div id="QuickMenuDiv"><ul>';

	if ($ScriptName != 'Dashboard.php') {
		echo '<li><a class="hint--bottom" data-hint="', _('Show Dashboard'), '" href="', $RootPath, '/Dashboard.php"><img width="32px" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/dashboard-icon.png" alt="', _('Show Dashboard'), '" /></a></li>'; //take off inline formatting, use CSS instead ===HJ===
	}
	if ($ScriptName != 'index.php') {
		echo '<li><a class="hint--bottom" data-hint="', _('Return to the main menu'), '" href="', $RootPath, '/index.php"><img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/home.png" alt="', _('Main Menu'), '" /></a></li>'; //take off inline formatting, use CSS instead ===HJ===
	}

	if (count($_SESSION['AllowedPageSecurityTokens']) > 1) {

		if ($_SESSION['DBUpdateNumber'] >= 56) {
			if (!isset($_SESSION['Favourites'])) {
				$SQL = "SELECT caption, href FROM favourites WHERE userid='" . $_SESSION['UserID'] . "'";
				$Result = DB_query($SQL);
				while ($MyRow = DB_fetch_array($Result)) {
					$_SESSION['Favourites'][$MyRow['href']] = $MyRow['caption'];
				}
				if (DB_num_rows($Result) == 0) {
					$_SESSION['Favourites'] = Array();
				}
			}
			if ($ScriptName != 'index.php') {
				if (!isset($_SESSION['Favourites'][$ScriptName]) or $_SESSION['Favourites'][$ScriptName] == '') {
					echo '<li><span class="hint--bottom" data-hint="', _('Add this script to your list of commonly used'), '"><img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/add.png" id="PlusMinus" onclick="AddScript(\'', $ScriptName, '\',\'', $Title, '\')"', '" alt="', _('Add to commonly used'), '" /></span></li>';
				} else {
					echo '<li><span class="hint--bottom" data-hint="', _('Remove this script from your list of commonly used'), '"><img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/subtract.png" id="PlusMinus" onclick="RemoveScript(\'', $ScriptName, '\')"', '" alt="', _('Remove from commonly used'), '" /></span></li>';
				}
			}
			echo '<li><select name="Favourites" id="favourites" onchange="window.open (this.value,\'_self\',false)">';
			echo '<option value=""><i><----', _('Commonly used'), '----></i></option>';
			foreach ($_SESSION['Favourites'] as $Url=>$Caption) {
				echo '<option value="', $Url, '">', $Caption, '</option>';
			}
			echo '</select></li>';
		}

		$DefaultManualLink = '<li><a class="hint--left" data-hint="' . _('Read the manual page for this functionality') . '" target="_blank" href="' . $RootPath . '/doc/Manual/ManualContents.php' . $ViewTopic . $BookMark . '"><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/manual.png" alt="' . _('Help') . '" /></a></li>';

		if (strstr($_SESSION['Language'], 'en')) {
			echo $DefaultManualLink;
		} else {
			if (file_exists('locale/' . $_SESSION['Language'] . '/Manual/ManualContents.php')) {
				echo '<a class="hint--left" data-hint="', _('Read the manual page for this functionality'), '" href="', $RootPath, '/locale/', $_SESSION['Language'], '/Manual/ManualContents.php', $ViewTopic, $BookMark, '"><img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/manual.png" title="', _('Help'), '" alt="', _('Help'), '" /></a>';
			} else {
				echo $DefaultManualLink;
			}
		}
	}

	echo '<li><a class="hint--left" data-hint="', _('Logout'), '" href="', $RootPath, '/Logout.php" onclick="return MakeConfirm(\'', _('Are you sure you wish to logout?'), '\', \'', _('Confirm Logout'), '\', this);"><img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/quit.png" alt="', _('Logout'), '" /></a></li>';

	echo '</ul></div>'; // QuickMenuDiv
}
echo '</div>'; // HeaderWrapDiv
echo '</div>'; // Headerdiv
echo '<div id="HiddenOutput" style="display: none"></div>';
echo '<div id="BodyDiv">';
echo '<input type="hidden" name="Theme" id="Theme" value="', $_SESSION['Theme'], '" />';
echo '<div id="BodyWrapDiv">';

?>