<?php

/* Session started in session.inc for password checking and authorisation level check
config.php is in turn included in session.inc $PageSecurity now comes from session.inc (and gets read in by GetConfig.php*/

include('includes/session.inc');
echo '<script type="text/javascript" src = "' . $RootPath . '/javascripts/AjaxFunctions.js"></script>';
$Title = _('Search for a Customer');

if (isset($_POST['DebtorNo'])) {
	$_SESSION['DebtorNo'] = $_POST['DebtorNo'];
}

if (isset($_POST['BranchNo'])) {
	$_SESSION['BranchNo'] = $_POST['BranchNo'];
}

if (isset($_GET['SingleOption'])) {
	$_SESSION['SingleOption'] = $_GET['SingleOption'];
}

if (isset($_GET['Update']) and $_GET['Update'] == 'Customers') {
	CustomerBox($_POST['PartialCode'], $_POST['PartialName'], $_POST['PartialAddress']);
	exit;
}

if (isset($_GET['Update']) and $_GET['Update'] == 'Branches') {
	BranchBox($_POST['DebtorNo']);
	exit;
}

if (isset($_GET['Update']) and $_GET['Update'] == 'Details') {
	ShowOptionLinks($_SESSION['DebtorNo'], $_SESSION['BranchNo'], $_SESSION['SingleOption']);
	exit;
}
include('includes/header.inc');

unset($_SESSION['DebtorNo']);
unset($_SESSION['BranchNo']);

echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Search for a Customer') . '<br /></p>';

/* Container box to hold three separate boxes horizontally */
echo '<div class="container">';

/* First box contains the input of the criteria to search for customers */
echo '<form method="post" name="CustDetails" onSubmit="return SubmitForm(this, \'customers\');" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Update=Customers">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<div class="box">
			<input type="submit" name="SubmitCustDetails" style="display: none;" />
			<div class="box_header">' . _('Search Criteria') . '</div>

			<label class="box">' . _('Enter Partial Code') . '</label>
			<input class="box" type="text" name="PartialCode" onKeyUp="ReloadForm(SubmitCustDetails)" />

			<label class="box">' . _('Enter Partial Name') . '</label>
			<input class="box" type="text" name="PartialName" onKeyUp="ReloadForm(SubmitCustDetails)" />

			<label class="box">' . _('Enter Partial Address') . '</label>
			<input class="box" type="text" name="PartialAddress" onKeyUp="ReloadForm(SubmitCustDetails)" />

		</div>
	</form>';
/* End of the first box */

/* Second box contains a list of the top 15 customers fitting the criteria in box 1 */
echo '<form method="post" name="BranchDetails" onSubmit="return SubmitForm(this, \'branches\');" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Update=Branches">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<div class="box">
			<input type="submit" name="SubmitBranchDetails" style="display: none;" />
			<div class="box_header">' . _('Select a Customer') . '</div>
			<select size="15" class="box bloc" id="customers" name="DebtorNo" onChange="ReloadForm(SubmitBranchDetails)">';
CustomerBox();
echo '</select>
		</div>
	</form>';
/* End of the second box */

/* Third box contains a list of the branches belonging to the customer selected in box 2 */
echo '<form method="post" name="Details" onSubmit="return SubmitForm(this, \'options\');" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Update=Details">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<div class="box">
			<input type="submit" name="SubmitAllDetails" style="display: none;" />
			<div class="box_header">' . _('Select a Branch') . '</div>
			<select size="15" class="box bloc" id="branches" name="BranchNo" onChange="ReloadForm(SubmitAllDetails)">';
BranchBox();
echo '</select>
		</div>
	</form>';
/* End of third box */

/* Close the container box to hold the three horizontal boxes */
echo '</div>';

if (!isset($_SESSION['BranchNo'])) {
	echo '<div style="width: 20%; margin-top: 1%;" class="box">
			<div class="box_header">' . _('Options') . '</div>
				<div id="options">';
	if (isset($_SESSION['DebtorNo'])) {
		ShowOptionLinks($_SESSION['DebtorNo'], $_SESSION['BranchNo'], $_SESSION['SingleOption']);
	}
	echo '</div>
		</div>';
}

include('includes/footer.inc');

function CustomerBox($PartialCode = '', $PartialName = '', $PartialAddress = '') {
	if ($PartialCode == '') {
		$PartialCode = '%';
	} else {
		$PartialCode = '%' . $PartialCode . '%';
	}
	if ($PartialName == '') {
		$PartialName = '%';
	} else {
		$PartialName = '%' . $PartialName . '%';
	}
	if ($PartialAddress == '') {
		$PartialAddress = '%';
	} else {
		$PartialAddress = '%' . $PartialAddress . '%';
	}
	$CustomerSQL = "SELECT debtorno,
							name
						FROM debtorsmaster
						WHERE name LIKE '" . $PartialName . "'
							AND debtorno LIKE '" . $PartialCode . "'
							AND (address1 LIKE '" . $PartialAddress . "'
								OR address2 LIKE '" . $PartialAddress . "'
								OR address3 LIKE '" . $PartialAddress . "'
								OR address4 LIKE '" . $PartialAddress . "'
								OR address5 LIKE '" . $PartialAddress . "'
								OR address6 LIKE '" . $PartialAddress . "')
						LIMIT 15";

	$CustomerResult = DB_query($CustomerSQL);

	while ($MyCustomerRow = DB_fetch_array($CustomerResult)) {
		echo '<option value="' . $MyCustomerRow['debtorno'] . '">' . $MyCustomerRow['debtorno'] . ' - ' . $MyCustomerRow['name'] . '</option>';
	}

}

function BranchBox($DebtorNo = '') {
	if ($DebtorNo != '') {
		$BranchSQL = "SELECT branchcode,
							brname
						FROM custbranch
						WHERE debtorno='" . $DebtorNo . "'";
		$BranchResult = DB_query($BranchSQL);
		while ($MyBranchRow = DB_fetch_array($BranchResult)) {
			echo '<option value="' . $MyBranchRow['branchcode'] . '">' . $MyBranchRow['branchcode'] . ' - ' . $MyBranchRow['brname'] . '</option>';
		}
	}
}

function ShowOptionLinks($DebtorNo, $BranchNo, $SingleOption = '') {
	$Scripts = array();
	$Scripts['SelectOrderItems.php'] = _('Enter An Order or Quotation');
	switch ($SingleOption) {
		case 'QuickInvoice.php':
			if (in_array($_SESSION['PageSecurityArray'][$SingleOption], $_SESSION['AllowedPageSecurityTokens'])) {
				echo '<div style="text-align: left;margin: 1%;"><a href="' . $SingleOption . '?DebtorNo=' . $DebtorNo . '&BranchNo=' . $BranchNo . '">' . _('Raise a Quick Invoice') . '</a></div>';
			}
			break;
		default:
			foreach ($Scripts as $Script => $Caption) {
				if (in_array($_SESSION['PageSecurityArray'][$Script], $_SESSION['AllowedPageSecurityTokens'])) {
					echo '<div style="text-align: left;margin: 1%;"><a href="' . $Script . '?DebtorNo=' . $DebtorNo . '&BranchNo=' . $BranchNo . '">' . $Caption . '</a></div>';
				}
			}
	}
}

?>