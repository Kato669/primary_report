<?php
ob_start();
include("partials/header.php");

// ---------------- Role & Login Check ----------------
if (!isset($_SESSION['logged_in']) || !in_array($_SESSION['role'], ['admin', 'class_teacher'])) {
    $_SESSION['must_login'] = "Please log in first.";
    header("Location: login.php");
    exit;
}

// ---------------- Get Session Info ----------------
$role = $_SESSION['role'];
$class_id = $_SESSION['class_id'] ?? null;
$stream_id = $_SESSION['stream_id'] ?? null;

// ---------------- Fetch Students and Marks ----------------
$filter = '';
$params = [];

if ($role === 'class_teacher' && $class_id && $stream_id) {
    $filter = "WHERE st.class_id = ? AND st.stream_id = ?";
    $params = [$class_id, $stream_id];
}

// Prepare SQL
$sql = "
    SELECT 
        st.student_id,
        st.first_name,
        st.last_name,
        e.exam_name,
        t.term_name,
        s.subject_name,
        m.score,
        m.mark_id
    FROM students st
    LEFT JOIN marks m ON st.student_id = m.student_id
    LEFT JOIN exams e ON m.exam_id = e.exam_id
    LEFT JOIN terms t ON e.term_id = t.term_id
    LEFT JOIN subjects s ON m.subject_id = s.subject_id
    $filter
    ORDER BY st.first_name, st.last_name, e.exam_name, s.subject_name
";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param(str_repeat('i', count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}

// Organize results by student
$students = [];
while ($row = $result->fetch_assoc()) {
    $student_id = $row['student_id'];
    if (!isset($students[$student_id])) {
        $students[$student_id] = [
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'marks' => []
        ];
    }

    if ($row['mark_id']) {
        $students[$student_id]['marks'][] = [
            'exam_name' => $row['exam_name'],
            'term_name' => $row['term_name'],
            'subject_name' => $row['subject_name'],
            'score' => $row['score']
        ];
    }
}
$stmt->close();
?>

<div class="container-fluid">
    <div class="row g-0 my-2">
        <div class="col-lg-4 col-md-4 col-sm-12">
            <a href="<?php echo SITEURL ?>select_exam.php" class="btn text-capitalize text-white btn-success fs-6">
                Add Score
                <i class="fa-solid fa-pen-to-square"></i>
            </a>
        </div>
    </div>

    <h3 class="text-capitalize fs-6 text-dark py-2">View Scores</h3>
    <table id="example" class="display table table-bordered table-striped">
        <thead>
            <tr>
                <th>Sn</th>
                <th>Student Name</th>
                <th>Exam</th>
                <th>Term</th>
                <th>Subject</th>
                <th>Score</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $sn = 1;
            foreach ($students as $student) : 
                if (!empty($student['marks'])) :
                    foreach ($student['marks'] as $mark) :
            ?>
            <tr>
                <td><?php echo $sn++; ?></td>
                <td><?php echo htmlspecialchars($student['name']); ?></td>
                <td><?php echo htmlspecialchars(strtoupper($mark['exam_name'])); ?></td>
                <td><?php echo htmlspecialchars($mark['term_name']); ?></td>
                <td><?php echo htmlspecialchars($mark['subject_name']); ?></td>
                <td><?php echo htmlspecialchars($mark['score']); ?></td>
            </tr>
            <?php 
                    endforeach; 
                else: // student exists but has no marks
            ?>
            <tr>
                <td><?php echo $sn++; ?></td>
                <td><?php echo htmlspecialchars($student['name']); ?></td>
                <td>No marks</td>
                <td>No marks</td>
                <td>No marks</td>
                <td>No marks</td>
            </tr>
            <?php endif; endforeach; ?>
        </tbody>
    </table>
</div>

<?php include("partials/footer.php"); ?>
