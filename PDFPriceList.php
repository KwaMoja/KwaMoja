<?php
/*	Script to print a price list by inventory category */
/*	Output column sizes:
 * stockmaster.stockid, varchar(20), len = 20chr
 * stockmaster.description, varchar(50), len = 50chr
 * prices.startdate, date, len = 10chr
 * prices.enddate, date/'No End Date', len = 12chr
 * custbranch.brname, varchar(40), len = 40chr
 * Gross Profit, calculated, len = 8chr
 * prices.price, decimal(20,4), len = 20chr + 4spaces */

/*	Please note that addTextWrap() YPos is a font-size-height further down than
	addText() and other functions. Use addText() instead of addTextWrap() to
	print left aligned elements.*/

include ('includes/PDFPriceListHeader.php');
include ('includes/session.php');

// Merges gets into posts:
if (isset($_GET['ShowObsolete'])) { // Show obsolete items.
	$_POST['ShowObsolete'] = $_GET['ShowObsolete'];
}

if (isset($_GET['ItemOrder'])) { // Option to select the order of the items in the report.
	$_POST['ItemOrder'] = $_GET['ItemOrder'];
}

if (isset($_POST['PrintPDF']) and isset($_POST['Categories']) and sizeOf($_POST['Categories']) > 0) {

	/*	if ($_POST['CustomerSpecials']=='Customer Special Prices Only') {
		// To do: For special prices, change from portrait to landscape orientation.
	}*/
	include ('includes/PDFStarter.php'); // Sets $PageNumber, page width, page height, top margin, bottom margin, left margin and right margin.
	$PDF->addInfo('Title', _('Price list by inventory category'));
	$PDF->addInfo('Subject', _('Price List'));

	$FontSize = 10;

	$line_height = 12;

	$WhereCurrency = '';
	if ($_POST['Currency'] != "All") {
		$WhereCurrency = " AND prices.currabrev = '" . $_POST['Currency'] . "' "; // Query element to select a currency.
		
	}

	$ShowObsolete = " AND `stockmaster`.`discontinued` != 1 "; // Query element to exclude obsolete items.
	if (isset($_POST['ShowObsolete'])) {
		$ShowObsolete = ''; // Cleans the query element to exclude obsolete items.
		
	}

	// Option to select the order of the items in the report:
	$ItemOrder = ',stockmaster.stockid'; // Query element to sort by currency, item_stock_category, and item_code.
	if ($_POST['ItemOrder'] == 'Description') {
		$ItemOrder = ',stockmaster.description'; // Query element to sort by currency, item_stock_category, and item_description.
		
	}

	/*Now figure out the inventory data to report for the category range under review */
	if ($_POST['CustomerSpecials'] == _('Customer Special Prices Only')) {

		if ($_SESSION['CustomerID'] == '') {
			$Title = _('Special price List - No Customer Selected');
			$ViewTopic = 'SalesTypes'; // Filename in ManualContents.php's TOC.
			$BookMark = 'PDFPriceList'; // Anchor's id in the manual's html document.
			include ('includes/header.php');
			echo '<br />';
			prnMsg(_('The customer must first be selected from the select customer link') . '. ' . _('Re-run the price list once the customer has been selected'));
			echo '<br /><br /><a href="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '">' . _('Back') . '</a>';
			include ('includes/footer.php');
			exit;
		}
		if (!is_date($_POST['EffectiveDate'])) {
			$Title = _('Special price List - No Customer Selected');
			$ViewTopic = 'SalesTypes'; // Filename in ManualContents.php's TOC.
			$BookMark = 'PDFPriceList'; // Anchor's id in the manual's html document.
			include ('includes/header.php');
			prnMsg(_('The effective date must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
			echo '<br /><br /><a href="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '">' . _('Back') . '</a>';
			include ('includes/footer.php');
			exit;
		}

		$SQL = "SELECT debtorsmaster.name,
				debtorsmaster.salestype
				FROM debtorsmaster
				WHERE debtorno = '" . $_SESSION['CustomerID'] . "'";
		$CustNameResult = DB_query($SQL);
		$CustNameRow = DB_fetch_row($CustNameResult);
		$CustomerName = $CustNameRow[0];
		$SalesType = $CustNameRow[1];

		$SQL = "SELECT
					prices.typeabbrev,
					prices.stockid,
					stockmaster.description,
					stockmaster.longdescription,
					prices.currabrev,
					prices.startdate,
					prices.enddate,
					prices.price,
					stockcosts.materialcost+stockcosts.labourcost+stockcosts.overheadcost AS standardcost,
					stockmaster.categoryid,
					stockcategory.categorydescription,
					prices.debtorno,
					prices.branchcode,
					custbranch.brname,
					currencies.decimalplaces
				FROM stockmaster
					INNER JOIN	stockcategory ON stockmaster.categoryid=stockcategory.categoryid
					INNER JOIN	stockcosts ON stockmaster.stockid=stockcosts.stockid AND stockcosts.succeeded=0
					INNER JOIN prices ON stockmaster.stockid=prices.stockid
					INNER JOIN currencies ON prices.currabrev=currencies.currabrev
					LEFT JOIN custbranch ON prices.debtorno=custbranch.debtorno AND prices.branchcode=custbranch.branchcode
				WHERE prices.typeabbrev = '" . $SalesType . "'
					AND stockmaster.categoryid IN ('" . implode("','", $_POST['Categories']) . "')
					AND prices.debtorno='" . $_SESSION['CustomerID'] . "'
					AND prices.startdate<='" . FormatDateForSQL($_POST['EffectiveDate']) . "'
					AND (prices.enddate='0000-00-00' OR prices.enddate >'" . FormatDateForSQL($_POST['EffectiveDate']) . "')" . $WhereCurrency . $ShowObsolete . "
				ORDER BY
					prices.currabrev,
					stockcategory.categorydescription,
					stockmaster.stockid,
					prices.startdate," . $ItemOrder;

	} else {
		/* the sales type list only */

		$SQL = "SELECT sales_type FROM salestypes WHERE typeabbrev='" . $_POST['SalesType'] . "'";
		$SalesTypeResult = DB_query($SQL);
		$SalesTypeRow = DB_fetch_row($SalesTypeResult);
		$SalesTypeName = $SalesTypeRow[0];

		$SQL = "SELECT
					prices.typeabbrev,
					prices.stockid,
					prices.startdate,
					prices.enddate,
					stockmaster.description,
					stockmaster.longdescription,
					prices.currabrev,
					prices.price,
					stockcosts.materialcost+stockcosts.labourcost+stockcosts.overheadcost AS standardcost,
					stockmaster.categoryid,
					stockcategory.categorydescription,
					currencies.decimalplaces
				FROM stockmaster
					INNER JOIN	stockcategory ON stockmaster.categoryid=stockcategory.categoryid
					INNER JOIN	stockcosts ON stockmaster.stockid=stockcosts.stockid AND stockcosts.succeeded=0
					INNER JOIN prices ON stockmaster.stockid=prices.stockid
					INNER JOIN currencies ON prices.currabrev=currencies.currabrev
				WHERE stockmaster.categoryid IN ('" . implode("','", $_POST['Categories']) . "')
					AND prices.typeabbrev='" . $_POST['SalesType'] . "'
					AND prices.startdate<='" . FormatDateForSQL($_POST['EffectiveDate']) . "'
					AND (prices.enddate='0000-00-00' OR prices.enddate>'" . FormatDateForSQL($_POST['EffectiveDate']) . "')" . $WhereCurrency . $ShowObsolete . "
					AND prices.debtorno=''
				ORDER BY
					prices.currabrev,
					stockcategory.categorydescription,
					stockmaster.stockid,
					prices.startdate " . $ItemOrder;
	}

	$PricesResult = DB_query($SQL, '', '', false, false);
	if (DB_error_no() != 0) {
		$Title = _('Price List') . ' - ' . _('Problem Report....');
		include ('includes/header.php');
		prnMsg(_('The Price List could not be retrieved by the SQL because') . ' - ' . DB_error_msg(), 'error');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($Debug == 1) {
			prnMsg(_('For debugging purposes the SQL used was') . ': ' . $SQL, 'error');
		}
		include ('includes/footer.php');
		exit;
	}
	if (DB_num_rows($PricesResult) == 0) {
		$Title = _('Print Price List Error');
		include ('includes/header.php');
		prnMsg(_('There were no price details to print out for the customer or category specified'), 'warn');
		echo '<br /><a href="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '">' . _('Back') . '</a>';
		include ('includes/footer.php');
		exit;
	}

	PageHeader();

	$CurrCode = '';
	$Category = '';
	$CatTot_Val = 0;

	$PDF->SetFillColor(238, 238, 238);
	$Fill = false;

	while ($PriceList = DB_fetch_array($PricesResult)) {

		if ($CurrCode != $PriceList['currabrev']) {
			$FontSize = 10;
			if ($YPos < $Bottom_Margin + $FontSize * 3) { // If the next line reaches the bottom margin, do PageHeader().
				PageHeader();
			}
			$YPos-= $FontSize; // Jumps additional line before.
			require_once ('includes/CurrenciesArray.php'); // To get the currency name from the currency code.
			$LeftOvers = $PDF->addText($Left_Margin, $YPos, $FontSize, $PriceList['currabrev'] . ' - ' . _($CurrencyName[$PriceList['currabrev']]));
			$CurrCode = $PriceList['currabrev'];
			$YPos-= $FontSize; // End-of-line line-feed.
			
		}

		if ($Category != $PriceList['categoryid']) {
			$FontSize = 10;
			if ($YPos < $Bottom_Margin + $FontSize * 3) { // If the next line reaches the bottom margin, do PageHeader().
				PageHeader();
			}
			$YPos-= $FontSize; // Jumps additional line before.
			$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, $FontSize, $PriceList['categoryid'] . ' - ' . $PriceList['categorydescription']);
			$Category = $PriceList['categoryid'];
			$YPos-= $FontSize; // End-of-line line-feed.
			
		}

		$FontSize = 8;
		$PDF->addTextWrap($Left_Margin, $YPos - $FontSize, 80, $FontSize, $PriceList['stockid'], 'left', 0, $Fill);
		$PDF->addTextWrap($Left_Margin + 80, $YPos - $FontSize, 200, $FontSize, $PriceList['description'], 'left', 0, $Fill);
		$PDF->addTextWrap($Left_Margin + 280, $YPos - $FontSize, 60, $FontSize, ConvertSQLDate($PriceList['startdate']), 'left', 0, $Fill);
		if ($PriceList['enddate'] != '0000-00-00') {
			$DisplayEndDate = ConvertSQLDate($PriceList['enddate']);
		} else {
			$DisplayEndDate = _('No End Date');
		}
		$PDF->addTextWrap($Left_Margin + 320, $YPos - $FontSize, 80, $FontSize, $DisplayEndDate, 'left', 0, $Fill);

		// Shows gross profit percentage:
		if ($_POST['ShowGPPercentages'] == 'Yes') {
			$DisplayGPPercent = '-';
			if ($PriceList['price'] != 0) {
				$DisplayGPPercent = locale_number_format((($PriceList['price'] - $PriceList['standardcost']) * 100 / $PriceList['price']), 2) . '%';
			}
			$PDF->addTextWrap($Page_Width - $Right_Margin - 128, $YPos - $FontSize, 32, $FontSize, $DisplayGPPercent, 'right', 0, $Fill);
		} else {
			$PDF->addTextWrap($Page_Width - $Right_Margin - 128, $YPos - $FontSize, 32, $FontSize, '', 'right', 0, $Fill);
		}
		// Displays unit price:
		$PDF->addTextWrap($Page_Width - $Right_Margin - 96, $YPos - $FontSize, 96, $FontSize, locale_number_format($PriceList['price'], $PriceList['decimalplaces']), 0, $Fill);
		if ($_POST['CustomerSpecials'] == 'Customer Special Prices Only') {
			/*Need to show to which branch the price relates */
			if ($PriceList['branchcode'] != '') {
				$PDF->addTextWrap($Left_Margin + 425, $YPos - $FontSize, 50, $FontSize, $PriceList['brname'], 'left', 0, $Fill);
			} else {
				$PDF->addTextWrap($Left_Margin + 425, $YPos - $FontSize, 50, $FontSize, _('All'), 'left', 0, $Fill);
			}
			$YPos-= $FontSize; // End-of-line line-feed.
			
		} elseif ($_POST['CustomerSpecials'] == 'Full Description') {
			$YPos-= $FontSize;

			// Prints item image:
			$SupportedImgExt = array('png', 'jpg', 'jpeg');
			$ImageFileArray = glob($_SESSION['part_pics_dir'] . '/' . $PriceList['stockid'] . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE);
			$ImageFile = reset($ImageFileArray);
			$YPosImage = $YPos; // Initializes the image bottom $YPos.
			if (file_exists($ImageFile)) {
				if ($YPos - 36 < $Bottom_Margin) { // If the image bottom reaches the bottom margin, do PageHeader().
					PageHeader();
				}
				$LeftOvers = $PDF->Image($ImageFile, $Left_Margin + 3, $Page_Height - $YPos, 36, 36);
				$YPosImage = $YPos - 36; // Stores the $YPos of the image bottom (see bottom).
				
			}
			// Prints stockmaster.longdescription:
			$XPos = $Left_Margin + 80; // Takes out this calculation from the loop.
			$Width = $Page_Width - $Right_Margin - $XPos; // Takes out this calculation from the loop.
			$FontSize2 = $FontSize * 0.80; // Font size and line height of Full Description section.
			$Split = explode("\r\n", $PriceList['longdescription']);
			foreach ($Split as $LeftOvers) {
				$LeftOvers = stripslashes($LeftOvers);
				while (mb_strlen($LeftOvers) > 1) {
					if ($YPos < $Bottom_Margin) { // If the description line reaches the bottom margin, do PageHeader().
						PageHeader();
						$YPosImage = $YPos; // Resets the image bottom $YPos.
						
					}
					$LeftOvers = $PDF->addTextWrap($XPos, $YPos - $FontSize2, $Width, $FontSize2, $LeftOvers, 'j', 0, $Fill);
					$YPos-= $FontSize2;
					$LeftOvers = $PDF->Image($_SESSION['part_pics_dir'] . '/' . $PriceList['stockid'] . '.jpg', 265, $Page_Height - $Top_Margin - $YPos + 33, 33, 33);
				}
			}
			// Assigns to $YPos the lowest $YPos value between the image and the description:
			$YPos = min($YPosImage, $YPos);
			$YPos-= $FontSize; // Jumps additional line after the image and the description.
			
		} else {
			$PDF->addTextWrap($Left_Marginv + 425, $YPos - $FontSize, 50, $FontSize, '', 'left', 0, $Fill);
			$YPos-= $FontSize; // End-of-line line-feed.
			
		} /* Endif full descriptions*/

		if ($YPos < $Bottom_Margin + $line_height) {
			PageHeader();
		}
		$Fill = !$Fill;
	}
	/*end inventory valn while loop */

	// Warns if obsolete items are included:
	if ($_POST['ShowObsolete']) {
		$FontSize = 8;
		$YPos-= $FontSize; // Jumps additional line.
		if ($YPos < $Bottom_Margin + $FontSize) {
			PageHeader();
		}
		$PDF->addText($Left_Margin, $YPos, $FontSize, _('* Obsolete items included.')); // Warning text.
		
	}

	$FontSize = 10;
	$FileName = $_SESSION['DatabaseName'] . '_' . _('Price_List') . '_' . date('Y-m-d') . '.pdf';
	ob_clean();
	$PDF->OutputD($FileName);
	$PDF->__destruct();

} else {
	/*The option to print PDF was not hit */

	$Title = _('Price Listing');
	$ViewTopic = 'SalesTypes'; // Filename in ManualContents.php's TOC.
	$BookMark = 'PDFPriceList'; // Anchor's id in the manual's html document.
	include ('includes/header.php');

	echo '<p class="page_title_text">
			<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/customer.png" title="', _('Price List'), '" />', ' ', _('Print a price list by inventory category'), '
		</p>';

	if (!isset($_POST['FromCriteria']) or !isset($_POST['ToCriteria'])) {

		echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
		echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
		echo '<fieldset>
				<legend>', _('Select report criteria'), '</legend>
				<field>
					<label for="Categories">', _('Select Inventory Categories'), ':</label>
					<select autofocus="autofocus" required="required" size="12" name="Categories[]" multiple="multiple">';

		$SQL = "SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription";
		$CatResult = DB_query($SQL);
		while ($MyRow = DB_fetch_array($CatResult)) {
			if (isset($_POST['Categories']) and in_array($MyRow['categoryid'], $_POST['Categories'])) {
				echo '<option selected="selected" value="', $MyRow['categoryid'], '">', $MyRow['categorydescription'], '</option>';
			} else {
				echo '<option value="', $MyRow['categoryid'], '">', $MyRow['categorydescription'], '</option>';
			}
		}
		echo '</select>
			<fieldhelp>', _('Choose the stock category or multiple categories to report on.'), '</fieldhelp>
		</field>';

		echo '<field>
				<label for="SalesType">', _('For Sales Type/Price List'), ':</label>
				<select name="SalesType">';
		$SQL = "SELECT sales_type, typeabbrev FROM salestypes";
		$SalesTypesResult = DB_query($SQL);

		while ($MyRow = DB_fetch_array($SalesTypesResult)) {
			echo '<option value="', $MyRow['typeabbrev'], '">', $MyRow['sales_type'], '</option>';
		}
		echo '</select>
			<fieldhelp>', _('Select the sales type to report on'), '</fieldhelp>
		</field>';

		echo '<field>
				<label for="Currency">', _('For Currency'), ':</label>
				<select name="Currency">';
		$SQL = "SELECT currabrev, currency FROM currencies ORDER BY currency";
		$CurrencyResult = DB_query($SQL);
		echo '<option selected="selected" value="All">', _('All'), '</option>';
		while ($MyRow = DB_fetch_array($CurrencyResult)) {
			echo '<option value="', $MyRow['currabrev'], '">', $MyRow['currency'], '</option>';
		}
		echo '</select>
			<fieldhelp>', _('Select the currencies to report on. To report on all currencies select All'), '</fieldhelp>
		</field>';

		echo '<field>
				<label for="ShowGPPercentages">', _('Show Gross Profit %'), ':</label>
				<select required="required" name="ShowGPPercentages">
					<option selected="selected" value="No">', _('Prices Only'), '</option>
					<option value="Yes">', _('Show GP % too'), '</option>
				</select>
				<fieldhelp>', _('To include the gross profit percentage in the report select Yes otherwise select no.'), '</fieldhelp>
			</field>';

		echo '<field>
				<label for="CustomerSpecials">', _('Price Listing Type'), ':</label>
				<select required="required" name="CustomerSpecials">
					<option selected="selected" value="Sales Type Prices">', _('Default Sales Type Prices'), '</option>
					<option value="Customer Special Prices Only">', _('Customer Special Prices Only'), '</option>
					<option value="Full Description">', _('Full Description'), '</option>
				</select>
				<fieldhelp>', _('Show customer special prices, or just the default ones.'), '</fieldhelp>
			</field>';

		echo '<field>
				<label for="EffectiveDate">', _('Effective As At'), ':</label>
				<input type="text" size="11" required="required" maxlength="10" class="date" name="EffectiveDate" value="', Date($_SESSION['DefaultDateFormat']), '" />
				<fieldhelp>', _('Show prices that are effective on this date.'), '</fieldhelp>
			</field>';

		if (isset($_POST['ShowObsolete'])) {
			$Checked = ' checked="checked" ';
		} else {
			$Checked = ' ';
		}

		echo '<field>
				<label for="ShowObsolete">', _('Show obsolete items'), ':</label>
				<input', $Checked, 'id="ShowObsolete" name="ShowObsolete" type="checkbox" />
				<fieldhelp>', _('Check this box to show the obsolete items'), ':</fieldhelp>
			</field>';

		// Option to select the order of the items in the report:
		echo '<field>
				<label for="ItemOrder">', _('Sort items by'), ':</label>
				<input checked="checked" name="ItemOrder" type="radio" value="Code">', _('Currency, category and code'), '<br>
				<label>&nbsp;</label>
				<input name="ItemOrder" type="radio" value="Description">', _('Currency, category and description'), '
				<fieldhelp>', _('Select the order of the items in the report'), '</fieldhelp>
			</field>';

		echo '</fieldset>';
		echo '<div class="centre">
				<input type="submit" name="PrintPDF" value="', _('Print PDF'), '" />
			</div>';
		echo '</form>';
	}
	include ('includes/footer.php');

}
/*end of else not PrintPDF */

?>