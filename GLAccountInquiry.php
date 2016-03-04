<?php

include('includes/session.inc');
$Title = _('General Ledger Account Inquiry');
$ViewTopic = 'GeneralLedger';
$BookMark = 'GLAccountInquiry';
include('includes/header.inc');
include('includes/GLPostings.inc');

if (isset($_POST['Select'])) {
	$_POST['Account'] = $_POST['Select'];
}
if (isset($_POST['Account'])) {
	$SelectedAccount = $_POST['Account'];
} elseif (isset($_GET['Account'])) {
	$SelectedAccount = $_GET['Account'];
}

if (isset($_POST['ToPeriod'])) {
	$SelectedToPeriod = $_POST['ToPeriod'];
} elseif (isset($_GET['ToPeriod'])) {
	$SelectedToPeriod = $_GET['ToPeriod'];
}

if (isset($_POST['FromPeriod'])) {
	$SelectedFromPeriod = $_POST['FromPeriod'];
} elseif (isset($_GET['FromPeriod'])) {
	$SelectedFromPeriod = $_GET['FromPeriod'];
}

if (isset($_POST['Period'])) {
	$SelectedPeriod = $_POST['Period'];
}

echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/transactions.png" title="' . _('General Ledger Account Inquiry') . '" alt="' . _('General Ledger Account Inquiry') . '" />' . ' ' . _('General Ledger Account Inquiry') . '</p>';

if (isset($SelectedAccount) and $_SESSION['CompanyRecord']['retainedearnings'] == $SelectedAccount) {
	prnMsg(_('The retained earnings account is managed separately by the system, and therefore cannot be inquired upon. See manual for details'), 'info');
	echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Select another account') . '</a>';
	include('includes/footer.inc');
	exit;
}

echo '<div class="page_help_text">' . _('Use the keyboard Shift key to select multiple periods') . '</div>';

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

/* Get the start and periods, depending on how this script was called*/
if (isset($SelectedPeriod)) { //If it was called from itself (in other words an inquiry was run and we wish to leave the periods selected unchanged
	$FirstPeriodSelected = min($SelectedPeriod);
	$LastPeriodSelected = max($SelectedPeriod);
} elseif (isset($_GET['FromPeriod'])) { //If it was called from the Trial Balance/P&L or Balance sheet
	$FirstPeriodSelected = $_GET['FromPeriod'];
	$LastPeriodSelected = $_GET['ToPeriod'];
} else { // Otherwise just highlight the current period
	$FirstPeriodSelected = GetPeriod(date($_SESSION['DefaultDateFormat']));
	$LastPeriodSelected = GetPeriod(date($_SESSION['DefaultDateFormat']));
}

/*Dates in SQL format for the last day of last month*/
$DefaultPeriodDate = Date('Y-m-d', Mktime(0, 0, 0, Date('m'), 0, Date('Y')));

/*Show a form to allow input of criteria for TB to show */
echo '<table class="selection" summary="', _('Inquiry Selection Criteria'), '">
		<tr>
			<td>', _('Account'), ':</td>
			<td><select name="Account">';
$SQL = "SELECT chartmaster.accountcode,
			   chartmaster.accountname
		FROM chartmaster
		INNER JOIN glaccountusers
			ON glaccountusers.accountcode=chartmaster.accountcode
			AND glaccountusers.userid='" .  $_SESSION['UserID'] . "'
			AND glaccountusers.canview=1
		WHERE chartmaster.accountcode<>'" . $_SESSION['CompanyRecord']['retainedearnings'] . "'
			AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
		ORDER BY chartmaster.accountcode";
echo $SQL;
$Account = DB_query($SQL);
while ($MyRow = DB_fetch_array($Account)) {
	if (isset($SelectedAccount) and $MyRow['accountcode'] == $SelectedAccount) {
		echo '<option selected="selected" value="', $MyRow['accountcode'], '">', $MyRow['accountcode'], ' ', htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false), '</option>';
	} else {
		echo '<option value="', $MyRow['accountcode'], '">', $MyRow['accountcode'], ' ', htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false), '</option>';
	}
}
echo '</select>
		</td>
	</tr>';

//Select the tag
echo '<tr>
		<td>', _('Select Tag'), ':</td>
		<td><select name="tag">';

$SQL = "SELECT tagref,
			tagdescription
		FROM tags
		ORDER BY tagdescription";

$Result = DB_query($SQL);
echo '<option value="0">0 - ', _('All tags'), '</option>';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['tag']) and $_POST['tag'] == $MyRow['tagref']) {
		echo '<option selected="selected" value="', $MyRow['tagref'], '">', $MyRow['tagref'], ' - ', $MyRow['tagdescription'], '</option>';
	} else {
		echo '<option value="', $MyRow['tagref'] . '">', $MyRow['tagref'], ' - ', $MyRow['tagdescription'], '</option>';
	}
}
echo '</select>
			</td>
		</tr>
		<tr>
			<td>', _('For Period range'), ':</td>
			<td><select name="Period[]" size="12" multiple="multiple">';
$SQL = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC";
$Periods = DB_query($SQL);
$id = 0;
while ($MyRow = DB_fetch_array($Periods)) {
	if (isset($FirstPeriodSelected) and $MyRow['periodno'] >= $FirstPeriodSelected and $MyRow['periodno'] <= $LastPeriodSelected) {
		echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' . _(MonthAndYearFromSQLDate($MyRow['lastdate_in_period'])) . '</option>';
		$id++;
	} else {
		echo '<option value="' . $MyRow['periodno'] . '">' . _(MonthAndYearFromSQLDate($MyRow['lastdate_in_period'])) . '</option>';
	}
}
echo '</select>
			</td>
		</tr>
	</table>
	<div class="centre">
		<input type="submit" name="Show" value="', _('Show Account Transactions'), '" />
	</div>
</form>';

/* End of the Form  rest of script is what happens if the show button is hit*/

if (isset($_POST['Show'])) {

	if (!isset($SelectedPeriod)) {
		prnMsg(_('A period or range of periods must be selected from the list box'), 'info');
		include('includes/footer.inc');
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

	$SQL = "SELECT counterindex,
				type,
				typename,
				gltrans.typeno,
				trandate,
				narrative,
				chequeno,
				amount,
				periodno,
				gltrans.tag,
				tagdescription
			FROM gltrans INNER JOIN systypes
			ON systypes.typeid=gltrans.type
			LEFT JOIN tags
			ON gltrans.tag = tags.tagref
			WHERE gltrans.account = '" . $SelectedAccount . "'
			AND posted=1
			AND periodno>='" . $FirstPeriodSelected . "'
			AND periodno<='" . $LastPeriodSelected . "'";

	if ($_POST['tag'] != 0) {
		$SQL = $SQL . " AND tag='" . $_POST['tag'] . "'";
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

	echo '<table class="selection" summary="', _('General Ledger account inquiry details'), '">
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
					<th>', _('Debit'), '</th>
					<th>', _('Credit'), '</th>
					<th>', _('Narrative'), '</th>
					<th>', _('Balance'), '</th>
					<th>', _('Tag'), '</th>
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
						WHERE account='" . $SelectedAccount . "'
							AND periodno<" . $FirstPeriodSelected . "
							AND tag like '" . $_POST['tag'] . "'";
		$BfwdResult = DB_query($BfwdSQL);
		$BfwdRow = DB_fetch_array($BfwdResult);

		$RunningTotal = $BfwdRow['bfwd'];
		if ($RunningTotal < 0) { //its a credit balance b/fwd
			echo '<tr>
					<td colspan="5"><b>' . _('Brought Forward Balance') . '</b></td>
					<td class="number"><b>' . locale_number_format(-$RunningTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
				</tr>';
		} else { //its a debit balance b/fwd
			echo '<tr>
					<td colspan="4"><b>' . _('Brought Forward Balance') . '</b></td>
					<td class="number"><b>' . locale_number_format($RunningTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
				</tr>';
		}
	}
	$PeriodTotal = 0;
	$PeriodNo = -9999;
	$ShowIntegrityReport = False;
	$j = 1;
	$k = 0; //row colour counter
	$IntegrityReport = '';
	while ($MyRow = DB_fetch_array($TransResult)) {
		if ($MyRow['periodno'] != $PeriodNo) {
			if ($PeriodNo != -9999) { //ie its not the first time around
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

				if ($PeriodNo != -9999) {
					$PeriodSQL = "SELECT lastdate_in_period FROM periods WHERE periodno='" . $PeriodNo . "'";
					$PeriodResult = DB_query($PeriodSQL);
					$PeriodRow = DB_fetch_array($PeriodResult);
					echo '<tr>
							<td colspan="4"><b>' . _('Total for period ending') . ' ' . ConvertSQLDate($PeriodRow['lastdate_in_period']) . '</b></td>';
					if ($PeriodTotal < 0) { //its a credit balance b/fwd
						if ($PandLAccount == True) {
							//							$RunningTotal = 0;
						}
						echo '<td></td>
									<td class="number"><b>' . locale_number_format(-$PeriodTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
									<td></td>
								</tr>';
					} else { //its a debit balance b/fwd
						if ($PandLAccount == True) {
							//								$RunningTotal = 0;
						}
						echo '<td class="number"><b>' . locale_number_format($PeriodTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
									<td colspan="2"></td>
								</tr>';
					}
				}
				$IntegrityReport .= '<br />' . _('Period') . ': ' . $PeriodNo . _('Account movement per transaction') . ': ' . locale_number_format($PeriodTotal, $_SESSION['CompanyRecord']['decimalplaces']) . ' ' . _('Movement per ChartDetails record') . ': ' . locale_number_format($ChartDetailRow['actual'], $_SESSION['CompanyRecord']['decimalplaces']) . ' ' . _('Period difference') . ': ' . locale_number_format($PeriodTotal - $ChartDetailRow['actual'], 3);

				if (ABS($PeriodTotal - $ChartDetailRow['actual']) > 0.01 and $_POST['tag'] == 0) {
					$ShowIntegrityReport = True;
				}
			}
			$PeriodNo = $MyRow['periodno'];
			$PeriodTotal = 0;
		}

		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			++$k;
		}

		$RunningTotal += $MyRow['amount'];
		$PeriodTotal += $MyRow['amount'];

		if ($MyRow['amount'] >= 0) {
			$DebitAmount = locale_number_format($MyRow['amount'], $_SESSION['CompanyRecord']['decimalplaces']);
			$CreditAmount = '';
		} else {
			$CreditAmount = locale_number_format(-$MyRow['amount'], $_SESSION['CompanyRecord']['decimalplaces']);
			$DebitAmount = '';
		}

		$FormatedTranDate = ConvertSQLDate($MyRow['trandate']);
		$URL_to_TransDetail = $RootPath . '/GLTransInquiry.php?TypeID=' . $MyRow['type'] . '&amp;TransNo=' . $MyRow['typeno'];

		$tagsql = "SELECT tagdescription FROM tags WHERE tagref='" . $MyRow['tag'] . "'";
		$tagresult = DB_query($tagsql);
		$tagrow = DB_fetch_array($tagresult);
		if ($tagrow['tagdescription'] == '') {
			$tagrow['tagdescription'] = _('None');
		}
		printf('<td>%s</td>
				<td class="number"><a href="%s">%s</a></td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				<td class="number"><b>%s</b></td>
				<td>%s</td>
			</tr>', _($MyRow['typename']), $URL_to_TransDetail, $MyRow['typeno'], $MyRow['chequeno'], $FormatedTranDate, $DebitAmount, $CreditAmount, $MyRow['narrative'], locale_number_format($RunningTotal, $_SESSION['CompanyRecord']['decimalplaces']), $tagrow['tagdescription']);

	}
	if ($PeriodNo != -9999) {
		$PeriodSQL = "SELECT lastdate_in_period FROM periods WHERE periodno='" . $PeriodNo . "'";
		$PeriodResult = DB_query($PeriodSQL);
		$PeriodRow = DB_fetch_array($PeriodResult);
		echo '<tr>
				<td colspan="3"><b>' . _('Total for period ending') . ' ' . ConvertSQLDate($PeriodRow['lastdate_in_period']) . '</b></td>';
		if ($PeriodTotal < 0) { //its a credit balance b/fwd
			echo '<td></td>
					<td class="number"><b>' . locale_number_format(-$PeriodTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
					<td></td>
				</tr>';
		} else { //its a debit balance b/fwd
			echo '<td class="number"><b>' . locale_number_format($PeriodTotal, $_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
					<td colspan="2"></td>
				</tr>';
		}
	}

	echo '<tr><td colspan="4"><b>';
	if ($PandLAccount == True) {
		echo _('Total Movement for selected periods');
	} else {
		/*its a balance sheet account*/
		echo _('Balance C/Fwd');
	}
	echo '</b></td>';

	if ($RunningTotal > 0) {
		echo '<td class="number">
				<b>' . locale_number_format(($RunningTotal), $_SESSION['CompanyRecord']['decimalplaces']) . '</b>
			</td>
		</tr>';
	} else {
		echo '<td class="number" colspan="2">
				<b>' . locale_number_format((-$RunningTotal), $_SESSION['CompanyRecord']['decimalplaces']) . '</b>
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
include('includes/footer.inc');
?>