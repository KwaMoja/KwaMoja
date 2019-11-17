<?php
/* Shows the stock on hand together with outstanding sales orders and outstanding purchase orders by stock location for all items in the selected stock category */

include ('includes/session.php');
include ('includes/SQL_CommonFunctions.php');
$Title = _('All Stock Status By Location/Category');

$ViewTopic = 'Inventory';
$BookMark = 'StockLocStatus';
include ('includes/header.php');

if (isset($_GET['StockID'])) {
	$StockId = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])) {
	$StockId = trim(mb_strtoupper($_POST['StockID']));
}

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', _('Search'), '" alt="" />', ' ', $Title, '
	</p>';

echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

echo '<fieldset>
		<legend>', _('Inquiry Criteria'), '</legend>';

$SQL = "SELECT locationname,
				locations.loccode
			FROM locations
			INNER JOIN locationusers
				ON locationusers.loccode=locations.loccode
				AND locationusers.userid='" . $_SESSION['UserID'] . "'
				AND locationusers.canview=1";
$ResultStkLocs = DB_query($SQL);
if (!isset($_POST['StockLocation'])) {
	$_POST['StockLocation'] = 'All';
}
echo '<field>
		<label for="StockLocation">', _('From Stock Location'), ':</label>
		<select name="StockLocation">
			<option selected="selected" value="All">', _('All Locations'), '</option>';
while ($MyRow = DB_fetch_array($ResultStkLocs)) {
	if (isset($_POST['StockLocation']) and $_POST['StockLocation'] != 'All') {
		if ($MyRow['loccode'] == $_POST['StockLocation']) {
			echo '<option selected="selected" value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
		} else {
			echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
		}
	} else {
		echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
	}
}
echo '</select>
	</field>';

$SQL = "SELECT categoryid,
				categorydescription
		FROM stockcategory
		ORDER BY categorydescription";
$Result1 = DB_query($SQL);
if (DB_num_rows($Result1) == 0) {
	echo '</fieldset><p>';
	prnMsg(_('There are no stock categories currently defined please use the link below to set them up'), 'warn');
	echo '<br /><a href="', $RootPath, '/StockCategories.php">', _('Define Stock Categories'), '</a>';
	include ('includes/footer.php');
	exit;
}

echo '<field>
		<label for="StockCat">', _('In Stock Category'), ':</label>
		<select required="required" name="StockCat">';
if (!isset($_POST['StockCat'])) {
	$_POST['StockCat'] = 'All';
}
if ($_POST['StockCat'] == 'All') {
	echo '<option selected="selected" value="All">', _('All'), '</option>';
} else {
	echo '<option value="All">', _('All'), '</option>';
}
while ($MyRow1 = DB_fetch_array($Result1)) {
	if ($MyRow1['categoryid'] == $_POST['StockCat']) {
		echo '<option selected="selected" value="', $MyRow1['categoryid'], '">', $MyRow1['categorydescription'], '</option>';
	} else {
		echo '<option value="', $MyRow1['categoryid'], '">', $MyRow1['categorydescription'], '</option>';
	}
}
echo '</select>
	</field>';

echo '<field>
		<label for="BelowReorderQuantity">', _('Shown Only Items Where'), ':</label>
		<select required="required" name="BelowReorderQuantity">';
if (!isset($_POST['BelowReorderQuantity'])) {
	$_POST['BelowReorderQuantity'] = 'All';
}
if ($_POST['BelowReorderQuantity'] == 'All') {
	echo '<option selected="selected" value="All">', _('All'), '</option>
		  <option value="Below">', _('Only items below re-order quantity'), '</option>
		  <option value="NotZero">', _('Only items where stock is available'), '</option>
		  <option value="OnOrder">', _('Only items currently on order'), '</option>';
} else if ($_POST['BelowReorderQuantity'] == 'Below') {
	echo '<option value="All">', _('All'), '</option>
		  <option selected="selected" value="Below">', _('Only items below re-order quantity'), '</option>
		  <option value="NotZero">', _('Only items where stock is available'), '</option>
		  <option value="OnOrder">', _('Only items currently on order'), '</option>';
} else if ($_POST['BelowReorderQuantity'] == 'OnOrder') {
	echo '<option value="All">', _('All'), '</option>
		  <option value="Below">', _('Only items below re-order quantity'), '</option>
		  <option value="NotZero">', _('Only items where stock is available'), '</option>
		  <option selected="selected" value="OnOrder">', _('Only items currently on order'), '</option>';
} else {
	echo '<option value="All">', _('All'), '</option>
		  <option value="Below">', _('Only items below re-order quantity'), '</option>
		  <option selected="selected" value="NotZero">', _('Only items where stock is available'), '</option>
		  <option value="OnOrder">', _('Only items currently on order'), '</option>';
}

echo '</select>
	</field>
</fieldset>';

echo '<div class="centre">
		  <input type="submit" name="ShowStatus" value="', _('Show Stock Status'), '" />
	 </div>';

if (isset($_POST['ShowStatus'])) {

	if ($_POST['StockCat'] == 'All') {
		$_POST['StockCat'] = '%';
	}
	if ($_POST['StockLocation'] == 'All') {
		$_POST['StockLocation'] = '%';
	}
	$SQL = "SELECT locstock.stockid,
					stockmaster.description,
					locstock.loccode,
					locstock.bin,
					locations.locationname,
					locstock.quantity,
					locstock.reorderlevel,
					stockmaster.decimalplaces,
					stockmaster.serialised,
					stockmaster.controlled
				FROM locstock
				INNER JOIN stockmaster
					ON locstock.stockid=stockmaster.stockid
				INNER JOIN locations
					ON locstock.loccode=locations.loccode
				WHERE locstock.loccode " . LIKE . "'" . $_POST['StockLocation'] . "'
					AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
					AND stockmaster.categoryid " . LIKE . "'" . $_POST['StockCat'] . "'
				ORDER BY locstock.stockid";

	$ErrMsg = _('The stock held at each location cannot be retrieved because');
	$DbgMsg = _('The SQL that failed was');
	$LocStockResult = DB_query($SQL, $ErrMsg, $DbgMsg);

	echo '<table cellpadding="5" cellspacing="4">
			<thead>
				<tr>
					<th colspan="10">', _('Report produced'), ' - ', DisplayDateTime(), '</th>
				</tr>
				<tr>
					<th class="SortedColumn">', _('Location'), '</th>
					<th class="SortedColumn">', _('StockID'), '</th>
					<th>', _('Description'), '</th>
					<th>', _('Quantity On Hand'), '</th>
					<th>', _('Re-Order Level'), '</th>
					<th>', _('Demand'), '</th>
					<th>', _('Available'), '</th>
					<th>', _('On Order'), '</th>
					<th>', _('Need To Order(ROL)'), '</th>
					<th>', _('Batch/Serial Numbers'), '</th>
				</tr>
			</thead>
			<tbody>';

	while ($MyRow = DB_fetch_array($LocStockResult)) {

		$StockId = $MyRow['stockid'];

		$SQL = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
					FROM salesorderdetails INNER JOIN salesorders
					ON salesorders.orderno = salesorderdetails.orderno
					WHERE salesorders.fromstkloc='" . $MyRow['loccode'] . "'
					AND salesorderdetails.completed=0
					AND salesorderdetails.stkcode='" . $StockId . "'
					AND salesorders.quotation=0";

		$ErrMsg = _('The demand for this product from') . ' ' . $MyRow['loccode'] . ' ' . _('cannot be retrieved because');
		$DemandResult = DB_query($SQL, $ErrMsg);

		if (DB_num_rows($DemandResult) == 1) {
			$DemandRow = DB_fetch_row($DemandResult);
			$DemandQty = $DemandRow[0];
		} else {
			$DemandQty = 0;
		}

		//Also need to add in the demand as a component of an assembly items if this items has any assembly parents.
		$SQL = "SELECT SUM((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
				FROM salesorderdetails INNER JOIN salesorders
					 ON salesorders.orderno = salesorderdetails.orderno
				INNER JOIN bom
					  ON salesorderdetails.stkcode=bom.parent
				INNER JOIN stockmaster
					  ON stockmaster.stockid=bom.parent
				WHERE salesorders.fromstkloc='" . $MyRow['loccode'] . "'
				AND salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
				AND bom.component='" . $StockId . "'
				AND stockmaster.mbflag='A'
				AND salesorders.quotation=0";

		$ErrMsg = _('The demand for this product from') . ' ' . $MyRow['loccode'] . ' ' . _('cannot be retrieved because');
		$DemandResult = DB_query($SQL, $ErrMsg);

		if (DB_num_rows($DemandResult) == 1) {
			$DemandRow = DB_fetch_row($DemandResult);
			$DemandQty+= $DemandRow[0];
		}
		$SQL = "SELECT SUM((woitems.qtyreqd-woitems.qtyrecd)*bom.quantity) AS dem
				FROM workorders INNER JOIN woitems
					 ON woitems.wo = workorders.wo
				INNER JOIN bom
					  ON woitems.stockid =  bom.parent
				WHERE workorders.closed=0
				AND   bom.component = '" . $StockId . "'
				AND   workorders.loccode='" . $MyRow['loccode'] . "'";
		$DemandResult = DB_query($SQL, $ErrMsg);

		if (DB_num_rows($DemandResult) == 1) {
			$DemandRow = DB_fetch_row($DemandResult);
			$DemandQty+= $DemandRow[0];
		}

		// Get the QOO due to Purchase orders for all locations. Function defined in SQL_CommonFunctions.php
		$QOO = GetQuantityOnOrderDueToPurchaseOrders($StockId, $MyRow['loccode']);
		// Get the QOO dues to Work Orders for all locations. Function defined in SQL_CommonFunctions.php
		$QOO+= GetQuantityOnOrderDueToWorkOrders($StockId, $MyRow['loccode']);

		if (($_POST['BelowReorderQuantity'] == 'Below' and ($MyRow['quantity'] - $MyRow['reorderlevel'] - $DemandQty) < 0) or $_POST['BelowReorderQuantity'] == 'All' or $_POST['BelowReorderQuantity'] == 'NotZero' or ($_POST['BelowReorderQuantity'] == 'OnOrder' and $QOO != 0)) {

			if (($MyRow['quantity'] - $DemandQty) < $MyRow['reorderlevel']) {
				$ReorderQty = $MyRow['reorderlevel'] - ($MyRow['quantity'] - $DemandQty);
			} else {
				$ReorderQty = 0;
			}

			if (($_POST['BelowReorderQuantity'] == 'NotZero') and (($MyRow['quantity'] - $DemandQty) > 0)) {
				echo '<tr class="striped_row">
						<td>', mb_strtoupper($MyRow['locationname']), '</td>
						<td><a target="_blank" href="', $RootPath, '/StockStatus.php?StockID=', urlencode(mb_strtoupper($MyRow['stockid'])), '">', mb_strtoupper($MyRow['stockid']), '</a></td>
						<td>', $MyRow['description'], '</td>
						<td class="number">', locale_number_format($MyRow['quantity'], $MyRow['decimalplaces']), '</td>
						<td class="number">', locale_number_format($MyRow['reorderlevel'], $MyRow['decimalplaces']), '</td>
						<td class="number">', locale_number_format($DemandQty, $MyRow['decimalplaces']), '</td>
						<td class="number"><a target="_blank" href="', $RootPath, '/SelectProduct.php?StockID=', urlencode(mb_strtoupper($MyRow['stockid'])), '">', locale_number_format($MyRow['quantity'] - $DemandQty, $MyRow['decimalplaces']), '</a></td>
						<td class="number">', locale_number_format($QOO, $MyRow['decimalplaces']), '</td>
						<td class="number">', locale_number_format($ReorderQty, $MyRow['decimalplaces']), '</td>';

				if ($MyRow['serialised'] == 1) {
					/*The line is a serialised item*/
					echo '<td><a target="_blank" href="', $RootPath, '/StockSerialItems.php?Serialised=Yes&Location=', urlencode($MyRow['loccode']), '&StockID=', urlencode($StockId), '">', _('Serial Numbers'), '</a></td>
						</tr>';
				} elseif ($MyRow['controlled'] == 1) {
					echo '<td><a target="_blank" href="', $RootPath, '/StockSerialItems.php?Location=', urlencode($MyRow['loccode']), '&StockID=', urlencode($StockId), '">', _('Batches'), '</a></td>
						</tr>';
				} else {
					echo '<td>', _('Not Controlled'), '</td>
						</tr>';
				}
			} else if ($_POST['BelowReorderQuantity'] != 'NotZero') {
				echo '<tr class="striped_row">
						<td>', mb_strtoupper($MyRow['locationname']), '</td>
						<td><a target="_blank" href="', $RootPath, '/StockStatus.php?StockID=', urlencode(mb_strtoupper($MyRow['stockid'])), '">', mb_strtoupper($MyRow['stockid']), '</a></td>
						<td>', $MyRow['description'], '</td>
						<td class="number">', locale_number_format($MyRow['quantity'], $MyRow['decimalplaces']), '</td>
						<td class="number">', locale_number_format($MyRow['reorderlevel'], $MyRow['decimalplaces']), '</td>
						<td class="number">', locale_number_format($DemandQty, $MyRow['decimalplaces']), '</td>
						<td class="number"><a target="_blank" href="', $RootPath, '/SelectProduct.php?StockID=', urlencode(mb_strtoupper($MyRow['stockid'])), '">', locale_number_format($MyRow['quantity'] - $DemandQty, $MyRow['decimalplaces']), '</a></td>
						<td class="number">', locale_number_format($QOO, $MyRow['decimalplaces']), '</td>
						<td class="number">', locale_number_format($ReorderQty, $MyRow['decimalplaces']), '</td>';
				if ($MyRow['serialised'] == 1) {
					/*The line is a serialised item*/

					echo '<td><a target="_blank" href="', $RootPath, '/StockSerialItems.php?Serialised=Yes&Location=', urlencode($MyRow['loccode']), '&StockID=', urlencode($StockId), '">', _('Serial Numbers'), '</a></td>
						</tr>';
				} elseif ($MyRow['controlled'] == 1) {
					echo '<td><a target="_blank" href="', $RootPath, '/StockSerialItems.php?Location=', urlencode($MyRow['loccode']), '&StockID=', urlencode($StockId), '">', _('Batches'), '</a></td>
						</tr>';
				} else {
					echo '<td>', _('Not Controlled'), '</td>
						</tr>';
				}
			} //end of page full new headings if
			
		} //end of if BelowOrderQuantity or all items
		
	}
	//end of while loop
	echo '</tbody>
		</table>';
}
/* Show status button hit */
echo '</form>';
include ('includes/footer.php');

?>