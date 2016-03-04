<?php
include('includes/session.inc');
$Title = _('View Other Income Data');

include('includes/header.inc');



if (isset($_GET['Counter'])) {
	$Counter = $_GET['Counter'];
} elseif (isset($_POST['Counter'])) {
	$Counter = $_POST['Counter'];
} else {
	unset($Counter);
}



if (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

	// PREVENT DELETES IF DEPENDENT RECORDS
	if ($CancelDelete == 0) {
		$SQL = "DELETE FROM prlothincfile WHERE counterindex='$Counter'";
		$Result = DB_query($SQL);
		prnMsg(_('Other Income record for') . ' ' . $Counter . ' ' . _('has been deleted'), 'success');
		unset($Counter);
		unset($_SESSION['Counter']);
	} //end if Delete paypayperiod
}


if (!isset($Counter)) {
	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
	echo '<input type="hidden" name="New" value="Yes">';
	echo '<table>';

	$SQL = "SELECT  	counterindex,
						employeeid,
						othdate,
						othincid,
						othincamount
		FROM prlothincfile
		ORDER BY counterindex";
	$ErrMsg = _('The ot could not be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);

	echo '<table border=1>';
	echo "<tr>
		<th>" . _('Index') . "</td>
		<th>" . _('Emp ID') . "</td>
		<th>" . _('Date') . "</td>
		<th>" . _('OthInc ID') . "</td>
		<th>" . _('Amount') . "</td>
	</tr>";

	$k = 0; //row colour counter

	while ($MyRow = DB_fetch_row($Result)) {

		if ($k == 1) {
			echo "<tr bgcolor='#CCCCCC'>";
			$k = 0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}

		echo '<td>' . $MyRow[0] . '</td>';
		echo '<td>' . $MyRow[1] . '</td>';
		echo '<td>' . $MyRow[2] . '</td>';
		echo '<td>' . $MyRow[3] . '</td>';
		echo '<td>' . $MyRow[4] . '</td>';
		//echo '<td>' . $MyRow[5] . '</td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?&Counter=' . $MyRow[0] . '&delete=1">' . _('Delete') . '</a></td>';
		echo '</tr>';

	} //END WHILE LIST LOOP

	//END WHILE LIST LOOP
} //END IF selected="selected" ACCOUNT


echo '</table>';
//end of ifs and buts!

include('includes/footer.inc');
?>