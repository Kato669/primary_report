<?php
include("partials/header.php"); // make sure $conn is available

if(isset($_GET['class_id'])){
    $class_id = intval($_GET['class_id']);

    $query = "SELECT s.subject_id, s.subject_name
              FROM class_subjects cs
              JOIN subjects s ON cs.subject_id = s.subject_id
              WHERE cs.class_id = $class_id
              ORDER BY s.subject_name";

    $res = mysqli_query($conn, $query);

    if($res && mysqli_num_rows($res) > 0){
        while($row = mysqli_fetch_assoc($res)){
            echo "<option value='{$row['subject_id']}'>{$row['subject_name']}</option>";
        }
    } else {
        echo "<option disabled>No subjects assigned</option>";
    }
}
