<?php
include ('includes/session.php');

$Title = _('Purchase Order Authorisation Maintenance');
include ('includes/header.php');

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/group_add.png" title="', _('Search'), '" alt="" />', ' ', $Title, '
	</p>';

/*Note: If CanCreate==0 then this means the user can create orders
 *	 Also if OffHold==0 then the user can release purchase invocies
 *	 This logic confused me a bit to start with
*/

if (isset($_POST['Submit'])) {
	if (isset($_POST['CanCreate']) and $_POST['CanCreate'] == 'on') {
		$CanCreate = 0;
	} else {
		$CanCreate = 1;
	}
	if (isset($_POST['OffHold']) and $_POST['OffHold'] == 'on') {
		$OffHold = 0;
	} else {
		$OffHold = 1;
	}
	if ($_POST['AuthLevel'] == '') {
		$_POST['AuthLevel'] = 0;
	}
	$SQL = "SELECT COUNT(*)
		FROM purchorderauth
		WHERE userid='" . $_POST['UserID'] . "'
		AND currabrev='" . $_POST['CurrCode'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	if ($MyRow[0] == 0) {
		$SQL = "INSERT INTO purchorderauth ( userid,
						currabrev,
						cancreate,
						offhold,
						authlevel)
					VALUES( '" . $_POST['UserID'] . "',
						'" . $_POST['CurrCode'] . "',
						'" . $CanCreate . "',
						'" . $OffHold . "',
						'" . filter_number_format($_POST['AuthLevel']) . "')";
		$ErrMsg = _('The authentication details cannot be inserted because');
		$Result = DB_query($SQL, $ErrMsg);

		if (DB_error_no($Result) == 0) {
			prnMsg(_('The authorisation record for') . ' ' . $_POST['UserID'] . ' ' . _('has been created'), 'success');
		} else {
			prnMsg(_('The authorisation record for') . ' ' . $_POST['UserID'] . ' ' . _('could not be created'), 'error');
		}

	} else {
		prnMsg(_('There already exists an entry for this user/currency combination'), 'error');
		echo '<br />';
	}
}

if (isset($_POST['Update'])) {
	if (isset($_POST['CanCreate']) and $_POST['CanCreate'] == 'on') {
		$CanCreate = 0;
	} else {
		$CanCreate = 1;
	}
	if (isset($_POST['OffHold']) and $_POST['OffHold'] == 'on') {
		$OffHold = 0;
	} else {
		$OffHold = 1;
	}
	$SQL = "UPDATE purchorderauth SET
			cancreate='" . $CanCreate . "',
			offhold='" . $OffHold . "',
			authlevel='" . filter_number_format($_POST['AuthLevel']) . "'
			WHERE userid='" . $_POST['UserID'] . "'
			AND currabrev='" . $_POST['CurrCode'] . "'";

	$ErrMsg = _('The authentication details cannot be updated because');
	$Result = DB_query($SQL, $ErrMsg);

	if (DB_error_no($Result) == 0) {
		prnMsg(_('The authorisation record for') . ' ' . $_POST['UserID'] . ' ' . _('has been updated'), 'success');
	} else {
		prnMsg(_('The authorisation record for') . ' ' . $_POST['UserID'] . ' ' . _('could not be updated'), 'error');
	}
}

if (isset($_GET['Delete'])) {
	$SQL = "DELETE FROM purchorderauth
		WHERE userid='" . $_GET['UserID'] . "'
		AND currabrev='" . $_GET['Currency'] . "'";

	$ErrMsg = _('The authentication details cannot be deleted because');
	$Result = DB_query($SQL, $ErrMsg);

	if (DB_error_no($Result) == 0) {
		prnMsg(_('The authorisation record for') . ' ' . $_GET['UserID'] . ' ' . _('has been deleted'), 'success');
	} else {
		prnMsg(_('The authorisation record for') . ' ' . $_GET['UserID'] . ' ' . _('could not be deleted'), 'error');
	}
}

if (isset($_GET['Edit'])) {
	$SQL = "SELECT cancreate,
				offhold,
				authlevel
			FROM purchorderauth
			WHERE userid='" . $_GET['UserID'] . "'
			AND currabrev='" . $_GET['Currency'] . "'";
	$ErrMsg = _('The authentication details cannot be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);
	$MyRow = DB_fetch_array($Result);
	$UserID = $_GET['UserID'];
	$Currency = $_GET['Currency'];
	$CanCreate = $MyRow['cancreate'];
	$OffHold = $MyRow['offhold'];
	$AuthLevel = $MyRow['authlevel'];
}

$SQL = "SELECT purchorderauth.userid,
			www_users.realname,
			currencies.currabrev,
			currencies.currency,
			currencies.decimalplaces,
			purchorderauth.cancreate,
			purchorderauth.offhold,
			purchorderauth.authlevel
	FROM purchorderauth INNER JOIN www_users
		ON purchorderauth.userid=www_users.userid
	INNER JOIN currencies
		ON purchorderauth.currabrev=currencies.currabrev";

$ErrMsg = _('The authentication details cannot be retrieved because');
$Result = DB_query($SQL, $ErrMsg);

echo '<table>
		<thead>
			<tr>
				<th class="SortedColumn">', _('User ID'), '</th>
				<th class="SortedColumn">', _('User Name'), '</th>
				<th class="SortedColumn">', _('Currency'), '</th>
				<th class="SortedColumn">', _('Create Order'), '</th>
				<th class="SortedColumn">', _('Can Release'), '<br />', _('Invoices'), '</th>
				<th>', _('Authority Level'), '</th>
				<th colspan="2">&nbsp;</th>
			</tr>
		</thead>';

echo '<tbody>';
while ($MyRow = DB_fetch_array($Result)) {
	if ($MyRow['cancreate'] == 0) {
		$DisplayCanCreate = _('Yes');
	} else {
		$DisplayCanCreate = _('No');
	}
	if ($MyRow['offhold'] == 0) {
		$DisplayOffHold = _('Yes');
	} else {
		$DisplayOffHold = _('No');
	}
	echo '<tr class="striped_row">
			<td>', $MyRow['userid'], '</td>
			<td>', $MyRow['realname'], '</td>
			<td>', _($MyRow['currency']), '</td>
			<td>', $DisplayCanCreate, '</td>
			<td>', $DisplayOffHold, '</td>
			<td class="number">', locale_number_format($MyRow['authlevel'], $MyRow['decimalplaces']), '</td>
			<td><a href="', $RootPath, '/PO_AuthorisationLevels.php?Edit=Yes&amp;UserID=', urlencode($MyRow['userid']), '&amp;Currency=', urlencode($MyRow['currabrev']), '">', _('Edit'), '</a></td>
			<td><a href="', $RootPath, '/PO_AuthorisationLevels.php?Delete=Yes&amp;UserID=', urlencode($MyRow['userid']), '&amp;Currency=', urlencode($MyRow['currabrev']), '" onclick="return MakeConfirm(\'', _('Are you sure you wish to delete this authorisation level?'), '\', \'Confirm Delete\', this);">', _('Delete'), '</a></td>
		</tr>';
}

echo '</tbody>
	</table>';

if (!isset($_GET['Edit'])) {
	$UserID = $_SESSION['UserID'];
	$Currency = $_SESSION['CompanyRecord']['currencydefault'];
	$CanCreate = 0;
	$OffHold = 0;
	$AuthLevel = 0;
}

echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post" id="form1">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
echo '<fieldset>';

if (isset($_GET['Edit'])) {
	echo '<legend>', _('Edit authorisation levels for'), ' ', $_GET['UserID'], '</legend>
			<field>
				<label for="UserID">', _('User ID'), '</label>
				<div class="fieldtext">', $_GET['UserID'], '</div>
			</field>';
	echo '<input type="hidden" name="UserID" value="', $_GET['UserID'], '" />';

	$SQL = "SELECT cancreate,
				offhold,
				authlevel,
				currency,
				decimalplaces
			FROM purchorderauth INNER JOIN currencies
			ON purchorderauth.currabrev=currencies.currabrev
			WHERE userid='" . $_GET['UserID'] . "'
			AND purchorderauth.currabrev='" . $_GET['Currency'] . "'";
	$ErrMsg = _('The authentication details cannot be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);
	$MyRow = DB_fetch_array($Result);
	$UserID = $_GET['UserID'];
	$Currency = $_GET['Currency'];
	$CanCreate = $MyRow['cancreate'];
	$OffHold = $MyRow['offhold'];
	$AuthLevel = $MyRow['authlevel'];
	$CurrDecimalPlaces = $MyRow['decimalplaces'];

	echo '<field>
			<label for="currency">', _('Currency'), '</label>
			<div class="fieldtext">', $MyRow['currency'], '</div>
		</field>';
	echo '<input type="hidden" name="CurrCode" value="', $Currency, '" />';

} else {
	echo '<legend>', _('Create new authorisation level'), '</legend>
			<field>
				<label for="UserID">', _('User ID'), '</label>
				<select required="required" autofocus="autofocus" name="UserID">';
	$UserSQL = "SELECT userid FROM www_users";
	$UserResult = DB_query($UserSQL);
	while ($MyRow = DB_fetch_array($UserResult)) {
		if ($MyRow['userid'] == $UserID) {
			echo '<option selected="selected" value="', $MyRow['userid'], '">', $MyRow['userid'], '</option>';
		} else {
			echo '<option value="', $MyRow['userid'], '">', $MyRow['userid'], '</option>';
		}
	}
	echo '</select>
		<fieldhelp>', _('Select the user to set authorisations for'), '</fieldhelp>
	</field>';

	echo '<field>
			<label for="CurrCode">', _('Currency'), '</label>
			<select required="required" name="CurrCode">';
	$CurrencySQL = "SELECT currabrev,
							currency,
							decimalplaces
						FROM currencies";
	$CurrencyResult = DB_query($CurrencySQL);
	while ($MyRow = DB_fetch_array($CurrencyResult)) {
		if ($_SESSION['CompanyRecord']['currencydefault'] == $MyRow['currabrev']) {
			$CurrDecimalPlaces = $MyRow['decimalplaces'];
		}
		if ($MyRow['currabrev'] == $Currency) {
			echo '<option selected="selected" value="', $MyRow['currabrev'], '">', $MyRow['currency'], '</option>';
		} else {
			echo '<option value="', $MyRow['currabrev'], '">', $MyRow['currency'], '</option>';
		}
	}
	echo '</select>
		<fieldhelp>', _('Select thecurrency to which this authorisation applies.'), '</fieldhelp>
	</field>';
}

echo '<field>
		<label for="CanCreate">', _('User can create orders'), '</label>';
if ($CanCreate == 1) {
	echo '<input type="checkbox" autofocus="autofocus" name="CanCreate" />';
} else {
	echo '<input type="checkbox" autofocus="autofocus" checked="checked" name="CanCreate" />';
}
echo '<fieldhelp>', _('If this user can create new purchase orders in this currency, then tick this box'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="OffHold">', _('User can release invoices') . '</label>';
if ($OffHold == 1) {
	echo '<input type="checkbox" name="OffHold" />';
} else {
	echo '<input type="checkbox" checked="checked" name="OffHold" />';
}
echo '<fieldhelp>', _('If this user can release help invoices in this currency, then tick this box'), '</fieldhelp>
	</field>';

echo '<field>
		<label for="AuthLevel">', _('User can authorise orders up to'), ':</label>
		<input type="text" name="AuthLevel" required="required" maxlength="11" size="11" class="number" value="', locale_number_format($AuthLevel, $CurrDecimalPlaces), '" />
		<fieldhelp>', _('Set the limit up to which this user can create orders for'), '</fieldhelp
	</field>';

echo '</fieldset>';

if (isset($_GET['Edit'])) {
	echo '<div class="centre">
			<input type="submit" name="Update" value="', _('Update Information'), '" />
		</div>';
} else {
	echo '<div class="centre">
			<input type="submit" name="Submit" value="', _('Enter Information'), '" />
		</div>';
}
echo '</form>';
include ('includes/footer.php');
?>