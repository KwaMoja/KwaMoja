<?php
include ('includes/session.php');

$Title = _('Tax Group Maintenance');
$ViewTopic = 'Tax'; // Filename in ManualContents.php's TOC.
$BookMark = 'TaxGroups'; // Anchor's id in the manual's html document.
include ('includes/header.php');

if (isset($_GET['SelectedGroup'])) {
	$SelectedGroup = $_GET['SelectedGroup'];
} elseif (isset($_POST['SelectedGroup'])) {
	$SelectedGroup = $_POST['SelectedGroup'];
}

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', $Title, '" alt="" />', ' ', $Title, '
	</p>';

if (isset($_POST['submit']) or isset($_GET['remove']) or isset($_GET['add'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	 ie the page has called itself with some user input */
	//first off validate inputs sensible
	if (isset($_POST['GroupName']) and mb_strlen($_POST['GroupName']) < 4) {
		$InputError = 1;
		prnMsg(_('The Group description entered must be at least 4 characters long'), 'error');
	}

	// if $_POST['GroupName'] then it is a modification of a tax group name
	// else it is either an add or remove of taxgroup
	unset($SQL);
	if (isset($_POST['GroupName'])) { // Update or Add a tax group
		if (isset($SelectedGroup)) { // Update a tax group
			$SQL = "UPDATE taxgroups SET taxgroupdescription = '" . $_POST['GroupName'] . "'
					WHERE taxgroupid = '" . $SelectedGroup . "'";
			$ErrMsg = _('The update of the tax group description failed because');
			$Result = DB_query($SQL, $ErrMsg);
			if ($Result) {
				prnMsg(_('The tax group description was updated to') . ' ' . $_POST['GroupName'], 'success');
			}
			unset($SelectedGroup);
		} else { // Add new tax group
			$GroupResult = DB_query("SELECT taxgroupid
								FROM taxgroups
								WHERE taxgroupdescription='" . $_POST['GroupName'] . "'");
			if (DB_num_rows($GroupResult) == 1) {
				prnMsg(_('A new tax group could not be added because a tax group already exists for') . ' ' . $_POST['GroupName'], 'warn');
				unset($SQL);
			} else {
				$SQL = "INSERT INTO taxgroups (taxgroupdescription)
						VALUES ('" . $_POST['GroupName'] . "')";
				$ErrMsg = _('The addition of the group failed because');
				$Result = DB_query($SQL, $ErrMsg);
				if ($Result) {
					prnMsg(_('Added the new tax group') . ' ' . $_POST['GroupName'], 'success');
				}
				$GroupResult = DB_query("SELECT taxgroupid
									FROM taxgroups
									WHERE taxgroupdescription='" . $_POST['GroupName'] . "'");
				$GroupRow = DB_fetch_array($GroupResult);
				$SelectedGroup = $GroupRow['taxgroupid'];
			}
		}
		unset($_POST['GroupName']);
	} elseif (isset($SelectedGroup) and isset($_GET['TaxAuthority'])) {
		$TaxAuthority = $_GET['TaxAuthority'];
		if (isset($_GET['add'])) { // adding a tax authority to a tax group
			$SQL = "INSERT INTO taxgrouptaxes ( taxgroupid,
												taxauthid,
												calculationorder)
					VALUES ('" . $SelectedGroup . "',
							'" . $TaxAuthority . "',
							0)";

			$ErrMsg = _('The addition of the tax failed because');
			$Result = DB_query($SQL, $ErrMsg);
			if ($Result) {
				prnMsg(_('The tax was added.'), 'success');
			}
		} elseif (isset($_GET['remove'])) { // remove a taxauthority from a tax group
			$SQL = "DELETE FROM taxgrouptaxes
					WHERE taxgroupid = '" . $SelectedGroup . "'
					AND taxauthid = '" . $TaxAuthority . "'";
			$ErrMsg = _('The removal of this tax failed because');
			$Result = DB_query($SQL, $ErrMsg);
			if ($Result) {
				prnMsg(_('This tax was removed.'), 'success');
			}
		}
		unset($_GET['add']);
		unset($_GET['remove']);
		unset($_GET['TaxAuthority']);
	}
} elseif (isset($_POST['UpdateOrder'])) {
	//A calculation order update
	$SQL = "SELECT taxauthid FROM taxgrouptaxes WHERE taxgroupid='" . $SelectedGroup . "'";
	$Result = DB_query($SQL, _('Could not get tax authorities in the selected tax group'));

	while ($MyRow = DB_fetch_row($Result)) {

		if (is_numeric($_POST['CalcOrder_' . $MyRow[0]]) and $_POST['CalcOrder_' . $MyRow[0]] < 10) {

			$UpdateSQL = "UPDATE taxgrouptaxes
				SET calculationorder='" . $_POST['CalcOrder_' . $MyRow[0]] . "',
					taxontax='" . $_POST['TaxOnTax_' . $MyRow[0]] . "'
				WHERE taxgroupid='" . $SelectedGroup . "'
				AND taxauthid='" . $MyRow[0] . "'";

			$UpdateResult = DB_query($UpdateSQL);
		}
	}

	//need to do a reality check to ensure that taxontax is relevant only for taxes after the first tax
	$SQL = "SELECT taxauthid,
					taxontax
			FROM taxgrouptaxes
			WHERE taxgroupid='" . $SelectedGroup . "'
			ORDER BY calculationorder";

	$Result = DB_query($SQL, _('Could not get tax authorities in the selected tax group'));

	if (DB_num_rows($Result) > 0) {
		$MyRow = DB_fetch_array($Result);
		if ($MyRow['taxontax'] == 1) {
			prnMsg(_('It is inappropriate to set tax on tax where the tax is the first in the calculation order. The system has changed it back to no tax on tax for this tax authority'), 'warning');
			$Result = DB_query("UPDATE taxgrouptaxes SET taxontax=0
								WHERE taxgroupid='" . $SelectedGroup . "'
								AND taxauthid='" . $MyRow['taxauthid'] . "'");
		}
	}
} elseif (isset($_GET['Delete'])) {

	/* PREVENT DELETES IF DEPENDENT RECORDS IN 'custbranch, suppliers */

	$SQL = "SELECT COUNT(*) FROM custbranch WHERE taxgroupid='" . $_GET['SelectedGroup'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		prnMsg(_('Cannot delete this tax group because some customer branches are setup using it'), 'warn');
		echo '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('customer branches referring to this tax group');
	} else {
		$SQL = "SELECT COUNT(*) FROM suppliers
				WHERE taxgroupid='" . $_GET['SelectedGroup'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0] > 0) {
			prnMsg(_('Cannot delete this tax group because some suppliers are setup using it'), 'warn');
			echo '<br />' . _('There are') . ' ' . $MyRow[0] . ' ' . _('suppliers referring to this tax group');
		} else {

			$SQL = "DELETE FROM taxgrouptaxes
					WHERE taxgroupid='" . $_GET['SelectedGroup'] . "'";
			$Result = DB_query($SQL);
			$SQL = "DELETE FROM taxgroups
					WHERE taxgroupid='" . $_GET['SelectedGroup'] . "'";
			$Result = DB_query($SQL);
			prnMsg($_GET['GroupID'] . ' ' . _('tax group has been deleted') . '!', 'success');
		}
	} //end if taxgroup used in other tables
	unset($SelectedGroup);
	unset($_GET['GroupName']);
}

if (!isset($SelectedGroup)) {

	/* If its the first time the page has been displayed with no parameters then none of the above are true and the list of tax groups will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of tax group taxes*/

	$SQL = "SELECT taxgroupid,
					taxgroupdescription
			FROM taxgroups";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) == 0) {
		echo '<div class="page_help_text">', _('As this is the first time that the system has been used, you must first create a tax group.'), '<br />', _('For help, click on the help icon in the top right'), '<br />', _('Once you have filled in all the details, click on the button at the bottom of the screen'), '</div>';
	}

	if (DB_num_rows($Result) == 0) {
		prnMsg(_('There are no tax groups configured.'), 'info');
	} else {
		echo '<table>
				<thead>
					<tr>
						<th class="SortedColumn">' . _('Tax Group') . '</th>
						<th colspan="2" >', _('Maintenance') . '</th>
					</tr>
				</thead>';

		echo '<tbody>';

		while ($MyRow = DB_fetch_array($Result)) {

			echo '<tr class="striped_row">
					<td>', $MyRow['taxgroupdescription'], '</td>
					<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?', '&amp;SelectedGroup=', $MyRow['taxgroupid'], '">', _('Edit'), '</a></td>
					<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?', '&amp;SelectedGroup=', $MyRow['taxgroupid'], '&amp;Delete=1&amp;GroupID=', urlencode($MyRow['taxgroupdescription']), '" onclick="return MakeConfirm(\'', _('Are you sure you wish to delete this tax group?'), '\', \'Confirm Delete\', this);">', _('Delete'), '</a></td>
				</tr>';

		} //END WHILE LIST LOOP
		echo '</tbody>';
		echo '</table>';
	}
} //end of ifs and buts!


if (isset($SelectedGroup)) {
	echo '<div class="centre">
			<a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">', _('Review Existing Groups'), '</a>
		</div>';
}

if (isset($SelectedGroup)) {
	//editing an existing role
	$SQL = "SELECT taxgroupid,
					taxgroupdescription
			FROM taxgroups
			WHERE taxgroupid='" . $SelectedGroup . "'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 0) {
		prnMsg(_('The selected tax group is no longer available.'), 'warn');
	} else {
		$MyRow = DB_fetch_array($Result);
		$_POST['SelectedGroup'] = $MyRow['taxgroupid'];
		$_POST['GroupName'] = $MyRow['taxgroupdescription'];
	}
}

echo '<form method="post" action="' . htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (isset($_POST['SelectedGroup'])) {
	echo '<input type="hidden" name="SelectedGroup" value="' . $_POST['SelectedGroup'] . '" />';
}

if (!isset($_POST['GroupName'])) {
	$_POST['GroupName'] = '';
}
echo '<fieldset>
		<legend>', _('Group Details'), '</legend>
		<field>
			<label for="GroupName">', _('Tax Group'), '</label>
			<input type="text" name="GroupName" size="40" autofocus="autofocus" required="required" maxlength="40" value="', $_POST['GroupName'], '" />
			<fieldhelp>', _('Enter the name by which this tax group will be known'), '</fieldhelp>
		</field>
	</fieldset>';

echo '<div class="centre">
		<input type="submit" name="submit" value="', _('Enter Group'), '" />
	</div>
</form>';

if (isset($SelectedGroup)) {

	$SQL = "SELECT taxid,
			description as taxname
			FROM taxauthorities
			ORDER BY taxid";

	$SQLUsed = "SELECT taxauthid,
				description AS taxname,
				calculationorder,
				taxontax
			FROM taxgrouptaxes INNER JOIN taxauthorities
				ON taxgrouptaxes.taxauthid=taxauthorities.taxid
			WHERE taxgroupid='" . $SelectedGroup . "'
			ORDER BY calculationorder";

	$Result = DB_query($SQL);

	/*Make an array of the used tax authorities in calculation order */
	$UsedResult = DB_query($SQLUsed);
	$TaxAuthsUsed = array(); //this array just holds the taxauthid of all authorities in the group
	$TaxAuthRow = array(); //this array holds all the details of the tax authorities in the group
	$i = 1;
	while ($MyRow = DB_fetch_array($UsedResult)) {
		$TaxAuthsUsed[$i] = $MyRow['taxauthid'];
		$TaxAuthRow[$i] = $MyRow;
		++$i;
	}

	/* the order and tax on tax will only be an issue if more than one tax authority in the group */
	if (count($TaxAuthsUsed) > 0) {
		echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
		echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

		echo '<input type="hidden" name="SelectedGroup" value="', $SelectedGroup, '" />';

		echo '<table>
				<thead>
					<tr>
						<th colspan="3">', _('Calculation Order'), '</th>
					</tr>
					<tr>
						<th class="SortedColumn">', _('Tax Authority'), '</th>
						<th>', _('Order'), '</th>
						<th>', _('Tax on Prior Taxes'), '</th>
					</tr>
				</thead>';

		echo '<tbody>';
		for ($i = 1;$i < count($TaxAuthRow) + 1;$i++) {

			if ($TaxAuthRow[$i]['calculationorder'] == 0) {
				$TaxAuthRow[$i]['calculationorder'] = $i;
			}

			echo '<tr class="striped_row">
					<td>', $TaxAuthRow[$i]['taxname'], '</td>
					<td>
						<input type="text" class="number" name="CalcOrder_', $TaxAuthRow[$i]['taxauthid'], '" value="', $TaxAuthRow[$i]['calculationorder'], '" size="2" required="required" maxlength="2" />
					</td>
					<td>
						<select required="required" name="TaxOnTax_', $TaxAuthRow[$i]['taxauthid'], '">';
			if ($TaxAuthRow[$i]['taxontax'] == 1) {
				echo '<option selected="selected" value="1">', _('Yes'), '</option>';
				echo '<option value="0">', _('No'), '</option>';
			} else {
				echo '<option value="1">', _('Yes'), '</option>';
				echo '<option selected="selected" value="0">', _('No'), '</option>';
			}
			echo '</select>
					</td>
				</tr>';

		}
		echo '</tbody>
			</table>';

		echo '<div class="centre">
				<input type="submit" name="UpdateOrder" value="', _('Update Order'), '" />
			</div>';
	}

	echo '</form>';

	if (DB_num_rows($UsedResult) == 0) {
		echo '<div class="page_help_text">', _('As this is the first time that the system has been used, you must first create a tax group.'), '<br />', _('For help, click on the help icon in the top right'), '<br />', _('Once you have filled in all the details, click on the button at the bottom of the screen'), '</div>';
	} elseif (DB_num_rows($UsedResult) == 1 and isset($_SESSION['FirstStart'])) {
		echo '<meta http-equiv="refresh" content="0; url=', $RootPath, '/TaxProvinces.php">';
		exit;
	}

	if (DB_num_rows($Result) > 0) {
		echo '<table>
				<thead>
					<tr>
						<th colspan="5">', _('Assigned Taxes'), '</th>
						<th colspan="3">', _('Available Taxes'), '</th>
					</tr>
					<tr>
						<th>', _('Tax Auth ID'), '</th>
						<th>', _('Tax Authority Name'), '</th>
						<th>', _('Calculation Order'), '</th>
						<th>', _('Tax on Prior Tax(es)'), '</th>
						<th></th>
						<th>', _('Tax Auth ID'), '</th>
						<th>', _('Tax Authority Name'), '</th>
						<th></th>
					</tr>
				</thead>';

	} else {
		echo '<div class="centre">
				', _('There are no tax authorities defined to allocate to this tax group'), '
			</div>';
	}

	echo '<tbody>';

	while ($AvailRow = DB_fetch_array($Result)) {
		$TaxAuthUsedPointer = array_search($AvailRow['taxid'], $TaxAuthsUsed);

		if ($TaxAuthUsedPointer) {

			if ($TaxAuthRow[$TaxAuthUsedPointer]['taxontax'] == 1) {
				$TaxOnTax = _('Yes');
			} else {
				$TaxOnTax = _('No');
			}

			echo '<tr class="striped_row">
					<td>', $AvailRow['taxid'], '</td>
					<td>', $AvailRow['taxname'], '</td>
					<td>', $TaxAuthRow[$TaxAuthUsedPointer]['calculationorder'], '</td>
					<td>', $TaxOnTax, '</td>
					<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedGroup=', $SelectedGroup, '&amp;remove=1&amp;TaxAuthority=', $AvailRow['taxid'], '" onclick="return MakeConfirm(\'', _('Are you sure you wish to remove this tax authority from the group?'), '\', \'Confirm Delete\', this);">', _('Remove'), '</a></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>';

		} else {
			echo '<tr class="striped_row">
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>', $AvailRow['taxid'], '</td>
					<td>', $AvailRow['taxname'], '</td>
					<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedGroup=', $SelectedGroup, '&amp;add=1&amp;TaxAuthority=', $AvailRow['taxid'], '">', _('Add'), '</a></td>
				</tr>';
		}
	}
	echo '</tbody>';
	echo '</table>';

}

echo '<div class="centre">
		<a href="', $RootPath, '/TaxAuthorities.php">', _('Tax Authorities and Rates Maintenance'), '</a><br />
		<a href="', $RootPath, '/TaxProvinces.php">', _('Dispatch Tax Province Maintenance'), '</a><br />
		<a href="', $RootPath, '/TaxCategories.php">', _('Tax Category Maintenance'), '</a>
	</div>';

include ('includes/footer.php');

?>