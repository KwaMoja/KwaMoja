<?php

include('includes/session.inc');

$Title = _('Process regular payments');
$ViewTopic = 'GeneralLedger';
$BookMark = 'RegularPayments';

include('includes/header.inc');

echo '<p class="page_title_text" >
		<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/money_add.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
	</p>';

$Frequencies['D'] = _('Daily');
$Frequencies['W'] = _('Weekly');
$Frequencies['F'] = _('Fortnightly');
$Frequencies['M'] = _('Monthly');
$Frequencies['Q'] = _('Quarterly');
$Frequencies['Y'] = _('Annually');

if (isset($_POST['Add'])){
	$AddedPayments = array();
	foreach($_POST as $Key=>$Value) {
		if (substr($Key, 0, 7) == 'Payment') {
			$ID = substr($Key, 7);
			$AddedPayments[$ID]['PaymentExchangeRate'] = 'test';
			$AddedPayments[$ID]['FunctionalExchangeRate'] = 'test';
		}
	}
	var_dump($AddedPayments);
}

echo '<form method="post" id="RegularPaymentsProcess" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

$SQL = "SELECT regularpayments.id,
				regularpayments.frequency,
				regularpayments.days,
				regularpayments.glcode,
				chartmaster.accountname,
				bankaccounts.bankaccountname,
				regularpayments.tag,
				regularpayments.amount,
				regularpayments.currabrev,
				regularpayments.narrative,
				regularpayments.firstpayment,
				regularpayments.finalpayment,
				regularpayments.nextpayment
			FROM regularpayments
			INNER JOIN bankaccounts
				ON bankaccounts.accountcode=regularpayments.bankaccountcode
			INNER JOIN chartmaster
				ON chartmaster.accountcode=regularpayments.glcode
			WHERE completed=0
				AND nextpayment<=CURRENT_DATE
				AND chartmaster.language='" . $_SESSION['ChartLanguage'] . "'";
$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0 and !isset($_GET['Edit'])) {
	echo '<table class="selection">
			<tr>
				<th>' . _('Frequency') . '</th>
				<th>' . _('Days into Period') . '</th>
				<th>' . _('Bank Account') . '</th>
				<th>' . _('GL Account') . '</th>
				<th>' . _('GL Tag') . '</th>
				<th>' . _('Amount of Payment') . '</th>
				<th>' . _('Currency of payment') . '</th>
				<th>' . _('Description') . '</th>
				<th>' . _('First payment Date') . '</th>
				<th>' . _('Next payment Date') . '</th>
				<th>' . _('Last payment Date') . '</th>
			</tr>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr>
				<td>' . $Frequencies[$MyRow['frequency']] . '</td>
				<td class="number">' . $MyRow['days'] . '</td>
				<td>' . $MyRow['bankaccountname'] . '</td>
				<td>' . $MyRow['glcode'] . ' - ' . $MyRow['accountname'] . '</td>
				<td>' . $MyRow['tag'] . '</td>
				<td class="number">' . $MyRow['amount'] . '</td>
				<td>' . $MyRow['currabrev'] . '</td>
				<td>' . $MyRow['narrative'] . '</td>
				<td>' . ConvertSQLDate($MyRow['firstpayment']) . '</td>
				<td>' . ConvertSQLDate($MyRow['nextpayment']) . '</td>
				<td>' . ConvertSQLDate($MyRow['finalpayment']) . '</td>
				<td><input type="checkbox" name="Payment' . $MyRow['id'] . '" />' . _('Process') . '</td>
			</tr>';
	}
	echo '</table>';
}

echo '<div class="centre">
		<input type="submit" name="Add" value="' . _('Add Selected payments') . '" />
	</div>';

echo '</form>';

include('includes/footer.inc');

?>