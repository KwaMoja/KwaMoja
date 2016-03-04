<?php

NewScript('UserLocations.php', '15');
NewMenuItem('system', 'Maintenance', _('User Authorised Inventory Locations Maintenance'), '/UserLocations.php', 14);

UpdateDBNo(basename(__FILE__, '.php'));

?>