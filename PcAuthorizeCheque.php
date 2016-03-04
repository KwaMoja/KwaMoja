<?php

include('includes/session.inc');
$Title = _('Authorisation of Petty Cash Expenses');
/* Manual links before header.inc */
$ViewTopic = 'PettyCash';
$BookMark = 'AuthorizeExpense';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_POST['SelectedTabs'])) {
	$SelectedTabs = mb_strtoupper($_POST['SelectedTabs']);
} elseif (isset($_GET['SelectedTabs'])) {
	$SelectedTabs = mb_strtoupper($_GET['SelectedTabs']);
}

if (isset($_POST['SelectedIndex'])) {
	$SelectedIndex = $_POST['SelectedIndex'];
} elseif (isset($_GET['SelectedIndex'])) {
	$SelectedIndex = $_GET['SelectedIndex'];
}

if (isset($_POST['Days'])) {
	$Days = filter_number_format($_POST['Days']);
} elseif (isset($_GET['Days'])) {
	$Days = filter_number_format($_GET['Days']);
}

if (isset($_POST['Process'])) {
	if ($SelectedTabs == '') {
		prnMsg(_('You Must First Select a Petty Cash Tab To Authorise'), 'error');
		unset($SelectedTabs);
	}
}

if (isset($_POST['Go'])) {
	if ($Days <= 0) {
		prnMsg(_('The number of days must be a positive number'), 'error');
		$Days = 30;
	}
}

if (isset($SelectedTabs)) {
	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . _('Petty Cash') . '" alt="" />' . _('Authorisation Of Petty Cash Expenses ') . '' . $SelectedTabs . '</p>';
} else {
	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . _('Petty Cash') . '" alt="" />' . _('Authorisation Of Petty Cash Expenses ') . '</p>';
}
if (isset($_POST['Submit']) or isset($_POST['update']) or isset($SelectedTabs) or isset($_POST['GO'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (!isset($Days)) {
		$Days = 30;
	}
	echo '<input type="hidden" name="SelectedTabs" value="' . $SelectedTabs . '" />';
	echo '<br /><table class="selection">';
	echo '<tr>
			<th colspan="7">' . _('Detail Of Movement For Last ') . ': ';
	echo '<input type="text" class="number" name="Days" value="' . $Days . '" maxlength="3" size="4" />' . _('Days');
	echo '<input type="submit" name="Go" value="' . _('Go') . '" /></th></tr>';

	$SQL = "SELECT pcashdetails.counterindex,
				pcashdetails.tabcode,
				pcashdetails.date,
				pcashdetails.codeexpense,
				pcashdetails.amount,
				pcashdetails.authorized,
				pcashdetails.posted,
				pcashdetails.notes,
				pcashdetails.receipt,
				pctabs.glaccountassignment,
				pctabs.glaccountpcash,
				pctabs.usercode,
				pctabs.currency,
				currencies.rate,
				currencies.decimalplaces
			FROM pcashdetails, pctabs, currencies
			WHERE pcashdetails.tabcode = pctabs.tabcode
				AND pctabs.currency = currencies.currabrev
				AND pcashdetails.tabcode = '" . $SelectedTabs . "'
				AND pcashdetails.date >= DATE_SUB(CURDATE(), INTERVAL '" . $Days . "' DAY)
				AND pcashdetails.codeexpense='ASSIGNCASH'
			ORDER BY pcashdetails.date, pcashdetails.counterindex ASC";

	$Result = DB_query($SQL);

	echo '<tr>
		<th>' . _('Date') . '</th>
		<th>' . _('Expense Code') . '</th>
		<th>' . _('Amount') . '</th>
		<th>' . _('Posted') . '</th>
		<th>' . _('Notes') . '</th>
		<th>' . _('Receipt') . '</th>
		<th>' . _('Authorised') . '</th>
	</tr>';

	$k = 0; //row colour counter

	while ($MyRow = DB_fetch_array($Result)) {
		$CurrDecimalPlaces = $MyRow['decimalplaces'];
		//update database if update pressed
		if (isset($_POST['Submit']) and $_POST['Submit'] == _('Update') and isset($_POST[$MyRow['counterindex']])) {

			$PeriodNo = GetPeriod(ConvertSQLDate($MyRow['date']));

			if ($MyRow['rate'] == 1) { // functional currency
				$Amount = $MyRow['amount'];
			} else { // other currencies
				$Amount = $MyRow['amount'] / $MyRow['rate'];
			}

			if ($MyRow['codeexpense'] == 'ASSIGNCASH') {
				$type = 2;
				$AccountFrom = $MyRow['glaccountassignment'];
				$AccountTo = $MyRow['glaccountpcash'];
				$TagTo = 0;
			} else {
				$type = 1;
				$Amount = -$Amount;
				$AccountFrom = $MyRow['glaccountpcash'];
				$SQLAccExp = "SELECT glaccount,
									tag
								FROM pcexpenses
								WHERE codeexpense = '" . $MyRow['codeexpense'] . "'";
				$ResultAccExp = DB_query($SQLAccExp);
				$MyRowAccExp = DB_fetch_array($ResultAccExp);
				$AccountTo = $MyRowAccExp['glaccount'];
				$TagTo = $MyRowAccExp['tag'];
			}

			//get typeno
			$typeno = GetNextTransNo($type);

			//build narrative
			$Narrative = _('PettyCash') . ' - ' . $MyRow['tabcode'] . ' - ' . $MyRow['codeexpense'] . ' - ' . DB_escape_string($MyRow['notes']) . ' - ' . $MyRow['receipt'];
			//insert to gltrans
			DB_Txn_Begin();

			$SQLFrom = "INSERT INTO `gltrans` (`counterindex`,
											`type`,
											`typeno`,
											`chequeno`,
											`trandate`,
											`periodno`,
											`account`,
											`narrative`,
											`amount`,
											`posted`,
											`jobref`,
											`tag`)
									VALUES (NULL,
											'" . $type . "',
											'" . $typeno . "',
											0,
											'" . $MyRow['date'] . "',
											'" . $PeriodNo . "',
											'" . $AccountFrom . "',
											'" . $Narrative . "',
											'" . -$Amount . "',
											0,
											'',
											'" . $TagTo . "')";

			$ResultFrom = DB_Query($SQLFrom, '', '', true);

			$SQLTo = "INSERT INTO `gltrans` (`counterindex`,
										`type`,
										`typeno`,
										`chequeno`,
										`trandate`,
										`periodno`,
										`account`,
										`narrative`,
										`amount`,
										`posted`,
										`jobref`,
										`tag`)
								VALUES (NULL,
										'" . $type . "',
										'" . $typeno . "',
										0,
										'" . $MyRow['date'] . "',
										'" . $PeriodNo . "',
										'" . $AccountTo . "',
										'" . $Narrative . "',
										'" . $Amount . "',
										0,
										'',
										'" . $TagTo . "')";

			$ResultTo = DB_Query($SQLTo, '', '', true);

			if ($MyRow['codeexpense'] == 'ASSIGNCASH') {
				// if it's a cash assignation we need to updated banktrans table as well.
				$ReceiptTransNo = GetNextTransNo(2);
				$SQLBank = "INSERT INTO banktrans (transno,
												type,
												bankact,
												ref,
												exrate,
												functionalexrate,
												transdate,
												banktranstype,
												amount,
												currcode,
												userid
											) VALUES (
												'" . $ReceiptTransNo . "',
												1,
												'" . $AccountFrom . "',
												'" . $Narrative . "',
												1,
												'" . $MyRow['rate'] . "',
												'" . $MyRow['date'] . "',
												'Cash',
												'" . -$MyRow['amount'] . "',
												'" . $MyRow['currency'] . "',
												'" . $_SESSION['UserID'] . "'
											)";
				$ErrMsg = _('Cannot insert a bank transaction because');
				$DbgMsg = _('Cannot insert a bank transaction with the SQL');
				$ResultBank = DB_query($SQLBank, $ErrMsg, $DbgMsg, true);

			}

			$SQL = "UPDATE pcashdetails
					SET authorized = CURRENT_DATE,
					posted = 1
					WHERE counterindex = '" . $MyRow['counterindex'] . "'";
			$Resultupdate = DB_query($SQL, '', '', true);
			DB_Txn_Commit();
		}

		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}
		if ($MyRow['posted'] == 0) {
			$Posted = _('No');
		} else {
			$Posted = _('Yes');
		}
		echo '<td>' . ConvertSQLDate($MyRow['date']) . '</td>
			<td>' . $MyRow['codeexpense'] . '</td>
			<td class="number">' . locale_number_format($MyRow['amount'], $CurrDecimalPlaces) . '</td>
			<td>' . $Posted . '</td>
			<td>' . $MyRow['notes'] . '</td>
			<td>' . $MyRow['receipt'] . '</td>';

		if (isset($_POST[$MyRow['counterindex']])) {
			echo '<td>' . ConvertSQLDate(Date('Y-m-d'));
		} else {
			//compare against raw SQL format date, then convert for display.
			if (($MyRow['authorized'] != '0000-00-00')) {
				echo '<td>' . ConvertSQLDate($MyRow['authorized']);
			} else {
				echo '<td align="right"><input type="checkbox" name="' . $MyRow['counterindex'] . '" />';
			}
		}

		echo '<input type="hidden" name="SelectedIndex" value="' . $MyRow['counterindex'] . '" />';
		echo '</td></tr>';


	} //end of looping

	$SQLamount = "SELECT sum(amount)
			FROM pcashdetails
			WHERE tabcode='" . $SelectedTabs . "'
				AND codeexpense='ASSIGNCASH'";

	$ResultAmount = DB_query($SQLamount);
	$Amount = DB_fetch_array($ResultAmount);

	if (!isset($Amount['0'])) {
		$Amount['0'] = 0;
	}

	echo '<tr><td colspan="2" class="number">' . _('Current balance') . ':</td>
				<td class="number">' . locale_number_format($Amount['0'], $CurrDecimalPlaces) . '</td></tr>';

	// Do the postings
	include('includes/GLPostings.inc');
	echo '</table><br /><div class="centre"><input type="submit" name="Submit" value="' . _('Update') . '" /></div>
		  </form>';


} else {
	/*The option to submit was not hit so display form */


	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<br /><table class="selection">'; //Main table

	$SQL = "SELECT tabcode
		FROM pctabs
		WHERE authorizer='" . $_SESSION['UserID'] . "'";

	$Result = DB_query($SQL);

	echo '<tr>
			<td>' . _('Authorise expenses to Petty Cash Tab') . ':</td>
			<td><select required="required" name="SelectedTabs">';

	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['SelectTabs']) and $MyRow['tabcode'] == $_POST['SelectTabs']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $MyRow['tabcode'] . '">' . $MyRow['tabcode'] . '</option>';

	} //end while loop get type of tab

	echo '</select></td></tr>';
	echo '</table>'; // close main table
	DB_free_result($Result);

	echo '<br /><div class="centre"><input type="submit" name="Process" value="' . _('Accept') . '" />
								<input type="submit" name="Cancel" value="' . _('Cancel') . '" /></div>';
	echo '</form>';
}
/*end of else not submit */
include('includes/footer.inc');
?>