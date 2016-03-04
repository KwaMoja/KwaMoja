<?php
/* $Revision: 1.0 $ */

Class Tardiness {

	var $TDEntries; /*array of objects class - id is the pointer */
	var $TDDate; /*Date to be processed */
	var $TDItemCounter; /*Counter for the number of entires being posted */
	var $TDItemID;
	var $TDTotal; /*Running total */

	function Tardiness(){
	/*Constructor function initialises */
		$this->TDEntries = array();
		$this->TDItemCounter=0;
		$this->TDTotal=0;
		$this->TDTotalAbs=0;
		$this->TDItemID=0;
	}
	function Add_TDEntry($TDHours, $TDHoursAbs, $EmployeeID, $LastName, $FirstName, $TDDesc){
		if ((isset($EmployeeID) AND $TDHours!=0) OR (isset($EmployeeID) AND $TDHoursAbs!=0)) {
			$this->TDEntries[$this->TDItemID] = new TDAnalysis($this->TDItemID,$TDHours, $TDHoursAbs, $EmployeeID, $LastName, $FirstName, $TDDesc);
			$this->TDItemCounter++;
			$this->TDItemID++;
			$this->TDTotal += $TDHours;
			$this->TDTotalAbs += $TDHoursAbs;
			Return 1;
		}
		Return 0;
	}

	function remove_TDEntry($GL_ID){
		$this->TDTotal -= $this->TDEntries[$GL_ID]->TDHours;
		$this->TDTotalAbs -= $this->TDEntries[$GL_ID]->TDHoursAbs;
		unset($this->TDEntries[$GL_ID]);
		$this->TDItemCounter--;
	}

} /* end of class defintion */
Class TDAnalysis {
	Var $TDHours;
	Var $TDHoursAbs;
	Var $EmployeeID;
	Var $LastName;
	var $FirstName;
	var $RegTimeDesc;
	Var $ID;

	function TDAnalysis ($id, $Oth, $OthAbs, $Empcode, $Last, $First, $TDDesc){

/* Constructor function to add a new  object with passed params */
		$this->TDHours =$Oth;
		$this->TDHoursAbs =$OthAbs;
		$this->EmployeeID = $Empcode;
		$this->LastName = $Last;
		$this->FirstName = $First;
		$this->OverTimeDesc = $TDDesc;
		$this->ID = $id;
    }
}


?>