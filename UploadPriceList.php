<?php
include ('includes/session.php');
$Title = _('Import Sales Price List');
include ('includes/header.php');

$FieldHeadings = array('StockID', //  0 'STOCKID',
'PriceListID', //  1 'Price list id',
'CurrencyCode', //  2 'Currency Code',
'Price'
//  3 'Price'
);

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', $Title, '" alt="', $Title, '" />', ' ', $Title, '
	</p>';

if (isset($_FILES['userfile']) and $_FILES['userfile']['name']) { //start file processing
	//check file info
	$FileName = $_FILES['userfile']['name'];
	$TempName = $_FILES['userfile']['tmp_name'];
	$FileSize = $_FILES['userfile']['size'];
	$FieldTarget = 4;
	$InputError = 0;

	//get file handle
	$FileHandle = fopen($TempName, 'r');

	//get the header row
	$HeadRow = fgetcsv($FileHandle, 10000, ",");

	//check for correct number of fields
	if (count($HeadRow) != count($FieldHeadings)) {
		prnMsg(_('File contains ' . count($HeadRow) . ' columns, expected ' . count($FieldHeadings) . '. Try downloading a new template.'), 'error');
		fclose($FileHandle);
		include ('includes/footer.php');
		exit;
	}

	//test header row field name and sequence
	$head = 0;
	foreach ($HeadRow as $HeadField) {
		if (trim(mb_strtoupper($HeadField)) != trim(mb_strtoupper($FieldHeadings[$head]))) {
			prnMsg(_('File contains incorrect headers ' . mb_strtoupper($HeadField) . ' != ' . mb_strtoupper($FieldHeadings[$head]) . '. Try downloading a new template.'), 'error');
			fclose($FileHandle);
			include ('includes/footer.php');
			exit;
		}
		$head++;
	}

	//start database transaction
	DB_Txn_Begin();

	//loop through file rows
	$RowNumber = 1;
	while (($MyRow = fgetcsv($FileHandle, 10000, ",")) !== false) {

		//check for correct number of fields
		$FieldCount = count($MyRow);
		if ($FieldCount != $FieldTarget) {
			prnMsg(_($FieldTarget . ' fields required, ' . $FieldCount . ' fields received'), 'error');
			fclose($FileHandle);
			include ('includes/footer.php');
			exit;
		}

		// cleanup the data (csv files often import with empty strings and such)
		$StockId = mb_strtoupper($MyRow[0]);
		foreach ($MyRow as & $Value) {
			$Value = trim($Value);
			$Value = str_replace('"', '', $Value);
		}

		//first off check that the item actually exists
		$SQL = "SELECT COUNT(stockid) FROM stockmaster WHERE stockid='" . $MyRow[0] . "'";
		$Result = DB_query($SQL);
		$testrow = DB_fetch_row($Result);
		if ($testrow[0] == 0) {
			$InputError = 1;
			prnMsg(_('Stock item ' . $MyRow[0] . ' does not exist'), 'error');
		}
		//Then check that the price list actually exists
		$SQL = "SELECT COUNT(typeabbrev) FROM salestypes WHERE typeabbrev='" . $MyRow[1] . "'";
		$Result = DB_query($SQL);
		$testrow = DB_fetch_row($Result);
		if ($testrow[0] == 0) {
			$InputError = 1;
			prnMsg(_('Price List ' . $MyRow[1] . ' does not exist'), 'error');
		}

		//Then check that the currency code actually exists
		$SQL = "SELECT COUNT(currabrev) FROM currencies WHERE currabrev='" . $MyRow[2] . "'";
		$Result = DB_query($SQL);
		$testrow = DB_fetch_row($Result);
		if ($testrow[0] == 0) {
			$InputError = 1;
			prnMsg(_('Price List ' . $MyRow[2] . ' does not exist'), 'error');
		}

		//Finally force the price to be a double
		$MyRow[3] = (double)$MyRow[3];
		if ($InputError != 1) {

			//Firstly close any open prices for this item
			$SQL = "UPDATE prices
						SET enddate='" . FormatDateForSQL($_POST['StartDate']) . "'
						WHERE stockid='" . $MyRow[0] . "'
							AND enddate>CURRENT_DATE
							AND typeabbrev='" . $MyRow[1] . "'";
			$Result = DB_query($SQL);

			//Insert the price
			$SQL = "INSERT INTO prices (stockid,
										typeabbrev,
										currabrev,
										price,
										startdate
									) VALUES (
										'" . $MyRow[0] . "',
										'" . $MyRow[1] . "',
										'" . $MyRow[2] . "',
										'" . $MyRow[3] . "',
										'" . FormatDateForSQL($_POST['StartDate']) . "'
										)";

			$ErrMsg = _('The price could not be added because');
			$DbgMsg = _('The SQL that was used to add the price failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

		}

		if ($InputError == 1) { //this row failed so exit loop
			break;
		}

		$RowNumber++;

	}

	if ($InputError == 1) { //exited loop with errors so rollback
		prnMsg(_('Failed on row ' . $RowNumber . '. Batch import has been rolled back.'), 'error');
		DB_Txn_Rollback();
	} else { //all good so commit data transaction
		DB_Txn_Commit();
		prnMsg(_('Batch Import of') . ' ' . $FileName . ' ' . _('has been completed. All transactions committed to the database.'), 'success');
	}

	fclose($FileHandle);

} else { //show file upload form
	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post" enctype="multipart/form-data">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />';

	echo '<div class="page_help_text">', _('This function loads a new sales price list from a comma separated variable (csv) file.'), '<br />', _('The file must contain four columns, and the first row should be the following headers'), ':', '<br />', _('StockID,PriceListID,CurrencyCode,Price'), '<br />', _('followed by rows containing these four fields for each price to be uploaded.'), '<br />', _('The StockID, PriceListID, and CurrencyCode fields must have a corresponding entry in the stockmaster, salestypes, and currencies tables.'), '</div>';

	echo '<fieldset>
			<legend>', _('Input File Details'), '</legend>
			<field>
				<label for="StartDate">', _('Prices effective from'), ':</label>
				<input type="text" name="StartDate" size="10" class="date" value="', date($_SESSION['DefaultDateFormat']), '" />
			</field>
			<field>
				<label for="userfile>', _('Upload file'), ':</label>
				<input name="userfile" type="file" />
			</field>
		</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="submit" value="', _('Send File'), '" />
		</div>
	</form>';

}

include ('includes/footer.php');

?>