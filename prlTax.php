<?php

include('includes/session.inc');

$Title = _('Tax Table Section');

include('includes/header.inc');

if (isset($_GET['Bracket'])) {
	$Bracket = $_GET['Bracket'];
} elseif (isset($_POST['Bracket'])) {

	$Bracket = $_POST['Bracket'];
} else {
	unset($Bracket);
}
?>
<a href="prlUserSettings.php">Back to User Settings
    </a>
	<?php

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strlen($_POST['bracket']) == 0) {
		$InputError = 1;
		prnMsg(_('The Tax Bracket cannot be empty'), 'error');
	}

	if ($InputError != 1) {

		if (!isset($_POST["New"])) {
			$SQL = "UPDATE prltaxtablerate SET
					rangefrom='" . DB_escape_string($_POST['RangeFr']) . "',
					rangeto='" . DB_escape_string($_POST['RangeTo']) . "',
					fixtaxableamount='" . DB_escape_string($_POST['FixAmt']) . "',
					fixtax='" . DB_escape_string($_POST['FixTax']) . "',
					percentofexcessamount='" . DB_escape_string($_POST['Percent']) . "',
					taxname='" . DB_escape_string($_POST['Taxname']) . "'
						WHERE bracket='$Bracket'";

			$ErrMsg = _('The Tax could not be updated because');
			$DbgMsg = _('The SQL that was used to update the Tax but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
			prnMsg(_('The Tax master record for') . ' ' . $Bracket . ' ' . _('has been updated'), 'success');

		} else { //its a new Tax
			$SQL = "INSERT INTO prltaxtablerate (
					bracket,
					rangefrom,
					rangeto,
					fixtaxableamount,
					fixtax,
					percentofexcessamount,
					taxname )
				 VALUES ('" . DB_escape_string($_POST['bracket']) . "',
					 	'" . DB_escape_string($_POST['RangeFr']) . "',
						'" . DB_escape_string($_POST['RangeTo']) . "',
						'" . DB_escape_string($_POST['FixAmt']) . "',
						'" . DB_escape_string($_POST['FixTax']) . "',
						'" . DB_escape_string($_POST['Percent']) . "',
						'" . DB_escape_string($_POST['Taxname']) . "')";
			$ErrMsg = _('The Tax') . ' ' . $_POST['FixAmt'] . ' ' . _('could not be added because');
			$DbgMsg = _('The SQL that was used to insert the Tax but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
			prnMsg(_($SQL), '');

			prnMsg(_('A new Tax has been added to the database'), 'success');

			unset($_POST['bracket']);
			unset($_POST['RangeFr']);
			unset($_POST['RangeTo']);
			unset($_POST['FixAmt']);
			unset($_POST['FixTax']);
			unset($_POST['Percent']);
			unset($_POST['Taxname']);
		}

	} else {

		prnMsg(_('Validation failed') . _('no updates or deletes took place'), 'warn');

	}

} elseif (isset($_POST['delete']) AND $_POST['delete'] != '') {

	//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'SuppTrans' , PurchOrders, SupplierContacts
	if ($CancelDelete == 0) {
		$SQL = "DELETE FROM prltaxtablerate WHERE bracket='$Bracket'";
		$Result = DB_query($SQL);
		prnMsg(_('Tax record for') . ' ' . $Bracket . ' ' . _('has been deleted'), 'success');
		unset($Bracket);
		unset($_SESSION['Bracket']);
	} //end if Delete paypayperiod
}


if (!isset($Bracket)) {

	/*If the page was called without $SupplierID passed to page then assume a new supplier is to be entered show a form with a Supplier Code field other wise the form showing the fields with the existing entries against the supplier will show for editing with only a hidden SupplierID field*/

	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';

	echo '<input type="hidden" name="New" value="Yes">';

	echo '<table>';
	echo '<tr><td>' . _('Tax Bracket') . ":</td><td><input type='text' name='bracket' SIZE=5 MAXLENGTH=12></td></tr>";
	echo '<tr><td>' . _('Range From') . ":</td><td><input type='text' name='RangeFr' SIZE=14 MAXLENGTH=12></td></tr>";
	echo '<tr><td>' . _('Range To') . ":</td><td><input type='text' name='RangeTo' SIZE=14 MAXLENGTH=12></td></tr>";
	echo '<tr><td>' . _('Fix Taxable Amount') . ":</td><td><input type='text' name='FixAmt' SIZE=14 MAXLENGTH=12></td></tr>";
	echo '<tr><td>' . _('Fix Tax for Fix Taxable Amount') . ":</td><td><input type='text' name='FixTax' SIZE=14 MAXLENGTH=12></td></tr>";
	echo '<tr><td>' . _('% of excess over Fix Taxable Amount') . ":</td><td><input type='text' name='Percent' SIZE=6 MAXLENGTH=4></td></tr>";
	echo '<tr><td>' . _('Tax Name') . ":</td><td><input type='text' name='Taxname' SIZE=15 MAXLENGTH=15></td></tr>";
	//	echo '</select></td></tr>';
	echo "</select></td></tr></table><p><input type='Submit' name='submit' value='" . _('Insert New Tax') . "'>";
	echo '</form>';

	$SQL = "SELECT bracket,
					rangefrom,
					rangeto,
					fixtaxableamount,
					fixtax,
					percentofexcessamount,
					taxname
				FROM prltaxtablerate
				ORDER BY bracket";

	$ErrMsg = _('Could not get Tax because');
	$Result = DB_query($SQL, $ErrMsg);

	echo '<table border=1>';
	echo "<tr>
		<th>" . _('Bracket') . "</td>
		<th>" . _('Range From') . "</td>
		<th>" . _('Range To') . "</td>
		<th>" . _('Fix Taxable Amount') . "</td>
		<th>" . _('Fix Tax for Fix Taxable Amount') . "</td>
		<th>" . _('% of excess over Fix Taxable Amount') . "</td>
		<th>" . _('Tax Name') . "</td>
	</tr>";


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
		echo '<td>' . $MyRow[2] . '</td>';
		echo '<td>' . $MyRow[3] . '</td>';
		echo '<td>' . $MyRow[4] . '</td>';
		echo '<td>' . $MyRow[5] . '</td>';
		echo '<td>' . $MyRow[6] . '</td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?&Bracket=' . $MyRow[0] . '">' . _('Edit') . '</a></td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?&Bracket=' . $MyRow[0] . '&delete=1">' . _('Delete') . '</a></td>';
		echo '</tr>';

	} //END WHILE LIST LOOP
	echo '</table><p>';


} else {
	//Bracket exists - either passed when calling the form or from the form itself

	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
	echo '<table>';

	//if (!isset($_POST["New"])) {
	if (!isset($_POST["New"])) {
		$SQL = "SELECT bracket,
					rangefrom,
					rangeto,
					fixtaxableamount,
					fixtax,
					percentofexcessamount,
					taxname
				FROM prltaxtablerate
				WHERE bracket='$Bracket'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['RangeFr'] = $MyRow['rangefrom'];
		$_POST['RangeTo'] = $MyRow['rangeto'];
		$_POST['FixAmt'] = $MyRow['fixtaxableamount'];
		$_POST['FixTax'] = $MyRow['fixtax'];
		$_POST['Percent'] = $MyRow['percentofexcessamount'];
		$_POST['Taxname'] = $MyRow['taxname'];
		echo '<input type="hidden" name="Bracket" value="' . $Bracket . '">';

	} else {
		// its a new Tax being added
		echo '<input type="hidden" name="New" value="Yes">';
		echo '<tr><td>' . _('Tax Code') . ":</td><td><input type='text' name='Bracket' value='$Bracket' SIZE=5 MAXLENGTH=4></td></tr>";
	}
	echo '<tr><td>' . _('Range From') . ":</td><td><input type='text' name='RangeFr' SIZE=14 MAXLENGTH=12 value='" . $_POST['RangeFr'] . "'></td></tr>";
	echo '<tr><td>' . _('Range To') . ":</td><td><input type='text' name='RangeTo' SIZE=14 MAXLENGTH=12 value='" . $_POST['RangeTo'] . "'></td></tr>";
	echo '<tr><td>' . _('Fix Taxable Amount') . ":</td><td><input type='text' name='FixAmt' SIZE=14 MAXLENGTH=12 value='" . $_POST['FixAmt'] . "'></td></tr>";
	echo '<tr><td>' . _('Fix Tax for Fix Taxable Amount') . ":</td><td><input type='text' name='FixTax' SIZE=14 MAXLENGTH=12 value='" . $_POST['FixTax'] . "'></td></tr>";
	echo '<tr><td>' . _('% of excess over Fix Taxable Amount') . ":</td><td><input type='text' name='Percent' SIZE=6 MAXLENGTH=4 value='" . $_POST['Percent'] . "'></td></tr>";
	echo '<tr><td>' . _('Tax Name') . ":</td><td><input type='text' name='taxname' SIZE=14 MAXLENGTH=12 value='" . $_POST['Taxname'] . "'></td></tr>";
	echo '</select></td></tr>';

	if (isset($_POST["New"])) {
		echo "</table><p><input type='Submit' name='submit' value='" . _('Add These New Tax Details') . "'></form>";
	} else {
		echo "</table><p><input type='Submit' name='submit' value='" . _('Update Tax') . "'>";
		echo '<p><font color=red><B>' . _('WARNING') . ': ' . _('There is no second warning if you hit the delete button below') . '. ' . _('However checks will be made to ensure before the deletion is processed') . '<br /></FONT></B>';
		echo '<input type="Submit" name="delete" value="' . _('Delete Tax') . '" onclick="return confirm("' . _('Are you sure you wish to delete this Tax?') . '");"></form>';
	}

} // end of main ifs

include('includes/footer.inc');
?>