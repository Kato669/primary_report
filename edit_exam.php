<?php
ob_start();
include("partials/header.php");

$errors = [];

// Make sure exam_id is provided
if(!isset($_GET['exam_id'])){
    header("Location: examination.php");
    exit();
}

$exam_id = intval($_GET['exam_id']);

// Fetch current exam using prepared statement
$stmt = $conn->prepare("SELECT * FROM exams WHERE exam_id = ?");
if(!$stmt){
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $exam_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0){
    die("Exam not found.");
}

$examData = $result->fetch_assoc();

// Current values for preselecting dropdowns
$currentTerm = $examData['term_id'];
$currentClass = $examData['class_id'];

// Handle form submission
if(isset($_POST['updateExam'])){
    $exam_name = trim(strtolower(mysqli_real_escape_string($conn, $_POST['exam'] ?? "")));
    $term_id = intval($_POST['term'] ?? 0);
    $class_id = intval($_POST['class'] ?? 0);
    $year = trim($_POST['year'] ?? "");

    // Validation
    if(empty($exam_name) || $term_id === 0 || $class_id === 0 || empty($year)){
        $errors[] = "Please fill in all fields.";
    } elseif(!preg_match('/^\d{4}$/', $year)){
        $errors[] = "Academic year must be a valid 4-digit year.";
    } else {
        // Check for duplicates (excluding current exam)
        $dupCheck = $conn->prepare("SELECT * FROM exams WHERE exam_name=? AND term_id=? AND class_id=? AND academic_year=? AND exam_id != ?");
        if(!$dupCheck){
            die("Prepare failed: " . $conn->error);
        }
        $dupCheck->bind_param("siisi", $exam_name, $term_id, $class_id, $year, $exam_id);
        $dupCheck->execute();
        $dupResult = $dupCheck->get_result();

        if($dupResult->num_rows > 0){
            $errors[] = "An exam with the same name, term, class, and year already exists.";
        } else {
            // Update the exam
            $update = $conn->prepare("UPDATE exams SET exam_name=?, term_id=?, class_id=?, academic_year=? WHERE exam_id=?");
            if(!$update){
                die("Prepare failed: " . $conn->error);
            }
            $update->bind_param("siisi", $exam_name, $term_id, $class_id, $year, $exam_id);

            if($update->execute()){
                header("Location: examination.php?msg=updated");
                exit();
            } else {
                $errors[] = "Failed to update exam. Please try again.";
            }
        }
    }
}
?>

<div class="container-fluid my-3">
    <div class="row">
        <div class="col-lg-6 col-sm-12 col-md-12 shadow rounded p-5">
            <?php if(!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach($errors as $e) echo "<div>$e</div>"; ?>
                </div>
            <?php endif; ?>

            <h4 class="mb-3 text-primary">Update Exam</h4>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="exam" class="form-label fw-bold">Exam Name</label>
                    <input type="text" class="form-control" name="exam" id="exam" value="<?php echo htmlspecialchars($examData['exam_name']); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Term</label>
                    <select class="form-select" name="term">
                        <option disabled>Choose term</option>
                        <?php
                        $terms = $conn->query("SELECT * FROM terms");
                        while($row = $terms->fetch_assoc()){
                            $selected = ($currentTerm == $row['term_id']) ? "selected" : "";
                            echo "<option value='{$row['term_id']}' $selected>{$row['term_name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Class</label>
                    <select class="form-select" name="class">
                        <option disabled>Choose class</option>
                        <?php
                        $classes = $conn->query("SELECT * FROM classes");
                        while($row = $classes->fetch_assoc()){
                            $selected = ($currentClass == $row['id']) ? "selected" : "";
                            echo "<option value='{$row['id']}' $selected>{$row['class_name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="year" class="form-label fw-bold">Academic Year</label>
                    <input type="text" class="form-control" name="year" id="year" value="<?php echo htmlspecialchars($examData['academic_year']); ?>">
                </div>

                <button type="submit" name="updateExam" class="btn btn-primary w-100">Update Exam</button>
            </form>
        </div>
    </div>
</div>

<?php include("partials/footer.php"); ?>
