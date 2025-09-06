<?php 
ob_start();
include("partials/header.php");

// Make sure $conn (DB connection) is included
// include("partials/db_connect.php");

// Check for class_subject ID in URL
if(!isset($_GET['id'])){
    header("Location: class_subjects.php");
    exit();
}

$class_subject_id = intval($_GET['id']);

// Fetch current record
$stmt = $conn->prepare("SELECT * FROM class_subjects WHERE id = ?");
$stmt->bind_param("i", $class_subject_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0){
    die("Class-Subject record not found.");
}

$record = $result->fetch_assoc();
$currentClass = $record['class_id'];
$currentSubject = $record['subject_id'];

// Handle form submission
if(isset($_POST['updateClass'])){
    $class_id = intval($_POST['class'] ?? 0);
    $subject_id = intval($_POST['subject'] ?? 0);

    // Validate
    if($class_id === 0 || $subject_id === 0){
        echo '<div class="alert alert-danger">Please select both class and subject.</div>';
    } else {
        // Check for duplicates (excluding current record)
        $dupCheck = $conn->prepare("SELECT * FROM class_subjects WHERE class_id = ? AND subject_id = ? AND id != ?");
        $dupCheck->bind_param("iii", $class_id, $subject_id, $class_subject_id);
        $dupCheck->execute();
        $dupResult = $dupCheck->get_result();

        if($dupResult->num_rows > 0){
            echo '<div class="alert alert-warning">This class and subject combination already exists.</div>';
        } else {
            // Update record
            $update = $conn->prepare("UPDATE class_subjects SET class_id = ?, subject_id = ? WHERE id = ?");
            $update->bind_param("iii", $class_id, $subject_id, $class_subject_id);

            if($update->execute()){
                $_SESSION['update_subject'] = "updated successfully";
                header("Location: class_subjects.php?msg=updated");
                exit();
            } else {
                echo '<div class="alert alert-danger">Update failed. Please try again.</div>';
            }
        }
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-6 rounded shadow m-3 py-3 px-4">
            <h3 class="text-capitalize fs-6 text-dark py-2">Edit Class-Subject</h3>
            <form method="POST" action="">
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
                    <label class="form-label fw-bold">Subject</label>
                    <select class="form-select" name="subject">
                        <option disabled>Choose subject</option>
                        <?php 
                        $subjects = $conn->query("SELECT * FROM subjects");
                        while($row = $subjects->fetch_assoc()){
                            $selected = ($currentSubject == $row['subject_id']) ? "selected" : "";
                            echo "<option value='{$row['subject_id']}' $selected>{$row['subject_name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <button type="submit" name="updateClass" class="btn btn-warning">Update</button>
            </form>
          
        </div>
    </div>
</div>

<?php include("partials/footer.php"); ?>
