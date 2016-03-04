<?php

/*  ******************************************  */
/** STANDARD MESSAGE HANDLING & FORMATTING **/
/*  ******************************************  */

function prnMsg($Msg, $Type = 'info', $Prefix = '') {
	global $Messages;
	$Messages[] = array(
		$Msg,
		$Type,
		$Prefix
	);

} //prnMsg

function reverse_escape($str) {
	$search = array(
		"\\\\",
		"\\0",
		"\\n",
		"\\r",
		"\Z",
		"\'",
		'\"'
	);
	$replace = array(
		"\\",
		"\0",
		"\n",
		"\r",
		"\x1a",
		"'",
		'"'
	);
	return str_replace($search, $replace, $str);
}

function getMsg($Msg, $Type = 'info', $Prefix = '') {
	$Colour = '';
	if (isset($_SESSION['LogSeverity']) and $_SESSION['LogSeverity'] > 0) {
		$LogFile = fopen($_SESSION['LogPath'] . '/KwaMoja.log', 'a');
	}
	switch ($Type) {
		case 'error':
			$Class = 'error';
			$Prefix = $Prefix ? $Prefix : _('ERROR') . ' ' . _('Message Report');
			if (isset($_SESSION['LogSeverity']) and $_SESSION['LogSeverity'] > 0) {
				fwrite($LogFile, date('Y-m-d H:i:s') . ',' . $Type . ',' . $_SESSION['UserID'] . ',' . trim(str_replace("<br />", " ", $Msg), ',') . "\n");
			}
			break;
		case 'warn':
			$Class = 'warn';
			$Prefix = $Prefix ? $Prefix : _('WARNING') . ' ' . _('Message Report');
			if (isset($_SESSION['LogSeverity']) and $_SESSION['LogSeverity'] > 1) {
				fwrite($LogFile, date('Y-m-d H:i:s') . ',' . $Type . ',' . $_SESSION['UserID'] . ',' . trim(str_replace("<br />", " ", $Msg), ',') . "\n");
			}
			break;
		case 'success':
			$Class = 'success';
			$Prefix = $Prefix ? $Prefix : _('SUCCESS') . ' ' . _('Report');
			if (isset($_SESSION['LogSeverity']) and $_SESSION['LogSeverity'] > 3) {
				fwrite($LogFile, date('Y-m-d H:i:s') . ',' . $Type . ',' . $_SESSION['UserID'] . ',' . trim(str_replace("<br />", " ", $Msg), ',') . "\n");
			}
			break;
		case 'info':
		default:
			$Prefix = $Prefix ? $Prefix : _('INFORMATION') . ' ' . _('Message');
			$Class = 'info';
			if (isset($_SESSION['LogSeverity']) and $_SESSION['LogSeverity'] > 2) {
				fwrite($LogFile, date('Y-m-d H:i:s') . ',' . $Type . ',' . $_SESSION['UserID'] . ',' . trim(str_replace("<br />", " ", $Msg), ',') . "\n");
			}
	}
	return '<div class="' . $Class . '"><b>' . $Prefix . '</b> : ' . $Msg . '</div>';
} //getMsg

function IsEmailAddress($Email) {

	$AtIndex = strrpos($Email, "@");
	if ($AtIndex == false) {
		return false; // No @ sign is not acceptable.
	}

	if (preg_match('/\\.\\./', $Email)) {
		return false; // > 1 consecutive dot is not allowed.
	}
	//  Check component length limits
	$Domain = mb_substr($Email, $AtIndex + 1);
	$Local = mb_substr($Email, 0, $AtIndex);
	$LocalLen = mb_strlen($Local);
	$DomainLen = mb_strlen($Domain);
	if ($LocalLen < 1 or $LocalLen > 64) {
		// local part length exceeded
		return false;
	}
	if ($DomainLen < 1 or $DomainLen > 255) {
		// domain part length exceeded
		return false;
	}

	if ($Local[0] == '.' or $Local[$LocalLen - 1] == '.') {
		// local part starts or ends with '.'
		return false;
	}
	if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $Domain)) {
		// character not valid in domain part
		return false;
	}
	if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $Local))) {
		// character not valid in local part unless local part is quoted
		if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $Local))) {
			return false;
		}
	}

	//  Check for a DNS 'MX' or 'A' record.
	//  Windows supported from PHP 5.3.0 on - so check.
	$Ret = true;
	/*  Apparentely causes some problems on some versions - perhaps bleeding edge just yet
	if (version_compare(PHP_VERSION, '5.3.0') >= 0 or mb_strtoupper(mb_substr(PHP_OS, 0, 3) !== 'WIN')) {
	$Ret = checkdnsrr( $Domain, 'MX' ) OR checkdnsrr( $Domain, 'A' );
	}
	*/
	return $Ret;
}


function ContainsIllegalCharacters($CheckVariable) {

	if (mb_strstr($CheckVariable, "'") or mb_strstr($CheckVariable, '+') or mb_strstr($CheckVariable, '?') or mb_strstr($CheckVariable, '.') or mb_strstr($CheckVariable, "\"") or mb_strstr($CheckVariable, '&') or mb_strstr($CheckVariable, "\\") or mb_strstr($CheckVariable, '"') or mb_strstr($CheckVariable, '>') or mb_strstr($CheckVariable, '<')) {

		return true;
	} else {
		return false;
	}
}


function pre_var_dump(&$var) {
	echo '<div align=left><pre>';
	var_dump($var);
	echo '</pre></div>';
}



class XmlElement {
	var $name;
	var $attributes;
	var $content;
	var $children;
}

function GetECBCurrencyRates() {
	/* See http://www.ecb.int/stats/exchange/eurofxref/html/index.en.html
	for detail of the European Central Bank rates - published daily */
	if (http_file_exists('http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml')) {
		$xml = file_get_contents('http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml');
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, $xml, $tags);
		xml_parser_free($parser);

		$elements = array(); // the currently filling [child] XmlElement array
		$stack = array();
		foreach ($tags as $tag) {
			$index = count($elements);
			if ($tag['type'] == 'complete' or $tag['type'] == 'open') {
				$elements[$index] = new XmlElement;
				$elements[$index]->name = $tag['tag'];
				if (isset($tag['attributes'])) {
					$elements[$index]->attributes = $tag['attributes'];
				}
				if (isset($tag['value'])) {
					$elements[$index]->content = $tag['value'];
				}
				if ($tag['type'] == 'open') { // push
					$elements[$index]->children = array();
					$stack[count($stack)] =& $elements;
					$elements =& $elements[$index]->children;
				}
			}
			if ($tag['type'] == 'close') { // pop
				$elements =& $stack[count($stack) - 1];
				unset($stack[count($stack) - 1]);
			}
		}


		$Currencies = array();
		foreach ($elements[0]->children[2]->children[0]->children as $CurrencyDetails) {
			$Currencies[$CurrencyDetails->attributes['currency']] = $CurrencyDetails->attributes['rate'];
		}
		$Currencies['EUR'] = 1; //ECB delivers no rate for Euro
		//return an array of the currencies and rates
		return $Currencies;
	} else {
		return false;
	}
}

function GetCurrencyRate($CurrCode, $CurrencyRates) {
	if ((!isset($CurrenciesArray[$CurrCode]) or !isset($CurrenciesArray[$_SESSION['CompanyRecord']['currencydefault']])) and $_SESSION['UpdateCurrencyRatesDaily'] != '0'){
		return google_currency_rate($CurrCode);
	} elseif ($CurrCode == 'EUR') {
		if ($CurrencyRates[$_SESSION['CompanyRecord']['currencydefault']] == 0) {
			return 0;
		} else {
			return 1 / $CurrencyRates[$_SESSION['CompanyRecord']['currencydefault']];
		}
	} else {
		if (!isset($CurrencyRates[$_SESSION['CompanyRecord']['currencydefault']]) or $CurrencyRates[$_SESSION['CompanyRecord']['currencydefault']] == 0) {
			return 0;
		} else {
			return $CurrencyRates[$CurrCode] / $CurrencyRates[$_SESSION['CompanyRecord']['currencydefault']];
		}
	}
}

function quote_oanda_currency($CurrCode) {
	if (http_file_exists('http://www.oanda.com/convert/fxdaily?value=1&redirected=1&exch=' . $CurrCode . '&format=CSV&dest=Get+Table&sel_list=' . $_SESSION['CompanyRecord']['currencydefault'])) {
		$page = file('http://www.oanda.com/convert/fxdaily?value=1&redirected=1&exch=' . $CurrCode . '&format=CSV&dest=Get+Table&sel_list=' . $_SESSION['CompanyRecord']['currencydefault']);
		$match = array();
		preg_match('/(.+),(\w{3}),([0-9.]+),([0-9.]+)/i', implode('', $page), $match);

		if (sizeof($match) > 0) {
			return $match[3];
		} else {
			return false;
		}
	}
}

function google_currency_rate($CurrCode) {

	$Rate = 0;
	$PageLines = file('http://www.google.com/finance/converter?a=1&from=' . $_SESSION['CompanyRecord']['currencydefault'] . '&to=' . $CurrCode);
	foreach ($PageLines as $Line) {
		if (mb_strpos($Line, 'currency_converter_result')) {
			$Length = mb_strpos($Line, '</span>') - 58;
			$Rate = floatval(mb_substr($Line, 58, $Length));
		}
	}
	return $Rate;
}

function AddCarriageReturns($str) {
	return str_replace('\r\n', chr(10), $str);
}


function wikiLink($WikiType, $WikiPageID) {
	if (strstr($_SESSION['WikiPath'], 'http:')) {
		$WikiPath = $_SESSION['WikiPath'];
	} else {
		$WikiPath = '../' . $_SESSION['WikiPath'] . '/';
	}
	if ($_SESSION['WikiApp'] == _('WackoWiki')) {
		echo '<a href=""' . $WikiPath . $WikiType . $WikiPageID . '"" target="_blank">' . _('Wiki ' . $WikiType . ' Knowledge Base') . ' </a>';
	} elseif ($_SESSION['WikiApp'] == _('MediaWiki')) {
		echo '<a target="_blank" href="' . $WikiPath . 'index.php?title=' . $WikiType . '/' . $WikiPageID . '">' . _('Wiki ' . $WikiType . ' Knowledge Base') . '</a>';
	} elseif ($_SESSION['WikiApp'] == _('DokuWiki')) {
		echo '<a href="' . $WikiPath . '/doku.php?id=' . urlencode($WikiType . ':' . $WikiPageID) . '" target="_blank">' . _('Wiki ' . $WikiType . ' Knowlege Base') . '</a>';
	}
}


//  Lindsay debug stuff
function LogBackTrace($dest = 0) {
	error_log("***BEGIN STACK BACKTRACE***", $dest);

	$stack = debug_backtrace();
	//  Leave out our frame and the topmost - huge for xmlrpc!
	for ($ii = 1; $ii < count($stack) - 3; $ii++) {
		$frame = $stack[$ii];
		$Msg = "FRAME " . $ii . ": ";
		if (isset($frame['file'])) {
			$Msg .= "; file=" . $frame['file'];
		}
		if (isset($frame['line'])) {
			$Msg .= "; line=" . $frame['line'];
		}
		if (isset($frame['function'])) {
			$Msg .= "; function=" . $frame['function'];
		}
		if (isset($frame['args'])) {
			// Either function args, or included file name(s)
			$Msg .= ' (';
			foreach ($frame['args'] as $val) {

				$typ = gettype($val);
				switch ($typ) {
					case 'array':
						$Msg .= '[ ';
						foreach ($val as $v2) {
							if (gettype($v2) == 'array') {
								$Msg .= '[ ';
								foreach ($v2 as $v3)
									$Msg .= $v3;
								$Msg .= ' ]';
							} else {
								$Msg .= $v2 . ', ';
							}
							$Msg .= ' ]';
							break;
						}
					case 'string':
						$Msg .= $val . ', ';
						break;

					case 'integer':
						$Msg .= sprintf("%d, ", $val);
						break;

					default:
						$Msg .= '<' . gettype($val) . '>, ';
						break;

				}
				$Msg .= ' )';
			}
		}
		error_log($Msg, $dest);
	}

	error_log('++++END STACK BACKTRACE++++', $dest);

	return;
}

function http_file_exists($url) {
	$f = @fopen($url, 'r');
	if ($f) {
		fclose($f);
		return true;
	}
	return false;
}

/*Functions to display numbers in locale of the user */

function locale_number_format($Number, $DecimalPlaces = 0) {
	global $DecimalPoint;
	global $ThousandsSeparator;
	if(substr($_SESSION['Language'], 3, 2) == 'IN') {
		return indian_number_format(floatval($Number), $DecimalPlaces);
	} else {
		if (!is_numeric($DecimalPlaces) and $DecimalPlaces == 'Variable') {
			$DecimalPlaces = mb_strlen($Number) - mb_strlen(intval($Number));
			if ($DecimalPlaces > 0) {
				$DecimalPlaces--;
			}
		}
		return number_format(floatval($Number), $DecimalPlaces, $DecimalPoint, $ThousandsSeparator);
	}
}

/* and to parse the input of the user into useable number */

function filter_number_format($Number) {
	global $DecimalPoint;
	global $ThousandsSeparator;
	$SQLFormatNumber = str_replace($DecimalPoint, '.', str_replace($ThousandsSeparator, '', trim($Number)));
	/*It is possible if the user entered the $DecimalPoint as a thousands separator and the $DecimalPoint is a comma that the result of this could contain several periods "." so need to ditch all but the last "." */
	if (mb_substr_count($SQLFormatNumber, '.') > 1) {
		return str_replace('.', '', mb_substr($SQLFormatNumber, 0, mb_strrpos($SQLFormatNumber, '.'))) . mb_substr($SQLFormatNumber, mb_strrpos($SQLFormatNumber, '.'));

		echo '<br /> Number of periods: ' . $NumberOfPeriods . ' $SQLFormatNumber = ' . $SQLFormatNumber;

	} else {
		return $SQLFormatNumber;
	}
}


function indian_number_format($Number, $DecimalPlaces) {
	$IntegerNumber = intval($Number);
	$DecimalValue = $Number - $IntegerNumber;
	if ($DecimalPlaces != 'Variable') {
		$DecimalValue = round($DecimalValue, $DecimalPlaces);
	}
	if ($DecimalPlaces != 'Variable' and strlen(substr($DecimalValue, 2)) > 0) {
		/*If the DecimalValue is longer than '0.' then chop off the leading 0*/
		$DecimalValue = substr($DecimalValue, 1);
		if ($DecimalPlaces > 0) {
			$DecimalValue = str_pad($DecimalValue, $DecimalPlaces, '0');
		} else {
			$DecimalValue = '';
		}
	} else {
		if ($DecimalPlaces != 'Variable' and $DecimalPlaces > 0) {
			$DecimalValue = '.' . str_pad($DecimalValue, $DecimalPlaces, '0');
		} elseif ($DecimalPlaces == 0) {
			$DecimalValue = '';
		}
	}
	if (strlen($IntegerNumber) > 3) {
		$LastThreeNumbers = substr($IntegerNumber, strlen($IntegerNumber) - 3, strlen($IntegerNumber));
		$RestUnits = substr($IntegerNumber, 0, strlen($IntegerNumber) - 3); // extracts the last three digits
		$RestUnits = (strlen($RestUnits) % 2 == 1) ? '0' . $RestUnits : $RestUnits; // explodes the remaining digits in 2's formats, adds a zero in the beginning to maintain the 2's grouping.
		$FirstPart = '';
		$ExplodedUnits = str_split($RestUnits, 2);
		$SizeOfExplodedUnits = sizeOf($ExplodedUnits);
		for ($i = 0; $i < $SizeOfExplodedUnits; $i++) {
			$FirstPart .= intval($ExplodedUnits[$i]) . ','; // creates each of the 2's group and adds a comma to the end
		}

		return $FirstPart . $LastThreeNumbers . $DecimalValue;
	} else {
		return $IntegerNumber . $DecimalValue;
	}
}

function SendMailBySmtp(&$Mail, $To) {
	if (IsEmailAddress($_SESSION['SMTPSettings']['username'])) { //user has set the fully mail address as user name
		$SendFrom = $_SESSION['SMTPSettings']['username'];
	} else { //user only set it's name instead of fully mail address
		if (strpos($_SESSION['SMTPSettings']['host'], 'mail') !== false) {
			$SubStr = 'mail';
		} elseif (strpos($_SESSION['SMTPSettings']['host'], 'smtp') !== false) {
			$SubStr = 'smtp';
		}
		$Domain = substr($_SESSION['SMTPSettings']['host'], strpos($_SESSION['SMTPSettings']['host'], $SubStr) + 5);
		$SendFrom = $_SESSION['SMTPSettings']['username'] . '@' . $Domain;
	}
	$Mail->setFrom($SendFrom);
	$Result = $Mail->send($To, 'smtp');
	return $Result;
}

function GetMailList($Recipients) {
	$ToList = array();
	$SQL = "SELECT email,realname FROM mailgroupdetails INNER JOIN www_users ON www_users.userid=mailgroupdetails.userid WHERE mailgroupdetails.groupname='" . $Recipients . "'";
	$ErrMsg = _('Failed to retrieve mail lists');
	$Result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($Result) != 0) {

		//Create the string which meets the Recipients requirements
		while ($MyRow = DB_fetch_array($Result)) {
			$ToList[] = $MyRow['realname'] . '<' . $MyRow['email'] . '>';
		}

	}
	return $ToList;
}

function ChangeFieldInTable($TableName, $FieldName, $OldValue, $NewValue) {
	/* Used in Z_ scripts to change one field across the table.
	 */
	echo '<br />' . _('Changing') . ' ' . $TableName . ' ' . _('records');
	$SQL = "UPDATE " . $TableName . " SET " . $FieldName . " ='" . $NewValue . "' WHERE " . $FieldName . "='" . $OldValue . "'";
	$DbgMsg = _('The SQL statement that failed was');
	$ErrMsg = _('The SQL to update' . ' ' . $TableName . ' ' . _('records failed'));
	$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
	echo ' ... ' . _('completed');
}

function GetChartLanguage() {

/* Need to pick the language that the account sections will
 * be shown in
 */

	$Language = '';

	$SQL = "SELECT language, COUNT(sectionid) AS total FROM accountsection GROUP BY language";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		$SectionLanguages[$MyRow['language']] = $MyRow['total'];
	}

	/* If the users locale exists then look no further */
	if (isset($SectionLanguages[$_SESSION['Language']])) {
		$Language = $_SESSION['Language'];
	}

	/* If the language exists but not the locale then use that */
	if ($Language == '') {
		foreach ($SectionLanguages as $Lang => $Count) {
			if (substr($Lang, 0, 2) == substr($_SESSION['Language'], 0, 2)) {
				$Language = $Lang;
			}
		}
	}

	/* Finally just pick a language */
	if ($Language == '') {
		$Language = $Lang;
	}
	return $Language;
}

function GetInventoryLanguage() {

/* Need to pick the language that the account sections will
 * be shown in
 */

	$Language = '';
	$InventoryLanguages = array();
	$Lang = $_SESSION['DefaultLanguage'];

	$SQL = "SELECT language_id, COUNT(stockid) AS total FROM stockdescriptiontranslations GROUP BY language_id";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		$InventoryLanguages[$MyRow['language_id']] = $MyRow['total'];
	}

	/* If the users locale exists then look no further */
	if (isset($InventoryLanguages[$_SESSION['Language']])) {
		$Language = $_SESSION['Language'];
	}

	/* If the language exists but not the locale then use that */
	if ($Language == '' and count($InventoryLanguages) > 0) {
		foreach ($InventoryLanguages as $Lang => $Count) {
			if (substr($Lang, 0, 2) == substr($_SESSION['Language'], 0, 2)) {
				$Language = $Lang;
			}
		}
	}

	/* Finally just pick a language */
	if ($Language == '') {
		$Language = $Lang;
	}
	return $Language;
}
?>