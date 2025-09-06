<?php
    include("./constants/constants.php");
    if(isset($_GET['id'])){
        $class_id = intval($_GET['id']);
        $deleteData = "DELETE FROM classes where id=$class_id";
        $execute_data = mysqli_query($conn, $deleteData);
        if($execute_data){
            $_SESSION['delete_class'] = "data deleted successfully";
            header("Location:".SITEURL."class.php");
            exit();
        }else{
            $_SESSION['delete_class'] = "<div class='text-danger'>Failed to delete class</div>";
            header("Location:".SITEURL."class.php");
        }
    }else{
        header("Location:".SITEURL."class.php");
    }
?>