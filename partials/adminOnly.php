<?php
// include("partials/header.php"); // or your constants/session include

// Check if user is logged in and is an admin
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['must_login'] = "You must be an admin to access this page.";
    header("Location: ".SITEURL);
    exit;
}
?>
