<?php

include('includes/session.inc');
$Title = _('Tax Authorities Maintenance');
$ViewTopic = 'Tax';// Filename in ManualContents.php's TOC.
$BookMark = 'TaxAuthorities';// Anchor's id in the manual's html document.
include('includes/header.inc');

echo '<p class="page_title_text" >
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', $Title, '" alt="" />', $Title, '
	</p>';

if (isset($_POST['SelectedTaxAuthID'])) {
	$SelectedTaxAuthID = $_POST['SelectedTaxAuthID'];
} elseif (isset($_GET['SelectedTaxAuthID'])) {
	$SelectedTaxAuthID = $_GET['SelectedTaxAuthID'];
}


if (isset($_POST['submit'])) {

	$InputError = 0;

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

		$SQL = "UPDATE taxauthorities
					SET taxglcode ='" . $_POST['TaxGLCode'] . "',
					purchtaxglaccount ='" . $_POST['PurchTaxGLCode'] . "',
					description = '" . $_POST['Description'] . "',
					bank = '" . $_POST['Bank'] . "',
					bankacctype = '" . $_POST['BankAccType'] . "',
					bankacc = '" . $_POST['BankAcc'] . "',
					bankswift = '" . $_POST['BankSwift'] . "'
				WHERE taxid = '" . $SelectedTaxAuthID . "'";

		$ErrMsg = _('The update of this tax authority failed because');
		$Result = DB_query($SQL, $ErrMsg);

		$Msg = _('The tax authority for record has been updated');

	} elseif ($InputError != 1) {

		/*Selected tax authority is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new tax authority form */

		$SQL = "INSERT INTO taxauthorities (
							taxglcode,
							purchtaxglaccount,
							description,
							bank,
							bankacctype,
							bankacc,
							bankswift)
						VALUES (
							'" . $_POST['TaxGLCode'] . "',
							'" . $_POST['PurchTaxGLCode'] . "',
							'" . $_POST['Description'] . "',
							'" . $_POST['Bank'] . "',
							'" . $_POST['BankAccType'] . "',
							'" . $_POST['BankAcc'] . "',
							'" . $_POST['BankSwift'] . "'
						)";

		$ErrMsg = _('The addition of this tax authority failed because');
		$Result = DB_query($SQL, $ErrMsg);

		$Msg = _('The new tax authority record has been added to the database');

		$NewTaxID = DB_Last_Insert_ID('taxauthorities', 'taxid');

		$SQL = "INSERT INTO taxauthrates (
							taxauthority,
							dispatchtaxprovince,
							taxcatid
							)
						SELECT '" . $NewTaxID . "',
								taxprovinces.taxprovinceid,
								taxcategories.taxcatid
						FROM taxprovinces,
							taxcategories";

		$InsertResult = DB_query($SQL);
	}
	//run the SQL from either of the above possibilites
	if (isset($InputError) and $InputError != 1) {
		unset($_POST['TaxGLCode']);
		unset($_POST['PurchTaxGLCode']);
		unset($_POST['Description']);
		unset($SelectedTaxID);
	}

	prnMsg($Msg);

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button

	// PREVENT DELETES IF DEPENDENT RECORDS IN OTHER TABLES

	$SQL = "SELECT COUNT(*)
			FROM taxgrouptaxes
		WHERE taxauthid='" . $SelectedTaxAuthID . "'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		prnmsg(_('Cannot delete this tax authority because there are tax groups defined that use it'), 'warn');
	} else {
		/*Cascade deletes in TaxAuthLevels */
		$Result = DB_query("DELETE FROM taxauthrates WHERE taxauthority= '" . $SelectedTaxAuthID . "'");
		$Result = DB_query("DELETE FROM taxauthorities WHERE taxid= '" . $SelectedTaxAuthID . "'");
		prnMsg(_('The selected tax authority record has been deleted'), 'success');
		unset($SelectedTaxAuthID);
	} // end of related records testing
}

if (!isset($SelectedTaxAuthID)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedTaxAuthID will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then none of the above are true and the list of tax authorities will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$SQL = "SELECT taxid,
					description,
					taxglcode,
					purchtaxglaccount,
					bank,
					bankacc,
					bankacctype,
					bankswift
				FROM taxauthorities";

	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The defined tax authorities could not be retrieved because');
	$DbgMsg = _('The following SQL to retrieve the tax authorities was used');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

	if (DB_num_rows($Result) == 0) {
		echo '<div class="page_help_text">' . _('As this is the first time that the system has been used, you must first create a tax authority.') .
				'<br />' . _('For help, click on the help icon in the top right') .
				'<br />' . _('Once you have filled in all the details, click on the button at the bottom of the screen') . '</div>';
	}

	echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">', _('ID'), '</th>
					<th class="SortedColumn">', _('Tax Authority'), '</th>
					<th>', _('Input Tax'), '<br />', _('GL Account'), '</th>
					<th>', _('Output Tax'), '<br />', _('GL Account'), '</th>
					<th>', _('Bank'), '</th>
					<th>', _('Bank Account'), '</th>
					<th>', _('Bank Act Type'), '</th>
					<th>', _('Bank Swift'), '</th>
					<th colspan="4">', _('Maintenance'), '</th>
				</tr>
			</thead>';
	$k = 0;
	echo '<tbody>';
	while ($MyRow = DB_fetch_row($Result)) {

		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			++$k;
		}

		echo '<td class="number">', $MyRow[0], '</td>
				<td>', $MyRow[1]. '</td>
				<td>', $MyRow[3], '</td>
				<td>', $MyRow[2], '</td>
				<td>', $MyRow[4], '</td>
				<td>', $MyRow[5], '</td>
				<td>', $MyRow[6], '</td>
				<td>', $MyRow[7], '</td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedTaxAuthID=', urlencode($MyRow[0]), '">', _('Edit'), '</a></td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedTaxAuthID=', urlencode($MyRow[0]), '&amp;delete=yes" onclick="return MakeConfirm(\'', _('Are you sure you wish to delete this tax authority?'), '\', \'Confirm Delete\', this);">', _('Delete'), '</a></td>
				<td><a href="', $RootPath, '/TaxAuthorityRates.php?TaxAuthority=', urlencode($MyRow[0]), '">', _('Edit Rates'), '</a></td>
				</tr>';

	}
	//END WHILE LIST LOOP

	//end of ifs and buts!

	echo '</tbody>';
	echo '</table>';
}

if (isset($SelectedTaxAuthID)) {
	echo '<div class="centre">
			<a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">', _('Review all defined tax authority records'), '</a>
		</div>';
}


echo '<form method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

if (isset($SelectedTaxAuthID)) {
	//editing an existing tax authority

	$SQL = "SELECT taxglcode,
				purchtaxglaccount,
				description,
				bank,
				bankacc,
				bankacctype,
				bankswift
			FROM taxauthorities
			WHERE taxid='" . $SelectedTaxAuthID . "'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['TaxGLCode'] = $MyRow['taxglcode'];
	$_POST['PurchTaxGLCode'] = $MyRow['purchtaxglaccount'];
	$_POST['Description'] = $MyRow['description'];
	$_POST['Bank'] = $MyRow['bank'];
	$_POST['BankAccType'] = $MyRow['bankacctype'];
	$_POST['BankAcc'] = $MyRow['bankacc'];
	$_POST['BankSwift'] = $MyRow['bankswift'];


	echo '<input type="hidden" name="SelectedTaxAuthID" value="', $SelectedTaxAuthID, '" />';

} //end of if $SelectedTaxAuthID only do the else when a new record is being entered

$SQL = "SELECT accountcode,
				accountname
		FROM chartmaster
		INNER JOIN accountgroups
		ON chartmaster.groupcode=accountgroups.groupcode
		AND chartmaster.language=accountgroups.language
		WHERE accountgroups.pandl=0
			AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
		ORDER BY accountcode";
$Result = DB_query($SQL);
while ($MyRow = DB_fetch_array($Result)) {
	$GLAccounts[$MyRow['accountcode']] = $MyRow['accountname'];
}

if (!isset($_POST['Description'])) {
	$_POST['Description'] = '';
}
echo '<table class="selection">
		<tr>
			<td>', _('Tax Type Description'), ':</td>
			<td><input type="text" name="Description" size="21" required="required" maxlength="40" value="', $_POST['Description'], '" /></td>
		</tr>
		<tr>
			<td>' . _('Input tax GL Account') . ':</td>
			<td><select required="required" name="PurchTaxGLCode">';

foreach ($GLAccounts as $Code => $Name) {
	if (isset($_POST['PurchTaxGLCode']) and $Code == $_POST['PurchTaxGLCode']) {
		echo '<option selected="selected" value="', $Code, '">', $Name, ' (', $Code,  ')</option>';
	} else {
		echo '<option value="', $Code, '">', $Name, ' (', $Code,  ')</option>';
	}
} //end while loop

echo '</select>
		</td>
	</tr>';

echo '<tr>
		<td>', _('Output tax GL Account'), ':</td>
		<td><select required="required" name="TaxGLCode">';

foreach ($GLAccounts as $Code => $Name) {
	if (isset($_POST['TaxGLCode']) and $Code == $_POST['TaxGLCode']) {
		echo '<option selected="selected" value="', $Code, '">', $Name, ' (', $Code,  ')</option>';
	} else {
		echo '<option value="', $Code, '">', $Name, ' (', $Code,  ')</option>';
	}
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
if (!isset($_POST['BankSwift'])) {
	$_POST['BankSwift'] = '';
}

echo '</select></td>
	</tr>
	<tr>
		<td>', _('Bank Name'), ':</td>
		<td><input type="text" name="Bank" size="41" maxlength="40" value="', $_POST['Bank'], '" /></td>
	</tr>
	<tr>
		<td>', _('Bank Account Type'), ':</td>
		<td><input type="text" name="BankAccType" size="15" maxlength="20" value="', $_POST['BankAccType'], '" /></td>
	</tr>
	<tr>
		<td>', _('Bank Account'), ':</td>
		<td><input type="text" name="BankAcc" size="21" maxlength="20" value="', $_POST['BankAcc'], '" /></td>
	</tr>
	<tr>
		<td>', _('Bank Swift No'), ':</td>
		<td><input type="text" name="BankSwift" size="15" maxlength="14" value="', $_POST['BankSwift'], '" /></td>
	</tr>
</table>';

echo '<div class="centre">
			<input type="submit" name="submit" value="', _('Enter Information'), '" />
		</div>
	</form>';

echo '<div class="centre">
		<a href="', $RootPath, '/TaxGroups.php">', _('Tax Group Maintenance'), '</a>
		<a href="', $RootPath, '/TaxProvinces.php">', _('Dispatch Tax Province Maintenance'), '</a>
		<a href="', $RootPath, '/TaxCategories.php">', _('Tax Category Maintenance'), '</a>
	</div>';


include('includes/footer.inc');

?>