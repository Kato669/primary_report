<?php
    include('constants/constants.php'); // Make sure to include your config file for SITEURL

    if(isset($_GET['student_id']) && isset($_GET['image'])){
        $stdnt_id = intval($_GET['student_id']);
        $image_name = $_GET['image'];

        // Start transaction
        mysqli_begin_transaction($conn);

        try {
            // Delete related records from marks table first
            $sql_marks = "DELETE FROM marks WHERE student_id = $stdnt_id";
            mysqli_query($conn, $sql_marks);

            // Delete related records from other tables if they exist
            // Add more DELETE queries here for other tables that reference student_id

            // Delete image file if exists
            if(!empty($image_name) && file_exists("img/stdent_image/$image_name")){
                unlink("img/stdent_image/$image_name");
            }

            // Finally, delete the student record
            $sql = "DELETE FROM students WHERE student_id = $stdnt_id";
            $res = mysqli_query($conn, $sql);

            if($res){
                // Commit transaction
                mysqli_commit($conn);
                $_SESSION['delete'] = "<div class='success'>Student deleted successfully.</div>";
            }else{
                throw new Exception("Failed to delete student");
            }
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            $_SESSION['delete'] = "<div class='error'>Failed to delete student. Error: " . $e->getMessage() . "</div>";
        }

        header("Location:".SITEURL."students.php");
        exit();
    }else{
        header("Location:".SITEURL."students.php");
        exit();
    }
?>