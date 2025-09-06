<?php 
    include("constants/constants.php");
    session_destroy();
    header("Location:".SITEURL."login.php");
?>
