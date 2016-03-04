<?php

/* Steve Kitchen */

/* This code is really ugly ... */

include('includes/session.inc');

$Title = _('Edit Remaining Items');// _('Edit Remaining Strings For This Language')
$ViewTopic = 'SpecialUtilities';
$BookMark = 'Z_poEditLangRemaining';// Anchor's id in the manual's html document.
include('includes/header.inc');

echo '<div class="toplink">
		<a href="' . $RootPath . '/Z_poAdmin.php">' . _('Back to the translation menu') . '</a>
	</div>';

echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . _('Edit Remaining Strings For This Language') . '" />' . ' ' . _('Edit Remaining Strings For This Language') . '</p>';

/* Your webserver user MUST have read/write access to here,
otherwise you'll be wasting your time */

if ($_SESSION['Language'] == 'en_GB.utf8') {
	$PathToLanguage = './locale/' . $_SESSION['Language'] . '/LC_MESSAGES/messages.pot';
} else {
	$PathToLanguage = './locale/' . $_SESSION['Language'] . '/LC_MESSAGES/messages.po';
}
$PathToNewLanguage = './locale/' . $_SESSION['Language'] . '/LC_MESSAGES/messages.po.new';

echo '<div class="page_help_text">' .
		_('Utility to edit a language file module') . _('Current language is') . ' ' . $_SESSION['Language'] .
		_('When finished modifying you must click on Modify at the bottom in order to save changes') .
	'</div>';

$PathToLanguage_mo = mb_substr($PathToLanguage, 0, strrpos($PathToLanguage, '.')) . '.mo';

/* now read in the language file */

$LangFile = file($PathToLanguage);
$LangFileEntries = sizeof($LangFile);

if (isset($_POST['submit'])) {
	// save the modifications

	echo '<table><tr><td>';
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
	echo '</td></tr></table>';
	/* End of Submit block */
} else {

	/* now we need to parse the resulting array into something we can show the user */

	$j = 1;
	$AlsoIn = array();
	$DefaultText = array();
	$ModuleText = array();
	for ($i = 17; $i < $LangFileEntries; $i++) {
		/* start at line 18 to skip the header */
		if (mb_substr($LangFile[$i], 0, 2) == '#:') {
			/* it's a module reference */
			$AlsoIn[$j] = str_replace(' ', '<br />', mb_substr($LangFile[$i], 3)) . '<br />';
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
	prnMsg(_('Your existing translation file (messages.po) will be saved as messages.po.old') . '<br />', 'info', _('PLEASE NOTE'));
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table>
			<tr>
				<th>' . _('Language File for') . ' "' . $_SESSION['Language'] . '"</th>
			</tr>
			<tr>
				<td></td>
			</tr>
			<tr>
				<td>
					<table width="100%">
						<tr>
							<th>' . _('Default text') . '</th>
							<th>' . _('Translation') . '</th>
							<th>' . _('Exists in') . '</th>
						</tr>' . "\n";

	for ($i = 1; $i <= $TotalLines; $i++) {
		if ($ModuleText[$i] == "") {
			echo '<tr>
					<td valign="top"><i>' . $DefaultText[$i] . '</i></td>
					<td valign="top"><input type="text" size="60" name="moduletext_' . $Msgstr[$i] . '" value="' . $ModuleText[$i] . '" /></td>
					<td valign="top">' . $AlsoIn[$i] . '<input type="hidden" name="msgstr_' . $Msgstr[$i] . '" value="' . $Msgstr[$i] . '" /></td>
				</tr>
				<tr>
					<th colspan="3"></th>
				</tr>';
		}
	}

	echo '</table>';

	echo '</td></tr>';
	echo '</table>';
	echo '<div class="centre">
			<input type="submit" name="submit" value="' . _('Modify') . '" />
			<input type="hidden" name="module" value="' . $_POST['module'] . '" />
		</div>';

	echo '</form>';
}



include('includes/footer.inc');

?>