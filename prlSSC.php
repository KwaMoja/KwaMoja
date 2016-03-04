<?php

include('includes/session.inc');

$Title = _('Social Security Companies Section');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/prlFunctions.php');

if (isset($_GET['CompanyID'])) {
	$CompanyID = $_GET['CompanyID'];
} elseif (isset($_POST['CompanyID'])) {

	$CompanyID = $_POST['CompanyID'];
} else {
	unset($CompanyID);
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


	if (strlen(isset($_POST['companyid'])) > 10) {
		$InputError = 1;
		prnMsg(_('The Company Id must be entered and be ten characters or less long'), 'error');
	}

	if ($InputError != 1) {

		if (!isset($_POST["New"])) {
			$SQL = "UPDATE prlssc SET
					companyname='" . DB_escape_string($_POST['companyname']) . "',
					country='" . DB_escape_string($_POST['country']) . "',
					companycontact='" . DB_escape_string($_POST['companycontact']) . "',
					email='" . DB_escape_string($_POST['email']) . "',
					address='" . DB_escape_string($_POST['address']) . "',
					employeepercentage ='" . DB_escape_string(isset($_POST['employeepercentage '])) . "',
					employerpercentage ='" . DB_escape_string(isset($_POST['employerpercentage '])) . "'
						WHERE companyid='$CompanyID'";
			$ErrMsg = _('The Social Security Company could not be updated because');
			$DbgMsg = _('The SQL that was used to update the Basic Pay but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
			prnMsg(_('The Social Security Company master record for') . ' ' . $CompanyID . ' ' . _('has been updated'), 'success');

		} else { //its a new social security company

			$SQL = "INSERT INTO prlssc (
					companyid,
					companyname,
					country,
					companycontact,
					email,
					address,
					employeepercentage ,
					employerpercentage )
				 VALUES ('',
					 	'" . DB_escape_string($_POST['companyname']) . "',
						'" . DB_escape_string($_POST['country']) . "',
						'" . DB_escape_string($_POST['companycontact']) . "',
						'" . DB_escape_string($_POST['email']) . "',
						'" . DB_escape_string($_POST['address']) . "',
						'" . DB_escape_string($_POST['employeepercentage ']) . "',
						'" . DB_escape_string($_POST['employerpercentage ']) . "')";
			$ErrMsg = _('The Social Security Company') . ' ' . $_POST['companyname'] . ' ' . _('could not be added because');
			$DbgMsg = _('The SQL that was used to insert the Basic Pay but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

			prnMsg(_('A new Social Security Company has been added to the database'), 'success');


			unset($CompanyID);
			unset($_POST['companyname']);
			unset($_POST['country']);
			unset($_POST['companycontact']);
			unset($_POST['email']);
			unset($_POST['address']);
			unset($_POST['employeepercentage']);
			unset($_POST['employerpercentage']);
		}

	} else {

		prnMsg(_('Validation failed') . _('no updates or deletes took place'), 'warn');

	}

} elseif (isset($_POST['delete']) AND $_POST['delete'] != '') {

	//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'SuppTrans' , PurchOrders, SupplierContacts
	if ($CancelDelete == 0) {
		$SQL = "DELETE FROM prlssc WHERE companyid='$CompanyID'";
		$Result = DB_query($SQL);
		prnMsg(_('The Social Security Company record for') . ' ' . $CompanyID . ' ' . _('has been deleted'), 'success');
		unset($CompanyID);
		unset($_SESSION['CompanyID']);
	} //end if Delete paypayperiod
}


if (!isset($CompanyID)) {

	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';

	echo '<input type="hidden" name="New" value="Yes">';

	echo '<table>';
	echo '<tr><td>' . _('Company ID') . ":</td><td><input type='text' name='companyid' SIZE=10 MAXLENGTH=10></td></tr>";
	echo '<tr><td>' . _('Company Name') . ":</td><td><input type='text' name='companyname' SIZE=30 MAXLENGTH=30></td></tr>";
	echo '<tr><td>' . _('Country') . ":</td><td><input type='text' name='country' SIZE=30 MAXLENGTH=30></td></tr>";
	echo '<tr><td>' . _(' Phone Number') . ":</td><td><input type='text' name='companycontact' SIZE=30 MAXLENGTH=30></td></tr>";
	echo '<tr><td>' . _('Email Address') . ":</td><td><input type='text' name='email' SIZE=30 MAXLENGTH=30></td></tr>";
	echo '<tr><td>' . _('Location Address') . ":</td><td><input type='text' name='address' SIZE=30 MAXLENGTH=30></td></tr>";
	echo '<tr><td>' . _('% Of Employee') . ":</td><td><input type='text' name='employeepercentage' SIZE=30 MAXLENGTH=30></td></tr>";
	echo '<tr><td>' . _('% Of Employer') . ":</td><td><input type='text' name='employerpercentage' SIZE=30 MAXLENGTH=30></td></tr>";
	//	echo '</select></td></tr>';
	echo "</select></td></tr></table><p><input type='Submit' name='submit' value='" . _('Insert New Social Security Company') . "'>";
	echo '</form>';

	$SQL = "SELECT companyid,
					companyname,
					country,
					companycontact,
					email,
					address,
					employeepercentage,
					employerpercentage
				FROM prlssc
				ORDER BY companyid";

	$ErrMsg = _('Could not get Social Security Company because');
	$Result = DB_query($SQL, $ErrMsg);

	echo '<table border=1>';
	echo "<tr>
		<th>" . _('Company ID') . "</td>
		<th>" . _('Company Name') . "</td>
		<th>" . _('Country') . "</td>
		<th>" . _('Phone Number') . "</td>
		<th>" . _('Email') . "</td>
		<th>" . _('Location Address') . "</td>
		<th>" . _('Employee %') . "</td>
		<th>" . _('Employer %') . "</td>
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
		echo '<td>' . $MyRow[7] . '</td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?&CompanyID=' . $MyRow[0] . '">' . _('Edit') . '</a></td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?&CompanyID=' . $MyRow[0] . '&delete=1">' . _('Delete') . '</a></td>';
		echo '</tr>';

	} //END WHILE LIST LOOP
	echo '</table><p>';


} else {
	//Companyid exists - either passed when calling the form or from the form itself

	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
	echo '<table>';

	//if (!isset($_POST["New"])) {
	if (!isset($_POST["New"])) {
		$SQL = "SELECT companyid,
					companyname,
					country,
					companycontact,
					email,
					address,
					employeepercentage,
					employerpercentage
				FROM prlssc
				WHERE companyid='$CompanyID'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['companyid'] = $MyRow['companyid'];
		$_POST['companyname'] = $MyRow['companyname'];
		$_POST['country'] = $MyRow['country'];
		$_POST['companycontact'] = $MyRow['companycontact'];
		$_POST['email'] = $MyRow['email'];
		$_POST['address'] = $MyRow['address'];
		$_POST['employeepercentage'] = $MyRow['employeepercentage'];
		$_POST['employerpercentage'] = $MyRow['employerpercentage'];

		echo '<input type="hidden" name="CompanyID" value="' . $CompanyID . '">';

	} else {
		// its a new Social Security Company  being added
		echo '<input type="hidden" name="New" value="Yes">';
		echo '<tr><td>' . _('Company ID') . ":</td><td><input type='text' name='CompanyID' value='$CompanyID' SIZE=10 MAXLENGTH=10></td></tr>";
	}

	echo '<tr><td>' . _('Company Name') . ":</td><td><input type='text' name='companyname' SIZE=30 MAXLENGTH=30 value='" . $_POST['companyname'] . "'></td></tr>";
	echo '<tr><td>' . _('Country') . ":</td><td><input type='text' name='country' SIZE=30 MAXLENGTH=30 value='" . $_POST['country'] . "'></td></tr>";
	echo '<tr><td>' . _('Phone Number') . ":</td><td><input type='text' name='companycontact' SIZE=30 MAXLENGTH=30 value='" . $_POST['companycontact'] . "'></td></tr>";
	echo '<tr><td>' . _('Email') . ":</td><td><input type='text' name='email' SIZE=30 MAXLENGTH=30 value='" . $_POST['email'] . "'></td></tr>";
	echo '<tr><td>' . _('Location Address') . ":</td><td><input type='text' name='address' SIZE=30 MAXLENGTH=30 value='" . $_POST['address'] . "'></td></tr>";
	echo '<tr><td>' . _('% Of Employee') . ":</td><td><input type='text' name='employeepercentage' SIZE=30 MAXLENGTH=30 value='" . isset($_POST['employee%']) . "'></td></tr>";
	echo '<tr><td>' . _('% Of Employer') . ":</td><td><input type='text' name='employerpercentage' SIZE=30 MAXLENGTH=30 value='" . isset($_POST['employer%']) . "'></td></tr>";
	echo '</select></td></tr>';

	if (isset($_POST["New"])) {
		echo "</table><p><input type='Submit' name='submit' value='" . _('Add These New Social Security Company Details') . "'></form>";
	} else {
		echo "</table><p><input type='Submit' name='submit' value='" . _('Update Basic Pay') . "'>";
		echo '<p><font color=red><B>' . _('WARNING') . ': ' . _('There is no second warning if you hit the delete button below') . '. ' . _('However checks will be made to ensure before the deletion is processed') . '<br /></FONT></B>';
		echo '<input type="Submit" name="delete" value="' . _('Delete Social Security Company') . '" onclick="return confirm("' . _('Are you sure you wish to delete this Social Security Company?') . '");\"></form>';
	}

} // end of main ifs
include('includes/footer.inc');
?>