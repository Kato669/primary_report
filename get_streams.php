<?php
include("constants/constants.php"); // or your DB connection file

if (!isset($_GET['class_id'])) {
    echo '<option value="">No class selected</option>';
    exit;
}

$class_id = intval($_GET['class_id']);
if ($class_id <= 0) {
    echo '<option value="">Invalid class</option>';
    exit;
}

// Fetch streams for this class
$query = "SELECT id, stream_name FROM streams WHERE class_id = $class_id ORDER BY stream_name";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo '<option value="">No streams available</option>';
    exit;
}

echo '<option value="" selected disabled>Select Stream</option>';
while ($row = mysqli_fetch_assoc($result)) {
    echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['stream_name']) . '</option>';
}
?>
