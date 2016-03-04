<?php
$PageSecurity=1;
include('includes/session.inc');

$Title = _('Report a bug to the project team');

include('includes/header.inc');

if (http_file_exists($HomePage . '/buggenie/thebuggenie/' . $DefaultDatabase)) {
	echo '<script type="text/javascript">window.open(\'' . $HomePage . 'buggenie/thebuggenie/' . $DefaultDatabase . ');</script>';
	prnMsg(_('You will now be redirected to the ') . $ProjectName . _(' bug genie. If you do not have an account then you can login as user - guest with password - guest. If you use the guest login you will not be notified of progress with your report'), 'info');
} else {
	prnMsg(_('You do not appear to have an internet connection. To use this function you require access to the internet'), 'warn');
}
include('includes/footer.inc');

?>