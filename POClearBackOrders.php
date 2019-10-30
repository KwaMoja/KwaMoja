<?php
include ('includes/session.php');
$Title = _('Clear purchase orders with quantity on back order');
include ('includes/header.php');

if (isset($_POST['ClearSupplierBackOrders'])) {
	$SQL = "UPDATE purchorderdetails
					INNER JOIN purchorders
						ON purchorderdetails.orderno=purchorders.orderno
					SET purchorderdetails.quantityord=purchorderdetails.quantityrecd,
						purchorderdetails.completed=1
					WHERE quantityrecd >0
						AND supplierno>= '" . $_POST['FromSupplierNo'] . "'
						AND supplierno <= '" . $_POST['ToSupplierNo'] . "'";
	$Result = DB_query($SQL);
	prnMsg(_('All back order quantities have been cleared'), 'success');

}
echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', _('Search'), '" alt="" />', ' ', $Title, '
	</p>';

$SQL = "SELECT min(supplierid) AS fromcriteria,
				max(supplierid) AS tocriteria
			FROM suppliers";

$Result = DB_query($SQL);
$MyRow = DB_fetch_array($Result);

echo '<fieldset>
		<legend>', _('Select Supplier Range'), '</legend>
		<field>
			<label for="FromSupplierNo">', _('From Supplier Code'), ':</label>
			<input type="text" name="FromSupplierNo" size="20" required="required" maxlength="20" value="', $MyRow['fromcriteria'], '" />
		</field>
		<field>
			<label for="ToSupplierNo"> ', _('To Supplier Code'), ':</label>
			<input type="text" name="ToSupplierNo" size="20" required="required" maxlength="20" value="', $MyRow['tocriteria'], '" />
		</field>
	</fieldset>
	<div class="centre">
		<input type="submit" name="ClearSupplierBackOrders" value="', _('Clear Supplier Back Orders'), '" />
	</div>
</form>';

include ('includes/footer.php');
?>