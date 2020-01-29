<?php
/* Defines the various centres of work within a manufacturing company. Also the overhead and labour rates applicable to the work centre and its standard capacity */

include ('includes/session.php');
include ('includes/SQL_CommonFunctions.php');
$Title = _('Work Centres');
$ViewTopic = 'Manufacturing';
$BookMark = 'WorkCentres';
include ('includes/header.php');

if (isset($_POST['SelectedWC'])) {
	$SelectedWC = $_POST['SelectedWC'];
} elseif (isset($_GET['SelectedWC'])) {
	$SelectedWC = $_GET['SelectedWC'];
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	 ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (RecordExists('workcentres', 'code', $_POST['Code']) and !isset($SelectedWC)) {
		$InputError = 1;
		prnMsg(_('This work centre already exists in the database. Please use a different code.'), 'error');
	}
	if (mb_strlen($_POST['Code']) < 2) {
		$InputError = 1;
		prnMsg(_('The Work Centre code must be at least 2 characters long'), 'error');
	}
	if (mb_strlen($_POST['Description']) < 3) {
		$InputError = 1;
		prnMsg(_('The Work Centre description must be at least 3 characters long'), 'error');
	}
	if (mb_strstr($_POST['Code'], ' ') or ContainsIllegalCharacters($_POST['Code'])) {
		$InputError = 1;
		prnMsg(_('The work centre code cannot contain any of the following characters') . " - ' &amp; + \" \\ " . _('or a space'), 'error');
	}

	if (isset($SelectedWC) and $InputError != 1) {

		/*SelectedWC could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$SQL = "UPDATE workcentres SET location = '" . $_POST['Location'] . "',
						description = '" . $_POST['Description'] . "',
						overheadrecoveryact ='" . $_POST['OverheadRecoveryAct'] . "',
						overheadperhour = '" . $_POST['OverheadPerHour'] . "'
				WHERE code = '" . $SelectedWC . "'";
		$Msg = _('The work centre record has been updated');
	} elseif ($InputError != 1) {

		/*Selected work centre is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new work centre form */

		$SQL = "INSERT INTO workcentres (code,
										location,
										description,
										overheadrecoveryact,
										overheadperhour)
					VALUES ('" . $_POST['Code'] . "',
						'" . $_POST['Location'] . "',
						'" . $_POST['Description'] . "',
						'" . $_POST['OverheadRecoveryAct'] . "',
						'" . $_POST['OverheadPerHour'] . "'
						)";
		$Msg = _('The new work centre has been added to the database');
	}
	//run the SQL from either of the above possibilites
	if ($InputError != 1) {
		$Result = DB_query($SQL, _('The update/addition of the work centre failed because'));
		prnMsg($Msg, 'success');
		unset($_POST['Location']);
		unset($_POST['Description']);
		unset($_POST['Code']);
		unset($_POST['OverheadRecoveryAct']);
		unset($_POST['OverheadPerHour']);
		unset($SelectedWC);
	}

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button
	// PREVENT DELETES IF DEPENDENT RECORDS IN 'BOM'
	$SQL = "SELECT COUNT(*) FROM bom WHERE bom.workcentreadded='" . $SelectedWC . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		prnMsg(_('Cannot delete this work centre because bills of material have been created requiring components to be added at this work center') . '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('BOM items referring to this work centre code'), 'warn');
	} else {
		$SQL = "SELECT COUNT(*) FROM contractbom WHERE contractbom.workcentreadded='" . $SelectedWC . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0] > 0) {
			prnMsg(_('Cannot delete this work centre because contract bills of material have been created having components added at this work center') . '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('Contract BOM items referring to this work centre code'), 'warn');
		} else {
			$SQL = "DELETE FROM workcentres WHERE code='" . $SelectedWC . "'";
			$Result = DB_query($SQL);
			prnMsg(_('The selected work centre record has been deleted'), 'succes');
		} // end of Contract BOM test

	} // end of BOM test
	unset($SelectedWC);
}

if (!isset($SelectedWC)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedWC will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of work centres will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/
	echo '<p class="page_title_text" >
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '
		</p>';

	$SQL = "SELECT workcentres.code,
					workcentres.description,
					locations.locationname,
					workcentres.overheadrecoveryact,
					chartmaster.accountname,
					workcentres.overheadperhour
				FROM workcentres
				INNER JOIN locations
					ON workcentres.location = locations.loccode
				INNER JOIN chartmaster
					ON workcentres.overheadrecoveryact=chartmaster.accountcode
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" . $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				WHERE chartmaster.language='" . $_SESSION['ChartLanguage'] . "'";
	$Result = DB_query($SQL);
	echo '<table>
			<thead>
				<tr>
					<th class="SortedColumn">', _('WC Code'), '</th>
					<th class="SortedColumn">', _('Description'), '</th>
					<th class="SortedColumn">', _('Location'), '</th>
					<th>', _('Overhead GL Account'), '</th>
					<th>', _('Overhead Per Hour'), '</th>
					<th colspan="2"></th>
				</tr>
			</thead>';
	echo '<tbody>';
	while ($MyRow = DB_fetch_array($Result)) {

		echo '<tr class="striped_row">
				<td>', $MyRow['code'], '</td>
				<td>', $MyRow['description'], '</td>
				<td>', $MyRow['locationname'], '</td>
				<td>', $MyRow['overheadrecoveryact'], ' - ', $MyRow['accountname'], '</td>
				<td class="number">', $MyRow['overheadperhour'], '</td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedWC=', urlencode($MyRow['code']), '">', _('Edit'), '</a></td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedWC=', urlencode($MyRow['code']), '&amp;delete=yes" onclick="return MakeConfirm(\'', _('Are you sure you wish to delete this work centre?'), '\', \'Confirm Delete\', this);">', _('Delete'), '</a></td>
			</tr>';
	}
	echo '</tbody>';
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!
if (isset($SelectedWC)) {
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', _('Search'), '" alt="" />', ' ', $Title, '
		</p>';
	echo '<div class="centre">
			<a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">', _('Show all Work Centres'), '</a>
		</div>';
}

echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

if (isset($SelectedWC)) {
	//editing an existing work centre
	$SQL = "SELECT code,
					location,
					description,
					overheadrecoveryact,
					overheadperhour
			FROM workcentres
			INNER JOIN locationusers
				ON locationusers.loccode=workcentres.location
				AND locationusers.userid='" . $_SESSION['UserID'] . "'
				AND locationusers.canupd=1
			WHERE code='" . $SelectedWC . "'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['Code'] = $MyRow['code'];
	$_POST['Location'] = $MyRow['location'];
	$_POST['Description'] = $MyRow['description'];
	$_POST['OverheadRecoveryAct'] = $MyRow['overheadrecoveryact'];
	$_POST['OverheadPerHour'] = $MyRow['overheadperhour'];

	echo '<input type="hidden" name="SelectedWC" value="', $SelectedWC, '" />
		<input type="hidden" name="Code" value="', $_POST['Code'], '" />
		<fieldset>
			<legend>', _('Edit Work Centre'), ' - ', $_POST['Description'], ' ', '(', $SelectedWC, ')</legend>
			<field>
				<label for="Code">', _('Work Centre Code'), ':</label>
				<div class="fieldtext">', $_POST['Code'], '</div>
			</field>';

} else { //end of if $SelectedWC only do the else when a new record is being entered
	if (!isset($_POST['Code'])) {
		$_POST['Code'] = '';
	}
	echo '<fieldset>
			<legend>', _('Create New Work Centre'), '</legend>
			<field>
				<label for="Code">', _('Work Centre Code'), ':</label>
				<input type="text" class="AlphaNumeric" name="Code" size="6" autofocus="autofocus" required="required" maxlength="5" value="', $_POST['Code'], '" />
				<fieldhelp>', _('The alphanumeric code by which this work centre will be identified. Up to 5 characters can be used.'), '</fieldhelp>
			</field>';
}

if (!isset($_POST['Description'])) {
	$_POST['Description'] = '';
}
echo '<field>
		<label for="Description">', _('Work Centre Description'), ':</label>
		<input type="text" name="Description" size="21" required="required" autofocus="autofocus" maxlength="20" value="', $_POST['Description'], '" />
		<fieldhelp>', _('A description that helps the user identify this work centre'), '</fieldhelp>
	</field>';

$SQL = "SELECT locationname,
				locations.loccode
			FROM locations
			INNER JOIN locationusers
				ON locationusers.loccode=locations.loccode
				AND locationusers.userid='" . $_SESSION['UserID'] . "'
				AND locationusers.canupd=1";
$Result = DB_query($SQL);
echo '<field>
		<label for="Location">', _('Location'), ':</label>
		<select required="required" name="Location">';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['Location']) and $MyRow['loccode'] == $_POST['Location']) {
		echo '<option selected="selected" value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
	} else {
		echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>', _('The location of this work centre'), '</fieldhelp>
</field>';

echo '<field>
		<label for="OverheadRecoveryAct">', _('Overhead Recovery GL Account'), ':</label>';
GLSelect(1, 'OverheadRecoveryAct');
echo '<fieldhelp>', _('The general ledger expense code where any overheads absorbed into the costings at this work centre will be posted.'), '</fieldhelp>
	</field>';

if (!isset($_POST['OverheadPerHour'])) {
	$_POST['OverheadPerHour'] = 0;
}
echo '<field>
		<label for="OverheadPerHour">', _('Overhead Per Hour'), ':</label>
		<input type="text" class="number" name="OverheadPerHour" size="6" required="required" maxlength="6" value="', $_POST['OverheadPerHour'], '" />
		<fieldhelp>', _('The hourly raate at which to absorb overhead costs at this work centre'), '</fieldhelp>
	</field>
</fieldset>';

echo '<div class="centre">
		<input type="submit" name="submit" value="', _('Enter Information'), '" />
	</div>';

echo '</form>';
include ('includes/footer.php');
?>