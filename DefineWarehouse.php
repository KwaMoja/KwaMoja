<?php

include('includes/session.inc');

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
	include('includes/header.inc');
	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/supplier.png" title="' . _('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	$SQL = "SELECT loccode,
					locationname
				FROM locations";
	$Result = DB_query($SQL);
	echo '<table class="selection">
			<tr>
				<td><select name="Location">';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	}
	echo '</select></td>
		</tr>
	</table>';
	echo '<div class="centre">
			<input type="submit" name="Submit" value="Select" />
		</div>';

	echo '</form>';
	include('includes/footer.inc');
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
		for ($i = 1; $i <= $_POST['NoWide']; $i++) {
			for ($j = 1; $j <= $_POST['NoLong']; $j++) {
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
													'" . ($_POST['X'] +($_POST['Width']*$i)) . "',
													'" . ($_POST['Y'] +($_POST['Length']*$j)) . "',
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

include('includes/header.inc');
echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/supplier.png" title="' . _('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';
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
			$Style ='';
		}
		if ($ContainerRow['parentid'] == '') {
			echo '<tr class="visible" ' . $Style . '><td style="display:none">' . $ContainerRow['id'] . '</td>';
		} else {
			echo '<tr class="invisible" ' . $Style . '><td style="display:none">' . $ContainerRow['id'] . '</td>';
		}
        echo '<td>'.str_repeat('&nbsp;&nbsp;&nbsp;',$level).$ContainerRow['id'].'</td>
				<td>'.$ContainerRow['name'].'</td>
				<td>'.$ContainerRow['parentid'].'</td>
				<td class="number">'.$ContainerRow['sequence'].'</td>
				<td>'.$ContainerRow['putaway'].'</td>
				<td>'.$ContainerRow['picking'].'</td>
				<td>'.$ContainerRow['replenishment'].'</td>
				<td>'.$ContainerRow['quarantine'].'</td>
				<td class="number">' . $ContainerRow['xcoord'] . '</td>
				<td class="number">' . $ContainerRow['ycoord'] . '</td>
				<td class="number">' . $ContainerRow['zcoord'] . '</td>
				<td class="number">' . $ContainerRow['width'] . '</td>
				<td class="number">' . $ContainerRow['length'] . '</td>
				<td class="number">' . $ContainerRow['height'] . '</td>
				<td><a onclick="return true" href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Edit=' . $ContainerRow['id'] . '&Location=' . $LocationCode . '">' . _('Edit') . '</a></td>
			</tr>';
        // call this function again to display this
        // child's children
        display_children($ContainerRow['id'], $level+1, $LocationCode);
    }
}

echo '<table class="selection" id="Containers">
		<tr>
			<th>' . _('Container') . '</th>
			<th>' . _('Name') . '</th>
			<th>' . _('Parent') . '</th>
			<th>' . _('Sequence') . '</th>
			<th>' . _('Allow') . '</th>
			<th>' . _('Allow') . '</th>
			<th>' . _('Allow') . '</th>
			<th>' . _('Quarantine') . '</th>
			<th colspan="3">' . _('Position') . '</th>
			<th colspan="3">' . _('Dimensions') . '</th>
		</tr>
		<tr>
			<th colspan="2"></th>
			<th>' . _('Container') . '</th>
			<th></th>
			<th>' . _('Putaway') . '</th>
			<th>' . _('Picking') . '</th>
			<th>' . _('Replenishment') . '</th>
			<th>' . _('Area') . '</th>
			<th>X</th>
			<th>Y</th>
			<th>Z</th>
			<th>' . _('Width') . '</th>
			<th>' . _('Length') . '</th>
			<th>' . _('Height') . '</th>
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

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<input type="hidden" name="Location" value="' . $LocationCode . '" />';
echo '<table class="selection">';

if (isset($_GET['Edit'])) {
	echo '<tr>
				<td>' . _('Container ID') . '</td>
				<td colspan="6">' . $_POST['ID'] . '</td>
			</tr>';
	echo '<input type="hidden" name="ID" value="' . $_POST['ID'] . '" />';
} else {
	echo '<tr>
				<td>' . _('Container ID') . '</td>
				<td colspan="6"><input type="text" size="5" maxlength="6" name="ID" value="' . $_POST['ID'] . '" /></td>
			</tr>';
}
echo '<tr>
			<td>' . _('Description') . '</td>
			<td colspan="6"><input type="text" size="25" name="Description" value="' . $_POST['Description'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Parent Container') . '</td>
			<td colspan="6"><select name="Parent">';

$ParentSQL = "SELECT id,
					name
				FROM container
				WHERE location='" . $LocationCode . "'";
$ParentResult = DB_query($ParentSQL);
echo '<option value="">' . _('None') . '</option>';
while ($ParentRow = DB_fetch_array($ParentResult)) {
	if ($_POST['Parent'] == $ParentRow['id']) {
		echo '<option selected="selected" value="' . $ParentRow['id'] . '">' . $ParentRow['name'] . '</option>';
	} else {
		echo '<option value="' . $ParentRow['id'] . '">' . $ParentRow['name'] . '</option>';
	}
}
echo '</select>
		</td>
	</tr>';

echo '<tr>
		<td>' . _('Sequence') . '</td>
		<td colspan="6"><input type="text" size="5" class="integer" name="Sequence" value="' . $_POST['Sequence'] . '" /></td>
	</tr>';

if ($_POST['Putaway'] == 0) {
	$Selected = 'selected="selected"';
} else {
	$Selected = '';
}
echo '<tr>
		<td>' . _('Allow Putaway') . '</td>
		<td colspan="6">
			<select name="Putaway">
				<option value="1">' . _('Yes') . '</option>
				<option ' . $Selected . ' value="0">' . _('No') . '</option>
			</select>
		</td>
	</tr>';

if ($_POST['Picking'] == 0) {
	$Selected = 'selected="selected"';
} else {
	$Selected = '';
}
echo '<tr>
		<td>' . _('Allow Picking') . '</td>
		<td colspan="6">
			<select name="Picking">
				<option value="1">' . _('Yes') . '</option>
				<option ' . $Selected . ' value="0">' . _('No') . '</option>
			</select>
		</td>
	</tr>';

if ($_POST['Replenishment'] == 0) {
	$Selected = 'selected="selected"';
} else {
	$Selected = '';
}
echo '<tr>
		<td>' . _('Allow Replenishment') . '</td>
		<td colspan="6">
			<select name="Replenishment">
				<option value="1">' . _('Yes') . '</option>
				<option ' . $Selected . ' value="0">' . _('No') . '</option>
			</select>
		</td>
	</tr>';

if ($_POST['Quarantine'] == 0) {
	$Selected = 'selected="selected"';
} else {
	$Selected = '';
}
echo '<tr>
		<td>' . _('Quarantine Area') . '</td>
		<td colspan="6">
			<select name="Quarantine">
				<option value="1">' . _('Yes') . '</option>
				<option ' . $Selected . ' value="0">' . _('No') . '</option>
			</select>
		</td>
	</tr>';

echo '<tr>
		<td>' . _('Position in Parent Container') . ': </td>
		<td class="number">x : ' . '</td><td><input type="text" size="5" class="integer" name="X" value="' . $_POST['X'] . '" /></td>
		<td class="number">y : ' . '</td><td><input type="text" size="5" class="integer" name="Y" value="' . $_POST['Y'] . '" /></td>
		<td class="number">z : ' . '</td><td><input type="text" size="5" class="integer" name="Z" value="' . $_POST['Z'] . '" /></td>
	</tr>';

echo '<tr>
		<td>' . _('Size of Container') . ': </td>
		<td class="number">' . _('width') . ':</td><td><input type="text" size="5" class="integer" name="Width" value="' . $_POST['Width'] . '" /></td>
		<td class="number">' . _('length') . ':</td><td><input type="text" size="5" class="integer" name="Length" value="' . $_POST['Length'] . '" /></td>
		<td class="number">' . _('height') . ':</td><td><input type="text" size="5" class="integer" name="Height" value="' . $_POST['Height'] . '" /></td>
	</tr>';

if (!isset($_GET['Edit'])) {
	if (!isset($_POST['NoWide'])) {
		$_POST['NoWide'] = 1;
		$_POST['NoLong'] = 1;
	}
	echo '<tr>
			<td>' . _('Create a Block of Containers') . ':</td>
			<td><input type="text" size="5" class="integer" name="NoWide" value="' . $_POST['NoWide'] . '" />&nbsp;X</td>
			<td><input type="text" size="5" class="integer" name="NoLong" value="' . $_POST['NoLong'] . '" /></td>
		</tr>';
}

echo '</table>';

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

include('includes/footer.inc');

?>