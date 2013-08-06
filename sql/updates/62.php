<?php

if (is_writable('companies/' . $_SESSION['DatabaseName'])) {
	$FileHandle = fopen('companies/' . $_SESSION['DatabaseName'] . '/Companies.php', 'w');

	fwrite($FileHandle, '<?php' . "\n");

	fwrite($FileHandle, '$CompanyName[\'' . $_SESSION['DatabaseName'] . '\'] = \'' . stripslashes($_SESSION['CompanyRecord']['coyname']) . '\';' . "\n");

	fwrite($FileHandle, '?>');

	fclose($FileHandle);
	$_SESSION['Updates']['Successes']++;
} else {
	prnMsg( _('The directory') . ' ' . 'companies/' . $_SESSION['DatabaseName'] . ' ' . _('must be writable by the web server'), 'error');
	include('includes/footer.inc');
	$_SESSION['Updates']['Errors']++;
	exit;
}

UpdateDBNo(basename(__FILE__, '.php'), $db);

?>