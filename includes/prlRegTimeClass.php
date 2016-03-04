<?php
/* $Revision: 1.3 $ */
/* definition of the Journal class */

Class OverTime {

	var $RTEntries; /*array of objects of JournalGLAnalysis class - id is the pointer */
	var $RTDate; /*Date the journal to be processed */
	var $RTItemCounter; /*Counter for the number of GL entires being posted to by the journal */
	var $RTItemID;
	var $RTTotal; /*Running total for the journal */

	function OverTime(){
	/*Constructor function initialises a new journal */
		$this->RTEntries = array();
		$this->RTItemCounter=0;
		$this->RTTotal=0;
		$this->RTItemID=0;
	}
	function Add_RTEntry($RTHours, $EmployeeID, $LastName, $FirstName, $RTDesc){
		if (isset($EmployeeID) AND $RTHours!=0){
			$this->RTEntries[$this->RTItemID] = new RTAnalysis($this->RTItemID,$RTHours, $EmployeeID, $LastName, $FirstName, $RTDesc);
			$this->RTItemCounter++;
			$this->RTItemID++;
			$this->RTTotal += $RTHours;
			Return 1;
		}
		Return 0;
	}

	function remove_RTEntry($GL_ID){
		$this->RTTotal -= $this->RTEntries[$GL_ID]->RTHours;
		unset($this->RTEntries[$GL_ID]);
		$this->RTItemCounter--;
	}

} /* end of class defintion */
Class RTAnalysis {
	Var $RTHours;
	Var $EmployeeID;
	Var $LastName;
	var $FirstName;
	var $RegTimeDesc;
	Var $ID;

	function RTAnalysis ($id, $Oth, $Empcode, $Last, $First, $RTDesc){

/* Constructor function to add a new  object with passed params */
		$this->RTHours =$Oth;
		$this->EmployeeID = $Empcode;
		$this->LastName = $Last;
		$this->FirstName = $First;
		$this->OverTimeDesc = $RTDesc;
		$this->ID = $id;
    }
}


?>