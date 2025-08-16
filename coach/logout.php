<?php
session_start();

// Clear all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to the unified index.php located in the sms directory
header("Location: ../index.php");
exit();
?>
