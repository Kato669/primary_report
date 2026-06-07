<?php
    session_start();
    define('LOCALHOST', 'localhost');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', 'Root@123');
    define('DB_NAME', 'primary_db');
    define("SITEURL","http://localhost/primary_report/");

    $conn = mysqli_connect(LOCALHOST, DB_USERNAME, DB_PASSWORD) or die(mysqli_error());
    $db_select = mysqli_select_db($conn, DB_NAME) or die(mysqli_error());

    $createExamVisibilityTable = "CREATE TABLE IF NOT EXISTS exam_visibility (
        class_id INT NOT NULL,
        exam_type VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
        visible TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (class_id, exam_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;";
    mysqli_query($conn, $createExamVisibilityTable);
?>
