<?php

/* definition of the Project class */

class Project {

	var $ProjectNo;
	/*auto generated contract no - but there for existing contracts */
	var $ProjectRef;
	/*the contract short description used for stockid when contract submitted for quotation */
	var $ProjectDescription;
	/*the description of the contract */
	var $DonorNo;
	/*the customer that the contract is for */
	var $DonorName;
	var $DonorRef;
	var $Status;
	/* 100 = initiated - 1=quoted - 2=ordered - 3=completed */
	var $CategoryID;
	/* the category where the contract will be when converted to an item  for quotation*/
	var $OrderNo;
	/* the order number created when the contract is quoted */
	var $LocCode;
	/* the inventory location where the contract is to be performed */
	var $CustomerRef;
	var $Margin;
	/*the margin used in quoting for the contract */
	var $WO;
	/*the wo created when the quotation is converted to an order */
	var $RequiredDate;
	var $CompletionDate;
	var $Drawing;
	/*a link to the contract drawing*/
	var $CurrCode;
	/*the currency of the customer to quote in */
	var $ExRate;
	/*the rate of exchange between customer currency and company functional currency used when quoting */
	var $BOMComponentCounter;
	var $RequirementsCounter;

	var $ProjectBOM;
	/*array of stockid components  required for the contract */
	var $ProjectReqts;
	/*array of other items required for the contract */

	function __construct() {
		/*Constructor function initialises a new Payment batch */
		$this->ProjectBOM = array();
		$this->ProjectReqts = array();
		$this->BOMComponentCounter = 0;
		$this->RequirementsCounter = 0;
		$this->Status = 0;
	}

	function Add_To_ProjectBOM($StockId, $ItemDescription, $RequiredBy, $WorkCentre, $Quantity, $ItemCost, $UOM, $DecimalPlaces) {

		if (isset($StockId) and $Quantity != 0) {
			$this->ProjectBOM[$this->BOMComponentCounter] = new ProjectComponent($this->BOMComponentCounter, $StockId, $ItemDescription, $RequiredBy, $WorkCentre, $Quantity, $ItemCost, $UOM, $DecimalPlaces);
			$this->BOMComponentCounter++;
			return 1;
		}
		return 0;
	}

	function Remove_ProjectComponent($ProjectComponent_ID) {
		$Result = DB_query("DELETE FROM projectbom
											WHERE projectref='" . $this->ProjectRef . "'
											AND stockid='" . $this->ProjectBOM[$ProjectComponent_ID]->StockID . "'");
		unset($this->ProjectBOM[$ProjectComponent_ID]);
	}


	/*Requirments Methods */

	function Add_To_ProjectRequirements($Requirement, $Quantity, $CostPerUnit, $ProjectReqID = 0) {

		if (isset($Requirement) and $Quantity != 0 and $CostPerUnit != 0) {
			$this->ProjectReqts[$this->RequirementsCounter] = new ProjectRequirement($Requirement, $Quantity, $CostPerUnit, $ProjectReqID);
			$this->RequirementsCounter++;
			return 1;
		}
		return 0;
	}

	function Remove_ProjectRequirement($ProjectRequirementID) {
		$Result = DB_query("DELETE FROM projectreqts WHERE projectreqid='" . $this->ProjectReqts[$ProjectRequirementID]->ProjectReqID . "'");
		unset($this->ProjectReqts[$ProjectRequirementID]);
	}

}
/* end of class defintion */

class ProjectComponent {
	var $ComponentID;
	var $StockId;
	var $ItemDescription;
	var $RequiredBy;
	var $WorkCentre;
	var $Quantity;
	var $ItemCost;
	var $UOM;
	var $DecimalPlaces;

	function __construct($ComponentID, $StockId, $ItemDescription, $RequiredBy, $WorkCentre, $Quantity, $ItemCost, $UOM, $DecimalPlaces = 0) {

		/* Constructor function to add a new Project Component object with passed params */
		$this->ComponentID = $ComponentID;
		$this->StockID = $StockId;
		$this->ItemDescription = $ItemDescription;
		$this->RequiredBy = $RequiredBy;
		$this->WorkCentre = $WorkCentre;
		$this->Quantity = $Quantity;
		$this->ItemCost = $ItemCost;
		$this->UOM = $UOM;
		$this->DecimalPlaces = $DecimalPlaces;
	}
}

class ProjectRequirement {

	var $ProjectReqID;
	/*Used to hold the database ID of the contractreqtID  - if an existing contract*/
	var $Requirement;
	/*The description of the requirement for the contract */
	var $Quantity;
	var $CostPerUnit;

	function __construct($Requirement, $Quantity, $CostPerUnit, $ProjectReqID = 0) {

		/* Constructor function to add a new Project Component object with passed params */
		$this->Requirement = $Requirement;
		$this->Quantity = $Quantity;
		$this->CostPerUnit = $CostPerUnit;
		$this->ProjectReqID = $ProjectReqID;
	}
}
?>