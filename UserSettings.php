<?php

include('includes/session.inc');
$Title = _('User Settings');
include('includes/header.inc');

echo '<p class="page_title_text" ><img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/user.png" title="', _('User Settings'), '" alt="" />', ' ', _('User Settings'), '</p>';

$PDFLanguages = array(
	_('Latin Western Languages - Times'),
	_('Eastern European Russian Japanese Korean Hebrew Arabic Thai'),
	_('Chinese'),
	_('Free Serif')
);


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
		if ($UpdatePassword != 'Y') {
			$SQL = "UPDATE www_users
				SET displayrecordsmax='" . $_POST['DisplayRecordsMax'] . "',
					theme='" . $_POST['Theme'] . "',
					language='" . $_POST['Language'] . "',
					email='" . $_POST['email'] . "',
					pdflanguage='" . $_POST['PDFLanguage'] . "',
					fontsize='" . $_POST['FontSize'] . "'
				WHERE userid = '" . $_SESSION['UserID'] . "'";

			$ErrMsg = _('The user alterations could not be processed because');
			$DbgMsg = _('The SQL that was used to update the user and failed was');

			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

			prnMsg(_('The user settings have been updated') . '. ' . _('Be sure to remember your password for the next time you login'), 'success');
		} else {
			$SQL = "UPDATE www_users
				SET displayrecordsmax='" . $_POST['DisplayRecordsMax'] . "',
					theme='" . $_POST['Theme'] . "',
					language='" . $_POST['Language'] . "',
					email='" . $_POST['email'] . "',
					pdflanguage='" . $_POST['PDFLanguage'] . "',
					password='" . CryptPass($_POST['Password']) . "',
					fontsize='" . $_POST['FontSize'] . "'
				WHERE userid = '" . $_SESSION['UserID'] . "'";

			$ErrMsg = _('The user alterations could not be processed because');
			$DbgMsg = _('The SQL that was used to update the user and failed was');

			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

			prnMsg(_('The user settings have been updated'), 'success');
		}
		// update the session variables to reflect user changes on-the-fly
		$_SESSION['DisplayRecordsMax'] = $_POST['DisplayRecordsMax'];
		$_SESSION['Theme'] = trim($_POST['Theme']);
		/*already set by session.inc but for completeness */
		$_SESSION['Theme'] = $_SESSION['Theme'];
		$_SESSION['Language'] = trim($_POST['Language']);
		$_SESSION['PDFLanguage'] = $_POST['PDFLanguage'];
		include('includes/MainMenuLinksArray.php');
		include('includes/LanguageSetup.php');

	}
	$_SESSION['ChartLanguage'] = GetChartLanguage();
	$_SESSION['InventoryLanguage'] = GetInventoryLanguage();
}

echo '<form method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

if (!isset($_POST['DisplayRecordsMax']) or $_POST['DisplayRecordsMax'] == '') {

	$_POST['DisplayRecordsMax'] = $_SESSION['DefaultDisplayRecordsMax'];

}

echo '<table class="selection">
		<tr>
			<td>', _('User ID'), ':</td>
			<td>', $_SESSION['UserID'], '</td>
		</tr>';

echo '<tr>
		<td>', _('User Name'), ':</td>
		<td>', $_SESSION['UsersRealName'], '
		<input type="hidden" name="RealName" value="', $_SESSION['UsersRealName'], '" /></td>
	</tr>';

echo '<tr>
		<td>', _('Maximum Number of Records to Display'), ':</td>
		<td><input type="text" class="number" name="DisplayRecordsMax" size="3" required="required" maxlength="3" value="', $_POST['DisplayRecordsMax'], '"  /></td>
	</tr>';


echo '<tr>
		<td>', _('Language'), ':</td>
		<td><select name="Language">';

if (!isset($_POST['Language'])) {
	$_POST['Language'] = $_SESSION['Language'];
}

foreach ($LanguagesArray as $LanguageEntry => $LanguageName) {
	if (isset($_POST['Language']) and $_POST['Language'] == $LanguageEntry) {
		echo '<option selected="selected" value="' . $LanguageEntry . '">' . $LanguageName['LanguageName'] . '</option>';
	} elseif (!isset($_POST['Language']) and $LanguageEntry == $_SESSION['DefaultLanguage']) {
		echo '<option selected="selected" value="' . $LanguageEntry . '">' . $LanguageName['LanguageName'] . '</option>';
	} else {
		echo '<option value="' . $LanguageEntry . '">' . $LanguageName['LanguageName'] . '</option>';
	}
}
echo '</select>
			</td>
		</tr>';

echo '<tr>
		<td>', _('Theme'), ':</td>
		<td><select name="Theme">';

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

if (!isset($_POST['PasswordCheck'])) {
	$_POST['PasswordCheck'] = '';
}
if (!isset($_POST['Password'])) {
	$_POST['Password'] = '';
}
echo '</select>
			</td>
		</tr>
	<tr>
		<td>', _('New Password'), ':</td>
		<td><input type="password" autocomplete="OFF" name="Password" size="20" value="', $_POST['Password'], '" /></td>
	</tr>
	<tr>
		<td>', _('Confirm Password'), ':</td>
		<td><input type="password" name="PasswordCheck" size="20"  value="', $_POST['PasswordCheck'], '" /></td>
	</tr>
	<tr>
		<td colspan="2" align="center"><i>', _('if you leave the password boxes empty your password will not change'), '</i></td>
	</tr>
	<tr>
		<td>', _('Email'), ':</td>
		<td><input type="email" name="email" size="40" value="', $_SESSION['UserEmail'], '" /></td>
	</tr>';

if (!isset($_POST['PDFLanguage'])) {
	$_POST['PDFLanguage'] = $_SESSION['PDFLanguage'];
}

/* Screen Font Size */

echo '<tr>
		<td>', _('Screen Font Size'), ':</td>
		<td><select name="FontSize">';
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
		</td>
	</tr>';

echo '<tr>
		<td>', _('PDF Language Support'), ': </td>
		<td><select name="PDFLanguage">';

for ($i = 0; $i < count($PDFLanguages); $i++) {
	if ($_POST['PDFLanguage'] == $i) {
		echo '<option selected="selected" value="', $i, '">', $PDFLanguages[$i], '</option>';
	} else {
		echo '<option value="', $i, '">', $PDFLanguages[$i], '</option>';
	}
}
echo '</select>
			</td>
		</tr>
	</table>';

echo '<div class="centre">
		<input type="submit" name="Modify" value="', _('Modify'), '" />
	</div>
	</form>';

include('includes/footer.inc');
?>