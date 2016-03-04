<?php

include('includes/session.inc');

$Title = _('GL Codes Inquiry');

include('includes/header.inc');

$SQL = "SELECT group_,
			accountcode ,
			accountname
		FROM chartmaster
		INNER JOIN accountgroups
			ON chartmaster.groupcode=accountgroups.groupcode
			AND chartmaster.language=accountgroups.language
		WHERE chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
		ORDER BY sequenceintb,
				accountcode";

$ErrMsg = _('No general ledger accounts were returned by the SQL because');
$AccountsResult = DB_query($SQL, $ErrMsg);

/*show a table of the orders returned by the SQL */

echo '<table cellpadding="2">
		<tr>
			<th><h3>' . _('Group') . '</h3></th>
			<th><h3>' . _('Code') . '</h3></th>
			<th><h3>' . _('Account Name') . '</h3></th>
		</tr>';

$j = 1;
$k = 0; //row colour counter
$ActGrp = '';

while ($MyRow = DB_fetch_array($AccountsResult)) {
	if ($k == 1) {
		echo '<tr class="EvenTableRows">';
		$k = 0;
	} else {
		echo '<tr class="OddTableRows">';
		++$k;
	}

	if ($MyRow['group_'] == $ActGrp) {
		printf('<td></td>
		  			  <td>%s</td>
					  <td>%s</td>
					  </tr>', $MyRow['accountcode'], htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false));
	} else {
		$ActGrp = $MyRow['group_'];
		printf('<td><b>%s</b></td>
		  			  <td>%s</td>
					  <td>%s</td>
					  </tr>', $MyRow['group_'], $MyRow['accountcode'], htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false));
	}
}
//end of while loop

echo '</table>';
include('includes/footer.inc');
?>