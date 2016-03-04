<?php

include('includes/session.inc');

$Title = _('Employee Maintenance');

include('includes/header.inc');
include('includes/CountriesArray.php');

if (isset($_GET['EmployeeID'])) {
	$EmployeeID = strtoupper($_GET['EmployeeID']);
} elseif (isset($_POST['EmployeeID'])) {
	$EmployeeID = strtoupper($_POST['EmployeeID']);
} else {
	unset($EmployeeID);
}

echo '<p class="page_title_text noPrint"><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/customer.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '<br /></p>';

if (isset($_POST['insert']) or isset($_POST['update'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit 'Insert New Employee' */
	// Checking if Employee ID is set
	if ($EmployeeID == "") {
		prnMsg(_('Employee ID Not Set'), 'error');
		$InputError = 1;
	}

	if ($_POST['LastName'] == "") {
		prnMsg(_('LastName must not be empty'), 'error');
		$InputError = 1;
	}

	if ($_POST['FirstName'] == "") {
		prnMsg(_('FirstName must not be empty'), 'error');
		$InputError = 1;
	}

	if ($InputError == 0) {
		//	$SQL_SupplierSince = FormatDateForSQL($_POST['SupplierSince']);
		if (isset($_POST['update'])) {
			$SQL = "UPDATE prlemployeemaster SET
					lastname='" . $_POST['LastName'] . "',
					firstname='" . $_POST['FirstName'] . "',
					middlename='" . $_POST['MiddleName'] . "',
					address1='" . $_POST['Address1'] . "',
					address2='" . $_POST['Address2'] . "',
					address3='" . $_POST['Address3'] . "',
					zip='" . $_POST['Zip'] . "',
					country='" . $_POST['Country'] . "',
					phone1='" . $_POST['Telephone'] . "',
					email1='" . $_POST['Email'] . "',
					id='" . $_POST['ID'] . "',
					ni='" . $_POST['NI'] . "',
					costcenterid='" . $_POST['CostCenterID'] . "',
					departmentid='" . $_POST['DepartmentID'] . "',
					position='" . $_POST['Position'] . "',
					birthdate='" . FormatDateForSQL($_POST['BirthDate']) . "',
					marital='" . $_POST['Marital'] . "',
					gender='" . $_POST['Gender'] . "',
					taxstatusid='" . $_POST['TaxStatusID'] . "',
					payperiodid='" . $_POST['PayPeriodID'] . "',
					paytype='" . $_POST['PayType'] . "',
					employmentid='" . $_POST['EmpStatID'] . "',
					active='" . $_POST['Active'] . "'
				WHERE employeeid = '" . $EmployeeID . "'";
			$ErrMsg = _('The employee could not be updated because');
			$DbgMsg = _('The SQL that was used to update the employee but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
			prnMsg(_('The employee master record for') . ' ' . $EmployeeID . ' ' . _('has been updated'), 'success');

		} else if (isset($_POST['insert'])) { //its a new employee
			$SQL = "INSERT INTO prlemployeemaster ( employeeid,
													lastname,
													firstname,
													middlename,
													address1,
													address2,
													address3,
													zip,
													country,
													phone1,
													email1,
													id,
													ni,
													costcenterid,
													departmentid,
													position,
													birthdate,
													marital,
													gender,
													taxstatusid,
													payperiodid,
													paytype,
													employmentid,
													active)
												VALUES (
													'" . $EmployeeID . "',
													'" . $_POST['LastName'] . "',
													'" . $_POST['FirstName'] . "',
													'" . $_POST['MiddleName'] . "',
													'" . $_POST['Address1'] . "',
													'" . $_POST['Address2'] . "',
													'" . $_POST['Address3'] . "',
													'" . $_POST['Zip'] . "',
													'" . $_POST['Country'] . "',
													'" . $_POST['Telephone'] . "',
													'" . $_POST['Email'] . "',
													'" . $_POST['ID'] . "',
													'" . $_POST['NI'] . "',
													'" . $_POST['CostCenterID'] . "',
													'" . $_POST['DepartmentID'] . "',
													'" . $_POST['Position'] . "',
													'" . FormatDateForSQL($_POST['BirthDate']) . "',
													'" . $_POST['Marital'] . "',
													'" . $_POST['Gender'] . "',
													'" . $_POST['TaxStatusID'] . "',
													'" . $_POST['PayPeriodID'] . "',
													'" . $_POST['PayType'] . "',
													'" . $_POST['EmpStatID'] . "',
													'" . $_POST['Active'] . "'
												)";
			$ErrMsg = _('The employee') . ' ' . $_POST['LastName'] . ' ' . _('could not be added because');
			$DbgMsg = _('The SQL that was used to insert the employee but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

			prnMsg(_('A new employee for') . ' ' . $_POST['LastName'] . ' ' . _('has been added to the database'), 'success');

			unset($EmployeeID);
			unset($_POST['LastName']);
			unset($_POST['FirstName']);
			unset($_POST['MiddleName']);
			unset($_POST['Address1']);
			unset($_POST['Address2']);
			unset($_POST['Address3']);
			unset($_POST['Zip']);
			unset($_POST['Country']);
			unset($_POST['Telephone']);
			unset($_POST['Email']);
			unset($_POST['ID']);
			unset($_POST['NI']);
			unset($_POST['CostCenterID']);
			unset($_POST['DepartmentID']);
			unset($_POST['Position']);
			unset($_POST['BirthDate']);
			unset($_POST['Marital']);
			unset($_POST['Gender']);
			unset($_POST['TaxStatusID']);
			unset($_POST['PayPeriodID']);
			unset($_POST['PayType']);
			unset($_POST['EmpStatID']);
			unset($_POST['Active']);
		}

	} else {

		prnMsg(_('Validation failed') . _('no updates or deletes took place'), 'warn');

	}

} elseif (isset($_POST['delete']) and $_POST['delete'] != '') {

	//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

	$SQL = "SELECT counterindex,overtimeid,employeeid
					FROM prlottrans
					WHERE prlottrans.employeeid='" . $EmployeeID . "'";
	$EmpDetails = DB_query($SQL);
	if (DB_num_rows($EmpDetails) > 0) {
		$CancelDelete = 1;
		exit("This employee has payroll records can not be deleted..");
	}

	if ($CancelDelete == 0) {
		$SQL = "DELETE FROM prlemployeemaster WHERE employeeid='$EmployeeID'";
		$Result = DB_query($SQL);
		prnMsg(_('Employee record for') . ' ' . $EmployeeID . ' ' . _('has been deleted'), 'success');
		unset($EmployeeID);
		unset($_SESSION['EmployeeID']);
	} //end if Delete employee
} //end of (isset($_POST['submit']))

//EmployeeID exists - either passed when calling the form or from the form itself
echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table>';

if (isset($EmployeeID)) {
	$SQL = "SELECT  employeeid,
					lastname,
					firstname,
					middlename,
					address1,
					address2,
					address3,
					zip,
					country,
					phone1,
					email1,
					id,
					ni,
					costcenterid,
					departmentid,
					birthdate,
					marital,
					gender,
					taxstatusid,
					payperiodid,
					paytype,
					employmentid,
					active
				FROM prlemployeemaster
				WHERE employeeid = '" . $EmployeeID . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$_POST['LastName'] = $MyRow['lastname'];
	$_POST['FirstName'] = $MyRow['firstname'];
	$_POST['MiddleName'] = $MyRow['middlename'];
	$_POST['Address1'] = $MyRow['address1'];
	$_POST['Address2'] = $MyRow['address2'];
	$_POST['Address3'] = $MyRow['address3'];
	$_POST['Zip'] = $MyRow['zip'];
	$_POST['Country'] = $MyRow['country'];
	$_POST['Telephone'] = $MyRow['phone1'];
	$_POST['Email'] = $MyRow['email1'];
	$_POST['ID'] = $MyRow['id'];
	$_POST['NI'] = $MyRow['ni'];
	$_POST['CostCenterID'] = $MyRow['costcenterid'];
	$_POST['DepartmentID'] = $MyRow['departmentid'];
	$_POST['Position'] = $MyRow['position'];
	$_POST['BirthDate'] = ConvertSQLDate($MyRow['birthdate']);
	$_POST['Marital'] = $MyRow['marital'];
	$_POST['Gender'] = $MyRow['gender'];
	$_POST['TaxStatusID'] = $MyRow['taxstatusid'];
	$_POST['PayPeriodID'] = $MyRow['payperiodid'];
	$_POST['PayType'] = $MyRow['paytype'];
	$_POST['EmpStatID'] = $MyRow['employmentid'];
	$_POST['Active'] = $MyRow['active'];
	echo '<input type="hidden" name="EmployeeID" value="' . $EmployeeID . '" />';
	echo '<tr>
			<td>' . _('Employee ID') . '</td>
			<td>' . $EmployeeID . '</td>
		</tr>';
} else {
	$_POST['LastName'] = '';
	$_POST['FirstName'] = '';
	$_POST['MiddleName'] = '';
	$_POST['Address1'] = '';
	$_POST['Address2'] = '';
	$_POST['Address3'] = '';
	$_POST['Zip'] = '';
	$_POST['Country'] = '';
	$_POST['Telephone'] = '';
	$_POST['Email'] = '';
	$_POST['ID'] = '';
	$_POST['NI'] = '';
	$_POST['CostCenterID'] = '';
	$_POST['DepartmentID'] = '';
	$_POST['Position'] = '';
	$_POST['BirthDate'] = date('d/m/Y');
	$_POST['Marital'] = '';
	$_POST['Gender'] = '';
	$_POST['TaxStatusID'] = '';
	$_POST['PayPeriodID'] = 0;
	$_POST['PayType'] = '';
	$_POST['EmpStatID'] = '';
	$_POST['Active'] = '';
	echo '<tr>
			<td>' . _('Employee ID') . ':</td>
			<td><input type="text" name="EmployeeID" value="" size="10" maxlength="15" /></td>
		</tr>';
}

echo '<tr>
			<td>' . _('First Name') . ':</td>
			<td><input type="text" name="FirstName" value="' . $_POST['FirstName'] . '" size="42" maxlength="40" /></td>
		</tr>';
echo '<tr>
			<td>' . _('Middle Name') . ':</td>
			<td><input type="text" name="MiddleName" value="' . $_POST['MiddleName'] . '" size="42" maxlength="40"></td>
		</tr>';
echo '<tr>
			<td>' . _('Last Name') . ':</td>
			<td><input type="text" name="LastName" value="' . $_POST['LastName'] . '" size="42" maxlength="40" /></td>
		</tr>';
echo '<tr>
			<td>' . _('Address') . ':</td>
			<td><input type="text" name="Address1" value="' . $_POST['Address1'] . '" size="42" maxlength="40" /></td>
		</tr>';
echo '<tr>
			<td></td>
			<td><input type="text" name="Address2" value="' . $_POST['Address2'] . '" size="42" maxlength="40" /></td>
		</tr>';
echo '<tr>
			<td></td>
			<td><input type="text" name="Address3" value="' . $_POST['Address3'] . '" size="42" maxlength="40" /></td>
		</tr>';
echo '<tr>
			<td>' . _('Post/Zip Code') . ':</td>
			<td><input type="text" name="Zip" value="' . $_POST['Zip'] . '" size="17" maxlength="15" /></td>
		</tr>';
echo '<tr>
		<td>' . _('Country') . ':</td>
		<td><select minlength="0" name="Country">';
foreach ($CountriesArray as $CountryEntry => $CountryName) {
	if (isset($_POST['Country']) and (strtoupper($_POST['Country']) == strtoupper($CountryName))) {
		echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName . '</option>';
	} //!isset($_POST['Address6']) and $CountryName == ""
	else {
		echo '<option value="' . $CountryName . '">' . $CountryName . '</option>';
	}
} //$CountriesArray as $CountryEntry => $CountryName
echo '</select></td>
	</tr>';
echo '<tr>
			<td>' . _('Telephone Number') . ':</td>
			<td><input type="tel" name="Telephone" value="' . $_POST['Telephone'] . '" size="17" maxlength="15" /></td>
		</tr>';
echo '<tr>
			<td>' . _('Email Address') . ':</td>
			<td><input type="email" name="Email" value="' . $_POST['Email'] . '" size="17" maxlength="78" /></td>
		</tr>';
echo '<tr>
			<td>' . _('Passport/ID Number') . ':</td>
			<td><input type="text" name="ID" value="' . $_POST['ID'] . '" size="17" maxlength="15" /></td>
		</tr>';
echo '<tr>
			<td>' . _('NI/SS Number') . ':</td>
			<td><input type="text" name="NI" value="' . $_POST['NI'] . '" size="17" maxlength="15" /></td>
		</tr>';
echo '<tr>
			<td>' . _('Cost Centre') . ':</td>
			<td><select name="CostCenterID">';
echo '<option value=""></option>';
$SQL = 'SELECT code, description FROM workcentres';
$Result = DB_query($SQL);
while ($MyRow = DB_fetch_array($Result)) {
	if ($MyRow['code'] == $_POST['CostCenterID']) {
		echo '<option selected="selected" value="' . $MyRow['code'] . '">' . $MyRow['description'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['code'] . '">' . $MyRow['description'] . '</option>';
	}
} //end while loop
echo '</select>
			<a class="ButtonLink" href="WorkCentres.php" target="_blank">' . _('Create new Cost Centres') . '</a>
			</td>
		</tr>';
echo '<tr>
			<td>' . _('Department') . ':</td>
			<td><select name="DepartmentID">';
echo '<option value=""></option>';
$SQL = 'SELECT departmentid, description FROM departments';
$Result = DB_query($SQL);
while ($MyRow = DB_fetch_array($Result)) {
	if ($MyRow['departmentid'] == $_POST['DepartmentID']) {
		echo '<option selected="selected" value="' . $MyRow['departmentid'] . '">' . $MyRow['description'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['departmentid'] . '">' . $MyRow['description'] . '</option>';
	}
} //end while loop
echo '</select>
			<a class="ButtonLink" href="Departments.php" target="_blank">' . _('Create new Departments') . '</a>
			</td>
		</tr>';
echo '<tr>
			<td>' . _('Position') . ':</td>
			<td><select name="Position">';
$SQL = 'SELECT secroleid, secrolename FROM securityroles';
$Result = DB_query($SQL);
echo '<option value=""></option>';
while ($MyRow = DB_fetch_array($Result)) {
	if ($MyRow['secroleid'] == $_POST['Position']) {
		echo '<option selected="selected" value="' . $MyRow['secroleid'] . '">' . $MyRow['secrolename'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['secroleid'] . '">' . $MyRow['secrolename'] . '</option>';
	}
} //end while loop
echo '</select>
			<a class="ButtonLink" href="WWW_Access.php" target="_blank">' . _('Create new Roles') . '</a>
			</td>
		</tr>';
echo '<tr>
			<td>' . _('Date of Birth') . ':</td>
			<td><input type="text" alt="' . $_SESSION['DefaultDateFormat'] . '" class="date" name="BirthDate" value="' . $_POST['BirthDate'] . '" size="10" maxlength="10" /></td>
		</tr>';
echo '<tr>
			<td>' . _('Marital Status') . ':</td>
			<td><select name="Marital">';
if ($_POST['Marital'] == 'Single') {
	echo '<option selected="selected" value="Single">' . _('Single') . '</option>';
	echo '<option value="Married">' . _('Married') . '</option>';
	echo '<option value="Sep/Div">' . _('Separated/Divorced') . '</option>';
	echo '<option value="Widowed">' . _('Widowed') . '</option>';
} elseif ($_POST['Marital'] == 'Married') {
	echo '<option selected="selected" value="Married">' . _('Married') . '</option>';
	echo '<option value="Single">' . _('Single') . '</option>';
	echo '<option value="Sep/Div">' . _('Separated/Divorced') . '</option>';
	echo '<option value="Widowed">' . _('Widowed') . '</option>';
} elseif ($_POST['Marital'] == 'Sep/Div') {
	echo '<option selected="selected" value="Sep/Div">' . _('Separated/Divorced') . '</option>';
	echo '<option value="Single">' . _('Single') . '</option>';
	echo '<option value="Married">' . _('Married') . '</option>';
	echo '<option value="Widowed">' . _('Widowed') . '</option>';
} elseif ($_POST['Marital'] == 'Widowed') {
	echo '<option selected="selected" value="Widowed">' . _('Widowed') . '</option>';
	echo '<option value="Single">' . _('Single') . '</option>';
	echo '<option value="Married">' . _('Married') . '</option>';
	echo '<option value="Sep/Div">' . _('Separated/Divorced') . '</option>';
} else {
	echo '<option selected="selected" value=""></option>';
	echo '<option value="Single">' . _('Single') . '</option>';
	echo '<option value="Married">' . _('Married') . '</option>';
	echo '<option value="Sep/Div">' . _('Separated/Divorced') . '</option>';
	echo '<option value="Widowed">' . _('Widowed') . '</option>';
}

echo '</select>
			</td>
		</tr>';
echo '<tr>
			<td>' . _('Gender') . ':</td>
			<td><select name="Gender">';
if ($_POST['Gender'] == 'M') {
	echo '<option selected="selected" value="M">' . _('Male') . '</option>';
	echo '<option value="F">' . _('Female') . '</option>';
} elseif ($_POST['Gender'] == 'F') {
	echo '<option selected="selected" value="F">' . _('Female') . '</option>';
	echo '<option value="M">' . _('Male') . '</option>';
} else {
	echo '<option selected="selected" value=""></option>';
	echo '<option value="F">' . _('Female') . '</option>';
	echo '<option value="M">' . _('Male') . '</option>';
}
echo '</select>
			</td>
		</tr>';
echo '<tr>
			<td>' . _('Tax Status') . ':</td>
			<td><select name="TaxStatusID">';
echo '<option value=""></option>';
$SQL = 'SELECT taxstatusid, taxstatusdescription FROM prltaxstatus';
$Result = DB_query($SQL);
while ($MyRow = DB_fetch_array($Result)) {
	if ($MyRow['taxstatusid'] == $_POST['TaxStatusID']) {
		echo '<option selected="selected" value="' . $MyRow['taxstatusid'] . '">' . $MyRow['taxstatusdescription'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['taxstatusid'] . '">' . $MyRow['taxstatusdescription'] . '</option>';
	}
} //end while loop
echo '</select>
			<a class="ButtonLink" href="prlTaxStatus.php" target="_blank">' . _('Create new Tax Status') . '</a>
			</td>
		</tr>';
echo '<tr>
			<td>' . _('Pay Period') . ':</td>
			<td><select name="PayPeriodID">';
$SQL = 'SELECT payperiodid, payperioddesc FROM prlpayperiod';
$Result = DB_query($SQL);
echo '<option value=""></option>';
while ($MyRow = DB_fetch_array($Result)) {
	if ($MyRow['payperiodid'] == $_POST['PayPeriodID']) {
		echo '<option selected="selected" value="' . $MyRow['payperiodid'] . '">' . $MyRow['payperioddesc'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['payperiodid'] . '">' . $MyRow['payperioddesc'] . '</option>';
	}
} //end while loop
echo '</select>
			<a class="ButtonLink" href="prlPayPeriod.php" target="_blank">' . _('Create/Review Pay Periods') . '</a>
			</td>
		</tr>';
echo '<tr>
			<td>' . _('Pay Type') . ':</td>
			<td><select name="PayType">';
if ($_POST['PayType'] === 0) {
	echo '<option selected="selected" value=0>' . _('Salary') . '</option>';
	echo '<option value=1>' . _('Hourly') . '</option>';
} elseif ($_POST['PayType'] === 1) {
	echo '<option selected="selected" value=1>' . _('Hourly') . '</option>';
	echo '<option value=0>' . _('Salary') . '</option>';
} else {
	echo '<option selected="selected" value=""></option>';
	echo '<option value=1>' . _('Hourly') . '</option>';
	echo '<option value=0>' . _('Salary') . '</option>';#
}
echo '</select>
			</td>
		</tr>';
echo '<tr>
			<td>' . _('Employment Status') . ':</td>
			<td><select name="EmpStatID">';
$SQL = 'SELECT employmentid, employmentdesc FROM prlemploymentstatus';
$Result = DB_query($SQL);
echo '<option value=""></option>';
while ($MyRow = DB_fetch_array($Result)) {
	if ($_POST['EmpStatID'] == $MyRow['employmentid']) {
		echo '<option selected="selected" value="' . $MyRow['employmentid'] . '">' . $MyRow['employmentdesc'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['employmentid'] . '">' . $MyRow['employmentdesc'] . '</option>';
	}
} //end while loop
echo '</select>
			<a class="ButtonLink" href="prlEmploymentStatus.php" target="_blank">' . _('Create a new Employment Status') . '</a>
			</td>
		</tr>';
echo '<tr>
			<td>' . _('Employee Status') . ':</td>
			<td><select name="Active">';
if ($_POST['Active'] === 0) {
	echo '<option selected="selected" value=0>' . _('Active') . '</option>';
	echo '<option value=1>' . _('InActive') . '</option>';
} elseif ($_POST['Active'] === 1) {
	echo '<option value=0>' . _('Active') . '</option>';
	echo '<option selected="selected" value=1>' . _('InActive') . '</option>';
} else {
	echo '<option selected="selected" value=""></option>';
	echo '<option value=0>' . _('Active') . '</option>';
	echo '<option value=1>' . _('InActive') . '</option>';
}
echo '</select>
			</td>
		</tr>';
if (!isset($EmployeeID)) {
	echo '</table>';
	echo '<div class="centre">
				<input type="submit" name="insert" value="' . _('Add These New Employee Details') . '" />
			</div>
		</form>';
} else {
	echo '</table>';
	echo '<div class="centre">
				<input type="submit" name="update" value="' . _('Update Employee') . '" />
			</div>';
	echo '<div class="centre">
				<input type="submit" name="delete" value="' . _('Delete Employee') . '" onclick="return confirm("' . _('Are you sure you wish to delete this employee?') . '");">
			</div>
		</form>';
}

include('includes/footer.inc');
?>