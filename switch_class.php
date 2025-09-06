<?php
session_start();

// Check if index is provided and valid
if (isset($_GET['index']) && isset($_SESSION['assignments'][$_GET['index']])) {
    $selected = $_SESSION['assignments'][$_GET['index']];
    $_SESSION['class_id'] = $selected['class_id'];
    $_SESSION['stream_id'] = $selected['stream_id'];
    $_SESSION['current_assignment_index'] = $_GET['index']; // optional for tracking
}

// Redirect back to previous page or dashboard
$redirectTo = $_SERVER['HTTP_REFERER'] ?? 'teacher_dashboard.php';
header("Location: $redirectTo");
exit;
