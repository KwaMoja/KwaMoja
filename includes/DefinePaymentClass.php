<?php

/* definition of the Payment class */

class Payment {

	var $GLItems;
	/*array of objects of Payment class - id is the pointer */
	var $Account;
	/*Bank account GL Code Paid from */
	var $AccountCurrency;
	/*Bank account currency */
	var $BankAccountName;
	/*Bank account name */
	var $DatePaid;
	/*Date the batch of Payments was Paid */
	var $ExRate;
	/*Exchange rate between the payment and the account currency*/
	var $FunctionalExRate;
	/*Ex rate between the account currency and functional currency */
	var $Currency;
	/*Currency being Paid - defaulted to bank account currency */
	var $CurrDecimalPlaces;
	var $SupplierID;
	/* supplier code */
	var $SuppName;
	var $Address1;
	var $Address2;
	var $Address3;
	var $Address4;
	var $Address5;
	var $Address6;
	var $Discount;
	var $Amount;
	var $Narrative;
	var $GLItemCounter;
	var $BankTransRef;
	/*Counter for the number of GL accounts being posted to by the Payment */
	var $ChequeNumber; //if using pre-printed stationery
	var $Paymenttype;

	function __construct() {
		/*Constructor function initialises a new Payment batch */
		$this->GLItems = array();
		$this->GLItemCounter = 0;
		$this->SupplierID = "";
		$this->SuppName = "";
		$this->Address1 = "";
		$this->Address2 = "";
		$this->Address3 = "";
		$this->Address4 = "";
		$this->Address5 = "";
		$this->Address6 = "";
		$this->ChequeNumber = 0;
		$this->BankTransRef = '';
		$this->DatePaid = Date($_SESSION['DefaultDateFormat']);
		$this->ExRate = 1;
		$this->FunctionalExRate = 1;
	}

	function Add_To_GLAnalysis($Amount, $Narrative, $GLCode, $GLActName, $Tag, $Cheque) {

		if (isset($GLCode) and $Amount != 0) {
			$this->GLItems[$this->GLItemCounter] = new PaymentGLAnalysis($Amount, $Narrative, $this->GLItemCounter, $GLCode, $GLActName, $Tag, $Cheque);
			$this->GLItemCounter++;
			$this->Amount += $Amount;
			return 1;
		}
		return 0;
	}

	function remove_GLItem($GL_ID) {
		unset($this->GLItems[$GL_ID]);
	}

}
/* end of class defintion */

class PaymentGLAnalysis {

	var $Amount;
	/* in currency of the payment*/
	var $Narrative;
	var $GLCode;
	var $GLActName;
	var $ID;
	var $Tag;
	var $Cheque;

	function __construct($Amt, $Narr, $id, $GLCode, $GLActName, $Tag, $Cheque) {

		/* Constructor function to add a new PaymentGLAnalysis object with passed params */
		$this->Amount = $Amt;
		$this->Narrative = $Narr;
		$this->GLCode = $GLCode;
		$this->GLActName = $GLActName;
		$this->ID = $id;
		$this->Tag = $Tag;
		$this->Cheque = $Cheque;
	}
}

?>