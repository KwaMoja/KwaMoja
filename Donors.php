<?php
$PageSecurity=1;
include ('includes/session.inc');

$Title = _('Donor Maintenance');
/* Manual links before header.inc */
if (isset($_POST['Edit']) or isset($_GET['Edit']) or isset($_GET['DonorNo'])) {
//	$ViewTopic = 'AccountsReceivable';
//	$BookMark = 'AmendCustomer';
} else {
//	$ViewTopic = 'AccountsReceivable';
//	$BookMark = 'NewCustomer';
}
include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');
include ('includes/CountriesArray.php');

echo '<p class="page_title_text" >
		<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/customer.png" title="' . _('Donors') . '" alt="" />' . ' ' . _('Donor Maintenance') . '
	</p>';

if (isset($Errors)) {
	unset($Errors);
} //isset($Errors)
$Errors = array();

if (isset($_POST['submit'])) {
	//initialise no input errors assumed initially before we test
	$InputError = 0;
	/* actions to take once the user has clicked the submit button
	 ie the page has called itself with some user input */
	//first off validate inputs sensible
	if (mb_strlen($_POST['DonorName']) > 40 or mb_strlen($_POST['DonorName']) == 0) {
		$InputError = 1;
		prnMsg(_('The donor name must be entered and be forty characters or less long'), 'error');
	} elseif (mb_strlen($_POST['Address1']) > 40) {
		$InputError = 1;
		prnMsg(_('The Line 1 of the address must be forty characters or less long'), 'error');
	} elseif (mb_strlen($_POST['Address2']) > 40) {
		$InputError = 1;
		prnMsg(_('The Line 2 of the address must be forty characters or less long'), 'error');
	} elseif (mb_strlen($_POST['Address3']) > 40) {
		$InputError = 1;
		prnMsg(_('The Line 3 of the address must be forty characters or less long'), 'error');
	} elseif (mb_strlen($_POST['Address4']) > 50) {
		$InputError = 1;
		prnMsg(_('The Line 4 of the address must be fifty characters or less long'), 'error');
	} elseif (mb_strlen($_POST['Address5']) > 20) {
		$InputError = 1;
		prnMsg(_('The Line 5 of the address must be twenty characters or less long'), 'error');
	}
	if ($InputError != 1) {
		if (!isset($_POST['New'])) {
			$SQL = "UPDATE donors SET name='" . $_POST['DonorName'] . "',
										address1='" . $_POST['Address1'] . "',
										address2='" . $_POST['Address2'] . "',
										address3='" . $_POST['Address3'] . "',
										address4='" . $_POST['Address4'] . "',
										address5='" . $_POST['Address5'] . "',
										address6='" . $_POST['Address6'] . "',
										currcode='" . $_POST['CurrCode'] . "',
										language_id='" . $_POST['LanguageID'] . "'
									WHERE donorno = '" . stripslashes($_POST['DonorNo']) . "'";
			$ErrMsg = _('The donor could not be updated because');
			$Result = DB_query($SQL, $ErrMsg);
			prnMsg(_('Donor updated'), 'success');
			include ('includes/footer.inc');
			exit;
		} else { //it is a new customer
			/* set the DonorNo if $AutoDonorNo in config.php has been set to
			 something greater 0 */
			$_POST['DonorNo'] = GetNextTransNo(510);

			$SQL = "INSERT INTO donors (donorno,
										name,
										address1,
										address2,
										address3,
										address4,
										address5,
										address6,
										currcode,
										language_id
									) VALUES (
										'" . $_POST['DonorNo'] . "',
										'" . $_POST['DonorName'] . "',
										'" . $_POST['Address1'] . "',
										'" . $_POST['Address2'] . "',
										'" . $_POST['Address3'] . "',
										'" . $_POST['Address4'] . "',
										'" . $_POST['Address5'] . "',
										'" . $_POST['Address6'] . "',
										'" . $_POST['CurrCode'] . "',
										'" . $_POST['LanguageID'] . "'
									)";
			$ErrMsg = _('This donor could not be added because');
			$Result = DB_query($SQL, $ErrMsg);
			prnMsg(_('The donor was created successfuly'), 'success');
			include ('includes/footer.inc');
			exit;
		}
	} else {
		prnMsg(_('Validation failed') . '. ' . _('No updates or deletes took place'), 'error');
	}
}

if (isset($_POST['Reset'])) {
	unset($_POST['DonorName']);
	unset($_POST['Address1']);
	unset($_POST['Address2']);
	unset($_POST['Address3']);
	unset($_POST['Address4']);
	unset($_POST['Address5']);
	unset($_POST['Address6']);
	unset($_POST['LanguageID']);

} //isset($_POST['Reset'])

/*DonorNo could be set from a post or a get when passed as a parameter to this page */

if (isset($_POST['DonorNo'])) {
	$DonorNo = stripslashes($_POST['DonorNo']);
} elseif (isset($_GET['DonorNo'])) {
	$DonorNo = stripslashes($_GET['DonorNo']);
} if (isset($_POST['ID'])) {
	$ID = $_POST['ID'];
} elseif (isset($_GET['ID'])) {
	$ID = $_GET['ID'];
} else {
	$ID = '';
}
if (isset($_POST['Edit'])) {
	$Edit = $_POST['Edit'];
} elseif (isset($_GET['Edit'])) {
	$Edit = $_GET['Edit'];
} else {
	$Edit = '';
}

if (isset($_POST['Add'])) {
	$Add = $_POST['Add'];
} elseif (isset($_GET['Add'])) {
	$Add = $_GET['Add'];
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (!isset($DonorNo)) {
	/* if $AutoDonorNo in config.php has not been set or if it has been set to a number less than one,
	 then provide an input box for the DonorNo to manually assigned */
	//DonorNo exists - either passed when calling the form or from the form itself
	$_POST['New'] = true;
	$_POST['DonorName'] = '';
	$_POST['Address1'] = '';
	$_POST['Address2'] = '';
	$_POST['Address3'] = '';
	$_POST['Address4'] = '';
	$_POST['Address5'] = '';
	$_POST['Address6'] = $CountriesArray[$_SESSION['CountryOfOperation']];
	$_POST['CurrCode'] = $_SESSION['CompanyRecord']['currencydefault'];
	$_POST['LanguageID'] = '';
	//Sub table
	echo '<table class="selection" width="100%">';
} else {
	$SQL = "SELECT donorno,
					name,
					address1,
					address2,
					address3,
					address4,
					address5,
					address6,
					currcode,
					language_id
				FROM donors
				WHERE donorno = '" . $DonorNo . "'";
	$ErrMsg = _('The donors details could not be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);
	$MyRow = DB_fetch_array($Result);
	/* if $AutoDonorNo in config.php has not been set or if it has been set to a number less than one,
	 then display the DonorNo */
	$_POST['DonorName'] = $MyRow['name'];
	$_POST['Address1'] = $MyRow['address1'];
	$_POST['Address2'] = $MyRow['address2'];
	$_POST['Address3'] = $MyRow['address3'];
	$_POST['Address4'] = $MyRow['address4'];
	$_POST['Address5'] = $MyRow['address5'];
	$_POST['Address6'] = $MyRow['address6'];
	$_POST['CurrCode'] = $MyRow['currcode'];
	$_POST['LanguageID'] = $MyRow['language_id'];
	echo '<input type="hidden" name="DonorNo" value="' . $DonorNo . '" />';
	echo '<table class="selection" width="100%">';
	echo '<tr>
			<td>' . _('Donor Code') . ':</td>
			<td>' . stripslashes($DonorNo) . '</td>
		</tr>';

}

echo '<tr>
		<td>' . _('Donor Name') . ':</td>
		<td><input type="text" name="DonorName" value="' . $_POST['DonorName'] . '" size="42" required="required" maxlength="40" /></td>
	</tr>
	<tr>
		<td>' . _('Address Line 1 (Street)') . ':</td>
		<td><input type="text" name="Address1" size="42" maxlength="40" value="' . $_POST['Address1'] . '" /></td>
	</tr>
	<tr>
		<td>' . _('Address Line 2 (Street)') . ':</td>
		<td><input type="text" name="Address2" size="42" maxlength="40" value="' . $_POST['Address2'] . '" /></td>
	</tr>
	<tr>
		<td>' . _('Address Line 3 (Suburb/City)') . ':</td>
		<td><input type="text" name="Address3" size="42" maxlength="40" value="' . $_POST['Address3'] . '" /></td>
	</tr>
	<tr>
		<td>' . _('Address Line 4 (State/Province)') . ':</td>
		<td><input type="text" name="Address4" size="42" maxlength="40" value="' . $_POST['Address4'] . '" /></td>
	</tr>
	<tr>
		<td>' . _('Address Line 5 (Postal Code)') . ':</td>
		<td><input type="text" name="Address5" size="42" maxlength="40" value="' . $_POST['Address5'] . '" /></td>
	</tr>
	<tr>
		<td>' . _('Country') . ':</td>
		<td><select name="Address6">';
foreach ($CountriesArray as $CountryEntry => $CountryName) {
	if (isset($_POST['Address6']) and (strtoupper($_POST['Address6']) == strtoupper($CountryName))) {
		echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName . '</option>';
	} //isset($_POST['Address6']) and ($_POST['Address6'] == $CountryName)
	elseif (!isset($_POST['Address6']) and $CountryName == "") {
		echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName . '</option>';
	} //!isset($_POST['Address6']) and $CountryName == ""
	else {
		echo '<option value="' . $CountryName . '">' . $CountryName . '</option>';
	}
}
echo '</select>
			</td>
		</tr>';

$Result = DB_query("SELECT currency, currabrev FROM currencies");
echo '<tr>
		<td>' . _('Donor Currency') . ':</td>
		<td><select name="CurrCode">';
while ($MyRow = DB_fetch_array($Result)) {
	if ($_POST['CurrCode'] == $MyRow['currabrev']) {
		echo '<option selected="selected" value="' . $MyRow['currabrev'] . '">' . $MyRow['currency'] . '</option>';
	} //$_POST['CurrCode'] == $MyRow['currabrev']
	else {
		echo '<option value="' . $MyRow['currabrev'] . '">' . $MyRow['currency'] . '</option>';
	}
} //end while loop
echo '</select>
			</td>
		</tr>';

if (!isset($_POST['LanguageID']) or $_POST['LanguageID'] == '') {
	$_POST['LanguageID'] = $_SESSION['Language'];
}

echo '<tr>
		<td>' . _('Language') . ':</td>
		<td><select name="LanguageID" required="required">';
foreach ($LanguagesArray as $LanguageCode => $LanguageName) {
	if ($_POST['LanguageID'] == $LanguageCode) {
		echo '<option selected="selected" value="' . $LanguageCode . '">' . $LanguageName['LanguageName'] . '</option>';
	} else {
		echo '<option value="' . $LanguageCode . '">' . $LanguageName['LanguageName'] . '</option>';
	}
}
echo '</select>
			</td>
		</tr>';

echo '</table>';

if (isset($_POST['New']) and $_POST['New']) {
	echo '<input type="hidden" name="New" value="Yes" />';
	echo '<div class="centre">
			<input type="submit" name="submit" value="' . _('Add New Donor') . '" />&nbsp;
			<input type="submit" name="Reset" value="' . _('Reset') . '" />
		</div>';
} elseif (!isset($_GET['Modify'])) {
	echo '<div class="centre">
			<input type="submit" name="submit" value="' . _('Update Donor Information') . '" />&nbsp;
		</div>';
} //!isset($_GET['Modify'])

echo '</form>';

include ('includes/footer.inc');
?>