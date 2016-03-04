<?php

function OpenCartToKwaMojaSync($ShowMessages, $oc_tableprefix, $EmailText = '') {
	//	$begintime = time_start();

	// connect to opencart DB
	DB_Txn_Begin();

	// check last time we run this script, so we know which records need to update from OC to KwaMoja
	$LastTimeRun = CheckLastTimeRun('OpenCartToKwaMoja');
	if ($ShowMessages) {
		prnMsg('This script was last run on: ' . $LastTimeRun . ' Server time difference: ' . SERVER_TO_LOCAL_TIME_DIFFERENCE, 'success');
		prnMsg('Server time now: ' . GetServerTimeNow(SERVER_TO_LOCAL_TIME_DIFFERENCE), 'success');
	}
	if ($EmailText != '') {
		$EmailText = $EmailText . 'OpenCart to KwaMoja Sync was last run on: ' . $LastTimeRun . "\n\n" . 'Server time difference: ' . SERVER_TO_LOCAL_TIME_DIFFERENCE . "\n\n" . 'Server time now: ' . GetServerTimeNow(SERVER_TO_LOCAL_TIME_DIFFERENCE) . "\n\n";
	}
	// update order information
	$EmailText = SyncOrderInformation($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText);

	// update payment information
	$EmailText = SyncPaypalPaymentInformation($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText);

	// We are done!
	SetLastTimeRun('OpenCartToKwaMoja');
	DB_Txn_Commit();
	if ($ShowMessages) {
		//		time_finish($begintime);
	}
	return $EmailText;
}

function SyncOrderInformation($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText = '') {
	$ServerNow = GetServerTimeNow(SERVER_TO_LOCAL_TIME_DIFFERENCE);
	$Today = date('Y-m-d');

	if ($EmailText != '') {
		$EmailText = $EmailText . "Sync OpenCart Order Information --> Server Time = " . $ServerNow . " --> KwaMoja Time = " . date('d/M/Y H:i:s') . "\n\n";
	}

	$i = 0;
	$SQL = "SELECT 	" . $oc_tableprefix . "order.order_id,
					" . $oc_tableprefix . "order.customer_id,
					" . $oc_tableprefix . "order.firstname AS customerfirstname,
					" . $oc_tableprefix . "order.lastname AS customerlastname,
					" . $oc_tableprefix . "order.email,
					" . $oc_tableprefix . "order.telephone,
					" . $oc_tableprefix . "order.fax,
					" . $oc_tableprefix . "order.comment,
					" . $oc_tableprefix . "order.payment_firstname AS paymentfirstname,
					" . $oc_tableprefix . "order.payment_lastname AS paymentlastname,
					" . $oc_tableprefix . "order.payment_company AS paymentcompany,
					" . $oc_tableprefix . "order.payment_address_1,
					" . $oc_tableprefix . "order.payment_address_2,
					" . $oc_tableprefix . "order.payment_city,
					" . $oc_tableprefix . "order.payment_postcode,
					" . $oc_tableprefix . "order.payment_zone,
					" . $oc_tableprefix . "order.payment_country,
					" . $oc_tableprefix . "order.payment_method,
					" . $oc_tableprefix . "order.shipping_firstname AS shippingfirstname,
					" . $oc_tableprefix . "order.shipping_lastname AS shippinglastname,
					" . $oc_tableprefix . "order.shipping_company AS shippingcompany,
					" . $oc_tableprefix . "order.shipping_address_1,
					" . $oc_tableprefix . "order.shipping_address_2,
					" . $oc_tableprefix . "order.shipping_city,
					" . $oc_tableprefix . "order.shipping_postcode,
					" . $oc_tableprefix . "order.shipping_zone,
					" . $oc_tableprefix . "order.shipping_country,
					" . $oc_tableprefix . "order.shipping_method,
					" . $oc_tableprefix . "order.shipping_code,
					" . $oc_tableprefix . "order.total,
					" . $oc_tableprefix . "order.order_status_id,
					" . $oc_tableprefix . "order.currency_code,
					" . $oc_tableprefix . "order.currency_value,
					" . $oc_tableprefix . "order.date_modified
			FROM " . $oc_tableprefix . "order
			WHERE " . $oc_tableprefix . "order.order_status_id >= 1
				AND ( " . $oc_tableprefix . "order.date_added >= '" . $LastTimeRun . "'
					OR " . $oc_tableprefix . "order.date_modified >= '" . $LastTimeRun . "')
			ORDER BY " . $oc_tableprefix . "order.order_id";

	$Result = DB_query_oc($SQL);
	if (DB_num_rows($Result) != 0) {
		if ($ShowMessages) {
			echo '<p class="page_title_text" align="center"><strong>' . _('Orders from OpenCart') . '</strong></p>';
			echo '<div>';
			$TableHeader = '<tr>
								<th>' . _('OC #') . '</th>
								<th>' . _('KwaMoja #') . '</th>
								<th>' . _('Name') . '</th>
								<th>' . _('eMail') . '</th>
								<th>' . _('Shipping Cost') . '</th>
								<th>' . _('Shipper') . '</th>
								<th>' . _('Currency') . '</th>
								<th>' . _('Country') . '</th>
								<th>' . _('Action') . '</th>
							</tr>';

			$TableHeaderForItems = '<tr>
								<th>' . _('OC #') . '</th>
								<th>' . _('KwaMoja #') . '</th>
								<th>' . _('OrderLine') . '</th>
								<th>' . _('Code') . '</th>
								<th>' . _('Unit Price') . '</th>
								<th>' . _('Quantity') . '</th>
								<th>' . _('Action') . '</th>
							</tr>';
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update OpenCart orders in KwaMoja failed');
		$InsertErrMsg = _('The SQL to insert OpenCart orders in KwaMoja failed');

		$k = 0; //row colour counter
		while ($MyRow = DB_fetch_array($Result)) {
			if ($ShowMessages) {
				echo '<table class="selection">';
				echo $TableHeader;
				echo '<tr class="EvenTableRows">';
			}
			/* FIELD MATCHING */
			if (defined('KWAMOJA_ONLINE_CUSTOMER_CODE_PREFIX')) {
				$CustomerCode = GetKwaMojaCustomerIdFromCurrency($MyRow['currency_code']);
			} else if (!DataExistsInKwaMoja('debtorsmaster', 'debtorno', $MyRow['customer_id'])) {
				CreateDebtorFromOpenCartID($MyRow['customer_id'], $MyRow['currency_code'], $oc_tableprefix);
				$CustomerCode = $MyRow['customer_id'];
			} else {
				$CustomerCode = $MyRow['customer_id'];
			}
			$CustomerName = $MyRow['customerfirstname'] . ' ' . $MyRow['customerlastname'];
			$PaymentName = $MyRow['paymentfirstname'] . ' ' . $MyRow['paymentlastname'];
			$ShippingName = $MyRow['shippingfirstname'] . ' ' . $MyRow['shippinglastname'];
			$SalesType = OPENCART_DEFAULT_CUSTOMER_SALES_TYPE;
			$DefaultShipVia = GetKwaMojaShippingMethod($MyRow['shipping_method']);
			$Quotation = 1; // is NOT a firm order until we check the payments
			$FreightCost = RoundPriceFromCart(GetTotalFromOrder("shipping", $MyRow['order_id'], $oc_tableprefix) * $MyRow['currency_value'], $MyRow['currency_code']);
			$CouponDiscount = RoundPriceFromCart(GetTotalFromOrder("coupon", $MyRow['order_id'], $oc_tableprefix) * $MyRow['currency_value'], $MyRow['currency_code']);
			$OrderDiscount = RoundPriceFromCart(GetTotalFromOrder("dco", $MyRow['order_id'], $oc_tableprefix) * $MyRow['currency_value'], $MyRow['currency_code']);
			$OpenCartOrderNumber = $MyRow['order_id'];
			$Salesman = OPENCART_DEFAULT_SALESMAN;
			$Location = OPENCART_DEFAULT_LOCATION;

			if ($CustomerCode == 'WEB-KL-IDR') {
				$Area = OPENCART_DEFAULT_AREA_INDONESIA;
			} else {
				$Area = OPENCART_DEFAULT_AREA;
			}

			if ($CustomerCode != 'Error') {
				// First process order header
				if (DataExistsInKwaMoja('salesorders', 'customerref', $MyRow['order_id'])) {
					$Action = "Update";
				} else {
					$Action = "Insert";
					do {
						$OrderNo = GetNextSequenceNo(30);
						$CheckDoesntExistResult = DB_query("SELECT count(*) FROM salesorders WHERE orderno='" . $OrderNo . "'");
						$CheckDoesntExistRow = DB_fetch_row($CheckDoesntExistResult);
					} while ($CheckDoesntExistRow[0] == 1);

					$SQLInsert = "INSERT INTO salesorders (
									orderno,
									debtorno,
									branchcode,
									customerref,
									comments,
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
									salesperson,
									fromstkloc,
									freightcost,
									quotation,
									deliverydate,
									quotedate,
									confirmeddate)
								VALUES (
									'" . $OrderNo . "',
									'" . $CustomerCode . "',
									'" . $CustomerCode . "',
									'" . $OpenCartOrderNumber . "',
									'" . $MyRow['comment'] . "',
									'" . $MyRow['date_modified'] . "',
									'" . $SalesType . "',
									'" . $DefaultShipVia . "',
									'" . $ShippingName . "',
									'" . DB_escape_string($MyRow['shipping_address_1']) . "',
									'" . DB_escape_string($MyRow['shipping_address_2']) . "',
									'" . DB_escape_string($MyRow['shipping_city']) . "',
									'" . DB_escape_string($MyRow['shipping_zone']) . "',
									'" . DB_escape_string($MyRow['shipping_postcode']) . "',
									'" . DB_escape_string($MyRow['shipping_country']) . "',
									'" . DB_escape_string($MyRow['telephone']) . "',
									'" . DB_escape_string($MyRow['email']) . "',
									'" . $Salesman . "',
									'" . $Location . "',
									'" . $FreightCost . "',
									'" . $Quotation . "',
									'" . $MyRow['date_modified'] . "',
									'" . $MyRow['date_modified'] . "',
									'" . $MyRow['date_modified'] . "')";
					$ResultInsert = DB_query($SQLInsert, $InsertErrMsg, $DbgMsg, true);
				}
				if ($ShowMessages) {
					printf('<td>%s</td>
							<td>%s</td>
							<td>%s</td>
							<td>%s</td>
							<td>%s</td>
							<td>%s</td>
							<td>%s</td>
							<td>%s</td>
							<td>%s</td>
							</tr>', $MyRow['order_id'], $OrderNo, $ShippingName, $MyRow['email'], $FreightCost, $DefaultShipVia, $MyRow['currency_code'], $MyRow['shipping_country'], $Action);
				}
				if ($EmailText != '') {
					$EmailText = $EmailText . $MyRow['order_id'] . " = " . $OrderNo . " = " . $ShippingName . " = " . $MyRow['email'] . " = " . $MyRow['currency_code'] . " = " . $MyRow['shipping_country'] . " --> " . $Action . "\n";
				}
				// Now the items of the order
				$SQLItemsOrder = "SELECT " . $oc_tableprefix . "order_product.model,
										" . $oc_tableprefix . "order_product.quantity,
										" . $oc_tableprefix . "order_product.price,
										" . $oc_tableprefix . "order_product.total,
										" . $oc_tableprefix . "order_product.tax,
										" . $oc_tableprefix . "order_product.reward
								FROM " . $oc_tableprefix . "order_product
								WHERE " . $oc_tableprefix . "order_product.order_id = " . $MyRow['order_id'] . "
								ORDER BY " . $oc_tableprefix . "order_product.order_product_id";
				$ResultItemsOrder = DB_query_oc($SQLItemsOrder);
				$ItemsOrder = 0;
				if ($ShowMessages) {
					echo '<table class="selection">';
					echo $TableHeaderForItems;
					echo '<tr class="OddTableRows">';
				}
				while ($myitems = DB_fetch_array($ResultItemsOrder)) {
					$ItemsOrder++;
					if ($Action == "Update") {
						$Action = "Update";
					} else {
						$Price = RoundPriceFromCart($myitems['price'] * $MyRow['currency_value'], $MyRow['currency_code']);
						$SQLInsert = "INSERT INTO salesorderdetails
											(orderlineno,
											orderno,
											stkcode,
											unitprice,
											quantity,
											itemdue,
											discountpercent)
									VALUES ('" . $ItemsOrder . "',
											'" . $OrderNo . "',
											'" . $myitems['model'] . "',
											'" . $Price . "',
											'" . $myitems['quantity'] . "',
											'" . $MyRow['date_modified'] . "',
											'0')"; // prices come already net from OpenCart
						$ResultInsert = DB_query($SQLInsert, $InsertErrMsg, $DbgMsg, true);

						// prepare the RL for the items just ordered online
						$SQLUpdate = "UPDATE locstock
										SET reorderlevel = reorderlevel + " . $myitems['quantity'] . "
										WHERE stockid = '" . $myitems['model'] . "'
										AND loccode = '" . $Location . "'";
						$ResultUpdate = DB_query($SQLUpdate, $UpdateErrMsg, $DbgMsg, true);
						if ($ShowMessages) {
							printf('<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									</tr>', $MyRow['order_id'], $OrderNo, $ItemsOrder, $myitems['model'], $Price, $myitems['quantity'], $Action);
						}
						if ($EmailText != '') {
							$EmailText = $EmailText . "            " . $ItemsOrder . " = " . $myitems['model'] . " = " . $ShippingName . " = " . $Price . " = " . $myitems['quantity'] . " --> " . $Action . "\n";
						}
					}
				}
				if ($CouponDiscount != 0) {
					$ItemsOrder++;
					// we need to register the coupon use
					$CouponCode = GetTotalTitleFromOrder("coupon", $MyRow['order_id'], $oc_tableprefix);
					$CouponStockId = OPENCART_ONLINE_COUPON_CODE;
					$CouponQty = 1;
					if ($Action == "Update") {
						$Action = "Update";
					} else {
						$SQLInsert = "INSERT INTO salesorderdetails
											(orderlineno,
											orderno,
											stkcode,
											unitprice,
											quantity,
											itemdue,
											narrative,
											discountpercent)
									VALUES ('" . $ItemsOrder . "',
											'" . $OrderNo . "',
											'" . $CouponStockId . "',
											'" . $CouponDiscount . "',
											'" . $CouponQty . "',
											'" . $MyRow['date_modified'] . "',
											'" . $CouponCode . "',
											'0')"; // prices come already net from OpenCart
						$ResultInsert = DB_query($SQLInsert, $InsertErrMsg, $DbgMsg, true);
						if ($ShowMessages) {
							printf('<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									</tr>', $MyRow['order_id'], $OrderNo, $ItemsOrder, $CouponStockId, $CouponDiscount, $CouponQty, $Action);
						}
						if ($EmailText != '') {
							$EmailText = $EmailText . "            " . $ItemsOrder . " = " . $CouponStockId . " = " . $CouponDiscount . " = " . $CouponQty . " --> " . $Action . "\n";
						}
					}
				}
				if ($OrderDiscount != 0) {
					$ItemsOrder++;
					// we need to register the dco discount use (GENERAL ORDER DISCOUNT)
					$DiscountCode = GetTotalTitleFromOrder("dco", $MyRow['order_id'], $oc_tableprefix);
					$DiscountStockId = OPENCART_ONLINE_ORDER_DISCOUNT_CODE;
					$DiscountQty = 1;
					if ($Action == "Update") {
						$Action = "Update";
					} else {
						$SQLInsert = "INSERT INTO salesorderdetails
											(orderlineno,
											orderno,
											stkcode,
											unitprice,
											quantity,
											itemdue,
											narrative,
											discountpercent)
									VALUES ('" . $ItemsOrder . "',
											'" . $OrderNo . "',
											'" . $DiscountStockId . "',
											'" . $OrderDiscount . "',
											'" . $DiscountQty . "',
											'" . $MyRow['date_modified'] . "',
											'" . $DiscountCode . "',
											'0')"; // prices come already net from OpenCart
						$ResultInsert = DB_query($SQLInsert, $InsertErrMsg, $DbgMsg, true);
						if ($ShowMessages) {
							printf('<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									<td>%s</td>
									</tr>', $MyRow['order_id'], $OrderNo, $ItemsOrder, $DiscountStockId, $OrderDiscount, $DiscountQty, $Action);
						}
						if ($EmailText != '') {
							$EmailText = $EmailText . "            " . $ItemsOrder . " = " . $DiscountStockId . " = " . $OrderDiscount . " = " . $DiscountQty . " --> " . $Action . "\n";
						}
					}
				}
				++$i;
				if ($ShowMessages) {
					echo '</table>';
					echo '</table>';
				}
			} else {
				// Order does not belong to a valid customer for any reason, escape it
				if ($ShowMessages) {
					prnMsg('Sales Order from ' . $MyRow['email'] . ' is not valid as is not a valid currency code.', 'warn');
				}
			}
		}
		if ($ShowMessages) {
			echo '</div>
					</form>';
		}
	}
	if ($ShowMessages) {
		prnMsg(locale_number_format($i, 0) . ' ' . _('Orders synchronized from OpenCart to KwaMoja'), 'success');
	}
	if ($EmailText != '') {
		$EmailText = $EmailText . locale_number_format($i, 0) . ' ' . _('Orders synchronized from OpenCart to KwaMoja') . "\n\n";
	}
	return $EmailText;
}

function SyncPaypalPaymentInformation($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText = '') {
	$ServerNow = GetServerTimeNow(SERVER_TO_LOCAL_TIME_DIFFERENCE);
	$Today = date('Y-m-d');

	if ($EmailText != '') {
		$EmailText = $EmailText . "Sync OpenCart Order Information --> Server Time = " . $ServerNow . " --> KwaMoja Time = " . date('d/M/Y H:i:s') . "\n\n";
	}

	// Now deal with the Paypal payment/s of the order...
	$SQL = "SELECT 	" . $oc_tableprefix . "paypal_order.paypal_order_id,
				" . $oc_tableprefix . "order.order_id,
				" . $oc_tableprefix . "order.currency_code AS ordercurrency,
				" . $oc_tableprefix . "order.currency_value,
				" . $oc_tableprefix . "order.customer_id,
				" . $oc_tableprefix . "customer.email,
				" . $oc_tableprefix . "order.total AS ordertotal,
				" . $oc_tableprefix . "paypal_order.paypal_order_id,
				" . $oc_tableprefix . "paypal_order.capture_status,
				" . $oc_tableprefix . "paypal_order.currency_code AS paypalcurrency,
				" . $oc_tableprefix . "paypal_order.authorization_id,
				" . $oc_tableprefix . "paypal_order.total AS paypaltotal,
				" . $oc_tableprefix . "paypal_order_transaction.transaction_id,
				" . $oc_tableprefix . "paypal_order_transaction.created,
				" . $oc_tableprefix . "paypal_order_transaction.payment_status,
				" . $oc_tableprefix . "paypal_order_transaction.pending_reason,
				" . $oc_tableprefix . "paypal_order_transaction.transaction_entity,
				" . $oc_tableprefix . "paypal_order_transaction.amount,
				" . $oc_tableprefix . "paypal_order_transaction.debug_data,
				" . $oc_tableprefix . "paypal_order_transaction.call_data
		FROM " . $oc_tableprefix . "paypal_order,
			 " . $oc_tableprefix . "paypal_order_transaction,
			 " . $oc_tableprefix . "order,
			 " . $oc_tableprefix . "customer
		WHERE " . $oc_tableprefix . "paypal_order.paypal_order_id = " . $oc_tableprefix . "paypal_order_transaction.paypal_order_id
				AND " . $oc_tableprefix . "paypal_order.order_id  = " . $oc_tableprefix . "order.order_id
				AND " . $oc_tableprefix . "order.customer_id  = " . $oc_tableprefix . "customer.customer_id
				AND ( " . $oc_tableprefix . "paypal_order.created >= '" . $LastTimeRun . "'
					OR " . $oc_tableprefix . "paypal_order.modified >= '" . $LastTimeRun . "')
		ORDER BY " . $oc_tableprefix . "paypal_order.paypal_order_id";
	$Result = DB_query_oc($SQL);

	$i = 0;
	if (DB_num_rows($Result) != 0) {
		if ($ShowMessages) {
			echo '<p class="page_title_text" align="center"><strong>' . _('Paypal Payments from OpenCart') . '</strong></p>';
			echo '<div>';
			echo '<table class="selection">';
			$TableHeader = '<tr>
								<th>' . _('CustomerID') . '</th>
								<th>' . _('email') . '</th>
								<th>' . _('KwaMoja Code') . '</th>
								<th>' . _('OrderID') . '</th>
								<th>' . _('KwaMoja #') . '</th>
								<th>' . _('Order Total') . '</th>
								<th>' . _('Order Curr') . '</th>
								<th>' . _('Paypal Total') . '</th>
								<th>' . _('Paypal Curr') . '</th>
								<th>' . _('Paypal Trx') . '</th>
								<th>' . _('Trx Total') . '</th>
								<th>' . _('Commission') . '</th>
								<th>' . _('Date') . '</th>
								<th>' . _('Status') . '</th>
								<th>' . _('Pending reason') . '</th>
							</tr>';
			echo $TableHeader;
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update OpenCart Paypal payments in KwaMoja failed');
		$InsertErrMsg = _('The SQL to insert OpenCart Paypal payments in KwaMoja failed');

		$k = 0; //row colour counter
		while ($MyRow = DB_fetch_array($Result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}

			/* FIELD MATCHING */
			if (defined('KWAMOJA_ONLINE_CUSTOMER_CODE_PREFIX')) {
				$CustomerCode = GetKwaMojaCustomerIdFromCurrency($MyRow['ordercurrency']);
			} else if (!DataExistsInKwaMoja('debtorsmaster', 'debtorno', $MyRow['customer_id'])) {
				CreateDebtorFromOpenCartID($MyRow['customer_id'], $MyRow['ordercurrency'], $oc_tableprefix);
				$CustomerCode = $MyRow['customer_id'];
			} else {
				$CustomerCode = $MyRow['customer_id'];
			}
			$OrderNo = GetKwaMojaOrderNo($CustomerCode, $MyRow['order_id']);
			$PaymentSystem = OPENCART_DEFAULT_PAYMENT_SYSTEM;
			$CurrencyOrder = $MyRow['ordercurrency'];
			$CurrencyPayment = $MyRow['paypalcurrency'];
			$TotalOrder = round($MyRow['ordertotal'] * $MyRow['currency_value'], 2); // from OC default currency to order and payment currency
			$Rate = GetKwaMojaCurrencyRate($CurrencyOrder);
			$AmountPaid = $MyRow['paypaltotal'];
			$TransactionID = $MyRow['transaction_id'];
			$GLAccount = GetKwaMojaGLAccountFromCurrency($CurrencyPayment);
			$GLCommissionAccount = GetKwaMojaGLCommissionAccountFromCurrency($CurrencyPayment);
			$PayPalResponseArray = GetPaypalReturnDataInArray($MyRow['debug_data']);
			$Commission = urldecode($PayPalResponseArray['PAYMENTINFO_0_FEEAMT']);


			if (($MyRow['paypalcurrency'] == $MyRow['ordercurrency']) and ($MyRow['pending_reason'] == 'None')) {
				// order currency and Paypal currency are the same
				// AND has been paid OK
				$PaymentOK = true;
			} else {
				prnMsg("HORROR: Currency mess", "warn");
				$PaymentOK = false;
			}

			if ($PaymentOK) {
				$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']));
				InsertCustomerReceipt($CustomerCode, $AmountPaid, $CurrencyPayment, $Rate, $GLAccount, $PaymentSystem, $TransactionID, $OrderNo, $PeriodNo);
				TransactionCommissionGL($CustomerCode, $GLAccount, $GLCommissionAccount, $Commission, $CurrencyPayment, $Rate, $PaymentSystem, $TransactionID, $PeriodNo);
				ChangeOrderQuotationFlag($OrderNo, 0); // it has been paid, so we consider it a firm order
			}

			if ($ShowMessages) {
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>', $MyRow['customer_id'], $MyRow['email'], $CustomerCode, $MyRow['order_id'], $OrderNo, $TotalOrder, $MyRow['ordercurrency'], $AmountPaid, $MyRow['paypalcurrency'], $TransactionID, $MyRow['amount'], $Commission, $MyRow['created'], $MyRow['payment_status'], $MyRow['pending_reason']);
			}
			if ($EmailText != '') {
				$EmailText = $EmailText . $MyRow['customer_id'] . " = " . $MyRow['email'] . " = " . $CustomerCode . " = " . $MyRow['order_id'] . " = " . $TotalOrder . " = " . $MyRow['ordercurrency'] . " = " . $AmountPaid . " = " . $MyRow['payment_status'] . " --> " . $Action . "\n";
			}
			++$i;
		}
		if ($ShowMessages) {
			echo '</table>
					</div>
					</form>';
		}
	}
	if ($ShowMessages) {
		prnMsg(locale_number_format($i, 0) . ' ' . _('Payments synchronized from OpenCart to KwaMoja'), 'success');
	}
	if ($EmailText != '') {
		$EmailText = $EmailText . locale_number_format($i, 0) . ' ' . _('Payments synchronized from OpenCart to KwaMoja') . "\n\n";
	}
	return $EmailText;
}

?>