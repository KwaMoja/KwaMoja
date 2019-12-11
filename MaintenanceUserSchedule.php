<?php
include ('includes/session.php');

$Title = _('Fixed Assets Maintenance Schedule');
include ('includes/header.php');

$ViewTopic = 'FixedAssets';
$BookMark = 'AssetMaintenance';

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/group_add.png" title="', _('Search'), '" alt="" />', ' ', $Title, '
	</p>';

if (isset($_GET['Complete'])) {
	$Result = DB_query("UPDATE fixedassettasks SET lastcompleted=CURRENT_DATE WHERE taskid='" . $_GET['TaskID'] . "'");
}

$SQL = "SELECT taskid,
				fixedassettasks.assetid,
				description,
				taskdescription,
				frequencydays,
				lastcompleted,
				ADDDATE(lastcompleted,frequencydays) AS duedate,
				userresponsible,
				realname,
				manager
		FROM fixedassettasks
		INNER JOIN fixedassets
		ON fixedassettasks.assetid=fixedassets.assetid
		INNER JOIN www_users
		ON fixedassettasks.userresponsible=www_users.userid
		WHERE userresponsible='" . $_SESSION['UserID'] . "'
		OR manager = '" . $_SESSION['UserID'] . "'
		ORDER BY ADDDATE(lastcompleted,frequencydays) DESC";

$ErrMsg = _('The maintenance schedule cannot be retrieved because');
$Result = DB_query($SQL, $ErrMsg);

echo '<table>
		<tr>
			<th>', _('Task ID'), '</th>
			<th>', _('Asset'), '</th>
			<th>', _('Description'), '</th>
			<th>', _('Last Completed'), '</th>
			<th>', _('Due By'), '</td>
			<th>', _('Person'), '</th>
			<th>', _('Manager'), '</th>
			<th>', _('Now Complete'), '</th>
		</tr>';

while ($MyRow = DB_fetch_array($Result)) {

	if ($MyRow['manager'] != '') {
		$ManagerResult = DB_query("SELECT realname FROM www_users WHERE userid='" . $MyRow['manager'] . "'");
		$ManagerRow = DB_fetch_array($ManagerResult);
		$ManagerName = $ManagerRow['realname'];
	} else {
		$ManagerName = _('No Manager Set');
	}

	echo '<tr class="striped_row">
			<td>', $MyRow['taskid'], '</td>
			<td>', $MyRow['description'], '</td>
			<td>', $MyRow['taskdescription'], '</td>
			<td>', ConvertSQLDate($MyRow['lastcompleted']), '</td>
			<td>', ConvertSQLDate($MyRow['duedate']), '</td>
			<td>', $MyRow['realname'], '</td>
			<td>', $ManagerName, '</td>
			<td><a href="', $RootPath, '/MaintenanceUserSchedule.php?Complete=Yes&amp;TaskID=', urlencode($MyRow['taskid']), '" onclick="return confirm(\'', _('Are you sure you wish to mark this maintenance task as completed?'), '\');">', _('Mark Completed'), '</a></td>
		</tr>';
}

echo '</table>';

include ('includes/footer.php');
?>