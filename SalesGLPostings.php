<?php
include ('includes/session.php');
$Title = _('Sales GL Postings Set Up');
$ViewTopic = 'CreatingNewSystem';
$BookMark = 'SalesGLPostings';
include ('includes/header.php');

if (isset($_GET['SelectedSalesPostingID'])) {
	$SelectedSalesPostingID = $_GET['SelectedSalesPostingID'];
} elseif (isset($_POST['SelectedSalesPostingID'])) {
	$SelectedSalesPostingID = $_POST['SelectedSalesPostingID'];
}

$InputError = false;

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/customer.png" title="', _('Search'), '" alt="" />', ' ', $Title, '
	</p>';

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

		echo '<table>
				<thead>
					<tr>
						<th>', _('Area'), '</th>
						<th>', _('Stock Category'), '</th>
						<th>', _('Sales Type'), '</th>
						<th>', _('Sales Account'), '</th>
						<th>', _('Discount Account'), '</th>
						<th colspan="2"></th>
					</tr>
				</thead>';

		while ($MyRow = DB_fetch_array($Result)) {

			if ($MyRow['area'] != 'AN') {
				$SQL = "SELECT areadescription FROM areas WHERE areacode='" . $MyRow['area'] . "'";
				$AreaResult = DB_query($SQL);
				$AreaRow = DB_fetch_array($AreaResult);
			} else {
				$AreaRow['areadescription'] = _('Any Other Area');
			}

			if ($MyRow['stkcat'] != 'ANY') {
				$SQL = "SELECT categorydescription FROM stockcategory WHERE categoryid='" . $MyRow['stkcat'] . "'";
				$CategoryResult = DB_query($SQL);
				$CategoryRow = DB_fetch_array($CategoryResult);
			} else {
				$CategoryRow['categorydescription'] = _('Any Other Category');
			}

			if ($MyRow['salestype'] != 'AN') {
				$SQL = "SELECT sales_type FROM salestypes WHERE typeabbrev='" . $MyRow['salestype'] . "'";
				$TypeResult = DB_query($SQL);
				$TypeRow = DB_fetch_array($TypeResult);
			} else {
				$TypeRow['sales_type'] = _('Any Other Sales Type');
			}

			$SQL = "SELECT chartmaster.accountname
					FROM chartmaster
					WHERE accountcode='" . $MyRow['salesglcode'] . "'
						AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'";
			$Result = DB_query($SQL);
			$SalesRow = DB_fetch_array($Result);

			$SQL = "SELECT chartmaster.accountname
					FROM chartmaster
					WHERE accountcode='" . $MyRow['discountglcode'] . "'
						AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'";
			$Result = DB_query($SQL);
			$DiscountRow = DB_fetch_array($Result);

			echo '<tr class="striped_row">
					<td>', $AreaRow['areadescription'], ' (', $MyRow['area'], ')</td>
					<td>', $CategoryRow['categorydescription'], ' (', $MyRow['stkcat'], ')</td>
					<td>', $TypeRow['sales_type'], ' (', $MyRow['salestype'], ')</td>
					<td>', htmlspecialchars($MyRow['salesglcode'] . ' - ' . $SalesRow['accountname'], ENT_QUOTES, 'UTF-8'), '</td>
					<td>', htmlspecialchars($MyRow['discountglcode'] . ' - ' . $DiscountRow['accountname'], ENT_QUOTES, 'UTF-8'), '</td>
					<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedSalesPostingID=', urlencode($MyRow['id']), '">', _('Edit'), '</a></td>
					<td><a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?SelectedSalesPostingID=', urlencode($MyRow['id']), '&amp;delete=yes" onclick="return MakeConfirm(\'', _('Are you sure you wish to delete this sales GL posting record?'), '\', \'Confirm Delete\', this);">', _('Delete'), '</a></td>
				</tr>';
		}
		//END WHILE LIST LOOP
		echo '</table>';
	}
}

//end of ifs and buts!
if (isset($SelectedSalesPostingID)) {
	echo '<div class="centre">
			<a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">', _('Show All Sales Posting Codes Defined'), '</a>
		</div>';
}

echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

echo '<fieldset>';

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

	echo '<input type="hidden" name="SelectedSalesPostingID" value="', $SelectedSalesPostingID, '" />';
	echo '<legend>', _('Edit Sales GL details'), '</legend>';

} else {
	echo '<legend>', _('Create New Sales GL details'), '</legend>';
}

$SQL = "SELECT areacode,
			areadescription FROM areas";
$AreaResult = DB_query($SQL);

echo '<field>
			<label for="Area">', _('Area'), ':</label>
			<select required="required" autofocus="autofocus" name="Area">
				<option value="AN">', _('Any Other'), '</option>';

while ($AreaRow = DB_fetch_array($AreaResult)) {
	if (isset($_POST['Area']) and $AreaRow['areacode'] == $_POST['Area']) {
		echo '<option selected="selected" value="', $AreaRow['areacode'], '">', $AreaRow['areadescription'], '</option>';
	} else {
		echo '<option value="', $AreaRow['areacode'], '">', $AreaRow['areadescription'], '</option>';
	}
} //end while loop
echo '</select>
		<fieldhelp>', _('Select an Area here. If this record is to refer to all areas that do not have a specific record then select Any Other.'), '</fieldhelp>
	</field>';

$SQL = "SELECT categoryid, categorydescription FROM stockcategory";
$CategoryResult = DB_query($SQL);

echo '<field>
			<label for="StkCat">', _('Stock Category'), ':</label>
			<select required="required" name="StkCat">
				<option value="ANY">', _('Any Other'), '</option>';

while ($CategoryRow = DB_fetch_array($CategoryResult)) {

	if (isset($_POST['StkCat']) and ($CategoryRow['categoryid'] == $_POST['StkCat'])) {
		echo '<option selected="selected" value="', $CategoryRow['categoryid'], '">', $CategoryRow['categorydescription'], '</option>';
	} else {
		echo '<option value="', $CategoryRow['categoryid'], '">', $CategoryRow['categorydescription'], '</option>';
	}
} //end while loop
echo '</select>
		<fieldhelp>', _('Select a Stock Category here. If this record is to refer to all categories that do not have a specific record then select Any Other.'), '</fieldhelp>
	</field>';

$SQL = "SELECT typeabbrev,
					sales_type
			FROM salestypes";
$Result = DB_query($SQL);

echo '<field>
			<label for="SalesType">', _('Sales Type'), ' / ', _('Price List'), ':</label>
			<select required="required" name="SalesType">
				<option value="AN">', _('Any Other'), '</option>';

while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['SalesType']) and $MyRow['typeabbrev'] == $_POST['SalesType']) {
		echo '<option selected="selected" value="', $MyRow['typeabbrev'], '">', $MyRow['sales_type'], '</option>';
	} else {
		echo '<option value="', $MyRow['typeabbrev'], '">', $MyRow['sales_type'], '</option>';
	}
} //end while loop
echo '</select>
		<fieldhelp>', _('Select a Sales Type here. If this record is to refer to all types that do not have a specific record then select Any Other.'), '</fieldhelp>
	</field>';

echo '<field>
			<label for="SalesGLCode">', _('Post Sales to GL Account'), ':</label>';
GLSelect(1, 'SalesGLCode');
echo '<fieldhelp>', _('Select the GL code to which sales should be posted for this criteria.'), '</fieldhelp>
	</field>';

echo '<field>
			<label for="DiscountGLCode">', _('Post Discount to GL Account'), ':</label>';
GLSelect(1, 'DiscountGLCode');
echo '<fieldhelp>', _('Select the GL code to which sales discount should be posted for this criteria.'), '</fieldhelp>
	</field>';

echo '</fieldset>';

echo '<div class="centre">
			<input type="submit" name="submit" value="', _('Enter Information'), '" />
		</div>';

echo '</form>';

include ('includes/footer.php');
?>