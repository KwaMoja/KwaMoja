<?php
/* $Id: Manufacturers.php 5498 2012-07-13 08:35:54Z tehonu $*/

include ('includes/session.php');

$Title = _('Brands Maintenance');

include ('includes/header.php');

if (isset($_GET['SelectedManufacturer'])) {
	$SelectedManufacturer = $_GET['SelectedManufacturer'];
} elseif (isset($_POST['SelectedManufacturer'])) {
	$SelectedManufacturer = $_POST['SelectedManufacturer'];
}

$SupportedImgExt = array('png', 'jpg', 'jpeg');

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	 ie the page has called itself with some user input */

	if (isset($SelectedManufacturer) and $InputError != 1) {

		if (isset($_FILES['BrandPicture']) and $_FILES['BrandPicture']['name'] != '') {

			$Result = $_FILES['BrandPicture']['error'];
			$UploadTheFile = 'Yes'; //Assume all is well to start off with
			$ImgExt = pathinfo($_FILES['BrandPicture']['name'], PATHINFO_EXTENSION);
			$FileName = $_SESSION['part_pics_dir'] . '/BRAND-' . $SelectedManufacturer . '.' . $ImgExt;

			//But check for the worst
			if (!in_array($ImgExt, $SupportedImgExt)) {
				prnMsg(_('Only ' . implode(", ", $SupportedImgExt) . ' files are supported - a file extension of ' . implode(", ", $SupportedImgExt) . ' is expected'), 'warn');
				$UploadTheFile = 'No';
			} elseif ($_FILES['BrandPicture']['size'] > ($_SESSION['MaxImageSize'] * 1024)) { //File Size Check
				prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'], 'warn');
				$UploadTheFile = 'No';
			} elseif ($_FILES['BrandPicture']['type'] == 'text/plain') { //File Type Check
				prnMsg(_('Only graphics files can be uploaded'), 'warn');
				$UploadTheFile = 'No';
			}
			foreach ($SupportedImgExt as $ext) {
				$File = $_SESSION['part_pics_dir'] . '/BRAND-' . $SelectedManufacturer . '.' . $ext;
				if (file_exists($File)) {
					$Result = unlink($File);
					if (!$Result) {
						prnMsg(_('The existing image could not be removed'), 'error');
						$UploadTheFile = 'No';
					}
				}
			}

			if ($UploadTheFile == 'Yes') {
				$Result = move_uploaded_file($_FILES['BrandPicture']['tmp_name'], $FileName);
				$message = ($Result) ? _('File url') . '<a href="' . $FileName . '">' . $FileName . '</a>' : _('Something is wrong with uploading a file');
				$_POST['ManufacturersImage'] = 'BRAND-' . $SelectedManufacturer;
			} else {
				$_POST['ManufacturersImage'] = '';
			}
		}
		if (isset($_POST['ManufacturersImage'])) {
			foreach ($SupportedImgExt as $ext) {
				$File = $_SESSION['part_pics_dir'] . '/BRAND-' . $SelectedManufacturer . '.' . $ext;
				if (file_exists($File)) {
					$_POST['ManufacturersImage'] = 'BRAND-' . $SelectedManufacturer;
					break;
				} else {
					$_POST['ManufacturersImage'] = '';
				}
			}
		}
		if (isset($_POST['ClearImage'])) {
			foreach ($SupportedImgExt as $ext) {
				$file = $_SESSION['part_pics_dir'] . '/BRAND-' . $SelectedManufacturer . '.' . $ext;
				if (file_exists($file)) {
					@unlink($file);
					$_POST['ManufacturersImage'] = '';
					if (is_file($ImageFile)) {
						prnMsg(_('You do not have access to delete this item image file.'), 'error');
					}
				}
			}
		}

		$SQL = "UPDATE manufacturers SET manufacturers_name='" . $_POST['ManufacturersName'] . "',
									manufacturers_url='" . $_POST['ManufacturersURL'] . "'";
		if (isset($_POST['ManufacturersImage'])) {
			$SQL.= ", manufacturers_image='" . $_POST['ManufacturersImage'] . "'";
		}
		$SQL.= " WHERE manufacturers_id = '" . $SelectedManufacturer . "'";

		$ErrMsg = _('An error occurred updating the') . ' ' . $SelectedManufacturer . ' ' . _('manufacturer record because');
		$DbgMsg = _('The SQL used to update the manufacturer record was');

		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

		prnMsg(_('The manufacturer record has been updated'), 'success');
		unset($_POST['ManufacturersName']);
		unset($_POST['ManufacturersURL']);
		unset($_POST['ManufacturersImage']);
		unset($SelectedManufacturer);

	} elseif ($InputError != 1) {

		/*SelectedManufacturer is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new Location form */

		$SQL = "INSERT INTO manufacturers (manufacturers_name,
										manufacturers_url)
						VALUES ('" . $_POST['ManufacturersName'] . "',
								'" . $_POST['ManufacturersURL'] . "')";

		$ErrMsg = _('An error occurred inserting the new manufacturer record because');
		$DbgMsg = _('The SQL used to insert the manufacturer record was');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

		if (isset($_FILES['BrandPicture']) and $_FILES['BrandPicture']['name'] != '') {

			$Result = $_FILES['BrandPicture']['error'];
			$UploadTheFile = 'Yes'; //Assume all is well to start off with
			$ImgExt = pathinfo($_FILES['BrandPicture']['name'], PATHINFO_EXTENSION);
			$FileName = $_SESSION['part_pics_dir'] . '/BRAND-' . $_SESSION['LastInsertId'] . '.' . $ImgExt;

			//But check for the worst
			if (!in_array($ImgExt, $SupportedImgExt)) {
				prnMsg(_('Only ' . implode(", ", $SupportedImgExt) . ' files are supported - a file extension of ' . implode(", ", $SupportedImgExt) . ' is expected'), 'warn');
				$UploadTheFile = 'No';
			} elseif ($_FILES['BrandPicture']['size'] > ($_SESSION['MaxImageSize'] * 1024)) { //File Size Check
				prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'], 'warn');
				$UploadTheFile = 'No';
			} elseif ($_FILES['BrandPicture']['type'] == 'text/plain') { //File Type Check
				prnMsg(_('Only graphics files can be uploaded'), 'warn');
				$UploadTheFile = 'No';
			}
			foreach ($SupportedImgExt as $ext) {
				$File = $_SESSION['part_pics_dir'] . '/BRAND-' . $_SESSION['LastInsertId'] . '.' . $ext;
				if (file_exists($File)) {
					$Result = unlink($File);
					if (!$Result) {
						prnMsg(_('The existing image could not be removed'), 'error');
						$UploadTheFile = 'No';
					}
				}
			}

			if ($UploadTheFile == 'Yes') {
				$Result = move_uploaded_file($_FILES['BrandPicture']['tmp_name'], $FileName);
				$message = ($Result) ? _('File url') . '<a href="' . $FileName . '">' . $FileName . '</a>' : _('Something is wrong with uploading a file');
				DB_query("UPDATE manufacturers
					SET  manufacturers_image='" . 'BRAND-' . $_SESSION['LastInsertId'] . "'
					WHERE manufacturers_id = '" . $_SESSION['LastInsertId'] . "'
					");
			}
		}

		prnMsg(_('The new manufacturer record has been added'), 'success');

		unset($_POST['ManufacturersName']);
		unset($_POST['ManufacturersURL']);
		unset($_POST['ManufacturersImage']);
		unset($SelectedManufacturer);
	}

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button
	$CancelDelete = false;

	// PREVENT DELETES IF DEPENDENT RECORDS
	$SQL = "SELECT COUNT(*) FROM salescatprod WHERE manufacturers_id='" . $SelectedManufacturer . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		$CancelDelete = true;
		prnMsg(_('Cannot delete this manufacturer because products have been defined as from this manufacturer'), 'warn');
		echo _('There are') . ' ' . $MyRow[0] . ' ' . _('items with this manufacturer code');
	}

	if (!$CancelDelete) {

		$Result = DB_query("DELETE FROM manufacturers WHERE manufacturers_id='" . $SelectedManufacturer . "'");
		foreach ($SupportedImgExt as $ext) {
			$File = $_SESSION['part_pics_dir'] . '/BRAND-' . $SelectedManufacturer . '.' . $ext;
			if (file_exists($File)) {
				@unlink($File);
			}
		}
		prnMsg(_('Manufacturer') . ' ' . $SelectedManufacturer . ' ' . _('has been deleted') . '!', 'success');
		unset($SelectedManufacturer);
	} //end if Delete Manufacturer
	unset($SelectedManufacturer);
	unset($_GET['delete']);
}

if (!isset($SelectedManufacturer)) {

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedManufacturer will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of Manufacturers will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/

	$SQL = "SELECT manufacturers_id,
				manufacturers_name,
				manufacturers_url,
				manufacturers_image
			FROM manufacturers";
	$Result = DB_query($SQL);

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/supplier.png" Title="', _('Manufacturers'), '" alt="" />', ' ', $Title, '
		</p>';

	if (DB_num_rows($Result) != 0) {

		echo '<table>
				<tr>
					<th>', _('Brand Code'), '</th>
					<th>', _('Brand Name'), '</th>
					<th>', _('Brand URL'), '</th>
					<th>', _('Brands Image'), '</th>
					<th colspan="2"></th>
				</tr>';

		while ($MyRow = DB_fetch_array($Result)) {

			$ImageFileArray = glob($_SESSION['part_pics_dir'] . '/BRAND-' . $MyRow['manufacturers_id'] . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE);
			$ImageFile = reset($ImageFileArray);
			if (extension_loaded('gd') and function_exists('gd_info') and file_exists($ImageFile)) {
				$BrandImgLink = '<img class="StockImage" src="GetStockImage.php?automake=1&textcolor=FFFFFF&bgcolor=CCCCCC&StockID=' . urlencode('/BRAND-' . $MyRow['manufacturers_id']) . '" alt="" />';
			} else if (file_exists($ImageFile)) {
				$BrandImgLink = '<img class="StockImage" src="' . $ImageFile . '" />';
			} else {
				$BrandImgLink = _('No Image');
			}
			echo '<tr class="striped_row">
					<td>', $MyRow['manufacturers_id'], '</td>
					<td>', $MyRow['manufacturers_name'], '</td>
					<td><a target="_blank" href="', $MyRow['manufacturers_url'], '">', $MyRow['manufacturers_url'], '</a></td>
					<td>', $BrandImgLink, '</td>
					<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedManufacturer=', urlencode($MyRow['manufacturers_id']), '&edit=1">', _('Edit'), '</a></td>
					<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedManufacturer=', urlencode($MyRow['manufacturers_id']), '&delete=1" onclick="return MakeConfirm(\'', _('Are you sure you wish to delete this brand?'), '\', \'Confirm Delete\', this);">', _('Delete'), '</a></td>
				</tr>';

		}
		//END WHILE LIST LOOP
		echo '</table>';
	}
}

//end of ifs and buts!
if (isset($SelectedManufacturer)) {
	echo '<a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">', _('Review Records'), '</a>';
}

if (!isset($_GET['delete'])) {

	echo '<form enctype="multipart/form-data" method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	if (isset($SelectedManufacturer)) {
		//editing an existing Brand
		echo '<p class="page_title_text">
				<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/supplier.png" title="', _('Brand'), '" alt="" />', ' ', $Title, '
			</p>';

		$SQL = "SELECT manufacturers_id,
					manufacturers_name,
					manufacturers_url,
					manufacturers_image
				FROM manufacturers
				WHERE manufacturers_id='" . $SelectedManufacturer . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['ManufacturersName'] = $MyRow['manufacturers_name'];
		$_POST['ManufacturersURL'] = $MyRow['manufacturers_url'];
		$_POST['ManufacturersImage'] = $MyRow['manufacturers_image'];

		echo '<input type="hidden" name="SelectedManufacturer" value="', $SelectedManufacturer, '" />';
		echo '<fieldset>
				<legend>', _('Amend Brand Details'), '</legend>';

	} else { //end of if $SelectedManufacturer only do the else when a new record is being entered
		echo '<fieldset>
				<legend>', _('New Brand/Manufacturer Details'), '</legend>';
	}
	if (!isset($_POST['ManufacturersName'])) {
		$_POST['ManufacturersName'] = '';
	}
	if (!isset($_POST['ManufacturersURL'])) {
		$_POST['ManufacturersURL'] = '';
	}
	if (!isset($_POST['ManufacturersImage'])) {
		$_POST['ManufacturersImage'] = '';
	}

	echo '<field>
			<label for="ManufacturersName">', _('Brand Name'), ':</label>
			<input type="text" name="ManufacturersName" value="', $_POST['ManufacturersName'], '" size="32" autofocus="autofocus" required="required" maxlength="32" />
			<fieldhelp>', _('The name of this manufacturer.'), '</fieldhelp>
		</field>
		<field>
			<label for="ManufacturersURL">', _('Brand URL'), ':</label>
			<input type="text" name="ManufacturersURL" value="', $_POST['ManufacturersURL'], '" size="50" required="required" maxlength="50" />
			<fieldhelp>', _('URL for this companies web site'), '</fieldhelp>
		</field>
		<field>
			<label for="BrandPicture">', _('Brand Image File (' . implode(", ", $SupportedImgExt) . ')'), ':</label>
			<input type="file" id="BrandPicture" name="BrandPicture" />';

	if (isset($_GET['edit'])) {
		echo '<input type="checkbox" name="ClearImage" id="ClearImage" value="1" > ', _('Clear Image'), ' ';
	}

	echo '<fieldhelp>', _('An image file or logo for this company.'), '</fieldhelp>
		</field>';
	if (isset($SelectedManufacturer)) {
		$ImageFileArray = glob($_SESSION['part_pics_dir'] . '/BRAND-' . $SelectedManufacturer . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE);
		$ImageFile = reset($ImageFileArray);
		if (extension_loaded('xgd') and function_exists('gd_info') and file_exists($ImageFile)) {
			$BrandImgLink = '<img class="StockImage" src="GetStockImage.php?automake=1&textcolor=FFFFFF&bgcolor=CCCCCC&StockID=' . urlencode('/BRAND-' . $SelectedManufacturer) . '" alt="" />';
		} else {
			if (isset($SelectedManufacturer) and !empty($SelectedManufacturer) and file_exists($ImageFile)) {
				$BrandImgLink = '<img class="StockImage" src="' . $ImageFile . '" />';
			} else {
				$BrandImgLink = _('No Image');
			}
		}
		echo '<field>
				<label>&nbsp;</label>
					', $BrandImgLink, '
			</field>';
	}

	echo '</fieldset>
			<div class="centre">
				<input type="submit" name="submit" value="', _('Enter Information'), '" />
			</div>
		</form>';

} //end if record deleted no point displaying form to add record
include ('includes/footer.php');
?>