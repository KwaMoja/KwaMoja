<?php
// SpecialOrder.php
// Allows for a sales order to be created and an indent order to be created on a supplier for a one off item that may never be purchased again. A dummy part is created based on the description and cost details given.
include ('includes/DefineSpecialOrderClass.php');
/* Session started in header.php for password checking and authorisation level check */
include ('includes/session.php');
include ('includes/SQL_CommonFunctions.php');

$ViewTopic = '';
$BookMark = 'SpecialOrder';
$Title = _('Special Order Entry');

include ('includes/header.php');

if (empty($_GET['identifier'])) {
	/*unique session identifier to ensure that there is no conflict with other supplier tender sessions on the same machine  */
	$Identifier = date('U');
} else {
	$Identifier = $_GET['identifier'];
}

echo '<form action="', basename(__FILE__), '?identifier=', urlencode($Identifier), '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

if (isset($_GET['NewSpecial']) and $_GET['NewSpecial'] == 'yes') {
	unset($_SESSION['SPL' . $Identifier]);
}

if (!isset($_SESSION['SupplierID'])) {
	prnMsg(_('To set up a special') . ', ' . _('the supplier must first be selected from the Select Supplier page'), 'info');
	echo '<a href="', $RootPath, '/SelectSupplier.php">', _('Select the supplier now'), '</a>';
	include ('includes/footer.php');
	exit;
}

if (!isset($_SESSION['CustomerID']) or $_SESSION['CustomerID'] == '') {
	prnMsg(_('To set up a special') . ', ' . _('the customer must first be selected from the Select Customer page'), 'info');
	echo '<a href="', $RootPath, '/SelectCustomer.php">', _('Select the customer now'), '</a>';
	include ('includes/footer.php');
	exit;
}

if (isset($_POST['Cancel'])) {
	unset($_SESSION['SPL' . $Identifier]);
}

if (!isset($_SESSION['SPL' . $Identifier])) {
	/* It must be a new special order being created $_SESSION['SPL'.$Identifier] would be set up from the order modification code above if a modification to an existing order.  */

	$_SESSION['SPL' . $Identifier] = new SpecialOrder;

}

/*if not already done populate the SPL object with supplier data */
if (!isset($_SESSION['SPL' . $Identifier]->SupplierID)) {
	$SQL = "SELECT suppliers.suppname,
					suppliers.currcode,
					currencies.rate,
					currencies.decimalplaces
				FROM suppliers INNER JOIN currencies
					ON suppliers.currcode=currencies.currabrev
				WHERE supplierid='" . $_SESSION['SupplierID'] . "'";
	$ErrMsg = _('The supplier record of the supplier selected') . ": " . $_SESSION['SupplierID'] . ' ' . _('cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the supplier details and failed was');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

	$MyRow = DB_fetch_array($Result);
	$_SESSION['SPL' . $Identifier]->SupplierID = $_SESSION['SupplierID'];
	$_SESSION['SPL' . $Identifier]->SupplierName = $MyRow['suppname'];
	$_SESSION['SPL' . $Identifier]->SuppCurrCode = $MyRow['currcode'];
	$_SESSION['SPL' . $Identifier]->SuppCurrExRate = $MyRow['rate'];
	$_SESSION['SPL' . $Identifier]->SuppCurrDecimalPlaces = $MyRow['decimalplaces'];
}
if (!isset($_SESSION['SPL' . $Identifier]->CustomerID)) {
	// Now check to ensure this account is not on hold */
	$SQL = "SELECT debtorsmaster.name,
					holdreasons.dissallowinvoices,
					debtorsmaster.currcode,
					currencies.rate,
					currencies.decimalplaces
			FROM debtorsmaster INNER JOIN holdreasons
			ON debtorsmaster.holdreason=holdreasons.reasoncode
			INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			WHERE debtorsmaster.debtorno = '" . $_SESSION['CustomerID'] . "'";

	$ErrMsg = _('The customer record for') . ' : ' . $_SESSION['CustomerID'] . ' ' . _('cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the customer details and failed was');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

	$MyRow = DB_fetch_array($Result);
	if ($MyRow['dissallowinvoices'] != 1) {
		if ($MyRow['dissallowinvoices'] == 2) {
			prnMsg(_('The') . ' ' . $MyRow['name'] . ' ' . _('account is currently flagged as an account that needs to be watched. Please contact the credit control personnel to discuss'), 'warn');
		}
	}
	$_SESSION['SPL' . $Identifier]->CustomerID = $_SESSION['CustomerID'];
	$_SESSION['SPL' . $Identifier]->CustomerName = $MyRow['name'];
	$_SESSION['SPL' . $Identifier]->CustCurrCode = $MyRow['currcode'];
	$_SESSION['SPL' . $Identifier]->CustCurrExRate = $MyRow['rate'];
	$_SESSION['SPL' . $Identifier]->CustCurrDecimalPlaces = $MyRow['decimalplaces'];
}

if (isset($_POST['SelectBranch'])) {

	$SQL = "SELECT brname,
					salesman
			FROM custbranch
			WHERE debtorno='" . $_SESSION['SPL' . $Identifier]->CustomerID . "'
			AND branchcode='" . $_POST['SelectBranch'] . "'";
	$BranchResult = DB_query($SQL);
	$MyRow = DB_fetch_array($BranchResult);
	$_SESSION['SPL' . $Identifier]->BranchCode = $_POST['SelectBranch'];
	$_SESSION['SPL' . $Identifier]->BranchName = $MyRow['brname'];
	$_SESSION['SPL' . $Identifier]->SalesPerson = $MyRow['salesman'];
}

if (!isset($_SESSION['SPL' . $Identifier]->BranchCode)) {
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/customer.png" title="', _('Shop Configuration'), '" alt="" />
			', _('Purchase from'), ' ', $_SESSION['SPL' . $Identifier]->SupplierName, ' ', _('in'), ' ', $_SESSION['SPL' . $Identifier]->SuppCurrCode, ' ', _('for'), ' ', $_SESSION['SPL' . $Identifier]->CustomerName, ' (', $_SESSION['SPL' . $Identifier]->CustCurrCode, ')
		</p>';
} else {
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/customer.png" title="', _('Shop Configuration'), '" alt="" />
			', _('Purchase from'), ' ', $_SESSION['SPL' . $Identifier]->SupplierName, ' ', _('in'), ' ', $_SESSION['SPL' . $Identifier]->SuppCurrCode, ' ', _('for'), ' ', $_SESSION['SPL' . $Identifier]->CustomerName, ' (', $_SESSION['SPL' . $Identifier]->CustCurrCode, ') - ', _('delivered to'), ' ', $_SESSION['SPL' . $Identifier]->BranchName, ' ', _('branch'), '
		</p>';
}

/*if the branch details and delivery details have not been entered then select them from the list */
if (!isset($_SESSION['SPL' . $Identifier]->BranchCode)) {

	$SQL = "SELECT branchcode,
					brname
			FROM custbranch
			WHERE debtorno='" . $_SESSION['CustomerID'] . "'";
	$BranchResult = DB_query($SQL);

	if (DB_num_rows($BranchResult) > 0) {

		echo '<div class="centre">
				', _('Select the customer branch to deliver the special to from the list below'), '</div>';

		echo '<table>
				<tr>
					<th>', _('Code'), '</th>
					<th>', _('Branch Name'), '</th>
				</tr>';

		$k = 0; //row counter to determine background colour
		while ($MyRow = DB_fetch_array($BranchResult)) {

			echo '<tr class="striped_row">
						<td><input type="submit" name="SelectBranch" value="', $MyRow['branchcode'], '" /></td>
						<td>', htmlspecialchars($MyRow['brname'], ENT_QUOTES, 'UTF-8', false), '</td>
					</tr>';

		}
		//end of while loop
		echo '</table>';
		echo '</form>';
		include ('includes/footer.php');
		exit;

	} else {
		prnMsg(_('There are no branches defined for the customer selected') . '. ' . _('Please select a customer that has branches defined'), 'info');
		include ('includes/footer.php');
		exit;
	}
}

if (isset($_GET['Delete'])) {
	/*User hit the delete link on a line */
	$_SESSION['SPL' . $Identifier]->remove_from_order($_GET['Delete']);
}

if (isset($_POST['EnterLine'])) {

	/*Add the header info to the session variable in any event */

	if (mb_strlen($_POST['QuotationRef']) < 3) {
		prnMsg(_('The reference for this order is less than 3 characters') . ' - ' . _('a reference more than 3 characters is required before the order can be added'), 'warn');
	}
	if ($_POST['Initiator'] == '') {
		prnMsg(_('The person entering this order must be specified in the initiator field') . ' - ' . _('a blank initiator is not allowed'), 'warn');
	}

	$AllowAdd = True;
	/*always assume the best */

	/*THEN CHECK FOR THE WORST */

	if (!is_numeric(filter_number_format($_POST['Qty']))) {
		$AllowAdd = False;
		prnMsg(_('Cannot Enter this order line') . '<br />' . _('The quantity of the order item must be numeric'), 'warn');
	}

	if (filter_number_format($_POST['Qty']) < 0) {
		$AllowAdd = False;
		prnMsg(_('Cannot Enter this order line') . '<br />' . _('The quantity of the ordered item entered must be a positive amount'), 'warn');
	}

	if (!is_numeric(filter_number_format($_POST['Price']))) {
		$AllowAdd = False;
		prnMsg(_('Cannot Enter this order line') . '<br />' . _('The price entered must be numeric'), 'warn');
	}

	if (!is_numeric(filter_number_format($_POST['Cost']))) {
		$AllowAdd = False;
		prnMsg(_('Cannot Enter this order line') . '<br />' . _('The cost entered must be numeric'), 'warn');
	}

	if (((filter_number_format($_POST['Price']) / $_SESSION['SPL' . $Identifier]->CustCurrExRate) - (filter_number_format($_POST['Cost']) / $_SESSION['SPL' . $Identifier]->SuppCurrExRate)) < 0) {
		$AllowAdd = False;
		prnMsg(_('Cannot Enter this order line') . '<br />' . _('The sale is at a lower price than the cost'), 'warn');
	}

	if (!is_date($_POST['ReqDelDate'])) {
		$AllowAdd = False;
		prnMsg(_('Cannot Enter this order line') . '<br />' . _('The date entered must be in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'warn');
	}
	if ($AllowAdd == True) {

		$_SESSION['SPL' . $Identifier]->add_to_order($_POST['LineNo'], filter_number_format($_POST['Qty']), $_POST['ItemDescription'], filter_number_format($_POST['Price']), filter_number_format($_POST['Cost']), $_POST['StkCat'], $_POST['ReqDelDate']);

		unset($_POST['Price']);
		unset($_POST['Cost']);
		unset($_POST['ItemDescription']);
		unset($_POST['StkCat']);
		unset($_POST['ReqDelDate']);
		unset($_POST['Qty']);
	}
}

if (isset($_POST['StkLocation'])) {
	$_SESSION['SPL' . $Identifier]->StkLocation = $_POST['StkLocation'];
}
if (isset($_POST['Initiator'])) {
	$_SESSION['SPL' . $Identifier]->Initiator = $_POST['Initiator'];
}
if (isset($_POST['QuotationRef'])) {
	$_SESSION['SPL' . $Identifier]->QuotationRef = $_POST['QuotationRef'];
}
if (isset($_POST['Comments'])) {
	$_SESSION['SPL' . $Identifier]->Comments = $_POST['Comments'];
}
if (isset($_POST['CustRef'])) {
	$_SESSION['SPL' . $Identifier]->CustRef = $_POST['CustRef'];
}

if (isset($_POST['Commit'])) {
	/*User wishes to commit the order to the database */

	/*First do some validation
	 Is the delivery information all entered*/
	$InputError = 0;
	/*Start off assuming the best */
	if ($_SESSION['SPL' . $Identifier]->StkLocation == '' or !isset($_SESSION['SPL' . $Identifier]->StkLocation)) {
		prnMsg(_('The purchase order can not be committed to the database because there is no stock location specified to book any stock items into'), 'error');
		$InputError = 1;
	} elseif ($_SESSION['SPL' . $Identifier]->LinesOnOrder <= 0) {
		$InputError = 1;
		prnMsg(_('The purchase order can not be committed to the database because there are no lines entered on this order'), 'error');
	} elseif (mb_strlen($_POST['QuotationRef']) < 3) {
		$InputError = 1;
		prnMsg(_('The reference for this order is less than 3 characters') . ' - ' . _('a reference more than 3 characters is required before the order can be added'), 'error');
	} elseif ($_POST['Initiator'] == '') {
		$InputError = 1;
		prnMsg(_('The person entering this order must be specified in the initiator field') . ' - ' . _('a blank initiator is not allowed'), 'error');
	}

	if ($InputError != 1) {

		if (IsEmailAddress($_SESSION['UserEmail'])) {
			$UserDetails = ' <a href="mailto:' . $_SESSION['UserEmail'] . '">' . $_SESSION['UsersRealName'] . '</a>';
		} else {
			$UserDetails = ' ' . $_SESSION['UsersRealName'] . ' ';
		}

		if ($_SESSION['AutoAuthorisePO'] == 1) {
			//if the user has authority to authorise the PO then it will automatically be authorised
			$AuthSQL = "SELECT authlevel
						FROM purchorderauth
						WHERE userid='" . $_SESSION['UserID'] . "'
						AND currabrev='" . $_SESSION['SPL' . $Identifier]->SuppCurrCode . "'";

			$AuthResult = DB_query($AuthSQL);
			$AuthRow = DB_fetch_array($AuthResult);

			if (DB_num_rows($AuthResult) > 0 and $AuthRow['authlevel'] > $_SESSION['SPL' . $Identifier]->Order_Value()) { //user has authority to authrorise as well as create the order
				$StatusComment = date($_SESSION['DefaultDateFormat']) . ' - ' . _('Order Created and Authorised by') . $UserDetails . '<br />';
				$_SESSION['SPL' . $Identifier]->AllowPrintPO = 1;
				$_SESSION['SPL' . $Identifier]->Status = 'Authorised';
			} else { // no authority to authorise this order
				if (DB_num_rows($AuthResult) == 0) {
					$AuthMessage = _('Your authority to approve purchase orders in') . ' ' . $_SESSION['SPL' . $Identifier]->SuppCurrCode . ' ' . _('has not yet been set up') . '<br />';
				} else {
					$AuthMessage = _('You can only authorise up to') . ' ' . $_SESSION['SPL' . $Identifier]->SuppCurrCode . ' ' . $AuthRow['authlevel'] . '.<br />';
				}

				prnMsg(_('You do not have permission to authorise this purchase order') . '.<br />' . _('This order is for') . ' ' . $_SESSION['SPL' . $Identifier]->SuppCurrCode . ' ' . $_SESSION['SPL' . $Identifier]->Order_Value() . '. ' . $AuthMessage . _('if you think this is a mistake please contact the systems administrator') . '<br />' . _('The order will be created with a status of pending and will require authorisation'), 'warn');

				$StatusComment = date($_SESSION['DefaultDateFormat']) . ' - ' . _('Order Created by') . $UserDetails;
				$_SESSION['SPL' . $Identifier]->Status = 'Pending';
			}
		} else { //auto authorise is set to off
			$StatusComment = date($_SESSION['DefaultDateFormat']) . ' - ' . _('Order Created by') . $UserDetails;
			$_SESSION['SPL' . $Identifier]->Status = 'Pending';
		}

		$SQL = "SELECT contact,
						deladd1,
						deladd2,
						deladd3,
						deladd4,
						deladd5,
						deladd6
				FROM locations
				WHERE loccode='" . $_SESSION['SPL' . $Identifier]->StkLocation . "'";

		$StkLocAddResult = DB_query($SQL);
		$StkLocAddress = DB_fetch_array($StkLocAddResult);

		$Result = DB_Txn_Begin();

		/*Insert to purchase order header record */
		$SQL = "INSERT INTO purchorders (supplierno,
					 					comments,
										orddate,
										rate,
										initiator,
										requisitionno,
										intostocklocation,
										deladd1,
										deladd2,
										deladd3,
										deladd4,
										deladd5,
										deladd6,
										contact,
										status,
										stat_comment,
										allowprint,
										revised,
										deliverydate)
							VALUES ('" . $_SESSION['SPL' . $Identifier]->SupplierID . "',
							 		'" . $_SESSION['SPL' . $Identifier]->Comments . "',
									CURRENT_DATE,
									'" . $_SESSION['SPL' . $Identifier]->SuppCurrExRate . "',
									'" . $_SESSION['SPL' . $Identifier]->Initiator . "',
									'" . $_SESSION['SPL' . $Identifier]->QuotationRef . "',
									'" . $_SESSION['SPL' . $Identifier]->StkLocation . "',
									'" . $StkLocAddress['deladd1'] . "',
									'" . $StkLocAddress['deladd2'] . "',
									'" . $StkLocAddress['deladd3'] . "',
									'" . $StkLocAddress['deladd4'] . "',
									'" . $StkLocAddress['deladd5'] . "',
									'" . $StkLocAddress['deladd6'] . "',
									'" . $StkLocAddress['contact'] . "',
									'" . $_SESSION['SPL' . $Identifier]->Status . "',
									'" . htmlspecialchars($StatusComment, ENT_QUOTES, 'UTF-8') . "',
									'" . $_SESSION['SPL' . $Identifier]->AllowPrintPO . "',
									CURRENT_DATE,
									CURRENT_DATE)";

		$ErrMsg = _('The purchase order header record could not be inserted into the database because');
		$DbgMsg = _('The SQL statement used to insert the purchase order header record and failed was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

		$_SESSION['SPL' . $Identifier]->PurchOrderNo = GetNextTransNo(18);

		/*Insert the purchase order detail records */
		foreach ($_SESSION['SPL' . $Identifier]->LineItems as $SPLLine) {

			/*Set up the part codes required for this order */

			$PartCode = "*" . $_SESSION['SPL' . $Identifier]->PurchOrderNo . "_" . $SPLLine->LineNo;

			$PartAlreadyExists = True;
			/*assume the worst */
			$Counter = 0;
			while ($PartAlreadyExists == True) {
				$SQL = "SELECT COUNT(*) FROM stockmaster WHERE stockid = '" . $PartCode . "'";
				$PartCountResult = DB_query($SQL);
				$PartCount = DB_fetch_row($PartCountResult);
				if ($PartCount[0] != 0) {
					$PartAlreadyExists = True;
					if (mb_strlen($PartCode) == 20) {
						$PartCode = '*' . mb_strtoupper(mb_substr($_SESSION['SPL' . $Identifier]->PurchOrderNo, 0, 13)) . '_' . $SPLLine->LineNo;
					}
					$PartCode = $PartCode . $Counter;
					$Counter++;
				} else {
					$PartAlreadyExists = False;
				}
			}

			$_SESSION['SPL' . $Identifier]->LineItems[$SPLLine->LineNo]->PartCode = $PartCode;

			$SQL = "INSERT INTO stockmaster (stockid,
							categoryid,
							description,
							longdescription)
					VALUES ('" . $PartCode . "',
						'" . $SPLLine->StkCat . "',
						'" . $SPLLine->ItemDescription . "',
						'" . $SPLLine->ItemDescription . "')";
			$ErrMsg = _('The item record for line') . ' ' . $SPLLine->LineNo . ' ' . _('could not be created because');
			$DbgMsg = _('The SQL statement used to insert the item and failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			$SQL = "INSERT INTO stockcosts VALUES('" . $PartCode . "',
											" . $SPLLine->Cost . ",
											0.0,
											0.0,
											CURRENT_TIME,
											0)";
			$ErrMsg = _('The cost record for line') . ' ' . $SPLLine->LineNo . ' ' . _('could not be created because');
			$DbgMsg = _('The SQL statement used to insert the item and failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			$SQL = "INSERT INTO locstock (loccode, stockid)
					SELECT loccode,'" . $PartCode . "' FROM locations";
			$ErrMsg = _('The item stock locations for the special order line') . ' ' . $SPLLine->LineNo . ' ' . _('could not be created because');
			$DbgMsg = _('The SQL statement used to insert the location stock records and failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			/*need to get the stock category GL information */
			$SQL = "SELECT stockact FROM stockcategory WHERE categoryid = '" . $SPLLine->StkCat . "'";
			$ErrMsg = _('The item stock category information for the special order line') . ' ' . $SPLLine->LineNo . ' ' . _('could not be retrieved because');
			$DbgMsg = _('The SQL statement used to get the category information and that failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			$StkCatGL = DB_fetch_row($Result);
			$GLCode = $StkCatGL[0];

			$OrderDate = FormatDateForSQL($SPLLine->ReqDelDate);

			$SQL = "INSERT INTO purchorderdetails (orderno,
								itemcode,
								deliverydate,
								itemdescription,
								glcode,
								unitprice,
								quantityord)
					VALUES ('";
			$SQL = $SQL . $_SESSION['SPL' . $Identifier]->PurchOrderNo . "',
					'" . $PartCode . "',
					'" . $OrderDate . "',
					'" . $SPLLine->ItemDescription . "',
					'" . $GLCode . "',
					'" . $SPLLine->Cost . "',
					'" . $SPLLine->Quantity . "')";

			$ErrMsg = _('One of the purchase order detail records could not be inserted into the database because');
			$DbgMsg = _('The SQL statement used to insert the purchase order detail record and failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

		}
		/* end of the loop round the detail line items on the order */

		echo _('Purchase Order'), ' ', $_SESSION['SPL' . $Identifier]->PurchOrderNo, ' ', _('on'), ' ', $_SESSION['SPL' . $Identifier]->SupplierName, ' ', _('has been created'), '<br />';
		echo '<a href="', $RootPath, '/PO_PDFPurchOrder.php?OrderNo=', urlencode($_SESSION['SPL' . $Identifier]->PurchOrderNo), '">', _('Print Purchase Order'), '</a><br />';

		/*Now insert the sales order too */

		/*First get the customer delivery information */
		$SQL = "SELECT salestype,
					brname,
					braddress1,
					braddress2,
					braddress3,
					braddress4,
					braddress5,
					braddress6,
					defaultshipvia,
					email,
					phoneno
				FROM custbranch INNER JOIN debtorsmaster
					ON custbranch.debtorno=debtorsmaster.debtorno
				WHERE custbranch.debtorno='" . $_SESSION['SPL' . $Identifier]->CustomerID . "'
				AND custbranch.branchcode = '" . $_SESSION['SPL' . $Identifier]->BranchCode . "'";

		$ErrMsg = _('The delivery and sales type for the customer could not be retrieved for this special order') . ' ' . $SPLLine->LineNo . ' ' . _('because');
		$DbgMsg = _('The SQL statement used to get the delivery details and that failed was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

		$BranchDetails = DB_fetch_array($Result);
		$SalesOrderNo = GetNextTransNo(30);
		$HeaderSQL = "INSERT INTO salesorders (orderno,
											debtorno,
											branchcode,
											customerref,
											orddate,
											ordertype,
											shipvia,
											deliverto,
											deladd1,
											deladd2,
											deladd3,
											deladd4,
											deladd5,
											deladd6,
											contactphone,
											contactemail,
											fromstkloc,
											salesperson,
											quotedate,
											confirmeddate,
											deliverydate)
					VALUES ('" . $SalesOrderNo . "',
							'" . $_SESSION['SPL' . $Identifier]->CustomerID . "',
							'" . $_SESSION['SPL' . $Identifier]->BranchCode . "',
							'" . $_SESSION['SPL' . $Identifier]->CustRef . "',
							CURRENT_DATE,
							'" . $BranchDetails['salestype'] . "',
							'" . $BranchDetails['defaultshipvia'] . "',
							'" . $BranchDetails['brname'] . "',
							'" . $BranchDetails['braddress1'] . "',
							'" . $BranchDetails['braddress2'] . "',
							'" . $BranchDetails['braddress3'] . "',
							'" . $BranchDetails['braddress4'] . "',
							'" . $BranchDetails['braddress5'] . "',
							'" . $BranchDetails['braddress6'] . "',
							'" . $BranchDetails['phoneno'] . "',
							'" . $BranchDetails['email'] . "',
							'" . $_SESSION['SPL' . $Identifier]->StkLocation . "',
							'" . $_SESSION['SPL' . $Identifier]->SalesPerson . "',
							CURRENT_DATE,
							CURRENT_DATE,
							'" . $OrderDate . "')";

		$ErrMsg = _('The sales order cannot be added because');
		$InsertQryResult = DB_query($HeaderSQL, $ErrMsg, $DbgMsg);

		$StartOf_LineItemsSQL = "INSERT INTO salesorderdetails (orderno,
																stkcode,
																unitprice,
																quantity,
																orderlineno)
						VALUES ('" . $SalesOrderNo . "'";

		$ErrMsg = _('There was a problem inserting a line into the sales order because');

		foreach ($_SESSION['SPL' . $Identifier]->LineItems as $StockItem) {

			$LineItemsSQL = $StartOf_LineItemsSQL . ",
							'" . $StockItem->PartCode . "',
							'" . $StockItem->Price . "',
							'" . $StockItem->Quantity . "',
							'" . $StockItem->LineNo . "')";
			$Ins_LineItemResult = DB_query($LineItemsSQL, $ErrMsg);

		}
		/* inserted line items into sales order details */

		unset($_SESSION['SPL' . $Identifier]);
		prnMsg(_('Sales Order Number') . ' ' . $SalesOrderNo . ' ' . _('has been entered') . '. <br />' . _('Orders created on a cash sales account may need the delivery details for the order to be modified') . '. <br /><br />' . _('A freight charge may also be applicable'), 'success');

		if (count($_SESSION['AllowedPageSecurityTokens']) > 1) {

			/* Only allow print of packing slip for internal staff - customer logon's cannot go here */
			echo '<a href="', $RootPath, '/PrintCustOrder.php?TransNo=', urlencode($SalesOrderNo), '">', _('Print packing slip'), ' (', _('Preprinted stationery'), ')</a><br />';
			echo '<a href="', $RootPath, '/PrintCustOrder_generic.php?TransNo=', urlencode($SalesOrderNo), '">', _('Print packing slip'), ' (', _('Laser'), ')</a><br />';

		}

		$Result = DB_Txn_Commit();
		unset($_SESSION['SPL' . $Identifier]);
		/*Clear the PO data to allow a newy to be input*/
		echo '<a href="', $RootPath, '/SpecialOrder.php">', _('Enter A New Special Order'), '</a>';
		include ('includes/footer.php');
		exit;
	}
	/*end if there were no input errors trapped */
}
/* end of the code to do transfer the SPL object to the database  - user hit the place Order*/

/*Show the header information for modification */

echo '<fieldset>
		<legend>', _('Special order header'), '</legend>
		<field>
			<label for="StkLocation">', _('Receive Purchase Into and Sell From'), ':</label>
			<select name="StkLocation">';

$SQL = "SELECT locations.loccode,
				locationname
				FROM locations
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" . $_SESSION['UserID'] . "'
					AND locationusers.canupd=1";
$LocnResult = DB_query($SQL);

if (!isset($_SESSION['SPL' . $Identifier]->StkLocation) or $_SESSION['SPL' . $Identifier]->StkLocation == '') {
	/*if this is the first time the form loaded set up defaults */
	$_SESSION['SPL' . $Identifier]->StkLocation = $_SESSION['UserStockLocation'];
}

while ($LocnRow = DB_fetch_array($LocnResult)) {
	if ($_SESSION['SPL' . $Identifier]->StkLocation == $LocnRow['loccode']) {
		echo '<option selected="selected" value="', $LocnRow['loccode'], '">', $LocnRow['locationname'], '</option>';
	} else {
		echo '<option value="', $LocnRow['loccode'], '">', $LocnRow['locationname'], '</option>';
	}
}
echo '</select>
	</field>';

echo '<field>
		<label for="Initiator">', _('Initiated By'), ':</label>
		<input type="text" name="Initiator" size="11" maxlength="10" value="', $_SESSION['SPL' . $Identifier]->Initiator, '" />
	</field>';

echo '<field>
		<label for="QuotationRef">', _('Special Ref'), ':</label>
		<input type="text" name="QuotationRef" size="16" maxlength="15" value="', $_SESSION['SPL' . $Identifier]->QuotationRef, '" />
	</field>';

echo '<field>
		<label for="CustRef">', _('Customer Ref'), ':</label>
		<input type="text" name="CustRef" size="11" maxlength="10" value="', $_SESSION['SPL' . $Identifier]->CustRef, '" />
	</field>';

echo '<field>
		<label for="Comments">', _('Comments'), ':</label>
		<textarea name="Comments" cols="70" rows="2">', $_SESSION['SPL' . $Identifier]->Comments, '</textarea>
	</field>
</fieldset>';
/* Rule off the header */

/*Now show the order so far */

if (count($_SESSION['SPL' . $Identifier]->LineItems) > 0) {

	echo '<table>
			<tr>
				<th colspan="10"><b>', _('Special Order Summary'), '</b></th>
			</tr>
			<tr>
				<th>', _('Item Description'), '</th>
				<th>', _('Delivery'), '</th>
				<th>', _('Quantity'), '</th>
				<th>', _('Purchase Cost'), '<br />', $_SESSION['SPL' . $Identifier]->SuppCurrCode, '</th>
				<th>', _('Sell Price'), '<br />', $_SESSION['SPL' . $Identifier]->CustCurrCode, '</th>
				<th>', _('Total Cost'), '<br />', $_SESSION['SPL' . $Identifier]->SuppCurrCode, '</th>
				<th>', _('Total Price'), '<br />', $_SESSION['SPL' . $Identifier]->CustCurrCode, '</th>
				<th>', _('Total Cost'), '<br />', $_SESSION['CompanyRecord']['currencydefault'], '</th>
				<th>', _('Total Price'), '<br />', $_SESSION['CompanyRecord']['currencydefault'], '</th>
				<th></th>
			</tr>';

	$_SESSION['SPL' . $Identifier]->total = 0;

	foreach ($_SESSION['SPL' . $Identifier]->LineItems as $SPLLine) {

		$LineTotal = $SPLLine->Quantity * $SPLLine->Price;
		$LineCostTotal = $SPLLine->Quantity * $SPLLine->Cost;
		$DisplayLineTotal = locale_number_format($LineTotal, $_SESSION['SPL' . $Identifier]->CustCurrDecimalPlaces);
		$DisplayLineCostTotal = locale_number_format($LineCostTotal, $_SESSION['SPL' . $Identifier]->SuppCurrDecimalPlaces);
		$DisplayLineTotalCurr = locale_number_format($LineTotal / $_SESSION['SPL' . $Identifier]->CustCurrExRate, $_SESSION['CompanyRecord']['decimalplaces']);
		$DisplayLineCostTotalCurr = locale_number_format($LineCostTotal / $_SESSION['SPL' . $Identifier]->SuppCurrExRate, $_SESSION['CompanyRecord']['decimalplaces']);
		$DisplayCost = locale_number_format($SPLLine->Cost, $_SESSION['SPL' . $Identifier]->SuppCurrDecimalPlaces);
		$DisplayPrice = locale_number_format($SPLLine->Price, $_SESSION['SPL' . $Identifier]->CustCurrDecimalPlaces);
		$DisplayQuantity = locale_number_format($SPLLine->Quantity, 'Variable');

		echo '<tr class="striped_row">
				<td>', $SPLLine->ItemDescription, '</td>
				<td>', $SPLLine->ReqDelDate, '</td>
				<td class="number">', $DisplayQuantity, '</td>
				<td class="number">', $DisplayCost, '</td>
				<td class="number">', $DisplayPrice, '</td>
				<td class="number">', $DisplayLineCostTotal, '</td>
				<td class="number">', $DisplayLineTotal, '</td>
				<td class="number">', $DisplayLineCostTotalCurr, '</td>
				<td class="number">', $DisplayLineTotalCurr, '</td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', urlencode($Identifier), '&Delete=', urlencode($SPLLine->LineNo), '">', _('Delete'), '</a></td>
			</tr>';

		$_SESSION['SPL' . $Identifier]->total+= ($LineTotal / $_SESSION['SPL' . $Identifier]->CustCurrExRate);
	}

	$DisplayTotal = locale_number_format($_SESSION['SPL' . $Identifier]->total, $_SESSION['SPL' . $Identifier]->CustCurrDecimalPlaces);
	echo '<tr>
			<td colspan="8" class="number">', _('Total excluding tax'), '</td>
			<td class="number"><b>', $DisplayTotal, '</b></td>
		</tr>
	</table>';

}

/*Set up the form to enter new special items into */

echo '<input type="hidden" name="LineNo" value="', ($_SESSION['SPL' . $Identifier]->LinesOnOrder + 1), '" />';

if (!isset($_POST['ItemDescription'])) {
	$_POST['ItemDescription'] = '';
}

echo '<fieldset>
		<legend>', _('Special Order Details'), '</legend>';

echo '<field>
		<label for="ItemDescription">', _('Ordered item Description'), ':</label>
		<input type="text" name="ItemDescription" size="40" maxlength="40" value="' . $_POST['ItemDescription'] . '" />
	</field>';

echo '<field>
		<label for="StkCat">', _('Category'), ':</label>
		<select required="required" name="StkCat">';

$SQL = "SELECT categoryid, categorydescription FROM stockcategory";
$ErrMsg = _('The stock categories could not be retrieved because');
$DbgMsg = _('The SQL used to retrieve stock categories and failed was');
$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['StkCat']) and $MyRow['categoryid'] == $_POST['StkCat']) {
		echo '<option selected="selected" value="', $MyRow['categoryid'], '">', $MyRow['categorydescription'], '</option>';
	} else {
		echo '<option value="', $MyRow['categoryid'], '">', $MyRow['categorydescription'], '</option>';
	}
}
echo '</select>
	</field>';

/*default the order quantity to 1 unit */
$_POST['Qty'] = 1;

echo '<field>
		<label for="Qty">', _('Order Quantity'), ':</label>
		<input type="text" class="number" size="7" required="required" maxlength="6" name="Qty" value="', locale_number_format($_POST['Qty'], 'Variable'), '" />
	</field>';

if (!isset($_POST['Cost'])) {
	$_POST['Cost'] = 0;
}
echo '<field>
		<label for="Cost">', _('Unit Cost'), ':</label>
		<input type="text" class="number" size="15" required="required" maxlength="14" name="Cost" value="', locale_number_format($_POST['Cost'], $_SESSION['SPL' . $Identifier]->SuppCurrDecimalPlaces), '" />
	</field>';

if (!isset($_POST['Price'])) {
	$_POST['Price'] = 0;
}
echo '<field>
		<label for="Price">', _('Unit Price'), ':</label>
		<input type="text" class="number" size="15" required="required" maxlength="14" name="Price" value="', locale_number_format($_POST['Price'], $_SESSION['SPL' . $Identifier]->CustCurrDecimalPlaces), '" />
	</field>';

/*Default the required delivery date to tomorrow as a starting point */
$_POST['ReqDelDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, Date('m'), Date('d') + 1, Date('y')));
echo '<field>
		<label for="ReqDelDate">', _('Required Delivery Date'), ':</label>
		<input type="text" class="date" size="12" maxlength="11" name="ReqDelDate" value="', $_POST['ReqDelDate'], '" />
	</field>';

echo '</fieldset>';
/* end of main table */

echo '<div class="centre">
		<input type="submit" name="EnterLine" value="', _('Add Item to Order'), '" /><br />
		<input type="submit" name="Cancel" value="', _('Start Again'), '" /><br />
		<input type="submit" name="Commit" value="', _('Process This Order'), '" />
	</div>
</form>';

include ('includes/footer.php');
?>