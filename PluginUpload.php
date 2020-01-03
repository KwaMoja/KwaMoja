<?php
include ('includes/session.php');

$Title = _('Upload a Plugin');

include ('includes/header.php');

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/ascending.png" title="', _('Upload Plugin'), '" alt="" />', _('Upload Plugin'), '
	</p>';

if (isset($_POST['submit'])) {
	switch ($_FILES['pluginfile']['error']) {
		case UPLOAD_ERR_OK:
			if (IsPlugin($_FILES['pluginfile'])) {
				if (!is_dir('/plugins')) {
					if (is_writable('../')) {
						mkdir('plugins', 0777);
					} else {
						prnMsg(_('The plugins directory cannot be created. Please make your root directory writeable for the web server'), 'error');
						unset($_POST['submit']);
						break;
					}
				}
				move_uploaded_file($_FILES['pluginfile']['tmp_name'], 'plugins/' . $_FILES['pluginfile']['name']);
				prnMsg(_('The plugin has been successfully uploaded to your plugins directory. Now you can install it.'), 'success');
				include ('includes/footer.php');
				exit;
			}
		break;
		case UPLOAD_ERR_INI_SIZE:
			prnMsg(_('The file you have selected is too big. It exceeds the maximum size allowed by your PHP installation.'), 'error');
			unset($_POST['submit']);
		break;
		case UPLOAD_ERR_FORM_SIZE:
			prnMsg(_('The file you have selected is too big. Please try a different file.'), 'error');
			unset($_POST['submit']);
		break;
		case UPLOAD_ERR_PARTIAL:
			prnMsg(_('The upload was interrupted. Please start it again.'), 'error');
			unset($_POST['submit']);
		break;
		case UPLOAD_ERR_NO_FILE:
			prnMsg(_('The file either does not exist, or the file name is empty. Please try again.'), 'error');
			unset($_POST['submit']);
		break;
		case UPLOAD_ERR_NO_TMP_DIR:
			prnMsg(_('There is no temporary directory to upload the file to. Please see your system administrator.'), 'error');
			unset($_POST['submit']);
		break;
		default:
			prnMsg(_('An unknown error occurred while uploading the file. Please see your system administrator.'), 'error');
			unset($_POST['submit']);
	}
}

if (!isset($_POST['submit'])) {
	echo '<form enctype="multipart/form-data" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<div class="page_help_text">', _('Use this screen to upload a plugin file to your plugins directory. The file should be of type *.zip'), '</div>';

	echo '<input type="submit" name="reload" value="Reload" hidden="hidden" />';
	if (!isset($_POST['reload'])) {
		echo '<fieldset>
				<legend>', _('Plugin File'), '</legend>
				<field>
					<input type="hidden" name="MAX_FILE_SIZE" value="1000000" /><br />
					<label for="pluginfile">', _('Select a plugin file'), '</label>
					<input name="pluginfile" type="file" accept="application/x-gzip" onchange="ReloadForm(reload);" />
				</field>
			</fieldset>';
	} else {
		$ZipFile = zip_open($_FILES['pluginfile']['tmp_name']);
		while ($FileName = zip_read($ZipFile)) {
			$Entry = zip_entry_name($FileName);
			if ($Entry == 'summary.xml') {
				$xml = zip_entry_read($FileName);
				$Summary = new SimpleXMLElement($xml);
				$PluginName = $Summary->name;
				$MenuLinks = $Summary->menulinks;
				$DBUpdates = $Summary->dbupdates;
				$DBRemoval = $Summary->dbremoval;
				$Scripts = $Summary->scripts->script;
			}
		}
		$ApprovedString = '<font color="0E9320"><b>' . _('Yes') . '</b></font>';
		zip_close($ZipFile);
		echo '<table>
					<tr>
						<th colspan="2">', _('Plugin attributes'), '</th>
					</tr>
					<tr>
						<td>', _('File Name'), '</td>
						<td><font color="#0E9320"><b>', $_FILES['pluginfile']['name'], '</b></font></td>
					</tr>
					<tr>
						<td>', _('Plugin Name'), '</td>
						<td><font color="0E9320"><b>', $Summary->name, '</b></font></td>
					</tr>
					<tr>
						<td>', _('License'), '</td>
						<td><font color="0E9320"><b>', $Summary->license, '</b></font></td>
					</tr>
					<tr>
						<td>', _('Approved'), '</td>
						<td>', $ApprovedString, '</td>
					</tr>
				</table>';
		echo '<input type="submit" name="submit" value="', _('Upload Plugin'), '" />';
	}
	echo '</form>';
}

include ('includes/footer.php');

function IsPlugin($File) {
	if ($File['type'] != 'application/zip') {
		prnMsg(_('The file you have selected is not a proper zip file. Please try again.'), 'error');
		unset($_POST['submit']);
		return False;
	}
	$ZipFile = zip_open($File['tmp_name']);
	while ($FileName = zip_read($ZipFile)) {
		$Entry = zip_entry_name($FileName);
		if ($Entry == 'summary.xml') {
			return True;
		}
	}
	prnMsg(_('The archive you uploaded does not appear to be a valid plugin file'), 'error');
	return False;
}

?>