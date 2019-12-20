<?php
include ('includes/session.php');
$Title = _('Mailing Group Maintenance');
include ('includes/header.php');

echo '<p class= "page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/group_add.png" alt="" />', $Title, '
	</p>';

//show the mail group existed only when user request this page first
if (!isset($_POST['Clean']) and !isset($_GET['Delete']) and !isset($_GET['Edit']) and !isset($_GET['Add']) and !isset($_GET['Remove'])) {
	GetMailGroup();
}
//validate the input
if (isset($_POST['Enter'])) { //user has input a new value
	$InputError = 0;
	if (!empty($_POST['MailGroup']) and mb_strlen(trim($_POST['MailGroup'])) <= 100) {
		$MailGroup = strtolower(trim($_POST['MailGroup']));
	} else {
		$InputError = 1;
		prnMsg(_('The Mail Group should be less than 100 characters and cannot be null'), 'error');
		exit;
		include ('includes/footer.php');
	}
	if ($InputError == 0) {
		$SQL = "INSERT INTO mailgroups (groupname) VALUES ('" . $MailGroup . "')";
		$ErrMsg = _('Failed to add new mail group');
		$Result = DB_query($SQL, $ErrMsg);
		GetMailGroup();

	}

} //end of handling new mail group input
//Add the new users to the mail group
if (isset($_GET['Add']) and isset($_GET['UserId'])) {
	if (isset($_GET['UserId']) and mb_strlen($_GET['UserId']) < 21) {
		$UserId = $_GET['UserId'];
	} else {
		prnMsg(_('The User Id should be set and must be less than 21 characters'), 'error');
		include ('includes/footer.php');
		exit;
	}
	if (isset($_GET['GroupId']) and is_numeric($_GET['GroupId'])) {
		$GroupId = (int)$_GET['GroupId'];
	} else {
		prnMsg(_('The Group Id must be integer'), 'error');
		include ('includes/footer.php');
		exit;
	}
	if (!empty($_GET['GroupName']) and mb_strlen($_GET['GroupName']) <= 100) {
		$GroupName = trim($_GET['GroupName']);

	} else {
		prnMsg(_('The Group name should be set and must be less than 100 characters'), 'error');
		include ('includes/footer.php');
		exit;
	}
	$SQL = "INSERT INTO mailgroupdetails (groupname, userid) VALUES ('" . $GroupName . "',
									'" . $UserId . "')";
	$ErrMsg = _('Failed to add users to mail group');
	$Result = DB_query($SQL, $ErrMsg);
	GetUsers($GroupId, $GroupName);
}

//User try to delete one of the record
if (isset($_GET['Delete'])) {
	if (is_numeric($_GET['Id'])) {
		$id = (int)$_GET['Id'];
		$SQL = "DELETE FROM mailgroups WHERE id = '" . $id . "'";
		$ErrMsg = _('Failed to delete the mail group which id is ' . $id);
		$Result = DB_query($SQL, $ErrMsg);
		GetMailGroup();
	} else {
		prnMsg(_('The group id must be numeric'), 'error');
		include ('includes/footer.php');
		exit;

	}

}

//User try to Edit the details of the mail groups
if (isset($_GET['Edit'])) {
	//First Get mailing list from database;
	if (isset($_GET['GroupId']) and is_numeric($_GET['GroupId'])) {
		$GroupId = (int)$_GET['GroupId'];
		if (isset($_GET['GroupName']) and mb_strlen($_GET['GroupName']) <= 100) {
			$GroupName = trim($_GET['GroupName']);
		} else {
			prnMsg(_('The Group Name should be less than 100 characters long'), 'error');
			include ('includes/footer.php');
			exit;
		}
	} else {
		prnMsg(_('The page must be called with a group id'), 'error');
		include ('includes/footer.php');
		exit;
	}
	GetUsers($GroupId, $GroupName);
	include ('includes/footer.php');
}

//Users remove one user from the group
if (isset($_GET['Remove'])) {
	if (!empty($_GET['GroupName']) and mb_strlen($_GET['GroupName']) <= 100) {
		$GroupName = trim($_GET['GroupName']);
	} else {
		prnMsg(_('The Group Name should be less than 100 characters'), 'error');
		include ('includes/footer.php');
		exit;

	}
	if (isset($_GET['UserId']) and mb_strlen($_GET['UserId']) < 21) {
		$UserId = $_GET['UserId'];
	} else {
		prnMsg(_('The User Id should be set and must be less than 21 characters'), 'error');
		include ('includes/footer.php');
		exit;
	}

	if (isset($_GET['GroupId']) and is_numeric($_GET['GroupId'])) {
		$GroupId = (int)$_GET['GroupId'];
		if (isset($_GET['GroupName']) and mb_strlen($_GET['GroupName']) <= 100) {
			$GroupName = trim($_GET['GroupName']);
		} else {
			prnMsg(_('The Group Name should be less than 100 characters'), 'error');
			include ('includes/footer.php');
			exit;
		}

	}
	$SQL = "DELETE FROM mailgroupdetails WHERE userid = '" . $UserId . "' AND groupname = '" . $GroupName . "'";
	$ErrMsg = 'Failed to delete the userid ' . $UserId . ' from group ' . $GroupName;
	$Result = DB_query($SQL, $ErrMsg);
	GetUsers($GroupId, $GroupName);
}

if (!isset($_GET['Edit'])) { //display the input form
	echo '<form id="MailGroups" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<fieldset>
			<legend>', _('Mailing Group Details'), '</legend>
			<field>
				<label for="MailGroup">', _('Mail Group'), '</label>
				<input type="text" autofocus="autofocus" name="MailGroup" required="required" maxlength="100" size="20" />
			</field>
		</fieldset>';
	echo '<div class="centre">
			<input type="hidden" name="Clean" value="1" />
			<input type="submit" name="Enter" value="', _('Submit'), '" />
		</div>
	</form>';
	include ('includes/footer.php');
}

function GetMailGroup() {
	//GET the mailing group data if there are any
	$SQL = "SELECT groupname, id FROM mailgroups ORDER BY groupname";
	$ErrMsg = _('Failed to retrieve mail groups information');
	$Result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($Result) != 0) {
		echo '<table>
				<tr>
					<th>', _('Mail Group'), '</th>
					<th></th>
					<th></th>
				</tr>';

		while ($MyRow = DB_fetch_array($Result)) {
			echo '<tr class="striped_row">
					<td>', $MyRow['groupname'], '</td>
					<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?GroupId=', urlencode($MyRow['id']), '&amp;Edit=1&amp;GroupName=', urlencode($MyRow['groupname']), '" >', _('Edit'), '</a></td>
					<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?Id=', urlencode($MyRow['id']), '&amp;Delete=1" onclick="return MakeConfirm(\'', _('Are you sure you wish to delete this group?'), '\', \'Confirm Delete\', this);">', _('Delete'), '</a></td>
				</tr>';
		}
		echo '</table>';
	}
}

function GetUsers($GroupId, $GroupName) {
	$SQL = "SELECT userid FROM mailgroups INNER JOIN mailgroupdetails ON mailgroups.groupname=mailgroupdetails.groupname WHERE mailgroups.id = '" . $GroupId . "'";
	$ErrMsg = _('Failed to retrieve userid');
	$Result = DB_query($SQL, $ErrMsg);

	$UsersAssigned = array();
	if (DB_num_rows($Result) != 0) {
		$i = 0;
		while ($MyRow = DB_fetch_array($Result)) {
			$UsersAssigned[$i] = $MyRow['userid'];
			++$i;
		}
	}

	$SQL = "SELECT userid, realname, email FROM www_users ORDER BY realname";
	$ErrMsg = _('Failed to retrieve user information');
	$Result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($Result) != 0) {
		echo '<p class="page_title_text">', _('Current Mail Group'), ' : ', stripslashes($GroupName), '</p>';

		echo '<table>
				<tr>
					<th colspan="3">', _('Assigned Users'), '</th>
					<th colspan="3">', _('Available Users'), '</th>
				</tr>';
		$k = 0;
		while ($MyRow = DB_fetch_array($Result)) {

			if (in_array($MyRow['userid'], $UsersAssigned)) {
				echo '<tr class="striped_row">
						<td>', $MyRow['userid'], '</td>
						<td>', $MyRow['realname'], '</td>
						<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?UserId=', urlencode(stripslashes($MyRow['userid'])), '&amp;GroupName=', urlencode(stripslashes($GroupName)), '&amp;Remove=1&amp;GroupId=', urlencode(stripslashes($GroupId)), '" onclick="return MakeConfirm(\'Are you sure you want to remove this user?\', \'Confirm Delete\', this); ">', _('Remove'), '</a></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>';
			} else {
				echo '<tr class="striped_row">
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>', $MyRow['userid'], '</td>
						<td>', $MyRow['realname'], '</td>
						<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?UserId=', urlencode(stripslashes($MyRow['userid'])), '&amp;Add=1&amp;GroupName=', urlencode(stripslashes($GroupName)), '&amp;GroupId=', urlencode(stripslashes($GroupId)), '">', _('Add'), '</a></td>';
			}

			echo '</tr>';
		}
		echo '</table>';

		echo '<div class="centre"><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">', _('View All Groups'), '</a></div>';
	} else {
		prnMsg(_('There are no user set up, please set up user first'), 'error');
		include ('includes/footer.php');
		exit;
	}
}
?>