<?php
/* $Revision: 1.0 $ */

include('includes/session.inc');

$Title = _('Social Security System Section');

include('includes/header.inc');

if (isset($_GET['employeeno'])) {
	$employeeno = $_GET['employeeno'];
} elseif (isset($_POST['employeeno'])) {

	$employeeno = $_POST['employeeno'];
} else {
	unset($employeeno);
}
?>
<a href="index.php">Back to Main Menu </a>
<?php

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strlen($employeeno) == 0) {
		$InputError = 1;
		prnMsg(_('The  employeeno cannot be empty'), 'error');
	}
	if (!isset($_POST['dob'])) {
		// Checking if Month, Day and Year fields have been filled
		if (($_POST['Month'] == "") or ($_POST['Day'] == "") or ($_POST['Year'] == "")) {
			prnMsg(_('The dob field must be a date in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
			$InputError = 1;
		} else {
			// Concatenating Month, Day and Year
			// for MySQL type Date (YYYY-MM-DD)
			$dob = $_POST['Month'] . "/" . $_POST['Day'] . "/" . $_POST['Year'];
			if (!is_date($dob)) {
				prnMsg(_('The dob field must be a date in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
				$InputError = 1;
			} else {
				$SQL_dob = FormatDateForSQL($dob);
			}
		}
	} else {
		$SQL_dob = FormatDateForSQL($_POST['dob']);
	}
	if ($InputError != 1) {

		if (!isset($_POST["New"])) {
			$SQL = "UPDATE prlnssftable SET
					surname='" . DB_escape_string($_POST['surname']) . "',
					othernames='" . DB_escape_string($_POST['othernames']) . "',
					dob='" . $SQL_dob . "',
					gender='" . DB_escape_string(isset($_POST['gender'])) . "',
					dateofregistration='" . isset($SQL_dateofregistration) . "',
					placeofbirth='" . DB_escape_string($_POST['placeofbirth']) . "',
					areaoffice='" . DB_escape_string($_POST['areaoffice']) . "',
					nationality='" . DB_escape_string($_POST['nationality']) . "',
					employer='" . DB_escape_string($_POST['employer']) . "',
					subemployer='" . DB_escape_string($_POST['subemployer']) . "',
					biometricdetailsstatus='" . DB_escape_string($_POST['biometricdetailsstatus']) . "',
					numberofdependants='" . DB_escape_string($_POST['numberofdependants']) . "',
					residential='" . DB_escape_string($_POST['residential']) . "'
					phonenumber='" . DB_escape_string($_POST['phonenumber']) . "',
					email='" . DB_escape_string($_POST['email']) . "'
						WHERE employeeno='$employeeno'";
			$ErrMsg = _('The nssf could not be updated because');
			$DbgMsg = _('The SQL that was used to update the nssf but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
			prnMsg(_('The nssf master record for') . ' ' . $employeeno . ' ' . _('has been updated'), 'success');

		} else { //its a new SSS

			$SQL = "INSERT INTO prlnssftable (employeeno,
					surname,
					othernames,
					dob,
					gender,
					dateofregistration,
					placeofbirth,
					areaoffice,
					nationality,
					employer,
					subemployer,
					biometricdetailsstatus,
					numberofdependants,
					residential,
					phonenumber,
					email)
				 VALUES ('',
					 	'" . DB_escape_string($_POST['surname']) . "',
						'" . DB_escape_string($_POST['othernames']) . "',
						'" . $SQL_dob . "',
						'" . DB_escape_string(isset($_POST['gender'])) . "',
						'" . isset($SQL_dateofregistration) . "',
						'" . DB_escape_string($_POST['placeofbirth']) . "',
						'" . DB_escape_string($_POST['areaoffice']) . "',
						'" . DB_escape_string($_POST['nationality']) . "',
						'" . DB_escape_string($_POST['employer']) . "',
						'" . DB_escape_string($_POST['subemployer']) . "',
						'" . DB_escape_string($_POST['biometricdetailsstatus']) . "',
						'" . DB_escape_string($_POST['numberofdependants']) . "',
						'" . DB_escape_string($_POST['residential']) . "',
						'" . DB_escape_string($_POST['phonenumber']) . "',
						'" . DB_escape_string($_POST['email']) . "')";
			//prnMsg(_('A new nssf has been added to the database'),'success'.$SQL);
			$ErrMsg = _('The nssf') . ' ' . $_POST['othernames'] . ' ' . _('could not be added because');
			$DbgMsg = _('The SQL that was used to insert the nssf but failed was');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg);

			prnMsg(_('A new nssf has been added to the database'), 'success');

			unset($employeeno);
			unset($_POST['surname']);
			unset($_POST['othernames']);
			unset($_POST['dob']);
			unset($_POST['gender']);
			unset($_POST['dateofregistration']);
			unset($_POST['placeofbirth']);
			unset($_POST['areaoffice']);
			unset($_POST['nationality']);
			unset($_POST['employer']);
			unset($_POST['subemployer']);
			unset($_POST['biometricdetailsstatus']);
			unset($_POST['numberofdependants']);
			unset($_POST['residential']);
			unset($_POST['phonenumber']);
			unset($_POST['email']);
		}

	} else {

		prnMsg(_('Validation failed') . _('no updates or deletes took place'), 'warn');

	}

} elseif (isset($_POST['delete']) AND $_POST['delete'] != '') {

	//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'Supptrans' , PurchOrders, SupplierContacts
	if ($CancelDelete == 0) {
		$SQL = "DELETE FROM prlnssftable WHERE employeeno='$employeeno'";
		$Result = DB_query($SQL);
		prnMsg(_('nssf record for') . ' ' . $employeeno . ' ' . _('has been deleted'), 'success');
		unset($employeeno);
		unset($_SESSION['employeeno']);
	} //end if Delete paypayperiod
}


if (!isset($employeeno)) {

	/*If the page was called without $SupplierID passed to page then assume a new supplier is to be entered show a form with a Supplier Code field other wise the form showing the fields with the existing entries against the supplier will show for editing with only a hidden SupplierID field*/

	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';

	echo '<input type="hidden" name="New" value="Yes">';

	echo '<table>';
	echo '<tr><td>' . _('Employee Number') . ":</td><td><input type='text' name='employeeno' SIZE=10 MAXLENGTH=10></td></tr>";

	//echo "<tr><td width=200 height=20><div align='right'><b>" . _('Employee Number') . ":</td><td><input type='text' name='employeeno'  SIZE=11 MAXLENGTH=10></td>";
	//echo "<td><align=right><b>Accept Alpha Numeric Character</b></td></tr>";

	echo '<tr><td>' . _('Surname') . ":</td><td><input type='text' name='surname' SIZE=40 MAXLENGTH=40></td></tr>";
	echo '<tr><td>' . _('Othernames') . ":</td><td><input type='text' name='othernames' SIZE=40 MAXLENGTH=40></Ttd></tr>";
?>
	       <tr>
	          <td width=200 height="20">
              <div align="left"><b>Date Of Birth :</b> </div>
              </td>
              <td height="20">
                <select name="Month">
   	            <option value="" selected="selected">Month</option>
                <option value=01>January</option>
                <option value=02>February</option>
                <option value=03>March</option>
                <option value=04>April</option>
                <option value=05>May</option>
                <option value=06>June</option>
                <option value=07>July</option>
                <option value=08>August</option>
                <option value=09>September</option>
                <option value=10>October</option>
                <option value=11>November</option>
                <option value=12>December</option>
              </select>
              <select name="Day">
                <option value="" selected="selected">Day</option>
                <option value=01>01</option>
                <option value=02>02</option>
                <option value=03>03</option>
                <option value=04>04</option>
                <option value=05>05</option>
                <option value=06>06</option>
                <option value=07>07</option>
                <option value=08>08</option>
                <option value=09>09</option>
                <option value=10>10</option>
                <option value=11>11</option>
                <option value=12>12</option>
                <option value=13>13</option>
                <option value=14>14</option>
                <option value=15>15</option>
                <option value=16>16</option>
                <option value=17>17</option>
                <option value=18>18</option>
                <option value=19>19</option>
                <option value=20>20</option>
                <option value=21>21</option>
                <option value=22>22</option>
                <option value=23>23</option>
                <option value=24>24</option>
                <option value=25>25</option>
                <option value=26>26</option>
                <option value=27>27</option>
                <option value=28>28</option>
                <option value=29>29</option>
                <option value=30>30</option>
                <option value=31>31</option>
              </select>
              <select name="Year">
              <option value="" Selected>Year</option>
              <?php

	for ($yy = 1900; $yy <= 2010; $yy++) {

		echo "<option value=$yy>$yy</option>\n";

	}
?>

			  <tr>
            <td width=200 height="21">
              <div align="left"><b>Gender : </b></div>
            </td>
            <td height="21">
              <input type=radio CHECKED value=M name=Gender>
              Male
              <input type=radio value=F name=Gender>
              Female </td>
          </tr>

		 <tr>
	          <td width=200 height="20">
              <div align="left"><b>Date Of Registration:</b></div>
              </td>
              <td height="20">
             <strong>   <select name="Month"</strong>>
   	            <option value="" selected="selected">Month</option>
                <option value=01>January</option>
                <option value=02>February</option>
                <option value=03>March</option>
                <option value=04>April</option>
                <option value=05>May</option>
                <option value=06>June</option>
                <option value=07>July</option>
                <option value=08>August</option>
                <option value=09>September</option>
                <option value=10>October</option>
                <option value=11>November</option>
                <option value=12>December</option>
              </select>
              <select name="Day">
                <option value="" selected="selected">Day</option>
                <option value=01>01</option>
                <option value=02>02</option>
                <option value=03>03</option>
                <option value=04>04</option>
                <option value=05>05</option>
                <option value=06>06</option>
                <option value=07>07</option>
                <option value=08>08</option>
                <option value=09>09</option>
                <option value=10>10</option>
                <option value=11>11</option>
                <option value=12>12</option>
                <option value=13>13</option>
                <option value=14>14</option>
                <option value=15>15</option>
                <option value=16>16</option>
                <option value=17>17</option>
                <option value=18>18</option>
                <option value=19>19</option>
                <option value=20>20</option>
                <option value=21>21</option>
                <option value=22>22</option>
                <option value=23>23</option>
                <option value=24>24</option>
                <option value=25>25</option>
                <option value=26>26</option>
                <option value=27>27</option>
                <option value=28>28</option>
                <option value=29>29</option>
                <option value=30>30</option>
                <option value=31>31</option>
              </select>
              <select name="Year">
              <option value="" Selected>Year</option>
              <p>
                <?php

	for ($yy = 1930; $yy <= 2010; $yy++) {

		echo "<option value=$yy>$yy</option>\n";

	}
?>
                <?php

	echo '<tr><td>' . _('Place of Birth') . ":</td><td><input type='text' name='placeofbirth' SIZE=40 MAXLENGTH=40></td></tr>";
	echo '<tr><td>' . _('Area Office') . ":</td><td><input type='text' name='areaoffice' SIZE=40 MAXLENGTH=40></td></tr>";
	echo '<tr><td>' . _('Nationality') . ":</td><td><input type='text' name='nationality' SIZE=40 MAXLENGTH=40></td></tr>";
	echo '<tr><td>' . _('Employer') . ":</td><td><input type='text' name='employer' SIZE=40 MAXLENGTH=40></td></tr>";
	echo '<tr><td>' . _('Sub employer') . ":</td><td><input type='text' name='subemployer' SIZE=40 MAXLENGTH=40></Ttd></tr>";
	echo '<tr><td>' . _('Biometricdetailsstatus') . ":</td><td><input type='text' name='biometricdetailsstatus' SIZE=30 MAXLENGTH=30></td></tr>";
	echo '<tr><td>' . _('No of Dependants') . ":</td><td><input type='text' name='numberofdependants' SIZE=14 MAXLENGTH=12></td></tr>";
	echo '<tr><td>' . _('Residential') . ":</td><td><input type='text' name='residential' SIZE=40MAXLENGTH=40></td></tr>";
	echo '<tr><td>' . _('Phone Number') . ":</td><td><input type='text' name='phonenumber' SIZE=14 MAXLENGTH=12></td></tr>";
	echo '<tr><td>' . _('Email') . ":</td><td><input type='text' name='email' SIZE=40 MAXLENGTH=40></td></tr>";
	//	echo '</select></td></tr>';
	echo "</select></td></tr></table><p><input type='Submit' name='submit' value='" . _('Insert New nssf') . "'>";
	echo '</form>';


	$SQL = "SELECT employeeno,
					surname,
					othernames,
					dob,
					gender,
					dateofregistration,
					placeofbirth,
					areaoffice,
					nationality,
					employer,
					subemployer,
					biometricdetailsstatus,
					numberofdependants,
					residential,
					phonenumber,
					email
					FROM prlnssftable
				ORDER BY employeeno";

	$ErrMsg = _('Could not get nssf because');
	$Result = DB_query($SQL, $ErrMsg);

	echo '<table border=1>';
	echo "<tr>
		<th>" . _('Employee Number') . "</td>
		<th>" . _('Surname') . "</td>
		<th>" . _('Othernames') . "</td>
		<th>" . _('Date Of Birth') . "</td>
		<th>" . _('Gender') . "</td>
		<th>" . _('Date Of Registration') . "</td>
		<th>" . _('place of Birth') . "</td>
		<th>" . _('Area Office') . "</td>
		<th>" . _('Nationality') . "</td>
		<th>" . _('Employer') . "</td>
		<th>" . _('Sub employer') . "</td>
		<th>" . _('Biometricdetailsstatus') . "</td>
		<th>" . _('No of Dependants') . "</td>
		<th>" . _('Residential') . "</td>
		<th>" . _('Phone Number') . "</td>
		<th>" . _('Email') . "</td>
	</tr>";


	$k = 0; //row colour counter
	while ($MyRow = DB_fetch_row($Result)) {

		if ($k == 1) {
			echo "<tr bgcolor='#CCCCCC'>";
			$k = 0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}
		echo '<td>' . $MyRow[0] . '</td>';
		echo '<td>' . $MyRow[1] . '</td>';
		echo '<td>' . $MyRow[2] . '</td>';
		echo '<td>' . $MyRow[3] . '</td>';
		echo '<td>' . $MyRow[4] . '</td>';
		echo '<td>' . $MyRow[5] . '</td>';
		echo '<td>' . $MyRow[6] . '</td>';
		echo '<td>' . $MyRow[7] . '</td>';
		echo '<td>' . $MyRow[8] . '</td>';
		echo '<td>' . $MyRow[9] . '</td>';
		echo '<td>' . $MyRow[10] . '</td>';
		echo '<td>' . $MyRow[11] . '</td>';
		echo '<td>' . $MyRow[12] . '</td>';
		echo '<td>' . $MyRow[13] . '</td>';
		echo '<td>' . $MyRow[14] . '</td>';
		echo '<td>' . $MyRow[15] . '</td>';
		echo '<td>' . isset($MyRow[16]) . '</td>';

		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?&employeeno=' . $MyRow[0] . '">' . _('Edit') . '</a></td>';
		echo '<td><a href="' . $_SERVER['PHP_SELF'] . '?&employeeno=' . $MyRow[0] . '&delete=1">' . _('Delete') . '</a></td>';
		echo '</tr>';

	} //END WHILE LIST LOOP
	echo '</table><p>';


} else {
	//employeeno exists - either passed when calling the form or from the form itself

	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
	echo '<table>';

	//if (!isset($_POST["New"])) {
	if (!isset($_POST["New"])) {
		$SQL = "SELECT employeeno
					surname,
					othernames,
					dob,
					gender,
					dateofregistration,
					placeofbirth
					areaoffice,
					nationality,
					employer,
					subemployer,
					biometricdetailsstatus,
					numberofdependants,
					residential,
					phonenumber,
					email
				FROM prlnssftable
				WHERE employeeno='$employeeno'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['employeeno'] = isset($MyRow['employeeno']);
		$_POST['surname'] = $MyRow['surname'];
		$_POST['othernames'] = $MyRow['othernames'];
		$_POST['dob'] = ConvertSQLDate($MyRow['dob']);
		$_POST['gender'] = $MyRow['gender'];
		$_POST['dateofregistration'] = ConvertSQLDate($MyRow['dateofregistration']);
		$_POST['placeofbirth'] = isset($MyRow['placeofbirth']);
		$_POST['nationality'] = $MyRow['nationality'];
		$_POST['areaoffice'] = $MyRow['areaoffice'];
		$_POST['employer'] = $MyRow['employer'];
		$_POST['subemployer'] = $MyRow['subemployer'];
		$_POST['biometricdetailsstatus'] = $MyRow['biometricdetailsstatus'];
		$_POST['numberofdependants'] = $MyRow['numberofdependants'];
		$_POST['residential'] = $MyRow['residential'];
		$_POST['phonenumber	'] = $MyRow['phonenumber'];
		$_POST['email'] = $MyRow['email'];

		echo '<input type="hidden" name="employeeno" value="' . $employeeno . '">';

	} else {
		// its a new SSS being added
		echo '<input type="hidden" name="New" value="Yes">';
		echo '<tr><td>' . _('Employee Number') . ":</td><td><input type='text' name='employeeno' value='$employeeno' SIZE=11 MAXLENGTH=10></td></tr>";
	}

	echo '<tr><td>' . _('Surname') . ":</td><td><input type='text' name='surname' SIZE=40 MAXLENGTH=40 value='" . $_POST['surname'] . "'></td></tr>";
	echo '<tr><td>' . _('Othernames') . ":</td><td><input type='text' name='othernames' SIZE=40 MAXLENGTH=40 value='" . $_POST['othernames'] . "'></td></tr>";
	//echo '<tr><td>' . _('Oldempno') . ":</td><td><input type='text' name='oldempno' SIZE=14 MAXLENGTH=12 value='" . $_POST['oldempno'] . "'></td></tr>";
	//echo '<tr><td>' . _('Date Of Birth') . ":</td><td><input type='text' name='dob' SIZE=14 MAXLENGTH=12 value='" . $_POST['dob'] . "'></td></tr>";
	echo '<tr><td width=200 height=20><div align="left">' . _('Date of Birth') . ":</td><td><input type='Text' name='dob' value='" . $_POST['dob'] . "' SIZE=14 MAXLENGTH=12><b>format (mm/dd/yyyy)</b></td></tr>";
	//echo '<td><align=right><b>format (mm/dd/yyyy)</b></td>';
	//echo '<tr><td>' . _('Gender') . ":</td><td><input type='text' name='gender' SIZE=14 MAXLENGTH=12 value='" . $_POST['gender'] . "'></td></tr>";
	echo '</select></td></tr>';
	echo '</select></td></tr><tr><td width=200 height=20><div align="left">' . _('Gender') . ":</td><td><select name='Gender'>";
	if ($_POST['Gender'] == 'M') {
		echo '<option selected="selected" value="M">' . _('Male');
		echo '<option value="F">' . _('Female');
	} else {
		echo '<option selected="selected" value="F">' . _('Female');
		echo '<option value="M">' . _('Male');
	}
	//echo '<tr><td>' . _('Date Of Registration') . ":</td><td><input type='text' name='dateofregistration' SIZE=14 MAXLENGTH=12 value='" . $_POST['dateofregistration'] . "'></td></tr>";
	echo '<tr><td width=200 height=20><div align="left">' . _('Date Of Registration') . ":</td><td><input type='Text' name='dateofregistration' value='" . $_POST['dateofregistration'] . "' SIZE=14 MAXLENGTH=12> <b> format (mm/dd/yyyy)</b></td></tr>";
	//echo '<td><align=right><b>format (mm/dd/yyyy)</b></td>';
	echo '<tr><td>' . _('place of Birth') . ":</td><td><input type='text' name='placeofbirth' SIZE=40 MAXLENGTH=40 value='" . $_POST['placeofbirth'] . "'></td></tr>";
	echo '<tr><td>' . _('Area Office') . ":</td><td><input type='text' name='areaoffice' SIZE=40 MAXLENGTH=40 value='" . $_POST['areaoffice'] . "'></td></tr>";
	echo '<tr><td>' . _('Nationality') . ":</td><td><input type='text' name='nationality' SIZE=40 MAXLENGTH=40 value='" . $_POST['nationality'] . "'></td></tr>";
	echo '<tr><td>' . _('Employer') . ":</td><td><input type='text' name='employer' SIZE=40 MAXLENGTH=40 value='" . $_POST['employer'] . "'></td></tr>";
	echo '<tr><td>' . _('Sub employer') . ":</td><td><input type='text' name='subemployer' SIZE=40 MAXLENGTH=40 value='" . $_POST['subemployer'] . "'></td></tr>";
	echo '<tr><td>' . _('Biometricdetailsstatus') . ":</td><td><input type='text' name='biometricdetailsstatus' SIZE=30 MAXLENGTH=30 value='" . $_POST['biometricdetailsstatus'] . "'></td></tr>";
	echo '<tr><td>' . _('No of Dependants') . ":</td><td><input type='text' name='numberofdependants' SIZE=14 MAXLENGTH=12 value='" . $_POST['numberofdependants'] . "'></td></tr>";
	echo '<tr><td>' . _('Residential') . ":</td><td><input type='text' name='residential' SIZE=40 MAXLENGTH=40 value='" . $_POST['residential'] . "'></td></tr>";
	echo '<tr><td>' . _('Phone Number') . ":</td><td><input type='text' name='phonenumber' SIZE=14 MAXLENGTH=12 value='" . isset($_POST['phonenumber']) . "'></td></tr>";
	echo '<tr><td>' . _('Email') . ":</td><td><input type='text' name='email' SIZE=40 MAXLENGTH=40 value='" . $_POST['email'] . "'></td></tr>";
	echo '</select></td></tr>';

	if (isset($_POST["New"])) {
		echo "</table><p><input type='Submit' name='submit' value='" . _('Add These New Nssf Details') . "'></form>";
	} else {
		echo "</table><p><input type='Submit' name='submit' value='" . _('Update nssf') . "'>";
		echo '<p><font color=red><B>' . _('WARNING') . ': ' . _('There is no second warning if you hit the delete button below') . '. ' . _('However checks will be made to ensure before the deletion is processed') . '<br /></FONT></B>';
		echo '<input type="Submit" name="delete" value="' . _('Delete nssf') . '" onclick="return confirm("' . _('Are you sure you wish to delete this Nssf?') . '");"></form>';
	}

} // end of main ifs

include('includes/footer.inc');
?>