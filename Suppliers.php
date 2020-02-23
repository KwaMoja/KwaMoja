<?php
include ('includes/session.php');

$Title = _('Supplier Maintenance');
/* Manual links before header.php */
$ViewTopic = 'AccountsPayable';
$BookMark = 'NewSupplier';
include ('includes/header.php');
include ('includes/SQL_CommonFunctions.php');
include ('includes/CountriesArray.php');

if (isset($_GET['SupplierID'])) {
	$SupplierID = mb_strtoupper(stripslashes($_GET['SupplierID']));
} elseif (isset($_POST['SupplierID'])) {
	$SupplierID = mb_strtoupper(stripslashes($_POST['SupplierID']));
} else {
	unset($SupplierID);
}

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/supplier.png" title="', _('Search'), '" alt="" />', ' ', _('Suppliers'), '
	</p>';

if (isset($SupplierID)) {
	echo '<p>
			<a href="', $RootPath, '/SupplierContacts.php?SupplierID=', urlencode(stripslashes($SupplierID)), '">', _('Review Supplier Contact Details'), '</a>
		</p>';
}

$InputError = 0;

if (isset($_POST['insert']) or isset($_POST['update'])) {

	//initialise no input errors assumed initially before we test
	$i = 1;
	/* actions to take once the user has clicked the submit button
	 ie the page has called itself with some user input */

	if ($_SESSION['AutoSupplierNo'] == 0) {
		//first off validate inputs sensible
		$SQL = "SELECT COUNT(supplierid) FROM suppliers WHERE supplierid='" . DB_escape_string($SupplierID) . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0] > 0 and isset($_POST['insert'])) {
			$InputError = 1;
			prnMsg(_('The supplier number already exists in the database'), 'error');
			$DuplicateSupplierNo = $SupplierID;
		}
	}
	if (mb_strlen(trim($_POST['SuppName'])) > 40 or mb_strlen(trim($_POST['SuppName'])) == 0 or trim($_POST['SuppName']) == '') {
		$InputError = 1;
		prnMsg(_('The supplier name must be entered and be forty characters or less long'), 'error');
	}
	if ($_SESSION['AutoSupplierNo'] == 0 and mb_strlen($SupplierID) == 0) {
		$InputError = 1;
		prnMsg(_('The Supplier Code cannot be empty'), 'error');
	}
	if (mb_strlen($_POST['Phone']) > 25) {
		$InputError = 1;
		prnMsg(_('The telephone number must be 25 characters or less long'), 'error');
	}
	if (mb_strlen($_POST['Fax']) > 25) {
		$InputError = 1;
		prnMsg(_('The fax number must be 25 characters or less long'), 'error');
	}
	if (mb_strlen($_POST['Email']) > 55) {
		$InputError = 1;
		prnMsg(_('The email address must be 55 characters or less long'), 'error');
	}
	if (mb_strlen($_POST['Email']) > 0 and !IsEmailAddress($_POST['Email'])) {
		$InputError = 1;
		prnMsg(_('The email address is not correctly formed'), 'error');
	}
	if (mb_strlen($_POST['URL']) > 50) {
		$InputError = 1;
		prnMsg(_('The URL address must be 50 characters or less long'), 'error');
	}
	if (mb_strlen($_POST['BankRef']) > 12) {
		$InputError = 1;
		prnMsg(_('The bank reference text must be less than 12 characters long'), 'error');
	}
	if (!is_date($_POST['SupplierSince'])) {
		$InputError = 1;
		prnMsg(_('The supplier since field must be a date in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
	}
	if ($_POST['DefaultShipper'] == '') {
		$InputError = 1;
		prnMsg(_('You mst select a default shipper for tos supplier'), 'error');
	}

	if ($InputError != 1) {

		$SQL_SupplierSince = FormatDateForSQL($_POST['SupplierSince']);

		$Latitude = 0;
		$Longitude = 0;
		if ($_SESSION['geocode_integration'] == 1) {
			// Get the lat/long from our geocoding host
			$SQL = "SELECT * FROM geocode_param WHERE 1";
			$ErrMsg = _('An error occurred in retrieving the information');
			$ResultGeo = DB_query($SQL, $ErrMsg);
			$Row = DB_fetch_array($ResultGeo);
			$ApiKey = $Row['geocode_key'];
			$MapHost = $Row['map_host'];
			define('MAPS_HOST', $MapHost);
			define('KEY', $ApiKey);
			// check that some sane values are setup already in geocode tables, if not skip the geocoding but add the record anyway.
			if ($MapHost == "") {
				echo '<div class="warn">', _('Warning - Geocode Integration is enabled, but no hosts are setup.  Go to Geocode Setup'), '</div>';
			} else {
				$Address = urlencode($_POST['Address1'] . ', ' . $_POST['Address2'] . ', ' . $_POST['Address3'] . ', ' . $_POST['Address4'] . ', ' . $_POST['Address5'] . ', ' . $_POST['Address6']);

				$BaseURL = "http://" . MAPS_HOST . "/maps/api/geocode/xml?address=";
				$RequestURL = $BaseURL . $Address . ',&sensor=true';

				$xml = simplexml_load_string(utf8_encode(file_get_contents($RequestURL))) or die("url not loading");
				//			$xml = simplexml_load_file($RequestURL) or die("url not loading");
				$Status = $xml->status;
				if (strcmp($Status, 'OK') == 0) {
					// Successful geocode
					$GeoCodePending = false;
					// Format: Longitude, Latitude, Altitude
					$Latitude = $xml->result->geometry->location->lat;
					$Longitude = $xml->result->geometry->location->lng;
				} else {
					// failure to geocode
					$GeoCodePending = false;
					echo '<p>', _('Address'), ': ', $Address, ' ', _('failed to geocode'), "\n";
					echo _('Received status'), ' ', $Status, "\n", '</p>';
				}
			}
		}
		if (isset($_POST['update'])) {

			$SuppTransSQL = "SELECT supplierno
							FROM supptrans
							WHERE supplierno='" . DB_escape_string($SupplierID) . "'";
			$SuppResult = DB_query($SuppTransSQL);
			$SuppTrans = DB_num_rows($SuppResult);

			$SuppCurrSQL = "SELECT currcode
							FROM suppliers
							WHERE supplierid='" . DB_escape_string($SupplierID) . "'";
			$CurrResult = DB_query($SuppCurrSQL);
			$SuppCurr = DB_fetch_row($CurrResult);

			if ($SuppTrans == 0) {
				$SQL = "UPDATE suppliers SET suppname='" . $_POST['SuppName'] . "',
							address1='" . $_POST['Address1'] . "',
							address2='" . $_POST['Address2'] . "',
							address3='" . $_POST['Address3'] . "',
							address4='" . $_POST['Address4'] . "',
							address5='" . $_POST['Address5'] . "',
							address6='" . $_POST['Address6'] . "',
							telephone='" . $_POST['Phone'] . "',
							fax='" . $_POST['Fax'] . "',
							email='" . $_POST['Email'] . "',
							url='" . $_POST['URL'] . "',
							supptype='" . $_POST['SupplierType'] . "',
							currcode='" . $_POST['CurrCode'] . "',
							suppliersince='" . $SQL_SupplierSince . "',
							paymentterms='" . $_POST['PaymentTerms'] . "',
							bankpartics='" . $_POST['BankPartics'] . "',
							bankref='" . $_POST['BankRef'] . "',
					 		bankact='" . $_POST['BankAct'] . "',
							remittance='" . $_POST['Remittance'] . "',
							taxgroupid='" . $_POST['TaxGroup'] . "',
							factorcompanyid='" . $_POST['FactorID'] . "',
							suppliergroupid='" . $_POST['GroupID'] . "',
							salespersonid='" . $_POST['SalesPersonID'] . "',
							lat='" . $Latitude . "',
							lng='" . $Longitude . "',
							defaultshipper='" . $_POST['DefaultShipper'] . "',
							defaultgl='" . $_POST['DefaultGL'] . "',
							taxref='" . $_POST['TaxRef'] . "'
						WHERE supplierid = '" . DB_escape_string($SupplierID) . "'";
			} else {
				if ($SuppCurr[0] != $_POST['CurrCode']) {
					prnMsg(_('Cannot change currency code as transactions already exist'), 'info');
				}
				$SQL = "UPDATE suppliers SET suppname='" . $_POST['SuppName'] . "',
							address1='" . $_POST['Address1'] . "',
							address2='" . $_POST['Address2'] . "',
							address3='" . $_POST['Address3'] . "',
							address4='" . $_POST['Address4'] . "',
							address5='" . $_POST['Address5'] . "',
							address6='" . $_POST['Address6'] . "',
							telephone='" . $_POST['Phone'] . "',
							fax='" . $_POST['Fax'] . "',
							email='" . $_POST['Email'] . "',
							url='" . $_POST['URL'] . "',
							supptype='" . $_POST['SupplierType'] . "',
							suppliersince='" . $SQL_SupplierSince . "',
							paymentterms='" . $_POST['PaymentTerms'] . "',
							bankpartics='" . $_POST['BankPartics'] . "',
							bankref='" . $_POST['BankRef'] . "',
					 		bankact='" . $_POST['BankAct'] . "',
							remittance='" . $_POST['Remittance'] . "',
							taxgroupid='" . $_POST['TaxGroup'] . "',
							factorcompanyid='" . $_POST['FactorID'] . "',
							suppliergroupid='" . $_POST['GroupID'] . "',
							salespersonid='" . $_POST['SalesPersonID'] . "',
							lat='" . $Latitude . "',
							lng='" . $Longitude . "',
							defaultshipper='" . $_POST['DefaultShipper'] . "',
							defaultgl='" . $_POST['DefaultGL'] . "',
							taxref='" . $_POST['TaxRef'] . "'
						WHERE supplierid = '" . DB_escape_string($SupplierID) . "'";
			}

			$ErrMsg = _('The supplier could not be updated because');
			$DbgMsg = _('The SQL that was used to update the supplier but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

			prnMsg(_('The supplier master record for') . ' ' . $SupplierID . ' ' . _('has been updated'), 'success');

		} elseif (isset($_POST['insert'])) { //its a new supplier
			if ($_SESSION['AutoSupplierNo'] == 1) {
				/* system assigned, sequential, numeric */
				/* $SupplierID = GetNextTransNo(600); */
				$SupplierID = GetNextSupplierCode($_POST['SupplierType']);
			}

			$SQL = "INSERT INTO suppliers (supplierid,
										suppname,
										address1,
										address2,
										address3,
										address4,
										address5,
										address6,
										telephone,
										fax,
										email,
										url,
										supptype,
										currcode,
										suppliersince,
										paymentterms,
										bankpartics,
										bankref,
										bankact,
										remittance,
										taxgroupid,
										factorcompanyid,
										suppliergroupid,
										salespersonid,
										lat,
										lng,
										defaultshipper,
										defaultgl,
										taxref)
								 VALUES ('" . DB_escape_string($SupplierID) . "',
								 	'" . $_POST['SuppName'] . "',
									'" . $_POST['Address1'] . "',
									'" . $_POST['Address2'] . "',
									'" . $_POST['Address3'] . "',
									'" . $_POST['Address4'] . "',
									'" . $_POST['Address5'] . "',
									'" . $_POST['Address6'] . "',
									'" . $_POST['Phone'] . "',
									'" . $_POST['Fax'] . "',
									'" . $_POST['Email'] . "',
									'" . $_POST['URL'] . "',
									'" . $_POST['SupplierType'] . "',
									'" . $_POST['CurrCode'] . "',
									'" . $SQL_SupplierSince . "',
									'" . $_POST['PaymentTerms'] . "',
									'" . $_POST['BankPartics'] . "',
									'" . $_POST['BankRef'] . "',
									'" . $_POST['BankAct'] . "',
									'" . $_POST['Remittance'] . "',
									'" . $_POST['TaxGroup'] . "',
									'" . $_POST['FactorID'] . "',
									'" . $_POST['GroupID'] . "',
									'" . $_POST['SalesPersonID'] . "',
									'" . $Latitude . "',
									'" . $Longitude . "',
									'" . $_POST['DefaultShipper'] . "',
									'" . $_POST['DefaultGL'] . "',
									'" . $_POST['TaxRef'] . "')";

			$ErrMsg = _('The supplier') . ' ' . $_POST['SuppName'] . ' ' . _('could not be added because');
			$DbgMsg = _('The SQL that was used to insert the supplier but failed was');

			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

			prnMsg(_('A new supplier for') . ' ' . $_POST['SuppName'] . ' ' . _('has been added to the database'), 'success');

			echo '<p>
					<a href="', $RootPath, '/SupplierContacts.php?SupplierID=', urlencode($SupplierID), '">', _('Review Supplier Contact Details'), '</a>
				  </p>';

			unset($SupplierID);
			unset($_POST['SuppName']);
			unset($_POST['Address1']);
			unset($_POST['Address2']);
			unset($_POST['Address3']);
			unset($_POST['Address4']);
			unset($_POST['Address5']);
			unset($_POST['Address6']);
			unset($_POST['Phone']);
			unset($_POST['Fax']);
			unset($_POST['Email']);
			unset($_POST['URL']);
			unset($_POST['SupplierType']);
			unset($_POST['CurrCode']);
			unset($SQL_SupplierSince);
			unset($_POST['PaymentTerms']);
			unset($_POST['BankPartics']);
			unset($_POST['BankRef']);
			unset($_POST['BankAct']);
			unset($_POST['Remittance']);
			unset($_POST['TaxGroup']);
			unset($_POST['FactorID']);
			unset($_POST['GroupID']);
			unset($_POST['SalesPersonID']);
			unset($_POST['DefaultShipper']);
			unset($_POST['DefaultGL']);
			unset($_POST['TaxRef']);

		}

	} else {

		prnMsg(_('Validation failed') . _('no updates or deletes took place'), 'warn');

	}

} elseif (isset($_POST['delete']) and $_POST['delete'] != '') {

	//the link to delete a selected record was clicked instead of the submit button
	$CancelDelete = 0;

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'SuppTrans' , PurchOrders, SupplierContacts
	$SQL = "SELECT COUNT(*) FROM supptrans WHERE supplierno='" . $SupplierID . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this supplier because there are transactions that refer to this supplier'), 'warn');
		echo '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('transactions against this supplier');

	} else {
		$SQL = "SELECT COUNT(*) FROM purchorders WHERE supplierno='" . $SupplierID . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0] > 0) {
			$CancelDelete = 1;
			prnMsg(_('Cannot delete the supplier record because purchase orders have been created against this supplier'), 'warn');
			echo '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('orders against this supplier');
		} else {
			$SQL = "SELECT COUNT(*) FROM suppliercontacts WHERE supplierid='" . $SupplierID . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_row($Result);
			if ($MyRow[0] > 0) {
				$CancelDelete = 1;
				prnMsg(_('Cannot delete this supplier because there are supplier contacts set up against it') . ' - ' . _('delete these first'), 'warn');
				echo '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('supplier contacts relating to this supplier');

			}
		}

	}
	if ($CancelDelete == 0) {
		$SQL = "DELETE FROM suppliers WHERE supplierid='" . $SupplierID . "'";
		$Result = DB_query($SQL);
		prnMsg(_('Supplier record for') . ' ' . $SupplierID . ' ' . _('has been deleted'), 'success');
		unset($SupplierID);
		unset($_SESSION['SupplierID']);
	} //end if Delete supplier
	
}

echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

echo '<fieldset>
		<legend>', _('Supplier Details'), '</legend>';

if (!isset($SupplierID)) {

	/* if $AutoSupplierNo is off (not 0) then provide an input box for the SupplierID to manually assigned */
	if ($_SESSION['AutoSupplierNo'] == 0) {
		echo '<field>
				<label for="SupplierID">', _('Supplier Code'), ':</label>
				<input type="text" autofocus="autofocus" required="required" name="SupplierID" size="11" maxlength="10" />
				<fieldhelp>', _('The supplier id should not be more than 10 legal characters long, and cannot be blank'), '</fieldhelp>

			</field>';
	}
	$_POST['SuppName'] = '';
	$_POST['Address1'] = '';
	$_POST['Address2'] = '';
	$_POST['Address3'] = '';
	$_POST['Address4'] = '';
	$_POST['Address5'] = '';
	$_POST['Address6'] = $CountriesArray[$_SESSION['CountryOfOperation']];
	$_POST['CurrCode'] = $_SESSION['CompanyRecord']['currencydefault'];
	$_POST['Phone'] = '';
	$_POST['Fax'] = '';
	$_POST['Email'] = '';
	$_POST['URL'] = '';
	$_POST['SupplierType'] = '';
	$_POST['SupplierSince'] = date($_SESSION['DefaultDateFormat']);
	$_POST['PaymentTerms'] = '';
	$_POST['BankPartics'] = '';
	$_POST['Remittance'] = 0;
	$_POST['BankRef'] = '';
	$_POST['BankAct'] = '';
	$_POST['TaxGroup'] = '';
	$_POST['FactorID'] = '';
	$_POST['GroupID'] = '';
	$_POST['SalesPersonID'] = '';
	$_POST['DefaultShipper'] = $_SESSION['Default_Shipper'];
	$_POST['TaxRef'] = '';
} elseif (isset($DuplicateSupplierNo)) {
	echo '<field>
			<label for="SupplierID">', _('Supplier Code'), ':</label>
			<input type="text" autofocus="autofocus" required="required" name="SupplierID" size="11" maxlength="10" value="', $DuplicateSupplierNo, '" />
			<fieldhelp>', _('The supplier id should not be more than 10 legal characters long, and cannot be blank'), '</fieldhelp>
		</field>';

} else {

	$SQL = "SELECT supplierid,
					suppname,
					address1,
					address2,
					address3,
					address4,
					address5,
					address6,
					telephone,
					fax,
					email,
					url,
					supptype,
					currcode,
					suppliersince,
					paymentterms,
					bankpartics,
					bankref,
					bankact,
					remittance,
					taxgroupid,
					factorcompanyid,
					suppliergroupid,
					defaultshipper,
					taxref,
					defaultgl
				FROM suppliers
				WHERE supplierid = '" . DB_escape_string($SupplierID) . "'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['SuppName'] = stripcslashes($MyRow['suppname']);
	$_POST['Address1'] = stripcslashes($MyRow['address1']);
	$_POST['Address2'] = stripcslashes($MyRow['address2']);
	$_POST['Address3'] = stripcslashes($MyRow['address3']);
	$_POST['Address4'] = stripcslashes($MyRow['address4']);
	$_POST['Address5'] = stripcslashes($MyRow['address5']);
	$_POST['Address6'] = stripcslashes($MyRow['address6']);
	$_POST['CurrCode'] = stripcslashes($MyRow['currcode']);
	$_POST['Phone'] = $MyRow['telephone'];
	$_POST['Fax'] = $MyRow['fax'];
	$_POST['Email'] = $MyRow['email'];
	$_POST['URL'] = $MyRow['url'];
	$_POST['SupplierType'] = $MyRow['supptype'];
	$_POST['SupplierSince'] = ConvertSQLDate($MyRow['suppliersince']);
	$_POST['PaymentTerms'] = $MyRow['paymentterms'];
	$_POST['BankPartics'] = stripcslashes($MyRow['bankpartics']);
	$_POST['Remittance'] = $MyRow['remittance'];
	$_POST['BankRef'] = stripcslashes($MyRow['bankref']);
	$_POST['BankAct'] = $MyRow['bankact'];
	$_POST['TaxGroup'] = $MyRow['taxgroupid'];
	$_POST['FactorID'] = $MyRow['factorcompanyid'];
	$_POST['GroupID'] = $MyRow['suppliergroupid'];
	$_POST['DefaultShipper'] = $MyRow['defaultshipper'];
	$_POST['TaxRef'] = $MyRow['taxref'];
	$_POST['DefaultGL'] = $MyRow['defaultgl'];

	if (isset($_GET['Copy'])) {
		echo '<input type="hidden" name="New" value="Yes" />';
		if ($_SESSION['AutoSupplierNo'] == 0) {
			// its a new supplier being added
			echo '<field>
					<label for="SupplierID">', _('Supplier Code'), ':</label>
					<input type="text" autofocus="autofocus" required="required" name="SupplierID" value="" size="12" maxlength="10" />
					<fieldhelp>', _('The supplier id should not be more than 10 legal characters long, and cannot be blank'), '</fieldhelp>
				</field>';
		}
	} else {
		echo '<input type="hidden" name="SupplierID" value="', $SupplierID, '" />';
	}

}
echo '<field>
		<label for="SuppName">', _('Supplier Name'), ':</label>
		<input type="text" name="SuppName" value="', $_POST['SuppName'], '" size="42" required="required" autofocus="autofocus" maxlength="40" />
		<fieldhelp>', _('The name by which this supplier is known'), '</fieldhelp>
	</field>';
echo '<field>
		<label for="Address1">', _('Address Line 1 (Street)'), ':</label>
		<input type="text" name="Address1" value="', $_POST['Address1'], '" size="42" maxlength="40" /></td>
		<fieldhelp>', _('The first line of the suppliers address'), '</fieldhelp>
	</field>';
echo '<field>
		<label for"Address2">', _('Address Line 2 (Street)'), ':</label>
		<input type="text" name="Address2" value="', $_POST['Address2'], '" size="42" maxlength="40" />
		<fieldhelp>', _('The second line of the suppliers address'), '</fieldhelp>
	</field>';
echo '<field>
		<label for="Address3">', _('Address Line 3 (Suburb/City)'), ':</label>
		<input type="text" name="Address3" value="', $_POST['Address3'], '" size="42" maxlength="40" />
		<fieldhelp>', _('The third line of the suppliers address'), '</fieldhelp>
	</field>';
echo '<field>
		<label for="Address4">', _('Address Line 4 (State/Province)'), ':</label>
		<input type="text" name="Address4" value="', $_POST['Address4'], '" size="42" maxlength="40" />
		<fieldhelp>', _('The fourth line of the suppliers address'), '</fieldhelp>
	</field>';
echo '<field>
		<label for="Address5">', _('Address Line 5 (Postal Code)'), ':</label>
		<input type="text" name="Address5" value="', $_POST['Address5'], '" size="42" maxlength="40" />
		<fieldhelp>', _('The fifth line of the suppliers address'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="Address6">', _('Country'), ':</label>
		<select required="required" name="Address6">';
foreach ($CountriesArray as $CountryEntry => $CountryName) {
	if (isset($_POST['Address6']) and ($_POST['Address6'] == $CountryName)) {
		echo '<option selected="selected" value="', $CountryName, '">', $CountryName, '</option>';
	} elseif (!isset($_POST['Address6']) and $CountryName == "") {
		echo '<option selected="selected" value="', $CountryName, '">', $CountryName, '</option>';
	} else {
		echo '<option value="', $CountryName, '">', $CountryName, '</option>';
	}
}
echo '</select>
	<fieldhelp>', _('The country where this supplier is based'), '</fieldhelp>
</field>';

echo '<field>
		<label for="Phone">', _('Telephone'), ':</label>
		<input type="tel" name="Phone" value="', $_POST['Phone'], '" size="42" maxlength="40" />
		<fieldhelp>', _('A contact telephone number for this supplier'), '</fieldhelp>
	</field>';
echo '<field>
		<label for="Fax">', _('Facsimile'), ':</label>
		<input  type="tel" name="Fax" value="', $_POST['Fax'], '" size="42" maxlength="40" />
		<fieldhelp>', _('A fax number for this supplier'), '</fieldhelp>
	</field>';
echo '<field>
		<label for="Email">', _('Email Address'), ':</label>
		<input type="email" name="Email" value="', $_POST['Email'], '" size="42" maxlength="40" />
		<fieldhelp>', _('An email address for this supplier'), '</fieldhelp>
	</field>';
echo '<field>
		<label for="url">', _('URL'), ':</label>
		<input type="url" name="URL" value="', $_POST['URL'], '" size="42" maxlength="40" />
		<fieldhelp>', _('A URL that refers to this supplier'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="SupplierType">', _('Supplier Type'), ':</label>
		<select required="required" name="SupplierType">';
$Result = DB_query("SELECT typeid, typename FROM suppliertype");
while ($MyRow = DB_fetch_array($Result)) {
	if ($_POST['SupplierType'] == $MyRow['typeid']) {
		echo '<option selected="selected" value="', $MyRow['typeid'], '">', $MyRow['typename'], '</option>';
	} else {
		echo '<option value="', $MyRow['typeid'], '">', $MyRow['typename'], '</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>', _('The supplier type for this supplier'), '</fieldhelp>
</field>';

echo '<field>
		<label for="SupplierSince">', _('Supplier Since'), ' (', $_SESSION['DefaultDateFormat'], '):</label>
		<input size="12" required="required" maxlength="10" type="text" class="date" name="SupplierSince" value="', $_POST['SupplierSince'], '" />
		<fieldhelp>', _('The date when this company first started to supply you'), '</fieldhelp>
	</field>';
echo '<field>
		<label for="BankPartics">', _('Bank Particulars'), ':</label>
		<input type="text" name="BankPartics" size="13" maxlength="12" value="', $_POST['BankPartics'], '" />
		<fieldhelp>', _('The particulars of this suppliers bank account'), '</fieldhelp>
	</field>';
echo '<field>
		<label for="BankRef">', _('Bank Reference'), ':</label>
		<input type="text" name="BankRef" size="13" maxlength="12" value="', $_POST['BankRef'], '" />
		<fieldhelp>', _('The reference details for this suppliers bank account'), '</fieldhelp>
	</field>';
echo '<field>
		<label for="BankAct">', _('Bank Account No'), ':</label>
		<input type="text" name="BankAct" size="31" maxlength="40" value="', $_POST['BankAct'], '" />
		<fieldhelp>', _('The bank account number for this suppliers bank account'), '</fieldhelp>
	</field>';

$Result = DB_query("SELECT terms, termsindicator FROM paymentterms");

echo '<field>
		<label for="PaymentTerms">', _('Payment Terms'), ':</label>
		<select required="required" name="PaymentTerms">';
while ($MyRow = DB_fetch_array($Result)) {
	if ($_POST['PaymentTerms'] == $MyRow['termsindicator']) {
		echo '<option selected="selected" value="', $MyRow['termsindicator'], '">', $MyRow['terms'], '</option>';
	} else {
		echo '<option value="', $MyRow['termsindicator'], '">', $MyRow['terms'], '</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>', _('The terms under which payments to this supplier must be made'), '</fieldhelp>
</field>';

$Result = DB_query("SELECT id, coyname FROM factorcompanies");

echo '<field>
		<label for="FactorID">', _('Factor Company'), ':</label>
		<select name="FactorID">';
echo '<option value="0">', _('None'), '</option>';
while ($MyRow = DB_fetch_array($Result)) {
	if ($_POST['FactorID'] == $MyRow['id']) {
		echo '<option selected="selected" value="', $MyRow['id'], '">', $MyRow['coyname'], '</option>';
	} else {
		echo '<option value="', $MyRow['id'], '">', $MyRow['coyname'], '</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>', _('The factor company to whom payment for this supplier must be made'), '</fieldhelp>
</field>';

$Result = DB_query("SELECT id, coyname FROM suppliergroups");

echo '<field>
		<label for="GroupID">', _('Supplier Group'), ':</label>
		<select name="GroupID">';
echo '<option value="0">', _('None'), '</option>';
while ($MyRow = DB_fetch_array($Result)) {
	if ($_POST['GroupID'] == $MyRow['id']) {
		echo '<option selected="selected" value="', $MyRow['id'], '">', $MyRow['coyname'], '</option>';
	} else {
		echo '<option value="', $MyRow['id'], '">', $MyRow['coyname'], '</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>', _('The supplier group if any that this supplier belongs to'), '</fieldhelp>
</field>';

echo '<field>
		<label for="TaxRef">', _('Tax Reference'), ':</label>
		<input type="text" name="TaxRef" size="21" maxlength="20" value="', $_POST['TaxRef'], '" />
		<fieldhelp>', _('The tax reference for this supplier'), '</fieldhelp>
	<fieldhelp>', _('Select the supplier group that this supplier belongs to.'), '</fieldhelp>
</field>';

$Result = DB_query("SELECT salesmancode, salesmanname FROM salesman");

echo '<field>
		<label for="SalesPersonID">', _('Sales Person'), ':</label>
		<select name="SalesPersonID">';
echo '<option value="">', _('None'), '</option>';
while ($MyRow = DB_fetch_array($Result)) {
	if ($_POST['SalesPersonID'] == $MyRow['salesmancode']) {
		echo '<option selected="selected" value="', $MyRow['salesmancode'], '">', $MyRow['salesmanname'], '</option>';
	} else {
		echo '<option value="', $MyRow['salesmancode'], '">', $MyRow['salesmanname'], '</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>', _('Select the sales person that this supplier refers to. If this supplier is not a sales person then select None.'), '</fieldhelp>
</field>';

echo '<field>
		<label for="TaxRef">', _('Tax Reference'), ':</label>
		<input type="text" name="TaxRef" size="21" maxlength="20" value="', $_POST['TaxRef'], '" />
		<fieldhelp>', _('The tax reference for this supplier.'), '</fieldhelp>
	</field>';

$Result = DB_query("SELECT currency, currabrev FROM currencies");

echo '<field>
		<label for="CurrCode">', _('Supplier Currency'), ':</label>
		<select name="CurrCode">';
while ($MyRow = DB_fetch_array($Result)) {
	if ($_POST['CurrCode'] == $MyRow['currabrev']) {
		echo '<option selected="selected" value="', $MyRow['currabrev'], '">', $MyRow['currency'], '</option>';
	} else {
		echo '<option value="', $MyRow['currabrev'], '">', $MyRow['currency'], '</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>', _('The currency that this supplier normally trades in'), '</fieldhelp>
</field>';

echo '<field>
		<label for="Remittance">', _('Remittance Advice'), ':</label>
		<select name="Remittance">';
if ($_POST['Remittance'] == 0) {
	echo '<option selected="selected" value="0">', _('Not Required'), '</option>';
	echo '<option value="1">', _('Required'), '</option>';
} else {
	echo '<option value="0">', _('Not Required'), '</option>';
	echo '<option selected="selected" value="1">', _('Required'), '</option>';
}
echo '</select>
	<fieldhelp>', _('If the supplier requires a remittance advice with each payment then select Required here'), '</fieldhelp>
</field>';

// Default_Shipper
$SQL = "SELECT shipper_id, shippername FROM shippers orDER BY shippername";
$ErrMsg = _('Could not load shippers');
$Result = DB_query($SQL, $ErrMsg);
echo '<field>
		<label for="DefaultShipper">', _('Default Shipper'), ':</label>
		<select required="required" name="DefaultShipper">';
while ($MyRow = DB_fetch_array($Result)) {
	if ($_POST['DefaultShipper'] == $MyRow['shipper_id']) {
		echo '<option selected="selected" value="', $MyRow['shipper_id'], '">', $MyRow['shippername'], '</option>';
	} else {
		echo '<option value="', $MyRow['shipper_id'], '">', $MyRow['shippername'], '</option>';
	}
}
echo '</select>
	<fieldhelp>', _('If the supplier has a default shipper that they normally use then select it here, and it will appear by default when you create a purchase order for this supplier.'), '</fieldhelp>
</field>';

$Result = DB_query("SELECT accountcode,
						accountname
					FROM chartmaster
					INNER JOIN accountgroups
						ON chartmaster.groupcode=accountgroups.groupcode
						AND chartmaster.language=accountgroups.language
					WHERE accountgroups.pandl=1
						AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
					ORDER BY chartmaster.accountcode");
echo '<field>
		<label for="DefaultGL">', _('Default GL Account'), ':</label>
		<select name="DefaultGL">';

while ($MyRow = DB_fetch_row($Result)) {
	if ($_POST['DefaultGL'] == $MyRow[0]) {
		echo '<option selected="selected" value="', $MyRow[0], '">', htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8'), ' (', $MyRow[0], ')</option>';
	} else {
		echo '<option value="', $MyRow[0], '">', htmlspecialchars($MyRow[1], ENT_QUOTES, 'UTF-8'), ' (', $MyRow[0], ')</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>', _('If the supplier has a general ledger code that their invoices are nrmally posted to, then enter it here and when you create a general ledger invoice for this supplier this code will appear by default.'), '</fieldhelp>
</field>';

echo '<field>
		<label for="TaxGroup">', _('Tax Group'), ':</label>
		<select name="TaxGroup">';

$SQL = "SELECT taxgroupid, taxgroupdescription FROM taxgroups";
$Result = DB_query($SQL);

while ($MyRow = DB_fetch_array($Result)) {
	if ($MyRow['taxgroupid'] == $_POST['TaxGroup']) {
		echo '<option selected="selected" value="', $MyRow['taxgroupid'], '">', $MyRow['taxgroupdescription'], '</option>';
	} else {
		echo '<option value="', $MyRow['taxgroupid'], '">', $MyRow['taxgroupdescription'], '</option>';
	}
} //end while loop
echo '</select>
	<fieldhelp>', _('What tax group does this supplier belong to'), '</fieldhelp>
</field>';

echo '</fieldset>';

if (!isset($SupplierID) or isset($DuplicateSupplierNo)) {
	echo '<div class="centre">
			<input type="submit" name="insert" value="', _('Add These New Supplier Details'), '" />
		</div>';
} else {
	echo '<div class="centre">
					<input type="submit" name="update" value="' . _('Update Supplier') . '" />
				</div>';

	prnMsg(_('There is no second warning if you hit the delete button below') . '. ' . _('However checks will be made to ensure there are no outstanding purchase orders or existing accounts payable transactions before the deletion is processed'), 'Warn');
	echo '<div class="centre">
			<input type="submit" name="delete" value="', _('Delete Supplier'), '" onclick="return MakeConfirm(\'', _('Are you sure you wish to delete this supplier?'), '\');" />
		</div>';
}
echo '</form>';
// end of main ifs
include ('includes/footer.php');
?>