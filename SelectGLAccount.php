<?php
/* $Id$*/

include('includes/session.inc');

$Title = _('Search GL Accounts');

$ViewTopic= "GeneralLedger";
$BookMark = "GLAccountInquiry";
include('includes/header.inc');

$msg='';
unset($result);

if (isset($_POST['Search'])){

	if (mb_strlen($_POST['Keywords']>0) and mb_strlen($_POST['GLCode'])>0) {
		$msg=_('Account name keywords have been used in preference to the account code extract entered');
	}
	if ($_POST['Keywords']=='' and $_POST['GLCode']=='') {
			$SQL = "SELECT chartmaster.accountcode,
					chartmaster.accountname,
					chartmaster.group_,
					CASE WHEN accountgroups.pandl!=0 THEN '" . _('Profit and Loss') . "' ELSE '" . _('Balance Sheet') ."' END AS pl
					FROM chartmaster,
						accountgroups
					WHERE chartmaster.group_=accountgroups.groupname
					ORDER BY chartmaster.accountcode";
	}
	elseif (mb_strlen($_POST['Keywords'])>0) {
			//insert wildcard characters in spaces
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

			$SQL = "SELECT chartmaster.accountcode,
					chartmaster.accountname,
					chartmaster.group_,
					CASE WHEN accountgroups.pandl!=0
						THEN '" . _('Profit and Loss') . "'
						ELSE '" . _('Balance Sheet') . "' END AS pl
				FROM chartmaster,
					accountgroups
				WHERE chartmaster.group_ = accountgroups.groupname
				AND accountname " . LIKE  . " '$SearchString'
				ORDER BY accountgroups.sequenceintb,
					chartmaster.accountcode";

		} elseif (mb_strlen($_POST['GLCode'])>0 and is_numeric($_POST['GLCode'])){

			$SQL = "SELECT chartmaster.accountcode,
					chartmaster.accountname,
					chartmaster.group_,
					CASE WHEN accountgroups.pandl!=0 THEN '" . _('Profit and Loss') . "' ELSE '" . _('Balance Sheet') ."' END AS pl
					FROM chartmaster,
						accountgroups
					WHERE chartmaster.group_=accountgroups.groupname
					AND chartmaster.accountcode >= '" . $_POST['GLCode'] . "'
					ORDER BY chartmaster.accountcode";
		} elseif(!is_numeric($_POST['GLCode'])){
			prnMsg(_('The general ledger code specified must be numeric - all account numbers must be numeric'),'warn');
			unset($SQL);
		}

		if (isset($SQL) and $SQL!=''){
			$result = DB_query($SQL, $db);
		}
} //end of if search

if (!isset($AccountID)) {


	echo '<p class="page_title_text noPrint" ><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') .
		'" alt="' . _('Search for General Ledger Accounts') . '" />' . ' ' . _('Search for General Ledger Accounts') . '</p>';
	echo '<br />
		<form onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post" class="noPrint">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if(mb_strlen($msg)>1){
		prnMsg($msg,'info');
	}

	echo '<table class="selection" summary="' . _('Criteria for inquiry') . '">
		<tr>
			<td>' . _('Enter extract of text in the Account name') .':</td>
			<td><input type="text" name="Keywords" size="20" minlength="0" maxlength="25" /></td>
			<td><b>' .  _('OR') . '</b></td>
			<td>' . _('Enter Account No. to search from') . ':</td>
			<td><input type="text" name="GLCode" size="15" minlength="0" maxlength="18" class="number" /></td>
		</tr>
		</table>
		<br />';

	echo '<div class="centre">
			<input type="submit" name="Search" value="' . _('Search Now') . '" />
			<input type="submit" name="reset" value="' . _('Reset') .'" />
		</div></form>';

	if (isset($result) and DB_num_rows($result)>0) {

	echo '<br />
		<form onSubmit="return VerifyForm(this);" action="GLAccountInquiry.php" method="post" class="noPrint">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<br /><table class="selection" summary="' . _('List of GL Accounts') . '">';

		$TableHeader = '<tr>
							<th>' . _('Code') . '</th>
							<th>' . _('Account Name') . '</th>
							<th>' . _('Group') . '</th>
							<th>' . _('Account Type') . '</th>
						</tr>';

		echo $TableHeader;

		$j = 1;

		while ($myrow=DB_fetch_array($result)) {

			printf('<tr>
					<td><input type="submit" name="Select" value="%s" /></td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>',
					$myrow['accountcode'],
					htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false),
					$myrow['group_'],
					$myrow['pl']);

			$j++;
			if ($j == 12){
				$j=1;
				echo $TableHeader;

			}
//end of page full new headings if
		}
//end of while loop

		echo '</table>';

	}
//end if results to show

	echo '</div>
		  </form>';

} //end AccountID already selected

include('includes/footer.inc');
?>