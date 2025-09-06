<?php
    include("constants/constants.php");
    session_start();

    if (isset($_GET['id'])) {
        $stream_id = intval($_GET['id']);

        // First, delete related teacher_assignments
        $stmt1 = mysqli_prepare($conn, "DELETE FROM teacher_assignments WHERE stream_id = ?");
        if ($stmt1) {
            mysqli_stmt_bind_param($stmt1, "i", $stream_id);
            mysqli_stmt_execute($stmt1);
            mysqli_stmt_close($stmt1);
        }

        // Now, delete the stream
        $stmt2 = mysqli_prepare($conn, "DELETE FROM streams WHERE id = ?");
        if ($stmt2) {
            mysqli_stmt_bind_param($stmt2, "i", $stream_id);

            if (mysqli_stmt_execute($stmt2)) {
                $_SESSION['delete_stream'] = "Stream deleted successfully";
            } else {
                $_SESSION['delete_stream'] = "Failed to delete stream: " . mysqli_error($conn);
            }

            mysqli_stmt_close($stmt2);
        } else {
            $_SESSION['delete_stream'] = "Failed to prepare statement: " . mysqli_error($conn);
        }
        header("Location:" . SITEURL . "streams.php");
        exit();
    } else {
        header("Location:" . SITEURL . "streams.php");
        exit();
    }
?>