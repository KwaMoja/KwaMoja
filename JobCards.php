<?php

if (!isset($_GET['DebtorNo'])) {
	// Show Message Box
	echo "<script type='text/javascript'>";
	echo "location.href = 'index.php'";
	echo "</script>";
} //!isset($_GET['DebtorNo'])

if (isset($_POST['BranchNo'])) {
	$_GET['BranchNo'] = $_POST['BranchNo'];
} //isset($_POST['BranchNo'])

include('includes/session.inc');
$Title = _('Customer Job Cards');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/JobCards.inc');

if (!isset($_POST['JobCPrint'])) {
	$SQL = "SELECT name FROM debtorsmaster WHERE debtorno='" . $_GET['DebtorNo'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$CustomerName = $MyRow['name'];
	echo '<p class="page_title_text" >';
	echo '<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/customer.png" title="' . _('Job Cards for Customer') . '" alt="" />' . ' ' . _('Job Cards for Customer') . ' : ' . $_SESSION['CustomerID'] . ' - ' . $CustomerName;
	echo '</p>';
	$printbk = 'background:white;';
} //!isset($_POST['JobCPrint'])
else {
	$printbk = 'background:white;';
	echo '<br>';
}

if (!isset($_POST['SaveUpdateJob'])) {
	if ((!isset($_GET['AddJob'])) and (!isset($_POST['UpdateJob']))) {
		echo '<div class="page_help_text">' . _('To view or update a Job Card, click on the Job Card #.') . '</div><br />';

		echo '<table border="0"  width=81% style="BORDER: #a52a2a 1px solid;">
				<tr>
					<th width=10%>' . _('Job Card #') . '</th>
					<th width=10%>' . _('Invoice #') . '</th>
					<th width=10%>' . _('Creation Date') . '</th>
					<th width=10%>' . _('Completion Date') . '</th>
					<th width=50%>' . _('Job Card Description') . '</th>
					<th width=10%>' . _('Print') . '</th>
				</tr>';
		echo GetJobCards($RootPath);
		echo '</table>';
		echo '<form action="' . $_SERVER['PHP_SELF'] . '?DebtorNo=' . $_SESSION['CustomerID'] . '&BranchNo=' . $_GET['BranchNo'] . '&AddJob=1" method="post">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<div class="centre">';
		echo '<br><input type=submit name="AddJob" value="' . _('Add Job Card') . '">';
		echo '</div>';
		echo '</form>';

	} //(!isset($_GET['AddJob'])) and (!isset($_POST['UpdateJob']))
	else {
		if ((isset($_GET["SaveJob"])) and (!isset($_POST['UpdateJob']))) {
			$query = "INSERT INTO jobcards (debtorno,
											description,
											task1,
											task2,
											task3,
											task4,
											task5,
											task6,
											createdate
										) VALUES (
											'" . $_SESSION['CustomerID'] . "',
											'" . $_POST['AddJobDescription'] . "',
											'" . $_POST['AddJobTask0'] . "',
											'" . $_POST['AddJobTask1'] . "',
											'" . $_POST['AddJobTask2'] . "',
											'" . $_POST['AddJobTask3'] . "',
											'" . $_POST['AddJobTask4'] . "',
											'" . $_POST['AddJobTask5'] . "',
											'" . date('Y/m/d') . "'
											)";
			$Result = DB_query($query, $ErrMsg);

			echo '<div class="page_help_text">' . _('Record has been added.') . '<br />' . _('Please wait the browser will redirect you automatically ...') . '.</div><br />';

			// Show Message Box
			echo "<script type='text/javascript'>";
			echo "location.href = 'JobCards.php?DebtorNo=" . $_SESSION['CustomerID'] . "&BranchNo=" . $_GET['BranchNo'] . "'";
			echo "</script>";
		} //(isset($_POST["SaveJob"])) and (!isset($_POST['UpdateJob']))
		else {
			if (isset($_POST['UpdateJob'])) {
				echo '<form action="' . $_SERVER['PHP_SELF'] . '?DebtorNo=' . $_SESSION['CustomerID'] . '&BranchNo=' . $_GET['BranchNo'] . '&JobCardNo=' . $_POST['JobCardNo'] . '&SaveUpdateJob=1" method="post">';
			} //isset($_POST['UpdateJob'])
			else {
				echo '<form action="' . $_SERVER['PHP_SELF'] . '?DebtorNo=' . $_SESSION['CustomerID'] . '&BranchNo=' . $_GET['BranchNo'] . '&AddJob=1&SaveJob=1" method="post">';
			}
			echo '<table cellpadding=4 width=99% style ="outline-style:solid;outline-width:1px;' . $printbk . '">';
			echo '<tr>';
			if (isset($_POST['UpdateJob'])) {
				$jobno = $_POST['JobCardNo'];
			} //isset($_POST['UpdateJob'])
			else {
				$jobno = GetJobCardNO();
			}
			echo '<th width=50% colspan="2" style="font-size:16px;">' . _('Job Card # ' . $jobno) . '</th>';
			echo '</tr>';
			echo '<tr>
					<td colspan="2">
						<table border="0" cellpadding=0 width=100%  style ="' . $printbk . '">
							<tr>
								<td style="' . $printbk . '">
									<b>Creation Date:</b> <input type=text name="crdate" id="crdate" value="' . date('d/m/Y') . '" style="border:0;width:50%;background:none;" disabled>
								</td>
								<td style="' . $printbk . '">
									<b>Completion Date:</b> <input type=text name="codate" id="codate" value="" style="border:0;width:50%;background:none;">
								</td>
								<td style="' . $printbk . '">
									<b>Invoice #:</b> <input type=text name="inno" id="inno" value="" style="border:0;width:50%;background:none;">
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<th width=50% style="text-align:left;font-size:12px;">' . _('Customer Address') . '</th>
					<th width=50%  style="text-align:left;font-size:12px;">' . _('Customer Contact') . '</th>
				</tr>';
			echo GetDebtorInfo($printbk, $_GET['DebtorNo'], $_GET['BranchNo']);
			echo '<tr>
					<th width=100% colspan="2" style="font-size:14px;">' . _('Job Description') . '</th>
				</tr>
				<tr>
					<th width=100% colspan="2" style="font-size:14px;' . $printbk . '">
						<input type=text name="AddJobDescription" id="JobDescription" value="' . _('Job Description') . '" style="width:100%;border:1px dotted #7f7f7f;background:none;">
					</th>
				</tr>
				<tr>
					<th width=50% style="text-align:left;font-size:12px;">' . _('Task') . '</th>
					<th width=50%  style="text-align:left;font-size:12px;">' . _('Action taken') . '</th>
				</tr>';
			$c = 0;
			while ($c < 6) {
				echo '<tr>
						<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:0px 0px 1px 0px;">
							<input type=text id="task' . $c . '" name="AddJobTask' . $c . '" value="" style="height:20px;width:100%;border:1px dotted #7f7f7f;background:none;">
						</td>
						<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:0px 0px 1px 0px;">&nbsp;</td>
					</tr>';
				$c++;
			} //$c < 6
			echo '<tr>
					<th width=90% colspan="2" style="font-size:14px;">' . _('Items Used') . '</th>
				</tr>
				<tr>
					<td colspan="2">
						<table cellpadding=0 cellspacing=0 width=100%>
							<tr>
								<th width=50%  style="text-align:left;font-size:12px;">' . _('Description') . '</th>
								<th width=10% style="text-align:left;font-size:12px;">' . _('Doc #') . '</th>
								<th width=10% style="text-align:left;font-size:12px;">' . _('QTY') . '</th>
								<th width=15% style="text-align:left;font-size:12px;">' . _('Unit Price N$') . '</th>
								<th width=15%  style="text-align:left;font-size:12px;">' . _('Amount N$') . '</th>
							</tr>
							<tr>
								<td style="line-height:20px;border-style:solid;' . $printbk . 'border-width:1px 1px 1px 0px;">&nbsp;</td>
								<td style="line-height:20px;border-style:solid;' . $printbk . 'border-width:1px 1px 1px 0px;">&nbsp;</td>
								<td style="line-height:20px;border-style:solid;' . $printbk . 'border-width:1px 1px 1px 0px;">&nbsp;</td>
								<td style="line-height:20px;border-style:solid;' . $printbk . 'border-width:1px 1px 1px 0px;">&nbsp;</td>
								<td style="line-height:20px;border-style:solid;' . $printbk . 'border-width:1px 1px 1px 0px;">&nbsp;</td>
							</tr>';
			$c = 0;
			while ($c < 14) {
				echo '<tr>
						<td style="line-height:20px;border-style:solid;' . $printbk . 'border-width:0px 1px 1px 0px;">&nbsp;</td>
						<td style="line-height:20px;border-style:solid;' . $printbk . 'border-width:0px 1px 1px 0px;">&nbsp;</td>
						<td style="line-height:20px;border-style:solid;' . $printbk . 'border-width:0px 1px 1px 0px;">&nbsp;</td>
						<td style="line-height:20px;border-style:solid;' . $printbk . 'border-width:0px 1px 1px 0px;">&nbsp;</td>
						<td style="line-height:20px;border-style:solid;' . $printbk . 'border-width:0px 1px 1px 0px;">&nbsp;</td>
					</tr>';
				$c++;
			} //$c < 14
			echo '</table>
				</td>
			</tr>';
			echo '<tr>
					<th width=100% colspan="2" style="font-size:14px;">' . _('Labour') . '</th>
				</tr>
				<tr>
					<td colspan="2">
						<table cellpadding=0 cellspacing=0 width=100%>
							<tr>
								<th width=30%  style="text-align:left;font-size:12px;">' . _('Technician') . '</th>
								<th width=10% style="text-align:left;font-size:12px;">' . _('Date') . '</th>
								<th width=10% style="text-align:left;font-size:12px;">' . _('Hours') . '</th>
								<th width=10% style="text-align:left;font-size:12px;">' . _('Hour Rate') . '</th>
								<th width=10% style="text-align:left;font-size:12px;">' . _('Travel') . '</th>
								<th width=15% style="text-align:left;font-size:12px;">' . _('Travel Rate') . '</th>
								<th width=15% style="text-align:left;font-size:12px;">' . _('Total Amount N$') . '</th>
							</tr>
							<tr>
								<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:1px 1px 1px 1px;">&nbsp;</td>';
			echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:1px 1px 1px 0px;">&nbsp;</td>';
			echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:1px 1px 1px 0px;">&nbsp;</td>';
			echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:1px 1px 1px 0px;">&nbsp;</td>';
			echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:1px 1px 1px 0px;">&nbsp;</td>';
			echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:1px 1px 1px 0px;">&nbsp;</td>';
			echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:1px 1px 1px 0px;">&nbsp;</td>';
			echo '</tr>';
			$c = 0;
			while ($c < 4) {
				echo '<tr>';
				echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:0px 1px 1px 1px;">&nbsp;</td>';
				echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:0px 1px 1px 0px;">&nbsp;</td>';
				echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:0px 1px 1px 0px;">&nbsp;</td>';
				echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:0px 1px 1px 0px;">&nbsp;</td>';
				echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:0px 1px 1px 0px;">&nbsp;</td>';
				echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:0px 1px 1px 0px;">&nbsp;</td>';
				echo '<td style="line-height:20px;' . $printbk . 'border-style:solid; border-width:0px 1px 1px 0px;">&nbsp;</td>';
				echo '</tr>';
				$c++;
			} //$c < 4
			echo '</table>';
			echo '</td>';
			echo '</tr>';
			echo '</table>';

			if (isset($_POST['UpdateJob'])) {
				//Get Values
				$SQL = 'SELECT id,debtorno,description,task1,task2,task3,task4,task5,task6, DATE_FORMAT(CreateDate, "%d/%m/%Y") as CreateDate,
						DATE_FORMAT(CompleteDate, "%d/%m/%Y") as CompleteDate, Invoice from jobcards where debtorno="' . $_SESSION['CustomerID'] . '" and id=' . $_POST['JobCardNo'];
				$ErrMsg = _('The job cards can not be retrieved!');
				$job_update = DB_query($SQL, $ErrMsg);
				$MyRow = DB_fetch_row($job_update);

				//Set Values
				echo "<script type='text/javascript'>";
				echo "document.getElementById('crdate').value ='" . $MyRow[9] . "';";
				echo "document.getElementById('codate').value = '" . $MyRow[10] . "';";
				echo "document.getElementById('inno').value = '" . $MyRow[11] . "';";
				echo "document.getElementById('JobDescription').value = '" . $MyRow[2] . "';";
				echo "document.getElementById('task0').value = '" . $MyRow[3] . "';";
				echo "document.getElementById('task1').value = '" . $MyRow[4] . "';";
				echo "document.getElementById('task2').value = '" . $MyRow[5] . "';";
				echo "document.getElementById('task3').value = '" . $MyRow[6] . "';";
				echo "document.getElementById('task4').value = '" . $MyRow[7] . "';";
				echo "document.getElementById('task5').value = '" . $MyRow[8] . "';";
				echo "</script>";

				if (!isset($_POST['JobCPrint'])) {
					echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
					echo '<br><div class="centre"><input type=submit name="UpdateJob" value="' . _('Update Job Card') . '">';
					echo '</form>';
					echo '<a href="JobCards.php?DebtorNo=' . urlencode($_SESSION['CustomerID']) . '&BranchNo=' . urlencode($_GET['BranchNo']) . '"><input type=button value="Close"></a></div>';
				} else {
					echo '<br>';
				}

			} else {
				echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
				echo '<br><div class="centre"><input type=submit name="SaveJob" value="' . _('Save Job Card') . '">';
				echo '</form>';
				echo '<a href="JobCards.php?DebtorNo=' . urlencode($_SESSION['CustomerID']) . '&BranchNo=' . urlencode($_GET['BranchNo']) . '"><input type=button value="Close"></a></div>';
			}
		}
	}
} else {
	// Insert new Account in DB
	$DBValues = 'Description="' . $_POST["AddJobDescription"] . '",';
	$DBValues = $DBValues . 'CompleteDate=DATE_FORMAT(STR_TO_DATE("' . $_POST["codate"] . '", "%d/%m/%Y"), "%Y/%m/%d"),';
	$DBValues = $DBValues . 'Invoice="' . $_POST["inno"] . '",';
	$DBValues = $DBValues . 'task1="' . $_POST["AddJobTask0"] . '",task2="' . $_POST["AddJobTask1"] . '",';
	$DBValues = $DBValues . 'task3="' . $_POST["AddJobTask2"] . '",task4="' . $_POST["AddJobTask3"] . '",';
	$DBValues = $DBValues . 'task5="' . $_POST["AddJobTask4"] . '",task6="' . $_POST["AddJobTask5"] . '"';

	$query = "Update jobcards SET " . $DBValues . " where id=" . $_POST['JobCardNo'];
	$Result = DB_query($query, $ErrMsg);

	echo '<div class="page_help_text">' . _('Record has been updated.') . '<br />' . _('Please wait the browser will redirect you automatically ...') . '.</div><br />';

	// Show Message Box
	echo "<script type='text/javascript'>";
	echo "location.href = 'JobCards.php?DebtorNo=" . $_SESSION['CustomerID'] . '&BranchNo=' . $_GET['BranchNo'] . "'";
	echo "</script>";
}
include('includes/footer.inc');

?>