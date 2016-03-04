<?php

include('includes/session.inc');

if (isset($_POST['PrintPDF']) and isset($_POST['FromCriteria']) and mb_strlen($_POST['FromCriteria']) >= 1 and isset($_POST['ToCriteria']) and mb_strlen($_POST['ToCriteria']) >= 1) {

	include('includes/PDFStarter.php');
	$PDF->addInfo('Title', _('Bill Of Material Listing'));
	$PDF->addInfo('Subject', _('Bill Of Material Listing'));
	$FontSize = 12;
	$PageNumber = 0;
	$line_height = 12;

	/*Now figure out the bills to report for the part range under review */
	$SQL = "SELECT bom.parent,
				bom.component,
				stockmaster.description as compdescription,
				stockmaster.decimalplaces,
				bom.quantity,
				bom.loccode,
				bom.workcentreadded,
				bom.effectiveto AS eff_to,
				bom.effectiveafter AS eff_frm
			FROM stockmaster
			INNER JOIN bom
				ON stockmaster.stockid=bom.component
			INNER JOIN locationusers
				ON locationusers.loccode=bom.loccode
				AND locationusers.userid='" .  $_SESSION['UserID'] . "'
				AND locationusers.canview=1
			WHERE bom.parent >= '" . $_POST['FromCriteria'] . "'
				AND bom.parent <= '" . $_POST['ToCriteria'] . "'
				AND bom.effectiveto > CURRENT_DATE
				AND bom.effectiveafter <= CURRENT_DATE
			ORDER BY bom.parent,
					bom.component";

	$BOMResult = DB_query($SQL, '', '', false, false); //dont do error trapping inside DB_query

	if (DB_error_no() != 0) {
		$Title = _('Bill of Materials Listing') . ' - ' . _('Problem Report');
		include('includes/header.inc');
		prnMsg(_('The Bill of Material listing could not be retrieved by the SQL because'), 'error');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($Debug == 1) {
			echo '<br />' . $SQL;
		}
		include('includes/footer.inc');
		exit;
	}
	if (DB_num_rows($BOMResult) == 0) {
		$Title = _('Bill of Materials Listing') . ' - ' . _('Problem Report');
		include('includes/header.inc');
		prnMsg(_('The Bill of Material listing has no bills to report on'), 'warn');
		include('includes/footer.inc');
		exit;
	}

	include('includes/PDFBOMListingPageHeader.inc');

	$ParentPart = '';

	while ($BOMList = DB_fetch_array($BOMResult)) {

		if ($ParentPart != $BOMList['parent']) {

			$FontSize = 10;
			if ($ParentPart != '') {
				/*Then it's NOT the first time round */
				/* need to rule off from the previous parent listed */
				$YPos -= $line_height;
				$PDF->line($Page_Width - $Right_Margin, $YPos, $Left_Margin, $YPos);
				$YPos -= $line_height;
			}
			$SQL = "SELECT description FROM stockmaster WHERE stockmaster.stockid = '" . $BOMList['parent'] . "'";
			$ParentResult = DB_query($SQL);
			$ParentRow = DB_fetch_row($ParentResult);
			$LeftOvers = $PDF->addTextWrap($Left_Margin, $YPos, 400 - $Left_Margin, $FontSize, $BOMList['parent'] . ' - ' . $ParentRow[0], 'left');
			$ParentPart = $BOMList['parent'];
		}

		$YPos -= $line_height;
		$FontSize = 8;
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 5, $YPos, 80, $FontSize, $BOMList['component'], 'left');
		$LeftOvers = $PDF->addTextWrap(110, $YPos, 200, $FontSize, $BOMList['compdescription'], 'left');

		$DisplayQuantity = locale_number_format($BOMList['quantity'], $BOMList['decimalplaces']);
		$LeftOvers = $PDF->addTextWrap(320, $YPos, 50, $FontSize, ConvertSQLDate($BOMList['eff_frm']), 'left');
		$LeftOvers = $PDF->addTextWrap(370, $YPos, 50, $FontSize, ConvertSQLDate($BOMList['eff_to']), 'left');
		$LeftOvers = $PDF->addTextWrap(420, $YPos, 20, $FontSize, $BOMList['loccode'], 'left');
		$LeftOvers = $PDF->addTextWrap(440, $YPos, 30, $FontSize, $BOMList['workcentreadded'], 'left');
		$LeftOvers = $PDF->addTextWrap(480, $YPos, 60, $FontSize, $DisplayQuantity, 'right');

		if ($YPos < $Bottom_Margin + $line_height) {
			include('includes/PDFBOMListingPageHeader.inc');
		}

	}
	/*end BOM Listing while loop */

	$YPos -= $line_height;
	$PDF->line($Page_Width - $Right_Margin, $YPos, $Left_Margin, $YPos);

	$PDF->OutputD($_SESSION['DatabaseName'] . '_BOMListing_' . date('Y-m-d') . '.pdf');
	$PDF->__destruct();

} else {
	/*The option to print PDF was not hit */

	$Title = _('Bill Of Material Listing');
	include('includes/header.inc');

	$SQL = "SELECT min(stockid) AS fromcriteria,
					max(stockid) AS tocriteria
				FROM stockmaster";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/reports.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';
	if (!isset($_POST['FromCriteria']) or !isset($_POST['ToCriteria'])) {

		/*if $FromCriteria is not set then show a form to allow input	*/

		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">
			  <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			  <table class="selection">';

		echo '<tr>
				<td>' . _('From Inventory Part Code') . ':' . '</td>
				<td><input tabindex="1" type="text" name="FromCriteria" size="20" autofocus="autofocus" required="required" maxlength="20" value="' . $MyRow['fromcriteria'] . '" /></td>
			</tr>';

		echo '<tr>
				<td>' . _('To Inventory Part Code') . ':' . '</td>
				<td><input tabindex="2" type="text" name="ToCriteria" size="20" required="required" maxlength="20" value="' . $MyRow['tocriteria'] . '" /></td>
			</tr>';


		echo '</table>
				<div class="centre"><input tabindex="3" type="submit" name="PrintPDF" value="' . _('Print PDF') . '" /></div>
			 </form>';

	}
	include('includes/footer.inc');

}
/*end of else not PrintPDF */

?>