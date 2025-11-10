<?php
ob_start();
include("partials/header.php");

// ---------------- Role & Login Check ----------------
if (!isset($_SESSION['logged_in']) || !in_array($_SESSION['role'], ['class_teacher', 'admin'])) {
    $_SESSION['must_login'] = "Please log in first.";
    header("Location: login.php");
    exit;
}

// ---------------- Get Session Variables ----------------
$class_id  = $_SESSION['class_id'] ?? null;
$stream_id = $_SESSION['stream_id'] ?? null;
$term      = $_SESSION['term'] ?? null;
$year      = $_SESSION['year'] ?? null;

// ---------------- Handle selection change (keep selections visible) ----------------
if (isset($_POST['change_selection'])) {
    // set session values and reload so the rest of the page uses them
    $_SESSION['class_id']  = intval($_POST['class_id']);
    $_SESSION['stream_id'] = intval($_POST['stream_id']);
    $_SESSION['term']      = intval($_POST['term']);
    $_SESSION['year']      = intval($_POST['year']);

    header("Location: add_comments.php");
    exit;
}

// If any of the required selection is missing, show a compact selection form at the top
// so the user can pick Class / Stream / Term / Year without leaving this page.
// We'll render that form below and stop further processing if selections are still missing.

// Determine comment column (class teacher vs head teacher)
$comment_column = $_SESSION['role'] === 'admin' ? 'head_teacher_comment' : 'class_teacher_comment';

$errors = [];
$success = "";

// ---------------- Get all exams for this term/year/class ----------------
$exam_ids = [];
$exam_res = mysqli_query($conn, "SELECT exam_id FROM exams WHERE class_id=$class_id AND term_id=$term AND academic_year=$year");
while ($row = mysqli_fetch_assoc($exam_res)) {
    $exam_ids[] = intval($row['exam_id']);
}

if (empty($exam_ids)) {
    echo '<div class="alert alert-danger">No exams found for this class and term/year.</div>';
    include("partials/footer.php");
    exit;
}

$exam_ids_list = implode(',', $exam_ids);

// ---------------- Handle Form Submission ----------------
if (isset($_POST['save_comment'])) {
    if (!empty($_POST['comments']) && is_array($_POST['comments'])) {
        foreach ($_POST['comments'] as $student_id => $comment) {
            $student_id = intval($student_id);
            $comment = trim($comment);
            if ($comment === '') continue;

            // For each exam, insert or update comment
            foreach ($exam_ids as $exam_id) {
                $check_sql = "SELECT comment_id FROM student_comments WHERE student_id=$student_id AND exam_id=$exam_id LIMIT 1";
                $check_res = mysqli_query($conn, $check_sql);
                if ($check_res && mysqli_num_rows($check_res) > 0) {
                    $update_sql = "UPDATE student_comments 
                                   SET $comment_column='".mysqli_real_escape_string($conn, $comment)."', updated_at=NOW()
                                   WHERE student_id=$student_id AND exam_id=$exam_id";
                    mysqli_query($conn, $update_sql);
                } else {
                    $insert_sql = "INSERT INTO student_comments (student_id, exam_id, $comment_column, created_at)
                                   VALUES ($student_id, $exam_id, '".mysqli_real_escape_string($conn, $comment)."', NOW())";
                    mysqli_query($conn, $insert_sql);
                }
            }
        }
        $success = "Comments saved successfully for all students.";
    }
}

// ---------------- Fetch Students & Compute Averages ----------------
$student_sql = "
    SELECT 
    s.student_id, 
    s.first_name, 
    s.last_name, 
    s.gender
FROM students s
WHERE s.class_id = $class_id
  AND s.stream_id = $stream_id
  AND s.level = 'active'
ORDER BY s.first_name, s.last_name

";

$students_res = mysqli_query($conn, $student_sql);

// ---------------- Define Dropdown Comments ----------------
$class_teacher_comments = [
    "Excellent performance and exemplary discipline — keep it up!",
    "Hardworking and disciplined; continues to make steady progress.",
    "Good effort and behavior; can achieve more with greater focus.",
    "Average performance; needs to be more consistent and attentive.",
    "Below average work; must improve concentration and discipline.",
    "Poor results and irregular conduct; serious improvement is required."
];

$head_teacher_comments = [
    "An outstanding performance — keep up this excellent standard.",
    "Good work — continue striving for excellence.",
    "A commendable achievement; consistent effort clearly shown.",
    "Good performance — aim even higher next term.",
    "A fair result; steady progress observed, more effort encouraged.",
    "Satisfactory work though improvement is still needed.",
    "Below average performance — more commitment required."
];
?>

<div class="container my-4">
    <h3 class="text-capitalize fs-6 text-dark mb-3">
        <?php echo $_SESSION['role']==='admin' ? "Enter Head Teacher Comments (Batch)" : "Enter Class Teacher Comments "; ?>
    </h3>

    <?php
    // Fetch classes and terms for the top selection panel
    $classes_res = mysqli_query($conn, "SELECT id, class_name FROM classes");
    $terms_res = mysqli_query($conn, "SELECT term_id, term_name FROM terms ORDER BY term_id");

    // Preload streams for the currently selected class (if any)
    $streams_res = null;
    if ($class_id) {
        $streams_res = mysqli_query($conn, "SELECT id, stream_name FROM streams WHERE class_id = $class_id ORDER BY stream_name");
    }
    ?>

    <!-- Selection panel to keep Class / Stream / Term / Year visible & changeable -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="POST" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Class</label>
                    <select name="class_id" id="topClassSelect" class="form-select" <?php echo $_SESSION['role']==='class_teacher' ? 'disabled' : ''; ?> required>
                        <option value="" disabled <?php echo !$class_id ? 'selected' : ''; ?>>Select Class</option>
                        <?php while ($c = mysqli_fetch_assoc($classes_res)): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo ($class_id && $class_id == $c['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['class_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Stream</label>
                    <select name="stream_id" id="topStreamSelect" class="form-select" required>
                        <option value="" disabled <?php echo !$stream_id ? 'selected' : ''; ?>>Select Stream</option>
                        <?php if ($streams_res): while($s = mysqli_fetch_assoc($streams_res)): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo ($stream_id && $stream_id == $s['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['stream_name']); ?></option>
                        <?php endwhile; endif; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Term</label>
                    <select name="term" class="form-select" required>
                        <option value="" disabled <?php echo !$term ? 'selected' : ''; ?>>Select Term</option>
                        <?php while ($t = mysqli_fetch_assoc($terms_res)): ?>
                            <option value="<?php echo $t['term_id']; ?>" <?php echo ($term && $term == $t['term_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($t['term_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-select" required>
                        <option value="" disabled <?php echo !$year ? 'selected' : ''; ?>>Select Year</option>
                        <?php $currentYear = date("Y"); for ($y = $currentYear; $y >= ($currentYear - 5); $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo ($year && $year == $y) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="submit" name="change_selection" class="btn btn-secondary">SEARCH <i class="fa-solid fa-search"></i></button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Load streams dynamically when class is changed in the top panel
        $(document).ready(function(){
            $('#topClassSelect').change(function(){
                var classID = $(this).val();
                if (!classID) return;

                $.get('get_streams.php', { class_id: classID })
                    .done(function(data){
                        $('#topStreamSelect').html(data);
                    })
                    .fail(function(xhr){
                        console.error('Error loading streams:', xhr.responseText);
                    });
            });
        });
    </script>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $err) echo "<p>$err</p>"; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <table class="table table-bordered">
            <thead class="table-success">
                <tr>
                    <th>Student Name</th>
                    <th>Total Average</th>
                    <th>Comment</th>
                </tr>
            </thead>
            <tbody>
                <?php while($student = mysqli_fetch_assoc($students_res)):
                    $student_id = $student['student_id'];

                    // Get subject averages for this student
                    $marks_sql = "
                        SELECT m.subject_id, AVG(m.score) AS avg_score
                        FROM marks m
                        INNER JOIN exams e ON e.exam_id = m.exam_id
                        WHERE m.student_id=$student_id
                        AND e.exam_id IN ($exam_ids_list)
                        GROUP BY m.subject_id
                    ";
                    $marks_res = mysqli_query($conn, $marks_sql);

                    $total_avg = 0;
                    $subject_count = 0;

                    if ($marks_res && mysqli_num_rows($marks_res) > 0) {
                        while ($row = mysqli_fetch_assoc($marks_res)) {
                            $total_avg += floatval($row['avg_score']);
                            $subject_count++;
                        }
                    }

                    $final_avg = $subject_count > 0 ? round($total_avg / $subject_count, 0) : 0.00;

                    // Fetch existing comment (any exam in the term)
                    $comment_sql = "
                        SELECT $comment_column FROM student_comments 
                        WHERE student_id=$student_id AND exam_id IN ($exam_ids_list)
                        LIMIT 1
                    ";
                    $comment_res = mysqli_query($conn, $comment_sql);
                    $existing_comment = ($comment_res && mysqli_num_rows($comment_res) > 0)
                        ? mysqli_fetch_assoc($comment_res)[$comment_column]
                        : '';

                    // Determine which comment set to show
                    $dropdown_comments = $_SESSION['role'] === 'admin' ? $head_teacher_comments : $class_teacher_comments;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['first_name'].' '.$student['last_name']); ?></td>
                    <td><input type="text" class="form-control" value="<?php echo $final_avg; ?>" disabled></td>
                    <td>
                        <div class="input-group">
                            <select class="form-select comment-select" onchange="updateComment(this)">
                                <option value="">-- Select Comment --</option>
                                <?php foreach($dropdown_comments as $comment): ?>
                                    <option value="<?php echo htmlspecialchars($comment); ?>"><?php echo htmlspecialchars($comment); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <textarea name="comments[<?php echo $student_id; ?>]" class="form-control mt-2" rows="2"><?php echo htmlspecialchars($existing_comment); ?></textarea>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <button type="submit" name="save_comment" class="btn btn-primary text-capitalize">Save All Comments</button>
    </form>
</div>

<script>
function updateComment(selectElem) {
    const textarea = selectElem.closest('td').querySelector('textarea');
    if (selectElem.value !== "") {
        textarea.value = selectElem.value;
    }
}
</script>

<?php include("partials/footer.php"); ?>