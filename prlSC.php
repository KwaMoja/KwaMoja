<?php

include('includes/session.inc');

$Title = _('Salary Scale Section');

include('includes/header.inc');

if (isset($_GET['SalaryScaleID'])) {
	$SalaryScaleID = $_GET['SalaryScaleID'];
} elseif (isset($_POST['SalaryScaleID'])) {

	$SalaryScaleID = $_POST['SalaryScaleID'];
} else {
	unset($SalaryScaleID);
}

?>
<a href="prlUserSettings.php">Back to User Settings
    </a>
	<?php
if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strpos(isset($_POST['SalaryScaleDesc']), '&') > 0 OR strpos(isset($_POST['SalaryScaleDesc']), "'") > 0) {
		$InputError = 1;
		prnMsg(_('The salary Scale description cannot contain the character') . " '&' " . _('or the character') . " '", 'error');
	}
	if (trim(isset($_POST['SalaryScaleDesc'])) == '') {
		$InputError = 1;
		prnMsg(_('The Salary Scale description may not be empty'), 'error');
	}

	if (strlen(isset($SalaryScaleID)) == 0) {
		$InputError = 1;
		prnMsg(_('The Salary Scale Code cannot be empty'), 'error');
	}

	if ($InputError != 1) {

		if (!isset($_POST["New"])) {

			$SQL = "UPDATE salaryscale SET description='" . DB_escape_string($_POST['SalaryScaleDesc']) . "'
						WHERE code = '$SalaryScaleID'";

			$ErrMsg = _('The Salary Scale could not be updated because');
			$DbgMsg = _('The SQL that was used to update the Salary Scale table but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
			prnMsg(_('The Salary scale table master record for') . ' ' . $SalaryScaleID . ' ' . _('has been updated'), 'success');

		} else { //its a new cost center record

			$SQL = "INSERT INTO salaryscale (code,
							description)
					 VALUES ('$SalaryScaleID',
					 	'" . DB_escape_string($_POST['SalaryScaleDesc']) . "')";

			$ErrMsg = _('The Salary Scale') . ' ' . $_POST['SalaryScaleDesc'] . ' ' . _('could not be added because');
			$DbgMsg = _('The SQL that was used to insert the Salary Scale table but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

			prnMsg(_('A new Salary Scale table for') . ' ' . $_POST['SalaryScaleDesc'] . ' ' . _('has been added to the database'), 'success');

			unset($SalaryScaleID);
			unset($_POST['SalaryScaleDesc']);

		}

	} else {

		prnMsg(_('Validation failed') . _('no updates or deletes took place'), 'warn');

	}

} elseif (isset($_POST['delete']) AND $_POST['delete'] != '') {

	//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

	// PREVENT DELETES IF DEPENDENT RECORDS FOUND
	if ($CancelDelete == 0) {
		$SQL = "DELETE FROM salaryscale WHERE code='$SalaryScaleID'";
		$Result = DB_query($SQL);
		prnMsg(_('Salary Scale table record for') . ' ' . $SalaryScaleID . ' ' . _('has been deleted'), 'success');
		unset($SalaryScaleID);
		unset($_SESSION['SalaryScaleID']);
	}
}


if (!isset($SalaryScaleID)) {

	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';

	echo '<input type="hidden" name="New" value="Yes">';

	echo '<table>';
	echo '<tr><td>' . _('Salary Scale Code') . ":</td><td><input type='text' name='SalaryScaleID' SIZE=5 MAXLENGTH=4></td></tr>";
	echo '<tr><td>' . _('Salary Scale Description') . ":</td><td><input type='text' name='SalaryScaleDesc' SIZE=41 MAXLENGTH=40></td></tr>";
	echo "</select></td></tr>'</table><p><input type='Submit' name='submit' value='" . _('Insert New Salary Scale') . "'>";
	echo '</form>';

	$SQL = "SELECT code,
			     description
			     FROM salaryscale
			     ORDER BY code";

	$ErrMsg = _('Could not get cost center because');
	$Result = DB_query($SQL, $ErrMsg);

	echo '<table border=1>';
	echo "<tr>
		<th>" . _('Salary Scale Code') . "</td>
		<th>" . _('Salary Scale Description') . "</td>
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
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?&SalaryScaleID=' . $MyRow[0] . '">' . _('Edit') . '</a></td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?&SalaryScaleID=' . $MyRow[0] . '&delete=1">' . _('Delete') . '</a></td>';
		echo '</tr>';

	} //END WHILE LIST LOOP
	echo '</table><p>';


} else {

	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
	echo '<table>';

	//if (!isset($_POST["New"])) {
	if (!isset($_POST["New"])) {
		$SQL = "SELECT code,
				description
			FROM salaryscale
			WHERE code = '$SalaryScaleID'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['SalaryScaleDesc'] = $MyRow['description'];
		echo '<input type="hidden" name="SalaryScaleID" value="' . $SalaryScaleID . '">';

	} else {
		// its a new cost center being added
		echo '<input type="hidden" name="New" value="Yes">';
		echo '<tr><td>' . _('Salary Scale Code') . ":</td><td><input type='text' name='SalaryScaleID' value='$SalaryScaleID' SIZE=5 MAXLENGTH=4></td></tr>";
	}
	echo "<tr><td>" . _('Salary Scale Description') . ':' . "</td><td><input type='Text' name='SalaryScaleDesc' SIZE=41 MAXLENGTH=40 value='" . $_POST['SalaryScaleDesc'] . "'></td></tr>";
	echo '</select></td></tr>';

	if (isset($_POST["New"])) {
		echo "</table><p><input type='Submit' name='submit' value='" . _('Add These New Salary Scale Details') . "'></form>";
	} else {
		echo "</table><p><input type='Submit' name='submit' value='" . _('Update Salary Scale ') . "'>";
		echo '<p><font color=red><B>' . _('WARNING') . ': ' . _('There is no second warning if you hit the delete button below') . '. ' . _('However checks will be made to ensure before the deletion is processed') . '<br /></FONT></B>';
		echo '<input type="Submit" name="delete" value="' . _('Delete Salary Scale ') . '" onclick="return confirm("' . _('Are you sure you wish to delete this Salary Scale?') . '");"></form>';
	}

} // end of main ifs

include('includes/footer.inc');
?>