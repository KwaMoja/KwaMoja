<?php
/* $Revision: 1.9 $ */

// Include functions needed for ReportCreator.php
function PrepStep($StepNum) {
	// This function sets the titles and include information to prepare for the defined step number
	switch ($StepNum) {
		case '1': // home form with form listings
		default:
			$FormParams['title'] = RPT_RPRBLDR . RPT_STEP1;
			$FormParams['heading'] = RPT_ADMIN;
			$FormParams['IncludePage'] = 'forms/ReportsHome.html';
			break;
		case '2': // id, copy, new report name form
			$FormParams['title'] = RPT_RPRBLDR . RPT_STEP2;
			$FormParams['heading'] = RPT_RPTID;
			$FormParams['IncludePage'] = 'forms/ReportsID.html';
			break;
		case '3': // page setup form
			$FormParams['title'] = RPT_RPRBLDR . RPT_STEP3;
			$FormParams['heading'] = RPT_RPTFRM;
			$FormParams['IncludePage'] = 'forms/ReportsPageSetup.html';
			break;
		case '4': // db setup form
			$FormParams['title'] = RPT_RPRBLDR . RPT_STEP4;
			$FormParams['heading'] = RPT_RPTFRM;
			$FormParams['IncludePage'] = 'forms/ReportsDBSetup.html';
			break;
		case '5': // field setup form
			$FormParams['title'] = RPT_RPRBLDR . RPT_STEP5;
			$FormParams['heading'] = RPT_RPTFRM;
			$FormParams['IncludePage'] = 'forms/ReportsFieldSetup.html';
			break;
		case 'prop': // Form field properties form
			global $Params; // we need the form type from the Params variable to load the correct form
			$FormParams['title'] = RPT_RPRBLDR . RPT_BTN_PROP;
			$FormParams['heading'] = RPT_RPTFRM;
			$FormParams['IncludePage'] = 'forms/TplFrm' . $Params['index'] . '.html';
			break;
		case '6': // criteria setup form
			$FormParams['title'] = RPT_RPRBLDR . RPT_STEP6;
			$FormParams['heading'] = RPT_RPTFRM;
			$FormParams['IncludePage'] = 'forms/ReportsCritSetup.html';
			break;
		case 'imp': // import form
			$FormParams['title'] = RPT_RPRBLDR . RPT_RPTIMPORT;
			$FormParams['heading'] = RPT_RPTIMPORT;
			$FormParams['IncludePage'] = 'forms/ReportsImport.html';
			break;
	} // end switch $StepNum
	return $FormParams;
}

function RetrieveReports() {
	global $ReportGroups, $FormGroups;

	$OutputString = '';
	foreach ($ReportGroups as $Key => $GName) {
		$OutputString .= '<tr style="background-color:#CCCCCC"><td colspan="2" align="center">' . $GName . '</td></tr>';
		$OutputString .= '<tr><td align="center">' . RPT_REPORTS . '</td><td align="center">' . RPT_FORMS . '</td></tr>';
		$OutputString .= '<tr><td width="250" valign="top">';
		$SQL = "SELECT id, reportname FROM " . DBReports . "
			WHERE defaultreport='1' AND reporttype='rpt' AND groupname='" . $Key . "'
			ORDER BY reportname";
		$Result = DB_query($SQL, '', '', false, true);
		while ($Temp = DB_fetch_array($Result))
			$OutputString .= '<input type="radio" name="ReportID" value="' . $Temp['id'] . '">' . $Temp['reportname'] . '<br />';
		$SQL = "SELECT id, reportname FROM " . DBReports . "
			WHERE defaultreport='0' AND reporttype='rpt' AND groupname='" . $Key . "'
			ORDER BY reportname";
		$Result = DB_query($SQL, '', '', false, true);
		if (DB_num_rows($Result) > 0)
			$OutputString .= '<u>' . RPT_CUSTRPT . '</u><br />';
		while ($Temp = DB_fetch_array($Result))
			$OutputString .= '<input type="radio" name="ReportID" value="' . $Temp['id'] . '">' . $Temp['reportname'] . '<br />';
		$OutputString .= '</td>' . chr(10) . '<td width="250" valign="top">';
		$SQL = "SELECT id, groupname, reportname FROM " . DBReports . "
			WHERE defaultreport='1' AND reporttype='frm'
			ORDER BY groupname, reportname";
		$Result = DB_query($SQL, '', '', false, true);
		$FormList = '';
		while ($Temp = DB_fetch_array($Result))
			$FormList[] = $Temp;
		foreach ($FormGroups as $index => $Value) {
			$Group = explode(':', $index); // break into main group and form group array
			if ($Group[0] == $Key and $FormList <> '') { // then it's a part of the group we're showing
				$WriteOnce = true;
				foreach ($FormList as $Entry) {
					if ($Entry['groupname'] == $index) { // then it's part of this listing
						if ($WriteOnce) {
							$OutputString .= $Value . '<br />';
							$WriteOnce = false;
						}
						$OutputString .= '&nbsp;&nbsp;<input type="radio" name="ReportID" value="' . $Entry['id'] . '">' . $Entry['reportname'] . '<br />';
					}
				}
			}
		}
		$OutputString .= '</td></tr>';
	}
	return $OutputString;
}

function RetrieveFields($EntryType) {
	global $ReportID;
	$FieldListings['fields'] = '';
	$SQL = "SELECT *	FROM " . DBRptFields . "
		WHERE reportid = " . $ReportID . " AND entrytype = '" . $EntryType . "'
		ORDER BY seqnum";
	$Result = DB_query($SQL, '', '', false, true);
	if (DB_num_rows($Result) > 0) {
		while ($FieldValues = DB_fetch_array($Result)) {
			$FieldListings['lists'][] = $FieldValues;
		}
	}
	// set the form field defaults
	$FieldListings['defaults']['seqnum'] = '';
	$FieldListings['defaults']['fieldname'] = '';
	$FieldListings['defaults']['displaydesc'] = '';
	$FieldListings['defaults']['visible'] = '';
	$FieldListings['defaults']['columnbreak'] = '';
	$FieldListings['defaults']['params'] = '';
	$FieldListings['defaults']['buttonvalue'] = RPT_BTN_ADDNEW;
	return $FieldListings;
}

function UpdatePageFields($ReportID) {
	global $Type;
	// For both reports and forms start sql string
	$SQL = "UPDATE " . DBReports . " SET
			papersize = '" . $_POST['PaperSize'] . "',
			paperorientation = '" . $_POST['PaperOrientation'] . "',
			margintop = " . $_POST['MarginTop'] . ",
			marginbottom = " . $_POST['MarginBottom'] . ",
			marginleft = " . $_POST['MarginLeft'] . ",
			marginright = " . $_POST['MarginRight'];
	// the checkboxes to false if not checked
	if ($Type <> 'frm') { // then it's a report, ad more info
		if (!isset($_POST['CoyNameShow']))
			$_POST['CoyNameShow'] = '0';
		if (!isset($_POST['Title1Show']))
			$_POST['Title1Show'] = '0';
		if (!isset($_POST['Title2Show']))
			$_POST['Title2Show'] = '0';
		$SQL .= ", coynamefont = '" . $_POST['CoyNameFont'] . "',
				coynamefontsize = " . $_POST['CoyNameFontSize'] . ",
				coynamefontcolor = '" . $_POST['CoyNameFontColor'] . "',
				coynamealign = '" . $_POST['CoyNameAlign'] . "',
				coynameshow = '" . $_POST['CoyNameShow'] . "',
				title1desc = '" . DB_escape_string($_POST['Title1Desc']) . "',
				title1font = '" . $_POST['Title1Font'] . "',
				title1fontsize = " . $_POST['Title1FontSize'] . ",
				title1fontcolor = '" . $_POST['Title1FontColor'] . "',
				title1fontalign = '" . $_POST['Title1FontAlign'] . "',
				title1show = '" . $_POST['Title1Show'] . "',
				title2desc = '" . DB_escape_string($_POST['Title2Desc']) . "',
				title2font = '" . $_POST['Title2Font'] . "',
				title2fontsize = " . $_POST['Title2FontSize'] . ",
				title2fontcolor = '" . $_POST['Title2FontColor'] . "',
				title2fontalign = '" . $_POST['Title2FontAlign'] . "',
				title2show = '" . $_POST['Title2Show'] . "',
				filterfont = '" . $_POST['FilterFont'] . "',
				filterfontsize = " . $_POST['FilterFontSize'] . ",
				filterfontcolor = '" . $_POST['FilterFontColor'] . "',
				filterfontalign = '" . $_POST['FilterFontAlign'] . "',
				datafont = '" . $_POST['DataFont'] . "',
				datafontsize = " . $_POST['DataFontSize'] . ",
				datafontcolor = '" . $_POST['DataFontColor'] . "',
				datafontalign = '" . $_POST['DataFontAlign'] . "',
				totalsfont = '" . $_POST['TotalsFont'] . "',
				totalsfontsize = " . $_POST['TotalsFontSize'] . ",
				totalsfontcolor = '" . $_POST['TotalsFontColor'] . "',
				totalsfontalign = '" . $_POST['TotalsFontAlign'] . "',
				col1width = " . $_POST['Col1Width'] . ",
				col2width = " . $_POST['Col2Width'] . ",
				col3width = " . $_POST['Col3Width'] . ",
				col4width = " . $_POST['Col4Width'] . ",
				col5width = " . $_POST['Col5Width'] . ",
				col6width = " . $_POST['Col6Width'] . ",
				col7width = " . $_POST['Col7Width'] . ",
				col8width = " . $_POST['Col8Width'] . ",
				col9width = " . $_POST['Col9Width'] . ",
				col10width = " . $_POST['Col10Width'] . ",
				col11width = " . $_POST['Col11Width'] . ",
				col12width = " . $_POST['Col12Width'] . ",
				col13width = " . $_POST['Col13Width'] . ",
				col14width = " . $_POST['Col14Width'] . ",
				col15width = " . $_POST['Col15Width'] . ",
				col16width = " . $_POST['Col16Width'] . ",
				col17width = " . $_POST['Col17Width'] . ",
				col18width = " . $_POST['Col18Width'] . ",
				col19width = " . $_POST['Col19Width'] . ",
				col20width = " . $_POST['Col20Width'];
	}
	$SQL .= " WHERE id =" . $ReportID . ";";
	$Result = DB_query($SQL, '', '', false, true);
	return true;
}

function UpdateCritFields($ReportID, $DateString) {
	global $Type;
	$SQL = "UPDATE " . DBRptFields . " SET
		reportid = '" . $ReportID . "',
		entrytype = 'dateselect',
		fieldname = '" . DB_escape_string($_POST['DateField']) . "',
		displaydesc = '" . $DateString . "',
		params = '" . $_POST['DefDate'] . "'
		WHERE reportid = " . $ReportID . " AND entrytype = 'dateselect';";
	$Result = DB_query($SQL, '', '', false, true);
	if ($Type <> 'frm') { // then write specifics for a report
		// write the truncate long descriptions choice
		$SQL = "UPDATE " . DBRptFields . " SET
			reportid = '" . $ReportID . "',
			entrytype = 'trunclong',
			params = '" . $_POST['TruncLongDesc'] . "',
			displaydesc = ''
			WHERE reportid = " . $ReportID . " AND entrytype = 'trunclong';";
		$Result = DB_query($SQL, '', '', false, true);
	} else { // it's a form update the page break info
		// write the form page break fieldname
		$SQL = "UPDATE " . DBRptFields . " SET
			reportid = '" . $ReportID . "',
			entrytype = 'grouplist',
			seqnum = 1,
			fieldname = '" . $_POST['FormBreakField'] . "',
			params = '',
			displaydesc = ''
			WHERE reportid = " . $ReportID . " AND entrytype = 'grouplist';";
		$Result = DB_query($SQL, '', '', false, true);
	}
	return true;
}

function UpdateDBFields($ReportID) {
	// Test inputs to see if they are valid
	$strTable = DB_escape_string($_POST['Table1']);
	if ($_POST['Table2'])
		$strTable .= ' INNER JOIN ' . DB_escape_string($_POST['Table2']) . ' ON ' . DB_escape_string($_POST['Table2Criteria']);
	if ($_POST['Table3'])
		$strTable .= ' INNER JOIN ' . DB_escape_string($_POST['Table3']) . ' ON ' . DB_escape_string($_POST['Table3Criteria']);
	if ($_POST['Table4'])
		$strTable .= ' INNER JOIN ' . DB_escape_string($_POST['Table4']) . ' ON ' . DB_escape_string($_POST['Table4Criteria']);
	if ($_POST['Table5'])
		$strTable .= ' INNER JOIN ' . DB_escape_string($_POST['Table5']) . ' ON ' . DB_escape_string($_POST['Table5Criteria']);
	if ($_POST['Table6'])
		$strTable .= ' INNER JOIN ' . DB_escape_string($_POST['Table6']) . ' ON ' . DB_escape_string($_POST['Table6Criteria']);
	//	$SQL = "SELECT * FROM ".$strTable." LIMIT 1";

	for ($i = 0; $i < 6; $i++) {
		if (isset($_POST['Table' . $i]) and $_POST['Table' . $i]) {
			$SQL = "SHOW TABLES WHERE Tables_in_" . $_SESSION['DatabaseName'] . "='" . $_POST['Table' . $i] . "'";
			$Result = DB_query($SQL, '', '', false, false);
			if (DB_num_rows($Result) == 0)
				return false;
		}
		// if we have a row, sql was valid
	}
	$SQL = "UPDATE " . DBReports . " SET
			table1 = '" . DB_escape_string($_POST['Table1']) . "',
			table2 = '" . DB_escape_string($_POST['Table2']) . "',
			table2criteria = '" . DB_escape_string($_POST['Table2Criteria']) . "',
			table3 = '" . DB_escape_string($_POST['Table3']) . "',
			table3criteria = '" . DB_escape_string($_POST['Table3Criteria']) . "',
			table4 = '" . DB_escape_string($_POST['Table4']) . "',
			table4criteria = '" . DB_escape_string($_POST['Table4Criteria']) . "',
			table5 = '" . DB_escape_string($_POST['Table5']) . "',
			table5criteria = '" . DB_escape_string($_POST['Table5Criteria']) . "',
			table6 = '" . DB_escape_string($_POST['Table6']) . "',
			table6criteria = '" . DB_escape_string($_POST['Table6Criteria']) . "'
		WHERE id =" . $ReportID . ";";
	$Result = DB_query($SQL, '', '', false, true);
	return true;
}

function UpdateSequence($EntryType) {
	global $ReportID, $Type;
	if (!isset($_POST['Visible']))
		$_POST['Visible'] = '0';
	if (!isset($_POST['ColumnBreak']))
		$_POST['ColumnBreak'] = '0';
	if (!isset($_POST['Params']))
		$Params = '0';
	else
		$Params = $_POST['Params'];
	$SQL = "UPDATE " . DBRptFields . " SET
			fieldname = '" . DB_escape_string($_POST['FieldName']) . "',
			displaydesc = '" . DB_escape_string($_POST['DisplayDesc']) . "',
			visible = '" . $_POST['Visible'] . "',
			columnbreak = '" . $_POST['ColumnBreak'] . "' ";
	// Only update params if not a form (cannot update params once initially set)
	if ($Type <> 'frm')
		$SQL .= ", params = '" . $Params . "' ";
	$SQL .= "WHERE reportid = " . $ReportID . " AND entrytype = '" . $EntryType . "' AND seqnum = " . $_POST['SeqNum'] . ";";
	$Result = DB_query($SQL, '', '', false, true);
	return true;
}

function ChangeSequence($SeqNum, $EntryType, $UpDown) {
	global $ReportID;
	// find the id of the row to move
	$SQL = "SELECT id FROM " . DBRptFields . "
		WHERE reportid = " . $ReportID . " AND entrytype = '" . $EntryType . "' AND seqnum = " . $SeqNum . ";";
	$Result = DB_query($SQL, '', '', false, true);
	$MyRow = DB_fetch_row($Result);
	$OrigID = $MyRow[0];
	if ($UpDown == 'up')
		$NewSeqNum = $SeqNum - 1;
	else
		$NewSeqNum = $SeqNum + 1;
	// first move affected sequence to seqnum, then seqnum to new position
	$SQL = "UPDATE " . DBRptFields . " SET seqnum='" . $SeqNum . "'
		WHERE reportid = " . $ReportID . " AND entrytype = '" . $EntryType . "' AND seqnum = " . $NewSeqNum . ";";
	$Result = DB_query($SQL, '', '', false, true);
	$SQL = "UPDATE " . DBRptFields . " SET seqnum='" . $NewSeqNum . "' WHERE id = " . $OrigID . ";";
	$Result = DB_query($SQL, '', '', false, true);
	return true;
}

function InsertSequence($SeqNum, $EntryType) {
	// This function creates a hole in the sequencing to allow inserting new data
	global $ReportID, $Type;
	if (!$SeqNum)
		$SeqNum = 999; // set sequence to max if not entered
	// read the sequence numbers for the given EntryType
	$SQL = "SELECT id FROM " . DBRptFields . "
		WHERE reportid = " . $ReportID . " AND entrytype = '" . $EntryType . "'
		ORDER BY seqnum;";
	$Result = DB_query($SQL, '', '', false, true);
	while ($FieldID = DB_fetch_array($Result)) {
		$IDList[] = $FieldID['id'];
	}
	$NumRows = DB_num_rows($Result);
	if (!$IDList or ($NumRows < $SeqNum)) {
		$SeqNum = DB_num_rows($Result) + 1;
	}
	if ($SeqNum <= $NumRows) { // shift the fields down to make a sequence hole
		for ($j = $SeqNum - 1; $j < $NumRows; $j++) {
			$SQL = "UPDATE " . DBRptFields . " SET seqnum = " . ($j + 2) . " WHERE id=" . $IDList[$j] . ";";
			$Result = DB_query($SQL, '', '', false, true);
		}
	}
	if (!isset($_POST['Visible']))
		$Visible = '0';
	else
		$Visible = $_POST['Visible'];
	if (!isset($_POST['ColumnBreak']))
		$ColumnBreak = '0';
	else
		$ColumnBreak = $_POST['ColumnBreak'];
	if (!isset($_POST['Params'])) {
		$Params = '0';
	} elseif ($Type == 'frm' and $EntryType == 'fieldlist') {
		$EntryIndex['index'] = $_POST['Params'];
		$Params = serialize($EntryIndex);
	} else {
		$Params = $_POST['Params'];
	}
	$SQL = "INSERT INTO " . DBRptFields . "
			(reportid, entrytype, seqnum, fieldname, displaydesc, visible, columnbreak, params)
		VALUES (" . $ReportID . ",'" . $EntryType . "'," . $SeqNum . ",'" . DB_escape_string($_POST['FieldName']) . "',
			'" . DB_escape_string($_POST['DisplayDesc']) . "','" . $Visible . "','" . $ColumnBreak . "','" . $Params . "');";
	$Result = DB_query($SQL, '', '', false, true);
	return $SeqNum;
}

function DeleteSequence($SeqNum, $EntryType) {
	// This function removes a sequence field and fills the sequence hole left behind
	global $ReportID;
	//  delete the sequence number from the list
	$SQL = "DELETE FROM " . DBRptFields . "
		WHERE reportid = " . $ReportID . " AND entrytype = '" . $EntryType . "' AND seqnum = " . $SeqNum . ";";
	$Result = DB_query($SQL, '', '', false, true);
	// read in the remaining sequences and re-number
	$SQL = "SELECT id FROM " . DBRptFields . "
		WHERE reportid = " . $ReportID . " AND entrytype = '" . $EntryType . "'
		ORDER BY seqnum;";
	$Result = DB_query($SQL, '', '', false, true);
	while ($FieldID = DB_fetch_array($Result)) {
		$IDList[] = $FieldID['id'];
	}
	$NumRows = DB_num_rows($Result);
	if ($NumRows >= $SeqNum) { // then not at end of list re-number sequences
		for ($j = $SeqNum - 1; $j < $NumRows; $j++) {
			$SQL = "UPDATE " . DBRptFields . " SET seqnum = " . ($j + 1) . " WHERE id=" . $IDList[$j] . ";";
			$Result = DB_query($SQL, '', '', false, true);
		}
	}
	return true;
}
function InsertFormSeq(&$Params, $Insert) {
	// This function creates a hole in the sequencing to allow inserting new form table field data
	$SeqNum = $_POST['TblSeqNum'];
	if (!$SeqNum)
		$SeqNum = count($Params['Seq']) + 1; // set sequence to last entry if not entered
	if (isset($Params['Seq'][$SeqNum - 1]) and $Insert == 'insert') {
		// then the sequence number exists make a hole for this insert
		for ($j = count($Params['Seq']); $j >= $SeqNum; $j--) {
			$Params['Seq'][$j] = $Params['Seq'][$j - 1]; // move the array element down one
			$Params['Seq'][$j]['TblSeqNum'] = $j + 1; // increment the sequence number
		}
	} // else it's an update which we do anyway
	// Fill in the new data
	$Params['Seq'][$SeqNum - 1]['TblSeqNum'] = $SeqNum;
	$Params['Seq'][$SeqNum - 1]['TblField'] = $_POST['TblField'];
	$Params['Seq'][$SeqNum - 1]['TblDesc'] = $_POST['TblDesc'];
	$Params['Seq'][$SeqNum - 1]['Processing'] = $_POST['Processing'];
	$Params['Seq'][$SeqNum - 1]['Font'] = $_POST['Font'];
	$Params['Seq'][$SeqNum - 1]['FontSize'] = $_POST['FontSize'];
	$Params['Seq'][$SeqNum - 1]['FontAlign'] = $_POST['FontAlign'];
	$Params['Seq'][$SeqNum - 1]['FontColor'] = $_POST['FontColor'];
	$Params['Seq'][$SeqNum - 1]['TblColWidth'] = $_POST['TblColWidth'];
	if (!isset($_POST['TblShow']))
		$Params['Seq'][$SeqNum - 1]['TblShow'] = '0';
	else
		$Params['Seq'][$SeqNum - 1]['TblShow'] = '1';
	return true;
}

function ModFormTblEntry(&$Params) {
	for ($i = 1; $i < 100; $i++) { // see if a button was pressed
		if (isset($_POST['up' . $i . '_x']) and $i <> 1) { // sequence up[i] was pressed, swap it with the element before
			$Temp = $Params['Seq'][$i - 1];
			$Params['Seq'][$i - 1] = $Params['Seq'][$i - 2];
			$Params['Seq'][$i - 2] = $Temp;
			// update the sequence numbers
			$Params['Seq'][$i - 1]['TblSeqNum'] = $i;
			$Params['Seq'][$i - 2]['TblSeqNum'] = $i - 1;
			return true;
		}
		if (isset($_POST['dn' . $i . '_x']) and $i <> count($Params['Seq'])) { // sequence dn[i] was pressed, swap it with the element after
			$Temp = $Params['Seq'][$i - 1];
			$Params['Seq'][$i - 1] = $Params['Seq'][$i];
			$Params['Seq'][$i] = $Temp;
			// update the sequence numbers
			$Params['Seq'][$i - 1]['TblSeqNum'] = $i;
			$Params['Seq'][$i]['TblSeqNum'] = $i + 1;
			return true;
		}
		if (isset($_POST['ed' . $i . '_x'])) { // sequence ed[i] was pressed
			// set the defaults to the sequence selected
			// Set the form with the values from the sequence selected
			$Params['TblSeqNum'] = $Params['Seq'][$i - 1]['TblSeqNum'];
			$Params['TblField'] = $Params['Seq'][$i - 1]['TblField'];
			$Params['TblDesc'] = $Params['Seq'][$i - 1]['TblDesc'];
			$Params['Processing'] = $Params['Seq'][$i - 1]['Processing'];
			$Params['Font'] = $Params['Seq'][$i - 1]['Font'];
			$Params['FontSize'] = $Params['Seq'][$i - 1]['FontSize'];
			$Params['FontAlign'] = $Params['Seq'][$i - 1]['FontAlign'];
			$Params['FontColor'] = $Params['Seq'][$i - 1]['FontColor'];
			$Params['TblColWidth'] = $Params['Seq'][$i - 1]['TblColWidth'];
			$Params['TblShow'] = $Params['Seq'][$i - 1]['TblShow'];
			return 'edit';
		}
		if (isset($_POST['rm' . $i . '_x'])) { // sequence rm[i] was pressed, delete the entry
			for ($j = $i; $j < count($Params['Seq']); $j++) {
				$Params['Seq'][$j - 1] = $Params['Seq'][$j];
				$Params['Seq'][$j - 1]['TblSeqNum'] = $j;
			}
			$Temp = array_pop($Params['Seq']);
			break;
		}
	}
	return true;
}

function ValidateField($ReportID, $FieldName, $Description) {
	global $Type;
	// This function checks the fieldname and field reference and validates that it is good.
	// first check if a form (fieldname is not provided unless it's the form page break field)
	if ($Type == 'frm' and $Description <> 'TestField') { // then check for non-zero description unless a fieldname is present
		if (mb_strlen($Description) < 1)
			return false;
		else
			return true;
	}
	// fetch the table values to build sql
	$SQL = "SELECT table1,
			table2, table2criteria,
			table3, table3criteria,
			table4, table4criteria,
			table5, table5criteria,
			table6, table6criteria
		FROM " . DBReports . " WHERE id='" . $ReportID . "'";
	$Result = DB_query($SQL, '', '', false, true);
	$Prefs = DB_fetch_assoc($Result);
	// Check for a non-blank entry in the field description or fieldname
	if (mb_strlen($FieldName) < 1 or mb_strlen($Description) < 1)
		return false;
	// Build the table to search, then test inputs to see if they are valid
	$strTable = $Prefs['table1'];
	if ($Prefs['table2'])
		$strTable .= ' INNER JOIN ' . $Prefs['table2'] . ' ON ' . $Prefs['table2criteria'];
	if ($Prefs['table3'])
		$strTable .= ' INNER JOIN ' . $Prefs['table3'] . ' ON ' . $Prefs['table3criteria'];
	if ($Prefs['table4'])
		$strTable .= ' INNER JOIN ' . $Prefs['table4'] . ' ON ' . $Prefs['table4criteria'];
	if ($Prefs['table5'])
		$strTable .= ' INNER JOIN ' . $Prefs['table5'] . ' ON ' . $Prefs['table5criteria'];
	if ($Prefs['table6'])
		$strTable .= ' INNER JOIN ' . $Prefs['table6'] . ' ON ' . $Prefs['table6criteria'];
	$SQL = "SELECT " . $FieldName . " FROM " . $strTable . " LIMIT 1";
	$Result = DB_query($SQL, '', '', false, false);
	// Try to fetch one row, if we have a row, sql was valid
	if (DB_num_rows($Result) < 1)
		return false;
	else
		return true;
}

function ReadDefReports() {
	global $ReportGroups;
	$dh = opendir(DefRptPath);
	$i = 0;
	while ($DefRpt = readdir($dh)) {
		$pinfo = pathinfo(DefRptPath . $DefRpt);
		if ($pinfo['extension'] == 'txt') { // then it's a report file read name and type
			$FileLines = file(DefRptPath . $DefRpt);
			foreach ($FileLines as $OneLine) { // find the main reports sql statement, language and execute it
				if (mb_strpos($OneLine, 'ReportData:') === 0) { // then it's the line we'er after with reportname and groupname
					$GrpPos = mb_strpos($OneLine, "groupname='") + 11;
					$GrpName = mb_substr($OneLine, $GrpPos, mb_strpos($OneLine, "',", $GrpPos) - $GrpPos);
					$RptPos = mb_strpos($OneLine, "reportname='") + 12;
					$RptName = mb_substr($OneLine, $RptPos, mb_strpos($OneLine, "',", $RptPos) - $RptPos);
					$ReportList[$i]['GrpName'] = $GrpName;
					$ReportList[$i]['RptName'] = $RptName;
					$ReportList[$i]['FileName'] = $pinfo[basename];
					++$i;
				}
			}
		}
	}
	closedir($dh);
	$OptionList = '';
	$LstGroup = '';
	$CloseOptGrp = false;
	$i = 0;
	while ($Temp = $ReportList[$i]) {
		if ($Temp['GrpName'] <> $LstGroup) { // then it's a new group, close old group and start new group
			if ($LstGroup <> '')
				echo '</optgroup>';
			$CloseOptGrp = true; // we need to close the last option group
			$LstGroup = $Temp['GrpName'];
			$OptionList .= '<optgroup label="' . $ReportGroups[$Temp['GrpName']] . '" title="' . $Temp['GrpName'] . '">';
		}
		$GrpMember = $ReportGroups[$Temp['GrpName']];
		if (!$GrpMember)
			$Temp['GrpName'] = RPT_MISC;
		$OptionList .= '<option value="' . $Temp['FileName'] . '">' . $Temp['RptName'] . '</option>';
		++$i;
	}
	if ($CloseOptGrp)
		$OptionList .= '</optgroup>';
	return $OptionList;
}

function ReadImages($Default) {
	$OptionList = '';
	$dh = opendir(DefRptPath);
	while ($DefRpt = readdir($dh)) {
		$pinfo = pathinfo(DefRptPath . $DefRpt);
		$Ext = mb_strtoupper($pinfo['extension']);
		if ($Ext == 'JPG' or $Ext == 'JPEG' or $Ext == 'PNG') {
			if ($Default == $pinfo['basename'])
				$checked = ' selected';
			else
				$checked = '';
			$OptionList .= '<option value="' . $pinfo['basename'] . '"' . $checked . '> ' . $pinfo['basename'] . '</option>';
		}
	}
	closedir($dh);
	return $OptionList;
}

function ImportImage() {
	if ($_POST['ImgChoice'] == 'Select') { // then a locally stored image was chosen, return with image name
		$Rtn['result'] = 'success';
		$Rtn['message'] = $_POST['ImgFileName'] . RPT_IMP_ERMSG9;
		$Rtn['filename'] = $_POST['ImgFileName'];
		return $Rtn;
	}
	$Rtn['result'] = 'error';
	if ($_FILES['imagefile']['error']) { // php error uploading file
		switch ($_FILES['imagefile']['error']) {
			case '1':
				$Rtn['message'] = RPT_IMP_ERMSG1;
				break;
			case '2':
				$Rtn['message'] = RPT_IMP_ERMSG2;
				break;
			case '3':
				$Rtn['message'] = RPT_IMP_ERMSG3;
				break;
			case '4':
				$Rtn['message'] = RPT_IMP_ERMSG4;
				break;
			default:
				$Rtn['message'] = RPT_IMP_ERMSG5 . $_FILES['imagefile']['error'] . '.';
		}
	} elseif (!is_uploaded_file($_FILES['imagefile']['tmp_name'])) { // file uploaded
		$Rtn['message'] = RPT_IMP_ERMSG10;
	} elseif (mb_strpos($_FILES['imagefile']['type'], 'image') === false) { // not an imsge file extension
		$Rtn['message'] = RPT_IMP_ERMSG6;
	} elseif ($_FILES['imagefile']['size'] == 0) { // report contains no data, error
		$Rtn['message'] = RPT_IMP_ERMSG7;
	} else { // passed all error checking, save the image
		$success = move_uploaded_file($_FILES['imagefile']['tmp_name'], DefRptPath . $_FILES['imagefile']['name']);
		if (!$success) { // someone tried to hack the script
			$Rtn['message'] = 'Upload error. File cannot be processed, check directory permissions!';
		} else {
			$Rtn['result'] = 'success';
			$Rtn['message'] = $_FILES['imagefile']['name'] . RPT_IMP_ERMSG9;
			$Rtn['filename'] = $_FILES['imagefile']['name'];
		}
	}
	return $Rtn;
}

function ExportReport($ReportID) {
	$crlf = chr(10);
	$CSVOutput = '/* Report Builder Export Tool */' . $crlf;
	$CSVOutput .= 'version:1.0' . $crlf;
	// Fetch the core report data from table reports
	$SQL = "SELECT * FROM " . DBReports . " WHERE id = " . $ReportID . ";";
	$Result = DB_query($SQL, '', '', false, true);
	$MyRow = DB_fetch_assoc($Result);
	// Fetch the language dependent db entries
	$ReportName = $MyRow['reportname'];
	// Enter some export file info for language translation
	$CSVOutput .= '/* Report Name: ' . $ReportName . ' */' . $crlf;
	$CSVOutput .= '/* Export File Generated: : ' . date('Y-m-d h:m:s', time()) . ' */' . $crlf . $crlf . $crlf;
	$CSVOutput .= '/* Language Fields. */' . $crlf;
	$CSVOutput .= '/* Only modify the language portion between the single quotes after the colon. */' . $crlf . $crlf;
	$CSVOutput .= '/* Report Name and Title Information: */' . $crlf;
	$CSVOutput .= "ReportName:'" . DB_escape_string($ReportName) . "'" . $crlf;
	if ($MyRow['reporttype'] <> 'frm') {
		$CSVOutput .= "Title1Desc:'" . DB_escape_string($MyRow['title1desc']) . "'" . $crlf;
		$CSVOutput .= "Title2Desc:'" . DB_escape_string($MyRow['title2desc']) . "'" . $crlf;
	}
	// Now add the report fields
	$CSVOutput .= $crlf . '/* Report Field Description Information: */' . $crlf;
	$SQL = "SELECT * FROM " . DBRptFields . " WHERE reportid = " . $ReportID . " ORDER BY entrytype, seqnum;";
	$Result = DB_query($SQL, '', '', false, true);
	$i = 0;
	while ($FieldRows = DB_fetch_assoc($Result)) {
		if ($FieldRows['entrytype'] <> 'dateselect' and $FieldRows['entrytype'] <> 'trunclong') {
			$CSVOutput .= "FieldDesc" . $i . ":'" . DB_escape_string($FieldRows['displaydesc']) . "'" . $crlf;
		}
		$SQL = 'FieldData' . $i . ':';
		foreach ($FieldRows as $Key => $Value) {
			if ($Key <> 'id' and $Key <> 'reportid')
				$SQL .= $Key . "='" . DB_escape_string($Value) . "', ";
		}
		$SQL = mb_substr($SQL, 0, -2) . ";"; // Strip the last comma and space and add a semicolon
		$FieldData[$i] = $SQL;
		++$i;
	}
	$CSVOutput .= '/* End of language fields. */' . $crlf . $crlf;
	$CSVOutput .= '/* DO NOT EDIT BELOW THIS LINE! */' . $crlf . $crlf . $crlf;
	$CSVOutput .= '/* SQL report data. */' . $crlf;
	// Build the report sql string
	$RptData = 'ReportData:';
	foreach ($MyRow as $Key => $Value)
		if ($Key <> 'id')
			$RptData .= $Key . "='" . DB_escape_string($Value) . "', ";
	$RptData = mb_substr($RptData, 0, -2) . ";"; // Strip the last comma and space and add a semicolon
	$CSVOutput .= $RptData . $crlf . $crlf;
	$CSVOutput .= '/* SQL field data. */' . $crlf;
	for ($i = 0; $i < count($FieldData); $i++)
		$CSVOutput .= $FieldData[$i] . $crlf;
	$CSVOutput .= $crlf;
	$CSVOutput .= '/* End of Export File */' . $crlf;
	// export the file
	$FileSize = mb_strlen($CSVOutput);
	header("Content-type: application/txt");
	header("Content-disposition: attachment; filename=" . preg_replace('/ /', '', $ReportName) . ".rpt.txt; size=" . $FileSize);
	// These next two lines are needed for MSIE
	header('Pragma: cache');
	header('Cache-Control: public, must-revalidate, max-age=0');
	print $CSVOutput;
	exit();
}

function ImportReport($RptName) {
	if ($_POST['RptFileName'] <> '') { // then a locally stored report was chosen
		$arrSQL = file(DefRptPath . $_POST['RptFileName']);
	} else { // check for an uploaded file
		$Rtn['result'] = 'error';
		if ($_FILES['reportfile']['error']) { // php error uploading file
			switch ($_FILES['reportfile']['error']) {
				case '1':
					$Rtn['message'] = RPT_IMP_ERMSG1;
					break;
				case '2':
					$Rtn['message'] = RPT_IMP_ERMSG2;
					break;
				case '3':
					$Rtn['message'] = RPT_IMP_ERMSG3;
					break;
				case '4':
					$Rtn['message'] = RPT_IMP_ERMSG4;
					break;
				default:
					$Rtn['message'] = RPT_IMP_ERMSG5 . $_FILES['reportfile']['error'] . '.';
			}
		} elseif (!is_uploaded_file($_FILES['reportfile']['tmp_name'])) { // file uploaded
			$Rtn['message'] = RPT_IMP_ERMSG10;
		} elseif (mb_strpos($_FILES['reportfile']['type'], 'text') === false) { // not a text file, error
			$Rtn['message'] = RPT_IMP_ERMSG6;
		} elseif ($_FILES['reportfile']['size'] == 0) { // report contains no data, error
			$Rtn['message'] = RPT_IMP_ERMSG7;
		} else { // passed all error checking, read file and reset error message
			$arrSQL = file($_FILES['reportfile']['tmp_name']);
			$Rtn['result'] = '';
		}
		if ($Rtn['result'] == 'error')
			return $Rtn;
	}

	$Title1Desc = ''; // Initialize to null, not used for forms
	$Title2Desc = '';
	foreach ($arrSQL as $SQL) { // find the report translated reportname and title information
		if (mb_strpos($SQL, 'ReportName:') === 0)
			$ReportName = mb_substr(trim($SQL), 12, -1);
		if (mb_strpos($SQL, 'Title1Desc:') === 0)
			$Title1Desc = mb_substr(trim($SQL), 12, -1);
		if (mb_strpos($SQL, 'Title2Desc:') === 0)
			$Title2Desc = mb_substr(trim($SQL), 12, -1);
	}
	// check for valid file, duplicate report name
	if ($RptName == '')
		$RptName = $ReportName; // then no report was entered use reportname from file
	$SQL = "SELECT id FROM " . DBReports . " WHERE reportname='" . DB_escape_string($RptName) . "';";
	$Result = DB_query($SQL, '', '', false, true);
	if (DB_num_rows($Result) > 0) { // the report name already exists, error
		$Rtn['result'] = 'error';
		$Rtn['message'] = RPT_REPDUP;
		return $Rtn;
	}
	// Find the line with the table reports element, needs to be written first
	$ValidReportSQL = false;
	foreach ($arrSQL as $SQL) { // find the main reports sql statement, language and execute it
		if (mb_strpos($SQL, 'ReportData:') === 0) {
			$SQL = "INSERT INTO " . DBReports . " SET " . mb_substr(trim($SQL), 11);
			$Result = DB_query($SQL, '', '', false, true);
			$ValidReportSQL = true;
		}
	}
	if (!$ValidReportSQL) { // no valid report sql statement found in the text file, error
		$Rtn['result'] = 'error';
		$Rtn['message'] = RPT_IMP_ERMSG8;
		return $Rtn;
	}
	// fetch the id of the row inserted
	$ReportID = DB_Last_Insert_ID(DBReports, 'id');
	// update the translated report name and title fields into the newly imported report
	$SQL = "UPDATE " . DBReports . " SET
			reportname = '" . $RptName . "',
			title1desc = '" . $Title1Desc . "',
			title2desc = '" . $Title2Desc . "'
		WHERE id = " . $ReportID . ";";
	$Result = DB_query($SQL, '', '', false, true);
	foreach ($arrSQL as $SQL) { // fetch the translations for the field descriptions
		if (mb_strpos($SQL, 'FieldDesc') === 0) { // then it's a field description, find the index and save
			$SQL = trim($SQL);
			$FldIndex = mb_substr($SQL, 9, mb_strpos($SQL, ':') - 9);
			$Language[$FldIndex] = mb_substr($SQL, mb_strpos($SQL, ':') + 2, -1);
		}
	}
	foreach ($arrSQL as $SQL) {
		if (mb_strpos($SQL, 'FieldData') === 0) { // a valid field, write it
			$SQL = trim($SQL);
			$FldIndex = mb_substr($SQL, 9, mb_strpos($SQL, ':') - 9);
			$SQL = "INSERT INTO " . DBRptFields . " SET " . mb_substr($SQL, mb_strpos($SQL, ':') + 1);
			$Result = DB_query($SQL, '', '', false, true);
			$FieldID = DB_Last_Insert_ID( DBRptFields, 'id');
			if ($FieldID <> 0) { // A field was successfully written update the report id
				if (isset($Language[$FldIndex]))
					$DispSQL = "displaydesc='" . $Language[$FldIndex] . "', ";
				else
					$DispSQL = '';
				$tsql = "UPDATE " . DBRptFields . " SET " . $DispSQL . " reportid='" . $ReportID . "'
					WHERE id=" . $FieldID . ";";
				$Result = DB_query($tsql, '', '', false, true);
			}
		}
	}
	$Rtn['result'] = 'success';
	$Rtn['message'] = $RptName . RPT_IMP_ERMSG9;
	return $Rtn;
}

function CreateTableList($ReportID, $Table) {
	$SQL = "SELECT table" . $Table . " FROM " . DBReports . " WHERE id='" . $ReportID . "'";
	$Result = DB_query($SQL, '', '', false, true);
	$MyRow = DB_fetch_row($Result);

	$TableList = '';

	$Result = DB_show_tables();

	while ($mytable = DB_fetch_row($Result)) {
		$tablename = strtolower($mytable[0]);
		if ($MyRow[0] == $tablename)
			$TableList .= "<option selected='selected' value='" . $tablename . "'>" . $tablename . "</option>";
		else
			$TableList .= "<option value='" . $tablename . "'>" . $tablename . "</option>";
	}
	return $TableList;
} // CreateTableList

function CreateLinkList($ReportID, $Table) {
	$SQL = "SELECT table1, table2, table3, table4, table5, table6
		FROM " . DBReports . " WHERE id='" . $ReportID . "'";
	$Result = DB_query($SQL, '', '', false, true);
	$MyRow = DB_fetch_row($Result);
	$LinkList = '';
	$j = 0;

	/* Get list of link tables from foreign keys */
	for ($i = 0; $i < $Table; $i++) {
		$comments = '';
		$SQL = "SELECT table1, table2 FROM reportlinks WHERE table1 = '" . $MyRow[$i] . "'";
		$Result = DB_query($SQL, '', '', false, true);
		while ($mytable = DB_fetch_row($Result)) {
			if ($MyRow[$Table]) {
				if ($MyRow[$Table] == $mytable[1]) {
					$LinkList .= "<option selected='selected' value='" . $mytable[1] . "'>" . $mytable[1];
				} else {
					$LinkList .= "<option value='" . $mytable[1] . "'>" . $mytable[1];
				}
			} else {
				if ($j == 0) {
					$LinkList .= "<option selected='selected' value='" . $mytable[1] . "'>" . $mytable[1];
				} else {
					$LinkList .= "<option value='" . $mytable[1] . "'>" . $mytable[1];
				}
				++$j;
			}
		} // while
	} // for
	if (!$MyRow[$Table] and $Table > $j) {
		$LinkList = '';
	}
	return $LinkList;
}

function CreateLinkEqList($ReportID, $Table) {
	$SQL = "SELECT table1,
		table2, table2criteria,
		table3, table3criteria,
		table4, table4criteria,
		table5, table5criteria,
		table6, table6criteria
		FROM " . DBReports . " WHERE id='" . $ReportID . "'";
	$Result = DB_query($SQL, '', '', false, true);
	$MyRow = DB_fetch_row($Result);
	$LinkEqList = '';
	$j = 0;

	/* Get list of foreign key constraints */
	for ($i = 0; $i < $Table; $i++) {
		$comments = '';
		$SQL = "SELECT table1, table2, equation FROM reportlinks WHERE table1 = '" . $MyRow[$i] . "'";
		$Result = DB_query($SQL, '', '', false, true);
		while ($mytable = DB_fetch_row($Result)) {
			if ($MyRow[$Table + 3]) {
				if ($MyRow[$Table + 3] == $mytable[2]) {
					$LinkEqList .= "<option selected='selected' value='" . $mytable[2] . "'>" . $mytable[2];
				} else {
					$LinkEqList .= "<option value='" . $mytable[2] . "'>" . $mytable[2];
				}
			} else {
				if ($j == 0) {
					$LinkEqList .= "<option selected='selected' value='" . $mytable[2] . "'>" . $mytable[2];
				} else {
					$LinkEqList .= "<option value='" . $mytable[2] . "'>" . $mytable[2];
				}
				++$j;
			}
		} // while
	} // for
	if (!$MyRow[$Table] and $Table > $j) {
		$LinkEqList = '';
	}
	return $LinkEqList;
} // CreateLinkEqList

function CreateFieldList($ReportID, $FName, $Type) {
	if ($Type == 'Company') { // then pull from the company information table
		$MyRow[] = CompanyDataBase;
	} else { // pull from user selected tables for this report
		$SQL = "SELECT table1, table2, table3, table4, table5, table6
			FROM " . DBReports . " WHERE id='" . $ReportID . "'";
		$Result = DB_query($SQL, '', '', false, true);
		$MyRow = DB_fetch_row($Result);
	}
	$FieldList = '';

	for ($i = 0; $i < 6; $i++) {
		if ($MyRow[$i]) {
			$Result = DB_show_fields($MyRow[$i]);
			while ($mytable = DB_fetch_row($Result)) {
				$fieldname = strtolower($MyRow[$i]) . "." . strtolower($mytable[0]);
				if ($FName == $fieldname) {
					$FieldList .= "<option selected='selected' value='" . $fieldname . "'>" . $fieldname . "</option>";
				} else {
					$FieldList .= "<option value='" . $fieldname . "'>" . $fieldname . "</option>";
				}
			} // while
		} // if
	} // for
	return $FieldList;
} // CreateFieldList

?>