<?php
ob_start();
include("partials/header.php");

// Clear any previously selected exam
if (isset($_SESSION['exam_id'])) unset($_SESSION['exam_id']);

$errors = [];

// Handle form submission
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

// Fetch classes
$classes_res = mysqli_query($conn, "SELECT id, class_name FROM classes ORDER BY class_name");

// Fetch terms
$terms = [];
$term_res = mysqli_query($conn, "SELECT term_id, term_name FROM terms");
while ($t = mysqli_fetch_assoc($term_res)) {
    $terms[$t['term_id']] = $t['term_name'];
}
?>

<h3 class="text-capitalize py-3 fs-6">Select Exam</h3>
<div class="container-fluid">
    <form action="" method="POST" class="p-3 shadow rounded col-lg-6">
        <!-- Class dropdown -->
        <div class="mb-3">
            <label class="form-label fw-bold">Class</label>
            <select name="class_id" id="classSelect" class="form-select shadow-none" required>
                <option value="" selected disabled>Choose class</option>
                <?php while ($class = mysqli_fetch_assoc($classes_res)): ?>
                    <option value="<?= $class['id']; ?>"><?= htmlspecialchars($class['class_name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Exam dropdown -->
        <div class="mb-3">
            <label class="form-label fw-bold">Exam</label>
            <select name="exam_id" id="examSelect" class="form-select shadow-none" required disabled>
                <option value="" selected disabled>Select exam</option>
            </select>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?= implode("<br>", $errors); ?>
            </div>
        <?php endif; ?>

        <button type="submit" name="select_exam" class="btn btn-success text-capitalize">Continue</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#classSelect').change(function() {
        var classID = $(this).val();
        if (!classID) return;

        // Disable exam dropdown and show loading
        $('#examSelect').prop('disabled', true).html('<option>Loading exams...</option>');

        // Fetch exams for the selected class via AJAX
        $.get('get_exams.php', { class_id: classID }, function(data) {
            $('#examSelect').html(data).prop('disabled', false);
        }).fail(function(xhr) {
            console.error("Failed to fetch exams:", xhr.responseText);
            $('#examSelect').html('<option value="">Error loading exams</option>').prop('disabled', true);
        });
    });
});
</script>

<?php include("partials/footer.php"); ?>
