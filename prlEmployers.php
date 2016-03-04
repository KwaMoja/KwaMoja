<?php

include('includes/session.inc');

$Title = _('Employer Section');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/prlFunctions.php');


if (isset($_GET['EmployerID'])) {
	$EmployerID = strtoupper($_GET['EmployerID']);
} elseif (isset($_POST['EmployerID'])) {
	$EmployerID = strtoupper($_POST['EmployerID']);
} else {
	unset($EmployerID);
}

$InputError = 0;

if (isset($Errors)) {
	unset($Errors);
}
$Errors = Array();
?>
<a href="prlUserSettings.php">Back to User Settings
    </a>
<?php

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$i = 1;
	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$SQL = "SELECT COUNT(employerid) FROM prlemployer WHERE employerid='" . $EmployerID . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0 and isset($_POST["New"])) {
		$InputError = 1;
		prnMsg(_('The employer number already exists in the database'), 'error');
		$Errors[$i] = 'ID';
		$i++;
	}
	if (strlen($_POST['employername']) > 40 or strlen($_POST['employername']) == 0 or $_POST['employername'] == '') {
		$InputError = 1;
		prnMsg(_('The employer name must be entered and be forty characters or less long'), 'error');
		$Errors[$i] = 'Name';
		$i++;
	}
	if (strlen($EmployerID) == 0) {
		$InputError = 1;
		prnMsg(_('The employer Code cannot be empty'), 'error');
		$Errors[$i] = 'ID';
		$i++;
	}
	if (ContainsIllegalCharacters($EmployerID)) {
		$InputError = 1;
		prnMsg(_('The employer code cannot contain any of the following characters') . " - . ' & + \" \\" . ' ' . _('or a space'), 'error');
		$Errors[$i] = 'ID';
		$i++;
	}
	if (strlen($_POST['telephone']) > 25) {
		$InputError = 1;
		prnMsg(_('The telephone number must be 25 characters or less long'), 'error');
		$Errors[$i] = 'Telephone';
		$i++;
	}
	if (strlen(isset($_POST['Fax'])) > 25) {
		$InputError = 1;
		prnMsg(_('The fax number must be 25 characters or less long'), 'error');
		$Errors[$i] = 'Fax';
		$i++;
	}
	if (strlen($_POST['Email']) > 55) {
		$InputError = 1;
		prnMsg(_('The email address must be 55 characters or less long'), 'error');
		$Errors[$i] = 'Email';
		$i++;
	}
	if (strlen($_POST['Email']) > 0 and !IsEmailAddress($_POST['Email'])) {
		$InputError = 1;
		prnMsg(_('The email address is not correctly formed'), 'error');
		$Errors[$i] = 'Email';
		$i++;
	}

	if ($InputError != 1) {

		if (!isset($_POST["New"])) {

			$SQL = "UPDATE prlemployer SET employername='" . $_POST['employername'] . "',
							address1='" . $_POST['Address1'] . "',
							address2='" . $_POST['Address2'] . "',
							address3='" . $_POST['Address3'] . "',
							email = '" . $_POST['Email'] . "',
							telephone='" . $_POST['telephone'] . "',
							fax = '" . $_POST['Fax'] . "',
							bankparticulars='" . $_POST['BankPartics'] . "',
					 		bankacct='" . $_POST['BankAct'] . "',
							country='" . $_POST['country'] . "'
						WHERE employerid = '$EmployerID'";

		}
		$SQL_dob = FormatDateForSQL($_POST['dob']);
		$ErrMsg = _('The employer could not be updated because');
		$DbgMsg = _('The SQL that was used to update the employer but failed was');

		// echo $SQL;
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

		prnMsg(_('The employer master record for') . ' ' . $EmployerID . ' ' . _('has been updated'), 'success');

	} else { //its a new employer

		$SQL = "INSERT INTO prlemployer (
							employerid,
							employername,
							address1,
							address2,
							address3,
							email,
							telephone,
							fax,
							bankparticulars,
							bankacct,
							country )
					 VALUES ('" . $EmployerID . "',
					 	'" . DB_escape_string($_POST['employername']) . "',
						'" . DB_escape_string($_POST['Address1']) . "',
						'" . DB_escape_string($_POST['Address2']) . "',
						'" . DB_escape_string($_POST['Address3']) . "',
						'" . DB_escape_string($_POST['Email']) . "',
						'" . DB_escape_string($_POST['telephone']) . "',
						'" . DB_escape_string($_POST['fax']) . "',
						'" . DB_escape_string($_POST['bankparticulars']) . "',
						'" . DB_escape_string($_POST['bankacct']) . "',
						'" . DB_escape_string($_POST['country']) . "'
						)";

		$ErrMsg = _('The employer') . ' ' . $_POST['employername'] . ' ' . _('could not be added because');
		$DbgMsg = _('The SQL that was used to insert the employer but failed was');

		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

		prnMsg(_('A new employer for') . ' ' . $_POST['employername'] . ' ' . _('has been added to the database'), 'success');

		unset($EmployerID);
		unset($_POST['employername']);
		unset($_POST['Address1']);
		unset($_POST['Address2']);
		unset($_POST['Address3']);
		unset($_POST['Email']);
		unset($_POST['telephone']);
		unset($_POST['Fax']);
		unset($_POST['bankparticulars']);
		unset($_POST['bankacct']);
		unset($_POST['country']);
	}

} else {

	prnMsg(_('Validation failed') . _('no updates or deletes took place'), 'warn');

}

if (isset($_POST['delete']) AND $_POST['delete'] != '') {

	//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'SuppTrans' , PurchOrders, SupplierContacts


	if ($CancelDelete == 0) {
		$SQL = "DELETE FROM prlemployer WHERE employerid='$EmployerID'";
		$Result = DB_query($SQL);
		prnMsg(_('Supplier record for') . ' ' . $EmployerID . ' ' . _('has been deleted'), 'success');
		unset($EmployerID);
		unset($_SESSION['EmployerID']);
	} //end if Delete employer
}


if (!isset($EmployerID)) {

	/*If the page was called without $EmployerID passed to page then assume a new employer is to be entered show a form with a Supplier Code field other wise the form showing the fields with the existing entries against the employer will show for editing with only a hidden EmployerID field*/

	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';

	echo '<input type="hidden" name="New" value="Yes">';

	echo '<table>';
	echo '<tr><td>' . _('Employer ID') . ":</td><td><input type='text' name='EmployerID' size=11 maxlength=10></td></tr>";
	echo '<tr><td>' . _('Employer Name') . ":</td><td><input type='text' name='employername' size=42 maxlength=40></td></tr>";
	echo '<tr><td>' . _('Address Line 1 (Home)') . ":</td><td><input type='text' name='Address1' size=42 maxlength=40></td></tr>";
	echo '<tr><td>' . _('Address Line 2 (City)') . ":</td><td><input type='text' name='Address2' size=42 maxlength=40></td></tr>";
	echo '<tr><td>' . _('Address Line 3 (Postal Code)') . ":</td><td><input type='text' name='Address3' size=42 maxlength=40></td></tr>";
	echo "</select></td></tr>";
	echo '<tr><td>' . _('Email Address') . ":</td><td><input type='text' name='Email' size=30 maxlength=40></td></tr>";
	echo '<tr><td>' . _('Telephone') . ":</td><td><input type='text' name='telephone' size=30 maxlength=40></td></tr>";
	echo '<tr><td>' . _('Fax') . ":</td><td><input type='text' name='fax' size=30 maxlength=40></td></tr>";
	echo '<tr><td>' . _('Bank Particulars') . ":</td><td><input type='text' name='bankparticulars' size=13 maxlength=12></td></tr>";
	echo '<tr><td>' . _('Bank Account No') . ":</td><td><input type='text' name='bankacct' size=31 maxlength=30></td></tr>";

	echo '<tr><td>' . _('Country') . ":</td><td><input type='text' name='country' size=30 maxlength=40></td></tr>";


	echo "</select></td></tr></table><p><div class='centre'><input type='Submit' name='submit' value='" . _('Insert New Employer') . "'>";
	echo '</form>';

} else {

	//EmployerID exists - either passed when calling the form or from the form itself

	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
	echo '<table>';

	if (!isset($_POST["New"])) {
		$SQL = "SELECT employerid,
							employername,
							address1,
							address2,
							address3,
							dob,
							email,
							telephone,
							fax,
							bankparticulars,
							bankacct,
							country
			FROM prlemployer
			WHERE employerid = '$EmployerID'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['employername'] = stripcslashes($MyRow['employername']);
		$_POST['Address1'] = stripcslashes($MyRow['address1']);
		$_POST['Address2'] = stripcslashes($MyRow['address2']);
		$_POST['Address3'] = stripcslashes($MyRow['address3']);
		$_POST['employersince'] = ConvertSQLDate($MyRow['employersince']);
		$_POST['Email'] = $MyRow['email'];
		$_POST['telephone'] = $MyRow['telephone'];
		$_POST['Fax'] = $MyRow['fax'];
		$_POST['bankparticulars'] = stripcslashes($MyRow['bankparticulars']);
		$_POST['bankacct'] = $MyRow['bankacct'];
		$_POST['country'] = stripcslashes($MyRow['country']);

		echo '<input type=hidden name="EmployerID" value="' . $EmployerID . '">';

	} else {
		// its a new employer being added
		echo '<input type=hidden name="New" value="Yes">';
		echo '<tr><td>' . _('Employer ID') . ':</td><td><input ' . (in_array('ID', $Errors) ? 'class="inputerror"' : '') . ' type="text" name="EmployerID" value="' . $EmployerID . '" size=12 maxlength=10></td></tr>';
	}

	echo '<tr><td>' . _('Employer Name') . ':</td><td><input ' . (in_array('Name', $Errors) ? 'class="inputerror"' : '') . ' type="text" name="employername" value="' . $_POST['employername'] . '" size=42 maxlength=40></td></tr>';
	echo '<tr><td>' . _('Address Line 1 (Home)') . ':</td><td><input type="text" name="Address1" value="' . $_POST['Address1'] . '" size=42 maxlength=40></td></tr>';
	echo '<tr><td>' . _('Address Line 2 (City)') . ':</td><td><input type="text" name="Address2" value="' . $_POST['Address2'] . '" size=42 maxlength=40></td></tr>';
	echo '<tr><td>' . _('Address Line 3 (Postal Code)') . ':</td><td><input type="text" name="Address3" value="' . isset($_POST['Address 3']) . '" size=42 maxlength=40></td></tr>';
	echo "</select></td></tr>";
	echo '<tr><td>' . _('Date of Birth') . ' (' . $_SESSION['DefaultDateFormat'] . '):</td><td><input ' . (in_array('dob', $Errors) ? 'class="inputerror"' : '') . '  size=12 maxlength=10 type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="dob" value=' . isset($_POST['dob']) . '></td></tr>';
	echo '<tr><td>' . _('Email Address') . ':</td><td><input ' . (in_array('Name', $Errors) ? 'class="inputerror"' : '') . ' type="text" name="Email" value="' . $_POST['Email'] . '" size=42 maxlength=40></td></tr>';
	echo '<tr><td>' . _('Telephone') . ':</td><td><input ' . (in_array('Name', $Errors) ? 'class="inputerror"' : '') . ' type="text" name="telephone" value="' . $_POST['telephone'] . '" size=42 maxlength=40></td></tr>';
	echo '<tr><td>' . _('Fax') . ':</td><td><input ' . (in_array('Name', $Errors) ? 'class="inputerror"' : '') . ' type="text" name="Fax" value="' . isset($_POST['Fax']) . '" size=42 maxlength=40></td></tr>';

	echo '<tr><td>' . _('Bank Particulars') . ":</td><td><input type='text' name='bankparticulars' size=13 maxlength=12 value='" . $_POST['bankparticulars'] . "'></td></tr>";
	echo '<tr><td>' . _('Bank Account No') . ":</td><td><input type='text' name='bankacct' size=31 maxlength=30 value='" . $_POST['bankacct'] . "'></td></tr>";

	echo '<tr><td>' . _('Country') . ':</td><td><input ' . (in_array('Name', $Errors) ? 'class="inputerror"' : '') . ' type="text" name="country" value="' . $_POST['country'] . '" size=42 maxlength=40></td></tr>';


	echo '</select></td></tr></table>';

	if (isset($_POST["New"])) {
		echo "<p><div class='centre'><input type='Submit' name='submit' value='" . _('Add These New Employer Details') . "'></form>";
	} else {
		echo "<br><p><div class='centre'><input type='Submit' name='submit' value='" . _('Update Employer') . "'></div><br>";
		//		echo '<p><font color=red><b>' . _('WARNING') . ': ' . _('There is no second warning if you hit the delete button below') . '. ' . _('However checks will be made to ensure there are no outstanding purchase orders or existing accounts payable transactions before the deletion is processed') . '<br></font></b>';
		prnMsg(_('WARNING') . ': ' . _('There is no second warning if you hit the delete button below') . '. ' . _('However checks will be made to ensure there are no outstanding employers before the deletion is processed'), 'Warn');
		echo "<br><div class=centre><input type='Submit' name='delete' value='" . _('Delete Employer') . "' onclick=\"return confirm('" . _('Are you sure you wish to delete this employer?') . "');\"></form>";

	}

} // end of main ifs

include('includes/footer.inc');
?>