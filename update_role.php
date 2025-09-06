<?php 
    ob_start();
    include("partials/header.php"); 
?>


<?php
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // If no ID is passed, redirect back to view page
    $_SESSION['error'] = "No assignment selected!";
    header("Location: teacher_assignments.php");
    exit();
}

$assignment_id = $_GET['id'];

// Fetch current record
$sql = "SELECT * FROM teacher_assignments WHERE id = $assignment_id LIMIT 1";
$res = mysqli_query($conn, $sql);

if (!$res || mysqli_num_rows($res) == 0) {
    $_SESSION['error'] = "Assignment not found!";
    header("Location:".SITEURL."assign_roles.php");
    exit();
}

$currentData = mysqli_fetch_assoc($res);
$current_user_id = $currentData['user_id'];
$current_class_id = $currentData['class_id'];
$current_stream_id = $currentData['stream_id'];

// Handle form submit
if (isset($_POST['update_assignment'])) {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $class_id = mysqli_real_escape_string($conn, $_POST['class_id']);
    $stream_id = mysqli_real_escape_string($conn, $_POST['stream_id']);

    // Basic validation
    if (empty($user_id) || empty($class_id) || empty($stream_id)) {
        $_SESSION['error'] = "All fields are required.";
    } else {
        // Check for duplicates
        $check_sql = "SELECT * FROM teacher_assignments 
                      WHERE user_id = '$user_id' 
                      AND class_id = '$class_id' 
                      AND stream_id = '$stream_id'
                      AND id != $assignment_id"; // exclude current record
        $check_res = mysqli_query($conn, $check_sql);

        if ($check_res && mysqli_num_rows($check_res) > 0) {
            $_SESSION['error'] = "This assignment already exists!";
        } else {
            // Perform update
            $update_sql = "UPDATE teacher_assignments 
                           SET user_id = '$user_id', 
                               class_id = '$class_id',
                               stream_id = '$stream_id'
                           WHERE id = $assignment_id";
            $update_res = mysqli_query($conn, $update_sql);

            if ($update_res) {
                $_SESSION['success'] = "Assignment updated successfully!";
                header("Location:".SITEURL."assign_roles.php");
                exit();
            } else {
                $_SESSION['error'] = "Failed to update assignment. Try again.";
            }
        }
    }
}
?>

<div class="container">
    <h3 class="text-capitalize my-3">Update Teacher Assignment</h3>

    <!-- Show feedback -->
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <div class="row">
        <div class="col-lg-6">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Select Teacher</label>
                    <select name="user_id" class="form-select" required>
                        <option value="">-- Select Teacher --</option>
                        <?php
                        $teacher_sql = "SELECT * FROM users";
                        $teacher_res = mysqli_query($conn, $teacher_sql);
                        while ($teacher = mysqli_fetch_assoc($teacher_res)) {
                            $selected = ($teacher['user_id'] == $current_user_id) ? "selected" : "";
                            echo "<option value='{$teacher['user_id']}' $selected>{$teacher['username']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Select Class</label>
                    <select name="class_id" class="form-select" required>
                        <option value="">-- Select Class --</option>
                        <?php
                        $class_sql = "SELECT * FROM classes";
                        $class_res = mysqli_query($conn, $class_sql);
                        while ($class = mysqli_fetch_assoc($class_res)) {
                            $selected = ($class['id'] == $current_class_id) ? "selected" : "";
                            echo "<option value='{$class['id']}' $selected>{$class['class_name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Select Stream</label>
                    <select name="stream_id" class="form-select" required>
                        <option value="">-- Select Stream --</option>
                        <?php
                        $stream_sql = "SELECT * FROM streams";
                        $stream_res = mysqli_query($conn, $stream_sql);
                        while ($stream = mysqli_fetch_assoc($stream_res)) {
                            $selected = ($stream['id'] == $current_stream_id) ? "selected" : "";
                            echo "<option value='{$stream['id']}' $selected>{$stream['stream_name']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <button type="submit" name="update_assignment" class="btn btn-primary">Update Assignment</button>
                <a href="assign_roles.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>

</div>

<?php include("partials/footer.php"); ?>
