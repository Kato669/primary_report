<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'partials/header.php';

// ---------------- Role & Login Check ----------------
if (!isset($_SESSION['logged_in']) || !in_array($_SESSION['role'], ['admin', 'class_teacher', 'teacher'])) {
    $_SESSION['must_login'] = 'Please log in first.';
    header('Location: login.php');
    exit;
}

$role = $_SESSION['role'];
$user_id = intval($_SESSION['user_id']);
$class_id = intval($_SESSION['class_id'] ?? 0);
$stream_id = intval($_SESSION['stream_id'] ?? 0);

// ---------------- Get Assigned Subjects for Teacher ----------------
$allowed_subjects = [];
if ($role === 'teacher') {
    $assign_query = "
        SELECT subject_id
        FROM teacher_subject_assignments
        WHERE teacher_id = ?
          AND academic_year = (
              SELECT MAX(academic_year)
              FROM teacher_subject_assignments
              WHERE teacher_id = ?
          )
    ";

    $stmt = $conn->prepare($assign_query);
    $stmt->bind_param('ii', $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $allowed_subjects[] = intval($row['subject_id']);
    }
    $stmt->close();
}

// ---------------- Build Student Filter ----------------
$where_clauses = ["level = 'active'"];
$filter_params = [];
$filter_types = '';

if (($role === 'class_teacher' || $role === 'teacher') && $class_id && $stream_id) {
    $where_clauses[] = 'class_id = ?';
    $where_clauses[] = 'stream_id = ?';
    $filter_params = [$class_id, $stream_id];
    $filter_types = 'ii';
}

$student_sql = 'SELECT student_id, first_name, last_name FROM students WHERE ' . implode(' AND ', $where_clauses) . ' ORDER BY first_name, last_name';
$stmt = $conn->prepare($student_sql);
if ($filter_types !== '') {
    $stmt->bind_param($filter_types, ...$filter_params);
}
$stmt->execute();
$student_result = $stmt->get_result();

$students = [];
$student_ids = [];
while ($row = $student_result->fetch_assoc()) {
    $student_id = intval($row['student_id']);
    $students[$student_id] = [
        'name' => trim($row['first_name'] . ' ' . $row['last_name']),
        'marks' => []
    ];
    $student_ids[] = $student_id;
}
$stmt->close();

// ---------------- Fetch Marks ----------------
if (!empty($student_ids)) {
    $student_list = implode(',', array_map('intval', $student_ids));
    $mark_sql = "
        SELECT
            m.mark_id,
            m.student_id,
            m.score,
            e.exam_name,
            t.term_name,
            s.subject_id,
            s.subject_name
        FROM marks m
        JOIN exams e ON m.exam_id = e.exam_id
        JOIN terms t ON e.term_id = t.term_id
        JOIN subjects s ON m.subject_id = s.subject_id
        WHERE m.student_id IN ($student_list)
    ";

    if ($role === 'teacher') {
        if (empty($allowed_subjects)) {
            $mark_sql .= ' AND 0 = 1';
        } else {
            $subject_list = implode(',', array_map('intval', $allowed_subjects));
            $mark_sql .= " AND m.subject_id IN ($subject_list)";
        }
    }

    $mark_sql .= ' ORDER BY m.student_id, e.exam_name, t.term_name, s.subject_name';
    $mark_result = $conn->query($mark_sql);

    if ($mark_result) {
        while ($row = $mark_result->fetch_assoc()) {
            $sid = intval($row['student_id']);
            $students[$sid]['marks'][] = [
                'mark_id' => intval($row['mark_id']),
                'exam_name' => $row['exam_name'],
                'term_name' => $row['term_name'],
                'subject_id' => intval($row['subject_id']),
                'subject_name' => $row['subject_name'],
                'score' => $row['score'] === null ? '' : $row['score']
            ];
        }
    }
}

$has_editable_mark = false;
?>

<div class="container-fluid">
    <div class="row g-0 my-2">
        <div class="col-lg-4 col-md-4 col-sm-12">
            <a href="<?= SITEURL ?>select_exam.php" class="btn text-capitalize text-white btn-success fs-6">
                Add Score
                <i class="fa-solid fa-pen-to-square"></i>
            </a>
        </div>
    </div>

    <h3 class="text-capitalize fs-6 text-dark py-2">View & Edit Scores</h3>

    <?php if (empty($students)): ?>
        <div class="alert alert-info">No active students found for the selected class/stream.</div>
    <?php elseif ($role === 'teacher' && empty($allowed_subjects)): ?>
        <div class="alert alert-warning">You have no assigned subjects for the current academic year.</div>
    <?php else: ?>
        <form method="POST" action="save_scores.php">
            <div class="table-responsive">
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
                        <?php $sn = 1; ?>
                        <?php foreach ($students as $student_id => $student): ?>
                            <?php if (!empty($student['marks'])): ?>
                                <?php foreach ($student['marks'] as $mark): ?>
                                    <?php
                                        $can_edit = (
                                            $role === 'admin' ||
                                            $role === 'class_teacher' ||
                                            ($role === 'teacher' && in_array($mark['subject_id'], $allowed_subjects, true))
                                        );
                                        $has_editable_mark = $has_editable_mark || $can_edit;
                                    ?>
                                    <tr>
                                        <td><?= $sn++; ?></td>
                                        <td><?= htmlspecialchars($student['name']); ?></td>
                                        <td><?= htmlspecialchars(strtoupper($mark['exam_name'])); ?></td>
                                        <td><?= htmlspecialchars($mark['term_name']); ?></td>
                                        <td><?= htmlspecialchars($mark['subject_name']); ?></td>
                                        <td>
                                            <input
                                                type="number"
                                                name="scores[<?= $mark['mark_id']; ?>]"
                                                value="<?= htmlspecialchars($mark['score']); ?>"
                                                class="form-control form-control-sm"
                                                min="0"
                                                max="100"
                                                <?= $can_edit ? '' : 'disabled'; ?>
                                            />
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td><?= $sn++; ?></td>
                                    <td><?= htmlspecialchars($student['name']); ?></td>
                                    <td colspan="4" class="text-center text-muted">No marks available</td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($has_editable_mark): ?>
                <button type="submit" name="save_scores" class="btn btn-primary mt-3">Save Changes</button>
            <?php endif; ?>
        </form>
    <?php endif; ?>
</div>

<?php require_once 'partials/footer.php'; ?>
