<?php
define("VERSIONFILE", "1.10");

/* Session started in session.inc for password checking and authorisation level check
config.php is in turn included in session.inc*/

include('includes/session.inc');
$Title = _('KwaMoja to OpenCart Hourly Synchronizer ' . VERSIONFILE);
include('includes/header.inc');
include('includes/GetPrice.inc');

// include ('includes/KLGeneralFunctions.php');
include('includes/OcKwaMojaOpenCartDefines.php');
include('includes/OcOpenCartGeneralFunctions.php');
include('includes/OcKwaMojaToOpenCartSync.php');
include('includes/OcOpenCartConnectDB.php');

KwaMojaToOpenCartHourlySync(TRUE, $oc_tableprefix, TRUE, '');

include('includes/footer.inc');

?>