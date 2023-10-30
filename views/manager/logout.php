<?php
session_start();
session_destroy();
// Redirect to the login page:
header("location: /index.php");
date_default_timezone_set('GMT');
?>