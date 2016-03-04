<?php

include('includes/session.inc');

$Title = _('Dispatch Tax Province Maintenance');

$ViewTopic = 'Tax';// Filename in ManualContents.php's TOC.
$BookMark = 'TaxProvinces';// Anchor's id in the manual's html document.
include('includes/header.inc');

echo '<p class="page_title_text" >
		<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
	</p>';

if (isset($_GET['SelectedTaxProvince']))
	$SelectedTaxProvince = $_GET['SelectedTaxProvince'];
elseif (isset($_POST['SelectedTaxProvince']))
	$SelectedTaxProvince = $_POST['SelectedTaxProvince'];

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (trim($_POST['TaxProvinceName']) == '') {
		$InputError = 1;
		prnMsg(_('The tax province name may not be empty'), 'error');
	}

	if (isset($_POST['SelectedTaxProvince']) and $_POST['SelectedTaxProvince'] != '' and $InputError != 1) {

		/*SelectedTaxProvince could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		// Check the name does not clash
		$SQL = "SELECT count(*) FROM taxprovinces
				WHERE taxprovinceid <> '" . $SelectedTaxProvince . "'
				AND taxprovincename " . LIKE . " '" . $_POST['TaxProvinceName'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0] > 0) {
			$InputError = 1;
			prnMsg(_('The tax province cannot be renamed because another with the same name already exists.'), 'error');
		} else {
			// Get the old name and check that the record still exists
			$SQL = "SELECT taxprovincename FROM taxprovinces
						WHERE taxprovinceid = '" . $SelectedTaxProvince . "'";
			$Result = DB_query($SQL);
			if (DB_num_rows($Result) != 0) {
				// This is probably the safest way there is
				$MyRow = DB_fetch_row($Result);
				$OldTaxProvinceName = $MyRow[0];
				$SQL = "UPDATE taxprovinces
					SET taxprovincename='" . $_POST['TaxProvinceName'] . "'
					WHERE taxprovincename " . LIKE . " '" . DB_escape_string($OldTaxProvinceName) . "'";
				$ErrMsg = _('Could not update tax province');
				$Result = DB_query($SQL, $ErrMsg);
				if (!$Result) {
					prnMsg(_('Tax province name changed'), 'success');
				}
			} else {
				$InputError = 1;
				prnMsg(_('The tax province no longer exists'), 'error');
			}
		}
	} elseif ($InputError != 1) {
		/*SelectedTaxProvince is null cos no item selected on first time round so must be adding a record*/
		$SQL = "SELECT count(*) FROM taxprovinces
				WHERE taxprovincename " . LIKE . " '" . $_POST['TaxProvinceName'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);

		if ($MyRow[0] > 0) {

			$InputError = 1;
			prnMsg(_('The tax province cannot be created because another with the same name already exists'), 'error');

		} else {

			$SQL = "INSERT INTO taxprovinces (taxprovincename )
					VALUES ('" . $_POST['TaxProvinceName'] . "')";

			$ErrMsg = _('Could not add tax province');
			$Result = DB_query($SQL, $ErrMsg);

			$TaxProvinceID = DB_Last_Insert_ID('taxprovinces', 'taxprovinceid');
			$SQL = "INSERT INTO taxauthrates (taxauthority, dispatchtaxprovince, taxcatid)
					SELECT taxauthorities.taxid, '" . $TaxProvinceID . "', taxcategories.taxcatid
					FROM taxauthorities CROSS JOIN taxcategories";
			$ErrMsg = _('Could not add tax authority rates for the new dispatch tax province. The rates of tax will not be able to be added - manual database interaction will be required to use this dispatch tax province');
			$Result = DB_query($SQL, $ErrMsg);
		}

		if (!$Result) {
			prnMsg(_('Errors were encountered adding this tax province'), 'error');
		} else {
			prnMsg(_('New tax province added'), 'success');
		}
	}
	unset($SelectedTaxProvince);
	unset($_POST['SelectedTaxProvince']);
	unset($_POST['TaxProvinceName']);

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button
	// PREVENT DELETES IF DEPENDENT RECORDS IN 'stockmaster'
	// Get the original name of the tax province the ID is just a secure way to find the tax province
	$SQL = "SELECT taxprovincename FROM taxprovinces
		WHERE taxprovinceid = '" . $SelectedTaxProvince . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 0) {
		// This is probably the safest way there is
		prnMsg(_('Cannot delete this tax province because it no longer exists'), 'warn');
	} else {
		$MyRow = DB_fetch_row($Result);
		$OldTaxProvinceName = $MyRow[0];
		$SQL = "SELECT COUNT(*) FROM locations WHERE taxprovinceid = '" . $SelectedTaxProvince . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0] > 0) {
			prnMsg(_('Cannot delete this tax province because at least one stock location is defined to be inside this province'), 'warn');
			echo '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('stock locations that refer to this tax province') . '</font>';
		} else {
			$SQL = "DELETE FROM taxauthrates WHERE dispatchtaxprovince = '" . $SelectedTaxProvince . "'";
			$Result = DB_query($SQL);
			$SQL = "DELETE FROM taxprovinces WHERE taxprovinceid = '" . $SelectedTaxProvince . "'";
			$Result = DB_query($SQL);
			prnMsg($OldTaxProvinceName . ' ' . _('tax province and any tax rates set for it have been deleted'), 'success');
		}
	} //end if
	unset($SelectedTaxProvince);
	unset($_GET['SelectedTaxProvince']);
	unset($_GET['delete']);
	unset($_POST['SelectedTaxProvince']);
	unset($_POST['TaxProvinceName']);
}

if (!isset($SelectedTaxProvince)) {

	/* An tax province could be posted when one has been edited and is being updated
	or GOT when selected for modification
	SelectedTaxProvince will exist because it was sent with the page in a GET .
	If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of account groups will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/

	$SQL = "SELECT taxprovinceid,
			taxprovincename
			FROM taxprovinces
			ORDER BY taxprovinceid";

	$ErrMsg = _('Could not get tax categories because');
	$Result = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result) == 0) {
		echo '<div class="page_help_text">' . _('As this is the first time that the system has been used, you must first create a tax province.') .
				'<br />' . _('For help, click on the help icon in the top right') .
				'<br />' . _('Once you have filled in the details, click on the button at the bottom of the screen') . '</div>';
	}

	echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">', _('Tax Provinces'), '</th>
					<th colspan="2">', _('Maintenance'), '</th>
				</tr>
			</thead>';

	echo '<tbody>';
	$k = 0; //row colour counter
	while ($MyRow = DB_fetch_row($Result)) {

		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			++$k;
		}

		echo '<td>' . $MyRow[1] . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedTaxProvince=' . $MyRow[0] . '">' . _('Edit') . '</a></td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedTaxProvince=' . $MyRow[0] . '&amp;delete=1">' . _('Delete') . '</a></td>
			</tr>';

	} //END WHILE LIST LOOP
	echo '</tbody>';
	echo '</table>';
} //end of ifs and buts!


if (isset($SelectedTaxProvince)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Review Tax Provinces') . '</a>
		</div>';
}

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedTaxProvince)) {
		//editing an existing section

		$SQL = "SELECT taxprovinceid,
				taxprovincename
				FROM taxprovinces
				WHERE taxprovinceid='" . $SelectedTaxProvince . "'";

		$Result = DB_query($SQL);
		if (DB_num_rows($Result) == 0) {
			prnMsg(_('Could not retrieve the requested tax province, please try again.'), 'warn');
			unset($SelectedTaxProvince);
		} else {
			$MyRow = DB_fetch_array($Result);

			$_POST['TaxProvinceName'] = $MyRow['taxprovincename'];

			echo '<input type="hidden" name="SelectedTaxProvince" value="' . $MyRow['taxprovinceid'] . '" />';
			echo '<table class="selection">';
		}

	} else {
		$_POST['TaxProvinceName'] = '';
		echo '<table class="selection">';
	}
	echo '<tr>
			<td>' . _('Tax Province Name') . ':' . '</td>
			<td><input type="text" name="TaxProvinceName" size="30" required="required" maxlength="30" value="' . $_POST['TaxProvinceName'] . '" /></td>
		</tr>
		</table>';

	echo '<div class="centre">
			<input type="submit" name="submit" value="' . _('Enter Information') . '" />
		</div>';

	echo '</form>';

} //end if record deleted no point displaying form to add record

echo '<div class="centre">
		<a href="' . $RootPath . '/TaxAuthorities.php">' . _('Edit/Review Tax Authorities') . '</a>
		<a href="' . $RootPath . '/TaxGroups.php">' . _('Edit/Review Tax Groupings') . '</a>
		<a href="' . $RootPath . '/TaxCategories.php">' . _('Edit/Review Tax Categories') . '</a>
	</div>';

include('includes/footer.inc');
?>