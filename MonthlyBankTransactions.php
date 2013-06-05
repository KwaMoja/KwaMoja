<?php
$PageSecurity=1;
/* $Id: DailyBankTransactions.php 4556 2011-04-26 11:03:36Z daintree $ */

include ('includes/session.inc');
$Title = _('Monthly Bank Transactions Inquiry');
include('includes/header.inc');

echo '<p class="page_title_text noPrint" > <img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title.'</p>';

if (!isset($_POST['Show'])) {
	echo '<form onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" class="noPrint">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table class="selection">';

	$SQL = "SELECT 	bankaccountname,
					bankaccounts.accountcode,
					bankaccounts.currcode
			FROM bankaccounts,
				chartmaster
			WHERE bankaccounts.accountcode=chartmaster.accountcode";

	$ErrMsg = _('The bank accounts could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the bank accounts was');
	$AccountsResults = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	echo '<tr>
			<td>' . _('Bank Account') . ':</td>
			<td><select name="BankAccount">';

	if (DB_num_rows($AccountsResults)==0){
		echo '</select></td>
				</tr></table>';
		prnMsg( _('Bank Accounts have not yet been defined. You must first') . ' <a href="' . $RootPath . '/BankAccounts.php">' . _('define the bank accounts') . '</a> ' . _('and general ledger accounts to be affected'),'warn');
		include('includes/footer.inc');
		exit;
	} else {
		while ($myrow=DB_fetch_array($AccountsResults)){
		/*list the bank account names */
			if (!isset($_POST['BankAccount']) and $myrow['currcode']==$_SESSION['CompanyRecord']['currencydefault']){
				$_POST['BankAccount']=$myrow['accountcode'];
			}
			if (isset($_POST['BankAccount']) and $_POST['BankAccount']==$myrow['accountcode']){
				echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . ' - ' . $myrow['currcode'] . '</option>';
			} else {
				echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . ' - ' . $myrow['currcode'] . '</option>';
			}
		}
		echo '</select></td></tr>';
	}
	$NextYear = date('Y-m-d',strtotime('+1 Year'));
	$sql = "SELECT periodno,
					lastdate_in_period
				FROM periods
				WHERE lastdate_in_period < '" . $NextYear . "'
				ORDER BY periodno DESC";
	$Periods = DB_query($sql,$db);

	echo '<tr>
				<td>' . _('Select Period:') . '</td>
				<td><select name="FromPeriod">';
	while ($myrow=DB_fetch_array($Periods,$db)){
		if(isset($_POST['FromPeriod']) and $_POST['FromPeriod']== $myrow['periodno']){
			echo '<option selected="selected" value="' . $myrow['periodno'] . '">' .MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
		} else {
			echo '<option value="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
		}
	}

	echo '</select></td>
		</table>
		<br />
		<div class="centre">
			<input type="submit" name="Show" value="' . _('Show transactions'). '" />
		</div>
		</div>
		</form>';
} else {

	$BalancesSQL = "SELECT actual,
							bfwd
						FROM chartdetails
						WHERE accountcode='" . $_POST['BankAccount'] . "'
							AND period='" . $_POST['FromPeriod'] . "'";
	$BalancesResult = DB_query($BalancesSQL, $db);
	$BalancesRow = DB_fetch_array($BalancesResult);
	$OpeningBalance = $BalancesRow['bfwd'];
	$ClosingBalance = $BalancesRow['actual'];

	$SQL = "SELECT 	bankaccountname,
					bankaccounts.currcode,
					currencies.decimalplaces
			FROM bankaccounts
			INNER JOIN currencies
				ON bankaccounts.currcode = currencies.currabrev
			WHERE bankaccounts.accountcode='" . $_POST['BankAccount'] . "'";
	$BankResult = DB_query($SQL,$db,_('Could not retrieve the bank account details'));


	$sql="SELECT 	banktrans.currcode,
					banktrans.amount,
					banktrans.amountcleared,
					banktrans.functionalexrate,
					banktrans.exrate,
					banktrans.banktranstype,
					banktrans.transdate,
					banktrans.ref,
					bankaccounts.bankaccountname,
					systypes.typename,
					systypes.typeid
				FROM banktrans
				INNER JOIN gltrans
				ON banktrans.transno=gltrans.typeno
					AND banktrans.type=gltrans.type
					AND banktrans.bankact=gltrans.account
				INNER JOIN bankaccounts
				ON banktrans.bankact=bankaccounts.accountcode
				INNER JOIN systypes
				ON banktrans.type=systypes.typeid
				WHERE bankact='".$_POST['BankAccount']."'
					AND periodno>='" . $_POST['FromPeriod'] . "'
				ORDER BY banktrans.type,
						banktrans.transdate";
	$result = DB_query($sql, $db);
	if (DB_num_rows($result)==0) {
		prnMsg(_('There are no transactions for this account in the date range selected'), 'info');
	} else {
		$sql = "SELECT lastdate_in_period
					FROM periods
					WHERE periodno = '" . $_POST['FromPeriod'] . "'";
		$Periods = DB_query($sql,$db);
		$PeriodRow = DB_fetch_array($Periods);
		$EndDate = ConvertSQLDate($PeriodRow['lastdate_in_period']);

		$BankDetailRow = DB_fetch_array($BankResult);
		echo '<table>
				<tr>
					<th colspan="9">
						<h3>' . _('Account Transactions For').' '.$BankDetailRow['bankaccountname'] . ' ' . _('and Month Ending') . ' ' . $EndDate . '
							<img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" class="PrintIcon noPrint" title="' . _('Print') . '" alt="" onclick="window.print();" />
						</h3>
					</th>
				</tr>
				<tr>
					<th>' . ('Date') . '</th>
					<th>' . _('Transaction type').'</th>
					<th>' . _('Type').'</th>
					<th>' . _('Reference').'</th>
					<th>' . _('Amount in').' '.$BankDetailRow['currcode'].'</th>
					<th>' . _('Matched') . '</th>
				</tr>';

		echo '<tr>
				<td colspan="3"></td>
				<td>' . _('Opening Balance') . '</td>
				<td class="number">' . $OpeningBalance . '</td>
			</tr>';

		$ReceiptsTotal = 0;
		$PaymentsTotal = 0;
		$LastType=12;

		while ($myrow = DB_fetch_array($result)){

			if ($myrow['typeid']==12 or $myrow['typeid']==2) {
				$ReceiptsTotal += $myrow['amount'];
			} else {
				$PaymentsTotal += $myrow['amount'];
			}

			if ($myrow['amount']==$myrow['amountcleared']) {
				$Matched=_('Yes');
			} else {
				$Matched=_('No');
			}
			if (($LastType==12 or $LastType==2) and ($myrow['typeid']==22 or $myrow['typeid']==1)) {
				echo '<tr>
					<td colspan="3"></td>
					<td>' . _('Total Receipts') . '</td>
					<td class="number">' . $ReceiptsTotal . '</td>
				</tr>';
			}

			echo '<tr>
					<td>'. ConvertSQLDate($myrow['transdate']) . '</td>
					<td>'.$myrow['typename'].'</td>
					<td>'.$myrow['banktranstype'].'</td>
					<td>'.$myrow['ref'].'</td>
					<td class="number">'.locale_number_format($myrow['amount'],$BankDetailRow['decimalplaces']).'</td>
					<td class="number">'.$Matched.'</td>
				</tr>';
			$LastType=$myrow['typeid'];
		}
		echo '<tr>
				<td colspan="3"></td>
				<td>' . _('Total Payments') . '</td>
				<td class="number">' . $PaymentsTotal . '</td>
			</tr>';

		echo '<tr>
				<td colspan="3"></td>
				<td>' . _('Closing Balance') . '</td>
				<td class="number">' . $ClosingBalance . '</td>
			</tr>';
		echo '</table>';
	} //end if no bank trans in the range to show

	echo '<form onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" class="noPrint">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<br /><div class="centre"><input type="submit" name="Return" value="' . _('Select Another Date'). '" /></div>';
	echo '</div>';
	echo '</form>';
}
include('includes/footer.inc');

?>