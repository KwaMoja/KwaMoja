<?php

include('includes/session.inc');

$Title = _('Tax Status Maintenance');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['TaxStatusID'])) {
	$TaxStatusID = strtoupper($_GET['TaxStatusID']);
} elseif (isset($_POST['TaxStatusID'])) {
	$TaxStatusID = strtoupper($_POST['TaxStatusID']);
} else {
	unset($TaxStatusID);
}


//initialise no input errors assumed initially before we test
$InputError = 0;

/* actions to take once the user has clicked the submit button
ie the page has called itself with some user input */

//first off validate inputs sensible


if ($InputError != 1 and (isset($_POST['update']) or isset($_POST['insert']))) {

	if (isset($_POST['update'])) {

		$SQL = "UPDATE prltaxstatus SET taxstatusdescription='" . $_POST['TaxStatusDescription'] . "',
											personalexemption='" . $_POST['PersonalExemption'] . "',
											additionalexemption='" . $_POST['AdditionalExemption'] . "',
											totalexemption='" . $_POST['TotalExemption'] . "'
										WHERE taxstatusid = '" . $TaxStatusID . "'";
		$ErrMsg = _('The tax status could not be updated because');
		$DbgMsg = _('The SQL that was used to update the tax status but failed was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
		prnMsg(_('The tax status master record for') . ' ' . $TaxStatusID . ' ' . _('has been updated'), 'success');

	} elseif (isset($_POST['insert'])) { //its a new tax status
		$SQL = "INSERT INTO prltaxstatus (taxstatusid,
												taxstatusdescription,
												personalexemption,
												additionalexemption,
												totalexemption
											) VALUES ('" . $_POST['TaxStatusID'] . "',
												'" . $_POST['TaxStatusDescription'] . "',
												'" . $_POST['PersonalExemption'] . "',
												'" . $_POST['AdditionalExemption'] . "',
												'" . $_POST['TotalExemption'] . "'
											)";
		$ErrMsg = _('The tax status') . ' ' . $_POST['TaxStatusDescription'] . ' ' . _('could not be added because');
		$DbgMsg = _('The SQL that was used to insert the tax status but failed was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

		prnMsg(_('A new tax status for') . ' ' . $_POST['TaxStatusDescription'] . ' ' . _('has been added to the database'), 'success');

	}
	unset($TaxStatusID);
	unset($_POST['TaxStatusDescription']);
	unset($_POST['PersonalExemption']);
	unset($_POST['AdditionalExemption']);
	unset($_POST['TotalExemption']);

} elseif ($InputError > 0) {
	prnMsg(_('Validation failed') . _('no updates or deletes took place'), 'warn');
}

if (isset($_POST['delete']) AND $_POST['delete'] != '') {

	//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'SuppTrans' , PurchOrders, SupplierContacts

	if ($CancelDelete == 0) {
		$SQL = "DELETE FROM prltaxstatus WHERE taxstatusid='$TaxStatusID'";
		$Result = DB_query($SQL);
		prnMsg(_('Tax status record for') . ' ' . $TaxStatusID . ' ' . _('has been deleted'), 'success');
		unset($TaxStatusID);
		unset($_SESSION['TaxStatusID']);
	} //end if Delete tax status
} //end of (isset($_POST['submit']))



//SupplierID exists - either passed when calling the form or from the form itself
echo '<form method="post" class="noPrint" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text noPrint" >
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/money_add.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
		</p>';

$SQL = "SELECT  taxstatusid,
				taxstatusdescription,
				personalexemption,
				additionalexemption,
				totalexemption
			FROM prltaxstatus";
$Result = DB_query($SQL);

if (DB_num_rows($Result)) {
	echo '<table class="selection">
			<tr>
				<th>' . _('Status ID') . '<th>
				<th>' . _('Description') . '<th>
				<th>' . _('Personal Excemption') . '<th>
				<th>' . _('Additional Excemption') . '<th>
				<th>' . _('Total Excemption') . '<th>
			</tr>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr>
				<td>' . $MyRow['taxstatusid'] . '<td>
				<td>' . $MyRow['taxstatusdescription'] . '<td>
				<td class="number">' . locale_number_format($MyRow['personalexemption'], $_SESSION['CompanyRecord']['decimalplaces']) . '<td>
				<td class="number">' . locale_number_format($MyRow['additionalexemption'], $_SESSION['CompanyRecord']['decimalplaces']) . '<td>
				<td class="number">' . locale_number_format($MyRow['totalexemption'], $_SESSION['CompanyRecord']['decimalplaces']) . '<td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?TaxStatusID=' . $MyRow['taxstatusid'] . '">' . _('Edit') . '</a></td>
			</tr>';
	}
	echo '</table>';
}

if (isset($TaxStatusID)) {

	$SQL = "SELECT  taxstatusid,
						taxstatusdescription,
						personalexemption,
						additionalexemption,
						totalexemption
			FROM prltaxstatus
			WHERE taxstatusid = '" . $TaxStatusID . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$_POST['TaxStatusDescription'] = $MyRow['taxstatusdescription'];
	$_POST['PersonalExemption'] = $MyRow['personalexemption'];
	$_POST['AdditionalExemption'] = $MyRow['additionalexemption'];
	$_POST['TotalExemption'] = $MyRow['totalexemption'];

	echo '<table class="selection">';
	echo '<input type="hidden" name="TaxStatusID" value="' . $TaxStatusID . '" />';
	echo '<tr>
			<td>' . _('Tax Status ID') . ':</td>
			<td>' . $TaxStatusID . '</td>
		</tr>';
} else {
	// its a new status being added
	echo '<table class="selection">';
	echo '<tr>
			<td>' . _('Tax Status ID') . ':</td>
			<td><input type="text" name="TaxStatusID" value="" size="12" maxlength="10" /></td>
		</tr>';
	$_POST['TaxStatusDescription'] = '';
	$_POST['PersonalExemption'] = 0;
	$_POST['AdditionalExemption'] = 0;
	$_POST['TotalExemption'] = 0;
}
echo '<tr>
		<td>' . _('Tax Status Description') . ':</td>
		<td><input type="text" name="TaxStatusDescription" value="' . $_POST['TaxStatusDescription'] . '" size="42" maxlength="40" //></td>
	</tr>';
echo '<tr>
		<td>' . _('Personal Exemption') . ':</td>
		<td><input type="text" class="number" name="PersonalExemption" value="' . $_POST['PersonalExemption'] . '" size="13" maxlength="12" /></td>
	</tr>';
echo '<tr>
		<td>' . _('Additional Exemption') . ':</td>
		<td><input type="text" class="number" name="AdditionalExemption" size="13" maxlength="12" value="' . $_POST['AdditionalExemption'] . '" /></td>
	</tr>';
echo '<tr>
		<td>' . _('Total Exemption') . ':</td>
		<td><input type="text" class="number" name="TotalExemption" size="13" maxlength="12" value="' . $_POST['TotalExemption'] . '" /></td>
	</tr>';

if (!isset($TaxStatusID)) {
	echo '</table>
			<div class="centre">
				<input type="submit" name="insert" value="' . _('Add These New Tax Status Details') . '" />
			</div>
		</form>';
} else {
	echo '</table>
			<div class="centre">
				<input type="submit" name="update" value="' . _('Update Tax Status') . '" />
			</div>';
	echo '<div class="centre">
			<input type="Submit" name="delete" value="' . _('Delete Employee') . '" onclick="return confirm("' . _('Are you sure you wish to delete this tax status?') . '");" />
		</div>
	</form>';
}

include('includes/footer.inc');
?>