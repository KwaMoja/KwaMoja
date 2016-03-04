<?php

include('includes/session.inc');

$Title = _('Company Preferences');
/* Manual links before header.inc */
$ViewTopic = 'CreatingNewSystem';
$BookMark = 'CompanyParameters';
include('includes/header.inc');

if (isset($Errors)) {
	unset($Errors);
}

//initialise no input errors assumed initially before we test
$InputError = 0;
$Errors = array();
$i = 1;

if (isset($_POST['submit'])) {


	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (mb_strlen($_POST['CoyName']) > 50 or mb_strlen($_POST['CoyName']) == 0) {
		$InputError = 1;
		prnMsg(_('The company name must be entered and be fifty characters or less long'), 'error');
		$Errors[$i] = 'CoyName';
		++$i;
	}
	if (mb_strlen($_POST['RegOffice1']) > 40) {
		$InputError = 1;
		prnMsg(_('The Line 1 of the address must be forty characters or less long'), 'error');
		$Errors[$i] = 'RegOffice1';
		++$i;
	}
	if (mb_strlen($_POST['RegOffice2']) > 40) {
		$InputError = 1;
		prnMsg(_('The Line 2 of the address must be forty characters or less long'), 'error');
		$Errors[$i] = 'RegOffice2';
		++$i;
	}
	if (mb_strlen($_POST['RegOffice3']) > 40) {
		$InputError = 1;
		prnMsg(_('The Line 3 of the address must be forty characters or less long'), 'error');
		$Errors[$i] = 'RegOffice3';
		++$i;
	}
	if (mb_strlen($_POST['RegOffice4']) > 40) {
		$InputError = 1;
		prnMsg(_('The Line 4 of the address must be forty characters or less long'), 'error');
		$Errors[$i] = 'RegOffice4';
		++$i;
	}
	if (mb_strlen($_POST['RegOffice5']) > 20) {
		$InputError = 1;
		prnMsg(_('The Line 5 of the address must be twenty characters or less long'), 'error');
		$Errors[$i] = 'RegOffice5';
		++$i;
	}
	if (mb_strlen($_POST['RegOffice6']) > 15) {
		$InputError = 1;
		prnMsg(_('The Line 6 of the address must be fifteen characters or less long'), 'error');
		$Errors[$i] = 'RegOffice6';
		++$i;
	}
	if (mb_strlen($_POST['Telephone']) > 25) {
		$InputError = 1;
		prnMsg(_('The telephone number must be 25 characters or less long'), 'error');
		$Errors[$i] = 'Telephone';
		++$i;
	}
	if (mb_strlen($_POST['Fax']) > 25) {
		$InputError = 1;
		prnMsg(_('The fax number must be 25 characters or less long'), 'error');
		$Errors[$i] = 'Fax';
		++$i;
	}
	if (mb_strlen($_POST['Email']) > 55) {
		$InputError = 1;
		prnMsg(_('The email address must be 55 characters or less long'), 'error');
		$Errors[$i] = 'Email';
		++$i;
	}
	if (mb_strlen($_POST['Email']) > 0 and !IsEmailAddress($_POST['Email'])) {
		$InputError = 1;
		prnMsg(_('The email address is not correctly formed'), 'error');
		$Errors[$i] = 'Email';
		++$i;
	}

	if ($InputError != 1) {

		$CompanySQL = "SELECT coycode FROM companies";
		$CompanyResult = DB_query($CompanySQL);
		if (DB_num_rows($CompanyResult) == 0) {
			$SQL = "INSERT INTO companies (coycode,
											coyname,
											companynumber,
											gstno,
											regoffice1,
											regoffice2,
											regoffice3,
											regoffice4,
											regoffice5,
											regoffice6,
											telephone,
											fax,
											email,
											currencydefault,
											debtorsact,
											pytdiscountact,
											creditorsact,
											payrollact,
											grnact,
											exchangediffact,
											purchasesexchangediffact,
											retainedearnings,
											gllink_debtors,
											gllink_creditors,
											gllink_stock,
											freightact
										) VALUES (
											1,
											'" . $_POST['CoyName'] . "',
											'" . $_POST['CompanyNumber'] . "',
											'" . $_POST['GSTNo'] . "',
											'" . $_POST['RegOffice1'] . "',
											'" . $_POST['RegOffice2'] . "',
											'" . $_POST['RegOffice3'] . "',
											'" . $_POST['RegOffice4'] . "',
											'" . $_POST['RegOffice5'] . "',
											'" . $_POST['RegOffice6'] . "',
											'" . $_POST['Telephone'] . "',
											'" . $_POST['Fax'] . "',
											'" . $_POST['Email'] . "',
											'" . $_POST['CurrencyDefault'] . "',
											'" . $_POST['DebtorsAct'] . "',
											'" . $_POST['PytDiscountAct'] . "',
											'" . $_POST['CreditorsAct'] . "',
											'" . $_POST['PayrollAct'] . "',
											'" . $_POST['GRNAct'] . "',
											'" . $_POST['ExchangeDiffAct'] . "',
											'" . $_POST['PurchasesExchangeDiffAct'] . "',
											'" . $_POST['RetainedEarnings'] . "',
											'" . $_POST['GLLink_Debtors'] . "',
											'" . $_POST['GLLink_Creditors'] . "',
											'" . $_POST['GLLink_Stock'] . "',
											'" . $_POST['FreightAct'] . "'
										)";
		} else {

			$SQL = "UPDATE companies SET coyname='" . $_POST['CoyName'] . "',
										companynumber = '" . $_POST['CompanyNumber'] . "',
										gstno='" . $_POST['GSTNo'] . "',
										regoffice1='" . $_POST['RegOffice1'] . "',
										regoffice2='" . $_POST['RegOffice2'] . "',
										regoffice3='" . $_POST['RegOffice3'] . "',
										regoffice4='" . $_POST['RegOffice4'] . "',
										regoffice5='" . $_POST['RegOffice5'] . "',
										regoffice6='" . $_POST['RegOffice6'] . "',
										telephone='" . $_POST['Telephone'] . "',
										fax='" . $_POST['Fax'] . "',
										email='" . $_POST['Email'] . "',
										currencydefault='" . $_POST['CurrencyDefault'] . "',
										debtorsact='" . $_POST['DebtorsAct'] . "',
										pytdiscountact='" . $_POST['PytDiscountAct'] . "',
										creditorsact='" . $_POST['CreditorsAct'] . "',
										payrollact='" . $_POST['PayrollAct'] . "',
										grnact='" . $_POST['GRNAct'] . "',
										exchangediffact='" . $_POST['ExchangeDiffAct'] . "',
										purchasesexchangediffact='" . $_POST['PurchasesExchangeDiffAct'] . "',
										retainedearnings='" . $_POST['RetainedEarnings'] . "',
										gllink_debtors='" . $_POST['GLLink_Debtors'] . "',
										gllink_creditors='" . $_POST['GLLink_Creditors'] . "',
										gllink_stock='" . $_POST['GLLink_Stock'] . "',
										freightact='" . $_POST['FreightAct'] . "'
									WHERE coycode=1";
		}

		$ErrMsg = _('The company preferences could not be updated because');
		$Result = DB_query($SQL, $ErrMsg);
		prnMsg(_('Company preferences updated'), 'success');

		/* Alter the exchange rates in the currencies table */

		/* Get default currency rate */
		$SQL = "SELECT rate from currencies WHERE currabrev='" . $_POST['CurrencyDefault'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		$NewCurrencyRate = $MyRow[0];

		/* Set new rates */
		$SQL = "UPDATE currencies SET rate=rate/" . $NewCurrencyRate;
		$ErrMsg = _('Could not update the currency rates');
		$Result = DB_query($SQL, $ErrMsg);

		/* End of update currencies */

		$ForceConfigReload = True; // Required to force a load even if stored in the session vars
		include('includes/GetConfig.php');
		$ForceConfigReload = False;

	} else {
		prnMsg(_('Validation failed') . ', ' . _('no updates or deletes took place'), 'warn');
	}

}
/* end of if submit */

echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="selection">';

if ($InputError != 1) {
	$SQL = "SELECT coyname,
					gstno,
					companynumber,
					regoffice1,
					regoffice2,
					regoffice3,
					regoffice4,
					regoffice5,
					regoffice6,
					telephone,
					fax,
					email,
					currencydefault,
					debtorsact,
					pytdiscountact,
					creditorsact,
					payrollact,
					grnact,
					exchangediffact,
					purchasesexchangediffact,
					retainedearnings,
					gllink_debtors,
					gllink_creditors,
					gllink_stock,
					freightact
				FROM companies
				WHERE coycode=1";

	$ErrMsg = _('The company preferences could not be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);

	$MyRow = DB_fetch_array($Result);

	$_POST['CoyName'] = $MyRow['coyname'];
	$_POST['GSTNo'] = $MyRow['gstno'];
	$_POST['CompanyNumber'] = $MyRow['companynumber'];
	$_POST['RegOffice1'] = $MyRow['regoffice1'];
	$_POST['RegOffice2'] = $MyRow['regoffice2'];
	$_POST['RegOffice3'] = $MyRow['regoffice3'];
	$_POST['RegOffice4'] = $MyRow['regoffice4'];
	$_POST['RegOffice5'] = $MyRow['regoffice5'];
	$_POST['RegOffice6'] = $MyRow['regoffice6'];
	$_POST['Telephone'] = $MyRow['telephone'];
	$_POST['Fax'] = $MyRow['fax'];
	$_POST['Email'] = $MyRow['email'];
	$_POST['CurrencyDefault'] = $MyRow['currencydefault'];
	$_POST['DebtorsAct'] = $MyRow['debtorsact'];
	$_POST['PytDiscountAct'] = $MyRow['pytdiscountact'];
	$_POST['CreditorsAct'] = $MyRow['creditorsact'];
	$_POST['PayrollAct'] = $MyRow['payrollact'];
	$_POST['GRNAct'] = $MyRow['grnact'];
	$_POST['ExchangeDiffAct'] = $MyRow['exchangediffact'];
	$_POST['PurchasesExchangeDiffAct'] = $MyRow['purchasesexchangediffact'];
	$_POST['RetainedEarnings'] = $MyRow['retainedearnings'];
	$_POST['GLLink_Debtors'] = $MyRow['gllink_debtors'];
	$_POST['GLLink_Creditors'] = $MyRow['gllink_creditors'];
	$_POST['GLLink_Stock'] = $MyRow['gllink_stock'];
	$_POST['FreightAct'] = $MyRow['freightact'];
}

if (DB_num_rows($Result) == 0) {
	echo '<div class="page_help_text">' . _('As this is the first time that the system has been used, you must first fill out the company details.') .
			'<br />' . _('Once you have filled in all the details, click on the button at the bottom of the screen') . '</div>';
	include('companies/' . $_SESSION['DatabaseName'] . '/Companies.php');
	$_POST['CoyName'] = $CompanyName[$_SESSION['DatabaseName']];
}

echo '<tr>
		<td>' . _('Name') . ' (' . _('to appear on reports') . '):</td>
		<td><input tabindex="1" type="text" name="CoyName" value="' . stripslashes($_POST['CoyName']) . '" size="52" required="required" maxlength="50" /></td>
	</tr>';

echo '<tr>
		<td>' . _('Official Company Number') . ':</td>
		<td><input tabindex="2" type="text" name="CompanyNumber" value="' . $_POST['CompanyNumber'] . '" size="22" maxlength="20" /></td>
	</tr>';

echo '<tr>
		<td>' . _('Tax Authority Reference') . ':</td>
		<td><input tabindex="3" type="text" name="GSTNo" value="' . stripslashes($_POST['GSTNo']) . '" size="22" maxlength="20" /></td>
	</tr>';

echo '<tr>
		<td>' . _('Address Line 1') . ':</td>
		<td><input tabindex="4" type="text" name="RegOffice1" size="42" maxlength="40" value="' . stripslashes($_POST['RegOffice1']) . '" /></td>
	</tr>';

echo '<tr>
		<td>' . _('Address Line 2') . ':</td>
		<td><input tabindex="5" type="text" name="RegOffice2" size="42" maxlength="40" value="' . stripslashes($_POST['RegOffice2']) . '" /></td>
	</tr>';

echo '<tr>
		<td>' . _('Address Line 3') . ':</td>
		<td><input tabindex="6" type="text" name="RegOffice3" size="42" maxlength="40" value="' . stripslashes($_POST['RegOffice3']) . '" /></td>
	</tr>';

echo '<tr>
		<td>' . _('Address Line 4') . ':</td>
		<td><input tabindex="7" type="text" name="RegOffice4" size="42" maxlength="40" value="' . stripslashes($_POST['RegOffice4']) . '" /></td>
</tr>';

echo '<tr>
		<td>' . _('Address Line 5') . ':</td>
		<td><input tabindex="8" type="text" name="RegOffice5" size="22" maxlength="20" value="' . stripslashes($_POST['RegOffice5']) . '" /></td>
	</tr>';

echo '<tr>
		<td>' . _('Address Line 6') . ':</td>
		<td><input tabindex="9" type="text" name="RegOffice6" size="17" maxlength="15" value="' . stripslashes($_POST['RegOffice6']) . '" /></td>
	</tr>';

echo '<tr>
		<td>' . _('Telephone Number') . ':</td>
		<td><input tabindex="10" type="tel" name="Telephone" size="26" maxlength="25" value="' . $_POST['Telephone'] . '" /></td>
	</tr>';

echo '<tr>
		<td>' . _('Facsimile Number') . ':</td>
		<td><input tabindex="11" type="tel" name="Fax" size="26" maxlength="25" value="' . $_POST['Fax'] . '" /></td>
	</tr>';

echo '<tr>
		<td>' . _('Email Address') . ':</td>
		<td><input tabindex="12" type="email" name="Email" size="50" maxlength="55" value="' . $_POST['Email'] . '" /></td>
	</tr>';


$Result = DB_query("SELECT currabrev, currency FROM currencies");

echo '<tr>
		<td>' . _('Home Currency') . ':</td>
		<td><select tabindex="13" name="CurrencyDefault">';

while ($MyRow = DB_fetch_array($Result)) {
	if ($_POST['CurrencyDefault'] == $MyRow['currabrev']) {
		echo '<option selected="selected" value="' . $MyRow['currabrev'] . '">' . _($MyRow['currency']) . '</option>';
	} else {
		echo '<option value="' . $MyRow['currabrev'] . '">' . _($MyRow['currency']) . '</option>';
	}
} //end while loop

DB_free_result($Result);

echo '</select></td>
	</tr>';

$Result = DB_query("SELECT accountcode,
						accountname
					FROM chartmaster
					INNER JOIN accountgroups
						ON chartmaster.groupcode=accountgroups.groupcode
						AND chartmaster.language=accountgroups.language
					WHERE accountgroups.pandl=0
						AND chartmaster.Language='" . $_SESSION['ChartLanguage'] . "'
					ORDER BY chartmaster.accountcode");

echo '<tr>
		<td>' . _('Debtors Control GL Account') . ':</td>
		<td><select tabindex="14" name="DebtorsAct">';

while ($MyRow = DB_fetch_row($Result)) {
	if ($_POST['DebtorsAct'] == $MyRow[0]) {
		echo '<option selected="selected" value="' . $MyRow[0] . '">' . htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8') . ' (' . $MyRow[0] . ')</option>';
	} else {
		echo '<option value="' . $MyRow[0] . '">' . htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8') . ' (' . $MyRow[0] . ')</option>';
	}
} //end while loop

DB_data_seek($Result, 0);

echo '</select></td>
	</tr>';

echo '<tr>
		<td>' . _('Creditors Control GL Account') . ':</td>
		<td><select tabindex="15" name="CreditorsAct">';

while ($MyRow = DB_fetch_row($Result)) {
	if ($_POST['CreditorsAct'] == $MyRow[0]) {
		echo '<option selected="selected" value="' . $MyRow[0] . '">' . htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8') . ' (' . $MyRow[0] . ')</option>';
	} else {
		echo '<option value="' . $MyRow[0] . '">' . htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8') . ' (' . $MyRow[0] . ')</option>';
	}
} //end while loop

DB_data_seek($Result, 0);

echo '</select></td>
	</tr>';

echo '<tr>
		<td>' . _('Payroll Net Pay Clearing GL Account') . ':</td>
		<td><select tabindex="16" name="PayrollAct">';

while ($MyRow = DB_fetch_row($Result)) {
	if ($_POST['PayrollAct'] == $MyRow[0]) {
		echo '<option selected="selected" value="' . $MyRow[0] . '">' . htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8') . ' (' . $MyRow[0] . ')</option>';
	} else {
		echo '<option value="' . $MyRow[0] . '">' . htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8') . ' (' . $MyRow[0] . ')</option>';
	}
} //end while loop

DB_data_seek($Result, 0);

echo '</select></td>
	</tr>';

echo '<tr>
		<td>' . _('Goods Received Clearing GL Account') . ':</td>
		<td><select tabindex="17" name="GRNAct">';

while ($MyRow = DB_fetch_row($Result)) {
	if ($_POST['GRNAct'] == $MyRow[0]) {
		echo '<option selected="selected" value="' . $MyRow[0] . '">' . htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8') . ' (' . $MyRow[0] . ')</option>';
	} else {
		echo '<option value="' . $MyRow[0] . '">' . htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8') . ' (' . $MyRow[0] . ')</option>';
	}
} //end while loop

DB_data_seek($Result, 0);
echo '</select></td>
	</tr>';

echo '<tr>
		<td>' . _('Retained Earning Clearing GL Account') . ':</td>
		<td><select tabindex="18" name="RetainedEarnings">';

while ($MyRow = DB_fetch_row($Result)) {
	if ($_POST['RetainedEarnings'] == $MyRow[0]) {
		echo '<option selected="selected" value="' . $MyRow[0] . '">' . htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8') . ' (' . $MyRow[0] . ')</option>';
	} else {
		echo '<option value="' . $MyRow[0] . '">' . htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8') . ' (' . $MyRow[0] . ')</option>';
	}
} //end while loop

DB_free_result($Result);

echo '</select></td>
	</tr>';

echo '<tr>
		<td>' . _('Freight Re-charged GL Account') . ':</td>
		<td><select tabindex="19" name="FreightAct">';

$Result = DB_query("SELECT accountcode,
						accountname
					FROM chartmaster
					INNER JOIN accountgroups
						ON chartmaster.groupcode=accountgroups.groupcode
						AND chartmaster.language=accountgroups.language
					WHERE accountgroups.pandl=1
						AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
					ORDER BY chartmaster.accountcode");

while ($MyRow = DB_fetch_row($Result)) {
	if ($_POST['FreightAct'] == $MyRow[0]) {
		echo '<option selected="selected" value="' . $MyRow[0] . '">' . htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8') . ' (' . $MyRow[0] . ')</option>';
	} else {
		echo '<option value="' . $MyRow[0] . '">' . htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8') . ' (' . $MyRow[0] . ')</option>';
	}
} //end while loop

DB_data_seek($Result, 0);

echo '</select></td>
	</tr>';

echo '<tr>
		<td>' . _('Sales Exchange Variances GL Account') . ':</td>
		<td><select tabindex="20" name="ExchangeDiffAct">';

while ($MyRow = DB_fetch_row($Result)) {
	if ($_POST['ExchangeDiffAct'] == $MyRow[0]) {
		echo '<option selected="selected" value="' . $MyRow[0] . '">' . htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8') . ' (' . $MyRow[0] . ')</option>';
	} else {
		echo '<option value="' . $MyRow[0] . '">' . htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8') . ' (' . $MyRow[0] . ')</option>';
	}
} //end while loop

DB_data_seek($Result, 0);

echo '</select></td>
	</tr>';

echo '<tr>
		<td>' . _('Purchases Exchange Variances GL Account') . ':</td>
		<td><select tabindex="21" name="PurchasesExchangeDiffAct">';

while ($MyRow = DB_fetch_row($Result)) {
	if ($_POST['PurchasesExchangeDiffAct'] == $MyRow[0]) {
		echo '<option selected="selected" value="' . $MyRow[0] . '">' . htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8') . ' (' . $MyRow[0] . ')</option>';
	} else {
		echo '<option  value="' . $MyRow[0] . '">' . htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8') . ' (' . $MyRow[0] . ')</option>';
	}
} //end while loop

DB_data_seek($Result, 0);

echo '</select></td>
	</tr>';

echo '<tr>
		<td>' . _('Payment Discount GL Account') . ':</td>
		<td><select tabindex="22" name="PytDiscountAct">';

while ($MyRow = DB_fetch_row($Result)) {
	if ($_POST['PytDiscountAct'] == $MyRow[0]) {
		echo '<option selected="selected" value="' . $MyRow[0] . '">' . htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8') . ' (' . $MyRow[0] . ')</option>';
	} else {
		echo '<option value="' . $MyRow[0] . '">' . htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8') . ' (' . $MyRow[0] . ')</option>';
	}
} //end while loop

DB_data_seek($Result, 0);

echo '</select></td>
	</tr>';

echo '<tr>
		<td>' . _('Create GL entries for accounts receivable transactions') . ':</td>
		<td><select tabindex="23" name="GLLink_Debtors">';

if ($_POST['GLLink_Debtors'] == 0) {
	echo '<option selected="selected" value="0">' . _('No') . '</option>';
	echo '<option value="1">' . _('Yes') . '</option>';
} else {
	echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	echo '<option value="0">' . _('No') . '</option>';
}

echo '</select></td>
	</tr>';

echo '<tr>
		<td>' . _('Create GL entries for accounts payable transactions') . ':</td>
		<td><select tabindex="24" name="GLLink_Creditors">';

if ($_POST['GLLink_Creditors'] == 0) {
	echo '<option selected="selected" value="0">' . _('No') . '</option>';
	echo '<option value="1">' . _('Yes') . '</option>';
} else {
	echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	echo '<option value="0">' . _('No') . '</option>';
}

echo '</select></td>
	</tr>';

echo '<tr>
		<td>' . _('Create GL entries for stock transactions') . ':</td>
		<td><select tabindex="25" name="GLLink_Stock">';

if ($_POST['GLLink_Stock'] == '0') {
	echo '<option selected="selected" value="0">' . _('No') . '</option>';
	echo '<option value="1">' . _('Yes') . '</option>';
} else {
	echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	echo '<option value="0">' . _('No') . '</option>';
}

echo '</select></td>
	</tr>';


echo '</table>
	<br />
	<div class="centre">
		<input tabindex="26" type="submit" name="submit" value="' . _('Update') . '" />
	</div>';
echo '</form>';

include('includes/footer.inc');
?>