<?php
/* Shows the general ledger transactions for a specified account over a specified range of periods */
include ('includes/session.php');
$Title = _('General Ledger Account Inquiry');
$ViewTopic = 'GeneralLedger';
$BookMark = 'GLAccountInquiry';
include ('includes/header.php');
include ('includes/GLPostings.php');

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/transactions.png" title="', _('General Ledger Account Inquiry'), '" alt="', _('General Ledger Account Inquiry'), '" />', ' ', _('General Ledger Account Inquiry'), '
	</p>';

if (isset($_POST['Select'])) {
	$_POST['Account'] = $_POST['Select'];
}
if (isset($_POST['Account'])) {
	$SelectedAccount = $_POST['Account'];
} elseif (isset($_GET['Account'])) {
	$SelectedAccount = $_GET['Account'];
}

if (isset($_POST['PeriodTo'])) {
	$SelectedPeriodTo = $_POST['PeriodTo'];
} elseif (isset($_GET['PeriodTo'])) {
	$SelectedPeriodTo = $_GET['PeriodTo'];
}

if (isset($_POST['PeriodFrom'])) {
	$SelectedPeriodFrom = $_POST['PeriodFrom'];
} elseif (isset($_GET['PeriodFrom'])) {
	$SelectedPeriodFrom = $_GET['PeriodFrom'];
}

if (isset($_POST['Period'])) {
	$SelectedPeriod = $_POST['Period'];
}

if (isset($_GET['Show'])) {
	$_POST['Show'] = $_GET['Show'];
}

if (!isset($_POST['tag'])) {
	$_POST['tag'] = 0;
}

if (isset($SelectedAccount) and $_SESSION['CompanyRecord']['retainedearnings'] == $SelectedAccount) {
	prnMsg(_('The retained earnings account is managed separately by the system, and therefore cannot be inquired upon. See manual for details'), 'info');
	echo '<a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">', _('Select another account'), '</a>';
	include ('includes/footer.php');
	exit;
}

echo '<div class="page_help_text noPrint">', _('Use the keyboard Shift key to select multiple periods'), '</div>';

echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

/* Get the start and periods, depending on how this script was called*/
if (isset($SelectedPeriod)) { //If it was called from itself (in other words an inquiry was run and we wish to leave the periods selected unchanged
	$FirstPeriodSelected = min($SelectedPeriod);
	$LastPeriodSelected = max($SelectedPeriod);
} elseif (isset($_GET['PeriodFrom'])) { //If it was called from the Trial Balance/P&L or Balance sheet
	$FirstPeriodSelected = $_GET['PeriodFrom'];
	$LastPeriodSelected = $_GET['PeriodTo'];
	$SelectedPeriod[0] = $_GET['PeriodFrom'];
	$SelectedPeriod[1] = $_GET['PeriodTo'];
} else { // Otherwise just highlight the current period
	$FirstPeriodSelected = GetPeriod(date($_SESSION['DefaultDateFormat']));
	$LastPeriodSelected = GetPeriod(date($_SESSION['DefaultDateFormat']));
}

/*Dates in SQL format for the last day of last month*/
$DefaultPeriodDate = Date('Y-m-d', Mktime(0, 0, 0, Date('m'), 0, Date('Y')));

/*Show a form to allow input of criteria for TB to show */

echo '<fieldset>
		<legend>', _('Inquiry Selection Criteria'), '</legend>
		<field>
			<label for="Account">', _('Account'), ':</label>';
GLSelect(2, 'Account');
echo '<fieldhelp>', _('Select a General Ledger account to report on.'), '</fieldhelp>
	</field>';

//Select the tag
$SQL = "SELECT tagref,
			tagdescription
		FROM tags
		ORDER BY tagdescription";
$Result = DB_query($SQL);

echo '<field>
		<label for="tag">', _('Select Tag'), ':</label>
		<select name="tag">';
echo '<option value="0">0 - ', _('All tags'), '</option>';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['tag']) and $_POST['tag'] == $MyRow['tagref']) {
		echo '<option selected="selected" value="', $MyRow['tagref'], '">', $MyRow['tagref'], ' - ', $MyRow['tagdescription'], '</option>';
	} else {
		echo '<option value="', $MyRow['tagref'], '">', $MyRow['tagref'], ' - ', $MyRow['tagdescription'], '</option>';
	}
}
echo '</select>
	<fieldhelp>', _('Select a tag to filter the report on.'), '</fieldhelp>
</field>';

$SQL = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC";
$Periods = DB_query($SQL);
$id = 0;
echo '<field>
		<label for="Period">', _('For Period range'), ':</label>
		<select name="Period[]" size="12" multiple="multiple">';
while ($MyRow = DB_fetch_array($Periods)) {
	if (isset($FirstPeriodSelected) and $MyRow['periodno'] >= $FirstPeriodSelected and $MyRow['periodno'] <= $LastPeriodSelected) {
		echo '<option selected="selected" value="', $MyRow['periodno'], '">', _(MonthAndYearFromSQLDate($MyRow['lastdate_in_period'])), '</option>';
		$id++;
	} else {
		echo '<option value="', $MyRow['periodno'], '">', _(MonthAndYearFromSQLDate($MyRow['lastdate_in_period'])), '</option>';
	}
}
echo '</select>
	<fieldhelp>', _('Select one or more financial periods to report on.'), '</fieldhelp>
</field>';

echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="Show" value="', _('Show Account Transactions'), '" />
	</div>
</form>';

/* End of the Form  rest of script is what happens if the show button is hit*/

if (isset($_POST['Show'])) {

	if (!isset($SelectedPeriod)) {
		prnMsg(_('A period or range of periods must be selected from the list box'), 'info');
		include ('includes/footer.php');
		exit;
	}
	/*Is the account a balance sheet or a profit and loss account */
	$Result = DB_query("SELECT pandl
				FROM accountgroups
				INNER JOIN chartmaster
					ON accountgroups.groupcode=chartmaster.groupcode
					AND accountgroups.language=chartmaster.language
				WHERE chartmaster.accountcode='" . $SelectedAccount . "'
					AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'");
	$PandLRow = DB_fetch_row($Result);
	if ($PandLRow[0] == 1) {
		$PandLAccount = True;
	} else {
		$PandLAccount = False;
		/*its a balance sheet account */
	}

	$SQL = "SELECT  gltrans.counterindex,
					type,
					typename,
					gltrans.typeno,
					trandate,
					narrative,
					chequeno,
					amount,
					periodno,
					gltags.tagref AS tag,
					tagdescription
				FROM gltrans
				INNER JOIN systypes
					ON systypes.typeid=gltrans.type
				INNER JOIN gltags
					ON gltrans.counterindex=gltags.counterindex
				LEFT JOIN tags
					ON gltags.tagref = tags.tagref
				WHERE gltrans.account = '" . $SelectedAccount . "'
					AND posted=1
					AND periodno>='" . $FirstPeriodSelected . "'
					AND periodno<='" . $LastPeriodSelected . "'";

	if ($_POST['tag'] != 0) {
		$SQL = $SQL . " AND gltags.tagref='" . $_POST['tag'] . "'";
	}

	$SQL = $SQL . " ORDER BY periodno, gltrans.trandate, counterindex";

	$NameSQL = "SELECT accountname
					FROM chartmaster
					WHERE accountcode='" . $SelectedAccount . "'
						AND language='" . $_SESSION['ChartLanguage'] . "'";
	$NameResult = DB_query($NameSQL);
	$NameRow = DB_fetch_array($NameResult);
	$SelectedAccountName = $NameRow['accountname'];
	$ErrMsg = _('The transactions for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved because');
	$TransResult = DB_query($SQL, $ErrMsg);

	echo '<table summary="', _('General Ledger account inquiry details'), '">
			<thead>
				<tr>
					<th colspan="9">
						<b>', _('Transactions for account'), ' ', $SelectedAccount, ' - ', $SelectedAccountName, '</b>
						<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/printer.png" class="PrintIcon" title="', _('Print'), '" alt="', _('Print'), '" onclick="window.print();" />
					</th>
				</tr>
				<tr>
					<th>', _('Type'), '</th>
					<th>', _('Trans no'), '</th>
					<th>', _('Cheque'), '</th>
					<th>', _('Date'), '</th>
					<th>', _('Narrative'), '</th>
					<th>', _('Tag'), '</th>
					<th>', _('Debit'), '</th>
					<th>', _('Credit'), '</th>
					<th>', _('Balance'), '</th>
				</tr>
			</thead>';

	if ($PandLAccount == True) {
		$RunningTotal = 0;
	} else {
		// added to fix bug with Brought Forward Balance always being zero
		$SQL = "SELECT bfwd,
						actual,
						period
					FROM chartdetails
					WHERE chartdetails.accountcode='" . $SelectedAccount . "'
					AND chartdetails.period='" . $FirstPeriodSelected . "'";

		$ErrMsg = _('The chart details for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved');
		$ChartDetailsResult = DB_query($SQL, $ErrMsg);
		$ChartDetailRow = DB_fetch_array($ChartDetailsResult);

		if ($_POST['tag'] == 0) {
			$_POST['tag'] = '%%';
		}
		$BfwdSQL = "SELECT sum(amount) as bfwd
						FROM gltrans
						INNER JOIN gltags
							ON gltrans.counterindex=gltags.counterindex
						WHERE account='" . $SelectedAccount . "'
							AND periodno<" . $FirstPeriodSelected . "
							AND gltags.tagref like '" . $_POST['tag'] . "'";
		$BfwdResult = DB_query($BfwdSQL);
		$BfwdRow = DB_fetch_array($BfwdResult);

		$RunningTotal = $BfwdRow['bfwd'];
		if ($RunningTotal < 0) { //its a credit balance b/fwd
			echo '<tr>
					<td colspan="5"><b>', _('Brought Forward Balance'), '</b></td>
					<td class="number"><b>', locale_number_format(-$RunningTotal, $_SESSION['CompanyRecord']['decimalplaces']), '</b></td>
				</tr>';
		} else { //its a debit balance b/fwd
			echo '<tr>
					<td colspan="4"><b>', _('Brought Forward Balance'), '</b></td>
					<td class="number"><b>', locale_number_format($RunningTotal, $_SESSION['CompanyRecord']['decimalplaces']), '</b></td>
				</tr>';
		}
	}
	$PeriodTotal = 0;
	$PeriodNo = - 9999;
	$ShowIntegrityReport = False;
	$j = 1;

	$IntegrityReport = '';
	while ($MyRow = DB_fetch_array($TransResult)) {
		if ($MyRow['periodno'] != $PeriodNo) {
			if ($PeriodNo != - 9999) { //ie its not the first time around
				/*Get the ChartDetails balance b/fwd and the actual movement in the account for the period as recorded in the chart details - need to ensure integrity of transactions to the chart detail movements. Also, for a balance sheet account it is the balance carried forward that is important, not just the transactions*/

				$SQL = "SELECT bfwd,
								actual,
								period
							FROM chartdetails
							WHERE chartdetails.accountcode='" . $SelectedAccount . "'
								AND chartdetails.period='" . $PeriodNo . "'";

				$ErrMsg = _('The chart details for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved');
				$ChartDetailsResult = DB_query($SQL, $ErrMsg);
				$ChartDetailRow = DB_fetch_array($ChartDetailsResult);

				if ($PeriodNo != - 9999) {
					$PeriodSQL = "SELECT lastdate_in_period FROM periods WHERE periodno='" . $PeriodNo . "'";
					$PeriodResult = DB_query($PeriodSQL);
					$PeriodRow = DB_fetch_array($PeriodResult);
					echo '<tr>
							<td colspan="4"><b>', _('Total for period ending'), ' ', ConvertSQLDate($PeriodRow['lastdate_in_period']), '</b></td>';
					if ($PeriodTotal < 0) { //its a credit balance b/fwd
						if ($PandLAccount == True) {
							//							$RunningTotal = 0;
							
						}
						echo '<td></td>
								<td class="number"><b>', locale_number_format(-$PeriodTotal, $_SESSION['CompanyRecord']['decimalplaces']), '</b></td>
								<td></td>
							</tr>';
					} else { //its a debit balance b/fwd
						if ($PandLAccount == True) {
							//								$RunningTotal = 0;
							
						}
						echo '<td class="number"><b>', locale_number_format($PeriodTotal, $_SESSION['CompanyRecord']['decimalplaces']), '</b></td>
								<td colspan="2"></td>
							</tr>';
					}
				}
				$IntegrityReport.= '<br />' . _('Period') . ': ' . $PeriodNo . _('Account movement per transaction') . ': ' . locale_number_format($PeriodTotal, $_SESSION['CompanyRecord']['decimalplaces']) . ' ' . _('Movement per ChartDetails record') . ': ' . locale_number_format($ChartDetailRow['actual'], $_SESSION['CompanyRecord']['decimalplaces']) . ' ' . _('Period difference') . ': ' . locale_number_format($PeriodTotal - $ChartDetailRow['actual'], 3);

				if (ABS($PeriodTotal - $ChartDetailRow['actual']) > 0.01 and $_POST['tag'] == 0) {
					$ShowIntegrityReport = True;
				}
			}
			$PeriodNo = $MyRow['periodno'];
			$PeriodTotal = 0;
		}

		$RunningTotal+= $MyRow['amount'];
		$PeriodTotal+= $MyRow['amount'];

		if ($MyRow['amount'] >= 0) {
			$DebitAmount = locale_number_format($MyRow['amount'], $_SESSION['CompanyRecord']['decimalplaces']);
			$CreditAmount = '';
		} else {
			$CreditAmount = locale_number_format(-$MyRow['amount'], $_SESSION['CompanyRecord']['decimalplaces']);
			$DebitAmount = '';
		}

		$FormatedTranDate = ConvertSQLDate($MyRow['trandate']);
		$URL_to_TransDetail = $RootPath . '/GLTransInquiry.php?TypeID=' . urlencode($MyRow['type']) . '&amp;TransNo=' . urlencode($MyRow['typeno']);

		$TagSQL = "SELECT tagdescription FROM tags WHERE tagref='" . $MyRow['tag'] . "'";
		$TagResult = DB_query($TagSQL);
		$TagRow = DB_fetch_array($TagResult);
		if ($TagRow['tagdescription'] == '') {
			$TagRow['tagdescription'] = _('None');
		}
		echo '<tr class="striped_row">
				<td>', _($MyRow['typename']), '</td>
				<td class="number"><a href="', $URL_to_TransDetail, '">', $MyRow['typeno'], '</a></td>
				<td>', $MyRow['chequeno'], '</td>
				<td>', $FormatedTranDate, '</td>
				<td>', $MyRow['narrative'], '</td>
				<td>', $TagRow['tagdescription'], '</td>
				<td class="number">', $DebitAmount, '</td>
				<td class="number">', $CreditAmount, '</td>
				<td class="number"><b>', locale_number_format($RunningTotal, $_SESSION['CompanyRecord']['decimalplaces']), '</b></td>
			</tr>';

	}
	if ($PeriodNo != - 9999) {
		$PeriodSQL = "SELECT lastdate_in_period FROM periods WHERE periodno='" . $PeriodNo . "'";
		$PeriodResult = DB_query($PeriodSQL);
		$PeriodRow = DB_fetch_array($PeriodResult);
		echo '<tr>
				<td colspan="5"><b>', _('Total for period ending'), ' ', ConvertSQLDate($PeriodRow['lastdate_in_period']), '</b></td>';
		if ($PeriodTotal < 0) { //its a credit balance b/fwd
			echo '<td></td>
					<td class="number"><b>', locale_number_format(-$PeriodTotal, $_SESSION['CompanyRecord']['decimalplaces']), '</b></td>
					<td></td>
				</tr>';
		} else { //its a debit balance b/fwd
			echo '<td class="number"><b>', locale_number_format($PeriodTotal, $_SESSION['CompanyRecord']['decimalplaces']), '</b></td>
					<td colspan="4"></td>
				</tr>';
		}
	}

	echo '<tr>
			<td colspan="6"><b>';
	if ($PandLAccount == True) {
		echo _('Total Movement for selected periods');
	} else {
		/*its a balance sheet account*/
		echo _('Balance C/Fwd');
	}
	echo '</b></td>';

	if ($RunningTotal > 0) {
		echo '<td class="number">
				<b>', locale_number_format(($RunningTotal), $_SESSION['CompanyRecord']['decimalplaces']), '</b>
			</td>
		</tr>';
	} else {
		echo '<td class="number" colspan="2">
				<b>', locale_number_format((-$RunningTotal), $_SESSION['CompanyRecord']['decimalplaces']), '</b>
			</td>
		</tr>';
	}
	echo '</table>';
}
/* end of if Show button hit */

if (isset($ShowIntegrityReport) and $ShowIntegrityReport == True) {
	if (!isset($IntegrityReport)) {
		$IntegrityReport = '';
	}
	prnMsg(_('There are differences between the sum of the transactions and the recorded movements in the ChartDetails table') . '. ' . _('A log of the account differences for the periods report shows below'), 'warn');
	echo '<p>' . $IntegrityReport;
}
include ('includes/footer.php');
?>