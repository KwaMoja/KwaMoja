<?php

include('includes/session.inc');
$Title = _('Select Project');
$ViewTopic = 'Projects';
$BookMark = 'SelectProject';
include('includes/header.inc');

echo '<div class="toplink">
		<a href="' . $RootPath . '/Projects.php">' . _('Create a New Project') . '</a>
	</div>';
echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/contract.png" title="' . _('Projects') . '" alt="" />' . ' ' . _('Select A Project') . '</p> ';
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($_GET['ProjectRef'])) {
	$_POST['ProjectRef'] = $_GET['ProjectRef'];
}
if (isset($_GET['SelectedDonor'])) {
	$_POST['SelectedDonor'] = $_GET['SelectedDonor'];
}


if (isset($_POST['ProjectRef']) and $_POST['ProjectRef'] != '') {
	$_POST['ProjectRef'] = trim($_POST['ProjectRef']);
	echo _('Project Reference') . ' - ' . $_POST['ProjectRef'];
} else {
	if (isset($_POST['SelectedDonor'])) {
		echo _('For customer') . ': ' . $_POST['SelectedDonor'] . ' ' . _('and') . ' ';
		echo '<input type="hidden" name="SelectedDonor" value="' . $_POST['SelectedDonor'] . '" />';
	}
}

if (!isset($_POST['ProjectRef']) or $_POST['ProjectRef'] == '') {

	echo _('Project Reference') . ': <input type="text" name="ProjectRef" maxlength="20" size="20" />&nbsp;&nbsp;';
	echo '<select name="Status">';

	if (isset($_GET['Status'])) {
		$_POST['Status'] = $_GET['Status'];
	}
	if (!isset($_POST['Status'])) {
		$_POST['Status'] = 4;
	}

	$Statuses[] = _('Budget Request Entered');
	$Statuses[] = _('Quoted - No Order Placed');
	$Statuses[] = _('Order Placed');
	$Statuses[] = _('Completed');
	$Statuses[] = _('All Projects');

	$StatusCount = count($Statuses);

	for ($i = 0; $i < $StatusCount; $i++) {
		if ($i == $_POST['Status']) {
			echo '<option selected="selected" value="' . $i . '">' . $Statuses[$i] . '</option>';
		} else {
			echo '<option value="' . $i . '">' . $Statuses[$i] . '</option>';
		}
	}

	echo '</select> &nbsp;&nbsp;';
}
echo '<input type="submit" name="SearchProjects" value="' . _('Search') . '" />';

//figure out the SQL required from the inputs available

if (isset($_POST['ProjectRef']) and $_POST['ProjectRef'] != '') {
	$SQL = "SELECT projectref,
				   projectdescription,
				   categoryid,
				   projects.donorno,
				   donors.name AS customername,
				   status,
				   wo,
				   customerref,
				   requireddate
				FROM projects
				INNER JOIN donors
					ON projects.donorno = donors.donorno
				INNER JOIN locationusers
					ON locationusers.loccode=projects.loccode
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				WHERE projectref " . LIKE . " '%" . $_POST['ProjectRef'] . "%'";

} else { //projectref not selected
	if (isset($_POST['SelectedDonor'])) {

		$SQL = "SELECT projectref,
					   projectdescription,
					   categoryid,
					   projects.donorno,
					   donors.name AS customername,
					   status,
					   wo,
					   customerref,
					   requireddate
				FROM projects
				INNER JOIN donors
					ON projects.donorno = donors.donorno
				INNER JOIN locationusers
					ON locationusers.loccode=projects.loccode
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				WHERE donorno='" . $_POST['SelectedDonor'] . "'";
		if ($_POST['Status'] != 4) {
			$SQL .= " AND status='" . $_POST['Status'] . "'";
		}
	} else { //no customer selected
		$SQL = "SELECT projectref,
					   projectdescription,
					   categoryid,
					   projects.donorno,
					   donors.name AS customername,
					   status,
					   wo,
					   customerref,
					   requireddate
				FROM projects
				INNER JOIN donors
					ON projects.donorno = donors.donorno
				INNER JOIN locationusers
					ON locationusers.loccode=projects.loccode
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canview=1";
		if ($_POST['Status'] != 4) {
			$SQL .= " AND status='" . $_POST['Status'] . "'";
		}
	}
} //end not project ref selected

$ErrMsg = _('No projects were returned by the SQL because');
$ProjectsResult = DB_query($SQL, $ErrMsg);

/*show a table of the projects returned by the SQL */

echo '<table cellpadding="2" width="98%" class="selection">
		<thead>
			<tr>
				<th>' . _('Modify') . '</th>
				<th class="SortedColumn">' . _('Order') . '</th>
				<th>' . _('Issue To WO') . '</th>
				<th>' . _('Costing') . '</th>
				<th class="SortedColumn">' . _('Project Ref') . '</th>
				<th>' . _('Description') . '</th>
				<th>' . _('Customer') . '</th>
				<th>' . _('Required Date') . '</th>
			</tr>
		</thead>';

$k = 0; //row colour counter
echo '<tbody>';
while ($MyRow = DB_fetch_array($ProjectsResult)) {
	if ($k == 1) {
		echo '<tr class="EvenTableRows">';
		$k = 0;
	} else {
		echo '<tr class="OddTableRows">';
		++$k;
	}

	$ModifyPage = $RootPath . '/Projects.php?ModifyProjectRef=' . $MyRow['projectref'];
	$IssueToWOPage = $RootPath . '/WorkOrderIssue.php?WO=' . $MyRow['wo'] . '&amp;StockID=' . $MyRow['projectref'];
	$CostingPage = $RootPath . '/ProjectCosting.php?SelectedProject=' . $MyRow['projectref'];
	$FormatedRequiredDate = ConvertSQLDate($MyRow['requireddate']);

	if ($MyRow['status'] == 0 or $MyRow['status'] == 1) { //still setting up the project
		echo '<td><a href="' . $ModifyPage . '">' . _('Modify') . '</a></td>';
	} else {
		echo '<td>' . _('n/a') . '</td>';
	}
	if ($MyRow['status'] == 1 or $MyRow['status'] == 2) { // quoted or ordered
		echo '<td><a href="' . $OrderModifyPage . '">' . $MyRow['orderno'] . '</a></td>';
	} else {
		echo '<td>' . _('n/a') . '</td>';
	}
	if ($MyRow['status'] == 2) { //the customer has accepted the quote but not completed project yet
		echo '<td><a href="' . $IssueToWOPage . '">' . $MyRow['wo'] . '</a></td>';
	} else {
		echo '<td>' . _('n/a') . '</td>';
	}
	if ($MyRow['status'] == 2 or $MyRow['status'] == 3) {
		echo '<td><a href="' . $CostingPage . '">' . _('View') . '</a></td>';
	} else {
		echo '<td>' . _('n/a') . '</td>';
	}
	echo '<td>' . $MyRow['projectref'] . '</td>
		  <td>' . $MyRow['projectdescription'] . '</td>
		  <td>' . $MyRow['customername'] . '</td>
		  <td>' . $FormatedRequiredDate . '</td></tr>';

}
//end of while loop

echo '</tbody>
	</table>
</form>';
include('includes/footer.inc');
?>