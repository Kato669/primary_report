<?php
ob_start();
include("partials/header.php");

// Allow class_teacher or admin (headteacher) to access
if (!isset($_SESSION['logged_in']) || !in_array($_SESSION['role'], ['class_teacher', 'admin'])) {
    $_SESSION['must_login'] = "Please log in first.";
    header("Location: login.php");
    exit;
}

$class_id  = $_SESSION['class_id'] ?? null;
$stream_id = $_SESSION['stream_id'] ?? null;

if (!$class_id || !$stream_id) {
    echo "<div class='alert alert-danger'>Class/Stream not assigned.</div>";
    include("partials/footer.php");
    exit;
}

// Fetch unique exams (one per exam_name) for dropdown
$exam_res = mysqli_query($conn, "
    SELECT MIN(exam_id) AS exam_id, exam_name
    FROM exams
    GROUP BY exam_name
    ORDER BY exam_name ASC
");

// Handle selection
if (isset($_POST['select_exam'])) {
    $_SESSION['exam_id'] = intval($_POST['exam_id']);
    header("Location: add_comments.php"); // redirect to comment form
    exit;
}
?>

<div class="container my-4">
    <h3 class="mb-3">Select Exam to Add Comments</h3>
    <form method="POST">
        <div class="row">
            <div class="col-lg-6 shadow rounded p-5">
                <div class="mb-3">
                    <select name="exam_id" class="form-select" required>
                        <option value="" disabled selected>Select Exam</option>
                        <?php while ($exam = mysqli_fetch_assoc($exam_res)): ?>
                            <option value="<?php echo $exam['exam_id']; ?>">
                                <?php echo htmlspecialchars($exam['exam_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" name="select_exam" class="btn btn-primary">Proceed to Comment Form</button>
            </div>
        </div>
    </form>
</div>

<?php include("partials/footer.php"); ?>
