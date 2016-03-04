<?php

include('includes/session.inc');
$Title = _('View Payroll Data');

include('includes/header.inc');



if (isset($_GET['Counter'])) {
	$Counter = $_GET['Counter'];
} elseif (isset($_POST['Counter'])) {
	$Counter = $_POST['Counter'];
} else {
	unset($Counter);
}


/*
if (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

$CancelDelete = 0;

$SQL = "SELECT payrollid
FROM prlpayrollperiod
WHERE prlpayrollperiod.payrollid='" . $PayrollID . "'
AND prlpayrollperiod.payclosed='1'";
$PayDetails = DB_query($SQL);
if(DB_num_rows($PayDetails)>0)
{
$CancelDelete = 1;
prnMsg('Payroll is closed. Can not delete this record...','success');
}


// PREVENT DELETES IF DEPENDENT RECORDSs
if ($CancelDelete == 0) {
$SQL="DELETE FROM prlpayrolltrans WHERE counterindex='$Counter'";
$Result = DB_query($SQL);
prnMsg(_('Payroll record ') . ' ' . $Counter . ' ' . _('has been deleted'),'success');
unset($Counter);
unset($_SESSION['Counter']);
} //end of Delete
}
*/


if (!isset($Counter)) {
	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
	echo '<input type="hidden" name="New" value="Yes">';
	echo '<table>';

	$SQL = "SELECT  	payrollid,
						employeeid,
						periodrate,
						hourlyrate,
						basicpay,
						othincome,
						absent,
						late,
						otpay,
						grosspay,
						loandeduction,
						sss,
						hdmf,
						philhealth,
						tax,
						netpay,
						fsmonth,
						fsyear
		FROM prlpayrolltrans
		ORDER BY counterindex";
	$ErrMsg = _('Payroll record could not be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);

	echo '<table border=1>';
	echo "<tr>
		<th>" . _('Pay ID ') . "</td>
		<th>" . _('Emp ID') . "</td>
		<th>" . _('Period Rate') . "</td>
		<th>" . _('Hourly Rate') . "</td>
		<th>" . _('Basic Pay') . "</td>
		<th>" . _('Other Income') . "</td>
		<th>" . _('Absent') . "</td>
		<th>" . _('Late') . "</td>
		<th>" . _('Overtime Pay') . "</td>
		<th>" . _('Gross Pay') . "</td>
		<th>" . _('Loan Deduction') . "</td>
		<th>" . _('NSSF') . "</td>
		<th>" . _('Tax') . "</td>
		<th>" . _('Net Pay') . "</td>
		<th>" . _('Month') . "</td>
		<th>" . _('Year') . "</td>
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
		echo '<td>' . $MyRow[5] . '</td>';
		echo '<td>' . $MyRow[6] . '</td>';
		echo '<td>' . $MyRow[7] . '</td>';
		echo '<td>' . $MyRow[8] . '</td>';
		echo '<td>' . $MyRow[9] . '</td>';
		echo '<td>' . $MyRow[10] . '</td>';
		echo '<td>' . $MyRow[11] . '</td>';
		echo '<td>' . $MyRow[12] . '</td>';
		echo '<td>' . $MyRow[13] . '</td>';
		echo '<td>' . $MyRow[14] . '</td>';
		echo '<td>' . $MyRow[15] . '</td>';
		//echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?&Counter=' . $MyRow[0] . '&delete=1">' . _('Delete') .'</a></td>';
		echo '</tr>';

	} //END WHILE LIST LOOP

	//END WHILE LIST LOOP
} //END IF selected="selected" ACCOUNT


echo '</table>';
//end of ifs and buts!

include('includes/footer.inc');
?>