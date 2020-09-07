<?php
include ('includes/session.php');
$Title = _('Enter Bacteriology Test Findings');
$ViewTopic = '';
$BookMark = '';
include ('includes/header.php');

if (isset($_POST['Batch'])) {
	$SelectedBatch = $_POST['Batch'];
} else if (isset($_GET['Batch'])) {
	$SelectedBatch = $_GET['Batch'];
} else {
	prnMsg(_('You must first select a batch of tests to view'), 'warn');
	echo '<div class="centre">
			<a href="', $RootPath, '/KCMCPendingBacteriologyTests.php>', _('Select a batch to view'), '</a>
		</div>';
	include ('includes/footer.php');
	exit;
}

include ('includes/SQL_CommonFunctions.php');
include ('includes/HospitalFunctions.php');

if (isset($SelectedBatch)) {
	$HeaderSQL = "SELECT `encounter_nr`,
						`dept_nr`,
						`material_note`,
						`diagnosis_note`,
						`immune_supp`,
						`sample_date`,
						`history`
					FROM care_test_request_baclabor
					WHERE batch_nr='" . $SelectedBatch . "'";
	$HeaderResult = DB_query($HeaderSQL);
	$HeaderRow = DB_fetch_array($HeaderResult);

	$Encounter = $HeaderRow['encounter_nr'];
	$SelectedPatient = GetPIDFromEncounter($Encounter);

	$Department = $HeaderRow['dept_nr'];
	$MaterialNote = $HeaderRow['material_note'];
	$DiagnosisNote = $HeaderRow['diagnosis_note'];
	$ImmuneSupp = $HeaderRow['immune_supp'];
	$SampleDate = $HeaderRow['sample_date'];

	$History = $HeaderRow['history'];

	$LinesSQL = "SELECT sub_id,
						encounter_nr,
						test_type,
						test_type_value,
						material,
						material_value
					FROM care_test_request_baclabor_sub
					WHERE batch_nr='" . $SelectedBatch . "'";
	$LinesResult = DB_query($LinesSQL);
	while ($LinesRow = DB_fetch_array($LinesResult)) {
		if ($LinesRow['test_type_value'] != 0) {
			$TestArray[] = $LinesRow['test_type'];
		} elseif ($LinesRow['material_value'] != 0) {
			$MaterialArray[] = $LinesRow['material'];
		}
	}
	$SQL = "SELECT encounter_nr
			FROM care_test_request_baclabor
			WHERE batch_nr='" . $SelectedBatch . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$Encounter = $MyRow['encounter_nr'];
	$PID = GetPIDFromEncounter($Encounter);

	$SQL = "SELECT pid,
					hospital_file_nr,
					name_first,
					name_last,
					phone_1_nr
				FROM care_person
				WHERE pid='" . $PID . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$PatientName = $MyRow['name_first'] . ' ' . $MyRow['name_last'];
	$PhoneNo = $MyRow['phone_1_nr'];
	echo '<p class="page_title_text">
			<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/labtest.png" title="', _('Enter Bacteriology Test Findings'), '" /> ', _('Enter Bacteriology Test Findings'), ' ', _('For'), ' ', $PatientName, ' (', _('PID'), ' - ', $PID, ') - ', _('Batch Number'), ' ', $SelectedBatch, '
		</p>';
} else {
	echo '<p class="page_title_text">
			<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images-medical/labtest.png" title="', _('Enter Bacteriology Test Findings'), '" /> ', _('Enter Bacteriology Test Findings'), '
		</p>';
}

echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?identifier=', $Identifier, '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
echo '<input type="hidden" name="SelectedPatient" value="', $SelectedPatient, '" />';

$SQL = "SELECT type, name FROM care_baclabor_material_type";
$Result = DB_query($SQL);

echo '<div class="baclab_container centre">';

echo '<fieldset class="material">
		<legend>', _('Material'), '</legend>';

while ($MyRow = DB_fetch_array($Result)) {
	if (in_array($MyRow['type'], $MaterialArray)) {
		$Checked = ' checked="checked" ';
	} else {
		$Checked = '';
	}
	echo '<field>
			<label class="container" for="', $MyRow['type'], '" onclick="">', _($MyRow['name']), '
				<input type="checkbox" ', $Checked, ' id="material', $MyRow['type'], '" name="material', $MyRow['type'], '" />
				<span class="checkmark"></span>
			</label>
		</field>';
}

echo '</fieldset>';

$SQL = "SELECT type, name FROM care_baclabor_test_type";
$Result = DB_query($SQL);
echo '<fieldset class="test">
		<legend>', _('Requested Tests'), '</legend>';

while ($MyRow = DB_fetch_array($Result)) {
	if (in_array($MyRow['type'], $TestArray)) {
		$Checked = ' checked="checked" ';
	} else {
		$Checked = '';
	}
	echo '<field>
				<label class="container" for="', $MyRow['type'], '" onclick="">', _($MyRow['name']), '
					<input type="checkbox" ', $Checked, ' id="test', $MyRow['type'], '" name="test', $MyRow['type'], '" />
					<span class="checkmark"></span>
				</label>
			</field>';
}

echo '</fieldset>';

echo '<div class="label">
		<fieldset>
			<legend>', _('Label'), '</legend>';
include ('includes/KCMCDrawPatientLabel.php');
echo '</fieldset>
	</div>';

echo '<div class="batch_details">
		<fieldset>';

echo '<field>
		<label>', _('Material Note'), '</label>
		<input type="text" size="50" readonly="readonly" value="', $HeaderRow['material_note'], '" />
	</field>';

echo '<field>
		<label>', _('Diagnosis Note'), '</label>
		<input type="text" size="50" readonly="readonly" value="', $HeaderRow['diagnosis_note'], '" />
	</field>';

echo '<field>
		<label for="Status">', _('Status'), '</label>
		<select name="Status">
			<option value="0">', _('Initial'), '</option>
			<option value="1">', _('Current'), '</option>
			<option value="2">', _('Final'), '</option>
		</select>
	</field>';

if ($HeaderRow['immune_supp'] == 0) {
	$ImmuneSuppressed = _('No');
} else {
	$ImmuneSuppressed = _('Yes');
}
echo '<field>
		<label for="ImmuneSupp">', _('Immune Suppressed'), '</label>
		<input type="text" size="10" readonly="readonly" value="', $ImmuneSuppressed, '" />
	</field>';

echo '<field>
		<label>', _('Laboratory Entry Number'), '</label>
		<input type="text" size="20" value="" name="EntryNumber" />
	</field>';

echo '<field>
		<label>', _('Reception Date'), '</label>
		<input type="text" class="date" size="10" value="', date($_SESSION['DefaultDateFormat']), '" name="ReceptionDate" />
	</field>';

$SQL = "SELECT type, name FROM care_baclabor_labtest_type WHERE name<>''";
$Result = DB_query($SQL);
echo '<fieldset style="width:auto">';
while ($MyRow = DB_fetch_array($Result)) {
	echo '<field>
			<label class="container" for="', $MyRow['type'], '" onclick="ToggleCheckbox(document.getElementById(\'labtest', $MyRow['type'], '\'));">', _($MyRow['name']), '
				<input type="checkbox" id="labtest', $MyRow['type'], '" name="test', $MyRow['type'], '" />
				<span class="checkmark"></span>
			</label>';
	$MyRow = DB_fetch_array($Result);
	echo '<label class="container" for="', $MyRow['type'], '" onclick="ToggleCheckbox(document.getElementById(\'labtest', $MyRow['type'], '\'));">', _($MyRow['name']), '
				<input type="checkbox" id="labtest', $MyRow['type'], '" name="labtest', $MyRow['type'], '" />
				<span class="checkmark"></span>
			</label>
		</field>';
}
echo '</fieldset>';

echo '</fieldset>';

echo '</fieldset>
	</div>';

echo '<div class="lab_notes">
		<fieldset>
			<legend>', _('For lab use only'), '</legend>';

echo '<field>
		<label></label>
		<textarea name="notes" cols=45 rows=12 wrap="physical"></textarea>
	</field>';

$SQL = "SELECT name FROM care_lab_resistanaerobacro";
$Result = DB_query($SQL);

echo '<table>';
while ($MyRow = DB_fetch_array($Result)) {
	echo '<tr>
			<th rowspan="5">', $MyRow['name'], '</th>
			<th>S</th>
			<th>R</th>
			<th>S</th>
			<th>R</th>
			<th>S</th>
			<th>R</th>
		</tr>
		<tr>
			<td><label class="container" for="X_', $MyRow['name'], '_S1" onclick="ToggleCheckbox(document.getElementById(\'X_', $MyRow['name'], '_S1\'));">
					<input type="checkbox" id="X_', $MyRow['name'], '_S1" name="X_', $MyRow['name'], '_S1" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td><label class="container" for="X_', $MyRow['name'], '_R1" onclick="ToggleCheckbox(document.getElementById(\'X_', $MyRow['name'], '_R1\'));">
					<input type="checkbox" id="X_', $MyRow['name'], '_R1" name="X_', $MyRow['name'], '_R1" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td><label class="container" for="X_', $MyRow['name'], '_S2" onclick="ToggleCheckbox(document.getElementById(\'X_', $MyRow['name'], '_S2\'));">
					<input type="checkbox" id="X_', $MyRow['name'], '_S2" name="X_', $MyRow['name'], '_S2" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td><label class="container" for="X_', $MyRow['name'], '_R2" onclick="ToggleCheckbox(document.getElementById(\'X_', $MyRow['name'], '_R2\'));">
					<input type="checkbox" id="X_', $MyRow['name'], '_R2" name="X_', $MyRow['name'], '_R2" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td><label class="container" for="X_', $MyRow['name'], '_S3" onclick="ToggleCheckbox(document.getElementById(\'X_', $MyRow['name'], '_S3\'));">
					<input type="checkbox" id="X_', $MyRow['name'], '_S3" name="X_', $MyRow['name'], '_S3" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td><label class="container" for="X_', $MyRow['name'], '_R3" onclick="ToggleCheckbox(document.getElementById(\'X_', $MyRow['name'], '_R3\'));">
					<input type="checkbox" id="X_', $MyRow['name'], '_R3" name="X_', $MyRow['name'], '_R3" />
					<span class="checkmark"></span>
				</label>
			</td>
		</tr>
		<tr></tr>
		<tr></tr>
		<tr></tr>';
}

echo '</table>';
echo '</fieldset>
	</div>';

echo '<div class="findings">
		<fieldset class="findings">
			<legend>', _('Test results / Findings'), '</legend>';

echo '<fieldset>';
echo '<field>
		<label class="container" for="BlockerPositive" onclick="ToggleCheckbox(document.getElementById(\'labtest', $MyRow['type'], '\'));">', _('Blocker Positive'), '
			<input type="checkbox" id="BlockerPositive" name="BlockerPositive" />
			<span class="checkmark"></span>
		</label>
	</field>';

echo '<field>
		<label class="container" for="BlockerNegative" onclick="ToggleCheckbox(document.getElementById(\'labtest', $MyRow['type'], '\'));">', _('Blocker Negative'), '
			<input type="checkbox" id="BlockerNegative" name="BlockerNegative" />
			<span class="checkmark"></span>
		</label>
	</field>';
echo '</fieldset>';

echo '<fieldset>';
echo '<field>
		<label class="container" for="StreptococcusResistance" onclick="ToggleCheckbox(document.getElementById(\'labtest', $MyRow['type'], '\'));">', _('Mark by streptococcus resistance'), '
			<input type="checkbox" id="StreptococcusResistance" name="StreptococcusResistance" />
			<span class="checkmark"></span>
		</label>
	</field>';

echo '<field>
		<label class="container" for="BacCTGt10" onclick="ToggleCheckbox(document.getElementById(\'labtest', $MyRow['type'], '\'));">', _('Bac.ct.>10^5'), '
			<input type="checkbox" id="BacCTGt10" name="BacCTGt10" />
			<span class="checkmark"></span>
		</label>
	</field>';

echo '<field>
		<label class="container" for="BacCTLt10" onclick="ToggleCheckbox(document.getElementById(\'labtest', $MyRow['type'], '\'));">', _('Bac.ct.<10^5'), '
			<input type="checkbox" id="BacCTLt10" name="BacCTLt10" />
			<span class="checkmark"></span>
		</label>
	</field>';

echo '<field>
		<label class="container" for="BacCTNeg" onclick="ToggleCheckbox(document.getElementById(\'labtest', $MyRow['type'], '\'));">', _('Bac.ct.neg'), '
			<input type="checkbox" id="BacCTNeg" name="BacCTNeg" />
			<span class="checkmark"></span>
		</label>
	</field>';
echo '</fieldset>';

$SQL = "SELECT nr, name FROM care_lab_testresultid ORDER BY nr";
$Result = DB_query($SQL);

echo '<fieldset style="width:80%">';

while ($MyRow = DB_fetch_array($Result)) {
	echo '<field>
			<label class="container" style="width:0px;margin-bottom:10px" for="id1_', $MyRow['nr'], '_1" onclick="ToggleCheckbox(document.getElementById(\'id1_', $MyRow['nr'], '_1\'));">
				<input type="checkbox" id="id1_', $MyRow['nr'], '_1" name="id1_', $MyRow['nr'], '_1" />
				<span class="checkmark"></span>
			</label>
			<label class="container" style="width:0px" for="id1_', $MyRow['nr'], '_2" onclick="ToggleCheckbox(document.getElementById(\'id1_', $MyRow['nr'], '_2\'));">
				<input type="checkbox" id="id1_', $MyRow['nr'], '_2" name="id1_', $MyRow['nr'], '_2" />
				<span class="checkmark"></span>
			</label>
			<label class="container" style="width:20%;margin-right:10px" for="id1_', $MyRow['nr'], '_3" onclick="ToggleCheckbox(document.getElementById(\'id1_', $MyRow['nr'], '_3\'));">', $MyRow['name'], '
				<input type="checkbox" id="id1_', $MyRow['nr'], '_3" name="id1_', $MyRow['nr'], '_3" />
				<span class="checkmark"></span>
			</label>';
	$MyRow = DB_fetch_array($Result);

	echo '<label class="container" style="width:0px;margin-bottom:10px" for="id1_', $MyRow['nr'], '_1" onclick="ToggleCheckbox(document.getElementById(\'id1_', $MyRow['nr'], '_1\'));">
				<input type="checkbox" id="id1_', $MyRow['nr'], '_1" name="id1_', $MyRow['nr'], '_1" />
				<span class="checkmark"></span>
			</label>
			<label class="container" style="width:0px" for="id1_', $MyRow['nr'], '_2" onclick="ToggleCheckbox(document.getElementById(\'id1_', $MyRow['nr'], '_2\'));">
				<input type="checkbox" id="id1_', $MyRow['nr'], '_2" name="id1_', $MyRow['nr'], '_2" />
				<span class="checkmark"></span>
			</label>
			<label class="container" style="width:20%;margin-right:10px" for="id1_', $MyRow['nr'], '_3" onclick="ToggleCheckbox(document.getElementById(\'id1_', $MyRow['nr'], '_3\'));">', $MyRow['name'], '
				<input type="checkbox" id="id1_', $MyRow['nr'], '_3" name="id1_', $MyRow['nr'], '_3" />
				<span class="checkmark"></span>
			</label>';
	$MyRow = DB_fetch_array($Result);

	echo '<label class="container" style="width:0px;margin-bottom:10px" for="id1_', $MyRow['nr'], '_1" onclick="ToggleCheckbox(document.getElementById(\'id1_', $MyRow['nr'], '_1\'));">
				<input type="checkbox" id="id1_', $MyRow['nr'], '_1" name="id1_', $MyRow['nr'], '_1" />
				<span class="checkmark"></span>
			</label>
			<label class="container" style="width:0px" for="id1_', $MyRow['nr'], '_2" onclick="ToggleCheckbox(document.getElementById(\'id1_', $MyRow['nr'], '_2\'));">
				<input type="checkbox" id="id1_', $MyRow['nr'], '_2" name="id1_', $MyRow['nr'], '_2" />
				<span class="checkmark"></span>
			</label>
			<label class="container" style="width:auto;margin-right:10px" for="id1_', $MyRow['nr'], '_3" onclick="ToggleCheckbox(document.getElementById(\'id1_', $MyRow['nr'], '_3\'));">', $MyRow['name'], '
				<input type="checkbox" id="id1_', $MyRow['nr'], '_3" name="id1_', $MyRow['nr'], '_3" />
				<span class="checkmark"></span>
			</label>
		</field><br />';
}

echo '</fieldset>';

$SQL = "SELECT nr, name FROM care_lab_testresultid_2 ORDER BY nr";
$Result = DB_query($SQL);

echo '<fieldset style="width:80%">';

while ($MyRow = DB_fetch_array($Result)) {
	echo '<field>
			<label class="container" style="width:20%;margin-right:10px" for="id2_', $MyRow['nr'], '_3" onclick="ToggleCheckbox(document.getElementById(\'id2_', $MyRow['nr'], '_3\'));">', $MyRow['name'], '
				<input type="checkbox" id="id2_', $MyRow['nr'], '_3" name="id2_', $MyRow['nr'], '_3" />
				<span class="checkmark"></span>
			</label>';
	$MyRow = DB_fetch_array($Result);

	echo '<label class="container" style="width:20%;margin-right:10px" for="id2_', $MyRow['nr'], '_3" onclick="ToggleCheckbox(document.getElementById(\'id2_', $MyRow['nr'], '_3\'));">', $MyRow['name'], '
				<input type="checkbox" id="id2_', $MyRow['nr'], '_3" name="id2_', $MyRow['nr'], '_3" />
				<span class="checkmark"></span>
			</label>';
	$MyRow = DB_fetch_array($Result);

	echo '<label class="container" style="width:auto;margin-right:10px" for="id2_', $MyRow['nr'], '_3" onclick="ToggleCheckbox(document.getElementById(\'id2_', $MyRow['nr'], '_3\'));">', $MyRow['name'], '
				<input type="checkbox" id="id2_', $MyRow['nr'], '_3" name="id2_', $MyRow['nr'], '_3" />
				<span class="checkmark"></span>
			</label>
		</field><br />';
}

echo '</fieldset>';

echo '</fieldset>
	</div>';

echo '<div class="resistance_test">
		<fieldset>
			<legend>', _('Resistance Test Aerobe'), '</legend>';

$SQL = "SELECT nr, name FROM care_aerobic_resistance_acro ORDER BY nr";
$Result = DB_query($SQL);

echo '<table>';

echo '<tr>
		<th></th>
		<td colspan="3">
			<label class="container" for="Patho1" onclick="ToggleCheckbox(document.getElementById(\'Patho1\'));">', _('Patho 1'), '
				<input type="checkbox" id="Patho1" name="Patho1" />
				<span class="checkmark"></span>
			</label>
		</td>
		<th colspan="6"></th>
		<td colspan="3">
			<label class="container" for="Patho2" onclick="ToggleCheckbox(document.getElementById(\'Patho2\'));">', _('Patho 2'), '
				<input type="checkbox" id="Patho2" name="Patho2" />
				<span class="checkmark"></span>
			</label>
		</td>
		<th colspan="6"></th>
		<td colspan="3">
			<label class="container" for="Patho3" onclick="ToggleCheckbox(document.getElementById(\'Patho3\'));">', _('Patho 3'), '
				<input type="checkbox" id="Patho3" name="Patho3" />
				<span class="checkmark"></span>
			</label>
		</td>
	</tr>';

while ($MyRow = DB_fetch_array($Result)) {

	echo '<tr>
			<th rowspan="5">', $MyRow['name'], '</th>
			<th>S</th>
			<th>M</th>
			<th>R</th>
			<td colspan="6"></td>
			<th>S</th>
			<th>M</th>
			<th>R</th>
			<td colspan="6"></td>
			<th>S</th>
			<th>M</th>
			<th>R</th>
		</tr>
		<tr>
			<td><label class="container" for="Y_', $MyRow['nr'], '_S1" onclick="ToggleCheckbox(document.getElementById(\'Y_', $MyRow['nr'], '_S1\'));">
					<input type="checkbox" id="Y_', $MyRow['nr'], '_S1" name="Y_', $MyRow['nr'], '_S1" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td><label class="container" for="Y_', $MyRow['nr'], '_M1" onclick="ToggleCheckbox(document.getElementById(\'Y_', $MyRow['nr'], '_M1\'));">
					<input type="checkbox" id="Y_', $MyRow['nr'], '_M1" name="Y_', $MyRow['nr'], '_M1" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td><label class="container" for="Y_', $MyRow['nr'], '_R1" onclick="ToggleCheckbox(document.getElementById(\'Y_', $MyRow['nr'], '_R1\'));">
					<input type="checkbox" id="Y_', $MyRow['nr'], '_R1" name="Y_', $MyRow['nr'], '_R1" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td colspan="6"></td>
			<td><label class="container" for="Y_', $MyRow['nr'], '_S2" onclick="ToggleCheckbox(document.getElementById(\'Y_', $MyRow['nr'], '_S2\'));">
					<input type="checkbox" id="Y_', $MyRow['nr'], '_S2" name="Y_', $MyRow['nr'], '_S2" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td><label class="container" for="Y_', $MyRow['nr'], '_M2" onclick="ToggleCheckbox(document.getElementById(\'Y_', $MyRow['nr'], '_M2\'));">
					<input type="checkbox" id="Y_', $MyRow['nr'], '_M2" name="Y_', $MyRow['nr'], '_M2" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td><label class="container" for="Y_', $MyRow['nr'], '_R2" onclick="ToggleCheckbox(document.getElementById(\'Y_', $MyRow['nr'], '_R2\'));">
					<input type="checkbox" id="Y_', $MyRow['nr'], '_R2" name="Y_', $MyRow['nr'], '_R2" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td colspan="6"></td>
			<td><label class="container" for="Y_', $MyRow['nr'], '_S3" onclick="ToggleCheckbox(document.getElementById(\'Y_', $MyRow['nr'], '_S3\'));">
					<input type="checkbox" id="Y_', $MyRow['nr'], '_S3" name="Y_', $MyRow['nr'], '_S3" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td><label class="container" for="Y_', $MyRow['nr'], '_M3" onclick="ToggleCheckbox(document.getElementById(\'Y_', $MyRow['nr'], '_M3\'));">
					<input type="checkbox" id="Y_', $MyRow['nr'], '_M3" name="Y_', $MyRow['nr'], '_M3" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td><label class="container" for="Y_', $MyRow['nr'], '_R3" onclick="ToggleCheckbox(document.getElementById(\'Y_', $MyRow['nr'], '_R3\'));">
					<input type="checkbox" id="Y_', $MyRow['nr'], '_R3" name="Y_', $MyRow['nr'], '_R3" />
					<span class="checkmark"></span>
				</label>
			</td>
		</tr>
		<tr></tr>
		<tr></tr>
		<tr></tr>';
}

echo '</table>';

echo '</fieldset>
	</div>';

echo '<div class="resistance_test_fungi">
		<fieldset>';

$SQL = "SELECT nr, name FROM care_aerobic_resistance_extra ORDER BY nr";
$Result = DB_query($SQL);

echo '<table>';

echo '<tr>
		<td colspan="4">
			<label class="container" for="Fungi" onclick="ToggleCheckbox(document.getElementById(\'Fungi\'));">', _('Fungi'), '
				<input type="checkbox" id="Fungi" name="Fungi" />
				<span class="checkmark"></span>
			</label>
		</td>
	</tr>';

while ($MyRow = DB_fetch_array($Result)) {

	echo '<tr>
			<th rowspan="5">', $MyRow['name'], '</th>
			<th>S</th>
			<th>M</th>
			<th>R</th>
		</tr>
		<tr>
			<td><label class="container" for="Z_', $MyRow['nr'], '_S1" onclick="ToggleCheckbox(document.getElementById(\'Z_', $MyRow['nr'], '_S1\'));">
					<input type="checkbox" id="Z_', $MyRow['nr'], '_S1" name="Z_', $MyRow['nr'], '_S1" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td><label class="container" for="Z_', $MyRow['nr'], '_M1" onclick="ToggleCheckbox(document.getElementById(\'Z_', $MyRow['nr'], '_M1\'));">
					<input type="checkbox" id="Z_', $MyRow['nr'], '_M1" name="Z_', $MyRow['nr'], '_M1" />
					<span class="checkmark"></span>
				</label>
			</td>
			<td><label class="container" for="Z_', $MyRow['nr'], '_R1" onclick="ToggleCheckbox(document.getElementById(\'Z_', $MyRow['nr'], '_R1\'));">
					<input type="checkbox" id="Z_', $MyRow['nr'], '_R1" name="Z_', $MyRow['nr'], '_R1" />
					<span class="checkmark"></span>
				</label>
			</td>
			<tr></tr>
			<tr></tr>
			<tr></tr>
			<tr></tr>
		</tr>';
}

echo '</table>';

echo '</fieldset>
	</div>';

echo '</div>';

echo '</form>';

include ('includes/footer.php');

?>