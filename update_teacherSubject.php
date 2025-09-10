<?php
ob_start();
include("partials/header.php");

if (!isset($_GET['id'])) {
    header("Location:" . SITEURL . "teacherSubject.php");
    exit;
}

$assigned_id = intval($_GET['id']);
$errors = [];

// Fetch the current assignment to populate the form
$select_assign = "SELECT * FROM teacher_subject_assignments WHERE id = $assigned_id LIMIT 1";
$res_assign = mysqli_query($conn, $select_assign);
if (!$res_assign || mysqli_num_rows($res_assign) === 0) {
    $_SESSION['error'] = "Assignment not found.";
    header("Location:" . SITEURL . "teacherSubject.php");
    exit;
}
$current = mysqli_fetch_assoc($res_assign);

// Pre-fill form variables
$teacher_id     = $current['teacher_id'];
$classes        = $current['class_id'];
$stream         = $current['stream_id'];
$subject        = $current['subject_id'];
$initials       = $current['initials'] ?? '';
$term_id        = $current['term_id'] ?? '';
$academic_year  = $current['academic_year'] ?? '';

if (isset($_POST['update_teacher'])) {
    $teacher_id_post = intval($_POST['teacher_id'] ?? 0);
    $classes_post    = intval($_POST['classes'] ?? 0);
    $stream_post     = intval($_POST['stream'] ?? 0);
    $subject_post    = intval($_POST['subject'] ?? 0);
    $term_post       = intval($_POST['term_id'] ?? 0);
    $year_post       = trim($_POST['academic_year'] ?? '');
    $initials_post   = strtoupper(trim($_POST['initials'] ?? ''));

    // Validation
    if ($teacher_id_post <= 0) $errors['teacher_id'] = "Select a teacher.";
    if ($classes_post <= 0) $errors['classes'] = "Select a class.";
    if ($stream_post <= 0) $errors['stream'] = "Select a stream.";
    if ($subject_post <= 0) $errors['subject'] = "Select a subject.";
    if ($term_post <= 0) $errors['term_id'] = "Select a term.";
    if (empty($year_post) || !preg_match('/^[0-9]{4}$/', $year_post)) {
        $errors['academic_year'] = "Enter a valid year (e.g. 2025).";
    }
    if (empty($initials_post) || !preg_match('/^[A-Z]{1,5}$/', $initials_post)) {
        $errors['initials'] = "Enter valid initials (1-5 uppercase letters).";
    }

    // Check for duplicate assignment excluding current assignment
    $dup_check = "SELECT * FROM teacher_subject_assignments 
                  WHERE teacher_id=$teacher_id_post 
                    AND class_id=$classes_post 
                    AND stream_id=$stream_post 
                    AND subject_id=$subject_post
                    AND term_id=$term_post
                    AND academic_year='$year_post'
                    AND id != $assigned_id";
    $dup_res = mysqli_query($conn, $dup_check);
    if ($dup_res && mysqli_num_rows($dup_res) > 0) {
        $errors['duplicate'] = "This teacher already has this subject for the same class, stream, term, and year.";
    }

    // If no errors, update the assignment
    if (empty($errors)) {
        $update_sql = "UPDATE teacher_subject_assignments 
                       SET teacher_id=$teacher_id_post, 
                           class_id=$classes_post, 
                           stream_id=$stream_post, 
                           subject_id=$subject_post, 
                           term_id=$term_post,
                           academic_year='".mysqli_real_escape_string($conn, $year_post)."',
                           initials='".mysqli_real_escape_string($conn, $initials_post)."'
                       WHERE id=$assigned_id";
        $res_update = mysqli_query($conn, $update_sql);

        if ($res_update) {
            $_SESSION['success'] = "Teacher subject assignment updated successfully.";
            header("Location:" . SITEURL . "teacherSubject.php");
            exit;
        } else {
            $errors['db'] = "Failed to update assignment: " . mysqli_error($conn);
        }
    }
}
?>

<h3 class="text-capitalize fs-6 text-dark py-2">Update Teacher Subjects</h3>
<div class="container-fluid my-3">
    <div class="row">
        <div class="col-lg-6 col-sm-12 col-md-12 shadow rounded p-3">
            <form method="POST" action="">
                <!-- Teacher -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Teacher Fullname</label>
                    <select class="form-select shadow-none" name="teacher_id" required>
                        <option selected disabled>Choose teacher</option>
                        <?php 
                        $user = "SELECT * FROM users ORDER BY fullname";
                        $executeUser = mysqli_query($conn, $user);
                        while($fetchUser = mysqli_fetch_assoc($executeUser)) {
                            $u_id = $fetchUser['user_id'];
                            $f_fullname = $fetchUser['fullname'];
                            ?>
                            <option value="<?php echo $u_id ?>" <?php echo ($teacher_id == $u_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($f_fullname); ?>
                            </option>
                        <?php } ?>
                    </select>
                    <?php if(isset($errors['teacher_id'])) echo "<small class='text-danger'>{$errors['teacher_id']}</small>"; ?>
                </div>

                <!-- Initials -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Teacher Initials</label>
                    <input type="text" class="form-control shadow-none text-uppercase" name="initials" placeholder="JD" value="<?php echo htmlspecialchars($initials); ?>">
                    <?php if(isset($errors['initials'])) echo "<small class='text-danger'>{$errors['initials']}</small>"; ?>
                </div>

                <!-- Class -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Class</label>
                    <select class="form-select shadow-none" name="classes" required>
                        <option disabled <?php echo empty($classes) ? 'selected' : ''; ?>>Choose class</option>
                        <?php 
                        $class = "SELECT * FROM classes ORDER BY class_name";
                        $executeClass = mysqli_query($conn, $class);
                        while($fetchClass = mysqli_fetch_assoc($executeClass)) {
                            $c_id = $fetchClass['id'];
                            $c_name = $fetchClass['class_name'];
                            ?>
                            <option value="<?php echo $c_id ?>" <?php echo ($classes == $c_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c_name); ?>
                            </option>
                        <?php } ?>
                    </select>
                    <?php if(isset($errors['classes'])) echo "<small class='text-danger'>{$errors['classes']}</small>"; ?>
                </div>

                <!-- Stream -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Stream</label>
                    <select class="form-select shadow-none" name="stream" required>
                        <option disabled <?php echo empty($stream) ? 'selected' : ''; ?>>Choose stream</option>
                        <?php 
                        $streamQ = "SELECT * FROM streams";
                        $executestream = mysqli_query($conn, $streamQ);
                        while($fetchstream = mysqli_fetch_assoc($executestream)) {
                            $s_id = $fetchstream['id'];
                            $s_name = $fetchstream['stream_name'];
                            ?>
                            <option value="<?php echo $s_id ?>" <?php echo ($stream == $s_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s_name); ?>
                            </option>
                        <?php } ?>
                    </select>
                    <?php if(isset($errors['stream'])) echo "<small class='text-danger'>{$errors['stream']}</small>"; ?>
                </div>

                <!-- Subject -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Subject</label>
                    <select class="form-select shadow-none" name="subject" required>
                        <option disabled <?php echo empty($subject) ? 'selected' : ''; ?>>Choose subject</option>
                        <?php 
                        $subjectQ = "SELECT * FROM subjects ORDER BY subject_name";
                        $executesubject = mysqli_query($conn, $subjectQ);
                        while($fetchSubject = mysqli_fetch_assoc($executesubject)) {
                            $sub_id = $fetchSubject['subject_id'];
                            $sub_name = $fetchSubject['subject_name'];
                            ?>
                            <option value="<?php echo $sub_id ?>" <?php echo ($subject == $sub_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sub_name); ?>
                            </option>
                        <?php } ?>
                    </select>
                    <?php if(isset($errors['subject'])) echo "<small class='text-danger'>{$errors['subject']}</small>"; ?>
                </div>

                <!-- Term -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Term</label>
                    <select class="form-select shadow-none" name="term_id" required>
                        <option disabled selected>Choose term</option>
                        <?php
                        $terms_res = mysqli_query($conn, "SELECT * FROM terms ORDER BY term_id");
                        while($term = mysqli_fetch_assoc($terms_res)){
                            $selected = ($term_id == $term['term_id']) ? 'selected' : '';
                            echo "<option value='{$term['term_id']}' {$selected}>{$term['term_name']}</option>";
                        }
                        ?>
                    </select>
                    <?php if(isset($errors['term_id'])) echo "<small class='text-danger'>{$errors['term_id']}</small>"; ?>
                </div>

                <!-- Academic Year -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Academic Year</label>
                    <input type="text" class="form-control shadow-none" name="academic_year" placeholder="2025" value="<?php echo htmlspecialchars($academic_year); ?>">
                    <?php if(isset($errors['academic_year'])) echo "<small class='text-danger'>{$errors['academic_year']}</small>"; ?>
                </div>

                <?php if(isset($errors['duplicate'])) echo "<small class='text-danger'>{$errors['duplicate']}</small>"; ?>
                <?php if(isset($errors['db'])) echo "<small class='text-danger'>{$errors['db']}</small>"; ?>

                <button type="submit" name="update_teacher" class="btn btn-success text-capitalize">Update Assignment</button>
            </form>
        </div>
    </div>
</div>

<?php include("partials/footer.php"); ?>
