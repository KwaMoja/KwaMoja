<?php

include ('includes/DefineCartClass.php');

/* Session started in session.inc for password checking and authorisation level check
 config.php is in turn included in session.inc*/

include ('includes/session.inc');

if (isset($_GET['ModifyOrderNumber'])) {
	$Title = _('Modifying Order') . ' ' . $_GET['ModifyOrderNumber'];
} else {
	$Title = _('Select Order Items');
}
/* Manual links before header.inc */
$ViewTopic = 'SalesOrders';
$BookMark = 'SalesOrderEntry';

include ('includes/header.inc');
include ('includes/GetPrice.inc');
include ('includes/SQL_CommonFunctions.inc');

if (isset($_POST['QuickEntry'])) {
	unset($_POST['PartSearch']);
} //isset($_POST['QuickEntry'])

if (isset($_POST['SelectingOrderItems'])) {
	foreach ($_POST as $FormVariable => $Quantity) {
		if (mb_strpos($FormVariable, 'OrderQty') !== false) {
			$NewItemArray[$_POST['StockID' . mb_substr($FormVariable, 8) ]] = filter_number_format($Quantity);
		} //mb_strpos($FormVariable, 'OrderQty') !== false

	} //$_POST as $FormVariable => $Quantity

} //isset($_POST['SelectingOrderItems'])

if (isset($_GET['NewItem'])) {
	$NewItem = trim($_GET['NewItem']);
} //isset($_GET['NewItem'])

if (empty($_GET['identifier'])) {
	/*unique session identifier to ensure that there is no conflict with other order entry sessions on the same machine  */
	$Identifier = date('U');
} else {
	$Identifier = $_GET['identifier'];
}

if (isset($_GET['NewOrder'])) {
	/*New order entry - clear any existing order details from the Items object and initiate a newy*/
	if (isset($_SESSION['Items' . $Identifier])) {
		unset($_SESSION['Items' . $Identifier]->LineItems);
		$_SESSION['Items' . $Identifier]->ItemsOrdered = 0;
		unset($_SESSION['Items' . $Identifier]);
	} //isset($_SESSION['Items' . $Identifier])

	$_SESSION['ExistingOrder' . $Identifier] = 0;
	$_SESSION['Items' . $Identifier] = new cart;

	if ((isset($SupplierLogin) and $SupplierLogin == 0)) { //its a customer logon
		$_SESSION['Items' . $Identifier]->DebtorNo = $_SESSION['CustomerID'];
		$_SESSION['RequireCustomerSelection'] = 0;
	} //count($_SESSION['AllowedPageSecurityTokens']) == 1
	else {
		$_SESSION['Items' . $Identifier]->DebtorNo = '';
		$_SESSION['RequireCustomerSelection'] = 1;
	}

} //isset($_GET['NewOrder'])

if (isset($_GET['ModifyOrderNumber']) and $_GET['ModifyOrderNumber'] != '') {
	/* The delivery check screen is where the details of the order are either updated or inserted depending on the value of ExistingOrder */

	if (isset($_SESSION['Items' . $Identifier])) {
		unset($_SESSION['Items' . $Identifier]->LineItems);
		unset($_SESSION['Items' . $Identifier]);
	} //isset($_SESSION['Items' . $Identifier])
	$_SESSION['ExistingOrder' . $Identifier] = $_GET['ModifyOrderNumber'];
	$_SESSION['RequireCustomerSelection'] = 0;
	$_SESSION['Items' . $Identifier] = new cart;

	/*read in all the guff from the selected order into the Items cart  */

	$OrderHeaderSQL = "SELECT salesorders.debtorno,
			 				  debtorsmaster.name,
							  salesorders.branchcode,
							  salesorders.customerref,
							  salesorders.comments,
							  salesorders.orddate,
							  salesorders.ordertype,
							  salestypes.sales_type,
							  salesorders.shipvia,
							  salesorders.deliverto,
							  salesorders.deladd1,
							  salesorders.deladd2,
							  salesorders.deladd3,
							  salesorders.deladd4,
							  salesorders.deladd5,
							  salesorders.deladd6,
							  salesorders.contactphone,
							  salesorders.contactemail,
							  salesorders.salesperson,
							  salesorders.freightcost,
							  salesorders.deliverydate,
							  debtorsmaster.currcode,
							  currencies.decimalplaces,
							  paymentterms.terms,
							  salesorders.fromstkloc,
							  salesorders.printedpackingslip,
							  salesorders.datepackingslipprinted,
							  salesorders.quotation,
							  salesorders.quotedate,
							  salesorders.confirmeddate,
							  salesorders.deliverblind,
							  debtorsmaster.customerpoline,
							  locations.locationname,
							  custbranch.estdeliverydays,
							  custbranch.salesman
						FROM salesorders
						INNER JOIN debtorsmaster
							ON salesorders.debtorno = debtorsmaster.debtorno
						INNER JOIN salestypes
							ON salesorders.ordertype=salestypes.typeabbrev
						INNER JOIN custbranch
							ON salesorders.debtorno = custbranch.debtorno
							AND salesorders.branchcode = custbranch.branchcode
						INNER JOIN paymentterms
							ON debtorsmaster.paymentterms=paymentterms.termsindicator
						INNER JOIN locations
							ON locations.loccode=salesorders.fromstkloc
						INNER JOIN currencies
							ON debtorsmaster.currcode=currencies.currabrev
						INNER JOIN locationusers
							ON locationusers.loccode=salesorders.fromstkloc
							AND locationusers.userid='" . $_SESSION['UserID'] . "'
							AND locationusers.canupd=1
						WHERE salesorders.orderno = '" . $_GET['ModifyOrderNumber'] . "'";

	$ErrMsg = _('The order cannot be retrieved because');
	$GetOrdHdrResult = DB_query($OrderHeaderSQL, $ErrMsg);

	if (DB_num_rows($GetOrdHdrResult) == 1) {
		$MyRow = DB_fetch_array($GetOrdHdrResult);
		if ($_SESSION['SalesmanLogin'] != '' and $_SESSION['SalesmanLogin'] != $MyRow['salesman']) {
			prnMsg(_('Your account is set up to see only a specific salespersons orders. You are not authorised to modify this order'), 'error');

			include ('includes/footer.inc');
			exit;
		} //$_SESSION['SalesmanLogin'] != '' and $_SESSION['SalesmanLogin'] != $MyRow['salesman']
		$_SESSION['Items' . $Identifier]->OrderNo = $_GET['ModifyOrderNumber'];
		$_SESSION['Items' . $Identifier]->DebtorNo = DB_escape_string($MyRow['debtorno']);
		$_SESSION['Items' . $Identifier]->CreditAvailable = GetCreditAvailable($_SESSION['Items' . $Identifier]->DebtorNo);
		/*CustomerID defined in header.inc */
		$_SESSION['Items' . $Identifier]->Branch = DB_escape_string($MyRow['branchcode']);
		$_SESSION['Items' . $Identifier]->CustomerName = $MyRow['name'];
		$_SESSION['Items' . $Identifier]->CustRef = $MyRow['customerref'];
		$_SESSION['Items' . $Identifier]->Comments = stripcslashes($MyRow['comments']);
		$_SESSION['Items' . $Identifier]->PaymentTerms = $MyRow['terms'];
		$_SESSION['Items' . $Identifier]->DefaultSalesType = $MyRow['ordertype'];
		$_SESSION['Items' . $Identifier]->SalesTypeName = $MyRow['sales_type'];
		$_SESSION['Items' . $Identifier]->DefaultCurrency = $MyRow['currcode'];
		$_SESSION['Items' . $Identifier]->CurrDecimalPlaces = $MyRow['decimalplaces'];
		$_SESSION['Items' . $Identifier]->ShipVia = $MyRow['shipvia'];
		$BestShipper = $MyRow['shipvia'];
		$_SESSION['Items' . $Identifier]->DeliverTo = $MyRow['deliverto'];
		$_SESSION['Items' . $Identifier]->DeliveryDate = ConvertSQLDate($MyRow['deliverydate']);
		$_SESSION['Items' . $Identifier]->DelAdd1 = $MyRow['deladd1'];
		$_SESSION['Items' . $Identifier]->DelAdd2 = $MyRow['deladd2'];
		$_SESSION['Items' . $Identifier]->DelAdd3 = $MyRow['deladd3'];
		$_SESSION['Items' . $Identifier]->DelAdd4 = $MyRow['deladd4'];
		$_SESSION['Items' . $Identifier]->DelAdd5 = $MyRow['deladd5'];
		$_SESSION['Items' . $Identifier]->DelAdd6 = $MyRow['deladd6'];
		$_SESSION['Items' . $Identifier]->PhoneNo = $MyRow['contactphone'];
		$_SESSION['Items' . $Identifier]->Email = $MyRow['contactemail'];
		$_SESSION['Items' . $Identifier]->SalesPerson = $MyRow['salesperson'];
		$_SESSION['Items' . $Identifier]->Location = $MyRow['fromstkloc'];
		$_SESSION['Items' . $Identifier]->LocationName = $MyRow['locationname'];
		$_SESSION['Items' . $Identifier]->Quotation = $MyRow['quotation'];
		$_SESSION['Items' . $Identifier]->QuoteDate = ConvertSQLDate($MyRow['quotedate']);
		$_SESSION['Items' . $Identifier]->ConfirmedDate = ConvertSQLDate($MyRow['confirmeddate']);
		$_SESSION['Items' . $Identifier]->FreightCost = $MyRow['freightcost'];
		$_SESSION['Items' . $Identifier]->Orig_OrderDate = $MyRow['orddate'];
		$_SESSION['PrintedPackingSlip'] = $MyRow['printedpackingslip'];
		$_SESSION['DatePackingSlipPrinted'] = $MyRow['datepackingslipprinted'];
		$_SESSION['Items' . $Identifier]->DeliverBlind = $MyRow['deliverblind'];
		$_SESSION['Items' . $Identifier]->DefaultPOLine = $MyRow['customerpoline'];
		$_SESSION['Items' . $Identifier]->DeliveryDays = $MyRow['estdeliverydays'];

		//Get The exchange rate used for GPPercent calculations on adding or amending items
		if ($_SESSION['Items' . $Identifier]->DefaultCurrency != $_SESSION['CompanyRecord']['currencydefault']) {
			$ExRateResult = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['Items' . $Identifier]->DefaultCurrency . "'");
			if (DB_num_rows($ExRateResult) > 0) {
				$ExRateRow = DB_fetch_row($ExRateResult);
				$ExRate = $ExRateRow[0];
			} //DB_num_rows($ExRateResult) > 0
			else {
				$ExRate = 1;
			}
		} //$_SESSION['Items' . $Identifier]->DefaultCurrency != $_SESSION['CompanyRecord']['currencydefault']
		else {
			$ExRate = 1;
		}

		/*need to look up customer name from debtors master then populate the line items array with the sales order details records */

		$LineItemsSQL = "SELECT salesorderdetails.orderlineno,
								salesorderdetails.stkcode,
								stockmaster.description,
								stockmaster.longdescription,
								stockmaster.volume,
								stockmaster.grossweight,
								stockmaster.units,
								stockmaster.serialised,
								stockmaster.nextserialno,
								stockmaster.eoq,
								salesorderdetails.unitprice,
								salesorderdetails.quantity,
								salesorderdetails.discountpercent,
								salesorderdetails.actualdispatchdate,
								salesorderdetails.qtyinvoiced,
								salesorderdetails.narrative,
								salesorderdetails.itemdue,
								salesorderdetails.poline,
								locstock.quantity as qohatloc,
								stockmaster.mbflag,
								stockmaster.discountcategory,
								stockmaster.decimalplaces,
								stockcosts.materialcost+stockcosts.labourcost+stockcosts.overheadcost AS standardcost,
								salesorderdetails.completed
							FROM salesorderdetails
							INNER JOIN stockmaster
								ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcosts
								ON stockcosts.stockid = stockmaster.stockid
								AND stockcosts.succeeded=0
							INNER JOIN locstock
								ON locstock.stockid = stockmaster.stockid
							WHERE  locstock.loccode = '" . $MyRow['fromstkloc'] . "'
								AND salesorderdetails.orderno ='" . $_GET['ModifyOrderNumber'] . "'
							ORDER BY salesorderdetails.orderlineno";

		$ErrMsg = _('The line items of the order cannot be retrieved because');
		$LineItemsResult = DB_query($LineItemsSQL, $ErrMsg);
		if (DB_num_rows($LineItemsResult) > 0) {
			while ($MyRow = DB_fetch_array($LineItemsResult)) {
				if ($MyRow['completed'] == 0) {
					$_SESSION['Items' . $Identifier]->add_to_cart($MyRow['stkcode'], $MyRow['quantity'], $MyRow['description'], $MyRow['longdescription'], $MyRow['unitprice'], $MyRow['discountpercent'], $MyRow['units'], $MyRow['volume'], $MyRow['grossweight'], $MyRow['qohatloc'], $MyRow['mbflag'], $MyRow['actualdispatchdate'], $MyRow['qtyinvoiced'], $MyRow['discountcategory'], 0, /*Controlled*/
					$MyRow['serialised'], $MyRow['decimalplaces'], $MyRow['narrative'], 'No', /* Update DB */
					$MyRow['orderlineno'], 0, '', ConvertSQLDate($MyRow['itemdue']), $MyRow['poline'], $MyRow['standardcost'], $MyRow['eoq'], $MyRow['nextserialno'], $ExRate, $Identifier);

					/*Just populating with existing order - no DBUpdates */
				} //$MyRow['completed'] == 0
				$LastLineNo = $MyRow['orderlineno'];
			} //$MyRow = DB_fetch_array($LineItemsResult)

			/* line items from sales order details */
			$_SESSION['Items' . $Identifier]->LineCounter = $LastLineNo + 1;
		} //end of checks on returned data set

	} //DB_num_rows($GetOrdHdrResult) == 1

} //isset($_GET['ModifyOrderNumber']) and $_GET['ModifyOrderNumber'] != ''

if (!isset($_SESSION['Items' . $Identifier])) {
	/* It must be a new order being created $_SESSION['Items'.$Identifier] would be set up from the order
	modification code above if a modification to an existing order. Also $ExistingOrder would be
	set to 1. The delivery check screen is where the details of the order are either updated or
	inserted depending on the value of ExistingOrder */

	$_SESSION['ExistingOrder' . $Identifier] = 0;
	$_SESSION['Items' . $Identifier] = new cart;
	$_SESSION['PrintedPackingSlip'] = 0;
	/*Of course cos the order aint even started !!*/

	if (($_SESSION['Items' . $Identifier]->DebtorNo == '' or !isset($_SESSION['Items' . $Identifier]->DebtorNo))) {
		/* need to select a customer for the first time out if authorisation allows it and if a customer
		has been selected for the order or not the session variable CustomerID holds the customer code
		already as determined from user id /password entry  */
		$_SESSION['RequireCustomerSelection'] = 1;
	} //($_SESSION['Items' . $Identifier]->DebtorNo == '' or !isset($_SESSION['Items' . $Identifier]->DebtorNo))
	else {
		$_SESSION['RequireCustomerSelection'] = 0;
	}
} //!isset($_SESSION['Items' . $Identifier])

if (isset($_POST['ChangeCustomer']) and $_POST['ChangeCustomer'] != '') {
	if ($_SESSION['Items' . $Identifier]->Any_Already_Delivered() == 0) {
		$_SESSION['RequireCustomerSelection'] = 1;
	} //$_SESSION['Items' . $Identifier]->Any_Already_Delivered() == 0
	else {
		prnMsg(_('The customer the order is for cannot be modified once some of the order has been invoiced'), 'warn');
	}
} //isset($_POST['ChangeCustomer']) and $_POST['ChangeCustomer'] != ''

//Customer logins are not allowed to select other customers hence in_array(2,$_SESSION['AllowedPageSecurityTokens'])

if (isset($_POST['SearchCust']) and $_SESSION['RequireCustomerSelection'] == 1) {
	//insert wildcard characters in spaces
	$_POST['CustKeywords'] = mb_strtoupper(trim($_POST['CustKeywords']));
	$SearchString = str_replace(' ', '%', $_POST['CustKeywords']);

	$SQL = "SELECT custbranch.brname,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.branchcode,
					custbranch.debtorno,
					debtorsmaster.name
				FROM custbranch
				LEFT JOIN debtorsmaster
					ON custbranch.debtorno=debtorsmaster.debtorno
				WHERE custbranch.brname " . LIKE . " '%" . $SearchString . "%'
					AND custbranch.branchcode " . LIKE . " '%" . mb_strtoupper(trim($_POST['CustCode'])) . "%'
					AND custbranch.phoneno " . LIKE . " '%" . trim($_POST['CustPhone']) . "%'
					AND custbranch.disabletrans=0";
	if ($_SESSION['SalesmanLogin'] != '') {
		$SQL.= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
	} //$_SESSION['SalesmanLogin'] != ''
	$SQL.= " ORDER BY custbranch.debtorno,
						custbranch.branchcode";

	$ErrMsg = _('The searched customer records requested cannot be retrieved because');
	$Result_CustSelect = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result_CustSelect) == 1) {
		$MyRow = DB_fetch_array($Result_CustSelect);
		$SelectedCustomer = $MyRow['debtorno'];
		$SelectedBranch = $MyRow['branchcode'];
	} //DB_num_rows($Result_CustSelect) == 1
	elseif (DB_num_rows($Result_CustSelect) == 0) {
		prnMsg(_('No Customer Branch records contain the search criteria') . ' - ' . _('please try again') . ' - ' . _('Note a Customer Branch Name may be different to the Customer Name'), 'info');
	} //DB_num_rows($Result_CustSelect) == 0

} //isset($_POST['SearchCust']) and $_SESSION['RequireCustomerSelection'] == 1)

/*end of if search for customer codes/names */

if (isset($_POST['JustSelectedACustomer']) and !isset($_POST['SearchCust'])) {
	/*Need to figure out the number of the form variable that the user clicked on */
	for ($i = 0;$i < count($_POST);$i++) { //loop through the returned customers
		if (isset($_POST['SubmitCustomerSelection' . $i])) {
			break;
		} //isset($_POST['SubmitCustomerSelection' . $i])

	} //$i = 0; $i < count($_POST); $i++
	if ($i == count($_POST) and !isset($SelectedCustomer)) {//if there is ONLY one customer searched at above, the $SelectedCustomer already setup, then there is a wrong warning
		prnMsg(_('Unable to identify the selected customer'), 'error');
	} elseif (!isset($SelectedCustomer)) {
		$SelectedCustomer = $_POST['SelectedCustomer' . $i];
		$SelectedBranch = $_POST['SelectedBranch' . $i];
	}
} //isset($_POST['JustSelectedACustomer'])

/* will only be true if page called from customer selection form or set because only one customer
 record returned from a search so parse the $SelectCustomer string into customer code and branch code */
if (isset($SelectedCustomer)) {
	$_SESSION['Items' . $Identifier]->DebtorNo = trim($SelectedCustomer);
	$_SESSION['Items' . $Identifier]->Branch = trim($SelectedBranch);

	// Now check to ensure this account is not on hold */
	$SQL = "SELECT debtorsmaster.name,
					holdreasons.dissallowinvoices,
					debtorsmaster.salestype,
					salestypes.sales_type,
					debtorsmaster.currcode,
					debtorsmaster.customerpoline,
					paymentterms.terms,
					currencies.decimalplaces
			FROM debtorsmaster INNER JOIN holdreasons
			ON debtorsmaster.holdreason=holdreasons.reasoncode
			INNER JOIN salestypes
			ON debtorsmaster.salestype=salestypes.typeabbrev
			INNER JOIN paymentterms
			ON debtorsmaster.paymentterms=paymentterms.termsindicator
			INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			WHERE debtorsmaster.debtorno = '" . $_SESSION['Items' . $Identifier]->DebtorNo . "'";

	$ErrMsg = _('The details of the customer selected') . ': ' . $_SESSION['Items' . $Identifier]->DebtorNo . ' ' . _('cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the customer details and failed was') . ':';
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

	$MyRow = DB_fetch_array($Result);
	if ($MyRow['dissallowinvoices'] != 1) {
		if ($MyRow['dissallowinvoices'] == 2) {
			prnMsg(_('The') . ' ' . htmlspecialchars($MyRow[0], ENT_QUOTES, 'UTF-8', false) . ' ' . _('account is currently flagged as an account that needs to be watched. Please contact the credit control personnel to discuss'), 'warn');
		} //$MyRow[1] == 2

		$_SESSION['RequireCustomerSelection'] = 0;
		$_SESSION['Items' . $Identifier]->CustomerName = $MyRow['name'];

		// the sales type determines the price list to be used by default the customer of the user is
		// defaulted from the entry of the userid and password.

		$_SESSION['Items' . $Identifier]->DefaultSalesType = $MyRow['salestype'];
		$_SESSION['Items' . $Identifier]->SalesTypeName = $MyRow['sales_type'];
		$_SESSION['Items' . $Identifier]->DefaultCurrency = $MyRow['currcode'];
		$_SESSION['Items' . $Identifier]->DefaultPOLine = $MyRow['customerpoline'];
		$_SESSION['Items' . $Identifier]->PaymentTerms = $MyRow['terms'];
		$_SESSION['Items' . $Identifier]->CurrDecimalPlaces = $MyRow['decimalplaces'];

		// the branch was also selected from the customer selection so default the delivery details from the customer branches table CustBranch. The order process will ask for branch details later anyway

		$Result = GetCustBranchDetails($Identifier);

		$MyRow = DB_fetch_array($Result);

		if ($_SESSION['SalesmanLogin'] != NULL and $_SESSION['SalesmanLogin'] != $MyRow['salesman']) {
			prnMsg(_('Your login is only set up for a particular salesperson. This customer has a different salesperson.'), 'error');
			include ('includes/footer.inc');
			exit;
		} //$_SESSION['SalesmanLogin'] != NULL and $_SESSION['SalesmanLogin'] != $MyRow['salesman']
		$_SESSION['Items' . $Identifier]->DeliverTo = $MyRow['brname'];
		$_SESSION['Items' . $Identifier]->DelAdd1 = $MyRow['braddress1'];
		$_SESSION['Items' . $Identifier]->DelAdd2 = $MyRow['braddress2'];
		$_SESSION['Items' . $Identifier]->DelAdd3 = $MyRow['braddress3'];
		$_SESSION['Items' . $Identifier]->DelAdd4 = $MyRow['braddress4'];
		$_SESSION['Items' . $Identifier]->DelAdd5 = $MyRow['braddress5'];
		$_SESSION['Items' . $Identifier]->DelAdd6 = $MyRow['braddress6'];
		$_SESSION['Items' . $Identifier]->PhoneNo = $MyRow['phoneno'];
		$_SESSION['Items' . $Identifier]->Email = $MyRow['email'];
		$_SESSION['Items' . $Identifier]->Location = $MyRow['defaultlocation'];
		$_SESSION['Items' . $Identifier]->ShipVia = $MyRow['defaultshipvia'];
		$_SESSION['Items' . $Identifier]->DeliverBlind = $MyRow['deliverblind'];
		$_SESSION['Items' . $Identifier]->SpecialInstructions = $MyRow['specialinstructions'];
		$_SESSION['Items' . $Identifier]->DeliveryDays = $MyRow['estdeliverydays'];
		$_SESSION['Items' . $Identifier]->LocationName = $MyRow['locationname'];
		if ($_SESSION['SalesmanLogin'] != NULL and $_SESSION['SalesmanLogin'] != '') {
			$_SESSION['Items' . $Identifier]->SalesPerson = $_SESSION['SalesmanLogin'];
		} //$_SESSION['SalesmanLogin'] != NULL and $_SESSION['SalesmanLogin'] != ''
		else {
			$_SESSION['Items' . $Identifier]->SalesPerson = $MyRow['salesman'];
		}
		if ($_SESSION['Items' . $Identifier]->SpecialInstructions) prnMsg($_SESSION['Items' . $Identifier]->SpecialInstructions, 'warn');

		if ($_SESSION['CheckCreditLimits'] > 0) {
			/*Check credit limits is 1 for warn and 2 for prohibit sales */

			$_SESSION['Items' . $Identifier]->CreditAvailable = GetCreditAvailable($_SESSION['Items' . $Identifier]->DebtorNo);

			if ($_SESSION['CheckCreditLimits'] == 1 and $_SESSION['Items' . $Identifier]->CreditAvailable <= 0) {
				prnMsg(_('The') . ' ' . htmlspecialchars($MyRow[0], ENT_QUOTES, 'UTF-8', false) . ' ' . _('account is currently at or over their credit limit'), 'warn');
			} //$_SESSION['CheckCreditLimits'] == 1 and $_SESSION['Items' . $Identifier]->CreditAvailable <= 0
			elseif ($_SESSION['CheckCreditLimits'] == 2 and $_SESSION['Items' . $Identifier]->CreditAvailable <= 0) {
				prnMsg(_('No more orders can be placed by') . ' ' . htmlspecialchars($MyRow[0], ENT_QUOTES, 'UTF-8', false) . ' ' . _(' their account is currently at or over their credit limit'), 'warn');
				include ('includes/footer.inc');
				exit;
			} //$_SESSION['CheckCreditLimits'] == 2 and $_SESSION['Items' . $Identifier]->CreditAvailable <= 0

		} //$_SESSION['CheckCreditLimits'] > 0

	} //$MyRow[1] != 1
	else {
		prnMsg(_('The') . ' ' . htmlspecialchars($MyRow[0], ENT_QUOTES, 'UTF-8', false) . ' ' . _('account is currently on hold please contact the credit control personnel to discuss'), 'warn');
	}

} elseif ((!$_SESSION['Items' . $Identifier]->DefaultSalesType or $_SESSION['Items' . $Identifier]->DefaultSalesType == '') and !isset($_GET['NewOrder'])) {
	//Possible that the check to ensure this account is not on hold has not been done
	//if the customer is placing own order, if this is the case then
	//DefaultSalesType will not have been set as above

	$SQL = "SELECT debtorsmaster.name,
					holdreasons.dissallowinvoices,
					debtorsmaster.salestype,
					debtorsmaster.currcode,
					currencies.decimalplaces,
					debtorsmaster.customerpoline
			FROM debtorsmaster
			INNER JOIN holdreasons
				ON debtorsmaster.holdreason=holdreasons.reasoncode
			INNER JOIN currencies
				ON debtorsmaster.currcode=currencies.currabrev
			WHERE debtorsmaster.debtorno = '" . $_SESSION['Items' . $Identifier]->DebtorNo . "'";

	$ErrMsg = _('The details for the customer selected') . ': ' . $_SESSION['Items' . $Identifier]->DebtorNo . ' ' . _('cannot be retrieved because');
	$DbgMsg = _('SQL used to retrieve the customer details was') . ':<br />' . $SQL;
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

	$MyRow = DB_fetch_array($Result);
	if ($MyRow[1] == 0) {
		$_SESSION['Items' . $Identifier]->CustomerName = $MyRow[0];

		// the sales type determines the price list to be used by default the customer of the user is
		// defaulted from the entry of the userid and password.

		$_SESSION['Items' . $Identifier]->DefaultSalesType = $MyRow['salestype'];
		$_SESSION['Items' . $Identifier]->DefaultCurrency = $MyRow['currcode'];
		$_SESSION['Items' . $Identifier]->CurrDecimalPlaces = $MyRow['decimalplaces'];
		$_SESSION['Items' . $Identifier]->Branch = $_SESSION['UserBranch'];
		$_SESSION['Items' . $Identifier]->DefaultPOLine = $MyRow['customerpoline'];

		// the branch would be set in the user data so default delivery details as necessary. However,
		// the order process will ask for branch details later anyway

		$Result = GetCustBranchDetails($Identifier);

		$MyRow = DB_fetch_array($Result);

		$_SESSION['Items' . $Identifier]->DeliverTo = $MyRow['brname'];
		$_SESSION['Items' . $Identifier]->DelAdd1 = $MyRow['braddress1'];
		$_SESSION['Items' . $Identifier]->DelAdd2 = $MyRow['braddress2'];
		$_SESSION['Items' . $Identifier]->DelAdd3 = $MyRow['braddress3'];
		$_SESSION['Items' . $Identifier]->DelAdd4 = $MyRow['braddress4'];
		$_SESSION['Items' . $Identifier]->DelAdd5 = $MyRow['braddress5'];
		$_SESSION['Items' . $Identifier]->DelAdd6 = $MyRow['braddress6'];
		$_SESSION['Items' . $Identifier]->PhoneNo = $MyRow['phoneno'];
		$_SESSION['Items' . $Identifier]->Email = $MyRow['email'];
		$_SESSION['Items' . $Identifier]->Location = $MyRow['defaultlocation'];
		$_SESSION['Items' . $Identifier]->DeliverBlind = $MyRow['deliverblind'];
		$_SESSION['Items' . $Identifier]->DeliveryDays = $MyRow['estdeliverydays'];
		$_SESSION['Items' . $Identifier]->LocationName = $MyRow['locationname'];
		if ($_SESSION['SalesmanLogin'] != NULL and $_SESSION['SalesmanLogin'] != '') {
			$_SESSION['Items' . $Identifier]->SalesPerson = $_SESSION['SalesmanLogin'];
		} else {
			$_SESSION['Items' . $Identifier]->SalesPerson = $MyRow['salesman'];
		}
	} //$MyRow[1] == 0
	else {
		prnMsg(_('Sorry, your account has been put on hold for some reason, please contact the credit control personnel.'), 'warn');
		include ('includes/footer.inc');
		exit;
	}
} //!$_SESSION['Items' . $Identifier]->DefaultSalesType or $_SESSION['Items' . $Identifier]->DefaultSalesType == ''

if ($_SESSION['RequireCustomerSelection'] == 1 or !isset($_SESSION['Items' . $Identifier]->DebtorNo) or $_SESSION['Items' . $Identifier]->DebtorNo == '') {
	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Enter an Order or Quotation') . ' : ' . _('Search for the Customer Branch.') . '</p>';
	echo '<div class="page_help_text">' . _('Orders/Quotations are placed against the Customer Branch. A Customer may have several Branches.') . '</div>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $Identifier . '" method="post">
			 <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			 <table cellpadding="3" class="selection">
				<tr>
				<td>' . _('Part of the Customer Branch Name') . ':</td>
				<td><input tabindex="1" type="text" name="CustKeywords" size="20" autofocus="autofocus" maxlength="25" /></td>
				<td><b>' . _('OR') . '</b></td>
				<td>' . _('Part of the Customer Branch Code') . ':</td>
				<td><input tabindex="2" type="text" name="CustCode" size="15" maxlength="18" /></td>
				<td><b>' . _('OR') . '</b></td>
				<td>' . _('Part of the Branch Phone Number') . ':</td>
				<td><input tabindex="3" type="text" name="CustPhone" size="15" maxlength="18" /></td>
				</tr>
			</table>
			<div class="centre">
				<input tabindex="4" type="submit" name="SearchCust" value="' . _('Search Now') . '" />
				<input tabindex="5" type="submit" name="reset" value="' . _('Reset') . '" />
			</div>';

	if (isset($Result_CustSelect)) {
		echo '<div>
				<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
				<input type="hidden" name="JustSelectedACustomer" value="Yes" />
				<table class="selection">
					<thead>
						<tr>
							<th class="SortedColumn">' . _('Customer') . '</th>
							<th class="SortedColumn">' . _('Branch') . '</th>
							<th class="SortedColumn">' . _('Contact') . '</th>
							<th>' . _('Phone') . '</th>
							<th>' . _('Fax') . '</th>
						</tr>
					</thead>';

		$j = 1;
		$k = 0; //row counter to determine background colour
		$LastCustomer = '';
		echo '<tbody>';
		while ($MyRow = DB_fetch_array($Result_CustSelect)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} //$k == 1
			else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			if ($LastCustomer != $MyRow['name']) {
				echo '<td>' . htmlspecialchars($MyRow['name'], ENT_QUOTES, 'UTF-8', false) . '</td>';
			} //$LastCustomer != $MyRow['name']
			else {
				echo '<td></td>';
			}
			echo '<td><input tabindex="' . strval($j + 5) . '" type="submit" name="SubmitCustomerSelection' . $j . '" value="' . htmlspecialchars($MyRow['brname'], ENT_QUOTES, 'UTF-8', false) . '" />
					<input type="hidden" name="SelectedCustomer' . $j . '" value="' . $MyRow['debtorno'] . '" />
					<input type="hidden" name="SelectedBranch' . $j . '" value="' . $MyRow['branchcode'] . '" /></td>
					<td>' . $MyRow['contactname'] . '</td>
					<td>' . $MyRow['phoneno'] . '</td>
					<td>' . $MyRow['faxno'] . '</td>
					</tr>';
			$LastCustomer = $MyRow['name'];
			++$j;
			//end of page full new headings if

		} //$MyRow = DB_fetch_array($Result_CustSelect)
		//end of while loop

		echo '</tbody>';
		echo '</table>';
	} //end if results to show
	echo '</form>';
	//end if RequireCustomerSelection

} else { //dont require customer selection
	// everything below here only do if a customer is selected

	if (isset($_POST['CancelOrder'])) {
		$OK_to_delete = 1; //assume this in the first instance

		if ($_SESSION['ExistingOrder' . $Identifier] != 0) { //need to check that not already dispatched

			$SQL = "SELECT qtyinvoiced
					FROM salesorderdetails
					WHERE orderno='" . $_SESSION['ExistingOrder' . $Identifier] . "'
					AND qtyinvoiced>0";

			$InvQties = DB_query($SQL);

			if (DB_num_rows($InvQties) > 0) {
				$OK_to_delete = 0;

				prnMsg(_('There are lines on this order that have already been invoiced. Please delete only the lines on the order that are no longer required') . '<p>' . _('There is an option on confirming a dispatch/invoice to automatically cancel any balance on the order at the time of invoicing if you know the customer will not want the back order'), 'warn');
			} //DB_num_rows($InvQties) > 0

		} //$_SESSION['ExistingOrder' . $Identifier] != 0

		if ($OK_to_delete == 1) {
			if ($_SESSION['ExistingOrder' . $Identifier] != 0) {
				$SQL = "DELETE FROM salesorderdetails WHERE salesorderdetails.orderno ='" . $_SESSION['ExistingOrder' . $Identifier] . "'";
				$ErrMsg = _('The order detail lines could not be deleted because');
				$DelResult = DB_query($SQL, $ErrMsg);

				$SQL = "DELETE FROM salesorders WHERE salesorders.orderno='" . $_SESSION['ExistingOrder' . $Identifier] . "'";
				$ErrMsg = _('The order header could not be deleted because');
				$DelResult = DB_query($SQL, $ErrMsg);

				$_SESSION['ExistingOrder' . $Identifier] = 0;
			} //$_SESSION['ExistingOrder' . $Identifier] != 0

			unset($_SESSION['Items' . $Identifier]->LineItems);
			$_SESSION['Items' . $Identifier]->ItemsOrdered = 0;
			unset($_SESSION['Items' . $Identifier]);
			$_SESSION['Items' . $Identifier] = new cart;

			$_SESSION['RequireCustomerSelection'] = 0;
			prnMsg(_('This sales order has been cancelled as requested'), 'success');
			include ('includes/footer.inc');
			exit;
		} //$OK_to_delete == 1

	} else {
		/*Not cancelling the order */

		echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/inventory.png" title="' . _('Order') . '" alt="" />' . ' ';

		if ($_SESSION['Items' . $Identifier]->Quotation == 1) {
			echo _('Quotation for customer') . ' ';
		} //$_SESSION['Items' . $Identifier]->Quotation == 1
		else {
			echo _('Order for customer') . ' ';
		}

		echo ':<b> ' . stripslashes($_SESSION['Items' . $Identifier]->DebtorNo) . ' ' . _('Customer Name') . ': ' . htmlspecialchars($_SESSION['Items' . $Identifier]->CustomerName, ENT_QUOTES, 'UTF-8', false);
		echo '</b></p><div class="page_help_text">' . '<b>' . _('Default Options (can be modified during order)') . ':</b><br />' . _('Deliver To') . ':<b> ' . htmlspecialchars($_SESSION['Items' . $Identifier]->DeliverTo, ENT_QUOTES, 'UTF-8', false);
		echo '</b>&nbsp;' . _('From Location') . ':<b> ' . $_SESSION['Items' . $Identifier]->LocationName;
		echo '</b><br />' . _('Sales Type') . '/' . _('Price List') . ':<b> ' . $_SESSION['Items' . $Identifier]->SalesTypeName;
		echo '</b><br />' . _('Terms') . ':<b> ' . $_SESSION['Items' . $Identifier]->PaymentTerms;
		echo '</b></div>';
	}

	$Msg = '';
	if (isset($_POST['Search']) or isset($_POST['Next']) or isset($_POST['Previous'])) {
		if (!empty($_POST['RawMaterialFlag'])) {
			$RawMaterialSellable = " OR stockcategory.stocktype='M'";
		} else {
			$RawMaterialSellable = '';
		}
		if (!empty($_POST['CustItemFlag'])) {
			$IncludeCustItem = " INNER JOIN custitem ON custitem.stockid=stockmaster.stockid
								AND custitem.debtorno='" . $_SESSION['Items' . $Identifier]->DebtorNo . "' ";
		} else {
			$IncludeCustItem = " LEFT OUTER JOIN custitem ON custitem.stockid=stockmaster.stockid
								AND custitem.debtorno='" . $_SESSION['Items' . $Identifier]->DebtorNo . "' ";
		}

		//insert wildcard characters in spaces
		$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
		$KeywordsString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		$_POST['StockCode'] = mb_strtoupper($_POST['StockCode']);
		$StockIdString = '%' . $_POST['StockCode'] . '%';

		if ($_POST['StockCat'] == 'All') {
			$_POST['StockCat'] = '%%';
		} //$_POST['StockCat'] == 'All'
		$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.units,
						custitem.cust_part,
						custitem.cust_description
					FROM stockmaster
					INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid" . $IncludeCustItem . "
					WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='D' OR stockcategory.stocktype='L' " . $RawMaterialSellable . ")
						AND stockmaster.mbflag <>'G'
						AND stockmaster.discontinued=0
						AND stockmaster.discontinued=0
						AND stockmaster.description " . LIKE . " '" . $KeywordsString . "'
						AND stockmaster.categoryid " . LIKE . " '" . $_POST['StockCat'] . "'
						AND stockmaster.stockid " . LIKE . " '" . $StockIdString . "'
					ORDER BY stockmaster.stockid";

		if (isset($_POST['Next'])) {
			$Offset = $_POST['NextList'];
		} //isset($_POST['Next'])
		if (isset($_POST['Previous'])) {
			$Offset = $_POST['PreviousList'];

		} //isset($_POST['Previous'])
		if (!isset($Offset) or $Offset < 0) {
			$Offset = 0;
		} //!isset($Offset) or $Offset < 0
		$SQL = $SQL . " LIMIT " . $_SESSION['DisplayRecordsMax'] . " OFFSET " . strval($_SESSION['DisplayRecordsMax'] * $Offset);

		$ErrMsg = _('There is a problem selecting the part records to display because');
		$DbgMsg = _('The SQL used to get the part selection was');

		$SearchResult = DB_query($SQL, $ErrMsg, $DbgMsg);

		if (DB_num_rows($SearchResult) == 0) {
			prnMsg(_('There are no products available meeting the criteria specified'), 'info');
		} //DB_num_rows($SearchResult) == 0
		if (DB_num_rows($SearchResult) == 1) {
			$MyRow = DB_fetch_array($SearchResult);
			$NewItem = $MyRow['stockid'];
			DB_data_seek($SearchResult, 0);
		} //DB_num_rows($SearchResult) == 1
		if (DB_num_rows($SearchResult) < $_SESSION['DisplayRecordsMax']) {
			$Offset = 0;
		} //DB_num_rows($SearchResult) < $_SESSION['DisplayRecordsMax']

	} //end of if search

	//Always do the stuff below if not looking for a customerid

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $Identifier . '" id="SelectParts" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	//Get The exchange rate used for GPPercent calculations on adding or amending items
	if ($_SESSION['Items' . $Identifier]->DefaultCurrency != $_SESSION['CompanyRecord']['currencydefault']) {
		$ExRateResult = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['Items' . $Identifier]->DefaultCurrency . "'");
		if (DB_num_rows($ExRateResult) > 0) {
			$ExRateRow = DB_fetch_row($ExRateResult);
			$ExRate = $ExRateRow[0];
		} //DB_num_rows($ExRateResult) > 0
		else {
			$ExRate = 1;
		}
	} //$_SESSION['Items' . $Identifier]->DefaultCurrency != $_SESSION['CompanyRecord']['currencydefault']
	else {
		$ExRate = 1;
	}

	/*Process Quick Entry */
	/* If enter is pressed on the quick entry screen, the default button may be Recalculate */

	if (isset($_POST['SelectingOrderItems']) or isset($_POST['QuickEntry']) or isset($_POST['Recalculate'])) {
		/* get the item details from the database and hold them in the cart object */

		/*Discount can only be set later on  -- after quick entry -- so default discount to 0 in the first place */
		$AlreadyWarnedAboutCredit = false;
		$i = 1;
		while ($i <= $_SESSION['QuickEntries'] and isset($_POST['part_' . $i]) and $_POST['part_' . $i] != '') {
			$QuickEntryCode = 'part_' . $i;
			$QuickEntryQty = 'qty_' . $i;
			$QuickEntryPOLine = 'poline_' . $i;
			$QuickEntryItemDue = 'itemdue_' . $i;

			++$i;

			if (isset($_POST[$QuickEntryCode])) {
				$NewItem = mb_strtoupper($_POST[$QuickEntryCode]);
			} //isset($_POST[$QuickEntryCode])
			if (isset($_POST[$QuickEntryQty])) {
				$NewItemQty = filter_number_format($_POST[$QuickEntryQty]);
			} //isset($_POST[$QuickEntryQty])
			if (isset($_POST[$QuickEntryItemDue])) {
				$NewItemDue = $_POST[$QuickEntryItemDue];
			} //isset($_POST[$QuickEntryItemDue])
			else {
				$NewItemDue = DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', $_SESSION['Items' . $Identifier]->DeliveryDays);
			}
			if (isset($_POST[$QuickEntryPOLine])) {
				$NewPOLine = $_POST[$QuickEntryPOLine];
			} //isset($_POST[$QuickEntryPOLine])
			else {
				$NewPOLine = 0;
			}

			if (!isset($NewItem)) {
				unset($NewItem);
				break;
				/* break out of the loop if nothing in the quick entry fields*/
			} //!isset($NewItem)

			if (!is_date($NewItemDue)) {
				prnMsg(_('An invalid date entry was made for ') . ' ' . $NewItem . ' ' . _('The date entry') . ' ' . $NewItemDue . ' ' . _('must be in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'warn');
				//Attempt to default the due date to something sensible?
				$NewItemDue = DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', $_SESSION['Items' . $Identifier]->DeliveryDays);
			} //!is_date($NewItemDue)

			/*Now figure out if the item is a kit set - the field MBFlag='K'*/
			$SQL = "SELECT stockmaster.mbflag
					FROM stockmaster
					WHERE stockmaster.stockid='" . $NewItem . "'";

			$ErrMsg = _('Could not determine if the part being ordered was a kitset or not because');
			$DbgMsg = _('The sql that was used to determine if the part being ordered was a kitset or not was ');
			$KitResult = DB_query($SQL, $ErrMsg, $DbgMsg);

			if (DB_num_rows($KitResult) == 0) {
				prnMsg(_('The item code') . ' ' . $NewItem . ' ' . _('could not be retrieved from the database and has not been added to the order'), 'warn');
			} //DB_num_rows($KitResult) == 0
			elseif ($MyRow = DB_fetch_array($KitResult)) {
				if ($MyRow['mbflag'] == 'K') {
					/*It is a kit set item */
					$SQL = "SELECT bom.component,
							bom.quantity
							FROM bom
							WHERE bom.parent='" . $NewItem . "'
							AND bom.effectiveto > CURRENT_DATE
							AND bom.effectiveafter <= CURRENT_DATE";

					$ErrMsg = _('Could not retrieve kitset components from the database because') . ' ';
					$KitResult = DB_query($SQL, $ErrMsg, $DbgMsg);

					$ParentQty = $NewItemQty;
					while ($KitParts = DB_fetch_array($KitResult)) {
						$NewItem = $KitParts['component'];
						$NewItemQty = $KitParts['quantity'] * $ParentQty;
						$NewPOLine = 0;
						include ('includes/SelectOrderItems_IntoCart.inc');
					} //$KitParts = DB_fetch_array($KitResult)

				} //$MyRow['mbflag'] == 'K'
				elseif ($MyRow['mbflag'] == 'G') {
					prnMsg(_('Phantom assemblies cannot be sold, these items exist only as bills of materials used in other manufactured items. The following item has not been added to the order') . ': ' . $NewItem, 'warn');
				} //$MyRow['mbflag'] == 'G'
				else {
					/*Its not a kit set item*/
					include ('includes/SelectOrderItems_IntoCart.inc');
				}
			} //$MyRow = DB_fetch_array($KitResult)

		} //$i <= $_SESSION['QuickEntries'] and isset($_POST['part_' . $i]) and $_POST['part_' . $i] != ''
		unset($NewItem);
	} //isset($_POST['SelectingOrderItems']) or isset($_POST['QuickEntry']) or isset($_POST['Recalculate'])

	/* end of if quick entry */

	if (isset($_POST['AssetDisposalEntered'])) { //its an asset being disposed of
		if ($_POST['AssetToDisposeOf'] == 'NoAssetSelected') { //don't do anything unless an asset is disposed of
			prnMsg(_('No asset was selected to dispose of. No assets have been added to this customer order'), 'warn');
		} //$_POST['AssetToDisposeOf'] == 'NoAssetSelected'
		else { //need to add the asset to the order

			/*First need to create a stock ID to hold the asset and record the sale - as only stock items can be sold
			 * 		and before that we need to add a disposal stock category - if not already created
			 * 		first off get the details about the asset being disposed of */
			$AssetDetailsResult = DB_query("SELECT  fixedassets.description,
													fixedassets.longdescription,
													fixedassets.barcode,
													fixedassetcategories.costact,
													fixedassets.cost-fixedassets.accumdepn AS nbv
											FROM fixedassetcategories INNER JOIN fixedassets
											ON fixedassetcategories.categoryid=fixedassets.assetcategoryid
											WHERE fixedassets.assetid='" . $_POST['AssetToDisposeOf'] . "'");
			$AssetRow = DB_fetch_array($AssetDetailsResult);

			/* Check that the stock category for disposal "ASSETS" is defined already */
			$AssetCategoryResult = DB_query("SELECT categoryid FROM stockcategory WHERE categoryid='ASSETS'");
			if (DB_num_rows($AssetCategoryResult) == 0) {
				/*Although asset GL posting will come from the asset category - we should set the GL codes to something sensible
				 * based on the category of the asset under review at the moment - this may well change for any other assets sold subsequentely */

				/*OK now we can insert the stock category for this asset */
				$InsertAssetStockCatResult = DB_query("INSERT INTO stockcategory ( categoryid,
																				categorydescription,
																				stockact)
														VALUES ('ASSETS',
																'" . _('Asset Disposals') . "',
																'" . $AssetRow['costact'] . "')");
			} //DB_num_rows($AssetCategoryResult) == 0

			/*First check to see that it doesn't exist already assets are of the format "ASSET-" . $AssetID
			*/
			$TestAssetExistsAlreadyResult = DB_query("SELECT stockid
														FROM stockmaster
														WHERE stockid ='ASSET-" . $_POST['AssetToDisposeOf'] . "'");
			$j = 0;
			while (DB_num_rows($TestAssetExistsAlreadyResult) == 1) { //then it exists already ... bum
				++$j;
				$TestAssetExistsAlreadyResult = DB_query("SELECT stockid
														FROM stockmaster
														WHERE stockid ='ASSET-" . $_POST['AssetToDisposeOf'] . '-' . $j . "'");
			} //DB_num_rows($TestAssetExistsAlreadyResult) == 1
			if ($j > 0) {
				$AssetStockID = 'ASSET-' . $_POST['AssetToDisposeOf'] . '-' . $j;
			} //$j > 0
			else {
				$AssetStockID = 'ASSET-' . $_POST['AssetToDisposeOf'];
			}
			if ($AssetRow['nbv'] == 0) {
				$NBV = 0.001;
				/* stock must have a cost to be invoiced if the flag is set so set to 0.001 */
			} //$AssetRow['nbv'] == 0
			else {
				$NBV = $AssetRow['nbv'];
			}
			/*OK now we can insert the item for this asset */
			$InsertAssetAsStockItemSQL = "INSERT INTO stockmaster ( stockid,
																				description,
																				longdescription,
																				categoryid,
																				mbflag,
																				controlled,
																				serialised,
																				taxcatid)
										VALUES ('" . $AssetStockID . "',
												'" . DB_escape_string($AssetRow['description']) . "',
												'" . DB_escape_string($AssetRow['longdescription']) . "',
												'ASSETS',
												'D',
												'0',
												'0',
												'" . $_SESSION['DefaultTaxCategory'] . "')";
			$InsertAssetAsStockItemResult = DB_query($InsertAssetAsStockItemSQL);

			$InserAssetCostsSQL = "INSERT INTO stockcosts VALUES('" . $AssetStockID . "',
																'" . $NBV . "',
																0,
																0,
																CURRENT_TIME,
																0)";
			$InsertAssetCostsResult = DB_query($InserAssetCostsSQL);

			/*not forgetting the location records too */
			$InsertStkLocRecsResult = DB_query("INSERT INTO locstock (loccode,
																	stockid)
												SELECT loccode, '" . $AssetStockID . "'
												FROM locations");
			/*Now the asset has been added to the stock master we can add it to the sales order */
			$NewItemDue = date($_SESSION['DefaultDateFormat']);
			if (isset($_POST['POLine'])) {
				$NewPOLine = $_POST['POLine'];
			} //isset($_POST['POLine'])
			else {
				$NewPOLine = 0;
			}
			$NewItem = $AssetStockID;
			include ('includes/SelectOrderItems_IntoCart.inc');
		} //end if adding a fixed asset to the order

	} //end if the fixed asset selection box was set

	/*Now do non-quick entry delete/edits/adds */

	if ((isset($_SESSION['Items' . $Identifier])) or isset($NewItem)) {
		if (isset($_GET['Delete'])) {
			//page called attempting to delete a line - GET['Delete'] = the line number to delete
			$QuantityAlreadyDelivered = $_SESSION['Items' . $Identifier]->Some_Already_Delivered($_GET['Delete']);
			if ($QuantityAlreadyDelivered == 0) {
				$_SESSION['Items' . $Identifier]->remove_from_cart($_GET['Delete'], 'Yes', $Identifier);
				/*Do update DB */
			} //$QuantityAlreadyDelivered == 0
			else {
				$_SESSION['Items' . $Identifier]->LineItems[$_GET['Delete']]->Quantity = $QuantityAlreadyDelivered;
			}
		} //isset($_GET['Delete'])

		$AlreadyWarnedAboutCredit = false;

		foreach ($_SESSION['Items' . $Identifier]->LineItems as $OrderLine) {
			if (isset($_POST['Quantity_' . $OrderLine->LineNumber])) {
				$Quantity = round(filter_number_format($_POST['Quantity_' . $OrderLine->LineNumber]), $OrderLine->DecimalPlaces);

				if (ABS($OrderLine->Price - filter_number_format($_POST['Price_' . $OrderLine->LineNumber])) > 0.01) {
					/*There is a new price being input for the line item */

					$Price = filter_number_format($_POST['Price_' . $OrderLine->LineNumber]);
					$_POST['GPPercent_' . $OrderLine->LineNumber] = (($Price * (1 - (filter_number_format($_POST['Discount_' . $OrderLine->LineNumber]) / 100))) - $OrderLine->StandardCost * $ExRate) / ($Price * (1 - filter_number_format($_POST['Discount_' . $OrderLine->LineNumber]) / 100) / 100);

				} //ABS($OrderLine->Price - filter_number_format($_POST['Price_' . $OrderLine->LineNumber])) > 0.01
				elseif (ABS($OrderLine->GPPercent - filter_number_format($_POST['GPPercent_' . $OrderLine->LineNumber])) >= 0.01) {
					/* A GP % has been input so need to do a recalculation of the price at this new GP Percentage */

					prnMsg(_('Recalculated the price from the GP % entered - the GP % was') . ' ' . $OrderLine->GPPercent . '  the new GP % is ' . filter_number_format($_POST['GPPercent_' . $OrderLine->LineNumber]), 'info');

					$Price = ($OrderLine->StandardCost * $ExRate) / (1 - ((filter_number_format($_POST['GPPercent_' . $OrderLine->LineNumber]) + filter_number_format($_POST['Discount_' . $OrderLine->LineNumber])) / 100));
				} //ABS($OrderLine->GPPercent - filter_number_format($_POST['GPPercent_' . $OrderLine->LineNumber])) >= 0.01
				else {
					$Price = filter_number_format($_POST['Price_' . $OrderLine->LineNumber]);
				}
				$DiscountPercentage = filter_number_format($_POST['Discount_' . $OrderLine->LineNumber]);
				if ($_SESSION['AllowOrderLineItemNarrative'] == 1) {
					$Narrative = $_POST['Narrative_' . $OrderLine->LineNumber];
				} //$_SESSION['AllowOrderLineItemNarrative'] == 1
				else {
					$Narrative = '';
				}

				if (!isset($OrderLine->DiscountPercent)) {
					$OrderLine->DiscountPercent = 0;
				} //!isset($OrderLine->DiscountPercent)

				if (!is_date($_POST['ItemDue_' . $OrderLine->LineNumber])) {
					prnMsg(_('An invalid date entry was made for ') . ' ' . $NewItem . ' ' . _('The date entry') . ' ' . $ItemDue . ' ' . _('must be in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'warn');
					//Attempt to default the due date to something sensible?

					$_POST['ItemDue_' . $OrderLine->LineNumber] = DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', $_SESSION['Items' . $Identifier]->DeliveryDays);
				} //!is_date($_POST['ItemDue_' . $OrderLine->LineNumber])
				if ($Quantity < 0 or $Price < 0 or $DiscountPercentage > 100 or $DiscountPercentage < 0) {
					prnMsg(_('The item could not be updated because you are attempting to set the quantity ordered to less than 0 or the price less than 0 or the discount more than 100% or less than 0%'), 'warn');
				} //$Quantity < 0 or $Price < 0 or $DiscountPercentage > 100 or $DiscountPercentage < 0
				elseif ($_SESSION['Items' . $Identifier]->Some_Already_Delivered($OrderLine->LineNumber) != 0 and $_SESSION['Items' . $Identifier]->LineItems[$OrderLine->LineNumber]->Price != $Price) {
					prnMsg(_('The item you attempting to modify the price for has already had some quantity invoiced at the old price the items unit price cannot be modified retrospectively'), 'warn');
				} //$_SESSION['Items' . $Identifier]->Some_Already_Delivered($OrderLine->LineNumber) != 0 and $_SESSION['Items' . $Identifier]->LineItems[$OrderLine->LineNumber]->Price != $Price
				elseif ($_SESSION['Items' . $Identifier]->Some_Already_Delivered($OrderLine->LineNumber) != 0 and $_SESSION['Items' . $Identifier]->LineItems[$OrderLine->LineNumber]->DiscountPercent != ($DiscountPercentage / 100)) {
					prnMsg(_('The item you attempting to modify has had some quantity invoiced at the old discount percent the items discount cannot be modified retrospectively'), 'warn');

				} //$_SESSION['Items' . $Identifier]->Some_Already_Delivered($OrderLine->LineNumber) != 0 and $_SESSION['Items' . $Identifier]->LineItems[$OrderLine->LineNumber]->DiscountPercent != ($DiscountPercentage / 100)
				elseif ($_SESSION['Items' . $Identifier]->LineItems[$OrderLine->LineNumber]->QtyInv > $Quantity) {
					prnMsg(_('You are attempting to make the quantity ordered a quantity less than has already been invoiced') . '. ' . _('The quantity delivered and invoiced cannot be modified retrospectively'), 'warn');
				} //$_SESSION['Items' . $Identifier]->LineItems[$OrderLine->LineNumber]->QtyInv > $Quantity
				elseif ($OrderLine->Quantity != $Quantity or $OrderLine->Price != $Price or ABS($OrderLine->DiscountPercent - $DiscountPercentage / 100) > 0.001 or $OrderLine->Narrative != $Narrative or $OrderLine->ItemDue != $_POST['ItemDue_' . $OrderLine->LineNumber] or $OrderLine->POLine != $_POST['POLine_' . $OrderLine->LineNumber]) {
					$WithinCreditLimit = true;

					if ($_SESSION['CheckCreditLimits'] > 0 and $AlreadyWarnedAboutCredit == false) {
						/*Check credit limits is 1 for warn breach their credit limit and 2 for prohibit sales */
						$DifferenceInOrderValue = ($Quantity * $Price * (1 - $DiscountPercentage / 100)) - ($OrderLine->Quantity * $OrderLine->Price * (1 - $OrderLine->DiscountPercent));

						$_SESSION['Items' . $Identifier]->CreditAvailable-= $DifferenceInOrderValue;

						if ($_SESSION['CheckCreditLimits'] == 1 and $_SESSION['Items' . $Identifier]->CreditAvailable <= 0) {
							prnMsg(_('The customer account will breach their credit limit'), 'warn');
							$AlreadyWarnedAboutCredit = true;
						} //$_SESSION['CheckCreditLimits'] == 1 and $_SESSION['Items' . $Identifier]->CreditAvailable <= 0
						elseif ($_SESSION['CheckCreditLimits'] == 2 and $_SESSION['Items' . $Identifier]->CreditAvailable <= 0) {
							prnMsg(_('This change would put the customer over their credit limit and is prohibited'), 'warn');
							$WithinCreditLimit = false;
							$_SESSION['Items' . $Identifier]->CreditAvailable+= $DifferenceInOrderValue;
							$AlreadyWarnedAboutCredit = true;
						} //$_SESSION['CheckCreditLimits'] == 2 and $_SESSION['Items' . $Identifier]->CreditAvailable <= 0

					} //$_SESSION['CheckCreditLimits'] > 0 and $AlreadyWarnedAboutCredit == false

					/* The database data will be updated at this step, it will make big mistake if users do not know this and change the quantity to zero, unfortuately, the appearance shows that this change not allowed but the sales order details' quantity has been changed to zero in database. Must to filter this out! A zero quantity order line means nothing */
					if ($WithinCreditLimit and $Quantity > 0) {
						$_SESSION['Items' . $Identifier]->update_cart_item($OrderLine->LineNumber, $Quantity, $Price, ($DiscountPercentage / 100), $Narrative, 'Yes', /*Update DB */
						$_POST['ItemDue_' . $OrderLine->LineNumber], $_POST['POLine_' . $OrderLine->LineNumber], filter_number_format($_POST['GPPercent_' . $OrderLine->LineNumber]), $Identifier);
					} //within credit limit so make changes

				} //there are changes to the order line to process

			} //page not called from itself - POST variables not set

		} // Loop around all items on the order

		/* Now Run through each line of the order again to work out the appropriate discount from the discount matrix */
		$DiscCatsDone = array();

		foreach ($_SESSION['Items' . $Identifier]->LineItems as $OrderLine) {
			if ($OrderLine->DiscCat != '' and !in_array($OrderLine->DiscCat, $DiscCatsDone)) {
				$DiscCatsDone[] = $OrderLine->DiscCat;
				$QuantityOfDiscCat = 0;

				foreach ($_SESSION['Items' . $Identifier]->LineItems as $OrderLine_2) {
					/* add up total quantity of all lines of this DiscCat */
					if ($OrderLine_2->DiscCat == $OrderLine->DiscCat) {
						$QuantityOfDiscCat+= $OrderLine_2->Quantity;
					} //$OrderLine_2->DiscCat == $OrderLine->DiscCat

				} //$_SESSION['Items' . $Identifier]->LineItems as $OrderLine_2
				$Result = DB_query("SELECT MAX(discountrate) AS discount
									FROM discountmatrix
									WHERE salestype='" . $_SESSION['Items' . $Identifier]->DefaultSalesType . "'
									AND discountcategory ='" . $OrderLine->DiscCat . "'
									AND quantitybreak <= '" . $QuantityOfDiscCat . "'");
				$MyRow = DB_fetch_row($Result);
				if ($MyRow[0] == NULL) {
					$DiscountMatrixRate = 0;
				} //$MyRow[0] == NULL
				else {
					$DiscountMatrixRate = $MyRow[0];
				}
				if ($DiscountMatrixRate != 0) {
					/* need to update the lines affected */
					foreach ($_SESSION['Items' . $Identifier]->LineItems as $OrderLine_2) {
						if ($OrderLine_2->DiscCat == $OrderLine->DiscCat) {
							$_SESSION['Items' . $Identifier]->LineItems[$OrderLine_2->LineNumber]->DiscountPercent = $DiscountMatrixRate;
							$_SESSION['Items' . $Identifier]->LineItems[$OrderLine_2->LineNumber]->GPPercent = (($_SESSION['Items' . $Identifier]->LineItems[$OrderLine_2->LineNumber]->Price * (1 - $DiscountMatrixRate)) - $_SESSION['Items' . $Identifier]->LineItems[$OrderLine_2->LineNumber]->StandardCost * $ExRate) / ($_SESSION['Items' . $Identifier]->LineItems[$OrderLine_2->LineNumber]->Price * (1 - $DiscountMatrixRate) / 100);
						} //$OrderLine_2->DiscCat == $OrderLine->DiscCat

					} //$_SESSION['Items' . $Identifier]->LineItems as $OrderLine_2

				} //$MyRow[0] != 0

			} //$OrderLine->DiscCat != '' and !in_array($OrderLine->DiscCat, $DiscCatsDone)

		} //$_SESSION['Items' . $Identifier]->LineItems as $OrderLine

		/* end of discount matrix lookup code */
	} // the order session is started or there is a new item being added
	if (isset($_POST['DeliveryDetails'])) {
		echo '<meta http-equiv="refresh" content="0; url=' . $RootPath . '/DeliveryDetails.php?identifier=' . $Identifier . '">';
		prnMsg(_('You should automatically be forwarded to the entry of the delivery details page') . '. ' . _('if this does not happen') . ' (' . _('if the browser does not support META Refresh') . ') ' . '<a href="' . $RootPath . '/DeliveryDetails.php?identifier=' . urlencode($Identifier) . '">' . _('click here') . '</a> ' . _('to continue'), 'info');
		exit;
	} //isset($_POST['DeliveryDetails'])

	if (isset($NewItem)) {
		/* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart */
		/*Now figure out if the item is a kit set - the field MBFlag='K'*/
		$SQL = "SELECT stockmaster.mbflag
		   		FROM stockmaster
				WHERE stockmaster.stockid='" . $NewItem . "'";

		$ErrMsg = _('Could not determine if the part being ordered was a kitset or not because');

		$KitResult = DB_query($SQL, $ErrMsg);

		$NewItemQty = 1;
		/*By Default */
		$Discount = 0;
		/*By default - can change later or discount category override */

		if ($MyRow = DB_fetch_array($KitResult)) {
			if ($MyRow['mbflag'] == 'K') {
				/*It is a kit set item */
				$SQL = "SELECT bom.component,
							bom.quantity
						FROM bom
						WHERE bom.parent='" . $NewItem . "'
						AND bom.effectiveto > CURRENT_DATE
						AND bom.effectiveafter <= CURRENT_DATE";

				$ErrMsg = _('Could not retrieve kitset components from the database because');
				$KitResult = DB_query($SQL, $ErrMsg);

				$ParentQty = $NewItemQty;
				while ($KitParts = DB_fetch_array($KitResult)) {
					$NewItem = $KitParts['component'];
					$NewItemQty = $KitParts['quantity'] * $ParentQty;
					$NewPOLine = 0;
					$NewItemDue = date($_SESSION['DefaultDateFormat']);
					include ('includes/SelectOrderItems_IntoCart.inc');
				} //$KitParts = DB_fetch_array($KitResult)

			} //$MyRow['mbflag'] == 'K'
			else {
				/*Its not a kit set item*/
				$NewItemDue = date($_SESSION['DefaultDateFormat']);
				$NewPOLine = 0;

				include ('includes/SelectOrderItems_IntoCart.inc');
			}

		} //$MyRow = DB_fetch_array($KitResult)

		/* end of if its a new item */

	} //isset($NewItem)

	if (isset($NewItemArray) and isset($_POST['SelectingOrderItems'])) {
		/* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart */
		/*Now figure out if the item is a kit set - the field MBFlag='K'*/
		$AlreadyWarnedAboutCredit = false;
		foreach ($NewItemArray as $NewItem => $NewItemQty) {
			if ($NewItemQty > 0) {
				$SQL = "SELECT stockmaster.mbflag
						FROM stockmaster
						WHERE stockmaster.stockid='" . $NewItem . "'";

				$ErrMsg = _('Could not determine if the part being ordered was a kitset or not because');

				$KitResult = DB_query($SQL, $ErrMsg);

				//$NewItemQty = 1; /*By Default */
				$Discount = 0;
				/*By default - can change later or discount category override */

				if ($MyRow = DB_fetch_array($KitResult)) {
					if ($MyRow['mbflag'] == 'K') {
						/*It is a kit set item */
						$SQL = "SELECT bom.component,
										bom.quantity
								FROM bom
								WHERE bom.parent='" . $NewItem . "'
								AND bom.effectiveto > CURRENT_DATE
								AND bom.effectiveafter <= CURRENT_DATE";

						$ErrMsg = _('Could not retrieve kitset components from the database because');
						$KitResult = DB_query($SQL, $ErrMsg);

						$ParentQty = $NewItemQty;
						while ($KitParts = DB_fetch_array($KitResult)) {
							$NewItem = $KitParts['component'];
							$NewItemQty = $KitParts['quantity'] * $ParentQty;
							$NewItemDue = date($_SESSION['DefaultDateFormat']);
							$NewPOLine = 0;
							include ('includes/SelectOrderItems_IntoCart.inc');
						} //$KitParts = DB_fetch_array($KitResult)

					} //$MyRow['mbflag'] == 'K'
					else {
						/*Its not a kit set item*/
						$NewItemDue = date($_SESSION['DefaultDateFormat']);
						$NewPOLine = 0;
						include ('includes/SelectOrderItems_IntoCart.inc');
					}
				} //$MyRow = DB_fetch_array($KitResult)

				/* end of if its a new item */
			} //$NewItemQty > 0

			/*end of if its a new item */
		} //$NewItemArray as $NewItem => $NewItemQty

		/* loop through NewItem array */
	} //isset($NewItemArray) and isset($_POST['SelectingOrderItems'])

	/* if the NewItem_array is set */

	/* Run through each line of the order and work out the appropriate discount from the discount matrix */
	$DiscCatsDone = array();
	$Counter = 0;
	foreach ($_SESSION['Items' . $Identifier]->LineItems as $OrderLine) {
		if ($OrderLine->DiscCat != "" and !in_array($OrderLine->DiscCat, $DiscCatsDone)) {
			$DiscCatsDone[$Counter] = $OrderLine->DiscCat;
			$QuantityOfDiscCat = 0;

			foreach ($_SESSION['Items' . $Identifier]->LineItems as $StkItems_2) {
				/* add up total quantity of all lines of this DiscCat */
				if ($StkItems_2->DiscCat == $OrderLine->DiscCat) {
					$QuantityOfDiscCat+= $StkItems_2->Quantity;
				} //$StkItems_2->DiscCat == $OrderLine->DiscCat

			} //$_SESSION['Items' . $Identifier]->LineItems as $StkItems_2
			$Result = DB_query("SELECT MAX(discountrate) AS discount
								FROM discountmatrix
								WHERE salestype='" . $_SESSION['Items' . $Identifier]->DefaultSalesType . "'
								AND discountcategory ='" . $OrderLine->DiscCat . "'
								AND quantitybreak <= '" . $QuantityOfDiscCat . "'");
			$MyRow = DB_fetch_row($Result);
			if ($MyRow[0] == NULL) {
				$DiscountMatrixRate = 0;
			} //$MyRow[0] == NULL
			else {
				$DiscountMatrixRate = $MyRow[0];
			}
			if ($DiscountMatrixRate != 0) {
				foreach ($_SESSION['Items'.$Identifier]->LineItems as $StkItems_2) {
					if ($StkItems_2->DiscCat == $OrderLine->DiscCat) {
						$_SESSION['Items' . $Identifier]->LineItems[$StkItems_2->LineNumber]->DiscountPercent = $DiscountMatrixRate;
					}
				} //$StkItems_2->DiscCat == $OrderLine->DiscCat

			} //$_SESSION['Items' . $Identifier]->LineItems as $StkItems_2

		} //$OrderLine->DiscCat != "" and !in_array($OrderLine->DiscCat, $DiscCatsDone)

	} //$_SESSION['Items' . $Identifier]->LineItems as $OrderLine

	/* end of discount matrix lookup code */

	if (count($_SESSION['Items' . $Identifier]->LineItems) > 0) {
		/*only show order lines if there are any */

		/* This is where the order as selected should be displayed  reflecting any deletions or insertions*/
	 	if($_SESSION['Items' . $Identifier]->DefaultPOLine == 1) {// Does customer require PO Line number by sales order line?
			$ShowPOLine = 1;// Show one additional column:  'PO Line'.
		} else {
			$ShowPOLine = 0;// Do NOT show 'PO Line'.
		}

		if(in_array(1000, $_SESSION['AllowedPageSecurityTokens'])) {//Is it an internal user with appropriate permissions?
			$ShowDiscountGP = 2;// Show two additional columns: 'Discount' and 'GP %'.
		} else {
			$ShowDiscountGP = 0;// Do NOT show 'Discount' and 'GP %'.
		}

		echo '<div class="page_help_text">' . _('Quantity (required) - Enter the number of units ordered.  Price (required) - Enter the unit price.  Discount (optional) - Enter a percentage discount.  GP% (optional) - Enter a percentage Gross Profit (GP) to add to the unit cost.  Due Date (optional) - Enter a date for delivery.') . '</div><br />';
		echo '<table width="90%" cellpadding="2">
				<tr style="background-color:#800000">';
		if($ShowPOLine) {
			echo '<th>' . _('PO Line') . '</th>';
		} //$_SESSION['Items' . $Identifier]->DefaultPOLine == 1
		echo '<th>' . _('Item Code') . '</th>
				<th>' . _('Item Description') . '</th>
				<th>' . _('Quantity') . '</th>
				<th>' . _('QOH') . '</th>
				<th>' . _('Unit') . '</th>
				<th>' . _('Price') . '</th>';

		if($ShowDiscountGP) {
			echo '<th>' . _('Discount') . '</th>
						<th>' . _('GP %') . '</th>';
		} //in_array(1000, $_SESSION['AllowedPageSecurityTokens'])
		echo '<th>' . _('Total') . '</th>
			<th>' . _('Due Date') . '</th>
			<th>&nbsp;</th>
		</tr>';

		$_SESSION['Items' . $Identifier]->total = 0;
		$_SESSION['Items' . $Identifier]->totalVolume = 0;
		$_SESSION['Items' . $Identifier]->totalWeight = 0;
		$k = 0; //row colour counter
		foreach ($_SESSION['Items' . $Identifier]->LineItems as $OrderLine) {
			$LineTotal = $OrderLine->Quantity * $OrderLine->Price * (1 - $OrderLine->DiscountPercent);
			$DisplayLineTotal = locale_number_format($LineTotal, $_SESSION['Items' . $Identifier]->CurrDecimalPlaces);
			$DisplayDiscount = locale_number_format(($OrderLine->DiscountPercent * 100), 2);
			$QtyOrdered = $OrderLine->Quantity;
			$QtyRemain = $QtyOrdered - $OrderLine->QtyInv;

			if ($OrderLine->QOHatLoc < $OrderLine->Quantity and ($OrderLine->MBflag == 'B' or $OrderLine->MBflag == 'M')) {
				/*There is a stock deficiency in the stock location selected */
				$RowStarter = '<tr style="background-color:#EEAABB">'; //rows show red where stock deficiency

			} //$OrderLine->QOHatLoc < $OrderLine->Quantity and ($OrderLine->MBflag == 'B' or $OrderLine->MBflag == 'M')
			elseif ($k == 1) {
				$RowStarter = '<tr class="OddTableRows">';
				$k = 0;
			} //$k == 1
			else {
				$RowStarter = '<tr class="EvenTableRows">';
				$k = 1;
			}

			echo $RowStarter;
			echo '<td>';
			if($ShowPOLine) {// Show the input field only if required.
				echo '<input maxlength="20" name="POLine_' . $OrderLine->LineNumber . '" size="20" type="text" value="' . $OrderLine->POLine . '" /></td><td>';
			} else {
				echo '<input name="POLine_' . $OrderLine->LineNumber . '" type="hidden" value="" />';
			}

			echo '<a href="' . $RootPath . '/StockStatus.php?identifier=' . urlencode($Identifier) . '&amp;StockID=' . urlencode($OrderLine->StockID) . '&amp;DebtorNo=' . urlencode($_SESSION['Items' . $Identifier]->DebtorNo) . '" target="_blank">' . $OrderLine->StockID . '</a></td>
				<td title="' . $OrderLine->LongDescription . '">' . $OrderLine->ItemDescription . '</td>';

			echo '<td><input class="number" tabindex="2" type="text" name="Quantity_' . $OrderLine->LineNumber . '" size="6" required="required" maxlength="11" value="' . locale_number_format($OrderLine->Quantity, $OrderLine->DecimalPlaces) . '" />';
			if ($QtyRemain != $QtyOrdered) {
				echo '<br />' . locale_number_format($OrderLine->QtyInv, $OrderLine->DecimalPlaces) . ' ' . _('of') . ' ' . locale_number_format($OrderLine->Quantity, $OrderLine->DecimalPlaces) . ' ' . _('invoiced');
			} //$QtyRemain != $QtyOrdered
			echo '</td>
					<td class="number">' . locale_number_format($OrderLine->QOHatLoc, $OrderLine->DecimalPlaces) . '</td>
					<td>' . $OrderLine->Units . '</td>';

			if (in_array(1000, $_SESSION['AllowedPageSecurityTokens'])) {
				/*OK to display with discount if it is an internal user with appropriate permissions */
				echo '<td><input class="number" type="text" name="Price_' . $OrderLine->LineNumber . '" size="16" required="required" maxlength="16" value="' . locale_number_format($OrderLine->Price, $_SESSION['Items' . $Identifier]->CurrDecimalPlaces) . '" /></td>
					<td><input class="number" type="text" name="Discount_' . $OrderLine->LineNumber . '" size="5" required="required" maxlength="4" value="' . locale_number_format(($OrderLine->DiscountPercent * 100), 2) . '" /></td>
					<td><input class="number" type="text" name="GPPercent_' . $OrderLine->LineNumber . '" size="4" required="required" maxlength="40" value="' . locale_number_format($OrderLine->GPPercent, 2) . '" /></td>';
			} //in_array(1000, $_SESSION['AllowedPageSecurityTokens'])
			else {
				echo '<td class="number">' . locale_number_format($OrderLine->Price, $_SESSION['Items' . $Identifier]->CurrDecimalPlaces);
				echo '<input class="number" type="hidden" name="GPPercent_' . $OrderLine->LineNumber . '" size="4" required="required" maxlength="40" value="' . locale_number_format($OrderLine->GPPercent, 2) . '" />';
				echo '<input type="hidden" name="Price_' . $OrderLine->LineNumber . '" value="' . locale_number_format($OrderLine->Price, $_SESSION['Items' . $Identifier]->CurrDecimalPlaces) . '" /></td>';
			}
			if ($_SESSION['Items' . $Identifier]->Some_Already_Delivered($OrderLine->LineNumber)) {
				$RemTxt = _('Clear Remaining');
			} //$_SESSION['Items' . $Identifier]->Some_Already_Delivered($OrderLine->LineNumber)
			else {
				$RemTxt = _('Delete');
			}
			echo '<td class="number">' . $DisplayLineTotal . '</td>';
			$LineDueDate = $OrderLine->ItemDue;
			if (!is_date($OrderLine->ItemDue)) {
				$LineDueDate = DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', $_SESSION['Items' . $Identifier]->DeliveryDays);
				$_SESSION['Items' . $Identifier]->LineItems[$OrderLine->LineNumber]->ItemDue = $LineDueDate;
			} //!is_date($OrderLine->ItemDue)

			echo '<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ItemDue_' . $OrderLine->LineNumber . '" size="10" required="required" maxlength="10" value="' . $LineDueDate . '" /></td>';

			echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $Identifier . '&amp;Delete=' . $OrderLine->LineNumber . '" onclick="return MakeConfirm(\'' . _('Are You Sure?') . '\', \'Confirm Delete\', this);">' . $RemTxt . '</a></td></tr>';

			if ($_SESSION['AllowOrderLineItemNarrative'] == 1) {
				echo $RowStarter;
				echo '<td colspan="10">' . _('Narrative') . ':<textarea name="Narrative_' . $OrderLine->LineNumber . '" cols="100%" rows="1">' . stripslashes(AddCarriageReturns($OrderLine->Narrative)) . '</textarea><br /></td></tr>';
			} //$_SESSION['AllowOrderLineItemNarrative'] == 1
			else {
				echo '<tr><td><input type="hidden" name="Narrative" value="" /></td></tr>';
			}

			$_SESSION['Items' . $Identifier]->total = $_SESSION['Items' . $Identifier]->total + $LineTotal;
			$_SESSION['Items' . $Identifier]->totalVolume = $_SESSION['Items' . $Identifier]->totalVolume + $OrderLine->Quantity * $OrderLine->Volume;
			$_SESSION['Items' . $Identifier]->totalWeight = $_SESSION['Items' . $Identifier]->totalWeight + $OrderLine->Quantity * $OrderLine->Weight;

		} //$_SESSION['Items' . $Identifier]->LineItems as $OrderLine

		/* end of loop around items */

		$DisplayTotal = locale_number_format($_SESSION['Items' . $Identifier]->total, $_SESSION['Items' . $Identifier]->CurrDecimalPlaces);
		if (in_array(1000, $_SESSION['AllowedPageSecurityTokens'])) {
			$ColSpanNumber = 2;
		} //in_array(1000, $_SESSION['AllowedPageSecurityTokens'])
		else {
			$ColSpanNumber = 1;
		}
		echo '<tr class="EvenTableRows">
				<td class="number" colspan="6"><b>' . _('TOTAL Excl Tax/Freight') . '</b></td>
				<td colspan="' . $ColSpanNumber . '" class="number">' . $DisplayTotal . '</td>
			</tr>
			</table>';

		$DisplayVolume = locale_number_format($_SESSION['Items' . $Identifier]->totalVolume, 2);
		$DisplayWeight = locale_number_format($_SESSION['Items' . $Identifier]->totalWeight, 2);
		echo '<table>
				<tr class="EvenTableRows">
					<td>' . _('Total Weight') . ':</td>
					<td>' . $DisplayWeight . '</td>
					<td>' . _('Total Volume') . ':</td>
					<td>' . $DisplayVolume . '</td>
				</tr>
			</table>';

		echo '<div class="centre">
				<input type="submit" name="Recalculate" value="' . _('Re-Calculate') . '" />
				<input type="submit" name="DeliveryDetails" value="' . _('Enter Delivery Details and Confirm Order') . '" />
			</div>';
	} // end of if lines

	/* Now show the stock item selection search stuff below */

	if ((!isset($_POST['QuickEntry']) and !isset($_POST['SelectAsset']))) {
		echo '<input type="hidden" name="PartSearch" value="' . _('Yes Please') . '" />';

		if ($_SESSION['FrequentlyOrderedItems'] > 0) { //show the Frequently Order Items selection where configured to do so

			// Select the most recently ordered items for quick select
			$SixMonthsAgo = DateAdd(Date($_SESSION['DefaultDateFormat']), 'm', -6);

			$SQL = "SELECT stockmaster.units,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.stockid,
						salesorderdetails.stkcode,
						SUM(qtyinvoiced) salesqty
					FROM `salesorderdetails`INNER JOIN `stockmaster`
					ON  salesorderdetails.stkcode = stockmaster.stockid
					WHERE ActualDispatchDate >= '" . FormatDateForSQL($SixMonthsAgo) . "'
					GROUP BY stkcode
					ORDER BY salesqty DESC
					LIMIT " . $_SESSION['FrequentlyOrderedItems'];

			$Result2 = DB_query($SQL);
			echo '<p class="page_title_text" >
					<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Frequently Ordered Items') . '</p>
					<div class="page_help_text">' . _('Frequently Ordered Items') . _(', shows the most frequently ordered items in the last 6 months.  You can choose from this list, or search further for other items') . '.</div>
					<table class="table1">
						<thead>
							<tr>
								<th class="SortedColumn">' . _('Code') . '</th>
								<th class="SortedColumn">' . _('Description') . '</th>
								<th>' . _('Units') . '</th>
								<th>' . _('On Hand') . '</th>
								<th>' . _('On Demand') . '</th>
								<th>' . _('On Order') . '</th>
								<th>' . _('Available') . '</th>
								<th>' . _('Quantity') . '</th>
							</tr>
						</thead>';
			$i = 0;
			$j = 1;
			$k = 0; //row colour counter
			echo '<tbody>';
			while ($MyRow = DB_fetch_array($Result2)) {
				// This code needs sorting out, but until then :
				$ImageSource = _('No Image');
				// Find the quantity in stock at location
				$QOHSQL = "SELECT sum(locstock.quantity) AS qoh
							FROM locstock
							WHERE stockid='" . $MyRow['stockid'] . "'
							AND loccode = '" . $_SESSION['Items' . $Identifier]->Location . "'";
				$QOHResult = DB_query($QOHSQL);
				$QOHRow = DB_fetch_array($QOHResult);
				$QOH = $QOHRow['qoh'];

				// Find the quantity on outstanding sales orders
				$SQL = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
						FROM salesorderdetails INNER JOIN salesorders
						ON salesorders.orderno = salesorderdetails.orderno
						WHERE salesorders.fromstkloc='" . $_SESSION['Items' . $Identifier]->Location . "'
						AND salesorderdetails.completed=0
						AND salesorders.quotation=0
						AND salesorderdetails.stkcode='" . $MyRow['stockid'] . "'";

				$ErrMsg = _('The demand for this product from') . ' ' . $_SESSION['Items' . $Identifier]->Location . ' ' . _('cannot be retrieved because');
				$DemandResult = DB_query($SQL, $ErrMsg);

				$DemandRow = DB_fetch_row($DemandResult);
				if ($DemandRow[0] != null) {
					$DemandQty = $DemandRow[0];
				} //$DemandRow[0] != null
				else {
					$DemandQty = 0;
				}

				// Get the QOO due to Purchase orders for all locations. Function defined in SQL_CommonFunctions.inc
				$PurchQty = GetQuantityOnOrderDueToPurchaseOrders($MyRow['stockid']);
				// Get the QOO dues to Work Orders for all locations. Function defined in SQL_CommonFunctions.inc
				$WoQty = GetQuantityOnOrderDueToWorkOrders($MyRow['stockid']);

				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} //$k == 1
				else {
					echo '<tr class="OddTableRows">';
					$k = 1;
				}
				$OnOrder = $PurchQty + $WoQty;

				$Available = $QOH - $DemandQty + $OnOrder;

				printf('<td>%s</td>
						<td title="%s">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td><input class="number"  tabindex="' . strval($j + 7) . '" type="text" required="required" maxlength="10" size="6" name="OrderQty' . $i . '" value="0" />
						<input type="hidden" name="StockID' . $i . '" value="' . $MyRow['stockid'] . '" />
						</td>
						</tr>', $MyRow['stockid'], $MyRow['longdescription'], $MyRow['description'], $MyRow['units'], locale_number_format($QOH, $QOHRow['decimalplaces']), locale_number_format($DemandQty, $QOHRow['decimalplaces']), locale_number_format($OnOrder, $QOHRow['decimalplaces']), locale_number_format($Available, $QOHRow['decimalplaces']));
				++$i;
				//end of page full new headings if

			} //$MyRow = DB_fetch_array($Result2)
			//end of while loop for Frequently Ordered Items
			echo '</tbody>';
			echo '<td style="text-align:center" colspan="8">
					<input type="hidden" name="SelectingOrderItems" value="1" />
					<input tabindex="' . strval($j + 8) . '" type="submit" value="' . _('Add to Sales Order') . '" />
				</td>
			</tr>';
			echo '</table>';
		} //end of if Frequently Ordered Items > 0
		echo '<div class="centre">' . $Msg;
		echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ';
		echo _('Search for Order Items') . '</p></div>';
		echo '<div class="page_help_text">' . _('Search for Order Items') . _(', Searches the database for items, you can narrow the results by selecting a stock category, or just enter a partial item description or partial item code') . '.</div><br />';
		echo '<table class="selection">
				<tr>
					<td><b>' . _('Select a Stock Category') . ': </b><select tabindex="1" name="StockCat">';

		if (!isset($_POST['StockCat']) or $_POST['StockCat'] == 'All') {
			echo '<option selected="selected" value="All">' . _('All') . '</option>';
			$_POST['StockCat'] = 'All';
		} //!isset($_POST['StockCat']) or $_POST['StockCat'] == 'All'
		else {
			echo '<option value="All">' . _('All') . '</option>';
		}
		$SQL = "SELECT categoryid,
						categorydescription
				FROM stockcategory
				WHERE stocktype='F' OR stocktype='D' OR stocktype='L'
				ORDER BY categorydescription";

		$Result1 = DB_query($SQL);
		while ($MyRow1 = DB_fetch_array($Result1)) {
			if ($_POST['StockCat'] == $MyRow1['categoryid']) {
				echo '<option selected="selected" value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
			} //$_POST['StockCat'] == $MyRow1['categoryid']
			else {
				echo '<option value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
			}
		} //$MyRow1 = DB_fetch_array($Result1)

		echo '</select></td>

			<td><b>' . _('Enter partial Description') . ':</b><input tabindex="2" type="text" name="Keywords" size="20" maxlength="25" value="';

		if (isset($_POST['Keywords'])) {
			echo $_POST['Keywords'];
		} //isset($_POST['Keywords'])
		echo '" /></td>';

		echo '<td align="right"><b>' . _('OR') . ' ' . _('Enter extract of the Stock Code') . ':</b><input tabindex="3" type="text" autofocus="autofocus" name="StockCode" size="15" maxlength="18" value="';
		if (isset($_POST['StockCode'])) {
			echo $_POST['StockCode'];
		} //isset($_POST['StockCode'])
		echo '" /></td>
				<td><input type="checkbox" name="CustItemFlag" value="C" />' . _('Customer Item flag') . '&nbsp;&nbsp;<br/><span class="dpTbl">' . _('If checked, only items for this customer will show') . '</span> </td>
			</tr>
		</table>';

		echo '<div class="centre">
				<input tabindex="4" type="submit" name="Search" value="' . _('Search Now') . '" />
				<input tabindex="5" type="submit" name="QuickEntry" value="' . _('Use Quick Entry') . '" />
			</div>';

		if (isset($SearchResult)) {
			echo '<div class="page_help_text">' . _('Select an item by entering the quantity required.  Click Order when ready.') . '</div>';
			$j = 1;
			echo '<table class="table1">
					<thead>
						<tr>
							<td colspan="1">
								<input type="hidden" name="PreviousList" value="' . strval($Offset - 1) . '" />
								<input tabindex="' . strval($j + 8) . '" type="submit" name="Previous" value="' . _('Previous') . '" />
							</td>
							<td style="text-align:center" colspan="6">
								<input type="hidden" name="SelectingOrderItems" value="1" />
								<input tabindex="' . strval($j + 9) . '" type="submit" value="' . _('Add to Sales Order') . '" />
							</td>
							<td colspan="1">
								<input type="hidden" name="NextList" value="' . strval($Offset + 1) . '" />
								<input tabindex="' . strval($j + 10) . '" type="submit" name="Next" value="' . _('Next') . '" />
							</td>
						</tr>
						<tr>
							<th class="SortedColumn">' . _('Code') . '</th>
							<th class="SortedColumn">' . _('Description') . '</th>
							<th class="SortedColumn" >' . _('Customer Item') . '</th>
							<th>' . _('Units') . '</th>
							<th>' . _('On Hand') . '</th>
							<th>' . _('On Demand') . '</th>
							<th>' . _('On Order') . '</th>
							<th>' . _('Available') . '</th>
							<th>' . _('Quantity') . '</th>
						</tr>
					</thead>';
			$ImageSource = _('No Image');
			$i = 0;
			$k = 0; //row colour counter
			echo '<tbody>';
			while ($MyRow = DB_fetch_array($SearchResult)) {
				// Find the quantity in stock at location
				$QOHSQL = "SELECT quantity AS qoh,
									stockmaster.decimalplaces
							   FROM locstock INNER JOIN stockmaster
							   ON locstock.stockid = stockmaster.stockid
							   WHERE locstock.stockid='" . $MyRow['stockid'] . "' AND
							   loccode = '" . $_SESSION['Items' . $Identifier]->Location . "'";
				$QOHResult = DB_query($QOHSQL);
				$QOHRow = DB_fetch_array($QOHResult);
				$QOH = $QOHRow['qoh'];

				// Find the quantity on outstanding sales orders
				$SQL = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
						FROM salesorderdetails INNER JOIN salesorders
						ON salesorders.orderno = salesorderdetails.orderno
						 WHERE  salesorders.fromstkloc='" . $_SESSION['Items' . $Identifier]->Location . "'
						 AND salesorderdetails.completed=0
						 AND salesorders.quotation=0
						 AND salesorderdetails.stkcode='" . $MyRow['stockid'] . "'";

				$ErrMsg = _('The demand for this product from') . ' ' . $_SESSION['Items' . $Identifier]->Location . ' ' . _('cannot be retrieved because');
				$DemandResult = DB_query($SQL, $ErrMsg);

				$DemandRow = DB_fetch_row($DemandResult);
				if ($DemandRow[0] != null) {
					$DemandQty = $DemandRow[0];
				} //$DemandRow[0] != null
				else {
					$DemandQty = 0;
				}

				// Get the QOO due to Purchase orders for all locations. Function defined in SQL_CommonFunctions.inc
				$PurchQty = GetQuantityOnOrderDueToPurchaseOrders($MyRow['stockid']);
				// Get the QOO dues to Work Orders for all locations. Function defined in SQL_CommonFunctions.inc
				$WoQty = GetQuantityOnOrderDueToWorkOrders($MyRow['stockid']);

				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} //$k == 1
				else {
					echo '<tr class="OddTableRows">';
					$k = 1;
				}
				$OnOrder = $PurchQty + $WoQty;
				$Available = $QOH - $DemandQty + $OnOrder;

				printf('<td>%s</td>
						<td title="%s">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td><input class="number"  tabindex="' . strval($j + 7) . '" type="text" size="6" required="required" maxlength="10" name="OrderQty' . $i . '" value="0" />
						<input type="hidden" name="StockID' . $i . '" value="' . $MyRow['stockid'] . '" />
						</td>
						</tr>', $MyRow['stockid'], $MyRow['longdescription'], $MyRow['description'], $MyRow['cust_part'] . '-' . $MyRow['cust_description'], $MyRow['units'], locale_number_format($QOH, $QOHRow['decimalplaces']), locale_number_format($DemandQty, $QOHRow['decimalplaces']), locale_number_format($OnOrder, $QOHRow['decimalplaces']), locale_number_format($Available, $QOHRow['decimalplaces']));
				++$i;
				//end of page full new headings if

			} //$MyRow = DB_fetch_array($SearchResult)
			//end of while loop
			echo '</tbody><tr><td><input type="hidden" name="PreviousList" value="' . strval($Offset - 1) . '" /><input tabindex="' . strval($j + 7) . '" type="submit" name="Previous" value="' . _('Previous') . '" /></td>';
			echo '<td style="text-align:center" colspan="6"><input type="hidden" name="SelectingOrderItems" value="1" /><input tabindex="' . strval($j + 8) . '" type="submit" value="' . _('Add to Sales Order') . '" /></td>';
			echo '<td><input type="hidden" name="NextList" value="' . strval($Offset + 1) . '" /><input tabindex="' . strval($j + 9) . '" type="submit" name="Next" value="' . _('Next') . '" /></td></tr>';
			echo '</table>';

		} //end if SearchResults to show

	} //(!isset($_POST['QuickEntry']) and !isset($_POST['SelectAsset']))

	/*end of PartSearch options to be displayed */
	elseif (isset($_POST['QuickEntry'])) {
		/* show the quick entry form variable */
		/*FORM VARIABLES TO POST TO THE ORDER  WITH PART CODE AND QUANTITY */
		echo '<div class="page_help_text"><b>' . _('Use this screen for the ') . _('Quick Entry') . _(' of products to be ordered') . '</b></div><br />
		 			<table border="1">
					<tr>';
		/*do not display colum unless customer requires po line number by sales order line*/
		if ($_SESSION['Items' . $Identifier]->DefaultPOLine == 1) {
			echo '<th>' . _('PO Line') . '</th>';
		} //$_SESSION['Items' . $Identifier]->DefaultPOLine == 1
		echo '<th>' . _('Part Code') . '</th>
				<th>' . _('Quantity') . '</th>
				<th>' . _('Due Date') . '</th>
			</tr>';
		$DefaultDeliveryDate = DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', $_SESSION['Items' . $Identifier]->DeliveryDays);
		for ($i = 1;$i <= $_SESSION['QuickEntries'];$i++) {
			echo '<tr class="OddTableRow">';
			/* Do not display colum unless customer requires po line number by sales order line*/
			if ($_SESSION['Items' . $Identifier]->DefaultPOLine > 0) {
				echo '<td><input type="text" name="poline_' . $i . '" size="21" maxlength="20" /></td>';
			} //$_SESSION['Items' . $Identifier]->DefaultPOLine > 0
			echo '<td><input type="text" name="part_' . $i . '" size="21" maxlength="20" /></td>
					<td><input type="text" name="qty_' . $i . '" size="6" maxlength="6" /></td>
					<td><input type="text" class="date" name="itemdue_' . $i . '" size="25" maxlength="25" alt="' . $_SESSION['DefaultDateFormat'] . '" value="' . $DefaultDeliveryDate . '" /></td></tr>';
		} //$i = 1; $i <= $_SESSION['QuickEntries']; $i++
		echo '</table>';

		echo '<div class="centre"><input type="submit" name="QuickEntry" value="' . _('Quick Entry') . '" />
				<input type="submit" name="PartSearch" value="' . _('Search Parts') . '" />
			</div>';

		echo '</form>';
	} //isset($_POST['QuickEntry'])
	elseif (isset($_POST['SelectAsset'])) {
		echo '<div class="page_help_text"><b>' . _('Use this screen to select an asset to dispose of to this customer') . '</b></div>
		 			<table border="1">';
		/*do not display colum unless customer requires po line number by sales order line*/
		if ($_SESSION['Items' . $Identifier]->DefaultPOLine == 1) {
			echo '<tr><td>' . _('PO Line') . '</td>
							<td><input type="text" name="poline" size="21" maxlength="20" /></td></tr>';
		} //$_SESSION['Items' . $Identifier]->DefaultPOLine == 1
		echo '<tr>
				<td>' . _('Asset to Dispose Of') . ':</td>
				<td><select name="AssetToDisposeOf">';
		$AssetsResult = DB_query("SELECT assetid, description FROM fixedassets WHERE disposaldate='0000-00-00'");
		echo '<option selected="selected" value="NoAssetSelected">' . _('Select Asset To Dispose of From the List Below') . '</option>';
		while ($AssetRow = DB_fetch_array($AssetsResult)) {
			echo '<option value="' . $AssetRow['assetid'] . '">' . $AssetRow['assetid'] . ' - ' . $AssetRow['description'] . '</option>';
		} //$AssetRow = DB_fetch_array($AssetsResult)
		echo '</select>
				</td>
			</tr>
		</table>';
		echo '<div class="centre">
				<input type="submit" name="AssetDisposalEntered" value="' . _('Add Asset To Order') . '" />
				<input type="submit" name="PartSearch" value="' . _('Search Parts') . '" />
			</div>';

		echo '</form>';

	} //end of if it is a Quick Entry screen/part search or asset selection form to display

	if ($_SESSION['Items' . $Identifier]->ItemsOrdered >= 1) {
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $Identifier . '" method="post" name="deleteform">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<div class="centre">
				<input type="submit" name="CancelOrder" value="' . _('Cancel Whole Order') . '" onclick="return MakeConfirm(\'' . _('Are you sure you wish to cancel this entire order?') . '\');" />
			</div>
		</form>';
	} //$_SESSION['Items' . $Identifier]->ItemsOrdered >= 1

} //end of else not selecting a customer

include ('includes/footer.inc');

function GetCustBranchDetails($Identifier) {
	$SQL = "SELECT custbranch.brname,
						custbranch.branchcode,
						custbranch.braddress1,
						custbranch.braddress2,
						custbranch.braddress3,
						custbranch.braddress4,
						custbranch.braddress5,
						custbranch.braddress6,
						custbranch.phoneno,
						custbranch.email,
						custbranch.defaultlocation,
						custbranch.defaultshipvia,
						custbranch.deliverblind,
						custbranch.specialinstructions,
						custbranch.estdeliverydays,
						locations.locationname,
						custbranch.salesman
					FROM custbranch
					INNER JOIN locations
					ON custbranch.defaultlocation=locations.loccode
					WHERE custbranch.branchcode='" . $_SESSION['Items' . $Identifier]->Branch . "'
					AND custbranch.debtorno = '" . $_SESSION['Items' . $Identifier]->DebtorNo . "'";

	$ErrMsg = _('The customer branch record of the customer selected') . ': ' . $_SESSION['Items' . $Identifier]->DebtorNo . ' ' . _('cannot be retrieved because');
	$DbgMsg = _('SQL used to retrieve the branch details was') . ':';
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
	return $Result;
}
?>