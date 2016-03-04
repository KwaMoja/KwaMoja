<?php

/*     This script is an utility to change an inventory item code.
 *     It uses function ChangeFieldInTable($TableName, $FieldName, $OldValue,
 *     $NewValue) from .../includes/MiscFunctions.php.
 */

include('includes/session.inc');
$Title = _('UTILITY PAGE Change A Stock Code');// Screen identificator.
$ViewTopic = 'SpecialUtilities'; // Filename in ManualContents.php's TOC.
$BookMark = 'Z_ChangeStockCode'; // Anchor's id in the manual's html document.
include('includes/header.inc');

echo '<p class="page_title_text">
		<img alt="" src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Change An Inventory Item Code') . '" />' . ' ' . _('Change An Inventory Item Code') . '
	</p>';

include('includes/SQL_CommonFunctions.inc');

if (isset($_POST['ProcessStockChange'])) {

	$InputError = 0;

	$_POST['NewStockID'] = mb_strtoupper($_POST['NewStockID']);

	/*First check the stock code exists */
	$Result = DB_query("SELECT stockid FROM stockmaster WHERE stockid='" . $_POST['OldStockID'] . "'");
	if (DB_num_rows($Result) == 0) {
		prnMsg(_('The stock code') . ': ' . $_POST['OldStockID'] . ' ' . _('does not currently exist as a stock code in the system'), 'error');
		$InputError = 1;
	}

	if (ContainsIllegalCharacters($_POST['NewStockID'])) {
		prnMsg(_('The new stock code to change the old code to contains illegal characters - no changes will be made'), 'error');
		$InputError = 1;
	}

	if ($_POST['NewStockID'] == '') {
		prnMsg(_('The new stock code to change the old code to must be entered as well'), 'error');
		$InputError = 1;
	}


	/*Now check that the new code doesn't already exist */
	$Result = DB_query("SELECT stockid FROM stockmaster WHERE stockid='" . $_POST['NewStockID'] . "'");
	if (DB_num_rows($Result) != 0) {
		echo '<br /><br />';
		prnMsg(_('The replacement stock code') . ': ' . $_POST['NewStockID'] . ' ' . _('already exists as a stock code in the system') . ' - ' . _('a unique stock code must be entered for the new code'), 'error');
		$InputError = 1;
	}


	if ($InputError == 0) { // no input errors
		DB_IgnoreForeignKeys();
		$Result = DB_Txn_Begin();
		echo '<br />' . _('Adding the new stock master record');
		$SQL = "INSERT INTO stockmaster (stockid,
										categoryid,
										description,
										longdescription,
										units,
										mbflag,
										actualcost,
										lastcost,
										lowestlevel,
										discontinued,
										controlled,
										serialised,
										eoq,
										volume,
										grossweight,
										barcode,
										discountcategory,
										taxcatid,
										decimalplaces,
										shrinkfactor,
										pansize,
										netweight,
										perishable,
										nextserialno)
				SELECT '" . $_POST['NewStockID'] . "',
					categoryid,
					description,
					longdescription,
					units,
					mbflag,
					actualcost,
					lastcost,
					lowestlevel,
					discontinued,
					controlled,
					serialised,
					eoq,
					volume,
					grossweight,
					barcode,
					discountcategory,
					taxcatid,
					decimalplaces,
					shrinkfactor,
					pansize,
					netweight,
					perishable,
					nextserialno
				FROM stockmaster
				WHERE stockid='" . $_POST['OldStockID'] . "'";

		$DbgMsg = _('The SQL statement that failed was');
		$ErrMsg = _('The SQL to insert the new stock master record failed');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
		echo ' ... ' . _('completed');
		echo '<br />' . _('Copying over the costs');
		$SQL = "INSERT INTO stockcosts SELECT '" . $_POST['NewStockID'] . "',
												stockcosts.materialcost,
												stockcosts.labourcost,
												stockcosts.overheadcost,
												stockcosts.costfrom,
												stockcosts.succeeded
											FROM stockcosts
											WHERE stockid='" . $_POST['OldStockID'] . "'";

		$DbgMsg = _('The SQL statement that failed was');
		$ErrMsg = _('The SQL to insert the new stock costs record failed');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
		echo ' ... ' . _('completed');

		ChangeFieldInTable("locstock", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("stockmoves", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("loctransfers", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("mrpdemands", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);

		//check if MRP tables exist before assuming

		$SQL = "SELECT * FROM mrpparameters";
		$Result = DB_query($SQL, '', '', false, false);
		if (DB_error_no() == 0) {
			$Result = DB_query("SELECT COUNT(*) FROM mrpplannedorders", '', '', false, false);
			if (DB_error_no() == 0) {
				ChangeFieldInTable("mrpplannedorders", "part", $_POST['OldStockID'], $_POST['NewStockID']);
			}

			$Result = DB_query("SELECT * FROM mrprequirements", '', '', false, false);
			if (DB_error_no() == 0) {
				ChangeFieldInTable("mrprequirements", "part", $_POST['OldStockID'], $_POST['NewStockID']);
			}

			$Result = DB_query("SELECT * FROM mrpsupplies", '', '', false, false);
			if (DB_error_no() == 0) {
				ChangeFieldInTable("mrpsupplies", "part", $_POST['OldStockID'], $_POST['NewStockID']);
			}
		}

		ChangeFieldInTable("salesanalysis", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("orderdeliverydifferenceslog", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("prices", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("salesorderdetails", "stkcode", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("purchorderdetails", "itemcode", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("purchdata", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("shipmentcharges", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("stockcheckfreeze", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("stockcounts", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("grns", "itemcode", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("contractbom", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("bom", "component", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("bom", "parent", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("stockrequestitems", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("custitem", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("pickreqdetails", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);

		DB_IgnoreForeignKeys();

		ChangeFieldInTable("bom", "parent", $_POST['OldStockID'], $_POST['NewStockID']);

		echo '<br />' . _('Changing any image files');
		if (file_exists($_SESSION['part_pics_dir'] . '/' . $_POST['OldStockID'] . '.jpg')) {
			if (rename($_SESSION['part_pics_dir'] . '/' . $_POST['OldStockID'] . '.jpg', $_SESSION['part_pics_dir'] . '/' . $_POST['NewStockID'] . '.jpg')) {
				echo ' ... ' . _('completed');
			} else {
				echo ' ... ' . _('failed');
			}
		} else {
			echo ' ... ' . _('completed');
		}

		ChangeFieldInTable("stockitemproperties", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("worequirements", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("worequirements", "parentstockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("woitems", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("salescatprod", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("stockserialitems", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("stockserialmoves", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("offers", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("tenderitems", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("prodspecs", "keyval", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("qasamples", "prodspeckey", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("stockdescriptiontranslations", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);// Updates the translated item titles (StockTitles)
		ChangeFieldInTable("stocklongdescriptiontranslations", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("custitem", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);
		ChangeFieldInTable("pricematrix", "stockid", $_POST['OldStockID'], $_POST['NewStockID']);

		DB_ReinstateForeignKeys();

		$Result = DB_Txn_Commit();

		echo '<br />' . _('Deleting the old stock master record');
		$SQL = "DELETE FROM stockmaster WHERE stockid='" . $_POST['OldStockID'] . "'";
		$ErrMsg = _('The SQL to delete the old stock master record failed');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
		echo ' ... ' . _('completed');


		echo '<p>' . _('Stock Code') . ': ' . $_POST['OldStockID'] . ' ' . _('was successfully changed to') . ' : ' . $_POST['NewStockID'];

		// If the current SelectedStockItem is the same as the OldStockID, it updates to the NewStockID:
		if ($_SESSION['SelectedStockItem'] == $_POST['OldStockID']) {
			$_SESSION['SelectedStockItem'] = $_POST['NewStockID'];
		}

	} //only do the stuff above if  $InputError==0
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/inventory.png" title="' . _('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';
echo '<table>
	<tr>
		<td>' . _('Existing Inventory Code') . ':</td>
		<td><input type="text" name="OldStockID" size="20" maxlength="20" /></td>
	</tr>
	<tr>
		<td>' . _('New Inventory Code') . ':</td>
		<td><input type="text" name="NewStockID" size="20" maxlength="20" /></td>
	</tr>
	</table>

		<input type="submit" name="ProcessStockChange" value="' . _('Process') . '" />
	</form>';

include('includes/footer.inc');

?>