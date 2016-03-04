<?php

include('includes/session.inc');
$Title = _('Sales GL Postings Set Up');
$ViewTopic = 'CreatingNewSystem';
$BookMark = 'SalesGLPostings';
include('includes/header.inc');

if (isset($_GET['SelectedSalesPostingID'])) {
	$SelectedSalesPostingID = $_GET['SelectedSalesPostingID'];
} elseif (isset($_POST['SelectedSalesPostingID'])) {
	$SelectedSalesPostingID = $_POST['SelectedSalesPostingID'];
}

$InputError = false;

echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/customer.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['submit'])) {

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	if (isset($SelectedSalesPostingID)) {

		/*SelectedSalesPostingID could also exist if submit had not been clicked this		code would not run in this case cos submit is false of course	see the delete code below*/

		$SQL = "UPDATE salesglpostings SET salesglcode = '" . $_POST['SalesGLCode'] . "',
										discountglcode = '" . $_POST['DiscountGLCode'] . "',
										area = '" . $_POST['Area'] . "',
										stkcat = '" . $_POST['StkCat'] . "',
										salestype = '" . $_POST['SalesType'] . "'
				WHERE salesglpostings.id = '" . $SelectedSalesPostingID . "'";
		$Msg = _('The sales GL posting record has been updated');
	} else {

		/*Selected Sales GL Posting is null cos no item selected on first time round so must be	adding a record must be submitting new entries in the new SalesGLPosting form */

		/* Verify if item doesn't exists to insert it, otherwise just refreshes the page. */
		$SQL = "SELECT count(*) FROM salesglpostings
				WHERE area='" . $_POST['Area'] . "'
				AND stkcat='" . $_POST['StkCat'] . "'
				AND salestype='" . $_POST['SalesType'] . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0] == 0) {
			$SQL = "INSERT INTO salesglpostings (
						salesglcode,
						discountglcode,
						area,
						stkcat,
						salestype)
					VALUES (
						'" . $_POST['SalesGLCode'] . "',
						'" . $_POST['DiscountGLCode'] . "',
						'" . $_POST['Area'] . "',
						'" . $_POST['StkCat'] . "',
						'" . $_POST['SalesType'] . "'
						)";
			$Msg = _('The new sales GL posting record has been inserted');
		} else {
			prnMsg(_('A sales gl posting account already exists for the selected area, stock category, salestype'), 'warn');
			$InputError = true;
		}
	}
	//run the SQL from either of the above possibilites

	$Result = DB_query($SQL);

	if ($InputError == false) {
		prnMsg($Msg, 'success');
	}
	unset($SelectedSalesPostingID);
	unset($_POST['SalesGLCode']);
	unset($_POST['DiscountGLCode']);
	unset($_POST['Area']);
	unset($_POST['StkCat']);
	unset($_POST['SalesType']);

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button

	$SQL = "DELETE FROM salesglpostings WHERE id='" . $SelectedSalesPostingID . "'";

	$Result = DB_query($SQL);

	prnMsg(_('Sales posting record has been deleted'), 'success');
}

$SQL = "SELECT chartmaster.accountcode,
			chartmaster.accountname
		FROM chartmaster
		INNER JOIN accountgroups
			ON chartmaster.groupcode=accountgroups.groupcode
			AND chartmaster.language=accountgroups.language
		WHERE chartmaster.group_=accountgroups.groupname
			AND accountgroups.pandl='1'
			AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'
		ORDER BY accountgroups.sequenceintb,
			chartmaster.accountcode";
$Result = DB_query($SQL);
while ($MyRow = DB_fetch_array($Result)) {
	$PossibleGLCodes[$MyRow['accountcode']] = $MyRow['accountname'];
}

if (!isset($SelectedSalesPostingID)) {

	$ShowLivePostingRecords = true;

	if ($ShowLivePostingRecords) {

		$SQL = "SELECT salesglpostings.id,
				salesglpostings.area,
				salesglpostings.stkcat,
				salesglpostings.salestype,
				salesglpostings.salesglcode,
				salesglpostings.discountglcode
			FROM salesglpostings
			ORDER BY salesglpostings.area,
					salesglpostings.stkcat,
					salesglpostings.salestype";

		$Result = DB_query($SQL);

		echo '<table class="selection">
				<tr>
				<th>' . _('Area') . '</th>
				<th>' . _('Stock Category') . '</th>
				<th>' . _('Sales Type') . '</th>
				<th>' . _('Sales Account') . '</th>
				<th>' . _('Discount Account') . '</th>
			</tr>';

		$k = 0; //row colour counter

		while ($MyRow = DB_fetch_row($Result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}

			echo '<td>', $MyRow[1], '</td>
				<td>', $MyRow[2], '</td>
				<td>', $MyRow[3], '</td>
				<td>', htmlspecialchars($PossibleGLCodes[$MyRow[4]], ENT_QUOTES, 'UTF-8'), '</td>
				<td>', htmlspecialchars($PossibleGLCodes[$MyRow[5]], ENT_QUOTES, 'UTF-8'), '</td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?', 'SelectedSalesPostingID=', $MyRow[0], '">' . _('Edit') . '</a></td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?', 'SelectedSalesPostingID=', $MyRow[0], '&amp;delete=yes" onclick="return MakeConfirm(\'' . _('Are you sure you wish to delete this sales GL posting record?') . '\', \'Confirm Delete\', this);">' . _('Delete') . '</a></td>
			</tr>';
		}
		//END WHILE LIST LOOP
		echo '</table>';
	}
}

//end of ifs and buts!

if (isset($SelectedSalesPostingID)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Show All Sales Posting Codes Defined') . '</a></div>';
}


if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedSalesPostingID)) {
		//editing an existing sales posting record

		$SQL = "SELECT salesglpostings.stkcat,
				salesglpostings.salesglcode,
				salesglpostings.discountglcode,
				salesglpostings.area,
				salesglpostings.salestype
			FROM salesglpostings
			WHERE salesglpostings.id='" . $SelectedSalesPostingID . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['SalesGLCode'] = $MyRow['salesglcode'];
		$_POST['DiscountGLCode'] = $MyRow['discountglcode'];
		$_POST['Area'] = $MyRow['area'];
		$_POST['StkCat'] = $MyRow['stkcat'];
		$_POST['SalesType'] = $MyRow['salestype'];
		DB_free_result($Result);

		echo '<input type="hidden" name="SelectedSalesPostingID" value="' . $SelectedSalesPostingID . '" />';

	}
	/*end of if $SelectedSalesPostingID only do the else when a new record is being entered */

	$SQL = "SELECT areacode,
			areadescription FROM areas";
	$Result = DB_query($SQL);

	echo '<br /><table class="selection">
		<tr>
			<td>' . _('Area') . ':</td>
			<td>
				<select required="required" name="Area">
					<option value="AN">' . _('Any Other') . '</option>';

	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['Area']) and $MyRow['areacode'] == $_POST['Area']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $MyRow['areacode'] . '">' . $MyRow['areadescription'] . '</option>';

	} //end while loop

	DB_free_result($Result);

	$SQL = "SELECT categoryid, categorydescription FROM stockcategory";
	$Result = DB_query($SQL);

	echo '</select></td></tr>';


	echo '<tr>
			<td>' . _('Stock Category') . ':</td>
			<td>
				<select required="required" name="StkCat">
					<option value="ANY">' . _('Any Other') . '</option>';

	while ($MyRow = DB_fetch_array($Result)) {

		if (isset($_POST['StkCat']) and $MyRow['categoryid'] == $_POST['StkCat']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';

	} //end while loop

	echo '</select></td></tr>';


	DB_free_result($Result);

	$SQL = "SELECT typeabbrev,
					sales_type
			FROM salestypes";
	$Result = DB_query($SQL);


	echo '<tr>
			<td>' . _('Sales Type') . ' / ' . _('Price List') . ':</td>
			<td><select required="required" name="SalesType">';
	echo '<option value="AN">' . _('Any Other') . '</option>';

	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['SalesType']) and $MyRow['typeabbrev'] == $_POST['SalesType']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $MyRow['typeabbrev'] . '">' . $MyRow['sales_type'] . '</option>';

	} //end while loop

	echo '</select></td></tr>';


	echo '<tr>
			<td>' . _('Post Sales to GL Account') . ':</td>
			<td><select required="required" name="SalesGLCode">';

	foreach ($PossibleGLCodes as $AccountCode => $AccountName) {
		if (isset($_POST['SalesGLCode']) and $AccountCode == $_POST['SalesGLCode']) {
			echo '<option selected="selected" value="', $AccountCode, '">', $AccountCode, ' - ', htmlspecialchars($AccountName, ENT_QUOTES, 'UTF-8', false), '</option>';
		} else {
			echo '<option value="', $AccountCode, '">', $AccountCode, ' - ', htmlspecialchars($AccountName, ENT_QUOTES, 'UTF-8', false), '</option>';
		}
	} //end while loop

	DB_data_seek($Result, 0);

	echo '</select></td></tr>
		<tr>
			<td>' . _('Post Discount to GL Account') . ':</td>
			<td>
				<select required="required" name="DiscountGLCode">';

	foreach ($PossibleGLCodes as $AccountCode => $AccountName) {
		if (isset($_POST['DiscountGLCode']) and $AccountCode == $_POST['DiscountGLCode']) {
			echo '<option selected="selected" value="', $AccountCode, '">', $AccountCode, ' - ', htmlspecialchars($AccountName, ENT_QUOTES, 'UTF-8', false), '</option>';
		} else {
			echo '<option value="', $AccountCode, '">', $AccountCode, ' - ', htmlspecialchars($AccountName, ENT_QUOTES, 'UTF-8', false), '</option>';
		}
	} //end while loop

	echo '</select></td>
		</tr>
		</table>';

	echo '<div class="centre"><input type="submit" name="submit" value="' . _('Enter Information') . '" /></div>';

	echo '</form>';

} //end if record deleted no point displaying form to add record


include('includes/footer.inc');
?>