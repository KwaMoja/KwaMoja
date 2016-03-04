<?php

// BOMIndented.php - Indented Bill of Materials

include('includes/session.inc');

if (isset($_POST['PrintPDF'])) {

	include('includes/PDFStarter.php');
	$PDF->addInfo('Title', _('Indented BOM Listing'));
	$PDF->addInfo('Subject', _('Indented BOM Listing'));
	$FontSize = 9;
	$PageNumber = 1;
	$line_height = 12;

	$SQL = "DROP TABLE IF EXISTS tempbom";
	$Result = DB_query($SQL);
	$SQL = "DROP TABLE IF EXISTS passbom";
	$Result = DB_query($SQL);
	$SQL = "DROP TABLE IF EXISTS passbom2";
	$Result = DB_query($SQL);
	$SQL = "CREATE TEMPORARY TABLE passbom (
				part char(20),
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
	$SQL = "INSERT INTO passbom (part, sortpart)
				SELECT bom.component AS part,
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
					CONCAT(bom.parent,bom.component) AS sortpart,
					" . $LevelCounter . " AS level,
					bom.workcentreadded,
					bom.loccode,
					bom.effectiveafter,
					bom.effectiveto,
					bom.quantity
				FROM bom
				INNER JOIN locationusers
					ON locationusers.loccode=bom.loccode
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				WHERE bom.parent ='" . $_POST['Part'] . "'
					AND bom.effectiveto > CURRENT_DATE
					AND bom.effectiveafter <= CURRENT_DATE";
	$Result = DB_query($SQL);
	//echo "<br />sql is $SQL<br />";
	// This while routine finds the other levels as long as $ComponentCounter - the
	// component counter - finds there are more components that are used as
	// assemblies at lower levels

	$ComponentCounter = 1;
	if ($_POST['Levels'] == 'All') {
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
						$LevelCounter as level,
						bom.workcentreadded,
						bom.loccode,
						bom.effectiveafter,
						bom.effectiveto,
						bom.quantity
				FROM bom
				INNER JOIN passbom
					ON bom.parent = passbom.part
				INNER JOIN locationusers
					ON locationusers.loccode=bom.loccode
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				WHERE bom.effectiveto > CURRENT_DATE
					AND bom.effectiveafter <= CURRENT_DATE";
			$Result = DB_query($SQL);

			$SQL = "DROP TABLE IF EXISTS passbom2";
			$Result = DB_query($SQL);

			$SQL = "ALTER TABLE passbom RENAME AS passbom2";
			$Result = DB_query($SQL);

			$SQL = "DROP TABLE IF EXISTS passbom";
			$Result = DB_query($SQL);

			$SQL = "CREATE TEMPORARY TABLE passbom (
								part char(20),
								sortpart text) DEFAULT CHARSET=utf8";
			$Result = DB_query($SQL);


			$SQL = "INSERT INTO passbom (part, sortpart)
						SELECT bom.component AS part,
							CONCAT(passbom2.sortpart,bom.component) AS sortpart
						FROM bom,passbom2
						WHERE bom.parent = passbom2.part
							AND bom.effectiveto > CURRENT_DATE
							AND bom.effectiveafter <= CURRENT_DATE";
			$Result = DB_query($SQL);


			$SQL = "SELECT COUNT(*) FROM bom,passbom WHERE bom.parent = passbom.part";
			$Result = DB_query($SQL);

			$MyRow = DB_fetch_row($Result);
			$ComponentCounter = $MyRow[0];

		} // End of while $ComponentCounter > 0
	} // End of if $_POST['Levels']

	if (DB_error_no() != 0) {
		$Title = _('Indented BOM Listing') . ' - ' . _('Problem Report');
		include('includes/header.inc');
		prnMsg(_('The Indented BOM Listing could not be retrieved by the SQL because') . ' ' . DB_error_msg(), 'error');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($Debug == 1) {
			echo '<br />' . $SQL;
		}
		include('includes/footer.inc');
		exit;
	}


	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description
				FROM stockmaster
				WHERE stockid = " . "'" . $_POST['Part'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$assembly = $_POST['Part'];
	$assemblydesc = $MyRow['description'];

	PrintHeader($PDF, $YPos, $PageNumber, $Page_Height, $Top_Margin, $Left_Margin, $Page_Width, $Right_Margin, $assemblydesc);

	$Tot_Val = 0;
	$SQL = "SELECT tempbom.*,
				stockmaster.description,
				stockmaster.mbflag
			FROM tempbom,stockmaster
			WHERE tempbom.component = stockmaster.stockid
			ORDER BY sortpart";
	$Result = DB_query($SQL);

	// $fill is used to alternate between lines with transparent and painted background
	$fill = false;
	$PDF->SetFillColor(224, 235, 255);

	$ListCount = DB_num_rows($Result);

	while ($MyRow = DB_fetch_array($Result)) {

		$YPos -= $line_height;
		$FontSize = 8;

		$FormatedEffectiveAfter = ConvertSQLDate($MyRow['effectiveafter']);
		$FormatedEffectiveTo = ConvertSQLDate($MyRow['effectiveto']);


		if ($_POST['Fill'] == 'yes') {
			$fill = !$fill;
		}

		// Parameters for addTextWrap are defined in /includes/class.pdf.php
		// 1) X position 2) Y position 3) Width
		// 4) Height 5) Text 6) Alignment 7) Border 8) Fill - True to use SetFillColor
		// and False to set to transparent
		$PDF->addTextWrap($Left_Margin + ($MyRow['level'] * 5), $YPos, 90, $FontSize, $MyRow['component'], '', 0, $fill);
		$PDF->addTextWrap(160, $YPos, 20, $FontSize, $MyRow['mbflag'], '', 0, $fill);
		$PDF->addTextWrap(180, $YPos, 180, $FontSize, $MyRow['description'], '', 0, $fill);
		$PDF->addTextWrap(360, $YPos, 30, $FontSize, $MyRow['loccode'], 'right', 0, $fill);
		$PDF->addTextWrap(390, $YPos, 25, $FontSize, $MyRow['workcentreadded'], 'right', 0, $fill);
		$PDF->addTextWrap(415, $YPos, 45, $FontSize, locale_number_format($MyRow['quantity'], 'Variable'), 'right', 0, $fill);
		$PDF->addTextWrap(460, $YPos, 55, $FontSize, $FormatedEffectiveAfter, 'right', 0, $fill);
		$PDF->addTextWrap(515, $YPos, 50, $FontSize, $FormatedEffectiveTo, 'right', 0, $fill);

		if ($YPos < $Bottom_Margin + $line_height) {
			PrintHeader($PDF, $YPos, $PageNumber, $Page_Height, $Top_Margin, $Left_Margin, $Page_Width, $Right_Margin, $assemblydesc);
		}

	}
	/*end while loop */

	$FontSize = 10;
	$YPos -= (2 * $line_height);

	if ($YPos < $Bottom_Margin + $line_height) {
		PrintHeader($PDF, $YPos, $PageNumber, $Page_Height, $Top_Margin, $Left_Margin, $Page_Width, $Right_Margin, $assemblydesc);
	}

	if ($ListCount == 0) {
		$Title = _('Print Indented BOM Listing Error');
		include('includes/header.inc');
		prnMsg(_('There were no items for the selected assembly'), 'error');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		include('includes/footer.inc');
		exit;
	} else {
		$PDF->OutputD($_SESSION['DatabaseName'] . '_Bill_Of_Material_Indented_' . date('Y-m-d') . '.pdf');
		$PDF->__destruct();
	}

} else {
	/*The option to print PDF was not hit so display form */

	$Title = _('Indented BOM Listing');
	include('includes/header.inc');
	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<table class="selection">';
	echo '<tr>
			<td>' . _('Part') . ':</td>
			<td><input type ="text" name="Part" autofocus="autofocus" required="required" maxlength="20" size="20" /></td>
		</tr>
		<tr>
			<td>' . _('Levels') . ':</td>
			<td><select name="Levels">
				<option selected="selected" value="All">' . _('All Levels') . '</option>
				<option value="One">' . _('One Level') . '</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>' . _('Print Option') . ':</td>
			<td><select name="Fill">
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


function PrintHeader(&$PDF, &$YPos, &$PageNumber, $Page_Height, $Top_Margin, $Left_Margin, $Page_Width, $Right_Margin, $assemblydesc) {

	$line_height = 12;
	/*PDF page header for Indented BOM Listing report */
	if ($PageNumber > 1) {
		$PDF->newPage();
	}

	$FontSize = 9;
	$YPos = $Page_Height - $Top_Margin - 5;

	$PDF->addTextWrap($Left_Margin, $YPos, 300, $FontSize, $_SESSION['CompanyRecord']['coyname']);

	$YPos -= $line_height;

	$PDF->addTextWrap($Left_Margin, $YPos, 300, $FontSize, _('Indented BOM Listing'));
	$PDF->addTextWrap($Page_Width - $Right_Margin - 105, $YPos, 160, $FontSize, _('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . ' ' . _('Page') . ' ' . $PageNumber, 'left');

	$YPos -= (2 * $line_height);

	/*set up the headings */
	$Xpos = $Left_Margin + 1;

	$PDF->addTextWrap($Xpos, $YPos, 90, $FontSize, _('Part Number'), 'left');
	$PDF->addTextWrap(160, $YPos, 20, $FontSize, _('M/B'), 'left');
	$PDF->addTextWrap(180, $YPos, 180, $FontSize, _('Description'), 'center');
	$PDF->addTextWrap(360, $YPos, 30, $FontSize, _('Locn'), 'right');
	$PDF->addTextWrap(390, $YPos, 25, $FontSize, _('WC'), 'right');
	$PDF->addTextWrap(415, $YPos, 45, $FontSize, _('Quantity'), 'right');
	$PDF->addTextWrap(460, $YPos, 55, $FontSize, _('From Date'), 'right');
	$PDF->addTextWrap(515, $YPos, 50, $FontSize, _('To Date'), 'right');
	$YPos = $YPos - $line_height;

	$FontSize = 8;
	$YPos = $YPos - (2 * $line_height);

	$PDF->addTextWrap($Left_Margin + 1, $YPos, 40, $FontSize, _('Assembly') . ': ', '', 0);
	$PDF->addTextWrap(85, $YPos, 100, $FontSize, mb_strtoupper($_POST['Part']), '', 0);
	$PDF->addTextWrap(185, $YPos, 150, $FontSize, $assemblydesc, '', 0);
	$YPos -= (2 * $line_height);
	$Xpos = $Left_Margin + 5;
	$PDF->addTextWrap($Xpos, $YPos, 90, $FontSize, _(' 12345678901234567890'), 'left');
	$YPos -= $line_height;

	$PageNumber++;

} // End of PrintHeader function

?>