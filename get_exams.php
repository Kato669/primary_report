<?php
include('constants/constants.php');

$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
$term_id  = isset($_GET['term_id'])  ? intval($_GET['term_id'])  : 0;

if ($class_id <= 0) {
    echo '<option value="">No Class Selected</option>';
    exit;
}

if ($term_id > 0) {
    $stmt = $conn->prepare("
        SELECT MIN(exam_id) AS exam_id, exam_name, term_id, academic_year
        FROM exams
        WHERE class_id = ? AND term_id = ?
        GROUP BY exam_name, term_id, academic_year
        ORDER BY academic_year DESC, exam_name ASC
    ");
    $stmt->bind_param("ii", $class_id, $term_id);
} else {
    $stmt = $conn->prepare("
        SELECT MIN(exam_id) AS exam_id, exam_name, term_id, academic_year
        FROM exams
        WHERE class_id = ?
        GROUP BY exam_name, term_id, academic_year
        ORDER BY academic_year DESC, term_id ASC, exam_name ASC
    ");
    $stmt->bind_param("i", $class_id);
}

$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo '<option value="">No exams found for this class</option>';
} else {
    echo '<option value="" disabled selected>Select Exam</option>';
    while ($row = $res->fetch_assoc()) {
        $term_name = '';
        $termQ = $conn->prepare("SELECT term_name FROM terms WHERE term_id = ? LIMIT 1");
        $termQ->bind_param("i", $row['term_id']);
        $termQ->execute();
        $termR = $termQ->get_result();
        if ($termR && $termR->num_rows > 0) {
            $term_name = strtoupper($termR->fetch_assoc()['term_name']);
        }
        $termQ->close();

        echo '<option value="' . $row['exam_id'] . '">'
            . htmlspecialchars(strtoupper($row['exam_name']))
            . ' (Term: ' . htmlspecialchars($term_name)
            . ' - ' . htmlspecialchars($row['academic_year']) . ')'
            . '</option>';
    }
}
$stmt->close();
?>
