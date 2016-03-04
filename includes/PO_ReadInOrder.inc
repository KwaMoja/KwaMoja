<?php

/*PO_ReadInOrder.inc is used by the modify existing order code in PO_Header.php and also by GoodsReceived.php */

if (isset($_SESSION['PO' . $Identifier])) {
	unset($_SESSION['PO' . $Identifier]->LineItems);
	unset($_SESSION['PO' . $Identifier]);
}

$_SESSION['ExistingOrder'] = $_GET['ModifyOrderNumber'];
$_SESSION['RequireSupplierSelection'] = 0;
$_SESSION['PO' . $Identifier] = new PurchOrder;

$_SESSION['PO' . $Identifier]->GLLink = $_SESSION['CompanyRecord']['gllink_stock'];

/*read in all the guff from the selected order into the PO PurchOrder Class variable  */

$OrderHeaderSQL = "SELECT purchorders.supplierno,
							suppliers.suppname,
							purchorders.comments,
							purchorders.orddate,
							purchorders.rate,
							purchorders.dateprinted,
							purchorders.deladd1,
							purchorders.deladd2,
							purchorders.deladd3,
							purchorders.deladd4,
							purchorders.deladd5,
							purchorders.deladd6,
							purchorders.tel,
							purchorders.suppdeladdress1,
							purchorders.suppdeladdress2,
							purchorders.suppdeladdress3,
							purchorders.suppdeladdress4,
							purchorders.suppdeladdress5,
							purchorders.suppdeladdress6,
							purchorders.suppliercontact,
							purchorders.supptel,
							purchorders.contact,
							purchorders.allowprint,
							purchorders.requisitionno,
							purchorders.intostocklocation,
							purchorders.initiator,
							purchorders.authoriser,
							purchorders.version,
							purchorders.status,
							purchorders.stat_comment,
							purchorders.deliverydate,
							purchorders.deliveryby,
							purchorders.port,
							suppliers.currcode,
							locations.managed,
							purchorders.paymentterms,
							currencies.decimalplaces
						FROM purchorders
						INNER JOIN locations
							ON purchorders.intostocklocation=locations.loccode
						INNER JOIN suppliers
							ON purchorders.supplierno = suppliers.supplierid
						INNER JOIN currencies
							ON suppliers.currcode=currencies.currabrev
						INNER JOIN locationusers
							ON locationusers.loccode=locations.loccode
							AND locationusers.userid='" .  $_SESSION['UserID'] . "'
							AND locationusers.canupd=1
						WHERE purchorders.orderno = '" . $_GET['ModifyOrderNumber'] . "'";

$ErrMsg = _('The order cannot be retrieved because');
$DbgMsg = _('The SQL statement that was used and failed was');
$GetOrdHdrResult = DB_query($OrderHeaderSQL, $ErrMsg, $DbgMsg);

if (DB_num_rows($GetOrdHdrResult) == 1 and !isset($_SESSION['PO' . $Identifier]->OrderNo)) {

	$MyRow = DB_fetch_array($GetOrdHdrResult);
	$_SESSION['PO' . $Identifier]->OrderNo = $_GET['ModifyOrderNumber'];
	$_SESSION['PO' . $Identifier]->SupplierID = $MyRow['supplierno'];
	$_SESSION['PO' . $Identifier]->SupplierName = $MyRow['suppname'];
	$_SESSION['PO' . $Identifier]->CurrCode = $MyRow['currcode'];
	$_SESSION['PO' . $Identifier]->CurrDecimalPlaces = $MyRow['decimalplaces'];
	$_SESSION['PO' . $Identifier]->Orig_OrderDate = $MyRow['orddate'];
	$_SESSION['PO' . $Identifier]->AllowPrintPO = $MyRow['allowprint'];
	$_SESSION['PO' . $Identifier]->DatePurchaseOrderPrinted = $MyRow['dateprinted'];
	$_SESSION['PO' . $Identifier]->Comments = $MyRow['comments'];
	$_SESSION['PO' . $Identifier]->ExRate = $MyRow['rate'];
	$_SESSION['PO' . $Identifier]->Location = $MyRow['intostocklocation'];
	$_SESSION['PO' . $Identifier]->Initiator = $MyRow['initiator'];
	$_SESSION['PO' . $Identifier]->Authoriser = $MyRow['authoriser'];
	$_SESSION['PO' . $Identifier]->RequisitionNo = $MyRow['requisitionno'];
	$_SESSION['PO' . $Identifier]->DelAdd1 = $MyRow['deladd1'];
	$_SESSION['PO' . $Identifier]->DelAdd2 = $MyRow['deladd2'];
	$_SESSION['PO' . $Identifier]->DelAdd3 = $MyRow['deladd3'];
	$_SESSION['PO' . $Identifier]->DelAdd4 = $MyRow['deladd4'];
	$_SESSION['PO' . $Identifier]->DelAdd5 = $MyRow['deladd5'];
	$_SESSION['PO' . $Identifier]->DelAdd6 = $MyRow['deladd6'];
	$_SESSION['PO' . $Identifier]->Tel = $MyRow['tel'];
	$_SESSION['PO' . $Identifier]->SuppDelAdd1 = $MyRow['suppdeladdress1'];
	$_SESSION['PO' . $Identifier]->SuppDelAdd2 = $MyRow['suppdeladdress2'];
	$_SESSION['PO' . $Identifier]->SuppDelAdd3 = $MyRow['suppdeladdress3'];
	$_SESSION['PO' . $Identifier]->SuppDelAdd4 = $MyRow['suppdeladdress4'];
	$_SESSION['PO' . $Identifier]->SuppDelAdd5 = $MyRow['suppdeladdress5'];
	$_SESSION['PO' . $Identifier]->SuppDelAdd6 = $MyRow['suppdeladdress6'];
	$_SESSION['PO' . $Identifier]->SupplierContact = $MyRow['suppliercontact'];
	$_SESSION['PO' . $Identifier]->SuppTel = $MyRow['supptel'];
	$_SESSION['PO' . $Identifier]->Contact = $MyRow['contact'];
	$_SESSION['PO' . $Identifier]->Managed = $MyRow['managed'];
	$_SESSION['PO' . $Identifier]->Version = $MyRow['version'];
	$_SESSION['PO' . $Identifier]->Port = $MyRow['port'];
	$_SESSION['PO' . $Identifier]->DeliveryBy = $MyRow['deliveryby'];
	$_SESSION['PO' . $Identifier]->Status = $MyRow['status'];
	$_SESSION['PO' . $Identifier]->StatusComments = html_entity_decode($MyRow['stat_comment'], ENT_QUOTES, 'UTF-8');
	$_SESSION['PO' . $Identifier]->DeliveryDate = ConvertSQLDate($MyRow['deliverydate']);
	$_SESSION['PO' . $Identifier]->PaymentTerms = $MyRow['paymentterms'];
	$_SESSION['ExistingOrder'] = $_SESSION['PO' . $Identifier]->OrderNo;

	$SupplierSQL = "SELECT suppliers.supplierid,
									suppliers.suppname,
									suppliers.address1,
									suppliers.address2,
									suppliers.address3,
									suppliers.address4,
									suppliers.address5,
									suppliers.address6,
									suppliers.currcode
							FROM suppliers
							WHERE suppliers.supplierid='" . DB_escape_string($_SESSION['PO' . $Identifier]->SupplierID) . "'
							ORDER BY suppliers.supplierid";

	$ErrMsg = _('The searched supplier records requested cannot be retrieved because');
	$Result_SuppSelect = DB_query($SupplierSQL, $ErrMsg);

	if (DB_num_rows($Result_SuppSelect) == 1) {
		$MyRow = DB_fetch_array($Result_SuppSelect);
	} elseif (DB_num_rows($Result_SuppSelect) == 0) {
		prnMsg(_('No supplier records contain the selected text') . ' - ' . _('please alter your search criteria and try again'), 'info');
	}

	/*now populate the line PO array with the purchase order details records */

	$LineItemsSQL = "SELECT podetailitem,
							purchorderdetails.itemcode,
							stockmaster.description,
							purchorderdetails.deliverydate,
							purchorderdetails.itemdescription,
							glcode,
							accountname,
							purchorderdetails.qtyinvoiced,
							purchorderdetails.unitprice,
							stockmaster.units,
							purchorderdetails.quantityord,
							purchorderdetails.quantityrecd,
							purchorderdetails.shiptref,
							purchorderdetails.completed,
							purchorderdetails.jobref,
							purchorderdetails.stdcostunit,
							stockmaster.controlled,
							stockmaster.serialised,
							stockmaster.decimalplaces,
							purchorderdetails.assetid,
							purchorderdetails.conversionfactor,
							purchorderdetails.suppliersunit,
							purchorderdetails.suppliers_partno
						FROM purchorderdetails
						LEFT JOIN stockmaster
							ON purchorderdetails.itemcode=stockmaster.stockid
						INNER JOIN purchorders
							ON purchorders.orderno=purchorderdetails.orderno
						LEFT JOIN chartmaster
							ON purchorderdetails.glcode=chartmaster.accountcode
						WHERE purchorderdetails.completed=0
							AND purchorderdetails.orderno ='" . $_GET['ModifyOrderNumber'] . "'
							AND chartmaster.language ='" . $_SESSION['ChartLanguage'] . "'
						ORDER BY podetailitem";

	$ErrMsg = _('The lines on the purchase order cannot be retrieved because');
	$DbgMsg = _('The SQL statement that was used to retrieve the purchase order lines was');
	$LineItemsResult = DB_query($LineItemsSQL, $ErrMsg, $DbgMsg);

	if (DB_num_rows($LineItemsResult) > 0) {

		while ($MyRow = DB_fetch_array($LineItemsResult)) {

			if (is_null($MyRow['glcode'])) {
				$GLCode = '';
			} else {
				$GLCode = $MyRow['glcode'];
			}
			if (is_null($MyRow['units'])) {
				$Units = _('each');
			} else {
				$Units = $MyRow['units'];
			}
			if (is_null($MyRow['itemcode'])) {
				$StockId = '';
			} else {
				$StockId = $MyRow['itemcode'];
			}

			$_SESSION['PO' . $Identifier]->add_to_order($_SESSION['PO' . $Identifier]->LinesOnOrder + 1, $StockId, $MyRow['serialised'], $MyRow['controlled'], $MyRow['quantityord'], stripslashes($MyRow['itemdescription']), $MyRow['unitprice'], $Units, $GLCode, ConvertSQLDate($MyRow['deliverydate']), $MyRow['shiptref'], $MyRow['completed'], $MyRow['jobref'], $MyRow['qtyinvoiced'], $MyRow['quantityrecd'], $MyRow['accountname'], $MyRow['decimalplaces'], $MyRow['suppliersunit'], $MyRow['conversionfactor'], 1, $MyRow['suppliers_partno'], $MyRow['assetid']);

			$_SESSION['PO' . $Identifier]->LineItems[$_SESSION['PO' . $Identifier]->LinesOnOrder]->PODetailRec = $MyRow['podetailitem'];
			$_SESSION['PO' . $Identifier]->LineItems[$_SESSION['PO' . $Identifier]->LinesOnOrder]->StandardCost = $MyRow['stdcostunit'];
			/*Needed for receiving goods and GL interface */
		}
		/* line PO from purchase order details */
	} //end is there were lines on the order
} // end if there was a header for the order
?>