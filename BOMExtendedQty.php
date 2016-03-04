<?php

// BOMExtendedQty.php - Quantity Extended Bill of Materials

include('includes/session.inc');

if (isset($_POST['PrintPDF'])) {

	include('includes/PDFStarter.php');
	$PDF->addInfo('Title', _('Quantity Extended BOM Listing'));
	$PDF->addInfo('Subject', _('Quantity Extended BOM Listing'));
	$FontSize = 9;
	$PageNumber = 1;
	$line_height = 12;
	PrintHeader($PDF, $YPos, $PageNumber, $Page_Height, $Top_Margin, $Left_Margin, $Page_Width, $Right_Margin);

	if (!$_POST['Quantity'] or !is_numeric(filter_number_format($_POST['Quantity']))) {
		$_POST['Quantity'] = 1;
	}

	$Result = DB_query("DROP TABLE IF EXISTS tempbom");
	$Result = DB_query("DROP TABLE IF EXISTS passbom");
	$Result = DB_query("DROP TABLE IF EXISTS passbom2");
	$SQL = "CREATE TEMPORARY TABLE passbom (
				part char(20),
				extendedqpa double,
				sortpart text) DEFAULT CHARSET=utf8";
	$ErrMsg = _('The SQL to create passbom failed with the message');
	$Result = DB_query($SQL, $ErrMsg);

	$SQL = "CREATE TEMPORARY TABLE tempbom (
				parent char(20),
				component char(20),
				sortpart text,
				level int,
				workcentreadded char(5),
				loccode char(5),
				effectiveafter date,
				effectiveto date,
				quantity double) DEFAULT CHARSET=utf8";
	$Result = DB_query($SQL, _('Create of tempbom failed because'));
	// First, find first level of components below requested assembly
	// Put those first level parts in passbom, use COMPONENT in passbom
	// to link to PARENT in bom to find next lower level and accumulate
	// those parts into tempbom

	// This finds the top level
	$SQL = "INSERT INTO passbom (part, extendedqpa, sortpart)
			   SELECT bom.component AS part,
					  (" . filter_number_format($_POST['Quantity']) . " * bom.quantity) as extendedqpa,
					   CONCAT(bom.parent,bom.component) AS sortpart
					  FROM bom
			  WHERE bom.parent ='" . $_POST['Part'] . "'
			  AND bom.effectiveto > CURRENT_DATE
			  AND bom.effectiveafter <= CURRENT_DATE";
	$Result = DB_query($SQL);

	$LevelCounter = 2;
	// $LevelCounter is the level counter
	$SQL = "INSERT INTO tempbom (
				parent,
				component,
				sortpart,
				level,
				workcentreadded,
				loccode,
				effectiveafter,
				effectiveto,
				quantity)
			SELECT bom.parent,
					 bom.component,
					 CONCAT(bom.parent,bom.component) AS sortpart," . $LevelCounter . " as level,
					 bom.workcentreadded,
					 bom.loccode,
					 bom.effectiveafter,
					 bom.effectiveto,
					 (" . filter_number_format($_POST['Quantity']) . " * bom.quantity) as extendedqpa
			FROM bom
			WHERE bom.parent ='" . $_POST['Part'] . "'
			AND bom.effectiveto > CURRENT_DATE
			AND bom.effectiveafter <= CURRENT_DATE";
	$Result = DB_query($SQL);
	//echo "<br />sql is $SQL<br />";
	// This while routine finds the other levels as long as $ComponentCounter - the
	// component counter finds there are more components that are used as
	// assemblies at lower levels

	$ComponentCounter = 1;
	while ($ComponentCounter > 0) {
		$LevelCounter++;
		$SQL = "INSERT INTO tempbom (
				parent,
				component,
				sortpart,
				level,
				workcentreadded,
				loccode,
				effectiveafter,
				effectiveto,
				quantity)
			  SELECT bom.parent,
					 bom.component,
					 CONCAT(passbom.sortpart,bom.component) AS sortpart,
					 " . $LevelCounter . " as level,
					 bom.workcentreadded,
					 bom.loccode,
					 bom.effectiveafter,
					 bom.effectiveto,
					 (bom.quantity * passbom.extendedqpa)
			 FROM bom,passbom
			 WHERE bom.parent = passbom.part
			  AND bom.effectiveto > CURRENT_DATE
			  AND bom.effectiveafter <= CURRENT_DATE";
		$Result = DB_query($SQL);

		$Result = DB_query("DROP TABLE IF EXISTS passbom2");
		$Result = DB_query("ALTER TABLE passbom RENAME AS passbom2");
		$Result = DB_query("DROP TABLE IF EXISTS passbom");

		$SQL = "CREATE TEMPORARY TABLE passbom (part char(20),
												extendedqpa decimal(10,3),
												sortpart text) DEFAULT CHARSET=utf8";
		$Result = DB_query($SQL);

		$SQL = "INSERT INTO passbom (part,
									extendedqpa,
									sortpart)
									SELECT bom.component AS part,
											(bom.quantity * passbom2.extendedqpa),
											CONCAT(passbom2.sortpart,bom.component) AS sortpart
									FROM bom
									INNER JOIN passbom2
									ON bom.parent = passbom2.part
									WHERE bom.effectiveto > CURRENT_DATE
										AND bom.effectiveafter <= CURRENT_DATE";
		$Result = DB_query($SQL);

		$SQL = "SELECT COUNT(bom.parent) AS components
					FROM bom
					INNER JOIN passbom
					ON bom.parent = passbom.part
					GROUP BY passbom.part";
		$Result = DB_query($SQL);

		$MyRow = DB_fetch_array($Result);
		$ComponentCounter = $MyRow['components'];

	} // End of while $ComponentCounter > 0

	if (DB_error_no() != 0) {
		$Title = _('Quantity Extended BOM Listing') . ' - ' . _('Problem Report');
		include('includes/header.inc');
		prnMsg(_('The Quantiy Extended BOM Listing could not be retrieved by the SQL because') . ' ' . DB_error_msg(), 'error');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($Debug == 1) {
			echo '<br />' . $SQL;
		}
		include('includes/footer.inc');
		exit;
	}

	$Tot_Val = 0;
	$fill = false;
	$PDF->SetFillColor(224, 235, 255);
	$SQL = "SELECT tempbom.component,
				   SUM(tempbom.quantity) as quantity,
				   stockmaster.description,
				   stockmaster.decimalplaces,
				   stockmaster.mbflag,
				   (SELECT
					  SUM(locstock.quantity) as invqty
					  FROM locstock
					  INNER JOIN locationusers
						ON locationusers.loccode=locstock.loccode
						AND locationusers.userid='" .  $_SESSION['UserID'] . "'
						AND locationusers.canview=1
					  WHERE locstock.stockid = tempbom.component
					  GROUP BY locstock.stockid) AS qoh,
				   (SELECT
					  SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) as netqty
					  FROM purchorderdetails
					  INNER JOIN purchorders
						ON purchorderdetails.orderno=purchorders.orderno
					  INNER JOIN locationusers
						ON locationusers.loccode=purchorders.intostocklocation
						AND locationusers.userid='" .  $_SESSION['UserID'] . "'
						AND locationusers.canview=1
					  WHERE purchorderdetails.itemcode = tempbom.component
					  AND completed = 0
					  AND (purchorders.status = 'Authorised' OR purchorders.status='Printed')
					  GROUP BY purchorderdetails.itemcode) AS poqty,
				   (SELECT
					  SUM(woitems.qtyreqd - woitems.qtyrecd) as netwoqty
					  FROM woitems INNER JOIN workorders
						ON woitems.wo = workorders.wo
					  INNER JOIN locationusers
						ON locationusers.loccode=workorders.loccode
						AND locationusers.userid='" .  $_SESSION['UserID'] . "'
						AND locationusers.canview=1
					  WHERE woitems.stockid = tempbom.component
					  AND workorders.closed=0
					  GROUP BY woitems.stockid) AS woqty
			  FROM tempbom
			  INNER JOIN stockmaster
				ON tempbom.component = stockmaster.stockid
			  INNER JOIN locationusers
				ON locationusers.loccode=tempbom.loccode
				AND locationusers.userid='" .  $_SESSION['UserID'] . "'
				AND locationusers.canview=1
			  GROUP BY tempbom.component,
					   stockmaster.description,
					   stockmaster.decimalplaces,
					   stockmaster.mbflag";
	$Result = DB_query($SQL);
	$ListCount = DB_num_rows($Result);
	while ($MyRow = DB_fetch_array($Result)) {

		// Parameters for addTextWrap are defined in /includes/class.pdf.php
		// 1) X position 2) Y position 3) Width
		// 4) Height 5) Text 6) Alignment 7) Border 8) Fill - True to use SetFillColor
		// and False to set to transparent
		$Difference = $MyRow['quantity'] - ($MyRow['qoh'] + $MyRow['poqty'] + $MyRow['woqty']);
		if (($_POST['Select'] == 'All') or ($Difference > 0)) {
			$YPos -= $line_height;
			$FontSize = 8;
			// Use to alternate between lines with transparent and painted background
			if ($_POST['Fill'] == 'yes') {
				$fill = !$fill;
			}
			$PDF->addTextWrap($Left_Margin + 1, $YPos, 90, $FontSize, $MyRow['component'], '', 0, $fill);
			$PDF->addTextWrap(140, $YPos, 30, $FontSize, $MyRow['mbflag'], '', 0, $fill);
			$PDF->addTextWrap(170, $YPos, 140, $FontSize, $MyRow['description'], '', 0, $fill);
			$PDF->addTextWrap(310, $YPos, 50, $FontSize, locale_number_format($MyRow['quantity'], $MyRow['decimalplaces']), 'right', 0, $fill);
			$PDF->addTextWrap(360, $YPos, 40, $FontSize, locale_number_format($MyRow['qoh'], $MyRow['decimalplaces']), 'right', 0, $fill);
			$PDF->addTextWrap(400, $YPos, 40, $FontSize, locale_number_format($MyRow['poqty'], $MyRow['decimalplaces']), 'right', 0, $fill);
			$PDF->addTextWrap(440, $YPos, 40, $FontSize, locale_number_format($MyRow['woqty'], $MyRow['decimalplaces']), 'right', 0, $fill);
			$PDF->addTextWrap(480, $YPos, 50, $FontSize, locale_number_format($Difference, $MyRow['decimalplaces']), 'right', 0, $fill);
		}
		if ($YPos < $Bottom_Margin + $line_height) {
			PrintHeader($PDF, $YPos, $PageNumber, $Page_Height, $Top_Margin, $Left_Margin, $Page_Width, $Right_Margin);
		}

	}
	/*end while loop */

	$FontSize = 10;
	$YPos -= (2 * $line_height);

	if ($YPos < $Bottom_Margin + $line_height) {
		PrintHeader($PDF, $YPos, $PageNumber, $Page_Height, $Top_Margin, $Left_Margin, $Page_Width, $Right_Margin);
	}
	if ($ListCount == 0) {
		$Title = _('Print Indented BOM Listing Error');
		include('includes/header.inc');
		prnMsg(_('There were no items for the selected assembly'), 'error');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		include('includes/footer.inc');
		exit;
	} else {
		$PDF->OutputD($_SESSION['DatabaseName'] . '_BOM_Extended_Qty_' . date('Y-m-d') . '.pdf');
		$PDF->__destruct();
	}

} else {
	/*The option to print PDF was not hit so display form */

	$Title = _('Quantity Extended BOM Listing');
	include('includes/header.inc');
	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<table class="selection">
		<tr>
			<td>' . _('Part') . ':</td>
			<td><input type ="text" name="Part" autofocus="autofocus" required="required" maxlength="20" size="20" /></td>
		</tr>
		<tr>
			<td>' . _('Quantity') . ':</td>
			<td><input type="text" class="number" name="Quantity" required="required" maxlength="11" size="4" /></td>
		</tr>
		<tr>
			<td>' . _('Selection Option') . ':</td>
			<td>
				<select name="Select">
					<option selected="selected" value="All">' . _('Show All Parts') . '</option>
					<option value="Shortages">' . _('Only Show Shortages') . '</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>' . _('Print Option') . ':</td>
			<td>
				<select name="Fill">
					<option selected="selected" value="yes">' . _('Print With Alternating Highlighted Lines') . '</option>
					<option value="no">' . _('Plain Print') . '</option>
				</select>
			</td>
		</tr>
		</table>
		<div class="centre">
			<input type="submit" name="PrintPDF" value="' . _('Print PDF') . '" />
		</div>
		</form>';

	include('includes/footer.inc');

}
/*end of else not PrintPDF */


function PrintHeader(&$PDF, &$YPos, &$PageNumber, $Page_Height, $Top_Margin, $Left_Margin, $Page_Width, $Right_Margin) {

	/*PDF page header for BOMExtendedQTY report */
	if ($PageNumber > 1) {
		$PDF->newPage();
	}
	$line_height = 12;
	$FontSize = 9;
	$YPos = $Page_Height - $Top_Margin - 5;

	$PDF->addTextWrap($Left_Margin, $YPos, 300, $FontSize, $_SESSION['CompanyRecord']['coyname']);

	$YPos -= $line_height;

	$PDF->addTextWrap($Left_Margin, $YPos, 300, $FontSize, _('Extended Quantity BOM Listing For	   ') . mb_strtoupper($_POST['Part']));
	$PDF->addTextWrap($Page_Width - $Right_Margin - 140, $YPos, 160, $FontSize, _('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '   ' . _('Page') . ' ' . $PageNumber, 'left');
	$YPos -= $line_height;
	$PDF->addTextWrap($Left_Margin, $YPos, 300, $FontSize, _('Build Quantity:  ') . locale_number_format($_POST['Quantity'], 'Variable'), 'left');

	$YPos -= (2 * $line_height);

	/*set up the headings */
	$Xpos = $Left_Margin + 1;

	$PDF->addTextWrap(310, $YPos, 50, $FontSize, _('Build'), 'center');
	$PDF->addTextWrap(360, $YPos, 40, $FontSize, _('On Hand'), 'right');
	$PDF->addTextWrap(400, $YPos, 40, $FontSize, _('P.O.'), 'right');
	$PDF->addTextWrap(440, $YPos, 40, $FontSize, _('W.O.'), 'right');
	$YPos -= $line_height;
	$PDF->addTextWrap($Xpos, $YPos, 90, $FontSize, _('Part Number'), 'left');
	$PDF->addTextWrap(140, $YPos, 30, $FontSize, _('M/B'), 'left');
	$PDF->addTextWrap(170, $YPos, 140, $FontSize, _('Part Description'), 'left');
	$PDF->addTextWrap(310, $YPos, 50, $FontSize, _('Quantity'), 'right');
	$PDF->addTextWrap(360, $YPos, 40, $FontSize, _('Quantity'), 'right');
	$PDF->addTextWrap(400, $YPos, 40, $FontSize, _('Quantity'), 'right');
	$PDF->addTextWrap(440, $YPos, 40, $FontSize, _('Quantity'), 'right');
	$PDF->addTextWrap(480, $YPos, 50, $FontSize, _('Shortage'), 'right');

	$YPos = $YPos - (2 * $line_height);
	$PageNumber++;
} // End of PrintHeader function
?>