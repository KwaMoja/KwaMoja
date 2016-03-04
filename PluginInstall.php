<?php

include('includes/session.inc');

$_SESSION['Updates']['Errors'] = 0;
$_SESSION['Updates']['Successes'] = 0;
$_SESSION['Updates']['Warnings'] = 0;

include('includes/UpgradeDB_' . $DBType . '.inc');
$Title = _('Install a Plugin');

include('includes/header.inc');

echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/plugin.png" width="24px" title="' . _('Install Plugin') . '" alt="" />' . _('Install Plugin') . '</p>';

if (isset($_POST['Install'])) {
	$ZipFile = zip_open('plugins/' . $_POST['Available']);
	prnMsg($_POST['Available'] . ' ' . _('appears to be a genuine plugin file.'), 'success');
	while ($FileName = zip_read($ZipFile)) {
		$Entry = zip_entry_name($FileName);
		if ($Entry == 'summary.xml') {
			$xml = zip_entry_read($FileName);
			$Summary = new SimpleXMLElement($xml);
			$PluginName = $Summary->name;
			$MenuLinks = $Summary->menulinks;
			$DBUpdates = $Summary->dbupdates;
			$DBRemoves = $Summary->dbremoval;
			$Scripts = $Summary->scripts->script;
			$Summary->installed = 1;
			$TempSummary = tempnam('', 'SM');
			$Summary->asXML($TempSummary);
		}
	}
	prnMsg(_('Plugin details have been successfully extracted from the file'), 'success');
	zip_close($ZipFile);
	$ZipFile = zip_open('plugins/' . $_POST['Available']);
	while ($FileName = zip_read($ZipFile)) {
		$Entry = zip_entry_name($FileName);
		if ($Entry == $MenuLinks) {
			$Menus = zip_entry_read($FileName);
			$TempMenus = tempnam('includes', 'MN');
			$handle = fopen($TempMenus, "w");
			fwrite($handle, $Menus);
			RemoveLine('includes/PluginMenuLinksArray.php');
			$Text = $Menus . "\n";
			$Text .= '?>';
			$fp = fopen('includes/PluginMenuLinksArray.php', 'a');
			fwrite($fp, $Text);
		}
	}
	if ($Menus != '') {
		prnMsg(_('The menu additions have been applied'), 'success');
	}
	zip_close($ZipFile);
	$ZipFile = zip_open('plugins/' . $_POST['Available']);
	while ($FileName = zip_read($ZipFile)) {
		$Entry = zip_entry_name($FileName);
		if ($Entry == $DBUpdates) {
			$Updates = zip_entry_read($FileName);
			$TempDB = tempnam('includes', 'DB');
			$handle = fopen($TempDB, "w");
			fwrite($handle, $Updates);
			include($TempDB);
		}
	}
	if ($Menus != '') {
		prnMsg(_('The database changes have been applied'), 'success');
	}
	zip_close($ZipFile);
	$ZipFile = zip_open('plugins/' . $_POST['Available']);
	while ($FileName = zip_read($ZipFile)) {
		$Entry = zip_entry_name($FileName);
		if ($Entry == $DBRemoves) {
			$Removes = zip_entry_read($FileName);
			$TempDBRemoves = tempnam('includes', 'DB');
			$handle = fopen($TempDBRemoves, "w");
			fwrite($handle, $Removes);
		}
	}
	zip_close($ZipFile);
	foreach ($Scripts as $Script) {
		$ZipFile = zip_open('plugins/' . $_POST['Available']);
		while ($FileName = zip_read($ZipFile)) {
			$Entry = zip_entry_name($FileName);
			if ($Entry == $Script->name) {
				$Code = zip_entry_read($FileName, 1048576);
				$TempName = tempnam('', 'SC');
				$handle = fopen($TempName, "w");
				fwrite($handle, $Code);
				rename($TempName, $Script->name);
				NewScript($Script->name, $Script->pagesecurity);
				prnMsg($Script->name . ' ' . _('has been successfully added'), 'success');
			}
		}
		zip_close($ZipFile);
	}
	$Zip = new ZipArchive;
	$Zip->open('plugins/' . $_POST['Available'], ZipArchive::OVERWRITE);
	$Zip->addFile($TempSummary, 'summary.xml');
	$Zip->addFile($TempMenus, $MenuLinks);
	$Zip->addFile($TempDB, $DBUpdates);
	$Zip->addFile($TempDBRemoves, $DBRemoves);
	foreach ($Scripts as $Script) {
		$Zip->addFile($Script->name);
	}
	$Zip->close();
	unlink($TempSummary);
	unlink($TempMenus);
	unlink($TempDB);
	unlink($TempDBRemoves);
	$ForceConfigReload = True;
	prnMsg(_('The plugin has been successfully installed. You can now use it from the main menu'), 'success');
} else {
	echo '<form enctype="multipart/form-data" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="submit" name="reload" value="Reload" hidden="hidden" />';
	if (!isset($_POST['reload'])) {

		echo '<div class="page_help_text">' . _('Select the plugin that you wish to install from the list below.') . '</div>';
		echo '<br /><div class="centre">
				<div class="box_header">' . _('Available plugins') . '</div>
				<select multiple="multiple" name="Available" onclick="ReloadForm(reload);">';

		$Plugins = scandir('plugins/');

		foreach ($Plugins as $Plugin) {
			if (IsPlugin($Plugin)) {
				$ZipFile = zip_open('plugins/' . $Plugin);
				while ($FileName = zip_read($ZipFile)) {
					$Entry = zip_entry_name($FileName);
					if ($Entry == 'summary.xml') {
						$xml = zip_entry_read($FileName);
						$Summary = new SimpleXMLElement($xml);
						$PluginName = $Summary->name;
						if ($Summary->installed == 0) {
							echo '<option value="' . $Plugin . '">' . $PluginName . '</option>';
						}
					}
				}
			}
		}

		echo '</select>
			</div>';
	} else {
		$ZipFile = zip_open('plugins/' . $_POST['Available']);
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
		echo '<input type="hidden" name="Available" value="' . $_POST['Available'] . '" />';
		$ApprovedString = '<font color="0E9320"><b>' . _('Yes') . '</b></font>';
		zip_close($ZipFile);
		echo '<br />
				<table class="selection">
					<tr>
						<th colspan="2">' . _('Plugin attributes') . '</th>
					</tr>
					<tr>
						<td>' . _('File Name') . '</td>
						<td><font color="#0E9320"><b>' . $_POST['Available'] . '</b></font></td>
					</tr>
					<tr>
						<td>' . _('Plugin Name') . '</td>
						<td><font color="0E9320"><b>' . $Summary->name . '</b></font></td>
					</tr>
					<tr>
						<td>' . _('License') . '</td>
						<td><font color="0E9320"><b>' . $Summary->license . '</b></font></td>
					</tr>
					<tr>
						<td>' . _('Approved') . '</td>
						<td>' . $ApprovedString . '</td>
					</tr>
				</table>';
		echo '<br /><div class="centre">
				<input type="submit" name="Install" value="' . _('Install Plugin') . '" />';
		echo '</form>';
	}

}

function IsPlugin($File) {
	if (mime_content_type('plugins/' . $File) != 'application/zip') {
		return False;
	}
	$ZipFile = zip_open('plugins/' . $File);
	while ($FileName = zip_read($ZipFile)) {
		$Entry = zip_entry_name($FileName);
		if ($Entry == 'summary.xml') {
			return True;
		}
	}
	return False;
}

function RemoveLine($FileName) {
	// load the data and delete the line from the array
	$Lines = file($FileName);
	$LastLine = sizeof($Lines) - 1;
	unset($Lines[$LastLine]);

	// write the new data to the file
	$fp = fopen($FileName, 'w');
	fwrite($fp, implode('', $Lines));
	fclose($fp);
}

function executeSQL($SQL, $TrapErrors = False) {
	/* Run an sql statement and return an error code */
	DB_IgnoreForeignKeys();
	$Result = DB_query($SQL, '', '', false, $TrapErrors);
	$ErrorNumber = DB_error_no();
	DB_ReinstateForeignKeys();
	return $ErrorNumber;
}

include('includes/footer.inc');

?>