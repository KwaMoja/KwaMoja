<?php
$ErrMsg = '';
$InputError = 0;
$WarnMsg = '';
$InputWarn = 0;

//set the default time zone
if (!empty($_SESSION['Installer']['TimeZone'])) {
	date_default_timezone_set($_SESSION['Installer']['TimeZone']);
}

//Check if the cookie is allowed
if (false) {
	$InputError = 1;
	echo '<div class="error">' . _('Please set Cookies allowed in your web brower, otherwise KwaMoja cannot run properly') . '</div>';
} else {
	echo '<div class="success">' . _('Cookies are properly enabled in your browser') . '</div>';
}

//Check if it's in safe model, safe mode has been deprecated at 5.3.0 and removed at 5.4
//Please refer to here for more details http://hk2.php.net/manual/en/features.safe-mode.php
if (ini_get('safe_mode')) {
	$SafeModeWarning = _('You php is running in safe mode, it will force a maximum script execution time of 30 seconds') . ' ' . _('This can sometimes mean that the installation cannot be completed in time.') . ' ' . _('It is better to turn this function off');
	$InputWarn = 1;
	$WarnMsg.= '<p>' . _($_POST['SafeModeWarning']) . '</p>';
}

//It's time to check the php version. The version should be run greater than 5.1
if (version_compare(PHP_VERSION, '5.1.0') < 0) {
	$InputError = 1;
	echo '<div class="error">' . _('You PHP version should be greater than 5.1') . '</div>';
} else {
	echo '<div class="success">' . _('Your PHP version is suitable for KwaMoja') . '</div>';
}

//Check the write access of the root path
$RootPath = '..';
if (!is_writable($RootPath)) {
	$InputError = 1;
	//get the directory where kwamoja live
	$KwaMojaHome = dirname(dirname(__FILE__));
	echo '<div class="error">' . _('The directory') . ' ' . $KwaMojaHome . ' ' . _('must be writable by web server') . '</div>';
} else {
	echo '<div class="success">' . _('The base KwaMoja directory is writable') . '</div>';
}

//Check the write access of the companies path
$Companies = $RootPath . '/companies';
if (!is_writable($Companies)) {
	$InputError = 1;
	$KwaMojaHome = dirname(dirname(__FILE__));
	echo '<div class="error">' . _('The directory') . ' ' . $KwaMojaHome . '/companies/' . ' ' . ('must be writable by web server') . '</div>';
} else {
	echo '<div class="success">' . _('The companies/ directory is writable') . '</div>';
}

//get the list of installed extensions
$Extensions = get_loaded_extensions();

//First check the gd module
if (!in_array('gd', $Extensions)) {
	$InputError = 1;
	echo '<div class="error">' . _('The GD extension should be installed in your PHP configuration') . '</div>';
} else {
	echo '<div class="success">' . _('The GD extension is correctly installed') . '</div>';
}

//Check the gettext module, it's a selectable
if (!in_array('gettext', $Extensions)) {
	$InputError = 1;
	echo '<div class="error">' . _('The gettext extension is not availble in your PHP') . '</div>';
} else {
	echo '<div class="success">' . _('The gettext extension is correctly installed') . '</div>';
}

//Check the mbstring module, it must be exist
if (!in_array('mbstring', $Extensions)) {
	$InputError = 1;
	echo '<div class="error">' . _('The mbstring extension is not availble in your PHP') . '</div>';
} else {
	echo '<div class="success">' . _('The mbstring extension is correctly installed') . '</div>';
}

//Check the libxml module
if (!in_array('libxml', $Extensions)) {
	$InputError = 1;
	echo '<div class="error">' . _('The libxml extension is not available in your PHP') . '</div>';
} else {
	echo '<div class="success">' . _('The libxml extension is correctly installed') . '</div>';
}

//Check that the DBMS driver is installed
if (isset($_POST['DBMS'])) {
	$DBExt = '0';
	if ($_POST['DBMS'] == 'mysql' and in_array('mysql', $Extensions)) {
		$DBExt = '1';
	}
	if ($_POST['DBMS'] == 'mysqli' and in_array('mysqli', $Extensions)) {
		$DBExt = '1';
	}
	if ($_POST['DBMS'] == 'mariadb' and in_array('mysqlnd', $Extensions)) {
		$DBExt = '1';
	}
	if ($DBExt != 1) {
		$InputError = 1;
		echo '<div class="error">' . _('You do not have the correct database extension installed for PHP') . '</div>';
	} else {
		echo '<div class="success">' . _('The database extension is installed') . '</div>';
	}
}

if ($InputError != 0) {
	echo '<form id="refresh" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<fieldset style="text-align:center">
			<button type="submit">' . _('Check Again') . '</button>
		</fieldset>';
	echo '</form';
	exit;
}
if ($InputWarn != 0) {
	echo '<form id="refresh" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<fieldset style="text-align:center">
			<button type="submit">' . _('Check Again') . '</button>
		</fieldset>';
	echo '</form';
}

echo '<form id="DatabaseConfig" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<fieldset style="text-align:center">
		<input type="hidden" name="SystemValid" value="1" />
		<button type="submit" name="previous">' . _('Previous Step') . '<img src="left.png" style="float:left" /></button>
		<button type="submit" name="next">' . _('Next Step') . '<img src="right.png" style="float:right" /></button><br />
		<button type="submit" name="cancel">' . _('Restart') . '<img src="cross.png" style="float:right" /></button>
	</fieldset>';
echo '</form>';

?>