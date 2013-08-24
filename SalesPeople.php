<?php

include('includes/session.inc');
$Title = _('Sales People Maintenance');
include('includes/header.inc');

if (isset($_GET['SelectedSalesPerson'])) {
	$SelectedSalesPerson = mb_strtoupper($_GET['SelectedSalesPerson']);
} elseif (isset($_POST['SelectedSalesPerson'])) {
	$SelectedSalesPerson = mb_strtoupper($_POST['SelectedSalesPerson']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	$i = 1;

	//first off validate inputs sensible

	if (mb_strlen($_POST['SalesmanCode']) > 3) {
		$InputError = 1;
		prnMsg(_('The salesperson code must be three characters or less long'), 'error');
		$Errors[$i] = 'SalesmanCode';
		$i++;
	} elseif (mb_strlen($_POST['SalesmanCode']) == 0 or $_POST['SalesmanCode'] == '') {
		$InputError = 1;
		prnMsg(_('The salesperson code cannot be empty'), 'error');
		$Errors[$i] = 'SalesmanCode';
		$i++;
	} elseif (mb_strlen($_POST['SalesmanName']) > 30) {
		$InputError = 1;
		prnMsg(_('The salesperson name must be thirty characters or less long'), 'error');
		$Errors[$i] = 'SalesmanName';
		$i++;
	} elseif (mb_strlen($_POST['SalesArea']) == 0) {
		$InputError = 1;
		prnMsg(_('You must select an area for this salesman'), 'error');
		$Errors[$i] = 'SalesmanName';
		$i++;
	} elseif (mb_strlen($_POST['SManTel']) > 20) {
		$InputError = 1;
		prnMsg(_('The salesperson telephone number must be twenty characters or less long'), 'error');

	} elseif (mb_strlen($_POST['SManFax']) > 20) {
		$InputError = 1;
		prnMsg(_('The salesperson telephone number must be twenty characters or less long'), 'error');

	} elseif (!is_numeric(filter_number_format($_POST['CommissionRate1'])) OR !is_numeric(filter_number_format($_POST['CommissionRate2']))) {
		$InputError = 1;
		prnMsg(_('The commission rates must be a floating point number'), 'error');
	} elseif (!is_numeric(filter_number_format($_POST['Breakpoint']))) {
		$InputError = 1;
		prnMsg(_('The breakpoint should be a floating point number'), 'error');
	}

	if (!isset($_POST['SManTel'])) {
		$_POST['SManTel'] = '';
	}
	if (!isset($_POST['SManFax'])) {
		$_POST['SManFax'] = '';
	}
	if (!isset($_POST['CommissionRate1'])) {
		$_POST['CommissionRate1'] = 0;
	}
	if (!isset($_POST['CommissionRate2'])) {
		$_POST['CommissionRate2'] = 0;
	}
	if (!isset($_POST['Breakpoint'])) {
		$_POST['Breakpoint'] = 0;
	}
	if (!isset($_POST['Manager'])) {
		$_POST['Manager'] = 0;
	}
	if (!isset($_POST['Current'])) {
		$_POST['Current'] = 0;
	}

	if ($_POST['Current'] == 0) {
		$_POST['Manager'] = 0;
	}

	if (isset($SelectedSalesPerson) and $InputError != 1) {

		/*SelectedSalesPerson could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		$sql = "UPDATE salesman SET salesmanname='" . $_POST['SalesmanName'] . "',
									salesarea='" . $_POST['SalesArea'] . "',
									commissionrate1='" . filter_number_format($_POST['CommissionRate1']) . "',
									smantel='" . $_POST['SManTel'] . "',
									smanfax='" . $_POST['SManFax'] . "',
									breakpoint='" . filter_number_format($_POST['Breakpoint']) . "',
									commissionrate2='" . filter_number_format($_POST['CommissionRate2']) . "',
									manager='" . $_POST['Manager'] . "',
									current='" . $_POST['Current'] . "'
								WHERE salesmancode = '" . $SelectedSalesPerson . "'";

		$msg = _('Salesperson record for') . ' ' . $_POST['SalesmanName'] . ' ' . _('has been updated');
	} elseif ($InputError != 1) {

		/*Selected group is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new Sales-person form */

		$sql = "INSERT INTO salesman (salesmancode,
						salesmanname,
						salesarea,
						manager,
						commissionrate1,
						commissionrate2,
						breakpoint,
						smantel,
						smanfax,
						current)
				VALUES ('" . $_POST['SalesmanCode'] . "',
						'" . $_POST['SalesmanName'] . "',
						'" . $_POST['SalesArea'] . "',
						'" . $_POST['Manager'] . "',
						'" . filter_number_format($_POST['CommissionRate1']) . "',
						'" . filter_number_format($_POST['CommissionRate2']) . "',
						'" . filter_number_format($_POST['Breakpoint']) . "',
						'" . $_POST['SManTel'] . "',
						'" . $_POST['SManFax'] . "',
						'" . $_POST['Current'] . "'
					)";

		$msg = _('A new salesperson record has been added for') . ' ' . $_POST['SalesmanName'];
	}
	if ($InputError != 1) {

		/* if the sales person is a manager, ensure that there is no other manager for this area */
		if ($_POST['Manager'] == 1 and $_POST['Current'] == 1) {
			$ErrMsg = _('The update of the manager field failed because');
			$DbgMsg = _('The SQL that was used and failed was');
			$ManagerSQL = "UPDATE salesman SET manager=0 WHERE salesarea='" . $_POST['SalesArea'] . "'";
			$result = DB_query($ManagerSQL, $db, $ErrMsg, $DbgMsg);
		}

		//run the SQL from either of the above possibilites
		$ErrMsg = _('The insert or update of the salesperson failed because');
		$DbgMsg = _('The SQL that was used and failed was');
		$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

		prnMsg($msg, 'success');

		unset($SelectedSalesPerson);
		unset($_POST['SalesmanCode']);
		unset($_POST['SalesmanName']);
		unset($_POST['SalesArea']);
		unset($_POST['Manager']);
		unset($_POST['CommissionRate1']);
		unset($_POST['CommissionRate2']);
		unset($_POST['Breakpoint']);
		unset($_POST['SManFax']);
		unset($_POST['SManTel']);
		unset($_POST['Current']);
	}

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorsMaster'

	$sql = "SELECT COUNT(*) FROM custbranch WHERE  custbranch.salesman='" . $SelectedSalesPerson . "'";
	$result = DB_query($sql, $db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0] > 0) {
		prnMsg(_('Cannot delete this salesperson because branches are set up referring to them') . ' - ' . _('first alter the branches concerned') . '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('branches that refer to this salesperson'), 'error');

	} else {
		$sql = "SELECT COUNT(*) FROM salesanalysis WHERE salesanalysis.salesperson='" . $SelectedSalesPerson . "'";
		$result = DB_query($sql, $db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0] > 0) {
			prnMsg(_('Cannot delete this salesperson because sales analysis records refer to them'), '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('sales analysis records that refer to this salesperson'), 'error');
		} else {
			$sql = "SELECT COUNT(*) FROM www_users WHERE salesman='" . $SelectedSalesPerson . "'";
			$result = DB_query($sql, $db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0] > 0) {
				prnMsg(_('Cannot delete this salesperson because'), '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('user records that refer to this salesperson') . '.' . _('First delete any users that refer to this sales person'), 'error');
			} else {

				$sql = "DELETE FROM salesman WHERE salesmancode='" . $SelectedSalesPerson . "'";
				$ErrMsg = _('The salesperson could not be deleted because');
				$result = DB_query($sql, $db, $ErrMsg);

				prnMsg(_('Salesperson') . ' ' . $SelectedSalesPerson . ' ' . _('has been deleted from the database'), 'success');
				unset($SelectedSalesPerson);
				unset($delete);
			}
		}
	} //end if Sales-person used in GL accounts

}

if (!isset($SelectedSalesPerson)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedSalesPerson will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of Sales-persons will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/

	$sql = "SELECT salesmancode,
				salesmanname,
				salesarea,
				manager,
				smantel,
				smanfax,
				commissionrate1,
				breakpoint,
				commissionrate2,
				current
			FROM salesman";
	$result = DB_query($sql, $db);

	echo '<table class="selection">';
	echo '<tr>
			<th class="SortableColumn">' . _('Code') . '</th>
			<th class="SortableColumn">' . _('Name') . '</th>
			<th class="SortableColumn">' . _('SalesArea') . '</th>
			<th class="SortableColumn">' . _('Manager') . '</th>
			<th>' . _('Telephone') . '</th>
			<th>' . _('Facsimile') . '</th>
			<th>' . _('Comm Rate 1') . '</th>
			<th>' . _('Break') . '</th>
			<th>' . _('Comm Rate 2') . '</th>
			<th class="SortableColumn">' . _('Current') . '</th>
		</tr>';
	$k = 0;
	while ($myrow = DB_fetch_array($result)) {

		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}
		if ($myrow['current'] == 1) {
			$ActiveText = _('Yes');
		} else {
			$ActiveText = _('No');
		}
		if ($myrow['manager'] == 1) {
			$ManagerText = _('Yes');
		} else {
			$ManagerText = _('No');
		}

		$sql = "SELECT areadescription FROM areas WHERE areacode='" . $myrow['salesarea'] . "'";
		$AreaResult = DB_query($sql, $db);
		$AreaRow = DB_fetch_array($AreaResult);

		printf('<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				<td><a href="%sSelectedSalesPerson=%s">' . _('Edit') . '</a></td>
				<td><a href="%sSelectedSalesPerson=%s&amp;delete=1" onclick="return MakeConfirm(\'' . _('Are you sure you wish to delete this sales person?') . '\', \'Confirm Delete\', this);">' . _('Delete') . '</a></td>
				</tr>', $myrow['salesmancode'], $myrow['salesmanname'], $AreaRow['areadescription'], $ManagerText, $myrow['smantel'], $myrow['smanfax'], locale_number_format($myrow['commissionrate1'], 2), locale_number_format($myrow['breakpoint'], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($myrow['commissionrate2'], 2), $ActiveText, htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?', $myrow['salesmancode'], htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?', $myrow['salesmancode']);

	} //END WHILE LIST LOOP
	echo '</table><br />';
} //end of ifs and buts!

if (isset($SelectedSalesPerson)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Show All Sales People') . '</a></div>';
}

if (!isset($_GET['delete'])) {

	echo '<form onSubmit="return VerifyForm(this);" method="post" class="noPrint" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedSalesPerson)) {
		//editing an existing Sales-person

		$sql = "SELECT salesmancode,
					salesmanname,
					salesarea,
					manager,
					smantel,
					smanfax,
					commissionrate1,
					breakpoint,
					commissionrate2,
					current
				FROM salesman
				WHERE salesmancode='" . $SelectedSalesPerson . "'";

		$result = DB_query($sql, $db);
		$myrow = DB_fetch_array($result);

		$_POST['SalesmanCode'] = $myrow['salesmancode'];
		$_POST['SalesmanName'] = $myrow['salesmanname'];
		$_POST['SalesArea'] = $myrow['salesarea'];
		$_POST['Manager'] = $myrow['manager'];
		$_POST['SManTel'] = $myrow['smantel'];
		$_POST['SManFax'] = $myrow['smanfax'];
		$_POST['CommissionRate1'] = locale_number_format($myrow['commissionrate1'], 'Variable');
		$_POST['Breakpoint'] = locale_number_format($myrow['breakpoint'], $_SESSION['CompanyRecord']['decimalplaces']);
		$_POST['CommissionRate2'] = locale_number_format($myrow['commissionrate2'], 'Variable');
		$_POST['Current'] = $myrow['current'];


		echo '<input type="hidden" name="SelectedSalesPerson" value="' . $SelectedSalesPerson . '" />';
		echo '<input type="hidden" name="SalesmanCode" value="' . $_POST['SalesmanCode'] . '" />';
		echo '<table class="selection">
				<tr>
					<td>' . _('Salesperson code') . ':</td>
					<td>' . $_POST['SalesmanCode'] . '</td>
				</tr>';

	} else { //end of if $SelectedSalesPerson only do the else when a new record is being entered

		echo '<table class="selection">
				<tr>
					<td>' . _('Salesperson code') . ':</td>
					<td><input type="text" name="SalesmanCode" size="3" autofocus="autofocus" required="required" minlength="1" maxlength="3" /></td>
				</tr>';
	}
	if (!isset($_POST['SalesmanName'])) {
		$_POST['SalesmanName'] = '';
	}
	if (!isset($_POST['SalesArea'])) {
		$_POST['SalesArea'] = '';
	}
	if (!isset($_POST['SManTel'])) {
		$_POST['SManTel'] = '';
	}
	if (!isset($_POST['SManFax'])) {
		$_POST['SManFax'] = '';
	}
	if (!isset($_POST['CommissionRate1'])) {
		$_POST['CommissionRate1'] = 0;
	}
	if (!isset($_POST['CommissionRate2'])) {
		$_POST['CommissionRate2'] = 0;
	}
	if (!isset($_POST['Breakpoint'])) {
		$_POST['Breakpoint'] = 0;
	}
	if (!isset($_POST['Manager'])) {
		$_POST['Manager'] = 0;
	}
	if (!isset($_POST['Current'])) {
		$_POST['Current'] = 1;
	}

	echo '<tr>
			<td>' . _('Salesperson Name') . ':</td>
			<td><input type="text" name="SalesmanName"  size="30" required="required" minlength="1" maxlength="30" value="' . $_POST['SalesmanName'] . '" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('Sales Area') . ':' . '</td>
			<td><select required="required" minlength="1" tabindex="2" name="SalesArea">';
	$sql = "SELECT areacode, areadescription FROM areas ORDER BY areadescription";
	$ErrMsg = _('An error occurred in retrieving the areas from the database');
	$DbgMsg = _('The SQL that was used to retrieve the area information and that failed in the process was');
	$AreaResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);
	echo '<option value=""></option>';
	while ($AreaRow = DB_fetch_array($AreaResult)) {
		if ($_POST['SalesArea'] == $AreaRow['areacode']) {
			echo '<option selected="selected" value="' . $AreaRow['areacode'] . '">' . $AreaRow['areadescription'] . ' (' . $AreaRow['areacode'] . ')</option>';
		} //$_POST['SectionInAccounts'] == $secrow['sectionid']
		else {
			echo '<option value="' . $AreaRow['areacode'] . '">' . $AreaRow['areadescription'] . ' (' . $AreaRow['areacode'] . ')</option>';
		}
	} //$secrow = DB_fetch_array($secresult)
	echo '</select>';
	echo '</td></tr>';
	echo '<tr>
			<td>' . _('Telephone No') . ':</td>
			<td><input type="tel" name="SManTel" size="20" minlength="0" maxlength="20" value="' . $_POST['SManTel'] . '" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('Facsimile No') . ':</td>
			<td><input type="tel" name="SManFax" size="20" minlength="0" maxlength="20" value="' . $_POST['SManFax'] . '" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('Commission Rate 1') . ':</td>
			<td><input type="text" class="number" name="CommissionRate1" size="5" required="required" minlength="1" maxlength="5" value="' . $_POST['CommissionRate1'] . '" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('Breakpoint') . ':</td>
			<td><input type="text" class="number" name="Breakpoint" size="6" minlength="0" maxlength="6" value="' . $_POST['Breakpoint'] . '" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('Commission Rate 2') . ':</td>
			<td><input type="text" class="number" name="CommissionRate2" size="5" required="required" minlength="1" maxlength="5" value="' . $_POST['CommissionRate2'] . '" /></td>
		</tr>';

	echo '<tr>
			<td>' . _('Area Manager?') . ':</td>
			<td><select required="required" minlength="1" name="Manager">';
	if ($_POST['Manager'] == 1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if ($_POST['Manager'] == 0) {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select></td>
		</tr>';

	echo '<tr>
			<td>' . _('Current?') . ':</td>
			<td><select required="required" minlength="1" name="Current">';
	if ($_POST['Current'] == 1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if ($_POST['Current'] == 0) {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select></td>
		</tr>
		</table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="' . _('Enter Information') . '" />
		</div>
		</div>
		</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>