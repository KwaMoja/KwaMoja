<?php
/* $Id: CounterSales.php 4469 2011-01-15 02:28:37Z daintree $*/
$PageSecurity=1;

/* Session started in session.inc for password checking and authorisation level check
config.php is in turn included in session.inc $PageSecurity now comes from session.inc (and gets read in by GetConfig.php*/

include('includes/session.inc');

$Title = _('Quick Invoicing');

include('includes/header.inc');

echo '<div class="container">';
echo '<form method="post" class="noPrint" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Update=Customers">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<div class="box">
		<div class="box_header">' . _('Search Criteria') . '</div>

		<label class="box">' . _('Enter Partial Code') . '</label>
		<input type="text" name="PartialCode" />

		<label class="box">' . _('Enter Partial Name') . '</label>
		<input type="text" name="PartialName" />

		<label class="box">' . _('Enter Partial Address') . '</label>
		<input type="text" name="PartialAddress" />

		<label class="box">' . _('Enter Partial Phone Number') . '</label>
		<input type="text" name="PartialPhone" />
	</div>';
echo '</form>';
CustomerBox($db);
echo '<div class="box">
		<div class="box_header">' . _('Branches') . '</div>
		<select size="15" width="98%">
		</select>
	</div>';
echo '</div>';

echo '<p width="100%">hhhhhhh</p>';

include('includes/footer.inc');

function CustomerBox($db) {
	$CustomerSQL = "SELECT debtorno,
							name
						FROM debtorsmaster
						LIMIT 15";

	$CustomerResult = DB_query($CustomerSQL, $db);
	echo '<div class="box">
			<div class="box_header" id="customers">' . _('Customers') . '</div>
			<select size="15" class="box bloc">';

	while ($MyCustomerRow = DB_fetch_array($CustomerResult)) {
		echo '<option value="' . $MyCustomerRow['debtorno'] . '">' . $MyCustomerRow['name'] . '</option>';
	}

	echo '</select>
		</div>';
}

?>