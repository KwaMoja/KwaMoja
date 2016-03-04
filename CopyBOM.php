<?php
/*
 * Author: Ashish Shukla <gmail.com!wahjava>
 *
 * Script to duplicate BoMs.
 */

include('includes/session.inc');

$Title = _('Copy a BOM to New Item Code');

include('includes/header.inc');

include('includes/SQL_CommonFunctions.inc');

if (isset($_POST['Submit'])) {
	$StockId = $_POST['StockID'];
	$NewOrExisting = $_POST['NewOrExisting'];
	$NewStockID = '';
	$InputError = 0; //assume the best

	if ($NewOrExisting == 'N') {
		$NewStockID = $_POST['ToStockID'];
		if (mb_strlen($NewStockID) == 0 or $NewStockID == '') {
			$InputError = 1;
			prnMsg(_('The new item code cannot be blank. Enter a new code for the item to copy the BOM to'), 'error');
		}
	} else {
		$NewStockID = $_POST['ExStockID'];
	}
	if ($InputError == 0) {
		$Result = DB_Txn_Begin();

		if ($NewOrExisting == 'N') {
			/* duplicate rows into stockmaster */
			$SQL = "INSERT INTO stockmaster( stockid,
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
									eoq,
									volume,
									grossweight,
									barcode,
									discountcategory,
									taxcatid,
									serialised,
									appendfile,
									perishable,
									decimalplaces,
									nextserialno,
									pansize,
									shrinkfactor,
									netweight )
							SELECT '" . $NewStockID . "' AS stockid,
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
									eoq,
									volume,
									grossweight,
									barcode,
									discountcategory,
									taxcatid,
									serialised,
									appendfile,
									perishable,
									decimalplaces,
									nextserialno,
									pansize,
									shrinkfactor,
									netweight
							FROM stockmaster
							WHERE stockid='" . $StockId . "'";
			$Result = DB_query($SQL);
			/* duplicate rows into stockcosts */
			$SQL = "INSERT INTO stockcosts SELECT  '" . $NewStockID . "' AS stockid,
															stockcosts.materialcost,
															stockcosts.labourcost,
															stockcosts.overheadcost,
															CURRENT_TIMESTAMP,
															0
														FROM stockcosts
														WHERE  stockcosts.stockid='" . $StockId . "'
															AND stockcosts.succeeded=0";
			$Result = DB_query($SQL);

		} else {

			$SQL = "UPDATE stockcosts SET succeeded=1
									WHERE stockid='" . $NewStockID . "'
										AND succeeded=0";
			$Result = DB_query($SQL);

			$SQL = "INSERT INTO stockcosts SELECT  '" . $NewStockID . "' AS stockid,
															stockcosts.materialcost,
															stockcosts.labourcost,
															stockcosts.overheadcost,
															CURRENT_TIMESTAMP,
															0
														FROM stockcosts
														WHERE  stockcosts.stockid='" . $StockId . "'
															AND stockcosts.succeeded=0";
			$Result = DB_query($SQL);
		}

		$SQL = "INSERT INTO bom
					SELECT '" . $NewStockID . "' AS parent,
							sequence,
							component,
							workcentreadded,
							loccode,
							effectiveafter,
							effectiveto,
							quantity,
							autoissue,
							comment
					FROM bom
					WHERE parent='" . $StockId . "';";
		$Result = DB_query($SQL);

		if ($NewOrExisting == 'N') {
			$SQL = "INSERT INTO locstock (
			            loccode,
			            stockid,
			            quantity,
			            reorderlevel
		        )
			  SELECT loccode,
					'" . $NewStockID . "' AS stockid,
					0 AS quantity,
					reorderlevel
				FROM locstock
				WHERE stockid='" . $StockId . "'";

			$Result = DB_query($SQL);
		}

		$Result = DB_Txn_Commit();

		UpdateCost($NewStockID);

		header('Location: BOMs.php?Select=' . $NewStockID);
		ob_end_flush();
	} //end  if there is no input error
} else {

	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/inventory.png" title="' . _('Contract') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	$SQL = "SELECT stockid,
					description
				FROM stockmaster
				WHERE stockid IN (SELECT DISTINCT parent FROM bom)
				AND  mbflag IN ('M', 'A', 'K', 'G');";
	$Result = DB_query($SQL);

	echo '<table class="selection">
			<tr>
				<td>' . _('From Stock ID') . '</td>';
	echo '<td><select name="StockID">';
	while ($MyRow = DB_fetch_row($Result)) {
		if (isset($_GET['Item']) and $MyRow[0] == $_GET['Item']) {
			echo '<option selected="selected" value="' . $MyRow[0] . '">' . $MyRow[0] . ' -- ' . $MyRow[1] . '</option>';
		} else {
			echo '<option value="' . $MyRow[0] . '">' . $MyRow[0] . ' -- ' . $MyRow[1] . '</option>';
		}
	}
	echo '</select></td>
			</tr>';
	echo '<tr>
			<td><input type="radio" name="NewOrExisting" value="N" />' . _(' To New Stock ID') . '</td>';
	echo '<td><input type="text" required="required" maxlength="20" name="ToStockID" /></td></tr>';

	$SQL = "SELECT stockid,
					description
				FROM stockmaster
				WHERE stockid NOT IN (SELECT DISTINCT parent FROM bom)
				AND mbflag IN ('M', 'A', 'K', 'G');";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) > 0) {
		echo '<tr>
				<td><input type="radio" name="NewOrExisting" value="E" />' . _('To Existing Stock ID') . '</td><td>';
		echo '<select name="ExStockID">';
		while ($MyRow = DB_fetch_row($Result)) {
			echo '<option value="' . $MyRow[0] . '">' . $MyRow[0] . ' -- ' . $MyRow[1] . '</option>';
		}
		echo '</select></td></tr>';
	}
	echo '</table>';
	echo '<div class="centre"><input type="submit" name="Submit" value="Submit" /></div>
		  </form>';

	include('includes/footer.inc');
}
?>