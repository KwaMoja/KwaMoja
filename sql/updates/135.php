<?php

NewScript('AnalysisHorizontalIncome.php', '8');
NewMenuItem('GL', 'Reports', _('Horizontal Analysis of Statement of Comprehensive Income'), '/AnalysisHorizontalIncome.php', 3);

UpdateDBNo(basename(__FILE__, '.php'));

?>