<?php
echo '<div id="mask">
				<div id="dialog" name="dialog"></div>
			</div>';

if (isset($Messages) and count($Messages) > 0) {
	echo '<br />';
	foreach ($Messages as $Message) {
		$Prefix = '';
		switch ($Message[1]) {
			case 'error':
				$Class = 'error';
				$Prefix = $Prefix ? $Prefix : _('ERROR') . ' ' . _('Report');
				if (isset($_SESSION['LogSeverity']) and $_SESSION['LogSeverity'] > 3) {
					fwrite($LogFile, date('Y-m-d h-m-s') . ',' . $Type . ',' . $_SESSION['UserID'] . ',' . trim($Msg, ',') . "\n");
				}
				echo '<div class="' . $Class . ' noPrint"><b>' . $Prefix . '</b> : ' . $Message[0] . '</div>';
				break;
			case 'warn':
				$Class = 'warn';
				$Prefix = $Prefix ? $Prefix : _('WARNING') . ' ' . _('Report');
				if (isset($_SESSION['LogSeverity']) and $_SESSION['LogSeverity'] > 3) {
					fwrite($LogFile, date('Y-m-d h-m-s') . ',' . $Type . ',' . $_SESSION['UserID'] . ',' . trim($Msg, ',') . "\n");
				}
				echo '<br /><div class="' . $Class . ' noPrint"><b>' . $Prefix . '</b> : ' . $Message[0] . '</div>';
				break;
			case 'success':
				$Class = 'success';
				$Prefix = $Prefix ? $Prefix : _('SUCCESS') . ' ' . _('Report');
				if (isset($_SESSION['LogSeverity']) and $_SESSION['LogSeverity'] > 3) {
					fwrite($LogFile, date('Y-m-d h-m-s') . ',' . $Type . ',' . $_SESSION['UserID'] . ',' . trim($Msg, ',') . "\n");
				}
				echo '<div class="' . $Class . ' noPrint"><b>' . $Prefix . '</b> : ' . $Message[0] . '</div>';
				break;
			case 'info':
			default:
				$Prefix = $Prefix ? $Prefix : _('INFORMATION') . ' ' . _('Message');
				$Class = 'info';
				if (isset($_SESSION['LogSeverity']) and $_SESSION['LogSeverity'] > 2) {
					fwrite($LogFile, date('Y-m-d h-m-s') . ',' . $Type . ',' . $_SESSION['UserID'] . ',' . trim($Msg, ',') . "\n");
				}
				echo '<div class="' . $Class . ' noPrint"><b>' . $Prefix . '</b> : ' . $Message[0] . '</div>';
		}
	}
}

echo '</div>'; // BodyWrapDiv

echo '</div>'; // BodyDiv

echo '<div id="FooterDiv" class="noPrint">';
echo '<div id="FooterWrapDiv">';

echo '<div id="FooterLogoDiv">
		<a href="http://www.kwamoja.com" target="_blank"><img src="', $RootPath, '/', $_SESSION['LogoFile'], '" width="120" alt="KwaMoja" title="KwaMoja" /></a>
	</div>';

echo '<div id="FooterVersionDiv">
		KwaMoja ', _('version'), ' ', $_SESSION['VersionNumber'], '.', $_SESSION['DBUpdateNumber'],
	'</div>';

echo '<div id="FooterTimeDiv">';
echo DisplayDateTime();
echo '</div>';

echo '</div>'; // FooterWrapDiv
echo '</div>'; // FooterDiv
echo '</div>'; // Canvas

echo '</body>';
echo '</html>';

?>