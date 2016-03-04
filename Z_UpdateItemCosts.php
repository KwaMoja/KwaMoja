<?php
include('includes/session.inc');
$Title = _('Update Item Costs From CSV');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Update Item Costs from CSV file') . '" />' . ' ' . _('Update Item Costs from CSV file') . '</p>';

$FieldHeadings = array(
	'StockID',
	'Material Cost',
	'Labour Cost',
	'Overhead Cost'
);

if (isset($_FILES['CostUpdateFile']) and $_FILES['CostUpdateFile']['name']) { //start file processing
	//check file info
	$FileName = $_FILES['CostUpdateFile']['name'];
	$TempName = $_FILES['CostUpdateFile']['tmp_name'];
	$FileSize = $_FILES['CostUpdateFile']['size'];
	$InputError = 0;

	//get file handle
	$FileHandle = fopen($TempName, 'r');

	//get the header row
	$HeadRow = fgetcsv($FileHandle, 10000, ',');

	//check for correct number of fields
	if (count($HeadRow) != count($FieldHeadings)) {
		prnMsg(_('File contains') . ' ' . count($HeadRow) . ' ' . _('columns, expected') . ' ' . count($FieldHeadings), 'error');
		fclose($FileHandle);
		include('includes/footer.inc');
		exit;
	}

	//test header row field name and sequence
	$HeadingColumnNumber = 0;
	foreach ($HeadRow as $HeadField) {
		if (trim(mb_strtoupper($HeadField)) != trim(mb_strtoupper($FieldHeadings[$HeadingColumnNumber]))) {
			prnMsg(_('The file to import the item cost updates from contains incorrect column headings') . ' ' . mb_strtoupper($HeadField) . ' != ' . mb_strtoupper($FieldHeadings[$HeadingColumnNumber]) . '<br />' . _('The column headings must be') . ' StockID, Material Cost, Labour Cost, Overhead Cost', 'error');
			fclose($FileHandle);
			include('includes/footer.inc');
			exit;
		}
		$HeadingColumnNumber++;
	}
	//start database transaction
	DB_Txn_Begin();

	//loop through file rows
	$LineNumber = 1;
	while (($MyRow = fgetcsv($FileHandle, 10000, ',')) !== FALSE) {

		$StockID = mb_strtoupper($MyRow[0]);

		$NewCost = (double) $MyRow[1] + (double) $MyRow[2] + (double) $MyRow[3];

		$SQL = "SELECT mbflag,
						stockcosts.materialcost,
						stockcosts.labourcost,
						stockcosts.overheadcost,
						sum(quantity) as totalqoh
				FROM stockmaster
				INNER JOIN locstock
					ON stockmaster.stockid=locstock.stockid
				LEFT JOIN stockcosts
					ON stockmaster.stockid=stockcosts.stockid
					AND stockcosts.succeeded=0
				WHERE stockmaster.stockid='" . $StockID . "'
				GROUP BY materialcost,
						labourcost,
						overheadcost";

		$ErrMsg = _('The selected item code does not exist');
		$OldResult = DB_query($SQL, $ErrMsg);
		$OldRow = DB_fetch_array($OldResult);
		$QOH = $OldRow['totalqoh'];

		$OldCost = $OldRow['materialcost'] + $OldRow['labourcost'] + $OldRow['overheadcost'];
		//dont update costs for assembly or kit-sets or ghost items!!
		if ((abs($NewCost - $OldCost) > pow(10, -($_SESSION['StandardCostDecimalPlaces'] + 1)))
			and $OldRow['mbflag'] != 'K'
			and $OldRow['mbflag'] != 'A'
			and $OldRow['mbflag'] != 'G') {

			ItemCostUpdateGL($StockID, $NewCost, $OldCost, $QOH);

			$ErrMsg = _('The old cost details for the stock item could not be updated because');
			$DbgMsg = _('The SQL that failed was');
			$SQL = "UPDATE stockcosts SET succeeded=1 WHERE stockid='" . $StockId . "'";
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			$SQL = "INSERT INTO stockcosts VALUES('" . $StockId . "',
												'" . $MyRow[1] . "',
												'" . $MyRow[2] . "',
												'" . $MyRow[3] . "',
												CURRENT_TIMESTAMP,
												0)";
			$ErrMsg = _('The new cost details for the stock item could not be inserted because');
			$DbgMsg = _('The SQL that failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			$SQL = "UPDATE stockmaster SET lastcostupdate=CURRENT_DATE WHERE stockid='" . $StockId . "'";
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			UpdateCost($StockID); //Update any affected BOMs

		}

		$LineNumber++;
	}

	DB_Txn_Commit();
	prnMsg(_('Batch Update of costs') . ' ' . $FileName . ' ' . _('has been completed. All transactions committed to the database.'), 'success');

	fclose($FileHandle);

} else { //show file upload form

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" enctype="multipart/form-data">';
	echo '<div class="centre">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<div class="page_help_text">' . _('This function updates the costs of all items from a comma separated variable (csv) file.') . '<br />' . _('The file must contain two columns, and the first row should be the following headers:') . '<br /><i>StockID, Material Cost, Labour Cost, Overhead Cost</i><br />' . _('followed by rows containing these four fields for each cost to be updated.') . '<br />' . _('The StockID field must have a corresponding entry in the stockmaster table.') . '</div>';

	echo '<br /><input type="hidden" name="MAX_FILE_SIZE" value="1000000" />' . _('Upload file') . ': <input name="CostUpdateFile" type="file" />
			<input type="submit" name="submit" value="' . _('Send File') . '" />
		</div>
		</form>';
}

include('includes/footer.inc');

?>