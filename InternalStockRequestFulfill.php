<?php
include ('includes/session.php');

$Title = _('Fulfil Stock Requests');
$ViewTopic = 'Inventory';
$BookMark = 'FulfilRequest';

include ('includes/header.php');
include ('includes/SQL_CommonFunctions.php');

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/inventory.png" title="', _('Contract'), '" alt="" />', _('Fulfil Stock Requests'), '
	</p>';

if (isset($_POST['UpdateAll'])) {
	foreach ($_POST as $Key => $Value) {
		if (mb_strpos($Key, 'Qty')) {
			$RequestID = mb_substr($Key, 0, mb_strpos($Key, 'Qty'));
			$LineID = mb_substr($Key, mb_strpos($Key, 'Qty') + 3);
			$Quantity = filter_number_format($_POST[$RequestID . 'Qty' . $LineID]);
			$StockId = $_POST[$RequestID . 'StockID' . $LineID];
			$Location = $_POST[$RequestID . 'Location' . $LineID];
			$Department = $_POST[$RequestID . 'Department' . $LineID];
			$Tags = $_POST[$RequestID . 'Tag' . $LineID];
			if (count($Tags) == 0) {
				$Tags = array(0);
			}
			$RequestedQuantity = filter_number_format($_POST[$RequestID . 'RequestedQuantity' . $LineID]);
			if (isset($_POST[$RequestID . 'Completed' . $LineID])) {
				$Completed = True;
			} else {
				$Completed = False;
			}

			$SQL = "SELECT stockcosts.materialcost,
							stockcosts.labourcost,
							stockcosts.overheadcost,
							stockmaster.decimalplaces
						FROM stockmaster
						LEFT JOIN stockcosts
							ON stockcosts.stockid=stockmaster.stockid
							AND stockcosts.succeeded=0
						WHERE stockcosts.stockid='" . $StockId . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_array($Result);
			$StandardCost = $MyRow['materialcost'] + $MyRow['labourcost'] + $MyRow['overheadcost'];
			$DecimalPlaces = $MyRow['decimalplaces'];

			$Narrative = _('Issue') . ' ' . $Quantity . ' ' . _('of') . ' ' . $StockId . ' ' . _('to department') . ' ' . $Department . ' ' . _('from') . ' ' . $Location;

			$AdjustmentNumber = GetNextTransNo(17);
			$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']));
			$SQLAdjustmentDate = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));

			$Result = DB_Txn_Begin();

			// Need to get the current location quantity will need it later for the stock movement
			$SQL = "SELECT locstock.quantity
					FROM locstock
					WHERE locstock.stockid='" . $StockId . "'
						AND loccode= '" . $Location . "'";
			$Result = DB_query($SQL);
			if (DB_num_rows($Result) == 1) {
				$LocQtyRow = DB_fetch_row($Result);
				$QtyOnHandPrior = $LocQtyRow[0];
			} else {
				// There must actually be some error this should never happen
				$QtyOnHandPrior = 0;
			}

			if ($_SESSION['ProhibitNegativeStock'] == 0 or ($_SESSION['ProhibitNegativeStock'] == 1 and $QtyOnHandPrior >= $Quantity)) {

				$SQL = "INSERT INTO stockmoves (
									stockid,
									type,
									transno,
									loccode,
									trandate,
									userid,
									prd,
									reference,
									qty,
									newqoh)
								VALUES (
									'" . $StockId . "',
									17,
									'" . $AdjustmentNumber . "',
									'" . $Location . "',
									'" . $SQLAdjustmentDate . "',
									'" . $_SESSION['UserID'] . "',
									'" . $PeriodNo . "',
									'" . $Narrative . "',
									'" . -$Quantity . "',
									'" . ($QtyOnHandPrior - $Quantity) . "'
								)";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record cannot be inserted because');
				$DbgMsg = _('The following SQL to insert the stock movement record was used');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

				/*Get the ID of the StockMove... */
				$StkMoveNo = DB_Last_Insert_ID('stockmoves', 'stkmoveno');

				$SQL = "UPDATE stockrequestitems
						SET qtydelivered=qtydelivered+" . $Quantity . "
						WHERE dispatchid='" . $RequestID . "'
							AND dispatchitemsid='" . $LineID . "'";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg = _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

				$SQL = "UPDATE locstock SET quantity = quantity - '" . $Quantity . "'
									WHERE stockid='" . $StockId . "'
										AND loccode='" . $Location . "'";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg = _('The following SQL to update the stock record was used');

				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

				if ($_SESSION['CompanyRecord']['gllink_stock'] == 1 and $StandardCost > 0) {

					$StockGLCodes = GetStockGLCode($StockId);

					$SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												amount,
												narrative)
											VALUES (17,
												'" . $AdjustmentNumber . "',
												'" . $SQLAdjustmentDate . "',
												'" . $PeriodNo . "',
												'" . $StockGLCodes['issueglact'] . "',
												'" . $StandardCost * ($Quantity) . "',
												'" . $Narrative . "'
											)";
					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction entries could not be added because');
					$DbgMsg = _('The following SQL to insert the GL entries was used');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
					foreach ($Tags as $Tag) {
						$SQL = "INSERT INTO gltags VALUES ( LAST_INSERT_ID(),
															'" . $Tag . "')";
						$ErrMsg = _('Cannot insert a GL tag for the journal line because');
						$DbgMsg = _('The SQL that failed to insert the GL tag record was');
						$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
					}

					$SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												amount,
												narrative)
											VALUES (17,
												'" . $AdjustmentNumber . "',
												'" . $SQLAdjustmentDate . "',
												'" . $PeriodNo . "',
												'" . $StockGLCodes['stockact'] . "',
												'" . $StandardCost * -$Quantity . "',
												'" . $Narrative . "'
											)";

					$Errmsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction entries could not be added because');
					$DbgMsg = _('The following SQL to insert the GL entries was used');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
					foreach ($Tags as $Tag) {
						$SQL = "INSERT INTO gltags VALUES ( LAST_INSERT_ID(),
															'" . $Tag . "')";
						$ErrMsg = _('Cannot insert a GL tag for the journal line because');
						$DbgMsg = _('The SQL that failed to insert the GL tag record was');
						$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
					}
				}

				if (($Quantity >= $RequestedQuantity) or $Completed == True) {
					$SQL = "UPDATE stockrequestitems
								SET completed=1
							WHERE dispatchid='" . $RequestID . "'
								AND dispatchitemsid='" . $LineID . "'";
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				}

				$Result = DB_Txn_Commit();

				$ConfirmationText = _('An internal stock request for') . ' ' . $StockId . ' ' . _('has been fulfilled from location') . ' ' . $Location . ' ' . _('for a quantity of') . ' ' . locale_number_format($Quantity, $DecimalPlaces);
				prnMsg($ConfirmationText, 'success');

				if ($_SESSION['InventoryManagerEmail'] != '') {
					$ConfirmationText = $ConfirmationText . ' ' . _('by user') . ' ' . $_SESSION['UserID'] . ' ' . _('at') . ' ' . Date('Y-m-d H:i:s');
					$EmailSubject = _('Internal Stock Request Fulfillment for') . ' ' . $StockId;
					if ($_SESSION['SmtpSetting'] == 0) {
						mail($_SESSION['InventoryManagerEmail'], $EmailSubject, $ConfirmationText);
					} else {
						include ('includes/htmlMimeMail.php');
						$Mail = new htmlMimeMail();
						$Mail->setSubject($EmailSubject);
						$Mail->setText($ConfirmationText);
						$Result = SendmailBySmtp($Mail, array($_SESSION['InventoryManagerEmail']));
					}

				}
			} else {
				$ConfirmationText = _('An internal stock request for') . ' ' . $StockId . ' ' . _('has been fulfilled from location') . ' ' . $Location . ' ' . _('for a quantity of') . ' ' . locale_number_format($Quantity, $DecimalPlaces) . ' ' . _('cannot be created as there is insufficient stock and your system is configured to not allow negative stocks');
				prnMsg($ConfirmationText, 'warn');
			}

			// Check if request can be closed and close if done.
			if (isset($RequestID)) {
				$SQL = "SELECT dispatchid
						FROM stockrequestitems
						WHERE dispatchid='" . $RequestID . "'
							AND completed=0";
				$Result = DB_query($SQL);
				if (DB_num_rows($Result) == 0) {
					$SQL = "UPDATE stockrequest
						SET closed=1
					WHERE dispatchid='" . $RequestID . "'";
					$Result = DB_query($SQL);
				}
			}
		}
	}
}

if (!isset($_POST['Location'])) {
	echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<fieldset>
			<legend>', _('Location Selection'), '</legend>
			<field>
				<label for="Location">', _('Choose a location to issue requests from'), '</label>
				<select required="required" name="Location">
					<option value="">', _('Select a Location'), '</option>';
	$SQL = "SELECT locationname,
					locations.loccode
				FROM locations
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" . $_SESSION['UserID'] . "'
					AND locationusers.canupd=1
				WHERE internalrequest = 1
				ORDER BY locationname";
	$ResultStkLocs = DB_query($SQL);
	while ($MyRow = DB_fetch_array($ResultStkLocs)) {
		if (isset($_SESSION['Adjustment']->StockLocation)) {
			if ($MyRow['loccode'] == $_SESSION['Adjustment']->StockLocation) {
				echo '<option selected="selected" value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
			} else {
				echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
			}
		} else {
			echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
		}
	}
	echo '</select>
		</field>';
	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="EnterAdjustment" value="', _('Show Requests'), '" />
		</div>';

	echo '</form>';
	include ('includes/footer.php');
	exit;
}

/* Retrieve the requisition header information
*/
if (isset($_POST['Location'])) {
	$SQL = "SELECT stockrequest.dispatchid,
			locations.locationname,
			stockrequest.despatchdate,
			stockrequest.narrative,
			departments.description,
			www_users.realname,
			www_users.email
		FROM stockrequest
		LEFT JOIN departments
			ON stockrequest.departmentid=departments.departmentid
		LEFT JOIN locations
			ON stockrequest.loccode=locations.loccode
		LEFT JOIN www_users
			ON www_users.userid=departments.authoriser
		WHERE stockrequest.authorised=1
			AND stockrequest.closed=0
			AND stockrequest.loccode='" . $_POST['Location'] . "'";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) == 0) {
		prnMsg(_('There are no outstanding authorised requests for this location'), 'info');
		echo '<br />';
		echo '<div class="centre"><a href="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '">' . _('Select another location') . '</a></div>';
		include ('includes/footer.php');
		exit;
	}

	echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<table>
			<thead>
				<tr>
					<th>', _('Request Number'), '</th>
					<th>', _('Department'), '</th>
					<th>', _('Location Of Stock'), '</th>
					<th>', _('Requested Date'), '</th>
					<th>', _('Narrative'), '</th>
				</tr>
			</thead>
			<tbody>';

	while ($MyRow = DB_fetch_array($Result)) {

		echo '<tr>
				<td>', $MyRow['dispatchid'], '</td>
				<td>', $MyRow['description'], '</td>
				<td>', $MyRow['locationname'], '</td>
				<td>', ConvertSQLDate($MyRow['despatchdate']), '</td>
				<td>', $MyRow['narrative'], '</td>
			</tr>';
		$LineSQL = "SELECT stockrequestitems.dispatchitemsid,
						stockrequestitems.dispatchid,
						stockrequestitems.stockid,
						stockrequestitems.decimalplaces,
						stockrequestitems.uom,
						stockmaster.description,
						stockrequestitems.quantity,
						stockrequestitems.qtydelivered
				FROM stockrequestitems
				LEFT JOIN stockmaster
				ON stockmaster.stockid=stockrequestitems.stockid
			WHERE dispatchid='" . $MyRow['dispatchid'] . "'
				AND completed=0";
		$LineResult = DB_query($LineSQL);

		echo '<tr>
				<td></td>
				<td colspan="5" align="left">
					<table align="left">
					<tr>
						<th>', _('Product'), '</th>
						<th>', _('Quantity'), '<br />', _('Required'), '</th>
						<th>', _('Quantity'), '<br />', _('Delivered'), '</th>
						<th>', _('Units'), '</th>
						<th>', _('Completed'), '</th>
						<th>', _('Tag'), '</th>
					</tr>';

		while ($LineRow = DB_fetch_array($LineResult)) {
			echo '<tr>
					<td valign="top">', $LineRow['description'], '</td>
					<td valign="top" class="number">', locale_number_format($LineRow['quantity'] - $LineRow['qtydelivered'], $LineRow['decimalplaces']), '</td>
					<td valign="top" class="number"><input type="text" class="number" name="', $LineRow['dispatchid'], 'Qty', $LineRow['dispatchitemsid'], '" value="', locale_number_format($LineRow['quantity'] - $LineRow['qtydelivered'], $LineRow['decimalplaces']), '" /></td>
					<td valign="top">', $LineRow['uom'], '</td>
					<td valign="top"><input type="checkbox" name="', $LineRow['dispatchid'], 'Completed', $LineRow['dispatchitemsid'], '" /></td>
					<td valign="top"><select multiple="multiple" name="', $LineRow['dispatchid'], 'Tag', $LineRow['dispatchitemsid'], '[]">';

			$SQL = "SELECT tagref,
							tagdescription
						FROM tags
						ORDER BY tagref";

			$TagResult = DB_query($SQL);
			echo '<option value=0>0 - None</option>';
			while ($mytagrow = DB_fetch_array($TagResult)) {
				if (isset($_SESSION['Adjustment']->tag) and $_SESSION['Adjustment']->tag == $mytagrow['tagref']) {
					echo '<option selected="selected" value="', $mytagrow['tagref'], '">', $mytagrow['tagref'], ' - ', $MyRow['tagdescription'], '</option>';
				} else {
					echo '<option value="', $mytagrow['tagref'], '">', $mytagrow['tagref'], ' - ', $mytagrow['tagdescription'], '</option>';
				}
			}
			echo '</select></td>';
			// End select tag
			echo '</tr>';
			echo '<input type="hidden" class="number" name="', $LineRow['dispatchid'], 'StockID', $LineRow['dispatchitemsid'], '" value="', $LineRow['stockid'], '" />';
			echo '<input type="hidden" class="number" name="', $LineRow['dispatchid'], 'Location', $LineRow['dispatchitemsid'], '" value="', $_POST['Location'], '" />';
			echo '<input type="hidden" class="number" name="', $LineRow['dispatchid'], 'RequestedQuantity', $LineRow['dispatchitemsid'], '" value="', locale_number_format($LineRow['quantity'] - $LineRow['qtydelivered'], $LineRow['decimalplaces']), '" />';
			echo '<input type="hidden" class="number" name="', $LineRow['dispatchid'], 'Department', $LineRow['dispatchitemsid'], '" value="', $MyRow['description'], '" />';
		} // end while order line detail
		echo '</table></td></tr>';
	} //end while header loop
	echo '</tbody>
		</table>';
	echo '<div class="centre">
			<input type="submit" name="UpdateAll" value="', _('Update'), '" />
		</div>
	</form>';
}

include ('includes/footer.php');

?>