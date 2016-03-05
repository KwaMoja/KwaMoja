<?php

/*Class to hold stock transfer records */

class StockTransfer {

	var $TrfID;
	var $StockLocationFrom;
	var $StockLocationFromName;
	var $StockLocationFromAccount;
	var $StockLocationTo;
	var $StockLocationToName;
	var $StockLocationToAccount;
	var $TranDate;
	var $TransferItem;
	/*Array of LineItems */

	function __construct($TrfID, $StockLocationFrom, $StockLocationFromName, $StockLocationFromAccount, $StockLocationTo, $StockLocationToName, $StockLocationToAccount, $TranDate) {

		$this->TrfID = $TrfID;
		$this->StockLocationFrom = $StockLocationFrom;
		$this->StockLocationFromName = $StockLocationFromName;
		$this->StockLocationFromAccount = $StockLocationFromAccount;
		$this->StockLocationTo = $StockLocationTo;
		$this->StockLocationToName = $StockLocationToName;
		$this->StockLocationToAccount = $StockLocationToAccount;
		$this->TranDate = $TranDate;
		$this->TransferItem = array();
		/*Array of LineItem s */
	}
}

class LineItem {
	var $StockId;
	var $ItemDescription;
	var $ShipQty;
	var $PrevRecvQty;
	var $Quantity;
	var $PartUnit;
	var $Controlled;
	var $Serialised;
	var $DecimalPlaces;
	var $Perishable;
	var $SerialItems;
	/*array to hold controlled items*/
	//Constructor
	function __construct($StockId, $ItemDescription, $Quantity, $PartUnit, $Controlled, $Serialised, $Perishable, $DecimalPlaces) {

		$this->StockID = $StockId;
		$this->ItemDescription = $ItemDescription;
		$this->PartUnit = $PartUnit;
		$this->Controlled = $Controlled;
		$this->Serialised = $Serialised;
		$this->DecimalPlaces = $DecimalPlaces;
		$this->Perishable = $Perishable;
		$this->ShipQty = $Quantity;
		if ($this->Controlled == 1) {
			$this->Quantity = 0;
		} else {
			$this->Quantity = $Quantity;
		}
		$this->SerialItems = array();
	}
}
?>