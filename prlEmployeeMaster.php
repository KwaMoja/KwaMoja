 <?php
/* $Revision: 1.0 $ */

$PageSecurity = 5;
include ('includes/session.php');

$Title = _('Employee Records Maintenance');

include ('includes/header.php');
include ('includes/SQL_CommonFunctions.php');
include ('includes/prlFunctions.php');

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/user.png" title="', $Title, '" alt="', $Title, '" />', $Title, '
	</p>';

if (isset($_GET['EmployeeID'])) {
	$EmployeeID = strtoupper($_GET['EmployeeID']);
} elseif (isset($_POST['EmployeeID'])) {
	$EmployeeID = strtoupper($_POST['EmployeeID']);
} else {
	unset($EmployeeID);
}
//printerr($EmployeeID);
if (isset($_POST['insert']) or isset($_POST['update'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit 'Insert New Employee' */
	// Checking if Employee ID is set
	if ($EmployeeID == '') {
		prnMsg(_('Employee ID Not Set.'), 'error');
		$InputError = 1;
	}

	if ($_POST['LastName'] == '') {
		prnMsg(_('LastName must not be empty.'), 'error');
		$InputError = 1;
	}

	if ($_POST['FirstName'] == '') {
		prnMsg(_('FirstName must not be empty.'), 'error');
		$InputError = 1;
	}

	if ($_POST['HourlyRate'] == 0 and $_POST['PayType'] == 1) {
		prnMsg(_('Hourly rate must not be 0.'), 'error');
		$InputError = 1;
	}

	if ($_POST['PeriodRate'] == 0) {
		$MyPeriodDesc = GetPayTypeDesc($_POST['PayType']);
		if ($MyPeriodDesc == 'Salary') {
			prnMsg(_('Pay per period must not be 0 for salaried employess.'), 'error');
			$InputError = 1;
		}
	}

	//if (!isset($_POST['New'])) {
	if (!is_date($_POST['BirthDate'])) {
		// Checking if Month, Day and Year fields have been filled
		if (($_POST['Month'] == '') or ($_POST['Day'] == '') or ($_POST['Year'] == '')) {
			prnMsg(_('The birthdate field must be a date in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
			$InputError = 1;
		} else {
			// Concatenating Month, Day and Year
			// for MySQL type Date (YYYY-MM-DD)
			$BirthDate = $_POST['Month'] . '/' . $_POST['Day'] . '/' . $_POST['Year'];
			if (!is_date($BirthDate)) {
				prnMsg(_('The birthdate field must be a date in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
				$InputError = 1;
			} else {
				$SQL_BirthDate = FormatDateForSQL($BirthDate);
			}
		}
	} else {
		$SQL_BirthDate = FormatDateForSQL($_POST['BirthDate']);
	}

	if ($InputError != 1) {

		//    $SQL_SupplierSince = FormatDateForSQL($_POST['SupplierSince']);
		if (isset($_POST['update'])) {
			$SQL = "UPDATE prlemployeemaster SET
					lastname='" . DB_escape_string($_POST['LastName']) . "',
					firstname='" . DB_escape_string($_POST['FirstName']) . "',
					middlename='" . DB_escape_string($_POST['MiddleName']) . "',
					address1='" . DB_escape_string($_POST['Address1']) . "',
					address2='" . DB_escape_string($_POST['Address2']) . "',
					city='" . DB_escape_string($_POST['City']) . "',
					state='" . DB_escape_string($_POST['State']) . "',
					zip='" . DB_escape_string($_POST['Zip']) . "',
					country='" . DB_escape_string($_POST['Country']) . "',
					userid='" . DB_escape_string($_POST['UserID']) . "',
					manager='" . DB_escape_string($_POST['Manager']) . "',
					stockid='" . DB_escape_string($_POST['StockID']) . "',
					costcenterid='" . $_POST['CostCenterID'] . "',
					position='" . DB_escape_string($_POST['Position']) . "',
					atmnumber='" . DB_escape_string($_POST['ATM']) . "',
					taxactnumber='" . DB_escape_string($_POST['TAN']) . "',
					ssnumber='" . DB_escape_string($_POST['SSS']) . "',
					hdmfnumber='" . DB_escape_string($_POST['HDMF']) . "',
					phnumber='" . DB_escape_string($_POST['PhilHealth']) . "',
					birthdate='" . $SQL_BirthDate . "',
					marital='" . $_POST['Marital'] . "',
					gender='" . $_POST['Gender'] . "',
					taxstatusid='" . $_POST['TaxStatusID'] . "',
					payperiodid='" . DB_escape_string($_POST['PayPeriodID']) . "',
					paytype='" . $_POST['PayType'] . "',
					periodrate='" . DB_escape_string($_POST['PeriodRate']) . "',
					hourlyrate='" . DB_escape_string($_POST['HourlyRate']) . "',
					normalhours='" . DB_escape_string($_POST['NormalHours']) . "',
					employmentid='" . $_POST['EmpStatID'] . "',
					active='" . $_POST['Active'] . "'
				WHERE employeeid = '" . $EmployeeID . "'";
			$ErrMsg = _('The employee could not be updated because');
			$DbgMsg = _('The SQL that was used to update the employee but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
			prnMsg(_('The employee master record for') . ' ' . $EmployeeID . ' ' . _('has been updated'), 'success');

		} elseif (isset($_POST['insert'])) { //its a new employee
			$SQL = "INSERT INTO prlemployeemaster (
					employeeid,
					lastname,
					firstname,
					middlename,
					address1,
					address2,
					city,
					state,
					zip,
					country,
					userid,
					manager,
					stockid,
					costcenterid,
					position,
					atmnumber,
					taxactnumber,
					ssnumber,
					hdmfnumber,
					phnumber,
					birthdate,
					marital,
					gender,
					taxstatusid,
					payperiodid,
					paytype,
					periodrate,
					hourlyrate,
					normalhours,
					employmentid,
					active)
				VALUES ('$EmployeeID',
					'" . DB_escape_string($_POST['LastName']) . "',
					'" . DB_escape_string($_POST['FirstName']) . "',
					'" . DB_escape_string($_POST['MiddleName']) . "',
					'" . DB_escape_string($_POST['Address1']) . "',
					'" . DB_escape_string($_POST['Address2']) . "',
					'" . DB_escape_string($_POST['City']) . "',
					'" . DB_escape_string($_POST['State']) . "',
					'" . DB_escape_string($_POST['Zip']) . "',
					'" . DB_escape_string($_POST['Country']) . "',
					'" . DB_escape_string($_POST['UserID']) . "',
					'" . DB_escape_string($_POST['Manager']) . "',
					'" . DB_escape_string($_POST['StockID']) . "',
					'" . $_POST['CostCenterID'] . "',
					'" . DB_escape_string($_POST['Position']) . "',
					'" . DB_escape_string($_POST['ATM']) . "',
					'" . DB_escape_string($_POST['TAN']) . "',
					'" . DB_escape_string($_POST['SSS']) . "',
					'" . DB_escape_string($_POST['HDMF']) . "',
					'" . DB_escape_string($_POST['PhilHealth']) . "',
					'" . $SQL_BirthDate . "',
					'" . $_POST['Marital'] . "',
					'" . $_POST['Gender'] . "',
					'" . $_POST['TaxStatusID'] . "',
					'" . DB_escape_string($_POST['PayPeriodID']) . "',
					'" . $_POST['PayType'] . "',
					'" . DB_escape_string($_POST['PeriodRate']) . "',
					'" . DB_escape_string($_POST['HourlyRate']) . "',
					'" . DB_escape_string($_POST['NormalHours']) . "',
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
			unset($_POST['City']);
			unset($_POST['State']);
			unset($_POST['Zip']);
			unset($_POST['Country']);
			unset($_POST['UserID']);
			unset($_POST['Manager']);
			unset($_POST['StockID']);
			unset($_POST['CostCenterID']);
			unset($_POST['Position']);
			unset($_POST['ATM']);
			unset($_POST['TAN']);
			unset($_POST['SSS']);
			unset($_POST['HDMF']);
			unset($_POST['PhilHealth']);
			unset($_POST['BirthDate']);
			unset($_POST['Marital']);
			unset($_POST['Gender']);
			unset($_POST['TaxStatusID']);
			unset($_POST['PayPeriodID']);
			unset($_POST['PayType']);
			unset($_POST['PeriodRate']);
			unset($_POST['HourlyRate']);
			unset($_POST['NormalHours']);
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
		prnMsg(_('employee record for') . ' ' . $EmployeeID . ' ' . _('has been deleted'), 'success');
		unset($EmployeeID);
		unset($_SESSION['EmployeeID']);
	} //end if Delete employee
	
} //end of (isset($_POST['submit']))
echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post" id="EmployeeMaster">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

echo '<fieldset>';

if (isset($EmployeeID)) {

	$SQL = "SELECT  employeeid,
					lastname,
					firstname,
					middlename,
					address1,
					address2,
					city,
					state,
					zip,
					country,
					userid,
					manager,
					stockid,
					costcenterid,
					position,
					atmnumber,
					taxactnumber,
					ssnumber,
					hdmfnumber,
					phnumber,
					birthdate,
					marital,
					gender,
					taxstatusid,
					payperiodid,
					paytype,
					periodrate,
					hourlyrate,
					normalhours,
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
	$_POST['City'] = $MyRow['city'];
	$_POST['State'] = $MyRow['state'];
	$_POST['Zip'] = $MyRow['zip'];
	$_POST['Country'] = $MyRow['country'];
	$_POST['UserID'] = $MyRow['userid'];
	$_POST['Manager'] = $MyRow['manager'];
	$_POST['StockID'] = $MyRow['stockid'];
	$_POST['CostCenterID'] = $MyRow['costcenterid'];
	$_POST['Position'] = $MyRow['position'];
	$_POST['ATM'] = $MyRow['atmnumber'];
	$_POST['TAN'] = $MyRow['taxactnumber'];
	$_POST['SSS'] = $MyRow['ssnumber'];
	$_POST['HDMF'] = $MyRow['hdmfnumber'];
	$_POST['PhilHealth'] = $MyRow['phnumber'];
	$_POST['BirthDate'] = ConvertSQLDate($MyRow['birthdate']);
	$_POST['Marital'] = $MyRow['marital'];
	$_POST['Gender'] = $MyRow['gender'];
	$_POST['TaxStatusID'] = $MyRow['taxstatusid'];
	$_POST['PayPeriodID'] = $MyRow['payperiodid'];
	$_POST['PayType'] = $MyRow['paytype'];
	$_POST['PeriodRate'] = $MyRow['periodrate'];
	$_POST['HourlyRate'] = $MyRow['hourlyrate'];
	$_POST['NormalHours'] = $MyRow['normalhours'];
	$_POST['EmpStatID'] = $MyRow['employmentid'];
	$_POST['Active'] = $MyRow['active'];

	echo '<legend>', _('Amend the details for'), ' ', $_POST['FirstName'], ' ', $_POST['LastName'], '(', $EmployeeID, ')</legend>';
	echo '<field>
			<label for="EmployeeID">', _('Employee ID'), ':</label>
			<div class="fieldtext">', $EmployeeID, '</div>
		</field>';
	echo '<input type="hidden" name="EmployeeID" value="', $EmployeeID, '" />';
} else {
	/*If the page was called without $EmployeeID passed to page then assume a new employee is to be entered show a form
	with a Employee Code field other wise the form showing the fields with the existing entries against the employee will
	show for editing with only a hidden EmployeeID field*/
	$_POST['LastName'] = '';
	$_POST['FirstName'] = '';
	$_POST['MiddleName'] = '';
	$_POST['Address1'] = '';
	$_POST['Address2'] = '';
	$_POST['City'] = '';
	$_POST['State'] = '';
	$_POST['Zip'] = '';
	$_POST['Country'] = $_SESSION['CountryOfOperation'];
	$_POST['CostCenterID'] = '';
	$_POST['Position'] = '';
	$_POST['ATM'] = '';
	$_POST['TAN'] = '';
	$_POST['SSS'] = '';
	$_POST['HDMF'] = '';
	$_POST['PhilHealth'] = '';
	$_POST['BirthDate'] = date($_SESSION['DefaultDateFormat']);
	$_POST['Marital'] = 'Single';
	$_POST['Gender'] = 'M';
	$_POST['TaxStatusID'] = '';
	$_POST['PayPeriodID'] = '';
	$_POST['PayType'] = '';
	$_POST['PeriodRate'] = 0;
	$_POST['HourlyRate'] = 0;
	$_POST['EmpStatID'] = '';
	$_POST['Active'] = '';

	echo '<legend>', _('Create new employee record'), '</legend>';

	echo '<field>
			<label for="EmployeeID">', _('Employee ID'), ':</label>
			<input type="text" required="required" autofocus="autofocus" name="EmployeeID" size="11" maxlength="10" />
			<fieldhelp>', _('A unique alpha numeric code to identify this employee. Up to 10 characters can be used'), '</fieldhelp>
		</field>';
}
echo '<field>
		<label for="LastName">', _('Last Name'), ':</label>
		<input type="text" name="LastName" autofocus="autofocus" size="42" maxlength="40" value="', $_POST['LastName'], '" />
		<fieldhelp>', _('The last, or family name of this employee'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="FirstName">', _('First Name'), ':</label>
		<input type="text" name="FirstName" size="42" maxlength="40" value="', $_POST['FirstName'], '" />
		<fieldhelp>', _('The first name of this employee.'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="MiddleName">', _('Middle Name'), ':</label>
		<input type="text" name="MiddleName" size="42" maxlength="40" value="', $_POST['MiddleName'], '" />
		<fieldhelp>', _('If the employee has a middle name enter it here.'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="Address1">', _('Address'), ':</label>
		<input type="text" name="Address1" size="42" maxlength="40" value="', $_POST['Address1'], '" />
		<fieldhelp>', _('The first line of the employees address.'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="Address2">&nbsp;</label>
		<input type="text" name="Address2" size="42" maxlength="40" value="', $_POST['Address2'], '" />
		<fieldhelp>', _('The second line of the employees address.'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="City">&nbsp;</label>
		<input type="text" name="City" size="42" maxlength="40" value="', $_POST['City'], '" />
		<fieldhelp>', _('The third line of the employees address.'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="State">&nbsp;</label>
		<input type="text" name="State" size="22" maxlength="20" value="', $_POST['State'], '" />
		<fieldhelp>', _('The fourth line of the employees address.'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="Zip">', _('Postal/Zip Code'), ':</label>
		<input type="text" name="Zip" size="17" maxlength="15" value="', $_POST['Zip'], '" />
		<fieldhelp>', _('The post code of the employee'), '</fieldhelp>
	</field>';

include ('includes/CountriesArray.php');
echo '<field>
		<label for="Country">', _('Country'), ':</label>
		<select required="required" name="Country">';
foreach ($CountriesArray as $CountryEntry => $CountryName) {
	if (isset($_POST['Country']) and ($_POST['Country'] == $CountryEntry)) {
		echo '<option selected="selected" value="', $CountryEntry, '">', $CountryName, '</option>';
	} else {
		echo '<option value="', $CountryEntry, '">', $CountryName, '</option>';
	}
}
echo '</select>
	<fieldhelp>', _('The country in which this employee resides.'), '</fieldhelp>
</field>';

echo '<field>
		<label for="UserID">', _('KwaMoja User'), ':</label>
		<select name="UserID">';
if ($_POST['UserID'] == '') {
	echo '<option selected="selected" value="">', _('Not a KwaMoja User'), '</option>';
} else {
	echo '<option value="">', _('Not a KwaMoja User'), '</option>';
}
$UsersResult = DB_query("SELECT userid, realname FROM www_users");
while ($MyRow = DB_fetch_array($UsersResult)) {
	if ($_POST['UserID'] == $MyRow['userid']) {
		echo '<option selected="selected" value="', $MyRow['userid'], '">', $MyRow['realname'], '</option>';
	} else {
		echo '<option value="', $MyRow['userid'], '">', $MyRow['realname'], '</option>';
	}
}
echo '</select>
		<fieldhelp>', _('Select the employees KwaMoja account so that when the user logs in to enter a time sheet the system knows which employee record to use'), '</fieldhelp>
	</field>';

echo '<field>
			<label for="Manager">', _('Manager'), ':</label>
			<select name="Manager">';

$ManagersResult = DB_query("SELECT employeeid,
										CONCAT(firstname, ' ', lastname) AS managername
									FROM prlemployeemaster
									WHERE employeeid != '" . $EmployeeID . "'
										ORDER BY lastname");
if ($_POST['Manager'] == '') {
	echo '<option selected="selected" value="0">', _('Not Managed'), '</option>';
} else {
	echo '<option value="0">', _('Not Managed'), '</option>';
}
while ($MyRow = DB_fetch_array($ManagersResult)) {
	if ($_POST['Manager'] == $MyRow['employeeid']) {
		echo '<option selected="selected" value="', $MyRow['employeeid'], '">', $MyRow['managername'], '</option>';
	} else {
		echo '<option value="', $MyRow['employeeid'], '">', $MyRow['managername'], '</option>';
	}
}

echo '</select>
		</field>';

$SQL = "SELECT code, description FROM workcentres";
$Result = DB_query($SQL);
echo '<field>
		<label for="CostCenterID">', _('Cost Centre'), ':</label>
		<select name="CostCenterID">';
while ($MyRow = DB_fetch_array($Result)) {
	if ($MyRow['code'] == $_POST['CostCenterID']) {
		echo '<option selected="selected" value="', $MyRow['code'], '">', $MyRow['description'], '</option>';
	} else {
		echo '<option value="', $MyRow['code'], '">', $MyRow['description'], '</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>', _('The cost centre to which this employees costs are apportioned.'), '</fieldhelp>
</field>';

echo '<field>
		<label for="StockID">', _('Labour Type'), ':</label>
		<select name="StockID">
			<option value=""></option>';

$LabourTypeItemsResult = DB_query("SELECT stockid,
											description
										FROM stockmaster
										INNER JOIN stockcategory
											ON stockmaster.categoryid = stockcategory.categoryid
										INNER JOIN stocktypes
											ON stockcategory.stocktype=stocktypes.type
											AND stocktypes.physicalitem=0
										ORDER BY description");
while ($MyRow = DB_fetch_array($LabourTypeItemsResult)) {
	if ($_POST['StockID'] == $MyRow['stockid']) {
		echo '<option selected="selected" value="', $MyRow['stockid'], '">', $MyRow['description'], '</option>';
	} else {
		echo '<option value="', $MyRow['stockid'] . '">', $MyRow['description'], '</option>';
	}
}

echo '</select>
	<fieldhelp>', _('Select the stock item that the labour costs for this employee are to be aportioned to'), '</fieldhelp>
</field>';

echo '<field>
		<label for="Position">', _('Position'), ':</label>
		<input type="text" name="Position" size="42" maxlength="40" value="', $_POST['Position'], '" />
		<fieldhelp>', _('The job role that this employees does.'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="ATM">', _('ATM Number'), ':</label>
		<input type="text" name="ATM" size="22" maxlength="20" value="', $_POST['ATM'], '" />
		<fieldhelp>', _('The ATM Number for this employee.'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="TAN">', _('Tax Account #'), ':</label>
		<input type="text" name="TAN" size="22" maxlength="20" value="', $_POST['TAN'], '" />
		<fieldhelp>', _('The Tax Account Number for this employee.'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="SSS">', _('SSS #'), ':</label>
		<input type="text" name="SSS" size="22" maxlength="20" value="', $_POST['SSS'], '" />
		<fieldhelp>', _('The SSS Number for this employee.'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="HDMF">', _('Pag-ibig #'), ':</label>
		<input type="text" name="HDMF" size="22" maxlength="20" value="', $_POST['HDMF'], '" />
		<fieldhelp>', _('The Pag-ibig Number for this employee.'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="PhilHealth">', _('PhilHealth #'), ':</label>
		<input type="text" name="PhilHealth" size="22" maxlength="20" value="', $_POST['PhilHealth'], '" />
		<fieldhelp>', _('The PhilHealth Number for this employee.'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="BirthDate">', _('Date of Birth'), ' (', $_SESSION['DefaultDateFormat'], '):</label>
		<input size="12" required="required" maxlength="10" type="text" class="date" name="BirthDate" value="', $_POST['BirthDate'], '" />
		<fieldhelp>', _('The date of birth of this employee.'), '</fieldhelp>
	</field>';

$MaritalStatus = array('Single' => _('Single'), 'Married' => _('Married'), 'Sep/Div' => _('Separated/Divorced'), 'Widowed' => _('Widowed'));
echo '<field>
		<label for="Marital">', _('Marital Status'), ':</label>
		<select name="Marital">';
foreach ($MaritalStatus as $Key => $Value) {
	if ($Key == $_POST['Marital']) {
		echo '<option selected="selected" value="', $Key, '">', $Value, '</option>';
	} else {
		echo '<option value="', $Key, '">', $Value, '</option>';
	}
}
echo '</select>
	<fieldhelp>', _('The employees marital status.'), '</fieldhelp>
</field>';

$Gender = array('M' => _('Male'), 'F' => _('Female'));
echo '<field>
		<label for="Gender">', _('Gender'), ':</label>
		<select name="Gender">';
foreach ($Gender as $Key => $Value) {
	if ($Key == $_POST['Gender']) {
		echo '<option selected="selected" value="', $Key, '">', $Value, '</option>';
	} else {
		echo '<option value="', $Key, '">', $Value, '</option>';
	}
}
echo '</select>
	<fieldhelp>', _('The employees gender.'), '</fieldhelp>
</field>';

$SQL = "SELECT taxstatusid, taxstatusdescription FROM prltaxstatus";
$Result = DB_query($SQL);
echo '<field>
			<label for="TaxStatusID">', _('Tax Status'), ':</label>
			<select name="TaxStatusID">';
while ($MyRow = DB_fetch_array($Result)) {
	if ($_POST['TaxStatusID'] == $MyRow['taxstatusid']) {
		echo '<option selected="selected" value=', $MyRow['taxstatusid'], '>', $MyRow['taxstatusdescription'], '</option>';
	} else {
		echo '<option value=', $MyRow['taxstatusid'], '>', $MyRow['taxstatusdescription'], '</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>', _('The tax status of this employee.'), '</fieldhelp>
</field>';

$SQL = "SELECT payperiodid, payperioddesc FROM prlpayperiod";
$Result = DB_query($SQL);
echo '<field>
		<label for="PayPeriodID">', _('Pay Period'), ':</label>
		<select name="PayPeriodID">';
while ($MyRow = DB_fetch_array($Result)) {
	if ($_POST['PayPeriodID'] == $MyRow['payperiodid']) {
		echo '<option selected="selected" value=', $MyRow['payperiodid'], '>', $MyRow['payperioddesc'], '</option>';
	} else {
		echo '<option value=', $MyRow['payperiodid'], '>', $MyRow['payperioddesc'], '</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>', _('What period is this employee for.'), '</fieldhelp>
</field>';

$PayType = array(0 => _('Salary'), 1 => _('Hourly'));
echo '<field>
		<label for="PayType">', _('Pay Type'), ':</label>
		<select name="PayType">';
foreach ($PayType as $Key => $Value) {
	if ($Key == $_POST['PayType']) {
		echo '<option selected="selected" value="', $Key, '">', $Value, '</option>';
	} else {
		echo '<option value="', $Key, '">', $Value, '</option>';
	}
}
echo '</select>
	<fieldhelp>', _('Is the employee salaried or paid by the hour.'), '</fieldhelp>
</field>';

echo '<field>
		<label for="PeriodRate">', _('Pay per period'), ':</label>
		<input type="text" class="number" name="PeriodRate" size="14" maxlength="12" value="', $_POST['PeriodRate'], '" />
		<fieldhelp>', _('The rate of pay per pay period, if the employee is salaried.'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="NormalHours">', _('Normal Weekly Hours'), ':</label>
		<input class="number" type="text" name="NormalHours" value="', $_POST['NormalHours'], '" size="3" maxlength="2" /></td>
		<fieldhelp>', _('Enter the employee\'s normal hours per week'), '</fieldhelp
	</field>';

echo '<field>
		<label for="HourlyRate">', _('Pay per Hour'), ':</label>
		<input type="text" class="number" name="HourlyRate" size="14" maxlength="12" value="', $_POST['HourlyRate'], '" />
		<fieldhelp>', _('Base Rate for Absent,Late and Overtime'), '</fieldhelp>
	</field>';

$SQL = "SELECT employmentid, employmentdesc FROM prlemploymentstatus";
$Result = DB_query($SQL);
echo '<field>
		<label for="EmpStatID">', _('Employment Status'), ':</label>
		<select name="EmpStatID">';
while ($MyRow = DB_fetch_array($Result)) {
	if ($_POST['EmpStatID'] == $MyRow['employmentid']) {
		echo '<option selected="selected" value=', $MyRow['employmentid'], '>', $MyRow['employmentdesc'], '</option>';
	} else {
		echo '<option value=', $MyRow['employmentid'], '>', $MyRow['employmentdesc'], '</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>', _('Select the employment status of this employee.'), '</fieldhelp>
</field>';

$EmploymentStatus = array(0 => _('Active'), 1 => _('InActive'));
echo '<field>
		<label for="Active">', _('Employment Status'), ':</label>
		<select name="Active">';
foreach ($EmploymentStatus as $Key => $Value) {
	if ($Key == $_POST['Active']) {
		echo '<option selected="selected" value="', $Key, '">', $Value, '</option>';
	} else {
		echo '<option value="', $Key, '">', $Value, '</option>';
	}
}
echo '</select>
	<fieldhelp>', _('If the employee is not to be part of the payroll select InActive, otherwise select Active.'), '</fieldhelp>
</field>';

echo '</fieldset>';

if (isset($EmployeeID)) {
	echo '<div class="centre">
			<input type="submit" name="update" value="', _('Update employee details'), '">
		</div>';
} else {
	echo '<div class="centre">
			<input type="submit" name="insert" value="', _('Insert New Employee'), '">
		</div>';
}

echo '</form>';

include ('includes/footer.php');
?>