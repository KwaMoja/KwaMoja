<?php
/* $Id: PDFPriceLabels.php 5228 2012-04-06 02:48:00Z vvs2012 $*/

include('includes/session.inc');
include('includes/barcodepack/class.code128.php');

$PtsPerMM = 2.83465; //pdf points per mm

if ((isset($_POST['ShowLabels']) or isset($_POST['SetAll']))
	and isset($_POST['FromCriteria'])
	and mb_strlen($_POST['FromCriteria'])>=1
	and isset($_POST['ToCriteria'])
	and mb_strlen($_POST['ToCriteria'])>=1){

	$Title = _('Print Labels');
	include('includes/header.inc');

	$SQL = "SELECT prices.stockid,
					stockmaster.description,
					stockmaster.barcode,
					prices.price,
					currencies.decimalplaces
			FROM stockmaster INNER JOIN	stockcategory
   				 ON stockmaster.categoryid=stockcategory.categoryid
			INNER JOIN prices
				ON stockmaster.stockid=prices.stockid
			INNER JOIN currencies
				ON prices.currabrev=currencies.currabrev
			WHERE stockmaster.categoryid >= '" . $_POST['FromCriteria'] . "'
			AND stockmaster.categoryid <= '" . $_POST['ToCriteria'] . "'
			AND prices.typeabbrev='" . $_POST['SalesType'] . "'
			AND prices.currabrev='" . $_POST['Currency'] . "'
			AND prices.startdate<='" . FormatDateForSQL($_POST['EffectiveDate']) . "'
			AND (prices.enddate='0000-00-00' OR prices.enddate>'" . FormatDateForSQL($_POST['EffectiveDate']) . "')
			AND prices.debtorno=''
			ORDER BY prices.currabrev,
				stockmaster.categoryid,
				stockmaster.stockid,
				prices.startdate";

	$LabelsResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
		prnMsg( _('The Price Labels could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<br /><a href="' .$RootPath .'/index.php">'.  _('Back to the menu'). '</a>';
		if ($debug==1){
			prnMsg(_('For debugging purposes the SQL used was:') . $SQL,'error');
		}
		include('includes/footer.inc');
		exit;
	}
	if (DB_num_rows($LabelsResult)==0){
		prnMsg(_('There were no price labels to print out for the category specified'),'warn');
		echo '<br /><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">'. _('Back').'</a>';
		include('includes/footer.inc');
		exit;
	}

	echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/printer.png" title="' . _('Price Labels') . '" alt="' . _('Print Price Labels') . '" />
		 ' . ' ' . _('Print Price Labels') . '</p>';

	echo '<form onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (!isset($_POST['All'])) {
		$_POST['All'] = 0;
	}

	if (isset($_POST['SetAll'])) {
		for ($i=0;$i < $_POST['NoOfLabels'];$i++){
			$_POST['Qty' . $i] = $_POST['All'];
		}
	}

	echo '<table class="selection" summary="' . _('Labels to print') . '">
			<tr>
				<th colspan="4">
					<input type="submit" name="SetAll" value="' . _('Set all to') . '" />
					<input type="input" name="All" size="4" class="number" value="' . $_POST['All'] . '" />
				</th>
			<tr>
				<th>' . _('Item Code') . '</th>
				<th>' . _('Item Description') . '</th>
				<th>' . _('Price') . '</th>
				<th>' . _('Qty To Print') . '</th>
			</tr>';

	$i=0;
	while ($LabelRow = DB_fetch_array($LabelsResult)){
		if (!isset($_POST['Qty' . $i])) {
			$_POST['Qty' . $i] = 0;
		}
		echo '<tr>
				<td>' . $LabelRow['stockid'] . '</td>
				<td>' . $LabelRow['description'] . '</td>
				<td class="number">' . locale_number_format($LabelRow['price'],$LabelRow['decimalplaces']) . '</td>
				<td><input type="input" name="Qty' . $i . '" size="4" class="number" value="' . locale_number_format($_POST['Qty' . $i], 0) . '" /></td>
			</tr>';
		echo '<input type="hidden" name="StockID' . $i . '" value="' . $LabelRow['stockid'] . '" />
			<input type="hidden" name="Description' . $i . '" value="' . $LabelRow['description'] . '" />
			<input type="hidden" name="Barcode' . $i . '" value="' . $LabelRow['barcode'] . '" />
			<input type="hidden" name="Price' . $i . '" value="' . locale_number_format($LabelRow['price'],$LabelRow['decimalplaces']) . '" />';
		$i++;
	}
	$i--;
	echo '</table>
		<input type="hidden" name="NoOfLabels" value="' . ($i+1) . '" />
		<input type="hidden" name="LabelID" value="' . $_POST['LabelID'] . '" />
		<input type="hidden" name="FromCriteria" value="' . $_POST['FromCriteria'] . '" />
		<input type="hidden" name="ToCriteria" value="' . $_POST['ToCriteria'] . '" />
		<input type="hidden" name="SalesType" value="' . $_POST['SalesType'] . '" />
		<input type="hidden" name="Currency" value="' . $_POST['Currency'] . '" />
		<input type="hidden" name="EffectiveDate" value="' . $_POST['EffectiveDate'] . '" />
		<br />
		<div class="centre">

			<input type="submit" name="PrintLabels" value="'. _('Print Labels'). '" />
		</div>
		<br />
			<div class="centre">
				<a href="'. $RootPath . '/Labels.php">' . _('Label Template Maintenance'). '</a>
			</div>
		</form>';
	include('includes/footer.inc');
	exit;
}

if (isset($_POST['PrintLabels'])
	and isset($_POST['NoOfLabels'])
	and $_POST['NoOfLabels']>0){
	$NoOfLabels = 0;
	for ($i=0;$i < $_POST['NoOfLabels'];$i++){
		if (isset($_POST['Qty'.$i])){
			$NoOfLabels++;
		}
	}
	if ($NoOfLabels ==0){
		prnMsg(_('There are no labels selected to print'),'info');
	}
}
if (isset($_POST['PrintLabels']) and $NoOfLabels>0) {

	$result = DB_query("SELECT 	description,
								pagewidth*" . $PtsPerMM . " as page_width,
								pageheight*" . $PtsPerMM . " as page_height,
								width*" . $PtsPerMM . " as label_width,
								height*" . $PtsPerMM . " as label_height,
								rowheight*" . $PtsPerMM . " as label_rowheight,
								columnwidth*" . $PtsPerMM . " as label_columnwidth,
								topmargin*" . $PtsPerMM . " as label_topmargin,
								leftmargin*" . $PtsPerMM . " as label_leftmargin
						FROM labels
						WHERE labelid='" . $_POST['LabelID'] . "'",
						$db);
	$LabelDimensions = DB_fetch_array($result);

	$result = DB_query("SELECT fieldvalue,
								vpos,
								hpos,
								fontsize,
								barcode
						FROM labelfields
						WHERE labelid = '" . $_POST['LabelID'] . "'",
						$db);
	$LabelFields = array();
	$i=0;
	while ($LabelFieldRow = DB_fetch_array($result)){
		if ($LabelFieldRow['fieldvalue'] == 'itemcode'){
			$LabelFields[$i]['FieldValue'] = 'stockid';
		} elseif ($LabelFieldRow['fieldvalue'] == 'itemdescription'){
			$LabelFields[$i]['FieldValue'] = 'description';
		} else {
			$LabelFields[$i]['FieldValue'] = $LabelFieldRow['fieldvalue'];
		}
		$LabelFields[$i]['VPos'] = $LabelFieldRow['vpos']*$PtsPerMM;
		$LabelFields[$i]['HPos'] = $LabelFieldRow['hpos']*$PtsPerMM;
		$LabelFields[$i]['FontSize'] = $LabelFieldRow['fontsize'];
		$LabelFields[$i]['Barcode'] = $LabelFieldRow['barcode'];
		$i++;
	}

	include('includes/PDFStarter.php');
	$Top_Margin = $LabelDimensions['label_topmargin'];
	$Left_Margin = $LabelDimensions['label_leftmargin'];
	$Page_Height = $LabelDimensions['page_height'];
	$Page_Width = $LabelDimensions['page_width'];
	$Right_Margin =0;
	$Bottom_Margin =0;

	$pdf->addInfo('Title', $LabelDimensions['description'] . ' ' . _('Price Labels') );
	$pdf->addInfo('Subject', $LabelDimensions['description'] . ' ' . _('Price Labels') );
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);


	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);

	$PageNumber=1;
	//go down first then accross
	$YPos = $Page_Height - $Top_Margin; //top of current label
	$XPos = $Left_Margin; // left of current label

	for ($i=0;$i < $_POST['NoOfLabels'];$i++){
		if (isset($_POST['Qty'.$i]) and ($_POST['Qty'.$i]>0)){
			for ($j=0;$j<$_POST['Qty'.$i]; $j++) {
				foreach ($LabelFields as $Field) {

					if ($Field['FieldValue']== 'price'){
						$Value = $_POST['Price' . $i];
					} elseif ($Field['FieldValue']== 'stockid'){
						$Value = $_POST['StockID' . $i];
					} elseif ($Field['FieldValue']== 'description'){
						$Value = $_POST['Description' . $i];
					} elseif ($Field['FieldValue']== 'barcode'){
						$Value = $_POST['Barcode' . $i];
					}
					if ($Field['FieldValue'] == 'price'){ //need to format for the number of decimal places
						$LeftOvers = $pdf->addTextWrap($XPos+$Field['HPos'],$YPos-$LabelDimensions['label_height']+$Field['VPos'],$LabelDimensions['label_width']-$Field['HPos'],$Field['FontSize'],$_POST['Price' . $i],'center');
					} elseif($Field['Barcode']==1) {

						$BarcodeImage = new code128(str_replace('_','',$Value));

						ob_start();
						imagepng(imagepng($BarcodeImage->draw()));
						$Image_String = ob_get_contents();
						ob_end_clean();

						$pdf->addJpegFromFile('@' . $Image_String,$XPos+$Field['HPos'],$YPos-$LabelDimensions['label_height']+$Field['VPos'],'', $Field['FontSize']);

					} else {
						$LeftOvers = $pdf->addTextWrap($XPos+$Field['HPos'],$YPos-$LabelDimensions['label_height']+$Field['VPos'],$LabelDimensions['label_width']-$Field['HPos']-20,$Field['FontSize'],$Value);
					}
				} // end loop through label fields
				if ($NoOfLabels>0) {
					//setup $YPos and $XPos for the next label
					if (($YPos - $LabelDimensions['label_rowheight']) < $LabelDimensions['label_height']){
						/* not enough space below the above label to print a new label
						* so the above was the last label in the column
						* need to start either a new column or new page
						*/
						if (($Page_Width - $XPos - $LabelDimensions['label_columnwidth']) < $LabelDimensions['label_width']) {
							/* Not enough space to start a new column so we are into a new page
							*/
							$pdf->newPage();
							$PageNumber++;
							$YPos = $Page_Height - $Top_Margin; //top of next label
							$XPos = $Left_Margin; // left of next label
						} else {
							/* There is enough space for another column */
							$YPos = $Page_Height - $Top_Margin; //back to the top of next label column
							$XPos += $LabelDimensions['label_columnwidth']; // left of next label
						}
					} else {
						/* There is space below to print a label
						*/
						$YPos -= $LabelDimensions['label_rowheight']; //Top of next label
					}
				}//end if there is another label to print
			}
			$NoOfLabels--;
		} //this label is set to print
	} //loop through labels selected to print


	$FileName=$_SESSION['DatabaseName']. '_' . _('Price_Labels') . '_' . date('Y-m-d').'.pdf';
//	ob_clean();
	$pdf->OutputI($FileName);
	$pdf->__destruct();

} else { /*The option to print PDF was not hit */

	$Title= _('Price Labels');
	include('includes/header.inc');

	echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/printer.png" title="' . _('Price Labels') . '" alt="' . _('Print Price Labels') . '" />
		 ' . ' ' . _('Print Price Labels') . '</p>';

	if (!isset($_POST['FromCriteria']) or !isset($_POST['ToCriteria'])) {

	/*if $FromCriteria is not set then show a form to allow input	*/

		echo '<form onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<table class="selection" summary="' . _('Print Price Labels') . '">';
		echo '<tr>
				<td>' . _('Label to print') . ':</td>
				<td><select name="LabelID">';

		$LabelResult = DB_query("SELECT labelid, description FROM labels",$db);
		while ($LabelRow = DB_fetch_array($LabelResult)){
			echo '<option value="' . $LabelRow['labelid'] . '">' . $LabelRow['description'] . '</option>';
		}
		echo '</select></td>
			</tr>
			<tr>
				<td>'. _('From Inventory Category Code') .':</td>
				<td><select name="FromCriteria">';

		$CatResult= DB_query("SELECT categoryid, categorydescription FROM stockcategory ORDER BY categoryid",$db);
		while ($myrow = DB_fetch_array($CatResult)){
			echo '<option value="' . $myrow['categoryid'] . '">' . $myrow['categoryid'] . ' - ' . $myrow['categorydescription'] . '</option>';
		}
		echo '</select>
				</td>
			</tr>';

		echo '<tr>
				<td>' . _('To Inventory Category Code'). ':</td>
				<td><select name="ToCriteria">';

		/*Set the index for the categories result set back to 0 */
		DB_data_seek($CatResult,0);

		while ($myrow = DB_fetch_array($CatResult)){
			echo '<option value="' . $myrow['categoryid'] . '">' . $myrow['categoryid'] . ' - ' . $myrow['categorydescription'] . '</option>';
		}
		echo '</select>
				</td>
			</tr>';

		echo '<tr>
				<td>' . _('For Sales Type/Price List').':</td>
				<td><select name="SalesType">';
		$sql = "SELECT sales_type, typeabbrev FROM salestypes";
		$SalesTypesResult=DB_query($sql,$db);

		while ($myrow=DB_fetch_array($SalesTypesResult)){
			if ($_SESSION['DefaultPriceList']==$myrow['typeabbrev']){
				echo '<option selected="selected" value="' . $myrow['typeabbrev'] . '">' . $myrow['sales_type'] . '</option>';
			} else {
				echo '<option value="' . $myrow['typeabbrev'] . '">' . $myrow['sales_type'] . '</option>';
			}
		}
		echo '</select></td></tr>';

		echo '<tr>
				<td>' . _('For Currency').':</td>
				<td><select name="Currency">';
		$sql = "SELECT currabrev, country, currency FROM currencies";
		$CurrenciesResult=DB_query($sql,$db);

		while ($myrow=DB_fetch_array($CurrenciesResult)){
			if ($_SESSION['CompanyRecord']['currencydefault']==$myrow['currabrev']){
				echo '<option selected="selected" value="' . $myrow['currabrev'] . '">' . $myrow['country'] . ' - ' .$myrow['currency'] . '</option>';
			} else {
				echo '<option value="' . $myrow['currabrev'] . '">' . $myrow['country'] . ' - ' .$myrow['currency'] . '</option>';
			}
		}
		echo '</select></td></tr>';

		echo '<tr>
				<td>' . _('Effective As At') . ':</td>
				<td><input type="text" size="11" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="EffectiveDate" value="' . Date($_SESSION['DefaultDateFormat']) . '" /></td>
			</tr>';

		echo '</table>
				<br />
				<div class="centre">
					<input type="submit" name="ShowLabels" value="'. _('Show Labels'). '" />
				</div>
				<br />
				<div class="centre">
					<a href="'. $RootPath . '/Labels.php">' . _('Label Template Maintenance'). '</a>
				</div>
				</form>';

	}
	include('includes/footer.inc');

} /*end of else not PrintPDF */

?>