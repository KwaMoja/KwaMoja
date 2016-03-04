<?php

include('includes/session.inc');

$Title = _('Paye Section');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/prlFunctions.php');

if (isset($_GET['PayeID'])) {
	$PayeID = strtoupper($_GET['PayeID']);
} elseif (isset($_POST['PayeID'])) {
	$PayeID = strtoupper($_POST['PayeID']);
} else {
	unset($PayeID);
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strlen(isset($_POST['companyname'])) > 50 OR strlen(isset($_POST['companyname'])) == 0) {
		$InputError = 1;
		prnMsg(_('The company name must be entered and be fifty characters or less long'), 'error');
	} elseif (strlen(isset($_POST['payeid'])) > 10) {
		$InputError = 1;
		prnMsg(_('The payeid must be entered and be ten characters or less long'), 'error');
	} elseif (strlen(isset($_POST['employeename'])) > 20) {
		$InputError = 1;
		prnMsg(_('The employee name must be entered and be twenty characters or less long'), 'error');
	} elseif (strlen($_POST['rangefrom']) > 10 OR strlen($_POST['rangefrom']) > 10) {
		$InputError = 1;
		prnMsg(_('All the range fields must be entered and be ten characters or less long'), 'error');
	} elseif (strlen(isset($_POST['Employershare'])) > 14 OR strlen(isset($_POST['EmployerShare'])) > 14) {
		$InputError = 1;
		prnMsg(_('The share fields must be entered and be ten digits or less long'), 'error');
	}

	if ($InputError != 1) {

		if (!isset($_POST["New"])) {

			$SQL = "UPDATE prlpaye SET
					companyname='" . DB_escape_string($_POST['companyname']) . "',
					rangefrom='" . $_POST['rangefrom'] . "',
					rangeto='" . DB_escape_string($_POST['rangeto']) . "',
					employershare='" . isset($_POST['employershare']) . "',
					employeeshare='" . DB_escape_string($_POST['employeeshare']) . "',
					employeeid='" . DB_escape_string($_POST['employeeid']) . "'
					WHERE payeid = '" . $PayeID . "'";

			$ErrMsg = _('The Paye could not be updated because');
			$DbgMsg = _('The SQL that was used to update the Paye but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
			prnMsg(_('The Paye master record for') . ' ' . $PayeID . ' ' . _('has been updated'), 'success');

		} else { //its a new Gross Pay
			$SQL = "INSERT INTO prlpaye (
		            payeid,
					companyname,
					rangefrom,
					rangeto,
					employershare,
					employeeshare,
					employeeid)
				 VALUES ('',
				  		'" . DB_escape_string($_POST['companyname']) . "',
					 	'" . DB_escape_string($_POST['rangefrom']) . "',
						'" . DB_escape_string($_POST['rangeto']) . "',
						'" . DB_escape_string($_POST['employershare']) . "',
						'" . DB_escape_string($_POST['employeeshare']) . "',
						'" . DB_escape_string($_POST['employeeid']) . "')";
			$ErrMsg = _('The Paye could not be added because');
			$DbgMsg = _('The SQL that was used to insert the Paye but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

			prnMsg(_('A new Paye has been added to the database'), 'success');

			unset($payeid);
			unset($_POST['companyname']);
			unset($_POST['rangefrom']);
			unset($_POST['rangeto']);
			unset($_POST['employershare']);
			unset($_POST['employeeshare']);
			unset($_POST['employeeid']);
		}

	} else {

		prnMsg(_('Validation failed') . _('no updates or deletes took place'), 'warn');

	}

} elseif (isset($_POST['delete']) AND $_POST['delete'] != '') {

	//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

	// PREVENT DELETES IF DEPENDENT RECORDS found
	if ($CancelDelete == 0) {
		$SQL = "DELETE FROM prlpaye WHERE payeid='$PayeID'";
		$Result = DB_query($SQL);
		prnMsg(_('paye record for') . ' ' . $PayeID . ' ' . _('has been deleted'), 'success');
		unset($PayeID);
		unset($_SESSION['PayeID']);
	}
}


if (!isset($PayeID)) {

	/*new hdmf*/

	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';

	echo '<input type="hidden" name="New" value="Yes">';

	echo '<table>';
	echo '<tr><td>' . _('Paye ID') . ":</td><td><input type='text' name='payeid' SIZE=15 MAXLENGTH=15></td></tr>";
	echo '<tr><td>' . _('Company Name') . ":</td><td><input type='text' name='companyname' SIZE=30 MAXLENGTH=30></td></tr>";
	echo '<tr><td>' . _('Range From') . ":</td><td><input type='text' name='rangefrom' SIZE=15 MAXLENGTH=15></td></tr>";
	echo '<tr><td>' . _('Range To') . ":</td><td><input type='text' name='rangeto' SIZE=15 MAXLENGTH=15></td></tr>";
	echo '</select></td></tr><tr><td>' . _('Employer Share') . ":</td><td><select name='employershare'>";
	echo '<option value="Fixed">' . _('Fixed');
	echo '<option value="Percentage">' . _('Percentage');
	//echo "<td><input type='text' name='employershare' SIZE=14 MAXLENGTH=12></td>";
	echo '</select></td></tr><tr><td>' . _('Employee Share') . ":</td><td><select name='employeeshare'>";
	echo '<option value="Fixed">' . _('Fixed');
	echo '<option value="Percentage">' . _('Percentage');
	//echo "<td><input type='text' name='employeeshare' SIZE=14 MAXLENGTH=12></td>";
	echo '<tr><td>' . _('Employee Name') . ":</td><td><input type='text' name='employeeid' SIZE=20 MAXLENGTH=20></td></tr>";
	echo '</select></td></tr>';
	echo "</select></td></tr></table><p><input type='Submit' name='submit' value='" . _('Insert New Paye') . "'>";
	echo '</form>';

	$SQL = "SELECT payeid,
					companyname,
					rangefrom,
					rangeto,
					employershare,
					employeeshare,
					employeeid
				FROM prlpaye
				ORDER BY payeid";

	$ErrMsg = _('Could not getPaye because');
	$Result = DB_query($SQL, $ErrMsg);

	echo '<table border=1>';
	echo "<tr>
		<th>" . _('PayeID') . "</td>
		<th>" . _('Company Name') . "</td>
		<th>" . _('Range From') . "</td>
		<th>" . _('Range To') . "</td>
		<th>" . _('Employer Share') . "</td>
		<th>" . _('Employee Share') . "</td>
		<th>" . _('Employee ID') . "</td>
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
		echo '<td>' . $MyRow[3] . '</td>';
		echo '<td>' . $MyRow[4] . '</td>';
		echo '<td>' . $MyRow[5] . '</td>';
		echo '<td>' . $MyRow[6] . '</td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?&PayeID=' . $MyRow[0] . '&edit=1">' . _('Edit') . '</a></td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?&PayeID=' . $MyRow[0] . '&delete=1">' . _('Delete') . '</a></td>';
		echo '</tr>';

	} //END WHILE LIST LOOP
	echo '</table><p>';


} else {
	//PayeID exists - either passed when calling the form or from the form itself

	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
	echo '<table>';

	//if (!isset($_POST["New"])) {
	if (!isset($_POST["New"])) {
		$SQL = "SELECT payeid,
					companyname
					rangefrom,
					rangeto,
					employershare,
					employeeshare,
					employeeid
				FROM prlpaye
				WHERE payeid='$PayeID'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		$_POST['companyname'] = isset($MyRow['companyname']);
		$_POST['rangefrom'] = $MyRow['rangefrom'];
		$_POST['rangeto'] = $MyRow['rangeto'];
		$_POST['employershare'] = $MyRow['employershare'];
		$_POST['employeename'] = $MyRow['employeeshare'];
		$_POST['employeeid'] = $MyRow['employeeid'];
		echo '<input type="hidden" name="PayeID" value="' . $PayeID . '">';

	} else {
		// its a new Pag-ibig being added
		echo '<input type="hidden" name="New" value="Yes">';
		echo '<tr><td>' . _('PayeID') . ":</td><td><input type='text' name='PayeID' value='$PayeID' SIZE=5 MAXLENGTH=4></td></tr>";
	}
	echo '<tr><td>' . _('Company Name') . ":</td><td><input type='text' name='companyname' SIZE=14 MAXLENGTH=12 value='" . $_POST['companyname'] . "'></td></tr>";
	echo '<tr><td>' . _('Range From') . ":</td><td><input type='text' name='rangefrom' SIZE=14 MAXLENGTH=12 value='" . $_POST['rangefrom'] . "'></td></tr>";
	echo '<tr><td>' . _('Range To') . ":</td><td><input type='text' name='rangeto' SIZE=14 MAXLENGTH=12 value='" . $_POST['rangeto'] . "'></td></tr>";
	echo '</select></td></tr><tr><td>' . _('Employer Share') . ":</td><td><select name='DedTypeER'>";
	if ($_POST['employershare'] == 'Fixed') {
		echo '<option selected="selected" value="Fixed">' . _('Fixed');
		echo '<option value="Percentage">' . _('Percentage');
	} elseif ($_POST['DedTypeER'] == 'Percentage') {
		echo '<option selected="selected" value="Percentage">' . _('Percentage');
		echo '<option value="Fixed">' . _('Fixed');
	} else {
		echo '<option selected="selected" value="">' . _('Select One');
		echo '<option value="Fixed">' . _('Fixed');
		echo '<option value="Percentage">' . _('Percentage');
	}

	echo '</select></td></tr><tr><td>' . _('Employee Share') . ":</td><td><select name='employeeshare'>";
	if ($_POST['employeeshare'] == 'Fixed') {
		echo '<option selected="selected" value="Fixed">' . _('Fixed');
		echo '<option value="Percentage">' . _('Percentage');
	} elseif ($_POST['DedTypeEE'] == 'Percentage') {
		echo '<option selected="selected" value="Percentage">' . _('Percentage');
		echo '<option value="Fixed">' . _('Fixed');
	} else {
		echo '<option selected="selected" value="">' . _('Select One');
		echo '<option value="Fixed">' . _('Fixed');
		echo '<option value="Percentage">' . _('Percentage');
	}

	echo '<tr><td>' . _('Employee Name') . ":</td><td><input type='text' name='employeeid' SIZE=20 MAXLENGTH=20></td></tr>";
	if (isset($_POST["New"])) {
		echo "</table><p><input type='Submit' name='submit' value='" . _('Add These New Paye Details') . "'>
		</form>";
	} else {
		echo "</table><p><input type='Submit' name='submit' value='" . _('Update Paye') . "'>";
		echo '<p><font COLOR=red><b>' . _('WARNING') . ': ' . _('There is no second warning if you hit the delete button below') . '. ' . _('However checks will be made to ensure before the deletion is processed') . '<br></font></b>';
		echo '<input type="Submit" name="delete" value="' . _('Delete Paye') . '" onclick="return confirm("' . _('Are you sure you wish to delete this Paye?') . '");\"></form>';
	}

} // end of main ifs

include('includes/footer.inc');
?>