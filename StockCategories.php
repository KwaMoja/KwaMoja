<?php
include ('includes/session.php');

$Title = _('Stock Category Maintenance');

include ('includes/header.php');

// BEGIN: Stock Type Name array.
$SQL = "SELECT type,
				name
			FROM stocktypes";
$Result = DB_query($SQL);
$StockTypeName = array();
while ($Row = DB_fetch_array($Result)) {
	$StockTypeName[$Row['type']] = $Row['name'];
}
asort($StockTypeName);
// END: Stock Type Name array.
// BEGIN: Tax Category Name array.
$TaxCategoryName = array();
$Query = "SELECT taxcatid, taxcatname FROM taxcategories ORDER BY taxcatname";
$Result = DB_query($Query);
while ($Row = DB_fetch_array($Result)) {
	$TaxCategoryName[$Row['taxcatid']] = $Row['taxcatname'];
}
// END: Tax Category Name array.
echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/supplier.png" title="', _('Inventory Adjustment'), '" alt="" />', ' ', $Title, '
	</p>';

if (isset($_GET['SelectedCategory'])) {
	$SelectedCategory = mb_strtoupper($_GET['SelectedCategory']);
} else if (isset($_POST['SelectedCategory'])) {
	$SelectedCategory = mb_strtoupper($_POST['SelectedCategory']);
}

if (isset($_GET['DeleteProperty'])) {

	$ErrMsg = _('Could not delete the property') . ' ' . $_GET['DeleteProperty'] . ' ' . _('because');
	$SQL = "DELETE FROM stockitemproperties WHERE stkcatpropid='" . $_GET['DeleteProperty'] . "'";
	$Result = DB_query($SQL, $ErrMsg);
	$SQL = "DELETE FROM stockcatproperties WHERE stkcatpropid='" . $_GET['DeleteProperty'] . "'";
	$Result = DB_query($SQL, $ErrMsg);
	prnMsg(_('Deleted the property') . ' ' . $_GET['DeleteProperty'], 'success');
}

if (isset($_POST['UpdateProperties'])) {
	if ($_POST['PropertyCounter'] == 0 and $_POST['PropLabel0'] != '') {
		$_POST['PropertyCounter'] = 0;
	}

	for ($i = 0;$i <= $_POST['PropertyCounter'];$i++) {

		if (isset($_POST['PropReqSO' . $i]) and $_POST['PropReqSO' . $i] == true) {
			$_POST['PropReqSO' . $i] = 1;
		} else {
			$_POST['PropReqSO' . $i] = 0;
		}
		if (isset($_POST['PropNumeric' . $i]) and $_POST['PropNumeric' . $i] == true) {
			$_POST['PropNumeric' . $i] = 1;
		} else {
			$_POST['PropNumeric' . $i] = 0;
		}
		if (!isset($_POST['PropMinimum' . $i]) or $_POST['PropMinimum' . $i] === '') {
			$_POST['PropMinimum' . $i] = '-999999999';
		}
		if (!isset($_POST['PropMaximum' . $i]) or $_POST['PropMaximum' . $i] === '') {
			$_POST['PropMaximum' . $i] = '999999999';
		}
		if ($_POST['PropID' . $i] == 'NewProperty' and mb_strlen($_POST['PropLabel' . $i]) > 0) {
			$SQL = "INSERT INTO stockcatproperties (categoryid,
													label,
													controltype,
													defaultvalue,
													minimumvalue,
													maximumvalue,
													numericvalue,
													reqatsalesorder)
											VALUES ('" . $SelectedCategory . "',
													'" . $_POST['PropLabel' . $i] . "',
													'" . $_POST['PropControlType' . $i] . "',
													'" . $_POST['PropDefault' . $i] . "',
													'" . filter_number_format($_POST['PropMinimum' . $i]) . "',
													'" . filter_number_format($_POST['PropMaximum' . $i]) . "',
													'" . $_POST['PropNumeric' . $i] . "',
													'" . $_POST['PropReqSO' . $i] . "')";
			$ErrMsg = _('Could not insert a new category property for') . $_POST['PropLabel' . $i];
			$Result = DB_query($SQL, $ErrMsg);
		} elseif ($_POST['PropID' . $i] != 'NewProperty') { //we could be amending existing properties
			$SQL = "UPDATE stockcatproperties SET label ='" . $_POST['PropLabel' . $i] . "',
											  controltype = '" . $_POST['PropControlType' . $i] . "',
											  defaultvalue = '" . $_POST['PropDefault' . $i] . "',
											  minimumvalue = '" . filter_number_format($_POST['PropMinimum' . $i]) . "',
											  maximumvalue = '" . filter_number_format($_POST['PropMaximum' . $i]) . "',
											  numericvalue = '" . $_POST['PropNumeric' . $i] . "',
											  reqatsalesorder = '" . $_POST['PropReqSO' . $i] . "'
										WHERE stkcatpropid ='" . $_POST['PropID' . $i] . "'";
			$ErrMsg = _('Updated the stock category property for') . ' ' . $_POST['PropLabel' . $i];
			$Result = DB_query($SQL, $ErrMsg);
		}
	}
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	 ie the page has called itself with some user input */

	//first off validate inputs sensible
	$_POST['CategoryID'] = mb_strtoupper($_POST['CategoryID']);

	$SQL = "SELECT type FROM stocktypes WHERE type='" . $_POST['StockType'] . "'";
	$Result = DB_query($SQL);

	if (empty($_POST['PropertyCounter'])) {
		$_POST['PropertyCounter'] = 0;
	}
	if (mb_strlen(stripslashes($_POST['CategoryID'])) > 6) {
		$InputError = 1;
		prnMsg(_('The Inventory Category code must be six characters or less long'), 'error');
	} elseif (mb_strlen($_POST['CategoryID']) == 0) {
		$InputError = 1;
		prnMsg(_('The Inventory category code must be at least 1 character but less than six characters long'), 'error');
	} elseif (mb_strlen(stripslashes($_POST['CategoryDescription'])) > 20) {
		$InputError = 1;
		prnMsg(_('The stock category description must be twenty characters or less long'), 'error');
	} elseif (DB_num_rows($Result) == 0) {
		$InputError = 1;
		prnMsg(_('The stock type selected must exist'), 'error');
	}
	for ($i = 0;$i <= $_POST['PropertyCounter'];$i++) {
		if (isset($_POST['PropNumeric' . $i]) and $_POST['PropNumeric' . $i] == true) {
			if (!is_numeric(filter_number_format($_POST['PropMinimum' . $i]))) {
				$InputError = 1;
				prnMsg(_('The minimum value is expected to be a numeric value'), 'error');
			}
			if (!is_numeric(filter_number_format($_POST['PropMaximum' . $i]))) {
				$InputError = 1;
				prnMsg(_('The maximum value is expected to be a numeric value'), 'error');
			}
		}
	} //check the properties are sensible
	if (isset($SelectedCategory) and $InputError != 1) {

		/*SelectedCategory could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$SQL = "UPDATE stockcategory SET stocktype = '" . $_POST['StockType'] . "',
									 categorydescription = '" . $_POST['CategoryDescription'] . "',
									 defaulttaxcatid = '" . $_POST['DefaultTaxCatID'] . "',
									 stockact = '" . $_POST['StockAct'] . "',
									 adjglact = '" . $_POST['AdjGLAct'] . "',
									 issueglact = '" . $_POST['IssueGLAct'] . "',
									 purchpricevaract = '" . $_POST['PurchPriceVarAct'] . "',
									 materialuseagevarac = '" . $_POST['MaterialUseageVarAc'] . "',
									 wipact = '" . $_POST['WIPAct'] . "'
								WHERE categoryid = '" . stripslashes($SelectedCategory) . "'";
		$ErrMsg = _('Could not update the stock category') . $_POST['CategoryDescription'] . _('because');
		$Result = DB_query($SQL, $ErrMsg);

		if ($_POST['PropertyCounter'] == 0 and $_POST['PropLabel0'] != '') {
			$_POST['PropertyCounter'] = 0;
		}

		for ($i = 0;$i <= $_POST['PropertyCounter'];$i++) {

			if (isset($_POST['PropReqSO' . $i]) and $_POST['PropReqSO' . $i] == true) {
				$_POST['PropReqSO' . $i] = 1;
			} else {
				$_POST['PropReqSO' . $i] = 0;
			}
			if (isset($_POST['PropNumeric' . $i]) and $_POST['PropNumeric' . $i] == true) {
				$_POST['PropNumeric' . $i] = 1;
			} else {
				$_POST['PropNumeric' . $i] = 0;
			}
			if ($_POST['PropID' . $i] == 'NewProperty' and mb_strlen($_POST['PropLabel' . $i]) > 0) {
				$SQL = "INSERT INTO stockcatproperties (categoryid,
														label,
														controltype,
														defaultvalue,
														minimumvalue,
														maximumvalue,
														numericvalue,
														reqatsalesorder)
											VALUES ('" . $SelectedCategory . "',
													'" . $_POST['PropLabel' . $i] . "',
													'" . $_POST['PropControlType' . $i] . "',
													'" . $_POST['PropDefault' . $i] . "',
													'" . filter_number_format($_POST['PropMinimum' . $i]) . "',
													'" . filter_number_format($_POST['PropMaximum' . $i]) . "',
													'" . $_POST['PropNumeric' . $i] . "',
													'" . $_POST['PropReqSO' . $i] . "')";
				$ErrMsg = _('Could not insert a new category property for') . $_POST['PropLabel' . $i];
				$Result = DB_query($SQL, $ErrMsg);
			} elseif ($_POST['PropID' . $i] != 'NewProperty') { //we could be amending existing properties
				$SQL = "UPDATE stockcatproperties SET label ='" . $_POST['PropLabel' . $i] . "',
													  controltype = '" . $_POST['PropControlType' . $i] . "',
													  defaultvalue = '" . $_POST['PropDefault' . $i] . "',
													  minimumvalue = '" . filter_number_format($_POST['PropMinimum' . $i]) . "',
													  maximumvalue = '" . filter_number_format($_POST['PropMaximum' . $i]) . "',
													  numericvalue = '" . $_POST['PropNumeric' . $i] . "',
													  reqatsalesorder = '" . $_POST['PropReqSO' . $i] . "'
												WHERE stkcatpropid ='" . $_POST['PropID' . $i] . "'";
				$ErrMsg = _('Updated the stock category property for') . ' ' . $_POST['PropLabel' . $i];
				$Result = DB_query($SQL, $ErrMsg);
			}

		} //end of loop round properties
		prnMsg(_('Updated the stock category record for') . ' ' . stripslashes($_POST['CategoryDescription']), 'success');
		unset($SelectedCategory);
		unset($_POST['CategoryID']);
		unset($_POST['StockType']);
		unset($_POST['CategoryDescription']);
		unset($_POST['StockAct']);
		unset($_POST['AdjGLAct']);
		unset($_POST['IssueGLAct']);
		unset($_POST['PurchPriceVarAct']);
		unset($_POST['MaterialUseageVarAc']);
		unset($_POST['WIPAct']);
	} elseif ($InputError != 1) {

		/*Selected category is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new stock category form */

		$SQL = "INSERT INTO stockcategory (categoryid,
											stocktype,
											categorydescription,
											defaulttaxcatid,
											stockact,
											adjglact,
											issueglact,
											purchpricevaract,
											materialuseagevarac,
											wipact
										) VALUES (
											'" . $_POST['CategoryID'] . "',
											'" . $_POST['StockType'] . "',
											'" . $_POST['CategoryDescription'] . "',
											'" . $_POST['DefaultTaxCatID'] . "',
											'" . $_POST['StockAct'] . "',
											'" . $_POST['AdjGLAct'] . "',
											'" . $_POST['IssueGLAct'] . "',
											'" . $_POST['PurchPriceVarAct'] . "',
											'" . $_POST['MaterialUseageVarAc'] . "',
											'" . $_POST['WIPAct'] . "')";
		$ErrMsg = _('Could not insert the new stock category') . $_POST['CategoryDescription'] . _('because');
		$Result = DB_query($SQL, $ErrMsg);
		prnMsg(_('A new stock category record has been added for') . ' ' . stripslashes($_POST['CategoryDescription']), 'success');

	}
	//run the SQL from either of the above possibilites
	unset($_POST['StockType']);
	unset($_POST['CategoryDescription']);
	unset($_POST['StockAct']);
	unset($_POST['AdjGLAct']);
	unset($_POST['IssueGLAct']);
	unset($_POST['PurchPriceVarAct']);
	unset($_POST['MaterialUseageVarAc']);
	unset($_POST['WIPAct']);

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button
	// PREVENT DELETES IF DEPENDENT RECORDS IN 'StockMaster'
	$SQL = "SELECT stockid FROM stockmaster WHERE stockmaster.categoryid='" . $SelectedCategory . "'";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {
		prnMsg(_('Cannot delete this stock category because stock items have been created using this stock category') . '<br /> ' . _('There are') . ' ' . $MyRow[0] . ' ' . _('items referring to this stock category code'), 'warn');

	} else {
		$SQL = "SELECT stkcat FROM salesglpostings WHERE stkcat='" . $SelectedCategory . "'";
		$Result = DB_query($SQL);

		if (DB_num_rows($Result) > 0) {
			prnMsg(_('Cannot delete this stock category because it is used by the sales') . ' - ' . _('GL posting interface') . '. ' . _('Delete any records in the Sales GL Interface set up using this stock category first'), 'warn');
		} else {
			$SQL = "SELECT stkcat FROM cogsglpostings WHERE stkcat='" . $SelectedCategory . "'";
			$Result = DB_query($SQL);

			if (DB_num_rows($Result) > 0) {
				prnMsg(_('Cannot delete this stock category because it is used by the cost of sales') . ' - ' . _('GL posting interface') . '. ' . _('Delete any records in the Cost of Sales GL Interface set up using this stock category first'), 'warn');
			} else {
				$SQL = "DELETE FROM stockcategory WHERE categoryid='" . $SelectedCategory . "'";
				$Result = DB_query($SQL);
				prnMsg(_('The stock category') . ' ' . stripslashes($SelectedCategory) . ' ' . _('has been deleted') . ' !', 'success');
				unset($SelectedCategory);
			}
		}
	} //end if stock category used in debtor transactions
	
}

if (!isset($SelectedCategory)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedCategory will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of stock categorys will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/

	$SQL = "SELECT categoryid,
					categorydescription,
					stocktype,
					defaulttaxcatid,
					stockact,
					adjglact,
					issueglact,
					purchpricevaract,
					materialuseagevarac,
					wipact
				FROM stockcategory";
	$Result = DB_query($SQL);

	echo '<table>
			<thead>
				<tr>
					<th class="SortedColumn">', _('Cat Code'), '</th>
					<th class="SortedColumn">', _('Description'), '</th>
					<th class="SortedColumn">', _('Stock Type'), '</th>
					<th class="SortedColumn">', _('Default Tax Category'), '</th>
					<th>', _('Stock GL'), '</th>
					<th>', _('Adjts GL'), '</th>
					<th>', _('Issues GL'), '</th>
					<th>', _('Price Var GL'), '</th>
					<th>', _('Usage Var GL'), '</th>
					<th>', _('WIP GL'), '</th>
					<th colspan="2">', _('Maintenance'), '</th>
				</tr>
			</thead>
			<tbody>';

	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
				<td>', $MyRow['categoryid'], '</td>
				<td>', $MyRow['categorydescription'], '</td>
				<td>', $StockTypeName[$MyRow['stocktype']], '</td>
				<td>', $TaxCategoryName[$MyRow['defaulttaxcatid']], '</td>
				<td class="number">', $MyRow['stockact'], '</td>
				<td class="number">', $MyRow['adjglact'], '</td>
				<td class="number">', $MyRow['issueglact'], '</td>
				<td class="number">', $MyRow['purchpricevaract'], '</td>
				<td class="number">', $MyRow['materialuseagevarac'], '</td>
				<td class="number">', $MyRow['wipact'], '</td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedCategory=', urlencode($MyRow['categoryid']), '">', _('Edit'), '</td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedCategory=', urlencode($MyRow['categoryid']), '&delete=yes" onclick="return MakeConfirm("', _('Are you sure you wish to delete this stock category? Additional checks will be performed before actual deletion to ensure data integrity is not compromised.'), '", \'Confirm Delete\', this);">', _('Delete'), '</td>
			</tr>';
	}
	//END WHILE LIST LOOP
	echo '</tbody>
		</table>';
}

//end of ifs and buts!
echo '<form name="CategoryForm" method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

if (isset($SelectedCategory)) {
	//editing an existing stock category
	if (!isset($_POST['UpdateTypes'])) {
		$SQL = "SELECT categoryid,
						stocktype,
						categorydescription,
						stockact,
						adjglact,
						issueglact,
						purchpricevaract,
						materialuseagevarac,
						wipact,
						defaulttaxcatid
					FROM stockcategory
					WHERE categoryid='" . $SelectedCategory . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['CategoryID'] = $MyRow['categoryid'];
		$_POST['StockType'] = $MyRow['stocktype'];
		$_POST['CategoryDescription'] = $MyRow['categorydescription'];
		$_POST['StockAct'] = $MyRow['stockact'];
		$_POST['AdjGLAct'] = $MyRow['adjglact'];
		$_POST['IssueGLAct'] = $MyRow['issueglact'];
		$_POST['PurchPriceVarAct'] = $MyRow['purchpricevaract'];
		$_POST['MaterialUseageVarAc'] = $MyRow['materialuseagevarac'];
		$_POST['WIPAct'] = $MyRow['wipact'];
		$_POST['DefaultTaxCatID'] = $MyRow['defaulttaxcatid'];
	}
	echo '<input type="hidden" name="SelectedCategory" value="', $SelectedCategory, '" />';
	echo '<input type="hidden" name="CategoryID" value="', $_POST['CategoryID'], '" />';
	echo '<fieldset>
			<legend>', _('Edit Stock Category Details'), '</legend>
			<field>
				<label for="CategoryID">', _('Category Code'), ':</label>
				<div class="fieldtext">', $_POST['CategoryID'], '</div>
			</field>';

} else { //end of if $SelectedCategory only do the else when a new record is being entered
	echo '<fieldset>
			<legend>', _('New Stock Category Details'), '</legend>
			<field>
				<label for="CategoryID">', _('Category Code'), ':</label>
				<input type="text" name="CategoryID" size="7" autofocus="autofocus" required="required" maxlength="6" value="" />
			</field>';
}

//SQL to poulate account selection boxes
$SQL = "SELECT accountcode,
				accountname
			FROM chartmaster
			LEFT JOIN accountgroups
				ON chartmaster.groupcode=accountgroups.groupcode
				AND chartmaster.language=accountgroups.language
			WHERE accountgroups.pandl=0
				AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
			ORDER BY accountcode";

$BSAccountsResult = DB_query($SQL);

$SQL = "SELECT accountcode,
				accountname
			FROM chartmaster
			LEFT JOIN accountgroups
				ON chartmaster.groupcode=accountgroups.groupcode
				AND chartmaster.language=accountgroups.language
			WHERE accountgroups.pandl=1
				AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
			ORDER BY accountcode";

$PnLAccountsResult = DB_query($SQL);

// Category Description input.
if (!isset($_POST['CategoryDescription'])) {
	$_POST['CategoryDescription'] = '';
}

echo '<field>
		<label for="CategoryDescription">', _('Category Description'), ':</label>
		<input type="text" name="CategoryDescription" size="22" required="required" maxlength="20" value="', $_POST['CategoryDescription'], '" />
	</field>';

// Stock Type input.
echo '<input type="submit" name="UpdateTypes" style="display:none;" value="Not Seen" />';
echo '<field>
		<label for="StockType">', _('Stock Type'), '</label>
		<select id="StockType" name="StockType" onChange="ReloadForm(CategoryForm.UpdateTypes)">';
foreach ($StockTypeName as $StockTypeId => $Row) {
	if (isset($_POST['StockType']) and $_POST['StockType'] == $StockTypeId) {
		echo '<option selected="selected" value="', $StockTypeId, '">', $Row, '</option>';
	} else {
		echo '<option value="', $StockTypeId, '">', $Row, '</option>';
	}
}
echo '</select>
	</field>';

$SQL = "SELECT taxcatid, taxcatname FROM taxcategories ORDER BY taxcatname";
$Result = DB_query($SQL);
if (!isset($_POST['DefaultTaxCatID'])) {
	$_POST['DefaultTaxCatID'] = $_SESSION['DefaultTaxCategory'];
}
echo '<field>
		<label for="DefaultTaxCatID">', _('Default Tax Category'), ':</label>
		<select name="DefaultTaxCatID">';
while ($MyRow = DB_fetch_array($Result)) {
	if ($_POST['DefaultTaxCatID'] == $MyRow['taxcatid']) {
		echo '<option selected="selected" value="', $MyRow['taxcatid'], '">', $MyRow['taxcatname'], '</option>';
	} else {
		echo '<option value="', $MyRow['taxcatid'], '">', $MyRow['taxcatname'], '</option>';
	}
} //end while loop
echo '</select>
	</field>';

if (isset($_POST['StockType']) and $_POST['StockType'] == 'L') {
	$Result = $PnLAccountsResult;
	echo '<field>
			<label for="StockAct">', _('Recovery GL Code'), ':</label>';
} else {
	$Result = $BSAccountsResult;
	echo '<field>
			<label for="StockAct">', _('Stock GL Code'), ':</label>';
}
echo '<select required="required" name="StockAct">';

while ($MyRow = DB_fetch_array($Result)) {

	if (isset($_POST['StockAct']) and $MyRow['accountcode'] == $_POST['StockAct']) {
		echo '<option selected="selected" value="', $MyRow['accountcode'], '">', $MyRow['accountname'], ' (', $MyRow['accountcode'], ')', '</option>';
	} else {
		echo '<option value="', $MyRow['accountcode'], '">', $MyRow['accountname'], ' (', $MyRow['accountcode'], ')', '</option>';
	}
} //end while loop
echo '</select>
	</field>';
DB_data_seek($PnLAccountsResult, 0);
DB_data_seek($BSAccountsResult, 0);

echo '<field>
		<label for="WIPAct">', _('WIP GL Code'), ':</label>
		<select required="required" name="WIPAct">';
while ($MyRow = DB_fetch_array($BSAccountsResult)) {

	if (isset($_POST['WIPAct']) and $MyRow['accountcode'] == $_POST['WIPAct']) {
		echo '<option selected="selected" value="', $MyRow['accountcode'], '">', $MyRow['accountname'], ' (', $MyRow['accountcode'], ')', '</option>';
	} else {
		echo '<option value="', $MyRow['accountcode'], '">', $MyRow['accountname'], ' (', $MyRow['accountcode'], ')', '</option>';
	}

} //end while loop
echo '</select>
	</field>';
DB_data_seek($BSAccountsResult, 0);

echo '<field>
		<label for="AdjGLAct">', _('Stock Adjustments GL Code'), ':</label>
		<select required="required" name="AdjGLAct">';

while ($MyRow = DB_fetch_array($PnLAccountsResult)) {
	if (isset($_POST['AdjGLAct']) and $MyRow['accountcode'] == $_POST['AdjGLAct']) {
		echo '<option selected="selected" value="', $MyRow['accountcode'], '">', $MyRow['accountname'], ' (', $MyRow['accountcode'], ')', '</option>';
	} else {
		echo '<option value="', $MyRow['accountcode'], '">', $MyRow['accountname'], ' (', $MyRow['accountcode'], ')', '</option>';
	}

} //end while loop
DB_data_seek($PnLAccountsResult, 0);
echo '</select>
	</field>';

echo '<field>
		<label for="IssueGLAct">', _('Internal Stock Issues GL Code'), ':</label>
		<select required="required" name="IssueGLAct">';

while ($MyRow = DB_fetch_array($PnLAccountsResult)) {
	if (isset($_POST['IssueGLAct']) and $MyRow['accountcode'] == $_POST['IssueGLAct']) {
		echo '<option selected="selected" value="', $MyRow['accountcode'], '">', $MyRow['accountname'], ' (', $MyRow['accountcode'], ')', '</option>';
	} else {
		echo '<option value="', $MyRow['accountcode'], '">', $MyRow['accountname'], ' (', $MyRow['accountcode'], ')', '</option>';
	}

} //end while loop
DB_data_seek($PnLAccountsResult, 0);
echo '</select>
	</field>';

echo '<field>
		<label for="PurchPriceVarAct">', _('Price Variance GL Code'), ':</label>
		<select required="required" name="PurchPriceVarAct">';

while ($MyRow = DB_fetch_array($PnLAccountsResult)) {
	if (isset($_POST['PurchPriceVarAct']) and $MyRow['accountcode'] == $_POST['PurchPriceVarAct']) {
		echo '<option selected="selected" value="', $MyRow['accountcode'], '">', $MyRow['accountname'], ' (', $MyRow['accountcode'], ')', '</option>';
	} else {
		echo '<option value="', $MyRow['accountcode'], '">', $MyRow['accountname'], ' (', $MyRow['accountcode'], ')', '</option>';
	}

} //end while loop
DB_data_seek($PnLAccountsResult, 0);

echo '</select>
	</field>';

echo '<field>
		<label for="MaterialUseageVarAc">';
if (isset($_POST['StockType']) and $_POST['StockType'] == 'L') {
	echo _('Labour Efficiency Variance GL Code');
} else {
	echo _('Usage Variance GL Code');
}
echo ':</label>
		<select required="required" name="MaterialUseageVarAc">';

while ($MyRow = DB_fetch_array($PnLAccountsResult)) {
	if (isset($_POST['MaterialUseageVarAc']) and $MyRow['accountcode'] == $_POST['MaterialUseageVarAc']) {
		echo '<option selected="selected" value="', $MyRow['accountcode'], '">', $MyRow['accountname'], ' (', $MyRow['accountcode'], ')', '</option>';
	} else {
		echo '<option value="', $MyRow['accountcode'], '">', $MyRow['accountname'], ' (', $MyRow['accountcode'], ')', '</option>';
	}

} //end while loop
DB_free_result($PnLAccountsResult);
echo '</select>
	</field>
</fieldset>';

if (isset($SelectedCategory)) {
	//editing an existing stock category
	$SQL = "SELECT stkcatpropid,
					label,
					controltype,
					defaultvalue,
					numericvalue,
					reqatsalesorder,
					minimumvalue,
					maximumvalue
			   FROM stockcatproperties
			   WHERE categoryid='" . $SelectedCategory . "'
			   ORDER BY stkcatpropid";

	$Result = DB_query($SQL);

	/*		echo '<br />Number of rows returned by the sql = ' . DB_num_rows($Result) .
	'<br />The SQL was:<br />' . $SQL;
	*/
	echo '<table>
			<tr>
				<th>', _('Property Label'), '</th>
				<th>', _('Control Type'), '</th>
				<th>', _('Default Value'), '</th>
				<th>', _('Numeric Value'), '</th>
				<th>', _('Minimum Value'), '</th>
				<th>', _('Maximum Value'), '</th>
				<th>', _('Require in SO'), '</th>
			</tr>';
	$PropertyCounter = 0;
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<input type="hidden" name="PropID', $PropertyCounter, '" value="', $MyRow['stkcatpropid'], '" />';
		echo '<tr>
				<td><input type="text" name="PropLabel', $PropertyCounter, '" size="50" maxlength="100" value="', $MyRow['label'], '" /></td>
				<td><select name="PropControlType', $PropertyCounter, '">';
		if ($MyRow['controltype'] == 0) {
			echo '<option selected="selected" value="0">', _('Text Box'), '</option>';
		} else {
			echo '<option value="0">', _('Text Box'), '</option>';
		}
		if ($MyRow['controltype'] == 1) {
			echo '<option selected="selected" value="1">', _('Select Box'), '</option>';
		} else {
			echo '<option value="1">', _('Select Box'), '</option>';
		}
		if ($MyRow['controltype'] == 2) {
			echo '<option selected="selected" value="2">', _('Check Box'), '</option>';
		} else {
			echo '<option value="2">', _('Check Box'), '</option>';
		}
		if ($MyRow['controltype'] == 3) {
			echo '<option selected="selected" value="3">', _('Date Box'), '</option>';
		} else {
			echo '<option value="3">', _('Date Box'), '</option>';
		}
		echo '</select>
			</td>';

		echo '<td><input type="text" name="PropDefault', $PropertyCounter, '" value="', $MyRow['defaultvalue'], '" /></td>';

		if ($MyRow['numericvalue'] == 1) {
			echo '<td><input type="checkbox" name="PropNumeric', $PropertyCounter, '" checked="checked" /></td>';
		} else {
			echo '<td><input type="checkbox" name="PropNumeric', $PropertyCounter, '" /></td>';
		}

		echo '<td><input type="text" name="PropMinimum', $PropertyCounter, '" value="', $MyRow['minimumvalue'], '" /></td>
				<td><input type="text" name="PropMaximum', $PropertyCounter, '" value="', $MyRow['maximumvalue'], '" /></td>';

		if ($MyRow['reqatsalesorder'] == 1) {
			echo '<td align="center"><input type="checkbox" name="PropReqSO', $PropertyCounter, '" checked="True" /></td>';
		} else {
			echo '<td align="center"><input type="checkbox" name="PropReqSO', $PropertyCounter, '" /></td>';
		}

		echo '<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?DeleteProperty=', $MyRow['stkcatpropid'], '&SelectedCategory=', $SelectedCategory, '" onclick=\'return MakeConfirm("', _('Are you sure you wish to delete this property? All properties of this type set up for stock items will also be deleted.'), '", \'Confirm Delete\', this);\'>', _('Delete'), '</td>
			</tr>';

		$PropertyCounter++;
	} //end loop around defined properties for this category
	echo '<input type="hidden" name="PropID', $PropertyCounter, '" value="NewProperty" />';
	echo '<tr>
			<td><input type="text" name="PropLabel', $PropertyCounter, '" size="50" maxlength="100" /></td>
			<td><select name="PropControlType', $PropertyCounter, '">
				<option selected="selected" value="0">', _('Text Box'), '</option>
				<option value="1">', _('Select Box'), '</option>
				<option value="2">', _('Check Box'), '</option>
				<option value="3">', _('Date Box'), '</option>
				</select></td>
			<td><input type="text" name="PropDefault', $PropertyCounter, '" /></td>
			<td><input type="checkbox" name="PropNumeric', $PropertyCounter, '" /></td>
			<td><input type="text" class="number" name="PropMinimum', $PropertyCounter, '" /></td>
			<td><input type="text" class="number" name="PropMaximum', $PropertyCounter, '" /></td>
			<td align="center"><input type="checkbox" name="PropReqSO', $PropertyCounter, '" /></td>
			<td><button type="submit" name="UpdateProperties">', _('Add new Property'), '</button></td>
			</tr>';
	echo '</table>';
	echo '<input type="hidden" name="PropertyCounter" value="', $PropertyCounter, '" />';

}
/* end if there is a category selected */

echo '<div class="centre">
			<input type="submit" name="submit" value="', _('Enter Information'), '" />
		</div>
	</form>';

if (isset($SelectedCategory)) {
	echo '<div style="text-align: right"><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">', _('Show All Stock Categories'), '</a></div>';
}

include ('includes/footer.php');
?>