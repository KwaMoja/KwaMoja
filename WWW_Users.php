<?php
include ('includes/session.php');

include ('includes/MainMenuLinksArray.php');

if (isset($_POST['UserID']) and isset($_POST['ID'])) {
	if ($_POST['UserID'] == $_POST['ID']) {
		if (isset($_POST['UserLanguage']) and !CheckLanguageChoice($_POST['UserLanguage'])) {
			$_POST['UserLanguage'] = $DefaultLanguage;
		}
	} else if (isset($_POST['UserLanguage']) and !CheckLanguageChoice($_POST['UserLanguage'])) {
		$_POST['UserLanguage'] = $DefaultLanguage;
	}
}

$PDFLanguages = array(_('Latin Western Languages'), _('Eastern European Russian Japanese Korean Vietnamese Hebrew Arabic Thai'), _('Chinese'), _('Free Serif'));

$Title = _('User Maintenance');
/* Manual links before header.php */
$ViewTopic = 'GettingStarted';
$BookMark = 'UserMaintenance';
include ('includes/header.php');
include ('includes/SQL_CommonFunctions.php');

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/group_add.png" title="', _('Search'), '" alt="" />', ' ', $Title, '
	</p>';

if ($AllowDemoMode == true) {
	prnMsg(_('The the system is in demo mode and the security model administration is disabled'), 'warn');
	include ('includes/footer.php');
	exit;
}

// Make an array of the security roles
$SQL = "SELECT secroleid,
				secrolename
		FROM securityroles
		ORDER BY secrolename";

$SecResult = DB_query($SQL);
$SecurityRoles = array();
// Now load it into an a ray using Key/Value pairs
while ($SecRow = DB_fetch_row($SecResult)) {
	$SecurityRoles[$SecRow[0]] = $SecRow[1];
}
DB_free_result($SecResult);

if (isset($_GET['SelectedUser'])) {
	$SelectedUser = $_GET['SelectedUser'];
} elseif (isset($_POST['SelectedUser'])) {
	$SelectedUser = $_POST['SelectedUser'];
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	 ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (mb_strlen($_POST['UserID']) < 3) {
		$InputError = 1;
		prnMsg(_('The user ID entered must be at least 3 characters long'), 'error');
	} elseif (ContainsIllegalCharacters($_POST['UserID'])) {
		$InputError = 1;
		prnMsg(_('User names cannot contain any of the following characters') . " - ' &amp; + \" \\ " . _('or a space'), 'error');
	} elseif (mb_strlen($_POST['Password']) < 5) {
		if (!$SelectedUser) {
			$InputError = 1;
			prnMsg(_('The password entered must be at least 5 characters long'), 'error');
		}
	} elseif (mb_strstr($_POST['Password'], $_POST['UserID']) != False) {
		$InputError = 1;
		prnMsg(_('The password cannot contain the user id'), 'error');
	} elseif ((mb_strlen($_POST['Cust']) > 0) and (mb_strlen($_POST['BranchCode']) == 0)) {
		$InputError = 1;
		prnMsg(_('If you enter a Customer Code you must also enter a Branch Code valid for this Customer'), 'error');
	}
	//comment out except for demo!  Do not want anyone modifying demo user.
	/*
	elseif ($_POST['UserID'] == 'admin') {
	prnMsg(_('The demonstration user called demo cannot be modified.'),'error');
	$InputError = 1;
	}
	*/
	if (!isset($SelectedUser)) {
		/* check to ensure the user id is not already entered */
		$Result = DB_query("SELECT userid FROM www_users WHERE userid='" . $_POST['UserID'] . "'");
		if (DB_num_rows($Result) == 1) {
			$InputError = 1;
			prnMsg(_('The user ID') . ' ' . $_POST['UserID'] . ' ' . _('already exists and cannot be used again'), 'error');
		}
	}

	if ((mb_strlen($_POST['BranchCode']) > 0) and ($InputError != 1)) {
		// check that the entered branch is valid for the customer code
		$SQL = "SELECT custbranch.debtorno
				FROM custbranch
				WHERE custbranch.debtorno='" . $_POST['Cust'] . "'
				AND custbranch.branchcode='" . $_POST['BranchCode'] . "'";

		$ErrMsg = _('The check on validity of the customer code and branch failed because');
		$DbgMsg = _('The SQL that was used to check the customer code and branch was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

		if (DB_num_rows($Result) == 0) {
			prnMsg(_('The entered Branch Code is not valid for the entered Customer Code'), 'error');
			$InputError = 1;
		}
	}

	/* Make a comma separated list of modules allowed ready to update the database*/
	$i = 0;
	$ModulesAllowed = '';
	while ($i < count($_SESSION['ModuleList'])) {
		$FormVbl = 'Module_' . $i;
		$ModulesAllowed.= $_POST[($FormVbl) ] . ',';
		++$i;
	}
	$_POST['ModulesAllowed'] = $ModulesAllowed;

	if (isset($SelectedUser) and $InputError != 1) {

		/*SelectedUser could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		if (!isset($_POST['Cust']) or $_POST['Cust'] == NULL or $_POST['Cust'] == '') {

			$_POST['Cust'] = '';
			$_POST['BranchCode'] = '';
		}
		$UpdatePassword = '';
		if ($_POST['Password'] != '') {
			$UpdatePassword = "password='" . CryptPass($_POST['Password']) . "',";
		}

		if ($SelectedUser == $_SESSION['UserID']) {
			switch ($_POST['FontSize']) {
				case 0:
					$_SESSION['ScreenFontSize'] = '8pt';
				break;
				case 1:
					$_SESSION['ScreenFontSize'] = '10pt';
				break;
				case 2:
					$_SESSION['ScreenFontSize'] = '12pt';
				break;
				default:
					$_SESSION['ScreenFontSize'] = '10pt';
			}
		}
		$SQL = "UPDATE www_users SET realname='" . $_POST['RealName'] . "',
						customerid='" . $_POST['Cust'] . "',
						phone='" . $_POST['Phone'] . "',
						email='" . $_POST['Email'] . "',
						" . $UpdatePassword . "
						changepassword='" . $_POST['ChangePassword'] . "',
						branchcode='" . $_POST['BranchCode'] . "',
						supplierid='" . $_POST['SupplierID'] . "',
						salesman='" . $_POST['Salesman'] . "',
						pagesize='" . $_POST['PageSize'] . "',
						fullaccess='" . $_POST['Access'] . "',
						cancreatetender='" . $_POST['CanCreateTender'] . "',
						canentertimesheets='" . $_POST['CanEnterTimesheets'] . "',
						theme='" . $_POST['Theme'] . "',
						language ='" . $_POST['UserLanguage'] . "',
						defaultlocation='" . $_POST['DefaultLocation'] . "',
						restrictlocations='" . $_POST['RestrictLocations'] . "',
						modulesallowed='" . $ModulesAllowed . "',
						blocked='" . $_POST['Blocked'] . "',
						pdflanguage='" . $_POST['PDFLanguage'] . "',
						department='" . $_POST['Department'] . "',
						fontsize='" . $_POST['FontSize'] . "',
						defaulttag='" . $_POST['DefaultTag'] . "',
						showpagehelp='" . $_POST['ShowPageHelp'] . "',
						showfieldhelp='" . $_POST['ShowFieldHelp'] . "'
					WHERE userid = '" . $SelectedUser . "'";

		prnMsg(_('The selected user record has been updated'), 'success');
	} elseif ($InputError != 1) {

		$LocationSql = "INSERT INTO locationusers (loccode,
													userid,
													canview,
													canupd
												) VALUES (
													'" . $_POST['DefaultLocation'] . "',
													'" . $_POST['UserID'] . "',
													1,
													1
												)";
		$ErrMsg = _('The default user locations could not be processed because');
		$DbgMsg = _('The SQL that was used to create the user locations and failed was');
		$Result = DB_query($LocationSql, $ErrMsg, $DbgMsg);
		prnMsg(_('User has been authorised to use and update only his / her default location'), 'success');

		$GLAccountsSql = "INSERT INTO glaccountusers (userid,
													accountcode,
													canview,
													canupd)
											SELECT '" . $_POST['UserID'] . "',
													chartmaster.accountcode,
													1,
													1
											FROM chartmaster";

		$ErrMsg = _('The default user GL Accounts could not be processed because');
		$DbgMsg = _('The SQL that was used to create the user GL Accounts and failed was');
		$Result = DB_query($GLAccountsSql, $ErrMsg, $DbgMsg);
		prnMsg(_('User has been authorised to use and update all GL accounts'), 'success');

		$SQL = "INSERT INTO www_users (userid,
						realname,
						customerid,
						branchcode,
						supplierid,
						salesman,
						password,
						changepassword,
						phone,
						email,
						pagesize,
						fullaccess,
						cancreatetender,
						canentertimesheets,
						defaultlocation,
						restrictlocations,
						modulesallowed,
						displayrecordsmax,
						theme,
						language,
						pdflanguage,
						department,
						fontsize,
						defaulttag,
						showpagehelp,
						showfieldhelp)
					VALUES ('" . $_POST['UserID'] . "',
						'" . $_POST['RealName'] . "',
						'" . $_POST['Cust'] . "',
						'" . $_POST['BranchCode'] . "',
						'" . $_POST['SupplierID'] . "',
						'" . $_POST['Salesman'] . "',
						'" . CryptPass($_POST['Password']) . "',
						'" . $_POST['ChangePassword'] . "',
						'" . $_POST['Phone'] . "',
						'" . $_POST['Email'] . "',
						'" . $_POST['PageSize'] . "',
						'" . $_POST['Access'] . "',
						'" . $_POST['CanCreateTender'] . "',
						'" . $_POST['CanEnterTimesheets'] . "',
						'" . $_POST['DefaultLocation'] . "',
						'" . $_POST['RestrictLocations'] . "',
						'" . $ModulesAllowed . "',
						'" . $_SESSION['DefaultDisplayRecordsMax'] . "',
						'" . $_POST['Theme'] . "',
						'" . $_POST['UserLanguage'] . "',
						'" . $_POST['PDFLanguage'] . "',
						'" . $_POST['Department'] . "',
						'" . $_POST['FontSize'] . "',
						'" . $_POST['DefaultTag'] . "',
						'" . $_POST['ShowPageHelp'] . "',
						'" . $_POST['ShowFieldHelp'] . "'
						)";
		prnMsg(_('A new user record has been inserted'), 'success');
	}
	if ($_SESSION['UserID'] == $_POST['UserID']) {
		$_SESSION['RestrictLocations'] = $_POST['RestrictLocations'];
		$_SESSION['ShowPageHelp'] = $_POST['ShowPageHelp'];
		$_SESSION['ShowFieldHelp'] = $_POST['ShowFieldHelp'];
	}
	if ($InputError != 1) {
		//run the SQL from either of the above possibilites
		$ErrMsg = _('The user alterations could not be processed because');
		$DbgMsg = _('The SQL that was used to update the user and failed was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
		$_SESSION['ChartLanguage'] = GetChartLanguage();
		$_SESSION['InventoryLanguage'] = GetInventoryLanguage();

		unset($_POST['UserID']);
		unset($_POST['RealName']);
		unset($_POST['Cust']);
		unset($_POST['BranchCode']);
		unset($_POST['SupplierID']);
		unset($_POST['Salesman']);
		unset($_POST['Phone']);
		unset($_POST['Email']);
		unset($_POST['Password']);
		unset($_POST['ChangePassword']);
		unset($_POST['PageSize']);
		unset($_POST['Access']);
		unset($_POST['CanCreateTender']);
		unset($_POST['CanEnterTimesheets']);
		unset($_POST['DefaultLocation']);
		unset($_POST['ModulesAllowed']);
		unset($_POST['Blocked']);
		unset($_POST['Theme']);
		unset($_POST['UserLanguage']);
		unset($_POST['PDFLanguage']);
		unset($_POST['Department']);
		unset($_POST['FontSize']);
		unset($_POST['DefaultTag']);
		unset($_POST['ShowPageHelp']);
		unset($_POST['ShowFieldHelp']);
		unset($SelectedUser);
	}

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button
	// comment out except for demo!  Do not want anyopne deleting demo user.
	

	if ($AllowDemoMode and $SelectedUser == 'admin') {
		prnMsg(_('The administration user called admin cannot be deleted'), 'error');
	} else {

		$SQL = "SELECT userid FROM audittrail where userid='" . $SelectedUser . "'";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result) != 0) {
			prnMsg(_('Cannot delete user as entries already exist in the audit trail'), 'warn');
		} else {
			$Result = DB_Txn_Begin();
			$SQL = "DELETE FROM locationusers WHERE userid='" . $SelectedUser . "'";
			$ErrMsg = _('The Location - User could not be deleted because');;
			$Result = DB_query($SQL, $ErrMsg, '', true);

			$SQL = "DELETE FROM glaccountusers WHERE userid='" . $SelectedUser . "'";
			$ErrMsg = _('The GL Account - User could not be deleted because');;
			$Result = DB_query($SQL, $ErrMsg);

			$SQL = "DELETE FROM bankaccountusers WHERE userid='" . $SelectedUser . "'";
			$ErrMsg = _('The Bank Accounts - User could not be deleted because');;
			$Result = DB_query($SQL, $ErrMsg);

			$SQL = "DELETE FROM www_users WHERE userid='" . $SelectedUser . "'";
			$ErrMsg = _('The User could not be deleted because');
			$Result = DB_query($SQL, $ErrMsg, '', true);
			$Result = DB_Txn_Commit();
			prnMsg(_('User Deleted'), 'info');
		}
		unset($SelectedUser);
	}

}

if (!isset($SelectedUser)) {

	/* If its the first time the page has been displayed with no parameters then none of the above are true and the list of Users will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$SQL = "SELECT userid,
					realname,
					phone,
					email,
					customerid,
					branchcode,
					supplierid,
					salesman,
					lastvisitdate,
					fullaccess,
					cancreatetender,
					canentertimesheets,
					pagesize,
					theme,
					language,
					fontsize,
					defaulttag,
					showpagehelp,
					showfieldhelp
				FROM www_users";
	$Result = DB_query($SQL);

	echo '<table>
			<thead>
				<tr>
					<th class="SortedColumn">', _('User Login'), '</th>
					<th class="SortedColumn">', _('Full Name'), '</th>
					<th class="SortedColumn">', _('Telephone'), '</th>
					<th class="SortedColumn">', _('Email'), '</th>
					<th class="SortedColumn">', _('Customer Code'), '</th>
					<th class="SortedColumn">', _('Branch Code'), '</th>
					<th class="SortedColumn">', _('Supplier Code'), '</th>
					<th class="SortedColumn">', _('Salesperson'), '</th>
					<th class="SortedColumn">', _('Last Visit'), '</th>
					<th class="SortedColumn">', _('Security Role'), '</th>
					<th class="SortedColumn">', _('Report Size'), '</th>
					<th class="SortedColumn">', _('Theme'), '</th>
					<th class="SortedColumn">', _('Language'), '</th>
					<th>', _('Screen Font Size'), '</th>
				</tr>
			</thead>';

	echo '<tbody>';
	while ($MyRow = DB_fetch_array($Result)) {

		if ($MyRow['lastvisitdate'] == '') {
			$LastVisitDate = _('User has not logged in yet');
		} else {
			$LastVisitDate = ConvertSQLDate($MyRow['lastvisitdate']);
		}

		/*The SecurityHeadings array is defined in config.php */

		switch ($MyRow['fontsize']) {

			case 0:
				$FontSize = _('Small');
			break;
			case 1:
				$FontSize = _('Medium');
			break;
			case 2:
				$FontSize = _('Large');
			break;
			default:
				$FontSize = _('Medium');
		}

		echo '<tr class="striped_row">
				<td>', $MyRow['userid'], '</td>
				<td>', $MyRow['realname'], '</td>
				<td>', $MyRow['phone'], '</td>
				<td>', $MyRow['email'], '</td>
				<td>', $MyRow['customerid'], '</td>
				<td>', $MyRow['branchcode'], '</td>
				<td>', $MyRow['supplierid'], '</td>
				<td>', $MyRow['salesman'], '</td>
				<td>', $LastVisitDate, '</td>
				<td>', $SecurityRoles[($MyRow['fullaccess']) ], '</td>
				<td>', $MyRow['pagesize'], '</td>
				<td>', $MyRow['theme'], '</td>
				<td>', $LanguagesArray[$MyRow['language']]['LanguageName'], '</td>
				<td>', $FontSize, '</td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '?SelectedUser=', urlencode($MyRow['userid']), '">', _('Edit'), '</a></td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '?SelectedUser=', urlencode($MyRow['userid']), '&amp;delete=1" onclick="return MakeConfirm(\'', _('Are you sure you wish to delete this user?'), '\', \'Confirm Delete\', this);">', _('Delete'), '</a></td>
			</tr>';

	} //END WHILE LIST LOOP
	echo '</tbody>
		</table>';
} //end of ifs and buts!


if (isset($SelectedUser)) {
	echo '<div class="centre">
			<a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">', _('Review Existing Users'), '</a>
		</div>';
}

echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

if (isset($SelectedUser)) {
	//editing an existing User
	$SQL = "SELECT userid,
			realname,
			phone,
			email,
			customerid,
			password,
			changepassword,
			branchcode,
			supplierid,
			salesman,
			pagesize,
			fullaccess,
			cancreatetender,
			canentertimesheets,
			defaultlocation,
			restrictlocations,
			modulesallowed,
			blocked,
			theme,
			language,
			pdflanguage,
			department,
			fontsize,
			defaulttag,
			showpagehelp,
			showfieldhelp
		FROM www_users
		WHERE userid='" . $SelectedUser . "'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['UserID'] = $MyRow['userid'];
	$_POST['RealName'] = $MyRow['realname'];
	$_POST['ChangePassword'] = $MyRow['changepassword'];
	$_POST['Phone'] = $MyRow['phone'];
	$_POST['Email'] = $MyRow['email'];
	$_POST['Cust'] = $MyRow['customerid'];
	$_POST['BranchCode'] = $MyRow['branchcode'];
	$_POST['SupplierID'] = $MyRow['supplierid'];
	$_POST['Salesman'] = $MyRow['salesman'];
	$_POST['PageSize'] = $MyRow['pagesize'];
	$_POST['Access'] = $MyRow['fullaccess'];
	$_POST['CanCreateTender'] = $MyRow['cancreatetender'];
	$_POST['CanEnterTimesheets'] = $MyRow['canentertimesheets'];
	$_POST['DefaultLocation'] = $MyRow['defaultlocation'];
	$_POST['RestrictLocations'] = $MyRow['restrictlocations'];
	$_POST['ModulesAllowed'] = $MyRow['modulesallowed'];
	$_POST['Theme'] = $MyRow['theme'];
	$_POST['UserLanguage'] = $MyRow['language'];
	$_POST['Blocked'] = $MyRow['blocked'];
	$_POST['PDFLanguage'] = $MyRow['pdflanguage'];
	$_POST['Department'] = $MyRow['department'];
	$_POST['FontSize'] = $MyRow['fontsize'];
	$_POST['DefaultTag'] = $MyRow['defaulttag'];
	$_POST['ShowPageHelp'] = $MyRow['showpagehelp'];
	$_POST['ShowFieldHelp'] = $MyRow['showfieldhelp'];

	echo '<input type="hidden" name="SelectedUser" value="', $SelectedUser, '" />';
	echo '<input type="hidden" name="UserID" value="', $_POST['UserID'], '" />';
	echo '<input type="hidden" name="ModulesAllowed" value="', $_POST['ModulesAllowed'], '" />';

	echo '<fieldset>
			<legend>', _('Edit Details For'), ' - ', $MyRow['realname'], ' (', $MyRow['userid'], ')</legend>
			<field>
				<label for="UserID">', _('User ID'), ':</label>
				<div class="fieldtext">', $_POST['UserID'], '</div>
			</field>';

	echo '<field>
			<label for="Password">', _('Password'), ':</label>
			<input type="password" name="Password" size="22" maxlength="20" autofocus="autofocus" autocomplete="off" value="" />
			<fieldhelp>', _('Enter the password for this user. If left empty then the password will remain unchanged.'), '</fieldhelp>
		</field>';
} else { //end of if $SelectedUser only do the else when a new record is being entered
	echo '<fieldset>
			<legend>', _('Insert New User Details'), '</legend>
			<field>
				<label for="UserID">', _('User Login'), ':</label>
				<input type="text" name="UserID" size="22" required="required" autofocus="autofocus" autocomplete="off" maxlength="20" />
				<fieldhelp>', _('Enter the unique ID the user will use to log in.'), '</fieldhelp>
			</field>';

	echo '<field>
			<label for="Password">', _('Password'), ':</label>
			<input type="password" name="Password" size="22" maxlength="20" autofocus="autofocus" autocomplete="off" value="" />
			<fieldhelp>', _('Enter the password for this user.'), '</fieldhelp>
		</field>';

	/*set the default modules to show to all
	 this had trapped a few people previously*/
	$i = 0;
	if (!isset($_POST['ModulesAllowed'])) {
		$_POST['ModulesAllowed'] = '1,1,1,1,1,1,1,1,1,1,1,1,';
	}
	foreach ($_SESSION['ModuleList'] as $ModuleName) {
		if ($i > 0) {
			$_POST['ModulesAllowed'].= ',';
		}
		$_POST['ModulesAllowed'].= '1';
		++$i;
	}
}

if (!isset($_POST['Password'])) {
	$_POST['Password'] = '';
}
if (!isset($_POST['ChangePassword'])) {
	$_POST['ChangePassword'] = 0;
}
if (!isset($_POST['RealName'])) {
	$_POST['RealName'] = '';
}
if (!isset($_POST['Phone'])) {
	$_POST['Phone'] = '';
}
if (!isset($_POST['Email'])) {
	$_POST['Email'] = '';
}

echo '<field>
		<label for="ChangePassword">', _('User Must Change their Password at next Login'), ':</label>
		<select name="ChangePassword">';

if (isset($_POST['ChangePassword']) and $_POST['ChangePassword'] == 0) {
	echo '<option selected="selected" value="0">', _('No'), '</option>';
	echo '<option value="1">', _('Yes'), '</option>';
} else {
	echo '<option selected="selected" value="1">', _('Yes'), '</option>';
	echo '<option value="0">', _('No'), '</option>';
}
echo '</select>
	<fieldhelp>', _('If set to Yes then the user must change their password at next Login.'), '</fieldhelp>
</field>';

echo '<field>
		<label for="RealName">', _('Full Name'), ':</label>
		<input type="text" name="RealName" value="', $_POST['RealName'], '" size="36" required="required" maxlength="35" />
		<fieldhelp>', _('Enter the full name of this user. Does not have to be unique.'), '</fieldhelp>
	</field>';
echo '<field>
		<label for="Phone">', _('Telephone No'), ':</label>
		<input type="tel" name="Phone" value="', $_POST['Phone'], '" size="32" maxlength="30" />
		<fieldhelp>', _('Enter the telephone number of this user.'), '</fieldhelp>
	</field>';
echo '<field>
		<label for="Email">', _('Email Address'), ':</label>
		<input type="email" name="Email" value="', $_POST['Email'], '" size="32" maxlength="55" />
		<fieldhelp>', _('Enter the email address of this user.'), '</fieldhelp>
	</field>';
echo '<field>
		<label for="Access">', _('Security Role'), ':</label>
		<select name="Access">';

foreach ($SecurityRoles as $SecKey => $SecVal) {
	if (isset($_POST['Access']) and $SecKey == $_POST['Access']) {
		echo '<option selected="selected" value="', $SecKey, '">', $SecVal, '</option>';
	} else {
		echo '<option value="', $SecKey, '">', $SecVal, '</option>';
	}
}
echo '</select>
	<fieldhelp>', _('Select the security role of this user. This will correspond with their job title.'), '</fieldhelp>
</field>';

echo '<field>
		<label for="CanCreateTender">', _('User Can Create Tenders'), ':</label>
		<select name="CanCreateTender">';

if (isset($_POST['CanCreateTender']) and $_POST['CanCreateTender'] == 0) {
	echo '<option selected="selected" value="0">', _('No'), '</option>';
	echo '<option value="1">', _('Yes'), '</option>';
} else {
	echo '<option selected="selected" value="1">', _('Yes'), '</option>';
	echo '<option value="0">', _('No'), '</option>';
}
echo '</select>
	<fieldhelp>', _('If the user is authorised to create supplier tenders then select Yes otherwise select No.'), '</fieldhelp>
</field>';

echo '<field>
		<label for="CanEnterTimesheets">', _('User Can Enter Timesheets'), ':</label>
		<select name="CanEnterTimesheets">';

if (isset($_POST['CanEnterTimesheets']) and $_POST['CanEnterTimesheets'] == 0) {
	echo '<option selected="selected" value="0">', _('No'), '</option>';
	echo '<option value="1">', _('Yes'), '</option>';
} else {
	echo '<option selected="selected" value="1">', _('Yes'), '</option>';
	echo '<option value="0">', _('No'), '</option>';
}
echo '</select>
	<fieldhelp>', _('If the user is authorised to enter timesheets then select Yes otherwise select No.'), '</fieldhelp>
</field>';

echo '<field>
		<label for="DefaultLocation">', _('Default Location'), ':</label>
		<select name="DefaultLocation">';

$SQL = "SELECT loccode, locationname FROM locations";
$Result = DB_query($SQL);

while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['DefaultLocation']) and $MyRow['loccode'] == $_POST['DefaultLocation']) {
		echo '<option selected="selected" value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
	} else {
		echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
	}
}
echo '</select>
	<fieldhelp>', _('Select the default stock location for this user.'), '</fieldhelp>
</field>';

echo '<field>
		<label for="RestrictLocations">', _('Restrict to just this location'), ': </label>
		<select name="RestrictLocations">';
if (isset($_POST['RestrictLocations']) and $_POST['RestrictLocations'] == 0) {
	echo '<option selected="selected" value="0">', _('No'), '</option>';
	echo '<option value="1">', _('Yes'), '</option>';
} else {
	echo '<option selected="selected" value="1">', _('Yes'), '</option>';
	echo '<option value="0">', _('No'), '</option>';
}
echo '</select>
	<fieldhelp>', _('Select Yes if the user is restricted to only using their default location, or select No if they can use any location.'), '</fieldhelp>
</field>';

if (!isset($_POST['Cust'])) {
	$_POST['Cust'] = '';
}
if (!isset($_POST['BranchCode'])) {
	$_POST['BranchCode'] = '';
}
if (!isset($_POST['SupplierID'])) {
	$_POST['SupplierID'] = '';
}
echo '<field>
		<label for="Cust">', _('Customer Code'), ':</label>
		<input type="text" name="Cust" size="10" maxlength="10" value="', $_POST['Cust'], '" />
		<fieldhelp>', _('If this is a customer only login, then enter the code for that cuatomer.'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="BranchCode">', _('Branch Code'), ':</label>
		<input type="text" name="BranchCode" size="10" maxlength="10" value="', $_POST['BranchCode'], '" />
		<fieldhelp>', _('If this is a customer only login, then enter the code for the relevant branch of that customer.'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="SupplierID">', _('Supplier Code'), ':</label>
		<input type="text" name="SupplierID" size="10" maxlength="10" value="', $_POST['SupplierID'], '" />
		<fieldhelp>', _('If this is a supplier only login, then enter the code for that supplier.'), '</fieldhelp>
	</field>';

$SQL = "SELECT salesmancode, salesmanname FROM salesman WHERE current = 1 ORDER BY salesmanname";
$Result = DB_query($SQL);
echo '<field>
		<label for="Salesman">', _('Restrict to Sales Person'), ':</label>
		<select name="Salesman">';
if ((isset($_POST['Salesman']) and $_POST['Salesman'] == '') or !isset($_POST['Salesman'])) {
	echo '<option selected="selected" value="">', _('Not a salesperson only login'), '</option>';
} else {
	echo '<option value="">', _('Not a salesperson only login'), '</option>';
}
while ($MyRow = DB_fetch_array($Result)) {

	if (isset($_POST['Salesman']) and $MyRow['salesmancode'] == $_POST['Salesman']) {
		echo '<option selected="selected" value="', $MyRow['salesmancode'], '">', $MyRow['salesmanname'], '</option>';
	} else {
		echo '<option value="', $MyRow['salesmancode'], '">', $MyRow['salesmanname'], '</option>';
	}
}
echo '</select>
	<fieldhelp>', _('If this is a sales person only login, then select the code for that sales person'), '</fieldhelp>
</field>';

echo '<field>
		<label for="PageSize">', _('Reports Page Size'), ':</label>
		<select name="PageSize">';

if (isset($_POST['PageSize']) and $_POST['PageSize'] == 'A4') {
	echo '<option selected="selected" value="A4">', _('A4'), '</option>';
} else {
	echo '<option value="A4">', _('A4'), '</option>';
}

if (isset($_POST['PageSize']) and $_POST['PageSize'] == 'A3') {
	echo '<option selected="selected" value="A3">', _('A3'), '</option>';
} else {
	echo '<option value="A3">', _('A3'), '</option>';
}

if (isset($_POST['PageSize']) and $_POST['PageSize'] == 'A3_Landscape') {
	echo '<option selected="selected" value="A3_Landscape">', _('A3'), ' ', _('landscape'), '</option>';
} else {
	echo '<option value="A3_Landscape">', _('A3'), ' ', _('landscape'), '</option>';
}

if (isset($_POST['PageSize']) and $_POST['PageSize'] == 'Letter') {
	echo '<option selected="selected" value="Letter">', _('Letter'), '</option>';
} else {
	echo '<option value="Letter">', _('Letter'), '</option>';
}

if (isset($_POST['PageSize']) and $_POST['PageSize'] == 'Letter_Landscape') {
	echo '<option selected="selected" value="Letter_Landscape">', _('Letter'), ' ', _('landscape'), '</option>';
} else {
	echo '<option value="Letter_Landscape">', _('Letter'), ' ', _('landscape'), '</option>';
}

if (isset($_POST['PageSize']) and $_POST['PageSize'] == 'Legal') {
	echo '<option selected="selected" value="Legal">', _('Legal'), '</option>';
} else {
	echo '<option value="Legal">', _('Legal'), '</option>';
}
if (isset($_POST['PageSize']) and $_POST['PageSize'] == 'Legal_Landscape') {
	echo '<option selected="selected" value="Legal_Landscape">', _('Legal'), ' ', _('landscape'), '</option>';
} else {
	echo '<option value="Legal_Landscape">', _('Legal'), ' ', _('landscape'), '</option>';
}

echo '</select>
	<fieldhelp>', _('Select the page size to be used by default on reports for this user'), '</fieldhelp>
</field>';

if (!isset($_POST['Theme'])) {
	$_POST['Theme'] = $_SESSION['DefaultTheme'];
}
echo '<field>
		<label for="Theme">', _('Theme'), ':</label>
		<select name="Theme">';
$Themes = glob('css/*', GLOB_ONLYDIR);
foreach ($Themes as $ThemeName) {
	$ThemeName = basename($ThemeName);
	if ($ThemeName != 'mobile' and mb_substr($ThemeName, -4) != '-rtl') {
		if ($_POST['Theme'] == $ThemeName) {
			echo '<option selected="selected" value="', $ThemeName, '">', ucfirst($ThemeName), '</option>';
		} else {
			echo '<option value="', $ThemeName, '">', ucfirst($ThemeName), '</option>';
		}
	}
}
echo '</select>
	<fieldhelp>', _('Select the theme to be used for this user.'), '</fieldhelp>
</field>';

echo '<field>
		<label for="UserLanguage">', _('Language'), ':</label>
		<select name="UserLanguage">';

foreach ($LanguagesArray as $LanguageEntry => $LanguageName) {
	if (isset($_POST['UserLanguage']) and $_POST['UserLanguage'] == $LanguageEntry) {
		echo '<option selected="selected" value="', $LanguageEntry, '">', $LanguageName['LanguageName'], '</option>';
	} elseif (!isset($_POST['UserLanguage']) and $LanguageEntry == $_SESSION['DefaultLanguage']) {
		echo '<option selected="selected" value="', $LanguageEntry, '">', $LanguageName['LanguageName'], '</option>';
	} else {
		echo '<option value="', $LanguageEntry, '">', $LanguageName['LanguageName'], '</option>';
	}
}
echo '</select>
	<fieldhelp>', _('Select the users language. All screens will be translated into this language'), '</fieldhelp>
</field>';

/*Make an array out of the comma separated list of modules allowed*/
$ModulesAllowed = explode(',', $_POST['ModulesAllowed']);

$i = 0;
foreach ($_SESSION['ModuleList'] as $ModuleName) {

	echo '<field>
			<label for="Module_', $i, '">', _('Display'), ' ', $ModuleName, ' ', _('module'), ': </label>
			<select name="Module_', $i, '">';
	if ($ModulesAllowed[$i] == 0) {
		echo '<option selected="selected" value="0">', _('No'), '</option>';
		echo '<option value="1">', _('Yes'), '</option>';
	} else {
		echo '<option selected="selected" value="1">', _('Yes'), '</option>';
		echo '<option value="0">', _('No'), '</option>';
	}
	echo '</select>
		<fieldhelp>', _('Select Yes if the user will be allowed to view the'), ' ', $ModuleName, ' ', _('module'), ' ', _('from the main menu screen'), '</fieldhelp>
	</field>';
	++$i;
}

if (!isset($_POST['PDFLanguage'])) {
	$_POST['PDFLanguage'] = 0;
}
echo '<field>
		<label for="PDFLanguage">', _('PDF Language Support'), ': </label>
		<select name="PDFLanguage">';
for ($i = 0;$i < count($PDFLanguages);$i++) {
	if ($_POST['PDFLanguage'] == $i) {
		echo '<option selected="selected" value="', $i, '">', $PDFLanguages[$i], '</option>';
	} else {
		echo '<option value="', $i, '">', $PDFLanguages[$i], '</option>';
	}
}
echo '</select>
	<fieldhelp>', _('Seleect the PDF languages to support for this user.'), '</fieldhelp>
</field>';

/* Allowed Department for Internal Requests */
$SQL = "SELECT departmentid,
			description
		FROM departments
		ORDER BY description";
$Result = DB_query($SQL);

echo '<field>
		<label for="Department">', _('Allowed Department for Internal Requests'), ':</label>';
echo '<select name="Department">';
if ((isset($_POST['Department']) and $_POST['Department'] == '0') or !isset($_POST['Department'])) {
	echo '<option selected="selected" value="0">', _('Any Internal Department'), '</option>';
} else {
	echo '<option value="">', _('Any Internal Department'), '</option>';
}
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['Department']) and $MyRow['departmentid'] == $_POST['Department']) {
		echo '<option selected="selected" value="', $MyRow['departmentid'], '">', $MyRow['description'], '</option>';
	} else {
		echo '<option value="', $MyRow['departmentid'], '">', $MyRow['description'], '</option>';
	}
}
echo '</select>
	<fieldhelp>', _('What department will this user be allowed to make internal stock requests from.'), '</fieldhelp>
</field>';

/* Account status */
echo '<field>
		<label for="Blocked">', _('Account Status'), ':</label>
		<select name="Blocked">';
if (isset($_POST['Blocked']) and $_POST['Blocked'] == 0) {
	echo '<option selected="selected" value="0">', _('Open'), '</option>';
	echo '<option value="1">', _('Blocked'), '</option>';
} else {
	echo '<option selected="selected" value="1">', _('Blocked'), '</option>';
	echo '<option value="0">', _('Open'), '</option>';
}
echo '</select>
	<fieldhelp>', _('If Blocked is chosen then this user will not be allowed to login.'), '</fieldhelp>
</field>';

// Turn off/on page help:
echo '<field>
		<label for="ShowPageHelp">', _('Display page help'), ':</label>
		<select id="ShowPageHelp" name="ShowPageHelp">';
if ($_SESSION['ShowPageHelp'] == 0) {
	echo '<option selected="selected" value="0">', _('No'), '</option>', '<option value="1">', _('Yes'), '</option>';
} else {
	echo '<option value="0">', _('No'), '</option>', '<option selected="selected" value="1">', _('Yes'), '</option>';
}
echo '</select>
	<fieldhelp>', _('Show page help when available.'), '</fieldhelp>
</field>';

// Turn off/on field help:
echo '<field>
		<label for="ShowFieldHelp">', _('Display field help'), ':</label>
		<select id="ShowFieldHelp" name="ShowFieldHelp">';
if ($_SESSION['ShowFieldHelp'] == 0) {
	echo '<option selected="selected" value="0">', _('No'), '</option>', '<option value="1">', _('Yes'), '</option>';
} else {
	echo '<option value="0">', _('No'), '</option>', '<option selected="selected" value="1">', _('Yes'), '</option>';
}
echo '</select>
	<fieldhelp>', _('Show field help when available.'), '</fieldhelp>
</field>';

/* Screen Font Size */

echo '<field>
		<label for="FontSize">', _('Screen Font Size'), ':</label>
		<select name="FontSize">';
if (isset($_POST['FontSize']) and $_POST['FontSize'] == 0) {
	echo '<option selected="selected" value="0">', _('Small'), '</option>';
	echo '<option value="1">', _('Medium'), '</option>';
	echo '<option value="2">', _('Large'), '</option>';
} else if (isset($_POST['FontSize']) and $_POST['FontSize'] == 1) {
	echo '<option value="0">', _('Small'), '</option>';
	echo '<option selected="selected" value="1">', _('Medium'), '</option>';
	echo '<option value="2">', _('Large'), '</option>';
} else {
	echo '<option value="0">', _('Small'), '</option>';
	echo '<option value="1">', _('Medium'), '</option>';
	echo '<option selected="selected" value="2">', _('Large'), '</option>';
}
echo '</select>
	<fieldhelp>', _('Select the font size that the screens will be drawn in for this user. It can be changed at any time from the main menu screen.'), '</fieldhelp>
</field>';

//Select the tag
$SQL = "SELECT tagref,
				tagdescription
		FROM tags
		ORDER BY tagref";
$Result = DB_query($SQL);

echo '<field>
		<label for="DefaultTag">', _('Default Tag For User'), '</label>
		<select name="DefaultTag">';
echo '<option value="0">0 - ', _('None'), '</option>';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['DefaultTag']) and $_POST['DefaultTag'] == $MyRow['tagref']) {
		echo '<option selected="selected" value="', $MyRow['tagref'], '">', $MyRow['tagref'], ' - ', $MyRow['tagdescription'], '</option>';
	} else {
		echo '<option value="', $MyRow['tagref'], '">', $MyRow['tagref'], ' - ', $MyRow['tagdescription'], '</option>';
	}
}
echo '</select>
	<fieldhelp>', _('Select a default tag for the user.'), '</fieldhelp>
</field>';
// End select tag
echo '</fieldset>';
echo '<div class="centre">
		<input type="submit" name="submit" value="', _('Enter Information'), '" />
	</div>
</form>';

include ('includes/footer.php');
?>