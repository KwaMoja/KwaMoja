<?php

include('includes/session.inc');
$Title = _('Clear purchase orders with quantity on back order');
include('includes/header.inc');

if (isset($_POST['ClearSupplierBackOrders'])) {
	$SQL = "UPDATE purchorderdetails
					INNER JOIN purchorders
						ON purchorderdetails.orderno=purchorders.orderno
					SET purchorderdetails.quantityord=purchorderdetails.quantityrecd,
						purchorderdetails.completed=1
					WHERE quantityrecd >0
						AND supplierno>= '" . $_POST['FromSupplierNo'] . "'
						AND supplierno <= '" . $_POST['ToSupplierNo'] . "'";
	$result = DB_query($SQL, $db);
	prnMsg( _('All back order quantities have been cleared'), 'success');

}
echo '<form onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

$sql = "SELECT min(supplierid) AS fromcriteria,
				max(supplierid) AS tocriteria
			FROM suppliers";

$result = DB_query($sql, $db);
$myrow = DB_fetch_array($result);

echo '<table>
		<tr>
			<td>' . _('From Supplier Code') . ':</td>
			<td><input type="text" name="FromSupplierNo" size="20" required="required" minlength="1" maxlength="20" value="' . $myrow['fromcriteria'] . '" /></td>
		</tr>
		<tr>
			<td> ' . _('To Supplier Code') . ':</td>
			<td><input type="text" name="ToSupplierNo" size="20" required="required" minlength="1" maxlength="20" value="' . $myrow['tocriteria'] . '" /></td>
		</tr>
	</table>
	<div class="centre">
		<button type="submit" name="ClearSupplierBackOrders">' . _('Clear Supplier Back Orders') . '</button>
	</div>
	</form>';

include('includes/footer.inc');
?>