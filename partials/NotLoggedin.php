<?php 
    // include("./constants/constants.php");
    if(!isset($_SESSION['login'])){
        $_SESSION["must_login"] = "Please login";
        header("Location:".SITEURL."login.php");
        exit;
    }
?>