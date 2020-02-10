<?php
include ('includes/session.php');

$Title = _('Hospital Configuration');

include ('includes/header.php');

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/hospital.png" title="', _('Hospital Configuration'), '" alt="" />', $Title, '
	</p>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	 ie the page has called itself with some user input */

	//first off validate inputs sensible
	if ($InputError != 1) {

		$SQL = array();

		if ($_SESSION['DispenseOnBill'] != $_POST['X_DispenseOnBill']) {
			$SQL[] = "UPDATE config SET confvalue = '" . $_POST['X_DispenseOnBill'] . "' WHERE confname = 'DispenseOnBill'";
		}
		if ($_SESSION['CanAmendBill'] != $_POST['X_CanAmendBill']) {
			$SQL[] = "UPDATE config SET confvalue = '" . $_POST['X_CanAmendBill'] . "' WHERE confname = 'CanAmendBill'";
		}
		if ($_SESSION['DefaultArea'] != $_POST['X_DefaultArea']) {
			$SQL[] = "UPDATE config SET confvalue='" . $_POST['X_DefaultArea'] . "' WHERE confname='DefaultArea'";
		}
		if ($_SESSION['DefaultSalesPerson'] != $_POST['X_DefaultSalesPerson']) {
			$SQL[] = "UPDATE config SET confvalue='" . $_POST['X_DefaultSalesPerson'] . "' WHERE confname='DefaultSalesPerson'";
		}
		if ($_SESSION['AutoPatientNo'] != $_POST['X_AutoPatientNo']) {
			$SQL[] = "UPDATE config SET confvalue='" . $_POST['X_AutoPatientNo'] . "' WHERE confname='AutoPatientNo'";
		}
		if ($_SESSION['InsuranceDebtorType'] != $_POST['X_InsuranceDebtorType']) {
			$SQL[] = "UPDATE config SET confvalue='" . $_POST['X_InsuranceDebtorType'] . "' WHERE confname='InsuranceDebtorType'";
		}
		$ErrMsg = _('The hospital configuration could not be updated because');
		$DbgMsg = _('The SQL that failed was') . ':';
		if (sizeof($SQL) > 0) {
			$Result = DB_Txn_Begin();
			foreach ($SQL as $SqlLine) {
				$Result = DB_query($SqlLine, $ErrMsg, $DbgMsg, true);
			}
			$Result = DB_Txn_Commit();
			prnMsg(_('Hospital configuration updated'), 'success');

			$ForceConfigReload = True; // Required to force a load even if stored in the session vars
			include ($PathPrefix . 'includes/GetConfig.php');
			$ForceConfigReload = False;
		}
	} else {
		prnMsg(_('Validation failed') . ', ' . _('no updates or deletes took place'), 'warn');
	}

}
/* end of if submit */

echo '<form method="post" action="', htmlspecialchars(htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8'), '">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

echo '<fieldset>
		<legend>', _('General Settings'), '</legend>';

echo '<field>
		<label for="X_DispenseOnBill">', _('Dispense on Bill'), ':</label>
		<select name="X_DispenseOnBill" autofocus="autofocus">';
if ($_SESSION['DispenseOnBill'] == '0') {
	echo '<option selected="selected" value="0">', _('No'), '</option>';
	echo '<option value="1">', _('Yes'), '</option>';
} else {
	echo '<option value="0">', _('No'), '</option>';
	echo '<option selected="selected" value="1">', _('Yes'), '</option>';
}
echo '</select>
	<fieldhelp>', _('Should items be deducted from stock automatically on production of the bill, or on actual dispensing?'), '</fieldhelp>
</field>';

echo '<field>
		<label for="X_CanAmendBill">', _('Cashiers can Amend Bills'), ':</label>
		<select name="X_CanAmendBill">';
if ($_SESSION['CanAmendBill'] == '0') {
	echo '<option selected="selected" value="0">', _('No'), '</option>';
	echo '<option value="1">', _('Yes'), '</option>';
} else {
	echo '<option value="0">', _('No'), '</option>';
	echo '<option selected="selected" value="1">', _('Yes'), '</option>';
}
echo '</select>
	<fieldhelp>' . _('Can the cashiers delete and insert lines in patients bills?') . '</fieldhelp>
</field>';

$SQL = "SELECT salesmancode, salesmanname FROM salesman";
$Result = DB_query($SQL);
echo '<field>
		<label for="X_DefaultSalesPerson">', _('Default Sales Person for Patients'), ':</label>
		<select required="required" minlength="1" name="X_DefaultSalesPerson">
			<option value=""></option>';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_SESSION['DefaultSalesPerson']) and $MyRow['salesmancode'] == $_SESSION['DefaultSalesPerson']) {
		echo '<option selected="selected" value="', $MyRow['salesmancode'], '">', $MyRow['salesmanname'], '</option>';
	} else {
		echo '<option value="', $MyRow['salesmancode'], '">', $MyRow['salesmanname'], '</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>' . _('The default sales person that will be used when patients are transferred from care2x') . '</fieldhelp>
</field>';

$SQL = "SELECT areacode, areadescription FROM areas";
$Result = DB_query($SQL);
echo '<field>
		<label for="X_DefaultArea">', _('Default Sales Area for Patients'), ':</label>
		<select required="required" minlength="1" name="X_DefaultArea">
			<option value=""></option>';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_SESSION['DefaultArea']) and $MyRow['areacode'] == $_SESSION['DefaultArea']) {
		echo '<option selected="selected" value="', $MyRow['areacode'], '">', $MyRow['areadescription'], '</option>';
	} else {
		echo '<option value="', $MyRow['areacode'], '">', $MyRow['areadescription'], '</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>' . _('The default sales area that will be used when patients are transferred from care2x') . '</fieldhelp>
</field>';

echo '<field>
		<label for="X_AutoPatientNo">', _('New Patient numbers Automatically Generated'), ':</label>
		<select name="X_AutoPatientNo">';
if ($_SESSION['AutoPatientNo'] == '0') {
	echo '<option selected="selected" value="0">', _('No'), '</option>';
	echo '<option value="1">', _('Yes'), '</option>';
} else {
	echo '<option value="0">', _('No'), '</option>';
	echo '<option selected="selected" value="1">', _('Yes'), '</option>';
}
echo '</select>
	<fieldhelp>' . _('If new patient numbers are to be automatically allocated select Yes here.') . '</fieldhelp>
</field>';

$SQL = "SELECT typeid, typename FROM debtortype";
$Result = DB_query($SQL);
echo '<field>
		<label for="X_InsuranceDebtorType">', _('Debtor type to use for Insurance companies'), '</label>
		<select name="X_InsuranceDebtorType">';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_SESSION['InsuranceDebtorType']) and $MyRow['typeid'] == $_SESSION['InsuranceDebtorType']) {
		echo '<option selected="selected" value="', $MyRow['typeid'], '">', $MyRow['typename'], '</option>';
	} else {
		echo '<option value="', $MyRow['typeid'], '">', $MyRow['typename'], '</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>' . _('The debtor type that is used for insurance companies. All Insurancecompanies must be of this type.') . '</fieldhelp>
</field>';

echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="submit" value="', _('Update'), '" />
	</div>
</form>';

include ('includes/footer.php');
?>