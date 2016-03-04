<?php

include('includes/session.inc');
$Title = _('Customer Type (Group) Notes');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['Id'])) {
	$Id = (int) $_GET['Id'];
} else if (isset($_POST['Id'])) {
	$Id = (int) $_POST['Id'];
}
if (isset($_POST['DebtorType'])) {
	$DebtorType = $_POST['DebtorType'];
} elseif (isset($_GET['DebtorType'])) {
	$DebtorType = $_GET['DebtorType'];
}
echo '<div class="toplink"><a href="' . $RootPath . '/SelectCustomer.php?DebtorType=' . urlencode($DebtorType) . '">' . _('Back to Select Customer') . '</a></div>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;
	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (!is_long((integer) $_POST['Priority'])) {
		$InputError = 1;
		prnMsg(_('The Contact priority must be an integer.'), 'error');
	} elseif (mb_strlen($_POST['Note']) > 200) {
		$InputError = 1;
		prnMsg(_('The contacts notes must be two hundred characters or less long'), 'error');
	} elseif (trim($_POST['Note']) == '') {
		$InputError = 1;
		prnMsg(_('The contacts notes may not be empty'), 'error');
	}

	if ($Id and $InputError != 1) {

		$SQL = "UPDATE debtortypenotes SET note='" . $_POST['Note'] . "',
											date='" . FormatDateForSQL($_POST['NoteDate']) . "',
											href='" . $_POST['Href'] . "',
											priority='" . $_POST['Priority'] . "'
										WHERE typeid ='" . $DebtorType . "'
										AND noteid='" . $Id . "'";
		$Msg = _('Customer Group Notes') . ' ' . $DebtorType . ' ' . _('has been updated');
	} elseif ($InputError != 1) {

		$SQL = "INSERT INTO debtortypenotes (typeid,
											href,
											note,
											date,
											priority)
									VALUES ('" . $DebtorType . "',
											'" . $_POST['Href'] . "',
											'" . $_POST['Note'] . "',
											'" . FormatDateForSQL($_POST['NoteDate']) . "',
											'" . $_POST['Priority'] . "')";
		$Msg = _('The contact group notes record has been added');
	}

	if ($InputError != 1) {
		$Result = DB_query($SQL);

		echo '<br />';
		prnMsg($Msg, 'success');
		unset($Id);
		unset($_POST['Note']);
		unset($_POST['NoteID']);
	}
} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'SalesOrders'

	$SQL = "DELETE FROM debtortypenotes
			WHERE noteid='" . $Id . "'
			AND typeid='" . $DebtorType . "'";
	$Result = DB_query($SQL);

	echo '<br />';
	prnMsg(_('The contact group note record has been deleted'), 'success');
	unset($Id);
	unset($_GET['delete']);

}

if (!isset($Id)) {
	$SQLname = "SELECT typename from debtortype where typeid='" . $DebtorType . "'";
	$Result = DB_query($SQLname);
	$MyRow = DB_fetch_array($Result);
	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/customer.png" title="' . _('Search') . '" alt="" />' . _('Notes for Customer Type') . ': <b>' . $MyRow['typename'] . '</b></p>
		<br />';

	$SQL = "SELECT noteid,
					typeid,
					href,
					note,
					date,
					priority
				FROM debtortypenotes
				WHERE typeid='" . $DebtorType . "'
				ORDER BY date DESC";
	$Result = DB_query($SQL);
	//echo '<br />'.$SQL;

	echo '<table class="selection">';
	echo '<tr>
			<th>' . _('Date') . '</th>
			<th>' . _('Note') . '</th>
			<th>' . _('href') . '</th>
			<th>' . _('Priority') . '</th>
		</tr>';

	$k = 0; //row colour counter

	while ($MyRow = DB_fetch_array($Result)) {
		if ($k == 1) {
			echo '<tr class="OddTableRows">';
			$k = 0;
		} else {
			echo '<tr class="EvenTableRows">';
			$k = 1;
		}
		printf('<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td><a href="%sId=%s&amp;DebtorType=%s">' . _('Edit') . '</a></td>
				<td><a href="%sId=%s&amp;DebtorType=%s&amp;delete=1">' . _('Delete') . '</a></td></tr>', $MyRow['date'], $MyRow['note'], $MyRow['href'], $MyRow['priority'], htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?', $MyRow['noteid'], $MyRow['typeid'], htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?', $MyRow['noteid'], $MyRow['typeid']);

	}
	//END WHILE LIST LOOP
	echo '</table>';
}
if (isset($Id)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?DebtorType=' . $DebtorType . '">' . _('Review all notes for this Customer Type') . '</a>
		</div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?DebtorType=' . $DebtorType . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($Id)) {
		//editing an existing

		$SQL = "SELECT noteid,
					typeid,
					href,
					note,
					date,
					priority
				FROM debtortypenotes
				WHERE noteid=" . $Id . "
					AND typeid='" . $DebtorType . "'";

		$Result = DB_query($SQL);
		//echo '<br />'.$SQL;

		$MyRow = DB_fetch_array($Result);

		$_POST['NoteID'] = $MyRow['noteid'];
		$_POST['Note'] = $MyRow['note'];
		$_POST['Href'] = $MyRow['href'];
		$_POST['NoteDate'] = $MyRow['date'];
		$_POST['Priority'] = $MyRow['priority'];
		$_POST['TypeID'] = $MyRow['typeid'];
		echo '<input type="hidden" name="Id" value="' . $Id . '" />';
		echo '<input type="hidden" name="Con_ID" value="' . $_POST['NoteID'] . '" />';
		echo '<input type="hidden" name="DebtorType" value="' . $_POST['TypeID'] . '" />';
		echo '<table class="selection">
				<tr>
					<td>' . _('Note ID') . ':</td>
					<td>' . $_POST['NoteID'] . '</td>
				</tr>';
	} else {
		echo '<table class="selection">';
		$_POST['NoteID'] = '';
		$_POST['Note'] = '';
		$_POST['Href'] = '';
		$_POST['NoteDate'] = '';
		$_POST['Priority'] = '';
		$_POST['TypeID'] = '';
	}

	echo '<tr>
			<td>' . _('Contact Group Note') . ':</td>
			<td><textarea name="Note" rows="3" cols="32">' . $_POST['Note'] . '</textarea></td>
		</tr>
		<tr>
			<td>' . _('Web site') . ':</td>
			<td><input type="text" name="Href" value="' . $_POST['Href'] . '" size="35" maxlength="100" /></td>
		</tr>
		<tr>
			<td>' . _('Date') . ':</td>
			<td><input type="text" name="NoteDate" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" value="' . $_POST['NoteDate'] . '" size="10" maxlength="10" /></td>
		</tr>
		<tr>
			<td>' . _('Priority') . ':</td>
			<td><input type="text" name="Priority" value="' . $_POST['Priority'] . '" size="1" maxlength="3" /></td>
		</tr>
		</table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="' . _('Enter Information') . '" />
		</div>
		</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>