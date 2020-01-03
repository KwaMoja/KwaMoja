<?php
include ('includes/session.php');

$Title = _('Edit The Modules Menu');
$ViewTopic = '';
$BookMark = '';
include ('includes/header.php');

if (isset($_POST['Save'])) {
	foreach ($_POST as $Key => $Value) {
		if (mb_substr($Key, 0, 8) == 'Sequence') {
			$ReportLink = mb_substr($Key, 8, mb_strlen($Key) - 8);
			$SQL = "UPDATE modules SET sequence='" . $Value . "',
										modulename='" . $_POST['Name' . $ReportLink] . "'
									WHERE reportlink='" . $ReportLink . "'
										AND secroleid='" . $_POST['SecurityRole'] . "'";
			$Result = DB_query($SQL);
		}
	}
}

if (!isset($_POST['SecurityRole'])) {
	$SQL = "SELECT secroleid,
					secrolename
				FROM securityroles";
	$Result = DB_query($SQL);

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', _('Search'), '" alt="" />', ' ', $Title, '
		</p>';

	echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<fieldset>
			<legend>', _('Security Roll Selection'), '</legend>
			<field>
				<label for="SecurityRole">', _('Select Roll'), '</label>
				<select name="SecurityRole" required="required">
					<option value=""></option>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="', $MyRow['secroleid'], '">', $MyRow['secrolename'], '</option>';
	}
	echo '</select>
		</field>
	</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="Submit" value="', _('Select Security Role'), '" />
		</div>';

	echo '</form>';
} else {

	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p><br />';

	echo '<form method="post" action="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="SecurityRole" value="' . $_POST['SecurityRole'] . '" />';

	$SQL = "SELECT modulelink,
					reportlink,
					modulename,
					sequence
				FROM modules
				WHERE secroleid='" . $_POST['SecurityRole'] . "'
				ORDER BY sequence";
	$Result = DB_query($SQL);

	echo '<table>
			<tr>
				<th>' . _('Module Name') . '</th>
				<th>' . _('New Name') . '</th>
				<th>' . _('Sequence') . '</th>
			</tr>';

	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr>
				<td>' . $MyRow['modulename'] . '</td>
				<td><input type="text" required="required" maxlength="50" size="25" name="Name' . $MyRow['reportlink'] . '" value="' . $MyRow['modulename'] . '" /></td>
				<td><input type="text" required="required" maxlength="5" size="5" class="number" name="Sequence' . $MyRow['reportlink'] . '" value="' . $MyRow['sequence'] . '" /></td>
			</tr>';
	}

	echo '</table>
			<div class="centre">
				<input type="submit" name="Save" value="' . _('Save') . '" />
			</div>
		</form>';
}

include ('includes/footer.php');

?>