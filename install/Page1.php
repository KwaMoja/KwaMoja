<?php
/* Get the php-gettext function.
 * When users have not select the language, we guess user's language via
 * the http header information. once the user has select their lanugage,
 * use the language user selected
*/

if (!isset($_POST['Language'])) {
	if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) { //get users preferred language
		$ClientLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
		switch ($ClientLang) {
			case 'ar':
				$Language = 'ar_EG.utf8';
			break;
			case 'cs':
				$Language = 'cs_CZ.utf8';
			break;
			case 'de':
				$Language = 'de_DE.utf8';
			break;
			case 'el':
				$Language = 'el_GR.utf8';
			break;
			case 'en':
				$Language = 'en_GB.utf8';
			break;
			case 'es':
				$Language = 'es_ES.utf8';
			break;
			case 'et':
				$Language = 'et_EE.utf8';
			break;
			case 'fa':
				$Language = 'fa_IR.utf8';
			break;
			case 'fr':
				$Langauge = 'fr_CA.utf8';
			break;
			case 'hi':
				$Language = 'hi_IN.utf8';
			break;
			case 'hr':
				$Language = 'hr_HR.utf8';
			break;
			case 'hu':
				$Language = 'hu_HU.utf8';
			break;
			case 'id':
				$Language = 'id_ID.utf8';
			break;
			case 'it':
				$Language = 'it_IT.utf8';
			break;
			case 'ja':
				$Language = 'ja_JP.utf8';
			break;
			case 'lv':
				$Language = 'lv_LV.utf8';
			break;
			case 'nl':
				$Language = 'nl_NL.utf8';
			break;
			case 'pl':
				$Language = 'pl_PL.utf8';
			break;
			case 'pt':
				$Language = 'pt-PT.utf8';
			break;
			case 'ro':
				$Language = 'ro_RO.utf8';
			break;
			case 'ru':
				$Language = 'ru_RU.utf8';
			break;
			case 'sq':
				$Language = 'sq_AL.utf8';
			break;
			case 'sv':
				$Language = 'sv_SE.utf8';
			break;
			case 'sw':
				$Language = 'sw_KE.utf8';
			break;
			case 'tr':
				$Language = 'tr_TR.utf8';
			break;
			case 'vi':
				$Language = 'vi_VN.utf8';
			break;
			case 'zh':
				$Language = 'zh_CN.utf8';
			break;
			default:
				$Language = 'en_GB.utf8';

		}
		$_SESSION['Installer']['Language'] = $Language;
		if (isset($_SESSION['Language'])) {
			unset($_SESSION['Language']);
		}

	}
} else {
	$_SESSION['Installer']['Language'] = $_POST['Language'];
}

echo '<form id="installation" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<fieldset>
			<legend>' . _('Welcome to the KwaMoja Installation Wizard') . '</legend>
			<div class="page_help_text">
				<ul>
					<li>' . _('During installation you may see different status messages.') . '</li>
					<li>' . _('When there is an error message you must correct the error to continue.') . '</li>
					<li>' . _('If you see a warning message you should take notice before you proceed.') . '</li>
					<li>' . _('If you are unsure of an option value, you should keep the default setting.') . '</li>
				</ul>
			</div>
		</fieldset>';

/* Select the language for the install. This language will also
 * be the default language for the admin user
*/
include ($PathPrefix . 'includes/LanguagesArray.php');
echo '<fieldset>
			<legend>' . _('Select your language') . '</legend>
				<div class="page_help_text">
					<p>' . _('The installer will try and guess your language from your browser, but may get it wrong. Please select your preferred language below.') . '</p>
				</div>
				<ul>
					<li>
						<label for="Language">' . _('Language:') . '&#160;</label>
							<select id="Language" name="Language" onchange="document.getElementById(\'installation\').submit()">';

foreach ($LanguagesArray as $Key => $Language1) {
	if (isset($_SESSION['Installer']['Language']) and $Key == $_SESSION['Installer']['Language']) {
		echo '<option value="' . $Key . '" selected="selected">' . $Language1['LanguageName'] . '</option>';
	} else {
		echo '<option value="' . $Key . '" >' . $Language1['LanguageName'] . '</option>';
	}
}

echo '</select>
		</li>
		</ul>
	</fieldset>';

/* Select the DBMS to be used for this implementation */
echo '<fieldset>
			<legend>' . _('Select your Database Management System') . '</legend>
			<div class="page_help_text" >
				<p>' . _('KwaMoja can use several different Database systems. Please select below the system you will be using.') . '</p>
			</div>
			<ul>
				<li>
					<label for="DBMS">' . _('DBMS:') . '&#160;</label>
					<select id="DBMS" name="DBMS">';
if ($_SESSION['Installer']['DBMS'] == 'mysql') {
	echo '<option selected="selected" value="mysql">MySQL</option>';
} else {
	echo '<option value="mysql">MySQL</option>';
}
if ($_SESSION['Installer']['DBMS'] == 'mysqli') {
	echo '<option selected="selected" value="mysqli">MySQLi</option>';
} else {
	echo '<option value="mysqli">MySQLi</option>';
}
if ($_SESSION['Installer']['DBMS'] == 'mariadb') {
	echo '<option selected="selected" value="mariadb">MariaDB</option>';
} else {
	echo '<option value="mariadb">MariaDB</option>';
}
echo '</select>
			</li>
		</ul>
	</fieldset>';

/* Now we acquire default information about the system setup */

echo '<input type="hidden" name="DefaultTimeZone" id="DefaultTimeZone" />';
/* JavaScript function to guess the default time zone */
echo '<script>
			function tz(){
				document.getElementById(\'DefaultTimeZone\').value = jstz.determine().name();
			}
		</script>';

if (!empty($SafeModeWarning)) {
	echo '<input type="hidden" name="SafeModeWarning" value="' . $SafeModeWarning . '" />';
}
if (!empty($PHPVersion)) { //
	echo '<input type="hidden" name="PHPVersion" value="1" />';
}
if (!empty($ConfigFile)) {
	echo '<input type="hidden" name="ConfigFile" value="1" />';
}
if (!empty($CompaniesCreate)) {
	echo '<input type="hidden" name="CompaniesCreate" value="1" />';
}
if (!empty($GDExt)) {
	echo '<input type="hidden" name="GdExt" value="1" />';
}
if (!empty($GettextExt)) {
	echo '<input type="hidden" name="GettextExt" value="1" />';
}
if (!empty($MbstringExt)) {
	echo '<input type="hidden" name="MbstringExt" value="1" />';
}
if (!empty($LibxmlExt)) {
	echo '<input type="hidden" name="LibxmlExt" value="1" />';
}
if (!empty($_SESSION['Installer']['DBExt'])) {
	echo '<input type="hidden" name="DBExt" value="1" />';
}
if (!empty($PHP55)) {
	echo '<input type="hidden" name="PHP55" value="1" />';
}

echo '<fieldset style="text-align:center">
		<input type="hidden" name="LanguageSet" value="1" />
		<button type="submit" name="next">' . _('Next Step') . '<img src="right.png" style="float:right" /></button><br />
		<button type="submit" name="cancel">' . _('Restart') . '<img src="cross.png" style="float:right" /></button>
	</fieldset>';

echo '<fieldset style="text-align:center">
			<p>' . _('KwaMoja is an open source application licenced under GPL V2 and absolutely free to download.') . '<br />' . _('By installing KwaMoja you acknowledge you have read and agreed to') . ' ' . '<a href="http://www.gnu.org/licenses/gpl-2.0.html#SEC1" target="_blank">the licence</a>. <br />' . _('Please visit the official KwaMoja website for more information.') . '</p>
		<div class="centre" style="display:block">
			<a href="https://www.kwamoja.org"><img src="../css/kwamoja.png" title="KwaMoja" alt="KwaMoja" /></a>
		</div>
		</fieldset>';

echo '</form>';

?>