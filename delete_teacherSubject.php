<?php 
    include("constants/constants.php");
    if(isset($_GET["id"])){
        $assigned_id = intval($_GET['id']);
        // echo $assigned_id;
        $delete = "DELETE FROM teacher_subject_assignments WHERE id=$assigned_id";
        $res = mysqli_query($conn, $delete);
        if($res){
            $_SESSION['teacher_subject_deleted'] = "Deleted successfully";
            header("Location:".SITEURL."teacherSubject.php");
        }else{
            die("Failed to delete". mysqli_error($conn));
        }
    }else{
        header("Location:".SITEURL."teacherSubject.php");
    }
?>