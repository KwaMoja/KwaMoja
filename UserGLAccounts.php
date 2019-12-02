<?php
/* $Id: UserGLAccounts.php 7427 2015-12-27 19:59:10Z rchacon $*/
/* Maintenance of GL Accounts allowed for a user. */

include ('includes/session.php');
$Title = _('User Authorised GL Accounts');
$ViewTopic = 'GeneralLedger';
$BookMark = 'UserGLAccounts';
include ('includes/header.php');

if (isset($_POST['SelectedUser']) and $_POST['SelectedUser'] <> '') { //If POST not empty:
	$SelectedUser = mb_strtoupper($_POST['SelectedUser']);
} elseif (isset($_GET['SelectedUser']) and $_GET['SelectedUser'] <> '') { //If GET not empty:
	$SelectedUser = mb_strtoupper($_GET['SelectedUser']);
}
if (isset($_POST['SelectedGLAccount']) and $_POST['SelectedGLAccount'] <> '') {
	$SelectedGLAccount = mb_strtoupper($_POST['SelectedGLAccount']);
} elseif (isset($_GET['SelectedGLAccount']) and $_GET['SelectedGLAccount'] <> '') {
	$SelectedGLAccount = mb_strtoupper($_GET['SelectedGLAccount']);
} else { // Unset empty SelectedGLAccount:
	unset($_GET['SelectedGLAccount']);
	unset($_POST['SelectedGLAccount']);
	unset($SelectedGLAccount);
}

if (isset($_GET['Cancel']) or isset($_POST['Cancel'])) {
	unset($SelectedUser);
	unset($SelectedGLAccount);
}

if (!isset($SelectedUser)) { // If is NOT set a user for GL accounts.
	echo '<p class="page_title_text">
			<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/gl.png" title="', _('User Authorised GL Accounts'), '" /> ', _('User Authorised GL Accounts'), '
		</p>'; // Page title.
	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedGLAccount will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then none of the above are true. These will call the same page again and allow update/input or deletion of the records.*/

	if (isset($_POST['Process'])) {
		prnMsg(_('You have not selected any user'), 'error');
	}
	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />';

	echo '<fieldset>
			<legend>', _('Users'), '</legend>';

	echo '<field>
			<label for="SelectedUser">', _('Select User'), ':</label>
			<select name="SelectedUser" onchange="this.form.submit()">
				<option value="">', _('Not Yet Selected'), '</option>';
	$SQL = "SELECT userid,
					realname
				FROM www_users
				ORDER BY userid";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedUser) and $MyRow['userid'] == $SelectedUser) {
			echo '<option selected="selected" value="', $MyRow['userid'], '">', $MyRow['userid'], ' - ', $MyRow['realname'], '</option>';
		} else {
			echo '<option value="', $MyRow['userid'], '">', $MyRow['userid'], ' - ', $MyRow['realname'], '</option>';
		}
	} // End while loop.
	echo '</select>
		</field>
	</fieldset>'; //Close Select_User table.
	echo '<div class="centre noPrint">
			<input name="Process" type="submit" value="' . _('Accept') . '" />
		</div> '; // "Accept" button.
	echo '</form>';

} else { // If is set a user for GL accounts ($SelectedUser).
	$SQL = "SELECT realname
				FROM www_users
				WHERE userid='" . $SelectedUser . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$SelectedUserName = $MyRow['realname'];
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/gl.png" title="', _('User Authorised GL Accounts'), '" /> ', _('Authorised GL Accounts for'), ' ', $SelectedUserName, '</p>'; // Page title.
	// BEGIN: Needs $SelectedUser, $SelectedGLAccount:
	if (isset($_POST['submit'])) {
		$InputError = 0;
		if ($_POST['SelectedGLAccount'] == '') {
			$InputError = 1;
			prnMsg(_('You have not selected an GL Account to be authorised for this user'), 'error');
			unset($SelectedGLAccount);
		}
		if ($InputError != 1) {
			// First check the user is not being duplicated
			$SQL = "SELECT count(*)
						FROM glaccountusers
						WHERE accountcode= '" . $SelectedGLAccount . "'
							AND userid = '" . $SelectedUser . "'";
			$CheckResult = DB_query($SQL);
			$CheckRow = DB_fetch_row($CheckResult);
			if ($CheckRow[0] > 0) {
				$InputError = 1;
				prnMsg(_('The GL Account') . ' ' . $SelectedGLAccount . ' ' . _('is already authorised for this user'), 'error');
			} else {
				// Add new record on submit
				$SQL = "INSERT INTO glaccountusers (accountcode,
													userid,
													canview,
													canupd)
											VALUES ('" . $SelectedGLAccount . "',
													'" . $SelectedUser . "',
													'1',
													'1')";
				$Result = DB_query($SQL);
				prnMsg(_('An access permission to a GL account was added') . '. ' . _('User') . ': ' . $SelectedUser . '. ' . _('GL Account') . ': ' . $SelectedGLAccount . '.', 'success');
				unset($_POST['SelectedGLAccount']);
			}
		}
	} elseif (isset($_GET['delete']) or isset($_POST['delete'])) {
		$SQL = "DELETE FROM glaccountusers
			WHERE accountcode='" . $SelectedGLAccount . "'
			AND userid='" . $SelectedUser . "'";
		$ErrMsg = _('The GL Account user record could not be deleted because');
		$Result = DB_query($SQL, $ErrMsg);
		prnMsg(_('An access permission to a GL account was removed') . '. ' . _('User') . ': ' . $SelectedUser . '. ' . _('GL Account') . ': ' . $SelectedGLAccount . '.', 'success');
		unset($_GET['delete']);
		unset($_POST['delete']);
	} elseif (isset($_GET['ToggleUpdate']) or isset($_POST['ToggleUpdate'])) { // Can update (write) GL accounts flag.
		if (isset($_GET['ToggleUpdate']) and $_GET['ToggleUpdate'] <> '') { //If GET not empty.
			$ToggleUpdate = $_GET['ToggleUpdate'];
		} elseif (isset($_POST['ToggleUpdate']) and $_POST['ToggleUpdate'] <> '') { //If POST not empty.
			$ToggleUpdate = $_POST['ToggleUpdate'];
		}
		$SQL = "UPDATE glaccountusers
				SET canupd='" . $ToggleUpdate . "'
				WHERE accountcode='" . $SelectedGLAccount . "'
				AND userid='" . $SelectedUser . "'";
		$ErrMsg = _('The GL Account user record could not be updated because');
		$Result = DB_query($SQL, $ErrMsg);
		prnMsg(_('The access permission to update a GL account was modified') . '. ' . _('User') . ': ' . $SelectedUser . '. ' . _('GL Account') . ': ' . $SelectedGLAccount . '.', 'success');
		unset($_GET['ToggleUpdate']);
		unset($_POST['ToggleUpdate']);
	}
	// END: Needs $SelectedUser, $SelectedGLAccount.
	echo '<table>
			<thead>
				<tr>
					<th class="SortedColumn">', _('Code'), '</th>
					<th class="SortedColumn">', _('Name'), '</th>
					<th class="centre">', _('View'), '</th>
					<th class="centre">', _('Update'), '</th>
					<th class="noPrint">&nbsp;</th>
					<th class="noPrint">
						<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/printer.png" class="PrintIcon" title="', _('Print'), '" alt="', _('Print'), '" onclick="window.print();" />
					</th>
				</tr>
			</thead>';
	$SQL = "SELECT glaccountusers.accountcode,
					canview,
					canupd,
					chartmaster.accountname
				FROM glaccountusers
				INNER JOIN chartmaster
					ON glaccountusers.accountcode=chartmaster.accountcode
				WHERE glaccountusers.userid='" . $SelectedUser . "'
					AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
				ORDER BY chartmaster.accountcode ASC";
	$Result = DB_query($SQL);
	echo '<tbody>';
	if (DB_num_rows($Result) > 0) { // If the user has access permissions to one or more GL accounts:
		while ($MyRow = DB_fetch_array($Result)) {
			echo '<tr class="striped_row">
					<td class="text">', $MyRow['accountcode'], '</td>
					<td class="text">', $MyRow['accountname'], '</td>
					<td class="centre">';
			if ($MyRow['canview'] == 1) {
				echo _('Yes');
			} else {
				echo _('No');
			}
			echo '</td>';

			$ScriptName = htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8');
			if ($MyRow['canupd'] == 1) {
				echo '<td class="centre">', _('Yes'), '</td>
					<td class="noPrint">
						<a href="', $ScriptName, '?SelectedUser=', urlencode($SelectedUser), '&amp;SelectedGLAccount=', urlencode($MyRow['accountcode']), '&amp;ToggleUpdate=0" onclick="return confirm(\'', _('Are you sure you wish to remove Update for this GL Account?'), '\');">', _('Remove Update'), '</a>
					</td>';
			} else {
				echo '<td class="centre">', _('No'), '</td>
					<td class="noPrint">
						<a href="', $ScriptName, '?SelectedUser=', urlencode($SelectedUser), '&amp;SelectedGLAccount=', urlencode($MyRow['accountcode']), '&amp;ToggleUpdate=1" onclick="return confirm(\'', _('Are you sure you wish to add Update for this GL Account?'), '\');">', _('Add Update'), '</a>
					</td>';
			}
			echo '<td class="noPrint">
					<a href="', $ScriptName, '?SelectedUser=', urlencode($SelectedUser), '&amp;SelectedGLAccount=', urlencode($MyRow['accountcode']), '&amp;delete=yes" onclick="return confirm(\'', _('Are you sure you wish to un-authorise this GL Account?'), '\');">', _('Un-authorise'), '</a>
				</td>
			</tr>';
		} // End while list loop.
		
	} else { // If the user does not have access permissions to GL accounts:
		echo '<tr>
				<td class="centre" colspan="6">', _('User does not have access permissions to GL accounts'), '</td>
			</tr>';
	}
	echo '</tbody>
		</table>';

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />';

	echo '<input name="SelectedUser" type="hidden" value="', $SelectedUser, '" />';

	$SQL = "SELECT accountcode,
					accountname
				FROM chartmaster
				WHERE NOT EXISTS (SELECT glaccountusers.accountcode
									FROM glaccountusers
									WHERE glaccountusers.userid='" . $SelectedUser . "'
										AND glaccountusers.accountcode=chartmaster.accountcode)
					AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
				ORDER BY accountcode";
	$Result = DB_query($SQL);
	echo '<fieldset>
			<legend>', _('General Ledger Accounts'), '</legend>';

	if (DB_num_rows($Result) > 0) { // If the user does not have access permissions to one or more GL accounts:
		echo '<field>
				<label for="SelectedGLAccount">', _('Add access permissions to a GL account'), ':</label>
				<select name="SelectedGLAccount">';
		if (!isset($_POST['SelectedGLAccount'])) {
			echo '<option selected="selected" value="">', _('Not Yet Selected'), '</option>';
		}
		while ($MyRow = DB_fetch_array($Result)) {
			if (isset($_POST['SelectedGLAccount']) and $MyRow['accountcode'] == $_POST['SelectedGLAccount']) {
				echo '<option selected="selected" value="', $MyRow['accountcode'], '">', $MyRow['accountcode'], ' - ', $MyRow['accountname'], '</option>';
			} else {
				echo '<option value="', $MyRow['accountcode'], '">', $MyRow['accountcode'], ' - ', $MyRow['accountname'], '</option>';
			}
		}
		echo '</select>
			</field>';

		echo '</fieldset>';

		echo '<div class="centre">
				<input type="submit" name="submit" value="Accept" />
			</div>';

	} else { // If the user has access permissions to all GL accounts:
		prnMsg(_('User has access permissions to all GL accounts'), 'info');
	}

	echo '<div class="page_title_text noPrint">
			<a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '"><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/user.png" /> ', _('Select A Different User'), '</a>
		</div>'; // "Select A Different User" button.
	echo '</form>';
}

include ('includes/footer.php');
?>