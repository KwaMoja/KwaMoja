<?php

include('includes/session.inc');
$Title = _('Currencies Maintenance');
$ViewTopic = 'Currencies';
$BookMark = 'Currencies';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/CurrenciesArray.php');

if (isset($_GET['SelectedCurrency'])) {
	$SelectedCurrency = $_GET['SelectedCurrency'];
} elseif (isset($_POST['SelectedCurrency'])) {
	$SelectedCurrency = $_POST['SelectedCurrency'];
}

$ForceConfigReload = true;
include('includes/GetConfig.php');

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/money_add.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';

$SQL = "SELECT count(currabrev)
		FROM currencies";
$Result = DB_query($SQL);
$MyRow = DB_fetch_row($Result);

if (isset($_SESSION['CompanyRecord']['currencydefault'])) {
	$FunctionalCurrency = $_SESSION['CompanyRecord']['currencydefault'];
}

if ($MyRow[0] == 0) {
	echo '<div class="page_help_text">' . _('As this is the first time that the system has been used, you must first set up your main accounting currency.') . '</div>';
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs are sensible
	$i = 1;

	$SQL = "SELECT count(currabrev)
			FROM currencies
			WHERE currabrev='" . $_POST['Abbreviation'] . "'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);

	if ($MyRow[0] != 0 and !isset($SelectedCurrency)) {
		$InputError = 1;
		prnMsg(_('The currency already exists in the database'), 'error');
		$Errors[$i] = 'Abbreviation';
		++$i;
	}
	if (!is_numeric(filter_number_format($_POST['ExchangeRate']))) {
		$InputError = 1;
		prnMsg(_('The exchange rate must be numeric'), 'error');
		$Errors[$i] = 'ExchangeRate';
		++$i;
	}
	if (!is_numeric(filter_number_format($_POST['DecimalPlaces']))) {
		$InputError = 1;
		prnMsg(_('The number of decimal places to display for amounts in this currency must be numeric'), 'error');
		$Errors[$i] = 'DecimalPlaces';
		++$i;
	} elseif (filter_number_format($_POST['DecimalPlaces']) < 0) {
		$InputError = 1;
		prnMsg(_('The number of decimal places to display for amounts in this currency must be positive or zero'), 'error');
		$Errors[$i] = 'DecimalPlaces';
		++$i;
	} elseif (filter_number_format($_POST['DecimalPlaces']) > 4) {
		$InputError = 1;
		prnMsg(_('The number of decimal places to display for amounts in this currency is expected to be 4 or less'), 'error');
		$Errors[$i] = 'DecimalPlaces';
		++$i;
	}
	if (mb_strlen($_POST['Country']) > 50) {
		$InputError = 1;
		prnMsg(_('The currency country must be 50 characters or less long'), 'error');
		$Errors[$i] = 'Country';
		++$i;
	}
	if (mb_strlen($_POST['HundredsName']) > 15) {
		$InputError = 1;
		prnMsg(_('The hundredths name must be 15 characters or less long'), 'error');
		$Errors[$i] = 'HundredsName';
		++$i;
	}
	if (($FunctionalCurrency != '') and (isset($SelectedCurrency) and $SelectedCurrency == $FunctionalCurrency)) {
		$_POST['ExchangeRate'] = 1;
	}

	if (isset($SelectedCurrency) and $InputError != 1) {
		/*Get the previous exchange rate. We will need it later to adjust bank account balances */
		$SQLOldRate = "SELECT rate
						FROM currencies
						WHERE currabrev = '" . $SelectedCurrency . "'";
		$ResultOldRate = DB_query($SQLOldRate);
		$MyRow = DB_fetch_row($ResultOldRate);
		$OldRate = $MyRow[0];

		/*SelectedCurrency could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		$SQL = "UPDATE currencies SET country='" . $_POST['Country'] . "',
										currency='" . $CurrencyName[$_POST['Abbreviation']] . "',
										hundredsname='" . $_POST['HundredsName'] . "',
										decimalplaces='" . filter_number_format($_POST['DecimalPlaces']) . "',
										rate='" . filter_number_format($_POST['ExchangeRate']) . "',
										webcart='" . $_POST['webcart'] . "'
									WHERE currabrev = '" . $SelectedCurrency . "'";
		$Msg = _('The currency definition record has been updated');
		$NewRate = $_POST['ExchangeRate'];

	} else if ($InputError != 1) {

		/*Selected currencies is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new payment terms form */
		$SQL = "INSERT INTO currencies (currency,
										currabrev,
										country,
										hundredsname,
										decimalplaces,
										rate,
										webcart)
								VALUES ('" . $CurrencyName[$_POST['Abbreviation']] . "',
										'" . $_POST['Abbreviation'] . "',
										'" . $_POST['Country'] . "',
										'" . $_POST['HundredsName'] . "',
										'" . filter_number_format($_POST['DecimalPlaces']) . "',
										'" . filter_number_format($_POST['ExchangeRate']) . "',
										'" . $_POST['webcart'] . "')";

		$Msg = _('The currency definition record has been added');
	}
	//run the SQL from either of the above possibilites
	$ExDiffTransNo = GetNextTransNo(36);
	$ResultTx = DB_Txn_Begin();
	$Result = DB_query($SQL);
	if ($InputError != 1) {
		prnMsg($Msg, 'success');
	}

	/* Now we should update the functional currency value of the bank accounts of the $SelectedCurrency
	Example: if functional currency = IDR and we have a bank account in USD.
	Before rate was 1 USD = 9.000 IDR so OldRate = 1 /9.000 = 0.000111
	if the new exchange rate is 1 USD = 10.000 IDR NewRate will be 0.0001.
	If we had 5.000 USD on the bank account, we had 45.000.000 IDR on the balance sheet.
	After we update to the new rate, we still have 5.000 USD on the bank account
	but the balance value of the bank account is 50.000.000 IDR, so let's adjust the value */

	if (isset($SelectedCurrency) and $InputError != 1) {
		/*Get the current period */
		$PostingDate = Date($_SESSION['DefaultDateFormat']);
		$PeriodNo = GetPeriod($PostingDate);

		/* get all the bank accounts denominated on the selected currency */
		$SQLBankAccounts = "SELECT 	bankaccountname,
									accountcode
							FROM bankaccounts
							WHERE currcode = '" . $SelectedCurrency . "'";
		$ResultBankAccounts = DB_query($SQLBankAccounts);
		while ($MyRowBankAccount = DB_fetch_array($ResultBankAccounts)) {

			/*Get the balance of the bank account concerned */
			$SQL = "SELECT bfwd+actual AS balance
					FROM chartdetails
					WHERE period='" . $PeriodNo . "'
					AND accountcode='" . $MyRowBankAccount['accountcode'] . "'";

			$ErrMsg = _('The bank account balance could not be returned by the SQL because');
			$BalanceResult = DB_query($SQL, $ErrMsg);
			$MyRow = DB_fetch_row($BalanceResult);
			$OldBalanceInFunctionalCurrency = $MyRow[0];
			$BalanceInAccountCurrency = $OldBalanceInFunctionalCurrency * $OldRate;

			/* Now calculate the Balance in functional currency at the new rate */
			$NewBalanceInFucntionalCurrency = $BalanceInAccountCurrency / $NewRate;

			/* If some adjustment has to be done, do it! */
			$DifferenceToAdjust = $NewBalanceInFucntionalCurrency - $OldBalanceInFunctionalCurrency;
			if ($OldRate != $NewRate) {

				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
										  VALUES (36,
											'" . $ExDiffTransNo . "',
											'" . FormatDateForSQL($PostingDate) . "',
											'" . $PeriodNo . "',
											'" . $_SESSION['CompanyRecord']['exchangediffact'] . "',
											'" . $MyRowBankAccount['bankaccountname'] . ' ' . _('currency rate adjustment to') . ' ' . locale_number_format($NewRate, 8) . ' ' . $SelectedCurrency . '/' . $_SESSION['CompanyRecord']['currencydefault'] . "',
											'" . (-$DifferenceToAdjust) . "')";

				$ErrMsg = _('Cannot insert a GL entry for the exchange difference because');
				$DbgMsg = _('The SQL that failed to insert the exchange difference GL entry was');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
										  VALUES (36,
											'" . $ExDiffTransNo . "',
											'" . FormatDateForSQL($PostingDate) . "',
											'" . $PeriodNo . "',
											'" . $MyRowBankAccount['accountcode'] . "',
											'" . $MyRowBankAccount['bankaccountname'] . ' ' . _('currency rate adjustment to') . ' ' . locale_number_format($NewRate, 8) . ' ' . $SelectedCurrency . '/' . $_SESSION['CompanyRecord']['currencydefault'] . "',
											'" . ($DifferenceToAdjust) . "')";

				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				prnMsg(_('Bank Account') . ' ' . $MyRowBankAccount['bankaccountname'] . ' ' . _('Currency Rate difference of') . ' ' . locale_number_format($DifferenceToAdjust, $_SESSION['CompanyRecord']['decimalplaces']) . ' ' . _('has been posted'), 'success');
			}
		}
	}
	$ResultTx = DB_Txn_Commit();
	unset($SelectedCurrency);
	unset($_POST['Country']);
	unset($_POST['HundredsName']);
	unset($_POST['DecimalPlaces']);
	unset($_POST['ExchangeRate']);
	unset($_POST['Abbreviation']);
	unset($_POST['webcart']);

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button

	// PREVENT DELETES IF DEPENDENT RECORDS IN DebtorsMaster

	$SQL = "SELECT COUNT(*) FROM debtorsmaster
			WHERE currcode = '" . $SelectedCurrency . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		prnMsg(_('Cannot delete this currency because customer accounts have been created referring to this currency') . '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('customer accounts that refer to this currency'), 'warn');
	} else {
		$SQL = "SELECT COUNT(*) FROM suppliers
				WHERE suppliers.currcode = '" . $SelectedCurrency . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0] > 0) {
			prnMsg(_('Cannot delete this currency because supplier accounts have been created referring to this currency') . '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('supplier accounts that refer to this currency'), 'warn');
		} else {
			$SQL = "SELECT COUNT(*) FROM banktrans
					WHERE currcode = '" . $SelectedCurrency . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_row($Result);
			if ($MyRow[0] > 0) {
				prnMsg(_('Cannot delete this currency because there are bank transactions that use this currency') . '<br />' . ' ' . _('There are') . ' ' . $MyRow[0] . ' ' . _('bank transactions that refer to this currency'), 'warn');
			} elseif ($FunctionalCurrency == $SelectedCurrency) {
				prnMsg(_('Cannot delete this currency because it is the functional currency of the company'), 'warn');
			} else {
				$SQL = "SELECT COUNT(*) FROM bankaccounts
						WHERE currcode = '" . $SelectedCurrency . "'";
				$Result = DB_query($SQL);
				$MyRow = DB_fetch_row($Result);
				if ($MyRow[0] > 0){
					prnMsg(_('Cannot delete this currency because there are bank accounts that use this currency') .
					'<br />' . ' ' . _('There are') . ' ' . $MyRow[0] . ' ' . _('bank accounts that refer to this currency'),'warn');
				} else {
					//only delete if used in neither customer or supplier, comp prefs, bank trans accounts
					$SQL = "DELETE FROM currencies WHERE currabrev='" . $SelectedCurrency . "'";
					$Result = DB_query($SQL);
					prnMsg(_('The currency definition record has been deleted'),'success');
				}
			}
		}
	}
	//end if currency used in customer or supplier accounts
}

if (!isset($SelectedCurrency)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedCurrency will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of payment termss will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/

	$SQL = "SELECT currency,
					currabrev,
					country,
					hundredsname,
					rate,
					decimalplaces,
					webcart
				FROM currencies";
	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<tr>
			<td></td>
			<th>' . _('ISO4217 Code') . '</th>
			<th>' . _('Currency Name') . '</th>
			<th>' . _('Country') . '</th>
			<th>' . _('Hundredths Name') . '</th>
			<th>' . _('Decimal Places') . '</th>
			<th>' . _('Show in webSHOP') . '</th>
			<th>' . _('Exchange Rate') . '</th>
			<th>' . _('1 / Ex Rate') . '</th>
			<th>' . _('Ex Rate - ECB') . '</th>
		</tr>';

	$k = 0; //row colour counter
	/*Get published currency rates from Eurpoean Central Bank */
	if ($_SESSION['UpdateCurrencyRatesDaily'] != '0') {
		$CurrencyRatesArray = GetECBCurrencyRates();
	} else {
		$CurrencyRatesArray = array();
	}

	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['currabrev'] == $FunctionalCurrency) {
			echo '<tr style="background-color:#FFbbbb">';
		} elseif ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			++$k;
		}
		// Lets show the country flag
		$ImageFile = 'flags/' . mb_strtoupper($MyRow['currabrev']) . '.gif';

		if (!file_exists($ImageFile)) {
			$ImageFile = 'flags/blank.gif';
		}
		if ($MyRow['webcart'] == 1) {
			$ShowInWebText = _('Yes');
		} else {
			$ShowInWebText = _('No');
		}

		if ($MyRow['currabrev'] != $FunctionalCurrency) {
			echo '<td><img src="' . $ImageFile . '" alt="" /></td>
					<td>' . $MyRow['currabrev'] . '</td>
					<td>' . _($MyRow['currency']) . '</td>
					<td>' . $MyRow['country'] . '</td>
					<td>' . $MyRow['hundredsname'] . '</td>
					<td class="number">' . locale_number_format($MyRow['decimalplaces'], 0) . '</td>
					<td>' . $ShowInWebText . '</td>
					<td class="number">' . locale_number_format($MyRow['rate'], 8) . '</td>
					<td class="number">' . locale_number_format(1 / $MyRow['rate'], 8) . '</td>
					<td class="number">' . locale_number_format(GetCurrencyRate($MyRow['currabrev'], $CurrencyRatesArray), 8) . '</td>
					<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?&amp;SelectedCurrency=' . urlencode($MyRow['currabrev']) . '">' . _('Edit') . '</a></td>
					<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?&amp;SelectedCurrency=' . urlencode($MyRow['currabrev']) . '&amp;delete=1" onclick="return MakeConfirm(\'' . _('Are you sure you wish to delete this currency?') . '\', \'Confirm Delete\', this);">' . _('Delete') . '</a></td>
					<td><a href="' . $RootPath . '/ExchangeRateTrend.php?CurrencyToShow=' . urlencode($MyRow['currabrev']) . '">' . _('Graph') . '</a></td>
				</tr>';
		} else {
			echo '<td><img src="' . $ImageFile . '" alt="" /></td>
					<td>' . $MyRow['currabrev'] . '</td>
					<td>' . $MyRow['currency'] . '</td>
					<td>' . $MyRow['country'] . '</td>
					<td>' . $MyRow['hundredsname'] . '</td>
					<td class="number">' . locale_number_format($MyRow['decimalplaces'], 0) . '</td>
					<td>' . $ShowInWebText . '</td>
					<td class="number">1</td>
					<td colspan="2">' . _('Functional Currency') . '</td>
					<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?&amp;SelectedCurrency=' . urlencode($MyRow['currabrev']) . '">' . _('Edit') . '</a></td>
					<td colspan="2"></td>
					</tr>';
		}

	} //END WHILE LIST LOOP
	echo '</table>';
} //end of ifs and buts!


if (isset($SelectedCurrency)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Show all currency definitions') . '</a></div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedCurrency) and $SelectedCurrency != '') {
		//editing an existing currency

		$SQL = "SELECT currency,
					currabrev,
					country,
					hundredsname,
					decimalplaces,
					rate,
					webcart
				FROM currencies
				WHERE currabrev='" . $SelectedCurrency . "'";

		$ErrMsg = _('An error occurred in retrieving the currency information');
		$Result = DB_query($SQL, $ErrMsg);

		$MyRow = DB_fetch_array($Result);

		$_POST['Abbreviation'] = $MyRow['currabrev'];
		$_POST['CurrencyName'] = $MyRow['currency'];
		$_POST['Country'] = $MyRow['country'];
		$_POST['HundredsName'] = $MyRow['hundredsname'];
		$_POST['ExchangeRate'] = locale_number_format($MyRow['rate'], 8);
		$_POST['DecimalPlaces'] = locale_number_format($MyRow['decimalplaces'], 0);
		$_POST['webcart'] = $MyRow['webcart'];


		echo '<input type="hidden" name="SelectedCurrency" value="' . $SelectedCurrency . '" />';
		echo '<input type="hidden" name="Abbreviation" value="' . $_POST['Abbreviation'] . '" />';
		echo '<table class="selection">
			<tr>
				<td>' . _('ISO 4217 Currency Code') . ':</td>
				<td>' . $_POST['Abbreviation'] . '</td>
			</tr>';

	} else { //end of if $SelectedCurrency only do the else when a new record is being entered
		if (!isset($_POST['Abbreviation'])) {
			$_POST['Abbreviation'] = '';
		}
		echo '<table class="selection">
			<tr>
				<td>' . _('Currency') . ':</td>
				<td><select name="Abbreviation">';
		foreach ($CurrencyName as $CurrencyAbbreviation => $Currency) {
			echo '<option value="' . $CurrencyAbbreviation . '">' . $CurrencyAbbreviation . '-' . $Currency . '</option>';
		}

		echo '</select></td>
			</tr>';
	}

	echo '<tr>
			<td>' . _('Country') . ':</td>';
	if (!isset($_POST['Country'])) {
		$_POST['Country'] = '';
	}
	if ($_POST['Abbreviation'] != $FunctionalCurrency) {
		echo '<td><input type="text" name="Country" size="30" required="required" maxlength="50" value="' . $_POST['Country'] . '" /></td>';
	} else {
		echo '<td>' . $_POST['Country'] . '</td>';
		echo '<input type="hidden" name="Country" value="' . $_POST['Country'] . '" />';
	}
	echo '</tr>
		<tr>
			<td>' . _('Hundredths Name') . ':</td>
			<td>';
	if (!isset($_POST['HundredsName'])) {
		$_POST['HundredsName'] = '';
	}
	echo '<input type="text" name="HundredsName" size="10" required="required" maxlength="15" value="' . $_POST['HundredsName'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Decimal Places to Display') . ':</td>
			<td>';
	if (!isset($_POST['DecimalPlaces'])) {
		$_POST['DecimalPlaces'] = 2;
	}
	echo '<input class="integer" type="text" name="DecimalPlaces" size="2" required="required" maxlength="2" value="' . $_POST['DecimalPlaces'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Exchange Rate') . ':</td>';
	if (!isset($_POST['ExchangeRate'])) {
		$_POST['ExchangeRate'] = 1;
	}
	if ($_POST['Abbreviation'] != $FunctionalCurrency) {
		echo '<td><input type="text" class="number" name="ExchangeRate" size="10" required="required" maxlength="10" value="' . $_POST['ExchangeRate'] . '" /></td>';
	} else {
		echo '<td>' . $_POST['ExchangeRate'] . '</td>';
		echo '<input type="hidden" class="number" name="ExchangeRate" value="' . $_POST['ExchangeRate'] . '" />';
	}
	echo '</tr>';
	if (!isset($_POST['webcart'])) {
		$_POST['webcart'] = 1;
	}

	echo '<tr>
			<td>' . _('Show in webSHOP') . ':</td>
			<td><select name="webcart">';

	if ($_POST['webcart'] == 1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if ($_POST['webcart'] == 0) {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}

	echo '</select>
				</td>
			</tr>
		</table>';

	echo '<div class="centre">
			<input type="submit" name="submit" value="' . _('Enter Information') . '" />
		</div>
	</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>