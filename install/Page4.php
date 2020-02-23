<?php
include ('../includes/CountriesArray.php');
echo '<form id="DatabaseConfig" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<fieldset>
			<legend>' . _('Company Settings') . '</legend>
			<div class="page_help_text">
			</div>
			<ul>
				<li>
					<label for="CompanyName">' . _("Company Name") . ': </label>
					<input type="text" name="CompanyName" required="required" value="' . $_SESSION['Installer']['Database'] . '" maxlength="50" size="50" />
					<span>' . _('The full name of the company that you want to be used throughout KwaMoja') . '</span>
				</li>
				<li>
				<label for="COA">' . _("Chart of Accounts") . ': </label>
				<select name="COA">';

$COAs = glob('coa/*.sql');

foreach ($COAs as $Value) {
	if ($Value == 'coa' . '/' . $_SESSION['Installer']['CoA']) {
		echo '<option value="' . $Value . '" selected="true">' . $CountriesArray[substr(basename($Value, '.sql'), 3, 2) ] . '</option>';
	} else {
		echo '<option value="' . $Value . '">' . $CountriesArray[substr(basename($Value, '.sql'), 3, 2) ] . '</option>';
	}
}
echo '</select>
			<span>' . _('Will be installed as starter Chart of Accounts. If installing the Demo data then this wont work and you will just get a standard set of accounts') . '</span>
		</li>';

echo '<li>
			<label for="TimeZone">' . _("Time Zone") . ': </label>
			<select name="TimeZone">';
include ('timezone.php');
echo '</select>
		</li>';

echo '<li>
			<label for="Logo">' . _('Company logo file') . ': </label>
			<input type="file" accept="image/jpg" name="LogoFile" title="' . _('A jpg file up to 10kb, and not greater than 170px x 80px') . '" />
			<span>' . _("jpg file to 10kb, and not greater than 170px x 80px") . '<br />' . _('If you do not select a file, the default KwaMoja logo will be used') . '</span>
		</li>
	</ul>
</fieldset>';

echo '<fieldset>
			<legend>' . _('Installation option') . '</legend>
				<ul>
					<li>
						<label for="InstallDemo">' . _('Install the demo data?') . '</label><input type="checkbox" name="Demo" value="Yes" />
						<span>' . _("KwaMojaDemo site and data will be installed") . '</span>
					</li>
				</ul>
		</fieldset>';
echo '<fieldset>
			<legend>' . _('Administrator account settings') . '</legend>
			<div class="page_help_text">
				<ul>
					<li>' . _('The default user name is') . ' ' . '<b><i>admin</i></b>' . ' ' . _('which you can change below.') . '</li>
					<li>' . _('The default password is') . ' ' . '<b><i>kwamoja</i></b>' . ' ' . _('which you can change below.') . '</li>
				</ul>
			</div>
			<ul>
				<li>
					<label for="adminaccount">' . _('KwaMoja Admin Account') . ': </label>
					<input type="text" name="adminaccount" value="admin" />
				</li>
				<li>
					<label for="Email">' . _('Email address') . ': </label>
					<input type="email" name="Email" value="' . $_SESSION['Installer']['Email'] . '" placeholder="admin@yoursite.com" />
					<span>' . _('For example: admin@yourcompany.com') . '</span>
				</li>
				<li>
					<label for="KwaMojaPassword">' . _('KwaMoja Password') . ': </label>
					<input type="password" name="KwaMojaPassword" required="required" value="kwamoja" />
				</li>
				<li>
					<label for="PasswordConfirm">' . _('Re-enter Password') . ': </label>
					<input type="password" name="PasswordConfirm" required="required" value="kwamoja" />
				</li>
			</ul>
		</fieldset>';

echo '<fieldset style="text-align:center">
		<button type="submit" name="previous">' . _('Previous Step') . '<img src="left.png" style="float:left" /></button>
		<button type="submit" name="next">' . _('Install') . '<img src="right.png" style="float:right" /></button><br />
		<button type="submit" name="cancel">' . _('Restart') . '<img src="cross.png" style="float:right" /></button>
	</fieldset>
</form>';

?>