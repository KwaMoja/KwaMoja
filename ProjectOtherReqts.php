<?php
$PageSecurity=1;
include('includes/DefineProjectClass.php');

include('includes/session.inc');
$Title = _('Project Other Requirements');

$Identifier = $_GET['identifier'];

/* If a project header doesn't exist, then go to
 * Projects.php to create one
 */

if (!isset($_SESSION['Project' . $Identifier])) {
	header('Location:' . $RootPath . '/Projects.php');
	exit;
}
$ViewTopic = 'Projects';
$BookMark = 'AddToProject';
include('includes/header.inc');


if (isset($_POST['UpdateLines']) or isset($_POST['BackToHeader'])) {
	if ($_SESSION['Project' . $Identifier]->Status != 2) { //dont do anything if the customer has committed to the project
		foreach ($_SESSION['Project' . $Identifier]->ProjectReqts as $ProjectComponentID => $ProjectRequirementItem) {

			if (filter_number_format($_POST['Qty' . $ProjectComponentID]) == 0) {
				//this is the same as deleting the line - so delete it
				$_SESSION['Project' . $Identifier]->Remove_ProjectRequirement($ProjectComponentID);
			} else {
				$_SESSION['Project' . $Identifier]->ProjectReqts[$ProjectComponentID]->Quantity = filter_number_format($_POST['Qty' . $ProjectComponentID]);
				$_SESSION['Project' . $Identifier]->ProjectReqts[$ProjectComponentID]->CostPerUnit = filter_number_format($_POST['CostPerUnit' . $ProjectComponentID]);
				$_SESSION['Project' . $Identifier]->ProjectReqts[$ProjectComponentID]->Requirement = $_POST['Requirement' . $ProjectComponentID];
			}
		} // end loop around the items on the project requirements array
	} // end if the project is not currently committed to by the customer
} // end if the user has hit the update lines or back to header buttons


if (isset($_POST['BackToHeader'])) {
	echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/Projects.php?identifier=' . $Identifier . '" />';
	echo '<br />';
	prnMsg(_('You should automatically be forwarded to the Project page. If this does not happen perhaps the browser does not support META Refresh') . '<a href="' . $RootPath . '/Projects.php?identifier=' . urlencode($Identifier) . '">' . _('click here') . '</a> ' . _('to continue'), 'info');
	include('includes/footer.inc');
	exit;
}


if (isset($_GET['Delete'])) {
	if ($_SESSION['Project' . $Identifier]->Status != 2) {
		$_SESSION['Project' . $Identifier]->Remove_ProjectRequirement($_GET['Delete']);
	} else {
		prnMsg(_('The other project requirements cannot be altered because the customer has already placed the order'), 'warn');
	}
}
if (isset($_POST['EnterNewRequirement'])) {
	$InputError = false;
	if (!is_numeric(filter_number_format($_POST['Quantity']))) {
		prnMsg(_('The quantity of the new requirement is expected to be numeric'), 'error');
		$InputError = true;
	}
	if (!is_numeric(filter_number_format($_POST['CostPerUnit']))) {
		prnMsg(_('The cost per unit of the new requirement is expected to be numeric'), 'error');
		$InputError = true;
	}
	if (!$InputError) {
		$_SESSION['Project' . $Identifier]->Add_To_ProjectRequirements($_POST['RequirementDescription'], filter_number_format($_POST['Quantity']), filter_number_format($_POST['CostPerUnit']));
		unset($_POST['RequirementDescription']);
		unset($_POST['Quantity']);
		unset($_POST['CostPerUnit']);
	}
}

/* This is where the other requirement as entered/modified should be displayed reflecting any deletions or insertions*/

echo '<form name="ProjectReqtsForm" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $Identifier . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/project.png" title="' . _('Project Other Requirements') . '" alt="" />  ' . _('Project Other Requirements') . ' - ' . $_SESSION['Project' . $Identifier]->DonorName . '</p>';

if (count($_SESSION['Project' . $Identifier]->ProjectReqts) > 0) {

	echo '<table class="selection">';

	if (isset($_SESSION['Project' . $Identifier]->ProjectRef)) {
		echo '<tr>
				<th colspan="5">' . _('Project Reference') . ': ' . $_SESSION['Project' . $Identifier]->ProjectRef . '</th>
			</tr>';
	}

	echo '<tr>
			<th>' . _('Description') . '</th>
			<th>' . _('Quantity') . '</th>
			<th>' . _('Unit Cost') . '</th>
			<th>' . _('Sub-total') . '</th>
		</tr>';

	$_SESSION['Project' . $Identifier]->total = 0;
	$k = 0; //row colour counter
	$TotalCost = 0;
	foreach ($_SESSION['Project' . $Identifier]->ProjectReqts as $ProjectReqtID => $ProjectComponent) {

		$LineTotal = $ProjectComponent->Quantity * $ProjectComponent->CostPerUnit;
		$DisplayLineTotal = locale_number_format($LineTotal, $_SESSION['CompanyRecord']['decimalplaces']);

		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}

		echo '<td><textarea name="Requirement' . $ProjectReqtID . '" required="required" autofocus="autofocus" cols="30" rows="3">' . $ProjectComponent->Requirement . '</textarea></td>
			  <td><input type="text" class="number" maxlength="11" required="required" name="Qty' . $ProjectReqtID . '" size="11" value="' . locale_number_format($ProjectComponent->Quantity, 'Variable') . '" /></td>
			  <td><input type="text" class="number" maxlength="11" required="required" name="CostPerUnit' . $ProjectReqtID . '" size="11" value="' . locale_number_format($ProjectComponent->CostPerUnit, $_SESSION['CompanyRecord']['decimalplaces']) . '" /></td>
			  <td class="number">' . $DisplayLineTotal . '</td>
			  <td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $Identifier . '&amp;Delete=' . $ProjectReqtID . '" onclick="return MakeConfirm(\'' . _('Are you sure you wish to delete this project requirement?') . '\', \'Confirm Delete\', this);">' . _('Delete') . '</a></td>
			  </tr>';
		$TotalCost += $LineTotal;
	}

	$DisplayTotal = locale_number_format($TotalCost, $_SESSION['CompanyRecord']['decimalplaces']);
	echo '<tr>
			<td colspan="4" class="number">' . _('Total Other Requirements Cost') . '</td>
			<td class="number"><b>' . $DisplayTotal . '</b></td>
		</tr>
		</table>
		<br />
		<div class="centre">
			<input type="submit" name="UpdateLines" value="' . _('Update Other Requirements Lines') . '" />
			<input type="submit" name="BackToHeader" value="' . _('Back To Project Header') . '" />
		</div>
	</form>';

}
/*Only display the project other requirements lines if there are any !! */

echo '<br />';
/*Now show  form to add new requirements to the project */
if (!isset($_POST['RequirementDescription'])) {
	$_POST['RequirementDescription'] = '';
	$_POST['Quantity'] = 0;
	$_POST['CostPerUnit'] = 0;
}
echo '<form name="ProjectReqtsForm" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $Identifier . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="selection">
		<tr>
			<th colspan="2">' . _('Enter New Requirements') . '</th>
		</tr>
		<tr>
			<td>' . _('Requirement Description') . '</td>
			<td><textarea name="RequirementDescription" required="required" autofocus="autofocus" cols="30" rows="3">' . $_POST['RequirementDescription'] . '</textarea></td>
		</tr>
		<tr>
			<td>' . _('Quantity Required') . ':</td>
			<td><input type="text" class="number" name="Quantity" size="10" required="required" maxlength="10" value="' . $_POST['Quantity'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Cost Per Unit') . ':</td>
			<td><input type="text" class="number" name="CostPerUnit" size="10" required="required" maxlength="10" value="' . $_POST['CostPerUnit'] . '" /></td>
		</tr>

		</table>

		<br />
		<div class="centre">
			<input type="submit" name="EnterNewRequirement" value="' . _('Enter New Project Requirement') . '" />
		</div>
		</form>';

include('includes/footer.inc');
?>