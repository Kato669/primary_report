<?php 
    session_start();
    include("constants/constants.php");
    if(isset($_GET['exam_id'])){
        $exam_id = intval($_GET['exam_id']);
        $sql = "DELETE FROM exams WHERE exam_id=$exam_id";
        $res = mysqli_query($conn, $sql);
        if($res){
            $_SESSION['delete_exam'] = "Exam deleted successfully";
            header("Location:".SITEURL."examination.php");
            exit();
        }else{
            die("Failed to execute". mysqli_error($conn));
        }
    }else{
        header("Location:".SITEURL."examination.php");
        exit();
    }
?>