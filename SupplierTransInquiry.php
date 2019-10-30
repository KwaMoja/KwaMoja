<?php
include ('includes/session.php');
$Title = _('Supplier Transactions Inquiry');
include ('includes/header.php');

echo '<p class="page_title_text" >
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/supplier.png" title="', _('Search'), '" alt="" />', ' ', $Title, '
	</p>';

echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

$SQL = "SELECT typeid,
				typename
		FROM systypes
		WHERE typeid >= 20
		AND typeid <= 23";

$ResultTypes = DB_query($SQL);
echo '<fieldset>
		<legend>', _('Report Criteria'), '</legend>
		<field>
			<label for="TransType">', _('Type'), ':</label>
			<select name="TransType">
				<option value="All">', _('All'), '</option>';
while ($MyRow = DB_fetch_array($ResultTypes)) {
	if (isset($_POST['TransType'])) {
		if ($MyRow['typeid'] == $_POST['TransType']) {
			echo '<option selected="selected" value="', $MyRow['typeid'], '">', $MyRow['typename'], '</option>';
		} else {
			echo '<option value="', $MyRow['typeid'], '">', $MyRow['typename'], '</option>';
		}
	} else {
		echo '<option value="', $MyRow['typeid'], '">', $MyRow['typename'], '</option>';
	}
}
echo '</select>
	</field>';

if (!isset($_POST['FromDate'])) {
	$_POST['FromDate'] = Date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, Date('m'), 1, Date('Y')));
}
if (!isset($_POST['ToDate'])) {
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['SupplierNo'])) {
	$_POST['SupplierNo'] = '';
}
echo '<field>
		<label for="FromDate">', _('From'), ':</label>
		<input type="text" class="date" name="FromDate" required="required" maxlength="10" size="11" value="', $_POST['FromDate'], '" />
	</field>';

echo '<field>
		<label for="ToDate">', _('To'), ':</label>
		<input type="text" class="date" name="ToDate" required="required" maxlength="10" size="11" value="', $_POST['ToDate'], '" />
	</field>';

echo '<field>
		<label for="SupplierNo">', _('Supplier No'), ':</label>
		<input type="text" name="SupplierNo" size="11" maxlength="10" value="', $_POST['SupplierNo'], '" />
	</field>';

echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="ShowResults" value="', _('Show transactions'), '" />
	</div>
</form>';

if (isset($_POST['ShowResults']) and $_POST['TransType'] != '') {
	$SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
	$SQL_ToDate = FormatDateForSQL($_POST['ToDate']);
	$SQL = "SELECT type,
				transno,
		   		trandate,
				duedate,
				supplierno,
				suppname,
				suppreference,
				transtext,
				supptrans.rate,
				diffonexch,
				alloc,
				ovamount+ovgst as totalamt,
				currcode,
				typename,
				decimalplaces AS currdecimalplaces
			FROM supptrans
			INNER JOIN suppliers ON supptrans.supplierno=suppliers.supplierid
			INNER JOIN systypes ON supptrans.type = systypes.typeid
			INNER JOIN currencies ON suppliers.currcode=currencies.currabrev
			WHERE ";

	$SQL = $SQL . "trandate >='" . $SQL_FromDate . "' AND trandate <= '" . $SQL_ToDate . "'";
	if ($_POST['TransType'] != 'All') {
		$SQL.= " AND type = " . $_POST['TransType'];
	}

	if ($_POST['SupplierNo'] != '') {
		$SQL.= " AND supptrans.supplierno LIKE '%" . $_POST['SupplierNo'] . "%'";
	}
	$SQL.= " ORDER BY id";

	$TransResult = DB_query($SQL);
	$ErrMsg = _('The supplier transactions for the selected criteria could not be retrieved because') . ' - ' . DB_error_msg();
	$DbgMsg = _('The SQL that failed was');

	echo '<table>
			<tr>
				<th>', _('Type'), '</th>
				<th>', _('Number'), '</th>
				<th>', _('Supp Ref'), '</th>
				<th>', _('Date'), '</th>
				<th>', _('Supplier'), '</th>
				<th>', _('Comments'), '</th>
				<th>', _('Due Date'), '</th>
				<th>', _('Ex Rate'), '</th>
				<th>', _('Amount'), '</th>
				<th>', _('Currency'), '</th>
			</tr>';

	while ($MyRow = DB_fetch_array($TransResult)) {

		echo '<tr class="striped_row">
				<td>', $MyRow['typename'], '</td>
				<td>', $MyRow['transno'], '</td>
				<td>', $MyRow['suppreference'], '</td>
				<td>', ConvertSQLDate($MyRow['trandate']), '</td>
				<td>', $MyRow['supplierno'] . ' - ' . $MyRow['suppname'], '</td>
				<td>', $MyRow['transtext'], '</td>
				<td>', ConvertSQLDate($MyRow['duedate']), '</td>
				<td class="number">', locale_number_format($MyRow['rate'], 'Variable'), '</td>
				<td class="number">', locale_number_format($MyRow['totalamt'], $MyRow['currdecimalplaces']), '</td>
				<td>', $MyRow['currcode'], '</td>
			</tr>';

		$GLTransResult = DB_query("SELECT account,
										accountname,
										narrative,
										amount
									FROM gltrans
									INNER JOIN chartmaster
									ON gltrans.account=chartmaster.accountcode
									WHERE type='" . $MyRow['type'] . "'
										AND typeno='" . $MyRow['transno'] . "'
										AND language='" . $_SESSION['ChartLanguage'] . "'", _('Could not retrieve the GL transactions for this AP transaction'));

		if (DB_num_rows($GLTransResult) == 0) {
			echo '<tr>
					<td colspan="10">', _('There are no GL transactions created for the above AP transaction'), '</td>
				</tr>';
		} else {
			echo '<tr>
					<td colspan="2"></td>
					<td colspan="8">
						<table>';
			echo '<tr>
					<th colspan="2"><b>', _('GL Account'), '</b></th>
					<th><b>', _('Local Amount'), '</b></th>
					<th><b>', _('Narrative'), '</b></th>
				</tr>';
			$CheckGLTransBalance = 0;
			while ($GLTransRow = DB_fetch_array($GLTransResult)) {

				echo '<tr class="striped_row">
						<td>', $GLTransRow['account'], '</td>
						<td>', $GLTransRow['accountname'], '</td>
						<td class="number">', locale_number_format($GLTransRow['amount'], $_SESSION['CompanyRecord']['decimalplaces']), '</td>
						<td>', $GLTransRow['narrative'], '</td>
					</tr>';

				$CheckGLTransBalance+= $GLTransRow['amount'];
			}
			if (round($CheckGLTransBalance, 5) != 0) {
				echo '<tr class="error_row">
						<td colspan="4""><b>', _('The GL transactions for this AP transaction are out of balance by'), ' ', $CheckGLTransBalance, '</b></td>
					</tr>';
			}
			echo '</table>
				</td>
			</tr>';
		}

		//end of page full new headings if
		
	}
	//end of while loop
	echo '</table>';
}
include ('includes/footer.php');
?>