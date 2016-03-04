<?php
/* $Id: RevisionTranslations.php 7040 2014-12-27 15:15:29Z tehonu $*/
/* This script is to review the item description translations. */

include('includes/session.inc');

$Title = _('Review Translated Descriptions'); // Screen identificator.
$ViewTopic = 'Inventory'; // Filename's id in ManualContents.php's TOC.
$BookMark = 'ReviewTranslatedDescriptions'; // Anchor's id in the manual's html document.
include('includes/header.inc');
echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" title="' . // Title icon.
	_('Review Translated Descriptions') . '" />' . // Icon title.
	_('Review Translated Descriptions') . '</p>'; // Page title.

include('includes/SQL_CommonFunctions.inc');

//update database if update pressed
if (isset($_POST['Submit'])) {
	for ($i = 1; $i < count($_POST); $i++) { //loop through the returned translations

		if (isset($_POST['Revised' . $i]) and ($_POST['Revised' . $i] == '1')) {
			$SQLUpdate = "UPDATE stockdescriptiontranslations
						SET needsrevision = '0',
							descriptiontranslation = '" . $_POST['DescriptionTranslation' . $i] . "',
						WHERE stockid = '" . $_POST['StockID' . $i] . "'
							AND language_id = '" . $_POST['LanguageID' . $i] . "'";
			$ResultUpdate = DB_Query($SQLUpdate, '', '', true);
			$SQLUpdate = "UPDATE stocklongdescriptiontranslations
						SET needsrevision = '0',
							longdescriptiontranslation = '" . $_POST['LongDescriptionTranslation' . $i] . "'
						WHERE stockid = '" . $_POST['StockID' . $i] . "'
							AND language_id = '" . $_POST['LanguageID' . $i] . "'";
			$ResultUpdate = DB_Query($SQLUpdate, '', '', true);
			prnMsg($_POST['StockID' . $i] . ' ' . _('descriptions') . ' ' . _('in') . ' ' . $_POST['LanguageID' . $i] . ' ' . _('have been updated'), 'success');
		}
	}
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class="selection">
		<tr>
			<th colspan="7">' . _('Translations to revise') . '</th>
		</tr>';

$SQL = "SELECT stockdescriptiontranslations.stockid,
				stockmaster.description,
				stockmaster.longdescription,
				stockdescriptiontranslations.language_id,
				stockdescriptiontranslations.descriptiontranslation,
				stocklongdescriptiontranslations.longdescriptiontranslation
		FROM stockmaster
		INNER JOIN stockdescriptiontranslations
			ON stockdescriptiontranslations.stockid = stockmaster.stockid
		INNER JOIN stocklongdescriptiontranslations
			ON stocklongdescriptiontranslations.stockid = stockmaster.stockid
		WHERE stockdescriptiontranslations.needsrevision = '1'
		ORDER BY stockdescriptiontranslations.stockid,
				stockdescriptiontranslations.language_id";

$Result = DB_query($SQL);

echo '<tr>
		<th>' . _('Code') . '</th>
		<th>' . _('Language') . '</th>
		<th>' . _('Part Description (short)') . '</th>
		<th>' . _('Part Description (long)') . '</th>
		<th>' . _('Revised?') . '</th>
	</tr>';

$k = 0; //row colour counter
$i = 1;
while ($MyRow = DB_fetch_array($Result)) {

	if ($k == 1) {
		echo '<tr class="EvenTableRows">';
		$k = 0;
	} else {
		echo '<tr class="OddTableRows">';
		$k = 1;
	}

	echo '<td>' . $MyRow['stockid'] . '</td>
		<td>' . $_SESSION['Language'] . '</td>
		<td>' . $MyRow['description'] . '</td>
		<td>' . nl2br($MyRow['longdescription']) . '</td>
		<td>&nbsp;</td></tr>'; // nl2br: Inserts HTML line breaks before all newlines in a string.

	if ($k == 1) {
		echo '<tr class="EvenTableRows">';
		$k = 0;
	} else {
		echo '<tr class="OddTableRows">';
		$k = 1;
	}

	echo '<td>&nbsp;</td>
		<td>' . $MyRow['language_id'] . '</td>';

	echo '<td><input class="text" maxlength="50" name="DescriptionTranslation' . $i . '" size="52" type="text" value="' . $MyRow['descriptiontranslation'] . '" /></td>
		<td><textarea name="LongDescriptionTranslation' . $i . '" cols="70" rows="5">' . $MyRow['longdescriptiontranslation'] . '" </textarea></td>';

	echo '<td>
			<input name="Revised' . $i . '" type="checkbox" value="1" />
			<input name="StockID' . $i . '" type="hidden" value="' . $MyRow['stockid'] . '" />
			<input name="LanguageID' . $i . '" type="hidden" value="' . $MyRow['language_id'] . '" />
		</td>
		</tr>';
	++$i;

} //end of looping

echo '</table>
		<div class="centre">
			<input type="submit" name="Submit" value="' . _('Update') . '" />
		</div>
	</form>';

include('includes/footer.inc');
?>