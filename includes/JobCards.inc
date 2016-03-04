<?php

function GetJobCards() {
	// No option selected yet, so show Customer Type drop down list
	$SQL = "SELECT id,
					invoice,
					createdate,
					completedate,
					description
				FROM jobcards
				WHERE debtorno='" . $_SESSION['CustomerID'] . "'";
	$ErrMsg = _('The job cards can not be retrieved!');
	$jobc_result = DB_query($SQL, $ErrMsg);

	// Error if no customer types setup
	if (DB_num_rows($jobc_result) == 0) {
		echo '<tr>';
		echo '<td colspan="5">';
		echo _('No Job Cards found');
		echo '</td>';
		echo '</tr>';
	} //DB_num_rows($jobc_result) == 0
	else {
		echo '<tr>';
		echo '<td colspan="5">';
		while ($MyRow = DB_fetch_array($jobc_result)) {
			if ($MyRow['completedate'] == NULL) {
				$MyRow['completedate'] = '0000-00-00';
			} //$MyRow['completedate'] == NULL
			echo '<tr>
					<td width=10%><a href="JobCards.php?DebtorNo=' . urlencode($_SESSION['CustomerID']) . '&BranchNo=' . urlencode($_GET['BranchNo']) . '&JobCardNo=' . urlencode($MyRow['id']) . '&UpdateJob=1">' . $MyRow['id'] . '</a></td>
					<td width=10%>' . $MyRow['invoice'] . '</td>
					<td width=10%>' . ConvertSQLDate($MyRow['createdate']) . '</td>
					<td width=10%>' . ConvertSQLDate($MyRow['completedate']) . '</td>
					<td width=50%>' . $MyRow['description'] . '</td>
					<td width=10%><a href="#" onclick=window.open("JobCards.php?DebtorNo=' . urlencode($_SESSION['CustomerID']) . '&BranchNo=' . urlencode($_GET['BranchNo']) . '&JobCardNo=' . urlencode($MyRow['id']) . '&UpdateJob=1&JobCPrint=1","Test")><center>Print</center></a></td>
				</tr>';
		} //$MyRow = DB_fetch_array($jobc_result)
		echo '</td>';
		echo '</tr>';
	}

}

function GetDebtorInfo($printbk, $DebtorNo, $BranchCode) {
	// No option selected yet, so show Customer Type drop down list
	$SQL = "SELECT debtorsmaster.debtorno,
					debtorsmaster.name,
					debtorsmaster.address1,
					debtorsmaster.address2,
					debtorsmaster.address3,
					debtorsmaster.address4,
					debtorsmaster.address5,
					custbranch.branchcode,
					custbranch.brname,
					custbranch.braddress1,
					custbranch.braddress2,
					custbranch.braddress3,
					custbranch.braddress4,
					custbranch.braddress5,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.contactname
				FROM debtorsmaster
				INNER JOIN custbranch
					ON debtorsmaster.debtorno = custbranch.debtorno
				WHERE custbranch.branchcode='" . $BranchCode . "'
					AND custbranch.debtorno='" . $DebtorNo . "'";

	$ErrMsg = _('The job cards can not be retrieved!');
	$jobc_result = DB_query($SQL, $ErrMsg);
	$MyRow = DB_fetch_array($jobc_result);

	// Error if no customer types setup
	if (DB_num_rows($jobc_result) != 0) {
		echo '<tr>
				<td style="' . $printbk . '">
					<b>' . _('Name') . ':</b>' . $MyRow['name'] . '<br>
					<b>' . _('Address 1') . ':</b>' . $MyRow['address1'] . '<br>
					<b>' . _('Address 2') . ':</b>' . $MyRow['address2'] . '<br>
					<b>' . _('Address 3') . ':</b>' . $MyRow['address3'] . '<br>
					<b>' . _('Address 4') . ':</b>' . $MyRow['address4'] . '<br>
					<b>' . _('Address 5') . ':</b>' . $MyRow['address5'] . '<br>
				</td>';
		echo '<td style="' . $printbk . '">
				<b>' . _('Contact') . ':</b>' . $MyRow['contactname'] . '<br>
				<b>' . _('Telephone') . ':</b>' . $MyRow['phoneno'] . '<br>
				<b>' . _('E-Mail') . ':</b> <br>
				<b>' . _('Fax') . ':</b> ' . $MyRow['faxno'] . '<br>
			</td>
		</tr>';
	} //DB_num_rows($jobc_result) != 0

}

function GetJobCardNO() {
	$SQL = 'SELECT MAX(id) FROM jobcards';
	$ErrMsg = _('The job cards can not be retrieved!');
	$jobc_result = DB_query($SQL, $ErrMsg);
	$MyRow = DB_fetch_row($jobc_result);
	$ret = $MyRow[0] + 1;
	return $ret;

}

?>