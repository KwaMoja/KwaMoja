<?php

include('includes/session.inc');
$Title = _('Customer Notes');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['Id'])) {
	$Id = (int) $_GET['Id'];
} else if (isset($_POST['Id'])) {
	$Id = (int) $_POST['Id'];
}
if (isset($_POST['DebtorNo'])) {
	$DebtorNo = $_POST['DebtorNo'];
} elseif (isset($_GET['DebtorNo'])) {
	$DebtorNo = stripslashes($_GET['DebtorNo']);
}
echo $DebtorNo;
echo '<div class="toplink"><a href="' . $RootPath . '/SelectCustomer.php?DebtorNo=' . urlencode($DebtorNo) . '">' . _('Back to Select Customer') . '</a></div>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;
	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (!is_long((integer) $_POST['Priority'])) {
		$InputError = 1;
		prnMsg(_('The contact priority must be an integer.'), 'error');
	} elseif (mb_strlen($_POST['Note']) > 200) {
		$InputError = 1;
		prnMsg(_('The contact\'s notes must be two hundred characters or less long'), 'error');
	} elseif (trim($_POST['Note']) == '') {
		$InputError = 1;
		prnMsg(_('The contact\'s notes may not be empty'), 'error');
	}

	if (isset($Id) and $InputError != 1) {

		$SQL = "UPDATE custnotes SET note='" . $_POST['Note'] . "',
									date='" . FormatDateForSQL($_POST['NoteDate']) . "',
									href='" . $_POST['Href'] . "',
									priority='" . $_POST['Priority'] . "'
				WHERE debtorno ='" . $DebtorNo . "'
				AND noteid='" . $Id . "'";
		$Msg = _('Customer Notes') . ' ' . $DebtorNo . ' ' . _('has been updated');
	} elseif ($InputError != 1) {

		$SQL = "INSERT INTO custnotes (debtorno,
										href,
										note,
										date,
										priority)
				VALUES ('" . $_POST['DebtorNo'] . "',
						'" . $_POST['Href'] . "',
						'" . $_POST['Note'] . "',
						'" . FormatDateForSQL($_POST['NoteDate']) . "',
						'" . $_POST['Priority'] . "')";
		$Msg = _('The contact notes record has been added');
	}

	if ($InputError != 1) {
		$Result = DB_query($SQL);
		//echo '<br />'.$SQL;

		echo '<br />';
		prnMsg($Msg, 'success');
		unset($Id);
		unset($_POST['Note']);
		unset($_POST['Noteid']);
		unset($_POST['NoteDate']);
		unset($_POST['Href']);
		unset($_POST['Priority']);
	}
} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'SalesOrders'

	$SQL = "DELETE FROM custnotes
			WHERE noteid='" . $Id . "'
			AND debtorno='" . $DebtorNo . "'";
	$Result = DB_query($SQL);

	echo '<br />';
	prnMsg(_('The contact note record has been deleted'), 'success');
	unset($Id);
	unset($_GET['delete']);
}

if (!isset($Id)) {
	$NameSql = "SELECT * FROM debtorsmaster
				WHERE debtorno='" . $DebtorNo . "'";
	$Result = DB_query($NameSql);
	$MyRow = DB_fetch_array($Result);
	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . _('Notes for Customer') . ': <b>' . $MyRow['name'] . '</b></p>
		<br />';

	$SQL = "SELECT noteid,
					debtorno,
					href,
					note,
					date,
					priority
				FROM custnotes
				WHERE debtorno='" . $DebtorNo . "'
				ORDER BY date DESC";
	$Result = DB_query($SQL);

	echo '<table class="selection">
		<tr>
			<th>' . _('Date') . '</th>
			<th>' . _('Note') . '</th>
			<th>' . _('WWW') . '</th>
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
				<td><a href="%s">%s</a></td>
				<td>%s</td>
				<td><a href="%sId=%s&DebtorNo=%s">' . _('Edit') . ' </td>
				<td><a href="%sId=%s&DebtorNo=%s&delete=1" onclick="return MakeConfirm(\'' . _('Are you sure you wish to delete this customer note?') . '\', \'Confirm Delete\', this);">' . _('Delete') . '</td></tr>', ConvertSQLDate($MyRow['date']), $MyRow['note'], $MyRow['href'], $MyRow['href'], $MyRow['priority'], htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?', $MyRow['noteid'], urlencode($MyRow['debtorno']), htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?', $MyRow['noteid'], urlencode($MyRow['debtorno']));

	}
	//END WHILE LIST LOOP
	echo '</table>';
}
if (isset($Id)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?DebtorNo=' . urlencode($DebtorNo) . '">' . _('Review all notes for this Customer') . '</a>
		</div>';
}
echo '<br />';

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?DebtorNo=' . $DebtorNo . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($Id)) {
		//editing an existing

		$SQL = "SELECT noteid,
						debtorno,
						href,
						note,
						date,
						priority
					FROM custnotes
					WHERE noteid='" . $Id . "'
						AND debtorno='" . $DebtorNo . "'";

		$Result = DB_query($SQL);

		$MyRow = DB_fetch_array($Result);

		$_POST['Noteid'] = $MyRow['noteid'];
		$_POST['Note'] = $MyRow['note'];
		$_POST['Href'] = $MyRow['href'];
		$_POST['NoteDate'] = $MyRow['date'];
		$_POST['Priority'] = $MyRow['priority'];
		$_POST['debtorno'] = $MyRow['debtorno'];
		echo '<input type="hidden" name="Id" value="' . $Id . '" />';
		echo '<input type="hidden" name="Con_ID" value="' . $_POST['Noteid'] . '" />';
		echo '<input type="hidden" name="DebtorNo" value="' . $_POST['debtorno'] . '" />';
		echo '<table class="selection">
			<tr>
				<td>' . _('Note ID') . ':</td>
				<td>' . $_POST['Noteid'] . '</td>
			</tr>';
	} else {
		echo '<table class="selection">';
	}

	echo '<input type="hidden" name="DebtorNo" value="' . stripslashes(stripslashes($DebtorNo)) . '" />';
	echo '<tr>
			<td>' . _('Contact Note') . '</td>';
	if (isset($_POST['Note'])) {
		echo '<td><textarea name="Note" rows="3" required="required" cols="32">' . $_POST['Note'] . '</textarea></td>
			</tr>';
	} else {
		echo '<td><textarea name="Note" rows="3" cols="32"></textarea></td>
			</tr>';
	}
	echo '<tr>
			<td>' . _('WWW') . '</td>';
	if (isset($_POST['Href'])) {
		echo '<td><input type="text" name="Href" value="' . $_POST['Href'] . '" size="35" maxlength="100" /></td>
			</tr>';
	} else {
		echo '<td><input type="text" name="Href" size="35" maxlength="100" /></td>
			</tr>';
	}
	echo '<tr>
			<td>' . _('Date') . '</td>';
	if (isset($_POST['date'])) {
		echo '<td><input type="text" name="NoteDate" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" id="datepicker" value="' . ConvertSQLDate($_POST['date']) . '" size="10" maxlength="10" /></td>
			</tr>';
	} else {
		echo '<td><input type="text" name="NoteDate" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" id="datepicker" value="' . date($_SESSION['DefaultDateFormat']) . '" size="10" maxlength="10" /></td>
			</tr>';
	}
	echo '<tr>
			<td>' . _('Priority') . '</td>';
	if (isset($_POST['Priority'])) {
		echo '<td><input type="text" class=integer" name="Priority" value="' . $_POST['Priority'] . '" size="1" maxlength="3" /></td>
			</tr>';
	} else {
		echo '<td><input type="text" class="integer" name="Priority" size="1" maxlength="3" /></td>
			</tr>';
	}
	echo '<tr>
			<td colspan="2">
			<div class="centre">
				<input type="submit" name="submit" value="' . _('Enter Information') . '" />
			</div>
			</td>
		</tr>
		</table>
		</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>