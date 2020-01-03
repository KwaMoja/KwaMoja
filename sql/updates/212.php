<?php
RemoveMenuItem('system', 'Maintenance', 'Report a problem with KwaMoja', '/ReportBug.php');

NewMenuItem('system', 'Maintenance', 'Report a problem with KwaMoja', 'https://github.com/KwaMoja/KwaMoja/issues', '9');

UpdateDBNo(basename(__FILE__, '.php'));

?>