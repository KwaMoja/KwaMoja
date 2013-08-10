<?php

include('includes/session.inc');

$Title = _('Edit The Modules Menu');
$ViewTopic = '';
$BookMark = '';
include('includes/header.inc');

if (isset($_POST['Save'])) {
	foreach ($_POST as $Key=>$Value) {
		if (mb_substr($Key, 0, 8) == 'Sequence') {
			$ReportLink = mb_substr($Key, 8, mb_strlen($Key) - 8);
			$sql = "UPDATE modules SET sequence='" . $Value . "'
									WHERE reportlink='" . $ReportLink . "'
										AND secroleid='" . $_POST['SecurityRole'] . "'";
			$result = DB_query($sql, $db);
		}
	}
}

if (!isset($_POST['SecurityRole'])) {
	$sql = "SELECT secroleid,
					secrolename
				FROM securityroles";
	$result = DB_query($sql, $db);

	echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p><br />';

	echo '<form onSubmit="return VerifyForm(this);" method="post" class="noPrint" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<div class="centre">
			<select name="SecurityRole" required="required">
				<option value=""></option>';

	while ($myrow = DB_fetch_array($result)) {
		echo '<option value="' . $myrow['secroleid'] . '">' . $myrow['secrolename'] . '</option>';
	}

	echo '</div>
		</select>';

	echo '<div class="centre">
			<input type="submit" name="Submit" value="' . _('Select Security Role') . '" />
		</div>';

	echo '</form>';
} else {

	echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p><br />';

	echo '<form onSubmit="return VerifyForm(this);" method="post" class="noPrint" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="SecurityRole" value="' . $_POST['SecurityRole'] . '" />';

	$sql = "SELECT modulelink,
					reportlink,
					modulename,
					sequence
				FROM modules
				WHERE secroleid='" . $_POST['SecurityRole'] . "'
				ORDER BY sequence";
	$result = DB_query($sql, $db);

	echo '<table class="selection">
			<tr>
				<th>' . _('Module Name') . '</th>
				<th>' . _('Sequence') . '</th>
			</tr>';

	while ($myrow = DB_fetch_array($result)) {
		echo '<tr>
				<td>' . $myrow['modulename'] . '</td>
				<td><input type="text" required="required" minlength="1" maxlength="5" size="5" class="number" name="Sequence' . $myrow['reportlink'] . '" value="' . $myrow['sequence'] . '" /></td>
			</tr>';
	}

	echo '</table>
			<div class="centre">
				<input type="submit" name="Save" value="' . _('Save') . '" />
			</div>
		</form>';
}

include('includes/footer.inc');

?>