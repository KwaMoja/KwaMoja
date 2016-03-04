<?php

include('includes/session.inc');
$Title = _('Emloyee Master Record Maintenance');

include('includes/header.inc');

echo '<div class="toplink"><a href="' . $RootPath . '/prlEmployeeMaster.php">' . _('Create a New Employee Record') . '</a></div>';

echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_GET['EmployeeID'])) {
	$EmployeeID = $_GET['EmployeeID'];
} elseif (isset($_POST['EmployeeID'])) {
	$EmployeeID = $_POST['EmployeeID'];
}

$PayTypes = array(
	_('Salary'),
	_('Hourly')
);

if (!isset($EmployeeID)) {
	$SQL = "SELECT prlemployeemaster.employeeid,
					prlemployeemaster.lastname,
					prlemployeemaster.firstname,
					prlemployeemaster.payperiodid,
					prlemployeemaster.paytype,
					prlemployeemaster.marital,
					prlemployeemaster.birthdate,
					prlemployeemaster.active,
					prlemployeemaster.payperiodid,
					prlpayperiod.payperioddesc
				FROM prlemployeemaster
				INNER JOIN prlpayperiod
					ON prlemployeemaster.payperiodid=prlpayperiod.payperiodid
				ORDER BY lastname,
						firstname";

	$ErrMsg = _('The employee master record could not be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result) > 0) {
		echo '<table class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('Employee ID') . '</th>
						<th class="SortedColumn">' . _('Last Name ') . '</th>
						<th class="SortedColumn">' . _('First Name') . '</th>
						<th class="SortedColumn">' . _('Pay Type  ') . '</th>
						<th class="SortedColumn">' . _('Marital Status') . '</th>
						<th class="SortedColumn">' . _('Date of Birth') . '</th>
						<th class="SortedColumn">' . _('Status   ') . '</th>
						<th class="SortedColumn">' . _('Pay Period') . '</th>
					</tr>
				</thead>';

		$k = 0; //row colour counter
		echo '<tbody>';
		while ($MyRow = DB_fetch_array($Result)) {

			//alternateTableRowColor($k);
			if ($k == 1) {
				echo '<tr class="OddTableRows">';
				$k = 0;
			} else {
				echo '<tr class="EvenTableRows">';
				$k++;
			}
			echo '<td>' . $MyRow['employeeid'] . '</td>
    			<td>' . $MyRow['lastname'] . '</td>
				<td>' . $MyRow['firstname'] . '</td>
				<td>' . $PayTypes[$MyRow['paytype']] . '</td>
				<td>' . $MyRow['marital'] . '</td>
				<td>' . ConvertSQLDate($MyRow['birthdate']) . '</td>
				<td>' . $MyRow['active'] . '</td>
				<td>' . $MyRow['payperioddesc'] . '</td>
				<td><a href=' . $RootPath . '/prlEmployeeMaster.php?EmployeeID=' . $MyRow['employeeid'] . '>' . _('Edit') . '</td></tr>';
		} //END WHILE LIST LOOP
		echo '</tbody>';
		echo '</table>';
	} else {
		prnMsg( _('No employees have been created. Please create an employee first'), 'info');
	}
}

include('includes/footer.inc');
?>