<?php
include ('includes/session.php');
$Title = _('Internal Stock Categories Requests By Security Role Maintenance ');

include ('includes/header.php');

if (isset($_POST['SelectedType'])) {
	$SelectedType = mb_strtoupper($_POST['SelectedType']);
} elseif (isset($_GET['SelectedType'])) {
	$SelectedType = mb_strtoupper($_GET['SelectedType']);
} else {
	$SelectedType = '';
}

if (!isset($_GET['delete']) and (ContainsIllegalCharacters($SelectedType) or mb_strpos($SelectedType, ' ') > 0)) {
	$InputError = 1;
	prnMsg(_('The Selected type cannot contain any of the following characters') . ' " \' - &amp; ' . _('or a space'), 'error');
}
if (isset($_POST['SelectedRole'])) {
	$SelectedRole = mb_strtoupper($_POST['SelectedRole']);
} elseif (isset($_GET['SelectedRole'])) {
	$SelectedRole = mb_strtoupper($_GET['SelectedRole']);
}

if (isset($_POST['Cancel'])) {
	unset($SelectedRole);
	unset($SelectedType);
}

if (isset($_POST['Process'])) {

	if ($_POST['SelectedRole'] == '') {
		prnMsg(_('You have not selected a security role to maintain the internal stock categories on'), 'error');
		unset($SelectedRole);
		unset($_POST['SelectedRole']);
	}
}

if (isset($_POST['submit'])) {

	$InputError = 0;

	if ($_POST['SelectedCategory'] == '') {
		$InputError = 1;
		prnMsg(_('You have not selected a stock category to be added as internal to this security role'), 'error');
		unset($SelectedRole);
	}

	if ($InputError != 1) {

		// First check the type is not being duplicated
		$CheckSQL = "SELECT count(*)
				 FROM internalstockcatrole
				 WHERE secroleid= '" . $_POST['SelectedRole'] . "'
				 AND categoryid = '" . $_POST['SelectedCategory'] . "'";

		$CheckResult = DB_query($CheckSQL);
		$CheckRow = DB_fetch_row($CheckResult);

		if ($CheckRow[0] > 0) {
			$InputError = 1;
			prnMsg(_('The Stock Category') . ' ' . $_POST['categoryid'] . ' ' . _('already allowed as internal for this security role'), 'error');
		} else {
			// Add new record on submit
			$SQL = "INSERT INTO internalstockcatrole (secroleid,
													categoryid
												) VALUES (
													'" . $_POST['SelectedRole'] . "',
													'" . $_POST['SelectedCategory'] . "'
												)";

			$Msg = _('Stock Category') . ': ' . stripslashes($_POST['SelectedCategory']) . ' ' . _('has been allowed to user role') . ' ' . $_POST['SelectedRole'] . ' ' . _('as internal');
			$CheckSQL = "SELECT count(secroleid)
							FROM securityroles";
			$Result = DB_query($CheckSQL);
			$MyRow = DB_fetch_row($Result);
		}
	}

	if ($InputError != 1) {
		//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);
		prnMsg($Msg, 'success');
		unset($_POST['SelectedCategory']);
	}

} elseif (isset($_GET['delete'])) {
	$SQL = "DELETE FROM internalstockcatrole
		WHERE secroleid='" . $SelectedRole . "'
		AND categoryid='" . $SelectedType . "'";

	$ErrMsg = _('The Stock Category by Role record could not be deleted because');
	$Result = DB_query($SQL, $ErrMsg);
	prnMsg(_('Internal Stock Category') . ' ' . stripslashes($SelectedType) . ' ' . _('for user role') . ' ' . $SelectedRole . ' ' . _('has been deleted'), 'success');
	unset($_GET['delete']);
}

if (!isset($SelectedRole)) {

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/user.png" title="', _('Select a user role'), '" alt="" />', ' ', _('Select a user role') . '
		</p>';

	echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<fieldset>
			<legend>', _('Select Role'), '</legend>'; //Main table
	$SQL = "SELECT secroleid,
					secrolename
			FROM securityroles";
	$Result = DB_query($SQL);

	echo '<field>
			<label for="SelectedRole">', _('Select User Role'), ':</label>
			<select required="required" name="SelectedRole">';
	echo '<option value="">', _('Not Yet Selected'), '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedRole) and $MyRow['secroleid'] == $SelectedRole) {
			echo '<option selected="selected" value="', $MyRow['secroleid'], '">', $MyRow['secroleid'], ' - ', $MyRow['secrolename'], '</option>';
		} else {
			echo '<option value="', $MyRow['secroleid'], '">', $MyRow['secroleid'], ' - ', $MyRow['secrolename'], '</option>';
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
if (isset($_POST['process']) or isset($SelectedRole)) {

	$SQL = "SELECT secrolename FROM securityroles WHERE secroleid='" . $SelectedRole . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$SelectedRoleName = $MyRow['secrolename'];

	echo '<div class="toplink"><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">', _('Select another role'), '</a></div>';
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/inventory.png" title="', _('Select a stock category'), '" alt="" />', _('Select a stock category'), '
		</p>';

	if (!isset($_GET['delete'])) {

		echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
		echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
		echo '<fieldset>
				<legend>', _('Select Category'), '</legend>'; //Main table
		$SQL = "SELECT categoryid,
						categorydescription
				FROM stockcategory";
		$Result = DB_query($SQL);
		echo '<field>
				<label for="SelectedCategory">', _('Select Stock Category Code'), ':</label>
				<select name="SelectedCategory">';
		if (!isset($_POST['SelectedCategory'])) {
			echo '<option selected="selected" value="">', _('Not Yet Selected'), '</option>';
		}
		while ($MyRow = DB_fetch_array($Result)) {
			if (isset($_POST['SelectedCategory']) and $MyRow['categoryid'] == $_POST['SelectedCategory']) {
				echo '<option selected="selected" value="', $MyRow['categoryid'], '">', $MyRow['categoryid'], ' - ', $MyRow['categorydescription'], '</option>';
			} else {
				echo '<option value="', $MyRow['categoryid'], '">', $MyRow['categoryid'], ' - ', $MyRow['categorydescription'], '</option>';
			}
		} //end while loop
		echo '</select>
			</field>';

		echo '</fieldset>'; // close main table
		echo '<div class="centre">
				<input type="submit" name="submit" value="', _('Accept'), '" />
				<input type="reset" name="Cancel" value="', _('Cancel'), '" />
			</div>';

		echo '<input type="hidden" name="SelectedRole" value="', $SelectedRole, '" />';

		echo '</form>';

		$SQL = "SELECT internalstockcatrole.categoryid,
					stockcategory.categorydescription
				FROM internalstockcatrole
				INNER JOIN stockcategory
					ON internalstockcatrole.categoryid=stockcategory.categoryid
				WHERE internalstockcatrole.secroleid='" . $SelectedRole . "'
				ORDER BY internalstockcatrole.categoryid ASC";

		$Result = DB_query($SQL);

		echo '<table>
				<thead>
					<tr>
						<th colspan="3"><h3>', _('Internal Stock Categories Allowed to user role'), ' - ', $SelectedRoleName, '</h3></th>
					</tr>
					<tr>
						<th class="SortedColumn">', _('Category Code'), '</th>
						<th class="SortedColumn">', _('Description'), '</th>
						<th></th>
					</tr>
				</thead>
				<tbody>';

		while ($MyRow = DB_fetch_array($Result)) {

			echo '<tr class="striped_row">
					<td>', $MyRow['categoryid'], '</td>
					<td>', $MyRow['categorydescription'], '</td>
					<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedType=', urlencode($MyRow['categoryid']), '&amp;delete=yes&amp;SelectedRole=', urlencode($SelectedRole), '" onclick="return MakeConfirm(\'', _('Are you sure you wish to delete this internal stock category code?'), '\', \'Confirm Delete\', this);">', _('Delete'), '</a></td>
				</tr>';
		}
		//END WHILE LIST LOOP
		echo '<tbody>
			</table>';
	} // end if user wish to delete
	
}

include ('includes/footer.php');
?>