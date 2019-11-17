<?php
include ('includes/session.php');
include ('includes/SQL_CommonFunctions.php');

$Title = _('Stock Status');

include ('includes/header.php');

if (isset($_GET['StockID'])) {
	$StockId = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])) {
	$StockId = trim(mb_strtoupper($_POST['StockID']));
} else {
	$StockId = '';
}

$Result = DB_query("SELECT description,
						   units,
						   mbflag,
						   decimalplaces,
						   serialised,
						   controlled
					FROM stockmaster
					WHERE stockid='" . $StockId . "'", _('Could not retrieve the requested item'), _('The SQL used to retrieve the items was'));

$MyRow = DB_fetch_array($Result);

$DecimalPlaces = $MyRow['decimalplaces'];
$Serialised = $MyRow['serialised'];
$Controlled = $MyRow['controlled'];

if (isset($StockId) and $StockId != '') {
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/inventory.png" title="', _('Inventory'), '" alt="" /><b>', ' ', $StockId, ' - ', $MyRow['description'], ' : ', _('in units of'), ' : ', $MyRow['units'], '</b>
		</p>';
} else {
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/inventory.png" title="', _('Inventory'), '" alt="" />', _('Stock Status Inquiry'), '
		</p>';
}

$Its_A_KitSet_Assembly_Or_Dummy = False;
if ($MyRow[2] == 'K') {
	$Its_A_KitSet_Assembly_Or_Dummy = True;
	prnMsg(_('This is a kitset part and cannot have a stock holding') . ', ' . _('only the total quantity on outstanding sales orders is shown'), 'info');
} elseif ($MyRow[2] == 'A') {
	$Its_A_KitSet_Assembly_Or_Dummy = True;
	prnMsg(_('This is an assembly part and cannot have a stock holding') . ', ' . _('only the total quantity on outstanding sales orders is shown'), 'info');
} elseif ($MyRow[2] == 'D') {
	$Its_A_KitSet_Assembly_Or_Dummy = True;
	prnMsg(_('This is an dummy part and cannot have a stock holding') . ', ' . _('only the total quantity on outstanding sales orders is shown'), 'info');
}

echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

echo '<fieldset>
		<legend>', _('Select Item for Inquiry'), '</legend>';

echo '<field>
		<label>', _('Stock Code'), ':</label>
		<input type="text" name="StockID" size="21" value="', $StockId, '" required="required" maxlength="20" />
	</field>';

echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="ShowStatus" value="', _('Show Stock Status'), '" />
	</div>';

if (isset($StockId) and $StockId != '') {
	$SQL = "SELECT locstock.loccode,
				locations.locationname,
				locstock.quantity,
				locstock.reorderlevel,
				locstock.bin,
				locations.managed,
				canupd
			FROM locstock
			INNER JOIN locations
				ON locstock.loccode=locations.loccode
			INNER JOIN locationusers
				ON locationusers.loccode=locations.loccode
				AND locationusers.userid='" . $_SESSION['UserID'] . "'
				AND locationusers.canview=1
			WHERE locstock.stockid = '" . $StockId . "'
			ORDER BY locations.locationname";

	$ErrMsg = _('The stock held at each location cannot be retrieved because');
	$DbgMsg = _('The SQL that was used to fetch the location details and failed was');
	$LocStockResult = DB_query($SQL, $ErrMsg, $DbgMsg);

	echo '<table>
			<thead>';

	if ($Its_A_KitSet_Assembly_Or_Dummy == True) {
		echo '<tr>
				<th class="SortedColumn">', _('Location'), '</th>
				<th>', _('Demand'), '</th>
			</tr>';
	} else {
		echo '<tr>
				<th class="SortedColumn">', _('Location'), '</th>
				<th>', _('Quantity On Hand'), '</th>
				<th>', _('Re-Order Level'), '</th>
				<th>', _('Demand'), '</th>
				<th>', _('In Transit'), '</th>
				<th>', _('Available'), '</th>
				<th>', _('On Order'), '</th>
			</tr>';
	}
	echo '</thead>';

	echo '<tbody>';
	while ($MyRow = DB_fetch_array($LocStockResult)) {

		$SQL = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
			FROM salesorderdetails INNER JOIN salesorders
			ON salesorders.orderno = salesorderdetails.orderno
			WHERE salesorders.fromstkloc='" . $MyRow['loccode'] . "'
			AND salesorderdetails.completed=0
			AND salesorders.quotation=0
			AND salesorderdetails.stkcode='" . $StockId . "'";

		$ErrMsg = _('The demand for this product from') . ' ' . $MyRow['loccode'] . ' ' . _('cannot be retrieved because');
		$DemandResult = DB_query($SQL, $ErrMsg, $DbgMsg);

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
		$DemandResult = DB_query($SQL, $ErrMsg, $DbgMsg);

		if (DB_num_rows($DemandResult) == 1) {
			$DemandRow = DB_fetch_row($DemandResult);
			$DemandQty+= $DemandRow[0];
		}

		//Also the demand for the item as a component of works orders
		$SQL = "SELECT SUM(qtypu*(woitems.qtyreqd - woitems.qtyrecd)) AS woqtydemo
			FROM woitems INNER JOIN worequirements
			ON woitems.stockid=worequirements.parentstockid
			INNER JOIN workorders
			ON woitems.wo=workorders.wo
			AND woitems.wo=worequirements.wo
			WHERE workorders.loccode='" . $MyRow['loccode'] . "'
			AND worequirements.stockid='" . $StockId . "'
			AND workorders.closed=0";

		$ErrMsg = _('The workorder component demand for this product from') . ' ' . $MyRow['loccode'] . ' ' . _('cannot be retrieved because');
		$DemandResult = DB_query($SQL, $ErrMsg, $DbgMsg);

		if (DB_num_rows($DemandResult) == 1) {
			$DemandRow = DB_fetch_row($DemandResult);
			$DemandQty+= $DemandRow[0];
		}

		if ($Its_A_KitSet_Assembly_Or_Dummy == False) {

			// Get the QOO due to Purchase orders for all locations. Function defined in SQL_CommonFunctions.php
			$QOO = GetQuantityOnOrderDueToPurchaseOrders($StockId, $MyRow['loccode']);
			// Get the QOO dues to Work Orders for all locations. Function defined in SQL_CommonFunctions.php
			$QOO+= GetQuantityOnOrderDueToWorkOrders($StockId, $MyRow['loccode']);

			$InTransitSQL = "SELECT SUM(shipqty-recqty) as intransit
						FROM loctransfers
						WHERE stockid='" . $StockId . "'
							AND shiploc='" . $MyRow['loccode'] . "'";
			$InTransitResult = DB_query($InTransitSQL);
			$InTransitRow = DB_fetch_array($InTransitResult);
			if ($InTransitRow['intransit'] != '') {
				$InTransitQuantityOut = - $InTransitRow['intransit'];
			} else {
				$InTransitQuantityOut = 0;
			}

			$InTransitSQL = "SELECT SUM(-shipqty+recqty) as intransit
						FROM loctransfers
						WHERE stockid='" . $StockId . "'
							AND recloc='" . $MyRow['loccode'] . "'";
			$InTransitResult = DB_query($InTransitSQL);
			$InTransitRow = DB_fetch_array($InTransitResult);
			if ($InTransitRow['intransit'] != '') {
				$InTransitQuantityIn = - $InTransitRow['intransit'];
			} else {
				$InTransitQuantityIn = 0;
			}

			if (($InTransitQuantityIn + $InTransitQuantityOut) < 0) {
				$Available = $MyRow['quantity'] - $DemandQty + ($InTransitQuantityIn + $InTransitQuantityOut);
			} else {
				$Available = $MyRow['quantity'] - $DemandQty;
			}

			echo '<tr class="striped_row">
					<td>', $MyRow['locationname'], '</td><td class="number">', locale_number_format($MyRow['quantity'], $DecimalPlaces), '</td>
					<td class="number">', locale_number_format($MyRow['reorderlevel'], $DecimalPlaces), '</td>
					<td class="number">', locale_number_format($DemandQty, $DecimalPlaces), '</td>
					<td class="number">', locale_number_format($InTransitQuantityIn + $InTransitQuantityOut, $DecimalPlaces), '</td>
					<td class="number">', locale_number_format($Available, $DecimalPlaces), '</td>
					<td class="number">', locale_number_format($QOO, $DecimalPlaces), '</td>
				</tr>';

			if ($Serialised == 1) {
				/*The line is a serialised item*/
				echo '<td>
						<a target="_blank" href="', $RootPath, '/StockSerialItems.php?Serialised=Yes&Location=', urlencode($MyRow['loccode']), '&amp;StockID=', urlencode($StockId), '">', _('Serial Numbers'), '</a>
					</td>
				</tr>';
			} elseif ($Controlled == 1) {
				echo '<td>
						<a target="_blank" href="', $RootPath, '/StockSerialItems.php?Location=', urlencode($MyRow['loccode']), '&amp;StockID=', urlencode($StockId), '">', _('Batches'), '</a>
					</td>
				</tr>';
			} else {
				echo '</tr>';
			}

		} else {
			/* It must be a dummy, assembly or kitset part */

			echo '<tr class="striped_row">
					<td>', $MyRow['locationname'], '</td>
					<td class="number">', locale_number_format($DemandQty, $DecimalPlaces), '</td>
				</tr>';
		}
		//end of page full new headings if
		
	}
	//end of while loop
	echo '</tbody>
		</table>';

	if (isset($_GET['DebtorNo'])) {
		$DebtorNo = trim(mb_strtoupper($_GET['DebtorNo']));
	} elseif (isset($_POST['DebtorNo'])) {
		$DebtorNo = trim(mb_strtoupper($_POST['DebtorNo']));
	} elseif (isset($_SESSION['CustomerID'])) {
		$DebtorNo = $_SESSION['CustomerID'];
	}

	if ($DebtorNo) {
		/* display recent pricing history for this debtor and this stock item */

		$SQL = "SELECT stockmoves.trandate,
				stockmoves.qty,
				stockmoves.price,
				stockmoves.discountpercent
			FROM stockmoves
			WHERE stockmoves.debtorno='" . $DebtorNo . "'
				AND stockmoves.type=10
				AND stockmoves.stockid = '" . $StockId . "'
				AND stockmoves.hidemovt=0
			ORDER BY stockmoves.trandate DESC";

		/* only show pricing history for sales invoices - type=10 */

		$ErrMsg = _('The stock movements for the selected criteria could not be retrieved because') . ' - ';
		$DbgMsg = _('The SQL that failed was');

		$MovtsResult = DB_query($SQL, $ErrMsg, $DbgMsg);

		$k = 1;
		$LastPrice = 0;
		while ($MyRow = DB_fetch_array($MovtsResult)) {
			if ($LastPrice != $MyRow['price'] or $LastDiscount != $MyRow['discount']) {
				/* consolidate price history for records with same price/discount */
				if (isset($Quantity)) {
					$DateRange = ConvertSQLDate($FromDate);
					if ($FromDate != $ToDate) {
						$DateRange.= ' - ' . ConvertSQLDate($ToDate);
					}
					$PriceHistory[] = array($DateRange, $Quantity, $LastPrice, $LastDiscount);
					++$k;
					if ($k > 9) {
						break;
						/* 10 price records is enough to display */
					}
					if ($MyRow['trandate'] < FormatDateForSQL(DateAdd(date($_SESSION['DefaultDateFormat']), 'y', -1))) {
						break;
						/* stop displaying pirce history more than a year old once we have at least one  to display */
					}
				}
				$LastPrice = $MyRow['price'];
				$LastDiscount = $MyRow['discountpercent'];
				$ToDate = $MyRow['trandate'];
				$Quantity = 0;
			}
			$Quantity+= $MyRow['qty'];
			$FromDate = $MyRow['trandate'];
		}
		if (isset($Quantity)) {
			$DateRange = ConvertSQLDate($FromDate);
			if ($FromDate != $ToDate) {
				$DateRange.= ' - ' . ConvertSQLDate($ToDate);
			}
			$PriceHistory[] = array($DateRange, $Quantity, $LastPrice, $LastDiscount);
		}
		if (isset($PriceHistory)) {
			echo '<table>
					<thead>
						<tr>
							<th colspan="4"><font color="navy" size="2">', _('Pricing history for sales of'), ' ', $StockId, ' ', _('to'), ' ', $DebtorNo, '</font></th>
						</tr>
						<tr>
							<th class="SortedColumn">', _('Date Range'), '</th>
							<th>', _('Quantity'), '</th>
							<th>', _('Price'), '</th>
							<th>', _('Discount'), '</th>
						</tr>
					</thead>';

			echo '<tbody>';
			foreach ($PriceHistory as $PreviousPrice) {
				echo '<tr class="striped_row">
						<td>', $PreviousPrice[0], '</td>
						<td class="number">', locale_number_format($PreviousPrice[1], $DecimalPlaces), '</td>
						<td class="number">', locale_number_format($PreviousPrice[2], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
						<td class="number">', locale_number_format($PreviousPrice[3] * 100, 2), '%</td>
					</tr>';
			}
			echo '</tbody>
			</table>';
		}
		//end of while loop
		else {
			echo '<p>' . _('No history of sales of') . ' ' . $StockId . ' ' . _('to') . ' ' . $DebtorNo;
		}
	} //end of displaying price history for a debtor
	echo '<div class="centre">
			<a href="', $RootPath, '/StockMovements.php?StockID=', urlencode($StockId), '">', _('Show Movements'), '</a><br />
			<a href="', $RootPath, '/StockUsage.php?StockID=', urlencode($StockId), '">', _('Show Usage'), '</a><br />
			<a href="', $RootPath, '/SelectSalesOrder.php?SelectedStockItem=', urlencode($StockId), '">', _('Search Outstanding Sales Orders'), '</a><br />
			<a href="', $RootPath, '/SelectCompletedOrder.php?SelectedStockItem=', urlencode($StockId), '">', _('Search Completed Sales Orders'), '</a>
		</div>';
	if ($Its_A_KitSet_Assembly_Or_Dummy == False) {
		echo '<a href="', $RootPath, '/PO_SelectOSPurchOrder.php?SelectedStockItem=', urlencode($StockId), '">', _('Search Outstanding Purchase Orders'), '</a>';
	}

	echo '</form>';
}
include ('includes/footer.php');

?>