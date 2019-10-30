<?php
include ('includes/DefinePOClass.php');
include ('includes/SQL_CommonFunctions.php');

/* Session started in header.php for password checking
 * and authorisation level check
*/
include ('includes/session.php');
$Title = _('Purchase Order Items');

$Identifier = $_GET['identifier'];

/* If a purchase order header doesn't exist, then go to
 * PO_Header.php to create one
*/
if (!isset($_SESSION['PO' . $Identifier])) {
	header('Location:' . $RootPath . '/PO_Header.php');
	exit;
} //end if (!isset($_SESSION['PO'.$Identifier]))
include ('includes/header.php');

if (!isset($_POST['Commit'])) {
	echo '<div class="toplink">
			<a href="', $RootPath, '/PO_Header.php?identifier=', urlencode($Identifier), '">', _('Back To Purchase Order Header'), '</a>
		</div>';
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/supplier.png" title="', _('Purchase Order'), '" alt="" />', ' ', _('Purchase Order Details'), '
		</p>';
} //!isset($_POST['Commit'])
if (isset($_POST['UpdateLines']) or isset($_POST['Commit']) or isset($_POST['RefreshPrices'])) {
	foreach ($_SESSION['PO' . $Identifier]->LineItems as $POLine) {
		if ($POLine->Deleted == false) {
			if (!is_numeric(filter_number_format($_POST['ConversionFactor' . $POLine->LineNo]))) {
				prnMsg(_('The conversion factor is expected to be numeric - the figure which converts from our units to the supplier units. e.g. if the supplier units is a tonne and our unit is a kilogram then the conversion factor that converts our unit to the suppliers unit is 1000'), 'error');
				$_SESSION['PO' . $Identifier]->LineItems[$POLine->LineNo]->ConversionFactor = 1;
			} //!is_numeric(filter_number_format($_POST['ConversionFactor' . $POLine->LineNo]))
			else { //a valid number for the conversion factor is entered
				$_SESSION['PO' . $Identifier]->LineItems[$POLine->LineNo]->ConversionFactor = filter_number_format($_POST['ConversionFactor' . $POLine->LineNo]);
			}
			if (!is_numeric(filter_number_format($_POST['SuppQty' . $POLine->LineNo]))) {
				prnMsg(_('The quantity in the supplier units is expected to be numeric. Please re-enter as a number'), 'error');
			} //!is_numeric(filter_number_format($_POST['SuppQty' . $POLine->LineNo]))
			else { //ok to update the PO object variables
				$_SESSION['PO' . $Identifier]->LineItems[$POLine->LineNo]->Quantity = round(filter_number_format($_POST['SuppQty' . $POLine->LineNo]) * $_SESSION['PO' . $Identifier]->LineItems[$POLine->LineNo]->ConversionFactor, $_SESSION['PO' . $Identifier]->LineItems[$POLine->LineNo]->DecimalPlaces);
			}
			if (!is_numeric(filter_number_format($_POST['SuppPrice' . $POLine->LineNo]))) {
				prnMsg(_('The supplier price is expected to be numeric. Please re-enter as a number'), 'error');
			} //!is_numeric(filter_number_format($_POST['SuppPrice' . $POLine->LineNo]))
			else { //ok to update the PO object variables
				$_SESSION['PO' . $Identifier]->LineItems[$POLine->LineNo]->Price = filter_number_format($_POST['SuppPrice' . $POLine->LineNo]) / $_SESSION['PO' . $Identifier]->LineItems[$POLine->LineNo]->ConversionFactor;
			}
			$_SESSION['PO' . $Identifier]->LineItems[$POLine->LineNo]->ReqDelDate = $_POST['ReqDelDate' . $POLine->LineNo];
			$_SESSION['PO' . $Identifier]->LineItems[$POLine->LineNo]->ItemDescription = $_POST['ItemDescription' . $POLine->LineNo];
		} //$POLine->Deleted == false
		
	} //$_SESSION['PO' . $Identifier]->LineItems as $POLine
	
} //isset($_POST['UpdateLines']) or isset($_POST['Commit'])
//Refresh the prices
if (isset($_POST['RefreshPrices'])) {
	foreach ($_SESSION['PO' . $Identifier]->LineItems as $POLine) {
		$SQL = "SELECT description,
						longdescription,
						stockid,
						units,
						decimalplaces,
						stockact,
						accountname
					FROM stockmaster
					INNER JOIN stockcategory
						ON stockcategory.categoryid = stockmaster.categoryid
					INNER JOIN chartmaster
						ON chartmaster.accountcode = stockcategory.stockact
					WHERE  stockmaster.stockid = '" . $POLine->StockID . "'
						AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'";

		$ErrMsg = _('The item details for') . ' ' . $POLine->StockID . ' ' . _('could not be retrieved because');
		$DbgMsg = _('The SQL used to retrieve the item details but failed was');
		$ItemResult = DB_query($SQL, $ErrMsg, $DbgMsg);
		$ItemRow = DB_fetch_array($ItemResult);

		$SQL = "SELECT price,
						conversionfactor,
						supplierdescription,
						suppliersuom,
						suppliers_partno,
						leadtime,
						MAX(purchdata.effectivefrom) AS latesteffectivefrom
					FROM purchdata
					WHERE purchdata.supplierno = '" . $_SESSION['PO' . $Identifier]->SupplierID . "'
						AND purchdata.effectivefrom <=CURRENT_DATE
						AND purchdata.stockid = '" . $POLine->StockID . "'
						AND qtygreaterthan<'" . $POLine->Quantity . "'
						GROUP BY purchdata.price,
								purchdata.conversionfactor,
								purchdata.supplierdescription,
								purchdata.suppliersuom,
								purchdata.suppliers_partno,
								purchdata.leadtime
						ORDER BY latesteffectivefrom DESC,
								 qtygreaterthan DESC LIMIT 1";
		$ErrMsg = _('The purchasing data for') . ' ' . $POLine->StockID . ' ' . _('could not be retrieved because');
		$DbgMsg = _('The SQL used to retrieve the purchasing data but failed was');
		$PurchDataResult = DB_query($SQL, $ErrMsg, $DbgMsg);
		if (DB_num_rows($PurchDataResult) > 0) { //the purchasing data is set up
			$PurchRow = DB_fetch_array($PurchDataResult);
			/* Now to get the applicable discounts */
			$SQL = "SELECT discountpercent,
							discountamount
						FROM supplierdiscounts
						WHERE supplierno= '" . $_SESSION['PO' . $Identifier]->SupplierID . "'
							AND effectivefrom <=CURRENT_DATE
							AND effectiveto >=CURRENT_DATE
							AND stockid = '" . $POLine->StockID . "'";

			$ItemDiscountPercent = 0;
			$ItemDiscountAmount = 0;
			$ErrMsg = _('Could not retrieve the supplier discounts applicable to the item');
			$DbgMsg = _('The SQL used to retrive the supplier discounts that failed was');
			$DiscountResult = DB_query($SQL, $ErrMsg, $DbgMsg);
			while ($DiscountRow = DB_fetch_array($DiscountResult)) {
				$ItemDiscountPercent+= $DiscountRow['discountpercent'];
				$ItemDiscountAmount+= $DiscountRow['discountamount'];
			}
			if ($ItemDiscountPercent != 0) {
				prnMsg(_('Taken accumulated supplier percentage discounts of') . ' ' . locale_number_format($ItemDiscountPercent * 100, 2) . '%', 'info');
			}
			if ($ItemDiscountAmount != 0) {
				prnMsg(_('Taken accumulated round sum supplier discount of') . ' ' . $_SESSION['PO' . $Identifier]->CurrCode . ' ' . locale_number_format($ItemDiscountAmount, $_SESSION['PO' . $Identifier]->CurrDecimalPlaces) . ' (' . _('per supplier unit') . ')', 'info');
			}
			$PurchPrice = ($PurchRow['price'] * (1 - $ItemDiscountPercent) - $ItemDiscountAmount) / $PurchRow['conversionfactor'];
			$ConversionFactor = $PurchRow['conversionfactor'];
			if (mb_strlen($PurchRow['supplierdescription']) > 2) {
				$SupplierDescription = $PurchRow['supplierdescription'];
			} //mb_strlen($PurchRow['supplierdescription']) > 2
			else {
				$SupplierDescription = $ItemRow['description'];
			}
			$SuppliersUnitOfMeasure = $PurchRow['suppliersuom'];
			$SuppliersPartNo = $PurchRow['suppliers_partno'];
			$LeadTime = $PurchRow['leadtime'];
			/* Work out the delivery date based on today + lead time
			 * if > header DeliveryDate then set DeliveryDate to today + leadtime
			*/
			$DeliveryDate = DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', $LeadTime);
			if (Date1GreaterThanDate2($_SESSION['PO' . $Identifier]->DeliveryDate, $DeliveryDate)) {
				$DeliveryDate = $_SESSION['PO' . $Identifier]->DeliveryDate;
			} //!Date1GreaterThanDate2($DeliveryDate, $_SESSION['PO' . $Identifier]->DeliveryDate)
			
		} //DB_num_rows($PurchDataResult) > 0
		else { // no purchasing data setup
			$PurchPrice = 0;
			$ConversionFactor = 1;
			$SupplierDescription = $ItemRow['description'];
			$SuppliersUnitOfMeasure = $ItemRow['units'];
			$SuppliersPartNo = '';
			$LeadTime = 1;
			$DeliveryDate = $_SESSION['PO' . $Identifier]->DeliveryDate;
		}
		$POLine->Price = $PurchPrice;
	}
}

if (isset($_POST['Commit'])) {
	/*User wishes to commit the order to the database */

	/*First do some validation
	 *Is the delivery information all entered
	*/
	$InputError = 0;
	/*Start off assuming the best */
	if ($_SESSION['PO' . $Identifier]->DelAdd1 == '' or mb_strlen($_SESSION['PO' . $Identifier]->DelAdd1) < 3) {
		prnMsg(_('The purchase order cannot be committed to the database because there is no delivery street address specified'), 'error');
		$InputError = 1;
	} //$_SESSION['PO' . $Identifier]->DelAdd1 == '' or mb_strlen($_SESSION['PO' . $Identifier]->DelAdd1) < 3
	elseif ($_SESSION['PO' . $Identifier]->Location == '' or !isset($_SESSION['PO' . $Identifier]->Location)) {
		prnMsg(_('The purchase order can not be committed to the database because there is no location specified to book any stock items into'), 'error');
		$InputError = 1;
	} //$_SESSION['PO' . $Identifier]->Location == '' or !isset($_SESSION['PO' . $Identifier]->Location)
	elseif ($_SESSION['PO' . $Identifier]->LinesOnOrder <= 0) {
		prnMsg(_('The purchase order can not be committed to the database because there are no lines entered on this order'), 'error');
		$InputError = 1;
	} //$_SESSION['PO' . $Identifier]->LinesOnOrder <= 0
	/*If all clear then proceed to update the database
	*/
	if ($InputError != 1) {
		$Result = DB_Txn_Begin();

		/*figure out what status to set the order to */
		if (IsEmailAddress($_SESSION['UserEmail'])) {
			$UserDetails = ' <a href="mailto:' . $_SESSION['UserEmail'] . '">' . $_SESSION['UsersRealName'] . '</a>';
		} //IsEmailAddress($_SESSION['UserEmail'])
		else {
			$UserDetails = ' ' . $_SESSION['UsersRealName'] . ' ';
		}
		if ($_SESSION['AutoAuthorisePO'] == 1) {
			//if the user has authority to authorise the PO then it will automatically be authorised
			$AuthSQL = "SELECT authlevel
						FROM purchorderauth
						WHERE userid='" . $_SESSION['UserID'] . "'
						AND currabrev='" . $_SESSION['PO' . $Identifier]->CurrCode . "'";

			$AuthResult = DB_query($AuthSQL);
			$AuthRow = DB_fetch_array($AuthResult);

			if (DB_num_rows($AuthResult) > 0 and $AuthRow['authlevel'] > $_SESSION['PO' . $Identifier]->Order_Value()) { //user has authority to authrorise as well as create the order
				$StatusComment = date($_SESSION['DefaultDateFormat']) . ' - ' . _('Order Created and Authorised by') . $UserDetails . '<br />' . $_SESSION['PO' . $Identifier]->StatusComments . '<br />';
				$_SESSION['PO' . $Identifier]->AllowPrintPO = 1;
				$_SESSION['PO' . $Identifier]->Authoriser = $_SESSION['UserID'];
				$_SESSION['PO' . $Identifier]->Status = 'Authorised';
			} //DB_num_rows($AuthResult) > 0 and $AuthRow['authlevel'] > $_SESSION['PO' . $Identifier]->Order_Value()
			else { // no authority to authorise this order
				if (DB_num_rows($AuthResult) == 0) {
					$AuthMessage = _('Your authority to approve purchase orders in') . ' ' . $_SESSION['PO' . $Identifier]->CurrCode . ' ' . _('has not yet been set up') . '<br />';
				} //DB_num_rows($AuthResult) == 0
				else {
					$AuthMessage = _('You can only authorise up to') . ' ' . $_SESSION['PO' . $Identifier]->CurrCode . ' ' . $AuthRow['authlevel'] . '.<br />';
				}

				prnMsg(_('You do not have permission to authorise this purchase order') . '.<br />' . _('This order is for') . ' ' . $_SESSION['PO' . $Identifier]->CurrCode . ' ' . $_SESSION['PO' . $Identifier]->Order_Value() . '. ' . $AuthMessage . _('If you think this is a mistake please contact the systems administrator') . '<br />' . _('The order will be created with a status of pending and will require authorisation'), 'warn');

				$_SESSION['PO' . $Identifier]->AllowPrintPO = 0;
				$StatusComment = date($_SESSION['DefaultDateFormat']) . ' - ' . _('Order Created by') . $UserDetails . '<br />' . $_SESSION['PO' . $Identifier]->StatusComments . '<br />';
				$_SESSION['PO' . $Identifier]->Status = 'Pending';
			}
		} //$_SESSION['AutoAuthorisePO'] == 1
		else { //auto authorise is set to off
			$_SESSION['PO' . $Identifier]->AllowPrintPO = 0;
			$StatusComment = date($_SESSION['DefaultDateFormat']) . ' - ' . _('Order Created by') . $UserDetails . ' - ' . $_SESSION['PO' . $Identifier]->StatusComments . '<br />';
			$_SESSION['PO' . $Identifier]->Status = 'Pending';
		}

		if ($_SESSION['ExistingOrder'] == 0) {
			/*its a new order to be inserted */

			/*Get the order number */
			$_SESSION['PO' . $Identifier]->OrderNo = GetNextTransNo(18);

			/*Insert to purchase order header record */
			$SQL = "INSERT INTO purchorders ( orderno,
											supplierno,
											comments,
											orddate,
											rate,
											initiator,
											authoriser,
											requisitionno,
											intostocklocation,
											deladd1,
											deladd2,
											deladd3,
											deladd4,
											deladd5,
											deladd6,
											tel,
											suppdeladdress1,
											suppdeladdress2,
											suppdeladdress3,
											suppdeladdress4,
											suppdeladdress5,
											suppdeladdress6,
											suppliercontact,
											supptel,
											contact,
											version,
											revised,
											deliveryby,
											status,
											stat_comment,
											deliverydate,
											paymentterms,
											allowprint)
							VALUES(	'" . $_SESSION['PO' . $Identifier]->OrderNo . "',
									'" . $_SESSION['PO' . $Identifier]->SupplierID . "',
									'" . $_SESSION['PO' . $Identifier]->Comments . "',
									CURRENT_DATE,
									'" . $_SESSION['PO' . $Identifier]->ExRate . "',
									'" . $_SESSION['PO' . $Identifier]->Initiator . "',
									'" . $_SESSION['PO' . $Identifier]->Authoriser . "',
									'" . $_SESSION['PO' . $Identifier]->RequisitionNo . "',
									'" . $_SESSION['PO' . $Identifier]->Location . "',
									'" . $_SESSION['PO' . $Identifier]->DelAdd1 . "',
									'" . $_SESSION['PO' . $Identifier]->DelAdd2 . "',
									'" . $_SESSION['PO' . $Identifier]->DelAdd3 . "',
									'" . $_SESSION['PO' . $Identifier]->DelAdd4 . "',
									'" . $_SESSION['PO' . $Identifier]->DelAdd5 . "',
									'" . $_SESSION['PO' . $Identifier]->DelAdd6 . "',
									'" . $_SESSION['PO' . $Identifier]->Tel . "',
									'" . $_SESSION['PO' . $Identifier]->SuppDelAdd1 . "',
									'" . $_SESSION['PO' . $Identifier]->SuppDelAdd2 . "',
									'" . $_SESSION['PO' . $Identifier]->SuppDelAdd3 . "',
									'" . $_SESSION['PO' . $Identifier]->SuppDelAdd4 . "',
									'" . $_SESSION['PO' . $Identifier]->SuppDelAdd5 . "',
									'" . $_SESSION['PO' . $Identifier]->SuppDelAdd6 . "',
									'" . $_SESSION['PO' . $Identifier]->SupplierContact . "',
									'" . $_SESSION['PO' . $Identifier]->SuppTel . "',
									'" . $_SESSION['PO' . $Identifier]->Contact . "',
									'" . $_SESSION['PO' . $Identifier]->Version . "',
									CURRENT_DATE,
									'" . $_SESSION['PO' . $Identifier]->DeliveryBy . "',
									'" . $_SESSION['PO' . $Identifier]->Status . "',
									'" . htmlspecialchars($StatusComment, ENT_QUOTES, 'UTF-8') . "',
									'" . FormatDateForSQL($_SESSION['PO' . $Identifier]->DeliveryDate) . "',
									'" . $_SESSION['PO' . $Identifier]->PaymentTerms . "',
									'" . $_SESSION['PO' . $Identifier]->AllowPrintPO . "' )";

			$ErrMsg = _('The purchase order header record could not be inserted into the database because');
			$DbgMsg = _('The SQL statement used to insert the purchase order header record and failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			/*Insert the purchase order detail records */
			foreach ($_SESSION['PO' . $Identifier]->LineItems as $POLine) {
				if ($POLine->Deleted == False) {
					$SQL = "INSERT INTO purchorderdetails (orderno,
														itemcode,
														deliverydate,
														itemdescription,
														glcode,
														unitprice,
														quantityord,
														shiptref,
														jobref,
														suppliersunit,
														suppliers_partno,
														assetid,
														conversionfactor )
									VALUES ('" . $_SESSION['PO' . $Identifier]->OrderNo . "',
											'" . $POLine->StockID . "',
											'" . FormatDateForSQL($POLine->ReqDelDate) . "',
											'" . DB_escape_string($POLine->ItemDescription) . "',
											'" . $POLine->GLCode . "',
											'" . $POLine->Price . "',
											'" . $POLine->Quantity . "',
											'" . $POLine->ShiptRef . "',
											'" . $POLine->JobRef . "',
											'" . $POLine->SuppliersUnit . "',
											'" . $POLine->Suppliers_PartNo . "',
											'" . $POLine->AssetID . "',
											'" . $POLine->ConversionFactor . "')";
					$ErrMsg = _('One of the purchase order detail records could not be inserted into the database because');
					$DbgMsg = _('The SQL statement used to insert the purchase order detail record and failed was');

					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				} //$POLine->Deleted == False
				
			} //$_SESSION['PO' . $Identifier]->LineItems as $POLine
			/* end of the loop round the detail line items on the order */
			prnMsg(_('Purchase Order') . ' ' . $_SESSION['PO' . $Identifier]->OrderNo . ' ' . _('on') . ' ' . $_SESSION['PO' . $Identifier]->SupplierName . ' ' . _('has been created'), 'success');
			if ($_SESSION['PO' . $Identifier]->AllowPrintPO == 1 and ($_SESSION['PO' . $Identifier]->Status == 'Authorised' or $_SESSION['PO' . $Identifier]->Status == 'Printed')) {
				echo '<div class="centre">
						<a target="_blank" href="', $RootPath, '/PO_PDFPurchOrder.php?OrderNo=', urlencode($_SESSION['PO' . $Identifier]->OrderNo), '">', _('Print Purchase Order'), '</a>
					</div>';
			}
		} //$_SESSION['ExistingOrder'] == 0
		else {
			/*its an existing order need to update the old order info */
			/*Check to see if there are any incomplete lines on the order */
			$Completed = true; //assume it is completed i.e. all lines are flagged as completed
			foreach ($_SESSION['PO' . $Identifier]->LineItems as $POLine) {
				if ($POLine->Completed == 0) {
					$Completed = false;
					break;
				} //$POLine->Completed == 0
				
			} //$_SESSION['PO' . $Identifier]->LineItems as $POLine
			if ($Completed) {
				$_SESSION['PO' . $Identifier]->Status = 'Completed';
				$_SESSION['PO' . $Identifier]->StatusComments = date($_SESSION['DefaultDateFormat']) . ' - ' . _('Order completed by') . $UserDetails . '<br />' . $_SESSION['PO' . $Identifier]->StatusComments;
			} //$Completed
			else {
				$_SESSION['PO' . $Identifier]->StatusComments = date($_SESSION['DefaultDateFormat']) . ' - ' . _('Order modified by') . $UserDetails . '<br />' . $_SESSION['PO' . $Identifier]->StatusComments;
			}
			/*Update the purchase order header with any changes */

			$SQL = "UPDATE purchorders SET supplierno = '" . DB_escape_string($_SESSION['PO' . $Identifier]->SupplierID) . "' ,
										comments='" . $_SESSION['PO' . $Identifier]->Comments . "',
										rate='" . $_SESSION['PO' . $Identifier]->ExRate . "',
										initiator='" . $_SESSION['PO' . $Identifier]->Initiator . "',
										requisitionno= '" . $_SESSION['PO' . $Identifier]->RequisitionNo . "',
										version= '" . $_SESSION['PO' . $Identifier]->Version . "',
										deliveryby='" . $_SESSION['PO' . $Identifier]->DeliveryBy . "',
										deliverydate='" . FormatDateForSQL($_SESSION['PO' . $Identifier]->DeliveryDate) . "',
										revised= CURRENT_DATE,
										intostocklocation='" . $_SESSION['PO' . $Identifier]->Location . "',
										deladd1='" . $_SESSION['PO' . $Identifier]->DelAdd1 . "',
										deladd2='" . $_SESSION['PO' . $Identifier]->DelAdd2 . "',
										deladd3='" . $_SESSION['PO' . $Identifier]->DelAdd3 . "',
										deladd4='" . $_SESSION['PO' . $Identifier]->DelAdd4 . "',
										deladd5='" . $_SESSION['PO' . $Identifier]->DelAdd5 . "',
										deladd6='" . $_SESSION['PO' . $Identifier]->DelAdd6 . "',
										tel='" . $_SESSION['PO' . $Identifier]->Tel . "',
										suppdeladdress1='" . $_SESSION['PO' . $Identifier]->SuppDelAdd1 . "',
										suppdeladdress2='" . $_SESSION['PO' . $Identifier]->SuppDelAdd2 . "',
										suppdeladdress3='" . $_SESSION['PO' . $Identifier]->SuppDelAdd3 . "',
										suppdeladdress4='" . $_SESSION['PO' . $Identifier]->SuppDelAdd4 . "',
										suppdeladdress5='" . $_SESSION['PO' . $Identifier]->SuppDelAdd5 . "',
										suppdeladdress6='" . $_SESSION['PO' . $Identifier]->SuppDelAdd6 . "',
										suppliercontact='" . $_SESSION['PO' . $Identifier]->SupplierContact . "',
										supptel='" . $_SESSION['PO' . $Identifier]->SuppTel . "',
										contact='" . $_SESSION['PO' . $Identifier]->Contact . "',
										paymentterms='" . $_SESSION['PO' . $Identifier]->PaymentTerms . "',
										allowprint='" . $_SESSION['PO' . $Identifier]->AllowPrintPO . "',
										status = '" . $_SESSION['PO' . $Identifier]->Status . "',
										stat_comment = '" . htmlspecialchars($_SESSION['PO' . $Identifier]->StatusComments, ENT_QUOTES, 'UTF-8') . "'
										WHERE orderno = '" . $_SESSION['PO' . $Identifier]->OrderNo . "'";

			$ErrMsg = _('The purchase order could not be updated because');
			$DbgMsg = _('The SQL statement used to update the purchase order header record, that failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			/*Now Update the purchase order detail records */
			foreach ($_SESSION['PO' . $Identifier]->LineItems as $POLine) {
				if ($POLine->Deleted == true) {
					if ($POLine->PODetailRec != '') {
						$SQL = "DELETE FROM purchorderdetails WHERE podetailitem='" . $POLine->PODetailRec . "'";
						$ErrMsg = _('The purchase order detail line could not be deleted because');
						$DbgMsg = _('The SQL statement used to delete the purchase order detail record, that failed was');
						$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
					} //$POLine->PODetailRec != ''
					
				} //$POLine->Deleted == true
				else if ($POLine->PODetailRec == '') {
					/*When the purchase order line is an existing record the auto-increment
					 * field PODetailRec is given to the session for that POLine
					 * So it will only be a new POLine if PODetailRec is empty
					*/
					$SQL = "INSERT INTO purchorderdetails ( orderno,
														itemcode,
														deliverydate,
														itemdescription,
														glcode,
														unitprice,
														quantityord,
														shiptref,
														jobref,
														suppliersunit,
														suppliers_partno,
														assetid,
														conversionfactor)
													VALUES (
														'" . $_SESSION['PO' . $Identifier]->OrderNo . "',
														'" . $POLine->StockID . "',
														'" . FormatDateForSQL($POLine->ReqDelDate) . "',
														'" . DB_escape_string($POLine->ItemDescription) . "',
														'" . $POLine->GLCode . "',
														'" . $POLine->Price . "',
														'" . $POLine->Quantity . "',
														'" . $POLine->ShiptRef . "',
														'" . $POLine->JobRef . "',
														'" . $POLine->SuppliersUnit . "',
														'" . $POLine->Suppliers_PartNo . "',
														'" . $POLine->AssetID . "',
														'" . $POLine->ConversionFactor . "')";

				} //$POLine->PODetailRec == ''
				else {
					if ($POLine->Quantity == $POLine->QtyReceived) {
						$SQL = "UPDATE purchorderdetails SET itemcode='" . $POLine->StockID . "',
															deliverydate ='" . FormatDateForSQL($POLine->ReqDelDate) . "',
															itemdescription='" . DB_escape_string($POLine->ItemDescription) . "',
															glcode='" . $POLine->GLCode . "',
															unitprice='" . $POLine->Price . "',
															quantityord='" . $POLine->Quantity . "',
															shiptref='" . $POLine->ShiptRef . "',
															jobref='" . $POLine->JobRef . "',
															suppliersunit='" . $POLine->SuppliersUnit . "',
															suppliers_partno='" . DB_escape_string($POLine->Suppliers_PartNo) . "',
															completed=1,
															assetid='" . $POLine->AssetID . "',
															conversionfactor = '" . $POLine->ConversionFactor . "'
								WHERE podetailitem='" . $POLine->PODetailRec . "'";
					} //$POLine->Quantity == $POLine->QtyReceived
					else {
						$SQL = "UPDATE purchorderdetails SET itemcode='" . $POLine->StockID . "',
															deliverydate ='" . FormatDateForSQL($POLine->ReqDelDate) . "',
															itemdescription='" . DB_escape_string($POLine->ItemDescription) . "',
															glcode='" . $POLine->GLCode . "',
															unitprice='" . $POLine->Price . "',
															quantityord='" . $POLine->Quantity . "',
															shiptref='" . $POLine->ShiptRef . "',
															jobref='" . $POLine->JobRef . "',
															suppliersunit='" . $POLine->SuppliersUnit . "',
															suppliers_partno='" . $POLine->Suppliers_PartNo . "',
															assetid='" . $POLine->AssetID . "',
															conversionfactor = '" . $POLine->ConversionFactor . "'
								WHERE podetailitem='" . $POLine->PODetailRec . "'";
					}
				}

				$ErrMsg = _('One of the purchase order detail records could not be updated because');
				$DbgMsg = _('The SQL statement used to update the purchase order detail record that failed was');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			} //$_SESSION['PO' . $Identifier]->LineItems as $POLine
			/* end of the loop round the detail line items on the order */
			prnMsg(_('Purchase Order') . ' ' . $_SESSION['PO' . $Identifier]->OrderNo . ' ' . _('has been updated'), 'success');
			if ($_SESSION['PO' . $Identifier]->AllowPrintPO == 1 and ($_SESSION['PO' . $Identifier]->Status == 'Authorised' or $_SESSION['PO' . $Identifier]->Status == 'Printed')) {
				echo '<div class="centre">
						<a target="_blank" href="', $RootPath, '/PO_PDFPurchOrder.php?OrderNo=', urlencode($_SESSION['PO' . $Identifier]->OrderNo), '">', _('Print Purchase Order'), '</a>
					</div>';
			} //$_SESSION['PO' . $Identifier]->AllowPrintPO == 1 and ($_SESSION['PO' . $Identifier]->Status == 'Authorised' or $_SESSION['PO' . $Identifier]->Status == 'Printed')
			
		}
		/*end of if its a new order or an existing one */

		$Result = DB_Txn_Commit();
		/* Only show the link to auto receive the order if the user has permission to receive goods and permission to authorise and has authorised the order */
		if ($_SESSION['PO' . $Identifier]->Status == 'Authorised' and in_array($_SESSION['PageSecurityArray']['GoodsReceived.php'], $_SESSION['AllowedPageSecurityTokens'])) {

			echo '<a href="SupplierInvoice.php?SupplierID=', urlencode($_SESSION['PO' . $Identifier]->SupplierID), '&amp;ReceivePO=', urlencode($_SESSION['PO' . $Identifier]->OrderNo), '&amp;DeliveryDate=', urlencode($_SESSION['PO' . $Identifier]->DeliveryDate), '">', _('Receive and Enter Purchase Invoice'), '</a>';
		} //$_SESSION['PO' . $Identifier]->Status == 'Authorised' and in_array(1001, $_SESSION['AllowedPageSecurityTokens'])
		unset($_SESSION['PO' . $Identifier]);
		/*Clear the PO data to allow a newy to be input*/
		include ('includes/footer.php');
		exit;
	} //$InputError != 1
	/*end if there were no input errors trapped */
} //isset($_POST['Commit'])
/* end of the code to do transfer the PO object to the database  - user hit the place PO*/

/* Always do the stuff below if not looking for a supplierid */

if (isset($_GET['Delete'])) {
	if ($_SESSION['PO' . $Identifier]->Some_Already_Received($_GET['Delete']) == 0) {
		$_SESSION['PO' . $Identifier]->remove_from_order($_GET['Delete']);
		include ('includes/PO_UnsetFormVbls.php');
	} //$_SESSION['PO' . $Identifier]->Some_Already_Received($_GET['Delete']) == 0
	else {
		prnMsg(_('This item cannot be deleted because some of it has already been received'), 'warn');
	}
} //isset($_GET['Delete'])
if (isset($_GET['Complete'])) {
	$_SESSION['PO' . $Identifier]->LineItems[$_GET['Complete']]->Completed = 1;
} //isset($_GET['Complete'])
if (isset($_POST['EnterLine'])) {
	/*Inputs from the form directly without selecting a stock item from the search */

	$AllowUpdate = true;
	/*always assume the best */
	if (!is_numeric(filter_number_format($_POST['Qty']))) {
		$AllowUpdate = false;
		prnMsg(_('Cannot Enter this order line') . '<br />' . _('The quantity of the order item must be numeric'), 'error');
	} //!is_numeric(filter_number_format($_POST['Qty']))
	if (filter_number_format($_POST['Qty']) < 0) {
		$AllowUpdate = false;
		prnMsg(_('Cannot Enter this order line') . '<br />' . _('The quantity of the ordered item entered must be a positive amount'), 'error');
	} //filter_number_format($_POST['Qty']) < 0
	if (!is_numeric(filter_number_format($_POST['Price']))) {
		$AllowUpdate = false;
		prnMsg(_('Cannot Enter this order line') . '<br />' . _('The price entered must be numeric'), 'error');
	} //!is_numeric(filter_number_format($_POST['Price']))
	if (!is_date($_POST['ReqDelDate'])) {
		$AllowUpdate = False;
		prnMsg(_('Cannot Enter this order line') . '</b><br />' . _('The date entered must be in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
	} //!is_date($_POST['ReqDelDate'])
	/*It's not a stock item
	 * need to check GL Code is valid if GLLink is active
	 * [icedlava] GL Code is required for non stock item variance in price vs purchase order when supplier invoice generated even if stock not linked to GL, but AP is else
	 * there will be an sql error  in SupplierInvoice.php without a valid GL Code
	*/
	if ($_SESSION['PO' . $Identifier]->GLLink == 1 or $_SESSION['CompanyRecord']['gllink_creditors'] == 1) {
		$SQL = "SELECT accountname
					FROM chartmaster
					WHERE accountcode ='" . $_POST['GLCode'] . "'
						AND language='" . $_SESSION['ChartLanguage'] . "'";
		$ErrMsg = _('The account details for') . ' ' . $_POST['GLCode'] . ' ' . _('could not be retrieved because');
		$DbgMsg = _('The SQL used to retrieve the details of the account, but failed was');
		$GLValidResult = DB_query($SQL, $ErrMsg, $DbgMsg, false, false);
		if (DB_error_no() != 0) {
			$AllowUpdate = false;
			prnMsg(_('The validation process for the GL Code entered could not be executed because') . ' ' . DB_error_msg(), 'error');
			if ($Debug == 1) {
				prnMsg(_('The SQL used to validate the code entered was') . ' ' . $SQL, 'error');
			} //$Debug == 1
			include ('includes/footer.php');
			exit;
		} //DB_error_no() != 0
		if (DB_num_rows($GLValidResult) == 0) {
			/*The GLCode entered does not exist */
			$AllowUpdate = false;
			prnMsg(_('Cannot enter this order line') . ':<br />' . _('The general ledger code') . ' - ' . $_POST['GLCode'] . ' ' . _('is not a general ledger code that is defined in the chart of accounts') . ' . ' . _('Please use a code that is already defined') . '. ' . _('See the Chart list from the link below'), 'error');
		} //DB_num_rows($GLValidResult) == 0
		else {
			$MyRow = DB_fetch_row($GLValidResult);
			$GLAccountName = $MyRow[0];
		}
	}
	/* dont bother checking the GL Code if there is no GL code to check ie not linked to GL */
	else {
		$_POST['GLCode'] = 0;
	}

	if ($_POST['AssetID'] != 'Not an Asset') {
		$ValidAssetResult = DB_query("SELECT assetid,
											description,
											costact
										FROM fixedassets
										INNER JOIN fixedassetcategories
										ON fixedassets.assetcategoryid=fixedassetcategories.categoryid
										WHERE assetid='" . $_POST['AssetID'] . "'");
		if (DB_num_rows($ValidAssetResult) == 0) { // then the asset id entered doesn't exist
			$AllowUpdate = false;
			prnMsg(_('An asset code was entered but it does not yet exist. Only pre-existing asset ids can be entered when ordering a fixed asset'), 'error');
		} //DB_num_rows($ValidAssetResult) == 0
		else {
			$AssetRow = DB_fetch_array($ValidAssetResult);
			$_POST['GLCode'] = $AssetRow['costact'];
			if ($_POST['ItemDescription'] == '') {
				$_POST['ItemDescription'] = $AssetRow['description'];
			} //$_POST['ItemDescription'] == ''
			
		}
	} //$_POST['AssetID'] != 'Not an Asset'
	/*end if an AssetID is entered */
	else {
		$_POST['AssetID'] = 0; // cannot commit a string to an integer field so make it 0 if AssetID = 'Not an Asset'
		
	}
	if (mb_strlen($_POST['ItemDescription']) <= 3) {
		$AllowUpdate = false;
		prnMsg(_('Cannot enter this order line') . ':<br />' . _('The description of the item being purchased is required where a non-stock item is being ordered'), 'warn');
	} //mb_strlen($_POST['ItemDescription']) <= 3
	if ($AllowUpdate == true) {
		//adding the non-stock item
		$_SESSION['PO' . $Identifier]->add_to_order($_SESSION['PO' . $Identifier]->LinesOnOrder + 1, '', 0, /*Serialised */
		0, /*Controlled */
		filter_number_format($_POST['Qty']), $_POST['ItemDescription'], filter_number_format($_POST['Price']), $_POST['SuppliersUnit'], $_POST['GLCode'], $_POST['ReqDelDate'], '', 0, '', 0, 0, $GLAccountName, 2, $_POST['SuppliersUnit'], 1, 1, '', $_POST['AssetID']);
		include ('includes/PO_UnsetFormVbls.php');
	} //$AllowUpdate == true
	
} //isset($_POST['EnterLine'])
/*end if Enter line button was hit - adding non stock items */

//Add variables $_SESSION['PO_ItemsResubmitForm' . $Identifier] and $_POST['PO_ItemsResubmitFormValue'] to prevent from page refreshing effect
$_SESSION['PO_ItemsResubmitForm' . $Identifier] = (empty($_SESSION['PO_ItemsResubmitForm' . $Identifier])) ? '1' : $_SESSION['PO_ItemsResubmitForm' . $Identifier];
if (isset($_POST['NewItem']) and !empty($_POST['PO_ItemsResubmitFormValue']) and $_SESSION['PO_ItemsResubmitForm' . $Identifier] == $_POST['PO_ItemsResubmitFormValue']) { //only submit values can be processed
	/* NewItem is set from the part selection list as the part code selected
	 * take the form entries and enter the data from the form into the PurchOrder class variable
	 * A series of form variables of the format "NewQty" with the ItemCode concatenated are created on the search for adding new
	 * items for each of these form variables need to parse out the item code and look up the details to add them to the purchase
	 * order  $_POST is of course the global array of all posted form variables
	*/

	foreach ($_POST as $FormVariableName => $Quantity) {
		/*The form entity name is of the format NewQtyX where X is the index number that identifies the stock item code held in the hidden StockIDX form variable
		 * */
		if (mb_substr($FormVariableName, 0, 6) == 'NewQty' and filter_number_format($Quantity) != 0) { //if the form variable represents a Qty to add to the order
			$ItemCode = $_POST['StockID' . mb_substr($FormVariableName, 6) ];
			$AlreadyOnThisOrder = 0;

			if ($_SESSION['PO_AllowSameItemMultipleTimes'] == false) {
				if (count($_SESSION['PO' . $Identifier]->LineItems) != 0) {
					foreach ($_SESSION['PO' . $Identifier]->LineItems as $OrderItem) {
						/* do a loop round the items on the order to see that the item is not already on this order */
						if (($OrderItem->StockID == $ItemCode) and ($OrderItem->Deleted == false)) {
							$AlreadyOnThisOrder = 1;
							prnMsg(_('The item') . ' ' . $ItemCode . ' ' . _('is already on this order') . '. ' . _('The system will not allow the same item on the order more than once') . '. ' . _('However you can change the quantity ordered of the existing line if necessary'), 'error');
						} //($OrderItem->StockID == $ItemCode) and ($OrderItem->Deleted == false)
						
					} //$_SESSION['PO' . $Identifier]->LineItems as $OrderItem
					/* end of the foreach loop to look for preexisting items of the same code */
				} //count($_SESSION['PO' . $Identifier]->LineItems) != 0
				
			} //$_SESSION['PO_AllowSameItemMultipleTimes'] == false
			if ($AlreadyOnThisOrder != 1 and filter_number_format($Quantity) > 0) {
				$SQL = "SELECT description,
							longdescription,
							stockid,
							units,
							decimalplaces,
							stockact,
							accountname
						FROM stockmaster
						INNER JOIN stockcategory
							ON stockcategory.categoryid = stockmaster.categoryid
						INNER JOIN chartmaster
							ON chartmaster.accountcode = stockcategory.stockact
						WHERE  stockmaster.stockid = '" . $ItemCode . "'
							AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'";

				$ErrMsg = _('The item details for') . ' ' . $ItemCode . ' ' . _('could not be retrieved because');
				$DbgMsg = _('The SQL used to retrieve the item details but failed was');
				$ItemResult = DB_query($SQL, $ErrMsg, $DbgMsg);
				if (DB_num_rows($ItemResult) == 1) {
					$ItemRow = DB_fetch_array($ItemResult);

					$SQL = "SELECT price,
								conversionfactor,
								supplierdescription,
								suppliersuom,
								suppliers_partno,
								leadtime,
								MAX(purchdata.effectivefrom) AS latesteffectivefrom
							FROM purchdata
							WHERE purchdata.supplierno = '" . $_SESSION['PO' . $Identifier]->SupplierID . "'
							AND purchdata.effectivefrom <=CURRENT_DATE
							AND purchdata.stockid = '" . $ItemCode . "'
							AND qtygreaterthan<'" . $Quantity . "'
							GROUP BY purchdata.price,
									purchdata.conversionfactor,
									purchdata.supplierdescription,
									purchdata.suppliersuom,
									purchdata.suppliers_partno,
									purchdata.leadtime
							ORDER BY latesteffectivefrom DESC,
									 qtygreaterthan DESC LIMIT 1";

					$ErrMsg = _('The purchasing data for') . ' ' . $ItemCode . ' ' . _('could not be retrieved because');
					$DbgMsg = _('The SQL used to retrieve the purchasing data but failed was');
					$PurchDataResult = DB_query($SQL, $ErrMsg, $DbgMsg);
					if (DB_num_rows($PurchDataResult) > 0) { //the purchasing data is set up
						$PurchRow = DB_fetch_array($PurchDataResult);
						/* Now to get the applicable discounts */
						$SQL = "SELECT discountpercent,
										discountamount
								FROM supplierdiscounts
								WHERE supplierno= '" . $_SESSION['PO' . $Identifier]->SupplierID . "'
								AND effectivefrom <=CURRENT_DATE
								AND effectiveto >=CURRENT_DATE
								AND stockid = '" . $ItemCode . "'";

						$ItemDiscountPercent = 0;
						$ItemDiscountAmount = 0;
						$ErrMsg = _('Could not retrieve the supplier discounts applicable to the item');
						$DbgMsg = _('The SQL used to retrive the supplier discounts that failed was');
						$DiscountResult = DB_query($SQL, $ErrMsg, $DbgMsg);
						while ($DiscountRow = DB_fetch_array($DiscountResult)) {
							$ItemDiscountPercent+= $DiscountRow['discountpercent'];
							$ItemDiscountAmount+= $DiscountRow['discountamount'];
						}
						if ($ItemDiscountPercent != 0) {
							prnMsg(_('Taken accumulated supplier percentage discounts of') . ' ' . locale_number_format($ItemDiscountPercent * 100, 2) . '%', 'info');
						}
						if ($ItemDiscountAmount != 0) {
							prnMsg(_('Taken accumulated round sum supplier discount of') . ' ' . $_SESSION['PO' . $Identifier]->CurrCode . ' ' . locale_number_format($ItemDiscountAmount, $_SESSION['PO' . $Identifier]->CurrDecimalPlaces) . ' (' . _('per supplier unit') . ')', 'info');
						}
						$PurchPrice = ($PurchRow['price'] * (1 - $ItemDiscountPercent) - $ItemDiscountAmount) / $PurchRow['conversionfactor'];
						$ConversionFactor = $PurchRow['conversionfactor'];
						if (mb_strlen($PurchRow['supplierdescription']) > 2) {
							$SupplierDescription = $PurchRow['supplierdescription'];
						} //mb_strlen($PurchRow['supplierdescription']) > 2
						else {
							$SupplierDescription = $ItemRow['description'];
						}
						$SuppliersUnitOfMeasure = $PurchRow['suppliersuom'];
						$SuppliersPartNo = $PurchRow['suppliers_partno'];
						$LeadTime = $PurchRow['leadtime'];
						/* Work out the delivery date based on today + lead time
						 * if > header DeliveryDate then set DeliveryDate to today + leadtime
						*/
						$DeliveryDate = DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', $LeadTime);
						if (Date1GreaterThanDate2($_SESSION['PO' . $Identifier]->DeliveryDate, $DeliveryDate)) {
							$DeliveryDate = $_SESSION['PO' . $Identifier]->DeliveryDate;
						} //!Date1GreaterThanDate2($DeliveryDate, $_SESSION['PO' . $Identifier]->DeliveryDate)
						
					} //DB_num_rows($PurchDataResult) > 0
					else { // no purchasing data setup
						$PurchPrice = 0;
						$ConversionFactor = 1;
						$SupplierDescription = $ItemRow['description'];
						$SuppliersUnitOfMeasure = $ItemRow['units'];
						$SuppliersPartNo = '';
						$LeadTime = 1;
						$DeliveryDate = $_SESSION['PO' . $Identifier]->DeliveryDate;
					}

					$_SESSION['PO' . $Identifier]->add_to_order($_SESSION['PO' . $Identifier]->LinesOnOrder + 1, $ItemCode, 0, /*Serialised */
					0, /*Controlled */
					filter_number_format($Quantity) * $ConversionFactor, /* Qty */
					$SupplierDescription, $PurchPrice, $ItemRow['units'], $ItemRow['stockact'], $DeliveryDate, 0, 0, 0, 0, 0, $ItemRow['accountname'], $ItemRow['decimalplaces'], $SuppliersUnitOfMeasure, $ConversionFactor, $LeadTime, $SuppliersPartNo);
				} //DB_num_rows($ItemResult) == 1
				else { //no rows returned by the SQL to get the item
					prnMsg(_('The item code') . ' ' . $ItemCode . ' ' . _('does not exist in the database and therefore cannot be added to the order'), 'error');
					if ($Debug == 1) {
						echo '<br />' . $SQL;
					} //$Debug == 1
					include ('includes/footer.php');
					exit;
				}
			} //$AlreadyOnThisOrder != 1 and filter_number_format($Quantity) > 0
			/* end of if not already on the order */
		} //mb_substr($FormVariableName, 0, 6) == 'NewQty' and filter_number_format($Quantity) != 0
		/* end if the $_POST has NewQty in the variable name */
	} //$_POST as $FormVariableName => $Quantity
	/* end loop around the $_POST array */
	$_SESSION['PO_ItemsResubmitForm' . $Identifier]++; //change the $_SESSION VALUE
	
} //isset($_POST['NewItem']) and !empty($_POST['PO_ItemsResubmitFormValue']) and $_SESSION['PO_ItemsResubmitForm' . $Identifier] == $_POST['PO_ItemsResubmitFormValue']
/* end of if its a new item */

if (isset($_POST['UploadFile'])) {
	if (isset($_FILES['CSVFile']) and $_FILES['CSVFile']['name']) {
		//check file info
		$FileName = $_FILES['CSVFile']['name'];
		$TempName = $_FILES['CSVFile']['tmp_name'];
		$FileSize = $_FILES['CSVFile']['size'];
		//get file handle
		$FileHandle = fopen($TempName, 'r');
		$Row = 0;
		$InsertNum = 0;
		while (($FileRow = fgetcsv($FileHandle, 10000, ",")) !== False) {
			++$Row;
			if (filter_number_format($FileRow[1]) != 0) { //if the form variable represents a Qty to add to the order
				$ItemCode = $FileRow[0];
				$Quantity = $FileRow[1];
				$AlreadyOnThisOrder = 0;

				if ($_SESSION['PO_AllowSameItemMultipleTimes'] == false) {
					if (count($_SESSION['PO' . $Identifier]->LineItems) != 0) {

						foreach ($_SESSION['PO' . $Identifier]->LineItems as $OrderItem) {

							/* do a loop round the items on the order to see that the item is not already on this order */
							if (($OrderItem->StockID == $ItemCode) and ($OrderItem->Deleted == false)) {
								$AlreadyOnThisOrder = 1;
								prnMsg(_('The item') . ' ' . $ItemCode . ' ' . _('is already on this order') . '. ' . _('The system will not allow the same item on the order more than once') . '. ' . _('However you can change the quantity ordered of the existing line if necessary'), 'error');
							}
						} /* end of the foreach loop to look for preexisting items of the same code */
					}
				}
				if ($AlreadyOnThisOrder != 1 and filter_number_format($Quantity) > 0) {
					$SQL = "SELECT description,
								longdescription,
								stockid,
								units,
								decimalplaces,
								stockact,
								accountname
							FROM stockmaster INNER JOIN stockcategory
							ON stockcategory.categoryid = stockmaster.categoryid
							INNER JOIN chartmaster
							ON chartmaster.accountcode = stockcategory.stockact
							WHERE  stockmaster.stockid = '" . $ItemCode . "'";
					$ErrMsg = _('The item details for') . ' ' . $ItemCode . ' ' . _('could not be retrieved because');
					$DbgMsg = _('The SQL used to retrieve the item details but failed was');
					$ItemResult = DB_query($SQL, $ErrMsg, $DbgMsg);
					if (DB_num_rows($ItemResult) == 1) {
						$ItemRow = DB_fetch_array($ItemResult);

						$SQL = "SELECT price,
									conversionfactor,
									supplierdescription,
									suppliersuom,
									suppliers_partno,
									leadtime,
									MAX(purchdata.effectivefrom) AS latesteffectivefrom
								FROM purchdata
								WHERE purchdata.supplierno = '" . $_SESSION['PO' . $Identifier]->SupplierID . "'
								AND purchdata.effectivefrom <='" . Date('Y-m-d') . "'
								AND purchdata.stockid = '" . $ItemCode . "'
								GROUP BY purchdata.price,
										purchdata.conversionfactor,
										purchdata.supplierdescription,
										purchdata.suppliersuom,
										purchdata.suppliers_partno,
										purchdata.leadtime
								ORDER BY latesteffectivefrom DESC";

						$ErrMsg = _('The purchasing data for') . ' ' . $ItemCode . ' ' . _('could not be retrieved because');
						$DbgMsg = _('The SQL used to retrieve the purchasing data but failed was');
						$PurchDataResult = DB_query($SQL, $ErrMsg, $DbgMsg);
						if (DB_num_rows($PurchDataResult) > 0) { //the purchasing data is set up
							$PurchRow = DB_fetch_array($PurchDataResult);

							/* Now to get the applicable discounts */
							$SQL = "SELECT discountpercent,
											discountamount
									FROM supplierdiscounts
									WHERE supplierno= '" . $_SESSION['PO' . $Identifier]->SupplierID . "'
									AND effectivefrom <='" . Date('Y-m-d') . "'
									AND effectiveto >='" . Date('Y-m-d') . "'
									AND stockid = '" . $ItemCode . "'";

							$ItemDiscountPercent = 0;
							$ItemDiscountAmount = 0;
							$ErrMsg = _('Could not retrieve the supplier discounts applicable to the item');
							$DbgMsg = _('The SQL used to retrive the supplier discounts that failed was');
							$DiscountResult = DB_query($SQL, $ErrMsg, $DbgMsg);
							while ($DiscountRow = DB_fetch_array($DiscountResult)) {
								$ItemDiscountPercent+= $DiscountRow['discountpercent'];
								$ItemDiscountAmount+= $DiscountRow['discountamount'];
							}
							if ($ItemDiscountPercent != 0) {
								prnMsg(_('Taken accumulated supplier percentage discounts of') . ' ' . locale_number_format($ItemDiscountPercent * 100, 2) . '%', 'info');
							}
							if ($ItemDiscountAmount != 0) {
								prnMsg(_('Taken accumulated round sum supplier discount of') . ' ' . $_SESSION['PO' . $Identifier]->CurrCode . ' ' . locale_number_format($ItemDiscountAmount, $_SESSION['PO' . $Identifier]->CurrDecimalPlaces) . ' (' . _('per supplier unit') . ')', 'info');
							}
							$PurchPrice = ($PurchRow['price'] * (1 - $ItemDiscountPercent) - $ItemDiscountAmount) / $PurchRow['conversionfactor'];
							$ConversionFactor = $PurchRow['conversionfactor'];
							if (mb_strlen($PurchRow['supplierdescription']) > 2) {
								$SupplierDescription = $PurchRow['supplierdescription'];
							} else {
								$SupplierDescription = $ItemRow['description'];
							}
							$SuppliersUnitOfMeasure = $PurchRow['suppliersuom'];
							$SuppliersPartNo = $PurchRow['suppliers_partno'];
							$LeadTime = $PurchRow['leadtime'];
							/* Work out the delivery date based on today + lead time
							 * if > header DeliveryDate then set DeliveryDate to today + leadtime
							*/
							$DeliveryDate = DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', $LeadTime);
							if (Date1GreaterThanDate2($_SESSION['PO' . $Identifier]->DeliveryDate, $DeliveryDate)) {
								$DeliveryDate = $_SESSION['PO' . $Identifier]->DeliveryDate;
							}
						} else { // no purchasing data setup
							$PurchPrice = 0;
							$ConversionFactor = 1;
							$SupplierDescription = $ItemRow['description'];
							$SuppliersUnitOfMeasure = $ItemRow['units'];
							$SuppliersPartNo = '';
							$LeadTime = 1;
							$DeliveryDate = $_SESSION['PO' . $Identifier]->DeliveryDate;
						}

						$_SESSION['PO' . $Identifier]->add_to_order($_SESSION['PO' . $Identifier]->LinesOnOrder + 1, $ItemCode, 0, /*Serialised */
						0, /*Controlled */
						filter_number_format($Quantity) * $ConversionFactor, /* Qty */
						$SupplierDescription, $PurchPrice, $ItemRow['units'], $ItemRow['stockact'], $DeliveryDate, 0, 0, 0, 0, 0, $ItemRow['accountname'], $ItemRow['decimalplaces'], $SuppliersUnitOfMeasure, $ConversionFactor, $LeadTime, $SuppliersPartNo);
						++$InsertNum;
					} else { //no rows returned by the SQL to get the item
						prnMsg(_('The item code') . ' ' . $ItemCode . ' ' . _('does not exist in the database and therefore cannot be added to the order'), 'error');
						if ($debug == 1) {
							echo '<br />' . $SQL;
						}
					}
				} /* end of if not already on the order */
			} /* end if the $_POST has NewQty in the variable name */
		}
	}
	$_SESSION['PO_ItemsResubmitForm' . $Identifier]++; //change the $_SESSION VALUE
	prnMsg($InsertNum . ' ' . _('of') . ' ' . $Row . ' ' . _('rows have been added to the order'), 'info');
} /* end of if its items uploaded from csv */

/* This is where the order as selected should be displayed  reflecting any deletions or insertions*/

echo '<form id="form1" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', urlencode($Identifier), '" method="post" enctype="multipart/form-data">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

/*need to set up entry for item description where not a stock item and GL Codes */

if (count($_SESSION['PO' . $Identifier]->LineItems) > 0 and !isset($_GET['Edit'])) {
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/supplier.png" title="', _('Purchase Order'), '" alt="" />  ', $_SESSION['PO' . $Identifier]->SupplierName;

	if (isset($_SESSION['PO' . $Identifier]->OrderNo)) {
		echo ' ', _('Purchase Order'), ' ', $_SESSION['PO' . $Identifier]->OrderNo;
	} //isset($_SESSION['PO' . $Identifier]->OrderNo)
	echo '<b>&nbsp;-&nbsp', _('Order Summary'), '</b>
		</p>';

	echo '<table cellpadding="2">
			<thead>
				<tr>
					<th class="SortedColumn">', _('Item Code'), '</th>
					<th class="SortedColumn">', _('Description'), '</th>
					<th>', _('Quantity Our Units'), '</th>
					<th>', _('Our Unit'), '</th>
					<th>', _('Price Our Units'), ' (', $_SESSION['PO' . $Identifier]->CurrCode, ')</th>
					<th>', _('Unit Conversion Factor'), '</th>
					<th>', _('Order Quantity'), '<br />', _('Supplier Units'), '</th>
					<th>', _('Supplier Unit'), '</th>
					<th>', _('Order Price'), '<br />', _('Supp Units'), ' (', $_SESSION['PO' . $Identifier]->CurrCode, ')</th>
					<th>', _('Sub-Total'), ' (', $_SESSION['PO' . $Identifier]->CurrCode, ')</th>
					<th>', _('Deliver By'), '</th>
					<th></th>
				</tr>
			</thead>';

	$_SESSION['PO' . $Identifier]->Total = 0;

	echo '<tbody>';
	foreach ($_SESSION['PO' . $Identifier]->LineItems as $POLine) {
		if ($POLine->Deleted == False) {
			$LineTotal = $POLine->Quantity * $POLine->Price;
			$DisplayLineTotal = locale_number_format($LineTotal, $_SESSION['PO' . $Identifier]->CurrDecimalPlaces);
			// Note if the price is greater than 1 use 2 decimal place, if the price is a fraction of 1, use 4 decimal places
			// This should help display where item-price is a fraction
			if ($POLine->Price > 1) {
				$DisplayPrice = locale_number_format($POLine->Price, $_SESSION['PO' . $Identifier]->CurrDecimalPlaces);
				$SuppPrice = locale_number_format(round(($POLine->Price * $POLine->ConversionFactor), $_SESSION['PO' . $Identifier]->CurrDecimalPlaces), $_SESSION['PO' . $Identifier]->CurrDecimalPlaces);
			} //$POLine->Price > 1
			else {
				$DisplayPrice = locale_number_format($POLine->Price, 4);
				$SuppPrice = locale_number_format(round(($POLine->Price * $POLine->ConversionFactor), 4), 4);
			}

			echo '<tr class="striped_row">
					<td>', $POLine->StockID, '</td>
					<td><input type="text" name="ItemDescription', $POLine->LineNo, '" size="30" value="', stripslashes($POLine->ItemDescription), '" /></td>
					<td class="number">', locale_number_format($POLine->Quantity, $POLine->DecimalPlaces), '</td>
					<td>', $POLine->Units, '</td>
					<td class="number">', $DisplayPrice, '</td>
					<td><input type="text" class="number" required="required" maxlength="8" name="ConversionFactor', $POLine->LineNo, '" size="8" value="', locale_number_format($POLine->ConversionFactor, 'Variable'), '" /></td>
					<td><input type="text" class="number" required="required" maxlength="10" name="SuppQty', $POLine->LineNo, '" size="10" value="', locale_number_format(round($POLine->Quantity / $POLine->ConversionFactor, $POLine->DecimalPlaces), $POLine->DecimalPlaces), '" /></td>
					<td>', $POLine->SuppliersUnit, '</td>';
			if (in_array(1002, $_SESSION['AllowedPageSecurityTokens'])) {
				echo '<td><input type="text" class="number" name="SuppPrice', $POLine->LineNo, '" size="10" value="', $SuppPrice, '" /></td>';
			} else {
				echo '<td><input type="hidden" class="number" name="SuppPrice', $POLine->LineNo, '" size="10" value="', $SuppPrice, '" /></td>';
				echo '<td>', $SuppPrice, '</td>';
			}
			echo '<td class="number">', $DisplayLineTotal, '</td>
				<td><input type="text" class="date" required="required" maxlength="10" name="ReqDelDate', $POLine->LineNo, '" size="10" value="', $POLine->ReqDelDate, '" /></td>';
			if ($POLine->QtyReceived != 0 and $POLine->Completed != 1) {
				echo '<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', urlencode($Identifier), '&amp;Complete=', urlencode($POLine->LineNo), '">', _('Complete'), '</a></td>';
			} //$POLine->QtyReceived != 0 and $POLine->Completed != 1
			elseif ($POLine->QtyReceived == 0) {
				echo '<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', urlencode($Identifier), '&amp;Delete=', urlencode($POLine->LineNo), '">', _('Delete'), '</a></td>';
			} //$POLine->QtyReceived == 0
			echo '</tr>';
			$_SESSION['PO' . $Identifier]->Total+= $LineTotal;
		} //$POLine->Deleted == False
		
	} //$_SESSION['PO' . $Identifier]->LineItems as $POLine
	echo '</tbody>';
	$DisplayTotal = locale_number_format($_SESSION['PO' . $Identifier]->Total, $_SESSION['PO' . $Identifier]->CurrDecimalPlaces);
	echo '<tr class="total_row">
			<td colspan="9" class="number">', _('Total excluding tax'), '</td>
			<td class="number"><b>', $DisplayTotal, '</b></td>
			<td></td>
			<td></td>
		</tr>
	</table>';

	echo '<div class="centre">
			<input type="submit" name="UpdateLines" value="', _('Update Order Lines'), '" />
			&nbsp;<input type="submit" name="RefreshPrices" value="', _('Refresh Prices'), '" />
			&nbsp;<input type="submit" name="Commit" value="', _('Process Order'), '" />
		</div>';

} //count($_SESSION['PO' . $Identifier]->LineItems) > 0 and !isset($_GET['Edit'])
/*Only display the order line items if there are any !! */

if (isset($_POST['NonStockOrder'])) {
	echo '<fieldset>
			<legend>', _('Order a non stock item'), '</legend>';

	echo '<field>
			<label for="ItemDescription">', _('Item Description'), '</label>
			<input type="text" name="ItemDescription" size="40" />
		</field>';

	$SQL = "SELECT accountcode,
				  accountname
				FROM chartmaster
				WHERE chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
				ORDER BY accountcode ASC";
	$Result = DB_query($SQL);
	echo '<field>
			<label for="GLCode">', _('General Ledger Code'), '</label>
			<select name="GLCode">';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="', $MyRow['accountcode'], '">', $MyRow['accountcode'], ' - ', $MyRow['accountname'], '</option>';
	} //$MyRow = DB_fetch_array($Result)
	echo '</select>
		</field>';

	$SQL = "SELECT assetid,
					description,
					datepurchased
				FROM fixedassets
				ORDER BY assetid DESC";
	$AssetsResult = DB_query($SQL);
	echo '<field>
			<label for="AssetID">', _('OR Asset ID'), '</label>
			<select required="required" name="AssetID">
				<option selected="selected" value="Not an Asset">', _('Not an Asset'), '</option>';
	while ($AssetRow = DB_fetch_array($AssetsResult)) {
		if ($AssetRow['datepurchased'] == '0000-00-00') {
			$DatePurchased = _('Not yet purchased');
		} //$AssetRow['datepurchased'] == '0000-00-00'
		else {
			$DatePurchased = ConvertSQLDate($AssetRow['datepurchased']);
		}
		echo '<option value="', $AssetRow['assetid'], '">', $AssetRow['assetid'], ' - ', $DatePurchased, ' - ', $AssetRow['description'], '</option>';
	} //$AssetRow = DB_fetch_array($AssetsResult)
	echo '</select>
			&nbsp;<a href="FixedAssetItems.php" target=_blank>', _('New Fixed Asset'), '</a>
		</field>';

	echo '<field>
			<label for="Qty">', _('Quantity to purchase'), '</label>
			<input type="text" class="number" name="Qty" required="required" maxlength="10" size="10" value="1" />
		</field>';

	echo '<field>
			<label for="Price">', _('Price per item'), '</label>
			<input type="text" required="required" maxlength="10" class="number" name="Price" size="10" />
		</field>';

	echo '<field>
			<label for="SuppliersUnit">', _('Unit'), '</label>
			<input type="text" required="required" maxlength="10" name="SuppliersUnit" size="10" value="', _('each'), '" />
		</field>';

	echo '<field>
			<label for="ReqDelDate">', _('Delivery Date'), '</label>
			<input type="text" class="date" required="required" maxlength="10" name="ReqDelDate" size="11" value="', $_SESSION['PO' . $Identifier]->DeliveryDate, '" />
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="EnterLine" value="', _('Enter Item'), '" />
		</div>';
} //isset($_POST['NonStockOrder'])
/* Now show the stock item selection search stuff below */
if (isset($_POST['Search'])) {
	/*ie seach for stock items */

	if ($_POST['Keywords'] and $_POST['StockCode']) {
		prnMsg(_('Stock description keywords have been used in preference to the Stock code extract entered'), 'info');
	} //$_POST['Keywords'] and $_POST['StockCode']
	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		if ($_POST['StockCat'] == 'All') {
			if (isset($_POST['SupplierItemsOnly']) and $_POST['SupplierItemsOnly'] == 'on') {
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.units
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						INNER JOIN purchdata
						ON stockmaster.stockid=purchdata.stockid
						WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
						AND stockmaster.mbflag<>'A'
						AND stockmaster.mbflag<>'K'
						AND stockmaster.mbflag<>'G'
						AND stockmaster.discontinued<>1
						AND purchdata.supplierno='" . $_SESSION['PO' . $Identifier]->SupplierID . "'
						AND stockmaster.description " . LIKE . " '" . $SearchString . "'
						GROUP BY stockmaster.stockid
						ORDER BY stockmaster.stockid
						LIMIT " . $_SESSION['DisplayRecordsMax'];
			} //$_POST['SupplierItemsOnly'] == 'on'
			else { // not just supplier purchdata items
				$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
					AND stockmaster.mbflag<>'A'
					AND stockmaster.mbflag<>'K'
					AND stockmaster.mbflag<>'G'
					AND stockmaster.discontinued<>1
					AND stockmaster.description " . LIKE . " '" . $SearchString . "'
					ORDER BY stockmaster.stockid
					LIMIT " . $_SESSION['DisplayRecordsMax'];
			}
		} //$_POST['StockCat'] == 'All'
		else { //for a specific stock category
			if (isset($_POST['SupplierItemsOnly']) and $_POST['SupplierItemsOnly'] == 'on') {
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.units
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						INNER JOIN purchdata
						ON stockmaster.stockid=purchdata.stockid
						WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
						AND stockmaster.mbflag<>'A'
						AND stockmaster.mbflag<>'K'
						AND stockmaster.mbflag<>'G'
						AND purchdata.supplierno='" . $_SESSION['PO' . $Identifier]->SupplierID . "'
						AND stockmaster.discontinued<>1
						AND stockmaster.description " . LIKE . " '" . $SearchString . "'
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
						GROUP BY stockmaster.stockid
						ORDER BY stockmaster.stockid
						LIMIT " . $_SESSION['DisplayRecordsMax'];
			} //$_POST['SupplierItemsOnly'] == 'on'
			else {
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.units
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
						AND stockmaster.mbflag<>'A'
						AND stockmaster.mbflag<>'K'
						AND stockmaster.mbflag<>'G'
						AND stockmaster.discontinued<>1
						AND stockmaster.description " . LIKE . " '" . $SearchString . "'
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
						ORDER BY stockmaster.stockid
						LIMIT " . $_SESSION['DisplayRecordsMax'];
			}
		}

	} //$_POST['Keywords']
	elseif ($_POST['StockCode']) {
		$_POST['StockCode'] = '%' . $_POST['StockCode'] . '%';

		if ($_POST['StockCat'] == 'All') {
			if (isset($_POST['SupplierItemsOnly']) and $_POST['SupplierItemsOnly'] == 'on') {
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.units
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						INNER JOIN purchdata
						ON stockmaster.stockid=purchdata.stockid
						WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
						AND stockmaster.mbflag<>'A'
						AND stockmaster.mbflag<>'K'
						AND stockmaster.mbflag<>'G'
						AND purchdata.supplierno='" . $_SESSION['PO' . $Identifier]->SupplierID . "'
						AND stockmaster.discontinued<>1
						AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'
						GROUP BY stockmaster.stockid
						ORDER BY stockmaster.stockid
						LIMIT " . $_SESSION['DisplayRecordsMax'];
			} //$_POST['SupplierItemsOnly'] == 'on'
			else {
				$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
					AND stockmaster.mbflag<>'A'
					AND stockmaster.mbflag<>'K'
					AND stockmaster.mbflag<>'G'
					AND stockmaster.discontinued<>1
					AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'
					ORDER BY stockmaster.stockid
					LIMIT " . $_SESSION['DisplayRecordsMax'];
			}
		} //$_POST['StockCat'] == 'All'
		else { //for a specific stock category and LIKE stock code
			if (isset($_POST['SupplierItemsOnly']) and $_POST['SupplierItemsOnly'] == 'on') {
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.units
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						INNER JOIN purchdata
						ON stockmaster.stockid=purchdata.stockid
						WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
						AND stockmaster.mbflag<>'A'
						AND stockmaster.mbflag<>'K'
						AND stockmaster.mbflag<>'G'
						AND purchdata.supplierno='" . $_SESSION['PO' . $Identifier]->SupplierID . "'
						AND stockmaster.discontinued<>1
						AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
						GROUP BY stockmaster.stockid
						ORDER BY stockmaster.stockid
						LIMIT " . $_SESSION['DisplayRecordsMax'];
			} //$_POST['SupplierItemsOnly'] == 'on'
			else {
				$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
					AND stockmaster.mbflag<>'A'
					AND stockmaster.mbflag<>'K'
					AND stockmaster.mbflag<>'G'
					AND stockmaster.discontinued<>1
					AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid
					LIMIT " . $_SESSION['DisplayRecordsMax'];
			}
		}

	} //$_POST['StockCode']
	else {
		if ($_POST['StockCat'] == 'All') {
			if (isset($_POST['SupplierItemsOnly']) and isset($_POST['SupplierItemsOnly'])) {
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.units
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						INNER JOIN purchdata
						ON stockmaster.stockid=purchdata.stockid
						WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
						AND stockmaster.mbflag<>'A'
						AND stockmaster.mbflag<>'K'
						AND stockmaster.mbflag<>'G'
						AND purchdata.supplierno='" . $_SESSION['PO' . $Identifier]->SupplierID . "'
						AND stockmaster.discontinued<>1
						GROUP BY stockmaster.stockid
						ORDER BY stockmaster.stockid
						LIMIT " . $_SESSION['DisplayRecordsMax'];
			} //isset($_POST['SupplierItemsOnly'])
			else {
				$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
					AND stockmaster.mbflag<>'A'
					AND stockmaster.mbflag<>'K'
					AND stockmaster.mbflag<>'G'
					AND stockmaster.discontinued<>1
					ORDER BY stockmaster.stockid
					LIMIT " . $_SESSION['DisplayRecordsMax'];
			}
		} //$_POST['StockCat'] == 'All'
		else { // for a specific stock category
			if (isset($_POST['SupplierItemsOnly']) and $_POST['SupplierItemsOnly'] == 'on') {
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.units
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						INNER JOIN purchdata
						ON stockmaster.stockid=purchdata.stockid
						WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
						AND stockmaster.mbflag<>'A'
						AND stockmaster.mbflag<>'K'
						AND stockmaster.mbflag<>'G'
						AND purchdata.supplierno='" . $_SESSION['PO' . $Identifier]->SupplierID . "'
						AND stockmaster.discontinued<>1
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
						GROUP BY stockmaster.stockid
						ORDER BY stockmaster.stockid
						LIMIT " . $_SESSION['DisplayRecordsMax'];
			} else {
				$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
					AND stockmaster.mbflag<>'A'
					AND stockmaster.mbflag<>'K'
					AND stockmaster.mbflag<>'G'
					AND stockmaster.discontinued<>1
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid
					LIMIT " . $_SESSION['DisplayRecordsMax'];
			}
		}
	}

	$ErrMsg = _('There is a problem selecting the part records to display because');
	$DbgMsg = _('The SQL statement that failed was');
	$SearchResult = DB_query($SQL, $ErrMsg, $DbgMsg);

	if (DB_num_rows($SearchResult) == 0 and $Debug == 1) {
		prnMsg(_('There are no products to display matching the criteria provided'), 'warn');
	} //DB_num_rows($SearchResult) == 0 and $Debug == 1
	if (DB_num_rows($SearchResult) == 1) {
		$MyRow = DB_fetch_array($SearchResult);
		$_GET['NewItem'] = $MyRow['stockid'];
		DB_data_seek($SearchResult, 0);
	} //DB_num_rows($SearchResult) == 1
	
} //end of if search
if (!isset($_GET['Edit'])) {
	$SQL = "SELECT categoryid,
				categorydescription
			FROM stockcategory
			WHERE stocktype<>'D'
			ORDER BY categorydescription";
	$ErrMsg = _('The supplier category details could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the category details but failed was');
	$Result1 = DB_query($SQL, $ErrMsg, $DbgMsg);

	echo '<fieldset>
			<legend>', _('Search For Stock Items'), ':</legend>';

	echo '<field>
			<label for="StockCat">', _('Item Category'), ':</label>
			<select name="StockCat">
			<option selected="selected" value="All">', _('All'), '</option>';
	while ($MyRow1 = DB_fetch_array($Result1)) {
		if (isset($_POST['StockCat']) and $_POST['StockCat'] == $MyRow1['categoryid']) {
			echo '<option selected="selected" value="', $MyRow1['categoryid'], '">', $MyRow1['categorydescription'], '</option>';
		} //isset($_POST['StockCat']) and $_POST['StockCat'] == $MyRow1['categoryid']
		else {
			echo '<option value="', $MyRow1['categoryid'], '">', $MyRow1['categorydescription'], '</option>';
		}
	} //$MyRow1 = DB_fetch_array($Result1)
	echo '</select>
		</field>';
	unset($_POST['Keywords']);
	unset($_POST['StockCode']);

	if (!isset($_POST['Keywords'])) {
		$_POST['Keywords'] = '';
	} //!isset($_POST['Keywords'])
	if (!isset($_POST['StockCode'])) {
		$_POST['StockCode'] = '';
	} //!isset($_POST['StockCode'])
	echo '<field>
			<label for="Keywords">', _('Enter text extracts in the description'), ':</label>
			<input type="text" name="Keywords" size="20" maxlength="25" value="', $_POST['Keywords'], '" />
		</field>';

	echo '<h1>', _('OR'), '</h1>';

	echo '<field>
			<label for="StockCode">', _('Enter extract of the Stock Code'), ':</label>
			<input type="text" name="StockCode" size="15" maxlength="18" value="', $_POST['StockCode'], '" />
		</field>';

	echo '<h1>', _('OR'), '</h1>';

	echo '<field>
			<td><a class="FontSize" target="_blank" href="', $RootPath, '/Stocks.php">', _('Insert New Item'), '</a></td>
		</field>';

	echo '<h1>', _('OR'), '</h1>';

	echo '<field>
			<label for="CSVFile">', _('Upload items from csv file'), '</label>
			<input type="file" name="CSVFile" />
			<input type="submit" name="UploadFile" value="', _('Upload File'), '" />
		</field>';

	echo '<field>
			<label for="SupplierItemsOnly">', _('Only items defined as from this Supplier'), '</label>';
	if (isset($_POST['SupplierItemsOnly']) and $_POST['SupplierItemsOnly'] == 'on') {
		echo '<input type="checkbox" name="SupplierItemsOnly" checked="checked"  />';
	} else {
		echo '<input type="checkbox" name="SupplierItemsOnly"  />';
	} //isset($_POST['SupplierItemsOnly']) and $_POST['SupplierItemsOnly'] == 'on'
	echo '</field>
		</fieldset>

		<div class="centre">
			<input type="submit" name="Search" value="', _('Search Now'), '" />
			<input type="submit" name="NonStockOrder" value="', _('Order a non stock item'), '" />
		</div>';

	$PartsDisplayed = 0;
} //!isset($_GET['Edit'])
if (isset($SearchResult)) {
	echo '<div class="centre">
			<input type="submit" name="NewItem" value="', _('Order some'), '" />
		</div>';
	echo '<table cellpadding="1">
			<thead>
				<tr>
					<th class="SortedColumn">', _('Code'), '</th>
					<th class="SortedColumn">' . _('Description'), '</th>
					<th>', _('Our Units'), '</th>
					<th>', _('Conversion'), '<br />', _('Factor'), '</th>
					<th>', _('Supplier/Order'), '<br />', _('Units'), '</th>
					<th colspan="2"><a href="#end">', _('Go to end of list'), '</a></th>
				</tr>
			</thead>';

	$j = 0;
	echo '<tbody>';
	while ($MyRow = DB_fetch_array($SearchResult)) {

		$SupportedImgExt = array('png', 'jpg', 'jpeg');
		$ImageFileArray = glob($_SESSION['part_pics_dir'] . '/' . $MyRow['stockid'] . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE);
		$ImageFile = reset($ImageFileArray);
		if (extension_loaded('gd') and function_exists('gd_info') and file_exists($ImageFile)) {
			$ImageSource = '<img src="GetStockImage.php?automake=1&amp;textcolor=FFFFFF&amp;bgcolor=CCCCCC&StockID=' . urlencode($MyRow['stockid']) . '&text=&width=64&height=64" alt="" />';
		} else if (file_exists($ImageFile)) {
			$ImageSource = '<img src="' . $ImageFile . '" height="100" width="100" />';
		} else {
			$ImageSource = _('No Image');
		}

		/*Get conversion factor and supplier units if any */
		$SQL = "SELECT purchdata.conversionfactor,
						purchdata.suppliersuom
					FROM purchdata
					WHERE purchdata.supplierno='" . $_SESSION['PO' . $Identifier]->SupplierID . "'
					AND purchdata.stockid='" . $MyRow['stockid'] . "'";
		$ErrMsg = _('Could not retrieve the purchasing data for the item');
		$PurchDataResult = DB_query($SQL, $ErrMsg);

		if (DB_num_rows($PurchDataResult) > 0) {
			$PurchDataRow = DB_fetch_array($PurchDataResult);
			$OrderUnits = $PurchDataRow['suppliersuom'];
			$ConversionFactor = locale_number_format($PurchDataRow['conversionfactor'], 'Variable');
		} else {
			$OrderUnits = $MyRow['units'];
			$ConversionFactor = 1;
		}
		echo '<tr class="striped_row">
				<td>', $MyRow['stockid'], '</td>
				<td>', $MyRow['description'], '</td>
				<td>', $MyRow['units'], '</td>
				<td class="number">', $ConversionFactor, '</td>
				<td>', $OrderUnits, '</td>
				<td>', $ImageSource, '</td>
				<td><input class="number" type="text" size="6" required="required" maxlength="11" value="0" name="NewQty', $j, '" /></td>
			</tr>';
		echo '<input type="hidden" name="StockID', $j, '", value="', $MyRow['stockid'], '" />';
		++$j;
		++$PartsDisplayed;
		//end of page full new headings if
		
	} //$MyRow = DB_fetch_array($SearchResult)
	//end of while loop
	echo '</tbody>';
	echo '</table>';
	echo '<input type="hidden" name="PO_ItemsResubmitFormValue" value="', $_SESSION['PO_ItemsResubmitForm' . $Identifier], '" />';
	echo '<a name="end"></a>';
	echo '<div class="centre">
			<input type="submit" name="NewItem" value="', _('Order some'), '" />
		</div>';
} //end if SearchResults to show
echo '</form>';
include ('includes/footer.php');
?>