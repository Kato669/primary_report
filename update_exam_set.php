<?php
include("constants/constants.php");

if(isset($_POST['class_id'], $_POST['exam_type'], $_POST['status'])){
    $class_id = (int)$_POST['class_id'];
    $exam_type = mysqli_real_escape_string($conn, strtolower($_POST['exam_type']));
    $status = $_POST['status'] === 'true' ? 1 : 0;

    $sql = "
        INSERT INTO exam_visibility (class_id, exam_type, visible)
        VALUES ($class_id, '$exam_type', $status)
        ON DUPLICATE KEY UPDATE visible = $status;
    ";
    mysqli_query($conn, $sql);
    echo "ok";
}
?>
