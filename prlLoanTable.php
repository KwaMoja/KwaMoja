<?php

include('includes/session.inc');

$Title = _('Loan Table Section');

include('includes/header.inc');

if (isset($_GET['LoanTableID'])) {
	$LoanTableID = $_GET['LoanTableID'];
} elseif (isset($_POST['LoanTableID'])) {

	$LoanTableID = $_POST['LoanTableID'];
} else {
	unset($LoanTableID);
}

echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['update']) or isset($_POST['insert'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (trim($_POST['LoanTableDesc']) == '') {
		$InputError = 1;
		prnMsg(_('The loan description may not be empty'), 'error');
	}

	if ($InputError != 1) {

		if (isset($_POST['update'])) {

			$SQL = "UPDATE prlloantable SET loantabledesc='" . $_POST['LoanTableDesc'] . "'
						WHERE loantableid = '" . $LoanTableID . "'";

			$ErrMsg = _('The loan could not be updated because');
			$DbgMsg = _('The SQL that was used to update the loan table but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
			prnMsg(_('The loan table master record for') . ' ' . $LoanTableID . ' ' . _('has been updated'), 'success');
			unset($LoanTableID);
			unset($_POST['LoanTableDesc']);

		} elseif (isset($_POST['insert'])) { //its a new loan record

			$SQL = "INSERT INTO prlloantable (loantableid,
							loantabledesc)
					 VALUES (NULL,
					 	'" . $_POST['LoanTableDesc'] . "')";

			$ErrMsg = _('The loan') . ' ' . $_POST['LoanTableDesc'] . ' ' . _('could not be added because');
			$DbgMsg = _('The SQL that was used to insert the loan table but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

			prnMsg(_('A new loan table for') . ' ' . $_POST['LoanTableDesc'] . ' ' . _('has been added to the database'), 'success');

			unset($LoanTableID);
			unset($_POST['LoanTableDesc']);

		}

	} else {

		prnMsg(_('Validation failed') . _('no updates or deletes took place'), 'warn');

	}

} elseif (isset($_POST['delete']) and $_POST['delete'] != '') {

	//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

	// PREVENT DELETES IF DEPENDENT RECORDS FOUND
	if ($CancelDelete == 0) {
		$SQL = "DELETE FROM prlloantable WHERE loantableid='$LoanTableID'";
		$Result = DB_query($SQL);
		prnMsg(_('Loan table record for') . ' ' . $LoanTableID . ' ' . _('has been deleted'), 'success');
		unset($LoanTableID);
		unset($_SESSION['LoanTableID']);
	}
}

$SQL = "SELECT loantableid,
				loantabledesc
			FROM prlloantable";
$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0) {
	echo '<table class="selection">
			<tr>
				<th>' . _('Loan Type ID') . '</th>
				<th>' . _('Loan Type Description') . '</th>
			</tr>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr>
				<td>' . $MyRow['loantableid'] . '</td>
				<td>' . $MyRow['loantabledesc'] . '</td>
				<td><a href="' . $_SERVER['PHP_SELF'] . '?LoanTableID=' . $MyRow['loantableid'] . '">' . _('Edit') . '</a></td>
			</tr>';
	}
	echo '</table>';
}

echo '<form method="post" class="noPrint" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="selection">';

//if (!isset($_POST["New"])) {
if (isset($LoanTableID)) {
	$SQL = "SELECT loantableid,
				loantabledesc
			FROM prlloantable
			WHERE loantableid = '" . $LoanTableID . "'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['LoanTableDesc'] = $MyRow['loantabledesc'];
	echo '<input type="hidden" name="LoanTableID" value="' . $LoanTableID . '" />';

} else {
	$_POST['LoanTableDesc'] = '';
}
echo '<tr>
		<td>' . _('Loan Description') . ':' . '</td>
		<td><input type="text" name="LoanTableDesc" size="41" maxlength="40" value="' . $_POST['LoanTableDesc'] . '" /></td>
	</tr>';

if (!isset($LoanTableID)) {
	echo '</table>
			<div class="centre">
				<input type="submit" name="insert" value="' . _('Add These New Loan Details') . '" />
			</div>';
} else {
	echo '</table>
			<div class="centre">
				<input type="submit" name="update" value="' . _('Update Loan Table') . '" />
				<input type="submit" name="delete" value="' . _('Delete Loan Table') . '" onclick="return confirm("' . _('Are you sure you wish to delete this loan?') . '");\" />
			</div>';
}


echo '</form>';

include('includes/footer.inc');
?>