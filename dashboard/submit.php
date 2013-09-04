<?php

/* $Id$*/
$PageSecurity=0;

require_once('connect.php');


$uid = $_GET['uid'];
if (isset($_POST['Submit'])) {
	

if(empty($_POST['dashboard']))
{
	echo "You haven't selected any pages to be displayed on dashboard; PLEASE GO BACK AND SELECT PAGES.";
	
}
else 
{
$array_string=implode(",", $_POST['dashboard']);


 	
$sql1 = "select * from dashboard_users where userid = '".$uid."' ";

$result1 = mysql_query($sql1) or die ("ERROR: " . mysql_error());	



if (mysql_num_rows($result1) > 0) 

            {

				
				$update = "update dashboard_users set scripts = '".$array_string."' where userid = '".$uid."' ";
				$res = mysql_query($update) or die (mysql_error());
				try{
	mysql_query($sql1) or die(mysql_error());
	echo "<h1>Updated Successfully</h1>";
	header("Refresh: 1; url= UserSettings.php");
 }
 catch(Exception $e){
	 header("Location: UserSettings.php");
 }	
					}
			
				else 
				
			{	
	

$sql = "insert into dashboard_users (id,userid, scripts) values ('','".$uid."', '".$array_string."')";

	try{
	mysql_query($sql) or die(mysql_error());
	
	header("Location: UserSettings.php");
 }
 catch(Exception $e){
	 header("Location: UserSettings.php");
 }	
			}
			

			
			}
}

include('includes/footer.inc');

?>