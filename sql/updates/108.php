<?php

NewScript('PrintWOItemSlip.php', 4);
NewScript('WOCanBeProducedNow.php', 4);

NewMenuItem('manuf', 'Reports', 'WO Items ready to produce', '/WOCanBeProducedNow.php', 15);

UpdateDBNo(basename(__FILE__, '.php'));

?>