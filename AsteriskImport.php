<?php

include('includes/session.inc');
$Title = _('Import Asterisk Data');
include('includes/header.inc');

echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . $Title . '" />' . ' ' . $Title . '</p>';

if (isset($_POST['Submit'])) { //start file processing

	/* First check both file exist */
	if (!file_exists($_POST['Day'])) {
		prnMsg(_('The Asterisk data file does not exist for this date.'), 'error');
		include('includes/footer.inc');
		exit;
	}

	/* Check that each file has the required number of fields for each record */
	/* First the service provider data file */
	$FileHandle = fopen($_POST['Day'], 'r');
	$i = 1;
	while (($MyRow = fgetcsv($FileHandle, 10000, ',')) !== FALSE) {
		if (count($MyRow) != 16) {
			prnMsg(_('Row number') . ' ' . $i . ' ' . _('has') . ' ' . count($MyRow) . ' ' . _('columns, expected') . ' 16. ' . _('Download the template to see the expected columns.'), 'error');
			fclose($FileHandle);
			include('includes/footer.inc');
			exit;
		}
		++$i;
		$SQL = "SELECT COUNT(debtorno) as totalrecords FROM debtorsmaster WHERE debtorno='" . $MyRow[0] . "'";
		$Result = DB_query($SQL);
		$CheckRow = DB_fetch_row($Result);
		if ($CheckRow[0] == 0) {
			echo '<div class="centre">
					<a href="Customers.php" target="_blank">' . _('Create a new customer account.') . '</a>
				</div>';
			prnMsg(_('Account code') . ' ' . $MyRow[0] . ' ' . _('has not been created yet. Please create and import the file again.'), 'error');
			include('includes/footer.inc');
			exit;
		}
	}

	//start database transaction
	DB_Txn_Begin();

	//loop through the service provider file rows
	$LineNumber = 1;
	$FileHandle = fopen($_POST['Day'], 'r');
	while (($MyRow = fgetcsv($FileHandle, 10000, ',')) !== FALSE) {

		// cleanup the data (csv files often import with empty strings and such)
		$InputError = 0;
		$StockId = mb_strtoupper($MyRow[0]);
		foreach ($MyRow as $Value) {
			$Value = trim($Value);
			$Value = str_replace('"', '', $Value);
		}

		//first off format the datetime fields correctly
		$StartDateTime = date('Y-m-d H:i:s', strtotime($MyRow[9]));
		$AnswerDateTime = date('Y-m-d H:i:s', strtotime($MyRow[10]));
		$EndDateTime = date('Y-m-d H:i:s', strtotime($MyRow[11]));

		$Duration = (int) $MyRow[12];
		$Billable = (int) $MyRow[13];

		/* Trim off any leading digits from destination number */
		$Source = ltrim($MyRow[1], '0');
		$Destination = ltrim($MyRow[2], '0');

		$TestString = '';
		for ($i = 0; $i < mb_strlen($Destination); $i++) {
			$TestString = $TestString . $Destination[$i];
			$SQL = "SELECT ratepersecond FROM telecomrates WHERE prefix LIKE '" . $TestString . '%' . "'";
			$Result = DB_query($SQL);
			$CostRow = DB_fetch_row($Result);
			if (DB_num_rows($Result) === 0) {
				$CostPerSecond = $LastCostPerSecond;
				break;
			} else {
				$CostPerSecond = 0;
			}
			$LastCostPerSecond = $CostRow[0];

			$QualityCode = mb_substr($MyRow[8], 4, 5);
			switch ($QualityCode) {

				case '00000':
					$Quality = 'Premium';
					break;

				case '00001':
					$Quality = 'Grey';
					break;

				default:
					$Quality = 'Standard';
			}
		}
		if ($InputError != 1) {

			//Insert the price
			$SQL = "INSERT INTO asteriskdata (accountcode,
											sourcenumber,
											destinationnumber,
											dcontext,
 											clid,
 											channel,
 											dstchannel,
 											lastapp,
 											lastdata,
 											callstartdate,
 											callanswerdate,
 											callenddate,
 											callduration,
 											billseconds,
 											disposition,
 											amaflags,
 											costpersecond,
 											quality
										) VALUES (
											'" . $MyRow[0] . "',
											'" . $Source . "',
											'" . $Destination . "',
											'" . $MyRow[3] . "',
											'" . $MyRow[4] . "',
											'" . $MyRow[5] . "',
											'" . $MyRow[6] . "',
											'" . $MyRow[7] . "',
											'" . $MyRow[8] . "',
											'" . $StartDateTime . "',
											'" . $AnswerDateTime . "',
											'" . $EndDateTime . "',
											'" . $Duration . "',
											'" . $Billable . "',
											'" . $MyRow[14] . "',
											'" . $MyRow[15] . "',
											'" . $CostPerSecond . "',
											'" . $Quality . "'
										)";

			$ErrMsg = _('The Asterisk record could not be added because');
			$DbgMsg = _('The SQL that was used to add the Asterisk record failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
		}
		$LineNumber++;
	}

	if ($InputError == 1) { //exited loop with errors so rollback
		prnMsg(_('Failed on row ') . $LineNumber . _('of ') . $_POST['Day'] . _('. Batch import has been rolled back.'), 'error');
		DB_Txn_Rollback();
	} else { //all good so commit data transaction
		DB_Txn_Commit();
		rename($_POST['Day'], 'companies/' . $_SESSION['DatabaseName'] . '/pbx-data/imported/' . basename($_POST['Day']));
		prnMsg(_('Batch Import of') . ' ' . $_POST['Day'] . ' ' . _('has been completed. All transactions committed to the database.'), 'success');
	}

	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Import another days transactions') . '</a>
		</div>';

	fclose($FileHandle);

} else { //show file upload form

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" enctype="multipart/form-data">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$FilesToImport = glob('companies/' . $_SESSION['DatabaseName'] . '/pbx-data/*.as');
	if (sizeof($FilesToImport) > 0) {
		echo '<table class="selection">';
		echo '<tr>
				<td>' . _('Days transactions to import') . '</td>
				<td><select name="Day">';
		foreach ($FilesToImport as $File) {
			echo '<option value="' . $File . '">' . date($_SESSION['DefaultDateFormat'], strtotime(basename($File, '.as'))) . '</option>';
		}
		echo '</select>
					</td>
				</tr>';

		echo '</table>';

		echo '<div class="centre">
				<input type="submit" name="Submit" value="Import Files" />
			</div>';
	} else {
		prnMsg(_('There are no files to import'), 'info');
	}
	echo '</form>';

}

include('includes/footer.inc');

?>