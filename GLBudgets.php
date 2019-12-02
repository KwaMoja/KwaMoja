<?php
include ('includes/session.php');
include ('includes/SQL_CommonFunctions.php');

$Title = _('Create GL Budgets');

$ViewTopic = 'GeneralLedger';
$BookMark = 'GLBudgets';
include ('includes/header.php');

if (isset($_POST['SelectedAccount'])) {
	$SelectedAccount = $_POST['SelectedAccount'];
} elseif (isset($_GET['SelectedAccount'])) {
	$SelectedAccount = $_GET['SelectedAccount'];
}

if (isset($_POST['Previous'])) {
	$SelectedAccount = $_POST['PrevAccount'];
} elseif (isset($_POST['Next'])) {
	$SelectedAccount = $_POST['NextAccount'];
}

if (isset($_POST['update'])) {
	prnMsg(_('Budget updated successfully'), 'success');
}

//If an account has not been selected then select one here.
echo '<p class="page_title_text" >
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', _('Budgets'), '" alt="', _('Budgets'), '" />', ' ', $Title, '
	</p>';

echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post" id="selectaccount">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

echo '<fieldset>
		<legend>', _('General ledger account selection'), '</legend>';

$SQL = "SELECT accountcode,
				accountname
			FROM chartmaster
			INNER JOIN accountgroups
				ON accountgroups.groupcode=chartmaster.groupcode
				AND accountgroups.language=chartmaster.language
			WHERE pandl=1
				AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
			ORDER BY accountcode";
$Result = DB_query($SQL);
echo '<field>
		<label for="SelectedAccount">', _('Select GL Account'), ':</label>
		<select required="required" name="SelectedAccount" onchange="ReloadForm(selectaccount.Select)">';
if (DB_num_rows($Result) == 0) {
	echo '</select>
		</field>';
	prnMsg(_('No General ledger accounts have been set up yet') . ' - ' . _('budgets cannot be allocated until the GL accounts are set up'), 'warn');
} else {
	while ($MyRow = DB_fetch_array($Result)) {
		$Account = $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false);
		if (isset($SelectedAccount) and isset($LastCode) and $SelectedAccount == $MyRow['accountcode']) {
			echo '<option selected="selected" value="', $MyRow['accountcode'], '">', $Account, '</option>';
			$PrevCode = $LastCode;
		} else {
			echo '<option value="', $MyRow['accountcode'], '">', $Account, '</option>';
			if (isset($SelectedAccount) and isset($LastCode) and $SelectedAccount == $LastCode) {
				$NextCode = $MyRow['accountcode'];
			}
		}
		$LastCode = $MyRow['accountcode'];
	}
	echo '</select>
		</field>';
}

if (!isset($PrevCode)) {
	$PrevCode = '';
}
if (!isset($NextCode)) {
	$NextCode = '';
}

echo '</fieldset>';
echo '<input type="hidden" name="PrevAccount" value="', $PrevCode, '" />';
echo '<input type="hidden" name="NextAccount" value="', $NextCode, '" />';

echo '<div class="centre">
		<input type="submit" name="Previous" value="', _('Prev Account'), '" />
		<input type="submit" name="Select" value="', _('Select Account'), '" />
		<input type="submit" name="Next" value="', _('Next Account'), '" />
	</div>
</form>';

// End of account selection
if (isset($SelectedAccount) and $SelectedAccount != '') {

	$CurrentYearEndPeriod = GetPeriod(Date($_SESSION['DefaultDateFormat'], YearEndDate($_SESSION['YearEnd'], 0)));

	// If the update button has been hit, then update chartdetails with the budget figures
	// for this year and next.
	if (isset($_POST['update'])) {
		DB_Txn_Begin();
		$ErrMsg = _('Cannot update GL budgets');
		$DbgMsg = _('The SQL that failed to update the GL budgets was');
		$LastYearBudgetCumulative = 0;
		$ThisYearBudgetCumulative = 0;
		$NextYearBudgetCumulative = 0;
		for ($i = 1;$i <= 12;$i++) {
			$LastYearBudget = round(filter_number_format($_POST[$i . 'last']), $_SESSION['CompanyRecord']['decimalplaces']);
			$LastYearBudgetCumulative+= $LastYearBudget;
			$SQL = "UPDATE chartdetails SET budget='" . $LastYearBudget . "',
											bfwdbudget='" . $LastYearBudgetCumulative . "'
					WHERE period='" . ($CurrentYearEndPeriod - (24 - $i)) . "'
					AND  accountcode = '" . $SelectedAccount . "'";
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
			$ThisYearBudget = round(filter_number_format($_POST[$i . 'this']), $_SESSION['CompanyRecord']['decimalplaces']);
			$ThisYearBudgetCumulative+= $ThisYearBudget;
			$SQL = "UPDATE chartdetails SET budget='" . $ThisYearBudget . "',
											bfwdbudget='" . $ThisYearBudgetCumulative . "'
					WHERE period='" . ($CurrentYearEndPeriod - (12 - $i)) . "'
					AND  accountcode = '" . $SelectedAccount . "'";
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
			$NextYearBudget = round(filter_number_format($_POST[$i . 'next']), $_SESSION['CompanyRecord']['decimalplaces']);
			$NextYearBudgetCumulative+= $NextYearBudget;
			$SQL = "UPDATE chartdetails SET budget='" . $NextYearBudget . "',
											bfwdbudget='" . $NextYearBudgetCumulative . "'
					WHERE period='" . ($CurrentYearEndPeriod + $i) . "'
					AND  accountcode = '" . $SelectedAccount . "'";
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
		}
		DB_Txn_Commit();
	}
	// End of update
	$YearEndYear = Date('Y', YearEndDate($_SESSION['YearEnd'], 0));

	/* If the periods dont exist then create them */
	for ($i = 1;$i <= 36;$i++) {
		$MonthEnd = mktime(0, 0, 0, $_SESSION['YearEnd'] + 1 + $i, 0, $YearEndYear - 2);
		$Period = GetPeriod(Date($_SESSION['DefaultDateFormat'], $MonthEnd), false);
		$PeriodEnd[$Period] = Date('M Y', $MonthEnd);
	}
	include ('includes/GLPostings.php'); //creates chartdetails with correct values
	// End of create periods
	$SQL = "SELECT period,
					budget,
					actual
				FROM chartdetails
				WHERE accountcode='" . $SelectedAccount . "'";

	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		$Budget[$MyRow['period']] = $MyRow['budget'];
		$Actual[$MyRow['period']] = $MyRow['actual'];
	}

	if (isset($_POST['Apportion'])) {
		for ($i = 1;$i <= 12;$i++) {
			if (filter_number_format($_POST['AnnualAmountLY']) != '0' and is_numeric(filter_number_format($_POST['AnnualAmountLY']))) {
				$Budget[$CurrentYearEndPeriod + $i - 24] = round(filter_number_format($_POST['AnnualAmountLY']) / 12, 0);
			}
			if (filter_number_format($_POST['AnnualAmountTY']) != '0' and is_numeric(filter_number_format($_POST['AnnualAmountTY']))) {
				$Budget[$CurrentYearEndPeriod + $i - 12] = round(filter_number_format($_POST['AnnualAmountTY']) / 12, 0);
			}
			if (filter_number_format($_POST['AnnualAmount']) != '0' and is_numeric(filter_number_format($_POST['AnnualAmount']))) {
				$Budget[$CurrentYearEndPeriod + $i] = round(filter_number_format($_POST['AnnualAmount']) / 12, 0);
			}
		}
	}

	$LastYearActual = 0;
	$LastYearBudget = 0;
	$ThisYearActual = 0;
	$ThisYearBudget = 0;
	$NextYearActual = 0;
	$NextYearBudget = 0;

	// Table Headers
	echo '<form id="form" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<table width="90%" summary="', _('Budget Entry'), '">
			<tr>
				<th colspan="3">', _('Last Financial Year'), '</th>
				<th colspan="3">', _('This Financial Year'), '</th>
				<th colspan="3">', _('Next Financial Year'), '</th>
			</tr>
			<tr>
				<th colspan="3">', _('Year ended'), ' - ', Date($_SESSION['DefaultDateFormat'], YearEndDate($_SESSION['YearEnd'], -1)), '</th>
				<th colspan="3">', _('Year ended'), ' - ', Date($_SESSION['DefaultDateFormat'], YearEndDate($_SESSION['YearEnd'], 0)), '</th>
				<th colspan="3">', _('Year ended'), ' - ', Date($_SESSION['DefaultDateFormat'], YearEndDate($_SESSION['YearEnd'], 1)), '</th>
			</tr>
			<tr>';
	for ($i = 0;$i < 3;$i++) {
		echo '<th width="10%">', _('Period'), '</th>
				<th width="10%">', _('Actual'), '</th>
				<th width="10%">', _('Budget'), '</th>';
	}
	echo '</tr>';

	// Main Table
	for ($i = 1;$i <= 12;$i++) {
		echo '<tr class="striped_row">
				<th>', $PeriodEnd[$CurrentYearEndPeriod - (24 - $i) ], '</th>
				<td class="number">', locale_number_format($Actual[$CurrentYearEndPeriod - (24 - $i) ], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td><input type="text" readonly="true" class="number" size="12" name="', $i, 'last" value="', locale_number_format($Budget[$CurrentYearEndPeriod - (24 - $i) ], $_SESSION['CompanyRecord']['decimalplaces']), '" /></td>
				<th>', $PeriodEnd[$CurrentYearEndPeriod - (12 - $i) ], '</th>
				<td class="number">', locale_number_format($Actual[$CurrentYearEndPeriod - (12 - $i) ], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td><input type="text" class="number" required="required" maxlength="12" size="12" name="', $i, 'this" value="', locale_number_format($Budget[$CurrentYearEndPeriod - (12 - $i) ], $_SESSION['CompanyRecord']['decimalplaces']), '" /></td>
				<th>', $PeriodEnd[$CurrentYearEndPeriod + ($i) ], '</th>
				<td class="number">', locale_number_format($Actual[$CurrentYearEndPeriod + $i], $_SESSION['CompanyRecord']['decimalplaces']), '</td>';
		if ($i == 1) {
			echo '<td><input type="text" class="number" autofocus="autofocus" required="required" maxlength="12" size="12" name="', $i, 'next" value="', locale_number_format($Budget[$CurrentYearEndPeriod + $i], $_SESSION['CompanyRecord']['decimalplaces']), '" /></td>';
		} else {
			echo '<td><input type="text" class="number" required="required" maxlength="12" size="12" name="', $i, 'next" value="', locale_number_format($Budget[$CurrentYearEndPeriod + $i], $_SESSION['CompanyRecord']['decimalplaces']), '" /></td>';
		}
		echo '</tr>';
		$LastYearActual = $LastYearActual + $Actual[$CurrentYearEndPeriod - (24 - $i) ];
		$LastYearBudget = $LastYearBudget + $Budget[$CurrentYearEndPeriod - (24 - $i) ];
		$ThisYearActual = $ThisYearActual + $Actual[$CurrentYearEndPeriod - (12 - $i) ];
		$ThisYearBudget = $ThisYearBudget + $Budget[$CurrentYearEndPeriod - (12 - $i) ];
		$NextYearActual = $NextYearActual + $Actual[$CurrentYearEndPeriod + ($i) ];
		$NextYearBudget = $NextYearBudget + $Budget[$CurrentYearEndPeriod + ($i) ];
	}

	// Total Line
	echo '<tr class="total_row">
			<th>', _('Total'), '</th>
			<td class="number">', locale_number_format($LastYearActual, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
			<td class="number">', locale_number_format($LastYearBudget, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
			<th>', _('Total'), '</th>
			<td class="number">', locale_number_format($ThisYearActual, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
			<td class="number">', locale_number_format($ThisYearBudget, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
			<th>', _('Total'), '</th>
			<td class="number">', locale_number_format($NextYearActual, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
			<td class="number">', locale_number_format($NextYearBudget, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
		</tr>
		<tr class="total_row">
			<td colspan="2">', _('Annual Budget'), '</td>
			<td><input class="number" readonly="true" type="text" size="12" name="AnnualAmountLY" value="0.00" /></td>
			<td colspan="2">', _('Annual Budget'), '</td>
			<td><input class="number" type="text" size="12" name="AnnualAmountTY" value="0.00" /></td>
			<td>', _('Annual Budget'), '</td>
			<td><input type="submit" name="Apportion" value="', _('Apportion Budget'), '" /></td>
			<td><input onchange="numberFormat(this,', $_SESSION['CompanyRecord']['decimalplaces'], ')" class="number" type="text" size="14" name="AnnualAmount" value="0.00" /></td>
		</tr>
	</table>';

	echo '<input type="hidden" name="SelectedAccount" value="', $SelectedAccount, '" />';

	echo '<div class="centre">
			<input type="submit" name="update" value="', _('Update'), '" />
		</div>
	</form>';
}

include ('includes/footer.php');

?>