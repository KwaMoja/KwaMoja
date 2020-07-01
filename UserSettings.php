<?php
/* Allows the user to change system wide defaults for the theme - appearance, the number of records to show in searches and the language to display messages in */

include ('includes/session.php');
$Title = _('User Settings');
$ViewTopic = 'GettingStarted';
$BookMark = 'UserSettings';

if (isset($_POST['ShowPageHelp'])) {
	$_SESSION['ShowPageHelp'] = $_POST['ShowPageHelp'];
}

if (isset($_POST['ShowFieldHelp'])) {
	$_SESSION['ShowFieldHelp'] = $_POST['ShowFieldHelp'];
}

if (isset($_POST['FontSize'])) {
	$_GET['FontSize'] = $_POST['FontSize'];
}

include ('includes/header.php');

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/user.png" title="', _('User Settings'), '" alt="" />', ' ', _('User Settings'), '
	</p>';

$PDFLanguages = array(_('Latin Western Languages - Times'), _('Eastern European Russian Japanese Korean Hebrew Arabic Thai'), _('Chinese'), _('Free Serif'));

if (isset($_POST['Modify'])) {
	// no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	 ie the page has called itself with some user input */

	//first off validate inputs sensible
	if ($_POST['DisplayRecordsMax'] <= 0) {
		$InputError = 1;
		prnMsg(_('The Maximum Number of Records on Display entered must not be negative') . '. ' . _('0 will default to system setting'), 'error');
	}

	//!!!for the demo only - enable this check so password is not changed
	if ($AllowDemoMode and $_POST['Password'] != '') {
		$InputError = 1;
		prnMsg(_('Cannot change password in the demo or others would be locked out!'), 'warn');
	}

	$UpdatePassword = 'N';

	if ($_POST['PasswordCheck'] != '') {
		if (mb_strlen($_POST['Password']) < 5) {
			$InputError = 1;
			prnMsg(_('The password entered must be at least 5 characters long'), 'error');
		} elseif (mb_strstr($_POST['Password'], $_SESSION['UserID']) != False) {
			$InputError = 1;
			prnMsg(_('The password cannot contain the user id'), 'error');
		}
		if ($_POST['Password'] != $_POST['PasswordCheck']) {
			$InputError = 1;
			prnMsg(_('The password and password confirmation fields entered do not match'), 'error');
		} else {
			$UpdatePassword = 'Y';
		}
	}

	if ($InputError != 1) {
		// no errors
		if (isset($_POST['Language']) and !CheckLanguageChoice($_POST['Language'])) {
			$_POST['Language'] = $DefaultLanguage;
		}

		if ($UpdatePassword != 'Y') {
			$SQL = "UPDATE www_users
				SET displayrecordsmax='" . $_POST['DisplayRecordsMax'] . "',
					theme='" . $_POST['Theme'] . "',
					language='" . $_POST['Language'] . "',
					email='" . $_POST['email'] . "',
					pdflanguage='" . $_POST['PDFLanguage'] . "',
					fontsize='" . $_POST['FontSize'] . "',
					showpagehelp='" . $_POST['ShowPageHelp'] . "',
					showfieldhelp='" . $_POST['ShowFieldHelp'] . "'
				WHERE userid = '" . $_SESSION['UserID'] . "'";

			$ErrMsg = _('The user alterations could not be processed because');
			$DbgMsg = _('The SQL that was used to update the user and failed was');

			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
			prnMsg(_('The user settings have been updated'), 'success');

		} else {
			$SQL = "UPDATE www_users
				SET displayrecordsmax='" . $_POST['DisplayRecordsMax'] . "',
					theme='" . $_POST['Theme'] . "',
					language='" . $_POST['Language'] . "',
					email='" . $_POST['email'] . "',
					pdflanguage='" . $_POST['PDFLanguage'] . "',
					password='" . CryptPass($_POST['Password']) . "',
					fontsize='" . $_POST['FontSize'] . "',
					showpagehelp='" . $_POST['ShowPageHelp'] . "',
					showfieldhelp='" . $_POST['ShowFieldHelp'] . "'
				WHERE userid = '" . $_SESSION['UserID'] . "'";

			$ErrMsg = _('The user alterations could not be processed because');
			$DbgMsg = _('The SQL that was used to update the user and failed was');

			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

			prnMsg(_('The user settings have been updated') . '. ' . _('Be sure to remember your password for the next time you login'), 'success');
		}
		// update the session variables to reflect user changes on-the-fly
		$_SESSION['DisplayRecordsMax'] = $_POST['DisplayRecordsMax'];
		$_SESSION['Theme'] = trim($_POST['Theme']);
		/*already set by session.php but for completeness */
		$_SESSION['Theme'] = $_SESSION['Theme'];
		$_SESSION['Language'] = trim($_POST['Language']);
		$_SESSION['PDFLanguage'] = $_POST['PDFLanguage'];
		$_SESSION['ShowPageHelp'] = $_POST['ShowPageHelp'];
		$_SESSION['ShowFieldHelp'] = $_POST['ShowFieldHelp'];
		include ('includes/MainMenuLinksArray.php');
		include ('includes/LanguageSetup.php');

	}
	$_SESSION['ChartLanguage'] = GetChartLanguage();
	$_SESSION['InventoryLanguage'] = GetInventoryLanguage();
}

echo '<form method="post" class="centre" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

echo '<div class="page_help_text">', _('This page contains the users settings than can be changed by the user.'), '<br />', _('For help, click on the help icon in the top right'), '<br />', _('Once you have filled in the details, click on the button at the bottom of the screen'), '
	</div>';

echo '<fieldset>
		<legend>', _('Edit the settings for this user'), '</legend>
		<field>
			<label for="UserID">', _('User ID'), ':</label>
			<div class="fieldtext">', $_SESSION['UserID'], '</div>
		</field>
		<field>
			<label for="RealName">', _('User Name'), ':</label>
			<div class="fieldtext">', $_SESSION['UsersRealName'], '</div>
			<input type="hidden" name="RealName" value="', $_SESSION['UsersRealName'], '" />
		</field>
		<field>
			<label for="DisplayRecordsMax">', _('Maximum Number of Records to Display'), '</label>
			<input type="text" class="number" autofocus="autofocus" name="DisplayRecordsMax" size="3" required="required" maxlength="3" value="', $_SESSION['DisplayRecordsMax'], '"  />
			<fieldhelp>', _('The maximum number of records to show on inquiries.'), '</fieldhelp>
		</field>
		<field>
			<label for="Language">', _('Language'), ':</label>
				<select name="Language">';

foreach ($LanguagesArray as $LanguageEntry => $LanguageName) {
	if (isset($_SESSION['Language']) and $_SESSION['Language'] == $LanguageEntry) {
		echo '<option selected="selected" value="', $LanguageEntry, '">', $LanguageName['LanguageName'], '</option>';
	} elseif (!isset($_SESSION['Language']) and $LanguageEntry == $_SESSION['DefaultLanguage']) {
		echo '<option selected="selected" value="', $LanguageEntry, '">', $LanguageName['LanguageName'], '</option>';
	} else {
		echo '<option value="', $LanguageEntry, '">', $LanguageName['LanguageName'], '</option>';
	}
}
echo '</select>
		<fieldhelp>', _('The language to be used for this user.'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="Theme">', _('Theme'), ':</label>
		<select name="Theme">';

$Themes = glob('css/*', GLOB_ONLYDIR);
foreach ($Themes as $ThemeName) {
	$ThemeName = basename($ThemeName);
	if ($ThemeName != 'mobile' and mb_substr($ThemeName, -4) != '-rtl') {
		if ($_SESSION['Theme'] == $ThemeName) {
			echo '<option selected="selected" value="', $ThemeName, '">', ucfirst($ThemeName), '</option>';
		} else {
			echo '<option value="', $ThemeName, '">', ucfirst($ThemeName), '</option>';
		}
	}
}
echo '</select>
		<fieldhelp>', _('The theme to be used for this user.'), '</fieldhelp>
	</field>';

if (!isset($_POST['PasswordCheck'])) {
	$_POST['PasswordCheck'] = '';
}
if (!isset($_POST['Password'])) {
	$_POST['Password'] = '';
}
echo '<field>
		<label for="Password">', _('New Password'), ':</label>
		<input type="password" autocomplete="OFF" name="Password" size="10" value="', $_POST['Password'], '" />
		<fieldhelp>', _('If you are changing the password enter it here. Otherwise leave blank.'), '</fieldhelp>
	</field>
	<field>
		<label for="PasswordCheck">', _('Confirm Password'), ':</label>
		<input type="password" name="PasswordCheck" size="10"  value="', $_POST['PasswordCheck'], '" />
		<fieldhelp>', _('Confirm the new password if you created one.'), '</fieldhelp>
	</field>
	<field>
		<i>', _('if you leave the password boxes empty your password will not change'), '</i>
	</field>
	<field>
		<label for="email">', _('Email'), ':</label>
		<input type="email" name="email" size="20" value="', $_SESSION['UserEmail'], '" />
		<fieldhelp>', _('The users email address.'), '</fieldhelp>
	</field>';

/* Screen Font Size */

echo '<field>
		<label for="FontSize">', _('Screen Font Size'), ':</label>
		<select name="FontSize">';
if (isset($_SESSION['ScreenFontSize']) and $_SESSION['ScreenFontSize'] == '8pt') {
	echo '<option selected="selected" value="0">', _('Small'), '</option>';
	echo '<option value="1">', _('Medium'), '</option>';
	echo '<option value="2">', _('Large'), '</option>';
} else if (isset($_SESSION['ScreenFontSize']) and $_SESSION['ScreenFontSize'] == '10pt') {
	echo '<option value="0">', _('Small'), '</option>';
	echo '<option selected="selected" value="1">', _('Medium'), '</option>';
	echo '<option value="2">', _('Large'), '</option>';
} else {
	echo '<option value="0">', _('Small'), '</option>';
	echo '<option value="1">', _('Medium'), '</option>';
	echo '<option selected="selected" value="2">', _('Large'), '</option>';
}
echo '</select>
			<fieldhelp>', _('The size of font to be displayed for this user.'), '</fieldhelp>
	</field>';

// Turn off/on page help:
echo '<field>
		<label for="ShowPageHelp">', _('Display page help'), ':</label>
		<select id="ShowPageHelp" name="ShowPageHelp">';
if ($_SESSION['ShowPageHelp'] == 0) {
	echo '<option selected="selected" value="0">', _('No'), '</option>', '<option value="1">', _('Yes'), '</option>';
} else {
	echo '<option value="0">', _('No'), '</option>', '<option selected="selected" value="1">', _('Yes'), '</option>';
}
echo '</select>
			<fieldhelp>', _('Show page help when available.'), '</fieldhelp>
	</field>';
// Turn off/on field help:
echo '<field>
		<label for="ShowFieldHelp">', _('Display field help'), ':</label>
		<select id="ShowFieldHelp" name="ShowFieldHelp">';
if ($_SESSION['ShowFieldHelp'] == 0) {
	echo '<option selected="selected" value="0">', _('No'), '</option>', '<option value="1">', _('Yes'), '</option>';
} else {
	echo '<option value="0">', _('No'), '</option>', '<option selected="selected" value="1">', _('Yes'), '</option>';
}
echo '</select>
			<fieldhelp>', _('Show field help when available.'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="PDFLanguage">', _('PDF Language Support'), ': </label>
		<select name="PDFLanguage">';

foreach ($PDFLanguages as $ID => $PDFLanguage) {
	if ($_SESSION['PDFLanguage'] == $ID) {
		echo '<option selected="selected" value="', $ID, '">', $PDFLanguage, '</option>';
	} else {
		echo '<option value="', $ID, '">', $PDFLanguage, '</option>';
	}
}
echo '</select>
		</field>';

echo '</fieldset>';
echo '<div  class="centre">
		<input type="submit" name="Modify" value="', _('Modify'), '" />
	</div>
</form>';

include ('includes/footer.php');
?>