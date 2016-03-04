<?php

include('includes/session.inc');
include('includes/phplot/phplot.php');
$Title = _('Sales Report Graph');
/* Manual links before header.inc */
$ViewTopic = 'ARInquiries';
$BookMark = 'SalesGraph';
include('includes/header.inc');

$SelectADifferentPeriod = '';

if (isset($_POST['FromPeriod']) and isset($_POST['ToPeriod'])) {

	if ($_POST['FromPeriod'] > $_POST['ToPeriod']) {
		prnMsg(_('The selected period from is actually after the period to! Please re-select the reporting period'), 'error');
		$SelectADifferentPeriod = _('Select A Different Period');
	}
	if ($_POST['ToPeriod'] - $_POST['FromPeriod'] > 12) {
		prnMsg(_('The selected period range is more than 12 months - only graphs for a period less than 12 months can be created'), 'error');
		$SelectADifferentPeriod = _('Select A Different Period');
	}
	if ((!isset($_POST['ValueFrom']) or $_POST['ValueFrom'] = '' or !isset($_POST['ValueTo']) or $_POST['ValueTo'] = '') and $_POST['GraphOn'] != 'All') {
		prnMsg(_('For graphs including either a customer or item range - the range must be specified. Please enter the value from and the value to for the range'), 'error');
		$SelectADifferentPeriod = _('Select A Different Period');
	}
}

if ((!isset($_POST['FromPeriod']) or !isset($_POST['ToPeriod'])) or $SelectADifferentPeriod == _('Select A Different Period')) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/sales.png" title="' . _('Select criteria') . '" alt="' . _('Select criteria') . '" />' . ' ' . $Title . '</p>';

	echo '<table class="selection" summary="' . _('Criteria for the sales graph') . '">
			<tr>
				<td>' . _('Select Period From') . ':</td>
				<td><select name="FromPeriod">';

	if (Date('m') > $_SESSION['YearEnd']) {
		/*Dates in SQL format */
		$DefaultFromDate = Date('Y-m-d', Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y')));
	} else {
		$DefaultFromDate = Date('Y-m-d', Mktime(0, 0, 0, $_SESSION['YearEnd'] + 2, 0, Date('Y') - 1));
	}
	$SQL = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno";
	$Periods = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Periods)) {
		if (isset($_POST['FromPeriod']) and $_POST['FromPeriod'] != '') {
			if ($_POST['FromPeriod'] == $MyRow['periodno']) {
				echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			} else {
				echo '<option value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			}
		} else {
			if ($MyRow['lastdate_in_period'] == $DefaultFromDate) {
				echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			} else {
				echo '<option value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			}
		}
	}

	echo '</select></td></tr>';
	if (!isset($_POST['ToPeriod']) or $_POST['ToPeriod'] == '') {
		$DefaultToPeriod = GetPeriod(DateAdd(ConvertSQLDate($DefaultFromDate), 'm', 11));
	} else {
		$DefaultToPeriod = $_POST['ToPeriod'];
	}

	echo '<tr>
			<td>' . _('Select Period To') . ':</td>
			<td><select name="ToPeriod">';

	$RetResult = DB_data_seek($Periods, 0);

	while ($MyRow = DB_fetch_array($Periods)) {

		if ($MyRow['periodno'] == $DefaultToPeriod) {
			echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
		} else {
			echo '<option value ="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
		}
	}
	echo '</select>
				</td>
			</tr>';

	$AreasResult = DB_query("SELECT areacode, areadescription FROM areas ORDER BY areadescriptions");

	if (!isset($_POST['SalesArea'])) {
		$_POST['SalesArea'] = '';
	}
	echo '<tr>
			<td>' . _('For Sales Area/Region') . ':</td>
			<td><select name="SalesArea">';
	if ($_POST['SalesArea'] == 'All') {
		echo '<option selected="selected" value="All">' . _('All') . '</option>';
	} else {
		echo '<option value="All">' . _('All') . '</option>';
	}
	while ($MyRow = DB_fetch_array($AreasResult)) {
		if ($MyRow['areacode'] == $_POST['SalesArea']) {
			echo '<option selected="selected" value="' . $MyRow['areacode'] . '">' . $MyRow['areadescription'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['areacode'] . '">' . $MyRow['areadescription'] . '</option>';
		}
	}
	echo '</select>
				</td>
			</tr>';

	$CategoriesResult = DB_query("SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription");

	if (!isset($_POST['CategoryID'])) {
		$_POST['CategoryID'] = '';
	}
	echo '<tr>
			<td>' . _('For Stock Category') . ':</td>
			<td><select name="CategoryID">';
	if ($_POST['CategoryID'] == 'All') {
		echo '<option selected="selected" value="All">' . _('All') . '</option>';
	} else {
		echo '<option value="All">' . _('All') . '</option>';
	}
	while ($MyRow = DB_fetch_array($CategoriesResult)) {
		if ($MyRow['categoryid'] == $_POST['CategoryID']) {
			echo '<option selected="selected" value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		}
	}
	echo '</select>
				</td>
			</tr>';

	echo '<tr>
			<td>' . _('For Sales Person') . ':</td>
			<td><select name="SalesmanCode">';

	$SQL = "SELECT salesmancode, salesmanname FROM salesman";
	if ($_SESSION['SalesmanLogin'] != '') {
		$SQL .= " WHERE salesmancode='" . $_SESSION['SalesmanLogin'] . "' ORDER BY salesmanname";
		$_POST['Salesperson'] = $_SESSION['SalesmanLogin'];
	} else if (!isset($_POST['SalesmanCode'])) {
		$SQL .= ' ORDER BY salesmanname';
		$_POST['SalesmanCode'] = 'All';
		echo '<option selected="selected" value="All">' . _('All') . '</option>';
	} else {
		echo '<option value="All">' . _('All') . '</option>';
	}
	$SalesFolkResult = DB_query($SQL);
	while ($MyRow = DB_fetch_array($SalesFolkResult)) {
		if ($MyRow['salesmancode'] == $_POST['SalesmanCode']) {
			echo '<option selected="selected" value="' . $MyRow['salesmancode'] . '">' . $MyRow['salesmanname'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['salesmancode'] . '">' . $MyRow['salesmanname'] . '</option>';
		}
	}
	echo '</select></td>
		</tr>';

	echo '<tr>
			<td>' . _('Graph Type') . '</td>
			<td>
				<select name="GraphType">
					<option value="bars">' . _('Bar Graph') . '</option>
					<option value="stackedbars">' . _('Stacked Bar Graph') . '</option>
					<option value="lines">' . _('Line Graph') . '</option>
					<option value="linepoints">' . _('Line Point Graph') . '</option>
					<option value="area">' . _('Area Graph') . '</option>
					<option value="points">' . _('Points Graph') . '</option>
					<option value="pie">' . _('Pie Graph') . '</option>
					<option value="thinbarline">' . _('Thin Bar Line Graph') . '</option>
					<option value="squared">' . _('Squared Graph') . '</option>
					<option value="stackedarea">' . _('Stacked Area Graph') . '</option>
				</select>
			</td>
		</tr>';

	if (!isset($_POST['ValueFrom'])) {
		$_POST['ValueFrom'] = '';
	}
	if (!isset($_POST['ValueTo'])) {
		$_POST['ValueTo'] = '';
	}
	echo '<tr>
			<td>' . _('Graph On') . ':</td>
			<td>
				<input type="radio" name="GraphOn" value="All" checked="checked" />' . _('All') . '<br />
				<input type="radio" name="GraphOn" value="Customer" />' . _('Customer') . '<br />
				<input type="radio" name="GraphOn" value="StockID" />' . _('Item Code') . '
			</td>
		</tr>';
	echo '<tr>
			<td>' . _('From') . ':<input type="text" name="ValueFrom" value="' . $_POST['ValueFrom'] . '" /></td>
	 		<td>' . _('To') . ':<input type="text" name="ValueTo" value="' . $_POST['ValueTo'] . '" /></td>
	 	</tr>';

	echo '<tr>
			<td>' . _('Graph Value') . ':</td>
			<td>
				<input type="radio" name="GraphValue" value="Net" checked="checked" />' . _('Net Sales Value') . '<br />
				<input type="radio" name="GraphValue" value="GP" />' . _('Gross Profit') . '<br />
				<input type="radio" name="GraphValue" value="Quantity" />' . _('Quantity') . '
			</td>
		</tr>';

	echo '</table>';

	echo '<div class="centre"><input type="submit" name="ShowGraph" value="' . _('Show Sales Graph') . '" /></div>';
	echo '</form>';
	include('includes/footer.inc');
} else {

	$graph = new PHPlot(950, 450);
	$SelectClause = '';
	$WhereClause = '';
	$GraphTitle = '';
	if ($_POST['GraphValue'] == 'Net') {
		$GraphTitle = _('Sales Value');
		$SelectClause = 'amt';
	} elseif ($_POST['GraphValue'] == 'GP') {
		$GraphTitle = _('Gross Profit');
		$SelectClause = '(amt - cost)';
	} else {
		$GraphTitle = _('Unit Sales');
		$SelectClause = 'qty';
	}

	$GraphTitle .= ' ' . _('From Period') . ' ' . $_POST['FromPeriod'] . ' ' . _('to') . ' ' . $_POST['ToPeriod'] . "\n\r";

	if ($_POST['SalesArea'] == 'All') {
		$GraphTitle .= ' ' . _('For All Sales Areas');
	} else {
		$Result = DB_query("SELECT areadescription FROM areas WHERE areacode='" . $_POST['SalesArea'] . "'");
		$MyRow = DB_fetch_row($Result);
		$GraphTitle .= ' ' . _('For') . ' ' . $MyRow[0];
		$WhereClause .= " area='" . $_POST['SalesArea'] . "' AND";
	}
	if ($_POST['CategoryID'] == 'All') {
		$GraphTitle .= ' ' . _('For All Stock Categories');
	} else {
		$Result = DB_query("SELECT categorydescription FROM stockcategory WHERE categoryid='" . $_POST['CategoryID'] . "'");
		$MyRow = DB_fetch_row($Result);
		$GraphTitle .= ' ' . _('For') . ' ' . $MyRow[0];
		$WhereClause .= " stkcategory='" . $_POST['CategoryID'] . "' AND";

	}
	if ($_POST['SalesmanCode'] == 'All') {
		$GraphTitle .= ' ' . _('For All Salespeople');
	} else {
		$Result = DB_query("SELECT salesmanname FROM salesman WHERE salesmancode='" . $_POST['SalesmanCode'] . "'");
		$MyRow = DB_fetch_row($Result);
		$GraphTitle .= ' ' . _('For Salesperson') . ': ' . $MyRow[0];
		$WhereClause .= " salesperson='" . $_POST['SalesmanCode'] . "' AND";

	}
	if ($_POST['GraphOn'] == 'Customer') {
		$GraphTitle .= ' ' . _('For Customers from') . ' ' . $_POST['ValueFrom'] . ' ' . _('to') . ' ' . $_POST['ValueTo'];
		$WhereClause .= "  cust >='" . $_POST['ValueFrom'] . "' AND cust <='" . $_POST['ValueTo'] . "' AND";
	}
	if ($_POST['GraphOn'] == 'StockID') {
		$GraphTitle .= ' ' . _('For Items from') . ' ' . $_POST['ValueFrom'] . ' ' . _('to') . ' ' . $_POST['ValueTo'];
		$WhereClause .= "  stockid >='" . $_POST['ValueFrom'] . "' AND stockid <='" . $_POST['ValueTo'] . "' AND";
	}

	$WhereClause = "WHERE " . $WhereClause . " salesanalysis.periodno>='" . $_POST['FromPeriod'] . "' AND salesanalysis.periodno <= '" . $_POST['ToPeriod'] . "'";

	$SQL = "SELECT salesanalysis.periodno,
				periods.lastdate_in_period,
				SUM(CASE WHEN budgetoractual=1 THEN " . $SelectClause . " ELSE 0 END) AS sales,
				SUM(CASE WHEN  budgetoractual=0 THEN " . $SelectClause . " ELSE 0 END) AS budget
		FROM salesanalysis INNER JOIN periods ON salesanalysis.periodno=periods.periodno " . $WhereClause . "
		GROUP BY salesanalysis.periodno,
			periods.lastdate_in_period
		ORDER BY salesanalysis.periodno";


	$graph->SetTitle($GraphTitle);
	$graph->SetTitleColor('blue');
	$graph->SetOutputFile('companies/' . $_SESSION['DatabaseName'] . '/reports/salesgraph.png');
	$graph->SetXTitle(_('Month'));
	if ($_POST['GraphValue'] == 'Net') {
		$graph->SetYTitle(_('Sales Value'));
	} elseif ($_POST['GraphValue'] == 'GP') {
		$graph->SetYTitle(_('Gross Profit'));
	} else {
		$graph->SetYTitle(_('Quantity'));
	}
	$graph->SetXTickPos('none');
	$graph->SetXTickLabelPos('none');
	$graph->SetXLabelAngle(90);
	$graph->SetBackgroundColor('white');
	$graph->SetTitleColor('blue');
	$graph->SetFileFormat('png');
	$graph->SetPlotType($_POST['GraphType']);
	$graph->SetIsInline('1');
	$graph->SetShading(5);
	$graph->SetDrawYGrid(TRUE);
	$graph->SetDataType('text-data');
	$graph->SetNumberFormat('.', ',');
	$graph->SetPrecisionY($_SESSION['CompanyRecord']['decimalplaces']);

	$SalesResult = DB_query($SQL);
	if (DB_error_no() != 0) {

		prnMsg(_('The sales graph data for the selected criteria could not be retrieved because') . ' - ' . DB_error_msg(), 'error');
		include('includes/footer.inc');
		exit;
	}
	if (DB_num_rows($SalesResult) == 0) {
		prnMsg(_('There is not sales data for the criteria entered to graph'), 'info');
		include('includes/footer.inc');
		exit;
	}

	$GraphArrays = array();
	$i = 0;
	while ($MyRow = DB_fetch_array($SalesResult)) {
		$GraphArray[$i] = array(
			MonthAndYearFromSQLDate($MyRow['lastdate_in_period']),
			$MyRow['sales'],
			$MyRow['budget']
		);
		++$i;
	}

	$graph->SetDataValues($GraphArray);
	$graph->SetDataColors(array(
		'grey',
		'wheat'
	), //Data Colors
		array(
		'black'
	) //Border Colors
		);
	$graph->SetLegend(array(
		_('Actual'),
		_('Budget')
	));

	//Draw it
	$graph->DrawGraph();
	echo '<table class="selection" summary="' . _('Sales Report Graph') . '">
			<tr>
				<th>' . _('Sales Report Graph') . '
					<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/printer.png" class="PrintIcon" title="' . _('Print') . '" alt="' . _('Print') . '" onclick="window.print();" />
				</th>
			</tr>
			<tr>
				<td>
					<img src="companies/' . $_SESSION['DatabaseName'] . '/reports/salesgraph.png" alt="' . _('Sales Report Graph') . '"></img>
				</td>
			</tr>
		</table>';
	include('includes/footer.inc');
}
?>