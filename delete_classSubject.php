<?php
    include("constants/constants.php");
    if(isset($_GET['class_id'])){
        $class_id = intval($_GET['class_id']);
        
        // Delete all subjects for the specified class
        $delete = $conn->prepare("DELETE FROM class_subjects WHERE class_id = ?");
        $delete->bind_param("i", $class_id);
        $res = $delete->execute();
        
        if($res){
            $_SESSION['subject_deleted'] = "All subjects for the class were deleted successfully";
            header("Location:".SITEURL."class_subjects.php");
            exit;
        }
    }
    
    header("Location:".SITEURL."class_subjects.php");
    exit;
?>