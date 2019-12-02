<?php
include ('includes/session.php');
$Title = _('GL Account Authorised Users');
$ViewTopic = 'GeneralLedger';
$BookMark = 'GLAccountUsers';
include ('includes/header.php');

if (isset($_POST['SelectedGLAccount']) and $_POST['SelectedGLAccount'] <> '') { //If POST not empty:
	$SelectedGLAccount = mb_strtoupper($_POST['SelectedGLAccount']);
} elseif (isset($_GET['SelectedGLAccount']) and $_GET['SelectedGLAccount'] <> '') { //If GET not empty:
	$SelectedGLAccount = mb_strtoupper($_GET['SelectedGLAccount']);
} else { // Unset empty SelectedGLAccount:
	unset($_GET['SelectedGLAccount']);
	unset($_POST['SelectedGLAccount']);
	unset($SelectedGLAccount);
}

if (isset($_POST['SelectedUser']) and $_POST['SelectedUser'] <> '') { //If POST not empty:
	$SelectedUser = mb_strtoupper($_POST['SelectedUser']);
} elseif (isset($_GET['SelectedUser']) and $_GET['SelectedGLAccount'] <> '') { //If GET not empty:
	$SelectedUser = mb_strtoupper($_GET['SelectedUser']);
} else { // Unset empty SelectedUser:
	unset($_GET['SelectedUser']);
	unset($_POST['SelectedUser']);
	unset($SelectedUser);
}

if (isset($_POST['Cancel']) or isset($_GET['Cancel'])) {
	unset($SelectedGLAccount);
	unset($SelectedUser);
}

if (!isset($SelectedGLAccount)) { // If is NOT set a GL account for users.
	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedUser will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then none of the above are true. These will call the same page again and allow update/input or deletion of the records*/

	echo '<p class="page_title_text">
			<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/gl.png" title="', _('GL Account Authorised Users'), '" /> ', _('GL Account Authorised Users'), '
		</p>'; // Page title.
	if (isset($_POST['Process'])) {
		prnMsg(_('You have not selected any GL Account'), 'error');
	}

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />';

	echo '<fieldset>
			<legend>', _('GL Accounts'), '</legend>';

	$SQL = "SELECT accountcode,
					accountname
				FROM chartmaster
				WHERE language='" . $_SESSION['ChartLanguage'] . "'
				ORDER BY accountcode";
	$Result = DB_query($SQL);
	echo '<field>
			<label for="SelectedGLAccount">', _('Select GL Account'), ':</label>
			<select name="SelectedGLAccount" onchange="this.form.submit()">
				<option value="">', _('Not Yet Selected'), '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedGLAccount) and $MyRow['accountcode'] == $SelectedGLAccount) {
			echo '<option selected="selected" value="', $MyRow['accountcode'] . '">' . $MyRow['accountcode'] . ' - ' . $MyRow['accountname'] . '</option>';
		} else {
			echo '<option value="', $MyRow['accountcode'] . '">' . $MyRow['accountcode'] . ' - ' . $MyRow['accountname'] . '</option>';
		}

	} // End while loop.
	echo '</select>
		</field>
	</fieldset>';
	//Close Select_GL_Account table.
	echo '<div class="centre noPrint">
			<input name="Process" type="submit" value="', _('Accept'), '" />
		</div>'; // "Accept" button.
	echo '</form>';
} else { // If is set a GL account for users ($SelectedGLAccount).
	$SQL = "SELECT accountname
				FROM chartmaster
				WHERE accountcode='" . $SelectedGLAccount . "'
					AND language='" . $_SESSION['ChartLanguage'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$SelectedGLAccountName = $MyRow['accountname'];
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/user.png" title="', _('GL Account Authorised Users'), '" /> ', _('Authorised Users for'), ' ', $SelectedGLAccount, ' - ', $SelectedGLAccountName, '</p>'; // Page title.
	// BEGIN: Needs $SelectedGLAccount, $SelectedUser.
	if (isset($_POST['submit'])) {
		if (!isset($SelectedUser)) {
			prnMsg(_('You have not selected an user to be authorised to use this GL Account'), 'error');
		} else {
			// First check the user is not being duplicated
			$SQL = "SELECT count(*)
					FROM glaccountusers
					WHERE accountcode= '" . $SelectedGLAccount . "'
						AND userid = '" . $SelectedUser . "'";
			$CheckResult = DB_query($SQL);
			$CheckRow = DB_fetch_row($CheckResult);

			if ($CheckRow[0] > 0) {
				prnMsg(_('The user') . ' ' . $SelectedUser . ' ' . _('is already authorised to use this GL Account'), 'error');
			} else {
				// Add new record on submit
				$SQL = "INSERT INTO glaccountusers (accountcode,
													userid,
													canview,
													canupd
												) VALUES (
													'" . $SelectedGLAccount . "',
													'" . $SelectedUser . "',
													'1',
													'1'
												)";
				$ErrMsg = _('An access permission for a user could not be added');
				if (DB_query($SQL, $ErrMsg)) {
					prnMsg(_('An access permission for a user was added') . '. ' . _('GL Account') . ': ' . $SelectedGLAccount . '. ' . _('User') . ': ' . $SelectedUser . '.', 'success');
					unset($_GET['SelectedUser']);
					unset($_POST['SelectedUser']);
				}
			}
		}
	} elseif (isset($_GET['delete'])) {
		$SQL = "DELETE FROM glaccountusers
			WHERE accountcode='" . $SelectedGLAccount . "'
			AND userid='" . $SelectedUser . "'";
		$ErrMsg = _('An access permission for a user could not be removed');
		if (DB_query($SQL, $ErrMsg)) {
			prnMsg(_('An access permission for a user was removed') . '. ' . _('GL Account') . ': ' . $SelectedGLAccount . '. ' . _('User') . ': ' . $SelectedUser . '.', 'success');
			unset($_GET['delete']);
			unset($_POST['delete']);
		}
	} elseif (isset($_GET['ToggleUpdate'])) {
		$SQL = "UPDATE glaccountusers
				SET canupd='" . $_GET['ToggleUpdate'] . "'
				WHERE accountcode='" . $SelectedGLAccount . "'
				AND userid='" . $SelectedUser . "'";
		$ErrMsg = _('An access permission to update a GL account could not be modified');
		if (DB_query($SQL, $ErrMsg)) {
			prnMsg(_('An access permission to update a GL account was modified') . '. ' . _('GL Account') . ': ' . $SelectedGLAccount . '. ' . _('User') . ': ' . $SelectedUser . '.', 'success');
			unset($_GET['ToggleUpdate']);
			unset($_POST['ToggleUpdate']);
		}
	}
	// END: Needs $SelectedGLAccount, $SelectedUser.
	echo '<table>
			<thead>
				<tr>
					<th class="SortedColumn">', _('User Code'), '</th>
					<th class="SortedColumn">', _('User Name'), '</th>
					<th class="centre">', _('View'), '</th>
					<th class="centre">', _('Update'), '</th>
					<th class="noPrint" colspan="1">&nbsp;</th>
					<th class="noPrint">
						<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/printer.png" class="PrintIcon" data-title="', _('Print'), '" alt="', _('Print'), '" onclick="window.print();" />
					</th>
				</tr>
			</thead>';
	$SQL = "SELECT glaccountusers.userid,
					canview,
					canupd,
					www_users.realname
				FROM glaccountusers
				INNER JOIN www_users
					ON glaccountusers.userid=www_users.userid
				WHERE glaccountusers.accountcode='" . $SelectedGLAccount . "'
				ORDER BY glaccountusers.userid ASC";
	$Result = DB_query($SQL);

	echo '<tbody>';
	if (DB_num_rows($Result) > 0) { // If the GL account has access permissions for one or more users:
		while ($MyRow = DB_fetch_array($Result)) {
			echo '<tr class="striped_row">
					<td class="text">', $MyRow['userid'], '</td>
					<td class="text">', $MyRow['realname'], '</td>
					<td class="centre">';
			if ($MyRow['canview'] == 1) {
				echo _('Yes');
			} else {
				echo _('No');
			}
			echo '</td>
				<td class="centre">';

			$ScriptName = htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8');
			if ($MyRow['canupd'] == 1) {
				echo _('Yes'), '</td>', '<td class="noPrint"><a href="', urlencode($ScriptName), '?SelectedGLAccount=', urlencode($SelectedGLAccount), '&amp;SelectedUser=', urlencode($MyRow['userid']), '&amp;ToggleUpdate=0" onclick="return confirm(\'', _('Are you sure you wish to remove Update for this user?'), '\');">', _('Remove Update');
			} else {
				echo _('No'), '</td>', '<td class="noPrint"><a href="', urlencode($ScriptName), '?SelectedGLAccount=', urlencode($SelectedGLAccount), '&amp;SelectedUser=', urlencode($MyRow['userid']), '&amp;ToggleUpdate=1" onclick="return confirm(\'', _('Are you sure you wish to add Update for this user?'), '\');">', _('Add Update');
			}
			echo '</a></td>', '<td class="noPrint"><a href="', urlencode($ScriptName), '?SelectedGLAccount=', urlencode($SelectedGLAccount), '&amp;SelectedUser=', urlencode($MyRow['userid']), '&amp;delete=yes" onclick="return confirm(\'', _('Are you sure you wish to un-authorise this user?'), '\');">', _('Un-authorise'), '</a></td>', '</tr>';
		} // End while list loop.
		
	} else { // If the GL account does not have access permissions for users:
		echo '<tr>
				<td class="centre" colspan="6">', _('GL account does not have access permissions for users'), '</td>
			</tr>';
	}
	echo '</tbody>
		</table>';

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />';

	echo '<input name="SelectedGLAccount" type="hidden" value="', $SelectedGLAccount, '" />';

	$SQL = "SELECT userid,
					realname
				FROM www_users
				WHERE NOT EXISTS (SELECT glaccountusers.userid
									FROM glaccountusers
									WHERE glaccountusers.accountcode='" . $SelectedGLAccount . "'
										AND glaccountusers.userid=www_users.userid)
				ORDER BY userid";
	$Result = DB_query($SQL);
	echo '<fieldset class="noPrint">
			<legend>', _('Access Permissions'), '</legend>';
	if (DB_num_rows($Result) > 0) { // If the GL account does not have access permissions for one or more users:
		echo '<field>
				<label for="SelectedUser">', _('Add access permissions to a user'), ':</label>
				<select name="SelectedUser">';
		if (!isset($_POST['SelectedUser'])) {
			echo '<option selected="selected" value="">', _('Not Yet Selected'), '</option>';
		}
		while ($MyRow = DB_fetch_array($Result)) {
			if (isset($_POST['SelectedUser']) and $MyRow['userid'] == $_POST['SelectedUser']) {
				echo '<option selected="selected" value="', $MyRow['userid'], '">', $MyRow['userid'], ' - ', $MyRow['realname'], '</option>';
			} else {
				echo '<option value="', $MyRow['userid'], '">', $MyRow['userid'], ' - ', $MyRow['realname'], '</option>';
			}
		}
		echo '</select>
			</field>';
	} else { // If the GL account has access permissions for all users:
		echo _('GL account has access permissions for all users');
	}
	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="submit" value="Accept" />
		</div>';

	echo '<div class="page_title_text noPrint">
			<a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '"><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/user.png" /> ', _('Select A Different GL account'), '</a>
		</div>'; // "Select A Different User" button.
	echo '</form>';
}

include ('includes/footer.php');
?>