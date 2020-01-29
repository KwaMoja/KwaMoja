<?php
$PageSecurity = 15;

include('includes/session.php');

if (!isset($_SESSION['InitialScripts'])) {
	$_SESSION['InitialScripts'][] = 'SystemParameters.php';
	$_SESSION['InitialScripts'][] = 'BankAccountUsers.php';
	$_SESSION['InitialScripts'][] = 'BankAccounts.php';
	$_SESSION['InitialScripts'][] = 'Shippers.php';
	$_SESSION['InitialScripts'][] = 'SalesTypes.php';
	$_SESSION['InitialScripts'][] = 'Locations.php';
	$_SESSION['InitialScripts'][] = 'TaxAuthorityRates.php';
	$_SESSION['InitialScripts'][] = 'TaxGroups.php';
	$_SESSION['InitialScripts'][] = 'TaxAuthorities.php';
	$_SESSION['InitialScripts'][] = 'TaxProvinces.php';
	$_SESSION['InitialScripts'][] = 'TaxCategories.php';
	$_SESSION['InitialScripts'][] = 'CompanyPreferences.php';
	$_SESSION['InitialScripts'][] = 'Currencies.php';
}

$Title = _('Initialise New Installation');

include('includes/header.php');

echo '<p class="page_title_text" >
		<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Initialise New Installation') . '" alt="' . _('Initialise New Installation') . '" />' . $Title . '
	</p>';

echo '<div class="page_help_text">' .
		_('You have reached this page because you have successfuly installed ') . $ProjectName . _(', but there are still some pages that need to be setup.') . '<br />' .
		_('Click on the link below and you will be taken to the next page you need to complete.') . '<br />' .
		_('Once you have completed the page, click on the main menu icon and if there are more pages to be completed you will be brought back here.') . '
	</div>';

$ThisScript = '';
while ($ThisScript == '' and count($_SESSION['InitialScripts']) > 0) {
	$NextScript = array_pop($_SESSION['InitialScripts']);
	if (trim($NextScript) == 'Currencies.php') {
		$CurrenciesSQL = "SELECT currency FROM currencies";
		$CurrenciesResult = DB_query($CurrenciesSQL);
		if (DB_num_rows($CurrenciesResult) == 0) {
			$ThisScript = $NextScript;
		}
	}
	if (trim($NextScript) == 'CompanyPreferences.php' and $ThisScript == '') {
		$CompanySQL = "SELECT coycode FROM companies";
		$CompanyResult = DB_query($CompanySQL);
		if (DB_num_rows($CompanyResult) == 0) {
			$ThisScript = $NextScript;
		}
	}
	if (trim($NextScript) == 'TaxProvinces.php' and $ThisScript == '') {
		$ProvinceSQL = "SELECT taxprovinceid FROM taxprovinces";
		$ProvinceResult = DB_query($ProvinceSQL);
		if (DB_num_rows($ProvinceResult) == 0) {
			$ThisScript = $NextScript;
		}
	}
	if (trim($NextScript) == 'TaxAuthorities.php' and $ThisScript == '') {
		$AuthoritySQL = "SELECT taxid FROM taxauthorities";
		$AuthorityResult = DB_query($AuthoritySQL);
		if (DB_num_rows($AuthorityResult) == 0) {
			$ThisScript = $NextScript;
		} else {
			$Row = DB_fetch_row($AuthorityResult);
			$_SESSION['TaxAuthority'] = $Row[0];
		}
	}
	if (trim($NextScript) == 'TaxCategories.php' and $ThisScript == '') {
		$CategorySQL = "SELECT taxcatid FROM taxcategories";
		$CategoryResult = DB_query($CategorySQL);
		if (DB_num_rows($CategoryResult) == 0) {
			$ThisScript = $NextScript;
		}
	}
	if (trim($NextScript) == 'TaxAuthorityRates.php' and $ThisScript == '') {
		$AuthRatesSQL = "SELECT SUM(taxrate) FROM taxauthrates";
		$AuthRatesResult = DB_query($AuthRatesSQL);
		$Row = DB_fetch_row($AuthRatesResult);
		if ($Row[0] == 0) {
			$ThisScript = 'TaxAuthorityRates.php?TaxAuthority=' . $_SESSION['TaxAuthority'];
		}
	}
	if (trim($NextScript) == 'Locations.php' and $ThisScript == '') {
		$LocationsSQL = "SELECT loccode FROM locations";
		$LocationsResult = DB_query($LocationsSQL);
		if (DB_num_rows($LocationsResult) == 0) {
			$ThisScript = $NextScript;
		}
	}
	if (trim($NextScript) == 'SalesTypes.php' and $ThisScript == '') {
		$SalesTypesSQL = "SELECT typeabbrev FROM salestypes";
		$SalesTypesResult = DB_query($SalesTypesSQL);
		if (DB_num_rows($SalesTypesResult) == 0) {
			$ThisScript = $NextScript;
		}
	}
	if (trim($NextScript) == 'Shippers.php' and $ThisScript == '') {
		$ShippersSQL = "SELECT shipper_id FROM shippers";
		$ShippersResult = DB_query($ShippersSQL);
		if (DB_num_rows($ShippersResult) == 0) {
			$ThisScript = $NextScript;
		}
	}
	if (trim($NextScript) == 'SystemParameters.php' and $ThisScript == '') {
		$ThisScript = $NextScript;
		$_SESSION['FirstLogIn'] = '0';
		$SQL = "UPDATE config SET confvalue=0 WHERE confname='FirstLogIn'";
		$Result = DB_query($SQL);
	}
}

echo '<div class="centre">
		<a href="' . $RootPath . '/' . $ThisScript . '">' . _('Click here to continue') . '</a>
	</div>';

include('includes/footer.php');

?>