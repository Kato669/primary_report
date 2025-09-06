<?php
    include('constants/constants.php'); // Make sure to include your config file for SITEURL

    if(isset($_GET['student_id']) && isset($_GET['image'])){
        $stdnt_id = intval($_GET['student_id']);
        $image_name = $_GET['image'];

        // Delete image file if exists
        if(!empty($image_name) && file_exists("img/stdent_image/$image_name")){
            unlink("img/stdent_image/$image_name");
        }

        // Delete student record from database
        $sql = "DELETE FROM students WHERE student_id = $stdnt_id";
        $res = mysqli_query($conn, $sql);

        if($res){
            $_SESSION['delete'] = "<div class='success'>Student deleted successfully.</div>";
        }else{
            $_SESSION['delete'] = "<div class='error'>Failed to delete student.</div>";
        }

        header("Location:".SITEURL."students.php");
        exit();
    }else{
        header("Location:".SITEURL."students.php");
        exit();
    }
?>