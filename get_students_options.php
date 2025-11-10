<?php
// Returns <option> elements for students in a class (used by reports.php JS)
include __DIR__ . '/constants/constants.php';

$class_id = intval($_GET['class_id'] ?? 0);
if (!$class_id) {
    echo '<option value="">Choose student</option>';
    exit;
}

$sql = "SELECT student_id, first_name, last_name FROM students WHERE class_id = $class_id ORDER BY first_name, last_name";
$res = mysqli_query($conn, $sql);
echo '<option value="">Choose student</option>';
if ($res) {
    while($r = mysqli_fetch_assoc($res)){
        $id = intval($r['student_id']);
        $name = htmlspecialchars($r['first_name'].' '.$r['last_name']);
        echo "<option value=\"$id\">$name</option>";
    }
}

?>
