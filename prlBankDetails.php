<?php
/* $Revision: 1.0 $ */

include('includes/session.inc');

$Title = _('Bank Details Section');

include('includes/header.inc');

echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/bank.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';

/* Fill $employeeid variable if set in either
 * $_GET or $_POST arrays
 */
if (isset($_GET['employeeid'])) {
	$employeeid = $_GET['employeeid'];
} elseif (isset($_POST['employeeid'])) {
	$employeeid = $_POST['employeeid'];
} else {
	unset($employeeid);
}
if (isset($_GET['delete'])) {
	$_POST['delete'] = $_GET['delete'];
}

//printerr($BankDetails);

if (isset($_POST['insert']) or isset($_POST['update'])) {
	/* If the user has submitted either the update or
	 * new item forms
	 */

	/*initialise no input errors assumed initially before we test  */
	$InputError = 0;

	if ($employeeid == "") /*Test to see if the employeeid has any value*/ {
		echo "<ul><li>Employeeid Not Set.</li></ul>";
		$InputError = 1;
	}

	if ($_POST['bankcode'] == "") /* Checking if Bank Details is set */ {
		prnMsg(_('bank code must not be empty'), 'error');
		$InputError = 1;
	}

	if (!is_numeric($_POST['bankcode'])) /* Check if the bank code is numeric */ {
		prnMsg(_('bank code must be numeric'), 'error');
		$InputError = 1;
	}

	if ($_POST['bankname'] == "") /* Check if the bank name is set */ {
		prnMsg(_('bankname  must not be empty'), 'error');
		$InputError = 1;
	}

	if ($_POST['branchname'] == "") /* Check if the branch name is set */ {
		prnMsg(_('branchname must not be empty'), 'error');
		$InputError = 1;
	}


	if ($InputError != 1) {
		/* If the are no errors then process the form */
		if (isset($_POST['update'])) {
			/* If we are updating an existing record */
			$SQL = "UPDATE prlbankdetails SET
					employeeid='" . DB_escape_string($_POST['employeeid']) . "',
					bankcode='" . DB_escape_string($_POST['bankcode']) . "',
					bankname='" . DB_escape_string($_POST['bankname']) . "',
					branchname='" . DB_escape_string($_POST['branchname']) . "'
					WHERE employeeid = '" . $employeeid . "'";
			$ErrMsg = _('The employee could not be updated because');
			$DbgMsg = _('The SQL that was used to update the employee but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
			prnMsg(_('The bank details record for') . ' ' . $employeeid . ' ' . _('has been updated'), 'success');
		} else if (isset($_POST['insert'])) {
			/* If we are inserting a new record */
			$SQL = "INSERT INTO prlbankdetails (employeeid,
												bankcode,
												bankname,
												branchname
											) VALUES (
												'" . $employeeid . "',
												'" . $_POST['bankcode'] . "',
												'" . $_POST['bankname'] . "',
												'" . $_POST['branchname'] . "'
											)";
			$ErrMsg = _('The bank record') . ' ' . $_POST['bankcode'] . ' ' . _('could not be added because');
			$DbgMsg = _('The SQL that was used to insert the bank record but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

			prnMsg(_('A new bank record for') . ' ' . $_POST['bankcode'] . ' ' . _('has been added to the database'), 'success');

		}
		/* End of insert or update */
		unset($_POST['employeeid']);
		unset($employeeid);
		unset($_POST['bankcode']);
		unset($_POST['bankname']);
		unset($_POST['branchname']);

	} else {
		prnMsg(_('Validation failed') . _('no updates or deletes took place'), 'warn');
	}
	/* End of if there were errors */

} else if (isset($_POST['delete']) AND $_POST['delete'] != '') {
	/* If we are deleting a record */
	$CancelDelete = 0;

	if ($CancelDelete == 0) {
		/* If ok then delete */
		$SQL = "DELETE FROM prlbankdetails WHERE employeeid ='$employeeid'";
		$Result = DB_query($SQL);
		prnMsg(_('Bank details record for ') . ' ' . $employeeid . ' ' . _('has been deleted'), 'success');
		unset($employeeid);
		unset($_SESSION['employeeid']);
	}

}
/* end delete */

if (!isset($employeeid)) {
	/* If there is no employee id set then show an input form */
	echo '<form method="post" class="noPrint" id="BankDetails" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>
			<tr>
				<td>' . _('Employee id') . ':</td>';
	/* Show a drop down list of employees with the id as value and first and last names as the text */
	echo '<td>
			<select name="employeeid">';
	$SQL = 'SELECT employeeid,firstname,lastname FROM prlemployeemaster';
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['employeeid']) and $_POST['employeeid'] == $MyRow['employeeid']) {
			echo '<option selected value=' . $MyRow['employeeid'] . '>' . $MyRow['employeeid'] . ' - ' . $MyRow['firstname'] . ' ' . $MyRow['lastname'] . '</option>';
		} else {
			echo '<option value=' . $MyRow['employeeid'] . '>' . $MyRow['employeeid'] . ' - ' . $MyRow['firstname'] . ' ' . $MyRow['lastname'] . '</option>';
		}
	}
	echo '</select>
				</td>
			</tr>';
	/* end while */
	echo '<tr>
			<td>' . _('Bank Code') . ':</td>
			<td><input type="text" name="bankcode" size="14" maxlength="12" /></td>
		</tr>
		<tr>
			<td>' . _('Bank Name') . ':</td>
			<td><input type="text" name="bankname" size="14" maxlengthy="12" /></td>
		</tr>
		<tr>
			<td>' . _('Branch Name') . ':</td>
			<td><input type="text" name="branchname" size="14" maxlength="12" /></td>
		</tr>
	</table>';
	echo '<div class="centre"><input type="submit" name="insert" value="' . _('Insert New Bank Record') . '" /></div>';
	echo '</form>';
}

if (isset($_GET['employeeid']) and (!isset($_GET['delete']))) {

	/*If we are editing an existing record then show the details */
	$SQL = "SELECT prlemployeemaster.employeeid,
					prlemployeemaster.firstname,
					prlemployeemaster.lastname ,
					prlbankdetails.bankcode,
					prlbankdetails.bankname,
					prlbankdetails.branchname
					FROM prlemployeemaster LEFT JOIN prlbankdetails
					ON prlemployeemaster.employeeid=prlbankdetails.employeeid
				    WHERE prlemployeemaster.employeeid='" . $employeeid . "'";
	$Result = DB_query($SQL);
	echo '<form method="post" class="noPrint" id="BankDetails" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>';

	while ($MyRow = DB_fetch_array($Result)) {
		$_POST['employeeid'] = $MyRow['employeeid'];
		$_POST['bankcode'] = $MyRow['bankcode'];
		$_POST['bankname'] = $MyRow['bankname'];
		$_POST['branchname'] = $MyRow['branchname'];
		echo '<tr>
				<td>' . _('Employee id') . ':</td>
				<td><select name="employeeid">';
		if (isset($_POST['employeeid']) and $_POST['employeeid'] == $MyRow['employeeid']) {
			echo '<option selected value="' . $MyRow['employeeid'] . '">' . $MyRow['employeeid'] . ' - ' . $MyRow['firstname'] . ' ' . $MyRow['lastname'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['employeeid'] . '">' . $MyRow['employeeid'] . ' - ' . $MyRow['firstname'] . ' ' . $MyRow['lastname'] . '</option>';
		}
		echo '</select>
				</td>
			</tr>';
		echo '<tr>
				<td>' . _('Bank Code') . ':</td>
				<td><input type="text" name="bankcode" size="14" maxlength="12" value="' . $_POST['bankcode'] . '" /></td>
			</tr>
			<tr>
				<td>' . _('Bank Name') . ':</td>
				<td><input type="text" name="bankname" size="14" maxlength="12" value="' . $_POST['bankname'] . '"></td>
			</tr>
			<tr>
				<td>' . _('Branch Name') . ':</td>
				<td><input type="text" name="branchname" size="14" maxlength="12" value="' . $_POST['branchname'] . '"></td>
			</tr>';

	}
	/* End of while loop */
	echo '</table>
				<div class="centre">
					<input type="submit" name="update" value="' . _('Update Bank Details') . '">
				</div>';
	echo '</form>';

}

//section for viewing, editting and deleting bank details values in the database
$SQL = "SELECT prlbankdetails.employeeid,
				prlemployeemaster.firstname,
				prlemployeemaster.lastname,
				bankcode,
				bankname,
				branchname
			FROM prlbankdetails
			INNER JOIN prlemployeemaster
				ON prlemployeemaster.employeeid=prlbankdetails.employeeid
			ORDER BY prlbankdetails.employeeid";

$Result = DB_query($SQL);

//This section caters for the html table
echo '<table class="selection">
		<tr>
			<th>' . _('Employee id') . '</th>
			<th>' . _('Employee Name') . '</th>
			<th>' . _('Bank code') . '</th>
			<th>' . _('Bank name') . '</th>
			<th>' . _('Bank branch') . '</th>
		</tr>';
$k = 0; //row colour counter

//while loop
while ($MyRow = DB_fetch_array($Result)) {

	if ($k == 1) {
		echo '<tr class="OddTableRows">';
		$k = 0;
	} else {
		echo '<tr class="EvenTableRows">';
		$k++;
	}
	echo '<td>' . $MyRow['employeeid'] . '</td>
			<td>' . $MyRow['firstname'] . ' ' . $MyRow['lastname'] . '</td>
			<td>' . $MyRow['bankcode'] . '</td>
			<td>' . $MyRow['bankname'] . '</td>
			<td>' . $MyRow['branchname'] . '</td>
			<td><a href="' . $_SERVER['PHP_SELF'] . '?employeeid=' . $MyRow['employeeid'] . '">' . _('Edit') . '</a></td>
			<td><a href="' . $_SERVER['PHP_SELF'] . '?employeeid=' . $MyRow['employeeid'] . '&delete=1">' . _('Delete') . '</a></td>
		</tr>';

} //END WHILE LIST LOOP

echo '</table>';
/* End of listing of all bank details */

include('includes/footer.inc');
?>