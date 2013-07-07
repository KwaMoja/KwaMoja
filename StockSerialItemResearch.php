<?php
/* $Id$*/

include('includes/session.inc');
$Title = _('Serial Item Research');
include('includes/header.inc');

echo '<p class="page_title_text noPrint" >
		<img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Inventory') . '" alt="" /><b>' . $Title. '</b>
	  </p>';

//validate the submission
if (isset($_POST['serialno'])) {
	$SerialNo = trim($_POST['serialno']);
} elseif(isset($_GET['serialno'])) {
	$SerialNo = trim($_GET['serialno']);
} else {
	$SerialNo = '';
}

echo '<div class="centre">
<br />
<form onSubmit="return VerifyForm(this);" id="SerialNoResearch" method="post" class="noPrint" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo  _('Serial Number') .': <input id="serialno" type="text" name="serialno" size="21" minlength="1" maxlength="20" value="'. $SerialNo . '" /> &nbsp;<input type="submit" name="submit" value="' . _('Search') . '" />
<br />
</div>
</form>';

echo '<script  type="text/javascript">
		document.getElementById("serialno").focus();
	</script>';

if ($SerialNo != '') {
	//the point here is to allow a semi fuzzy search, but still keep someone from killing the db server
	if (mb_strstr($SerialNo,'%')){
		while(mb_strstr($SerialNo,'%%'))	{
			$SerialNo = str_replace('%%','%',$SerialNo);
		}
		if (mb_strlen($SerialNo) < 11){
			$SerialNo = str_replace('%','',$SerialNo);
			prnMsg('You can not use LIKE with short numbers. It has been removed.','warn');
		}
	}
	if ($_SESSION['RestrictLocations']==0) {
		$SQL = "SELECT stockserialitems.serialno,
						stockserialitems.stockid,
						stockserialitems.quantity AS CurInvQty,
						stockserialmoves.moveqty,
						stockmoves.type,
						systypes.typename,
						stockmoves.transno,
						stockmoves.loccode,
						locations.locationname,
						stockmoves.trandate,
						stockmoves.debtorno,
						stockmoves.branchcode,
						stockmoves.reference,
						stockmoves.qty AS TotalMoveQty
					FROM stockserialitems
					INNER JOIN stockserialmoves
						ON stockserialitems.serialno = stockserialmoves.serialno
						AND stockserialitems.stockid=stockserialmoves.stockid
					INNER JOIN stockmoves
						ON stockserialmoves.stockmoveno = stockmoves.stkmoveno
						AND stockserialitems.loccode=stockmoves.loccode
					INNER JOIN systypes
						ON stockmoves.type=systypes.typeid
					INNER JOIN locations
						on stockmoves.loccode = locations.loccode
					WHERE stockserialitems.serialno " . LIKE . " '" . $SerialNo . "'
					ORDER BY stkmoveno";
	} else {
		$SQL = "SELECT stockserialitems.serialno,
						stockserialitems.stockid,
						stockserialitems.quantity AS CurInvQty,
						stockserialmoves.moveqty,
						stockmoves.type,
						systypes.typename,
						stockmoves.transno,
						stockmoves.loccode,
						locations.locationname,
						stockmoves.trandate,
						stockmoves.debtorno,
						stockmoves.branchcode,
						stockmoves.reference,
						stockmoves.qty AS TotalMoveQty
					FROM stockserialitems
					INNER JOIN stockserialmoves
						ON stockserialitems.serialno = stockserialmoves.serialno
						AND stockserialitems.stockid=stockserialmoves.stockid
					INNER JOIN stockmoves
						ON stockserialmoves.stockmoveno = stockmoves.stkmoveno
						AND stockserialitems.loccode=stockmoves.loccode
					INNER JOIN systypes
						ON stockmoves.type=systypes.typeid
					INNER JOIN locations
						ON stockmoves.loccode = locations.loccode
					INNER JOIN www_users
						ON locations.loccode=www_users.defaultlocation
					WHERE stockserialitems.serialno " . LIKE . " '" . $SerialNo . "'
						AND www_users.userid='" . $_SESSION['UserID'] . "'
					ORDER BY stkmoveno";
	}
	$result = DB_query($SQL,$db);

	if (DB_num_rows($result) == 0){
		prnMsg( _('No History found for Serial Number'). ': <b>'.$SerialNo.'</b>' , 'warn');
	} else {
		echo '<h4>'. _('Details for Serial Item').': <b>'.$SerialNo.'</b><br />'. _('Length').'='.mb_strlen($SerialNo).'</h4>';
		echo '<table class="selection">';
		echo '<tr>
				<th>' . _('StockID') . '</th>
				<th>' . _('CurInvQty') . '</th>
				<th>' . _('Move Qty') . '</th>
				<th>' . _('Move Type') . '</th>
				<th>' . _('Trans #') . '</th>
				<th>' . _('Location') . '</th>
				<th>' . _('Date') . '</th>
				<th>' . _('DebtorNo') . '</th>
				<th>' . _('Branch') . '</th>
				<th>' . _('Move Ref') . '</th>
				<th>' . _('Total Move Qty') . '</th>
			</tr>';
		while ($myrow=DB_fetch_row($result)) {
			printf('<tr>
					<td>%s<br />%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s (%s)</td>
					<td class="number">%s</td>
					<td>%s - %s</td>
					<td>%s &nbsp;</td>
					<td>%s &nbsp;</td>
					<td>%s &nbsp;</td>
					<td>%s &nbsp;</td>
					<td class="number">%s</td>
					</tr>',
					$myrow[1],
					$myrow[0],
					$myrow[2],
					$myrow[3],
					$myrow[5], $myrow[4],
					$myrow[6],
					$myrow[7], $myrow[8],
					$myrow[9],
					$myrow[10],
					$myrow[11],
					$myrow[12],
					$myrow[13]
				);
		} //END WHILE LIST LOOP
		echo '</table>';
	} // ELSE THERE WHERE ROWS
}//END OF POST IS SET
echo '</div>';

include('includes/footer.inc');
?>