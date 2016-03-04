<?php
$PageSecurity = 1;
include('includes/session.inc');

$Title = _('Menu Manager');

include('includes/header.inc');

if (!isset($_POST['SecurityRole'])) {
	$RoleSQL = "SELECT secroleid,
					secrolename
				FROM securityroles";
	$RoleResult = DB_query($RoleSQL);

	echo '<p class="page_title_text" >
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . _('Select a Security Role to work with') . '" alt="' . _('Select a Security Role to work with') . '" />' . ' ' . _('Select a Security Role to work with') . '
		</p>';

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<div class="centre">
			<select name="SecurityRole" autofocus="autofocus" required="required" onChange="ReloadForm(SelectSecRole)">
				<option value=""></option>';

	while ($RoleRow = DB_fetch_array($RoleResult)) {
		echo '<option value="' . $RoleRow['secroleid'] . '">' . $RoleRow['secrolename'] . '</option>';
	}

	echo '</select>
			<input type="submit" name="SelectSecRole" value="Select Security Role" />
		</div>
		</form>';
} else {
	$RoleSQL = "SELECT secrolename
				FROM securityroles
					WHERE secroleid='" . $_POST['SecurityRole'] . "'";
	$RoleResult = DB_query($RoleSQL);
	$RoleRow = DB_fetch_array($RoleResult);

	echo '<p class="page_title_text" >
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Maintian Menus for Role') . ' - ' . $RoleRow['secrolename'] . '" alt="' . _('Maintian Menus for Role') . ' - ' . $RoleRow['secrolename'] . '" />' . ' ' . _('Maintian Menus for Role') . ' - ' . $RoleRow['secrolename'] . '
		</p>';

	$ModuleSQL = "SELECT modulelink,
						modulename,
						sequence
					FROM modules
					WHERE secroleid='" . $_POST['SecurityRole'] . "'
					ORDER BY sequence";
	$ModuleResult = DB_query($ModuleSQL);
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="SecurityRole" value="' . $_POST['SecurityRole'] . '" />';
	echo '<div class="centre">
			<label>' . _('Select a module') . '</label>
				<select name="Module"autofocus="autofocus" required="required" onChange="ReloadForm(SelectModule)">
					<option value=""></option>';

	while ($ModuleRow = DB_fetch_array($ModuleResult)) {
		if (isset($_POST['Module']) and $_POST['Module'] == $ModuleRow['modulelink']) {
			echo '<option selected="selected" value="' . $ModuleRow['modulelink'] . '">' . $ModuleRow['modulename'] . '</option>';
		} else {
			echo '<option value="' . $ModuleRow['modulelink'] . '">' . $ModuleRow['modulename'] . '</option>';
		}
	}

	echo '</select>
			<input type="submit" name="SelectModule" value="Select Module" />
			</div>';

	if (isset($_POST['Module'])) {
		echo '<input type="hidden" name="SecurityRole" value="' . $_POST['SecurityRole'] . '" />';
		echo '<input type="hidden" name="Module" value="' . $_POST['Module'] . '" />';
		echo '<div class="centre">';

		echo '<div class="box">
				<div class="box_header">' . _('Select Menu Section') . '</div>
			</div>';

		echo '<div class="box">
				<div class="box_header">' . _('Available Menu Items') . '</div>
			</div>';

		echo '<div class="box">
				<div class="box_header">' . _('Selected Menu Items') . '</div>
			</div>';

		echo '</div>';
	}
	echo '</form>';
}

include('includes/footer.inc');

?>