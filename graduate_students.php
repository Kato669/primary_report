<?php
    include("partials/header.php");
    include("partials/adminOnly.php");
    $last_class_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM classes ORDER BY id DESC LIMIT 1"));
    $last_class_id = $last_class_row['id'];
    mysqli_query($conn, "UPDATE students SET level = 'graduated' WHERE class_id = $last_class_id AND level = 'active'");
    echo "All P7 students marked as graduated.";
    include("partials/footer.php");
?>