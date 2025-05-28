<?php
session_start();

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin_logged_in() {
    return isset($_SESSION['admin_id']);
}

// Get current page and directory
$current_page = basename($_SERVER['PHP_SELF']);
$is_admin_page = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;

// List of pages that don't require login
$public_pages = ['login.php', 'register.php', 'index.php'];

// Handle admin pages
if ($is_admin_page) {
    // If not logged in as admin and not on admin login page, redirect to admin login
    if (!is_admin_logged_in() && $current_page !== 'login.php') {
        header("Location: login.php");
        exit();
    }
    // If logged in as admin and on admin login page, redirect to dashboard
    if (is_admin_logged_in() && $current_page === 'login.php') {
        header("Location: dashboard.php");
        exit();
    }
}
// Handle user pages
else {
    // If not logged in and not on a public page, redirect to login
    if (!is_logged_in() && !in_array($current_page, $public_pages)) {
        header("Location: login.php");
        exit();
    }
    // If logged in and on login/register page, redirect to home
    if (is_logged_in() && in_array($current_page, ['login.php', 'register.php'])) {
        header("Location: index.php");
        exit();
    }
}
?>
