<?php
    //start the session
    session_start();
    //declare global
    define("LOCALHOST",'localhost');
    define('DBNAME', 'report');
    define("DBUSERNAME","root");
    define("DBPASSWORD","");
    define("SITEURL","http://localhost/report/");
    

    $conn = mysqli_connect(LOCALHOST, DBUSERNAME, DBPASSWORD, DBNAME);
    if(!$conn){
        die("Failed connection". mysqli_connect_error());
    }
?>