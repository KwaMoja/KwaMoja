<?php
/* $Revision: 1.7 $ */

/*Code to check that ShiptRef and Contract or JobRef entered are valid entries
This is used by the UpdateLine button when a purchase order line item is updated and
by the EnterLine button when a new purchase order line item is entered
*/

if (($_POST['ShiptRef'] != "" and $_POST['ShiptRef'] != 0) or !isset($_POST['ShiptRef'])) {
	/*Dont bother if no shipt ref selected */

	/*Check for existance of Shipment Selected */
	$sql = "SELECT COUNT(*) FROM shipments WHERE shiptref ='" . $_POST['ShiptRef'] . "' AND closed =0";
	$ShiptResult = DB_query($sql, $db, '', '', false, false);
	if (DB_error_no != 0 or DB_num_rows($ShiptResult) == 0) {
		$AllowUpdate = False;
		prnMsg(_('The update could not be processed') . '<br />' . _('There was some snag in retrieving the shipment reference entered') . ' - ' . _('see the listing of open shipments to ensure a valid shipment reference is entered'), 'error');
	} else {
		$ShiptRow = DB_fetch_row($ShiptResult);
		if ($ShiptRow[0] != 1) {
			$AllowUpdate = False;
			prnMsg(_('The update could not be processed') . '<br />' . _('The shipment entered is either closed or not set up in the database') . '. ' . _('Please refer to the list of open shipments from the link to ensure a valid shipment reference is entered'), 'error');
		}
	}
}

?>