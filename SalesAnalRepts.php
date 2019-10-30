<?php
include ('includes/session.php');

$Title = _('Sales Analysis Reports Maintenance');
/* Manual links before header.php */
$ViewTopic = 'SalesAnalysis';
$BookMark = 'SalesAnalysis';

include ('includes/header.php');

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/reports.png" data-title="', $Title, '" alt="" />', ' ', $Title, '
	</p>';

function GrpByDataOptions($GroupByDataX) {

	/*Sales analysis headers group by data options */
	if ($GroupByDataX == 'Sales Area') {
		echo '<option selected="selected" value="Sales Area">', _('Sales Area'), '</option>';
	} else {
		echo '<option value="Sales Area">', _('Sales Area'), '</option>';
	}
	if ($GroupByDataX == 'Product Code') {
		echo '<option selected="selected" value="Product Code">', _('Product Code'), '</option>';
	} else {
		echo '<option value="Product Code">', _('Product Code'), '</option>';
	}
	if ($GroupByDataX == 'Customer Code') {
		echo '<option selected="selected" value="Customer Code">', _('Customer Code'), '</option>';
	} else {
		echo '<option value="Customer Code">', _('Customer Code'), '</option>';
	}
	if ($GroupByDataX == 'Sales Type') {
		echo '<option selected="selected" value="Sales Type">', _('Sales Type'), '</option>';
	} else {
		echo '<option value="Sales Type">', _('Sales Type'), '</option>';
	}
	if ($GroupByDataX == 'Product Type') {
		echo '<option selected="selected" value="Product Type">', _('Product Type'), '</option>';
	} else {
		echo '<option value="Product Type">', _('Product Type'), '</option>';
	}
	if ($GroupByDataX == 'Customer Branch') {
		echo '<option selected="selected" value="Customer Branch">', _('Customer Branch'), '</option>';
	} else {
		echo '<option value="Customer Branch">', _('Customer Branch'), '</option>';
	}
	if ($GroupByDataX == 'Sales Person') {
		echo '<option selected="selected" value="Sales Person">', _('Sales Person'), '</option>';
	} else {
		echo '<option value="Sales Person">', _('Sales Person'), '</option>';
	}
	if ($GroupByDataX == 'Not Used' or $GroupByDataX == '' or !isset($GroupByDataX) or is_null($GroupByDataX)) {
		echo '<option selected="selected" value="Not Used">', _('Not Used'), '</option>';
	} else {
		echo '<option value="Not Used">', _('Not Used'), '</option>';
	}
}

/* end of function  */

if (isset($_GET['SelectedReport'])) {
	$SelectedReport = $_GET['SelectedReport'];
} elseif (isset($_POST['SelectedReport'])) {
	$SelectedReport = $_POST['SelectedReport'];
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	 ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (mb_strlen($_POST['ReportHeading']) < 2) {
		$InputError = 1;
		prnMsg(_('The report heading must be more than two characters long') . '. ' . _('No report heading was entered'), 'error', _('Heading too long'));
	}
	if ($_POST['GroupByData1'] == '' or !isset($_POST['GroupByData1']) or $_POST['GroupByData1'] == 'Not Used') {
		$InputError = 1;
		prnMsg(_('A group by item must be specified for the report to have any output'), 'error', _('No Group By selected'));
	}
	if ($_POST['GroupByData3'] == 'Not Used' and $_POST['GroupByData4'] != 'Not Used') {
		// If GroupByData3 is blank but GroupByData4 is used then move GroupByData3 to GroupByData2
		$_POST['GroupByData3'] = $_POST['GroupByData4'];
		$_POST['Lower3'] = $_POST['Lower4'];
		$_POST['Upper3'] = $_POST['Upper4'];
	}
	if ($_POST['GroupByData2'] == 'Not Used' and $_POST['GroupByData3'] != 'Not Used') {
		/*If GroupByData2 is blank but GroupByData3 is used then move GroupByData3 to GroupByData2 */
		$_POST['GroupByData2'] = $_POST['GroupByData3'];
		$_POST['Lower2'] = $_POST['Lower3'];
		$_POST['Upper2'] = $_POST['Upper3'];
	}
	if (($_POST['Lower1'] == '' or $_POST['Upper1'] == '')) {
		$InputError = 1;
		prnMsg(_('Group by Level 1 is set but the upper and lower limits are not set') . ' - ' . _('these must be specified for the report to have any output'), 'error', _('Upper/Lower limits not set'));
	}
	if (($_POST['GroupByData2'] != 'Not Used') and ($_POST['Lower2'] == '' or $_POST['Upper2'] == '')) {
		$InputError = 1;
		prnMsg(_('Group by Level 2 is set but the upper and lower limits are not set') . ' - ' . _('these must be specified for the report to have any output'), 'error', _('Upper/Lower Limits not set'));
	}
	if (($_POST['GroupByData3'] != 'Not Used') and ($_POST['Lower3'] == '' or $_POST['Upper3'] == '')) {
		$InputError = 1;
		prnMsg(_('Group by Level 3 is set but the upper and lower limits are not set') . ' - ' . _('these must be specified for the report to have any output'), 'error', _('Upper/Lower Limits not set'));
	}
	if (($_POST['GroupByData4'] != 'Not Used') and ($_POST['Lower4'] == '' or $_POST['Upper4'] == '')) {
		$InputError = 1;
		prnMsg(_('Group by Level 4 is set but the upper and lower limits are not set') . ' - ' . _('these must be specified for the report to have any output'), 'error', _('Upper/Lower Limits not set'));
	}
	if ($_POST['GroupByData1'] != 'Not Used' and $_POST['Lower1'] > $_POST['Upper1']) {
		$InputError = 1;
		prnMsg(_('Group by Level 1 is set but the lower limit is greater than the upper limit') . ' - ' . _('the report will have no output'), 'error', _('Lower Limit greater than Upper Limit'));
	}
	if ($_POST['GroupByData2'] != 'Not Used' and $_POST['Lower2'] > $_POST['Upper2']) {
		$InputError = 1;
		prnMsg(_('Group by Level 2 is set but the lower limit is greater than the upper limit') . ' - ' . _('the report will have no output'), 'error', _('Lower Limit greater than Upper Limit'));
	}
	if ($_POST['GroupByData3'] != 'Not Used' and $_POST['Lower3'] > $_POST['Upper3']) {
		$InputError = 1;
		prnMsg(_('Group by Level 3 is set but the lower limit is greater than the upper limit') . ' - ' . _('the report will have no output'), 'error', _('Lower Limit greater than Upper Limit'));
	}
	if ($_POST['GroupByData4'] != 'Not Used' and $_POST['Lower4'] > $_POST['Upper4']) {
		$InputError = 1;
		prnMsg(_('Group by Level 4 is set but the lower limit is greater than the upper limit') . ' - ' . _('the report will have no output'), 'error', _('Lower Limit greater than Upper Limit'));
	}

	if (isset($SelectedReport) and $InputError != 1) {

		/*SelectedReport could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$SQL = "UPDATE reportheaders SET reportheading='" . $_POST['ReportHeading'] . "',
										groupbydata1='" . $_POST['GroupByData1'] . "',
										groupbydata2='" . $_POST['GroupByData2'] . "',
										groupbydata3='" . $_POST['GroupByData3'] . "',
										groupbydata4='" . $_POST['GroupByData4'] . "',
										newpageafter1='" . $_POST['NewPageAfter1'] . "',
										newpageafter2='" . $_POST['NewPageAfter2'] . "',
										newpageafter3='" . $_POST['NewPageAfter3'] . "',
										lower1='" . filter_number_format($_POST['Lower1']) . "',
										upper1='" . filter_number_format($_POST['Upper1']) . "',
										lower2='" . filter_number_format($_POST['Lower2']) . "',
										upper2='" . filter_number_format($_POST['Upper2']) . "',
										lower3='" . filter_number_format($_POST['Lower3']) . "',
										upper3='" . filter_number_format($_POST['Upper3']) . "',
										lower4='" . filter_number_format($_POST['Lower4']) . "',
										upper4='" . filter_number_format($_POST['Upper4']) . "'
									WHERE reportid = " . $SelectedReport;

		$ErrMsg = _('The report could not be updated because');
		$DbgMsg = _('The SQL used to update the report headers was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

		prnMsg(_('The') . ' ' . $_POST['ReportHeading'] . ' ' . _('report has been updated'), 'success', 'Report Updated');
		unset($SelectedReport);
		unset($_POST['ReportHeading']);
		unset($_POST['GroupByData1']);
		unset($_POST['GroupByData2']);
		unset($_POST['GroupByData3']);
		unset($_POST['GroupByData4']);
		unset($_POST['NewPageAfter1']);
		unset($_POST['NewPageAfter2']);
		unset($_POST['NewPageAfter3']);
		unset($_POST['Lower1']);
		unset($_POST['Upper1']);
		unset($_POST['Lower2']);
		unset($_POST['Upper2']);
		unset($_POST['Lower3']);
		unset($_POST['Upper3']);
		unset($_POST['Lower4']);
		unset($_POST['Upper4']);

	} elseif ($InputError != 1) {

		/*SelectedReport is null cos no item selected on first time round so must be adding a new report */

		$SQL = "INSERT INTO reportheaders ( reportheading,
											groupbydata1,
											groupbydata2,
											groupbydata3,
											groupbydata4,
											newpageafter1,
											newpageafter2,
											newpageafter3,
											lower1,
											upper1,
											lower2,
											upper2,
											lower3,
											upper3,
											lower4,
											upper4
										) VALUES (
											'" . $_POST['ReportHeading'] . "',
											'" . $_POST['GroupByData1'] . "',
											'" . $_POST['GroupByData2'] . "',
											'" . $_POST['GroupByData3'] . "',
											'" . $_POST['GroupByData4'] . "',
											'" . $_POST['NewPageAfter1'] . "',
											'" . $_POST['NewPageAfter2'] . "',
											'" . $_POST['NewPageAfter3'] . "',
											'" . filter_number_format($_POST['Lower1']) . "',
											'" . filter_number_format($_POST['Upper1']) . "',
											'" . filter_number_format($_POST['Lower2']) . "',
											'" . filter_number_format($_POST['Upper2']) . "',
											'" . filter_number_format($_POST['Lower3']) . "',
											'" . filter_number_format($_POST['Upper3']) . "',
											'" . filter_number_format($_POST['Lower4']) . "',
											'" . filter_number_format($_POST['Upper4']) . "'
										)";

		$ErrMsg = _('The report could not be added because');
		$DbgMsg = _('The SQL used to add the report header was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

		prnMsg(_('The') . ' ' . $_POST['ReportHeading'] . ' ' . _('report has been added to the database'), 'success', 'Report Added');

		unset($SelectedReport);
		unset($_POST['ReportHeading']);
		unset($_POST['GroupByData1']);
		unset($_POST['GroupByData2']);
		unset($_POST['GroupByData3']);
		unset($_POST['GroupByData4']);
		unset($_POST['NewPageAfter1']);
		unset($_POST['NewPageAfter2']);
		unset($_POST['NewPageAfter3']);
		unset($_POST['Lower1']);
		unset($_POST['Upper1']);
		unset($_POST['Lower2']);
		unset($_POST['Upper2']);
		unset($_POST['Lower3']);
		unset($_POST['Upper3']);
		unset($_POST['Lower4']);
		unset($_POST['Upper4']);

	}

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button
	$SQL = "DELETE FROM reportcolumns WHERE reportid='" . $SelectedReport . "'";
	$ErrMsg = _('The deletion of the report column failed because');
	$DbgMsg = _('The SQL used to delete the report column was');

	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

	$SQL = "DELETE FROM reportheaders WHERE reportid='" . $SelectedReport . "'";
	$ErrMsg = _('The deletion of the report heading failed because');
	$DbgMsg = _('The SQL used to delete the report headers was');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

	prnMsg(_('Report Deleted'), 'info');
	unset($SelectedReport);
	include ('includes/footer.php');
	exit;

}

if (!isset($SelectedReport)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedReport will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of Reports will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/

	$Result = DB_query("SELECT reportid, reportheading FROM reportheaders ORDER BY reportid");

	if (DB_num_rows($Result) > 0) {
		echo '<table>';
		echo '<tr>
			<th>', _('Report No'), '</th>
			<th>', _('Report Title'), '</th>
		  </tr>';

		while ($MyRow = DB_fetch_array($Result)) {

			echo '<tr class="striped_row">
				<td>', $MyRow[0], '</td>
				<td>', $MyRow[1], '</td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?&amp;SelectedReport=', urlencode($MyRow[0]), '">', _('Design'), '</a></td>
				<td><a href="', $RootPath, '/SalesAnalReptCols.php?ReportID=', urlencode($MyRow[0]), '">', _('Define Columns'), '</a></td>
				<td><a href="', $RootPath, '/SalesAnalysis_UserDefined.php?ReportID=', urlencode($MyRow[0]), '&amp;ProducePDF=True">', _('Make PDF Report'), '</a></td>
				<td><a href="', $RootPath, '/SalesAnalysis_UserDefined.php?ReportID=', urlencode($MyRow[0]), '&amp;ProduceCVSFile=True">', _('Make CSV File'), '</a></td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?amp;SelectedReport=', urlencode($MyRow[0]), '&amp;delete=1" onclick="return MakeConfirm(\'', _('Are you sure you wish to remove this report design?'), '\', \'Confirm Delete\', this);">', _('Delete'), '</a></td>
			</tr>';

		}
		//END WHILE LIST LOOP
		echo '</table>';
	} else {
		prnMsg(_('No sales analysis reports have been created'), 'info');
	}
}

//end of ifs and buts!


if (isset($SelectedReport)) {
	echo '<a href="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '">' . _('Show All Defined Reports') . '</a>';
}

if (!isset($_GET['delete'])) {
	echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	if (isset($SelectedReport)) {
		//editing an existing Report
		$SQL = "SELECT reportid,
						reportheading,
						groupbydata1,
						newpageafter1,
						upper1,
						lower1,
						groupbydata2,
						newpageafter2,
						upper2,
						lower2,
						groupbydata3,
						upper3,
						lower3,
						newpageafter3,
						groupbydata4,
						upper4,
						lower4
				FROM reportheaders
				WHERE reportid='" . $SelectedReport . "'";

		$ErrMsg = _('The reports for display could not be retrieved because');
		$DbgMsg = _('The SQL used to retrieve the report headers was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

		$MyRow = DB_fetch_array($Result);

		$ReportID = $MyRow['reportid'];
		$_POST['ReportHeading'] = $MyRow['reportheading'];
		$_POST['GroupByData1'] = $MyRow['groupbydata1'];
		$_POST['NewPageAfter1'] = $MyRow['newpageafter1'];
		$_POST['Upper1'] = $MyRow['upper1'];
		$_POST['Lower1'] = $MyRow['lower1'];
		$_POST['GroupByData2'] = $MyRow['groupbydata2'];
		$_POST['NewPageAfter2'] = $MyRow['newpageafter2'];
		$_POST['Upper2'] = $MyRow['upper2'];
		$_POST['Lower2'] = $MyRow['lower2'];
		$_POST['GroupByData3'] = $MyRow['groupbydata3'];
		$_POST['Upper3'] = $MyRow['upper3'];
		$_POST['Lower3'] = $MyRow['lower3'];
		$_POST['GroupByData4'] = $MyRow['groupbydata4'];
		$_POST['Upper4'] = $MyRow['upper4'];
		$_POST['Lower4'] = $MyRow['lower4'];

		echo '<input type="hidden" name="SelectedReport" value="', $SelectedReport, '" />';
		echo '<input type="hidden" name="ReportID" value="', $ReportID, '" />';
		echo '<fieldset>
				<legend>', _('Edit The Selected Report') . '</legend>';
	} else {
		echo '<fieldset>
				<legend>', _('Define A New Report'), '</legend>';
	}

	if (!isset($_POST['ReportHeading'])) {
		$_POST['ReportHeading'] = '';
	}
	echo '<field>
			<label for="ReportHeading">', _('Report Heading'), ':</label>
			<input type="text" size="80" maxlength="80" name="ReportHeading" value="', $_POST['ReportHeading'], '" />
		</field>';

	echo '<fieldset>';

	echo '<field>
			<label for="GroupByData1">', _('Group By 1'), ':</label>
			<select name="GroupByData1">';
	if (!isset($_POST['GroupByData1'])) {
		$_POST['GroupByData1'] = 'Not Used';
	}
	GrpByDataOptions($_POST['GroupByData1']);
	echo '</select>
		</field>';

	echo '<field>
			<label for="GroupByData2">', _('Group By 2'), ':</label>
			<select name="GroupByData2">';
	if (!isset($_POST['GroupByData2'])) {
		$_POST['GroupByData2'] = 'Not Used';
	}
	GrpByDataOptions($_POST['GroupByData2']);
	echo '</select>
		</field>';

	echo '<field>
			<label for="GroupByData3">', _('Group By 3'), ':</label>
			<select name="GroupByData3">';
	if (!isset($_POST['GroupByData3'])) {
		$_POST['GroupByData3'] = 'Not Used';
	}
	GrpByDataOptions($_POST['GroupByData3']);
	echo '</select>
		</field>';

	echo '<field>
			<label for="GroupByData4">', _('Group By 4'), '</label>
			<select name="GroupByData4">';
	if (!isset($_POST['GroupByData4'])) {
		$_POST['GroupByData4'] = 'Not Used';
	}
	GrpByDataOptions($_POST['GroupByData4']);
	echo '</select>
		</field>';

	echo '</fieldset>';

	echo '<fieldset>';

	echo '<field>
			<label for="NewPageAfter1">', _('Page Break After'), ':</label> <select name="NewPageAfter1">';
	if (isset($_POST['NewPageAfter1']) and $_POST['NewPageAfter1'] == 0) {
		echo '<option selected="selected" value="0">', _('No'), '</option>';
		echo '<option value="1">', _('Yes'), '</option>';
	} else {
		echo '<option value="0">', _('No'), '</option>';
		echo '<option selected="selected" value="1">', _('Yes'), '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="NewPageAfter2">', _('Page Break After'), ':</label> <select name="NewPageAfter2">';
	if (isset($_POST['NewPageAfter2']) and $_POST['NewPageAfter2'] == 0) {
		echo '<option selected="selected" value="0">', _('No'), '</option>';
		echo '<option value="1">', _('Yes'), '</option>';
	} else {
		echo '<option value="0">', _('No'), '</option>';
		echo '<option selected="selected" value="1">', _('Yes'), '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="NewPageAfter3">', _('Page Break After'), ':</label> <select name="NewPageAfter3">';
	if (isset($_POST['NewPageAfter3']) and $_POST['NewPageAfter3'] == 0) {
		echo '<option selected="selected" value="0">', _('No'), '</option>';
		echo '<option value="1">', _('Yes'), '</option>';
	} else {
		echo '<option value="0">', _('No'), '</option>';
		echo '<option selected="selected" value="1">', _('Yes'), '</option>';
	}
	echo '</select>
		</field>';

	echo '</fieldset>';

	echo '<fieldset>';
	for ($i = 1;$i <= 4;$i++) {
		if (!isset($_POST['Lower' . $i])) {
			$_POST['Lower' . $i] = '';
		}
		if (!isset($_POST['Upper' . $i])) {
			$_POST['Upper' . $i] = '';
		}
		echo '<field>
				<td>', _('From'), ': <input type="text" name="Lower', $i, '" size="10" maxlength="10" value="', $_POST['Lower' . $i], '" /></td>
				<td>', _('To'), ': <input type="text" name="Upper', $i, '" size="10" maxlength="10" value="', $_POST['Upper' . $i], '" /></td>
			</field>';
	}

	echo '</fieldset>';
	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="submit" value="', _('Enter Information'), '" />
		</div>
	</form>';

} //end if record deleted no point displaying form to add record
include ('includes/footer.php');
?>