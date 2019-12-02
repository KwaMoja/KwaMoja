<?php
include ('includes/session.php');
$Title = _('Search Work Orders');
include ('includes/header.php');

echo '<div class="toplink">
		<a href="', $RootPath, '/WorkOrderEntry.php?New=True">', _('New Work Order'), '</a>
	</div>';

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', _('Search'), '" alt="" />', ' ', $Title, '
	</p>';

echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

if (isset($_GET['WO'])) {
	$SelectedWO = $_GET['WO'];
} elseif (isset($_POST['WO'])) {
	$SelectedWO = $_POST['WO'];
} else {
	unset($SelectedWO);
}

if (isset($_GET['SelectedStockItem'])) {
	$SelectedStockItem = $_GET['SelectedStockItem'];
} elseif (isset($_POST['SelectedStockItem'])) {
	$SelectedStockItem = $_POST['SelectedStockItem'];
} else {
	unset($SelectedStockItem);
}

if (isset($_POST['ResetPart'])) {
	unset($SelectedStockItem);
}

if (isset($SelectedWO) and $SelectedWO != '') {
	$SelectedWO = trim($SelectedWO);
	if (!is_numeric($SelectedWO)) {
		prnMsg(_('The work order number entered MUST be numeric'), 'warn');
		unset($SelectedWO);
		include ('includes/footer.php');
		exit;
	} else {
		echo _('Work Order Number') . ' - ' . $SelectedWO;
	}
}

if (isset($_POST['SearchParts'])) {

	if ($_POST['Keywords'] and $_POST['StockCode']) {
		echo _('Stock description keywords have been used in preference to the Stock code extract entered');
	}
	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						SUM(locstock.quantity) AS qoh,
						stockmaster.units
					FROM stockmaster,
						locstock
					WHERE stockmaster.stockid=locstock.stockid
					AND stockmaster.description " . LIKE . " '" . $SearchString . "'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					AND stockmaster.mbflag='M'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						stockmaster.units
					ORDER BY stockmaster.stockid";

	} elseif (isset($_POST['StockCode'])) {
		$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						sum(locstock.quantity) as qoh,
						stockmaster.units
					FROM stockmaster,
						locstock
					WHERE stockmaster.stockid=locstock.stockid
					AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					AND stockmaster.mbflag='M'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						stockmaster.units
					ORDER BY stockmaster.stockid";

	} elseif (!isset($_POST['StockCode']) and !isset($_POST['Keywords'])) {
		$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						sum(locstock.quantity) as qoh,
						stockmaster.units
					FROM stockmaster,
						locstock
					WHERE stockmaster.stockid=locstock.stockid
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					AND stockmaster.mbflag='M'
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.decimalplaces,
						stockmaster.units
					ORDER BY stockmaster.stockid";
	}

	$ErrMsg = _('No items were returned by the SQL because');
	$DbgMsg = _('The SQL used to retrieve the searched parts was');
	$StockItemsResult = DB_query($SQL, $ErrMsg, $DbgMsg);
}

if (isset($_POST['StockID'])) {
	$StockId = trim(mb_strtoupper($_POST['StockID']));
} elseif (isset($_GET['StockID'])) {
	$StockId = trim(mb_strtoupper($_GET['StockID']));
}

if (!isset($StockId)) {

	/* Not appropriate really to restrict search by date since may miss older
	ouststanding orders
	$OrdersAfterDate = Date('d/m/Y',Mktime(0,0,0,Date('m')-2,Date('d'),Date('Y')));
	*/

	if (!isset($SelectedWO) or ($SelectedWO == '')) {
		echo '<fieldset>
				<legend>', _('Selection Criteria'), '</legend>';

		if (isset($SelectedStockItem)) {
			echo '<field>
					<label for="SelectedStockItem">', _('For the item'), ':</label>
					<div class="fieldtext">', $SelectedStockItem, '</div>
					<input type="hidden" name="SelectedStockItem" value="', $SelectedStockItem, '" />
				</field>';
		}
		echo '<field>
				<label for="WO">', _('Work Order number'), '</label>
				<input type="search" autofocus="autofocus" name="WO" maxlength="8" size="9" />
			</field>';

		$SQL = "SELECT locationname,
						locations.loccode
					FROM locations
					INNER JOIN locationusers
						ON locationusers.loccode=locations.loccode
						AND locationusers.userid='" . $_SESSION['UserID'] . "'
						AND locationusers.canview=1
					WHERE locations.usedforwo=1";
		$ResultStkLocs = DB_query($SQL);
		echo '<field>
				<label for="StockLocation">', _('Processing at'), ':</label>
				<select name="StockLocation"> ';
		while ($MyRow = DB_fetch_array($ResultStkLocs)) {
			if (isset($_POST['StockLocation'])) {
				if ($MyRow['loccode'] == $_POST['StockLocation']) {
					echo '<option selected="selected" value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
				} else {
					echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
				}
			} elseif ($MyRow['loccode'] == $_SESSION['UserStockLocation']) {
				echo '<option selected="selected" value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
			} else {
				echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
			}
		}
		echo '</select>
			</field>';

		if (isset($_GET['ClosedOrOpen']) and $_GET['ClosedOrOpen'] == 'Closed_Only') {
			$_POST['ClosedOrOpen'] = 'Closed_Only';
		}
		echo '<field>
				<label>', _('Order Status'), '</label>
				<select name="ClosedOrOpen">';
		if (isset($_POST['ClosedOrOpen']) and $_POST['ClosedOrOpen'] == 'Closed_Only') {
			echo '<option selected="selected" value="Closed_Only">', _('Closed Work Orders Only'), '</option>';
			echo '<option value="Open_Only">', _('Open Work Orders Only'), '</option>';
		} else {
			echo '<option value="Closed_Only">', _('Closed Work Orders Only'), '</option>';
			echo '<option selected="selected" value="Open_Only">', _('Open Work Orders Only'), '</option>';
		}
		echo '</select>
			</field>';

		echo '</fieldset>';

		echo '<div class="centre">
				<input type="submit" name="SearchOrders" value="', _('Search'), '" />
			</div>';

	}

	$SQL = "SELECT categoryid,
			categorydescription
			FROM stockcategory
			ORDER BY categorydescription";

	$Result1 = DB_query($SQL);

	echo '<fieldset>
			<legend>', _('To search for work orders for a specific item use the item selection facilities below'), '</legend>';

	echo '<field>
			<label for="StockCat">', _('Select a stock category'), ':</label>
			<select name="StockCat">';
	while ($MyRow1 = DB_fetch_array($Result1)) {
		echo '<option value="', $MyRow1['categoryid'], '">', $MyRow1['categorydescription'], '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="Keywords">', _('Enter text extract(s) in the description'), ':</label>
			<input type="text" name="Keywords" size="20" maxlength="25" />
		</field>';

	echo '<h1>', _('OR'), '</h1>';

	echo '<field>
			<label  for="StockCode">', _('Enter extract of the Stock Code'), ':</label>
			<input type="text" name="StockCode" size="15" maxlength="18" />
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="SearchParts" value="', _('Search Items Now'), '" />
			<input type="submit" name="ResetPart" value="', _('Show All'), '" />
		</div>';

	if (isset($StockItemsResult)) {

		echo '<br />
			<table cellpadding="2">
				<tr>
					<th>' . _('Code') . '</th>
					<th>' . _('Description') . '</th>
					<th>' . _('On Hand') . '</th>
					<th>' . _('Units') . '</th>
				</tr>';

		while ($MyRow = DB_fetch_array($StockItemsResult)) {

			printf('<tr class="striped_row">
						<td><input type="submit" name="SelectedStockItem" value="%s" /></td>
						<td>%s</td>
						<td class="number">%s</td>
						<td>%s</td>
					</tr>', $MyRow['stockid'], $MyRow['description'], locale_number_format($MyRow['qoh'], $MyRow['decimalplaces']), $MyRow['units']);

		}
		//end of while loop
		echo '</table>';

	}
	//end if stock search results to show
	else {

		if (!isset($_POST['StockLocation'])) {
			$_POST['StockLocation'] = '';
		}

		//figure out the SQL required from the inputs available
		if (isset($_POST['ClosedOrOpen']) and $_POST['ClosedOrOpen'] == 'Open_Only') {
			$ClosedOrOpen = 0;
		} else {
			$ClosedOrOpen = 1;
		}
		if (isset($SelectedWO) and $SelectedWO != '') {
			$SQL = "SELECT workorders.wo,
								woitems.stockid,
								stockmaster.description,
								stockmaster.decimalplaces,
								woitems.qtyreqd,
								woitems.qtyrecd,
								workorders.requiredby,
								workorders.startdate,
								workorders.reference,
								workorders.loccode,
								locations.locationname
						FROM workorders
						INNER JOIN woitems
							ON workorders.wo=woitems.wo
						INNER JOIN locations
							ON workorders.loccode=locations.loccode
						INNER JOIN stockmaster
							ON woitems.stockid=stockmaster.stockid
						INNER JOIN locationusers
							ON locationusers.loccode=workorders.loccode
							AND locationusers.userid='" . $_SESSION['UserID'] . "'
							AND locationusers.canview=1
						WHERE workorders.closed='" . $ClosedOrOpen . "'
							AND workorders.wo='" . $SelectedWO . "'
						ORDER BY workorders.wo,
								woitems.stockid";
		} else {
			/* $DateAfterCriteria = FormatDateforSQL($OrdersAfterDate); */

			if (isset($SelectedStockItem)) {
				$SQL = "SELECT workorders.wo,
									woitems.stockid,
									stockmaster.description,
									stockmaster.decimalplaces,
									woitems.qtyreqd,
									woitems.qtyrecd,
									workorders.requiredby,
									workorders.startdate,
									workorders.reference,
									workorders.loccode,
									locations.locationname
							FROM workorders
							INNER JOIN woitems
								ON workorders.wo=woitems.wo
							INNER JOIN locations
								ON workorders.loccode=locations.loccode
							INNER JOIN stockmaster
								ON woitems.stockid=stockmaster.stockid
							INNER JOIN locationusers
								ON locationusers.loccode=workorders.loccode
								AND locationusers.userid='" . $_SESSION['UserID'] . "'
								AND locationusers.canview=1
							WHERE workorders.closed='" . $ClosedOrOpen . "'
							AND woitems.stockid='" . $SelectedStockItem . "'
							AND workorders.loccode='" . $_POST['StockLocation'] . "'
							ORDER BY workorders.wo,
								 woitems.stockid";
			} else {
				$SQL = "SELECT workorders.wo,
									woitems.stockid,
									stockmaster.description,
									stockmaster.decimalplaces,
									woitems.qtyreqd,
									woitems.qtyrecd,
									workorders.requiredby,
									workorders.startdate,
									workorders.reference,
									workorders.loccode,
									locations.locationname
							FROM workorders
							INNER JOIN woitems
								ON workorders.wo=woitems.wo
							INNER JOIN locations
								ON workorders.loccode=locations.loccode
							INNER JOIN stockmaster
								ON woitems.stockid=stockmaster.stockid
							INNER JOIN locationusers
								ON locationusers.loccode=workorders.loccode
								AND locationusers.userid='" . $_SESSION['UserID'] . "'
								AND locationusers.canview=1
							WHERE workorders.closed='" . $ClosedOrOpen . "'
							AND workorders.loccode='" . $_POST['StockLocation'] . "'
							ORDER BY workorders.wo,
									 woitems.stockid";
			}
		} //end not order number selected
		$ErrMsg = _('No works orders were returned by the SQL because');
		$WorkOrdersResult = DB_query($SQL, $ErrMsg);

		/*show a table of the orders returned by the SQL */
		if (DB_num_rows($WorkOrdersResult) > 0) {
			echo '<table cellpadding="2" width="95%">
					<thead>
						<tr>
							<th>', _('Modify'), '</th>
							<th>', _('Status'), '</th>
							<th>', _('Issue To'), '</th>
							<th>', _('Receive'), '</th>
							<th>', _('Costing'), '</th>
							<th>', _('Paperwork'), '</th>
							<th class="SortedColumn">', _('Location'), '</th>
							<th class="SortedColumn">', _('Item'), '</th>
							<th>', _('Quantity Required'), '</th>
							<th>', _('Quantity Received'), '</th>
							<th>', _('Quantity Outstanding'), '</th>
							<th class="SortedColumn">', _('Start Date'), '</th>
							<th class="SortedColumn">', _('Required Date'), '</th>
						</tr>
					</thead>';

			echo '<tbody>';

			while ($MyRow = DB_fetch_array($WorkOrdersResult)) {

				$ModifyPage = $RootPath . '/WorkOrderEntry.php?WO=' . urlencode($MyRow['wo']);
				$Status_WO = $RootPath . '/WorkOrderStatus.php?WO=' . urlencode($MyRow['wo']) . '&amp;StockID=' . urlencode($MyRow['stockid']);
				$Receive_WO = $RootPath . '/WorkOrderReceive.php?WO=' . urlencode($MyRow['wo']) . '&amp;StockID=' . urlencode($MyRow['stockid']);
				$Issue_WO = $RootPath . '/WorkOrderIssue.php?WO=' . urlencode($MyRow['wo']) . '&amp;StockID=' . urlencode($MyRow['stockid']);
				$Costing_WO = $RootPath . '/WorkOrderCosting.php?WO=' . urlencode($MyRow['wo']);
				$Printing_WO = $RootPath . '/PDFWOPrint.php?WO=' . urlencode($MyRow['wo']) . '&amp;StockID=' . urlencode($MyRow['stockid']);

				$FormatedRequiredByDate = ConvertSQLDate($MyRow['requiredby']);
				$FormatedStartDate = ConvertSQLDate($MyRow['startdate']);

				echo '<tr class="striped_row">
						<td><a href="', $ModifyPage, '">', $MyRow['wo'], ' - ', $MyRow['reference'], '</a></td>
						<td><a href="', $Status_WO, '">' . _('Status') . '</a></td>
						<td><a href="', $Issue_WO, '">' . _('Issue To') . '</a></td>
						<td><a href="', $Receive_WO, '">' . _('Receive') . '</a></td>
						<td><a href="', $Costing_WO, '">' . _('Costing') . '</a></td>
						<td><a href="', $Printing_WO, '">' . _('Print W/O') . '</a></td>
						<td>', $MyRow['loccode'], ' - ', $MyRow['locationname'], '</td>
						<td>', $MyRow['stockid'], ' - ', $MyRow['description'], '</td>
						<td class="number">', locale_number_format($MyRow['qtyreqd'], $MyRow['decimalplaces']), '</td>
						<td class="number">', locale_number_format($MyRow['qtyrecd'], $MyRow['decimalplaces']), '</td>
						<td class="number">', locale_number_format($MyRow['qtyreqd'] - $MyRow['qtyrecd'], $MyRow['decimalplaces']), '</td>
						<td>', $FormatedStartDate, '</td>
						<td>', $FormatedRequiredByDate, '</td>
					</tr>';

			}
			//end of while loop
			echo '</tbody>
			</table>';
		}
	}
	echo '</form>';
}

include ('includes/footer.php');
?>