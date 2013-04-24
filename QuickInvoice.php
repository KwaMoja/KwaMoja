<?php

/* Session started in session.inc for password checking and authorisation level check
config.php is in turn included in session.inc $PageSecurity now comes from session.inc (and gets read in by GetConfig.php*/

include('includes/session.inc');

$DebtorSQL = "SELECT name
				FROM debtorsmaster
					WHERE debtorno='" . $_SESSION['DebtorNo'] . "'";
$DebtorResult = DB_query($DebtorSQL, $db);
$DebtorInfo = DB_fetch_array($DebtorResult);

$Title = _('Raise an invoice for') . ' ' . $DebtorInfo['name'];

include('includes/header.inc');

include('includes/footer.inc');

?>