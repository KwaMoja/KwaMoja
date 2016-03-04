<?php

function KwaMojaToOpenCartDailySync($ShowMessages, $oc_tableprefix, $EmailText = '') {
	$begintime = time_start();
	DB_Txn_Begin();

	// check last time we run this script, so we know which records need to update from OC to KwaMoja
	$LastTimeRun = CheckLastTimeRun('KwaMojaToOpenCartDaily');
	if ($ShowMessages) {
		prnMsg('This script was last run on: ' . $LastTimeRun . ' Server time difference: ' . SERVER_TO_LOCAL_TIME_DIFFERENCE, 'success');
		prnMsg('Server time now: ' . GetServerTimeNow(SERVER_TO_LOCAL_TIME_DIFFERENCE), 'success');
	}
	if ($EmailText != '') {
		$EmailText = $EmailText . 'KwaMoja to OpenCart Daily Sync was last run on: ' . $LastTimeRun . "\n\n" . 'Server time difference: ' . SERVER_TO_LOCAL_TIME_DIFFERENCE . "\n\n" . 'Server time now: ' . GetServerTimeNow(SERVER_TO_LOCAL_TIME_DIFFERENCE) . "\n\n";
	}

	// maintain outlet category in KwaMoja
	// Not needed because now in KwaMoja one item only belongs to 1 sales category, so no chance to have more than one to clean up
	//	$EmailText = MaintainKwaMojaOutletSalesCategories($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText);

	// do all hourly maintenance as well...
	$EmailText = KwaMojaToOpenCartHourlySync($ShowMessages, $oc_tableprefix, FALSE, $EmailText);

	// recreate the list of featured in OpenCart
	$EmailText = SyncFeaturedList($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText);

	// update sales categories
	$EmailText = SyncSalesCategories($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText);

	// activate / inactivate categories depending on items No items = inactive. Items = Active
	$EmailText = ActivateCategoryDependingOnQOH($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText);

	// maintain the outlet category in a special way (both KwaMoja and OC)
	$EmailText = MaintainOpenCartOutletSalesCategories($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText);

	// assign multiple images to products
	$EmailText = SyncMultipleImages($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText);

	// assign related items
	$EmailText = SyncRelatedItems($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText);

	// We are done!
	SetLastTimeRun('KwaMojaToOpenCartDaily');
	DB_Txn_Commit();
	if ($ShowMessages) {
		time_finish($begintime);
	}

	return $EmailText;
}

function KwaMojaToOpenCartHourlySync($ShowMessages, $oc_tableprefix, $ControlTx = TRUE, $EmailText = '') {
//	$begintime = time_start();
	if ($ControlTx) {
		DB_Txn_Begin();
	}
	// check last time we run this script, so we know which records need to update from OC to KwaMoja
	$LastTimeRun = CheckLastTimeRun('KwaMojaToOpenCartHourly');
	if ($ShowMessages) {
		prnMsg('This script was last run on: ' . $LastTimeRun . ' Server time difference: ' . SERVER_TO_LOCAL_TIME_DIFFERENCE, 'success');
		prnMsg('Server time now: ' . GetServerTimeNow(SERVER_TO_LOCAL_TIME_DIFFERENCE), 'success');
	}
	if (($EmailText != '') and ControlTx) {
		$EmailText = $EmailText . 'KwaMoja to OpenCart Hourly Sync was last run on: ' . $LastTimeRun . "\n\n" . 'Server time difference: ' . SERVER_TO_LOCAL_TIME_DIFFERENCE . "\n\n" . 'Server time now: ' . GetServerTimeNow(SERVER_TO_LOCAL_TIME_DIFFERENCE) . "\n\n";
	}
	// update product basic information
	$EmailText = SyncProductBasicInformation($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText);

	// update product - sales categories relationship
	$EmailText = SyncProductSalesCategories($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText);

	// update product prices
	$EmailText = SyncProductPrices($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText);

	// update stock in hand
	$EmailText = SyncProductQOH($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText);

	// clean duplicated URL alias
	$EmailText = CleanDuplicatedUrlAlias($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText);

	// We are done!
	SetLastTimeRun('KwaMojaToOpenCartHourly');
	if ($ControlTx) {
		DB_Txn_Commit();
	}
	if ($ShowMessages) {
//		time_finish($begintime);
	}

	return $EmailText;
}

function SyncProductBasicInformation($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText = '') {
	$ServerNow = GetServerTimeNow(SERVER_TO_LOCAL_TIME_DIFFERENCE);
	$Today = date('Y-m-d');

	/*	if ($EmailText !=''){
	$EmailText = $EmailText . "Basic Product Information --> Server Time = " . $ServerNow . " --> KwaMoja Time = " .  date('d/M/Y H:i:s') . "\n\n";
	}
	*/
	/* let's get the KwaMoja price list and base currency for the online customer */
	list($PriceList, $Currency) = GetOnlinePriceList();

	/* Look for all stockid that have been modified lately */
	$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				stockmaster.longdescription,
				stockmaster.grossweight,
				stockmaster.length,
				stockmaster.width,
				stockmaster.height,
				stockmaster.unitsdimension,
				stockmaster.discountcategory,
				salescatprod.salescatid,
				salescatprod.manufacturers_id,
				stockmaster.discontinued
			FROM stockmaster
			INNER JOIN salescatprod
				ON stockmaster.stockid = salescatprod.stockid
			WHERE ((stockmaster.date_created >= '" . $LastTimeRun . "'	OR stockmaster.date_updated >= '" . $LastTimeRun . "'
					OR salescatprod.date_created >= '" . $LastTimeRun . "'	OR salescatprod.date_updated >= '" . $LastTimeRun . "'))
			ORDER BY stockmaster.stockid";

	$Result = DB_query($SQL);
	$i = 0;
	if (DB_num_rows($Result) != 0) {
		if ($ShowMessages) {
			echo '<p class="page_title_text" align="center"><strong>' . _('Product Basic Info') . '</strong></p>';
			echo '<table class="selection">
					<tr>
						<th>' . _('StockID') . '</th>
						<th>' . _('Description') . '</th>
						<th>' . _('QOH') . '</th>
						<th>' . _('Basic Price') . '</th>
						<th>' . _('Action') . '</th>
					</tr>';
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update Basic Product Information in Opencart failed');
		$InsertErrMsg = _('The SQL to insert Basic Product Information in Opencart failed');

		$k = 0; //row colour counter
		while ($MyRow = DB_fetch_array($Result)) {
			if ($ShowMessages) {
				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} else {
					echo '<tr class="OddTableRows">';
					++$k;
				}
			}
			/* Field Matching */
			$Model = $MyRow['stockid'];
			$SKU = $MyRow['stockid'];
			$UPC = '';
			$EAN = '';
			$JAN = '';
			$ISBN = '';
			$Location = '';
			$Quantity = GetOnlineQOH($MyRow['stockid']);
			$StockStatusId = 5; // Out of stock by default
			$Image = PATH_OPENCART_IMAGES . $MyRow['stockid'] . '.jpg';
			$ManufacturerId = $MyRow['manufacturers_id'];
			$Shipping = 1; // will need function depending if it's a shippable or not item
			if (defined('KWAMOJA_ONLINE_CUSTOMER_CODE_PREFIX')) {
				$CustomerCode = GetKwaMojaCustomerIdFromCurrency(OPENCART_DEFAULT_CURRENCY);
			} else {
				$CustomerCode = '';
			}
			$Price = GetPrice($MyRow['stockid'], '', ''); // Get the price without any discount from KwaMoja
			$DiscountCategory = $MyRow['discountcategory'];
			$Points = 0; // No points concept in KwaMoja
			$TaxClassId = 0; // Not sure how to link stockid and tax in KwaMoja
			$DateAvailable = $ServerNow;
			$Weight = $MyRow['grossweight'];
			$WeightClassId = 1; //In KwaMoja grossweight is always in Kg.
			$Length = $MyRow['length'];
			$Width = $MyRow['width'];
			$Height = $MyRow['height'];
			$LenghtClassId = GetLenghtClassId($MyRow['unitsdimension'], 1, $oc_tableprefix);
			$Subtract = 1;
			$Minimum = 1;
			$SortOrder = 1;
			if ($Quantity > 0) {
				$Status = 1;
			} else {
				$Status = 0;
			}
			$Status = $MyRow['discontinued'];
			$Viewed = 0;

			$LanguageId = 1;
			$Name = $MyRow['description'];
			$Description = str_replace("'", "\'", $MyRow['longdescription']);
			$MetaDescription = CreateMetaDescription($MyRow['stockid'], trim($MyRow['description']));
			$MetaKeyword = CreateMetaKeyword($MyRow['stockid'], trim($MyRow['description']));
			$Tag = $MyRow['description'];
			$StoreId = 0;

			/* Google Product Feed Fields */
			$MPN = $MyRow['stockid'];
			$GPFStatus = GetGoogleProductFeedStatus($MyRow['stockid'], $MyRow['salescatid'], $Quantity);
			$GoogleProductCategory = GetGoogleProductFeedCategory($MyRow['stockid'], $MyRow['salescatid']);
			$GoogleBrand = GOOGLE_BRAND;
			$GoogleGender = GOOGLE_GENDER;
			$GoogleAgeGroup = GOOGLE_AGEGROUP;
			$GoogleCondition = GOOGLE_CONDITION;
			$GoogleOosStatus = GOOGLE_OOS_STATUS;
			$GoogleIdentifier = GOOGLE_IDENTIFIER;

			/* END Google Product Feed Fields */

			if (DataExistsInOpenCart($oc_tableprefix . 'product', 'model', $MyRow['stockid'])) {
				$Action = "Update";
				// Let's get the OpenCart primary key for product
				$ProductId = GetOpenCartProductId($Model, $oc_tableprefix);
				$SQLUpdate = "UPDATE " . $oc_tableprefix . "product SET
								sku = '" . $SKU . "',
								mpn = '" . $MPN . "',
								image = '" . $Image . "',
								google_product_category = '" . $GoogleProductCategory . "',
								brand = '" . $GoogleBrand . "',
								gender = '" . $GoogleGender . "',
								agegroup = '" . $GoogleAgeGroup . "',
								`condition` = '" . $GoogleCondition . "',
								oos_status = '" . $GoogleOosStatus . "',
								identifier_exists = '" . $GoogleIdentifier . "',
								manufacturer_id = '" . $ManufacturerId . "',
								weight = '" . $Weight . "',
								length = '" . $Length . "',
								width = '" . $Width . "',
								height = '" . $Height . "',
								length_class_id = '" . $LenghtClassId . "'
							WHERE product_id = '" . $ProductId . "'";
				$ResultUpdate = DB_query_oc($SQLUpdate, $UpdateErrMsg, $DbgMsg, true);

				$SQLUpdate = "UPDATE " . $oc_tableprefix . "product_description SET
								name = '" . $Name . "',
								description = '" . $Description . "',
								meta_description = '" . $MetaDescription . "',
								meta_keyword = '" . $MetaKeyword . "',
								tag = '" . $Tag . "'
							WHERE product_id = '" . $ProductId . "'
								AND language_id = '" . $LanguageId . "'";
				$ResultUpdate = DB_query_oc($SQLUpdate, $UpdateErrMsg, $DbgMsg, true);

				// update discounts if needed
				MaintainOpenCartDiscountForItem($ProductId, $Price, $DiscountCategory, $PriceList, $oc_tableprefix);

				// update SEO Keywords if needed
				$SEOQuery = 'product_id=' . $ProductId;
				$SEOKeyword = CreateSEOKeyword($Model . "-" . $Name);
				MaintainUrlAlias($SEOQuery, $SEOKeyword, $oc_tableprefix);

			} else {
				$Action = "Insert";
				$SQLInsert = "INSERT INTO " . $oc_tableprefix . "product
								(model,
								sku,
								upc,
								ean,
								jan,
								isbn,
								mpn,
								location,
								quantity,
								stock_status_id,
								image,
								manufacturer_id,
								shipping,
								price,
								points,
								tax_class_id,
								date_available,
								weight,
								weight_class_id,
								length,
								width,
								height,
								length_class_id,
								subtract,
								minimum,
								sort_order,
								status,
								viewed,
								date_added,
								date_modified)
							VALUES
								('" . $Model . "',
								'" . $SKU . "',
								'" . $UPC . "',
								'" . $EAN . "',
								'" . $JAN . "',
								'" . $ISBN . "',
								'" . $MPN . "',
								'" . $Location . "',
								'" . $Quantity . "',
								'" . $StockStatusId . "',
								'" . $Image . "',
								'" . $ManufacturerId . "',
								'" . $Shipping . "',
								'" . $Price . "',
								'" . $Points . "',
								'" . $TaxClassId . "',
								'" . $DateAvailable . "',
								'" . $Weight . "',
								'" . $WeightClassId . "',
								'" . $Length . "',
								'" . $Width . "',
								'" . $Height . "',
								'" . $LenghtClassId . "',
								'" . $Subtract . "',
								'" . $Minimum . "',
								'" . $SortOrder . "',
								'" . $Status . "',
								'" . $Viewed . "',
								'" . $ServerNow . "',
								'" . $ServerNow . "'
								)";
				$ResultInsert = DB_query_oc($SQLInsert, $InsertErrMsg, $DbgMsg, true);

				// Let's get the OpenCart primary key for product
				$ProductId = GetOpenCartProductId($Model, $oc_tableprefix);

				$SQLInsert = "INSERT INTO " . $oc_tableprefix . "product_description
								(product_id,
								language_id,
								name,
								description,
								meta_description,
								meta_keyword,
								tag)
							VALUES
								('" . $ProductId . "',
								'" . $LanguageId . "',
								'" . $Name . "',
								'" . $Description . "',
								'" . $MetaDescription . "',
								'" . $MetaKeyword . "',
								'" . $Tag . "'
								)";
				$ResultInsert = DB_query_oc($SQLInsert, $InsertErrMsg, $DbgMsg, true);

				$SQLInsert = "INSERT INTO " . $oc_tableprefix . "product_to_store
								(product_id,
								store_id)
							VALUES
								('" . $ProductId . "',
								'" . $StoreId . "'
								)";
				$ResultInsert = DB_query_oc($SQLInsert, $InsertErrMsg, $DbgMsg, true);

				// create discounts if needed
				MaintainOpenCartDiscountForItem($ProductId, $Price, $DiscountCategory, $PriceList, $oc_tableprefix);

				// create SEO Keywords if needed
				$SEOQuery = 'product_id=' . $ProductId;
				$SEOKeyword = CreateSEOKeyword($Model . "-" . $Name);
				MaintainUrlAlias($SEOQuery, $SEOKeyword, $oc_tableprefix);

				$SortOrder++;
			}

			/* Update any translated descriptions */
			/* First fetch the open cart languages */
			$SQL = "SELECT language_id,
							code
						FROM " . $oc_tableprefix . "language
						WHERE language_id<>1";
			$LanguagesResult = DB_query_oc($SQL);
			while ($LanguageRow = DB_fetch_array($LanguagesResult)) {
				$SQL = "SELECT language_id,
								descriptiontranslation
							FROM stockdescriptiontranslations
							WHERE stockid='" . $MyRow['stockid'] . "'
								AND language_id LIKE '%" . $LanguageRow['code'] . "%'";
				$DescriptionResult = DB_query($SQL);
				$DescriptionRow = DB_fetch_array($DescriptionResult);
				$ShortDescription = $DescriptionRow['descriptiontranslation'];
				$SQL = "SELECT language_id,
								longdescriptiontranslation
							FROM stocklongdescriptiontranslations
							WHERE stockid='" . $MyRow['stockid'] . "'
								AND language_id LIKE '%" . $LanguageRow['code'] . "%'";
				$DescriptionResult = DB_query($SQL);
				$DescriptionRow = DB_fetch_array($DescriptionResult);
				$LongDescription = $DescriptionRow['longdescriptiontranslation'];
				if (DataExistsInOpenCart($oc_tableprefix . 'product_description', 'model', $MyRow['stockid'])) {
					$UpdateSQL = "UPDATE " . $oc_tableprefix . "product_description SET name='" . $ShortDescription. "',
																						description='" . $LongDescription . "'
																					WHERE product_id='" . $ProductId . "'
																						AND language_id='" . $LanguageRow['language_id'] . "'";
					$UpdateResult = DB_query_oc($UpdateSQL);
				} else {
					$InsertSQL = "INSERT INTO " . $oc_tableprefix . "product_description (product_id,
																						  language_id,
																						  name,
																						  description
																					) VALUES (
																						  '" . $ProductId . "',
																						  '" . $LanguageRow['language_id'] . "',
																						  '" . DB_escape_string($ShortDescription) . "',
																						  '" . DB_escape_string($LongDescription) . "'
																					)";
					$InsertResult = DB_query_oc($InsertSQL);
				}
			}

			if ($ShowMessages) {
				printf('<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						</tr>', $Model, $Name, locale_number_format($Quantity, 0), locale_number_format($Price, 2), $Action);
			}
			if ($EmailText != '') {
				$EmailText = $EmailText . str_pad($Model, 20, " ") . " = " . $Name . " --> " . $Action . "\n";
			}
			++$i;
		}
		if ($ShowMessages) {
			echo '</table>';
		}
	}
	if ($ShowMessages) {
		prnMsg(locale_number_format($i, 0) . ' ' . _('Products synchronized from KwaMoja to OpenCart'), 'success');
	}
	if ($EmailText != '') {
		$EmailText = $EmailText . locale_number_format($i, 0) . ' ' . _('Product Basic Info synchronized from KwaMoja to OpenCart') . "\n\n";
	}
	return $EmailText;
}

function SyncProductSalesCategories($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText = '') {
	$ServerNow = GetServerTimeNow(SERVER_TO_LOCAL_TIME_DIFFERENCE);
	$Today = date('Y-m-d');

	/*	if ($EmailText !=''){
	$EmailText = $EmailText . "Product - Sales Categories --> Server Time = " . $ServerNow . " --> KwaMoja Time = " .  date('d/M/Y H:i:s') . "\n\n";
	}
	*/
	/* Look for the late modifications of salescatprod table in KwaMoja */
	$SQL = "SELECT salescatprod.salescatid,
				salescatprod.stockid,
				salescatprod.manufacturers_id,
				salescatprod.featured
			FROM salescatprod
			WHERE (salescatprod.date_created >= '" . $LastTimeRun . "'
					OR salescatprod.date_updated >= '" . $LastTimeRun . "')
			ORDER BY salescatprod.salescatid, salescatprod.stockid";

	$Result = DB_query($SQL);
	$i = 0;
	if (DB_num_rows($Result) != 0) {
		if ($ShowMessages) {
			echo '<p class="page_title_text" align="center"><strong>' . _('Product - Sales Categories') . '</strong></p>';
			echo '<table class="selection">
					<tr>
						<th>' . _('StockID') . '</th>
						<th>' . _('Sales Category') . '</th>
						<th>' . _('Manufacturer Id') . '</th>
						<th>' . _('Featured') . '</th>
						<th>' . _('Action') . '</th>
					</tr>';
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update Product - Sales Categories in Opencart failed');
		$InsertErrMsg = _('The SQL to insert Product - Sales Categories in Opencart failed');

		$k = 0; //row colour counter
		while ($MyRow = DB_fetch_array($Result)) {

			/* Field Matching */
			$Model = $MyRow['stockid'];
			$SalesCatId = $MyRow['salescatid'];
			$ManufacturerId = $MyRow['manufacturers_id'];
			$Featured = $MyRow['featured'];
			if ($Featured == 1) {
				$PrintFeatured = "Yes";
			} else {
				$PrintFeatured = "No";
			}

			// Let's get the OpenCart primary key for product
			$ProductId = GetOpenCartProductId($Model, $oc_tableprefix);

			if (DataExistsInOpenCart($oc_tableprefix . 'product_to_category', 'product_id', $ProductId, 'category_id', $SalesCatId)) {
				$Action = "Update";
				$SQLUpdate = "UPDATE " . $oc_tableprefix . "product SET
								manufacturer_id = '" . $ManufacturerId . "'
							WHERE product_id = '" . $ProductId . "'";
				$ResultUpdate = DB_query_oc($SQLUpdate, $UpdateErrMsg, $DbgMsg, true);
			} else {
				$Action = "Insert";
				$SQLInsert = "INSERT INTO " . $oc_tableprefix . "product_to_category
								(product_id,
								category_id)
							VALUES
								('" . $ProductId . "',
								'" . $SalesCatId . "'
								)";
				$ResultInsert = DB_query_oc($SQLInsert, $InsertErrMsg, $DbgMsg, true);
			}
			if ($ShowMessages) {
				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} else {
					echo '<tr class="OddTableRows">';
					++$k;
				}
				printf('<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>', $Model, $SalesCatId, $ManufacturerId, $PrintFeatured, $Action);
			}
			if ($EmailText != '') {
				$EmailText = $EmailText . str_pad($Model, 20, " ") . " --> " . $SalesCatId . " --> " . $Action . "\n";
			}
			++$i;
		}
		if ($ShowMessages) {
			echo '</table>';
		}
	}
	if ($ShowMessages) {
		prnMsg(locale_number_format($i, 0) . ' ' . _('Products to Sales Categories synchronized from KwaMoja to OpenCart'), 'success');
	}
	if ($EmailText != '') {
		$EmailText = $EmailText . locale_number_format($i, 0) . ' ' . _('Product - Sales Categories synchronized from KwaMoja to OpenCart') . "\n\n";
	}
	return $EmailText;
}

function SyncProductPrices($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText = '') {
	$ServerNow = GetServerTimeNow(SERVER_TO_LOCAL_TIME_DIFFERENCE);
	$Today = date('Y-m-d');

	$i = 0;
	/*	if ($EmailText !=''){
	$EmailText = $EmailText . "Product Price Sync --> Server Time = " . $ServerNow . " --> KwaMoja Time = " .  date('d/M/Y H:i:s') . "\n\n";
	}
	*/
	/* let's get the KwaMoja price list and base currency for the online customer */
	list($PriceList, $Currency) = GetOnlinePriceList();

	/* Look for the late modifications of prices table in KwaMoja */
	$SQL = "SELECT prices.stockid,
				stockmaster.discountcategory
			FROM prices, stockmaster
			WHERE prices.stockid = stockmaster.stockid
				AND prices.typeabbrev ='" . $PriceList . "'
				AND prices.currabrev ='" . $Currency . "'
				AND (prices.date_created >= '" . $LastTimeRun . "'
				OR prices.date_updated >= '" . $LastTimeRun . "')
			ORDER BY prices.stockid";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0) {
		if ($ShowMessages) {
			echo '<p class="page_title_text" align="center"><strong>' . _('Product Prices Updates') . '</strong></p>';
			echo '<table class="selection">
					<tr>
						<th>' . _('StockID') . '</th>
						<th>' . _('New Price') . '</th>
						<th>' . _('Discount Category') . '</th>
						<th>' . _('Action') . '</th>
					</tr>';
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update Product Prices in Opencart failed');

		$k = 0; //row colour counter
		while ($MyRow = DB_fetch_array($Result)) {
			if ($ShowMessages) {
				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} else {
					echo '<tr class="OddTableRows">';
					++$k;
				}
			}

			/* Field Matching */
			$Model = $MyRow['stockid'];
			if (defined('KWAMOJA_ONLINE_CUSTOMER_CODE_PREFIX')) {
				$CustomerCode = GetKwaMojaCustomerIdFromCurrency(OPENCART_DEFAULT_CURRENCY);
			}
			$Price = GetPrice($MyRow['stockid'], $CustomerCode, $CustomerCode); // Get the price without any discount from KwaMoja
			$ManufacturerId = $MyRow['manufacturers_id'];
			$DiscountCategory = $MyRow['discountcategory'];

			// Let's get the OpenCart primary key for product
			$ProductId = GetOpenCartProductId($Model, $oc_tableprefix);

			$Action = "Update";
			$SQLUpdate = "UPDATE " . $oc_tableprefix . "product SET
							price = '" . $Price . "'
						WHERE product_id = '" . $ProductId . "'";
			$ResultUpdate = DB_query_oc($SQLUpdate, $UpdateErrMsg, $DbgMsg, true);

			// update discounts if needed
			MaintainOpenCartDiscountForItem($ProductId, $Price, $DiscountCategory, $PriceList, $oc_tableprefix);
			if ($ShowMessages) {
				printf('<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>', $Model, locale_number_format($Price, 2), $DiscountCategory, $Action);
			}
			if ($EmailText != '') {
				$EmailText = $EmailText . str_pad($Model, 20, " ") . " = " . locale_number_format($Price, 2) . " = " . $DiscountCategory . " --> " . $Action . "\n";
			}
			++$i;
		}
		if ($ShowMessages) {
			echo '</table>';
		}
	}
	if ($ShowMessages) {
		prnMsg(locale_number_format($i, 0) . ' ' . _('Product Prices synchronized from KwaMoja to OpenCart'), 'success');
	}
	if ($EmailText != '') {
		$EmailText = $EmailText . locale_number_format($i, 0) . ' ' . _('Product Prices synchronized from KwaMoja to OpenCart') . "\n\n";
	}
	return $EmailText;
}

function SyncProductQOH($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText = '') {
	$ServerNow = GetServerTimeNow(SERVER_TO_LOCAL_TIME_DIFFERENCE);
	$Today = date('Y-m-d');

	/*	if ($EmailText !=''){
	$EmailText = $EmailText . "Sync Product QOH --> Server Time = " . $ServerNow . " --> KwaMoja Time = " .  date('d/M/Y H:i:s') . "\n\n";
	}
	*/
	/* let's get the KwaMoja price list and base currency for the online customer */
	list($PriceList, $Currency) = GetOnlinePriceList();

	/* Look for the late modifications of prices table in KwaMoja */
	$SQL = "SELECT DISTINCT(locstock.stockid)
			FROM locstock, salescatprod
			WHERE locstock.stockid = salescatprod.stockid
				AND locstock.loccode IN ('" . str_replace(',', "','", LOCATIONS_WITH_STOCK_FOR_ONLINE_SHOP) . "')
				AND (locstock.date_created >= '" . $LastTimeRun . "'
					OR locstock.date_updated >= '" . $LastTimeRun . "')
			ORDER BY locstock.stockid";

	$Result = DB_query($SQL);
	$i = 0;
	if (DB_num_rows($Result) != 0) {
		if ($ShowMessages) {
			echo '<p class="page_title_text" align="center"><strong>' . _('Product QOH Updates') . '</strong></p>';
			echo '<table class="selection">
					<tr>
						<th>' . _('StockID') . '</th>
						<th>' . _('Online QOH') . '</th>
						<th>' . _('Action') . '</th>
					</tr>';
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update Product QOH in Opencart failed');

		$k = 0; //row colour counter
		while ($MyRow = DB_fetch_array($Result)) {

			/* Field Matching */
			$Model = $MyRow['stockid'];
			$Quantity = GetOnlineQOH($MyRow['stockid']);
			if ($Quantity > 0) {
				$Status = 1;
				$GPFStatus = 1;
			} else {
				$Status = 0;
				$GPFStatus = 0;
			}

			// Let's get the OpenCart primary key for product
			$ProductId = GetOpenCartProductId($Model, $oc_tableprefix);

			$Action = "Update";
			$SQLUpdate = "UPDATE " . $oc_tableprefix . "product SET
							quantity = '" . $Quantity . "',
							gpf_status = '" . $GPFStatus . "',
							status = '" . $Status . "'
						WHERE product_id = '" . $ProductId . "'";
			$ResultUpdate = DB_query_oc($SQLUpdate, $UpdateErrMsg, $DbgMsg, true);
			if ($ShowMessages) {
				if ($ShowMessages) {
					if ($k == 1) {
						echo '<tr class="EvenTableRows">';
						$k = 0;
					} else {
						echo '<tr class="OddTableRows">';
						++$k;
					}
				}
				printf('<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						</tr>', $Model, locale_number_format($Quantity, 0), $Action);
			}
			if ($EmailText != '') {
				$EmailText = $EmailText . str_pad($Model, 20, " ") . " QOH = " . locale_number_format($Quantity, 0) . "\n";
			}
			++$i;
		}
		if ($ShowMessages) {
			echo '</table>';
		}
	}
	if ($ShowMessages) {
		prnMsg(locale_number_format($i, 0) . ' ' . _('Product QOH synchronized from KwaMoja to OpenCart'), 'success');
	}
	if ($EmailText != '') {
		$EmailText = $EmailText . locale_number_format($i, 0) . ' ' . _('Product QOH synchronized from KwaMoja to OpenCart') . "\n\n";
	}

	return $EmailText;
}

function CleanDuplicatedUrlAlias($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText = '') {
	$ServerNow = GetServerTimeNow(SERVER_TO_LOCAL_TIME_DIFFERENCE);
	$Today = date('Y-m-d');

	/*	if ($EmailText !=''){
	$EmailText = $EmailText . "Clean Duplicated URL Alias --> Server Time = " . $ServerNow . " --> KwaMoja Time = " .  date('d/M/Y H:i:s') . "\n\n";
	}
	*/
	$SQL = "SELECT 	" . $oc_tableprefix . "url_alias.url_alias_id,
				" . $oc_tableprefix . "url_alias.query,
				" . $oc_tableprefix . "url_alias.keyword
		FROM " . $oc_tableprefix . "url_alias
		ORDER BY " . $oc_tableprefix . "url_alias.query,
				" . $oc_tableprefix . "url_alias.url_alias_id DESC";
	$Result = DB_query_oc($SQL);
	$i = 0;
	if (DB_num_rows($Result) != 0) {
		$k = 0; //row colour counter
		$PreviousQuery = "";
		$PreviousKeyword = "";
		$ShowHeader = TRUE;
		while ($MyRow = DB_fetch_array($Result)) {
			if ($PreviousQuery == $MyRow['query']) {
				// we have a duplicated
				$DuplicatedQuery = $MyRow['query'];
				$DuplicatedKeyword = $MyRow['keyword'];

				if ($ShowHeader) {
					if ($ShowMessages) {
						echo '<p class="page_title_text" align="center"><strong>' . _('Duplicated URL Alias clean up') . '</strong></p>';
						echo '<table class="selection">
								<tr>
									<th>' . _('URL Alias ID') . '</th>
									<th>' . _('Query') . '</th>
									<th>' . _('Keyword') . '</th>
								</tr>';
					}
					$ShowHeader = FALSE;
				}
				// we delete the duplicated
				$SQLDelete = "DELETE FROM " . $oc_tableprefix . "url_alias
							WHERE url_alias_id = '" . $MyRow['url_alias_id'] . "'";
				$ResultDelete = DB_query_oc($SQLDelete, $UpdateErrMsg, $DbgMsg, true);

				// we set it up as a redirect just in case someome uses this old URL keyword
				if ($PreviousKeyword != $MyRow['keyword']) {
					$Active = 1;
					$FromURL = PATH_OPENCART_BASE . '/' . $MyRow['keyword'];
					$ToURL = PATH_OPENCART_BASE . '/' . ROUTE_TO_PRODUCT . $MyRow['query'];
					$ResponseCode = REDIRECT_RESPONSE_CODE;
					$FromDate = date('Y-m-d');
					$TimesUsed = 0;
					$SQLInsert = "INSERT INTO " . $oc_tableprefix . "redirect
								(active,
								from_url,
								to_url,
								response_code,
								date_start,
								times_used)
							VALUES
								('" . $Active . "',
								'" . $FromURL . "',
								'" . $ToURL . "',
								'" . $ResponseCode . "',
								'" . $FromDate . "',
								'" . $TimesUsed . "'
								)";
					$ResultInsert = DB_query_oc($SQLInsert, $UpdateErrMsg, $DbgMsg, true);
				}

				if ($ShowMessages) {
					if ($k == 1) {
						echo '<tr class="EvenTableRows">';
						$k = 0;
					} else {
						echo '<tr class="OddTableRows">';
						++$k;
					}
					printf('<td class="number">%s</td>
							<td>%s</td>
							<td>%s</td>
							</tr>', locale_number_format($MyRow['url_alias_id'], 0), $MyRow['query'], $MyRow['keyword']);
				}
				if ($EmailText != '') {
					$EmailText = $EmailText . locale_number_format($MyRow['url_alias_id'], 0) . " --> " . $MyRow['query'] . " --> " . $MyRow['keyword'] . "\n";
				}
				++$i;
			}
			$PreviousQuery = $MyRow['query'];
			$PreviousKeyword = $MyRow['keyword'];
		}
		if (!$ShowHeader) {
			if ($ShowMessages) {
				echo '</table>';
			}
		}
	}
	if ($ShowMessages) {
		prnMsg(locale_number_format($i, 0) . ' ' . _('Duplicated URL Alias synchronized in OpenCart'), 'success');
	}
	if ($EmailText != '') {
		$EmailText = $EmailText . locale_number_format($i, 0) . ' ' . _('Duplicated URL Alias synchronized in OpenCart') . "\n\n";
	}
	return $EmailText;
}

function SyncSalesCategories($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText = '') {
	$ServerNow = GetServerTimeNow(SERVER_TO_LOCAL_TIME_DIFFERENCE);

	$SQL = "SELECT salescatid,
				parentcatid,
				salescatname,
				active
			FROM salescat
			WHERE date_created >= '" . $LastTimeRun . "'
				OR date_updated >= '" . $LastTimeRun . "'
			ORDER BY salescatid";
	$Result = DB_query($SQL);
	$i = 0;
	if (DB_num_rows($Result) != 0) {
		if ($ShowMessages) {
			echo '<p class="page_title_text" align="center"><strong>' . _('Sales categories') . '</strong></p>';
			echo '<table class="selection">
					<tr>
						<th>' . _('SalesCatID') . '</th>
						<th>' . _('SalesCatName') . '</th>
						<th>' . _('Action') . '</th>
					</tr>';
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update sales categories in Opencart failed');
		$InsertErrMsg = _('The SQL to insert sales categories in Opencart failed');

		$k = 0; //row colour counter
		while ($MyRow = DB_fetch_array($Result)) {

			/* FIELD MATCHING */
			if ($MyRow['parentcatid'] == 0) {
				$Top = 1;
			} else {
				$Top = 0;
			}
			$StoreId = 0;
			$Column = 1;
			$Language_Id = 1; // for now NO multi language
			$SortOrder = 1;
			$Name = trim($MyRow['salescatname']);
			$Description = trim($MyRow['salescatname']);
			$MetaDescription = CreateMetaDescription('Sales category', trim($MyRow['salescatname']));
			$MetaKeyword = CreateMetaKeyword('', trim($MyRow['salescatname']));
			$CategoryId = $MyRow['salescatid'];
			$LanguagesSQL = "SELECT language_id
						FROM " . $oc_tableprefix . "language";
			$LanguagesResult = DB_query_oc($LanguagesSQL);
			if (DataExistsInOpenCart($oc_tableprefix . 'category', 'category_id', $MyRow['salescatid'])) {
				$Action = "Update";
				$SQLUpdate = "UPDATE " . $oc_tableprefix . "category
								SET parent_id 		= '" . $MyRow['parentcatid'] . "',
									status 			= '" . $MyRow['active'] . "',
									top 			= '" . $Top . "',
									date_modified 	= '" . $ServerNow . "'
								WHERE category_id 	= '" . $CategoryId . "'";
				$ResultUpdate = DB_query_oc($SQLUpdate, $UpdateErrMsg, $DbgMsg, true);
				while ($LanguagesRow = DB_fetch_array($LanguagesResult)) {
					$SQLUpdate = "UPDATE " . $oc_tableprefix . "category_description
									SET name= '" . $Name . "'
									WHERE category_id 	= '" . $CategoryId . "'
										AND language_id 		= '" . $LanguagesRow['language_id'] . "'";
					$ResultUpdate = DB_query_oc($SQLUpdate, $UpdateErrMsg, $DbgMsg, true);
				}

				// update SEO Keywords if needed
				$SEOQuery = 'category_id=' . $CategoryId;
				$SEOKeyword = CreateSEOKeyword($Name);
				MaintainUrlAlias($SEOQuery, $SEOKeyword, $oc_tableprefix);

			} else {
				$Action = "Insert";
				$SQLInsert = "INSERT INTO " . $oc_tableprefix . "category
								(category_id,
								image,
								parent_id,
								top,
								`column`,
								sort_order,
								status,
								date_added,
								date_modified)
							VALUES
								('" . $CategoryId . "',
								'',
								'" . $MyRow['parentcatid'] . "',
								'" . $Top . "',
								'" . $Column . "',
								'" . $SortOrder . "',
								'" . $MyRow['active'] . "',
								'" . $ServerNow . "',
								'" . $ServerNow . "'
								)";
				$ResultInsert = DB_query_oc($SQLInsert, $InsertErrMsg, $DbgMsg, true);
				while ($LanguagesRow = DB_fetch_array($LanguagesResult)) {
					$SQLInsert = "INSERT INTO " . $oc_tableprefix . "category_description
									(category_id,
									language_id,
									name,
									description,
									meta_description,
									meta_keyword)
								VALUES
									('" . $CategoryId . "',
									'" . $LanguagesRow['language_id'] . "',
									'" . $Name . "',
									'" . $Description . "',
									'" . $MetaDescription . "',
									'" . $MetaKeyword . "'
								)";
					$ResultInsert = DB_query_oc($SQLInsert, $InsertErrMsg, $DbgMsg, true);
				}
				$SQLInsert = "INSERT INTO " . $oc_tableprefix . "category_to_store
								(category_id,
								store_id)
							VALUES
								('" . $CategoryId . "',
								'" . $StoreId . "'
								)";
				$ResultInsert = DB_query_oc($SQLInsert, $InsertErrMsg, $DbgMsg, true);
				$SortOrder++;

				// insert SEO Keywords if needed
				$SEOQuery = 'category_id=' . $CategoryId;
				$SEOKeyword = CreateSEOKeyword($Name);
				MaintainUrlAlias($SEOQuery, $SEOKeyword, $oc_tableprefix);

			}
			if ($ShowMessages) {
				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} else {
					echo '<tr class="OddTableRows">';
					++$k;
				}
				printf('<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>', $MyRow['salescatid'], $Name, $Action);
			}
			if ($EmailText != '') {
				$EmailText = $EmailText . $MyRow['salescatid'] . " = " . $Name . " --> " . $Action . "\n";
			}
			++$i;
		}
		if ($ShowMessages) {
			echo '</table>';
		}
	}
	if ($ShowMessages) {
		if ($i > 0) {
			prnMsg('Remind to run Repair Categories on OpenCart!', 'warn');
		}
		prnMsg(locale_number_format($i, 0) . ' ' . _('Sales Categories synchronized from KwaMoja to OpenCart'), 'success');
	}
	if ($EmailText != '') {
		$EmailText = $EmailText . locale_number_format($i, 0) . ' ' . _('Sales Categories synchronized from KwaMoja to OpenCart') . "\n\n";
	}
	return $EmailText;
}

function SyncFeaturedList($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText = '') {

	/* Let's get the ID for the list of featured products for featured module
	we will need it later on to save the results in the appropiate setting */
	$SettingId = GetOpenCartSettingId(0, "featured", "featured_product", $oc_tableprefix);
	$ListFeaturedOpenCart = "";

	/* Look for the featured items in KwaMoja
	we'll recreate the full list everytime as it will be short and
	it's a list that will change quite often */
	$SQL = "SELECT DISTINCT(salescatprod.stockid)
			FROM salescatprod
			WHERE salescatprod.featured ='1'
			ORDER BY salescatprod.stockid";
	$Result = DB_query($SQL);
	$i = 0;
	if (DB_num_rows($Result) != 0) {
		if ($ShowMessages) {
			echo '<p class="page_title_text" align="center"><strong>' . _('Create featured list in OpenCart') . '</strong></p>';
			echo '<table class="selection">
					<tr>
						<th>' . _('StockID') . '</th>
						<th>' . _('OpenCartID') . '</th>
						<th>' . _('Action') . '</th>
					</tr>';
		}
		$Action = "Added";
		$k = 0; //row colour counter
		while ($MyRow = DB_fetch_array($Result)) {
			/* Field Matching */
			$Model = $MyRow['stockid'];

			// Let's get the OpenCart primary key for product
			$ProductId = GetOpenCartProductId($Model, $oc_tableprefix);

			// Let's build the list
			if ($i == 0) {
				$ListFeaturedOpenCart = strval($ProductId);
			} else {
				$ListFeaturedOpenCart = $ListFeaturedOpenCart . "," . strval($ProductId);
			}
			if ($ShowMessages) {
				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} else {
					echo '<tr class="OddTableRows">';
					++$k;
				}
				printf('<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						</tr>', $Model, $ProductId, $Action);
			}
			if ($EmailText != '') {
				$EmailText = $EmailText . str_pad($Model, 20, " ") . " = " . $ProductId . " --> " . $Action . "\n";
			}
			++$i;
		}
		UpdateSettingValueOpenCart($SettingId, $ListFeaturedOpenCart, $oc_tableprefix);
		if ($ShowMessages) {
			echo '</table>';
		}
	}
	if ($ShowMessages) {
		prnMsg(locale_number_format($i, 0) . ' ' . _('Products included in the featured list in OpenCart'), 'success');
	}
	if ($EmailText != '') {
		$EmailText = $EmailText . locale_number_format($i, 0) . ' ' . _('Products included in the featured list in OpenCart') . "\n\n";
	}
	return $EmailText;
}

function ActivateCategoryDependingOnQOH($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText = '') {
	$SQL = "SELECT salescatid,
				parentcatid,
				salescatname,
				active,
				(SELECT COUNT(locstock.quantity)
					FROM salescatprod,locstock
					WHERE salescat.salescatid = salescatprod.salescatid
						AND salescatprod.stockid = locstock.stockid
						AND locstock.loccode IN ('" . str_replace(',', "','", LOCATIONS_WITH_STOCK_FOR_ONLINE_SHOP) . "')
				) as qoh
			FROM salescat
			WHERE active = 1
				AND parentcatid != 0
			ORDER BY salescatname";
	$Result = DB_query($SQL);
	$i = 0;
	if (DB_num_rows($Result) != 0) {
		if ($ShowMessages) {
			echo '<p class="page_title_text" align="center"><strong>' . _('Activate/Inactivate Categories depending on QOH') . '</strong></p>';
			echo '<table class="selection">
					<tr>
						<th>' . _('Sales Category') . '</th>
						<th>' . _('QOH') . '</th>
						<th>' . _('Action') . '</th>
					</tr>';
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to Activate Categories depending QOH in Opencart failed');

		$k = 0; //row colour counter
		while ($MyRow = DB_fetch_array($Result)) {

			/* Field Matching */
			$CategoryId = $MyRow['salescatid'];
			$CategoryName = $MyRow['salescatname'];
			$CategoryQOH = $MyRow['qoh'];

			if ($CategoryQOH > 0) {
				$Status = 1;
				$Action = "Active";
			} else {
				$Status = 0;
				$Action = "Inactive QOH = 0";
			}

			$SQLUpdate = "UPDATE " . $oc_tableprefix . "category SET
								status = '" . $Status . "'
							WHERE category_id = '" . $CategoryId . "'";
			$ResultUpdate = DB_query_oc($SQLUpdate, $UpdateErrMsg, $DbgMsg, true);
			if ($ShowMessages) {
				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} else {
					echo '<tr class="OddTableRows">';
					++$k;
				}
				printf('<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
						</tr>', $CategoryName, locale_number_format($CategoryQOH, 0), $Action);
			}
			/*			if ($EmailText !=''){
			$EmailText = $EmailText . $CategoryName . " --> " . locale_number_format($CategoryQOH,0) . " --> " . $Action . "\n";
			}
			*/
			++$i;
		}
		if ($ShowMessages) {
			echo '</table>';
		}
	}
	if ($ShowMessages) {
		prnMsg(locale_number_format($i, 0) . ' ' . _('OpenCart Categories Activated / Inactivated depending on QOH'), 'success');
	}
	if ($EmailText != '') {
		$EmailText = $EmailText . locale_number_format($i, 0) . ' ' . _('OpenCart Categories Activated / Inactivated depending on QOH') . "\n\n";
	}
	return $EmailText;
}

function MaintainOpenCartOutletSalesCategories($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText = '') {

	/* Look for all products in OC marked as OUTLET and "something else"*/
	/*	$SQL = "SELECT " . $oc_tableprefix . "product.product_id,
	" . $oc_tableprefix . "product.model
	FROM " . $oc_tableprefix . "product_to_category ,
	" . $oc_tableprefix . "product
	WHERE " . $oc_tableprefix . "product.product_id = " . $oc_tableprefix . "product_to_category.product_id
	AND category_id NOT IN (" . OPENCART_OUTLET_CATEGORIES . ")
	AND " . $oc_tableprefix . "product.product_id IN (SELECT product_id
	FROM  " . $oc_tableprefix . "product_to_category
	WHERE  category_id IN (" . OPENCART_OUTLET_CATEGORIES . "))";
	*/
	$SQL = "SELECT " . $oc_tableprefix . "product.product_id,
				   " . $oc_tableprefix . "product.model
			FROM " . $oc_tableprefix . "product_to_category ,
				 " . $oc_tableprefix . "product
			WHERE " . $oc_tableprefix . "product.product_id = " . $oc_tableprefix . "product_to_category.product_id
				AND category_id IN (" . OPENCART_OUTLET_CATEGORIES . ")";
	$Result = DB_query_oc($SQL);
	$i = 0;
	if (DB_num_rows($Result) != 0) {
		if ($ShowMessages) {
			echo '<p class="page_title_text" align="center"><strong>' . _('Maintain Outlet Sales Categories') . '</strong></p>';
			echo '<table class="selection">
					<tr>
						<th>' . _('StockID') . '</th>
						<th>' . _('Action') . '</th>
					</tr>';
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update Product QOH in Opencart failed');

		$k = 0; //row colour counter
		while ($MyRow = DB_fetch_array($Result)) {

			$ProductId = $MyRow['product_id'];
			$Model = $MyRow['model'];

			$Action = "Delete sales categories not OUTLET";
			$SQLDelete = "DELETE FROM " . $oc_tableprefix . "product_to_category
							WHERE product_id = '" . $ProductId . "'
								AND category_id NOT IN (" . OPENCART_OUTLET_CATEGORIES . ")";
			$ResultDelete = DB_query_oc($SQLDelete, $UpdateErrMsg, $DbgMsg, true);
			if ($ShowMessages) {
				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} else {
					echo '<tr class="OddTableRows">';
					++$k;
				}
				printf('<td>%s</td>
						<td>%s</td>
						</tr>', $Model, $Action);
			}
			/*			if ($EmailText !=''){
			$EmailText = $EmailText . str_pad($Model, 20, " ") . " --> " . $Action . "\n";
			}
			*/
			++$i;
		}
		if ($ShowMessages) {
			echo '</table>';
		}
	}
	if ($EmailText != '') {
		$EmailText = $EmailText . locale_number_format($i, 0) . ' ' . _('OpenCart Outlet Sales Categories maintained') . "\n\n";
	}
	return $EmailText;
}

function MaintainKwaMojaOutletSalesCategories($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText = '') {

	/* Look for all products in KwaMoja marked as OUTLET and "something else"*/

	$SQL = "SELECT salescatprod.stockid
			FROM salescatprod
			WHERE salescatprod.salescatid IN (" . KWAMOJA_OUTLET_CATEGORIES . ")";
	$Result = DB_query($SQL);
	$i = 0;
	if (DB_num_rows($Result) != 0) {
		if ($ShowMessages) {
			echo '<p class="page_title_text" align="center"><strong>' . _('Maintain KwaMoja Outlet Sales Categories') . '</strong></p>';
			echo '<table class="selection">
					<tr>
						<th>' . _('StockID') . '</th>
						<th>' . _('Action') . '</th>
					</tr>';
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update outlet sales category in KwaMoja failed');

		$k = 0; //row colour counter
		while ($MyRow = DB_fetch_array($Result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				++$k;
			}

			$ProductId = $MyRow['stockid'];

			$Action = "Delete sales categories not OUTLET";
			$SQLDelete = "DELETE FROM salescatprod
							WHERE stockid = '" . $ProductId . "'
								AND salescatid NOT IN (" . KWAMOJA_OUTLET_CATEGORIES . ")";
			$ResultDelete = DB_query($SQLDelete, $UpdateErrMsg, $DbgMsg, true);
			if ($ShowMessages) {
				printf('<td>%s</td>
						<td>%s</td>
						</tr>', $ProductId, $Action);
			}
			/*			if ($EmailText !=''){
			$EmailText = $EmailText . str_pad($ProductId, 20, " ") . " --> " . $Action . "\n";
			}
			*/
			++$i;
		}
		if ($ShowMessages) {
			echo '</table>';
		}
	}
	if ($EmailText != '') {
		$EmailText = $EmailText . locale_number_format($i, 0) . ' ' . _('KwaMoja Outlet Sales Categories Maintained') . "\n\n";
	}
	return $EmailText;
}

function SyncMultipleImages($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText = '') {
	$ServerNow = GetServerTimeNow(SERVER_TO_LOCAL_TIME_DIFFERENCE);
	$Today = date('Y-m-d');

	if ($ShowMessages) {
		echo '<p class="page_title_text" align="center"><strong>' . _('Synchronize multiple images per item') . '</strong></p>';
		echo '<table class="selection">
				<tr>
					<th>' . _('KwaMoja Code') . '</th>
					<th>' . _('File') . '</th>
				</tr>';
	}
	$SQLTruncate = "TRUNCATE " . $oc_tableprefix . "product_image";
	$ResultSQLTruncate = DB_query_oc($SQLTruncate);

	$k = 0; //row colour counter
	$i = 0;
	// get all images in part_pics folder (ideally should be OpenCart images folder...)
	$imagefiles = getDirectoryTree($_SESSION['part_pics_dir'], 'jpg');
	foreach ($imagefiles as $file) {
		$multipleimage = 1;
		$exist_multiple = TRUE;
		while ($multipleimage <= 5) {
			$suffix = "." . $multipleimage;
			if (strpos($file, $suffix) > 0) {
				// GET stockid from filename
				$StockId = substr($file, 0, strpos($file, $suffix));
				// get Opencart productid
				$ProductId = GetOpenCartProductId($StockId, $oc_tableprefix);
				if ($ProductId > 0) {
					// insert info about multiple images
					$SQLInsert = "INSERT INTO " . $oc_tableprefix . "product_image
									(product_id,
									image,
									sort_order)
								VALUES
									('" . $ProductId . "',
									'" . PATH_OPENCART_IMAGES . $file . "',
									'" . $multipleimage . "')";
					$ResultInsert = DB_query_oc($SQLInsert, $InsertErrMsg, $DbgMsg, true);
					if ($ShowMessages) {
						if ($k == 1) {
							echo '<tr class="EvenTableRows">';
							$k = 0;
						} else {
							echo '<tr class="OddTableRows">';
							++$k;
						}
						printf('<td>%s</td>
								<td>%s</td>
								</tr>', $StockId, $file);
					}
					++$i;
				}
			}
			$multipleimage++;
		}
	}
	if ($ShowMessages) {
		echo '</table>';
		prnMsg(locale_number_format($i, 0) . ' ' . _('Multiple Images Synchronized'), 'success');
	}
	if ($EmailText != '') {
		$EmailText = $EmailText . locale_number_format($i, 0) . ' ' . _('Multiple Images Synchronized') . "\n\n";
	}
	return $EmailText;
}

function SyncRelatedItems($ShowMessages, $LastTimeRun, $oc_tableprefix, $EmailText = '') {
	$ServerNow = GetServerTimeNow(SERVER_TO_LOCAL_TIME_DIFFERENCE);

	$SQL = "SELECT stockid,
				related
			FROM relateditems
			WHERE date_created >= '" . $LastTimeRun . "'
				OR date_updated >= '" . $LastTimeRun . "'
			ORDER BY stockid, related";

	$Result = DB_query($SQL);
	$i = 0;
	if (DB_num_rows($Result) != 0) {
		if ($ShowMessages) {
			echo '<p class="page_title_text" align="center"><strong>' . _('Related Items') . '</strong></p>';
			echo '<table class="selection">
					<tr>
						<th>' . _('Item KwaMoja') . '</th>
						<th>' . _('Related KwaMoja') . '</th>
						<th>' . _('Item OC') . '</th>
						<th>' . _('Related OC') . '</th>
						<th>' . _('Action') . '</th>
					</tr>';
		}
		$DbgMsg = _('The SQL statement that failed was');
		$UpdateErrMsg = _('The SQL to update related items in Opencart failed');
		$InsertErrMsg = _('The SQL to insert related items in Opencart failed');

		$k = 0; //row colour counter
		while ($MyRow = DB_fetch_array($Result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				++$k;
			}

			/* FIELD MATCHING */
			$ProductId = GetOpenCartProductId($MyRow['stockid'], $oc_tableprefix);
			$RelatedId = GetOpenCartProductId($MyRow['related'], $oc_tableprefix);

			if (DataExistsInOpenCart($oc_tableprefix . 'product_related', 'product_id', $ProductId, 'related_id', $RelatedId)) {
				$Action = "Update";
			} else {
				$Action = "Insert";
				$SQLInsert = "INSERT INTO " . $oc_tableprefix . "product_related
								(product_id,
								related_id)
							VALUES
								('" . $ProductId . "',
								'" . $RelatedId . "'
								)";
				$ResultInsert = DB_query_oc($SQLInsert, $InsertErrMsg, $DbgMsg, true);
			}
			if ($ShowMessages) {
				printf('<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>', $MyRow['stockid'], $MyRow['related'], $ProductId, $RelatedId, $Action);
			}
			++$i;
		}
		if ($ShowMessages) {
			echo '</table>';
		}
	}
	if ($ShowMessages) {
		prnMsg(locale_number_format($i, 0) . ' ' . _('Pairs of related items synchronized from KwaMoja to OpenCart'), 'success');
	}
	if ($EmailText != '') {
		$EmailText = $EmailText . locale_number_format($i, 0) . ' ' . _('Pairs of related items synchronized from KwaMoja to OpenCart') . "\n\n";
	}
	return $EmailText;
}

?>