<?php

include('includes/session.inc');
$Title = _('Claim Petty Cash Expenses From Tab');
/* Manual links before header.inc */
$ViewTopic = 'PettyCash';
$BookMark = 'ExpenseClaim';
include('includes/header.inc');


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

	if (isset($SelectedIndex) and $InputError != 1) {
		$SQL = "UPDATE pcashdetails
			SET date = '" . FormatDateForSQL($_POST['Date']) . "',
			codeexpense = '" . $_POST['SelectedExpense'] . "',
			amount = '" . -filter_number_format($_POST['Amount']) . "',
			notes = '" . $_POST['Notes'] . "',
			receipt = '" . $_POST['Receipt'] . "'
			WHERE counterindex = '" . $SelectedIndex . "'";

		$Msg = _('The Expense Claim on Tab') . ' ' . $SelectedTabs . ' ' . _('has been updated');

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
										notes,
										receipt)
								VALUES (NULL,
										'" . $_POST['SelectedTabs'] . "',
										'" . FormatDateForSQL($_POST['Date']) . "',
										'" . $_POST['SelectedExpense'] . "',
										'" . -filter_number_format($_POST['Amount']) . "',
										0,
										0,
										'" . $_POST['Notes'] . "',
										'" . $_POST['Receipt'] . "'
										)";

		$Msg = _('The Expense Claim on Tab') . ' ' . $_POST['SelectedTabs'] . ' ' . _('has been created');
	}

	if ($InputError != 1) {
		//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);
		prnMsg($Msg, 'success');

		unset($_POST['SelectedExpense']);
		unset($_POST['Amount']);
		unset($_POST['Date']);
		unset($_POST['Notes']);
		unset($_POST['Receipt']);
	}

} elseif (isset($_GET['delete'])) {

	$SQL = "DELETE FROM pcashdetails
			WHERE counterindex='" . $SelectedIndex . "'";
	$ErrMsg = _('Petty Cash Expense record could not be deleted because');
	$Result = DB_query($SQL, $ErrMsg);
	prnMsg(_('Petty cash Expense record') . ' ' . $SelectedTabs . ' ' . _('has been deleted'), 'success');

	unset($_GET['delete']);

} //end of get delete

if (!isset($SelectedTabs)) {

	/* It could still be the first time the page has been run and a record has been selected for modification - SelectedTabs will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of sales types will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/
	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/money_add.png" title="' . _('Payment Entry') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">'; //Main table

	echo '<tr><td>' . _('Petty Cash Tabs for User ') . $_SESSION['UserID'] . ':</td>
				<td><select required="required" name="SelectedTabs">';

	$SQL = "SELECT tabcode
		FROM pctabs
		WHERE usercode='" . $_SESSION['UserID'] . "'";

	$Result = DB_query($SQL);
	echo '<option value="">' . _('Not Yet Selected') . '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['SelectTabs']) and $MyRow['tabcode'] == $_POST['SelectTabs']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $MyRow['tabcode'] . '">' . $MyRow['tabcode'] . '</option>';

	} //end while loop

	echo '</select></td></tr>';
	echo '</table>'; // close main table
	DB_free_result($Result);

	echo '<div class="centre">
			<input type="submit" name="Process" value="' . _('Accept') . '" />
			<input type="submit" name="Cancel" value="' . _('Cancel') . '" />
		</div>';
	echo '</form>';

} else { // isset($SelectedTabs)

	echo '<div class="toplink"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Select another tab') . '</a></div>';

	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/money_add.png" title="' . _('Petty Cash Claim Entry') . '" alt="" />
		 ' . ' ' . $Title . '</p>';

	if (!isset($_GET['edit']) or isset($_POST['GO'])) {
		echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<table class="selection">';
		echo '<tr>
				<th colspan="8"><h3>' . _('Petty Cash Tab') . ' ' . $SelectedTabs . '</h3></th>
			</tr>
			<tr>
				<th colspan="8">' . _('Detail Of Movements For Last ') . ': ';


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

		echo '<input type="hidden" name="SelectedTabs" value="' . $SelectedTabs . '" />';
		echo '<input type="text" class="number" name="Days" value="' . $Days . '" required="required" maxlength="3" size="4" /> ' . _('Days');
		echo '<input type="submit" name="Go" value="' . _('Go') . '" />';
		echo '</th></tr>';

		if (isset($_POST['Cancel'])) {
			unset($_POST['SelectedExpense']);
			unset($_POST['Amount']);
			unset($_POST['Date']);
			unset($_POST['Notes']);
			unset($_POST['Receipt']);
		}

		$SQL = "SELECT * FROM pcashdetails
				WHERE tabcode='" . $SelectedTabs . "'
					AND date >=DATE_SUB(CURDATE(), INTERVAL " . $Days . " DAY)
				ORDER BY date, counterindex ASC";

		$Result = DB_query($SQL);

		echo '<tr>
				<th>' . _('Date Of Expense') . '</th>
				<th>' . _('Expense Description') . '</th>
				<th>' . _('Amount') . '</th>
				<th>' . _('Authorised') . '</th>
				<th>' . _('Notes') . '</th>
				<th>' . _('Receipt') . '</th>
			</tr>';

		$k = 0; //row colour counter

		while ($MyRow = DB_fetch_row($Result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}

			$SQLDes = "SELECT description
						FROM pcexpenses
						WHERE codeexpense='" . $MyRow['3'] . "'";

			$ResultDes = DB_query($SQLDes);
			$Description = DB_fetch_array($ResultDes);

			if (!isset($Description['0'])) {
				$Description['0'] = 'ASSIGNCASH';
			}
			if ($MyRow['5'] == '0000-00-00') {
				$AuthorisedDate = _('Unauthorised');
			} else {
				$AuthorisedDate = ConvertSQLDate($MyRow['5']);
			}
			if (($MyRow['5'] == '0000-00-00') and ($Description['0'] != 'ASSIGNCASH')) {
				// only movements NOT authorised can be modified or deleted
				printf('<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td><a href="%sSelectedIndex=%s&amp;SelectedTabs=' . $SelectedTabs . '&amp;Days=' . $Days . '&amp;edit=yes">' . _('Edit') . '</a></td>
						<td><a href="%sSelectedIndex=%s&amp;SelectedTabs=' . $SelectedTabs . '&amp;Days=' . $Days . '&amp;delete=yes" onclick=\'return MakeConfirm("' . _('Are you sure you wish to delete this code and the expenses it may have set up?') . '", \'Confirm Delete\', this);\'>' . _('Delete') . '</a></td>
					</tr>', ConvertSQLDate($MyRow['2']), $Description['0'], locale_number_format($MyRow['4'], $CurrDecimalPlaces), $AuthorisedDate, $MyRow['7'], $MyRow['8'], htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?', $MyRow['0'], htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?', $MyRow['0']);
			} else {
				printf('<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
					</tr>', ConvertSQLDate($MyRow['2']), $Description['0'], locale_number_format($MyRow['4'], $CurrDecimalPlaces), $AuthorisedDate, $MyRow['7'], $MyRow['8']);

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

		echo '<tr>
				<td colspan="2" class="number">' . _('Current balance') . ':</td>
				<td class="number">' . locale_number_format($Amount['0'], $CurrDecimalPlaces) . '</td>
			</tr>';


		echo '</table>';
		echo '</form>';
	}

	if (!isset($_GET['delete'])) {

		echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

		if (isset($_GET['edit'])) {
			$SQL = "SELECT *
				FROM pcashdetails
				WHERE counterindex='" . $SelectedIndex . "'";

			$Result = DB_query($SQL);
			$MyRow = DB_fetch_array($Result);

			$_POST['Date'] = ConvertSQLDate($MyRow['date']);
			$_POST['SelectedExpense'] = $MyRow['codeexpense'];
			$_POST['Amount'] = -$MyRow['amount'];
			$_POST['Notes'] = $MyRow['notes'];
			$_POST['Receipt'] = $MyRow['receipt'];

			echo '<input type="hidden" name="SelectedTabs" value="' . $SelectedTabs . '" />';
			echo '<input type="hidden" name="SelectedIndex" value="' . $SelectedIndex . '" />';
			echo '<input type="hidden" name="Days" value="' . $Days . '" />';

		} //end of Get Edit

		if (!isset($_POST['Date'])) {
			$_POST['Date'] = Date($_SESSION['DefaultDateFormat']);
		}

		echo '<table class="selection">'; //Main table
		echo '<tr>
				<td>' . _('Date Of Expense') . ':</td>
				<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="Date" size="10" required="required" maxlength="10" value="' . $_POST['Date'] . '" /></td>
			</tr>';
		echo '<tr>
				<td>' . _('Code Of Expense') . ':</td><td><select required="required" name="SelectedExpense">';

		DB_free_result($Result);

		$SQL = "SELECT pcexpenses.codeexpense,
					pcexpenses.description
			FROM pctabexpenses, pcexpenses, pctabs
			WHERE pctabexpenses.codeexpense = pcexpenses.codeexpense
				AND pctabexpenses.typetabcode = pctabs.typetabcode
				AND pctabs.tabcode = '" . $SelectedTabs . "'
			ORDER BY pcexpenses.codeexpense ASC";

		$Result = DB_query($SQL);
		echo '<option value="">' . _('Not Yet Selected') . '</option>';
		while ($MyRow = DB_fetch_array($Result)) {
			if (isset($_POST['SelectedExpense']) and $MyRow['codeexpense'] == $_POST['SelectedExpense']) {
				echo '<option selected="selected" value="';
			} else {
				echo '<option value="';
			}
			echo $MyRow['codeexpense'] . '">' . $MyRow['codeexpense'] . ' - ' . $MyRow['description'] . '</option>';

		} //end while loop

		echo '</select></td></tr>';

	//Select the tag
	echo '<tr>
			<td>' . _('Tag') . ':</td>
			<td><select name="Tag">';

	$SQL = "SELECT tagref,
					tagdescription
			FROM tags
			ORDER BY tagref";

	$Result = DB_query($SQL);
	echo '<option value="0">0 - ' . _('None') . '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['Tag']) and $_POST['Tag'] == $MyRow['tagref']) {
			echo '<option selected="selected" value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
		}
	}
	echo '</select></td></tr>';
	// End select tag


		if (!isset($_POST['Amount'])) {
			$_POST['Amount'] = 0;
		}

		echo '<tr>
				<td>' . _('Amount') . ':</td>
				<td><input type="text" class="number" name="Amount" size="12" required="required" maxlength="11" value="' . $_POST['Amount'] . '" /></td>
			</tr>';

		if (!isset($_POST['Notes'])) {
			$_POST['Notes'] = '';
		}

		echo '<tr><td>' . _('Notes') . ':</td>
				<td><input type="text" name="Notes" size="50" maxlength="49" value="' . $_POST['Notes'] . '" /></td></tr>';

		if (!isset($_POST['Receipt'])) {
			$_POST['Receipt'] = '';
		}

		echo '<tr>
				<td>' . _('Receipt') . ':</td>
				<td><input type="text" name="Receipt" size="50" maxlength="49" value="' . $_POST['Receipt'] . '" /></td>
			</tr>';
		echo '</table>'; // close main table

		echo '<input type="hidden" name="SelectedTabs" value="' . $SelectedTabs . '" />';
		echo '<input type="hidden" name="Days" value="' . $Days . '" />';

		echo '<div class="centre"><input type="submit" name="submit" value="' . _('Accept') . '" /><input type="submit" name="Cancel" value="' . _('Cancel') . '" /></div>';
		echo '</form>';

	} // end if user wish to delete

}

include('includes/footer.inc');
?>