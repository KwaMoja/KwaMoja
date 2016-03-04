<?php

NewScript('AnalysisHorizontalPosition.php', '8');
NewMenuItem('GL', 'Reports', _('Horizontal analysis of statement of financial position'), '/AnalysisHorizontalPosition.php', 2);

UpdateDBNo(basename(__FILE__, '.php'));

?>