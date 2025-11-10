<?php
ob_start();
include("partials/header.php");

// Only allow admin
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['must_login'] = "Please log in first.";
    header("Location: login.php");
    exit;
}

// Fetch all classes
$class_res = mysqli_query($conn, "SELECT id, class_name FROM classes");

// Fetch all terms from DB (instead of hardcoding 1,2,3)
$terms_res = mysqli_query($conn, "SELECT term_id, term_name FROM terms ORDER BY term_id");

// Handle form submission
if (isset($_POST['proceed_to_comments'])) {
    $_SESSION['class_id']  = intval($_POST['class_id']);
    $_SESSION['stream_id'] = intval($_POST['stream_id']);
    $_SESSION['term']      = intval($_POST['term']);  // real term_id from DB
    $_SESSION['year']      = intval($_POST['year']);  // academic_year is INT

    header("Location: add_comments.php");
    exit;
}
?>

<div class="container my-4">
    <h3 class="mb-3">Select Class, Stream, Term & Year to Enter Comments</h3>
    <form method="POST">
        <!-- Class Dropdown -->
        <div class="mb-3">
            <label>Class</label>
            <select id="classSelect" name="class_id" class="form-select" required>
                <option value="" disabled selected>Select Class</option>
                <?php while($cls = mysqli_fetch_assoc($class_res)): ?>
                    <option value="<?php echo $cls['id']; ?>">
                        <?php echo htmlspecialchars($cls['class_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Stream Dropdown -->
        <div class="mb-3">
            <label>Stream</label>
            <select id="streamSelect" name="stream_id" class="form-select" required>
                <option value="" disabled selected>Select Stream</option>
                <!-- Streams will be dynamically loaded via AJAX -->
            </select>
        </div>

        <!-- Term Dropdown (Dynamic) -->
        <div class="mb-3">
            <label>Term</label>
            <select name="term" class="form-select" required>
                <option value="" disabled selected>Select Term</option>
                <?php while ($term = mysqli_fetch_assoc($terms_res)): ?>
                    <option value="<?php echo $term['term_id']; ?>">
                        <?php echo htmlspecialchars($term['term_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Year Dropdown -->
        <div class="mb-3">
            <label>Year</label>
            <select name="year" class="form-select" required>
                <option value="" disabled selected>Select Year</option>
                <?php
                $currentYear = date("Y");
                for ($y = $currentYear; $y >= ($currentYear - 5); $y--): ?>
                    <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <button type="submit" name="proceed_to_comments" class="btn btn-primary">
            Proceed to Comment Form
        </button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    $('#classSelect').change(function(){
        var classID = $(this).val();
        if (!classID) return;

        // Load Streams dynamically
        $.get('get_streams.php', { class_id: classID })
            .done(function(data){
                $('#streamSelect').html(data);
            })
            .fail(function(xhr){
                console.error("Stream AJAX Error:", xhr.responseText);
            });
    });
});
</script>

<?php include("partials/footer.php"); ?>
