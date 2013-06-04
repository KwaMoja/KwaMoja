<?php

/* $Id$*/

/*
Call this page with:
	1. A TransID to show the make up and to modify existing allocations.
	2. A DebtorNo to show all outstanding receipts or credits yet to be allocated.
	3. No parameters to show all outstanding credits and receipts yet to be allocated.
*/

include('includes/DefineCustAllocsClass.php');
include('includes/session.inc');
$Title = _('Customer Receipt') . '/' . _('Credit Note Allocations');
/* KwaMoja manual links before header.inc */
$ViewTopic= "ARTransactions";
$BookMark = "CustomerAllocations";
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if ( isset($_POST['Cancel']) ) {
	unset($_POST['UpdateDatabase']);
	unset($_POST['RefreshAllocTotal']);
	unset($_POST['AllocTrans']);
}

if (isset($_POST['UpdateDatabase']) or isset($_POST['RefreshAllocTotal'])) {

	if (!isset($_SESSION['Alloc'])) {
		prnMsg(_('Allocations can not be processed again') . '. ' .
			_('If you hit refresh on this page after having just processed an allocation') . ', ' .
			_('try to use the navigation links provided rather than the back button') . ', ' .
			_('to avoid this message in future'),'info');
		include('includes/footer.inc');
		exit;
	}

	$InputError = 0;
	$TotalAllocated = 0;
	$TotalDiffOnExch = 0;

	for ($AllocCounter=0;$AllocCounter < $_POST['TotalNumberOfAllocs']; $AllocCounter++) {
	// loop through amounts allocated using AllocnItm->ID for each record
		if (isset($_POST['Amt' . $AllocCounter])) {
			// allocatable charge amounts
			if (!is_numeric(filter_number_format($_POST['Amt' . $AllocCounter]))) {
				$_POST['Amt' . $AllocCounter] = 0;
			}
			if (filter_number_format($_POST['Amt' . $AllocCounter]) < 0) {
				prnMsg(_('Amount entered was negative') . '. ' . _('Only positive amounts are allowed') . '.','warn');
				$_POST['Amt' . $AllocCounter] = 0;
			}
			if (isset($_POST['All' . $AllocCounter]) and $_POST['All' . $AllocCounter] == True) {
				$_POST['Amt' . $AllocCounter] = $_POST['YetToAlloc' . $AllocCounter];
			}
			if (filter_number_format($_POST['Amt' . $AllocCounter]) > $_POST['YetToAlloc' . $AllocCounter]) {
				$_POST['Amt' . $AllocCounter]=locale_number_format($_POST['YetToAlloc' . $AllocCounter],$_SESSION['Alloc']->CurrDecimalPlaces);
				// Amount entered must be smaller than unallocated amount
			}

			$_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->AllocAmt = filter_number_format($_POST['Amt' . $AllocCounter]);
			// recalcuate the new difference on exchange (a +positive amount is a gain -ve a loss)
			$_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->DiffOnExch =
			  (filter_number_format($_POST['Amt' . $AllocCounter]) / $_SESSION['Alloc']->TransExRate) -
			  (filter_number_format($_POST['Amt' . $AllocCounter]) / $_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->ExRate);

			$TotalDiffOnExch += $_SESSION['Alloc']->Allocs[$_POST['AllocID' . $AllocCounter]]->DiffOnExch;
			$TotalAllocated += filter_number_format($_POST['Amt' . $AllocCounter]);
		}

	}

	if ($TotalAllocated + $_SESSION['Alloc']->TransAmt > 0.008) {
		prnMsg(_('Allocation could not be processed because the amount allocated is more than the').' ' .
		$_SESSION['Alloc']->TransTypeName  . ' '._('being allocated') . '<br />' . _('Total allocated').' = ' .
		$TotalAllocated . ' '._('and the total amount of the') .' ' . $_SESSION['Alloc']->TransTypeName  . ' '.
		_('was').' ' . -$_SESSION['Alloc']->TransAmt,'error');
		$InputError=1;
	}
}

if (isset($_POST['UpdateDatabase'])) {
	if ($InputError==0) {
		//
		//========[ START TRANSACTION ]===========
		//
		$error = '';
		$Result=DB_Txn_Begin($db);
		$AllAllocations = 0;
		foreach ($_SESSION['Alloc']->Allocs as $AllocnItem) {
			if ($AllocnItem->PrevAllocRecordID != 'NA') {
			// original allocation has changed so delete the old allocation record
				$SQL = "DELETE FROM custallocns WHERE id = '" . $AllocnItem->PrevAllocRecordID . "'";
				if( !$Result = DB_query($SQL,$db) ) {
					$error = 'Could not delete old allocation record';
				}
			 }

//			 if ($AllocnItem->OrigAlloc != $AllocnItem->AllocAmt) // has previous allocated amount changed?
//			 {
				if ($AllocnItem->AllocAmt > 0) {
					$SQL = "INSERT INTO
								custallocns (
								datealloc,
								amt,
								transid_allocfrom,
								transid_allocto
							) VALUES (
								'CURRENT_DATE',
								'" . $AllocnItem->AllocAmt . "',
								'" . $_SESSION['Alloc']->AllocTrans . "',
								'" . $AllocnItem->ID . "'
							)";
					if( !$Result = DB_query($SQL,$db) ) {
						$error = 'Could not change allocation record';
					}
				}
				$NewAllocTotal = $AllocnItem->PrevAlloc + $AllocnItem->AllocAmt;
				$AllAllocations = $AllAllocations + $AllocnItem->AllocAmt;
				$Settled = (abs($NewAllocTotal-$AllocnItem->TransAmount) < 0.005) ? 1 : 0;

				$SQL = "UPDATE debtortrans
						SET diffonexch='" . $AllocnItem->DiffOnExch . "',
						alloc = '" . $NewAllocTotal . "',
						settled = '" . $Settled . "'
						WHERE id = '" . $AllocnItem->ID."'";
				if( !$Result = DB_query($SQL,$db) ) {
					$error = 'Could not update difference on exchange';
				}
//			 }
		}
		$Settled = (abs($TotalAllocated + $_SESSION['Alloc']->TransAmt) < 0.01) ? 1 : 0;

		// Update the receipt or credit note
		$SQL = "UPDATE debtortrans
				SET alloc = '" .  -$AllAllocations . "',
				diffonexch = '" . -$TotalDiffOnExch . "',
				settled='" . $Settled . "'
				WHERE id = '" . $_POST['AllocTrans']."'";

		if( !$Result = DB_query($SQL,$db) ) {
			$error = 'Could not update receipt or credit note';
		}

		// If GLLink to debtors active post diff on exchange to GL
		$MovtInDiffOnExch = -$_SESSION['Alloc']->PrevDiffOnExch - $TotalDiffOnExch;

		if ($MovtInDiffOnExch !=0) {
			if ($_SESSION['CompanyRecord']['gllink_debtors'] == 1) {
				$PeriodNo = GetPeriod($_SESSION['Alloc']->TransDate, $db);
				$_SESSION['Alloc']->TransDate = FormatDateForSQL($_SESSION['Alloc']->TransDate);

					$SQL = "INSERT INTO gltrans (
								type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount
							) VALUES (
								'" . $_SESSION['Alloc']->TransType . "',
								'" . $_SESSION['Alloc']->TransNo . "',
								'" . $_SESSION['Alloc']->TransDate . "',
								'" . $PeriodNo . "',
								'" . $_SESSION['CompanyRecord']['exchangediffact'] . "',
								'',
								'" . $MovtInDiffOnExch . "'
							)";
				if( !$Result = DB_query($SQL,$db) ) {
					$error = 'Could not update exchange difference in General Ledger';
				}

		  		$SQL = "INSERT INTO gltrans (
							type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount
		  				) VALUES (
							'" . $_SESSION['Alloc']->TransType . "',
							'" . $_SESSION['Alloc']->TransNo . "',
							'" . $_SESSION['Alloc']->TransDate . "',
							'" . $PeriodNo . "',
							'" . $_SESSION['CompanyRecord']['debtorsact'] . "',
							'',
							'" . -$MovtInDiffOnExch . "'
						)";
				if( !$Result = DB_query($SQL,$db) ) {
					$error = 'Could not update debtors control in General Ledger';
				}
			}

		}

		//
		//========[ COMMIT TRANSACTION ]===========
		//
		if ( empty($error) ) {
				$Result = DB_Txn_Commit($db);
		} else {
				$Result = DB_Txn_Rollback($db);
				prnMsg($error,'error');
		}
		unset($_SESSION['Alloc']);
		unset($_POST['AllocTrans']);
	}
}

if (isset($_GET['AllocTrans'])) {
	if (isset($_SESSION['Alloc'])) {
		unset($_SESSION['Alloc']->Allocs);
		unset($_SESSION['Alloc']);
	}

	$_SESSION['Alloc'] = new Allocation;
	$_POST['AllocTrans'] = $_GET['AllocTrans']; 	// Set AllocTrans when page first called

	$SQL= "SELECT systypes.typename,
					debtortrans.type,
					debtortrans.transno,
					debtortrans.trandate,
					debtortrans.debtorno,
					debtorsmaster.name,
					debtortrans.rate,
					(debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount) as total,
					debtortrans.diffonexch,
					debtortrans.alloc,
					currencies.decimalplaces
			FROM debtortrans INNER JOIN systypes
			ON debtortrans.type = systypes.typeid
			INNER JOIN debtorsmaster
			ON debtortrans.debtorno = debtorsmaster.debtorno
			INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			WHERE debtortrans.id='" . $_POST['AllocTrans'] . "'";
	$Result = DB_query($SQL,$db);
	$myrow = DB_fetch_array($Result);

	$_SESSION['Alloc']->AllocTrans	= $_POST['AllocTrans'];
	$_SESSION['Alloc']->DebtorNo		= $myrow['debtorno'];
	$_SESSION['Alloc']->CustomerName	= $myrow['name'];
	$_SESSION['Alloc']->TransType		= $myrow['type'];
	$_SESSION['Alloc']->TransTypeName	= $myrow['typename'];
	$_SESSION['Alloc']->TransNo		= $myrow['transno'];
	$_SESSION['Alloc']->TransExRate	= $myrow['rate'];
	$_SESSION['Alloc']->TransAmt		= $myrow['total'];
	$_SESSION['Alloc']->PrevDiffOnExch = $myrow['diffonexch'];
	$_SESSION['Alloc']->TransDate		= ConvertSQLDate($myrow['trandate']);
	$_SESSION['Alloc']->CurrDecimalPlaces = $myrow['decimalplaces'];

	// First get transactions that have outstanding balances
	$SQL = "SELECT debtortrans.id,
					typename,
					transno,
					trandate,
					rate,
					ovamount+ovgst+ovfreight+ovdiscount as total,
					diffonexch,
					alloc
			FROM debtortrans INNER JOIN systypes
			ON debtortrans.type = systypes.typeid
			WHERE debtortrans.settled=0
			AND debtorno='" . $_SESSION['Alloc']->DebtorNo . "'
			ORDER BY debtortrans.trandate";
	$Result = DB_query($SQL,$db);

	while ($myrow=DB_fetch_array($Result)) {
		$_SESSION['Alloc']->add_to_AllocsAllocn ($myrow['id'],
												$myrow['typename'],
												$myrow['transno'],
												ConvertSQLDate($myrow['trandate']),
												0,
												$myrow['total'],
												$myrow['rate'],
												$myrow['diffonexch'],
												$myrow['diffonexch'],
												$myrow['alloc'],
												'NA');
	}
	DB_free_result($Result);

	// Get trans previously allocated to by this trans - this will overwrite incomplete allocations above
	$SQL= "SELECT debtortrans.id,
					typename,
					transno,
					trandate,
					rate,
					ovamount+ovgst+ovfreight+ovdiscount AS total,
					diffonexch,
					debtortrans.alloc-custallocns.amt AS prevallocs,
					amt,
					custallocns.id AS allocid
			FROM debtortrans INNER JOIN systypes
			ON debtortrans.type = systypes.typeid
			INNER JOIN custallocns
			ON debtortrans.id=custallocns.transid_allocto
			WHERE custallocns.transid_allocfrom='" . $_POST['AllocTrans'] . "'
			AND debtorno='" . $_SESSION['Alloc']->DebtorNo . "'
			ORDER BY debtortrans.trandate";

	$Result=DB_query($SQL,$db);

	while ($myrow=DB_fetch_array($Result)) {
		$DiffOnExchThisOne = ($myrow['amt']/$myrow['rate']) - ($myrow['amt']/$_SESSION['Alloc']->TransExRate);
		$_SESSION['Alloc']->add_to_AllocsAllocn ($myrow['id'],
												$myrow['typename'],
												$myrow['transno'],
												ConvertSQLDate($myrow['trandate']),
												$myrow['amt'],
												$myrow['total'],
												$myrow['rate'],
												$DiffOnExchThisOne,
												($myrow['diffonexch'] - $DiffOnExchThisOne),
												$myrow['prevallocs'],
												$myrow['allocid']);
	}
	DB_free_result($Result);
}


echo '<p class="page_title_text noPrint" >
		<img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . _('Allocate Receipt') . '" alt="" />' . ' ' . _('Allocate Receipts') . '
	</p>';

$TableHeader = '<tr>
					<th>' . _('Trans Type') . '</th>
					<th>' . _('Customer') . '</th>
					<th>' . _('Cust No') . '</th>
					<th>' . _('Number') . '</th>
					<th>' . _('Date') . '</th>
					<th>' . _('Total') . '</th>
					<th>' . _('To Alloc') . '</th>
					<th>' . _('Currency') . '</th>
					<th>' . _('Action') . '</th>
				</tr>';

if (isset($_POST['AllocTrans'])) {
	// Page called with trans number
	echo '<form onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" class="noPrint">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="AllocTrans" value="' . $_POST['AllocTrans'] . '" />';

	// Show trans already allocated and potential new allocations

	echo '<table class="selection">';
	echo '<tr>
			<th colspan="7">
			<div class="centre">
				<b>' . $_SESSION['Alloc']->DebtorNo . ' - ' . $_SESSION['Alloc']->CustomerName . '</b>
			</div>';

	if ($_SESSION['Alloc']->TransExRate != 1) {
			echo '<br />' . _('Amount in customer currency') . ' <b>' .
			locale_number_format(-$_SESSION['Alloc']->TransAmt,$_SESSION['Alloc']->CurrDecimalPlaces) .
			'</b><i> ('._('converted into local currency at an exchange rate of'). ' '
			. $_SESSION['Alloc']->TransExRate . ')</i>';
	}
	echo '</th>
		</tr>
		<tr>
			<th>' . _('Trans') . '<br />' . _('Type') . '</th>
			<th>' . _('Trans') . '<br />' . _('Number') . '</th>
			<th>' . _('Trans') . '<br />' . _('Date') . '</th>
			<th>' . _('Total') . '<br />' . _('Amount') . '</th>
			<th>' . _('Yet to') . '<br />' . _('Allocate') . '</th>
			<th>' . _('This') . '<br />' . _('Allocation') . '</th>
			<th>' . _('Running') . '<br />' . _('Balance') . '</th>
		</tr>';

	$Counter = 0;
	$TotalAllocated = 0;
	$balance = 0;
	$j=0;
	foreach ($_SESSION['Alloc']->Allocs as $AllocnItem) {
		$YetToAlloc = ($AllocnItem->TransAmount - $AllocnItem->PrevAlloc);

		if ( $AllocnItem->ID == $_POST['AllocTrans'] ) {
			echo '<tr class="OddTableRows">';
			$curTrans = _('Being allocated');
		} else if ($AllocnItem->AllocAmt > 0) {
			echo '<tr class="OddTableRows">';
		} else {
			echo '<tr class="EvenTableRows">';
			$curTrans = "&nbsp;";
		}

		echo '<td>' . $AllocnItem->TransType . '</td>
			<td>' . $AllocnItem->TypeNo . '</td>
			<td class="number">' . $AllocnItem->TransDate . '</td>
			<td class="number">' . locale_number_format($AllocnItem->TransAmount,$_SESSION['Alloc']->CurrDecimalPlaces) . '</td>
			<td class="number">' . locale_number_format($YetToAlloc,$_SESSION['Alloc']->CurrDecimalPlaces) . '</td>';
		$j++;

		if ($AllocnItem->TransAmount < 0) {
				$balance+=$YetToAlloc;
				echo '<td>' . $curTrans .'</td>
						<td class="number">' . locale_number_format($balance,$_SESSION['Alloc']->CurrDecimalPlaces) . '</td>
					</tr>';
		} else {
				echo '<td class="number"><input type="hidden" name="YetToAlloc' . $Counter . '" value="' . round($YetToAlloc,$_SESSION['Alloc']->CurrDecimalPlaces) . '" />';
				echo '<input tabindex="' . $j .'" type="checkbox" name="All' .  $Counter . '"';

				if (ABS($AllocnItem->AllocAmt-$YetToAlloc) < 0.01) {
						echo ' checked="checked" />';
				} else {
						echo ' />';
				}
				$balance += $YetToAlloc-$AllocnItem->AllocAmt;
				$j++;
				echo '<input tabindex="'.$j.'" type="text" class="number" name="Amt' . $Counter .'" maxlength="12" size="13" value="' . locale_number_format(round($AllocnItem->AllocAmt,$_SESSION['Alloc']->CurrDecimalPlaces),$_SESSION['Alloc']->CurrDecimalPlaces) . '" />
					<input type="hidden" name="AllocID' . $Counter . '" value="' . $AllocnItem->ID . '" /></td>
					<td class="number">' . locale_number_format($balance,$_SESSION['Alloc']->CurrDecimalPlaces) . '</td>
				</tr>';
		}
		$TotalAllocated += round($AllocnItem->AllocAmt,$_SESSION['Alloc']->CurrDecimalPlaces);
		$Counter++;
	}

	echo '<tr>
			<td colspan="5" class="number"><b>'._('Total Allocated').':</b></td>
			<td class="number"><b>' . locale_number_format($TotalAllocated,$_SESSION['Alloc']->CurrDecimalPlaces) . '</b></td>';
	$j++;
	echo '<td colspan="2">
			<input tabindex="'.$j.'" type="submit" name="RefreshAllocTotal" value="' . _('Recalculate Total To Allocate') . '" /></td>
        </tr>
		<tr>
			<td colspan="5" class="number"><b>'._('Left to allocate').'</b></td>
			<td class="number"><b>' . locale_number_format(-$_SESSION['Alloc']->TransAmt-$TotalAllocated,$_SESSION['Alloc']->CurrDecimalPlaces).'</b></td>
		</tr>
		</table>
		<br />
		<input type="hidden" name="TotalNumberOfAllocs" value="' . $Counter . '" />
		<div class="centre">
			<input tabindex="' . $j . '" type="submit" name="UpdateDatabase" value="' . _('Process Allocations') . '" />
			<input tabindex="' . $j . '" type="submit" name="Cancel" value="' . _('Cancel') . '" />
		</div>
        </div>
        </form>';

} elseif (isset($_GET['DebtorNo'])) {
	// Page called with customer code
	unset($_SESSION['Alloc']->Allocs);
	unset($_SESSION['Alloc']);

	$SQL = "SELECT debtortrans.id,
				debtortrans.transno,
				systypes.typename,
				debtortrans.type,
				debtortrans.debtorno,
				debtorsmaster.name,
				debtortrans.trandate,
				debtortrans.reference,
				debtortrans.rate,
				debtortrans.ovamount+debtortrans.ovgst+debtortrans.ovdiscount+debtortrans.ovfreight as total,
				debtortrans.alloc,
				currencies.decimalplaces AS currdecimalplaces,
				debtorsmaster.currcode
			FROM debtortrans INNER JOIN debtorsmaster
			ON debtortrans.debtorno=debtorsmaster.debtorno
			INNER JOIN systypes
			ON debtortrans.type=systypes.typeid
			INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			WHERE debtortrans.debtorno='" . $_GET['DebtorNo'] . "'
			AND (debtortrans.type=12 OR debtortrans.type=11)
			AND debtortrans.settled=0
			ORDER BY debtortrans.id";
	$result = DB_query($SQL,$db);

	if (DB_num_rows($result)==0) {
		prnMsg(_('No outstanding receipts or credits to be allocated for this customer'),'info');
		include('includes/footer.inc');
		exit;
	}
	 echo '<table class="selection">';
	echo $TableHeader;
	$k=0;
	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo  '<tr class="OddTableRows">';;
			$k++;
		}
		echo '<td>' . $myrow['typename'] .'</td>
				<td>' . $myrow['name'] . '</td>
				<td>' . $myrow['debtorno'] . '</td>
				<td>' . $myrow['transno'] . '</td>
				<td>' . ConvertSQLDate($myrow['trandate']) . '</td>
				<td class="number">' . locale_number_format($myrow['total'],$myrow['currdecimalplaces']) . '</td>
				<td class="number">' . locale_number_format($myrow['total']-$myrow['alloc'],$myrow['currdecimalplaces']) . '</td>
				<td>' . $myrow['currcode'] . '</td>';
		echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'). '?AllocTrans=' . $myrow['id'] . '">' . _('Allocate') . '</a></td></tr>';
	}
	echo '</table>';
} else {
	/* Page called with no parameters */
	unset($_SESSION['Alloc']->Allocs);
	unset($_SESSION['Alloc']);

	$SQL = "SELECT debtortrans.id,
				debtortrans.transno,
				systypes.typename,
				debtortrans.type,
				debtortrans.debtorno,
				debtorsmaster.name,
				debtortrans.trandate,
				debtortrans.reference,
				debtortrans.rate,
				debtortrans.ovamount+debtortrans.ovgst+debtortrans.ovdiscount+debtortrans.ovfreight as total,
				debtortrans.alloc,
				debtorsmaster.currcode,
				currencies.decimalplaces AS currdecimalplaces
			FROM debtortrans INNER JOIN debtorsmaster
			ON debtortrans.debtorno=debtorsmaster.debtorno
			INNER JOIN systypes
			ON debtortrans.type=systypes.typeid
			INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			WHERE (debtortrans.type=12 OR debtortrans.type=11)
			AND debtortrans.settled=0
			AND debtortrans.ovamount<0
			ORDER BY debtorsmaster.name";
	$result = DB_query($SQL,$db);
	$NoOfUnallocatedTrans = DB_num_rows($result);
	$CurrentTransaction = 1;
	$CurrentDebtor = '';
	echo '<table class="selection">';
	echo $TableHeader;

	$k=0;
	while ($myrow = DB_fetch_array($result)) {

		$AllocateLink = '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'). '?AllocTrans=' . $myrow['id'] . '">' . _('Allocate') . '</a>';

		if ( $CurrentDebtor != $myrow['debtorno'] ) {
			if ( $CurrentTransaction > 1 ) {
				echo '<tr class="OddTableRows">
						<td colspan="7" class="number"><b>' . locale_number_format($Balance,$CurrDecimalPlaces) .'</b></td>
						<td><b>' . $CurrCode . '</b></td>
						<td><b>' . _('Balance') . '</b></td>
					</tr>';
			}

			$Balance = 0;
			$CurrentDebtor = $myrow['debtorno'];

			$BalSQL= "SELECT SUM(ovamount+ovgst+ovfreight+ovdiscount-alloc) as total
						FROM debtortrans
						WHERE (type=12 OR type=11)
						AND debtorno='" . $myrow['debtorno'] . "'";
			$BalResult = DB_query($BalSQL,$db);
			$BalRow = DB_fetch_array($BalResult);
			$Balance = $BalRow['total'];
		}
		$CurrentTransaction ++;
		$CurrCode = $myrow['currcode'];
		$CurrDecimalPlaces = $myrow['currdecimalplaces'];
		if (isset($Balance) and abs($Balance) < -0.01 ) {
			$AllocateLink = '&nbsp;';
		}

		if ($k==1) {
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo  '<tr class="OddTableRows">';;
			$k++;
		}

		echo 	'<td>' . $myrow['typename'] .'</td>
				<td>' . $myrow['name'] . '</td>
				<td>' . $myrow['debtorno'] . '</td>
				<td>' . $myrow['transno'] . '</td>
				<td>' . ConvertSQLDate($myrow['trandate']) . '</td>
				<td class="number">' . locale_number_format($myrow['total'],$CurrDecimalPlaces) . '</td>
				<td class="number">' . locale_number_format($myrow['total']-$myrow['alloc'],$CurrDecimalPlaces) . '</td>
				<td>' . $CurrCode . '</td>
				<td>' . $AllocateLink . '</td>
				</tr>';

	} //end loop around unallocated receipts and credit notes

	if (!isset($Balance)) {
		$Balance=0;
	}
	if ($NoOfUnallocatedTrans == 0) {
		prnMsg(_('There are no allocations to be done'),'info');
	} else {
		echo '<tr class="OddTableRows">
				<td colspan="7" class="number"><b>' . locale_number_format($Balance,$CurrDecimalPlaces) .'</b></td>
				<td><b>' . $CurrCode . '</b></td>
				<td><b>' . _('Balance') . '</b></td>
			</tr>';
	}
	echo '</table>
		<br />';
}

include('includes/footer.inc');

?>