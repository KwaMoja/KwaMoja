<?php

include('includes/session.inc');
$Title = _('Import Items');
include('includes/header.inc');

echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/inventory.png" title="' . _('Import Stock Items from .csv') . '" />' . ' ' . _('Import Stock Items from .csv') . '</p>';

// If this script is called with a file object, then the file contents are imported
// If this script is called with the gettemplate flag, then a template file is served
// Otherwise, a file upload form is displayed
// The CSV file must be saved in a format like the template in the import module I.E. "RECVALUE","RECVALUE2". The CSV file needs ANSI encoding for the import to work properly.

$ItemDescriptionLanguagesArray = explode(',',$_SESSION['ItemDescriptionLanguages']);//WARNING: if the last character is a ",", there are n+1 languages.

$FieldHeadings = array(
	'StockID', //  0 'STOCKID',
	'Description', //  1 'DESCRIPTION',
	'LongDescription', //  2 'LONGDESCRIPTION',
	'CategoryID', //  3 'CATEGORYID',
	'Units', //  4 'UNITS',
	'MBFlag', //  5 'MBFLAG',
	'EOQ', //  6 'EOQ',
	'Discontinued', //  7 'DISCONTINUED',
	'Controlled', //  8 'CONTROLLED',
	'Serialised', //  9 'SERIALISED',
	'Perishable', // 10 'PERISHABLE',
	'Volume', // 11 'VOLUME',
	'GrossWeight', // 12 'KGS',
	'BarCode', // 13 'BARCODE',
	'DiscountCategory', // 14 'DISCOUNTCATEGORY',
	'TaxCat', // 15 'TAXCAT',
	'DecimalPlaces', // 16 'DECIMALPLACES',
	'ItemPDF' // 17 'ITEMPDF'
);

if (count($ItemDescriptionLanguagesArray) > 1) {
	foreach ($ItemDescriptionLanguagesArray as $LanguageId) {
		if ($LanguageId != '') {
			$FieldHeadings[] = 'Description-' . $LanguageId;
			$FieldHeadings[] = 'LongDescription-' . $LanguageId;
		}
	}
}

$Defaults = array(
	'', //  0 'STOCKID',
	'', //  1 'DESCRIPTION',
	'', //  2 'LONGDESCRIPTION',
	'', //  3 'CATEGORYID',
	'each', //  4 'UNITS',
	'B', //  5 'MBFLAG',
	'0', //  6 'EOQ',
	'0', //  7 'DISCONTINUED',
	'0', //  8 'CONTROLLED',
	'0', //  9 'SERIALISED',
	'0', // 10 'PERISHABLE',
	'0', // 11 'VOLUME',
	'0', // 12 'KGS',
	'', // 13 'BARCODE',
	'', // 14 'DISCOUNTCATEGORY',
	'1', // 15 'TAXCAT',
	'0', // 16 'DECIMALPLACES',
	'none' // 17 'ITEMPDF'
);

if (count($ItemDescriptionLanguagesArray) > 1) {
	foreach ($ItemDescriptionLanguagesArray as $LanguageId) {
		if ($LanguageId != '') {
			$Defaults[] = '';
			$Defaults[] = '';
		}
	}
}

if (isset($_FILES['userfile']) and $_FILES['userfile']['name']) { //start file processing

	//initialize
	$FieldTarget = 16 + (count($ItemDescriptionLanguagesArray) * 2);
	$InputError = 0;

	//check file info
	$FileName = $_FILES['userfile']['name'];
	$TempName = $_FILES['userfile']['tmp_name'];
	$FileSize = $_FILES['userfile']['size'];

	//get file handle
	$FileHandle = fopen($TempName, 'r');

	//get the header row
	$HeadRow = fgetcsv($FileHandle, 10000, ",",'"');  // Modified to handle " "" " enclosed csv - useful if you need to include commas in your text descriptions

	//check for correct number of fields
	if (count($HeadRow) != count($FieldHeadings)) {
		prnMsg(_('File contains ' . count($HeadRow) . ' columns, expected ' . count($FieldHeadings) . '. Try downloading a new template.'), 'error');
		fclose($FileHandle);
		include('includes/footer.inc');
		exit;
	}

	//test header row field name and sequence
	$head = 0;
	foreach ($HeadRow as $HeadField) {
		if (mb_strtoupper($HeadField) != mb_strtoupper($FieldHeadings[$head])) {
			prnMsg(_('File contains incorrect headers (' . mb_strtoupper($HeadField) . ' != ' . mb_strtoupper($FieldHeadings[$head]) . '. Try downloading a new template.'), 'error');
			fclose($FileHandle);
			include('includes/footer.inc');
			exit;
		}
		$head++;
	}

	//start database transaction
	DB_Txn_Begin();

	//loop through file rows
	$row = 1;
	while (($MyRow = fgetcsv($FileHandle, 10000, ",")) !== FALSE) {
		$NumberOfFields = sizeOf($MyRow);
		for ($i = 0; $i < $NumberOfFields; $i++) {
			if ($MyRow[$i] == '') {
				$MyRow[$i] = $Defaults[$i];
			}
		}

		//check for correct number of fields
		$FieldCount = count($MyRow);
		if ($FieldCount != $FieldTarget) {
			prnMsg(_($FieldTarget . ' fields required, ' . $FieldCount . ' fields received'), 'error');
			fclose($FileHandle);
			include('includes/footer.inc');
			exit;
		}

		// cleanup the data (csv files often import with empty strings and such)
		$StockId = mb_strtoupper($MyRow[0]);
		foreach ($MyRow as &$Value) {
			$Value = trim($Value);
		}

		//first off check if the item already exists
		$SQL = "SELECT COUNT(stockid) FROM stockmaster WHERE stockid='" . $StockId . "'";
		$Result = DB_query($SQL);
		$testrow = DB_fetch_row($Result);
		if ($testrow[0] != 0) {
			$InputError = 1;
			prnMsg(_('Stock item "' . $StockId . '" already exists'), 'error');
		}

		//next validate inputs are sensible
		if (!$MyRow[1] or mb_strlen($MyRow[1]) > 50 or mb_strlen($MyRow[1]) == 0) {
			$InputError = 1;
			prnMsg(_('The stock item description must be entered and be fifty characters or less long') . '. ' . _('It cannot be a zero length string either') . ' - ' . _('a description is required') . ' ("' . implode('","', $MyRow) . $stockid . '") ', 'error');
		}
		if (mb_strlen($MyRow[2]) == 0) {
			$InputError = 1;
			prnMsg(_('The stock item description cannot be a zero length string') . ' - ' . _('a long description is required'), 'error');
		}
		if (mb_strlen($StockId) == 0) {
			$InputError = 1;
			prnMsg(_('The Stock Item code cannot be empty'), 'error');
		}
		if (ContainsIllegalCharacters($StockId) or mb_strstr($StockId, ' ')) {
			$InputError = 1;
			prnMsg(_('The stock item code cannot contain any of the following characters') . " ' & + \" \\ " . _('or a space') . " (" . $StockId . ")", 'error');
			$StockId = '';
		}
		if (mb_strlen($MyRow[4]) > 20) {
			$InputError = 1;
			prnMsg(_('The unit of measure must be 20 characters or less long'), 'error');
		}
		if (mb_strlen($MyRow[13]) > 20) {
			$InputError = 1;
			prnMsg(_('The barcode must be 20 characters or less long'), 'error');
		}
		if ($MyRow[10] != 0 and $MyRow[10] != 1) {
			$InputError = 1;
			prnMsg(_('Values in the Perishable field must be either 0 (No) or 1 (Yes)'), 'error');
		}
		if (!is_numeric($MyRow[11])) {
			$InputError = 1;
			prnMsg(_('The volume of the packaged item in cubic metres must be numeric'), 'error');
		}
		if ($MyRow[11] < 0) {
			$InputError = 1;
			prnMsg(_('The volume of the packaged item must be a positive number'), 'error');
		}
		if (!is_numeric($MyRow[12])) {
			$InputError = 1;
			prnMsg(_('The weight of the packaged item in KGs must be numeric'), 'error');
		}
		if ($MyRow[12] < 0) {
			$InputError = 1;
			prnMsg(_('The weight of the packaged item must be a positive number'), 'error');
		}
		if (!is_numeric($MyRow[6])) {
			$InputError = 1;
			prnMsg(_('The economic order quantity must be numeric'), 'error');
		}
		if ($MyRow[6] < 0) {
			$InputError = 1;
			prnMsg(_('The economic order quantity must be a positive number'), 'error');
		}
		if ($MyRow[8] == 0 and $MyRow[9] == 1) {
			$InputError = 1;
			prnMsg(_('The item can only be serialised if there is lot control enabled already') . '. ' . _('Batch control') . ' - ' . _('with any number of items in a lot/bundle/roll is enabled when controlled is enabled') . '. ' . _('Serialised control requires that only one item is in the batch') . '. ' . _('For serialised control') . ', ' . _('both controlled and serialised must be enabled'), 'error');
		}

		$mbflag = $MyRow[5];
		if ($mbflag != 'M' and $mbflag != 'K' and $mbflag != 'A' and $mbflag != 'B' and $mbflag != 'D' and $mbflag != 'G') {
			$InputError = 1;
			prnMsg(_('Items must be of MBFlag type Manufactured(M), Assembly(A), Kit-Set(K), Purchased(B), Dummy(D) or Phantom(G)'), 'error');
		}
		if (($mbflag == 'A' or $mbflag == 'K' or $mbflag == 'D' or $mbflag == 'G') and $MyRow[8] == 1) {
			$InputError = 1;
			prnMsg(_('Assembly/Kitset/Phantom/Service items cannot also be controlled items') . '. ' . _('Assemblies, Dummies and Kitsets are not physical items and batch/serial control is therefore not appropriate'), 'error');
		}
		if ($MyRow[3] == '') {
			$InputError = 1;
			prnMsg(_('There are no inventory categories defined. All inventory items must belong to a valid inventory category,'), 'error');
		}
		if ($MyRow[17] == '') {
			$InputError = 1;
			prnMsg(_('ItemPDF must contain either a filename, or the keyword `none`'), 'error');
		}

		if ($InputError != 1) {
			if ($MyRow[9] == 1) {
				/*Not appropriate to have several dp on serial items */
				$MyRow[16] = 0;
			}

			//attempt to insert the stock item
			$SQL = "INSERT INTO stockmaster (stockid,
											description,
											longdescription,
											categoryid,
											units,
											mbflag,
											eoq,
											discontinued,
											controlled,
											serialised,
											perishable,
											volume,
											grossweight,
											barcode,
											discountcategory,
											taxcatid,
											decimalplaces,
											appendfile
										) VALUES (
											'" . $StockId . "',
											'" . $MyRow[1] . "',
											'" . $MyRow[2] . "',
											'" . $MyRow[3] . "',
											'" . $MyRow[4] . "',
											'" . $MyRow[5] . "',
											" . $MyRow[6] . ",
											" . $MyRow[7] . ",
											" . $MyRow[8] . ",
											" . $MyRow[9] . ",
											" . $MyRow[10] . ",
											" . $MyRow[11] . ",
											" . $MyRow[12] . ",
											'" . $MyRow[13] . "',
											'" . $MyRow[14] . "',
											" . $MyRow[15] . ",
											" . $MyRow[16] . ",
											'" . $MyRow[17] . "'
										)";

			$ErrMsg = _('The item could not be added because');
			$DbgMsg = _('The SQL that was used to add the item failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
			$Result = DB_query("INSERT INTO stockdescriptiontranslations VALUES('" . $StockId . "','" . $_SESSION['DefaultLanguage'] . "', '" . $MyRow[1] . "', '0')", $ErrMsg, $DbgMsg);
			$Result = DB_query("INSERT INTO stocklongdescriptiontranslations VALUES('" . $StockId . "','" . $_SESSION['DefaultLanguage'] . "', '" . $MyRow[2] . "', '0')", $ErrMsg, $DbgMsg);

			$i = 0;
			foreach ($ItemDescriptionLanguagesArray as $LanguageId) {
				if ($LanguageId != '') {
					$Result = DB_query("INSERT INTO stockdescriptiontranslations VALUES('" . $StockId . "','" . $LanguageId . "', '" . $MyRow[18 + $i] . "', '0')", $ErrMsg, $DbgMsg);
					++$i;
					$Result = DB_query("INSERT INTO stocklongdescriptiontranslations VALUES('" . $StockId . "','" . $LanguageId . "', '" . $MyRow[18 + $i] . "', '0')", $ErrMsg, $DbgMsg);
					++$i;
				}
			}

			if (DB_error_no() == 0) { //the insert of the new code worked so bang in the stock location records too

				$SQL = "INSERT INTO locstock (loccode,
												stockid)
									SELECT locations.loccode,
									'" . $StockId . "'
									FROM locations";

				$ErrMsg = _('The locations for the item') . ' ' . $StockId . ' ' . _('could not be added because');
				$DbgMsg = _('NB Locations records can be added by opening the utility page') . ' <i>Z_MakeStockLocns.php</i> ' . _('The SQL that was used to add the location records that failed was');
				$InsResult = DB_query($SQL, $ErrMsg, $DbgMsg);

				if (DB_error_no() == 0) {
					prnMsg(_('New Item') . ' ' . $StockId . ' ' . _('has been added to the transaction'), 'info');
				} else { //location insert failed so set some useful error info
					$InputError = 1;
					prnMsg(_($InsResult), 'error');
				}

			} else { //item insert failed so set some useful error info
				$InputError = 1;
				prnMsg(_($InsResult), 'error');
			}

		}

		if ($InputError == 1) { //this row failed so exit loop
			break;
		}

		$row++;

	}

	if ($InputError == 1) { //exited loop with errors so rollback
		prnMsg(_('Failed on row ' . $row . '. Batch import has been rolled back.'), 'error');
		DB_Txn_Rollback();
	} else { //all good so commit data transaction
		DB_Txn_Commit();
		prnMsg(_('Batch Import of') . ' ' . $FileName . ' ' . _('has been completed. All transactions committed to the database.'), 'success');
	}

	fclose($FileHandle);

} elseif (isset($_POST['gettemplate']) or isset($_GET['gettemplate'])) { //download an import template

	echo '<br /><br /><br />"' . implode('","', $FieldHeadings) . '"<br /><br /><br />';

} else { //show file upload form

	echo '
		<br />
		<a href="Z_ImportStocks.php?gettemplate=1">Get Import Template</a>
		<br />
		<br />';
	echo '<form action="Z_ImportStocks.php" method="post" enctype="multipart/form-data">';
	echo '<div class="centre">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />' . _('Upload file') . ': <input name="userfile" type="file" />
			<input type="submit" value="' . _('Send File') . '" />
		</div>
		</form>';

}

include('includes/footer.inc');
?>