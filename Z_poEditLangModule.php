<?php

/* Steve Kitchen */

/* This code is really ugly ... */

include('includes/session.inc');

$Title = _('Edit Module');// _('Edit a Language File Module')
$ViewTopic = 'SpecialUtilities';
$BookMark = 'Z_poEditLangModule';// Anchor's id in the manual's html document.

include('includes/header.inc');

/* Your webserver user MUST have read/write access to here,	otherwise you'll be wasting your time */

echo '<div class="toplink">
		<a href="' . $RootPath . '/Z_poAdmin.php">' . _('Back to the translation menu') . '</a>
	</div>';

echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Edit a Language File Module') . '" />' . ' ' . _('Edit a Language File Module') . '</p>';

echo '<div class="page_help_text">' . _('Utility to edit a language file module') . '. '. _('Current language is') . ' ' . $_SESSION['Language'] . '. ' . _('To change language click on the user name at the top left, change to language desired and click Modify') . '. ' . _('Make sure you have selected the correct language to translate!') . '. ' . '</div>';

if ($_SESSION['Language'] == 'en_GB.utf8') {
	$PathToLanguage = './locale/' . $_SESSION['Language'] . '/LC_MESSAGES/messages.pot';
} else {
	$PathToLanguage = './locale/' . $_SESSION['Language'] . '/LC_MESSAGES/messages.po';
}
$PathToNewLanguage = './locale/' . $_SESSION['Language'] . '/LC_MESSAGES/messages.po.new';

if (isset($_POST['ReMergePO'])) {

	/*update the messages.po file with any new strings */

	if (!function_exists('msgmerge')) {
		prnMsg(_('The gettext utilities must be present on your server for these language utilities to work'),'error');
		exit;
	} else {
		/*first rebuild the en_GB default with xgettext */

		$PathToDefault = './locale/en_GB.utf8/LC_MESSAGES/messages.po';
		$FilesToInclude = '*php includes/*.php includes/*.inc';
		$xgettextCmd = 'xgettext --no-wrap -L php -o ' . $PathToDefault . ' ' . $FilesToInclude;

		system($xgettextCmd);
		/*now merge the translated file with the new template to get new strings*/

		$MsgMergeCmd = 'msgmerge --no-wrap --update ' . $PathToLanguage . ' ' . $PathToDefault;

		system($MsgMergeCmd);
		//$Result = rename($PathToNewLanguage, $PathToLanguage);
		exit;
	}
}

if (isset($_POST['module'])) {
	// a module has been selected and is being modified

	$PathToLanguage_mo = mb_substr($PathToLanguage, 0, strrpos($PathToLanguage, '.')) . '.mo';

	/* now read in the language file */

	$LangFile = file($PathToLanguage);
	$LangFileEntries = sizeof($LangFile);

	if (isset($_POST['submit'])) {
		// save the modifications

		echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

		/* write the new language file */

		prnMsg(_('Writing the language file') . '.....<br />', 'info', ' ');

		for ($i = 17; $i <= $LangFileEntries; $i++) {
			if (isset($_POST['msgstr_' . $i])) {
				$LangFile[$i] = 'msgstr "' . $_POST['moduletext_' . $i] . '"' . "\n";
			}
		}
		$fpOut = fopen($PathToNewLanguage, 'w');
		for ($i = 0; $i <= $LangFileEntries; $i++) {
			$Result = fputs($fpOut, $LangFile[$i]);
		}
		$Result = fclose($fpOut);

		/* Done writing, now move the original file to a .old */
		/* and the new one to the default */

		if (file_exists($PathToLanguage . '.old')) {
			$Result = rename($PathToLanguage . '.old', $PathToLanguage . '.bak');
		}
		$Result = rename($PathToLanguage, $PathToLanguage . '.old');
		$Result = rename($PathToNewLanguage, $PathToLanguage);
		if (file_exists($PathToLanguage . '.bak')) {
			$Result = unlink($PathToLanguage . '.bak');
		}

		/*now need to create the .mo file from the .po file */
		$MsgfmtCommand = 'msgfmt ' . $PathToLanguage . ' -o ' . $PathToLanguage_mo;
		system($MsgfmtCommand);

		prnMsg(_('Done') . '<br />', 'info', ' ');

		echo '</form>';

		/* End of Submit block */
	} else {

		/* now we need to parse the resulting array into something we can show the user */

		$j = 1;
		$AlsoIn = array();
		for ($i = 17; $i <= $LangFileEntries; $i++) {
			/* start at line 18 to skip the header */
			if (mb_substr($LangFile[$i], 0, 2) == '#:') {
				/* it's a module reference */
				$AlsoIn[$j] .= str_replace(' ', '<br />', mb_substr($LangFile[$i], 3)) . '<br />';
			} elseif (mb_substr($LangFile[$i], 0, 5) == 'msgid') {
				$DefaultText[$j] = mb_substr($LangFile[$i], 7, mb_strlen($LangFile[$i]) - 9);
			} elseif (mb_substr($LangFile[$i], 0, 6) == 'msgstr') {
				$ModuleText[$j] = mb_substr($LangFile[$i], 8, mb_strlen($LangFile[$i]) - 10);
				$Msgstr[$j] = $i;
				++$j;
			}
		}
		$TotalLines = $j - 1;

		/* stick it on the screen */

		echo '<br />&nbsp;' . _('When finished modifying you must click on Modify at the bottom in order to save changes');
		prnMsg(_('Your existing translation file (messages.po) will be saved as messages.po.old') . '<br />', 'info', _('PLEASE NOTE'));
		echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<table>
				<tr>
					<th align="center">' . _('Language File for') . ' "' . $_POST['language'] . '"</th>
				</tr>
				<tr>
					<td align="center">' . _('Module') . ' "' . $_POST['module'] . '"</td>
				</tr>
				<tr>
					<td></td>
				</tr>';
		echo '<tr><td>';

		echo '<table width="100%">
				<tr>
					<th>' . _('Default text') . '</th>
					<th>' . _('Translation') . '</th>
					<th>' . _('Exists in') . '</th>
				</tr>' . "\n";

		for ($i = 1; $i <= $TotalLines; $i++) {

			$b = mb_strpos($AlsoIn[$i], $_POST['module']);

			if ($b === False) {
				/* skip it */

			} else {
				echo '<tr>
						<td valign="top"><i>' . $DefaultText[$i] . '</i></td>
						<td valign="top"><input type="text" size="60" name="moduletext_' . $Msgstr[$i] . '" value="' . $ModuleText[$i] . '" /></td>
						<td valign="top">' . $AlsoIn[$i] . '<input type="hidden" name="msgstr_' . $Msgstr[$i] . '" value="' . $Msgstr[$i] . '" /></td>
					</tr>';
				echo '<tr>
						<th colspan="3"></th>
					</tr>';
			}

		}

		echo '</table>';

		echo '<div class="centre">
				<input type="submit" name="submit" value="' . _('Modify') . '" />
				<input type="hidden" name="module" value="' . $_POST['module'] . '" />
			</div>';

		echo '</form>';
	}

} else {

	/* get available modules */

	/* This is a messy way of producing a directory listing of ./locale to fish out */
	/* the language directories that have been set up */
	/* The other option would be to define an array of the languages you want */
	/* and check for the existance of the directory */

	/* $ListDirCmd should probably be defined in config.php as a global value */
	/* You'll need to change it if you are running a Windows server - sorry !! */

	if ($handle = opendir('.')) {
		$i = 0;
		while (false !== ($file = readdir($handle))) {
			if ((mb_substr($file, 0, 1) != ".") and (!is_dir($file))) {
				$AvailableModules[$i] = $file;
				$i += 1;
			}
		}
		closedir($handle);
	}

	if ($handle = opendir(".//includes")) {
		while (false !== ($file = readdir($handle))) {
			if ((mb_substr($file, 0, 1) != ".") and (!is_dir($file))) {
				$AvailableModules[$i] = $file;
				$i += 1;
			}
		}
		closedir($handle);
	}

	sort($AvailableModules);
	$NumberOfModules = sizeof($AvailableModules) - 1;

	if (!is_writable('./locale/' . $_SESSION['Language'])) {
		prnMsg(_('You do not have write access to the required files please contact your system administrator'), 'error');
	} else {
		echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" >';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

		echo '<table>
				<tr>
					<td>' . _('Select the module to edit') . '</td>
					<td><select name="module">';
		for ($i = 0; $i < $NumberOfModules; $i++) {
			echo '<option>' . $AvailableModules[$i] . '</option>';
		}
		echo '</select>
				</td>';

		echo '</tr>
		</table>';

		echo '<div class="centre">
				<input type="submit" name="proceed" value="' . _('Proceed') . '" />
				<input type="submit" name="ReMergePO" value="' . _('Refresh messages with latest strings') . '" />
			</div>
		</form>';
	}
}

include('includes/footer.inc');

?>