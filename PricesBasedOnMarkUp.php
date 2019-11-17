<?php
include ('includes/session.php');
$Title = _('Update Pricing');
include ('includes/header.php');

echo '<p class="page_title_text">
		<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/money_add.png" title="', _('Search'), '" alt="" />', $Title, '
	</p>';

echo '<div class="page_help_text">', _('This page adds new prices or updates already existing prices for a specified sales type (price list) and currency for the stock category selected - based on a percentage mark up from cost prices or from preferred supplier cost data or from another price list. The rounding factor ensures that prices are at least this amount or a multiple of it. A rounding factor of 5 would mean that prices would be a minimum of 5 and other prices would be expressed as multiples of 5.'), '</div>';

echo '<form method="post" action="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '">';
echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

$SQL = "SELECT sales_type, typeabbrev FROM salestypes";
$PricesResult = DB_query($SQL);

echo '<fieldset>
		<legend>', _('Price Update Criteria'), '</legend>';

echo '<field>
		<label for="PriceList">', _('Select the Price List to update'), ':</label>
		<select name="PriceList">';
if (!isset($_POST['PriceList']) or $_POST['PriceList'] == '0') {
	echo '<option selected="selected" value="0">', _('No Price List Selected'), '</option>';
}
while ($PriceLists = DB_fetch_array($PricesResult)) {
	if (isset($_POST['PriceList']) and $_POST['PriceList'] == $PriceLists['typeabbrev']) {
		echo '<option selected="selected" value="', $PriceLists['typeabbrev'], '">', $PriceLists['sales_type'], '</option>';
	} else {
		echo '<option value="', $PriceLists['typeabbrev'], '">', $PriceLists['sales_type'], '</option>';
	}
}
echo '</select>
	</field>';

$SQL = "SELECT currency, currabrev FROM currencies";
$Result = DB_query($SQL);
echo '<field>
		<label for="CurrCode">', _('Select the price list currency to update'), ':</label>
		<select required="required" name="CurrCode">';
if (!isset($_POST['CurrCode'])) {
	echo '<option selected="selected" value="0">', _('No Price List Currency Selected'), '</option>';
}
while ($Currencies = DB_fetch_array($Result)) {
	if (isset($_POST['CurrCode']) and $_POST['CurrCode'] == $Currencies['currabrev']) {
		echo '<option selected="selected" value="', $Currencies['currabrev'], '">', $Currencies['currency'], '</option>';
	} else {
		echo '<option value="', $Currencies['currabrev'], '">', $Currencies['currency'], '</option>';
	}
}
echo '</select>
	</field>';

if ($_SESSION['WeightedAverageCosting'] == 1) {
	$CostingBasis = _('Weighted Average Costs');
} else {
	$CostingBasis = _('Standard Costs');
}

echo '<field>
		<label for="CostType">', _('Cost/Preferred Supplier Data Or Other Price List'), ':</label>
		<select required="required" name="CostType">';
if (isset($_POST['CostType']) and $_POST['CostType'] == 'PreferredSupplier') {
	echo ' <option selected="selected" value="PreferredSupplier">', _('Preferred Supplier Cost Data'), '</option>
			<option value="StandardCost">', $CostingBasis, '</option>
			<option value="OtherPriceList">', _('Another Price List'), '</option>';
} elseif (isset($_POST['CostType']) and $_POST['CostType'] == 'StandardCost') {
	echo ' <option value="PreferredSupplier">', _('Preferred Supplier Cost Data'), '</option>
			<option selected="selected" value="StandardCost">', $CostingBasis, '</option>
			<option value="OtherPriceList">', _('Another Price List'), '</option>';
} else {
	echo ' <option value="PreferredSupplier">', _('Preferred Supplier Cost Data'), '</option>
			<option value="StandardCost">', $CostingBasis, '</option>
			<option selected="selected" value="OtherPriceList">', _('Another Price List'), '</option>';
}
echo '</select>
	</field>';

DB_data_seek($PricesResult, 0);

if (isset($_POST['CostType']) and $_POST['CostType'] == 'OtherPriceList') {
	echo '<field>
			<label for="BasePriceList">', _('Select the Base Price List to Use'), ':</label>
			<select required="required" name="BasePriceList">';

	if (!isset($_POST['BasePriceList']) or $_POST['BasePriceList'] == '0') {
		echo '<option selected="selected" value="0">', _('No Price List Selected'), '</option>';
	}
	while ($PriceLists = DB_fetch_array($PricesResult)) {
		if (isset($_POST['BasePriceList']) and $_POST['BasePriceList'] == $PriceLists['typeabbrev']) {
			echo '<option selected="selected" value="', $PriceLists['typeabbrev'], '">', $PriceLists['sales_type'], '</option>';
		} else {
			echo '<option value="', $PriceLists['typeabbrev'], '">', $PriceLists['sales_type'], '</option>';
		}
	}
	echo '</select>
		</field>';
}

$SQL = "SELECT categoryid, categorydescription FROM stockcategory ORDER BY categoryid";
$ErrMsg = _('The stock categories could not be retrieved because');
$DbgMsg = _('The SQL used to retrieve stock categories and failed was');
$Result = DB_query($SQL, $ErrMsg, $DbgMsg);
echo '<field>
		<label for="StkCatFrom">', _('Stock Category From'), ':</label>
		<select required="required" name="StkCatFrom">';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['StkCatFrom']) and $MyRow['categoryid'] == $_POST['StkCatFrom']) {
		echo '<option selected="selected" value="', $MyRow['categoryid'], '">', $MyRow['categoryid'], ' - ', $MyRow['categorydescription'], '</option>';
	} else {
		echo '<option value="', $MyRow['categoryid'], '">', $MyRow['categoryid'], ' - ', $MyRow['categorydescription'], '</option>';
	}
}
echo '</select>
	</field>';

DB_data_seek($Result, 0);
echo '<field>
		<label for="StkCatTo">', _('Stock Category To'), ':</label>
		<select required="required" name="StkCatTo">';
while ($MyRow = DB_fetch_array($Result)) {
	if (isset($_POST['StkCatFrom']) and $MyRow['categoryid'] == $_POST['StkCatTo']) {
		echo '<option selected="selected" value="', $MyRow['categoryid'], '">', $MyRow['categoryid'], ' - ', $MyRow['categorydescription'], '</option>';
	} else {
		echo '<option  value="', $MyRow['categoryid'], '">', $MyRow['categoryid'], ' - ', $MyRow['categorydescription'], '</option>';
	}
}
echo '</select>
	</field>';

if (!isset($_POST['RoundingFactor'])) {
	$_POST['RoundingFactor'] = 0.01;
}

if (!isset($_POST['PriceStartDate'])) {
	$_POST['PriceStartDate'] = DateAdd(date($_SESSION['DefaultDateFormat']), 'd', 1);
}

if (!isset($_POST['PriceEndDate'])) {
	$_POST['PriceEndDate'] = DateAdd(date($_SESSION['DefaultDateFormat']), 'y', 1);
}

echo '<field>
		<label for="RoundingFactor">', _('Rounding Factor'), ':</label>
		<input type="text" class="number" name="RoundingFactor" size="6" required="required" maxlength="6" value="', $_POST['RoundingFactor'], '" />
	</field>';

echo '<field>
		<label for="PriceStartDate">', _('New Price To Be Effective From'), ':</label>
		<input type="text" class="date" name="PriceStartDate" size="10" required="required" maxlength="10" value="', $_POST['PriceStartDate'], '" />
	</field>';

echo '<field>
		<label for="PriceEndDate">', _('New Price To Be Effective To (Blank = No End Date)'), ':</label>
		<input type="text" class="date" name="PriceEndDate" size="10" required="required" maxlength="10" value="', $_POST['PriceEndDate'], '" />
	</field>';

if (!isset($_POST['IncreasePercent'])) {
	$_POST['IncreasePercent'] = 0;
}

echo '<field>
		<label for="IncreasePercent">', _('Percentage Increase (positive) or decrease (negative)'), '</label>
		<input type="text" name="IncreasePercent" class="number" size="4" required="required" maxlength="4" value="', $_POST['IncreasePercent'], '" />
	</field>
</fieldset>';

echo '<div class="centre">
		<input type="submit" name="UpdatePrices" value="', _('Update Prices'), '"  onclick="return MakeConfirm(\'', _('Are you sure you wish to update or add all the prices according to the criteria selected?'), '\');" />
	</div>';

echo '</form>';

if (isset($_POST['UpdatePrices'])) {
	$InputError = 0; //assume the best
	if ($_POST['PriceList'] == '0') {
		prnMsg(_('No price list is selected to update. No updates will take place'), 'error');
		$InputError = 1;
	}
	if ($_POST['CurrCode'] == '0') {
		prnMsg(_('No price list currency is selected to update. No updates will take place'), 'error');
		$InputError = 1;
	}

	if (!is_date($_POST['PriceEndDate']) and $_POST['PriceEndDate'] != '') {
		$InputError = 1;
		prnMsg(_('The date the new price is to be in effect to must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
	}
	if (!is_date($_POST['PriceStartDate'])) {
		$InputError = 1;
		prnMsg(_('The date this price is to take effect from must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
	}
	if (Date1GreaterThanDate2($_POST['PriceStartDate'], $_POST['PriceEndDate']) and $_POST['PriceEndDate'] != '') {
		$InputError = 1;
		prnMsg(_('The end date is expected to be after the start date, enter an end date after the start date for this price'), 'error');
	}
	if (Date1GreaterThanDate2(Date($_SESSION['DefaultDateFormat']), $_POST['PriceStartDate'])) {
		$InputError = 1;
		prnMsg(_('The date this new price is to start from is expected to be after today'), 'error');
	}
	if ($_POST['StkCatTo'] < $_POST['StkCatFrom']) {
		prnMsg(_('The stock category from must be before the stock category to - there would be not items in the range to update'), 'error');
		$InputError = 1;
	}
	if ($_POST['CostType'] == 'OtherPriceList' and $_POST['BasePriceList'] == '0') {
		echo '<br />' . _('Base price list selected') . ': ' . $_POST['BasePriceList'];
		prnMsg(_('When you are updating prices based on another price list - the other price list must also be selected. No updates will take place until the other price list is selected'), 'error');
		$InputError = 1;
	}
	if ($_POST['CostType'] == 'OtherPriceList' and $_POST['BasePriceList'] == $_POST['PriceList']) {
		prnMsg(_('When you are updating prices based on another price list - the other price list cannot be the same as the price list being used for the calculation. No updates will take place until the other price list selected is different from the price list to be updated'), 'error');
		$InputError = 1;
	}

	if ($InputError == 0) {
		prnMsg(_('For a log of all the prices changed this page should be printed with CTRL+P'), 'info');
		echo '<br />', _('So we are using a price list/sales type of'), ' : ', $_POST['PriceList'];
		echo '<br />', _('updating only prices in'), ' : ', $_POST['CurrCode'];
		echo '<br />', _('and the stock category range from'), ' : ', $_POST['StkCatFrom'], ' ', _('to'), ' ', $_POST['StkCatTo'];
		echo '<br />', _('and we are applying a markup percent of'), ' : ', $_POST['IncreasePercent'];
		echo '<br />', _('against'), ' ';

		if ($_POST['CostType'] == 'PreferredSupplier') {
			echo _('Preferred Supplier Cost Data');
		} elseif ($_POST['CostType'] == 'OtherPriceList') {
			echo _('Price List') . ' ' . $_POST['BasePriceList'];
		} else {
			echo $CostingBasis;
		}

		if ($_POST['PriceList'] == '0') {
			echo '<br />' . _('The price list/sales type to be updated must be selected first');
			include ('includes/footer.php');
			exit;
		}
		if ($_POST['CurrCode'] == '0') {
			echo '<br />' . _('The currency of prices to be updated must be selected first');
			include ('includes/footer.php');
			exit;
		}
		if (is_date($_POST['PriceEndDate'])) {
			$SQLEndDate = FormatDateForSQL($_POST['PriceEndDate']);
		} else {
			$SQLEndDate = '0000-00-00';
		}
		$SQL = "SELECT stockid,
						stockcosts.materialcost+stockcosts.labourcost+stockcosts.overheadcost AS cost
				FROM stockmaster
				LEFT JOIN stockcosts
					ON stockmaster.stockid=stockcosts.stockid
					AND stockcosts.succeeded=0
				WHERE categoryid>='" . $_POST['StkCatFrom'] . "'
				AND categoryid <='" . $_POST['StkCatTo'] . "'";
		$PartsResult = DB_query($SQL);

		$IncrementPercentage = filter_number_format($_POST['IncreasePercent'] / 100);

		$CurrenciesResult = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_POST['CurrCode'] . "'");
		$CurrencyRow = DB_fetch_row($CurrenciesResult);
		$CurrencyRate = $CurrencyRow[0];

		while ($MyRow = DB_fetch_array($PartsResult)) {

			//Figure out the cost to use
			if ($_POST['CostType'] == 'PreferredSupplier') {
				$SQL = "SELECT purchdata.price/purchdata.conversionfactor/currencies.rate AS cost
							FROM purchdata INNER JOIN suppliers
								ON purchdata.supplierno=suppliers.supplierid
								INNER JOIN currencies
								ON suppliers.currcode=currencies.currabrev
							WHERE purchdata.preferred=1 AND purchdata.stockid='" . $MyRow['stockid'] . "'";
				$ErrMsg = _('Could not get the supplier purchasing information for a preferred supplier for the item') . ' ' . $MyRow['stockid'];
				$PrefSuppResult = DB_query($SQL, $ErrMsg);
				if (DB_num_rows($PrefSuppResult) == 0) {
					prnMsg(_('There is no preferred supplier data for the item') . ' ' . $MyRow['stockid'] . ' ' . _('prices will not be updated for this item'), 'warn');
					$Cost = 0;
				} elseif (DB_num_rows($PrefSuppResult) > 1) {
					prnMsg(_('There is more than a single preferred supplier data for the item') . ' ' . $MyRow['stockid'] . ' ' . _('prices will not be updated for this item'), 'warn');
					$Cost = 0;
				} else {
					$PrefSuppRow = DB_fetch_row($PrefSuppResult);
					$Cost = $PrefSuppRow[0];
				}
			} elseif ($_POST['CostType'] == 'OtherPriceList') {
				$SQL = "SELECT price FROM
								prices
							WHERE typeabbrev= '" . $_POST['BasePriceList'] . "'
								AND currabrev='" . $_POST['CurrCode'] . "'
								AND debtorno=''
								AND startdate <=CURRENT_DATE
								AND (enddate >= CURRENT_DATE OR enddate='0000-00-00')
								AND stockid='" . $MyRow['stockid'] . "'
							ORDER BY startdate DESC";
				$ErrMsg = _('Could not get the base price for the item') . ' ' . $MyRow['stockid'] . _('from the price list') . ' ' . $_POST['BasePriceList'];
				$BasePriceResult = DB_query($SQL, $ErrMsg);
				if (DB_num_rows($BasePriceResult) == 0) {
					prnMsg(_('There is no default price defined in the base price list for the item') . ' ' . $MyRow['stockid'] . ' ' . _('prices will not be updated for this item'), 'warn');
					$Cost = 0;
				} else {
					$BasePriceRow = DB_fetch_row($BasePriceResult);
					$Cost = $BasePriceRow[0];
				}
			} else { //Must be using standard/weighted average costs
				$Cost = $MyRow['cost'];
				if ($Cost <= 0) {
					prnMsg(_('The cost for this item is not set up or is set up as less than or equal to zero - no price changes will be made based on zero cost items. The item concerned is') . ': ' . $MyRow['stockid'], 'warn');
				}
			}
			$_POST['RoundingFactor'] = filter_number_format($_POST['RoundingFactor']);
			if ($_POST['CostType'] != 'OtherPriceList') {
				$RoundedPrice = round(($Cost * (1 + $IncrementPercentage) * $CurrencyRate + ($_POST['RoundingFactor'] / 2)) / $_POST['RoundingFactor']) * $_POST['RoundingFactor'];
				if ($RoundedPrice <= 0) {
					$RoundedPrice = $_POST['RoundingFactor'];
				}
			} else {
				$RoundedPrice = round(($Cost * (1 + $IncrementPercentage) + ($_POST['RoundingFactor'] / 2)) / $_POST['RoundingFactor']) * $_POST['RoundingFactor'];
				if ($RoundedPrice <= 0) {
					$RoundedPrice = $_POST['RoundingFactor'];
				}
			}

			if ($Cost > 0) {
				$CurrentPriceResult = DB_query("SELECT price,
											 		   startdate,
													   enddate
													FROM prices
													WHERE typeabbrev= '" . $_POST['PriceList'] . "'
													AND debtorno =''
													AND currabrev='" . $_POST['CurrCode'] . "'
													AND startdate <=CURRENT_DATE
													AND (enddate>=CURRENT_DATE OR enddate='0000-00-00')
													AND stockid='" . $MyRow['stockid'] . "'");
				if (DB_num_rows($CurrentPriceResult) == 1) {
					$DayPriorToNewPrice = DateAdd($_POST['PriceStartDate'], 'd', -1);
					$CurrentPriceRow = DB_fetch_array($CurrentPriceResult);
					$UpdateSQL = "UPDATE prices SET enddate='" . FormatDateForSQL($DayPriorToNewPrice) . "'
												WHERE typeabbrev='" . $_POST['PriceList'] . "'
												AND currabrev='" . $_POST['CurrCode'] . "'
												AND debtorno=''
												AND startdate ='" . $CurrentPriceRow['startdate'] . "'
												AND enddate ='" . $CurrentPriceRow['enddate'] . "'
												AND stockid='" . $MyRow['stockid'] . "'";
					$ErrMsg = _('Error updating prices for') . ' ' . $MyRow['stockid'] . ' ' . _('because');
					$Result = DB_query($UpdateSQL, $ErrMsg);

				}
				$SQL = "INSERT INTO prices (stockid,
												typeabbrev,
												currabrev,
												startdate,
												enddate,
												price)
								VALUES ('" . $MyRow['stockid'] . "',
										'" . $_POST['PriceList'] . "',
										'" . $_POST['CurrCode'] . "',
										'" . FormatDateForSQL($_POST['PriceStartDate']) . "',
										'" . $SQLEndDate . "',
								 		'" . filter_number_format($RoundedPrice) . "')";
				$ErrMsg = _('Error inserting new price for') . ' ' . $MyRow['stockid'] . ' ' . _('because');
				$Result = DB_query($SQL, $ErrMsg);
				prnMsg(_('Inserting new price for') . ' ' . $MyRow['stockid'] . ' ' . _('to') . ' ' . $RoundedPrice, 'info');

			} // end if cost > 0
			
		} //end while loop around items in the category
		
	}
}
include ('includes/footer.php');
?>