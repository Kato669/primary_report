<?php 
ob_start();
include("partials/header.php");

$errors = [];
$success = "";

// Handle form submission
if (isset($_POST['fees'])) {
    // Sanitize and validate input
    $class_id   = intval($_POST['class_name'] ?? 0);
    $term_id    = intval($_POST['term'] ?? 0);
    $year       = trim(mysqli_real_escape_string($conn, $_POST['year'] ?? ""));
    $term_end   = trim($_POST['end'] ?? "");
    $next_start = trim($_POST['next_term'] ?? "");
    $fees_day   = trim($_POST['d_fees'] ?? "");
    $fees_board = trim($_POST['b_fees'] ?? "");

    // --- Validation ---
    if ($class_id <= 0) $errors[] = "Please select a class.";
    if ($term_id <= 0) $errors[] = "Please select a term.";
    if (empty($year) || !preg_match('/^[0-9]{4}$/', $year)) $errors[] = "Please enter a valid academic year (e.g. 2025).";
    if (empty($term_end)) $errors[] = "Please select term end date.";
    if (empty($next_start)) $errors[] = "Please select next term start date.";
    if (!is_numeric($fees_day) || $fees_day < 0) $errors[] = "Day fees must be a valid positive number.";
    if (!is_numeric($fees_board) || $fees_board < 0) $errors[] = "Boarding fees must be a valid positive number.";

    if (empty($errors)) {
        // Check if record exists
        $check_sql = "SELECT info_id FROM term_info WHERE class_id=$class_id AND term_id=$term_id AND academic_year='$year' LIMIT 1";
        $check_res = mysqli_query($conn, $check_sql);

        if ($check_res && mysqli_num_rows($check_res) > 0) {
            // UPDATE existing
            $update_sql = "
                UPDATE term_info SET 
                    term_end='$term_end',
                    next_start='$next_start',
                    fees_day='$fees_day',
                    fees_boarding='$fees_board'
                WHERE class_id=$class_id AND term_id=$term_id AND academic_year='$year'
            ";
            $res = mysqli_query($conn, $update_sql);
            if ($res) {
                $success = "Fees structure updated successfully for the selected class and term.";
            } else {
                $errors[] = "Failed to update fees: " . mysqli_error($conn);
            }
        } else {
            // INSERT new
            $insert_sql = "
                INSERT INTO term_info (class_id, term_id, academic_year, term_end, next_start, fees_day, fees_boarding)
                VALUES ($class_id, $term_id, '$year', '$term_end', '$next_start', '$fees_day', '$fees_board')
            ";
            $res = mysqli_query($conn, $insert_sql);
            if ($res) {
                $success = "Fees structure added successfully.";
                $class_id && $term_id && $year && $term_end && $next_start && $fees_day && $fees_board = "";
            } else {
                $errors[] = "Failed to insert fees: " . mysqli_error($conn);
            }
        }
    }
}
?>

<div class="container-fluid">
    <h3 class="text-capitalize fs-6 text-dark py-2">Enter fees structure</h3>

    <div class="row g-0 shadow rounded my-4 p-4">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e) echo "<div>$e</div>"; ?>
            </div>
        <?php elseif (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="col-lg-6 col-12 p-2">
            <form method="POST" action="">
                <!-- Class Dropdown -->
                <div class="mb-3">
                    <label class="form-label text-capitalize fw-bold">Class</label>
                    <select class="form-select shadow-none" name="class_name" required>
                        <option selected disabled>Choose class</option>
                        <?php 
                            $classes = mysqli_query($conn, "SELECT * FROM classes ORDER BY class_name");
                            if($classes && mysqli_num_rows($classes)>0){
                                while($fetchClass = mysqli_fetch_assoc($classes)){
                                    echo "<option value='{$fetchClass['id']}' ".($fetchClass['id']==($class_id??'')?'selected':'').">{$fetchClass['class_name']}</option>";
                                }
                            }
                        ?>
                    </select>
                </div>

                <!-- Term Dropdown -->
                <div class="mb-3">
                    <label class="form-label text-capitalize fw-bold">Term</label>
                    <select class="form-select shadow-none" name="term" required>
                        <option selected disabled>Select</option>
                        <?php 
                            $terms = mysqli_query($conn, "SELECT * FROM terms ORDER BY term_id");
                            if($terms && mysqli_num_rows($terms) > 0){
                                while($t = mysqli_fetch_assoc($terms)){
                                    echo "<option value='{$t['term_id']}' ".($t['term_id']==($term_id??'')?'selected':'').">{$t['term_name']}</option>";
                                }
                            }
                        ?>
                    </select>
                </div>

                <!-- Academic Year -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Academic Year</label>
                    <input type="text" class="form-control shadow-none" name="year" placeholder="2025, 2026..." value="<?php echo htmlspecialchars($year ?? '') ?>" required>
                </div>

                <!-- Term End -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Term End</label>
                    <input type="date" class="form-control shadow-none" name="end" value="<?php echo htmlspecialchars($term_end ?? '') ?>" required>
                    <span class="text-danger fs-6">Enter date when the term has ended</span>
                </div>
        </div>

        <div class="col-lg-6 col-12 p-2">
            <!-- Next Term Start -->
            <div class="mb-3">
                <label class="form-label fw-bold">Next Term Begins</label>
                <input type="date" class="form-control shadow-none" name="next_term" value="<?php echo htmlspecialchars($next_start ?? '') ?>" required>
                <span class="text-danger fs-6">Enter date when next term begins</span>
            </div>

            <!-- Day Fees -->
            <div class="mb-3">
                <label class="form-label fw-bold text-capitalize">Day Fees</label>
                <input type="number" class="form-control shadow-none" name="d_fees" placeholder="Enter day fees" value="<?php echo htmlspecialchars($fees_day ?? '') ?>" required>
            </div>

            <!-- Boarding Fees -->
            <div class="mb-3">
                <label class="form-label fw-bold">Boarding Fees</label>
                <input type="number" class="form-control shadow-none" name="b_fees" placeholder="Enter boarding fees" value="<?php echo htmlspecialchars($fees_board ?? '') ?>" required>
            </div>

            <button type="submit" name="fees" class="btn btn-success w-100">Submit</button>
            </form>
        </div>
    </div>
</div>

<?php include("partials/footer.php"); ?>
