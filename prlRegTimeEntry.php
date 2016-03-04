<?php

/* $Revision: 1.0 $ */

include('includes/prlRegTimeClass.php');
include('includes/DefineJournalClass.php');

include('includes/session.inc');
$Title = _('Normal Time Entry for Hourly Employees');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['NewRT']) == 'Yes' AND isset($_SESSION['RTDetail'])) {
	unset($_SESSION['RTDetail']->RTEntries);
	unset($_SESSION['RTDetail']);
}

if (!isset($_SESSION['RTDetail'])) {
	$_SESSION['RTDetail'] = new OverTime;
}
if (!isset($_POST['RTDate'])) {
	$_SESSION['RTDetail']->RTDate = date($_SESSION['DefaultDateFormat']);
}

if (isset($_POST['RTDate'])) {
	$_SESSION['RTDetail']->RTDate = $_POST['RTDate'];
	$AllowThisPosting = true; //by default
	if (!Is_Date($_POST['RTDate'])) {
		prnMsg(_('The date entered was not valid please enter the overtime date') . $_SESSION['DefaultDateFormat'], 'warn');
		$_POST['CommitBatch'] = 'Do not do it the date is wrong';
		$AllowThisPosting = false; //do not allow posting
	}
}
$msg = '';

if (isset($_POST['CommitBatch']) and ($_POST['CommitBatch']) == _('Accept and Process Overtime')) {

	$Result = DB_query('BEGIN');

	foreach ($_SESSION['RTDetail']->RTEntries as $RTItem) {
		$SQL = "INSERT INTO prldailytrans ( rtref,
											rtdesc,
											rtdate,
											employeeid,
											reghrs
										) VALUES (
											'" . isset($_POST['OTRef']) . "',
											'" . isset($_POST['OTDesc']) . "',
											'" . FormatDateForSQL($_SESSION['RTDetail']->RTDate) . "',
											'" . $RTItem->EmployeeID . "',
											'" . $RTItem->RTHours . "'
										)";
		$ErrMsg = _('Cannot insert regular time entry because');
		$DbgMsg = _('The SQL that failed to insert the regular time Trans record was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
	}

	$ErrMsg = _('Cannot commit the changes');
	$Result = DB_query('COMMIT', $ErrMsg, _('The commit database transaction failed'), true);

	prnMsg(_('Regular Time') . ' ' . $_POST['RTDesc'] . ' ' . _('has been sucessfully entered'), 'success');
	unset($_POST['RTRef']);
	unset($_SESSION['RTDetail']->GLEntries);
	unset($_SESSION['RTDetail']);

	/*Set up a newy in case user wishes to enter another */
	echo '<a href="' . $_SERVER['PHP_SELF'] . '?NewRT=Yes">' . _('Enter Another Overtime Data') . '</a>';
	/*And post the journal too */
	exit;
} elseif (isset($_GET['Delete'])) {
	/* User hit delete the line from the ot */
	$_SESSION['RTDetail']->Remove_RTEntry($_GET['Delete']);

} elseif (isset($_POST['Process']) == _('Accept')) { //user hit submit a new GL Analysis line into the journal
	if ($AllowThisPosting) {
		$SQL = "SELECT  lastname,
						firstname
					FROM prlemployeemaster
					WHERE employeeid = '" . $_POST['EmployeeID'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		$_SESSION['RTDetail']->Add_RTEntry($_POST['RTHours'], $_POST['EmployeeID'], $MyRow['lastname'], $MyRow['firstname'], $_POST['RTDesc']);
		/*Make sure the same receipt is not double processed by a page refresh */
		$Cancel = 1;
	}
}

if (isset($Cancel)) {
	unset($_POST['EmployeeID']);
}

// set up the form whatever

echo '<form method="post" class="noPrint" id="EmployeeMaster" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table>
		<tr>
			<td valign="top" width="15%">
				<table>'; // A new table in the first column of the main table

echo '<tr>
		<td>' . _('Date') . ':</td>
		<td><input type="text" name="RTDate" maxlength="10" size="11" value="' . $_SESSION['RTDetail']->RTDate . '" /></td>
	</tr>';
echo '<tr>
		<td>' . _('RT Ref') . ':</td>
		<td><input type="text" name="RTRef" size="11" maxlength="10" value="' . isset($_POST['RTRef']) . '" /></td>
	</tr>';
echo '</td></tr>';
echo '</table></td>';
/*close off the table in the first column */
echo '<td>';
/* Set upthe form for the transaction entry for a GL Payment Analysis item */

echo '<font size="3" color=#0908F8>' . _('Regular Time Line Entry') . '</font><table>';

/*now set up a GLCode field to select from avaialble GL accounts */
echo '<tr>
		<td>' . _('Description') . ':</td>
		<td colspan="3"><input type="text" name="RTDesc" size="42" maxlength="40" value="' . isset($_POST['RTDesc']) . '" /></td>
	</tr>';
/*now set up a GLCode field to select from avaialble GL accounts */
echo '<tr>
		<td>' . _('Enter Employee Manually') . ':</td>
		<td><input type="text" name="EmployeeManualCode" maxlength="12" size="12" value="' . isset($_POST['EmployeeManualCode']) . '" /></td>
		<td>' . _('OR') . ' ' . _('Select Employee Name') . ':</td>
		<td><select name="EmployeeID">';
$SQL = 'SELECT employeeid,
				lastname,
				firstname
			FROM prlemployeemaster
			ORDER BY employeeid';
$Result = DB_query($SQL);
if (DB_num_rows($Result) == 0) {
	echo '</select>
			</td>
		</tr>';
	prnMsg(_('No Empoloyee accounts have been set up yet'), 'warn');
} else {
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['EmployeeID']) and ($_POST['EmployeeID']) == $MyRow['employeeid']) {
			echo '<option selected value=' . $MyRow['employeeid'] . '>' . $MyRow['lastname'] . ',' . $MyRow['firstname'] . '</option>';
		} else {
			echo '<option value=' . $MyRow['employeeid'] . '>' . $MyRow['lastname'] . ',' . $MyRow['firstname'] . '</option>';
		}
	} //end while loop
	echo '</select>
				</td>
			</tr>';
}
echo '<tr>
		<td>' . _('Hours') . ':</td>
		<td colspan="3"><input type="text" name="RTHours" maxlength="12" size="12" value="' . isset($_POST['RTHours']) . '" /></td>
	</tr>';
echo '</table>';
echo '<input type="submit" name="process" value="' . _('Accept') . '" />
		<input type="submit" name="cancel" value="' . _('Cancel') . '" />';

echo '</td>
	</tr>
</table>';
/*Close the main table */

echo '<table class="selection">
		<tr>
			<th>' . _('RT Hour') . '</td>
			<th>' . _('Employee Name') . '</td>
		</tr>';

foreach ($_SESSION['RTDetail']->RTEntries as $RTItem) {
	echo '<tr>
			<td class="number">' . number_format($RTItem->RTHours, 2) . '</td>
			<td>' . $RTItem->EmployeeID . ' - ' . $RTItem->LastName . ',' . $RTItem->FirstName . '</td>
			<td><a href="' . $_SERVER['PHP_SELF'] . '?Delete=' . $RTItem->ID . '">' . _('Delete') . '</a></td>
		</tr>';
}

echo '<tr>
		<td class="number">' . number_format($_SESSION['RTDetail']->RTTotal, 2) . '</td>
	</tr>
</table>';

if (ABS($_SESSION['RTDetail']->RTTotal) > 0.001 and $_SESSION['RTDetail']->RTItemCounter > 0) {
	echo '<input type="submit" name="CommitBatch" value="' . _('Accept and Process Overtime') . '" />';
}

echo '</form>';
include('includes/footer.inc');
?>