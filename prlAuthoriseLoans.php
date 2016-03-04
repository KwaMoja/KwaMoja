<?php
/* $Revision: 1.0 $ */

include('includes/session.inc');

$Status = array();
$Status[0] = _('Pending Authorisation');
$Status[1] = _('Authorised');
$Status[2] = _('Posted');
$Status[3] = _('Cancelled');
$Status[4] = _('Rejected');
$Status[5] = _('Written Off');

$Title = _('Employee Loan Authorisation');
include('includes/header.inc');

if (isset($_POST['update'])) {
	foreach ($_POST as $key=>$value) {
		if (mb_substr($key, 0, 6) == 'Status') {
			$Loan = mb_substr($key, 6);
			$sql = "UPDATE prlloanfile SET status='" . $value . "' WHERE counterindex='" . $Loan . "'";
			$result = DB_query($sql);
		}
	}
}

echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/loan.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';
echo '<form method="post" class="noPrint" id="LoanDeductionForm" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

$SQL = "SELECT prlloanfile.counterindex,
				prlloanfile.loanfileid,
				prlloanfile.loanfiledesc,
				prlloanfile.employeeid,
				prlemployeemaster.firstname,
				prlemployeemaster.middlename,
				prlemployeemaster.lastname,
				prlloanfile.loandate,
				prlloantable.loantabledesc,
				departments.description,
				prlloanfile.loanamount,
				prlloanfile.amortization,
				prlloanfile.nextdeduction,
				prlloanfile.loanbalance,
				prlloanfile.status,
				www_users.realname
			FROM prlloanfile
			INNER JOIN prlemployeemaster
				ON prlloanfile.employeeid=prlemployeemaster.employeeid
			INNER JOIN departments
				ON prlemployeemaster.departmentid=departments.departmentid
			INNER JOIN prlloantable
				ON prlloantable.loantableid=prlloanfile.loantableid
			INNER JOIN www_users
				ON www_users.userid=prlloanfile.authoriser
			WHERE prlloanfile.loanbalance>0
				AND status=0
				AND prlloanfile.authoriser='" . $_SESSION['UserID'] . "'";
$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0) {
	$AllowedStatuses = array(0, 1, 3, 4);
	echo '<table class="selection">
			<tr>
				<th>' . _('Description') . '</th>
				<th>' . _('Employee ID') . '</th>
				<th>' . _('Employee Name') . '</th>
				<th>' . _('Department') . '</th>
				<th>' . _('Loan Type') . '</th>
				<th>' . _('Loan Date') . '</th>
				<th>' . _('Loan Amount') . '</th>
				<th>' . _('Repayment per') . '<br />' . _('Pay period') . '</th>
				<th>' . _('Balance') . '</th>
				<th>' . _('Status') . '</th>
			</tr>';

	while ($LoanRow = DB_fetch_array($Result)) {
		echo '<tr>
				<td>' . $LoanRow['loanfiledesc'] . '</td>
				<td>' . $LoanRow['employeeid'] . '</td>
				<td>' . $LoanRow['firstname'] . ' ' . $LoanRow['middlename'] . ' ' . $LoanRow['lastname'] . '</td>
				<td>' . $LoanRow['description'] . '</td>
				<td>' . $LoanRow['loantabledesc'] . '</td>
				<td>' . ConvertSQLDate($LoanRow['loandate']) . '</td>
				<td class="number">' . locale_number_format($LoanRow['loanamount'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($LoanRow['amortization'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($LoanRow['loanbalance'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td><select name="Status' . $LoanRow['counterindex'] . '">';
		foreach ($AllowedStatuses as $AllowedStatus) {
			if ($AllowedStatus == $LoanRow['status']) {
				echo '<option selected="selected" value="' . $AllowedStatus . '">' . $Status[$AllowedStatus] . '</option>';
			} else {
				echo '<option value="' . $AllowedStatus . '">' . $Status[$AllowedStatus] . '</option>';
			}
		}
		echo '</select>
					</td>
				</tr>';
	}
	echo '</table>';
	echo '<div class="centre">
			<input type="submit" name="update" value="' . _('Update Status Information') . '" />
		</div>';
} else {
	prnMsg( _('There are no loans for you to authorise'), 'info');
}

include('includes/footer.inc');

?>