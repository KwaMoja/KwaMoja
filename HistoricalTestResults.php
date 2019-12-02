<?php
include ('includes/session.php');
$Title = _('Historical Test Results');
$ViewTopic = 'QualityAssurance'; // Filename in ManualContents.php's TOC.
$BookMark = 'QA_HistoricalResults'; // Anchor's id in the manual's html document.
include ('includes/header.php');

if (isset($_GET['KeyValue'])) {
	$KeyValue = mb_strtoupper($_GET['KeyValue']);
} elseif (isset($_POST['KeyValue'])) {
	$KeyValue = mb_strtoupper($_POST['KeyValue']);
} else {
	$KeyValue = '';
}

if (!isset($_POST['FromDate'])) {
	$_POST['FromDate'] = Date(($_SESSION['DefaultDateFormat']), strtotime(' - 180 days'));
}
if (!isset($_POST['ToDate'])) {
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}
if (!is_Date($_POST['FromDate'])) {
	$InputError = 1;
	prnMsg(_('Invalid From Date'), 'error');
	$_POST['FromDate'] = Date(($_SESSION['DefaultDateFormat']), strtotime(' - 180 days'));
}
if (!is_Date($_POST['ToDate'])) {
	$InputError = 1;
	prnMsg(_('Invalid To Date'), 'error');
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}
$FromDate = FormatDateForSQL($_POST['FromDate']);
$ToDate = FormatDateForSQL($_POST['ToDate']);

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/reports.png" title="', _('Search'), '" alt="" />', ' ', $Title, '
	</p>';

//prompt user for Key Value
echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

echo '<fieldset>
		<legend>', _('Inquiry Criteria'), '</legend>';

$SQLSpecSelect = "SELECT DISTINCT(prodspeckey),
						description
					FROM qasamples LEFT OUTER JOIN stockmaster
					ON stockmaster.stockid=qasamples.prodspeckey";

$ResultSelection = DB_query($SQLSpecSelect);
echo '<field>
		<label for="KeyValue">', _('Show Test Results For'), ':</label>
		<select name="KeyValue">';
while ($MyRowSelection = DB_fetch_array($ResultSelection)) {
	if ($MyRowSelection['prodspeckey'] == $KeyValue) {
		$Selected = ' selected="selected" ';
	} else {
		$Selected = '';
	}
	echo '<option', $Selected, ' value="', $MyRowSelection['prodspeckey'], '">', $MyRowSelection['prodspeckey'], ' - ', htmlspecialchars($MyRowSelection['description'], ENT_QUOTES, 'UTF-8', false), '</option>';
}
echo '</select>
	</field>';

echo '<field>
		<label for="FromDate">', _('From Sample Date'), ':</label>
		<input name="FromDate" size="10" class="date" value="', $_POST['FromDate'], '"/>
	</field>';

echo '<field>
		<label for="ToDate"> ', _('To Sample Date'), ':</label>
		<input name="ToDate" size="10" class="date" value="', $_POST['ToDate'], '"/>
	</field>
</fieldset>';

echo '<div class="centre">
		<input type="submit" name="pickspec" value="', _('Submit'), '" />
	</div>
</form>';

if (isset($KeyValue)) {
	//show header
	$SQLSpecSelect = "SELECT description
						FROM stockmaster
						WHERE stockmaster.stockid='" . $KeyValue . "'";

	$ResultSelection = DB_query($SQLSpecSelect);
	$MyRowSelection = DB_fetch_array($ResultSelection);
	$SQLTests = "SELECT sampleresults.testid,
					sampledate,
					sampleresults.sampleid,
					lotkey,
					identifier,
					cert,
					isinspec,
					testvalue,
					name
				FROM qasamples
				INNER JOIN sampleresults
					ON sampleresults.sampleid=qasamples.sampleid
				INNER JOIN qatests
					ON qatests.testid=sampleresults.testid
				WHERE qasamples.prodspeckey='" . $KeyValue . "'
					AND sampleresults.showontestplan='1'
					AND sampledate>='" . $FromDate . "'
					AND sampledate <='" . $ToDate . "'";

	$TestResult = DB_query($SQLTests);
	$TestsArray = array();
	$SamplesArray = array();
	$AllResultsArray = array();
	$TotResults = 0;
	while ($MyTestRow = DB_fetch_array($TestResult)) {
		$FormattedSampleID = str_pad($MyTestRow['sampleid'], 10, '0', STR_PAD_LEFT);
		$TestKey = array_search($MyTestRow['name'], $TestsArray);
		if ($TestKey === false) {
			$TestsArray[$MyTestRow['name']] = $MyTestRow['name'];
		}

		$TestKey = array_search($MyTestRow['sampleid'], $SamplesArray);
		if ($TestKey === false) {
			$SamplesArray[$FormattedSampleID] = $MyTestRow;
			++$TotResults;
		}
		$AllResultsArray[$MyTestRow['name']][$FormattedSampleID] = $MyTestRow;
	}

	if ($TotResults > 0) {
		echo '<table>
				<tr>
					<th colspan="2">', _('Historical Test Results for'), ' ', $KeyValue, '-', $MyRowSelection['description'], '</th>
				</tr>
				<tr>
					<th>', _('Sample ID:'), '<br>', _('Lot/Serial:'), '<br>', _('Identifier:'), '<br>', _('Sample Date:'), '</th>';
		foreach ($SamplesArray as $SampleKey => $SampleValue) {
			echo '<th>', $SampleKey, '<br>', $SampleValue['lotkey'], '<br>', $SampleValue['identifier'], '<br>', ConvertSQLDate($SampleValue['sampledate']), '</th>';
		}
		echo '</tr>';
		foreach ($TestsArray as $TestKey => $TestValue) {
			echo '<tr class="striped_row"><td>', $TestValue, '</td>';
			foreach ($SamplesArray as $SampleKey => $SampleValue) {
				if ($AllResultsArray[$TestKey][$SampleKey]['testvalue'] == '' or !isset($AllResultsArray[$TestKey][$SampleKey]['testvalue'])) {
					$AllResultsArray[$TestKey][$SampleKey]['testvalue'] = '&nbsp;';
				}
				echo '<td>', $AllResultsArray[$TestKey][$SampleKey]['testvalue'], '</td>';
			}
			echo '</tr>';
		}
		echo '</table>';
	} else {
		prnMsg(_('There are no test results meeting this criteria'), 'info');
	}
}
include ('includes/footer.php');
?>