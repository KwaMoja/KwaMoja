<?php


include('includes/session.inc');
$Title = _('Recalculation of GL Balances in Chart Details Table');
include('includes/header.inc');

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (!isset($_POST['FromPeriod'])) {

	/*Show a form to allow input of criteria for TB to show */
	echo '<table>
			 <tr>
				 <td>' . _('Select Period From') . ':</td>
				 <td><select name="FromPeriod">';

	$SQL = "SELECT periodno,
				   lastdate_in_period
				FROM periods ORDER BY periodno";
	$Periods = DB_query($SQL);

	while ($MyRow = DB_fetch_array($Periods)) {
		echo '<option value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
	}

	echo '</select></td>
			 </tr>
			 </table>';

	echo '<div class="centre"><input type="submit" name="recalc" value="' . _('Do the Recalculation') . '" onclick="return MakeConfirm(\'' . _('Are you sure you wish to re-post all general ledger transactions since the selected period this can take some time?') . '\');" /></div>
	</form>';

} else {

	/* Zeroise the whole table */
	$SQL = "UPDATE chartdetails SET actual=0, bfwd=0";
	$Result = DB_query($SQL);

	/* Then get the list of all GL codes */
	$SQL = "SELECT accountcode FROM chartmaster";
	$GLCodesResult = DB_query($SQL);

	/* and cycle through each code */
	while ($GLCodes = DB_fetch_array($GLCodesResult)) {
		/* Fetch the periods */
		$SQL = "SELECT period
					FROM chartdetails
					WHERE accountcode='" . $GLCodes['accountcode'] . "'
					ORDER BY period";
		$PeriodResult = DB_query($SQL);
		$BalanceBroughtForward = 0;
		while ($Periods = DB_fetch_array($PeriodResult)) {
			/* Get the actual period amount */
			$SQL = "SELECT SUM(amount) AS actual FROM gltrans WHERE account='" . $GLCodes['accountcode'] . "' AND periodno='" . $Periods['period'] . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_array($Result);

			/* Update the chartdetails table */
			$SQL = "UPDATE chartdetails SET actual='" . $MyRow['actual'] . "',
											bfwd=" . $BalanceBroughtForward ."
										WHERE accountcode='" . $GLCodes['accountcode'] . "'
											AND period='" . $Periods['period'] . "'";
			$Result = DB_query($SQL);

			/* Calculate the balance carried forward */
			$BalanceBroughtForward += $MyRow['actual'];
		}
	}
	prnMsg(_('All general ledger postings have been reposted from period') . ' ' . $_POST['FromPeriod'], 'success');
}
include('includes/footer.inc');
?>