<?php
// Titles and screen header
// Needs the file config.php loaded where the variables are defined for
//  $RootPath
//  $Title - should be defined in the page this file is included with
if (!isset($RootPath)) {
	$RootPath = dirname(htmlspecialchars(basename(__FILE__)));
	if ($RootPath == '/' or $RootPath == "\\") {
		$RootPath = '';
	}
}

$ViewTopic = isset($ViewTopic) ? '?ViewTopic=' . $ViewTopic : '';
$BookMark = isset($BookMark) ? '#' . $BookMark : '';

if (isset($_GET['Theme'])) {
	if (file_exists($PathPrefix . $RootPath . 'css/' . $_GET['Theme'])) {
		$_SESSION['Theme'] = $_GET['Theme'];
		$SQL = "UPDATE www_users SET theme='" . $_GET['Theme'] . "' WHERE userid='" . $_SESSION['UserID'] . "'";
		$Result = DB_query($SQL);
	}
}

if (isset($Title) and $Title == _('Copy a BOM to New Item Code')) { //solve the cannot modify heaer information in CopyBOM.php scritps
	ob_start();
}

echo '<!DOCTYPE html>';

echo '<html>
		<head>
			<meta http-equiv="Content-Type" content="application/html; charset=utf-8; cache-control: no-cache, no-store, must-revalidate; Pragma: no-cache" />
			<title>', _('KwaMoja'), ' - ', $Title, '</title>
			<link rel="icon" href="', $RootPath, '/favicon.ico" />
			<link href="', $RootPath, '/css/', $_SESSION['Theme'], '/styles.css?v=13" rel="stylesheet" type="text/css" media="screen" />
			<link href="', $RootPath, '/css/print.css" rel="stylesheet" type="text/css" media="print" />
			<meta name="viewport" content="width=device-width, initial-scale=1">';
echo '<script async type="text/javascript" src = "', $RootPath, '/javascripts/MiscFunctions.js"></script>';
echo '<script>
		localStorage.setItem("DateFormat", "', $_SESSION['DefaultDateFormat'], '");
		localStorage.setItem("Theme", "', $_SESSION['Theme'], '");
	</script>';

if ($_SESSION['ShowPageHelp'] == 0) {
	echo '<link href="', $RootPath, '/css/', $_SESSION['Theme'], '/page_help_off.css" rel="stylesheet" type="text/css" media="screen" />';
} else {
	echo '<link href="', $RootPath, '/css/', $_SESSION['Theme'], '/page_help_on.css" rel="stylesheet" type="text/css" media="screen" />';
}

if ($_SESSION['ShowFieldHelp'] == 0) {
	echo '<link href="', $RootPath, '/css/', $_SESSION['Theme'], '/field_help_off.css" rel="stylesheet" type="text/css" media="screen" />';
} else {
	echo '<link href="', $RootPath, '/css/', $_SESSION['Theme'], '/field_help_on.css" rel="stylesheet" type="text/css" media="screen" />';
}

if ($Debug === 0) {
	echo '</head>';
	if (isset($AutoPrintPage)) {
		echo '<body onload="window.print()">';
	} else {
		echo '<body onload="initial(); load()" onunload="GUnload()">';
	}
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

$ScriptName = basename($_SERVER['SCRIPT_NAME']);

echo '<header>';

echo '<div id="Info" data-title="', _('Company Details'), '">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/company.png" alt="', _('Company'), '"/>', stripslashes($_SESSION['CompanyRecord']['coyname']), '
	</div>';

echo '<div id="Info">
		<a class="FontSize" data-title="', _('Change the settings for'), ' ', $_SESSION['UsersRealName'], '" href="', $RootPath, '/UserSettings.php">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/user.png" alt="', stripslashes($_SESSION['UsersRealName']), '" />', $_SESSION['UsersRealName'], '
		</a>
	</div>';

echo '<div id="ExitIcon">
		<a data-title="', _('Logout'), '" href="', $RootPath, '/Logout.php" onclick="return MakeConfirm(\'', _('Are you sure you wish to logout?'), '\', \'', _('Confirm Logout'), '\', this);">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/quit.png" alt="', _('Logout'), '" />
		</a>
	</div>';

if (count($_SESSION['AllowedPageSecurityTokens']) > 1) {

	$DefaultManualLink = '<div id="ActionIcon"><a data-title="' . _('Read the manual') . '" target="_blank" href="' . $RootPath . '/doc/Manual/ManualContents.php' . $ViewTopic . $BookMark . '"><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/manual.png" alt="' . _('Help') . '" /></a></div>';

	if (strstr($_SESSION['Language'], 'en')) {
		echo $DefaultManualLink;
	} else {
		if (file_exists('locale/' . $_SESSION['Language'] . '/Manual/ManualContents.php')) {
			echo '<div id="ActionIcon">
					<a data-title="', _('Read the manual'), '" href="', $RootPath, '/locale/', $_SESSION['Language'], '/Manual/ManualContents.php', $ViewTopic, $BookMark, '">
						<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/manual.png" title="', _('Help'), '" alt="', _('Help'), '" />
					</a>
				</div>';
		} else {
			echo $DefaultManualLink;
		}
	}

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
		echo '<div id="ActionIcon">
				<select name="Favourites" id="favourites" onchange="window.open (this.value,\'_self\',false)">';
		echo '<option value=""><i>', _('Commonly used scripts'), '</i></option>';
		foreach ($_SESSION['Favourites'] as $Url => $Caption) {
			echo '<option value="', $Url, '">', _($Caption), '</option>';
		}
		echo '</select>
			</div>';
		if ($ScriptName != 'index.php') {
			if (!isset($_SESSION['Favourites'][$ScriptName]) or $_SESSION['Favourites'][$ScriptName] == '') {
				echo '<div id="ActionIcon">
						<a data-title="', _('Add this script to your list of commonly used'), '">
							<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/add.png" id="PlusMinus" onclick="AddScript(\'', $ScriptName, '\',\'', $Title, '\')"', ' alt="', _('Add to commonly used'), '" />
						</a>
					</div>';
			} else {
				echo '<div id="ActionIcon">
						<a data-title="', _('Remove this script from your list of commonly used'), '">
							<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/subtract.png" id="PlusMinus" onclick="RemoveScript(\'', $ScriptName, '\')"', ' alt="', _('Remove from commonly used'), '" />
						</a>
					</div>';
			}
		}
	}
}

if ($ScriptName != 'Dashboard.php') {
	echo '<div id="ActionIcon">
			<a data-title="', _('Show Dashboard'), '" href="', $RootPath, '/Dashboard.php">
				<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/dashboard-icon.png" alt="', _('Show Dashboard'), '" />
			</a>
		</div>'; //take off inline formatting, use CSS instead ===HJ===
	
}

if ($ScriptName != 'index.php') {
	echo '<div id="ActionIcon">
			<a data-title="', _('Return to the main menu'), '" href="', $RootPath, '/index.php">
				<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/home.png" alt="', _('Main Menu'), '" />
			</a>
		</div>'; //take off inline formatting, use CSS instead ===HJ===
	
}

echo '<br /><div class="ScriptTitle">', $Title, '</div>';
if ($ScriptName == 'index.php') {
	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	if ($_SESSION['ScreenFontSize'] == '8pt') {
		echo '<a style="font-size:8pt;" class="FontSize" href="', $RootPath, '/index.php?FontSize=0" data-title="', _('Small text size'), '"><u>A</u></a>';
	} else {
		echo '<a style="font-size:8pt;" class="FontSize" href="', $RootPath, '/index.php?FontSize=0" data-title="', _('Small text size'), '">A</a>';
	}
	if ($_SESSION['ScreenFontSize'] == '10pt') {
		echo '<a style="font-size:10pt;" class="FontSize" href="', $RootPath, '/index.php?FontSize=1" data-title="', _('Medium text size'), '"><u>A</u></a>';
	} else {
		echo '<a style="font-size:10pt;" class="FontSize" href="', $RootPath, '/index.php?FontSize=1" data-title="', _('Medium text size'), '">A</a>';
	}
	if ($_SESSION['ScreenFontSize'] == '12pt') {
		echo '<a style="font-size:12pt;" class="FontSize" href="', $RootPath, '/index.php?FontSize=2" data-title="', _('Large text size'), '"><u>A</u></a>';
	} else {
		echo '<a style="font-size:12pt;" class="FontSize" href="', $RootPath, '/index.php?FontSize=2" data-title="', _('Large text size'), '">A</a>';
	}
	echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	echo '<div class="ScriptTitle">', _('Theme'), ':</div>';

	echo '<select name="Theme" id="favourites" onchange="window.open (\'index.php?Theme=\' + this.value,\'_self\',false)">';

	$Themes = glob('css/*', GLOB_ONLYDIR);
	foreach ($Themes as $ThemeName) {
		$ThemeName = basename($ThemeName);
		if ($ThemeName != 'mobile') {
			if ($_SESSION['Theme'] == $ThemeName) {
				echo '<option selected="selected" value="', $ThemeName, '">', $ThemeName, '</option>';
			} else {
				echo '<option value="', $ThemeName, '">', $ThemeName, '</option>';
			}
		}
	}
	echo '</select>';
}

echo '</header>';

if ($ScriptName != 'index.php') {
	echo '<section class="MainBody">';
}

echo '<div id="MessageContainerHead"></div>';

?>