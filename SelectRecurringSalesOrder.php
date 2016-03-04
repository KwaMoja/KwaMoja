<?php

include('includes/session.inc');
$Title = _('Search Recurring Sales Orders');
/* Manual links before header.inc */
$ViewTopic = 'SalesOrders';
$BookMark = 'RecurringSalesOrders';

include('includes/header.inc');

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/customer.png" title="' . _('Inventory Items') . '" alt="" />' . ' ' . $Title . '</p>';

echo '<table class="selection">
		<tr>
			<td>' . _('Select recurring order templates for delivery from') . ':</td>
			<td>' . '<select required="required" name="StockLocation">';

$SQL = "SELECT locations.loccode,
				locationname
			FROM locations
			INNER JOIN locationusers
				ON locationusers.loccode=locations.loccode
				AND locationusers.userid='" .  $_SESSION['UserID'] . "'
				AND locationusers.canview=1";

$ResultStkLocs = DB_query($SQL);

while ($MyRow = DB_fetch_array($ResultStkLocs)) {
	if (isset($_POST['StockLocation'])) {
		if ($MyRow['loccode'] == $_POST['StockLocation']) {
			echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	} elseif ($MyRow['loccode'] == $_SESSION['UserStockLocation']) {
		echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	}
}

echo '</select></td>
	</tr>
	</table>';

echo '<br /><div class="centre"><input type="submit" name="SearchRecurringOrders" value="' . _('Search Recurring Orders') . '" /></div>';

if (isset($_POST['SearchRecurringOrders'])) {

	$SQL = "SELECT recurringsalesorders.recurrorderno,
				debtorsmaster.name,
				currencies.decimalplaces AS currdecimalplaces,
				custbranch.brname,
				recurringsalesorders.customerref,
				recurringsalesorders.orddate,
				recurringsalesorders.deliverto,
				recurringsalesorders.lastrecurrence,
				recurringsalesorders.stopdate,
				recurringsalesorders.frequency,
SUM(recurrsalesorderdetails.unitprice*recurrsalesorderdetails.quantity*(1-recurrsalesorderdetails.discountpercent)) AS ordervalue
			FROM recurringsalesorders INNER JOIN recurrsalesorderdetails
			ON recurringsalesorders.recurrorderno = recurrsalesorderdetails.recurrorderno
			INNER JOIN debtorsmaster
			ON recurringsalesorders.debtorno = debtorsmaster.debtorno
			INNER JOIN custbranch
			ON debtorsmaster.debtorno = custbranch.debtorno
			AND recurringsalesorders.branchcode = custbranch.branchcode
			INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			WHERE recurringsalesorders.fromstkloc = '" . $_POST['StockLocation'] . "'
			GROUP BY recurringsalesorders.recurrorderno,
				debtorsmaster.name,
				currencies.decimalplaces,
				custbranch.brname,
				recurringsalesorders.customerref,
				recurringsalesorders.orddate,
				recurringsalesorders.deliverto,
				recurringsalesorders.lastrecurrence,
				recurringsalesorders.stopdate,
				recurringsalesorders.frequency";

	$ErrMsg = _('No recurring orders were returned by the SQL because');
	$SalesOrdersResult = DB_query($SQL, $ErrMsg);

	/*show a table of the orders returned by the SQL */

	echo '<table cellpadding="2" width="90%" class="selection">
			<tr>
				<th>' . _('Modify') . '</th>
				<th>' . _('Customer') . '</th>
				<th>' . _('Branch') . '</th>
				<th>' . _('Cust Order') . ' #</th>
				<th>' . _('Last Recurrence') . '</th>
				<th>' . _('End Date') . '</th>
				<th>' . _('Times p.a.') . '</th>
				<th>' . _('Order Total') . '</th>
			</tr>';

	$k = 0; //row colour counter
	while ($MyRow = DB_fetch_array($SalesOrdersResult)) {


		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			++$k;
		}

		$ModifyPage = $RootPath . '/RecurringSalesOrders.php?ModifyRecurringSalesOrder=' . $MyRow['recurrorderno'];
		$FormatedLastRecurrence = ConvertSQLDate($MyRow['lastrecurrence']);
		$FormatedStopDate = ConvertSQLDate($MyRow['stopdate']);
		$FormatedOrderValue = locale_number_format($MyRow['ordervalue'], $MyRow['currdecimalplaces']);

		printf('<td><a href="%s">%s</a></td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				</tr>', $ModifyPage, $MyRow['recurrorderno'], $MyRow['name'], $MyRow['brname'], $MyRow['customerref'], $FormatedLastRecurrence, $FormatedStopDate, $MyRow['frequency'], $FormatedOrderValue);

		//end of page full new headings if
	}
	//end of while loop

	echo '</table>';
}
echo '</form>';

include('includes/footer.inc');
?>