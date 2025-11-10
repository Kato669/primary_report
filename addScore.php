<?php
ob_start();
include("partials/header.php");

// ---------------- AUTH CHECK ----------------
if (!isset($_SESSION['logged_in']) || !in_array($_SESSION['role'], ['class_teacher', 'admin', 'subject_teacher'])) {
    $_SESSION['must_login'] = "Please log in first.";
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'];

// ---------------- ALLOW SWITCHING ----------------
if (isset($_GET['change_selection'])) {
    unset($_SESSION['exam_id'], $_SESSION['class_id'], $_SESSION['stream_id'], $_SESSION['term_id']);
    header("Location: select_exam.php");
    exit;
}

// ---------------- ENSURE SELECTION ----------------
if (!isset($_SESSION['exam_id'], $_SESSION['class_id'], $_SESSION['stream_id'], $_SESSION['term_id'])) {
    header("Location: select_exam.php");
    exit;
}

$exam_id   = intval($_SESSION['exam_id']);
$class_id  = intval($_SESSION['class_id']);
$stream_id = intval($_SESSION['stream_id']);
$term_id   = intval($_SESSION['term_id']);
$year      = $_SESSION['academic_year'] ?? date('Y');

// ---------------- FETCH DISPLAY NAMES ----------------
$class_name = $stream_name = $term_name = $exam_name = '';

$class_res = mysqli_query($conn, "SELECT class_name FROM classes WHERE id=$class_id LIMIT 1");
if ($class_res && mysqli_num_rows($class_res) > 0) $class_name = mysqli_fetch_assoc($class_res)['class_name'];

$stream_res = mysqli_query($conn, "SELECT stream_name FROM streams WHERE id=$stream_id LIMIT 1");
if ($stream_res && mysqli_num_rows($stream_res) > 0) $stream_name = mysqli_fetch_assoc($stream_res)['stream_name'];

$term_res = mysqli_query($conn, "SELECT term_name FROM terms WHERE term_id=$term_id LIMIT 1");
if ($term_res && mysqli_num_rows($term_res) > 0) $term_name = mysqli_fetch_assoc($term_res)['term_name'];

$exam_res = mysqli_query($conn, "SELECT exam_name FROM exams WHERE exam_id=$exam_id LIMIT 1");
if ($exam_res && mysqli_num_rows($exam_res) > 0) $exam_name = mysqli_fetch_assoc($exam_res)['exam_name'];

// ---------------- SAVE MARKS ----------------
if (isset($_POST['save_marks']) && isset($_POST['marks'])) {
    $conn->begin_transaction();
    try {
        foreach ($_POST['marks'] as $student_id => $subjects) {
            foreach ($subjects as $subject_id => $score) {
                if ($score === '') continue;
                $student_id = intval($student_id);
                $subject_id = intval($subject_id);
                $score = floatval($score);

                if ($score < 0 || $score > 100) throw new Exception("Invalid score range.");

                $exists = $conn->query("SELECT 1 FROM marks WHERE exam_id=$exam_id AND student_id=$student_id AND subject_id=$subject_id LIMIT 1");
                if ($exists && $exists->num_rows > 0) {
                    $conn->query("UPDATE marks SET score=$score WHERE exam_id=$exam_id AND student_id=$student_id AND subject_id=$subject_id");
                } else {
                    $conn->query("INSERT INTO marks (exam_id, student_id, subject_id, score) VALUES ($exam_id, $student_id, $subject_id, $score)");
                }
            }
        }
        $conn->commit();
        $_SESSION['success_msg'] = "Marks saved successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_msg'] = "Error: " . $e->getMessage();
    }
    header("Location: addScore.php");
    exit;
}

// ---------------- FETCH STUDENTS ----------------
$students = [];
$student_sql = "SELECT student_id, first_name, last_name 
                FROM students 
                WHERE class_id=$class_id AND stream_id=$stream_id AND level='active'
                ORDER BY first_name, last_name";
$res = mysqli_query($conn, $student_sql);
if ($res) $students = mysqli_fetch_all($res, MYSQLI_ASSOC);

// ---------------- FETCH SUBJECTS ----------------
$subjects = [];
$subject_sql = "SELECT cs.subject_id, s.short_code 
                FROM class_subjects cs
                JOIN subjects s ON cs.subject_id=s.subject_id
                WHERE cs.class_id=$class_id
                ORDER BY s.short_code";
$res = mysqli_query($conn, $subject_sql);
if ($res) $subjects = mysqli_fetch_all($res, MYSQLI_ASSOC);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center py-3">
        <h3 class="text-capitalize fs-6 m-0">Declare Marks</h3>
        <a href="?change_selection=1" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left me-1"></i> Change Exam/Class
        </a>
    </div>

    <!-- Display selected context -->
    <div class="alert alert-info py-2">
    <strong>Class:</strong> <?= htmlspecialchars($class_name); ?> |
    <strong>Stream:</strong> <?= htmlspecialchars($stream_name); ?> |
    <strong>Term:</strong> <?= htmlspecialchars($term_name); ?> |
    <strong>Year:</strong> <?= htmlspecialchars($year); ?> |
    <strong>Exam Set:</strong> <?= strtoupper(htmlspecialchars($exam_name)); ?>
    </div>


    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_msg']); ?></div>
        <?php unset($_SESSION['success_msg']); ?>
    <?php elseif (isset($_SESSION['error_msg'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error_msg']); ?></div>
        <?php unset($_SESSION['error_msg']); ?>
    <?php endif; ?>

    <?php if (empty($subjects)): ?>
        <div class="alert alert-warning">No subjects assigned to this class.</div>
    <?php elseif (empty($students)): ?>
        <div class="alert alert-info">No students found in this class/stream.</div>
    <?php else: ?>
        <div class="table-responsive">
            <form method="POST">
                <table class="table table-bordered table-striped">
                    <thead class="table-success">
                        <tr>
                            <th>Student Name</th>
                            <?php foreach ($subjects as $sub): ?>
                                <th><?= htmlspecialchars($sub['short_code']); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                            <?php foreach ($subjects as $sub): 
                                $sid = intval($student['student_id']);
                                $sub_id = intval($sub['subject_id']);
                                $existing = mysqli_query($conn, "SELECT score FROM marks WHERE exam_id=$exam_id AND student_id=$sid AND subject_id=$sub_id LIMIT 1");
                                $score = ($existing && mysqli_num_rows($existing) > 0) ? mysqli_fetch_assoc($existing)['score'] : '';
                            ?>
                            <td>
                                <input type="number" name="marks[<?= $sid; ?>][<?= $sub_id; ?>]" 
                                    value="<?= htmlspecialchars($score); ?>" 
                                    min="0" max="100" class="form-control shadow-none">
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <button type="submit" name="save_marks" class="btn btn-success">
                        <i class="fa fa-save me-1"></i> Save Marks
                    </button>
                    <button type="button" class="btn btn-primary" id="printMarks">
                        <i class="fa fa-print me-1"></i> Print
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('printMarks').addEventListener('click', function() {
    const printArea = document.querySelector('.table-responsive');
    if (!printArea) { alert('Nothing to print!'); return; }
    const win = window.open('', '', 'width=900,height=700');
    win.document.write('<html><head><title>Print Marks</title>');
    win.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">');
    win.document.write('</head><body>');
    win.document.write('<h4 class="text-center mb-3">Student Marks</h4>');
    win.document.write('<div class="text-center mb-3"><strong>Class:</strong> <?= htmlspecialchars($class_name); ?> | <strong>Stream:</strong> <?= htmlspecialchars($stream_name); ?> | <strong>Term:</strong> <?= htmlspecialchars($term_name); ?> | <strong>Year:</strong> <?= htmlspecialchars($year); ?> | <strong>Exam:</strong> <?= htmlspecialchars($exam_name); ?></div>');
    win.document.write(printArea.innerHTML);
    win.document.write('</body></html>');
    win.document.close();
    win.focus();
    win.print();
    win.close();
});
</script>

<?php include("partials/footer.php"); ?>
