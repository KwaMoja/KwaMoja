<?php
include ('includes/session.php');
$Title = _('Label Templates');
include ('includes/header.php');

//define PaperSize array sizes in mm
$PaperSize = array();
$PaperSize['A4']['PageHeight'] = 297;
$PaperSize['A4']['PageWidth'] = 210;
$PaperSize['A5']['PageHeight'] = 210;
$PaperSize['A5']['PageWidth'] = 148;
$PaperSize['A3']['PageHeight'] = 420;
$PaperSize['A3']['PageWidth'] = 297;
$PaperSize['Letter']['PageHeight'] = 279.4;
$PaperSize['Letter']['PageWidth'] = 215.9;
$PaperSize['Legal']['PageHeight'] = 355.6;
$PaperSize['Legal']['PageWidth'] = 215.9;

echo '<p class="page_title_text" >
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', _('Label Template Maintenance'), '" alt="', _('Label Template Maintenance'), '" />', $Title, ' - ', _('All measurements in mm.'), '
	</p>';

if (!function_exists('gd_info')) {
	prnMsg(_('The GD module for PHP is required to print barcode labels. Your PHP installation is not capable currently. You will most likely experience problems with this script until the GD module is enabled.'), 'error');
}

if (isset($_POST['SelectedLabelID'])) {
	$SelectedLabelID = $_POST['SelectedLabelID'];
	if (ctype_digit($_POST['NoOfFieldsDefined'])) { //Now Process any field updates
		for ($i = 0;$i <= $_POST['NoOfFieldsDefined'];$i++) {

			if (ctype_digit($_POST['VPos' . $i]) and ctype_digit($_POST['HPos' . $i]) and ctype_digit($_POST['FontSize' . $i])) { // if all entries are integers
				$Result = DB_query("UPDATE labelfields SET fieldvalue='" . $_POST['FieldName' . $i] . "',
														vpos='" . $_POST['VPos' . $i] . "',
														hpos='" . $_POST['HPos' . $i] . "',
														fontsize='" . $_POST['FontSize' . $i] . "',
														barcode='" . $_POST['Barcode' . $i] . "'
								WHERE labelfieldid='" . $_POST['LabelFieldID' . $i] . "'");
			} else {
				prnMsg(_('Entries for Vertical Position, Horizontal Position, and Font Size must be integers.'), 'error');
			}
		}
	}
	if (ctype_digit($_POST['VPos']) and ctype_digit($_POST['HPos']) and ctype_digit($_POST['FontSize'])) {
		//insert the new label field entered
		$Result = DB_query("INSERT INTO labelfields (labelid,
													fieldvalue,
													vpos,
													hpos,
													fontsize,
													barcode)
							VALUES ('" . $SelectedLabelID . "',
									'" . $_POST['FieldName'] . "',
									'" . $_POST['VPos'] . "',
									'" . $_POST['HPos'] . "',
									'" . $_POST['FontSize'] . "',
									'" . $_POST['Barcode'] . "')");
	}
} elseif (isset($_GET['SelectedLabelID'])) {
	$SelectedLabelID = $_GET['SelectedLabelID'];
	if (isset($_GET['DeleteField'])) { //then process any deleted fields
		$Result = DB_query("DELETE FROM labelfields WHERE labelfieldid='" . $_GET['DeleteField'] . "'");
	}
}

if (isset($_POST['submit'])) {
	$InputError = 0;
	/* actions to take once the user has clicked the submit button
	 ie the page has called itself with some user input */
	if (trim($_POST['Description']) == '') {
		$InputError = 1;
		prnMsg(_('The label description may not be empty'), 'error');
	}
	$Message = '';

	if (isset($_POST['PaperSize']) and $_POST['PaperSize'] != 'custom') {
		$_POST['PageWidth'] = $PaperSize[$_POST['PaperSize']]['PageWidth'];
		$_POST['PageHeight'] = $PaperSize[$_POST['PaperSize']]['PageHeight'];
	} elseif ($_POST['PaperSize'] == 'custom' and !isset($_POST['PageWidth'])) {
		$_POST['PageWidth'] = 0;
		$_POST['PageHeight'] = 0;
	}

	if (isset($SelectedLabelID)) {

		/*SelectedLabelID could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$SQL = "UPDATE labels SET 	description = '" . $_POST['Description'] . "',
									height = '" . $_POST['Height'] . "',
									topmargin = '" . $_POST['TopMargin'] . "',
									width = '" . $_POST['Width'] . "',
									leftmargin = '" . $_POST['LeftMargin'] . "',
									rowheight =  '" . $_POST['RowHeight'] . "',
									columnwidth = '" . $_POST['ColumnWidth'] . "',
									pagewidth = '" . $_POST['PageWidth'] . "',
									pageheight = '" . $_POST['PageHeight'] . "'
				WHERE labelid = '" . $SelectedLabelID . "'";

		$ErrMsg = _('The update of this label template failed because');
		$Result = DB_query($SQL, $ErrMsg);

		$Message = _('The label template has been updated');

	} elseif ($InputError != 1) {

		/*Selected label is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new label form */

		$SQL = "INSERT INTO labels (description,
									height,
									topmargin,
									width,
									leftmargin,
									rowheight,
									columnwidth,
									pagewidth,
									pageheight)
			VALUES ('" . $_POST['Description'] . "',
					'" . $_POST['Height'] . "',
					'" . $_POST['TopMargin'] . "',
					'" . $_POST['Width'] . "',
					'" . $_POST['LeftMargin'] . "',
					'" . $_POST['RowHeight'] . "',
					'" . $_POST['ColumnWidth'] . "',
					'" . $_POST['PageWidth'] . "',
					'" . $_POST['PageHeight'] . "')";

		$ErrMsg = _('The addition of this label failed because');
		$Result = DB_query($SQL, $ErrMsg);
		$Message = _('The new label template has been added to the database');
	}
	//run the SQL from either of the above possibilites
	if (isset($InputError) and $InputError != 1) {
		unset($_POST['PaperSize']);
		unset($_POST['Description']);
		unset($_POST['Width']);
		unset($_POST['Height']);
		unset($_POST['TopMargin']);
		unset($_POST['LeftMargin']);
		unset($_POST['ColumnWidth']);
		unset($_POST['RowHeight']);
		unset($_POST['PageWidth']);
		unset($_POST['PageHeight']);
	}

	prnMsg($Message);

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button
	$Result = DB_query("DELETE FROM labelfields WHERE labelid= '" . $SelectedLabelID . "'");
	$Result = DB_query("DELETE FROM labels WHERE labelid= '" . $SelectedLabelID . "'");
	prnMsg(_('The selected label template has been deleted'), 'success');
	unset($SelectedLabelID);
}

if (!isset($SelectedLabelID)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedLabelID will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then none of the above are true and the list of label templates will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$SQL = "SELECT labelid,
				description,
				pagewidth,
				pageheight,
				height,
				width,
				topmargin,
				leftmargin,
				rowheight,
				columnwidth
			FROM labels";

	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The defined label templates could not be retrieved because');
	$DbgMsg = _('The following SQL to retrieve the label templates was used');
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

	if (DB_num_rows($Result) > 0) {
		echo '<table summary="', _('List of all currently setup Label dimensions'), '">
				<tr>
					<th>', _('Description'), '</th>
					<th>', _('Rows x Cols'), '</th>
					<th>', _('Page Width'), '</th>
					<th>', _('Page Height'), '</th>
					<th>', _('Height'), '</th>
					<th>', _('Width'), '</th>
					<th>', _('Row Height'), '</th>
					<th>', _('Column Width'), '</th>
				</tr>';
		$k = 0;
		while ($MyRow = DB_fetch_array($Result)) {
			if ($MyRow['rowheight'] == 0) {
				$NoOfRows = 0;
			} else {
				$NoOfRows = floor(($MyRow['pageheight'] - $MyRow['topmargin']) / $MyRow['rowheight']);
			}
			if ($MyRow['columnwidth'] == 0) {
				$NoOfCols = 0;
			} else {
				$NoOfCols = floor(($MyRow['pagewidth'] - $MyRow['leftmargin']) / $MyRow['columnwidth']);
			}

			foreach ($PaperSize as $PaperName => $PaperType) {
				if ($PaperType['PageWidth'] == $MyRow['pagewidth'] and $PaperType['PageHeight'] == $MyRow['pageheight']) {
					$Paper = $PaperName;
				}
			}
			if (isset($Paper)) {
				echo '<tr class="striped_row">
						<td>', $MyRow['description'], '</td>
						<td>', $NoOfRows, ' x ', $NoOfCols, '</td>
						<td colspan="2">', $Paper, '</td>
						<td class="number">', $MyRow['height'], '</td>
						<td class="number">', $MyRow['width'], '</td>
						<td class="number">', $MyRow['rowheight'], '</td>
						<td class="number">', $MyRow['columnwidth'], '</td>
						<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedLabelID=', urlencode($MyRow['labelid']), '">', _('Edit'), '</a></td>
						<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedLabelID=', urlencode($MyRow['labelid']), '&delete=yes" onclick="return MakeConfirm(\'', _('Are you sure you wish to delete this label?'), '\', \'Confirm Delete\', this);">', _('Delete'), '</a></td>
					</tr>';
			} else {
				echo '<tr class="striped_row">
						<td>', $MyRow['description'], '</td>
						<td>', $NoOfRows, ' x ', $NoOfCols, '</td>
						<td class="number">', $MyRow['pagewidth'], '</td>
						<td class="number">', $MyRow['pageheight'], '</td>
						<td class="number">', $MyRow['height'], '</td>
						<td class="number">', $MyRow['width'], '</td>
						<td class="number">', $MyRow['rowheight'], '</td>
						<td class="number">', $MyRow['columnwidth'], '</td>
						<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedLabelID=', urlencode($MyRow['labelid']), '">', _('Edit'), '</a></td>
						<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedLabelID=', urlencode($MyRow['labelid']), '&delete=yes" onclick="return MakeConfirm(\'', _('Are you sure you wish to delete this label?'), '\', \'Confirm Delete\', this);">', _('Delete'), '</a></td>
					</tr>';
			}
		}
		//END WHILE LIST LOOP
		//end of ifs and buts!
		echo '</table>';
	} //end if there are label definitions to show
	
}

if (isset($SelectedLabelID)) {
	echo '<div class="centre">
			<a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">', _('Review all defined label records'), '</a>
		</div>';
}

echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

if (isset($SelectedLabelID)) {
	//editing an existing label
	$SQL = "SELECT pagewidth,
					pageheight,
					description,
					height,
					width,
					topmargin,
					leftmargin,
					rowheight,
					columnwidth
			FROM labels
			WHERE labelid='" . $SelectedLabelID . "'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['PageWidth'] = $MyRow['pagewidth'];
	$_POST['PageHeight'] = $MyRow['pageheight'];
	$_POST['Description'] = $MyRow['description'];
	$_POST['Height'] = $MyRow['height'];
	$_POST['TopMargin'] = $MyRow['topmargin'];
	$_POST['Width'] = $MyRow['width'];
	$_POST['LeftMargin'] = $MyRow['leftmargin'];
	$_POST['RowHeight'] = $MyRow['rowheight'];
	$_POST['ColumnWidth'] = $MyRow['columnwidth'];

	foreach ($PaperSize as $PaperName => $PaperType) {
		if ($PaperType['PageWidth'] == $MyRow['pagewidth'] and $PaperType['PageHeight'] == $MyRow['pageheight']) {
			$_POST['PaperSize'] = $PaperName;
		}
	}

	echo '<input type="hidden" name="SelectedLabelID" value="' . $SelectedLabelID . '" />';

} //end of if $SelectedLabelID only do the else when a new record is being entered


if (!isset($_POST['Description'])) {
	$_POST['Description'] = '';
}
echo '<fieldset>
		<fieldset>
			<legend>', _('Label diagram'), '</legend>
			<field>
				<img src="css/labelsDim.png" />
			</field>
		</fieldset>
		<fieldset>
			<legend>', _('Label specifications'), '</legend>
				<field>
					<label for="Description">', _('Label Description'), ':</label>
					<input type="text" name="Description" size="21" required="required" maxlength="20" value="', $_POST['Description'], '" />
				</field>
				<field>
					<label for="PaperSize">', _('Label Paper Size'), ':</label>
					<select required="required" name="PaperSize" onchange="ReloadForm(submit)" >';

if (!isset($_POST['PaperSize'])) {
	echo '<option selected="selected" value="custom">', _('Custom Size'), '</option>';
} else {
	echo '<option value="custom">', _('Custom Size'), '</option>';
}
foreach ($PaperSize as $PaperType => $PaperSizeElement) {
	if (isset($_POST['PaperSize']) and $PaperType == $_POST['PaperSize']) {
		echo '<option selected="selected" value="', $PaperType, '">', $PaperType, '</option>';
	} else {
		echo '<option value="', $PaperType, '">', $PaperType, '</option>';
	}

} //end while loop
echo '</select>
	</field>';

if (!isset($_POST['PageHeight'])) {
	$_POST['PageHeight'] = 0;
}
if (!isset($_POST['PageWidth'])) {
	$_POST['PageWidth'] = 0;
}
if (!isset($_POST['Height'])) {
	$_POST['Height'] = 0;
}
if (!isset($_POST['TopMargin'])) {
	$_POST['TopMargin'] = 5;
}
if (!isset($_POST['Width'])) {
	$_POST['Width'] = 0;
}
if (!isset($_POST['LeftMargin'])) {
	$_POST['LeftMargin'] = 10;
}
if (!isset($_POST['RowHeight'])) {
	$_POST['RowHeight'] = 0;
}

if (!isset($_POST['ColumnWidth'])) {
	$_POST['ColumnWidth'] = 0;
}

if (!isset($_POST['PaperSize']) or $_POST['PaperSize'] == 'Custom') {
	if (!isset($_POST['PageWidth'])) {
		$_POST['PageWidth'] = 0;
		$_POST['PageHeight'] = 0;
	}
	echo '<field>
			<label for="PageWidth">', _('Page Width'), '</label>
			<input type="text" size="4" class="number" required="required" maxlength="4" name="PageWidth" value="', $_POST['PageWidth'], '" />
		</field>';

	echo '<field>
			<label for="PageHeight">', _('Page Height'), '</label>
			<input type="text" size="4" class="number" required="required" maxlength="4" name="PageHeight" value="', $_POST['PageHeight'], '" />
		</field>';
}
echo '<field>
		<label for="Height">', _('Label Height'), ' - (He):</label>
		<input type="text" name="Height" class="number" size="4" required="required" maxlength="4" value="', $_POST['Height'], '" />
	</field>';

echo '<field>
		<label for="Width">', _('Label Width'), ' - (Wi):</label>
		<input type="text" name="Width" size="4" class="number" required="required" maxlength="4" value="', $_POST['Width'], '" />
	</field>';

echo '<field>
		<label for="TopMargin">', _('Top Margin'), ' - (Tm):</label>
		<input type="text" name="TopMargin" class="number" size="4" required="required" maxlength="4" value="', $_POST['TopMargin'], '" />
	</field>';

echo '<field>
		<label for="LeftMargin">', _('Left Margin'), ' - (Lm):</label>
		<input type="text" name="LeftMargin" size="4" class="number" required="required" maxlength="4" value="', $_POST['LeftMargin'], '" />
	</field>';

echo '<field>
		<label for="RowHeight">', _('Row Height'), ' - (Rh):</label>
		<input type="text" name="RowHeight" size="4" class="number" required="required" maxlength="4" value="', $_POST['RowHeight'], '" />
	</field>';

echo '<field>
		<label for="ColumnWidth">', _('Column Width'), ' - (Cw):</label>
		<input type="text" name="ColumnWidth" size="4" class="number" required="required" maxlength="4" value="', $_POST['ColumnWidth'], '" />
	</field>';

echo '</fieldset>
	</fieldset>';

if (isset($SelectedLabelID)) {
	//get the fields to show
	$SQL = "SELECT labelfieldid,
					labelid,
					fieldvalue,
					vpos,
					hpos,
					fontsize,
					barcode
			FROM labelfields
			WHERE labelid = '" . $SelectedLabelID . "'
			ORDER BY vpos DESC";
	$ErrMsg = _('Could not get the label fields because');
	$Result = DB_query($SQL, $ErrMsg);
	$i = 0;
	echo '<table summary="', _('Outside container for label diagram and info'), '">
			<tr>
				<td>
					<img src="css/labelsDim.png" alt="Label dimensions diagram" />
				</td>
				<td>
					<table summary="', _('Label dimensions table'), '">
						<tr>
							<th>', _('Field'), '</th>
							<th>', _('Vertical'), '<br />', _('Position'), '<br />(VPos)</th>
							<th>', _('Horizontal'), '<br />', _('Position'), '<br />(HPos)</th>
							<th>', _('Font Size'), '</th>
							<th>', _('Bar-code'), '</th>
						</tr>';
	if (DB_num_rows($Result) > 0) {
		$k = 0;
		while ($MyRow = DB_fetch_array($Result)) {

			echo '<input type="hidden" name="LabelFieldID', $i, '" value="', $MyRow['labelfieldid'], '" />';

			echo '<tr class="striped_row">
					<td>
						<select name="FieldName', $i, '" onchange="ReloadForm(submit)">';
			if ($MyRow['fieldvalue'] == 'itemcode') {
				echo '<option selected="selected" value="itemcode">', _('Item Code'), '</option>';
			} else {
				echo '<option value="itemcode">', _('Item Code'), '</option>';
			}
			if ($MyRow['fieldvalue'] == 'itemdescription') {
				echo '<option selected="selected" value="itemdescription">', _('Item Description'), '</option>';
			} else {
				echo '<option value="itemdescription">', _('Item Descrption'), '</option>';
			}
			if ($MyRow['fieldvalue'] == 'barcode') {
				echo '<option selected="selected" value="barcode">', _('Item Barcode'), '</option>';
			} else {
				echo '<option value="barcode">', _('Item Barcode'), '</option>';
			}
			if ($MyRow['fieldvalue'] == 'price') {
				echo '<option selected="selected" value="price">', _('Price'), '</option>';
			} else {
				echo '<option value="price">', _('Price'), '</option>';
			}
			if ($MyRow['fieldvalue'] == 'logo') {
				echo '<option selected="selected" value="logo">', _('Company Logo'), '</option>';
			} else {
				echo '<option value="logo">', _('Company Logo'), '</option>';
			}
			echo '</select>
				</td>';

			echo '<td>
					<input type="text" name="VPos', $i, '" size="4" required="required" maxlength="4" value="', $MyRow['vpos'], '" />
				</td>
				<td>
					<input type="text" name="HPos', $i, '" size="4" required="required" maxlength="4" value="', $MyRow['hpos'], '" />
				</td>
				<td>
					<input type="text" name="FontSize', $i, '" size="4" required="required" maxlength="4" value="', $MyRow['fontsize'], '" />
				</td>
				<td>
					<select name="Barcode', $i, '" onchange="ReloadForm(submit)">';
			if ($MyRow['barcode'] == 0) {
				echo '<option selected="selected" value="0">', _('No'), '</option>';
				echo '<option value="1">', _('Yes'), '</option>';
			} else {
				echo '<option selected="selected" value="1">', _('Yes'), '</option>';
				echo '<option value="0">', _('No'), '</option>';
			}
			echo '</select>';

			echo '<td>
					<a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedLabelID=', urlencode($SelectedLabelID), '&amp;DeleteField=', urlencode($MyRow['labelfieldid']), ' onclick="return MakeConfirm(\'', _('Are you sure you wish to delete this label field?'), '\', \'Confirm Delete\', this);">', _('Delete'), '</a>
				</td>
			</tr>';
			++$i;
		}
		//END WHILE LIST LOOP
		$i--; //last increment needs to be wound back
		
	} //end if there are label definitions to show
	echo '<input type="hidden" name="NoOfFieldsDefined" value="' . $i . '" />';

	echo '<tr>
			<td>
				<select name="FieldName">
					<option value="itemcode">', _('Item Code'), '</option>
					<option value="itemdescription">', _('Item Descrption'), '</option>
					<option value="barcode">', _('Item Barcode'), '</option>
					<option value="price">', _('Price'), '</option>
					<option value="logo">', _('Company Logo'), '</option>
				</select>
			</td>';

	echo '<td>
			<input type="text" size="4" required="required" maxlength="4" name="VPos" />
		</td>';

	echo '<td>
			<input type="text" size="4" required="required" maxlength="4" name="HPos" />
		</td>';

	echo '<td>
			<input type="text" size="4" required="required" maxlength="4" name="FontSize" />
		</td>';

	echo '<td>
			<select name="Barcode">
				<option value="1">', _('Yes'), '</option>
				<option selected="selected" value="0">', _('No'), '</option>
			</select>
		</td>
	</tr>';

	echo '</table>
		</td>
	</tr>
</table>';
}

echo '<div class="centre">
			<input type="submit" name="submit" value="', _('Enter Information'), '" />
		</div>
		<div class="centre">
			<a href="', $RootPath, '/PDFPrintLabel.php">', _('Print Labels'), '</a>
		</div>
	</form>';

include ('includes/footer.php');

?>