<?php
define("VERSIONFILE", "1.10");
$PageSecurity = 1;
/* Session started in session.inc for password checking and authorisation level check
config.php is in turn included in session.inc*/

include_once('includes/session.inc');
$Title = _('OpenCart to KwaMoja Syncrhonizer ' . VERSIONFILE);
include('includes/header.inc');

//include ('includes/KLGeneralFunctions.php'); //contains some particular functions for Ricard's shop.
include('includes/OcKwaMojaOpenCartDefines.php');
include('includes/OcOpenCartGeneralFunctions.php');
include('includes/OcOpenCartToKwaMojaSync.php');
include('includes/OcOpenCartConnectDB.php');

OpenCartToKwaMojaSync(TRUE, $oc_tableprefix, '');

include('includes/footer.inc');

?>