<?php

include('includes/session.inc');

$Title = _('Department of Company Section');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/prlFunctions.php');

if (isset($_GET['DepartmentID'])) {
	$DepartmentID = $_GET['DepartmentID'];
} elseif (isset($_POST['DepartmentID'])) {

	$DepartmentID = $_POST['DepartmentID'];
} else {
	unset($DepartmentID);
}
echo '<a href="prlUserSettings.php">' . _('Back to User Settings') . '</a>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strpos(isset($_POST['departmentname']), '&') > 0 OR strpos(isset($_POST['departmentname']), "'") > 0) {
		$InputError = 1;
		prnMsg(_('The department name cannot contain the character') . " '&' " . _('or the character') . " '", 'error');
	}
	if (trim(isset($_POST['departmentname'])) == '') {
		$InputError = 1;
		prnMsg(_('The department name may not be empty'), 'error');
	}

	if (strlen(isset($DepartmentID)) == 0) {
		$InputError = 1;
		prnMsg(_('The department id cannot be empty'), 'error');
	}

	if ($InputError != 1) {

		if (!isset($_POST["New"])) {

			$SQL = "UPDATE prldepartment SET departmentname='" . DB_escape_string($_POST['departmentname']) . "',
						companyname='" . DB_escape_string($_POST['companyname']) . "'
						WHERE departmentid = '$DepartmentID'";

			$ErrMsg = _('The department could not be updated because');
			$DbgMsg = _('The SQL that was used to update the department table but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
			prnMsg(_('The department table master record for') . ' ' . $DepartmentID . ' ' . _('has been updated'), 'success');

		} else { //its a new cost center record

			$SQL = "INSERT INTO prldepartment (
							departmentid,
							departmentname,
							companyname)
					 VALUES ('$DepartmentID',
					 	'" . DB_escape_string($_POST['departmentname']) . "',
						'" . DB_escape_string($_POST['companyname']) . "')";
			$ErrMsg = _('The Department') . ' ' . $_POST['departmentname'] . ' ' . _('could not be added because');
			$DbgMsg = _('The SQL that was used to insert the Department table but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

			prnMsg(_('A newDepartment table for') . ' ' . $_POST['departmentname'] . ' ' . _('has been added to the database'), 'success');

			unset($DepartmentID);
			unset($_POST['departmentname']);
			unset($_POST['companyname']);

		}

	} else {

		prnMsg(_('Validation failed') . _('no updates or deletes took place'), 'warn');

	}

} elseif (isset($_POST['delete']) AND $_POST['delete'] != '') {

	//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

	// PREVENT DELETES IF DEPENDENT RECORDS FOUND
	if ($CancelDelete == 0) {
		$SQL = "DELETE FROM prldepartment WHERE departmentid='$DepartmentID'";
		$Result = DB_query($SQL);
		prnMsg(_('Department table record for') . ' ' . $DepartmentID . ' ' . _('has been deleted'), 'success');
		unset($DepartmentID);
		unset($_SESSION['DepartmentID']);
	}
}


if (!isset($DepartmentID)) {

	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';

	echo '<input type="hidden" name="New" value="Yes">';

	echo '<table>';
	echo '<tr><td>' . _('Department ID') . ":</td><td><input type='text' name='DepartmentID' SIZE=5 MAXLENGTH=4></td></tr>";
	echo '<tr><td>' . _('Department Name') . ":</td><td><input type='text' name='departmentname' SIZE=41 MAXLENGTH=40></td></tr>";
	echo '<tr><td>' . _('Company Name') . ":</td><td><input type='text' name='companyname' SIZE=41 MAXLENGTH=40></td></tr>";
	echo "</select></td></tr>'</table><p><input type='Submit' name='submit' value='" . _('Insert New Department') . "'>";
	echo '</form>';

	$SQL = "SELECT departmentid,
			     departmentname,
				 companyname
			     FROM prldepartment
			     ORDER BY departmentid";

	$ErrMsg = _('Could not get cost center because');
	$Result = DB_query($SQL, $ErrMsg);

	echo '<table border=1>';
	echo "<tr>
		<th>" . _('Department ID') . "</td>
		<th>" . _('Department Name') . "</td>
		<th>" . _('Company Name') . "</td>
	</tr>";


	$k = 0; //row colour counter
	while ($MyRow = DB_fetch_row($Result)) {

		if ($k == 1) {
			echo "<tr bgcolor='#CCCCCC'>";
			$k = 0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}
		echo '<td>' . $MyRow[0] . '</td>';
		echo '<td>' . $MyRow[1] . '</td>';
		echo '<td>' . $MyRow[2] . '</td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?&DepartmentID=' . $MyRow[0] . '">' . _('Edit') . '</a></td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?&DepartmentID=' . $MyRow[0] . '&delete=1">' . _('Delete') . '</a></td>';
		echo '</tr>';

	} //END WHILE LIST LOOP
	echo '</table><p>';


} else {

	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
	echo '<table>';

	//if (!isset($_POST["New"])) {
	if (!isset($_POST["New"])) {
		$SQL = "SELECT departmentid,
				departmentname,
				companyname
			FROM prldepartment
			WHERE departmentid = '$DepartmentID'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['departmentname'] = $MyRow['departmentname'];
		$_POST['companyname'] = $MyRow['companyname'];
		echo '<input type="hidden" name="DepartmentID" value="' . $DepartmentID . '">';

	} else {
		// its a new cost center being added
		echo '<input type="hidden" name="New" value="Yes">';
		echo '<tr><td>' . _('Department ID') . ":</td><td><input type='text' name='DepartmentID' value='$DepartmentID' SIZE=5 MAXLENGTH=4></td></tr>";
	}
	echo "<tr><td>" . _('Department Name') . ':' . "</td><td><input type='Text' name='departmentname' SIZE=41 MAXLENGTH=40 value='" . $_POST['departmentname'] . "'></td></tr>";
	echo "<tr><td>" . _('Company Name') . ':' . "</td><td><input type='Text' name='companyname' SIZE=41 MAXLENGTH=40 value='" . $_POST['companyname'] . "'></td></tr>";
	echo '</select></td></tr>';

	if (isset($_POST["New"])) {
		echo "</table><p><input type='Submit' name='submit' value='" . _('Add These New Department Details') . "'></form>";
	} else {
		echo "</table><p><input type='Submit' name='submit' value='" . _('Update Department Table') . "'>";
		echo '<p><font color=red><B>' . _('WARNING') . ': ' . _('There is no second warning if you hit the delete button below') . '. ' . _('However checks will be made to ensure before the deletion is processed') . '<br /></FONT></B>';
		echo '<input type="Submit" name="delete" value="' . _('Delete Department Table') . '" onclick="return confirm("' . _('Are you sure you wish to delete this Department?') . '");"></form>';
	}

} // end of main ifs

include('includes/footer.inc');
?>