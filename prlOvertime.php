<?php

include('includes/session.inc');

$Title = _('Overtime Section');

include('includes/header.inc');

if (isset($_GET['OverTimeID'])) {
	$OverTimeID = $_GET['OverTimeID'];
} elseif (isset($_POST['OverTimeID'])) {

	$OverTimeID = $_POST['OverTimeID'];
} else {
	unset($OverTimeID);
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

	if (strpos($_POST['OverTimeDesc'], '&') > 0 OR strpos($_POST['OverTimeDesc'], "'") > 0) {
		$InputError = 1;
		prnMsg(_('The overtime description cannot contain the character') . " '&' " . _('or the character') . " '", 'error');
	}
	if (trim($_POST['OverTimeDesc']) == '') {
		$InputError = 1;
		prnMsg(_('The overtime description may not be empty'), 'error');
	}
	if (is_numeric($_POST['OverTimeDesc'])) /* Check if the bank code is numeric */ {
		prnMsg(_('Over Time Description must be Character'), 'error');
		$InputError = 1;
	}

	if (strlen($OverTimeID) == 0) {
		$InputError = 1;
		prnMsg(_('The overtime Code cannot be empty'), 'error');
	}

	if ($InputError != 1) {

		if (!isset($_POST["New"])) {

			$SQL = "UPDATE prlovertimetable SET overtimedesc='" . DB_escape_string($_POST['OverTimeDesc']) . "',
							overtimerate='" . DB_escape_string($_POST['OverTimeRate']) . "'
						WHERE OverTimeID = '$OverTimeID'";

			$ErrMsg = _('The overtime could not be updated because');
			$DbgMsg = _('The SQL that was used to update the overtime but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
			prnMsg(_('The overtime master record for') . ' ' . $OverTimeID . ' ' . _('has been updated'), 'success');

		} else { //its a new overtime

			$SQL = "INSERT INTO prlovertimetable (OverTimeID,
							overtimedesc,
							overtimerate)
					 VALUES ('$OverTimeID',
					 	'" . DB_escape_string($_POST['OverTimeDesc']) . "',
						'" . DB_escape_string($_POST['OverTimeRate']) . "')";

			$ErrMsg = _('The overtime') . ' ' . $_POST['OverTimeDesc'] . ' ' . _('could not be added because');
			$DbgMsg = _('The SQL that was used to insert the overtime but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

			prnMsg(_('A new overtime for') . ' ' . $_POST['OverTimeDesc'] . ' ' . _('has been added to the database'), 'success');

			unset($OverTimeID);
			unset($_POST['OverTimeDesc']);
			unset($_POST['OverTimeRate']);

		}

	} else {

		prnMsg(_('Validation failed') . _('no updates or deletes took place'), 'warn');

	}

} elseif (isset($_POST['delete']) AND $_POST['delete'] != '') {

	//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'SuppTrans' , PurchOrders, SupplierContacts
	if ($CancelDelete == 0) {
		$SQL = "DELETE FROM prlovertimetable WHERE OverTimeID='$OverTimeID'";
		$Result = DB_query($SQL);
		prnMsg(_('Overtime record for') . ' ' . $OverTimeID . ' ' . _('has been deleted'), 'success');
		unset($OverTimeID);
		unset($_SESSION['OverTimeID']);
	} //end if Delete paypayperiod
}


if (!isset($OverTimeID)) {

	/*If the page was called without $SupplierID passed to page then assume a new supplier is to be entered show a form with a Supplier Code field other wise the form showing the fields with the existing entries against the supplier will show for editing with only a hidden SupplierID field*/

	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';

	echo '<input type="hidden" name="New" value="Yes">';

	echo '<table>';
	echo '<tr><td>' . _('Overtime Code') . ":</td><td><input type='text' name='OverTimeID' SIZE=5 MAXLENGTH=4></td></tr>";
	echo '<tr><td>' . _('Pay Description') . ":</td><td><input type='text' name='OverTimeDesc' SIZE=41 MAXLENGTH=40></td></tr>";
	echo '<tr><td>' . _('Overtime Rate') . ":</td><td><input type='text' name='OverTimeRate' SIZE=7 MAXLENGTH=6></td></tr>";
	//	echo '</select></td></tr>';
	echo "</select></td></tr></table><p><input type='Submit' name='submit' value='" . _('Insert New Overtime') . "'>";
	echo '</form>';

	$SQL = "SELECT OverTimeID,
			overtimedesc,
			overtimerate
			FROM prlovertimetable
			ORDER BY OverTimeID";

	$ErrMsg = _('Could not get overtime because');
	$Result = DB_query($SQL, $ErrMsg);

	echo '<table border=1>';
	echo "<tr>
		<th>" . _('Overtime Code') . "</td>
		<th>" . _('Overtime Description') . "</td>
		<th>" . _('Overtime Rate') . "</td>
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
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?&OverTimeID=' . $MyRow[0] . '">' . _('Edit') . '</a></td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?&OverTimeID=' . $MyRow[0] . '&delete=1">' . _('Delete') . '</a></td>';
		echo '</tr>';

	} //END WHILE LIST LOOP
	echo '</table><p>';


} else {
	//OverTimeID exists - either passed when calling the form or from the form itself

	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
	echo '<table>';

	//if (!isset($_POST["New"])) {
	if (!isset($_POST["New"])) {
		$SQL = "SELECT OverTimeID,
				overtimedesc,
				overtimerate
			FROM prlovertimetable
			WHERE OverTimeID = '$OverTimeID'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['OverTimeDesc'] = $MyRow['overtimedesc'];
		$_POST['OverTimeRate'] = $MyRow['overtimerate'];
		echo '<input type="hidden" name="OverTimeID" value="' . $OverTimeID . '">';

	} else {
		// its a new overtime being added
		echo '<input type="hidden" name="New" value="Yes">';
		echo '<tr><td>' . _('Overtime Code') . ":</td><td><input type='text' name='OverTimeID' value='$OverTimeID' SIZE=5 MAXLENGTH=4></td></tr>";
	}
	echo "<tr><td>" . _('Overtime Description') . ':' . "</td><td><input type='Text' name='OverTimeDesc' SIZE=41 MAXLENGTH=40 value='" . $_POST['OverTimeDesc'] . "'></td></tr>";
	echo "<tr><td>" . _('Overtime Rate') . ':' . "</td><td><input type='Text' name='OverTimeRate' SIZE=4 MAXLENGTH=6 value='" . $_POST['OverTimeRate'] . "'></td></tr>";
	echo '</select></td></tr>';

	if (isset($_POST["New"])) {
		echo "</table><p><input type='Submit' name='submit' value='" . _('Add These New overtime Details') . "'></form>";
	} else {
		echo "</table><p><input type='Submit' name='submit' value='" . _('Update overtime') . "'>";
		echo '<p><font color=red><b>' . _('WARNING') . ': ' . _('There is no second warning if you hit the delete button below') . '. ' . _('However checks will be made to ensure before the deletion is processed') . '<br /></FONT></B>';
		echo '<input type="Submit" name="delete" value="' . _('Delete overtime') . '" onclick="return confirm("' . _('Are you sure you wish to delete this overtime?') . '"");\"></form>';
	}

} // end of main ifs

include('includes/footer.inc');
?>