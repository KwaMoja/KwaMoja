<?php
include ('includes/session.php');
$Title = _('Sell Through Support Claims Report');

if (isset($_POST['PrintPDF'])) {

	include ('includes/PDFStarter.php');
	$PDF->addInfo('Title', _('Sell Through Support Claim'));
	$PDF->addInfo('Subject', _('Sell Through Support Claim'));
	$FontSize = 10;
	$PageNumber = 1;
	$line_height = 12;

	$Title = _('Sell Through Support Claim') . ' - ' . _('Problem Report');

	if (!is_date($_POST['FromDate']) or !is_date($_POST['ToDate'])) {
		include ('includes/header.php');
		prnMsg(_('The dates entered must be in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
		include ('includes/footer.php');
		exit;
	}

	/*Now figure out the data to report for the category range under review */
	$SQL = "SELECT sellthroughsupport.supplierno,
					suppliers.suppname,
					suppliers.currcode,
					currencies.decimalplaces as currdecimalplaces,
					stockmaster.stockid,
					stockmaster.decimalplaces,
					stockmaster.description,
					stockmoves.transno,
					stockmoves.trandate,
					systypes.typename,
					stockmoves.qty,
					stockmoves.debtorno,
					debtorsmaster.name,
					stockmoves.price*(1-stockmoves.discountpercent) as sellingprice,
					purchdata.price as fxcost,
					sellthroughsupport.rebatepercent,
					sellthroughsupport.rebateamount
				FROM stockmaster INNER JOIN stockmoves
					ON stockmaster.stockid=stockmoves.stockid
				INNER JOIN systypes
					ON stockmoves.type=systypes.typeid
				INNER JOIN debtorsmaster
					ON stockmoves.debtorno=debtorsmaster.debtorno
				INNER JOIN purchdata
					ON purchdata.stockid = stockmaster.stockid
				INNER JOIN suppliers
					ON suppliers.supplierid = purchdata.supplierno
				INNER JOIN sellthroughsupport
					ON sellthroughsupport.supplierno=suppliers.supplierid
				INNER JOIN currencies
					ON currencies.currabrev=suppliers.currcode
				WHERE stockmoves.trandate >= '" . FormatDateForSQL($_POST['FromDate']) . "'
				AND stockmoves.trandate <= '" . FormatDateForSQL($_POST['ToDate']) . "'
				AND sellthroughsupport.effectivefrom <= stockmoves.trandate
				AND sellthroughsupport.effectiveto >= stockmoves.trandate
				AND (stockmoves.type=10 OR stockmoves.type=11)
				AND (sellthroughsupport.stockid=stockmoves.stockid OR sellthroughsupport.categoryid=stockmaster.categoryid)
				AND (sellthroughsupport.debtorno=stockmoves.debtorno OR sellthroughsupport.debtorno='')
				ORDER BY sellthroughsupport.supplierno,
					stockmaster.stockid";

	$ClaimsResult = DB_query($SQL, '', '', false, false);

	if (DB_error_no() != 0) {

		include ('includes/header.php');
		prnMsg(_('The sell through support items to claim could not be retrieved by the SQL because') . ' - ' . DB_error_msg(), 'error');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($Debug == 1) {
			echo '<br />' . $SQL;
		}
		include ('includes/footer.php');
		exit;
	}

	if (DB_num_rows($ClaimsResult) == 0) {

		include ('includes/header.php');
		prnMsg(_('No sell through support items retrieved'), 'warn');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($Debug == 1) {
			echo '<br />' . $SQL;
		}
		include ('includes/footer.php');
		exit;
	}

	include ('includes/PDFSellThroughSupportClaimPageHeader.php');
	$SupplierClaimTotal = 0;
	$Supplier = '';
	$FontSize = 8;
	while ($SellThroRow = DB_fetch_array($ClaimsResult)) {

		$YPos-= $line_height;
		if ($SellThroRow['suppname'] != $Supplier) {
			if ($SupplierClaimTotal > 0) {
				$LeftOvers = $PDF->addTextWrap($Left_Margin + 2, $YPos, 30, $FontSize, $Supplier . ' ' . _('Total Claim') . ': (' . $CurrCode . ')');
				$LeftOvers = $PDF->addTextWrap(440, $YPos, 60, $FontSize, locale_number_format($SupplierClaimTotal, $CurrDecimalPlaces), 'right');
				include ('includes/PDFSellThroughClaimPageHeader.php');
			}
		}
		if ($SellThroRow['suppname'] != $Supplier) {
			$PDF->SetFont('helvetica', $style = 'B', $size = 11);
			$FontSize = 10;
			$YPos-= $line_height;
			$LeftOvers = $PDF->addTextWrap($Left_Margin + 2, $YPos, 250, $FontSize, $SellThroRow['suppname']);
			$Supplier = $SellThroRow['suppname'];
			$CurrDecimalPlaces = $SellThroRow['currdecimalplaces'];
			$CurrCode = $SellThroRow['currcode'];
			$SupplierClaimTotal = 0;
			$PDF->SetFont('helvetica', $style = 'N', $size = 8);
			$FontSize = 8;
			$YPos-= $line_height;
		}
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 2, $YPos, 60, $FontSize, $SellThroRow['typename'] . '-' . $SellThroRow['transno']);
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 63, $YPos, 160, $FontSize, $SellThroRow['stockid'] . '-' . $SellThroRow['description']);
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 223, $YPos, 110, $FontSize, $SellThroRow['name']);
		$DisplaySellingPrice = locale_number_format($SellThroRow['sellingprice'], $_SESSION['CompanyRecord']['decimalplaces']);
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 334, $YPos, 60, $FontSize, $DisplaySellingPrice, 'right');
		$ClaimAmount = (($SellThroRow['fxcost'] * $SellThroRow['rebatepercent']) + $SellThroRow['rebateamount']) * -$SellThroRow['qty'];
		$SupplierClaimTotal+= $ClaimAmount;

		$LeftOvers = $PDF->addTextWrap($Left_Margin + 395, $YPos, 60, $FontSize, locale_number_format(-$SellThroRow['qty']), 'right');
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 480, $YPos, 60, $FontSize, locale_number_format($ClaimAmount, $CurrDecimalPlaces), 'right');

		if ($YPos < $Bottom_Margin + $line_height) {
			include ('includes/PDFSellThroughSupportClaimPageHeader.php');
		}

	}
	/*end sell through support claims while loop */

	if ($SupplierClaimTotal > 0) {
		$YPos-= 5;
		$PDF->line($Left_Margin + 480, $YPos, $Left_Margin + 480 + 60, $YPos);
		$YPos-= $line_height;

		$LeftOvers = $PDF->addTextWrap($Left_Margin + 2, $YPos, 470, $FontSize, $Supplier . ' ' . _('Total Claim') . ': ', 'right');
		$LeftOvers = $PDF->addTextWrap($Left_Margin + 480, $YPos, 60, $FontSize, locale_number_format($SupplierClaimTotal, $CurrDecimalPlaces), 'right');
		$YPos-= 5;

		$PDF->line($Left_Margin + 480, $YPos, $Left_Margin + 480 + 60, $YPos);
		$YPos-= 1;
		$PDF->line($Left_Margin + 480, $YPos, $Left_Margin + 480 + 60, $YPos);

	}
	$FontSize = 10;

	$YPos-= (2 * $line_height);
	$PDF->OutputD($_SESSION['DatabaseName'] . '_SellThroughSupportClaim_' . date('Y-m-d') . '.pdf');
	$PDF->__destruct();

} else {
	/*The option to print PDF was not hit */

	include ('includes/header.php');

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/transactions.png" title="', $Title, '" alt="" />', ' ', _('Sell Through Support Claims Report'), '
		</p>';

	$_POST['FromDate'] = Date($_SESSION['DefaultDateFormat']);
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<fieldset>
			<legend>', _('Report Criteria'), '</legend>';

	echo '<field>
			<label for="FromDate">', _('Sales Made From'), ' (', _('in the format'), ' ', $_SESSION['DefaultDateFormat'], '):</label>
			<input type="text" class="date" name="FromDate" size="10" required="required" maxlength="10" value="', $_POST['FromDate'], '" />
		</field>';

	echo '<field>
			<label for="ToDate">', _('Sales Made To'), ' (', _('in the format'), ' ', $_SESSION['DefaultDateFormat'], '):</label>
			<input type="text" class="date" name="ToDate" size="10" required="required" maxlength="10" value="', $_POST['ToDate'], '" />
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="PrintPDF" value="' . _('Create Claims Report') . '" />
		</div>';

	echo '</form>';

	include ('includes/footer.php');

}
/*end of else not PrintPDF */

?>