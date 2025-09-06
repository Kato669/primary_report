<?php 
    include("constants/constants.php");
    if(isset($_GET['subject_id'])){
        $subject_id = intval($_GET['subject_id']);
        $delete = "DELETE FROM subjects WHERE subject_id = $subject_id";
        $res = mysqli_query($conn, $delete);
        if($res){
            $_SESSION['delete_subject'] = "subject deleted";
            header("Location:".SITEURL.'subject.php');
        }
    }else{
        header("Location:".SITEURL."delete_subject.php");
    }
?>