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

// Validate selection
if (!$class_id || !$stream_id || !$term || !$year) {
    echo '<div class="alert alert-danger">Class/Stream/Term/Year not selected.</div>';
    include("partials/footer.php");
    exit;
}

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
    SELECT s.student_id, s.first_name, s.last_name, s.gender
    FROM students s
    WHERE s.class_id=$class_id AND s.stream_id=$stream_id
    ORDER BY s.first_name, s.last_name
";

$students_res = mysqli_query($conn, $student_sql);
?>

<div class="container my-4">
    <h3 class="text-capitalize fs-6 text-dark mb-3">
        <?php echo $_SESSION['role']==='admin' ? "Enter Head Teacher Comments (Batch)" : "Enter Class Teacher Comments (Batch)"; ?>
    </h3>

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
            <thead class="table-dark">
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

                    $final_avg = $subject_count > 0 ? round($total_avg / $subject_count, 2) : 0.00;

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
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['first_name'].' '.$student['last_name']); ?></td>
                    <td><input type="text" class="form-control" value="<?php echo $final_avg; ?>" disabled></td>
                    <td>
                        <textarea name="comments[<?php echo $student_id; ?>]" class="form-control" rows="2"><?php echo htmlspecialchars($existing_comment); ?></textarea>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <button type="submit" name="save_comment" class="btn btn-primary text-capitalize">Save All Comments</button>
    </form>
</div>

<?php include("partials/footer.php"); ?>
