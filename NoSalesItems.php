<?php

include('includes/session.inc');
$Title = _('No Sales Items Searching');
include('includes/header.inc');
if (!(isset($_POST['Search']))) {
	echo '<div class="centre"><p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . _('No Sales Items') . '" alt="" />' . ' ' . _('No Sales Items') . '</p></div>';
	echo '<div class="page_help_text">' . _('List of items with stock available during the last X days at the selected locations but did not sell any quantity during these X days.') . '<br />' . _('This list gets the no selling items, items at the location just wasting space, or need a price reduction, etc.') . '<br />' . _('Stock available during the last X days means there was a stock movement that produced that item into that location before that day, and no other positive stock movement has been created afterwards.  No sell any quantity means, there is no sales order for that item from that location.') . '</div>';
	echo '<br />';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">';

	//select location
	echo '<tr>
			<td>' . _('Select Location') . '</td>
			<td>:</td>
			<td>
				<select required="required" name="Location">';
	$SQL = "SELECT locations.loccode,
					locationname
				FROM locations
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				ORDER BY locationname";
	echo '<option value="All" selected="selected">' . _('All') . '</option>';
	$locationresult = DB_query($SQL);
	$i = 0;
	while ($MyRow = DB_fetch_array($locationresult)) {
		if (isset($_POST['Location'][$i]) and $MyRow['loccode'] == $_POST['Location'][$i]) {
			echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			++$i;
		} else {
			echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	}
	echo '</select></td>
		</tr>';

	//to view list of customer
	echo '<tr>
			<td width="150">' . _('Select Customer Type') . '</td>
			<td>:</td>
			<td><select name="Customers">';

	$SQL = "SELECT typename,
					typeid
				FROM debtortype";
	$Result = DB_query($SQL);
	echo '<option value="All">' . _('All') . '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="' . $MyRow['typeid'] . '">' . $MyRow['typename'] . '</option>';
	}
	echo '</select></td>
		</tr>';

	// stock category selection
	$SQL = "SELECT categoryid,categorydescription
			FROM stockcategory
			ORDER BY categorydescription";
	$Result1 = DB_query($SQL);
	echo '<tr>
			<td width="150">' . _('In Stock Category') . ' </td>
			<td>:</td>
			<td><select name="StockCat">';
	if (!isset($_POST['StockCat'])) {
		$_POST['StockCat'] = 'All';
	}
	if ($_POST['StockCat'] == 'All') {
		echo '<option selected="selected" value="All">' . _('All') . '</option>';
	} else {
		echo '<option value="All">' . _('All') . '</option>';
	}
	while ($MyRow1 = DB_fetch_array($Result1)) {
		if ($MyRow1['categoryid'] == $_POST['StockCat']) {
			echo '<option selected="selected" value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		} else {
			echo '<option value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		}
	}

	//View number of days
	echo '<tr>
			<td>' . _('Number Of Days') . ' </td>
			<td>:</td>
			<td><input class="number" tabindex="3" type="text" name="NumberOfDays" size="8" required="required" maxlength="8" value="30" /></td>
		 </tr>
	</table>
	<br />
	<div class="centre">
		<input tabindex="5" type="submit" name="Search" value="' . _('Search') . '" />
	</div>
	</form>';
} else {

	// everything below here to view NumberOfNoSalesItems on selected location
	$FromDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']), 'd', -filter_number_format($_POST['NumberOfDays'])));
	if ($_POST['StockCat'] == 'All') {
		$WhereStockCat = "";
	} else {
		$WhereStockCat = " AND stockmaster.categoryid = '" . $_POST['StockCat'] . "'";
	}

	if ($_POST['Location'][0] == 'All') {
		$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
					FROM stockmaster
					INNER JOIN locstock
						ON stockmaster.stockid = locstock.stockid
					INNER JOIN locationusers
						ON locationusers.loccode=locstock.loccode
						AND locationusers.userid='" .  $_SESSION['UserID'] . "'
						AND locationusers.canview=1
				WHERE (locstock.quantity > 0)
					" . $WhereStockCat . "
					AND NOT EXISTS (
							SELECT *
							FROM salesorderdetails
							INNER JOIN salesorders
								ON salesorderdetails.orderno = salesorders.orderno
							INNER JOIN locationusers
								ON locationusers.loccode=salesorders.fromstkloc
								AND locationusers.userid='" .  $_SESSION['UserID'] . "'
								AND locationusers.canview=1
							WHERE 	stockmaster.stockid = salesorderdetails.stkcode
									AND salesorderdetails.actualdispatchdate > '" . $FromDate . "')
					AND NOT EXISTS (
							SELECT *
							FROM stockmoves
							INNER JOIN locationusers
								ON locationusers.loccode=stockmoves.loccode
								AND locationusers.userid='" .  $_SESSION['UserID'] . "'
								AND locationusers.canview=1
							WHERE stockmoves.stockid = stockmaster.stockid
								AND stockmoves.trandate >= '" . $FromDate . "')
					AND EXISTS (
							SELECT *
							FROM stockmoves
							INNER JOIN locationusers
								ON locationusers.loccode=stockmoves.loccode
								AND locationusers.userid='" .  $_SESSION['UserID'] . "'
								AND locationusers.canview=1
							WHERE stockmoves.stockid = stockmaster.stockid
								AND stockmoves.trandate < '" . $FromDate . "'
								AND stockmoves.qty >0)
				GROUP BY stockmaster.stockid
				ORDER BY stockmaster.stockid";
	} else {
		$WhereLocation = '';
		if (sizeof($_POST['Location']) == 1) {
			$WhereLocation = " AND locstock.loccode ='" . $_POST['Location'][0] . "' ";
		} else {
			$WhereLocation = " AND locstock.loccode IN(";
			$commactr = 0;
			foreach ($_POST['Location'] as $Key => $Value) {
				$WhereLocation .= "'" . $Value . "'";
				$commactr++;
				if ($commactr < sizeof($_POST['Location'])) {
					$WhereLocation .= " ";
				} // End of if
			} // End of foreach
			$WhereLocation .= ')';
		}
		$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units,
						locstock.quantity,
						locations.locationname
				FROM stockmaster
				INNER JOIN locstock
					ON stockmaster.stockid = locstock.stockid
				INNER JOIN locations
					ON locstock.loccode = locations.loccode
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				WHERE (locstock.quantity > 0)
						" . $WhereLocation . $WhereStockCat . "
						AND NOT EXISTS (
								SELECT *
								FROM salesorderdetails, salesorders
								WHERE 	stockmaster.stockid = salesorderdetails.stkcode
										AND (salesorders.fromstkloc = locstock.loccode)
										AND (salesorderdetails.orderno = salesorders.orderno)
										AND salesorderdetails.actualdispatchdate > '" . $FromDate . "')
						AND NOT EXISTS (
								SELECT *
								FROM 	stockmoves
								WHERE 	stockmoves.loccode = locstock.loccode
										AND stockmoves.stockid = stockmaster.stockid
										AND stockmoves.trandate >= '" . $FromDate . "')
						AND EXISTS (
								SELECT *
								FROM 	stockmoves
								WHERE 	stockmoves.loccode = locstock.loccode
										AND stockmoves.stockid = stockmaster.stockid
										AND stockmoves.trandate < '" . $FromDate . "'
										AND stockmoves.qty >0)
				ORDER BY stockmaster.stockid";
	}
	$Result = DB_query($SQL);
	echo '<p class="page_title_text"  align="center"><strong>' . _('No Sales Items') . '</strong></p>';
	echo '<form action="PDFNoSalesItems.php"  method="GET">
		<table class="selection">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<tr>
				<th>' . _('No') . '</th>
				<th>' . _('Location') . '</th>
				<th>' . _('Code') . '</th>
				<th>' . _('Description') . '</th>
				<th>' . _('Location QOH') . '</th>
				<th>' . _('Total QOH') . '</th>
				<th>' . _('Units') . '</th>
			</tr>';
	echo '<input type="hidden" value="' . $_POST['Location'] . '" name="Location" />
			<input type="hidden" value="' . filter_number_format($_POST['NumberOfDays']) . '" name="NumberOfDays" />
			<input type="hidden" value="' . $_POST['Customers'] . '" name="Customers" />';
	$k = 0; //row colour counter
	while ($MyRow = DB_fetch_array($Result)) {
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}
		$QOHResult = DB_query("SELECT sum(quantity)
				FROM locstock
				INNER JOIN locationusers
					ON locationusers.loccode=locstock.loccode
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				WHERE stockid = '" . $MyRow['stockid'] . "'" .
				$WhereLocation);
		$QOHRow = DB_fetch_row($QOHResult);
		$QOH = $QOHRow[0];

		$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . urlencode($MyRow['stockid']) . '">' . $MyRow['stockid'] . '</a>';
		if ($_POST['Location'][0] == 'All') {
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					</tr>', $i, 'All', $CodeLink, $MyRow['description'], $QOH, //on hand on ALL locations
				$QOH, // total on hand
				$MyRow['units'] //unit
				);
		} else {
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					</tr>', $i, $MyRow['locationname'], $CodeLink, $MyRow['description'], $MyRow['quantity'], //on hand on location selected only
				$QOH, // total on hand
				$MyRow['units'] //unit
				);
		}
	}
	echo '</table>';
	echo '</form>';
}
include('includes/footer.inc');
?>