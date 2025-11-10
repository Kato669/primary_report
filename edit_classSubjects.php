<?php 
ob_start();
include("partials/header.php");

// Make sure $conn (DB connection) is included
// include("partials/db_connect.php");

// Check for class ID in URL
if(!isset($_GET['class_id'])){
    header("Location: class_subjects.php");
    exit();
}

$class_id = intval($_GET['class_id']);

// Fetch current class and its subjects
$stmt = $conn->prepare("
    SELECT c.id as class_id, 
           c.class_name,
           GROUP_CONCAT(cs.subject_id) as subject_ids
    FROM classes c
    LEFT JOIN class_subjects cs ON c.id = cs.class_id
    WHERE c.id = ?
    GROUP BY c.id, c.class_name
");
$stmt->bind_param("i", $class_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0){
    die("Class record not found.");
}

$record = $result->fetch_assoc();
$currentClass = $record['class_id'];
$currentSubjects = $record['subject_ids'] ? explode(',', $record['subject_ids']) : [];

// Handle form submission
if(isset($_POST['updateClass'])){
    $selected_subjects = isset($_POST['subjects']) ? $_POST['subjects'] : [];
    
    if(empty($selected_subjects)){
        echo '<div class="alert alert-danger">Please select at least one subject.</div>';
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // First delete all existing subjects for this class
            $delete = $conn->prepare("DELETE FROM class_subjects WHERE class_id = ?");
            $delete->bind_param("i", $class_id);
            $delete->execute();
            
            // Insert new subject selections
            $insert = $conn->prepare("INSERT INTO class_subjects (class_id, subject_id) VALUES (?, ?)");
            foreach($selected_subjects as $subject_id){
                $insert->bind_param("ii", $class_id, $subject_id);
                $insert->execute();
            }
            
            $conn->commit();
            $_SESSION['update_subject'] = "Subjects updated successfully";
            header("Location: class_subjects.php");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            echo '<div class="alert alert-danger">Update failed. Please try again.</div>';
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
                    <input type="text" class="form-control" value="<?php echo $record['class_name']; ?>" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Select Subjects</label>
                    <div class="border rounded p-3">
                        <?php 
                        $subjects = $conn->query("SELECT * FROM subjects ORDER BY subject_name ASC");
                        while($row = $subjects->fetch_assoc()){
                            $checked = in_array($row['subject_id'], $currentSubjects) ? "checked" : "";
                            echo "
                            <div class='form-check'>
                                <input class='form-check-input' type='checkbox' name='subjects[]' 
                                    value='{$row['subject_id']}' id='subject_{$row['subject_id']}' $checked>
                                <label class='form-check-label' for='subject_{$row['subject_id']}'>
                                    {$row['subject_name']}
                                </label>
                            </div>";
                        }
                        ?>
                    </div>
                </div>

                <button type="submit" name="updateClass" class="btn btn-warning">Update Subjects</button>
                <a href="class_subjects.php" class="btn btn-secondary">Cancel</a>
            </form>
          
        </div>
    </div>
</div>

<?php include("partials/footer.php"); ?>
