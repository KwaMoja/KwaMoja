<?php

include('includes/session.inc');
include('includes/prlFunctions.php');
$Title = _('Employees Loan Deduction Entry');
include('includes/header.inc');
echo '<a href="' . $RootPath . '/prlSelectLoan.php">' . _('Back to View Loan File Records') . '</a><br />';
if (isset($_GET['SelectedID'])) {
	$SelectedID = $_GET['SelectedID'];
} elseif (isset($_POST['SelectedID'])) {
	$SelectedID = $_POST['SelectedID'];
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;
	$LoanBal = $_POST['LoanAmount'] - isset($_POST['YTDDeduction']);
	if ($LoanBal < 0) {
		$InputError = 1;
		prnMsg(_('Can not post. Total Deduction is greater that Loan Amount by') . ' ' . $LoanBal);
	}

	if ($InputError != 1) {
		//printerr($_POST['LoanTableID']);
		$SQL_LoanDate = FormatDateForSQL($_POST['LoanDate']);
		$SQL_StartDeduction = FormatDateForSQL($_POST['StartDeduction']);
		if (!isset($_POST["New"])) {
			$SQL = "UPDATE prlloanfile SET
					loanfiledesc='" . DB_escape_string($_POST['LoanFileDesc']) . "',
					employeeid='" . DB_escape_string($_POST['EmployeeID']) . "',
					loandate='$SQL_LoanDate',
					loantableid='" . DB_escape_string($_POST['LoanTableID']) . "',
					loanamount='" . DB_escape_string($_POST['LoanAmount']) . "',
					amortization='" . DB_escape_string($_POST['Amortization']) . "',
					nextdeduction='$SQL_StartDeduction',
					amortization='" . DB_escape_string($_POST['Amortization']) . "',
					loanbalance='$LoanBal',
					accountcode='" . DB_escape_string($_POST['AccountCode']) . "'
				WHERE counterindex = '$SelectedID'";
			$ErrMsg = _('The employee loan could not be updated because');
			$DbgMsg = _('The SQL that was used to update the employee loan but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
			prnMsg(_('The employee loan master record for') . ' ' . isset($_POST['LoanFileId']) . ' ' . _('has been updated'), 'success');

		} else { //its a new employee
			//new record
		}

	} else {

		prnMsg(_('Validation failed') . _('no updates or deletes took place'), 'warn');

	}

} elseif (isset($_POST['delete']) AND $_POST['delete'] != '') {

	//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;
	if (isset($_SESSION['Status']) == 'Closed') {
		$CancelDelete = 1;
		prnMsg(_('Payroll has been assigned,closed and can not be deleted :') . ' Name :' . $_POST['FullName'] . ' Payroll :' . $_SESSION['PayDesc'], 'error');
	}
	if ($CancelDelete == 0) {
		$SQL = "DELETE FROM prlloanfile WHERE counterindex = '$SelectedID'";
		$Result = DB_query($SQL);
		prnMsg(_('Employee loan record for') . ' ' . isset($_POST['LoanFileId']) . ' ' . _('has been deleted'), 'success');
		unset($SelectedID);
		unset($_SESSION['SelectedID']);
		unset($LoanFileId);
		unset($_POST['LoanFileDesc']);
		unset($_POST['EmployeeID']);
		unset($_POST['LoanDate']);
		unset($_POST['LoanTableID']);
		unset($_POST['LoanAmount']);
		unset($_POST['Amortization']);
		unset($_POST['StartDeduction']);
		unset($_POST['AccountCode']);
	} //end if Delete employee
} //end of (isset($_POST['submit']))

if (!isset($SelectedID)) {
	//new loan
} else {
	//SupplierID exists - either passed when calling the form or from the form itself
	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
	echo '<table>';
	if (!isset($_POST["New"])) {
		$SQL = "SELECT  loanfileid,
						loanfiledesc,
						employeeid,
						loandate,
						loantableid,
						loanamount,
						amortization,
						nextdeduction,
						ytddeduction,
						accountcode
			FROM prlloanfile
			WHERE counterindex = '$SelectedID'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		$_POST['LoanFileDesc'] = $MyRow['loanfiledesc'];
		$_POST['EmployeeID'] = $MyRow['employeeid'];
		$_POST['LoanDate'] = ConvertSQLDate($MyRow['loandate']);
		$_POST['LoanTableID'] = $MyRow['loantableid'];
		$_POST['LoanAmount'] = $MyRow['loanamount'];
		$_POST['Amortization'] = $MyRow['amortization'];
		$_POST['StartDeduction'] = ConvertSQLDate($MyRow['nextdeduction']);
		$_POST['YTDDeduction'] = $MyRow['ytddeduction'];
		$_POST['AccountCode'] = $MyRow['accountcode'];
		echo '<input type="hidden" name="SelectedID" value="' . $SelectedID . '">';
	} else {
		// its a new supplier being added
	}
	echo '<tr><td>' . _('Description') . ":</td>
		<td><input type='Text' name='LoanFileDesc' value='" . $_POST['LoanFileDesc'] . "' SIZE=42 MAXLENGTH=40></td></tr>";
	echo '<tr><td>' . _('Employee Name') . ":</td><td><select name='EmployeeID'>";
	DB_data_seek($Result, 0);
	$SQL = 'SELECT employeeid, lastname, firstname FROM prlemployeemaster ORDER BY lastname, firstname';
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['employeeid'] == $_POST['EmployeeID']) {
			echo '<option selected="selected" value=';
		} else {
			echo '<option value=';
		}
		echo $MyRow['employeeid'] . '>' . $MyRow['lastname'] . ',' . $MyRow['firstname'];
	} //end while loop
	echo '</select></td></tr><tr><td>' . _('Loan Date:') . ' (' . $_SESSION['DefaultDateFormat'] . "):</td>
	<td><input type='Text' name='LoanDate' SIZE=12 MAXLENGTH=10 value=" . $_POST['LoanDate'] . '></td></tr>';
	echo '<tr><td>' . _('Loan Type') . ":</td><td><select name='LoanTableID'>";
	DB_data_seek($Result, 0);
	$SQL = 'SELECT loantableid, loantabledesc FROM prlloantable';
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['loantableid'] == $_POST['LoanTableID']) {
			echo '<option selected="selected" value=';
		} else {
			echo '<option value=';
		}
		echo $MyRow['loantableid'] . '>' . $MyRow['loantabledesc'];
	} //end while loop
	echo '<tr><td>' . _('Loan Amount') . ":</td>
		<td><input type='Text' name='LoanAmount' SIZE=14 MAXLENGTH=12 value='" . $_POST['LoanAmount'] . "'></td></tr>";
	echo '<tr><td>' . _('Amortization') . ":</td>
		<td><input type='Text' name='Amortization' SIZE=14 MAXLENGTH=12 value='" . $_POST['Amortization'] . "'></td></tr>";
	echo '</select></td></tr><tr><td>' . _('Start Deduction') . ' (' . $_SESSION['DefaultDateFormat'] . "):</td>
	<td><input type='Text' name='StartDeduction' SIZE=12 MAXLENGTH=10 value=" . $_POST['StartDeduction'] . '></td></tr>';
	echo '<tr><td>' . _('Account Code') . ":</td><td><select name='AccountCode'>";
	DB_data_seek($Result, 0);
	$SQL = "SELECT accountcode,
					accountname
				FROM chartmaster
				WHERE language='" . $_SESSION['ChartLanguage'] . "'";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['accountcode'] == $_POST['AccountCode']) {
			echo '<option selected="selected" value=';
		} else {
			echo '<option value=';
		}
		echo $MyRow['accountcode'] . '>' . $MyRow['accountname'];
	} //end while loop

	if (isset($_POST["New"])) {
		echo "</table><p><input type='Submit' name='submit' value='" . _('Add These New Employee Loan Details') . "'></form>";
	} else {
		echo "</table><p><input type='Submit' name='submit' value='" . _('Update Employee Loan') . "'>";
		echo '<p><font color=red><B>' . _('WARNING') . ': ' . _('There is no second warning if you hit the delete button below') . '. ' . _('However checks will be made to ensure there are no outstanding purchase orders or existing accounts payable transactions before the deletion is processed') . '<br /></FONT></B>';
		echo '<input type="Submit" name="delete" value="' . _('Delete Employee Loan') . '" onclick="return confirm("' . _('Are you sure you wish to delete this employee loan?') . '");\"></form>';
	}

} // end of main ifs

include('includes/footer.inc');
?>