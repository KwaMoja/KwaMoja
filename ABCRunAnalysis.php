<?php

include('includes/session.inc');

$Title = _('Run stock ranking analysis');

include('includes/header.inc');

echo '<p class="page_title_text" >
		<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/rank.png" title="' . $Title . '" alt="' . $Title . '" />' . ' ' . $Title . '
	</p>';

if (isset($_POST['Submit'])) {

	if (!isset($_POST['GroupID']) or $_POST['GroupID']=='') {
		prnMsg( _('You must select an analysis group to use'), 'error');
		echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('Return to selection criteria') . '</a>';
		include('includes/footer.inc');
		exit;
	}

	$Result = DB_query("DELETE FROM abcstock WHERE groupid='" . $_POST['GroupID'] . "'");

	/*Firstly get the parameters needed */
	$SQL = "SELECT groupid,
					groupname,
					methodid,
					apercentage,
					bpercentage,
					cpercentage,
					zerousage,
					months
				FROM abcgroups
				WHERE groupid='" . $_POST['GroupID'] . "'";
	$Result = DB_query($SQL);
	$Parameters = DB_fetch_array($Result);

	$Result = DB_query("DROP TABLE IF EXISTS tempabc");
	$SQL = "CREATE TEMPORARY TABLE tempabc (
				stockid varchar(20),
				consumption INT(11)) DEFAULT CHARSET=utf8";
	$Result = DB_query($SQL, _('Create of tempabc failed because'));

	$CurrentPeriod = GetPeriod(date($_SESSION['DefaultDateFormat']));

	$SQL = "INSERT INTO tempabc
					(SELECT stockid,
					-SUM(qty)*price AS consumption
				FROM stockmoves
				WHERE prd<='" . $CurrentPeriod . "'
					AND prd>='" . ($CurrentPeriod - $Parameters['months']) . "'
					AND (type=10 OR type=11 OR type=28)
				GROUP BY stockid
				ORDER BY consumption)";
	$ErrMsg = _('Problem populating tempabc table');
	$Result = DB_query($SQL, $ErrMsg);

	$SQL = "SELECT COUNT(stockid) AS numofitems FROM tempabc WHERE consumption<>0";
	$Result = DB_query($SQL, _('Problem counting items'));
	$MyRow = DB_fetch_array($Result);
	$NumberOfItems = $MyRow['numofitems'];
	$AItems = round($NumberOfItems * $Parameters['apercentage'] / 100, 0);
	$BItems = round($NumberOfItems * $Parameters['bpercentage'] / 100, 0);
	$CItems = $NumberOfItems - $AItems - $BItems;

	$SQL = "SELECT stockid,
					consumption
				FROM tempabc
				WHERE consumption<>0
				ORDER BY consumption DESC";
	$Result = DB_query($SQL);

	$i = 1;
	while ($MyRow = DB_fetch_array($Result)) {
		switch ($i) {
			case ($i <= $AItems):
				$InsertSQL = "INSERT INTO abcstock VALUES(
															'" . $_POST['GroupID'] . "',
															'" . $MyRow['stockid'] . "',
															'A'
														)";
				$InsertResult = DB_query($InsertSQL);
				break;
			case ($i > $AItems and $i <= ($AItems + $BItems)):
				$InsertSQL = "INSERT INTO abcstock VALUES(
															'" . $_POST['GroupID'] . "',
															'" . $MyRow['stockid'] . "',
															'B'
														)";
				$InsertResult = DB_query($InsertSQL);
				break;
			default:
				$InsertSQL = "INSERT INTO abcstock VALUES(
															'" . $_POST['GroupID'] . "',
															'" . $MyRow['stockid'] . "',
															'C'
														)";
				$InsertResult = DB_query($InsertSQL);
		}
		++$i;
	}
	$SQL = "INSERT INTO abcstock (SELECT '" . $_POST['GroupID'] . "',
										stockid,
										'" . $Parameters['zerousage'] . "'
									FROM tempabc
									WHERE consumption=0)";
	$Result = DB_query($SQL);

	$SQL = "INSERT INTO abcstock (SELECT '" . $_POST['GroupID'] . "',
										stockmaster.stockid,
										'" . $Parameters['zerousage'] . "'
									FROM stockmaster
									LEFT JOIN tempabc
										ON stockmaster.stockid=tempabc.stockid
									WHERE consumption is NULL)";
	$Result = DB_query($SQL);

	$Result = DB_query("DROP TABLE IF EXISTS tempabc");

	prnMsg(_('The ABC analysis has been successfully run'), 'success');
} else {

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" id="ABCAnalysis">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table>
			<tr>
				<th colspan="2">
					<h3>' . _('Ranking Analysis Details') . '</h3>
				</th>
			</tr>
			<tr class="EvenTableRows">
				<td>' . _('Ranking group') . '</td>
				<td><select required="required" name="GroupID">';

	$SQL = "SELECT groupid,
					groupname
				FROM abcgroups";
	$Result = DB_query($SQL);

	echo '<option value=""></option>';
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="' . $MyRow['groupid'] . '">' . $MyRow['groupname'] . '</option>';
	}

	echo '</select>
			</td>
		</tr>';

	echo '</table>
		<div class="centre"><input type="submit" name="Submit" value="Run" />
	</form>';

	prnMsg(_('Please note if you run an ABC analysis against a ranking group that has been used before, that analysis will be deleted and replaced by this one'), 'warn');
}

include('includes/footer.inc');

?>