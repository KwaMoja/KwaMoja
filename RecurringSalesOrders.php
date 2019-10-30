<?php
/* This is where the details specific to the recurring order are entered and the template committed to the database once the Process button is hit */

include ('includes/DefineCartClass.php');

/* Session started in header.php for password checking the session will contain the details of the order from the Cart class object. The details of the order come from SelectOrderItems.php */
include ('includes/session.php');
$Title = _('Recurring Orders');

/* Manual links before header.php */
$ViewTopic = 'SalesOrders';
$BookMark = 'RecurringSalesOrders';

include ('includes/header.php');

if (empty($_GET['identifier'])) {
	$Identifier = date('U');
} else {
	$Identifier = $_GET['identifier'];
}

if (isset($_GET['NewRecurringOrder'])) {
	$NewRecurringOrder = 'Yes';
} elseif (isset($_POST['NewRecurringOrder'])) {
	$NewRecurringOrder = 'Yes';
} else {
	$NewRecurringOrder = 'No';
	if (isset($_GET['ModifyRecurringSalesOrder'])) {

		$_POST['ExistingRecurrOrderNo'] = $_GET['ModifyRecurringSalesOrder'];

		/*Need to read in the existing recurring order template */

		$_SESSION['Items' . $Identifier] = new cart;

		/*read in all the guff from the selected order into the Items cart  */

		$OrderHeaderSQL = "SELECT recurringsalesorders.debtorno,
									debtorsmaster.name,
									recurringsalesorders.branchcode,
									recurringsalesorders.customerref,
									recurringsalesorders.comments,
									recurringsalesorders.orddate,
									recurringsalesorders.ordertype,
									salestypes.sales_type,
									recurringsalesorders.shipvia,
									recurringsalesorders.deliverto,
									recurringsalesorders.deladd1,
									recurringsalesorders.deladd2,
									recurringsalesorders.deladd3,
									recurringsalesorders.deladd4,
									recurringsalesorders.deladd5,
									recurringsalesorders.deladd6,
									recurringsalesorders.contactphone,
									recurringsalesorders.contactemail,
									recurringsalesorders.freightcost,
									debtorsmaster.currcode,
									recurringsalesorders.fromstkloc,
									recurringsalesorders.frequency,
									recurringsalesorders.stopdate,
									recurringsalesorders.lastrecurrence,
									recurringsalesorders.autoinvoice
								FROM recurringsalesorders
								INNER JOIN debtorsmaster
								ON recurringsalesorders.debtorno = debtorsmaster.debtorno
								INNER JOIN salestypes
								ON recurringsalesorders.ordertype=salestypes.typeabbrev
								WHERE recurringsalesorders.recurrorderno = '" . $_GET['ModifyRecurringSalesOrder'] . "'";

		$ErrMsg = _('The order cannot be retrieved because');
		$GetOrdHdrResult = DB_query($OrderHeaderSQL, $ErrMsg);

		if (DB_num_rows($GetOrdHdrResult) == 1) {

			$MyRow = DB_fetch_array($GetOrdHdrResult);

			$_SESSION['Items' . $Identifier]->DebtorNo = $MyRow['debtorno'];
			/*CustomerID defined in header.php */
			$_SESSION['Items' . $Identifier]->Branch = $MyRow['branchcode'];
			$_SESSION['Items' . $Identifier]->CustomerName = $MyRow['name'];
			$_SESSION['Items' . $Identifier]->CustRef = $MyRow['customerref'];
			$_SESSION['Items' . $Identifier]->Comments = $MyRow['comments'];

			$_SESSION['Items' . $Identifier]->DefaultSalesType = $MyRow['ordertype'];
			$_SESSION['Items' . $Identifier]->SalesTypeName = $MyRow['sales_type'];
			$_SESSION['Items' . $Identifier]->DefaultCurrency = $MyRow['currcode'];
			$_SESSION['Items' . $Identifier]->ShipVia = $MyRow['shipvia'];
			$BestShipper = $MyRow['shipvia'];
			$_SESSION['Items' . $Identifier]->DeliverTo = $MyRow['deliverto'];
			//$_SESSION['Items'.$Identifier]->DeliveryDate = ConvertSQLDate($MyRow['deliverydate']);
			$_SESSION['Items' . $Identifier]->DelAdd1 = $MyRow['deladd1'];
			$_SESSION['Items' . $Identifier]->DelAdd2 = $MyRow['deladd2'];
			$_SESSION['Items' . $Identifier]->DelAdd3 = $MyRow['deladd3'];
			$_SESSION['Items' . $Identifier]->DelAdd4 = $MyRow['deladd4'];
			$_SESSION['Items' . $Identifier]->DelAdd5 = $MyRow['deladd5'];
			$_SESSION['Items' . $Identifier]->DelAdd6 = $MyRow['deladd6'];
			$_SESSION['Items' . $Identifier]->PhoneNo = $MyRow['contactphone'];
			$_SESSION['Items' . $Identifier]->Email = $MyRow['contactemail'];
			$_SESSION['Items' . $Identifier]->Location = $MyRow['fromstkloc'];
			$_SESSION['Items' . $Identifier]->Quotation = 0;
			$FreightCost = $MyRow['freightcost'];
			$_SESSION['Items' . $Identifier]->Orig_OrderDate = $MyRow['orddate'];
			$_POST['StopDate'] = ConvertSQLDate($MyRow['stopdate']);
			$_POST['StartDate'] = ConvertSQLDate($MyRow['lastrecurrence']);
			$_POST['Frequency'] = $MyRow['frequency'];
			$_POST['AutoInvoice'] = $MyRow['autoinvoice'];

			/*need to look up customer name from debtors master then populate the line items array with the sales order details records */
			$LineItemsSQL = "SELECT recurrsalesorderdetails.stkcode,
									stockmaster.description,
									stockmaster.longdescription,
									stockmaster.volume,
									stockmaster.grossweight,
									stockmaster.units,
									recurrsalesorderdetails.unitprice,
									recurrsalesorderdetails.quantity,
									recurrsalesorderdetails.discountpercent,
									recurrsalesorderdetails.narrative,
									locstock.quantity as qohatloc,
									stockmaster.mbflag,
									stockmaster.discountcategory,
									stockmaster.decimalplaces
									FROM recurrsalesorderdetails INNER JOIN stockmaster
									ON recurrsalesorderdetails.stkcode = stockmaster.stockid
									INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
									WHERE  locstock.loccode = '" . $MyRow['fromstkloc'] . "'
									AND recurrsalesorderdetails.recurrorderno ='" . $_GET['ModifyRecurringSalesOrder'] . "'";

			$ErrMsg = _('The line items of the order cannot be retrieved because');
			$LineItemsResult = DB_query($LineItemsSQL, $ErrMsg);
			if (DB_num_rows($LineItemsResult) > 0) {

				while ($MyRow = DB_fetch_array($LineItemsResult)) {
					$_SESSION['Items' . $Identifier]->add_to_cart($MyRow['stkcode'], $MyRow['quantity'], $MyRow['description'], $MyRow['longdescription'], $MyRow['unitprice'], $MyRow['discountpercent'], $MyRow['units'], $MyRow['volume'], $MyRow['grossweight'], $MyRow['qohatloc'], $MyRow['mbflag'], '', 0, $MyRow['discountcategory'], 0, /*Controlled*/
					0, /*Serialised */
					$MyRow['decimalplaces'], $MyRow['narrative']);
					/*Just populating with existing order - no DBUpdates */

				}
				/* line items from sales order details */
			} //end of checks on returned data set
			
		}
	}
}

if ((!isset($_SESSION['Items' . $Identifier]) or $_SESSION['Items' . $Identifier]->ItemsOrdered == 0) and $NewRecurringOrder == 'Yes') {
	prnMsg(_('A new recurring order can only be created if an order template has already been created from the normal order entry screen') . '. ' . _('To enter an order template select sales order entry from the orders tab of the main menu'), 'error');
	include ('includes/footer.php');
	exit;
}

if (isset($_POST['DeleteRecurringOrder'])) {
	$SQL = "DELETE FROM recurrsalesorderdetails WHERE recurrorderno='" . $_POST['ExistingRecurrOrderNo'] . "'";
	$ErrMsg = _('Could not delete recurring sales order lines for the recurring order template') . ' ' . $_POST['ExistingRecurrOrderNo'];
	$Result = DB_query($SQL, $ErrMsg);

	$SQL = "DELETE FROM recurringsalesorders WHERE recurrorderno='" . $_POST['ExistingRecurrOrderNo'] . "'";
	$ErrMsg = _('Could not delete the recurring sales order template number') . ' ' . $_POST['ExistingRecurrOrderNo'];
	$Result = DB_query($SQL, $ErrMsg);

	prnMsg(_('Successfully deleted recurring sales order template number') . ' ' . $_POST['ExistingRecurrOrderNo'], 'success');

	echo 'div class="centre">
			<a href="', $RootPath, '/SelectRecurringSalesOrder.php">', _('Select A Recurring Sales Order Template'), '</a>
		</div>';

	unset($_SESSION['Items' . $Identifier]->LineItems);
	unset($_SESSION['Items' . $Identifier]);
	include ('includes/footer.php');
	exit;
}
if (isset($_POST['Process'])) {
	$Result = DB_Txn_Begin();
	$InputErrors = 0;
	if (!is_date($_POST['StartDate'])) {
		$InputErrors = 1;
		prnMsg(_('The last recurrence or start date of this recurring order must be a valid date in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
	}
	if (!is_date($_POST['StopDate'])) {
		$InputErrors = 1;
		prnMsg(_('The end date of this recurring order must be a valid date in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
	}
	if (Date1GreaterThanDate2($_POST['StartDate'], $_POST['StopDate'])) {
		$InputErrors = 1;
		prnMsg(_('The end date of this recurring order must be after the start date'), 'error');
	}
	if (isset($_POST['MakeRecurringOrder']) and $_POST['Quotation'] == 1) {
		$InputErrors = 1;
		prnMsg(_('A recurring order cannot be made from a quotation'), 'error');
	}

	if ($InputErrors == 0) {
		/*Error checks above all passed ok so lets go*/

		if ($NewRecurringOrder == 'Yes') {

			/* finally write the recurring order header to the database and then the line details*/
			$DelDate = FormatDateforSQL($_SESSION['Items' . $Identifier]->DeliveryDate);

			$HeaderSQL = "INSERT INTO recurringsalesorders (
										debtorno,
										branchcode,
										customerref,
										comments,
										orddate,
										ordertype,
										deliverto,
										deladd1,
										deladd2,
										deladd3,
										deladd4,
										deladd5,
										deladd6,
										contactphone,
										contactemail,
										freightcost,
										fromstkloc,
										shipvia,
										lastrecurrence,
										stopdate,
										frequency,
										autoinvoice)
									values (
										'" . $_SESSION['Items' . $Identifier]->DebtorNo . "',
										'" . $_SESSION['Items' . $Identifier]->Branch . "',
										'" . $_SESSION['Items' . $Identifier]->CustRef . "',
										'" . $_SESSION['Items' . $Identifier]->Comments . "',
										'" . Date('Y-m-d H:i') . "',
										'" . $_SESSION['Items' . $Identifier]->DefaultSalesType . "',
										'" . $_SESSION['Items' . $Identifier]->DeliverTo . "',
										'" . $_SESSION['Items' . $Identifier]->DelAdd1 . "',
										'" . $_SESSION['Items' . $Identifier]->DelAdd2 . "',
										'" . $_SESSION['Items' . $Identifier]->DelAdd3 . "',
										'" . $_SESSION['Items' . $Identifier]->DelAdd4 . "',
										'" . $_SESSION['Items' . $Identifier]->DelAdd5 . "',
										'" . $_SESSION['Items' . $Identifier]->DelAdd6 . "',
										'" . $_SESSION['Items' . $Identifier]->PhoneNo . "',
										'" . $_SESSION['Items' . $Identifier]->Email . "',
										'" . $_SESSION['Items' . $Identifier]->FreightCost . "',
										'" . $_SESSION['Items' . $Identifier]->Location . "',
										'" . $_SESSION['Items' . $Identifier]->ShipVia . "',
										'" . FormatDateforSQL($_POST['StartDate']) . "',
										'" . FormatDateforSQL($_POST['StopDate']) . "',
										'" . $_POST['Frequency'] . "',
										'" . $_POST['AutoInvoice'] . "')";

			$ErrMsg = _('The recurring order cannot be added because');
			$DbgMsg = _('The SQL that failed was');
			$InsertQryResult = DB_query($HeaderSQL, $ErrMsg, $DbgMsg, true);

			$RecurrOrderNo = DB_Last_Insert_ID('recurringsalesorders', 'recurrorderno');

			$StartOf_LineItemsSQL = "INSERT INTO recurrsalesorderdetails (recurrorderno,
																			stkcode,
																			unitprice,
																			quantity,
																			discountpercent,
																			narrative)
																		VALUES ('";

			foreach ($_SESSION['Items' . $Identifier]->LineItems as $StockItem) {

				$LineItemsSQL = $StartOf_LineItemsSQL . $RecurrOrderNo . "',
								'" . $StockItem->StockID . "',
								'" . filter_number_format($StockItem->Price) . "',
								'" . filter_number_format($StockItem->Quantity) . "',
								'" . filter_number_format($StockItem->DiscountPercent) . "',
								'" . $StockItem->Narrative . "')";
				$Ins_LineItemResult = DB_query($LineItemsSQL, $ErrMsg, $DbgMsg, true);

			}
			/* inserted line items into sales order details */

			$Result = DB_Txn_Commit();
			prnmsg(_('The new recurring order template has been added'), 'success');

		} else {
			/* must be updating an existing recurring order */
			$HeaderSQL = "UPDATE recurringsalesorders SET
						stopdate =  '" . FormatDateforSQL($_POST['StopDate']) . "',
						frequency = '" . $_POST['Frequency'] . "',
						autoinvoice = '" . $_POST['AutoInvoice'] . "'
					WHERE recurrorderno = '" . $_POST['ExistingRecurrOrderNo'] . "'";

			$ErrMsg = _('The recurring order cannot be updated because');
			$UpdateQryResult = DB_query($HeaderSQL, $ErrMsg);
			prnmsg(_('The recurring order template has been updated'), 'success');
		}

		echo '<div class="centre">
				<a href="', $RootPath, '/SelectOrderItems.php?NewOrder=Yes">', _('Enter New Sales Order'), '</a><br />
				<a href="', $RootPath, '/SelectRecurringSalesOrder.php">', _('Select A Recurring Sales Order Template'), '</a>
			</div>';

		unset($_SESSION['Items' . $Identifier]->LineItems);
		unset($_SESSION['Items' . $Identifier]);
		include ('includes/footer.php');
		exit;

	}
}

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/customer.png" title="', _('Search'), '" alt="" /> ', _('Recurring Order for Customer'), ' : ', $_SESSION['Items' . $Identifier]->CustomerName, '
	</p>';

echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', urlencode($Identifier), '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

echo '<table cellpadding="2">';
echo '<tr>
		<th colspan="7">', _('Order Line Details'), '</th>
	</tr>
	<tr>
		<th>', _('Item Code'), '</th>
		<th>', _('Item Description'), '</th>
		<th>', _('Quantity'), '</th>
		<th>', _('Unit'), '</th>
		<th>', _('Price'), '</th>
		<th>', _('Discount'), ' %</th>
		<th>', _('Total'), '</th>
	</tr>';

$_SESSION['Items' . $Identifier]->total = 0;
$_SESSION['Items' . $Identifier]->totalVolume = 0;
$_SESSION['Items' . $Identifier]->totalWeight = 0;

foreach ($_SESSION['Items' . $Identifier]->LineItems as $StockItem) {

	$LineTotal = $StockItem->Quantity * $StockItem->Price * (1 - $StockItem->DiscountPercent);
	$DisplayLineTotal = locale_number_format($LineTotal, $_SESSION['Items' . $Identifier]->CurrDecimalPlaces);
	$DisplayPrice = locale_number_format($StockItem->Price, $_SESSION['Items' . $Identifier]->CurrDecimalPlaces);
	$DisplayQuantity = locale_number_format($StockItem->Quantity, $StockItem->DecimalPlaces);
	$DisplayDiscount = locale_number_format(($StockItem->DiscountPercent * 100), 2);

	echo '<tr class="striped_row">
			<td>', $StockItem->StockID, '</td>
			<td data-title="', $StockItem->LongDescription, '">', $StockItem->ItemDescription, '</td>
			<td class="number">', $DisplayQuantity, '</td>
			<td>', $StockItem->Units, '</td>
			<td class="number">', $DisplayPrice, '</td>
			<td class="number">', $DisplayDiscount, '</td>
			<td class="number">', $DisplayLineTotal, '</td>
		</tr>';

	$_SESSION['Items' . $Identifier]->total+= $LineTotal;
	$_SESSION['Items' . $Identifier]->totalVolume+= ($StockItem->Quantity * $StockItem->Volume);
	$_SESSION['Items' . $Identifier]->totalWeight+= ($StockItem->Quantity * $StockItem->Weight);
}

$DisplayTotal = locale_number_format($_SESSION['Items' . $Identifier]->total, $_SESSION['Items' . $Identifier]->CurrDecimalPlaces);
echo '<tr>
		<td colspan="6" class="number"><b>', _('TOTAL Excl Tax/Freight'), '</b></td>
		<td class="number">', $DisplayTotal, '</td>
	</tr>
</table>';

echo '<fieldset>
		<legend>', _('Order Header Details'), '</legend>';

echo '<field>
		<label>', _('Deliver To'), ':</label>
		<div class="fieldtext">', $_SESSION['Items' . $Identifier]->DeliverTo, '&nbsp;</div>
	</field>';

echo '<field>
		<label>', _('Deliver from the warehouse at'), ':</label>
		<div class="fieldtext">', $_SESSION['Items' . $Identifier]->Location, '&nbsp;</div>
	</field>';

echo '<field>
		<label>', _('Street'), ':</label>
		<div class="fieldtext">', $_SESSION['Items' . $Identifier]->DelAdd1, '&nbsp;</div>
	</field>';

echo '<field>
		<label>', _('Suburb'), ':</label>
		<div class="fieldtext">', $_SESSION['Items' . $Identifier]->DelAdd2, '&nbsp;</div>
	</field>';

echo '<field>
		<label>', _('City'), '/', _('Region'), ':</label>
		<div class="fieldtext">', $_SESSION['Items' . $Identifier]->DelAdd3, '&nbsp;</div>
	</field>';

echo '<field>
		<label>', _('Post Code'), ':</label>
		<div class="fieldtext">', $_SESSION['Items' . $Identifier]->DelAdd4, '&nbsp;</div>
	</field>';

echo '<field>
		<label>', _('Contact Phone Number'), ':</label>
		<div class="fieldtext">', $_SESSION['Items' . $Identifier]->PhoneNo, '&nbsp;</div>
	</field>';

echo '<field>
		<label>', _('Contact Email'), ':</label>
		<div class="fieldtext">', $_SESSION['Items' . $Identifier]->Email, '&nbsp;</div>
	</field>';

echo '<field>
		<label>', _('Customer Reference'), ':</label>
		<div class="fieldtext">', $_SESSION['Items' . $Identifier]->CustRef, '&nbsp;</div>
	</field>';

echo '<field>
		<label>', _('Comments'), ':</label>
		<div class="fieldtext">', $_SESSION['Items' . $Identifier]->Comments, '&nbsp;</div>
	</field>';

if (!isset($_POST['StartDate'])) {
	$_POST['StartDate'] = date($_SESSION['DefaultDateFormat']);
}

if ($NewRecurringOrder == 'Yes') {
	echo '<field>
			<label>', _('Start Date'), ':</label>
			<input type="text" class="date" name="StartDate" size="11" required="required" maxlength="10" value="', $_POST['StartDate'], '" />
		</field>';
} else {
	echo '<field>
			<label>', _('Last Recurrence'), ':</label>
			<div class="fieldtext">', $_POST['StartDate'], '&nbsp;</div>
		</field>';
	echo '<input type="hidden" name="StartDate" value="', $_POST['StartDate'], '" />';
}

if (!isset($_POST['StopDate'])) {
	$_POST['StopDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, Date('m'), Date('d') + 1, Date('y') + 1));
}

echo '<field>
		<label>', _('Finish Date'), ':</label>
		<input type="text" class="date" name="StopDate" size="11" required="required" maxlength="10" value="', $_POST['StopDate'], '" />
	</field>';

echo '<field>
		<label>', _('Frequency of Recurrence'), ':</label>
		<select required="required" name="Frequency">';

if (isset($_POST['Frequency']) and $_POST['Frequency'] == 52) {
	echo '<option selected="selected" value="52">', _('Weekly'), '</option>';
} else {
	echo '<option value="52">', _('Weekly'), '</option>';
}
if (isset($_POST['Frequency']) and $_POST['Frequency'] == 26) {
	echo '<option selected="selected" value="26">', _('Fortnightly'), '</option>';
} else {
	echo '<option value="26">', _('Fortnightly'), '</option>';
}
if (isset($_POST['Frequency']) and $_POST['Frequency'] == 12) {
	echo '<option selected="selected" value="12">', _('Monthly'), '</option>';
} else {
	echo '<option value="12">', _('Monthly'), '</option>';
}
if (isset($_POST['Frequency']) and $_POST['Frequency'] == 6) {
	echo '<option selected="selected" value="6">', _('Bi-monthly'), '</option>';
} else {
	echo '<option value="6">', _('Bi-monthly'), '</option>';
}
if (isset($_POST['Frequency']) and $_POST['Frequency'] == 4) {
	echo '<option selected="selected" value="4">', _('Quarterly'), '</option>';
} else {
	echo '<option value="4">', _('Quarterly'), '</option>';
}
if (isset($_POST['Frequency']) and $_POST['Frequency'] == 2) {
	echo '<option selected="selected" value="2">', _('Bi-Annually'), '</option>';
} else {
	echo '<option value="2">', _('Bi-Annually'), '</option>';
}
if (isset($_POST['Frequency']) and $_POST['Frequency'] == 1) {
	echo '<option selected="selected" value="1">', _('Annually'), '</option>';
} else {
	echo '<option value="1">', _('Annually'), '</option>';
}
echo '</select>
	</field>';

if ($_SESSION['Items' . $Identifier]->AllDummyLineItems() == true) {

	echo '<field>
			<label for="AutoInvoice">', _('Invoice Automatically'), ':</label>
			<select required="required" name="AutoInvoice">';
	if ($_POST['AutoInvoice'] == 0) {
		echo '<option selected="selected" value="0">', _('No'), '</option>';
		echo '<option value="1">', _('Yes'), '</option>';
	} else {
		echo '<option value="0">', _('No'), '</option>';
		echo '<option selected="selected" value="1">', _('Yes'), '</option>';
	}
	echo '</select>
		</field>
	</fieldset>';
} else {
	echo '</fieldset>';
	echo '<input type="hidden" name="AutoInvoice" value="0" />';
}

echo '<div class="centre">';
if ($NewRecurringOrder == 'Yes') {
	echo '<input type="hidden" name="NewRecurringOrder" value="Yes" />';
	echo '<input type="submit" name="Process" value="', _('Create Recurring Order'), '" />';
} else {
	echo '<input type="hidden" name="NewRecurringOrder" value="No" />';
	echo '<input type="hidden" name="ExistingRecurrOrderNo" value="', $_POST['ExistingRecurrOrderNo'], '" />';

	echo '<input type="submit" name="Process" value="', _('Update Recurring Order Details'), '" /><br />';
	echo '<input type="submit" name="DeleteRecurringOrder" value="', _('Delete Recurring Order'), ' ', $_POST['ExistingRecurrOrderNo'], '" onclick="return MakeConfirm(\'', _('Are you sure you wish to delete this recurring order template?'), '\');" />';
}

echo '</div>
	</form>';

include ('includes/footer.php');
?>