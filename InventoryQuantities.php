<?php
/* InventoryQuantities.php - Report of parts with quantity. Sorts by part and shows
 * all locations where there are quantities of the part
*/

include ('includes/session.php');
if (isset($_POST['PrintPDF']) or isset($_POST['CSV'])) {

	include ('includes/PDFStarter.php');
	$PDF->addInfo('Title', _('Inventory Quantities Report'));
	$PDF->addInfo('Subject', _('Parts With Quantities'));
	$FontSize = 9;
	$PageNumber = 1;
	$line_height = 12;

	$Xpos = $Left_Margin + 1;
	$WhereCategory = ' ';
	$CatDescription = ' ';
	if ($_POST['StockCat'] != 'All') {
		$WhereCategory = " AND stockmaster.categoryid='" . $_POST['StockCat'] . "'";
		$SQL = "SELECT categoryid,
					categorydescription
				FROM stockcategory
				WHERE categoryid='" . $_POST['StockCat'] . "' ";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		$CatDescription = $MyRow[1];
	}

	if ($_POST['Selection'] == 'All') {
		$SQL = "SELECT locstock.stockid,
					stockmaster.description,
					locstock.loccode,
					locations.locationname,
					locstock.quantity,
					locstock.reorderlevel,
					stockmaster.decimalplaces,
					stockmaster.serialised,
					stockmaster.controlled
				FROM locstock
				INNER JOIN stockmaster
					ON locstock.stockid=stockmaster.stockid
				INNER JOIN locations
					ON locstock.loccode=locations.loccode
				WHERE locstock.quantity <> 0
					AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M') " . $WhereCategory . "
					ORDER BY locstock.stockid,
							locstock.loccode";
	} else {
		// sql to only select parts in more than one location
		// The SELECT statement at the beginning of the WHERE clause limits the selection to
		// parts with quantity in more than one location
		$SQL = "SELECT locstock.stockid,
					stockmaster.description,
					locstock.loccode,
					locations.locationname,
					locstock.quantity,
					locstock.reorderlevel,
					stockmaster.decimalplaces,
					stockmaster.serialised,
					stockmaster.controlled
				FROM locstock INNER JOIN stockmaster
				ON locstock.stockid=stockmaster.stockid
				INNER JOIN locations
				ON locstock.loccode=locations.loccode
				WHERE (SELECT count(*)
					  FROM locstock
					  WHERE stockmaster.stockid = locstock.stockid
					  AND locstock.quantity <> 0
					  GROUP BY locstock.stockid) > 1
				AND locstock.quantity <> 0
				AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M') " . $WhereCategory . "
				ORDER BY locstock.stockid,
						locstock.loccode";
	}

	$Result = DB_query($SQL, '', '', false, true);

	if (DB_error_no() != 0) {
		$Title = _('Inventory Quantities') . ' - ' . _('Problem Report');
		include ('includes/header.php');
		prnMsg(_('The Inventory Quantity report could not be retrieved by the SQL because') . ' ' . DB_error_msg(), 'error');
		echo '<br /><a href="', $RootPath, '/index.php">', _('Back to the menu'), '</a>';
		if ($Debug == 1) {
			echo '<br />' . $SQL;
		}
		include ('includes/footer.php');
		exit;
	}
	if (DB_num_rows($Result) == 0) {
		$Title = _('Print Inventory Quantities Report');
		include ('includes/header.php');
		prnMsg(_('There were no items with inventory quantities'), 'error');
		echo '<br /><a href="', $RootPath, '/index.php">', _('Back to the menu'), '</a>';
		include ('includes/footer.php');
		exit;
	}

	if (isset($_POST['PrintPDF'])) {
		PrintHeader($PDF, $YPos, $PageNumber, $Page_Height, $Top_Margin, $Left_Margin, $Page_Width, $Right_Margin, $CatDescription);

		$FontSize = 8;

		$holdpart = " ";
		while ($MyRow = DB_fetch_array($Result)) {
			if ($MyRow['stockid'] != $holdpart) {
				$YPos-= (2 * $line_height);
				$holdpart = $MyRow['stockid'];
			} else {
				$YPos-= ($line_height);
			}

			// Parameters for addTextWrap are defined in /includes/class.pdf.php
			// 1) X position 2) Y position 3) Width
			// 4) Height 5) Text 6) Alignment 7) Border 8) Fill - True to use SetFillColor
			// and False to set to transparent
			$PDF->addTextWrap(50, $YPos, 100, $FontSize, $MyRow['stockid'], '', 0);
			$PDF->addTextWrap(150, $YPos, 150, $FontSize, $MyRow['description'], '', 0);
			$PDF->addTextWrap(310, $YPos, 60, $FontSize, $MyRow['loccode'], 'left', 0);
			$PDF->addTextWrap(370, $YPos, 50, $FontSize, locale_number_format($MyRow['quantity'], $MyRow['decimalplaces']), 'right', 0);
			$PDF->addTextWrap(420, $YPos, 50, $FontSize, locale_number_format($MyRow['reorderlevel'], $MyRow['decimalplaces']), 'right', 0);

			if ($YPos < $Bottom_Margin + $line_height) {
				PrintHeader($PDF, $YPos, $PageNumber, $Page_Height, $Top_Margin, $Left_Margin, $Page_Width, $Right_Margin, $CatDescription);
			}
		}
		/*end while loop */

		if ($YPos < $Bottom_Margin + $line_height) {
			PrintHeader($PDF, $YPos, $PageNumber, $Page_Height, $Top_Margin, $Left_Margin, $Page_Width, $Right_Margin, $CatDescription);
		}
		/*Print out the grand totals */

		$PDF->OutputD($_SESSION['DatabaseName'] . '_Inventory_Quantities_' . Date('Y-m-d') . '.pdf');
		$PDF->__destruct();
	} elseif (isset($_POST['CSV'])) {
		$CSVListing = _('Stock ID') . ',' . _('Description') . ',' . _('Location Code') . ',' . _('Location') . ',' . _('Quantity') . ',' . _('Reorder Level') . ',' . _('Decimal Places') . ',' . _('Serialised') . ',' . _('Controlled') . "\n";
		while ($InventoryQties = DB_fetch_row($Result)) {
			$CSVListing.= implode(',', $InventoryQties) . "\n";
		}
		header('Content-Encoding: UTF-8');
		header('Content-type: text/csv; charset=UTF-8');
		header("Content-disposition: attachment; filename=InventoryQuantities_" . '.csv');
		header("Pragma: public");
		header("Expires: 0");
		echo "\xEF\xBB\xBF"; // UTF-8 BOM
		echo $CSVListing;
		exit;
	}
} else {
	/*The option to print PDF was not hit so display form */

	$Title = _('Inventory Quantities Reporting');
	include ('includes/header.php');
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/inventory.png" title="', _('Inventory'), '" alt="" />', ' ', _('Inventory Quantities Report'), '
		</p>';

	echo '<div class="page_help_text">', _('Use this report to display the quantity of Inventory items in different categories.'), '</div><br />';

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<fieldset>
			<legend>', _('Report Criteria'), '</legend>
			<field>
				<label for="Selection">', _('Selection'), ':</label>
				<select name="Selection">
					<option selected="selected" value="All">', _('All'), '</option>
					<option value="Multiple">', _('Only Parts With Multiple Locations'), '</option>
				</select>
			</field>';

	$SQL = "SELECT categoryid,
				categorydescription
			FROM stockcategory
			ORDER BY categorydescription";
	$Result1 = DB_query($SQL);
	if (DB_num_rows($Result1) == 0) {
		echo '</fieldset>';
		prnMsg(_('There are no stock categories currently defined please use the link below to set them up'), 'warn');
		echo '<br /><a href="', $RootPath, '/StockCategories.php">', _('Define Stock Categories'), '</a>';
		include ('includes/footer.php');
		exit;
	}

	echo '<field>
			<label for="StockCat">', _('In Stock Category'), ':</label>
			<select name="StockCat">';
	if (!isset($_POST['StockCat'])) {
		$_POST['StockCat'] = 'All';
	}
	if ($_POST['StockCat'] == 'All') {
		echo '<option selected="selected" value="All">', _('All'), '</option>';
	} else {
		echo '<option value="All">', _('All'), '</option>';
	}
	while ($MyRow1 = DB_fetch_array($Result1)) {
		if ($MyRow1['categoryid'] == $_POST['StockCat']) {
			echo '<option selected="selected" value="', $MyRow1['categoryid'], '">', $MyRow1['categorydescription'], '</option>';
		} else {
			echo '<option value="', $MyRow1['categoryid'], '">', $MyRow1['categorydescription'], '</option>';
		}
	}
	echo '</select>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="PrintPDF" value="', _('Print PDF'), '" />
			<input type="submit" name="CSV" value="', _('Output to CSV'), '" />
		</div>';

	echo '</form>';
	include ('includes/footer.php');

}
/*end of else not PrintPDF */

function PrintHeader(&$PDF, &$YPos, &$PageNumber, $Page_Height, $Top_Margin, $Left_Margin, $Page_Width, $Right_Margin, $CatDescription) {

	/*PDF page header for Reorder Level report */
	if ($PageNumber > 1) {
		$PDF->newPage();
	}
	$line_height = 12;
	$FontSize = 9;
	$YPos = $Page_Height - $Top_Margin;

	$PDF->addTextWrap($Left_Margin, $YPos, 300, $FontSize, $_SESSION['CompanyRecord']['coyname']);

	$YPos-= $line_height;

	$PDF->addTextWrap($Left_Margin, $YPos, 150, $FontSize, _('Inventory Quantities Report'));
	$PDF->addTextWrap($Page_Width - $Right_Margin - 150, $YPos, 160, $FontSize, _('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '   ' . _('Page') . ' ' . $PageNumber, 'left');
	$YPos-= $line_height;
	$PDF->addTextWrap($Left_Margin, $YPos, 50, $FontSize, _('Category'));
	$PDF->addTextWrap(95, $YPos, 50, $FontSize, $_POST['StockCat']);
	$PDF->addTextWrap(160, $YPos, 150, $FontSize, $CatDescription, 'left');
	$YPos-= (2 * $line_height);

	/*set up the headings */
	$Xpos = $Left_Margin + 1;

	$PDF->addTextWrap(50, $YPos, 100, $FontSize, _('Part Number'), 'left');
	$PDF->addTextWrap(150, $YPos, 150, $FontSize, _('Description'), 'left');
	$PDF->addTextWrap(310, $YPos, 60, $FontSize, _('Location'), 'left');
	$PDF->addTextWrap(370, $YPos, 50, $FontSize, _('Quantity'), 'right');
	$PDF->addTextWrap(420, $YPos, 50, $FontSize, _('Reorder'), 'right');
	$YPos-= $line_height;
	$PDF->addTextWrap(415, $YPos, 50, $FontSize, _('Level'), 'right');

	$FontSize = 8;
	$PageNumber++;
} // End of PrintHeader() function

?>