<?php

function GetJobCards($db) {
	// No option selected yet, so show Customer Type drop down list
	$sql = "SELECT id,
							invoice,
							createdate,
							completedate,
							description
						FROM jobcards
						WHERE debtorno='" . $_SESSION['CustomerID'] . "'";
	$ErrMsg = _('The job cards can not be retrieved!');
	$jobc_result = DB_query($sql, $db, $ErrMsg);

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
		while ($myrow = DB_fetch_array($jobc_result)) {
			if ($myrow['completedate'] == NULL) {
				$myrow['completedate'] = '0000-00-00';
			} //$myrow['completedate'] == NULL
			echo '<tr>
					<td width=10%><a href="JobCards.php?DebtorNo=' . $_SESSION['CustomerID'] . '&BranchNo=' . $_GET['BranchNo'] . '&JobCardNo=' . $myrow['id'] . '&UpdateJob=1">' . $myrow['id'] . '</a></td>
					<td width=10%>' . $myrow['invoice'] . '</td>
					<td width=10%>' . ConvertSQLDate($myrow['createdate']) . '</td>
					<td width=10%>' . ConvertSQLDate($myrow['completedate']) . '</td>
					<td width=50%>' . $myrow['description'] . '</td>
					<td width=10%><a href="#" onclick=window.open("JobCards.php?DebtorNo=' . $_SESSION['CustomerID'] . '&BranchNo=' . $_GET['BranchNo'] . '&JobCardNo=' . $myrow['id'] . '&UpdateJob=1&JobCPrint=1","Test")><center>Print</center></a></td>
				</tr>';
		} //$myrow = DB_fetch_array($jobc_result)
		echo '</td>';
		echo '</tr>';
	}

}

function GetDebtorInfo($db, $printbk, $DebtorNo, $BranchCode) {
	// No option selected yet, so show Customer Type drop down list
	$sql = "SELECT debtorsmaster.debtorno,
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
	$jobc_result = DB_query($sql, $db, $ErrMsg);
	$myrow = DB_fetch_array($jobc_result);

	// Error if no customer types setup
	if (DB_num_rows($jobc_result) != 0) {
		echo '<tr>
				<td style="' . $printbk . '">
					<b>' . _('Name') . ':</b>' . $myrow['name'] . '<br>
					<b>' . _('Address 1') . ':</b>' . $myrow['address1'] . '<br>
					<b>' . _('Address 2') . ':</b>' . $myrow['address2'] . '<br>
					<b>' . _('Address 3') . ':</b>' . $myrow['address3'] . '<br>
					<b>' . _('Address 4') . ':</b>' . $myrow['address4'] . '<br>
					<b>' . _('Address 5') . ':</b>' . $myrow['address5'] . '<br>
				</td>';
		echo '<td style="' . $printbk . '">
				<b>' . _('Contact') . ':</b>' . $myrow['contactname'] . '<br>
				<b>' . _('Telephone') . ':</b>' . $myrow['phoneno'] . '<br>
				<b>' . _('E-Mail') . ':</b> <br>
				<b>' . _('Fax') . ':</b> ' . $myrow['faxno'] . '<br>
			</td>
		</tr>';
	} //DB_num_rows($jobc_result) != 0

}

function GetJobCardNO($db) {
	$sql = 'SELECT MAX(id) FROM jobcards';
	$ErrMsg = _('The job cards can not be retrieved!');
	$jobc_result = DB_query($sql, $db, $ErrMsg);
	$myrow = DB_fetch_row($jobc_result);
	$ret = $myrow[0] + 1;
	return $ret;

}

?>