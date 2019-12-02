<?php
/* $Id: PDFCOA.php 1 2014-09-15 06:31:08Z agaluski $ */

include ('includes/session.php');
include ('includes/SQL_CommonFunctions.php');

if (isset($_GET['LotKey'])) {
	$SelectedCOA = $_GET['LotKey'];
} elseif (isset($_POST['LotKey'])) {
	$SelectedCOA = $_POST['LotKey'];
}
if (isset($_GET['ProdSpec'])) {
	$SelectedSpec = $_GET['ProdSpec'];
} elseif (isset($_POST['ProdSpec'])) {
	$SelectedSpec = $_POST['ProdSpec'];
}

if (isset($_GET['QASampleID'])) {
	$QASampleID = $_GET['QASampleID'];
} elseif (isset($_POST['QASampleID'])) {
	$QASampleID = $_POST['QASampleID'];
}

//Get Out if we have no Certificate of Analysis
if ((!isset($SelectedCOA) || $SelectedCOA == '') and (!isset($QASampleID) or $QASampleID == '')) {
	$Title = _('Select Certificate of Analysis To Print');
	include ('includes/header.php');

	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/printer.png" title="', _('Print'), '" alt="" />', ' ', $Title, '
		</p>';

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	echo '<fieldset>
			<legend>', _('Batch Criteria'), '</legend>
			<field>
				<label for="ProdSpec">', _('Enter Item'), ':</label>
				<input type="search" name="ProdSpec" size="25" maxlength="25" />
			</field>
			<field>
				<label for="LotKey">', _('Enter Lot'), ':</label>
				<input type="search" name="LotKey" size="25" maxlength="25" />
			</field>
		</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="pickspec" value="', _('Submit'), '" />
		</div>';

	echo '</form>';

	echo '<form action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '" method="post">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

	$SQLSpecSelect = "SELECT sampleid,
							lotkey,
							prodspeckey,
							description
						FROM qasamples LEFT OUTER JOIN stockmaster
						ON stockmaster.stockid=qasamples.prodspeckey
						WHERE cert='1'
						ORDER BY lotkey";
	$ResultSelection = DB_query($SQLSpecSelect);
	echo '<fieldset>
			<legend>', _('Or Select Existing Lot'), '</legend>
			<field>
				<label for="QASampleID">', _('Select Existing Lot'), ':</label>
				<select name="QASampleID">';
	echo '<option value="">', str_pad(_('Lot/Serial'), 15, '_'), str_pad(_('Item'), 20, '_', STR_PAD_RIGHT), str_pad(_('Description'), 20, '_'), '</option>';
	while ($MyRowSelection = DB_fetch_array($ResultSelection)) {
		echo '<option value="', $MyRowSelection['sampleid'], '">', str_pad($MyRowSelection['lotkey'], 15, '_', STR_PAD_RIGHT), str_pad($MyRowSelection['prodspeckey'], 20, '_'), htmlspecialchars($MyRowSelection['description'], ENT_QUOTES, 'UTF-8', false), '</option>';
	}
	echo '</select>
		</field>
	</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="pickspec" value="', _('Submit'), '" />
		</div>
	</form>';

	include ('includes/footer.php');
	exit();
}

$ErrMsg = _('There was a problem retrieving the Lot Information') . ' ' . $SelectedCOA . ' ' . _('from the database');
if (isset($SelectedCOA)) {
	$SQL = "SELECT lotkey,
					description,
					name,
					method,
					qatests.units,
					type,
					testvalue,
					sampledate,
					groupby,
					prodspeckey
				FROM qasamples
				INNER JOIN sampleresults
					ON sampleresults.sampleid=qasamples.sampleid
				INNER JOIN qatests
					ON qatests.testid=sampleresults.testid
				LEFT OUTER JOIN stockmaster
					ON stockmaster.stockid=qasamples.prodspeckey
				WHERE qasamples.lotkey='" . $SelectedCOA . "'
					AND qasamples.prodspeckey='" . $SelectedSpec . "'
					AND qasamples.cert='1'
					AND sampleresults.showoncert='1'
				ORDER by groupby, sampleresults.testid";
} else {
	$SQL = "SELECT lotkey,
					description,
					name,
					method,
					qatests.units,
					type,
					testvalue,
					sampledate,
					groupby,
					prodspeckey
				FROM qasamples
				INNER JOIN sampleresults
					ON sampleresults.sampleid=qasamples.sampleid
				INNER JOIN qatests
					ON qatests.testid=sampleresults.testid
				LEFT OUTER JOIN stockmaster
					ON stockmaster.stockid=qasamples.prodspeckey
				WHERE qasamples.sampleid='" . $QASampleID . "'
					AND qasamples.cert='1'
					AND sampleresults.showoncert='1'
				ORDER by groupby, sampleresults.testid";
}
$Result = DB_query($SQL, $ErrMsg);

//If there are no rows, there's a problem.
if (DB_num_rows($Result) == 0) {
	$Title = _('Print Certificate of Analysis Error');
	include ('includes/header.php');
	prnMsg(_('Unable to Locate Lot') . ' : ' . $SelectedCOA . ' ', 'error');
	echo '<div class="centre">
			<a href="', $RootPath, '/PDFCOA.php">', _('Certificate of Analysis'), '</a></li></ul>
		</div>';
	include ('includes/footer.php');
	exit;
}
$PaperSize = 'Letter';
if ($QASampleID > '') {
	$MyRow = DB_fetch_array($Result);
	$SelectedCOA = $MyRow['lotkey'];
	DB_data_seek($Result, 0);
}
include ('includes/PDFStarter.php');
$PDF->addInfo('Title', _('Certificate of Analysis'));
$PDF->addInfo('Subject', _('Certificate of Analysis') . ' ' . $SelectedCOA);
$FontSize = 12;
$PageNumber = 1;
$HeaderPrinted = 0;
$LineHeight = $FontSize * 1.25;
$RectHeight = 12;
$SectionHeading = 0;
$CurSection = '';
$SectionTitle = '';
$SectionTrailer = '';

$SectionsArray = array(array('PhysicalProperty', 3, _('Physical Properties'), '', array(260, 110, 135), array(_('Physical Property'), _('Value'), _('Test Method')), array('left', 'center', 'center')), array('', 3, _('Header'), _('* Trailer'), array(260, 110, 135), array(_('Physical Property'), _('Value'), _('Test Method')), array('left', 'center', 'center')), array('Processing', 2, _('Injection Molding Processing Guidelines'), _('* Desicant type dryer required.'), array(240, 265), array(_('Setting'), _('Value')), array('left', 'center')), array('RegulatoryCompliance', 2, _('Regulatory Compliance'), '', array(240, 265), array(_('Regulatory Compliance'), _('Value')), array('left', 'center')));

while ($MyRow = DB_fetch_array($Result)) {
	if ($MyRow['description'] == '') {
		$MyRow['description'] = $MyRow['prodspeckey'];
	}
	$Spec = $MyRow['description'];
	$SampleDate = ConvertSQLDate($MyRow['sampledate']);

	foreach ($SectionsArray as $Row) {
		if ($MyRow['groupby'] == $Row[0]) {
			$SectionColSizes = $Row[4];
			$SectionColLabs = $Row[5];
			$SectionAlign = $Row[6];
		}
	}
	$TrailerPrinted = 1;
	if ($HeaderPrinted == 0) {
		include ('includes/PDFCOAHeader.php');
		$HeaderPrinted = 1;
	}

	if ($CurSection != $MyRow['groupby']) {
		$SectionHeading = 0;
		if ($CurSection != 'NULL' and $PrintTrailer == 1) {
			$PDF->line($XPos + 1, $YPos + $RectHeight, $XPos + 506, $YPos + $RectHeight);
		}
		$PrevTrailer = $SectionTrailer;
		$CurSection = $MyRow['groupby'];
		foreach ($SectionsArray as $Row) {
			if ($MyRow['groupby'] == $Row[0]) {
				$SectionTitle = $Row[2];
				$SectionTrailer = $Row[3];
			}
		}
	}

	if ($SectionHeading == 0) {
		$XPos = 65;
		if ($PrevTrailer > '' and $PrintTrailer == 1) {
			$PrevFontSize = $FontSize;
			$FontSize = 8;
			$LineHeight = $FontSize * 1.25;
			$LeftOvers = $PDF->addTextWrap($XPos + 5, $YPos, 500, $FontSize, $PrevTrailer, 'left');
			$FontSize = $PrevFontSize;
			$LineHeight = $FontSize * 1.25;
			$YPos-= $LineHeight;
			$YPos-= $LineHeight;
		}
		if ($YPos < ($Bottom_Margin + 90)) { // Begins new page
			$PrintTrailer = 0;
			$PageNumber++;
			include ('includes/PDFCOAHeader.php');
		}
		$LeftOvers = $PDF->addTextWrap($XPos, $YPos, 500, $FontSize, $SectionTitle, 'center');
		$YPos-= $LineHeight;
		$PDF->setFont('', 'B');
		$PDF->SetFillColor(200, 200, 200);
		$i = 0;
		foreach ($SectionColLabs as $CurColLab) {
			$ColLabel = $CurColLab;
			$ColWidth = $SectionColSizes[$i];
			++$i;
			$LeftOvers = $PDF->addTextWrap($XPos + 1, $YPos, $ColWidth, $FontSize, $ColLabel, 'center', 1, 'fill');
			$XPos+= $ColWidth;
		}
		$SectionHeading = 1;
		$YPos-= $LineHeight;
		$PDF->setFont('', '');
	} //$SectionHeading==0
	$XPos = 65;
	$Value = '';
	if ($MyRow['testvalue'] > '') {
		$Value = $MyRow['testvalue'];
	} //elseif ($MyRow['rangemin'] > '') {
	//	$Value=$MyRow['rangemin'] . ' - ' . $MyRow['rangemax'];
	//}
	if (strtoupper($Value) <> 'NB' and strtoupper($Value) <> 'NO BREAK') {
		$Value.= ' ' . $MyRow['units'];
	}
	$i = 0;
	foreach ($SectionColLabs as $CurColLab) {
		$ColLabel = $CurColLab;
		$ColWidth = $SectionColSizes[$i];
		$ColAlign = $SectionAlign[$i];
		switch ($i) {
			case 0;
			$DispValue = $MyRow['name'];
		break;
		case 1;
		$DispValue = $Value;
	break;
	case 2;
	$DispValue = $MyRow['method'];
break;
}
$LeftOvers = $PDF->addTextWrap($XPos + 1, $YPos, $ColWidth, $FontSize, $DispValue, $ColAlign, 1);
$XPos+= $ColWidth;
++$i;
}

$YPos-= $LineHeight;
$XPos = 65;
$PrintTrailer = 1;
if ($YPos < ($Bottom_Margin + 80)) { // Begins new page
	$PDF->line($XPos + 1, $YPos + $RectHeight, $XPos + 506, $YPos + $RectHeight);
	$PrintTrailer = 0;
	$PageNumber++;
	include ('includes/PDFCOAHeader.php');
}
//echo 'PrintTrailer'.$PrintTrailer.' '.$PrevTrailer.'<br>' ;

} //while loop
$PDF->line($XPos + 1, $YPos + $RectHeight, $XPos + 506, $YPos + $RectHeight);
if ($SectionTrailer > '') {
	$PrevFontSize = $FontSize;
	$FontSize = 8;
	$LineHeight = $FontSize * 1.25;
	$LeftOvers = $PDF->addTextWrap($XPos + 5, $YPos, 500, $FontSize, $SectionTrailer, 'left');
	$FontSize = $PrevFontSize;
	$LineHeight = $FontSize * 1.25;
	$YPos-= $LineHeight;
	$YPos-= $LineHeight;
}
if ($YPos < ($Bottom_Margin + 85)) { // Begins new page
	$PageNumber++;
	include ('includes/PDFCOAHeader.php');
}

$FontSize = 8;
$LineHeight = $FontSize * 1.25;
$YPos-= $LineHeight;
$YPos-= $LineHeight;

$Disclaimer = $_SESSION['QualityCOAText'];
$LeftOvers = $PDF->addTextWrap($XPos + 5, $YPos, 500, $FontSize, $Disclaimer);
while (mb_strlen($LeftOvers) > 1) {
	$YPos-= $LineHeight;
	$LeftOvers = $PDF->addTextWrap($XPos + 5, $YPos, 500, $FontSize, $LeftOvers, 'left');
}

$PDF->OutputI($_SESSION['DatabaseName'] . 'COA' . date('Y-m-d') . '.pdf');
$PDF->__destruct();

?>