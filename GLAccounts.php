<?php

include('includes/session.inc');
$Title = _('Chart of Accounts Maintenance');

$ViewTopic = 'GeneralLedger';
$BookMark = 'GLAccounts';

include('includes/header.inc');

if (isset($_POST['SelectedAccount'])) {
	$SelectedAccount = $_POST['SelectedAccount'];
} elseif (isset($_GET['SelectedAccount'])) {
	$SelectedAccount = $_GET['SelectedAccount'];
}

echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/transactions.png" title="' . _('General Ledger Accounts') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['submit'])) {

	foreach ($_POST as $Key=>$Value) {
		if (mb_substr($Key, 0, 11) == 'AccountName') {
			$AccountNames[mb_substr($Key, -5) . '.utf8'] = $Value;
		}
	}

	$GroupSQL = "SELECT groupname
					FROM accountgroups
					WHERE groupcode='" . $_POST['Group'] . "'
					AND language='" . $_SESSION['ChartLanguage'] . "'";
	$GroupResult = DB_query($GroupSQL);
	$GroupRow = DB_fetch_array($GroupResult);
	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (mb_strlen($_POST['AccountName']) > 150) {
		$InputError = 1;
		prnMsg(_('The account name must be one hundred and fifty characters or less long'), 'warn');
	}

	if (isset($SelectedAccount) and $InputError != 1) {

		foreach ($AccountNames as $AccountLanguage=>$AccountName) {
			$GroupNameSQL = "SELECT groupname
								FROM accountgroups
								WHERE language='" . $AccountLanguage . "'
									AND groupcode='" . $_POST['Group'] . "'";
			$GroupNameResult = DB_query($GroupNameSQL);
			$GroupNameRow = DB_fetch_array($GroupNameResult);
			$SQL = "UPDATE chartmaster SET accountname='" . htmlspecialchars($AccountName) . "',
											group_='" . htmlspecialchars($GroupNameRow['groupname']) . "',
											groupcode='" . $_POST['Group'] . "',
											language='" . $AccountLanguage . "'
										WHERE accountcode ='" . $SelectedAccount . "'";

			$ErrMsg = _('Could not update the account because');
			$Result = DB_query($SQL, $ErrMsg);
			prnMsg(_('The general ledger account has been updated'), 'success');
		}
	} elseif ($InputError != 1) {

		/*SelectedAccount is null cos no item selected on first time round so must be adding a	record must be submitting new entries */

		foreach ($AccountNames as $AccountLanguage=>$AccountName) {
			$GroupNameSQL = "SELECT groupname
								FROM accountgroups
								WHERE language='" . $AccountLanguage . "'
									AND groupcode='" . $_POST['Group'] . "'";
			$GroupNameResult = DB_query($GroupNameSQL);
			$GroupNameRow = DB_fetch_array($GroupNameResult);
			$ErrMsg = _('Could not add the new account code');
			$SQL = "INSERT INTO chartmaster (accountcode,
											language,
											accountname,
											group_,
											groupcode)
										VALUES (
											'" . $_POST['AccountCode'] . "',
											'" . $AccountLanguage . "',
											'" . $AccountName . "',
											'" . htmlspecialchars($GroupNameRow['groupname']) . "',
											'" . $_POST['Group'] . "'
										)";
			$Result = DB_query($SQL, $ErrMsg);

			prnMsg(_('The new general ledger account has been added'), 'success');
		}
	}

	unset($_POST['Group']);
	unset($_POST['AccountCode']);
	unset($_POST['AccountName']);
	unset($SelectedAccount);

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'ChartDetails'

	$SQL = "SELECT COUNT(*)
			FROM chartdetails
			WHERE chartdetails.accountcode ='" . $SelectedAccount . "'
			AND chartdetails.actual <>0";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this account because chart details have been created using this account and at least one period has postings to it.') . ' ' . _('There are') . ' ' . $MyRow[0] . ' ' . _('chart details that require this account code'), 'warn');
	} else {
		// PREVENT DELETES IF DEPENDENT RECORDS IN 'GLTrans'
		$SQL = "SELECT COUNT(*)
				FROM gltrans
				WHERE gltrans.account ='" . $SelectedAccount . "'";

		$ErrMsg = _('Could not test for existing transactions because');

		$Result = DB_query($SQL, $ErrMsg);

		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0] > 0) {
			$CancelDelete = 1;
			prnMsg(_('Cannot delete this account because transactions have been created using this account.') . ' ' . '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('transactions that require this account code'), 'warn');
		} else {
			//PREVENT DELETES IF Company default accounts set up to this account
			$SQL = "SELECT COUNT(*) FROM companies
					WHERE debtorsact='" . $SelectedAccount . "'
					OR pytdiscountact='" . $SelectedAccount . "'
					OR creditorsact='" . $SelectedAccount . "'
					OR payrollact='" . $SelectedAccount . "'
					OR grnact='" . $SelectedAccount . "'
					OR exchangediffact='" . $SelectedAccount . "'
					OR purchasesexchangediffact='" . $SelectedAccount . "'
					OR retainedearnings='" . $SelectedAccount . "'";


			$ErrMsg = _('Could not test for default company GL codes because');

			$Result = DB_query($SQL, $ErrMsg);

			$MyRow = DB_fetch_row($Result);
			if ($MyRow[0] > 0) {
				$CancelDelete = 1;
				prnMsg(_('Cannot delete this account because it is used as one of the company default accounts'), 'warn');

			} else {
				//PREVENT DELETES IF Company default accounts set up to this account
				$SQL = "SELECT COUNT(*) FROM taxauthorities
					WHERE taxglcode='" . $SelectedAccount . "'
					OR purchtaxglaccount ='" . $SelectedAccount . "'";

				$ErrMsg = _('Could not test for tax authority GL codes because');
				$Result = DB_query($SQL, $ErrMsg);

				$MyRow = DB_fetch_row($Result);
				if ($MyRow[0] > 0) {
					$CancelDelete = 1;
					prnMsg(_('Cannot delete this account because it is used as one of the tax authority accounts'), 'warn');
				} else {
					//PREVENT DELETES IF SALES POSTINGS USE THE GL ACCOUNT
					$SQL = "SELECT COUNT(*) FROM salesglpostings
						WHERE salesglcode='" . $SelectedAccount . "'
						OR discountglcode='" . $SelectedAccount . "'";

					$ErrMsg = _('Could not test for existing sales interface GL codes because');

					$Result = DB_query($SQL, $ErrMsg);

					$MyRow = DB_fetch_row($Result);
					if ($MyRow[0] > 0) {
						$CancelDelete = 1;
						prnMsg(_('Cannot delete this account because it is used by one of the sales GL posting interface records'), 'warn');
					} else {
						//PREVENT DELETES IF COGS POSTINGS USE THE GL ACCOUNT
						$SQL = "SELECT COUNT(*)
								FROM cogsglpostings
								WHERE glcode='" . $SelectedAccount . "'";

						$ErrMsg = _('Could not test for existing cost of sales interface codes because');

						$Result = DB_query($SQL, $ErrMsg);

						$MyRow = DB_fetch_row($Result);
						if ($MyRow[0] > 0) {
							$CancelDelete = 1;
							prnMsg(_('Cannot delete this account because it is used by one of the cost of sales GL posting interface records'), 'warn');

						} else {
							//PREVENT DELETES IF STOCK POSTINGS USE THE GL ACCOUNT
							$SQL = "SELECT COUNT(*) FROM stockcategory
									WHERE stockact='" . $SelectedAccount . "'
									OR adjglact='" . $SelectedAccount . "'
									OR purchpricevaract='" . $SelectedAccount . "'
									OR materialuseagevarac='" . $SelectedAccount . "'
									OR wipact='" . $SelectedAccount . "'";

							$Errmsg = _('Could not test for existing stock GL codes because');

							$Result = DB_query($SQL, $ErrMsg);

							$MyRow = DB_fetch_row($Result);
							if ($MyRow[0] > 0) {
								$CancelDelete = 1;
								prnMsg(_('Cannot delete this account because it is used by one of the stock GL posting interface records'), 'warn');
							} else {
								//PREVENT DELETES IF STOCK POSTINGS USE THE GL ACCOUNT
								$SQL = "SELECT COUNT(*) FROM bankaccounts
								WHERE accountcode='" . $SelectedAccount . "'";
								$ErrMsg = _('Could not test for existing bank account GL codes because');

								$Result = DB_query($SQL, $ErrMsg);

								$MyRow = DB_fetch_row($Result);
								if ($MyRow[0] > 0) {
									$CancelDelete = 1;
									prnMsg(_('Cannot delete this account because it is used by one the defined bank accounts'), 'warn');
								} else {

									$SQL = "DELETE FROM chartdetails WHERE accountcode='" . $SelectedAccount . "'";
									$Result = DB_query($SQL);
									$SQL = "DELETE FROM chartmaster WHERE accountcode= '" . $SelectedAccount . "'";
									$Result = DB_query($SQL);
									prnMsg(_('Account') . ' ' . $SelectedAccount . ' ' . _('has been deleted'), 'succes');
								}
							}
						}
					}
				}
			}
		}
	}
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" id="GLAccounts" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	if (isset($SelectedAccount)) {
		//editing an existing account

		$SQL = "SELECT accountcode,
						language,
						accountname,
						groupcode
					FROM chartmaster
					WHERE accountcode='" . $SelectedAccount . "'";

		$Result = DB_query($SQL);
		while ($MyRow = DB_fetch_array($Result)) {
			$_POST['AccountCode'] = $MyRow['accountcode'];
			$AccountName[$MyRow['language']] = $MyRow['accountname'];
			$_POST['Group'] = $MyRow['groupcode'];
		}

		echo '<input type="hidden" name="SelectedAccount" value="', $SelectedAccount, '" />';
		echo '<input type="hidden" name="AccountCode" value="', $_POST['AccountCode'], '" />';
		echo '<table class="selection">
				<tr>
					<td>', _('Account Code'), ':</td>
					<td>', $_POST['AccountCode'], '</td>
				</tr>';
		$SQL = "SELECT DISTINCT language FROM chartmaster";
		$LanguageResult = DB_query($SQL);
		while ($LanguageRow = DB_fetch_array($LanguageResult)) {
			if (!isset($AccountName[$LanguageRow['language']])) {
				$AccountName[$LanguageRow['language']] = '';
			}
			echo '<tr>
					<td>', _('Account Name'), ' (', $LanguagesArray[$LanguageRow['language']]['LanguageName'], ') :</td>
					<td><input type="text" size="51" autofocus="autofocus" required="required" maxlength="150" name="AccountName' . mb_substr($LanguageRow['language'], 0, 5) . '" value="', $AccountName[$LanguageRow['language']], '" /></td>
				</tr>';
		}
	} else {
		echo '<table class="selection">';
		echo '<tr>
				<td>', _('Account Code'), ':</td>
				<td><input type="text" name="AccountCode" size="11" autofocus="autofocus" required="required" maxlength="20" /></td>
			</tr>';
		$SQL = "SELECT DISTINCT language FROM chartmaster";
		$LanguageResult = DB_query($SQL);
		while ($LanguageRow = DB_fetch_array($LanguageResult)) {
			echo '<tr>
					<td>', _('Account Name'), ' (', $LanguagesArray[$LanguageRow['language']]['LanguageName'], ') :</td>
					<td><input type="text" size="51" autofocus="autofocus" required="required" maxlength="150" name="AccountName' . mb_substr($LanguageRow['language'], 0, 5) . '" value="" /></td>
				</tr>';
		}
	}

	if (!isset($_POST['AccountName'])) {
		$_POST['AccountName'] = '';
	}

	$SQL = "SELECT groupcode, groupname FROM accountgroups WHERE language='" . $_SESSION['ChartLanguage'] . "' ORDER BY sequenceintb";
	$Result = DB_query($SQL);

	echo '<tr>
			<td>', _('Account Group'), ':</td>
			<td><select required="required" name="Group">';

	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['Group']) and $MyRow['groupcode'] == $_POST['Group']) {
			echo '<option selected="selected" value="', $MyRow['groupcode'], '">', $MyRow['groupcode'] . ' - ' . $MyRow['groupname'], '</option>';
		} else {
			echo '<option value="', $MyRow['groupcode'], '">', $MyRow['groupcode'] . ' - ' . $MyRow['groupname'], '</option>';
		}
	}
	echo '</select>
				</td>
			</tr>
		</table>';

	echo '<div class="centre">
			<input type="submit" name="submit" value="', _('Enter Information'), '" />
		</div>';
	echo '</form>';

} //end if record deleted no point displaying form to add record


if (!isset($SelectedAccount)) {
	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedAccount will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of ChartMaster will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/

	$SQL = "SELECT accountcode,
					accountname,
					group_,
					chartmaster.groupcode,
					CASE WHEN pandl=0
						THEN '" . _('Balance Sheet') . "'
						ELSE '" . _('Profit/Loss') . "'
					END AS acttype
				FROM chartmaster
				INNER JOIN accountgroups
					ON chartmaster.groupcode=accountgroups.groupcode
					AND chartmaster.language=accountgroups.language
				WHERE chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
				ORDER BY chartmaster.accountcode";

	$ErrMsg = _('The chart accounts could not be retrieved because');

	$Result = DB_query($SQL, $ErrMsg);

	echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">', _('Account Code'), '</th>
					<th class="SortedColumn">', _('Account Name'), '</th>
					<th class="SortedColumn">', _('Account Group Code'), '</th>
					<th class="SortedColumn">', _('Account Group Name'), '</th>
					<th class="SortedColumn">', _('P/L or B/S'), '</th>
					<th colspan="2"></th>
				</tr>
			</thead>';

	$k = 0; //row colour counter
	echo '<tbody>';
	while ($MyRow = DB_fetch_array($Result)) {
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}


		echo '<td>', $MyRow['accountcode'],'</td>
			<td>', $MyRow['accountname'], '</td>
			<td>', $MyRow['groupcode'], '</td>
			<td>', $MyRow['group_'], '</td>
			<td>', $MyRow['acttype'], '</td>
			<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?', '&amp;SelectedAccount=', urlencode($MyRow['accountcode']), '">', _('Edit'), '</a></td>
			<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?', '&amp;SelectedAccount=', urlencode($MyRow['accountcode']), '&amp;delete=1" onclick="return MakeConfirm("', _('Are you sure you wish to delete this account? Additional checks will be performed in any event to ensure data integrity is not compromised.'), '", \'Confirm Delete\', this);">', _('Delete'), '</a></td>
		</tr>';

	}
	//END WHILE LIST LOOP
	echo '</tbody>';
	echo '</table>';
} //END IF selected ACCOUNT

if (isset($SelectedAccount)) {
	echo '<div class="centre">
			<a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">', _('Show All Accounts'), '</a>
		</div>';
}

include('includes/footer.inc');
?>