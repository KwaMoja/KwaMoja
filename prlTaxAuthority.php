<?php

include('includes/session.inc');

$Title = _('Tax Authority Section');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/prlFunctions.php');

if (isset($_POST['SelectedTaxAuthID'])) {
	$SelectedTaxAuthID = $_POST['SelectedTaxAuthID'];
} elseif (isset($_GET['SelectedTaxAuthID'])) {
	$SelectedTaxAuthID = $_GET['SelectedTaxAuthID'];
}
?>
<a href="prlUserSettings.php">Back to User Settings
    </a>
	<?php

if (isset($_POST['submit'])) {

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	if (trim($_POST['Description']) == '') {
		$InputError = 1;

		prnMsg(_('The tax type description may not be empty'), 'error');
	}

	if (isset($SelectedTaxAuthID)) {

		/*SelectedTaxAuthID could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$SQL = 'UPDATE prltaxauthority
				SET description = ' . $_POST['Description'] . "',
				bank = '" . $_POST['Bank'] . "',
				bankacctype = '" . $_POST['BankAccType'] . "',
				bankacc = '" . $_POST['BankAcc'] . "',
			WHERE taxid = " . $SelectedTaxAuthID;

		$ErrMsg = _('The update of this tax authority failed because');
		$Result = DB_query($SQL, $ErrMsg);

		$msg = _('The tax authority for record has been updated');

	} elseif ($InputError != 1) {

		/*Selected tax authority is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new tax authority form */

		$SQL = "INSERT INTO prltaxauthority (
						taxid,
						description,
						bank,
						bankacctype,
						bankacc)
			VALUES (
				'" . $_POST['taxid'] . "',
				'" . $_POST['Description'] . "',
				'" . $_POST['Bank'] . "',
				'" . $_POST['BankAccType'] . "',
				'" . $_POST['BankAcc'] . "')";

		$Errmsg = _('The addition of this tax authority failed because');
		$Result = DB_query($SQL, $Errmsg);

		$msg = _('The new tax authority record has been added to the database');

		$NewTaxID = DB_Last_Insert_ID('prltaxauthority', 'taxid');

		$SQL = 'INSERT INTO prltaxauthrates (
					taxauthority,
					dispatchtaxprovince,
					taxcatid
					)
				SELECT
					' . $NewTaxID . ',
					prltaxprovinces.taxprovinceid,
					prltaxcategories.taxcatid
				FROM prltaxprovinces,
					prltaxcategories';

		$InsertResult = DB_query($SQL);
	}
	//run the SQL from either of the above possibilites
	if (isset($InputError) and $InputError != 1) {
		unset($_POST['taxid']);
		unset($_POST['Description']);
		unset($_POST['bank']);
		unset($_POST['bankacctype']);
		unset($_POST['bankacc']);
		unset($SelectedTaxID);
	}

	prnMsg($msg);

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button

	// PREVENT DELETES IF DEPENDENT RECORDS IN OTHER TABLES

	$SQL = 'SELECT COUNT(*)
			FROM prltaxgrouptaxes
		WHERE taxauthid=' . $SelectedTaxAuthID;

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		prnmsg(_('Cannot delete this tax authority because there are tax groups defined that use it'), 'warn');
	} else {
		/*Cascade deletes in TaxAuthLevels */
		$Result = DB_query('DELETE FROM prltaxauthrates WHERE taxauthority= ' . $SelectedTaxAuthID);
		$Result = DB_query('DELETE FROM prltaxauthority WHERE taxid= ' . $SelectedTaxAuthID);
		prnMsg(_('The selected tax authority record has been deleted'), 'success');
		unset($SelectedTaxAuthID);
	} // end of related records testing
}

if (!isset($SelectedTaxAuthID)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedTaxAuthID will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then none of the above are true and the list of tax authorities will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$SQL = 'SELECT taxid,
			description,
			bank,
			bankacctype,
			bankacc
		FROM prltaxauthority';

	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The defined tax authorities could not be retrieved because');
	$DbgMsg = _('The following SQL to retrieve the tax authorities was used');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
	echo "<table>";
	echo '<table border=1>';
	echo "<tr>
		<th>" . _('ID') . "</td>
		<th>" . _('Tax Authority') . "</td>
		<th>" . _('Country') . "</td>
		<th>" . _('Bank With A/C ') . "</td>
		<th>" . _('Account Number') . "</td>
		</tr></font>";

	while ($MyRow = DB_fetch_row($Result)) {

		printf("<tr><td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td><a href=\"%s&SelectedTaxAuthID=%s\">" . _('Edit') . "</a></td>
				<td><a href=\"%s&SelectedTaxAuthID=%s&delete=yes\">" . _('Delete') . '</a></td>
			</tr>', $MyRow[0], $MyRow[1], $MyRow[3], $MyRow[2], $MyRow[4], $RootPath . '/prlTaxAuthorityRates.php?', $MyRow[0], $_SERVER['PHP_SELF'], $MyRow[0], $_SERVER['PHP_SELF'], $MyRow[0]);

	}
	//END WHILE LIST LOOP

	//end of ifs and buts!

	echo '</table><p>';
}



if (isset($SelectedTaxAuthID)) {
	echo '<div class="centre"><a href="' . $_SERVER['PHP_SELF'] . '">' . _('Review all defined tax authority records') . '</a></div>';
}


echo '<p><form method="post" action="' . $_SERVER['PHP_SELF'] . '">';

if (isset($SelectedTaxAuthID)) {
	//editing an existing tax authority

	$SQL = 'SELECT
			taxid,
			description,
			bank,
			bankacctype,
			bankacc
		FROM prltaxauthority
		WHERE taxid=' . $SelectedTaxAuthID;

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['taxid'] = $MyRow['taxid'];
	$_POST['Description'] = $MyRow['description'];
	$_POST['Bank'] = $MyRow['bank'];
	$_POST['BankAccType'] = $MyRow['bankacctype'];
	$_POST['BankAcc'] = $MyRow['bankacc'];

	echo '<input type="hidden" name="SelectedTaxAuthID" value="' . $SelectedTaxAuthID . '">';

} //end of if $SelectedTaxAuthID only do the else when a new record is being entered


$SQL = "SELECT accountcode,
		accountname
	FROM chartmaster,
		prlaccountgroups
	WHERE chartmaster.group_=prlaccountgroups.groupname
	AND prlaccountgroups.pandl=0
	AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
	ORDER BY accountcode";
$Result = DB_query($SQL);

if (!isset($_POST['Description'])) {
	$_POST['Description'] = '';
}
echo '<table>
<tr><td>' . _('Tax Authority Name') . ":</td>
<td><input type=Text name='Description' size=30 maxlength=50 value='" . $_POST['Description'] . "'></td></tr>";

while ($MyRow = DB_fetch_array($Result)) {
	echo $MyRow['accountcode'] . '>' . $MyRow['accountname'] . ' (' . $MyRow['accountcode'] . ')';

} //end while loop

echo '</select></td></tr>';

DB_data_seek($Result, 0);
while ($MyRow = DB_fetch_array($Result)) {

	echo $MyRow['accountcode'] . "'>" . $MyRow['accountname'] . ' (' . $MyRow['accountcode'] . ')';

} //end while loop

if (!isset($_POST['Bank'])) {
	$_POST['Bank'] = '';
}
if (!isset($_POST['BankAccType'])) {
	$_POST['BankAccType'] = '';
}
if (!isset($_POST['BankAcc'])) {
	$_POST['BankAcc'] = '';
}
echo '</select></td></tr>';
echo "<table>";
echo '<tr><td>' . _('Country') . ':</td>';
echo '<td><input type=Text name="BankAccType" size=41 maxlength=40 value="' . $_POST['BankAccType'] . '"></td></tr>';
echo '<tr><td>' . _('Bank With Account') . ':</td>';
echo '<td><input type=Text name="Bank" size=15 maxlength=20 value="' . $_POST['Bank'] . '"></td></tr>';
echo '<tr><td>' . _(' Account Number') . ':</td>';
echo '<td><input type=Text name="BankAcc" size=21 maxlength=20 value="' . $_POST['BankAcc'] . '"></td></tr>';

echo '</table>';

echo '<div class="centre"><input type=submit name=submit value=' . _('Enter Information') . '></div></form>';

include('includes/footer.inc');

?>