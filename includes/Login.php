<?php
/* $Id$*/
// Display demo user name and password within login form if $AllowDemoMode is true
include ('LanguageSetup.php');


if ($AllowDemoMode == True and !isset($demo_text)) {
	$demo_text = _('login as user') .': <i>' . _('admin') . '</i><br />' ._('with password') . ': <i>' . _('kwamoja') . '</i>';
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
if (get_magic_quotes_gpc()){
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
					<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8');?>" method="post" class="noPrint">
					<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
					<label><?php echo _('Company'); ?>:</label>
					<?php
						if ($AllowCompanySelectionBox == true){
							echo '<select name="CompanyNameField">';

							$DirHandle = dir('companies/');

							while (false !== ($CompanyEntry = $DirHandle->read())){
								if (is_dir('companies/' . $CompanyEntry) and $CompanyEntry != '..' and $CompanyEntry != '' and $CompanyEntry!='.svn' and $CompanyEntry!='.'){
									if ($CompanyEntry==$DefaultCompany) {
										echo '<option selected="selected" label="'.$CompanyEntry.'" value="'.$CompanyEntry.'">'.$CompanyEntry.'</option>';
									} else {
										echo '<option label="'.$CompanyEntry.'" value="'.$CompanyEntry.'">'.$CompanyEntry.'</option>';
									}
								}
							}

							$DirHandle->close();

							echo '</select>';
						} else {
							echo '<input type="text" name="CompanyNameField"  value="' . $DefaultCompany . '" />';
						}
					?>
					<br />
					<label><?php echo _('User name'); ?>:</label>
					<input type="text" name="UserNameEntryField" maxlength="20" /><br />
					<label><?php echo _('Password'); ?>:</label>
					<input type="password" name="Password" />
					<div class="centre"><div id="demo_text"><?php echo $demo_text;?></div>
					<button class="button" type="submit" value="<?php echo _('Login'); ?>" name="SubmitUser">
					<?php echo _('Login'); ?>
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
					<b>Follow us at :</b><br />
					<!--Follow us on twitter button-->
					<a href="https://twitter.com/KwaMoja" class="twitter-follow-button" data-show-count="false">Follow @KwaMoja</a><br />
					<a href="http://www.facebook.com/Kwamoja" target="_blank"><img src="css/FindUsOnFacebook.png" style="width:70%; border: 1px solid #A49999;" /></a>
				</div>
			</td>
		</tr>
	</table>
</div>
	<script type="text/javascript">
			<!--
				  document.forms[0].UserNameEntryField.focus();
			//-->
	</script>
</body>
</html>
