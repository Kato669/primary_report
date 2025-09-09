<?php 
ob_start();
include("partials/header.php");
include("partials/adminOnly.php");

$errors = [];
$success = "";

// Capture selected filters (via GET or POST)
$class_id   = intval($_REQUEST['filter_class'] ?? 0);
$term_id    = intval($_REQUEST['filter_term'] ?? 0);
$year       = trim($_REQUEST['filter_year'] ?? "");

// Preload data if filters are set
$existing_data = null;
if ($class_id && $term_id && !empty($year)) {
    $fetch_sql = "SELECT * FROM term_info WHERE class_id=$class_id AND term_id=$term_id AND academic_year='$year' LIMIT 1";
    $fetch_res = mysqli_query($conn, $fetch_sql);
    if ($fetch_res && mysqli_num_rows($fetch_res) > 0) {
        $existing_data = mysqli_fetch_assoc($fetch_res);
    }
}

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
            } else {
                $errors[] = "Failed to insert fees: " . mysqli_error($conn);
            }
        }
    }
}
?>

<div class="container-fluid">
    <h3 class="text-capitalize fs-6 text-dark py-2">Enter fees structure</h3>

    <!-- FILTERS AT THE TOP -->
    <form method="GET" class="row g-3 mb-4 shadow-sm p-3 rounded">
        <div class="col-md-3">
            <select class="form-select" name="filter_class" required>
                <option value="">Select Class</option>
                <?php 
                    $classes = mysqli_query($conn, "SELECT * FROM classes ORDER BY class_name");
                    while($c = mysqli_fetch_assoc($classes)){
                        echo "<option value='{$c['id']}' ".($class_id==$c['id']?"selected":"").">{$c['class_name']}</option>";
                    }
                ?>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" name="filter_term" required>
                <option value="">Select Term</option>
                <?php 
                    $terms = mysqli_query($conn, "SELECT * FROM terms ORDER BY term_id");
                    while($t = mysqli_fetch_assoc($terms)){
                        echo "<option value='{$t['term_id']}' ".($term_id==$t['term_id']?"selected":"").">{$t['term_name']}</option>";
                    }
                ?>
            </select>
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" name="filter_year" placeholder="Enter Year (2025)" value="<?php echo htmlspecialchars($year) ?>" required>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">Load Data</button>
        </div>
    </form>

    <div class="row g-0 shadow rounded my-4 p-4">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><?php foreach ($errors as $e) echo "<div>$e</div>"; ?></div>
        <?php elseif (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- FORM FOR INSERT/UPDATE -->
        <form method="POST" action="" class="row">
            <input type="hidden" name="class_name" value="<?php echo $class_id ?>">
            <input type="hidden" name="term" value="<?php echo $term_id ?>">
            <input type="hidden" name="year" value="<?php echo htmlspecialchars($year) ?>">

            <div class="col-lg-6 col-12 p-2">
                <div class="mb-3">
                    <label class="form-label fw-bold">Term End</label>
                    <input type="date" class="form-control shadow-none" name="end" 
                           value="<?php echo $existing_data['term_end'] ?? '' ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Next Term Begins</label>
                    <input type="date" class="form-control shadow-none" name="next_term"
                           value="<?php echo $existing_data['next_start'] ?? '' ?>" required>
                </div>
            </div>

            <div class="col-lg-6 col-12 p-2">
                <div class="mb-3">
                    <label class="form-label fw-bold">Day Fees</label>
                    <input type="number" class="form-control shadow-none" name="d_fees"
                           value="<?php echo $existing_data['fees_day'] ?? '' ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Boarding Fees</label>
                    <input type="number" class="form-control shadow-none" name="b_fees"
                           value="<?php echo $existing_data['fees_boarding'] ?? '' ?>" required>
                </div>

                <button type="submit" name="fees" class="btn btn-success w-100">
                    <?php echo $existing_data ? "Update Fees" : "Add Fees"; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php include("partials/footer.php"); ?>
