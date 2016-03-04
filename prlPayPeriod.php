<?php

include('includes/session.inc');

$Title = _('Pay Period Section');

include('includes/header.inc');

if (isset($_GET['PayPeriodID'])) {
	$PayPeriodID = $_GET['PayPeriodID'];
} elseif (isset($_POST['PayPeriodID'])) {
	$PayPeriodID = $_POST['PayPeriodID'];
} else {
	unset($PayPeriodID);
}

echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['insert']) or isset($_POST['update'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strpos($_POST['PayPeriodName'], '&') > 0 or strpos($_POST['PayPeriodName'], "'") > 0) {
		$InputError = 1;
		prnMsg(_('The Pay Period description cannot contain the character') . " '&' " . _('or the character') . " '", 'error');
	}
	if (trim($_POST['PayPeriodName']) == '') {
		$InputError = 1;
		prnMsg(_('The Pay Period description may not be empty'), 'error');
	}
	if (is_numeric($_POST['PayPeriodName'])) /* Check if the bank code is numeric */ {
		prnMsg(_('Pay Description must be Character'), 'error');
		$InputError = 1;
	}
	if (strlen($PayPeriodID) == 0) {
		$InputError = 1;
		prnMsg(_('The Pay Period Code cannot be empty'), 'error');
	}

	if ($InputError != 1) {

		if (isset($_POST['update'])) {

			$SQL = "UPDATE prlpayperiod SET payperioddesc='" . $_POST['PayPeriodName'] . "',
							numberofpayday='" . $_POST['NumberOfPayday'] . "',
							dayofpay='" . $_POST['DayOfPay'] . "'
						WHERE payperiodid = '$PayPeriodID'";

			$ErrMsg = _('The pay period could not be updated because');
			$DbgMsg = _('The SQL that was used to update the pay period but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
			prnMsg(_('The pay period master record for') . ' ' . $PayPeriodID . ' ' . _('has been updated'), 'success');
			unset($PayPeriodID);
			unset($_POST['PayPeriodName']);
			unset($_POST['NumberOfPayday']);

		} elseif (isset($_POST['insert'])) { //its a new pay period

			$SQL = "INSERT INTO prlpayperiod (payperiodid,
							payperioddesc,
							numberofpayday,
							dayofpay
						) VALUES (
							'" . $PayPeriodID . "',
							'" . $_POST['PayPeriodName'] . "',
							'" . $_POST['NumberOfPayday'] . "',
							'" . $_POST['DayOfPay'] . "'
						)";

			$ErrMsg = _('The pay period') . ' ' . $_POST['PayPeriodName'] . ' ' . _('could not be added because');
			$DbgMsg = _('The SQL that was used to insert the pay period but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

			prnMsg(_('A new pay period for') . ' ' . $_POST['PayPeriodName'] . ' ' . _('has been added to the database'), 'success');

			unset($PayPeriodID);
			unset($_POST['PayPeriodName']);
			unset($_POST['NumberOfPayday']);
			unset($_POST['DayOfPay']);
		}

	} else {
		prnMsg(_('Validation failed') . _('no updates or deletes took place'), 'warn');
	}

} elseif (isset($_POST['delete']) and $_POST['delete'] != '') {

	//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'SuppTrans' , PurchOrders, SupplierContacts
	if ($CancelDelete == 0) {
		$SQL = "DELETE FROM prlpayperiod WHERE payperiodid='$PayPeriodID'";
		$Result = DB_query($SQL);
		prnMsg(_('Pay Period record for') . ' ' . $PayPeriodID . ' ' . _('has been deleted'), 'success');
		unset($PayPeriodID);
		unset($_SESSION['PayPeriodID']);
	} //end if Delete paypayperiod
}

$SQL = "SELECT payperiodid,
				payperioddesc,
				numberofpayday,
				dayofpay
			FROM prlpayperiod
			ORDER BY payperiodid";

$ErrMsg = _('Could not get pay period because');
$Result = DB_query($SQL, $ErrMsg);

if (DB_num_rows($Result)) {
	echo '<table class="selection">';
	echo '<tr>
			<th>' . _('Pay Code') . '</td>
			<th>' . _('Pay Description') . '</td>
			<th>' . _('Number of Payday') . '</td>
			<th>' . _('Day in Peiod to Pay') . '</td>
		</tr>';

	$k = 0; //row colour counter
	while ($MyRow = DB_fetch_row($Result)) {

		if ($k == 1) {
			echo "<tr bgcolor='#CCCCCC'>";
			$k = 0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}
		echo '<td>' . $MyRow[0] . '</td>';
		echo '<td>' . $MyRow[1] . '</td>';
		echo '<td class="number">' . $MyRow[2] . '</td>';
		echo '<td class="number">' . $MyRow[3] . '</td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?&PayPeriodID=' . $MyRow[0] . '">' . _('Edit') . '</a></td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?&PayPeriodID=' . $MyRow[0] . '&delete=1">' . _('Delete') . '</a></td>';
		echo '</tr>';
	}
	echo '</table>';
}
//PayPeriodID exists - either passed when calling the form or from the form itself

echo '<form method="post" class="noPrint" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="selection">';

//if (!isset($_POST["New"])) {
if (isset($PayPeriodID)) {
	$SQL = "SELECT payperiodid,
				payperioddesc,
				numberofpayday
			FROM prlpayperiod
			WHERE payperiodid = '" . $PayPeriodID . "'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['PayPeriodName'] = $MyRow['payperioddesc'];
	$_POST['NumberOfPayday'] = $MyRow['numberofpayday'];
	echo '<input type="hidden" name="PayPeriodID" value="' . $PayPeriodID . '">';
	echo '<tr>
			<td>' . _('Pay Period Code') . ':</td>
			<td>' . $PayPeriodID . '</td>
		</tr>';
} else {
	// its a new supplier being added
	echo '<tr>
			<td>' . _('Pay Period Code') . ':</td>
			<td><input type="text" name="PayPeriodID" value="" size="5" maxlength="4" /></td>
		</tr>';
	$_POST['PayPeriodName'] = '';
	$_POST['NumberOfPayday'] = 0;
}
echo '<tr>
		<td>' . _('Pay Description') . ':</td>
		<td><input type="text" name="PayPeriodName" size="16" maxlength="15" value="' . $_POST['PayPeriodName'] . '" /></td>
	</tr>';
echo '<tr>
		<td>' . _('Number of Pay Day') . ':</td>
		<td><input type="text" class="number" name="NumberOfPayday" size="12" maxlength="11" value="' . $_POST['NumberOfPayday'] . '" /></td>
	</tr>';
echo '<tr>
		<td>' . _('Day in Peiod to Pay') . ':</td>
		<td><input type="text" class="number" name="DayOfPay" size="12" maxlength="11" value="' . $_POST['NumberOfPayday'] . '" /></td>
	</tr>';

if (!isset($PayPeriodID)) {
	echo '</table>
			<div class="centre">
				<input type="submit" name="insert" value="' . _('Add These New Pay Period Details') . '" />
			</div>
		</form>';
} else {
	echo '</table>
			<div class="centre">
				<input type="submit" name="update" value="' . _('Update Pay Period') . '">
				<input type="submit" name="delete" value="' . _('Delete Pay Period') . '" onclick="return confirm("' . _('Are you sure you wish to delete this pay period?') . '");\" />
			</div>
		</form>';
}

include('includes/footer.inc');
?>