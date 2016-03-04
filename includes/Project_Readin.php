<?php

/*Project_Readin.php is used by the modify existing Project in Projects.php and also by ProjectCosting.php */

$ProjectHeaderSQL = "SELECT projectdescription,
							projects.donorno,
							projects.loccode,
							status,
							categoryid,
							wo,
							requireddate,
							completiondate,
							drawing,
							exrate,
							donors.name,
							donors.currcode
						FROM projects
						INNER JOIN donors
							ON projects.donorno=donors.donorno
						INNER JOIN currencies
							ON donors.currcode=currencies.currabrev
						INNER JOIN locationusers
							ON locationusers.loccode=projects.loccode
							AND locationusers.userid='" .  $_SESSION['UserID'] . "'
							AND locationusers.canupd=1
						WHERE projectref= '" . $ProjectRef . "'";

$ErrMsg = _('The project cannot be retrieved because');
$DbgMsg = _('The SQL statement that was used and failed was');
$ProjectHdrResult = DB_query($ProjectHeaderSQL, $ErrMsg, $DbgMsg);

if (DB_num_rows($ProjectHdrResult) == 1 and !isset($_SESSION['Project' . $Identifier]->ProjectRef)) {

	$MyRow = DB_fetch_array($ProjectHdrResult);
	$_SESSION['Project' . $Identifier]->ProjectRef = $ProjectRef;
	$_SESSION['Project' . $Identifier]->ProjectDescription = $MyRow['projectdescription'];
	$_SESSION['Project' . $Identifier]->DonorNo = $MyRow['donorno'];
	$_SESSION['Project' . $Identifier]->LocCode = $MyRow['loccode'];
	$_SESSION['Project' . $Identifier]->Status = $MyRow['status'];
	$_SESSION['Project' . $Identifier]->CategoryID = $MyRow['categoryid'];
	$_SESSION['Project' . $Identifier]->WO = $MyRow['wo'];
	$_SESSION['Project' . $Identifier]->RequiredDate = ConvertSQLDate($MyRow['requireddate']);
	$_SESSION['Project' . $Identifier]->CompletionDate = ConvertSQLDate($MyRow['completiondate']);
	$_SESSION['Project' . $Identifier]->Drawing = $MyRow['drawing'];
	$_SESSION['Project' . $Identifier]->ExRate = $MyRow['exrate'];
	$_SESSION['Project' . $Identifier]->DonorName = $MyRow['name'];
	$_SESSION['Project' . $Identifier]->CurrCode = $MyRow['currcode'];


	/*now populate the project BOM array with the items required for the project */

	$ProjectBOMsql = "SELECT projectbom.stockid,
							stockmaster.description,
							projectbom.requiredby,
							projectbom.workcentreadded,
							projectbom.quantity,
							stockmaster.units,
							stockmaster.decimalplaces,
							stockcosts.materialcost+stockcosts.labourcost+stockcosts.overheadcost AS cost
						FROM projectbom
						INNER JOIN stockmaster
							ON projectbom.stockid=stockmaster.stockid
						LEFT JOIN stockcosts
							ON stockmaster.stockid=stockcosts.stockid
							AND stockcosts.succeeded=0
						WHERE projectref ='" . $ProjectRef . "'";

	$ErrMsg = _('The bill of material cannot be retrieved because');
	$DbgMsg = _('The SQL statement that was used to retrieve the project bill of material was');
	$ProjectBOMResult = DB_query($ProjectBOMsql, $ErrMsg, $DbgMsg);

	if (DB_num_rows($ProjectBOMResult) > 0) {
		while ($MyRow = DB_fetch_array($ProjectBOMResult)) {
			$_SESSION['Project' . $Identifier]->Add_To_ProjectBOM($MyRow['stockid'], $MyRow['description'], $MyRow['requiredby'], $MyRow['workcentreadded'], $MyRow['quantity'], $MyRow['cost'], $MyRow['units'], $MyRow['decimalplaces']);
		}
		/* add project bill of materials BOM lines*/
	} //end is there was a project BOM to add
	//Now add the project requirments
	$ProjectReqtsSQL = "SELECT requirement,
								quantity,
								costperunit,
								projectreqid
						FROM projectreqts
						WHERE projectref ='" . $ProjectRef . "'
						ORDER BY projectreqid";

	$ErrMsg = _('The other project requirementscannot be retrieved because');
	$DbgMsg = _('The SQL statement that was used to retrieve the other project requirments was');
	$ProjectReqtsResult = DB_query($ProjectReqtsSQL, $ErrMsg, $DbgMsg);

	if (DB_num_rows($ProjectReqtsResult) > 0) {
		while ($MyRow = DB_fetch_array($ProjectReqtsResult)) {
			$_SESSION['Project' . $Identifier]->Add_To_ProjectRequirements($MyRow['requirement'], $MyRow['quantity'], $MyRow['costperunit'], $MyRow['projectreqid']);
		}
		/* add other project requirments lines*/
	} //end is there are project other project requirments to add
} // end if there was a header for the project
?>