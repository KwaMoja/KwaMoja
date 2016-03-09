<?php

include('includes/DefinePaymentClass.php');
include('includes/session.inc');

$Title = _('Payment Entry');

if (isset($_GET['SupplierID'])) {
	$ViewTopic = 'AccountsPayable';
	$BookMark = 'SupplierPayments';
} else {
	$ViewTopic = 'GeneralLedger';
	$BookMark = 'BankAccountPayments';
}
include('includes/header.inc');

include('includes/SQL_CommonFunctions.inc');

if (isset($_POST['PaymentCancelled'])) {
	prnMsg(_('Payment Cancelled since cheque was not printed'), 'warning');
	include('includes/footer.inc');
	exit();
} //isset($_POST['PaymentCancelled'])
if (empty($_GET['identifier'])) {
	/*unique session identifier to ensure that there is no conflict with other order enty session on the same machine */
	$Identifier = date('U');
} else {
	$Identifier = $_GET['identifier']; //edit GLItems
}
if (isset($_GET['NewPayment']) and $_GET['NewPayment'] == 'Yes') {
	unset($_SESSION['PaymentDetail' . $Identifier]->GLItems);
	unset($_SESSION['PaymentDetail' . $Identifier]);
} //isset($_GET['NewPayment']) and $_GET['NewPayment'] == 'Yes'

if (!isset($_SESSION['PaymentDetail' . $Identifier])) {
	$_SESSION['PaymentDetail' . $Identifier] = new Payment;
	$_SESSION['PaymentDetail' . $Identifier]->GLItemCounter = 1;
} //!isset($_SESSION['PaymentDetail' . $Identifier])

if ((isset($_POST['UpdateHeader']) and $_POST['BankAccount'] == '') or (isset($_POST['Process']) and $_POST['BankAccount'] == '')) {
	prnMsg(_('A bank account must be selected to make this payment from'), 'warn');
	$BankAccountEmpty = true;
} //(isset($_POST['UpdateHeader']) and $_POST['BankAccount'] == '') or (isset($_POST['Process']) and $_POST['BankAccount'] == '')
else {
	$BankAccountEmpty = false;
}

if (isset($_POST['BankAccount'])) {
	$_SESSION['PaymentDetail' . $Identifier]->Account = $_POST['BankAccount'];
}

if (isset($_POST['DatePaid'])) {
	$_SESSION['PaymentDetail' . $Identifier]->DatePaid = $_POST['DatePaid'];
}

if (isset($_POST['Currency'])) {
	$_SESSION['PaymentDetail' . $Identifier]->Currency = $_POST['Currency'];
}

if (isset($_POST['ExRate'])) {
	$_SESSION['PaymentDetail' . $Identifier]->ExRate = $_POST['ExRate'];
}

if (isset($_POST['FunctionalExRate'])) {
	$_SESSION['PaymentDetail' . $Identifier]->FunctionalExRate = $_POST['FunctionalExRate'];
}

if (isset($_POST['Paymenttype'])) {
	$_SESSION['PaymentDetail' . $Identifier]->Paymenttype = $_POST['Paymenttype'];
}

if (isset($_POST['ChequeNum'])) {
	$_SESSION['PaymentDetail' . $Identifier]->ChequeNumber = $_POST['ChequeNum'];
}

if (isset($_POST['BankTransRef'])) {
	$_SESSION['PaymentDetail' . $Identifier]->BankTransRef = $_POST['BankTransRef'];
}

if (isset($_POST['Narrative'])) {
	$_SESSION['PaymentDetail' . $Identifier]->Narrative = $_POST['Narrative'];
}

echo '<p class="page_title_text" >
		<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/transactions.png" title="' . _('Bank Account Payments Entry') . '" alt="" />' . ' ' . _('Bank Account Payments Entry') . '
	</p>';
echo '<div class="page_help_text">' . _('Use this screen to enter payments FROM your bank account.  <br />Note: To enter a payment FROM a supplier, first select the Supplier, click Enter a Payment to, or Receipt from the Supplier, and use a negative Payment amount on this form.') . '</div>
	<br />';

if (isset($_GET['SupplierID'])) {
	/*The page was called with a supplierID check it is valid and default the inputs for Supplier Name and currency of payment */

	unset($_SESSION['PaymentDetail' . $Identifier]->GLItems);
	unset($_SESSION['PaymentDetail' . $Identifier]);
	$_SESSION['PaymentDetail' . $Identifier] = new Payment;
	$_SESSION['PaymentDetail' . $Identifier]->GLItemCounter = 1;


	$SQL = "SELECT suppname,
				address1,
				address2,
				address3,
				address4,
				address5,
				address6,
				currcode,
				factorcompanyid
			FROM suppliers
			WHERE supplierid='" . $_GET['SupplierID'] . "'";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 0) {
		prnMsg(_('The supplier code that this payment page was called with is not a currently defined supplier code') . '. ' . _('If this page is called from the selectSupplier page then this assures that a valid supplier is selected'), 'warn');
		include('includes/footer.inc');
		exit;
	} //DB_num_rows($Result) == 0
	else {
		$MyRow = DB_fetch_array($Result);
		if ($MyRow['factorcompanyid'] == 0) {
			$_SESSION['PaymentDetail' . $Identifier]->SuppName = $MyRow['suppname'];
			$_SESSION['PaymentDetail' . $Identifier]->Address1 = $MyRow['address1'];
			$_SESSION['PaymentDetail' . $Identifier]->Address2 = $MyRow['address2'];
			$_SESSION['PaymentDetail' . $Identifier]->Address3 = $MyRow['address3'];
			$_SESSION['PaymentDetail' . $Identifier]->Address4 = $MyRow['address4'];
			$_SESSION['PaymentDetail' . $Identifier]->Address5 = $MyRow['address5'];
			$_SESSION['PaymentDetail' . $Identifier]->Address6 = $MyRow['address6'];
			$_SESSION['PaymentDetail' . $Identifier]->SupplierID = $_GET['SupplierID'];
			$_SESSION['PaymentDetail' . $Identifier]->Currency = $MyRow['currcode'];
			$_POST['Currency'] = $_SESSION['PaymentDetail' . $Identifier]->Currency;

		} //$MyRow['factorcompanyid'] == 0
		else {
			$factorsql = "SELECT coyname,
			 					address1,
			 					address2,
			 					address3,
			 					address4,
			 					address5,
			 					address6
							FROM factorcompanies
							WHERE id='" . $MyRow['factorcompanyid'] . "'";

			$FactorResult = DB_query($factorsql);
			$myfactorrow = DB_fetch_array($FactorResult);
			$_SESSION['PaymentDetail' . $Identifier]->SuppName = $MyRow['suppname'] . ' ' . _('care of') . ' ' . $myfactorrow['coyname'];
			$_SESSION['PaymentDetail' . $Identifier]->Address1 = $myfactorrow['address1'];
			$_SESSION['PaymentDetail' . $Identifier]->Address2 = $myfactorrow['address2'];
			$_SESSION['PaymentDetail' . $Identifier]->Address3 = $myfactorrow['address3'];
			$_SESSION['PaymentDetail' . $Identifier]->Address4 = $myfactorrow['address4'];
			$_SESSION['PaymentDetail' . $Identifier]->Address5 = $myfactorrow['address5'];
			$_SESSION['PaymentDetail' . $Identifier]->Address6 = $myfactorrow['address6'];
			$_SESSION['PaymentDetail' . $Identifier]->SupplierID = $_GET['SupplierID'];
			$_SESSION['PaymentDetail' . $Identifier]->Currency = $MyRow['currcode'];
			$_POST['Currency'] = $_SESSION['PaymentDetail' . $Identifier]->Currency;
		}
		if (isset($_GET['Amount']) and is_numeric($_GET['Amount'])) {
			$_SESSION['PaymentDetail' . $Identifier]->Amount = filter_number_format($_GET['Amount']);
		} //isset($_GET['Amount']) and is_numeric($_GET['Amount'])
	}
} //isset($_GET['SupplierID'])

if (isset($_POST['BankAccount']) and $_POST['BankAccount'] != '') {
	$_SESSION['PaymentDetail' . $Identifier]->Account = $_POST['BankAccount'];
	/*Get the bank account currency and set that too */
	$ErrMsg = _('Could not get the currency of the bank account');
	$Result = DB_query("SELECT currcode,
								decimalplaces
						FROM bankaccounts INNER JOIN currencies
						ON bankaccounts.currcode = currencies.currabrev
						WHERE accountcode ='" . $_POST['BankAccount'] . "'", $ErrMsg);

	$MyRow = DB_fetch_array($Result);
	if ($_SESSION['PaymentDetail' . $Identifier]->AccountCurrency != $MyRow['currcode']) {
		//then we'd better update the functional exchange rate
		$DefaultFunctionalRate = true;
		$_SESSION['PaymentDetail' . $Identifier]->AccountCurrency = $MyRow['currcode'];
		$_SESSION['PaymentDetail' . $Identifier]->CurrDecimalPlaces = $MyRow['decimalplaces'];
	} else {
		$DefaultFunctionalRate = false;
	}
}

if (isset($_POST['DatePaid']) and $_POST['DatePaid'] != '' and is_date($_POST['DatePaid'])) {
	$_SESSION['PaymentDetail' . $Identifier]->DatePaid = $_POST['DatePaid'];
} //isset($_POST['DatePaid']) and $_POST['DatePaid'] != '' and is_date($_POST['DatePaid'])
if (isset($_POST['ExRate']) and $_POST['ExRate'] != '') {
	$_SESSION['PaymentDetail' . $Identifier]->ExRate = filter_number_format($_POST['ExRate']); //ex rate between payment currency and account currency
} //isset($_POST['ExRate']) and $_POST['ExRate'] != ''
if (isset($_POST['FunctionalExRate']) and $_POST['FunctionalExRate'] != '') {
	$_SESSION['PaymentDetail' . $Identifier]->FunctionalExRate = filter_number_format($_POST['FunctionalExRate']); //ex rate between payment currency and account currency
} //isset($_POST['FunctionalExRate']) and $_POST['FunctionalExRate'] != ''
if (isset($_POST['Paymenttype']) and $_POST['Paymenttype'] != '') {
	$_SESSION['PaymentDetail' . $Identifier]->Paymenttype = $_POST['Paymenttype'];
} //isset($_POST['Paymenttype']) and $_POST['Paymenttype'] != ''

if (isset($_POST['Currency']) and $_POST['Currency'] != '') {
	$_SESSION['PaymentDetail' . $Identifier]->Currency = $_POST['Currency']; //payment currency

	/*Get the exchange rate between the functional currency and the payment currency*/
	$Result = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['PaymentDetail' . $Identifier]->Currency . "'");
	$MyRow = DB_fetch_row($Result);
	$tableExRate = $MyRow[0]; //this is the rate of exchange between the functional currency and the payment currency

	if ($_POST['Currency'] == $_SESSION['PaymentDetail' . $Identifier]->AccountCurrency) {
		$_POST['ExRate'] = 1;
		$_SESSION['PaymentDetail' . $Identifier]->ExRate = filter_number_format($_POST['ExRate']); //ex rate between payment currency and account currency
		$SuggestedExRate = 1;
	} //$_POST['Currency'] == $_SESSION['PaymentDetail' . $Identifier]->AccountCurrency
	if ($_SESSION['PaymentDetail' . $Identifier]->AccountCurrency == $_SESSION['CompanyRecord']['currencydefault']) {
		$_POST['FunctionalExRate'] = 1;
		$_SESSION['PaymentDetail' . $Identifier]->FunctionalExRate = filter_number_format($_POST['FunctionalExRate']);
		$SuggestedExRate = $tableExRate;
		$SuggestedFunctionalExRate = 1;
		if ($DefaultFunctionalRate) {
			$_SESSION['PaymentDetail' . $Identifier]->FunctionalExRate = $SuggestedFunctionalExRate;
		}

	} //$_SESSION['PaymentDetail' . $Identifier]->AccountCurrency == $_SESSION['CompanyRecord']['currencydefault']
	else {
		/*To illustrate the rates required
		Take an example functional currency NZD payment in USD from an AUD bank account
		1 NZD = 0.80 USD
		1 NZD = 0.90 AUD
		The FunctionalExRate = 0.90 - the rate between the functional currency and the bank account currency
		The payment ex rate is the rate at which one can purchase the payment currency in the bank account currency
		or 0.8/0.9 = 0.88889
		*/

		/*Get suggested FunctionalExRate */
		$Result = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['PaymentDetail' . $Identifier]->AccountCurrency . "'");
		$MyRow = DB_fetch_row($Result);
		$SuggestedFunctionalExRate = $MyRow[0];

		/*Get the exchange rate between the functional currency and the payment currency*/
		$Result = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['PaymentDetail' . $Identifier]->Currency . "'");
		$MyRow = DB_fetch_row($Result);
		$tableExRate = $MyRow[0]; //this is the rate of exchange between the functional currency and the payment currency

		/*Calculate cross rate to suggest appropriate exchange rate between payment currency and account currency */
		if ($SuggestedFunctionalExRate != 0) {
			$SuggestedExRate = $tableExRate / $SuggestedFunctionalExRate;
		} else {
			$SuggestedExRate = 0;
		}

	}
} //isset($_POST['Currency']) and $_POST['Currency'] != ''

if (isset($_POST['BankTransRef']) and $_POST['BankTransRef'] != ''){     // Reference on Bank Transactions Inquiry
	$_SESSION['PaymentDetail' . $Identifier]->BankTransRef=$_POST['BankTransRef'];
}
if (isset($_POST['Narrative']) and $_POST['Narrative'] != '') {
	$_SESSION['PaymentDetail' . $Identifier]->Narrative = $_POST['Narrative'];
} //isset($_POST['Narrative']) and $_POST['Narrative'] != ''
if (isset($_POST['Amount']) and $_POST['Amount'] != '') {
	$_SESSION['PaymentDetail' . $Identifier]->Amount = filter_number_format($_POST['Amount']);
} //isset($_POST['Amount']) and $_POST['Amount'] != ''
else {
	if (!isset($_SESSION['PaymentDetail' . $Identifier]->Amount)) {
		$_SESSION['PaymentDetail' . $Identifier]->Amount = 0;
	} //!isset($_SESSION['PaymentDetail' . $Identifier]->Amount)
}
if (isset($_POST['Discount']) and $_POST['Discount'] != '') {
	$_SESSION['PaymentDetail' . $Identifier]->Discount = filter_number_format($_POST['Discount']);
} //isset($_POST['Discount']) and $_POST['Discount'] != ''
else {
	if (!isset($_SESSION['PaymentDetail' . $Identifier]->Discount)) {
		$_SESSION['PaymentDetail' . $Identifier]->Discount = 0;
	} //!isset($_SESSION['PaymentDetail' . $Identifier]->Discount)
}


if (isset($_POST['CommitBatch'])) {
	/* once the GL analysis of the payment is entered (if the Creditors_GLLink is active),
	process all the data in the session cookie into the DB creating a banktrans record for
	the payment in the batch and SuppTrans record for the supplier payment if a supplier was selected
	A GL entry is created for each GL entry (only one for a supplier entry) and one for the bank
	account credit.

	NB allocations against supplier payments are a separate exercice

	if GL integrated then
	first off run through the array of payment items $_SESSION['Payment']->GLItems and
	create GL Entries for the GL payment items
	*/

	/*First off  check we have an amount entered as paid ?? */
	$TotalAmount = 0;
	foreach ($_SESSION['PaymentDetail' . $Identifier]->GLItems as $PaymentItem) {
		$TotalAmount += $PaymentItem->Amount;
	} //$_SESSION['PaymentDetail' . $Identifier]->GLItems as $PaymentItem

	if ($TotalAmount == 0 and ($_SESSION['PaymentDetail' . $Identifier]->Discount + $_SESSION['PaymentDetail' . $Identifier]->Amount) / $_SESSION['PaymentDetail' . $Identifier]->ExRate == 0) {
		prnMsg(_('This payment has no amounts entered and will not be processed'), 'warn');
		include('includes/footer.inc');
		exit;
	} //$TotalAmount == 0 and ($_SESSION['PaymentDetail' . $Identifier]->Discount + $_SESSION['PaymentDetail' . $Identifier]->Amount) / $_SESSION['PaymentDetail' . $Identifier]->ExRate == 0

	if ($_POST['BankAccount'] == '') {
		prnMsg(_('No bank account has been selected so this payment cannot be processed'), 'warn');
		include('includes/footer.inc');
		exit;
	} //$_POST['BankAccount'] == ''

	/*Make an array of the defined bank accounts */
	$SQL = "SELECT bankaccounts.accountcode
			FROM bankaccounts,
				chartmaster
			WHERE bankaccounts.accountcode=chartmaster.accountcode";
	$Result = DB_query($SQL);
	$BankAccounts = array();
	$i = 0;

	while ($Act = DB_fetch_row($Result)) {
		$BankAccounts[$i] = $Act[0];
		++$i;
	} //$Act = DB_fetch_row($Result)

	$PeriodNo = GetPeriod($_SESSION['PaymentDetail' . $Identifier]->DatePaid);

	$SQL = "SELECT usepreprintedstationery
			FROM paymentmethods
			WHERE paymentname='" . $_SESSION['PaymentDetail' . $Identifier]->Paymenttype . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	// first time through commit if supplier cheque then print it first
	if ((!isset($_POST['ChequePrinted'])) and (!isset($_POST['PaymentCancelled'])) and ($MyRow['usepreprintedstationery'] == 1)) {
		// it is a supplier payment by cheque and haven't printed yet so print cheque

		echo '<a href="' . $RootPath . '/PrintCheque.php?ChequeNum=' . urlencode($_SESSION['PaymentDetail' . $Identifier]->ChequeNumber) . '&amp;identifier=' . urlencode($Identifier) . '">' . _('Print Cheque using pre-printed stationery') . '</a>';

		echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'] . '?identifier=' . $Identifier) . '">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo _('Has the cheque been printed') . '?
			<br />
			<br />
			<input type="hidden" name="CommitBatch" value="' . $_POST['CommitBatch'] . '" />
			<input type="hidden" name="BankAccount" value="' . $_POST['BankAccount'] . '" />
			<input type="submit" name="ChequePrinted" value="' . _('Yes / Continue') . '" />&nbsp;&nbsp;
			<input type="submit" name="PaymentCancelled" value="' . _('No / Cancel Payment') . '" />';

		echo '<br />Payment amount = ' . $_SESSION['PaymentDetail' . $Identifier]->Amount;
		echo '</form>';

	} //(!isset($_POST['ChequePrinted'])) and (!isset($_POST['PaymentCancelled'])) and ($MyRow[0] == 1)
	else {
		//Start a transaction to do the whole lot inside

		$Result = DB_Txn_Begin();


		if ($_SESSION['PaymentDetail' . $Identifier]->SupplierID == '') {
			//its a nominal bank transaction type 1

			$TransNo = GetNextTransNo(1);
			$TransType = 1;

			if ($_SESSION['CompanyRecord']['gllink_creditors'] == 1) {
				/* then enter GLTrans */
				$TotalAmount = 0;
				foreach ($_SESSION['PaymentDetail' . $Identifier]->GLItems as $PaymentItem) {
					/*The functional currency amount will be the
					payment currenct amount  / the bank account currency exchange rate  - to get to the bank account currency
					then / the functional currency exchange rate to get to the functional currency */
					if ($PaymentItem->Cheque == '')
						$PaymentItem->Cheque = 0;
					$SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount,
												chequeno,
												tag) ";
					$SQL = $SQL . "VALUES (1,
						'" . $TransNo . "',
						'" . FormatDateForSQL($_SESSION['PaymentDetail' . $Identifier]->DatePaid) . "',
						'" . $PeriodNo . "',
						'" . $PaymentItem->GLCode . "',
						'" . $PaymentItem->Narrative . "',
						'" . ($PaymentItem->Amount / $_SESSION['PaymentDetail' . $Identifier]->ExRate / $_SESSION['PaymentDetail' . $Identifier]->FunctionalExRate) . "',
						'" . $PaymentItem->Cheque . "',
						'" . $PaymentItem->Tag . "'
						)";
					$ErrMsg = _('Cannot insert a GL entry for the payment using the SQL');
					$Result = DB_query($SQL, $ErrMsg, _('The SQL that failed was'), true);

					$TotalAmount += $PaymentItem->Amount;
				} //$_SESSION['PaymentDetail' . $Identifier]->GLItems as $PaymentItem
				$_SESSION['PaymentDetail' . $Identifier]->Amount = $TotalAmount;
				$_SESSION['PaymentDetail' . $Identifier]->Discount = 0;
			} //$_SESSION['CompanyRecord']['gllink_creditors'] == 1

			//Run through the GL postings to check to see if there is a posting to another bank account (or the same one) if there is then a receipt needs to be created for this account too

			foreach ($_SESSION['PaymentDetail' . $Identifier]->GLItems as $PaymentItem) {
				if (in_array($PaymentItem->GLCode, $BankAccounts)) {
					/*Need to deal with the case where the payment from one bank account could be to a bank account in another currency */

					/*Get the currency and rate of the bank account transferring to*/
					$SQL = "SELECT currcode, rate
							FROM bankaccounts INNER JOIN currencies
							ON bankaccounts.currcode = currencies.currabrev
							WHERE accountcode='" . $PaymentItem->GLCode . "'";
					$TrfToAccountResult = DB_query($SQL);
					$TrfToBankRow = DB_fetch_array($TrfToAccountResult);
					$TrfToBankCurrCode = $TrfToBankRow['currcode'];
					$TrfToBankExRate = $TrfToBankRow['rate'];

					if ($_SESSION['PaymentDetail' . $Identifier]->AccountCurrency == $TrfToBankCurrCode) {
						/*Make sure to use the same rate if the transfer is between two bank accounts in the same currency */
						$TrfToBankExRate = $_SESSION['PaymentDetail' . $Identifier]->FunctionalExRate;
					} //$_SESSION['PaymentDetail' . $Identifier]->AccountCurrency == $TrfToBankCurrCode

					/*Consider an example
					functional currency NZD
					bank account in AUD - 1 NZD = 0.90 AUD (FunctionalExRate)
					paying USD - 1 AUD = 0.85 USD  (ExRate)
					to a bank account in EUR - 1 NZD = 0.52 EUR

					oh yeah - now we are getting tricky!
					Lets say we pay USD 100 from the AUD bank account to the EUR bank account

					To get the ExRate for the bank account we are transferring money to
					we need to use the cross rate between the NZD-AUD/NZD-EUR
					and apply this to the

					the payment record will read
					exrate = 0.85 (1 AUD = USD 0.85)
					amount = 100 (USD)
					functionalexrate = 0.90 (1 NZD = AUD 0.90)

					the receipt record will read

					amount 100 (USD)
					exrate    (1 EUR =  (0.85 x 0.90)/0.52 USD)
					(ExRate x FunctionalExRate) / USD Functional ExRate
					functionalexrate =     (1NZD = EUR 0.52)

					*/

					$ReceiptTransNo = GetNextTransNo(2);
					$SQL = "INSERT INTO banktrans (transno,
													type,
													bankact,
													ref,
													chequeno,
													exrate,
													functionalexrate,
													transdate,
													banktranstype,
													amount,
													currcode,
													userid
												) VALUES (
													'" . $ReceiptTransNo . "',
													2,
													'" . $PaymentItem->GLCode . "',
													'" . _('Act Transfer From ') . $_SESSION['PaymentDetail' . $Identifier]->Account . ' - ' . $PaymentItem->Narrative . "',
													'" . $PaymentItem->Cheque . "',
													'" . (($_SESSION['PaymentDetail' . $Identifier]->ExRate * $_SESSION['PaymentDetail' . $Identifier]->FunctionalExRate) / $TrfToBankExRate) . "',
													'" . $TrfToBankExRate . "',
													'" . FormatDateForSQL($_SESSION['PaymentDetail' . $Identifier]->DatePaid) . "',
													'" . $_SESSION['PaymentDetail' . $Identifier]->Paymenttype . "',
													'" . $PaymentItem->Amount . "',
													'" . $_SESSION['PaymentDetail' . $Identifier]->Currency . "',
													'" . $_SESSION['UserID'] . "'
												)";
					$ErrMsg = _('Cannot insert a bank transaction because');
					$DbgMsg = _('Cannot insert a bank transaction with the SQL');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

				} //in_array($PaymentItem->GLCode, $BankAccounts)
			} //$_SESSION['PaymentDetail' . $Identifier]->GLItems as $PaymentItem
		} else {

			/* Get an array of suuptans id fields that were paid */
			$PaidArray = array();
			foreach ($_POST as $name=>$Value) {
				if (substr($name, 0, 4) == 'paid') {
					$PaidArray[substr($name, 4)] = $Value;
				}
			}
			/*Its a supplier payment type 22 */
			$CreditorTotal = (($_SESSION['PaymentDetail' . $Identifier]->Discount + $_SESSION['PaymentDetail' . $Identifier]->Amount) / $_SESSION['PaymentDetail' . $Identifier]->ExRate) / $_SESSION['PaymentDetail' . $Identifier]->FunctionalExRate;

			$TransNo = GetNextTransNo(22);
			$TransType = 22;

			/* Create a SuppTrans entry for the supplier payment */
			$SQL = "INSERT INTO supptrans (transno,
											type,
											supplierno,
											trandate,
											inputdate,
											suppreference,
											rate,
											ovamount,
											transtext) ";
			$SQL = $SQL . "VALUES ('" . $TransNo . "',
					22,
					'" . $_SESSION['PaymentDetail' . $Identifier]->SupplierID . "',
					'" . FormatDateForSQL($_SESSION['PaymentDetail' . $Identifier]->DatePaid) . "',
					'" . date('Y-m-d H-i-s') . "',
					'" . $_SESSION['PaymentDetail' . $Identifier]->Paymenttype . "',
					'" . ($_SESSION['PaymentDetail' . $Identifier]->FunctionalExRate / $_SESSION['PaymentDetail' . $Identifier]->ExRate) . "',
					'" . (-$_SESSION['PaymentDetail' . $Identifier]->Amount - $_SESSION['PaymentDetail' . $Identifier]->Discount) . "',
					'" . $_SESSION['PaymentDetail' . $Identifier]->Narrative . "'
				)";

			$ErrMsg = _('Cannot insert a payment transaction against the supplier because');
			$DbgMsg = _('Cannot insert a payment transaction against the supplier using the SQL');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			$SQL = "SELECT id FROM supptrans WHERE transno='" . $TransNo . "' AND type=22";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_array($Result);
			$PaymentID = $MyRow['id'];

			if (sizeof($PaidArray) > 0) {
				foreach ($PaidArray as $PaidID=>$PaidAmount) {
					/* Firstly subtract from the payment the amount of the invoice  */
					$SQL = "UPDATE supptrans SET alloc=alloc-" . $PaidAmount . " WHERE id='" . $PaymentID . "'";
					$ErrMsg = _('Cannot update an allocation against the supplier because');
					$DbgMsg = _('Cannot update an allocation against the supplier using the SQL');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
					/* Then add theamount of the invoice to the invoice allocation */
					$SQL = "UPDATE supptrans SET alloc=alloc+" . $PaidAmount . " WHERE id='" . $PaidID . "'";
					$ErrMsg = _('Cannot update an allocation against the supplier because');
					$DbgMsg = _('Cannot update an allocation against the supplier using the SQL');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
					/* Finally update the supplier allocations table */
					$SQL = "INSERT INTO suppallocs (amt,
													datealloc,
													transid_allocfrom,
													transid_allocto
												) VALUES (
													'" . $PaidAmount . "',
													'" . FormatDateForSQL($_SESSION['PaymentDetail' . $Identifier]->DatePaid) . "',
													'" . $PaymentID . "',
													'" . $PaidID . "'
												)";
					$ErrMsg = _('Cannot update an allocation against the supplier because');
					$DbgMsg = _('Cannot update an allocation against the supplier using the SQL');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				}
			}

			/*Update the supplier master with the date and amount of the last payment made */
			$SQL = "UPDATE suppliers
					SET	lastpaiddate = '" . FormatDateForSQL($_SESSION['PaymentDetail' . $Identifier]->DatePaid) . "',
						lastpaid='" . $_SESSION['PaymentDetail' . $Identifier]->Amount . "'
					WHERE suppliers.supplierid='" . $_SESSION['PaymentDetail' . $Identifier]->SupplierID . "'";



			$ErrMsg = _('Cannot update the supplier record for the date of the last payment made because');
			$DbgMsg = _('Cannot update the supplier record for the date of the last payment made using the SQL');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			$_SESSION['PaymentDetail' . $Identifier]->Narrative = $_SESSION['PaymentDetail' . $Identifier]->SupplierID . ' - ' . $_SESSION['PaymentDetail' . $Identifier]->Narrative;

			if ($_SESSION['CompanyRecord']['gllink_creditors'] == 1) {
				/* then do the supplier control GLTrans */
				/* Now debit creditors account with payment + discount */

				$SQL = "INSERT INTO gltrans ( type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount) ";
				$SQL = $SQL . "VALUES (22,
								'" . $TransNo . "',
								'" . FormatDateForSQL($_SESSION['PaymentDetail' . $Identifier]->DatePaid) . "',
								'" . $PeriodNo . "',
								'" . $_SESSION['CompanyRecord']['creditorsact'] . "',
								'" . $_SESSION['PaymentDetail' . $Identifier]->Narrative . "',
								'" . $CreditorTotal . "')";
				$ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
				$DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

				if ($_SESSION['PaymentDetail' . $Identifier]->Discount != 0) {
					/* Now credit Discount received account with discounts */
					$SQL = "INSERT INTO gltrans ( type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount)
								VALUES (22,
										'" . $TransNo . "',
										'" . FormatDateForSQL($_SESSION['PaymentDetail' . $Identifier]->DatePaid) . "',
										'" . $PeriodNo . "',
										'" . $_SESSION['CompanyRecord']['pytdiscountact'] . "',
										'" . $_SESSION['PaymentDetail' . $Identifier]->Narrative . "',
										'" . (-$_SESSION['PaymentDetail' . $Identifier]->Discount / $_SESSION['PaymentDetail' . $Identifier]->ExRate / $_SESSION['PaymentDetail' . $Identifier]->FunctionalExRate) . "'
					  )";
					$ErrMsg = _('Cannot insert a GL transaction for the payment discount credit because');
					$DbgMsg = _('Cannot insert a GL transaction for the payment discount credit using the SQL');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				} // end if discount

			} // end if gl creditors
		} // end if supplier

		if ($_SESSION['CompanyRecord']['gllink_creditors'] == 1) {
			/* then do the common GLTrans */

			if ($_SESSION['PaymentDetail' . $Identifier]->Amount != 0) {
				/* Bank account entry first */
				$SQL = "INSERT INTO gltrans ( type,
											typeno,
											trandate,
											periodno,
											chequeno,
											account,
											narrative,
											amount)
									VALUES ('" . $TransType . "',
											'" . $TransNo . "',
											'" . FormatDateForSQL($_SESSION['PaymentDetail' . $Identifier]->DatePaid) . "',
											'" . $PeriodNo . "',
											" . isset($PaymentItem->Cheque) ? $PaymentItem->Cheque : 0 . ",
											'" . $_SESSION['PaymentDetail' . $Identifier]->Account . "',
											'" . $_SESSION['PaymentDetail' . $Identifier]->Narrative . "',
											'" . ($_SESSION['PaymentDetail' . $Identifier]>Amount / $_SESSION['PaymentDetail' . $Identifier]->ExRate / $_SESSION['PaymentDetail' . $Identifier]->FunctionalExRate) . "')";

				$ErrMsg = _('Cannot insert a GL transaction for the bank account credit because');
				$DbgMsg = _('Cannot insert a GL transaction for the bank account credit using the SQL');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

				EnsureGLEntriesBalance($TransType, $TransNo);
			} //$_SESSION['PaymentDetail' . $Identifier]->Amount != 0
		} //$_SESSION['CompanyRecord']['gllink_creditors'] == 1

		/*now enter the BankTrans entry */
		if ($TransType == 22) {
			$SQL = "INSERT INTO banktrans (transno,
										type,
										bankact,
										ref,
										chequeno,
										exrate,
										functionalexrate,
										transdate,
										banktranstype,
										amount,
										currcode,
										userid
									) VALUES (
										'" . $TransNo . "',
										'" . $TransType . "',
										'" . $_SESSION['PaymentDetail' . $Identifier]->Account . "',
										'" . $_SESSION['PaymentDetail' . $Identifier]->BankTransRef . "',
										'" . $_POST['Cheque'] . "',
										'" . $_SESSION['PaymentDetail' . $Identifier]->ExRate . "',
										'" . $_SESSION['PaymentDetail' . $Identifier]->FunctionalExRate . "',
										'" . FormatDateForSQL($_SESSION['PaymentDetail' . $Identifier]->DatePaid) . "',
										'" . $_SESSION['PaymentDetail' . $Identifier]->Paymenttype . "',
										'" . -$_SESSION['PaymentDetail' . $Identifier]->Amount . "',
										'" . $_SESSION['PaymentDetail' . $Identifier]->Currency . "',
										'" . $_SESSION['UserID'] . "'
									)";

			$ErrMsg = _('Cannot insert a bank transaction because');
			$DbgMsg = _('Cannot insert a bank transaction using the SQL');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
		} //$TransType == 22
		else {
			$SQL = "INSERT INTO banktrans (transno,
										type,
										bankact,
										ref,
										chequeno,
										exrate,
										functionalexrate,
										transdate,
										banktranstype,
										amount,
										currcode,
										userid
									) VALUES (
										'" . $TransNo . "',
										'" . $TransType . "',
										'" . $_SESSION['PaymentDetail' . $Identifier]->Account . "',
										'" . $_SESSION['PaymentDetail' . $Identifier]->BankTransRef . "',
										'" . $PaymentItem->Cheque . "',
										'" . $_SESSION['PaymentDetail' . $Identifier]->ExRate . "',
										'" . $_SESSION['PaymentDetail' . $Identifier]->FunctionalExRate . "',
										'" . FormatDateForSQL($_SESSION['PaymentDetail' . $Identifier]->DatePaid) . "',
										'" . $_SESSION['PaymentDetail' . $Identifier]->Paymenttype . "',
										'" . -$_SESSION['PaymentDetail' . $Identifier]->Amount . "',
										'" . $_SESSION['PaymentDetail' . $Identifier]->Currency . "',
										'" . $_SESSION['UserID'] . "'
									)";

			$ErrMsg = _('Cannot insert a bank transaction because');
			$DbgMsg = _('Cannot insert a bank transaction using the SQL');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
		}

		DB_Txn_Commit();
		prnMsg(_('Payment') . ' ' . $TransNo . ' ' . _('has been successfully entered'), 'success');

		$LastSupplier = ($_SESSION['PaymentDetail' . $Identifier]->SupplierID);

		unset($_POST['BankAccount']);
		unset($_POST['DatePaid']);
		unset($_POST['ExRate']);
		unset($_POST['Paymenttype']);
		unset($_POST['Currency']);
		unset($_POST['Narrative']);
		unset($_POST['Amount']);
		unset($_POST['Discount']);
		unset($_SESSION['PaymentDetail' . $Identifier]->GLItems);
		unset($_SESSION['PaymentDetail' . $Identifier]);

		/*Set up a newy in case user wishes to enter another */
		if (isset($LastSupplier) and $LastSupplier != '') {
			$SupplierSQL = "SELECT suppname FROM suppliers
					WHERE supplierid='" . $LastSupplier . "'";
			$SupplierResult = DB_query($SupplierSQL);
			$SupplierRow = DB_fetch_array($SupplierResult);
			$IdSQL = "SELECT id FROM supptrans WHERE type=22 AND transno='" . $TransNo . "'";
			$IdResult = DB_query($IdSQL);
			$IdRow = DB_fetch_array($IdResult);
			if (sizeof($PaidArray) == 0) {
				echo '<a href="' . $RootPath . '/SupplierAllocations.php?AllocTrans=' . urlencode($IdRow['id']) . '">' . _('Allocate this payment') . '</a>';
			}
			echo '<a href="' . $RootPath . '/Payments.php?SupplierID=' . urlencode($LastSupplier) . '">' . _('Enter another Payment for') . ' ' . $SupplierRow['suppname'] . '</a>';
		} else {
			echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Enter another General Ledger Payment') . '</a><br />';
		}
	}

	include('includes/footer.inc');
	exit;

} elseif (isset($_GET['Delete'])) {
	/* User hit delete the receipt entry from the batch */
	$_SESSION['PaymentDetail' . $Identifier]->Remove_GLItem($_GET['Delete']);
	//recover the bank account relative setting
	$_POST['BankAccount'] = $_SESSION['PaymentDetail' . $Identifier]->Account;
	$_POST['DatePaid'] = $_SESSION['PaymentDetail' . $Identifier]->DatePaid;
	$_POST['Currency'] = $_SESSION['PaymentDetail' . $Identifier]->Currency;
	$_POST['ExRate'] = $_SESSION['PaymentDetail' . $Identifier]->ExRate;
	$_POST['FunctionalExRate'] = $_SESSION['PaymentDetail' . $Identifier]->FunctionalExRate;
	$_POST['PaymentType'] = $_SESSION['PaymentDetail' . $Identifier]->Paymenttype;
	$_POST['BankTransRef'] = $_SESSION['PaymentDetail' . $Identifier]->BankTransRef;
	$_POST['Narrative'] = $_SESSION['PaymentDetail' . $Identifier]->Narrative;

} elseif (isset($_POST['Process']) and !$BankAccountEmpty) { //user hit submit a new GL Analysis line into the payment

	$ChequeNoSQL = "SELECT account FROM gltrans WHERE chequeno='" . $_POST['Cheque'] . "'";
	$ChequeNoResult = DB_query($ChequeNoSQL);

	if (is_numeric($_POST['GLManualCode'])) {
		$SQL = "SELECT accountname
				FROM chartmaster
				WHERE accountcode='" . $_POST['GLManualCode'] . "'
					AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'";

		$Result = DB_query($SQL);

		if (DB_num_rows($Result) == 0) {
			prnMsg(_('The manual GL code entered does not exist in the database') . ' - ' . _('so this GL analysis item could not be added'), 'warn');
			unset($_POST['GLManualCode']);
		} else if (DB_num_rows($ChequeNoResult) != 0 and $_POST['Cheque'] != '') {
			prnMsg(_('The Cheque/Voucher number has already been used') . ' - ' . _('This GL analysis item could not be added'), 'error');
		} else {
			$MyRow = DB_fetch_array($Result);
			$AllowThisPosting = true;
			if ($_SESSION['ProhibitJournalsToControlAccounts'] == 1) {
				if ($_SESSION['CompanyRecord']['gllink_debtors'] == '1' and $_POST['GLManualCode'] == $_SESSION['CompanyRecord']['debtorsact']) {
					prnMsg(_('Payments involving the debtors control account cannot be entered. The general ledger debtors ledger (AR) integration is enabled so control accounts are automatically maintained. This setting can be disabled in System Configuration'), 'warn');
					$AllowThisPosting = false;
				}
				if ($_SESSION['CompanyRecord']['gllink_creditors'] == '1' and $_POST['GLManualCode'] == $_SESSION['CompanyRecord']['creditorsact']) {
					prnMsg(_('Payments involving the creditors control account cannot be entered. The general ledger creditors ledger (AP) integration is enabled so control accounts are automatically maintained. This setting can be disabled in System Configuration'), 'warn');
					$AllowThisPosting = false;
				}
				if ($_POST['GLCode'] == $_SESSION['CompanyRecord']['retainedearnings']) {
					prnMsg(_('Payments involving the retained earnings control account cannot be entered. This account is automtically maintained.'), 'warn');
					$AllowThisPosting = false;
				}
			}
			if ($AllowThisPosting) {
				$_SESSION['PaymentDetail' . $Identifier]->add_to_glanalysis(filter_number_format($_POST['GLAmount']), $_POST['GLNarrative'], $_POST['GLManualCode'], $MyRow['accountname'], $_POST['Tag'], $_POST['Cheque']);
			}
			unset($_POST['GLManualCode']);
		}
	} //is_numeric($_POST['GLManualCode'])
	else if (DB_num_rows($ChequeNoResult) != 0 and $_POST['Cheque'] != '') {
		prnMsg(_('The cheque number has already been used') . ' - ' . _('This GL analysis item could not be added'), 'error');
	} //DB_num_rows($ChequeNoResult) != 0 and $_POST['Cheque'] != ''
	else if ($_POST['GLCode'] == '') {
		prnMsg(_('No General Ledger code has been chosen') . ' - ' . _('so this GL analysis item could not be added'), 'warn');
	} //$_POST['GLCode'] == ''
	else {
		$SQL = "SELECT accountname
					FROM chartmaster
					WHERE accountcode='" . $_POST['GLCode'] . "'
						AND language='" . $_SESSION['ChartLanguage'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);
		$_SESSION['PaymentDetail' . $Identifier]->add_to_glanalysis(filter_number_format($_POST['GLAmount']), $_POST['GLNarrative'], $_POST['GLCode'], $MyRow['accountname'], $_POST['Tag'], $_POST['Cheque']);
	}

	/*Make sure the same receipt is not double processed by a page refresh */
	$_POST['Cancel'] = 1;
} //isset($_POST['Process']) and !$BankAccountEmpty

if (isset($_POST['Cancel'])) {
	unset($_POST['GLAmount']);
	unset($_POST['GLNarrative']);
	unset($_POST['GLCode']);
	unset($_POST['AccountName']);
} //isset($_POST['Cancel'])

if (isset($_POST['DatePaid']) and ($_POST['DatePaid'] == '' or !is_date($_SESSION['PaymentDetail' . $Identifier]->DatePaid))) {
	$_POST['DatePaid'] = Date($_SESSION['DefaultDateFormat']);
	$_SESSION['PaymentDetail' . $Identifier]->DatePaid = $_POST['DatePaid'];
} //isset($_POST['DatePaid']) and ($_POST['DatePaid'] == '' or !is_date($_SESSION['PaymentDetail' . $Identifier]->DatePaid))

if ($_SESSION['PaymentDetail' . $Identifier]->Currency == '' and $_SESSION['PaymentDetail' . $Identifier]->SupplierID == '') {
	$_SESSION['PaymentDetail' . $Identifier]->Currency = $_SESSION['CompanyRecord']['currencydefault'];
} //$_SESSION['PaymentDetail' . $Identifier]->Currency == '' and $_SESSION['PaymentDetail' . $Identifier]->SupplierID == ''


if (isset($_POST['BankAccount']) and $_POST['BankAccount'] != '') {
	$SQL = "SELECT bankaccountname
			FROM bankaccounts,
				chartmaster
			WHERE bankaccounts.accountcode= chartmaster.accountcode
				AND chartmaster.accountcode='" . $_POST['BankAccount'] . "'
				AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'";

	$ErrMsg = _('The bank account name cannot be retrieved because');
	$DbgMsg = _('SQL used to retrieve the bank account name was');

	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

	if (DB_num_rows($Result) == 1) {
		$MyRow = DB_fetch_row($Result);
		$_SESSION['PaymentDetail' . $Identifier]->BankAccountName = $MyRow[0];
		unset($Result);
	} //DB_num_rows($Result) == 1
	elseif (DB_num_rows($Result) == 0) {
		prnMsg(_('The bank account number') . ' ' . $_POST['BankAccount'] . ' ' . _('is not set up as a bank account with a valid general ledger account'), 'error');
	} //DB_num_rows($Result) == 0
} //isset($_POST['BankAccount']) and $_POST['BankAccount'] != ''

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'] . '?identifier=' . $Identifier) . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<br /><table class="selection">';

echo '<tr>
		<th colspan="4"><h3>' . _('Payment');

if ($_SESSION['PaymentDetail' . $Identifier]->SupplierID != '') {
	echo ' ' . _('to') . ' ' . $_SESSION['PaymentDetail' . $Identifier]->SuppName;
} //$_SESSION['PaymentDetail' . $Identifier]->SupplierID != ''

if ($_SESSION['PaymentDetail' . $Identifier]->BankAccountName != '') {
	echo ' ' . _('from the') . ' ' . $_SESSION['PaymentDetail' . $Identifier]->BankAccountName;
} //$_SESSION['PaymentDetail' . $Identifier]->BankAccountName != ''

echo ' ' . _('on') . ' ' . $_SESSION['PaymentDetail' . $Identifier]->DatePaid . '</h3></th></tr>';

$SQL = "SELECT bankaccountname,
				bankaccounts.accountcode,
				bankaccounts.currcode
			FROM bankaccounts
			INNER JOIN chartmaster
				ON bankaccounts.accountcode=chartmaster.accountcode
			INNER JOIN bankaccountusers
				ON bankaccounts.accountcode=bankaccountusers.accountcode
			WHERE bankaccountusers.userid = '" . $_SESSION['UserID'] . "'
				AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
			ORDER BY bankaccountname";

$ErrMsg = _('The bank accounts could not be retrieved because');
$DbgMsg = _('The SQL used to retrieve the bank accounts was');
$AccountsResults = DB_query($SQL, $ErrMsg, $DbgMsg);

echo '<tr>
		<td>' . _('Bank Account') . ':</td>
		<td><select autofocus="autofocus" required="required" name="BankAccount" onchange="ReloadForm(UpdateHeader)">';

if (DB_num_rows($AccountsResults) == 0) {
	echo '</select></td>
		</tr>
		</table>';
	prnMsg(_('Bank Accounts have not yet been defined. You must first') . ' <a href="' . $RootPath . '/BankAccounts.php">' . _('define the bank accounts') . '</a> ' . _('and general ledger accounts to be affected'), 'warn');
	include('includes/footer.inc');
	exit;
} else {
	echo '<option value=""></option>';
	while ($MyRow = DB_fetch_array($AccountsResults)) {
		/*list the bank account names */
		if (isset($_SESSION['PaymentDetail' . $Identifier]->Account) and $_SESSION['PaymentDetail' . $Identifier]->Account == $MyRow['accountcode']) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] . '">' . $MyRow['bankaccountname'] . ' - ' . $MyRow['currcode'] . '</option>';
		} //isset($_POST['BankAccount']) and $_POST['BankAccount'] == $MyRow['accountcode']
		else {
			echo '<option value="' . $MyRow['accountcode'] . '">' . $MyRow['bankaccountname'] . ' - ' . $MyRow['currcode'] . '</option>';
		}
	} //$MyRow = DB_fetch_array($AccountsResults)
	echo '</select></td>
		</tr>';
}

echo '<tr>
		<td>' . _('Date Paid') . ':</td>
		<td><input type="text" name="DatePaid" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" required="required" maxlength="10" size="11" onchange="isDate(this, this.value, ' . "'" . $_SESSION['DefaultDateFormat'] . "'" . ')" value="' . $_SESSION['PaymentDetail' . $Identifier]->DatePaid . '" /></td>
	</tr>';


if ($_SESSION['PaymentDetail' . $Identifier]->SupplierID == '') {
	echo '<tr>
			<td>' . _('Currency of Payment') . ':</td>
			<td><select required="required" name="Currency" onchange="ReloadForm(UpdateHeader)">';
	$SQL = "SELECT currency, currabrev, rate FROM currencies";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) == 0) {
		echo '</select></td>
			</tr>';
		prnMsg(_('No currencies are defined yet. Payments cannot be entered until a currency is defined'), 'error');
	} else {
		while ($MyRow = DB_fetch_array($Result)) {
			if ($_SESSION['PaymentDetail' . $Identifier]->Currency == $MyRow['currabrev']) {
				echo '<option selected="selected" value="' . $MyRow['currabrev'] . '">' . _($MyRow['currency']) . '</option>';
			} //$_SESSION['PaymentDetail' . $Identifier]->Currency == $MyRow['currabrev']
			else {
				echo '<option value="' . $MyRow['currabrev'] . '">' . _($MyRow['currency']) . '</option>';
			}
		} //$MyRow = DB_fetch_array($Result)
		echo '</select></td>
				<td><i>' . _('The transaction currency does not need to be the same as the bank account currency') . '</i></td>
			</tr>';
	}
} else {
	/*its a supplier payment so it must be in the suppliers currency */
	echo '<tr>';
	echo '<td><input type="hidden" name="Currency" value="' . $_SESSION['PaymentDetail' . $Identifier]->Currency . '" />
			' . _('Supplier Currency') . ':</td>
			<td>' . $_SESSION['PaymentDetail' . $Identifier]->Currency . '</td>
		</tr>';
	/*get the default rate from the currency table if it has not been set */
	if (!isset($_SESSION['PaymentDetail' . $Identifier]->ExRate) or $_SESSION['PaymentDetail' . $Identifier]->ExRate == '') {
		$SQL = "SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['PaymentDetail' . $Identifier]->Currency . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		$_SESSION['PaymentDetail' . $Identifier]->ExRate = locale_number_format($MyRow[0], 'Variable');
	} //!isset($_POST['ExRate']) or $_POST['ExRate'] == ''
}

if ($_SESSION['PaymentDetail' . $Identifier]->AccountCurrency != $_SESSION['PaymentDetail' . $Identifier]->Currency and isset($_SESSION['PaymentDetail' . $Identifier]->AccountCurrency)) {
	if (isset($SuggestedExRate)) {
		$SuggestedExRateText = '<b>' . _('Suggested rate') . ': ' . locale_number_format($SuggestedExRate, 8) . '</b>';
	} else {
		$SuggestedExRateText = '';
	}
	if ($_SESSION['PaymentDetail' . $Identifier]->ExRate == 1 and isset($SuggestedExRate)) {
		$_SESSION['PaymentDetail' . $Identifier]->ExRate = locale_number_format($SuggestedExRate, 8);
	} elseif (isset($_POST['PreviousCurrency']) and ($_POST['Currency'] != $_POST['PreviousCurrency'] and isset($SuggestedExRate))) {
		$_SESSION['PaymentDetail' . $Identifier]->ExRate = locale_number_format($SuggestedExRate, 8);

	} //$_POST['Currency'] != $_POST['PreviousCurrency'] and isset($SuggestedExRate)
	echo '<tr>
			<td>' . _('Payment Exchange Rate') . ':</td>
			<td><input class="number" type="text" name="ExRate" required="required" maxlength="12" size="14" value="' . $_SESSION['PaymentDetail' . $Identifier]->ExRate . '" /></td>
			<td>' . $SuggestedExRateText . ' <i>' . _('The exchange rate between the currency of the bank account currency and the currency of the payment') . '. 1 ' . $_SESSION['PaymentDetail' . $Identifier]->AccountCurrency . ' = ? ' . $_SESSION['PaymentDetail' . $Identifier]->Currency . '</i></td>
		</tr>';
} //$_SESSION['PaymentDetail' . $Identifier]->AccountCurrency != $_SESSION['PaymentDetail' . $Identifier]->Currency and isset($_SESSION['PaymentDetail' . $Identifier]->AccountCurrency)

if ($_SESSION['PaymentDetail' . $Identifier]->AccountCurrency != $_SESSION['CompanyRecord']['currencydefault'] and isset($_SESSION['PaymentDetail' . $Identifier]->AccountCurrency)) {
	if (isset($SuggestedFunctionalExRate)) {
		$SuggestedFunctionalExRateText = '<b>' . _('Suggested rate') . ': ' . locale_number_format($SuggestedFunctionalExRate, 8) . '</b>';
	} else {
		$SuggestedFunctionalExRateText = '';
	}
	if ($_SESSION['PaymentDetail' . $Identifier]->FunctionalExRate == 1 and isset($SuggestedFunctionalExRate)) {
		$_SESSION['PaymentDetail' . $Identifier]->FunctionalExRate = locale_number_format($SuggestedFunctionalExRate, 'Variable');
	} //$_POST['FunctionalExRate'] == 1 and isset($SuggestedFunctionalExRate)
	echo '<tr>
			<td>' . _('Functional Exchange Rate') . ':</td>
			<td><input type="text" class="number" name="FunctionalExRate" required="required" maxlength="12" size="14" value="' . $_SESSION['PaymentDetail' . $Identifier]->FunctionalExRate . '" /></td>
			<td>' . ' ' . $SuggestedFunctionalExRateText . ' <i>' . _('The exchange rate between the currency of the business (the functional currency) and the currency of the bank account') . '. 1 ' . $_SESSION['CompanyRecord']['currencydefault'] . ' = ? ' . $_SESSION['PaymentDetail' . $Identifier]->AccountCurrency . '</i></td>
		</tr>';
} //$_SESSION['PaymentDetail' . $Identifier]->AccountCurrency != $_SESSION['CompanyRecord']['currencydefault'] and isset($_SESSION['PaymentDetail' . $Identifier]->AccountCurrency)
echo '<tr>
		<td>' . _('Payment type') . ':</td>
		<input type="submit" style="display:none;" name="UpdatePmtType" value="Update" />
		<td><select name="Paymenttype" onchange="return ReloadForm(UpdatePmtType)">';

include('includes/GetPaymentMethods.php');
/* The array Payttypes is set up in includes/GetPaymentMethods.php
payment methods can be modified from the setup tab of the main menu under payment methods*/

foreach ($PaytTypes as $PaytID => $PaytType) {
	if (isset($_SESSION['PaymentDetail' . $Identifier]->Paymenttype) and $_SESSION['PaymentDetail' . $Identifier]->Paymenttype == $PaytID) {
		echo '<option selected="selected" value="' . $PaytID . '">' . $PaytType . '</option>';
	} //isset($_POST['Paymenttype']) and $_POST['Paymenttype'] == $PaytType
	else {
		echo '<option value="' . $PaytID . '">' . $PaytType . '</option>';
	}
} //end foreach
echo '</select></td>
	</tr>';

$SQL = "SELECT usepreprintedstationery
		FROM paymentmethods
		WHERE paymentid='" . $_SESSION['PaymentDetail' . $Identifier]->Paymenttype . "'";
$Result = DB_query($SQL);
$MyRow = DB_fetch_array($Result);

if ($MyRow['usepreprintedstationery'] == 1) {
	echo '<tr>
			<td>' . _('Cheque Number') . ':</td>
			<td><input type="text" name="ChequeNum" maxlength="8" size="10" value="' . $_SESSION['PaymentDetail' . $Identifier]->ChequeNumber . '" /></td><td>' . _('(if using pre-printed stationery)') . '</td>
		</tr>';
}

echo '<tr>
		<td>' . _('Reference') . ':</td>
		<td colspan="2"><input type="text" name="BankTransRef" maxlength="50" size="52" value="' . stripslashes($_SESSION['PaymentDetail' . $Identifier]->BankTransRef) . '" />  ' . _('Reference on Bank Transactions Inquiry') . '</td>
	</tr>';

if (!isset($_POST['Currency'])) {
	$_POST['Currency'] = $_SESSION['CompanyRecord']['currencydefault'];
} //!isset($_POST['Currency'])

echo '<tr>
		<td>' . _('Narrative') . ':</td>
		<td colspan="2"><input type="text" name="Narrative" maxlength="80" size="82" value="' . stripslashes($_SESSION['PaymentDetail' . $Identifier]->Narrative) . '" />' . _('Narrative on General Ledger Account Inquiry') . '</td>
	</tr>
		<td><input type="hidden" name="PreviousCurrency" value="' . $_POST['Currency'] . '" /></td>
		<td colspan="3"><div class="centre"><input type="submit" name="UpdateHeader" value="' . _('Update') . '" /></div></td>
	</tr>';

echo '</table>';

if ($_SESSION['CompanyRecord']['gllink_creditors'] == 1 and $_SESSION['PaymentDetail' . $Identifier]->SupplierID == '') {
	/* Set upthe form for the transaction entry for a GL Payment Analysis item */

	echo '<table class="selection">';
	echo '<tr>
			<th colspan="2">
				<h3>' . _('General Ledger Payment Analysis Entry') . '</h3>
			</th>
		</tr>';

	//Select the Tag
	echo '<tr>
			<td>' . _('Select Tag') . ':</td>
			<td><select name="Tag">';

	$SQL = "SELECT tagref,
				tagdescription
			FROM tags
			ORDER BY tagref";

	$Result = DB_query($SQL);
	echo '<option value="0"></option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['Tag']) and $_POST['Tag'] == $MyRow['tagref']) {
			echo '<option selected="selected" value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
		} //isset($_POST['Tag']) and $_POST['Tag'] == $MyRow['tagref']
		else {
			echo '<option value="' . $MyRow['tagref'] . '">' . $MyRow['tagref'] . ' - ' . $MyRow['tagdescription'] . '</option>';
		}
	} //$MyRow = DB_fetch_array($Result)
	echo '</select></td>
		</tr>';
	// End select Tag

	/*now set up a GLCode field to select from avaialble GL accounts */
	if (isset($_POST['GLManualCode'])) {
		echo '<tr>
				<td>' . _('Enter GL Account Manually') . ':</td>
				<td><input type="text" class="number" name="GLManualCode" maxlength="12" size="12" onchange="return inArray(this, GLCode.options,' . "'" . 'The account code ' . "'" . '+ this.value+ ' . "'" . ' doesnt exist' . "'" . ')"' . ' value="' . $_POST['GLManualCode'] . '"   /></td>
			</tr>';
	} //isset($_POST['GLManualCode'])
	else {
		echo '<tr>
				<td>' . _('Enter GL Account Manually') . ':</td>
				<td><input type="text" class="number" name="GLManualCode" maxlength="12" size="12" onchange="return inArray(this, GLCode.options,' . "'" . 'The account code ' . "'" . '+ this.value+ ' . "'" . ' doesnt exist' . "'" . ')" /></td>
			</tr>';
	}

	echo '<tr>
			<td>' . _('Select GL Group') . ':</td>
			<td><select name="GLGroup" onchange="return ReloadForm(UpdateCodes)">';

	$SQL = "SELECT groupcode,
					groupname
			FROM accountgroups
			WHERE language='" . $_SESSION['ChartLanguage'] . "'
			ORDER BY groupcode";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 0) {
		echo '</select></td>
			</tr>';
		prnMsg(_('No General ledger account groups have been set up yet') . ' - ' . _('payments cannot be analysed against GL accounts until the GL accounts are set up'), 'error');
	} else {
		echo '<option value=""></option>';
		while ($MyRow = DB_fetch_array($Result)) {
			if (isset($_POST['GLGroup']) and (stripslashes($_POST['GLGroup']) == $MyRow['groupcode'])) {
				echo '<option selected="selected" value="' . $MyRow['groupcode'] . '">' . $MyRow['groupcode'] . ' - ' . $MyRow['groupname'] . '</option>';
			} //isset($_POST['GLGroup']) and ($_POST['GLGroup'] == $MyRow['groupname'])
			else {
				echo '<option value="' . $MyRow['groupcode'] . '">' . $MyRow['groupcode'] . ' - ' . $MyRow['groupname'] . '</option>';
			}
		} //$MyRow = DB_fetch_array($Result)
		echo '</select>
				<input type="submit" name="UpdateCodes" value="Select" /></td>
				</tr>';
	}

	if (isset($_POST['GLGroup']) and $_POST['GLGroup'] != '') {
		$SQL = "SELECT chartmaster.accountcode,
						chartmaster.accountname
				FROM chartmaster
					INNER JOIN glaccountusers ON glaccountusers.accountcode=chartmaster.accountcode AND glaccountusers.userid='" .  $_SESSION['UserID'] . "' AND glaccountusers.canupd=1
				WHERE chartmaster.groupcode='" . $_POST['GLGroup'] . "'
					AND language='" . $_SESSION['ChartLanguage'] . "'
				ORDER BY chartmaster.accountcode";
	} else {
		$SQL = "SELECT chartmaster.accountcode,
						chartmaster.accountname
				FROM chartmaster
					INNER JOIN glaccountusers ON glaccountusers.accountcode=chartmaster.accountcode AND glaccountusers.userid='" .  $_SESSION['UserID'] . "' AND glaccountusers.canupd=1
				WHERE language='" . $_SESSION['ChartLanguage'] . "'
				ORDER BY chartmaster.accountcode";
	}


	echo '<tr>
			<td>' . _('Select GL Account') . ':</td>
			<td><select name="GLCode" onchange="return assignComboToInput(this,' . 'GLManualCode' . ')">';

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 0) {
		echo '</select></td></tr>';
		prnMsg(_('No General ledger accounts have been set up yet') . ' - ' . _('payments cannot be analysed against GL accounts until the GL accounts are set up'), 'error');
	} //DB_num_rows($Result) == 0
	else {
		echo '<option value=""></option>';
		while ($MyRow = DB_fetch_array($Result)) {
			if (isset($_POST['GLCode']) and $_POST['GLCode'] == $MyRow['accountcode']) {
				echo '<option selected="selected" value="' . $MyRow['accountcode'] . '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
			} //isset($_POST['GLCode']) and $_POST['GLCode'] == $MyRow['accountcode']
			else {
				echo '<option value="' . $MyRow['accountcode'] . '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
			}
		} //$MyRow = DB_fetch_array($Result)
		echo '</select></td></tr>';
	}

	echo '<tr>
			<td>' . _('Cheque/Voucher Number') . '</td>
			<td><input type="text" name="Cheque" maxlength="12" size="12" /></td>
		</tr>';

	if (isset($_POST['GLNarrative'])) { // General Ledger Payment (Different than Bank Account) info to be inserted on gltrans.narrative, varchar(200).
		echo '<tr>
				<td>' . _('GL Narrative') . ':</td>
				<td><input type="text" name="GLNarrative" maxlength="50" size="52" value="' . stripslashes($_POST['GLNarrative']) . '" /></td>
			</tr>';
	} //isset($_POST['GLNarrative'])
	else {
		echo '<tr>
				<td>' . _('GL Narrative') . ':</td>
				<td><input type="text" name="GLNarrative" maxlength="50" size="52" /></td>
			</tr>';
	}

	if (isset($_POST['GLAmount'])) {
		echo '<tr>
				<td>' . _('Amount') . ' (' . $_SESSION['PaymentDetail' . $Identifier]->Currency . '):</td>
				<td><input type="text" name="GLAmount" maxlength="12" size="12" class="number" value="' . $_POST['GLAmount'] . '" /></td>
			</tr>';
	} //isset($_POST['GLAmount'])
	else {
		echo '<tr><td>' . _('Amount') . ' (' . $_SESSION['PaymentDetail' . $Identifier]->Currency . '):</td>
				<td><input type="text" name="GLAmount" maxlength="12" size="12" class="number" /></td>
			</tr>';
	}

	echo '</table><br />';
	echo '<div class="centre">
			<input type="submit" name="Process" value="' . _('Accept') . '" />
			<input type="submit" name="Cancel" value="' . _('Cancel') . '" />
		</div>';

	if (sizeOf($_SESSION['PaymentDetail' . $Identifier]->GLItems) > 0) {
		echo '<br />
			<table class="selection">
			<tr>
				<th>' . _('Cheque No') . '</th>
				<th>' . _('Amount') . ' (' . $_SESSION['PaymentDetail' . $Identifier]->Currency . ')</th>
				<th>' . _('GL Account') . '</th>
				<th>' . _('Narrative') . '</th>
				<th>' . _('Tag') . '</th>
			</tr>';

		$PaymentTotal = 0;
		foreach ($_SESSION['PaymentDetail' . $Identifier]->GLItems as $PaymentItem) {
			$Tagsql = "SELECT tagdescription from tags where tagref='" . $PaymentItem->Tag . "'";
			$TagResult = DB_query($Tagsql);
			$TagMyrow = DB_fetch_row($TagResult);
			if ($PaymentItem->Tag == 0) {
				$TagName = 'None';
			} //$PaymentItem->Tag == 0
			else {
				$TagName = $TagMyrow[0];
			}

			echo '<tr>
				<td>' . $PaymentItem->Cheque . '</td>
				<td class="number">' . locale_number_format($PaymentItem->Amount, $_SESSION['PaymentDetail' . $Identifier]->CurrDecimalPlaces) . '</td>
				<td>' . $PaymentItem->GLCode . ' - ' . $PaymentItem->GLActName . '</td>
				<td>' . stripslashes($PaymentItem->Narrative) . '</td>
				<td>' . $PaymentItem->Tag . ' - ' . $TagName . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'] . '?identifier=' . $Identifier) . '&amp;Delete=' . $PaymentItem->ID . '" onclick="return MakeConfirm(\'' . _('Are you sure you wish to delete this payment analysis item?') . '\', \'Confirm Delete\', this);">' . _('Delete') . '</a></td>
				</tr>';
			$PaymentTotal += $PaymentItem->Amount;
		} //$_SESSION['PaymentDetail' . $Identifier]->GLItems as $PaymentItem
		echo '<tr>
				<td></td>
				<td class="number"><b>' . locale_number_format($PaymentTotal, $_SESSION['PaymentDetail' . $Identifier]->CurrDecimalPlaces) . '</b></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			</table>
			<br />';
		echo '<div class="centre"><input type="submit" name="CommitBatch" value="' . _('Accept and Process Payment') . '" /></div>';
	} //sizeOf($_SESSION['PaymentDetail' . $Identifier]->GLItems) > 0

} else {
	/*a supplier is selected or the GL link is not active then set out
	the fields for entry of receipt amt and disc */
	$SQL = "SELECT systypes.typename,
					supptrans.id,
					supptrans.transno,
					supptrans.suppreference,
					supptrans.trandate,
					supptrans.ovamount+supptrans.ovgst+supptrans.diffonexch-supptrans.alloc AS amount
				FROM supptrans
				INNER JOIN systypes
					ON systypes.typeid=supptrans.type
				WHERE settled=0
					AND supplierno='" . $_SESSION['PaymentDetail' . $Identifier]->SupplierID . "'
					AND (supptrans.ovamount+supptrans.ovgst+supptrans.diffonexch-supptrans.alloc)<>0";
	$Result = DB_query($SQL);
	echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">' . _('Date') . '</th>
					<th class="SortedColumn">' . _('Transaction Type') . '</th>
					<th class="SortedColumn">' . _('Transaction Number') . '</th>
					<th class="SortedColumn">' . _('Reference') . '</th>
					<th class="SortedColumn">' . _('Amount') . '</th>
				</tr>
			</thead>';

	echo '<tbody>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr>
				<td>' . ConvertSQLDate($MyRow['trandate']) . '</td>
				<td>' . $MyRow['typename'] . '</td>
				<td>' . $MyRow['transno'] . '</td>
				<td>' . $MyRow['suppreference'] . '</td>
				<td class="number">' . locale_number_format($MyRow['amount'], $_SESSION['PaymentDetail' . $Identifier]->CurrDecimalPlaces) . '</td>
				<td><input onclick="AddAmount(this, \'Amount\');" type="checkbox" name="paid' . $MyRow['id'] . '" value="' . $MyRow['amount'] . '" />' . _('Pay') . '</td>
			</tr>';
	}
	echo '</tbody>';
	echo '</table>';
	echo '<table class="selection">
		<tr>
			<td>' . _('Amount of Payment') . ' ' . $_SESSION['PaymentDetail' . $Identifier]->Currency . ':</td>
			<td><input class="number" type="text" id="Amount" name="Amount" maxlength="12" size="13" value="' . locale_number_format($_SESSION['PaymentDetail' . $Identifier]->Amount, $_SESSION['PaymentDetail' . $Identifier]->CurrDecimalPlaces) . '" /></td>
		</tr>';

	if (isset($_SESSION['PaymentDetail' . $Identifier]->SupplierID)) {
		/*So it is a supplier payment so show the discount entry item */
		echo '<tr>';
		echo '<td><input type="hidden" name="SuppName" value="' . $_SESSION['PaymentDetail' . $Identifier]->SuppName . '" />
				' . _('Amount of Discount') . ':</td>
				<td><input class="number" type="text" name="Discount" maxlength="12" size="13" value="' . $_SESSION['PaymentDetail' . $Identifier]->Discount . '" /></td>
			</tr>';
	} //isset($_SESSION['PaymentDetail' . $Identifier]->SupplierID)
	else {
		echo '<input type="hidden" name="Discount" value="0" />';
	}
	echo '<tr>
			<td>' . _('Cheque/Voucher Number') . '</td>
			<td><input type="text" name="Cheque" maxlength="12" size="12" /></td>
		</tr>';
	echo '</table><br />';
	echo '<div class="centre"><input type="submit" name="CommitBatch" value="' . _('Accept and Process Payment') . '" /></div>';
}
echo '</form>';

include('includes/footer.inc');
?>