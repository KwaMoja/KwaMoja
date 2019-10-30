<?php
include ('includes/session.php');

if (isset($_POST['ShowReport'])) {

	$SQL = "SELECT grnno,
					purchorderdetails.orderno,
					grns.supplierid,
					suppliers.suppname,
					grns.itemcode,
					grns.itemdescription,
					qtyrecd,
					quantityinv,
					grns.stdcostunit,
					actprice,
					unitprice,
					suppliers.currcode,
					currencies.rate,
					currencies.decimalplaces as currdecimalplaces,
					stockmaster.decimalplaces as itemdecimalplaces
				FROM grns
				INNER JOIN purchorderdetails
					ON grns.podetailitem = purchorderdetails.podetailitem
				INNER JOIN suppliers
					ON grns.supplierid=suppliers.supplierid
				INNER JOIN currencies
					ON suppliers.currcode=currencies.currabrev
				LEFT JOIN stockmaster
					ON grns.itemcode=stockmaster.stockid
				WHERE qtyrecd-quantityinv>0
					AND grns.supplierid >='" . $_POST['FromCriteria'] . "'
					AND grns.supplierid <='" . $_POST['ToCriteria'] . "'
				ORDER BY supplierid,
					grnno";

	$GRNsResult = DB_query($SQL, '', '', false, false);

	if (DB_error_no() != 0) {
		$Title = _('Outstanding GRN Valuation') . ' - ' . _('Problem Report');
		include ('includes/header.php');
		prnMsg(_('The outstanding GRNs valuation details could not be retrieved by the SQL because') . ' - ' . DB_error_msg(), 'error');
		echo '<a href="', $RootPath, '/index.php">', _('Back to the menu'), '</a>';
		include ('includes/footer.php');
		exit;
	}
	if (DB_num_rows($GRNsResult) == 0) {
		$Title = _('Outstanding GRN Valuation') . ' - ' . _('Problem Report');
		include ('includes/header.php');
		prnMsg(_('No outstanding GRNs valuation details retrieved'), 'warn');
		echo '<a href="', $RootPath, '/index.php">', _('Back to the menu'), '</a>';
		include ('includes/footer.php');
		exit;
	}

	$Title = _('Outstanding GRNs Report');
	include ('includes/header.php');

	echo '<p class="page_title_text"  align="center">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/inventory.png" title="', _('Goods Received but not invoiced Yet'), '" alt="" />', _('Goods Received but not invoiced Yet'), '
		</p>';

	echo '<div class="page_help_text">', _('Shows the list of goods received not yet invoiced, both in supplier currency and home currency. When run for all suppliers, the total in home curency should match the GL Account for Goods received not invoiced.'), '</div>';

	echo '<table>
			<thead>
				<tr>
					<th colspan="14">
						<b>', _('Outstanding GRN report'), '</b>
						<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/printer.png" class="PrintIcon" title="', _('Print'), '" alt="', _('Print'), '" onclick="window.print();" />
					</th>
				</tr>
				<tr>
					<th>', _('Supplier'), '</th>
					<th>', _('Supplier Name'), '</th>
					<th>', _('PO#'), '</th>
					<th>', _('Item Code'), '</th>
					<th>', _('Qty Received'), '</th>
					<th>', _('Qty Invoiced'), '</th>
					<th>', _('Qty Pending'), '</th>
					<th colspan="2">', _('Unit Price'), '</th>
					<th colspan="2">', _('Line Total'), '</th>
					<th colspan="2">', _('Line Total'), '</th>
				</tr>
			</thead>';

	$TotalHomeCurrency = 0;
	while ($GRNs = DB_fetch_array($GRNsResult)) {
		$QtyPending = $GRNs['qtyrecd'] - $GRNs['quantityinv'];
		$TotalHomeCurrency = $TotalHomeCurrency + ($QtyPending * $GRNs['stdcostunit']);
		echo '<tr class="striped_row">
				<td>', $GRNs['supplierid'], '</td>
				<td>', $GRNs['suppname'], '</td>
				<td class="number">', $GRNs['orderno'], '</td>
				<td>', $GRNs['itemcode'], '</td>
				<td class="number">', locale_number_format($GRNs['qtyrecd'], $GRNs['itemdecimalplaces']), '</td>
				<td class="number">', locale_number_format($GRNs['quantityinv'], $GRNs['itemdecimalplaces']), '</td>
				<td class="number">', locale_number_format($QtyPending, $GRNs['itemdecimalplaces']), '</td>
				<td class="number">', locale_number_format($GRNs['unitprice'], $GRNs['currdecimalplaces']), '</td>
				<td>', $GRNs['currcode'], '</td>
				<td class="number">', locale_number_format(($QtyPending * $GRNs['unitprice']), $GRNs['currdecimalplaces']), '</td>
				<td>', $GRNs['currcode'], '</td>
				<td class="number">', locale_number_format(($GRNs['qtyrecd'] - $GRNs['quantityinv']) * $GRNs['stdcostunit'], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td>', $_SESSION['CompanyRecord']['currencydefault'], '</td>
			</tr>';

	}
	echo '<tr class="total_row">
			<td colspan="10"></td>
			<td>', _('Total'), ':</td>
			<td class="number">', locale_number_format($TotalHomeCurrency, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
			<td>', $_SESSION['CompanyRecord']['currencydefault'], '</td>
		</tr>';

	echo '</table>';

	include ('includes/footer.php');

} else {

	$SQL = "SELECT min(supplierid) AS fromcriteria,
					max(supplierid) AS tocriteria
				FROM suppliers";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$Title = _('Outstanding GRNs Report');
	include ('includes/header.php');

	echo '<p class="page_title_text" align="center">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/inventory.png" title="', $Title, '" alt="" />', $Title, '
		</p>';

	echo '<div class="page_help_text">', _('Shows the list of goods received not yet invoiced, both in supplier currency and home currency. When run for all suppliers the total in home curency should match the GL Account for Goods received not invoiced.'), '</div>';

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<fieldset>
			<legend>', _('Report Criteria'), '</legend>
			<field>
				<label for="FromCriteria">', _('From Supplier Code'), ':</label>
				<input type="text" name="FromCriteria" autofocus="autofocus" required="required" maxlength="20" value="', $MyRow['fromcriteria'], '" />
			</field>
			<field>
				<label for="ToCriteria">', _('To Supplier Code'), ':</label>
				<input type="text" name="ToCriteria" required="required" maxlength="20" value="', $MyRow['tocriteria'], '" />
			</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="ShowReport" value="', _('Display'), '" />
		</div>
	</form>';

	include ('includes/footer.php');

}

?>