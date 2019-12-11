<?php
include ('includes/session.php');
$Title = _('Claim Petty Cash Expenses From Tab');

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
$BookMark = 'ExpenseClaim';
include ('includes/header.php');

if (isset($_POST['SelectedTabs'])) {
	$SelectedTabs = $_POST['SelectedTabs'];
} elseif (isset($_GET['SelectedTabs'])) {
	$SelectedTabs = $_GET['SelectedTabs'];
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

if (!isset($_POST['tag']) or sizeOf($_POST['tag']) === 0) {
	$_POST['tag'][0] = 0;
}

if (isset($_POST['Cancel'])) {
	unset($SelectedTabs);
	unset($SelectedIndex);
	unset($Days);
	unset($_POST['Amount']);
	unset($_POST['Notes']);
	unset($_POST['Receipt']);
}

if (isset($_POST['Process'])) {

	if ($_POST['SelectedTabs'] == '') {
		echo prnMsg(_('You have not selected a tab to claim the expenses on'), 'error');
		unset($SelectedTabs);
	}
}

if (isset($_POST['Go'])) {
	if ($Days <= 0) {
		prnMsg(_('The number of days must be a positive number'), 'error');
		$Days = 30;
	}
}

if (isset($_POST['submit'])) {
	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	 ie the page has called itself with some user input */

	//first off validate inputs sensible
	if ($_POST['SelectedExpense'] == '') {
		$InputError = 1;
		prnMsg(_('You have not selected an expense to claim on this tab'), 'error');
	} elseif ($_POST['Amount'] == 0) {
		$InputError = 1;
		prnMsg(_('The Amount must be greater than 0'), 'error');
	}

	if (!is_date($_POST['Date'])) {
		$InputError = 1;
		prnMsg(_('The date input is not a right format'), 'error');
	}

	if (isset($SelectedIndex) and $InputError != 1) {
		$SQL = "UPDATE pcashdetails
			SET date = '" . FormatDateForSQL($_POST['Date']) . "',
				codeexpense = '" . $_POST['SelectedExpense'] . "',
				amount = '" . -filter_number_format($_POST['Amount']) . "',
				notes = '" . $_POST['Notes'] . "'
			WHERE counterindex = '" . $SelectedIndex . "'";

		$Msg = _('The Expense Claim on Tab') . ' ' . $SelectedTabs . ' ' . _('has been updated');
		$Result = DB_query($SQL);

		$SQL = "DELETE FROM pctags WHERE pccashdetail='" . $SelectedIndex . "'";
		$Result = DB_query($SQL);

		foreach ($_POST['tag'] as $Tag) {
			$SQL = "INSERT INTO pctags (pccashdetail,
										tag)
									VALUES (
										'" . $SelectedIndex . "',
										'" . $Tag . "'
									)";
			$Result = DB_query($SQL);
		}

		foreach ($_POST as $Index => $Value) {
			if (substr($Index, 0, 5) == 'index') {
				$Index = $Value;
				$SQL = "UPDATE pcashdetailtaxes SET pccashdetail='" . $_POST['PcCashDetail' . $Index] . "',
													calculationorder='" . $_POST['CalculationOrder' . $Index] . "',
													description='" . $_POST['Description' . $Index] . "',
													taxauthid='" . $_POST['TaxAuthority' . $Index] . "',
													purchtaxglaccount='" . $_POST['TaxGLAccount' . $Index] . "',
													taxontax='" . $_POST['TaxOnTax' . $Index] . "',
													taxrate='" . $_POST['TaxRate' . $Index] . "',
													amount='" . $_POST['TaxAmount' . $Index] . "'
												WHERE counterindex='" . $Index . "'";
				$Result = DB_query($SQL);
			}
		}
		if (isset($_FILES['Receipt']) and $_FILES['Receipt']['name'] != '') {

			$UploadTheFile = 'Yes'; //Assume all is well to start off with
			//But check for the worst
			if ($_FILES['Receipt']['size'] > ($_SESSION['MaxImageSize'] * 1024)) { //File Size Check
				prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'], 'warn');
				$UploadTheFile = 'No';
			} elseif ($_FILES['Receipt']['type'] != 'image/jpeg' and $_FILES['Receipt']['type'] != 'image/png') { //File Type Check
				prnMsg(_('Only jpg or png files can be uploaded'), 'warn');
				$UploadTheFile = 'No';
			} elseif ($_FILES['Receipt']['error'] == 6) { //upload temp directory check
				prnMsg(_('No tmp directory set. You must have a tmp directory set in your PHP for upload of files.'), 'warn');
				$UploadTheFile = 'No';
			}

			if ($UploadTheFile == 'Yes') {
				$Name = $_FILES['Receipt']['name'];
				$Type = $_FILES['Receipt']['type'];
				$Size = $_FILES['Receipt']['size'];
				$fp = fopen($_FILES['Receipt']['tmp_name'], 'r');
				$Content = fread($fp, $Size);
				$Content = addslashes($Content);
				fclose($fp);
				$SQL = "UPDATE pcreceipts SET name='" . $Name . "',
												type='" . $Type . "',
												size=" . $Size . ",
												content='" . $Content . "'
											WHERE pccashdetail='" . $SelectedIndex . "'";
				$Result = DB_query($SQL);

			}
		}

		prnMsg($Msg, 'success');

	} elseif ($InputError != 1) {

		// First check the type is not being duplicated
		// Add new record on submit
		$SQL = "INSERT INTO pcashdetails (counterindex,
										tabcode,
										date,
										codeexpense,
										amount,
										authorized,
										posted,
										notes)
								VALUES (NULL,
										'" . $_POST['SelectedTabs'] . "',
										'" . FormatDateForSQL($_POST['Date']) . "',
										'" . $_POST['SelectedExpense'] . "',
										'" . -filter_number_format($_POST['Amount']) . "',
										0,
										0,
										'" . $_POST['Notes'] . "'
										)";

		$Msg = _('The Expense Claim on Tab') . ' ' . $_POST['SelectedTabs'] . ' ' . _('has been created');
		$Result = DB_query($SQL);
		$SelectedIndex = DB_Last_Insert_ID('pcashdetails', 'counterindex');

		foreach ($_POST['tag'] as $Tag) {
			$SQL = "INSERT INTO pctags (pccashdetail,
										tag)
									VALUES (
										'" . $SelectedIndex . "',
										'" . $Tag . "'
									)";
			$Result = DB_query($SQL);
		}

		foreach ($_POST as $Index => $Value) {
			if (substr($Index, 0, 5) == 'index') {
				$Index = $Value;
				$SQL = "INSERT INTO pcashdetailtaxes (counterindex,
														pccashdetail,
														calculationorder,
														description,
														taxauthid,
														purchtaxglaccount,
														taxontax,
														taxrate,
														amount
												) VALUES (
														NULL,
														'" . $SelectedIndex . "',
														'" . $_POST['CalculationOrder' . $Index] . "',
														'" . $_POST['Description' . $Index] . "',
														'" . $_POST['TaxAuthority' . $Index] . "',
														'" . $_POST['TaxGLAccount' . $Index] . "',
														'" . $_POST['TaxOnTax' . $Index] . "',
														'" . $_POST['TaxRate' . $Index] . "',
														'" . $_POST['TaxAmount' . $Index] . "'
												)";
				$Result = DB_query($SQL);
			}
		}
		if (isset($_FILES['Receipt']) and $_FILES['Receipt']['name'] != '') {

			$UploadTheFile = 'Yes'; //Assume all is well to start off with
			if ($_FILES['Receipt']['error'] !== 0) {
			}

			//But check for the worst
			if ($_FILES['Receipt']['size'] > ($_SESSION['MaxImageSize'] * 1024)) { //File Size Check
				prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'], 'warn');
				$UploadTheFile = 'No';
			} elseif ($_FILES['Receipt']['type'] != 'image/jpeg' and $_FILES['Receipt']['type'] != 'image/png') { //File Type Check
				prnMsg(_('Only jpg or png files can be uploaded'), 'warn');
				$UploadTheFile = 'No';
			} elseif ($_FILES['Receipt']['error'] == 6) { //upload temp directory check
				prnMsg(_('No tmp directory set. You must have a tmp directory set in your PHP for upload of files.'), 'warn');
				$UploadTheFile = 'No';
			}

			if ($UploadTheFile == 'Yes') {
				$Name = $_FILES['Receipt']['name'];
				$Type = $_FILES['Receipt']['type'];
				$Size = $_FILES['Receipt']['size'];
				$fp = fopen($_FILES['Receipt']['tmp_name'], 'r');
				$Content = fread($fp, $Size);
				$Content = addslashes($Content);
				fclose($fp);
				$SQL = "INSERT INTO pcreceipts VALUES('" . $SelectedIndex . "',
													'" . $Name . "',
													'" . $Type . "',
													" . $Size . ",
													'" . $Content . "'
													)";
				$Result = DB_query($SQL);

			}
		}
		prnMsg($Msg, 'success');
	}

	if ($InputError != 1) {
		unset($_POST['SelectedExpense']);
		unset($_POST['Amount']);
		unset($_POST['Tag']);
		unset($_POST['Date']);
		unset($_POST['Notes']);
		unset($_POST['Receipt']);
	}

} elseif (isset($_GET['delete'])) {

	$SQL = "DELETE FROM pcashdetails
			WHERE counterindex='" . $SelectedIndex . "'";
	$ErrMsg = _('Petty Cash Expense record could not be deleted because');
	$Result = DB_query($SQL, $ErrMsg);

	$SQL = "DELETE FROM pctags
			WHERE pccashdetail='" . $SelectedIndex . "'";
	$Result = DB_query($SQL, $ErrMsg);

	$SQL = "DELETE FROM pcreceipts
			WHERE pccashdetail='" . $SelectedIndex . "'";
	$Result = DB_query($SQL, $ErrMsg);

	prnMsg(_('Petty cash Expense record') . ' ' . $SelectedTabs . ' ' . _('has been deleted'), 'success');

	unset($_GET['delete']);

} //end of get delete
if (!isset($SelectedTabs)) {

	/* It could still be the first time the page has been run and a record has been selected for modification - SelectedTabs will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of sales types will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/money_add.png" title="', _('Payment Entry'), '" alt="" />', ' ', $Title, '
		</p>';

	echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" enctype="multipart/form-data">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<fieldset>
			<legend>', _('Select Tab'), '</legend>';

	$SQL = "SELECT tabcode
		FROM pctabs
		WHERE usercode='" . $_SESSION['UserID'] . "'";
	$Result = DB_query($SQL);
	echo '<field>
			<label for="SelectedTabs">', _('Petty Cash Tabs for User '), $_SESSION['UserID'], ':</label>
			<select required="required" name="SelectedTabs">';
	echo '<option value="">', _('Not Yet Selected'), '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['SelectTabs']) and $MyRow['tabcode'] == $_POST['SelectTabs']) {
			echo '<option selected="selected" value="', $MyRow['tabcode'], '">', $MyRow['tabcode'], '</option>';
		} else {
			echo '<option value="', $MyRow['tabcode'], '">', $MyRow['tabcode'], '</option>';
		}
	} //end while loop
	echo '</select>
		</field>';

	echo '</fieldset>'; // close main table
	echo '<div class="centre">
			<input type="submit" name="Process" value="', _('Accept'), '" />
			<input type="submit" name="Cancel" value="', _('Cancel'), '" />
		</div>';

	echo '</form>';

} else { // isset($SelectedTabs)
	echo '<div class="toplink">
			<a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">', _('Select another tab'), '</a>
		</div>';

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/money_add.png" title="', _('Petty Cash Claim Entry'), '" alt="" />', ' ', $Title, '
		</p>';

	if (!isset($_GET['edit']) or isset($_POST['GO'])) {
		if (!isset($Days)) {
			$Days = 30;
		}

		/* Retrieve decimal places to display */
		$SqlDecimalPlaces = "SELECT decimalplaces
					FROM currencies,pctabs
					WHERE currencies.currabrev = pctabs.currency
						AND tabcode='" . $SelectedTabs . "'";
		$Result = DB_query($SqlDecimalPlaces);
		$MyRow = DB_fetch_array($Result);
		$CurrDecimalPlaces = $MyRow['decimalplaces'];

		echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" enctype="multipart/form-data">';
		echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
		echo '<table>
				<tr>
					<th colspan="11">
						<h3>', _('Petty Cash Tab'), ' ', $SelectedTabs, '</h3>
					</th>
				</tr>
				<tr>
					<th colspan="11">', _('Detail Of Movements For Last '), ':
						<input type="hidden" name="SelectedTabs" value="' . $SelectedTabs . '" />
						<input type="text" class="number" name="Days" value="', $Days, '" required="required" maxlength="4" size="4" /> ', _('Days'), '
						<input type="submit" name="Go" value="', _('Go'), '" />
					</th>
				</tr>';

		if (isset($_POST['Cancel'])) {
			unset($_POST['SelectedExpense']);
			unset($_POST['Amount']);
			unset($_POST['Date']);
			unset($_POST['Notes']);
			unset($_POST['Receipt']);
		}

		$SQL = "SELECT counterindex,
						tabcode,
						date,
						codeexpense,
						amount,
						authorized,
						posted,
						notes,
						receipt
					FROM pcashdetails
					WHERE tabcode='" . $SelectedTabs . "'
						AND date >=DATE_SUB(CURDATE(), INTERVAL " . $Days . " DAY)
					ORDER BY date,
							counterindex ASC";

		$Result = DB_query($SQL);

		echo '<tr>
				<th>', _('Date Of Expense'), '</th>
				<th>', _('Expense Description'), '</th>
				<th>', _('Amount'), '</th>
				<th>', _('Authorised'), '</th>
				<th colspan="2">', _('Taxes'), '</th>
				<th>', _('Tag'), '</th>
				<th>', _('Notes'), '</th>
				<th>', _('Receipt'), '</th>
				<th></th>
				<th></th>
			</tr>';

		while ($MyRow = DB_fetch_array($Result)) {

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

			$SQLDes = "SELECT description
						FROM pcexpenses
						WHERE codeexpense='" . $MyRow['codeexpense'] . "'";

			$ResultDes = DB_query($SQLDes);
			$Description = DB_fetch_array($ResultDes);

			if (!isset($Description['0'])) {
				$Description['0'] = 'ASSIGNCASH';
			}
			if ($MyRow['authorized'] == '0000-00-00') {
				$AuthorisedDate = _('Unauthorised');
			} else {
				$AuthorisedDate = ConvertSQLDate($MyRow['authorized']);
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

			if (($MyRow['authorized'] == '0000-00-00') and ($Description['0'] != 'ASSIGNCASH')) {
				// only movements NOT authorised can be modified or deleted
				echo '<tr class="striped_row">
						<td>', ConvertSQLDate($MyRow['date']), '</td>
						<td>', $Description['0'], '</td>
						<td class="number">', locale_number_format($MyRow['amount'], $CurrDecimalPlaces), '</td>
						<td>', $AuthorisedDate, '</td>
						<td>', $TaxesDescription, '</td>
						<td class="number">', $TaxesTaxAmount, '</td>
						<td>', $TagString, '</td>
						<td>', $MyRow['notes'], '</td>
						<td>', $ReceiptText, '</td>
						<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedIndex=', urlencode($MyRow['counterindex']), '&SelectedTabs=', urlencode($SelectedTabs), '&amp;Days=', urlencode($Days), '&amp;edit=yes">', _('Edit'), '</a></td>
						<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedIndex=', urlencode($MyRow['counterindex']), '&amp;SelectedTabs=', urlencode($SelectedTabs), '&amp;Days=', urlencode($Days), '&amp;delete=yes" onclick="return MakeConfirm(\'', _('Are you sure you wish to delete this code and the expenses it may have set up?'), '\', \'Confirm Delete\', this);">', _('Delete'), '</a></td>
					</tr>';
			} else {
				echo '<tr class="striped_row">
						<td>', ConvertSQLDate($MyRow['date']), '</td>
						<td>', $Description['0'], '</td>
						<td class="number">', locale_number_format($MyRow['amount'], $CurrDecimalPlaces), '</td>
						<td>', $AuthorisedDate, '</td>
						<td>', $TaxesDescription, '</td>
						<td class="number">', $TaxesTaxAmount, '</td>
						<td>', $TagString, '</td>
						<td>', $MyRow['notes'], '</td>
						<td>', $ReceiptText, '</td>
						<td></td>
						<td></td>
					</tr>';
			}

		}
		//END WHILE LIST LOOP
		$SQLAmount = "SELECT sum(amount)
					FROM pcashdetails
					WHERE tabcode='" . $SelectedTabs . "'";

		$ResultAmount = DB_query($SQLAmount);
		$Amount = DB_fetch_array($ResultAmount);

		if (!isset($Amount['0'])) {
			$Amount['0'] = 0;
		}

		echo '<tr class="total_row">
				<td colspan="2" class="number">', _('Current balance'), ':</td>
				<td class="number">', locale_number_format($Amount['0'], $CurrDecimalPlaces), '</td>
				<td colspan=8"></td>
			</tr>';

		echo '</table>';
		echo '</form>';
	}

	if (!isset($_GET['delete'])) {

		echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" enctype="multipart/form-data">';
		echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

		if (isset($_GET['edit'])) {
			$SQL = "SELECT counterindex,
							tabcode,
							date,
							codeexpense,
							amount,
							authorized,
							posted,
							notes,
							receipt
				FROM pcashdetails
				WHERE counterindex='" . $SelectedIndex . "'";

			$Result = DB_query($SQL);
			$MyRow = DB_fetch_array($Result);

			$_POST['Date'] = ConvertSQLDate($MyRow['date']);
			$_POST['SelectedExpense'] = $MyRow['codeexpense'];
			$_POST['Amount'] = - $MyRow['amount'];
			$_POST['Notes'] = $MyRow['notes'];
			$_POST['Receipt'] = $MyRow['receipt'];

			$SQL = "SELECT tag
						FROM pctags
						WHERE pccashdetail='" . $SelectedIndex . "'";
			$Result = DB_query($SQL);
			while ($MyRow = DB_fetch_array($Result)) {
				$TagArray[] = $MyRow['tag'];
			}

			echo '<input type="hidden" name="SelectedTabs" value="', $SelectedTabs, '" />';
			echo '<input type="hidden" name="SelectedIndex" value="', $SelectedIndex, '" />';
			echo '<input type="hidden" name="Days" value="', $Days, '" />';

		} //end of Get Edit
		if (!isset($_POST['Date'])) {
			$_POST['Date'] = Date($_SESSION['DefaultDateFormat']);
		}

		echo '<fieldset>';
		if (isset($_GET['SelectedIndex'])) {
			echo '<legend>', _('Update Expense'), '</legend>';
		} else {
			echo '<legend>', _('New Expense'), '</legend>';
		}
		echo '<field>
					<label for="Date">', _('Date Of Expense'), ':</label>
					<input type="text" class="date" name="Date" size="10" required="required" maxlength="10" value="', $_POST['Date'], '" />
				</field>';

		$SQL = "SELECT pcexpenses.codeexpense,
					pcexpenses.description,
					pctabs.defaulttag
			FROM pctabexpenses, pcexpenses, pctabs
			WHERE pctabexpenses.codeexpense = pcexpenses.codeexpense
				AND pctabexpenses.typetabcode = pctabs.typetabcode
				AND pctabs.tabcode = '" . $SelectedTabs . "'
			ORDER BY pcexpenses.codeexpense ASC";
		$Result = DB_query($SQL);
		echo '<field>
				<label for="SelectedExpense">', _('Code Of Expense'), ':</label>
				<select required="required" name="SelectedExpense">
					<option value="">', _('Not Yet Selected'), '</option>';
		while ($MyRow = DB_fetch_array($Result)) {
			if (isset($_POST['SelectedExpense']) and $MyRow['codeexpense'] == $_POST['SelectedExpense']) {
				echo '<option selected="selected" value="', $MyRow['codeexpense'], '">', $MyRow['codeexpense'], ' - ', $MyRow['description'], '</option>';
			} else {
				echo '<option value="', $MyRow['codeexpense'], '">', $MyRow['codeexpense'], ' - ', $MyRow['description'], '</option>';
			}
			$DefaultTag = $MyRow['defaulttag'];

		} //end while loop
		echo '</select>
			</field>';

		//Select the tag
		$SQL = "SELECT tagref,
						tagdescription
				FROM tags
				ORDER BY tagref";
		$Result = DB_query($SQL);
		echo '<field>
				<label for="tag">', _('Tag'), '</label>
				<select multiple="multiple" name="tag[]">';
		echo '<option value="0">0 - ' . _('None') . '</option>';
		while ($MyRow = DB_fetch_array($Result)) {
			if (isset($_POST['tag']) and in_array($MyRow['tagref'], $TagArray)) {
				echo '<option selected="selected" value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
			} else {
				echo '<option value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
			}
		}
		echo '</select>
			</field>';

		// 	End select tag
		if (!isset($_POST['Amount'])) {
			$_POST['Amount'] = 0;
		}
		echo '<field>
				<label for="Amount">', _('Gross Amount Claimed'), ':</label>
				<input type="text" class="number" name="Amount" size="12" required="required" maxlength="11" value="', $_POST['Amount'], '" />
			</field>';
		$i = 0;
		if (isset($_GET['edit'])) {
			$SQL = "SELECT counterindex,
							pccashdetail,
							calculationorder,
							description,
							taxauthid,
							purchtaxglaccount,
							taxontax,
							taxrate,
							amount
						FROM pcashdetailtaxes
						WHERE pccashdetail='" . $SelectedIndex . "'";
			$TaxesResult = DB_query($SQL);
			while ($MyTaxRow = DB_fetch_array($TaxesResult)) {
				echo '<input type="hidden" name="index', $MyTaxRow['counterindex'], '" value="', $MyTaxRow['counterindex'], '" />';
				echo '<input type="hidden" name="PcCashDetail', $MyTaxRow['counterindex'], '" value="', $MyTaxRow['pccashdetail'], '" />';
				echo '<input type="hidden" name="CalculationOrder', $MyTaxRow['counterindex'], '" value="', $MyTaxRow['calculationorder'], '" />';
				echo '<input type="hidden" name="Description', $MyTaxRow['counterindex'], '" value="', $MyTaxRow['description'], '" />';
				echo '<input type="hidden" name="TaxAuthority', $MyTaxRow['counterindex'], '" value="', $MyTaxRow['taxauthid'], '" />';
				echo '<input type="hidden" name="TaxGLAccount', $MyTaxRow['counterindex'], '" value="', $MyTaxRow['purchtaxglaccount'], '" />';
				echo '<input type="hidden" name="TaxOnTax', $MyTaxRow['counterindex'], '" value="', $MyTaxRow['taxontax'], '" />';
				echo '<input type="hidden" name="TaxRate', $MyTaxRow['counterindex'], '" value="', $MyTaxRow['taxrate'], '" />';
				echo '<field>
						<label for="TaxAmount">', $MyTaxRow['description'], ' - ', ($MyTaxRow['taxrate'] * 100), '%</label>
						<input type="text" class="number" size="12" name="TaxAmount', $MyTaxRow['counterindex'], '" value="', $MyTaxRow['amount'], '" />
					</field>';
			}
		} else {

			$SQL = "SELECT taxgrouptaxes.calculationorder,
							taxauthorities.description,
							taxgrouptaxes.taxauthid,
							taxauthorities.purchtaxglaccount,
							taxgrouptaxes.taxontax,
							taxauthrates.taxrate
						FROM taxauthrates
						INNER JOIN taxgrouptaxes
							ON taxauthrates.taxauthority=taxgrouptaxes.taxauthid
						INNER JOIN taxauthorities
							ON taxauthrates.taxauthority=taxauthorities.taxid
						INNER JOIN taxgroups
							ON taxgroups.taxgroupid=taxgrouptaxes.taxgroupid
						INNER JOIN pctabs
							ON pctabs.taxgroupid=taxgroups.taxgroupid
						WHERE taxauthrates.taxcatid = " . $_SESSION['DefaultTaxCategory'] . "
							AND pctabs.tabcode='" . $SelectedTabs . "'
						ORDER BY taxgrouptaxes.calculationorder";
			$TaxResult = DB_query($SQL);
			while ($MyTaxRow = DB_fetch_array($TaxResult)) {
				echo '<input type="hidden" name="index', $i, '" value="', $i, '" />';
				echo '<input type="hidden" name="CalculationOrder', $i, '" value="', $MyTaxRow['calculationorder'], '" />';
				echo '<input type="hidden" name="Description', $i, '" value="', $MyTaxRow['description'], '" />';
				echo '<input type="hidden" name="TaxAuthority', $i, '" value="', $MyTaxRow['taxauthid'], '" />';
				echo '<input type="hidden" name="TaxGLAccount', $i, '" value="', $MyTaxRow['purchtaxglaccount'], '" />';
				echo '<input type="hidden" name="TaxOnTax', $i, '" value="', $MyTaxRow['taxontax'], '" />';
				echo '<input type="hidden" name="TaxRate', $i, '" value="', $MyTaxRow['taxrate'], '" />';
				echo '<field>
						<label for="TaxAmount">', $MyTaxRow['description'], ' - ', ($MyTaxRow['taxrate'] * 100), '%</label>
						<input type="text" class="number" size="12" name="TaxAmount', $i, '" value="0" />
					</field>';
				++$i;
			}
		}

		if (!isset($_POST['Notes'])) {
			$_POST['Notes'] = '';
		}

		echo '<field>
				<label for="Notes">', _('Notes'), ':</label>
				<input type="text" name="Notes" size="50" maxlength="49" value="', $_POST['Notes'], '" />
			</field>';

		if (!isset($_POST['Receipt'])) {
			$_POST['Receipt'] = '';
		}

		echo '<field>
				<label for="Receipt">', _('Receipt'), ':</label>
				<input type="file" name="Receipt" id="Receipt" />
			</field>';

		echo '</fieldset>'; // close main table
		echo '<input type="hidden" name="SelectedTabs" value="', $SelectedTabs, '" />';
		echo '<input type="hidden" name="Days" value="', $Days, '" />';

		echo '<div class="centre">
				<input type="submit" name="submit" value="', _('Accept'), '" />
				<input type="submit" name="Cancel" value="', _('Cancel'), '" />
			</div>';

		echo '</form>';

	} // end if user wish to delete
	
}

include ('includes/footer.php');
?>