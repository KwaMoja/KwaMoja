<?php

echo '<img src="waiting.gif" id="loader" />';

$_SESSION['Installer']['CurrentPage']++;

echo '<form id="installation" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div class="page_help_text">
		<ul>
			<li>' . _('You are about to install the KwaMoja system.') . '</li>
			<li>' . _('This will take some time, please be patient.') . '</li>
			<li>' . _('To proceed click on the install button below.') . '</li>
		</ul>
	</div>';

echo '<button type="submit" name="next" onclick="return start_install();">' . _('Install') . '<img src="right.png" style="float:right" /></button>';
echo '<button type="submit" name="cancel">' . _('Restart') . '<img src="cross.png" style="float:right" /></button>';

echo '</form>';

echo '<h1 id="waittext">', _('KwaMoja is now being installed'), '<br />', _('Please wait..............'), '</h1>';
exit;
header('Location: index.php');

?>