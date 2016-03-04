<?php

include('includes/session.inc');
$Title = _('Internal Stock Categories Requests By Security Role Maintenance ');

include('includes/header.inc');

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
		echo prnMsg(_('You have not selected a security role to maintain the internal stock categories on'), 'error');
		echo '<br />';
		unset($SelectedRole);
		unset($_POST['SelectedRole']);
	}
}

if (isset($_POST['submit'])) {

	$InputError = 0;

	if ($_POST['SelectedCategory'] == '') {
		$InputError = 1;
		echo prnMsg(_('You have not selected a stock category to be added as internal to this security role'), 'error');
		echo '<br />';
		unset($SelectedRole);
	}

	if ($InputError != 1) {

		// First check the type is not being duplicated

		$checkSql = "SELECT count(*)
				 FROM internalstockcatrole
				 WHERE secroleid= '" . $_POST['SelectedRole'] . "'
				 AND categoryid = '" . $_POST['SelectedCategory'] . "'";

		$checkresult = DB_query($checkSql);
		$checkrow = DB_fetch_row($checkresult);

		if ($checkrow[0] > 0) {
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
			$checkSql = "SELECT count(secroleid)
							FROM securityroles";
			$Result = DB_query($checkSql);
			$row = DB_fetch_row($Result);
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

	echo '<form method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<table class="selection">'; //Main table

	$SQL = "SELECT secroleid,
					secrolename
			FROM securityroles";
	$Result = DB_query($SQL);

	echo '<tr>
			<td>', _('Select User Role'), ':</td>
			<td><select required="required" name="SelectedRole">';
	echo '<option value="">', _('Not Yet Selected'), '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedRole) and $MyRow['secroleid'] == $SelectedRole) {
			echo '<option selected="selected" value="', $MyRow['secroleid'], '">', $MyRow['secroleid'], ' - ', $MyRow['secrolename'], '</option>';
		} else {
			echo '<option value="', $MyRow['secroleid'], '">', $MyRow['secroleid'], ' - ', $MyRow['secrolename'], '</option>';
		}
	} //end while loop

	echo '</select>
			</td>
		</tr>';

	echo '</table>'; // close main table

	echo '<div class="centre">
			<input type="submit" name="Process" value="', _('Accept'), '" />
			<input type="submit" name="Cancel" value="', _('Cancel'), '" />
		</div>';

	echo '</form>';

}

//end of ifs and buts!
if (isset($_POST['process']) or isset($SelectedRole)) {

	echo '<div class="toplink"><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">', _('Select another role'), '</a></div>';
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/inventory.png" title="', _('Select a stock category'), '" alt="" />', _('Select a stock category'), '
		</p>';

	if (!isset($_GET['delete'])) {

		echo '<form method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">';
		echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
		echo '<table class="selection">'; //Main table

		echo '<tr>
				<td>', _('Select Stock Category Code'), ':</td>
				<td><select name="SelectedCategory">';

		$SQL = "SELECT categoryid,
						categorydescription
				FROM stockcategory";

		$Result = DB_query($SQL);
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
				</td>
			</tr>';

		echo '</table>'; // close main table

		echo '<div class="centre">
				<input type="submit" name="submit" value="', _('Accept'), '" />
				<input type="submit" name="Cancel" value="', _('Cancel'), '" />
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

		echo '<table class="selection">
				<tr>
					<th colspan="3"><h3>', _('Internal Stock Categories Allowed to user role'), ' ', $SelectedRole, '</h3></th>
				</tr>
				<tr>
					<th>', _('Category Code'), '</th>
					<th>', _('Description'), '</th>
				</tr>';

		$k = 0; //row colour counter

		while ($MyRow = DB_fetch_array($Result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}

			echo '<td>', $MyRow['categoryid'], '</td>
				<td>', $MyRow['categorydescription'], '</td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '?SelectedType=', urlencode($MyRow['categoryid']), '&amp;delete=yes&amp;SelectedRole=', urlencode($SelectedRole), '" onclick="return MakeConfirm(\'', _('Are you sure you wish to delete this internal stock category code?'), '\', \'Confirm Delete\', this);">', _('Delete'), '</a></td>
			</tr>';
		}
		//END WHILE LIST LOOP
		echo '</table>';
	} // end if user wish to delete
}

include('includes/footer.inc');
?>