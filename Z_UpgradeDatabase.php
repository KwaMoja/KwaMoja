<?php

$PageSecurity = 15;

include('includes/session.inc');

$Title = _('Database Upgrade');

//ob_start(); /*what is this for? */

if (!isset($_SESSION['DBVersion'])) {
	header('Location: index.php');
}

include('includes/header.inc');

function executeSQL($SQL, $TrapErrors = False) {
	global $SQLFile;
	/* Run an sql statement and return an error code */
	if (!isset($SQLFile)) {
		DB_IgnoreForeignKeys();
		$Result = DB_query($SQL, '', '', false, $TrapErrors);
		$ErrorNumber = DB_error_no();
		DB_ReinstateForeignKeys();
		return $ErrorNumber;
	} else {
		fwrite($SQLFile, $SQL . ";\n");
	}
}

function updateDBNo($NewNumber) {
	global $SQLFile;
	if (!isset($SQLFile)) {
		$SQL = "UPDATE config SET confvalue='" . $NewNumber . "' WHERE confname='DBUpdateNumber'";
		executeSQL($SQL);
		$_SESSION['DBUpdateNumber'] = $NewNumber;
	}
}

include('includes/UpgradeDB_' . $DBType . '.inc');

echo '<div class="centre"><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title;

if (!isset($_POST['continue'])) {
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<div class="page_help_text">' . _('You have database updates that are required.') . '<br />' . _('Please ensure that you have taken a backup of your current database before continuing.') . '</div><br />';
	echo '<div class="centre">
			<button type="submit" name="continue">' . _('Continue With Updates') . '</button>
		</div>';
	echo '</form></div>';
} else {
	$StartingUpdate = $_SESSION['DBUpdateNumber'] + 1;
	$EndingUpdate = $_SESSION['DBVersion'];
	if (isset($_POST['CreateSQLFile'])) {
		$SQLFile = fopen('./companies/' . $_SESSION['DatabaseName'] . '/reportwriter/UpgradeDB' . $StartingUpdate . '-' . $EndingUpdate . '.sql', 'w');
	}
	unset($_SESSION['Updates']);
	$_SESSION['Updates']['Errors'] = 0;
	$_SESSION['Updates']['Successes'] = 0;
	$_SESSION['Updates']['Warnings'] = 0;
	for ($UpdateNumber = $StartingUpdate; $UpdateNumber <= $EndingUpdate; $UpdateNumber++) {
		if (file_exists('sql/updates/' . $UpdateNumber . '.php')) {
			$SQL = "SET FOREIGN_KEY_CHECKS=0";
			$Result = DB_query($SQL);
			include('sql/updates/' . $UpdateNumber . '.php');
			$SQL = "SET FOREIGN_KEY_CHECKS=1";
			$Result = DB_query($SQL);
		}
	}
	echo '<table class="selection"><tr>';
	echo '<th colspan="4" class="header"><b>' . _('Database Updates Have Been Run') . '</b></th></tr>';
	echo '<tr><td style="background-color: #fddbdb;color: red;">' . $_SESSION['Updates']['Errors'] . ' ' . _('updates have errors in them') . '</td></tr>';
	echo '<tr><td style="background-color: #b9ecb4;color: #006400;">' . $_SESSION['Updates']['Successes'] . ' ' . _('updates have succeeded') . '</td></tr>';
	echo '<tr><td style="background-color: #c7ccf6;color: #616162;">' . $_SESSION['Updates']['Warnings'] . ' ' . _('updates have not been done as the update was unnecessary on this database') . '</td></tr>';
	if ($_SESSION['Updates']['Errors'] > 0) {
		$SizeOfErrorMessages = sizeOf($_SESSION['Updates']['Messages']);
		for ($i = 0; $i < $SizeOfErrorMessages; $i++) {
			echo '<tr><td>' . $_SESSION['Updates']['Messages'][$i] . '</td></tr>';
		}
	}
	echo '</table><br />';
	$ForceConfigReload = True;
}
if (isset($SQLFile)) {
	//		header('Location: Z_UpgradeDatabase.php'); //divert to the db upgrade if the table doesn't exist
}

include('includes/footer.inc');
?>