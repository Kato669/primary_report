<?php 
session_start();
include("constants/constants.php");
include("db/connect.php");

// Check if user is logged in and is admin
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['must_login'] = "Please log in with admin privileges.";
    header("Location: login.php");
    exit;
}

if (isset($_GET['subject_id'])) {
    $subject_id = intval($_GET['subject_id']);

    // Check if subject is linked in class_subjects
    $check_class_subjects = mysqli_query($conn, "SELECT * FROM class_subjects WHERE subject_id = $subject_id LIMIT 1");
    
    // Check if subject is linked in marks
    $check_marks = mysqli_query($conn, "SELECT * FROM marks WHERE subject_id = $subject_id LIMIT 1");
    
    // Check if subject is linked in teacher assignments
    $check_teachers = mysqli_query($conn, "SELECT * FROM teacher_subject_assignments WHERE subject_id = $subject_id LIMIT 1");

    if (mysqli_num_rows($check_class_subjects) > 0) {
        $_SESSION['delete_subject'] = "⚠️ Cannot delete this subject — it is assigned to one or more classes.";
        header("Location:" . SITEURL . "subject.php");
        exit;
    }

    if (mysqli_num_rows($check_marks) > 0) {
        $_SESSION['delete_subject'] = "⚠️ Cannot delete this subject — it has exam marks recorded.";
        header("Location:" . SITEURL . "subject.php");
        exit;
    }

    if (mysqli_num_rows($check_teachers) > 0) {
        $_SESSION['delete_subject'] = "⚠️ Cannot delete this subject — it is assigned to teachers.";
        header("Location:" . SITEURL . "subject.php");
        exit;
    }

    // If not linked anywhere, safe to delete
    $delete = "DELETE FROM subjects WHERE subject_id = $subject_id";
    $res = mysqli_query($conn, $delete);

    if ($res) {
        $_SESSION['delete_subject'] = "✅ Subject deleted successfully.";
    } else {
        $_SESSION['delete_subject'] = "❌ Failed to delete subject: " . mysqli_error($conn);
    }

    header("Location:" . SITEURL . "subject.php");
    exit;

} else {
    header("Location:" . SITEURL . "subject.php");
    exit;
}
?>
