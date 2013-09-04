<?php

$PageSecurity=0;

include('includes/session.inc');
$Title = _('Dashboard');
include('includes/header.inc');
include('includes/MainMenuLinksArray.php');

$sql = "SELECT scripts FROM dashboard_users WHERE userid = '".$_SESSION['UserID']."' ";

$result = DB_query($sql,$db);

$myrow = DB_fetch_array($result);
$arr = explode(',',$myrow["scripts"]);

if (isset($_GET['Remove'])) {
	if(($key = array_search($_GET['Remove'], $arr)) !== false) {
		unset($arr[$key]);
	}
	$UpdateSQL = "UPDATE dashboard_users SET scripts='" . implode(',', $arr) . "' WHERE userid = '".$_SESSION['UserID']."' ";
	$UpdateResult = DB_query($UpdateSQL, $db);
}

if (isset($_POST['Reports'])) {
	$arr[] = $_POST['Reports'];
	asort($arr);
	$UpdateSQL = "UPDATE dashboard_users SET scripts='" . implode(',', $arr) . "' WHERE userid = '".$_SESSION['UserID']."' ";
	$UpdateResult = DB_query($UpdateSQL, $db);
}

$sql = "SELECT id,
				scripts,
				pagesecurity,
				description
			FROM dashboard_scripts";

$result = DB_query($sql,$db);

while($myrow = DB_fetch_array($result)) {
	if (in_array($myrow['id'], $arr) and in_array($myrow['pagesecurity'], $_SESSION['AllowedPageSecurityTokens'])) {
		echo '<iframe src="dashboard/' . $myrow['scripts'] . '"></iframe>';
	}
}

DB_data_seek($result, 0);

echo '<form onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<input type="submit" name="submit" value="" style="display:none;" />';
echo '<div style="width:100%;font-size:120%;color:#2F4F4F;">' .
		_('Add reports to your dashboard') . '&nbsp;&nbsp;&nbsp;
		<select name="Reports" style="font-size:100%;margin-top:10px;margin-bottom:0px;color:#2F4F4F;" id="favourites" onchange="ReloadForm(submit)">';
echo '<option value=""></option>';
while ($myrow = DB_fetch_array($result)) {
	if (!in_array($myrow['id'], $arr) and in_array($myrow['pagesecurity'], $_SESSION['AllowedPageSecurityTokens'])) {
		echo '<option value="' . $myrow['id'] . '">' . $myrow['description'] . '</option>';
	}
}
echo '</select></div>';
echo '</form>';

include('includes/footer.inc');
?>