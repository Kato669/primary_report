<?php
ob_start();
include("partials/header.php");

// Clear any previously selected exam
if (isset($_SESSION['exam_id'])) unset($_SESSION['exam_id']);

$errors = [];

if (isset($_POST['select_exam'])) {
    $exam_id = intval($_POST['exam_id'] ?? 0);

    if ($exam_id > 0) {
        $_SESSION['exam_id'] = $exam_id;
        header("Location: addScore.php");
        exit;
    } else {
        $errors[] = "Please select a valid exam.";
    }
}

// Fetch **unique exams** (one per exam_name)
$current_year = date("Y");
$exam_sql = "SELECT MIN(exam_id) AS exam_id, exam_name, academic_year, term_id
             FROM exams
             WHERE academic_year = '$current_year'
             GROUP BY exam_name
             ORDER BY exam_name ASC";

$exam_res = mysqli_query($conn, $exam_sql);

// Optional: fetch term names for display
$terms = [];
$term_res = mysqli_query($conn, "SELECT term_id, term_name FROM terms");
while ($t = mysqli_fetch_assoc($term_res)) {
    $terms[$t['term_id']] = $t['term_name'];
}
?>

<h3 class="text-capitalize py-3 fs-6">Select Exam</h3>
<div class="container-fluid">
    <form action="" method="POST" class="p-3 shadow rounded col-lg-6">
        <div class="mb-3">
            <label class="form-label fw-bold">Exam</label>
            <select name="exam_id" class="form-select shadow-none" required>
                <option value="" selected disabled>Choose exam</option>
                <?php while ($exam = mysqli_fetch_assoc($exam_res)): ?>
                    <option value="<?php echo $exam['exam_id']; ?>">
                        <?php
                        $termName = $terms[$exam['term_id']] ?? '';
                        echo htmlspecialchars($exam['exam_name']) . 
                             ($termName ? " - Term $termName" : "") . 
                             " (" . $exam['academic_year'] . ")";
                        ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php echo implode("<br>", $errors); ?>
            </div>
        <?php endif; ?>

        <button type="submit" name="select_exam" class="btn btn-success text-capitalize">Continue</button>
    </form>
</div>

<?php include("partials/footer.php"); ?>
