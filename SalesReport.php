<?php
// SalesReport.php
// Shows a report of sales to customers for the range of selected dates.
// This program is under the GNU General Public License, last version. 2018-11-10.
// This creative work is under the CC BY-NC-SA, last version. 2018-11-10.
// Notes:
// Coding Conventions/Style: http://www.weberp.org/CodingConventions.html
/*
This script is "mirror-symmetric" to script PurchasesReport.php.
*/

include ('includes/session.php');
$Title = _('Sales to Customers');
$ViewTopic = 'Sales';
$BookMark = 'SalesReport';

// BEGIN Functions division ====================================================
// END Functions division ======================================================
// BEGIN Data division =========================================================
// END Data division ===========================================================
// BEGIN Procedure division ====================================================
include ('includes/header.php');
echo '<p class="page_title_text">
		<img alt="" src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/reports.png" title="', $Title, '" /> ', $Title, '
	</p>'; // Page title.
// Merges gets into posts:
if (isset($_GET['PeriodFrom'])) { // Select period from.
	$_POST['PeriodFrom'] = $_GET['PeriodFrom'];
}
if (isset($_GET['PeriodTo'])) { // Select period to.
	$_POST['PeriodTo'] = $_GET['PeriodTo'];
}
if (isset($_GET['ShowDetails'])) { // Show sales invoices for the period.
	$_POST['ShowDetails'] = $_GET['ShowDetails'];
}

// Validates the data submitted in the form:
if (isset($_POST['PeriodFrom']) and isset($_POST['PeriodTo'])) {
	if (Date1GreaterThanDate2($_POST['PeriodFrom'], $_POST['PeriodTo'])) {
		// The beginning is after the end.
		unset($_POST['PeriodFrom']);
		unset($_POST['PeriodTo']);
		prnMsg(_('The beginning of the period should be before or equal to the end of the period. Please reselect the reporting period.'), 'error');
	}
}

// Main code:
if (!isset($_POST['PeriodFrom']) or !isset($_POST['PeriodTo']) or $_POST['Action'] == 'New') {
	// If one or more parameters are not set or it is a new report, shows a form to allow input of criteria for the report to generate:
	echo '<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />';

	// Input table:
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

	echo '<field>
			<label for="ShowDetails">', _('Show details'), '</label>
			<input', (isset($_POST['ShowDetails']) && $_POST['ShowDetails'] ? ' checked="checked"' : ''), ' id="ShowDetails" name="ShowDetails" type="checkbox">
			<fieldhelp>', _('Check this box to show sales invoices'), '</fieldhelp>
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input name="Action" type="submit" value="', _('Submit'), '" />
		</div>
	</form>';
} else {
	// If all parameters are set and valid, generates the report:
	$TotalGlAmount = 0;
	$TotalGlTax = 0;
	$PeriodFrom = FormatDateForSQL($_POST['PeriodFrom']);
	$PeriodTo = FormatDateForSQL($_POST['PeriodTo']);
	if (isset($_POST['ShowDetails'])) { // Parameters: PeriodFrom, PeriodTo, ShowDetails=on.
		echo '<table class="selection">
				<thead>
					<tr>
						<th colspan="8">', _('Period from'), ': ', $_POST['PeriodFrom'], '<br />', _('Period to'), ': ', $_POST['PeriodTo'], '</th>
						<th>
							<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/printer.png" class="PrintIcon" data-title="', _('Print this report'), '" alt="', _('Print'), '" onclick="window.print();" />
						</th>
					</tr>
					<tr>
						<th>', _('Date'), '</th>
						<th>', _('Sales Invoice'), '</th>
						<th>', _('Reference'), '</th>
						<th>', _('Original Overall Amount'), '</th>
						<th>', _('Original Overall Taxes'), '</th>
						<th>', _('Original Overall Total'), '</th>
						<th>', _('GL Overall Amount'), '</th>
						<th>', _('GL Overall Taxes'), '</th>
						<th>', _('GL Overall Total'), '</th>
					</tr>
				</thead>
			<tbody>';
		// Includes $CurrencyName array with currency three-letter alphabetic code and name based on ISO 4217:
		include ('includes/CurrenciesArray.php');
		$CustomerId = '';
		$CustomerOvAmount = 0;
		$CustomerOvTax = 0;
		$CustomerGlAmount = 0;
		$CustomerGlTax = 0;
		$SQL = "SELECT
					debtortrans.debtorno,
					debtorsmaster.name,
					debtorsmaster.currcode,
					debtortrans.trandate,
					debtortrans.reference,
					debtortrans.transno,
					debtortrans.ovamount,
					debtortrans.ovgst,
					debtortrans.rate
				FROM debtortrans
					INNER JOIN debtorsmaster ON debtortrans.debtorno=debtorsmaster.debtorno
				WHERE debtortrans.trandate>='" . $PeriodFrom . "'
					AND debtortrans.trandate<='" . $PeriodTo . "'
					AND debtortrans.type=10
				ORDER BY debtortrans.debtorno, debtortrans.trandate";
		$Result = DB_query($SQL);
		foreach ($Result as $MyRow) {
			if ($MyRow['debtorno'] != $CustomerId) { // If different, prints customer totals:
				if ($CustomerId != '') { // If NOT the first line.
					echo '<tr class="total_row">
							<td colspan="3">&nbsp;</td>
							<td class="number">', locale_number_format($CustomerOvAmount, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', locale_number_format($CustomerOvTax, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', locale_number_format($CustomerOvAmount + $CustomerOvTax, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', locale_number_format($CustomerGlAmount, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', locale_number_format($CustomerGlTax, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
							<td class="number">', locale_number_format($CustomerGlAmount + $CustomerGlTax, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
						</tr>';
				}
				echo '<tr>
						<td colspan="9">&nbsp;</td>
					</tr>
					<tr class="total_row">
						<td class="text" colspan="9">
							<a href="', $RootPath, '/CustomerInquiry.php?CustomerID=', $MyRow['debtorno'], '">', $MyRow['debtorno'], ' - ', $MyRow['name'], '</a> - ', $MyRow['currcode'], ' ', $CurrencyName[$MyRow['currcode']], '
						</td>
					</tr>';
				$TotalGlAmount+= $CustomerGlAmount;
				$TotalGlTax+= $CustomerGlTax;
				$CustomerId = $MyRow['debtorno'];
				$CustomerOvAmount = 0;
				$CustomerOvTax = 0;
				$CustomerGlAmount = 0;
				$CustomerGlTax = 0;
			}

			$GlAmount = $MyRow['ovamount'] / $MyRow['rate'];
			$GlTax = $MyRow['ovgst'] / $MyRow['rate'];
			echo '<tr class="striped_row">
					<td class="centre">', $MyRow['trandate'], '</td>
					<td class="number">', $MyRow['transno'], '</td>
					<td class="text">', $MyRow['reference'], '</td>
					<td class="number">', locale_number_format($MyRow['ovamount'], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['ovgst'], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
					<td class="number"><a href="', $RootPath, '/CustWhereAlloc.php?TransType=10&TransNo=', $MyRow['transno'], '&amp;ScriptFrom=SalesReport" target="_blank" title="', _('Click to view where allocated'), '">', locale_number_format($MyRow['ovamount'] + $MyRow['ovgst'], $_SESSION['CompanyRecord']['decimalplaces']), '</a></td>
					<td class="number">', locale_number_format($GlAmount, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
					<td class="number">', locale_number_format($GlTax, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
					<td class="number"><a href="', $RootPath, '/GLTransInquiry.php?TypeID=10&amp;TransNo=', $MyRow['transno'], '&amp;ScriptFrom=SalesReport" target="_blank" title="', _('Click to view the GL entries'), '">', locale_number_format($GlAmount + $GlTax, $_SESSION['CompanyRecord']['decimalplaces']), '</a></td>
				</tr>';
			$CustomerOvAmount+= $MyRow['ovamount'];
			$CustomerOvTax+= $MyRow['ovgst'];
			$CustomerGlAmount+= $GlAmount;
			$CustomerGlTax+= $GlTax;
		}

		// Prints last customer total:
		echo '<tr class="total_row">
				<td colspan="3">&nbsp;</td>
				<td class="number">', locale_number_format($CustomerOvAmount, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td class="number">', locale_number_format($CustomerOvTax, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td class="number">', locale_number_format($CustomerOvAmount + $CustomerOvTax, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td class="number">', locale_number_format($CustomerGlAmount, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td class="number">', locale_number_format($CustomerGlTax, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
				<td class="number">', locale_number_format($CustomerGlAmount + $CustomerGlTax, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
			</tr>
			<tr>
				<td colspan="9">&nbsp;</td>
			</tr>';

		$TotalGlAmount+= $CustomerGlAmount;
		$TotalGlTax+= $CustomerGlTax;

	} else { // Parameters: PeriodFrom, PeriodTo, ShowDetails=off.
		// RChacon: Needs to update the table_sort function to use in this table.
		echo '<table class="selection">
				<thead>
					<tr>
						<th colspan="8">', _('Period from'), ': ', $_POST['PeriodFrom'], '<br />', _('Period to'), ': ', $_POST['PeriodTo'], '</th>
						<th>
							<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/printer.png" class="PrintIcon" data-title="', _('Print this report'), '" alt="', _('Print'), '" onclick="window.print();" />
						</th>
					</tr>
					<tr>
						<th>', _('Customer Code'), '</th>
						<th>', _('Customer Name'), '</th>
						<th>', _('Customer Currency'), '</th>
						<th>', _('Original Overall Amount'), '</th>
						<th>', _('Original Overall Taxes'), '</th>
						<th>', _('Original Overall Total'), '</th>
						<th>', _('GL Overall Amount'), '</th>
						<th>', _('GL Overall Taxes'), '</th>
						<th>', _('GL Overall Total'), '</th>
					</tr>
				</thead>';
		$SQL = "SELECT
					debtortrans.debtorno,
					debtorsmaster.name,
					debtorsmaster.currcode,
					SUM(debtortrans.ovamount) AS CustomerOvAmount,
					SUM(debtortrans.ovgst) AS CustomerOvTax,
					SUM(debtortrans.ovamount/debtortrans.rate) AS CustomerGlAmount,
					SUM(debtortrans.ovgst/debtortrans.rate) AS CustomerGlTax
				FROM debtortrans
					INNER JOIN debtorsmaster ON debtortrans.debtorno=debtorsmaster.debtorno
				WHERE debtortrans.trandate>='" . $PeriodFrom . "'
					AND debtortrans.trandate<='" . $PeriodTo . "'
					AND debtortrans.type=10
				GROUP BY
					debtortrans.debtorno
				ORDER BY debtortrans.debtorno, debtortrans.trandate";
		$Result = DB_query($SQL);
		include ('includes/CurrenciesArray.php');
		foreach ($Result as $MyRow) {
			echo '<tr class="striped_row">
					<td class="text">', $MyRow['debtorno'], '</td>
					<td class="text"><a href="', $RootPath, '/CustomerInquiry.php?CustomerID=', $MyRow['debtorno'], '">', $MyRow['name'], '</a></td>
					<td class="text">', $MyRow['currcode'], ' - ', $CurrencyName[$MyRow['currcode']], '</td>
					<td class="number">', locale_number_format($MyRow['CustomerOvAmount'], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['CustomerOvTax'], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['CustomerOvAmount'] + $MyRow['CustomerOvTax'], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['CustomerGlAmount'], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['CustomerGlTax'], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['CustomerGlAmount'] + $MyRow['CustomerGlTax'], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
				</tr>';
			$TotalGlAmount+= $MyRow['CustomerGlAmount'];
			$TotalGlTax+= $MyRow['CustomerGlTax'];
		}
	}
	// Prints all debtors total:
	echo '<tr class="total_row">
			<td class="text" colspan="6">&nbsp;</td>
			<td class="number">', locale_number_format($TotalGlAmount, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
			<td class="number">', locale_number_format($TotalGlTax, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
			<td class="number">', locale_number_format($TotalGlAmount + $TotalGlTax, $_SESSION['CompanyRecord']['decimalplaces']), '</td>
		</tr>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="9"><br /><b>', _('Notes'), '</b><br />', _('Original amounts in the customer\'s currency. GL amounts in the functional currency.'), '</td>
		</tr>
	</tfoot>
</table>';

	echo '<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />';
	echo '<input name="PeriodFrom" type="hidden" value="', $_POST['PeriodFrom'], '" />';
	echo '<input name="PeriodTo" type="hidden" value="', $_POST['PeriodTo'], '" />';
	echo '<input name="ShowDetails" type="hidden" value="', $_POST['ShowDetails'], '" />';

	echo '<div class="centre noprint">
			<a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">', _('New Report'), '</a>
		</div>';
}
echo '</form>';
include ('includes/footer.php');
// END Procedure division ======================================================

?>