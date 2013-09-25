<?php
echo '<form id="DatabaseConfig" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
if (!empty($Errors)) {
	foreach ($Errors as $Error) {
		echo '<div class="error">' . $Error . '</div>';
	}
}

echo '<fieldset>
			<legend>' . _('Database settings') . '</legend>
			<div class="page_help_text">
				<p>' . _('Please enter your Database information below. The database name is also used at log in time to choose the company for use.') . '<br />
				</p>
			</div>
			<ul>
				<li>
					<label for="HostName">' . _('Host Name') . ': </label>
					<input type="text" name="HostName" id="HostName" required="required" value="' . $_SESSION['Installer']['HostName'] . '" placeholder="' . _('Enter database host name') . '" />
					<span>' . _('Commonly: localhost or 127.0.0.1') . '</span>
				</li>
				<li>
					<label for="Database">' . _('Database Name') . ': </label>
					<input type="text" name="Database" id="Database" required="required" value="' . $_SESSION['Installer']['Database'] . '" maxlength="16" placeholder="' . _('The database name') . '" />
					<span>' . _('If your user name below does not have permissions to create a database then this database must be created and empty.') . '</span>
				</li>
				<li>
					<label for="Prefix">' . _('Database Prefix') . ' - ' . _('Optional') . ': </label>
					<input type="text" name="Prefix" size="25" placeholder="' . _('Useful with shared hosting') . '" pattern="^[A-Za-z0-9$]+_$" />&#160;
					<span>' . _('Optional: in the form of prefix_') . '</span>
				</li>
				<li>
					<label for="UserName">' . _('Database User Name') . ':</label>
					<input type="text" name="UserName" id="UserName" value="' . $_SESSION['Installer']['UserName'] . '" placeholder="' . _('A valid database user name') . '" maxlength="16" required="required" />&#160;
					<span>' . _('If this user does not have permission to create databases, then the database entered above must exist and be empty.') . '</span>
				</li>
				<li>
					<label for="Password">' . _('Password') . ': </label>
					<input type="password" name="Password" placeholder="' . _('Database user password') . '"  />
					<span>' . _('Enter the database user password if one exists') . '</span>
				</li>
			</ul>
		</fieldset>';

echo '<input type="hidden" name="UserLanguage" value="' . $Language . '" />';
echo '<input type="hidden" name="Language" value="' . $Language . '" />';
echo '<input type="hidden" name="DBExt" value="' . $_SESSION['Installer']['DBExt'] . '" />';

echo '<input type="hidden" name="required" value="' . $_SESSION['Installer']['DBMS'] . '" />';

echo '<fieldset style="text-align:center">
		<button type="submit" name="previous">' . _('Previous Step') . '<img src="left.png" style="float:left" /></button>
		<button type="submit" name="next">' . _('Next Step') . '<img src="right.png" style="float:right" /></button><br />
		<button type="submit" name="cancel">' . _('Restart') . '<img src="cross.png" style="float:right" /></button>
	</fieldset>
</form>';

?>