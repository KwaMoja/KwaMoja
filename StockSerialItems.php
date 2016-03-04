<?php

include('includes/session.inc');
$Title = _('Stock Of Controlled Items');
include('includes/header.inc');

echo '<p class="page_title_text" >
		<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/inventory.png" title="' . _('Inventory') . '" alt="" /><b>' . $Title . '</b>
	</p>';

if (isset($_GET['StockID'])) {
	if (ContainsIllegalCharacters($_GET['StockID'])) {
		prnMsg(_('The stock code sent to this page appears to be invalid'), 'error');
		include('includes/footer.inc');
		exit;
	}
	$StockId = trim(mb_strtoupper($_GET['StockID']));
} else {
	prnMsg(_('This page must be called with parameters specifying the item to show the serial references and quantities') . '. ' . _('It cannot be displayed without the proper parameters being passed'), 'error');
	include('includes/footer.inc');
	exit;
}

$Result = DB_query("SELECT description,
							units,
							mbflag,
							decimalplaces,
							serialised,
							controlled,
							perishable
						FROM stockmaster
						WHERE stockid='" . $StockId . "'", _('Could not retrieve the requested item because'));

$MyRow = DB_fetch_array($Result);

$Description = $MyRow['description'];
$UOM = $MyRow['units'];
$DecimalPlaces = $MyRow['decimalplaces'];
$Serialised = $MyRow['serialised'];
$Controlled = $MyRow['controlled'];
$Perishable = $MyRow['perishable'];

if ($MyRow['mbflag'] == 'K' or $MyRow['mbflag'] == 'A' or $MyRow['mbflag'] == 'D') {

	prnMsg(_('This item is either a kitset or assembly or a dummy part and cannot have a stock holding') . '. ' . _('This page cannot be displayed') . '. ' . _('Only serialised or controlled items can be displayed in this page'), 'error');
	include('includes/footer.inc');
	exit;
}

$LocationsSQL = "SELECT locationname
					FROM locations
					INNER JOIN locationusers
						ON locationusers.loccode=locations.loccode
						AND locationusers.userid='" .  $_SESSION['UserID'] . "'
						AND locationusers.canview=1
					WHERE locations.loccode='" . $_GET['Location'] . "'";
$ErrMsg = _('Could not retrieve the stock location of the item because');
$DbgMsg = _('The SQL used to lookup the location was');
$Result = DB_query($LocationsSQL, $ErrMsg, $DbgMsg);

$MyRow = DB_fetch_row($Result);

$SQL = "SELECT serialno,
				quantity,
				expirationdate
			FROM stockserialitems
			INNER JOIN locationusers
				ON locationusers.loccode=stockserialitems.loccode
				AND locationusers.userid='" .  $_SESSION['UserID'] . "'
				AND locationusers.canview=1
			WHERE stockserialitems.loccode='" . $_GET['Location'] . "'
				AND stockid = '" . $StockId . "'
				AND quantity <>0";


$ErrMsg = _('The serial numbers/batches held cannot be retrieved because');
$LocStockResult = DB_query($SQL, $ErrMsg);

echo '<table class="selection">';

if ($Serialised == 1) {
	echo '<tr>
			<th colspan="5"><font color="navy" size="2">' . _('Serialised items in') . ' ';
} else {
	echo '<tr>
			<th colspan="11"><font color="navy" size="2">' . _('Controlled items in') . ' ';
}
echo $MyRow[0] . '</font></th></tr>';

echo '<tr>
		<th colspan="11"><font color="navy" size="2">' . $StockId . '-' . $Description . '</b>  (' . _('In units of') . ' ' . $UOM . ')</font></th>
	</tr>';

if ($Serialised == 1 and $Perishable == 0) {
	echo '<tr>
			<th>' . _('Serial Number') . '</th>
			<th></th>
			<th>' . _('Serial Number') . '</th>
			<th></th>
			<th>' . _('Serial Number') . '</th>
		</tr>';
} else if ($Serialised == 1 and $Perishable == 1) {
	echo '<tr>
			<th>' . _('Serial Number') . '</th>
			<th>' . _('Expiry Date') . '</th>
			<th>' . _('Serial Number') . '</th>
			<th>' . _('Expiry Date') . '</th>
			<th>' . _('Serial Number') . '</th>
			<th>' . _('Expiry Date') . '</th>
		</tr>';
} else if ($Serialised == 0 and $Perishable == 0) {
	echo '<tr>
			<th>' . _('Batch/Bundle Ref') . '</th>
			<th>' . _('Quantity On Hand') . '</th>
			<th></th>
			<th>' . _('Batch/Bundle Ref') . '</th>
			<th>' . _('Quantity On Hand') . '</th>
			<th></th>
			<th>' . _('Batch/Bundle Ref') . '</th>
			<th>' . _('Quantity On Hand') . '</th>
		</tr>';
} else if ($Serialised == 0 and $Perishable == 1) {
	echo '<tr>
			<th>' . _('Batch/Bundle Ref') . '</th>
			<th>' . _('Quantity On Hand') . '</th>
			<th>' . _('Expiry Date') . '</th>
			<th></th>
			<th>' . _('Batch/Bundle Ref') . '</th>
			<th>' . _('Quantity On Hand') . '</th>
			<th>' . _('Expiry Date') . '</th>
			<th></th>
			<th>' . _('Batch/Bundle Ref') . '</th>
			<th>' . _('Quantity On Hand') . '</th>
			<th>' . _('Expiry Date') . '</th>
		</tr>';
}
$TotalQuantity = 0;
$j = 1;
$Col = 0;
$BGColor = '#CCCCCC';
while ($MyRow = DB_fetch_array($LocStockResult)) {

	if ($Col == 0 and $BGColor == '#EEEEEE') {
		$BGColor = '#CCCCCC';
		echo '<tr class="EvenTableRows">';
	} elseif ($Col == 0) {
		$BGColor = '#EEEEEE';
		echo '<tr class="OddTableRows">';
	}

	$TotalQuantity += $MyRow['quantity'];

	if ($Serialised == 1 and $Perishable == 0) {
		echo '<td>' . $MyRow['serialno'] . '</td>';
		echo '<th></th>';
	} else if ($Serialised == 1 and $Perishable == 1) {
		echo '<td>' . $MyRow['serialno'] . '</td>
				<td>' . ConvertSQLDate($MyRow['expirationdate']) . '</td>';
	} else if ($Serialised == 0 and $Perishable == 0) {
		echo '<td>' . $MyRow['serialno'] . '</td>
			<td class="number">' . locale_number_format($MyRow['quantity'], $DecimalPlaces) . '</td>';
		echo '<th></th>';
	} else if ($Serialised == 0 and $Perishable == 1) {
		echo '<td>' . $MyRow['serialno'] . '</td>
			<td class="number">' . locale_number_format($MyRow['quantity'], $DecimalPlaces) . '</td>
			<td>' . ConvertSQLDate($MyRow['expirationdate']) . '</td>
			<th></th>';
	}
	//end of page full new headings if
	$Col++;
	if ($Col == 3) {
		echo '</tr>';
		$Col = 0;
	}
}
//end of while loop

echo '</table><br />';
echo '<div class="centre"><br /><b>' . _('Total quantity') . ': ' . locale_number_format($TotalQuantity, $DecimalPlaces) . '<br /></div>';

echo '</form>';
include('includes/footer.inc');
?>