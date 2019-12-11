<?php
$PageSecurity = 0;

include ('includes/session.php');
$Title = _('Dashboard');
$ViewTopic = 'Dashboard';
$BookMark = 'MainScreen';
include ('includes/header.php');

$DashBoardURL = $_SERVER['REQUEST_URI'];

echo '<link href="', $RootPath, '/dashboard/css/dashboard.css?v=1" rel="stylesheet" type="text/css" media="screen" />';

$SQL = "SELECT scripts FROM dashboard_users WHERE userid = '" . $_SESSION['UserID'] . "' ";

$Result = DB_query($SQL);

$MyRow = DB_fetch_array($Result);
$ScriptArray = explode(',', $MyRow['scripts']);

$UserSQL = "SELECT scripts FROM dashboard_users WHERE userid = '" . $_SESSION['UserID'] . "' ";
$Result = DB_query($UserSQL);
if (DB_num_rows($Result) == 0) {
	$InsertSQL = "INSERT INTO dashboard_users VALUES(null, '" . $_SESSION['UserID'] . "', '')";
	$InsertResult = DB_query($InsertSQL);
}

if (isset($_GET['Remove'])) {
	foreach ($ScriptArray as $Key => $Value) {
		if ($Value == $_GET['Remove']) {
			unset($ScriptArray[$Key]);
		}
	}
	$UpdateSQL = "UPDATE dashboard_users SET scripts='" . implode(',', $ScriptArray) . "' WHERE userid = '" . $_SESSION['UserID'] . "'";
	$UpdateResult = DB_query($UpdateSQL);
}

if (isset($_POST['Reports']) and count($ScriptArray) < 7) {
	$ScriptArray[] = $_POST['Reports'];
	asort($ScriptArray);
	$UpdateSQL = "UPDATE dashboard_users SET scripts='" . implode(',', $ScriptArray) . "' WHERE userid = '" . $_SESSION['UserID'] . "' ";
	$UpdateResult = DB_query($UpdateSQL);
} else if (isset($_POST['Reports']) and count($ScriptArray) == 7) {
	prnMsg(_('A maximum of 6 reports is allowd on each users dashboard'), 'warn');
}

$SQL = "SELECT id,
				scripts,
				pagesecurity,
				description
			FROM dashboard_scripts";
$Result = DB_query($SQL);

$i = 0;
echo '<table>
		<tr>';
while ($MyRow = DB_fetch_array($Result)) {
	if (in_array($MyRow['id'], $ScriptArray) and in_array($MyRow['pagesecurity'], $_SESSION['AllowedPageSecurityTokens'])) {
		echo '<td class="dashboard_cell" id="dashboard_cell', $i, '" title="', $MyRow['scripts'], '" onload="">';
		include ('dashboard/' . $MyRow['scripts']);
		echo '</td>';
		if ($i == 2) {
			echo '</tr><tr>';
		}
		++$i;
	}
}
echo '</tr>
	</table>';
DB_data_seek($Result, 0);

echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

echo '<fieldset>
		<field>
			<label for="Reports">', _('Add reports to your dashboard'), '</label>
			<select name="Reports" onchange="ReloadForm(submit)">
			<option value=""></option>';
while ($MyRow = DB_fetch_array($Result)) {
	if (!in_array($MyRow['id'], $ScriptArray) and in_array($MyRow['pagesecurity'], $_SESSION['AllowedPageSecurityTokens'])) {
		echo '<option value="', $MyRow['id'], '">', $MyRow['description'], '</option>';
	}
}
echo '</select>
	</div>';

echo '<input type="submit" name="submit" value="" style="display:none;" />';

echo '</form>';

include ('includes/footer.php');
echo '<script async type="text/javascript" src = "', $RootPath, '/dashboard/javascript/dashboard.js"></script>';
?>