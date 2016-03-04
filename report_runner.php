#!/usr/bin/php
<?php
//--------------------------------------------------------------------
// report_runner.php
// This program is designed to run reports in batch command mode for
//
// --------------------------------------------------------------------
// Written by Alan B Jones (mor3ton@yahoo.com)
// (c) alan jones 2006.
// (c) 2006 logic works Ltd and others
// licenced under the terms of the GPL V(2)
// if you want to know the details of the use of this software
// and how you are licenced to use it under the terms of the
// see here http://www.gnu.org/licenses/gpl.txt
//--------------------------------------------------------------------
//you must tell the script where you main installation is located
//Rememeber this is different for each location

$usage = "USAGE\n" . $argv[0] . ":\n" . "     -r reportnumber (the number of the report)\n" . "     -n reportname   (the name you want to give the report)\n" . "     -e emailaddress[;emailaddress;emailaddres...] (who you want to send it to)\n" . "     -d database name (the mysql db to use for the data for the report)\n" . "     [-t reporttext ]  (some words you want to send with the report-optional)\n" . "     [ -H HOMEDIR]  (the home directory for" .  $ProjectName . " - or edit the php file)\n";

if ($argc < 7) {
	echo $usage;
	exit;
}
for ($i = 1; $i < $argc; $i++) {
	switch ($argv[$i]) {
		case '-r':
			++$i;
			$reportnumber = $argv[$i];
			break;
		case '-n':
			++$i;
			$reportname = $argv[$i];
			break;
		case '-e':
			++$i;
			$emailaddresses = $argv[$i];
			break;
		case '-d':
			++$i;
			$DatabaseName = $argv[$i];
			break;
		case '-H':
			++$i;
			$HOMEDIR = $argv[$i];
			break;
		case '-t':
			++$i;
			$Mailtext = $argv[$i];
			break;
		default:
			echo "unknown option" . $argv[$i] . "\n";
			echo $usage;
			exit;
			break;
	}
}
// test the existance
if (($reportname == "") or ($reportnumber == "") or ($emailaddresses == "")) {
	echo $usage;
	exit;
}
// do we have a variable
if ($HOMEDIR != "") {
	$home = $HOMEDIR;
}

if ($home == "") {
	echo "THe home folder is not set in this file or -H is not set";
}
// change directory to the home directory to get all the includes to work nicely
chdir($home);

// get me the report name from the command line

$_GET['ReportID'] = $reportnumber;
$Recipients = explode(";", $emailaddresses);
//wrap in angles so that mail can accept it
for ($i = 0; $i < count($Recipients); $i++) {
	$Recipient[$i] = "<" . $Recipient[$i] . ">";
}

$AllowAnyone = true;
include('includes/session.inc');

include('includes/ConstructSQLForUserDefinedSalesReport.inc');
include('includes/PDFSalesAnalysis.inc');

include('includes/htmlMimeMail.php');
$Mail = new htmlMimeMail();

if ($Counter > 0) {
	/* the number of lines of the sales report is more than 0  ie there is a report to send! */
	$PDFcode = $PDF->output();
	$fp = fopen($_SESSION['reports_dir'] . "/" . $reportname, "wb");
	fwrite($fp, $PDFcode);
	fclose($fp);

	$attachment = $Mail->getFile($_SESSION['reports_dir'] . "/" . $reportname);
	$Mail->setText($Mailtext . "\nPlease find herewith " . $reportname . "  report");
	$Mail->setSubject($reportname . " Report");
	$Mail->addAttachment($attachment, $reportname, 'application/pdf');
	if ($_SESSION['SmtpSetting'] == 0) {
		$Mail->setFrom("");
		$Result = $Mail->send($Recipients);
	} else {
		$Result = SendmailBySmtp($Mail, $Recipients);
	}

} else {
	$Mail->setText("Error running automated sales report number $ReportID");
	if ($_SESSION['SmtpSetting'] == 0) {
		$Mail->setFrom("Do_not_reply_" . $_SESSION['CompanyRecord']['coyname'] . "<" . $_SESSION['CompanyRecord']['email'] . ">");
		$Result = $Mail->send($Recipients);
	} else {
		$Result = SendmailBySmtp($Mail, $Recipients);
	}
	if ($_SESSION['SmtpSetting'] == 0) {
		$Mail->setFrom("Do_not_reply_" . $_SESSION['CompanyRecord']['coyname'] . "<" . $_SESSION['CompanyRecord']['email'] . ">");
		$Result = $Mail->send($Recipients);
	} else {
		$Result = SendmailBySmtp($Mail, $Recipients);
	}
}

?>