<?php

include('includes/session.inc');
$Title = _('Supplier Types') . ' / ' . _('Maintenance');
include('includes/header.inc');

if (isset($_POST['SelectedType'])) {
	$SelectedType = mb_strtoupper($_POST['SelectedType']);
} elseif (isset($_GET['SelectedType'])) {
	$SelectedType = mb_strtoupper($_GET['SelectedType']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Supplier Types') . '" alt="" />' . _('Supplier Type Setup') . '</p>';
echo '<div class="page_help_text">' . _('Add/edit/delete Supplier Types') . '</div>';

if (isset($_POST['insert']) or isset($_POST['update'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i = 1;
	if (mb_strlen($_POST['TypeName']) > 100) {
		$InputError = 1;
		prnMsg(_('The supplier type name description must be 100 characters or less long'), 'error');
	}

	if (mb_strlen(trim($_POST['TypeName'])) == 0) {
		$InputError = 1;
		prnMsg(_('The supplier type name description must contain at least one character'), 'error');
	}

	if (isset($_POST['insert'])) {
		$CheckSql = "SELECT count(*)
				FROM suppliertype
				WHERE typename = '" . $_POST['TypeName'] . "'";
		$CheckResult = DB_query($CheckSql);
		$CheckRow = DB_fetch_row($CheckResult);
		if ($CheckRow[0] > 0) {
			$InputError = 1;
			prnMsg(_('You already have a supplier type called') . ' ' . $_POST['TypeName'], 'error');
		}
	}

	if (isset($_POST['update']) and $InputError != 1) {

		$SQL = "UPDATE suppliertype
			SET typename = '" . $_POST['TypeName'] . "',
				nextsupplierno = '" . $_POST['NextNumber'] . "'
			WHERE typeid = '" . $SelectedType . "'";

		$Msg = _('The supplier type') . ' ' . $SelectedType . ' ' . _('has been updated');
	} elseif ($InputError != 1) {

		// Add new record on submit

		$SQL = "INSERT INTO suppliertype
					(typename,
					 nextsupplierno)
				VALUES ('" . $_POST['TypeName'] . "',
						'" . $_POST['NextNumber'] . "')";

		$Msg = _('Supplier type') . ' ' . stripslashes($_POST['TypeName']) . ' ' . _('has been created');

	}

	if ($InputError != 1) {
		//run the SQL from either of the above possibilites
		$Result = DB_query($SQL);

		// Does it exist
		$CheckSql = "SELECT count(*)
				 FROM suppliertype
				 WHERE typeid = '" . $_SESSION['DefaultSupplierType'] . "'";
		$CheckResult = DB_query($CheckSql);
		$CheckRow = DB_fetch_row($CheckResult);

		// If it doesnt then update config with newly created one.
		if ($CheckRow[0] == 0) {
			$SQL = "UPDATE config
					SET confvalue='" . $_POST['TypeID'] . "'
					WHERE confname='DefaultSupplierType'";
			$Result = DB_query($SQL);
			$_SESSION['DefaultSupplierType'] = $_POST['TypeID'];
		}
		unset($SelectedType);
		unset($_POST['TypeID']);
		unset($_POST['TypeName']);
		unset($_POST['NextNumber']);
	}

} elseif (isset($_GET['delete'])) {

	$SQL = "SELECT COUNT(*) FROM suppliers WHERE supptype='" . $SelectedType . "'";

	$ErrMsg = _('The number of suppliers using this Type record could not be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		prnMsg(_('Cannot delete this type because suppliers are currently set up to use this type') . '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('suppliers with this type code'));
	} else {

		$SQL = "DELETE FROM suppliertype WHERE typeid='" . $SelectedType . "'";
		$ErrMsg = _('The Type record could not be deleted because');
		$Result = DB_query($SQL, $ErrMsg);
		prnMsg(_('Supplier type') . $SelectedType . ' ' . _('has been deleted'), 'success');

		unset($SelectedType);
		unset($_GET['delete']);

	}
}

if (!isset($SelectedType)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedType will
	 *  exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then
	 * none of the above are true and the list of sales types will be displayed with links to delete or edit each. These will call
	 * the same page again and allow update/input or deletion of the records
	 */

	$SQL = "SELECT typeid, typename, nextsupplierno FROM suppliertype";
	$Result = DB_query($SQL);

	echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">' . _('Type ID') . '</th>
					<th class="SortedColumn">' . _('Type Name') . '</th>
					<th class="SortedColumn">' . _('Last Supplier No') . '</th>
				</tr>
			</thead>';

	$k = 0; //row colour counter
	echo '<tbody>';
	while ($MyRow = DB_fetch_row($Result)) {
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}

		echo '<td>' . $MyRow[0] . '</td>
				<td>' . $MyRow[1] . '</td>
				<td>' . $MyRow[2] . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedType=' . urlencode($MyRow[0]) . '&Edit=Yes">' . _('Edit') . '</a></td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedType=' . urlencode($MyRow[0]) . '&amp;delete=yes" onclick="return MakeConfirm(\'' . _('Are you sure you wish to delete this Supplier Type?') . '\', \'Confirm Delete\', this);">' . _('Delete') . '</a></td>
			</tr>';
	}
	//END WHILE LIST LOOP
	echo '</tbody>';
	echo '</table>';
}

//end of ifs and buts!
if (isset($SelectedType)) {

	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Show All Types Defined') . '</a>
		</div>';
}
if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">'; //Main table

	// The user wish to EDIT an existing type
	if (isset($SelectedType) and $SelectedType != '') {

		$SQL = "SELECT typeid,
				   typename,
				   nextsupplierno
				FROM suppliertype
				WHERE typeid='" . $SelectedType . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['TypeID'] = $MyRow['typeid'];
		$_POST['TypeName'] = $MyRow['typename'];
		$_POST['NextNumber'] = $MyRow['nextsupplierno'];

		echo '<input type="hidden" name="SelectedType" value="' . $SelectedType . '" />';

		// We dont allow the user to change an existing type code

		echo '<tr>
				<td>' . _('Type ID') . ': </td>
				<td>' . $_POST['TypeID'] . '</td>
			</tr>';
	}

	if (!isset($_POST['TypeName'])) {
		$_POST['TypeName'] = '';
	}
	if (!isset($_POST['NextNumber'])) {
		$_POST['NextNumber'] = '';
	}
	echo '<tr>
			<td>' . _('Type Name') . ':</td>
			<td><input type="text" autofocus="autofocus" required="required" maxlength="100" name="TypeName" value="' . $_POST['TypeName'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Last Supplier Number') . ':</td>
			<td><input type="text" autofocus="autofocus" maxlength="100" name="NextNumber" value="' . $_POST['NextNumber'] . '" /></td>
		</tr>
	</table>';

	if (isset($_GET['Edit'])) {
		echo '<div class="centre">
				<input type="submit" name="update" value="', _('Update Type'), '" />
			</div>';
	} else {
		echo '<div class="centre">
				<input type="submit" name="insert" value="', _('Add Type'), '" />
			</div>';
	}
	echo '</form>';

} // end if user wish to delete

include('includes/footer.inc');
?>