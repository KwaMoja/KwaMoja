<?php
/* Add, Edit, Delete, and List MRP demand records. Table is mrpdemands.
 * Have separate functions for each routine. Use pass-by-reference - (&$StockId) -
 * to pass values of $StockId to functions. - when just used $StockId as variable,
 * got error: Catchable fatal error: Object of class mysqli could not be converted to string
*/

include ('includes/session.php');
$ViewTopic = 'MRP';
$BookMark = 'MRP_MasterSchedule';
$Title = _('MRP Demands');
include ('includes/header.php');

if (isset($_POST['DemandID'])) {
	$DemandID = $_POST['DemandID'];
} elseif (isset($_GET['DemandID'])) {
	$DemandID = $_GET['DemandID'];
}

if (isset($_POST['StockID'])) {
	$StockId = trim(mb_strtoupper($_POST['StockID']));
} elseif (isset($_GET['StockID'])) {
	$StockId = trim(mb_strtoupper($_GET['StockID']));
}

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/inventory.png" title="', _('Inventory'), '" alt="" />', ' ', $Title, '
	</p>';

if (isset($_POST['Search'])) {
	search($StockId);
} elseif (isset($_POST['submit'])) {
	submit($StockId, $DemandID);
} elseif (isset($_GET['delete'])) {
	delete($DemandID, '', $StockId);
} elseif (isset($_POST['deletesome'])) {
	delete('', $_POST['MRPDemandtype'], $StockId);
} elseif (isset($_GET['listall'])) {
	listall('', '');
} elseif (isset($_POST['listsome'])) {
	listall($StockId, $_POST['MRPDemandtype']);
} else {
	display($StockId, $DemandID);
}

function search(&$StockId) { //####SEARCH_SEARCH_SEARCH_SEARCH_SEARCH_SEARCH_SEARCH_#####
	// Search by partial part number or description. Display the part number and description from
	// the stockmaster so user can select one. If the user clicks on a part number
	// MRPDemands.php is called again, and it goes to the display() routine.
	// Work around to auto select
	if ($_POST['Keywords'] == '' and $_POST['StockCode'] == '') {
		$_POST['StockCode'] = '%';
	}
	if ($_POST['Keywords'] and $_POST['StockCode']) {
		$Msg = _('Stock description keywords have been used in preference to the Stock code extract entered');
	}
	if ($_POST['Keywords'] == '' and $_POST['StockCode'] == '') {
		$Msg = _('At least one stock description keyword or an extract of a stock code must be entered for the search');
	} else {
		if (mb_strlen($_POST['Keywords']) > 0) {
			//insert wildcard characters in spaces
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description
					FROM stockmaster
					WHERE  stockmaster.description " . LIKE . " '" . $SearchString . "'
					ORDER BY stockmaster.stockid";

		} elseif (mb_strlen($_POST['StockCode']) > 0) {
			$SQL = "SELECT stockmaster.stockid,
						stockmaster.description
					FROM stockmaster
					WHERE  stockmaster.stockid " . LIKE . "'%" . $_POST['StockCode'] . "%'
					ORDER BY stockmaster.stockid";

		}

		$ErrMsg = _('The SQL to find the parts selected failed with the message');
		$Result = DB_query($SQL, $ErrMsg);

	} //one of keywords or StockCode was more than a zero length string
	// If the SELECT found records, display them
	if (DB_num_rows($Result) > 0) {
		echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
		echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
		echo '<table cellpadding="2">
				<tr>
					<th>', _('Code'), '</th>
					<th>', _('Description'), '</th>
				</tr>';

		while ($MyRow = DB_fetch_array($Result)) {
			echo '<tr class="striped_row">
					<td><input type="submit" name="StockID" value="', $MyRow['stockid'], '" /></td>
					<td>', $MyRow['description'], '</td>
				</tr>';
		} //end of while loop
		echo '</table>';
		echo '</form>';

	} else {
		prnMsg(_('No record found in search'), 'error');
		unset($StockId);
		display($StockId, $DemandID);
	}

} // End of function search()


function submit(&$StockId, &$DemandID) //####SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
{
	// In this section if hit submit button. Do edit checks. If all checks pass, see if record already
	// exists for StockID/Duedate/MRPDemandtype combo; that means do an Update, otherwise, do INSERT.
	//initialise no input errors assumed initially before we test
	// echo "<br/>Submit - DemandID = $DemandID<br/>";
	$FormatedDuedate = FormatDateForSQL($_POST['Duedate']);
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	 ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (!is_numeric(filter_number_format($_POST['Quantity']))) {
		$InputError = 1;
		prnMsg(_('Quantity must be numeric'), 'error');
	}
	if (filter_number_format($_POST['Quantity']) <= 0) {
		$InputError = 1;
		prnMsg(_('Quantity must be greater than 0'), 'error');
	}
	if (!is_date($_POST['Duedate'])) {
		$InputError = 1;
		prnMsg(_('Invalid due date'), 'error');
	}
	$SQL = "SELECT * FROM mrpdemandtypes
			WHERE mrpdemandtype='" . $_POST['MRPDemandtype'] . "'";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) == 0) {
		$InputError = 1;
		prnMsg(_('Invalid demand type'), 'error');
	}
	// Check if valid part number - Had done a Select Count(*), but that returned a 1 in DB_num_rows
	// even if there was no record.
	$SQL = "SELECT * FROM stockmaster
			WHERE stockid='" . $StockId . "'";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) == 0) {
		$InputError = 1;
		prnMsg($StockId . ' ' . _('is not a valid item code'), 'error');
		unset($_POST['StockID']);
		unset($StockId);
	}
	// Check if part number/demand type/due date combination already exists
	$SQL = "SELECT * FROM mrpdemands
			WHERE stockid='" . $StockId . "'
			AND mrpdemandtype='" . $_POST['MRPDemandtype'] . "'
			AND duedate='" . $FormatedDuedate . "'
			AND demandid <> '" . $DemandID . "'";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {
		$InputError = 1;
		prnMsg(_('Record already exists for part number/demand type/date'), 'error');
	}

	if ($InputError != 1) {
		$SQL = "SELECT COUNT(*) FROM mrpdemands
				   WHERE demandid='" . $DemandID . "'
				   GROUP BY demandid";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);

		if ($MyRow[0] > 0) {
			//If $MyRow[0] > 0, it means this is an edit, so do an update
			$SQL = "UPDATE mrpdemands SET quantity = '" . filter_number_format($_POST['Quantity']) . "',
							mrpdemandtype = '" . trim(mb_strtoupper($_POST['MRPDemandtype'])) . "',
							duedate = '" . $FormatedDuedate . "'
					WHERE demandid = '" . $DemandID . "'";
			$Msg = _("The MRP demand record has been updated for") . ' ' . $StockId;
		} else {

			// if $MyRow[0] from SELECT count(*) is zero, this is an entry of a new record
			$SQL = "INSERT INTO mrpdemands (stockid,
							mrpdemandtype,
							quantity,
							duedate)
						VALUES ('" . $StockId . "',
							'" . trim(mb_strtoupper($_POST['MRPDemandtype'])) . "',
							'" . filter_number_format($_POST['Quantity']) . "',
							'" . $FormatedDuedate . "'
						)";
			$Msg = _('A new MRP demand record has been added to the database for') . ' ' . $StockId;
		}

		$Result = DB_query($SQL, _('The update/addition of the MRP demand record failed because'));
		prnMsg($Msg, 'success');
		echo '<br />';
		unset($_POST['MRPDemandtype']);
		unset($_POST['Quantity']);
		unset($_POST['StockID']);
		unset($_POST['Duedate']);
		unset($StockId);
		unset($DemandID);
	} // End of else where DB_num_rows showed there was a valid stockmaster record
	display($StockId, $DemandID);
} // End of function submit()


function delete($DemandID, $DemandType, $StockId) { //####DELETE_DELETE_DELETE_DELETE_DELETE_DELETE_####
	// If wanted to have a Confirm routine before did actually deletion, could check if
	// deletion = "yes"; if it did, display link that redirects back to this page
	// like this - <a href=" ' . htmlspecialchars(basename(__FILE__),ENT_QUOTES,'UTF-8') . '?&delete=confirm&StockID=' . "$StockId" . ' ">
	// that sets delete=confirm. If delete=confirm, do actually deletion.
	//  This deletes an individual record by DemandID if called from a listall that shows
	// edit/delete or deletes all of a particular demand type if press Delete Demand Type button.
	$Where = " ";
	if ($DemandType) {
		$Where = " WHERE mrpdemandtype ='" . $DemandType . "'";
	}
	if ($DemandID) {
		$Where = " WHERE demandid ='" . $DemandID . "'";
	}
	$SQL = "DELETE FROM mrpdemands
		   $Where";
	$Result = DB_query($SQL);
	if ($DemandID) {
		prnMsg(_('The MRP demand record for') . ' ' . $StockId . ' ' . _('has been deleted'), 'succes');
	} else {
		prnMsg(_('All records for demand type') . ' ' . $DemandType . ' ' . _('have been deleted'), 'succes');
	}
	unset($DemandID);
	unset($StockId);
	display($stockID, $DemandID);

} // End of function delete()


function listall($Part, $DemandType) { //####LISTALL_LISTALL_LISTALL_LISTALL_LISTALL_LISTALL_LISTALL_####
	// List all mrpdemands records, with anchors to Edit or Delete records if hit List All anchor
	// Lists some in hit List Selection submit button, and uses part number if it is entered or
	// demandtype
	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	$Where = " ";
	if ($DemandType) {
		$Where = " WHERE mrpdemandtype ='" . $DemandType . "'";
	}
	if ($Part) {
		$Where = " WHERE mrpdemands.stockid ='" . $Part . "'";
	}
	// If part is entered, it overrides demandtype
	$SQL = "SELECT mrpdemands.demandid,
				   mrpdemands.stockid,
				   mrpdemands.mrpdemandtype,
				   mrpdemands.quantity,
				   mrpdemands.duedate,
				   stockmaster.description,
				   stockmaster.decimalplaces
			FROM mrpdemands
			LEFT JOIN stockmaster on mrpdemands.stockid = stockmaster.stockid" . $Where . " ORDER BY mrpdemands.stockid, mrpdemands.duedate";

	$ErrMsg = _('The SQL to find the parts selected failed with the message');
	$Result = DB_query($SQL, $ErrMsg);

	echo '<table>
			<tr>
				<th>', _('Part Number'), '</th>
				<th>', _('Description'), '</th>
				<th>', _('Demand Type'), '</th>
				<th>', _('Quantity'), '</th>
				<th>', _('Due Date'), '</th>
				<th></th>
				<th></th>
			</tr>';
	$Counter = 0;
	while ($MyRow = DB_fetch_array($Result)) {
		$DisplayDate = ConvertSQLDate($MyRow[4]);
		$Counter++;
		echo '<tr class="striped_row">
				<td>', $MyRow['stockid'], '</td>
				<td>', $MyRow['description'], '</td>
				<td>', $MyRow['mrpdemandtype'], '</td>
				<td>', locale_number_format($MyRow['quantity'], $MyRow['decimalplaces']), '</td>
				<td>', $DisplayDate, '</td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?DemandID=', urlencode($MyRow['demandid']), '&amp;StockID=', urlencode($MyRow['stockid']), '">', _('Edit'), '</a></td>
				<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?DemandID=', urlencode($MyRow['demandid']), '&amp;StockID=', urlencode($MyRow['stockid']), '&amp;delete=yes" onclick="return MakeConfirm(\'', _('Are you sure you wish to delete this demand?'), '\', \'Confirm Delete\', this);">', _('Delete'), '</a></td>
			</tr>';
	}

	//END WHILE LIST LOOP
	echo '<tr>
			<td>' . _('Number of Records') . '</td>
			<td>' . $Counter . '</td>
		</tr>';
	echo '</table>';
	echo '</form>';
	unset($StockId);
	display($StockId, $DemandID);

} // End of function listall()


function display(&$StockId, &$DemandID) { //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
	// Display Seach fields at top and Entry form below that. This function is called the first time
	// the page is called, and is also invoked at the end of all of the other functions.
	// echo "<br/>DISPLAY - DemandID = $DemandID<br/>";
	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	if (!isset($StockId)) {
		echo '<fieldset>
				<legend>', _('Item Search Criteria'), '</legend>';

		echo '<field>
				<label for="Keywords">', _('Enter text extracts in the'), ' <b>', _('description'), '</b>:</label>
				<input type="search" name="Keywords" size="20" maxlength="25" />
			</field>';

		echo '<h1>', _('OR'), '</h1>';

		echo '<field>
				<label for="StockCode">', _('Enter extract of the'), ' <b>', _('Stock Code'), '</b>:</label>
				<input type="search" name="StockCode" size="15" maxlength="20" />
			</field>';

		echo '<h1>', _('OR'), '</h1>';

		echo '<field>
				<a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?listall=yes">', _('List All Demands'), '</a>
			</field>';

		echo '</fieldset>';

		echo '<div class="centre">
				<input type="submit" name="Search" value="', _('Search Now'), '" />
			</div>';
	} else {
		if (isset($DemandID)) {
			//editing an existing MRP demand
			$SQL = "SELECT demandid,
					stockid,
					mrpdemandtype,
					quantity,
					duedate
				FROM mrpdemands
				WHERE demandid='" . $DemandID . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_array($Result);

			if (DB_num_rows($Result) > 0) {
				$_POST['DemandID'] = $MyRow['demandid'];
				$_POST['StockID'] = $MyRow['stockid'];
				$_POST['MRPDemandtype'] = $MyRow['mrpdemandtype'];
				$_POST['Quantity'] = locale_number_format($MyRow['quantity'], 'Variable');
				$_POST['Duedate'] = ConvertSQLDate($MyRow['duedate']);
			}

			echo '<input type="hidden" name="DemandID" value="' . $_POST['DemandID'] . '" />';
			echo '<input type="hidden" name="StockID" value="' . $_POST['StockID'] . '" />';
			echo '<fieldset>
					<legend>', _('Edit Demand'), '</legend>
					<field>
						<label for="StockID">', _('Part Number'), ':</label>
						<div class="fieldtext">', $_POST['StockID'], '</div>
					</field>';

		} else {
			if (!isset($_POST['StockID'])) {
				$_POST['StockID'] = '';
			}
			echo '<fieldset>
					<legend>', _('Create New Demand'), '</legend>
					<field>
						<label for="StockID">', _('Part Number'), ':</label>
						<input type="text" name="StockID" size="21" required="required" maxlength="20" value="', $_POST['StockID'], '" />
					</field>';
		}

		if (!isset($_POST['Quantity'])) {
			$_POST['Quantity'] = 0;
		}

		if (!isset($_POST['Duedate'])) {
			$_POST['Duedate'] = date($_SESSION['DefaultDateFormat']);
		}

		echo '<field>
				<label for="Quantity">', _('Quantity'), ':</label>
				<input type="text" name="Quantity" class="number" size="6" required="required" maxlength="6" value="', $_POST['Quantity'], '" />
			</field>';

		echo '<field>
				<label for="Duedate">', _('Due Date'), ':</label>
				<input type="text" class="date" name="Duedate" size="12" required="required" maxlength="12" value="', $_POST['Duedate'], '" />
			</field>';

		// Generate selections for Demand Type
		$SQL = "SELECT mrpdemandtype,
						description
				FROM mrpdemandtypes";
		$Result = DB_query($SQL);
		echo '<field>
				<label for="MRPDemandtype">', _('Demand Type'), '</label>
				<select name="MRPDemandtype">';
		while ($MyRow = DB_fetch_array($Result)) {
			if (isset($_POST['MRPDemandtype']) and $MyRow['mrpdemandtype'] == $_POST['MRPDemandtype']) {
				echo '<option selected="selected" value="', $MyRow['mrpdemandtype'], '">', $MyRow['mrpdemandtype'], ' - ', $MyRow['description'], '</option>';
			} else {
				echo '<option value="', $MyRow['mrpdemandtype'], '">', $MyRow['mrpdemandtype'], ' - ', $MyRow['description'], '</option>';
			}
		} //end while loop
		echo '</select>
			</field>';

		echo '</fieldset>';

		echo '<div class="centre">
				<input type="submit" name="submit" value="', _('Enter Information'), '" />&nbsp;&nbsp;
				<input type="submit" name="listsome" value="', _('List Selection'), '" />&nbsp;&nbsp;
				<input type="submit" name="deletesome" value="', _('Delete Demand Type'), '" />';
		// If mrpdemand record exists, display option to delete it
		if ((isset($DemandID)) and (DB_num_rows($Result) > 0)) {
			echo '<a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?delete=yes&amp;StockID=', urlencode($StockId), '&amp;DemandID=', urlencode($DemandID), '" onclick="return MakeConfirm(\'', _('Are you sure you wish to delete this demand?'), '\', \'Confirm Delete\', this);">', _('Or Delete Record'), '</a>';
		}
		echo '</div>';
	}
	echo '</form>';

} // End of function display()
include ('includes/footer.php');
?>