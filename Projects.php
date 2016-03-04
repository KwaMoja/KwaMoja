<?php

include('includes/DefineProjectClass.php');
include('includes/session.inc');

if (isset($_GET['ModifyProjectNo'])) {
	$Title = _('Modify Project') . ' ' . $_GET['ModifyProjectNo'];
} else {
	$Title = _('Project Entry');
}

if (isset($_GET['DonorID'])) {
	$_POST['SelectedDonor'] = $_GET['DonorID'];
}

foreach ($_POST as $Name => $Value) {
	if (substr($Name, 0, 6) == 'Submit') {
		$Index = substr($Name, 6);
		$_POST['SelectedDonor'] = $_POST['SelectedDonor' . $Index];
	}
}

$ViewTopic = 'Projects';
$BookMark = 'CreateProject';
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

/*If the page is called is called without an identifier being set then
 * it must be either a new project, or the start of a modification of an
 * existing project, and so we must create a new identifier.
 *
 * The identifier only needs to be unique for this php session, so a
 * unix timestamp will be sufficient.
 */

if (!isset($_GET['identifier'])) {
	$Identifier = date('U');
} else {
	$Identifier = $_GET['identifier'];
}

if (isset($_GET['NewProject']) and isset($_SESSION['Project' . $Identifier])) {
	unset($_SESSION['Project' . $Identifier]);
	$_SESSION['ExistingProject'] = 0;
}

if (isset($_GET['NewProject']) and isset($_GET['SelectedDonor'])) {
	/*
	 * initialize a new project
	 */
	$_SESSION['ExistingProject'] = 0;
	unset($_SESSION['Project' . $Identifier]->ProjectBOM);
	unset($_SESSION['Project' . $Identifier]->ProjectReqts);
	unset($_SESSION['Project' . $Identifier]);
	/* initialize new class object */
	$_SESSION['Project' . $Identifier] = new Project;

	$_POST['SelectedDonor'] = $_GET['SelectedDonor'];

	/*The customer is checked for credit and the Project Object populated
	 * using the usual logic of when a customer is selected
	 * */
}

if (isset($_SESSION['Project' . $Identifier]) and (isset($_POST['EnterProjectBOM']) or isset($_POST['EnterProjectRequirements']))) {
	/**  Ensure session variables updated */

	$_SESSION['Project' . $Identifier]->ProjectRef = $_POST['ProjectRef'];
	$_SESSION['Project' . $Identifier]->ProjectDescription = $_POST['ProjectDescription'];
	$_SESSION['Project' . $Identifier]->LocCode = $_POST['LocCode'];
	$_SESSION['Project' . $Identifier]->RequiredDate = $_POST['RequiredDate'];
	$_SESSION['Project' . $Identifier]->CompletionDate = $_POST['CompletionDate'];
	$_SESSION['Project' . $Identifier]->DonorRef = $_POST['DonorRef'];
	$_SESSION['Project' . $Identifier]->ExRate = filter_number_format($_POST['ExRate']);


	/*User hit the button to enter line items -
	then meta refresh to Project_Items.php*/
	$InputError = false;
	if (mb_strlen($_SESSION['Project' . $Identifier]->ProjectRef) < 5) {
		prnMsg(_('The project reference must be entered (and be longer than 5 characters) before the requirements of the project can be setup'), 'warn');
		$InputError = true;
	}

	if (isset($_POST['EnterProjectBOM']) and !$InputError) {
		echo '<meta http-equiv="refresh" content="0; url=' . $RootPath . '/ProjectBOM.php?identifier=' . $Identifier . '" />';
		echo '<br />';
		prnMsg(_('You should automatically be forwarded to the entry of the Project line items page') . '. ' . _('If this does not happen') . ' (' . _('if the browser does not support META Refresh') . ') ' . '<a href="' . $RootPath . '/ProjectBOM.php?identifier=' . urlencode($Identifier) . '">' . _('click here') . '</a> ' . _('to continue'), 'info');
		include('includes/footer.inc');
		exit;
	}
	if (isset($_POST['EnterProjectRequirements']) and !$InputError) {
		echo '<meta http-equiv="refresh" content="0; url=' . $RootPath . '/ProjectOtherReqts.php?identifier=' . $Identifier . '" />';
		echo '<br />';
		prnMsg(_('You should automatically be forwarded to the entry of the Project requirements page') . '. ' . _('If this does not happen') . ' (' . _('if the browser does not support META Refresh') . ') ' . '<a href="' . $RootPath . '/ProjectOtherReqts.php?identifier=' . urlencode($Identifier) . '">' . _('click here') . '</a> ' . _('to continue'), 'info');
		include('includes/footer.inc');
		exit;
	}
}
/* end of if going to project BOM or project requriements */

echo '<div class="toplink">
		<a href="' . $RootPath . '/SelectProject.php">' . _('Back to Project Selection') . '</a>
	</div>';

//attempting to upload the drawing image file
if (isset($_FILES['Drawing']) and $_FILES['Drawing']['name'] != '' and $_SESSION['Project' . $Identifier]->ProjectRef != '') {

	$Result = $_FILES['Drawing']['error'];
	$UploadTheFile = 'Yes'; //Assume all is well to start off with
	$FileName = $_SESSION['part_pics_dir'] . '/' . $_SESSION['Project' . $Identifier]->ProjectRef . '.jpg';

	//But check for the worst
	if (mb_strtoupper(mb_substr(trim($_FILES['Drawing']['name']), mb_strlen($_FILES['Drawing']['name']) - 3)) != 'JPG') {
		prnMsg(_('Only jpg files are supported - a file extension of .jpg is expected'), 'warn');
		$UploadTheFile = 'No';
	} elseif ($_FILES['Drawing']['size'] > ($_SESSION['MaxImageSize'] * 1024)) { //File Size Check
		prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'], 'warn');
		$UploadTheFile = 'No';
	} elseif ($_FILES['Drawing']['type'] == 'text/plain') { //File Type Check
		prnMsg(_('Only graphics files can be uploaded'), 'warn');
		$UploadTheFile = 'No';
	} elseif (file_exists($FileName)) {
		prnMsg(_('Attempting to overwrite an existing item image'), 'warn');
		$Result = unlink($FileName);
		if (!$Result) {
			prnMsg(_('The existing image could not be removed'), 'error');
			$UploadTheFile = 'No';
		}
	}

	if ($UploadTheFile == 'Yes') {
		$Result = move_uploaded_file($_FILES['Drawing']['tmp_name'], $FileName);
		$Message = ($Result) ? _('File url') . '<a href="' . $FileName . '">' . $FileName . '</a>' : _('Something is wrong with uploading the file');
	}
}


/*The page can be called with ModifyProjectRef=x where x is a project
 * reference. The page then looks up the details of project x and allows
 * these details to be modified */

if (isset($_GET['ModifyProjectRef'])) {

	if (isset($_SESSION['Project' . $Identifier])) {
		unset($_SESSION['Project' . $Identifier]->ProjectBOM);
		unset($_SESSION['Project' . $Identifier]->ProjectReqts);
		unset($_SESSION['Project' . $Identifier]);
	}

	$_SESSION['ExistingProject'] = $_GET['ModifyProjectRef'];
	$_SESSION['RequireDonorSelection'] = 0;
	$_SESSION['Project' . $Identifier] = new Project;

	/*read in all the guff from the selected project into the project Class variable  */
	$ProjectRef = $_GET['ModifyProjectRef'];
	include('includes/Project_Readin.php');

} // its an existing project to readin

if (isset($_POST['CancelProject'])) {
	/*The cancel button on the header screen - to delete the project */
	$OK_to_delete = true; //assume this in the first instance
	if (!isset($_SESSION['ExistingProject']) or $_SESSION['ExistingProject'] != 0) {
		/* need to check that not already ordered by the customer - status = 100  */
		if ($_SESSION['Project' . $Identifier]->Status == 2) {
			$OK_to_delete = false;
			prnMsg(_('The project has already been ordered by the customer the order must also be deleted first before the project can be deleted'), 'warn');
		}
	}

	if ($OK_to_delete == true) {
		$SQL = "DELETE FROM projectbom WHERE projectref='" . $_SESSION['Project' . $Identifier]->ProjectRef . "'";
		$ErrMsg = _('The project bill of materials could not be deleted because');
		$DelResult = DB_query($SQL, $ErrMsg);
		$SQL = "DELETE FROM projectreqts WHERE projectref='" . $_SESSION['Project' . $Identifier]->ProjectRef . "'";
		$ErrMsg = _('The project requirements could not be deleted because');
		$DelResult = DB_query($SQL, $ErrMsg);
		$SQL = "DELETE FROM projects WHERE projectref='" . $_SESSION['Project' . $Identifier]->ProjectRef . "'";
		$ErrMsg = _('The project could not be deleted because');
		$DelResult = DB_query($SQL, $ErrMsg);

		if ($_SESSION['Project' . $Identifier]->Status == 1) {
			$SQL = "DELETE FROM projectbudgetdetails WHERE orderno='" . $_SESSION['Project' . $Identifier]->OrderNo . "'";
			$ErrMsg = _('The budget lines for the project could not be deleted because');
			$DelResult = DB_query($SQL, $ErrMsg);
			$SQL = "DELETE FROM projectbudgets WHERE orderno='" . $_SESSION['Project' . $Identifier]->OrderNo . "'";
			$ErrMsg = _('The budget for the project could not be deleted because');
			$DelResult = DB_query($SQL, $ErrMsg);
		}
		prnMsg(_('Project') . ' ' . $_SESSION['Project' . $Identifier]->ProjectRef . ' ' . _('has been cancelled'), 'success');
		unset($_SESSION['ExistingProject']);
		unset($_SESSION['Project' . $Identifier]->ProjectBOM);
		unset($_SESSION['Project' . $Identifier]->ProjectReqts);
		unset($_SESSION['Project' . $Identifier]);
	}
}

if (!isset($_SESSION['Project' . $Identifier])) {
	/* It must be a new project being created
	 * $_SESSION['Project'.$Identifier] would be set up from the order modification
	 * code above if a modification to an existing project. Also
	 * $ExistingProject would be set to the ProjectRef
	 * */
	$_SESSION['ExistingProject'] = 0;
	$_SESSION['Project' . $Identifier] = new Project;

	if ($_SESSION['Project' . $Identifier]->DonorNo == '' or !isset($_SESSION['Project' . $Identifier]->DonorNo)) {

		/* a session variable will have to maintain if a supplier
		 * has been selected for the order or not the session
		 * variable DonorID holds the supplier code already
		 * as determined from user id /password entry  */
		$_SESSION['RequireDonorSelection'] = 1;
	} else {
		$_SESSION['RequireDonorSelection'] = 0;
	}
}

if (isset($_POST['CommitProject']) or isset($_POST['CreateBudget'])) {
	/*This is the bit where the project object is commited to the database after a bit of error checking */

	//First update the session['Project'.$Identifier] variable with all inputs from the form

	$InputError = False; //assume no errors on input then test for errors
	if (mb_strlen($_POST['ProjectRef']) < 2) {
		prnMsg(_('The project reference is expected to be more than 2 characters long. Please alter the project reference before proceeding.'), 'error');
		$InputError = true;
	}
	if (ContainsIllegalCharacters($_POST['ProjectRef'])) {
		prnMsg(_('The project reference cannot contain any spaces, slashes, or inverted commas. Please alter the project reference before proceeding.'), 'error');
		$InputError = true;
	}

	//The projectRef cannot be the same as an existing stockid or projectref
	$Result = DB_query("SELECT stockid FROM stockmaster WHERE stockid='" . $_POST['ProjectRef'] . "'");
	if (DB_num_rows($Result) == 1 and $_SESSION['Project' . $Identifier]->Status == 0) {
		prnMsg(_('The project reference cannot be the same as a previously created stock item. Please modify the project reference before continuing'), 'error');
		$InputError = true;
	}
	if (mb_strlen($_POST['ProjectDescription']) < 10) {
		prnMsg(_('The project description is expected to be more than 10 characters long. Please alter the project description in full before proceeding.'), 'error');
		$InputError = true;
	}
	if (!is_date($_POST['RequiredDate'])) {
		prnMsg(_('The date the project is required to be started by must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
		$InputError = true;
	}
	if (!is_date($_POST['CompletionDate'])) {
		prnMsg(_('The date the project is required to be completed by must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
		$InputError = true;
	}
	if (Date1GreaterThanDate2(Date($_SESSION['DefaultDateFormat']), $_POST['RequiredDate']) and $_POST['RequiredDate'] != '') {
		prnMsg(_('The date that the project is to be started by is expected to be a date in the future. Make the start date a date after today before proceeding.'), 'error');
		$InputError = true;
	}
	if (Date1GreaterThanDate2($_POST['RequiredDate'], $_POST['CompletionDate']) and $_POST['CompletionDate'] != '') {
		prnMsg(_('The date that the project is to be completed must be later than the start date.'), 'error');
		$InputError = true;
	}

	if (!$InputError) {
		$_SESSION['Project' . $Identifier]->ProjectRef = $_POST['ProjectRef'];
		$_SESSION['Project' . $Identifier]->ProjectDescription = $_POST['ProjectDescription'];
		$_SESSION['Project' . $Identifier]->LocCode = $_POST['LocCode'];
		$_SESSION['Project' . $Identifier]->RequiredDate = $_POST['RequiredDate'];
		$_SESSION['Project' . $Identifier]->CompletionDate = $_POST['CompletionDate'];
		$_SESSION['Project' . $Identifier]->Status = $_POST['Status'];
		$_SESSION['Project' . $Identifier]->DonorRef = $_POST['DonorRef'];
		$_SESSION['Project' . $Identifier]->ExRate = filter_number_format($_POST['ExRate']);
	}
	$SQL = "SELECT projectref,
					donorno,
					categoryid,
					loccode,
					requireddate,
					completiondate,
					margin,
					customerref,
					exrate,
					status
			FROM projects
			WHERE projectref='" . $_POST['ProjectRef'] . "'";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 1) { // then we have an existing project with this projectref
		$ExistingProject = DB_fetch_array($Result);
		if ($ExistingProject['donorno'] != $_SESSION['Project' . $Identifier]->DonorNo) {
			prnMsg(_('The project reference cannot be the same as a previously created project for another customer. Please modify the project reference before continuing'), 'error');
			$InputError = true;
		}

		if ($ExistingProject['status'] <= 1 and !$InputError) {
			//then we can accept any changes at all do an update on the whole lot
			$SQL = "UPDATE projects SET requireddate = '" . FormatDateForSQL($_POST['RequiredDate']) . "',
										completiondate = '" . FormatDateForSQL($_POST['CompletionDate']) . "',
										loccode='" . $_POST['LocCode'] . "',
										customerref = '" . $_POST['DonorRef'] . "',
										exrate = '" . filter_number_format($_POST['ExRate']) . "'
							WHERE projectref ='" . $_POST['ProjectRef'] . "'";
			$ErrMsg = _('Cannot update the project because');
			$Result = DB_query($SQL, $ErrMsg);
			/* also need to update the items on the project BOM  - delete the existing project BOM then add these items*/
			$Result = DB_query("DELETE FROM projectbom WHERE projectref='" . $_POST['ProjectRef'] . "'");
			$ErrMsg = _('Could not add a component to the project bill of material');
			foreach ($_SESSION['Project' . $Identifier]->ProjectBOM as $Component) {
				$SQL = "INSERT INTO projectbom (projectref,
												stockid,
												quantity,
												requiredby)
											VALUES ( '" . $_POST['ProjectRef'] . "',
												'" . $Component->StockID . "',
												'" . $Component->Quantity . "',
												'" . $Component->RequiredBy . "')";
				$Result = DB_query($SQL, $ErrMsg);
			}

			/*also need to update the items on the project requirements  - delete the existing database entries then add these */
			$Result = DB_query("DELETE FROM projectreqts WHERE projectref='" . $_POST['ProjectRef'] . "'");
			$ErrMsg = _('Could not add a requirement to the project requirements');
			foreach ($_SESSION['Project' . $Identifier]->ProjectReqts as $Requirement) {
				$SQL = "INSERT INTO projectreqts (projectref,
													requirement,
													costperunit,
													quantity)
												VALUES (
													'" . $_POST['ProjectRef'] . "',
													'" . $Requirement->Requirement . "',
													'" . $Requirement->CostPerUnit . "',
													'" . $Requirement->Quantity . "')";
				$Result = DB_query($SQL, $ErrMsg);
			}

			prnMsg(_('The changes to the project have been committed to the database'), 'success');
		}
		if ($ExistingProject['status'] == 1 and !$InputError) {
			//then the budget will need to be updated with the revised project cost if necessary
			$ProjectBOMCost = 0;
			foreach ($_SESSION['Project' . $Identifier]->ProjectBOM as $Component) {
				$ProjectBOMCost += ($Component->ItemCost * $Component->Quantity);
			}
			$ProjectReqtsCost = 0;
			foreach ($_SESSION['Project' . $Identifier]->ProjectReqts as $Requirement) {
				$ProjectReqtsCost += ($Requirement->CostPerUnit * $Requirement->Quantity);
			}
			$ProjectCost = $ProjectReqtsCost + $ProjectBOMCost;
			$ProjectPrice = ($ProjectBOMCost + $ProjectReqtsCost) / ((100 - $_SESSION['Project' . $Identifier]->Margin) / 100);

			$SQL = "UPDATE stockmaster SET description='" . $_SESSION['Project' . $Identifier]->ProjectDescription . "',
											longdescription='" . $_SESSION['Project' . $Identifier]->ProjectDescription . "',
											categoryid = '" . $_SESSION['Project' . $Identifier]->CategoryID . "'
										WHERE stockid ='" . $_SESSION['Project' . $Identifier]->ProjectRef . "'";
			$ErrMsg = _('The project item could not be updated because');
			$DbgMsg = _('The SQL that was used to update the project item failed was');
			$InsertNewItemResult = DB_query($SQL, $ErrMsg, $DbgMsg);

			$SQL = "UPDATE stockcosts SET succeeded=1
									WHERE stockid='" . $_SESSION['Project' . $Identifier]->ProjectRef . "'
										AND succeeded=0";
			$Result = DB_query($SQL);
			$SQL = "INSERT INTO stockcosts VALUES ('" . $_SESSION['Project' . $Identifier]->ProjectRef . "',
												'" . $ProjectCost . "',
												0,
												0,
												CURRENT_TIME,
												0)";
			$Result = DB_query($SQL);

			//update the budget
			$SQL = "UPDATE projectbudgetdetails
						SET unitprice = '" . $ProjectPrice * $_SESSION['Project' . $Identifier]->ExRate . "'
						WHERE stkcode='" . $_SESSION['Project' . $Identifier]->ProjectRef . "'
						AND orderno='" . $_SESSION['Project' . $Identifier]->OrderNo . "'";
			$ErrMsg = _('The project quotation could not be updated because');
			$DbgMsg = _('The SQL that failed to update the budget was');
			$UpdQuoteResult = DB_query($SQL, $ErrMsg, $DbgMsg);
			prnMsg(_('The project budget has been updated based on the new project cost and margin'), 'success');

		}
		if ($ExistingProject['status'] == 0 and $_POST['Status'] == 1) {
			/*we are updating the status on the project to a quotation so we need to
			 * add a new item for the project into the stockmaster
			 * add a salesorder header and detail as a quotation for the item
			 */


		}
	} elseif (!$InputError) {
		/*Its a new project - so insert */

		$SQL = "INSERT INTO projects ( projectref,
										donorno,
										projectdescription,
										loccode,
										requireddate,
										completiondate,
										customerref,
										exrate)
					VALUES ('" . $_POST['ProjectRef'] . "',
							'" . $_SESSION['Project' . $Identifier]->DonorNo . "',
							'" . $_POST['ProjectDescription'] . "',
							'" . $_POST['LocCode'] . "',
							'" . FormatDateForSQL($_POST['RequiredDate']) . "',
							'" . FormatDateForSQL($_POST['CompletionDate']) . "',
							'" . $_POST['DonorRef'] . "',
							'" . filter_number_format($_POST['ExRate']) . "')";

		$ErrMsg = _('The new project could not be added because');
		$Result = DB_query($SQL, $ErrMsg);

		/*Also need to add the reqts and contracbom*/
		$ErrMsg = _('Could not add a component to the project bill of material');
		foreach ($_SESSION['Project' . $Identifier]->ProjectBOM as $Component) {
			$SQL = "INSERT INTO projectbom (projectref,
											stockid,
											quantity,
											requiredby)
							VALUES ('" . $_POST['ProjectRef'] . "',
									'" . $Component->StockID . "',
									'" . $Component->Quantity . "',
									'" . $Component->RequiredBy . "',
									)";
			$Result = DB_query($SQL, $ErrMsg);
		}

		$ErrMsg = _('Could not add a requirement to the project requirements');
		foreach ($_SESSION['Project' . $Identifier]->ProjectReqts as $Requirement) {
			$SQL = "INSERT INTO projectreqts (projectref,
												requirement,
												costperunit,
												quantity)
							VALUES ( '" . $_POST['ProjectRef'] . "',
									'" . $Requirement->Requirement . "',
									'" . $Requirement->CostPerUnit . "',
									'" . $Requirement->Quantity . "')";
			$Result = DB_query($SQL, $ErrMsg);
		}
		prnMsg(_('The new project has been added to the database'), 'success');

	} //end of adding a new project
} //end of commital to database

if (isset($_POST['CreateBudget']) and !$InputError) {
	//Create a quotation for the project as entered
	//First need to create the item in stockmaster

	//calculate the item's project cost
	$ProjectBOMCost = 0;
	foreach ($_SESSION['Project' . $Identifier]->ProjectBOM as $Component) {
		$ProjectBOMCost += ($Component->ItemCost * $Component->Quantity);
	}
	$ProjectReqtsCost = 0;
	foreach ($_SESSION['Project' . $Identifier]->ProjectReqts as $Requirement) {
		$ProjectReqtsCost += ($Requirement->CostPerUnit * $Requirement->Quantity);
	}
	$ProjectCost = $ProjectReqtsCost + $ProjectBOMCost;
	$ProjectPrice = ($ProjectBOMCost + $ProjectReqtsCost) / ((100 - $_SESSION['Project' . $Identifier]->Margin) / 100);

	//Check if the item exists already
	$SQL = "SELECT stockid FROM stockmaster WHERE stockid='" . $_SESSION['Project' . $Identifier]->ProjectRef . "'";
	$ErrMsg = _('The item could not be retrieved because');
	$DbgMsg = _('The SQL that was used to find the item failed was');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
	if (DB_num_rows($Result) == 0) { //then the item doesn't currently exist so add it

		$SQL = "INSERT INTO stockmaster (stockid,
										description,
										longdescription,
										categoryid,
										mbflag,
										taxcatid)
							VALUES ('" . $_SESSION['Project' . $Identifier]->ProjectRef . "',
									'" . $_SESSION['Project' . $Identifier]->ProjectDescription . "',
									'" . $_SESSION['Project' . $Identifier]->ProjectDescription . "',
									'" . $_SESSION['Project' . $Identifier]->CategoryID . "',
									'M',
									'" . $_SESSION['DefaultTaxCategory'] . "')";
		$ErrMsg = _('The new project item could not be added because');
		$DbgMsg = _('The SQL that was used to insert the project item failed was');
		$InsertNewItemResult = DB_query($SQL, $ErrMsg, $DbgMsg);

		$SQL = "INSERT INTO stockcosts VALUES ( '" . $_SESSION['Project' . $Identifier]->ProjectRef . "',
												'" . $ProjectCost . "',
												0,
												0,
												CURRENT_TIME,
												0)";
		$ErrMsg = _('The new project costs could not be added because');
		$DbgMsg = _('The SQL that was used to insert the project costs failed was');
		$InsertItemCostResult = DB_query($SQL, $ErrMsg, $DbgMsg);

		$SQL = "INSERT INTO locstock (loccode,
										stockid)
						SELECT locations.loccode,
								'" . $_SESSION['Project' . $Identifier]->ProjectRef . "'
						FROM locations";

		$ErrMsg = _('The locations for the item') . ' ' . $_SESSION['Project' . $Identifier]->ProjectRef . ' ' . _('could not be added because');
		$DbgMsg = _('NB Locations records can be added by opening the utility page') . ' <i>Z_MakeStockLocns.php</i> ' . _('The SQL that was used to add the location records that failed was');
		$InsLocnsResult = DB_query($SQL, $ErrMsg, $DbgMsg);
	}
	//now add the quotation for the item

	//first need to get some more details from the customer/branch record
	$SQL = "SELECT name,
					address1,
					address2,
					address3,
					address4,
					address5,
					address6
				FROM donors
				WHERE donorno='" . $_SESSION['Project' . $Identifier]->DonorNo . "'";
	$ErrMsg = _('The customer and branch details could not be retrieved because');
	$DbgMsg = _('The SQL that was used to find the customer and branch details failed was');
	$DonorDetailsResult = DB_query($SQL, $ErrMsg, $DbgMsg);

	$DonorDetailsRow = DB_fetch_array($DonorDetailsResult);

	//start a DB transaction
	$Result = DB_Txn_Begin();
	$OrderNo = GetNextTransNo(30);
	$HeaderSQL = "INSERT INTO projectbudgets (	orderno,
											donorno,
											customerref,
											orddate,
											deliverto,
											deladd1,
											deladd2,
											deladd3,
											deladd4,
											deladd5,
											deladd6,
											fromstkloc,
											deliverydate,
											quotedate,
											quotation)
										VALUES (
											'" . $OrderNo . "',
											'" . $_SESSION['Project' . $Identifier]->DonorNo . "',
											'" . $_SESSION['Project' . $Identifier]->DonorRef . "',
											'" . Date('Y-m-d H:i') . "',
											'" . $DonorDetailsRow['name'] . "',
											'" . $DonorDetailsRow['address1'] . "',
											'" . $DonorDetailsRow['address2'] . "',
											'" . $DonorDetailsRow['address3'] . "',
											'" . $DonorDetailsRow['address4'] . "',
											'" . $DonorDetailsRow['address5'] . "',
											'" . $DonorDetailsRow['address6'] . "',
											'" . $_SESSION['Project' . $Identifier]->LocCode . "',
											'" . FormatDateForSQL($_SESSION['Project' . $Identifier]->CompletionDate) . "',
											CURRENT_DATE,
											'1' )";

	$ErrMsg = _('The quotation cannot be added because');
	$InsertQryResult = DB_query($HeaderSQL, $ErrMsg, true);
	$LineItemSQL = "INSERT INTO projectbudgetdetails ( orderlineno,
													orderno,
													stkcode,
													unitprice,
													quantity,
													poline,
													itemdue)
										VALUES ('0',
												'" . $OrderNo . "',
												'" . $_SESSION['Project' . $Identifier]->ProjectRef . "',
												'" . ($ProjectPrice * $_SESSION['Project' . $Identifier]->ExRate) . "',
												'1',
												'" . $_SESSION['Project' . $Identifier]->DonorRef . "',
												'" . FormatDateForSQL($_SESSION['Project' . $Identifier]->RequiredDate) . "')";
	$DbgMsg = _('The SQL that failed was');
	$ErrMsg = _('Unable to add the quotation line');
	$Ins_LineItemResult = DB_query($LineItemSQL, $ErrMsg, $DbgMsg, true);
	//end of adding the quotation to projectbudgets/details

	//make the status of the project 1 - to indicate that it is now quoted
	$SQL = "UPDATE projects SET budgetno='" . $OrderNo . "',
								status='" . 1 . "'
						WHERE projectref='" . DB_escape_string($_SESSION['Project' . $Identifier]->ProjectRef) . "'";
	$ErrMsg = _('Unable to update the project status and order number because');
	$UpdProjectResult = DB_query($SQL, $ErrMsg, $DbgMsg, true);
	$Result = DB_Txn_Commit();
	$_SESSION['Project' . $Identifier]->Status = 1;
	$_SESSION['Project' . $Identifier]->OrderNo = $OrderNo;
	prnMsg(_('The project has been made into quotation number') . ' ' . $OrderNo, 'info');
	echo '<br /><a href="' . $RootPath . '/SelectSalesOrder.php?OrderNumber=' . urlencode($OrderNo) . '&amp;Quotations=Quotes_Only">' . _('Go to quotation number') . ': ' . $OrderNo . '</a>';

} //end of if making a quotation

if (isset($_POST['SearchDonors'])) {

	$_POST['DonorKeywords'] = mb_strtoupper(trim($_POST['DonorKeywords']));
	$SearchString = '%' . str_replace(' ', '%', $_POST['DonorKeywords']) . '%';

	$SQL = "SELECT name,
					donorno
				FROM donors
				WHERE name " . LIKE . " '" . $SearchString . "'
					AND donorno " . LIKE . " '%" . $_POST['DonorCode'] . "%'
				ORDER BY name";


	$ErrMsg = _('The donor records requested cannot be retrieved because');
	$Result_DonorSelect = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result_DonorSelect) == 0) {
		prnMsg(_('No Donor records contain the search criteria') . ' - ' . _('please try again'), 'info');
	}
}
/*one of keywords or custcode was more than a zero length string */

if (isset($_POST['SelectedDonor'])) {

	$_SESSION['Project' . $Identifier]->DonorNo = $_POST['SelectedDonor'];

	$SQL = "SELECT donors.name,
					donors.currcode,
					currencies.rate
			FROM donors
			INNER JOIN currencies
				ON donors.currcode=currencies.currabrev
			WHERE donors.donorno='" . $_SESSION['Project' . $Identifier]->DonorNo . "'";

	$ErrMsg = _('The customer record selected') . ': ' . $_SESSION['Project' . $Identifier]->DonorNo . ' ' . _('cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the customer details and failed was');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
	$MyRow = DB_fetch_array($Result);
	if (DB_num_rows($Result) == 0) {
		prnMsg(_('The customer details were unable to be retrieved'), 'error');
		if ($Debug == 1) {
			prnMsg(_('The SQL used that failed to get the customer details was') . ':<br />' . $SQL, 'error');
		}
	} else {
		$_SESSION['RequireDonorSelection'] = 0;
		$_SESSION['Project' . $Identifier]->DonorName = $MyRow['name'];
		$_SESSION['Project' . $Identifier]->CurrCode = $MyRow['currcode'];
		$_SESSION['Project' . $Identifier]->ExRate = $MyRow['rate'];

		if ($_SESSION['CheckCreditLimits'] > 0) {
			/*Check credit limits is 1 for warn and 2 for prohibit projects */
			$CreditAvailable = GetCreditAvailable($_SESSION['Project' . $Identifier]->DonorNo);
			if ($_SESSION['CheckCreditLimits'] == 1 and $CreditAvailable <= 0) {
				prnMsg(_('The') . ' ' . $_SESSION['Project' . $Identifier]->DonorName . ' ' . _('account is currently at or over their credit limit'), 'warn');
			} elseif ($_SESSION['CheckCreditLimits'] == 2 and $CreditAvailable <= 0) {
				prnMsg(_('No more orders can be placed by') . ' ' . $MyRow[0] . ' ' . _(' their account is currently at or over their credit limit'), 'warn');
				include('includes/footer.inc');
				exit;
			}
		}
	} //a customer was retrieved ok
} //end if a customer has just been selected


if (!isset($_SESSION['Project' . $Identifier]->DonorNo) or $_SESSION['Project' . $Identifier]->DonorNo == '') {

	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/contract.png" title="' . _('Project') . '" alt="" />' . ' ' . _('Project: Select Donor') . '</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $Identifier . '" name="DonorSelection" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table cellpadding="3" class="selection">
			<tr>
			<td><h5>' . _('Part of the Donor Name') . ':</h5></td>
			<td><input tabindex="1" type="text" name="DonorKeywords" size="20" autofocus="autofocus" maxlength="25" /></td>
			<td><h2><b>' . _('OR') . '</b></h2></td>
			<td><h5>' . _('Part of the Donor Code') . ':</h5></td>
			<td><input tabindex="2" type="text" name="DonorCode" size="15" maxlength="18" /></td>
		</tr>
		</table>
		<div class="centre">
			<input tabindex="4" type="submit" name="SearchDonors" value="' . _('Search Now') . '" />
			<input tabindex="5" type="submit" name="reset" value="' . _('Reset') . '" />
		</div>';

	if (isset($Result_DonorSelect)) {

		echo '<table cellpadding="2" class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('Donor Number') . '</th>
						<th class="SortedColumn">' . _('Name') . '</th>
					</tr>
				</thead>';

		$k = 0; //row counter to determine background colour
		$j = 0;
		$LastDonor = '';
		echo '<tbody>';
		while ($MyRow = DB_fetch_array($Result_DonorSelect)) {

			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			if ($LastDonor != $MyRow['name']) {
				echo '<td>' . htmlentities($MyRow['donorno'], ENT_QUOTES, 'UTF-8') . '</td>';
			} else {
				echo '<td></td>';
			}
			echo '<td>
					<input type="hidden" name="SelectedDonor' . $j . '" value="' . $MyRow['donorno'] . '" />
					<input type="submit" name="Submit' . $j . '" value="' . htmlentities($MyRow['name'], ENT_QUOTES, 'UTF-8') . '" />
				</td>
			</tr>';
			$LastDonor = $MyRow['name'];
			++$j;
			//end of page full new headings if
		}
		//end of while loop

		echo '</tbody>
			</table>
		</form>';
	} //end if results to show

	//end if RequireDonorSelection
} else {
	/*A customer is already selected so get into the project setup proper */

	echo '<form name="ProjectEntry" enctype="multipart/form-data" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $Identifier . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text" >
			<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/contract.png" title="' . _('Project') . '" alt="" />' . _('Donor') . ': ' . $_SESSION['Project' . $Identifier]->DonorName . '<br />';

	if ($_SESSION['CompanyRecord']['currencydefault'] != $_SESSION['Project' . $Identifier]->CurrCode) {
		echo ' - ' . _('All amounts stated in') . ' ' . $_SESSION['Project' . $Identifier]->CurrCode . '<br />';
	}
	if ($_SESSION['ExistingProject']) {
		echo _('Modify Project') . ': ' . $_SESSION['Project' . $Identifier]->ProjectRef;
	}
	echo '</p>';

	/*Set up form for entry of project header stuff */

	echo '<table class="selection">
			<tr>
				<td>' . _('Project Reference') . ':</td>
				<td>';
	if ($_SESSION['Project' . $Identifier]->Status == 0) {
		/*Then the project has not become an order yet and we can allow changes to the ProjectRef */
		echo '<input type="text" name="ProjectRef" size="21" autofocus="autofocus" required="required" maxlength="20" value="' . $_SESSION['Project' . $Identifier]->ProjectRef . '" />';
	} else {
		/*Just show the project Ref - dont allow modification */
		echo '<input type="hidden" name="ProjectRef" value="' . $_SESSION['Project' . $Identifier]->ProjectRef . '" />' . $_SESSION['Project' . $Identifier]->ProjectRef;
	}
	echo '</td></tr>';

	$SQL = "SELECT locations.loccode,
					locationname
				FROM locations
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canupd=1";
	$ErrMsg = _('The stock locations could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve stock locations and failed was');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

	echo '<tr>
			<td>' . _('Location') . ':</td>
			<td><select name="LocCode" >';
	while ($MyRow = DB_fetch_array($Result)) {
		if (!isset($_SESSION['Project' . $Identifier]->LocCode) or $MyRow['loccode'] == $_SESSION['Project' . $Identifier]->LocCode) {
			echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	}

	echo '</select>
				</td>
			</tr>';

	echo '<tr>
			<td>' . _('Project Description') . ':</td>
			<td><textarea name="ProjectDescription" required="required" style="width:100%" rows="5" cols="40">' . $_SESSION['Project' . $Identifier]->ProjectDescription . '</textarea></td>
		</tr>';

    if (!isset($_SESSION['Project' . $Identifier]->RequiredDate)) {
            $_SESSION['Project' . $Identifier]->RequiredDate = DateAdd(date($_SESSION['DefaultDateFormat']), 'm', 1);
	}

	echo '<tr>
			<td>' . _('Required Start Date') . ':</td>
			<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="RequiredDate" size="11" value="' . $_SESSION['Project' . $Identifier]->RequiredDate . '" /></td>
		</tr>';

	if (!isset($_SESSION['Project' . $Identifier]->CompletionDate)) {
		$_SESSION['Project' . $Identifier]->CompletionDate = DateAdd(date($_SESSION['DefaultDateFormat']), 'm', 1);
	}

	echo '<tr>
			<td>' . _('Estimated Completion Date') . ':</td>
			<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="CompletionDate" size="11" value="' . $_SESSION['Project' . $Identifier]->CompletionDate . '" /></td>
		</tr>';

	echo '<tr>
			<td>' . _('Donor Reference') . ':</td>
			<td><input type="text" name="DonorRef" size="21" maxlength="20" value="' . $_SESSION['Project' . $Identifier]->DonorRef . '" /></td>
		</tr>';

	if ($_SESSION['CompanyRecord']['currencydefault'] != $_SESSION['Project' . $Identifier]->CurrCode) {
		echo '<tr>
				<td>' . $_SESSION['Project' . $Identifier]->CurrCode . ' ' . _('Exchange Rate') . ':</td>
				<td><input class="number" type="text" name="ExRate" size="10" required="required" maxlength="10" value="' . locale_number_format($_SESSION['Project' . $Identifier]->ExRate, 'Variable') . '" /></td>
			</tr>';
	} else {
		echo '<input type="hidden" name="ExRate" value="' . locale_number_format($_SESSION['Project' . $Identifier]->ExRate, 'Variable') . '" />';
	}

	echo '<tr>
			<td>' . _('Project Status') . ':</td>
			<td>';

	$StatusText = array();
	$StatusText[0] = _('Setup');
	$StatusText[1] = _('Quote');
	$StatusText[2] = _('Completed');
	if ($_SESSION['Project' . $Identifier]->Status == 0) {
		echo _('Project Setup');
	} elseif ($_SESSION['Project' . $Identifier]->Status == 1) {
		echo _('Donor Quoted');
	} elseif ($_SESSION['Project' . $Identifier]->Status == 2) {
		echo _('Order Placed');
	}
	echo '<input type="hidden" name="Status" value="' . $_SESSION['Project' . $Identifier]->Status . '" />';
	echo '</td>
		</tr>';
	if ($_SESSION['Project' . $Identifier]->Status >= 1) {
		echo '<tr>
				<td>' . _('Quotation Reference/Sales Order No') . ':</td>
				<td><a href="' . $RootPath . '/SelectSalesOrder.php?OrderNumber=' . urlencode($_SESSION['Project' . $Identifier]->OrderNo) . '&amp;Quotations=Quotes_Only">' . $_SESSION['Project' . $Identifier]->OrderNo . '</a></td>
			</tr>';
	}
	if ($_SESSION['Project' . $Identifier]->Status != 2 and isset($_SESSION['Project' . $Identifier]->WO)) {
		echo '<tr>
				<td>' . _('Project Work Order Ref') . ':</td>
				<td>' . $_SESSION['Project' . $Identifier]->WO . '</td>
			</tr>';
	}
	echo '</table>';

	echo '<table>
			<tr>
				<td>
					<table class="selection">
						<tr>
							<th colspan="6">' . _('Stock Items Required') . '</th>
						</tr>';
	$ProjectBOMCost = 0;
	if (count($_SESSION['Project' . $Identifier]->ProjectBOM) != 0) {
		echo '<tr>
				<th>' . _('Item Code') . '</th>
				<th>' . _('Item Description') . '</th>
				<th>' . _('Quantity') . '</th>
				<th>' . _('Unit') . '</th>
				<th>' . _('Unit Cost') . '</th>
				<th>' . _('Total Cost') . '</th>
			</tr>';

		foreach ($_SESSION['Project' . $Identifier]->ProjectBOM as $Component) {
			echo '<tr>
					<td>' . $Component->StockID . '</td>
					<td>' . $Component->ItemDescription . '</td>
					<td class="number">' . locale_number_format($Component->Quantity, $Component->DecimalPlaces) . '</td>
					<td>' . $Component->UOM . '</td>
					<td class="number">' . locale_number_format($Component->ItemCost, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format(($Component->ItemCost * $Component->Quantity), $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				</tr>';
			$ProjectBOMCost += ($Component->ItemCost * $Component->Quantity);
		}
		echo '<tr>
				<th colspan="5"><b>' . _('Total stock cost') . '</b></th>
					<th class="number"><b>' . locale_number_format($ProjectBOMCost, $_SESSION['CompanyRecord']['decimalplaces']) . '</b></th>
				</tr>';
	} else { //there are no items set up against this project
		echo '<tr>
				<td colspan="6"><i>' . _('None Entered') . '</i></td>
			</tr>';
	}
	echo '</table></td>'; //end of project BOM table
	echo '<td valign="top">
			<table class="selection">
				<tr>
					<th colspan="4">' . _('Other Requirements') . '</th>
				</tr>';
	$ProjectReqtsCost = 0;
	if (count($_SESSION['Project' . $Identifier]->ProjectReqts) != 0) {
		echo '<tr>
				<th>' . _('Requirement') . '</th>
				<th>' . _('Quantity') . '</th>
				<th>' . _('Unit Cost') . '</th>
				<th>' . _('Total Cost') . '</th>
			</tr>';
		foreach ($_SESSION['Project' . $Identifier]->ProjectReqts as $Requirement) {
			echo '<tr>
					<td>' . $Requirement->Requirement . '</td>
					<td class="number">' . locale_number_format($Requirement->Quantity, 'Variable') . '</td>
					<td class="number">' . locale_number_format($Requirement->CostPerUnit, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format(($Requirement->CostPerUnit * $Requirement->Quantity), $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				</tr>';
			$ProjectReqtsCost += ($Requirement->CostPerUnit * $Requirement->Quantity);
		}
		echo '<tr>
				<th colspan="3"><b>' . _('Total other costs') . '</b></th>
				<th class="number"><b>' . locale_number_format($ProjectReqtsCost, $_SESSION['CompanyRecord']['decimalplaces']) . '</b></th>
			</tr>';
	} else { //there are no items set up against this project
		echo '<tr>
				<td colspan="4"><i>' . _('None Entered') . '</i></td>
			</tr>';
	}
	echo '</table></td></tr></table>';
	echo '<table class="selection">
			<tr>
				<th>' . _('Total Project Cost') . '</th>
				<th class="number">' . locale_number_format(($ProjectBOMCost + $ProjectReqtsCost), $_SESSION['CompanyRecord']['decimalplaces']) . '</th>
			</tr>
		</table>';

	echo '<p></p>';
	echo '<div class="centre">
			<input type="submit" name="EnterProjectBOM" value="' . _('Enter Items Required') . '" />
			<input type="submit" name="EnterProjectRequirements" value="' . _('Enter Other Requirements') . '" />';
	echo '<input type="submit" name="CommitProject" value="' . _('Commit Changes') . '" />';

	echo '</div>';
	if ($_SESSION['Project' . $Identifier]->Status != 2) {
		echo '<div class="centre">
				 <input type="submit" name="CancelProject" value="' . _('Cancel and Delete Project') . '" />
			  </div>';
	}
	echo '</form>';
}
/*end of if customer selected  and entering project header*/

include('includes/footer.inc');
?>