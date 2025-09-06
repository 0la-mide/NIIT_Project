<?php
// Start session
session_start();

// Check if admin is already logged in
if (isset($_SESSION['admin_logged_in'])) {
    // If logged in, redirect to dashboard
    header('Location: admin/dashboard.php');
    exit;
} else {
    // If not logged in, redirect to login page
    header('Location: login.php');
    exit;
}
?>
