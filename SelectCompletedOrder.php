<?php

include('includes/session.inc');

$Title = _('Search All Sales Orders');

include('includes/header.inc');

echo '<p class="page_title_text" >
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', _('Search'), '" alt="" /> ', ' ', _('Search Sales Orders'), '
	</p>';

echo '<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

if (isset($_POST['completed'])) {
	$Completed = "=1";
	$ShowChecked = "checked='checked'";
} else {
	$Completed = ">=0";
	$ShowChecked = '';
}

if (isset($_GET['SelectedStockItem'])) {
	$SelectedStockItem = $_GET['SelectedStockItem'];
} elseif (isset($_POST['SelectedStockItem'])) {
	$SelectedStockItem = $_POST['SelectedStockItem'];
}
if (isset($_GET['OrderNumber'])) {
	$OrderNumber = filter_number_format($_GET['OrderNumber']);
} elseif (isset($_POST['OrderNumber'])) {
	$OrderNumber = filter_number_format($_POST['OrderNumber']);
}
if (isset($_GET['CustomerRef'])) {
	$CustomerRef = $_GET['CustomerRef'];
} elseif (isset($_POST['CustomerRef'])) {
	$CustomerRef = $_POST['CustomerRef'];
}
if (isset($_GET['SelectedCustomer'])) {
	$SelectedCustomer = stripslashes($_GET['SelectedCustomer']);
} elseif (isset($_POST['SelectedCustomer'])) {
	$SelectedCustomer = stripslashes($_POST['SelectedCustomer']);
}
if (isset($SupplierLogin) and $SupplierLogin == 0) {
	$SelectedCustomer = $_SESSION['CustomerID'];
}

if (isset($SelectedStockItem) and $SelectedStockItem == '') {
	unset($SelectedStockItem);
}
if (isset($OrderNumber) and $OrderNumber == '') {
	unset($OrderNumber);
}
if (isset($CustomerRef) and $CustomerRef == '') {
	unset($CustomerRef);
}
if (isset($SelectedCustomer) and $SelectedCustomer == '') {
	unset($SelectedCustomer);
}
if (isset($_POST['ResetPart'])) {
	unset($SelectedStockItem);
}

if (isset($OrderNumber)) {
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/sales.png" title="', _('Sales Order'), '" alt="" /> ', _('Order Number'), ' - ', $OrderNumber, '
		</p>';
	if (mb_strlen($_SESSION['UserBranch']) > 1) {
		echo _('For customer'), ': ', stripslashes($SelectedCustomer);
		echo '<input type="hidden" name="SelectedCustomer" value="', $SelectedCustomer, '" />';
	}
} elseif (isset($CustomerRef)) {
	echo _('Customer Ref') . ' - ' . $CustomerRef;
	if (mb_strlen($_SESSION['UserBranch']) > 1) {
		echo ' ', _('and for customer'), ': ', stripslashes($SelectedCustomer), ' ', _('and'), ' ';
		echo '<input type="hidden" name="SelectedCustomer" value="', $SelectedCustomer, '" />';
	}
} else {
	if (isset($SelectedCustomer)) {
		echo _('For customer'), ': ', stripslashes($SelectedCustomer), ' ' . _('and'), ' ';
		echo '<input type="hidden" name="SelectedCustomer" value="', $SelectedCustomer, '" />';
	}

	if (isset($SelectedStockItem)) {

		$PartString = _('for the part') . ': <b>' . $SelectedStockItem . '</b> ' . _('and') . ' ' . '<input type="hidden" name="SelectedStockItem" value="' . $SelectedStockItem . '" />';

	}
}

if (isset($_POST['SearchParts']) and $_POST['SearchParts'] != '') {

	$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
	$StockString = '%' . str_replace(' ', '%', $_POST['StockCode']) . '%';

	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.decimalplaces,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units
				FROM stockmaster
				LEFT JOIN locstock
					ON stockmaster.stockid=locstock.stockid
				WHERE stockmaster.description " . LIKE . " '" . $SearchString . "'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					AND stockmaster.stockid " . LIKE . " '" . $StockString . "'
				GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						stockmaster.units
				ORDER BY stockmaster.stockid";

	$ErrMsg = _('No stock items were returned by the SQL because');
	$DbgMsg = _('The SQL used to retrieve the searched parts was');
	$StockItemsResult = DB_query($SQL, $ErrMsg, $DbgMsg);

	if (DB_num_rows($StockItemsResult) == 1) {
		$MyRow = DB_fetch_row($StockItemsResult);
		$SelectedStockItem = $MyRow[0];
		$_POST['SearchOrders'] = 'True';
		unset($StockItemsResult);
		echo '<br />', _('For the part'), ': ', $SelectedStockItem, ' ', _('and'), ' <input type="hidden" name="SelectedStockItem" value="', $SelectedStockItem, '" />';
	}

} else if (isset($_POST['SearchOrders']) and is_date($_POST['OrdersAfterDate']) == 1) {

	//figure out the SQL required from the inputs available
	if (isset($OrderNumber)) {
		$SQL = "SELECT salesorders.orderno,
						debtorsmaster.name,
						custbranch.brname,
						salesorders.customerref,
						salesorders.orddate,
						salesorders.deliverydate,
						salesorders.deliverto,
						currencies.decimalplaces AS currdecimalplaces,
						SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
					FROM salesorders
					INNER JOIN salesorderdetails
						ON salesorders.orderno = salesorderdetails.orderno
					INNER JOIN debtorsmaster
						ON salesorders.debtorno = debtorsmaster.debtorno
					INNER JOIN custbranch
						ON salesorders.branchcode = custbranch.branchcode
						AND salesorders.debtorno = custbranch.debtorno
					INNER JOIN currencies
						ON debtorsmaster.currcode = currencies.currabrev
					WHERE salesorders.orderno='" . $OrderNumber . "'
						AND salesorders.quotation=0
						AND salesorderdetails.completed " . $Completed;
	} elseif (isset($CustomerRef)) {
		if (isset($SelectedCustomer)) {
			$SQL = "SELECT salesorders.orderno,
							debtorsmaster.name,
							currencies.decimalplaces AS currdecimalplaces,
							custbranch.brname,
							salesorders.customerref,
							salesorders.orddate,
							salesorders.deliverydate,
							salesorders.deliverto,
							SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
						FROM salesorders
						INNER JOIN salesorderdetails
							ON salesorders.orderno = salesorderdetails.orderno
						INNER JOIN debtorsmaster
							ON salesorders.debtorno = debtorsmaster.debtorno
						INNER JOIN custbranch
							ON salesorders.branchcode = custbranch.branchcode
							AND salesorders.debtorno = custbranch.debtorno
						INNER JOIN currencies
							ON debtorsmaster.currcode = currencies.currabrev
						WHERE salesorders.debtorno='" . $SelectedCustomer . "'
							AND salesorders.customerref like '%" . $CustomerRef . "%'
							AND salesorders.quotation=0
							AND salesorderdetails.completed " . $Completed;
		} else { //customer not selected
			$SQL = "SELECT salesorders.orderno,
							debtorsmaster.name,
							currencies.decimalplaces AS currdecimalplaces,
							custbranch.brname,
							salesorders.customerref,
							salesorders.orddate,
							salesorders.deliverydate,
							salesorders.deliverto,
							SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
						FROM salesorders
						INNER JOIN salesorderdetails
							ON salesorders.orderno = salesorderdetails.orderno
						INNER JOIN debtorsmaster
							ON salesorders.debtorno = debtorsmaster.debtorno
						INNER JOIN custbranch
							ON salesorders.branchcode = custbranch.branchcode
							AND salesorders.debtorno = custbranch.debtorno
						INNER JOIN currencies
							ON debtorsmaster.currcode = currencies.currabrev
						WHERE salesorders.customerref " . LIKE . " '%" . $CustomerRef . "%'
							AND salesorders.quotation=0
							AND salesorderdetails.completed " . $Completed;
		}

	} else {
		$DateAfterCriteria = FormatDateforSQL($_POST['OrdersAfterDate']);

		if (isset($SelectedCustomer) and !isset($OrderNumber) and !isset($CustomerRef)) {

			if (isset($SelectedStockItem)) {
				$SQL = "SELECT salesorders.orderno,
								debtorsmaster.name,
								currencies.decimalplaces AS currdecimalplaces,
								custbranch.brname,
								salesorders.customerref,
								salesorders.orddate,
								salesorders.deliverydate,
								salesorders.deliverto,
								SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
							FROM salesorders
							INNER JOIN salesorderdetails
								ON salesorders.orderno = salesorderdetails.orderno
							INNER JOIN debtorsmaster
								ON salesorders.debtorno = debtorsmaster.debtorno
							INNER JOIN custbranch
								ON salesorders.branchcode = custbranch.branchcode
								AND salesorders.debtorno = custbranch.debtorno
							INNER JOIN currencies
								ON debtorsmaster.currcode = currencies.currabrev
							WHERE salesorderdetails.stkcode='" . $SelectedStockItem . "'
								AND salesorders.debtorno='" . $SelectedCustomer . "'
								AND salesorders.orddate >= '" . $DateAfterCriteria . "'
								AND salesorders.quotation=0
								AND salesorderdetails.completed " . $Completed;
			} else {
				$SQL = "SELECT salesorders.orderno,
								debtorsmaster.name,
								currencies.decimalplaces AS currdecimalplaces,
								custbranch.brname,
								salesorders.customerref,
								salesorders.orddate,
								salesorders.deliverto,
								salesorders.deliverydate,
								SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
							FROM salesorders
							INNER JOIN salesorderdetails
								ON salesorders.orderno = salesorderdetails.orderno
							INNER JOIN debtorsmaster
								ON salesorders.debtorno = debtorsmaster.debtorno
							INNER JOIN custbranch
								ON salesorders.branchcode = custbranch.branchcode
								AND salesorders.debtorno = custbranch.debtorno
							INNER JOIN currencies
								ON debtorsmaster.currcode = currencies.currabrev
							WHERE salesorders.debtorno='" . $SelectedCustomer . "'
								AND salesorders.orddate >= '" . $DateAfterCriteria . "'
								AND salesorders.quotation=0
								AND salesorderdetails.completed " . $Completed;
			}
		} else { //no customer selected
			if (isset($SelectedStockItem)) {
				$SQL = "SELECT salesorders.orderno,
								debtorsmaster.name,
								currencies.decimalplaces AS currdecimalplaces,
								custbranch.brname,
								salesorders.customerref,
								salesorders.orddate,
								salesorders.deliverto,
								salesorders.deliverydate,
								SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
							FROM salesorders
							INNER JOIN salesorderdetails
								ON salesorders.orderno = salesorderdetails.orderno
							INNER JOIN debtorsmaster
								ON salesorders.debtorno = debtorsmaster.debtorno
							INNER JOIN custbranch
								ON salesorders.branchcode = custbranch.branchcode
								AND salesorders.debtorno = custbranch.debtorno
							INNER JOIN currencies
								ON debtorsmaster.currcode = currencies.currabrev
							WHERE salesorderdetails.stkcode='" . $SelectedStockItem . "'
								AND salesorders.orddate >= '" . $DateAfterCriteria . "'
								AND salesorders.quotation=0
								AND salesorderdetails.completed " . $Completed;
			} else {
				$SQL = "SELECT salesorders.orderno,
								debtorsmaster.name,
								currencies.decimalplaces AS currdecimalplaces,
								custbranch.brname,
								salesorders.customerref,
								salesorders.orddate,
								salesorders.deliverto,
								salesorders.deliverydate,
								SUM(salesorderdetails.unitprice*salesorderdetails.quantity*(1-salesorderdetails.discountpercent)) AS ordervalue
							FROM salesorders
							INNER JOIN salesorderdetails
								ON salesorders.orderno = salesorderdetails.orderno
							INNER JOIN debtorsmaster
								ON salesorders.debtorno = debtorsmaster.debtorno
							INNER JOIN custbranch
								ON salesorders.branchcode = custbranch.branchcode
								AND salesorders.debtorno = custbranch.debtorno
							INNER JOIN currencies
								ON debtorsmaster.currcode = currencies.currabrev
							WHERE salesorders.orddate >= '" . $DateAfterCriteria . "'
								AND salesorders.quotation=0
								AND salesorderdetails.completed " . $Completed;
			}
		} //end selected customer
	} //end not order number selected

	if ($_SESSION['SalesmanLogin'] != '') {
		$SQL .= " AND salesorders.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
	}
	$SQL .= " GROUP BY salesorders.orderno,
					debtorsmaster.name,
					currencies.decimalplaces,
					custbranch.brname,
					salesorders.customerref,
					salesorders.orddate,
					salesorders.deliverydate,
					salesorders.deliverto
				ORDER BY salesorders.orderno";

	$SalesOrdersResult = DB_query($SQL);

	if (DB_error_no() != 0) {
		prnMsg(_('No orders were returned by the SQL because') . ' ' . DB_error_msg(), 'info');
		echo '<br /> ' . $SQL;
	}

} //end of which button clicked options

if (!isset($_POST['OrdersAfterDate']) or $_POST['OrdersAfterDate'] == '' or !is_date($_POST['OrdersAfterDate'])) {
	$_POST['OrdersAfterDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, Date('m') - 2, Date('d'), Date('Y')));
}
echo '<table class="selection">';

if (isset($PartString)) {
	echo '<tr>
			<td>', $PartString, '</td>';
} else {
	echo '<tr>
			<td></td>';
}
if (!isset($_POST['OrderNumber'])) {
	$_POST['OrderNumber'] = '';
}
echo '<td>', _('Order Number'), ':</td>
		<td><input type="text" name="OrderNumber" maxlength="8" size="9" value="', $_POST['OrderNumber'], '" /></td>
		<td>', _('for all orders placed after'), ': </td>
		<td><input type="text" class="date" alt="', $_SESSION['DefaultDateFormat'], '"  name="OrdersAfterDate" maxlength="10" size="11" value="', $_POST['OrdersAfterDate'], '" /></td>
		<td><input type="submit" name="SearchOrders" value="', _('Search Orders'), '" /></td>
	</tr>';
echo '<tr>
		<td></td>
		<td>', _('Customer Ref'), ':</td><td><input type="text" name="CustomerRef" maxlength="8" size="9" /></td>
		<td></td>
		<td colspan="2"><input type="checkbox" ', $ShowChecked, ' name="completed" />', _('Show Completed orders only'), '</td>
	</tr>';

echo '</table>';

if (!isset($SelectedStockItem)) {
	$Result1 = DB_query("SELECT categoryid,
							categorydescription
						FROM stockcategory
						ORDER BY categorydescription");

	echo '<div class="page_help_text">', _('To search for sales orders for a specific part use the part selection facilities below'), '</div>';
	echo '<table class="selection">';
	echo '<tr>
			<td>', _('Select a stock category'), ':';
	echo '<select name="StockCat">';

	while ($MyRow1 = DB_fetch_array($Result1)) {
		if (isset($_POST['StockCat']) and $MyRow1['categoryid'] == $_POST['StockCat']) {
			echo '<option selected="selected" value="', $MyRow1['categoryid'], '">', $MyRow1['categorydescription'], '</option>';
		} else {
			echo '<option value="', $MyRow1['categoryid'], '">', $MyRow1['categorydescription'], '</option>';
		}
	}

	echo '</select>
			</td>';
	echo '<td>', _('Enter text extracts in the description'), ':</td>
		<td><input type="text" name="Keywords" size="20" maxlength="25" /></td>
	</tr>
	<tr>
		<td></td>
		<td><b> ', _('OR'), ' </b>', _('Enter extract of the Stock Code'), ':</td>
		<td><input type="text" name="StockCode" size="15" maxlength="18" /></td>
	</tr>
	<tr>
		<td colspan="4">
			<div class="centre">
				<input type="submit" name="SearchParts" value="', _('Search Parts Now'), '" />';

	if (count($_SESSION['AllowedPageSecurityTokens']) > 1) {
		echo '<input type="submit" name="ResetPart" value="', _('Show All'), '" /></div>';
	}
	echo '</div>
		</td>
	</tr>
</table>';

}

if (isset($StockItemsResult)) {

	echo '<table cellpadding="2" class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">', _('Code'), '</th>
					<th class="SortedColumn">', _('Description'), '</th>
					<th>', _('On Hand'), '</th>
					<th>', _('Units'), '</th>
				</tr>
			</thead>';

	$k = 0; //row colour counter
	echo '<tbody>';
	while ($MyRow = DB_fetch_array($StockItemsResult)) {

		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			++$k;
		}

		echo '<td>
				<input type="submit" name="SelectedStockItem" value="', $MyRow['stockid'], '" />
			</td>
			<td>', $MyRow['description'], '</td>
			<td class="number">', locale_number_format($MyRow['qoh'], $MyRow['decimalplaces']), '</td>
			<td>', $MyRow['units'], '</td>
		</tr>';

		//end of page full new headings if
	}
	//end of while loop

	echo '</tbody>';
	echo '</table>';

}
//end if stock search results to show

if (isset($SalesOrdersResult)) {

	/*show a table of the orders returned by the SQL */

	echo '<table cellpadding="2" width="90%" class="selection">
			<thead>
				<tr>
					<th colspan="9">
						<h3>', _('Sales Orders'), '
							<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/printer.png" class="PrintIcon" title="', _('Print'), '" alt="" onclick="window.print();" />
						</h3>
					</th>
				</tr>
				<tr>
					<th class="SortedColumn">', _('Order'), ' #</th>
					<th class="SortedColumn">', _('Customer'), '</th>
					<th class="SortedColumn">', _('Branch'), '</th>
					<th>', _('Cust Order'), ' #</th>
					<th class="SortedColumn">', _('Order Date'), '</th>
					<th class="SortedColumn">', _('Req Del Date'), '</th>
					<th>', _('Delivery To'), '</th>
					<th class="SortedColumn">', _('Order Total'), '</th>
				</tr>
			</thead>';

	$k = 0; //row colour counter
	echo '<tbody>';
	while ($MyRow = DB_fetch_array($SalesOrdersResult)) {

		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}

		echo '<td><a href="', $RootPath . '/OrderDetails.php?OrderNumber=' . $MyRow['orderno'], '">', $MyRow['orderno'], '</a></td>
				<td>', $MyRow['name'], '</td>
				<td>', $MyRow['brname'], '</td>
				<td>', $MyRow['customerref'], '</td>
				<td>', ConvertSQLDate($MyRow['orddate']), '</td>
				<td>', ConvertSQLDate($MyRow['deliverydate']), '</td>
				<td>', $MyRow['deliverto'], '</td>
				<td class="number">', locale_number_format($MyRow['ordervalue'], $MyRow['currdecimalplaces']), '</td>
			</tr>';

		//end of page full new headings if
	}
	//end of while loop

	echo '</tbody>
	</table>';
}

echo '</form>';
include('includes/footer.inc');

?>