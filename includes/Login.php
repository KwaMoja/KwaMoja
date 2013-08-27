<?php

// Display demo user name and password within login form if $AllowDemoMode is true

include('LanguageSetup.php');

if ((isset($AllowDemoMode)) and ($AllowDemoMode == True) and (!isset($demo_text))) {
	$demo_text = _('login as user') . ': <i>' . _('admin') . '</i><br />' . _('with password') . ': <i>' . _('kwamoja') . '</i>';
} elseif (!isset($demo_text)) {
	$demo_text = _('Please login here');
}

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
?>
<html>
<head>
	<title>KwaMoja Login screen</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" href="css/login.css" type="text/css" />
	<!-- Javascript required for Twitter follow me button-->
	<script>
	  !function(d,s,id){
		var js,fjs=d.getElementsByTagName(s)[0];
		if(!d.getElementById(id)){
		  js=d.createElement(s);
		  js.id=id;
		  js.src="//platform.twitter.com/widgets.js";
		  fjs.parentNode.insertBefore(js,fjs);
		}
	  }(document,"script","twitter-wjs");
	</script>
	<!-- End of Javascript required for Twitter follow me button-->
</head>
<body>

<?php
if (get_magic_quotes_gpc()) {
	echo '<p style="background:white">';
	echo _('Your webserver is configured to enable Magic Quotes. This may cause problems if you use punctuation (such as quotes) when doing data entry. You should contact your webmaster to disable Magic Quotes');
	echo '</p>';
}
?>

<div id="container">
	<table>
		<tr>
			<th colspan="2">
				<div id="login_logo">
					<a href="http://www.kwamoja.com" target="_blank"><img src="companies/logo.png" style="width:100%" /></a>
				</div>
			</th>
		</tr>
		<tr>
			<td width="70%">
				<div id="login_box">
					<form action="<?php
echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8');
?>" method="post" class="noPrint">
					<input type="hidden" name="FormID" value="<?php
echo $_SESSION['FormID'];
?>" />
					<label><?php
echo _('Company');
?>:</label>

					<?php
if ($AllowCompanySelectionBox === 'Hide') {
	// do not show input or selection box
	echo '<input type="hidden" name="CompanyNameField"  value="' . $DefaultCompany . '" />';
} else if ($AllowCompanySelectionBox === 'ShowInputBox'){
	// show input box
	echo '<input type="text" required="required" autofocus="autofocus" name="CompanyNameField"  value="' . $DefaultCompany . '" />';
} else {
	// Show selection box ($AllowCompanySelectionBox == 'ShowSelectionBox')
	echo '<select name="CompanyNameField">';

	$DirHandle = dir('companies/');

	while (false !== ($CompanyEntry = $DirHandle->read())) {
		if (is_dir('companies/' . $CompanyEntry) and $CompanyEntry != '..' and $CompanyEntry != '' and $CompanyEntry != '.') {
			if (file_exists('companies/' . $CompanyEntry . '/Companies.php')) {
				include('companies/' . $CompanyEntry . '/Companies.php');
			} else {
				$CompanyName[$CompanyEntry] = $CompanyEntry;
			}
			if ($CompanyEntry == $DefaultCompany) {
				echo '<option selected="selected" value="' . $CompanyEntry . '">' . $CompanyName[$CompanyEntry] . '</option>';
			} else {
				echo '<option  value="' . $CompanyEntry . '">' . $CompanyName[$CompanyEntry] . '</option>';
			}
		}
	}

	$DirHandle->close();

	echo '</select>';
}
?>

					<br />
					<label><?php
echo _('User name');
?>:</label>
					<input type="text" autofocus="autofocus" required="required" name="UserNameEntryField" maxlength="20" /><br />
					<label><?php
echo _('Password');
?>:</label>
					<input type="password" required="required" name="Password" />
	   <div id="demo_text">
	   <?php
if (isset($demo_text)) {
	echo $demo_text;
}
?>
	   </div>
					<button class="button" type="submit" value="<?php
echo _('Login');
?>" name="SubmitUser">
					<?php
echo _('Login');
?>
					 <img src="css/tick.png" title="' . _('Upgrade') . '" alt="" class="ButtonIcon" /></button>
					 </div>
					</form>
				</div>
			</td>
			<td style="width: 20%; padding-left: 1%;">
				<div>
					<b>Join us at :</b><br />
					<a href="https://sourceforge.net/projects/kwamoja" target="_blank"><img src="css/sourceforge-logo.png" style="width:70%; border: 1px solid #A49999;" /></a><br />
					<a href="https://launchpad.net/kwamoja" target="_blank"><img src="css/launchpad.png" style="width:70%; border: 1px solid #A49999;" /></a><br />
					<a href="https://kwamoja.codeplex.com/" target="_blank"><img src="css/codeplex-logo.png" style="width:70%; border: 1px solid #A49999;" /></a><br /><br />
				</div>
			</td>
			<td style="width: 25%; padding-left: 0%;">
				<div>
					<b>Follow us at :</b>
					<!--Follow us on twitter button-->
					<a href="https://twitter.com/KwaMoja" class="twitter-follow-button" data-show-count="false">Follow @KwaMoja</a><br />
					<a href="https://plus.google.com/u/0/communities/106845561370559503655" target="_blank"><img src="css/google-plus.png" style="width:50%; border: 1px solid #A49999;" /></a>
					<a href="http://www.facebook.com/Kwamoja" target="_blank"><img src="css/FindUsOnFacebook.png" style="width:70%; border: 1px solid #A49999;" /></a>
					<a href="http://www.linkedin.com/groups/KwaMoja-4833235?trk=myg_ugrp_ovr" target="_blank"><img src="css/linkedin.png" style="width:70%; border: 1px solid #A49999;" /></a>
				</div>
			</td>
		</tr>
	</table>
</div>

</body>
</html>