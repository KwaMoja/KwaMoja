<?php

/* Definition of the Works Order class to hold all the information for a purchase order and delivery
 */

Class WorkOrder {

	var $OrderNumber;
	var $LocationCode;
	var $RequiredBy;
	var $StartDate;
	var $CostIssued;
	var $Closed;
	var $Items; //Array of WOItem objects
	var $NumberOfItems;

	function WorkOrder() {
		$this->OrderNumber = 0;
		$this->LocationCode = $_SESSION['UserStockLocation'];
		$this->RequiredBy = Date($_SESSION['DefaultDateFormat']);
		$this->StartDate = Date($_SESSION['DefaultDateFormat']);
		$this->CostIssued = 0;
		$this->Closed = 0;
		$this->Items = array();
		$this->NumberOfItems = 0;
	}

	function AddItemToOrder($StockID, $QuantityRequired, $QuantityReceived, $NextLotSerialNumber) {
		$this->Items[$this->NumberOfItems + 1] = new WOItem($StockID, $QuantityRequired, $QuantityReceived, $NextLotSerialNumber, $this->LocationCode, $this->NumberOfItems + 1);
		$this->NumberOfItems++;
	}

	function UpdateItem($StockID, $QuantityRequired) {
		$this->Items[$this->ItemByStockID($StockID)]->QuantityRequired = $QuantityRequired;
		$this->Items[$this->ItemByStockID($StockID)]->RefreshRequirements($this->LocationCode);
	}

	function RemoveItemFromOrder($LineNumber) {
		global $db;

		$this->Items[$LineNumber]->QuantityRequired = $this->Items[$LineNumber]->QuantityReceived;
		$this->Items[$LineNumber]->RefreshRequirements($this->LocationCode);
		if ($this->OrderNumber != 0) {
			$sql = "DELETE FROM worequirements WHERE wo='" . $this->OrderNumber . "' AND parentstockid='" . $this->Items[$LineNumber]->StockID . "'";
			$DeleteResult = DB_query($sql, $db);
			$sql = "DELETE FROM woitems WHERE wo='" . $this->OrderNumber . "' AND stockid='" . $this->Items[$LineNumber]->StockID . "'";
			$DeleteResult = DB_query($sql, $db, _('Error deleting the item'));
		}
		unset($this->Items[$LineNumber]);
		$this->NumberOfItems--;
	}

	function ItemByStockID($StockID) {
		for ($i = 1; $i <= $this->NumberOfItems; $i++) {
			if (isset($this->Items[$i]) and $this->Items[$i]->StockID == $StockID) {
				return $i;
			}
		}
		return 0;
	}

	function Save() {
		global $db;

		if ($this->OrderNumber == 0) {
			$this->OrderNumber = GetNextTransNo(40, $db);
			$sql = "INSERT INTO workorders (wo,
											loccode,
											requiredby,
											startdate,
											costissued)
										VALUES (
											'" . $this->OrderNumber . "',
											'" . $this->LocationCode . "',
											'" . FormatDateForSQL($this->RequiredBy) . "',
											'" . FormatDateForSQL($this->StartDate) . "',
											'" . $this->CostIssued . "'
										)";
		} else {
			$sql = "UPDATE workorders SET   loccode='" . $this->LocationCode . "',
											requiredby='" . FormatDateForSQL($this->RequiredBy) . "',
											startdate='" . FormatDateForSQL($this->StartDate) . "',
											costissued='" . $this->CostIssued . "'
										WHERE wo='" . $this->OrderNumber . "'";
		}
		$UpdateWOResult = DB_query($sql, $db);
		foreach ($this->Items as $i => $Item) {
			$Item->Save($this->OrderNumber);
		}
	}

	function Load($WONumber) {
		global $db;

		$sql = "SELECT  loccode,
						requiredby,
						startdate,
						costissued,
						closed
					FROM workorders
					WHERE workorders.wo='" . $WONumber . "'";

		$WOResult = DB_query($sql, $db);
		if (DB_num_rows($WOResult) == 1) {

			$myrow = DB_fetch_array($WOResult);
			$this->StartDate = ConvertSQLDate($myrow['startdate']);
			$this->CostIssued = $myrow['costissued'];
			$this->Closed = $myrow['closed'];
			$this->RequiredBy = ConvertSQLDate($myrow['requiredby']);
			$this->LocationCode = $myrow['loccode'];
			$this->OrderNumber = $WONumber;

			$ErrMsg = _('Could not get the work order items');
			$WOItemsResult = DB_query("SELECT   stockid,
												qtyreqd,
												qtyrecd,
												stdcost,
												nextlotsnref
											FROM woitems
											WHERE wo='" . $WONumber . "'", $db, $ErrMsg);

			$NumberOfOutputs = DB_num_rows($WOItemsResult);
			$i = 1;
			while ($WOItem = DB_fetch_array($WOItemsResult)) {
				$this->Items[$i] = new WOItem($WOItem['stockid'], $WOItem['qtyreqd'], $WOItem['qtyrecd'], $WOItem['nextlotsnref'], $this->LocationCode, $i);
				$i++;
			}
			$this->NumberOfItems = $i;
		}
	}

}

Class WOItem {

	var $StockID;
	var $Description;
	var $DecimalPlaces;
	var $QuantityRequired;
	var $QuantityReceived;
	var $Controlled;
	var $Serialised;
	var $StandardCost;
	var $NextLotSerialNumbers;
	var $LineNumber;
	var $Requirements; // Array of WORequirement objects
	var $NumberOfRequirements;

	function WOItem($StockID, $QuantityRequired, $QuantityReceived, $NextLotSerialNumber, $LocationCode, $NumberOfItems) {
		global $db;

		$StockResult = DB_query("SELECT materialcost+labourcost+overheadcost AS cost,
										stockmaster.description,
										stockmaster.decimalplaces,
										stockmaster.controlled,
										stockmaster.serialised
									FROM stockmaster
									INNER JOIN bom
										ON stockmaster.stockid=bom.parent
									WHERE bom.parent='" . $StockID . "'
										AND bom.loccode='" . $LocationCode . "'", $db);
		$StockRow = DB_fetch_array($StockResult);
		$StandardCost = $StockRow['cost'] * $QuantityRequired;
		$Description = $StockRow['description'];
		$DecimalPlaces = $StockRow['decimalplaces'];
		$Controlled = $StockRow['controlled'];
		$Serialised = $StockRow['serialised'];

		$this->StockID = $StockID;
		$this->Description = $Description;
		$this->DecimalPlaces = $DecimalPlaces;
		$this->QuantityRequired = $QuantityRequired;
		$this->QuantityReceived = $QuantityReceived;
		$this->StandardCost = $StandardCost;
		$this->Controlled = $Controlled;
		$this->Serialised = $Serialised;
		$this->NextLotSerialNumber = $NextLotSerialNumber;
		$this->LineNumber = $NumberOfItems;
		$this->Requirements = array();
		$this->NumberOfRequirements = 0;

		$BOMResult = DB_Query("SELECT   bom.component,
										bom.quantity,
										bom.autoissue,
										description,
										decimalplaces,
										materialcost,
										labourcost,
										overheadcost
									FROM bom
									INNER JOIN stockmaster
										ON stockmaster.stockid=bom.component
									WHERE bom.parent='" . $StockID . "'
										AND bom.loccode='" . $LocationCode . "'", $db);
		while ($BOMRow = DB_fetch_array($BOMResult, $db)) {
			$this->AddRequirements($BOMRow['component'], $BOMRow['quantity'] * $QuantityRequired, $BOMRow['materialcost'] + $BOMRow['labourcost'] + $BOMRow['overheadcost'], $BOMRow['autoissue'], $BOMRow['description'], $BOMRow['decimalplaces']);
		}
	}

	function Save($OrderNumber) {
		global $db;

		$CheckSQL = "SELECT wo,
							stockid
						FROM woitems
						WHERE wo='" . $OrderNumber . "'
							AND stockid='" . $this->StockID . "'";
		$CheckResult = DB_query($CheckSQL, $db);

		if (DB_num_rows($CheckResult) == 0) {
			$sql = "INSERT INTO woitems (wo,
										stockid,
										qtyreqd,
										qtyrecd,
										stdcost,
										nextlotsnref
									) VALUES (
										'" . $OrderNumber . "',
										'" . $this->StockID . "',
										'" . $this->QuantityRequired . "',
										'" . $this->QuantityReceived . "',
										'" . $this->StandardCost . "',
										'" . $this->NextLotSerialNumbers . "'
									)";
		} else {
			$sql = "UPDATE woitems SET  qtyreqd='" . $this->QuantityRequired . "',
										qtyrecd='" . $this->QuantityReceived . "',
										stdcost='" . $this->StandardCost . "',
										nextlotsnref='" . $this->NextLotSerialNumbers . "'
									WHERE wo='" . $OrderNumber . "'
										AND stockid='" . $this->StockID . "'";
		}
		$UpdateItems = DB_query($sql, $db);
		foreach ($this->Requirements as $i => $Requirement) {
			$Requirement->Save($OrderNumber);
		}
	}

	function AddRequirements($StockID, $Quantity, $StandardCost, $AutoIssue, $Description, $DecimalPlaces) {
		$this->Requirements[$this->NumberOfRequirements + 1] = new WORequirement($this->StockID, $StockID, $Quantity, $StandardCost, $AutoIssue, $Description, $DecimalPlaces);
		$this->NumberOfRequirements++;
	}

	function LoadRequirements() {
	}

	function RefreshRequirements($LocationCode) {
		global $db;

		$BOMResult = DB_Query("SELECT   bom.component,
										bom.quantity,
										bom.autoissue,
										description,
										decimalplaces,
										materialcost,
										labourcost,
										overheadcost
									FROM bom
									INNER JOIN stockmaster
										ON stockmaster.stockid=bom.component
									WHERE bom.parent='" . $this->StockID . "'
										AND bom.loccode='" . $LocationCode . "'", $db);
		while ($BOMRow = DB_fetch_array($BOMResult, $db)) {
			$this->Requirements[$this->RequirementByStockID($this->StockID, $BOMRow['component'])]->Quantity = $BOMRow['quantity'];
		}
	}

	function RequirementByStockID($Parent, $StockID) {
		for ($i = 1; $i <= $this->NumberOfRequirements; $i++) {
			if ($this->Requirements[$i]->StockID == $StockID and $this->Requirements[$i]->ParentStockID == $Parent) {
				return $i;
			}
		}
		return 0;
	}
}

Class WORequirement {

	var $ParentStockID;
	var $StockID;
	var $Description;
	var $DecimalPlaces;
	var $Quantity;
	var $StandardCost;
	var $AutoIssue;

	function WORequirement($ParentStockID, $StockID, $Quantity, $StandardCost, $AutoIssue, $Description, $DecimalPlaces) {
		$this->ParentStockID = $ParentStockID;
		$this->StockID = $StockID;
		$this->Quantity = $Quantity;
		$this->StandardCost = $StandardCost;
		$this->AutoIssue = $AutoIssue;
		$this->Description = $Description;
		$this->DecimalPlaces = $DecimalPlaces;
	}

	function Save($OrderNumber) {
		global $db;

		$CheckSQL = "SELECT wo,
							parentstockid,
							stockid
						FROM worequirements
						WHERE wo='" . $OrderNumber . "'
							AND parentstockid='" . $this->ParentStockID . "'
							AND stockid='" . $this->StockID . "'";
		$CheckResult = DB_query($CheckSQL, $db);

		if (DB_num_rows($CheckResult) == 0) {
			$sql = "INSERT INTO worequirements (wo,
												parentstockid,
												stockid,
												qtypu,
												stdcost,
												autoissue
											) VALUES (
												'" . $OrderNumber . "',
												'" . $this->ParentStockID . "',
												'" . $this->StockID . "',
												'" . $this->Quantity . "',
												'" . $this->StandardCost . "',
												'" . $this->AutoIssue . "'
											)";
		} else {
			$sql = "UPDATE worequirements SET   qtypu='" . $this->Quantity . "',
												stdcost='" . $this->StandardCost . "',
												autoissue='" . $this->AutoIssue . "'
											WHERE wo='" . $OrderNumber . "'
												AND parentstockid='" . $this->ParentStockID . "'
												AND stockid='" . $this->StockID . "'";
		}
		$UpdateRequirements = DB_query($sql, $db);
	}

}

?>