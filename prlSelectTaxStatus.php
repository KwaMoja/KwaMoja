<?php

include('includes/session.inc');
$Title = _('Employee Tax Status Maintenance');

include('includes/header.inc');

?>
<a href="prlUserSettings.php">Back to User Settings
    </a>
	<?php

echo "<table WIDTH=30% BORDER=2></tr>";
echo '<tr><td WIDTH=100%>';
echo '<a href="' . $RootPath . '/prlTaxStatus.php?SelectedAccountr=">' . _('Add tax status records') . '</a><br />';
echo '</td><td WIDTH=100%>';
echo '</td></tr></table><br>';

if (isset($_GET['TaxStatusID'])) {
	$TaxStatusID = strtoupper($_GET['TaxStatusID']);
} elseif (isset($_POST['TaxStatusID'])) {
	$TaxStatusID = strtoupper($_POST['TaxStatusID']);
} else {
	unset($TaxStatusID);
}



if (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button
	$SQL = "DELETE FROM prltaxstatus WHERE taxstatusid " . LIKE . "'" . DB_escape_string($TaxStatusID) . "'";
	$Result = DB_query($SQL);
	prnMsg('employee id has been deleted' . '!', 'success');
	//}
	//end if account group used in GL accounts
	unset($TaxStatusID);
	unset($_GET['TaxStatusID']);
	unset($_GET['delete']);
	unset($_POST['TaxStatusID']);
	//unset ($_POST['EmployeeID']);
}

if (!isset($TaxStatusID)) {
	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedAccount will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of ChartMaster will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/

	$SQL = "SELECT taxstatusid,
			taxstatusdescription
		FROM prltaxstatus
		ORDER BY taxstatusid";
	$ErrMsg = _('The tax status master could not be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);

	echo '<table border=1>';
	echo "<tr>
		<th>" . _('Tax Status ID') . "</td>
		<th>" . _('Tax Status Description ') . "</td>
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
		echo '<td><a href="' . $RootPath . '/prlTaxStatus.php?&TaxStatusID=' . $MyRow[0] . '">' . _('Edit/Delete') . '</a></td>';
		echo '</tr>';

	} //END WHILE LIST LOOP

	//END WHILE LIST LOOP
} //END IF selected="selected" ACCOUNT


echo '</table>';
//end of ifs and buts!

include('includes/footer.inc');
?>