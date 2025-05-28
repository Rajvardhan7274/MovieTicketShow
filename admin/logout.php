<?php
session_start();

// Clear admin session variables
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to admin login page
header("Location: login.php");
exit();
?> 