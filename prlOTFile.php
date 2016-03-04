<?php

include('includes/prlOverTimeClass.php');

include('includes/session.inc');
$Title = _('Overtime Entry');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['NewOT']) == 'Yes' AND isset($_SESSION['OTDetail'])) {
	unset($_SESSION['OTDetail']->OTEntries);
	unset($_SESSION['OTDetail']);
}

if (!isset($_SESSION['OTDetail'])) {
	$_SESSION['OTDetail'] = new OverTime;

}
if (!isset($_POST['OTDate'])) {
	$_SESSION['OTDetail']->OTDate = date($_SESSION['DefaultDateFormat']);
}

if (isset($_POST['OTDate'])) {
	$_SESSION['OTDetail']->OTDate = $_POST['OTDate'];
	$AllowThisPosting = true; //by default
	if (!Is_Date($_POST['OTDate'])) {
		prnMsg(_('The date entered was not valid please enter the overtime date') . $_SESSION['DefaultDateFormat'], 'warn');
		$_POST['CommitBatch'] = 'Do not do it the date is wrong';
		$AllowThisPosting = false; //do not allow posting
	}
}
if (isset($_POST['OTType'])) {
	$_SESSION['OTDetail']->OTType = $_POST['OTType'];
}
$msg = '';

if (isset($_POST['CommitBatch']) == _('Accept and Process Overtime')) {

	/*Start a transaction to do the whole lot inside */
	$Result = DB_query('BEGIN');

	foreach ($_SESSION['OTDetail']->OTEntries as $OTItem) {
		$SQL = "INSERT INTO prlottrans (
						otref,
						otdesc,
						otdate,
						overtimeid,
						employeeid,
						othours)
				VALUES (
					'" . $_POST['OTRef'] . "',
					'" . $_POST['OTDesc'] . "',
					'" . FormatDateForSQL($_SESSION['OTDetail']->OTDate) . "',
					'" . $OTItem->OverTimeID . "',
					'" . $OTItem->EmployeeID . "',
					'" . $OTItem->OTHours . "'
					)";
		$ErrMsg = _('Cannot insert overtime entry because');
		$DbgMsg = _('The SQL that failed to insert the OT Trans record was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
	}


	$ErrMsg = _('Cannot commit the changes');
	$Result = DB_query('COMMIT', $ErrMsg, _('The commit database transaction failed'), true);

	prnMsg(_('Overtime') . ' ' . $_POST['OTRef'] . ' ' . _('has been sucessfully entered'), 'success');
	unset($_POST['OTRef']);
	unset($_SESSION['OTDetail']->GLEntries);
	unset($_SESSION['OTDetail']);

	/*Set up a new in case user wishes to enter another */
	echo '<br /><a href="' . $_SERVER['PHP_SELF'] . '?NewOT=Yes">' . _('Enter Another Overtime Data') . '</a>';
	exit;
} elseif (isset($_GET['Delete'])) {
	/* User hit delete the line from the ot */
	$_SESSION['OTDetail']->Remove_OTEntry($_GET['Delete']);

} elseif (isset($_POST['Process']) == _('Accept')) { //user hit submit
	if ($AllowThisPosting) {
		$SQL = "SELECT overtimedesc
			FROM prlovertimetable
			WHERE overtimeid = '" . $_POST['OvertimeID'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		$OTD = $MyRow['overtimedesc'];
		$SQL = "SELECT  lastname,firstname
			FROM prlemployeemaster
			WHERE employeeid = '" . $_POST['EmployeeID'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		$_SESSION['OTDetail']->Add_OTEntry($_POST['OTHours'], $_POST['EmployeeID'], $MyRow['lastname'], $MyRow['firstname'], $OTD, $_POST['OvertimeID']);
		$Cancel = 1;
	}
}

if (isset($Cancel)) {
	unset($_POST['EmployeeID']);
}

echo '<form action=' . $_SERVER['PHP_SELF'] . '? method=POST>';


echo '<p><table BORDER=1 WIDTH=100%>';
echo '<tr><td VALIGN=TOP WIDTH=15%><table>'; // A new table in the first column of the main table

echo '<tr><td>' . _('Date') . ":</td>
	<td><input type='text' name='OTDate' maxlength=10 size=11 value='" . $_SESSION['OTDetail']->OTDate . "'></td></tr>";


echo '<tr><td>' . _('OT Ref') . ":</td>
	   <td><input type='text' name='OTRef' SIZE='11' MAXLENGTH='10' value='" . isset($_POST['OTRef']) . "'></td></tr>";

echo '</select></td></tr>';

echo '</table></td>';
/*close off the table in the first column */

echo '<td>';
/* Set upthe form for the transaction entry */

echo '<FONT SIZE=3 COLOR=BLUE>' . _('Overtime Line Entry') . '</FONT><table>';

echo '<tr><td>' . _('Description') . ":</td><td COLSPAN=3><input type='Text' name='OTDesc' SIZE=42 MAXLENGTH=40 value='" . isset($_POST['OTDesc']) . "'></td></tr>";
echo '<tr><td>' . _('Employee Name') . ":</td><td><select name='EmployeeID'>";
DB_data_seek($Result, 0);
$SQL = 'SELECT employeeid, lastname, firstname FROM prlemployeemaster ORDER BY lastname, firstname';
$Result = DB_query($SQL);
if (DB_num_rows($Result) == 0) {
	echo '</select></td></tr>';
	prnMsg(_('No Empoloyee accounts have been set up yet'), 'warn');
} else {
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['EmployeeID']) and ($_POST['EmployeeID']) == $MyRow['employeeid']) {
			echo '<option selected="selected" value=' . $MyRow['employeeid'] . '>' . $MyRow['lastname'] . ',' . $MyRow['firstname'];
		} else {
			echo '<option value=' . $MyRow['employeeid'] . '>' . $MyRow['lastname'] . ',' . $MyRow['firstname'];
		}
	} //end while loop
	echo '</select></td></tr>';
}
echo '<tr><td>' . _('Overtime Type') . ":</td><td><select name='OvertimeID'>";
DB_data_seek($Result, 0);
$SQL = 'SELECT overtimeid, overtimedesc FROM prlovertimetable';
$Result = DB_query($SQL);
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['OvertimeID']) and ($_POST['OvertimeID']) == '') {
		echo '<option selected="selected" value=' . $MyRow['overtimeid'] . '>' . $MyRow['overtimedesc'];
	} else {
		echo '<option value=' . $MyRow['overtimeid'] . '>' . $MyRow['overtimedesc'];
	}
} //end while loop
echo '<tr><td>' . _('OTHours') . ":</td><td COLSPAN=3><input type=Text Name='OTHours' Maxlength=12 SIZE=12 value=" . isset($_POST['OTHours']) . '></td></tr>';
echo '</table>';
echo '<input type=SUBMIT name=Process value="' . _('Accept') . '"><input type=SUBMIT name=Cancel value="' . _('Cancel') . '">';

echo '</td></tr></table>';
/*Close the main table */


echo "<table WIDTH=100% BORDER=1><tr>
	<td class='tableheader'>" . _('OT Hour') . "</td>
	<td class='tableheader'>" . _('Employee Name') . "</td>
	<td class='tableheader'>" . _('Overtime Type') . '</td></tr>';

foreach ($_SESSION['OTDetail']->OTEntries as $OTItem) {
	echo '<tr><td align="right">' . number_format($OTItem->OTHours, 2) . '</td>
		<td>' . $OTItem->EmployeeID . ' - ' . $OTItem->LastName . ',' . $OTItem->FirstName . '</td>
		<td>' . $OTItem->OverTimeDesc . '</td>
		<td><a href="' . $_SERVER['PHP_SELF'] . '?&Delete=' . $OTItem->ID . '">' . _('Delete') . '</a></td>
	</tr>';
}

echo '<tr><td align="right"><B>' . number_format($_SESSION['OTDetail']->OTTotal, 2) . '</B></td></tr></table>';

if (ABS($_SESSION['OTDetail']->OTTotal) > 0.001 AND $_SESSION['OTDetail']->OTItemCounter > 0) {
	echo "<br /><br /><input type=SUBMIT name='CommitBatch' value='" . _('Accept and Process Overtime') . "'>";
}

echo '</form>';
include('includes/footer.inc');
?>