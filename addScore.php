<?php
ob_start();
include("partials/header.php");

// ---------------- basic auth check ----------------
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
if ($role === 'admin') {
    if (isset($_POST['switch_class_stream'])) {
        $_SESSION['class_id']  = intval($_POST['class_id']);
        $_SESSION['stream_id'] = intval($_POST['stream_id']);
        header("Location: " . $_SERVER['PHP_SELF']); // reload to refresh students/subjects
        exit;
    }
}

// If class teacher, make sure class_id & stream_id exist in session
if ($role === 'class_teacher' && (!isset($_SESSION['class_id']) || !isset($_SESSION['stream_id']))) {
    echo '<div class="container my-4"><div class="alert alert-danger">
            No class/stream found in session for this teacher.
          </div></div>';
    include("partials/footer.php");
    exit;
}

// ---------------- Fetch dropdown data for admin ----------------
if ($role === 'admin') {
    $classes_res = mysqli_query($conn, "SELECT id, class_name FROM classes ORDER BY class_name");
    $streams_res = mysqli_query($conn, "SELECT id, stream_name FROM streams ORDER BY stream_name");
}

// ---------------- Determine selected class/stream ----------------
$class_id  = isset($_SESSION['class_id']) ? intval($_SESSION['class_id']) : null;
$stream_id = isset($_SESSION['stream_id']) ? intval($_SESSION['stream_id']) : null;

/* ---------------- Fetch Students ---------------- */
$students = [];
if ($class_id && $stream_id) {
    $student_sql = "SELECT student_id, first_name, last_name 
                    FROM students 
                    WHERE class_id = $class_id AND stream_id = $stream_id
                    ORDER BY first_name, last_name";
    $students_res = mysqli_query($conn, $student_sql);
    if ($students_res) {
        $students = mysqli_fetch_all($students_res, MYSQLI_ASSOC);
    }
}

/* ---------------- Fetch Subjects for this Class ---------------- */
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

/* ---------------- Handle Form Submission ---------------- */
if (isset($_POST['save_marks'])) {
    if (!empty($_POST['marks']) && is_array($_POST['marks'])) {
        if (function_exists('mysqli_begin_transaction')) mysqli_begin_transaction($conn);

        $errors = [];
        foreach ($_POST['marks'] as $student_id_raw => $subject_marks) {
            $student_id = intval($student_id_raw);
            if (!is_array($subject_marks)) continue;

            foreach ($subject_marks as $subject_id_raw => $score_raw) {
                $subject_id = intval($subject_id_raw);
                $score_trim = trim($score_raw);

                if ($score_trim === '') continue; // skip empty
                if (!is_numeric($score_trim)) {
                    $errors[] = "Non-numeric score for student $student_id, subject $subject_id";
                    continue;
                }

                $score = intval($score_trim);
                if ($score < 0 || $score > 100) {
                    $errors[] = "Score out of range for student $student_id, subject $subject_id";
                    continue;
                }

                // Check existing mark
                $check_sql = "SELECT mark_id FROM marks 
                              WHERE exam_id = $exam_id 
                                AND student_id = $student_id 
                                AND subject_id = $subject_id
                              LIMIT 1";
                $check_res = mysqli_query($conn, $check_sql);

                if ($check_res && mysqli_num_rows($check_res) > 0) {
                    $row = mysqli_fetch_assoc($check_res);
                    $mark_id = intval($row['mark_id']);
                    $update_sql = "UPDATE marks SET score = $score WHERE mark_id = $mark_id";
                    if (!mysqli_query($conn, $update_sql)) {
                        $errors[] = "Failed updating mark for student $student_id, subject $subject_id.";
                    }
                } else {
                    $insert_sql = "INSERT INTO marks (exam_id, student_id, subject_id, score)
                                   VALUES ($exam_id, $student_id, $subject_id, $score)";
                    if (!mysqli_query($conn, $insert_sql)) {
                        $errors[] = "Failed inserting mark for student $student_id, subject $subject_id.";
                    }
                }
            }
        }

        if (empty($errors)) {
            if (function_exists('mysqli_commit')) mysqli_commit($conn);
            echo '<script>toastr.success("Marks saved successfully!");</script>';
        } else {
            if (function_exists('mysqli_rollback')) mysqli_rollback($conn);
            echo '<script>toastr.error("Some marks failed to save. Check console for details.");</script>';
            error_log(print_r($errors, true));
        }
    } else {
        echo '<script>toastr.warning("No marks were submitted.");</script>';
    }
}
?>

<div class="container-fluid">
    <h3 class="text-capitalize py-3 fs-6">Declare Marks</h3>

    <!-- 🔄 Admin class/stream switcher -->
    <?php if ($role === 'admin'): ?>
    <form method="POST" class="row g-3 mb-3">
    <div class="col-md-4">
        <select id="classSelect" name="class_id" class="form-select" required>
            <option value="" disabled <?= !$class_id ? 'selected' : ''; ?>>Select Class</option>
            <?php mysqli_data_seek($classes_res, 0); while ($class = mysqli_fetch_assoc($classes_res)): ?>
                <option value="<?= $class['id']; ?>" <?= ($class_id == $class['id']) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($class['class_name']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-4">
        <select id="streamSelect" name="stream_id" class="form-select" required>
            <option value="" disabled <?= !$stream_id ? 'selected' : ''; ?>>Select Stream</option>
            <!-- Streams will load here via AJAX -->
        </select>
    </div>

    <div class="col-md-4">
        <button type="submit" name="switch_class_stream" class="btn btn-primary">Switch</button>
    </div>
</form>

<!-- jQuery (needed for AJAX) -->
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
            });
    }

    // On class change → fetch streams
    $("#classSelect").change(function(){
        loadStreams($(this).val());
    });

    // Preload streams if class already selected
    <?php if($class_id): ?>
        loadStreams(<?= intval($class_id) ?>, <?= $stream_id ? intval($stream_id) : 'null' ?>);
    <?php endif; ?>
});
</script>

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