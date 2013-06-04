<?php

$PageSecurity = 15;

include('includes/session.inc');

$Title = _('Run stock ranking analysis');

include('includes/header.inc');

echo '<p class="page_title_text noPrint" >
		<img src="' . $RootPath . '/css/' . $Theme . '/images/rank.png" title="' . $Title . '" alt="' . $Title . '" />' . ' ' . $Title . '
	</p>';

if (isset($_POST['Submit'])) {

	$result = DB_query("DELETE FROM abcstock WHERE groupid='" . $_POST['GroupID'] . "'", $db);

	/*Firstly get the parameters needed */
	$sql = "SELECT groupid,
					groupname,
					methodid,
					apercentage,
					bpercentage,
					cpercentage,
					zerousage,
					months
				FROM abcgroups
				WHERE groupid='" . $_POST['GroupID'] . "'";
	$result = DB_query($sql, $db);
	$Parameters = DB_fetch_array($result);

	$result = DB_query("DROP TABLE IF EXISTS tempabc",$db);
	$sql = "CREATE TEMPORARY TABLE tempabc (
				stockid varchar(20),
				consumption INT(11)) DEFAULT CHARSET=utf8";
	$result = DB_query($sql,$db,_('Create of tempabc failed because'));

	$CurrentPeriod = GetPeriod(date($_SESSION['DefaultDateFormat']), $db);

	$sql = "INSERT INTO tempabc
					(SELECT stockid,
					-SUM(qty)*price AS consumption
				FROM stockmoves
				WHERE prd<='" . $CurrentPeriod . "'
					AND prd>='" . ($CurrentPeriod - $Parameters['months']) . "'
					AND (type=10 OR type=11 OR type=28)
				GROUP BY stockid
				ORDER BY consumption)";
	$ErrMsg = _('Problem populating tempabc table');
	$result = DB_query($sql, $db, $ErrMsg);

	$sql = "SELECT COUNT(stockid) AS numofitems FROM tempabc WHERE consumption<>0";
	$result = DB_query($sql, $db, _('Problem counting items'));
	$myrow = DB_fetch_array($result);
	$NumberOfItems = $myrow['numofitems'];
	$AItems = round($NumberOfItems * $Parameters['apercentage'] / 100, 0);
	$BItems = round($NumberOfItems * $Parameters['bpercentage'] / 100, 0);
	$CItems = $NumberOfItems - $AItems - $BItems;

	$sql = "SELECT stockid,
					consumption
				FROM tempabc
				WHERE consumption<>0
				ORDER BY consumption DESC";
	$result = DB_query($sql, $db);

	$i = 1;
	while ($myrow=DB_fetch_array($result)) {
		switch($i) {
			case ($i<=$AItems):
				$InsertSQL = "INSERT INTO abcstock VALUES(
															'" . $_POST['GroupID'] . "',
															'" . $myrow['stockid'] . "',
															'A'
														)";
				$InsertResult = DB_query($InsertSQL, $db);
				break;
			case ($i>$AItems and $i<=($AItems + $BItems)):
				$InsertSQL = "INSERT INTO abcstock VALUES(
															'" . $_POST['GroupID'] . "',
															'" . $myrow['stockid'] . "',
															'B'
														)";
				$InsertResult = DB_query($InsertSQL, $db);
				break;
			default:
				$InsertSQL = "INSERT INTO abcstock VALUES(
															'" . $_POST['GroupID'] . "',
															'" . $myrow['stockid'] . "',
															'C'
														)";
				$InsertResult = DB_query($InsertSQL, $db);
		}
		$i++;
	}
	$sql = "INSERT INTO abcstock (SELECT '" . $_POST['GroupID'] . "',
										stockid,
										'" . $Parameters['zerousage'] . "'
									FROM tempabc
									WHERE consumption=0)";
	$result = DB_query ($sql, $db);

	$sql = "INSERT INTO abcstock (SELECT '" . $_POST['GroupID'] . "',
										stockmaster.stockid,
										'" . $Parameters['zerousage'] . "'
									FROM stockmaster
									LEFT JOIN tempabc
										ON stockmaster.stockid=tempabc.stockid
									WHERE consumption is NULL)";
	$result = DB_query ($sql, $db);

	$result = DB_query("DROP TABLE IF EXISTS tempabc",$db);

	prnMsg( _('The ABC analysis has been successfully run'), 'success');
} else {

	echo '<form onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint" id="ABCAnalysis">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table>
			<tr>
				<th colspan="2">
					<h3>' . _('Ranking Analysis Details') . '</h3>
				</th>
			</tr>
			<tr class="EvenTableRows">
				<td>' . _('Ranking group') . '</td>
				<td><select name="GroupID">';

	$sql = "SELECT groupid,
					groupname
				FROM abcgroups";
	$result = DB_query($sql, $db);

	echo '<option value=""></option>';
	while ($myrow = DB_fetch_array($result)) {
		echo '<option value="' . $myrow['groupid'] . '">' . $myrow['groupname'] . '</option>';
	}

	echo '</select>
			</td>
		</tr>';

	echo '</table>
		<div class="centre"><input type="submit" name="Submit" value="Run" />
	</form>';

	prnMsg( _('Please note if you run an ABC analysis against a ranking group that has been used before, that analysis will be deleted and replaced by this one'), 'warn');
}

include('includes/footer.inc');

?>