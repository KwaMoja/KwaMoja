<?php
$PricesSecurity = 1000; //don't show pricing info unless security token 1000 available to user
$CostSecurity = 1002; //don't show cost info unless security token 1002 available to user
$SuppliersSecurity = 9; //don't show supplier purchasing info unless security token 9 available to user
include ('includes/session.php');
include ('includes/SQL_CommonFunctions.php');
$Title = _('Search Inventory Items');
/* Manual links before header.php */
$ViewTopic = 'Inventory';
$BookMark = 'SelectingInventory';

include ('includes/header.php');

if (isset($_GET['StockID'])) {
	//The page is called with a StockID
	$_GET['StockID'] = trim(mb_strtoupper($_GET['StockID']));
	$_POST['Select'] = trim(mb_strtoupper($_GET['StockID']));
}

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/inventory.png" title="', _('Inventory Items'), '" alt="" />', ' ', _('Inventory Items'), '
	</p>';

if (isset($_GET['NewSearch']) or isset($_POST['Next']) or isset($_POST['Previous']) or isset($_POST['Go'])) {
	unset($StockId);
	unset($_SESSION['SelectedStockItem']);
	unset($_POST['Select']);
}

if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} elseif ($_POST['PageOffset'] == 0) {
	$_POST['PageOffset'] = 1;
}

if (isset($_POST['StockCode'])) {
	$_POST['StockCode'] = trim(mb_strtoupper($_POST['StockCode']));
}

// Always show the search facilities
$SQL = "SELECT SQL_CACHE categoryid,
				categorydescription
		FROM stockcategory
		ORDER BY categorydescription";
$Result1 = DB_query($SQL);
if (DB_num_rows($Result1) == 0) {
	prnMsg(_('There are no stock categories currently defined please use the link below to set them up') . '<br /><a href="' . $RootPath . '/StockCategories.php">' . _('Define Stock Categories') . '</a>', 'warn');
	include ('includes/footer.php');
	exit;
}
// end of showing search facilities
/* displays item options if there is one and only one selected */
if (!isset($_POST['Search']) and (isset($_POST['Select']) or isset($_SESSION['SelectedStockItem']))) {
	if (isset($_POST['Select'])) {
		$_SESSION['SelectedStockItem'] = $_POST['Select'];
		$StockId = $_POST['Select'];
		unset($_POST['Select']);
	} else {
		$StockId = $_SESSION['SelectedStockItem'];
	}
	$SQL = "SELECT stockdescriptiontranslations.descriptiontranslation AS description,
					stocklongdescriptiontranslations.longdescriptiontranslation AS longdescription,
					stockmaster.mbflag,
					stockcategory.stocktype,
					stockmaster.units,
					stockmaster.decimalplaces,
					stockmaster.controlled,
					stockmaster.serialised,
					stockcosts.materialcost+stockcosts.labourcost+stockcosts.overheadcost AS cost,
					stockmaster.discontinued,
					stockmaster.eoq,
					stockmaster.volume,
					stockmaster.grossweight,
					stockcategory.categorydescription,
					stockmaster.categoryid
				FROM stockmaster
				INNER JOIN stockdescriptiontranslations
					ON stockdescriptiontranslations.stockid=stockmaster.stockid
				INNER JOIN stocklongdescriptiontranslations
					ON stocklongdescriptiontranslations.stockid=stockmaster.stockid
				INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
				LEFT JOIN stockcosts
					ON stockmaster.stockid=stockcosts.stockid
					AND stockcosts.succeeded=0
				WHERE stockcosts.stockid='" . $StockId . "'
					AND stockdescriptiontranslations.language_id='" . $_SESSION['InventoryLanguage'] . "'
					AND stocklongdescriptiontranslations.language_id='" . $_SESSION['InventoryLanguage'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$Its_A_Kitset_Assembly_Or_Dummy = false;
	$Its_A_Dummy = false;
	$Its_A_Kitset = false;
	$Its_A_Labour_Item = false;
	if ($MyRow['discontinued'] == 1) {
		$ItemStatus = '<p class="bad">' . _('Obsolete') . '</p>';
	} else {
		$ItemStatus = '';
	}

	echo '<table>
			<tr>
				<th colspan="5">
					<b>', _('Details for inventory item'), ' - ', ' ', $StockId, ' - ', $MyRow['description'], '</b> ', $ItemStatus, '
				</th>
			</tr>
			<tr>
				<td>
				<table>'; //nested table
	$SQL = "SELECT abccategory FROM abcstock WHERE stockid='" . $StockId . "'";
	$ABCResult = DB_query($SQL);
	$ABCRow = DB_fetch_array($ABCResult);
	echo '<tr class="striped_row">
			<th class="number">', _('Category'), ':</th>
			<td colspan="4">', $MyRow['categorydescription'], '</td>
			<th class="number">', _('ABC Rank'), ':</th>
			<td>', $ABCRow['abccategory'], '</td>
		</tr>';
	echo '<tr class="striped_row">
			<th class="number">', _('Item Type'), ':</th>
			<td colspan="2">';
	switch ($MyRow['mbflag']) {
		case 'A':
			echo _('Assembly Item'), '</td>';
			$Its_A_Kitset_Assembly_Or_Dummy = true;
		break;
		case 'G':
			echo _('Phantom Assembly Item');
			$Its_A_Kitset_Assembly_Or_Dummy = true;
			$Its_A_Kitset = true;
		break;
		case 'K':
			echo _('Kitset Item'), '</td>';
			$Its_A_Kitset_Assembly_Or_Dummy = true;
			$Its_A_Kitset = true;
		break;
		case 'D':
			echo _('Service/Labour Item'), '</td>';
			$Its_A_Kitset_Assembly_Or_Dummy = true;
			$Its_A_Dummy = true;
			if ($MyRow['stocktype'] == 'L') {
				$Its_A_Labour_Item = true;
			}
		break;
		case 'B':
			echo _('Purchased Item'), '</td>';
		break;
		default:
			echo _('Manufactured Item'), '</td>';
		break;
	}
	echo '<th class="number">', _('Control Level'), ':</th>
			<td>';
	if ($MyRow['serialised'] == 1) {
		echo _('serialised'), '</td>';
	} elseif ($MyRow['controlled'] == 1) {
		echo _('Batchs/Lots'), '</td>';
	} else {
		echo _('N/A'), '</td>';
	}
	echo '<th class="number">', _('Units'), ':</th>
			<td>', $MyRow['units'], '</td>
		</tr>';
	echo '<tr class="striped_row">
			<th>', _('Volume'), ':</th>
			<td class="number" colspan="2">', locale_number_format($MyRow['volume'], 3), '</td>
			<th>', _('Weight'), ':</th>
			<td class="number">', locale_number_format($MyRow['grossweight'], 3), '</td>
			<th>', _('EOQ'), ':</th>
			<td class="number">', locale_number_format($MyRow['eoq'], $MyRow['decimalplaces']), '</td>
		</tr>';
	if (in_array($PricesSecurity, $_SESSION['AllowedPageSecurityTokens']) or !isset($PricesSecurity)) {
		$PriceResult = DB_query("SELECT sales_type,
										currabrev,
										price
								FROM prices
								INNER JOIN salestypes
									ON prices.typeabbrev=salestypes.typeabbrev
								WHERE currabrev ='" . $_SESSION['CompanyRecord']['currencydefault'] . "'
									AND debtorno=''
									AND branchcode=''
									AND startdate <= CURRENT_DATE AND ( enddate >= CURRENT_DATE OR enddate = '0000-00-00')
									AND stockid='" . $StockId . "'");
		if ($MyRow['mbflag'] == 'K' or $MyRow['mbflag'] == 'A' or $MyRow['mbflag'] == 'G') {
			$CostResult = DB_query("SELECT SUM(bom.quantity * (stockcosts.materialcost+stockcosts.labourcost+stockcosts.overheadcost)) AS cost
									FROM bom
									LEFT JOIN stockcosts
										ON bom.component=stockcosts.stockid
										AND stockcosts.succeeded=0
									WHERE bom.parent='" . $StockId . "'
										AND bom.effectiveto > CURRENT_DATE
										AND bom.effectiveafter <= CURRENT_DATE");
			$CostRow = DB_fetch_row($CostResult);
			$Cost = $CostRow[0];
		} else {
			$Cost = $MyRow['cost'];
		}
		if (DB_num_rows($PriceResult) == 0) {
			echo '<tr class="striped_row">
					<th class="number">', _('Sell Price'), ':</th>
					<td>', _('No Default Price Set'), '</td>
				</tr>';
			$Price = 0;
		} else {
			while ($PriceRow = DB_fetch_array($PriceResult)) {
				$Price = $PriceRow['price'];
				if ($Price > 0) {
					$GP = locale_number_format(($Price - $Cost) * 100 / $Price, 1);
				} else {
					$GP = _('N/A');
				}
				echo '<tr class="striped_row">
						<td class="number">', _('Sell Price'), ':</td>
						<td>', $PriceRow['sales_type'], '</td>
						<td class="number">', locale_number_format($Price, $_SESSION['CompanyRecord']['decimalplaces']), ' ', $PriceRow['currabrev'], '</td>
						<td class="number">', _('Gross Profit'), ':</td>
						<td>', $GP, '%</td>
					</tr>';
			}
		}
	} //end of if PricesSecurity allows viewing of prices
	if (in_array($CostSecurity, $_SESSION['AllowedPageSecurityTokens'])) {
		if ($MyRow['mbflag'] == 'K' or $MyRow['mbflag'] == 'A') {
			$CostResult = DB_query("SELECT SUM(bom.quantity * (stockcosts.materialcost+stockcosts.labourcost+stockcosts.overheadcost)) AS cost
									FROM bom
									LEFT JOIN stockcosts
										ON bom.component=stockcosts.stockid
										AND stockcosts.succeeded=0
									WHERE bom.parent='" . $StockId . "'
										AND bom.effectiveto > CURRENT_DATE
										AND bom.effectiveafter <= CURRENT_DATE");
			$CostRow = DB_fetch_row($CostResult);
			$Cost = $CostRow[0];
		} else {
			$Cost = $MyRow['cost'];
		}
		echo '<tr class="striped_row">
				<th class="number">', _('Cost'), ':</th>
				<td class="number">', locale_number_format($Cost, $_SESSION['StandardCostDecimalPlaces']), '</td>
			</tr>';
	}

	echo '</table>'; //end of first nested table
	// Item Category Property mod: display the item properties
	echo '<table>';
	$SQL = "SELECT stkcatpropid,
					label,
					controltype,
					defaultvalue
				FROM stockcatproperties
				WHERE categoryid ='" . $MyRow['categoryid'] . "'
				AND reqatsalesorder =0
				ORDER BY stkcatpropid";
	$PropertiesResult = DB_query($SQL);
	$PropertyCounter = 0;
	$PropertyWidth = array();
	while ($PropertyRow = DB_fetch_array($PropertiesResult)) {
		$PropValResult = DB_query("SELECT value
									FROM stockitemproperties
									WHERE stockid='" . $StockId . "'
									AND stkcatpropid ='" . $PropertyRow['stkcatpropid'] . "'");
		$PropValRow = DB_fetch_row($PropValResult);
		if (DB_num_rows($PropValResult) == 0) {
			$PropertyValue = _('Not Set');
		} else {
			$PropertyValue = $PropValRow[0];
		}
		echo '<tr class="striped_row">
				<th align="right">', $PropertyRow['label'], ':</th>';
		switch ($PropertyRow['controltype']) {
			case 0:
			case 1:
				echo '<td>', $PropertyValue, '</td>';
			break;
			case 2; //checkbox
			echo '<td>';
			if ($PropertyValue == _('Not Set')) {
				echo _('Not Set'), '</td>';
			} elseif ($PropertyValue == 1) {
				echo _('Yes'), '</td>';
			} else {
				echo _('No'), '</td>';
			}
		break;
	} //end switch
	echo '</tr>';
	++$PropertyCounter;
} //end loop round properties for the item category
echo '</table>
			</td>'; //end of Item Category Property mod
echo '<td style="width:15%; vertical-align:top">
			<table>'; //nested table to show QOH/orders
$QOH = 0;
$QOO = 0;
switch ($MyRow['mbflag']) {
	case 'A':
	case 'D':
	case 'K':
		$QOH = _('N/A');
		$QOO = _('N/A');
	break;
	case 'M':
	case 'B':
		$QOHResult = DB_query("SELECT sum(quantity)
						FROM locstock
						WHERE stockid = '" . $StockId . "'");
		$QOHRow = DB_fetch_row($QOHResult);
		$QOH = locale_number_format($QOHRow[0], $MyRow['decimalplaces']);

		// Get the QOO due to Purchase orders for all locations. Function defined in SQL_CommonFunctions.php
		$QOO = GetQuantityOnOrderDueToPurchaseOrders($StockId);
		// Get the QOO dues to Work Orders for all locations. Function defined in SQL_CommonFunctions.php
		$QOO+= GetQuantityOnOrderDueToWorkOrders($StockId);

		$QOO = locale_number_format($QOO, $MyRow['decimalplaces']);
	break;
}
$Demand = 0;
$DemSql = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
					FROM salesorderdetails
					INNER JOIN salesorders
						ON salesorders.orderno = salesorderdetails.orderno
					INNER JOIN locationusers
						ON locationusers.loccode=salesorders.fromstkloc
						AND locationusers.userid='" . $_SESSION['UserID'] . "'
						AND locationusers.canview=1
					WHERE salesorderdetails.completed=0
						AND salesorders.quotation=0
						AND salesorderdetails.stkcode='" . $StockId . "'";
$DemResult = DB_query($DemSql);
$DemRow = DB_fetch_row($DemResult);
$Demand = $DemRow[0];
$DemAsComponentSql = "SELECT SUM((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
							FROM salesorderdetails
							INNER JOIN salesorders
								ON salesorders.orderno = salesorderdetails.orderno
							INNER JOIN bom
								ON salesorderdetails.stkcode=bom.parent
							INNER JOIN stockmaster
								ON stockmaster.stockid=bom.parent
							INNER JOIN locationusers
								ON locationusers.loccode=salesorders.fromstkloc
								AND locationusers.userid='" . $_SESSION['UserID'] . "'
								AND locationusers.canview=1
							WHERE salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
								AND bom.component='" . $StockId . "'
								AND stockmaster.mbflag='A'
								AND salesorders.quotation=0";
$DemAsComponentResult = DB_query($DemAsComponentSql);
$DemAsComponentRow = DB_fetch_row($DemAsComponentResult);
$Demand+= $DemAsComponentRow[0];
//Also the demand for the item as a component of works orders
$SQL = "SELECT SUM(qtypu*(woitems.qtyreqd - woitems.qtyrecd)) AS woqtydemo
				FROM woitems
				INNER JOIN worequirements
					ON woitems.stockid=worequirements.parentstockid
				INNER JOIN workorders
					ON woitems.wo=workorders.wo
					AND woitems.wo=worequirements.wo
				INNER JOIN locationusers
					ON locationusers.loccode=workorders.loccode
					AND locationusers.userid='" . $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				WHERE  worequirements.stockid='" . $StockId . "'
					AND workorders.closed=0";
$ErrMsg = _('The workorder component demand for this product cannot be retrieved because');
$DemandResult = DB_query($SQL, $ErrMsg);
if (DB_num_rows($DemandResult) == 1) {
	$DemandRow = DB_fetch_row($DemandResult);
	$Demand+= $DemandRow[0];
}
echo '<tr class="striped_row">
			<th class="number">', _('Quantity On Hand'), ':</th>
			<td class="number">', $QOH, '</td>
		</tr>';
echo '<tr class="striped_row">
			<th class="number">', _('Quantity Demand'), ':</th>
			<td class="number">', locale_number_format($Demand, $MyRow['decimalplaces']), '</td>
		</tr>';
echo '<tr class="striped_row">
			<th class="number">', _('Quantity On Order'), ':</th>
			<td class="number">', $QOO, '</td>
		</tr>
	</table>'; //end of nested table
echo '</td>'; //end cell of master table
if (($MyRow['mbflag'] == 'B' or ($MyRow['mbflag'] == 'M')) and (in_array($SuppliersSecurity, $_SESSION['AllowedPageSecurityTokens']))) {

	echo '<td style="width:50%" valign="top">
				<table>
					<tr>
						<th>', _('Supplier'), '</th>
						<th>', _('Price'), '</th>
						<th>', _('Qty Greater Than'), '</th>
						<th>', _('Curr'), '</th>
						<th>', _('Eff Date'), '</th>
						<th>', _('Lead Time'), '</th>
						<th>', _('Min Order Qty'), '</th>
						<th>', _('Prefer'), '</th>
						<th colspan="2"></th>
					</tr>';
	$SuppResult = DB_query("SELECT suppliers.suppname,
									suppliers.currcode,
									suppliers.supplierid,
									purchdata.price,
									purchdata.qtygreaterthan,
									purchdata.effectivefrom,
									purchdata.leadtime,
									purchdata.conversionfactor,
									purchdata.minorderqty,
									purchdata.preferred,
									currencies.decimalplaces
								FROM purchdata
								INNER JOIN suppliers
									ON purchdata.supplierno=suppliers.supplierid
								INNER JOIN currencies
									ON suppliers.currcode=currencies.currabrev
								WHERE purchdata.stockid = '" . $StockId . "'
								ORDER BY purchdata.preferred DESC,
								purchdata.effectivefrom DESC,
								purchdata.qtygreaterthan ASC");
	while ($SuppRow = DB_fetch_array($SuppResult)) {
		echo '<tr class="striped_row">
					<td>', $SuppRow['suppname'], '</td>
					<td>', locale_number_format($SuppRow['price'] / $SuppRow['conversionfactor'], $SuppRow['decimalplaces']), '</td>
					<td class="number">', locale_number_format($SuppRow['qtygreaterthan'], 'Variable'), '</td>
					<td>', $SuppRow['currcode'], '</td>
					<td>', ConvertSQLDate($SuppRow['effectivefrom']), '</td>
					<td class="number">', $SuppRow['leadtime'], '</td>
					<td class="number">', $SuppRow['minorderqty'], '</td>';

		if ($SuppRow['preferred'] == 1) { //then this is the preferred supplier
			echo '<td>', _('Yes'), '</td>';
		} else {
			echo '<td>', _('No'), '</td>';
		}
		echo '<td><a href="' . $RootPath . '/PurchData.php?StockID=', urlencode($StockId), '&SupplierID=', urlencode($SuppRow['supplierid']), '&Edit=1&EffectiveFrom=', urlencode($SuppRow['effectivefrom']), '">' . _('Edit') . ' </a></td>';
		echo '<td><a href="', $RootPath, '/PO_Header.php?NewOrder=Yes&amp;SelectedSupplier=', urlencode($SuppRow['supplierid']), '&amp;StockID=', urlencode($StockId), '&amp;Quantity=', urlencode($SuppRow['minorderqty']), '&amp;LeadTime=', urlencode($SuppRow['leadtime']), '">', _('Order'), ' </a></td>';
		echo '</tr>';
	}
	echo '</table>';
	DB_data_seek($Result, 0);
}
echo '</td>
		</tr>
	</table>'; // end first item details table
$UrlEncodedStockId = urlencode($StockId);
echo '<fieldset style="text-align:center">';
// Customer inquiries options:
echo '<fieldset class="MenuList">
			<legend><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/reports.png" data-title="', _('Inquiries and Reports'), '" />', _('Item Inquiries'), '</legend>
			<ul>
				<li class="MenuItem">
					<a href="', $RootPath, '/StockMovements.php?StockID=', $UrlEncodedStockId, '">', _('Show Stock Movements'), '</a>
				</li>';
if ($Its_A_Kitset_Assembly_Or_Dummy == false) {
	echo '<li class="MenuItem">
				<a href="', $RootPath, '/StockStatus.php?StockID=', $UrlEncodedStockId, '">', _('Show Stock Status'), '</a>
			</li>
			<li class="MenuItem">
				<a href="', $RootPath, '/StockUsage.php?StockID=', $UrlEncodedStockId, '">', _('Show Stock Usage'), '</a>
			</li>
			<li class="MenuItem">
				<a href="', $RootPath, '/PO_SelectOSPurchOrder.php?SelectedStockItem=', $UrlEncodedStockId, '">', _('Search Outstanding Purchase Orders'), '</a>
			</li>
			<li class="MenuItem">
				<a href="', $RootPath, '/PO_SelectPurchOrder.php?SelectedStockItem=', $UrlEncodedStockId, '">', _('Search All Purchase Orders'), '</a>
			</li>';
	$SupportedImgExt = array('png', 'jpg', 'jpeg');
	$ImageFileArray = glob($_SESSION['part_pics_dir'] . '/' . $StockId . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE);
	$ImageFile = reset($ImageFileArray);
	echo '<li class="MenuItem">
				<a href="', $RootPath, '/', $ImageFile, '" target="_blank">', _('Show Part Picture (if available)'), '</a>
			</li>';
}
echo '<li class="MenuItem">
			<a href="', $RootPath, '/SelectSalesOrder.php?SelectedStockItem=', $UrlEncodedStockId, '">', _('Search Outstanding Sales Orders'), '</a>
		</li>
		<li class="MenuItem">
			<a href="', $RootPath, '/SelectCompletedOrder.php?SelectedStockItem=', $UrlEncodedStockId, '">', _('Search Completed Sales Orders'), '</a>
		</li>';
if ($Its_A_Dummy == false) {
	echo '<li class="MenuItem">
				<a href="', $RootPath, '/BOMInquiry.php?StockID=', $UrlEncodedStockId . '">', _('View Costed Bill Of Material'), '</a>
			</li>
			<li class="MenuItem">
				<a href="', $RootPath, '/WhereUsedInquiry.php?StockID=', $UrlEncodedStockId, '">', _('Where This Item Is Used'), '</a>
			</li>';
}
if ($Its_A_Labour_Item == true) {
	echo '<li class="MenuItem">
				<a href="', $RootPath, '/WhereUsedInquiry.php?StockID=', $UrlEncodedStockId, '">', _('Where This Labour Item Is Used'), '</a>
			</li>';
}
echo '<li class="MenuItem">
			', wikiLink('Product', $StockId), '
		</li>
	</ul>
</fieldset>';

/* Stock Transactions */
echo '<fieldset class="MenuList">
			<legend><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/transactions.png" data-title="', _('Item Transactions'), '" />', _('Item Transactions'), '</legend>
			<ul>';
if ($Its_A_Kitset_Assembly_Or_Dummy == false) {
	echo '<li class="MenuItem">
				<a href="', $RootPath, '/StockAdjustments.php?StockID=', $UrlEncodedStockId, '">', _('Quantity Adjustments'), '</a>
			</li>
			<li class="MenuItem">
				<a href="', $RootPath, '/StockTransfers.php?StockID=', $UrlEncodedStockId, '&amp;NewTransfer=true">', _('Location Transfers'), '</a>
			</li>';
	//show the item image if it has been uploaded
	if (extension_loaded('gd') and function_exists('gd_info') and file_exists($ImageFile)) {
		if ($_SESSION['ShowStockidOnImages'] == '0') {
			$StockImgLink = '<img src="GetStockImage.php?automake=1&amp;textcolor=FFFFFF&amp;bgcolor=CCCCCC' . '&amp;StockID=' . $UrlEncodedStockId . '&amp;text=' . '&amp;width=200' . '&amp;height=200' . '" alt="" />';
		} else if (file_exists($ImageFile)) {
			$StockImgLink = '<img src="' . $ImageFile . '" height="100" width="100" />';
		} else {
			$StockImgLink = '<img src="GetStockImage.php?automake=1&amp;textcolor=FFFFFF&amp;bgcolor=CCCCCC' . '&amp;StockID=' . $UrlEncodedStockId . '&amp;text=' . $UrlEncodedStockId . '&amp;width=200' . '&amp;height=200' . '" alt="" />';
		}
	} else {
		if (isset($StockId) and file_exists($_SESSION['part_pics_dir'] . '/' . $StockId . '.jpg')) {
			$StockImgLink = '<img src="' . urlencode($_SESSION['part_pics_dir']) . '/' . urlencode($StockId) . '.jpg" height="200" width="200" />';
		} else {
			$StockImgLink = _('No Image');
		}
	}

	echo '<div class="centre">', $StockImgLink, '</div>';

	if (($MyRow['mbflag'] == 'B') and (in_array($SuppliersSecurity, $_SESSION['AllowedPageSecurityTokens'])) and $MyRow['discontinued'] == 0) {
		$SuppResult = DB_query("SELECT suppliers.suppname,
											suppliers.supplierid,
											purchdata.preferred,
											purchdata.minorderqty,
											purchdata.leadtime
										FROM purchdata
										INNER JOIN suppliers
											ON purchdata.supplierno=suppliers.supplierid
										WHERE purchdata.stockid='" . $StockId . "'
										ORDER BY purchdata.effectivefrom DESC");
		$LastSupplierShown = "";
		while ($SuppRow = DB_fetch_array($SuppResult)) {
			if ($LastSupplierShown != $SuppRow['supplierid']) {
				if (($MyRow['eoq'] < $SuppRow['minorderqty'])) {
					$EOQ = $SuppRow['minorderqty'];
				} else {
					$EOQ = $MyRow['eoq'];
				}
				echo '<li class="MenuItem">
							<a href="', $RootPath, '/PO_Header.php?NewOrder=Yes', '&amp;SelectedSupplier=', urlencode($SuppRow['supplierid']), '&amp;StockID=', $UrlEncodedStockId, '&amp;Quantity=', urlencode($EOQ), '&amp;LeadTime=', urlencode($SuppRow['leadtime']), '">', _('Purchase this Item from'), ' ', $SuppRow['suppname'], '</a>
						</li>';
				$LastSupplierShown = $SuppRow['supplierid'];
			}
			/**/
		}
		/* end of while */
	}
	/* end of $MyRow['mbflag'] == 'B' */
}
/* end of ($Its_A_Kitset_Assembly_Or_Dummy == false) */
echo '</fieldset>';

/* Stock Maintenance Options */
echo '<fieldset class="MenuList">
			<legend><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" data-title="', _('Supplier Maintenance'), '" />', _('Supplier Maintenance'), '</legend>
			<ul>
				<li class="MenuItem">
					<a href="', $RootPath, '/Stocks.php">', _('Insert New Item'), '</a>
				</li>
				<li class="MenuItem">
					<a href="', $RootPath, '/Stocks.php?StockID=', $UrlEncodedStockId, '">', _('Modify Item Details'), '</a>
				</li>';

if ($Its_A_Kitset_Assembly_Or_Dummy == false) {
	echo '<li class="MenuItem">
				<a href="', $RootPath, '/StockReorderLevel.php?StockID=', $UrlEncodedStockId, '">', _('Maintain Reorder Levels'), '</a>
			</li>
			<li class="MenuItem">
				<a href="', $RootPath, '/StockCostUpdate.php?StockID=', $UrlEncodedStockId, '">', _('Maintain Standard Cost'), '</a>
			</li>
			<li class="MenuItem">
				<a href="', $RootPath, '/PurchData.php?StockID=', $UrlEncodedStockId, '">', _('Maintain Purchasing Data'), '</a>
			</li>
			<li class="MenuItem">
				<a href="', $RootPath, '/CustItem.php?StockID=', $UrlEncodedStockId, '">', _('Maintain Customer Item Data'), '</a>
			</li>';
}
if ($Its_A_Labour_Item == true) {
	echo '<li class="MenuItem">
				<a href="', $RootPath, '/StockCostUpdate.php?StockID=', $UrlEncodedStockId, '">', _('Maintain Standard Cost'), '</a>
			</li>';
}
if (!$Its_A_Kitset) {
	echo '<li class="MenuItem">
				<a href="', $RootPath, '/Prices.php?Item=', $UrlEncodedStockId, '">', _('Maintain Pricing'), '</a>
			</li>';
	if (isset($_SESSION['CustomerID']) and $_SESSION['CustomerID'] != '' and mb_strlen($_SESSION['CustomerID']) > 0) {
		echo '<li class="MenuItem">
					<a href="', $RootPath, '/Prices_Customer.php?Item=', $UrlEncodedStockId, '">', _('Special Prices for customer'), ' - ', stripslashes($_SESSION['CustomerID']), '</a>
				</li>';
	}
}
echo '<li class="MenuItem">
			<a href="', $RootPath, '/DiscountCategories.php?StockID=', $UrlEncodedStockId, '">', _('Maintain Discount Category'), '</a>
		</li>
		<li class="MenuItem">
			<a href="', $RootPath, '/Stocks.php?Clone=', $UrlEncodedStockId . '">', _('Clone This Item'), '</a>
		</li>
		<li class="MenuItem">
			<a href="', $RootPath, '/RelatedItemsUpdate.php?Item=', $UrlEncodedStockId, '">', _('Maintain Related Items'), '</a>
		</li>
		<li class="MenuItem">
			<a href="', $RootPath, '/PriceMatrix.php?StockID=', $UrlEncodedStockId, '">', _('Mantain prices by quantity break and sales types'), '</a>
		</li>
	</ul>';
echo '</fieldset>
		</fieldset>';
}

echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', _('Search'), '" alt="" />', ' ', _('Search for Inventory Items'), '
	</p>';

echo '<fieldset>
		<legend>', _('Item Search Criteria'), '</legend>
		<field>
			<label for="StockCat">', _('In Stock Category'), ':</label>
			<select name="StockCat">';
if (!isset($_POST['StockCat'])) {
	$_POST['StockCat'] = '';
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
		<label for="Keywords">', _('Enter partial'), '<b> ', _('Description'), '</b>:</label>';
if (isset($_POST['Keywords'])) {
	echo '<input type="search" name="Keywords" value="', $_POST['Keywords'], '" size="20" maxlength="25" />';
} else {
	echo '<input type="search" name="Keywords" size="20" maxlength="25" />';
}
echo '</field>';

echo '<h1>', _('OR'), '</h1>';

echo '<field>
		<label for="StockCode">', _('Enter partial'), ' <b>', _('Stock Code'), '</b>:</label>';
if (isset($_POST['StockCode'])) {
	echo '<input type="search" name="StockCode" autofocus="autofocus" value="', $_POST['StockCode'], '" size="15" maxlength="18" />';
} else {
	echo '<input type="search" name="StockCode" autofocus="autofocus" size="15" maxlength="18" />';
}
echo '</field>';

echo '<h1>', _('OR'), '</h1>';

echo '<field>
		<label for="SupplierStockCode">', _('Enter partial'), ' <b>', _('Supplier Stock Code'), '</b>:</label>';
if (isset($_POST['SupplierStockCode'])) {
	echo '<input type="search" name="SupplierStockCode" autofocus="autofocus" value="', $_POST['SupplierStockCode'], '" size="15" maxlength="18" />';
} else {
	echo '<input type="search" name="SupplierStockCode" autofocus="autofocus" size="15" maxlength="18" />';
}
echo '</field>
	</fieldset>';

echo '<div class="centre">
		<input type="submit" name="Search" value="', _('Search Now'), '" />
	</div>';

echo '</form>';

// query for list of record(s)
if (isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Previous'])) {
	$_POST['Search'] = 'Search';
}
if (isset($_POST['Search']) or isset($_POST['Go']) or isset($_POST['Next']) or isset($_POST['Previous'])) {
	if (!isset($_POST['Go']) and !isset($_POST['Next']) and !isset($_POST['Previous'])) {
		// if Search then set to first page
		$_POST['PageOffset'] = 1;
	}
	if ($_POST['Keywords'] and $_POST['StockCode']) {
		prnMsg(_('Stock description keywords have been used in preference to the Stock code extract entered'), 'info');
	}
	//insert wildcard characters in spaces
	$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
	$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
	if ($_POST['StockCat'] == 'All') {
		$_POST['StockCat'] = '%';
	}
	if ($_POST['SupplierStockCode'] != '') {
		$SupplierCodeSQL = " AND purchdata.suppliers_partno " . LIKE . " '%" . $_POST['SupplierStockCode'] . "%' ";
	} else {
		$SupplierCodeSQL = " ";
	}
	$SQL = "SELECT stockmaster.stockid,
					purchdata.suppliers_partno,
					stockdescriptiontranslations.descriptiontranslation AS description,
					stocklongdescriptiontranslations.longdescriptiontranslation AS longdescription,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units,
					stockmaster.mbflag,
					stockmaster.discontinued,
					stockmaster.decimalplaces,
					suppliers.suppname
				FROM stockmaster
				LEFT JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
				LEFT JOIN locstock
					ON stockmaster.stockid=locstock.stockid
				LEFT JOIN purchdata
					ON stockmaster.stockid=purchdata.stockid
				LEFT JOIN suppliers
					ON purchdata.supplierno=suppliers.supplierid
				LEFT JOIN stockdescriptiontranslations
					ON stockmaster.stockid=stockdescriptiontranslations.stockid
				LEFT JOIN stocklongdescriptiontranslations
					ON stockmaster.stockid=stocklongdescriptiontranslations.stockid
				WHERE stockdescriptiontranslations.descriptiontranslation " . LIKE . " '" . $SearchString . "'
					AND stockdescriptiontranslations.language_id='" . $_SESSION['InventoryLanguage'] . "'
					AND stocklongdescriptiontranslations.language_id='" . $_SESSION['InventoryLanguage'] . "'
					AND stockmaster.categoryid " . LIKE . " '" . $_POST['StockCat'] . "'
					AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'" . $SupplierCodeSQL . "
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.longdescription,
					stockmaster.units,
					stockmaster.mbflag,
					stockmaster.discontinued,
					stockmaster.decimalplaces
				ORDER BY stockmaster.discontinued, stockmaster.stockid";
	$ErrMsg = _('No stock items were returned by the SQL because');
	$DbgMsg = _('The SQL that returned an error was');
	$SearchResult = DB_query($SQL, $ErrMsg, $DbgMsg);
	if (DB_num_rows($SearchResult) == 0) {
		prnMsg(_('No stock items were returned by this search please re-enter alternative criteria to try again'), 'info');
	}
	unset($_POST['Search']);
}
/* end query for list of records */
/* display list if there is more than one record */
if (isset($SearchResult) and !isset($_POST['Select'])) {
	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	$ListCount = DB_num_rows($SearchResult);
	if ($ListCount > 0) {
		// If the user hit the search button and there is more than one item to show
		$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
		if (isset($_POST['Next'])) {
			if ($_POST['PageOffset'] < $ListPageMax) {
				$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
			}
		}
		if (isset($_POST['Previous'])) {
			if ($_POST['PageOffset'] > 1) {
				$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
			}
		}
		if ($_POST['PageOffset'] > $ListPageMax) {
			$_POST['PageOffset'] = $ListPageMax;
		}
		if ($ListPageMax > 1) {
			echo '<div class="centre">&nbsp;&nbsp;', $_POST['PageOffset'], ' ', _('of'), ' ', $ListPageMax, ' ', _('pages'), '. ', _('Go to Page'), ': ';
			echo '<select name="PageOffset">';
			$ListPage = 1;
			while ($ListPage <= $ListPageMax) {
				if ($ListPage == $_POST['PageOffset']) {
					echo '<option value="', $ListPage, '" selected="selected">', $ListPage, '</option>';
				} else {
					echo '<option value="', $ListPage, '">', $ListPage, '</option>';
				}
				++$ListPage;
			}
			echo '</select>';
			echo '<input type="submit" name="Go" value="', _('Go'), '" />
					<input type="submit" name="Previous" value="', _('Previous'), '" />
					<input type="submit" name="Next" value="', _('Next'), '" />
					<input type="hidden" name="Keywords" value="', $_POST['Keywords'], '" />
					<input type="hidden" name="StockCat" value="', $_POST['StockCat'], '" />
					<input type="hidden" name="StockCode" value="', $_POST['StockCode'], '" />
				</div>';
		}
		echo '<table>
				<thead>
					<tr>
						<th>', _('Stock Status'), '</th>
						<th class="SortedColumn">', _('Code'), '</th>
						<th>' . _('image') . '</th>
						<th class="SortedColumn">', _('Description'), '</th>
						<th class="SortedColumn">', _('Suppliers Stock Code'), '</th>
						<th>', _('Total Qty On Hand'), '</th>
						<th>', _('Units'), '</th>
						<th></th>
					</tr>
				</thead>';
		$k = 0; //row counter to determine background colour
		if (DB_num_rows($SearchResult) <> 0) {
			DB_data_seek($SearchResult, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		}
		$RowIndex = 1;
		echo '<tbody>';
		while (($MyRow = DB_fetch_array($SearchResult)) and ($RowIndex <= $_SESSION['DisplayRecordsMax'])) {
			if ($MyRow['mbflag'] == 'D') {
				$QOH = _('N/A');
			} else {
				$QOH = locale_number_format($MyRow['qoh'], $MyRow['decimalplaces']);
			}
			if ($MyRow['discontinued'] == 1) {
				$ItemStatus = '<p class="bad">' . _('Obsolete') . '</p>';
			} else {
				$ItemStatus = '';
			}
			if (file_exists($_SESSION['part_pics_dir'] . '/' . $MyRow['stockid'] . '.png') or file_exists($_SESSION['part_pics_dir'] . '/' . $MyRow['stockid'] . '.jpg') or file_exists($_SESSION['part_pics_dir'] . '/' . $MyRow['stockid'] . '.jpeg')) {
				$StockImgLink = '<img src="GetStockImage.php?automake=1&amp;textcolor=FFFFFF&amp;bgcolor=CCCCCC&StockID=' . urlencode($MyRow['stockid']) . '&text=' . urlencode($MyRow['stockid']) . '&width=100&height=100" alt="" />';
			} else {
				$StockImgLink = '<p>' . _('No Image') . '</p>';
			}
			echo '<tr class="striped_row">
					<td>', $ItemStatus, '</td>
					<td><input type="submit" name="Select" value="', $MyRow['stockid'], '" /></td>
					<td>', $StockImgLink, '</td>
					<td data-title="', $MyRow['longdescription'], '">', $MyRow['description'], '</td>
					<td data-title="', $MyRow['suppname'], '">', $MyRow['suppliers_partno'], '</td>
					<td class="number">', $QOH, '</td>
					<td>', $MyRow['units'], '</td>
					<td><a target="_blank" href="', $RootPath, '/StockStatus.php?StockID=', urlencode($MyRow['stockid']), '">', _('View'), '</a></td>
				</tr>';
			++$RowIndex;
		}
		//end of while loop
		echo '</tbody>
			</table>
		</form>';
	}
}
/* end display list if there is more than one record */
include ('includes/footer.php');
?>