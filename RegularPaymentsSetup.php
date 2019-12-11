<?php
include ('includes/session.php');

$Title = _('Setup regular payments');
$ViewTopic = 'GeneralLedger';
$BookMark = 'RegularPayments';

include ('includes/header.php');

echo '<p class="page_title_text" >
		<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
	</p>';

if (isset($_GET['Complete'])) {
	$SQL = "UPDATE regularpayments SET completed=1 WHERE id='" . $_GET['Payment'] . "'";
	$ErrMsg = _('Cannot set regular payment as completed because');
	$DbgMsg = _('Cannot set regular payment as completed using the SQL');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
	if (DB_error_no() == 0) {
		prnMsg(_('The regular payment has been marked as complete and no further payments will be made'), 'success');
	} else {
		prnMsg(_('There was a problem marking this payment as completed'), 'error');
	}
}

if (isset($_GET['Edit'])) {
	$SQL = "SELECT regularpayments.frequency,
					regularpayments.days,
					regularpayments.glcode,
					regularpayments.bankaccountcode,
					regularpayments.tag,
					regularpayments.amount,
					regularpayments.currabrev,
					regularpayments.narrative,
					regularpayments.firstpayment,
					regularpayments.finalpayment,
					regularpayments.nextpayment,
					regularpayments.completed
				FROM regularpayments
				WHERE id='" . $_GET['Payment'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$_POST['Frequency'] = $MyRow['frequency'];
	$_POST['Days'] = $MyRow['days'];
	$_POST['GLManualCode'] = $MyRow['glcode'];
	$_POST['BankAccount'] = $MyRow['bankaccountcode'];
	$_POST['Tag'] = $MyRow['tag'];
	$_POST['GLAmount'] = $MyRow['amount'];
	$_POST['Currency'] = $MyRow['currabrev'];
	$_POST['GLNarrative'] = $MyRow['narrative'];
	$_POST['FirstPaymentDate'] = ConvertSQLDate($MyRow['firstpayment']);
	$_POST['LastPaymentDate'] = ConvertSQLDate($MyRow['finalpayment']);
}

if (isset($_POST['Add']) or isset($_POST['Update'])) {
	$Error = 0; //Assume everything is ok.
	if ($_POST['Frequency'] == '') {
		prnMsg(_('You must select a frequency for the payment to occur'), 'error');
		$Error = 1;
	}
	if (!isset($_POST['Days']) or $_POST['Days'] == '') {
		prnMsg(_('The days field must be an integer'), 'error');
		$Error = 1;
	}
	if (isset($_POST['Frequency']) and $_POST['Frequency'] == 'D') {
		$_POST['Days'] = 0; // If its a Daily payment then Days must be zero
		
	}
	if (isset($_POST['Frequency']) and $_POST['Frequency'] == 'W' and $_POST['Days'] > 6) {
		prnMsg(_('If the payment is to be made weekly then the days field must be an integer between 0 and 6'), 'error');
		$Error = 1;
	}
	if (isset($_POST['Frequency']) and $_POST['Frequency'] == 'F' and $_POST['Days'] > 13) {
		prnMsg(_('If the payment is to be made fortnightly then the days field must be an integer between 0 and 13'), 'error');
		$Error = 1;
	}
	if (isset($_POST['Frequency']) and $_POST['Frequency'] == 'M' and $_POST['Days'] > 31) {
		prnMsg(_('If the payment is to be made monthly then the days field must be an integer between 0 and 31'), 'error');
		$Error = 1;
	}
	if (isset($_POST['Frequency']) and $_POST['Frequency'] == 'Q' and $_POST['Days'] > 92) {
		prnMsg(_('If the payment is to be made quarterly then the days field must be an integer between 0 and 92'), 'error');
		$Error = 1;
	}
	if (isset($_POST['Frequency']) and $_POST['Frequency'] == 'Y' and $_POST['Days'] > 365) {
		prnMsg(_('If the payment is to be made annually then the days field must be an integer between 0 and 365'), 'error');
		$Error = 1;
	}
	if (!isset($_POST['BankAccount']) or $_POST['BankAccount'] == '') {
		prnMsg(_('You must select a bank account where this payment will be made from'), 'error');
		$Error = 1;
	}
	if (!isset($_POST['GLManualCode']) or $_POST['GLManualCode'] == '') {
		prnMsg(_('A general ledger code must be selected'), 'error');
		$Error = 1;
	}
	if (!isset($_POST['GLAmount']) or $_POST['GLAmount'] == '') {
		prnMsg(_('You must enter a payment amount'), 'error');
		$Error = 1;
	}
	if ($Error == 0) {
		if (isset($_POST['Update'])) {
			$SQL = "UPDATE regularpayments SET frequency='" . $_POST['Frequency'] . "',
												days='" . $_POST['Days'] . "',
												glcode='" . $_POST['GLManualCode'] . "',
												bankaccountcode='" . $_POST['BankAccount'] . "',
												tag='" . $_POST['Tag'] . "',
												amount='" . $_POST['GLAmount'] . "',
												currabrev='" . $_POST['Currency'] . "',
												narrative='" . $_POST['GLNarrative'] . "',
												firstpayment='" . FormatDateForSQL($_POST['FirstPaymentDate']) . "',
												finalpayment='" . FormatDateForSQL($_POST['LastPaymentDate']) . "'
											WHERE id='" . $_POST['ID'] . "'";
			$ErrMsg = _('Cannot update regular payment because');
			$DbgMsg = _('Cannot update regular payment using the SQL');
		} else {
			$SQL = "INSERT INTO regularpayments (frequency,
												days,
												glcode,
												bankaccountcode,
												tag,
												amount,
												currabrev,
												narrative,
												firstpayment,
												finalpayment,
												nextpayment
											) VALUES (
												'" . $_POST['Frequency'] . "',
												'" . $_POST['Days'] . "',
												'" . $_POST['GLManualCode'] . "',
												'" . $_POST['BankAccount'] . "',
												'" . $_POST['Tag'] . "',
												'" . $_POST['GLAmount'] . "',
												'" . $_POST['Currency'] . "',
												'" . $_POST['GLNarrative'] . "',
												'" . FormatDateForSQL($_POST['FirstPaymentDate']) . "',
												'" . FormatDateForSQL($_POST['LastPaymentDate']) . "',
												'" . FormatDateForSQL($_POST['FirstPaymentDate']) . "'
											)";
			$ErrMsg = _('Cannot insert a new regular payment because');
			$DbgMsg = _('Cannot insert a new regular payment using the SQL');
		}
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
		unset($_POST['ID']);
		unset($_POST['Frequency']);
		unset($_POST['Days']);
		unset($_POST['GLManualCode']);
		unset($_POST['BankAccount']);
		unset($_POST['Tag']);
		unset($_POST['GLAmount']);
		unset($_POST['Currency']);
		unset($_POST['GLNarrative']);
		unset($_POST['FirstPaymentDate']);
		unset($_POST['LastPaymentDate']);
	}
}

echo '<form method="post" id="RegularPaymentsSetup" action="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table>';
echo '<tr>
		<th colspan="2">
			<h3>' . _('Regular General Ledger Payment') . '</h3>
		</th>
	</tr>';

$Frequencies['D'] = _('Daily');
$Frequencies['W'] = _('Weekly');
$Frequencies['F'] = _('Fortnightly');
$Frequencies['M'] = _('Monthly');
$Frequencies['Q'] = _('Quarterly');
$Frequencies['Y'] = _('Annually');

if (!isset($_POST['Frequency'])) {
	$_POST['Frequency'] = '';
}
if (!isset($_POST['Days'])) {
	$_POST['Days'] = 0;
}
/*now set up a GLCode field to select from avaialble GL accounts */
if (!isset($_POST['GLManualCode'])) {
	$_POST['GLManualCode'] = '';
}
if (!isset($_POST['FirstPaymentDate'])) {
	$_POST['FirstPaymentDate'] = date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['LastPaymentDate'])) {
	$_POST['LastPaymentDate'] = date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['Currency']) or $_POST['Currency'] == '') {
	$_POST['Currency'] = $_SESSION['CompanyRecord']['currencydefault'];
}

echo '<tr>
		<td>' . _('Frequency Of Payment') . '</td>
		<td>
			<select autofocus="autofocus" name="Frequency">
				<option value=""></option>';
foreach ($Frequencies as $Initial => $Name) {
	if ($Initial == $_POST['Frequency']) {
		echo '<option selected="selected" value="' . $Initial . '">' . $Name . '</option>';
	} else {
		echo '<option value="' . $Initial . '">' . $Name . '</option>';
	}
}
echo '</select>
			&nbsp;&nbsp;&nbsp;' . _('Days') . '&nbsp;:&nbsp;&nbsp;&nbsp;<input type="number" min="0" max="366" step="1" class="number" size="4" name="Days" value="' . $_POST['Days'] . '" />
			<i>' . _('The number of days from the start of the period that the payment is to be made') . '</i>
		</td>
	</tr>
	<tr>
		<td>' . _('Date of first payment') . '</td>
		<td><input type="date" name="FirstPaymentDate" class="date" required="required" maxlength="10" size="11" onchange="isDate(this, this.value, ' . "'" . $_SESSION['DefaultDateFormat'] . "'" . ')" value="' . $_POST['FirstPaymentDate'] . '" /></td>
	</tr>
	<tr>
		<td>' . _('Date of Last payment') . '</td>
		<td><input type="date" name="LastPaymentDate" class="date" required="required" maxlength="10" size="11" onchange="isDate(this, this.value, ' . "'" . $_SESSION['DefaultDateFormat'] . "'" . ')" value="' . $_POST['LastPaymentDate'] . '" /></td>
	</tr>';

$SQL = "SELECT bankaccountname,
				bankaccounts.accountcode,
				bankaccounts.currcode
			FROM bankaccounts
			INNER JOIN chartmaster
				ON bankaccounts.accountcode=chartmaster.accountcode
			INNER JOIN bankaccountusers
				ON bankaccounts.accountcode=bankaccountusers.accountcode
			WHERE bankaccountusers.userid = '" . $_SESSION['UserID'] . "'
				AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
			ORDER BY bankaccountname";

$ErrMsg = _('The bank accounts could not be retrieved because');
$DbgMsg = _('The SQL used to retrieve the bank accounts was');
$AccountsResults = DB_query($SQL, $ErrMsg, $DbgMsg);

echo '<tr>
		<td>' . _('Bank Account') . ':</td>
		<td><select name="BankAccount">';

if (DB_num_rows($AccountsResults) == 0) {
	echo '</select></td>
		</tr>
		</table>';
	prnMsg(_('Bank Accounts have not yet been defined. You must first') . ' <a href="' . $RootPath . '/BankAccounts.php">' . _('define the bank accounts') . '</a> ' . _('and general ledger accounts to be affected'), 'warn');
	include ('includes/footer.php');
	exit;
} else {
	echo '<option value=""></option>';
	while ($MyRow = DB_fetch_array($AccountsResults)) {
		/*list the bank account names */
		if (isset($_POST['BankAccount']) and $_POST['BankAccount'] == $MyRow['accountcode']) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] . '">' . $MyRow['bankaccountname'] . ' - ' . $MyRow['currcode'] . '</option>';
		} //isset($_POST['BankAccount']) and $_POST['BankAccount'] == $MyRow['accountcode']
		else {
			echo '<option value="' . $MyRow['accountcode'] . '">' . $MyRow['bankaccountname'] . ' - ' . $MyRow['currcode'] . '</option>';
		}
	} //$MyRow = DB_fetch_array($AccountsResults)
	echo '</select></td>
		</tr>';
}

echo '<tr>
		<td>' . _('Currency of Payment') . ':</td>
		<td><select required="required" name="Currency">';
$SQL = "SELECT currency, currabrev, rate FROM currencies";
$Result = DB_query($SQL);

if (DB_num_rows($Result) == 0) {
	echo '</select></td>
		</tr>';
	prnMsg(_('No currencies are defined yet. Payments cannot be entered until a currency is defined'), 'error');
} else {
	while ($MyRow = DB_fetch_array($Result)) {
		if ($_POST['Currency'] == $MyRow['currabrev']) {
			echo '<option selected="selected" value="' . $MyRow['currabrev'] . '">' . _($MyRow['currency']) . '</option>';
		} else {
			echo '<option value="' . $MyRow['currabrev'] . '">' . _($MyRow['currency']) . '</option>';
		}
	} //$MyRow = DB_fetch_array($Result)
	echo '</select>
			<i>' . _('The transaction currency does not need to be the same as the bank account currency') . '</i></td>
		</tr>';
}
$SQL = "SELECT tagref,
				tagdescription
			FROM tags
			ORDER BY tagdescription";
$Result = DB_query($SQL);
echo '<tr>
		<td>' . _('Select Tag') . ':</td>
		<td><select name="Tag">
			<option value="0">' . _('None') . '</option>';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['Tag']) and $_POST['Tag'] == $MyRow['tagref']) {
		echo '<option selected="selected" value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
	}
} //$MyRow = DB_fetch_array($Result)
echo '</select>
		</td>
	</tr>';
// End select Tag
echo '<tr>
		<td>' . _('Enter GL Account Manually') . ':</td>
		<td><input type="text" name="GLManualCode" maxlength="12" size="12" onchange="return inArray(this, GLCode.options,' . "'" . 'The account code ' . "'" . '+ this.value+ ' . "'" . ' doesnt exist' . "'" . ')"' . ' value="' . $_POST['GLManualCode'] . '"   /></td>
	</tr>';

echo '<tr>
		<td>' . _('Select GL Group') . ':</td>
		<td><select name="GLGroup" onchange="return ReloadForm(UpdateCodes)">';

$SQL = "SELECT groupcode,
				groupname
			FROM accountgroups
			WHERE language='" . $_SESSION['ChartLanguage'] . "'
			ORDER BY sequenceintb";

$Result = DB_query($SQL);
if (DB_num_rows($Result) == 0) {
	echo '</select></td>
		</tr>';
	prnMsg(_('No General ledger account groups have been set up yet') . ' - ' . _('payments cannot be analysed against GL accounts until the GL accounts are set up'), 'error');
} else {
	echo '<option value=""></option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['GLGroup']) and ($_POST['GLGroup'] == $MyRow['groupcode'])) {
			echo '<option selected="selected" value="', $MyRow['groupcode'], '">', $MyRow['groupcode'], ' - ', $MyRow['groupname'], '</option>';
		} else {
			echo '<option value="', $MyRow['groupcode'], '">', $MyRow['groupcode'], ' - ', $MyRow['groupname'] . '</option>';
		}
	} //$MyRow = DB_fetch_array($Result)
	echo '</select>
			<input type="submit" name="UpdateCodes" value="Select" /></td>
			</tr>';
}

if (isset($_POST['GLGroup']) and $_POST['GLGroup'] != '') {
	$SQL = "SELECT accountcode,
					accountname
			FROM chartmaster
			WHERE groupcode='" . $_POST['GLGroup'] . "'
				AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
			ORDER BY accountcode";
} else {
	$SQL = "SELECT accountcode,
					accountname
			FROM chartmaster
			WHERE chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
			ORDER BY accountcode";
}

echo '<tr>
		<td>' . _('Select GL Account') . ':</td>
		<td><select name="GLCode" onchange="return assignComboToInput(this,' . 'GLManualCode' . ')">';

$Result = DB_query($SQL);
if (DB_num_rows($Result) == 0) {
	echo '</select></td></tr>';
	prnMsg(_('No General ledger accounts have been set up yet') . ' - ' . _('payments cannot be analysed against GL accounts until the GL accounts are set up'), 'error');
} else {
	echo '<option value=""></option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['GLCode']) and $_POST['GLCode'] == $MyRow['accountcode']) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] . '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
		} else {
			echo '<option value="' . $MyRow['accountcode'] . '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
		}
	} //$MyRow = DB_fetch_array($Result)
	echo '</select></td></tr>';
}

if (isset($_POST['GLNarrative'])) { // General Ledger Payment (Different than Bank Account) info to be inserted on gltrans.narrative, varchar(200).
	echo '<tr>
			<td>' . _('GL Narrative') . ':</td>
			<td><input type="text" name="GLNarrative" maxlength="50" size="52" value="' . stripslashes($_POST['GLNarrative']) . '" /></td>
		</tr>';
} else {
	echo '<tr>
			<td>' . _('GL Narrative') . ':</td>
			<td><input type="text" name="GLNarrative" maxlength="50" size="52" /></td>
		</tr>';
}

if (isset($_POST['GLAmount'])) {
	echo '<tr>
			<td>' . _('Amount') . '</td>
			<td><input type="text" name="GLAmount" maxlength="12" size="12" class="number" value="' . $_POST['GLAmount'] . '" /></td>
		</tr>';
} else {
	echo '<tr><td>' . _('Amount') . '</td>
			<td><input type="text" name="GLAmount" maxlength="12" size="12" class="number" /></td>
		</tr>';
}

echo '</table>';

if (!isset($_GET['Edit'])) {
	echo '<div class="centre">
			<input type="submit" name="Add" value="' . _('Add New Regular Payment') . '" />
		</div>';
} else {
	echo '<div class="centre">
			<input type="submit" name="Update" value="' . _('Update Regular Payment Details') . '" />
		</div>
		<input type="hidden" name="ID" value="' . $_GET['Payment'] . '" />';
}
echo '</form>';

$SQL = "SELECT regularpayments.id,
				regularpayments.frequency,
				regularpayments.days,
				regularpayments.glcode,
				chartmaster.accountname,
				bankaccounts.bankaccountname,
				regularpayments.tag,
				regularpayments.amount,
				regularpayments.currabrev,
				regularpayments.narrative,
				regularpayments.firstpayment,
				regularpayments.finalpayment,
				regularpayments.nextpayment
			FROM regularpayments
			INNER JOIN bankaccounts
				ON bankaccounts.accountcode=regularpayments.bankaccountcode
			INNER JOIN chartmaster
				ON chartmaster.accountcode=regularpayments.glcode
			WHERE completed=0
				AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'";
$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0 and !isset($_GET['Edit'])) {
	echo '<table>
			<tr>
				<th>' . _('Frequency') . '</th>
				<th>' . _('Days into Period') . '</th>
				<th>' . _('Bank Account') . '</th>
				<th>' . _('GL Account') . '</th>
				<th>' . _('GL Tag') . '</th>
				<th>' . _('Amount of Payment') . '</th>
				<th>' . _('Currency of payment') . '</th>
				<th>' . _('Description') . '</th>
				<th>' . _('First payment Date') . '</th>
				<th>' . _('Next payment Date') . '</th>
				<th>' . _('Last payment Date') . '</th>
			</tr>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr>
				<td>' . $Frequencies[$MyRow['frequency']] . '</td>
				<td class="number">' . $MyRow['days'] . '</td>
				<td>' . $MyRow['bankaccountname'] . '</td>
				<td>' . $MyRow['glcode'] . ' - ' . $MyRow['accountname'] . '</td>
				<td>' . $MyRow['tag'] . '</td>
				<td class="number">' . $MyRow['amount'] . '</td>
				<td>' . $MyRow['currabrev'] . '</td>
				<td>' . $MyRow['narrative'] . '</td>
				<td>' . ConvertSQLDate($MyRow['firstpayment']) . '</td>
				<td>' . ConvertSQLDate($MyRow['nextpayment']) . '</td>
				<td>' . ConvertSQLDate($MyRow['finalpayment']) . '</td>
				<td><a href="' . htmlspecialchars(basename(__FILE__) . '?Payment=' . $MyRow['id']) . '&Edit=True">' . _('Edit') . '</a></td>
				<td><a href="' . htmlspecialchars(basename(__FILE__) . '?Payment=' . $MyRow['id']) . '&Complete=True">' . _('Complete') . '</a></td>
			</tr>';
	}
	echo '</table>';

}

include ('includes/footer.php');

?>