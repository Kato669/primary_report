<?php
include('constants/constants.php');

$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;

if ($class_id <= 0) {
    echo '<option value="">No Class Selected</option>';
    exit;
}

// ✅ Fetch exams for this class, grouped by exam_name + term + academic_year
$stmt = $conn->prepare("
    SELECT 
        MIN(exam_id) AS exam_id, 
        exam_name, 
        term_id, 
        academic_year
    FROM exams
    WHERE class_id = ?
    GROUP BY exam_name, term_id, academic_year
    ORDER BY academic_year DESC, term_id ASC, exam_name ASC
");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo '<option value="">No exams found for this class</option>';
} else {
    echo '<option value="" disabled selected>Select Exam</option>';
    while ($row = $res->fetch_assoc()) {
        // Fetch term name for readability
        $term_name = '';
        $termQ = mysqli_query($conn, "SELECT term_name FROM terms WHERE term_id = {$row['term_id']} LIMIT 1");
        if ($termQ && mysqli_num_rows($termQ) > 0) {
            $termRow = mysqli_fetch_assoc($termQ);
            $term_name = strtoupper($termRow['term_name']);
        }

        echo '<option value="'.$row['exam_id'].'">'
                . htmlspecialchars(strtoupper($row['exam_name'])) 
                . " (Term: " . htmlspecialchars($term_name) 
                . " - " . htmlspecialchars($row['academic_year']) . ")"
             . '</option>';
    }
}
$stmt->close();
?>
