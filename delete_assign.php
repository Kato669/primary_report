<?php 
    include("constants/constants.php");
    if(isset($_GET['id'])){
        $id = $_GET['id'];
        $delete = "DELETE FROM teacher_assignments WHERE id=$id";
        $execute = mysqli_query($conn, $delete);
        if($execute){
            $_SESSION['delete_assign'] = "deleted successfully";
            header("Location:".SITEURL."assign_roles.php");
        }
    }else{
        header("Location:".SITEURL."assign_roles.php");
    }
?>