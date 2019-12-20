<?php
include ('includes/session.php');

$Title = _('Schedule tasks to run periodically');

include ('includes/header.php');

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', _('Search'), '" alt="" />', ' ', $Title, '
	</p>';

if (isset($_GET['Delete'])) {
	$SQL = "DELETE FROM schedule WHERE jobnumber='" . $_GET['JobNumber'] . "'";
	$ErrMsg = _('An error occurred in deleting the job from the schedule');
	$DbgMsg = _('The SQL that was used to delete the job from the schedule was');
	$Result = DB_query($SQL);
}

if (isset($_POST['Insert']) or isset($_POST['Update'])) {
	$InputError = 0;
	$SQL = "SELECT jobnumber
				FROM schedule
				WHERE script='" . $_POST['Script'] . "'
					AND frequency='" . $_POST['Frequency'] . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) > 0) {
		prnMsg(_('This script is already scheduled to run at this frequency'), 'error');
		$InputError = 1;
	}

	if (!in_array($_POST['Frequency'], array('h', 'd', 'w'))) {
		prnMsg(_('You must select a frequency for this job to happen'), 'error');
		$InputError = 1;
	}

	if ($_POST['Script'] == '') {
		prnMsg(_('You must select a script to run'), 'error');
		$InputError = 1;
	}

	if ($InputError == 0 and isset($_POST['Insert'])) {
		$SQL = "INSERT INTO schedule (script,
									nextrun,
									frequency
								) VALUES (
									'" . $_POST['Script'] . "',
									CURRENT_TIMESTAMP,
									'" . $_POST['Frequency'] . "'
								)";
		$ErrMsg = _('An error occurred in inserting the job schedule');
		$DbgMsg = _('The SQL that was used to insert the job schedule was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
		if (DB_error_no() == 0) {
			prnMsg(_('The job has been correctly scheduled'), 'success');
		}
	} elseif ($InputError == 0 and isset($_POST['Update'])) {
		$SQL = "UPDATE schedule SET script='" . $_POST['Script'] . "',
									nextrun=CURRENT_TIMESTAMP,
									frequency='" . $_POST['Frequency'] . "'
								WHERE jobnumber='" . $_POST['JobNumber'] . "'";
		$ErrMsg = _('An error occurred in updating the job schedule');
		$DbgMsg = _('The SQL that was used to update the job schedule was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
		if (DB_error_no() == 0) {
			prnMsg(_('The job has been correctly updated'), 'success');
		}
	}
}

$SQL = "SELECT jobnumber,
				script,
				nextrun,
				frequency
			FROM schedule";
$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0) {
	echo '<table>
			<tr>
				<th>', _('Script'), '</th>
				<th>', _('Frequency'), '</th>
				<th>', _('Next Run'), '</th>
				<th colspan="2">', _('Maintenance'), '</th>
			</tr>';
	while ($MyRow = DB_fetch_array($Result)) {
		switch ($MyRow['frequency']) {
			case 'd':
				$Frequency = 'Daily';
			break;
			case 'h':
				$Frequency = 'Hourly';
			break;
			case 'w':
				$Frequency = 'Weekly';
			break;
		}

		echo '<tr class="striped_row">
				<td>', $MyRow['script'], '</td>
				<td>', $Frequency, '</td>
				<td>', ConvertSQLDateTime($MyRow['nextrun']), '</td>
				<td><a href="', htmlspecialchars(basename(__FILE__), '?JobNumber=', urlencode($MyRow['jobnumber']), ENT_QUOTES, 'UTF-8'), '&amp;Edit=1">', _('Edit'), '</a></td>
				<td><a href="', htmlspecialchars(basename(__FILE__), '?JobNumber=', urlencode($MyRow['jobnumber']), ENT_QUOTES, 'UTF-8'), '&amp;Delete=1">', _('Remove'), '</a></td>
			</tr>';
	}
	echo '</table>';
}

if (isset($_GET['Edit'])) {
	$SQL = "SELECT script,
					frequency
				FROM schedule
				WHERE jobnumber ='" . $_GET['JobNumber'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$_POST['Script'] = $MyRow['script'];
	$_POST['Frequency'] = $MyRow['frequency'];
} elseif (!isset($_POST['Insert']) and !isset($_POST['Update'])) {
	$_POST['Script'] = '';
	$_POST['Frequency'] = '';
}

echo '<form method="post" id="JobScheduler" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

$Files = glob('*.php');
natsort($Files);

echo '<fieldset>
		<legend>', _('Schedule Details'), '</legend>';

echo '<field>
		<label for="Script">', _('Select Script to Schedule'), '</label>
		<select name="Script">
			<option value=""></option>';
foreach ($Files as $File) {
	if (strpos(file_get_contents($File), "include_once('includes/session.php')") !== false and $File != 'JobScheduler.php') {
		if ($_POST['Script'] == $File) {
			echo '<option selected="selected" value="', $File, '">', $File, '</option>';
		} else {
			echo '<option value="', $File, '">', $File, '</option>';
		}
	}
}
echo '</select>
	</field>';

switch ($_POST['Frequency']) {
	case 'd':
		$Daily = 'selected="selected"';
		$Weekly = '';
		$Hourly = '';
	break;
	case 'h':
		$Daily = '';
		$Hourly = 'selected="selected"';
		$Weekly = '';
	break;
	case 'w':
		$Weekly = 'selected="selected"';
		$Daily = '';
		$Hourly = '';
	break;
	default:
		$Weekly = '';
		$Daily = '';
		$Hourly = '';
	break;
}

echo '<field>
		<label for="Frequency">', _('Frequency'), '</label>
		<select name="Frequency">
			<option value=""></option>
			<option ', $Hourly, ' value="h">', _('Hourly'), '</option>
			<option ', $Daily, ' value="d">', _('Daily'), '</option>
			<option ', $Weekly, ' value="w">', _('Weekly'), '</option>
		</select>
	</field>';

echo '</fieldset>';

if (isset($_GET['Edit'])) {

	echo '<div class="centre">
			<input type="submit" name="Update" value="', _('Submit Job'), '" />
		</div>';
	echo '<input type="hidden" name="JobNumber" value="', $_GET['JobNumber'], '" />';
} else {
	echo '<div class="centre">
			<input type="submit" name="Insert" value="', _('Submit Job'), '" />
		</div>';
}

echo '</form>';
include ('includes/footer.php');

?>