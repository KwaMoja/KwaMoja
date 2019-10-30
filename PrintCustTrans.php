<?php
include ('includes/session.php');

if (isset($_GET['FromTransNo'])) {
	$FromTransNo = filter_number_format($_GET['FromTransNo']);
} elseif (isset($_POST['FromTransNo'])) {
	$FromTransNo = filter_number_format($_POST['FromTransNo']);
} else {
	$FromTransNo = '';
}

if (isset($_GET['InvOrCredit'])) {
	$InvOrCredit = $_GET['InvOrCredit'];
} elseif (isset($_POST['InvOrCredit'])) {
	$InvOrCredit = $_POST['InvOrCredit'];
}

if (isset($_GET['PrintPDF'])) {
	$PrintPDF = $_GET['PrintPDF'];
} elseif (isset($_POST['PrintPDF'])) {
	$PrintPDF = $_POST['PrintPDF'];
}

if (!isset($_POST['ToTransNo']) or trim($_POST['ToTransNo']) == '' or filter_number_format($_POST['ToTransNo']) < $FromTransNo) {
	$_POST['ToTransNo'] = $FromTransNo;
}

$FirstTrans = $FromTransNo;
/* Need to start a new page only on subsequent transactions */

if (isset($PrintPDF) or isset($_GET['PrintPDF']) and $PrintPDF and isset($FromTransNo) and isset($InvOrCredit) and $FromTransNo != '') {
	$PaperSize = 'A4_Landscape';
	include ('includes/PDFStarter.php');

	if ($InvOrCredit == 'Invoice') {
		$PDF->addInfo('Title', _('Sales Invoice') . ' ' . $FromTransNo . _('to') . $_POST['ToTransNo']);
		$PDF->addInfo('Subject', _('Invoices from') . ' ' . $FromTransNo . ' ' . _('to') . ' ' . $_POST['ToTransNo']);
	} else {
		$PDF->addInfo('Title', _('Sales Credit Note'));
		$PDF->addInfo('Subject', _('Credit Notes from') . ' ' . $FromTransNo . ' ' . _('to') . ' ' . $_POST['ToTransNo']);
	}

	$FirstPage = true;
	$line_height = 16;

	//Keep a record of the user's language
	$UserLanguage = $_SESSION['Language'];

	while ($FromTransNo <= filter_number_format($_POST['ToTransNo'])) {

		/* retrieve the invoice details from the database to print
		notice that salesorder record must be present to print the invoice purging of sales orders will
		nobble the invoice reprints */

		// check if the user has set a default bank account for invoices, if not leave it blank
		$SQL = "SELECT bankaccounts.invoice,
					bankaccounts.bankaccountnumber,
					bankaccounts.bankaccountcode
				FROM bankaccounts
				WHERE bankaccounts.invoice = '1'";
		$Result = DB_query($SQL, '', '', false, false);
		if (DB_error_no() != 1) {
			if (DB_num_rows($Result) == 1) {
				$MyRow = DB_fetch_array($Result);
				$DefaultBankAccountNumber = _('Account') . ': ' . $MyRow['bankaccountnumber'];
				$DefaultBankAccountCode = _('Bank Code:') . ' ' . $MyRow['bankaccountcode'];
			} else {
				$DefaultBankAccountNumber = '';
				$DefaultBankAccountCode = '';
			}
		} else {
			$DefaultBankAccountNumber = '';
			$DefaultBankAccountCode = '';
		}
		// gather the invoice data
		if (isset($_POST['LocCode']) and $_POST['LocCode'] == 'All') {
			$_POST['LocCode'] = '%';
		}

		if ($InvOrCredit == 'Invoice') {
			$SQL = "SELECT debtortrans.trandate,
							debtortrans.ovamount,
							debtortrans.ovdiscount,
							debtortrans.ovfreight,
							debtortrans.ovgst,
							debtortrans.rate,
							debtortrans.invtext,
							debtortrans.packages,
							debtortrans.consignment,
							debtorsmaster.name,
							debtorsmaster.address1,
							debtorsmaster.address2,
							debtorsmaster.address3,
							debtorsmaster.address4,
							debtorsmaster.address5,
							debtorsmaster.address6,
							debtorsmaster.currcode,
							debtorsmaster.invaddrbranch,
							debtorsmaster.taxref,
							debtorsmaster.language_id,
							paymentterms.terms,
							paymentterms.dayinfollowingmonth,
							paymentterms.daysbeforedue,
							salesorders.deliverto,
							salesorders.deladd1,
							salesorders.deladd2,
							salesorders.deladd3,
							salesorders.deladd4,
							salesorders.deladd5,
							salesorders.deladd6,
							salesorders.customerref,
							salesorders.orderno,
							salesorders.orddate,
							locations.locationname,
							shippers.shippername,
							custbranch.brname,
							custbranch.braddress1,
							custbranch.braddress2,
							custbranch.braddress3,
							custbranch.braddress4,
							custbranch.braddress5,
							custbranch.braddress6,
							custbranch.brpostaddr1,
							custbranch.brpostaddr2,
							custbranch.brpostaddr3,
							custbranch.brpostaddr4,
							custbranch.brpostaddr5,
							custbranch.brpostaddr6,
							custbranch.salesman,
							salesman.salesmanname,
							debtortrans.debtorno,
							debtortrans.branchcode,
							currencies.decimalplaces
						FROM debtortrans
						INNER JOIN debtorsmaster
							ON debtortrans.debtorno=debtorsmaster.debtorno
						INNER JOIN custbranch
							ON debtortrans.debtorno=custbranch.debtorno
							AND debtortrans.branchcode=custbranch.branchcode
						INNER JOIN salesorders
							ON debtortrans.order_ = salesorders.orderno
						INNER JOIN shippers
							ON debtortrans.shipvia=shippers.shipper_id
						INNER JOIN salesman
							ON custbranch.salesman=salesman.salesmancode
						INNER JOIN locations
							ON salesorders.fromstkloc=locations.loccode
						INNER JOIN locationusers
							ON locationusers.loccode=locations.loccode
							AND locationusers.userid='" . $_SESSION['UserID'] . "'
							AND locationusers.canview=1
						INNER JOIN paymentterms
							ON debtorsmaster.paymentterms=paymentterms.termsindicator
						INNER JOIN currencies
							ON debtorsmaster.currcode=currencies.currabrev
						WHERE debtortrans.type=10
							AND debtortrans.transno='" . $FromTransNo . "'";

			if (isset($_POST['PrintEDI']) and $_POST['PrintEDI'] == 'No') {
				$SQL = $SQL . " AND debtorsmaster.ediinvoices=0";
			}
		} else { /* then its a credit note */
			$SQL = "SELECT debtortrans.trandate,
							debtortrans.ovamount,
							debtortrans.ovdiscount,
							debtortrans.ovfreight,
							debtortrans.ovgst,
							debtortrans.rate,
							debtortrans.invtext,
							debtorsmaster.invaddrbranch,
							debtorsmaster.name,
							debtorsmaster.address1,
							debtorsmaster.address2,
							debtorsmaster.address3,
							debtorsmaster.address4,
							debtorsmaster.address5,
							debtorsmaster.address6,
							debtorsmaster.currcode,
							debtorsmaster.taxref,
							debtorsmaster.language_id,
							custbranch.brname,
							custbranch.braddress1,
							custbranch.braddress2,
							custbranch.braddress3,
							custbranch.braddress4,
							custbranch.braddress5,
							custbranch.braddress6,
							custbranch.brpostaddr1,
							custbranch.brpostaddr2,
							custbranch.brpostaddr3,
							custbranch.brpostaddr4,
							custbranch.brpostaddr5,
							custbranch.brpostaddr6,
							custbranch.salesman,
							salesman.salesmanname,
							debtortrans.debtorno,
							debtortrans.branchcode,
							currencies.currency,
							currencies.decimalplaces
						FROM debtortrans
						INNER JOIN debtorsmaster
							ON debtortrans.debtorno=debtorsmaster.debtorno
						INNER JOIN custbranch
							ON debtortrans.debtorno=custbranch.debtorno
							AND debtortrans.branchcode=custbranch.branchcode
						INNER JOIN salesman
							ON custbranch.salesman=salesman.salesmancode
						INNER JOIN currencies
							ON debtorsmaster.currcode=currencies.currabrev
						WHERE debtortrans.type=11
							AND debtortrans.transno='" . $FromTransNo . "'
							AND debtortrans.transno='" . $FromTransNo . "'";

			if (isset($_POST['PrintEDI']) and $_POST['PrintEDI'] == 'No') {
				$SQL = $SQL . " AND debtorsmaster.ediinvoices=0";
			}
		} // end else
		$Result = DB_query($SQL, '', '', false, false);

		if (DB_error_no() != 0) {
			$Title = _('Transaction Print Error Report');
			include ('includes/header.php');
			prnMsg(_('There was a problem retrieving the invoice or credit note details for note number') . ' ' . $InvoiceToPrint . ' ' . _('from the database') . '. ' . _('To print an invoice, the sales order record, the customer transaction record and the branch record for the customer must not have been purged') . '. ' . _('To print a credit note only requires the customer, transaction, salesman and branch records be available'), 'error');
			if ($Debug == 1) {
				prnMsg(_('The SQL used to get this information that failed was') . '<br />' . $SQL, 'error');
			}
			include ('includes/footer.php');
			exit;
		}
		if (DB_num_rows($Result) == 1) {

			$MyRow = DB_fetch_array($Result);

			if ($_SESSION['SalesmanLogin'] != '' and $_SESSION['SalesmanLogin'] != $MyRow['salesman']) {
				$Title = _('Select Invoices/Credit Notes To Print');
				include ('includes/header.php');
				prnMsg(_('Your account is set up to see only a specific salespersons orders. You are not authorised to view transaction for this order'), 'error');
				include ('includes/footer.php');
				exit;
			}

			if ($CustomerLogin == 1 and $MyRow['debtorno'] != $_SESSION['CustomerID']) {
				$Title = _('Select Invoices/Credit Notes To Print');
				include ('includes/header.php');
				echo '<p class="bad">' . _('This transaction is addressed to another customer and cannot be displayed for privacy reasons') . '. ' . _('Please select only transactions relevant to your company') . '</p>';
				include ('includes/footer.php');
				exit;
			}

			$ExchRate = $MyRow['rate'];

			//Change the language to the customer's language
			$_SESSION['Language'] = $MyRow['language_id'];
			include ('includes/LanguageSetup.php');

			if ($InvOrCredit == 'Invoice') {

				$SQL = "SELECT stockmoves.stockid,
								stockmaster.description,
								-stockmoves.qty as quantity,
								stockmoves.discountpercent,
								((1 - stockmoves.discountpercent) * stockmoves.price * " . $ExchRate . "* -stockmoves.qty) AS fxnet,
								(stockmoves.price * " . $ExchRate . ") AS fxprice,
								stockmoves.narrative,
								stockmaster.controlled,
								stockmaster.serialised,
								stockmaster.units,
								stockmoves.stkmoveno,
								stockmaster.decimalplaces
							FROM stockmoves INNER JOIN stockmaster
							ON stockmoves.stockid = stockmaster.stockid
							WHERE stockmoves.type=10
							AND stockmoves.transno=" . $FromTransNo . "
							AND stockmoves.show_on_inv_crds=1";
			} else {
				/* only credit notes to be retrieved */
				$SQL = "SELECT stockmoves.stockid,
								stockmaster.description,
								stockmoves.qty as quantity,
								stockmoves.discountpercent,
								((1 - stockmoves.discountpercent) * stockmoves.price * " . $ExchRate . " * stockmoves.qty) AS fxnet,
								(stockmoves.price * " . $ExchRate . ") AS fxprice,
								stockmoves.narrative,
								stockmaster.controlled,
								stockmaster.serialised,
								stockmaster.units,
								stockmoves.stkmoveno,
								stockmaster.decimalplaces
							FROM stockmoves INNER JOIN stockmaster
							ON stockmoves.stockid = stockmaster.stockid
							WHERE stockmoves.type=11
							AND stockmoves.transno=" . $FromTransNo . "
							AND stockmoves.show_on_inv_crds=1";
			} // end else
			$Result = DB_query($SQL);
			if (DB_error_no() != 0) {
				$Title = _('Transaction Print Error Report');
				include ('includes/header.php');
				echo '<br />' . _('There was a problem retrieving the invoice or credit note stock movement details for invoice number') . ' ' . $FromTransNo . ' ' . _('from the database');
				if ($Debug == 1) {
					echo '<br />' . _('The SQL used to get this information that failed was') . '<br />' . $SQL;
				}
				include ('includes/footer.php');
				exit;

			}

			if ($InvOrCredit == 'Invoice') {
				/* Calculate Due Date info. This reference is used in the PDFTransPageHeader.inc file. */
				$DisplayDueDate = CalcDueDate(ConvertSQLDate($MyRow['trandate']), $MyRow['dayinfollowingmonth'], $MyRow['daysbeforedue']);
			}

			if (DB_num_rows($Result) > 0) {

				$FontSize = 10;
				$PageNumber = 1;

				include ('includes/PDFTransPageHeader.php');
				$FirstPage = False;
				while ($MyRow2 = DB_fetch_array($Result)) {

					if ($MyRow2['discountpercent'] == 0) {
						$DisplayDiscount = '';
					} else {
						$DisplayDiscount = locale_number_format($MyRow2['discountpercent'] * 100, 2) . '%';
						$DiscountPrice = $MyRow2['fxprice'] * (1 - $MyRow2['discountpercent']);
					}
					$DisplayNet = locale_number_format($MyRow2['fxnet'], $MyRow['decimalplaces']);
					$DisplayPrice = locale_number_format($MyRow2['fxprice'], $MyRow['decimalplaces']);
					$DisplayQty = locale_number_format($MyRow2['quantity'], $MyRow2['decimalplaces']);

					$LeftOvers = $PDF->addTextWrap($Left_Margin + 3, $YPos, 95, $FontSize, $MyRow2['stockid']);
					//Get translation if it exists
					$TranslationResult = DB_query("SELECT descriptiontranslation
													FROM stockdescriptiontranslations
													WHERE stockid='" . $MyRow2['stockid'] . "'
													AND language_id='" . $MyRow['language_id'] . "'");

					if (DB_num_rows($TranslationResult) == 1) { //there is a translation
						$TranslationRow = DB_fetch_array($TranslationResult);
						$LeftOvers = $PDF->addTextWrap($Left_Margin + 100, $YPos, 251, $FontSize, $TranslationRow['descriptiontranslation']);
					} else {
						$LeftOvers = $PDF->addTextWrap($Left_Margin + 100, $YPos, 251, $FontSize, $MyRow2['description']);
					}

					$lines = 1;
					while ($LeftOvers != '') {
						$LeftOvers = $PDF->addTextWrap($Left_Margin + 100, $YPos, 251, $FontSize, $LeftOvers);
						$lines++;
					}

					$LeftOvers = $PDF->addTextWrap($Left_Margin + 353, $YPos, 96, $FontSize, $DisplayPrice, 'right');
					$LeftOvers = $PDF->addTextWrap($Left_Margin + 453, $YPos, 95, $FontSize, $DisplayQty, 'right');
					$LeftOvers = $PDF->addTextWrap($Left_Margin + 553, $YPos, 35, $FontSize, $MyRow2['units'], 'centre');
					$LeftOvers = $PDF->addTextWrap($Left_Margin + 590, $YPos, 50, $FontSize, $DisplayDiscount, 'right');
					$LeftOvers = $PDF->addTextWrap($Left_Margin + 642, $YPos, 120, $FontSize, $DisplayNet, 'right');
					if ($MyRow2['controlled'] == 1) {

						$GetControlMovts = DB_query("SELECT moveqty,
															serialno
														FROM stockserialmoves
														WHERE stockmoveno='" . $MyRow2['stkmoveno'] . "'");

						if ($MyRow2['serialised'] == 1) {
							while ($ControlledMovtRow = DB_fetch_array($GetControlMovts)) {
								$YPos-= ($line_height);
								$LeftOvers = $PDF->addTextWrap($Left_Margin + 100, $YPos, 100, $FontSize, $ControlledMovtRow['serialno'], 'left');
								if ($YPos - $line_height <= $Bottom_Margin) {
									/* head up a new invoice/credit note page */
									/*draw the vertical column lines right to the bottom */
									PrintLinesToBottom();
									include ('includes/PDFTransPageHeaderPortrait.inc');
								} //end if need a new page headed up
								
							}
						} else {
							while ($ControlledMovtRow = DB_fetch_array($GetControlMovts)) {
								$YPos-= ($line_height);
								$LeftOvers = $PDF->addTextWrap($Left_Margin + 100, $YPos, 100, $FontSize, (-$ControlledMovtRow['moveqty']) . ' x ' . $ControlledMovtRow['serialno'], 'left');
								if ($YPos - $line_height <= $Bottom_Margin) {
									/* head up a new invoice/credit note page */
									/*draw the vertical column lines right to the bottom */
									PrintLinesToBottom();
									include ('includes/PDFTransPageHeaderPortrait.inc');
								} //end if need a new page headed up
								
							}
						}
					}

					$YPos-= ($line_height);

					$lines = explode("\r\n", htmlspecialchars_decode($MyRow2['narrative']));
					$SizeOfLines = sizeOf($lines);
					for ($i = 0;$i < $SizeOfLines;$i++) {
						while (mb_strlen($lines[$i]) > 1) {
							if ($YPos - $line_height <= $Bottom_Margin) {
								/* head up a new invoice/credit note page */
								/* draw the vertical column lines right to the bottom */
								PrintLinesToBottom();
								include ('includes/PDFTransPageHeader.php');
							} //end if need a new page headed up
							/* increment a line down for the next line item */
							if (mb_strlen($lines[$i]) > 1) {
								$lines[$i] = $PDF->addTextWrap($Left_Margin + 100, $YPos, 245, $FontSize, stripslashes($lines[$i]));
							}
							$YPos-= ($line_height);
						}
					} //end for loop around lines of narrative to display
					if ($YPos <= $Bottom_Margin) {

						/* head up a new invoice/credit note page */
						/*draw the vertical column lines right to the bottom */
						PrintLinesToBottom();
						include ('includes/PDFTransPageHeader.php');
					} //end if need a new page headed up
					
				} //end while there are line items to print out
				
			}
			/*end if there are stock movements to show on the invoice or credit note*/

			$YPos-= $line_height;

			/* check to see enough space left to print the 4 lines for the totals/footer */
			if (($YPos - $Bottom_Margin) < (2 * $line_height)) {
				PrintLinesToBottom();
				include ('includes/PDFTransPageHeader.php');
			}
			/* Print a column vertical line  with enough space for the footer */
			/* draw the vertical column lines to 4 lines shy of the bottom to leave space for invoice footer info ie totals etc */
			$PDF->line($Left_Margin + 97, $TopOfColHeadings + 12, $Left_Margin + 97, $Bottom_Margin + (4 * $line_height));

			/* Print a column vertical line */
			$PDF->line($Left_Margin + 350, $TopOfColHeadings + 12, $Left_Margin + 350, $Bottom_Margin + (4 * $line_height));

			/* Print a column vertical line */
			$PDF->line($Left_Margin + 450, $TopOfColHeadings + 12, $Left_Margin + 450, $Bottom_Margin + (4 * $line_height));

			/* Print a column vertical line */
			$PDF->line($Left_Margin + 550, $TopOfColHeadings + 12, $Left_Margin + 550, $Bottom_Margin + (4 * $line_height));

			/* Print a column vertical line */
			$PDF->line($Left_Margin + 587, $TopOfColHeadings + 12, $Left_Margin + 587, $Bottom_Margin + (4 * $line_height));

			$PDF->line($Left_Margin + 640, $TopOfColHeadings + 12, $Left_Margin + 640, $Bottom_Margin + (4 * $line_height));

			/* Rule off at bottom of the vertical lines */
			$PDF->line($Left_Margin, $Bottom_Margin + (4 * $line_height), $Page_Width - $Right_Margin, $Bottom_Margin + (4 * $line_height));

			/* Now print out the footer and totals */

			if ($InvOrCredit == 'Invoice') {

				$DisplaySubTot = locale_number_format($MyRow['ovamount'], $MyRow['decimalplaces']);
				$DisplayFreight = locale_number_format($MyRow['ovfreight'], $MyRow['decimalplaces']);
				$DisplayTax = locale_number_format($MyRow['ovgst'], $MyRow['decimalplaces']);
				$DisplayTotal = locale_number_format($MyRow['ovfreight'] + $MyRow['ovgst'] + $MyRow['ovamount'], $MyRow['decimalplaces']);

			} else {

				$DisplaySubTot = locale_number_format(-$MyRow['ovamount'], $MyRow['decimalplaces']);
				$DisplayFreight = locale_number_format(-$MyRow['ovfreight'], $MyRow['decimalplaces']);
				$DisplayTax = locale_number_format(-$MyRow['ovgst'], $MyRow['decimalplaces']);
				$DisplayTotal = locale_number_format(-$MyRow['ovfreight'] - $MyRow['ovgst'] - $MyRow['ovamount'], $MyRow['decimalplaces']);
			}

			$FontSize = 8;
			$LeftOvers = $PDF->addTextWrap($Left_Margin + 5, $YPos - 12, 270, $FontSize, $MyRow['invtext']);
			if (mb_strlen($LeftOvers) > 0) {
				$LeftOvers = $PDF->addTextWrap($Left_Margin + 5, $YPos - 24, 270, $FontSize, $LeftOvers);
				if (mb_strlen($LeftOvers) > 0) {
					$LeftOvers = $PDF->addTextWrap($Left_Margin + 5, $YPos - 36, 270, $FontSize, $LeftOvers);
					/*If there is some of the InvText leftover after 3 lines 200 wide then it is not printed :( */
				}
			}
			$FontSize = 10;

			$PDF->addText($Page_Width - $Right_Margin - 220, $YPos + 15, $FontSize, _('Sub Total'));
			$LeftOvers = $PDF->addTextWrap($Left_Margin + 642, $YPos + 5, 120, $FontSize, $DisplaySubTot, 'right');

			$PDF->addText($Page_Width - $Right_Margin - 220, $YPos + 2, $FontSize, _('Freight'));
			$LeftOvers = $PDF->addTextWrap($Left_Margin + 642, $YPos - 8, 120, $FontSize, $DisplayFreight, 'right');

			$PDF->addText($Page_Width - $Right_Margin - 220, $YPos - 10, $FontSize, _('Tax'));
			$LeftOvers = $PDF->addTextWrap($Left_Margin + 642, $YPos - ($line_height) - 5, 120, $FontSize, $DisplayTax, 'right');

			/*rule off for total */
			$PDF->line($Page_Width - $Right_Margin - 222, $YPos - (2 * $line_height), $Page_Width - $Right_Margin, $YPos - (2 * $line_height));

			/*vertical to separate totals from comments and ROMALPA */
			$PDF->line($Page_Width - $Right_Margin - 222, $YPos + $line_height, $Page_Width - $Right_Margin - 222, $Bottom_Margin);

			$YPos+= 10;
			if ($InvOrCredit == 'Invoice') {
				/* Print out the payment terms */
				$PDF->addTextWrap($Left_Margin + 5, $YPos - 5, 280, $FontSize, _('Payment Terms') . ': ' . $MyRow['terms']);

				$PDF->addText($Page_Width - $Right_Margin - 220, $YPos - ($line_height * 2) - 10, $FontSize, _('TOTAL INVOICE'));
				$FontSize = 9;
				$YPos-= 4;
				$LeftOvers = $PDF->addTextWrap($Left_Margin + 280, $YPos, 260, $FontSize, $_SESSION['RomalpaClause']);
				while (mb_strlen($LeftOvers) > 0 and $YPos > $Bottom_Margin) {
					$YPos-= 12;
					$LeftOvers = $PDF->addTextWrap($Left_Margin + 280, $YPos, 260, $FontSize, $LeftOvers);
				}

				/* Add Images for Visa / Mastercard / Paypal */
				if (file_exists('companies/' . $_SESSION['DatabaseName'] . '/payment.jpg')) {
					$PDF->addJpegFromFile('companies/' . $_SESSION['DatabaseName'] . '/payment.jpg', $Page_Width / 2 - 280, $YPos - 20, 0, 40);
				}
				// Print Bank acount details if available and default for invoices is selected
				$PDF->addText($Page_Width - $Right_Margin - 490, $YPos - ($line_height * 3) + 32, $FontSize, $DefaultBankAccountCode . ' ' . $DefaultBankAccountNumber);
				$FontSize = 10;
			} else {
				$PDF->addText($Page_Width - $Right_Margin - 220, $YPos - ($line_height * 2) - 10, $FontSize, _('TOTAL CREDIT'));
			}
			$LeftOvers = $PDF->addTextWrap($Left_Margin + 642, 35, 120, $FontSize, $DisplayTotal, 'right');
		}
		/* end of check to see that there was an invoice record to print */

		$FromTransNo++;
	}
	/* end loop to print invoices */

	/* Put the transaction number back as would have been incremented by one after last pass */
	$FromTransNo--;

	if (isset($_GET['Email'])) { //email the invoice to address supplied
		$Title = _('Emailing') . ' ' . $InvOrCredit . ' ' . _('Number') . ' ' . $FromTransNo;
		include ('includes/header.php');
		include ('includes/PHPMailer/PHPMailerAutoload.php');
		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->CharSet = 'UTF-8';

		$SQL = "SELECT realname FROM www_users WHERE userid='" . $_SESSION['UserID'] . "'";
		$UserResult = DB_query($SQL);
		$MyUserRow = DB_fetch_array($UserResult);
		$SenderName = $MyUserRow['realname'];

		$mail->Host = $_SESSION['SMTPSettings']['host']; // SMTP server example
		$mail->SMTPDebug = 0; // enables SMTP debug information (for testing)
		$mail->SMTPAuth = $_SESSION['SMTPSettings']['auth'];
		$mail->SMTPSecure = $_SESSION['SMTPSettings']['security']; // enable SMTP authentication
		$mail->Port = $_SESSION['SMTPSettings']['port']; // set the SMTP port for the GMAIL server
		$mail->Username = html_entity_decode($_SESSION['SMTPSettings']['username']); // SMTP account username example
		$mail->Password = html_entity_decode($_SESSION['SMTPSettings']['password']); // SMTP account password example
		$mail->From = $_SESSION['CompanyRecord']['email'];
		$mail->FromName = $SenderName;
		$mail->addAddress($_GET['Email']); // Add a recipient
		$FileName = $_SESSION['reports_dir'] . '/' . $_SESSION['DatabaseName'] . '_' . $InvOrCredit . '_' . $FromTransNo . '.pdf';
		$PDF->Output($FileName, 'F');

		$mail->WordWrap = 50; // Set word wrap to 50 characters
		$mail->addAttachment($FileName); // Add attachments
		$mail->isHTML(true); // Set email format to HTML
		if (isset($_GET['Subject']) and $_GET['Subject'] != '') {
			$mail->Subject = $_GET['Subject'];
		} else {
			$mail->Subject = _('Please find attached') . ': ' . $InvOrCredit . ' ' . $FromTransNo;
		}
		$mail->Body = $InvOrCredit . ' ' . $FromTransNo;

		if (!$mail->send()) {
			echo 'Message could not be sent.';
			echo 'Mailer Error: ' . $mail->ErrorInfo;
		} else {
			echo '<p>' . $InvOrCredit . ' ' . _('number') . ' ' . $FromTransNo . ' ' . _('has been emailed to') . ' ' . $_GET['Email'];
		}

		unlink($FileName); //delete the temporary file
		include ('includes/footer.php');

		exit;

	} else { //its not an email just print the invoice to PDF
		$PDF->OutputD($_SESSION['DatabaseName'] . '_' . $InvOrCredit . '_' . $FromTransNo . '.pdf');

	}
	$PDF->__destruct();
	//Now change the language back to the user's language
	$_SESSION['Language'] = $UserLanguage;
	include ('includes/LanguageSetup.php');

} else {
	/*The option to print PDF was not hit */

	$Title = _('Select Invoices or Credit Notes To Print');
	/* Manual links before header.php */
	$ViewTopic = 'ARReports';
	$BookMark = 'PrintInvoicesCredits';
	include ('includes/header.php');

	if (!isset($FromTransNo) or $FromTransNo == '') {

		/* if FromTransNo is not set then show a form to allow input of either a single invoice number or a range of invoices to be printed. Also get the last invoice number created to show the user where the current range is up to */
		echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
		echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

		echo '<p class="page_title_text">
				<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/printer.png" title="', _('Print'), '" alt="" />', ' ', _('Print Invoices or Credit Notes (Landscape Mode)'), '
			</p>';
		echo '<fieldset>
				<legend>', _('Report Criteria'), '</legend>
				<field>
					<label for="InvOrCredit">', _('Print Invoices or Credit Notes'), '</label>
					<select name="InvOrCredit">';
		if (!isset($InvOrCredit) or $InvOrCredit == 'Invoice') {
			echo '<option selected="selected" value="Invoice">', _('Invoices'), '</option>';
			echo '<option value="Credit">', _('Credit Notes'), '</option>';
		} else {
			echo '<option selected="selected" value="Credit">', _('Credit Notes'), '</option>';
			echo '<option value="Invoice">', _('Invoices'), '</option>';
		}
		echo '</select>
			</field>';

		echo '<field>
				<label for="PrintEDI">', _('Print EDI Transactions'), '</label>
				<select name="PrintEDI">';
		if (!isset($InvOrCredit) or $InvOrCredit == 'Invoice') {
			echo '<option selected="selected" value="No">', _('Do not Print PDF EDI Transactions'), '</option>';
			echo '<option value="Yes">', _('Print PDF EDI Transactions Too'), '</option>';
		} else {
			echo '<option value="No">', _('Do not Print PDF EDI Transactions'), '</option>';
			echo '<option selected="selected" value="Yes">', _('Print PDF EDI Transactions Too'), '</option>';
		}
		echo '</select>
			</field>';

		echo '<field>
				<label for="LocCode">', _('Despatch Location'), ': </label>
				<select name="LocCode">';

		if ($_SESSION['RestrictLocations'] == 0) {
			$SQL = "SELECT locationname,
							loccode
						FROM locations";
			echo '<option selected="selected" value="All">', _('All Locations'), '</option>';
		} else {
			$SQL = "SELECT locationname,
							loccode
						FROM locations
						INNER JOIN www_users
							ON locations.loccode=www_users.defaultlocation
						WHERE www_users.userid='" . $_SESSION['UserID'] . "'";
		}
		$Result = DB_query($SQL);

		while ($MyRow = DB_fetch_array($Result)) {
			if (isset($_POST['LocCode']) and $MyRow['loccode'] == $_POST['LocCode']) {
				echo '<option selected="selected" value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
			} else {
				echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
			}
		} //end while loop
		echo '</select>
			</field>';

		echo '<field>
				<label for="FromTransNo">', _('Start invoice/credit note number to print'), '</label>
				<input type="text" class="number" required="required" maxlength="6" size="7" name="FromTransNo" />
			</field>';

		echo '<field>
				<label for="ToTransNo">', _('End invoice/credit note number to print'), '</label>
				<input type="text" class="number" required="required" maxlength="6" size="7" name="ToTransNo" />
			</field>
		</fieldset>';

		echo '<div class="centre">
				<input type="submit" name="Print" value="', _('Print'), '" />
				<input type="submit" name="PrintPDF" value="', _('Print PDF'), '" />
			</div>';

		$SQL = "SELECT typeno FROM systypes WHERE typeid=10";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);

		echo '<div class="page_help_text">
				<b>', _('The last invoice created was number'), ' ', $MyRow[0], '</b>
				<br />', _('If only a single invoice is required'), ', ', _('enter the invoice number to print in the Start transaction number to print field and leave the End transaction number to print field blank'), '. ', _('Only use the end invoice to print field if you wish to print a sequential range of invoices');

		$SQL = "SELECT typeno FROM systypes WHERE typeid=11";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);

		echo '<br /><b>', _('The last credit note created was number'), ' ', $MyRow[0], '</b><br />', _('A sequential range can be printed using the same method as for invoices above'), '. ', _('A single credit note can be printed by only entering a start transaction number'), '
		</div>';

		echo '</form>';
	} else { // A FromTransNo number IS set
		while ($FromTransNo <= filter_number_format($_POST['ToTransNo'])) {

			/*retrieve the invoice details from the database to print
			notice that salesorder record must be present to print the invoice purging of sales orders will
			nobble the invoice reprints */

			if ($InvOrCredit == 'Invoice') {

				$SQL = "SELECT debtortrans.trandate,
								debtortrans.ovamount,
								debtortrans.ovdiscount,
								debtortrans.ovfreight,
								debtortrans.ovgst,
								debtortrans.rate,
								debtortrans.invtext,
								debtortrans.consignment,
								debtorsmaster.name,
								debtorsmaster.address1,
								debtorsmaster.address2,
								debtorsmaster.address3,
								debtorsmaster.address4,
								debtorsmaster.address5,
								debtorsmaster.address6,
								debtorsmaster.currcode,
								salesorders.deliverto,
								salesorders.deladd1,
								salesorders.deladd2,
								salesorders.deladd3,
								salesorders.deladd4,
								salesorders.deladd5,
								salesorders.deladd6,
								salesorders.customerref,
								salesorders.orderno,
								salesorders.orddate,
								shippers.shippername,
								custbranch.brname,
								custbranch.braddress1,
								custbranch.braddress2,
								custbranch.braddress3,
								custbranch.braddress4,
								custbranch.braddress5,
								custbranch.braddress6,
								custbranch.salesman,
								salesman.salesmanname,
								debtortrans.debtorno,
								currencies.decimalplaces,
								paymentterms.dayinfollowingmonth,
								paymentterms.daysbeforedue,
								paymentterms.terms
							FROM debtortrans
							INNER JOIN debtorsmaster
								ON debtortrans.debtorno=debtorsmaster.debtorno
							INNER JOIN custbranch
								ON debtortrans.debtorno=custbranch.debtorno
								AND debtortrans.branchcode=custbranch.branchcode
							INNER JOIN salesorders
								ON debtortrans.order_ = salesorders.orderno
							INNER JOIN shippers
								ON debtortrans.shipvia=shippers.shipper_id
							INNER JOIN salesman
								ON custbranch.salesman=salesman.salesmancode
							INNER JOIN locations
								ON salesorders.fromstkloc=locations.loccode
							INNER JOIN locationusers
								ON locationusers.loccode=locations.loccode
								AND locationusers.userid='" . $_SESSION['UserID'] . "'
								AND locationusers.canview=1
							INNER JOIN paymentterms
								ON debtorsmaster.paymentterms=paymentterms.termsindicator
							INNER JOIN currencies
								ON debtorsmaster.currcode=currencies.currabrev
							WHERE debtortrans.type=10
								AND debtortrans.transno='" . $FromTransNo . "'";
			} else { /* then its a credit note */

				$SQL = "SELECT debtortrans.trandate,
								debtortrans.ovamount,
								debtortrans.ovdiscount,
								debtortrans.ovfreight,
								debtortrans.ovgst,
								debtortrans.rate,
								debtortrans.invtext,
								debtorsmaster.name,
								debtorsmaster.address1,
								debtorsmaster.address2,
								debtorsmaster.address3,
								debtorsmaster.address4,
								debtorsmaster.address5,
								debtorsmaster.address6,
								debtorsmaster.currcode,
								custbranch.brname,
								custbranch.braddress1,
								custbranch.braddress2,
								custbranch.braddress3,
								custbranch.braddress4,
								custbranch.braddress5,
								custbranch.braddress6,
								custbranch.salesman,
								salesman.salesmanname,
								debtortrans.debtorno,
								currencies.decimalplaces
							FROM debtortrans INNER JOIN debtorsmaster
							ON debtortrans.debtorno=debtorsmaster.debtorno
							INNER JOIN custbranch
							ON debtortrans.debtorno=custbranch.debtorno
							AND debtortrans.branchcode=custbranch.branchcode
							INNER JOIN salesman
							ON custbranch.salesman=salesman.salesmancode
							INNER JOIN currencies
							ON debtorsmaster.currcode=currencies.currabrev
							WHERE debtortrans.type=11
							AND debtortrans.transno='" . $FromTransNo . "'";
			}

			$Result = DB_query($SQL);
			if (DB_num_rows($Result) == 0 or DB_error_no() != 0) {
				echo '<div class="centre">
						', _('There was a problem retrieving the invoice or credit note details for note number'), ' ', $FromTransNo, ' ', _('from the database'), '. ', _('To print an invoice, the sales order record, the customer transaction record and the branch record for the customer must not have been purged'), '. ', _('To print a credit note only requires the customer, transaction, salesman and branch records be available'), '
					</div>';
				if ($Debug == 1) {
					echo _('The SQL used to get this information that failed was'), '<br />', $SQL;
				}
				include ('includes/footer.php');
				exit;
			} elseif (DB_num_rows($Result) == 1) {

				$MyRow = DB_fetch_array($Result);

				if ($_SESSION['SalesmanLogin'] != '' and $_SESSION['SalesmanLogin'] != $MyRow['salesman']) {
					prnMsg(_('Your account is set up to see only a specific salespersons orders. You are not authorised to view transaction for this order'), 'error');
					include ('includes/footer.php');
					exit;
				}

				if (($_SESSION['CustomerID'] != '') and $MyRow['debtorno'] != $_SESSION['CustomerID']) {
					/* If it's a customer login and the invoice is for a different customer the do not print */
					prnMsg(_('This transaction is addressed to another customer and cannot be printed for privacy reasons') . '. ' . _('Please select only transactions relevant to your company'), 'error');
					include ('includes/header.php');
					exit;
				}
				/* Then there's an invoice (or credit note) to print. So print out the invoice header and GST Number from the company record */
				if (count($_SESSION['AllowedPageSecurityTokens']) == 1 and in_array(1, $_SESSION['AllowedPageSecurityTokens']) and $MyRow['debtorno'] != $_SESSION['CustomerID']) {

					echo '<p class="bad">
							', _('This transaction is addressed to another customer and cannot be displayed for privacy reasons'), '. ', _('Please select only transactions relevant to your company'), '</p>';
					exit;
				}

				$ExchRate = $MyRow['rate'];
				$PageNumber = 1;
				/* Now print out the logo and company name and address */
				echo '<table style="width:100%">
						<tr>
							<td valign="top"><b>', _('Charge To'), ':</b>';
				echo '<br />', $MyRow['name'];
				if ($MyRow['address1'] != '') echo '<br />', $MyRow['address1'];
				if ($MyRow['address2'] != '') echo '<br />', $MyRow['address2'];
				if ($MyRow['address3'] != '') echo '<br />', $MyRow['address3'];
				if ($MyRow['address4'] != '') echo '<br />', $MyRow['address4'];
				if ($MyRow['address5'] != '') echo '<br />', $MyRow['address5'];
				if ($MyRow['address6'] != '') echo '<br />', $MyRow['address6'];
				echo '</td>';

				echo '<td><h2 style="margin:0px;padding:0px">', $_SESSION['CompanyRecord']['coyname'], '</h2>';
				if ($_SESSION['CompanyRecord']['regoffice1'] != '') echo '<br />', $_SESSION['CompanyRecord']['regoffice1'];
				if ($_SESSION['CompanyRecord']['regoffice2'] != '') echo '<br />', $_SESSION['CompanyRecord']['regoffice2'];
				if ($_SESSION['CompanyRecord']['regoffice3'] != '') echo '<br />', $_SESSION['CompanyRecord']['regoffice3'];
				if ($_SESSION['CompanyRecord']['regoffice4'] != '') echo '<br />', $_SESSION['CompanyRecord']['regoffice4'];
				if ($_SESSION['CompanyRecord']['regoffice5'] != '') echo '<br />', $_SESSION['CompanyRecord']['regoffice5'];
				if ($_SESSION['CompanyRecord']['regoffice6'] != '') echo '<br />', $_SESSION['CompanyRecord']['regoffice6'];
				echo '<br />', _('Telephone'), ': ', $_SESSION['CompanyRecord']['telephone'], '<br />';
				echo _('Facsimile'), ': ', $_SESSION['CompanyRecord']['fax'], '<br />';
				echo _('Email'), ': ', $_SESSION['CompanyRecord']['email'], '<br />';
				echo '</td>';

				echo '<td align="right" valign="top">';
				if ($InvOrCredit == 'Invoice') {
					echo '<h2 style="margin:0px;padding:0px">', _('TAX INVOICE'), '</h2>';
				} else {
					echo '<h2 style="margin:0px;padding:0px;color:red">', _('TAX CREDIT NOTE'), '</h2>';
				}
				echo '<br />', _('Number'), ' ', $FromTransNo, '<br />', _('Tax Authority Ref'), '. ', $_SESSION['CompanyRecord']['gstno'], '</td>
					</tr>';

				echo '<tr>
						<td align="left"><b>', _('Charge Branch'), ':</b>';
				echo '<br />', $MyRow['brname'];
				if ($MyRow['braddress1'] != '') echo '<br />', $MyRow['braddress1'];
				if ($MyRow['braddress2'] != '') echo '<br />', $MyRow['braddress2'];
				if ($MyRow['braddress3'] != '') echo '<br />', $MyRow['braddress3'];
				if ($MyRow['braddress4'] != '') echo '<br />', $MyRow['braddress4'];
				if ($MyRow['braddress5'] != '') echo '<br />', $MyRow['braddress5'];
				if ($MyRow['braddress6'] != '') echo '<br />', $MyRow['braddress6'];
				echo '</td>';

				if ($InvOrCredit == 'Invoice') {
					echo '<td align="left"><b>' . _('Delivered To') . ':</b>';
					echo '<br />' . $MyRow['deliverto'];
					if ($MyRow['deladd1'] != '') echo '<br />', $MyRow['deladd1'];
					if ($MyRow['deladd2'] != '') echo '<br />', $MyRow['deladd2'];
					if ($MyRow['deladd3'] != '') echo '<br />', $MyRow['deladd3'];
					if ($MyRow['deladd4'] != '') echo '<br />', $MyRow['deladd4'];
					if ($MyRow['deladd5'] != '') echo '<br />', $MyRow['deladd5'];
					if ($MyRow['deladd6'] != '') echo '<br />', $MyRow['deladd6'];
					echo '</td>';
				} else {
					echo '<td></td>';
				}

				echo '<td align="right">
						<br /><b>', _('All amounts stated in'), ' ', $MyRow['currcode'], '</b>
					</td>
				</tr>
			</table>';
				/*end of the main table showing the company name and charge to details */

				if ($InvOrCredit == 'Invoice') {

					echo '<table style="width:100%">
							<tr>
								<th><b>', _('Your Order Ref'), '</b></th>
								<th><b>', _('Our Order No'), '</b></th>
								<th><b>', _('Order Date'), '</b></th>
								<th><b>', _('Invoice Date'), '</b></th>
								<th><b>', _('Sales Person'), '</b></th>
								<th><b>', _('Shipper'), '</b></th>
								<th><b>', _('Consignment Ref'), '</b></th>
							</tr>
							<tr>
								<td>', $MyRow['customerref'], '</td>
								<td>', $MyRow['orderno'], '</td>
								<td>', ConvertSQLDate($MyRow['orddate']), '</td>
								<td>', ConvertSQLDate($MyRow['trandate']), '</td>
								<td>', $MyRow['salesmanname'], '</td>
								<td>', $MyRow['shippername'], '</td>
								<td>', $MyRow['consignment'], '</td>
							</tr>
						</table>';

					$SQL = "SELECT stockmoves.stockid,
						   		stockmaster.description,
								-stockmoves.qty as quantity,
								stockmoves.discountpercent,
								((1 - stockmoves.discountpercent) * stockmoves.price * " . $ExchRate . "* -stockmoves.qty) AS fxnet,
								(stockmoves.price * " . $ExchRate . ") AS fxprice,
								stockmoves.narrative,
								stockmaster.units,
								stockmaster.decimalplaces
							FROM stockmoves,
								stockmaster
							WHERE stockmoves.stockid = stockmaster.stockid
							AND stockmoves.type=10
							AND stockmoves.transno='" . $FromTransNo . "'
							AND stockmoves.show_on_inv_crds=1";

				} else {
					/* then its a credit note */

					echo '<table>
							<tr>
								<td><b>', _('Date'), '</b></td>
								<td><b>', _('Sales Person'), '</b></td>
							</tr>
							<tr>
								<td>', ConvertSQLDate($MyRow['trandate']), '</td>
								<td>', $MyRow['salesmanname'], '</td>
							</tr>
						</table>';

					$SQL = "SELECT stockmoves.stockid,
						   		stockmaster.description,
								stockmoves.qty as quantity,
								stockmoves.discountpercent, ((1 - stockmoves.discountpercent) * stockmoves.price * " . $ExchRate . " * stockmoves.qty) AS fxnet,
								(stockmoves.price * " . $ExchRate . ") AS fxprice,
								stockmoves.narrative,
								stockmaster.units,
								stockmaster.decimalplaces
							FROM stockmoves,
								stockmaster
							WHERE stockmoves.stockid = stockmaster.stockid
							AND stockmoves.type=11
							AND stockmoves.transno='" . $FromTransNo . "'
							AND stockmoves.show_on_inv_crds=1";
				}

				$Result = DB_query($SQL);
				if (DB_error_no() != 0) {
					echo '<br />', _('There was a problem retrieving the invoice or credit note stock movement details for invoice number'), '&nbsp;', $FromTransNo, '&nbsp;', _('from the database');
					if ($Debug == 1) {
						echo '<br />', _('The SQL used to get this information that failed was'), '<br />', $SQL;
					}
					exit;
				}

				if (DB_num_rows($Result) > 0) {
					echo '<table class="table1">
							<tr>
								<th>', _('Item Code'), '</th>
								<th>', _('Item Description'), '</th>
								<th>', _('Quantity'), '</th>
								<th>', _('Unit'), '</th>
								<th>', _('Price'), '</th>
								<th>', _('Discount'), '</th>
								<th>', _('Net'), '</th>
							</tr>';

					$LineCounter = 17;

					while ($MyRow2 = DB_fetch_array($Result)) {

						$DisplayPrice = locale_number_format($MyRow2['fxprice'], $MyRow['decimalplaces']);
						$DisplayQty = locale_number_format($MyRow2['quantity'], $MyRow2['decimalplaces']);
						$DisplayNet = locale_number_format($MyRow2['fxnet'], $MyRow['decimalplaces']);

						if ($MyRow2['discountpercent'] == 0) {
							$DisplayDiscount = '';
						} else {
							$DisplayDiscount = locale_number_format($MyRow2['discountpercent'] * 100, 2) . '%';
						}

						echo '<tr class="striped_row">
								<td>', $MyRow2['stockid'], '</td>
								<td>', $MyRow2['description'], '</td>
								<td class="number">', $DisplayQty, '</td>
								<td class="number">', $MyRow2['units'], '</td>
								<td class="number">', $DisplayPrice, '</td>
								<td class="number">', $DisplayDiscount, '</td>
								<td class="number">', $DisplayNet, '</td>
							</tr>';

						if (mb_strlen($MyRow2['narrative']) > 1) {
							$Narrative = str_replace(array("\r\n", "\n", "\r", "\\r\\n"), '<br />', $MyRow2['narrative']);
							echo $RowStarter . '<td colspan="7">', $Narrative, '</td></tr>';
							++$LineCounter;
						}

						++$LineCounter;

					} //end while there are line items to print out
					echo '</table>';
				}
				/*end if there are stock movements to show on the invoice or credit note*/

				/* Now print out the footer and totals */

				if ($InvOrCredit == 'Invoice') {

					$DisplaySubTot = locale_number_format($MyRow['ovamount'], $MyRow['decimalplaces']);
					$DisplayFreight = locale_number_format($MyRow['ovfreight'], $MyRow['decimalplaces']);
					$DisplayTax = locale_number_format($MyRow['ovgst'], $MyRow['decimalplaces']);
					$DisplayTotal = locale_number_format($MyRow['ovfreight'] + $MyRow['ovgst'] + $MyRow['ovamount'], $MyRow['decimalplaces']);
				} else {
					$DisplaySubTot = locale_number_format(-$MyRow['ovamount'], $MyRow['decimalplaces']);
					$DisplayFreight = locale_number_format(-$MyRow['ovfreight'], $MyRow['decimalplaces']);
					$DisplayTax = locale_number_format(-$MyRow['ovgst'], $MyRow['decimalplaces']);
					$DisplayTotal = locale_number_format(-$MyRow['ovfreight'] - $MyRow['ovgst'] - $MyRow['ovamount'], $MyRow['decimalplaces']);
				}

				/*Print out the invoice text entered */
				echo '<table style="width:100%">
						<tr>
							<td style="width:80%">', $MyRow['invtext'], '</td>
							<td class="number">', _('Sub Total'), '&nbsp;', $DisplaySubTot, '<br />';
				echo _('Freight'), '&nbsp;', $DisplayFreight, '<br />';
				echo _('Tax'), '&nbsp;', $DisplayTax, '<br />';
				if ($InvOrCredit == 'Invoice') {
					echo '<b>', _('TOTAL INVOICE'), '&nbsp;', $DisplayTotal, '</b>';
				} else {
					echo '<b>', _('TOTAL CREDIT'), '&nbsp;', $DisplayTotal, '</b>';
				}
				echo '</td>
					</tr>
				</table>';
			}
			/* end of check to see that there was an invoice record to print */
			++$FromTransNo;
		}
		/* end loop to print invoices */
	}
	/*end of if FromTransNo exists */
	include ('includes/footer.php');
}
/*end of else not PrintPDF */

function PrintLinesToBottom() {

	global $PDF;
	global $PageNumber;
	global $TopOfColHeadings;
	global $Left_Margin;
	global $Bottom_Margin;
	global $line_height;

	/* draw the vertical column lines right to the bottom */
	$PDF->line($Left_Margin + 97, $TopOfColHeadings + 12, $Left_Margin + 97, $Bottom_Margin);

	/* Print a column vertical line */
	$PDF->line($Left_Margin + 350, $TopOfColHeadings + 12, $Left_Margin + 350, $Bottom_Margin);

	/* Print a column vertical line */
	$PDF->line($Left_Margin + 450, $TopOfColHeadings + 12, $Left_Margin + 450, $Bottom_Margin);

	/* Print a column vertical line */
	$PDF->line($Left_Margin + 550, $TopOfColHeadings + 12, $Left_Margin + 550, $Bottom_Margin);

	/* Print a column vertical line */
	$PDF->line($Left_Margin + 587, $TopOfColHeadings + 12, $Left_Margin + 587, $Bottom_Margin);

	$PDF->line($Left_Margin + 640, $TopOfColHeadings + 12, $Left_Margin + 640, $Bottom_Margin);

	$PageNumber++;

}

?>