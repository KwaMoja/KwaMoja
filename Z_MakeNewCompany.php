<?php

include('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');

/* Was the Cancel button pressed the last time through ? */

if (isset($_POST['EnterCompanyDetails'])) {

	header('Location:' . $RootPath . '/CompanyPreferences.php');
	exit;
}

$Title = _('Make New Company Database Utility');

include('includes/header.inc');

/* Your webserver user MUST have read/write access to here,
otherwise you'll be wasting your time */
if (!is_writeable('./companies/')) {
	prnMsg(_('The web-server does not appear to be able to write to the companies directory to create the required directories for the new company and to upload the logo to. The system administrator will need to modify the permissions on your installation before a new company can be created'), 'error');
	include('includes/footer.inc');
	exit;
}


if (isset($_POST['submit']) and isset($_POST['NewCompany'])) {

	if (mb_strlen($_POST['NewCompany']) > 32 OR ContainsIllegalCharacters($_POST['NewCompany'])) {
		prnMsg(_('Company abbreviations must not contain spaces,') . ' \& ' . _('or') . ' " ' . _('or') . ' \'', 'error');
	} else {

		$_POST['NewCompany'] = strtolower($_POST['NewCompany']);
		echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
		echo '<div class="centre">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		/* check for directory existence */
		if (!file_exists('./companies/' . $_POST['NewCompany']) AND (isset($_FILES['LogoFile']) AND $_FILES['LogoFile']['name'] != '')) {

			$Result = $_FILES['LogoFile']['error'];
			$UploadTheLogo = 'Yes'; //Assume all is well to start off with
			$FileName = './companies/' . $_POST['NewCompany'] . '/logo.jpg';

			//But check for the worst
			if (mb_strtoupper(mb_substr(trim($_FILES['LogoFile']['name']), mb_strlen($_FILES['LogoFile']['name']) - 3)) != 'JPG') {
				prnMsg(_('Only jpg files are supported - a file extension of .jpg is expected'), 'warn');
				$UploadTheLogo = 'No';
			} elseif ($_FILES['LogoFile']['size'] > ($_SESSION['MaxImageSize'] * 1024)) { //File Size Check
				prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'], 'warn');
				$UploadTheLogo = 'No';
			} elseif ($_FILES['LogoFile']['type'] == "text/plain") { //File Type Check
				prnMsg(_('Only graphics files can be uploaded'), 'warn');
				$UploadTheLogo = 'No';
			} elseif (file_exists($FileName)) {
				prnMsg(_('Attempting to overwrite an existing item image'), 'warn');
				$Result = unlink($FileName);
				if (!$Result) {
					prnMsg(_('The existing image could not be removed'), 'error');
					$UploadTheLogo = 'No';
				}
			}

			if ($_POST['CreateDB'] == TRUE) {
				/* Need to read in the sql script and process the queries to initate a new DB */

				$Result = DB_query('CREATE DATABASE ' . $_POST['NewCompany']);
				DB_select_database($_POST['NewCompany']);

				$ScriptFileEntries = sizeof($SQLScriptFile);
				$ErrMsg = _('The script to create the new company database failed because');
				$SQL = '';
				$InAFunction = false;

				for ($i = 0; $i <= $ScriptFileEntries; $i++) {

					$SQLScriptFile[$i] = trim($SQLScriptFile[$i]);

					if (mb_substr($SQLScriptFile[$i], 0, 2) != '--' AND mb_substr($SQLScriptFile[$i], 0, 3) != 'USE' AND mb_strstr($SQLScriptFile[$i], '/*') == FALSE AND mb_strlen($SQLScriptFile[$i]) > 1) {

						$SQL .= ' ' . $SQLScriptFile[$i];

						//check if this line kicks off a function definition - pg chokes otherwise
						if (mb_substr($SQLScriptFile[$i], 0, 15) == 'CREATE FUNCTION') {
							$InAFunction = true;
						}
						//check if this line completes a function definition - pg chokes otherwise
						if (mb_substr($SQLScriptFile[$i], 0, 8) == 'LANGUAGE') {
							$InAFunction = false;
						}
						if (mb_strpos($SQLScriptFile[$i], ';') > 0 and !$InAFunction) {
							$SQL = mb_substr($SQL, 0, mb_strlen($SQL) - 1);
							$Result = DB_query($SQL, $ErrMsg);
							$SQL = '';
						}

					} //end if its a valid sql line not a comment
				} //end of for loop around the lines of the sql script
			} //end if CreateDB was checked

			prnMsg(_('Attempting to create the new company directories') . '.....<br />', 'info');
			$Result = mkdir('./companies/' . $_POST['NewCompany']);
			$Result = mkdir('./companies/' . $_POST['NewCompany'] . '/part_pics');
			$Result = mkdir('./companies/' . $_POST['NewCompany'] . '/EDI_Incoming_Orders');
			$Result = mkdir('./companies/' . $_POST['NewCompany'] . '/reports');
			$Result = mkdir('./companies/' . $_POST['NewCompany'] . '/EDI_Sent');
			$Result = mkdir('./companies/' . $_POST['NewCompany'] . '/EDI_Pending');
			$Result = mkdir('./companies/' . $_POST['NewCompany'] . '/FormDesigns');
			$Result = mkdir('./companies/' . $_POST['NewCompany'] . '/reportwriter');

			copy('./companies/' . $_SESSION['DatabaseName'] . '/FormDesigns/GoodsReceived.xml', './companies/' . $_POST['NewCompany'] . '/FormDesigns/GoodsReceived.xml');
			copy('./companies/' . $_SESSION['DatabaseName'] . '/FormDesigns/PickingList.xml', './companies/' . $_POST['NewCompany'] . '/FormDesigns/PickingList.xml');
			copy('./companies/' . $_SESSION['DatabaseName'] . '/FormDesigns/PurchaseOrder.xml', './companies/' . $_POST['NewCompany'] . '/FormDesigns/PurchaseOrder.xml');
			copy('./companies/' . $_SESSION['DatabaseName'] . '/FormDesigns/FGLabel.xml', './companies/' . $_POST['NewDatabase'] . '/FormDesigns/FGLabel.xml');
			copy('./companies/' . $_SESSION['DatabaseName'] . '/FormDesigns/WOPaperwork.xml', './companies/' . $_POST['NewDatabase'] . '/FormDesigns/WOPaperwork.xml');
			copy('./companies/' . $_SESSION['DatabaseName'] . '/FormDesigns/QALabel.xml', './companies/' . $_POST['NewDatabase'] . '/FormDesigns/QALabel.xml');

			/*OK Now upload the logo */
			if ($UploadTheLogo == 'Yes') {
				$Result = move_uploaded_file($_FILES['LogoFile']['tmp_name'], $FileName);
				$message = ($Result) ? _('File url') . '<a href="' . $FileName . '">' . $FileName . '</a>' : _('Something is wrong with uploading a file');
			}

		} else {
			prnMsg(_('This company cannot be added because either it already exists or no logo is being uploaded!'), 'error');
			if (isset($_FILES['LogoFile'])) {
				prnMsg('_Files[LogoFile] ' . _('is set ok'), 'info');
			} else {
				prnMsg('_FILES[LogoFile] ' . _('is not set'), 'info');
			}
			if ($_FILES['LogoFile']['name'] != '') {
				prnMsg('_FILES[LogoFile][name] ' . _('is not blank'), 'info');
			} else {
				prnMsg('_FILES[LogoFile][name] ' . _('is blank'), 'info');
			}

			echo '</div>';
			echo '</form>';
			include('includes/footer.inc');
			exit;
		}

		$_SESSION['DatabaseName'] = $_POST['NewCompany'];

		unset($_SESSION['CustomerID']);
		unset($_SESSION['SupplierID']);
		unset($_SESSION['StockID']);
		unset($_SESSION['Items']);
		unset($_SESSION['CreditItems']);

		$SQL = "UPDATE config SET confvalue='companies/" . $_POST['NewCompany'] . "/EDI__Sent' WHERE confname='EDI_MsgSent'";
		$Result = DB_query($SQL);
		$SQL = "UPDATE config SET confvalue='companies/" . $_POST['NewCompany'] . "/EDI_Incoming_Orders' WHERE confname='EDI_Incoming_Orders'";
		$Result = DB_query($SQL);
		$SQL = "UPDATE config SET confvalue='companies/" . $_POST['NewCompany'] . "/part_pics' WHERE confname='part_pics_dir'";
		$Result = DB_query($SQL);
		$SQL = "UPDATE config SET confvalue='companies/" . $_POST['NewCompany'] . "/reports' WHERE confname='reports_dir'";
		$Result = DB_query($SQL);
		$SQL = "UPDATE config SET confvalue='companies/" . $_POST['NewCompany'] . "/EDI_Pending' WHERE confname='EDI_MsgPending'";
		$Result = DB_query($SQL);

		$ForceConfigReload = true;
		include('includes/GetConfig.php');

		prnMsg(_('The new company database has been created for' . ' ' . $_POST['NewCompany'] . '. ' . _('The company details and parameters should now be set up for the new company. NB: Only a single user "demo" is defined with the password "') . $DefaultDatabase . _('" in the new company database. A new system administrator user should be defined for the new company and this account deleted immediately.')), 'info');

		echo '<p><a href="' . $RootPath . '/CompanyPreferences.php">' . _('Set Up New Company Details') . '</a>';
		echo '<p><a href="' . $RootPath . '/SystemParameters.php">' . _('Set Up Configuration Details') . '</a>';
		echo '<p><a href="' . $RootPath . '/WWW_Users.php">' . _('Set Up User Accounts') . '</a>';

		echo '</div>';
		echo '</form>';
		include('includes/footer.inc');
		exit;
	}
}

echo '<div class="centre">';
echo '<br />';
prnMsg(_('This utility will create a new company') . '<br /><br />' . _('If the company name already exists then you cannot recreate it'), 'info', _('PLEASE NOTE'));
echo '<br /></div>';
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" enctype="multipart/form-data">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table>
		<tr>
			<td>' . _('Enter up to 32 character lower case character abbreviation for the company') . '</td>
			<td><input type="text" size="33" maxlength="32" name="NewCompany" /></td>
		</tr>
		<tr>
			<td>' . _('Logo Image File (.jpg)') . ':</td><td><input type="file" required="required" id="LogoFile" name="LogoFile" /></td>
		</tr>
		<tr>
			<td>' . _('Create Database?') . '</td>
			<td><input type="checkbox" name="CreateDB" /></td>
		</tr>
	</table>';

echo '<input type="submit" name="submit" value="' . _('Proceed') . '" />';
echo '</form>';

include('includes/footer.inc');
?>