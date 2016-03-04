<?php
/* $Revision: 1.3 $ */
/* definition of the Journal class */

Class OverTime {

	var $OTEntries; /*array of objects of JournalGLAnalysis class - id is the pointer */
	var $OTDate; /*Date the journal to be processed */
	var $OTType;
	var $OTItemCounter; /*Counter for the number of GL entires being posted to by the journal */
	var $OTItemID;
	var $OTTotal; /*Running total for the journal */

	function OverTime(){
	/*Constructor function initialises a new journal */
		$this->OTEntries = array();
		$this->OTItemCounter=0;
		$this->OTTotal=0;
		$this->OTItemID=0;
	}
	function Add_OTEntry($OTHours, $EmployeeID, $LastName, $FirstName, $OverTimeDesc, $OverTimeID){
		if (isset($EmployeeID) AND $OTHours!=0){
			$this->OTEntries[$this->OTItemID] = new OTAnalysis($this->OTItemID,$OTHours, $EmployeeID, $LastName, $FirstName, $OverTimeDesc, $OverTimeID);
			$this->OTItemCounter++;
			$this->OTItemID++;
			$this->OTTotal += $OTHours;
			Return 1;
		}
		Return 0;
	}

	function remove_OTEntry($GL_ID){
		$this->OTTotal -= $this->OTEntries[$GL_ID]->OTHours;
		unset($this->OTEntries[$GL_ID]);
		$this->OTItemCounter--;
	}

} /* end of class defintion */
Class OTAnalysis {
	Var $OTHours;
	Var $EmployeeID;
	Var $LastName;
	var $FirstName;
	var $OverTimeDesc;
	var $OverTimeID;
	var $GLCode;
	Var $ID;

	function OTAnalysis ($id, $Oth, $Empcode, $Last, $First, $OTDesc, $OTID){

/* Constructor function to add a new  object with passed params */
		$this->OTHours =$Oth;
		$this->EmployeeID = $Empcode;
		$this->LastName = $Last;
		$this->FirstName = $First;
		$this->OverTimeDesc = $OTDesc;
		$this->OverTimeID = $OTID;
		
		$this->ID = $id;
    }
}


?>