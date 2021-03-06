<?php
include ('includes/session.php');
$Title = _('Item Maintenance');
/* Manual links before header.php */
$ViewTopic = 'Inventory';
$BookMark = 'InventoryAddingItems';
include ('includes/header.php');
include ('includes/SQL_CommonFunctions.php');

/*If this form is called with the StockID then it is assumed that the stock item is to be modified */

if (isset($_GET['StockID'])) {
	$StockId = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])) {
	$StockId = trim(mb_strtoupper($_POST['StockID']));
} else {
	$StockId = '';
}

$ItemDescriptionLanguagesArray = explode(',', $_SESSION['ItemDescriptionLanguages']); //WARNING: if the last character is a ",", there are n+1 languages.
if (isset($_POST['NextItem_x'])) {
	$Result = DB_query("SELECT stockid FROM stockmaster WHERE stockid>'" . $StockId . "' ORDER BY stockid ASC LIMIT 1");
	$NextItemRow = DB_fetch_row($Result);
	$StockId = $NextItemRow[0];
	foreach ($ItemDescriptionLanguagesArray as $LanguageId) {
		unset($_POST['Description_' . str_replace('.', '_', $LanguageId) ]);
	}
}
if (isset($_POST['PreviousItem_x'])) {
	$Result = DB_query("SELECT stockid FROM stockmaster WHERE stockid<'" . $StockId . "' ORDER BY stockid DESC LIMIT 1");
	$PreviousItemRow = DB_fetch_row($Result);
	$StockId = $PreviousItemRow[0];
	foreach ($ItemDescriptionLanguagesArray as $LanguageId) {
		unset($_POST['Description_' . str_replace('.', '_', $LanguageId) ]);
	}
}

if (isset($StockId) and !isset($_POST['UpdateCategories'])) {
	$SQL = "SELECT COUNT(stockid)
			FROM stockmaster
			WHERE stockid='" . $StockId . "'
			GROUP BY stockid";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] == 0) {
		$New = 1;
	} else {
		$New = 0;
	}
}

if (isset($_POST['New'])) {
	$New = $_POST['New'];
}

echo '<div class="toplink">
		<a href="', $RootPath, '/SelectProduct.php">', _('Back to Items'), '</a>
	</div>
	<p class="page_title_text" >
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/inventory.png" title="', _('Stock'), '" alt="" />', ' ', $Title, '
	</p>';

$SupportedImgExt = array('png', 'jpg', 'jpeg');

if (isset($_FILES['ItemPicture']) and $_FILES['ItemPicture']['name'] != '') {

	$ImgExt = pathinfo($_FILES['ItemPicture']['name'], PATHINFO_EXTENSION);
	$Result = $_FILES['ItemPicture']['error'];
	$UploadTheFile = 'Yes'; //Assume all is well to start off with
	$FileName = $_SESSION['part_pics_dir'] . '/' . $StockId . '.' . $ImgExt;

	//But check for the worst
	if (!in_array($ImgExt, $SupportedImgExt)) {
		prnMsg(_('Only ' . implode(", ", $SupportedImgExt) . ' files are supported - a file extension of ' . implode(", ", $SupportedImgExt) . ' is expected'), 'warn');
		$UploadTheFile = 'No';
	} elseif ($_FILES['ItemPicture']['size'] > ($_SESSION['MaxImageSize'] * 1024)) { //File Size Check
		prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'], 'warn');
		$UploadTheFile = 'No';
	} elseif ($_FILES['ItemPicture']['type'] == 'text/plain') { //File Type Check
		prnMsg(_('Only graphics files can be uploaded'), 'warn');
		$UploadTheFile = 'No';
	} elseif ($_FILES['ItemPicture']['error'] == 6) { //upload temp directory check
		prnMsg(_('No tmp directory set. You must have a tmp directory set in your PHP for upload of files.'), 'warn');
		$UploadTheFile = 'No';
	} elseif (!is_writable($_SESSION['part_pics_dir'])) {
		prnMsg(_('The web server user does not have permission to upload files. Please speak to your system administrator'), 'warn');
		$UploadTheFile = 'No';
	}
	foreach ($SupportedImgExt as $ext) {
		$File = $_SESSION['part_pics_dir'] . '/' . $StockId . '.' . $ext;
		if (file_exists($File)) {
			$Result = unlink($File);
			if (!$Result) {
				prnMsg(_('The existing image could not be removed'), 'error');
				$UploadTheFile = 'No';
			}
		}
	}

	if ($UploadTheFile == 'Yes') {
		$Result = move_uploaded_file($_FILES['ItemPicture']['tmp_name'], $FileName);
		$Message = ($Result) ? _('File url') . '<a href="' . $FileName . '">' . $FileName . '</a>' : _('Something is wrong with uploading a file');
	}
}

if (isset($_FILES['ItemSpec']) and $_FILES['ItemSpec']['name'] != '') {

	$ImgExt = pathinfo($_FILES['ItemSpec']['name'], PATHINFO_EXTENSION);
	$Result = $_FILES['ItemSpec']['error'];
	$UploadTheFile = 'Yes'; //Assume all is well to start off with
	$FileName = $_SESSION['part_pics_dir'] . '/' . $StockId . '.' . $ImgExt;

	//But check for the worst
	if ($ImgExt != 'pdf') {
		prnMsg(_('Only pdf files are supported - a file extension of *.pdf is expected'), 'warn');
		$UploadTheFile = 'No';
	} elseif ($_FILES['ItemSpec']['size'] > ($_SESSION['MaxImageSize'] * 1024)) { //File Size Check
		prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'], 'warn');
		$UploadTheFile = 'No';
	} elseif ($_FILES['ItemSpec']['error'] == 6) { //upload temp directory check
		prnMsg(_('No tmp directory set. You must have a tmp directory set in your PHP for upload of files.'), 'warn');
		$UploadTheFile = 'No';
	} elseif (!is_writable($_SESSION['part_pics_dir'])) {
		prnMsg(_('The web server user does not have permission to upload files. Please speak to your system administrator'), 'warn');
		$UploadTheFile = 'No';
	}
	foreach ($SupportedImgExt as $ext) {
		$File = $_SESSION['part_pics_dir'] . '/' . $StockId . '.' . $ext;
		if (file_exists($File)) {
			$Result = unlink($File);
			if (!$Result) {
				prnMsg(_('The existing specification could not be removed'), 'error');
				$UploadTheFile = 'No';
			}
		}
	}

	if ($UploadTheFile == 'Yes') {
		$Result = move_uploaded_file($_FILES['ItemSpec']['tmp_name'], $FileName);
		$Message = ($Result) ? _('File url') . '<a href="' . $FileName . '">' . $FileName . '</a>' : _('Something is wrong with uploading a file');
	}
}

$InputError = 0;

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	/* actions to take once the user has clicked the submit button
	 ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i = 1;

	if (!isset($_POST['Description']) or mb_strlen($_POST['Description']) > 50 or mb_strlen($_POST['Description']) == 0) {
		$InputError = 1;
		prnMsg(_('The stock item description must be entered and be fifty characters or less long') . '. ' . _('It cannot be a zero length string either') . ' - ' . _('a description is required'), 'error');
	}
	if (mb_strlen($_POST['LongDescription']) == 0) {
		$InputError = 1;
		prnMsg(_('The stock item description cannot be a zero length string') . ' - ' . _('a long description is required'), 'error');
	}
	if (mb_strlen($StockId) == 0 and $_SESSION['AutoInvenoryNo'] == 0) {
		$InputError = 1;
		prnMsg(_('The Stock Item code cannot be empty'), 'error');
	}
	if (ContainsIllegalCharacters($StockId) or mb_strpos($StockId, ' ')) {
		$InputError = 1;
		prnMsg(_('The stock item code cannot contain any of the following characters') . " - ' &amp; + \" \\ ." . _('or a space'), 'error');
		$StockId = '';
	}
	if (mb_strlen($_POST['Units']) > 20) {
		$InputError = 1;
		prnMsg(_('The unit of measure must be 20 characters or less long'), 'error');
	}
	if (mb_strlen($_POST['BarCode']) > 20) {
		$InputError = 1;
		prnMsg(_('The barcode must be 20 characters or less long'), 'error');
	}
	if (!is_numeric(filter_number_format($_POST['Volume']))) {
		$InputError = 1;
		prnMsg(_('The volume of the packaged item in cubic metres must be numeric'), 'error');
	}
	if (filter_number_format($_POST['Volume']) < 0) {
		$InputError = 1;
		prnMsg(_('The volume of the packaged item must be a positive number'), 'error');
	}
	if (!is_numeric(filter_number_format($_POST['GrossWeight']))) {
		$InputError = 1;
		prnMsg(_('The weight of the packaged item in KGs must be numeric'), 'error');
	}
	if (filter_number_format($_POST['GrossWeight']) < 0) {
		$InputError = 1;
		prnMsg(_('The weight of the packaged item must be a positive number'), 'error');
	}
	if (!is_numeric(filter_number_format($_POST['NetWeight']))) {
		$InputError = 1;
		prnMsg(_('The net weight of the item in Net Weight must be numeric'), 'error');
	}
	if (filter_number_format($_POST['NetWeight']) < 0) {
		$InputError = 1;
		prnMsg(_('The net weight of the item must be a positive number'), 'error');
	}
	if (!is_numeric(filter_number_format($_POST['EOQ']))) {
		$InputError = 1;
		prnMsg(_('The economic order quantity must be numeric'), 'error');
	}
	if (filter_number_format($_POST['EOQ']) < 0) {
		$InputError = 1;
		prnMsg(_('The economic order quantity must be a positive number'), 'error');
	}
	if ($_POST['Controlled'] == 0 and $_POST['Serialised'] == 1) {
		$InputError = 1;
		prnMsg(_('The item can only be serialised if there is lot control enabled already') . '. ' . _('Batch control') . ' - ' . _('with any number of items in a lot/bundle/roll is enabled when controlled is enabled') . '. ' . _('Serialised control requires that only one item is in the batch') . '. ' . _('For serialised control') . ', ' . _('both controlled and serialised must be enabled'), 'error');
	}
	if ($_POST['NextSerialNo'] != 0 and $_POST['Serialised'] == 0) {
		$InputError = 1;
		prnMsg(_('The item can only have automatically generated serial numbers if it is a serialised item'), 'error');
	}
	if ($_POST['NextSerialNo'] != 0 and $_POST['MBFlag'] != 'M') {
		$InputError = 1;
		prnMsg(_('The item can only have automatically generated serial numbers if it is a manufactured item'), 'error');
	}
	if (($_POST['MBFlag'] == 'A' or $_POST['MBFlag'] == 'K' or $_POST['MBFlag'] == 'D' or $_POST['MBFlag'] == 'G') and $_POST['Controlled'] == 1) {
		$InputError = 1;
		prnMsg(_('Assembly/Kitset/Phantom/Service/Labour items cannot also be controlled items') . '. ' . _('Assemblies/Dummies/Phantom and Kitsets are not physical items and batch/serial control is therefore not appropriate'), 'error');
	}
	if (trim($_POST['CategoryID']) == '') {
		$InputError = 1;
		prnMsg(_('There are no inventory categories defined. All inventory items must belong to a valid inventory category,'), 'error');
	}
	if (!is_numeric(filter_number_format($_POST['Pansize']))) {
		$InputError = 1;
		prnMsg(_('Pansize quantity must be numeric'), 'error');
	}
	if (!is_numeric(filter_number_format($_POST['ShrinkFactor']))) {
		$InputError = 1;
		prnMsg(_('Shrinkage factor quantity must be numeric'), 'error');
	}

	if ($InputError != 1) {
		if ($_POST['Serialised'] == 1) {
			/*Not appropriate to have several dp on serial items */
			$_POST['DecimalPlaces'] = 0;
		}
		if ($New == 0) {
			/*so its an existing one */

			/*first check on the changes being made we must disallow:
			- changes from manufactured or purchased to Service, Assembly or Kitset if there is stock			- changes from manufactured, kitset or assembly where a BOM exists
			*/
			$SQL = "SELECT mbflag,
							controlled,
							serialised,
							stockcosts.materialcost+stockcosts.labourcost+stockcosts.overheadcost AS itemcost,
							stockcategory.stockact,
							stockcategory.wipact,
							description,
							longdescription
					FROM stockmaster
					INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
					LEFT JOIN stockcosts
						ON stockmaster.stockid=stockcosts.stockid
							AND succeeded=0
					WHERE stockmaster.stockid = '" . $StockId . "'";
			$MBFlagResult = DB_query($SQL);
			$MyRow = DB_fetch_row($MBFlagResult);
			$OldMBFlag = $MyRow[0];
			$OldControlled = $MyRow[1];
			$OldSerialised = $MyRow[2];
			$UnitCost = $MyRow[3];
			$OldStockAccount = $MyRow[4];
			$OldWIPAccount = $MyRow[5];
			$OldDescription = $MyRow[6];
			$OldLongDescription = $MyRow[7];

			$SQL = "SELECT SUM(locstock.quantity)
					FROM locstock
					WHERE stockid='" . $StockId . "'
					GROUP BY stockid";
			$Result = DB_query($SQL);
			$StockQtyRow = DB_fetch_row($Result);

			/*Now check the GL account of the new category to see if it is different to the old stock gl account */

			$Result = DB_query("SELECT stockact,
										wipact
								FROM stockcategory
								WHERE categoryid='" . $_POST['CategoryID'] . "'");
			$NewStockActRow = DB_fetch_array($Result);
			$NewStockAct = $NewStockActRow['stockact'];
			$NewWIPAct = $NewStockActRow['wipact'];

			if ($OldMBFlag != $_POST['MBFlag']) {
				if (($OldMBFlag == 'M' or $OldMBFlag == 'B') and ($_POST['MBFlag'] == 'A' or $_POST['MBFlag'] == 'K' or $_POST['MBFlag'] == 'D' or $_POST['MBFlag'] == 'G')) {
					/*then need to check that there is no stock holding first */
					/* stock holding OK for phantom (ghost) items */
					if ($StockQtyRow[0] != 0 and $OldMBFlag != 'G') {
						$InputError = 1;
						prnMsg(_('The make or buy flag cannot be changed from') . ' ' . $OldMBFlag . ' ' . _('to') . ' ' . $_POST['MBFlag'] . ' ' . _('where there is a quantity of stock on hand at any location') . '. ' . _('Currently there are') . ' ' . $StockQtyRow[0] . ' ' . _('on hand'), 'errror');
					}
					/* don't allow controlled/serialized  */
					if ($_POST['Controlled'] == 1) {
						$InputError = 1;
						prnMsg(_('The make or buy flag cannot be changed from') . ' ' . $OldMBFlag . ' ' . _('to') . ' ' . $_POST['MBFlag'] . ' ' . _('where the item is to be lot controlled') . '. ' . _('Kitset, phantom, dummy and assembly items cannot be lot controlled'), 'error');
					}
				}
				/*now check that if the item is being changed to a kitset, there are no items on sales orders or purchase orders*/
				if ($_POST['MBFlag'] == 'K') {
					$SQL = "SELECT quantity-qtyinvoiced
							FROM salesorderdetails
							WHERE stkcode = '" . $StockId . "'
							AND completed=0";

					$Result = DB_query($SQL);
					$ChkSalesOrds = DB_fetch_row($Result);
					if ($ChkSalesOrds[0] != 0) {
						$InputError = 1;
						prnMsg(_('The make or buy flag cannot be changed to a kitset where there is a quantity outstanding to be delivered on sales orders') . '. ' . _('Currently there are') . ' ' . $ChkSalesOrds[0] . ' ' . _('outstanding'), 'error');
					}
				}
				/*now check that if it is to be a kitset or assembly or dummy there is no quantity on purchase orders outstanding*/
				if ($_POST['MBFlag'] == 'K' or $_POST['MBFlag'] == 'A' or $_POST['MBFlag'] == 'D') {

					$SQL = "SELECT quantityord-quantityrecd
							FROM purchorderdetails INNER JOIN purchorders
							ON purchorders.orderno=purchorderdetails.orderno
							WHERE itemcode = '" . $StockId . "'
							AND purchorderdetails.completed=0
							AND purchorders.status<>'Cancelled'
							AND purchorders.status<>'Completed'
							AND purchorders.status<>'Rejected'";

					$Result = DB_query($SQL);
					$ChkPurchOrds = DB_fetch_row($Result);
					if ($ChkPurchOrds[0] != 0) {
						$InputError = 1;
						prnMsg(_('The make or buy flag cannot be changed to') . ' ' . $_POST['MBFlag'] . ' ' . _('where there is a quantity outstanding to be received on purchase orders') . '. ' . _('Currently there are') . ' ' . $ChkPurchOrds[0] . ' ' . _('yet to be received') . 'error');
					}
				}

				/*now check that if it was a Manufactured, Kitset, Phantom or Assembly and is being changed to a purchased or dummy - that no BOM exists */
				if (($OldMBFlag == 'M' or $OldMBFlag == 'K' or $OldMBFlag == 'A' or $OldMBFlag == 'G') and ($_POST['MBFlag'] == 'B' or $_POST['MBFlag'] == 'D')) {
					$SQL = "SELECT COUNT(*)
							FROM bom
							WHERE parent = '" . $StockId . "'
							GROUP BY parent";
					$Result = DB_query($SQL);
					$ChkBOM = DB_fetch_row($Result);
					if ($ChkBOM[0] != 0) {
						$InputError = 1;
						prnMsg(_('The make or buy flag cannot be changed from manufactured, kitset or assembly to') . ' ' . $_POST['MBFlag'] . ' ' . _('where there is a bill of material set up for the item') . '. ' . _('Bills of material are not appropriate for purchased or dummy items'), 'error');
					}
				}

				/*now check that if it was Manufac, Phantom or Purchased and is being changed to assembly or kitset, it is not a component on an existing BOM */
				if (($OldMBFlag == 'M' or $OldMBFlag == 'B' or $OldMBFlag == 'D' or $OldMBFlag == 'G') and ($_POST['MBFlag'] == 'A' or $_POST['MBFlag'] == 'K')) {
					$SQL = "SELECT COUNT(*)
							FROM bom
							WHERE component = '" . $StockId . "'
							GROUP BY component";
					$Result = DB_query($SQL);
					$ChkBOM = DB_fetch_row($Result);
					if ($ChkBOM[0] != 0) {
						$InputError = 1;
						prnMsg(_('The make or buy flag cannot be changed from manufactured, purchased or dummy to a kitset or assembly where the item is a component in a bill of material') . '. ' . _('Assembly and kitset items are not appropriate as components in a bill of materials'), 'error');
					}
				}
			}

			/* Do some checks for changes in the Serial & Controlled setups */
			if ($OldControlled != $_POST['Controlled'] and $StockQtyRow[0] != 0) {
				$InputError = 1;
				prnMsg(_('You can not change a Non-Controlled Item to Controlled (or back from Controlled to non-controlled when there is currently stock on hand for the item'), 'error');

			}
			if ($OldSerialised != $_POST['Serialised'] and $StockQtyRow[0] != 0) {
				$InputError = 1;
				prnMsg(_('You can not change a Serialised Item to Non-Serialised (or vice-versa) when there is a quantity on hand for the item'), 'error');
			}
			/* Do some check for property input */

			for ($i = 0;$i < $_POST['PropertyCounter'];$i++) {
				if ($_POST['PropNumeric' . $i] == 1) {
					if (filter_number_format($_POST['PropValue' . $i]) < $_POST['PropMin' . $i] or filter_number_format($_POST['PropValue' . $i]) > $_POST['PropMax' . $i]) {
						$InputError = 1;
						prnMsg(_('The property value should between') . ' ' . $_POST['PropMin' . $i] . ' ' . _('and') . $_POST['PropMax' . $i], 'error');
					}
				}
			}

			if ($InputError == 0) {

				DB_Txn_Begin();

				$SQL = "UPDATE stockmaster
						SET longdescription='" . $_POST['LongDescription'] . "',
							description='" . $_POST['Description'] . "',
							discontinued='" . $_POST['Discontinued'] . "',
							controlled='" . $_POST['Controlled'] . "',
							serialised='" . $_POST['Serialised'] . "',
							perishable='" . $_POST['Perishable'] . "',
							categoryid='" . $_POST['CategoryID'] . "',
							units='" . $_POST['Units'] . "',
							mbflag='" . $_POST['MBFlag'] . "',
							eoq='" . filter_number_format($_POST['EOQ']) . "',
							volume='" . filter_number_format($_POST['Volume']) . "',
							grossweight='" . filter_number_format($_POST['GrossWeight']) . "',
							netweight='" . filter_number_format($_POST['NetWeight']) . "',
							barcode='" . $_POST['BarCode'] . "',
							drawingnumber='" . $_POST['DrawingNumber'] . "',
							discountcategory='" . $_POST['DiscountCategory'] . "',
							taxcatid='" . $_POST['TaxCat'] . "',
							decimalplaces='" . $_POST['DecimalPlaces'] . "',
							shrinkfactor='" . filter_number_format($_POST['ShrinkFactor']) . "',
							pansize='" . filter_number_format($_POST['Pansize']) . "',
							nextserialno='" . $_POST['NextSerialNo'] . "'
					WHERE stockid='" . $StockId . "'";

				$ErrMsg = _('The stock item could not be updated because');
				$DbgMsg = _('The SQL that was used to update the stock item and failed was');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

				if (in_array($_SESSION['PageSecurityArray']['StockCostUpdate.php'], $_SESSION['AllowedPageSecurityTokens'])) {
					/*We need to update the costs for the item */
					$NewCost = $_POST['MaterialCost'] + $_POST['LabourCost'] + $_POST['OverheadCost'];
					ItemCostUpdateGL($StockId, $NewCost);

					$ErrMsg = _('The old cost details for the stock item could not be updated because');
					$DbgMsg = _('The SQL that failed was');
					$SQL = "UPDATE stockcosts SET succeeded=1 WHERE stockid='" . $StockId . "'";
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

					$SQL = "INSERT INTO stockcosts VALUES('" . $StockId . "',
														'" . filter_number_format($_POST['MaterialCost']) . "',
														'" . filter_number_format($_POST['LabourCost']) . "',
														'" . filter_number_format($_POST['OverheadCost']) . "',
														CURRENT_TIMESTAMP,
														0)";
					$ErrMsg = _('The new cost details for the stock item could not be inserted because');
					$DbgMsg = _('The SQL that failed was');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

					$SQL = "UPDATE stockmaster SET lastcostupdate=CURRENT_DATE WHERE stockid='" . $StockId . "'";
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

					UpdateCost($StockId); //Update any affected BOMs
					/* End of cost updates */
				}

				$ErrMsg = _('Could not update the language description because');
				$DbgMsg = _('The SQL that was used to update the language description and failed was');

				if (count($ItemDescriptionLanguagesArray) > 0) {
					foreach ($ItemDescriptionLanguagesArray as $LanguageId) {
						if ($LanguageId != '') {
							$Result = DB_query("DELETE FROM stockdescriptiontranslations WHERE stockid='" . $StockId . "' AND language_id='" . $LanguageId . "'", $ErrMsg, $DbgMsg, true);
							$Result = DB_query("INSERT INTO stockdescriptiontranslations VALUES('" . $StockId . "','" . $LanguageId . "', '" . $_POST['Description_' . str_replace('.', '_', $LanguageId) ] . "', '0')", $ErrMsg, $DbgMsg, true);
							$Result = DB_query("DELETE FROM stocklongdescriptiontranslations WHERE stockid='" . $StockId . "' AND language_id='" . $LanguageId . "'", $ErrMsg, $DbgMsg, true);
							$Result = DB_query("INSERT INTO stocklongdescriptiontranslations VALUES('" . $StockId . "','" . $LanguageId . "', '" . $_POST['LongDescription_' . str_replace('.', '_', $LanguageId) ] . "', '0')", $ErrMsg, $DbgMsg, true);
						}
					}
					/*
					foreach ($ItemDescriptionLanguagesArray as $LanguageId) {
						$DescriptionTranslation = $_POST['Description_' . str_replace('.', '_', $LanguageId)];
							//WARNING: It DOES NOT update if database row DOES NOT exist.
							$SQL = "UPDATE stockdescriptiontranslations " .
									"SET descriptiontranslation='" . $DescriptionTranslation . "' " .
									"WHERE stockid='" . $StockId . "' AND (language_id='" . $LanguageId. "')";
							$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
					}
					*/

				}

				/* Activate the needs revision flag for translations for modified descriptions */
				if ($OldDescription != $_POST['Description'] or $OldLongDescription != $_POST['LongDescription']) {
					$SQL = "UPDATE stockdescriptiontranslations
						SET needsrevision = '0'
						WHERE stockid='" . $StockId . "'";
					$ErrMsg = _('The stock description translations could not be updated because');
					$DbgMsg = _('The SQL that was used to set the flag for translation revision failed was');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

					$SQL = "UPDATE stocklongdescriptiontranslations
						SET needsrevision = '0'
						WHERE stockid='" . $StockId . "'";
					$ErrMsg = _('The stock description translations could not be updated because');
					$DbgMsg = _('The SQL that was used to set the flag for translation revision failed was');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

					$SQL = "UPDATE stockdescriptiontranslations SET descriptiontranslation='" . $_POST['Description'] . "'
									WHERE stockid='" . $StockId . "'
										AND language_id='" . $_SESSION['DefaultLanguage'] . "'";
					$ErrMsg = _('The stock description translations could not be updated because');
					$DbgMsg = _('The SQL that was used to set the flag for translation revision failed was');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

					$SQL = "UPDATE stocklongdescriptiontranslations SET longdescriptiontranslation='" . $_POST['LongDescription'] . "'
									WHERE stockid='" . $StockId . "'
										AND language_id='" . $_SESSION['DefaultLanguage'] . "'";
					$ErrMsg = _('The stock description translations could not be updated because');
					$DbgMsg = _('The SQL that was used to set the flag for translation revision failed was');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

				}

				//delete any properties for the item no longer relevant with the change of category
				$Result = DB_query("DELETE FROM stockitemproperties
									WHERE stockid ='" . $StockId . "'", $ErrMsg, $DbgMsg, true);

				//now insert any item properties
				for ($i = 0;$i < $_POST['PropertyCounter'];$i++) {

					if ($_POST['PropType' . $i] == 2) {
						if ($_POST['PropValue' . $i] == 'on') {
							$_POST['PropValue' . $i] = 1;
						} else {
							$_POST['PropValue' . $i] = 0;
						}
					}
					if ($_POST['PropNumeric' . $i] == 1) {
						$_POST['PropValue' . $i] = filter_number_format($_POST['PropValue' . $i]);
					} else {
						$_POST['PropValue' . $i] = $_POST['PropValue' . $i];
					}
					$Result = DB_query("INSERT INTO stockitemproperties (stockid,
																		stkcatpropid,
																		value)
														VALUES ('" . $StockId . "',
																'" . $_POST['PropID' . $i] . "',
																'" . $_POST['PropValue' . $i] . "')", $ErrMsg, $DbgMsg, true);
				} //end of loop around properties defined for the category
				if ($OldStockAccount != $NewStockAct and $_SESSION['CompanyRecord']['gllink_stock'] == 1) {
					/*Then we need to make a journal to transfer the cost to the new stock account */
					$JournalNo = GetNextTransNo(0); //enter as a journal
					$SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount)
										VALUES ( 0,
												'" . $JournalNo . "',
												CURRENT_DATE,
												'" . GetPeriod(Date($_SESSION['DefaultDateFormat']), true) . "',
												'" . $NewStockAct . "',
												'" . $StockId . ' ' . _('Change stock category') . "',
												'" . ($UnitCost * $StockQtyRow[0]) . "')";
					$ErrMsg = _('The stock cost journal could not be inserted because');
					$DbgMsg = _('The SQL that was used to create the stock cost journal and failed was');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
					$SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount)
										VALUES ( 0,
												'" . $JournalNo . "',
												CURRENT_DATE,
												'" . GetPeriod(Date($_SESSION['DefaultDateFormat']), true) . "',
												'" . $OldStockAccount . "',
												'" . $StockId . ' ' . _('Change stock category') . "',
												'" . (-$UnitCost * $StockQtyRow[0]) . "')";
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

				}
				/* end if the stock category changed and forced a change in stock cost account */
				if ($OldWIPAccount != $NewWIPAct and $_SESSION['CompanyRecord']['gllink_stock'] == 1) {
					/*Then we need to make a journal to transfer the cost  of WIP to the new WIP account */
					/*First get the total cost of WIP for this category */

					$WOCostsResult = DB_query("SELECT workorders.costissued,
													SUM(woitems.qtyreqd * woitems.stdcost) AS costrecd
												FROM woitems INNER JOIN workorders
												ON woitems.wo = workorders.wo
												INNER JOIN stockmaster
												ON woitems.stockid=stockmaster.stockid
												WHERE stockmaster.stockid='" . $StockId . "'
												AND workorders.closed=0
												GROUP BY workorders.costissued", _('Error retrieving value of finished goods received and cost issued against work orders for this item'));
					$WIPValue = 0;
					while ($WIPRow = DB_fetch_array($WOCostsResult)) {
						$WIPValue+= ($WIPRow['costissued'] - $WIPRow['costrecd']);
					}
					if ($WIPValue != 0) {
						$JournalNo = GetNextTransNo(0); //enter as a journal
						$SQL = "INSERT INTO gltrans (type,
													typeno,
													trandate,
													periodno,
													account,
													narrative,
													amount)
											VALUES ( 0,
													'" . $JournalNo . "',
													CURRENT_DATE,
													'" . GetPeriod(Date($_SESSION['DefaultDateFormat']), true) . "',
													'" . $NewWIPAct . "',
													'" . $StockId . ' ' . _('Change stock category') . "',
													'" . $WIPValue . "')";
						$ErrMsg = _('The WIP cost journal could not be inserted because');
						$DbgMsg = _('The SQL that was used to create the WIP cost journal and failed was');
						$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
						$SQL = "INSERT INTO gltrans (type,
													typeno,
													trandate,
													periodno,
													account,
													narrative,
													amount)
											VALUES ( 0,
													'" . $JournalNo . "',
													CURRENT_DATE,
													'" . GetPeriod(Date($_SESSION['DefaultDateFormat']), true) . "',
													'" . $OldWIPAccount . "',
													'" . $StockId . ' ' . _('Change stock category') . "',
													'" . (-$WIPValue) . "')";
						$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
					}
				}
				/* end if the stock category changed and forced a change in WIP account */
				DB_Txn_Commit();
				prnMsg(_('Stock Item') . ' ' . $StockId . ' ' . _('has been updated'), 'success');
			}
			if (DB_error_no() != 0) {
				$Result = DB_Txn_Rollback();
			}

		} else { //it is a NEW part
			//but lets be really sure here
			$Result = DB_query("SELECT stockid
								FROM stockmaster
								WHERE stockid='" . $StockId . "'");
			if (DB_num_rows($Result) == 1) {
				prnMsg(_('The stock code entered is already in the database - duplicate stock codes are prohibited by the system. Try choosing an alternative stock code'), 'error');
				$InputError = 1;
			} else {
				DB_Txn_Begin();
				if ($_SESSION['AutoInvenoryNo'] == 1) {
					$StockId = GetNextTransNo(700);
				}
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
												netweight,
												barcode,
												drawingnumber,
												discountcategory,
												taxcatid,
												decimalplaces,
												shrinkfactor,
												pansize)
							VALUES ('" . $StockId . "',
								'" . $_POST['Description'] . "',
								'" . $_POST['LongDescription'] . "',
								'" . $_POST['CategoryID'] . "',
								'" . $_POST['Units'] . "',
								'" . $_POST['MBFlag'] . "',
								'" . filter_number_format($_POST['EOQ']) . "',
								'" . $_POST['Discontinued'] . "',
								'" . $_POST['Controlled'] . "',
								'" . $_POST['Serialised'] . "',
								'" . $_POST['Perishable'] . "',
								'" . filter_number_format($_POST['Volume']) . "',
								'" . filter_number_format($_POST['GrossWeight']) . "',
								'" . filter_number_format($_POST['NetWeight']) . "',
								'" . $_POST['BarCode'] . "',
								'" . $_POST['DrawingNumber'] . "',
								'" . $_POST['DiscountCategory'] . "',
								'" . $_POST['TaxCat'] . "',
								'" . $_POST['DecimalPlaces'] . "',
								'" . filter_number_format($_POST['ShrinkFactor']) . "',
								'" . filter_number_format($_POST['Pansize']) . "')";

				$ErrMsg = _('The item could not be added because');
				$DbgMsg = _('The SQL that was used to add the item failed was');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

				if (in_array($_SESSION['PageSecurityArray']['StockCostUpdate.php'], $_SESSION['AllowedPageSecurityTokens'])) {

					$SQL = "INSERT INTO stockcosts VALUES('" . $StockId . "',
														'" . filter_number_format($_POST['MaterialCost']) . "',
														'" . filter_number_format($_POST['LabourCost']) . "',
														'" . filter_number_format($_POST['OverheadCost']) . "',
														CURRENT_TIMESTAMP,
														0)";
					$ErrMsg = _('The new cost details for the stock item could not be inserted because');
					$DbgMsg = _('The SQL that failed was');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

					/* End of cost updates */
				} else {

					$SQL = "INSERT INTO stockcosts VALUES('" . $StockId . "',
														0,
														0,
														0,
														CURRENT_TIMESTAMP,
														0)";
					$ErrMsg = _('The new cost details for the stock item could not be inserted because');
					$DbgMsg = _('The SQL that failed was');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

					/* End of cost updates */
				}
				if (DB_error_no() == 0) {
					//now insert the language descriptions
					$ErrMsg = _('Could not update the language description because');
					$DbgMsg = _('The SQL that was used to update the language description and failed was');
					if (count($ItemDescriptionLanguagesArray) > 0) {
						foreach ($ItemDescriptionLanguagesArray as $LanguageId) {
							if ($LanguageId != '' and $_POST['Description_' . str_replace('.', '_', $LanguageId) ] != '') {
								$SQL = "INSERT INTO stockdescriptiontranslations VALUES('" . $StockId . "','" . $LanguageId . "', '" . $_POST['Description_' . str_replace('.', '_', $LanguageId) ] . "', 0)";
								$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
								$SQL = "INSERT INTO stocklongdescriptiontranslations VALUES('" . $StockId . "','" . $LanguageId . "', '" . $_POST['LongDescription_' . str_replace('.', '_', $LanguageId) ] . "', 0)";
								$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
							}
						}
					}

					/* Insert the default language descriptions into the translations table */
					$SQL = "INSERT INTO stockdescriptiontranslations VALUES('" . $StockId . "','" . $_SESSION['DefaultLanguage'] . "', '" . $_POST['Description'] . "', 0)";
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
					$SQL = "INSERT INTO stocklongdescriptiontranslations VALUES('" . $StockId . "','" . $_SESSION['DefaultLanguage'] . "', '" . $_POST['LongDescription'] . "', 0)";
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
					/* End default language descriptions */

					//now insert any item properties
					for ($i = 0;$i < $_POST['PropertyCounter'];$i++) {

						if ($_POST['PropType' . $i] == 2) {
							if ($_POST['PropValue' . $i] == 'on') {
								$_POST['PropValue' . $i] = 1;
							} else {
								$_POST['PropValue' . $i] = 0;
							}
						}

						if ($_POST['PropNumeric' . $i] == 1) {
							$_POST['PropValue' . $i] = filter_number_format($_POST['PropValue' . $i]);
						} else {
							$_POST['PropValue' . $i] = $_POST['PropValue' . $i];
						}

						$Result = DB_query("INSERT INTO stockitemproperties (stockid,
													stkcatpropid,
													value)
													VALUES ('" . $StockId . "',
														'" . $_POST['PropID' . $i] . "',
														'" . $_POST['PropValue' . $i] . "')", $ErrMsg, $DbgMsg, true);
					} //end of loop around properties defined for the category
					//Add data to locstock
					$SQL = "INSERT INTO locstock (loccode,
													stockid)
										SELECT locations.loccode,
										'" . $StockId . "'
										FROM locations";

					$ErrMsg = _('The locations for the item') . ' ' . $StockId . ' ' . _('could not be added because');
					$DbgMsg = _('NB Locations records can be added by opening the utility page') . ' <i>Z_MakeStockLocns.php</i> ' . _('The SQL that was used to add the location records that failed was');
					$InsResult = DB_query($SQL, $ErrMsg, $DbgMsg, true);
					DB_Txn_Commit();

					if (DB_error_no() == 0) {
						prnMsg(_('New Item') . ' ' . '
								<a href="SelectProduct.php?StockID=' . urlencode($StockId) . '">' . $StockId . '</a> ' . _('has been added to the database') . '<br />' . _('NB: The item cost and pricing must also be setup') . '<br />' . '<a target="_blank" href="StockCostUpdate.php?StockID=' . $StockId . '">' . _('Enter Item Cost') . '</a>
							<br />' . '<a target="_blank" href="Prices.php?Item=' . urlencode($StockId) . '">' . _('Enter Item Prices') . '</a> ', 'success');
						echo '<br />';
						unset($_POST['Description']);
						unset($_POST['LongDescription']);
						unset($_POST['EOQ']);
						// Leave Category ID set for ease of batch entry
						//						unset($_POST['CategoryID']);
						unset($_POST['Units']);
						unset($_POST['MBFlag']);
						unset($_POST['Discontinued']);
						unset($_POST['Controlled']);
						unset($_POST['Serialised']);
						unset($_POST['Perishable']);
						unset($_POST['Volume']);
						unset($_POST['GrossWeight']);
						unset($_POST['NetWeight']);
						unset($_POST['BarCode']);
						unset($_POST['DrawingNumber']);
						unset($_POST['ReorderLevel']);
						unset($_POST['DiscountCategory']);
						unset($_POST['DecimalPlaces']);
						unset($_POST['ShrinkFactor']);
						unset($_POST['Pansize']);
						unset($StockId);
						foreach ($ItemDescriptionLanguagesArray as $LanguageId) {
							unset($_POST['Description_' . str_replace('.', '_', $LanguageId) ]);
						}
						foreach ($ItemDescriptionLanguagesArray as $LanguageId) {
							unset($_POST['LongDescription_' . str_replace('.', '_', $LanguageId) ]);
						}
						$New = 1;
					} else {
						$InsResult = DB_Txn_Rollback();
					}
				} //THE INSERT OF THE NEW CODE WORKED SO BANG IN THE STOCK LOCATION RECORDS TOO
				
			} //END CHECK FOR ALREADY EXISTING ITEM OF THE SAME CODE
			
		}

	} else {
		echo '<br />' . "\n";
		prnMsg(_('Validation failed, no updates or deletes took place'), 'error');
	}

} else if (isset($_POST['delete']) and mb_strlen($_POST['delete']) > 1) {
	//the button to delete a selected record was clicked instead of the submit button
	$CancelDelete = 0;

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'StockMoves'
	$SQL = "SELECT COUNT(*) FROM stockmoves WHERE stockid='" . $StockId . "' GROUP BY stockid";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this stock item because there are stock movements that refer to this item'), 'warn');
		echo '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('stock movements that refer to this item');

	} else {
		$SQL = "SELECT COUNT(*) FROM bom WHERE component='" . $StockId . "' GROUP BY component";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0] > 0) {
			$CancelDelete = 1;
			prnMsg(_('Cannot delete this item record because there are bills of material that require this part as a component'), 'warn');
			echo '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('bills of material that require this part as a component');
		} else {
			$SQL = "SELECT COUNT(*) FROM salesorderdetails WHERE stkcode='" . $StockId . "' GROUP BY stkcode";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_row($Result);
			if ($MyRow[0] > 0) {
				$CancelDelete = 1;
				prnMsg(_('Cannot delete this item record because there are existing sales orders for this part'), 'warn');
				echo '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('sales order items against this part');
			} else {
				$SQL = "SELECT COUNT(*) FROM salesanalysis WHERE stockid='" . $StockId . "' GROUP BY stockid";
				$Result = DB_query($SQL);
				$MyRow = DB_fetch_row($Result);
				if ($MyRow[0] > 0) {
					$CancelDelete = 1;
					prnMsg(_('Cannot delete this item because sales analysis records exist for it'), 'warn');
					echo '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('sales analysis records against this part');
				} else {
					$SQL = "SELECT COUNT(*) FROM purchorderdetails WHERE itemcode='" . $StockId . "' GROUP BY itemcode";
					$Result = DB_query($SQL);
					$MyRow = DB_fetch_row($Result);
					if ($MyRow[0] > 0) {
						$CancelDelete = 1;
						prnMsg(_('Cannot delete this item because there are existing purchase order items for it'), 'warn');
						echo '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('purchase order item record relating to this part');
					} else {
						$SQL = "SELECT SUM(quantity) AS qoh FROM locstock WHERE stockid='" . $StockId . "' GROUP BY stockid";
						$Result = DB_query($SQL);
						$MyRow = DB_fetch_row($Result);
						if ($MyRow[0] != 0) {
							$CancelDelete = 1;
							prnMsg(_('Cannot delete this item because there is currently some stock on hand'), 'warn');
							echo '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('on hand for this part');
						} else {
							$SQL = "SELECT COUNT(*) FROM offers WHERE stockid='" . $StockId . "' GROUP BY stockid";
							$Result = DB_query($SQL);
							$MyRow = DB_fetch_row($Result);
							if ($MyRow[0] != 0) {
								$CancelDelete = 1;
								prnMsg(_('Cannot delete this item because there are offers for this item'), 'warn');
								echo '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('offers from suppliers for this part');
							} else {
								$SQL = "SELECT COUNT(*) FROM tenderitems WHERE stockid='" . $StockId . "' GROUP BY stockid";
								$Result = DB_query($SQL);
								$MyRow = DB_fetch_row($Result);
								if ($MyRow[0] != 0) {
									$CancelDelete = 1;
									prnMsg(_('Cannot delete this item because there are tenders for this item'), 'warn');
									echo '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('tenders from suppliers for this part');
								}
							}
						}
					}
				}
			}
		}

	}
	if ($CancelDelete == 0) {
		$Result = DB_Txn_Begin();

		/*Deletes LocStock records*/
		$SQL = "DELETE FROM locstock WHERE stockid='" . $StockId . "'";
		$Result = DB_query($SQL, _('Could not delete the location stock records because'), '', true);
		/*Deletes Price records*/
		$SQL = "DELETE FROM prices WHERE stockid='" . $StockId . "'";
		$Result = DB_query($SQL, _('Could not delete the prices for this stock record because'), '', true);
		/*and cascade deletes in PurchData */
		$SQL = "DELETE FROM purchdata WHERE stockid='" . $StockId . "'";
		$Result = DB_query($SQL, _('Could not delete the purchasing data because'), '', true);
		/*and cascade delete the bill of material if any */
		$SQL = "DELETE FROM bom WHERE parent='" . $StockId . "'";
		$Result = DB_query($SQL, _('Could not delete the bill of material because'), '', true);
		//and cascade delete the item properties
		$SQL = "DELETE FROM stockitemproperties WHERE stockid='" . $StockId . "'";
		$Result = DB_query($SQL, _('Could not delete the item properties'), '', true);
		//and cascade delete the item descriptions in other languages
		$SQL = "DELETE FROM stockdescriptiontranslations WHERE stockid='" . $StockId . "'";
		$Result = DB_query($SQL, _('Could not delete the item language descriptions'), '', true);
		$SQL = "DELETE FROM stocklongdescriptiontranslations WHERE stockid='" . $StockId . "'";
		$Result = DB_query($SQL, _('Could not delete the item language descriptions'), '', true);
		//and finally remove the stockmaster
		$SQL = "DELETE FROM stockmaster WHERE stockid='" . $StockId . "'";
		$Result = DB_query($SQL, _('Could not delete the item record'), '', true);

		$Result = DB_Txn_Commit();

		prnMsg(_('Deleted the stock master record for') . ' ' . $StockId . '....' . '<br />. . ' . _('and all the location stock records set up for the part') . '<br />. . .' . _('and any bill of material that may have been set up for the part') . '<br /> . . . .' . _('and any purchasing data that may have been set up for the part') . '<br /> . . . . .' . _('and any prices that may have been set up for the part'), 'success');
		echo '<br />';
		unset($_POST['LongDescription']);
		unset($_POST['Description']);
		unset($_POST['EOQ']);
		unset($_POST['CategoryID']);
		unset($_POST['Units']);
		unset($_POST['MBFlag']);
		unset($_POST['Discontinued']);
		unset($_POST['Controlled']);
		unset($_POST['Serialised']);
		unset($_POST['Perishable']);
		unset($_POST['Volume']);
		unset($_POST['GrossWeight']);
		unset($_POST['NetWeight']);
		unset($_POST['BarCode']);
		unset($_POST['ReorderLevel']);
		unset($_POST['DiscountCategory']);
		unset($_POST['TaxCat']);
		unset($_POST['DecimalPlaces']);
		unset($_SESSION['SelectedStockItem']);
		foreach ($ItemDescriptionLanguagesArray as $LanguageId) {
			unset($_POST['Description_' . str_replace('.', '_', $LanguageId) ]);
		}
		unset($StockId);

		$New = 1;
	} //end if Delete Part
	
}

echo '<form id="ItemForm" enctype="multipart/form-data" method="post" action="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($StockId) and $StockId != '') {
	echo '<table width="100%">
			<tr>
				<td><input class="image" src="css/' . $_SESSION['Theme'] . '/images/previous.png" type="image" name="PreviousItem" title="' . _('Previous Item') . '" value="" /></td>
				<td><label>' . _('Navigate Items') . '</label></td>
				<td><input class="image" src="css/' . $_SESSION['Theme'] . '/images/next.png" type="image" name="NextItem" title="' . _('Next Item') . '" value="" /></td>
			</tr>
		</table>';
}

echo '<input type="hidden" name="New" value="' . $New . '" />';
echo '<fieldset>';

if (isset($_GET['Clone'])) {
	$StockId = $_GET['Clone'];
}

if (!isset($StockId) or $StockId == '' or isset($_POST['UpdateCategories'])) {

	echo '<legend>', _('New Stock Item Details'), '</legend>';

	/*If the page was called without $StockId passed to page then assume a new stock item is to be entered show a form with a part Code field other wise the form showing the fields with the existing entries against the part will show for editing with only a hidden StockID field. New is set to flag that the page may have called itself and still be entering a new part, in which case the page needs to know not to go looking up details for an existing part*/
	if (!isset($StockId)) {
		$StockId = '';
	}
	if ($New == 1) {
		if ($_SESSION['AutoInvenoryNo'] == 0) {
			echo '<field>
					<label for="StockID">', _('Item Code'), ':</label>
					<input type="text" value="', $StockId, '" name="StockID" size="21" required="required" maxlength="20" />
				</field>';
		} else {
			echo '<field>
					<label for="StockID">', _('Item Code'), ':</label>
					<div class="fieldtext">N/A</div>
				</field>';
		}
	} else {
		echo '<field>
				<label for="StockID">', _('Item Code'), ':</label>
				<div class="fieldtext">', $StockId, '<input type="hidden" name ="StockID" value="', $StockId, '" /></div>
			</field>';
	}

} elseif ((!isset($_POST['UpdateCategories']) and $InputError != 1) or isset($_GET['Clone'])) {
	// Must be modifying or cloning an existing item and no changes made yet
	$SQL = "SELECT stockmaster.stockid,
					description,
					longdescription,
					categoryid,
					units,
					mbflag,
					discontinued,
					controlled,
					serialised,
					perishable,
					eoq,
					volume,
					grossweight,
					netweight,
					barcode,
					drawingnumber,
					discountcategory,
					taxcatid,
					decimalplaces,
					nextserialno,
					pansize,
					shrinkfactor,
					stockcosts.materialcost,
					stockcosts.labourcost,
					stockcosts.overheadcost
			FROM stockmaster
			LEFT JOIN stockcosts
				ON stockmaster.stockid = stockcosts.stockid
				AND stockcosts.succeeded=0
			WHERE stockmaster.stockid = '" . $StockId . "'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['LongDescription'] = $MyRow['longdescription'];
	$_POST['Description'] = $MyRow['description'];
	$_POST['EOQ'] = $MyRow['eoq'];
	$_POST['CategoryID'] = $MyRow['categoryid'];
	$_POST['Units'] = $MyRow['units'];
	$_POST['MBFlag'] = $MyRow['mbflag'];
	$_POST['Discontinued'] = $MyRow['discontinued'];
	$_POST['Controlled'] = $MyRow['controlled'];
	$_POST['Serialised'] = $MyRow['serialised'];
	$_POST['Perishable'] = $MyRow['perishable'];
	$_POST['Volume'] = $MyRow['volume'];
	$_POST['GrossWeight'] = $MyRow['grossweight'];
	$_POST['NetWeight'] = $MyRow['netweight'];
	$_POST['BarCode'] = $MyRow['barcode'];
	$_POST['DrawingNumber'] = $MyRow['drawingnumber'];
	$_POST['DiscountCategory'] = $MyRow['discountcategory'];
	$_POST['TaxCat'] = $MyRow['taxcatid'];
	$_POST['DecimalPlaces'] = $MyRow['decimalplaces'];
	$_POST['NextSerialNo'] = $MyRow['nextserialno'];
	$_POST['Pansize'] = $MyRow['pansize'];
	$_POST['ShrinkFactor'] = $MyRow['shrinkfactor'];
	$_POST['MaterialCost'] = $MyRow['materialcost'];
	$_POST['LabourCost'] = $MyRow['labourcost'];
	$_POST['OverheadCost'] = $MyRow['overheadcost'];
	$SQL = "SELECT descriptiontranslation, language_id FROM stockdescriptiontranslations WHERE stockid='" . $StockId . "' AND (";

	foreach ($ItemDescriptionLanguagesArray as $LanguageId) {
		$SQL.= "language_id='" . $LanguageId . "' OR ";
	}
	$SQL = mb_substr($SQL, 0, mb_strlen($SQL) - 3) . ')';
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		$_POST['Description_' . str_replace('.', '_', $MyRow['language_id']) ] = $MyRow['descriptiontranslation'];
	}
	$SQL = "SELECT longdescriptiontranslation, language_id FROM stocklongdescriptiontranslations WHERE stockid='" . $StockId . "' AND (";
	foreach ($ItemDescriptionLanguagesArray as $LanguageId) {
		$SQL.= "language_id='" . $LanguageId . "' OR ";
	}
	$SQL = mb_substr($SQL, 0, mb_strlen($SQL) - 3) . ')';
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		$_POST['LongDescription_' . str_replace('.', '_', $MyRow['language_id']) ] = $MyRow['longdescriptiontranslation'];
	}
	if (isset($_GET['Clone'])) {
		echo '<legend>', _('Clone Stock Item Details'), '</legend>';
		if ($_SESSION['AutoInvenoryNo'] == 0) {
			echo '<field>
					<label for="StockID">', _('Item Code'), ':</label>
					<input type="text" value="" name="StockID" size="21" required="required" maxlength="20" />
				</field>';
		} else {
			echo '<field>
					<label for="StockID">', _('Item Code'), ':</label>
					<div class="fieldtext">N/A</div>
				</field>';
		}
	} else {
		echo '<legend>', _('Amend Stock Item Details'), '</legend>';
		echo '<field>
				<label for="StockID">', _('Item Code'), ':</label>
				<div class="fieldtext">', $StockId, '<input type="hidden" name="StockID" value="', $StockId, '" /></div>
			</field>';
	}

} else { // some changes were made to the data so don't re-set form variables to DB ie the code above
	echo '<field>
			<label for="StockID">', _('Item Code'), ':</label>
			<div class="fieldtext">', $StockId, '<input type="hidden" name="StockID" value="', $StockId, '" /></div>
		</field>';
}

if (isset($_POST['Description'])) {
	$Description = $_POST['Description'];
} else {
	$Description = '';
}
echo '<field>
		<label for="Description">', _('Part Description'), ' (', _('short'), '):</label>
		<input type="text" name="Description" size="52" required="required" maxlength="50" value="', stripslashes($Description), '" />
	</field>';

if (isset($_POST['LongDescription'])) {
	$LongDescription = AddCarriageReturns($_POST['LongDescription']);
} else {
	$LongDescription = '';
}
echo '<field>
		<label for="LongDescription">', _('Part Description'), ' (', _('long'), '):</label>
		<textarea required="required" name="LongDescription" cols="40" rows="3">', stripslashes($LongDescription), '</textarea>
	</field>';

foreach ($ItemDescriptionLanguagesArray as $LanguageId) {
	if ($LanguageId != '') {
		//unfortunately cannot have points in POST variables so have to mess with the language id
		$PostVariableName = 'Description_' . str_replace('.', '_', $LanguageId);
		if (!isset($_POST[$PostVariableName])) {
			$_POST[$PostVariableName] = '';
		}
		echo '<field>
				<label for="', $PostVariableName, '">' . $LanguagesArray[$LanguageId]['LanguageName'], ' ', _('Description'), ':</label>
				<input type="text" name="', $PostVariableName, '" size="52" maxlength="50" value="', $_POST[$PostVariableName], '" />
			</field>';
		if (!isset($_POST['Long' . $PostVariableName])) {
			$_POST['Long' . $PostVariableName] = '';
		}
		echo '<field>
				<label for="Long', $PostVariableName, '">', $LanguagesArray[$LanguageId]['LanguageName'], ' ', _('Long Description'), ':</label>
				<textarea name="Long' . $PostVariableName . '" cols="40" rows="3">' . $_POST['Long' . $PostVariableName] . '</textarea>
			</field>';
	}
}

$ImageFileArray = glob($_SESSION['part_pics_dir'] . '/' . $StockId . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE);
$ImageFile = reset($ImageFileArray);
if (extension_loaded('gd') and function_exists('gd_info') and isset($StockId) and $StockId != '') {
	$StockImgLink = '<img class="StockImage" src="GetStockImage.php?automake=1&amp;textcolor=FFFFFF&amp;bgcolor=CCCCCC' . '&amp;StockID=' . urlencode($StockId) . '&amp;text=' . '" alt="" />';
} else if (file_exists($ImageFile) and $StockId != '') {
	$StockImgLink = '<img class="StockImage" src="' . $ImageFile . '" />';
} else {
	$StockImgLink = _('No Image');
}
echo '<field>
		<label for="ItemPicture">', _('Image File (' . implode(", ", $SupportedImgExt) . ')'), ':</label>
		<div class="fieldtext">', $StockImgLink, '</div>
		<input type="file" id="ItemPicture" name="ItemPicture" />
		<input type="checkbox" name="ClearImage" id="ClearImage" value="1" > ', _('Clear Image');

if (isset($_POST['ClearImage'])) {
	foreach ($SupportedImgExt as $ext) {
		$File = $_SESSION['part_pics_dir'] . '/' . $StockId . '.' . $ext;
		if (file_exists($File)) {
			//workaround for many variations of permission issues that could cause unlink fail
			@unlink($File);
			if (is_file($ImageFile)) {
				prnMsg(_('You do not have access to delete this item image file.'), 'error');
			} else {
				$StockImgLink = _('No Image');
			}
		}
	}
}
echo '</field>';

if (file_exists($_SESSION['part_pics_dir'] . '/' . $StockId . '.pdf')) {
	$FileName = ':' . $StockId . '.pdf';
} else {
	$FileName = '';
}
echo '<field>
		<label for="ItemSpec">', _('Specification File (.pdf format)'), ':</label>
		<input type="file" id="ItemSpec" name="ItemSpec" />', $FileName, '
		<input type="checkbox" name="ClearImage" id="ClearSpec" value="1" > ', _('Clear Specification');
if (isset($_POST['ClearSpec'])) {
	$File = $_SESSION['part_pics_dir'] . '/' . $StockId . '.pdf';
	if (file_exists($File)) {
		//workaround for many variations of permission issues that could cause unlink fail
		@unlink($File);
		if (is_file($ImageFile)) {
			prnMsg(_('You do not have access to delete this item specification file.'), 'error');
		} else {
			$StockImgLink = _('No Image');
		}
	}
}
echo '</field>';

echo '<field>
		<label for="CategoryID">', _('Category'), ':</label>
		<select required="required" name="CategoryID" onchange="ReloadForm(ItemForm.UpdateCategories)">';

$SQL = "SELECT categoryid, categorydescription FROM stockcategory";
$ErrMsg = _('The stock categories could not be retrieved because');
$DbgMsg = _('The SQL used to retrieve stock categories and failed was');
$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

while ($MyRow = DB_fetch_array($Result)) {
	if (!isset($_POST['CategoryID']) or $MyRow['categoryid'] == $_POST['CategoryID']) {
		echo '<option selected="selected" value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
	}
	$Category = $MyRow['categoryid'];
}

if (!isset($_POST['CategoryID'])) {
	$_POST['CategoryID'] = $Category;
}

echo '</select><a target="_blank" href="' . $RootPath . '/StockCategories.php">&nbsp;&nbsp;&nbsp;' . _('Add or Modify Stock Categories') . '</a>
	</field>';

if (!isset($_POST['EOQ']) or $_POST['EOQ'] == '') {
	$_POST['EOQ'] = 0;
}

if (!isset($_POST['Volume']) or $_POST['Volume'] == '') {
	$_POST['Volume'] = 0;
}
if (!isset($_POST['GrossWeight']) or $_POST['GrossWeight'] == '') {
	$_POST['GrossWeight'] = 0;
}
if (!isset($_POST['NetWeight']) or $_POST['NetWeight'] == '') {
	$_POST['NetWeight'] = 0;
}
if (!isset($_POST['Controlled']) or $_POST['Controlled'] == '') {
	$_POST['Controlled'] = 0;
}
if (!isset($_POST['Serialised']) or $_POST['Serialised'] == '' or $_POST['Controlled'] == 0) {
	$_POST['Serialised'] = 0;
}
if (!isset($_POST['DecimalPlaces']) or $_POST['DecimalPlaces'] == '') {
	$_POST['DecimalPlaces'] = 0;
}
if (!isset($_POST['Discontinued']) or $_POST['Discontinued'] == '') {
	$_POST['Discontinued'] = 0;
}
if (!isset($_POST['Pansize'])) {
	$_POST['Pansize'] = 0;
}
if (!isset($_POST['ShrinkFactor'])) {
	$_POST['ShrinkFactor'] = 0;
}
if (!isset($_POST['NextSerialNo'])) {
	$_POST['NextSerialNo'] = 0;
}
if (!isset($_POST['MaterialCost'])) {
	$_POST['MaterialCost'] = 0;
}
if (!isset($_POST['LabourCost'])) {
	$_POST['LabourCost'] = 0;
}
if (!isset($_POST['OverheadCost'])) {
	$_POST['OverheadCost'] = 0;
}

echo '<field>
		<label for="EOQ">', _('Economic Order Quantity'), ':</label>
		<input type="text" class="number" name="EOQ" size="12" maxlength="10" value="', locale_number_format($_POST['EOQ'], 'Variable'), '" />
	</field>';

echo '<field>
		<label for="Volume">', _('Packaged Volume (metres cubed)'), ':</label>
		<input type="text" class="number" name="Volume" size="12" maxlength="10" value="', locale_number_format($_POST['Volume'], 'Variable'), '" />
	</field>';

echo '<field>
		<label for="GrossWeight">', _('Packaged Weight (KGs)'), ':</label>
		<input type="text" class="number" name="GrossWeight" size="12" maxlength="10" value="', locale_number_format($_POST['GrossWeight'], 'Variable'), '" />
	</field>';

echo '<field>
		<label for="NetWeight">', _('Net Weight (KGs)'), ':</label>
		<input type="text" class="number" name="NetWeight" size="12" maxlength="10" value="', locale_number_format($_POST['NetWeight'], 'Variable'), '" />
	</field>';

echo '<field>
		<label for="Units">', _('Units of Measure'), ':</label>
		<select name="Units">';

$SQL = "SELECT unitname FROM unitsofmeasure ORDER by unitname";
$UOMResult = DB_query($SQL);

if (!isset($_POST['Units'])) {
	$UOMrow['unitname'] = _('each');
}
while ($UOMrow = DB_fetch_array($UOMResult)) {
	if (isset($_POST['Units']) and $_POST['Units'] == $UOMrow['unitname']) {
		echo '<option selected="selected" value="', $UOMrow['unitname'], '">', $UOMrow['unitname'], '</option>';
	} else {
		echo '<option value="', $UOMrow['unitname'], '">', $UOMrow['unitname'], '</option>';
	}
}
echo '</select>
	</field>';

echo '<field>
		<label for="MBFlag">', _('Assembly, Kit, Manufactured or Service/Labour'), ':</label>
		<select name="MBFlag">';
if (!isset($_POST['MBFlag']) or $_POST['MBFlag'] == 'A') {
	echo '<option selected="selected" value="A">', _('Assembly'), '</option>';
} else {
	echo '<option value="A">', _('Assembly'), '</option>';
}
if (!isset($_POST['MBFlag']) or $_POST['MBFlag'] == 'K') {
	echo '<option selected="selected" value="K">', _('Kit'), '</option>';
} else {
	echo '<option value="K">', _('Kit'), '</option>';
}
if (!isset($_POST['MBFlag']) or $_POST['MBFlag'] == 'M') {
	echo '<option selected="selected" value="M">', _('Manufactured'), '</option>';
} else {
	echo '<option value="M">', _('Manufactured'), '</option>';
}
if (!isset($_POST['MBFlag']) or $_POST['MBFlag'] == 'G' or !isset($_POST['MBFlag']) or $_POST['MBFlag'] == '') {
	echo '<option selected="selected" value="G">', _('Phantom'), '</option>';
} else {
	echo '<option value="G">', _('Phantom'), '</option>';
}
if (!isset($_POST['MBFlag']) or $_POST['MBFlag'] == 'B' or !isset($_POST['MBFlag']) or $_POST['MBFlag'] == '') {
	echo '<option selected="selected" value="B">', _('Purchased'), '</option>';
} else {
	echo '<option value="B">', _('Purchased'), '</option>';
}

if (isset($_POST['MBFlag']) and $_POST['MBFlag'] == 'D') {
	echo '<option selected="selected" value="D">', _('Service/Labour'), '</option>';
} else {
	echo '<option value="D">', _('Service/Labour'), '</option>';
}

echo '</select>
	</field>';

echo '<field>
		<label for="Discontinued">', _('Current or Obsolete'), ':</label>
		<select name="Discontinued">';
if ($_POST['Discontinued'] == 0) {
	echo '<option selected="selected" value="0">', _('Current'), '</option>';
} else {
	echo '<option value="0">', _('Current'), '</option>';
}
if ($_POST['Discontinued'] == 1) {
	echo '<option selected="selected" value="1">', _('Obsolete'), '</option>';
} else {
	echo '<option value="1">', _('Obsolete'), '</option>';
}
echo '</select>
	</field>';

echo '<field>
		<label for="Controlled">', _('Batch, Serial or Lot Control'), ':</label>
		<select name="Controlled">';

if ($_POST['Controlled'] == 0) {
	echo '<option selected="selected" value="0">', _('No Control'), '</option>';
} else {
	echo '<option value="0">', _('No Control'), '</option>';
}
if ($_POST['Controlled'] == 1) {
	echo '<option selected="selected" value="1">', _('Controlled'), '</option>';
} else {
	echo '<option value="1">', _('Controlled'), '</option>';
}
echo '</select>
	</field>';

echo '<field>
		<label for="Serialised">', _('Serialised'), ':</label>
		<select name="Serialised">';
if ($_POST['Serialised'] == 0) {
	echo '<option selected="selected" value="0">', _('No'), '</option>';
} else {
	echo '<option value="0">', _('No'), '</option>';
}
if ($_POST['Serialised'] == 1) {
	echo '<option selected="selected" value="1">', _('Yes'), '</option>';
} else {
	echo '<option value="1">', _('Yes'), '</option>';
}
echo '</select>
	<fieldhelp>', _('this has no effect if the item is not Controlled'), '</fieldhelp>
</field>';

if ($_POST['Serialised'] == 1 and $_POST['MBFlag'] == 'M') {
	echo '<field>
			<td>' . _('Next Serial No (Greater than zero for auto numbering)') . ':</td>
			<td><input  type="text" name="NextSerialNo" size="15" maxlength="15" value="' . $_POST['NextSerialNo'] . '" /></td></field>';
} else {
	echo '<field><td><input type="hidden" name="NextSerialNo" value="0" /></td></field>';
}

echo '<field>
		<label for="Perishable">', _('Perishable'), ':</label>
		<select name="Perishable">';

if (!isset($_POST['Perishable']) or $_POST['Perishable'] == 0) {
	echo '<option selected="selected" value="0">', _('No'), '</option>';
} else {
	echo '<option value="0">', _('No'), '</option>';
}
if (isset($_POST['Perishable']) and $_POST['Perishable'] == 1) {
	echo '<option selected="selected" value="1">', _('Yes'), '</option>';
} else {
	echo '<option value="1">', _('Yes'), '</option>';
}
echo '</select>
	</field>';

echo '<field>
		<label for="DecimalPlaces">', _('Decimal Places for display Quantity'), ':</label>
		<input type="text" class="number" name="DecimalPlaces" size="1" maxlength="1" value="', $_POST['DecimalPlaces'], '" />
	</field>';

if (isset($_POST['BarCode'])) {
	$BarCode = $_POST['BarCode'];
} else {
	$BarCode = '';
}
echo '<field>
		<label for="BarCode">', _('Bar Code'), ':</label>
		<input type="text" name="BarCode" size="22" maxlength="20" value="', $BarCode, '" />
	</field>';

if (isset($_POST['DrawingNumber'])) {
	$DrawingNumber = $_POST['DrawingNumber'];
} else {
	$DrawingNumber = '';
}
echo '<field>
		<label for="DrawingNumber">', _('Drawing Number'), ':</label>
		<input type="text" name="DrawingNumber" size="22" maxlength="20" value="', $DrawingNumber, '" />
	</field>';

if (isset($_POST['DiscountCategory'])) {
	$DiscountCategory = $_POST['DiscountCategory'];
} else {
	$DiscountCategory = '';
}
echo '<field>
		<label for="DiscountCategory">', _('Discount Category'), ':</label>
		<input type="text" name="DiscountCategory" size="2" maxlength="2" value="', $DiscountCategory, '" />
	</field>';

echo '<field>
		<label for="TaxCat">', _('Tax Category'), ':</label>
		<select name="TaxCat">';
$SQL = "SELECT taxcatid, taxcatname FROM taxcategories ORDER BY taxcatname";
$Result = DB_query($SQL);

if (!isset($_POST['TaxCat'])) {
	$_POST['TaxCat'] = $_SESSION['DefaultTaxCategory'];
}

while ($MyRow = DB_fetch_array($Result)) {
	if ($_POST['TaxCat'] == $MyRow['taxcatid']) {
		echo '<option selected="selected" value="', $MyRow['taxcatid'], '">', $MyRow['taxcatname'], '</option>';
	} else {
		echo '<option value="', $MyRow['taxcatid'], '">', $MyRow['taxcatname'], '</option>';
	}
} //end while loop
echo '</select>
	</field>';

echo '<field>
		<label for="Pansize">', _('Pan Size'), ':</label>
		<input type="text" class="number" name="Pansize" size="6" maxlength="6" value="', locale_number_format($_POST['Pansize'], 0), '" />
	</field>';

echo '<field>
		<label for="ShrinkFactor">', _('Shrinkage Factor'), ':</label>
		<input type="text" class="number" name="ShrinkFactor" size="6" maxlength="6" value="', locale_number_format($_POST['ShrinkFactor'], 'Variable'), '" />
	</field>';

if (in_array($_SESSION['PageSecurityArray']['StockCostUpdate.php'], $_SESSION['AllowedPageSecurityTokens'])) {
	echo '<field>
			<label for="MaterialCost">', _('Material Cost'), ':</label>
			<input type="text" class="number" name="MaterialCost" size="6" maxlength="12" value="', locale_number_format($_POST['MaterialCost'], $_SESSION['StandardCostDecimalPlaces']), '" />
		</field>';

	echo '<field>
			<label for="LabourCost">', _('Labour Cost'), ':</label>
			<input type="text" class="number" name="LabourCost" size="6" maxlength="12" value="', locale_number_format($_POST['LabourCost'], $_SESSION['StandardCostDecimalPlaces']), '" />
		</field>';

	echo '<field>
			<label for="OverheadCost">', _('Overhead Cost'), ':</label>
			<input type="text" class="number" name="OverheadCost" size="6" maxlength="12" value="', locale_number_format($_POST['OverheadCost'], $_SESSION['StandardCostDecimalPlaces']), '" />
		</field>';
} else {
	echo '<field>
			<label for="MaterialCost">', _('Material Cost'), ':</label>
			<div class="fieldtext">', locale_number_format($_POST['MaterialCost'], $_SESSION['StandardCostDecimalPlaces']), '</div>
			<input type="hidden" name="MaterialCost" value="', $_POST['MaterialCost'], '" />
		</field>
		<field>
			<label for="LabourCost">', _('Labour Cost'), ':</label>
			<div class="fieldtext">', locale_number_format($_POST['LabourCost'], $_SESSION['StandardCostDecimalPlaces']), '</div>
			<input type="hidden" name="LabourCost" value="', $_POST['LabourCost'], '" />
		</field>
		<field>
			<label for="OverheadCost">', _('Overhead Cost'), ':</label>
			<div class="fieldtext">', locale_number_format($_POST['OverheadCost'], $_SESSION['StandardCostDecimalPlaces']), '</div>
			<input type="hidden" name="OverheadCost" value="' . $_POST['OverheadCost'] . '" />
		</field>';
}

echo '</fieldset>';

if (!isset($_POST['CategoryID'])) {
	$_POST['CategoryID'] = '';
}

$SQL = "SELECT stkcatpropid,
				label,
				controltype,
				defaultvalue,
				numericvalue,
				minimumvalue,
				maximumvalue
		FROM stockcatproperties
		WHERE categoryid ='" . $_POST['CategoryID'] . "'
		AND reqatsalesorder =0
		ORDER BY stkcatpropid";

$PropertiesResult = DB_query($SQL);
$PropertyCounter = 0;
$PropertyWidth = array();

if (DB_num_rows($PropertiesResult) > 0) {
	echo '<br />
			<fieldset>
				<legend>', _('Item Category Properties'), '</legend>';

	while ($PropertyRow = DB_fetch_array($PropertiesResult)) {

		if (isset($StockId)) {
			$PropValResult = DB_query("SELECT value FROM
									stockitemproperties
									WHERE stockid='" . $StockId . "'
									AND stkcatpropid ='" . $PropertyRow['stkcatpropid'] . "'");
			$PropValRow = DB_fetch_row($PropValResult);
			$PropertyValue = $PropValRow[0];
		} else {
			$PropertyValue = '';
		}
		echo '<field>';
		echo '<input type="hidden" name="PropID', $PropertyCounter, '" value="', $PropertyRow['stkcatpropid'], '" />';
		echo '<input type="hidden" name="PropNumeric', $PropertyCounter, '" value="', $PropertyRow['numericvalue'], '" />';
		echo '<label>', $PropertyRow['label'], '</label>';
		switch ($PropertyRow['controltype']) {
			case 0; //textbox
			if ($PropertyRow['numericvalue'] == 1) {
				echo '<input type="hidden" name="PropMin', $PropertyCounter, '" value="', $PropertyRow['minimumvalue'], '" />';
				echo '<input type="hidden" name="PropMax', $PropertyCounter, '" value="', $PropertyRow['maximumvalue'], '" />';

				echo '<input type="text" class="number" name="PropValue', $PropertyCounter, '" size="20" maxlength="100" value="', locale_number_format($PropertyValue, 'Variable'), '" />';
				echo _('A number between'), ' ', locale_number_format($PropertyRow['minimumvalue'], 'Variable'), ' ', _('and'), ' ', locale_number_format($PropertyRow['maximumvalue'], 'Variable'), ' ', _('is expected');
			} else {
				echo '<input type="text" name="PropValue', $PropertyCounter, '" size="20" maxlength="100" value="', $PropertyValue, '" />';
			}
		break;
		case 1; //select box
		$OptionValues = explode(',', $PropertyRow['defaultvalue']);
		echo '<select name="PropValue', $PropertyCounter, '">';
		foreach ($OptionValues as $PropertyOptionValue) {
			if ($PropertyOptionValue == $PropertyValue) {
				echo '<option selected="selected" value="', $PropertyOptionValue, '">', $PropertyOptionValue, '</option>';
			} else {
				echo '<option value="', $PropertyOptionValue, '">', $PropertyOptionValue, '</option>';
			}
		}
		echo '</select>';
	break;
	case 2; //checkbox
	echo '<input type="checkbox" name="PropValue', $PropertyCounter, '"';
	if ($PropertyValue == 1) {
		echo 'checked';
	}
	echo ' />';
break;
} //end switch
echo '<input type="hidden" name="PropType', $PropertyCounter, '" value="', $PropertyRow['controltype'], '" />';
echo '</field>';
$PropertyCounter++;

} //end loop round properties for the item category
unset($StockId);
echo '</fieldset>';
}
echo '<input type="hidden" name="PropertyCounter" value="', $PropertyCounter, '" />';

if ($New == 1) {
	echo '<div class="centre">
			<input type="submit" name="submit" value="', _('Insert New Item'), '" />
		</div>';
	echo '<input type="submit" name="UpdateCategories" style="visibility:hidden;width:1px" value="', _('Categories'), '" />';

} else {

	// Now the form to enter the item properties
	echo '<div class="centre">
			<input type="submit" name="submit" value="', _('Update'), '" /><br />
			<input type="submit" name="delete" value="', _('Delete This Item'), '" onclick="return MakeConfirm(\'', _('Are You Sure?'), '\');" />
		</div>
		<input type="submit" name="UpdateCategories" style="visibility:hidden;width:1px" value="', _('Categories'), '" />';

	prnMsg(_('Only click the Delete button if you are sure you wish to delete the item!') . _('Checks will be made to ensure that there are no stock movements, sales analysis records, sales order items or purchase order items for the item') . '. ' . _('No deletions will be allowed if they exist'), 'warn', _('WARNING'));
}

echo '</form>';
include ('includes/footer.php');
?>