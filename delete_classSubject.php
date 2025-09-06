<?php
    include("constants/constants.php");
    if(isset($_GET['id'])){
        $subject_id = $_GET['id'];
        $delete = "DELETE FROM class_subjects WHERE id=$subject_id";
        $res = mysqli_query($conn, $delete);
        if($res){
            $_SESSION['subject_deleted'] = "deleted successfully";
            header("Location:".SITEURL."class_subjects.php");
            exit;
        }
    }else{
        header("Location:".SITEURL."class_subjects.php");
        exit;
    }
?>