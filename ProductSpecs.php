<?php
include ('includes/session.php');
$Title = _('Product Specifications Maintenance');
$ViewTopic = 'QualityAssurance'; // Filename in ManualContents.php's TOC.
$BookMark = 'QA_ProdSpecs'; // Anchor's id in the manual's html document.
include ('includes/header.php');

if (isset($_GET['SelectedQATest'])) {
	$SelectedQATest = mb_strtoupper($_GET['SelectedQATest']);
} elseif (isset($_POST['SelectedQATest'])) {
	$SelectedQATest = mb_strtoupper($_POST['SelectedQATest']);
}
if (isset($_GET['KeyValue'])) {
	$KeyValue = mb_strtoupper($_GET['KeyValue']);
} elseif (isset($_POST['KeyValue'])) {
	$KeyValue = mb_strtoupper($_POST['KeyValue']);
}

if (!isset($_POST['RangeMin']) or $_POST['RangeMin'] == '') {
	$RangeMin = 'NULL';
} else {
	$RangeMin = "'" . $_POST['RangeMin'] . "'";
}
if (!isset($_POST['RangeMax']) or $_POST['RangeMax'] == '') {
	$RangeMax = 'NULL';
} else {
	$RangeMax = "'" . $_POST['RangeMax'] . "'";
}

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '
	</p>';

if (isset($_GET['CopySpec']) or isset($_POST['CopySpec'])) {
	if (!isset($_POST['CopyTo']) or $_POST['CopyTo'] == '') {
		echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
		echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

		echo '<fieldset>
				<legend>', _('Copy Criteria'), '</legend>
				<field>
					<label for="CopyTo">', _('Enter The Item, Fixed Asset or Template to Copy this Specification to'), ':</label>
					<input type="text" name="CopyTo" size="25" maxlength="25" />
				</field>
			</fieldset>';

		echo '<div class="centre">
					<input type="hidden" name="KeyValue" value="', $KeyValue, '" />
					<input type="submit" name="CopySpec" value="', _('Copy'), '" />
			</div>
		</form>';
		include ('includes/footer.php');
		exit;
	} else {
		$SQL = "INSERT IGNORE INTO prodspecs
							(keyval,
							testid,
							defaultvalue,
							targetvalue,
							rangemin,
							rangemax,
							showoncert,
							showonspec,
							showontestplan,
							active)
					SELECT '" . $_POST['CopyTo'] . "',
								testid,
								defaultvalue,
								targetvalue,
								rangemin,
								rangemax,
								showoncert,
								showonspec,
								showontestplan,
								active
					FROM prodspecs WHERE keyval='" . $KeyValue . "'";
		$Msg = _('A Product Specification has been copied to') . ' ' . $_POST['CopyTo'] . ' from ' . ' ' . $KeyValue;
		$ErrMsg = _('The insert of the Product Specification failed because');
		$DbgMsg = _('The SQL that was used and failed was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
		prnMsg($Msg, 'success');
		$KeyValue = $_POST['CopyTo'];
		unset($_GET['CopySpec']);
		unset($_POST['CopySpec']);
	} //else
	
} //CopySpec
if (!isset($KeyValue) or $KeyValue == '') {
	//prompt user for Key Value
	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<fieldset>
			<field>
				<label for="KeyValue">', _('Enter Specification Name'), ':</label>
				<input type="search" name="KeyValue" size="25" maxlength="25" />
			</field>
		</fieldset>';

	echo '<div>
			<input type="submit" name="pickspec" value="', _('Submit'), '" />
		</div>
	</form>';

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	$SQLSpecSelect = "SELECT DISTINCT(keyval),
							description
						FROM prodspecs LEFT OUTER JOIN stockmaster
						ON stockmaster.stockid=prodspecs.keyval";
	$ResultSelection = DB_query($SQLSpecSelect);
	echo '<fieldset>
			<field>
				<label for="KeyValue">', _('Or Select Existing Specification'), ':</label>
				<select name="KeyValue">';
	while ($MyRowSelection = DB_fetch_array($ResultSelection)) {
		echo '<option value="', $MyRowSelection['keyval'], '">', $MyRowSelection['keyval'], ' - ', htmlspecialchars($MyRowSelection['description'], ENT_QUOTES, 'UTF-8', false), '</option>';
	}
	echo '</select>
		</field>
	</fieldset>';

	echo '<div>
			<input type="submit" name="pickspec" value="', _('Submit'), '" />
		</div>
	</form>';

} else {
	//show header
	$SQLSpecSelect = "SELECT description
						FROM stockmaster
						WHERE stockmaster.stockid='" . $KeyValue . "'";

	$ResultSelection = DB_query($SQLSpecSelect);
	$MyRowSelection = DB_fetch_array($ResultSelection);
	echo '<div class="page_title_text">', _('Product Specification for'), ' ', $KeyValue, '-', $MyRowSelection['description'], '</div>';
}
if (isset($_GET['ListTests'])) {
	$SQL = "SELECT qatests.testid,
				name,
				method,
				units,
				type,
				numericvalue,
				qatests.defaultvalue
			FROM qatests
			LEFT JOIN prodspecs
			ON prodspecs.testid=qatests.testid
			AND prodspecs.keyval='" . $KeyValue . "'
			WHERE qatests.active='1'
			AND prodspecs.keyval IS NULL
			ORDER BY name";
	$Result = DB_query($SQL);
	echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<table>
			<thead>
				<tr>
					<th class="SortedColumn">', _('Add'), '</th>
					<th class="SortedColumn">', _('Name'), '</th>
					<th class="SortedColumn">', _('Method'), '</th>
					<th class="SortedColumn">', _('Units'), '</th>
					<th>', _('Possible Values'), '</th>
					<th>', _('Target Value'), '</th>
					<th>', _('Range Min'), '</th>
					<th>', _('Range Max'), '</th>
				</tr>
			</thead>';
	$k = 0;
	$x = 0;
	echo '<tbody>';
	while ($MyRow = DB_fetch_array($Result)) {
		++$x;
		$Class = '';
		$RangeMin = '';
		$RangeMax = '';
		if ($MyRow['numericvalue'] == 1) {
			$IsNumeric = _('Yes');
			$Class = "number";
		} else {
			$IsNumeric = _('No');
		}

		switch ($MyRow['type']) {
			case 0; //textbox
			$TypeDisp = _('Text Box');
		break;
		case 1; //select box
		$TypeDisp = _('Select Box');
	break;
	case 2; //checkbox
	$TypeDisp = _('Check Box');
break;
case 3; //datebox
$TypeDisp = _('Date Box');
$Class = "date";
break;
case 4; //range
$TypeDisp = _('Range');
$RangeMin = '<input  class="' . $Class . '" type="text" name="AddRangeMin' . $x . '" />';
$RangeMax = '<input  class="' . $Class . '" type="text" name="AddRangeMax' . $x . '" />';
break;
} //end switch
echo '<tr class="striped_row">
				<td><input type="checkbox" name="AddRow', $x, '"><input type="hidden" name="AddTestID', $x, '" value="', $MyRow['testid'], '"></td>
				<td>', $MyRow['name'], '</td>
				<td>', $MyRow['method'], '</td>
				<td>', $MyRow['units'], '</td>
				<td>', $MyRow['defaultvalue'], '</td>
				<td><input  class="', $Class, '" type="text" name="AddTargetValue', $x, '" /></td>
				<td>', $RangeMin, '</td>
				<td>', $RangeMax, '</td>
			</tr>';

} //END WHILE LIST LOOP
echo '</tbody>
		</table>';

echo '<div class="centre">
			<input type="hidden" name="KeyValue" value="', $KeyValue, '" />
			<input type="hidden" name="AddTestsCounter" value="', $x, '" />
			<input type="submit" name="AddTests" value="', _('Add'), '" />
		</div>
	</form>';
include ('includes/footer.php');
exit;
} //ListTests
if (isset($_POST['AddTests'])) {
	for ($i = 0;$i <= $_POST['AddTestsCounter'];$i++) {
		if (isset($_POST['AddRow' . $i]) and $_POST['AddRow' . $i] == 'on') {
			if ($_POST['AddRangeMin' . $i] == '') {
				$AddRangeMin = "NULL";
			} else {
				$AddRangeMin = "'" . $_POST['AddRangeMin' . $i] . "'";
			}
			if ($_POST['AddRangeMax' . $i] == '') {
				$AddRangeMax = "NULL";
			} else {
				$AddRangeMax = "'" . $_POST['AddRangeMax' . $i] . "'";
			}

			$SQL = "INSERT INTO prodspecs
							(keyval,
							testid,
							defaultvalue,
							targetvalue,
							rangemin,
							rangemax,
							showoncert,
							showonspec,
							showontestplan,
							active)
						SELECT '" . $KeyValue . "',
								testid,
								defaultvalue,
								'" . $_POST['AddTargetValue' . $i] . "',
								" . $AddRangeMin . ",
								" . $AddRangeMax . ",
								showoncert,
								showonspec,
								showontestplan,
								active
						FROM qatests WHERE testid='" . $_POST['AddTestID' . $i] . "'";
			$Msg = _('A Product Specification record has been added for Test ID') . ' ' . $_POST['AddTestID' . $i] . ' for ' . ' ' . $KeyValue;
			$ErrMsg = _('The insert of the Product Specification failed because');
			$DbgMsg = _('The SQL that was used and failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
			prnMsg($Msg, 'success');
		} //if on
		
	} //for
	
} //AddTests
if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	 ie the page has called itself with some user input */
	$i = 1;

	//first off validate inputs sensible
	if (isset($SelectedQATest) and $InputError != 1) {

		/*SelectedQATest could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$SQL = "UPDATE prodspecs SET defaultvalue='" . $_POST['DefaultValue'] . "',
									targetvalue='" . $_POST['TargetValue'] . "',
									rangemin=" . $RangeMin . ",
									rangemax=" . $RangeMax . ",
									showoncert='" . $_POST['ShowOnCert'] . "',
									showonspec='" . $_POST['ShowOnSpec'] . "',
									showontestplan='" . $_POST['ShowOnTestPlan'] . "',
									active='" . $_POST['Active'] . "'
				WHERE prodspecs.keyval = '" . $KeyValue . "'
				AND prodspecs.testid = '" . $SelectedQATest . "'";

		$Msg = _('Product Specification record for') . ' ' . $_POST['QATestName'] . ' for ' . ' ' . $KeyValue . _('has been updated');
		$ErrMsg = _('The update of the Product Specification failed because');
		$DbgMsg = _('The SQL that was used and failed was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

		prnMsg($Msg, 'success');

		unset($SelectedQATest);
		unset($_POST['DefaultValue']);
		unset($_POST['TargetValue']);
		unset($_POST['RangeMax']);
		unset($_POST['RangeMin']);
		unset($_POST['ShowOnCert']);
		unset($_POST['ShowOnSpec']);
		unset($_POST['Active']);
	}
} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button
	// PREVENT DELETES IF DEPENDENT RECORDS
	$SQL = "SELECT COUNT(*) FROM qasamples
			INNER JOIN sampleresults on sampleresults.sampleid=qasamples.sampleid AND sampleresults.testid='" . $SelectedQATest . "'
			WHERE qasamples.prodspeckey='" . $KeyValue . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		prnMsg(_('Cannot delete this Product Specification because there are test results tied to it'), 'error');
	} else {
		$SQL = "DELETE FROM prodspecs WHERE keyval='" . $KeyValue . "'
									AND testid='" . $SelectedQATest . "'";
		$ErrMsg = _('The Product Specification could not be deleted because');
		$Result = DB_query($SQL, $ErrMsg);

		prnMsg(_('Product Specification') . ' ' . $SelectedQATest . ' for ' . ' ' . $KeyValue . _('has been deleted from the database'), 'success');
		unset($SelectedQATest);
		unset($delete);
		unset($_GET['delete']);
	}
}

if (!isset($SelectedQATest) and isset($KeyValue)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedQATest will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of QA Test will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/

	$SQL = "SELECT prodspecs.testid,
				name,
				method,
				units,
				type,
				numericvalue,
				prodspecs.defaultvalue,
				prodspecs.targetvalue,
				prodspecs.rangemin,
				prodspecs.rangemax,
				prodspecs.showoncert,
				prodspecs.showonspec,
				prodspecs.showontestplan,
				prodspecs.active
			FROM prodspecs INNER JOIN qatests
			ON qatests.testid=prodspecs.testid
			WHERE prodspecs.keyval='" . $KeyValue . "'
			ORDER BY name";
	$Result = DB_query($SQL);

	echo '<table>
			<thead>
				<tr>
					<th class="SortedColumn">', _('Name'), '</th>
					<th class="SortedColumn">', _('Method'), '</th>
					<th class="SortedColumn">', _('Units'), '</th>
					<th class="SortedColumn">', _('Type'), '</th>
					<th>', _('Possible Values'), '</th>
					<th>', _('Target Value'), '</th>
					<th>', _('Range Min'), '</th>
					<th>', _('Range Max'), '</th>
					<th class="SortedColumn">', _('Show on Cert'), '</th>
					<th class="SortedColumn">', _('Show on Spec'), '</th>
					<th class="SortedColumn">', _('Show on Test Plan'), '</th>
					<th class="SortedColumn">', _('Active'), '</th>
					<th></th>
					<th></th>
				</tr>
			</thead>';
	$k = 0;
	echo '<tbody>';
	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['active'] == 1) {
			$ActiveText = _('Yes');
		} else {
			$ActiveText = _('No');
		}
		if ($MyRow['numericvalue'] == 1) {
			$IsNumeric = _('Yes');
			$Class = "number";
		} else {
			$IsNumeric = _('No');
		}
		if ($MyRow['showoncert'] == 1) {
			$ShowOnCertText = _('Yes');
		} else {
			$ShowOnCertText = _('No');
		}
		if ($MyRow['showonspec'] == 1) {
			$ShowOnSpecText = _('Yes');
		} else {
			$ShowOnSpecText = _('No');
		}
		if ($MyRow['showontestplan'] == 1) {
			$ShowOnTestPlanText = _('Yes');
		} else {
			$ShowOnTestPlanText = _('No');
		}
		switch ($MyRow['type']) {
			case 0; //textbox
			$TypeDisp = 'Text Box';
		break;
		case 1; //select box
		$TypeDisp = 'Select Box';
	break;
	case 2; //checkbox
	$TypeDisp = 'Check Box';
break;
case 3; //datebox
$TypeDisp = 'Date Box';
$Class = "date";
break;
case 4; //range
$TypeDisp = 'Range';
break;
} //end switch
echo '<tr class="striped_row">
				<td>', $MyRow['name'], '</td>
				<td>', $MyRow['method'], '</td>
				<td>', $MyRow['units'], '</td>
				<td>', $TypeDisp, '</td>
				<td>', $MyRow['defaultvalue'], '</td>
				<td>', $MyRow['targetvalue'], '</td>
				<td>', $MyRow['rangemin'], '</td>
				<td>', $MyRow['rangemax'], '</td>
				<td>', $ShowOnCertText, '</td>
				<td>', $ShowOnSpecText, '</td>
				<td>', $ShowOnTestPlanText, '</td>
				<td>', $ActiveText, '</td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedQATest=', urlencode($MyRow['testid']), '&amp;KeyValue=', urlencode($KeyValue), '">', _('Edit'), '</a></td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedQATest=', urlencode($MyRow['testid']), '&amp;KeyValue=', urlencode($KeyValue), '&amp;delete=1" onclick="return confirm(\'', _('Are you sure you wish to delete this Product Specification ?'), '\');">', _('Delete'), '</a></td>
			</tr>';

} //END WHILE LIST LOOP
echo '</tbody>
		</table>';
} //end of ifs and buts!
if (isset($SelectedQATest)) {
	echo '<div class="centre">
			<a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?KeyValue=', urlencode($KeyValue), '">', _('Show All Product Specs'), '</a>
		</div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	if (isset($SelectedQATest)) {
		//editing an existing Prod Spec
		$SQL = "SELECT prodspecs.testid,
						name,
						method,
						units,
						type,
						numericvalue,
						groupby,
						prodspecs.defaultvalue,
						prodspecs.targetvalue,
						prodspecs.rangemin,
						prodspecs.rangemax,
						prodspecs.showoncert,
						prodspecs.showonspec,
						prodspecs.showontestplan,
						prodspecs.active
				FROM prodspecs INNER JOIN qatests
				ON qatests.testid=prodspecs.testid
				WHERE prodspecs.keyval='" . $KeyValue . "'
				AND prodspecs.testid='" . $SelectedQATest . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['SelectedQATest'] = $MyRow['testid'];
		$_POST['QATestName'] = $MyRow['name'];
		$_POST['Method'] = $MyRow['method'];
		$_POST['GroupBy'] = $MyRow['groupby'];
		$_POST['Type'] = $MyRow['type'];
		$_POST['Units'] = $MyRow['units'];
		$_POST['DefaultValue'] = $MyRow['defaultvalue'];
		$_POST['NumericValue'] = $MyRow['numericvalue'];
		$_POST['TargetValue'] = $MyRow['targetvalue'];
		$_POST['RangeMin'] = $MyRow['rangemin'];
		$_POST['RangeMax'] = $MyRow['rangemax'];
		$_POST['ShowOnCert'] = $MyRow['showoncert'];
		$_POST['ShowOnSpec'] = $MyRow['showonspec'];
		$_POST['ShowOnTestPlan'] = $MyRow['showontestplan'];
		$_POST['Active'] = $MyRow['active'];

		echo '<input type="hidden" name="SelectedQATest" value="', $SelectedQATest, '" />';
		echo '<input type="hidden" name="KeyValue" value="', $KeyValue, '" />';
		echo '<input type="hidden" name="TestID" value="', $_POST['SelectedQATest'], '" />';
		echo '<input type="hidden" name="QATestName" value="', $_POST['QATestName'], '" />';
		echo '<fieldset>
				<legend>', _('Edit Test Details'), '</legend>
				<field>
					<label for="QATestName">', _('Test Name'), ':</label>
					<div class="fieldtext">', $_POST['QATestName'], '</div>
				</field>';

		if (!isset($_POST['Active'])) {
			$_POST['Active'] = 1;
		}
		if (!isset($_POST['ShowOnCert'])) {
			$_POST['ShowOnCert'] = 1;
		}
		if (!isset($_POST['ShowOnSpec'])) {
			$_POST['ShowOnSpec'] = 1;
		}
		if ($MyRow['numericvalue'] == 1) {
			$IsNumeric = _('Yes');
			$Class = "number";
		}
		switch ($MyRow['type']) {
			case 0; //textbox
			$TypeDisp = 'Text Box';
		break;
		case 1; //select box
		$TypeDisp = 'Select Box';
	break;
	case 2; //checkbox
	$TypeDisp = 'Check Box';
break;
case 3; //datebox
$TypeDisp = 'Date Box';
$Class = "date";
break;
case 4; //range
$TypeDisp = 'Range';
break;
} //end switch
if ($TypeDisp == 'Select Box') {
	echo '<field>
					<label for="DefaultValue">', _('Possible Values'), ':</label>
					<input type="text" name="DefaultValue" size="50" maxlength="150" value="', $_POST['DefaultValue'], '" />
				</field>';
} else {
	echo '<input type="hidden" name="DefaultValue" size="50" maxlength="150" value="" />';
}
echo '<field>
				<label for="TargetValue">', _('Target Value'), ':</label>
				<input type="text" class="', $Class, '" name="TargetValue" size="15" maxlength="15" value="', $_POST['TargetValue'], '" />&nbsp;', $_POST['Units'], '
			</field>';

if ($TypeDisp == 'Range') {
	echo '<field>
					<label for="RangeMin">', _('Range Min'), ':</label>
					<input class="', $Class, '" type="text" name="RangeMin" size="10" maxlength="10" value="', $_POST['RangeMin'], '" />
				</field>';
	echo '<field>
					<label for="RangeMax">', _('Range Max'), ':</label>
					<input class="', $Class, '" type="text" name="RangeMax" size="10" maxlength="10" value="', $_POST['RangeMax'], '" />
				</field>';
}
echo '<field>
				<label for="ShowOnCert">', _('Show On Cert?'), ':</label>
				<select name="ShowOnCert">';
if ($_POST['ShowOnCert'] == 1) {
	echo '<option selected="selected" value="1">', _('Yes'), '</option>';
} else {
	echo '<option value="1">' . _('Yes') . '</option>';
}
if ($_POST['ShowOnCert'] == 0) {
	echo '<option selected="selected" value="0">' . _('No') . '</option>';
} else {
	echo '<option value="0">' . _('No') . '</option>';
}
echo '</select>
			</field>';

echo '<field>
				<label for="ShowOnSpec">', _('Show On Spec?'), ':</label>
				<select name="ShowOnSpec">';
if ($_POST['ShowOnSpec'] == 1) {
	echo '<option selected="selected" value="1">', _('Yes'), '</option>';
} else {
	echo '<option value="1">', _('Yes'), '</option>';
}
if ($_POST['ShowOnSpec'] == 0) {
	echo '<option selected="selected" value="0">', _('No'), '</option>';
} else {
	echo '<option value="0">', _('No'), '</option>';
}
echo '</select>
			</field>';

echo '<field>
				<label for="ShowOnTestPlan">', _('Show On Test Plan?'), ':</label>
				<select name="ShowOnTestPlan">';
if ($_POST['ShowOnTestPlan'] == 1) {
	echo '<option selected="selected" value="1">', _('Yes'), '</option>';
} else {
	echo '<option value="1">', _('Yes'), '</option>';
}
if ($_POST['ShowOnTestPlan'] == 0) {
	echo '<option selected="selected" value="0">', _('No'), '</option>';
} else {
	echo '<option value="0">', _('No'), '</option>';
}
echo '</select>
			</field>';

echo '<field>
				<label for="Active">', _('Active?'), ':</label>
				<select name="Active">';
if ($_POST['Active'] == 1) {
	echo '<option selected="selected" value="1">', _('Yes'), '</option>';
} else {
	echo '<option value="1">', _('Yes'), '</option>';
}
if ($_POST['Active'] == 0) {
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
if (isset($KeyValue)) {
	echo '<div class="centre">
				<a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?ListTests=yes&amp;KeyValue=', $KeyValue, '">', _('Add More Tests'), '</a><br />
				<a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?CopySpec=yes&amp;KeyValue=', $KeyValue, '">', _('Copy This Specification'), '</a><br />
				<a target="_blank" href="', $RootPath, '/PDFProdSpec.php?KeyValue=', $KeyValue, '">', _('Print Product Specification'), '</a><br />
				<a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">', _('Product Specification Main Page'), '</a>
			</div>';
}
} //end if record deleted no point displaying form to add record
include ('includes/footer.php');
?>