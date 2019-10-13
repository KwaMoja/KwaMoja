<?php
include ('includes/session.php');

$Title = _('Sell Through Support');

include ('includes/header.php');

if (isset($_GET['SupplierID']) and $_GET['SupplierID'] != '') {
	$SupplierID = trim(mb_strtoupper($_GET['SupplierID']));
} elseif (isset($_POST['SupplierID'])) {
	$SupplierID = trim(mb_strtoupper($_POST['SupplierID']));
}

//if $Edit == true then we are editing an existing SellThroughSupport record
if (isset($_GET['Edit'])) {
	$Edit = true;
} elseif (isset($_POST['Edit'])) {
	$Edit = true;
} else {
	$Edit = false;
}

if (!isset($SupplierID)) {
	/* Then display all the sell through support for the supplier */
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', _('Search'), '" alt="" />', ' ', $Title, '
		</p>';
}

/*Deleting a supplier sell through support record */
if (isset($_GET['Delete'])) {
	$Result = DB_query("DELETE FROM sellthroughsupport WHERE id='" . intval($_GET['SellSupportID']) . "'");
	prnMsg(_('Deleted the supplier sell through support record'), 'success');
}

if ((isset($_POST['AddRecord']) or isset($_POST['UpdateRecord'])) and isset($SupplierID)) {
	/*Validate Inputs */
	$InputError = 0;
	/*Start assuming the best */

	if (is_numeric(filter_number_format($_POST['RebateAmount'])) == false) {
		$InputError = 1;
		prnMsg(_('The rebate amount entered was not numeric and a number is required.'), 'error');
		unset($_POST['RebateAmount']);
	} elseif (filter_number_format($_POST['RebateAmount']) == 0 and filter_number_format($_POST['RebatePercent']) == 0) {
		prnMsg(_('Both the rebate amount and the rebate percent is zero. One or the other must be a positive number?'), 'error');
		$InputError = 1;

		/*
		} elseif (mb_strlen($_POST['Narrative'])==0 OR $_POST['Narrative']==''){
		prnMsg(_('The narrative cannot be empty.'),'error');
		$InputError = 1;
		*/
	} elseif (filter_number_format($_POST['RebatePercent']) > 100 or filter_number_format($_POST['RebatePercent']) < 0) {
		prnMsg(_('The rebate percent must be greater than zero but less than 100 percent. No changes will be made to this record'), 'error');
		$InputError = 1;
	} elseif (filter_number_format($_POST['RebateAmount']) != 0 and filter_number_format($_POST['RebatePercent']) != 0) {
		prnMsg(_('Both the rebate percent and rebate amount are non-zero. Only one or the other can be used.'), 'error');
		$InputError = 1;
	} elseif (Date1GreaterThanDate2($_POST['EffectiveFrom'], $_POST['EffectiveTo'])) {
		prnMsg(_('The effective to date is prior to the effective from date.'), 'error');
		$InputError = 1;
	}

	if ($InputError == 0 and isset($_POST['AddRecord'])) {
		$SQL = "INSERT INTO sellthroughsupport (supplierno,
												debtorno,
												categoryid,
												stockid,
												narrative,
												rebateamount,
												rebatepercent,
												effectivefrom,
												effectiveto )
						VALUES ('" . $SupplierID . "',
							'" . $_POST['DebtorNo'] . "',
							'" . $_POST['CategoryID'] . "',
							'" . $_POST['StockID'] . "',
							'" . $_POST['Narrative'] . "',
							'" . filter_number_format($_POST['RebateAmount']) . "',
							'" . filter_number_format($_POST['RebatePercent'] / 100) . "',
							'" . FormatDateForSQL($_POST['EffectiveFrom']) . "',
							'" . FormatDateForSQL($_POST['EffectiveTo']) . "')";

		$ErrMsg = _('The sell through support record could not be added to the database because');
		$DbgMsg = _('The SQL that failed was');
		$AddResult = DB_query($SQL, $ErrMsg, $DbgMsg);
		prnMsg(_('This sell through support has been added to the database'), 'success');
	}
	if ($InputError == 0 and isset($_POST['UpdateRecord'])) {
		$SQL = "UPDATE sellthroughsupport SET debtorno='" . $_POST['DebtorNo'] . "',
											categoryid='" . $_POST['CategoryID'] . "',
											stockid='" . $_POST['StockID'] . "',
											narrative='" . $_POST['Narrative'] . "',
											rebateamount='" . filter_number_format($_POST['RebateAmount']) . "',
											rebatepercent='" . filter_number_format($_POST['RebatePercent']) / 100 . "',
											effectivefrom='" . FormatDateForSQL($_POST['EffectiveFrom']) . "',
											effectiveto='" . FormatDateForSQL($_POST['EffectiveTo']) . "'
							WHERE id='" . $_POST['SellSupportID'] . "'";

		$ErrMsg = _('The sell through support record could not be updated because');
		$DbgMsg = _('The SQL that failed was');
		$UpdResult = DB_query($SQL, $ErrMsg, $DbgMsg);
		prnMsg(_('Sell Through Support record has been updated'), 'success');
		$Edit = false;

	}

	if ($InputError == 0) {
		/*  insert took place and need to clear the form  */
		unset($_POST['StockID']);
		unset($_POST['EffectiveFrom']);
		unset($_POST['DebtorNo']);
		unset($_POST['CategoryID']);
		unset($_POST['Narrative']);
		unset($_POST['RebatePercent']);
		unset($_POST['RebateAmount']);
		unset($_POST['EffectiveFrom']);
		unset($_POST['EffectiveTo']);
	}
}

if (isset($_POST['SearchSupplier'])) {
	if ($_POST['Keywords'] == '' and $_POST['SupplierCode'] == '') {
		$_POST['Keywords'] = ' ';
	}
	if (mb_strlen($_POST['Keywords']) > 0) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		$SQL = "SELECT suppliers.supplierid,
						suppliers.suppname,
						suppliers.currcode,
						suppliers.address1,
						suppliers.address2,
						suppliers.address3
				FROM suppliers
				WHERE suppliers.suppname " . LIKE . " '" . $SearchString . "'";

	} elseif (mb_strlen($_POST['SupplierCode']) > 0) {
		$SQL = "SELECT suppliers.supplierid,
						suppliers.suppname,
						suppliers.currcode,
						suppliers.address1,
						suppliers.address2,
						suppliers.address3
				FROM suppliers
				WHERE suppliers.supplierid " . LIKE . " '%" . $_POST['SupplierCode'] . "%'";

	} //one of keywords or SupplierCode was more than a zero length string
	$ErrMsg = _('The suppliers matching the criteria entered could not be retrieved because');
	$DbgMsg = _('The SQL to retrieve supplier details that failed was');
	$SuppliersResult = DB_query($SQL, $ErrMsg, $DbgMsg);

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<table cellpadding="2" colspan="7">
			<thead>
				<tr>
					<th class="SortedColumn">', _('Code'), '</th>
					<th class="SortedColumn">', _('Supplier Name'), '</th>
					<th>', _('Currency'), '</th>
					<th>', _('Address 1'), '</th>
					<th>', _('Address 2'), '</th>
					<th>', _('Address 3'), '</th>
				</tr>
			</thead>';

	echo '<tbody>';
	while ($MyRow = DB_fetch_array($SuppliersResult)) {
		echo '<tr class="striped_row">
				<td><input type="submit" name="SupplierID" value="', $MyRow['supplierid'], '" /></td>
				<td>', $MyRow['suppname'], '</td>
				<td>', $MyRow['currcode'], '</td>
				<td>', $MyRow['address1'], '</td>
				<td>', $MyRow['address2'], '</td>
				<td>', $MyRow['address3'], '</td>
			</tr>';
	} //end of while loop
	echo '</tbody>
		</table>
	</form>';
} //end if results to show
if (!isset($SupplierID) or isset($_POST['SearchSupplier'])) {
	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<fieldset>
			<legend>', _('Search for Suppliers'), '</legend>
			<field>
				<label for="Keywords">', _('Text in the Supplier Name'), ':</label>
				<input type="search" name="Keywords" size="20" maxlength="25" />
			</field>
			<h1><b>', _('OR'), '</b></h1>
			<field>
				<label for="SupplierCode">', _('Text in Supplier'), ':</label>
				<input type="search" name="SupplierCode" size="20" maxlength="50" />
			</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="SearchSupplier" value="' . _('Find Suppliers Now') . '" />
		</div>
	</form>';
	include ('includes/footer.php');
	exit;
}

if (isset($SupplierID)) {
	/* Then display all the sell through support for the supplier */

	/*Get the supplier details */
	$SuppResult = DB_query("SELECT suppname,
									currcode,
									decimalplaces
							FROM suppliers INNER JOIN currencies
							ON suppliers.currcode=currencies.currabrev
							WHERE supplierid='" . $SupplierID . "'");
	$SuppRow = DB_fetch_array($SuppResult);
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', _('Search'), '" alt="" />', ' ', $Title, ' ', _('For Supplier'), ' - ', $SupplierID, ' - ', $SuppRow['suppname'], '
		</p>';

}

if (isset($SupplierID) and $Edit == false) {

	$SQL = "SELECT	id,
					sellthroughsupport.debtorno,
					debtorsmaster.name,
					rebateamount,
					rebatepercent,
					effectivefrom,
					effectiveto,
					sellthroughsupport.stockid,
					description,
					categorydescription,
					sellthroughsupport.categoryid,
					narrative
			FROM sellthroughsupport LEFT JOIN stockmaster
			ON sellthroughsupport.stockid=stockmaster.stockid
			LEFT JOIN stockcategory
			ON sellthroughsupport.categoryid = stockcategory.categoryid
			LEFT JOIN debtorsmaster
			ON sellthroughsupport.debtorno=debtorsmaster.debtorno
			WHERE supplierno = '" . $SupplierID . "'
			ORDER BY sellthroughsupport.effectivefrom DESC";
	$ErrMsg = _('The supplier sell through support deals could not be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($Result) == 0) {
		prnMsg(_('There are no sell through support deals entered for this supplier'), 'info');
	} else {
		echo '<table cellpadding="2">
				<tr>
					<th>', _('Item or Category'), '</th>
					<th>', _('Customer'), '</th>
					<th>', _('Rebate'), '<br />', _('Value'), ' ', $SuppRow['currcode'], '</th>
					<th>', _('Rebate'), '<br />', _('Percent'), '</th>
					<th>', _('Narrative'), '</th>
					<th>', _('Effective From'), '</th>
					<th>', _('Effective To'), '</th>
					<th colspan="2"></th>
				</tr>';

		while ($MyRow = DB_fetch_array($Result)) {
			if ($MyRow['categoryid'] == '') {
				$ItemDescription = $MyRow['stockid'] . ' - ' . $MyRow['description'];
			} else {
				$ItemDescription = _('Any') . ' ' . $MyRow['categorydescription'];
			}
			if ($MyRow['debtorno'] == '') {
				$Customer = _('All Customers');
			} else {
				$Customer = $MyRow['debtorno'] . ' - ' . $MyRow['name'];
			}

			echo '<tr class="striped_row">
					<td>', $ItemDescription, '</td>
					<td>', $Customer, '</td>
					<td class="number">', locale_number_format($MyRow['rebateamount'], $SuppRow['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['rebatepercent'] * 100, 2), '</td>
					<td>', $MyRow['narrative'], '</td>
					<td>', ConvertSQLDate($MyRow['effectivefrom']), '</td>
					<td>', ConvertSQLDate($MyRow['effectiveto']), '</td>
					<td><a href="', htmlspecialchars(basename(__FILE__)), '?SellSupportID=', urlencode($MyRow['id']), '&amp;SupplierID=', urlencode($SupplierID), '&amp;Edit=1">', _('Edit'), '</a></td>
					<td><a href="', htmlspecialchars(basename(__FILE__)), '?SellSupportID=', urlencode($MyRow['id']), '&amp;Delete=1&amp;SupplierID=', urlencode($SupplierID), '" onclick=\'return MakeConfirm("', _('Are you sure you wish to delete this sell through support record?'), '", \'Confirm Delete\', this);\'>', _('Delete'), '</a></td>
				</tr>';
		} //end of while loop
		echo '</table>';
	} // end of there are sell through support rows to show
	
}
/* Only show the existing supplier sell through support records if one is not being edited */

/*Show the input form for new supplier sell through support details */
if (isset($SupplierID)) { //not selecting a supplier
	$SuppName = $SuppRow['suppname'];

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<input type="hidden" name="SupplierID" value="', $SupplierID, '" />';

	if ($Edit == true) {
		$SQL = "SELECT id,
						debtorno,
						rebateamount,
						rebatepercent,
						effectivefrom,
						effectiveto,
						stockid,
						categoryid,
						narrative
				FROM sellthroughsupport
				WHERE id='" . floatval($_GET['SellSupportID']) . "'";

		$ErrMsg = _('The supplier sell through support could not be retrieved because');
		$EditResult = DB_query($SQL, $ErrMsg);
		$MyRow = DB_fetch_array($EditResult);
		$_POST['DebtorNo'] = $MyRow['debtorno'];
		$_POST['StockID'] = $MyRow['stockid'];
		$_POST['CategoryID'] = $MyRow['categoryid'];
		$_POST['Narrative'] = $MyRow['narrative'];
		$_POST['RebatePercent'] = locale_number_format($MyRow['rebatepercent'] * 100, 2);
		$_POST['RebateAmount'] = locale_number_format($MyRow['rebateamount'], $SuppRow['decimalplaces']);
		$_POST['EffectiveFrom'] = ConvertSQLDate($MyRow['effectivefrom']);
		$_POST['EffectiveTo'] = ConvertSQLDate($MyRow['effectiveto']);

		echo '<input type="hidden" name="SellSupportID" value="' . $MyRow['id'] . '" />';
	}
	if (!isset($_POST['RebateAmount'])) {
		$_POST['RebateAmount'] = 0;
	}
	if (!isset($_POST['RebatePercent'])) {
		$_POST['RebatePercent'] = 0;
	}
	if (!isset($_POST['EffectiveFrom'])) {
		$_POST['EffectiveFrom'] = Date($_SESSION['DefaultDateFormat']);
	}
	if (!isset($_POST['EffectiveTo'])) {
		/* Default EffectiveTo to the end of the month */
		$_POST['EffectiveTo'] = Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, Date('m') + 1, 0, Date('y')));
	}
	if (!isset($_POST['DebtorNo'])) {
		$_POST['DebtorNo'] = '';
	}
	if (!isset($_POST['Narrative'])) {
		$_POST['Narrative'] = '';
	}

	echo '<fieldset>
			<legend>', _('Support Details'), '</legend>';

	echo '<field>
			<label for="DebtorNo">', _('Support for Customer'), ':</label>
			<select name="DebtorNo">';
	if ($_POST['DebtorNo'] == '') {
		echo '<option selected="selected" value="">', _('All Customers'), '</option>';
	} else {
		echo '<option value="">', _('All Customers'), '</option>';
	}

	$CustomerResult = DB_query("SELECT debtorno, name FROM debtorsmaster");

	while ($CustomerRow = DB_fetch_array($CustomerResult)) {
		if ($CustomerRow['debtorno'] == $_POST['DebtorNo']) {
			echo '<option selected="selected" value="', $CustomerRow['debtorno'], '">', $CustomerRow['name'], '</option>';
		} else {
			echo '<option value="', $CustomerRow['debtorno'], '">', $CustomerRow['name'], '</option>';
		}
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="CategoryID">', _('Support Whole Category'), ':</label>
			<select name="CategoryID">';
	if (isset($_POST['CategoryID']) and $_POST['CategoryID'] == '') {
		echo '<option selected="selected" value="">', _('Specific Item Only'), '</option>';
	} else {
		echo '<option value="">', _('Specific Item Only'), '</option>';
	}

	$CategoriesResult = DB_query("SELECT categoryid, categorydescription FROM stockcategory WHERE stocktype='F'");

	while ($CategoriesRow = DB_fetch_array($CategoriesResult)) {
		if (isset($_POST['CategoryID']) and $CategoriesRow['categoryid'] == $_POST['CategoryID']) {
			echo '<option selected="selected" value="', $CategoriesRow['categoryid'], '">', $CategoriesRow['categorydescription'], '</option>';
		} else {
			echo '<option value="', $CategoriesRow['categoryid'], '">', $CategoriesRow['categorydescription'], '</option>';
		}
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="StockID">', _('Support Specific Item'), ':</label>
			<select name="StockID">';
	if (isset($_POST['StockID']) and $_POST['StockID'] == '') {
		echo '<option selected="selected" value="">', _('Support An Entire Category'), '</option>';
	} else {
		echo '<option value="">', _('Support An Entire Category'), '</option>';
	}

	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description
			FROM purchdata INNER JOIN stockmaster
			ON purchdata.stockid=stockmaster.stockid
			WHERE supplierno ='" . $SupplierID . "'
			AND preferred=1";
	$ErrMsg = _('Could not retrieve the items that the supplier provides');
	$DbgMsg = _('The SQL that was used to get the supplier items and failed was');
	$ItemsResult = DB_query($SQL, $ErrMsg, $DbgMsg);

	while ($ItemsRow = DB_fetch_array($ItemsResult)) {
		if (isset($_POST['StockID']) and $ItemsRow['stockid'] == $_POST['StockID']) {
			echo '<option selected="selected" value="', $ItemsRow['stockid'], '">', $ItemsRow['stockid'], ' - ', $ItemsRow['description'], '</option>';
		} else {
			echo '<option value="', $ItemsRow['stockid'], '">', $ItemsRow['stockid'], ' - ', $ItemsRow['description'], '</option>';
		}
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="Narrative">', _('Narrative'), ':</label>
			<input type="text" name="Narrative" required="required" maxlength="20" size="21" value="', $_POST['Narrative'], '" />
		</field>
		 <field>
			<label for="RebateAmount">', _('Rebate value per unit'), ' (', $SuppRow['currcode'], '):</label>
			<input type="text" class="number" name="RebateAmount" required="required" maxlength="12" size="12" value="', $_POST['RebateAmount'], '" />
		</field>
		<field>
			<label for="RebatePercent">', _('Rebate Percent'), ':</label>
			<input type="text" class="number" name="RebatePercent" required="required" maxlength="5" size="6" value="', $_POST['RebatePercent'], '" />%
		</field>
		<field>
			<label for="EffectiveFrom">', _('Support Start Date'), ':</label>
			<input type="text" class="date" name="EffectiveFrom" required="required" maxlength="10" size="11" value="', $_POST['EffectiveFrom'], '" />
		</field>
		<field>
			<label for="EffectiveTo">', _('Support End Date'), ':</label>
			<input type="text" class="date" name="EffectiveTo" required="required" maxlength="10" size="11" value="', $_POST['EffectiveTo'], '" />
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">';
	if ($Edit == true) {
		echo '<input type="submit" name="UpdateRecord" value="', _('Update'), '" />';
		echo '<input type="hidden" name="Edit" value="1" />';

		/*end if there is a supplier sell through support record being updated */
	} else {
		echo '<input type="submit" name="AddRecord" value="', _('Add'), '" />';
	}

	echo '</div>
		</form>';
}

include ('includes/footer.php');
?>