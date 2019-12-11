<?php
include ('includes/session.php');

if (!isset($_POST['Parent'])) {
	$_POST['Parent'] = '';
}

if (isset($_GET['Location'])) {
	/* If the location code is sent as part of the
	 * $_GET array
	*/
	$LocationCode = $_GET['Location'];
} else if (isset($_POST['Location'])) {
	/* If the location code is sent as part of the
	 * $_POST array
	*/
	$LocationCode = $_POST['Location'];
} else {
	/* If no stock location has been chosen then
	 * show a selection form for the user to choose one
	*/
	$Title = _('Select Warehouse to Define');
	include ('includes/header.php');
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/supplier.png" title="', _('Warehouse'), '" alt="" />', $Title, '
		</p>';

	echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	$SQL = "SELECT loccode,
					locationname
				FROM locations";
	$Result = DB_query($SQL);
	echo '<fieldset>
			<legend>', _('Select Warehouse to Define'), '</legend
			<field>
				<label for="Location">', _('Location of warehouse'), '</label>
				<select name="Location" autofocus="autofocus">';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="', $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';
	}
	echo '</select>
		<fieldhelp>', _('Select the location of the warehouse to be defined.'), '</fieldhelp>
	</field>
</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="Submit" value="Select" />
		</div>';

	echo '</form>';
	include ('includes/footer.php');
	exit;
}

if (isset($_POST['Insert']) or isset($_POST['Update'])) {
	$Errors = 0;
	if (mb_strlen($_POST['ID']) == 0) {
		prnMsg(_('The container identifier code must contain at least one character'), 'error');
		$Errors++;
	}
	if (mb_strlen($_POST['ID']) > 10) {
		prnMsg(_('The container identifier code must be ten charcters or less'), 'error');
		$Errors++;
	}
	if (mb_strlen($_POST['Description']) == 0) {
		prnMsg(_('The container description must contain at least one character'), 'error');
		$Errors++;
	}
	if (mb_strlen($_POST['ID']) > 10) {
		prnMsg(_('The container description must be fifty charcters or less'), 'error');
		$Errors++;
	}
	$LocationSQL = "SELECT loccode FROM locations WHERE loccode='" . $LocationCode . "'";
	$LocationResult = DB_query($LocationSQL);
	if (DB_num_rows($LocationResult) == 0) {
		prnMsg(_('You have not chosen a valid location code'), 'error');
		$Errors++;
	}

	$ParentSQL = "SELECT id FROM container WHERE id='" . $_POST['Parent'] . "'";
	$ParentResult = DB_query($ParentSQL);
	if (DB_num_rows($ParentResult) == 0 and $_POST['Parent'] != '') {
		prnMsg(_('You have not chosen a valid parent container'), 'error');
		$Errors++;
	}

	if (!is_numeric($_POST['X']) or !is_numeric($_POST['Y']) or !is_numeric($_POST['Z'])) {
		prnMsg(_('The positional co-ordinates of the container must be numbers'), 'error');
		$Errors++;
	}

	if (!is_numeric($_POST['Width']) or !is_numeric($_POST['Length']) or !is_numeric($_POST['Height'])) {
		prnMsg(_('The dimensions of the container must be numbers'), 'error');
		$Errors++;
	}

	if ($Errors == 0 and isset($_POST['Insert'])) {
		$k = 0;
		for ($i = 1;$i <= $_POST['NoWide'];$i++) {
			for ($j = 1;$j <= $_POST['NoLong'];$j++) {
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
													replenishment,
													quarantine
												) VALUES (
													'" . $_POST['ID'] . ($k) . "',
													'" . $_POST['Description'] . $i . 'x' . $j . "',
													'" . $LocationCode . "',
													'" . $_POST['Parent'] . "',
													'" . ($_POST['X'] + ($_POST['Width'] * $i)) . "',
													'" . ($_POST['Y'] + ($_POST['Length'] * $j)) . "',
													'" . $_POST['Z'] . "',
													'" . $_POST['Width'] . "',
													'" . $_POST['Length'] . "',
													'" . $_POST['Height'] . "',
													'" . ($_POST['Sequence'] + $k) . "',
													'" . $_POST['Putaway'] . "',
													'" . $_POST['Picking'] . "',
													'" . $_POST['Replenishment'] . "',
													'" . $_POST['Quarantine'] . "'
												)";

				$ErrMsg = _('An error occurred inserting the container detaails');
				$DbgMsg = _('The SQL used to insert the container record was');
				$Result = DB_query($InsertSQL, $ErrMsg, $DbgMsg);
				if (DB_error_no() == 0) {
					prnMsg(_('The container') . ' ' . $_POST['Description'] . $i . 'x' . $j . ' ' . _('has been successfully created in') . ' ' . $_POST['Parent'], 'success');
				}
				++$k;
			}
		}
	}

	if ($Errors == 0 and isset($_POST['Update'])) {
		$UpdateSQL = "UPDATE container set  name='" . $_POST['Description'] . "',
											location='" . $LocationCode . "',
											parentid='" . $_POST['Parent'] . "',
											xcoord='" . $_POST['X'] . "',
											ycoord='" . $_POST['Y'] . "',
											zcoord='" . $_POST['Z'] . "',
											width='" . $_POST['Width'] . "',
											length='" . $_POST['Length'] . "',
											height='" . $_POST['Height'] . "',
											sequence='" . $_POST['Sequence'] . "',
											putaway='" . $_POST['Putaway'] . "',
											picking='" . $_POST['Picking'] . "',
											replenishment='" . $_POST['Replenishment'] . "',
											quarantine='" . $_POST['Quarantine'] . "'
										WHERE id='" . $_POST['ID'] . "'";

		$ErrMsg = _('An error occurred updating the container detaails');
		$DbgMsg = _('The SQL used to update the container record was');
		$Result = DB_query($UpdateSQL, $ErrMsg, $DbgMsg);
	}
}

/* Get the location name */
$SQL = "SELECT locationname FROM locations WHERE loccode='" . $LocationCode . "'";
$Result = DB_query($SQL);
$LocationRow = DB_fetch_array($Result);

$Title = _('Define Warehouse at') . ' ' . $LocationRow['locationname'];

include ('includes/header.php');
echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/supplier.png" title="', _('Inventory'), '" alt="" />', $Title, '
	</p>';

if (!isset($_GET['Edit'])) {
	function display_children($parent, $level, $LocationCode) {
		// retrieve all children of $parent
		$ContainerSQL = "SELECT id,
							name,
							parentid,
							sequence,
							putaway,
							picking,
							replenishment,
							quarantine,
							xcoord,
							ycoord,
							zcoord,
							width,
							length,
							height
						FROM container
						WHERE location='" . $LocationCode . "'
							AND parentid='" . $parent . "'
						ORDER BY parentid, sequence";
		$ContainerResult = DB_query($ContainerSQL);

		// display each child
		while ($ContainerRow = DB_fetch_array($ContainerResult)) {
			// indent and display the title of this child
			if ($ContainerRow['putaway'] == 1) {
				$ContainerRow['putaway'] = _('Yes');
			} else {
				$ContainerRow['putaway'] = _('No');
			}
			if ($ContainerRow['picking'] == 1) {
				$ContainerRow['picking'] = _('Yes');
			} else {
				$ContainerRow['picking'] = _('No');
			}
			if ($ContainerRow['replenishment'] == 1) {
				$ContainerRow['replenishment'] = _('Yes');
			} else {
				$ContainerRow['replenishment'] = _('No');
			}
			if ($ContainerRow['quarantine'] == 1) {
				$ContainerRow['quarantine'] = _('Yes');
			} else {
				$ContainerRow['quarantine'] = _('No');
			}
			$ChildrenSQL = "SELECT COUNT(id) as children
								FROM container
								WHERE parentid='" . $ContainerRow['id'] . "'";
			$ChildrenResult = DB_query($ChildrenSQL);
			$ChildrenRow = DB_fetch_array($ChildrenResult);
			$NumberOfChildren = $ChildrenRow['children'];
			if ($NumberOfChildren > 0) {
				$Style = ' onClick="expandTable(this)" style="cursor:pointer" ';
			} else {
				$Style = '';
			}
			if ($ContainerRow['parentid'] == '') {
				echo '<tr class="visible striped_row" ', $Style, ' data-title="Click here to view the sub-containers"><td style="display:none">', $ContainerRow['id'], '</td>';
			} else {
				echo '<tr class="invisible" ', $Style, '><td style="display:none">', $ContainerRow['id'], '</td>';
			}
			echo '<td>', str_repeat('&nbsp;&nbsp;&nbsp;', $level), $ContainerRow['id'], '</td>
				<td>', $ContainerRow['name'], '</td>
				<td>', $ContainerRow['parentid'], '</td>
				<td class="number">', $ContainerRow['sequence'], '</td>
				<td>', $ContainerRow['putaway'], '</td>
				<td>', $ContainerRow['picking'], '</td>
				<td>', $ContainerRow['replenishment'], '</td>
				<td>', $ContainerRow['quarantine'], '</td>
				<td class="number">', $ContainerRow['xcoord'], '</td>
				<td class="number">', $ContainerRow['ycoord'], '</td>
				<td class="number">', $ContainerRow['zcoord'], '</td>
				<td class="number">', $ContainerRow['width'], '</td>
				<td class="number">', $ContainerRow['length'], '</td>
				<td class="number">', $ContainerRow['height'], '</td>
				<td><a onclick="return true" href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?Edit=', $ContainerRow['id'], '&Location=', $LocationCode, '">', _('Edit'), '</a></td>
			</tr>';
			// call this function again to display this
			// child's children
			display_children($ContainerRow['id'], $level + 1, $LocationCode);
		}
	}

	echo '<table id="Containers">
		<tr>
			<th rowspan="2">', _('Container'), '</th>
			<th rowspan="2">', _('Container Name'), '</th>
			<th rowspan="2">', _('Parent'), '</th>
			<th rowspan="2">', _('Sequence'), '</th>
			<th rowspan="2">', _('Allow Putaway'), '</th>
			<th rowspan="2">', _('Allow Picking'), '</th>
			<th rowspan="2">', _('Allow Replenishment'), '</th>
			<th rowspan="2">', _('Quarantine Area'), '</th>
			<th colspan="3">', _('Position'), '</th>
			<th colspan="3">', _('Dimensions'), '</th>
		</tr>
		<tr>
			<th>X</th>
			<th>Y</th>
			<th>Z</th>
			<th>', _('Width'), '</th>
			<th>', _('Length'), '</th>
			<th>', _('Height'), '</th>
		</tr>';

	display_children('', 0, $LocationCode);
	echo '</table>';
}
if (isset($_GET['Edit'])) {
	$SQL = "SELECT id,
					name,
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
					replenishment,
					quarantine
				FROM container
				WHERE id='" . $_GET['Edit'] . "'";
	$Result = DB_query($SQL);
}

if (DB_num_rows($Result) != 0) {
	$MyRow = DB_fetch_array($Result);
	$_POST['ID'] = $MyRow['id'];
	$_POST['Description'] = $MyRow['name'];
	$_POST['Parent'] = $MyRow['parentid'];
	$_POST['X'] = $MyRow['xcoord'];
	$_POST['Y'] = $MyRow['ycoord'];
	$_POST['Z'] = $MyRow['zcoord'];
	$_POST['Width'] = $MyRow['width'];
	$_POST['Length'] = $MyRow['length'];
	$_POST['Height'] = $MyRow['height'];
	$_POST['Sequence'] = $MyRow['sequence'];
	$_POST['Putaway'] = $MyRow['putaway'];
	$_POST['Picking'] = $MyRow['picking'];
	$_POST['Replenishment'] = $MyRow['replenishment'];
	$_POST['Quarantine'] = $MyRow['quarantine'];
} else {
	$_POST['ID'] = '';
	$_POST['Description'] = '';
	$_POST['Parent'] = '';
	$_POST['X'] = 0;
	$_POST['Y'] = 0;
	$_POST['Z'] = 0;
	$_POST['Width'] = 0;
	$_POST['Length'] = 0;
	$_POST['Height'] = 0;
	$_POST['Sequence'] = 0;
	$_POST['Putaway'] = 1;
	$_POST['Picking'] = 1;
	$_POST['Replenishment'] = 1;
	$_POST['Quarantine'] = 0;
}

echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
echo '<input type="hidden" name="Location" value="', $LocationCode, '" />';

if (isset($_GET['Edit'])) {
	echo '<fieldset>
			<legend>', _('Edit container details'), '</legend>
			<field>
				<label for="ID">', _('Container ID'), '</label>
				<div class="fieldtext">', $_POST['ID'], '</div>
			</field>';
	echo '<input type="hidden" name="ID" value="', $_POST['ID'], '" />';
} else {
	echo '<fieldset>
			<legend>', _('Create container details'), '</legend>
			<field>
				<label for="ID">', _('Container ID'), '</label>
				<input type="text" autofocus="autofocus" size="5" maxlength="6" name="ID" value="', $_POST['ID'], '" />
				<fieldhelp>', _('Enter an Id by which this container will be referred to. The ID can have up to 6 characters.'), '</fieldhelp>
			</field>';
}
echo '<field>
		<label for="Description">', _('Description'), '</label>
		<input type="text" size="25" name="Description" value="', $_POST['Description'], '" />
		<fieldhelp>', _('Enter a description of this container. The description can have up to 50 characters.'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="Parent">', _('Parent Container'), '</label>
		<select name="Parent">';

$ParentSQL = "SELECT id,
					name
				FROM container
				WHERE location='" . $LocationCode . "'";
$ParentResult = DB_query($ParentSQL);
echo '<option value="">', _('None'), '</option>';
while ($ParentRow = DB_fetch_array($ParentResult)) {
	if ($_POST['Parent'] == $ParentRow['id']) {
		echo '<option selected="selected" value="', $ParentRow['id'], '">', $ParentRow['name'], '</option>';
	} else {
		echo '<option value="', $ParentRow['id'], '">', $ParentRow['name'], '</option>';
	}
}
echo '</select>
	<fieldhelp>', _('Select the parent container (if any) that this container belongs to.'), '</fieldhelp>
</field>';

echo '<field>
		<label for="Sequence">', _('Sequence'), '</label>
		<input type="text" size="5" class="number" name="Sequence" value="', $_POST['Sequence'], '" />
		<fieldhelp>', _('Enter the sequence number for this container.'), '</fieldhelp>
	</field>';

if ($_POST['Putaway'] == 0) {
	$Selected = 'selected="selected"';
} else {
	$Selected = '';
}
echo '<field>
		<label for="Putaway">', _('Allow Putaway'), '</label>
		<select name="Putaway">
			<option value="1">', _('Yes'), '</option>
			<option ', $Selected, ' value="0">', _('No'), '</option>
		</select>
		<fieldhelp>', _('Select "Yes" if this container can be used for directed put aways. Otherwise select "No"'), '</fieldhelp>
	</field>';

if ($_POST['Picking'] == 0) {
	$Selected = 'selected="selected"';
} else {
	$Selected = '';
}
echo '<field>
		<label for="Picking">', _('Allow Picking'), '</label>
		<select name="Picking">
			<option value="1">', _('Yes'), '</option>
			<option ', $Selected, ' value="0">', _('No'), '</option>
		</select>
		<fieldhelp>', _('Select "Yes" if this container can be used for directed picking. Otherwise select "No"'), '</fieldhelp>
	</field>';

if ($_POST['Replenishment'] == 0) {
	$Selected = 'selected="selected"';
} else {
	$Selected = '';
}
echo '<field>
		<label for="Replenishment">', _('Allow Replenishment'), '</label>
		<select name="Replenishment">
			<option value="1">', _('Yes'), '</option>
			<option ', $Selected, ' value="0">', _('No'), '</option>
		</select>
		<fieldhelp>', _('Select "Yes" if this container can be replenished. Otherwise select "No"'), '</fieldhelp>
	</field>';

if ($_POST['Quarantine'] == 0) {
	$Selected = 'selected="selected"';
} else {
	$Selected = '';
}
echo '<field>
		<label for="">', _('Quarantine Area'), '</label>
		<select name="Quarantine">
			<option value="1">', _('Yes'), '</option>
			<option ', $Selected, ' value="0">', _('No'), '</option>
		</select>
		<fieldhelp>', _('Select "Yes" if this container is designated as a quarantine rea. Otherwise select "No"'), '</fieldhelp>
	</field>';

echo '<fieldset>
		<legend>', _('Position in Parent Container') . ':</legend>
		<field>
			<label for="X">x : ', '</label>
			<input type="text" size="5" class="number" name="X" value="', $_POST['X'], '" />
			<fieldhelp>', _('The x co-ordinate of the location within the parent container'), '</fieldhelp>
		</field>
		<field>
			<label for="Y">y : ' . '</label>
			<input type="text" size="5" class="number" name="Y" value="', $_POST['Y'], '" />
			<fieldhelp>', _('The y co-ordinate of the location within the parent container'), '</fieldhelp>
		</field>
		<field>
			<label for="Z">z : ' . '</label>
			<input type="text" size="5" class="number" name="Z" value="', $_POST['Z'], '" />
			<fieldhelp>', _('The z co-ordinate of the location within the parent container'), '</fieldhelp>
		</field>
	</fieldset><br />';

echo '<fieldset>
		<legend>', _('Size of Container'), ': </legend>
		<field>
			<label for="Width">', _('width'), ':</label>
			<input type="text" size="5" class="number" name="Width" value="', $_POST['Width'], '" />
			<fieldhelp>', _('The width of this container.'), '</fieldhelp>
		</field>
		<field>
			<label for="Length">', _('length'), ':</label>
			<input type="text" size="5" class="number" name="Length" value="', $_POST['Length'], '" />
			<fieldhelp>', _('The length of this container.'), '</fieldhelp>
		</field>
		<field>
			<label for="Height">', _('height'), ':</label>
			<input type="text" size="5" class="number" name="Height" value="', $_POST['Height'], '" />
			<fieldhelp>', _('The height of this container.'), '</fieldhelp>
		</field>
	</fieldset><br />';

if (!isset($_GET['Edit'])) {
	if (!isset($_POST['NoWide'])) {
		$_POST['NoWide'] = 1;
		$_POST['NoLong'] = 1;
	}
	echo '<field>
			<label>', _('Create a Block of Containers'), ':</label>
			<input type="text" size="5" class="number" name="NoWide" value="' . $_POST['NoWide'] . '" />&nbsp;X
			<input type="text" size="5" class="number" name="NoLong" value="' . $_POST['NoLong'] . '" />
		</field>';
}

echo '</fieldset>';

if (!isset($_GET['Edit'])) {
	echo '<div class="centre">
			<input type="submit" name="Insert" value="' . _('Define Container') . '" />
		</div>';
} else {
	echo '<div class="centre">
			<input type="submit" name="Update" value="' . _('Update Container Definition') . '" />
		</div>';
}

echo '</form>';

include ('includes/footer.php');

?>