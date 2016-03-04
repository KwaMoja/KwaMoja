<?php

include('includes/session.inc');
$Title = _('Sales Types') . ' / ' . _('Price List Maintenance');
include('includes/header.inc');

if (isset($_POST['SelectedType'])) {
	$SelectedType = mb_strtoupper($_POST['SelectedType']);
} elseif (isset($_GET['SelectedType'])) {
	$SelectedType = mb_strtoupper($_GET['SelectedType']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i = 1;

	if (mb_strlen(stripslashes($_POST['TypeAbbrev'])) > 2) {
		$InputError = 1;
		prnMsg(_('The sales type (price list) code must be two characters or less long'), 'error');
		$Errors[$i] = 'SalesType';
		++$i;
	} elseif ($_POST['TypeAbbrev'] == '' or $_POST['TypeAbbrev'] == ' ' or $_POST['TypeAbbrev'] == '  ') {
		$InputError = 1;
		prnMsg(_('The sales type (price list) code cannot be an empty string or spaces'), 'error');
		$Errors[$i] = 'SalesType';
		++$i;
	} elseif (trim($_POST['Sales_Type']) == '') {
		$InputError = 1;
		prnMsg(_('The sales type (price list) description cannot be empty'), 'error');
		$Errors[$i] = 'SalesType';
		++$i;
	} elseif (mb_strlen($_POST['Sales_Type']) > 40) {
		$InputError = 1;
		echo prnMsg(_('The sales type (price list) description must be forty characters or less long'), 'error');
		$Errors[$i] = 'SalesType';
		++$i;
	} elseif ($_POST['TypeAbbrev'] == 'AN') {
		$InputError = 1;
		prnMsg(_('The sales type code cannot be AN since this is a system defined abbreviation for any sales type in general ledger interface lookups'), 'error');
		$Errors[$i] = 'SalesType';
		++$i;
	}

	if (isset($SelectedType) and $InputError != 1) {

		$SQL = "UPDATE salestypes
			SET sales_type = '" . $_POST['Sales_Type'] . "'
			WHERE typeabbrev = '" . stripslashes($SelectedType) . "'";

		$Msg = _('The customer/sales/pricelist type') . ' ' . stripslashes($SelectedType) . ' ' . _('has been updated');
	} elseif ($InputError != 1) {

		// First check the type is not being duplicated

		$CheckSql = "SELECT count(*)
				 FROM salestypes
				 WHERE typeabbrev = '" . $_POST['TypeAbbrev'] . "'";

		$CheckResult = DB_query($CheckSql);
		$CheckRow = DB_fetch_row($CheckResult);

		if ($CheckRow[0] > 0) {
			$InputError = 1;
			prnMsg(_('The customer/sales/pricelist type ') . $_POST['TypeAbbrev'] . _(' already exist.'), 'error');
		} else {

			// Add new record on submit

			$SQL = "INSERT INTO salestypes (typeabbrev,
											sales_type)
							VALUES ('" . str_replace(' ', '', $_POST['TypeAbbrev']) . "',
									'" . $_POST['Sales_Type'] . "')";
			$Msg = _('Customer/sales/pricelist type') . ' ' . stripslashes($_POST['Sales_Type']) . ' ' . _('has been created');
		}
	}

	if ($InputError != 1) {
		//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);

		// Check the default price list exists
		$CheckSql = "SELECT count(*)
				 FROM salestypes
				 WHERE typeabbrev = '" . $_SESSION['DefaultPriceList'] . "'";
		$CheckResult = DB_query($CheckSql);
		$CheckRow = DB_fetch_row($CheckResult);

		// If it doesnt then update config with newly created one.
		if ($CheckRow[0] == 0) {
			$SQL = "UPDATE config
					SET confvalue='" . $_POST['TypeAbbrev'] . "'
					WHERE confname='DefaultPriceList'";
			$Result = DB_query($SQL);
			$_SESSION['DefaultPriceList'] = $_POST['TypeAbbrev'];
		}

		prnMsg($Msg, 'success');

		unset($SelectedType);
		unset($_POST['TypeAbbrev']);
		unset($_POST['Sales_Type']);
	}

} elseif (isset($_GET['delete'])) {

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorTrans'
	// Prevent delete if saletype exist in customer transactions

	$SQL = "SELECT COUNT(*)
		   FROM debtortrans
		   WHERE debtortrans.tpe='" . $SelectedType . "'";

	$ErrMsg = _('The number of transactions using this customer/sales/pricelist type could not be retrieved');
	$Result = DB_query($SQL, $ErrMsg);

	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		prnMsg(_('Cannot delete this sale type because customer transactions have been created using this sales type') . '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('transactions using this sales type code'), 'error');

	} else {

		$SQL = "SELECT COUNT(*) FROM debtorsmaster WHERE salestype='" . $SelectedType . "'";

		$ErrMsg = _('The number of transactions using this Sales Type record could not be retrieved because');
		$Result = DB_query($SQL, $ErrMsg);
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0] > 0) {
			prnMsg(_('Cannot delete this sale type because customers are currently set up to use this sales type') . '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('customers with this sales type code'));
		} else {

			$SQL = "DELETE FROM salestypes WHERE typeabbrev='" . $SelectedType . "'";
			$ErrMsg = _('The Sales Type record could not be deleted because');
			$Result = DB_query($SQL, $ErrMsg);
			prnMsg(_('Sales type') . ' / ' . _('price list') . ' ' . stripslashes($SelectedType) . ' ' . _('has been deleted'), 'success');

			$SQL = "DELETE FROM prices WHERE prices.typeabbrev='" . $SelectedType . "'";
			$ErrMsg = _('The Sales Type prices could not be deleted because');
			$Result = DB_query($SQL, $ErrMsg);

			prnMsg(' ...  ' . _('and any prices for this sales type / price list were also deleted'), 'success');
			unset($SelectedType);
			unset($_GET['delete']);

		}
	} //end if sales type used in debtor transactions or in customers set up
}


if (isset($_POST['Cancel'])) {
	unset($SelectedType);
	unset($_POST['TypeAbbrev']);
	unset($_POST['Sales_Type']);
}

if (!isset($SelectedType)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedType will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of sales types will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/

	echo '<p class="page_title_text" ><img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', _('Search'), '" alt="" />', ' ', $Title, '</p>';

	$SQL = "SELECT typeabbrev,
					sales_type
				FROM salestypes";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) == 0) {
		echo '<div class="page_help_text">', _('As this is the first time that the system has been used, you must first create a sales type.') ,
				'<br />', _('Once you have filled in all the details, click on the button at the bottom of the screen'), '</div>';
		$_SESSION['RestrictLocations'] = 0;
	}

	echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">', _('Type Code'), '</th>
					<th class="SortedColumn">', _('Type Name'), '</th>
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

		echo '<td>', $MyRow['typeabbrev'], '</td>
				<td>', $MyRow['sales_type'], '</td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedType=', urlencode($MyRow['typeabbrev']), '">', _('Edit'), '</a></td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedType=', urlencode($MyRow['typeabbrev']), '&delete=yes" onclick="return MakeConfirm(\'', _('Are you sure you wish to delete this price list and all the prices it may have set up?'), '\', \'Confirm Delete\', this);">', _('Delete'), '</a></td>
			</tr>';
	}
	//END WHILE LIST LOOP
	echo '</tbody>';
	echo '</table>';
}

//end of ifs and buts!
if (isset($SelectedType)) {
	echo '<div class="toplink">
			<a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">', _('Show All Sales Types Defined'), '</a>
		</div>';
	echo '<p class="page_title_text" ><img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', _('Search'), '" alt="" />', ' ', $Title, '</p>';
}
if (!isset($_GET['delete'])) {

	echo '<form name="SalesTypesForm" method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" >';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	// The user wish to EDIT an existing type
	if (isset($SelectedType) and $SelectedType != '') {

		$SQL = "SELECT typeabbrev,
				   sales_type
				FROM salestypes
				WHERE typeabbrev='" . $SelectedType . "'";
		$Result = DB_query($SQL);

		if (DB_num_rows($Result) == 0) {
			echo '<div class="page_help_text">', _('As this is the first time that the system has been used, you must first create a default price list.'),
					'<br />', _('Once you have filled in all the details, click on the button at the bottom of the screen'), '</div>';
		}

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['TypeAbbrev'] = $MyRow['typeabbrev'];
		$_POST['Sales_Type'] = $MyRow['sales_type'];

		echo '<input type="hidden" name="SelectedType" value="', $SelectedType, '" />';
		echo '<input type="hidden" name="TypeAbbrev" value="', $_POST['TypeAbbrev'], '" />';
		echo '<table class="selection">
				<tr>
					<th colspan="4"><b>', _('Sales Type/Price List Setup'), '</b></th>
				</tr>';
		echo '<tr>
				<td>', _('Type Code'), ':</td>
				<td>', $_POST['TypeAbbrev'], '</td>
			</tr>';

	} else {

		// This is a new type so the user may volunteer a type code

		echo '<table class="selection">
				<tr>
					<th colspan="4"><b>', _('Sales Type/Price List Setup'), '</b></th>
				</tr>
				<tr>
					<td>' . _('Type Code') . ':</td>
					<td><input type="text" class="AlphaNumeric" size="3" required="required" maxlength="2" name="TypeAbbrev" /></td>
				</tr>';
	}

	if (!isset($_POST['Sales_Type'])) {
		$_POST['Sales_Type'] = '';
	}
	echo '<tr>
			<td>', _('Sales Type Name'), ':</td>
			<td><input type="text" required="required" maxlength="40" name="Sales_Type" value="', $_POST['Sales_Type'], '" /></td></tr>';

	echo '</table>'; // close main table

	echo '<div class="centre">
			<input type="submit" name="submit" value="', _('Accept'), '" />
			<input type="submit" name="Cancel" value="', _('Cancel'), '" />
		</div>';

	echo '</form>';

} // end if user wish to delete

include('includes/footer.inc');
?>