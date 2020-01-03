<?php
include ('includes/session.php');
$Title = _('Inventory Location Authorised Users Maintenance');
$ViewTopic = 'Inventory'; // Filename in ManualContents.php's TOC.
$BookMark = 'LocationUsers'; // Anchor's id in the manual's html document.
include ('includes/header.php');

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/money_add.png" data-title="', _('Location Authorised Users'), '" alt="" />', ' ', $Title, '
	</p>';

if (isset($_POST['SelectedUser'])) {
	$SelectedUser = mb_strtoupper($_POST['SelectedUser']);
} elseif (isset($_GET['SelectedUser'])) {
	$SelectedUser = mb_strtoupper($_GET['SelectedUser']);
} else {
	$SelectedUser = '';
}

if (isset($_POST['SelectedLocation'])) {
	$SelectedLocation = mb_strtoupper($_POST['SelectedLocation']);
} elseif (isset($_GET['SelectedLocation'])) {
	$SelectedLocation = mb_strtoupper($_GET['SelectedLocation']);
}

if (isset($_POST['Cancel'])) {
	unset($SelectedLocation);
	unset($SelectedUser);
}

if (isset($_POST['Process'])) {
	if ($_POST['SelectedLocation'] == '') {
		prnMsg(_('You have not selected any Location'), 'error');
		echo '<br />';
		unset($SelectedLocation);
		unset($_POST['SelectedLocation']);
	}
}

if (isset($_POST['submit'])) {

	$InputError = 0;

	if ($_POST['SelectedUser'] == '') {
		$InputError = 1;
		prnMsg(_('You have not selected an user to be authorised to use this Location'), 'error');
		echo '<br />';
		unset($SelectedLocation);
	}

	if ($InputError != 1) {

		// First check the user is not being duplicated
		$CheckSql = "SELECT count(*)
			     FROM locationusers
			     WHERE loccode= '" . $_POST['SelectedLocation'] . "'
				 AND userid = '" . $_POST['SelectedUser'] . "'";

		$CheckResult = DB_query($CheckSql);
		$CheckRow = DB_fetch_row($CheckResult);

		if ($CheckRow[0] > 0) {
			$InputError = 1;
			prnMsg(_('The user') . ' ' . $_POST['SelectedUser'] . ' ' . _('is already authorised to use this Location'), 'error');
		} else {
			// Add new record on submit
			$SQL = "INSERT INTO locationusers (loccode,
												userid,
												canview,
												canupd)
										VALUES ('" . $_POST['SelectedLocation'] . "',
												'" . $_POST['SelectedUser'] . "',
												'1',
												'1')";

			$Msg = _('User') . ': ' . $_POST['SelectedUser'] . ' ' . _('authorisation to use') . ' ' . $_POST['SelectedLocation'] . ' ' . _('Location has been changed');
			$Result = DB_query($SQL);
			prnMsg($Msg, 'success');
			unset($_POST['SelectedUser']);
		}
	}
} elseif (isset($_GET['delete'])) {
	$SQL = "DELETE FROM locationusers
		WHERE loccode='" . $SelectedLocation . "'
		AND userid='" . $SelectedUser . "'";

	$ErrMsg = _('The Location user record could not be deleted because');
	$Result = DB_query($SQL, $ErrMsg);
	prnMsg(_('User') . ' ' . $SelectedUser . ' ' . _('has been un-authorised to use') . ' ' . $SelectedLocation . ' ' . _('Location'), 'success');
	unset($_GET['delete']);
} elseif (isset($_GET['ToggleUpdate'])) {
	$SQL = "UPDATE locationusers
		SET canupd='" . $_GET['ToggleUpdate'] . "'
		WHERE loccode='" . $SelectedLocation . "'
		AND userid='" . $SelectedUser . "'";

	$ErrMsg = _('The Location user record could not be deleted because');
	$Result = DB_query($SQL, $ErrMsg);
	prnMsg(_('User') . ' ' . $SelectedUser . ' ' . _('has been un-authorised to update') . ' ' . $SelectedLocation . ' ' . _('Location'), 'success');
	unset($_GET['ToggleUpdate']);
}

if (!isset($SelectedLocation)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedUser will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	 then none of the above are true. These will call the same page again and allow update/input or deletion of the records*/
	echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />
			<fieldset>
				<legend>', _('Location'), '</legend>
				<field>
					<label for="SelectedLocation">', _('Select the location'), '</label>
					<select name="SelectedLocation" autofocus="autofocus">';

	$SQL = "SELECT loccode,
					locationname
			FROM locations";

	$Result = DB_query($SQL);
	echo '<option value="">', _('Not Yet Selected'), '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedLocation) and $MyRow['loccode'] == $SelectedLocation) {
			echo '<option selected="selected" value="', $MyRow['loccode'], '">', $MyRow['loccode'], ' - ', $MyRow['locationname'], '</option>';
		} else {
			echo '<option value="', $MyRow['loccode'], '">', $MyRow['loccode'], ' - ', $MyRow['locationname'], '</option>';
		}

	} //end while loop
	echo '</select>
		</field>';

	echo '</fieldset>'; // close main table
	echo '<div class="centre">
			<input type="submit" name="Process" value="', _('Accept'), '" />
			<input type="reset" name="Cancel" value="', _('Cancel'), '" />
		</div>';

	echo '</form>';

}

//end of ifs and buts!
if (isset($_POST['process']) or isset($SelectedLocation)) {
	$SQLName = "SELECT locationname
			FROM locations
			WHERE loccode='" . $SelectedLocation . "'";
	$Result = DB_query($SQLName);
	$MyRow = DB_fetch_array($Result);
	$SelectedLocationName = $MyRow['locationname'];

	echo '<div class="centre">
			<a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">', _('Authorised users for'), ' ', $SelectedLocationName, ' ', _('Location'), '</a>
		</div>';

	echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<input type="hidden" name="SelectedLocation" value="', $SelectedLocation, '" />';

	$SQL = "SELECT locationusers.userid,
					canview,
					canupd,
					www_users.realname
			FROM locationusers INNER JOIN www_users
			ON locationusers.userid=www_users.userid
			WHERE locationusers.loccode='" . $SelectedLocation . "'
			ORDER BY locationusers.userid ASC";

	$Result = DB_query($SQL);

	echo '<table>
			<thead>
				<tr>
					<th colspan="6"><h3>', _('Authorised users for Location'), ': ', $SelectedLocationName, '</h3></th>
				</tr>
				<tr>
					<th class="SortedColumn">', _('User Code'), '</th>
					<th class="SortedColumn">', _('User Name'), '</th>
					<th>', _('View'), '</th>
					<th>', _('Update'), '</th>
					<th colspan="2"></th>
				</tr>
			</thead>
			<tbody>';

	while ($MyRow = DB_fetch_array($Result)) {

		if ($MyRow['canupd'] == 1) {
			$ToggleText = '<td><a href="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '?SelectedUser=' . urlencode($MyRow['userid']) . '&amp;ToggleUpdate=0&amp;SelectedLocation=' . urlencode($SelectedLocation) . '" onclick="return confirm(\'' . _('Are you sure you wish to remove Update for this user?') . '\');">' . _('Remove Update') . '</a></td>';
			$Update = _('Yes');
		} else {
			$ToggleText = '<td><a href="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '?SelectedUser=' . urlencode($MyRow['userid']) . '&amp;ToggleUpdate=1&amp;SelectedLocation=' . urlencode($SelectedLocation) . '" onclick="return confirm(\'' . _('Are you sure you wish to add Update for this user?') . '\');">' . _('Add Update') . '</a></td>';
			$Update = _('No');
		}

		if ($MyRow['canview'] == 1) {
			$CanView = _('Yes');
		} else {
			$CanView = _('No');
		}

		echo '<tr class="striped_row">
				<td>', $MyRow['userid'], '</td>
				<td>', $MyRow['realname'], '</td>
				<td>', $CanView, '</td>
				<td>', $Update, '</td>', $ToggleText, '
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedUser=', $MyRow['userid'], '&amp;delete=yes&amp;SelectedLocation=' . $SelectedLocation . '" onclick="return confirm(\'' . _('Are you sure you wish to un-authorise this user?') . '\');">' . _('Un-authorise') . '</a></td>
			</tr>';
	}
	//END WHILE LIST LOOP
	echo '</tbody>
		</table>';

	if (!isset($_GET['delete'])) {

		echo '<fieldset >
				<legend>', _('Users'), '</legend>
				<field>
					<label for="SelectedUser">', _('Select User'), ':</label>
					<select name="SelectedUser">';

		$SQL = "SELECT userid,
						realname
							FROM www_users
							WHERE NOT EXISTS (SELECT locationusers.userid
											FROM locationusers
											WHERE locationusers.loccode='" . $SelectedLocation . "'
												AND locationusers.userid=www_users.userid)";

		$Result = DB_query($SQL);
		if (!isset($_POST['SelectedUser'])) {
			echo '<option selected="selected" value="">', _('Not Yet Selected'), '</option>';
		}
		while ($MyRow = DB_fetch_array($Result)) {
			if (isset($_POST['SelectedUser']) and $MyRow['userid'] == $_POST['SelectedUser']) {
				echo '<option selected="selected" value="', $MyRow['userid'], '">', $MyRow['userid'], ' - ', $MyRow['realname'], '</option>';
			} else {
				echo '<option value="', $MyRow['userid'], '">', $MyRow['userid'], ' - ', $MyRow['realname'], '</option>';
			}
		} //end while loop
		echo '</select>
			<fieldhelp>', _('Select the user to Authorise'), '</fieldhelp>
		</field>';

		echo '</fieldset>'; // close main table
		echo '<div class="centre">
				<input type="submit" name="submit" value="', _('Accept'), '" />
				<input type="submit" name="Cancel" value="', _('Cancel'), '" />
			</div>';

		echo '</form>';

	} // end if user wish to delete
	
}

include ('includes/footer.php');
?>