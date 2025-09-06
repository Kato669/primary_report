<?php
include("constants/constants.php"); // or your db connection file

if(isset($_GET['class_id'])){
    $class_id = intval($_GET['class_id']);

    $query = "SELECT id, stream_name FROM streams WHERE class_id = $class_id ORDER BY stream_name";
    $res = mysqli_query($conn, $query);

    if($res && mysqli_num_rows($res) > 0){
        while($row = mysqli_fetch_assoc($res)){
            echo "<option value='{$row['id']}'>{$row['stream_name']}</option>";
        }
    } else {
        echo "<option disabled>No streams found</option>";
    }
}
