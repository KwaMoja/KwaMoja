<?php

include('includes/session.inc');
$Title = _('Database table details');
include('includes/header.inc');

$sql = 'DESCRIBE ' . $_GET['table'];
$result = DB_query($sql, $db);

echo '<table>
		<tr>
			<th>' . _('Field name') . '</th>
			<th>' . _('Field type') . '</th>
			<th>' . _('Can field be null') . '</th>
			<th>' . _('Default') . '</th>
		</tr>';
while ($myrow = DB_fetch_row($result)) {
	echo '<tr>
			<td>' . $myrow[0] . '</td>
			<td>' . $myrow[1] . '</td>
			<td>' . $myrow[2] . '</td>
			<td>' . $myrow[4] . '</td><
		/tr>';
}
echo '</table>';
include('includes/footer.inc');

?>