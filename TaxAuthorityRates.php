<?php
if (isset($_POST['TaxAuthority'])) {
	$TaxAuthority = $_POST['TaxAuthority'];
}
if (isset($_GET['TaxAuthority'])) {
	$TaxAuthority = $_GET['TaxAuthority'];
}

include ('includes/session.php');
$Title = _('Tax Rates Maintenance');
$ViewTopic = 'Tax'; // Filename in ManualContents.php's TOC.
$BookMark = 'TaxAuthorityRates'; // Anchor's id in the manual's html document.
include ('includes/header.php');

echo '<p class="page_title_text" >
		<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . $Title . '" alt="" />' . $Title . '
	</p>';

if (!isset($TaxAuthority)) {
	prnMsg(_('This page can only be called after selecting the tax authority to edit the rates for') . '. ' . _('Please select the Rates link from the tax authority page') . '<br /><a href="' . $RootPath . '/TaxAuthorities.php">' . _('click here') . '</a> ' . _('to go to the Tax Authority page'), 'error');
	include ('includes/footer.php');
	exit;
}

if (isset($_POST['UpdateRates'])) {

	$TaxRatesResult = DB_query("SELECT taxauthrates.taxcatid,
										taxauthrates.taxrate,
										taxauthrates.dispatchtaxprovince
								FROM taxauthrates
								WHERE taxauthrates.taxauthority='" . $TaxAuthority . "'");

	while ($MyRow = DB_fetch_array($TaxRatesResult)) {

		$SQL = "UPDATE taxauthrates SET taxrate=" . (filter_number_format($_POST[$MyRow['dispatchtaxprovince'] . '_' . $MyRow['taxcatid']]) / 100) . "
						WHERE taxcatid = '" . $MyRow['taxcatid'] . "'
						AND dispatchtaxprovince = '" . $MyRow['dispatchtaxprovince'] . "'
						AND taxauthority = '" . $TaxAuthority . "'";
		DB_query($SQL);
	}
	prnMsg(_('All rates updated successfully'), 'info');
}

/* end of update code
*/

/*Display updated rates
*/

$TaxAuthDetail = DB_query("SELECT description
							FROM taxauthorities WHERE taxid='" . $TaxAuthority . "'");
$MyRow = DB_fetch_row($TaxAuthDetail);

echo '<form action="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<input type="hidden" name="TaxAuthority" value="' . $TaxAuthority . '" />';

$TaxRatesResult = DB_query("SELECT taxauthrates.taxcatid,
									taxcategories.taxcatname,
									taxauthrates.taxrate,
									taxauthrates.dispatchtaxprovince,
									taxprovinces.taxprovincename
							FROM taxauthrates INNER JOIN taxauthorities
							ON taxauthrates.taxauthority=taxauthorities.taxid
							INNER JOIN taxprovinces
							ON taxauthrates.dispatchtaxprovince= taxprovinces.taxprovinceid
							INNER JOIN taxcategories
							ON taxauthrates.taxcatid=taxcategories.taxcatid
							WHERE taxauthrates.taxauthority='" . $TaxAuthority . "'
							ORDER BY taxauthrates.dispatchtaxprovince,
							taxauthrates.taxcatid");

if (isset($_SESSION['FirstStart'])) {
	echo '<div class="page_help_text">' . _('As this is the first time that the system has been used, you must first create a tax authority.') . '<br />' . _('For help, click on the help icon in the top right') . '<br />' . _('Once you have filled in all the details, click on the button at the bottom of the screen') . '</div>';
}

if (DB_num_rows($TaxRatesResult) > 0) {

	echo '<table>
			<thead>
				<tr>
					<th colspan="3"><h3>' . _('Update') . ' ' . $MyRow[0] . ' ' . _('Rates') . '</h3></th>
				</tr>
				<tr>
					<th class="SortedColumn">' . _('Deliveries From') . '<br />' . _('Tax Province') . '</th>
					<th class="SortedColumn">' . _('Tax Category') . '</th>
					<th>' . _('Tax Rate') . ' %</th>
				</tr>
			</thead>';
	$k = 0; //row counter to determine background colour
	$OldProvince = '';
	echo '<tbody>';
	while ($MyRow = DB_fetch_array($TaxRatesResult)) {

		if ($OldProvince != $MyRow['dispatchtaxprovince'] and $OldProvince != '') {
			echo '<tr style="background-color:#555555"><td colspan="3"></td></tr>';
		}

		printf('<tr class="striped_row">
					<td>%s</td>
					<td>%s</td>
					<td><input type="text" class="number" name="%s" required="required" maxlength="5" size="5" value="%s" /></td>
				</tr>', $MyRow['taxprovincename'], _($MyRow['taxcatname']), $MyRow['dispatchtaxprovince'] . '_' . $MyRow['taxcatid'], locale_number_format($MyRow['taxrate'] * 100, 3));

		$OldProvince = $MyRow['dispatchtaxprovince'];

	}
	//end of while loop
	echo '</tbody>';
	echo '</table>';
	echo '<div class="centre">
			<input type="submit" name="UpdateRates" value="' . _('Update Rates') . '" />
		</div>';
} else {
	prnMsg(_('There are no tax rates to show - perhaps the dispatch tax province records have not yet been created?'), 'warn');
}

echo '<div class="centre">
		<a href="' . $RootPath . '/TaxAuthorities.php">' . _('Tax Authorities') . '</a><br />
		<a href="' . $RootPath . '/TaxGroups.php">' . _('Tax Groupings') . '</a><br />
		<a href="' . $RootPath . '/TaxCategories.php">' . _('Tax Categories') . '</a><br />
		<a href="' . $RootPath . '/TaxProvinces.php">' . _('Dispatch Tax Provinces') . '</a>
	</div>';

echo '</form>';

include ('includes/footer.php');
?>