<?php

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<table cellpadding="3" cellspacing="0" align="center" width="75%">
		<tr>
			<th colspan="3">' . _('Select a chart of accounts') . '</th>
		</tr>';
echo '</table>';
echo '<tr>
			<td class="button_bar"><button id="navigate" name="submit" value="3">&lt;&lt;&nbsp;&nbsp;' . _('Go Back') . '</button>
			<button id="navigate" name="submit" value="5">' . _('Continue') . '&nbsp;&nbsp;&gt;&gt;</button></td>
		</tr>
	</form>';
?>