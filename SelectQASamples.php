<?php
/* $Id: SelectQASamples.php 1 2014-09-08 10:42:50Z agaluski $*/

include ('includes/session.php');
$Title = _('Select QA Samples');
$ViewTopic = 'QualityAssurance'; // Filename in ManualContents.php's TOC.
$BookMark = 'QA_Samples'; // Anchor's id in the manual's html document.
include ('includes/header.php');
include ('includes/SQL_CommonFunctions.php');

if (isset($_GET['SelectedSampleID'])) {
	$SelectedSampleID = mb_strtoupper($_GET['SelectedSampleID']);
} elseif (isset($_POST['SelectedSampleID'])) {
	$SelectedSampleID = mb_strtoupper($_POST['SelectedSampleID']);
}

if (isset($_GET['SelectedStockItem'])) {
	$SelectedStockItem = $_GET['SelectedStockItem'];
} elseif (isset($_POST['SelectedStockItem'])) {
	$SelectedStockItem = $_POST['SelectedStockItem'];
}
if (isset($_GET['LotNumber'])) {
	$LotNumber = $_GET['LotNumber'];
} elseif (isset($_POST['LotNumber'])) {
	$LotNumber = $_POST['LotNumber'];
} else {
	$LotNumber = '';
}
if (isset($_GET['SampleID'])) {
	$SampleID = $_GET['SampleID'];
} elseif (isset($_POST['SampleID'])) {
	$SampleID = $_POST['SampleID'];
} else {
	$SampleID = '';
}
if (!isset($_POST['FromDate'])) {
	$_POST['FromDate'] = Date(($_SESSION['DefaultDateFormat']), strtotime(' - 15 days'));
}
if (!isset($_POST['ToDate'])) {
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', _('Search'), '" alt="" />', $Title, '
	</p>';

if (isset($_POST['submit'])) {

	//first off validate inputs sensible
	if (isset($SelectedSampleID) and $InputError != 1) {
		//check to see all values are in spec or at least entered
		$Result = DB_query("SELECT count(sampleid) FROM sampleresults
							WHERE sampleid = '" . $SelectedSampleID . "'
							AND showoncert='1'
							AND testvalue=''");
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0] > 0 and $_POST['Cert'] == '1') {
			$_POST['Cert'] = '0';
			$Msg = _('Test Results have not all been entered.  This Lot is not able to be used for a a Certificate of Analysis');
			prnMsg($Msg, 'error');
		}
		$Result = DB_query("SELECT count(sampleid) FROM sampleresults
							WHERE sampleid = '" . $SelectedSampleID . "'
							AND showoncert='1'
							AND isinspec='0'");
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0] > 0 and $_POST['Cert'] == '1') {
			$Msg = _('Some Results are out of Spec');
			prnMsg($Msg, 'warning');
		}
		$SQL = "UPDATE qasamples SET identifier='" . $_POST['Identifier'] . "',
									sampledate='" . FormatDateForSQL($_POST['SampleDate']) . "',
									comments='" . $_POST['Comments'] . "',
									cert='" . $_POST['Cert'] . "'
				WHERE sampleid = '" . $SelectedSampleID . "'";

		$Msg = _('QA Sample record for') . ' ' . $SelectedSampleID . ' ' . _('has been updated');
		$ErrMsg = _('The update of the QA Sample failed because');
		$DbgMsg = _('The SQL that was used and failed was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
		prnMsg($Msg, 'success');
		if ($_POST['Cert'] == 1) {
			$Result = DB_query("SELECT prodspeckey, lotkey FROM qasamples
							WHERE sampleid = '" . $SelectedSampleID . "'");
			$MyRow = DB_fetch_row($Result);
			if ($MyRow[0] > '') {
				$SQL = "UPDATE qasamples SET cert='0'
						WHERE sampleid <> '" . $SelectedSampleID . "'
						AND prodspeckey='" . $MyRow[0] . "'
						AND lotkey='" . $MyRow[1] . "'";
				$Msg = _('All other samples for this Specification and Lot was marked as Cert=No');
				$ErrMsg = _('The update of the QA Sample failed because');
				$DbgMsg = _('The SQL that was used and failed was');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
				prnMsg($Msg, 'success');
			}
		}

	} else {
		CreateQASample($_POST['ProdSpecKey'], $_POST['LotKey'], $_POST['Identifier'], $_POST['Comments'], $_POST['Cert'], $_POST['DuplicateOK']);
		$SelectedSampleID = DB_Last_Insert_ID('qasamples', 'sampleid');
		if ($SelectedSampleID > '') {
			$Msg = _('Created New Sample');
			prnMsg($Msg, 'success');
		}
	}
	unset($SelectedSampleID);
	unset($_POST['ProdSpecKey']);
	unset($_POST['LotKey']);
	unset($_POST['Identifier']);
	unset($_POST['Comments']);
	unset($_POST['Cert']);
} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button
	// PREVENT DELETES IF DEPENDENT RECORDS
	$SQL = "SELECT COUNT(*) FROM sampleresults WHERE sampleresults.sampleid='" . $SelectedSampleID . "'
											AND sampleresults.testvalue>''";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		prnMsg(_('Cannot delete this Sample ID because there are test results tied to it'), 'error');
	} else {
		$SQL = "DELETE FROM sampleresults WHERE sampleid='" . $SelectedSampleID . "'";
		$ErrMsg = _('The sample results could not be deleted because');
		$Result = DB_query($SQL, $ErrMsg);
		$SQL = "DELETE FROM qasamples WHERE sampleid='" . $SelectedSampleID . "'";
		$ErrMsg = _('The QA Sample could not be deleted because');
		$Result = DB_query($SQL, $ErrMsg);

		prnMsg(_('QA Sample') . ' ' . $SelectedSampleID . _('has been deleted from the database'), 'success');
		unset($SelectedSampleID);
		unset($_GET['delete']);
	}
}

if (!isset($SelectedSampleID)) {

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	if (isset($_POST['ResetPart'])) {
		unset($SelectedStockItem);
	}
	$InputError = 0;
	if (isset($SampleID) and $SampleID != '') {
		if (!is_numeric($SampleID)) {
			prnMsg(_('The Sample ID entered') . ' <U>' . _('MUST') . '</U> ' . _('be numeric'), 'error');
			unset($SampleID);
		} else {
			echo _('Sample ID') . ' - ' . $SampleID;
		}
	}
	if (!is_date($_POST['FromDate'])) {
		$InputError = 1;
		prnMsg(_('Invalid From Date'), 'error');
		$_POST['FromDate'] = Date(($_SESSION['DefaultDateFormat']), strtotime($UpcomingDate . ' - 15 days'));
	}
	if (!is_date($_POST['ToDate'])) {
		$InputError = 1;
		prnMsg(_('Invalid To Date'), 'error');
		$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
	}
	if (isset($_POST['SearchParts'])) {
		if ($_POST['Keywords'] and $_POST['StockCode']) {
			prnMsg(_('Stock description keywords have been used in preference to the Stock code extract entered'), 'info');
		}
		if ($_POST['Keywords']) {
			//insert wildcard characters in spaces
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.decimalplaces,
					SUM(locstock.quantity) as qoh,
					stockmaster.units,
				FROM stockmaster INNER JOIN locstock
				ON stockmaster.stockid = locstock.stockid
				INNER JOIN locationusers ON locationusers.loccode = locstock.loccode
						AND locationusers.userid='" . $_SESSION['UserID'] . "'
						AND locationusers.canview=1
				WHERE stockmaster.description " . LIKE . " '" . $SearchString . "'
				AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.decimalplaces,
					stockmaster.units
				ORDER BY stockmaster.stockid";
		} elseif ($_POST['StockCode']) {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.decimalplaces,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units
				FROM stockmaster INNER JOIN locstock
					ON stockmaster.stockid = locstock.stockid
				INNER JOIN locationusers ON locationusers.loccode = locstock.loccode
						AND locationusers.userid='" . $_SESSION['UserID'] . "'
						AND locationusers.canview=1
				WHERE stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
				AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.decimalplaces,
					stockmaster.units
				ORDER BY stockmaster.stockid";
		} elseif (!$_POST['StockCode'] and !$_POST['Keywords']) {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.decimalplaces,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units
				FROM stockmaster INNER JOIN locstock ON stockmaster.stockid = locstock.stockid
				INNER JOIN locationusers ON locationusers.loccode = locstock.loccode
						AND locationusers.userid='" . $_SESSION['UserID'] . "'
						AND locationusers.canview =1
				WHERE stockmaster.categoryid='" . $_POST['StockCat'] . "'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.decimalplaces,
					stockmaster.units
				ORDER BY stockmaster.stockid";
		}

		$ErrMsg = _('No stock items were returned by the SQL because');
		$DbgMsg = _('The SQL used to retrieve the searched parts was');
		$StockItemsResult = DB_query($SQL, $ErrMsg, $DbgMsg);
	}

	if (true or !isset($LotNumber) or $LotNumber == '') { //revisit later, right now always show all inputs
		if (!isset($LotNumber)) {
			$LotNumber = '';
		}
		if (!isset($SampleID)) {
			$SampleID = '';
		}
		echo '<fieldset>
				<legend>', _('Search Criteria'), '</legend>';

		if (isset($SelectedStockItem)) {
			echo '<field>
					<label>', _('For the part'), ':</label>
					<div class="fieldtext">', $SelectedStockItem, '</div> ', _('and'), ' ', '<input type="hidden" name="SelectedStockItem" value="', $SelectedStockItem, '" />
				</field>';
		}
		echo '<field>
				<label for="">', _('Lot Number'), ':</label>
				<input type="search" name="LotNumber" autofocus="autofocus" maxlength="20" size="12" value="', $LotNumber, '" />
			</field>';

		echo '<field>
				<label for="">', _('Sample ID'), ':</label>
				<input type="search" name="SampleID" maxlength="10" size="10" value="', $SampleID, '" />
			</field>';

		echo '<field>
				<label for="">', _('From Sample Date'), ':</label>
				<input type="text" name="FromDate" size="10" class="date" value="', $_POST['FromDate'], '" />
			</field>';

		echo '<field>
				<label for="">', _('To Sample Date'), ':</label>
				<input type="text" name="ToDate" size="10" class="date" value="', $_POST['ToDate'], '" />
			</field>';

		echo '</fieldset>';

		echo '<div class="centre">
				<input type="submit" name="SearchSamples" value="', _('Search Samples'), '" />
			</div>';
	}
	$SQL = "SELECT categoryid,
				categorydescription
			FROM stockcategory
			ORDER BY categorydescription";
	$Result1 = DB_query($SQL);
	echo '<fieldset>
			<legend>', _('To search for samples for a specific part use the part selection facilities below'), '</legend>';

	echo '<field>
			<label for="StockCat">', _('Select a stock category'), ':</label>
			<select name="StockCat">';
	while ($MyRow1 = DB_fetch_array($Result1)) {
		if (isset($_POST['StockCat']) and $MyRow1['categoryid'] == $_POST['StockCat']) {
			echo '<option selected="selected" value="', $MyRow1['categoryid'], '">', $MyRow1['categorydescription'], '</option>';
		} else {
			echo '<option value="', $MyRow1['categoryid'], '">', $MyRow1['categorydescription'], '</option>';
		}
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="Keywords">', _('Enter text extracts in the'), ' <b>', _('description'), '</b>:</label>
			<td><input type="text" name="Keywords" size="20" maxlength="25" /></td>
		</field>';

	echo '<field>
			<label for="StockCode">', _('OR'), ' </b>', _('Enter extract of the'), '<b> ', _('Stock Code'), '</b>:</label>
			<input type="text" name="StockCode" size="20" maxlength="25" />
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="SearchParts" value="', _('Search Parts Now'), '" />
			<input type="submit" name="ResetPart" value="', _('Show All'), '" />
		</div>';

	if (isset($StockItemsResult)) {
		echo '<table>
				<thead>
					<tr>
						<th class="SortedColumn">', _('Code'), '</th>
						<th class="SortedColumn">', _('Description'), '</th>
						<th class="SortedColumn">', _('On Hand'), '</th>
						<th class="SortedColumn">', _('Units'), '</th>
					</tr>
				</thead>';
		echo '<tbody>';

		while ($MyRow = DB_fetch_array($StockItemsResult)) {
			echo '<tr class="striped_row">
					<td><input type="submit" name="SelectedStockItem" value="', $MyRow['stockid'], '"</td>
					<td>', $MyRow['description'], '</td>
					<td class="number">', locale_number_format($MyRow['qoh'], $MyRow['decimalplaces']), '</td>
					<td>', $MyRow['units'], '</td>
				</tr>';
		}
		//end of while loop
		echo '</tbody>';
		echo '</table>';
	}
	//end if stock search results to show
	else {
		$FromDate = FormatDateForSQL($_POST['FromDate']);
		$ToDate = FormatDateForSQL($_POST['ToDate']);
		if (isset($LotNumber) and $LotNumber != '') {
			$SQL = "SELECT sampleid,
							prodspeckey,
							description,
							lotkey,
							identifier,
							createdby,
							sampledate,
							cert
						FROM qasamples
						LEFT OUTER JOIN stockmaster
							ON stockmaster.stockid=qasamples.prodspeckey
						WHERE lotkey='" . filter_number_format($LotNumber) . "'";
		} elseif (isset($SampleID) and $SampleID != '') {
			$SQL = "SELECT sampleid,
							prodspeckey,
							description,
							lotkey,
							identifier,
							createdby,
							sampledate,
							cert
						FROM qasamples
						LEFT OUTER JOIN stockmaster
							ON stockmaster.stockid=qasamples.prodspeckey
						WHERE sampleid='" . filter_number_format($SampleID) . "'";
		} else {
			if (isset($SelectedStockItem)) {
				$SQL = "SELECT sampleid,
							prodspeckey,
							description,
							lotkey,
							identifier,
							createdby,
							sampledate,
							cert
						FROM qasamples
						INNER JOIN stockmaster
							ON stockmaster.stockid=qasamples.prodspeckey
						WHERE stockid='" . $SelectedStockItem . "'
							AND sampledate>='" . $FromDate . "'
							AND sampledate <='" . $ToDate . "'";
			} else {
				$SQL = "SELECT sampleid,
							prodspeckey,
							description,
							lotkey,
							identifier,
							createdby,
							sampledate,
							comments,
							cert
						FROM qasamples
						LEFT OUTER JOIN stockmaster
							ON stockmaster.stockid=qasamples.prodspeckey
						WHERE sampledate>='" . $FromDate . "'
							AND sampledate <='" . $ToDate . "'";
			} //no stock item selected
			
		} //end no sample id selected
		$ErrMsg = _('No QA samples were returned by the SQL because');
		$SampleResult = DB_query($SQL, $ErrMsg);
		if (DB_num_rows($SampleResult) > 0) {

			echo '<table cellpadding="2" width="90%">
					<thead>
						<tr>
							<th class="SortedColumn">', _('Enter Results'), '</th>
							<th class="SortedColumn">', _('Specification'), '</th>
							<th class="SortedColumn">', _('Description'), '</th>
							<th class="SortedColumn">', _('Lot / Serial'), '</th>
							<th class="SortedColumn">', _('Identifier'), '</th>
							<th class="SortedColumn">', _('Created By'), '</th>
							<th class="SortedColumn">', _('Sample Date'), '</th>
							<th class="SortedColumn">', _('Comments'), '</th>
							<th class="SortedColumn">', _('Cert Allowed'), '</th>
							<th></th>
							<th></th>
						</tr>
					</thead>';

			echo '<tbody>';
			while ($MyRow = DB_fetch_array($SampleResult)) {
				$ModifySampleID = $RootPath . '/TestPlanResults.php?SelectedSampleID=' . $MyRow['sampleid'];
				$Edit = '<a href="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '?SelectedSampleID=' . urlencode($MyRow['sampleid']) . '">' . _('Edit') . '</a>';
				$Delete = '<a href="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '?delete=yes&amp;SelectedSampleID=' . urlencode($MyRow['sampleid']) . '" onclick="return confirm(\'' . _('Are you sure you wish to delete this Sample ID ?') . '\');">' . _('Delete') . '</a>';
				$FormatedSampleDate = ConvertSQLDate($MyRow['sampledate']);

				//echo '<div class="centre"><a href="' . htmlspecialchars(basename(__FILE__),ENT_QUOTES,'UTF-8') . '?CopyTest=yes&amp;SelectedSampleID=' .$SelectedSampleID .'">' . _('Copy These Results') . '</a></div>';
				//echo '<div class="centre"><a target="_blank" href="'. $RootPath . '/PDFTestPlan.php?SelectedSampleID=' .$SelectedSampleID .'">' . _('Print Testing Worksheet') . '</a></div>';
				if ($MyRow['cert'] == 1) {
					$CertAllowed = '<a target="_blank" href="' . $RootPath . '/PDFCOA.php?LotKey=' . urlencode($MyRow['lotkey']) . '&ProdSpec=' . urlencode($MyRow['prodspeckey']) . '">' . _('Yes') . '</a>';
				} else {
					$CertAllowed = _('No');
				}

				echo '<tr class="striped_row">
						<td><a href="', $ModifySampleID, '">', str_pad($MyRow['sampleid'], 10, '0', STR_PAD_LEFT), '</a></td>
						<td>', $MyRow['prodspeckey'], '</td>
						<td>', $MyRow['description'], '</td>
						<td>', $MyRow['lotkey'], '</td>
						<td>', $MyRow['identifier'], '</td>
						<td>', $MyRow['createdby'], '</td>
						<td>', $FormatedSampleDate, '</td>
						<td>', $MyRow['comments'], '</td>
						<td>', $CertAllowed, '</td>
						<td>', $Edit, '</td>
						<td>', $Delete, '</td>
					</tr>';
				//end of page full new headings if
				
			} //end of while loop
			echo '</tbody>';
			echo '</table>';
		} // end if Pick Lists to show
		
	}
} //end of ifs and buts!
if (isset($SelectedSampleID)) {
	echo '<div class="centre">
			<a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">', _('Show All Samples'), '</a>
		</div>';
}

if (!isset($_GET['delete'])) {

	if (isset($SelectedSampleID)) {

		$SQL = "SELECT prodspeckey,
						lotkey,
						identifier,
						comments,
						cert,
						sampledate
				FROM qasamples
				WHERE sampleid='" . $SelectedSampleID . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['ProdSpecKey'] = $MyRow['prodspeckey'];
		$_POST['LotKey'] = $MyRow['lotkey'];
		$_POST['Identifier'] = $MyRow['identifier'];
		$_POST['Comments'] = $MyRow['comments'];
		$_POST['SampleDate'] = ConvertSQLDate($MyRow['sampledate']);
		$_POST['Cert'] = $MyRow['cert'];

		echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
		echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
		echo '<input type="hidden" name="SelectedSampleID" value="', $SelectedSampleID, '" />';

		echo '<fieldset>
				<legend>', _('Edit Sample Information'), '</legend>';

		echo '<field>
				<label>', _('Sample ID'), ':</label>
				<div class="fieldtext">', str_pad($SelectedSampleID, 10, '0', STR_PAD_LEFT), '</div>
			</field>';

		echo '<field>
				<label>', _('Specification'), ':</label>
				<div class="fieldtext">', $_POST['ProdSpecKey'], '</div>
			</field>';

		echo '<field>
				<label>', _('Lot'), ':</label>
				<div class="fieldtext">', $_POST['LotKey'], '</div>
			</field>';

		echo '<field>
				<label for="Identifier">', _('Identifier'), ':</label>
				<input type="text" name="Identifier" size="10" maxlength="10" value="', $_POST['Identifier'], '" />
			</field>';

		echo '<field>
				<label for="Comments">', _('Comments'), ':</label>
				<input type="text" name="Comments" size="30" maxlength="255" value="', $_POST['Comments'], '" />
			</field>';

		echo '<field>
				<label for="SampleDate">', _('Sample Date'), ':</label>
				<input class="date" type="text" name="SampleDate" size="10" maxlength="10" value="', $_POST['SampleDate'], '" />
			</field>';

		echo '<field>
				<label for="Cert">', _('Use for Cert?'), ':</label>
				<select name="Cert">';
		if ($_POST['Cert'] == 1) {
			echo '<option selected="selected" value="1">', _('Yes'), '</option>';
		} else {
			echo '<option value="1">', _('Yes'), '</option>';
		}
		if ($_POST['Cert'] == 0) {
			echo '<option selected="selected" value="0">', _('No'), '</option>';
		} else {
			echo '<option value="0">', _('No'), '</option>';
		}
		echo '</select>
			</field>
		</fieldset>';

		echo '<div class="centre">
				<input type="submit" name="submit" value="', _('Enter Information'), '" />
			</div>
		</form>';

	} else { //end of if $SelectedSampleID only do the else when a new record is being entered
		$_POST['ProdSpecKey'] = '';
		$_POST['LotKey'] = '';
		$_POST['Identifier'] = '';
		$_POST['Comments'] = '';
		$_POST['Cert'] = 0;
		$_POST['DuplicateOK'] = 1;

		echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
		echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

		echo '<fieldset>
				<legend>', _('New Sample Information'), '</legend>';

		$SQLSpecSelect = "SELECT DISTINCT(keyval),
								description
							FROM prodspecs
							LEFT OUTER JOIN stockmaster
								ON stockmaster.stockid=prodspecs.keyval";
		$ResultSelection = DB_query($SQLSpecSelect);
		echo '<field>
				<label for="ProdSpecKey">', _('Specification'), ':</label>
				<select name="ProdSpecKey">';
		while ($MyRowSelection = DB_fetch_array($ResultSelection)) {
			echo '<option value="', $MyRowSelection['keyval'], '">', $MyRowSelection['keyval'], ' - ', htmlspecialchars($MyRowSelection['description'], ENT_QUOTES, 'UTF-8', false), '</option>';
		}
		echo '</select>
			</field>';

		echo '<field>
				<label for="LotKey">', _('Lot'), ':</label>
				<input type="text" name="LotKey" size="25" maxlength="25" value="', $_POST['LotKey'], '" />
			</field>';

		echo '<field>
				<label for="Identifier">', _('Identifier'), ':</label>
				<input type="text" name="Identifier" size="10" maxlength="10" value="', $_POST['Identifier'], '" />
			</field>';

		echo '<field>
				<label for="Comments">', _('Comments'), ':</label>
				<input type="text" name="Comments" size="30" maxlength="255" value="', $_POST['Comments'], '" />
			</field>';

		echo '<field>
				<label for="Cert">', _('Use for Cert?'), ':</label>
				<select name="Cert">';
		if ($_POST['Cert'] == 1) {
			echo '<option selected="selected" value="1">', _('Yes'), '</option>';
		} else {
			echo '<option value="1">', _('Yes'), '</option>';
		}
		if ($_POST['Cert'] == 0) {
			echo '<option selected="selected" value="0">', _('No'), '</option>';
		} else {
			echo '<option value="0">', _('No'), '</option>';
		}
		echo '</select>
			</field>';

		echo '<field>
				<label for="DuplicateOK">', _('Duplicate for Lot OK?'), ':</label>
				<select name="DuplicateOK">';
		if ($_POST['DuplicateOK'] == 1) {
			echo '<option selected="selected" value="1">', _('Yes'), '</option>';
		} else {
			echo '<option value="1">', _('Yes'), '</option>';
		}
		if ($_POST['DuplicateOK'] == 0) {
			echo '<option selected="selected" value="0">', _('No'), '</option>';
		} else {
			echo '<option value="0">', _('No'), '</option>';
		}
		echo '</select>
			</field>';

		echo '</fieldset>';

		echo '<div class="centre">
				<input type="submit" name="submit" value="', _('Enter Information'), '" />
			</div>
		</form>';
	}
} //end if record deleted no point displaying form to add record
include ('includes/footer.php');
?>