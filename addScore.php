<?php
ob_start();
include("partials/header.php");

// ---------------- Basic Auth ----------------
if (!isset($_SESSION['logged_in']) || !in_array($_SESSION['role'], ['class_teacher', 'admin'])) {
    $_SESSION['must_login'] = "Please log in first.";
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['exam_id'])) {
    header("Location: select_exam.php");
    exit;
}

$exam_id = intval($_SESSION['exam_id']);
$role    = $_SESSION['role'];

/* ---------------- Handle class/stream selection (for admins) ---------------- */
if ($role === 'admin' && isset($_POST['switch_class_stream'])) {
    // Only stream is switchable here; class is fixed
    $_SESSION['stream_id'] = intval($_POST['stream_id']);
    header("Location: " . $_SERVER['PHP_SELF']); // reload to refresh students/subjects
    exit;
}

// ---------------- Selected class/stream ----------------
$class_id  = $_SESSION['class_id'] ?? null;
$stream_id = $_SESSION['stream_id'] ?? null;

if (!$class_id) {
    echo '<div class="alert alert-danger">Class not selected. Please go back and select an exam.</div>';
    include("partials/footer.php");
    exit;
}

// Fetch classes for admin (just for display)
if ($role === 'admin') {
    $classes_res = mysqli_query($conn, "SELECT id, class_name FROM classes ORDER BY class_name");
}

// ---------------- Fetch Students ----------------
$students = [];
if ($class_id && $stream_id) {
    $student_sql = "SELECT student_id, first_name, last_name 
                    FROM students 
                    WHERE class_id = $class_id 
                      AND stream_id = $stream_id
                      AND level = 'active'
                    ORDER BY first_name, last_name";
    $students_res = mysqli_query($conn, $student_sql);
    if ($students_res) {
        $students = mysqli_fetch_all($students_res, MYSQLI_ASSOC);
    }
}

// ---------------- Fetch Subjects ----------------
$subjects = [];
if ($class_id) {
    $subject_sql = "SELECT cs.subject_id, s.subject_name
                    FROM class_subjects cs
                    JOIN subjects s ON cs.subject_id = s.subject_id
                    WHERE cs.class_id = $class_id
                    ORDER BY s.subject_name";
    $subjects_res = mysqli_query($conn, $subject_sql);
    if ($subjects_res) {
        $subjects = mysqli_fetch_all($subjects_res, MYSQLI_ASSOC);
    }
}
?>

<div class="container-fluid">
    <h3 class="text-capitalize py-3 fs-6">Declare Marks</h3>

    <!-- Admin class/stream switcher -->
    <?php if ($role === 'admin'): ?>
    <form method="POST" class="row g-3 mb-3">
        <div class="col-md-4">
            <!-- Disabled class dropdown -->
            <select id="classSelect" class="form-select" disabled>
                <option value="" disabled>Select Class</option>
                <?php mysqli_data_seek($classes_res, 0); while ($class = mysqli_fetch_assoc($classes_res)): ?>
                    <option value="<?= $class['id']; ?>" <?= ($class_id == $class['id']) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($class['class_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <!-- Hidden input to submit class_id -->
            <input type="hidden" name="class_id" value="<?= $class_id; ?>">
        </div>

        <div class="col-md-4">
            <select id="streamSelect" name="stream_id" class="form-select" required>
                <option value="" disabled>Loading streams...</option>
            </select>
        </div>

        <div class="col-md-4">
            <button type="submit" name="switch_class_stream" class="btn btn-primary">Switch Stream</button>
        </div>
    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function(){
        function loadStreams(classID, selectedStream = null){
            if(!classID) return;
            $.get("get_streams.php", { class_id: classID })
                .done(function(data){
                    $("#streamSelect").html(data);
                    if(selectedStream) $("#streamSelect").val(selectedStream);
                })
                .fail(function(xhr){
                    console.error("Stream AJAX Error:", xhr.responseText);
                    $("#streamSelect").html('<option value="">Error loading streams</option>');
                });
        }

        // Load streams for pre-selected class & stream
        <?php if($class_id): ?>
            loadStreams(<?= intval($class_id) ?>, <?= $stream_id ? intval($stream_id) : 'null' ?>);
        <?php endif; ?>
    });
    </script>
    <?php endif; ?>

    <!-- Students & subjects table -->
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
                                <th><?= htmlspecialchars($sub['subject_name']); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                            <?php foreach ($subjects as $sub): 
                                $existing_score = '';
                                $sid = intval($student['student_id']);
                                $sub_id = intval($sub['subject_id']);
                                $check_sql = "SELECT score FROM marks 
                                              WHERE exam_id = $exam_id AND student_id = $sid AND subject_id = $sub_id LIMIT 1";
                                $check_res = mysqli_query($conn, $check_sql);
                                if ($check_res && mysqli_num_rows($check_res) > 0) {
                                    $existing_score = mysqli_fetch_assoc($check_res)['score'];
                                }
                            ?>
                            <td>
                                <input 
                                    type="number" 
                                    name="marks[<?= $sid; ?>][<?= $sub_id; ?>]"
                                    class="form-control shadow-none"
                                    min="0" max="100"
                                    value="<?= htmlspecialchars($existing_score); ?>">
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" name="save_marks" class="btn btn-success">Save Marks</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php include("partials/footer.php"); ?>
