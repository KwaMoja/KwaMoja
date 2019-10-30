<?php
// PurchasesReport.php
// Shows a report of purchases from suppliers for the range of selected dates.
// This program is under the GNU General Public License, last version. 2016-12-18.
// This creative work is under the CC BY-NC-SA, last version. 2016-12-18.
// Notes:
// Coding Conventions/Style: http://www.weberp.org/CodingConventions.html
/*
This script is "mirror-symmetric" to script SalesReport.php
*/

include ('includes/session.php');
$Title = _('Purchases from Suppliers');
$ViewTopic = 'PurchaseOrdering';
$BookMark = 'PurchasesReport';

// BEGIN Functions division ====================================================
// END Functions division ======================================================
// BEGIN Data division =========================================================
// END Data division ===========================================================
// BEGIN Procedure division ====================================================
include ('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/reports.png" title="', // Icon image.
$Title, '" /> ', // Icon title.
$Title, '</p>'; // Page title.
// Merges gets into posts:
if (isset($_GET['PeriodFrom'])) { // Select period from.
	$_POST['PeriodFrom'] = $_GET['PeriodFrom'];
}
if (isset($_GET['PeriodTo'])) { // Select period to.
	$_POST['PeriodTo'] = $_GET['PeriodTo'];
}
if (isset($_GET['ShowDetails'])) { // Show purchase invoices for the period.
	$_POST['ShowDetails'] = $_GET['ShowDetails'];
}

// Main code:
if (!isset($_POST['PeriodFrom']) or !isset($_POST['PeriodTo']) or $_POST['Action'] == 'New') {
	// If one or more parameters are not set or it is a new report, shows a form to allow input of criteria for the report to generate:
	echo '<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />';

	echo '<fieldset>
			<legend>', _('Report Parameters'), '</legend>';
	// Content of the body of the input table:
	// Select period from:
	if (!isset($_POST['PeriodFrom'])) {
		$_POST['PeriodFrom'] = date($_SESSION['DefaultDateFormat'], strtotime("-1 year", time())); // One year before current date.
		
	}
	echo '<field>
			<label for="PeriodFrom">', _('Period from'), '</label>
			<input class="date" id="PeriodFrom" maxlength="10" name="PeriodFrom" required="required" size="11" type="text" value="', $_POST['PeriodFrom'], '" />
			<fieldhelp>', _('Select the beginning of the reporting period'), '</fieldhelp>
		</field>';

	// Select period to:
	if (!isset($_POST['PeriodTo'])) {
		$_POST['PeriodTo'] = date($_SESSION['DefaultDateFormat']);
	}
	echo '<field>
			<label for="PeriodTo">', _('Period to'), '</label>
			<input class="date" id="PeriodTo" maxlength="10" name="PeriodTo" required="required" size="11" type="text" value="', $_POST['PeriodTo'], '" />
			<fieldhelp>', _('Select the end of the reporting period'), '</fieldhelp>
		</field>';

	// Show the budget for the period:
	echo '<field>
			<label for="ShowDetails">', _('Show details'), '</label></td>
			<input', (isset($_POST['ShowDetails']) && $_POST['ShowDetails'] ? ' checked="checked"' : ''), ' id="ShowDetails" name="ShowDetails" type="checkbox">
			<fieldhelp>', _('Check this box to show purchase invoices'), '</fieldhelp>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input name="Action" type="submit" value="', _('Submit'), '" />
		</div>';

} else {
	// If all parameters are set and valid, generates the report:
	// Validates the data submitted in the form:
	if (Date1GreaterThanDate2($_POST['PeriodFrom'], $_POST['PeriodTo'])) { // RChacon: Is it the correct way to do this? **********************************************************************************
		// The beginning is after the end.
		unset($_POST['PeriodFrom']);
		unset($_POST['PeriodTo']);
		prnMsg(_('The beginning of the period should be before or equal to the end of the period. Please reselect the reporting period.'), 'error');
	}
	$TotalGlAmount = 0;
	$TotalGlTax = 0;
	$k = 1; // Row colour counter.
	$PeriodFrom = FormatDateForSQL($_POST['PeriodFrom']);
	$PeriodTo = FormatDateForSQL($_POST['PeriodTo']);

	echo '<p></p>';

	if (isset($_POST['ShowDetails'])) { // Parameters: PeriodFrom, PeriodTo, ShowDetails=on.
		echo '<table>
				<thead>
					<tr>
						<th colspan="9">', _('Period from'), ': ', $_POST['PeriodFrom'], '<br />', _('Period to'), ': ', $_POST['PeriodTo'], '
							<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/printer.png" class="PrintIcon" title="', _('Print'), '" alt="', _('Print'), '" onclick="window.print();" />
						</th>
					</tr>
					<tr>
						<th>', _('Date'), '</th>
						<th>', _('Purchase Invoice'), '</th>
						<th>', _('Reference'), '</th>
						<th>', _('Original Overall Amount'), '</th>
						<th>', _('Original Overall Taxes'), '</th>
						<th>', _('Original Overall Total'), '</th>
						<th>', _('GL Overall Amount'), '</th>
						<th>', _('GL Overall Taxes'), '</th>
						<th>', _('GL Overall Total'), '</th>
					</tr>
				</thead>'; // Common table code.
		$SupplierId = '';
		$SupplierOvAmount = 0;
		$SupplierOvTax = 0;
		$SupplierGlAmount = 0;
		$SupplierGlTax = 0;
		$SQL = "SELECT
					supptrans.supplierno,
					suppliers.suppname,
					suppliers.currcode,
					supptrans.trandate,
					supptrans.suppreference,
					supptrans.transno,
					supptrans.ovamount,
					supptrans.ovgst,
					supptrans.rate
				FROM supptrans
					INNER JOIN suppliers ON supptrans.supplierno=suppliers.supplierid
				WHERE supptrans.trandate>='" . $PeriodFrom . "'
					AND supptrans.trandate<='" . $PeriodTo . "'
					AND supptrans.`type`=20
				ORDER BY supptrans.supplierno, supptrans.trandate";
		$Result = DB_query($SQL);
		include ('includes/CurrenciesArray.php'); // To get the currency name from the currency code.
		echo '<tbody>';
		while ($MyRow = DB_fetch_array($Result)) {
			if ($MyRow['supplierno'] != $SupplierId) { // If different, prints supplier totals:
				if ($SupplierId != '') { // If NOT the first line.
					echo '<tr class="total_row">
							<td colspan="3">&nbsp;</td>
							<td class="number">', locale_number_format($SupplierOvAmount, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', locale_number_format($SupplierOvTax, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', locale_number_format($SupplierOvAmount + $SupplierOvTax, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', locale_number_format($SupplierGlAmount, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', locale_number_format($SupplierGlTax, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', locale_number_format($SupplierGlAmount + $SupplierGlTax, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
						</tr>';
				}
				echo '<tr>
						<td colspan="9">&nbsp;</td>
					</tr>';
				echo '<tr class="total_row">
						<td colspan="9">', $MyRow['supplierno'], ' - ', $MyRow['suppname'], ' - ', $MyRow['currcode'], ' ', $CurrencyName[$MyRow['currcode']], '</td>
					</tr>';
				$TotalGlAmount+= $SupplierGlAmount;
				$TotalGlTax+= $SupplierGlTax;
				$SupplierId = $MyRow['supplierno'];
				$SupplierOvAmount = 0;
				$SupplierOvTax = 0;
				$SupplierGlAmount = 0;
				$SupplierGlTax = 0;
			}
			$GlAmount = $MyRow['ovamount'] / $MyRow['rate'];
			$GlTax = $MyRow['ovgst'] / $MyRow['rate'];
			echo '<tr class="striped_row">
					<td class="centre">', $MyRow['trandate'], '</td>', '<td class="number">', $MyRow['transno'], '</td>', '<td class="text">', $MyRow['suppreference'], '</td>', '<td class="number">', locale_number_format($MyRow['ovamount'], $_SESSION['CompanyRecord']['decimalplaces']), '</td>', '<td class="number">', locale_number_format($MyRow['ovgst'], $_SESSION['CompanyRecord']['decimalplaces']), '</td>', '<td class="number"><a href="', $RootPath, '/SuppWhereAlloc.php?TransType=20&TransNo=', $MyRow['transno'], '&amp;ScriptFrom=PurchasesReport" target="_blank" title="', _('Click to view where allocated'), '">', locale_number_format($MyRow['ovamount'] + $MyRow['ovgst'], $_SESSION['CompanyRecord']['decimalplaces']), '</a></td>', '<td class="number">', locale_number_format($GlAmount, $_SESSION['CompanyRecord']['decimalplaces']), '</td>', '<td class="number">', locale_number_format($GlTax, $_SESSION['CompanyRecord']['decimalplaces']), '</td>', '<td class="number"><a href="', $RootPath, '/GLTransInquiry.php?TypeID=20&amp;TransNo=', $MyRow['transno'], '&amp;ScriptFrom=PurchasesReport" target="_blank" title="', _('Click to view the GL entries'), '">', locale_number_format($GlAmount + $GlTax, $_SESSION['CompanyRecord']['decimalplaces']), '</a></td>', // RChacon: Should be "Click to view the General Ledger transaction" instead?
			'</tr>';
			$SupplierOvAmount+= $MyRow['ovamount'];
			$SupplierOvTax+= $MyRow['ovgst'];
			$SupplierGlAmount+= $GlAmount;
			$SupplierGlTax+= $GlTax;
		}

		// Prints last supplier total:
		echo '<tr class="total_row">
				<td colspan="3">&nbsp;</td>
				<td class="number">', locale_number_format($SupplierOvAmount, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td class="number">', locale_number_format($SupplierOvTax, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td class="number">', locale_number_format($SupplierOvAmount + $SupplierOvTax, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td class="number">', locale_number_format($SupplierGlAmount, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td class="number">', locale_number_format($SupplierGlTax, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td class="number">', locale_number_format($SupplierGlAmount + $SupplierGlTax, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
			</tr>
			<tr>
				<td colspan="9">&nbsp;</td>
			</tr>';
		echo '</tbody>';

		$TotalGlAmount+= $SupplierGlAmount;
		$TotalGlTax+= $SupplierGlTax;

	} else { // Parameters: PeriodFrom, PeriodTo, ShowDetails=off.
		// RChacon: Needs to update the table_sort function to use in this table.
		echo '<table>
				<thead>
					<tr>
						<th colspan="9">', _('Period from'), ': ', $_POST['PeriodFrom'], '<br />', _('Period to'), ': ', $_POST['PeriodTo'], '
							<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/printer.png" class="PrintIcon" title="', _('Print'), '" alt="', _('Print'), '" onclick="window.print();" />
						</th>
					</tr>
					<tr>
						<th>', _('Supplier Code'), '</th>
						<th>', _('Supplier Name'), '</th>
						<th>', _('Supplier\'s Currency'), '</th>
						<th>', _('Original Overall Amount'), '</th>
						<th>', _('Original Overall Taxes'), '</th>
						<th>', _('Original Overall Total'), '</th>
						<th>', _('GL Overall Amount'), '</th>
						<th>', _('GL Overall Taxes'), '</th>
						<th>', _('GL Overall Total'), '</th>
					</tr>
				</thead>';
		$SQL = "SELECT
					supptrans.supplierno,
					suppliers.suppname,
					suppliers.currcode,
					SUM(supptrans.ovamount) AS SupplierOvAmount,
					SUM(supptrans.ovgst) AS SupplierOvTax,
					SUM(supptrans.ovamount/supptrans.rate) AS SupplierGlAmount,
					SUM(supptrans.ovgst/supptrans.rate) AS SupplierGlTax
				FROM supptrans
					INNER JOIN suppliers ON supptrans.supplierno=suppliers.supplierid
				WHERE supptrans.trandate>='" . $PeriodFrom . "'
					AND supptrans.trandate<='" . $PeriodTo . "'
					AND supptrans.`type`=20
				GROUP BY
					supptrans.supplierno
				ORDER BY supptrans.supplierno, supptrans.trandate";
		$Result = DB_query($SQL);
		include ('includes/CurrenciesArray.php');
		echo '<tbody>';
		foreach ($Result as $MyRow) {
			echo '<tr class="striped_row">
					<td class="text"><a href="', $RootPath, '/SupplierInquiry.php?SupplierID=', $MyRow['supplierno'], '">', $MyRow['supplierno'], '</a></td>
					<td class="text">', $MyRow['suppname'], '</td>
					<td class="text">', $MyRow['currcode'], ' - ', $CurrencyName[$MyRow['currcode']], '</td>
					<td class="number">', locale_number_format($MyRow['SupplierOvAmount'], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['SupplierOvTax'], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['SupplierOvAmount'] + $MyRow['SupplierOvTax'], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['SupplierGlAmount'], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['SupplierGlTax'], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['SupplierGlAmount'] + $MyRow['SupplierGlTax'], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
				</tr>';
			$TotalGlAmount+= $MyRow['SupplierGlAmount'];
			$TotalGlTax+= $MyRow['SupplierGlTax'];
		}
	}
	echo '<tr class="total_row">
			<td class="text" colspan="6">&nbsp;</td>
			<td class="number">', locale_number_format($TotalGlAmount, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
			<td class="number">', locale_number_format($TotalGlTax, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
			<td class="number">', locale_number_format($TotalGlAmount + $TotalGlTax, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
		</tr>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="9"><br /><b>' . _('Notes') . '</b><br />' . _('Original amounts in the supplier\'s currency. GL amounts in the functional currency.') . '</td>
			</tr>
		</tfoot>
	</table>'; // Prints all suppliers total.
	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />';
	echo '<input name="PeriodFrom" type="hidden" value="', $_POST['PeriodFrom'], '" />';
	echo '<input name="PeriodTo" type="hidden" value="', $_POST['PeriodTo'], '" />';
	echo '<input name="ShowDetails" type="hidden" value="', $_POST['ShowDetails'], '" />';

	echo '<div class="centre noPrint">
			<a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">', _('New Report'), '</a>
		</div>
	</form>';

}
include ('includes/footer.php');
// END: Procedure division -----------------------------------------------------

?>