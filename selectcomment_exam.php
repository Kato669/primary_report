<?php
ob_start();
include("partials/header.php");

// ---------------- Role & Login Check ----------------
if(!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'class_teacher'){
    $_SESSION['must_login'] = "Please log in first.";
    header("Location: login.php");
    exit;
}

$class_id = $_SESSION['class_id'] ?? null;

if(!$class_id){
    echo '<div class="alert alert-danger">Class not assigned.</div>';
    include("partials/footer.php");
    exit;
}

// ---------------- Fetch terms for this class ----------------
// Join terms table to get term_name
$term_res = mysqli_query($conn, "
    SELECT exams.term_id, exams.academic_year, terms.term_name
    FROM exams
    JOIN terms ON exams.term_id = terms.term_id
    WHERE exams.class_id = $class_id
    GROUP BY exams.term_id, exams.academic_year
    ORDER BY exams.academic_year DESC, exams.term_id ASC
");

// ---------------- Handle term selection ----------------
if(isset($_POST['select_term'])){
    $_SESSION['term'] = intval($_POST['term_id']);
    $_SESSION['year'] = intval($_POST['academic_year']);
    header("Location: add_comments.php");
    exit;
}
?>

<div class="container my-4">
    <h3 class="mb-3">Select Term to Add Comments</h3>
    <form method="POST">
        <div class="mb-3">
            <select name="term_id" class="form-select" required>
                <option value="" disabled selected>Select Term</option>
                <?php while($term = mysqli_fetch_assoc($term_res)): ?>
                    <option value="<?php echo $term['term_id']; ?>" data-year="<?php echo $term['academic_year']; ?>">
                        <?php echo htmlspecialchars($term['term_name']); ?> <?php echo $term['academic_year']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Hidden input for academic year -->
        <input type="hidden" name="academic_year" id="academic_year">

        <button type="submit" name="select_term" class="btn btn-primary">Proceed to Comment Form</button>
    </form>
</div>

<script>
    // Set hidden academic_year based on selected term
    const termSelect = document.querySelector('select[name="term_id"]');
    const yearInput = document.getElementById('academic_year');

    termSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        yearInput.value = selectedOption.getAttribute('data-year');
    });
</script>

<?php include("partials/footer.php"); ?>
