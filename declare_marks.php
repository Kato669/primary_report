<?php
ob_start();
include("partials/header.php");

// ---------------- Role & Login Check ----------------
if (!isset($_SESSION['logged_in']) || !in_array($_SESSION['role'], ['admin', 'class_teacher', 'teacher'])) {
    $_SESSION['must_login'] = "Please log in first.";
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$class_id = $_SESSION['class_id'] ?? null;
$stream_id = $_SESSION['stream_id'] ?? null;

// ---------------- Get Assigned Subjects for Teacher ----------------
$allowed_subjects = [];

if ($role === 'teacher') {
    $asgn_query = "
        SELECT subject_id 
        FROM teacher_subject_assignments 
        WHERE teacher_id = ? 
          AND academic_year = (SELECT MAX(academic_year) FROM teacher_subject_assignments WHERE teacher_id = ?)
    ";
    $stmt = $conn->prepare($asgn_query);
    $stmt->bind_param('ii', $user_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $allowed_subjects[] = $row['subject_id'];
    }
    $stmt->close();
}

// ---------------- Build Filter ----------------
$where_clauses = ["st.level = 'active'"];
$params = [];
$types = '';

if ($role === 'class_teacher' && $class_id && $stream_id) {
    $where_clauses[] = "st.class_id = ?";
    $where_clauses[] = "st.stream_id = ?";
    $params = [$class_id, $stream_id];
    $types = 'ii';
}

// ---------------- Fetch Students and Marks ----------------
$where_sql = $where_clauses ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

$sql = "
    SELECT 
        st.student_id,
        st.first_name,
        st.last_name,
        e.exam_name,
        t.term_name,
        s.subject_id,
        s.subject_name,
        m.score,
        m.mark_id
    FROM students st
    LEFT JOIN marks m ON st.student_id = m.student_id
    LEFT JOIN exams e ON m.exam_id = e.exam_id
    LEFT JOIN terms t ON e.term_id = t.term_id
    LEFT JOIN subjects s ON m.subject_id = s.subject_id
    $where_sql
    ORDER BY st.first_name, st.last_name, e.exam_name, s.subject_name
";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// ---------------- Organize Results by Student ----------------
$students = [];
while ($row = $result->fetch_assoc()) {
    $sid = $row['student_id'];
    if (!isset($students[$sid])) {
        $students[$sid] = [
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'marks' => []
        ];
    }

    if ($row['mark_id']) {
        $students[$sid]['marks'][] = [
            'exam_name' => $row['exam_name'],
            'term_name' => $row['term_name'],
            'subject_name' => $row['subject_name'],
            'subject_id' => $row['subject_id'],
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

    <h3 class="text-capitalize fs-6 text-dark py-2">View & Edit Scores</h3>
    <form method="POST" action="save_scores.php">
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
                foreach ($students as $student_id => $student) : 
                    if (!empty($student['marks'])) :
                        foreach ($student['marks'] as $mark) :
                            // Determine edit permission
                            $can_edit = (
                                $role === 'admin' || 
                                $role === 'class_teacher' || 
                                ($role === 'teacher' && in_array($mark['subject_id'], $allowed_subjects))
                            );
                            $disabled = $can_edit ? '' : 'disabled';
                ?>
                <tr>
                    <td><?php echo $sn++; ?></td>
                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                    <td><?php echo htmlspecialchars(strtoupper($mark['exam_name'])); ?></td>
                    <td><?php echo htmlspecialchars($mark['term_name']); ?></td>
                    <td><?php echo htmlspecialchars($mark['subject_name']); ?></td>
                    <td>
                        <input 
                            type="number" 
                            name="scores[<?php echo $mark['subject_id'] . '_' . $student_id; ?>]" 
                            value="<?php echo htmlspecialchars($mark['score']); ?>" 
                            class="form-control form-control-sm"
                            min="0" max="100" 
                            <?php echo $disabled; ?>>
                    </td>
                </tr>
                <?php 
                        endforeach; 
                    else: 
                ?>
                <tr>
                    <td><?php echo $sn++; ?></td>
                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                    <td colspan="4" class="text-center text-muted">No marks available</td>
                </tr>
                <?php endif; endforeach; ?>
            </tbody>
        </table>

        <?php if ($role !== 'teacher' || !empty($allowed_subjects)): ?>
            <button type="submit" name="save_scores" class="btn btn-primary mt-3">Save Changes</button>
        <?php endif; ?>
    </form>
</div>

<?php include("partials/footer.php"); ?>
