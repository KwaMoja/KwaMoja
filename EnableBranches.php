<?php
include ('includes/session.php');

$Title = _('Enable Customer Branches');
$ViewTopic = '';
$BookMark = '';
include ('includes/header.php');

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/maintenance.png" title="', _('Enable Customer Branches'), '" alt="" />', $Title, '
	</p>';

if (isset($_POST['Submit'])) {
	foreach ($_POST as $Key => $Value) {
		if (substr($Key, 0, 6) == 'enable') {
			$Index = substr($Key, 6);
			$UpdateSQL = "UPDATE custbranch SET disabletrans=0
								WHERE debtorno='" . $_POST['debtorno' . $Index] . "'
									AND branchcode='" . $_POST['branchno' . $Index] . "'";
			$UpdateResult = DB_query($UpdateSQL);
		}
	}
	prnMsg(_('All updates have been applied'), 'success');
}

$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.name,
				custbranch.branchcode,
				custbranch.brname
			FROM custbranch
			INNER JOIN debtorsmaster
				ON debtorsmaster.debtorno=custbranch.debtorno
			WHERE custbranch.disabletrans=1";
$Result = DB_query($SQL);
if (DB_num_rows($Result) > 0) {

	echo '<form action="', basename(__FILE__), '" method="post"  enctype="multipart/form-data">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<table>
			<tr>
				<th>', _('Debtor Number'), '</th>
				<th>', _('Debtors Name'), '</th>
				<th>', _('Branch Code'), '</th>
				<th>', _('Branch Name'), '</th>
			</tr>';
	$i = 0;
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<input type="hidden" name="debtorno', $i, '" value="', $MyRow['debtorno'], '" />';
		echo '<input type="hidden" name="branchno', $i, '" value="', $MyRow['branchcode'], '" />';
		echo '<tr class="striped_row">
				<td>', $MyRow['debtorno'], '</td>
				<td>', $MyRow['name'], '</td>
				<td>', $MyRow['branchcode'], '</td>
				<td>', $MyRow['brname'], '</td>
				<td><input type="checkbox" name="enable', $i, '" /></td><td>', _('Enable'), '</td>
			</tr>';
		++$i;
	}

	echo '</table>';

	echo '<div class="centre">
			<input type="submit" name="Submit" value="', _('Enable Customer Branches'), '" />
		</div>';
	echo '</form>';
} else {
	prnMsg(_('There are no customer branches requiring authorisation'), 'info');
}

include ('includes/footer.php');

?>