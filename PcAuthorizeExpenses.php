<?php
include ('includes/session.php');
$Title = _('Authorisation of Petty Cash Expenses');

if (isset($_GET['download'])) {
	$SQL = "SELECT type,
					size,
					content
				FROM pcreceipts
				WHERE pccashdetail='" . $_GET['receipt'] . "'
					AND name='" . $_GET['name'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	header('Content-type: ' . $MyRow['type'] . "\n");
	header('Content-Disposition: attachment; filename=' . $_GET['name'] . "\n");
	header('Content-Length: ' . $MyRow['size'] . "\n");
	echo $MyRow['content'];
	exit;
}

/* Manual links before header.php */
$ViewTopic = 'PettyCash';
$BookMark = 'AuthorizeExpense';
include ('includes/header.php');
include ('includes/SQL_CommonFunctions.php');

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
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', _('Petty Cash'), '" alt="" />', _('Authorisation Of Petty Cash Expenses'), ' ', $SelectedTabs, '
		</p>';
} else {
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', _('Petty Cash'), '" alt="" />', _('Authorisation Of Petty Cash Expenses'), '
		</p>';
}
if (isset($_POST['Submit']) or isset($_POST['update']) or isset($SelectedTabs) or isset($_POST['GO'])) {

	echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	if (!isset($Days)) {
		$Days = 30;
	}
	echo '<input type="hidden" name="SelectedTabs" value="', $SelectedTabs, '" />';

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
				AND pcashdetails.codeexpense<>'ASSIGNCASH'
			ORDER BY pcashdetails.date, pcashdetails.counterindex ASC";
	$Result = DB_query($SQL);

	echo '<table>
			<thead>
				<tr>
					<th colspan="10">', _('Detail Of Movement For Last '), ':
						<input type="text" class="number" name="Days" value="', $Days, '" maxlength="4" size="4" />', _('Days'), '
						<input type="submit" name="Go" value="', _('Go'), '" />
					</th>
				</tr>
				<tr>
					<th class="SortedColumn">', _('Date'), '</th>
					<th class="SortedColumn">', _('Expense Code'), '</th>
					<th class="SortedColumn">', _('Amount Claimed'), '</th>
					<th colspan="2">', _('Taxes'), '</th>
					<th class="SortedColumn">', _('Tag'), '</th>
					<th class="SortedColumn">', _('Posted'), '</th>
					<th class="SortedColumn">', _('Notes'), '</th>
					<th>', _('Receipt'), '</th>
					<th>', _('Authorised'), '</th>
				</tr>
			</thead>';

	echo '<tbody>';

	while ($MyRow = DB_fetch_array($Result)) {
		$CurrDecimalPlaces = $MyRow['decimalplaces'];
		//update database if update pressed
		$PeriodNo = GetPeriod(ConvertSQLDate($MyRow['date']));

		$TaxTotalSQL = "SELECT SUM(amount) as totaltax FROM pcashdetailtaxes WHERE pccashdetail='" . $MyRow['counterindex'] . "'";
		$TaxTotalResult = DB_query($TaxTotalSQL);
		$TaxTotalRow = DB_fetch_array($TaxTotalResult);

		if ($MyRow['rate'] == 1) { // functional currency
			$GrossAmount = $MyRow['amount'];
			$NetAmount = $MyRow['amount'] - $TaxTotalRow['totaltax'];
		} else { // other currencies
			$GrossAmount = ($MyRow['amount']) / $MyRow['rate'];
			$NetAmount = ($MyRow['amount'] - $TaxTotalRow['totaltax']) / $MyRow['rate'];
		}

		if ($MyRow['codeexpense'] == 'ASSIGNCASH') {
			$Type = 2;
			$AccountFrom = $MyRow['glaccountassignment'];
			$AccountTo = $MyRow['glaccountpcash'];
			$TagDescription = '0 - ' . _('None');
		} else {
			$Type = 1;
			$GrossAmount = - $GrossAmount;
			$NetAmount = - $NetAmount;
			$AccountFrom = $MyRow['glaccountpcash'];
			$SQLAccExp = "SELECT glaccount,
								tag
							FROM pcexpenses
							WHERE codeexpense = '" . $MyRow['codeexpense'] . "'";
			$ResultAccExp = DB_query($SQLAccExp);
			$MyRowAccExp = DB_fetch_array($ResultAccExp);
			$AccountTo = $MyRowAccExp['glaccount'];
		}
		if (isset($_POST['Submit']) and $_POST['Submit'] == _('Update') and isset($_POST[$MyRow['counterindex']])) {

			//get typeno
			$TypeNo = GetNextTransNo($Type);

			$TagsSQL = "SELECT tag FROM pctags WHERE pccashdetail='" . $MyRow['counterindex'] . "'";
			$TagsResult = DB_query($TagsSQL);
			while ($TagRow = DB_fetch_array($TagsResult)) {
				$Tags[] = $TagRow['tag'];
			}

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
											`jobref`)
									VALUES (NULL,
											'" . $Type . "',
											'" . $TypeNo . "',
											0,
											'" . $MyRow['date'] . "',
											'" . $PeriodNo . "',
											'" . $AccountFrom . "',
											'" . $Narrative . "',
											'" . -$NetAmount . "',
											0,
											'')";
			$ResultFrom = DB_query($SQLFrom, '', '', true);
			$SQL = "INSERT INTO gltags VALUES ( LAST_INSERT_ID(),
												'0')";
			$ErrMsg = _('Cannot insert a GL tag for the payment line because');
			$DbgMsg = _('The SQL that failed to insert the GL tag record was');
			$InsertResult = DB_query($SQL, $ErrMsg, $DbgMsg, true);

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
										`jobref`)
								VALUES (NULL,
										'" . $Type . "',
										'" . $TypeNo . "',
										0,
										'" . $MyRow['date'] . "',
										'" . $PeriodNo . "',
										'" . $AccountTo . "',
										'" . $Narrative . "',
										'" . $GrossAmount . "',
										0,
										'')";
			$ResultTo = DB_query($SQLTo, '', '', true);
			foreach ($Tags as $Tag) {
				$SQL = "INSERT INTO gltags VALUES ( LAST_INSERT_ID(),
													'" . $Tag . "')";
				$ErrMsg = _('Cannot insert a GL tag for the payment line because');
				$DbgMsg = _('The SQL that failed to insert the GL tag record was');
				$InsertResult = DB_query($SQL, $ErrMsg, $DbgMsg, true);
			}

			$TaxSQL = "SELECT counterindex,
								pccashdetail,
								calculationorder,
								description,
								taxauthid,
								purchtaxglaccount,
								taxontax,
								taxrate,
								amount
							FROM pcashdetailtaxes
							WHERE pccashdetail='" . $MyRow['counterindex'] . "'";
			$TaxResult = DB_query($TaxSQL);
			while ($MyTaxRow = DB_fetch_array($TaxResult)) {
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
												`jobref`)
										VALUES (NULL,
												'" . $Type . "',
												'" . $TypeNo . "',
												0,
												'" . $MyRow['date'] . "',
												'" . $PeriodNo . "',
												'" . $MyTaxRow['purchtaxglaccount'] . "',
												'" . $Narrative . "',
												'" . $MyTaxRow['amount'] . "',
												0,
												'')";
				$ResultTax = DB_query($SQLTo, '', '', true);
			}

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
												2,
												'" . $AccountFrom . "',
												'" . $Narrative . "',
												1,
												'" . $MyRow['rate'] . "',
												'" . $MyRow['date'] . "',
												'Cash',
												'" . -($MyRow['amount'] / $MyRow['rate']) . "',
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
			prnMsg(_('Expenses have been correctly authorised'), 'success');
			unset($_POST['Submit']);
			unset($SelectedTabs);
			unset($_POST['SelectedTabs']);
		}
		if ($MyRow['posted'] == 0) {
			$Posted = _('No');
		} else {
			$Posted = _('Yes');
		}
		echo '<tr class="striped_row">
				<td>', ConvertSQLDate($MyRow['date']), '</td>
				<td>', $MyRow['codeexpense'], '</td>
				<td class="number">', locale_number_format($MyRow['amount'], $CurrDecimalPlaces), '</td>';

		$SQLTags = "SELECT pctags.tag,
							tags.tagdescription
						FROM pctags
						INNER JOIN tags
							ON tags.tagref=pctags.tag
						WHERE pctags.pccashdetail='" . $MyRow['counterindex'] . "'";
		$TagsResult = DB_query($SQLTags);
		$TagString = '';
		while ($TagRow = DB_fetch_array($TagsResult)) {
			$TagString.= $TagRow['tag'] . ' - ' . $TagRow['tagdescription'] . '<br />';
		}

		$TaxesDescription = '';
		$TaxesTaxAmount = '';
		$TaxSQL = "SELECT counterindex,
							pccashdetail,
							calculationorder,
							description,
							taxauthid,
							purchtaxglaccount,
							taxontax,
							taxrate,
							amount
						FROM pcashdetailtaxes
						WHERE pccashdetail='" . $MyRow['counterindex'] . "'";
		$TaxResult = DB_query($TaxSQL);
		while ($MyTaxRow = DB_fetch_array($TaxResult)) {
			$TaxesDescription.= $MyTaxRow['description'] . '<br />';
			$TaxesTaxAmount.= locale_number_format($MyTaxRow['amount'], $CurrDecimalPlaces) . '<br />';
		}

		$ReceiptSQL = "SELECT name
							FROM pcreceipts
							WHERE pccashdetail='" . $MyRow['counterindex'] . "'";
		$ReceiptResult = DB_query($ReceiptSQL);

		if (DB_num_rows($ReceiptResult) > 0) {
			$ReceiptRow = DB_fetch_array($ReceiptResult);
			$ReceiptText = '<a href="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '?download=yes&receipt=' . urlencode($MyRow['counterindex']) . '&name=' . urlencode($ReceiptRow['name']) . '">' . _('View receipt') . '</a>';
		} else {
			$ReceiptText = _('No receipt');
		}

		echo '<td>', $TaxesDescription, '</td>
			<td>', $TaxesTaxAmount, '</td>
			<td>', $TagString, '</td>
			<td>', $Posted, '</td>
			<td>', $MyRow['notes'], '</td>
			<td>', $ReceiptText, '</td>';

		if (isset($_POST[$MyRow['counterindex']])) {
			echo '<td>' . ConvertSQLDate(Date('Y-m-d'));
		} else {
			//compare against raw SQL format date, then convert for display.
			if (($MyRow['authorized'] != '0000-00-00')) {
				echo '<td>', ConvertSQLDate($MyRow['authorized']);
			} else {
				echo '<td align="right"><input type="checkbox" name="', $MyRow['counterindex'], '" />';
			}
		}

		echo '<input type="hidden" name="SelectedIndex" value="', $MyRow['counterindex'], '" />
			</td>
		</tr>';

	} //end of looping
	echo '</tbody>';

	$SQLamount = "SELECT sum(amount)
			FROM pcashdetails
			WHERE tabcode='" . $SelectedTabs . "'
				AND codeexpense<>'ASSIGNCASH'";

	$ResultAmount = DB_query($SQLamount);

	$Amount = DB_fetch_array($ResultAmount);

	if (!isset($Amount['0'])) {
		$Amount['0'] = 0;
	}

	echo '<tr class="total_row">
			<td colspan="2" class="number">', _('Current balance'), ':</td>
			<td class="number">', locale_number_format($Amount['0'], $CurrDecimalPlaces), '</td>
			<td colspan="7"></td>
		</tr>';

	// Do the postings
	include ('includes/GLPostings.php');
	echo '</table>';

	echo '<div class="centre">
			<input type="submit" name="Submit" value="', _('Update'), '" />
		</div>
	</form>';

} else {
	/*The option to submit was not hit so display form */

	echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	$SQL = "SELECT tabcode
		FROM pctabs
		WHERE authorizerexpenses='" . $_SESSION['UserID'] . "'
		ORDER BY tabcode";
	$Result = DB_query($SQL);

	echo '<fieldset>
			<legend>', _('Select Tab'), '</legend>'; //Main table
	echo '<field>
			<label for="SelectedTabs">', _('Authorise expenses to Petty Cash Tab'), ':</label>
			<select required="required" name="SelectedTabs">';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['SelectTabs']) and $MyRow['tabcode'] == $_POST['SelectTabs']) {
			echo '<option selected="selected" value="', $MyRow['tabcode'], '">', $MyRow['tabcode'], '</option>';
		} else {
			echo '<option value="', $MyRow['tabcode'], '">', $MyRow['tabcode'], '</option>';
		}
	} //end while loop get type of tab
	echo '</select>
		</field>';

	echo '</fieldset>'; // close main table
	echo '<div class="centre">
			<input type="submit" name="Process" value="', _('Accept'), '" />
			<input type="submit" name="Cancel" value="', _('Cancel'), '" />
		</div>';

	echo '</form>';
}
/*end of else not submit */
include ('includes/footer.php');
?>