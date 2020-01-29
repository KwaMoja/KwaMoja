<?php
/*The credit selection screen uses the Cart class used for the making up orders
 some of the variable names refer to order - please think credit when you read order */

include ('includes/DefineCartClass.php');
include ('includes/DefineSerialItems.php');
/* Session started in session.php for password checking and authorisation level check */
include ('includes/session.php');

$Title = _('Create Credit Note');

/* Manual links before header.php */
$ViewTopic = 'ARTransactions';
$BookMark = 'CreditItems';
include ('includes/header.php');
include ('includes/SQL_CommonFunctions.php');
include ('includes/GetSalesTransGLCodes.php');
include ('includes/GetPrice.php');

if (empty($_GET['identifier'])) {
	/*unique session identifier to ensure that there is no conflict with other order entry sessions on the same machine  */
	$Identifier = date('U');
} else {
	$Identifier = $_GET['identifier'];
}

if (isset($_POST['ProcessCredit']) and !isset($_SESSION['CreditItems' . $Identifier])) {
	prnMsg(_('This credit note has already been processed. Refreshing the page will not enter the credit note again') . '<br />' . _('Please use the navigation links provided rather than using the browser back button and then having to refresh'), 'info');
	echo '<br /><a href="', $RootPath, '/index.php">', _('Back to the menu'), '</a>';
	include ('includes/footer.php');
	exit;
}

if (isset($_GET['NewCredit'])) {
	/*New credit note entry - clear any existing credit note details from the Items object and initiate a newy*/
	if (isset($_SESSION['CreditItems' . $Identifier])) {
		unset($_SESSION['CreditItems' . $Identifier]->LineItems);
		unset($_SESSION['CreditItems' . $Identifier]);
	}
}

if (!isset($_SESSION['CreditItems' . $Identifier])) {
	/* It must be a new credit note being created $_SESSION['CreditItems'.$Identifier] would be set up from a previous call*/

	$_SESSION['CreditItems' . $Identifier] = new cart;

	$_SESSION['RequireCustomerSelection'] = 1;
}

if (isset($_POST['ChangeCustomer'])) {
	$_SESSION['RequireCustomerSelection'] = 1;
}

if (isset($_POST['Quick'])) {
	unset($_POST['PartSearch']);
}

if (isset($_POST['CancelCredit'])) {
	unset($_SESSION['CreditItems' . $Identifier]->LineItems);
	unset($_SESSION['CreditItems' . $Identifier]);
	$_SESSION['CreditItems' . $Identifier] = new cart;
	$_SESSION['RequireCustomerSelection'] = 1;
}

if (isset($_POST['SearchCust']) and $_SESSION['RequireCustomerSelection'] == 1) {
	unset($_POST['JustSelectedACustomer']);
	$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

	$SQL = "SELECT	debtorsmaster.name,
					custbranch.debtorno,
					custbranch.brname,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.branchcode
				FROM custbranch
				INNER JOIN debtorsmaster
					ON custbranch.debtorno=debtorsmaster.debtorno
				WHERE debtorsmaster.name " . LIKE . " '" . $SearchString . "'
					AND custbranch.debtorno " . LIKE . "'%" . $_POST['CustCode'] . "%'
					AND custbranch.disabletrans='0'";

	$ErrMsg = _('Customer branch records requested cannot be retrieved because');
	$DbgMsg = _('SQL used to retrieve the customer details was');
	$Result_CustSelect = DB_query($SQL, $ErrMsg, $DbgMsg);

	if (DB_num_rows($Result_CustSelect) == 1) {
		$MyRow = DB_fetch_array($Result_CustSelect);
		$SelectedCustomer = trim($MyRow['debtorno']);
		$SelectedBranch = trim($MyRow['branchcode']);
		$_POST['JustSelectedACustomer'] = 'Yes';
	} elseif (DB_num_rows($Result_CustSelect) == 0) {
		prnMsg(_('Sorry') . ' ... ' . _('there are no customer branch records contain the selected text') . ' - ' . _('please alter your search criteria and try again'), 'info');
	}

	/*one of keywords or custcode was more than a zero length string */
}
/*end of if search button for customers was hit*/

if (isset($_POST['JustSelectedACustomer']) and !isset($SelectedCustomer)) {
	/*Need to figure out the number of the form variable that the user clicked on */
	for ($i = 1;$i < count($_POST);$i++) { //loop through the returned customers
		if (isset($_POST['SubmitCustomerSelection' . $i])) {
			break;
		}
	}
	if ($i == count($_POST)) {
		prnMsg(_('Unable to identify the selected customer'), 'error');
	} else {
		$SelectedCustomer = trim($_POST['SelectedCustomer' . $i]);
		$SelectedBranch = trim($_POST['SelectedBranch' . $i]);
	}
}

if (isset($SelectedCustomer) and isset($_POST['JustSelectedACustomer'])) {

	/*will only be true if page called from customer selection form
	Now retrieve customer information - name, salestype, currency, terms etc
	*/

	$_SESSION['CreditItems' . $Identifier]->DebtorNo = $SelectedCustomer;
	$_SESSION['CreditItems' . $Identifier]->Branch = $SelectedBranch;
	$_SESSION['RequireCustomerSelection'] = 0;

	/*  default the branch information from the customer branches table CustBranch -particularly where the stock
	 will be booked back into. */

	$SQL = "SELECT debtorsmaster.name,
					debtorsmaster.salestype,
					debtorsmaster.currcode,
					currencies.rate,
					currencies.decimalplaces,
					custbranch.brname,
					custbranch.braddress1,
					custbranch.braddress2,
					custbranch.braddress3,
					custbranch.braddress4,
					custbranch.braddress5,
					custbranch.braddress6,
					custbranch.phoneno,
					custbranch.email,
					custbranch.salesman,
					custbranch.defaultlocation,
					custbranch.taxgroupid,
					locations.taxprovinceid
				FROM custbranch
				INNER JOIN locations ON locations.loccode=custbranch.defaultlocation
				INNER JOIN debtorsmaster ON custbranch.debtorno=debtorsmaster.debtorno
				INNER JOIN currencies ON debtorsmaster.currcode=currencies.currabrev
				WHERE custbranch.branchcode='" . $_SESSION['CreditItems' . $Identifier]->Branch . "'
				AND custbranch.debtorno = '" . $_SESSION['CreditItems' . $Identifier]->DebtorNo . "'";

	$ErrMsg = _('The customer branch record of the customer selected') . ': ' . $SelectedCustomer . ' ' . _('cannot be retrieved because');
	$DbgMsg = _('SQL used to retrieve the branch details was');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

	$MyRow = DB_fetch_array($Result);

	/* the sales type determines the price list to be used by default the customer of the user is
	 defaulted from the entry of the userid and password.  */
	$_SESSION['CreditItems' . $Identifier]->CustomerName = $MyRow['name'];
	$_SESSION['CreditItems' . $Identifier]->DefaultSalesType = $MyRow['salestype'];
	$_SESSION['CreditItems' . $Identifier]->DefaultCurrency = $MyRow['currcode'];
	$_SESSION['CreditItems' . $Identifier]->CurrDecimalPlaces = $MyRow['decimalplaces'];
	$_SESSION['CurrencyRate'] = $MyRow['rate'];
	$_SESSION['CreditItems' . $Identifier]->DeliverTo = $MyRow['brname'];
	$_SESSION['CreditItems' . $Identifier]->BrAdd1 = $MyRow['braddress1'];
	$_SESSION['CreditItems' . $Identifier]->BrAdd2 = $MyRow['braddress2'];
	$_SESSION['CreditItems' . $Identifier]->BrAdd3 = $MyRow['braddress3'];
	$_SESSION['CreditItems' . $Identifier]->BrAdd4 = $MyRow['braddress4'];
	$_SESSION['CreditItems' . $Identifier]->BrAdd5 = $MyRow['braddress5'];
	$_SESSION['CreditItems' . $Identifier]->BrAdd6 = $MyRow['braddress6'];
	$_SESSION['CreditItems' . $Identifier]->PhoneNo = $MyRow['phoneno'];
	$_SESSION['CreditItems' . $Identifier]->Email = $MyRow['email'];
	$_SESSION['CreditItems' . $Identifier]->SalesPerson = $MyRow['salesman'];
	$_SESSION['CreditItems' . $Identifier]->Location = $MyRow['defaultlocation'];
	$_SESSION['CreditItems' . $Identifier]->TaxGroup = $MyRow['taxgroupid'];
	$_SESSION['CreditItems' . $Identifier]->DispatchTaxProvince = $MyRow['taxprovinceid'];
	$_SESSION['CreditItems' . $Identifier]->GetFreightTaxes();
}

/* if the change customer button hit or the customer has not already been selected */
if ($_SESSION['RequireCustomerSelection'] == 1 or !isset($_SESSION['CreditItems' . $Identifier]->DebtorNo) or $_SESSION['CreditItems' . $Identifier]->DebtorNo == '') {

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', $Identifier, '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', _('Search'), '" alt="" />', ' ', _('Select Customer For Credit Note'), '
		</p>';

	echo '<fieldset>
			<legend class="search"> ', _('Customer Selection'), '</legend>';

	echo '<field>
			<label for="Keywords">', _('Enter text in the customer name'), ':</label>
			<input type="text" name="Keywords" size="20" maxlength="25" />
		</field>';

	echo '<h1>', _('OR'), '</h1	>';

	echo '<field>
			<label for="CustCode">', _('Enter text extract in the customer code'), ':</label>
			<input type="text" name="CustCode" size="15" maxlength="18" />
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="SearchCust" value="', _('Search Now'), '" />
		</div>';

	if (isset($Result_CustSelect)) {

		echo '<table cellpadding="2">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('Customer') . '</th>
						<th class="SortedColumn">' . _('Branch') . '</th>
						<th>' . _('Contact') . '</th>
						<th>' . _('Phone') . '</th>
						<th>' . _('Fax') . '</th>
					</tr>
				</thead>';

		$j = 1;

		$LastCustomer = '';
		echo '<tbody>';
		while ($MyRow = DB_fetch_array($Result_CustSelect)) {
			echo '<tr class="striped_row">';
			if ($LastCustomer != $MyRow['name']) {
				echo '<td>', $MyRow['name'], '</td>';
			} else {
				echo '<td></td>';
			}

			echo '<td><input type="submit" name="SubmitCustomerSelection', $j, '" value="', htmlspecialchars($MyRow['brname'], ENT_QUOTES, 'UTF-8'), '" />
					<input type="hidden" name="SelectedCustomer', $j, '" value="', $MyRow['debtorno'], '" />
					<input type="hidden" name="SelectedBranch', $j, '" value="', $MyRow['branchcode'], '" /></td>
					<td>', $MyRow['contactname'], '</td>
					<td>', $MyRow['phoneno'], '</td>
					<td>', $MyRow['faxno'], '</td>
				</tr>';
			$LastCustomer = $MyRow['name'];
			++$j;
			//end of page full new headings if
			
		} //end of while loop
		echo '</tbody>
			</table>
		<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
	} //end if results to show
	echo '</form>';

	//end if RequireCustomerSelection
	
} else {
	/* everything below here only do if a customer is selected
	 first add a header to show who we are making a credit note for */

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', _('Search'), '" alt="" />', ' ', $_SESSION['CreditItems' . $Identifier]->CustomerName, ' - ', $_SESSION['CreditItems' . $Identifier]->DeliverTo . '
		</p>';

	if (isset($_POST['SalesPerson'])) {
		$_SESSION['CreditItems' . $Identifier]->SalesPerson = $_POST['SalesPerson'];
	}

	/* do the search for parts that might be being looked up to add to the credit note */
	if (isset($_POST['Search'])) {

		if ($_POST['Keywords'] != '' and $_POST['StockCode'] != '') {
			prnMsg(_('Stock description keywords have been used in preference to the Stock code extract entered') . '.', 'info');
		}

		if ($_POST['Keywords'] != '') {
			//insert wildcard characters in spaces
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

			if ($_POST['StockCat'] == 'All') {
				$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.description " . LIKE . " '" . $SearchString . "'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					ORDER BY stockmaster.stockid";
			} else {
				$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.description " . LIKE . " '" . $SearchString . "'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					ORDER BY stockmaster.stockid";
			}

		} elseif ($_POST['StockCode'] != '') {
			$SearchString = '%' . $_POST['StockCode'] . '%';
			if ($_POST['StockCat'] == 'All') {
				$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND  stockmaster.stockid " . LIKE . " '" . $SearchString . "'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					ORDER BY stockmaster.stockid";
			} else {
				$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
						AND stockmaster.stockid " . LIKE . " '" . $SearchString . "'
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
						GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.units
						ORDER BY stockmaster.stockid";
			}
		} else {
			if ($_POST['StockCat'] == 'All') {
				$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					ORDER BY stockmaster.stockid";
			} else {
				$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					ORDER BY stockmaster.stockid";
			}
		}

		$ErrMsg = _('There is a problem selecting the part records to display because');
		$SearchResult = DB_query($SQL, $ErrMsg);

		if (DB_num_rows($SearchResult) == 0) {
			prnMsg(_('There are no products available that match the criteria specified'), 'info');
			if ($Debug == 1) {
				prnMsg(_('The SQL statement used was') . ':<br />' . $SQL, 'info');
			}
		}
		if (DB_num_rows($SearchResult) == 1) {
			$MyRow = DB_fetch_array($SearchResult);
			$_POST['NewItem'] = $MyRow['stockid'];
			DB_data_seek($SearchResult, 0);
		}

	} //end of if search for parts to add to the credit note
	/*Always do the stuff below if not looking for a customerid
	 Set up the form for the credit note display and  entry*/

	echo '<form id="MainForm" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', urlencode($Identifier), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	/*Process Quick Entry */
	if (isset($_POST['QuickEntry'])) {
		/* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart */
		$i = 1;
		do {
			do {
				$QuickEntryCode = 'part_' . $i;
				$QuickEntryQty = 'qty_' . $i;
				++$i;
			} while (!is_numeric(filter_number_format($_POST[$QuickEntryQty])) and filter_number_format($_POST[$QuickEntryQty]) <= 0 and mb_strlen($_POST[$QuickEntryCode]) != 0 and $i <= $QuickEntires);

			$_POST['NewItem'] = trim($_POST[$QuickEntryCode]);
			$NewItemQty = filter_number_format($_POST[$QuickEntryQty]);

			if (mb_strlen($_POST['NewItem']) == 0) {
				break;
				/* break out of the loop if nothing in the quick entry fields*/
			}

			$AlreadyOnThisCredit = 0;

			foreach ($_SESSION['CreditItems' . $Identifier]->LineItems as $OrderItem) {

				/* do a loop round the items on the credit note to see that the item
				 is not already on this credit note */

				if ($_SESSION['SO_AllowSameItemMultipleTimes'] == 0 and strcasecmp($OrderItem->StockID, $_POST['NewItem']) == 0) {
					$AlreadyOnThisCredit = 1;
					prnMsg($_POST['NewItem'] . ' ' . _('is already on this credit - the system will not allow the same item on the credit note more than once. However you can change the quantity credited of the existing line if necessary'), 'warn');
				}
			}
			/* end of the foreach loop to look for preexisting items of the same code */

			if ($AlreadyOnThisCredit != 1) {

				$SQL = "SELECT stockmaster.description,
								stockmaster.longdescription,
								stockmaster.stockid,
								stockmaster.units,
								stockmaster.volume,
								stockmaster.grossweight,
								(stockcosts.materialcost+stockcosts.labourcost+stockcosts.overheadcost) AS standardcost,
								stockmaster.mbflag,
								stockmaster.decimalplaces,
								stockmaster.controlled,
								stockmaster.serialised,
								stockmaster.discountcategory,
								stockmaster.taxcatid
							FROM stockmaster
							LEFT JOIN stockcosts
								ON stockcosts.stockid=stockmaster.stockid
								AND stockcosts.succeeded=0
							WHERE  stockmaster.stockid = '" . $_POST['NewItem'] . "'";

				$ErrMsg = _('There is a problem selecting the part because');
				$Result1 = DB_query($SQL, $ErrMsg);

				if ($MyRow = DB_fetch_array($Result1)) {

					$LineNumber = $_SESSION['CreditItems' . $Identifier]->LineCounter;

					if ($_SESSION['CreditItems' . $Identifier]->add_to_cart($MyRow['stockid'], $NewItemQty, $MyRow['description'], $MyRow['longdescription'], GetPrice($_POST['NewItem'], $_SESSION['CreditItems' . $Identifier]->DebtorNo, $_SESSION['CreditItems' . $Identifier]->Branch), 0, $MyRow['units'], $MyRow['volume'], $MyRow['grossweight'], 0, $MyRow['mbflag'], Date($_SESSION['DefaultDateFormat']), 0, $MyRow['discountcategory'], $MyRow['controlled'], $MyRow['serialised'], $MyRow['decimalplaces'], '', 'No', -1, $MyRow['taxcatid'], '', '', '', $MyRow['standardcost']) == 1) {

						$_SESSION['CreditItems' . $Identifier]->GetTaxes($LineNumber);

						if ($MyRow['controlled'] == 1) {
							/*Qty must be built up from serial item entries */
							$_SESSION['CreditItems' . $Identifier]->LineItems[$LineNumber]->Quantity = 0;
						}

					}
				} else {
					prnMsg($_POST['NewItem'] . ' ' . _('does not exist in the database and cannot therefore be added to the credit note'), 'warn');
				}
			}
			/* end of if not already on the credit note */
		}
		while ($i <= $_SESSION['QuickEntries']);
		/*loop to the next quick entry record */
		unset($_POST['NewItem']);
	}
	/* end of if quick entry */

	/* setup system defaults for looking up prices and the number of ordered items
	 if an item has been selected for adding to the basket add it to the session arrays */
	if ($_SESSION['CreditItems' . $Identifier]->ItemsOrdered > 0 or isset($_POST['NewItem'])) {

		if (isset($_GET['Delete'])) {
			$_SESSION['CreditItems' . $Identifier]->remove_from_cart($_GET['Delete']);
		}

		if (isset($_POST['ChargeFreightCost'])) {
			$_SESSION['CreditItems' . $Identifier]->FreightCost = filter_number_format($_POST['ChargeFreightCost']);
		}

		if (isset($_POST['Location']) and $_POST['Location'] != $_SESSION['CreditItems' . $Identifier]->Location) {

			$_SESSION['CreditItems' . $Identifier]->Location = $_POST['Location'];

			$NewDispatchTaxProvResult = DB_query("SELECT taxprovinceid FROM locations WHERE loccode='" . $_POST['Location'] . "'");
			$MyRow = DB_fetch_array($NewDispatchTaxProvResult);

			$_SESSION['CreditItems' . $Identifier]->DispatchTaxProvince = $MyRow['taxprovinceid'];

			foreach ($_SESSION['CreditItems' . $Identifier]->LineItems as $LineItem) {
				$_SESSION['CreditItems' . $Identifier]->GetTaxes($LineItem->LineNumber);
			}
		}

		foreach ($_SESSION['CreditItems' . $Identifier]->LineItems as $LineItem) {

			if (isset($_POST['Quantity_' . $LineItem->LineNumber])) {

				$Quantity = filter_number_format($_POST['Quantity_' . $LineItem->LineNumber]);
				$Narrative = $_POST['Narrative_' . $LineItem->LineNumber];

				if (isset($_POST['Price_' . $LineItem->LineNumber])) {
					if (isset($_POST['Gross']) and $_POST['Gross'] == true) {
						$TaxTotalPercent = 0;
						foreach ($LineItem->Taxes as $Tax) {
							if ($Tax->TaxOnTax == 1) {
								$TaxTotalPercent+= (1 + $TaxTotalPercent) * $Tax->TaxRate;
							} else {
								$TaxTotalPercent+= $Tax->TaxRate;
							}
						}
						$Price = round(filter_number_format($_POST['Price_' . $LineItem->LineNumber]) / ($TaxTotalPercent + 1), $_SESSION['CreditItems' . $Identifier]->CurrDecimalPlaces);
					} else {
						$Price = filter_number_format($_POST['Price_' . $LineItem->LineNumber]);
					}

					$DiscountPercentage = filter_number_format($_POST['Discount_' . $LineItem->LineNumber]);

					foreach ($LineItem->Taxes as $TaxKey => $TaxLine) {
						if (is_numeric(filter_number_format($_POST[$LineItem->LineNumber . $TaxLine->TaxCalculationOrder . '_TaxRate']))) {
							$_SESSION['CreditItems' . $Identifier]->LineItems[$LineItem->LineNumber]->Taxes[$TaxKey]->TaxRate = filter_number_format($_POST[$LineItem->LineNumber . $TaxKey . '_TaxRate']) / 100;
						}
					}
				}
				if ($Quantity < 0 or $Price < 0 or $DiscountPercentage > 100 or $DiscountPercentage < 0) {
					prnMsg(_('The item could not be updated because you are attempting to set the quantity credited to less than 0 or the price less than 0 or the discount more than 100% or less than 0%'), 'warn');
				} elseif (isset($_POST['Quantity_' . $LineItem->LineNumber])) {
					$_SESSION['CreditItems' . $Identifier]->update_cart_item($LineItem->LineNumber, $Quantity, $Price, $DiscountPercentage / 100, $Narrative, 'No', $LineItem->ItemDue, $LineItem->POLine, 0, $Identifier);
				}
			}

		}

		foreach ($_SESSION['CreditItems' . $Identifier]->FreightTaxes as $FreightTaxKey => $FreightTaxLine) {
			if (is_numeric(filter_number_format($_POST['FreightTaxRate' . $FreightTaxLine->TaxCalculationOrder]))) {
				$_SESSION['CreditItems' . $Identifier]->FreightTaxes[$FreightTaxKey]->TaxRate = filter_number_format($_POST['FreightTaxRate' . $FreightTaxKey]) / 100;
			}
		}

		if (isset($_POST['NewItem'])) {
			/* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart */

			$AlreadyOnThisCredit = 0;

			foreach ($_SESSION['CreditItems' . $Identifier]->LineItems as $OrderItem) {

				/* do a loop round the items on the credit note to see that the item
				 is not already on this credit note */

				if ($_SESSION['SO_AllowSameItemMultipleTimes'] == 0 and strcasecmp($OrderItem->StockID, $_POST['NewItem']) == 0) {
					$AlreadyOnThisCredit = 1;
					prnMsg(_('The item selected is already on this credit the system will not allow the same item on the credit note more than once. However you can change the quantity credited of the existing line if necessary.'), 'warn');
				}
			}
			/* end of the foreach loop to look for preexisting items of the same code */

			if ($AlreadyOnThisCredit != 1) {

				$SQL = "SELECT stockmaster.description,
								stockmaster.longdescription,
								stockmaster.stockid,
								stockmaster.units,
								stockmaster.volume,
								stockmaster.grossweight,
								stockmaster.mbflag,
								stockmaster.discountcategory,
								stockmaster.controlled,
								stockmaster.decimalplaces,
								stockmaster.serialised,
								(stockcosts.materialcost+stockcosts.labourcost+stockcosts.overheadcost) AS standardcost,
								stockmaster.taxcatid
							FROM stockmaster
							LEFT JOIN stockcosts
								ON stockcosts.stockid=stockmaster.stockid
								AND stockcosts.succeeded=0
							WHERE stockmaster.stockid = '" . $_POST['NewItem'] . "'";

				$ErrMsg = _('The item details could not be retrieved because');
				$DbgMsg = _('The SQL used to retrieve the item details but failed was');
				$Result1 = DB_query($SQL, $ErrMsg, $DbgMsg);
				$MyRow = DB_fetch_array($Result1);

				$LineNumber = $_SESSION['CreditItems' . $Identifier]->LineCounter;
				/*validate the data returned before adding to the items to credit */
				if ($_SESSION['CreditItems' . $Identifier]->add_to_cart($MyRow['stockid'], 1, $MyRow['description'], $MyRow['longdescription'], GetPrice($_POST['NewItem'], $_SESSION['CreditItems' . $Identifier]->DebtorNo, $_SESSION['CreditItems' . $Identifier]->Branch), 0, $MyRow['units'], $MyRow['volume'], $MyRow['grossweight'], 0, $MyRow['mbflag'], Date($_SESSION['DefaultDateFormat']), 0, $MyRow['discountcategory'], $MyRow['controlled'], $MyRow['serialised'], $MyRow['decimalplaces'], '', 'No', -1, $MyRow['taxcatid'], '', '', '', $MyRow['standardcost']) == 1) {

					$_SESSION['CreditItems' . $Identifier]->GetTaxes($LineNumber);

					if ($MyRow['controlled'] == 1) {
						/*Qty must be built up from serial item entries */
						$_SESSION['CreditItems' . $Identifier]->LineItems[$LineNumber]->Quantity = 0;
					}
				}
			}
			/* end of if not already on the credit note */
		}
		/* end of if its a new item */

		/* This is where the credit note as selected should be displayed  reflecting any deletions or insertions*/

		echo '<table cellpadding="2">
				<tr>
					<th>', _('Item Code'), '</th>
					<th>', _('Item Description'), '</th>
					<th>', _('Quantity'), '</th>
					<th>', _('Unit'), '</th>
					<th>', _('Price'), '</th>
					<th>', _('Gross'), '</th>
					<th>', _('Discount'), '</th>
					<th>', _('Total'), '<br />', _('Excl Tax'), '</th>
					<th>', _('Tax Authority'), '</th>
					<th>', _('Tax'), '<br />', _('Rate'), '</th>
					<th>', _('Tax'), '<br />', _('Amount'), '</th>
					<th>', _('Total'), '<br />', _('Incl Tax'), '</th>
				</tr>';

		$_SESSION['CreditItems' . $Identifier]->total = 0;
		$_SESSION['CreditItems' . $Identifier]->totalVolume = 0;
		$_SESSION['CreditItems' . $Identifier]->totalWeight = 0;

		$TaxTotal = 0;
		$TaxTotals = array();
		$TaxGLCodes = array();

		foreach ($_SESSION['CreditItems' . $Identifier]->LineItems as $LineItem) {

			$LineTotal = round($LineItem->Quantity * $LineItem->Price * (1 - $LineItem->DiscountPercent), $_SESSION['CreditItems' . $Identifier]->CurrDecimalPlaces);
			$DisplayLineTotal = locale_number_format($LineTotal, $_SESSION['CreditItems' . $Identifier]->CurrDecimalPlaces);

			echo '<tr class="striped_row">
					<td>', $LineItem->StockID, '</td>
					<td title="', $LineItem->LongDescription, '">', $LineItem->ItemDescription, '</td>';

			if ($LineItem->Controlled == 0) {
				echo '<td>
						<input type="text" class="number" name="Quantity_', $LineItem->LineNumber, '" required="required" maxlength="11" size="6" value="', locale_number_format(round($LineItem->Quantity, $LineItem->DecimalPlaces), $LineItem->DecimalPlaces), '" />
					</td>';
			} else {
				echo '<td class="number">
						<a href="', $RootPath, '/CreditItemsControlled.php?LineNo=', urlencode($LineItem->LineNumber), '&identifier=', urlencode($Identifier), '">', locale_number_format($LineItem->Quantity, $LineItem->DecimalPlaces), '</a>
					  <input type="hidden" name="Quantity_', $LineItem->LineNumber, '" value="', locale_number_format(round($LineItem->Quantity, $LineItem->DecimalPlaces), $LineItem->DecimalPlaces), '" /></td>';
			}

			echo '<td>', $LineItem->Units, '</td>
				<td><input type="text" class="number" name="Price_', $LineItem->LineNumber, '" size="10" required="required" maxlength="12" value="', locale_number_format($LineItem->Price, $_SESSION['CreditItems' . $Identifier]->CurrDecimalPlaces), '" /></td>
				<td><input type="CheckBox" name="Gross" value="false" /></td>
				<td><input type="text" class="number" name="Discount_', $LineItem->LineNumber, '" size="3" required="required" maxlength="3" value="', locale_number_format(($LineItem->DiscountPercent * 100), 'Variable'), '" />%</td>
				<td class="number">', $DisplayLineTotal, '</td>';

			/*Need to list the taxes applicable to this line */
			echo '<td>';
			foreach ($_SESSION['CreditItems' . $Identifier]->LineItems[$LineItem->LineNumber]->Taxes as $Tax) {
				echo '<br />';
				echo $Tax->TaxAuthDescription;
			}
			echo '</td>';
			echo '<td>';

			$i = 0; // initialise the number of taxes iterated through
			$TaxLineTotal = 0; //initialise tax total for the line
			foreach ($LineItem->Taxes as $TaxKey => $Tax) {

				if ($i > 0) {
					echo '<br />';
				}
				echo '<input type="text" class="number" name="', $LineItem->LineNumber, $TaxKey, '_TaxRate" maxlength="4" size="4" value="', locale_number_format($Tax->TaxRate * 100, 'Variable'), '" /> %';
				++$i;
				if ($Tax->TaxOnTax == 1) {
					$TaxTotals[$Tax->TaxAuthID]+= ($Tax->TaxRate * ($LineTotal + $TaxLineTotal));
					$TaxLineTotal+= ($Tax->TaxRate * ($LineTotal + $TaxLineTotal));
				} else {
					$TaxTotals[$Tax->TaxAuthID]+= ($Tax->TaxRate * $LineTotal);
					$TaxLineTotal+= ($Tax->TaxRate * $LineTotal);
				}
				$TaxGLCodes[$Tax->TaxAuthID] = $Tax->TaxGLCode;
			}
			echo '</td>';

			$TaxTotal+= $TaxLineTotal;

			$DisplayTaxAmount = locale_number_format($TaxLineTotal, $_SESSION['CreditItems' . $Identifier]->CurrDecimalPlaces);
			$DisplayGrossLineTotal = locale_number_format($LineTotal + $TaxLineTotal, $_SESSION['CreditItems' . $Identifier]->CurrDecimalPlaces);

			echo '<td class="number">', $DisplayTaxAmount, '</td>
				<td class="number">', $DisplayGrossLineTotal, '</td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', urlencode($Identifier), '&Delete=', urlencode($LineItem->LineNumber), '" onclick="return MakeConfirm(\'', _('Are you sure you wish to delete this line item from the credit note?'), '\', \'Confirm Delete\', this);">', _('Delete'), '</a></td>
			</tr>';

			echo '<tr>
					<td>', _('Comments'), '</td>
					<td colspan="10">
						<textarea  name="Narrative_', $LineItem->LineNumber, '" cols="100%" rows="1">', $LineItem->Narrative, '</textarea>
					</td>
				</tr>';

			$_SESSION['CreditItems' . $Identifier]->total+= $LineTotal;
			$_SESSION['CreditItems' . $Identifier]->totalVolume+= ($LineItem->Quantity * $LineItem->Volume);
			$_SESSION['CreditItems' . $Identifier]->totalWeight+= ($LineItem->Quantity * $LineItem->Weight);
		}

		if (!isset($_POST['ChargeFreightCost']) and !isset($_SESSION['CreditItems' . $Identifier]->FreightCost)) {
			$_POST['ChargeFreightCost'] = 0;
		}
		echo '<tr>
				<td colspan="5"></td>';

		echo '<td colspan="2" class="number">' . _('Credit Freight') . '</td>
			<td><input type="text" class="number" size="6" required="required" maxlength="6" name="ChargeFreightCost" value="' . locale_number_format($_SESSION['CreditItems' . $Identifier]->FreightCost, $_SESSION['CreditItems' . $Identifier]->CurrDecimalPlaces) . '" /></td>';

		$FreightTaxTotal = 0; //initialise tax total
		echo '<td>';

		$i = 0; // initialise the number of taxes iterated through
		foreach ($_SESSION['CreditItems' . $Identifier]->FreightTaxes as $FreightTaxLine) {
			if ($i > 0) {
				echo '<br />';
			}
			echo $FreightTaxLine->TaxAuthDescription;
			++$i;
		}

		echo '</td><td>';

		$i = 0;
		foreach ($_SESSION['CreditItems' . $Identifier]->FreightTaxes as $FreightTaxLine) {
			if ($i > 0) {
				echo '<br />';
			}

			echo '<input type="text" class="number" name=FreightTaxRate' . $FreightTaxLine->TaxCalculationOrder . ' required="required" maxlength="4" size="4" value="' . locale_number_format(($FreightTaxLine->TaxRate * 100), 'Variable') . '" /> %';

			if ($FreightTaxLine->TaxOnTax == 1) {
				$TaxTotals[$FreightTaxLine->TaxAuthID]+= ($FreightTaxLine->TaxRate * ($_SESSION['CreditItems' . $Identifier]->FreightCost + $FreightTaxTotal));
				$FreightTaxTotal+= ($FreightTaxLine->TaxRate * ($_SESSION['CreditItems' . $Identifier]->FreightCost + $FreightTaxTotal));
			} else {
				$TaxTotals[$FreightTaxLine->TaxAuthID]+= ($FreightTaxLine->TaxRate * $_SESSION['CreditItems' . $Identifier]->FreightCost);
				$FreightTaxTotal+= ($FreightTaxLine->TaxRate * $_SESSION['CreditItems' . $Identifier]->FreightCost);
			}
			++$i;
			$TaxGLCodes[$FreightTaxLine->TaxAuthID] = $FreightTaxLine->TaxGLCode;
		}
		echo '</td>';

		echo '<td class="number">' . locale_number_format($FreightTaxTotal, $_SESSION['CreditItems' . $Identifier]->CurrDecimalPlaces) . '</td>
			<td class="number">' . locale_number_format($FreightTaxTotal + $_SESSION['CreditItems' . $Identifier]->FreightCost, $_SESSION['CreditItems' . $Identifier]->CurrDecimalPlaces) . '</td>
			</tr>';

		$TaxTotal+= $FreightTaxTotal;
		$DisplayTotal = locale_number_format($_SESSION['CreditItems' . $Identifier]->total + $_SESSION['CreditItems' . $Identifier]->FreightCost, $_SESSION['CreditItems' . $Identifier]->CurrDecimalPlaces);

		echo '<tr>
				<td colspan="7" class="number">' . _('Credit Totals') . '</td>
				<td class="number"><b>' . $DisplayTotal . '</b></td>
				<td colspan="2"></td>
				<td class="number"><b>' . locale_number_format($TaxTotal, $_SESSION['CreditItems' . $Identifier]->CurrDecimalPlaces) . '</td>
				<td class="number"><b>' . locale_number_format($TaxTotal + ($_SESSION['CreditItems' . $Identifier]->total + $_SESSION['CreditItems' . $Identifier]->FreightCost), $_SESSION['CreditItems' . $Identifier]->CurrDecimalPlaces) . '</b></td>
			</tr>
			</table>';

		/*Now show options for the credit note */

		echo '<br />
				<table>
				<tr>
					<td>' . _('Credit Note Type') . ' :</td>
					<td><select required="required" name="CreditType" onchange="ReloadForm(MainForm.Update)" >';

		if (!isset($_POST['CreditType']) or $_POST['CreditType'] == 'Return') {
			echo '<option selected="selected" value="Return">' . _('Goods returned to store') . '</option>
					<option value="WriteOff">' . _('Goods written off') . '</option>
					<option value="ReverseOverCharge">' . _('Reverse an Overcharge') . '</option>';
		} elseif ($_POST['CreditType'] == 'WriteOff') {
			echo '<option selected="selected" value="WriteOff">' . _('Goods written off') . '</option>
					<option value="Return">' . _('Goods returned to store') . '</option>
					<option value="ReverseOverCharge">' . _('Reverse an Overcharge') . '</option>';
		} elseif ($_POST['CreditType'] == 'ReverseOverCharge') {
			echo '<option selected="selected" value="ReverseOverCharge">' . _('Reverse Overcharge Only') . '</option>
				<option value="Return">' . _('Goods Returned To Store') . '</option>
				<option value="WriteOff">' . _('Good written off') . '</option>';
		}

		echo '</select></td></tr>';

		if (!isset($_POST['CreditType']) or $_POST['CreditType'] == 'Return') {

			/*if the credit note is a return of goods then need to know which location to receive them into */

			echo '<tr>
					<td>' . _('Goods Returned to Location') . ' :</td>
					<td><select required="required" name="Location">';

			$SQL = "SELECT locations.loccode,
							locationname
						FROM locations
						INNER JOIN locationusers
							ON locationusers.loccode=locations.loccode
							AND locationusers.userid='" . $_SESSION['UserID'] . "'
							AND locationusers.canupd=1";
			$Result = DB_query($SQL);

			if (!isset($_POST['Location'])) {
				$_POST['Location'] = $_SESSION['CreditItems' . $Identifier]->Location;
			}
			while ($MyRow = DB_fetch_array($Result)) {

				if ($_POST['Location'] == $MyRow['loccode']) {
					echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
				} else {
					echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
				}
			}
			echo '</select></td></tr>';

		} elseif ($_POST['CreditType'] == 'WriteOff') {
			/* the goods are to be written off to somewhere */

			echo '<tr>
					<td>' . _('Write off the cost of the goods to') . '</td>
					<td><select required="required" name=WriteOffGLCode>';

			$SQL = "SELECT accountcode,
						accountname
					FROM chartmaster
					INNER JOIN accountgroups
						ON chartmaster.groupcode=accountgroups.groupcode
						AND chartmaster.language=accountgroups.language
					WHERE accountgroups.pandl=1
						AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
					ORDER BY accountcode";
			$Result = DB_query($SQL);

			while ($MyRow = DB_fetch_array($Result)) {

				if ($_POST['WriteOffGLCode'] == $MyRow['accountcode']) {
					echo '<option selected="selected" value="' . $MyRow['accountcode'] . '">' . $MyRow['accountcode'] . ' - ' . $MyRow['accountname'] . '</option>';
				} else {
					echo '<option value="' . $MyRow['accountcode'] . '">' . $MyRow['accountcode'] . ' - ' . $MyRow['accountname'] . '</option>';
				}
			}
			echo '</select></td></tr>';
		}
		echo '<tr>
				<td>' . _('Sales person') . ':</td>
				<td><select required="required" name="SalesPerson">';
		$SalesPeopleResult = DB_query("SELECT salesmancode, salesmanname FROM salesman WHERE current=1");
		if (!isset($_POST['SalesPerson']) and $_SESSION['SalesmanLogin'] != NULL) {
			$_SESSION['CreditItems' . $Identifier]->SalesPerson = $_SESSION['SalesmanLogin'];
		}

		while ($SalesPersonRow = DB_fetch_array($SalesPeopleResult)) {
			if ($SalesPersonRow['salesmancode'] == $_SESSION['CreditItems' . $Identifier]->SalesPerson) {
				echo '<option selected="selected" value="' . $SalesPersonRow['salesmancode'] . '">' . $SalesPersonRow['salesmanname'] . '</option>';
			} else {
				echo '<option value="' . $SalesPersonRow['salesmancode'] . '">' . $SalesPersonRow['salesmanname'] . '</option>';
			}
		}

		echo '</select></td>
			</tr>';
		if (!isset($_POST['CreditText'])) {
			$_POST['CreditText'] = '';
		}
		echo '<tr>
				<td>' . _('Credit Note Text') . ' :</td>
		  		<td><textarea name="CreditText" COLS="31" rows="5">' . $_POST['CreditText'] . '</textarea></td>
			</tr>
			</table><br />';

		$OKToProcess = true;
		/*Check for the worst */
		if (isset($_POST['CreditType']) and $_POST['CreditType'] == 'WriteOff' and !isset($_POST['WriteOffGLCode'])) {
			prnMsg(_('The GL code to write off the credit value to must be specified. Please select the appropriate GL code for the selection box'), 'info');
			$OKToProcess = false;
		}
		echo '<div class="centre">
				<input type="submit" name="Update" value="' . _('Update') . '" />
				<input type="submit" name="CancelCredit" value="' . _('Cancel') . '" onclick="return MakeConfirm(\'' . _('Are you sure you wish to cancel the whole of this credit note?') . '\');" />';
		if (!isset($_POST['ProcessCredit']) and $OKToProcess == true) {
			echo '<input type="submit" name="ProcessCredit" value="' . _('Process Credit Note') . '" />
					<br />';
		}
		echo '</div>';
	} // end of if lines
	

	/* Now show the stock item selection search stuff below */

	if (isset($_POST['PartSearch']) and $_POST['PartSearch'] != '' and !isset($_POST['ProcessCredit'])) {

		echo '<input type="hidden" name="PartSearch" value="' . _('Yes Please') . '" />';

		$SQL = "SELECT categoryid,
					categorydescription
				FROM stockcategory
				WHERE stocktype='F'
				ORDER BY categorydescription";

		$Result1 = DB_query($SQL);

		echo '<fieldset>
				<legend class="search">', _('Search Criteria'), '</legend>';

		echo '<field>
				<label for="StockCat">', _('Select a stock category'), '</label>
				<select name="StockCat">';

		echo '<option selected="selected" value="All">', _('All'), '</option>';
		while ($MyRow1 = DB_fetch_array($Result1)) {
			if (isset($_POST['StockCat']) and $_POST['StockCat'] == $MyRow1['categoryid']) {
				echo '<option selected="selected" value="', $MyRow1['categoryid'], '">', $MyRow1['categorydescription'], '</option>';
			} else {
				echo '<option value="', $MyRow1['categoryid'], '">', $MyRow1['categorydescription'], '</option>';
			}
		}
		echo '</select>
			<fieldhelp>', _('Select the stock category to filter by, or choose All to select from all categories'), '</fieldhelp>
		</field>';

		if (!isset($_POST['Keywords'])) {
			$_POST['Keywords'] = '';
		}
		if (!isset($_POST['StockCode'])) {
			$_POST['StockCode'] = '';
		}
		echo '<field>
				<label for="Keywords">', _('Enter text extracts in the description'), ':</label>
				<input type="text" name="Keywords" size="20" maxlength="25" value="', $_POST['Keywords'], '" />
				<fieldhelp>', _('Enter all or part of the item description you are searching for'), '</fieldhelp>
			</field>';

		echo '<h1>', _('OR'), '</h1>';

		echo '<field>
				<label for="StockCode">', _('Enter extract of the Stock Code'), ':</label>
				<input type="text" name="StockCode" size="15" maxlength="18" value="', $_POST['StockCode'], '" />
				<fieldhelp>', _('Enter all or part of the item code you are searching for'), '</fieldhelp>
			</field>';

		echo '</fieldset>';

		echo '<div class="centre">
				<input type="submit" name="Search" value="', _('Search Now'), '" />
				<input type="submit" name="ChangeCustomer" value="', _('Change Customer'), '" />
				<input type="submit" name="Quick" value="', _('Quick Entry'), '" />
			</div>';

		if (isset($SearchResult)) {

			echo '<table cellpadding="2">
					<tr>
						<th>', _('Code'), '</th>
						<th>', _('Description'), '</th>
						<th>', _('Units'), '</th>
					</tr>';

			$j = 1;

			while ($MyRow = DB_fetch_array($SearchResult)) {

				$SupportedImgExt = array('png', 'jpg', 'jpeg');
				//				$ImageFile = reset((glob($_SESSION['part_pics_dir'] . '/' . $MyRow['stockid'] . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE)));
				$SupportedImgExt = array('png', 'jpg', 'jpeg');
				$ImageFileArray = glob($_SESSION['part_pics_dir'] . '/' . $MyRow['stockid'] . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE);
				$ImageFile = reset($ImageFileArray);
				if (extension_loaded('gd') and function_exists('gd_info') and file_exists($ImageFile)) {
					$ImageSource = '<img class="StockImage" src="GetStockImage.php?automake=1&textcolor=FFFFFF&bgcolor=CCCCCC&StockID=' . urlencode($MyRow['stockid']) . '" alt="" />';
				} else if (file_exists($ImageFile)) {
					$ImageSource = '<img class="StockImage" src="' . $ImageFile . '" />';
				} else {
					$ImageSource = _('No Image');
				}

				if (extension_loaded('gd') and function_exists('gd_info') and file_exists($ImageFile) == 1) {
					echo '<tr class="striped_row">
							<td><input type="submit" name="NewItem" value="', $MyRow['stockid'], '" /></td>
							<td>', $MyRow['description'], '</td>
							<td>', $MyRow['units'], '</td>
							<td>', $ImageSource, '</td>
						</tr>';
				} else { //don't try to show the image
					echo '<tr class="striped_row">
							<td><input type="submit" name="NewItem" value="', $MyRow['stockid'], '" /></td>
							<td>', $MyRow['description'], '</td>
							<td>', $MyRow['units'], '</td>
							<td>', _('No Image'), '</td>
						</tr>';
				}
				//end of page full new headings if
				
			}
			//end of while loop
			echo '</table>';
		} //end if SearchResults to show
		
	}
	/*end if part searching required */
	elseif (!isset($_POST['ProcessCredit'])) {
		/*quick entry form */

		/*FORM VARIABLES TO POST TO THE CREDIT NOTE 10 AT A TIME WITH PART CODE AND QUANTITY */
		echo '<table>
				<tr>
					<th colspan="2"><h3>', _('Quick Entry'), '</h3></th>
				</tr>
				<tr>
					<th>', _('Part Code'), '</th>
					<th>', _('Quantity'), '</th>
				</tr>';

		for ($i = 1;$i <= $_SESSION['QuickEntries'];$i++) {

			echo '<tr class="striped_row">
					<td><input type="text" name="part_', $i, '" size="21" maxlength="20" /></td>
					<td><input type="text" class="number" name="qty_', $i, '" size="6" maxlength="6" /></td>
				</tr>';
		}

		echo '</table>';

		echo '<div class="centre">
				<input type="submit" name="QuickEntry" value="', _('Process Entries'), '" />
				<input type="submit" name="PartSearch" value="', _('Search Parts'), '" />
			</div>';

	}

	echo '</form>';
} //end of else not selecting a customer
if (isset($_POST['ProcessCredit']) and $OKToProcess == true) {

	/* SQL to process the postings for sales credit notes...
	 First Get the area where the credit note is to from the branches table */

	$SQL = "SELECT area
		 	FROM custbranch
			WHERE custbranch.debtorno ='" . $_SESSION['CreditItems' . $Identifier]->DebtorNo . "'
			AND custbranch.branchcode = '" . $_SESSION['CreditItems' . $Identifier]->Branch . "'";
	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The area cannot be determined for this customer');
	$DbgMsg = _('The following SQL to insert the customer credit note was used');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

	if ($MyRow = DB_fetch_row($Result)) {
		$Area = $MyRow[0];
	}

	DB_free_result($Result);

	if ($_SESSION['CompanyRecord']['gllink_stock'] == 1 and $_POST['CreditType'] == 'WriteOff' and (!isset($_POST['WriteOffGLCode']) or $_POST['WriteOffGLCode'] == '')) {

		prnMsg(_('For credit notes created to write off the stock a general ledger account is required to be selected. Please select an account to write the cost of the stock off to then click on Process again'), 'error');
		include ('includes/footer.php');
		exit;
	}

	/*Now Get the next credit note number - function in SQL_CommonFunctions*/

	$CreditNo = GetNextTransNo(11);
	$SQLCreditDate = Date('Y-m-d');
	$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']));

	/*Start an SQL transaction */
	DB_Txn_Begin();

	/*Now insert the Credit Note into the DebtorTrans table allocations will have to be done seperately*/

	$SQL = "INSERT INTO debtortrans (transno,
							 		type,
									debtorno,
									branchcode,
									trandate,
									inputdate,
									prd,
									tpe,
									ovamount,
									ovgst,
									ovfreight,
									rate,
									invtext,
									salesperson)
								  VALUES ('" . $CreditNo . "',
								  	'11',
									'" . $_SESSION['CreditItems' . $Identifier]->DebtorNo . "',
									'" . $_SESSION['CreditItems' . $Identifier]->Branch . "',
									'" . $SQLCreditDate . "',
									'" . date('Y-m-d H-i-s') . "',
									'" . $PeriodNo . "',
									'" . $_SESSION['CreditItems' . $Identifier]->DefaultSalesType . "',
									'" . -($_SESSION['CreditItems' . $Identifier]->total) . "',
									'" . -$TaxTotal . "',
								  	'" . -$_SESSION['CreditItems' . $Identifier]->FreightCost . "',
									'" . $_SESSION['CurrencyRate'] . "',
									'" . $_POST['CreditText'] . "',
									'" . $_SESSION['CreditItems' . $Identifier]->SalesPerson . "'
									)";

	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The customer credit note transaction could not be added to the database because');
	$DbgMsg = _('The following SQL to insert the customer credit note was used');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

	$CreditTransID = DB_Last_Insert_ID('debtortrans', 'id');

	/* Insert the tax totals for each tax authority where tax was charged on the invoice */
	foreach ($TaxTotals as $TaxAuthID => $TaxAmount) {

		$SQL = "INSERT INTO debtortranstaxes (debtortransid,
							taxauthid,
							taxamount)
				VALUES ('" . $CreditTransID . "',
						'" . $TaxAuthID . "',
						'" . -$TaxAmount / $_SESSION['CurrencyRate'] . "')";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction taxes records could not be inserted because');
		$DbgMsg = _('The following SQL to insert the debtor transaction taxes record was used');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
	}

	/* Insert stock movements for stock coming back in if the Credit is a return of goods */

	foreach ($_SESSION['CreditItems' . $Identifier]->LineItems as $CreditLine) {

		if ($CreditLine->Quantity > 0) {

			$LocalCurrencyPrice = ($CreditLine->Price / $_SESSION['CurrencyRate']);

			if ($CreditLine->MBflag == 'M' or $CreditLine->MBflag == 'B') {
				/*Need to get the current location quantity will need it later for the stock movement */
				$SQL = "SELECT locstock.quantity
						FROM locstock
						WHERE locstock.stockid='" . $CreditLine->StockID . "'
						AND loccode= '" . $_SESSION['CreditItems' . $Identifier]->Location . "'";

				$Result = DB_query($SQL);
				if (DB_num_rows($Result) == 1) {
					$LocQtyRow = DB_fetch_row($Result);
					$QtyOnHandPrior = $LocQtyRow[0];
				} else {
					/*There must actually be some error this should never happen */
					$QtyOnHandPrior = 0;
				}
			} else {
				$QtyOnHandPrior = 0; //because its a dummy/assembly/kitset part
				
			}

			if ($_POST['CreditType'] == 'ReverseOverCharge') {
				/*Insert a stock movement coming back in to show the credit note  - flag the stockmovement not to show on stock movement enquiries - its is not a real stock movement only for invoice line - also no mods to location stock records*/
				$SQL = "INSERT INTO stockmoves (stockid,
												type,
												transno,
												loccode,
												trandate,
												userid,
												debtorno,
												branchcode,
												price,
												prd,
												reference,
												qty,
												discountpercent,
												standardcost,
												newqoh,
												hidemovt,
												narrative)
										VALUES
											('" . $CreditLine->StockID . "',
											11,
											'" . $CreditNo . "',
											'" . $_SESSION['CreditItems' . $Identifier]->Location . "',
											'" . $SQLCreditDate . "',
											'" . $_SESSION['UserID'] . "',
											'" . $_SESSION['CreditItems' . $Identifier]->DebtorNo . "',
											'" . $_SESSION['CreditItems' . $Identifier]->Branch . "',
											'" . $LocalCurrencyPrice . "',
											'" . $PeriodNo . "',
											'" . $_POST['CreditText'] . "',
											'" . $CreditLine->Quantity . "',
											'" . $CreditLine->DiscountPercent . "',
											'" . $CreditLine->StandardCost . "',
											'" . $QtyOnHandPrior . "',
											1,
											'" . $CreditLine->Narrative . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records could not be inserted because');
				$DbgMsg = _('The following SQL to insert the stock movement records for the purpose of display on the credit note was used');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			} else { //its a return or a write off need to record goods coming in first
				if ($CreditLine->MBflag == "M" or $CreditLine->MBflag == "B") {
					$SQL = "INSERT INTO stockmoves (stockid,
													type,
													transno,
													loccode,
													trandate,
													userid,
													debtorno,
													branchcode,
													price,
													prd,
													qty,
													discountpercent,
													standardcost,
													reference,
													newqoh,
													narrative)
												VALUES (
													'" . $CreditLine->StockID . "',
													11,
													" . $CreditNo . ",
													'" . $_SESSION['CreditItems' . $Identifier]->Location . "',
													'" . $SQLCreditDate . "',
													'" . $_SESSION['UserID'] . "',
													'" . $_SESSION['CreditItems' . $Identifier]->DebtorNo . "',
													'" . $_SESSION['CreditItems' . $Identifier]->Branch . "',
													'" . $LocalCurrencyPrice . "',
													'" . $PeriodNo . "',
													'" . $CreditLine->Quantity . "',
													'" . $CreditLine->DiscountPercent . "',
													'" . $CreditLine->StandardCost . "',
													'" . $_POST['CreditText'] . "',
													'" . ($QtyOnHandPrior + $CreditLine->Quantity) . "',
													'" . $CreditLine->Narrative . "'
												)";

				} else {
					/*its an assembly/kitset or dummy so don't attempt to figure out new qoh */
					$SQL = "INSERT INTO stockmoves (stockid,
													type,
													transno,
													loccode,
													trandate,
													userid,
													debtorno,
													branchcode,
													price,
													prd,
													qty,
													discountpercent,
													standardcost,
													reference,
													narrative)
												VALUES (
													'" . $CreditLine->StockID . "',
													11,
													'" . $CreditNo . "',
													'" . $_SESSION['CreditItems' . $Identifier]->Location . "',
													'" . $SQLCreditDate . "',
													'" . $_SESSION['UserID'] . "',
													'" . $_SESSION['CreditItems' . $Identifier]->DebtorNo . "',
													'" . $_SESSION['CreditItems' . $Identifier]->Branch . "',
													'" . $LocalCurrencyPrice . "',
													'" . $PeriodNo . "',
													'" . $CreditLine->Quantity . "',
													'" . $CreditLine->DiscountPercent . "',
													'" . $CreditLine->StandardCost . "',
													'" . $_POST['CreditText'] . "',
													'" . $CreditLine->Narrative . "'
													)";
				}

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records could not be inserted because');
				$DbgMsg = _('The following SQL to insert the stock movement records was used');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

				/*Get the stockmoveno from above - need to ref StockMoveTaxes and possibly SerialStockMoves */
				$StkMoveNo = DB_Last_Insert_ID('stockmoves', 'stkmoveno');

				/*Insert the taxes that applied to this line */
				foreach ($CreditLine->Taxes as $Tax) {

					$SQL = "INSERT INTO stockmovestaxes (stkmoveno,
										taxauthid,
										taxrate,
										taxcalculationorder,
										taxontax)
							VALUES ('" . $StkMoveNo . "',
								'" . $Tax->TaxAuthID . "',
								'" . $Tax->TaxRate . "',
								'" . $Tax->TaxCalculationOrder . "',
								'" . $Tax->TaxOnTax . "')";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Taxes and rates applicable to this credit note line item could not be inserted because');
					$DbgMsg = _('The following SQL to insert the stock movement tax detail records was used');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				}

				if (($CreditLine->MBflag == 'M' or $CreditLine->MBflag == 'B') and $CreditLine->Controlled == 1) {
					/*Need to do the serial stuff in here now */

					foreach ($CreditLine->SerialItems as $Item) {

						/*1st off check if StockSerialItems already exists */
						$SQL = "SELECT COUNT(*)
								FROM stockserialitems
								WHERE stockid='" . $CreditLine->StockID . "'
								AND loccode='" . $_SESSION['CreditItems' . $Identifier]->Location . "'
								AND serialno='" . $Item->BundleRef . "'";
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The existence of the serial stock item record could not be determined because');
						$DbgMsg = _('The following SQL to find out if the serial stock item record existed already was used');
						$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
						$MyRow = DB_fetch_row($Result);

						if ($MyRow[0] == 0) {
							/*The StockSerialItem record didnt exist
							 so insert a new record */
							$SQL = "INSERT INTO stockserialitems ( stockid,
																loccode,
																serialno,
																quantity)
																VALUES (
																'" . $CreditLine->StockID . "',
																'" . $_SESSION['CreditItems' . $Identifier]->Location . "',
																'" . $Item->BundleRef . "',
																'" . $Item->BundleQty . "'
																)";

							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The new serial stock item record could not be inserted because');
							$DbgMsg = _('The following SQL to insert the new serial stock item record was used');
							$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
						} else {
							/*Update the existing StockSerialItems record */
							$SQL = "UPDATE stockserialitems SET quantity= quantity + " . $Item->BundleQty . "
									WHERE stockid='" . $CreditLine->StockID . "'
									AND loccode='" . $_SESSION['CreditItems' . $Identifier]->Location . "'
									AND serialno='" . $Item->BundleRef . "'";

							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated because');
							$DbgMsg = _('The following SQL to update the serial stock item record was used');
							$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
						}
						/* now insert the serial stock movement */

						$SQL = "INSERT INTO stockserialmoves ( stockmoveno,
															stockid,
															serialno,
															moveqty)
														VALUES (
															'" . $StkMoveNo . "',
															'" . $CreditLine->StockID . "',
															'" . $Item->BundleRef . "',
															'" . $Item->BundleQty . "')";
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
						$DbgMsg = _('The following SQL to insert the serial stock movement record was used');
						$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

					}
					/* foreach serial item in the serialitems array */

				}
				/*end if the credit line is a controlled item */

			}
			/*End of its a return or a write off */

			if ($_POST['CreditType'] == 'Return') {

				/* Update location stock records if not a dummy stock item */

				if ($CreditLine->MBflag == 'B' or $CreditLine->MBflag == 'M') {

					$SQL = "UPDATE locstock
							SET locstock.quantity = locstock.quantity + " . $CreditLine->Quantity . "
							WHERE locstock.stockid = '" . $CreditLine->StockID . "'
							AND locstock.loccode = '" . $_SESSION['CreditItems' . $Identifier]->Location . "'";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Location stock record could not be updated because');
					$DbgMsg = _('The following SQL to update the location stock record was used');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

				} else if ($CreditLine->MBflag == 'A') {
					/* its an assembly */
					/*Need to get the BOM for this part and make stock moves
					for the componentsand of course update the Location stock
					balances for all the components*/

					$StandardCost = 0;
					/*To start with then accumulate the cost of the comoponents
					 for use in journals later on */

					$SQL = "SELECT bom.component,
									bom.quantity,
									stockcosts.materialcost+stockcosts.labourcoststockcosts.overheadcost AS standard
							FROM bom
							LEFT JOIN stockcosts
								ON stockcosts.stockid=bom.component
								AND stockcosts.succeeded=0
							WHERE bom.parent='" . $CreditLine->StockID . "'
								AND bom.effectiveto > CURRENT_DATE
								AND bom.effectiveafter <= CURRENT_DATE";

					$ErrMsg = _('Could not retrieve assembly components from the database for') . ' ' . $CreditLine->StockID . ' ' . _('because');
					$DbgMsg = _('The SQL that failed was');
					$AssResult = DB_query($SQL, $ErrMsg, $DbgMsg, true);

					while ($AssParts = DB_fetch_array($AssResult)) {

						$StandardCost+= $AssParts['standard'] * $AssParts['quantity'];

						/*Need to get the current location quantity will need it later for the stock movement */
						$SQL = "SELECT locstock.quantity
						   		FROM locstock
								WHERE locstock.stockid='" . $AssParts['component'] . "'
								AND locstock.loccode= '" . $_SESSION['CreditItems' . $Identifier]->Location . "'";

						$Result = DB_query($SQL);
						if (DB_num_rows($Result) == 1) {
							$LocQtyRow = DB_fetch_row($Result);
							$QtyOnHandPrior = $LocQtyRow[0];
						} else {
							/*There must actually be some error this should never happen */
							$QtyOnHandPrior = 0;
						}

						/*Add stock movements for the assembly component items */
						$SQL = "INSERT INTO stockmoves (stockid,
														type,
														transno,
														loccode,
														trandate,
														userid,
														debtorno,
														branchcode,
														prd,
														reference,
														qty,
														standardcost,
														show_on_inv_crds,
														newqoh)
												VALUES (
													'" . $AssParts['component'] . "',
													11,
													'" . $CreditNo . "',
													'" . $_SESSION['CreditItems' . $Identifier]->Location . "',
													'" . $SQLCreditDate . "',
													'" . $_SESSION['UserID'] . "',
													'" . $_SESSION['CreditItems' . $Identifier]->DebtorNo . "',
													'" . $_SESSION['CreditItems' . $Identifier]->Branch . "',
													'" . $PeriodNo . "',
													'" . _('Assembly') . ': ' . $CreditLine->StockID . "',
													'" . $AssParts['quantity'] * $CreditLine->Quantity . "',
													'" . $AssParts['standard'] . "',
													0,
													'" . ($QtyOnHandPrior + ($AssParts['quantity'] * $CreditLine->Quantity)) . "'
													)";

						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records for the assembly components of') . ' ' . $CreditLine->StockID . ' ' . _('could not be inserted because');
						$DbgMsg = _('The following SQL to insert the assembly components stock movement records was used');
						$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

						/*Update the stock quantities for the assembly components */
						$SQL = "UPDATE locstock
					   		SET locstock.quantity = locstock.quantity + " . $AssParts['quantity'] * $CreditLine->Quantity . "
							WHERE locstock.stockid = '" . $AssParts['component'] . "'
							AND locstock.loccode = '" . $_SESSION['CreditItems' . $Identifier]->Location . "'";

						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Location stock record could not be updated for an assembly component because');
						$DbgMsg = _('The following SQL to update the component location stock record was used');
						$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
					}
					/* end of assembly explosion and updates */

					/*Update the cart with the recalculated standard cost
					 from the explosion of the assembly's components*/
					$_SESSION['CreditItems' . $Identifier]->LineItems[$CreditLine->LineNumber]->StandardCost = $StandardCost;
					$CreditLine->StandardCost = $StandardCost;
				}
				/*end of its a return of stock */
			} elseif ($_POST['CreditType'] == 'WriteOff') {
				/*its a stock write off */

				if ($CreditLine->MBflag == 'B' or $CreditLine->MBflag == 'M') {
					/* Insert stock movements for the
					 item being written off - with unit cost */
					$SQL = "INSERT INTO stockmoves ( stockid,
													type,
													transno,
													loccode,
													trandate,
													userid,
													debtorno,
													branchcode,
													price,
													prd,
													qty,
													discountpercent,
													standardcost,
													reference,
													show_on_inv_crds,
													newqoh,
													narrative)
												VALUES (
													'" . $CreditLine->StockID . "',
													11,
													'" . $CreditNo . "',
													'" . $_SESSION['CreditItems' . $Identifier]->Location . "',
													'" . $SQLCreditDate . "',
													'" . $_SESSION['UserID'] . "',
													'" . $_SESSION['CreditItems' . $Identifier]->DebtorNo . "',
													'" . $_SESSION['CreditItems' . $Identifier]->Branch . "',
													'" . $LocalCurrencyPrice . "',
													'" . $PeriodNo . "',
													'" . -$CreditLine->Quantity . "',
													'" . $CreditLine->DiscountPercent . "',
													'" . $CreditLine->StandardCost . "',
													'" . $_POST['CreditText'] . "',
													0,
													'" . $QtyOnHandPrior . "',
													'" . $CreditLine->Narrative . "'
													)";

				} else {
					/* its an assembly, so dont figure out the new qoh */

					$SQL = "INSERT INTO stockmoves (stockid,
													type,
													transno,
													loccode,
													trandate,
													userid,
													debtorno,
													branchcode,
													price,
													prd,
													qty,
													discountpercent,
													standardcost,
													reference,
													show_on_inv_crds)
												VALUES (
													'" . $CreditLine->StockID . "',
													11,
													'" . $CreditNo . "',
													'" . $_SESSION['CreditItems' . $Identifier]->Location . "',
													'" . $SQLCreditDate . "',
													'" . $_SESSION['UserID'] . "',
													'" . $_SESSION['CreditItems' . $Identifier]->DebtorNo . "',
													'" . $_SESSION['CreditItems' . $Identifier]->Branch . "',
													'" . $LocalCurrencyPrice . "',
													'" . $PeriodNo . "',
													'" . -$CreditLine->Quantity . "',
													'" . $CreditLine->DiscountPercent . "',
													'" . $CreditLine->StandardCost . "',
													'" . $_POST['CreditText'] . "',
													0)";

				}

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement record to write the stock off could not be inserted because');
				$DbgMsg = _('The following SQL to insert the stock movement to write off the stock was used');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

				if (($CreditLine->MBflag == 'M' or $CreditLine->MBflag == 'B') and $CreditLine->Controlled == 1) {
					/*Its a write off too still so need to process the serial items
					 written off */

					$StkMoveNo = DB_Last_Insert_ID('stockmoves', 'stkmoveno');

					foreach ($CreditLine->SerialItems as $Item) {
						/*no need to check StockSerialItems record exists
						 it would have been added by the return stock movement above */
						$SQL = "UPDATE stockserialitems SET quantity= quantity - " . $Item->BundleQty . "
								WHERE stockid='" . $CreditLine->StockID . "'
								AND loccode='" . $_SESSION['CreditItems' . $Identifier]->Location . "'
								AND serialno='" . $Item->BundleRef . "'";

						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated for the write off because');
						$DbgMsg = _('The following SQL to update the serial stock item record was used');
						$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

						/* now insert the serial stock movement */

						$SQL = "INSERT INTO stockserialmoves ( stockmoveno,
															stockid,
															serialno,
															moveqty)
														VALUES (
															'" . $StkMoveNo . "',
															'" . $CreditLine->StockID . "',
															'" . $Item->BundleRef . "',
															'" . -$Item->BundleQty . "'
															)";
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record for the write off could not be inserted because');
						$DbgMsg = _('The following SQL to insert the serial stock movement write off record was used');
						$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

					}
					/* foreach serial item in the serialitems array */

				}
				/*end if the credit line is a controlled item */

			}
			/*end if its a stock write off */

			/*Insert Sales Analysis records use links to the customer master and branch tables to ensure that if
			the salesman or area has changed a new record is inserted for the customer and salesman of the new
			set up. Considered just getting the area and salesman from the branch table but these can alter and the
			sales analysis needs to reflect the sales made before and after the changes*/

			$SalesValue = 0;
			if ($_SESSION['CurrencyRate'] > 0) {
				$SalesValue = $CreditLine->Price * $CreditLine->Quantity / $_SESSION['CurrencyRate'];
			}

			$SQL = "SELECT COUNT(*),
							salesanalysis.stkcategory,
							salesanalysis.area
						FROM salesanalysis,
							custbranch,
							stockmaster
						WHERE salesanalysis.stkcategory=stockmaster.categoryid
						AND salesanalysis.stockid=stockmaster.stockid
						AND salesanalysis.cust=custbranch.debtorno
						AND salesanalysis.custbranch=custbranch.branchcode
						AND salesanalysis.area=custbranch.area
						AND salesanalysis.salesperson='" . $_SESSION['CreditItems' . $Identifier]->SalesPerson . "'
						AND salesanalysis.typeabbrev ='" . $_SESSION['CreditItems' . $Identifier]->DefaultSalesType . "'
						AND salesanalysis.periodno='" . $PeriodNo . "'
						AND salesanalysis.cust = '" . $_SESSION['CreditItems' . $Identifier]->DebtorNo . "'
						AND salesanalysis.custbranch = '" . $_SESSION['CreditItems' . $Identifier]->Branch . "'
						AND salesanalysis.stockid = '" . $CreditLine->StockID . "'
						AND salesanalysis.budgetoractual=1
						GROUP BY salesanalysis.stkcategory,
							salesanalysis.area,
							salesanalysis.salesperson";

			$ErrMsg = _('The count to check for existing Sales analysis records could not run because');
			$DbgMsg = _('SQL to count the no of sales analysis records');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			$MyRow = DB_fetch_array($Result);

			if ($MyRow[0] > 0) {
				/*Update the existing record that already exists */

				if ($_POST['CreditType'] == 'ReverseOverCharge') {

					/*No updates to qty or cost data */

					$SQL = "UPDATE salesanalysis SET amt=amt-" . $SalesValue . ",
													disc=disc-" . $CreditLine->DiscountPercent * $SalesValue . "
							WHERE salesanalysis.area='" . $MyRow['area'] . "'
							AND salesanalysis.salesperson='" . $_SESSION['CreditItems' . $Identifier]->SalesPerson . "'
							AND salesanalysis.typeabbrev ='" . $_SESSION['CreditItems' . $Identifier]->DefaultSalesType . "'
							AND salesanalysis.periodno = '" . $PeriodNo . "'
							AND salesanalysis.cust = '" . $_SESSION['CreditItems' . $Identifier]->DebtorNo . "'
							AND salesanalysis.custbranch = '" . $_SESSION['CreditItems' . $Identifier]->Branch . "'
							AND salesanalysis.stockid = '" . $CreditLine->StockID . "'
							AND salesanalysis.stkcategory ='" . $MyRow['stkcategory'] . "'
							AND salesanalysis.budgetoractual=1";

				} else {

					$SQL = "UPDATE salesanalysis SET Amt=Amt-" . $SalesValue . ",
													Cost=Cost-" . $CreditLine->StandardCost * $CreditLine->Quantity . ",
													Qty=Qty-" . $CreditLine->Quantity . ",
													Disc=Disc-" . $CreditLine->DiscountPercent * $SalesValue . "
							WHERE salesanalysis.area='" . $MyRow['area'] . "'
							AND salesanalysis.salesperson='" . $_SESSION['CreditItems' . $Identifier]->SalesPerson . "'
							AND salesanalysis.typeabbrev ='" . $_SESSION['CreditItems' . $Identifier]->DefaultSalesType . "'
							AND salesanalysis.periodno = '" . $PeriodNo . "'
							AND salesanalysis.cust = '" . $_SESSION['CreditItems' . $Identifier]->DebtorNo . "'
							AND salesanalysis.custbranch = '" . $_SESSION['CreditItems' . $Identifier]->Branch . "'
							AND salesanalysis.stockid = '" . $CreditLine->StockID . "'
							AND salesanalysis.stkcategory ='" . $MyRow['stkcategory'] . "'
							AND salesanalysis.budgetoractual=1";
				}

			} else {
				/* insert a new sales analysis record */

				if ($_POST['CreditType'] == 'ReverseOverCharge') {

					$SQL = "INSERT salesanalysis (typeabbrev,
												periodno,
												amt,
												cust,
												custbranch,
												qty,
												disc,
												stockid,
												area,
												budgetoractual,
												salesperson,
												stkcategory)
										 SELECT '" . $_SESSION['CreditItems' . $Identifier]->DefaultSalesType . "',
												'" . $PeriodNo . "',
												'" . -$SalesValue . "',
												'" . $_SESSION['CreditItems' . $Identifier]->DebtorNo . "',
												'" . $_SESSION['CreditItems' . $Identifier]->Branch . "',
												0,
												'" . -$CreditLine->DiscountPercent * $SalesValue . "',
												'" . $CreditLine->StockID . "',
												custbranch.area,
												1,
												'" . $_SESSION['CreditItems' . $Identifier]->SalesPerson . "',
												stockmaster.categoryid
										FROM stockmaster, custbranch
										WHERE stockmaster.stockid = '" . $CreditLine->StockID . "'
										AND custbranch.debtorno = '" . $_SESSION['CreditItems' . $Identifier]->DebtorNo . "'
										AND custbranch.branchcode='" . $_SESSION['CreditItems' . $Identifier]->Branch . "'";

				} else {

					$SQL = "INSERT salesanalysis ( typeabbrev,
												periodno,
												amt,
												cost,
												cust,
												custbranch,
												qty,
												disc,
												stockid,
												area,
												budgetoractual,
												salesperson,
												stkcategory)
										SELECT '" . $_SESSION['CreditItems' . $Identifier]->DefaultSalesType . "',
												'" . $PeriodNo . "',
												'" . -$SalesValue . "',
												'" . -$CreditLine->StandardCost * $CreditLine->Quantity . "',
												'" . $_SESSION['CreditItems' . $Identifier]->DebtorNo . "',
												'" . $_SESSION['CreditItems' . $Identifier]->Branch . "',
												'" . -$CreditLine->Quantity . "',
												'" . -$CreditLine->DiscountPercent * $SalesValue . "',
												'" . $CreditLine->StockID . "',
												custbranch.area,
												1,
												'" . $_SESSION['CreditItems' . $Identifier]->SalesPerson . "',
												stockmaster.categoryid
										FROM stockmaster,
												custbranch
										WHERE stockmaster.stockid = '" . $CreditLine->StockID . "'
										AND custbranch.debtorno = '" . $_SESSION['CreditItems' . $Identifier]->DebtorNo . "'
										AND custbranch.branchcode='" . $_SESSION['CreditItems' . $Identifier]->Branch . "'";
				}
			}

			$ErrMsg = _('The sales analysis record for this credit note could not be added because');
			$DbgMsg = _('The following SQL to insert the sales analysis record was used');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			/* If GLLink_Stock then insert GLTrans to either debit stock or an expense
			depending on the valuve of $_POST['CreditType'] and then credit the cost of sales
			at standard cost*/

			if ($_SESSION['CompanyRecord']['gllink_stock'] == 1 and $CreditLine->StandardCost != 0 and $_POST['CreditType'] != 'ReverseOverCharge') {

				/*first reverse credit the cost of sales entry*/
				$COGSAccount = GetCOGSGLAccount($Area, $CreditLine->StockID, $_SESSION['CreditItems' . $Identifier]->DefaultSalesType);
				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
										VALUES (
											11,
											'" . $CreditNo . "',
											'" . $SQLCreditDate . "',
											'" . $PeriodNo . "',
											'" . $COGSAccount . "',
											'" . $_SESSION['CreditItems' . $Identifier]->DebtorNo . " - " . $CreditLine->StockID . " x " . $CreditLine->Quantity . " @ " . $CreditLine->StandardCost . "',
											'" . ($CreditLine->StandardCost * -$CreditLine->Quantity) . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The cost of the stock credited GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

				if ($_POST['CreditType'] == 'WriteOff') {

					/* The double entry required is to reverse the cost of sales entry as above
					 then debit the expense account the stock is to written off to */

					$SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount)
								VALUES (11,
										'" . $CreditNo . "',
										'" . $SQLCreditDate . "',
										'" . $PeriodNo . "',
										'" . $_POST['WriteOffGLCode'] . "',
										'" . $_SESSION['CreditItems' . $Identifier]->DebtorNo . " - " . $CreditLine->StockID . " x " . $CreditLine->Quantity . " @ " . $CreditLine->StandardCost . "',
										'" . ($CreditLine->StandardCost * $CreditLine->Quantity) . "'
										)";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The cost of the stock credited GL posting could not be inserted because');
					$DbgMsg = _('The following SQL to insert the GLTrans record was used');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				} else {

					/*the goods are coming back into stock so debit the stock account*/
					$StockGLCode = GetStockGLCode($CreditLine->StockID);
					$SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount)
										VALUES (11,
											'" . $CreditNo . "',
											'" . $SQLCreditDate . "',
											'" . $PeriodNo . "',
											'" . $StockGLCode['stockact'] . "',
											'" . $_SESSION['CreditItems' . $Identifier]->DebtorNo . " - " . $CreditLine->StockID . " x " . $CreditLine->Quantity . " @ " . $CreditLine->StandardCost . "',
											'" . ($CreditLine->StandardCost * $CreditLine->Quantity) . "'
											)";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock side (or write off) of the cost of sales GL posting could not be inserted because');
					$DbgMsg = _('The following SQL to insert the GLTrans record was used');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				}

			}
			/* end of if GL and stock integrated and standard cost !=0 */

			if ($_SESSION['CompanyRecord']['gllink_debtors'] == 1 and $CreditLine->Price != 0) {

				//Post sales transaction to GL credit sales
				$SalesGLAccounts = GetSalesGLAccount($Area, $CreditLine->StockID, $_SESSION['CreditItems' . $Identifier]->DefaultSalesType);

				$SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount)
										VALUES (11,
											'" . $CreditNo . "',
											'" . $SQLCreditDate . "',
											'" . $PeriodNo . "',
											'" . $SalesGLAccounts['salesglcode'] . "',
											'" . $_SESSION['CreditItems' . $Identifier]->DebtorNo . " - " . $CreditLine->StockID . " x " . $CreditLine->Quantity . " @ " . $CreditLine->Price . "',
											'" . (($CreditLine->Price * $CreditLine->Quantity) / $_SESSION['CurrencyRate']) . "'
											)";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The credit note GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

				if ($CreditLine->DiscountPercent != 0) {

					$SQL = "INSERT INTO gltrans (type,
													typeno,
													trandate,
													periodno,
													account,
													narrative,
													amount)
									VALUES (11,
										'" . $CreditNo . "',
										'" . $SQLCreditDate . "',
										'" . $PeriodNo . "',
										'" . $SalesGLAccounts['discountglcode'] . "',
										'" . $_SESSION['CreditItems' . $Identifier]->DebtorNo . " - " . $CreditLine->StockID . " @ " . ($CreditLine->DiscountPercent * 100) . "%',
										'" . -(($CreditLine->Price * $CreditLine->Quantity * $CreditLine->DiscountPercent) / $_SESSION['CurrencyRate']) . "'
										)";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The credit note discount GL posting could not be inserted because');
					$DbgMsg = _('The following SQL to insert the GLTrans record was used');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				}
				/* end of if discount not equal to 0 */
			}
			/*end of if sales integrated with debtors */
		}
		/*Quantity credited is more than 0 */
	}
	/*end of CreditLine loop */

	if ($_SESSION['CompanyRecord']['gllink_debtors'] == 1) {

		/*Post credit note transaction to GL credit debtors, debit freight re-charged and debit sales */
		if (($_SESSION['CreditItems' . $Identifier]->total + $_SESSION['CreditItems' . $Identifier]->FreightCost + $TaxTotal) != 0) {
			$SQL = "INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										narrative,
										amount)
							VALUES (11,
								'" . $CreditNo . "',
								'" . $SQLCreditDate . "',
								'" . $PeriodNo . "',
								'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
								'" . $_SESSION['CreditItems' . $Identifier]->DebtorNo . "',
								'" . -(($_SESSION['CreditItems' . $Identifier]->total + $_SESSION['CreditItems' . $Identifier]->FreightCost + $TaxTotal) / $_SESSION['CurrencyRate']) . "')";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The total debtor GL posting for the credit note could not be inserted because');
			$DbgMsg = _('The following SQL to insert the GLTrans record was used');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
		}
		if ($_SESSION['CreditItems' . $Identifier]->FreightCost != 0) {
			$SQL = "INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										narrative,
										amount)
							VALUES (11,
								'" . $CreditNo . "',
								'" . $SQLCreditDate . "',
								'" . $PeriodNo . "',
								'" . $_SESSION['CompanyRecord']['freightact'] . "',
								'" . $_SESSION['CreditItems' . $Identifier]->DebtorNo . "',
								'" . ($_SESSION['CreditItems' . $Identifier]->FreightCost / $_SESSION['CurrencyRate']) . "')";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The freight GL posting for this credit note could not be inserted because');
			$DbgMsg = _('The following SQL to insert the GLTrans record was used');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
		}
		foreach ($TaxTotals as $TaxAuthID => $TaxAmount) {
			if ($TaxAmount != 0) {
				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount )
										VALUES (11,
											'" . $CreditNo . "',
											'" . $SQLCreditDate . "',
											'" . $PeriodNo . "',
											'" . $TaxGLCodes[$TaxAuthID] . "',
											'" . $_SESSION['CreditItems' . $Identifier]->DebtorNo . "',
											'" . ($TaxAmount / $_SESSION['CurrencyRate']) . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The tax GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
			}
		}

		EnsureGLEntriesBalance(11, $CreditNo);

	}
	/*end of if Sales and GL integrated */

	DB_Txn_Commit();

	unset($_SESSION['CreditItems' . $Identifier]->LineItems);
	unset($_SESSION['CreditItems' . $Identifier]);

	echo _('Credit Note number') . ' ' . $CreditNo . ' ' . _('processed') . '<br />';
	echo '<a target="_blank" href="' . $RootPath . '/PrintCustTrans.php?FromTransNo=' . urlencode($CreditNo) . '&InvOrCredit=Credit">' . _('Show this Credit Note on screen') . '</a><br />';
	echo '<a href="' . $RootPath . '/PrintCustTrans.php?FromTransNo=' . urlencode($CreditNo) . '&InvOrCredit=Credit&PrintPDF=True">' . _('Print this Credit Note') . '</a>';
	echo '<br /><a href="' . $RootPath . '/SelectCreditItems.php">' . _('Enter Another Credit Note') . '</a>';

}
/*end of process credit note */

include ('includes/footer.php');
?>