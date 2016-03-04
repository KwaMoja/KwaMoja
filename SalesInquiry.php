<?php

// SalesInquiry.php
// Inquiry on Sales Orders - if Date Type is Order Date, salesorderdetails is the main table
// if Date Type is Invoice, stockmoves is the main table

include('includes/session.inc');
$Title = _('Sales Inquiry');
include('includes/header.inc');

// Sets default date range for current month
if (!isset($_POST['FromDate'])) {

	$_POST['FromDate'] = Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, Date('m'), 1, Date('Y')));
} //!isset($_POST['FromDate'])
if (!isset($_POST['ToDate'])) {
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
} //!isset($_POST['ToDate'])

if (isset($_POST['PartNumber'])) {
	$PartNumber = trim(mb_strtoupper($_POST['PartNumber']));
} //isset($_POST['PartNumber'])
elseif (isset($_GET['PartNumber'])) {
	$PartNumber = trim(mb_strtoupper($_GET['PartNumber']));
} //isset($_GET['PartNumber'])

// Part Number operator - either LIKE or =
if (isset($_POST['PartNumberOp'])) {
	$PartNumberOp = $_POST['PartNumberOp'];
} //isset($_POST['PartNumberOp'])
else {
	$PartNumberOp = '=';
}

if (isset($_POST['DebtorNo'])) {
	$DebtorNo = trim(mb_strtoupper($_POST['DebtorNo']));
} //isset($_POST['DebtorNo'])
elseif (isset($_GET['DebtorNo'])) {
	$DebtorNo = trim(mb_strtoupper($_GET['DebtorNo']));
} //isset($_GET['DebtorNo'])
if (isset($_POST['DebtorNoOp'])) {
	$DebtorNoOp = $_POST['DebtorNoOp'];
} //isset($_POST['DebtorNoOp'])
else {
	$DebtorNoOp = '=';
}
if (isset($_POST['DebtorName'])) {
	$DebtorName = trim(mb_strtoupper($_POST['DebtorName']));
} //isset($_POST['DebtorName'])
elseif (isset($_GET['DebtorName'])) {
	$DebtorName = trim(mb_strtoupper($_GET['DebtorName']));
} //isset($_GET['DebtorName'])
if (isset($_POST['DebtorNameOp'])) {
	$DebtorNameOp = $_POST['DebtorNameOp'];
} //isset($_POST['DebtorNameOp'])
else {
	$DebtorNameOp = '=';
}

// Save $_POST['SummaryType'] in $SaveSummaryType because change $_POST['SummaryType'] when
// create $SQL
if (isset($_POST['SummaryType'])) {
	$SaveSummaryType = $_POST['SummaryType'];
} //isset($_POST['SummaryType'])
else {
	$SaveSummaryType = 'name';
}

if (isset($_POST['submit'])) {
	submit($PartNumber, $PartNumberOp, $DebtorNo, $DebtorNoOp, $DebtorName, $DebtorNameOp, $SaveSummaryType, $RootPath, $_SESSION['Theme']);
} //isset($_POST['submit'])
else {
	display();
}


//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($PartNumber, $PartNumberOp, $DebtorNo, $DebtorNoOp, $DebtorName, $DebtorNameOp, $SaveSummaryType, $RootPath, $CurrentTheme) {
	//initialise no input errors
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (!is_date($_POST['FromDate'])) {
		$InputError = 1;
		prnMsg(_('Invalid From Date'), 'error');
	} //!is_date($_POST['FromDate'])
	if (!is_date($_POST['ToDate'])) {
		$InputError = 1;
		prnMsg(_('Invalid To Date'), 'error');
	} //!is_date($_POST['ToDate'])

	if ($_POST['ReportType'] == 'Summary' and $_POST['DateType'] == 'Order' and $_POST['SummaryType'] == 'transno') {
		$InputError = 1;
		prnMsg(_('Cannot summarise by transaction number with a date type of Order Date'), 'error');
		return;
	} //$_POST['ReportType'] == 'Summary' and $_POST['DateType'] == 'Order' and $_POST['SummaryType'] == 'transno'

	if ($_POST['ReportType'] == 'Detail' and $_POST['DateType'] == 'Order' and $_POST['SortBy'] == 'tempstockmoves.transno,salesorderdetails.stkcode') {
		$InputError = 1;
		prnMsg(_('Cannot sort by transaction number with a date type of Order Date'), 'error');
		return;
	} //$_POST['ReportType'] == 'Detail' and $_POST['DateType'] == 'Order' and $_POST['SortBy'] == 'tempstockmoves.transno,salesorderdetails.stkcode'

	// TempStockmoves function creates a temporary table of stockmoves that is used when the DateType
	// is Invoice Date
	if ($_POST['DateType'] == 'Invoice') {
		TempStockmoves();
	} //$_POST['DateType'] == 'Invoice'


	// Add more to WHERE statement, if user entered something for the part number,debtorno, name
	// Variables that end with Op - meaning operator - are either = or LIKE
	$WherePart = ' ';
	if (mb_strlen($PartNumber) > 0 and $PartNumberOp == 'LIKE') {
		$PartNumber = $PartNumber . '%';
	} //mb_strlen($PartNumber) > 0 and $PartNumberOp == 'LIKE'
	else {
		$PartNumberOp = '=';
	}
	if (mb_strlen($PartNumber) > 0) {
		$WherePart = " AND salesorderdetails.stkcode " . $PartNumberOp . " '" . $PartNumber . "'  ";
	} //mb_strlen($PartNumber) > 0

	$WhereDebtorNo = ' ';
	if ($DebtorNoOp == 'LIKE') {
		$DebtorNo = $DebtorNo . '%';
	} //$DebtorNoOp == 'LIKE'
	else {
		$DebtorNoOp = '=';
	}
	if (mb_strlen($DebtorNo) > 0) {
		$WhereDebtorNo = " AND salesorders.debtorno " . $DebtorNoOp . " '" . $DebtorNo . "'  ";
	} //mb_strlen($DebtorNo) > 0
	else {
		$WhereDebtorNo = ' ';
	}

	$WhereDebtorName = ' ';
	if (mb_strlen($DebtorName) > 0 and $DebtorNameOp == 'LIKE') {
		$DebtorName = $DebtorName . '%';
	} //mb_strlen($DebtorName) > 0 and $DebtorNameOp == 'LIKE'
	else {
		$DebtorNameOp = '=';
	}
	if (mb_strlen($DebtorName) > 0) {
		$WhereDebtorName = " AND debtorsmaster.name " . $DebtorNameOp . " '" . $DebtorName . "'  ";
	} //mb_strlen($DebtorName) > 0
	if (mb_strlen($_POST['OrderNo']) > 0) {
		$WhereOrderNo = " AND salesorderdetails.orderno = " . " '" . $_POST['OrderNo'] . "'  ";
	} //mb_strlen($_POST['OrderNo']) > 0
	else {
		$WhereOrderNo = " ";
	}

	$WhereLineStatus = ' ';
	// Had to use IF statement instead of comparing 'linestatus' to $_POST['LineStatus']
	//in WHERE clause because the WHERE clause did not recognize
	// that had used the IF statement to create a field caused linestatus
	if ($_POST['LineStatus'] != 'All') {
		$WhereLineStatus = " AND IF(salesorderdetails.quantity = salesorderdetails.qtyinvoiced or
		  salesorderdetails.completed = 1,'Completed','Open') = '" . $_POST['LineStatus'] . "'";
	} //$_POST['LineStatus'] != 'All'

	// The following is from PDFCustomerList.php and shows how to set up WHERE clause
	// for multiple selections from Areas - decided to just allow selection of one Area at
	// a time, so used simpler code
	$WhereArea = ' ';
	if ($_POST['Area'] != 'All') {
		$WhereArea = " AND custbranch.area = '" . $_POST['Area'] . "'";
	} //$_POST['Area'] != 'All'

	$WhereSalesman = ' ';
	if ($_SESSION['SalesmanLogin'] != '') {

		$WhereSalesman .= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";

	} elseif ($_POST['Salesman'] != 'All') {
		$WhereSalesman = " AND custbranch.salesman = '" . $_POST['Salesman'] . "'";
	} //$_POST['Salesman'] != 'All'

	$WhereCategory = ' ';
	if ($_POST['Category'] != 'All') {
		$WhereCategory = " AND stockmaster.categoryid = '" . $_POST['Category'] . "'";
	} //$_POST['Category'] != 'All'

	// Only used for Invoice Date type where tempstockmoves is the main table
	$WhereType = " AND (tempstockmoves.type='10' OR tempstockmoves.type='11')";
	if ($_POST['InvoiceType'] != 'All') {
		$WhereType = " AND tempstockmoves.type = '" . $_POST['InvoiceType'] . "'";
	} //$_POST['InvoiceType'] != 'All'
	if ($InputError != 1) {
		$FromDate = FormatDateForSQL($_POST['FromDate']);
		$ToDate = FormatDateForSQL($_POST['ToDate']);
		if ($_POST['ReportType'] == 'Detail') {
			if ($_POST['DateType'] == 'Order') {
				$SQL = "SELECT salesorderdetails.orderno,
							   salesorderdetails.stkcode,
							   salesorderdetails.itemdue,
							   salesorders.debtorno,
							   salesorders.orddate,
							   salesorders.branchcode,
							   salesorderdetails.quantity,
							   salesorderdetails.qtyinvoiced,
							   (salesorderdetails.quantity * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent) / currencies.rate) as extprice,
							   (salesorderdetails.quantity * stockmaster.actualcost) as extcost,
							   IF(salesorderdetails.quantity = salesorderdetails.qtyinvoiced or
								  salesorderdetails.completed = 1,'Completed','Open') as linestatus,
							   debtorsmaster.name,
							   custbranch.brname,
							   custbranch.area,
							   custbranch.salesman,
							   stockmaster.decimalplaces,
							   stockmaster.description
							   FROM salesorderdetails
						LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
						LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
						LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
						LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
						LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
						WHERE salesorders.orddate >='" . $FromDate . "'
						 AND salesorders.orddate <='" . $ToDate . "'
						 AND salesorders.quotation = '" . $_POST['OrderType'] . "'" . $WherePart . $WhereOrderNo . $WhereDebtorNo . $WhereDebtorName . $WhereLineStatus . $WhereArea . $WhereSalesman . $WhereCategory . "ORDER BY " . $_POST['SortBy'];
			} //$_POST['DateType'] == 'Order'
			else {
				// Selects by tempstockmoves.trandate not order date
				$SQL = "SELECT salesorderdetails.orderno,
							   salesorderdetails.stkcode,
							   salesorderdetails.itemdue,
							   salesorders.debtorno,
							   salesorders.orddate,
							   salesorders.branchcode,
							   salesorderdetails.quantity,
							   salesorderdetails.qtyinvoiced,
							   (tempstockmoves.qty * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent) * -1 / currencies.rate) as extprice,
							   (tempstockmoves.qty * tempstockmoves.standardcost) * -1 as extcost,
							   IF(salesorderdetails.quantity = salesorderdetails.qtyinvoiced or
								  salesorderdetails.completed = 1,'Completed','Open') as linestatus,
							   debtorsmaster.name,
							   custbranch.brname,
							   custbranch.area,
							   custbranch.salesman,
							   stockmaster.decimalplaces,
							   stockmaster.description,
							   (tempstockmoves.qty * -1) as qty,
							   tempstockmoves.transno,
							   tempstockmoves.trandate,
							   tempstockmoves.type
							   FROM tempstockmoves
						LEFT JOIN salesorderdetails ON tempstockmoves.reference=salesorderdetails.orderno
						LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
						LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
						LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
						LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
						LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
						WHERE tempstockmoves.trandate >='" . $FromDate . "'
						 AND tempstockmoves.trandate <='" . $ToDate . "'
						 AND tempstockmoves.stockid=salesorderdetails.stkcode
						 AND tempstockmoves.hidemovt=0
						 AND salesorders.quotation = '" . $_POST['OrderType'] . "' " . $WherePart . $WhereType . $WhereOrderNo . $WhereDebtorNo . $WhereDebtorName . $WhereLineStatus . $WhereArea . $WhereSalesman . $WhereCategory . "ORDER BY " . $_POST['SortBy'];
			}
		} //$_POST['ReportType'] == 'Detail'
		else {
			// sql for Summary report
			$orderby = $_POST['SummaryType'];
			// The following is because the 'extprice' summary is a special case - with the other
			// summaries, you group and order on the same field; with 'extprice', you are actually
			// grouping on the stkcode and ordering by extprice descending
			if ($_POST['SummaryType'] == 'extprice') {
				$_POST['SummaryType'] = 'stkcode';
				$orderby = 'extprice DESC';
			} //$_POST['SummaryType'] == 'extprice'
			if ($_POST['DateType'] == 'Order') {
				if ($_POST['SummaryType'] == 'extprice' or $_POST['SummaryType'] == 'stkcode') {
					$SQL = "SELECT salesorderdetails.stkcode,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent) / currencies.rate) as extprice,
								   SUM(salesorderdetails.quantity * stockmaster.actualcost) as extcost,
								   stockmaster.description,
								   stockmaster.decimalplaces
								   FROM salesorderdetails
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE salesorders.orddate >='" . $FromDate . "'
							 AND salesorders.orddate <='" . $ToDate . "'
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "' " . $WherePart . $WhereOrderNo . $WhereDebtorNo . $WhereDebtorName . $WhereLineStatus . $WhereArea . $WhereSalesman . $WhereCategory . "GROUP BY " . $_POST['SummaryType'] . ",salesorderdetails.stkcode,
								   stockmaster.description,
								   stockmaster.decimalplaces
								   ORDER BY " . $orderby;
				} //$_POST['SummaryType'] == 'extprice' or $_POST['SummaryType'] == 'stkcode'
				elseif ($_POST['SummaryType'] == 'orderno') {
					$SQL = "SELECT salesorderdetails.orderno,
								   salesorders.debtorno,
								   debtorsmaster.name,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent) / currencies.rate) as extprice,
								   SUM(salesorderdetails.quantity * stockmaster.actualcost) as extcost
								   FROM salesorderdetails
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE salesorders.orddate >='" . $FromDate . "'
							 AND salesorders.orddate <='" . $ToDate . "'
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "' " . $WherePart . $WhereOrderNo . $WhereDebtorNo . $WhereDebtorName . $WhereLineStatus . $WhereArea . $WhereSalesman . $WhereCategory . "GROUP BY " . $_POST['SummaryType'] . ",salesorders.debtorno,
								   debtorsmaster.name
								   ORDER BY " . $orderby;
				} //$_POST['SummaryType'] == 'orderno'
					elseif ($_POST['SummaryType'] == 'debtorno' or $_POST['SummaryType'] == 'name') {
					if ($_POST['SummaryType'] == 'name') {
						$orderby = 'name';
					} //$_POST['SummaryType'] == 'name'
					$SQL = "SELECT debtorsmaster.debtorno,
								   debtorsmaster.name,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent) / currencies.rate) as extprice,
								   SUM(salesorderdetails.quantity * stockmaster.actualcost) as extcost
								   FROM salesorderdetails
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE salesorders.orddate >='" . $FromDate . "'
							 AND salesorders.orddate <='" . $ToDate . "'
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "' " . $WherePart . $WhereOrderNo . $WhereDebtorNo . $WhereDebtorName . $WhereLineStatus . $WhereArea . $WhereSalesman . $WhereCategory . "GROUP BY debtorsmaster.debtorno
							,debtorsmaster.name
							ORDER BY " . $orderby;
				} //$_POST['SummaryType'] == 'debtorno' or $_POST['SummaryType'] == 'name'
					elseif ($_POST['SummaryType'] == 'month') {
					$SQL = "SELECT EXTRACT(YEAR_MONTH from salesorders.orddate) as month,
								   CONCAT(MONTHNAME(salesorders.orddate),' ',YEAR(salesorders.orddate)) as monthname,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent) / currencies.rate) as extprice,
								   SUM(salesorderdetails.quantity * stockmaster.actualcost) as extcost
								   FROM salesorderdetails
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE salesorders.orddate >='" . $FromDate . "'
							 AND salesorders.orddate <='" . $ToDate . "'
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "'" . $WherePart . $WhereOrderNo . $WhereDebtorNo . $WhereDebtorName . $WhereLineStatus . $WhereArea . $WhereSalesman . $WhereCategory . "GROUP BY " . $_POST['SummaryType'] . ",monthname
							ORDER BY " . $orderby;
				} //$_POST['SummaryType'] == 'month'
					elseif ($_POST['SummaryType'] == 'categoryid') {
					$SQL = "SELECT stockmaster.categoryid,
								   stockcategory.categorydescription,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent) / currencies.rate) as extprice,
								   SUM(salesorderdetails.quantity * stockmaster.actualcost) as extcost
								   FROM salesorderdetails
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE salesorders.orddate >='" . $FromDate . "'
							 AND salesorders.orddate <='" . $ToDate . "'
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "'" . $WherePart . $WhereOrderNo . $WhereDebtorNo . $WhereDebtorName . $WhereLineStatus . $WhereArea . $WhereSalesman . $WhereCategory . "GROUP BY " . $_POST['SummaryType'] . ",categorydescription

							ORDER BY " . $orderby;
				} //$_POST['SummaryType'] == 'categoryid'
					elseif ($_POST['SummaryType'] == 'salesman') {
					$SQL = "SELECT custbranch.salesman,
								   salesman.salesmanname,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent) / currencies.rate) as extprice,
								   SUM(salesorderdetails.quantity * stockmaster.actualcost) as extcost
								   FROM salesorderdetails
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE salesorders.orddate >='" . $FromDate . "'
							 AND salesorders.orddate <='" . $ToDate . "'
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "'" . $WherePart . $WhereOrderNo . $WhereDebtorNo . $WhereDebtorName . $WhereLineStatus . $WhereArea . $WhereSalesman . $WhereCategory . "GROUP BY " . $_POST['SummaryType'] . ",salesmanname
							ORDER BY " . $orderby;
				} //$_POST['SummaryType'] == 'salesman'
					elseif ($_POST['SummaryType'] == 'area') {
					$SQL = "SELECT custbranch.area,
								   areas.areadescription,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(salesorderdetails.quantity * salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent) / currencies.rate) as extprice,
								   SUM(salesorderdetails.quantity * stockmaster.actualcost) as extcost
								   FROM salesorderdetails
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE salesorders.orddate >='" . $FromDate . "'
							 AND salesorders.orddate <='" . $ToDate . "'
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "' " . $WherePart . $WhereOrderNo . $WhereDebtorNo . $WhereDebtorName . $WhereLineStatus . $WhereArea . $WhereSalesman . $WhereCategory . "GROUP BY " . $_POST['SummaryType'] . ",areas.areadescription
							ORDER BY " . $orderby;
				} //$_POST['SummaryType'] == 'area'
			} //$_POST['DateType'] == 'Order'
			else {
				// Selects by tempstockmoves.trandate not order date
				if ($_POST['SummaryType'] == 'extprice' or $_POST['SummaryType'] == 'stkcode') {
					$SQL = "SELECT salesorderdetails.stkcode,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price * -1 / currencies.rate) as extprice,
								   SUM(tempstockmoves.qty * tempstockmoves.standardcost) * -1 as extcost,
								   stockmaster.description,
								   SUM(tempstockmoves.qty * -1) as qty
								   FROM tempstockmoves
							LEFT JOIN salesorderdetails ON tempstockmoves.reference=salesorderdetails.orderno
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE tempstockmoves.trandate >='" . $FromDate . "'
							 AND tempstockmoves.trandate <='" . $ToDate . "'
							 AND tempstockmoves.stockid=salesorderdetails.stkcode
							 AND tempstockmoves.hidemovt=0
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "'" . $WherePart . $WhereType . $WhereOrderNo . $WhereDebtorNo . $WhereDebtorName . $WhereLineStatus . $WhereArea . $WhereSalesman . $WhereCategory . "GROUP BY " . $_POST['SummaryType'] . ",stockmaster.description
							ORDER BY " . $orderby;
				} //$_POST['SummaryType'] == 'extprice' or $_POST['SummaryType'] == 'stkcode'
				elseif ($_POST['SummaryType'] == 'orderno') {
					$SQL = "SELECT salesorderdetails.orderno,
								   salesorders.debtorno,
								   debtorsmaster.name,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price * -1 / currencies.rate) as extprice,
								   SUM(tempstockmoves.qty * tempstockmoves.standardcost) * -1 as extcost,
								   SUM(tempstockmoves.qty * -1) as qty
								   FROM tempstockmoves
							LEFT JOIN salesorderdetails ON tempstockmoves.reference=salesorderdetails.orderno
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE tempstockmoves.trandate >='" . $FromDate . "'
							 AND tempstockmoves.trandate <='" . $ToDate . "'
							 AND tempstockmoves.stockid=salesorderdetails.stkcode
							 AND tempstockmoves.hidemovt=0
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "'" . $WherePart . $WhereType . $WhereOrderNo . $WhereDebtorNo . $WhereDebtorName . $WhereLineStatus . $WhereArea . $WhereSalesman . $WhereCategory . "GROUP BY " . $_POST['SummaryType'] . ",salesorders.debtorno,
							  debtorsmaster.name
							ORDER BY " . $orderby;
				} //$_POST['SummaryType'] == 'orderno'
					elseif ($_POST['SummaryType'] == 'debtorno' or $_POST['SummaryType'] == 'name') {
					if ($_POST['SummaryType'] == 'name') {
						$orderby = 'name';
					} //$_POST['SummaryType'] == 'name'
					$SQL = "SELECT debtorsmaster.debtorno,
								   debtorsmaster.name,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price * -1 / currencies.rate) as extprice,
								   SUM(tempstockmoves.qty * tempstockmoves.standardcost) * -1 as extcost,
								   SUM(tempstockmoves.qty * -1) as qty
								   FROM tempstockmoves
							LEFT JOIN salesorderdetails ON tempstockmoves.reference=salesorderdetails.orderno
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE tempstockmoves.trandate >='" . $FromDate . "'
							 AND tempstockmoves.trandate <='" . $ToDate . "'
							 AND tempstockmoves.stockid=salesorderdetails.stkcode
							 AND tempstockmoves.hidemovt=0
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "'" . $WherePart . $WhereType . $WhereOrderNo . $WhereDebtorNo . $WhereDebtorName . $WhereLineStatus . $WhereArea . $WhereSalesman . $WhereCategory . "GROUP BY debtorsmaster.debtorno" . ' ' . ",debtorsmaster.name
							ORDER BY " . $orderby;
				} //$_POST['SummaryType'] == 'debtorno' or $_POST['SummaryType'] == 'name'
					elseif ($_POST['SummaryType'] == 'month') {
					$SQL = "SELECT EXTRACT(YEAR_MONTH from salesorders.orddate) as month,
								   CONCAT(MONTHNAME(salesorders.orddate),' ',YEAR(salesorders.orddate)) as monthname,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price * -1 / currencies.rate) as extprice,
								   SUM(tempstockmoves.qty * tempstockmoves.standardcost) * -1 as extcost,
								   SUM(tempstockmoves.qty * -1) as qty
								   FROM tempstockmoves
							LEFT JOIN salesorderdetails ON tempstockmoves.reference=salesorderdetails.orderno
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE tempstockmoves.trandate >='" . $FromDate . "'
							 AND tempstockmoves.trandate <='" . $ToDate . "'
							 AND tempstockmoves.stockid=salesorderdetails.stkcode
							 AND tempstockmoves.hidemovt=0
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "'" . $WherePart . $WhereType . $WhereOrderNo . $WhereDebtorNo . $WhereDebtorName . $WhereLineStatus . $WhereArea . $WhereSalesman . $WhereCategory . "GROUP BY " . $_POST['SummaryType'] . ",monthname
							ORDER BY " . $orderby;
				} //$_POST['SummaryType'] == 'month'
					elseif ($_POST['SummaryType'] == 'categoryid') {
					$SQL = "SELECT stockmaster.categoryid,
								   stockcategory.categorydescription,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price * -1 / currencies.rate) as extprice,
								   SUM(tempstockmoves.qty * tempstockmoves.standardcost) * -1 as extcost,
								   SUM(tempstockmoves.qty * -1) as qty
								   FROM tempstockmoves
							LEFT JOIN salesorderdetails ON tempstockmoves.reference=salesorderdetails.orderno
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE tempstockmoves.trandate >='" . $FromDate . "'
							 AND tempstockmoves.trandate <='" . $ToDate . "'
							 AND tempstockmoves.stockid=salesorderdetails.stkcode
							 AND tempstockmoves.hidemovt=0
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "'" . $WherePart . $WhereType . $WhereOrderNo . $WhereDebtorNo . $WhereDebtorName . $WhereLineStatus . $WhereArea . $WhereSalesman . $WhereCategory . "GROUP BY " . $_POST['SummaryType'] . ",categorydescription
							ORDER BY " . $orderby;
				} //$_POST['SummaryType'] == 'categoryid'
					elseif ($_POST['SummaryType'] == 'salesman') {
					$SQL = "SELECT custbranch.salesman,
								   salesman.salesmanname,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price * -1 / currencies.rate) as extprice,
								   SUM(tempstockmoves.qty * tempstockmoves.standardcost) * -1 as extcost,
								   SUM(tempstockmoves.qty * -1) as qty
								   FROM tempstockmoves
							LEFT JOIN salesorderdetails ON tempstockmoves.reference=salesorderdetails.orderno
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE tempstockmoves.trandate >='" . $FromDate . "'
							 AND tempstockmoves.trandate <='" . $ToDate . "'
							 AND tempstockmoves.stockid=salesorderdetails.stkcode
							 AND tempstockmoves.hidemovt=0
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "'" . $WherePart . $WhereType . $WhereOrderNo . $WhereDebtorNo . $WhereDebtorName . $WhereLineStatus . $WhereArea . $WhereSalesman . $WhereCategory . "GROUP BY " . $_POST['SummaryType'] . ",salesmanname
							ORDER BY " . $orderby;
				} //$_POST['SummaryType'] == 'salesman'
					elseif ($_POST['SummaryType'] == 'area') {
					$SQL = "SELECT custbranch.area,
								   areas.areadescription,
								   SUM(salesorderdetails.quantity) as quantity,
								   SUM(salesorderdetails.qtyinvoiced) as qtyinvoiced,
								   SUM(tempstockmoves.qty * tempstockmoves.price * -1 / currencies.rate) as extprice,
								   SUM(tempstockmoves.qty * tempstockmoves.standardcost) * -1 as extcost,
								   SUM(tempstockmoves.qty * -1) as qty
								   FROM tempstockmoves
							LEFT JOIN salesorderdetails ON tempstockmoves.reference=salesorderdetails.orderno
							LEFT JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
							LEFT JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
							LEFT JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
							LEFT JOIN stockmaster ON salesorderdetails.stkcode = stockmaster.stockid
							LEFT JOIN stockcategory ON stockcategory.categoryid = stockmaster.categoryid
							LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
							LEFT JOIN areas ON areas.areacode = custbranch.area
							LEFT JOIN currencies ON currencies.currabrev = debtorsmaster.currcode
							WHERE tempstockmoves.trandate >='" . $FromDate . "'
							 AND tempstockmoves.trandate <='" . $ToDate . "'
							 AND tempstockmoves.stockid=salesorderdetails.stkcode
							 AND tempstockmoves.hidemovt=0
							 AND salesorders.quotation = '" . $_POST['OrderType'] . "'" . $WherePart . $WhereType . $WhereOrderNo . $WhereDebtorNo . $WhereDebtorName . $WhereLineStatus . $WhereArea . $WhereSalesman . $WhereCategory . "GROUP BY " . $_POST['SummaryType'] . ",areas.areadescription
							ORDER BY " . $orderby;
				} //$_POST['SummaryType'] == 'area'
			}
		} // End of if ($_POST['ReportType']
		$ErrMsg = _('The SQL to find the parts selected failed with the message');
		$Result = DB_query($SQL, $ErrMsg);
		$ctr = 0;
		$TotalQty = 0;
		$TotalExtCost = 0;
		$TotalExtPrice = 0;
		$TotalInvQty = 0;

		// Create array for summary type to display in header. Access it with $SaveSummaryType
		$Summary_Array['orderno'] = _('Order Number');
		$Summary_Array['stkcode'] = _('Stock Code');
		$Summary_Array['extprice'] = _('Extended Price');
		$Summary_Array['debtorno'] = _('Customer Code');
		$Summary_Array['name'] = _('Customer Name');
		$Summary_Array['month'] = _('Month');
		$Summary_Array['categoryid'] = _('Stock Category');
		$Summary_Array['salesman'] = _('Salesman');
		$Summary_Array['area'] = _('Sales Area');
		$Summary_Array['transno'] = _('Transaction Number');
		// Create array for sort for detail report to display in header
		$Detail_Array['salesorderdetails.orderno'] = _('Order Number');
		$Detail_Array['salesorderdetails.stkcode'] = _('Stock Code');
		$Detail_Array['debtorsmaster.debtorno,salesorderdetails.orderno'] = _('Customer Code');
		$Detail_Array['debtorsmaster.name,debtorsmaster.debtorno,salesorderdetails.orderno'] = _('Customer Name');
		$Detail_Array['tempstockmoves.transno,salesorderdetails.stkcode'] = _('Transaction Number');

		// Display Header info
		echo '<br /><div style="width:20%;margin-left: 3%;border: 1px solid;text-align: left;padding: 1%; border-radius: 4px;">';
		if ($_POST['ReportType'] == 'Summary') {
			$SortBy_Display = $Summary_Array[$SaveSummaryType];
		} //$_POST['ReportType'] == 'Summary'
		else {
			$SortBy_Display = $Detail_Array[$_POST['SortBy']];
		}
		echo '  ' . _('Sales Inquiry') . ' - ' . $_POST['ReportType'] . ' ' . _('By') . ' ' . $SortBy_Display . '<br/>';
		if ($_POST['OrderType'] == '0') {
			echo '  ' . _('Order Type - Sales Orders') . '<br/>';
		} //$_POST['OrderType'] == '0'
		else {
			echo '  ' . _('Order Type - Quotations') . '<br/>';
		}
		echo '  ' . _('Date Type') . ' - ' . $_POST['DateType'] . '<br/>';
		echo '  ' . _('Date Range') . ' - ' . $_POST['FromDate'] . ' ' . _('To') . ' ' . $_POST['ToDate'] . '<br/>';
		if (mb_strlen(trim($PartNumber)) > 0) {
			echo '  ' . _('Stock Code') . ' - ' . $_POST['PartNumberOp'] . ' ' . $_POST['PartNumber'] . '<br/>';
		} //mb_strlen(trim($PartNumber)) > 0
		if (mb_strlen(trim($_POST['DebtorNo'])) > 0) {
			echo '  ' . _('Customer Code') . ' - ' . $_POST['DebtorNoOp'] . ' ' . $_POST['DebtorNo'] . '<br/>';
		} //mb_strlen(trim($_POST['DebtorNo'])) > 0
		if (mb_strlen(trim($_POST['DebtorName'])) > 0) {
			echo '  ' . _('Customer Name') . ' - ' . $_POST['DebtorNameOp'] . ' ' . $_POST['DebtorName'] . '<br/>';
		} //mb_strlen(trim($_POST['DebtorName'])) > 0
		echo '  ' . _('Line Item Status') . '  - ' . $_POST['LineStatus'] . '<br/>';
		echo '  ' . _('Stock Category') . '  - ' . $_POST['Category'] . '<br/>';
		echo '  ' . _('Salesman') . '  - ' . $_POST['Salesman'] . '<br/>';
		echo '  ' . _('Sales Area') . '  - ' . $_POST['Area'] . '<br/>';
		if ($_POST['DateType'] != 'Order') {
			$itype = 'All';
			if ($_POST['InvoiceType'] == '10') {
				$itype = 'Sales Invoice';
			} //$_POST['InvoiceType'] == '10'
			elseif ($_POST['InvoiceType'] == '11') {
				$itype = 'Credit Notes';
			} //$_POST['InvoiceType'] == '11'
			echo '  ' . _('Invoice Type') . '  - ' . $itype . '<br/>';
		} //$_POST['DateType'] != 'Order'
		echo '</div><br />';
		echo '<table class="selection" style="width: 99%">
				<tr>
					<th colspan="15">
						<h3>' . _('Sales Inquiry') . '
							<img src="' . $RootPath . '/css/' . $CurrentTheme . '/images/printer.png" class="PrintIcon" title="' . _('Print') . '" alt="" onclick="window.print();" />
						</h3>
					</th>
				</tr>';
		if ($_POST['ReportType'] == 'Detail') {
			if ($_POST['DateType'] == 'Order') {
				printf('<tr>
							<th> %10s </th>
							<th> %-20s </th>
							<th> %10s </th>
							<th> %-10s </th>
							<th> %-30s </th>
							<th> %-30s </th>
							<th> %12s </th>
							<th> %14s </th>
							<th> %14s </th>
							<th> %14s </th>
							<th> %12s </th>
							<th> %-10s </th>
							<th> %-10s </th>
							<th> %-10s </th>
							<th> %-40s </th>
						</tr>', _('Order No'), _('Stock Code'), _('Order Date'), _('Debtor No'), _('Debtor Name'), _('Branch Name'), _('Order Qty'), _('Extended Cost'), _('Extended Price'), _('Invoiced Qty'), _('Line Status'), _('Item Due'), _('Salesman'), _('Area'), _('Item Description'));
			} //$_POST['DateType'] == 'Order'
			else {
				// Headings for Invoiced Date
				printf('<tr>
							<th> %10s </th>
							<th> %14s </th>
							<th> %-20s </th>
							<th> %10s </th>
							<th> %-10s </th>
							<th> %-30s </th>
							<th> %-30s </th>
							<th> %12s </th>
							<th> %14s </th>
							<th> %14s </th>
							<th> %12s </th>
							<th> %-10s </th>
							<th> %-10s </th>
							<th> %-10s </th>
							<th> %-40s </th>
						</tr>', _('Order No'), _('Trans. No'), _('Stock Code'), _('Order Date'), _('Debtor No'), _('Debtor Name'), _('Branch Name'), _('Invoiced Qty'), _('Extended Cost'), _('Extended Price'), _('Line Status'), _('Invoiced'), _('Salesman'), _('Area'), _('Item Description'));
			}
			$linectr = 0;
			while ($MyRow = DB_fetch_array($Result)) {
				$linectr++;
				if ($_POST['DateType'] == 'Order') {
					printf('<tr>
								<td class="select"> %10s </td>
								<td class="select"> %-20s </td>
								<td class="select"> %10s </td>
								<td class="select"> %-10s </td>
								<td class="select"> %-30s </td>
								<td class="select"> %-30s </td>
								<td class="select number"> %12s </td>
								<td class="select number"> %14s </td>
								<td class="select number"> %14s </td>
								<td class="select number"> %14s </td>
								<td class="select"> %12s </td>
								<td class="select"> %-10s </td>
								<td class="select"> %-10s </td>
								<td class="select"> %-10s </td>
								<td class="select"> %-40s </td<
							/tr>', $MyRow['orderno'], $MyRow['stkcode'], ConvertSQLDate($MyRow['orddate']), $MyRow['debtorno'], $MyRow['name'], $MyRow['brname'], locale_number_format($MyRow['quantity'], $MyRow['decimalplaces']), locale_number_format($MyRow['extcost'], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($MyRow['extprice'], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($MyRow['qtyinvoiced'], $MyRow['decimalplaces']), $MyRow['linestatus'], ConvertSQLDate($MyRow['itemdue']), $MyRow['salesman'], $MyRow['area'], $MyRow['description']);
					print '<br/>';
					$TotalQty += $MyRow['quantity'];
				} //$_POST['DateType'] == 'Order'
				else {
					// Detail for Invoiced Date
					printf('<tr>
								<td class="select"> %10s </td>
								<td class="select"> %14s </td>
								<td class="select"> %-20s </td>
								<td class="select"> %10s </td>
								<td class="select"> %-10s </td>
								<td class="select"> %-30s </td>
								<td class="select"> %-30s </td>
								<td class="select number"> %12s </td>
								<td class="select number"> %14s </td>
								<td class="select number"> %14s </td>
								<td class="select"> %12s </td>
								<td class="select"> %-10s </td>
								<td class="select"> %-10s </td>
								<td class="select"> %-10s </td>
								<td class="select"> %-40s </td>
							</tr>', $MyRow['orderno'], $MyRow['transno'], $MyRow['stkcode'], ConvertSQLDate($MyRow['orddate']), $MyRow['debtorno'], $MyRow['name'], $MyRow['brname'], locale_number_format($MyRow['qty'], $MyRow['decimalplaces']), locale_number_format($MyRow['extcost'], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($MyRow['extprice'], $_SESSION['CompanyRecord']['decimalplaces']), $MyRow['linestatus'], ConvertSQLDate($MyRow['trandate']), $MyRow['salesman'], $MyRow['area'], $MyRow['description']);
					print '<br/>';
					$TotalQty += $MyRow['qty'];
				}
				$lastdecimalplaces = $MyRow['decimalplaces'];
				$TotalExtCost += $MyRow['extcost'];
				$TotalExtPrice += $MyRow['extprice'];
				$TotalInvQty += $MyRow['qtyinvoiced'];
			} //END WHILE LIST LOOP
			// Print totals
			if ($_POST['DateType'] == 'Order') {
				printf('<tr>
								<th> %10s </th>
								<th> %-20s </th>
								<th> %10s </th>
								<th> %-10s </th>
								<th> %-30s </th>
								<th> %-30s </th>
								<th class="number"> %12s </th>
								<th class="number"> %14s </th>
								<th class="number"> %14s </th>
								<th class="number"> %14s </th>
								<th> %12s </th>
								<th> %-10s </th>
								<th> %-40s </th>
							</tr>', _('Totals'), _('Lines - ') . $linectr, ' ', ' ', ' ', ' ', locale_number_format($TotalQty, 2), locale_number_format($TotalExtCost, $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($TotalExtPrice, $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($TotalInvQty, 2), ' ', ' ', ' ');
			} //$_POST['DateType'] == 'Order'
			else {
				// Print totals for Invoiced Date Type - Don't print invoice quantity
				printf('<tr>
								<th> %10s </th>
								<th> %14s </th>
								<th> %-20s </th>
								<th> %10s </th>
								<th> %-10s </th>
								<th> %-30s </th>
								<th> %-30s </th>
								<th class="number"> %12s </th>
								<th class="number"> %14s </th>
								<th class="number"> %14s </th>
								<th> %12s </th>
								<th> %10s </th>
								<th> %-40s </th>
							</tr>', _('Totals'), _('Lines - ') . $linectr, ' ', ' ', ' ', ' ', ' ', locale_number_format($TotalQty, 2), locale_number_format($TotalExtCost, $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($TotalExtPrice, $_SESSION['CompanyRecord']['decimalplaces']), ' ', ' ', ' ');
			}
		} //$_POST['ReportType'] == 'Detail'
		else {
			// Print summary stuff
			$SummaryType = $_POST['SummaryType'];
			$columnheader7 = ' ';
			// Set up description based on the Summary Type
			if ($SummaryType == 'name') {
				$SummaryType = 'name';
				$Description = 'debtorno';
				$SummaryHeader = _('Customer Name');
				$Descriptionheader = _('Customer Code');
			} //$SummaryType == 'name'
			if ($SummaryType == 'stkcode' or $SummaryType == 'extprice') {
				$Description = 'Description';
				$SummaryHeader = _('Stock Code');
				$Descriptionheader = _('Item Description');
			} //$SummaryType == 'stkcode' or $SummaryType == 'extprice'
			if ($SummaryType == 'transno') {
				$Description = 'name';
				$SummaryHeader = _('Transaction Number');
				$Descriptionheader = _('Customer Name');
				$columnheader7 = _('Order Number');
			} //$SummaryType == 'transno'
			if ($SummaryType == 'debtorno') {
				$Description = 'name';
				$SummaryHeader = _('Customer Code');
				$Descriptionheader = _('Customer Name');
			} //$SummaryType == 'debtorno'
			if ($SummaryType == 'orderno') {
				$Description = 'debtorno';
				$SummaryHeader = _('Order Number');
				$Descriptionheader = _('Customer Code');
				$columnheader7 = _('Customer Name');
			} //$SummaryType == 'orderno'
			if ($SummaryType == 'categoryid') {
				$Description = 'categorydescription';
				$SummaryHeader = _('Stock Category');
				$Descriptionheader = _('Category Description');
			} //$SummaryType == 'categoryid'
			if ($SummaryType == 'salesman') {
				$Description = 'salesmanname';
				$SummaryHeader = _('Salesman Code');
				$Descriptionheader = _('Salesman Name');
			} //$SummaryType == 'salesman'
			if ($SummaryType == 'area') {
				$Description = 'areadescription';
				$SummaryHeader = _('Sales Area');
				$Descriptionheader = _('Area Description');
			} //$SummaryType == 'area'
			if ($SummaryType == 'month') {
				$Description = 'monthname';
				$SummaryHeader = _('Month');
				$Descriptionheader = _('Month');
			} //$SummaryType == 'month'
			printf('<tr>
						<th>	%-30s </th>
						<th> %-40s </th>
						<th> %12s </th>
						<th> %14s </th>
						<th> %14s </th>
						<th> %14s </th>
						<th> %-15s </th>
					</tr>', _($SummaryHeader), _($Descriptionheader), _('Quantity'), _('Extended Cost'), _('Extended Price'), _('Invoiced Qty'), _($columnheader7));
			print '<br/><br/>';

			$column7 = ' ';
			$linectr = 0;
			while ($MyRow = DB_fetch_array($Result)) {
				$linectr++;
				if ($SummaryType == 'orderno') {
					$column7 = $MyRow['name'];
				} //$SummaryType == 'orderno'
				if ($SummaryType == 'transno') {
					$column7 = $MyRow['orderno'];
				} //$SummaryType == 'transno'
				if ($_POST['DateType'] == 'Order') {
					// quantity is from salesorderdetails
					$DisplayQty = $MyRow['quantity'];
				} //$_POST['DateType'] == 'Order'
				else {
					// qty is from stockmoves
					$DisplayQty = $MyRow['qty'];
				}
				printf('	%-30s | %-40s | %12s | %14s | %14s | %14s |  %-40s', $MyRow[$SummaryType], $MyRow[$Description], locale_number_format($DisplayQty, 2), locale_number_format($MyRow['extcost'], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($MyRow['extprice'], $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($MyRow['qtyinvoiced'], 2), $column7);

				print '<br/>';
				$TotalQty += $DisplayQty;
				$TotalExtCost += $MyRow['extcost'];
				$TotalExtPrice += $MyRow['extprice'];
				$TotalInvQty += $MyRow['qtyinvoiced'];
			} //END WHILE LIST LOOP
			// Print totals
			printf('	%-30s | %-40s | %12s | %14s | %14s | %14s', _('Totals'), _('Lines - ') . $linectr, locale_number_format($TotalQty, 2), locale_number_format($TotalExtCost, $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($TotalExtPrice, $_SESSION['CompanyRecord']['decimalplaces']), locale_number_format($TotalInvQty, 2), ' ');
			echo '</pre>';
		} // End of if ($_POST['ReportType']

	} // End of if inputerror != 1
	echo '</table>';
} // End of function submit()


function display() //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
	{
	// Display form fields. This function is called the first time
	// the page is called.

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table>';

	echo '<tr>
			<td>' . _('Report Type') . ':</td>
			<td><select required="required" name="ReportType">
				<option selected="selected" value="Detail">' . _('Detail') . '</option>
				<option value="Summary">' . _('Summary') . '</option>
			</select></td>
			<td>&nbsp;</td>
		</tr>';


	echo '<tr>
			<td>' . _('Order Type') . ':</td>
			<td><select required="required" name="OrderType">
				<option selected="selected" value="0">' . _('Sales Order') . '</option>
				<option value="1">' . _('Quotation') . '</option>
			</select></td>
			<td>&nbsp;</td>
		</tr>';

	echo '<tr>
			<td>' . _('Date Type') . ':</td>
			<td><select required="required" name="DateType">
				<option selected="selected" value="Order">' . _('Order Date') . '</option>
				<option value="Invoice">' . _('Invoice Date') . '</option>
			</select></td>
			<td>&nbsp;</td>
		</tr>';

	echo '<tr>
			<td>' . _('Invoice Type') . ':</td>
			<td><select required="required" name="InvoiceType">
				<option selected="selected" value="All">' . _('All') . '</option>
				<option value="10">' . _('Sales Invoice') . '</option>
				<option value="11">' . _('Credit Note') . '</option>
			</select></td>
			<td>&nbsp;</td>
			<td>' . _('Only Applies To Invoice Date Type') . '</td>
		</tr>';

	echo '<tr>
			<td>' . _('Date Range') . ':</td>
			<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" size="10" required="required" maxlength="10" value="' . $_POST['FromDate'] . '" /></td>
			<td>' . _('To') . ':</td>
			<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" size="10" required="required" maxlength="10" value="' . $_POST['ToDate'] . '" /></td>
		</tr>';
	if (!isset($_POST['PartNumber'])) {
		$_POST['PartNumber'] = '';
	} //!isset($_POST['PartNumber'])
	echo '<tr>
			<td>' . _('Stock Code') . ':</td>
			<td><select required="required" name="PartNumberOp">
				<option selected="selected" value="Equals">' . _('Equals') . '</option>
				<option value="LIKE">' . _('Begins With') . '</option>
			</select></td>
			<td>&nbsp;</td>
			<td><input type="text" name="PartNumber" size="20" maxlength="20" value="' . $_POST['PartNumber'] . '" /></td>
		</tr>';
	if (!isset($_POST['DebtorNo'])) {
		$_POST['DebtorNo'] = '';
	} //!isset($_POST['DebtorNo'])
	echo '<tr>
			<td>' . _('Customer Code') . ':</td>
			<td><select required="required" name="DebtorNoOp">
				<option selected="selected" value="Equals">' . _('Equals') . '</option>
				<option value="LIKE">' . _('Begins With') . '</option>
			</select></td>
			<td>&nbsp;</td>
			<td><input type="text" name="DebtorNo" size="10" maxlength="10" value="' . $_POST['DebtorNo'] . '" /></td>
		</tr>';
	if (!isset($_POST['DebtorName'])) {
		$_POST['DebtorName'] = '';
	} //!isset($_POST['DebtorName'])
	echo '<tr>
			<td>' . _('Customer Name') . ':</td>
			<td><select required="required" name="DebtorNameOp">
				<option selected="selected" value="LIKE">' . _('Begins With') . '</option>
				<option value="Equals">' . _('Equals') . '</option>
			</select></td>
			<td>&nbsp;</td>
			<td><input type="text" name="DebtorName" size="30" maxlength="30" value="' . $_POST['DebtorName'] . '" /></td>
		</tr>';
	if (!isset($_POST['OrderNo'])) {
		$_POST['OrderNo'] = '';
	} //!isset($_POST['OrderNo'])
	echo '<tr>
			<td>' . _('Order Number') . ':</td>
			<td>' . _('Equals') . '</td>
			<td>&nbsp;</td>
			<td><input type="text" name="OrderNo" size="10" maxlength="10" value="' . $_POST['OrderNo'] . '" /></td>
		</tr>';

	echo '<tr>
			<td>' . _('Line Item Status') . ':</td>
			<td><select name="LineStatus">
				<option selected="selected" value="All">' . _('All') . '</option>
				<option value="Completed">' . _('Completed') . '</option>
				<option value="Open">' . _('Not Completed') . '</option>
			</select></td>
			<td>&nbsp;</td>
		</tr>';

	echo '<tr>
			<td>' . _('Stock Categories') . ':</td>
			<td><select name="Category">';

	$CategoryResult = DB_query("SELECT categoryid, categorydescription FROM stockcategory");
	echo '<option selected="selected" value="All">' . _('All Categories') . '</option>';
	while ($MyRow = DB_fetch_array($CategoryResult)) {
		echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
	} //$MyRow = DB_fetch_array($CategoryResult)
	echo '</select></td>
		</tr>';

	echo '<tr>
			<td>' . _('For Sales Person') . ':</td>';
	if ($_SESSION['SalesmanLogin'] != '') {
		echo '<td>';
		echo $_SESSION['UsersRealName'];
		echo '</td>';
	} else {
		echo '<td><select name="Salesman">';
		$SQL = "SELECT salesmancode, salesmanname FROM salesman";
		$SalesmanResult = DB_query($SQL);
		echo '<option selected="selected" value="All">' . _('All Salesmen') . '</option>';
		while ($MyRow = DB_fetch_array($SalesmanResult)) {
			echo '<option value="' . $MyRow['salesmancode'] . '">' . $MyRow['salesmanname'] . '</option>';
		}
		echo '</select></td>';
	}
	echo '</tr>';

	// Use name='Areas[]' multiple - if want to create an array for Areas and allow multiple selections
	echo '<tr><td>' . _('For Sales Areas') . ':</td>
				<td><select name="Area">';
	$AreasResult = DB_query("SELECT areacode, areadescription FROM areas");
	echo '<option selected="selected" value="All">' . _('All Areas') . '</option>';
	while ($MyRow = DB_fetch_array($AreasResult)) {
		echo '<option value="' . $MyRow['areacode'] . '">' . $MyRow['areadescription'] . '</option>';
	} //$MyRow = DB_fetch_array($AreasResult)
	echo '</select></td></tr>';

	echo '<tr><td>&nbsp;</td></tr>';
	echo '<tr>
			<td>' . _('Sort By') . ':</td>
			<td><select name="SortBy">
				<option selected="selected" value="salesorderdetails.orderno">' . _('Order Number') . '</option>
				<option value="salesorderdetails.stkcode">' . _('Stock Code') . '</option>
				<option value="debtorsmaster.debtorno,salesorderdetails.orderno">' . _('Customer Code') . '</option>
				<option value="debtorsmaster.name,debtorsmaster.debtorno,salesorderdetails.orderno">' . _('Customer Name') . '</option>
				<option value="tempstockmoves.transno,salesorderdetails.stkcode">' . _('Transaction Number') . '</option>
			</select></td>
			<td>&nbsp;</td>
			<td>' . _('Transaction Number sort only valid for Invoice Date Type') . '</td>
		</tr>';

	echo '<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>';

	echo '<tr><td>&nbsp;</td></tr>';
	echo '<tr><td>' . _('Summary Type') . ':</td>
			<td><select name="SummaryType">
				<option selected="selected" value="orderno">' . _('Order Number') . '</option>
				<option value="transno">' . _('Transaction Number') . '</option>
				<option value="stkcode">' . _('Stock Code') . '</option>
				<option value="extprice">' . _('Extended Price') . '</option>
				<option value="debtorno">' . _('Customer Code') . '</option>
				<option value="name">' . _('Customer Name') . '</option>
				<option value="month">' . _('Month') . '</option>
				<option value="categoryid">' . _('Stock Category') . '</option>
				<option value="salesman">' . _('Salesman') . '</option>
				<option value="area">' . _('Sales Area') . '</option>
			</select></td>
			<td>&nbsp;</td>
			<td>' . _('Transaction Number summary only valid for Invoice Date Type') . '</td>
		</tr>';

	echo '<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . _('Run Inquiry') . '" /></td>
		</tr>
		</table>';
	echo '</form>';

} // End of function display()

function TempStockmoves() {
	// When report based on Invoice Date, use stockmoves as the main file, but credit
	// notes, which are type 11 in stockmoves, do not have the order number in the
	// reference field; instead they have "Ex Inv - " and then the transno from the
	// type 10 stockmoves the credit note was applied to. Use this function to load all
	// type 10 and 11 stockmoves into a temporary table and then update the
	// reference field for type 11 records with the orderno from the type 10 records.

	$FromDate = FormatDateForSQL($_POST['FromDate']);
	$ToDate = FormatDateForSQL($_POST['ToDate']);

	$SQL = "CREATE TEMPORARY TABLE tempstockmoves LIKE stockmoves";
	$ErrMsg = _('The SQL to the create temp stock moves table failed with the message');
	$Result = DB_query($SQL, $ErrMsg);

	$SQL = "INSERT tempstockmoves
			  SELECT * FROM stockmoves
			  WHERE (stockmoves.type='10' OR stockmoves.type='11')
			  AND stockmoves.trandate >='" . $FromDate . "' AND stockmoves.trandate <='" . $ToDate . "'";
	$ErrMsg = _('The SQL to insert temporary stockmoves records failed with the message');
	$Result = DB_query($SQL, $ErrMsg);

	$SQL = "UPDATE tempstockmoves, stockmoves
			  SET tempstockmoves.reference = stockmoves.reference
			  WHERE tempstockmoves.type='11'
				AND SUBSTR(tempstockmoves.reference,10,10) = stockmoves.transno
				AND tempstockmoves.stockid = stockmoves.stockid
				AND stockmoves.type ='10'";
	$ErrMsg = _('The SQL to update tempstockmoves failed with the message');
	$Result = DB_query($SQL, $ErrMsg);


} // End of function TempStockmoves

include('includes/footer.inc');
?>
