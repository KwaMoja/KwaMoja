<?php

AddColumn('comment', 'bom', 'TEXT', 'NOT NULL', NULL, 'autoissue');

UpdateDBNo(basename(__FILE__, '.php'));

?>