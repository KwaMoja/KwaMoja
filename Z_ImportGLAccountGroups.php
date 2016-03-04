<?php
/* $Id$*/

include('includes/session.inc');
$Title = _('Import Chart of Accounts');
include('includes/header.inc');
include('xmlrpc/lib/xmlrpc.inc');
include('api/api_errorcodes.php');

$user = $_SESSION['UserID'];
$SQL = "SELECT password FROM www_users WHERE userid='" . $user . "'";
$Result = DB_query($SQL);
$MyRow = DB_fetch_array($Result);
$password = $MyRow[0];

$ServerURL = "http://localhost/KwaMoja/api/api_xml-rpc.php";
$DebugLevel = 2; //Set to 0,1, or 2 with 2 being the highest level of debug info


if (isset($_POST['update'])) {
	$fp = fopen($_FILES['ImportFile']['tmp_name'], "r");
	$buffer = fgets($fp, 4096);
	$FieldNames = explode(',', $buffer);
	$SuccessStyle = 'style="color:green; font-weight:bold"';
	$FailureStyle = 'style="color:red; font-weight:bold"';
	echo '<table>
			<tr>
				<th>' . _('Account Group') . '</th>
				<th>' . _('Result') . '</th>
				<th>' . _('Comments') . '</th>
			</tr>';
	$successes = 0;
	$failures = 0;
	while (!feof($fp)) {
		$buffer = fgets($fp, 4096);
		$FieldValues = explode(',', $buffer);
		if ($FieldValues[0] != '') {
			$SizeOfFieldValues = sizeOf($FieldValues);
			for ($i = 0; $i < $SizeOfFieldValues; $i++) {
				$AccountGroupDetails[$FieldNames[$i]] = $FieldValues[$i];
			}
			$accountgroup = php_xmlrpc_encode($AccountGroupDetails);
			$user = new xmlrpcval($user);
			$password = new xmlrpcval($password);

			$Msg = new xmlrpcmsg($APIServer . ".xmlrpc_InsertGLAccountGroup", array(
				$accountgroup,
				$user,
				$password
			));

			$client = new xmlrpc_client($ServerURL);
			$client->setDebug($DebugLevel);

			$response = $client->send($Msg);
			$answer = php_xmlrpc_decode($response->value());
			if ($answer[0] == 0) {
				echo '<tr ' . $SuccessStyle . '>
						<td>' . $AccountGroupDetails['groupname'] . '</td>
						<td>' . 'Success' . '</td>
					</tr>';
				$successes++;
			} else {
				echo '<tr ' . $FailureStyle . '>
						<td>' . $AccountGroupDetails['groupname'] . '</td>
						<td>' . 'Failure' . '</td>
						<td>';
				$SizeOfAnswer = sizeOf($answer);
				for ($i = 0; $i < $SizeOfAnswer; $i++) {
					echo 'Error no ' . $answer[$i] . ' - ' . $ErrorDescription[$answer[$i]] . '<br />';
				}
				echo '</td>
					</tr>';
				$failures++;
			}
		}
		unset($AccountDetails);
	}
	echo '<tr>
			<td>' . $successes . _(' records successfully imported') . '</td>
		</tr>
		<tr>
			<td>' . $failures . _(' records failed to import') . '</td>
		</tr>
	</table>';
	fclose($fp);
} else {
	prnMsg(_('Select a csv file containing the details of the account sections that you wish to import') . '. ' . '<br />' . _('The first line must contain the field names that you wish to import. ') . '<a href ="Z_DescribeTable.php?table=accountsection">' . _('The field names can be found here') . '</a>', 'info');
	echo '<form id="ItemForm" enctype="multipart/form-data" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<div class="centre">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table><tr><td>' . _('File to import') . '</td>' . '<td><input type="file" id="ImportFile" name="ImportFile" /></td></tr></table>';
	echo '<div class="centre"><input type="submit" name="update" value="Process" /></div>';
	echo '</div>
		  </form>';
}

include('includes/footer.inc');

?>