<?php

class StockRequest {

	var $ID;
	var $LineItems;
	/*array of objects of class LineDetails using the product id as the pointer */
	var $DispatchDate;
	var $UserID;
	var $Location;
	var $Department;
	var $Narrative;
	var $NewRequest=0;
	var $LineCounter = 0;

	function __construct() {
		/*Constructor function initialises a new shopping cart */
		$this->DispatchDate = date($_SESSION['DefaultDateFormat']);
		$this->LineItems = array();
	}

	function AddLine($StockId, $ItemDescription, $Quantity, $UOM, $DecimalPlaces, $LineNumber = -1) {

		if ($LineNumber == -1) {
			$LineNumber = $this->LineCounter;
		}
		$this->LineItems[$LineNumber] = new LineDetails($StockId, $ItemDescription, $Quantity, $UOM, $DecimalPlaces, $LineNumber);
		$this->LineCounter = $LineNumber + 1;
	}
}

class LineDetails {
	var $StockId;
	var $ItemDescription;
	var $Quantity;
	var $UOM;
	var $LineNumber;

	function __construct($StockId, $ItemDescription, $Quantity, $UOM, $DecimalPlaces, $LineNumber) {

		$this->LineNumber = $LineNumber;
		$this->StockID = $StockId;
		$this->ItemDescription = $ItemDescription;
		$this->Quantity = $Quantity;
		$this->DecimalPlaces = $DecimalPlaces;
		$this->UOM = $UOM;
	}

}

?>