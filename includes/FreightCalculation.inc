<?php

/*Function to calculate the freight cost.
Freight cost is determined by looking for a match of destination city from the Address2 and Address3 fields then looking through the freight company rates for the total KGs and Cubic meters  to figure out the least cost shipping company. */


function CalcFreightCost($TotalValue, $BrAdd2, $BrAdd3, $BrAdd4, $BrAdd5, $BrAddCountry, $TotalVolume, $TotalWeight, $FromLocation, $Currency, $CountriesArra) {

	$CalcFreightCost =9999999999;
	$CalcBestShipper ='';
	global $CountriesArray;

	$ParameterError = FALSE;
	if ((!isset($BrAdd2)) and (!isset($BrAdd3)) and (!isset($BrAdd4)) and (!isset($BrAdd5)) and (!isset($BrAddCountry))){
		// No address field to detect destination ==> ERROR
		$ParameterError = TRUE;
	}
	if ((!isset($TotalVolume)) and (!isset($TotalWeight))){
		// No weight AND no volume ==> ERROR
		$ParameterError = TRUE;
	}
	if (!isset($FromLocation)){
		// No location FROM ==> ERROR
		$ParameterError = TRUE;
	}
	if (!isset($Currency)){
		// No Currency ==> ERROR
		$ParameterError = TRUE;
	}
	if ($ParameterError){
		return array ("NOT AVAILABLE", "NOT AVAILABLE");
	}
	// All parameters are OK, so we move ahead...

	// make an array of all the words that could be the name of the destination zone (city, state or ZIP)
	$FindCity = array($BrAdd2, $BrAdd3, $BrAdd4, $BrAdd5);

	$SQL = "SELECT shipperid,
					kgrate * " . $TotalWeight . " AS kgcost,
					cubrate * " . $TotalVolume . " AS cubcost,
					fixedprice,
					minimumchg
				FROM freightcosts
				WHERE locationfrom = '" . $FromLocation . "'
					AND destinationcountry = '" . $BrAddCountry . "'
					AND maxkgs > " . $TotalWeight . "
					AND maxcub >" . $TotalVolume . "  AND (";

	//ALL suburbs and cities are compared in upper case - so data in freight tables must be in upper case too
	foreach ($FindCity as $City) {
		if ($City != ''){
			$SQL .= " destination LIKE '" . $City . "%' OR";
		}
	}
	if ($BrAddCountry != $CountriesArray[$_SESSION['CountryOfOperation']]) {
		/* For international shipments empty destination (ANY) is allowed */
		$SQL .= " destination = '' OR";
	}
	$SQL = mb_substr($SQL, 0, mb_strrpos($SQL,' OR')) . ')';

	$CalcFreightCostResult = DB_query($SQL);
	if (DB_error_no() != 0) {
		echo _('The freight calculation for the destination city cannot be performed because') . ' - ' . DB_error_msg() . ' - ' . $SQL;
	} elseif (DB_num_rows($CalcFreightCostResult) > 0) {

		while ($MyRow = DB_fetch_array($CalcFreightCostResult)) {

			/**********      FREIGHT CALCULATION
			IF FIXED PRICE TAKE IT IF BEST PRICE SO FAR OTHERWISE
			TAKE HIGHER OF CUBE, KG OR MINIMUM CHARGE COST 	**********/

			if ($MyRow['fixedprice'] != 0) {
				if ($MyRow['fixedprice'] < $CalcFreightCost) {
					$CalcFreightCost = $MyRow['fixedprice'];
					$CalcBestShipper = $MyRow['shipperid'];
				}
			} elseif ($MyRow['cubcost'] > $MyRow['kgcost'] and $MyRow['cubcost'] > $MyRow['minimumchg'] and $MyRow['cubcost'] <= $CalcFreightCost) {

				$CalcFreightCost = $MyRow['cubcost'];
				$CalcBestShipper = $MyRow['shipperid'];

			} elseif ($MyRow['kgcost'] > $MyRow['cubcost'] and $MyRow['kgcost'] > $MyRow['minimumchg'] and $MyRow['kgcost'] <= $CalcFreightCost) {

				$CalcFreightCost = $MyRow['kgcost'];
				$CalcBestShipper = $MyRow['shipperid'];

			} elseif ($MyRow['minimumchg'] < $CalcFreightCost) {

				$CalcFreightCost = $MyRow['minimumchg'];
				$CalcBestShipper = $MyRow['shipperid'];

			}
		}
	} else {
		$CalcFreightCost = "NOT AVAILABLE";
		$CalcBestShipper = 1;
	}
	if ($TotalValue >= $_SESSION['FreightChargeAppliesIfLessThan'] and $_SESSION['FreightChargeAppliesIfLessThan'] != 0){

		/*Even though the order is over the freight free threshold - still need to calculate the best shipper to ensure get best deal*/

		$CalcFreightCost = 0;
	}

	if ($Currency != $_SESSION['CompanyRecord']['currencydefault']){
		$ExRateResult = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $Currency . "'");
		if (DB_num_rows($ExRateResult)>0){
			$ExRateRow = DB_fetch_row($ExRateResult);
			$ExRate = $ExRateRow[0];
		} else {
			$ExRate =1;
		}
		if ($CalcFreightCost != "NOT AVAILABLE"){
			$CalcFreightCost *= $ExRate;
		}
	}

	return array(
		$CalcFreightCost,
		$CalcBestShipper
	);
}

?>