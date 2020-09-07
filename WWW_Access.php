<?php
include ('includes/session.php');

$Title = _('Access Permissions Maintenance'); // Screen identificator.
$ViewTopic = 'SecuritySchema'; // Filename's id in ManualContents.php's TOC.
$BookMark = 'WWW_Access'; // Anchor's id in the manual's html document.
include ('includes/header.php');

echo '<p class="page_title_text" >
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/group_add.png" title="', _('Search'), '" alt="" />', ' ', $Title, '
	</p>';

if ($AllowDemoMode == true) {
	prnMsg(_('The the system is in demo mode and the security model administration is disabled'), 'warn');
	include ('includes/footer.php');
	exit;
}

if (isset($_GET['SelectedRole'])) {
	$SelectedRole = $_GET['SelectedRole'];
} elseif (isset($_POST['SelectedRole'])) {
	$SelectedRole = $_POST['SelectedRole'];
}

if (isset($_POST['submit']) or isset($_GET['remove']) or isset($_GET['add'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;
	if ($AllowDemoMode) {
		$InputError = 1;
		prnMsg('The demo functionality is crippled to prevent access problems. No changes will be made', 'warn');
	}

	/* actions to take once the user has clicked the submit button
	 ie the page has called itself with some user input */
	//first off validate inputs sensible
	if (isset($_POST['SecRoleName']) and mb_strlen($_POST['SecRoleName']) < 4) {
		$InputError = 1;
		prnMsg(_('The role description entered must be at least 4 characters long'), 'error');
	}

	// if $_POST['SecRoleName'] then it is a modifications on a SecRole
	// else it is either an add or remove of a page token
	unset($SQL);
	if (isset($_POST['SecRoleName'])) { // Update or Add Security Headings
		if (isset($SelectedRole)) { // Update Security Heading
			$SQL = "UPDATE securityroles SET secrolename = '" . $_POST['SecRoleName'] . "',
											clinician='" . $_POST['Clinician'] . "'
					WHERE secroleid = '" . $SelectedRole . "'";
			$ErrMsg = _('The update of the security role description failed because');
			$ResMsg = _('The Security role description was updated.');
		} else { // Add Security Heading
			$SQL = "INSERT INTO securityroles (secrolename, clinician) VALUES ('" . $_POST['SecRoleName'] . "', '" . $_POST['Clinician'] . "')";
			$ErrMsg = _('The insert of the security role failed because');
			$ResMsg = _('The Security role was created.');
		}
		unset($_POST['SecRoleName']);
	} elseif (isset($SelectedRole)) {
		$PageTokenId = $_GET['PageToken'];
		if (isset($_GET['add'])) { // updating Security Groups add a page token
			$SQL = "INSERT INTO securitygroups (secroleid,
											tokenid)
									VALUES ('" . $SelectedRole . "',
											'" . $PageTokenId . "' )";
			$ErrMsg = _('The addition of the page group access failed because');
			$ResMsg = _('The page group access was added.');
		} elseif (isset($_GET['remove'])) { // updating Security Groups remove a page token
			$SQL = "DELETE FROM securitygroups
					WHERE secroleid = '" . $SelectedRole . "'
					AND tokenid = '" . $PageTokenId . "'";
			$ErrMsg = _('The removal of this page-group access failed because');
			$ResMsg = _('This page-group access was removed.');
		}
		unset($_GET['add']);
		unset($_GET['remove']);
		unset($_GET['PageToken']);
	}
	// Need to exec the query
	if (isset($SQL) and $InputError != 1) {
		$Result = DB_query($SQL, $ErrMsg);
		if ($Result) {
			prnMsg($ResMsg, 'success');
			if (isset($SelectedRole)) {
				$SQL = "DELETE FROM modules WHERE secroleid='" . $SelectedRole . "'";
				$Result = DB_query($SQL);
				$SQL = "DELETE FROM menuitems WHERE secroleid='" . $SelectedRole . "'";
				$Result = DB_query($SQL);
				$SQL = "INSERT INTO `modules` SELECT '" . $SelectedRole . "',
												modulelink,
												reportlink,
												modulename,
												sequence
											FROM modules
											WHERE secroleid=8";
				$Result = DB_query($SQL);
				$SQL = "INSERT INTO `menuitems` SELECT '" . $SelectedRole . "',
											modulelink,
											menusection,
											caption,
											url,
											sequence
										FROM menuitems
										WHERE secroleid=8";
				$Result = DB_query($SQL);
			}
		}
	}
} elseif (isset($_GET['delete'])) {
	//the Security heading wants to be deleted but some checks need to be performed fist
	// PREVENT DELETES IF DEPENDENT RECORDS IN 'www_users'
	$SQL = "SELECT COUNT(*) FROM www_users WHERE fullaccess='" . $_GET['SelectedRole'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		prnMsg(_('Cannot delete this role because user accounts are setup using it'), 'warn');
		echo '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('user accounts that have this security role setting') . '</font>';
	} else {
		$SQL = "DELETE FROM securitygroups WHERE secroleid='" . $_GET['SelectedRole'] . "'";
		$Result = DB_query($SQL);
		$SQL = "DELETE FROM securityroles WHERE secroleid='" . $_GET['SelectedRole'] . "'";
		$Result = DB_query($SQL);
		prnMsg(stripslashes($_GET['SecRoleName']) . ' ' . _('security role has been deleted') . '!', 'success');

	} //end if account group used in GL accounts
	unset($SelectedRole);
	unset($_GET['SecRoleName']);
}

if (!isset($SelectedRole)) {

	/* If its the first time the page has been displayed with no parameters then none of the above are true and the list of Users will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$SQL = "SELECT secroleid,
			secrolename,
			clinician
		FROM securityroles
		ORDER BY secrolename";
	$Result = DB_query($SQL);

	echo '<table>
			<tr>
				<th>', _('Role'), '</th>
				<th>', _('Clinician'), '</th>
				<th colspan="2"></th>
			</tr>';

	while ($MyRow = DB_fetch_array($Result)) {

		/*The SecurityHeadings array is defined in config.php */
		if ($MyRow['clinician'] == 0) {
			$Clinician = _('No');
		} else {
			$Clinician = _('Yes');
		}

		echo '<tr class="striped_row">
				<td>', $MyRow['secrolename'], '</td>
				<td>', $Clinician, '</td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?&amp;SelectedRole=', urlencode($MyRow['secroleid']), '">', _('Edit'), '</a></td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?&amp;SelectedRole=', urlencode($MyRow['secroleid']), '&amp;delete=1&amp;SecRoleName=', urlencode($MyRow['secrolename']), '" onclick="return MakeConfirm(\'', _('Are you sure you wish to delete this role?'), '\', \'Confirm Delete\', this);">', _('Delete'), '</a></td>
			</tr>';

	} //END WHILE LIST LOOP
	echo '</table>';
} //end of ifs and buts!


if (isset($SelectedRole)) {
	echo '<div class="centre">
			<a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">', _('Review Existing Roles'), '</a>
		</div>';
}

if (isset($SelectedRole)) {
	//editing an existing role
	$SQL = "SELECT secroleid,
					secrolename,
					clinician
		FROM securityroles
		WHERE secroleid='" . $SelectedRole . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 0) {
		prnMsg(_('The selected role is no longer available.'), 'warn');
	} else {
		$MyRow = DB_fetch_array($Result);
		$_POST['SelectedRole'] = $MyRow['secroleid'];
		$_POST['SecRoleName'] = $MyRow['secrolename'];
		$_POST['Clinician'] = $MyRow['clinician'];
	}
}

echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
if (isset($_POST['SelectedRole'])) {
	echo '<input type="hidden" name="SelectedRole" value="', $_POST['SelectedRole'], '" />';
}
echo '<fieldset>
		<legend>', _('Security Role Name'), '</legend>';
if (!isset($_POST['SecRoleName'])) {
	$_POST['SecRoleName'] = '';
}
echo '<field>
		<label for="SecRoleName">', _('Role'), ':</label>
		<input type="text" name="SecRoleName" size="40" required="required" maxlength="40" value="', $_POST['SecRoleName'], '" />
	</field>';

echo '<field>
		<label for="Clinician">', _('Clinician'), '</label>
		<select name="Clinician">';
if ($_POST['Clinician'] == 0) {
	echo '<option value="0" selected="selected">', _('No'), '</option>';
	echo '<option value="1">', _('Yes'), '</option>';
} else {
	echo '<option value="0">', _('No'), '</option>';
	echo '<option value="1" selected="selected">', _('Yes'), '</option>';
}
echo '</select>
	<fieldhelp>', _('Is the role a clinical one. For instance, doctor, or a nurse'), '</fieldhelp>
</field>';

echo '</fieldset>
	<div class="centre">
		<input type="submit" name="submit" value="', _('Enter Role'), '" />
	</div>
</form>';

if (isset($SelectedRole)) {
	$SQL = "SELECT tokenid,
					tokenname
				FROM securitytokens";

	$SQLUsed = "SELECT tokenid FROM securitygroups WHERE secroleid='" . $SelectedRole . "'";

	$Result = DB_query($SQL);

	/*Make an array of the used tokens */
	$UsedResult = DB_query($SQLUsed);
	$TokensUsed = array();
	$i = 0;
	while ($MyRow = DB_fetch_row($UsedResult)) {
		$TokensUsed[$i] = $MyRow[0];
		++$i;
	}

	echo '<table><tr>';

	if (DB_num_rows($Result) > 0) {
		echo '<th colspan="3"><div class="centre">', _('Assigned Security Tokens'), '</div></th>';
		echo '<th colspan="3"><div class="centre">', _('Available Security Tokens'), '</div></th>';
	}
	echo '</tr>';

	while ($AvailRow = DB_fetch_array($Result)) {

		if (in_array($AvailRow['tokenid'], $TokensUsed)) {
			echo '<tr class="striped_row">
					<td>', $AvailRow['tokenid'], '</td>
					<td>', $AvailRow['tokenname'], '</td>
					<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedRole=', urlencode($SelectedRole), '&amp;remove=1&amp;PageToken=', urlencode($AvailRow['tokenid']), '">', _('Remove'), '</a></td>
					<td colspan="3">&nbsp;</td>';
		} else {
			echo '<tr class="striped_row">
					<td colspan="3">&nbsp;</td>
					<td>', $AvailRow['tokenid'], '</td>
					<td>', $AvailRow['tokenname'], '</td>
					<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '?SelectedRole=', urlencode($SelectedRole), '&amp;add=1&amp;PageToken=', urlencode($AvailRow['tokenid']), '">', _('Add'), '</a></td>';
		}
		echo '</tr>';
	}
	echo '</table>';
}

include ('includes/footer.php');

?>