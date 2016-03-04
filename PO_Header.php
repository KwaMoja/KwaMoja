<?php

include('includes/DefinePOClass.php');
include('includes/session.inc');


if (isset($_GET['ModifyOrderNumber'])) {
	$Title = _('Modify Purchase Order') . ' ' . $_GET['ModifyOrderNumber'];
} //isset($_GET['ModifyOrderNumber'])
else {
	$Title = _('Purchase Order Entry');
}

if (isset($_GET['SupplierID'])) {
	$_POST['Select'] = $_GET['SupplierID'];
} //isset($_GET['SupplierID'])

/* Manual links before header.inc */
$ViewTopic = 'PurchaseOrdering';
$BookMark = 'PurchaseOrdering';

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

/*If the page is called is called without an identifier being set then
 * it must be either a new order, or the start of a modification of an
 * order, and so we must create a new identifier.
 *
 * The identifier only needs to be unique for this php session, so a
 * unix timestamp will be sufficient.
 */

if (empty($_GET['identifier'])) {
	$Identifier = date('U');
} //empty($_GET['identifier'])
else {
	$Identifier = $_GET['identifier'];
}

/*Page is called with NewOrder=Yes when a new order is to be entered
 * the session variable that holds all the PO data $_SESSION['PO'][$Identifier]
 * is unset to allow all new details to be created */

if (isset($_GET['NewOrder']) and isset($_SESSION['PO' . $Identifier])) {
	unset($_SESSION['PO' . $Identifier]);
	$_SESSION['ExistingOrder'] = 0;
} //isset($_GET['NewOrder']) and isset($_SESSION['PO' . $Identifier])

if (isset($_POST['Select']) and empty($_POST['SupplierContact'])) {
	$SQL = "SELECT contact
				FROM suppliercontacts
				WHERE supplierid='" . $_POST['Select'] . "'";

	$SuppCoResult = DB_query($SQL);
	if (DB_num_rows($SuppCoResult) > 0) {
		$MyRow = DB_fetch_row($SuppCoResult);
		$_POST['SupplierContact'] = $MyRow[0];
	} //DB_num_rows($SuppCoResult) > 0
	else {
		$_POST['SupplierContact'] = '';
	}
} //isset($_POST['Select']) and empty($_POST['SupplierContact'])

if ((isset($_POST['UpdateStatus']) and $_POST['UpdateStatus'] != '')) {
	if ($_SESSION['ExistingOrder'] == 0) {
		prnMsg(_('This is a new order. It must be created before you can change the status'), 'warn');
		$OKToUpdateStatus = 0;
	} //$_SESSION['ExistingOrder'] == 0
	elseif ($_SESSION['PO' . $Identifier]->Status != $_POST['Status']) { //the old status  != new status
		$OKToUpdateStatus = 1;
		$AuthSQL = "SELECT authlevel
					FROM purchorderauth
					WHERE userid='" . $_SESSION['UserID'] . "'
					AND currabrev='" . $_SESSION['PO' . $Identifier]->CurrCode . "'";

		$AuthResult = DB_query($AuthSQL);
		$MyRow = DB_fetch_array($AuthResult);
		$AuthorityLevel = $MyRow['authlevel'];
		$OrderTotal = $_SESSION['PO' . $Identifier]->Order_Value();

		if ($_POST['StatusComments'] != '') {
			$_POST['StatusComments'] = ' - ' . $_POST['StatusComments'];
		} //$_POST['StatusComments'] != ''
		if (IsEmailAddress($_SESSION['UserEmail'])) {
			$UserChangedStatus = ' <a href="mailto:' . $_SESSION['UserEmail'] . '">' . $_SESSION['UsersRealName'] . '</a>';
		} //IsEmailAddress($_SESSION['UserEmail'])
		else {
			$UserChangedStatus = ' ' . $_SESSION['UsersRealName'] . ' ';
		}

		if ($_POST['Status'] == 'Authorised') {
			if ($AuthorityLevel > $OrderTotal) {
				$_SESSION['PO' . $Identifier]->StatusComments = date($_SESSION['DefaultDateFormat']) . ' - ' . _('Authorised by') . $UserChangedStatus . $_POST['StatusComments'] . '<br />' . html_entity_decode($_POST['StatusCommentsComplete'], ENT_QUOTES, 'UTF-8');
				$_SESSION['PO' . $Identifier]->AllowPrintPO = 1;
			} //$AuthorityLevel > $OrderTotal
			else {
				$OKToUpdateStatus = 0;
				prnMsg(_('You do not have permission to authorise this purchase order') . '.<br />' . _('This order is for') . ' ' . $_SESSION['PO' . $Identifier]->CurrCode . ' ' . $OrderTotal . '. ' . _('You can only authorise up to') . ' ' . $_SESSION['PO' . $Identifier]->CurrCode . ' ' . $AuthorityLevel . '.<br />' . _('If you think this is a mistake please contact the systems administrator'), 'warn');
			}
		} //$_POST['Status'] == 'Authorised'

		if ($_POST['Status'] == 'Rejected' or $_POST['Status'] == 'Cancelled') {
			if (!isset($_SESSION['ExistingOrder']) or $_SESSION['ExistingOrder'] != 0) {
				/* need to check that not already dispatched or invoiced by the supplier */
				if ($_SESSION['PO' . $Identifier]->Any_Already_Received() == 1) {
					$OKToUpdateStatus = 0; //not ok to update the status
					prnMsg(_('This order cannot be cancelled or rejected because some of it has already been received') . '. ' . _('The line item quantities may be modified to quantities more than already received') . '. ' . _('Prices cannot be altered for lines that have already been received') . ' ' . _('and quantities cannot be reduced below the quantity already received'), 'warn');
				} //$_SESSION['PO' . $Identifier]->Any_Already_Received() == 1
				$ShipmentExists = $_SESSION['PO' . $Identifier]->Any_Lines_On_A_Shipment();
				if ($ShipmentExists != false) {
					$OKToUpdateStatus = 0; //not ok to update the status
					prnMsg(_('This order cannot be cancelled or rejected because there is at least one line that is allocated to a shipment') . '. ' . _('See shipment number') . ' ' . $ShipmentExists, 'warn');
				} //$ShipmentExists != false
			} //!isset($_SESSION['ExistingOrder']) or $_SESSION['ExistingOrder'] != 0
			if ($OKToUpdateStatus == 1) { // none of the order has been received
				if ($AuthorityLevel > $OrderTotal) {
					$_SESSION['PO' . $Identifier]->StatusComments = date($_SESSION['DefaultDateFormat']) . ' - ' . $_POST['Status'] . ' ' . _('by') . $UserChangedStatus . $_POST['StatusComments'] . '<br />' . html_entity_decode($_POST['StatusCommentsComplete'], ENT_QUOTES, 'UTF-8');
				} //$AuthorityLevel > $OrderTotal
				else {
					$OKToUpdateStatus = 0;
					prnMsg(_('You do not have permission to reject this purchase order') . '.<br />' . _('This order is for') . ' ' . $_SESSION['PO' . $Identifier]->CurrCode . ' ' . $OrderTotal . '. ' . _('Your authorisation limit is set at') . ' ' . $_SESSION['PO' . $Identifier]->CurrCode . ' ' . $AuthorityLevel . '.<br />' . _('If you think this is a mistake please contact the systems administrator'), 'warn');
				}
			} //$OKToUpdateStatus == 1
		} //$_POST['Status'] == 'Rejected' or $_POST['Status'] == 'Cancelled'

		if ($_POST['Status'] == 'Pending') {
			if ($_SESSION['PO' . $Identifier]->Any_Already_Received() == 1) {
				$OKToUpdateStatus = 0; //not OK to update status
				prnMsg(_('This order could not have the status changed back to pending because some of it has already been received. Quantities received will need to be returned to change the order back to pending.'), 'warn');
			} //$_SESSION['PO' . $Identifier]->Any_Already_Received() == 1

			if (($AuthorityLevel > $OrderTotal or $_SESSION['UserID'] == $_SESSION['PO' . $Identifier]->Initiator) and $OKToUpdateStatus == 1) {
				$_SESSION['PO' . $Identifier]->StatusComments = date($_SESSION['DefaultDateFormat']) . ' - ' . _('Order set to pending status by') . $UserChangedStatus . $_POST['StatusComments'] . '<br />' . html_entity_decode($_POST['StatusCommentsComplete'], ENT_QUOTES, 'UTF-8');

			} //($AuthorityLevel > $OrderTotal or $_SESSION['UserID'] == $_SESSION['PO' . $Identifier]->Initiator) and $OKToUpdateStatus == 1
			elseif ($AuthorityLevel < $OrderTotal and $_SESSION['UserID'] != $_SESSION['PO' . $Identifier]->Initiator) {
				$OKToUpdateStatus = 0;
				prnMsg(_('You do not have permission to change the status of this purchase order') . '.<br />' . _('This order is for') . ' ' . $_SESSION['PO' . $Identifier]->CurrCode . ' ' . $OrderTotal . '. ' . _('Your authorisation limit is set at') . ' ' . $_SESSION['PO' . $Identifier]->CurrCode . ' ' . $AuthorityLevel . '.<br />' . _('If you think this is a mistake please contact the systems administrator'), 'warn');
			} //$AuthorityLevel < $OrderTotal and $_SESSION['UserID'] != $_SESSION['PO' . $Identifier]->Initiator
		} //$_POST['Status'] == 'Pending'

		if ($OKToUpdateStatus == 1) {
			$_SESSION['PO' . $Identifier]->Status = $_POST['Status'];
			if ($_SESSION['PO' . $Identifier]->Status == 'Authorised') {
				$AllowPrint = 1;
			} //$_SESSION['PO' . $Identifier]->Status == 'Authorised'
			else {
				$AllowPrint = 0;
			}
			$SQL = "UPDATE purchorders SET status='" . $_POST['Status'] . "',
							stat_comment='" . $_SESSION['PO' . $Identifier]->StatusComments . "',
							allowprint='" . $AllowPrint . "'
					WHERE purchorders.orderno ='" . $_SESSION['ExistingOrder'] . "'";

			$ErrMsg = _('The order status could not be updated because');
			$UpdateResult = DB_query($SQL, $ErrMsg);

			if ($_POST['Status'] == 'Completed' OR $_POST['Status'] == 'Cancelled' OR $_POST['Status'] == 'Rejected') {
				$SQL = "UPDATE purchorderdetails SET completed=1 WHERE orderno='" . $_SESSION['ExistingOrder'] . "'";
				$UpdateResult = DB_query($SQL, $ErrMsg);
			} else {//To ensure that the purchorderdetails status is correct when it is recovered from a cancelled orders
				$SQL = "UPDATE purchorderdetails SET completed=0 WHERE orderno='" . $_SESSION['ExistingOrder'] . "'";
				$UpdateResult = DB_query($SQL, $ErrMsg);
			}
		} //$OKToUpdateStatus == 1
	} //end if there is actually a status change the class Status != the POST['Status']
} //End if user hit Update Status

if (isset($_GET['NewOrder']) and isset($_GET['StockID']) and isset($_GET['SelectedSupplier'])) {
	/*
	 * initialise a new order
	 */
	$_SESSION['ExistingOrder'] = 0;
	unset($_SESSION['PO' . $Identifier]);
	/* initialise new class object */
	$_SESSION['PO' . $Identifier] = new PurchOrder;
	/*
	 * and fill it with essential data
	 */
	$_SESSION['PO' . $Identifier]->AllowPrintPO = 1;
	/* Of course 'cos the order aint even started !!*/
	$_SESSION['PO' . $Identifier]->GLLink = $_SESSION['CompanyRecord']['gllink_stock'];
	/* set the SupplierID we got */
	$_SESSION['PO' . $Identifier]->SupplierID = $_GET['SelectedSupplier'];
	$_SESSION['PO' . $Identifier]->DeliveryDate = date($_SESSION['DefaultDateFormat']);
	$_SESSION['PO' . $Identifier]->Initiator = $_SESSION['UserID'];
	$_SESSION['RequireSupplierSelection'] = 0;
	$_POST['Select'] = $_GET['SelectedSupplier'];

	/*
	 * the item (it's item code) that should be purchased
	 */
	$Purch_Item = $_GET['StockID'];

} //End if it's a new order sent with supplier code and the item to order

if (isset($_POST['EnterLines']) or isset($_POST['AllowRePrint'])) {
	/*User hit the button to enter line items -
	 *  ensure session variables updated then meta refresh to PO_Items.php*/

	$_SESSION['PO' . $Identifier]->Location = $_POST['StkLocation'];
	$_SESSION['PO' . $Identifier]->SupplierContact = $_POST['SupplierContact'];
	$_SESSION['PO' . $Identifier]->DelAdd1 = $_POST['DelAdd1'];
	$_SESSION['PO' . $Identifier]->DelAdd2 = $_POST['DelAdd2'];
	$_SESSION['PO' . $Identifier]->DelAdd3 = $_POST['DelAdd3'];
	$_SESSION['PO' . $Identifier]->DelAdd4 = $_POST['DelAdd4'];
	$_SESSION['PO' . $Identifier]->DelAdd5 = $_POST['DelAdd5'];
	$_SESSION['PO' . $Identifier]->DelAdd6 = $_POST['DelAdd6'];
	$_SESSION['PO' . $Identifier]->SuppDelAdd1 = $_POST['SuppDelAdd1'];
	$_SESSION['PO' . $Identifier]->SuppDelAdd2 = $_POST['SuppDelAdd2'];
	$_SESSION['PO' . $Identifier]->SuppDelAdd3 = $_POST['SuppDelAdd3'];
	$_SESSION['PO' . $Identifier]->SuppDelAdd4 = $_POST['SuppDelAdd4'];
	$_SESSION['PO' . $Identifier]->SuppDelAdd5 = $_POST['SuppDelAdd5'];
	$_SESSION['PO' . $Identifier]->SuppTel = $_POST['SuppTel'];
	$_SESSION['PO' . $Identifier]->Initiator = $_POST['Initiator'];
	$_SESSION['PO' . $Identifier]->RequisitionNo = $_POST['Requisition'];
	$_SESSION['PO' . $Identifier]->Version = $_POST['Version'];
	$_SESSION['PO' . $Identifier]->DeliveryDate = $_POST['DeliveryDate'];
	$_SESSION['PO' . $Identifier]->Revised = $_POST['Revised'];
	$_SESSION['PO' . $Identifier]->ExRate = filter_number_format($_POST['ExRate']);
	$_SESSION['PO' . $Identifier]->Comments = $_POST['Comments'];
	$_SESSION['PO' . $Identifier]->DeliveryBy = $_POST['DeliveryBy'];
	if (isset($_POST['StatusComments'])) {
		$_SESSION['PO' . $Identifier]->StatusComments = $_POST['StatusComments'];
	} //isset($_POST['StatusComments'])
	$_SESSION['PO' . $Identifier]->PaymentTerms = $_POST['PaymentTerms'];
	$_SESSION['PO' . $Identifier]->Contact = $_POST['Contact'];
	$_SESSION['PO' . $Identifier]->Tel = $_POST['Tel'];
	$_SESSION['PO' . $Identifier]->Port = $_POST['Port'];

	if (isset($_POST['RePrint']) and $_POST['RePrint'] == 1) {
		$_SESSION['PO' . $Identifier]->AllowPrintPO = 1;

		$SQL = "UPDATE purchorders
				SET purchorders.allowprint='1'
				WHERE purchorders.orderno='" . $_SESSION['PO' . $Identifier]->OrderNo . "'";

		$ErrMsg = _('An error occurred updating the purchase order to allow reprints') . '. ' . _('The error says');
		$UpdateResult = DB_query($SQL, $ErrMsg);
	} //end if change to allow reprint
	else {
		$_POST['RePrint'] = 0;
	}
	if (!isset($_POST['AllowRePrint'])) { // user only hit update not "Enter Lines"
		echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/PO_Items.php?identifier=' . $Identifier . '">';
		echo '<p>';
		prnMsg(_('You should automatically be forwarded to the entry of the purchase order line items page') . '. ' . _('If this does not happen') . ' (' . _('if the browser does not support META Refresh') . ') ' . '<a href="' . $RootPath . '/PO_Items.php?identifier=' . urlencode($Identifier) . '">' . _('click here') . '</a> ' . _('to continue'), 'info');
		include('includes/footer.inc');
		exit;
	} // end if reprint not allowed
} //isset($_POST['EnterLines']) or isset($_POST['AllowRePrint'])

/* end of if isset _POST'EnterLines' */

echo '<div class="toplink"><a href="' . $RootPath . '/PO_SelectOSPurchOrder.php?identifier=' . urlencode($Identifier) . '">' . _('Back to Purchase Orders') . '</a></div>';

/*The page can be called with ModifyOrderNumber=x where x is a purchase
 * order number. The page then looks up the details of order x and allows
 * these details to be modified */

if (isset($_GET['ModifyOrderNumber'])) {
	include('includes/PO_ReadInOrder.inc');
} //isset($_GET['ModifyOrderNumber'])


if (!isset($_SESSION['PO' . $Identifier])) {
	/* It must be a new order being created
	 * $_SESSION['PO'.$Identifier] would be set up from the order modification
	 * code above if a modification to an existing order. Also
	 * $ExistingOrder would be set to 1. The delivery check screen
	 * is where the details of the order are either updated or
	 * inserted depending on the value of ExistingOrder
	 * */

	$_SESSION['ExistingOrder'] = 0;
	$_SESSION['PO' . $Identifier] = new PurchOrder;
	$_SESSION['PO' . $Identifier]->AllowPrintPO = 1;
	/*Of course cos the order aint even started !!*/
	$_SESSION['PO' . $Identifier]->GLLink = $_SESSION['CompanyRecord']['gllink_stock'];

	if ($_SESSION['PO' . $Identifier]->SupplierID == '' or !isset($_SESSION['PO' . $Identifier]->SupplierID)) {
		/* a session variable will have to maintain if a supplier
		 * has been selected for the order or not the session
		 * variable supplierID holds the supplier code already
		 * as determined from user id /password entry  */
		$_SESSION['RequireSupplierSelection'] = 1;
	} //$_SESSION['PO' . $Identifier]->SupplierID == '' or !isset($_SESSION['PO' . $Identifier]->SupplierID)
	else {
		$_SESSION['RequireSupplierSelection'] = 0;
	}

} //end if initiating a new PO

if (isset($_POST['ChangeSupplier'])) {
	if ($_SESSION['PO' . $Identifier]->Status == 'Pending' and $_SESSION['UserID'] == $_SESSION['PO' . $Identifier]->Initiator) {
		if ($_SESSION['PO' . $Identifier]->Any_Already_Received() == 0) {
			$_SESSION['RequireSupplierSelection'] = 1;
			$_SESSION['PO' . $Identifier]->Status = 'Pending';
			$_SESSION['PO' . $Identifier]->StatusComments == date($_SESSION['DefaultDateFormat']) . ' - ' . _('Supplier changed by') . ' <a href="mailto:' . $_SESSION['UserEmail'] . '">' . $_SESSION['UserID'] . '</a> - ' . $_POST['StatusComments'] . '<br />' . html_entity_decode($_POST['StatusCommentsComplete'], ENT_QUOTES, 'UTF-8');

		} //$_SESSION['PO' . $Identifier]->Any_Already_Received() == 0
		else {
			echo '<br /><br />';
			prnMsg(_('Cannot modify the supplier of the order once some of the order has been received'), 'warn');
		}
	} //$_SESSION['PO' . $Identifier]->Status == 'Pending' and $_SESSION['UserID'] == $_SESSION['PO' . $Identifier]->Initiator
} //user hit ChangeSupplier

if (isset($_POST['SearchSuppliers'])) {
	if (mb_strlen($_POST['Keywords']) > 0 and mb_strlen($_SESSION['PO' . $Identifier]->SupplierID) > 0) {
		prnMsg(_('Supplier name keywords have been used in preference to the supplier code extract entered'), 'warn');
	} //mb_strlen($_POST['Keywords']) > 0 and mb_strlen($_SESSION['PO' . $Identifier]->SupplierID) > 0
	if (mb_strlen($_POST['Keywords']) > 0) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		$SQL = "SELECT suppliers.supplierid,
						suppliers.suppname,
						suppliers.address1,
						suppliers.address2,
						suppliers.address3,
						suppliers.address4,
						suppliers.address5,
						suppliers.address6,
						suppliers.currcode
					FROM suppliers
					WHERE suppliers.suppname " . LIKE . " '" . $SearchString . "'
					ORDER BY suppliers.suppname";

	} //mb_strlen($_POST['Keywords']) > 0
	elseif (mb_strlen($_POST['SuppCode']) > 0) {
		$SQL = "SELECT suppliers.supplierid,
						suppliers.suppname,
						suppliers.address1,
						suppliers.address2,
						suppliers.address3,
						suppliers.address4,
						suppliers.address5,
						suppliers.address6,
						suppliers.currcode
					FROM suppliers
					WHERE suppliers.supplierid " . LIKE . " '%" . $_POST['SuppCode'] . "%'
					ORDER BY suppliers.supplierid";
	} //mb_strlen($_POST['SuppCode']) > 0
	else {
		$SQL = "SELECT suppliers.supplierid,
						suppliers.suppname,
						suppliers.address1,
						suppliers.address2,
						suppliers.address3,
						suppliers.address4,
						suppliers.address5,
						suppliers.address6,
						suppliers.currcode
					FROM suppliers
					ORDER BY suppliers.supplierid";
	}

	$ErrMsg = _('The searched supplier records requested cannot be retrieved because');
	$Result_SuppSelect = DB_query($SQL, $ErrMsg);
	$SuppliersReturned = DB_num_rows($Result_SuppSelect);
	if (DB_num_rows($Result_SuppSelect) == 1) {
		$MyRow = DB_fetch_array($Result_SuppSelect);
		$_POST['Select'] = $MyRow['supplierid'];
	} //DB_num_rows($Result_SuppSelect) == 1
	elseif (DB_num_rows($Result_SuppSelect) == 0) {
		prnMsg(_('No supplier records contain the selected text') . ' - ' . _('please alter your search criteria and try again'), 'info');
	} //DB_num_rows($Result_SuppSelect) == 0
} //isset($_POST['SearchSuppliers'])

/*end of if search for supplier codes/names */


if ((!isset($_POST['SearchSuppliers']) or $_POST['SearchSuppliers'] == '') and (isset($_SESSION['PO' . $Identifier]->SupplierID) and $_SESSION['PO' . $Identifier]->SupplierID != '')) {
	/*The session variables are set but the form variables could have been lost
	need to restore the form variables from the session */
	$_POST['SupplierID'] = $_SESSION['PO' . $Identifier]->SupplierID;
	$_POST['SupplierName'] = $_SESSION['PO' . $Identifier]->SupplierName;
	$_POST['CurrCode'] = $_SESSION['PO' . $Identifier]->CurrCode;
	$_POST['ExRate'] = $_SESSION['PO' . $Identifier]->ExRate;
	$_POST['PaymentTerms'] = $_SESSION['PO' . $Identifier]->PaymentTerms;
	$_POST['DelAdd1'] = $_SESSION['PO' . $Identifier]->DelAdd1;
	$_POST['DelAdd2'] = $_SESSION['PO' . $Identifier]->DelAdd2;
	$_POST['DelAdd3'] = $_SESSION['PO' . $Identifier]->DelAdd3;
	$_POST['DelAdd4'] = $_SESSION['PO' . $Identifier]->DelAdd4;
	$_POST['DelAdd5'] = $_SESSION['PO' . $Identifier]->DelAdd5;
	$_POST['DelAdd6'] = $_SESSION['PO' . $Identifier]->DelAdd6;
	$_POST['SuppDelAdd1'] = $_SESSION['PO' . $Identifier]->SuppDelAdd1;
	$_POST['SuppDelAdd2'] = $_SESSION['PO' . $Identifier]->SuppDelAdd2;
	$_POST['SuppDelAdd3'] = $_SESSION['PO' . $Identifier]->SuppDelAdd3;
	$_POST['SuppDelAdd4'] = $_SESSION['PO' . $Identifier]->SuppDelAdd4;
	$_POST['SuppDelAdd5'] = $_SESSION['PO' . $Identifier]->SuppDelAdd5;
	$_POST['SuppDelAdd6'] = $_SESSION['PO' . $Identifier]->SuppDelAdd6;
	$_POST['DeliveryDate'] = $_SESSION['PO' . $Identifier]->DeliveryDate;

} //(!isset($_POST['SearchSuppliers']) or $_POST['SearchSuppliers'] == '') and (isset($_SESSION['PO' . $Identifier]->SupplierID) and $_SESSION['PO' . $Identifier]->SupplierID != '')

if (isset($_POST['Select'])) {
	/* will only be true if page called from supplier selection form or item purchasing data order link
	 * or set because only one supplier record returned from a search
	 */

	$SQL = "SELECT suppliers.suppname,
					suppliers.currcode,
					currencies.rate,
					currencies.decimalplaces,
					suppliers.paymentterms,
					suppliers.address1,
					suppliers.address2,
					suppliers.address3,
					suppliers.address4,
					suppliers.address5,
					suppliers.address6,
					suppliers.telephone,
					suppliers.port,
					suppliers.defaultshipper
				FROM suppliers INNER JOIN currencies
				ON suppliers.currcode=currencies.currabrev
				WHERE supplierid='" . $_POST['Select'] . "'";

	$ErrMsg = _('The supplier record of the supplier selected') . ': ' . $_POST['Select'] . ' ' . _('cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the supplier details and failed was');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
	$MyRow = DB_fetch_array($Result);
	// added for suppliers lookup fields

	$AuthSql = "SELECT cancreate
				FROM purchorderauth
				WHERE userid='" . $_SESSION['UserID'] . "'
				AND currabrev='" . $MyRow['currcode'] . "'";

	$AuthResult = DB_query($AuthSql);

	if (($AuthRow = DB_fetch_array($AuthResult) and $AuthRow['cancreate'] == 0)) {
		$_POST['SupplierName'] = $MyRow['suppname'];
		$_POST['CurrCode'] = $MyRow['currcode'];
		$_POST['CurrDecimalPlaces'] = $MyRow['decimalplaces'];
		$_POST['ExRate'] = $MyRow['rate'];
		$_POST['PaymentTerms'] = $MyRow['paymentterms'];
		$_POST['SuppDelAdd1'] = $MyRow['address1'];
		$_POST['SuppDelAdd2'] = $MyRow['address2'];
		$_POST['SuppDelAdd3'] = $MyRow['address3'];
		$_POST['SuppDelAdd4'] = $MyRow['address4'];
		$_POST['SuppDelAdd5'] = $MyRow['address5'];
		$_POST['SuppDelAdd6'] = $MyRow['address6'];
		$_POST['SuppTel'] = $MyRow['telephone'];
		$_POST['Port'] = $MyRow['port'];
		$_POST['DeliveryBy'] = $MyRow['defaultshipper'];

		$_SESSION['PO' . $Identifier]->SupplierID = $_POST['Select'];
		$_SESSION['RequireSupplierSelection'] = 0;
		$_SESSION['PO' . $Identifier]->SupplierName = $_POST['SupplierName'];
		$_SESSION['PO' . $Identifier]->CurrCode = $_POST['CurrCode'];
		$_SESSION['PO' . $Identifier]->CurrDecimalPlaces = $_POST['CurrDecimalPlaces'];
		$_SESSION['PO' . $Identifier]->ExRate = $_POST['ExRate'];
		$_SESSION['PO' . $Identifier]->PaymentTerms = $_POST['PaymentTerms'];
		$_SESSION['PO' . $Identifier]->SuppDelAdd1 = $_POST['SuppDelAdd1'];
		$_SESSION['PO' . $Identifier]->SuppDelAdd2 = $_POST['SuppDelAdd2'];
		$_SESSION['PO' . $Identifier]->SuppDelAdd3 = $_POST['SuppDelAdd3'];
		$_SESSION['PO' . $Identifier]->SuppDelAdd4 = $_POST['SuppDelAdd4'];
		$_SESSION['PO' . $Identifier]->SuppDelAdd5 = $_POST['SuppDelAdd5'];
		$_SESSION['PO' . $Identifier]->SuppDelAdd6 = $_POST['SuppDelAdd6'];
		$_SESSION['PO' . $Identifier]->SuppTel = $_POST['SuppTel'];
		$_SESSION['PO' . $Identifier]->Port = $_POST['Port'];

	} //($AuthRow = DB_fetch_array($AuthResult) and $AuthRow['cancreate'] == 0)
	else {
		prnMsg(_('You do not have the authority to raise Purchase Orders for') . ' ' . $MyRow['suppname'] . '. ' . _('Please Consult your system administrator for more information.') . '<br />' . _('You can setup authorisations') . ' ' . '<a href="PO_AuthorisationLevels.php">' . _('here') . '</a>', 'warn');
		include('includes/footer.inc');
		exit;
	}

	// end of added for suppliers lookup fields

} //isset($_POST['Select'])

/* isset($_POST['Select'])  will only be true if page called from supplier selection form or item purchasing data order link
 * or set because only one supplier record returned from a search
 */
else {
	$_POST['Select'] = DB_escape_string($_SESSION['PO' . $Identifier]->SupplierID);
	$SQL = "SELECT suppliers.suppname,
					suppliers.currcode,
					currencies.decimalplaces,
					suppliers.paymentterms,
					suppliers.address1,
					suppliers.address2,
					suppliers.address3,
					suppliers.address4,
					suppliers.address5,
					suppliers.address6,
					suppliers.telephone,
					suppliers.defaultshipper,
					suppliers.port
			FROM suppliers
			INNER JOIN currencies
				ON suppliers.currcode=currencies.currabrev
			WHERE supplierid='" . $_POST['Select'] . "'";

	$ErrMsg = _('The supplier record of the supplier selected') . ': ' . $_POST['Select'] . ' ' . _('cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the supplier details and failed was');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

	$MyRow = DB_fetch_array($Result);

	// added for suppliers lookup fields
	if (!isset($_SESSION['PO' . $Identifier])) {
		$_POST['SupplierName'] = $MyRow['suppname'];
		$_POST['CurrCode'] = $MyRow['currcode'];
		$_POST['CurrDecimalPlaces'] = $MyRow['decimalplaces'];
		$_POST['ExRate'] = $MyRow['rate'];
		$_POST['PaymentTerms'] = $MyRow['paymentterms'];
		$_POST['SuppDelAdd1'] = $MyRow['address1'];
		$_POST['SuppDelAdd2'] = $MyRow['address2'];
		$_POST['SuppDelAdd3'] = $MyRow['address3'];
		$_POST['SuppDelAdd4'] = $MyRow['address4'];
		$_POST['SuppDelAdd5'] = $MyRow['address5'];
		$_POST['SuppDelAdd6'] = $MyRow['address6'];
		$_POST['SuppTel'] = $MyRow['telephone'];
		$_POST['Port'] = $MyRow['port'];
		$_POST['DeliveryBy'] = $MyRow['defaultshipper'];


		$_SESSION['PO' . $Identifier]->SupplierID = $_POST['Select'];
		$_SESSION['RequireSupplierSelection'] = 0;
		$_SESSION['PO' . $Identifier]->SupplierName = $_POST['SupplierName'];
		$_SESSION['PO' . $Identifier]->CurrCode = $_POST['CurrCode'];
		$_SESSION['PO' . $Identifier]->CurrDecimalPlaces = $_POST['CurrDecimalPlaces'];
		$_SESSION['PO' . $Identifier]->ExRate = filter_number_format($_POST['ExRate']);
		$_SESSION['PO' . $Identifier]->PaymentTerms = $_POST['PaymentTerms'];
		$_SESSION['PO' . $Identifier]->SuppDelAdd1 = $_POST['SuppDelAdd1'];
		$_SESSION['PO' . $Identifier]->SuppDelAdd2 = $_POST['SuppDelAdd2'];
		$_SESSION['PO' . $Identifier]->SuppDelAdd3 = $_POST['SuppDelAdd3'];
		$_SESSION['PO' . $Identifier]->SuppDelAdd4 = $_POST['SuppDelAdd4'];
		$_SESSION['PO' . $Identifier]->SuppDelAdd5 = $_POST['SuppDelAdd5'];
		$_SESSION['PO' . $Identifier]->SuppDelAdd6 = $_POST['SuppDelAdd6'];
		$_SESSION['PO' . $Identifier]->SuppTel = $_POST['SuppTel'];
		$_SESSION['PO' . $Identifier]->Port = $_POST['Port'];
		$_SESSION['PO' . $Identifier]->DeliveryBy = $_POST['DeliveryBy'];
		// end of added for suppliers lookup fields
	} //!isset($_SESSION['PO' . $Identifier])
} // NOT isset($_POST['Select']) - not called with supplier selection so update variables

// part of step 1
if ($_SESSION['RequireSupplierSelection'] == 1 or !isset($_SESSION['PO' . $Identifier]->SupplierID) or $_SESSION['PO' . $Identifier]->SupplierID == '') {
	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/supplier.png" title="' . _('Purchase Order') . '" alt="" />' . ' ' . _('Purchase Order: Select Supplier') . '</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $Identifier . '" method="post" id="choosesupplier">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SuppliersReturned)) {
		echo '<input type="hidden" name="SuppliersReturned" value="' . $SuppliersReturned . '" />';
	} //isset($SuppliersReturned)

	echo '<table cellpadding="3" class="selection">
			<tr>
				<td>' . _('Enter text in the supplier name') . ':</td>
				<td><input type="text" autofocus="autofocus" name="Keywords" size="20" maxlength="25" /></td>
				<td><h3><b>' . _('OR') . '</b></h3></td>
				<td>' . _('Enter text extract in the supplier code') . ':</td>
				<td><input type="text" name="SuppCode" size="15" maxlength="18" /></td>
			</tr>
		</table>
		<div class="centre">
			<input type="submit" name="SearchSuppliers" value="' . _('Search Now') . '" />
			<input type="submit" value="' . _('Reset') . '" />
		</div>';

	if (isset($Result_SuppSelect)) {
		echo '<table cellpadding="3" class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('Code') . '</th>
						<th class="SortedColumn">' . _('Supplier Name') . '</th>
						<th>' . _('Address') . '</th>
						<th>' . _('Currency') . '</th>
					</tr>
				</thead>';

		$k = 0;
		/*row counter to determine background colour */
		echo '<tbody>';
		while ($MyRow = DB_fetch_array($Result_SuppSelect)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} //$k == 1
			else {
				echo '<tr class="OddTableRows">';
				++$k;
			}

			echo '<td><input type="submit" style="width:100%" name="Select" value="' . $MyRow['supplierid'] . '" /></td>
				<td>' . $MyRow['suppname'] . '</td><td>';

			for ($i = 1; $i <= 6; $i++) {
				if ($MyRow['address' . $i] != '') {
					echo $MyRow['address' . $i] . '<br />';
				} //$MyRow['address' . $i] != ''
			} //$i = 1; $i <= 6; $i++
			echo '</td>
					<td>' . $MyRow['currcode'] . '</td>
				</tr>';

			//end of page full new headings if
		} //end of while loop

		echo '</tbody>';
		echo '</table>';

	} //isset($Result_SuppSelect)
	//end if results to show

	//end if RequireSupplierSelection
} //$_SESSION['RequireSupplierSelection'] == 1 or !isset($_SESSION['PO' . $Identifier]->SupplierID) or $_SESSION['PO' . $Identifier]->SupplierID == ''
else {
	/* everything below here only do if a supplier is selected */

	echo '<form id="form1" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $Identifier . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text" >
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/supplier.png" title="' . _('Purchase Order') . '" alt="" />
			' . $_SESSION['PO' . $Identifier]->SupplierName . ' - ' . _('All amounts stated in') . '
			' . $_SESSION['PO' . $Identifier]->CurrCode . '</p>';

	if ($_SESSION['ExistingOrder']) {
		echo _(' Modify Purchase Order Number') . ' ' . $_SESSION['PO' . $Identifier]->OrderNo;
	} //$_SESSION['ExistingOrder']

	if (isset($Purch_Item)) {
		/*This is set if the user hits the link from the supplier purchasing info shown on SelectProduct.php */
		prnMsg(_('Purchase Item(s) with this code') . ': ' . $Purch_Item, 'info');

		echo '<div class="centre">';
		echo '<br />
				<table class="table_index">
				<tr>
					<td class="menu_group_item">';

		/* the link */
		echo '<a href="' . $RootPath . '/PO_Items.php?NewItem=' . urlencode($Purch_Item) . '&identifier=' . urlencode($Identifier) . '">' . _('Enter Line Item to this purchase order') . '</a>';

		echo '</td>
			</tr>
			</table>
			</div>
			<br />';

		if (isset($_GET['Quantity'])) {
			$Qty = $_GET['Quantity'];
		} //isset($_GET['Quantity'])
		else {
			$Qty = 1;
		}

		$SQL = "SELECT stockmaster.controlled,
						stockmaster.serialised,
						stockmaster.description,
						stockmaster.units ,
						stockmaster.decimalplaces,
						purchdata.price,
						purchdata.suppliersuom,
						purchdata.suppliers_partno,
						purchdata.conversionfactor,
						purchdata.leadtime,
						stockcategory.stockact
				FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
				LEFT JOIN purchdata
					ON stockmaster.stockid = purchdata.stockid
				WHERE stockmaster.stockid='" . $Purch_Item . "'
				AND purchdata.supplierno ='" . $_GET['SelectedSupplier'] . "'";
		$Result = DB_query($SQL);
		$PurchItemRow = DB_fetch_array($Result);

		if (!isset($PurchItemRow['conversionfactor'])) {
			$PurchItemRow['conversionfactor'] = 1;
		} //!isset($PurchItemRow['conversionfactor'])

		if (!isset($PurchItemRow['leadtime'])) {
			$PurchItemRow['leadtime'] = 1;
		} //!isset($PurchItemRow['leadtime'])

		$_SESSION['PO' . $Identifier]->add_to_order(1, $Purch_Item, $PurchItemRow['serialised'], $PurchItemRow['controlled'], $Qty * $PurchItemRow['conversionfactor'], $PurchItemRow['description'], $PurchItemRow['price'] / $PurchItemRow['conversionfactor'], $PurchItemRow['units'], $PurchItemRow['stockact'], $_SESSION['PO' . $Identifier]->DeliveryDate, 0, 0, '', 0, 0, '', $PurchItemRow['decimalplaces'], $PurchItemRow['suppliersuom'], $PurchItemRow['conversionfactor'], $PurchItemRow['leadtime'], $PurchItemRow['suppliers_partno']);

		echo '<meta http-equiv="refresh" content="0; url=' . $RootPath . '/PO_Items.php?identifier=' . $Identifier . '">';
	} //isset($Purch_Item)

	/*Set up form for entry of order header stuff */

	if (!isset($_POST['LookupDeliveryAddress']) and (!isset($_POST['StkLocation']) or $_POST['StkLocation']) and (isset($_SESSION['PO' . $Identifier]->Location) and $_SESSION['PO' . $Identifier]->Location != '')) {
		/* The session variables are set but the form variables have
		 * been lost --
		 * need to restore the form variables from the session */
		$_POST['StkLocation'] = $_SESSION['PO' . $Identifier]->Location;
		$_POST['SupplierContact'] = $_SESSION['PO' . $Identifier]->SupplierContact;
		$_POST['DelAdd1'] = $_SESSION['PO' . $Identifier]->DelAdd1;
		$_POST['DelAdd2'] = $_SESSION['PO' . $Identifier]->DelAdd2;
		$_POST['DelAdd3'] = $_SESSION['PO' . $Identifier]->DelAdd3;
		$_POST['DelAdd4'] = $_SESSION['PO' . $Identifier]->DelAdd4;
		$_POST['DelAdd5'] = $_SESSION['PO' . $Identifier]->DelAdd5;
		$_POST['DelAdd6'] = $_SESSION['PO' . $Identifier]->DelAdd6;
		$_POST['Initiator'] = $_SESSION['PO' . $Identifier]->Initiator;
		$_POST['Requisition'] = $_SESSION['PO' . $Identifier]->RequisitionNo;
		$_POST['Version'] = $_SESSION['PO' . $Identifier]->Version;
		$_POST['DeliveryDate'] = $_SESSION['PO' . $Identifier]->DeliveryDate;
		$_POST['Revised'] = $_SESSION['PO' . $Identifier]->Revised;
		$_POST['ExRate'] = $_SESSION['PO' . $Identifier]->ExRate;
		$_POST['Comments'] = $_SESSION['PO' . $Identifier]->Comments;
		$_POST['DeliveryBy'] = $_SESSION['PO' . $Identifier]->DeliveryBy;
		$_POST['PaymentTerms'] = $_SESSION['PO' . $Identifier]->PaymentTerms;
		$SQL = "SELECT realname FROM www_users WHERE userid='" . $_POST['Initiator'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		$_POST['InitiatorName'] = $MyRow['realname'];
	} //!isset($_POST['LookupDeliveryAddress']) and (!isset($_POST['StkLocation']) or $_POST['StkLocation']) and (isset($_SESSION['PO' . $Identifier]->Location) and $_SESSION['PO' . $Identifier]->Location != '')

	echo '<br />
			<table width="80%">
				<tr>
					<th><h3>' . _('Order Initiation Details') . '</h3></th>
					<th><h3>' . _('Order Status') . '</h3></th>
				</tr>
				<tr>
					<td style="width:50%">';
	//sub table starts
	echo '<table class="selection" width="100%">';
	echo '<tr>
			<td>' . _('PO Date') . ':</td>
			<td>';
	if ($_SESSION['ExistingOrder'] != 0) {
		echo ConvertSQLDate($_SESSION['PO' . $Identifier]->Orig_OrderDate);
	} //$_SESSION['ExistingOrder'] != 0
	else {
		/* DefaultDateFormat defined in config.php */
		echo Date($_SESSION['DefaultDateFormat']);
	}
	echo '</td></tr>';

	if (isset($_GET['ModifyOrderNumber']) and $_GET['ModifyOrderNumber'] != '') {
		$_SESSION['PO' . $Identifier]->Version += 1;
		$_POST['Version'] = $_SESSION['PO' . $Identifier]->Version;
	} //isset($_GET['ModifyOrderNumber']) and $_GET['ModifyOrderNumber'] != ''
	elseif (isset($_SESSION['PO' . $Identifier]->Version) and $_SESSION['PO' . $Identifier]->Version != '') {
		$_POST['Version'] = $_SESSION['PO' . $Identifier]->Version;
	} //isset($_SESSION['PO' . $Identifier]->Version) and $_SESSION['PO' . $Identifier]->Version != ''
	else {
		$_POST['Version'] = '1';
	}

	if (!isset($_POST['DeliveryDate'])) {
		$_POST['DeliveryDate'] = date($_SESSION['DefaultDateFormat']);
	} //!isset($_POST['DeliveryDate'])

	echo '<tr>
			<td>' . _('Version') . ' #' . ':</td>
			<td><input type="hidden" name="Version" size="16" maxlength="15" value="' . $_POST['Version'] . '" />' . $_POST['Version'] . '</td>
		</tr>
		<tr>
			<td>' . _('Revised') . ':</td>
			<td><input type="hidden" name="Revised" size="11" maxlength="15" value="' . date($_SESSION['DefaultDateFormat']) . '" />' . date($_SESSION['DefaultDateFormat']) . '</td>
		</tr>
		<tr>
			<td>' . _('Delivery Date') . ':</td>
			<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="DeliveryDate" required="required" maxlength="10" size="11" value="' . $_POST['DeliveryDate'] . '" /></td>
		</tr>';

	if (!isset($_POST['Initiator'])) {
		$_POST['Initiator'] = $_SESSION['UserID'];
		$_POST['InitiatorName'] = $_SESSION['UsersRealName'];
		$_POST['Requisition'] = '';
	} //!isset($_POST['Initiator'])

	if (!isset($_POST['InitiatorName'])) {
		$_POST['InitiatorName'] = $_SESSION['UsersRealName'];
	}

	echo '<tr>
			<td>' . _('Initiated By') . ':</td>
			<td><input type="hidden" name="Initiator" size="11" maxlength="10" value="' . $_POST['Initiator'] . '" />' . $_POST['InitiatorName'] . '</td>
		</tr>
		<tr>
			<td>' . _('Requisition Ref') . ':</td>
			<td><input type="text" name="Requisition" size="16" maxlength="15" value="' . $_POST['Requisition'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Date Printed') . ':</td>';

	if (isset($_SESSION['PO' . $Identifier]->DatePurchaseOrderPrinted) and mb_strlen($_SESSION['PO' . $Identifier]->DatePurchaseOrderPrinted) > 6) {
		echo '<td>' . ConvertSQLDate($_SESSION['PO' . $Identifier]->DatePurchaseOrderPrinted) . '</td></tr>';
		$Printed = True;
	} //isset($_SESSION['PO' . $Identifier]->DatePurchaseOrderPrinted) and mb_strlen($_SESSION['PO' . $Identifier]->DatePurchaseOrderPrinted) > 6
	else {
		$Printed = False;
		echo '<td>' . _('Not yet printed') . '</td></tr>';
	}

	if (isset($_POST['AllowRePrint'])) {
		$SQL = "UPDATE purchorders SET allowprint=1 WHERE orderno='" . $_SESSION['PO' . $Identifier]->OrderNo . "'";
		$Result = DB_query($SQL);
	} //isset($_POST['AllowRePrint'])

	if ($_SESSION['PO' . $Identifier]->AllowPrintPO == 0 and empty($_POST['RePrint'])) {
		echo '<tr>
				<td>' . _('Allow Reprint') . ':</td>
				<td>
					<select required="required" name="RePrint" onchange="ReloadForm(form1.AllowRePrint)">
						<option selected="selected" value="0">' . _('No') . '</option>
						<option value="1">' . _('Yes') . '</option>
					</select>
				</td>';
		echo '<td><input type="submit" name="AllowRePrint" value="Update" /></td></tr>';
	} //$_SESSION['PO' . $Identifier]->AllowPrintPO == 0 and empty($_POST['RePrint'])
	elseif ($Printed) {
		echo '<tr>
				<td colspan="2"><a target="_blank"  href="' . $RootPath . '/PO_PDFPurchOrder.php?OrderNo=' . $_SESSION['ExistingOrder'] . '&amp;identifier=' . $Identifier . '">' . _('Reprint Now') . '</a></td></tr>';
	} //$Printed

	echo '</table></td>';
	//Set up the next column with a sub-table in it too
	echo '<td style="width:50%" valign="top">
		<table class="selection" width="100%">';

	if ($_SESSION['ExistingOrder'] != 0 and $_SESSION['PO' . $Identifier]->Status == 'Printed') {
		echo '<tr>
				<td><a href="' . $RootPath . '/GoodsReceived.php?PONumber=' . urlencode($_SESSION['PO' . $Identifier]->OrderNo) . '&amp;identifier=' . urlencode($Identifier) . '">' . _('Receive this order') . '</a></td>
			</tr>';
	} //$_SESSION['ExistingOrder'] != 0 and $_SESSION['PO' . $Identifier]->Status == 'Printed'

	if ($_SESSION['PO' . $Identifier]->Status == '') { //then its a new order
		echo '<tr>
				<td><input type="hidden" name="Status" value="NewOrder" />' . _('New Purchase Order') . '</td>
			</tr>';
	} //$_SESSION['PO' . $Identifier]->Status == ''
	else {
		echo '<tr>
				<td>' . _('Status') . ' :  </td>
				<td><select required="required" name="Status" onchange="ReloadForm(form1.UpdateStatus)">';

		switch ($_SESSION['PO' . $Identifier]->Status) {
			case 'Pending':
				echo '<option selected="selected" value="Pending">' . _('Pending') . '</option>
						<option value="Authorised">' . _('Authorised') . '</option>
						<option value="Rejected">' . _('Rejected') . '</option>';
				break;
			case 'Authorised':
				echo '<option value="Pending">' . _('Pending') . '</option>
						<option selected="selected" value="Authorised">' . _('Authorised') . '</option>
						<option value="Cancelled">' . _('Cancelled') . '</option>';
				break;
			case 'Printed':
				echo '<option value="Pending">' . _('Pending') . '</option>
						<option selected="selected" value="Printed">' . _('Printed') . '</option>
						<option value="Cancelled">' . _('Cancelled') . '</option>
						<option value="Completed">' . _('Completed') . '</option>';
				break;
			case 'Completed':
				echo '<option selected="selected" value="Completed">' . _('Completed') . '</option>';
				break;
			case 'Rejected':
				echo '<option selected="selected" value="Rejected">' . _('Rejected') . '</option>
						<option value="Pending">' . _('Pending') . '</option>
						<option value="Authorised">' . _('Authorised') . '</option>';
				break;
			case 'Cancelled':
				echo '<option selected="selected" value="Cancelled">' . _('Cancelled') . '</option>
						<option value="Authorised">' . _('Authorised') . '</option>
						<option value="Pending">' . _('Pending') . '</option>';
				break;
		} //$_SESSION['PO' . $Identifier]->Status
		echo '</select></td></tr>';

		echo '<tr>
				<td>' . _('Status Comment') . ':</td>
				<td><input type="text" maxlength="50" name="StatusComments" size="50" /></td>
			</tr>
			<tr>
				<td colspan="2">' . html_entity_decode($_SESSION['PO' . $Identifier]->StatusComments, ENT_QUOTES, 'UTF-8') . '</td>
			</tr>';

		echo '<input type="hidden" name="StatusCommentsComplete" value="' . htmlspecialchars($_SESSION['PO' . $Identifier]->StatusComments, ENT_QUOTES, 'UTF-8') . '" />';
		echo '<tr>
				<td><input type="submit" name="UpdateStatus" value="' . _('Status Update') . '" /></td>
			</tr>';
	} //end its not a new order

	echo '</table></td></tr>';

	echo '<tr>
			<th><h3>' . _('Warehouse Info') . '</h3></th>
			<th><h3>' . _('Supplier Info') . '</h3></th>
		</tr>
		<tr><td valign="top">';
	/*nested table level1 */

	echo '<table class="selection" width="100%">
			<tr>
				<td>' . _('Warehouse') . ':</td>
				<td><select required="required" name="StkLocation" onchange="ReloadForm(form1.LookupDeliveryAddress)">';

	$SQL = "SELECT locationname,
					locations.loccode
				FROM locations
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canupd=1";
	$LocnResult = DB_query($SQL);

	while ($LocnRow = DB_fetch_array($LocnResult)) {
		if (isset($_POST['StkLocation']) and ($_POST['StkLocation'] == $LocnRow['loccode']) or (empty($_POST['StkLocation']) and $LocnRow['loccode'] == $_SESSION['UserStockLocation'])) {
			echo '<option selected="selected" value="' . $LocnRow['loccode'] . '">' . $LocnRow['locationname'] . '</option>';
		} //isset($_POST['StkLocation']) and ($_POST['StkLocation'] == $LocnRow['loccode']) or (empty($_POST['StkLocation']) and $LocnRow['loccode'] == $_SESSION['UserStockLocation'])
		else {
			echo '<option value="' . $LocnRow['loccode'] . '">' . $LocnRow['locationname'] . '</option>';
		}
	} //$LocnRow = DB_fetch_array($LocnResult)

	echo '</select>
		<input type="submit" name="LookupDeliveryAddress" value="' . _('Select') . '" /></td>
		</tr>';

	/* If this is the first time
	 * the form loaded set up defaults */

	if (!isset($_POST['StkLocation']) or $_POST['StkLocation'] == '') {
		$_POST['StkLocation'] = $_SESSION['UserStockLocation'];

		$SQL = "SELECT deladd1,
			 			deladd2,
						deladd3,
						deladd4,
						deladd5,
						deladd6,
						tel,
						contact
					FROM locations
					WHERE loccode='" . $_POST['StkLocation'] . "'";

		$LocnAddrResult = DB_query($SQL);
		if (DB_num_rows($LocnAddrResult) == 1) {
			$LocnRow = DB_fetch_array($LocnAddrResult);
			$_POST['DelAdd1'] = $LocnRow['deladd1'];
			$_POST['DelAdd2'] = $LocnRow['deladd2'];
			$_POST['DelAdd3'] = $LocnRow['deladd3'];
			$_POST['DelAdd4'] = $LocnRow['deladd4'];
			$_POST['DelAdd5'] = $LocnRow['deladd5'];
			$_POST['DelAdd6'] = $LocnRow['deladd6'];
			$_POST['Tel'] = $LocnRow['tel'];
			$_POST['Contact'] = $LocnRow['contact'];

			$_SESSION['PO' . $Identifier]->Location = $_POST['StkLocation'];
			$_SESSION['PO' . $Identifier]->DelAdd1 = $_POST['DelAdd1'];
			$_SESSION['PO' . $Identifier]->DelAdd2 = $_POST['DelAdd2'];
			$_SESSION['PO' . $Identifier]->DelAdd3 = $_POST['DelAdd3'];
			$_SESSION['PO' . $Identifier]->DelAdd4 = $_POST['DelAdd4'];
			$_SESSION['PO' . $Identifier]->DelAdd5 = $_POST['DelAdd5'];
			$_SESSION['PO' . $Identifier]->DelAdd6 = $_POST['DelAdd6'];
			$_SESSION['PO' . $Identifier]->Tel = $_POST['Tel'];
			$_SESSION['PO' . $Identifier]->Contact = $_POST['Contact'];

		} //end a location record was returned
		else {
			/*The default location of the user is crook */
			prnMsg(_('The default stock location set up for this user is not a currently defined stock location') . '. ' . _('Your system administrator needs to amend your user record'), 'error');
		}


	} //end StkLocation was not set
	elseif (isset($_POST['LookupDeliveryAddress'])) {
		$SQL = "SELECT deladd1,
						deladd2,
						deladd3,
						deladd4,
						deladd5,
						deladd6,
						tel,
						contact
					FROM locations
					WHERE loccode='" . $_POST['StkLocation'] . "'";

		$LocnAddrResult = DB_query($SQL);
		if (DB_num_rows($LocnAddrResult) == 1) {
			$LocnRow = DB_fetch_array($LocnAddrResult);
			$_POST['DelAdd1'] = $LocnRow['deladd1'];
			$_POST['DelAdd2'] = $LocnRow['deladd2'];
			$_POST['DelAdd3'] = $LocnRow['deladd3'];
			$_POST['DelAdd4'] = $LocnRow['deladd4'];
			$_POST['DelAdd5'] = $LocnRow['deladd5'];
			$_POST['DelAdd6'] = $LocnRow['deladd6'];
			$_POST['Tel'] = $LocnRow['tel'];
			$_POST['Contact'] = $LocnRow['contact'];

			$_SESSION['PO' . $Identifier]->Location = $_POST['StkLocation'];
			$_SESSION['PO' . $Identifier]->DelAdd1 = $_POST['DelAdd1'];
			$_SESSION['PO' . $Identifier]->DelAdd2 = $_POST['DelAdd2'];
			$_SESSION['PO' . $Identifier]->DelAdd3 = $_POST['DelAdd3'];
			$_SESSION['PO' . $Identifier]->DelAdd4 = $_POST['DelAdd4'];
			$_SESSION['PO' . $Identifier]->DelAdd5 = $_POST['DelAdd5'];
			$_SESSION['PO' . $Identifier]->DelAdd6 = $_POST['DelAdd6'];
			$_SESSION['PO' . $Identifier]->Tel = $_POST['Tel'];
			$_SESSION['PO' . $Identifier]->Contact = $_POST['Contact'];
		} //There was a location record returned
	} //user clicked  Lookup Delivery Address


	echo '<tr>
			<td>' . _('Delivery Contact') . ':</td>
			<td><input type="text" name="Contact" size="41" maxlength="40"  value="' . $_SESSION['PO' . $Identifier]->Contact . '" /></td>
		</tr>
		<tr>
			<td>' . _('Address') . ' 1 :</td>
			<td><input type="text" name="DelAdd1" size="41" maxlength="40" value="' . $_POST['DelAdd1'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Address') . ' 2 :</td>
			<td><input type="text" name="DelAdd2" size="41" maxlength="40" value="' . $_POST['DelAdd2'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Address') . ' 3 :</td>
			<td><input type="text" name="DelAdd3" size="41" maxlength="40" value="' . $_POST['DelAdd3'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Address') . ' 4 :</td>
			<td><input type="text" name="DelAdd4" size="41" maxlength="40" value="' . $_POST['DelAdd4'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Address') . ' 5 :</td>
			<td><input type="text" name="DelAdd5" size="21" maxlength="20" value="' . $_POST['DelAdd5'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Address') . ' 6 :</td>
			<td><input type="text" name="DelAdd6" size="16" maxlength="15" value="' . $_POST['DelAdd6'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Phone') . ':</td>
			<td><input type="tel" name="Tel" size="31" maxlength="30" value="' . $_SESSION['PO' . $Identifier]->Tel . '" /></td>
		</tr>
		<tr>
			<td>' . _('Delivery By') . ':</td><td><select name="DeliveryBy">';

	$ShipperResult = DB_query("SELECT shipper_id, shippername FROM shippers");

	while ($ShipperRow = DB_fetch_array($ShipperResult)) {
		if (isset($_POST['DeliveryBy']) and ($_POST['DeliveryBy'] == $ShipperRow['shipper_id'])) {
			echo '<option selected="selected" value="' . $ShipperRow['shipper_id'] . '">' . $ShipperRow['shippername'] . '</option>';
		} //isset($_POST['DeliveryBy']) and ($_POST['DeliveryBy'] == $ShipperRow['shipper_id'])
		else {
			echo '<option value="' . $ShipperRow['shipper_id'] . '">' . $ShipperRow['shippername'] . '</option>';
		}
	} //$ShipperRow = DB_fetch_array($ShipperResult)

	echo '</select></td>
		</tr>
		</table>';
	/* end of sub table */

	echo '</td><td>';
	/*sub table nested */
	echo '<table class="selection" width="100%">
			<tr>
				<td>' . _('Supplier Selection') . ':</td>
				<td><select required="required" name="Keywords" onchange="ReloadForm(form1.SearchSuppliers)">';

	$SuppCoResult = DB_query("SELECT supplierid, suppname FROM suppliers ORDER BY suppname");

	while ($SuppCoRow = DB_fetch_array($SuppCoResult)) {
		if ($SuppCoRow['suppname'] == $_SESSION['PO' . $Identifier]->SupplierName) {
			echo '<option selected="selected" value="' . $SuppCoRow['suppname'] . '">' . $SuppCoRow['suppname'] . '</option>';
		} //$SuppCoRow['suppname'] == $_SESSION['PO' . $Identifier]->SupplierName
		else {
			echo '<option value="' . $SuppCoRow['suppname'] . '">' . $SuppCoRow['suppname'] . '</option>';
		}
	} //$SuppCoRow = DB_fetch_array($SuppCoResult)

	echo '</select> ';
	echo '<input type="submit" name="SearchSuppliers" value="' . _('Select Now') . '" /></td>
		</tr>';

	$SQL = "SELECT contact FROM suppliercontacts WHERE supplierid='" . $_POST['Select'] . "'";
	$SuppCoResult = DB_query($SQL);

	echo '<tr>
				<td>' . _('Supplier Contact') . ':</td>
				<td>
					<select name="SupplierContact">
						<option value=""></option>';

	while ($SuppCoRow = DB_fetch_array($SuppCoResult)) {
		if ($_POST['SupplierContact'] == $SuppCoRow['contact'] or ($_POST['SupplierContact'] == '' and $SuppCoRow['contact'] == $_SESSION['PO' . $Identifier]->SupplierContact)) {
			echo '<option selected="selected" value="' . $SuppCoRow['contact'] . '">' . $SuppCoRow['contact'] . '</option>';
		} //$_POST['SupplierContact'] == $SuppCoRow['contact'] or ($_POST['SupplierContact'] == '' and $SuppCoRow['contact'] == $_SESSION['PO' . $Identifier]->SupplierContact)
		else {
			echo '<option value="' . $SuppCoRow['contact'] . '">' . $SuppCoRow['contact'] . '</option>';
		}
	} //$SuppCoRow = DB_fetch_array($SuppCoResult)

	echo '</select> </td>
		</tr>
		<tr>
			<td>' . _('Address') . ' 1 :</td>
			<td><input type="text" name="SuppDelAdd1" size="41" maxlength="40" value="' . $_POST['SuppDelAdd1'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Address') . ' 2 :</td>
			<td><input type="text" name="SuppDelAdd2" size="41" maxlength="40" value="' . $_POST['SuppDelAdd2'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Address') . ' 3 :</td>
			<td><input type="text" name="SuppDelAdd3" size="41" maxlength="40" value="' . $_POST['SuppDelAdd3'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Address') . ' 4 :</td>
			<td><input type="text" name="SuppDelAdd4" size="21" maxlength="20" value="' . $_POST['SuppDelAdd4'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Address') . ' 5 :</td>
			<td><input type="text" name="SuppDelAdd5" size="21" maxlength="20" value="' . $_POST['SuppDelAdd5'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Address') . ' 6 :</td>
			<td><input type="text" name="SuppDelAdd6" size="16" maxlength="15" value="' . $_POST['SuppDelAdd6'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Phone') . ':</td>
			<td><input type="tel" name="SuppTel" size="31" maxlength="30" value="' . $_SESSION['PO' . $Identifier]->SuppTel . '" /></td>
		</tr>';

	$Result = DB_query("SELECT terms, termsindicator FROM paymentterms");

	echo '<tr>
			<td>' . _('Payment Terms') . ':</td>
			<td><select name="PaymentTerms">';

	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['termsindicator'] == $_SESSION['PO' . $Identifier]->PaymentTerms) {
			echo '<option selected="selected" value="' . $MyRow['termsindicator'] . '">' . $MyRow['terms'] . '</option>';
		} //$MyRow['termsindicator'] == $_SESSION['PO' . $Identifier]->PaymentTerms
		else {
			echo '<option value="' . $MyRow['termsindicator'] . '">' . $MyRow['terms'] . '</option>';
		} //end while loop
	} //$MyRow = DB_fetch_array($Result)
	DB_data_seek($Result, 0);
	echo '</select></td></tr>';

	$Result = DB_query("SELECT loccode,
							locationname
						FROM locations WHERE loccode='" . $_SESSION['PO' . $Identifier]->Port . "'");
	$MyRow = DB_fetch_array($Result);
	$_POST['Port'] = $MyRow['locationname'];

	echo '<tr>
			<td>' . _('Delivery To') . ':</td>
			<td><input type="text" name="Port" size="31" value="' . $_POST['Port'] . '" /></td>
		</tr>';

	if ($_SESSION['PO' . $Identifier]->CurrCode != $_SESSION['CompanyRecord']['currencydefault']) {
		echo '<tr><td>' . _('Exchange Rate') . ':' . '</td>
				<td><input type="text" name="ExRate" value="' . locale_number_format($_POST['ExRate'], 5) . '" class="number" size="11" /></td>
			</tr>';
	} //$_SESSION['PO' . $Identifier]->CurrCode != $_SESSION['CompanyRecord']['currencydefault']
	else {
		echo '<tr>
				<td><input type="hidden" name="ExRate" value="1" /></td>
			</tr>';
	}
	echo '</table>';
	/*end of sub table */

	echo '</td></tr>';
	echo '<tr>
			<th colspan="4"><h3>' . _('Comments');

	$Default_Comments = '';

	if (!isset($_POST['Comments'])) {
		$_POST['Comments'] = $Default_Comments;
	} //!isset($_POST['Comments'])

	echo ':</h3></th>
			</tr>
			<tr>
				<td colspan="4"><textarea name="Comments" style="width:100%" rows="5" cols="200">' . stripcslashes($_POST['Comments']) . '</textarea></td>
			</tr>
			</table>
			<br />';
	/* end of main table */

	echo '<div class="centre">
			<input type="submit" name="EnterLines" value="' . _('Enter Line Items') . '" />
		</div>';

}
/*end of if supplier selected */

echo '</form>';
include('includes/footer.inc');
?>