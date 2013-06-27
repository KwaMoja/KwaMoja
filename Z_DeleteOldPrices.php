<?php

include('includes/session.inc');
$Title = _('UTILITY PAGE To Delete All Old Prices');
include('includes/header.inc');

if (isset($_GET['DeleteOldPrices'])) {
	$result = DB_query("DELETE FROM prices WHERE enddate<'" . Date('Y-m-d') . "' AND enddate <>'0000-00-00'", $db);
	prnMsg(_('All old prices have been deleted'), 'success');
}

echo '<form onSubmit="return VerifyForm(this);" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint">';
echo '<div class="centre">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<br />
	<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?DeleteOldPrices=Yes" onclick="return makeConfirm(\'' . _('Are You Sure you wish to delete all old prices?') . '\', \'Confirm Delete\', this);">' . _('Purge Old Prices') . '</a>';

echo '</div>
	  </form>';

include('includes/footer.inc');
?>