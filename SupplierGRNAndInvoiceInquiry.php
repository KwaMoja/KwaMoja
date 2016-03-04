<?php

include('includes/session.inc');

$Title = _('Supplier Invoice and GRN inquiry');
include('includes/header.inc');

if (isset($_GET['SelectedSupplier'])) {
	$SupplierID = $_GET['SelectedSupplier'];
} elseif (isset($_POST['SelectedSupplier'])) {
	$SupplierID = $_POST['SelectedSupplier'];
} else {
	prnMsg(_('The page must be called from suppliers selected interface, please click following link to select the supplier'), 'error');
	echo '<a href="' . $RootPath . '/SelectSupplier.php">' . _('Select Supplier') . '</a>';
	include('includes/footer.inc');
	exit;
}

if (isset($_GET['SupplierName'])) {
	$SupplierName = $_GET['SupplierName'];
} else if (isset($_POST['SupplierName'])) {
	$SupplierName = $_POST['SupplierName'];
}

if (!isset($_POST['SupplierRef']) or trim($_POST['SupplierRef']) == '') {
	$_POST['SupplierRef'] = '';
	if (empty($_POST['GRNBatchNo']) and empty($_POST['InvoiceNo'])) {
		$_POST['GRNBatchNo'] = '';
		$_POST['InvoiceNo'] = '';
	} elseif (!empty($_POST['GRNBatchNo']) and !empty($_POST['InvoiceNo'])) {
		$_POST['InvoiceNo'] = '';
	}
} elseif (isset($_POST['GRNBatchNo']) or isset($_POST['InvoiceNo'])) {
	$_POST['GRNBatchNo'] = '';
	$_POST['InvoiceNo'] = '';
}

echo '<p class="page_title_text">' . _('Supplier Invoice and Delivery Note Inquiry') . '<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/transactions.png" alt="" />' . _('Supplier') . ': ' . $SupplierName . '</p>';
echo '<div class="page_help_text">' . _('The supplier\'s delivery note is prefer to GRN No, and GRN No is prefered to Invoice No') . '</div>';
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<input type="hidden" name="SelectedSupplier" value="' . $SupplierID . '" />';
echo '<input type="hidden" name="SupplierName" value="' . $SupplierName . '" />';

echo '<table class="selection">
		<tr>
			<td>' . _('Part of Supplier\'s Delivery Note') . ':</td><td><input type="text" name="SupplierRef" value="' . $_POST['SupplierRef'] . '" size="20" maxlength="30" ></td>
			<td>' . _('GRN No') . ':</td><td><input type="text" name="GRNBatchNo" value="' . $_POST['GRNBatchNo'] . '" size="6" maxlength="6" /></td>
			<td>' . _('Invoice No') . ':</td><td><input type="text" name="InvoiceNo" value="' . $_POST['InvoiceNo'] . '" size="11" maxlength="11" /></td>
		</tr>
	</table>';

echo '<div class="center">
		<input type="submit" name="Submit" value="' . _('Submit') . '" />
	</div>';

if (isset($_POST['Submit'])) {
	$Where = '';
	if (isset($_POST['SupplierRef']) and trim($_POST['SupplierRef']) != '') {
		$SupplierRef = trim($_POST['SupplierRef']);
		$WhereSupplierRef = " AND grns.supplierref LIKE '%" . $SupplierRef . "%'";
		$Where .= $WhereSupplierRef;
	} elseif (isset($_POST['GRNBatchNo']) and trim($_POST['GRNBatchNo']) != '') {
		$GRNBatchNo = trim($_POST['GRNBatchNo']);
		$WhereGRN = " AND grnbatch LIKE '%" . $GRNBatchNo . "%'";
		$Where .= $WhereGRN;
	} elseif (isset($_POST['InvoiceNo']) and (trim($_POST['InvoiceNo']) != '')) {
		$InvoiceNo = trim($_POST['InvoiceNo']);
		$WhereInvoiceNo = " AND suppinv LIKE '%" . $InvoiceNo . "%'";
		$Where .= $WhereInvoiceNo;
	}

	$SQL = "SELECT grnbatch,
					grns.supplierref,
					suppinv,purchorderdetails.orderno
				FROM grns
				INNER JOIN purchorderdetails
					ON grns.podetailitem=purchorderdetails.podetailitem
				LEFT JOIN suppinvstogrn
					ON grns.grnno=suppinvstogrn.grnno
				WHERE supplierid='" . $SupplierID . "'" . $Where;
	$ErrMsg = _('Failed to retrieve supplier invoice and grn data');
	$Result = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result) > 0) {
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('Supplier Delivery Note') . '</th>
						<th class="SortedColumn">' . _('GRN Batch No') . '</th>
						<th class="SortedColumn">' . _('PO No') . '</th>
						<th class="SortedColumn">' . _('Invoice No') . '</th>
					</tr>
				</thead>';
		$k = 0;

		while ($MyRow = DB_fetch_array($Result)) {
			if ($k == 0) {
				echo '<tr class="EvenTableRows">';
				$k = 1;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 0;
			}
			echo '<td>' . $MyRow['supplierref'] . '</td>
				<td><a href="' . $RootPath . '/PDFGrn.php?GRNNo=' . $MyRow['grnbatch'] . '&amp;PONo=' . $MyRow['orderno'] . '">' . $MyRow['grnbatch'] . '</td>
				<td>' . $MyRow['orderno'] . '</td>
				<td>' . $MyRow['suppinv'] . '</td>
			</tr>';

		}
		echo '</table>';

	}

}
include('includes/footer.inc');
?>