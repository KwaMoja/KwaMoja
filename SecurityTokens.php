<?php

include('includes/session.inc');
$Title = _('Maintain Security Tokens');

include('includes/header.inc');

if (isset($_GET['SelectedToken'])) {
	if ($_GET['Action'] == 'delete') {
		$Result = DB_query("SELECT script FROM scripts WHERE pagesecurity='" . $_GET['SelectedToken'] . "'");
		if (DB_num_rows($Result) > 0) {
			prnMsg(_('This secuirty token is currently used by the following scripts and cannot be deleted'), 'error');
			echo '<table>
					<tr>';
			$i = 0;
			while ($ScriptRow = DB_fetch_array($Result)) {
				if ($i == 5) {
					$i = 0;
					echo '</tr>
							<tr>';
				}
				++$i;
				echo '<td>' . $ScriptRow['script'] . '</td>';
			}
			echo '</tr></table>';
		} else {
			$Result = DB_query("DELETE FROM securitytokens WHERE tokenid='" . $_GET['SelectedToken'] . "'");
		}
	} else { // it must be an edit
		$SQL = "SELECT tokenid,
					tokenname
				FROM securitytokens
				WHERE tokenid='" . $_GET['SelectedToken'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		$_POST['TokenID'] = $MyRow['tokenid'];
		$_POST['TokenDescription'] = $MyRow['tokenname'];
	}
}
if (!isset($_POST['TokenID'])) {
	$_POST['TokenID'] = '';
	$_POST['TokenDescription'] = '';
}

$InputError = 0;

if (isset($_POST['Submit']) or isset($_POST['Update'])) {
	if (!is_numeric($_POST['TokenID'])) {
		prnMsg(_('The token ID is expected to be a number. Please enter a number for the token ID'), 'error');
		$InputError = 1;
	}
	if ($_POST['TokenID'] > 999) {
		prnMsg(_('The token ID must be less than 1000'), 'error');
		$InputError = 1;
	}
	if (mb_strlen($_POST['TokenDescription']) == 0) {
		prnMsg(_('A token description must be entered'), 'error');
		$InputError = 1;
	}
}

if (isset($_POST['Submit'])) {

	$TestSQL = "SELECT tokenid FROM securitytokens WHERE tokenid='" . $_POST['TokenID'] . "'";
	$TestResult = DB_query($TestSQL);
	if (DB_num_rows($TestResult) != 0) {
		prnMsg(_('This token ID has already been used. Please use a new one'), 'warn');
		$InputError = 1;
	}
	if ($InputError == 0) {
		$SQL = "INSERT INTO securitytokens values('" . $_POST['TokenID'] . "', '" . $_POST['TokenDescription'] . "')";
		$Result = DB_query($SQL);
		$_POST['TokenID'] = '';
		$_POST['TokenDescription'] = '';
	}
}

if (isset($_POST['Update']) and $InputError == 0) {
	$SQL = "UPDATE securitytokens
				SET tokenname='" . $_POST['TokenDescription'] . "'
			WHERE tokenid='" . $_POST['TokenID'] . "'";
	$Result = DB_query($SQL);
	$_POST['TokenDescription'] = '';
	$_POST['TokenID'] = '';
}
echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Print') . '" alt="" />' . ' ' . $Title . '</p>';

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" id="form">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table>
		<tr>';

if (isset($_GET['Action']) and $_GET['Action'] == 'edit') {
	echo '<td>' . _('Description') . '</td>
		<td><input type="text" size="50" autofocus="autofocus" required="required" maxlength="50" name="TokenDescription" value="' . _($_POST['TokenDescription']) . '" /></td>
		<td><input type="hidden" name="TokenID" value="' . $_GET['SelectedToken'] . '" />
			<input type="submit" name="Update" value="' . _('Update') . '" />';
} else {
	echo '<td>' . _('Token ID') . '</td>
			<td><input class="number" size="6" required="required" maxlength="4" type="text" name="TokenID" value="' . $_POST['TokenID'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Description') . '</td>
			<td><input type="text" size="50" required="required" maxlength="50" name="TokenDescription" value="' . _($_POST['TokenDescription']) . '" /></td>
			<td><input type="submit" name="Submit" value="' . _('Insert') . '" />';
}

echo '</td>
	</tr>
	</table>';

echo '</form>';

echo '<table class="selection">';
echo '<tr>
		<th>' . _('Token ID') . '</th>
		<th>' . _('Description') . '</th>
	</tr>';

$SQL = "SELECT tokenid, tokenname FROM securitytokens WHERE tokenid<1000 ORDER BY tokenid";
$Result = DB_query($SQL);

while ($MyRow = DB_fetch_array($Result)) {
	echo '<tr>
			<td>' . $MyRow['tokenid'] . '</td>
			<td>' . htmlspecialchars($MyRow['tokenname'], ENT_QUOTES, 'UTF-8') . '</td>
			<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedToken=' . $MyRow['tokenid'] . '&amp;Action=edit">' . _('Edit') . '</a></td>
			<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedToken=' . $MyRow['tokenid'] . '&amp;Action=delete" onclick="return MakeConfirm(\'' . _('Are you sure you wish to delete this security token?') . '\', \'Confirm Delete\', this);">' . _('Delete') . '</a></td>
		</tr>';
}

echo '</table><br />';

include('includes/footer.inc');
?>