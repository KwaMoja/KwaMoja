<?php
include ('includes/session.php');

$Title = _('SMTP Server details');

include ('includes/header.php');

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/email.png" title="', _('SMTP Server'), '" alt="" />', ' ', _('SMTP Server Settings'), '
	</p>';

// First check if there are smtp server data or not
$SecurityOptions = array('', 'ssl', 'tls');

if ((isset($_POST['submit']) or isset($_POST['reload'])) and $_POST['MailServerSetting'] == 1) { //if there are already data setup, Update the table
	$SQL = "UPDATE emailsettings SET
				host='" . $_POST['Host'] . "',
				port='" . $_POST['Port'] . "',
				heloaddress='" . $_POST['HeloAddress'] . "',
				username='" . $_POST['UserName'] . "',
				password='" . $_POST['Password'] . "',
				timeout='" . $_POST['Timeout'] . "',
				auth='" . $_POST['Auth'] . "',
				security='" . $_POST['Security'] . "'";
	$ErrMsg = _('The email setting information failed to update');
	$DbgMsg = _('The SQL failed to update is ');
	$Result1 = DB_query($SQL, $ErrMsg, $DbgMsg);
	unset($_POST['MailServerSetting']);
	if (isset($_POST['submit'])) {
		prnMsg(_('The settings for the SMTP server have been successfully updated'), 'success');
	}

} elseif ((isset($_POST['submit']) or isset($_POST['reload'])) and $_POST['MailServerSetting'] == 0) { //There is no data setup yet
	$SQL = "INSERT INTO emailsettings(host,
		 				port,
						heloaddress,
						username,
						password,
						timeout,
						auth,
						security)
				VALUES (
					'" . $_POST['Host'] . "',
					'" . $_POST['Port'] . "',
					'" . $_POST['HeloAddress'] . "',
					'" . $_POST['UserName'] . "',
					'" . $_POST['Password'] . "',
					'" . $_POST['Timeout'] . "',
					'" . $_POST['Auth'] . "'
					'" . $_POST['Security'] . "')";
	$ErrMsg = _('The email settings failed to be inserted');
	$DbgMsg = _('The SQL failed to insert the email information is');
	$Result2 = DB_query($SQL);
	unset($_POST['MailServerSetting']);
	if (isset($_POST['submit'])) {
		prnMsg(_('The settings for the SMTP server have been sucessfully inserted'), 'success');
	}
}

// Check the mail server setting status
$SQL = "SELECT id,
				host,
				port,
				heloaddress,
				username,
				password,
				timeout,
				auth,
				security
			FROM emailsettings";
$ErrMsg = _('The email settings information cannot be retrieved');
$DbgMsg = _('The SQL that failed was');

$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
if (DB_num_rows($Result) != 0) {
	$MailServerSetting = 1;
	$MyRow = DB_fetch_array($Result);
} else {
	DB_free_result($Result);
	$MailServerSetting = 0;
	$MyRow['host'] = '';
	$MyRow['port'] = '';
	$MyRow['heloaddress'] = '';
	$MyRow['username'] = '';
	$MyRow['password'] = '';
	$MyRow['timeout'] = 5;
	$MyRow['auth'] = 1;
	$MyRow['security'] = '';
}

echo '<form method="post" action="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<input type="hidden" name="MailServerSetting" value="' . $MailServerSetting . '" />';

echo '<fieldset>
		<legend>', _('Server Settings'), '</legend>';

echo '<field>
		<label for="Host">', _('Server Host Name'), '</label>
		<input type="text" name="Host" required="required" maxlength="50" value="', $MyRow['host'], '" />
	</field>';

echo '<field>
		<label for="Port">', _('SMTP port'), '</label>
		<input type="text" name="Port" required="required" maxlength="4" size="4" class="number" value="', $MyRow['port'], '" />
	</field>';

echo '<field>
		<label for="HeloAddress">', _('Helo Command'), '</label>
		<input type="text" name="HeloAddress" required="required" maxlength="10" value="', $MyRow['heloaddress'], '" />
	</field>';

echo '<field>
		<label for="Auth">', _('Authorisation Required'), '</label>
		<select required="required" name="Auth"  onchange="ReloadForm(reload);">';
if ($MyRow['auth'] == 1) {
	echo '<option selected="selected" value="1">', _('True'), '</option>';
	echo '<option value="0">', _('False'), '</option>';
} else {
	echo '<option value="1">', _('True'), '</option>';
	echo '<option selected="selected" value="0">', _('False'), '</option>';
}
echo '</select>
	</field>';

if ($MyRow['auth'] == 1) {
	echo '<field>
			<label for="UserName">', _('User Name'), '</label>
			<input type="text" name="UserName" required="required" maxlength="100" size="30" value="', $MyRow['username'], '" />
		</field>';

	echo '<field>
			<label for="Password">', _('Password'), '</label>
			<input type="password" name="Password" required="required" maxlength="50" value="', $MyRow['password'], '" />
		</field>';

	echo '<field>
			<label for="Security">', _('SSL/TLS'), '</label>
			<select name="Security">';
	foreach ($SecurityOptions as $SecurityOption) {
		if ($SecurityOption == $MyRow['security']) {
			echo '<option selected="selected" value="', $SecurityOption, '">', mb_strtoupper($SecurityOption), '</option>';
		} else {
			echo '<option value="', $SecurityOption, '">', mb_strtoupper($SecurityOption), '</option>';
		}
	}
	echo '</select>
		</field>';
} else {
	echo '<input type="hidden" name="UserName" value="', $MyRow['username'], '" />
		<input type="hidden" name="Password" value="', $MyRow['password'], '" />';
}
echo '<field>
		<label for="Timeout">', _('Timeout (seconds)'), '</label>
		<input type="text" size="5" name="Timeout" required="required" maxlength="4" class="number" value="', $MyRow['timeout'], '" />
	</field>';

echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="submit" value="', _('Update'), '" />
	</div>';

echo '<input type="submit" name="reload" value="Reload" hidden="hidden" />
	</form>';

include ('includes/footer.php');

?>