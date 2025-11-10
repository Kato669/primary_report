<?php
    include("./constants/constants.php");

    if (isset($_GET['id'])) {
        $class_id = intval($_GET['id']);

        // Step 1: Delete related records from dependent tables
        mysqli_query($conn, "DELETE FROM students WHERE class_id=$class_id");
        mysqli_query($conn, "DELETE FROM class_subjects WHERE class_id=$class_id");
        mysqli_query($conn, "DELETE FROM exams WHERE class_id=$class_id");
        mysqli_query($conn, "DELETE FROM streams WHERE class_id=$class_id");
        mysqli_query($conn, "DELETE FROM teacher_assignments WHERE class_id=$class_id");
        mysqli_query($conn, "DELETE FROM teacher_subject_assignments WHERE class_id=$class_id");
        mysqli_query($conn, "DELETE FROM term_info WHERE class_id=$class_id");

        // Step 2: Delete the class itself
        $deleteData = "DELETE FROM classes WHERE id=$class_id";
        $execute_data = mysqli_query($conn, $deleteData);

        // Step 3: Handle success or failure
        if ($execute_data) {
            $_SESSION['delete_class'] = "Class deleted successfully";
        } else {
            $_SESSION['delete_class'] = "<div class='text-danger'>Failed to delete class</div>";
        }

        header("Location:" . SITEURL . "class.php");
        exit();
    } else {
        header("Location:" . SITEURL . "class.php");
    }
?>