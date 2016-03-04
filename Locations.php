<?php

include('includes/session.inc');

$Title = _('Location Maintenance');
$ViewTopic = 'Inventory';// Filename in ManualContents.php's TOC.
$BookMark = 'Locations';// Anchor's id in the manual's html document.
include('includes/header.inc');

if (isset($_GET['SelectedLocation'])) {
	$SelectedLocation = $_GET['SelectedLocation'];
} elseif (isset($_POST['SelectedLocation'])) {
	$SelectedLocation = $_POST['SelectedLocation'];
}

if (isset($_POST['submit'])) {
	$_POST['Managed'] = 'off';
	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	$_POST['LocCode'] = mb_strtoupper($_POST['LocCode']);
	if (trim($_POST['LocCode']) == '') {
		$InputError = 1;
		prnMsg(_('The location code may not be empty'), 'error');
	}

	if (isset($SelectedLocation) and $InputError != 1) {

		/* Set the managed field to 1 if it is checked, otherwise 0 */
		if (isset($_POST['Managed']) and $_POST['Managed'] == 'on') {
			$_POST['Managed'] = 1;
		} else {
			$_POST['Managed'] = 0;
		}

		$SQL = "UPDATE locations SET loccode='" . $_POST['LocCode'] . "',
									locationname='" . $_POST['LocationName'] . "',
									deladd1='" . $_POST['DelAdd1'] . "',
									deladd2='" . $_POST['DelAdd2'] . "',
									deladd3='" . $_POST['DelAdd3'] . "',
									deladd4='" . $_POST['DelAdd4'] . "',
									deladd5='" . $_POST['DelAdd5'] . "',
									deladd6='" . $_POST['DelAdd6'] . "',
									tel='" . $_POST['Tel'] . "',
									fax='" . $_POST['Fax'] . "',
									email='" . $_POST['Email'] . "',
									contact='" . $_POST['Contact'] . "',
									taxprovinceid = '" . $_POST['TaxProvince'] . "',
									managed = '" . $_POST['Managed'] . "',
									internalrequest = '" . $_POST['InternalRequest'] . "',
									usedforwo = '" . $_POST['UsedForWO'] . "',
									glaccountcode = '" . $_POST['GLAccountCode'] . "',
									allowinvoicing = '" . $_POST['AllowInvoicing'] . "'
						WHERE loccode = '" . $SelectedLocation . "'";

		$ErrMsg = _('An error occurred updating the') . ' ' . $SelectedLocation . ' ' . _('location record because');
		$DbgMsg = _('The SQL used to update the location record was');

		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

		prnMsg(_('The location record has been updated'), 'success');
		unset($_POST['LocCode']);
		unset($_POST['LocationName']);
		unset($_POST['DelAdd1']);
		unset($_POST['DelAdd2']);
		unset($_POST['DelAdd3']);
		unset($_POST['DelAdd4']);
		unset($_POST['DelAdd5']);
		unset($_POST['DelAdd6']);
		unset($_POST['Tel']);
		unset($_POST['Fax']);
		unset($_POST['Email']);
		unset($_POST['TaxProvince']);
		unset($_POST['Managed']);
		unset($SelectedLocation);
		unset($_POST['Contact']);
		unset($_POST['InternalRequest']);
		unset($_POST['UsedForWO']);
		unset($_POST['GLAccountCode']);
		unset($_POST['AllowInvoicing']);

	} elseif ($InputError != 1) {

		/* Set the managed field to 1 if it is checked, otherwise 0 */
		if ($_POST['Managed'] == 'on') {
			$_POST['Managed'] = 1;
		} else {
			$_POST['Managed'] = 0;
		}

		/*SelectedLocation is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new Location form */

		$SQL = "INSERT INTO locations (loccode,
										locationname,
										deladd1,
										deladd2,
										deladd3,
										deladd4,
										deladd5,
										deladd6,
										tel,
										fax,
										email,
										contact,
										taxprovinceid,
										managed,
										internalrequest,
										usedforwo,
										glaccountcode,
										allowinvoicing)
						VALUES ('" . $_POST['LocCode'] . "',
								'" . $_POST['LocationName'] . "',
								'" . $_POST['DelAdd1'] . "',
								'" . $_POST['DelAdd2'] . "',
								'" . $_POST['DelAdd3'] . "',
								'" . $_POST['DelAdd4'] . "',
								'" . $_POST['DelAdd5'] . "',
								'" . $_POST['DelAdd6'] . "',
								'" . $_POST['Tel'] . "',
								'" . $_POST['Fax'] . "',
								'" . $_POST['Email'] . "',
								'" . $_POST['Contact'] . "',
								'" . $_POST['TaxProvince'] . "',
								'" . $_POST['Managed'] . "',
								'" . $_POST['InternalRequest'] . "',
								'" . $_POST['UsedForWO'] . "',
								'" . $_POST['GLAccountCode'] . "',
								'" . $_POST['AllowInvoicing'] . "')";

		$ErrMsg = _('An error occurred inserting the new location record because');
		$DbgMsg = _('The SQL used to insert the location record was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

		prnMsg(_('The new location record has been added'), 'success');

		/* Also need to add LocStock records for all existing stock items */

		$SQL = "INSERT INTO locstock (
					loccode,
					stockid,
					quantity,
					reorderlevel)
			SELECT '" . $_POST['LocCode'] . "',
				stockmaster.stockid,
				0,
				0
			FROM stockmaster";

		$ErrMsg = _('An error occurred inserting the new location stock records for all pre-existing parts because');
		$DbgMsg = _('The SQL used to insert the new stock location records was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

		prnMsg('........ ' . _('and new stock locations inserted for all existing stock items for the new location'), 'success');

		/* Also need to add locationuser records for all existing users*/
		$SQL = "INSERT INTO locationusers (userid, loccode, canview, canupd)
					SELECT www_users.userid,
					locations.loccode,
					1,
					1
					FROM www_users CROSS JOIN locations
					LEFT JOIN locationusers
					ON www_users.userid = locationusers.userid
					AND locations.loccode = locationusers.loccode
					WHERE locationusers.userid IS NULL
					AND  locations.loccode='" . $_POST['LocCode'] . "'";

		$ErrMsg = _('The users/locations that need user location records created cannot be retrieved because');
		$Result = DB_query($SQL, $ErrMsg);
		prnMsg(_('Existing users have been authorized for this location'),'success');

		/* Also need to create a container for the whole of the warehouse */

		$InsertSQL = "INSERT INTO container (id,
											name,
											location,
											parentid,
											xcoord,
											ycoord,
											zcoord,
											width,
											length,
											height,
											sequence,
											putaway,
											picking,
											replenishment
										) VALUES (
											'" . $_POST['LocCode'] . "',
											'" . _('Primary location for warehouse') . '-' . $_POST['LocCode'] . "',
											'" . $_POST['LocCode'] . "',
											'',
											'0',
											'0',
											'0',
											'0',
											'0',
											'0',
											'1',
											'1',
											'1',
											'1'
										)";

		$ErrMsg = _('An error occurred inserting the container detaails');
		$DbgMsg = _('The SQL used to insert the container record was');
		$Result = DB_query($InsertSQL, $ErrMsg, $DbgMsg);

		unset($_POST['LocCode']);
		unset($_POST['LocationName']);
		unset($_POST['DelAdd1']);
		unset($_POST['DelAdd2']);
		unset($_POST['DelAdd3']);
		unset($_POST['DelAdd4']);
		unset($_POST['DelAdd5']);
		unset($_POST['DelAdd6']);
		unset($_POST['Tel']);
		unset($_POST['Fax']);
		unset($_POST['Email']);
		unset($_POST['TaxProvince']);
		unset($_POST['Managed']);
		unset($SelectedLocation);
		unset($_POST['Contact']);
		unset($_POST['InternalRequest']);
		unset($_POST['UsedForWO']);
		unset($_POST['GLAccountCode']);
		unset($_POST['AllowInvoicing']);
	}


	/* Go through the tax authorities for all Locations deleting or adding TaxAuthRates records as necessary */

	$Result = DB_query("SELECT COUNT(taxid) FROM taxauthorities");
	$NoTaxAuths = DB_fetch_row($Result);

	$DispTaxProvincesResult = DB_query("SELECT taxprovinceid FROM locations");
	$TaxCatsResult = DB_query("SELECT taxcatid FROM taxcategories");
	if (DB_num_rows($TaxCatsResult) > 0) { // This will only work if there are levels else we get an error on seek.

		while ($MyRow = DB_fetch_row($DispTaxProvincesResult)) {
			/*Check to see there are TaxAuthRates records set up for this TaxProvince */
			$NoTaxRates = DB_query("SELECT taxauthority FROM taxauthrates WHERE dispatchtaxprovince='" . $MyRow[0] . "'");

			if (DB_num_rows($NoTaxRates) < $NoTaxAuths[0]) {

				/*First off delete any tax authoritylevels already existing */
				$DelTaxAuths = DB_query("DELETE FROM taxauthrates WHERE dispatchtaxprovince='" . $MyRow[0] . "'");

				/*Now add the new TaxAuthRates required */
				while ($CatRow = DB_fetch_row($TaxCatsResult)) {
					$SQL = "INSERT INTO taxauthrates (taxauthority,
										dispatchtaxprovince,
										taxcatid)
							SELECT taxid,
								'" . $MyRow[0] . "',
								'" . $CatRow[0] . "'
							FROM taxauthorities";

					$InsTaxAuthRates = DB_query($SQL);
				}
				DB_data_seek($TaxCatsResult, 0);
			}
		}
	}


} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

	// PREVENT DELETES IF DEPENDENT RECORDS
	$SQL = "SELECT COUNT(*) FROM salesorders WHERE fromstkloc='" . $SelectedLocation . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this location because sales orders have been created delivering from this location'), 'warn');
		echo _('There are') . ' ' . $MyRow[0] . ' ' . _('sales orders with this Location code');
	} else {
		$SQL = "SELECT COUNT(*) FROM stockmoves WHERE stockmoves.loccode='" . $SelectedLocation . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0] > 0) {
			$CancelDelete = 1;
			prnMsg(_('Cannot delete this location because stock movements have been created using this location'), 'warn');
			echo '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('stock movements with this Location code');

		} else {
			$SQL = "SELECT COUNT(*) FROM locstock
					WHERE locstock.loccode='" . $SelectedLocation . "'
					AND locstock.quantity !=0";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_row($Result);
			if ($MyRow[0] > 0) {
				$CancelDelete = 1;
				prnMsg(_('Cannot delete this location because location stock records exist that use this location and have a quantity on hand not equal to 0'), 'warn');
				echo '<br /> ' . _('There are') . ' ' . $MyRow[0] . ' ' . _('stock items with stock on hand at this location code');
			} else {
				$SQL = "SELECT COUNT(*) FROM www_users
						WHERE www_users.defaultlocation='" . $SelectedLocation . "'";
				$Result = DB_query($SQL);
				$MyRow = DB_fetch_row($Result);
				if ($MyRow[0] > 0) {
					$CancelDelete = 1;
					prnMsg(_('Cannot delete this location because it is the default location for a user') . '. ' . _('The user record must be modified first'), 'warn');
					echo '<br /> ' . _('There are') . ' ' . $MyRow[0] . ' ' . _('users using this location as their default location');
				} else {
					$SQL = "SELECT COUNT(*) FROM bom
							WHERE bom.loccode='" . $SelectedLocation . "'";
					$Result = DB_query($SQL);
					$MyRow = DB_fetch_row($Result);
					if ($MyRow[0] > 0) {
						$CancelDelete = 1;
						prnMsg(_('Cannot delete this location because it is the default location for a bill of material') . '. ' . _('The bill of materials must be modified first'), 'warn');
						echo '<br /> ' . _('There are') . ' ' . $MyRow[0] . ' ' . _('bom components using this location');
					} else {
						$SQL = "SELECT COUNT(*) FROM workcentres
								WHERE workcentres.location='" . $SelectedLocation . "'";
						$Result = DB_query($SQL);
						$MyRow = DB_fetch_row($Result);
						if ($MyRow[0] > 0) {
							$CancelDelete = 1;
							prnMsg(_('Cannot delete this location because it is used by some work centre records'), 'warn');
							echo '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('works centres using this location');
						} else {
							$SQL = "SELECT COUNT(*) FROM workorders
									WHERE workorders.loccode='" . $SelectedLocation . "'";
							$Result = DB_query($SQL);
							$MyRow = DB_fetch_row($Result);
							if ($MyRow[0] > 0) {
								$CancelDelete = 1;
								prnMsg(_('Cannot delete this location because it is used by some work order records'), 'warn');
								echo '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('work orders using this location');
							} else {
								$SQL = "SELECT COUNT(*) FROM custbranch
										WHERE custbranch.defaultlocation='" . $SelectedLocation . "'";
								$Result = DB_query($SQL);
								$MyRow = DB_fetch_row($Result);
								if ($MyRow[0] > 0) {
									$CancelDelete = 1;
									prnMsg(_('Cannot delete this location because it is used by some branch records as the default location to deliver from'), 'warn');
									echo '<br /> ' . _('There are') . ' ' . $MyRow[0] . ' ' . _('branches set up to use this location by default');
								} else {
									$SQL = "SELECT COUNT(*) FROM purchorders WHERE intostocklocation='" . $SelectedLocation . "'";
									$Result = DB_query($SQL);
									$MyRow = DB_fetch_row($Result);
									if ($MyRow[0] > 0) {
										$CancelDelete = 1;
										prnMsg(_('Cannot delete this location because it is used by some purchase order records as the location to receive stock into'), 'warn');
										echo '<br /> ' . _('There are') . ' ' . $MyRow[0] . ' ' . _('purchase orders set up to use this location as the receiving location');
									}
								}
							}
						}
					}
				}
			}
		}
	}
	if (!$CancelDelete) {

		/* need to figure out if this location is the only one in the same tax province */
		$Result = DB_query("SELECT taxprovinceid FROM locations
							WHERE loccode='" . $SelectedLocation . "'");
		$TaxProvinceRow = DB_fetch_row($Result);
		$Result = DB_query("SELECT COUNT(taxprovinceid) FROM locations
							WHERE taxprovinceid='" . $TaxProvinceRow[0] . "'");
		$TaxProvinceCount = DB_fetch_row($Result);
		if ($TaxProvinceCount[0] == 1) {
			/* if its the only location in this tax authority then delete the appropriate records in TaxAuthLevels */
			$Result = DB_query("DELETE FROM taxauthrates
								WHERE dispatchtaxprovince='" . $TaxProvinceRow[0] . "'");
		}

		$Result = DB_query("DELETE FROM container WHERE location ='" . $SelectedLocation . "'");
		$Result = DB_query("DELETE FROM locstock WHERE loccode ='" . $SelectedLocation . "'");
		$Result = DB_query("DELETE FROM locations WHERE loccode='" . $SelectedLocation . "'");
		$Result = DB_query("DELETE FROM locationusers WHERE loccode='" . $SelectedLocation . "'");
		prnMsg(_('Location') . ' ' . $SelectedLocation . ' ' . _('has been deleted') . '!', 'success');
		unset($SelectedLocation);
	} //end if Delete Location
	unset($SelectedLocation);
	unset($_GET['delete']);
}

if (!isset($SelectedLocation)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedLocation will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of Locations will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/

	$SQL = "SELECT locations.loccode,
					locationname,
					taxprovinces.taxprovincename as description,
					glaccountcode,
					allowinvoicing,
					managed
				FROM locations
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canview=1
					AND locationusers.canupd=1
				INNER JOIN taxprovinces
					ON locations.taxprovinceid=taxprovinces.taxprovinceid";
	$Result = DB_query($SQL);

	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/supplier.png" title="' . _('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';

	if (DB_num_rows($Result) == 0) {
		echo '<div class="page_help_text">' . _('As this is the first time that the system has been used, you must first create a location.') .
				'<br />' . _('Once you have filled in all the details, click on the button at the bottom of the screen') . '</div>';
	}

	if (DB_num_rows($Result) != 0) {

		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">', _('Location Code'), '</th>
						<th class="SortedColumn">', _('Location Name'), '</th>
						<th class="SortedColumn">', _('Tax Province'), '</th>
						<th class="SortedColumn">', _('GL Account Code'), '</th>
						<th class="SortedColumn">', _('Allow Invoicing'), '</th>
						<th class="noPrint" colspan="2">&nbsp;</th>
					</tr>
				</thead>';

		echo'<tbody>';
		$k = 0; //row colour counter
		while ($MyRow = DB_fetch_array($Result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			/* warehouse management not implemented ... yet
			if($MyRow['managed'] == 1) {
			$MyRow['managed'] = _('Yes');
			}  else {
			$MyRow['managed'] = _('No');
			}
			*/
			printf('<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="centre">%s</td>
					<td>%s</td>
					<td><a href="%sSelectedLocation=%s">' . _('Edit') . '</a></td>
					<td><a href="%sLocation=%s">' . _('Define') . '</a></td>
					<td><a href="%sSelectedLocation=%s&amp;delete=1" onclick="return MakeConfirm(\'' . _('Are you sure you wish to delete this inventory location?') . '\', \'Confirm Delete\', this);">' . _('Delete') . '</a></td>
					</tr>',
					$MyRow['loccode'],
					$MyRow['locationname'],
					$MyRow['description'],
					($MyRow['glaccountcode'] != '' ? $MyRow['glaccountcode'] : '&nbsp;'),// Use a non-breaking space to avoid an empty cell in a HTML table.
					($MyRow['allowinvoicing'] == 1 ? _('Yes') : _('No')),
					htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?',
					$MyRow['loccode'],
					'DefineWarehouse.php?',
					$MyRow['loccode'],
					htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?',
					$MyRow['loccode']);
		}
	}
	//END WHILE LIST LOOP
	echo '</tbody>';
	echo '</table>';
}

//end of ifs and buts!
if (isset($SelectedLocation)) {
	echo '<div class="toplink">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Review Records') . '</a>
		</div>';
}

if (!isset($_GET['delete'])) {

	include('includes/CountriesArray.php');
	echo '<form id="Locations" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedLocation)) {
		//editing an existing Location
		echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/supplier.png" title="' . _('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';

		$SQL = "SELECT loccode,
					locationname,
					deladd1,
					deladd2,
					deladd3,
					deladd4,
					deladd5,
					deladd6,
					contact,
					fax,
					tel,
					email,
					taxprovinceid,
					managed,
					internalrequest,
					usedforwo,
					glaccountcode,
					allowinvoicing
				FROM locations
				WHERE loccode='" . $SelectedLocation . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['LocCode'] = $MyRow['loccode'];
		$_POST['LocationName'] = $MyRow['locationname'];
		$_POST['DelAdd1'] = $MyRow['deladd1'];
		$_POST['DelAdd2'] = $MyRow['deladd2'];
		$_POST['DelAdd3'] = $MyRow['deladd3'];
		$_POST['DelAdd4'] = $MyRow['deladd4'];
		$_POST['DelAdd5'] = $MyRow['deladd5'];
		$_POST['DelAdd6'] = $MyRow['deladd6'];
		$_POST['Contact'] = $MyRow['contact'];
		$_POST['Tel'] = $MyRow['tel'];
		$_POST['Fax'] = $MyRow['fax'];
		$_POST['Email'] = $MyRow['email'];
		$_POST['TaxProvince'] = $MyRow['taxprovinceid'];
		$_POST['Managed'] = $MyRow['managed'];
		$_POST['InternalRequest'] = $MyRow['internalrequest'];
		$_POST['UsedForWO'] = $MyRow['usedforwo'];
		$_POST['GLAccountCode'] = $MyRow['glaccountcode'];
		$_POST['AllowInvoicing'] = $MyRow['allowinvoicing'];

		echo '<input type="hidden" name="SelectedLocation" value="' . $SelectedLocation . '" />';
		echo '<input type="hidden" name="LocCode" value="' . $_POST['LocCode'] . '" />';
		echo '<table class="selection">';
		echo '<tr>
				<th colspan="2">' . _('Amend Location details') . '</th>
			</tr>';
		echo '<tr>
				<td>' . _('Location Code') . ':</td>
				<td>' . $_POST['LocCode'] . '</td>
			</tr>';
	} else { //end of if $SelectedLocation only do the else when a new record is being entered
		if (!isset($_POST['LocCode'])) {
			$_POST['LocCode'] = '';
		}
		echo '<table class="selection">
				<tr>
					<th colspan="2"><h3>' . _('New Location details') . '</h3></th>
				</tr>';
		echo '<tr>
				<td>' . _('Location Code') . ':</td>
				<td><input type="text" name="LocCode" value="' . $_POST['LocCode'] . '" size="5" required="required" maxlength="5" /></td>
			</tr>';
	}
	if (!isset($_POST['LocationName'])) {
		$_POST['LocationName'] = '';
	}
	if (!isset($_POST['Contact'])) {
		$_POST['Contact'] = '';
	}
	if (!isset($_POST['DelAdd1'])) {
		$_POST['DelAdd1'] = ' ';
	}
	if (!isset($_POST['DelAdd2'])) {
		$_POST['DelAdd2'] = '';
	}
	if (!isset($_POST['DelAdd3'])) {
		$_POST['DelAdd3'] = '';
	}
	if (!isset($_POST['DelAdd4'])) {
		$_POST['DelAdd4'] = '';
	}
	if (!isset($_POST['DelAdd5'])) {
		$_POST['DelAdd5'] = '';
	}
	if (!isset($_POST['DelAdd6'])) {
		$_POST['DelAdd6'] = '';
	}
	if (!isset($_POST['Tel'])) {
		$_POST['Tel'] = '';
	}
	if (!isset($_POST['Fax'])) {
		$_POST['Fax'] = '';
	}
	if (!isset($_POST['Email'])) {
		$_POST['Email'] = '';
	}
	if (!isset($_POST['Managed'])) {
		$_POST['Managed'] = 0;
	}
	if(!isset($_POST['AllowInvoicing'])) {
		$_POST['AllowInvoicing'] = 1;// If not set, set value to "Yes".
	}
	if (!isset($_POST['GLAccountCode'])) {
		$_POST['GLAccountCode'] = '';
	}

	echo '<tr>
			<td>' . _('Location Name') . ':' . '</td>
			<td><input type="text" name="LocationName" value="' . $_POST['LocationName'] . '" size="51" required="required" maxlength="50" /></td>
		</tr>
		<tr>
			<td>' . _('Contact for deliveries') . ':' . '</td>
			<td><input type="text" name="Contact" value="' . $_POST['Contact'] . '" size="31" required="required" maxlength="30" /></td>
		</tr>
		<tr>
			<td>' . _('Delivery Address 1') . ':' . '</td>
			<td><input type="text" name="DelAdd1" value="' . $_POST['DelAdd1'] . '" size="41" required="required" maxlength="40" /></td>
		</tr>
		<tr>
			<td>' . _('Delivery Address 2') . ':' . '</td>
			<td><input type="text" name="DelAdd2" value="' . $_POST['DelAdd2'] . '" size="41" maxlength="40" /></td>
		</tr>
		<tr>
			<td>' . _('Delivery Address 3') . ':' . '</td>
			<td><input type="text" name="DelAdd3" value="' . $_POST['DelAdd3'] . '" size="41" maxlength="40" /></td>
		</tr>
		<tr>
			<td>' . _('Delivery Address 4') . ':' . '</td>
			<td><input type="text" name="DelAdd4" value="' . $_POST['DelAdd4'] . '" size="41" maxlength="40" /></td>
		</tr>
		<tr>
			<td>' . _('Delivery Address 5') . ':' . '</td>
			<td><input type="text" name="DelAdd5" value="' . $_POST['DelAdd5'] . '" size="21" maxlength="20" /></td>
		</tr>
			<td>' . _('Country') . ':</td>
			<td><select name="DelAdd6">';
		foreach ($CountriesArray as $CountryEntry => $CountryName) {
			if (isset($_POST['DelAdd6']) and (strtoupper($_POST['DelAdd6']) == strtoupper($CountryName))) {
				echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName . '</option>';
			} elseif (!isset($_POST['Address6']) and $CountryName == "") {
				echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName . '</option>';
			} else {
				echo '<option value="' . $CountryName . '">' . $CountryName . '</option>';
			}
		}
		echo '</select></td>
 		</tr>
		<tr>
			<td>' . _('Telephone No') . ':' . '</td>
			<td><input type="tel" name="Tel" value="' . $_POST['Tel'] . '" size="31" maxlength="30" /></td>
		</tr>
		<tr>
			<td>' . _('Facsimile No') . ':' . '</td>
			<td><input type="tel" name="Fax" value="' . $_POST['Fax'] . '" size="31" maxlength="30" /></td>
		</tr>
		<tr>
			<td>' . _('Email') . ':' . '</td>
			<td><input type="email" name="Email" value="' . $_POST['Email'] . '" size="31" maxlength="55" /></td>
		</tr>
		<tr>
			<td>' . _('Tax Province') . ':' . '</td>
			<td><select name="TaxProvince">';

	$TaxProvinceResult = DB_query("SELECT taxprovinceid, taxprovincename FROM taxprovinces");
	while ($MyRow = DB_fetch_array($TaxProvinceResult)) {
		if (isset($_POST['TaxProvince']) and $_POST['TaxProvince'] == $MyRow['taxprovinceid']) {
			echo '<option selected="selected" value="' . $MyRow['taxprovinceid'] . '">' . $MyRow['taxprovincename'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['taxprovinceid'] . '">' . $MyRow['taxprovincename'] . '</option>';
		}
	}

	echo '</select></td>
		</tr>';
	// Location's ledger account:

	//SQL to poulate account selection boxes
	$SQL = "SELECT accountcode,
					accountname
				FROM chartmaster
				LEFT JOIN accountgroups
					ON chartmaster.groupcode=accountgroups.groupcode
					AND chartmaster.language=accountgroups.language
				WHERE accountgroups.pandl=0
					AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
				ORDER BY accountcode";
	$BSAccountsResult = DB_query($SQL);
	echo '<tr title="', _('Enter the GL account for this location, or leave it in blank if not needed'), '">
			<td><label for="GLAccountCode">', _('GL Account Code'), ':</label></td>
			<td><select name="GLAccountCode">';

	while ($MyRow = DB_fetch_array($BSAccountsResult)) {

		if (isset($_POST['GLAccountCode']) and $MyRow['accountcode'] == $_POST['GLAccountCode']) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] . '">' . $MyRow['accountname'] . ' (' . $MyRow['accountcode'] . ')' . '</option>';
		} else {
			echo '<option value="' . $MyRow['accountcode'] . '">' . $MyRow['accountname'] . ' (' . $MyRow['accountcode'] . ')' . '</option>';
		}
	} //end while loop
	echo '</select>
			</td>
		</tr>';
	// Allow or deny the invoicing of items in this location:
	echo '<tr title="', _('Use this parameter to indicate whether these inventory location allows or denies the invoicing of its items.'), '">
			<td><label for="AllowInvoicing">', _('Allow Invoicing'), ':</label></td>
			<td>
				<select name="AllowInvoicing">
					<option', ($_POST['AllowInvoicing'] == 1 ? ' selected="selected"' : ''), ' value="1">', _('Yes'), '</option>
					<option', ($_POST['AllowInvoicing'] == 0 ? ' selected="selected"' : ''), ' value="0">', _('No'), '</option>
				</select>
			</td>
		</tr>';
	echo '<tr>
			<td>' . _('Allow internal requests?') . ':</td>
			<td><select name="InternalRequest">';
	if (isset($_POST['InternalRequest']) and $_POST['InternalRequest'] == 1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if (isset($_POST['InternalRequest']) and $_POST['InternalRequest'] == 0) {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select>
				</td>
			</tr>';

	echo '<tr>
			<td>' . _('Use for Work Order Productions?') . ':</td>
			<td><select name="UsedForWO">';
	if (isset($_POST['UsedForWO']) and $_POST['UsedForWO'] == 1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if (isset($_POST['UsedForWO']) and $_POST['UsedForWO'] == 0) {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select>
				</td>
			</tr>';

	/*
	This functionality is not written yet ...
	<tr><td><?php echo _('Enable Warehouse Management') . ':'; ?></td>
	<td><input type='checkbox' name='Managed'<?php if($_POST['Managed'] == 1) echo ' checked';?>></td></tr>
	*/
	echo '</table>
		<div class="centre">
			<input type="submit" name="submit" value="' . _('Enter Information') . '" />
		</div>
		</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.inc');
?>