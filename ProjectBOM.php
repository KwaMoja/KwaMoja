<?php

include('includes/DefineProjectClass.php');

include('includes/session.inc');
$Title = _('Project Bill of Materials');

$Identifier = $_GET['identifier'];

/* If a contract header doesn't exist, then go to
 * Projects.php to create one
 */

if (!isset($_SESSION['Project' . $Identifier])) {
	header('Location:' . $RootPath . '/Projects.php');
	exit;
}
$ViewTopic = 'Projects';
$BookMark = 'AddToProject';
include('includes/header.inc');

if (isset($_POST['UpdateLines']) or isset($_POST['BackToHeader'])) {
	if ($_SESSION['Project' . $Identifier]->Status != 2) { //dont do anything if the customer has committed to the contract
		foreach ($_SESSION['Project' . $Identifier]->ProjectBOM as $ProjectComponent) {
			if (filter_number_format($_POST['Qty' . $ProjectComponent->ComponentID]) == 0) {
				//this is the same as deleting the line - so delete it
				$_SESSION['Project' . $Identifier]->Remove_ProjectComponent($ProjectComponent->ComponentID);
			} else {
				$_SESSION['Project' . $Identifier]->ProjectBOM[$ProjectComponent->ComponentID]->Quantity = filter_number_format($_POST['Qty' . $ProjectComponent->ComponentID]);
				$_SESSION['Project' . $Identifier]->ProjectBOM[$ProjectComponent->ComponentID]->RequiredBy = $_POST['RequiredBy' . $ProjectComponent->ComponentID];
			}
		} // end loop around the items on the contract BOM
	} // end if the contract is not currently committed to by the customer
} // end if the user has hit the update lines or back to header buttons


if (isset($_POST['BackToHeader'])) {
	echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/Projects.php?identifier=' . $Identifier . '" />';
	echo '<br />';
	prnMsg(_('You should automatically be forwarded to the Project page. If this does not happen perhaps the browser does not support META Refresh') . '<a href="' . $RootPath . '/Projects.php?identifier=' . urlencode($Identifier) . '">' . _('click here') . '</a> ' . _('to continue'), 'info');
	include('includes/footer.inc');
	exit;
}

if (isset($_POST['Search'])) {

	$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

	if ($_POST['StockCat'] == 'All') {
		$_POST['StockCat'] = '%';
	}

	$_POST['StockCode'] = '%' . $_POST['StockCode'] . '%';

	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
				FROM stockmaster
				INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
				WHERE stockmaster.mbflag!='D'
					AND stockmaster.mbflag!='A'
					AND stockmaster.mbflag!='K'
					AND stockmaster.discontinued<>1
					AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'
					AND stockmaster.description " . LIKE . " '" . $SearchString . "'
					AND stockmaster.categoryid " . LIKE . " '" . $_POST['StockCat'] . "'
				ORDER BY stockmaster.stockid";

	$ErrMsg = _('There is a problem selecting the part records to display because');
	$DbgMsg = _('The SQL statement that failed was');
	$SearchResult = DB_query($SQL, $ErrMsg, $DbgMsg);

	if (DB_num_rows($SearchResult) == 0 and $Debug == 1) {
		prnMsg(_('There are no products to display matching the criteria provided'), 'warn');
	}
	if (DB_num_rows($SearchResult) == 1) {
		$MyRow = DB_fetch_array($SearchResult);
		$_GET['NewItem'] = $MyRow['stockid'];
		DB_data_seek($SearchResult, 0);
	}

} //end of if search


if (isset($_GET['Delete'])) {
	if ($_SESSION['Project' . $Identifier]->Status != 2) {
		$_SESSION['Project' . $Identifier]->Remove_ProjectComponent($_GET['Delete']);
	} else {
		prnMsg(_('The contract BOM cannot be altered because the customer has already placed the order'), 'warn');
	}
}

if (isset($_POST['NewItem'])) {
	/* NewItem is set from the part selection list as the part code selected */
	for ($i = 0; $i < $_POST['CountOfItems']; $i++) {

		if (filter_number_format($_POST['Qty' . $i]) > 0) {

			$SQL = "SELECT stockmaster.description,
							stockmaster.stockid,
							stockmaster.units,
							stockmaster.decimalplaces,
							stockcosts.materialcost+stockcosts.labourcost+stockcosts.overheadcost AS unitcost
						FROM stockmaster
						LEFT JOIN stockcosts
							ON stockcosts.stockid=stockmaster.stockid
								AND succeeded=0
						WHERE stockmaster.stockid = '" . trim($_POST['StockID' . $i]) . "'";

			$ErrMsg = _('The item details could not be retrieved');
			$DbgMsg = _('The SQL used to retrieve the item details but failed was');
			$Result1 = DB_query($SQL, $ErrMsg, $DbgMsg);

			if ($MyRow = DB_fetch_array($Result1)) {

				$_SESSION['Project' . $Identifier]->Add_To_ProjectBOM(trim($_POST['StockID' . $i]), $MyRow['description'], $_POST['ReqBy' . $i], '', filter_number_format($_POST['Qty' . $i]), $MyRow['unitcost'], $MyRow['units'], $MyRow['decimalplaces']);
			} else {
				prnMsg(_('The item code') . ' ' . trim($_POST['StockID' . $i]) . ' ' . _('does not exist in the database and therefore cannot be added to the project BOM'), 'error');
				if ($Debug == 1) {
					echo '<br />' . $SQL;
				}
				include('includes/footer.inc');
				exit;
			}
			/* end of if not already on the contract BOM */
		}
		/* the quantity of the item is > 0 */
	}
}
/* end of if its a new item */

/* This is where the order as selected should be displayed  reflecting any deletions or insertions*/

echo '<form id="ProjectBOMForm" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $Identifier . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (count($_SESSION['Project' . $Identifier]->ProjectBOM) > 0) {
	echo '<p class="page_title_text" >
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/contract.png" title="' . _('Project Bill of Material') . '" alt="" />  ' . $_SESSION['Project' . $Identifier]->DonorName . '
		</p>';

	echo '<table class="selection">';

	if (isset($_SESSION['Project' . $Identifier]->ProjectRef)) {
		echo '<tr>
				<th colspan="7">' . _('Project Reference') . ': ' . $_SESSION['Project' . $Identifier]->ProjectRef . '</th>
			</tr>';
	}

	echo '<tr>
			<th>' . _('Item Code') . '</th>
			<th>' . _('Description') . '</th>
			<th>' . _('Quantity') . '</th>
			<th>' . _('UOM') . '</th>
			<th>' . _('Unit Cost') . '</th>
			<th>' . _('Sub-total') . '</th>
			<th>' . _('Required By') . '</th>
		</tr>';

	$_SESSION['Project' . $Identifier]->total = 0;
	$k = 0; //row colour counter
	$TotalCost = 0;
	foreach ($_SESSION['Project' . $Identifier]->ProjectBOM as $ProjectComponent) {

		$LineTotal = $ProjectComponent->Quantity * $ProjectComponent->ItemCost;

		$DisplayLineTotal = locale_number_format($LineTotal, $_SESSION['CompanyRecord']['decimalplaces']);

		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}

		echo '<td>' . $ProjectComponent->StockID . '</td>
			  <td>' . $ProjectComponent->ItemDescription . '</td>
			  <td><input type="text" class="number" name="Qty' . $ProjectComponent->ComponentID . '" required="required" maxlength="11" size="11" value="' . locale_number_format($ProjectComponent->Quantity, $ProjectComponent->DecimalPlaces) . '" /></td>
			  <td>' . $ProjectComponent->UOM . '</td>
			  <td class="number">' . locale_number_format($ProjectComponent->ItemCost, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			  <td class="number">' . $DisplayLineTotal . '</td>
				<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="RequiredBy' . $ProjectComponent->ComponentID . '" size="11" value="' . $ProjectComponent->RequiredBy . '" />
			  <td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $Identifier . '&amp;Delete=' . $ProjectComponent->ComponentID . '" onclick="return MakeConfirm(\'' . _('Are you sure you wish to delete this item from the contract BOM?') . '\', \'Confirm Delete\', this);">' . _('Delete') . '</a></td></tr>';
		$TotalCost += $LineTotal;
	}

	$DisplayTotal = locale_number_format($TotalCost, $_SESSION['CompanyRecord']['decimalplaces']);
	echo '<tr>
			<td colspan="5" class="number">' . _('Total Cost') . '</td>
			<td class="number"><b>' . $DisplayTotal . '</b></td>
		</tr>
		</table>';
	echo '<div class="centre">
			<input type="submit" name="UpdateLines" value="' . _('Update Lines') . '" />
			<input type="submit" name="BackToHeader" value="' . _('Back To Project Header') . '" />
		</div>';

}
/*Only display the contract BOM lines if there are any !! */

if (!isset($_GET['Edit'])) {
	$SQL = "SELECT categoryid,
				categorydescription
			FROM stockcategory
			WHERE stocktype<>'L'
			AND stocktype<>'D'
			ORDER BY categorydescription";
	$ErrMsg = _('The supplier category details could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the category details but failed was');
	$Result1 = DB_query($SQL, $ErrMsg, $DbgMsg);
	echo '<p class="page_title_text" >
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . _('Print') . '" alt="" />' . ' ' . _('Search For Stock Items') . '</p>';
	echo '<table class="selection">
			<tr></tr>
			<tr>
				<td><select name="StockCat">';

	echo '<option selected="selected" value="All">' . _('All') . '</option>';
	while ($MyRow1 = DB_fetch_array($Result1)) {
		if (isset($_POST['StockCat']) and $_POST['StockCat'] == $MyRow1['categoryid']) {
			echo '<option selected="selected" value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		} else {
			echo '<option value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		}
	}

	unset($_POST['Keywords']);
	unset($_POST['StockCode']);

	if (!isset($_POST['Keywords'])) {
		$_POST['Keywords'] = '';
	}

	if (!isset($_POST['StockCode'])) {
		$_POST['StockCode'] = '';
	}

	echo '</select></td>
			<td>' . _('Enter text extracts in the description') . ':</td>
			<td><input type="text" name="Keywords" size="20" autofocus="autofocus" maxlength="25" value="' . $_POST['Keywords'] . '" /></td>
		</tr>
		<tr>
			<td></td>
			<td><b>' . _('OR') . ' </b>' . _('Enter extract of the Stock Code') . ':</td>
			<td><input type="text" name="StockCode" size="15" maxlength="18" value="' . $_POST['StockCode'] . '" /></td>
		</tr>
		<tr>
			<td></td>
			<td><b>' . _('OR') . ' </b><a target="_blank" href="' . $RootPath . '/Stocks.php">' . _('Create a New Stock Item') . '</a></td>
		</tr>
		</table>
		<div class="centre">
			<input type="submit" name="Search" value="' . _('Search Now') . '" />
		</div>';

}

if (isset($SearchResult)) {

	echo '<table cellpadding="1">
			<thead>
				<tr>
					<th class="SortedColumn">' . _('Code') . '</th>
					<th class="SortedColumn">' . _('Description') . '</th>
					<th>' . _('Units') . '</th>
					<th>' . _('Image') . '</th>
					<th>' . _('Quantity') . '</th>
					<th>' . _('Required By') . '</th>
				</tr>
			</thead>';

	$k = 0; //row colour counter
	$i = 0;
	echo '<tbody>';
	while ($MyRow = DB_fetch_array($SearchResult)) {

		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}

		if (file_exists($_SESSION['part_pics_dir'] . '/' . $MyRow['stockid'] . '.jpg')) {
			$ImageSource = '<img src="GetStockImage.php?automake=1&amp;textcolor=FFFFFF&amp;bgcolor=CCCCCC&amp;StockID=' . $MyRow['stockid'] . '&amp;text=&amp;width=50&amp;height=50" />';
		} else {
			$ImageSource = '<i>' . _('No Image') . '</i>';
		}

		echo '<td>' . $MyRow['stockid'] . '</td>
				<td>' . $MyRow['description'] . '</td>
				<td>' . $MyRow['units'] . '</td>
				<td>' . $ImageSource . '</td>
				<td><input class="number" type="text" size="6" value="0" name="Qty' . $i . '" />
				<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ReqBy' . $i . '" size="11" value="' . $_SESSION['Project' . $Identifier]->CompletionDate . '" />
				<input type="hidden" name="StockID' . $i . '" value="' . $MyRow['stockid'] . '" />
				</td>
			</tr>';
		++$i;
		if ($i == $_SESSION['DisplayRecordsMax']) {
			break;
		}
		#end of page full new headings if
	}

	#end of while loop
	echo '</tbody>
	</table>';

	echo '<input type="hidden" name="CountOfItems" value="' . $i . '" />';
	if ($i == $_SESSION['DisplayRecordsMax']) {

		prnMsg(_('Only the first') . ' ' . $_SESSION['DisplayRecordsMax'] . ' ' . _('can be displayed') . '. ' . _('Please restrict your search to only the parts required'), 'info');
	}
	echo '<div class="centre">
			<input type="submit" name="NewItem" value="' . _('Add to Project Bill Of Material') . '" />
		</div>';
} #end if SearchResults to show

echo '</form>';
include('includes/footer.inc');
?>