<?php

AddColumn('comment', 'bom', 'TEXT', 'NOT NULL', "", 'autoissue');

UpdateDBNo(basename(__FILE__, '.php'));

?>