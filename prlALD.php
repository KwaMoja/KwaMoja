<?php
/* $Revision: 1.0 $ */

include('includes/session.inc');

$Title = _('Employee Loan Deduction Entry');
include('includes/header.inc');

if (isset($_GET['SelectedID'])) {
	$SelectedID = $_GET['SelectedID'];
} elseif (isset($_POST['SelectedID'])) {
	$SelectedID = $_POST['SelectedID'];
}

$Status = array();
$Status[0] = _('Pending Authorisation');
$Status[1] = _('Authorised');
$Status[2] = _('Posted');
$Status[3] = _('Cancelled');
$Status[4] = _('Rejected');
$Status[5] = _('Written Off');

if (isset($_POST['insert']) or isset($_POST['update'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible


	if ($InputError != 1) {
		//printerr($_POST['LoanTableID']);
		$SQL_LoanDate = FormatDateForSQL($_POST['LoanDate']);
		$SQL_StartDeduction = FormatDateForSQL($_POST['StartDeduction']);
		if (isset($_POST['update'])) {
			//edit
			$SQL = "UPDATE prlloanfile SET loanfiledesc='" . $_POST['LoanFileDesc'] . "',
											employeeid='" . $_POST['EmployeeID'] . "',
											loandate='" . $SQL_LoanDate . "',
											loantableid='" . $_POST['LoanTableID'] . "',
											loanamount='" . $_POST['LoanAmount'] . "',
											amortization='" . $_POST['Amortization'] . "',
											nextdeduction='" . $SQL_StartDeduction . "',
											accountcode='" . $_POST['LoanAct'] . "',
											bankaccount='" . $_POST['BankAct'] . "',
											tagref='" . $_POST['tag'] . "',
											status='" . $_POST['Status'] . "'
										WHERE counterindex='" . $SelectedID . "'";
			$ErrMsg = _('The employee loan') . ' ' . $_POST['LoanFileDesc'] . ' ' . _('could not be updated because');
			$DbgMsg = _('The SQL that was used to update the employee loan but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
		} elseif (isset($_POST['insert'])) { //its a new employee
			$AuthoriserSQL = "SELECT authoriser
								FROM departments
								INNER JOIN prlemployeemaster
									ON prlemployeemaster.departmentid=departments.departmentid
								WHERE prlemployeemaster.employeeid='" . $_POST['EmployeeID'] . "'";
			$AuthoriserResult = DB_query($AuthoriserSQL);
			$AuthoriserRow = DB_fetch_array($AuthoriserResult);
			$SQL = "INSERT INTO prlloanfile (loanfileid,
											loanfiledesc,
											employeeid,
											loandate,
											loantableid,
											loanamount,
											amortization,
											nextdeduction,
											loanbalance,
											accountcode,
											bankaccount,
											tagref,
											authoriser
										) VALUES (
											'" . $_POST['LoanFileId'] . "',
											'" . $_POST['LoanFileDesc'] . "',
											'" . $_POST['EmployeeID'] . "',
											'" . $SQL_LoanDate . "',
											'" . $_POST['LoanTableID'] . "',
											'" . $_POST['LoanAmount'] . "',
											'" . $_POST['Amortization'] . "',
											'" . $SQL_StartDeduction . "',
											'" . $_POST['LoanAmount'] . "',
											'" . $_POST['LoanAct'] . "',
											'" . $_POST['BankAct'] . "',
											'" . $_POST['tag'] . "',
											'" . $AuthoriserRow['authoriser'] . "'
										)";

			$ErrMsg = _('The employee loan') . ' ' . $_POST['LoanFileDesc'] . ' ' . _('could not be added because');
			$DbgMsg = _('The SQL that was used to insert the employee loan but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

			prnMsg(_('A new employee loan for') . ' ' . $_POST['LoanFileDesc'] . ' ' . _('has been added to the database'), 'success');
		}
		unset($_POST['LoanFileId']);
		unset($_POST['LoanFileDesc']);
		unset($_POST['EmployeeID']);
		unset($_POST['LoanDate']);
		unset($_POST['LoanTableID']);
		unset($_POST['LoanAmount']);
		unset($_POST['Amortization']);
		unset($_POST['StartDeduction']);
		unset($_POST['LoanAmount']);
		unset($_POST['LoanAct']);
		unset($_POST['BankAct']);
		unset($_POST['tag']);
		unset($SelectedID);

	} else {

		prnMsg(_('Validation failed') . _('no updates or deletes took place'), 'warn');

	}

}

echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/loan.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';
echo '<form method="post" class="noPrint" id="LoanDeductionForm" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

$SQL = "SELECT prlloanfile.counterindex,
				prlloanfile.loanfileid,
				prlloanfile.loanfiledesc,
				prlloanfile.employeeid,
				prlemployeemaster.firstname,
				prlemployeemaster.middlename,
				prlemployeemaster.lastname,
				prlloanfile.loandate,
				prlloantable.loantabledesc,
				departments.description,
				prlloanfile.loanamount,
				prlloanfile.amortization,
				prlloanfile.nextdeduction,
				prlloanfile.loanbalance,
				prlloanfile.status,
				www_users.realname
			FROM prlloanfile
			INNER JOIN prlemployeemaster
				ON prlloanfile.employeeid=prlemployeemaster.employeeid
			INNER JOIN departments
				ON prlemployeemaster.departmentid=departments.departmentid
			INNER JOIN prlloantable
				ON prlloantable.loantableid=prlloanfile.loantableid
			INNER JOIN www_users
				ON www_users.userid=prlloanfile.authoriser
			WHERE prlloanfile.loanbalance>0";
$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0) {
	echo '<table class="selection">
			<tr>
				<th>' . _('Description') . '</th>
				<th>' . _('Employee ID') . '</th>
				<th>' . _('Employee Name') . '</th>
				<th>' . _('Department') . '</th>
				<th>' . _('Loan Type') . '</th>
				<th>' . _('Loan Date') . '</th>
				<th>' . _('Loan Amount') . '</th>
				<th>' . _('Repayment per') . '<br />' . _('Pay period') . '</th>
				<th>' . _('Balance') . '</th>
				<th>' . _('Status') . '</th>
				<th>' . _('Authoriser') . '</th>
			</tr>';

	while ($LoanRow = DB_fetch_array($Result)) {
		echo '<tr>
				<td>' . $LoanRow['loanfiledesc'] . '</td>
				<td>' . $LoanRow['employeeid'] . '</td>
				<td>' . $LoanRow['firstname'] . ' ' . $LoanRow['middlename'] . ' ' . $LoanRow['lastname'] . '</td>
				<td>' . $LoanRow['description'] . '</td>
				<td>' . $LoanRow['loantabledesc'] . '</td>
				<td>' . ConvertSQLDate($LoanRow['loandate']) . '</td>
				<td class="number">' . locale_number_format($LoanRow['loanamount'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($LoanRow['amortization'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($LoanRow['loanbalance'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td>' . $Status[$LoanRow['status']] . '</td>
				<td>' . $LoanRow['realname'] . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedID=' . urlencode($LoanRow['counterindex']) . '&Edit=Yes">' . _('Edit') . '</a></td>
			</tr>';
	}
	echo '</table>';
}

if (isset($_GET['Edit'])) {
	$SQL = "SELECT prlloanfile.loanfileid,
					prlloanfile.loanfiledesc,
					prlloanfile.employeeid,
					prlloanfile.loandate,
					prlloanfile.loantableid,
					prlloanfile.loanamount,
					prlloanfile.amortization,
					prlloanfile.nextdeduction,
					prlloanfile.loanbalance,
					prlloanfile.accountcode,
					prlloanfile.bankaccount,
					prlloanfile.tagref,
					prlloanfile.status,
					prlloanfile.authoriser
				FROM prlloanfile
				WHERE prlloanfile.counterindex='" . $SelectedID . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$_POST['LoanFileId'] = $MyRow['loanfileid'];
	$_POST['LoanFileDesc'] = $MyRow['loanfiledesc'];
	$_POST['EmployeeID'] = $MyRow['employeeid'];
	$_POST['LoanDate'] = ConvertSQLDate($MyRow['loandate']);
	$_POST['LoanTableID'] = $MyRow['loantableid'];
	$_POST['LoanAmount'] = $MyRow['loanamount'];
	$_POST['Amortization'] = $MyRow['amortization'];
	$_POST['StartDeduction'] = ConvertSQLDate($MyRow['nextdeduction']);
	$_POST['LoanAct'] = $MyRow['accountcode'];
	$_POST['BankAct'] = $MyRow['bankaccount'];
	$_POST['tag'] = $MyRow['tagref'];
	$_POST['Status'] = $MyRow['status'];
	$_POST['Authoriser'] = $MyRow['authoriser'];
} else {
	$_POST['LoanFileId'] = '';
	$_POST['LoanFileDesc'] = '';
	$_POST['EmployeeID'] = '';
	$_POST['LoanDate'] = Date($_SESSION['DefaultDateFormat']);
	$_POST['LoanTableID'] = '';
	$_POST['LoanAmount'] = 0;
	$_POST['Amortization'] = 0;
	$_POST['StartDeduction'] = Date($_SESSION['DefaultDateFormat']);
	$_POST['LoanAct'] = '';
	$_POST['BankAct'] = '';
	$_POST['tag'] = 0;
	$_POST['Status'] = 0;
	$_POST['Authoriser'] = '';
}

if (!isset($SelectedID)) {
	$AllowedStatuses = array(0);
} else {
	if ($_SESSION['UserID'] == $_POST['Authoriser']) {
		if ($_POST['Status'] === '0') {
			$AllowedStatuses = array(0, 1, 3, 4);
		}
		if ($_POST['Status'] === '1') {
			$AllowedStatuses = array(1);
		}
		if ($_POST['Status'] === '2') {
			$AllowedStatuses = array(2);
		}
		if ($_POST['Status'] === '3') {
			$AllowedStatuses = array(3);
		}
		if ($_POST['Status'] === '4') {
			$AllowedStatuses = array(0, 4);
		}
	} else {
		$AllowedStatuses = array($_POST['Status']);
	}
}

echo '<table class="selection">';

if (!isset($SelectedID)) {
	echo '<tr>
			<td>' . _('Loan Ref') . ':</td>
			<td><input type="text" required="required" name="LoanFileId" size="11" maxlength="10" /></td>
		</tr>';
} else {
	echo '<tr>
			<td>' . _('Loan Ref') . ':</td>
			<td>' . $_POST['LoanFileId'] . '</td>
		</tr>';
	echo '<input type="hidden" name="SelectedID" value="' . $SelectedID . '" />';
}
echo '<tr>
		<td>' . _('Description') . ':</td>
		<td><input type="text" required="required" name="LoanFileDesc" size="42" maxlength="40" value="' . $_POST['LoanFileDesc'] . '" /></td>
	</tr>';

if ($_POST['Status'] == 0) {
	echo '<tr>
			<td>' . _('Employee Name') . ':</td>
			<td><select name="EmployeeID" required="required">';

	$SQL = "SELECT employeeid, lastname, firstname FROM prlemployeemaster ORDER BY lastname, firstname";
	$Result = DB_query($SQL);
	echo '<option value=""></option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['EmployeeID']) and ($_POST['EmployeeID']) == $MyRow['employeeid']) {
			echo '<option selected value=' . $MyRow['employeeid'] . '>' . $MyRow['lastname'] . ',' . $MyRow['firstname'] . '</option>';
		} else {
			echo '<option value=' . $MyRow['employeeid'] . '>' . $MyRow['lastname'] . ', ' . $MyRow['firstname'] . '</option>';
		}
	} //end while loop
	echo '</select>
				</td>
			</tr>';
} else {
	$SQL = "SELECT employeeid, lastname, firstname FROM prlemployeemaster WHERE employeeid='" . $_POST['EmployeeID'] . "' ORDER BY lastname, firstname";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	echo '<tr>
			<td>' . _('Employee Name') . ':</td>
			<td>' . $MyRow['lastname'] . ', ' . $MyRow['firstname'] . '</td>
		</tr>';
	echo '<input type="hidden" value="' . $_POST['EmployeeID'] . ' name="EmployeeID" />';
}

if ($_POST['Status'] == 0) {
	echo '<tr>
			<td>' . _('Loan Date') . ':</td>
			<td><input type="text" required="required" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="LoanDate" maxlength="10" size="11" value="' . $_POST['LoanDate'] . '" /></td>
		</tr>';
} else {
	echo '<tr>
			<td>' . _('Loan Date') . ':</td>
			<td>' . $_POST['LoanDate'] . '</td>
		</tr>';
	echo '<input type="hidden" name="LoanDate" value="' . $_POST['LoanDate'] . '" />';
}

if ($_POST['Status'] == 0 or $_POST['Status'] == 4) {
	echo '<tr>
			<td>' . _('Loan Type') . ':</td>
			<td><select name="LoanTableID" required="required">';
	$SQL = 'SELECT loantableid, loantabledesc FROM prlloantable';
	$Result = DB_query($SQL);
	echo '<option value=""></option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['LoanTableID']) and ($_POST['LoanTableID']) == $MyRow['loantableid']) {
			echo '<option selected="selected" value=' . $MyRow['loantableid'] . '>' . $MyRow['loantabledesc'] . '</option>';
		} else {
			echo '<option value=' . $MyRow['loantableid'] . '>' . $MyRow['loantabledesc'] . '</option>';
		}
	} //end while loop
	echo '</select>
			</td>
		</tr>';
} else {
	$SQL = 'SELECT loantableid, loantabledesc FROM prlloantable';
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	echo '<tr>
			<td>' . _('Loan Type') . ':</td>
			<td>' . $MyRow['loantabledesc'] . '</td>
		</tr>';
	echo '<input type="hidden" name="LoanTableID" value="' . $_POST['LoanTableID'] . '" />';
}

if ($_POST['Status'] == 0 or $_POST['Status'] == 4) {
	echo '<tr>
			<td>' . _('Loan Amount') . '</td>
			<td><input type="text" required="required" class="number" name="LoanAmount" size="14" maxlength="12" value="' . $_POST['LoanAmount'] . '" /></td>
		</tr>';
} else {
	echo '<tr>
			<td>' . _('Loan Amount') . ':</td>
			<td>' . $_POST['LoanAmount'] . '</td>
		</tr>';
	echo '<input type="hidden" name="LoanAmount" value="' . $_POST['LoanAmount'] . '" />';
}

if ($_POST['Status'] == 0 or $_POST['Status'] == 4) {
	echo '<tr>
			<td>' . _('Repayment per Pay Period') . ':</td>
			<td><input type="text" required="required" class="number" name="Amortization" size="14" maxlength="12" value="' . $_POST['Amortization'] . '" /></td>
		</tr>';
} else {
	echo '<tr>
			<td>' . _('Repayment per Pay Period') . ':</td>
			<td>' . $_POST['Amortization'] . '</td>
		</tr>';
	echo '<input type="hidden" name="Amortization" value="' . $_POST['Amortization'] . '" />';
}

echo '<tr>
		<td>' . _('Next Deduction') . ':</td>
		<td><input type="text" required="required" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="StartDeduction" maxlength="10" size="11" value="' . $_POST['StartDeduction'] . '"></td>
	</tr>';

if ($_POST['Status'] == 0 or $_POST['Status'] == 4) {
	$Result = DB_query("SELECT accountcode,
								accountname
							FROM chartmaster
							INNER JOIN accountgroups
								ON chartmaster.groupcode=accountgroups.groupcode
								AND chartmaster.language=accountgroups.language
							WHERE accountgroups.pandl=0
								AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
							ORDER BY chartmaster.accountcode");
	echo '<tr>
			<td>' . _('GL Loan Account') . ':</td>
			<td><select required="required" name="LoanAct">';

	while ($MyRow = DB_fetch_row($Result)) {
		if ($_POST['LoanAct'] == $MyRow[0]) {
			echo '<option selected="selected" value="' . $MyRow[0] . '">' . htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8') . ' (' . $MyRow[0] . ')</option>';
		} else {
			echo '<option value="' . $MyRow[0] . '">' . htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8') . ' (' . $MyRow[0] . ')</option>';
		}
	} //end while loop

	echo '</select>
			</td>
		</tr>';
} else {
	$Result = DB_query("SELECT accountcode,
								accountname
							FROM chartmaster
							WHERE accountcode='" . $_POST['LoanAct'] . "'
								AND language='" . $_SESSION['ChartLanguage'] . "'");
	$MyRow = DB_fetch_row($Result);
	echo '<tr>
			<td>' . _('GL Loan Account') . ':</td>
			<td>' . htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8') . ' (' . $MyRow[0] . ')</td>
		</tr>';
	echo '<input type="hidden" name="LoanAct" value="' . $_POST['LoanAct'] . '" />';
}

$SQL = "SELECT bankaccountusers.accountcode,
				bankaccountname
			FROM bankaccounts
			INNER JOIN bankaccountusers
				ON bankaccounts.accountcode=bankaccountusers.accountcode
			WHERE bankaccountusers.userid = '" . $_SESSION['UserID'] . "'
				AND currcode='" . $_SESSION['CompanyRecord']['currencydefault'] . "'
			ORDER BY bankaccountusers.accountcode";

$Result = DB_query($SQL);

echo '<tr>
		<td>' . _('Cash/Bank Account') . ':</td>
		<td><select required="required" name="BankAct">';

while ($MyRow = DB_fetch_row($Result)) {
	if ($_POST['BankAct'] == $MyRow[0]) {
		echo '<option selected="selected" value="' . $MyRow[0] . '">' . htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8') . ' (' . $MyRow[0] . ')</option>';
	} else {
		echo '<option value="' . $MyRow[0] . '">' . htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8') . ' (' . $MyRow[0] . ')</option>';
	}
} //end while loop

echo '</select>
		</td>
	</tr>';

if ($_POST['Status'] == 0 or $_POST['Status'] == 4) {
	//Select the tag
	echo '<tr>
			<td>' . _('Select tag for this loan') . '</td>
			<td><select required="required" name="tag">';

	$SQL = "SELECT tagref,
					tagdescription
				FROM tags
				ORDER BY tagref";

	$Result = DB_query($SQL);
	echo '<option value="0">0 - ' . _('None') . '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['tag']) and $_POST['tag'] == $MyRow['tagref']) {
			echo '<option selected="selected" value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
		}
	}
	echo '</select>
			</td>
		</tr>';
	// End select tag
} else {
	$SQL = "SELECT tagref,
					tagdescription
				FROM tags
				WHERE tagref='" . $_POST['tag'] . "'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	echo '<tr>
			<td>' . _('Select tag for this loan') . '</td>
			<td>' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</td>
		</tr>';
	echo '<input type="hidden" name="tag" value="' . $_POST['tag'] . '" />';
}

echo '<tr>
		<td>' . _('Status') . '</td>
		<td><select name="Status" required="required">';
foreach ($AllowedStatuses as $AllowedStatus) {
	if ($AllowedStatus === $_POST['Status']) {
		echo '<option selected="selected" value="' . $AllowedStatus . '">' . $Status[$AllowedStatus] . '</option>';
	} else {
		echo '<option value="' . $AllowedStatus . '">' . $Status[$AllowedStatus] . '</option>';
	}
}
echo '</select>
			</td>
		</tr>';

echo '</table>';

if (!isset($SelectedID)) {
	echo '<div class="centre">
			<input type="submit" name="insert" value="' . _('Insert New Employee Loan') . '" />
		</div>';
} else {
	echo '<div class="centre">
			<input type="submit" name="update" value="' . _('Update Employee Loan Information') . '" />
		</div>';
}

echo '</form>';


include('includes/footer.inc');
?>