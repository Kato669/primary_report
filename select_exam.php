<?php
ob_start();
include("partials/header.php");

// Clear any previously selected exam
unset($_SESSION['exam_id']);

// Determine user role
$is_class_teacher = isset($_SESSION['role']) && $_SESSION['role'] === 'class_teacher';
$class_teacher_class_id = $_SESSION['class_id'] ?? null;
$errors = [];

// Handle form submission
if (isset($_POST['select_exam'])) {
    $exam_id  = intval($_POST['exam_id'] ?? 0);
    $class_id = intval($_POST['class_id'] ?? 0);
    $stream_id = intval($_POST['stream_id'] ?? 0);
    $term_id  = intval($_POST['term_id'] ?? 0);

    if ($exam_id > 0 && $class_id > 0 && $stream_id > 0 && $term_id > 0) {
        $_SESSION['exam_id']   = $exam_id;
        $_SESSION['class_id']  = $class_id;
        $_SESSION['stream_id'] = $stream_id;
        $_SESSION['term_id']   = $term_id;

        header("Location: addScore.php");
        exit;
    } else {
        $errors[] = "Please select all required fields.";
    }
}

// Fetch classes (only if not class_teacher)
if (!$is_class_teacher) {
    $classes_res = mysqli_query($conn, "SELECT id, class_name FROM classes");
}

// Fetch terms
$terms = [];
$term_res = mysqli_query($conn, "SELECT term_id, term_name FROM terms ORDER BY term_id");
if ($term_res) {
    while ($t = mysqli_fetch_assoc($term_res)) {
        $terms[$t['term_id']] = $t['term_name'];
    }
}
?>

<h3 class="text-capitalize py-3 fs-6">Select Exam</h3>
<div class="container-fluid">
    <form action="" method="POST" class="p-3 shadow rounded col-lg-6">

        <!-- Class dropdown -->
        <?php if (!$is_class_teacher): ?>
        <div class="mb-3">
            <label class="form-label fw-bold">Class</label>
            <select name="class_id" id="classSelect" class="form-select shadow-none" required>
                <option value="" selected disabled>Choose class</option>
                <?php while ($class = mysqli_fetch_assoc($classes_res)): ?>
                    <option value="<?= $class['id']; ?>"><?= htmlspecialchars($class['class_name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <?php else: ?>
            <input type="hidden" name="class_id" id="classSelect" value="<?= htmlspecialchars($class_teacher_class_id); ?>">
        <?php endif; ?>

        <!-- Stream dropdown -->
        <div class="mb-3">
            <label class="form-label fw-bold">Stream</label>
            <select name="stream_id" id="streamSelect" class="form-select shadow-none" required disabled>
                <option value="" selected disabled>Select Stream</option>
            </select>
        </div>

        <!-- Term dropdown -->
        <div class="mb-3">
            <label class="form-label fw-bold">Term</label>
            <select name="term_id" id="termSelect" class="form-select shadow-none" required>
                <option value="" selected disabled>Choose Term</option>
                <?php foreach ($terms as $term_id => $term_name): ?>
                    <option value="<?= $term_id; ?>"><?= htmlspecialchars($term_name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Exam dropdown -->
        <div class="mb-3">
            <label class="form-label fw-bold">Exam</label>
            <select name="exam_id" id="examSelect" class="form-select shadow-none" required disabled>
                <option value="" selected disabled>Select Exam</option>
            </select>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><?= implode("<br>", $errors); ?></div>
        <?php endif; ?>

        <button type="submit" name="select_exam" class="btn btn-success text-capitalize">Continue</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {

    function loadStreams(classID) {
        if (!classID) return;
        $('#streamSelect').prop('disabled', true).html('<option>Loading streams...</option>');
        $.get('get_streams.php', { class_id: classID }, function(data) {
            $('#streamSelect').html(data).prop('disabled', false);
        }).fail(function() {
            $('#streamSelect').html('<option value="">Error loading streams</option>').prop('disabled', true);
        });
    }

    function loadExams(classID, termID) {
        if (!classID || !termID) return;
        $('#examSelect').prop('disabled', true).html('<option>Loading exams...</option>');
        $.get('get_exams.php', { class_id: classID, term_id: termID }, function(data) {
            $('#examSelect').html(data).prop('disabled', false);
        }).fail(function() {
            $('#examSelect').html('<option value="">Error loading exams</option>').prop('disabled', true);
        });
    }

    // When class changes
    $('#classSelect').change(function() {
        var classID = $(this).val();
        loadStreams(classID);
    });

    // When term changes
    $('#termSelect').change(function() {
        var termID = $(this).val();
        var classID = $('#classSelect').val();
        loadExams(classID, termID);
    });
});
</script>

<?php include("partials/footer.php"); ?>
