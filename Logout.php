<?php

$AllowAnyone=True; /* Allow all users to log off  */

include('includes/session.inc');
// Cleanup
unset($_SESSION);
session_unset();
session_destroy();
?>