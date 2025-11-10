<?php 
// ob_start();
    ob_start();
    include("partials/header.php");
    // include("partials/adminOnly.php");

$errors = [];
$success = [];

if (isset($_POST['addExam'])) {
    $exam = trim(strtolower(mysqli_real_escape_string($conn, $_POST['exam'] ?? "")));
    $term_id = trim($_POST['term'] ?? "");
    $classes = $_POST['class'] ?? []; // array of classes
    $year = intval(trim($_POST['year'] ?? ""));

    if (empty($exam) || empty($term_id) || empty($classes) || empty($year)) {
        $errors[] = "Please fill in all fields.";
    } elseif (!preg_match('/^\d{4}$/', $year)) {
        $errors[] = "Academic year must be a valid 4-digit year.";
    } else {
        foreach ($classes as $class_id) {
            if (empty($class_id)) continue; // skip empty selects

            $class_id = intval($class_id);
            $check_sql = "SELECT * FROM exams 
                          WHERE exam_name = '$exam' 
                          AND term_id = $term_id 
                          AND academic_year = $year 
                          AND class_id = $class_id
                          LIMIT 1";
            $check_res = mysqli_query($conn, $check_sql);

            if ($check_res && mysqli_num_rows($check_res) > 0) {
                $errors[] = "Exam '$exam' already exists for class ID $class_id in $year.";
                continue;
            }

            $sql = "INSERT INTO exams SET
                        exam_name = '$exam',
                        term_id = $term_id,
                        academic_year = $year,
                        class_id = $class_id";
            $res = mysqli_query($conn, $sql);

            if ($res) {
                $exam_type = strtolower($exam);
                mysqli_query($conn, "
                INSERT INTO exam_visibility (class_id, exam_type, visible)
                VALUES ($class_id, '$exam_type', 1)
                ON DUPLICATE KEY UPDATE visible = visible;
                ");

                $success[] = "Added exam for class ID $class_id successfully.";
            } else {
                $errors[] = "Failed to insert exam for class ID $class_id: " . mysqli_error($conn);
            }
        }
    }
}

$role = $_SESSION['role'] ?? '';
$class_id = $_SESSION['class_id'] ?? null;
$stream_id = $_SESSION['stream_id'] ?? null;
?>

<div class="container-fluid my-3">
    <div class="row">
        <div class="col-lg-6 col-sm-12 col-md-12 shadow rounded p-5">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger"><?php foreach ($errors as $e) echo "<div>$e</div>"; ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php foreach ($success as $s) echo "<div>$s</div>"; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="exam" class="form-label fw-bold shadow-none">Exam Name</label>
                    <input type="text" class="form-control" name="exam" id="exam"
                           placeholder="B.O.T, M.O.T, E.O.T, Mock" value="<?php echo htmlspecialchars($_POST['exam'] ?? ''); ?>">
                    <span class="text-danger fs-6">Enter B.O.T for beginning of term, M.O.T, E.O.T </span>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Term</label>
                    <select class="form-select" name="term">
                        <option disabled <?php echo empty($_POST['term']) ? 'selected' : ''; ?>>Choose term</option>
                        <?php
                        $selectTerm = mysqli_query($conn, "SELECT * FROM terms");
                        while ($row = mysqli_fetch_assoc($selectTerm)) {
                            $selected = (isset($_POST['term']) && $_POST['term'] == $row['term_id']) ? 'selected' : '';
                            echo "<option class='shadow-none' value='{$row['term_id']}' $selected>{$row['term_name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold d-flex justify-content-between align-items-center">
                        Classes 
                        <?php if ($role === 'admin'): ?>
                            <button type="button" id="addClass" class="btn btn-sm btn-outline-primary">+</button>
                        <?php endif; ?>
                    </label>
                    <div id="classWrapper">
                        <?php if ($role === 'admin'): ?>
                            <div class="d-flex align-items-center class-select mb-2">
                                <select class="form-select me-2" name="class[]">
                                    <option disabled selected>Choose class</option>
                                    <?php 
                                    $classes = mysqli_query($conn, "SELECT * FROM classes");
                                    while ($row = mysqli_fetch_assoc($classes)) {
                                        echo "<option class='shadow-none' value='{$row['id']}'>{$row['class_name']}</option>";
                                    }
                                    ?>
                                </select>
                                <button type="button" class="btn btn-sm btn-outline-danger removeClass">-</button>
                            </div>
                        <?php elseif ($role === 'class_teacher' && $class_id): ?>
                            <input type="hidden" name="class[]" value="<?php echo $class_id; ?>">
                            <select class="form-select me-2" disabled>
                                <?php 
                                $class = mysqli_query($conn, "SELECT * FROM classes WHERE id=$class_id");
                                $row = mysqli_fetch_assoc($class);
                                echo "<option selected>{$row['class_name']}</option>";
                                ?>
                            </select>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($role === 'class_teacher' && $stream_id): ?>
                    <input type="hidden" name="stream_id" value="<?php echo $stream_id; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="year" class="form-label fw-bold">Academic Year</label>
                    <input type="text" class="form-control shadow-none" name="year" id="year"
                           placeholder="2025, 2026...." value="<?php echo htmlspecialchars($_POST['year'] ?? ''); ?>">
                </div>

                <button type="submit" name="addExam" class="btn btn-primary">Add Exam</button>
            </form>
        </div>
    </div>
</div>

<script>
<?php if ($role === 'admin'): ?>
const wrapper = document.getElementById('classWrapper');
const addBtn = document.getElementById('addClass');

addBtn.addEventListener('click', () => {
    const firstSelect = wrapper.firstElementChild.cloneNode(true);
    firstSelect.querySelector('select').value = ""; // reset selected value
    wrapper.appendChild(firstSelect);
});

// Event delegation to handle remove buttons dynamically
wrapper.addEventListener('click', function(e) {
    if (e.target.classList.contains('removeClass')) {
        if (wrapper.children.length > 1) {
            e.target.closest('.class-select').remove();
        } else {
            // Just reset the select if it's the only one left
            wrapper.querySelector('select').value = "";
        }
    }
});
<?php endif; ?>
</script>

<?php include("partials/footer.php"); ?>
