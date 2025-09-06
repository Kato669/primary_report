<?php
ob_start();
include("partials/header.php");
include("partials/adminOnly.php");

// Initialize variables
$teacher_id = $initials = $classes = $stream = $subject = "";
$errors = [];

if(isset($_POST['addteacher'])){
    // Get and sanitize input
    $teacher_id = $_POST['teacher_id'] ?? "";
    $initials = strtoupper(trim($_POST['initials'] ?? ""));
    $classes = $_POST['classes'] ?? "";
    $stream = $_POST['stream'] ?? "";
    $subject = $_POST['subject'] ?? "";

    // Validate teacher_id
    if(empty($teacher_id) || !ctype_digit($teacher_id)){
        $errors['teacher_id'] = "Valid teacher selection is required.";
    }

    // Validate initials
    if(empty($initials)){
        $errors['initials'] = "Initials are required.";
    } elseif(!preg_match('/^[A-Z]+$/', $initials)){
        $errors['initials'] = "Initials must contain only uppercase letters.";
    }

    // Validate class
    if(empty($classes) || !ctype_digit($classes)){
        $errors['classes'] = "Valid class selection is required.";
    }

    // Validate stream
    if(empty($stream) || !ctype_digit($stream)){
        $errors['stream'] = "Valid stream selection is required.";
    }

    // Validate subject
    if(empty($subject) || !ctype_digit($subject)){
        $errors['subject'] = "Valid subject selection is required.";
    }

    // Check for duplication
    if(empty($errors)){
        $teacher_idEsc = mysqli_real_escape_string($conn, $teacher_id);
        $initialsEsc = mysqli_real_escape_string($conn, $initials);
        $classesEsc = mysqli_real_escape_string($conn, $classes);
        $streamEsc = mysqli_real_escape_string($conn, $stream);
        $subjectEsc = mysqli_real_escape_string($conn, $subject);

        $dup_sql = "SELECT * FROM teacher_subject_assignments 
                    WHERE teacher_id='$teacher_idEsc' 
                      AND class_id='$classesEsc' 
                      AND stream_id='$streamEsc' 
                      AND subject_id='$subjectEsc'";
        $dup_res = mysqli_query($conn, $dup_sql);

        if(mysqli_num_rows($dup_res) > 0){
            echo '<div class="alert alert-warning">This teacher is already assigned to this class, stream, and subject.</div>';
        } else {
            $sql = "INSERT INTO teacher_subject_assignments (teacher_id, initials, class_id, stream_id, subject_id) 
                    VALUES ('$teacher_idEsc', '$initialsEsc', '$classesEsc', '$streamEsc', '$subjectEsc')";
            if(mysqli_query($conn, $sql)){
                echo '<div class="alert alert-success">Teacher assignment added successfully.</div>';
                $teacher_id = $initials = $classes = $stream = $subject = "";
            } else {
                echo '<div class="alert alert-danger">Error: '.mysqli_error($conn).'</div>';
            }
        }
    }
}
?>

<div class="container-fluid my-3">
    <div class="row">
        <h3 class="text-capitalize fs-6 text-dark py-2">Enter teachers and subjects they teach</h3>
        <div class="col-lg-6 col-sm-12 col-md-12 shadow rounded p-3">
            <form method="POST" action="">
                <!-- Teacher Dropdown -->
                <div class="mb-3">
                    <label for="teacher_id" class="form-label fw-bold">Teacher Fullname</label>
                    <select class="form-select shadow-none" name="teacher_id" required>
                        <option selected disabled>Choose teacher</option>
                        <?php 
                        $users = mysqli_query($conn, "SELECT user_id, fullname FROM users ORDER BY fullname");
                        while($user = mysqli_fetch_assoc($users)){
                            $selected = ($teacher_id == $user['user_id']) ? 'selected' : '';
                            echo "<option value='{$user['user_id']}' {$selected}>{$user['fullname']}</option>";
                        }
                        ?>
                    </select>
                    <?php if(isset($errors['teacher_id'])): ?>
                        <small class="text-danger"><?= $errors['teacher_id']; ?></small>
                    <?php endif; ?>
                </div>

                <!-- Initials -->
                <div class="mb-3">
                    <label for="initials" class="form-label fw-bold">Teacher Initials</label>
                    <input type="text" class="form-control shadow-none text-uppercase" name="initials" placeholder="JD" value="<?= htmlspecialchars($initials); ?>">
                    <?php if(isset($errors['initials'])): ?>
                        <small class="text-danger"><?= $errors['initials']; ?></small>
                    <?php endif; ?>
                </div>

                <!-- Class Dropdown -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Class</label>
                    <select id="classSelect" class="form-select shadow-none" name="classes" required>
                        <option disabled selected>Choose class</option>
                        <?php
                        $classes_res = mysqli_query($conn, "SELECT * FROM classes ORDER BY class_name");
                        while($cls = mysqli_fetch_assoc($classes_res)){
                            $selected = ($classes == $cls['id']) ? 'selected' : '';
                            echo "<option value='{$cls['id']}' {$selected}>{$cls['class_name']}</option>";
                        }
                        ?>
                    </select>
                    <?php if(isset($errors['classes'])): ?>
                        <small class="text-danger"><?= $errors['classes']; ?></small>
                    <?php endif; ?>
                </div>

                <!-- Stream Dropdown -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Stream</label>
                    <select id="streamSelect" class="form-select shadow-none" name="stream" required>
                        <option disabled selected>Choose stream</option>
                        <?php
                        if($classes){
                            $streams_res = mysqli_query($conn, "SELECT * FROM streams WHERE class_id='$classes' ORDER BY stream_name");
                            while($str = mysqli_fetch_assoc($streams_res)){
                                $selected = ($stream == $str['id']) ? 'selected' : '';
                                echo "<option value='{$str['id']}' {$selected}>{$str['stream_name']}</option>";
                            }
                        }
                        ?>
                    </select>
                    <?php if(isset($errors['stream'])): ?>
                        <small class="text-danger"><?= $errors['stream']; ?></small>
                    <?php endif; ?>
                </div>

                <!-- Subject Dropdown -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Subject</label>
                    <select id="subjectSelect" class="form-select shadow-none" name="subject" required>
                        <option disabled selected>Choose subject</option>
                        <?php
                        if($classes){
                            $subject_res = mysqli_query($conn, "SELECT s.subject_id, s.subject_name
                                FROM class_subjects cs 
                                JOIN subjects s ON cs.subject_id = s.subject_id
                                WHERE cs.class_id='$classes' ORDER BY s.subject_name");
                            while($sub = mysqli_fetch_assoc($subject_res)){
                                $selected = ($subject == $sub['subject_id']) ? 'selected' : '';
                                echo "<option value='{$sub['subject_id']}' {$selected}>{$sub['subject_name']}</option>";
                            }
                        }
                        ?>
                    </select>
                    <?php if(isset($errors['subject'])): ?>
                        <small class="text-danger"><?= $errors['subject']; ?></small>
                    <?php endif; ?>
                </div>

                <button type="submit" name="addteacher" class="btn btn-success text-capitalize">Add teacher</button>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    $('#classSelect').change(function(){
        var classID = $(this).val();

        // Update streams
        $.get('get_streams.php', {class_id: classID}, function(data){
            $('#streamSelect').html(data);
        });

        // Update subjects
        $.get('get_subjects.php', {class_id: classID}, function(data){
            $('#subjectSelect').html(data);
        });
    });
});
</script>

<?php include("partials/footer.php"); ?>
